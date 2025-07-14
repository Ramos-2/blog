<?php
require '../includes/header.php';

// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=session_expired');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Utilisateur';
$user_role = $_SESSION['role'] ?? 'user';

// Connexion à la base de données
$host = 'localhost';
$dbname = 'blog_forum';
$dbuser = 'root';
$dbpass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer toutes les catégories
    $categories = $pdo->query("SELECT id, nom FROM categories ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer tous les articles publiés
    $category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
    
    if ($category_filter > 0) {
        $stmt = $pdo->prepare("
            SELECT a.*, u.username as author_name, c.nom as category_name
            FROM articles a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.status = 'published' AND a.category_id = ?
            ORDER BY a.created_at DESC
        ");
        $stmt->execute([$category_filter]);
    } else {
        $stmt = $pdo->prepare("
            SELECT a.*, u.username as author_name, c.nom as category_name
            FROM articles a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.status = 'published'
            ORDER BY a.created_at DESC
        ");
        $stmt->execute();
    }
    
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $articles = [];
    $categories = [];
}
?>


    <div class="layout-with-sidebar">
        <?php 
        $sidebar_type = 'user';
        include '../includes/sidebar.php'; 
        ?>
        
        <div class="main-content">
            <div class="all-articles-container">
                <!-- En-tête de la page -->
                <div class="page-header">
                    <h1>Découvrir tous les articles</h1>
                    <p class="page-subtitle">Explorez les articles de toutes les catégories</p>
                </div>

                <!-- Filtre par catégorie -->
                <div class="category-filter-section">
                    <form method="GET" class="filter-form">
                        <label for="category">Filtrer par catégorie :</label>
                        <select name="category" id="category" onchange="this.form.submit()">
                            <option value="0">Toutes les catégories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <!-- Affichage des articles -->
                <?php if (!empty($articles)): ?>
                    <div class="articles-section">
                        <h2>
                            <?php if (isset($_GET['category']) && $_GET['category'] > 0): ?>
                                <?php 
                                $category_name = 'Catégorie';
                                foreach ($categories as $cat) {
                                    if ($cat['id'] == $_GET['category']) {
                                        $category_name = $cat['nom'];
                                        break;
                                    }
                                }
                                ?>
                                Articles de la catégorie : <?= htmlspecialchars($category_name) ?>
                            <?php else: ?>
                                Tous les articles (<?= count($articles) ?>)
                            <?php endif; ?>
                        </h2>
                        
                        <div class="articles-grid">
                            <?php foreach ($articles as $article): ?>
                                <article class="article-card">
                                    <!-- Image de l'article -->
                                    <div class="article-image">
                                        <?php
                                        // Images par catégorie
                                        $category_images = [
                                            'Sport' => 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?w=800&h=400&fit=crop',
                                            'Voyage' => 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=800&h=400&fit=crop',
                                            'Nutrition' => 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=800&h=400&fit=crop',
                                            'Informatique' => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=800&h=400&fit=crop',
                                            'Intelligence Artificielle' => 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=800&h=400&fit=crop'
                                        ];
                                        
                                        $image_url = !empty($article['image_url']) ? '../' . $article['image_url'] : ($category_images[$article['category_name']] ?? 'https://via.placeholder.com/800x400/3498db/ffffff?text=Article');
                                        ?>
                                        <img src="<?= $image_url ?>" 
                                             alt="<?= htmlspecialchars($article['title']) ?>"
                                             onerror="this.src='https://via.placeholder.com/800x400/3498db/ffffff?text=Article'">
                                    </div>
                                    
                                    <div class="article-header">
                                        <h3 class="article-title">
                                            <a href="#" class="read-more" onclick="showFullArticle(<?= $article['id'] ?>); return false;">
                                                <?= htmlspecialchars($article['title']) ?>
                                            </a>
                                        </h3>
                                        <div class="article-meta">
                                            <span class="article-author">Par <?= htmlspecialchars($article['author_name']) ?></span>
                                            <span class="article-date">
                                                <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                                            </span>
                                            <?php if ($article['category_name']): ?>
                                                <span class="article-category"><?= htmlspecialchars($article['category_name']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if ($article['excerpt']): ?>
                                        <div class="article-excerpt">
                                            <?= htmlspecialchars($article['excerpt']) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="article-footer">
                                        <div class="article-actions">
                                            <a href="#" class="read-more" onclick="showFullArticle(<?= $article['id'] ?>); return false;">
                                                Lire la suite →
                                            </a>
                                            <div class="interaction-buttons">
                                                <button class="btn-like" data-article-id="<?= $article['id'] ?>" onclick="toggleLike(<?= $article['id'] ?>)">
                                                    <span class="like-icon">❤️</span>
                                                    <span class="like-count">0</span>
                                                </button>
                                                <button class="btn-comment" onclick="showComments(<?= $article['id'] ?>)">
                                                    <span class="comment-icon">💬</span>
                                                    <span class="comment-count">0</span>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Section commentaires (cachée par défaut) -->
                                        <div class="comments-section" id="comments-<?= $article['id'] ?>" style="display: none;">
                                            <div class="comments-list">
                                                <!-- Les commentaires seront chargés ici -->
                                            </div>
                                            <div class="add-comment">
                                                <textarea class="comment-input" placeholder="Ajouter un commentaire..." rows="2"></textarea>
                                                <button class="btn-submit-comment" onclick="submitComment(<?= $article['id'] ?>)">
                                                    Commenter
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-articles">
                        <div class="no-articles-content">
                            <h2>Aucun article trouvé</h2>
                            <?php if (isset($_GET['category']) && $_GET['category'] > 0): ?>
                                <p>Aucun article n'est disponible dans cette catégorie pour le moment.</p>
                                <a href="all_articles.php" class="btn-primary">Voir tous les articles</a>
                            <?php else: ?>
                                <p>Aucun article n'est disponible pour le moment.</p>
                                <a href="../Articles/articles.php" class="btn-primary">Créer le premier article</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal pour afficher l'article complet -->
    <div id="article-modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1000;">
        <div style="background:#fff; padding:30px; border-radius:10px; max-width:800px; width:90%; max-height:80vh; overflow-y:auto; position:relative;">
            <span style="position:absolute; top:10px; right:20px; cursor:pointer; font-size:24px;" onclick="closeArticleModal()">&times;</span>
            <div id="modal-article-content"></div>
        </div>
    </div>

    <style>
    .all-articles-container {
        padding: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .page-header {
        text-align: center;
        margin-bottom: 40px;
        padding: 30px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .page-header h1 {
        color: #2c3e50;
        margin: 0 0 10px 0;
        font-size: 2.5em;
    }

    .page-subtitle {
        color: #7f8c8d;
        font-size: 1.1em;
        margin: 0;
    }

    .category-filter-section {
        background: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .filter-form {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .filter-form label {
        font-weight: bold;
        color: #2c3e50;
        margin: 0;
    }

    .filter-form select {
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        min-width: 200px;
    }

    .articles-section {
        margin-bottom: 40px;
    }

    .articles-section h2 {
        color: #2c3e50;
        margin-bottom: 30px;
        font-size: 1.8em;
        text-align: center;
    }

    .articles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 25px;
    }

    .article-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .article-card:hover {
        transform: translateY(-5px);
    }

    .article-image {
        width: 100%;
        height: 200px;
        overflow: hidden;
        position: relative;
    }

    .article-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .article-card:hover .article-image img {
        transform: scale(1.05);
    }

    .article-header {
        padding: 25px 25px 15px;
    }

    .article-title {
        margin: 0 0 15px 0;
        font-size: 1.3em;
    }

    .article-title a {
        color: #2c3e50;
        text-decoration: none;
    }

    .article-title a:hover {
        color: #3498db;
    }

    .article-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        font-size: 0.9em;
        color: #7f8c8d;
    }

    .article-category {
        background: #3498db;
        color: white;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.8em;
    }

    .article-excerpt {
        padding: 0 25px 15px;
        color: #555;
        line-height: 1.6;
    }

    .article-footer {
        padding: 15px 25px 25px;
        border-top: 1px solid #eee;
    }

    .read-more {
        color: #3498db;
        text-decoration: none;
        font-weight: 500;
    }

    .article-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .interaction-buttons {
        display: flex;
        gap: 10px;
    }

    .btn-like, .btn-comment {
        background: none;
        border: 1px solid #ddd;
        padding: 5px 10px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.9em;
        transition: all 0.3s ease;
    }

    .btn-like:hover, .btn-comment:hover {
        background: #f8f9fa;
        border-color: #3498db;
    }

    .comments-section {
        border-top: 1px solid #eee;
        padding-top: 15px;
    }

    .add-comment {
        margin-top: 15px;
    }

    .comment-input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        resize: vertical;
        font-family: inherit;
    }

    .btn-submit-comment {
        background: #3498db;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 10px;
    }

    .btn-submit-comment:hover {
        background: #2980b9;
    }

    .no-articles {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .no-articles-content h2 {
        color: #2c3e50;
        margin-bottom: 15px;
    }

    .no-articles-content p {
        color: #7f8c8d;
        margin-bottom: 20px;
    }

    .btn-primary {
        background: #3498db;
        color: white;
        padding: 12px 24px;
        border-radius: 5px;
        text-decoration: none;
        display: inline-block;
        transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
        background: #2980b9;
        text-decoration: none;
        color: white;
    }

    @media (max-width: 768px) {
        .all-articles-container {
            padding: 15px;
        }
        
        .page-header h1 {
            font-size: 2em;
        }
        
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-form select {
            min-width: auto;
        }
        
        .articles-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Styles pour le thème sombre */
    .dark-theme .all-articles-container {
        background: #181a1b !important;
        color: #f1f1f1 !important;
    }

    .dark-theme .page-header {
        background: #23272b !important;
        color: #f1f1f1 !important;
        border-color: #444 !important;
    }

    .dark-theme .page-header h1 {
        color: #f1f1f1 !important;
    }

    .dark-theme .page-subtitle {
        color: #bdc3c7 !important;
    }

    .dark-theme .category-filter-section {
        background: #23272b !important;
        color: #f1f1f1 !important;
        border-color: #444 !important;
    }

    .dark-theme .filter-form label {
        color: #f1f1f1 !important;
    }

    .dark-theme .filter-form select {
        background: #23272b !important;
        color: #f1f1f1 !important;
        border-color: #444 !important;
    }

    .dark-theme .filter-form select:focus {
        background: #2c3e50 !important;
        border-color: #3498db !important;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25) !important;
    }

    .dark-theme .articles-section h2 {
        color: #f1f1f1 !important;
    }

    .dark-theme .article-card {
        background: #23272b !important;
        color: #f1f1f1 !important;
        border-color: #444 !important;
    }

    .dark-theme .article-card:hover {
        background: #2c3e50 !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3) !important;
    }

    .dark-theme .article-title a {
        color: #f1f1f1 !important;
    }

    .dark-theme .article-title a:hover {
        color: #3498db !important;
    }

    .dark-theme .article-meta {
        color: #bdc3c7 !important;
    }

    .dark-theme .article-excerpt {
        color: #bdc3c7 !important;
    }

    .dark-theme .article-footer {
        border-color: #444 !important;
    }

    .dark-theme .read-more {
        color: #3498db !important;
    }

    .dark-theme .btn-like, .dark-theme .btn-comment {
        background: #23272b !important;
        color: #f1f1f1 !important;
        border-color: #444 !important;
    }

    .dark-theme .btn-like:hover, .dark-theme .btn-comment:hover {
        background: #2c3e50 !important;
        border-color: #3498db !important;
    }

    .dark-theme .comments-section {
        border-color: #444 !important;
    }

    .dark-theme .comment-input {
        background: #23272b !important;
        color: #f1f1f1 !important;
        border-color: #444 !important;
    }

    .dark-theme .comment-input:focus {
        background: #2c3e50 !important;
        border-color: #3498db !important;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25) !important;
    }

    .dark-theme .no-articles {
        background: #23272b !important;
        color: #f1f1f1 !important;
        border-color: #444 !important;
    }

    .dark-theme .no-articles-content h2 {
        color: #f1f1f1 !important;
    }

    .dark-theme .no-articles-content p {
        color: #bdc3c7 !important;
    }

    /* Modal pour le thème sombre */
    .dark-theme #article-modal > div {
        background: #23272b !important;
        color: #f1f1f1 !important;
        border-color: #444 !important;
    }

    .dark-theme .modal-meta {
        color: #bdc3c7 !important;
    }

    .dark-theme .modal-excerpt {
        color: #bdc3c7 !important;
    }

    .dark-theme .modal-content-full {
        color: #f1f1f1 !important;
    }
    </style>

    <script>
    function showFullArticle(articleId) {
        const modal = document.getElementById('article-modal');
        const modalContent = document.getElementById('modal-article-content');
        modalContent.innerHTML = '<div style="text-align:center;padding:40px;">Chargement...</div>';
        modal.style.display = 'flex';
        fetch(`../actions/get_article_full.php?article_id=${articleId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modalContent.innerHTML = `
                        <h2>${data.title}</h2>
                        <div class='modal-meta'>
                            Par <strong>${data.author}</strong> | ${data.date} | <span class='article-category'>${data.category}</span>
                        </div>
                        <img src='${data.image_url}' class='modal-image' alt='${data.title}' onerror="this.src='https://via.placeholder.com/800x400/3498db/ffffff?text=Article'">
                        <div class='modal-excerpt'><em>${data.excerpt}</em></div>
                        <div class='modal-content-full' style='margin-top:20px;'>${data.content}</div>
                    `;
                } else {
                    modalContent.innerHTML = '<div style="color:#e74c3c;text-align:center;padding:40px;">Erreur lors du chargement de l\'article.</div>';
                }
            })
            .catch(() => {
                modalContent.innerHTML = '<div style="color:#e74c3c;text-align:center;padding:40px;">Erreur lors du chargement de l\'article.</div>';
            });
    }
    
    function closeArticleModal() {
        document.getElementById('article-modal').style.display = 'none';
    }
    
    function toggleLike(articleId) {
        // Fonctionnalité de like à implémenter
        console.log('Like article:', articleId);
    }
    
    function showComments(articleId) {
        const commentsSection = document.getElementById(`comments-${articleId}`);
        if (commentsSection.style.display === 'none') {
            commentsSection.style.display = 'block';
        } else {
            commentsSection.style.display = 'none';
        }
    }
    
    function submitComment(articleId) {
        // Fonctionnalité de commentaire à implémenter
        console.log('Submit comment for article:', articleId);
    }
    
    window.onclick = function(event) {
        const modal = document.getElementById('article-modal');
        if (event.target === modal) {
            closeArticleModal();
        }
    }
    </script> 