<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../includes/service-functions.php';

// verifier_session('admin');

$page_title = "Gestion des Services";

// Récupérer les paramètres de pagination et filtres
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$per_page = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
$search = isset($_GET['search']) ? nettoyer_input($_GET['search']) : '';
$category = isset($_GET['category']) ? nettoyer_input($_GET['category']) : '';
$status = isset($_GET['status']) ? nettoyer_input($_GET['status']) : '';
$featured = isset($_GET['featured']) ? nettoyer_input($_GET['featured']) : '';

// Préparer les filtres
$filters = [];
if ($search)
    $filters['search'] = $search;
if ($category)
    $filters['category'] = $category;
if ($status !== '')
    $filters['status'] = $status;
if ($featured !== '')
    $filters['featured'] = $featured;

// Récupérer les services
$result = getAllServices($page, $per_page, $filters);
$services = $result['services'];
$total = $result['total'];
$total_pages = $result['total_pages'];

// Récupérer les statistiques
$stats = getServicesStats();

include '../includes/header.php';
?>


<div class="contentbar">
    <!-- Start row -->
    <div class="row">
        <!-- Start col -->
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title mb-0">
                                <i class="ri-service-line mr-2"></i>Gestion des Services
                            </h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="create.php" class="btn btn-primary">
                                <i class="ri-add-line mr-2"></i>Nouveau Service
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    <!-- Statistiques -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-sm-6">
                            <div class="card bg-primary-rgba mb-3 mb-md-0">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <span class="avatar avatar-md">
                                                <i class="ri-service-line font-24"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h4 class="mb-0"><?php echo $stats['total']; ?></h4>
                                            <p class="mb-0">Total Services</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="card bg-success-rgba mb-3 mb-md-0">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <span class="avatar avatar-md">
                                                <i class="ri-check-line font-24"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h4 class="mb-0"><?php echo $stats['active']; ?></h4>
                                            <p class="mb-0">Actifs</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>

                    <!-- Filtres et recherche -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="get" class="row">
                                <div class="col-md-3 mb-3">
                                    <input type="text" class="form-control" name="search"
                                        value="<?php echo htmlspecialchars($search); ?>" placeholder="Rechercher...">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <select class="form-control" name="category">
                                        <option value="">Toutes catégories</option>
                                        <option value="development" <?php echo $category == 'development' ? 'selected' : ''; ?>>Développement</option>
                                        <option value="design" <?php echo $category == 'design' ? 'selected' : ''; ?>>
                                            Design</option>
                                        <option value="consulting" <?php echo $category == 'consulting' ? 'selected' : ''; ?>>Consulting</option>
                                        <option value="maintenance" <?php echo $category == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                        <option value="ia" <?php echo $category == 'ia' ? 'selected' : ''; ?>>IA
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <select class="form-control" name="status">
                                        <option value="">Tous statuts</option>
                                        <option value="1" <?php echo $status === '1' ? 'selected' : ''; ?>>Actif
                                        </option>
                                        <option value="0" <?php echo $status === '0' ? 'selected' : ''; ?>>Inactif
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <select class="form-control" name="featured">
                                        <option value="">Tous</option>
                                        <option value="1" <?php echo $featured === '1' ? 'selected' : ''; ?>>En
                                            vedette
                                        </option>
                                        <option value="0" <?php echo $featured === '0' ? 'selected' : ''; ?>>Non
                                            vedette
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <select class="form-control" name="per_page">
                                        <option value="10" <?php echo $per_page == 10 ? 'selected' : ''; ?>>10 par
                                            page
                                        </option>
                                        <option value="25" <?php echo $per_page == 25 ? 'selected' : ''; ?>>25 par
                                            page
                                        </option>
                                        <option value="50" <?php echo $per_page == 50 ? 'selected' : ''; ?>>50 par
                                            page
                                        </option>
                                        <option value="100" <?php echo $per_page == 100 ? 'selected' : ''; ?>>100 par
                                            page
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-1 mb-3">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="ri-search-line"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tableau des services -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">ID</th>
                                    <th>Service</th>
                                    <th>Catégorie</th>
                                    <th>Prix</th>
                                    <th>Caractéristiques</th>
                                    <th>Statut</th>

                                    <th>Création</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($services)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="ri-inbox-line display-4"></i>
                                                <h5 class="mt-3">Aucun service trouvé</h5>
                                                <p>Créez votre premier service !</p>
                                                <a href="create.php" class="btn btn-primary">
                                                    <i class="ri-add-line mr-2"></i>Créer un service
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($services as $service): ?>
                                        <tr>
                                            <td>
                                                <span class="badge badge-light">#<?php echo $service['id']; ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($service['icon']): ?>
                                                        <div class="mr-3">
                                                            <i
                                                                class="<?php echo htmlspecialchars($service['icon']); ?> font-20"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($service['name']); ?>
                                                        </h6>
                                                        <small class="text-muted">
                                                            <?php
                                                            $description = strip_tags($service['description']);
                                                            echo strlen($description) > 80 ? substr($description, 0, 80) . '...' : $description;
                                                            ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $category_labels = [
                                                    'development' => ['label' => 'Développement', 'class' => 'primary'],
                                                    'design' => ['label' => 'Design', 'class' => 'success'],
                                                    'consulting' => ['label' => 'Consulting', 'class' => 'info'],
                                                    'maintenance' => ['label' => 'Maintenance', 'class' => 'warning'],
                                                    'ia' => ['label' => 'IA', 'class' => 'danger']
                                                ];
                                                $cat = $category_labels[$service['category']] ?? ['label' => $service['category'], 'class' => 'secondary'];
                                                ?>
                                                <span class="badge badge-<?php echo $cat['class']; ?>">
                                                    <?php echo $cat['label']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span
                                                    class="font-weight-bold"><?php echo htmlspecialchars($service['price']); ?></span>
                                                <?php if ($service['duration']): ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo $service['duration']; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($service['features'])): ?>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php foreach (array_slice($service['features'], 0, 3) as $feature): ?>
                                                            <span
                                                                class="badge badge-light"><?php echo htmlspecialchars($feature); ?></span>
                                                        <?php endforeach; ?>
                                                        <?php if (count($service['features']) > 3): ?>
                                                            <span
                                                                class="badge badge-secondary">+<?php echo count($service['features']) - 3; ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($service['is_active']): ?>
                                                    <span class="badge badge-success">Actif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Inactif</span>
                                                <?php endif; ?>


                                            </td>

                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('d/m/Y', strtotime($service['created_at'])); ?>
                                                    <br>
                                                    <small><?php echo date('H:i', strtotime($service['created_at'])); ?></small>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="view.php?id=<?php echo $service['id']; ?>"
                                                        class="btn btn-sm btn-outline-info" title="Voir">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $service['id']; ?>"
                                                        class="btn btn-sm btn-outline-primary" title="Modifier">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-service"
                                                        data-id="<?php echo $service['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($service['name']); ?>"
                                                        title="Supprimer">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Pagination">
                            <ul class="pagination justify-content-center">
                                <!-- Premier et précédent -->
                                <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=1<?php echo buildQueryString(['page']); ?>">
                                        <i class="ri-skip-back-line"></i>
                                    </a>
                                </li>
                                <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link"
                                        href="?page=<?php echo $page - 1; ?><?php echo buildQueryString(['page']); ?>">
                                        <i class="ri-arrow-left-line"></i>
                                    </a>
                                </li>

                                <!-- Pages -->
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);

                                if ($start_page > 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }

                                for ($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link"
                                            href="?page=<?php echo $i; ?><?php echo buildQueryString(['page']); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                if ($end_page < $total_pages) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>' ; } ?>

                                    <!-- Suivant et dernier -->
                                    <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link"
                                            href="?page=<?php echo $page + 1; ?><?php echo buildQueryString(['page']); ?>">
                                            <i class="ri-arrow-right-line"></i>
                                        </a>
                                    </li>
                                    <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link"
                                            href="?page=<?php echo $total_pages; ?><?php echo buildQueryString(['page']); ?>">
                                            <i class="ri-skip-forward-line"></i>
                                        </a>
                                    </li>
                            </ul>
                            <p class="text-center text-muted mt-2">
                                Affichage
                                <?php echo ($page - 1) * $per_page + 1; ?>-<?php echo min($page * $per_page, $total); ?>
                                sur <?php echo $total; ?> services
                            </p>
                        </nav>
                    <?php endif; ?>

                </div>
            </div>
        </div>
        <!-- End col -->
    </div>
    <!-- End row -->
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer le service "<span id="serviceName"></span>" ?</p>
                <p class="text-danger"><small>Cette action est irréversible.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Supprimer</button>
            </div>
        </div>
    </div>
</div>

<style>
    .table-hover tbody tr {
        transition: all 0.3s ease;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(52, 152, 219, 0.05);
        transform: translateX(5px);
    }

    .badge-light {
        background-color: #f8f9fa;
        color: #495057;
        border: 1px solid #dee2e6;
    }

    .gap-1>* {
        margin-right: 4px;
        margin-bottom: 4px;
    }

    .bg-primary-rgba {
        background-color: rgba(52, 152, 219, 0.1);
    }

    .bg-success-rgba {
        background-color: rgba(46, 204, 113, 0.1);
    }

    .bg-warning-rgba {
        background-color: rgba(241, 196, 15, 0.1);
    }

    .bg-info-rgba {
        background-color: rgba(52, 152, 219, 0.1);
    }

    .avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
    }

    .avatar-md {
        width: 40px;
        height: 40px;
    }

    .font-20 {
        font-size: 20px;
    }

    .font-24 {
        font-size: 24px;
    }
