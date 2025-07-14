<?php 
require 'auth_check.php';
require '../includes/header.php'; 

// Charger les paramètres
$parametres = file_exists("../data/parametre.json") ? json_decode(file_get_contents("../data/parametre.json"), true) : [];

// Mise à jour des paramètres
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $parametres['titre'] = $_POST['titre'];
    $parametres['theme'] = $_POST['theme'];
    $parametres['langue'] = $_POST['langue'];
    $parametres['fuseau'] = $_POST['fuseau'];
    
    if (file_put_contents("../data/parametre.json", json_encode($parametres, JSON_PRETTY_PRINT))) {
        header('Location: settings.php?success=1');
        exit;
    }
}

// Messages de succès
$success = '';
if (isset($_GET['success'])) {
    $success = 'Paramètres mis à jour avec succès !';
}
?>

<style>
.admin-container {
    max-width: 800px;
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

.settings-form {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.settings-form h3 {
    margin-top: 0;
    color: #333;
    margin-bottom: 25px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #333;
}

.form-group input, .form-group select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group input:focus, .form-group select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.save-btn {
    background: #28a745;
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
}

.save-btn:hover {
    background: #218838;
}

.settings-section {
    margin-bottom: 30px;
}

.settings-section h4 {
    color: #007bff;
    margin-bottom: 15px;
    font-size: 1.1em;
}

.current-value {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
    margin-top: 5px;
    font-size: 14px;
    color: #666;
}

.current-value strong {
    color: #333;
}
</style>

<div class="admin-container">
    <div class="admin-header">
        <h1>Paramètres du blog</h1>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="dashboard.php" class="back-btn">Retour au tableau de bord</a>
            <button onclick="logout()" style="background: #e74c3c; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-weight: bold;">Déconnexion</button>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="success-message"><?= $success ?></div>
    <?php endif; ?>

    <div class="settings-form">
        <h3>Configuration générale</h3>
        <form method="post">
            <input type="hidden" name="action" value="update">
            
            <div class="settings-section">
                <h4>Informations du blog</h4>
                <div class="form-group">
                    <label for="titre">Titre du blog :</label>
                    <input type="text" id="titre" name="titre" 
                           value="<?= htmlspecialchars($parametres['titre'] ?? 'Mon blog') ?>" required>
                    <div class="current-value">
                        <strong>Valeur actuelle :</strong> <?= htmlspecialchars($parametres['titre'] ?? 'Mon blog') ?>
                    </div>
                </div>
            </div>

            <div class="settings-section">
                <h4>Apparence</h4>
                <div class="form-group">
                    <label for="theme">Thème :</label>
                    <select id="theme" name="theme">
                        <option value="clair" <?= ($parametres['theme'] ?? 'clair') === 'clair' ? 'selected' : '' ?>>Clair</option>
                        <option value="sombre" <?= ($parametres['theme'] ?? 'clair') === 'sombre' ? 'selected' : '' ?>>Sombre</option>
                        <option value="auto" <?= ($parametres['theme'] ?? 'clair') === 'auto' ? 'selected' : '' ?>>Automatique</option>
                    </select>
                    <div class="current-value">
                        <strong>Valeur actuelle :</strong> <?= htmlspecialchars($parametres['theme'] ?? 'clair') ?>
                    </div>
                </div>
            </div>

            <div class="settings-section">
                <h4>Paramètres régionaux</h4>
                <div class="form-group">
                    <label for="langue">Langue :</label>
                    <select id="langue" name="langue">
                        <option value="français" <?= ($parametres['langue'] ?? 'français') === 'français' ? 'selected' : '' ?>>Français</option>
                        <option value="english" <?= ($parametres['langue'] ?? 'français') === 'english' ? 'selected' : '' ?>>English</option>
                        <option value="español" <?= ($parametres['langue'] ?? 'français') === 'español' ? 'selected' : '' ?>>Español</option>
                        <option value="deutsch" <?= ($parametres['langue'] ?? 'français') === 'deutsch' ? 'selected' : '' ?>>Deutsch</option>
                    </select>
                    <div class="current-value">
                        <strong>Valeur actuelle :</strong> <?= htmlspecialchars($parametres['langue'] ?? 'français') ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="fuseau">Fuseau horaire :</label>
                    <select id="fuseau" name="fuseau">
                        <option value="UTC" <?= ($parametres['fuseau'] ?? 'UTC') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                        <option value="UTC+1" <?= ($parametres['fuseau'] ?? 'UTC') === 'UTC+1' ? 'selected' : '' ?>>UTC+1 (Europe centrale)</option>
                        <option value="UTC+2" <?= ($parametres['fuseau'] ?? 'UTC') === 'UTC+2' ? 'selected' : '' ?>>UTC+2 (Europe de l'Est)</option>
                        <option value="UTC-5" <?= ($parametres['fuseau'] ?? 'UTC') === 'UTC-5' ? 'selected' : '' ?>>UTC-5 (Est des États-Unis)</option>
                        <option value="UTC-8" <?= ($parametres['fuseau'] ?? 'UTC') === 'UTC-8' ? 'selected' : '' ?>>UTC-8 (Pacifique)</option>
                    </select>
                    <div class="current-value">
                        <strong>Valeur actuelle :</strong> <?= htmlspecialchars($parametres['fuseau'] ?? 'UTC') ?>
                    </div>
                </div>
            </div>

            <button type="submit" class="save-btn">Sauvegarder les paramètres</button>
        </form>
    </div>
</div>

<script>
function logout() {
    if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
        window.location.href = 'login.php?logout=1';
    }
}
</script>

<?php require '../includes/footer.php'; ?> 