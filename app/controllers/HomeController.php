<?php

class HomeController
{
    /**
     * Viser forsiden
     * Indlæser home.php view-filen
     */
    public function index()
    {
        require_once __DIR__ . '/../views/home.php';
    }
}
