<?php
include_once "config/config.php";
include_once "config/database.php";
include_once "includes/functions.php";
include_once "admin/includes/article-functions.php";

// Récupérer les paramètres de pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 6; // Nombre d'articles par page

// Récupérer les filtres
$filters = [];
if (isset($_GET['category']) && $_GET['category']) {
    $filters['category'] = $_GET['category'];
}
if (isset($_GET['search']) && $_GET['search']) {
    $filters['search'] = $_GET['search'];
}
if (isset($_GET['tag']) && $_GET['tag']) {
    // Pour filtrer par tag, nous devons modifier la logique
    $filters['search'] = $_GET['tag']; // Recherche simple par tag
}

// Récupérer uniquement les articles publiés
$filters['status'] = 'published';

// Récupérer les articles avec pagination
$result = getAllArticles($page, $per_page, $filters);
$articles = $result['articles'];
$total_pages = $result['total_pages'];
$total_articles = $result['total'];

// Récupérer les statistiques pour les catégories
$stats = getArticlesStats();

// Récupérer les articles récents
$recent_articles = getAllArticles(1, 3, ['status' => 'published'])['articles'];

// Récupérer tous les tags utilisés
$all_tags = [];
foreach ($articles as $article) {
    if (!empty($article['tags']) && is_array($article['tags'])) {
        $all_tags = array_merge($all_tags, $article['tags']);
    }
}
$all_tags = array_unique($all_tags);
$popular_tags = array_slice($all_tags, 0, 8); // Limiter à 8 tags

include 'includes/header.php';
?>

<!-- page-title -->
<section class="page-title" style="background-image: url(assets/images/background/pagetitle-bg.png);">
    <div class="anim-icons">
        <div class="icon icon-1"><img src="<?= IMAGES_URL; ?>/icons/anim-icon-17.png" alt=""></div>
        <div class="icon icon-2 rotate-me"><img src="<?= IMAGES_URL; ?>/icons/anim-icon-18.png" alt=""></div>
        <div class="icon icon-3 rotate-me"><img src="<?= IMAGES_URL; ?>/icons/anim-icon-19.png" alt=""></div>
        <div class="icon icon-4"></div>
    </div>
    <div class="container">
        <div class="content-box clearfix">
            <div class="title-box pull-left">
                <h1>Notre Blog</h1>
                <p>Actualités, conseils et tendances en développement logiciel.</p>
            </div>
            <ul class="bread-crumb pull-right">
                <li>Blog</li>
                <li><a href="index.php">Accueil</a></li>
            </ul>
        </div>
    </div>
</section>
<!-- page-title end -->

