<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['auth']) && isset($_COOKIE['remember_token'])) {
    $host = 'localhost';
    $dbname = 'blog_forum';
    $dbuser = 'root';
    $dbpass = '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        
        $stmt = $pdo->prepare("SELECT u.* FROM users u 
                              JOIN remember_tokens rt ON u.id = rt.user_id 
                              WHERE rt.token = ? AND rt.expires_at > NOW()");
        $stmt->execute([$_COOKIE['remember_token']]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['auth'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
        } else {
            
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
    } catch(PDOException $e) {
        
        setcookie('remember_token', '', time() - 3600, '/');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark position-relative">
    <div class="container">
        <a class="navbar-brand" href="index.php">Forum</a>
        <div class="d-flex align-items-center ms-auto">
            <span id="clock" class="text-light me-3" style="font-weight:bold;"></span>
            <button id="theme-toggle" class="btn btn-outline-light btn-sm me-3" title="Changer de thème" style="border-radius:50%;width:38px;height:38px;display:flex;align-items:center;justify-content:center;">
                <span id="theme-icon">🌙</span>
            </button>
            <?php if(isset($_SESSION['auth'])): ?>
                <a class="btn btn-danger" href="actions/logout_action.php" style="font-weight:bold;">Déconnexion</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
// Horloge temps réel
function updateClock() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    const s = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('clock').textContent = `${h}:${m}:${s}`;
}
setInterval(updateClock, 1000);
updateClock();

// Thème sombre/clair
function setTheme(theme) {
    if (theme === 'dark') {
        document.body.classList.add('dark-theme');
        document.getElementById('theme-icon').textContent = '☀️';
        localStorage.setItem('theme', 'dark');
    } else {
        document.body.classList.remove('dark-theme');
        document.getElementById('theme-icon').textContent = '🌙';
        localStorage.setItem('theme', 'light');
    }
}

// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.onclick = function() {
            const isDark = document.body.classList.contains('dark-theme');
            setTheme(isDark ? 'light' : 'dark');
        };
    }
});

// Appliquer le thème au chargement
document.addEventListener('DOMContentLoaded', function() {
    const saved = localStorage.getItem('theme');
    if (saved === 'dark') setTheme('dark');
    else setTheme('light');
});
</script>

<style>
/* Thème sombre global */
.dark-theme, .dark-theme body {
    background: #181a1b !important;
    color: #f1f1f1 !important;
}

/* Navigation */
.dark-theme .navbar, .dark-theme .navbar * {
    background: #23272b !important;
    color: #f1f1f1 !important;
}

/* Cartes et conteneurs */
.dark-theme .card, .dark-theme .modal-content, .dark-theme .settings-form,
.dark-theme .dashboard-container, .dark-theme .profile-container,
.dark-theme .article-form-container, .dark-theme .home-container,
.dark-theme .all-articles-container, .dark-theme .categories-container {
    background: #23272b !important;
    color: #f1f1f1 !important;
    border-color: #444 !important;
}

/* Formulaires */
.dark-theme .form-control, .dark-theme input, .dark-theme select, .dark-theme textarea {
    background: #23272b !important;
    color: #f1f1f1 !important;
    border-color: #444 !important;
}

.dark-theme .form-control:focus, .dark-theme input:focus, .dark-theme select:focus, .dark-theme textarea:focus {
    background: #2c3e50 !important;
    color: #f1f1f1 !important;
    border-color: #3498db !important;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25) !important;
}

/* Boutons */
.dark-theme .btn, .dark-theme .btn-primary, .dark-theme .btn-outline-secondary {
    background: #444 !important;
    color: #f1f1f1 !important;
    border-color: #444 !important;
}

.dark-theme .btn-outline-light {
    background: #23272b !important;
    color: #f1f1f1 !important;
    border-color: #f1f1f1 !important;
}

.dark-theme .btn:hover {
    background: #555 !important;
    color: #f1f1f1 !important;
}

/* Alertes */
.dark-theme .alert {
    background: #23272b !important;
    color: #f1f1f1 !important;
    border-color: #444 !important;
}

/* Articles */
.dark-theme .article-card, .dark-theme .stat-card, .dark-theme .action-card {
    background: #23272b !important;
    color: #f1f1f1 !important;
    border-color: #444 !important;
}

.dark-theme .article-card:hover, .dark-theme .stat-card:hover, .dark-theme .action-card:hover {
    background: #2c3e50 !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3) !important;
}

/* En-têtes et titres */
.dark-theme .dashboard-header, .dark-theme .profile-header, .dark-theme .home-header,
.dark-theme .page-header, .dark-theme .form-header {
    background: #2c3e50 !important;
    color: #f1f1f1 !important;
}

/* Titres avec couleurs opposées */
.dark-theme h1, .dark-theme h2, .dark-theme h3, .dark-theme h4, .dark-theme h5, .dark-theme h6 {
    color: #f1f1f1 !important;
}

.dark-theme .dashboard-header h1, .dark-theme .profile-header h1, .dark-theme .home-header h1,
.dark-theme .page-header h1, .dark-theme .form-header h1 {
    color: #f1f1f1 !important;
}

