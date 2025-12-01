<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../includes/project-functions.php';

// verifier_session(['admin']);
$page_title = "Gestion des Projets";

// Paramètres
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$search = isset($_GET['search']) ? nettoyer_input($_GET['search']) : '';
$category = isset($_GET['category']) ? nettoyer_input($_GET['category']) : '';
$status = isset($_GET['status']) ? nettoyer_input($_GET['status']) : '';
$priority = isset($_GET['priority']) ? nettoyer_input($_GET['priority']) : '';
$client_id = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;

// Filtres
$filters = [];
if ($search) $filters['search'] = $search;
if ($category) $filters['category'] = $category;
if ($status) $filters['status'] = $status;
if ($priority) $filters['priority'] = $priority;
if ($client_id) $filters['client_id'] = $client_id;

// Récupérer les projets
$result = getAllProjects($page, $per_page, $filters);
$projects = $result['projects'];
$total = $result['total'];
$total_pages = $result['total_pages'];

// Statistiques
$stats = getProjectsStats();

// Récupérer les clients pour le filtre
$pdo = getConnexion();
$clients = $pdo->query("SELECT id, first_name, last_name FROM users WHERE role = 'client' ORDER BY last_name")->fetchAll();

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
                                <i class="ri-projector-line mr-2"></i>Gestion des Projets
                            </h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="create.php" class="btn btn-primary">
                                <i class="ri-add-line mr-2"></i>Nouveau Projet
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
                                                <i class="ri-projector-line font-24"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h4 class="mb-0"><?php echo  htmlspecialchars($stats['total']); ?></h4>
                                            <p class="mb-0">Total</p>
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
                                                <i class="ri-loader-line font-24"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h4 class="mb-0"><?php echo $stats['in_progress']; ?></h4>
                                            <p class="mb-0">En cours</p>
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
                                            <h4 class="mb-0"><?php echo $stats['completed_this_month']; ?></h4>
                                            <p class="mb-0">Terminés (mois)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="card bg-danger-rgba">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <span class="avatar avatar-md">
                                                <i class="ri-alarm-warning-line font-24"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h4 class="mb-0"><?php echo $stats['late']; ?></h4>
                                            <p class="mb-0">En retard</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Barre de progression globale -->
                    <?php
                    $total_projects = $stats['total'];
                    $completed_projects = 0;
                    foreach ($stats['by_status'] as $status_stat) {
                        if (in_array($status_stat['status'], ['completed', 'delivered'])) {
                            $completed_projects += $status_stat['count'];
                        }
                    }
                    $global_progress = $total_projects > 0 ? round(($completed_projects / $total_projects) * 100) : 0;
                    ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Progression globale</span>
                                <span><?php echo $global_progress; ?>%</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?php echo $global_progress; ?>%">
                                </div>
                            </div>
                            <div class="row text-center mt-3">
                                <?php foreach ($stats['by_status'] as $status_stat): ?>
                                <div class="col">
                                    <h5 class="mb-0"><?php echo $status_stat['count']; ?></h5>
                                    <small class="text-muted">
                                        <?php
                                        $status_labels = [
                                            'draft' => 'Brouillon',
                                            'in_progress' => 'En cours',
                                            'completed' => 'Terminé',
                                            'delivered' => 'Livré'
                                        ];
                                        echo $status_labels[$status_stat['status']] ?? $status_stat['status'];
                                        ?>
                                    </small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filtres avancés -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Filtres avancés</h6>
                        </div>
                        <div class="card-body">
                            <form method="get" class="row">
                                <div class="col-md-3 mb-3">
                                    <input type="text" class="form-control" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>" 
                                           placeholder="Rechercher...">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <select class="form-control" name="category">
                                        <option value="">Toutes catégories</option>
                                        <option value="web" <?php echo $category == 'web' ? 'selected' : ''; ?>>Web</option>
                                        <option value="mobile" <?php echo $category == 'mobile' ? 'selected' : ''; ?>>Mobile</option>
                                        <option value="desktop" <?php echo $category == 'desktop' ? 'selected' : ''; ?>>Desktop</option>
                                        <option value="cloud" <?php echo $category == 'cloud' ? 'selected' : ''; ?>>Cloud</option>
                                        <option value="ia" <?php echo $category == 'ia' ? 'selected' : ''; ?>>IA</option>
                                        <option value="database" <?php echo $category == 'database' ? 'selected' : ''; ?>>Base de données</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <select class="form-control" name="status">
                                        <option value="">Tous statuts</option>
                                        <option value="draft" <?php echo $status == 'draft' ? 'selected' : ''; ?>>Brouillon</option>
                                        <option value="in_progress" <?php echo $status == 'in_progress' ? 'selected' : ''; ?>>En cours</option>
                                        <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Terminé</option>
                                        <option value="delivered" <?php echo $status == 'delivered' ? 'selected' : ''; ?>>Livré</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <select class="form-control" name="priority">
                                        <option value="">Toutes priorités</option>
                                        <option value="low" <?php echo $priority == 'low' ? 'selected' : ''; ?>>Basse</option>
                                        <option value="medium" <?php echo $priority == 'medium' ? 'selected' : ''; ?>>Moyenne</option>
                                        <option value="high" <?php echo $priority == 'high' ? 'selected' : ''; ?>>Haute</option>
                                        <option value="urgent" <?php echo $priority == 'urgent' ? 'selected' : ''; ?>>Urgente</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <select class="form-control" name="client_id">
                                        <option value="">Tous clients</option>
                                        <?php foreach ($clients as $client): ?>
                                        <option value="<?php echo $client['id']; ?>" 
                                                <?php echo $client_id == $client['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?>
                                        </option>
                                        <?php endforeach; ?>
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
                    
                    <!-- Tableau des projets -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">ID</th>
                                    <th>Projet</th>
                                    <th>Client</th>
                                    <th>Catégorie</th>
                                    <th>Statut</th>
                                    <th>Priorité</th>
                                    <th>Progression</th>
                                    <th>Budget</th>
                                    <th>Dates</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($projects)): ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ri-inbox-line display-4"></i>
                                            <h5 class="mt-3">Aucun projet trouvé</h5>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($projects as $project): ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-light">#<?php echo $project['id']; ?></span>
                                    </td>
                                    <td>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($project['title']); ?></h6>
                                        <small class="text-muted">
                                            <?php 
                                            $desc = strip_tags($project['description']);
                                            echo strlen($desc) > 60 ? substr($desc, 0, 60) . '...' : $desc;
                                            ?>
                                        </small>
                                        <?php if ($project['featured']): ?>
                                        <br>
                                        <small class="text-warning">
                                            <i class="ri-star-fill mr-1"></i>Vedette
                                        </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($project['client_first_name']): ?>
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <i class="ri-user-line text-muted"></i>
                                            </div>
                                            <div>
                                                <small class="d-block"><?php echo htmlspecialchars($project['client_first_name'] . ' ' . $project['client_last_name']); ?></small>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $category_labels = [
                                            'web' => ['label' => 'Web', 'class' => 'primary'],
                                            'mobile' => ['label' => 'Mobile', 'class' => 'info'],
                                            'desktop' => ['label' => 'Desktop', 'class' => 'secondary'],
                                            'cloud' => ['label' => 'Cloud', 'class' => 'success'],
                                            'ia' => ['label' => 'IA', 'class' => 'danger'],
                                            'database' => ['label' => 'BDD', 'class' => 'warning']
                                        ];
                                        $cat = $category_labels[$project['category']] ?? ['label' => $project['category'], 'class' => 'light'];
                                        ?>
                                        <span class="badge badge-<?php echo $cat['class']; ?>">
                                            <?php echo $cat['label']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $status_classes = [
                                            'draft' => 'secondary',
                                            'in_progress' => 'warning',
                                            'completed' => 'success',
                                            'delivered' => 'primary'
                                        ];
                                        $status_labels = [
                                            'draft' => 'Brouillon',
                                            'in_progress' => 'En cours',
                                            'completed' => 'Terminé',
                                            'delivered' => 'Livré'
                                        ];
                                        ?>
                                        <span class="badge badge-<?php echo $status_classes[$project['status']] ?? 'light'; ?>">
                                            <?php echo $status_labels[$project['status']] ?? $project['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $priority_classes = [
                                            'low' => 'success',
                                            'medium' => 'warning',
                                            'high' => 'danger',
                                            'urgent' => 'dark'
                                        ];
                                        ?>
                                        <span class="badge badge-<?php echo $priority_classes[$project['priority']] ?? 'light'; ?>">
                                            <?php echo ucfirst($project['priority']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 mr-2" style="height: 6px;">
                                                <div class="progress-bar bg-<?php 
                                                    echo $project['progress'] >= 100 ? 'success' : 
                                                         ($project['progress'] >= 70 ? 'info' : 
                                                         ($project['progress'] >= 30 ? 'warning' : 'danger')); 
                                                ?>" 
                                                     style="width: <?php echo $project['progress']; ?>%">
                                                </div>
                                            </div>
                                            <small><?php echo $project['progress']; ?>%</small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($project['estimated_budget']): ?>
                                        <div class="text-nowrap">
                                            <span class="font-weight-bold">
                                                <?php echo number_format($project['estimated_budget'], 2); ?> <?php echo $project['currency']; ?>
                                            </span>
                                            <?php if ($project['actual_budget']): ?>
                                            <br>
                                            <small class="text-<?php echo $project['actual_budget'] <= $project['estimated_budget'] ? 'success' : 'danger'; ?>">
                                                <?php echo number_format($project['actual_budget'], 2); ?> <?php echo $project['currency']; ?>
                                            </small>
                                            <?php endif; ?>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted d-block">
                                            <i class="ri-calendar-line mr-1"></i>
                                            <?php echo $project['start_date'] ? date('d/m/y', strtotime($project['start_date'])) : '-'; ?>
                                        </small>
                                        <small class="text-muted">
                                            <i class="ri-flag-line mr-1"></i>
                                            <?php echo $project['end_date'] ? date('d/m/y', strtotime($project['end_date'])) : '-'; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="view.php?id=<?php echo $project['id']; ?>" 
                                               class="btn btn-sm btn-outline-info" 
                                               title="Voir">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $project['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Modifier">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger delete-project" 
                                                    data-id="<?php echo $project['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($project['title']); ?>"
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
                            <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=1<?php echo buildQueryString(['page']); ?>">
                                    <i class="ri-skip-back-line"></i>
                                </a>
                            </li>
                            <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo buildQueryString(['page']); ?>">
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
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo buildQueryString(['page']); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php
                            if ($end_page < $total_pages) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            ?>
                            
                            <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo buildQueryString(['page']); ?>">
                                    <i class="ri-arrow-right-line"></i>
                                </a>
                            </li>
                            <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo buildQueryString(['page']); ?>">
                                    <i class="ri-skip-forward-line"></i>
                                </a>
                            </li>
                        </ul>
                        <p class="text-center text-muted mt-2">
                            Affichage <?php echo ($page - 1) * $per_page + 1; ?>-<?php echo min($page * $per_page, $total); ?> 
                            sur <?php echo $total; ?> projets
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
                <p>Êtes-vous sûr de vouloir supprimer le projet "<span id="projectName"></span>" ?</p>
                <p class="text-danger"><small>Toutes les données du projet seront supprimées définitivement.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Supprimer</button>
            </div>
        </div>
    </div>
</div>

<style>
    .progress {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .progress-bar {
        border-radius: 10px;
    }
    
    .table-hover tbody tr {
        cursor: pointer;
    }
    
    .badge-dark {
        background-color: #343a40;
    }
    
    .text-nowrap {
        white-space: nowrap;
    }
</style>

<script>
$(document).ready(function() {
    var projectIdToDelete = null;
    
    // Click sur une ligne pour voir le projet
    $('tbody tr').click(function(e) {
        // Ne pas déclencher si on clique sur un bouton d'action
        if (!$(e.target).closest('.btn').length && !$(e.target).closest('a').length) {
            var projectId = $(this).find('.delete-project').data('id');
            if (projectId) {
                window.location.href = 'view.php?id=' + projectId;
            }
        }
    });
    
    // Gestion de la suppression
    $('.delete-project').click(function(e) {
        e.stopPropagation();
        projectIdToDelete = $(this).data('id');
        var projectName = $(this).data('name');
        
        $('#projectName').text(projectName);
        $('#deleteModal').modal('show');
    });
    
    $('#confirmDelete').click(function() {
        if (projectIdToDelete) {
            $.ajax({
                url: 'delete.php',
                method: 'POST',
                data: {
                    id: projectIdToDelete,
                    csrf_token: '<?php echo generer_token(); ?>'
                },
                success: function(response) {
                    try {
                        var data = JSON.parse(response);
                        
                        if (data.success) {
                            toastr.success(data.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            toastr.error(data.message);
                        }
                    } catch (e) {
                        toastr.error('Réponse invalide du serveur');
                    }
                    
                    $('#deleteModal').modal('hide');
                },
                error: function(xhr, status, error) {
                    toastr.error('Une erreur est survenue: ' + error);
                    $('#deleteModal').modal('hide');
                }
            });
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>