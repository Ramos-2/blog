<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = $_POST['motdepasse'];
    $confirm_password = $_POST['confirm_motdepasse'];
    
    // Validation des champs
    if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($confirm_password)) {
        header("Location: ../pages/register.php?error=empty_fields");
        exit();
    }
    
    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../pages/register.php?error=invalid_email");
        exit();
    }
    
    // Validation du mot de passe
    if ($password !== $confirm_password) {
        header("Location: ../pages/register.php?error=password_mismatch");
        exit();
    }
    
    if (strlen($password) < 6) {
        header("Location: ../pages/register.php?error=password_too_short");
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
        
        // Vérification si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            header("Location: ../pages/register.php?error=email_exists");
            exit();
        }
        
        // Création du nom d'utilisateur
        $username = strtolower($prenom . '.' . $nom);
        
        // Vérification si le nom d'utilisateur existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $username = $username . rand(100, 999);
        }
        
        // Hashage du mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertion de l'utilisateur
        $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, username, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, 'user', NOW())");
        $stmt->execute([$nom, $prenom, $username, $email, $hashed_password]);
        
        // Récupération de l'ID de l'utilisateur créé
        $user_id = $pdo->lastInsertId();
        
        // Suppression de la connexion automatique
        // $_SESSION['user_id'] = $user_id;
        // $_SESSION['username'] = $username;
        // $_SESSION['email'] = $email;
        // $_SESSION['role'] = 'user';
        // $_SESSION['logged_in'] = true;
        
        // Redirection vers la page de connexion avec message de succès
        setcookie('already_registered', $email, time() + 365*24*60*60, '/');
        header("Location: ../pages/login.php?success=registered");
        exit();
        
    } catch (PDOException $e) {
        header("Location: ../pages/register.php?error=database_error");
        exit();
    }
    
} else {
    header("Location: ../pages/register.php");
    exit();
}
?>