.dark-theme .dashboard-subtitle, .dark-theme .profile-subtitle, .dark-theme .home-subtitle,
.dark-theme .page-subtitle, .dark-theme .form-subtitle {
    color: #bdc3c7 !important;
}

.dark-theme .article-title a {
    color: #3498db !important;
}

.dark-theme .article-title a:hover {
    color: #5dade2 !important;
}

.dark-theme .stat-label, .dark-theme .action-title {
    color: #bdc3c7 !important;
}

.dark-theme .stat-number {
    color: #3498db !important;
}

/* Sidebar */
.dark-theme .sidebar {
    background: #23272b !important;
    color: #f1f1f1 !important;
    border-color: #444 !important;
}

.dark-theme .sidebar a {
    color: #f1f1f1 !important;
}

.dark-theme .sidebar a:hover {
    background: #2c3e50 !important;
    color: #3498db !important;
}

/* Tableaux */
.dark-theme table {
    background: #23272b !important;
    color: #f1f1f1 !important;
}

.dark-theme th {
    background: #2c3e50 !important;
    color: #f1f1f1 !important;
    border-color: #444 !important;
}

.dark-theme td {
    border-color: #444 !important;
}

.dark-theme tr:nth-child(even) {
    background: #2c3e50 !important;
}

.dark-theme tr:hover {
    background: #34495e !important;
}

/* Modales */
.dark-theme .modal-content {
    background: #23272b !important;
    color: #f1f1f1 !important;
    border-color: #444 !important;
}

.dark-theme .modal-header, .dark-theme .modal-footer {
    border-color: #444 !important;
}

/* Liens */
.dark-theme a {
    color: #3498db !important;
}

.dark-theme a:hover {
    color: #5dade2 !important;
}

/* Images et médias */
.dark-theme .article-image img {
    border-color: #444 !important;
}

/* Messages et notifications */
.dark-theme .success-message, .dark-theme .error-message {
    background: #23272b !important;
    color: #f1f1f1 !important;
    border-color: #444 !important;
}

/* Sections spéciales */
.dark-theme .stats-grid, .dark-theme .actions-grid, .dark-theme .articles-grid {
    background: transparent !important;
}

.dark-theme .quick-actions, .dark-theme .articles-section {
    background: transparent !important;
}

/* Boutons d'action spécifiques */
.dark-theme .btn-submit, .dark-theme .btn-preview, .dark-theme .btn-cancel {
    background: #444 !important;
    color: #f1f1f1 !important;
    border-color: #444 !important;
}

.dark-theme .btn-submit:hover {
    background: #27ae60 !important;
}

.dark-theme .btn-preview:hover {
    background: #3498db !important;
}

.dark-theme .btn-cancel:hover {
    background: #95a5a6 !important;
}

/* Commentaires */
.dark-theme .comments-section {
    background: #2c3e50 !important;
    border-color: #444 !important;
}

.dark-theme .comment-input {
    background: #23272b !important;
    color: #f1f1f1 !important;
    border-color: #444 !important;
}

/* Filtres et sélecteurs */
.dark-theme .category-filter-section, .dark-theme .filter-form {
    background: transparent !important;
}

.dark-theme .category-option {
    background: #23272b !important;
    border-color: #444 !important;
    color: #f1f1f1 !important;
}

.dark-theme .category-option:hover {
    background: #2c3e50 !important;
    border-color: #3498db !important;
}

/* Scrollbar personnalisée pour le thème sombre */
.dark-theme ::-webkit-scrollbar {
    width: 8px;
}

.dark-theme ::-webkit-scrollbar-track {
    background: #23272b;
}

.dark-theme ::-webkit-scrollbar-thumb {
    background: #444;
    border-radius: 4px;
}

.dark-theme ::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Thème clair - Titres avec couleurs opposées */
body:not(.dark-theme) h1, body:not(.dark-theme) h2, body:not(.dark-theme) h3, 
body:not(.dark-theme) h4, body:not(.dark-theme) h5, body:not(.dark-theme) h6 {
    color: #2c3e50 !important;
}

body:not(.dark-theme) .dashboard-header h1, body:not(.dark-theme) .profile-header h1, 
body:not(.dark-theme) .home-header h1, body:not(.dark-theme) .page-header h1, 
body:not(.dark-theme) .form-header h1 {
    color: #2c3e50 !important;
}

body:not(.dark-theme) .dashboard-subtitle, body:not(.dark-theme) .profile-subtitle, 
body:not(.dark-theme) .home-subtitle, body:not(.dark-theme) .page-subtitle, 
body:not(.dark-theme) .form-subtitle {
    color: #7f8c8d !important;
}

body:not(.dark-theme) .article-title a {
    color: #2c3e50 !important;
}

body:not(.dark-theme) .article-title a:hover {
    color: #3498db !important;
}

body:not(.dark-theme) .stat-label, body:not(.dark-theme) .action-title {
    color: #7f8c8d !important;
}

body:not(.dark-theme) .stat-number {
    color: #2c3e50 !important;
}
</style>
