<?php
enum Page
{
    case Home;
    case Details;

    public function text(): string {

        return match($this) {
            
            Page::Home => 'Accueil',
            Page::Details => 'Détails'
        };

    }

    public function url(): string {

        return match($this) {
            
            Page::Home => '/',
            Page::Details => '/detail.php'
        };

    }

}