<?php
namespace Controllers;

use Models\Publication;
use Core\Env;

class PublicationController {
    private $publication;
    
    public function __construct() {
        $this->publication = new Publication();
    }
    
    /**
     * Crée une nouvelle publication
     * 
     * @param array $data Les données du formulaire
     * @param array $files Les fichiers téléchargés
     * @param array $user L'utilisateur connecté
     * @return array Réponse avec succès/erreur
     */
    public function create($data, $files, $user) {
        // Vérifier que l'utilisateur est admin ou super_admin
        if (!in_array($user['role'], ['admin', 'super_admin'])) {
            return [
                'success' => false,
                'message' => 'Vous n\'avez pas les droits pour créer une publication'
            ];
        }
        
        // Vérifier les données requises
        if (empty($data['title']) || empty($data['description']) || 
            empty($data['type']) || empty($data['links']) || !is_array($data['links']) || count(array_filter($data['links'])) === 0) {
            return [
                'success' => false,
                'message' => 'Tous les champs sont obligatoires'
            ];
        }
        
        // Créer la publication
        $image = isset($files['image']) ? $files['image'] : null;
        $result = $this->publication->create($data, $image, $user['id']);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Publication créée avec succès',
                'publication' => $result
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Erreur lors de la création de la publication'
        ];
    }
    
    /**
     * Récupère toutes les publications avec filtrage et pagination
     * 
     * @param array $params Paramètres de requête
     * @return array Publications et métadonnées
     */
    public function getAll($params) {
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
        $type = isset($params['type']) ? $params['type'] : null;
        $search = isset($params['q']) ? $params['q'] : null;
        
        return $this->publication->findAll($page, $limit, $type, $search);
    }
    
    /**
     * Récupère une publication par son ID
     * 
     * @param string $id ID de la publication
     * @return array|null Publication ou null
     */
    public function getById($id) {
        return $this->publication->findById($id);
    }
    
    /**
     * Met à jour une publication
     * 
     * @param string $id ID de la publication
     * @param array $data Nouvelles données
     * @param array $files Nouveaux fichiers
     * @param array $user Utilisateur connecté
     * @return array Réponse avec succès/erreur
     */
    public function update($id, $data, $files, $user) {
        // Vérifier que l'utilisateur est admin ou super_admin
        if (!in_array($user['role'], ['admin', 'super_admin'])) {
            return [
                'success' => false,
                'message' => 'Vous n\'avez pas les droits pour modifier une publication'
            ];
        }
        
        // Vérifier que la publication existe
        $publication = $this->publication->findById($id);
        if (!$publication) {
            return [
                'success' => false,
                'message' => 'Publication introuvable'
            ];
        }
        
        // Mettre à jour la publication
        $image = isset($files['image']) ? $files['image'] : null;
        $result = $this->publication->update($id, $data, $image);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Publication mise à jour avec succès',
                'publication' => $result
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Erreur lors de la mise à jour de la publication'
        ];
    }
    
    /**
     * Supprime une publication
     * 
     * @param string $id ID de la publication
     * @param array $user Utilisateur connecté
     * @return array Réponse avec succès/erreur
     */
    public function delete($id, $user) {
        // Vérifier que l'utilisateur est admin ou super_admin
        if (!in_array($user['role'], ['admin', 'super_admin'])) {
            return [
                'success' => false,
                'message' => 'Vous n\'avez pas les droits pour supprimer une publication'
            ];
        }
        
        // Vérifier que la publication existe
        $publication = $this->publication->findById($id);
        if (!$publication) {
            return [
                'success' => false,
                'message' => 'Publication introuvable'
            ];
        }
        
        // Supprimer la publication
        $result = $this->publication->delete($id);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Publication supprimée avec succès'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Erreur lors de la suppression de la publication'
        ];
    }
    
    /**
     * Ajoute ou met à jour une validation pour une publication
     * 
     * @param string $id ID de la publication
     * @param bool $isValid true pour valider, false pour invalider
     * @param array $user Utilisateur connecté
     * @return array Réponse avec succès/erreur
     */
    public function validatePublication($id, $isValid, $user) {
        // Vérifier que l'utilisateur est connecté
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Vous devez être connecté pour valider une publication'
            ];
        }
        
        // Vérifier que la publication existe
        $publication = $this->publication->findById($id);
        if (!$publication) {
            return [
                'success' => false,
                'message' => 'Publication introuvable'
            ];
        }
        
        // Ajouter/mettre à jour la validation
        $result = $this->publication->addValidation($id, $user['id'], $isValid);
        
        // Récupérer les stats à jour
        $stats = $this->publication->getValidationStats($id);
        
        if ($result) {
            return [
                'success' => true,
                'message' => $isValid ? 'Publication validée avec succès' : 'Publication invalidée avec succès',
                'validation' => $result,
                'stats' => $stats
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Erreur lors de la validation de la publication'
        ];
    }
} 