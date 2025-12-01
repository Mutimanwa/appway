<?php
/**
 * Sidebar adaptée pour PCL Lab Admin
 */
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Start Navigationbar -->
<div class="navigationbar">
    <ul class="vertical-menu">
        <!-- Dashboard -->
        <li class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <a href="<?php echo ROOT_URL; ?>/admin/dashboard.php">
                <i class="ri-dashboard-fill"></i><span>Dashboard</span>
            </a>
        </li>

        <li class="vertical-header">Gestion</li>

        <!-- Utilisateurs -->
        <li class="<?php echo strpos($current_page, 'users') !== false ? 'active' : ''; ?>">
            <a href="javascript:void();">
                <i class="ri-user-fill"></i><span>Utilisateurs</span><i class="ri-arrow-right-s-line"></i>
            </a>
            <ul class="vertical-submenu">
                <li><a href="<?php echo ROOT_URL; ?>/admin/users/list.php">Liste utilisateurs</a></li>
                <li><a href="<?php echo ROOT_URL; ?>/admin/users/create.php">Nouvel utilisateur</a></li>
                <li><a href="<?php echo ROOT_URL; ?>/admin/users/roles.php">Rôles & Permissions</a></li>
            </ul>
        </li>

        <!-- Services -->
        <li class="<?php echo strpos($current_page, 'services') !== false ? 'active' : ''; ?>">
            <a href="javascript:void();">
                <i class="ri-service-fill"></i><span>Services</span><i class="ri-arrow-right-s-line"></i>
            </a>
            <ul class="vertical-submenu">
                <li><a href="<?php echo ROOT_URL; ?>/admin/services/list.php">Tous les services</a></li>
                <li><a href="<?php echo ROOT_URL; ?>/admin/services/create.php">Nouveau service</a></li>
                <li><a href="<?php echo ROOT_URL; ?>/admin/services/categories.php">Catégories</a></li>
            </ul>
        </li>

        <!-- Projets -->
        <li class="<?php echo strpos($current_page, 'projects') !== false ? 'active' : ''; ?>">
            <a href="javascript:void();">
                <i class="ri-projector-fill"></i><span>Projets</span><i class="ri-arrow-right-s-line"></i>
            </a>
            <ul class="vertical-submenu">
                <li><a href="<?php echo ROOT_URL; ?>/admin/projects/list.php">Tous les projets</a></li>
                <li><a href="<?php echo ROOT_URL; ?>/admin/projects/create.php">Nouveau projet</a></li>
                <li><a href="<?php echo ROOT_URL; ?>/admin/projects/categories.php">Catégories</a></li>
                <li><a href="<?php echo ROOT_URL; ?>/admin/projects/status.php">Statuts</a></li>
            </ul>
        </li>

        <!-- Équipe -->
        <li class="<?php echo strpos($current_page, 'team') !== false ? 'active' : ''; ?>">
            <a href="<?php echo ROOT_URL; ?>/admin/team/list.php">
                <i class="ri-team-fill"></i><span>Équipe</span>
            </a>
        </li>

        <!-- Contacts -->
        <li class="<?php echo strpos($current_page, 'contacts') !== false ? 'active' : ''; ?>">
            <a href="javascript:void();">
                <i class="ri-contacts-fill"></i><span>Contacts</span><i class="ri-arrow-right-s-line"></i>
            </a>
            <ul class="vertical-submenu">
                <li><a href="<?php echo ROOT_URL; ?>/admin/contacts/list.php">Tous les contacts</a></li>
                <li><a href="<?php echo ROOT_URL; ?>/admin/contacts/new.php">Nouveaux (5)</a></li>
                <li><a href="<?php echo ROOT_URL; ?>/admin/contacts/messages.php">Messages</a></li>
            </ul>
        </li>

        <!-- Articles -->
        <li class="<?php echo strpos($current_page, 'articles') !== false ? 'active' : ''; ?>">
            <a href="javascript:void();">
                <i class="ri-article-fill"></i><span>Articles</span><i class="ri-arrow-right-s-line"></i>
            </a>
            <ul class="vertical-submenu">
                <li><a href="<?php echo ROOT_URL; ?>/admin/articles/list.php">Tous les articles</a></li>
                <li><a href="<?php echo ROOT_URL; ?>/admin/articles/create.php">Nouvel article</a></li>
                <li><a href="<?php echo ROOT_URL; ?>/admin/articles/categories.php">Catégories</a></li>
                <li><a href="<?php echo ROOT_URL; ?>/admin/articles/comments.php">Commentaires</a></li>
            </ul>
        </li>

        <li class="vertical-header">Système</li>

        <!-- Paramètres -->
        <li class="<?php echo strpos($current_page, 'settings') !== false ? 'active' : ''; ?>">
            <a href="<?php echo ROOT_URL; ?>/admin/settings/general.php">
                <i class="ri-settings-3-fill"></i><span>Paramètres</span>
            </a>
        </li>

        <!-- Rapports -->
        <li class="<?php echo strpos($current_page, 'reports') !== false ? 'active' : ''; ?>">
            <a href="javascript:void();">
                <i class="ri-bar-chart-fill"></i><span>Rapports</span><i class="ri-arrow-right-s-line"></i>
            </a>
            <ul class="vertical-submenu">
                <li><a href="<?php echo ROOT_URL; ?>/admin/reports/analytics.php">Analytics</a></li>
                <li><a href="<?php echo ROOT_URL; ?>/admin/reports/financial.php">Financier</a></li>
                <li><a href="<?php echo ROOT_URL; ?>/admin/reports/performance.php">Performance</a></li>
            </ul>
        </li>

        <!-- Backup -->
        <li class="<?php echo strpos($current_page, 'backup') !== false ? 'active' : ''; ?>">
            <a href="<?php echo ROOT_URL; ?>/admin/backup.php">
                <i class="ri-database-fill"></i><span>Sauvegarde</span>
            </a>
        </li>

        <!-- Déconnexion -->
        <li>
            <a href="<?php echo ROOT_URL; ?>/logout.php" class="text-danger">
                <i class="ri-logout-box-fill"></i><span>Déconnexion</span>
            </a>
        </li>
    </ul>
</div>
<!-- End Navigationbar -->