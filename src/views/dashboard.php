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
            <form class="d-flex" method="get" action="/dashboard">
                <input class="form-control me-2" type="search" placeholder="Rechercher une publication..." name="q">
                <button class="btn btn-outline-primary" type="submit">Rechercher</button>
            </form>
        </div>
        <div class="col-md-3 mb-2 mb-md-0">
            <div class="dropdown w-100">
                <button class="btn btn-secondary dropdown-toggle w-100" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Filtrer par type
                </button>
                <ul class="dropdown-menu w-100" aria-labelledby="filterDropdown">
                    <li><a class="dropdown-item" href="?type=all">Tous</a></li>
                    <li><a class="dropdown-item" href="?type=roman">Roman</a></li>
                    <li><a class="dropdown-item" href="?type=manhwa">Manhwa</a></li>
                    <li><a class="dropdown-item" href="?type=anime">Animé</a></li>
                </ul>
            </div>
        </div>
        <div class="col-md-2 mb-2 mb-md-0">
            <button class="btn btn-success w-100" type="button" data-bs-toggle="collapse" data-bs-target="#createForm" aria-expanded="false" aria-controls="createForm">
                Créer une publication
            </button>
        </div>
        <?php if ($is_super_admin): ?>
        <div class="col-md-2">
            <button class="btn btn-info w-100" type="button" data-bs-toggle="modal" data-bs-target="#usersModal">
                Gestion des utilisateurs
            </button>
        </div>
        <?php endif; ?>
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
                    <select class="form-select" id="type" name="type" required>
                        <option value="roman">Roman</option>
                        <option value="manhwa">Manhwa</option>
                        <option value="anime">Animé</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    <div class="invalid-feedback" id="imageError"></div>
                </div>
                <div class="mb-3">
                    <label for="link" class="form-label">Lien pour lire</label>
                    <input type="url" class="form-control" id="link" name="link" placeholder="https://exemple.com/roman" required>
                    <div class="invalid-feedback" id="linkError"></div>
                </div>
                <button type="submit" class="btn btn-primary">Publier</button>
            </form>
        </div>
    </div>

    <div id="publicationList">
        <div class="alert alert-info text-center">Aucune publication pour le moment.</div>
        <nav aria-label="Pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Précédent</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Suivant</a>
                </li>
            </ul>
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

<script>
// --- Gestion recherche et pagination utilisateurs ---
document.addEventListener('DOMContentLoaded', function() {
    // --- Recherche et pagination ---
    const users = <?php echo json_encode($users ?? []); ?>;
    const usersPerPage = 7;
    let filteredUsers = users;
    let currentPage = 1;

    const userSearchInput = document.getElementById('userSearchInput');
    const usersTableBody = document.getElementById('usersTableBody');
    const usersPagination = document.getElementById('usersPagination');

    function renderTable(page = 1) {
        usersTableBody.innerHTML = '';
        const start = (page - 1) * usersPerPage;
        const end = start + usersPerPage;
        const pageUsers = filteredUsers.slice(start, end);
        if (pageUsers.length === 0) {
            usersTableBody.innerHTML = '<tr><td colspan="3" class="text-center">Aucun utilisateur trouvé</td></tr>';
        } else {
            pageUsers.forEach(user => {
                usersTableBody.innerHTML += `
                <tr>
                    <td>${user.username}</td>
                    <td><span class="badge ${user.role === 'super_admin' ? 'bg-danger' : (user.role === 'admin' ? 'bg-warning' : 'bg-primary')}">${formatRoleJS(user.role)}</span></td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Modifier rôle</button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item change-role" href="#" data-userid="${user.id}" data-role="admin">Admin</a></li>
                                <li><a class="dropdown-item change-role" href="#" data-userid="${user.id}" data-role="user">Utilisateur</a></li>
                                <li><a class="dropdown-item change-role" href="#" data-userid="${user.id}" data-role="disabled">Désactiver</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                `;
            });
        }
        attachRoleChangeListeners();
    }

    function renderPagination() {
        usersPagination.innerHTML = '';
        const totalPages = Math.ceil(filteredUsers.length / usersPerPage);
        if (totalPages <= 1) return;
        for (let i = 1; i <= totalPages; i++) {
            usersPagination.innerHTML += `<li class="page-item${i === currentPage ? ' active' : ''}"><a class="page-link" href="#">${i}</a></li>`;
        }
        // Pagination click
        usersPagination.querySelectorAll('a').forEach((a, idx) => {
            a.addEventListener('click', function(e) {
                e.preventDefault();
                currentPage = idx + 1;
                renderTable(currentPage);
                renderPagination();
            });
        });
    }

    userSearchInput.addEventListener('input', function() {
        const q = this.value.trim().toLowerCase();
        filteredUsers = users.filter(u => u.username.toLowerCase().includes(q));
        currentPage = 1;
        renderTable(currentPage);
        renderPagination();
    });

    function formatRoleJS(role) {
        switch (role) {
            case 'super_admin': return 'Super Admin';
            case 'admin': return 'Admin';
            case 'disabled': return 'Désactivé';
            default: return 'Utilisateur';
        }
    }

    // --- Gestion changement de rôle (AJAX) ---
    function attachRoleChangeListeners() {
        document.querySelectorAll('.change-role').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('data-userid');
                const newRole = this.getAttribute('data-role');
                if (confirm(`Êtes-vous sûr de vouloir changer le rôle de cet utilisateur à ${formatRoleJS(newRole)} ?`)) {
                    fetch('/api/update-role', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            new_role: newRole
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Rôle mis à jour avec succès !');
                            window.location.reload();
                        } else {
                            alert('Erreur: ' + (data.message || 'Une erreur est survenue'));
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue lors de la mise à jour du rôle');
                    });
                }
            });
        });
    }

    // Initialisation
    renderTable(currentPage);
    renderPagination();
});
</script>

