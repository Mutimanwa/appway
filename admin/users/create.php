<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../includes/user-functions.php';

// Vérifier l'accès admin
// verifier_session(['admin']);

$page_title = isset($_GET['id']) ? "Modifier l'Utilisateur" : "Nouvel Utilisateur";
$is_edit = isset($_GET['id']);
$user_id = $is_edit ? (int)$_GET['id'] : 0;

// Récupérer les données de l'utilisateur en mode édition
$user = null;
if ($is_edit) {
    $user = getUserById($user_id);
    if (!$user) {
        header('Location: users.php');
        exit;
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !verifier_token($_POST['csrf_token'])) {
        $error = "Token CSRF invalide. Veuillez réessayer.";
    } else {
        try {
            $data = $_POST;
            
            // Ajouter l'ID en mode édition
            if ($is_edit) {
                $data['id'] = $user_id;
                if (isset($user['avatar'])) {
                    $data['current_avatar'] = $user['avatar'];
                }
            }
            
            // Traiter le formulaire
            $result = saveUser($data, $_FILES);
            
            if ($result['success']) {
                // Redirection avec message de succès
                $_SESSION['message'] = $result['message'];
                $_SESSION['message_type'] = 'success';
                
                header('Location: list.php');
                exit;
            } else {
                $error = isset($result['message']) ? $result['message'] : "Erreur lors de l'enregistrement";
                $errors = isset($result['errors']) ? $result['errors'] : [];
                
                // Pré-remplir les champs avec les valeurs soumises
                $submitted_data = $data;
            }
        } catch (Exception $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    }
}

// Générer un token CSRF
$csrf_token = generer_token();

include '../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<div class="contentbar">
    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-user-add-line mr-2"></i>
                        <?php echo $is_edit ? 'Modifier l\'utilisateur' : 'Créer un nouvel utilisateur'; ?>
                    </h5>
                    <?php if ($is_edit): ?>
                    <small class="text-muted">ID: <?php echo $user['id']; ?> | Créé le: <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></small>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    
                    <!-- Messages d'erreur -->
                    <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ri-error-warning-line mr-2"></i><?php echo $error; ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Messages d'erreur de validation -->
                    <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="alert alert-warning">
                        <h6><i class="ri-alert-line mr-2"></i>Veuillez corriger les erreurs suivantes:</h6>
                        <ul class="mb-0">
                            <?php foreach ($errors as $field => $error_msg): ?>
                            <li><strong><?php echo ucfirst(str_replace('_', ' ', $field)); ?>:</strong> <?php echo $error_msg; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <form id="userCreateForm" action="" method="post" enctype="multipart/form-data">
                        <!-- Token CSRF -->
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name">Prénom *</label>
                                    <input type="text" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" 
                                           id="first_name" name="first_name" 
                                           value="<?php echo isset($submitted_data['first_name']) ? htmlspecialchars($submitted_data['first_name']) : ($user ? htmlspecialchars($user['first_name']) : ''); ?>" 
                                           required>
                                    <div class="invalid-feedback">
                                        <?php echo isset($errors['first_name']) ? $errors['first_name'] : 'Veuillez saisir un prénom'; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name">Nom *</label>
                                    <input type="text" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" 
                                           id="last_name" name="last_name" 
                                           value="<?php echo isset($submitted_data['last_name']) ? htmlspecialchars($submitted_data['last_name']) : ($user ? htmlspecialchars($user['last_name']) : ''); ?>" 
                                           required>
                                    <div class="invalid-feedback">
                                        <?php echo isset($errors['last_name']) ? $errors['last_name'] : 'Veuillez saisir un nom'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                           id="email" name="email" 
                                           value="<?php echo isset($submitted_data['email']) ? htmlspecialchars($submitted_data['email']) : ($user ? htmlspecialchars($user['email']) : ''); ?>" 
                                           required>
                                    <div class="invalid-feedback">
                                        <?php echo isset($errors['email']) ? $errors['email'] : 'Veuillez saisir un email valide'; ?>
                                    </div>
                                    <small class="form-text text-muted">L'utilisateur utilisera cet email pour se connecter</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Téléphone</label>
                                    <input type="tel" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                                           id="phone" name="phone" 
                                           value="<?php echo isset($submitted_data['phone']) ? htmlspecialchars($submitted_data['phone']) : ($user ? htmlspecialchars($user['phone']) : ''); ?>">
                                    <?php if (isset($errors['phone'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['phone']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company">Entreprise</label>
                                    <input type="text" class="form-control" id="company" name="company" 
                                           value="<?php echo isset($submitted_data['company']) ? htmlspecialchars($submitted_data['company']) : ($user ? htmlspecialchars($user['company']) : ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role">Rôle *</label>
                                    <select class="form-control <?php echo isset($errors['role']) ? 'is-invalid' : ''; ?>" 
                                            id="role" name="role" required>
                                        <option value="">Sélectionner un rôle</option>
                                        <option value="admin" <?php echo (isset($submitted_data['role']) && $submitted_data['role'] == 'admin') || ($user && $user['role'] == 'admin') ? 'selected' : ''; ?>>Administrateur</option>
                                        <option value="client" <?php echo (isset($submitted_data['role']) && $submitted_data['role'] == 'client') || ($user && $user['role'] == 'client') ? 'selected' : ''; ?>>Client</option>
                                        <option value="visitor" <?php echo (isset($submitted_data['role']) && $submitted_data['role'] == 'visitor') || ($user && $user['role'] == 'visitor') ? 'selected' : ''; ?>>Visiteur</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        <?php echo isset($errors['role']) ? $errors['role'] : 'Veuillez sélectionner un rôle'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section mot de passe -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Informations de connexion</h6>
                            </div>
                            <div class="card-body">
                                <?php if ($is_edit): ?>
                                <div class="alert alert-info">
                                    <i class="ri-information-line mr-2"></i>
                                    Laissez les champs de mot de passe vides pour conserver le mot de passe actuel.
                                </div>
                                <?php endif; ?>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password">
                                                <?php echo $is_edit ? 'Nouveau mot de passe' : 'Mot de passe *'; ?>
                                            </label>
                                            <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                                   id="password" name="password" 
                                                   <?php echo !$is_edit ? 'required' : ''; ?>>
                                            <div class="invalid-feedback">
                                                <?php echo isset($errors['password']) ? $errors['password'] : 'Le mot de passe doit contenir au moins 8 caractères'; ?>
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="ri-information-line"></i> Minimum 8 caractères
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="confirm_password">
                                                <?php echo $is_edit ? 'Confirmer le nouveau mot de passe' : 'Confirmer le mot de passe *'; ?>
                                            </label>
                                            <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                                   id="confirm_password" name="confirm_password"
                                                   <?php echo !$is_edit ? 'required' : ''; ?>>
                                            <div class="invalid-feedback">
                                                <?php echo isset($errors['confirm_password']) ? $errors['confirm_password'] : 'Les mots de passe ne correspondent pas'; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Force du mot de passe</label>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%"></div>
                                            </div>
                                            <small class="form-text text-muted" id="passwordHint"></small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="force_password_change" 
                                                   name="force_password_change" 
                                                   <?php echo (isset($submitted_data['force_password_change']) && $submitted_data['force_password_change']) || ($user && $user['force_password_change'] == 1) ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="force_password_change">
                                                Forcer le changement de mot de passe à la première connexion
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <?php if (!$is_edit): ?>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="send_welcome_email" 
                                                   name="send_welcome_email" checked>
                                            <label class="custom-control-label" for="send_welcome_email">
                                                Envoyer un email de bienvenue
                                            </label>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="avatar">Avatar</label>
                                    <?php if ($user && $user['avatar']): ?>
                                    <div class="mb-3">
                                        <img src="<?php echo ROOT_URL . '/uploads/avatars/' . $user['avatar']; ?>" 
                                             alt="Avatar" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                        <div class="mt-2">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="delete_avatar" name="delete_avatar">
                                                <label class="custom-control-label text-danger" for="delete_avatar">
                                                    <i class="ri-delete-bin-line mr-1"></i>Supprimer l'avatar
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp">
                                        <label class="custom-file-label" for="avatar">
                                            <?php echo $user && $user['avatar'] ? 'Changer l\'avatar...' : 'Choisir un avatar...'; ?>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">JPG, PNG, GIF ou WebP (max. 2MB). Taille recommandée: 200x200px</small>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="newsletter">Newsletter</label>
                                    <select class="form-control" id="newsletter" name="newsletter">
                                        <option value="1" <?php echo (isset($submitted_data['newsletter']) && $submitted_data['newsletter'] == 1) || ($user && $user['newsletter'] == 1) ? 'selected' : ''; ?>>Abonné</option>
                                        <option value="0" <?php echo (isset($submitted_data['newsletter']) && $submitted_data['newsletter'] == 0) || ($user && $user['newsletter'] == 0) ? 'selected' : ''; ?>>Non abonné</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="notifications">Notifications</label>
                                    <select class="form-control" id="notifications" name="notifications">
                                        <option value="1" <?php echo (isset($submitted_data['notifications']) && $submitted_data['notifications'] == 1) || ($user && $user['notifications'] == 1) ? 'selected' : ''; ?>>Activées</option>
                                        <option value="0" <?php echo (isset($submitted_data['notifications']) && $submitted_data['notifications'] == 0) || ($user && $user['notifications'] == 0) ? 'selected' : ''; ?>>Désactivées</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Statut du compte</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="1" <?php echo (isset($submitted_data['status']) && $submitted_data['status'] == 1) || ($user && $user['is_active'] == 1) ? 'selected' : ''; ?>>Actif</option>
                                        <option value="0" <?php echo (isset($submitted_data['status']) && $submitted_data['status'] == 0) || ($user && $user['is_active'] == 0) ? 'selected' : ''; ?>>Inactif</option>
                                        <option value="2" <?php echo (isset($submitted_data['status']) && $submitted_data['status'] == 2) || ($user && $user['is_active'] == 2) ? 'selected' : ''; ?>>En attente</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="language">Langue préférée</label>
                                    <select class="form-control" id="language" name="language">
                                        <option value="fr" <?php echo (isset($submitted_data['language']) && $submitted_data['language'] == 'fr') || ($user && $user['language'] == 'fr') ? 'selected' : ''; ?>>Français</option>
                                        <option value="en" <?php echo (isset($submitted_data['language']) && $submitted_data['language'] == 'en') || ($user && $user['language'] == 'en') ? 'selected' : ''; ?>>English</option>
                                        <option value="es" <?php echo (isset($submitted_data['language']) && $submitted_data['language'] == 'es') || ($user && $user['language'] == 'es') ? 'selected' : ''; ?>>Español</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes">Notes internes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Notes optionnelles sur cet utilisateur..."><?php echo isset($submitted_data['notes']) ? htmlspecialchars($submitted_data['notes']) : ($user ? htmlspecialchars($user['notes']) : ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <?php if (!$is_edit): ?>
                        <div class="custom-control custom-checkbox mt-3">
                            <input type="checkbox" class="custom-control-input" id="terms" name="terms" required>
                            <label class="custom-control-label" for="terms">
                                Je confirme que les informations saisies sont correctes et que je suis autorisé à créer ce compte.
                            </label>
                            <div class="invalid-feedback">Vous devez accepter les conditions</div>
                        </div>
                        <?php endif; ?>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line mr-2"></i>
                                <?php echo $is_edit ? 'Mettre à jour' : 'Créer l\'utilisateur'; ?>
                            </button>
                            <button type="reset" class="btn btn-outline-secondary ml-2">
                                <i class="ri-refresh-line mr-2"></i>Réinitialiser
                            </button>
                            <a href="users.php" class="btn btn-outline-danger ml-2">
                                <i class="ri-close-line mr-2"></i>Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    // Validation du mot de passe en temps réel
    $("#password").on("input", function () {
        var password = $(this).val();
        var strength = 0;
        var hint = "";

        if (password.length >= 8) strength += 25;
        if (/[A-Z]/.test(password)) strength += 25;
        if (/[0-9]/.test(password)) strength += 25;
        if (/[^A-Za-z0-9]/.test(password)) strength += 25;

        $("#passwordStrength").css("width", strength + "%");

        if (strength < 50) {
            $("#passwordStrength").removeClass("bg-success bg-warning").addClass("bg-danger");
            hint = "Faible";
        } else if (strength < 75) {
            $("#passwordStrength").removeClass("bg-danger bg-success").addClass("bg-warning");
            hint = "Moyen";
        } else {
            $("#passwordStrength").removeClass("bg-danger bg-warning").addClass("bg-success");
            hint = "Fort";
        }

        $("#passwordHint").text("Force: " + hint);

        // Vérification de la correspondance
        var confirm = $("#confirm_password").val();
        if (confirm && password !== confirm) {
            $("#confirm_password").addClass("is-invalid");
        } else {
            $("#confirm_password").removeClass("is-invalid");
        }
    });

    $("#confirm_password").on("input", function () {
        if ($(this).val() !== $("#password").val()) {
            $(this).addClass("is-invalid");
        } else {
            $(this).removeClass("is-invalid");
        }
    });

    // Afficher le nom du fichier sélectionné pour l'avatar
    $('.custom-file-input').on('change', function () {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // Validation du formulaire
    $("#userCreateForm").submit(function (e) {
        var isValid = true;
        var isEdit = <?php echo $is_edit ? 'true' : 'false'; ?>;

        // Validation des champs requis
        $("[required]").each(function () {
            if ($(this).is(":checkbox") && !$(this).is(":checked")) {
                $(this).addClass("is-invalid");
                isValid = false;
            } else if ($(this).is("select") && $(this).val() === "") {
                $(this).addClass("is-invalid");
                isValid = false;
            } else if (!$(this).val().trim()) {
                $(this).addClass("is-invalid");
                isValid = false;
            } else {
                $(this).removeClass("is-invalid");
            }
        });

        // Validation spécifique de l'email
        var email = $("#email").val();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            $("#email").addClass("is-invalid");
            isValid = false;
        }

        // Validation des mots de passe
        if (!isEdit) {
            // En mode création, les mots de passe sont requis
            var password = $("#password").val();
            var confirmPassword = $("#confirm_password").val();

            if (password.length < 8) {
                $("#password").addClass("is-invalid");
                isValid = false;
            }

            if (password !== confirmPassword) {
                $("#confirm_password").addClass("is-invalid");
                isValid = false;
            }
        } else {
            // En mode édition, vérifier seulement si les champs sont remplis
            var password = $("#password").val();
            var confirmPassword = $("#confirm_password").val();

            if (password || confirmPassword) {
                if (password.length < 8) {
                    $("#password").addClass("is-invalid");
                    isValid = false;
                }

                if (password !== confirmPassword) {
                    $("#confirm_password").addClass("is-invalid");
                    isValid = false;
                }
            }
        }

        // Validation du téléphone
        var phone = $("#phone").val();
        if (phone && !/^[0-9\s\+\-\(\)]{10,20}$/.test(phone)) {
            $("#phone").addClass("is-invalid");
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            toastr.error("Veuillez corriger les erreurs dans le formulaire.");
        }
    });

    // Réinitialisation du formulaire
    $("button[type='reset']").click(function () {
        // Ne pas réinitialiser en mode édition
        if (!<?php echo $is_edit ? 'true' : 'false'; ?>) {
            $("#userCreateForm")[0].reset();
            $(".form-control").removeClass("is-invalid");
            $(".custom-file-label").html("Choisir un fichier");
            $("#passwordStrength").css("width", "0%");
            $("#passwordHint").text("");
        }
    });

    // Suppression d'avatar
    $("#delete_avatar").change(function() {
        if ($(this).is(":checked")) {
            toastr.warning("L'avatar sera supprimé lors de la sauvegarde.");
        }
    });
});
</script>

<style>
    .custom-file-label::after {
        content: "Parcourir";
    }

    .progress {
        border-radius: 4px;
        background-color: #e9ecef;
    }

    .progress-bar {
        transition: width 0.3s ease;
    }

    .invalid-feedback {
        display: none;
    }

    .is-invalid ~ .invalid-feedback {
        display: block;
    }

    .is-invalid {
        border-color: #dc3545;
    }

    .img-thumbnail {
        border-radius: 50%;
    }
</style>

<?php include '../includes/footer.php'; ?>