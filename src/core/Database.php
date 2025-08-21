<?php
namespace Core;

class Database {
    private static $instance = null;
    private $client;
    private $db;
    private $mongoHandler;

    private function __construct() {
        // Utiliser le handler MongoDB intelligent
        $this->mongoHandler = DatabaseMongoDB::getInstance();
        
        if (!$this->mongoHandler->isAvailable()) {
            error_log('❌ ERREUR CRITIQUE: MongoDB non disponible sur Railway');
            error_log('🔧 SOLUTION: Railway doit installer l\'extension MongoDB');
            error_log('📋 Extensions disponibles: ' . implode(', ', get_loaded_extensions()));
            throw new \Exception('MongoDB extension non disponible sur Railway');
        }
        
        $this->db = $this->mongoHandler->getDatabase();
        
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
 