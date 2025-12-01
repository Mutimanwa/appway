<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../includes/project-functions.php';

// verifier_session(['admin']);

$page_title = "Détails du Projet";

// Récupérer l'ID du projet
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$id) {
    header("Location: list.php");
    exit();
}

// Récupérer le projet
$project = getProjectById($id);

if (!$project) {
    header("Location: list.php");
    exit();
}

include '../includes/header.php';

// Labels pour les statuts et priorités
$status_labels = [
    'draft' => ['label' => 'Brouillon', 'class' => 'secondary'],
    'in_progress' => ['label' => 'En cours', 'class' => 'warning'],
    'completed' => ['label' => 'Terminé', 'class' => 'success'],
    'delivered' => ['label' => 'Livré', 'class' => 'primary']
];

$priority_labels = [
    'low' => ['label' => 'Basse', 'class' => 'success'],
    'medium' => ['label' => 'Moyenne', 'class' => 'warning'],
    'high' => ['label' => 'Haute', 'class' => 'danger'],
    'urgent' => ['label' => 'Urgente', 'class' => 'dark']
];

$category_labels = [
    'web' => ['label' => 'Web', 'class' => 'primary'],
    'mobile' => ['label' => 'Mobile', 'class' => 'info'],
    'desktop' => ['label' => 'Desktop', 'class' => 'secondary'],
    'cloud' => ['label' => 'Cloud', 'class' => 'success'],
    'ia' => ['label' => 'IA', 'class' => 'danger'],
    'database' => ['label' => 'Base de données', 'class' => 'warning']
];
?>

