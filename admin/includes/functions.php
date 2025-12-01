<?php
// admin/includes/functions.php

/**
 * Récupérer les statistiques du dashboard
 */
function getDashboardStats($pdo)
{
    $stats = [];

    try {
        // Nombre total d'utilisateurs
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $stats['total_users'] = $stmt->fetch()['total'];

        // Nombre total de projets
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM projects");
        $stats['total_projects'] = $stmt->fetch()['total'];

        // Projets en cours
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM projects WHERE status = 'in_progress'");
        $stats['active_projects'] = $stmt->fetch()['total'];

        // Nouveaux contacts ce mois-ci
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM contacts WHERE MONTH(created_at) = MONTH(CURRENT_DATE())");
        $stats['new_contacts'] = $stmt->fetch()['total'];

        // Membres de l'équipe actifs
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM team_members WHERE is_active = 1");
        $stats['team_members'] = $stmt->fetch()['total'];

        // Articles publiés
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM articles WHERE status = 'published'");
        $stats['total_articles'] = $stmt->fetch()['total'];

    } catch (Exception $e) {
        error_log("Erreur stats dashboard: " . $e->getMessage());
    }

    return $stats;
}

/**
 * Récupérer les projets récents
 */
function getRecentProjects($pdo, $limit = 5)
{
    try {
        $sql = "SELECT p.*, u.first_name as client_name 
                FROM projects p 
                LEFT JOIN users u ON p.client_id = u.id 
                ORDER BY p.created_at DESC 
                LIMIT ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Erreur projets récents: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupérer les top membres de l'équipe
 */
function getTopTeamMembers($pdo, $limit = 3)
{
    try {
        $sql = "SELECT tm.*, 
                (SELECT COUNT(*) FROM projects WHERE assigned_to = tm.id) as projects_count 
                FROM team_members tm 
                WHERE tm.is_active = 1 
                ORDER BY projects_count DESC 
                LIMIT ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Erreur top membres: " . $e->getMessage());
        return [];
    }
}

?>