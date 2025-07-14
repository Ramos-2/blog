<?php 
require '../includes/header.php';

// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=session_expired');
    exit();
}

// Récupération des infos utilisateur
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Utilisateur';
$user_role = $_SESSION['role'] ?? 'user';
$email = $_SESSION['email'] ?? '';

// Connexion à la base de données
$host = 'localhost';
$dbname = 'blog_forum';
$dbuser = 'root';
$dbpass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer les informations complètes de l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Traitement de la mise à jour du profil
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'update_profile') {
            $nom = trim($_POST['nom']);
            $prenom = trim($_POST['prenom']);
            $new_email = trim($_POST['email']);
            
            // Validation
            if (empty($nom) || empty($prenom) || empty($new_email)) {
                $error = "Tous les champs sont obligatoires.";
            } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                $error = "L'adresse email n'est pas valide.";
            } else {
                // Vérifier si l'email existe déjà (sauf pour l'utilisateur actuel)
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$new_email, $user_id]);
                if ($stmt->fetch()) {
                    $error = "Cette adresse email est déjà utilisée.";
                } else {
                    // Mise à jour du profil
                    $stmt = $pdo->prepare("UPDATE users SET nom = ?, prenom = ?, email = ? WHERE id = ?");
                    if ($stmt->execute([$nom, $prenom, $new_email, $user_id])) {
                        // Mettre à jour la session
                        $_SESSION['email'] = $new_email;
                        $success = "Profil mis à jour avec succès !";
                        
                        // Recharger les données utilisateur
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    } else {
                        $error = "Erreur lors de la mise à jour du profil.";
                    }
                }
            }
        } elseif ($_POST['action'] === 'change_password') {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Validation
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $error = "Tous les champs sont obligatoires.";
            } elseif ($new_password !== $confirm_password) {
                $error = "Les nouveaux mots de passe ne correspondent pas.";
            } elseif (strlen($new_password) < 6) {
                $error = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
            } elseif (!password_verify($current_password, $user['password'])) {
                $error = "Le mot de passe actuel est incorrect.";
            } else {
                // Mise à jour du mot de passe
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($stmt->execute([$hashed_password, $user_id])) {
                    $success = "Mot de passe modifié avec succès !";
                } else {
                    $error = "Erreur lors de la modification du mot de passe.";
                }
            }
        }
    }
    
} catch (PDOException $e) {
    $error = "Erreur de connexion à la base de données.";
    $user = null;
}
?>


    <div class="layout-with-sidebar">
        <?php 
        $sidebar_type = 'user';
        include '../includes/sidebar.php'; 
        ?>
        
        <div class="main-content">
            <div class="profile-container">
                <!-- En-tête du profil -->
                <div class="profile-header">
                    <h1>Mon Profil</h1>
                    <p class="profile-subtitle">Gérez vos informations personnelles</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if ($user): ?>
                <div class="profile-sections">
                    <!-- Informations personnelles -->
                    <div class="profile-section">
                        <h2>Informations personnelles</h2>
                        <form method="post" class="profile-form">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="form-group">
                                <label for="nom">Nom :</label>
                                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="prenom">Prénom :</label>
                                <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email :</label>
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="username">Nom d'utilisateur :</label>
                                <input type="text" id="username" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                                <small>Le nom d'utilisateur ne peut pas être modifié</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="role">Rôle :</label>
                                <input type="text" id="role" value="<?= htmlspecialchars($user['role']) ?>" readonly>
                            </div>
                            
                            <button type="submit" class="btn-primary">Mettre à jour le profil</button>
                        </form>
                    </div>

                    <!-- Changement de mot de passe -->
                    <div class="profile-section">
                        <h2>Changer le mot de passe</h2>
                        <form method="post" class="profile-form">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="form-group">
                                <label for="current_password">Mot de passe actuel :</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">Nouveau mot de passe :</label>
                                <input type="password" id="new_password" name="new_password" required>
                                <small>Minimum 6 caractères</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" class="btn-secondary">Changer le mot de passe</button>
                        </form>
                    </div>


                </div>
                <?php else: ?>
                    <div class="alert alert-error">Impossible de charger les informations du profil.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
    .profile-container {
        padding: 30px;
        max-width: 800px;
        margin: 0 auto;
    }

    .profile-header {
        text-align: center;
        margin-bottom: 40px;
        padding: 30px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .profile-header h1 {
        color: #2c3e50;
        margin: 0 0 10px 0;
        font-size: 2.5em;
    }

    .profile-subtitle {
        color: #7f8c8d;
        font-size: 1.1em;
        margin: 0;
    }

    .alert {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        font-weight: bold;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .profile-sections {
        display: grid;
        gap: 30px;
    }

    .profile-section {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .profile-section h2 {
        color: #2c3e50;
        margin-top: 0;
        margin-bottom: 25px;
        padding-bottom: 10px;
        border-bottom: 2px solid #ecf0f1;
        font-size: 1.5em;
    }

    .profile-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group label {
        font-weight: bold;
        color: #2c3e50;
        font-size: 0.95em;
    }

    .form-group input {
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        transition: border-color 0.3s ease;
    }

    .form-group input:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 2px rgba(52,152,219,0.2);
    }

    .form-group input[readonly] {
        background-color: #f8f9fa;
        color: #6c757d;
    }

    .form-group small {
        color: #6c757d;
        font-size: 0.85em;
        margin-top: 5px;
    }

    .btn-primary, .btn-secondary {
        padding: 12px 24px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: #3498db;
        color: white;
    }

    .btn-primary:hover {
        background: #2980b9;
    }

    .btn-secondary {
        background: #95a5a6;
        color: white;
    }

    .btn-secondary:hover {
        background: #7f8c8d;
    }



    @media (max-width: 768px) {
        .profile-container {
            padding: 15px;
        }
        
        .profile-header h1 {
            font-size: 2em;
        }
    }
    </style>
 