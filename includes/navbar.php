<!-- main header -->
<header class="main-header home-9">
    <div class="outer-container">
        <div class="container">
            <div class="main-box clearfix">
                <div class="logo-box pull-left py-5">
                    <a href="#" class="logo ">
                        <img src="<?= IMAGES_URL ?>/logo-plc.png" width="150px" alt="PLC Lab" title="PLC Lab">

                    </a>
                </div>
                <div class="menu-area pull-right clearfix">
                    <!--Mobile Navigation Toggler-->
                    <div class="mobile-nav-toggler">
                        <i class="icon-bar"></i>
                        <i class="icon-bar"></i>
                        <i class="icon-bar"></i>
                    </div>
                    <nav class="main-menu navbar-expand-md navbar-light">
                        <div class="collapse navbar-collapse show clearfix" id="navbarSupportedContent">
                            <?php
                            $current_page = basename($_SERVER['PHP_SELF']);
                            ?>

                            <ul class="navigation clearfix">
                                <li class="<?= ($current_page == 'index.php') ? 'current' : '' ?>"><a
                                        href="index.php">Accueil</a></li>
                                <li class="<?= ($current_page == 'service.php') ? 'current' : '' ?>"><a
                                        href="service.php">Services</a></li>
                                <li
                                    class="dropdown <?= in_array($current_page, ['notre-equipe.php', 'about.php']) ? 'current' : '' ?>">
                                    <a href="#">À propos</a>
                                    <ul>
                                        <li class="<?= ($current_page == 'notre-equipe.php') ? 'current' : '' ?>"><a
                                                href="notre-equipe.php">Notre Équipe</a></li>
                                        <li class="<?= ($current_page == 'about.php') ? 'current' : '' ?>"><a
                                                href="about.php">Notre Histoire</a></li>
                                    </ul>
                                </li>
                                <li class="<?= ($current_page == 'portfolio.php') ? 'current' : '' ?>"><a
                                        href="portfolio.php">Portfolio</a></li>
                                <li class="<?= ($current_page == 'blog.php') ? 'current' : '' ?>"><a
                                        href="blog.php">Blog</a></li>
                                <li class="<?= ($current_page == 'contact.php') ? 'current' : '' ?>"><a
                                        href="contact.php">Contact</a></li>
                            </ul>
                        </div>
                    </nav>
                    <div class="btn-box"><a href="contact.php">Démarrer un projet</a></div>
                </div>
            </div>
        </div>
    </div>

    <!--sticky Header-->
    <div class="sticky-header">
        <div class="container clearfix">
            <figure class="logo-box ">
                <a href="index.php">
                    <img src="<?= IMAGES_URL ?>/logo-plc.png" width="100px" alt="PLC Lab" title="PLC Lab">
                </a>
            </figure>
            <div class="menu-area ">
                <nav class="main-menu clearfix">
                    <!--Keep This Empty / Menu will come through Javascript-->
                </nav>
            </div>
        </div>
    </div>
</header>
<!-- main-header end -->

<!-- Mobile Menu  -->
<div class="mobile-menu">
    <div class="menu-backdrop"></div>
    <div class="close-btn"><i class="fas fa-times"></i></div>

    <nav class="menu-box">
        <div class="nav-logo"><a href="index.php">
                <img src="<?= IMAGES_URL ?>/logo-plc.png" width="100" alt="PLC Lab" title="PLC Lab">
            </a></div>
        <div class="menu-outer"><!--Here Menu Will Come Automatically Via Javascript / Same Menu as in Header-->
        </div>
        <div class="contact-info">
            <h4>Coordonnées</h4>
            <ul>
                <li>Bujumbura , Burundi </li>
                <li><a href="tel:+25761103320">+257 61 10 33 20</a></li>
                <li><a href="mailto:plccreativeroom7@gmail.com">plccreativeroom7@gmail.com</a></li>
            </ul>
        </div>
        <div class="social-links">
            <ul class="clearfix">
                <li><a href="<?= SITE_FACEBOOK ?>" target="_blank"><span class="fab fa-facebook-square"></span></a></li>
                <li><a href="<?= SITE_X ?>" target="_blank"><span class="fab fa-twitter"></span></a></li>
                <li><a href="<?= SITE_LINKEDIN ?>" target="_blank"><span class="fab fa-linkedin-in"></span></a></li>
                <li><a href="<?= SITE_GITHUB ?>" target="_blank"><span class="fab fa-github"></span></a></li>
            </ul>
        </div>
    </nav>
</div>
<!-- End Mobile Menu -->