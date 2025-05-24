<?php
namespace Core;

class Config {
    private static $config = null;
    
    /**
     * Charge le fichier de configuration
     *
     * @return array
     */
    public static function load() {
        if (self::$config === null) {
            $configFile = dirname(__DIR__) . '/config/config.php';
            
            if (file_exists($configFile)) {
                self::$config = require $configFile;
            } else {
                throw new \Exception("Le fichier de configuration n'existe pas: $configFile");
            }
        }
        
        return self::$config;
    }
    
    /**
     * Récupère une valeur de configuration
     *
     * @param string $key La clé de configuration (utiliser le format 'section.clé')
     * @param mixed $default Valeur par défaut si la clé n'existe pas
     * @return mixed
     */
    public static function get($key, $default = null) {
        self::load();
        
        $keys = explode('.', $key);
        $value = self::$config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    /**
     * Vérifie si une clé de configuration existe
     *
     * @param string $key La clé de configuration (utiliser le format 'section.clé')
     * @return bool
     */
    public static function has($key) {
        self::load();
        
        $keys = explode('.', $key);
        $value = self::$config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return false;
            }
            $value = $value[$k];
        }
        
        return true;
    }
} 