<?php
if (!isset($_GET['id'])) exit('ID manquant');
$id = intval($_GET['id']);
$pdo = new PDO("mysql:host=localhost;dbname=blog_forum;charset=utf8", 'root', '');
$stmt = $pdo->prepare("SELECT title, content FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$article) exit('Article introuvable');
echo "<h3>" . htmlspecialchars($article['title']) . "</h3>";
echo "<div>" . nl2br(htmlspecialchars($article['content'])) . "</div>"; 