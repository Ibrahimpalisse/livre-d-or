<?php
namespace Models;

use Core\Database;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class Comment {
    private $collection;
    
    public function __construct() {
        $this->collection = Database::getInstance()->getCollection('comments');
    }
    
    /**
     * Ajoute un nouveau commentaire
     * 
     * @param string $publicationId ID de la publication
     * @param string $userId ID de l'utilisateur
     * @param string $username Nom d'utilisateur
     * @param string $content Contenu du commentaire
     * @return array|null Le commentaire créé ou null en cas d'erreur
     */
    public function create(string $publicationId, string $userId, string $username, string $content): ?array {
        try {
            $result = $this->collection->insertOne([
                'publication_id' => new ObjectId($publicationId),
                'user_id' => new ObjectId($userId),
                'username' => $username,
                'content' => $content,
                'created_at' => new UTCDateTime(),
                'likes' => 0,
                'dislikes' => 0
            ]);
            
            if ($result->getInsertedId()) {
                return $this->findById($result->getInsertedId());
            }
        } catch (\Exception $e) {
            error_log('Erreur lors de la création du commentaire: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Récupère un commentaire par son ID
     * 
     * @param string $id ID du commentaire
     * @return array|null Le commentaire ou null s'il n'existe pas
     */
    public function findById(string $id): ?array {
        try {
            // Vérifier que l'ID est un ObjectId valide
            if (!preg_match('/^[a-f\d]{24}$/i', $id)) {
                error_log("ID de commentaire non valide dans findById: " . $id);
                return null;
            }
            
            $comment = $this->collection->findOne(['_id' => new ObjectId($id)]);
            if ($comment) {
                return $this->formatComment($comment);
            }
        } catch (\Exception $e) {
            error_log('Erreur lors de la récupération du commentaire: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Récupère tous les commentaires d'une publication
     * 
     * @param string $publicationId ID de la publication
     * @return array Les commentaires
     */
    public function findByPublicationId(string $publicationId): array {
        try {
            $comments = [];
            $cursor = $this->collection->find(
                ['publication_id' => new ObjectId($publicationId)],
                ['sort' => ['created_at' => -1]] // Du plus récent au plus ancien
            );
            
            foreach ($cursor as $comment) {
                $comments[] = $this->formatComment($comment);
            }
            
            return $comments;
        } catch (\Exception $e) {
            error_log('Erreur lors de la récupération des commentaires: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Ajoute un like ou un dislike à un commentaire
     * 
     * @param string $id ID du commentaire
     * @param bool $isLike true pour like, false pour dislike
     * @return bool Succès ou échec
     */
    public function addReaction(string $id, bool $isLike = true): bool {
        try {
            // Vérifier que l'ID est un ObjectId valide
            if (!preg_match('/^[a-f\d]{24}$/i', $id)) {
                error_log("ID de commentaire non valide: " . $id);
                return false;
            }
            
            $field = $isLike ? 'likes' : 'dislikes';
            $result = $this->collection->updateOne(
                ['_id' => new ObjectId($id)],
                ['$inc' => [$field => 1]]
            );
            
            if ($result->getModifiedCount() > 0) {
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log('Erreur lors de l\'ajout de la réaction: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Formate un commentaire pour l'affichage
     * 
     * @param object $comment Commentaire de MongoDB
     * @return array Commentaire formaté
     */
    private function formatComment(object $comment): array {
        return [
            'id' => (string) $comment->_id,
            'publication_id' => (string) $comment->publication_id,
            'user_id' => (string) $comment->user_id,
            'username' => $comment->username,
            'content' => $comment->content,
            'created_at' => $comment->created_at->toDateTime()->format(DATE_ATOM),
            'likes' => $comment->likes ?? 0,
            'dislikes' => $comment->dislikes ?? 0
        ];
    }
} 