<?php
/**
 * Fonctions pour la gestion des articles
 */

/**
 * Créer ou mettre à jour un article
 */
function saveArticle($data, $files = [])
{
    $pdo = getConnexion();

    try {
        // Validation des données
        $errors = validateArticleData($data, isset($data['id']) ? $data['id'] : null);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Préparer les données selon la structure SQL
        $title = nettoyer_input($data['title']);
        $slug = nettoyer_input($data['slug']);
        $excerpt = nettoyer_input($data['excerpt']);
        $content = $data['content'];
        $author_id = (int) $data['author_id'];
        $category = nettoyer_input($data['category']);
        $status = nettoyer_input($data['status']);

        // Gérer les tags (JSON dans la table)
        $tags = [];
        if (isset($data['tags']) && is_array($data['tags'])) {
            foreach ($data['tags'] as $tag) {
                $clean_tag = nettoyer_input($tag);
                if (!empty($clean_tag)) {
                    $tags[] = $clean_tag;
                }
            }
        }
        $tags_json = !empty($tags) ? json_encode($tags, JSON_UNESCAPED_UNICODE) : null;

        // Gérer l'image à la une (JSON dans la table)
        $featured_image_data = [];

        // Upload de la nouvelle image à la une
        if (isset($files['featured_image']) && $files['featured_image']['error'] === UPLOAD_ERR_OK) {
            $image_filename = uploadArticleImage($files['featured_image'], 'featured');
            $featured_image_data = [
                'url' => $image_filename,
                'alt' => $title,
                'caption' => ''
            ];
        } elseif (isset($data['current_featured_image']) && !empty($data['current_featured_image'])) {
            // Conserver l'image existante
            $featured_image_data = json_decode($data['current_featured_image'], true) ?? [];
        }

        $featured_image_json = !empty($featured_image_data) ? json_encode($featured_image_data, JSON_UNESCAPED_UNICODE) : null;

        // Gérer les métas SEO
        $meta_title = isset($data['meta_title']) ? nettoyer_input($data['meta_title']) : null;
        $meta_description = isset($data['meta_description']) ? nettoyer_input($data['meta_description']) : null;

        // Gérer la date de publication
        if (isset($data['published_at']) && !empty($data['published_at'])) {
            $published_at = date('Y-m-d H:i:s', strtotime($data['published_at']));
        } elseif ($status === 'published') {
            $published_at = date('Y-m-d H:i:s');
        } else {
            $published_at = null;
        }

        // Vérifier si c'est une création ou une mise à jour
        if (isset($data['id']) && !empty($data['id'])) {
            // MISE À JOUR
            $sql = "UPDATE articles SET 
                    title = :title,
                    slug = :slug,
                    excerpt = :excerpt,
                    content = :content,
                    author_id = :author_id,
                    category = :category,
                    tags = :tags,
                    featured_image = :featured_image,
                    status = :status,
                    meta_title = :meta_title,
                    meta_description = :meta_description,
                    published_at = :published_at,
                    updated_at = NOW()
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title' => $title,
                ':slug' => $slug,
                ':excerpt' => $excerpt,
                ':content' => $content,
                ':author_id' => $author_id,
                ':category' => $category,
                ':tags' => $tags_json,
                ':featured_image' => $featured_image_json,
                ':status' => $status,
                ':meta_title' => $meta_title,
                ':meta_description' => $meta_description,
                ':published_at' => $published_at,
                ':id' => $data['id']
            ]);

            // Gérer les images supplémentaires dans la table post_images
            managePostImages($data['id'], $data);

            return ['success' => true, 'message' => 'Article mis à jour avec succès', 'id' => $data['id']];

        } else {
            // CRÉATION
            $sql = "INSERT INTO articles (
                    title, slug, excerpt, content, author_id, category,
                    tags, featured_image, status, meta_title, meta_description,
                    published_at, created_at
                ) VALUES (
                    :title, :slug, :excerpt, :content, :author_id, :category,
                    :tags, :featured_image, :status, :meta_title, :meta_description,
                    :published_at, NOW()
                )";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title' => $title,
                ':slug' => $slug,
                ':excerpt' => $excerpt,
                ':content' => $content,
                ':author_id' => $author_id,
                ':category' => $category,
                ':tags' => $tags_json,
                ':featured_image' => $featured_image_json,
                ':status' => $status,
                ':meta_title' => $meta_title,
                ':meta_description' => $meta_description,
                ':published_at' => $published_at
            ]);

            $id = $pdo->lastInsertId();

            // Gérer les images supplémentaires dans la table post_images
            managePostImages($id, $data);

            return ['success' => true, 'message' => 'Article créé avec succès', 'id' => $id];
        }

    } catch (Exception $e) {
        error_log("Erreur saveArticle: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()];
    }
}

