<?php
enum Page
{
    case Home;
    case Connexion;
    case CreationCompte;

    public function text(): string {

        return match($this) {
            
            Page::Home => 'Accueil',
            Page::Connexion => 'Connexion',
            Page::CreationCompte => 'Création de compte'
        };

    }

    public function url(): string {

        return match($this) {
            
            Page::Home => '/',
            Page::Connexion => '/login',
            Page::CreationCompte => '/signup'
        };

    }

}