<?php 
require 'auth_check.php'; 

// Charger les utilisateurs
$utilisateurs = file_exists("../page admin complet/utilisateurs.json") ? json_decode(file_get_contents("../page admin complet/utilisateurs.json"), true) : [];

// Suppression d'utilisateur
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $index = intval($_GET['delete']);
    if (isset($utilisateurs[$index])) {
        array_splice($utilisateurs, $index, 1);
        file_put_contents("../page admin complet/utilisateurs.json", json_encode($utilisateurs));
        header('Location: users.php?success=1');
        exit;
    }
}

// Ajout d'utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $nouvelUtilisateur = [
        'nom' => $_POST['nom'],
        'role' => $_POST['role']
    ];
    $utilisateurs[] = $nouvelUtilisateur;
    file_put_contents("../page admin complet/utilisateurs.json", json_encode($utilisateurs));
    header('Location: users.php?success=2');
    exit;
}

// Messages de succès
$success = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case '1':
            $success = 'Utilisateur supprimé avec succès !';
            break;
        case '2':
            $success = 'Utilisateur ajouté avec succès !';
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

.add-user-form {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.add-user-form h3 {
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

.form-group input, .form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
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

.users-table {
    width: 100%;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.users-table th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
    font-weight: bold;
    color: #333;
    border-bottom: 1px solid #dee2e6;
}

.users-table td {
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.users-table tr:hover {
    background: #f8f9fa;
}

.user-name {
    font-weight: bold;
    color: #007bff;
}

.user-role {
    background: #e9ecef;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    color: #495057;
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

.no-users {
    text-align: center;
    padding: 40px;
    color: #666;
    font-style: italic;
}
</style>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs - Admin</title>
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
        <h1>Gestion des utilisateurs</h1>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="dashboard.php" class="back-btn">Retour au tableau de bord</a>
            <button onclick="logout()" style="background: #e74c3c; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-weight: bold;">Déconnexion</button>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="success-message"><?= $success ?></div>
    <?php endif; ?>

    <!-- Formulaire d'ajout d'utilisateur -->
    <div class="add-user-form">
        <h3>Ajouter un nouvel utilisateur</h3>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="nom">Nom d'utilisateur :</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            <div class="form-group">
                <label for="role">Rôle :</label>
                <select id="role" name="role" required>
                    <option value="">Sélectionner un rôle</option>
                    <option value="Administrateur">Administrateur</option>
                    <option value="Rédacteur">Rédacteur</option>
                    <option value="Modérateur">Modérateur</option>
                    <option value="Utilisateur">Utilisateur</option>
                </select>
            </div>
            <button type="submit" class="add-btn">Ajouter l'utilisateur</button>
        </form>
    </div>

    <?php if (empty($utilisateurs)): ?>
        <div class="no-users">
            <h3>Aucun utilisateur</h3>
            <p>Il n'y a actuellement aucun utilisateur enregistré.</p>
        </div>
    <?php else: ?>
        <table class="users-table">
            <thead>
                <tr>
                    <th>Nom d'utilisateur</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilisateurs as $index => $utilisateur): ?>
                    <tr>
                        <td>
                            <div class="user-name"><?= htmlspecialchars($utilisateur['nom']) ?></div>
                        </td>
                        <td>
                            <span class="user-role"><?= htmlspecialchars($utilisateur['role']) ?></span>
                        </td>
                        <td>
                            <a href="users.php?delete=<?= $index ?>" 
                               class="delete-btn" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
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
