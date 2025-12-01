<?php
/**
 * Fonctions pour la gestion des utilisateurs
 */

/**
 * Créer ou mettre à jour un utilisateur
 */
function saveUser($data, $files = [])
{
    $pdo = getConnexion();

    try {
        // Validation des données
        $errors = validateUserData($data, isset($data['id']) ? $data['id'] : null);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Préparer les données
        $first_name = nettoyer_input($data['first_name']);
        $last_name = nettoyer_input($data['last_name']);
        $email = nettoyer_input($data['email']);
        $phone = isset($data['phone']) ? nettoyer_input($data['phone']) : null;
        $company = isset($data['company']) ? nettoyer_input($data['company']) : null;
        $role = nettoyer_input($data['role']);
        $newsletter = isset($data['newsletter']) ? (int) $data['newsletter'] : 0;
        $notifications = isset($data['notifications']) ? (int) $data['notifications'] : 1;
        $is_active = isset($data['status']) ? (int) $data['status'] : 1;

        // Gérer le mot de passe
        if (isset($data['password']) && !empty($data['password'])) {
            $password = password_hash($data['password'], PASSWORD_DEFAULT);
            $password_changed = true;
        } else {
            $password_changed = false;
        }

        // Gérer l'avatar
        $avatar = null;
        if (isset($data['current_avatar']) && !empty($data['current_avatar'])) {
            $avatar = $data['current_avatar'];
        }

        // Upload du nouvel avatar
        if (isset($files['avatar']) && $files['avatar']['error'] === UPLOAD_ERR_OK) {
            // Supprimer l'ancien avatar s'il existe
            if ($avatar && file_exists(UPLOAD_PATH . 'avatars/' . $avatar)) {
                unlink(UPLOAD_PATH . 'avatars/' . $avatar);
            }

            // Upload du nouvel avatar
            $avatar = uploadAvatar($files['avatar']);
        }

        // Vérifier si c'est une création ou une mise à jour
        if (isset($data['id']) && !empty($data['id'])) {
            // MISE À JOUR
            $sql = "UPDATE users SET 
                    first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    phone = :phone,
                    company = :company,
                    role = :role,
                    newsletter = :newsletter,
                    notifications = :notifications,
                    is_active = :is_active,
                    avatar = :avatar
                    ";

            // Ajouter le mot de passe si changé
            if ($password_changed) {
                $sql .= ", password = :password";
            }

            $sql .= ", updated_at = NOW()
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);

            $params = [
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':email' => $email,
                ':phone' => $phone,
                ':company' => $company,
                ':role' => $role,
                ':newsletter' => $newsletter,
                ':notifications' => $notifications,
                ':is_active' => $is_active,
                ':avatar' => $avatar,
                ':id' => $data['id']
            ];

            if ($password_changed) {
                $params[':password'] = $password;
            }

            $stmt->execute($params);

            // Envoyer un email de bienvenue si demandé
            if (isset($data['send_welcome_email']) && $data['send_welcome_email'] && $password_changed) {
                sendWelcomeEmail($email, $first_name . ' ' . $last_name, $data['password']);
            }

            return ['success' => true, 'message' => 'Utilisateur mis à jour avec succès', 'id' => $data['id']];

        } else {
            // CRÉATION - Le mot de passe est requis
            if (!isset($data['password']) || empty($data['password'])) {
                return ['success' => false, 'errors' => ['password' => 'Le mot de passe est requis']];
            }

            $sql = "INSERT INTO users (
                    first_name, last_name, email, phone, company, role,
                    password, newsletter, notifications, is_active, avatar,
                   created_at
                ) VALUES (
                    :first_name, :last_name, :email, :phone, :company, :role,
                    :password, :newsletter, :notifications, :is_active, :avatar,
                     NOW()
                )";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':email' => $email,
                ':phone' => $phone,
                ':company' => $company,
                ':role' => $role,
                ':password' => $password,
                ':newsletter' => $newsletter,
                ':notifications' => $notifications,
                ':is_active' => $is_active,
                ':avatar' => $avatar,
            ]);

            $id = $pdo->lastInsertId();

            // Envoyer un email de bienvenue si demandé
            if (isset($data['send_welcome_email']) && $data['send_welcome_email']) {
                sendWelcomeEmail($email, $first_name . ' ' . $last_name, $data['password']);
            }

            return ['success' => true, 'message' => 'Utilisateur créé avec succès', 'id' => $id];
        }

    } catch (Exception $e) {
        error_log("Erreur saveUser: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()];
    }
}

