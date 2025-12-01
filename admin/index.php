<?php
require_once dirname(__DIR__) . "/config/database.php";
require_once dirname(__DIR__) . "/includes/functions.php";

$message = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // Nettoyage des variables 
    $email = nettoyer_input($_POST['email']);
    $password = $_POST['password'];

    // Validation des champs
    if (empty($email) || empty($password)) {
        $message = afficher_alert("Tous les champs sont obligatoires", "warning");

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = afficher_alert("Email invalide", "warning");

    } else {
        // Tentative de connexion avec vérification du résultat
        if (connecter_utilisateur($email, $password)) {
            // Connexion réussie - redirection
            rediriger_vers("dashboard.php");
            exit;
        } else {
            // Échec de connexion
            $message = afficher_alert("Email ou mot de passe incorrect", "danger");
        }
    }
}


?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="description"
        content="PLC Lab - Startup innovante spécialisée dans la programmation et le développement de solutions logicielles">

    <title>PLC Lab - Solutions de Programmation Innovantes</title>

    <!-- Fav Icon -->
    <link rel="icon" href="images/logo-plc.png" type="image/x-icon">

    <!-- Google Fonts -->
    <!-- <link href="https://fonts.googleapis.com/css?family=Ubuntu:300,300i,400,400i,500,500i,700,700i&display=swap"
        rel="stylesheet"> -->

    <!-- Stylesheets -->
    <link href="../css/font-awesome-all.css" rel="stylesheet">
    <link href="../css/flaticon.css" rel="stylesheet">
    <link href="../css/bootstrap.css" rel="stylesheet">
    <link href="../css/animate.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">


</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-lg-4 my-5 mx-auto rounded-5">
                <div class="card">
                    <div class="card-header text-center flex-column bg-primary text-white"> <!-- Ajout text-white -->
                        <div class="">
                            <img width="150px" src="../images/logo-plc.png" alt="Logo">
                        </div>
                        <h4>Connexion</h4>
                    </div>
                    <form method="post" class="card-body">
                        <!-- Message d'alerte  -->
                        <?php if (isset($message) && !empty($message)): ?>
                            <div class="mb-3">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label" for="email">Email <span class="text-warning">*</span></label>
                            <input name="email" type="email" class="form-control" placeholder="Email" required
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe <span
                                    class="text-warning">*</span></label>
                            <input name="password" type="password" class="form-control" placeholder="Mot de passe"
                                required>
                        </div>
                        <div>
                            <button type="submit" class="w-100 mt-4 btn btn-primary">Connexion</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>

</html>