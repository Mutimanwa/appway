<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="PCL Lab - Panel d'administration">
    <meta name="keywords" content="admin, pcl lab, dashboard, gestion">
    <meta name="author" content="PCL Lab">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>PCL Lab Admin - <?php echo $page_title ?? 'Dashboard'; ?></title>

    <!-- Fevicon -->
    <link rel="shortcut icon" href="<?php echo ROOT_URL; ?>/assets/images/logo-plc.png">

    <!-- Start css -->
    <!-- Switchery css -->
    <link href="<?php echo ROOT_URL; ?>/admin/assets/plugins/switchery/switchery.min.css" rel="stylesheet">
    <!-- Apex css -->
    <link href="<?php echo ROOT_URL; ?>/admin/assets/plugins/apexcharts/apexcharts.css" rel="stylesheet">
    <!-- Slick css -->
    <link href="<?php echo ROOT_URL; ?>/admin/assets/plugins/slick/slick.css" rel="stylesheet">
    <link href="<?php echo ROOT_URL; ?>/admin/assets/plugins/slick/slick-theme.css" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="<?php echo ROOT_URL; ?>/admin/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo ROOT_URL; ?>/admin/assets/css/icons.css" rel="stylesheet" type="text/css">
    <link href="<?php echo ROOT_URL; ?>/admin/assets/css/flag-icon.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo ROOT_URL; ?>/admin/assets/css/style.css" rel="stylesheet" type="text/css">
    <link href="<?php echo ROOT_URL; ?>/admin/assets/plugins/dropzone/dist/dropzone.css" rel="stylesheet">
    <link href="<?php echo ROOT_URL; ?>/admin/assets/plugins/select2/select2.min.css" rel="stylesheet">
    <link href="<?php echo ROOT_URL; ?>/admin/assets/plugins/summernote/summernote-bs4.css" rel="stylesheet">

    <!-- cropper -->
    <link href="<?php echo ROOT_URL; ?>/admin/assets/plugins/cropperjs/cropper.css" rel="stylesheet">
    <link href="<?php echo ROOT_URL; ?>/admin/assets/plugins/cropperjs/main.css" rel="stylesheet">
    <link href="<?php echo ROOT_URL; ?>/admin/assets/plugins/" rel="stylesheet">
    <!-- Custom CSS -->

    <!-- jquery -->
    <script src="<?php echo ROOT_URL; ?>/admin/assets/js/jquery.min.js"></script>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
        }

        .logobar .logo-large img {
            max-height: 40px;
        }

        .topbar .profilebar img {
            border: 2px solid var(--primary-color);
        }

        .breadcrumb-icon {
            color: var(--primary-color);
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eef2f7;
        }
    </style>
    <!-- End css -->
</head>

