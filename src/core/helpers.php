<?php
/**
 * Fichier contenant les fonctions d'aide globales
 */

// Fonction pour récupérer une variable d'environnement avec une valeur par défaut
if (!function_exists('env')) {
    function env($key, $default = null) {
        return Core\Env::get($key, $default);
    }
} 