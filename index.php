<?php
// Démarrer la session
session_start();

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Journalisation
error_log("Début de l'exécution de index.php - REQUEST_URI: " . $_SERVER['REQUEST_URI']);

// Vérifier si mod_rewrite fonctionne correctement
if (!isset($_SERVER['REDIRECT_URL']) && $_SERVER['REQUEST_URI'] != '/' && $_SERVER['REQUEST_URI'] != '/index.php') {
    error_log("ATTENTION: mod_rewrite ne semble pas fonctionner! REQUEST_URI: " . $_SERVER['REQUEST_URI']);
}

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

// Route API pour que le super admin modifie le rôle d'un utilisateur
$router->addProtected('/superadmin/update-role', function() {
    $controller = new Controllers\SuperAdminController();
    $controller->updateRole();
}, Models\User::ROLE_SUPER_ADMIN);

// Route pour la création d'une publication (admin ou super_admin)
$router->addProtected('/publication/create', function() {
    $controller = new Controllers\PublicationController();
    $response = $controller->create($_POST, $_FILES, Auth::getCurrentUser());
    
    // Retourner une réponse JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}, Models\User::ROLE_ADMIN);

// Route pour lister les publications (accessible aux admin et super_admin au tableau de bord)
$router->addProtected('/publication/list', function() {
    $controller = new Controllers\PublicationController();
    $result = $controller->getAll($_GET);
    
    header('Content-Type: application/json');
    echo json_encode($result);
}, Models\User::ROLE_ADMIN);

// Route publique pour lister les publications (pour la page d'accueil)
$router->add('/publication/list', function() {
    $controller = new Controllers\PublicationController();
    $result = $controller->getAll($_GET);
    
    header('Content-Type: application/json');
    echo json_encode($result);
});

// Route pour récupérer une publication par son ID
$router->addProtected('/publication/get', function() {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID de publication manquant']);
        return;
    }
    $controller = new Controllers\PublicationController();
    $publication = $controller->getById($id);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $publication !== null,
        'publication' => $publication,
        'message' => $publication ? null : 'Publication non trouvée'
    ]);
}, Models\User::ROLE_ADMIN);

// Route pour mettre à jour une publication
$router->addProtected('/publication/update', function() {
    $id = $_POST['id'] ?? $_GET['id'] ?? null;
    if (!$id) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID de publication manquant']);
        return;
    }
    $controller = new Controllers\PublicationController();
    $response = $controller->update($id, $_POST, $_FILES, Core\Auth::getCurrentUser());
    header('Content-Type: application/json');
    echo json_encode($response);
}, Models\User::ROLE_ADMIN);

// Route pour supprimer une publication
$router->addProtected('/publication/delete', function() {
    $id = $_POST['id'] ?? $_GET['id'] ?? null;
    if (!$id) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID de publication manquant']);
        return;
    }
    $controller = new Controllers\PublicationController();
    $response = $controller->delete($id, Core\Auth::getCurrentUser());
    header('Content-Type: application/json');
    echo json_encode($response);
}, Models\User::ROLE_ADMIN);

// Route pour afficher la page d'édition d'une publication
$router->addProtected('/dashboard/edit-publication/{id}', function() use ($router) {
    $id = explode('/', $_SERVER['REQUEST_URI'])[3] ?? null;
    
    if (!$id) {
        header('Location: /dashboard');
        exit;
    }
    
    $controller = new Controllers\PublicationController();
    $publication = $controller->getById($id);
    
    if (!$publication) {
        header('Location: /dashboard');
        exit;
    }
    
    $router->render('edit-publication', [
        'title' => 'Modifier une publication',
        'publication' => $publication
    ]);
}, Models\User::ROLE_ADMIN);

// Route pour récupérer les commentaires d'une publication
$router->add('/comments/publication/{id}', function() {
    $id = explode('/', $_SERVER['REQUEST_URI'])[3] ?? null;
    
    $controller = new Controllers\CommentController();
    $result = $controller->getCommentsByPublication($id);
    
    header('Content-Type: application/json');
    echo json_encode($result);
});

// Nouvelle route alternative pour récupérer les commentaires par GET query parameter
$router->add('/comments/by-publication', function() {
    $id = $_GET['id'] ?? null;
    
    $controller = new Controllers\CommentController();
    $result = $controller->getCommentsByPublication($id);
    
    header('Content-Type: application/json');
    echo json_encode($result);
});

