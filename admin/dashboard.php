<?php 
require 'auth_check.php';
require '../includes/header.php';

// Connexion à la base de données
$host = 'localhost';
$dbname = 'blog_forum';
$dbuser = 'root';
$dbpass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données");
}

// Suppression utilisateur
if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    header('Location: dashboard.php');
    exit();
}
// Suppression article
if (isset($_GET['delete_article'])) {
    $id = intval($_GET['delete_article']);
    $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$id]);
    header('Location: dashboard.php');
    exit();
}
// Suppression commentaire
if (isset($_GET['delete_comment'])) {
    $id = intval($_GET['delete_comment']);
    $pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$id]);
    header('Location: dashboard.php');
    exit();
}
// Bloquer un article
if (isset($_GET['block_article'])) {
    $id = intval($_GET['block_article']);
    $pdo->prepare("UPDATE articles SET status = 'blocked' WHERE id = ?")->execute([$id]);
    header('Location: dashboard.php?msg=blocked');
    exit();
}
// Débloquer un article
if (isset($_GET['unblock_article'])) {
    try {
        $id = intval($_GET['unblock_article']);
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE articles SET status = 'published' WHERE id = ?");
            $result = $stmt->execute([$id]);
            if ($result) {
                header('Location: dashboard.php?msg=unblocked');
                exit();
            } else {
                header('Location: dashboard.php?error=update_failed');
                exit();
            }
        } else {
            header('Location: dashboard.php?error=invalid_id');
            exit();
        }
    } catch (PDOException $e) {
        header('Location: dashboard.php?error=database_error');
        exit();
    }
}
// Suppression message utilisateur
if (isset($_GET['delete_message'])) {
    $id = intval($_GET['delete_message']);
    $pdo->prepare("DELETE FROM admin_messages WHERE id = ?")->execute([$id]);
    header('Location: dashboard.php');
    exit();
}

