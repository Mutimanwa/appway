<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// les fichiers de l'admin
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est admin
// verifier_session(['admin']);

// Récupérer les statistiques
$pdo = getConnexion();
$stats = getDashboardStats($pdo);

// Définir le titre de la page
$page_title = "Tableau de bord";
?>
<?php include 'includes/header.php'; ?>


<!-- Start Contentbar -->
<div class="contentbar">
    <!-- Start row -->
    <div class="row">
        <!-- Start col -->
        <div class="col-lg-12 col-xl-4">
            <div class="card m-b-30">
                <div class="card-header">
                    <h5 class="card-title mb-0">Utilisateurs</h5>
                </div>
                <div class="card-body pb-0">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h4><?php echo $stats['total_users'] ?? '0'; ?></h4>
                        </div>
                        <div class="col-6 text-right">
                            <p class="mb-0"><i
                                    class="ri-arrow-right-up-line text-success align-middle font-18 mr-1"></i>12%
                            </p>
                            <p class="mb-0">Ce mois</p>
                        </div>
                    </div>
                    <div id="apex-line-chart1"></div>
                </div>
            </div>
        </div>
        <!-- End col -->

        <!-- Start col -->
        <div class="col-lg-12 col-xl-4">
            <div class="card m-b-30">
                <div class="card-header">
                    <h5 class="card-title mb-0">Projets</h5>
                </div>
                <div class="card-body pb-0">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h4><?php echo $stats['total_projects'] ?? '0'; ?></h4>
                        </div>
                        <div class="col-6 text-right">
                            <p class="mb-0"><i
                                    class="ri-arrow-right-up-line text-success align-middle font-18 mr-1"></i>25%
                            </p>
                            <p class="mb-0">Ce mois</p>
                        </div>
                    </div>
                    <div id="apex-line-chart2"></div>
                </div>
            </div>
        </div>
        <!-- End col -->

        <!-- Start col -->
        <div class="col-lg-12 col-xl-4">
            <div class="card m-b-30">
                <div class="card-header">
                    <h5 class="card-title mb-0">Contacts</h5>
                </div>
                <div class="card-body pb-0">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h4><?php echo $stats['new_contacts'] ?? '0'; ?></h4>
                        </div>
                        <div class="col-6 text-right">
                            <p class="mb-0"><i
                                    class="ri-arrow-right-up-line text-success align-middle font-18 mr-1"></i>8%</p>
                            <p class="mb-0">Aujourd'hui</p>
                        </div>
                    </div>
                    <div id="apex-line-chart3"></div>
                </div>
            </div>
        </div>
        <!-- End col -->
    </div>
    <!-- End row -->

    <!-- Start row -->
    <div class="row">
        <!-- Start col -->
        <div class="col-lg-12 col-xl-4">
            <div class="card m-b-30">
                <div class="card-header text-center">
                    <h5 class="card-title mb-0">Statut des Projets</h5>
                </div>
                <div class="card-body p-0">
                    <div id="apex-circle-chart"></div>
                </div>
            </div>
        </div>
        <!-- End col -->

        <!-- Start col -->
        <div class="col-lg-12 col-xl-8">
            <div class="card m-b-30">
                <div class="card-header">
                    <h5 class="card-title mb-0">Revenus & Dépenses</h5>
                </div>
                <div class="card-body py-0">
                    <div class="row align-items-center">
                        <div class="col-lg-12 col-xl-4">
                            <h4 class="text-muted"><sup>€</sup>59,876.00</h4>
                            <p>Balance courante</p>
                            <ul class="list-unstyled my-5">
                                <li><i class="ri-checkbox-blank-circle-fill text-primary font-10 mr-2"></i>Revenus
                                </li>
                                <li><i class="ri-checkbox-blank-circle-fill text-success font-10 mr-2"></i>Dépenses
                                </li>
                            </ul>
                            <button type="button" class="btn btn-primary">Exporter<i
                                    class="ri-arrow-right-line align-middle ml-2"></i></button>
                        </div>
                        <div class="col-lg-12 col-xl-8">
                            <div id="apex-horizontal-bar-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End col -->
    </div>
    <!-- End row -->

    <!-- Start row -->
    <div class="row">
        <!-- Start col -->
        <div class="col-lg-12 col-xl-6">
            <div class="card m-b-30">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-6 col-lg-9">
                            <h5 class="card-title mb-0">Projets Récents</h5>
                        </div>
                        <div class="col-6 col-lg-3">
                            <select class="form-control font-12">
                                <option value="class1" selected>Ce mois</option>
                                <option value="class2">Le mois dernier</option>
                                <option value="class3">Trimestre</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nom du Projet</th>
                                    <th scope="col">Client</th>
                                    <th scope="col">Prix</th>
                                    <th scope="col">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Récupérer les projets récents
                                $recent_projects = getRecentProjects($pdo);
                                if ($recent_projects):
                                    foreach ($recent_projects as $index => $project):
                                        ?>
                                        <tr>
                                            <th scope="row"><?php echo $index + 1; ?></th>
                                            <td><?php echo htmlspecialchars($project['title']); ?></td>
                                            <td><?php echo htmlspecialchars($project['client_name'] ?? 'N/A'); ?></td>
                                            <td>€<?php echo number_format($project['estimated_budget'] ?? 0, 2); ?></td>
                                            <td>
                                                <?php
                                                $status_class = [
                                                    'draft' => 'secondary',
                                                    'in_progress' => 'warning',
                                                    'completed' => 'success',
                                                    'delivered' => 'primary'
                                                ][$project['status']] ?? 'light';
                                                ?>
                                                <span class="badge badge-<?php echo $status_class; ?>">
                                                    <?php echo ucfirst($project['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Aucun projet trouvé</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- End col -->

        <!-- Start col -->
        <div class="col-lg-12 col-xl-3">
            <div class="card m-b-30">
                <div class="card-header text-center">
                    <h5 class="card-title mb-0">Top Membres</h5>
                </div>
                <div class="card-body">
                    <div class="user-slider">
                        <?php
                        $top_members = getTopTeamMembers($pdo);
                        foreach ($top_members as $member):
                            ?>
                            <div class="user-slider-item">
                                <div class="card-body text-center">
                                    <span class="action-icon badge badge-primary-inverse">
                                        <?php echo substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1); ?>
                                    </span>
                                    <h5><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                                    </h5>
                                    <p><?php echo htmlspecialchars($member['position']); ?></p>
                                    <p class="mt-3 mb-0">
                                        <span class="badge badge-primary font-weight-normal font-14 py-1 px-2">
                                            <?php echo $member['projects_count'] ?? 0; ?> projets
                                        </span>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- End col -->

        <!-- Start col -->
        <div class="col-lg-12 col-xl-3">
            <div class="card bg-primary-rgba text-center m-b-30">
                <div class="card-header">
                    <h5 class="card-title mb-0">Activité</h5>
                </div>
                <div class="card-body">
                    <img src="<?php echo ROOT_URL; ?>/admin/assets/images/general/activity.svg"
                        class="img-fluid img-winner" alt="activité">
                    <h5 class="my-0"><?php echo $stats['active_projects'] ?? 0; ?> projets en cours</h5>
                    <p class="text-muted mt-2">Performance optimale</p>
                </div>
            </div>
        </div>
        <!-- End col -->
    </div>
    <!-- End row -->
</div>
<!-- End Contentbar -->

<?php include 'includes/footer.php'; ?>