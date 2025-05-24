<?php
namespace Controllers;

use Models\Comment;
use Models\Publication;
use Core\Auth;

class CommentController {
    private $comment;
    private $publication;

    public function __construct() {
        $this->comment = new Comment();
        $this->publication = new Publication();
    }

    /**
     * Récupère tous les commentaires d'une publication
     * 
     * @param string $publicationId ID de la publication
     * @return array Les commentaires et les métadonnées
     */
    public function getCommentsByPublication($publicationId) {
        if (empty($publicationId)) {
            return [
                'success' => false,
                'message' => 'ID de publication manquant',
                'comments' => []
            ];
        }

        $comments = $this->comment->findByPublicationId($publicationId);

        return [
            'success' => true,
            'comments' => $comments,
            'count' => count($comments)
        ];
    }

    /**
     * Ajoute un nouveau commentaire
     * 
     * @param string $publicationId ID de la publication
     * @param string $content Contenu du commentaire
     * @param array $user Utilisateur connecté
     * @return array Réponse avec succès/erreur
     */
    public function addComment($publicationId, $content, $user) {
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Vous devez être connecté pour commenter'
            ];
        }
        if (isset($user['role']) && $user['role'] === 'disabled') {
            return [
                'success' => false,
                'message' => 'Votre compte a été désactivé. Vous ne pouvez plus commenter.'
            ];
        }

        if (empty($publicationId) || empty(trim($content))) {
            return [
                'success' => false,
                'message' => 'ID de publication ou contenu manquant'
            ];
        }

        $comment = $this->comment->create(
            $publicationId,
            $user['id'],
            $user['username'],
            trim($content)
        );

        if ($comment) {
            return [
                'success' => true,
                'message' => 'Commentaire ajouté avec succès',
                'comment' => $comment
            ];
        }

        return [
            'success' => false,
            'message' => 'Erreur lors de l\'ajout du commentaire'
        ];
    }
    
    /**
     * Valide ou invalide une publication (système de vote)
     * 
     * @param string $publicationId ID de la publication
     * @param bool $isValid true pour valider, false pour invalider
     * @param array $user Utilisateur connecté
     * @return array Réponse avec succès/erreur
     */
    public function validatePublication($publicationId, $isValid, $user) {
        // Vérifier que l'utilisateur est connecté
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Vous devez être connecté pour valider une publication'
            ];
        }
        
        // Vérifier que l'ID de publication est fourni
        if (empty($publicationId)) {
            return [
                'success' => false,
                'message' => 'ID de publication manquant'
            ];
        }
        
        // Ajouter/mettre à jour la validation
        $result = $this->publication->addValidation($publicationId, $user['id'], $isValid);
        
        if ($result) {
            // En plus d'ajouter une validation, on va aussi ajouter un commentaire automatique
            $message = $isValid 
                ? "J'ai validé cette publication ! 👍" 
                : "Je n'ai pas validé cette publication. 👎";
                
            // Créer un commentaire (optionnel - peut être commenté si non souhaité)
            $this->comment->create(
                $publicationId,
                $user['id'],
                $user['username'],
                $message
            );
            
            // Récupérer les statistiques de validation
            $stats = $this->publication->getValidationStats($publicationId);
            
            return [
                'success' => true,
                'message' => $isValid ? 'Publication validée avec succès' : 'Publication invalidée avec succès',
                'validation' => [
                    'status' => $isValid ? 'valid' : 'invalid',
                    'stats' => $stats
                ]
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Erreur lors de la validation de la publication'
        ];
    }
}