<?php
enum Page
{
    case Home;
    case Catalogue;
    case Details;
    case Connexion;
    case Panier;
    case CreationCompte;


    public function text(): string {

        return match($this) {
            
            Page::Home => 'Accueil',
            Page::Catalogue => 'Catalogue',
            Page::Details => 'Détails',
            Page::Connexion => 'Connexion',
            Page::Panier => 'Panier',
            Page::CreationCompte => 'Création de compte'
        };

    }

    public function url(): string {

        return match($this) {
            
            Page::Home => '/',
            Page::Catalogue => '/catalogue',
            Page::Details => '/detail',
            Page::Connexion => '/login',
            Page::Panier => '/panier',
            Page::CreationCompte => '/signup'
        };

    }

}