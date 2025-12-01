<?php include_once 'config/config.php';
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
    <link rel="icon" href="<?= IMAGES_URL ?>/logo-plc.png" type="image/x-icon">

    <!-- Google Fonts -->
    <!-- <link href="https://fonts.googleapis.com/css?family=Ubuntu:300,300i,400,400i,500,500i,700,700i&display=swap"
        rel="stylesheet"> -->

    <!-- Stylesheets -->
    <link href="<?= CSS_URL ?>/font-awesome-all.css" rel="stylesheet">
    <link href="<?= CSS_URL ?>/flaticon.css" rel="stylesheet">
    <link href="<?= CSS_URL ?>/owl.css" rel="stylesheet">
    <link href="<?= CSS_URL ?>/bootstrap.css" rel="stylesheet">
    <link href="<?= CSS_URL ?>/jquery.fancybox.min.css" rel="stylesheet">
    <link href="<?= CSS_URL ?>/animate.css" rel="stylesheet">
    <link href="<?= CSS_URL ?>/style.css" rel="stylesheet">
    <link href="<?= CSS_URL ?>/responsive.css" rel="stylesheet">

</head>

<body class="boxed_wrapper">

    <!-- preloader -->
    <!-- <div class="preloader"></div> -->
    <!-- preloader -->

    <?php
    // Inclure la barre de navigation
    // Le fichier navbar.php choisira la bonne barre en fonction du rôle de l'utilisateur
    if (file_exists(INCLUDES_PATH . '/navbar.php')) {
        require_once(INCLUDES_PATH . '/navbar.php');
    }

    ?>