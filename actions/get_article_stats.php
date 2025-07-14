<?php
session_start();
header('Content-Type: application/json');

// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expirée']);
    exit();
}

$article_id = $_GET['article_id'] ?? null;

if (!$article_id) {
    echo json_encode(['success' => false, 'message' => 'ID d\'article manquant']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Connexion à la base de données
$host = 'localhost';
$dbname = 'blog_forum';
$dbuser = 'root';
$dbpass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Compter les likes
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM article_likes WHERE article_id = ?");
    $stmt->execute([$article_id]);
    $likes_count = $stmt->fetchColumn();
    
    // Compter les commentaires
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE article_id = ? AND status = 'approved'");
    $stmt->execute([$article_id]);
    $comments_count = $stmt->fetchColumn();
    
    // Vérifier si l'utilisateur a liké cet article
    $stmt = $pdo->prepare("SELECT id FROM article_likes WHERE user_id = ? AND article_id = ?");
    $stmt->execute([$user_id, $article_id]);
    $user_liked = $stmt->rowCount() > 0;
    
    echo json_encode([
        'success' => true,
        'likes' => $likes_count,
        'comments' => $comments_count,
        'user_liked' => $user_liked
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors du chargement des statistiques']);
}
?> 