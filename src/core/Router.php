<?php
namespace Core;

use Models\User;
use Controllers\AuthController;

class Router
{
    private $routes = [];
    private $authController;

    public function __construct()
    {
        $this->authController = new AuthController();
    }

    /**
     * Ajoute une route
     */
    public function add($path, $callback, $options = [])
    {
        $this->routes[$path] = [
            'callback' => $callback,
            'options' => $options
        ];
    }

    /**
     * Ajoute une route protégée par authentification
     */
    public function addProtected($path, $callback, $role = User::ROLE_USER)
    {
        $this->routes[$path] = [
            'callback' => $callback,
            'options' => [
                'protected' => true,
                'role' => $role
            ]
        ];
    }

    /**
     * Rend une vue avec des données
     */
    public function render($view, $data = [])
    {
        // Ajout des informations utilisateur aux données de vue
        $user = Auth::getCurrentUser();
        if ($user) {
            $data['user'] = $user;
            $data['is_authenticated'] = true;
            $data['is_admin'] = $user['role'] === User::ROLE_ADMIN || $user['role'] === User::ROLE_SUPER_ADMIN;
            $data['is_super_admin'] = $user['role'] === User::ROLE_SUPER_ADMIN;
        } else {
            $data['is_authenticated'] = false;
            $data['is_admin'] = false;
            $data['is_super_admin'] = false;
        }

        // Extrait les données pour les rendre accessibles dans la vue
        extract($data);

        // Démarre la mise en cache de sortie
        ob_start();
        
        // Inclut la vue
        include "src/views/{$view}.php";
        
        // Récupère le contenu mis en cache
        $content = ob_get_clean();
        
        // Inclut le layout principal avec le contenu
        include 'src/views/layout.php';
    }

    /**
     * Traite une requête HTTP
     */
    public function dispatch($uri)
    {
        // Supprimer les paramètres de requête
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Normaliser l'URI
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }

        // Vérifier si la route existe
        if (isset($this->routes[$uri])) {
            $route = $this->routes[$uri];
            $callback = $route['callback'];
            $options = $route['options'];

            // Vérifier si la route est protégée
            if (isset($options['protected']) && $options['protected']) {
                $requiredRole = $options['role'] ?? User::ROLE_USER;
                $access = $this->authController->checkAccess($requiredRole);

                if (!$access['authenticated']) {
                    // Rediriger vers la page de connexion
                    header('Location: /login');
                    exit;
                }

                if (!$access['authorized']) {
                    // Accès refusé 
                    // Ajouter un message de débogage pour comprendre pourquoi l'accès est refusé
                    $user = Auth::getCurrentUser();
                    $userRole = $user ? $user['role'] : 'non connecté';
                    $requiredRoleStr = $requiredRole;

                    error_log("Accès refusé - Rôle utilisateur: {$userRole}, Rôle requis: {$requiredRoleStr}");
                    
                    if ($user && $user['role'] === User::ROLE_USER) {
                        // Rediriger les utilisateurs standards vers la page d'accueil
                        header('Location: /home');
                        exit;
                    } else {
                        // Pour d'autres cas (rare), afficher une erreur 403
                        http_response_code(403);
                        $this->render('error', [
                            'title' => 'Accès refusé',
                            'message' => "Vous n'avez pas les permissions nécessaires pour accéder à cette page. Votre rôle : {$userRole}, Rôle requis : {$requiredRoleStr}"
                        ]);
                        exit;
                    }
                }
            }

            // Exécuter le callback de la route
            call_user_func($callback);
        } else {
            // Route non trouvée - Erreur 404
            http_response_code(404);
            $this->render('error', [
                'title' => 'Page non trouvée',
                'message' => 'La page demandée n\'existe pas.'
            ]);
        }
    }
}