<body class="vertical-layout">
    <!-- Start Infobar Setting Sidebar -->
    <div id="infobar-settings-sidebar" class="infobar-settings-sidebar">
        <div class="infobar-settings-sidebar-head d-flex w-100 justify-content-between">
            <h4>Paramètres</h4>
            <a href="javascript:void(0)" id="infobar-settings-close" class="infobar-settings-close">
                <i class="ri-close-line menu-hamburger-close"></i>
            </a>
        </div>
        <div class="infobar-settings-sidebar-body">
            <div class="custom-mode-setting">
                <div class="row align-items-center pb-3">
                    <div class="col-8">
                        <h6 class="mb-0">Notifications Email</h6>
                    </div>
                    <div class="col-4"><input type="checkbox" class="js-switch-setting-first" checked /></div>
                </div>
                <div class="row align-items-center pb-3">
                    <div class="col-8">
                        <h6 class="mb-0">Notifications SMS</h6>
                    </div>
                    <div class="col-4"><input type="checkbox" class="js-switch-setting-second" checked /></div>
                </div>
                <div class="row align-items-center pb-3">
                    <div class="col-8">
                        <h6 class="mb-0">Mode Sombre</h6>
                    </div>
                    <div class="col-4"><input type="checkbox" class="js-switch-setting-third" /></div>
                </div>
                <div class="row align-items-center pb-3">
                    <div class="col-8">
                        <h6 class="mb-0">Maintenance</h6>
                    </div>
                    <div class="col-4"><input type="checkbox" class="js-switch-setting-fourth" /></div>
                </div>
            </div>
        </div>
    </div>
    <div class="infobar-settings-sidebar-overlay"></div>
    <!-- End Infobar Setting Sidebar -->

    <!-- Start Containerbar -->
    <div id="containerbar">
        <!-- Start Leftbar -->
        <div class="leftbar">
            <!-- Start Sidebar -->
            <div class="sidebar">
                <!-- Start Logobar -->
                <div class="logobar">
                    <a href="<?php echo ROOT_URL; ?>/admin/dashboard.php" class="logo logo-large">
                        <img src="<?php echo ROOT_URL; ?>/images/logo-plc.png" class="img-fluid" alt="PCL Lab">
                    </a>
                    <a href="<?php echo ROOT_URL; ?>/admin/dashboard.php" class="logo logo-small">
                        <img src="<?php echo ROOT_URL; ?>/images/small_logo.png" class="img-fluid" alt="PCL Lab">
                    </a>
                </div>
                <!-- End Logobar -->

                <!-- Navigationbar (sidebar.php) -->
                <?php include 'sidebar.php'; ?>
            </div>
            <!-- End Sidebar -->
        </div>
        <!-- End Leftbar -->


        <!-- Start Rightbar -->
        <div class="rightbar">
            <!-- Start Topbar Mobile -->
            <div class="topbar-mobile">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="mobile-logobar">
                            <a href="<?php echo ROOT_URL; ?>/admin/dashboard.php" class="mobile-logo">
                                <img src="<?php echo IMAGES_URL; ?>/logo-plc.png" width="100px" class="" alt="PCL Lab">
                            </a>
                        </div>
                        <div class="mobile-togglebar">
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item">
                                    <div class="topbar-toggle-icon">
                                        <a class="topbar-toggle-hamburger" href="javascript:void();">
                                            <i class="ri-more-fill menu-hamburger-horizontal"></i>
                                            <i class="ri-more-2-fill menu-hamburger-vertical"></i>
                                        </a>
                                    </div>
                                </li>
                                <li class="list-inline-item">
                                    <div class="menubar">
                                        <a class="menu-hamburger" href="javascript:void();">
                                            <i class="ri-menu-2-line menu-hamburger-collapse"></i>
                                            <i class="ri-close-line menu-hamburger-close"></i>
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Start Topbar -->
            <div class="topbar">
                <!-- Start row -->
                <div class="row align-items-center">
                    <!-- Start col -->
                    <div class="col-md-12 align-self-center">
                        <div class="togglebar">
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item">
                                    <div class="menubar">
                                        <a class="menu-hamburger" href="javascript:void();">
                                            <i class="ri-menu-2-line menu-hamburger-collapse"></i>
                                            <i class="ri-close-line menu-hamburger-close"></i>
                                        </a>
                                    </div>
                                </li>
                                <li class="list-inline-item">
                                    <div class="searchbar">
                                        <form>
                                            <div class="input-group">
                                                <input type="search" class="form-control" placeholder="Rechercher..."
                                                    aria-label="Search">
                                                <div class="input-group-append">
                                                    <button class="btn" type="submit"><i
                                                            class="ri-search-2-line"></i></button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="infobar">
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item">
                                    <div class="settingbar">
                                        <a href="javascript:void(0)" id="infobar-settings-open" class="infobar-icon">
                                            <i class="ri-settings-line"></i>
                                        </a>
                                    </div>
                                </li>
                                <li class="list-inline-item">
                                    <div class="notifybar">
                                        <div class="dropdown">
                                            <a class="dropdown-toggle infobar-icon" href="#" role="button"
                                                id="notoficationlink" data-toggle="dropdown">
                                                <i class="ri-notification-line"></i>
                                                <span class="live-icon"></span>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right"
                                                aria-labelledby="notoficationlink">
                                                <div class="notification-dropdown-title">
                                                    <h5>Notifications <a href="#">Tout effacer</a></h5>
                                                </div>
                                                <ul class="list-unstyled">
                                                    <li class="media dropdown-item">
                                                        <span class="action-icon badge badge-primary"><i
                                                                class="ri-user-add-line"></i></span>
                                                        <div class="media-body">
                                                            <h5 class="action-title">Nouvel utilisateur</h5>
                                                            <p><span class="timing">Aujourd'hui, 09:05</span></p>
                                                        </div>
                                                    </li>
                                                    <li class="media dropdown-item">
                                                        <span class="action-icon badge badge-success"><i
                                                                class="ri-projector-line"></i></span>
                                                        <div class="media-body">
                                                            <h5 class="action-title">Projet terminé</h5>
                                                            <p><span class="timing">Hier, 14:30</span></p>
                                                        </div>
                                                    </li>
                                                    <li class="media dropdown-item">
                                                        <span class="action-icon badge badge-warning"><i
                                                                class="ri-mail-line"></i></span>
                                                        <div class="media-body">
                                                            <h5 class="action-title">Nouveau message</h5>
                                                            <p><span class="timing">5 Juin, 12:10</span></p>
                                                        </div>
                                                    </li>
                                                </ul>
                                                <div class="notification-dropdown-footer">
                                                    <h5><a href="<?php echo ROOT_URL; ?>/admin/notifications.php">Voir
                                                            tout</a>
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-inline-item">
                                    <div class="profilebar">
                                        <div class="dropdown">
                                            <a class="dropdown-toggle" href="#" role="button" id="profilelink"
                                                data-toggle="dropdown">
                                                <img src="<?php echo ROOT_URL; ?>/uploads/avatars/<?php echo $_SESSION['avatar'] ?? 'default.png'; ?>"
                                                    class="img-fluid rounded-circle"
                                                    alt="<?php echo $_SESSION['nom'] ?? 'Admin'; ?>"
                                                    style="width: 32px; height: 32px;">
                                                <span
                                                    class="live-icon"><?php echo $_SESSION['nom'] ?? 'Admin'; ?></span>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right"
                                                aria-labelledby="profilelink">
                                                <a class="dropdown-item"
                                                    href="<?php echo ROOT_URL; ?>/admin/profile.php">
                                                    <i class="ri-user-line"></i>Mon Profil
                                                </a>
                                                <a class="dropdown-item"
                                                    href="<?php echo ROOT_URL; ?>/admin/settings.php">
                                                    <i class="ri-settings-3-line"></i>Paramètres
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item text-danger"
                                                    href="<?php echo ROOT_URL; ?>/logout.php">
                                                    <i class="ri-shut-down-line"></i>Déconnexion
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- End col -->
                </div>
                <!-- End row -->
            </div>
            <!-- End Topbar -->

            <!-- Start Breadcrumbbar -->
            <div class="breadcrumbbar">
                <div class="row align-items-center">
                    <div class="col-md-8 col-lg-8">
                        <div class="media">
                            <span class="breadcrumb-icon"><i class="ri-dashboard-fill"></i></span>
                            <div class="media-body">
                                <h4 class="page-title">Tableau de bord</h4>
                                <div class="breadcrumb-list">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a
                                                href="<?php echo ROOT_URL; ?>/admin/dashboard.php">Accueil</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Breadcrumbbar -->