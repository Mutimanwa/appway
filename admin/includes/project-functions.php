<?php
/**
 * Fonctions pour la gestion des projets
 */

/**
 * Créer ou mettre à jour un projet
 */
function saveProject($data, $files = [])
{
    $pdo = getConnexion();

    try {
        // Validation des données
        $errors = validateProjectData($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Préparer les données
        $title = nettoyer_input($data['title']);
        $description = nettoyer_input($data['description']);
        $category = nettoyer_input($data['category']);
        $client_id = (int) $data['client_id'];
        $status = nettoyer_input($data['status']);
        $priority = isset($data['priority']) ? nettoyer_input($data['priority']) : 'medium';
        $progress = isset($data['progress']) ? (int) $data['progress'] : 0;
        $featured = isset($data['featured']) ? 1 : 0;
        $start_date = !empty($data['start_date']) ? $data['start_date'] : null;
        $end_date = !empty($data['end_date']) ? $data['end_date'] : null;
        $delivery_date = !empty($data['delivery_date']) ? $data['delivery_date'] : null;
        $estimated_budget = !empty($data['estimated_budget']) ? (float) $data['estimated_budget'] : null;
        $actual_budget = !empty($data['actual_budget']) ? (float) $data['actual_budget'] : null;
        $currency = isset($data['currency']) ? nettoyer_input($data['currency']) : 'EUR';
        $demo_url = isset($data['demo_url']) ? nettoyer_input($data['demo_url']) : null;
        $github_url = isset($data['github_url']) ? nettoyer_input($data['github_url']) : null;
        $specifications = isset($data['specifications']) ? $data['specifications'] : null;
        $internal_notes = isset($data['internal_notes']) ? $data['internal_notes'] : null;

        // Gérer les technologies
        $technologies = [];
        if (isset($data['technologies']) && is_array($data['technologies'])) {
            foreach ($data['technologies'] as $tech) {
                $clean_tech = nettoyer_input($tech);
                if (!empty($clean_tech)) {
                    $technologies[] = $clean_tech;
                }
            }
        }
        $technologies_json = json_encode($technologies, JSON_UNESCAPED_UNICODE);

        // Gérer les images
        $images = [];
        if (isset($data['current_images']) && is_array($data['current_images'])) {
            $images = $data['current_images'];
        }

        // Ajouter les nouvelles images uploadées
        if (isset($data['project_images']) && is_array($data['project_images'])) {
            foreach ($data['project_images'] as $image) {
                if (!empty($image)) {
                    $images[] = $image;
                }
            }
        }

        // Supprimer les images marquées pour suppression
        if (isset($data['delete_images']) && is_array($data['delete_images'])) {
            foreach ($data['delete_images'] as $image_to_delete) {
                $key = array_search($image_to_delete, $images);
                if ($key !== false) {
                    // Supprimer physiquement le fichier
                    if (file_exists(UPLOAD_PATH . 'projects/' . $image_to_delete)) {
                        unlink(UPLOAD_PATH . 'projects/' . $image_to_delete);
                    }
                    unset($images[$key]);
                }
            }
            $images = array_values($images); // Réindexer le tableau
        }

        $images_json = json_encode($images, JSON_UNESCAPED_UNICODE);

        // Vérifier si c'est une création ou une mise à jour
        if (isset($data['id']) && !empty($data['id'])) {
            // MISE À JOUR
            $sql = "UPDATE projects SET 
                    title = :title,
                    description = :description,
                    category = :category,
                    client_id = :client_id,
                    status = :status,
                    priority = :priority,
                    progress = :progress,
                    technologies = :technologies,
                    images = :images,
                    demo_url = :demo_url,
                    github_url = :github_url,
                    featured = :featured,
                    start_date = :start_date,
                    end_date = :end_date,
                    delivery_date = :delivery_date,
                    estimated_budget = :estimated_budget,
                    actual_budget = :actual_budget,
                    currency = :currency,
                    specifications = :specifications,
                    internal_notes = :internal_notes,
                    updated_at = NOW()
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':category' => $category,
                ':client_id' => $client_id,
                ':status' => $status,
                ':priority' => $priority,
                ':progress' => $progress,
                ':technologies' => $technologies_json,
                ':images' => $images_json,
                ':demo_url' => $demo_url,
                ':github_url' => $github_url,
                ':featured' => $featured,
                ':start_date' => $start_date,
                ':end_date' => $end_date,
                ':delivery_date' => $delivery_date,
                ':estimated_budget' => $estimated_budget,
                ':actual_budget' => $actual_budget,
                ':currency' => $currency,
                ':specifications' => $specifications,
                ':internal_notes' => $internal_notes,
                ':id' => $data['id']
            ]);

            return ['success' => true, 'message' => 'Projet mis à jour avec succès', 'id' => $data['id']];

        } else {
            // CRÉATION
            $sql = "INSERT INTO projects (
                    title, description, category, client_id, status, priority,
                    progress, technologies, images, demo_url, github_url,
                    featured, start_date, end_date, delivery_date,
                    estimated_budget, actual_budget, currency, specifications,
                    internal_notes, created_at
                ) VALUES (
                    :title, :description, :category, :client_id, :status, :priority,
                    :progress, :technologies, :images, :demo_url, :github_url,
                    :featured, :start_date, :end_date, :delivery_date,
                    :estimated_budget, :actual_budget, :currency, :specifications,
                    :internal_notes, NOW()
                )";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':category' => $category,
                ':client_id' => $client_id,
                ':status' => $status,
                ':priority' => $priority,
                ':progress' => $progress,
                ':technologies' => $technologies_json,
                ':images' => $images_json,
                ':demo_url' => $demo_url,
                ':github_url' => $github_url,
                ':featured' => $featured,
                ':start_date' => $start_date,
                ':end_date' => $end_date,
                ':delivery_date' => $delivery_date,
                ':estimated_budget' => $estimated_budget,
                ':actual_budget' => $actual_budget,
                ':currency' => $currency,
                ':specifications' => $specifications,
                ':internal_notes' => $internal_notes
            ]);

            $id = $pdo->lastInsertId();
            return ['success' => true, 'message' => 'Projet créé avec succès', 'id' => $id];
        }

    } catch (Exception $e) {
        error_log("Erreur saveProject: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()];
    }
}

