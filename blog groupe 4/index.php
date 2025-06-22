<?php require 'includes/header.php'; ?>

<?php if(isset($_SESSION['auth'])): ?>
    
    <h1>Bienvenue sur le forum </h1>
<?php else: ?>

    <div class="info">
        <h1>Bienvenue !</h1>
        <p>connectez-vous pour continuer</p>
        <br>
        <a href="pages/login.php" >Se connecter</a>
        <a href="pages/register.php" >S'inscrire</a>
    </div>

<?php endif; ?>

<?php require 'includes/footer.php'; ?> 