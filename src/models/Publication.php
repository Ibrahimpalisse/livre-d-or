<?php
namespace Models;

use Core\Database;
use Core\Env;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class Publication {
    private $collection;
    
    // Types de publications valides
    const TYPE_ROMAN = 'roman';
    const TYPE_MANHWA = 'manhwa'; 
    const TYPE_ANIME = 'anime';
    
    // Dossier pour stocker les images
    const UPLOAD_DIR = '/public/uploads/publications/';
    
    public function __construct() {
        $this->collection = Database::getInstance()->getCollection('publications');
    }
    
    /**
     * Ajoute une nouvelle publication
     * 
     * @param array $data Les données de la publication
     * @param array $image Les données de l'image téléchargée ($_FILES['image'])
     * @param string $userId ID de l'utilisateur qui crée la publication
     * @return array|null La publication créée ou null en cas d'erreur
     */
    public function create($data, $image, $userId) {
        // Vérifier que les champs obligatoires sont présents
        if (empty($data['title']) || empty($data['description']) || 
            empty($data['type']) || empty($data['links']) || !is_array($data['links']) || count(array_filter($data['links'])) === 0) {
            return null;
        }
        
        // Vérifier que le type est valide
        if (!in_array($data['type'], [self::TYPE_ROMAN, self::TYPE_MANHWA, self::TYPE_ANIME])) {
            return null;
        }
        
        // Chemin d'image par défaut
        $imagePath = null;
        
        // Traiter l'image si elle existe
        if (!empty($image) && $image['error'] === UPLOAD_ERR_OK) {
            $imagePath = $this->saveImage($image);
            if (!$imagePath) {
                return null; // Erreur lors de l'enregistrement de l'image
            }
        }
        
        // Créer la publication
        $result = $this->collection->insertOne([
            'title' => $data['title'],
            'description' => $data['description'],
            'type' => $data['type'],
            'links' => array_values(array_filter($data['links'])),
            'image_path' => $imagePath,
            'created_by' => new ObjectId($userId),
            'created_at' => new UTCDateTime(),
            'updated_at' => new UTCDateTime(),
        ]);
        
        if ($result->getInsertedId()) {
            return $this->findById($result->getInsertedId());
        }
        
        return null;
    }
    
    /**
     * Sauvegarde une image téléchargée
     * 
     * @param array $image Les données de l'image ($_FILES['image'])
     * @return string|null Le chemin relatif de l'image ou null en cas d'erreur
     */
    private function saveImage($image) {
        // Vérifier que le dossier d'upload existe, sinon le créer
        $uploadDir = dirname(dirname(__DIR__)) . self::UPLOAD_DIR;
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return null;
            }
        }
        
        // Générer un nom unique pour l'image
        $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
        $filename = uniqid('pub_') . '.' . $extension;
        $targetPath = $uploadDir . $filename;
        
        // Déplacer l'image vers le dossier d'upload
        if (move_uploaded_file($image['tmp_name'], $targetPath)) {
            return self::UPLOAD_DIR . $filename; // Chemin relatif pour la BD
        }
        
        return null;
    }
    
    /**
     * Récupère une publication par son ID
     * 
     * @param string $id ID de la publication
     * @return array|null La publication ou null si elle n'existe pas
     */
    public function findById($id) {
        $publication = $this->collection->findOne(['_id' => new ObjectId($id)]);
        if ($publication) {
            return $this->formatPublication($publication);
        }
        return null;
    }
    
    /**
     * Liste toutes les publications avec pagination et filtrage
     * 
     * @param int $page Numéro de page (commence à 1)
     * @param int $limit Nombre d'éléments par page
     * @param string $type Type de publication (optionnel)
     * @param string $search Terme de recherche (optionnel)
     * @return array Publications et métadonnées de pagination
     */
    public function findAll($page = 1, $limit = 10, $type = null, $search = null) {
        $filter = [];
        
        // Filtrer par type si spécifié
        if ($type && $type !== 'all') {
            $filter['type'] = $type;
        }
        
        // Recherche par titre si spécifié
        if ($search) {
            $filter['title'] = ['$regex' => $search, '$options' => 'i'];
        }
        
        // Calculer l'offset pour la pagination
        $skip = ($page - 1) * $limit;
        
        // Récupérer le nombre total d'éléments
        $totalCount = $this->collection->countDocuments($filter);
        
        // Récupérer les publications
        $cursor = $this->collection->find(
            $filter,
            [
                'limit' => $limit,
                'skip' => $skip,
                'sort' => ['created_at' => -1] // Du plus récent au plus ancien
            ]
        );
        
        $publications = [];
        foreach ($cursor as $publication) {
            $publications[] = $this->formatPublication($publication);
        }
        
        return [
            'publications' => $publications,
            'pagination' => [
                'total' => $totalCount,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($totalCount / $limit)
            ]
        ];
    }
    
    /**
     * Met à jour une publication
     * 
     * @param string $id ID de la publication
     * @param array $data Nouvelles données
     * @param array $image Nouvelle image (optionnel)
     * @return array|null La publication mise à jour ou null en cas d'erreur
     */
    public function update($id, $data, $image = null) {
        $updateData = [
            'updated_at' => new UTCDateTime()
        ];
        
        // Mettre à jour les champs fournis
        foreach (['title', 'description', 'type'] as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        // Gérer les liens multiples
        if (isset($data['links']) && is_array($data['links'])) {
            $updateData['links'] = array_values(array_filter($data['links']));
        }
        
        // Traiter l'image si elle existe
        if ($image && $image['error'] === UPLOAD_ERR_OK) {
            $imagePath = $this->saveImage($image);
            if ($imagePath) {
                $updateData['image_path'] = $imagePath;
                
                // Supprimer l'ancienne image
                $publication = $this->findById($id);
                if ($publication && !empty($publication['image_path'])) {
                    $this->deleteImage($publication['image_path']);
                }
            }
        }
        
        // Mettre à jour la publication
        $result = $this->collection->updateOne(
            ['_id' => new ObjectId($id)],
            ['$set' => $updateData]
        );
        
        if ($result->getModifiedCount() > 0) {
            return $this->findById($id);
        }
        
        return null;
    }
    
    /**
     * Supprime une publication
     * 
     * @param string $id ID de la publication
     * @return bool Succès ou échec
     */
    public function delete($id) {
        // Récupérer la publication pour supprimer l'image
        $publication = $this->findById($id);
        if ($publication && !empty($publication['image_path'])) {
            $this->deleteImage($publication['image_path']);
        }
        
        $result = $this->collection->deleteOne(['_id' => new ObjectId($id)]);
        return $result->getDeletedCount() > 0;
    }
    
    /**
     * Supprime une image
     * 
     * @param string $path Chemin de l'image
     * @return bool Succès ou échec
     */
    private function deleteImage($path) {
        $fullPath = dirname(dirname(__DIR__)) . $path;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }
    
    /**
     * Formate une publication pour l'affichage
     * 
     * @param object $publication Publication de MongoDB
     * @return array Publication formatée
     */
    private function formatPublication($publication) {
        $formattedPublication = [
            'id' => (string) $publication->_id,
            'title' => $publication->title,
            'description' => $publication->description,
            'type' => $publication->type,
            'links' => isset($publication->links) ? $publication->links : [],
            'created_at' => $publication->created_at->toDateTime()->format('Y-m-d H:i:s'),
            'created_by' => (string) $publication->created_by,
        ];
        
        if (isset($publication->image_path)) {
            $formattedPublication['image_path'] = $publication->image_path;
        }
        
        if (isset($publication->updated_at)) {
            $formattedPublication['updated_at'] = $publication->updated_at->toDateTime()->format('Y-m-d H:i:s');
        }
        
        return $formattedPublication;
    }
    
    /**
     * Ajoute ou met à jour une validation pour une publication
     * 
     * @param string $id ID de la publication
     * @param string $userId ID de l'utilisateur
     * @param bool $isValid true pour valider, false pour invalider
     * @return bool|array Succès ou échec
     */
    public function addValidation($id, $userId, $isValid) {
        try {
            // Chercher si une validation existe déjà pour cet utilisateur et cette publication
            $validationCollection = Database::getInstance()->getCollection('validations');
            
            $existingValidation = $validationCollection->findOne([
                'publication_id' => new ObjectId($id),
                'user_id' => new ObjectId($userId)
            ]);
            
            if ($existingValidation) {
                // Mettre à jour la validation existante
                $result = $validationCollection->updateOne(
                    [
                        'publication_id' => new ObjectId($id),
                        'user_id' => new ObjectId($userId)
                    ],
                    [
                        '$set' => [
                            'is_valid' => $isValid,
                            'updated_at' => new UTCDateTime()
                        ]
                    ]
                );
                
                return $result->getModifiedCount() > 0;
            } else {
                // Créer une nouvelle validation
                $result = $validationCollection->insertOne([
                    'publication_id' => new ObjectId($id),
                    'user_id' => new ObjectId($userId),
                    'is_valid' => $isValid,
                    'created_at' => new UTCDateTime(),
                    'updated_at' => new UTCDateTime()
                ]);
                
                return $result->getInsertedId() ? true : false;
            }
        } catch (\Exception $e) {
            // Enregistrer l'erreur dans les logs pour le débogage
            error_log('Erreur lors de la validation d\'une publication: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère les statistiques de validation d'une publication
     * 
     * @param string $id ID de la publication
     * @return array Statistiques de validation (nombre de validations positives/négatives)
     */
    public function getValidationStats($id) {
        try {
            $validationCollection = Database::getInstance()->getCollection('validations');
            
            // Compter les validations positives
            $validCount = $validationCollection->countDocuments([
                'publication_id' => new ObjectId($id),
                'is_valid' => true
            ]);
            
            // Compter les validations négatives
            $invalidCount = $validationCollection->countDocuments([
                'publication_id' => new ObjectId($id),
                'is_valid' => false
            ]);
            
            // Récupérer le total des validations
            $totalCount = $validCount + $invalidCount;
            
            return [
                'valid_count' => $validCount,
                'invalid_count' => $invalidCount,
                'total_count' => $totalCount,
                'valid_percentage' => $totalCount > 0 ? round(($validCount / $totalCount) * 100) : 0,
                'invalid_percentage' => $totalCount > 0 ? round(($invalidCount / $totalCount) * 100) : 0
            ];
        } catch (\Exception $e) {
            error_log('Erreur lors de la récupération des statistiques de validation: ' . $e->getMessage());
            return [
                'valid_count' => 0,
                'invalid_count' => 0,
                'total_count' => 0,
                'valid_percentage' => 0,
                'invalid_percentage' => 0
            ];
        }
    }
} 