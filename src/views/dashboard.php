<?php
// Définition de la fonction formatRole si elle n'existe pas déjà
if (!function_exists('formatRole')) {
    function formatRole($role) {
        switch ($role) {
            case 'super_admin': return 'Super Admin';
            case 'admin': return 'Admin';
            case 'disabled': return 'Désactivé';
            default: return 'Utilisateur';
        }
    }
}

// Vérification de sécurité supplémentaire - bloquer l'accès aux utilisateurs non admin
if (isset($user) && $user['role'] === 'user') {
    header('Location: /home');
    exit;
}
?>

<div class="container py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-5 mb-2 mb-md-0">
            <div class="input-group">
                <input class="form-control" type="search" placeholder="Rechercher une publication..." id="searchInput" name="q">
                <button class="btn btn-primary" type="button" id="searchButton">
                    <i class="bi bi-search"></i> Rechercher
                </button>
            </div>
        </div>
        <div class="col-md-3 mb-2 mb-md-0">
            <select class="form-select form-select-lg" name="filterType" id="filterTypeSelect">
                <option value="all" selected>Tous les types</option>
                <option value="roman">📖 Roman</option>
                <option value="manhwa">🖼️ Manhwa</option>
                <option value="anime">🎬 Animé</option>
            </select>
        </div>
        <div class="col-md-2 mb-2 mb-md-0">
            <button class="btn btn-success w-100" type="button" data-bs-toggle="collapse" data-bs-target="#createForm" aria-expanded="false" aria-controls="createForm">
                <i class="bi bi-plus-lg"></i> Créer
            </button>
        </div>
        <?php if ($is_super_admin): ?>
        <div class="col-md-2">
            <button class="btn btn-info w-100" type="button" data-bs-toggle="modal" data-bs-target="#usersModal">
                <i class="bi bi-people"></i> Utilisateurs
            </button>
        </div>
        <?php endif; ?>
        </div>
    </div>

    <div class="collapse mb-4" id="createForm">
        <div class="card card-body">
            <h5 class="mb-3">Nouvelle publication</h5>
            <form method="post" action="/dashboard" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="title" class="form-label">Titre</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                    <div class="invalid-feedback" id="titleError"></div>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="type" class="form-label">Type</label>
                    <div class="d-flex">
                        <div class="form-check me-4">
                            <input class="form-check-input" type="radio" name="type" id="typeRoman" value="roman" checked>
                            <label class="form-check-label" for="typeRoman">
                                <span class="badge bg-primary">Roman</span>
                            </label>
                        </div>
                        <div class="form-check me-4">
                            <input class="form-check-input" type="radio" name="type" id="typeManhwa" value="manhwa">
                            <label class="form-check-label" for="typeManhwa">
                                <span class="badge bg-success">Manhwa</span>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type" id="typeAnime" value="anime">
                            <label class="form-check-label" for="typeAnime">
                                <span class="badge bg-danger">Animé</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    <div class="invalid-feedback" id="imageError"></div>
                </div>
                <div class="mb-3" id="linksContainer">
                    <label class="form-label">Liens pour lire</label>
                    <div class="input-group mb-2">
                        <input type="url" class="form-control" name="links[]" placeholder="https://exemple.com/roman" required>
                        <button class="btn btn-outline-secondary" type="button" id="addLinkBtn">Ajouter un lien</button>
                    </div>
                    <div id="additionalLinks"></div>
                    <div class="invalid-feedback" id="linkError"></div>
                </div>
                <button type="submit" class="btn btn-primary">Publier</button>
            </form>
        </div>
    </div>

    <div id="publicationList">
        <!-- Les publications seront chargées ici par JavaScript -->
        <div class="row" id="publicationsContainer">
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>
        </div>
                <!-- Template pour l'affichage d'une publication -->        <template id="publicationTemplate">            <div class="col-md-4 mb-4">                <div class="card h-100">                    <div class="position-relative">                        <div class="card-img-top bg-light text-center publication-image" style="height: 200px; background-size: cover; background-position: center;">                            <!-- Image de couverture sera définie via JavaScript -->                        </div>                        <span class="position-absolute top-0 end-0 badge publication-type-badge m-2" style="font-size: 0.9rem; padding: 6px 10px;">Type</span>                    </div>                    <div class="card-body">                        <h5 class="card-title publication-title">Titre</h5>                        <p class="card-text text-muted">                            <small class="publication-date">Date</small>                        </p>                        <p class="card-text publication-description">Description</p>                    </div>                    <div class="card-footer d-flex justify-content-between">                        <a href="#" class="btn btn-sm btn-primary publication-link" target="_blank">Consulter</a>                        <div class="publication-actions">                            <button class="btn btn-sm btn-warning edit-publication" title="Modifier">                                <i class="bi bi-pencil"></i>                            </button>                            <button class="btn btn-sm btn-danger delete-publication" title="Supprimer">                                <i class="bi bi-trash"></i>                            </button>                        </div>                    </div>                </div>            </div>        </template>
        <!-- Pagination -->
        <nav aria-label="Pagination" class="mt-4">
            <ul class="pagination justify-content-center" id="publicationPagination"></ul>
        </nav>
    </div>
