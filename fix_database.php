<?php
// Script pour corriger complètement la base de données

// Informations de connexion à la base de données
$host = 'localhost';
$dbuser = 'root';
$dbpass = '';

try {
    // Connexion à MySQL sans spécifier de base de données
    $pdo = new PDO("mysql:host=$host;charset=utf8", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Correction de la base de données</h2>";
    
    // Vérifier si la base de données existe
    $stmt = $pdo->query("SHOW DATABASES LIKE 'blog_forum'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: orange;'>⚠️ La base de données 'blog_forum' n'existe pas. Création en cours...</p>";
        
        // Créer la base de données
        $pdo->exec("CREATE DATABASE `blog_forum` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p style='color: green;'>✅ Base de données 'blog_forum' créée avec succès !</p>";
    } else {
        echo "<p style='color: green;'>✅ La base de données 'blog_forum' existe déjà.</p>";
    }
    
    // Se connecter à la base de données blog_forum
    $pdo = new PDO("mysql:host=$host;dbname=blog_forum;charset=utf8", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Désactiver les vérifications de clés étrangères
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Supprimer toutes les tables existantes pour repartir de zéro
    $tables = ['article_tags', 'comments', 'articles', 'media', 'tags', 'categories', 'settings', 'users'];
    
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "<p style='color: orange;'>🗑️ Table '$table' supprimée.</p>";
    }
    
    // Réactiver les vérifications de clés étrangères
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Créer la table users avec la bonne structure
    $sql = "CREATE TABLE `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `nom` varchar(100) NOT NULL,
        `prenom` varchar(100) NOT NULL,
        `username` varchar(50) NOT NULL UNIQUE,
        `email` varchar(255) NOT NULL UNIQUE,
        `password` varchar(255) NOT NULL,
        `role` enum('user','admin','moderator') NOT NULL DEFAULT 'user',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `last_login` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_username` (`username`),
        KEY `idx_email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Table 'users' créée avec succès !</p>";
    
    // Créer les autres tables
    $tables_sql = [
        'categories' => "CREATE TABLE `categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nom` varchar(100) NOT NULL,
            `description` text,
            `slug` varchar(100) NOT NULL UNIQUE,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'articles' => "CREATE TABLE `articles` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `content` longtext NOT NULL,
            `excerpt` text,
            `slug` varchar(255) NOT NULL UNIQUE,
            `user_id` int(11) NOT NULL,
            `category_id` int(11) DEFAULT NULL,
            `status` enum('draft','published','archived') NOT NULL DEFAULT 'published',
            `featured` tinyint(1) NOT NULL DEFAULT 0,
            `views` int(11) NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_category_id` (`category_id`),
            KEY `idx_slug` (`slug`),
            KEY `idx_status` (`status`),
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'comments' => "CREATE TABLE `comments` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `content` text NOT NULL,
            `user_id` int(11) NOT NULL,
            `article_id` int(11) NOT NULL,
            `parent_id` int(11) DEFAULT NULL,
            `status` enum('pending','approved','spam') NOT NULL DEFAULT 'pending',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_article_id` (`article_id`),
            KEY `idx_parent_id` (`parent_id`),
            KEY `idx_status` (`status`),
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'tags' => "CREATE TABLE `tags` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(50) NOT NULL UNIQUE,
            `slug` varchar(50) NOT NULL UNIQUE,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'article_tags' => "CREATE TABLE `article_tags` (
            `article_id` int(11) NOT NULL,
            `tag_id` int(11) NOT NULL,
            PRIMARY KEY (`article_id`, `tag_id`),
            FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'media' => "CREATE TABLE `media` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `filename` varchar(255) NOT NULL,
            `original_name` varchar(255) NOT NULL,
            `file_path` varchar(500) NOT NULL,
            `file_size` int(11) NOT NULL,
            `mime_type` varchar(100) NOT NULL,
            `user_id` int(11) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'settings' => "CREATE TABLE `settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `setting_key` varchar(100) NOT NULL UNIQUE,
            `setting_value` text,
            `description` text,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_setting_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    
    foreach ($tables_sql as $tableName => $sql) {
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Table '$tableName' créée avec succès !</p>";
    }
    
    // Insérer les données par défaut
    $pdo->exec("INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
        ('site_title', 'Mon Blog', 'Titre du site'),
        ('site_description', 'Un blog moderne et élégant', 'Description du site'),
        ('site_theme', 'light', 'Thème du site (light/dark)'),
        ('posts_per_page', '10', 'Nombre d\'articles par page'),
        ('comments_enabled', '1', 'Activer les commentaires (1/0)'),
        ('registration_enabled', '1', 'Activer l\'inscription (1/0)'),
        ('maintenance_mode', '0', 'Mode maintenance (1/0)')");
    
    $pdo->exec("INSERT IGNORE INTO `categories` (`nom`, `description`, `slug`) VALUES
        ('Général', 'Articles généraux', 'general'),
        ('Technologie', 'Articles sur la technologie', 'technologie'),
        ('Lifestyle', 'Mode de vie et bien-être', 'lifestyle'),
        ('Sport', 'Actualités sportives', 'sport'),
        ('Culture', 'Arts, cinéma, littérature', 'culture')");
    
    $pdo->exec("INSERT IGNORE INTO `tags` (`name`, `slug`) VALUES
        ('PHP', 'php'),
        ('MySQL', 'mysql'),
        ('Web Design', 'web-design'),
        ('Développement', 'developpement'),
        ('Tutoriel', 'tutoriel')");
    
    echo "<p style='color: green;'>✅ Données par défaut insérées avec succès !</p>";
    
    echo "<hr>";
    echo "<h3>✅ Base de données corrigée avec succès !</h3>";
    echo "<p>Vous pouvez maintenant exécuter le script <a href='setup_default_user.php'>setup_default_user.php</a> pour créer les utilisateurs par défaut.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erreur : " . $e->getMessage() . "</p>";
    echo "<p>Vérifiez que :</p>";
    echo "<ul>";
    echo "<li>XAMPP est démarré (Apache et MySQL)</li>";
    echo "<li>Les informations de connexion sont correctes</li>";
    echo "<li>Vous avez les droits d'administrateur sur MySQL</li>";
    echo "</ul>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background: #f8f9fa;
}

h2, h3 {
    color: #2c3e50;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
}

p {
    margin: 10px 0;
    padding: 10px;
    border-radius: 5px;
}

hr {
    border: none;
    border-top: 1px solid #ddd;
    margin: 30px 0;
}

a {
    color: #3498db;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}
</style> 