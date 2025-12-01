<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'admin/includes/project-functions.php';

$page_title = "Portfolio - Nos Réalisations";

// Récupérer les projets publiés (statut completed ou delivered)
$projects_result = getAllProjects(1, 12, ['status' => 'completed']);
$projects = $projects_result['projects'];

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
                <h1>Nos Réalisations</h1>
                <p>Découvrez une sélection de nos projets de développement les plus significatifs.</p>
            </div>
            <ul class="bread-crumb pull-right">
                <li><a href="index.php">Accueil</a></li>
                <li>Portfolio</li>
            </ul>
        </div>
    </div>
</section>
<!-- page-title end -->

<!-- portfolio-section -->
<section class="portfolio-section">
    <div class="container">
        <div class="sortable-masonry">
            <div class="filters">
                <ul class="filter-tabs filter-btns centred clearfix">
                    <li class="active filter" data-role="button" data-filter=".all">Tous les projets</li>
                    <li class="filter" data-role="button" data-filter=".web">Développement Web</li>
                    <li class="filter" data-role="button" data-filter=".mobile">Applications Mobiles</li>
                    <li class="filter" data-role="button" data-filter=".cloud">Solutions Cloud</li>
                    <li class="filter" data-role="button" data-filter=".desktop">Applications Desktop</li>
                </ul>
            </div>
            <div class="items-container row clearfix">
                <?php if (empty($projects)): ?>
                    <div class="col-12 text-center py-5">
                        <div class="text-muted">
                            <i class="ri-inbox-line display-4"></i>
                            <h5 class="mt-3">Aucun projet disponible pour le moment</h5>
                            <p>Nos projets sont en cours de préparation. Revenez bientôt!</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($projects as $project): ?>
                        <?php
                        // Déterminer la classe CSS en fonction de la catégorie
                        $category_class = '';
                        switch ($project['category']) {
                            case 'web':
                                $category_class = 'web';
                                break;
                            case 'mobile':
                                $category_class = 'mobile';
                                break;
                            case 'cloud':
                                $category_class = 'cloud';
                                break;
                            case 'desktop':
                                $category_class = 'desktop';
                                break;
                            case 'ia':
                                $category_class = 'web';
                                break;
                            case 'database':
                                $category_class = 'cloud';
                                break;
                            default:
                                $category_class = 'web';
                        }

                        // Obtenir la première image
                        $first_image = !empty($project['images']) ? $project['images'][0] : 'default.jpg';
                        $image_url = !empty($project['images']) ?
                            UPLOAD_URL . 'projects/' . $first_image :
                            IMAGES_URL . '/images/gallery/default-project.jpg';

                        // Limiter la description
                        $short_description = strlen($project['description']) > 150 ?
                            substr(strip_tags($project['description']), 0, 150) . '...' :
                            strip_tags($project['description']);
                        ?>

                        <div class="col-lg-6 col-md-6 col-sm-12 masonry-item small-column all <?php echo $category_class; ?>">
                            <div class="portfolio-block-one">
                                <div class="image-box">
                                    <figure class="image">
                                        <img src="<?php echo $image_url; ?>"
                                            alt="<?php echo htmlspecialchars($project['title']); ?>">
                                    </figure>
                                    <div class="content-box">
                                        <div class="inner text-white">
                                            <div class="title">
                                                <?php
                                                $category_labels = [
                                                    'web' => 'Développement Web',
                                                    'mobile' => 'Application Mobile',
                                                    'desktop' => 'Application Desktop',
                                                    'cloud' => 'Solution Cloud',
                                                    'ia' => 'Intelligence Artificielle',
                                                    'database' => 'Base de Données'
                                                ];
                                                echo $category_labels[$project['category']] ?? 'Projet';
                                                ?>
                                            </div>
                                            <h3>
                                                <a href="project-details.php?id=<?php echo $project['id']; ?>">
                                                    <?php echo htmlspecialchars($project['title']); ?>
                                                </a>
                                            </h3>
                                            <p class="text-warning"><?php echo $short_description; ?></p>

                                            <?php if (!empty($project['technologies'])): ?>
                                                <div class="technologies">
                                                    <?php
                                                    // Afficher seulement 3 technologies maximum
                                                    $tech_count = 0;
                                                    foreach ($project['technologies'] as $tech):
                                                        if ($tech_count < 3):
                                                            ?>
                                                            <span class="tech-tag"><?php echo $tech; ?></span>
                                                            <?php
                                                            $tech_count++;
                                                        endif;
                                                    endforeach;
                                                    ?>
                                                    <?php if (count($project['technologies']) > 3): ?>
                                                        <span
                                                            class="tech-tag">+<?php echo count($project['technologies']) - 3; ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($project['featured']): ?>
                                                <div class="mt-2">
                                                    <span class="badge badge-warning">
                                                        <i class="ri-star-fill mr-1"></i>Projet vedette
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<!-- portfolio-section end -->

<!-- cta-section -->
<section class="cta-section">
    <div class="container">
        <div class="inner-container">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-12 col-sm-12 content-column">
                    <div class="content-box">
                        <h2>Prêt à Lancer Votre Projet ?</h2>
                        <p>Discutons de votre idée et transformons-la en une solution numérique performante.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 col-sm-12 btn-column">
                    <div class="btn-box">
                        <a href="contact.php" class="theme-btn">Démarrer un projet</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- cta-section end -->


<!-- Ajoutez ces scripts pour Isotope -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/imagesloaded/4.1.4/imagesloaded.pkgd.min.js"></script>

<script>
    $(document).ready(function () {
        // Filtrage des projets
        $('.filter').click(function () {
            $('.filter').removeClass('active');
            $(this).addClass('active');

            var filterValue = $(this).attr('data-filter');
            $('.items-container').isotope({ filter: filterValue });
        });

        // Initialiser Isotope
        $('.items-container').isotope({
            itemSelector: '.masonry-item',
            percentPosition: true,
            masonry: {
                columnWidth: '.masonry-item'
            }
        });
    });
</script>
<?php include 'includes/footer.php'; ?>