<!-- blog-classic -->
<section class="sidebar-page-container">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-12 col-sm-12 content-side">
                <div class="blog-content" id="articles-container">
                    <?php if (empty($articles)): ?>
                        <div class="alert alert-info">
                            <p>Aucun article trouvé. Essayez une autre recherche ou catégorie.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($articles as $article): ?>
                            <div class="single-blog-content">
                                <div class="inner-box">
                                    <?php if (!empty($article['featured_image_data']['url'])): ?>
                                        <figure class="image-box">
                                            <a href="article.php?slug=<?php echo $article['slug']; ?>">
                                                <img src="<?php echo UPLOAD_URL . 'articles/' . $article['featured_image_data']['url']; ?>"
                                                    alt="<?php echo htmlspecialchars($article['featured_image_data']['alt'] ?? $article['title']); ?>">
                                            </a>
                                        </figure>
                                    <?php endif; ?>
                                    <div class="lower-content">
                                        <div class="upper-box">
                                            <div class="post-date">
                                                <i class="fas fa-calendar-alt"></i>
                                                <?php echo date('d M Y', strtotime($article['created_at'])); ?>
                                            </div>
                                            <h3>
                                                <a href="article.php?slug=<?php echo $article['slug']; ?>">
                                                    <?php echo htmlspecialchars($article['title']); ?>
                                                </a>
                                            </h3>
                                            <div class="text">
                                                <?php
                                                $excerpt = strip_tags($article['excerpt']);
                                                echo strlen($excerpt) > 200 ? substr($excerpt, 0, 200) . '...' : $excerpt;
                                                ?>
                                            </div>
                                            <?php if (!empty($article['tags'])): ?>
                                                <div class="article-tags mt-2">
                                                    <?php foreach (array_slice($article['tags'], 0, 3) as $tag): ?>
                                                        <a href="blog.php?tag=<?php echo urlencode($tag); ?>"
                                                            class="badge badge-secondary mr-1">
                                                            <?php echo htmlspecialchars($tag); ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="lower-box clearfix">
                                            <div class="left-content pull-left">
                                                <figure class="admin-image">
                                                    <img src="<?= IMAGES_URL; ?>/resource/admin-1.png"
                                                        alt="<?php echo htmlspecialchars($article['author_first_name'] . ' ' . $article['author_last_name']); ?>">
                                                </figure>
                                                <span class="admin-name">
                                                    par
                                                    <?php echo htmlspecialchars($article['author_first_name'] . ' ' . $article['author_last_name']); ?>
                                                </span>
                                            </div>
                                            <ul class="right-content pull-right">
                                                <li>
                                                    <a href="#">
                                                        <?php echo $article['views'] ?? 0; ?>
                                                        <i class="far fa-eye"></i>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#">
                                                        <?php echo $article['likes'] ?? 0; ?>
                                                        <i class="far fa-heart"></i>
                                                    </a>
                                                </li>
                                                <li class="share">
                                                    <a href="#"><i class="fas fa-share-alt"></i></a>
                                                    <ul class="social-links">
                                                        <li>
                                                            <a href="#"
                                                                onclick="shareOnFacebook('<?php echo ROOT_URL; ?>/article.php?slug=<?php echo $article['slug']; ?>', '<?php echo htmlspecialchars($article['title']); ?>')">
                                                                <i class="fab fa-facebook-f"></i>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#"
                                                                onclick="shareOnTwitter('<?php echo htmlspecialchars($article['title']); ?>', '<?php echo ROOT_URL; ?>/article.php?slug=<?php echo $article['slug']; ?>')">
                                                                <i class="fab fa-twitter"></i>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#"
                                                                onclick="shareOnLinkedIn('<?php echo ROOT_URL; ?>/article.php?slug=<?php echo $article['slug']; ?>', '<?php echo htmlspecialchars($article['title']); ?>', '<?php echo htmlspecialchars($article['excerpt']); ?>')">
                                                                <i class="fab fa-linkedin-in"></i>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-wrapper centred">
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                                <li>
                                    <a
                                        href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?><?php echo isset($_GET['tag']) ? '&tag=' . $_GET['tag'] : ''; ?>">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li <?php echo ($i == $page) ? 'class="active"' : ''; ?>>
                                    <a
                                        href="?page=<?php echo $i; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?><?php echo isset($_GET['tag']) ? '&tag=' . $_GET['tag'] : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li>
                                    <a
                                        href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?><?php echo isset($_GET['tag']) ? '&tag=' . $_GET['tag'] : ''; ?>">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4 col-md-12 col-sm-12 sidebar-side">
                <div class="sidebar">
                    <!-- Recherche -->
                    <div class="sidebar-search sidebar-widget">
                        <div class="search-form">
                            <form id="search-form" action="blog.php" method="get">
                                <div class="form-group">
                                    <input type="search" name="search" id="search-input" placeholder="Rechercher..."
                                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                    <button type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Catégories -->
                    <div class="sidebar-categories sidebar-widget">
                        <h3 class="sidebar-title">Catégories</h3>
                        <div class="widget-content">
                            <ul>
                                <?php
                                $categories = [
                                    'tech' => 'Technologie',
                                    'business' => 'Business',
                                    'tutorial' => 'Tutoriel',
                                    'news' => 'Actualités'
                                ];

                                $category_counts = [];
                                if (isset($stats['by_category'])) {
                                    foreach ($stats['by_category'] as $cat) {
                                        $category_counts[$cat['category']] = $cat['count'];
                                    }
                                }

                                foreach ($categories as $key => $label):
                                    $count = $category_counts[$key] ?? 0;
                                    if ($count > 0):
                                        ?>
                                        <li>
                                            <a href="blog.php?category=<?php echo $key; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $key) ? 'class="active"' : ''; ?>>
                                                <?php echo $label; ?>
                                                <span>(<?php echo $count; ?>)</span>
                                            </a>
                                        </li>
                                        <?php
                                    endif;
                                endforeach;
                                ?>
                                <li>
                                    <a href="blog.php" <?php echo !isset($_GET['category']) ? 'class="active"' : ''; ?>>
                                        Toutes les catégories
                                        <span>(<?php echo $total_articles; ?>)</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Articles Récents -->
                    <div class="sidebar-post sidebar-widget">
                        <h3 class="sidebar-title">Articles Récents</h3>
                        <div class="widget-content">
                            <?php foreach ($recent_articles as $recent): ?>
                                <div class="post">
                                    <?php if (!empty($recent['featured_image_data']['url'])): ?>
                                        <figure class="image">
                                            <a href="article.php?slug=<?php echo $recent['slug']; ?>">
                                                <img src="<?php echo UPLOAD_URL . 'articles/' . $recent['featured_image_data']['url']; ?>"
                                                    alt="<?php echo htmlspecialchars($recent['featured_image_data']['alt'] ?? $recent['title']); ?>">
                                            </a>
                                        </figure>
                                    <?php endif; ?>
                                    <h5>
                                        <a href="article.php?slug=<?php echo $recent['slug']; ?>">
                                            <?php echo htmlspecialchars($recent['title']); ?>
                                        </a>
                                    </h5>
                                    <span class="post-date">
                                        <?php echo date('d M Y', strtotime($recent['created_at'])); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Tags Populaires -->
                    <div class="sidebar-tags sidebar-widget">
                        <h3 class="sidebar-title">Tags Populaires</h3>
                        <div class="widget-content">
                            <ul class="tag-list clearfix">
                                <?php foreach ($popular_tags as $tag): ?>
                                    <li>
                                        <a href="blog.php?tag=<?php echo urlencode($tag); ?>" <?php echo (isset($_GET['tag']) && $_GET['tag'] == $tag) ? 'class="active"' : ''; ?>>
                                            <?php echo htmlspecialchars($tag); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Newsletter -->
                    <div class="sidebar-newsletter sidebar-widget">
                        <h3 class="sidebar-title">Newsletter</h3>
                        <div class="widget-content">
                            <p>Recevez nos derniers articles et conseils en développement.</p>
                            <form id="newsletter-form" action="includes/subscribe.php" method="post"
                                class="newsletter-form">
                                <div class="form-group">
                                    <input type="email" name="email" id="newsletter-email" placeholder="Votre email"
                                        required>
                                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                                </div>
                                <div id="newsletter-message" class="mt-2"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- blog-classic end -->

