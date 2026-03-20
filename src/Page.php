<?php
enum Page
{
    case Home;
    case Catalogue;

    public function text(): string {

        return match($this) {
            
            Page::Home => 'Accueil',
            Page::Catalogue => 'Catalogue'
        };

    }

    public function url(): string {

        return match($this) {
            
            Page::Home => '/',
            Page::Catalogue => '/catalogue'
        };

    }

}