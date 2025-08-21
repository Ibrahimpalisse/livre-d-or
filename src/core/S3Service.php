<?php
namespace Core;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class S3Service {
    private $s3Client;
    private $bucket;

    public function __construct() {
        $this->bucket = getenv('AWS_BUCKET');
        
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region'  => getenv('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key'    => getenv('AWS_ACCESS_KEY_ID'),
                'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
            ]
        ]);
        
        // Créer le dossier Livre-d-or lors de la première utilisation
        $this->ensureFolderExists();
    }

    /**
     * S'assurer que le dossier Livre-d-or existe dans le bucket
     */
    private function ensureFolderExists() {
        try {
            // Vérifier si le "dossier" existe déjà
            $result = $this->s3Client->listObjectsV2([
                'Bucket' => $this->bucket,
                'Prefix' => 'Livre-d-or/',
                'MaxKeys' => 1
            ]);

            // Si aucun objet avec ce préfixe n'existe, créer un marqueur de dossier
            if (empty($result['Contents'])) {
                $this->s3Client->putObject([
                    'Bucket' => $this->bucket,
                    'Key'    => 'Livre-d-or/.folder',
                    'Body'   => '',
                    'ACL'    => 'private'
                ]);
                
                error_log('Dossier Livre-d-or créé dans S3');
            }
        } catch (AwsException $e) {
            error_log('Erreur lors de la création du dossier S3: ' . $e->getMessage());
        }
    }

    /**
     * Upload une image vers S3
     */
    public function uploadImage($file, $fileName) {
        try {
            // Générer un nom de fichier unique
            $uniqueFileName = 'pub_' . uniqid() . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
            $key = 'Livre-d-or/publications/' . $uniqueFileName;

            $result = $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key'    => $key,
                'Body'   => $file,
                'ACL'    => 'public-read',
                'ContentType' => $this->getMimeType($fileName)
            ]);

            // Retourner l'URL publique
            return $this->s3Client->getObjectUrl($this->bucket, $key);
            
        } catch (AwsException $e) {
            error_log('Erreur upload S3: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprimer une image de S3
     */
    public function deleteImage($imageUrl) {
        try {
            // Extraire la clé depuis l'URL
            $key = $this->extractKeyFromUrl($imageUrl);
            
            if ($key) {
                $this->s3Client->deleteObject([
                    'Bucket' => $this->bucket,
                    'Key'    => $key
                ]);
                return true;
            }
            
            return false;
        } catch (AwsException $e) {
            error_log('Erreur suppression S3: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extraire la clé S3 depuis une URL
     */
    private function extractKeyFromUrl($url) {
        $parsedUrl = parse_url($url);
        if (isset($parsedUrl['path'])) {
            // Supprimer le premier "/" et retourner le chemin
            return ltrim($parsedUrl['path'], '/');
        }
        return null;
    }

    /**
     * Obtenir le type MIME d'un fichier
     */
    private function getMimeType($fileName) {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp'
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}