<?php
session_start();
header('Content-Type: application/json');

// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expirée']);
    exit();
}

// Récupération des données JSON
$input = json_decode(file_get_contents('php://input'), true);
$article_id = $input['article_id'] ?? null;
$action = $input['action'] ?? null;

if (!$article_id || !$action) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
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
    
    if ($action === 'like') {
        // Vérifier si l'utilisateur a déjà liké cet article
        $stmt = $pdo->prepare("SELECT id FROM article_likes WHERE user_id = ? AND article_id = ?");
        $stmt->execute([$user_id, $article_id]);
        
        if ($stmt->rowCount() === 0) {
            // Ajouter le like
            $stmt = $pdo->prepare("INSERT INTO article_likes (user_id, article_id, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$user_id, $article_id]);
        }
    } else if ($action === 'unlike') {
        // Supprimer le like
        $stmt = $pdo->prepare("DELETE FROM article_likes WHERE user_id = ? AND article_id = ?");
        $stmt->execute([$user_id, $article_id]);
    }
    
    // Récupérer le nombre total de likes
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM article_likes WHERE article_id = ?");
    $stmt->execute([$article_id]);
    $likes_count = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true, 
        'likes_count' => $likes_count,
        'action' => $action
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données']);
}
?> 