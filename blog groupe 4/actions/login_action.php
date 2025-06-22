<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $host = 'localhost';
    $dbname = 'blog_forum';
    $dbuser = 'root';
    $dbpass = '';

else {
    header("Location: ../pages/login.php");
    exit();
}
}
?> 