<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expirée']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

$message = trim($_POST['message'] ?? '');
if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Le message ne peut pas être vide']);
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Utilisateur';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=blog_forum;charset=utf8", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Insérer le message dans une table de messages (à créer si elle n'existe pas)
    $stmt = $pdo->prepare("
        INSERT INTO admin_messages (user_id, username, message, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$user_id, $username, $message]);
    
    echo json_encode(['success' => true, 'message' => 'Votre message a été envoyé à l\'administrateur']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi du message']);
}
?> 