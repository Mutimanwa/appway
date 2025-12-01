<?php
/**
 * Fonctions pour la gestion de l'équipe
 */

/**
 * Créer ou mettre à jour un membre d'équipe
 */
function saveTeamMember($data, $files = [])
{
    $pdo = getConnexion();

    try {
        // Validation des données
        $errors = validateTeamMemberData($data, isset($data['id']) ? $data['id'] : null);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Préparer les données
        $first_name = nettoyer_input($data['first_name']);
        $last_name = nettoyer_input($data['last_name']);
        $position = nettoyer_input($data['position']);
        $description = isset($data['description']) ? nettoyer_input($data['description']) : null;
        $email = isset($data['email']) ? nettoyer_input($data['email']) : null;
        $phone = isset($data['phone']) ? nettoyer_input($data['phone']) : null;
        $display_order = isset($data['display_order']) ? (int) $data['display_order'] : 0;
        $is_active = isset($data['is_active']) ? (int) $data['is_active'] : 1;

        // Gérer les liens sociaux
        $social_links = [];
        if (isset($data['social_links']) && is_array($data['social_links'])) {
            foreach ($data['social_links'] as $network => $url) {
                if (!empty($url)) {
                    $social_links[$network] = nettoyer_input($url);
                }
            }
        }
        $social_links_json = json_encode($social_links, JSON_UNESCAPED_UNICODE);

        // Gérer l'avatar
        $avatar = null;
        if (isset($data['current_avatar']) && !empty($data['current_avatar'])) {
            $avatar = $data['current_avatar'];
        }

        // Upload du nouvel avatar
        if (isset($files['avatar']) && $files['avatar']['error'] === UPLOAD_ERR_OK) {
            // Supprimer l'ancien avatar s'il existe
            if ($avatar && file_exists(UPLOAD_PATH . 'team/' . $avatar)) {
                unlink(UPLOAD_PATH . 'team/' . $avatar);
            }

            // Upload du nouvel avatar
            $avatar = uploadTeamAvatar($files['avatar']);
        }

        // Vérifier si c'est une création ou une mise à jour
        if (isset($data['id']) && !empty($data['id'])) {
            // MISE À JOUR
            $sql = "UPDATE team_members SET 
                    first_name = :first_name,
                    last_name = :last_name,
                    position = :position,
                    description = :description,
                    email = :email,
                    phone = :phone,
                    avatar = :avatar,
                    social_links = :social_links,
                    display_order = :display_order,
                    is_active = :is_active,
                    updated_at = NOW()
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':position' => $position,
                ':description' => $description,
                ':email' => $email,
                ':phone' => $phone,
                ':avatar' => $avatar,
                ':social_links' => $social_links_json,
                ':display_order' => $display_order,
                ':is_active' => $is_active,
                ':id' => $data['id']
            ]);

            return ['success' => true, 'message' => 'Membre mis à jour avec succès', 'id' => $data['id']];

        } else {
            // CRÉATION
            $sql = "INSERT INTO team_members (
                    first_name, last_name, position, description, email, phone,
                    avatar, social_links, display_order, is_active, created_at
                ) VALUES (
                    :first_name, :last_name, :position, :description, :email, :phone,
                    :avatar, :social_links, :display_order, :is_active, NOW()
                )";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':position' => $position,
                ':description' => $description,
                ':email' => $email,
                ':phone' => $phone,
                ':avatar' => $avatar,
                ':social_links' => $social_links_json,
                ':display_order' => $display_order,
                ':is_active' => $is_active
            ]);

            $id = $pdo->lastInsertId();
            return ['success' => true, 'message' => 'Membre créé avec succès', 'id' => $id];
        }

    } catch (Exception $e) {
        error_log("Erreur saveTeamMember: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()];
    }
}

/**
 * Valider les données d'un membre d'équipe
 */
function validateTeamMemberData($data, $member_id = null)
{
    $errors = [];

    // Validation du prénom
    if (empty($data['first_name'])) {
        $errors['first_name'] = 'Le prénom est requis';
    }

    // Validation du nom
    if (empty($data['last_name'])) {
        $errors['last_name'] = 'Le nom est requis';
    }

    // Validation du poste
    if (empty($data['position'])) {
        $errors['position'] = 'Le poste est requis';
    }

    // Validation de l'email
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'L\'email n\'est pas valide';
    }

    // Validation du téléphone
    if (!empty($data['phone']) && !preg_match('/^[0-9\s\+\-\(\)]{10,20}$/', $data['phone'])) {
        $errors['phone'] = 'Le format du téléphone est invalide';
    }

    // Validation des URLs sociales
    if (isset($data['social_links']) && is_array($data['social_links'])) {
        foreach ($data['social_links'] as $network => $url) {
            if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) {
                $errors['social_' . $network] = "L'URL $network n'est pas valide";
            }
        }
    }

    return $errors;
}

/**
 * Upload un avatar d'équipe
 */