// Récupérer les utilisateurs
$users = $pdo->query("SELECT id, username, email FROM users")->fetchAll(PDO::FETCH_ASSOC);
// Récupérer les articles (avec status et catégorie)
$articles = $pdo->query("
    SELECT a.id, a.title, a.status, a.category_id, c.nom as category_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    ORDER BY a.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
// Récupérer les commentaires
$comments = $pdo->query("SELECT id, content FROM comments")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les catégories
try {
    $categories = $pdo->query("SELECT id, nom FROM categories ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

// Récupérer les messages des utilisateurs (si la table existe)
try {
    $adminMessages = $pdo->query("SELECT id, username, message, created_at FROM admin_messages ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $adminMessages = [];
}
?>

    <style>
        body { font-family: Arial; background: #f4f4f4; margin: 0; padding: 20px; }
        .admin-header { 
            background: #2c3e50; 
            color: white; 
            padding: 20px; 
            border-radius: 10px; 
            margin-bottom: 30px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .admin-header h1 { margin: 0; }
        .admin-nav { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .nav-tabs { display: flex; gap: 10px; flex-wrap: wrap; }
        .nav-tab { padding: 10px 20px; background: #ecf0f1; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; color: #2c3e50; }
        .nav-tab.active { background: #3498db; color: white; }
        .nav-tab:hover { background: #bdc3c7; }
        .nav-tab.active:hover { background: #2980b9; }
        
        .section { display: none; }
        .section.active { display: block; }
        
        h2 { margin-top: 30px; color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ddd; padding: 12px; }
        th { background: #3498db; color: #fff; font-weight: bold; }
        tr:nth-child(even) { background: #f8f9fa; }
        tr:hover { background: #e9ecef; }
        
        a.btn-delete { color: #e74c3c; text-decoration: none; font-weight: bold; padding: 5px 10px; border-radius: 3px; }
        a.btn-delete:hover { background: #e74c3c; color: white; }
        a.btn-block { color: #e67e22; text-decoration: none; font-weight: bold; padding: 5px 10px; border-radius: 3px; }
        a.btn-block:hover { background: #e67e22; color: white; }
        a.btn-unblock { color: #27ae60; text-decoration: none; font-weight: bold; padding: 5px 10px; border-radius: 3px; }
        a.btn-unblock:hover { background: #27ae60; color: white; }
        button.btn-view { background: #2980b9; color: #fff; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
        button.btn-view:hover { background: #3498db; }
        
        .category-filter { margin-bottom: 20px; }
        .category-filter select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .category-filter label { font-weight: bold; margin-right: 10px; color: #2c3e50; }
        
        .logout-btn { 
            background: #e74c3c; 
            color: white; 
            border: none; 
            padding: 8px 16px; 
            border-radius: 5px; 
            cursor: pointer; 
            font-weight: bold;
        }
        .logout-btn:hover { background: #c0392b; }
        
        /* Styles pour le thème sombre dans le dashboard admin */
        .dark-theme .admin-header {
            background: #2c3e50 !important;
            color: #f1f1f1 !important;
        }
        
        .dark-theme .admin-header h1 {
            color: #f1f1f1 !important;
        }
        
        .dark-theme .logout-btn {
            background: #e74c3c !important;
            color: #f1f1f1 !important;
        }
        
        .dark-theme .logout-btn:hover {
            background: #c0392b !important;
        }
    </style>
</head>
<body>
    <?php if (isset($_GET['msg'])): ?>
        <div id="flash-message" style="background:#27ae60; color:#fff; padding:15px; text-align:center; position:fixed; top:20px; left:50%; transform:translateX(-50%); border-radius:6px; z-index:1000; min-width:200px;">
        <?php 
                if ($_GET['msg'] === 'blocked') echo "Article bloqué avec succès.";
                elseif ($_GET['msg'] === 'unblocked') echo "Article débloqué avec succès.";
            ?>
                </div>
        <script>
            setTimeout(function() {
                var msg = document.getElementById('flash-message');
                if (msg) msg.style.display = 'none';
            }, 2000); // 2 secondes
        </script>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div id="error-message" style="background:#e74c3c; color:#fff; padding:15px; text-align:center; position:fixed; top:20px; left:50%; transform:translateX(-50%); border-radius:6px; z-index:1000; min-width:200px;">
            <?php
                if ($_GET['error'] === 'update_failed') echo "Erreur lors de la mise à jour de l'article.";
                elseif ($_GET['error'] === 'invalid_id') echo "ID d'article invalide.";
                elseif ($_GET['error'] === 'database_error') echo "Erreur de base de données.";
                elseif ($_GET['error'] === 'admin_invalid') echo "Session administrateur invalide.";
                else echo "Une erreur s'est produite.";
            ?>
                    </div>
        <script>
            setTimeout(function() {
                var msg = document.getElementById('error-message');
                if (msg) msg.style.display = 'none';
            }, 3000); // 3 secondes
        </script>
    <?php endif; ?>
    <div class="admin-header">
        <h1>Dashboard Administrateur</h1>
        <button class="logout-btn" onclick="logout()">Déconnexion</button>
                    </div>

    <div class="admin-nav">
        <div class="nav-tabs">
            <button class="nav-tab active" onclick="showSection('users')">Utilisateurs</button>
            <button class="nav-tab" onclick="showSection('articles')">Articles</button>
            <button class="nav-tab" onclick="showSection('comments')">Commentaires</button>
            <?php if (!empty($adminMessages)): ?>
            <button class="nav-tab" onclick="showSection('messages')">Messages</button>
            <?php endif; ?>
                    </div>
                </div>

    <!-- Section Utilisateurs -->
    <div id="users-section" class="section active">
        <h2>Gestion des Utilisateurs</h2>
        <table>
            <tr><th>ID</th><th>Nom</th><th>Email</th><th>Action</th></tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td>
                    <a class="btn-delete" href="?delete_user=<?= $user['id'] ?>" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
                </div>

    <!-- Section Articles -->
    <div id="articles-section" class="section">
        <h2>Gestion des Articles</h2>
        
        <!-- Filtre par catégorie -->
        <div class="category-filter">
            <label for="category-filter">Filtrer par catégorie :</label>
            <select id="category-filter" onchange="filterArticles()">
                <option value="">Toutes les catégories</option>
                <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['nom']) ?></option>
                                <?php endforeach; ?>
            </select>
                    </div>

        <table id="articles-table">
            <tr><th>ID</th><th>Titre</th><th>Catégorie</th><th>Status</th><th>Action</th></tr>
            <?php foreach ($articles as $article): ?>
            <tr data-category="<?= $article['category_id'] ?? '' ?>">
                <td><?= $article['id'] ?></td>
                <td><?= htmlspecialchars($article['title']) ?></td>
                <td><?= htmlspecialchars($article['category_name'] ?? 'Non classé') ?></td>
                <td><?= htmlspecialchars($article['status']) ?></td>
                <td>
                    <button class="btn-view" onclick="showArticle(<?= $article['id'] ?>)">Voir</button>
                    <a class="btn-delete" href="?delete_article=<?= $article['id'] ?>" onclick="return confirm('Supprimer cet article ?')">Supprimer</a>
                    <?php if ($article['status'] === 'published'): ?>
                        <a class="btn-block" href="?block_article=<?= $article['id'] ?>" onclick="return confirm('Bloquer cet article ?')">Bloquer</a>
                    <?php else: ?>
                        <button class="btn-unblock" onclick="unblockArticle(<?= $article['id'] ?>)" style="background:none; border:none; cursor:pointer; color:#27ae60; font-weight:bold; padding:5px 10px; border-radius:3px;">Débloquer</button>
                    <?php endif; ?>
                </td>
            </tr>
                            <?php endforeach; ?>
        </table>
                    </div>

    <!-- Section Commentaires -->
    <div id="comments-section" class="section">
        <h2>Gestion des Commentaires</h2>
        <table>
            <tr><th>ID</th><th>Contenu</th><th>Action</th></tr>
            <?php foreach ($comments as $comment): ?>
            <tr>
                <td><?= $comment['id'] ?></td>
                <td><?= htmlspecialchars($comment['content']) ?></td>
                <td>
                    <a class="btn-delete" href="?delete_comment=<?= $comment['id'] ?>" onclick="return confirm('Supprimer ce commentaire ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
                    </div>

    <!-- Section Messages -->
    <?php if (!empty($adminMessages)): ?>
    <div id="messages-section" class="section">
        <h2>Messages des Utilisateurs</h2>
        <table>
            <tr><th>ID</th><th>Utilisateur</th><th>Message</th><th>Date</th><th>Action</th></tr>
            <?php foreach ($adminMessages as $msg): ?>
            <tr>
                <td><?= $msg['id'] ?></td>
                <td><?= htmlspecialchars($msg['username']) ?></td>
                <td><?= htmlspecialchars(substr($msg['message'], 0, 100)) ?>...</td>
                <td><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></td>
                <td>
                    <button class="btn-view" onclick="showMessage(<?= $msg['id'] ?>)">Voir</button>
                    <a class="btn-delete" href="?delete_message=<?= $msg['id'] ?>" onclick="return confirm('Supprimer ce message ?')">Supprimer</a>
                </td>
            </tr>
                                <?php endforeach; ?>
        </table>
                            </div>
                        <?php endif; ?>

    <!-- Modale pour afficher l'article -->
    <div id="articleModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
        <div style="background:#fff; padding:30px; border-radius:10px; max-width:600px; width:90%; position:relative;">
            <span style="position:absolute; top:10px; right:20px; cursor:pointer; font-size:20px;" onclick="closeModal()">&times;</span>
            <div id="articleContent">Chargement...</div>
                    </div>
                </div>

    <!-- Modale pour afficher le message -->
    <div id="messageModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
        <div style="background:#fff; padding:30px; border-radius:10px; max-width:600px; width:90%; position:relative;">
            <span style="position:absolute; top:10px; right:20px; cursor:pointer; font-size:20px;" onclick="closeMessageModal()">&times;</span>
            <div id="messageContent">Chargement...</div>
        </div>
    </div>
    <script>
    function showArticle(id) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_article_full.php?id=' + id, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('articleContent').innerHTML = xhr.responseText;
                document.getElementById('articleModal').style.display = 'flex';
            } else {
                document.getElementById('articleContent').innerHTML = "Erreur de chargement";
                document.getElementById('articleModal').style.display = 'flex';
            }
        };
        xhr.send();
    }
    function closeModal() {
        document.getElementById('articleModal').style.display = 'none';
    }
    
    function showMessage(id) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_message_full.php?id=' + id, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('messageContent').innerHTML = xhr.responseText;
                document.getElementById('messageModal').style.display = 'flex';
            } else {
                document.getElementById('messageContent').innerHTML = "Erreur de chargement";
                document.getElementById('messageModal').style.display = 'flex';
            }
        };
        xhr.send();
    }
    
    function closeMessageModal() {
        document.getElementById('messageModal').style.display = 'none';
    }

    // Fonction pour changer d'onglet
    function showSection(sectionName) {
        // Masquer toutes les sections
        const sections = document.querySelectorAll('.section');
        sections.forEach(section => section.classList.remove('active'));
        
        // Afficher la section sélectionnée
        document.getElementById(sectionName + '-section').classList.add('active');
        
        // Mettre à jour les onglets
        const tabs = document.querySelectorAll('.nav-tab');
        tabs.forEach(tab => tab.classList.remove('active'));
        event.target.classList.add('active');
    }

    // Fonction pour filtrer les articles par catégorie
    function filterArticles() {
        const selectedCategory = document.getElementById('category-filter').value;
        const rows = document.querySelectorAll('#articles-table tr[data-category]');
        
        rows.forEach(row => {
            if (selectedCategory === '' || row.getAttribute('data-category') === selectedCategory) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Fonction de déconnexion
    function logout() {
        if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
            window.location.href = 'login.php?logout=1';
        }
    }
    
    // Fonction pour débloquer un article de manière sécurisée
    function unblockArticle(articleId) {
        if (confirm('Êtes-vous sûr de vouloir débloquer cet article ?')) {
            // Créer un formulaire temporaire pour envoyer la requête
            var form = document.createElement('form');
            form.method = 'GET';
            form.action = 'dashboard.php';
            
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'unblock_article';
            input.value = articleId;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script> 