<?php
namespace Controllers;

use Models\User;
use Core\Auth;

class ApiController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Met à jour le rôle d'un utilisateur
     */
    public function updateRole() {
        $response = [
            'success' => false,
            'message' => ''
        ];

        // Vérifier que l'utilisateur est un super admin
        if (!Auth::isSuperAdmin()) {
            $response['message'] = 'Accès refusé. Vous devez être super administrateur pour modifier les rôles.';
            return $this->sendJsonResponse($response, 403);
        }

        // Récupérer les données POST JSON
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Accepter deux formats possibles de paramètres
        $userId = $data['user_id'] ?? $data['userId'] ?? null;
        $role = $data['new_role'] ?? $data['role'] ?? null;
        
        if (!$userId || !$role) {
            $response['message'] = 'Données incomplètes.';
            return $this->sendJsonResponse($response, 400);
        }

        // Valider le rôle (ajouter "disabled" comme rôle valide)
        $validRoles = [User::ROLE_USER, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN, 'disabled'];
        if (!in_array($role, $validRoles)) {
            $response['message'] = 'Rôle invalide.';
            return $this->sendJsonResponse($response, 400);
        }

        // Mettre à jour le rôle
        $success = $this->userModel->updateRole($userId, $role);

        if ($success) {
            $response['success'] = true;
            $response['message'] = 'Rôle mis à jour avec succès.';
            return $this->sendJsonResponse($response);
        } else {
            $response['message'] = 'Erreur lors de la mise à jour du rôle.';
            return $this->sendJsonResponse($response, 500);
        }
    }

    /**
     * Envoie une réponse JSON
     */
    private function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
} 