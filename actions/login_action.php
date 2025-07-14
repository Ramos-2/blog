<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Validation des champs
    if (empty($username) || empty($password)) {
        header("Location: ../pages/login.php?error=empty_fields");
        exit();
    }
    
    // Connexion à la base de données
    $host = 'localhost';
    $dbname = 'blog_forum';
    $dbuser = 'root';
    $dbpass = '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $dbuser, $dbpass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Recherche de l'utilisateur
        $stmt = $pdo->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            // Redirection vers le dashboard
            header("Location: ../pages/dashboard.php");
            exit();
        } else {
            // Identifiants incorrects
            header("Location: ../pages/login.php?error=invalid_credentials");
            exit();
        }
        
    } catch (PDOException $e) {
        // Erreur de base de données
        header("Location: ../pages/login.php?error=database_error");
        exit();
    }
    
} else {
    // Méthode non autorisée
    header("Location: ../pages/login.php");
    exit();
}
?> 