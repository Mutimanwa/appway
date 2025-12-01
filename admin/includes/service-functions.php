<?php
/**
 * Fonctions pour la gestion des services
 */

/**
 * Créer ou mettre à jour un service
 */
function saveService($data)
{
    $pdo = getConnexion();

    try {
        // Préparer les données
        $name = nettoyer_input($data['name']);
        $description = nettoyer_input($data['description']);
        $category = nettoyer_input($data['category']);
        $price = nettoyer_input($data['price']);
        $duration = isset($data['duration']) ? nettoyer_input($data['duration']) : null;
        $display_order = isset($data['display_order']) ? (int) $data['display_order'] : 0;
        $is_active = isset($data['status']) ? (int) $data['status'] : 1;
        $icon = isset($data['icon']) ? nettoyer_input($data['icon']) : null;

        // Traiter les features (s'ils existent)
        $features = [];
        if (isset($data['features']) && is_array($data['features'])) {
            foreach ($data['features'] as $feature) {
                $trimmed = trim($feature);
                if (!empty($trimmed)) {
                    $features[] = $trimmed;
                }
            }
        }
        $features_json = !empty($features) ? json_encode($features) : null;

        // Vérifier si c'est une création ou une mise à jour
        if (isset($data['id']) && !empty($data['id'])) {
            // MISE À JOUR
            $sql = "UPDATE services SET 
                    name = :name,
                    description = :description,
                    category = :category,
                    price = :price,
                    duration = :duration,
                    icon = :icon,
                    display_order = :display_order,
                    is_active = :is_active,
                    features = :features,
                    updated_at = NOW()
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':category' => $category,
                ':price' => $price,
                ':duration' => $duration,
                ':icon' => $icon,
                ':display_order' => $display_order,
                ':is_active' => $is_active,
                ':features' => $features_json,
                ':id' => $data['id'],
            ]);

            return ['success' => true, 'message' => 'Service mis à jour avec succès', 'id' => $data['id']];

        } else {
            // CRÉATION
            $sql = "INSERT INTO services (
                    name, description, category, price, duration, icon, 
                    display_order, is_active, features, created_at
                ) VALUES (
                    :name, :description, :category, :price, :duration, :icon,
                    :display_order, :is_active, :features, NOW()
                )";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':category' => $category,
                ':price' => $price,
                ':duration' => $duration,
                ':icon' => $icon,
                ':display_order' => $display_order,
                ':is_active' => $is_active,
                ':features' => $features_json,
            ]);

            $id = $pdo->lastInsertId();
            return ['success' => true, 'message' => 'Service créé avec succès', 'id' => $id];
        }

    } catch (Exception $e) {
        error_log("Erreur saveService: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()];
    }
}

/**
 * Récupérer un service par son ID
 */
function getServiceById($id)
{
    $pdo = getConnexion();

    try {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $service = $stmt->fetch();

        if ($service) {
            $service['features'] = json_decode($service['features'], true) ?? [];
        }

        return $service;
    } catch (Exception $e) {
        error_log("Erreur getServiceById: " . $e->getMessage());
        return null;
    }
}

/**
 * Récupérer tous les services avec pagination
 */
function getAllServices($page = 1, $per_page = 10, $filters = [])
{
    $pdo = getConnexion();

    try {
        $conditions = [];
        $params = [];

        // Appliquer les filtres
        if (!empty($filters['category'])) {
            $conditions[] = "category = ?";
            $params[] = $filters['category'];
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $conditions[] = "is_active = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(name LIKE ? OR description LIKE ?)";
            $search_term = '%' . $filters['search'] . '%';
            $params[] = $search_term;
            $params[] = $search_term;
        }

        // Construire la clause WHERE
        $where_sql = '';
        if (!empty($conditions)) {
            $where_sql = 'WHERE ' . implode(' AND ', $conditions);
        }

        // Calculer l'offset
        $offset = ($page - 1) * $per_page;

        // Total
        $count_sql = "SELECT COUNT(*) as total FROM services $where_sql";
        $stmt = $pdo->prepare($count_sql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        $total_pages = ceil($total / $per_page);

        // Requête pour les données
        // LIMIT et OFFSET injectés directement, les filtres restent liés
        $sql = "SELECT * FROM services $where_sql ORDER BY display_order ASC, created_at DESC LIMIT $per_page OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $services = $stmt->fetchAll();

        // Décoder les features JSON
        foreach ($services as &$service) {
            $service['features'] = json_decode($service['features'], true) ?? [];
        }

        return [
            'services' => $services,
            'total' => $total,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'per_page' => $per_page
        ];

    } catch (Exception $e) {
        error_log("Erreur getAllServices: " . $e->getMessage());
        return ['services' => [], 'total' => 0, 'total_pages' => 0];
    }
}


/**
 * Supprimer un service
 */
function deleteService($id)
{
    $pdo = getConnexion();

    try {
        // Vérifier si le service existe
        $service = getServiceById($id);

        if (!$service) {
            return ['success' => false, 'message' => 'Service non trouvé'];
        }

        // Supprimer le service de la base de données
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);

        return ['success' => true, 'message' => 'Service supprimé avec succès'];

    } catch (Exception $e) {
        error_log("Erreur deleteService: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la suppression: ' . $e->getMessage()];
    }
}

/**
 * Activer/désactiver un service
 */
function toggleServiceStatus($id, $status)
{
    $pdo = getConnexion();

    try {
        $stmt = $pdo->prepare("UPDATE services SET is_active = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        return ['success' => true, 'message' => 'Statut mis à jour'];
    } catch (Exception $e) {
        error_log("Erreur toggleServiceStatus: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
    }
}

/**
 * Récupérer les statistiques des services
 */
function getServicesStats()
{
    $pdo = getConnexion();

    try {
        $stats = [];

        // Total des services
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM services");
        $stats['total'] = $stmt->fetch()['total'];

        // Services actifs
        $stmt = $pdo->query("SELECT COUNT(*) as active FROM services WHERE is_active = 1");
        $stats['active'] = $stmt->fetch()['active'];

        // Par catégorie
        $stmt = $pdo->query("SELECT category, COUNT(*) as count FROM services GROUP BY category");
        $stats['by_category'] = $stmt->fetchAll();

        return $stats;

    } catch (Exception $e) {
        error_log("Erreur getServicesStats: " . $e->getMessage());
        return [];
    }
}
?>