<!-- Start Contentbar -->
<div class="contentbar">
    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="card-title mb-0">
                                <i class="ri-projector-line mr-2"></i>Détails du projet
                            </h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-primary mr-2">
                                <i class="ri-edit-line mr-2"></i>Modifier
                            </a>
                            <a href="list.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line mr-2"></i>Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <!-- Titre et description -->
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h3 class="mb-3"><?php echo htmlspecialchars($project['title']); ?></h3>
                                    <?php if ($project['featured']): ?>
                                        <span class="badge badge-warning mb-3">
                                            <i class="ri-star-fill mr-1"></i>Projet en vedette
                                        </span>
                                    <?php endif; ?>
                                    <div class="mb-4">
                                        <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <h6>Informations générales</h6>
                                            <ul class="list-unstyled">
                                                <li class="mb-2">
                                                    <strong>Catégorie:</strong>
                                                    <span
                                                        class="badge badge-<?php echo $category_labels[$project['category']]['class']; ?>">
                                                        <?php echo $category_labels[$project['category']]['label']; ?>
                                                    </span>
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Client:</strong>
                                                    <?php echo htmlspecialchars($project['client_first_name'] . ' ' . $project['client_last_name']); ?>
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Email client:</strong>
                                                    <a href="mailto:<?php echo $project['client_email']; ?>">
                                                        <?php echo $project['client_email']; ?>
                                                    </a>
                                                </li>
                                                <li>
                                                    <strong>Date de création:</strong>
                                                    <?php echo date('d/m/Y H:i', strtotime($project['created_at'])); ?>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Statut et progression</h6>
                                            <ul class="list-unstyled">
                                                <li class="mb-2">
                                                    <strong>Statut:</strong>
                                                    <span
                                                        class="badge badge-<?php echo $status_labels[$project['status']]['class']; ?>">
                                                        <?php echo $status_labels[$project['status']]['label']; ?>
                                                    </span>
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Priorité:</strong>
                                                    <span
                                                        class="badge badge-<?php echo $priority_labels[$project['priority']]['class']; ?>">
                                                        <?php echo $priority_labels[$project['priority']]['label']; ?>
                                                    </span>
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Progression:</strong>
                                                    <div class="progress" style="height: 8px; width: 150px;">
                                                        <div class="progress-bar bg-success"
                                                            style="width: <?php echo $project['progress']; ?>%"></div>
                                                    </div>
                                                    <small><?php echo $project['progress']; ?>%</small>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Technologies -->
                                    <?php if (!empty($project['technologies'])): ?>
                                        <div class="mb-4">
                                            <h6>Technologies utilisées</h6>
                                            <div class="d-flex flex-wrap">
                                                <?php foreach ($project['technologies'] as $tech): ?>
                                                    <span class="badge badge-light mr-2 mb-2 p-2">
                                                        <i class="ri-code-line mr-1"></i><?php echo $tech; ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Dates -->
                                    <div class="mb-4">
                                        <h6>Dates importantes</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="text-center">
                                                    <div class="small text-muted">Date de début</div>
                                                    <div class="h5">
                                                        <?php echo $project['start_date'] ? date('d/m/Y', strtotime($project['start_date'])) : '-'; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-center">
                                                    <div class="small text-muted">Date de fin prévue</div>
                                                    <div class="h5">
                                                        <?php echo $project['end_date'] ? date('d/m/Y', strtotime($project['end_date'])) : '-'; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-center">
                                                    <div class="small text-muted">Date de livraison</div>
                                                    <div class="h5">
                                                        <?php echo $project['delivery_date'] ? date('d/m/Y', strtotime($project['delivery_date'])) : '-'; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Budget -->
                                    <div class="mb-4">
                                        <h6>Budget</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <div class="small text-muted">Budget estimé</div>
                                                        <div class="h4">
                                                            <?php if ($project['estimated_budget']): ?>
                                                                <?php echo number_format($project['estimated_budget'], 2); ?>
                                                                <?php echo $project['currency']; ?>
                                                            <?php else: ?>
                                                                -
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <div class="small text-muted">Budget réel</div>
                                                        <div
                                                            class="h4 <?php echo $project['actual_budget'] <= $project['estimated_budget'] ? 'text-success' : 'text-danger'; ?>">
                                                            <?php if ($project['actual_budget']): ?>
                                                                <?php echo number_format($project['actual_budget'], 2); ?>
                                                                <?php echo $project['currency']; ?>
                                                            <?php else: ?>
                                                                -
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- URLs -->
                                    <div class="mb-4">
                                        <h6>Liens externes</h6>
                                        <div class="row">
                                            <?php if ($project['demo_url']): ?>
                                                <div class="col-md-6 mb-2">
                                                    <a href="<?php echo $project['demo_url']; ?>" target="_blank"
                                                        class="btn btn-outline-info btn-block">
                                                        <i class="ri-external-link-line mr-2"></i>Voir la démo
                                                    </a>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($project['github_url']): ?>
                                                <div class="col-md-6 mb-2">
                                                    <a href="<?php echo $project['github_url']; ?>" target="_blank"
                                                        class="btn btn-outline-dark btn-block">
                                                        <i class="ri-github-fill mr-2"></i>Voir sur GitHub
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Spécifications techniques -->
                            <?php if (!empty($project['specifications'])): ?>
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-file-text-line mr-2"></i>Spécifications techniques
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <?php echo nl2br(htmlspecialchars($project['specifications'])); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Notes internes -->
                            <?php if (!empty($project['internal_notes'])): ?>
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-sticky-note-line mr-2"></i>Notes internes</h6>
                                    </div>
                                    <div class="card-body">
                                        <?php echo nl2br(htmlspecialchars($project['internal_notes'])); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-lg-4">
                            <!-- Images du projet -->
                            <?php if (!empty($project['images'])): ?>
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-image-line mr-2"></i>Galerie du projet</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <?php foreach ($project['images'] as $image): ?>
                                                <div class="col-md-6 col-sm-4 mb-3">
                                                    <a href="<?php echo UPLOAD_URL; ?>projects/<?php echo $image; ?>"
                                                        data-fancybox="gallery">
                                                        <img src="<?php echo UPLOAD_URL; ?>projects/<?php echo $image; ?>"
                                                            class="img-fluid rounded" alt="Image projet"
                                                            style="height: 100px; object-fit: cover;">
                                                    </a>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Informations client -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="ri-user-line mr-2"></i>Informations client</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <div class="avatar avatar-xl mb-2">
                                            <i class="ri-user-3-fill font-24"></i>
                                        </div>
                                        <h5><?php echo htmlspecialchars($project['client_first_name'] . ' ' . $project['client_last_name']); ?>
                                        </h5>
                                        <p class="text-muted">Client</p>
                                    </div>
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <i class="ri-mail-line mr-2"></i>
                                            <a href="mailto:<?php echo $project['client_email']; ?>">
                                                <?php echo $project['client_email']; ?>
                                            </a>
                                        </li>
                                    </ul>
                                    <a href="<?php echo ADMIN_URL; ?>/users/edit.php?id=<?php echo $project['client_id']; ?>"
                                        class="btn btn-sm btn-outline-primary btn-block">
                                        <i class="ri-user-settings-line mr-2"></i>Voir le profil client
                                    </a>
                                </div>
                            </div>

                            <!-- Métadonnées -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="ri-information-line mr-2"></i>Métadonnées</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <strong>ID:</strong> #<?php echo $project['id']; ?>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Créé le:</strong>
                                            <?php echo date('d/m/Y H:i', strtotime($project['created_at'])); ?>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Modifié le:</strong>
                                            <?php echo date('d/m/Y H:i', strtotime($project['updated_at'])); ?>
                                        </li>
                                        <li>
                                            <strong>Type:</strong>
                                            <span
                                                class="badge badge-<?php echo $category_labels[$project['category']]['class']; ?>">
                                                <?php echo $category_labels[$project['category']]['label']; ?>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css">
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<script>
    $(document).ready(function () {
        Fancybox.bind("[data-fancybox]", {
            // Options de configuration
        });
    });
</script>

<?php include '../includes/footer.php'; ?>