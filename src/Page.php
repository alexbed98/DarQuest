<?php
enum Page
{
    case Home;

    public function text(): string {

        return match($this) {
            
            Page::Home => 'Accueil'
        };

    }

    public function url(): string {

        return match($this) {
            
            Page::Home => '/'
        };

    }

}