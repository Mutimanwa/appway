<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $article_id = isset($_POST['article_id']) ? (int) $_POST['article_id'] : 0;
    $name = nettoyer_input($_POST['name']);
    $email = nettoyer_input($_POST['email']);
    $content = nettoyer_input($_POST['content']);
    $website = isset($_POST['website']) ? nettoyer_input($_POST['website']) : null;

    // Validation
    if (empty($name) || empty($email) || empty($content) || $article_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Adresse email invalide.']);
        exit;
    }

    if (strlen($name) < 2 || strlen($name) > 50) {
        echo json_encode(['success' => false, 'message' => 'Le nom doit contenir entre 2 et 50 caractères.']);
        exit;
    }

    if (strlen($content) < 10 || strlen($content) > 1000) {
        echo json_encode(['success' => false, 'message' => 'Le commentaire doit contenir entre 10 et 1000 caractères.']);
        exit;
    }

    $pdo = getConnexion();

    try {
        // Vérifier si l'article existe
        $stmt = $pdo->prepare("SELECT id FROM articles WHERE id = ? AND status = 'published'");
        $stmt->execute([$article_id]);

        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Article non trouvé.']);
            exit;
        }

        // Insérer le commentaire
        $stmt = $pdo->prepare("
            INSERT INTO commentaires (article_id, name, email, website, content, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$article_id, $name, $email, $website, $content]);

        echo json_encode([
            'success' => true,
            'message' => 'Votre commentaire a été posté avec succès. Il sera visible après modération.'
        ]);

    } catch (Exception $e) {
        error_log("Erreur ajout commentaire: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Une erreur est survenue. Veuillez réessayer.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
}
?>