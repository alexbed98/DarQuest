<?php
enum Page
{
    case Home;
    case Catalogue;
    case Details;
    case Connexion;
    case Panier;
    case Inventaire;
    case CreationCompte;
    case Profil;
    case Enigme;
    case Admin;
    case Email;
    case ResetMDP;



    public function text(): string {

        return match($this) {
            
            Page::Home => 'Accueil',
            Page::Catalogue => 'Accueil',
            Page::Details => 'Détails',
            Page::Connexion => 'Connexion',
            Page::Panier => 'Panier',
            Page::Inventaire => 'Inventaire',
            Page::CreationCompte => 'Création de compte',
            Page::Admin => 'Administration',
            Page::Profil => 'Profil',
            Page::Enigme => 'Enigme',
            Page::Email => '',
            Page::ResetMDP => ''
        };

    }

    public function url(): string {

        $base = defined('URL_ROOT') ? URL_ROOT : '/';

        return match($this) {
            
            Page::Home => $base . 'index.php',
            Page::Catalogue => $base . 'catalogue.php',
            Page::Details => $base . 'detail.php',
            Page::Connexion => $base . 'login.php',
            Page::Panier => $base . 'panier.php',
            Page::Inventaire => $base . 'inventaire.php',
            Page::CreationCompte => $base . 'signup.php',
            Page::Admin => $base . 'admin.php',
            Page::Profil => $base . 'profil.php',
            Page::Enigme => $base . 'enigme.php',
            Page::Email => $base . 'email.php',
            Page::ResetMDP => $base . 'mdp.php'
        };

    }

}