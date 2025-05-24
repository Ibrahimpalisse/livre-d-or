<?php
namespace Models;

use Core\Database;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class User {
    private $collection;
    
    // Définition des rôles
    const ROLE_USER = 'user';
    const ROLE_ADMIN = 'admin';
    const ROLE_SUPER_ADMIN = 'super_admin';

    public function __construct() {
        $this->collection = Database::getInstance()->getCollection('users');
    }

    /**
     * Vérifie si un nom d'utilisateur existe déjà
     */
    public function usernameExists($username) {
        $user = $this->collection->findOne(['username' => $username]);
        return $user !== null;
    }

    /**
     * Compte le nombre d'utilisateurs
     */
    public function countUsers() {
        return $this->collection->countDocuments();
    }

    /**
     * Crée un nouvel utilisateur
     */
    public function create($username, $password) {
        // Hachage du mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Déterminer le rôle - le premier utilisateur devient super_admin
        $role = self::ROLE_USER;
        if ($this->countUsers() === 0) {
            $role = self::ROLE_SUPER_ADMIN;
        }
        
        $result = $this->collection->insertOne([
            'username' => $username,
            'password' => $hashedPassword,
            'created_at' => new UTCDateTime(),
            'role' => $role
        ]);
        
        if ($result->getInsertedId()) {
            $user = $this->findById($result->getInsertedId());
            return $this->formatUserForJWT($user);
        }
        
        return null;
    }

    /**
     * Authentifie un utilisateur
     */
    public function authenticate($username, $password) {
        $user = $this->collection->findOne(['username' => $username]);
        
        if ($user && password_verify($password, $user->password)) {
            return $this->formatUserForJWT($user);
        }
        
        return null;
    }

    /**
     * Récupère un utilisateur par son ID
     */
    public function findById($id) {
        return $this->collection->findOne(['_id' => new ObjectId($id)]);
    }
    
    /**
     * Récupère un utilisateur par son nom d'utilisateur
     */
    public function findByUsername($username) {
        return $this->collection->findOne(['username' => $username]);
    }
    
    /**
     * Met à jour le rôle d'un utilisateur
     */
    public function updateRole($userId, $role) {
        $result = $this->collection->updateOne(
            ['_id' => new ObjectId($userId)],
            ['$set' => ['role' => $role]]
        );
        
        return $result->getModifiedCount() > 0;
    }
    
    /**
     * Formate les données utilisateur pour JWT
     */
    private function formatUserForJWT($user) {
        return [
            'id' => (string) $user->_id,
            'username' => $user->username,
            'role' => $user->role
        ];
    }
    
    /**
     * Liste tous les utilisateurs
     */
    public function findAll() {
        $users = $this->collection->find();
        $result = [];
        
        foreach ($users as $user) {
            $result[] = [
                'id' => (string) $user->_id,
                'username' => $user->username,
                'role' => $user->role,
                'created_at' => $user->created_at->toDateTime()->format('Y-m-d H:i:s')
            ];
        }
        
        return $result;
    }
} 