<?php
// les constantes pour la connexion a la base de donnees 
define("DB_NAME", "pcllab");
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PSWD", "");

define('SITE_NAME', 'Plc lab');

define('ROOT_PATH', dirname(__DIR__));

// Chemins vers les dossiers importants
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');

// chemin principale  
define("ROOT_URL", "/appway");


// URLs vers les ressources
define('ASSETS_URL', ROOT_URL . '/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');
define('IMAGES_URL', ASSETS_URL . '/images');
define('UPLOAD_URL', ROOT_URL . '/uploads');

// liens externes du site 
define("SITE_NUMERO", "+2576110332");
define("SITE_EMAIL", "plccreativeroom7@gmail.com");
define("SITE_FACEBOOK", "https://www.facebook.com/share/1CvNBoBngA/");
define("SITE_INSTAGRAM", "");
define("SITE_LINKEDIN", "");
define("SITE_X", "https://x.com/PLCLab?t=trNAovDs5Yrij0sd4lMjrw&s=08");
define("SITE_TIKTOK", "");
define("SITE_GITHUB", "");

// les url de l'admin
define("ADMIN_URL", ROOT_URL . "/admin");
