<?php 
require '../includes/header.php'; 

// Gestion des erreurs de connexion
$error_message = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'invalid_credentials') {
        $error_message = 'Nom d\'utilisateur ou mot de passe incorrect.';
    } else if ($_GET['error'] == 'empty_fields') {
        $error_message = 'Veuillez remplir tous les champs.';
    } else if ($_GET['error'] == 'session_expired') {
        $error_message = 'Votre session a expiré. Veuillez vous reconnecter.';
    } else {
        $error_message = 'Une erreur est survenue.';
    }
}

// Message de succès pour l'inscription
$success_message = '';
if (isset($_GET['success']) && $_GET['success'] == 'registered') {
    $success_message = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
}

// Gestion du cookie pour utilisateur déjà inscrit (sans affichage de message)
$already_registered_email = isset($_COOKIE['already_registered']) ? $_COOKIE['already_registered'] : '';

?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h3 class="mb-0">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Connexion
                    </h3>
                </div>
                <div class="card-body p-4">
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <?php echo $error_message; ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle mr-2"></i>
                            <?php echo $success_message; ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <form action="../actions/login_action.php" method="post" id="loginForm">
                        <div class="form-group">
                            <label for="username">
                                <i class="fas fa-user mr-2"></i>
                                Nom d'utilisateur ou Email
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   name="username" 
                                   placeholder="Entrez votre nom d'utilisateur ou email"
                                   required
                                   value="<?php echo htmlspecialchars($already_registered_email); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-lock mr-2"></i>
                                Mot de passe
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Entrez votre mot de passe"
                                       required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            id="togglePassword">
                                        <i class="fas fa-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="rememberMe" name="remember_me">
                                <label class="custom-control-label" for="rememberMe">
                                    Se souvenir de moi
                                </label>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                Se connecter
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-2">
                            <a href="#" class="text-muted">
                                <i class="fas fa-question-circle mr-1"></i>
                                Mot de passe oublié ?
                            </a>
                        </p>
                        <p class="mb-0">
                            Pas encore de compte ? 
                            <a href="register.php" class="text-primary font-weight-bold">
                                Créer un compte
                            </a>
                        </p>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    border-bottom: none;
}

.form-control {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-lg {
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-lg:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.alert {
    border-radius: 8px;
    border: none;
}

.custom-checkbox .custom-control-label::before {
    border-radius: 4px;
}
</style>

