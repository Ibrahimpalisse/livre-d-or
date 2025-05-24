<?php
/**
 * Fichier de configuration de l'application
 */

// Charger les variables d'environnement
require_once dirname(__DIR__) . '/core/Env.php';
Core\Env::load();

// Fonction pour récupérer une variable d'environnement avec une valeur par défaut
function env($key, $default = null) {
    return Core\Env::get($key, $default);
}

return [
    // Configuration JWT
    'jwt' => [
        'key' => env('JWT_SECRET', 'default_insecure_key'), // Utiliser la clé du fichier .env
        'algorithm' => env('JWT_ALGORITHM', 'HS256'),
        'expiration' => (int)env('JWT_EXPIRATION', 3600), // 1 heure en secondes
    ],
    
    // Configuration MongoDB
    'mongodb' => [
        'host' => env('MONGODB_HOST', 'mongo'),
        'port' => (int)env('MONGODB_PORT', 27017),
        'database' => env('MONGODB_DATABASE', 'livre_d_or'),
        'username' => env('MONGODB_USERNAME', ''), // Laissez vide si pas d'authentification
        'password' => env('MONGODB_PASSWORD', ''), // Laissez vide si pas d'authentification
    ],
    
    // Autres paramètres de l'application
    'app' => [
        'name' => env('APP_NAME', 'Livre d\'or'),
        'debug' => (bool)env('APP_DEBUG', true), // Mettez à false en production
        'url' => env('APP_URL', 'http://localhost:8080'),
    ]
]; 