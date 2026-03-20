<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=======================================================

// definition du chemin d'accès système
define('ROOT', dirname(__DIR__));

// constante pour les dossiers
// a creer a chaque dossier qu'on cree
const TEMPLATE = ROOT . '/template';
const SRC = ROOT . '/src';
const UPLOAD = ROOT . '/upload';

//=======================================================

// url publique
const URL_ROOT = '/';

// On pourrait faire des constantes pour d'autres url
const IMG = URL_ROOT . 'public/img';
const CSS = URL_ROOT . 'public/css';
const PRODUCT_IMG = URL_ROOT . 'upload';

//=======================================================

// constante pour simplifier le code
define('IS_POST', $_SERVER['REQUEST_METHOD'] === 'POST');
define('IS_AUTH', isset($_SESSION['id']));
define('IS_ADMIN', IS_AUTH && $_SESSION['role'] === 1);

//=======================================================

// mySql
// il faut verifier comment ca fonctionne et entrer les 
// bonnes infos
// $dbConfig = [
//     "dbHost" => "127.0.0.1",
//     "dbName" => "darquest",
//     "dbUser" => "root",
//     "dbPass" => "",
//     "dbParams" => [
//         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
//         PDO::ATTR_CASE => PDO::CASE_NATURAL,
//         PDO::ATTR_ORACLE_NULLS => PDO::NULL_EMPTY_STRING,
//         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
//     ],
// ];

//==========================================

const PASSWORD_SIZE = 8;
const MATCH_PATTERN ='/^(?=.*\d)(?=.*[@#\-_$%^&+=§!\?])(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z@#\-_$%^&+=§!\?]{8,20}$/';

/* 
minimum 1 lettre minuscule
minimum 1 lettre majuscule
minimum un chiffre
au moins un symbole @#-_$%^&+=§!?
*/  

// EX: !123Abc$

//==========================================