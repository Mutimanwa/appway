<?php
require_once "config.php";
require_once __DIR__ . "/../includes/functions.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


/**
 * Fonction pour récupérer la connexion à la base de données
 * @return PDO
 * @throws Exception Si la connexion échoue
 */
function getConnexion()
{
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PSWD,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        return $pdo;

    } catch (Exception $e) {
        throw new Exception("Erreur de connexion à la base de données: " . $e->getMessage());
    }
}

demarrer_session_securisee();

?>