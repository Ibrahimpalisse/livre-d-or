<?php
// Démarrer la session
session_start();

require 'vendor/autoload.php';

use Core\Router;
use Core\Auth;
use Controllers\AuthController;
use Models\User;

$router = new Router();

// Exemple de route pour la page d'accueil
$router->add('/', function() use ($router) {
    $router->render('home', [
        'title' => 'Accueil',
        // tu peux passer d'autres variables à la vue ici
    ]);
});

// Route pour la page d'accueil, accessible à tous
$router->add('/home', function() use ($router) {
    $router->render('home', [
        'title' => 'Accueil',
    ]);
});

// Route pour la page d'inscription, accessible aux visiteurs
$router->add('/register', function() use ($router) {
    // Si l'utilisateur est déjà connecté, rediriger vers la page appropriée
    $user = Auth::getCurrentUser();
    if ($user) {
        if ($user['role'] === User::ROLE_SUPER_ADMIN || $user['role'] === User::ROLE_ADMIN) {
            header('Location: /dashboard');
        } else {
            header('Location: /home');
        }
        exit;
    }

    $authController = new AuthController();
    $response = [];
    
    // Si la requête est POST, traiter l'inscription
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $response = $authController->register();
        
        // Si l'inscription est réussie et ce n'est pas une requête AJAX, rediriger vers la page de connexion
        if ($response['success'] && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Location: /login');
            exit;
        }
    }
    
    $router->render('register', [
        'title' => 'Inscription',
        'response' => $response
    ]);
});

// Route pour la page de connexion, accessible aux visiteurs
$router->add('/login', function() use ($router) {
    // Si l'utilisateur est déjà connecté, rediriger vers la page appropriée
    $user = Auth::getCurrentUser();
    if ($user) {
        if ($user['role'] === User::ROLE_SUPER_ADMIN || $user['role'] === User::ROLE_ADMIN) {
            header('Location: /dashboard');
        } else {
            header('Location: /home');
        }
        exit;
    }

    $authController = new AuthController();
    $response = [];
    
    // Si la requête est POST, traiter la connexion
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $response = $authController->login();
        
        // Si la connexion est réussie et ce n'est pas une requête AJAX, rediriger vers la page appropriée
        if ($response['success'] && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            if ($response['user']['role'] === User::ROLE_SUPER_ADMIN || $response['user']['role'] === User::ROLE_ADMIN) {
                header('Location: /dashboard');
            } else {
                header('Location: /home');
            }
            exit;
        }
    }
    
    $router->render('login', [
        'title' => 'Connexion',
        'response' => $response
    ]);
});

// Route pour la déconnexion, accessible à tous les utilisateurs authentifiés
$router->add('/logout', function() use ($router) {
    $authController = new AuthController();
    $authController->logout();
});

// Route pour le tableau de bord, accessible uniquement aux admins et super admins
$router->addProtected('/dashboard', function() use ($router) {
    // Vérification supplémentaire de sécurité - même si la protection de route devrait fonctionner
    $currentUser = Auth::getCurrentUser();
    if ($currentUser['role'] === User::ROLE_USER) {
        header('Location: /home');
        exit;
    }
    
    // Charger la liste des utilisateurs uniquement pour les super admins
    $users = [];
    
    if ($currentUser && $currentUser['role'] === User::ROLE_SUPER_ADMIN) {
        $userModel = new User();
        $users = $userModel->findAll();
    }
    
    $router->render('dashboard', [
        'title' => 'Tableau de bord',
        'users' => $users
    ]);
}, User::ROLE_ADMIN);

// Route pour la gestion des utilisateurs, accessible uniquement aux super admins
// $router->addProtected('/users', function() use ($router) {
//     $userModel = new User();
//     $users = $userModel->findAll();
//     
//     $router->render('users', [
//         'title' => 'Gestion des utilisateurs',
//         'users' => $users
//     ]);
// }, User::ROLE_SUPER_ADMIN);

// Route API pour mettre à jour le rôle d'un utilisateur (accessible uniquement aux super admin)
// $router->addProtected('/api/update-role', function() {
//     $apiController = new Controllers\ApiController();
//     $apiController->updateRole();
// }, User::ROLE_SUPER_ADMIN);

// Ajoute d'autres routes ici...

$router->dispatch($_SERVER['REQUEST_URI']);