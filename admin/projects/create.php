<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../includes/project-functions.php';

// Vérifier la session si nécessaire
// verifier_session(['admin']);

$page_title = "Nouveau Projet";
$message = '';
$error = '';

// Vérifier si c'est un formulaire soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Gérer l'upload des images
        $uploaded_images = [];
        if (!empty($_FILES['project_images'])) {
            // Traitement des images uploadées via le champ multiple
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
                    $uploaded_images[] = $image_name;
                }
            }
        }

        // Préparer les données
        $data = [
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
            'project_images' => $uploaded_images
        ];

        // Sauvegarder le projet
        $result = saveProject($data);

        if ($result['success']) {
            $message = 'Projet créé avec succès!';
            // Redirection vers la liste ou l'édition
            header("Location: edit.php?id=" . $result['id'] . "&success=1");
            exit();
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
                    <h5 class="card-title mb-0">
                        <i class="ri-projector-line mr-2"></i>Créer un nouveau projet
                    </h5>
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
                                                value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                                            <small class="form-text text-muted">Un titre clair et descriptif</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="description">Description *</label>
                                            <textarea class="form-control" id="description" name="description" rows="4"
                                                placeholder="Décrivez le projet en détails..."
                                                required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="category">Catégorie *</label>
                                                    <select class="form-control select2" id="category" name="category"
                                                        required>
                                                        <option value="">Sélectionner...</option>
                                                        <option value="web" <?php echo (isset($_POST['category']) && $_POST['category'] == 'web') ? 'selected' : ''; ?>>Web
                                                        </option>
                                                        <option value="mobile" <?php echo (isset($_POST['category']) && $_POST['category'] == 'mobile') ? 'selected' : ''; ?>>Mobile
                                                        </option>
                                                        <option value="desktop" <?php echo (isset($_POST['category']) && $_POST['category'] == 'desktop') ? 'selected' : ''; ?>>Desktop
                                                        </option>
                                                        <option value="cloud" <?php echo (isset($_POST['category']) && $_POST['category'] == 'cloud') ? 'selected' : ''; ?>>Cloud
                                                        </option>
                                                        <option value="ia" <?php echo (isset($_POST['category']) && $_POST['category'] == 'ia') ? 'selected' : ''; ?>>IA</option>
                                                        <option value="database" <?php echo (isset($_POST['category']) && $_POST['category'] == 'database') ? 'selected' : ''; ?>>
                                                            Base de données</option>
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
                                                            <option value="<?php echo $client['id']; ?>" <?php echo (isset($_POST['client_id']) && $_POST['client_id'] == $client['id']) ? 'selected' : ''; ?>>
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
                                                rows="6"><?php echo isset($_POST['specifications']) ? htmlspecialchars($_POST['specifications']) : ''; ?></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label>Notes internes</label>
                                            <textarea id="internal_notes" name="internal_notes" class="form-control"
                                                rows="4"><?php echo isset($_POST['internal_notes']) ? htmlspecialchars($_POST['internal_notes']) : ''; ?></textarea>
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
                                                <option value="draft" <?php echo (isset($_POST['status']) && $_POST['status'] == 'draft') ? 'selected' : 'selected'; ?>>Brouillon
                                                </option>
                                                <option value="in_progress" <?php echo (isset($_POST['status']) && $_POST['status'] == 'in_progress') ? 'selected' : ''; ?>>En cours
                                                </option>
                                                <option value="completed" <?php echo (isset($_POST['status']) && $_POST['status'] == 'completed') ? 'selected' : ''; ?>>Terminé
                                                </option>
                                                <option value="delivered" <?php echo (isset($_POST['status']) && $_POST['status'] == 'delivered') ? 'selected' : ''; ?>>Livré</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="priority">Priorité</label>
                                            <select class="form-control" id="priority" name="priority">
                                                <option value="low" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'low') ? 'selected' : ''; ?>>Basse</option>
                                                <option value="medium" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'medium') ? 'selected' : 'selected'; ?>>Moyenne
                                                </option>
                                                <option value="high" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'high') ? 'selected' : ''; ?>>Haute</option>
                                                <option value="urgent" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'urgent') ? 'selected' : ''; ?>>Urgente</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="progress">Progression (%)</label>
                                            <input type="range" class="custom-range" id="progress" name="progress"
                                                min="0" max="100"
                                                value="<?php echo isset($_POST['progress']) ? $_POST['progress'] : 0; ?>">
                                            <div class="d-flex justify-content-between">
                                                <small>0%</small>
                                                <small
                                                    id="progressValue"><?php echo isset($_POST['progress']) ? $_POST['progress'] : 0; ?>%</small>
                                                <small>100%</small>
                                            </div>
                                        </div>

                                        <div class="custom-control custom-switch mb-3">
                                            <input type="checkbox" class="custom-control-input" id="featured"
                                                name="featured" <?php echo (isset($_POST['featured']) && $_POST['featured']) ? 'checked' : ''; ?>>
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
                                                value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>">
                                        </div>

                                        <div class="form-group">
                                            <label for="end_date">Date de fin prévue</label>
                                            <input type="date" class="form-control" id="end_date" name="end_date"
                                                value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ''; ?>">
                                        </div>

                                        <div class="form-group">
                                            <label for="delivery_date">Date de livraison</label>
                                            <input type="date" class="form-control" id="delivery_date"
                                                name="delivery_date"
                                                value="<?php echo isset($_POST['delivery_date']) ? $_POST['delivery_date'] : ''; ?>">
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
                                                    value="<?php echo isset($_POST['estimated_budget']) ? $_POST['estimated_budget'] : ''; ?>">
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
                                                    value="<?php echo isset($_POST['actual_budget']) ? $_POST['actual_budget'] : ''; ?>">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="currency">Devise</label>
                                            <select class="form-control" id="currency" name="currency">
                                                <option value="EUR" <?php echo (isset($_POST['currency']) && $_POST['currency'] == 'EUR') ? 'selected' : 'selected'; ?>>EUR (€)
                                                </option>
                                                <option value="USD" <?php echo (isset($_POST['currency']) && $_POST['currency'] == 'USD') ? 'selected' : ''; ?>>USD ($)</option>
                                                <option value="GBP" <?php echo (isset($_POST['currency']) && $_POST['currency'] == 'GBP') ? 'selected' : ''; ?>>GBP (£)</option>
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
                                                <option value="HTML5" <?php echo (isset($_POST['technologies']) && in_array('HTML5', $_POST['technologies'])) ? 'selected' : ''; ?>>
                                                    HTML5</option>
                                                <option value="CSS3" <?php echo (isset($_POST['technologies']) && in_array('CSS3', $_POST['technologies'])) ? 'selected' : ''; ?>>CSS3
                                                </option>
                                                <option value="JavaScript" <?php echo (isset($_POST['technologies']) && in_array('JavaScript', $_POST['technologies'])) ? 'selected' : ''; ?>>JavaScript</option>
                                                <option value="PHP" <?php echo (isset($_POST['technologies']) && in_array('PHP', $_POST['technologies'])) ? 'selected' : ''; ?>>PHP
                                                </option>
                                                <option value="MySQL" <?php echo (isset($_POST['technologies']) && in_array('MySQL', $_POST['technologies'])) ? 'selected' : ''; ?>>
                                                    MySQL</option>
                                                <option value="React" <?php echo (isset($_POST['technologies']) && in_array('React', $_POST['technologies'])) ? 'selected' : ''; ?>>
                                                    React</option>
                                                <option value="Vue.js" <?php echo (isset($_POST['technologies']) && in_array('Vue.js', $_POST['technologies'])) ? 'selected' : ''; ?>>
                                                    Vue.js</option>
                                                <option value="Laravel" <?php echo (isset($_POST['technologies']) && in_array('Laravel', $_POST['technologies'])) ? 'selected' : ''; ?>>
                                                    Laravel</option>
                                                <option value="Node.js" <?php echo (isset($_POST['technologies']) && in_array('Node.js', $_POST['technologies'])) ? 'selected' : ''; ?>>
                                                    Node.js</option>
                                                <option value="Python" <?php echo (isset($_POST['technologies']) && in_array('Python', $_POST['technologies'])) ? 'selected' : ''; ?>>
                                                    Python</option>
                                                <option value="Django" <?php echo (isset($_POST['technologies']) && in_array('Django', $_POST['technologies'])) ? 'selected' : ''; ?>>
                                                    Django</option>
                                                <option value="Flutter" <?php echo (isset($_POST['technologies']) && in_array('Flutter', $_POST['technologies'])) ? 'selected' : ''; ?>>
                                                    Flutter</option>
                                                <option value="React Native" <?php echo (isset($_POST['technologies']) && in_array('React Native', $_POST['technologies'])) ? 'selected' : ''; ?>>React Native</option>
                                            </select>
                                            <small class="form-text text-muted">Ajoutez des tags en tapant puis appuyez
                                                sur Entrée</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Upload d'images -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-image-line mr-2"></i>Images du projet</h6>
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
                                                value="<?php echo isset($_POST['demo_url']) ? htmlspecialchars($_POST['demo_url']) : ''; ?>">
                                        </div>

                                        <div class="form-group">
                                            <label for="github_url">URL GitHub</label>
                                            <input type="url" class="form-control" id="github_url" name="github_url"
                                                placeholder="https://github.com/username/project"
                                                value="<?php echo isset($_POST['github_url']) ? htmlspecialchars($_POST['github_url']) : ''; ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Boutons d'action -->
                                <div class="card">
                                    <div class="card-body text-center">
                                        <button type="submit" class="btn btn-primary btn-lg mr-2">
                                            <i class="ri-save-line mr-2"></i>Créer le projet
                                        </button>
                                        <button type="reset" class="btn btn-outline-secondary btn-lg">
                                            <i class="ri-refresh-line mr-2"></i>Réinitialiser
                                        </button>
                                        <a href="<?php echo ADMIN_URL; ?>/projects/list.php"
                                            class="btn btn-outline-danger btn-lg ml-2">
                                            <i class="ri-close-line mr-2"></i>Annuler
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
    });
</script>

<?php include '../includes/footer.php'; ?>