/**
 * Valider les données utilisateur
 */
function validateUserData($data, $user_id = null)
{
    $errors = [];

    // Validation du prénom
    if (empty($data['first_name'])) {
        $errors['first_name'] = 'Le prénom est requis';
    } elseif (strlen($data['first_name']) < 2) {
        $errors['first_name'] = 'Le prénom doit contenir au moins 2 caractères';
    }

    // Validation du nom
    if (empty($data['last_name'])) {
        $errors['last_name'] = 'Le nom est requis';
    }

    // Validation de l'email
    if (empty($data['email'])) {
        $errors['email'] = 'L\'email est requis';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'L\'email n\'est pas valide';
    } else {
        // Vérifier si l'email existe déjà (sauf pour l'utilisateur courant)
        $pdo = getConnexion();
        $sql = "SELECT id FROM users WHERE email = ?";
        $params = [$data['email']];

        if ($user_id) {
            $sql .= " AND id != ?";
            $params[] = $user_id;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->fetch()) {
            $errors['email'] = 'Cet email est déjà utilisé';
        }
    }

    // Validation du rôle
    $allowed_roles = ['admin', 'client', 'visitor'];
    if (empty($data['role']) || !in_array($data['role'], $allowed_roles)) {
        $errors['role'] = 'Le rôle est invalide';
    }

    // Validation du mot de passe (pour la création)
    if (!$user_id) {
        if (empty($data['password'])) {
            $errors['password'] = 'Le mot de passe est requis';
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères';
        }

        if ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Les mots de passe ne correspondent pas';
        }
    }

    // Validation du téléphone
    if (!empty($data['phone']) && !preg_match('/^[0-9\s\+\-\(\)]{10,20}$/', $data['phone'])) {
        $errors['phone'] = 'Le format du téléphone est invalide';
    }

    return $errors;
}

/**
 * Upload un avatar
 */
function uploadAvatar($file)
{
    $upload_dir = UPLOAD_PATH . 'avatars/';

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
    $filename = 'avatar_' . uniqid() . '_' . time() . '.' . $extension;
    $destination = $upload_dir . $filename;

    // Déplacer le fichier uploadé
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Erreur lors du déplacement du fichier');
    }

    // Redimensionner l'avatar en carré 200x200
    resizeImage($destination, 200, 200);

    return $filename;
}

/**
 * Envoyer un email de bienvenue
 */
function sendWelcomeEmail($email, $name, $password)
{
    $subject = 'Bienvenue sur PCL Lab';

    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #3498db; color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            .button { 
                display: inline-block; 
                padding: 12px 24px; 
                background: #3498db; 
                color: white; 
                text-decoration: none; 
                border-radius: 4px; 
                margin: 10px 0; 
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Bienvenue sur PCL Lab</h1>
            </div>
            <div class='content'>
                <h2>Bonjour $name,</h2>
                <p>Votre compte a été créé avec succès sur notre plateforme.</p>
                <p><strong>Vos identifiants :</strong></p>
                <ul>
                    <li><strong>Email :</strong> $email</li>
                    <li><strong>Mot de passe :</strong> $password</li>
                </ul>
                <p>Nous vous recommandons de changer votre mot de passe après votre première connexion.</p>
                <p>
                    <a href='" . ROOT_URL . "/login' class='button'>Se connecter</a>
                </p>
                <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>
            </div>
            <div class='footer'>
                <p>© " . date('Y') . " PCL Lab. Tous droits réservés.</p>
            </div>
        </div>
    </body>
    </html>";

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: PCL Lab <noreply@plclab.com>',
        'Reply-To: contact@plclab.com'
    ];

    return mail($email, $subject, $message, implode("\r\n", $headers));
}

/**
 * Récupérer un utilisateur par son ID
 */
function getUserById($id)
{
    $pdo = getConnexion();

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Erreur getUserById: " . $e->getMessage());
        return null;
    }
}

/**
 * Récupérer tous les utilisateurs avec pagination
 */
