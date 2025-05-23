<?php
require 'vendor/autoload.php';

use Core\Router;

$router = new Router();

// Exemple de route pour la page d'accueil
$router->add('/', function() use ($router) {
    $router->render('home', [
        'title' => 'Accueil',
        // tu peux passer d'autres variables à la vue ici
    ]);
});

$router->add('/register', function() use ($router) {
    $router->render('register', [
        'title' => 'Inscription',
    ]);
});

$router->add('/login', function() use ($router) {
    $router->render('login', [
        'title' => 'Connexion',
    ]);
});
$router->add('/home', function() use ($router) {
    $router->render('home', [
        'title' => 'Accueil',
    ]);
});

$router->add('/dashboard', function() use ($router) {
    $router->render('dashboard', [
        'title' => 'Dashboard',
    ]);
});

// Ajoute d'autres routes ici...

$router->dispatch($_SERVER['REQUEST_URI']);