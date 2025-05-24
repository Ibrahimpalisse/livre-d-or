<?php
// Inclure les classes nécessaires
require_once __DIR__ . '/../Publication.php';

/**
 * Contrôleur pour gérer les commentaires
 */
class CommentController {
    
    private $db;
    
    /**
     * Constructeur
     * 
     * @param MongoDB\Database $db
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Répond avec un JSON
     * 
     * @param array $data
     * @return void
     */
    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    }
    
    /**
     * Ajoute un commentaire
     * 
     * @param array $params
     * @return void
     */
    public function add($params = []) {
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Vous devez être connecté pour commenter']);
            return;
        }
        
        // Vérifier les paramètres
        if (!isset($params['publication_id']) || !isset($params['content'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Paramètres manquants']);
            return;
        }
        
        // Récupérer les données du formulaire
        $publicationId = $params['publication_id'];
        $content = htmlspecialchars(trim($params['content']));
        $userId = $_SESSION['user_id'];
        $username = $_SESSION['username'];
        
        // Vérifier que le contenu n'est pas vide
        if (empty($content)) {
            $this->jsonResponse(['success' => false, 'message' => 'Le commentaire ne peut pas être vide']);
            return;
        }
        
        try {
            // Insérer le commentaire dans la base de données
            $comment = [
                'publication_id' => new MongoDB\BSON\ObjectId($publicationId),
                'user_id' => new MongoDB\BSON\ObjectId($userId),
                'username' => $username,
                'content' => $content,
                'created_at' => new MongoDB\BSON\UTCDateTime()
            ];
            
            $result = $this->db->comments->insertOne($comment);
            
            if ($result->getInsertedCount() === 1) {
                $this->jsonResponse(['success' => true, 'message' => 'Commentaire ajouté avec succès']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de l\'ajout du commentaire']);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Une erreur est survenue: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Récupère les commentaires d'une publication
     * 
     * @param array $params
     * @return void
     */
    public function byPublication($params = []) {
        // Vérifier les paramètres
        if (!isset($params['id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de publication manquant']);
            return;
        }
        
        // Récupérer l'ID de la publication
        $publicationId = $params['id'];
        
        try {
            // Convertir l'ID en ObjectId
            $pubId = new MongoDB\BSON\ObjectId($publicationId);
            
            // Récupérer les commentaires
            $comments = $this->db->comments->find(
                ['publication_id' => $pubId],
                ['sort' => ['created_at' => -1]]
            );
            
            // Convertir les résultats en tableau
            $commentsList = [];
            $count = 0;
            
            foreach ($comments as $comment) {
                $count++;
                $commentsList[] = [
                    'id' => (string) $comment->_id,
                    'publication_id' => (string) $comment->publication_id,
                    'user_id' => (string) $comment->user_id,
                    'username' => $comment->username,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at->toDateTime()->format(DateTime::ATOM)
                ];
            }
            
            $this->jsonResponse([
                'success' => true,
                'comments' => $commentsList,
                'count' => $count
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Une erreur est survenue: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Valide ou invalide une publication
     * 
     * @param array $params
     * @return void
     */
    public function validatePublication($params = []) {
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Vous devez être connecté pour valider une publication']);
            return;
        }
    
        // Vérifier les paramètres
        if (!isset($params['publication_id']) || !isset($params['is_valid'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Paramètres manquants']);
            return;
        }
    
        // Récupérer les paramètres
        $publicationId = $params['publication_id'];
        $isValid = (bool) $params['is_valid'];
        $userId = $_SESSION['user_id'];
    
        try {
            // Convertir les ID en ObjectId
            $pubId = new MongoDB\BSON\ObjectId($publicationId);
            $uId = new MongoDB\BSON\ObjectId($userId);
    
            // Vérifier si l'utilisateur a déjà validé cette publication
            $existingValidation = $this->db->validations->findOne([
                'publication_id' => $pubId,
                'user_id' => $uId
            ]);
    
            if ($existingValidation) {
                // Si la validation existe déjà avec la même valeur, ne rien faire
                if ($existingValidation->is_valid === $isValid) {
                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Vous avez déjà ' . ($isValid ? 'validé' : 'invalidé') . ' cette publication'
                    ]);
                    return;
                }
                
                // Mettre à jour la validation existante
                $this->db->validations->updateOne(
                    ['_id' => $existingValidation->_id],
                    ['$set' => ['is_valid' => $isValid]]
                );
                
                $message = 'Votre validation a été mise à jour';
            } else {
                // Créer une nouvelle validation
                $this->db->validations->insertOne([
                    'publication_id' => $pubId,
                    'user_id' => $uId,
                    'is_valid' => $isValid,
                    'created_at' => new MongoDB\BSON\UTCDateTime()
                ]);
                
                $message = 'Publication ' . ($isValid ? 'validée' : 'invalidée') . ' avec succès';
            }
    
            // Récupérer les statistiques mises à jour
            $publication = new Publication($this->db);
            $stats = $publication->getValidationStats($publicationId);
    
            $this->jsonResponse([
                'success' => true,
                'message' => $message,
                'stats' => $stats
            ]);
        } catch (Exception $e) {
            error_log('Erreur lors de la validation: ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Une erreur est survenue lors de la validation']);
        }
    }
    
    /**
     * Récupère les statistiques de validation d'une publication
     * 
     * @param array $params
     * @return void
     */
    public function getValidationStats($params = []) {
        // Vérifier les paramètres
        if (!isset($params['id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de publication manquant']);
            return;
        }
    
        // Récupérer l'ID de la publication
        $publicationId = $params['id'];
    
        try {
            // Récupérer les statistiques
            $publication = new Publication($this->db);
            $stats = $publication->getValidationStats($publicationId);
    
            $this->jsonResponse([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (Exception $e) {
            error_log('Erreur lors de la récupération des stats: ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Une erreur est survenue']);
        }
    }
} 
 