function getAllUsers($page = 1, $per_page = 10, $filters = [])
{
    $pdo = getConnexion();

    try {
        $conditions = [];
        $params = [];

        // Filtres
        if (!empty($filters['role'])) {
            $conditions[] = "role = ?";
            $params[] = $filters['role'];
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $conditions[] = "is_active = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $where_sql = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // Pagination
        $offset = ($page - 1) * $per_page;

        // Count
        $count_sql = "SELECT COUNT(*) as total FROM users $where_sql";
        $stmt = $pdo->prepare($count_sql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        $total_pages = ceil($total / $per_page);

        // Query principale sans LIMIT dans les paramètres
        $sql = "SELECT * FROM users $where_sql 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);

        // Bind filtres
        foreach ($params as $i => $value) {
            $stmt->bindValue($i + 1, $value);
        }

        // Bind LIMIT/OFFSET en INT obligatoire
        $stmt->bindValue(':limit', (int) $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

        $stmt->execute();
        $users = $stmt->fetchAll();

        return [
            'users' => $users,
            'total' => $total,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'per_page' => $per_page
        ];

    } catch (Exception $e) {
        error_log("Erreur getAllUsers: " . $e->getMessage());
        return ['users' => [], 'total' => 0, 'total_pages' => 0];
    }
}


/**
 * Supprimer un utilisateur
 */
function deleteUser($id)
{
    $pdo = getConnexion();

    try {
        // Récupérer l'utilisateur pour supprimer son avatar
        $user = getUserById($id);

        if (!$user) {
            return ['success' => false, 'message' => 'Utilisateur non trouvé'];
        }

        // Ne pas permettre la suppression de l'utilisateur connecté
        if ($user['id'] == $_SESSION['id_utilisateur']) {
            return ['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte'];
        }

        // Supprimer l'avatar s'il existe
        if ($user['avatar'] && file_exists(UPLOAD_PATH . 'avatars/' . $user['avatar'])) {
            unlink(UPLOAD_PATH . 'avatars/' . $user['avatar']);
        }

        // Supprimer l'utilisateur de la base de données
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        return ['success' => true, 'message' => 'Utilisateur supprimé avec succès'];

    } catch (Exception $e) {
        error_log("Erreur deleteUser: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la suppression: ' . $e->getMessage()];
    }
}

/**
 * Activer/désactiver un utilisateur
 */
function toggleUserStatus($id, $status)
{
    $pdo = getConnexion();

    try {
        // Ne pas permettre de désactiver son propre compte
        if ($id == $_SESSION['id_utilisateur'] && $status == 0) {
            return ['success' => false, 'message' => 'Vous ne pouvez pas désactiver votre propre compte'];
        }

        $stmt = $pdo->prepare("UPDATE users SET is_active = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $id]);

        return ['success' => true, 'message' => 'Statut mis à jour'];
    } catch (Exception $e) {
        error_log("Erreur toggleUserStatus: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
    }
}

/**
 * Récupérer les statistiques des utilisateurs
 */
function getUsersStats()
{
    $pdo = getConnexion();

    try {
        $stats = [];

        // Total des utilisateurs
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $stats['total'] = $stmt->fetch()['total'];

        // Utilisateurs actifs
        $stmt = $pdo->query("SELECT COUNT(*) as active FROM users WHERE is_active = 1");
        $stats['active'] = $stmt->fetch()['active'];

        // Par rôle
        $stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
        $stats['by_role'] = $stmt->fetchAll();

        // Inscriptions par mois (derniers 6 mois)
        $stmt = $pdo->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                   COUNT(*) as count 
            FROM users 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
        ");
        $stats['registrations_by_month'] = $stmt->fetchAll();

        // Dernière connexion
        $stmt = $pdo->query("SELECT COUNT(*) as never_logged FROM users WHERE last_login IS NULL");
        $stats['never_logged'] = $stmt->fetch()['never_logged'];

        // Nouveaux utilisateurs ce mois-ci
        $stmt = $pdo->query("
            SELECT COUNT(*) as new_this_month 
            FROM users 
            WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
        ");
        $stats['new_this_month'] = $stmt->fetch()['new_this_month'];

        return $stats;

    } catch (Exception $e) {
        error_log("Erreur getUsersStats: " . $e->getMessage());
        return [];
    }
}
?>