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
    
    // Récupérer les catégories préférées de l'utilisateur
    $stmt = $pdo->prepare("SELECT category_name FROM user_categories WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $articles = [];
    if (!empty($user_categories)) {
        // Construire la requête pour récupérer les articles des catégories préférées
        $placeholders = str_repeat('?,', count($user_categories) - 1) . '?';
        $query = "
            SELECT a.*, u.username as author_name, c.nom as category_name
            FROM articles a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.status = 'published' 
            AND (c.nom IN ($placeholders) OR c.slug IN ($placeholders))
            ORDER BY a.created_at DESC
            LIMIT 20
        ";
        
        $params = array_merge($user_categories, $user_categories);
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    $articles = [];
    $user_categories = [];
}
?>


    <div class="layout-with-sidebar">
        <?php 
        $sidebar_type = 'user';
        include '../includes/sidebar.php'; 
        ?>
        
        <div class="main-content">
            <div class="home-container">
                <!-- En-tête de la page d'accueil -->
                <div class="home-header">
                    <h1>Bienvenue sur votre page d'accueil personnalisée, <?= htmlspecialchars($username) ?> !</h1>
                    <?php if (!empty($user_categories)): ?>
                        <p class="home-subtitle">
                            Voici les articles de vos catégories préférées : 
                            <strong><?= implode(', ', array_map('ucfirst', $user_categories)) ?></strong>
                        </p>
                    <?php else: ?>
                        <p class="home-subtitle">
                            Vous n'avez pas encore choisi vos catégories préférées. 
                            <a href="../Categories/Inscription.php" class="link-primary">Cliquez ici pour les configurer</a>
                        </p>
                    <?php endif; ?>
                    
                    <!-- Boutons d'action -->
                    <div class="action-buttons-section">
                        <a href="../Articles/articles.php" class="btn-add-article">
                            <span class="btn-icon">✏️</span>
                            Ajouter un nouvel article
                        </a>
                        <a href="all_articles.php" class="btn-view-all-articles">
                            <span class="btn-icon">📚</span>
                            Voir tous les articles
                        </a>
                    </div>
                </div>

                <!-- Articles des catégories préférées -->
                <?php if (!empty($articles)): ?>
                    <div class="articles-section">
                        <h2>Articles de vos centres d'intérêt</h2>
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
                            <?php if (empty($user_categories)): ?>
                                <p>Vous n'avez pas encore configuré vos catégories préférées.</p>
                                <a href="../Categories/Inscription.php" class="btn-primary">Choisir mes catégories</a>
                            <?php else: ?>
                                <p>Aucun article n'est disponible dans vos catégories préférées pour le moment.</p>
                                <a href="../pages/index.php" class="btn-secondary">Voir tous les articles</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modale pour l'article complet -->
    <div id="article-modal" class="article-modal" style="display:none;">
        <div class="modal-content">
            <span class="close-modal" onclick="closeArticleModal()">&times;</span>
            <div id="modal-article-content">
                <!-- Le contenu complet de l'article sera chargé ici -->
            </div>
        </div>
    </div>

    <style>
    .home-container {
        padding: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .home-header {
        text-align: center;
        margin-bottom: 40px;
        padding: 30px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .home-header h1 {
        color: #2c3e50;
        margin: 0 0 15px 0;
        font-size: 2.5em;
    }

    .home-subtitle {
        color: #7f8c8d;
        font-size: 1.1em;
        margin: 0;
    }

    .link-primary {
        color: #3498db;
        text-decoration: none;
        font-weight: 500;
    }

    .link-primary:hover {
        text-decoration: underline;
    }

    .action-buttons-section {
        margin-top: 25px;
        text-align: center;
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-add-article, .btn-view-all-articles {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .btn-add-article {
        background: #27ae60;
        color: white;
    }

    .btn-view-all-articles {
        background: #3498db;
        color: white;
    }

    .btn-add-article:hover {
        background: #229954;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        text-decoration: none;
        color: white;
    }

    .btn-view-all-articles:hover {
        background: #2980b9;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        text-decoration: none;
        color: white;
    }

    .btn-icon {
        font-size: 1.2em;
    }

    .articles-section {
        margin-bottom: 40px;
    }

    .articles-section h2 {
        color: #2c3e50;
        margin-bottom: 30px;
        font-size: 1.8em;
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

    .read-more:hover {
        text-decoration: underline;
    }

    .article-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .interaction-buttons {
        display: flex;
        gap: 10px;
    }

    .btn-like, .btn-comment {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 8px 12px;
        border: 1px solid #e0e0e0;
        background: white;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9em;
    }

    .btn-like:hover, .btn-comment:hover {
        background: #f8f9fa;
        border-color: #3498db;
    }

    .btn-like.liked {
        background: #e74c3c;
        color: white;
        border-color: #e74c3c;
    }

    .btn-like.liked .like-icon {
        animation: heartBeat 0.3s ease;
    }

    @keyframes heartBeat {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }

    .like-icon, .comment-icon {
        font-size: 1.1em;
    }

    .like-count, .comment-count {
        font-weight: 500;
    }

    .comments-section {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .comments-list {
        margin-bottom: 15px;
        max-height: 200px;
        overflow-y: auto;
    }

    .comment-item {
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .comment-item:last-child {
        border-bottom: none;
    }

    .comment-author {
        font-weight: 500;
        color: #2c3e50;
        font-size: 0.9em;
    }

    .comment-content {
        margin-top: 5px;
        color: #555;
        font-size: 0.9em;
    }

    .comment-date {
        font-size: 0.8em;
        color: #7f8c8d;
        margin-top: 3px;
    }

    .add-comment {
        display: flex;
        gap: 10px;
        align-items: flex-end;
    }

    .comment-input {
        flex: 1;
        padding: 10px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        resize: vertical;
        font-size: 0.9em;
        font-family: inherit;
    }

    .comment-input:focus {
        outline: none;
        border-color: #3498db;
    }

    .btn-submit-comment {
        padding: 10px 15px;
        background: #3498db;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.9em;
        transition: background 0.3s ease;
    }

    .btn-submit-comment:hover {
        background: #2980b9;
    }

    .no-articles {
        text-align: center;
        padding: 60px 20px;
    }

    .no-articles-content {
        background: white;
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        max-width: 500px;
        margin: 0 auto;
    }

    .no-articles h2 {
        color: #2c3e50;
        margin-bottom: 20px;
    }

    .no-articles p {
        color: #7f8c8d;
        margin-bottom: 25px;
    }

    .btn-primary, .btn-secondary {
        display: inline-block;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: #3498db;
        color: white;
    }

    .btn-primary:hover {
        background: #2980b9;
    }

    .btn-secondary {
        background: #95a5a6;
        color: white;
    }

    .btn-secondary:hover {
        background: #7f8c8d;
    }

    @media (max-width: 768px) {
        .articles-grid {
            grid-template-columns: 1fr;
        }
        
        .home-header h1 {
            font-size: 2em;
        }
    }

    @media (max-width: 768px) {
        .article-actions {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .add-comment {
            flex-direction: column;
            align-items: stretch;
        }
    }

    .article-modal {
        display: none;
        position: fixed;
        z-index: 2000;
        left: 0;
        top: 0;
        width: 100vw;
        height: 100vh;
        overflow: auto;
        background: rgba(44, 62, 80, 0.85);
        justify-content: center;
        align-items: center;
    }
    .article-modal .modal-content {
        background: #fff;
        margin: 60px auto;
        padding: 30px 30px 20px 30px;
        border-radius: 12px;
        max-width: 700px;
        box-shadow: 0 4px 32px rgba(0,0,0,0.25);
        position: relative;
        animation: modalFadeIn 0.3s;
    }
    @keyframes modalFadeIn {
        from { transform: translateY(40px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .close-modal {
        position: absolute;
        top: 18px;
        right: 22px;
        font-size: 2em;
        color: #7f8c8d;
        cursor: pointer;
        transition: color 0.2s;
    }
    .close-modal:hover {
        color: #e74c3c;
    }
    #modal-article-content h2 {
        margin-top: 0;
        color: #2c3e50;
    }
    #modal-article-content .modal-meta {
        color: #7f8c8d;
        font-size: 0.95em;
        margin-bottom: 18px;
    }
    #modal-article-content .modal-image {
        width: 100%;
        max-height: 300px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 18px;
    }
    </style>

    <script>
    // Gestion des likes
    function toggleLike(articleId) {
        const likeButton = document.querySelector(`[data-article-id="${articleId}"]`);
        const likeCount = likeButton.querySelector('.like-count');
        const likeIcon = likeButton.querySelector('.like-icon');
        
        if (likeButton.classList.contains('liked')) {
            // Unlike
            likeButton.classList.remove('liked');
            likeIcon.textContent = '🤍';
            likeCount.textContent = parseInt(likeCount.textContent) - 1;
        } else {
            // Like
            likeButton.classList.add('liked');
            likeIcon.textContent = '❤️';
            likeCount.textContent = parseInt(likeCount.textContent) + 1;
        }
        
        // Envoyer la requête au serveur
        fetch('../actions/like_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                article_id: articleId,
                action: likeButton.classList.contains('liked') ? 'like' : 'unlike'
            })
        }).catch(error => console.error('Erreur lors du like:', error));
    }

    // Gestion des commentaires
    function showComments(articleId) {
        const commentsSection = document.getElementById(`comments-${articleId}`);
        const commentButton = event.target.closest('.btn-comment');
        
        if (commentsSection.style.display === 'none') {
            commentsSection.style.display = 'block';
            commentButton.style.background = '#3498db';
            commentButton.style.color = 'white';
            loadComments(articleId);
        } else {
            commentsSection.style.display = 'none';
            commentButton.style.background = 'white';
            commentButton.style.color = 'inherit';
        }
    }

    function loadComments(articleId) {
        const commentsList = document.querySelector(`#comments-${articleId} .comments-list`);
        
        fetch(`../actions/get_comments.php?article_id=${articleId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayComments(commentsList, data.comments);
                    updateCommentCount(articleId, data.comments.length);
                }
            })
            .catch(error => console.error('Erreur lors du chargement des commentaires:', error));
    }

    function displayComments(commentsList, comments) {
        if (comments.length === 0) {
            commentsList.innerHTML = '<p style="color: #7f8c8d; text-align: center; padding: 20px;">Aucun commentaire pour le moment. Soyez le premier à commenter !</p>';
            return;
        }
        
        commentsList.innerHTML = comments.map(comment => `
            <div class="comment-item">
                <div class="comment-author">${comment.author}</div>
                <div class="comment-content">${comment.content}</div>
                <div class="comment-date">${comment.date}</div>
            </div>
        `).join('');
    }

    function submitComment(articleId) {
        const commentInput = document.querySelector(`#comments-${articleId} .comment-input`);
        const content = commentInput.value.trim();
        
        if (!content) {
            alert('Veuillez saisir un commentaire');
            return;
        }
        
        fetch('../actions/comment_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                article_id: articleId,
                content: content
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                commentInput.value = '';
                loadComments(articleId);
            } else {
                alert('Erreur lors de l\'ajout du commentaire: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur lors de l\'ajout du commentaire:', error);
            alert('Erreur lors de l\'ajout du commentaire');
        });
    }

    function updateCommentCount(articleId, count) {
        const commentButton = document.querySelector(`[onclick="showComments(${articleId})"]`);
        const commentCount = commentButton.querySelector('.comment-count');
        commentCount.textContent = count;
    }

    // Charger les likes et commentaires au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        // Charger les statistiques pour chaque article
        const articles = document.querySelectorAll('.article-card');
        articles.forEach(article => {
            const articleId = article.querySelector('.btn-like').getAttribute('data-article-id');
            loadArticleStats(articleId);
        });
    });

    function loadArticleStats(articleId) {
        fetch(`../actions/get_article_stats.php?article_id=${articleId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateLikeCount(articleId, data.likes);
                    updateCommentCount(articleId, data.comments);
                    
                    // Mettre à jour l'état du bouton like
                    if (data.user_liked) {
                        const likeButton = document.querySelector(`[data-article-id="${articleId}"]`);
                        likeButton.classList.add('liked');
                        const likeIcon = likeButton.querySelector('.like-icon');
                        likeIcon.textContent = '❤️';
                    }
                }
            })
            .catch(error => console.error('Erreur lors du chargement des statistiques:', error));
    }

    function updateLikeCount(articleId, count) {
        const likeButton = document.querySelector(`[data-article-id="${articleId}"]`);
        const likeCount = likeButton.querySelector('.like-count');
        likeCount.textContent = count;
    }

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
    window.onclick = function(event) {
        const modal = document.getElementById('article-modal');
        if (event.target === modal) {
            closeArticleModal();
        }
    }
    </script> 