<!-- clients-style-four -->
<!-- <section class="clients-style-four style-five">
    <div class="image-layer" style="background-image: url('images/icons/layer-image-7.png');"></div>
    <div class="container">
        <div class="clients-carousel owl-carousel owl-theme owl-dots-none">
            <figure class="image-box"><a href="#"><img src="<?= IMAGES_URL; ?>/clients/client-1.png"
                        alt="Client 1"></a></figure>
            <figure class="image-box"><a href="#"><img src="<?= IMAGES_URL; ?>/clients/client-2.png"
                        alt="Client 2"></a></figure>
            <figure class="image-box"><a href="#"><img src="<?= IMAGES_URL; ?>/clients/client-3.png"
                        alt="Client 3"></a></figure>
            <figure class="image-box"><a href="#"><img src="<?= IMAGES_URL; ?>/clients/client-4.png"
                        alt="Client 4"></a></figure>
        </div>
    </div>
</section> -->
<!-- clients-style-four end -->

<script>
    // Fonctions de partage sur les réseaux sociaux
    function shareOnFacebook(url, title) {
        window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url) + '&quote=' + encodeURIComponent(title),
            'facebook-share-dialog', 'width=800,height=600');
        return false;
    }

    function shareOnTwitter(text, url) {
        window.open('https://twitter.com/intent/tweet?text=' + encodeURIComponent(text) + '&url=' + encodeURIComponent(url),
            'twitter-share-dialog', 'width=800,height=600');
        return false;
    }

    function shareOnLinkedIn(url, title, summary) {
        window.open('https://www.linkedin.com/shareArticle?mini=true&url=' + encodeURIComponent(url) +
            '&title=' + encodeURIComponent(title) + '&summary=' + encodeURIComponent(summary),
            'linkedin-share-dialog', 'width=800,height=600');
        return false;
    }

    // Gestion de la newsletter avec AJAX
    $(document).ready(function () {
        // Form validation for search
        $('#search-form').on('submit', function (e) {
            var searchTerm = $('#search-input').val().trim();
            if (searchTerm.length < 2) {
                alert('Veuillez saisir au moins 2 caractères pour la recherche.');
                e.preventDefault();
                return false;
            }
        });

        // Newsletter subscription with AJAX
        $('#newsletter-form').on('submit', function (e) {
            e.preventDefault();

            var email = $('#newsletter-email').val().trim();
            var messageDiv = $('#newsletter-message');

            if (!isValidEmail(email)) {
                messageDiv.html('<div class="alert alert-danger">Veuillez saisir une adresse email valide.</div>');
                return false;
            }

            // Show loading
            messageDiv.html('<div class="alert alert-info">Envoi en cours...</div>');

            // Send AJAX request
            $.ajax({
                url: 'includes/subscribe.php',
                method: 'POST',
                data: {
                    email: email,
                    action: 'subscribe'
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        messageDiv.html('<div class="alert alert-success">' + response.message + '</div>');
                        $('#newsletter-email').val('');
                    } else {
                        messageDiv.html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function () {
                    messageDiv.html('<div class="alert alert-danger">Une erreur est survenue. Veuillez réessayer.</div>');
                }
            });
        });

        // Validation d'email
        function isValidEmail(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Smooth scrolling for pagination links
        $('.pagination a').on('click', function (e) {
            var href = $(this).attr('href');
            if (href && href !== '#') {
                $('html, body').animate({
                    scrollTop: $('.blog-content').offset().top - 100
                }, 500);
            }
        });

        // Highlight active filters
        $('.sidebar a[href*="category="], .sidebar a[href*="tag="]').each(function () {
            var href = $(this).attr('href');
            var currentUrl = window.location.href;
            if (currentUrl.includes(href.split('?')[1])) {
                $(this).addClass('active');
            }
        });

        // Auto-hide messages after 5 seconds
        setTimeout(function () {
            $('.alert').fadeOut('slow');
        }, 5000);
    });

// Infinite scroll option (décommenter si besoin)
/*
let page = <?php echo $page; ?>;
    let loading = false;
    let hasMore = page < <?php echo $total_pages; ?>;

    $(window).scroll(function () {
        if ($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
            if (!loading && hasMore) {
                loadMoreArticles();
            }
        }
    });

    function loadMoreArticles() {
        loading = true;
        page++;

        // Show loading indicator
        $('#articles-container').append('<div class="loading-indicator">Chargement...</div>');

        $.ajax({
            url: 'includes/load-articles.php',
            method: 'GET',
            data: {
                page: page,
                category: '<?php echo isset($_GET["category"]) ? $_GET["category"] : ""; ?>',
                search: '<?php echo isset($_GET["search"]) ? $_GET["search"] : ""; ?>',
                tag: '<?php echo isset($_GET["tag"]) ? $_GET["tag"] : ""; ?>'
            },
            success: function (response) {
                $('.loading-indicator').remove();
                if (response.html) {
                    $('#articles-container').append(response.html);
                    hasMore = response.hasMore;
                }
                loading = false;
            },
            error: function () {
                $('.loading-indicator').remove();
                loading = false;
            }
        });
    }
*/
</script>

<style>
    /* Styles additionnels */
    .single-blog-content {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        margin-bottom: 30px;
    }

    .single-blog-content:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .image-box img {
        height: 250px;
        object-fit: cover;
        width: 100%;
        transition: transform 0.3s ease;
    }

    .image-box:hover img {
        transform: scale(1.05);
    }

    .article-tags .badge {
        font-size: 0.8rem;
        padding: 5px 10px;
        margin-right: 5px;
        transition: background-color 0.3s ease;
    }

    .article-tags .badge:hover {
        background-color: #007bff;
        color: white;
    }

    .sidebar a.active {
        color: #007bff;
        font-weight: bold;
    }

    .tag-list li a.active {
        background-color: #007bff;
        color: white;
    }

    .loading-indicator {
        text-align: center;
        padding: 20px;
        color: #666;
        font-style: italic;
    }

    .share .social-links {
        display: none;
        position: absolute;
        background: white;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        z-index: 100;
    }

    .share:hover .social-links {
        display: flex;
    }

    .social-links li {
        margin: 0 5px;
    }

    .social-links a {
        color: #666;
        transition: color 0.3s ease;
    }

    .social-links a:hover {
        color: #007bff;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .image-box img {
            height: 200px;
        }

        .lower-box {
            flex-direction: column;
        }

        .left-content,
        .right-content {
            width: 100%;
            text-align: center;
            margin-bottom: 10px;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>