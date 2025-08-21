<?php
namespace Core;

class Database {
    private static $instance = null;
    private $client;
    private $db;
    private $mongoHandler;
    private $httpHandler;
    private $isUsingHTTP = false;

    private function __construct() {
        // Tenter d'abord le MongoDB natif
        $this->mongoHandler = DatabaseMongoDB::getInstance();
        
        if (!$this->mongoHandler->isAvailable()) {
            error_log('⚠️ MongoDB extension non disponible sur Railway');
            error_log('🔄 Fallback: Utilisation de l\'API HTTP MongoDB Atlas');
            error_log('📋 Extensions PHP disponibles: ' . implode(', ', get_loaded_extensions()));
            
            // Utiliser le fallback HTTP
            $this->httpHandler = DatabaseHTTP::getInstance();
            $this->isUsingHTTP = true;
            error_log('✅ Fallback HTTP MongoDB Atlas activé');
            return;
        }
        
        $this->db = $this->mongoHandler->getDatabase();
        $this->isUsingHTTP = false;
        
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
        if ($this->isUsingHTTP) {
            return $this->httpHandler->getCollection($collection);
        }
        return $this->db->$collection;
    }
} 
 