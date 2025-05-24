<?php
namespace Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Models\User;
use Core\Config;

class Auth {
    /**
     * Génère un token JWT
     * 
     * @param array $userData Les données utilisateur à inclure dans le token
     * @return string Le token JWT généré
     */
    public static function generateToken(array $userData): string {
        try {
            $issuedAt = time();
            $expire = $issuedAt + Config::get('jwt.expiration', 3600);

            $payload = [
                'iat' => $issuedAt,    // Heure de création
                'exp' => $expire,      // Heure d'expiration
                'data' => $userData    // Données utilisateur
            ];

            return JWT::encode($payload, Config::get('jwt.key'), Config::get('jwt.algorithm', 'HS256'));
        } catch (\Exception $e) {
            error_log('Erreur lors de la génération du token JWT: ' . $e->getMessage());
            throw $e; // Relancer l'exception pour permettre un traitement au niveau supérieur
        }
    }

    /**
     * Vérifie et décode un token JWT
     * 
     * @param string $token Le token JWT à vérifier
     * @return array|false Les données contenues dans le token, ou false si invalide
     */
    public static function verifyToken(string $token) {
        try {
            $decoded = JWT::decode($token, new Key(Config::get('jwt.key'), Config::get('jwt.algorithm', 'HS256')));
            return (array) $decoded->data;
        } catch (\Exception $e) {
            error_log('Erreur lors de la vérification du token JWT: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Définit un cookie HTTP pour stocker le token JWT
     * 
     * @param string $token Le token JWT
     * @param int $expiration La durée de vie du cookie en secondes
     * @return void
     */
    public static function setTokenCookie(string $token, int $expiration = null): void {
        if ($expiration === null) {
            $expiration = Config::get('jwt.expiration', 3600);
        }
        
        $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $options = [
            'expires' => time() + $expiration,
            'path' => '/',
            'domain' => '',
            'secure' => $isSecure, // Activer uniquement en HTTPS
            'httponly' => true,    // Empêche l'accès via JavaScript
            'samesite' => 'Lax'    // Protection contre CSRF
        ];
        
        if (PHP_VERSION_ID >= 70300) {
            // Pour PHP 7.3.0 et supérieur, nous pouvons utiliser le tableau d'options
            setcookie('jwt_token', $token, $options);
        } else {
            // Pour les versions plus anciennes de PHP
            setcookie(
                'jwt_token', 
                $token, 
                $options['expires'], 
                $options['path'], 
                $options['domain'], 
                $options['secure'], 
                $options['httponly']
            );
        }
    }

    /**
     * Récupère le token JWT du cookie
     * 
     * @return string|null Le token JWT ou null s'il n'existe pas
     */
    public static function getTokenFromCookie(): ?string {
        return $_COOKIE['jwt_token'] ?? null;
    }

    /**
     * Récupère le token JWT de l'en-tête Authorization
     * 
     * @return string|null Le token JWT ou null s'il n'existe pas
     */
    public static function getTokenFromHeader(): ?string {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? '';
        
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Supprime le cookie de token JWT
     * 
     * @return void
     */
    public static function removeTokenCookie(): void {
        $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $options = [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ];
        
        if (PHP_VERSION_ID >= 70300) {
            // Pour PHP 7.3.0 et supérieur
            setcookie('jwt_token', '', $options);
        } else {
            // Pour les versions plus anciennes de PHP
            setcookie(
                'jwt_token', 
                '', 
                $options['expires'], 
                $options['path'], 
                $options['domain'], 
                $options['secure'], 
                $options['httponly']
            );
        }
        
        // Nettoyage des variables globales
        if (isset($_COOKIE['jwt_token'])) {
            unset($_COOKIE['jwt_token']);
        }
        
        // Forcer l'expiration immédiate de la session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /**
     * Obtient l'utilisateur actuel à partir du token
     * 
     * @return array|null Les données de l'utilisateur ou null si non authentifié
     */
    public static function getCurrentUser(): ?array {
        $token = self::getTokenFromCookie() ?? self::getTokenFromHeader();
        
        if (!$token) {
            return null;
        }
        
        $userData = self::verifyToken($token);
        return $userData ?: null;
    }

    /**
     * Vérifie si l'utilisateur actuel a un rôle spécifique
     * 
     * @param string|array $roles Le ou les rôles à vérifier
     * @return bool True si l'utilisateur a le rôle, false sinon
     */
    public static function hasRole($roles): bool {
        $user = self::getCurrentUser();
        
        if (!$user) {
            return false;
        }
        
        if (is_string($roles)) {
            $roles = [$roles];
        }
        
        return in_array($user['role'], $roles);
    }

    /**
     * Vérifie si l'utilisateur actuel est un super admin
     * 
     * @return bool True si l'utilisateur est un super admin, false sinon
     */
    public static function isSuperAdmin(): bool {
        return self::hasRole('super_admin');
    }

    /**
     * Vérifie si l'utilisateur actuel est un admin
     * 
     * @return bool True si l'utilisateur est un admin, false sinon
     */
    public static function isAdmin(): bool {
        return self::hasRole(['admin', 'super_admin']);
    }

    /**
     * Vérifie si l'utilisateur est authentifié
     * 
     * @return bool True si l'utilisateur est authentifié, false sinon
     */
    public static function isAuthenticated(): bool {
        return self::getCurrentUser() !== null;
    }
} 