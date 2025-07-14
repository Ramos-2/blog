<?php
// Déterminer le type de sidebar (admin ou user)
$sidebar_type = $sidebar_type ?? 'user';
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Récupérer les informations utilisateur
$username = $_SESSION['username'] ?? 'Utilisateur';
$user_role = $_SESSION['role'] ?? 'user';
$user_id = $_SESSION['user_id'] ?? null;
?>

<style>
/* Variables CSS pour les couleurs */
:root {
    --sidebar-width: 250px;
    --sidebar-bg: #2c3e50;
    --sidebar-hover: #34495e;
    --sidebar-active: #3498db;
    --text-light: #ecf0f1;
    --text-muted: #bdc3c7;
    --border-color: #34495e;
}

/* Layout principal avec sidebar */
.layout-with-sidebar {
    display: flex;
    min-height: 100vh;
}

/* Sidebar */
.sidebar {
    width: var(--sidebar-width);
    background: var(--sidebar-bg);
    color: var(--text-light);
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    z-index: 1000;
    transition: transform 0.3s ease;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
    text-align: center;
}

.sidebar-logo {
    font-size: 1.5em;
    font-weight: bold;
    color: var(--text-light);
    text-decoration: none;
    display: block;
    margin-bottom: 10px;
}

.sidebar-user {
    font-size: 0.9em;
    color: var(--text-muted);
}

.sidebar-user strong {
    color: var(--text-light);
}

/* Navigation */
.sidebar-nav {
    padding: 20px 0;
}

.nav-section {
    margin-bottom: 30px;
}

.nav-section-title {
    padding: 0 20px 10px;
    font-size: 0.8em;
    text-transform: uppercase;
    color: var(--text-muted);
    font-weight: bold;
    letter-spacing: 1px;
}

.nav-item {
    display: block;
    padding: 12px 20px;
    color: var(--text-light);
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.nav-item:hover {
    background: var(--sidebar-hover);
    border-left-color: var(--sidebar-active);
    color: var(--text-light);
    text-decoration: none;
}

.nav-item.active {
    background: var(--sidebar-active);
    border-left-color: var(--text-light);
}

.nav-item i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

/* Contenu principal */
.main-content {
    flex: 1;
    margin-left: var(--sidebar-width);
    background: #f8f9fa;
    min-height: 100vh;
}

/* Bouton toggle pour mobile */
.sidebar-toggle {
    display: none;
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1001;
    background: var(--sidebar-bg);
    color: var(--text-light);
    border: none;
    padding: 10px;
    border-radius: 5px;
    cursor: pointer;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.open {
        transform: translateX(0);
    }
    
    .main-content {
        margin-left: 0;
    }
    
    .sidebar-toggle {
        display: block;
    }
}

/* Icônes FontAwesome simulées */
.icon {
    display: inline-block;
    width: 16px;
    height: 16px;
    margin-right: 10px;
    background-size: contain;
    background-repeat: no-repeat;
}

.icon-dashboard::before { content: "📊"; }
.icon-users::before { content: "👥"; }
.icon-articles::before { content: "📝"; }
.icon-comments::before { content: "💬"; }
.icon-categories::before { content: "📂"; }
.icon-settings::before { content: "⚙️"; }
.icon-profile::before { content: "👤"; }
.icon-logout::before { content: "🚪"; }
.icon-admin::before { content: "🔧"; }
</style>

<!-- Bouton toggle pour mobile -->
<button class="sidebar-toggle" onclick="toggleSidebar()">
    ☰
</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="<?= $sidebar_type === 'admin' ? '../admin/dashboard.php' : '../pages/dashboard.php' ?>" class="sidebar-logo">
            <?= $sidebar_type === 'admin' ? '🔧 Admin Panel' : '📊 Dashboard' ?>
        </a>
        <div class="sidebar-user">
            Connecté en tant que<br>
            <strong><?= htmlspecialchars($username) ?></strong>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php if ($sidebar_type === 'admin'): ?>
            <!-- Navigation Admin -->
            <div class="nav-section">
                <div class="nav-section-title">Gestion</div>
                <a href="../admin/dashboard.php" class="nav-item <?= $current_page === 'dashboard' ? 'active' : '' ?>">
                    <span class="icon icon-dashboard"></span>Tableau de bord
                </a>
                <a href="../admin/users.php" class="nav-item <?= $current_page === 'users' ? 'active' : '' ?>">
                    <span class="icon icon-users"></span>Utilisateurs
                </a>
                <a href="../admin/articles.php" class="nav-item <?= $current_page === 'articles' ? 'active' : '' ?>">
                    <span class="icon icon-articles"></span>Articles
                </a>
                <a href="../admin/comments.php" class="nav-item <?= $current_page === 'comments' ? 'active' : '' ?>">
                    <span class="icon icon-comments"></span>Commentaires
                </a>
                <a href="../admin/category.php" class="nav-item <?= $current_page === 'category' ? 'active' : '' ?>">
                    <span class="icon icon-categories"></span>Catégories
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Configuration</div>
                <a href="../admin/settings.php" class="nav-item <?= $current_page === 'settings' ? 'active' : '' ?>">
                    <span class="icon icon-settings"></span>Paramètres
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Navigation</div>
                <a href="../pages/index.php" class="nav-item">
                    <span class="icon icon-dashboard"></span>Voir le blog
                </a>
                <a href="../admin/login.php?logout=1" class="nav-item">
                    <span class="icon icon-logout"></span>Se déconnecter
                </a>
            </div>
            
        <?php else: ?>
            <!-- Navigation Utilisateur -->
            <div class="nav-section">
                <div class="nav-section-title">Mon Compte</div>
                <a href="../pages/dashboard.php" class="nav-item <?= $current_page === 'dashboard' ? 'active' : '' ?>">
                    <span class="icon icon-dashboard"></span>Tableau de bord
                </a>
                <a href="../pages/profile.php" class="nav-item <?= $current_page === 'profile' ? 'active' : '' ?>">
                    <span class="icon icon-profile"></span>Mon profil
                </a>
            </div>
            
            <?php if ($user_role === 'admin'): ?>
            <div class="nav-section">
                <div class="nav-section-title">Administration</div>
                <a href="../admin/dashboard.php" class="nav-item">
                    <span class="icon icon-admin"></span>Panel admin
                </a>
            </div>
            <?php endif; ?>
            
            <div class="nav-section">
                <div class="nav-section-title">Navigation</div>
                <a href="../pages/personalized_home.php" class="nav-item">
                    <span class="icon icon-dashboard"></span>Accueil
                </a>
                <a href="../pages/all_articles.php" class="nav-item <?= $current_page === 'all_articles' ? 'active' : '' ?>">
                    <span class="icon icon-articles"></span>Tous les articles
                </a>
                <a href="../actions/logout_action.php" class="nav-item">
                    <span class="icon icon-logout"></span>Se déconnecter
                </a>
            </div>
        <?php endif; ?>
    </nav>
</div>

<!-- Script pour le toggle mobile -->
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('open');
}

// Fermer la sidebar en cliquant à l'extérieur sur mobile
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
            sidebar.classList.remove('open');
        }
    }
});

// Fermer la sidebar lors du redimensionnement
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('sidebar');
    if (window.innerWidth > 768) {
        sidebar.classList.remove('open');
    }
});
</script> 