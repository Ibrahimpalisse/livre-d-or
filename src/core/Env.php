<?php
namespace Core;

class Env {
    private static $variables = [];
    private static $loaded = false;

    /**
     * Charge les variables d'environnement depuis le fichier .env
     * 
     * @return void
     */
    public static function load() {
        if (self::$loaded) {
            return;
        }

        // Charger depuis le fichier .env s'il existe
        $envFile = dirname(dirname(__DIR__)) . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                // Ignorer les commentaires
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }

                // Parser les variables d'environnement
                if (strpos($line, '=') !== false) {
                    list($name, $value) = explode('=', $line, 2);
                    $name = trim($name);
                    $value = trim($value);
                    
                    // Enlever les guillemets si présents
                    if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                        $value = substr($value, 1, -1);
                    } elseif (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1) {
                        $value = substr($value, 1, -1);
                    }
                    
                    self::$variables[$name] = $value;
                    
                    // Également définir comme variable d'environnement réelle
                    if (!isset($_ENV[$name]) && !isset($_SERVER[$name])) {
                        $_ENV[$name] = $value;
                    }
                }
            }
        }

        // Aussi prendre en compte les variables d'environnement réelles (ex: Docker)
        foreach ($_ENV as $key => $value) {
            self::$variables[$key] = $value;
        }
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') !== 0) { // Exclure les en-têtes HTTP
                self::$variables[$key] = $value;
            }
        }

        self::$loaded = true;
    }

    /**
     * Récupère une variable d'environnement
     * 
     * @param string $key La clé de la variable
     * @param mixed $default Valeur par défaut si la variable n'existe pas
     * @return mixed
     */
    public static function get($key, $default = null) {
        self::load();
        return isset(self::$variables[$key]) ? self::$variables[$key] : $default;
    }

    /**
     * Définit une variable d'environnement
     * 
     * @param string $key La clé de la variable
     * @param mixed $value La valeur
     * @return void
     */
    public static function set($key, $value) {
        self::load();
        self::$variables[$key] = $value;
    }
} 