<?php
/**
 * Fonctions pour la gestion des contacts
 */

/**
 * Créer un nouveau contact
 */
function saveContact($data)
{
    $pdo = getConnexion();

    try {
        // Validation des données
        $errors = validateContactData($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Préparer les données
        $first_name = nettoyer_input($data['first_name']);
        $last_name = nettoyer_input($data['last_name']);
        $email = nettoyer_input($data['email']);
        $phone = isset($data['phone']) ? nettoyer_input($data['phone']) : null;
        $company = isset($data['company']) ? nettoyer_input($data['company']) : null;
        $service_id = isset($data['service_id']) ? (int) $data['service_id'] : null;
        $project_type = isset($data['project_type']) ? nettoyer_input($data['project_type']) : null;
        $budget = isset($data['budget']) ? nettoyer_input($data['budget']) : null;
        $message = nettoyer_input($data['message']);
        $source = isset($data['source']) ? nettoyer_input($data['source']) : 'website';

        // Assigner automatiquement si non spécifié
        $assigned_to = isset($data['assigned_to']) ? (int) $data['assigned_to'] : null;

        $sql = "INSERT INTO contacts (
                first_name, last_name, email, phone, company, service_id,
                project_type, budget, message, source, assigned_to, created_at
            ) VALUES (
                :first_name, :last_name, :email, :phone, :company, :service_id,
                :project_type, :budget, :message, :source, :assigned_to, NOW()
            )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':email' => $email,
            ':phone' => $phone,
            ':company' => $company,
            ':service_id' => $service_id,
            ':project_type' => $project_type,
            ':budget' => $budget,
            ':message' => $message,
            ':source' => $source,
            ':assigned_to' => $assigned_to
        ]);

        $id = $pdo->lastInsertId();

        // Envoyer une notification email si configuré
        sendContactNotification($id);

        return ['success' => true, 'message' => 'Message envoyé avec succès', 'id' => $id];

    } catch (Exception $e) {
        error_log("Erreur saveContact: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de l\'envoi: ' . $e->getMessage()];
    }
}

/**
 * Valider les données de contact
 */
function validateContactData($data)
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

    // Validation de l'email
    if (empty($data['email'])) {
        $errors['email'] = 'L\'email est requis';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'L\'email n\'est pas valide';
    }

    // Validation du message
    if (empty($data['message'])) {
        $errors['message'] = 'Le message est requis';
    } elseif (strlen($data['message']) < 10) {
        $errors['message'] = 'Le message doit contenir au moins 10 caractères';
    }

    // Validation du téléphone
    if (!empty($data['phone']) && !preg_match('/^[0-9\s\+\-\(\)]{10,20}$/', $data['phone'])) {
        $errors['phone'] = 'Le format du téléphone est invalide';
    }

    return $errors;
}

/**
 * Envoyer une notification email pour un nouveau contact
 */
function sendContactNotification($contact_id)
{
    $contact = getContactById($contact_id);
    if (!$contact)
        return false;

    $subject = 'Nouveau contact - PCL Lab';

    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #3498db; color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .info { background: white; padding: 15px; border-left: 4px solid #3498db; margin: 10px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Nouveau contact reçu</h1>
            </div>
            <div class='content'>
                <h2>Informations du contact</h2>
                <div class='info'>
                    <p><strong>Nom :</strong> {$contact['first_name']} {$contact['last_name']}</p>
                    <p><strong>Email :</strong> {$contact['email']}</p>
                    <p><strong>Téléphone :</strong> " . ($contact['phone'] ?: 'Non spécifié') . "</p>
                    <p><strong>Entreprise :</strong> " . ($contact['company'] ?: 'Non spécifié') . "</p>
                    <p><strong>Date :</strong> " . date('d/m/Y H:i', strtotime($contact['created_at'])) . "</p>
                </div>
                
                <h3>Message :</h3>
                <div class='info'>
                    <p>" . nl2br(htmlspecialchars($contact['message'])) . "</p>
                </div>
                
                <p>
                    <a href='" . ADMIN_URL . "/contacts/view.php?id={$contact_id}' style='color: #3498db;'>
                        Voir les détails dans l'administration
                    </a>
                </p>
            </div>
            <div class='footer'>
                <p>© " . date('Y') . " PCL Lab. Tous droits réservés.</p>
            </div>
        </div>
    </body>
    </html>";

    $admin_email = SITE_EMAIL;
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: PCL Lab <noreply@plclab.com>'
    ];

    return mail($admin_email, $subject, $message, implode("\r\n", $headers));
}

/**
 * Récupérer un contact par son ID
 */