/**
 * Gérer les images supplémentaires dans la table post_images
 */
function managePostImages($article_id, $data)
{
    $pdo = getConnexion();

    try {
        // Supprimer les anciennes images si nécessaire
        if (isset($data['delete_images']) && is_array($data['delete_images'])) {
            foreach ($data['delete_images'] as $image_id) {
                // Récupérer le nom du fichier avant suppression
                $stmt = $pdo->prepare("SELECT IMAGES_URL FROM post_images WHERE id = ?");
                $stmt->execute([$image_id]);
                $image = $stmt->fetch();

                if ($image && file_exists(UPLOAD_PATH . 'articles/' . $image['IMAGES_URL'])) {
                    unlink(UPLOAD_PATH . 'articles/' . $image['IMAGES_URL']);
                }

                // Supprimer de la base
                $stmt = $pdo->prepare("DELETE FROM post_images WHERE id = ?");
                $stmt->execute([$image_id]);
            }
        }

        // Ajouter de nouvelles images
        if (isset($data['new_images']) && is_array($data['new_images'])) {
            $display_order = 1;

            // Trouver l'ordre maximum actuel
            $stmt = $pdo->prepare("SELECT MAX(display_order) as max_order FROM post_images WHERE article_id = ?");
            $stmt->execute([$article_id]);
            $result = $stmt->fetch();
            $display_order = $result['max_order'] ? $result['max_order'] + 1 : 1;

            foreach ($data['new_images'] as $image_data) {
                $stmt = $pdo->prepare("INSERT INTO post_images (article_id, IMAGES_URL, display_order) VALUES (?, ?, ?)");
                $stmt->execute([$article_id, $image_data, $display_order]);
                $display_order++;
            }
        }

        // Mettre à jour l'ordre des images
        if (isset($data['image_order']) && is_array($data['image_order'])) {
            foreach ($data['image_order'] as $order => $image_id) {
                $stmt = $pdo->prepare("UPDATE post_images SET display_order = ? WHERE id = ?");
                $stmt->execute([$order + 1, $image_id]);
            }
        }

        // Définir la première image
        if (isset($data['first_image']) && !empty($data['first_image'])) {
            // Réinitialiser toutes les images
            $stmt = $pdo->prepare("UPDATE post_images SET is_first = 0 WHERE article_id = ?");
            $stmt->execute([$article_id]);

            // Définir la première image
            $stmt = $pdo->prepare("UPDATE post_images SET is_first = 1 WHERE id = ? AND article_id = ?");
            $stmt->execute([$data['first_image'], $article_id]);
        }

    } catch (Exception $e) {
        error_log("Erreur managePostImages: " . $e->getMessage());
    }
}

/**
 * Valider les données d'article
 */
function validateArticleData($data, $article_id = null)
{
    $errors = [];

    // Validation du titre
    if (empty($data['title'])) {
        $errors['title'] = 'Le titre est requis';
    } elseif (strlen($data['title']) < 5) {
        $errors['title'] = 'Le titre doit contenir au moins 5 caractères';
    }

    // Validation du slug
    if (empty($data['slug'])) {
        $errors['slug'] = 'Le slug est requis';
    } elseif (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $data['slug'])) {
        $errors['slug'] = 'Le slug ne doit contenir que des lettres minuscules, des chiffres et des tirets';
    } else {
        // Vérifier si le slug existe déjà (sauf pour l'article courant)
        $pdo = getConnexion();
        $sql = "SELECT id FROM articles WHERE slug = ?";
        $params = [$data['slug']];

        if ($article_id) {
            $sql .= " AND id != ?";
            $params[] = $article_id;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->fetch()) {
            $errors['slug'] = 'Ce slug est déjà utilisé';
        }
    }

    // Validation de l'extrait
    if (empty($data['excerpt'])) {
        $errors['excerpt'] = 'L\'extrait est requis';
    } elseif (strlen($data['excerpt']) < 50) {
        $errors['excerpt'] = 'L\'extrait doit contenir au moins 50 caractères';
    }

    // Validation du contenu
    if (empty($data['content'])) {
        $errors['content'] = 'Le contenu est requis';
    } elseif (strlen(strip_tags($data['content'])) < 100) {
        $errors['content'] = 'Le contenu doit contenir au moins 100 caractères';
    }

    // Validation de l'auteur
    if (empty($data['author_id'])) {
        $errors['author_id'] = 'L\'auteur est requis';
    }

    // Validation de la catégorie
    $allowed_categories = ['tech', 'business', 'tutorial', 'news'];
    if (empty($data['category']) || !in_array($data['category'], $allowed_categories)) {
        $errors['category'] = 'La catégorie est invalide';
    }

    // Validation du statut
    $allowed_statuses = ['draft', 'published', 'archived'];
    if (empty($data['status']) || !in_array($data['status'], $allowed_statuses)) {
        $errors['status'] = 'Le statut est invalide';
    }

    return $errors;
}

