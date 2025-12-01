<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'subscribe') {
    $email = nettoyer_input($_POST['email']);

    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Adresse email invalide.']);
        exit;
    }

    $pdo = getConnexion();

    try {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND newsletter = 1");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Vous êtes déjà inscrit à notre newsletter.']);
            exit;
        }

        // Vérifier si l'email existe dans users mais sans newsletter
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($user = $stmt->fetch()) {
            // Mettre à jour l'utilisateur existant
            $stmt = $pdo->prepare("UPDATE users SET newsletter = 1 WHERE id = ?");
            $stmt->execute([$user['id']]);
        } else {
            // Créer un nouvel utilisateur visitor pour la newsletter
            $stmt = $pdo->prepare("INSERT INTO users (email, role, newsletter, created_at) VALUES (?, 'visitor', 1, NOW())");
            $stmt->execute([$email]);
        }

        // Envoyer un email de confirmation (optionnel)
        // sendConfirmationEmail($email);

        echo json_encode(['success' => true, 'message' => 'Merci pour votre inscription à notre newsletter !']);

    } catch (Exception $e) {
        error_log("Erreur inscription newsletter: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Une erreur est survenue. Veuillez réessayer.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
}
?>