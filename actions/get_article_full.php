<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expirée']);
    exit();
}

$article_id = $_GET['article_id'] ?? null;
if (!$article_id) {
    echo json_encode(['success' => false, 'message' => 'ID d\'article manquant']);
    exit();
}

$host = 'localhost';
$dbname = 'blog_forum';
$dbuser = 'root';
$dbpass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("
        SELECT a.*, u.username as author_name, c.nom as category_name
        FROM articles a
        LEFT JOIN users u ON a.user_id = u.id
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.id = ?
        LIMIT 1
    ");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$article) {
        echo json_encode(['success' => false, 'message' => 'Article introuvable']);
        exit();
    }
    // Image par catégorie
    $category_images = [
        'Sport' => 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?w=800&h=400&fit=crop',
        'Voyage' => 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=800&h=400&fit=crop',
        'Nutrition' => 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=800&h=400&fit=crop',
        'Informatique' => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=800&h=400&fit=crop',
        'Intelligence Artificielle' => 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=800&h=400&fit=crop'
    ];
    $image_url = $category_images[$article['category_name']] ?? 'https://via.placeholder.com/800x400/3498db/ffffff?text=Article';
    
    echo json_encode([
        'success' => true,
        'title' => htmlspecialchars($article['title']),
        'author' => htmlspecialchars($article['author_name'] ?? 'Utilisateur'),
        'date' => date('d/m/Y H:i', strtotime($article['created_at'])),
        'category' => htmlspecialchars($article['category_name'] ?? ''),
        'image_url' => $image_url,
        'excerpt' => htmlspecialchars($article['excerpt'] ?? ''),
        'content' => nl2br(htmlspecialchars($article['content']))
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors du chargement de l\'article']);
}
?> 