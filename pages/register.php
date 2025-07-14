<?php 
require '../includes/header.php'; 
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Créer un compte</h3>
                </div>
                <div class="card-body">
                    <form action="../actions/register_action.php" method="post">
                        <div class="form-group">
                            <label for="nom">Nom :</label>
                            <input type="text" class="form-control" name="nom" required>
                        </div>
                        <div class="form-group">
                            <label for="prenom">Prénom :</label>
                            <input type="text" class="form-control" name="prenom" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email :</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="motdepasse">Mot de passe :</label>
                            <input type="password" class="form-control" name="motdepasse" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_motdepasse">Confirmer le mot de passe :</label>
                            <input type="password" class="form-control" name="confirm_motdepasse" required>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">S'inscrire</button>
                        </div>
                    </form>
                    <p class="text-center mt-3">Déjà un compte ? <a href="login.php">Se connecter</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require '../includes/footer.php'; 
?> 