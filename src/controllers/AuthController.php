<?php
namespace Controllers;

use Models\User;
use Core\Auth;

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Gère l'inscription d'un utilisateur
     */
    public function register() {
        $response = [
            'success' => false,
            'message' => '',
            'errors' => []
        ];

        // Vérifier si la requête est POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $password_confirm = trim($_POST['password_confirm'] ?? '');
            
            // Validation des données
            $errors = $this->validateRegistrationData($username, $password, $password_confirm);
            
            if (empty($errors)) {
                // Vérifier si le nom d'utilisateur existe déjà
                if ($this->userModel->usernameExists($username)) {
                    $response['errors']['username'] = 'Ce nom d\'utilisateur est déjà pris.';
                } else {
                    // Créer le nouvel utilisateur
                    $user = $this->userModel->create($username, $password);
                    
                    if ($user) {
                        // Création réussie - ne pas démarrer de session, juste informer du succès
                        $response['success'] = true;
                        $response['message'] = 'Inscription réussie! Vous pouvez maintenant vous connecter.';
                        $response['redirect'] = '/login';
                        $response['user'] = [
                            'username' => $user['username'],
                            'role' => $user['role']
                        ];
                    } else {
                        $response['message'] = 'Une erreur est survenue lors de l\'inscription.';
                    }
                }
            } else {
                $response['errors'] = $errors;
            }
            
            // Toujours renvoyer un JSON pour les requêtes POST sur la route d'inscription
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        return $response;
    }

    /**
     * Valide les données d'inscription
     */
    private function validateRegistrationData($username, $password, $password_confirm) {
        $errors = [];
        
        // Regex pour le nom d'utilisateur (lettres, chiffres, _, au moins 3 caractères)
        $usernamePattern = '/^[a-zA-Z0-9_]{3,20}$/';
        
        // Regex pour le mot de passe (au moins 8 caractères, une majuscule, une minuscule, un chiffre)
        $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/';
        
        // Validation du nom d'utilisateur
        if (empty($username)) {
            $errors['username'] = 'Le nom d\'utilisateur est requis.';
        } elseif (!preg_match($usernamePattern, $username)) {
            $errors['username'] = 'Le nom d\'utilisateur doit contenir entre 3 et 20 caractères alphanumériques.';
        }
        
        // Validation du mot de passe
        if (empty($password)) {
            $errors['password'] = 'Le mot de passe est requis.';
        } elseif (!preg_match($passwordPattern, $password)) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères, dont une majuscule, une minuscule et un chiffre.';
        }
        
        // Validation de la confirmation du mot de passe
        if ($password !== $password_confirm) {
            $errors['password_confirm'] = 'Les mots de passe ne correspondent pas.';
        }
        
        return $errors;
    }

    /**
     * Gère la connexion d'un utilisateur
     */
    public function login() {
        $response = [
            'success' => false,
            'message' => '',
            'errors' => []
        ];

        // Vérifier si la requête est POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            
            if (empty($username)) {
                $response['errors']['username'] = 'Le nom d\'utilisateur est requis.';
            }
            
            if (empty($password)) {
                $response['errors']['password'] = 'Le mot de passe est requis.';
            }
            
            if (empty($response['errors'])) {
                $user = $this->userModel->authenticate($username, $password);
                
                if ($user) {
                    try {
                        // Authentification réussie, générer un token JWT
                        $token = Auth::generateToken($user);
                        Auth::setTokenCookie($token);
                        
                        $response['success'] = true;
                        $response['message'] = 'Connexion réussie!';
                        
                        // Rediriger en fonction du rôle
                        if ($user['role'] === User::ROLE_SUPER_ADMIN || $user['role'] === User::ROLE_ADMIN) {
                            $response['redirect'] = '/dashboard';
                        } else {
                            $response['redirect'] = '/home';
                        }
                        
                        $response['token'] = $token;
                        $response['user'] = [
                            'username' => $user['username'],
                            'role' => $user['role']
                        ];
                    } catch (\Exception $e) {
                        error_log('Erreur JWT: ' . $e->getMessage());
                        $response['message'] = 'Une erreur est survenue lors de la connexion. Veuillez réessayer.';
                    }
                } else {
                    // Message générique pour des raisons de sécurité
                    $response['message'] = 'Identifiants invalides. Veuillez vérifier votre nom d\'utilisateur et mot de passe.';
                    // Ne pas préciser quel champ est incorrect pour éviter les attaques par énumération
                }
            }
            
            // Toujours renvoyer un JSON pour les requêtes POST sur la route de login
            // Cette solution simple réglera le problème
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        return $response;
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout() {
        // Log de débogage pour voir quel utilisateur est déconnecté
        $user = Auth::getCurrentUser();
        
        // Supprimer le cookie JWT
        Auth::removeTokenCookie();
        
        // Détruire complètement la session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
            session_write_close();
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
                session_regenerate_id(true);
            }
        }
        
        // Supprimer manuellement le cookie JWT côté client
        setcookie('jwt_token', '', time() - 3600, '/', '', false, true);
        unset($_COOKIE['jwt_token']);
        
        // Empêcher la mise en cache de la redirection pour forcer un rafraîchissement complet
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Forcer le navigateur à vider son cache en ajoutant un paramètre aléatoire
        $cacheBuster = '?logout=' . time() . rand(1000, 9999);
        
        // Rediriger vers la page de connexion
        header('Location: /login' . $cacheBuster);
        exit;
    }
    
    /**
     * Vérifie l'accès en fonction du rôle
     */
    public function checkAccess($requiredRole = null) {
        $user = Auth::getCurrentUser();
        
        if (!$user) {
            return [
                'authenticated' => false,
                'authorized' => false,
                'message' => 'Non authentifié'
            ];
        }
        
        if ($requiredRole) {
            $authorized = false;
            $userRole = $user['role'];
            
            // Ajouter un log pour comprendre les rôles
            error_log("CheckAccess: Rôle utilisateur={$userRole}, Rôle requis={$requiredRole}");
            
            switch ($requiredRole) {
                case User::ROLE_SUPER_ADMIN:
                    // Seulement les super_admin peuvent accéder
                    $authorized = ($userRole === User::ROLE_SUPER_ADMIN);
                    break;
                    
                case User::ROLE_ADMIN:
                    // Les admin ET super_admin peuvent accéder
                    $authorized = ($userRole === User::ROLE_ADMIN || $userRole === User::ROLE_SUPER_ADMIN);
                    break;
                    
                case User::ROLE_USER:
                    // Tous les utilisateurs authentifiés peuvent accéder
                    $authorized = true;
                    break;
                    
                default:
                    // Si le rôle requis n'est pas reconnu, refuser l'accès
                    $authorized = false;
            }
            
            // Ajouter un autre log pour voir la décision d'autorisation
            error_log("CheckAccess: Autorisation={$authorized} pour {$userRole} demandant accès à {$requiredRole}");
            
            return [
                'authenticated' => true,
                'authorized' => $authorized,
                'user' => $user,
                'message' => $authorized ? 'Accès autorisé' : "Accès refusé: votre rôle ({$userRole}) n'est pas suffisant pour cette ressource ({$requiredRole})"
            ];
        }
        
        return [
            'authenticated' => true,
            'authorized' => true,
            'user' => $user,
            'message' => 'Authentifié'
        ];
    }
} 