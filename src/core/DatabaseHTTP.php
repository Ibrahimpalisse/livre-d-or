<?php
namespace Core;

/**
 * Alternative HTTP client pour MongoDB Atlas quand l'extension native manque
 */
class DatabaseHTTP {
    private static $instance = null;
    private $baseUrl;
    private $headers;
    private $database;
    
    private function __construct() {
        // Utiliser Railway database ou fallback
        $this->database = $_ENV['MONGODB_DATABASE'] ?? 'railway';
        
        // Note: Ce fallback HTTP n'est plus nécessaire avec Railway MongoDB
        // Mais gardé pour compatibilité si jamais Railway MongoDB échoue
        error_log('⚠️ DatabaseHTTP utilisé - Railway MongoDB devrait être disponible normalement');
        
        // MongoDB Atlas Data API endpoint (fallback uniquement)
        $this->baseUrl = "https://data.mongodb-api.com/app/data-xvlcm/endpoint/data/v1";
        
        $this->headers = [
            'Content-Type: application/json',
            'api-key: ' . $this->getAPIKey()
        ];
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function getAPIKey() {
        // Pour MongoDB Atlas Data API, il faut une clé API
        // Alternative : utiliser l'API REST MongoDB Atlas
        return $_ENV['MONGODB_API_KEY'] ?? '';
    }
    
    public function getCollection($collectionName) {
        return new HTTPCollection($collectionName, $this->baseUrl, $this->headers, $this->database);
    }
}

/**
 * Collection MongoDB via HTTP/REST API
 */
class HTTPCollection {
    private $collection;
    private $baseUrl;
    private $headers;
    private $database;
    
    public function __construct($collection, $baseUrl, $headers, $database) {
        $this->collection = $collection;
        $this->baseUrl = $baseUrl;
        $this->headers = $headers;
        $this->database = $database;
    }
    
    public function insertOne($document) {
        $data = [
            'database' => $this->database,
            'collection' => $this->collection,
            'document' => $document
        ];
        
        return $this->makeRequest('/action/insertOne', $data);
    }
    
    public function find($filter = [], $options = []) {
        $data = [
            'database' => $this->database,
            'collection' => $this->collection,
            'filter' => $filter
        ];
        
        if (!empty($options)) {
            $data = array_merge($data, $options);
        }
        
        $result = $this->makeRequest('/action/find', $data);
        return new HTTPCursor($result['documents'] ?? []);
    }
    
    public function findOne($filter = []) {
        $data = [
            'database' => $this->database,
            'collection' => $this->collection,
            'filter' => $filter
        ];
        
        $result = $this->makeRequest('/action/findOne', $data);
        return $result['document'] ?? null;
    }
    
    public function updateOne($filter, $update) {
        $data = [
            'database' => $this->database,
            'collection' => $this->collection,
            'filter' => $filter,
            'update' => $update
        ];
        
        return $this->makeRequest('/action/updateOne', $data);
    }
    
    public function deleteOne($filter) {
        $data = [
            'database' => $this->database,
            'collection' => $this->collection,
            'filter' => $filter
        ];
        
        return $this->makeRequest('/action/deleteOne', $data);
    }
    
    private function makeRequest($endpoint, $data) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            error_log("MongoDB HTTP API Error: HTTP $httpCode - $response");
            return null;
        }
        
        return json_decode($response, true);
    }
}

/**
 * Cursor MongoDB via HTTP
 */
class HTTPCursor implements \IteratorAggregate {
    private $documents;
    
    public function __construct($documents) {
        $this->documents = $documents;
    }
    
    public function getIterator(): \Traversable {
        return new \ArrayIterator($this->documents);
    }
    
    public function toArray() {
        return $this->documents;
    }
}