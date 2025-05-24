document.addEventListener('DOMContentLoaded', function () {
    var createForm = document.getElementById('createForm');
    var publicationList = document.getElementById('publicationList');
    var createBtn = document.querySelector('[data-bs-target="#createForm"]');
    if (!createForm) return;

    // Variable pour stocker l'ID de la publication à supprimer
    let publicationToDelete = null;
    
    // Initialiser la modale de confirmation
    const confirmDeleteModal = document.getElementById('confirmDeleteModal');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    if (confirmDeleteModal && confirmDeleteBtn) {
        // Gestionnaire d'événement pour le bouton confirmer de la modale
        confirmDeleteBtn.addEventListener('click', function() {
            if (publicationToDelete) {
                // Fermer la modale
                const modal = bootstrap.Modal.getInstance(confirmDeleteModal);
                modal.hide();
                
                // Appeler la fonction de suppression
                deletePublication(publicationToDelete);
                
                // Réinitialiser l'ID
                publicationToDelete = null;
            }
        });
    }

    // Toujours masquer le formulaire au chargement (optimisé)
    createForm.classList.remove('show');
    createForm.setAttribute('aria-expanded', 'false');
    createForm.style.height = null;
    if (publicationList) publicationList.style.display = '';

    // Initialiser le collapse Bootstrap proprement
    var bsCollapse = bootstrap.Collapse.getOrCreateInstance(createForm, {toggle: false});

    if (!createBtn || !publicationList) return;

    createBtn.addEventListener('click', function () {
        setTimeout(function () {
            if (createForm.classList.contains('show')) {
                publicationList.style.display = 'none';
            } else {
                publicationList.style.display = '';
            }
        }, 350);
    });

    createForm.addEventListener('hidden.bs.collapse', function () {
        publicationList.style.display = '';
    });

    // Gestion de la recherche et du filtrage
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    
    // Événement pour le bouton de recherche
    if (searchButton && searchInput) {
        searchButton.addEventListener('click', function() {
            performSearch();
        });
        
        // Recherche aussi lorsqu'on appuie sur Entrée dans le champ de recherche
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });
    }
    
    // Fonction pour exécuter la recherche
    function performSearch() {
        if (searchInput && validateSearch(searchInput.value)) {
            const selectedType = typeSelect ? typeSelect.value : 'all';
            loadPublications({
                q: searchInput.value,
                type: selectedType,
                page: 1
            });
        } else if (searchInput) {
            const selectedType = typeSelect ? typeSelect.value : 'all';
            loadPublications({
                type: selectedType,
                page: 1
            });
        }
    }
    
    // Gestion des filtres par type
    const typeSelect = document.getElementById('filterTypeSelect');
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            const searchQuery = searchInput ? searchInput.value : '';
            loadPublications({
                q: searchQuery,
                type: this.value,
                page: 1
            });
        });
    }
    
    // Fonction pour réinitialiser le formulaire de création/édition
    function resetCreateForm() {
        const form = document.querySelector('form[action="/dashboard"][enctype="multipart/form-data"]');
        if (!form) return;
        
        // Réinitialiser les champs
        form.reset();
        
        // Réinitialiser le texte du bouton
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.textContent = 'Publier';
            submitBtn.dataset.mode = 'create';
            delete submitBtn.dataset.id;
        }
        
        // Supprimer le bouton d'annulation s'il existe
        const cancelBtn = form.querySelector('.btn-cancel');
        if (cancelBtn) {
            cancelBtn.remove();
        }
        
        // Supprimer l'aperçu de l'image s'il existe
        const imagePreview = form.querySelector('.image-preview');
        if (imagePreview) {
            imagePreview.remove();
        }
        
        // Réinitialiser le titre du formulaire
        const formTitle = form.querySelector('h5');
        if (formTitle) {
            formTitle.textContent = 'Nouvelle publication';
        }
        
        // S'assurer que le bouton radio 'Roman' est sélectionné par défaut
        const romanRadio = document.getElementById('typeRoman');
        if (romanRadio) {
            romanRadio.checked = true;
        }
    }
    
    // Gestion du formulaire de création/édition de publication
    var publicationForm = document.querySelector('form[action="/dashboard"][enctype="multipart/form-data"]');
    if (publicationForm) {
        publicationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var formData = new FormData(publicationForm);
            const submitBtn = publicationForm.querySelector('button[type="submit"]');
            const isUpdate = submitBtn.dataset.mode === 'update';
            const publicationId = isUpdate ? submitBtn.dataset.id : null;
            
            // URL et méthode selon le mode (création ou mise à jour)
            const url = isUpdate ? `/publication/update` : '/publication/create';
            if (isUpdate) {
                formData.append('id', publicationId);
            }
            
            // Désactiver le bouton pendant le traitement
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement en cours...';
            
            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Réactiver le bouton
                submitBtn.disabled = false;
                submitBtn.textContent = isUpdate ? 'Mettre à jour' : 'Publier';
                
                if (data.success) {
                    alert(data.message);
                    
                    // Réinitialiser le formulaire si c'était une mise à jour
                    if (isUpdate) {
                        resetCreateForm();
                    } else {
                        publicationForm.reset();
                    }
                    
                    bsCollapse.hide(); // Fermer le formulaire
                    loadPublications(); // Recharger les publications
                } else {
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(error => {
                // Réactiver le bouton en cas d'erreur
                submitBtn.disabled = false;
                submitBtn.textContent = isUpdate ? 'Mettre à jour' : 'Publier';
                
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors du traitement de la publication');
            });
        });
    }
    
    // Chargement initial des publications
    loadPublications();

    // Validation regex pour la recherche utilisateur dans la modal
    var userSearchForm = document.querySelector('#usersModal form.d-flex');
    if (userSearchForm) {
        var userSearchInput = userSearchForm.querySelector('input[type="search"]');
        userSearchForm.addEventListener('submit', function(e) {
            if (userSearchInput && !validateSearch(userSearchInput.value)) {
                e.preventDefault();
            }
        });
        // Si le bouton est de type button, on peut aussi ajouter un click
        var userSearchBtn = userSearchForm.querySelector('button[type="button"]');
        if (userSearchBtn) {
            userSearchBtn.addEventListener('click', function(e) {
                if (userSearchInput && !validateSearch(userSearchInput.value)) {
                    e.preventDefault();
                }
            });
        }
    }

    // --- Gestion changement de rôle (AJAX) ---
    function attachRoleChangeListeners() {
        document.querySelectorAll('.change-role').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('data-userid');
                const newRole = this.getAttribute('data-role');
                const username = this.closest('tr').querySelector('td:first-child').textContent.trim();
                
                if (confirm(`Êtes-vous sûr de vouloir changer le rôle de ${username} à ${formatRoleJS(newRole)} ?`)) {
                    fetch('/superadmin/update-role', {
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
                            // Si un nouveau token est fourni, l'utilisateur a modifié son propre rôle
                            if (data.new_token) {
                                localStorage.setItem('token', data.new_token);
                            }
                            
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

    // Fonction utilitaire pour afficher le rôle en texte
    function formatRoleJS(role) {
        switch (role) {
            case 'super_admin': return 'Super Admin';
            case 'admin': return 'Admin';
            case 'disabled': return 'Désactivé';
            default: return 'Utilisateur';
        }
    }

    // Appel à la fin du DOMContentLoaded
    attachRoleChangeListeners();
    
    /**
     * Charge les publications avec filtrage et pagination
     * @param {Object} params Paramètres de requête (page, limit, type, q)
     */
    function loadPublications(params = {}) {
        // Valeurs par défaut
        const page = params.page || 1;
        const limit = params.limit || 9;
        const type = params.type || 'all';
        const search = params.q || '';
        
        // Mettre à jour l'interface utilisateur (filtres)
        if (type !== 'all') {
            const filterRadio = document.getElementById('filter' + type.charAt(0).toUpperCase() + type.slice(1));
            if (filterRadio) {
                filterRadio.checked = true;
            }
        } else {
            const filterAll = document.getElementById('filterAll');
            if (filterAll) {
                filterAll.checked = true;
            }
        }
        
        if (searchInput) {
            searchInput.value = search;
        }
        
        // Construire l'URL de requête
        let url = `/publication/list?page=${page}&limit=${limit}`;
        if (type !== 'all') url += `&type=${type}`;
        if (search) url += `&q=${encodeURIComponent(search)}`;
        
        // Afficher le spinner
        const container = document.getElementById('publicationsContainer');
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>
        `;
        
        // Charger les publications
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Effacer le conteneur
            container.innerHTML = '';
            
            if (data.publications.length === 0) {
                container.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            Aucune publication trouvée.
                        </div>
                    </div>
                `;
                return;
            }
            
            // Construire les cartes de publications
            const template = document.getElementById('publicationTemplate');
            data.publications.forEach(publication => {
                const clone = document.importNode(template.content, true);
                
                // Définir l'image
                const imageElement = clone.querySelector('.publication-image');
                if (publication.image_path) {
                    imageElement.style.backgroundImage = `url('${publication.image_path}')`;
                } else {
                    // Image par défaut selon le type
                    const defaultImages = {
                        'roman': '/public/images/default-book.jpg',
                        'manhwa': '/public/images/default-manhwa.jpg',
                        'anime': '/public/images/default-anime.jpg'
                    };
                    imageElement.style.backgroundImage = `url('${defaultImages[publication.type] || '/public/images/default.jpg'}')`;
                }
                
                // Remplir les données
                clone.querySelector('.publication-title').textContent = publication.title;
                
                // Définir le badge de type avec une icône
                const badge = clone.querySelector('.publication-type-badge');
                badge.innerHTML = getTypeIcon(publication.type) + ' ' + formatPublicationType(publication.type);
                badge.classList.add(getTypeClass(publication.type));
                
                clone.querySelector('.publication-date').textContent = formatDate(publication.created_at);
                
                // Limiter la description à 100 caractères
                const desc = publication.description;
                clone.querySelector('.publication-description').textContent = 
                    desc.length > 100 ? desc.substring(0, 97) + '...' : desc;
                
                // Définir le bouton 'Consulter' pour ouvrir la modale avec tous les liens
                const linkBtn = clone.querySelector('.publication-link');
                linkBtn.removeAttribute('href');
                linkBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log("DEBUG - Click sur bouton Consulter");
                    console.log("DEBUG - linksModalBody existe:", document.getElementById('linksModalBody') !== null);
                    console.log("DEBUG - linksModal existe:", document.getElementById('linksModal') !== null);
                    
                    if (Array.isArray(publication.links) && publication.links.length > 0) {
                        let html = '<ul class="list-group">';
                        publication.links.forEach((l, idx) => {
                            html += `<li class="list-group-item"><a href="${l}" target="_blank" rel="noopener">${l}</a></li>`;
                        });
                        html += '</ul>';
                        
                        const linksModalBody = document.getElementById('linksModalBody');
                        if (linksModalBody) {
                            linksModalBody.innerHTML = html;
                        } else {
                            console.error("DEBUG - linksModalBody est null");
                            // Fallback: ouvrir le premier lien en nouvelle fenêtre
                            window.open(publication.links[0], '_blank');
                            return;
                        }
                    } else {
                        const linksModalBody = document.getElementById('linksModalBody');
                        if (linksModalBody) {
                            linksModalBody.innerHTML = '<div class="alert alert-warning">Aucun lien disponible.</div>';
                        } else {
                            console.error("DEBUG - linksModalBody est null");
                            return;
                        }
                    }
                    
                    const linksModal = document.getElementById('linksModal');
                    if (linksModal) {
                        try {
                            const modal = new bootstrap.Modal(linksModal);
                            modal.show();
                        } catch (error) {
                            console.error("DEBUG - Erreur lors de l'ouverture de la modale:", error);
                        }
                    } else {
                        console.error("DEBUG - linksModal est null");
                    }
                });
                
                // Ajouter les attributs data pour les actions
                clone.querySelector('.edit-publication').dataset.id = publication.id;
                clone.querySelector('.delete-publication').dataset.id = publication.id;
                
                // Ajouter les event listeners pour les actions
                clone.querySelector('.edit-publication').addEventListener('click', function() {
                    openEditModal(publication.id);
                });
                
                clone.querySelector('.delete-publication').addEventListener('click', function() {
                    console.log('[DEBUG] Click sur bouton suppression, id:', publication.id);
                    
                    // Stocker l'ID de la publication à supprimer
                    publicationToDelete = publication.id;
                    
                    // Afficher la modale de confirmation
                    if (confirmDeleteModal) {
                        const modal = new bootstrap.Modal(confirmDeleteModal);
                        modal.show();
                    } else {
                        // Fallback si la modale n'existe pas
                        if (confirm(`Êtes-vous sûr de vouloir supprimer "${publication.title}" ?`)) {
                            deletePublication(publication.id);
                        } else {
                            console.log('[DEBUG] Suppression annulée par l\'utilisateur');
                        }
                    }
                });
                
                container.appendChild(clone);
            });
            
            // Générer la pagination
            generatePagination(data.pagination, params);
        })
        .catch(error => {
            console.error('Erreur:', error);
            container.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger text-center">
                        Une erreur est survenue lors du chargement des publications.
                    </div>
                </div>
            `;
        });
    }
    
    /**
     * Ouvre la modal d'édition d'une publication
     * @param {string} id ID de la publication
     */
    function openEditModal(id) {
        console.log('DEBUG - openEditModal appelé avec id:', id);
        
        // Récupérer les détails de la publication
        fetch(`/publication/get?id=${id}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(async response => {
            const text = await response.text();
            console.log('Réponse brute:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                alert('Réponse inattendue du serveur (voir console)');
                console.error('Réponse serveur:', text);
                throw e;
            }
        })
        .then(data => {
            if (data.success) {
                const publication = data.publication;
                
                // Déboguer les éléments du DOM
                console.log('DEBUG - createForm existe:', document.getElementById('createForm') !== null);
                console.log('DEBUG - title existe:', document.getElementById('title') !== null);
                console.log('DEBUG - description existe:', document.getElementById('description') !== null);
                console.log('DEBUG - linksContainer existe:', document.getElementById('linksContainer') !== null);
                console.log('DEBUG - additionalLinks existe:', document.getElementById('additionalLinks') !== null);
                
                const createForm = document.getElementById('createForm');
                if (!createForm) {
                    console.error('DEBUG - createForm est null');
                    alert("Erreur: Le formulaire d'édition est introuvable.");
                    return;
                }
                
                // Afficher le formulaire via Bootstrap Collapse
                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(createForm, {toggle: false});
                bsCollapse.show();
                
                // Cacher la liste
                document.getElementById('publicationList').style.display = 'none';
                
                // Remplir le formulaire avec les données de la publication
                document.getElementById('title').value = publication.title;
                document.getElementById('description').value = publication.description;
                
                // Sélectionner le bon type par bouton radio
                const typeRadio = document.getElementById('type' + publication.type.charAt(0).toUpperCase() + publication.type.slice(1));
                if (typeRadio) {
                    typeRadio.checked = true;
                }
                
                // Gérer les liens multiples dans le formulaire
                const linksContainer = document.getElementById('linksContainer');
                const additionalLinks = document.getElementById('additionalLinks');
                const firstLinkInput = linksContainer.querySelector('input[name="links[]"]');
                firstLinkInput.value = '';
                additionalLinks.innerHTML = '';
                if (Array.isArray(publication.links) && publication.links.length > 0) {
                    // Premier lien dans le premier champ
                    firstLinkInput.value = publication.links[0] || '';
                    // Les autres liens dans des champs supplémentaires
                    for (let i = 1; i < publication.links.length; i++) {
                        const div = document.createElement('div');
                        div.className = 'input-group mb-2';
                        div.innerHTML = `
                            <input type="url" class="form-control" name="links[]" placeholder="https://exemple.com/roman" required value="${publication.links[i]}">
                            <button class="btn btn-outline-danger remove-link-btn" type="button">Supprimer</button>
                        `;
                        additionalLinks.appendChild(div);
                        div.querySelector('.remove-link-btn').addEventListener('click', function() {
                            div.remove();
                        });
                    }
                } else {
                    firstLinkInput.value = '';
                }
                
                // Ajouter un aperçu de l'image si elle existe
                const imageContainer = document.querySelector('.mb-3:has(#image)');
                if (imageContainer && publication.image_path) {
                    // Supprimer l'aperçu existant s'il y en a un
                    const existingPreview = imageContainer.querySelector('.image-preview');
                    if (existingPreview) {
                        existingPreview.remove();
                    }
                    
                    // Créer un nouvel aperçu
                    const preview = document.createElement('div');
                    preview.className = 'image-preview mb-2';
                    preview.innerHTML = `
                        <img src="${publication.image_path}" alt="${publication.title}" class="img-thumbnail" style="max-height: 150px;">
                        <p class="text-muted small mt-1">Image actuelle. Téléchargez une nouvelle image pour la remplacer.</p>
                    `;
                    
                    // Insérer avant l'input de fichier
                    imageContainer.insertBefore(preview, imageContainer.querySelector('#image'));
                }
                
                // Changer le texte du bouton et l'action du formulaire
                const submitBtn = createForm.querySelector('button[type="submit"]');
                submitBtn.textContent = 'Mettre à jour';
                submitBtn.dataset.mode = 'update';
                submitBtn.dataset.id = publication.id;
                
                // Ajouter un bouton d'annulation
                if (!createForm.querySelector('.btn-cancel')) {
                    const cancelBtn = document.createElement('button');
                    cancelBtn.type = 'button';
                    cancelBtn.className = 'btn btn-secondary me-2 btn-cancel';
                    cancelBtn.textContent = 'Annuler';
                    cancelBtn.addEventListener('click', function() {
                        resetCreateForm();
                        document.getElementById('publicationList').style.display = '';
                        bsCollapse.hide();
                    });
                    
                    submitBtn.parentNode.insertBefore(cancelBtn, submitBtn);
                }
                
                // Changer le titre du formulaire
                const formTitle = createForm.querySelector('h5');
                if (formTitle) {
                    formTitle.textContent = 'Modifier la publication';
                }
                
                // Faire défiler jusqu'au formulaire
                createForm.scrollIntoView({ behavior: 'smooth' });
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue lors de la récupération des détails de la publication');
        });
    }
    
    /**
     * Supprime une publication
     * @param {string} id ID de la publication
     */
    function deletePublication(id) {
        console.log('[DEBUG] deletePublication appelé avec id:', id);
        
        // Suppression du confirm() ici car nous utilisons maintenant la modale
        
        const formData = new FormData();
        formData.append('id', id);
        console.log('[DEBUG] Envoi requête fetch POST /publication/delete avec id:', id);
        fetch('/publication/delete', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('[DEBUG] Réponse reçue de /publication/delete');
            return response.json();
        })
        .then(data => {
            console.log('[DEBUG] Réponse JSON:', data);
            if (data.success) {
                alert(data.message);
                
                // Forcer un rechargement complet au lieu de loadPublications() 
                // pour éviter les problèmes sur certains navigateurs
                window.location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            console.error('[DEBUG] Erreur dans fetch /publication/delete:', error);
            alert('Une erreur est survenue lors de la suppression de la publication');
        });
    }
    
    /**
     * Génère la pagination pour les publications
     * @param {Object} pagination Métadonnées de pagination
     * @param {Object} currentParams Paramètres actuels de requête
     */
    function generatePagination(pagination, currentParams) {
        const paginationElement = document.getElementById('publicationPagination');
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
    
    /**
     * Formate le type de publication pour l'affichage
     * @param {string} type Type de publication
     * @return {string} Type formaté
     */
    function formatPublicationType(type) {
        switch (type) {
            case 'roman': return 'Roman';
            case 'manhwa': return 'Manhwa';
            case 'anime': return 'Animé';
            default: return type;
        }
    }
    
    /**
     * Récupère la classe CSS pour un type de publication
     * @param {string} type Type de publication
     * @return {string} Classe CSS
     */
    function getTypeClass(type) {
        switch (type) {
            case 'roman': return 'bg-primary';
            case 'manhwa': return 'bg-success';
            case 'anime': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }
    
    /**
     * Récupère l'icône pour un type de publication
     * @param {string} type Type de publication
     * @return {string} Code HTML de l'icône
     */
    function getTypeIcon(type) {
        switch (type) {
            case 'roman': return '<i class="bi bi-book"></i>';
            case 'manhwa': return '<i class="bi bi-file-earmark-image"></i>';
            case 'anime': return '<i class="bi bi-film"></i>';
            default: return '<i class="bi bi-question-circle"></i>';
        }
    }
    
    /**
     * Formate une date pour l'affichage
     * @param {string} dateString Date au format chaîne
     * @return {string} Date formatée
     */
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    // Gestion des liens multiples
    const addLinkBtn = document.getElementById('addLinkBtn');
    const additionalLinks = document.getElementById('additionalLinks');

    if (addLinkBtn && additionalLinks) {
        addLinkBtn.addEventListener('click', function() {
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <input type="url" class="form-control" name="links[]" placeholder="https://exemple.com/roman" required>
                <button class="btn btn-outline-danger remove-link-btn" type="button">Supprimer</button>
            `;
            additionalLinks.appendChild(div);

            div.querySelector('.remove-link-btn').addEventListener('click', function() {
                div.remove();
            });
        });
    }
}); 
