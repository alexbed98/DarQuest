<?php
session_start();
require_once 'src/initialization.php';
require_once 'core/Database.php';
require_once 'src/CartDAL.php';

// Sauvegarder le panier en BD avant de détruire la session
if (!empty($_SESSION['id'])) {
    $cartKey = 'panier_user_' . (int) $_SESSION['id'];
    if (!empty($_SESSION[$cartKey]) && is_array($_SESSION[$cartKey])) {
        $connexion = Database::getConnexion($dbConfig);
        CartDAL::saveCart($connexion, (int) $_SESSION['id'], $_SESSION[$cartKey]);
    }
}

session_unset();
session_destroy();
header('Location: catalogue.php');
exit;
