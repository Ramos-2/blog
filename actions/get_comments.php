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

// Connexion à la base de données
$host = 'localhost';
$dbname = 'blog_forum';
$dbuser = 'root';
$dbpass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer les commentaires avec les informations utilisateur
    $stmt = $pdo->prepare("
        SELECT c.*, u.username as author_name 
        FROM comments c 
        LEFT JOIN users u ON c.user_id = u.id 
        WHERE c.article_id = ? AND c.status = 'approved' 
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$article_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formater les commentaires
    $formatted_comments = [];
    foreach ($comments as $comment) {
        $formatted_comments[] = [
            'id' => $comment['id'],
            'content' => htmlspecialchars($comment['content']),
            'author' => htmlspecialchars($comment['author_name'] ?? 'Utilisateur'),
            'date' => date('d/m/Y H:i', strtotime($comment['created_at']))
        ];
    }
    
    echo json_encode([
        'success' => true,
        'comments' => $formatted_comments
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors du chargement des commentaires']);
}
?> 