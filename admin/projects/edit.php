<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../includes/project-functions.php';

// verifier_session(['admin']);

$page_title = "Modifier le Projet";
$message = '';
$error = '';

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

// Vérifier si c'est un formulaire soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupérer les images existantes
        $existing_images = $project['images'];

        // Gérer les images à supprimer
        $delete_images = isset($_POST['delete_images']) ? $_POST['delete_images'] : [];

        // Gérer les nouvelles images uploadées
        $new_images = [];
        if (!empty($_FILES['project_images']['name'][0])) {
            foreach ($_FILES['project_images']['name'] as $key => $name) {
                if ($_FILES['project_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['project_images']['name'][$key],
                        'type' => $_FILES['project_images']['type'][$key],
                        'tmp_name' => $_FILES['project_images']['tmp_name'][$key],
                        'error' => $_FILES['project_images']['error'][$key],
                        'size' => $_FILES['project_images']['size'][$key]
                    ];

                    // Uploader l'image
                    $image_name = uploadProjectImage($file);
                    $new_images[] = $image_name;
                }
            }
        }

        // Préparer les données
        $data = [
            'id' => $id,
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'client_id' => $_POST['client_id'],
            'status' => $_POST['status'],
            'priority' => $_POST['priority'] ?? 'medium',
            'progress' => $_POST['progress'] ?? 0,
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
            'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
            'delivery_date' => !empty($_POST['delivery_date']) ? $_POST['delivery_date'] : null,
            'estimated_budget' => !empty($_POST['estimated_budget']) ? $_POST['estimated_budget'] : null,
            'actual_budget' => !empty($_POST['actual_budget']) ? $_POST['actual_budget'] : null,
            'currency' => $_POST['currency'] ?? 'EUR',
            'demo_url' => $_POST['demo_url'] ?? null,
            'github_url' => $_POST['github_url'] ?? null,
            'specifications' => $_POST['specifications'] ?? null,
            'internal_notes' => $_POST['internal_notes'] ?? null,
            'technologies' => isset($_POST['technologies']) ? $_POST['technologies'] : [],
            'current_images' => $existing_images,
            'delete_images' => $delete_images,
            'project_images' => $new_images
        ];

        // Sauvegarder le projet
        $result = saveProject($data);

        if ($result['success']) {
            $message = 'Projet mis à jour avec succès!';
            // Recharger les données du projet
            $project = getProjectById($id);
        } else {
            $error = $result['message'];
            if (isset($result['errors'])) {
                $error .= "<br>" . implode("<br>", $result['errors']);
            }
        }
    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

// Récupérer les clients pour le select
$pdo = getConnexion();
$clients = $pdo->query("SELECT id, first_name, last_name, email FROM users WHERE role = 'client' ORDER BY last_name")->fetchAll();

