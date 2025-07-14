<?php require '../includes/header.php'; ?>

<div class="layout-with-sidebar">
    <?php 
    $sidebar_type = 'user';
    include '../includes/sidebar.php'; 
    ?>
    
    <div class="main-content">
        <div class="category-container">
            <!-- Bannière de la catégorie Voyage -->
            <div class="category-banner voyage-banner">
                <div class="banner-overlay">
                    <div class="banner-content">
                        <h1>✈️ Voyage</h1>
                        <p>Partagez vos aventures, découvertes et conseils de voyage autour du monde</p>
                    </div>
                </div>
            </div>

            <!-- Contenu de la catégorie -->
            <div class="category-content">
                <div class="category-header">
                    <h2>Publier dans la catégorie Voyage</h2>
                    <p>Partagez vos expériences de voyage, conseils de destination ou photos de vos aventures</p>
                </div>

                <!-- Formulaire de publication -->
                <div class="publication-form">
                    <form action="enregistrer_message.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="titre">Titre de votre publication :</label>
                            <input type="text" id="titre" name="titre" placeholder="Ex: Mon voyage au Japon - Tokyo en 5 jours" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description :</label>
                            <textarea id="description" name="description" rows="6" placeholder="Décrivez votre voyage, partagez vos conseils, racontez vos aventures..." required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="fichier">Ajouter une image ou une vidéo :</label>
                            <input type="file" id="fichier" name="fichier" accept="image/*,video/*" required>
                            <small>Formats acceptés : JPG, PNG, GIF, MP4, AVI</small>
                        </div>

                        <!-- Champ caché pour la catégorie actuelle -->
                        <input type="hidden" name="categorie" value="voyage">

                        <div class="form-actions">
                            <button type="submit" class="btn-publish">📝 Publier</button>
                            <a href="../pages/all_articles.php" class="btn-cancel">❌ Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.category-container {
    min-height: 100vh;
}

.category-banner {
    height: 300px;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 40px;
}

.voyage-banner {
    background-image: url('https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=1200&h=400&fit=crop');
}

.banner-overlay {
    background: rgba(0, 0, 0, 0.5);
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.banner-content {
    text-align: center;
    color: white;
    max-width: 600px;
    padding: 20px;
}

.banner-content h1 {
    font-size: 3.5em;
    margin: 0 0 15px 0;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
}

.banner-content p {
    font-size: 1.2em;
    margin: 0;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
}

.category-content {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 30px;
}

.category-header {
    text-align: center;
    margin-bottom: 40px;
}

.category-header h2 {
    color: #2c3e50;
    font-size: 2.2em;
    margin: 0 0 15px 0;
}

.category-header p {
    color: #7f8c8d;
    font-size: 1.1em;
    margin: 0;
}

.publication-form {
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 8px;
    font-size: 1.1em;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 15px;
    border: 2px solid #ecf0f1;
    border-radius: 8px;
    font-size: 16px;
    font-family: inherit;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.form-group small {
    color: #7f8c8d;
    font-size: 0.9em;
    margin-top: 5px;
    display: block;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
}

.btn-publish, .btn-cancel {
    padding: 15px 30px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;
}

.btn-publish {
    background: #27ae60;
    color: white;
}

.btn-publish:hover {
    background: #229954;
    transform: translateY(-2px);
}

.btn-cancel {
    background: #95a5a6;
    color: white;
}

.btn-cancel:hover {
    background: #7f8c8d;
    transform: translateY(-2px);
    text-decoration: none;
    color: white;
}

/* Styles pour le thème sombre */
.dark-theme .category-banner {
    filter: brightness(0.8);
}

.dark-theme .category-header h2 {
    color: #f1f1f1 !important;
}

.dark-theme .category-header p {
    color: #bdc3c7 !important;
}

.dark-theme .publication-form {
    background: #23272b !important;
    color: #f1f1f1 !important;
    border-color: #444 !important;
}

.dark-theme .form-group label {
    color: #f1f1f1 !important;
}

.dark-theme .form-group input,
.dark-theme .form-group textarea {
    background: #23272b !important;
    color: #f1f1f1 !important;
    border-color: #444 !important;
}

.dark-theme .form-group input:focus,
.dark-theme .form-group textarea:focus {
    background: #2c3e50 !important;
    border-color: #3498db !important;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2) !important;
}

.dark-theme .form-group small {
    color: #bdc3c7 !important;
}

@media (max-width: 768px) {
    .banner-content h1 {
        font-size: 2.5em;
    }
    
    .category-content {
        padding: 0 15px;
    }
    
    .publication-form {
        padding: 20px;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>
