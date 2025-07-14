<?php
require '../includes/header.php';

// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php?error=session_expired');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Utilisateur';

// Connexion à la base de données
$host = 'localhost';
$dbname = 'blog_forum';
$dbuser = 'root';
$dbpass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer les catégories
    $categories = $pdo->query("SELECT id, nom FROM categories ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
    
    // Traitement de l'ajout d'article
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_article') {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 0);
        
        // Validation
        $errors = [];
        if (empty($title)) $errors[] = "Le titre est obligatoire";
        if (empty($content)) $errors[] = "Le contenu est obligatoire";
        if (empty($excerpt)) $errors[] = "L'extrait est obligatoire";
        if ($category_id <= 0) $errors[] = "Veuillez sélectionner une catégorie";
        
        if (empty($errors)) {
            // Générer un slug unique
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            $slug = preg_replace('/-+/', '-', $slug);
            $slug = trim($slug, '-');
            
            // Vérifier si le slug existe déjà
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetchColumn() > 0) {
                $slug .= '-' . time();
            }
            
            // Traitement de l'image
            $image_url = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($file_extension, $allowed_extensions)) {
                    $filename = 'img_' . uniqid() . '.' . $file_extension;
                    $filepath = $upload_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
                        $image_url = 'uploads/' . $filename;
                    }
                }
            }
            
            // Insérer l'article
            $stmt = $pdo->prepare("
                INSERT INTO articles (title, content, excerpt, slug, user_id, category_id, image_url, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'published', NOW())
            ");
            
            if ($stmt->execute([$title, $content, $excerpt, $slug, $user_id, $category_id, $image_url])) {
                $success_message = "Article publié avec succès !";
                // Réinitialiser le formulaire
                $_POST = [];
            } else {
                $errors[] = "Erreur lors de la publication de l'article";
            }
        }
    }
    
} catch (PDOException $e) {
    $errors[] = "Erreur de connexion à la base de données";
    $categories = [];
}
?>


    <style>
        .article-form-container {
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-header h1 {
            color: #2c3e50;
            margin: 0 0 10px 0;
            font-size: 2.5em;
        }
        
        .form-subtitle {
            color: #7f8c8d;
            font-size: 1.1em;
            margin: 0;
        }
        
        .article-form {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .form-group textarea[name="content"] {
            min-height: 300px;
        }
        
        .form-group textarea[name="excerpt"] {
            min-height: 100px;
        }
        
        .image-preview {
            margin-top: 10px;
            max-width: 300px;
            border-radius: 8px;
            overflow: hidden;
            display: none;
        }
        
        .image-preview img {
            width: 100%;
            height: auto;
        }
        
        .form-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn-submit {
            background: #27ae60;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            background: #229954;
            transform: translateY(-2px);
        }
        
        .btn-preview {
            background: #3498db;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-preview:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-cancel {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
            text-decoration: none;
            color: white;
        }
        
        .success-message {
            background: #27ae60;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        
        .error-message {
            background: #e74c3c;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        
        .error-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .error-list li {
            margin-bottom: 5px;
        }
        
        .character-count {
            font-size: 12px;
            color: #7f8c8d;
            text-align: right;
            margin-top: 5px;
        }
        
        .preview-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .preview-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 800px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        }
        
        .preview-close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #7f8c8d;
        }
        
        .preview-close:hover {
            color: #e74c3c;
        }
        
        @media (max-width: 768px) {
            .article-form-container {
                padding: 15px;
            }
            
            .form-header h1 {
                font-size: 2em;
            }
            
            .article-form {
                padding: 20px;
            }
            
            .form-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="layout-with-sidebar">
        <?php 
        $sidebar_type = 'user';
        include '../includes/sidebar.php'; 
        ?>
        
        <div class="main-content">
            <div class="article-form-container">
                <!-- En-tête -->
                <div class="form-header">
                    <h1>✏️ Créer un nouvel article</h1>
                    <p class="form-subtitle">Partagez vos idées avec la communauté</p>
                </div>

                <!-- Messages -->
                <?php if (isset($success_message)): ?>
                    <div class="success-message">
                        ✅ <?= htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <ul class="error-list">
                            <?php foreach ($errors as $error): ?>
                                <li>❌ <?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Formulaire -->
                <form class="article-form" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_article">
                    
                    <div class="form-group">
                        <label for="title">Titre de l'article *</label>
                        <input type="text" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required maxlength="255">
                       
                    </div>

                    <div class="form-group">
                        <label for="category_id">Catégorie *</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Sélectionner une catégorie</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                   

                    <div class="form-group">
                        <label for="content">Contenu de l'article *</label>
                        <textarea id="content" name="content" placeholder="Rédigez votre article ici..." required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image">Image de l'article (optionnel)</label>
                        <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                        <div class="image-preview" id="image-preview">
                            <img id="preview-img" src="" alt="Aperçu">
                        </div>
                    </div>

                    <div class="form-buttons">
                        <button type="button" class="btn-preview" onclick="previewArticle()">👁️ Aperçu</button>
                        <button type="submit" class="btn-submit">📝 Publier l'article</button>
                        <a href="../pages/dashboard.php" class="btn-cancel">❌ Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal d'aperçu -->
    <div class="preview-modal" id="preview-modal">
        <div class="preview-content">
            <span class="preview-close" onclick="closePreview()">&times;</span>
            <div id="preview-content">
                <!-- Le contenu de l'aperçu sera inséré ici -->
            </div>
        </div>
    </div>

    <script>
    // Compteurs de caractères
    function updateCharacterCount(elementId, maxLength) {
        const element = document.getElementById(elementId);
        const countElement = document.getElementById(elementId + '-count');
        const currentLength = element.value.length;
        countElement.textContent = currentLength;
        
        if (currentLength > maxLength * 0.9) {
            countElement.style.color = '#e74c3c';
        } else {
            countElement.style.color = '#7f8c8d';
        }
    }

    document.getElementById('title').addEventListener('input', function() {
        updateCharacterCount('title', 255);
    });

    document.getElementById('excerpt').addEventListener('input', function() {
        updateCharacterCount('excerpt', 500);
    });

    // Prévisualisation d'image
    function previewImage(input) {
        const preview = document.getElementById('image-preview');
        const previewImg = document.getElementById('preview-img');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.style.display = 'none';
        }
    }

    // Aperçu de l'article
    function previewArticle() {
        const title = document.getElementById('title').value;
        const excerpt = document.getElementById('excerpt').value;
        const content = document.getElementById('content').value;
        const categorySelect = document.getElementById('category_id');
        const category = categorySelect.options[categorySelect.selectedIndex].text;
        
        if (!title || !excerpt || !content) {
            alert('Veuillez remplir tous les champs obligatoires avant de prévisualiser.');
            return;
        }
        
        const previewContent = `
            <h1>${title}</h1>
            <div style="margin-bottom: 20px; color: #7f8c8d;">
                <strong>Catégorie :</strong> ${category}
            </div>
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; font-style: italic;">
                <strong>Extrait :</strong> ${excerpt}
            </div>
            <div style="line-height: 1.6;">
                ${content.replace(/\n/g, '<br>')}
            </div>
        `;
        
        document.getElementById('preview-content').innerHTML = previewContent;
        document.getElementById('preview-modal').style.display = 'flex';
    }

    function closePreview() {
        document.getElementById('preview-modal').style.display = 'none';
    }

    // Fermer la modal en cliquant à l'extérieur
    window.onclick = function(event) {
        const modal = document.getElementById('preview-modal');
        if (event.target === modal) {
            closePreview();
        }
    }

   
    </script> 