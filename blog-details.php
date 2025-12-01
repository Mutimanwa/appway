<?php
include_once "includes/functions.php";
include_once "config/config.php";
include_once "config/database.php";
include_once "admin/includes/article-functions.php";

if (!isset($_GET['slug'])) {
    header("Location: blog.php");
    exit();
}

$slug = nettoyer_input($_GET['slug']);
$article = getArticleBySlug($slug);

if (!$article) {
    header("HTTP/1.0 404 Not Found");
    include '404.php';
    exit();
}

// Récupérer les commentaires
$pdo = getConnexion();
$stmt = $pdo->prepare("SELECT * FROM commentaires WHERE article_id = ? AND is_unabled = 0 ORDER BY created_at DESC");
$stmt->execute([$article['id']]);
$commentaires = $stmt->fetchAll();

// Récupérer les articles similaires
$stmt = $pdo->prepare("
    SELECT a.*, u.first_name, u.last_name 
    FROM articles a 
    LEFT JOIN users u ON a.author_id = u.id 
    WHERE a.category = ? AND a.status = 'published' AND a.id != ? 
    ORDER BY a.created_at DESC 
    LIMIT 3
");
$stmt->execute([$article['category'], $article['id']]);
$related_articles = $stmt->fetchAll();

// Décoder les données JSON pour les articles similaires
foreach ($related_articles as &$related) {
    $related['tags'] = json_decode($related['tags'], true) ?? [];
    $related['featured_image_data'] = json_decode($related['featured_image'], true) ?? [];
}

include 'includes/header.php';
?>

<!-- page-title -->
<section class="page-title" style="background-image: url(images/background/pagetitle-bg.png);">
    <div class="anim-icons">
        <div class="icon icon-1"><img src="images/icons/anim-icon-17.png" alt=""></div>
        <div class="icon icon-2 rotate-me"><img src="images/icons/anim-icon-18.png" alt=""></div>
        <div class="icon icon-3 rotate-me"><img src="images/icons/anim-icon-19.png" alt=""></div>
        <div class="icon icon-4"></div>
    </div>
    <div class="container">
        <div class="content-box clearfix">
            <div class="title-box pull-left">
                <h1><?php echo htmlspecialchars($article['title']); ?></h1>
                <div class="article-meta">
                    <span><i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($article['author_first_name'] . ' ' . $article['author_last_name']); ?></span>
                    <span><i class="fas fa-calendar-alt"></i>
                        <?php echo date('d M Y', strtotime($article['created_at'])); ?></span>
                    <span><i class="fas fa-eye"></i> <?php echo $article['views']; ?> vues</span>
                    <span><i class="fas fa-heart"></i> <?php echo $article['likes'] ?? 0; ?> likes</span>
                </div>
            </div>
            <ul class="bread-crumb pull-right">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="blog.php">Blog</a></li>
                <li><?php echo htmlspecialchars($article['title']); ?></li>
            </ul>
        </div>
    </div>
</section>
<!-- page-title end -->

<!-- blog-details -->
<section class="sidebar-page-container">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-12 col-sm-12 content-side">
                <div class="blog-details-content">
                    <?php if (!empty($article['featured_image_data']['url'])): ?>
                        <div class="image-box mb-4">
                            <img src="<?php echo UPLOAD_URL . 'articles/' . $article['featured_image_data']['url']; ?>"
                                alt="<?php echo htmlspecialchars($article['featured_image_data']['alt'] ?? $article['title']); ?>"
                                class="img-fluid rounded">
                            <?php if (!empty($article['featured_image_data']['caption'])): ?>
                                <div class="image-caption mt-2 text-center text-muted">
                                    <?php echo htmlspecialchars($article['featured_image_data']['caption']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="article-content">
                        <div class="excerpt mb-4">
                            <p class="lead"><?php echo htmlspecialchars($article['excerpt']); ?></p>
                        </div>

                        <div class="main-content">
                            <?php echo $article['content']; ?>
                        </div>

                        <?php if (!empty($article['tags'])): ?>
                            <div class="tags-section mt-4">
                                <h4>Tags:</h4>
                                <div class="tags-list">
                                    <?php foreach ($article['tags'] as $tag): ?>
                                        <a href="blog.php?tag=<?php echo urlencode($tag); ?>" class="badge badge-secondary">
                                            <?php echo htmlspecialchars($tag); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="share-section mt-4">
                            <h4>Partager cet article:</h4>
                            <div class="social-share">
                                <a href="#"
                                    onclick="shareOnFacebook('<?php echo ROOT_URL; ?>/article.php?slug=<?php echo $article['slug']; ?>', '<?php echo htmlspecialchars($article['title']); ?>')"
                                    class="social-icon facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#"
                                    onclick="shareOnTwitter('<?php echo htmlspecialchars($article['title']); ?>', '<?php echo ROOT_URL; ?>/article.php?slug=<?php echo $article['slug']; ?>')"
                                    class="social-icon twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="#"
                                    onclick="shareOnLinkedIn('<?php echo ROOT_URL; ?>/article.php?slug=<?php echo $article['slug']; ?>', '<?php echo htmlspecialchars($article['title']); ?>', '<?php echo htmlspecialchars($article['excerpt']); ?>')"
                                    class="social-icon linkedin">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Commentaires -->
                    <div class="comments-section mt-5">
                        <h3>Commentaires (<?php echo count($commentaires); ?>)</h3>

                        <?php if (empty($commentaires)): ?>
                            <p>Soyez le premier à commenter cet article !</p>
                        <?php else: ?>
                            <div class="comments-list">
                                <?php foreach ($commentaires as $comment): ?>
                                    <div class="comment-item mb-4">
                                        <div class="comment-header">
                                            <strong><?php echo htmlspecialchars($comment['name']); ?></strong>
                                            <span class="text-muted">
                                                <?php echo date('d M Y H:i', strtotime($comment['created_at'])); ?>
                                            </span>
                                        </div>
                                        <div class="comment-body">
                                            <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Formulaire de commentaire -->
                        <div class="comment-form mt-5">
                            <h3>Laisser un commentaire</h3>
                            <form id="comment-form" action="includes/add-comment.php" method="post">
                                <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input type="text" name="name" class="form-control" placeholder="Votre nom"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input type="email" name="email" class="form-control"
                                                placeholder="Votre email" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <textarea name="content" class="form-control" rows="5"
                                                placeholder="Votre commentaire" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">Poster le commentaire</button>
                                    </div>
                                </div>
                            </form>
                            <div id="comment-message" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4 col-md-12 col-sm-12 sidebar-side">
                <div class="sidebar">
                    <!-- A propos de l'auteur -->
                    <div class="sidebar-widget author-widget">
                        <h3 class="sidebar-title">À propos de l'auteur</h3>
                        <div class="widget-content">
                            <div class="author-info">
                                <div class="author-avatar">
                                    <img src="images/resource/admin-1.png"
                                        alt="<?php echo htmlspecialchars($article['author_first_name'] . ' ' . $article['author_last_name']); ?>">
                                </div>
                                <h4><?php echo htmlspecialchars($article['author_first_name'] . ' ' . $article['author_last_name']); ?>
                                </h4>
                                <p>Expert en développement logiciel avec plus de