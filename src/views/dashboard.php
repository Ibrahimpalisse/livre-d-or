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
        <div class="col-md-2">
            <button class="btn btn-info w-100" type="button" data-bs-toggle="modal" data-bs-target="#usersModal">
                Utilisateurs
            </button>
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
                <h5 class="modal-title" id="usersModalLabel">Liste des utilisateurs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Barre de recherche utilisateurs -->
                <div class="row mb-3">
                    <div class="col-md-8 offset-md-2">
                        <form class="d-flex">
                            <input class="form-control me-2" type="search" placeholder="Rechercher un utilisateur..." aria-label="Search">
                            <button class="btn btn-outline-primary" type="button">Rechercher</button>
                        </form>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nom d'utilisateur</th>
                                <th scope="col">Rôle</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row">1</th>
                                <td>admin</td>
                                <td>
                                    <span class="badge bg-danger">Admin</span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Modifier rôle
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#">Admin</a></li>
                                            <li><a class="dropdown-item" href="#">Utilisateur</a></li>
                                            <li><a class="dropdown-item" href="#">Désactiver</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">2</th>
                                <td>user1</td>
                                <td>
                                    <span class="badge bg-primary">Utilisateur</span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Modifier rôle
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#">Admin</a></li>
                                            <li><a class="dropdown-item" href="#">Utilisateur</a></li>
                                            <li><a class="dropdown-item" href="#">Désactiver</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">3</th>
                                <td>user2</td>
                                <td>
                                    <span class="badge bg-secondary">Désactivé</span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Modifier rôle
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#">Admin</a></li>
                                            <li><a class="dropdown-item" href="#">Utilisateur</a></li>
                                            <li><a class="dropdown-item" href="#">Désactiver</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination pour les utilisateurs -->
                <nav aria-label="Pagination utilisateurs" class="mt-3">
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

