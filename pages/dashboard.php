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

// Connexion à la base de données pour récupérer les statistiques
$host = 'localhost';
$dbname = 'blog_forum';
$dbuser = 'root';
$dbpass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Compter les articles de l'utilisateur
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $nbArticles = $stmt->fetchColumn();
    
    // Compter les commentaires de l'utilisateur
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $nbComments = $stmt->fetchColumn();
    

    
    // Vérifier s'il y a des articles bloqués
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE user_id = ? AND status = 'blocked'");
    $stmt->execute([$user_id]);
    $blockedArticlesCount = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    $nbArticles = 0;
    $nbComments = 0;
    $blockedArticlesCount = 0;
}
?>


    <div class="layout-with-sidebar">
        <?php 
        $sidebar_type = 'user';
        include '../includes/sidebar.php'; 
        ?>
        
        <div class="main-content">
            <div class="dashboard-container">
                <!-- Message de succès -->
                <?php if (isset($_GET['success']) && $_GET['success'] === 'categories_updated'): ?>
                    <div class="success-message">
                        ✅ Vos catégories préférées ont été mises à jour avec succès !
                    </div>
                <?php endif; ?>
                
                <!-- Notification d'articles bloqués -->
                <?php if ($blockedArticlesCount > 0): ?>
                <div class="blocked-notification" id="blockedNotification">
                    <div class="notification-content">
                        <div class="notification-icon">⚠️</div>
                        <div class="notification-text">
                            <h3>Article(s) bloqué(s)</h3>
                            <p>Vous avez <?= $blockedArticlesCount ?> article(s) qui ont été bloqués par l'administrateur.</p>
                            <button class="btn-contact-admin" onclick="showContactForm()">Contacter l'administrateur</button>
                        </div>
                        <button class="notification-close" onclick="closeNotification()">×</button>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- En-tête du dashboard -->
                <div class="dashboard-header">
                    <h1>Bienvenue, <?= htmlspecialchars($username) ?> !</h1>
                    <p class="dashboard-subtitle">Voici un aperçu de votre activité</p>
                </div>

                <!-- Statistiques -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📝</div>
                        <div class="stat-number"><?= $nbArticles ?></div>
                        <div class="stat-label">Mes Articles</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">💬</div>
                        <div class="stat-number"><?= $nbComments ?></div>
                        <div class="stat-label">Mes Commentaires</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">👤</div>
                        <div class="stat-number"><?= $user_role === 'admin' ? 'Admin' : 'Membre' ?></div>
                        <div class="stat-label">Mon Rôle</div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="quick-actions">
                    <h2>Actions rapides</h2>
                    <div class="actions-grid">
                        <a href="profile.php" class="action-card">
                            <div class="action-icon">👤</div>
                            <div class="action-title">Modifier mon profil</div>
                            <div class="action-desc">Gérer mes informations personnelles</div>
                        </a>
                        <a href="../pages/personalized_home.php" class="action-card">
                            <div class="action-icon">🏠</div>
                            <div class="action-title">Mon accueil personnalisé</div>
                            <div class="action-desc">Voir les articles de mes catégories préférées</div>
                        </a>
                        <a href="../Categories/Inscription.php" class="action-card">
                            <div class="action-icon">⚙️</div>
                            <div class="action-title">Configurer mes catégories</div>
                            <div class="action-desc">Choisir mes centres d'intérêt</div>
                        </a>
                        <?php if ($user_role === 'admin'): ?>
                        <a href="../admin/dashboard.php" class="action-card">
                            <div class="action-icon">🔧</div>
                            <div class="action-title">Panel administrateur</div>
                            <div class="action-desc">Accéder aux outils d'administration</div>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>


            </div>
        </div>
    </div>

    <style>
    .dashboard-container {
        padding: 30px;
        max-width: 1200px;
        margin: 0 auto;
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

    .dashboard-header {
        text-align: center;
        margin-bottom: 40px;
        padding: 30px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .dashboard-header h1 {
        color: #2c3e50;
        margin: 0 0 10px 0;
        font-size: 2.5em;
    }

    .dashboard-subtitle {
        color: #7f8c8d;
        font-size: 1.1em;
        margin: 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        text-align: center;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        font-size: 2.5em;
        margin-bottom: 15px;
    }

    .stat-number {
        font-size: 2.5em;
        font-weight: bold;
        color: #3498db;
        margin-bottom: 10px;
    }

    .stat-label {
        color: #7f8c8d;
        font-size: 1.1em;
    }

    .quick-actions {
        margin-bottom: 40px;
    }

    .quick-actions h2 {
        color: #2c3e50;
        margin-bottom: 20px;
        font-size: 1.8em;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .action-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        text-decoration: none;
        color: inherit;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .action-card:hover {
        transform: translateY(-3px);
        border-color: #3498db;
        text-decoration: none;
        color: inherit;
    }

    .action-icon {
        font-size: 2em;
        margin-bottom: 15px;
    }

    .action-title {
        font-size: 1.2em;
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .action-desc {
        color: #7f8c8d;
        font-size: 0.9em;
    }



    @media (max-width: 768px) {
        .dashboard-container {
            padding: 15px;
        }
        
        .dashboard-header h1 {
            font-size: 2em;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .actions-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Styles pour la notification d'articles bloqués */
    .blocked-notification {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 8px;
        margin-bottom: 20px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .notification-content {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .notification-icon {
        font-size: 2em;
        flex-shrink: 0;
    }

    .notification-text {
        flex: 1;
    }

    .notification-text h3 {
        margin: 0 0 5px 0;
        color: #856404;
        font-size: 1.2em;
    }

    .notification-text p {
        margin: 0 0 10px 0;
        color: #856404;
    }

    .btn-contact-admin {
        background: #e67e22;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.9em;
        transition: background-color 0.3s ease;
    }

    .btn-contact-admin:hover {
        background: #d35400;
    }

    .notification-close {
        background: none;
        border: none;
        font-size: 1.5em;
        cursor: pointer;
        color: #856404;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .notification-close:hover {
        color: #d35400;
    }

    /* Modal de contact admin */
    .contact-modal {
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

    .contact-modal-content {
        background: white;
        padding: 30px;
        border-radius: 10px;
        max-width: 500px;
        width: 90%;
        position: relative;
    }

    .contact-modal h2 {
        margin: 0 0 20px 0;
        color: #2c3e50;
    }

    .contact-form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .contact-form textarea {
        width: 100%;
        min-height: 120px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        resize: vertical;
        font-family: inherit;
    }

    .contact-form-buttons {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .btn-cancel {
        background: #95a5a6;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-send {
        background: #27ae60;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-cancel:hover {
        background: #7f8c8d;
    }

    .btn-send:hover {
        background: #229954;
    }
    </style>

    <!-- Modal de contact admin -->
    <div class="contact-modal" id="contactModal">
        <div class="contact-modal-content">
            <h2>Contacter l'administrateur</h2>
            <form class="contact-form" id="contactForm">
                <div>
                    <label for="message">Votre message :</label>
                    <textarea id="message" name="message" placeholder="Expliquez pourquoi vous pensez que votre article ne devrait pas être bloqué..." required></textarea>
                </div>
                <div class="contact-form-buttons">
                    <button type="button" class="btn-cancel" onclick="closeContactForm()">Annuler</button>
                    <button type="submit" class="btn-send">Envoyer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function showContactForm() {
        document.getElementById('contactModal').style.display = 'flex';
    }

    function closeContactForm() {
        document.getElementById('contactModal').style.display = 'none';
    }

    function closeNotification() {
        document.getElementById('blockedNotification').style.display = 'none';
    }

    // Gestion du formulaire de contact
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = document.getElementById('message').value;
        const formData = new FormData();
        formData.append('message', message);
        
        // Envoyer le message à l'admin
        fetch('../actions/contact_admin.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                closeContactForm();
                closeNotification();
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => {
            alert('Erreur lors de l\'envoi du message');
        });
    });

    // Fermer la modal en cliquant à l'extérieur
    window.onclick = function(event) {
        const modal = document.getElementById('contactModal');
        if (event.target === modal) {
            closeContactForm();
        }
    }
    </script> 