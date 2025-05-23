<form class="mb-4" method="get" action="/home">
    <div class="row g-2 align-items-center">
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Rechercher un roman, manhwa ou animé..." name="q">
                <button class="btn btn-primary" type="submit">Rechercher</button>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dropdown w-100">
                <button class="btn btn-secondary dropdown-toggle w-100" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    Filtrer par type
                </button>
                <ul class="dropdown-menu w-100" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="?type=all">Tous</a></li>
                    <li><a class="dropdown-item" href="?type=roman">Roman</a></li>
                    <li><a class="dropdown-item" href="?type=manhwa">Manhwa</a></li>
                    <li><a class="dropdown-item" href="?type=anime">Animé</a></li>
                </ul>
            </div>
        </div>
    </div>
</form>

<div class="row g-4">
    <!-- Exemple de carte 1 -->
    <div class="col-md-4">
        <div class="card h-100">
            <img src="https://via.placeholder.com/400x250?text=Image+Roman+1" class="card-img-top" alt="Roman 1">
            <div class="card-body">
                <h5 class="card-title">Titre du roman 1</h5>
                <p class="card-text">Petite description du roman, manhwa ou animé. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                <a href="https://exemple.com/roman1" target="_blank" class="btn btn-primary btn-sm mt-2">
                    <i class="bi bi-book"></i> Lire
                </a>
                <div class="mt-3">
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#commentsModal1">
                        <i class="bi bi-chat-dots"></i> Commentaires
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Exemple de carte 2 -->
    <div class="col-md-4">
        <div class="card h-100">
            <img src="https://via.placeholder.com/400x250?text=Image+Roman+2" class="card-img-top" alt="Roman 2">
            <div class="card-body">
                <h5 class="card-title">Titre du roman 2</h5>
                <p class="card-text">Une autre description pour un autre roman ou animé. Vivamus luctus urna sed urna ultricies ac tempor dui sagittis.</p>
                <a href="https://exemple.com/roman2" target="_blank" class="btn btn-primary btn-sm mt-2">
                    <i class="bi bi-book"></i> Lire
                </a>
                <div class="mt-3">
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#commentsModal2">
                        <i class="bi bi-chat-dots"></i> Commentaires
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Exemple de carte 3 -->
    <div class="col-md-4">
        <div class="card h-100">
            <img src="https://via.placeholder.com/400x250?text=Image+Roman+3" class="card-img-top" alt="Roman 3">
            <div class="card-body">
                <h5 class="card-title">Titre du roman 3</h5>
                <p class="card-text">Encore une description pour un manhwa ou animé. Pellentesque habitant morbi tristique senectus et netus.</p>
                <a href="https://exemple.com/roman3" target="_blank" class="btn btn-primary btn-sm mt-2">
                    <i class="bi bi-book"></i> Lire
                </a>
                <div class="mt-3">
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#commentsModal3">
                        <i class="bi bi-chat-dots"></i> Commentaires
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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

<!-- Modal pour les commentaires du Roman 1 -->
<div class="modal fade" id="commentsModal1" tabindex="-1" aria-labelledby="commentsModalLabel1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commentsModalLabel1">Commentaires - Titre du roman 1</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Liste des commentaires -->
                <div class="comments-list mb-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h6 class="card-subtitle mb-2 text-muted">John Doe</h6>
                                <small class="text-muted">Il y a 2 jours</small>
                            </div>
                            <p class="card-text">J'ai vraiment adoré ce roman, l'intrigue est captivante!</p>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h6 class="card-subtitle mb-2 text-muted">Jane Smith</h6>
                                <small class="text-muted">Il y a 5 jours</small>
                            </div>
                            <p class="card-text">Les personnages sont bien développés, j'attends la suite avec impatience.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Formulaire pour ajouter un commentaire -->
                <div class="card">
                    <div class="card-header">
                        Ajouter un commentaire
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="mb-3">
                                <textarea class="form-control" rows="3" placeholder="Votre commentaire..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Publier</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour les commentaires du Roman 2 -->
<div class="modal fade" id="commentsModal2" tabindex="-1" aria-labelledby="commentsModalLabel2" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commentsModalLabel2">Commentaires - Titre du roman 2</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Liste des commentaires -->
                <div class="comments-list mb-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h6 class="card-subtitle mb-2 text-muted">Alice Johnson</h6>
                                <small class="text-muted">Il y a 1 jour</small>
                            </div>
                            <p class="card-text">L'histoire est prenante mais certains passages sont un peu longs.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Formulaire pour ajouter un commentaire -->
                <div class="card">
                    <div class="card-header">
                        Ajouter un commentaire
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="mb-3">
                                <textarea class="form-control" rows="3" placeholder="Votre commentaire..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Publier</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour les commentaires du Roman 3 -->
<div class="modal fade" id="commentsModal3" tabindex="-1" aria-labelledby="commentsModalLabel3" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commentsModalLabel3">Commentaires - Titre du roman 3</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Liste des commentaires -->
                <div class="comments-list mb-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h6 class="card-subtitle mb-2 text-muted">Robert Brown</h6>
                                <small class="text-muted">Il y a 3 jours</small>
                            </div>
                            <p class="card-text">Excellent manhwa, les illustrations sont magnifiques!</p>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h6 class="card-subtitle mb-2 text-muted">Emma Wilson</h6>
                                <small class="text-muted">Il y a 1 semaine</small>
                            </div>
                            <p class="card-text">J'ai beaucoup aimé le développement des personnages secondaires.</p>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h6 class="card-subtitle mb-2 text-muted">David Miller</h6>
                                <small class="text-muted">Il y a 2 semaines</small>
                            </div>
                            <p class="card-text">Un peu déçu par la fin, j'espère qu'il y aura une suite.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Formulaire pour ajouter un commentaire -->
                <div class="card">
                    <div class="card-header">
                        Ajouter un commentaire
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="mb-3">
                                <textarea class="form-control" rows="3" placeholder="Votre commentaire..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Publier</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 