/**
 * Valider les données du projet
 */
function validateProjectData($data)
{
    $errors = [];

    // Validation du titre
    if (empty($data['title'])) {
        $errors['title'] = 'Le titre est requis';
    } elseif (strlen($data['title']) < 5) {
        $errors['title'] = 'Le titre doit contenir au moins 5 caractères';
    }

    // Validation de la description
    if (empty($data['description'])) {
        $errors['description'] = 'La description est requise';
    } elseif (strlen($data['description']) < 20) {
        $errors['description'] = 'La description doit contenir au moins 20 caractères';
    }

    // Validation de la catégorie
    $allowed_categories = ['web', 'mobile', 'desktop', 'cloud', 'ia', 'database'];
    if (empty($data['category']) || !in_array($data['category'], $allowed_categories)) {
        $errors['category'] = 'La catégorie est invalide';
    }

    // Validation du client
    if (empty($data['client_id'])) {
        $errors['client_id'] = 'Le client est requis';
    } else {
        // Vérifier que le client existe
        $pdo = getConnexion();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'client'");
        $stmt->execute([$data['client_id']]);

        if (!$stmt->fetch()) {
            $errors['client_id'] = 'Client invalide';
        }
    }

    // Validation du statut
    $allowed_statuses = ['draft', 'in_progress', 'completed', 'delivered'];
    if (empty($data['status']) || !in_array($data['status'], $allowed_statuses)) {
        $errors['status'] = 'Le statut est invalide';
    }

    // Validation de la progression
    if (isset($data['progress'])) {
        $progress = (int) $data['progress'];
        if ($progress < 0 || $progress > 100) {
            $errors['progress'] = 'La progression doit être entre 0 et 100';
        }
    }

    // Validation des URLs
    if (!empty($data['demo_url']) && !filter_var($data['demo_url'], FILTER_VALIDATE_URL)) {
        $errors['demo_url'] = 'L\'URL de démo n\'est pas valide';
    }

    if (!empty($data['github_url']) && !filter_var($data['github_url'], FILTER_VALIDATE_URL)) {
        $errors['github_url'] = 'L\'URL GitHub n\'est pas valide';
    }

    // Validation des dates
    if (!empty($data['start_date']) && !empty($data['end_date'])) {
        $start = strtotime($data['start_date']);
        $end = strtotime($data['end_date']);

        if ($end < $start) {
            $errors['end_date'] = 'La date de fin ne peut pas être antérieure à la date de début';
        }
    }

    if (!empty($data['end_date']) && !empty($data['delivery_date'])) {
        $end = strtotime($data['end_date']);
        $delivery = strtotime($data['delivery_date']);

        if ($delivery < $end) {
            $errors['delivery_date'] = 'La date de livraison ne peut pas être antérieure à la date de fin';
        }
    }

    // Validation du budget
    if (!empty($data['estimated_budget']) && $data['estimated_budget'] < 0) {
        $errors['estimated_budget'] = 'Le budget estimé ne peut pas être négatif';
    }

    if (!empty($data['actual_budget']) && $data['actual_budget'] < 0) {
        $errors['actual_budget'] = 'Le budget réel ne peut pas être négatif';
    }

    return $errors;
}

