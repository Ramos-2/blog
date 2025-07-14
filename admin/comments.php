<?php 
require 'auth_check.php'; 

// Charger les commentaires
$commentaires = file_exists("../page admin complet/commentaires.json") ? json_decode(file_get_contents("../page admin complet/commentaires.json"), true) : [];

// Suppression de commentaire
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $index = intval($_GET['delete']);
    if (isset($commentaires[$index])) {
        array_splice($commentaires, $index, 1);
        file_put_contents("../page admin complet/commentaires.json", json_encode($commentaires));
        header('Location: comments.php?success=1');
        exit;
    }
}

// Message de succès
$success = '';
if (isset($_GET['success'])) {
    $success = 'Commentaire supprimé avec succès !';
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

.comments-table {
    width: 100%;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.comments-table th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
    font-weight: bold;
    color: #333;
    border-bottom: 1px solid #dee2e6;
}

.comments-table td {
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.comments-table tr:hover {
    background: #f8f9fa;
}

.comment-author {
    font-weight: bold;
    color: #007bff;
}

.comment-message {
    color: #666;
    line-height: 1.5;
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

.no-comments {
    text-align: center;
    padding: 40px;
    color: #666;
    font-style: italic;
}

.comment-date {
    font-size: 12px;
    color: #999;
}
</style>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des commentaires - Admin</title>
    <link rel="stylesheet" href="../assets/styles/index.css">
</head>
<body>
    <div class="layout-with-sidebar">
        <?php 
        $sidebar_type = 'admin';
        include '../includes/sidebar.php'; 
        ?>
        
        <div class="main-content">
            <div class="admin-container">
    <div class="admin-header">
        <h1>Gestion des commentaires</h1>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="dashboard.php" class="back-btn">Retour au tableau de bord</a>
            <button onclick="logout()" style="background: #e74c3c; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-weight: bold;">Déconnexion</button>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="success-message"><?= $success ?></div>
    <?php endif; ?>

    <?php if (empty($commentaires)): ?>
        <div class="no-comments">
            <h3>Aucun commentaire</h3>
            <p>Il n'y a actuellement aucun commentaire à gérer.</p>
        </div>
    <?php else: ?>
        <table class="comments-table">
            <thead>
                <tr>
                    <th>Auteur</th>
                    <th>Message</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commentaires as $index => $commentaire): ?>
                    <tr>
                        <td>
                            <div class="comment-author"><?= htmlspecialchars($commentaire['auteur']) ?></div>
                            <div class="comment-date">Commentaire #<?= $index + 1 ?></div>
                        </td>
                        <td class="comment-message"><?= htmlspecialchars($commentaire['message']) ?></td>
                        <td>
                            <a href="comments.php?delete=<?= $index ?>" 
                               class="delete-btn" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')">
                                Supprimer
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

    </div>
</div>

<script>
function logout() {
    if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
        window.location.href = 'login.php?logout=1';
    }
}
</script>

</body>
</html>
