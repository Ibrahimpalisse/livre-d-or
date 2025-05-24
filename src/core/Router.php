<?php
namespace Core;

use Models\User;
use Controllers\AuthController;
use Core\Auth;
use Core\Security;

class Router
{
    private $routes = [];
    private $authController;

    public function __construct()
    {
        $this->authController = new AuthController();
        session_start();
    }

    /**
     * Ajoute une route à l'application
     * 
     * @param string $path Chemin URL de la route
     * @param mixed $callback Callback à exécuter quand la route correspond
     * @return void
     */
    public function add($path, $callback)
    {
        $this->routes[$path] = [
            'callback' => $callback,
            'options' => []
        ];
    }

    /**
     * Ajoute une route protégée (nécessitant une authentification)
     * 
     * @param string $path Chemin URL de la route
     * @param mixed $callback Callback à exécuter quand la route correspond
     * @param string $role Rôle requis pour accéder à la route
     * @return void
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
     * Vérifie si une route correspond à l'URL actuelle
     * 
     * @param string $route URL de la route
     * @param string $path URL actuelle
     * @return bool|array Faux si pas de correspondance, sinon un tableau de paramètres
     */
    private function matchRoute($route, $path)
    {
        // Supprimer les trailing slashes
        $route = rtrim($route, '/');
        $path = rtrim($path, '/');
        
        // Vérifier les routes exactes
        if ($route === $path) {
            return [];
        }
        
        // Vérifier les routes avec paramètres dans l'URL
        $routeParts = explode('/', $route);
        $pathParts = explode('/', $path);
        
        if (count($routeParts) !== count($pathParts)) {
            return false;
        }
        
        $params = [];
        for ($i = 0; $i < count($routeParts); $i++) {
            // Si c'est un paramètre {param}
            if (preg_match('/^{([a-z_]+)}$/', $routeParts[$i], $matches)) {
                $params[$matches[1]] = $pathParts[$i];
            } elseif ($routeParts[$i] !== $pathParts[$i]) {
                return false;
            }
        }
        
        return $params;
    }

    /**
     * Exécute le routeur
     * 
     * @return void
     */
    public function run()
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        foreach ($this->routes as $route => $data) {
            if ($params = $this->matchRoute($route, $path)) {
                $callback = $data['callback'];
                $options = $data['options'];

                // Vérifier le token CSRF pour les requêtes POST non-API
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && !strpos($path, '/api/')) {
                    // Si c'est une requête AJAX, le token CSRF pourrait être dans JSON ou form data
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        $requestData = file_get_contents('php://input');
                        $jsonData = json_decode($requestData, true);
                        
                        if (!empty($jsonData) && isset($jsonData['csrf_token'])) {
                            if (!Security::verifyCsrfToken($jsonData['csrf_token'])) {
                                http_response_code(403);
                                echo json_encode(['success' => false, 'message' => 'Token CSRF invalide.']);
                                exit;
                            }
                        } elseif (isset($_POST['csrf_token'])) {
                            if (!Security::verifyCsrfToken($_POST['csrf_token'])) {
                                http_response_code(403);
                                echo json_encode(['success' => false, 'message' => 'Token CSRF invalide.']);
                                exit;
                            }
                        } else {
                            // Pour les requêtes AJAX sans token, on vérifie l'origine
                            // Mais on permet l'opération (laxiste) car on est en phase de migration
                            error_log("Attention: Requête AJAX sans token CSRF: " . $_SERVER['REQUEST_URI']);
                        }
                    }
                    // Pour les formulaires HTML standards, vérifier le token CSRF
                    elseif (!isset($_POST['csrf_token']) || !Security::verifyCsrfToken($_POST['csrf_token'])) {
                        // Si la requête n'est pas encore mise à jour pour CSRF, log et continue
                        // À terme, cette condition devrait être stricte (403)
                        error_log("Attention: Requête POST sans token CSRF: " . $_SERVER['REQUEST_URI']);
                    }
                }

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
                call_user_func_array($callback, $params);
                return;
            }
        }
        
        // Route non trouvée - erreur 404
        http_response_code(404);
        $this->render('error', [
            'title' => 'Page non trouvée',
            'message' => "La page demandée n'existe pas."
        ]);
    }

    /**
     * Rend une vue avec les données fournies
     * 
     * @param string $view Nom de la vue à rendre
     * @param array $data Données à passer à la vue
     * @return void
     */
    public function render($view, $data = [])
    {
        // Extraire les variables pour qu'elles soient disponibles dans la vue
        extract($data);
        
        // Variables du layout
        $is_authenticated = false;
        $is_admin = false;
        $is_super_admin = false;
        $user = null;
        
        // Vérifier si l'utilisateur est connecté
        $currentUser = Auth::getCurrentUser();
        if ($currentUser) {
            $is_authenticated = true;
            $user = $currentUser;
            
            if ($currentUser['role'] === User::ROLE_ADMIN || $currentUser['role'] === User::ROLE_SUPER_ADMIN) {
                $is_admin = true;
            }
            
            if ($currentUser['role'] === User::ROLE_SUPER_ADMIN) {
                $is_super_admin = true;
            }
        }
        
        // Démarrer la temporisation
        ob_start();
        
        // Inclure la vue
        include_once(__DIR__ . '/../views/' . $view . '.php');
        
        // Récupérer le contenu
        $content = ob_get_clean();
        
        // Inclure le layout avec le contenu
        include_once(__DIR__ . '/../views/layout.php');
    }
}