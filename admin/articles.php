<?php 
require 'auth_check.php';
require '../includes/header.php'; 

// Charger les articles (simulation pour l'exemple)
$articles = [];

// Vérifier s'il existe des articles dans le dossier Articles
$articlesDir = "../Articles/";
if (is_dir($articlesDir)) {
    $articleFiles = array_diff(scandir($articlesDir), ['.', '..']);
    foreach ($articleFiles as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $articles[] = [
                'id' => count($articles) + 1,
                'title' => pathinfo($file, PATHINFO_FILENAME),
                'filename' => $file,
                'date' => date('Y-m-d', filemtime($articlesDir . $file))
            ];
        }
    }
}

// Suppression d'article
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $index = intval($_GET['delete']);
    if (isset($articles[$index])) {
        $filename = $articles[$index]['filename'];
        if (file_exists($articlesDir . $filename)) {
            unlink($articlesDir . $filename);
        }
        header('Location: articles.php?success=1');
        exit;
    }
}

// Ajout d'article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $titre = $_POST['titre'];
    $contenu = $_POST['contenu'];
    $categorie = $_POST['categorie'];
    
    // Créer le nom de fichier
    $filename = strtolower(str_replace(' ', '_', $titre)) . '.php';
    
    // Créer le contenu de l'article
    $articleContent = "<?php\n";
    $articleContent .= "// Article: " . $titre . "\n";
    $articleContent .= "// Catégorie: " . $categorie . "\n";
    $articleContent .= "// Date: " . date('Y-m-d H:i:s') . "\n";
    $articleContent .= "?>\n\n";
    $articleContent .= "<h1>" . htmlspecialchars($titre) . "</h1>\n";
    $articleContent .= "<p class='article-meta'>Catégorie: " . htmlspecialchars($categorie) . " | Date: " . date('d/m/Y') . "</p>\n";
    $articleContent .= "<div class='article-content'>\n";
    $articleContent .= nl2br(htmlspecialchars($contenu)) . "\n";
    $articleContent .= "</div>\n";
    
    // Sauvegarder le fichier
    if (file_put_contents($articlesDir . $filename, $articleContent)) {
        header('Location: articles.php?success=2');
        exit;
    }
}

// Messages de succès
$success = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case '1':
            $success = 'Article supprimé avec succès !';
            break;
        case '2':
            $success = 'Article créé avec succès !';
            break;
    }
}
?>

<style>
.admin-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #eee;
}

.admin-header h1 {
    color: #333;
    margin: 0;
}

.back-btn {
    background: #6c757d;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
}

.back-btn:hover {
    background: #5a6268;
}

.success-message {
    background: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    border: 1px solid #c3e6cb;
}

.add-article-form {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.add-article-form h3 {
    margin-top: 0;
    color: #333;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
}

.form-group input, .form-group select, .form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    font-family: inherit;
}

.form-group textarea {
    min-height: 200px;
    resize: vertical;
}

.add-btn {
    background: #28a745;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.add-btn:hover {
    background: #218838;
}

.articles-table {
    width: 100%;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.articles-table th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
    font-weight: bold;
    color: #333;
    border-bottom: 1px solid #dee2e6;
}

.articles-table td {
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.articles-table tr:hover {
    background: #f8f9fa;
}

.article-title {
    font-weight: bold;
    color: #007bff;
}

.article-date {
    font-size: 12px;
    color: #999;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.view-btn {
    background: #17a2b8;
    color: white;
    padding: 5px 10px;
    text-decoration: none;
    border-radius: 3px;
    font-size: 12px;
}

.view-btn:hover {
    background: #138496;
}

.delete-btn {
    background: #dc3545;
    color: white;
    padding: 5px 10px;
    text-decoration: none;
    border-radius: 3px;
    font-size: 12px;
}

.delete-btn:hover {
    background: #c82333;
}

.no-articles {
    text-align: center;
    padding: 40px;
    color: #666;
    font-style: italic;
}
</style>

<div class="admin-container">
    <div class="admin-header">
        <h1>Gestion des articles</h1>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="dashboard.php" class="back-btn">Retour au tableau de bord</a>
            <button onclick="logout()" style="background: #e74c3c; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-weight: bold;">Déconnexion</button>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="success-message"><?= $success ?></div>
    <?php endif; ?>

    <!-- Formulaire d'ajout d'article -->
    <div class="add-article-form">
        <h3>Créer un nouvel article</h3>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="titre">Titre de l'article :</label>
                <input type="text" id="titre" name="titre" required>
            </div>
            <div class="form-group">
                <label for="categorie">Catégorie :</label>
                <select id="categorie" name="categorie" required>
                    <option value="">Sélectionner une catégorie</option>
                    <option value="Technologie">Technologie</option>
                    <option value="Lifestyle">Lifestyle</option>
                    <option value="Sport">Sport</option>
                    <option value="Culture">Culture</option>
                    <option value="Actualités">Actualités</option>
                </select>
            </div>
            <div class="form-group">
                <label for="contenu">Contenu de l'article :</label>
                <textarea id="contenu" name="contenu" placeholder="Rédigez votre article ici..." required></textarea>
            </div>
            <button type="submit" class="add-btn">Publier l'article</button>
        </form>
    </div>

    <?php if (empty($articles)): ?>
        <div class="no-articles">
            <h3>Aucun article</h3>
            <p>Il n'y a actuellement aucun article publié.</p>
        </div>
    <?php else: ?>
        <table class="articles-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Date de création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $index => $article): ?>
                    <tr>
                        <td>
                            <div class="article-title"><?= htmlspecialchars($article['title']) ?></div>
                            <div class="article-date">ID: <?= $article['id'] ?></div>
                        </td>
                        <td><?= $article['date'] ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="../Articles/<?= urlencode($article['filename']) ?>" 
                                   class="view-btn" 
                                   target="_blank">
                                    Voir
                                </a>
                                <a href="articles.php?delete=<?= $index ?>" 
                                   class="delete-btn" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')">
                                    Supprimer
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
function logout() {
    if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
        window.location.href = 'login.php?logout=1';
    }
}
</script>

<?php require '../includes/footer.php'; ?>
