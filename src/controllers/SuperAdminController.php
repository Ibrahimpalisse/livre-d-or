<?php
namespace Controllers;

use Models\User;
use Core\Auth;

class SuperAdminController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * API pour modifier le rôle d'un utilisateur (super admin uniquement)
     */
    public function updateRole() {
        $response = [
            'success' => false,
            'message' => ''
        ];

        // Vérifier que l'utilisateur est super admin
        if (!Auth::isSuperAdmin()) {
            $response['message'] = 'Accès refusé. Seul un super administrateur peut modifier les rôles.';
            return $this->sendJsonResponse($response, 403);
        }

        // Récupérer les données POST JSON
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['user_id'] ?? $data['userId'] ?? null;
        $role = $data['new_role'] ?? $data['role'] ?? null;

        if (!$userId || !$role) {
            $response['message'] = 'Données incomplètes.';
            return $this->sendJsonResponse($response, 400);
        }

        // Valider le rôle
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
            
            // Vérifier si l'utilisateur a modifié son propre rôle
            $currentUser = Auth::getCurrentUser();
            
            if ($currentUser && $currentUser['id'] === $userId) {
                // L'utilisateur a modifié son propre rôle, générer un nouveau token
                $updatedUser = $this->userModel->findById($userId);
                if ($updatedUser) {
                    // Générer un nouveau token JWT avec le rôle mis à jour
                    $response['new_token'] = Auth::generateToken($updatedUser);
                }
            }
        } else {
            $response['message'] = 'Erreur lors de la mise à jour du rôle.';
        }
        
        return $this->sendJsonResponse($response);
    }

    private function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
} 