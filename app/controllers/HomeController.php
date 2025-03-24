<?php

class HomeController
{
    public function index()
    {
        session_start();
        require_once __DIR__ . '/../views/home.php';

    }
}
