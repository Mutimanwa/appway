<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../includes/article-functions.php';

// verifier_session(['admin']);

// Déterminer si c'est une création ou une modification
$is_edit = isset($_GET['id']);
$page_title = $is_edit ? "Modifier l'Article" : "Nouvel Article";

// Récupérer l'article si modification
$article = null;
$post_images = [];
if ($is_edit) {
    $article = getArticleById($_GET['id']);
    if (!$article) {
        header("Location: list.php");
        exit();
    }
    $post_images = isset($article['post_images']) ? $article['post_images'] : [];
}

// Générer un slug par défaut si création
$default_slug = $is_edit ? $article['slug'] : 'article-' . date('Y-m-d') . '-' . uniqid();

// Gérer l'envoi du formulaire
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = saveArticle($_POST, $_FILES);

    if ($result['success']) {
        $message = $result['message'];
        $message_type = 'success';

        // Rediriger vers la liste après un succès
        header("refresh:2;url=list.php");
    } else {
        $message = isset($result['errors']) ? implode('<br>', $result['errors']) : $result['message'];
        $message_type = 'error';
    }
}

include '../includes/header.php';
?>

<div class="contentbar">
    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-article-line mr-2"></i><?php echo $page_title; ?>
                    </h5>
                </div>
                <div class="card-body">

                    <?php if ($message): ?>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show"
                                    role="alert">
                                    <?php echo $message; ?>
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

                    <form id="articleForm" action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $is_edit ? $article['id'] : ''; ?>">

                        <?php if ($is_edit && isset($article['featured_image_data']['url'])): ?>
                            <input type="hidden" name="current_featured_image"
                                value="<?php echo htmlspecialchars(json_encode($article['featured_image_data'])); ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-lg-8">
                                <!-- Éditeur principal -->
                                <div class="form-group">
                                    <label for="title">Titre de l'article *</label>
                                    <input type="text" class="form-control form-control-lg" id="title" name="title"
                                        value="<?php echo $is_edit ? htmlspecialchars($article['title']) : ''; ?>"
                                        placeholder="Saisissez un titre accrocheur..." required>
                                    <small class="form-text text-muted">Le titre apparaîtra en tête de l'article</small>
                                </div>

                                <div class="form-group">
                                    <label for="slug">Slug (URL) *</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><?php echo ROOT_URL; ?>/blog/</span>
                                        </div>
                                        <input type="text" class="form-control" id="slug" name="slug"
                                            value="<?php echo $default_slug; ?>" required>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" id="generateSlug">
                                                <i class="ri-refresh-line"></i> Générer
                                            </button>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">URL SEO-friendly (sans accents, espaces
                                        remplacés par des tirets)</small>
                                </div>

                                <div class="form-group">
                                    <label for="excerpt">Extrait *</label>
                                    <textarea class="form-control" id="excerpt" name="excerpt" rows="3"
                                        placeholder="Court résumé de l'article (150-200 caractères)..." maxlength="200"
                                        required><?php echo $is_edit ? htmlspecialchars($article['excerpt']) : ''; ?></textarea>
                                    <small class="form-text text-muted">
                                        <span
                                            id="excerptCount"><?php echo $is_edit ? strlen($article['excerpt']) : '0'; ?></span>/200
                                        caractères
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label>Contenu de l'article *</label>
                                    <div class="summernote" id="summernote">
                                        <?php echo $is_edit ? $article['content'] : ''; ?>
                                    </div>
                                    <input type="hidden" id="content" name="content"
                                        value="<?php echo $is_edit ? htmlspecialchars($article['content']) : ''; ?>">
                                </div>

                                <!-- Images de l'article -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-image-2-line mr-2"></i>Images de l'article</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="featured_image">Image à la une</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id=""
                                                            name="featured_image" accept="image/*">
                                                        <label class="custom-file-label" for="featured_image">
                                                            <?php echo ($is_edit && isset($article['featured_image_data']['url'])) ? htmlspecialchars($article['featured_image_data']['url']) : 'Choisir une image...'; ?>
                                                        </label>
                                                    </div>
                                                    <small class="form-text text-muted">Image principale de l'article
                                                        (recommandé: 1200x630px)</small>
                                                    <div class="mt-2" id="featuredPreview">
                                                        <?php if ($is_edit && isset($article['featured_image_data']['url'])): ?>
                                                            <img src="<?php echo UPLOAD_URL . 'articles/' . $article['featured_image_data']['url']; ?>"
                                                                class="img-fluid rounded" style="max-height: 200px;">
                                                            <div class="mt-2">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input"
                                                                        id="delete_featured_image"
                                                                        name="delete_featured_image">
                                                                    <label class="custom-control-label text-danger"
                                                                        for="delete_featured_image">
                                                                        Supprimer cette image
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Images supplémentaires</label>
                                                    <div class="mb-3" id="existingImages">
                                                        <?php if (!empty($post_images)): ?>
                                                            <?php foreach ($post_images as $image): ?>
                                                                <div class="image-item mb-2"
                                                                    data-id="<?php echo $image['id']; ?>">
                                                                    <div class="d-flex align-items-center">
                                                                        <img src="<?php echo UPLOAD_URL . 'articles/' . $image['IMAGES_URL']; ?>"
                                                                            class="img-thumbnail mr-2"
                                                                            style="width: 50px; height: 50px;">
                                                                        <div class="flex-grow-1">
                                                                            <small><?php echo $image['IMAGES_URL']; ?></small>
                                                                            <div class="custom-control custom-checkbox">
                                                                                <input type="checkbox"
                                                                                    class="custom-control-input"
                                                                                    id="delete_image_<?php echo $image['id']; ?>"
                                                                                    name="delete_images[]"
                                                                                    value="<?php echo $image['id']; ?>">
                                                                                <label class="custom-control-label text-danger"
                                                                                    for="delete_image_<?php echo $image['id']; ?>">
                                                                                    Supprimer
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="new_images">Ajouter des images</label>
                                                        <input type="file" class="form-control" id="new_images"
                                                            name="new_images[]" accept="image/*" multiple>
                                                        <small class="form-text text-muted">Sélectionnez plusieurs
                                                            images</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <!-- Publication -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-send-plane-line mr-2"></i>Publication</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="status">Statut</label>
                                            <select class="form-control" id="status" name="status">
                                                <option value="draft" <?php echo ($is_edit && $article['status'] == 'draft') ? 'selected' : ''; ?>>Brouillon
                                                </option>
                                                <option value="published" <?php echo ($is_edit && $article['status'] == 'published') ? 'selected' : ''; ?>>Publié
                                                </option>
                                                <option value="archived" <?php echo ($is_edit && $article['status'] == 'archived') ? 'selected' : ''; ?>>Archivé
                                                </option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="published_at">Date de publication</label>
                                            <input type="datetime-local" class="form-control" id="published_at"
                                                name="published_at"
                                                value="<?php echo $is_edit && $article['published_at'] ? date('Y-m-d\TH:i', strtotime($article['published_at'])) : ''; ?>">
                                            <small class="form-text text-muted">Si vide, utilise la date actuelle lors
                                                de la publication</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="author_id">Auteur *</label>
                                            <select class="form-control" id="author_id" name="author_id" required>
                                                <option value="">Sélectionner...</option>
                                                <?php
                                                $pdo = getConnexion();
                                                $authors = $pdo->query("SELECT id, first_name, last_name FROM users WHERE role = 'admin' ORDER BY last_name")->fetchAll();
                                                foreach ($authors as $author):
                                                    ?>
                                                    <option value="<?php echo $author['id']; ?>" <?php echo ($is_edit && $article['author_id'] == $author['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($author['first_name'] . ' ' . $author['last_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Catégorie et tags -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-price-tag-3-line mr-2"></i>Catégorie & Tags</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="category">Catégorie *</label>
                                            <select class="form-control" id="category" name="category" required>
                                                <option value="">Sélectionner...</option>
                                                <option value="tech" <?php echo ($is_edit && $article['category'] == 'tech') ? 'selected' : ''; ?>>Technologie
                                                </option>
                                                <option value="business" <?php echo ($is_edit && $article['category'] == 'business') ? 'selected' : ''; ?>>Business
                                                </option>
                                                <option value="tutorial" <?php echo ($is_edit && $article['category'] == 'tutorial') ? 'selected' : ''; ?>>Tutoriel
                                                </option>
                                                <option value="news" <?php echo ($is_edit && $article['category'] == 'news') ? 'selected' : ''; ?>>Actualités
                                                </option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="tags">Tags</label>
                                            <select class="form-control select2-tags" id="tags" name="tags[]" multiple>
                                                <?php
                                                $default_tags = ['php', 'javascript', 'web-development', 'design', 'seo', 'mobile', 'cloud', 'ia'];
                                                $current_tags = $is_edit ? $article['tags'] : [];

                                                foreach ($default_tags as $tag):
                                                    ?>
                                                    <option value="<?php echo $tag; ?>" <?php echo (in_array($tag, $current_tags)) ? 'selected' : ''; ?>>
                                                        <?php echo ucfirst($tag); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="form-text text-muted">Sélectionnez ou tapez de nouveaux
                                                tags</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- SEO -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-search-line mr-2"></i>SEO</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="meta_title">Meta Title</label>
                                            <input type="text" class="form-control" id="meta_title" name="meta_title"
                                                value="<?php echo $is_edit ? htmlspecialchars($article['meta_title']) : ''; ?>"
                                                placeholder="Généré automatiquement si vide">
                                            <small class="form-text text-muted">Titre pour les moteurs de recherche
                                                (50-60 caractères)</small>
                                            <small class="form-text text-muted">
                                                <span
                                                    id="metaTitleCount"><?php echo $is_edit ? strlen($article['meta_title']) : '0'; ?></span>/60
                                                caractères
                                            </small>
                                        </div>

                                        <div class="form-group">
                                            <label for="meta_description">Meta Description</label>
                                            <textarea class="form-control" id="meta_description" name="meta_description"
                                                rows="3"
                                                placeholder="Description pour les moteurs de recherche..."><?php echo $is_edit ? htmlspecialchars($article['meta_description']) : ''; ?></textarea>
                                            <small class="form-text text-muted">Description pour les moteurs de
                                                recherche (150-160 caractères)</small>
                                            <small class="form-text text-muted">
                                                <span
                                                    id="metaDescCount"><?php echo $is_edit ? strlen($article['meta_description']) : '0'; ?></span>/160
                                                caractères
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Boutons d'action -->
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="ri-save-line mr-2"></i>
                                                <?php echo $is_edit ? 'Mettre à jour' : 'Créer l\'article'; ?>
                                            </button>

                                            <?php if ($is_edit): ?>
                                                <a href="<?php echo ROOT_URL; ?>/blog/article.php?slug=<?php echo $article['slug']; ?>"
                                                    class="btn btn-outline-info" target="_blank">
                                                    <i class="ri-eye-line mr-2"></i>Voir l'article
                                                </a>
                                            <?php endif; ?>

                                            <a href="list.php" class="btn btn-outline-secondary">
                                                <i class="ri-arrow-go-back-line mr-2"></i>Retour à la liste
                                            </a>

                                            <?php if ($is_edit): ?>
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

<style>
    .note-editor.note-frame {
        border: 1px solid #ced4da;
        border-radius: .25rem;
    }

    .note-editor.note-frame .note-toolbar {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        border-radius: .25rem .25rem 0 0;
    }

    .char-counter {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .char-counter.warning {
        color: #ffc107;
    }

    .char-counter.danger {
        color: #dc3545;
    }

    #featuredPreview img {
        max-width: 100%;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    #featuredPreview img:hover {
        transform: scale(1.02);
    }

    .btn-lg {
        padding: .75rem 1.5rem;
        font-size: 1.1rem;
    }

    .d-grid {
        display: grid;
    }

    .gap-2 {
        gap: 1rem;
    }

    .input-group-text {
        background-color: #f8f9fa;
        font-family: monospace;
        font-size: 0.9rem;
    }

    .image-item {
        padding: 10px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
    }
</style>
<?php include '../includes/footer.php'; ?>
<script>
    $(document).ready(function () {
        // Initialiser Summernote
        $('#summernote').summernote({
            height: 400,
            lang: 'fr-FR',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            callbacks: {
                onChange: function (contents) {
                    $('#content').val(contents);
                }
            }
        });

        // Générer le slug à partir du titre
        $('#title').on('input', function () {
            if (!$('#slug').data('modified')) {
                var title = $(this).val();
                var slug = title.toLowerCase()
                    .replace(/[^\w\s]/gi, '')
                    .replace(/\s+/g, '-')
                    .replace(/--+/g, '-')
                    .replace(/^-+|-+$/g, '');
                $('#slug').val(slug);

                // Meta title automatique
                if (!$('#meta_title').data('modified')) {
                    $('#meta_title').val(title + ' | PCL Lab');
                }
            }
        });

        // Marquer le slug comme modifié manuellement
        $('#slug').on('input', function () {
            $(this).data('modified', true);
        });

        // Générer un slug manuellement
        $('#generateSlug').click(function () {
            var title = $('#title').val() || 'article-' + Date.now();
            var slug = title.toLowerCase()
                .replace(/[^\w\s]/gi, '')
                .replace(/\s+/g, '-')
                .replace(/--+/g, '-')
                .replace(/^-+|-+$/g, '');
            $('#slug').val(slug).data('modified', true);
        });

        // Marquer le meta title comme modifié manuellement
        $('#meta_title').on('input', function () {
            $(this).data('modified', true);
        });

        // Compteur de caractères pour l'extrait
        $('#excerpt').on('input', function () {
            var length = $(this).val().length;
            $('#excerptCount').text(length);

            if (length > 200) {
                $(this).val($(this).val().substring(0, 200));
                $('#excerptCount').text(200);
            }
        });

        // Compteur de caractères pour le meta title
        $('#meta_title').on('input', function () {
            var length = $(this).val().length;
            $('#metaTitleCount').text(length);

            if (length > 60) {
                $('#metaTitleCount').addClass('text-danger');
            } else if (length > 50) {
                $('#metaTitleCount').addClass('text-warning');
            } else {
                $('#metaTitleCount').removeClass('text-warning text-danger');
            }
        });

        // Compteur de caractères pour la meta description
        $('#meta_description').on('input', function () {
            var length = $(this).val().length;
            $('#metaDescCount').text(length);

            if (length > 160) {
                $('#metaDescCount').addClass('text-danger');
            } else if (length > 150) {
                $('#metaDescCount').addClass('text-warning');
            } else {
                $('#metaDescCount').removeClass('text-warning text-danger');
            }
        });

        // Initialiser Select2 pour les tags
        $('.select2-tags').select2({
            tags: true,
            tokenSeparators: [',', ' '],
            width: '100%',
            theme: 'bootstrap'
        });

        // Aperçu de l'image à la une
        $('#featured_image').on('change', function (e) {
            var file = e.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#featuredPreview').html(`
                    <div class="mt-2">
                        <img src="${e.target.result}" class="img-fluid rounded" style="max-height: 200px;" alt="Aperçu">
                        <div class="mt-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" 
                                       id="delete_featured_image" name="delete_featured_image">
                                <label class="custom-control-label text-danger" for="delete_featured_image">
                                    Supprimer cette image
                                </label>
                            </div>
                        </div>
                    </div>
                `);
                }
                reader.readAsDataURL(file);
            }
        });

        // Validation du formulaire
        $('#articleForm').on('submit', function (e) {
            let isValid = true;

            // Validation des champs requis
            const requiredFields = $(this).find('[required]');
            requiredFields.each(function () {
                if (!$(this).val().trim()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            // Validation du slug
            const slug = $('#slug').val();
            const slugPattern = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;
            if (!slugPattern.test(slug)) {
                $('#slug').addClass('is-invalid');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                alert('Veuillez corriger les erreurs dans le formulaire.');
            } else {
                // Récupérer le contenu de Summernote
                var content = $('#summernote').summernote('code');
                $('#content').val(content);
            }
        });

        // Bouton de suppression
        $('#deleteBtn').click(function () {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet article ? Cette action est irréversible.')) {
                window.location.href = 'list.php?action=delete&id=<?php echo $article['id']; ?>';
            }
        });

        // Afficher le nom du fichier sélectionné
        $('.custom-file-input').on('change', function () {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').text(fileName);
        });
    });
</script>