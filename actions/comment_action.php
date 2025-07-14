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
$content = trim($input['content'] ?? '');

if (!$article_id || empty($content)) {
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
    
    // Insérer le commentaire
    $stmt = $pdo->prepare("INSERT INTO comments (content, user_id, article_id, status, created_at) VALUES (?, ?, ?, 'approved', NOW())");
    $stmt->execute([$content, $user_id, $article_id]);
    
    echo json_encode(['success' => true, 'message' => 'Commentaire ajouté avec succès']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout du commentaire']);
}
?> 