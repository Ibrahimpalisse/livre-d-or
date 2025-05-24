/**
 * Récupère les statistiques de validation pour une publication
 * 
 * @param string $publicationId
 * @return array
 */
public function getValidationStats($publicationId)
{
    // Convertir l'ID en ObjectId
    $pubId = new MongoDB\BSON\ObjectId($publicationId);
    
    // Rechercher les validations positives et négatives
    $validations = $this->db->validations->find(['publication_id' => $pubId]);
    
    $valid_count = 0;
    $invalid_count = 0;
    
    // Compter les validations
    foreach ($validations as $validation) {
        if ($validation->is_valid) {
            $valid_count++;
        } else {
            $invalid_count++;
        }
    }
    
    // Calculer les pourcentages
    $total = $valid_count + $invalid_count;
    $valid_percentage = $total > 0 ? round(($valid_count / $total) * 100) : 0;
    $invalid_percentage = $total > 0 ? round(($invalid_count / $total) * 100) : 0;
    
    // Log pour débogage
    error_log("Stats pour publication $publicationId: valid=$valid_count, invalid=$invalid_count, total=$total");
    
    return [
        'publication_id' => $publicationId,
        'valid_count' => $valid_count,
        'invalid_count' => $invalid_count,
        'valid_percentage' => $valid_percentage,
        'invalid_percentage' => $invalid_percentage,
        'total' => $total
    ];
} 