function uploadTeamAvatar($file)
{
    $upload_dir = UPLOAD_PATH . 'team/';

    // Créer le dossier s'il n'existe pas
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Vérifier les erreurs d'upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erreur lors de l\'upload de l\'avatar');
    }

    // Vérifier le type de fichier
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = mime_content_type($file['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
        throw new Exception('Type de fichier non autorisé. Formats acceptés: JPG, PNG, GIF, WebP');
    }

    // Vérifier la taille (max 2MB)
    $max_size = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $max_size) {
        throw new Exception('L\'avatar est trop grand. Taille maximale: 2MB');
    }

    // Générer un nom de fichier unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'team_' . uniqid() . '_' . time() . '.' . $extension;
    $destination = $upload_dir . $filename;

    // Déplacer le fichier uploadé
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Erreur lors du déplacement du fichier');
    }

    // Redimensionner l'avatar en carré 400x400
    resizeImage($destination, 400, 400);

    return $filename;
}

/**
 * Récupérer un membre d'équipe par son ID
 */
function getTeamMemberById($id)
{
    $pdo = getConnexion();

    try {
        $stmt = $pdo->prepare("SELECT * FROM team_members WHERE id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch();

        if ($member) {
            $member['social_links'] = json_decode($member['social_links'], true) ?? [];
        }

        return $member;
    } catch (Exception $e) {
        error_log("Erreur getTeamMemberById: " . $e->getMessage());
        return null;
    }
}

/**
 * Récupérer tous les membres d'équipe
 */
function getAllTeamMembers($active_only = false)
{
    $pdo = getConnexion();

    try {
        $sql = "SELECT * FROM team_members";
        $params = [];

        if ($active_only) {
            $sql .= " WHERE is_active = :active";
            $params[':active'] = 1;
        }

        $sql .= " ORDER BY display_order ASC, last_name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Décodage sécurisé des liens sociaux
        foreach ($members as &$member) {
            $decoded = json_decode($member['social_links'], true);

            // Si JSON corrompu → renvoyer tableau vide
            $member['social_links'] = is_array($decoded) ? $decoded : [];
        }

        return $members;

    } catch (Exception $e) {
        error_log("Erreur getAllTeamMembers: " . $e->getMessage());
        return [];
    }
}


/**
 * Supprimer un membre d'équipe
 */
function deleteTeamMember($id)
{
    $pdo = getConnexion();

    try {
        // Récupérer le membre pour supprimer son avatar
        $member = getTeamMemberById($id);

        if (!$member) {
            return ['success' => false, 'message' => 'Membre non trouvé'];
        }

        // Supprimer l'avatar s'il existe
        if ($member['avatar'] && file_exists(UPLOAD_PATH . 'team/' . $member['avatar'])) {
            unlink(UPLOAD_PATH . 'team/' . $member['avatar']);
        }

        // Supprimer le membre de la base de données
        $stmt = $pdo->prepare("DELETE FROM team_members WHERE id = ?");
        $stmt->execute([$id]);

        return ['success' => true, 'message' => 'Membre supprimé avec succès'];

    } catch (Exception $e) {
        error_log("Erreur deleteTeamMember: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la suppression: ' . $e->getMessage()];
    }
}

/**
 * Activer/désactiver un membre d'équipe
 */
function toggleTeamMemberStatus($id, $status)
{
    $pdo = getConnexion();

    try {
        $stmt = $pdo->prepare("UPDATE team_members SET is_active = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $id]);

        return ['success' => true, 'message' => 'Statut mis à jour'];
    } catch (Exception $e) {
        error_log("Erreur toggleTeamMemberStatus: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
    }
}

/**
 * Récupérer les statistiques de l'équipe
 */
function getTeamStats()
{
    $pdo = getConnexion();

    try {
        $stats = [];

        // Total des membres
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM team_members");
        $stats['total'] = $stmt->fetch()['total'];

        // Membres actifs
        $stmt = $pdo->query("SELECT COUNT(*) as active FROM team_members WHERE is_active = 1");
        $stats['active'] = $stmt->fetch()['active'];

        // Par poste (top 5)
        $stmt = $pdo->query("
            SELECT position, COUNT(*) as count 
            FROM team_members 
            WHERE is_active = 1
            GROUP BY position 
            ORDER BY count DESC 
            LIMIT 5
        ");
        $stats['by_position'] = $stmt->fetchAll();

        // Derniers membres ajoutés
        $stmt = $pdo->query("
            SELECT first_name, last_name, position, created_at 
            FROM team_members 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $stats['recent'] = $stmt->fetchAll();

        // Membres sans avatar
        $stmt = $pdo->query("SELECT COUNT(*) as no_avatar FROM team_members WHERE avatar IS NULL OR avatar = ''");
        $stats['no_avatar'] = $stmt->fetch()['no_avatar'] ?? 0;

        return $stats;

    } catch (Exception $e) {
        error_log("Erreur getTeamStats: " . $e->getMessage());
        return [];
    }
}
?>