</style>

<script>
    $(document).ready(function () {
        // Gestion de la suppression
        var serviceIdToDelete = null;

        $('.delete-service').click(function () {
            serviceIdToDelete = $(this).data('id');
            var serviceName = $(this).data('name');

            $('#serviceName').text(serviceName);
            $('#deleteModal').modal('show');
        });

        $('#confirmDelete').click(function () {
            if (serviceIdToDelete) {
                $.ajax({
                    url: 'delete.php',
                    method: 'POST',
                    data: {
                        id: serviceIdToDelete,
                        csrf_token: '<?php echo generer_token(); ?>'
                    },
                    success: function (response) {
                        var data = JSON.parse(response);

                        if (data.success) {
                            toastr.success(data.message);
                            setTimeout(function () {
                                location.reload();
                            }, 1500);
                        } else {
                            toastr.error(data.message);
                        }

                        $('#deleteModal').modal('hide');
                    },
                    error: function () {
                        toastr.error('Une erreur est survenue');
                        $('#deleteModal').modal('hide');
                    }
                });
            }
        });

        // Toggle du statut actif/inactif
        $('.toggle-status').click(function () {
            var button = $(this);
            var serviceId = button.data('id');
            var currentStatus = button.data('status');
            var newStatus = currentStatus == 1 ? 0 : 1;

            $.ajax({
                url: 'toggle-status.php',
                method: 'POST',
                data: {
                    id: serviceId,
                    status: newStatus,
                    csrf_token: '<?php echo generer_token(); ?>'
                },
                success: function (response) {
                    var data = JSON.parse(response);

                    if (data.success) {
                        // Mettre à jour le bouton
                        button.data('status', newStatus);

                        if (newStatus == 1) {
                            button.removeClass('btn-outline-danger').addClass('btn-outline-success');
                            button.html('<i class="ri-toggle-fill"></i> Actif');
                        } else {
                            button.removeClass('btn-outline-success').addClass('btn-outline-danger');
                            button.html('<i class="ri-toggle-line"></i> Inactif');
                        }

                        toastr.success(data.message);
                    } else {
                        toastr.error(data.message);
                    }
                },
                error: function () {
                    toastr.error('Une erreur est survenue');
                }
            });
        });

        // Toggle du statut vedette
        $('.toggle-featured').click(function () {
            var button = $(this);
            var serviceId = button.data('id');
            var currentFeatured = button.data('featured');
            var newFeatured = currentFeatured == 1 ? 0 : 1;

            $.ajax({
                url: 'toggle-featured.php',
                method: 'POST',
                data: {
                    id: serviceId,
                    featured: newFeatured,
                    csrf_token: '<?php echo generer_token(); ?>'
                },
                success: function (response) {
                    var data = JSON.parse(response);

                    if (data.success) {
                        button.data('featured', newFeatured);

                        if (newFeatured == 1) {
                            button.removeClass('btn-outline-secondary').addClass('btn-outline-warning');
                            button.html('<i class="ri-star-fill"></i>');
                        } else {
                            button.removeClass('btn-outline-warning').addClass('btn-outline-secondary');
                            button.html('<i class="ri-star-line"></i>');
                        }

                        toastr.success(data.message);
                    } else {
                        toastr.error(data.message);
                    }
                },
                error: function () {
                    toastr.error('Une erreur est survenue');
                }
            });
        });

        // Export des données
        $('#exportBtn').click(function () {
            var filters = {
                search: '<?php echo $search; ?>',
                category: '<?php echo $category; ?>',
                status: '<?php echo $status; ?>',
                featured: '<?php echo $featured; ?>'
            };

            window.location.href = 'export.php?' + $.param(filters);
        });

        // Initialiser les tooltips
        $('[title]').tooltip();

        // Auto-refresh toutes les 5 minutes (optionnel)
        setInterval(function () {
            if (!$('#deleteModal').hasClass('show')) {
                // location.reload();
            }
        }, 300000);
    });

    <?php
    // Fonction pour construire la query string sans le paramètre spécifié
    function buildQueryString($exclude_params = [])
    {
        $params = $_GET;
        foreach ($exclude_params as $param) {
            unset($params[$param]);
        }
        return empty($params) ? '' : '&' . http_build_query($params);
    }
    ?>
</script>

<?php include '../includes/footer.php'; ?>