</div>


<!-- Modal pour afficher la liste des utilisateurs -->
<div class="modal fade" id="usersModal" tabindex="-1" aria-labelledby="usersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="usersModalLabel">Gestion des utilisateurs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Barre de recherche -->
                <div class="mb-3">
                    <input type="text" class="form-control" id="userSearchInput" placeholder="Rechercher un utilisateur...">
                </div>
                <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                            <th>Nom d'utilisateur</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                            </tr>
                        </thead>
                    <tbody id="usersTableBody">
                        <?php foreach ($users ?? [] as $user): ?>
                            <tr>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                                <td>
                                <span class="badge <?= $user['role'] === 'super_admin' ? 'bg-danger' : ($user['role'] === 'admin' ? 'bg-warning' : 'bg-primary') ?>">
                                    <?= formatRole($user['role']) ?>
                                </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Modifier rôle
                                        </button>
                                        <ul class="dropdown-menu">
                                        <li><a class="dropdown-item change-role" href="#" data-userid="<?= $user['id'] ?>" data-role="admin">Admin</a></li>
                                        <li><a class="dropdown-item change-role" href="#" data-userid="<?= $user['id'] ?>" data-role="user">Utilisateur</a></li>
                                        <li><a class="dropdown-item change-role" href="#" data-userid="<?= $user['id'] ?>" data-role="disabled">Désactiver</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="3" class="text-center">Aucun utilisateur trouvé</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                <!-- Pagination -->
                <nav aria-label="Pagination utilisateurs" class="mt-3">
                    <ul class="pagination justify-content-center mb-0" id="usersPagination"></ul>
                </nav>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour confirmer la suppression -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer cette publication ?</p>
                <p class="text-danger"><strong>Cette action est irréversible.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Supprimer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour afficher les liens de lecture -->
<div class="modal fade" id="linksModal" tabindex="-1" aria-labelledby="linksModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="linksModalLabel">Liens</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body" id="linksModalBody">
        <!-- Les liens seront injectés ici par JS -->
            </div>
        </div>
    </div>
</div>

<!-- Modal pour éditer une publication -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Modifier la publication</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <form id="editForm" method="post" enctype="multipart/form-data">
          <input type="hidden" id="edit_id" name="id">
          
          <div class="mb-3">
            <label for="edit_title" class="form-label">Titre</label>
            <input type="text" class="form-control" id="edit_title" name="title" required>
            <div class="invalid-feedback" id="edit_titleError"></div>
          </div>
          
          <div class="mb-3">
            <label for="edit_description" class="form-label">Description</label>
            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
          </div>
          
          <div class="mb-3">
            <label for="edit_type" class="form-label">Type</label>
            <select class="form-select" id="edit_type" name="type">
              <option value="roman">Roman</option>
              <option value="manhwa">Manhwa</option>
              <option value="anime">Animé</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label for="edit_image" class="form-label">Image (optionnelle)</label>
            <div id="edit_image_preview" class="mb-2"></div>
            <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
            <div class="invalid-feedback" id="edit_imageError"></div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Liens</label>
            <div id="edit_links_container">
              <!-- Les liens seront ajoutés ici dynamiquement -->
            </div>
            <button type="button" class="btn btn-outline-secondary mt-2" id="edit_add_link_btn">
              <i class="bi bi-plus-circle"></i> Ajouter un lien
            </button>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary" id="saveEditBtn">Enregistrer</button>
      </div>
    </div>
  </div>
</div>

