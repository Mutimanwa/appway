<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../includes/service-functions.php';

// Vérifier la session admin (décommenter quand nécessaire)
// verifier_session(['admin']);

$page_title = isset($_GET['id']) ? "Modifier le Service" : "Nouveau Service";

// Gérer l'envoi du formulaire
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = saveService($_POST);

    if ($result['success']) {
        $message = $result['message'];
        $message_type = 'success';

        // Rediriger vers la liste après un succès
        header("refresh:2;url=list.php");
    } else {
        $message = $result['message'];
        $message_type = 'error';
    }
}

// Récupérer les données du service si modification
$service = null;
if (isset($_GET['id'])) {
    $service = getServiceById($_GET['id']);

    if (!$service) {
        header("Location: list.php");
        exit();
    }
}

include '../includes/header.php';
?>

<div class="contentbar">
    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ri-service-line mr-2"></i>
                            <?php echo $page_title; ?>
                        </h5>
                    </div>
                </div>
                <div class="card-body">

                    <?php if ($message): ?>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show"
                                    role="alert">
                                    <?php echo htmlspecialchars($message); ?>
                                    <?php if ($message_type === 'success'): ?>
                                        <br><small>Redirection vers la liste dans 2 secondes...</small>
                                    <?php endif; ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form id="serviceForm" action="" method="post">
                        <input type="hidden" name="id"
                            value="<?php echo isset($service['id']) ? $service['id'] : ''; ?>">

                        <div class="row">
                            <div class="col-lg-8">
                                <!-- Informations de base -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-information-line mr-2"></i>Informations du service
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="name">Nom du service *</label>
                                            <input type="text" class="form-control" id="name" name="name"
                                                value="<?php echo isset($service['name']) ? htmlspecialchars($service['name']) : ''; ?>"
                                                placeholder="Ex: Développement Web sur mesure" required>
                                            <small class="form-text text-muted">Nom attractif et descriptif</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="description">Description *</label>
                                            <textarea class="form-control" id="description" name="description" rows="4"
                                                placeholder="Décrivez votre service en détails..."
                                                required><?php echo isset($service['description']) ? htmlspecialchars($service['description']) : ''; ?></textarea>
                                            <small class="form-text text-muted">
                                                <span
                                                    id="descCount"><?php echo isset($service['description']) ? strlen($service['description']) : '0'; ?></span>/500
                                                caractères
                                            </small>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="category">Catégorie *</label>
                                                    <select class="form-control" id="category" name="category" required>
                                                        <option value="">Sélectionner...</option>
                                                        <option value="development" <?php echo (isset($service['category']) && $service['category'] == 'development') ? 'selected' : ''; ?>>
                                                            Développement</option>
                                                        <option value="design" <?php echo (isset($service['category']) && $service['category'] == 'design') ? 'selected' : ''; ?>>
                                                            Design</option>
                                                        <option value="consulting" <?php echo (isset($service['category']) && $service['category'] == 'consulting') ? 'selected' : ''; ?>>
                                                            Consulting</option>
                                                        <option value="maintenance" <?php echo (isset($service['category']) && $service['category'] == 'maintenance') ? 'selected' : ''; ?>>
                                                            Maintenance</option>
                                                        <option value="ia" <?php echo (isset($service['category']) && $service['category'] == 'ia') ? 'selected' : ''; ?>>
                                                            Intelligence Artificielle</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="price">Prix *</label>
                                                    <input type="text" class="form-control" id="price" name="price"
                                                        value="<?php echo isset($service['price']) ? htmlspecialchars($service['price']) : ''; ?>"
                                                        placeholder="Ex: 999€, Sur devis, Gratuit" required>
                                                    <small class="form-text text-muted">Format libre ou montant
                                                        numérique</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="duration">Durée estimée</label>
                                                    <input type="text" class="form-control" id="duration"
                                                        name="duration"
                                                        value="<?php echo isset($service['duration']) ? htmlspecialchars($service['duration']) : ''; ?>"
                                                        placeholder="Ex: 2-4 semaines, 1 mois, Flexible">
                                                    <small class="form-text text-muted">Temps de réalisation
                                                        estimé</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="display_order">Ordre d'affichage</label>
                                                    <input type="number" class="form-control" id="display_order"
                                                        name="display_order"
                                                        value="<?php echo isset($service['display_order']) ? $service['display_order'] : 0; ?>"
                                                        min="0" max="100">
                                                    <small class="form-text text-muted">Plus le chiffre est bas, plus le
                                                        service apparaît en haut</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Caractéristiques -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-list-check-2 mr-2"></i>Caractéristiques du service
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="featuresContainer">
                                            <?php
                                            $features = isset($service['features']) && is_array($service['features']) ? $service['features'] : [''];
                                            foreach ($features as $feature):
                                                ?>
                                                <div class="feature-item mb-3">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" name="features[]"
                                                            value="<?php echo htmlspecialchars($feature); ?>"
                                                            placeholder="Ex: Design responsive">
                                                        <div class="input-group-append">
                                                            <button type="button"
                                                                class="btn btn-outline-danger remove-feature">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <button type="button" id="addFeature" class="btn btn-outline-primary mt-2">
                                            <i class="ri-add-line mr-2"></i>Ajouter une caractéristique
                                        </button>
                                        <small class="form-text text-muted">Listez les points forts de votre
                                            service</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <!-- Visuel du service -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-image-line mr-2"></i>Visuel du service</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="icon">Icône</label>
                                            <input type="text" class="form-control" id="icon" name="icon"
                                                value="<?php echo isset($service['icon']) ? htmlspecialchars($service['icon']) : ''; ?>"
                                                placeholder="ri-code-s-slash-line">
                                            <small class="form-text text-muted">Classe d'icône Remix Icon (ex:
                                                ri-code-s-slash-line)</small>
                                            <div class="mt-2" id="iconPreview">
                                                <?php if (isset($service['icon']) && $service['icon']): ?>
                                                    <i class="<?php echo $service['icon']; ?> fs-3 text-primary"></i>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Paramètres -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-settings-3-line mr-2"></i>Paramètres</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="status">Statut</label>
                                            <select class="form-control" id="status" name="status">
                                                <option value="1" <?php echo (isset($service['is_active']) && $service['is_active']) ? 'selected' : ''; ?>>Actif</option>
                                                <option value="0" <?php echo (isset($service['is_active']) && !$service['is_active']) ? 'selected' : ''; ?>>Inactif</option>
                                            </select>
                                            <small class="form-text text-muted">Les services inactifs ne seront pas
                                                visibles</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Boutons d'action -->
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="ri-save-line mr-2"></i>
                                                <?php echo isset($service['id']) ? 'Mettre à jour' : 'Créer le service'; ?>
                                            </button>

                                            <a href="list.php" class="btn btn-outline-secondary">
                                                <i class="ri-arrow-go-back-line mr-2"></i>Retour à la liste
                                            </a>

                                            <?php if (isset($service['id'])): ?>
                                                <button type="button" class="btn btn-outline-danger" id="deleteBtn">
                                                    <i class="ri-delete-bin-line mr-2"></i>Supprimer
                                                </button>
                                            <?php endif; ?>
                                        </div>
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
    document.addEventListener('DOMContentLoaded', function () {
        // Compteur de caractères pour la description
        const description = document.getElementById('description');
        const descCount = document.getElementById('descCount');

        if (description && descCount) {
            description.addEventListener('input', function () {
                descCount.textContent = this.value.length;
            });
        }

        // Aperçu de l'icône
        const iconInput = document.getElementById('icon');
        const iconPreview = document.getElementById('iconPreview');

        if (iconInput && iconPreview) {
            iconInput.addEventListener('input', function () {
                const iconClass = this.value.trim();
                if (iconClass) {
                    iconPreview.innerHTML = `<i class="${iconClass} fs-3 text-primary"></i>`;
                } else {
                    iconPreview.innerHTML = '';
                }
            });
        }

        // Gestion des caractéristiques
        const addFeatureBtn = document.getElementById('addFeature');
        const featuresContainer = document.getElementById('featuresContainer');

        if (addFeatureBtn && featuresContainer) {
            addFeatureBtn.addEventListener('click', function () {
                const newFeature = document.createElement('div');
                newFeature.className = 'feature-item mb-3';
                newFeature.innerHTML = `
                <div class="input-group">
                    <input type="text" class="form-control" name="features[]" placeholder="Ex: Design responsive">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-danger remove-feature">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
            `;
                featuresContainer.appendChild(newFeature);
            });

            // Déléguation d'événements pour les boutons de suppression
            featuresContainer.addEventListener('click', function (e) {
                if (e.target.closest('.remove-feature')) {
                    const featureItem = e.target.closest('.feature-item');
                    if (featureItem) {
                        featureItem.remove();
                    }
                }
            });
        }

        // Validation du formulaire
        const form = document.getElementById('serviceForm');
        if (form) {
            form.addEventListener('submit', function (e) {
                let isValid = true;

                // Validation des champs requis
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(function (field) {
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Veuillez remplir tous les champs obligatoires (*)');
                }
            });
        }

        // Bouton de suppression
        const deleteBtn = document.getElementById('deleteBtn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function () {
                if (confirm('Êtes-vous sûr de vouloir supprimer ce service ? Cette action est irréversible.')) {
                    window.location.href = 'list.php?action=delete&id=<?php echo $service['id']; ?>';
                }
            });
        }
    });
</script>

<style>
    .feature-item:last-child .remove-feature {
        display: none;
    }

    .is-invalid {
        border-color: #dc3545;
    }

    #iconPreview i {
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .feature-item {
        position: relative;
    }

    .feature-item .remove-feature {
        transition: all 0.3s ease;
    }

    .feature-item .remove-feature:hover {
        background-color: #dc3545;
        color: white;
    }
</style>

<?php include '../includes/footer.php'; ?>