/**
 * Upload une image d'article
 */
function uploadArticleImage($file, $type = 'gallery')
{
    $upload_dir = UPLOAD_PATH . 'articles/';

    // Créer le dossier s'il n'existe pas
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Vérifier les erreurs d'upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erreur lors de l\'upload de l\'image');
    }

    // Vérifier le type de fichier
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = mime_content_type($file['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
        throw new Exception('Type de fichier non autorisé. Formats acceptés: JPG, PNG, GIF, WebP');
    }

    // Vérifier la taille (max 3MB)
    $max_size = 3 * 1024 * 1024; // 3MB
    if ($file['size'] > $max_size) {
        throw new Exception('L\'image est trop grande. Taille maximale: 3MB');
    }

    // Générer un nom de fichier unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $prefix = ($type === 'featured') ? 'featured_' : 'gallery_';
    $filename = $prefix . uniqid() . '_' . time() . '.' . $extension;
    $destination = $upload_dir . $filename;

    // Déplacer le fichier uploadé
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Erreur lors du déplacement du fichier');
    }

    // Redimensionner l'image selon le type
    if ($type === 'featured') {
        resizeImage($destination, 1200, 630); // Format social media
    } else {
        resizeImage($destination, 800, 600); // Format galerie
    }

    return $filename;
}

/**
 * Redimensionner une image
 */
function resizeImage($file_path, $max_width, $max_height)
{
    if (!function_exists('gd_info')) {
        return false;
    }

    $image_info = getimagesize($file_path);
    if (!$image_info) {
        return false;
    }

    list($width, $height, $type) = $image_info;

    $ratio = $width / $height;

    if ($width > $max_width || $height > $max_height) {
        if ($max_width / $max_height > $ratio) {
            $new_width = $max_height * $ratio;
            $new_height = $max_height;
        } else {
            $new_width = $max_width;
            $new_height = $max_width / $ratio;
        }
    } else {
        return true;
    }

    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($file_path);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($file_path);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($file_path);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($file_path);
            break;
        default:
            return false;
    }

    $destination = imagecreatetruecolor($new_width, $new_height);

    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefilledrectangle($destination, 0, 0, $new_width, $new_height, $transparent);
    }

    imagecopyresampled($destination, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($destination, $file_path, 85);
            break;
        case IMAGETYPE_PNG:
            imagepng($destination, $file_path, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($destination, $file_path);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($destination, $file_path, 85);
            break;
    }

    imagedestroy($source);
    imagedestroy($destination);

    return true;
}

/**
 * Récupérer un article par son ID
 */
