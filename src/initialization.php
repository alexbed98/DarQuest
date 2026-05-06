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
const VENDOR = ROOT . '/vendor';
const IMG_ITEMS = ROOT . '/public/img/items';

//=======================================================

// url publique (compatible root and subfolder deployments)
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$baseUrl = rtrim($scriptDir, '/');
define('URL_ROOT', ($baseUrl === '' ? '/' : $baseUrl . '/'));

const IMG = URL_ROOT . 'public/img';
const CSS = URL_ROOT . 'public/css';
const AVATAR = URL_ROOT . 'upload/';

//=======================================================

// constante pour simplifier le code
define('IS_POST', $_SERVER['REQUEST_METHOD'] === 'POST');
define('IS_AUTH', isset($_SESSION['email']) || isset($_SESSION['id']));
define('IS_ADMIN', IS_AUTH && !empty($_SESSION['role']) && $_SESSION['role'] === 1);

//=======================================================

// genere un long string random utilise pour la validation de compte
function generateGUID() {
    return bin2hex(random_bytes(16));
}

//=======================================================

// pour version en ligne 

 $dbConfig = [
     "dbHost" => "158.69.48.109",
     "dbName" => "dbdarquest12",
     "dbUser" => "equipe12",
     "dbPass" => "9s6uak23",
     "dbParams" => [
         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_CASE => PDO::CASE_NATURAL,
         PDO::ATTR_ORACLE_NULLS => PDO::NULL_EMPTY_STRING,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
     ],
 ];

// pour version en local

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