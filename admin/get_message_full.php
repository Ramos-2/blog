<?php
if (!isset($_GET['id'])) exit('ID manquant');
$id = intval($_GET['id']);
$pdo = new PDO("mysql:host=localhost;dbname=blog_forum;charset=utf8", 'root', '');
$stmt = $pdo->prepare("SELECT username, message, created_at FROM admin_messages WHERE id = ?");
$stmt->execute([$id]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$message) exit('Message introuvable');

echo "<h3>Message de " . htmlspecialchars($message['username']) . "</h3>";
echo "<p><strong>Date :</strong> " . date('d/m/Y H:i', strtotime($message['created_at'])) . "</p>";
echo "<div style='margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;'>";
echo nl2br(htmlspecialchars($message['message']));
echo "</div>";
?> 