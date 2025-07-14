<?php 
require 'auth_check.php';
require '../includes/header.php'; 

// Charger les catégories (simulation pour l'exemple)
$categories = [
    ['id' => 1, 'nom' => 'Technologie', 'description' => 'Articles sur les nouvelles technologies', 'nb_articles' => 5],
    ['id' => 2, 'nom' => 'Lifestyle', 'description' => 'Mode de vie et bien-être', 'nb_articles' => 3],
    ['id' => 3, 'nom' => 'Sport', 'description' => 'Actualités sportives', 'nb_articles' => 2],
    ['id' => 4, 'nom' => 'Culture', 'description' => 'Arts, cinéma, littérature', 'nb_articles' => 4],
    ['id' => 5, 'nom' => 'Actualités', 'description' => 'Informations générales', 'nb_articles' => 6]
];

// Suppression de catégorie
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $categories = array_filter($categories, function($cat) use ($id) {
        return $cat['id'] !== $id;
    });
    $categories = array_values($categories); // Réindexer
    header('Location: category.php?success=1');
    exit;
}

// Ajout de catégorie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $nouvelleCategorie = [
        'id' => max(array_column($categories, 'id')) + 1,
        'nom' => $_POST['nom'],
        'description' => $_POST['description'],
        'nb_articles' => 0
    ];
    $categories[] = $nouvelleCategorie;
    header('Location: category.php?success=2');
    exit;
}

// Messages de succès
$success = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case '1':
            $success = 'Catégorie supprimée avec succès !';
            break;
        case '2':
            $success = 'Catégorie ajoutée avec succès !';
            break;
    }
}
?>

<style>
.admin-container {
    max-width: 1000px;
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

.add-category-form {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.add-category-form h3 {
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

.form-group input, .form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    font-family: inherit;
}

.form-group textarea {
    min-height: 100px;
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

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.category-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.2s;
}

.category-card:hover {
    transform: translateY(-2px);
}

.category-header {
    background: #f8f9fa;
    padding: 15px;
    border-bottom: 1px solid #dee2e6;
}

.category-name {
    font-weight: bold;
    color: #007bff;
    margin: 0;
    font-size: 1.1em;
}

.category-id {
    font-size: 12px;
    color: #999;
    margin-top: 5px;
}

.category-body {
    padding: 15px;
}

.category-description {
    color: #666;
    line-height: 1.5;
    margin-bottom: 15px;
}

.category-stats {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.article-count {
    background: #e9ecef;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    color: #495057;
}

.category-actions {
    display: flex;
    gap: 5px;
}

.edit-btn {
    background: #ffc107;
    color: #212529;
    padding: 5px 10px;
    text-decoration: none;
    border-radius: 3px;
    font-size: 12px;
}

.edit-btn:hover {
    background: #e0a800;
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

.no-categories {
    text-align: center;
    padding: 40px;
    color: #666;
    font-style: italic;
}
</style>

<div class="admin-container">
    <div class="admin-header">
        <h1>Gestion des catégories</h1>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="dashboard.php" class="back-btn">Retour au tableau de bord</a>
            <button onclick="logout()" style="background: #e74c3c; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-weight: bold;">Déconnexion</button>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="success-message"><?= $success ?></div>
    <?php endif; ?>

    <!-- Formulaire d'ajout de catégorie -->
    <div class="add-category-form">
        <h3>Ajouter une nouvelle catégorie</h3>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="nom">Nom de la catégorie :</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            <div class="form-group">
                <label for="description">Description :</label>
                <textarea id="description" name="description" placeholder="Décrivez cette catégorie..." required></textarea>
            </div>
            <button type="submit" class="add-btn">Ajouter la catégorie</button>
        </form>
    </div>

    <?php if (empty($categories)): ?>
        <div class="no-categories">
            <h3>Aucune catégorie</h3>
            <p>Il n'y a actuellement aucune catégorie définie.</p>
        </div>
    <?php else: ?>
        <div class="categories-grid">
            <?php foreach ($categories as $categorie): ?>
                <div class="category-card">
                    <div class="category-header">
                        <div class="category-name"><?= htmlspecialchars($categorie['nom']) ?></div>
                        <div class="category-id">ID: <?= $categorie['id'] ?></div>
                    </div>
                    <div class="category-body">
                        <div class="category-description"><?= htmlspecialchars($categorie['description']) ?></div>
                        <div class="category-stats">
                            <span class="article-count"><?= $categorie['nb_articles'] ?> article(s)</span>
                        </div>
                        <div class="category-actions">
                            <a href="#" class="edit-btn">Modifier</a>
                            <a href="category.php?delete=<?= $categorie['id'] ?>" 
                               class="delete-btn" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')">
                                Supprimer
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
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
