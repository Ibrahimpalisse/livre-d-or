<?php
namespace Core;

use MongoDB\Client;

class Database {
    private static $instance = null;
    private $client;
    private $db;

    private function __construct() {
        // Charger la configuration
        $config = include dirname(__DIR__) . '/config/config.php';
        $mongoConfig = $config['mongodb'];
        
        // Vérifier d'abord si MONGO_URI est défini dans l'environnement
        $mongoUri = getenv('MONGO_URI');
        
        // Si MONGO_URI n'est pas défini, construire l'URI à partir des paramètres de configuration
        if (!$mongoUri) {
            $auth = '';
            if (!empty($mongoConfig['username']) && !empty($mongoConfig['password'])) {
                $auth = urlencode($mongoConfig['username']) . ':' . urlencode($mongoConfig['password']) . '@';
            }
            
            $mongoUri = "mongodb://{$auth}{$mongoConfig['host']}:{$mongoConfig['port']}";
        }
        
        try {
            $options = [
                'connectTimeoutMS' => 3000,
                'serverSelectionTimeoutMS' => 5000
            ];
            
            $this->client = new Client($mongoUri, $options);
            $this->db = $this->client->{$mongoConfig['database']};
            
            // Test de connexion
            $this->db->command(['ping' => 1]);
        } catch (\Exception $e) {
            error_log('Erreur de connexion à MongoDB: ' . $e->getMessage());
            die('Erreur de connexion à la base de données. Consultez les logs pour plus d\'informations.');
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getCollection($collection) {
        return $this->db->$collection;
    }
} 
 