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


    public function text(): string {

        return match($this) {
            
            Page::Home => 'Accueil',
            Page::Catalogue => 'Catalogue',
            Page::Details => 'Détails',
            Page::Connexion => 'Connexion',
            Page::Panier => 'Panier',
            Page::Inventaire => 'Inventaire',
            Page::CreationCompte => 'Création de compte',
            Page::Admin => 'Administration'
            Page::Profil => 'Profil',
            Page::Enigme => 'Enigme',
        };

    }

    public function url(): string {

        return match($this) {
            
            Page::Home => '/',
            Page::Catalogue => '/catalogue',
            Page::Details => '/detail',
            Page::Connexion => '/login',
            Page::Panier => '/panier',
            Page::Inventaire => '/inventaire',
            Page::CreationCompte => '/signup',
            Page::Admin => '/admin',
            Page::Profil => '/profil',
            Page::Enigme => '/enigme',
        };

    }

}