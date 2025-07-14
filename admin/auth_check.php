<?php
// Démarrer la session seulement si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté en tant qu'administrateur
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    // Rediriger vers la page de connexion admin
    header('Location: login.php');
    exit;
}

// Vérifier si la session n'est pas expirée (optionnel, pour plus de sécurité)
$session_timeout = 3600; // 1 heure
if (isset($_SESSION['admin_login_time']) && (time() - $_SESSION['admin_login_time']) > $session_timeout) {
    session_destroy();
    header('Location: login.php?error=session_expired');
    exit;
}
?> 