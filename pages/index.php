<?php 
require '../includes/header.php'; 
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="welcome-card">
                <h1 class="display-4 mb-4">Bienvenue sur notre site</h1>
                <p class="lead mb-5">Connectez-vous pour continuer et accéder à toutes nos fonctionnalités</p>
                
                <div class="row justify-content-center">
                    <div class="col-md-4 mb-3">
                        <a href="login.php" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Connexion
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="register.php" class="btn btn-success btn-lg btn-block">
                            <i class="fas fa-user-plus mr-2"></i>
                            Inscription
                        </a>
                    </div>
                </div>
                
                <div class="mt-5">
                    <p class="text-muted">
                        <i class="fas fa-info-circle mr-2"></i>
                        Rejoignez notre communauté et découvrez nos articles passionnants
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 3rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.welcome-card h1 {
    font-weight: 700;
    margin-bottom: 1.5rem;
}

.welcome-card .lead {
    font-size: 1.25rem;
    opacity: 0.9;
}

.btn-lg {
    padding: 15px 30px;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-lg:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.btn-block {
    width: 100%;
}

.text-muted {
    opacity: 0.8;
}
</style>

<?php 
require '../includes/footer.php'; 
?> 