<?php
// Démarrer la session seulement si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$passwordAdmin = 'victorien';
$error = '';

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Vérifier si l'utilisateur est connecté en tant qu'administrateur
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === $passwordAdmin) {
            $_SESSION['admin'] = true;
            $_SESSION['admin_login_time'] = time(); // Enregistrer le temps de connexion
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Mot de passe incorrect';
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8" />
        <title>Admin - Connexion</title>
        <link rel="stylesheet" href="../assets/styles/index.css" />
        <style>
            body {
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                margin: 0;
                padding: 20px;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            main {
                max-width: 400px;
                width: 100%;
                background: white;
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            }
            h2 {
                text-align: center;
                color: #333;
                margin-bottom: 30px;
                font-size: 2em;
            }
            .error {
                color: #e74c3c;
                text-align: center;
                margin-bottom: 20px;
                padding: 10px;
                background: #fdf2f2;
                border-radius: 5px;
                border: 1px solid #fecaca;
            }
            .session-expired {
                color: #f39c12;
                text-align: center;
                margin-bottom: 20px;
                padding: 10px;
                background: #fef9e7;
                border-radius: 5px;
                border: 1px solid #fdeaa7;
            }
            form {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }
            .form-group {
                position: relative;
            }
            input[type="password"] {
                width: 100%;
                padding: 15px;
                border: 2px solid #e1e8ed;
                border-radius: 8px;
                font-size: 16px;
                transition: border-color 0.3s ease;
                box-sizing: border-box;
            }
            input[type="password"]:focus {
                outline: none;
                border-color: #667eea;
            }
            button {
                padding: 15px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: bold;
                cursor: pointer;
                transition: transform 0.2s ease;
            }
            button:hover {
                transform: translateY(-2px);
            }
            .back-link {
                text-align: center;
                margin-top: 30px;
            }
            .back-link a {
                color: #667eea;
                text-decoration: none;
                font-weight: 500;
            }
            .back-link a:hover {
                text-decoration: underline;
            }
            .admin-icon {
                text-align: center;
                font-size: 3em;
                margin-bottom: 20px;
                color: #667eea;
            }
        </style>
    </head>
    <body>
        <main>
            <div class="admin-icon">🔐</div>
            <h2>Connexion administrateur</h2>
            
            <?php if ($error): ?>
                <div class="error">❌ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error']) && $_GET['error'] === 'session_expired'): ?>
                <div class="session-expired">⏰ Votre session a expiré. Veuillez vous reconnecter.</div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <input type="password" name="password" placeholder="Mot de passe administrateur" required />
                </div>
                <button type="submit">Se connecter</button>
            </form>
            <div class="back-link">
                <a href="../pages/index.php">← Retour au blog</a>
            </div>
        </main>
    </body>
    </html>

    <?php
    exit;
}
?> 