function getContactById($id)
{
    $pdo = getConnexion();

    try {
        $stmt = $pdo->prepare("
            SELECT c.*, 
                   s.name as service_name,
                   u.first_name as assigned_first_name,
                   u.last_name as assigned_last_name
            FROM contacts c
            LEFT JOIN services s ON c.service_id = s.id
            LEFT JOIN users u ON c.assigned_to = u.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Erreur getContactById: " . $e->getMessage());
        return null;
    }
}

/**
 * Récupérer tous les contacts avec pagination
 */
function getAllContacts($page = 1, $per_page = 10, $filters = [])
{
    $pdo = getConnexion();

    try {
        $conditions = [];
        $params = [];

        // Appliquer les filtres
        if (isset($filters['status']) && $filters['status']) {
            $conditions[] = "c.status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['source']) && $filters['source']) {
            $conditions[] = "c.source = ?";
            $params[] = $filters['source'];
        }

        if (isset($filters['service_id']) && $filters['service_id']) {
            $conditions[] = "c.service_id = ?";
            $params[] = $filters['service_id'];
        }

        if (isset($filters['assigned_to']) && $filters['assigned_to']) {
            if ($filters['assigned_to'] === 'unassigned') {
                $conditions[] = "c.assigned_to IS NULL";
            } else {
                $conditions[] = "c.assigned_to = ?";
                $params[] = $filters['assigned_to'];
            }
        }

        if (isset($filters['search']) && $filters['search']) {
            $conditions[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR c.message LIKE ?)";
            $search_term = '%' . $filters['search'] . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }

        // Construire la requête WHERE
        $where_sql = '';
        if (!empty($conditions)) {
            $where_sql = 'WHERE ' . implode(' AND ', $conditions);
        }

        // Calculer l'offset
        $offset = ($page - 1) * $per_page;

        // Requête pour le nombre total
        $count_sql = "SELECT COUNT(*) as total FROM contacts c $where_sql";
        $stmt = $pdo->prepare($count_sql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        $total_pages = ceil($total / $per_page);

        // Requête pour les données
        $sql = "
            SELECT c.*, 
                   s.name as service_name,
                   u.first_name as assigned_first_name,
                   u.last_name as assigned_last_name
            FROM contacts c
            LEFT JOIN services s ON c.service_id = s.id
            LEFT JOIN users u ON c.assigned_to = u.id
            $where_sql
            ORDER BY 
                CASE 
                    WHEN c.status = 'new' THEN 1
                    WHEN c.status = 'contacted' THEN 2
                    WHEN c.status = 'qualified' THEN 3
                    WHEN c.status = 'client' THEN 4
                    ELSE 5
                END,
                c.created_at DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $per_page;
        $params[] = $offset;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $contacts = $stmt->fetchAll();

        return [
            'contacts' => $contacts,
            'total' => $total,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'per_page' => $per_page
        ];

    } catch (Exception $e) {
        error_log("Erreur getAllContacts: " . $e->getMessage());
        return ['contacts' => [], 'total' => 0, 'total_pages' => 0];
    }
}

/**
 * Mettre à jour le statut d'un contact
 */
function updateContactStatus($id, $status, $assigned_to = null)
{
    $pdo = getConnexion();

    try {
        // Vérifier que le statut est valide
        $allowed_statuses = ['new', 'contacted', 'qualified', 'client', 'archived'];
        if (!in_array($status, $allowed_statuses)) {
            return ['success' => false, 'message' => 'Statut invalide'];
        }

        $sql = "UPDATE contacts SET status = ?, assigned_to = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $assigned_to, $id]);

        return ['success' => true, 'message' => 'Statut mis à jour'];
    } catch (Exception $e) {
        error_log("Erreur updateContactStatus: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
    }
}

/**
 * Supprimer un contact
 */
function deleteContact($id)
{
    $pdo = getConnexion();

    try {
        $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->execute([$id]);

        return ['success' => true, 'message' => 'Contact supprimé avec succès'];

    } catch (Exception $e) {
        error_log("Erreur deleteContact: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la suppression: ' . $e->getMessage()];
    }
}

/**
 * Récupérer les statistiques des contacts
 */
function getContactsStats()
{
    $pdo = getConnexion();

    try {
        $stats = [];

        // Total des contacts
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM contacts");
        $stats['total'] = $stmt->fetch()['total'];

        // Par statut
        $stmt = $pdo->query("
            SELECT status, COUNT(*) as count 
            FROM contacts 
            GROUP BY status
            ORDER BY 
                CASE status
                    WHEN 'new' THEN 1
                    WHEN 'contacted' THEN 2
                    WHEN 'qualified' THEN 3
                    WHEN 'client' THEN 4
                    WHEN 'archived' THEN 5
                    ELSE 6
                END
        ");
        $stats['by_status'] = $stmt->fetchAll();

        // Par source
        $stmt = $pdo->query("SELECT source, COUNT(*) as count FROM contacts GROUP BY source");
        $stats['by_source'] = $stmt->fetchAll();

        // Non assignés
        $stmt = $pdo->query("SELECT COUNT(*) as unassigned FROM contacts WHERE assigned_to IS NULL");
        $stats['unassigned'] = $stmt->fetch()['unassigned'] ?? 0;

        // Nouveaux aujourd'hui
        $stmt = $pdo->query("
            SELECT COUNT(*) as new_today 
            FROM contacts 
            WHERE DATE(created_at) = CURDATE()
            AND status = 'new'
        ");
        $stats['new_today'] = $stmt->fetch()['new_today'] ?? 0;

        // Nouveaux cette semaine
        $stmt = $pdo->query("
            SELECT COUNT(*) as new_this_week 
            FROM contacts 
            WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
            AND status = 'new'
        ");
        $stats['new_this_week'] = $stmt->fetch()['new_this_week'] ?? 0;

        // Conversion en clients
        $stmt = $pdo->query("SELECT COUNT(*) as clients FROM contacts WHERE status = 'client'");
        $stats['clients'] = $stmt->fetch()['clients'] ?? 0;

        // Par service
        $stmt = $pdo->query("
            SELECT s.name, COUNT(c.id) as count
            FROM contacts c
            LEFT JOIN services s ON c.service_id = s.id
            WHERE c.service_id IS NOT NULL
            GROUP BY c.service_id, s.name
            ORDER BY count DESC
            LIMIT 5
        ");
        $stats['by_service'] = $stmt->fetchAll();

        // Tendances mensuelles
        $stmt = $pdo->query("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as contacts_count,
                SUM(CASE WHEN status = 'client' THEN 1 ELSE 0 END) as clients_count
            FROM contacts
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
        ");
        $stats['monthly'] = $stmt->fetchAll();

        return $stats;

    } catch (Exception $e) {
        error_log("Erreur getContactsStats: " . $e->getMessage());
        return [];
    }
}
?>