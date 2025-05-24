<?php
namespace Core;

use MongoDB\Client;

class Database {
    private static $instance = null;
    private $client;
    private $db;

    private function __construct() {
        // Récupérer l'URI MongoDB depuis la variable d'environnement ou utiliser une valeur par défaut
        $mongoUri = getenv('MONGO_URI') ?: 'mongodb://mongo:27017';
        
        try {
            $this->client = new Client($mongoUri);
            $this->db = $this->client->livre_d_or; // Nom de la base de données
        } catch (\Exception $e) {
            die('Erreur de connexion à MongoDB: ' . $e->getMessage());
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