function getArticleById($id)
{
    $pdo = getConnexion();

    try {
        $stmt = $pdo->prepare("
            SELECT a.*, 
                   u.first_name as author_first_name,
                   u.last_name as author_last_name
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        $article = $stmt->fetch();

        if ($article) {
            $article['tags'] = json_decode($article['tags'], true) ?? [];
            $article['featured_image_data'] = json_decode($article['featured_image'], true) ?? [];

            // Récupérer les images supplémentaires
            $stmt2 = $pdo->prepare("SELECT * FROM post_images WHERE article_id = ? ORDER BY display_order ASC");
            $stmt2->execute([$id]);
            $article['post_images'] = $stmt2->fetchAll();
        }

        return $article;
    } catch (Exception $e) {
        error_log("Erreur getArticleById: " . $e->getMessage());
        return null;
    }
}

/**
 * Récupérer un article par son slug
 */
function getArticleBySlug($slug)
{
    $pdo = getConnexion();

    try {
        $stmt = $pdo->prepare("
            SELECT a.*, 
                   u.first_name as author_first_name,
                   u.last_name as author_last_name
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
            WHERE a.slug = ? AND a.status = 'published'
        ");
        $stmt->execute([$slug]);
        $article = $stmt->fetch();

        if ($article) {
            $article['tags'] = json_decode($article['tags'], true) ?? [];
            $article['featured_image_data'] = json_decode($article['featured_image'], true) ?? [];

            // Récupérer les images supplémentaires
            $stmt2 = $pdo->prepare("SELECT * FROM post_images WHERE article_id = ? ORDER BY display_order ASC");
            $stmt2->execute([$article['id']]);
            $article['post_images'] = $stmt2->fetchAll();

            // Incrémenter le compteur de vues
            incrementArticleViews($article['id']);
        }

        return $article;
    } catch (Exception $e) {
        error_log("Erreur getArticleBySlug: " . $e->getMessage());
        return null;
    }
}

/**
 * Récupérer tous les articles avec pagination
 */
function getAllArticles($page = 1, $per_page = 10, $filters = [])
{
    $pdo = getConnexion();

    try {
        $conditions = [];
        $params = [];

        // Filtres
        if (!empty($filters['category'])) {
            $conditions[] = "a.category = :category";
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = "a.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['author_id'])) {
            $conditions[] = "a.author_id = :author_id";
            $params[':author_id'] = $filters['author_id'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(a.title LIKE :search OR a.excerpt LIKE :search2 OR a.content LIKE :search3)";
            $search = '%' . $filters['search'] . '%';
            $params[':search'] = $search;
            $params[':search2'] = $search;
            $params[':search3'] = $search;
        }

        $where_sql = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $offset = ($page - 1) * $per_page;

        // COUNT
        $count_sql = "SELECT COUNT(*) as total FROM articles a $where_sql";
        $stmt = $pdo->prepare($count_sql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        $total_pages = ceil($total / $per_page);

        // DATA
        $sql = "
            SELECT a.*, 
                   u.first_name as author_first_name,
                   u.last_name as author_last_name
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
            $where_sql
            ORDER BY 
                CASE 
                    WHEN a.status = 'published' THEN 1
                    WHEN a.status = 'draft' THEN 2
                    ELSE 3
                END,
                a.published_at DESC,
                a.created_at DESC
            LIMIT :limit OFFSET :offset
        ";

        // Ajoute limit et offset
        $params[':limit'] = (int) $per_page;
        $params[':offset'] = (int) $offset;

        $stmt = $pdo->prepare($sql);

        // Bind sécurisé
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $stmt->execute();
        $articles = $stmt->fetchAll();

        foreach ($articles as &$article) {
            $article['tags'] = json_decode($article['tags'], true) ?? [];
            $article['featured_image_data'] = json_decode($article['featured_image'], true) ?? [];
        }

        return [
            'articles' => $articles,
            'total' => $total,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'per_page' => $per_page
        ];

    } catch (Exception $e) {
        error_log("Erreur getAllArticles: " . $e->getMessage());
        return ['articles' => [], 'total' => 0, 'total_pages' => 0];
    }
}



/**
 * Supprimer un article
 */
function deleteArticle($id)
{
    $pdo = getConnexion();

    try {
        // Récupérer l'article pour supprimer ses images
        $article = getArticleById($id);

        if (!$article) {
            return ['success' => false, 'message' => 'Article non trouvé'];
        }

        // Supprimer l'image à la une
        if (isset($article['featured_image_data']['url']) && file_exists(UPLOAD_PATH . 'articles/' . $article['featured_image_data']['url'])) {
            unlink(UPLOAD_PATH . 'articles/' . $article['featured_image_data']['url']);
        }

        // Supprimer les images supplémentaires
        foreach ($article['post_images'] as $post_image) {
            if (file_exists(UPLOAD_PATH . 'articles/' . $post_image['IMAGES_URL'])) {
                unlink(UPLOAD_PATH . 'articles/' . $post_image['IMAGES_URL']);
            }
        }

        // Supprimer les commentaires associés
        $stmt = $pdo->prepare("DELETE FROM commentaires WHERE article_id = ?");
        $stmt->execute([$id]);

        // Supprimer les images de l'article
        $stmt = $pdo->prepare("DELETE FROM post_images WHERE article_id = ?");
        $stmt->execute([$id]);

        // Supprimer l'article de la base de données
        $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->execute([$id]);

        return ['success' => true, 'message' => 'Article supprimé avec succès'];

    } catch (Exception $e) {
        error_log("Erreur deleteArticle: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la suppression: ' . $e->getMessage()];
    }
}

/**
 * Publier/dépublier un article
 */
function toggleArticleStatus($id, $status)
{
    $pdo = getConnexion();

    try {
        $allowed_statuses = ['draft', 'published', 'archived'];
        if (!in_array($status, $allowed_statuses)) {
            return ['success' => false, 'message' => 'Statut invalide'];
        }

        $published_at = null;
        if ($status === 'published') {
            $published_at = date('Y-m-d H:i:s');
        }

        $sql = "UPDATE articles SET status = ?, published_at = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $published_at, $id]);

        return ['success' => true, 'message' => 'Statut mis à jour'];
    } catch (Exception $e) {
        error_log("Erreur toggleArticleStatus: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
    }
}

/**
 * Incrémenter le compteur de vues d'un article
 */
function incrementArticleViews($id)
{
    $pdo = getConnexion();

    try {
        $stmt = $pdo->prepare("UPDATE articles SET views = COALESCE(views, 0) + 1 WHERE id = ?");
        $stmt->execute([$id]);

        return true;
    } catch (Exception $e) {
        error_log("Erreur incrementArticleViews: " . $e->getMessage());
        return false;
    }
}

/**
 * Incrémenter le compteur de likes d'un article
 */
function incrementArticleLikes($id)
{
    $pdo = getConnexion();

    try {
        $stmt = $pdo->prepare("UPDATE articles SET likes = COALESCE(likes, 0) + 1 WHERE id = ?");
        $stmt->execute([$id]);

        return true;
    } catch (Exception $e) {
        error_log("Erreur incrementArticleLikes: " . $e->getMessage());
        return false;
    }
}

/**
 * Récupérer les statistiques des articles
 */
function getArticlesStats()
{
    $pdo = getConnexion();

    try {
        $stats = [];

        // Total des articles
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM articles");
        $stats['total'] = $stmt->fetch()['total'];

        // Articles publiés
        $stmt = $pdo->query("SELECT COUNT(*) as published FROM articles WHERE status = 'published'");
        $stats['published'] = $stmt->fetch()['published'];

        // Articles en brouillon
        $stmt = $pdo->query("SELECT COUNT(*) as draft FROM articles WHERE status = 'draft'");
        $stats['draft'] = $stmt->fetch()['draft'];

        // Par catégorie
        $stmt = $pdo->query("SELECT category, COUNT(*) as count FROM articles GROUP BY category");
        $stats['by_category'] = $stmt->fetchAll();

        // Total des vues
        $stmt = $pdo->query("SELECT SUM(COALESCE(views, 0)) as total_views FROM articles");
        $stats['total_views'] = $stmt->fetch()['total_views'] ?? 0;

        // Total des likes
        $stmt = $pdo->query("SELECT SUM(COALESCE(likes, 0)) as total_likes FROM articles");
        $stats['total_likes'] = $stmt->fetch()['total_likes'] ?? 0;

        // Articles les plus vus
        $stmt = $pdo->query("SELECT id, title, views FROM articles ORDER BY COALESCE(views, 0) DESC LIMIT 5");
        $stats['most_viewed'] = $stmt->fetchAll();

        // Articles les plus aimés
        $stmt = $pdo->query("SELECT id, title, likes FROM articles ORDER BY COALESCE(likes, 0) DESC LIMIT 5");
        $stats['most_liked'] = $stmt->fetchAll();

        // Articles récents
        $stmt = $pdo->query("
            SELECT a.id, a.title, a.created_at, 
                   u.first_name as author_first_name,
                   u.last_name as author_last_name
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
            ORDER BY a.created_at DESC
            LIMIT 5
        ");
        $stats['recent'] = $stmt->fetchAll();

        return $stats;

    } catch (Exception $e) {
        error_log("Erreur getArticlesStats: " . $e->getMessage());
        return [];
    }
}
?>