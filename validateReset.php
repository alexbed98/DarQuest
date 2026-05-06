<?php

session_start();

require_once 'src/initialization.php';
require_once 'src/Page.php';
require_once 'core/Database.php';
require_once 'src/AccountDAL.php';

$reset_guid = $_GET['reset_guid'] ?? null;

if ($reset_guid) {
    $connexion = Database::getConnexion($dbConfig);
    $idJoueur = AccountDAL::selectByResetGuid($connexion, $reset_guid);

    if ($idJoueur != false) {
        $_SESSION['reset_id_joueur'] = $idJoueur;
        $_SESSION['reset_autorise'] = true;

        header('Location: ' . Page::ResetMDP->url());
        exit;
    }
}


