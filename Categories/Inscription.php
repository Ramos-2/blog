<?php
require '../includes/header.php';

// Traitement du formulaire de sélection des catégories
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['categories'])) {
    $user_id = $_SESSION['user_id'] ?? null;
    
    if ($user_id) {
        // Connexion à la base de données
        $host = 'localhost';
        $dbname = 'blog_forum';
        $dbuser = 'root';
        $dbpass = '';
        
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $dbuser, $dbpass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Supprimer les anciennes préférences
            $stmt = $pdo->prepare("DELETE FROM user_categories WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Insérer les nouvelles catégories sélectionnées
            $stmt = $pdo->prepare("INSERT INTO user_categories (user_id, category_name) VALUES (?, ?)");
            
            foreach ($_POST['categories'] as $category) {
                $stmt->execute([$user_id, $category]);
            }
            // Placer un cookie pour retenir le choix des catégories
            setcookie('categories_selected', '1', time() + 365*24*60*60, '/');
            // Rediriger vers le dashboard
            header('Location: ../pages/dashboard.php?success=categories_updated');
            exit();
            
        } catch (PDOException $e) {
            $error = "Erreur lors de la sauvegarde des catégories: " . $e->getMessage();
        }
    } else {
        $error = "Session utilisateur non trouvée";
    }
}

// Récupérer les catégories actuelles de l'utilisateur
$user_categories = [];
if (isset($_SESSION['user_id'])) {
    $host = 'localhost';
    $dbname = 'blog_forum';
    $dbuser = 'root';
    $dbpass = '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $dbuser, $dbpass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("SELECT category_name FROM user_categories WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
    } catch (PDOException $e) {
        // Erreur silencieuse
    }
}
?>


  <style>
    .categories-container {
        max-width: 600px;
        margin: 50px auto;
        padding: 30px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .category-option {
        display: flex;
        align-items: center;
        padding: 15px;
        margin: 10px 0;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .category-option:hover {
        border-color: #3498db;
        background: #f8f9fa;
    }
    
    .category-option input[type="checkbox"] {
        margin-right: 15px;
        transform: scale(1.2);
    }
    
    .category-option label {
        cursor: pointer;
        font-size: 1.1em;
        font-weight: 500;
        color: #2c3e50;
    }
    
    .submit-btn {
        background: #3498db;
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 8px;
        font-size: 1.1em;
        cursor: pointer;
        transition: background 0.3s ease;
        margin-top: 20px;
    }
    
    .submit-btn:hover {
        background: #2980b9;
    }
    
    .error-message {
        background: #e74c3c;
        color: white;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <div class="categories-container">
    <h2>Choisissez vos centres d'intérêt</h2>
    <p>Sélectionnez les catégories qui vous intéressent. Vous verrez uniquement les articles de ces catégories sur votre page d'accueil personnalisée.</p>
    
    <?php if (isset($error)): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="category-option">
            <input type="checkbox" name="categories[]" value="sport" id="sport" 
                   <?= in_array('sport', $user_categories) ? 'checked' : '' ?>>
            <label for="sport">🏃‍♂️ Sport</label>
        </div>

        <div class="category-option">
            <input type="checkbox" name="categories[]" value="voyage" id="voyage"
                   <?= in_array('voyage', $user_categories) ? 'checked' : '' ?>>
            <label for="voyage">✈️ Voyage</label>
        </div>

        <div class="category-option">
            <input type="checkbox" name="categories[]" value="nutrition" id="nutrition"
                   <?= in_array('nutrition', $user_categories) ? 'checked' : '' ?>>
            <label for="nutrition">🥗 Nutrition</label>
        </div>

        <div class="category-option">
            <input type="checkbox" name="categories[]" value="informatique" id="informatique"
                   <?= in_array('informatique', $user_categories) ? 'checked' : '' ?>>
            <label for="informatique">💻 Informatique</label>
        </div>

        <div class="category-option">
            <input type="checkbox" name="categories[]" value="ia" id="ia"
                   <?= in_array('ia', $user_categories) ? 'checked' : '' ?>>
            <label for="ia">🤖 Intelligence Artificielle</label>
        </div>

        <button type="submit" class="submit-btn">Sauvegarder mes préférences</button>
    </form>
  </div>
