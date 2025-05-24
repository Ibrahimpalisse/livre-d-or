<?php
namespace Core;

class Security {
    /**
     * Génère un token CSRF et le stocke en session
     * 
     * @return string Le token CSRF généré
     */
    public static function generateCsrfToken() {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Vérifie si le token CSRF est valide
     * 
     * @param string $token Le token à vérifier
     * @return bool True si le token est valide, false sinon
     */
    public static function verifyCsrfToken($token) {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        // Comparaison en temps constant pour éviter les attaques timing
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Génère le HTML du champ CSRF à inclure dans les formulaires
     * 
     * @return string HTML du champ caché contenant le token CSRF
     */
    public static function csrfField() {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Vérifie le token CSRF et renvoie une erreur 403 si invalide
     * 
     * @param string $token Le token à vérifier
     * @return void
     */
    public static function validateCsrfToken($token) {
        if (!self::verifyCsrfToken($token)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur de sécurité: Token CSRF invalide.'
            ]);
            exit;
        }
    }
} 
 