include '../includes/header.php';
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
                                <i class="ri-projector-line mr-2"></i>Modifier le projet
                            </h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="view.php?id=<?php echo $id; ?>" class="btn btn-info mr-2">
                                <i class="ri-eye-line mr-2"></i>Voir le projet
                            </a>
                            <a href="list.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line mr-2"></i>Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <form id="projectForm" action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">

                        <div class="row">
                            <div class="col-lg-8">
                                <!-- Informations de base -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-information-line mr-2"></i>Informations du projet
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="title">Titre du projet *</label>
                                            <input type="text" class="form-control form-control-lg" id="title"
                                                name="title" placeholder="Ex: Développement site e-commerce" required
                                                value="<?php echo htmlspecialchars($project['title']); ?>">
                                        </div>

                                        <div class="form-group">
                                            <label for="description">Description *</label>
                                            <textarea class="form-control" id="description" name="description" rows="4"
                                                required><?php echo htmlspecialchars($project['description']); ?></textarea>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="category">Catégorie *</label>
                                                    <select class="form-control select2" id="category" name="category"
                                                        required>
                                                        <option value="">Sélectionner...</option>
                                                        <option value="web" <?php echo $project['category'] == 'web' ? 'selected' : ''; ?>>Web</option>
                                                        <option value="mobile" <?php echo $project['category'] == 'mobile' ? 'selected' : ''; ?>>Mobile</option>
                                                        <option value="desktop" <?php echo $project['category'] == 'desktop' ? 'selected' : ''; ?>>
                                                            Desktop</option>
                                                        <option value="cloud" <?php echo $project['category'] == 'cloud' ? 'selected' : ''; ?>>Cloud</option>
                                                        <option value="ia" <?php echo $project['category'] == 'ia' ? 'selected' : ''; ?>>IA</option>
                                                        <option value="database" <?php echo $project['category'] == 'database' ? 'selected' : ''; ?>>Base
                                                            de données</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="client_id">Client *</label>
                                                    <select class="form-control select2" id="client_id" name="client_id"
                                                        required>
                                                        <option value="">Sélectionner un client...</option>
                                                        <?php foreach ($clients as $client): ?>
                                                            <option value="<?php echo $client['id']; ?>" <?php echo $project['client_id'] == $client['id'] ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name'] . ' (' . $client['email'] . ')'); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Éditeur de contenu -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-file-text-line mr-2"></i>Contenu détaillé</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Spécifications techniques</label>
                                            <textarea id="specifications" name="specifications" class="form-control"
                                                rows="6"><?php echo htmlspecialchars($project['specifications'] ?? ''); ?></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label>Notes internes</label>
                                            <textarea id="internal_notes" name="internal_notes" class="form-control"
                                                rows="4"><?php echo htmlspecialchars($project['internal_notes'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <!-- Métadonnées -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-settings-3-line mr-2"></i>Paramètres</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="status">Statut</label>
                                            <select class="form-control" id="status" name="status">
                                                <option value="draft" <?php echo $project['status'] == 'draft' ? 'selected' : ''; ?>>Brouillon</option>
                                                <option value="in_progress" <?php echo $project['status'] == 'in_progress' ? 'selected' : ''; ?>>En cours</option>
                                                <option value="completed" <?php echo $project['status'] == 'completed' ? 'selected' : ''; ?>>Terminé</option>
                                                <option value="delivered" <?php echo $project['status'] == 'delivered' ? 'selected' : ''; ?>>Livré</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="priority">Priorité</label>
                                            <select class="form-control" id="priority" name="priority">
                                                <option value="low" <?php echo $project['priority'] == 'low' ? 'selected' : ''; ?>>Basse</option>
                                                <option value="medium" <?php echo $project['priority'] == 'medium' ? 'selected' : ''; ?>>Moyenne</option>
                                                <option value="high" <?php echo $project['priority'] == 'high' ? 'selected' : ''; ?>>Haute</option>
                                                <option value="urgent" <?php echo $project['priority'] == 'urgent' ? 'selected' : ''; ?>>Urgente</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="progress">Progression (%)</label>
                                            <input type="range" class="custom-range" id="progress" name="progress"
                                                min="0" max="100" value="<?php echo $project['progress']; ?>">
                                            <div class="d-flex justify-content-between">
                                                <small>0%</small>
                                                <small id="progressValue"><?php echo $project['progress']; ?>%</small>
                                                <small>100%</small>
                                            </div>
                                        </div>

                                        <div class="custom-control custom-switch mb-3">
                                            <input type="checkbox" class="custom-control-input" id="featured"
                                                name="featured" <?php echo $project['featured'] ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="featured">Projet en vedette</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dates -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-calendar-line mr-2"></i>Dates</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="start_date">Date de début</label>
                                            <input type="date" class="form-control" id="start_date" name="start_date"
                                                value="<?php echo $project['start_date']; ?>">
                                        </div>

                                        <div class="form-group">
                                            <label for="end_date">Date de fin prévue</label>
                                            <input type="date" class="form-control" id="end_date" name="end_date"
                                                value="<?php echo $project['end_date']; ?>">
                                        </div>

                                        <div class="form-group">
                                            <label for="delivery_date">Date de livraison</label>
                                            <input type="date" class="form-control" id="delivery_date"
                                                name="delivery_date" value="<?php echo $project['delivery_date']; ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Budget -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-money-dollar-circle-line mr-2"></i>Budget</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="estimated_budget">Budget estimé (€)</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">€</span>
                                                </div>
                                                <input type="number" class="form-control" id="estimated_budget"
                                                    name="estimated_budget" step="0.01" min="0"
                                                    value="<?php echo $project['estimated_budget']; ?>">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="actual_budget">Budget réel (€)</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">€</span>
                                                </div>
                                                <input type="number" class="form-control" id="actual_budget"
                                                    name="actual_budget" step="0.01" min="0"
                                                    value="<?php echo $project['actual_budget']; ?>">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="currency">Devise</label>
                                            <select class="form-control" id="currency" name="currency">
                                                <option value="EUR" <?php echo $project['currency'] == 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                                                <option value="USD" <?php echo $project['currency'] == 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                                <option value="GBP" <?php echo $project['currency'] == 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Technologies et images -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-code-s-slash-line mr-2"></i>Technologies utilisées
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Sélectionnez les technologies</label>
                                            <select class="form-control select2-tags" id="technologies"
                                                name="technologies[]" multiple>
                                                <?php
                                                $tech_options = [
                                                    'HTML5',
                                                    'CSS3',
                                                    'JavaScript',
                                                    'PHP',
                                                    'MySQL',
                                                    'React',
                                                    'Vue.js',
                                                    'Laravel',
                                                    'Node.js',
                                                    'Python',
                                                    'Django',
                                                    'Flutter',
                                                    'React Native'
                                                ];
                                                foreach ($tech_options as $tech): ?>
                                                    <option value="<?php echo $tech; ?>" <?php echo in_array($tech, $project['technologies']) ? 'selected' : ''; ?>>
                                                        <?php echo $tech; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Images existantes -->
                                <?php if (!empty($project['images'])): ?>
                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="ri-image-line mr-2"></i>Images du projet</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row" id="existingImages">
                                                <?php foreach ($project['images'] as $image): ?>
                                                    <div class="col-md-3 col-sm-4 mb-3">
                                                        <div class="image-preview">
                                                            <img src="<?php echo UPLOAD_URL; ?>projects/<?php echo $image; ?>"
                                                                class="img-fluid rounded" alt="Image projet">
                                                            <div class="mt-2 text-center">
                                                                <button type="button" class="btn btn-sm btn-danger remove-image"
                                                                    data-image="<?php echo $image; ?>">
                                                                    <i class="ri-delete-bin-line"></i> Supprimer
                                                                </button>
                                                                <input type="hidden" name="delete_images[]" value=""
                                                                    data-image="<?php echo $image; ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Upload de nouvelles images -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-image-add-line mr-2"></i>Ajouter des images</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="project_images">Sélectionner des images</label>
                                            <input type="file" class="form-control-file" id="project_images"
                                                name="project_images[]" multiple accept="image/*">
                                            <small class="form-text text-muted">JPG, PNG, GIF, WebP (max. 5MB par
                                                image)</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- URLs -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-links-line mr-2"></i>Liens externes</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="demo_url">URL de démo</label>
                                            <input type="url" class="form-control" id="demo_url" name="demo_url"
                                                placeholder="https://demo.exemple.com"
                                                value="<?php echo htmlspecialchars($project['demo_url'] ?? ''); ?>">
                                        </div>

                                        <div class="form-group">
                                            <label for="github_url">URL GitHub</label>
                                            <input type="url" class="form-control" id="github_url" name="github_url"
                                                placeholder="https://github.com/username/project"
                                                value="<?php echo htmlspecialchars($project['github_url'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Boutons d'action -->
                                <div class="card">
                                    <div class="card-body text-center">
                                        <button type="submit" class="btn btn-primary btn-lg mr-2">
                                            <i class="ri-save-line mr-2"></i>Enregistrer les modifications
                                        </button>
                                        <a href="list.php" class="btn btn-outline-secondary btn-lg">
                                            <i class="ri-arrow-left-line mr-2"></i>Retour à la liste
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Mise à jour de la valeur de progression
        $('#progress').on('input', function () {
            $('#progressValue').text($(this).val() + '%');
        });

        // Initialiser Select2
        $('.select2').select2({
            theme: 'bootstrap4'
        });

        $('.select2-tags').select2({
            theme: 'bootstrap4',
            tags: true,
            tokenSeparators: [',', ' ']
        });

        // Gestion de la suppression des images
        $('.remove-image').click(function () {
            var imageName = $(this).data('image');
            var imageDiv = $(this).closest('.col-md-3');

            // Marquer l'image pour suppression
            $(this).siblings('input[type="hidden"]').val(imageName);

            // Masquer l'image avec effet
            imageDiv.fadeOut(300, function () {
                $(this).remove();
            });

            toastr.warning('L\'image sera supprimée lors de l\'enregistrement');
        });
    });
</script>

<?php include '../includes/footer.php'; ?>