// Route pour ajouter un commentaire
$router->addProtected('/comments/add', function() {
    $data = json_decode(file_get_contents('php://input'), true);
    $publicationId = $data['publication_id'] ?? null;
    $content = $data['content'] ?? null;
    
    $controller = new Controllers\CommentController();
    $result = $controller->addComment($publicationId, $content, Auth::getCurrentUser());
    
    header('Content-Type: application/json');
    echo json_encode($result);
});

// Route pour supprimer un commentaire (utilisateur connecté uniquement)
$router->addProtected('/comments/delete/{id}', function() {
    $id = explode('/', $_SERVER['REQUEST_URI'])[3] ?? null;
    
    $controller = new Controllers\CommentController();
    $result = $controller->deleteComment($id, Core\Auth::getCurrentUser());
    
    header('Content-Type: application/json');
    echo json_encode($result);
}, Models\User::ROLE_USER);

// Route pour ajouter une réaction (like/dislike) à un commentaire - nouvelle approche sans paramètre dans l'URL
$router->addProtected('/comments/react', function() {
    // Récupérer les données JSON envoyées
    $data = json_decode(file_get_contents('php://input'), true);
    $commentId = $data['commentId'] ?? null;
    $isLike = isset($data['isLike']) ? (bool)$data['isLike'] : true;
    
    // Vérifier que l'ID est présent
    if (!$commentId) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'ID de commentaire manquant'
        ]);
        return;
    }
    
    // Log pour le débogage
    error_log("Traitement de la réaction pour le commentaire ID: " . $commentId);
    
    // Récupérer l'utilisateur connecté
    $user = Auth::getCurrentUser();
    
    // Appeler le contrôleur
    $controller = new Controllers\CommentController();
    $result = $controller->addReaction($commentId, $isLike, $user);
    
    // Récupérer les compteurs mis à jour
    if ($result['success']) {
        $comment = (new Models\Comment())->findById($commentId);
        if ($comment) {
            $result['likes'] = $comment['likes'];
            $result['dislikes'] = $comment['dislikes'];
        }
    }
    
    // Toujours retourner du JSON
    header('Content-Type: application/json');
    echo json_encode($result);
});

// Route pour valider/invalider une publication
$router->addProtected('/publication/validate', function() {
    // Récupérer les données JSON envoyées
    $data = json_decode(file_get_contents('php://input'), true);
    $publicationId = $data['publication_id'] ?? null;
    $isValid = isset($data['isValid']) ? (bool)$data['isValid'] : null;
    
    // Vérifier que les champs obligatoires sont présents
    if (!$publicationId || $isValid === null) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'ID de publication ou statut de validation manquant'
        ]);
        return;
    }
    
    // Log pour le débogage
    error_log("Traitement de la validation pour la publication ID: " . $publicationId . ", isValid: " . ($isValid ? 'true' : 'false'));
    
    // Récupérer l'utilisateur connecté
    $user = Auth::getCurrentUser();
    
    // Appeler le contrôleur
    $controller = new Controllers\PublicationController();
    $result = $controller->validatePublication($publicationId, $isValid, $user);
    
    // Toujours retourner du JSON
    header('Content-Type: application/json');
    echo json_encode($result);
});

// Route pour valider/invalider une publication via le CommentController
$router->addProtected('/comments/validate-publication', function() {
    // Récupérer les données JSON envoyées
    $data = json_decode(file_get_contents('php://input'), true);
    $publicationId = $data['publication_id'] ?? null;
    $isValid = isset($data['isValid']) ? (bool)$data['isValid'] : null;
    
    // Vérifier que les champs obligatoires sont présents
    if (!$publicationId || $isValid === null) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'ID de publication ou statut de validation manquant'
        ]);
        return;
    }
    
    // Log pour le débogage
    error_log("Traitement de la validation (via CommentController) pour la publication ID: " . $publicationId . ", isValid: " . ($isValid ? 'true' : 'false'));
    
    // Récupérer l'utilisateur connecté
    $user = Auth::getCurrentUser();
    
    // Appeler le contrôleur
    $controller = new Controllers\CommentController();
    $result = $controller->validatePublication($publicationId, $isValid, $user);
    
    // Toujours retourner du JSON
    header('Content-Type: application/json');
    echo json_encode($result);
});

// Route pour récupérer les statistiques de validation d'une publication
$router->add('/publication/stats', function() {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'ID de publication manquant'
        ]);
        return;
    }
    
    $stats = (new Models\Publication())->getValidationStats($id);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
});

$router->run();