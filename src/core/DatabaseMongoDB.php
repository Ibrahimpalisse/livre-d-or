<?php
namespace Core;

/**
 * Classe pour gérer MongoDB avec fallback intelligent
 */
class DatabaseMongoDB {
    private static $instance = null;
    private $connection = null;
    private $isAvailable = false;
    
    private function __construct() {
        $this->checkMongoDBSupport();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function checkMongoDBSupport() {
        // Vérifier si l'extension MongoDB est disponible
        if (!extension_loaded('mongodb')) {
            error_log('ERREUR: Extension MongoDB non disponible sur Railway');
            $this->isAvailable = false;
            return;
        }
        
        // Vérifier si les classes MongoDB existent
        if (!class_exists('MongoDB\Client')) {
            error_log('ERREUR: Classes MongoDB non disponibles');
            $this->isAvailable = false;
            return;
        }
        
        try {
            // Prioriser les variables Railway si disponibles
            $mongoUri = $_ENV['MONGO_URL'] ?? $_ENV['MONGO_URI'] ?? '';
            
            // Si pas d'URI complet, construire avec les variables Railway
            if (empty($mongoUri) && isset($_ENV['MONGOHOST'])) {
                $host = $_ENV['MONGOHOST'];
                $port = $_ENV['MONGOPORT'] ?? '27017';
                $user = $_ENV['MONGOUSER'] ?? 'mongo';
                $pass = $_ENV['MONGOPASSWORD'] ?? '';
                $database = $_ENV['MONGODB_DATABASE'] ?? 'railway';
                
                $mongoUri = "mongodb://$user:$pass@$host:$port/$database";
                error_log("🔧 URI MongoDB Railway construite: mongodb://$user:***@$host:$port/$database");
            }
            
            if (empty($mongoUri)) {
                throw new \Exception('Aucune URI MongoDB disponible (ni MONGO_URL ni variables Railway)');
            }
            
            $this->connection = new \MongoDB\Client($mongoUri, [
                'connectTimeoutMS' => 5000,
                'serverSelectionTimeoutMS' => 5000
            ]);
            
            // Test de ping
            $this->connection->selectDatabase('admin')->command(['ping' => 1]);
            $this->isAvailable = true;
            error_log('✅ MongoDB disponible et connecté');
            
        } catch (\Exception $e) {
            error_log('❌ MongoDB indisponible: ' . $e->getMessage());
            $this->isAvailable = false;
        }
    }
    
    public function isAvailable(): bool {
        return $this->isAvailable;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function getDatabase() {
        if (!$this->isAvailable) {
            return null;
        }
        
        $dbName = $_ENV['MONGODB_DATABASE'] ?? 'livre_d_or';
        return $this->connection->selectDatabase($dbName);
    }
}