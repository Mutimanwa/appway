<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../includes/project-functions.php';

// verifier_session(['admin']);

header('Content-Type: application/json');

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

// Vérifier le token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== generer_token()) {
    echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
    exit();
}

// Récupérer l'ID
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID de projet manquant']);
    exit();
}

// Supprimer le projet
$result = deleteProject($id);

echo json_encode($result);
?>