/**
 * Upload une image de projet
 */
function uploadProjectImage($file)
{
    $upload_dir = UPLOAD_PATH . 'projects/';

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

    // Vérifier la taille (max 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        throw new Exception('L\'image est trop grande. Taille maximale: 5MB');
    }

    // Générer un nom de fichier unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'project_' . uniqid() . '_' . time() . '.' . $extension;
    $destination = $upload_dir . $filename;

    // Déplacer le fichier uploadé
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Erreur lors du déplacement du fichier');
    }

    // Redimensionner l'image si nécessaire
    resizeImage($destination, 1200, 800);

    return $filename;
}

/**
 * Récupérer un projet par son ID
 */
function getProjectById($id)
{
    $pdo = getConnexion();

    try {
        $stmt = $pdo->prepare("
            SELECT p.*, 
                   u.first_name as client_first_name,
                   u.last_name as client_last_name,
                   u.email as client_email
            FROM projects p
            LEFT JOIN users u ON p.client_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $project = $stmt->fetch();

        if ($project) {
            $project['technologies'] = json_decode($project['technologies'], true) ?? [];
            $project['images'] = json_decode($project['images'], true) ?? [];
        }

        return $project;
    } catch (Exception $e) {
        error_log("Erreur getProjectById: " . $e->getMessage());
        return null;
    }
}

/**
 * Récupérer tous les projets avec pagination
 */
function getAllProjects($page = 1, $per_page = 10, $filters = [])
{
    $pdo = getConnexion();

    try {

        $conditions = [];
        $params = [];

        // Filtres dynamiques
        if (!empty($filters['category'])) {
            $conditions[] = "p.category = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = "p.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['client_id'])) {
            $conditions[] = "p.client_id = ?";
            $params[] = $filters['client_id'];
        }

        if (!empty($filters['priority'])) {
            $conditions[] = "p.priority = ?";
            $params[] = $filters['priority'];
        }

        if (isset($filters['featured']) && $filters['featured'] !== '') {
            $conditions[] = "p.featured = ?";
            $params[] = $filters['featured'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(p.title LIKE ? OR p.description LIKE ?)";
            $search_term = '%' . $filters['search'] . '%';
            $params[] = $search_term;
            $params[] = $search_term;
        }

        // WHERE dynamique
        $where_sql = '';
        if ($conditions) {
            $where_sql = 'WHERE ' . implode(' AND ', $conditions);
        }

        // Pagination
        $offset = ($page - 1) * $per_page;

        // === TOTAL ===
        $count_sql = "SELECT COUNT(*) AS total FROM projects p $where_sql";
        $stmt = $pdo->prepare($count_sql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $total_pages = ceil($total / $per_page);

        // === DATA ===
        $sql = "
            SELECT p.*, 
                   u.first_name AS client_first_name,
                   u.last_name AS client_last_name
            FROM projects p
            LEFT JOIN users u ON p.client_id = u.id
            $where_sql
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?
        ";

        // Paramètres pour LIMIT/OFFSET → doivent être ajoutés séparément
        $queryParams = $params;
        $queryParams[] = (int) $per_page;
        $queryParams[] = (int) $offset;

        $stmt = $pdo->prepare($sql);

        // Binding strict (évite les erreurs SQLSTATE[HY093])
        $i = 1;
        foreach ($queryParams as $value) {
            $stmt->bindValue($i, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            $i++;
        }

        $stmt->execute();
        $projects = $stmt->fetchAll();

        // JSON decode
        foreach ($projects as &$project) {
            $project['technologies'] = json_decode($project['technologies'], true) ?? [];
            $project['images'] = json_decode($project['images'], true) ?? [];
        }

        return [
            'projects' => $projects,
            'total' => $total,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'per_page' => $per_page
        ];

    } catch (Exception $e) {
        error_log("Erreur getAllProjects: " . $e->getMessage());
        return ['projects' => [], 'total' => 0, 'total_pages' => 0];
    }
}


/**
 * Supprimer un projet
 */
function deleteProject($id)
{
    $pdo = getConnexion();

    try {
        // Récupérer le projet pour supprimer ses images
        $project = getProjectById($id);

        if (!$project) {
            return ['success' => false, 'message' => 'Projet non trouvé'];
        }

        // Supprimer les images associées
        if (!empty($project['images'])) {
            $images = json_decode($project['images'], true);
            foreach ($images as $image) {
                if (file_exists(UPLOAD_PATH . 'projects/' . $image)) {
                    unlink(UPLOAD_PATH . 'projects/' . $image);
                }
            }
        }

        // Supprimer le projet de la base de données
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$id]);

        return ['success' => true, 'message' => 'Projet supprimé avec succès'];

    } catch (Exception $e) {
        error_log("Erreur deleteProject: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la suppression: ' . $e->getMessage()];
    }
}

/**
 * Mettre à jour le statut d'un projet
 */
function updateProjectStatus($id, $status)
{
    $pdo = getConnexion();

    try {
        // Vérifier que le statut est valide
        $allowed_statuses = ['draft', 'in_progress', 'completed', 'delivered'];
        if (!in_array($status, $allowed_statuses)) {
            return ['success' => false, 'message' => 'Statut invalide'];
        }

        $stmt = $pdo->prepare("UPDATE projects SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $id]);

        // Si le projet est livré, mettre à jour la date de livraison
        if ($status === 'delivered') {
            $stmt = $pdo->prepare("UPDATE projects SET delivery_date = CURDATE() WHERE id = ? AND delivery_date IS NULL");
            $stmt->execute([$id]);
        }

        return ['success' => true, 'message' => 'Statut mis à jour'];
    } catch (Exception $e) {
        error_log("Erreur updateProjectStatus: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
    }
}

/**
 * Mettre à jour la progression d'un projet
 */
function updateProjectProgress($id, $progress)
{
    $pdo = getConnexion();

    try {
        $progress = (int) $progress;
        if ($progress < 0 || $progress > 100) {
            return ['success' => false, 'message' => 'La progression doit être entre 0 et 100'];
        }

        $stmt = $pdo->prepare("UPDATE projects SET progress = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$progress, $id]);

        // Si la progression atteint 100%, marquer comme terminé
        if ($progress == 100) {
            $stmt = $pdo->prepare("UPDATE projects SET status = 'completed', end_date = CURDATE() WHERE id = ? AND status != 'completed'");
            $stmt->execute([$id]);
        }

        return ['success' => true, 'message' => 'Progression mise à jour'];
    } catch (Exception $e) {
        error_log("Erreur updateProjectProgress: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
    }
}

/**
 * Récupérer les statistiques des projets
 */
function getProjectsStats()
{
    $pdo = getConnexion();

    try {
        $stats = [];

        // Total des projets
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM projects");
        $stats['total'] = $stmt->fetch()['total'] ?? 0;

        // Par statut
        $stmt = $pdo->query("
            SELECT status, COUNT(*) as count 
            FROM projects 
            GROUP BY status
            ORDER BY 
                CASE status
                    WHEN 'in_progress' THEN 1
                    WHEN 'completed' THEN 2
                    WHEN 'delivered' THEN 3
                    WHEN 'draft' THEN 4
                    ELSE 5
                END
        ");
        $stats['by_status'] = $stmt->fetchAll() ?: [];

        // Par catégorie
        $stmt = $pdo->query("SELECT category, COUNT(*) as count FROM projects GROUP BY category");
        $stats['by_category'] = $stmt->fetchAll() ?: [];

        // Projets en retard
        $stmt = $pdo->query("
            SELECT COUNT(*) as late 
            FROM projects 
            WHERE end_date < CURDATE() 
            AND status NOT IN ('completed', 'delivered')
            AND end_date IS NOT NULL
        ");
        $stats['late'] = $stmt->fetch()['late'] ?? 0;

        // Projets en cours
        $stmt = $pdo->query("SELECT COUNT(*) as in_progress FROM projects WHERE status = 'in_progress'");
        $stats['in_progress'] = $stmt->fetch()['in_progress'] ?? 0;

        // Budget total estimé vs réel
        $stmt = $pdo->query("
            SELECT 
                SUM(COALESCE(estimated_budget, 0)) as total_estimated,
                SUM(COALESCE(actual_budget, 0)) as total_actual
            FROM projects
            WHERE status NOT IN ('draft')
        ");
        $budget = $stmt->fetch();
        $stats['total_estimated'] = $budget['total_estimated'] ?? 0;
        $stats['total_actual'] = $budget['total_actual'] ?? 0;

        // Projets terminés ce mois-ci
        $stmt = $pdo->query("
            SELECT COUNT(*) as completed_this_month 
            FROM projects 
            WHERE status IN ('completed', 'delivered')
            AND MONTH(updated_at) = MONTH(CURRENT_DATE())
            AND YEAR(updated_at) = YEAR(CURRENT_DATE())
        ");
        $stats['completed_this_month'] = $stmt->fetch()['completed_this_month'] ?? 0;

        // Projets en vedette
        $stmt = $pdo->query("SELECT COUNT(*) as featured FROM projects WHERE featured = 1");
        $stats['featured'] = $stmt->fetch()['featured'] ?? 0;

        return $stats;

    } catch (Exception $e) {
        error_log("Erreur getProjectsStats: " . $e->getMessage());
        return [
            'total' => 0,
            'by_status' => [],
            'by_category' => [],
            'late' => 0,
            'in_progress' => 0,
            'total_estimated' => 0,
            'total_actual' => 0,
            'completed_this_month' => 0,
            'featured' => 0,
        ];
    }
}


/**
 * Récupérer les projets récents
 */
function getRecentProjects($limit = 5)
{
    $pdo = getConnexion();

    try {
        $sql = "
            SELECT p.*, 
                   u.first_name as client_first_name,
                   u.last_name as client_last_name
            FROM projects p
            LEFT JOIN users u ON p.client_id = u.id
            ORDER BY p.created_at DESC
            LIMIT ?
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit]);
        $projects = $stmt->fetchAll();

        // Décoder les JSON
        foreach ($projects as &$project) {
            $project['technologies'] = json_decode($project['technologies'], true) ?? [];
            $project['images'] = json_decode($project['images'], true) ?? [];
        }

        return $projects;
    } catch (Exception $e) {
        error_log("Erreur getRecentProjects: " . $e->getMessage());
        return [];
    }
}
?>