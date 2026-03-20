<?php
enum Page
{
    case Home;
    case Panier;

    public function text(): string {

        return match($this) {
            
            Page::Home => 'Accueil',
            Page::Panier => 'Panier'
        };

    }

    public function url(): string {

        return match($this) {
            
            Page::Home => '/',
            Page::Panier => '/panier.php'
        };

    }

}