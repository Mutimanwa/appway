<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../includes/user-functions.php';

// verifier_session(['admin']);

$pdo = getConnexion();

$page_title = "Gestion des Utilisateurs";

// Récupérer les paramètres
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$per_page = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
$search = isset($_GET['search']) ? nettoyer_input($_GET['search']) : '';
$role = isset($_GET['role']) ? nettoyer_input($_GET['role']) : '';
$status = isset($_GET['status']) ? nettoyer_input($_GET['status']) : '';

// Filtres
$filters = [];
if ($search)
    $filters['search'] = $search;
if ($role)
    $filters['role'] = $role;
if ($status !== '')
    $filters['status'] = $status;

// Récupérer les utilisateurs
$result = getAllUsers($page, $per_page, $filters);
$users = $result['users'];
$total = $result['total'];
$total_pages = $result['total_pages'];



// Statistiques
$stats = getUsersStats();

include '../includes/header.php';
?>

<div class="contentbar">
    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title mb-0">
                                <i class="ri-user-line mr-2"></i>Gestion des Utilisateurs
                            </h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="create.php" class="btn btn-primary">
                                <i class="ri-user-add-line mr-2"></i>Nouvel Utilisateur
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
                                                <i class="ri-user-line font-24"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h4 class="mb-0"><?php echo $stats['total']; ?></h4>
                                            <p class="mb-0">Total</p>
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
                        <div class="col-md-3 col-sm-6">
                            <div class="card bg-warning-rgba mb-3 mb-md-0">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <span class="avatar avatar-md">
                                                <i class="ri-time-line font-24"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h4 class="mb-0"><?php echo $stats['never_logged']; ?></h4>
                                            <p class="mb-0">Jamais connecté</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="card bg-info-rgba">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <span class="avatar avatar-md">
                                                <i class="ri-user-add-line font-24"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h4 class="mb-0"><?php echo $stats['new_this_month']; ?></h4>
                                            <p class="mb-0">Nouveaux (mois)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="get" class="row">
                                <div class="col-md-3 mb-3">
                                    <input type="text" class="form-control" name="search"
                                        value="<?php echo htmlspecialchars($search); ?>"
                                        placeholder="Nom, prénom, email...">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <select class="form-control" name="role">
                                        <option value="">Tous rôles</option>
                                        <option value="admin" <?php echo $role == 'admin' ? 'selected' : ''; ?>>Admin
                                        </option>
                                        <option value="client" <?php echo $role == 'client' ? 'selected' : ''; ?>>Client
                                        </option>
                                        <option value="visitor" <?php echo $role == 'visitor' ? 'selected' : ''; ?>>
                                            Visiteur</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <select class="form-control" name="status">
                                        <option value="">Tous statuts</option>
                                        <option value="1" <?php echo $status === '1' ? 'selected' : ''; ?>>Actif</option>
                                        <option value="0" <?php echo $status === '0' ? 'selected' : ''; ?>>Inactif
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <select class="form-control" name="per_page">
                                        <option value="10" <?php echo $per_page == 10 ? 'selected' : ''; ?>>10 par page
                                        </option>
                                        <option value="25" <?php echo $per_page == 25 ? 'selected' : ''; ?>>25 par page
                                        </option>
                                        <option value="50" <?php echo $per_page == 50 ? 'selected' : ''; ?>>50 par page
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="ri-search-line mr-2"></i>Filtrer
                                    </button>
                                </div>
                                <div class="col-md-1 mb-3">
                                    <a href="list.php" class="btn btn-outline-secondary btn-block"
                                        title="Réinitialiser">
                                        <i class="ri-refresh-line"></i>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tableau -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">ID</th>
                                    <th>Utilisateur</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Entreprise</th>
                                    <th>Dernière connexion</th>
                                    <th>Statut</th>
                                    <th>Inscription</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="ri-user-line display-4"></i>
                                                <h5 class="mt-3">Aucun utilisateur trouvé</h5>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <span class="badge badge-light">#<?php echo $user['id']; ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="mr-3">
                                                        <?php if ($user['avatar']): ?>
                                                            <img src="<?php echo ROOT_URL; ?>/uploads/avatars/<?php echo $user['avatar']; ?>"
                                                                class="rounded-circle" width="40" height="40"
                                                                alt="<?php echo htmlspecialchars($user['first_name']); ?>"
                                                                onerror="this.src='<?php echo ROOT_URL; ?>/images/default-avatar.png'">
                                                        <?php else: ?>
                                                            <div class="avatar-circle bg-primary text-white">
                                                                <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">
                                                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                        </h6>
                                                        <small class="text-muted">
                                                            <?php echo $user['phone'] ?: 'Aucun téléphone'; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="mailto:<?php echo $user['email']; ?>" class="text-primary">
                                                    <?php echo htmlspecialchars($user['email']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php
                                                $role_labels = [
                                                    'admin' => ['label' => 'Admin', 'class' => 'danger'],
                                                    'client' => ['label' => 'Client', 'class' => 'primary'],
                                                    'visitor' => ['label' => 'Visiteur', 'class' => 'secondary']
                                                ];
                                                $role_info = $role_labels[$user['role']] ?? ['label' => $user['role'], 'class' => 'light'];
                                                ?>
                                                <span class="badge badge-<?php echo $role_info['class']; ?>">
                                                    <?php echo $role_info['label']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($user['company']): ?>
                                                    <span
                                                        class="font-weight-bold"><?php echo htmlspecialchars($user['company']); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($user['last_login']): ?>
                                                    <small class="text-muted">
                                                        <?php echo date('d/m/Y', strtotime($user['last_login'])); ?>
                                                        <br>
                                                        <small><?php echo date('H:i', strtotime($user['last_login'])); ?></small>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Jamais</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($user['is_active']): ?>
                                                    <span class="badge badge-success">Actif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Inactif</span>
                                                <?php endif; ?>

                                                <?php if ($user['newsletter']): ?>
                                                    <br>
                                                    <small class="text-info">
                                                        <i class="ri-mail-line mr-1"></i>Newsletter
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="view.php?id=<?php echo $user['id']; ?>"
                                                        class="btn btn-sm btn-outline-info" title="Voir">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $user['id']; ?>"
                                                        class="btn btn-sm btn-outline-primary" title="Modifier">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <?php if ($user['id'] != $_SESSION['id_utilisateur']): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-user"
                                                            data-id="<?php echo $user['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>"
                                                            title="Supprimer">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled
                                                            title="Vous ne pouvez pas supprimer votre compte">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    <?php endif; ?>
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
                                sur <?php echo $total; ?> utilisateurs
                            </p>
                        </nav>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de suppression -->
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
                <p>Êtes-vous sûr de vouloir supprimer l'utilisateur "<span id="userName"></span>" ?</p>
                <p class="text-danger"><small>Toutes ses données seront supprimées définitivement.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Supprimer</button>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    .table td {
        vertical-align: middle;
    }

    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
</style>

<script>
    $(document).ready(function () {
        var userIdToDelete = null;

        // Gestion de la suppression
        $('.delete-user').click(function () {
            userIdToDelete = $(this).data('id');
            var userName = $(this).data('name');

            $('#userName').text(userName);
            $('#deleteModal').modal('show');
        });

        $('#confirmDelete').click(function () {
            if (userIdToDelete) {
                $.ajax({
                    url: 'delete.php',
                    method: 'POST',
                    data: {
                        id: userIdToDelete,
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
            var userId = button.data('id');
            var currentStatus = button.data('status');
            var newStatus = currentStatus == 1 ? 0 : 1;

            if (userId == <?php echo $_SESSION['id_utilisateur']; ?> && newStatus == 0) {
                toastr.error('Vous ne pouvez pas désactiver votre propre compte');
                return;
            }

            $.ajax({
                url: 'toggle-status.php',
                method: 'POST',
                data: {
                    id: userId,
                    status: newStatus,
                    csrf_token: '<?php echo generer_token(); ?>'
                },
                success: function (response) {
                    var data = JSON.parse(response);

                    if (data.success) {
                        button.data('status', newStatus);

                        if (newStatus == 1) {
                            button.removeClass('btn-outline-danger').addClass('btn-outline-success');
                            button.html('<i class="ri-toggle-fill"></i>');
                        } else {
                            button.removeClass('btn-outline-success').addClass('btn-outline-danger');
                            button.html('<i class="ri-toggle-line"></i>');
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

        // Exporter en CSV
        $('#exportBtn').click(function () {
            window.location.href = 'export.php?' + window.location.search.substring(1);
        });
    });
</script>

<?php include '../includes/footer.php'; ?>