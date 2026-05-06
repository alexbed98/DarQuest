<?php

session_start();

require_once 'src/initialization.php';
require_once 'src/Page.php';
require_once 'core/Database.php';
require_once 'src/AccountDAL.php';

$guid = $_GET['guid'] ?? null;

if ($guid) {
    $connexion = Database::getConnexion($dbConfig);
    $idJoueur = AccountDAL::selectByGuid($connexion, $guid);

    if ($idJoueur != false) {
        if (AccountDAL::removeActivationGuid($connexion, $idJoueur)){
            header('Location: ' . Page::Connexion->url());
            exit;
        }
    }
}

