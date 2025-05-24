document.addEventListener('DOMContentLoaded', function () {
    // Éléments DOM
    const publicationsContainer = document.getElementById('publicationsContainer');
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    const typeSelect = document.getElementById('filterTypeSelect');
    
    // Variables globales
    let currentPage = 1;
    const limit = 9; // Publications par page
    let isUserAuthenticated = false;
    
    // Ajouter le token CSRF à toutes les requêtes POST
    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
    
    // Fonction de secours pour afficher des messages
    function showToast(type, message) {
        // Utilise simplement alert comme solution de secours
        alert(message);
    }
    
    // Chargement initial
    loadPublications();
    
    // Événements
    if (searchButton && searchInput) {
        searchButton.addEventListener('click', performSearch);
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });
    }
    
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            loadPublications({
                type: this.value,
                q: searchInput ? searchInput.value : '',
                page: 1
            });
        });
    }
    
    // Vérifier si l'utilisateur est connecté (rechercher un élément spécifique dans la page)
    const userDropdown = document.querySelector('.nav-link.dropdown-toggle');
    isUserAuthenticated = !!userDropdown;
    
    // Fonction pour charger les publications
    function loadPublications(params = {}) {
        // Paramètres par défaut
        const page = params.page || 1;
        const type = params.type || 'all';
        const search = params.q || '';
        
        // Mettre à jour l'interface utilisateur (filtres)
        if (type !== 'all') {
            const filterRadio = document.getElementById('filter' + type.charAt(0).toUpperCase() + type.slice(1));
            if (filterRadio) filterRadio.checked = true;
        } else {
            const filterAll = document.getElementById('filterAll');
            if (filterAll) filterAll.checked = true;
        }
        
        if (searchInput) searchInput.value = search;
        
        // Montrer le spinner de chargement
        if (publicationsContainer) {
            publicationsContainer.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>
            `;
        }
        
        // Construire l'URL de requête
        let url = `/publication/list?page=${page}&limit=${limit}`;
        if (type !== 'all') url += `&type=${type}`;
        if (search) url += `&q=${encodeURIComponent(search)}`;
        
        // Charger les publications
        fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            // Effacer le conteneur
            if (publicationsContainer) {
                publicationsContainer.innerHTML = '';
                
                if (data.publications.length === 0) {
                    publicationsContainer.innerHTML = `
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                Aucune publication trouvée.
                            </div>
                        </div>
                    `;
                    return;
                }
                
                // Générer les cartes de publications
                data.publications.forEach(publication => {
                    const card = createPublicationCard(publication);
                    publicationsContainer.appendChild(card);
                });
                
                // Générer la pagination
                generatePagination(data.pagination, params);
            }
        })
        .catch(error => {
            if (publicationsContainer) {
                publicationsContainer.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger text-center">
                            Une erreur est survenue lors du chargement des publications.
                        </div>
                    </div>
                `;
            }
        });
    }
    
    // Fonction pour exécuter la recherche
    function performSearch() {
        if (searchInput) {
            const selectedType = typeSelect ? typeSelect.value : 'all';
            loadPublications({
                q: searchInput.value,
                type: selectedType,
                page: 1
            });
        }
    }
    
    // Fonction pour créer une carte de publication
    function createPublicationCard(publication) {
        // Créer les éléments du DOM plutôt que d'insérer du HTML
        const col = document.createElement('div');
        col.className = 'col-md-4 mb-4';
        
        const card = document.createElement('div');
        card.className = 'card h-100 shadow-sm';
        
        // Image
        const imageContainer = document.createElement('div');
        imageContainer.className = 'position-relative';
        
        const image = document.createElement('div');
        image.className = 'card-img-top bg-light text-center publication-image';
        image.style.height = '200px';
        image.style.backgroundSize = 'cover';
        image.style.backgroundPosition = 'center';
        image.style.cursor = 'pointer';
        
        if (publication.image_path) {
            image.style.backgroundImage = `url('${publication.image_path}')`;
        } else {
            const defaultImages = {
                'roman': '/public/images/default-book.jpg',
                'manhwa': '/public/images/default-manhwa.jpg',
                'anime': '/public/images/default-anime.jpg'
            };
            image.style.backgroundImage = `url('${defaultImages[publication.type] || '/public/images/default.jpg'}')`;
        }
        
        // Ajouter l'événement pour agrandir l'image
        image.addEventListener('click', function() {
            showImageModal(publication);
        });
        
        // Badge de type
        const badge = document.createElement('span');
        badge.className = `position-absolute top-0 end-0 badge ${getTypeClass(publication.type)} m-2`;
        badge.style.fontSize = '0.9rem';
        badge.style.padding = '6px 10px';
        badge.innerHTML = getTypeIcon(publication.type) + ' ' + formatPublicationType(publication.type);
        
        imageContainer.appendChild(image);
        imageContainer.appendChild(badge);
        
        // Contenu de la carte
        const cardBody = document.createElement('div');
        cardBody.className = 'card-body';
        
        const title = document.createElement('h5');
        title.className = 'card-title';
        title.textContent = publication.title;
        
        const dateContainer = document.createElement('p');
        dateContainer.className = 'card-text text-muted';
        
        const dateSpan = document.createElement('small');
        dateSpan.textContent = formatDate(publication.created_at);
        dateContainer.appendChild(dateSpan);
        
        const description = document.createElement('p');
        description.className = 'card-text';
        const maxLength = 100;
        if (publication.description.length > maxLength) {
            description.textContent = publication.description.substring(0, maxLength - 3) + '...';
            const moreBtn = document.createElement('button');
            moreBtn.className = 'btn btn-link btn-sm p-0 ms-1';
            moreBtn.textContent = 'Lire plus';
            moreBtn.type = 'button';
            moreBtn.addEventListener('click', function(e) {
                e.preventDefault();
                showDescriptionModal(publication);
            });
            description.appendChild(moreBtn);
        } else {
            description.textContent = publication.description;
        }
        
        cardBody.appendChild(title);
        cardBody.appendChild(dateContainer);
        cardBody.appendChild(description);
        
        // Nombre de liens disponibles
        const linksInfo = document.createElement('p');
        linksInfo.className = 'card-text';
        const numLinks = Array.isArray(publication.links) ? publication.links.length : 0;
        
        const linksIcon = document.createElement('i');
        linksIcon.className = 'bi bi-link-45deg';
        
        const linksBadge = document.createElement('span');
        linksBadge.className = 'badge bg-secondary';
        linksBadge.textContent = numLinks + ' lien(s)';
        
        linksInfo.appendChild(linksIcon);
        linksInfo.appendChild(document.createTextNode(' '));
        linksInfo.appendChild(linksBadge);
        
        cardBody.appendChild(linksInfo);
        
        // Statistiques (commentaires, validations)
        const statsContainer = document.createElement('div');
        statsContainer.className = 'd-flex justify-content-between align-items-center mb-2 px-2';
        
        // Compteur de commentaires
        const commentsCount = document.createElement('div');
        commentsCount.className = 'text-muted small';
        
        const commentsIcon = document.createElement('i');
        commentsIcon.className = 'bi bi-chat-dots';
        
        const commentsCountSpan = document.createElement('span');
        commentsCountSpan.id = `commentsCount-${publication.id}`;
        commentsCountSpan.textContent = '0';
        
        commentsCount.appendChild(commentsIcon);
        commentsCount.appendChild(document.createTextNode(' '));
        commentsCount.appendChild(commentsCountSpan);
        commentsCount.appendChild(document.createTextNode(' commentaires'));
        
        statsContainer.appendChild(commentsCount);
        
        cardBody.appendChild(statsContainer);
        
        // Compteurs de validation (thumbs up/down)
        const validationContainer = document.createElement('div');
        validationContainer.className = 'd-flex justify-content-between align-items-center mb-2 px-2';
        validationContainer.id = `validationContainer-${publication.id}`;
        
        // Compteur de validations positives
        const validCount = document.createElement('div');
        validCount.className = 'badge bg-success';
        
        const validIcon = document.createElement('i');
        validIcon.className = 'bi bi-hand-thumbs-up-fill';
        
        const validCountSpan = document.createElement('span');
        validCountSpan.id = `validCount-${publication.id}`;
        validCountSpan.textContent = '0';
        validCountSpan.className = 'ms-1';
        
        validCount.appendChild(validIcon);
        validCount.appendChild(validCountSpan);
        
        // Compteur de validations négatives
        const invalidCount = document.createElement('div');
        invalidCount.className = 'badge bg-danger';
        
        const invalidIcon = document.createElement('i');
        invalidIcon.className = 'bi bi-hand-thumbs-down-fill';
        
        const invalidCountSpan = document.createElement('span');
        invalidCountSpan.id = `invalidCount-${publication.id}`;
        invalidCountSpan.textContent = '0';
        invalidCountSpan.className = 'ms-1';
        
        invalidCount.appendChild(invalidIcon);
        invalidCount.appendChild(invalidCountSpan);
        
        validationContainer.appendChild(validCount);
        validationContainer.appendChild(invalidCount);
        
        cardBody.appendChild(validationContainer);
        
        // Pied de carte
        const cardFooter = document.createElement('div');
        cardFooter.className = 'card-footer text-center';
        
        const btnGroup = document.createElement('div');
        btnGroup.className = 'btn-group w-100';
        
        // Bouton Liens
        const linkButton = document.createElement('button');
        linkButton.className = 'btn btn-primary';
        
        const linkButtonIcon = document.createElement('i');
        linkButtonIcon.className = 'bi bi-link-45deg';
        
        linkButton.appendChild(linkButtonIcon);
        linkButton.appendChild(document.createTextNode(` Liens (${numLinks})`));
        
        linkButton.addEventListener('click', function() {
            showLinksModal(publication);
        });
        
        // Bouton Commentaires
        const commentsButton = document.createElement('button');
        commentsButton.className = 'btn btn-outline-secondary';
        
        const commentsButtonIcon = document.createElement('i');
        commentsButtonIcon.className = 'bi bi-chat-dots';
        
        commentsButton.appendChild(commentsButtonIcon);
        commentsButton.appendChild(document.createTextNode(' Commentaires'));
        
        commentsButton.addEventListener('click', function() {
            showCommentsModal(publication);
        });
        
        // Bouton Noter
        const ratingButton = document.createElement('button');
        ratingButton.className = 'btn btn-outline-warning';
        
        const ratingButtonIcon = document.createElement('i');
        ratingButtonIcon.className = 'bi bi-star';
        
        ratingButton.appendChild(ratingButtonIcon);
        ratingButton.appendChild(document.createTextNode(' Noter'));
        
        ratingButton.addEventListener('click', function() {
            showRatingModal(publication);
        });
        
        btnGroup.appendChild(linkButton);
        btnGroup.appendChild(commentsButton);
        btnGroup.appendChild(ratingButton);
        
        cardFooter.appendChild(btnGroup);
        
        // Assembler la carte
        card.appendChild(imageContainer);
        card.appendChild(cardBody);
        card.appendChild(cardFooter);
        col.appendChild(card);
        
        // Charger les statistiques des commentaires et de validation
        loadCommentStats(publication.id);
        loadValidationStats(publication.id);
        
        return col;
    }
    
    // Fonction pour afficher la modale des liens
    function showLinksModal(publication) {
        // Vérifier si links existe et est un tableau
        const hasLinks = Array.isArray(publication.links) && publication.links.length > 0;
        
        const content = hasLinks 
            ? `
                <h6 class="mb-3">${publication.links.length} lien(s) disponible(s) :</h6>
                <ul class="list-group">
                    ${publication.links.map((link, index) => 
                        `<li class="list-group-item">
                            <span class="badge bg-primary me-2">${index + 1}</span>
                            <a href="${link}" target="_blank" rel="noopener" class="text-break">${link}</a>
                        </li>`
                    ).join('')}
                </ul>
            `
            : '<div class="alert alert-warning">Aucun lien disponible.</div>';
        
        const modal = createModal('linksModal', `Liens pour lire "${publication.title}"`, content);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
    
    // Fonction pour afficher l'image en grand
    function showImageModal(publication) {
        const image = publication.image_path 
            ? `<img id="modalImage" src="${publication.image_path}" alt="${publication.title}" class="img-fluid">`
            : `<img id="modalImage" src="${getDefaultImage(publication.type)}" alt="${publication.title}" class="img-fluid">`;
        
        const modal = createModal('imageModal', publication.title, `<div class="text-center">${image}</div>`);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
    
    // Fonction pour générer la pagination
    function generatePagination(pagination, currentParams) {
        const paginationElement = document.getElementById('publicationPagination');
        if (!paginationElement) return;
        
        paginationElement.innerHTML = '';
        
        if (pagination.pages <= 1) return;
        
        // Bouton précédent
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${pagination.page <= 1 ? 'disabled' : ''}`;
        const prevLink = document.createElement('a');
        prevLink.className = 'page-link';
        prevLink.href = '#';
        prevLink.textContent = 'Précédent';
        prevLi.appendChild(prevLink);
        paginationElement.appendChild(prevLi);
        
        if (pagination.page > 1) {
            prevLink.addEventListener('click', function(e) {
                e.preventDefault();
                loadPublications({...currentParams, page: pagination.page - 1});
            });
        }
        
        // Pages
        const maxPages = 5;
        let startPage = Math.max(1, pagination.page - Math.floor(maxPages / 2));
        let endPage = Math.min(pagination.pages, startPage + maxPages - 1);
        
        if (endPage - startPage + 1 < maxPages) {
            startPage = Math.max(1, endPage - maxPages + 1);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const pageLi = document.createElement('li');
            pageLi.className = `page-item ${i === pagination.page ? 'active' : ''}`;
            
            const pageLink = document.createElement('a');
            pageLink.className = 'page-link';
            pageLink.href = '#';
            pageLink.textContent = i;
            
            if (i !== pagination.page) {
                pageLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    loadPublications({...currentParams, page: i});
                });
            }
            
            pageLi.appendChild(pageLink);
            paginationElement.appendChild(pageLi);
        }
        
        // Bouton suivant
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${pagination.page >= pagination.pages ? 'disabled' : ''}`;
        const nextLink = document.createElement('a');
        nextLink.className = 'page-link';
        nextLink.href = '#';
        nextLink.textContent = 'Suivant';
        nextLi.appendChild(nextLink);
        paginationElement.appendChild(nextLi);
        
        if (pagination.page < pagination.pages) {
            nextLink.addEventListener('click', function(e) {
                e.preventDefault();
                loadPublications({...currentParams, page: pagination.page + 1});
            });
        }
    }
    
    // Fonctions utilitaires
    function formatPublicationType(type) {
        switch (type) {
            case 'roman': return 'Roman';
            case 'manhwa': return 'Manhwa';
            case 'anime': return 'Animé';
            default: return type;
        }
    }
    
    function getTypeClass(type) {
        switch (type) {
            case 'roman': return 'bg-primary';
            case 'manhwa': return 'bg-success';
            case 'anime': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }
    
    function getTypeIcon(type) {
        switch (type) {
            case 'roman': return '<i class="bi bi-book"></i>';
            case 'manhwa': return '<i class="bi bi-file-earmark-image"></i>';
            case 'anime': return '<i class="bi bi-film"></i>';
            default: return '<i class="bi bi-question-circle"></i>';
        }
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }
    
    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    // Ajouter la fonction pour la modale de description complète
    function showDescriptionModal(publication) {
        const modal = createModal('descModal', 'Description complète', publication.description);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
    
    // Fonction pour afficher la modale des commentaires
    function showCommentsModal(publication) {
        // Contenu du formulaire de commentaire ou du message de connexion
        let formContent = '';
        
        if (isUserAuthenticated) {
            // Utilisateur connecté : afficher le formulaire
            formContent = `
                <div class="card">
                    <div class="card-header">
                        Ajouter un commentaire
                    </div>
                    <div class="card-body">
                        <form id="commentForm">
                            <div class="mb-3">
                                <textarea class="form-control" id="commentContent" rows="3" placeholder="Votre commentaire..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" id="submitComment">Publier</button>
                        </form>
                    </div>
                </div>
            `;
        } else {
            // Utilisateur non connecté : afficher un message et un lien de connexion
            formContent = `
                <div class="alert alert-warning">
                    <p>Vous devez être connecté pour commenter.</p>
                    <a href="/login" class="btn btn-primary">Se connecter</a>
                </div>
            `;
        }
        
        const commentsContent = `
            <div id="commentsList" class="mb-4" style="max-height: 350px; overflow-y: auto;">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement des commentaires...</span>
                    </div>
                </div>
            </div>
            ${formContent}
        `;
        
        const modal = createModal('commentsModal', 'Commentaires', commentsContent);
        // Stocker l'ID de publication dans un attribut data pour le récupérer lors des appels API
        modal.setAttribute('data-publication-id', publication.id);
        
        // Référence à la modale pour Bootstrap
        const bsModal = new bootstrap.Modal(modal);
        
        // Chargement des commentaires
        loadComments(publication.id);
        
        // Gestion du formulaire de commentaire uniquement si l'utilisateur est connecté
        if (isUserAuthenticated) {
            const commentForm = document.getElementById('commentForm');
            const submitButton = document.getElementById('submitComment');
            
            commentForm.onsubmit = function(e) {
                e.preventDefault();
                const content = document.getElementById('commentContent').value.trim();
                if (content) {
                    submitButton.disabled = true;
                    submitButton.textContent = '';
                    const spinner = document.createElement('span');
                    spinner.className = 'spinner-border spinner-border-sm';
                    spinner.setAttribute('role', 'status');
                    spinner.setAttribute('aria-hidden', 'true');
                    submitButton.appendChild(spinner);
                    submitButton.appendChild(document.createTextNode(' Envoi...'));
                    submitComment(publication.id, content, function() {
                        // Toujours rechercher le bouton par son ID (au cas où le DOM a changé)
                        const btn = document.getElementById('submitComment');
                        if (btn) {
                            btn.disabled = false;
                            btn.textContent = 'Publier';
                        }
                        const textarea = document.getElementById('commentContent');
                        if (textarea) textarea.value = '';
                        loadComments(publication.id);
                    });
                }
            };
        }
        
        // Afficher la modale
        bsModal.show();
    }
    
    // Fonction pour charger les commentaires
    function loadComments(publicationId) {
        const commentsList = document.getElementById('commentsList');
        if (!commentsList) return;
        // Afficher le spinner de chargement
        commentsList.textContent = '';
        const spinner = document.createElement('div');
        spinner.className = 'text-center';
        const spinnerInner = document.createElement('div');
        spinnerInner.className = 'spinner-border text-primary';
        spinnerInner.setAttribute('role', 'status');
        const span = document.createElement('span');
        span.className = 'visually-hidden';
        span.textContent = 'Chargement des commentaires...';
        spinnerInner.appendChild(span);
        spinner.appendChild(spinnerInner);
        commentsList.appendChild(spinner);
        fetch(`/comments/by-publication?id=${publicationId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                commentsList.textContent = '';
                if (data.success && data.comments.length > 0) {
                    // Trier les commentaires du plus récent au plus ancien
                    data.comments.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                    data.comments.forEach(comment => {
                        const card = document.createElement('div');
                        card.className = 'card mb-3';
                        card.id = `comment-${comment.id}`;
                        const cardBody = document.createElement('div');
                        cardBody.className = 'card-body';
                        const header = document.createElement('div');
                        header.className = 'd-flex justify-content-between align-items-center';
                        const user = document.createElement('h6');
                        user.className = 'card-subtitle mb-2 text-muted';
                        user.textContent = comment.username;
                        const date = document.createElement('small');
                        date.className = 'text-success fw-bold';
                        date.textContent = formatDateTime(comment.created_at);
                        header.appendChild(user);
                        header.appendChild(date);
                        const content = document.createElement('p');
                        content.className = 'card-text';
                        content.textContent = comment.content;
                        cardBody.appendChild(header);
                        cardBody.appendChild(content);
                        card.appendChild(cardBody);
                        commentsList.appendChild(card);
                    });
                } else {
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-info';
                    alert.textContent = 'Aucun commentaire pour le moment. Soyez le premier à commenter !';
                    commentsList.appendChild(alert);
                }
            })
            .catch(error => {
                commentsList.textContent = '';
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger';
                alert.textContent = 'Une erreur est survenue lors du chargement des commentaires.';
                commentsList.appendChild(alert);
            });
    }
    
    // Fonction pour soumettre un commentaire
    function submitComment(publicationId, content, callback) {
        fetch('/comments/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                publication_id: publicationId,
                content: content
            })
        })
        .then(response => {
            // Vérifier si la réponse est JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // Si ce n'est pas du JSON, c'est probablement une redirection vers la page de connexion
                if (response.status === 401 || response.status === 302) {
                    // Rediriger vers la page de connexion
                    window.location.href = '/login';
                    throw new Error('Vous devez être connecté pour commenter.');
                } else {
                    // Autre type d'erreur
                    throw new Error('La réponse du serveur n\'est pas au format attendu. Veuillez réessayer plus tard.');
                }
            }
        })
        .then(data => {
            if (data.success) {
                // Commentaire ajouté avec succès
                if (callback) callback();
                // Mettre à jour les statistiques pour cette publication
                loadCommentStats(publicationId);
            } else {
                // Erreur lors de l'ajout du commentaire
                alert(data.message || 'Erreur lors de l\'ajout du commentaire.');
                if (callback) callback();
            }
        })
        .catch(error => {
            // Si l'erreur contient le mot "connecté", proposer la redirection
            if (error.message.includes('connecté')) {
                if (confirm('Vous devez être connecté pour commenter. Voulez-vous vous connecter maintenant?')) {
                    window.location.href = '/login';
                }
            } else {
                alert(error.message || 'Une erreur est survenue lors de l\'ajout du commentaire.');
            }
            // Toujours appeler le callback, même en cas d'erreur
            if (callback) callback();
        });
    }
    
    // Fonction pour afficher la modale de notation
    function showRatingModal(publication) {
        let content = '';
        
        if (isUserAuthenticated) {
            content = `
                <div class="text-center">
                    <p>Qu'avez-vous pensé de "${publication.title}" ?</p>
                    <div class="d-flex justify-content-center my-4">
                        <button class="btn btn-success btn-lg mx-2" id="validateBtn">
                            <i class="bi bi-hand-thumbs-up-fill"></i> Valider
                        </button>
                        <button class="btn btn-danger btn-lg mx-2" id="invalidateBtn">
                            <i class="bi bi-hand-thumbs-down-fill"></i> Ne pas valider
                        </button>
                    </div>
                    <div id="validationStats" class="mt-3 d-none">
                        <h5>Statistiques de validation</h5>
                        <div class="progress mt-2 mb-2" style="height: 25px;">
                            <div class="progress-bar bg-success" id="validPercentage" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                            <div class="progress-bar bg-danger" id="invalidPercentage" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span><i class="bi bi-hand-thumbs-up-fill text-success"></i> <span id="validCount">0</span> votes</span>
                            <span><i class="bi bi-hand-thumbs-down-fill text-danger"></i> <span id="invalidCount">0</span> votes</span>
                        </div>
                    </div>
                    <div id="validationMessage" class="text-muted mt-3"></div>
                </div>
            `;
        } else {
            content = `
                <div class="alert alert-warning text-center">
                    <p>Vous devez être connecté pour noter cette publication.</p>
                    <a href="/login" class="btn btn-primary">Se connecter</a>
                </div>
            `;
        }
        
        const modal = createModal('validationModal', 'Valider cette publication', content);
        const bsModal = new bootstrap.Modal(modal);
        
        // Ajouter les gestionnaires d'événements après l'ajout de la modale au DOM seulement si l'utilisateur est connecté
        if (isUserAuthenticated) {
            setTimeout(() => {
                const validateBtn = document.getElementById('validateBtn');
                const invalidateBtn = document.getElementById('invalidateBtn');
                const validationMessage = document.getElementById('validationMessage');
                const validationStats = document.getElementById('validationStats');
                
                // Charger les statistiques actuelles
                loadValidationStats(publication.id);
                
                if (validateBtn) {
                    validateBtn.addEventListener('click', () => handleValidation(publication.id, true, validateBtn, invalidateBtn, validationMessage));
                }
                
                if (invalidateBtn) {
                    invalidateBtn.addEventListener('click', () => handleValidation(publication.id, false, validateBtn, invalidateBtn, validationMessage));
                }
            }, 100);
        }
        
        bsModal.show();
    }
    
    // Utilitaire pour fetch avec gestion d'erreur
    async function fetchJson(url, options = {}) {
        const response = await fetch(url, options);
        if (!response.ok) throw new Error('Erreur réseau');
        return response.json();
    }

    // Met à jour les compteurs sur la carte et dans la modale
    function updateValidationUI(publicationId, stats) {
        // Carte
        const validCount = document.getElementById(`validCount-${publicationId}`);
        const invalidCount = document.getElementById(`invalidCount-${publicationId}`);
        if (validCount) validCount.textContent = stats.valid_count;
        if (invalidCount) invalidCount.textContent = stats.invalid_count;

        // Modale
        const validCountModal = document.getElementById('validCount');
        const invalidCountModal = document.getElementById('invalidCount');
        const validPercentage = document.getElementById('validPercentage');
        const invalidPercentage = document.getElementById('invalidPercentage');
        if (validCountModal && invalidCountModal && validPercentage && invalidPercentage) {
            validCountModal.textContent = stats.valid_count;
            invalidCountModal.textContent = stats.invalid_count;
            validPercentage.style.width = stats.valid_percentage + '%';
            validPercentage.textContent = stats.valid_percentage + '%';
            invalidPercentage.style.width = stats.invalid_percentage + '%';
            invalidPercentage.textContent = stats.invalid_percentage + '%';
            document.getElementById('validationStats')?.classList.remove('d-none');
        }
    }

    // Gestion du vote
    async function handleValidation(publicationId, isValid, validateBtn, invalidateBtn, validationMessage) {
        try {
            validateBtn.disabled = true;
            invalidateBtn.disabled = true;
            validationMessage.innerHTML = `<div class="spinner-border text-primary" role="status"></div>`;
            const data = await fetchJson('/publication/validate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ publication_id: publicationId, isValid })
            });
            validationMessage.textContent = data.message || (isValid ? 'Publication validée !' : 'Publication non validée.');
            validationMessage.className = data.success ? 'alert alert-success mt-3' : 'alert alert-danger mt-3';
            if (data.success && data.stats) updateValidationUI(publicationId, data.stats);
            validateBtn.disabled = false;
            invalidateBtn.disabled = false;
        } catch (error) {
            validationMessage.textContent = 'Erreur lors de la validation.';
            validationMessage.className = 'alert alert-danger mt-3';
            validateBtn.disabled = false;
            invalidateBtn.disabled = false;
        }
    }
    
    // Fonction pour charger les statistiques de validation
    function loadValidationStats(publicationId) {
        // Construire l'URL avec l'ID de publication
        const url = `/publication/stats?id=${publicationId}`;
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    // Mettre à jour les compteurs sur la carte
                    updateValidationCounters(publicationId, data.stats);
                } else {
                    console.error('Données invalides ou success=false:', data);
                }
            })
            .catch(error => {
                console.error(`Erreur lors du chargement des stats pour la publication ${publicationId}:`, error);
            });
    }
    
    // Fonction pour mettre à jour les compteurs de validation
    function updateValidationCounters(publicationId, stats) {
        // 1. Mise à jour des compteurs sur la carte
        updateCardCounters(publicationId, stats);
        
        // 2. Mise à jour des statistiques dans la modale (si elle est ouverte)
        updateModalCounters(stats);
    }
    
    // Fonction spécifique pour mettre à jour les compteurs sur la carte
    function updateCardCounters(publicationId, stats) {
        // Utiliser querySelector pour être sûr de trouver les éléments
        const validCount = document.querySelector(`#validCount-${publicationId}`);
        const invalidCount = document.querySelector(`#invalidCount-${publicationId}`);
        
        if (validCount) {
            validCount.textContent = stats.valid_count;
        } else {
            console.error(`Élément #validCount-${publicationId} introuvable`);
            
            // Essayer de trouver tous les compteurs de validation (débogage)
            const allValidCounts = document.querySelectorAll('[id^="validCount-"]');
            console.log(`Tous les éléments de compteurs de validation trouvés (${allValidCounts.length}):`, 
                Array.from(allValidCounts).map(el => el.id));
        }
        
        if (invalidCount) {
            invalidCount.textContent = stats.invalid_count;
        } else {
            console.error(`Élément #invalidCount-${publicationId} introuvable`);
        }
    }
    
    // Fonction spécifique pour mettre à jour les statistiques dans la modale
    function updateModalCounters(stats) {
        const validCountModal = document.getElementById('validCount');
        const invalidCountModal = document.getElementById('invalidCount');
        const validPercentage = document.getElementById('validPercentage');
        const invalidPercentage = document.getElementById('invalidPercentage');
        
        if (validCountModal && invalidCountModal && validPercentage && invalidPercentage) {
            validCountModal.textContent = stats.valid_count;
            invalidCountModal.textContent = stats.invalid_count;
            validPercentage.style.width = stats.valid_percentage + '%';
            validPercentage.setAttribute('aria-valuenow', stats.valid_percentage);
            validPercentage.textContent = stats.valid_percentage + '%';
            invalidPercentage.style.width = stats.invalid_percentage + '%';
            invalidPercentage.setAttribute('aria-valuenow', stats.invalid_percentage);
            invalidPercentage.textContent = stats.invalid_percentage + '%';
            
            // S'assurer que les statistiques sont visibles
            const validationStats = document.getElementById('validationStats');
            if (validationStats) {
                validationStats.classList.remove('d-none');
            }
        }
    }
    
    // Fonction pour afficher la modale des statistiques de validation
    function loadValidationStatsCard(publicationId) {
        const validationStatsCard = document.getElementById(`validationStatsCard-${publicationId}`);
        if (!validationStatsCard) return;
        
        // Afficher le bloc de statistiques
        validationStatsCard.classList.remove('d-none');
        
        // Charger les statistiques depuis le serveur
        fetch(`/publication/stats?id=${publicationId}`)
            .then(response => {
                if (!response.ok) {
                    // Si l'API n'existe pas encore, on masque simplement le bloc de stats
                    validationStatsCard.classList.add('d-none');
                    return null;
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    updateValidationStatsCard(data.stats);
                } else {
                    // Si pas de données, on masque le bloc de stats
                    validationStatsCard.classList.add('d-none');
                }
            })
            .catch(error => {
                console.error(`Erreur lors du chargement des stats pour la publication ${publicationId}:`, error);
                validationStatsCard.classList.add('d-none');
            });
    }
    
    // Fonction pour mettre à jour l'affichage des statistiques de validation
    function updateValidationStatsCard(stats) {
        const validationStatsCard = document.getElementById(`validationStatsCard-${stats.publication_id}`);
        if (!validationStatsCard) return;
        
        // Mettre à jour les statistiques
        const validPercentage = document.getElementById('validPercentageCard');
        const invalidPercentage = document.getElementById('invalidPercentageCard');
        const validCount = document.getElementById('validCountCard');
        const invalidCount = document.getElementById('invalidCountCard');
        
        if (!validPercentage || !invalidPercentage || !validCount || !invalidCount) return;
        
        // Mettre à jour les statistiques
        validCount.textContent = stats.valid_count;
        invalidCount.textContent = stats.invalid_count;
        
        // Mettre à jour les barres de progression
        validPercentage.style.width = stats.valid_percentage + '%';
        validPercentage.setAttribute('aria-valuenow', stats.valid_percentage);
        validPercentage.textContent = stats.valid_percentage + '%';
        
        invalidPercentage.style.width = stats.invalid_percentage + '%';
        invalidPercentage.setAttribute('aria-valuenow', stats.invalid_percentage);
        invalidPercentage.textContent = stats.invalid_percentage + '%';
        
        // Afficher le bloc de statistiques
        validationStatsCard.classList.remove('d-none');
    }
    
    // Fonction pour charger les statistiques des commentaires
    function loadCommentStats(publicationId) {
        fetch(`/comments/by-publication?id=${publicationId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Mettre à jour le compteur de commentaires
                    const commentsCountEl = document.getElementById(`commentsCount-${publicationId}`);
                    if (commentsCountEl) {
                        commentsCountEl.textContent = data.count || 0;
                    }
                }
            })
            .catch(error => {
                console.error(`Erreur lors du chargement des stats pour la publication ${publicationId}:`, error);
            });
    }
    
    // Fonction pour formater la date relative
    function formatRelativeDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffSec = Math.floor(diffMs / 1000);
        const diffMin = Math.floor(diffSec / 60);
        const diffHour = Math.floor(diffMin / 60);
        const diffDay = Math.floor(diffHour / 24);
        
        if (diffSec < 60) {
            return 'À l\'instant';
        } else if (diffMin < 60) {
            return `Il y a ${diffMin} minute${diffMin > 1 ? 's' : ''}`;
        } else if (diffHour < 24) {
            return `Il y a ${diffHour} heure${diffHour > 1 ? 's' : ''}`;
        } else if (diffDay < 7) {
            return `Il y a ${diffDay} jour${diffDay > 1 ? 's' : ''}`;
        } else {
            return date.toLocaleDateString('fr-FR', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }
    }
    
    // Fonction utilitaire pour obtenir l'image par défaut
    function getDefaultImage(type) {
        const defaultImages = {
            'roman': '/public/images/default-book.jpg',
            'manhwa': '/public/images/default-manhwa.jpg',
            'anime': '/public/images/default-anime.jpg'
        };
        return defaultImages[type] || '/public/images/default.jpg';
    }
    
    // Fonction pour créer une modale
    function createModal(id, title, content) {
        let modal = document.getElementById(id);
        if (!modal) {
            modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.id = id;
            modal.tabIndex = '-1';
            // Structure statique de la modale
            modal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <div class="modal-body" id="${id}Body"></div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        // Met à jour le titre
        modal.querySelector('.modal-title').textContent = title;
        // Met à jour le contenu du corps
        const body = document.getElementById(`${id}Body`);
        if (typeof content === 'string') {
            // Si le contenu est du HTML complexe, on utilise innerHTML (ex: listes de liens, etc.)
            body.innerHTML = content;
        } else if (content instanceof Node) {
            body.textContent = '';
            body.appendChild(content);
        } else {
            body.textContent = content;
        }
        return modal;
    }
});


