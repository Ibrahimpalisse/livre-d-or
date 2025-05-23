<?php
namespace Core;

class Router
{
    private $routes = [];

    public function add($path, $callback)
    {
        $this->routes[$path] = $callback;
    }

    public function dispatch($uri)
    {
        $path = parse_url($uri, PHP_URL_PATH);

        if (isset($this->routes[$path])) {
            // Exécute le callback de la route
            call_user_func($this->routes[$path]);
        } else {
            http_response_code(404);
            $this->render('404', ['title' => 'Page non trouvée']);
        }
    }

    // Méthode pour rendre une vue avec le layout
    public function render($view, $params = [])
    {
        extract($params);

        // Capture le contenu de la vue
        ob_start();
        $viewFile = __DIR__ . "/../views/{$view}.php";
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "Vue {$view} introuvable.";
        }
        $content = ob_get_clean();

        // Affiche le layout principal
        require __DIR__ . '/../views/layout.php';
    }
}