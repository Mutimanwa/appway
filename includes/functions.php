<?php
require_once dirname(__DIR__) . "/config/database.php";
require_once dirname(__DIR__) . "/config/config.php";
/**
 * Démarre une session PHP de manière sécurisée.
 * Configure les options pour renforcer la sécurité des sessions.
 */
function demarrer_session_securisee()
{
    // Si une session est déjà active, ne rien faire
    if (session_status() == PHP_SESSION_NONE) {
        // Configure les cookies de session pour être plus sécurisés
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => $cookieParams['lifetime'],
            'path' => $cookieParams['path'],
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => isset($_SERVER['HTTPS']), // Vrai si en HTTPS
            'httponly' => true, // Empêche l'accès au cookie via JavaScript
            'samesite' => 'Strict' // Protection contre les attaques CSRF
        ]);
        session_start();
    }
}

function afficher_alert($message, $type = 'warning')
{
    // Liste des types Bootstrap autorisés
    $types_autorises = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'];

    // Si le type n'est pas valide, on utilise 'warning' par défaut
    if (!in_array($type, $types_autorises)) {
        $type = 'warning';
    }

    // Construction du HTML
    $html = '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
    $html .= htmlspecialchars($message);
    $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    $html .= '</div>';

    return $html;
}

/**
 * Nettoie une chaîne de caractères pour éviter les attaques XSS.
 *
 * @param string $data La donnée à nettoyer.
 * @return string La donnée nettoyée.
 */
function nettoyer_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Redirige l'utilisateur vers une URL spécifiée.
 *
 * @param string $url L'URL de destination.
 */
function rediriger_vers($url)
{
    header("Location: " . $url);
    exit();
}

/**
 * Formate une date (ou un datetime) en format français.
 *
 * @param string $date_sql La date au format Y-m-d H:i:s ou Y-m-d.
 * @return string La date formatée (ex: "13 novembre 2025 à 14:30").
 */
function formater_date($date_sql)
{
    if (empty($date_sql)) {
        return "Date non spécifiée";
    }

    return date("d/m/Y H:i", strtotime($date_sql));
}


// les iformation du crud0  

function connecter_utilisateur($email, $mot_de_passe)
{
    $pdo = getConnexion();

    if (!$pdo) {
        error_log("Connexion à la base de données échouée");
        return false;
    }

    try {
        // Sélectionner l'utilisateur
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $utilisateur = $stmt->fetch();

        if (!$utilisateur) {
            return false; // Aucun utilisateur trouvé
        }

        // Vérifier le mot de passe
        if (!password_verify($mot_de_passe, $utilisateur['password'])) {
            return false; // Mot de passe incorrect
        }

        // Mettre à jour la dernière connexion
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$utilisateur['id']]);

        // Démarrer la session si ce n'est pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Stocker les infos dans la session
        $_SESSION['id_utilisateur'] = $utilisateur['id'];
        $_SESSION['nom'] = $utilisateur['first_name'];
        $_SESSION['prenom'] = $utilisateur['last_name'];
        $_SESSION['email'] = $utilisateur['email'];
        $_SESSION['role'] = $utilisateur['role'];

        return true;

    } catch (Exception $e) {
        error_log("Erreur connexion utilisateur: " . $e->getMessage());
        return false;
    }
}



function verifier_session($roles_requis = [])
{
    demarrer_session_securisee();

    // Si l'utilisateur n'est pas connecté
    if (!isset($_SESSION['id_utilisateur'])) {
        // Stocker l'URL demandée pour rediriger après la connexion
        $_SESSION['message_erreur'] = "Vous devez être connecté pour accéder à cette page.";
        rediriger_vers(ROOT_URL . '/admin/');
    }

    // Si des rôles spécifiques sont requis
    if (!empty($roles_requis)) {
        // Si le rôle de l'utilisateur n'est pas dans le tableau des rôles autorisés
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $roles_requis)) {
            // On peut rediriger vers une page d'accès refusé ou le tableau de bord
            $_SESSION['message_erreur'] = "Accès non autorisé.";
            rediriger_vers(ROOT_URL . '/index.php');
        }
    }

    // Si tout est bon, l'exécution du script continue.
}

/**
 * Génère un token CSRF
 * @return string Token CSRF
 */
function generer_token()
{
    // Si la session n'est pas démarrée, la démarrer
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Générer un nouveau token si inexistant ou expiré
    if (
        !isset($_SESSION['csrf_token']) ||
        !isset($_SESSION['csrf_token_time']) ||
        (time() - $_SESSION['csrf_token_time'] > 3600)
    ) { // Token valide 1 heure

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }

    return $_SESSION['csrf_token'];
}

/**
 * Vérifie un token CSRF
 * @param string $token Token à vérifier
 * @return bool True si le token est valide
 */
function verifier_token($token)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérifier l'existence du token en session
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }

    // Vérifier si le token a expiré (1 heure)
    if (
        isset($_SESSION['csrf_token_time']) &&
        (time() - $_SESSION['csrf_token_time'] > 3600)
    ) {
        // Supprimer le token expiré
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }

    // Comparer les tokens
    return hash_equals($_SESSION['csrf_token'], $token);
}