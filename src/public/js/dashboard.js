document.addEventListener('DOMContentLoaded', function () {
    var createForm = document.getElementById('createForm');
    var publicationList = document.getElementById('publicationList');
    var createBtn = document.querySelector('[data-bs-target="#createForm"]');
    if (!createForm) return;

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

    // Validation regex pour la recherche de publication
    var searchForm = document.querySelector('form[action="/dashboard"][method="get"]');
    if (searchForm) {
        var searchInput = searchForm.querySelector('input[name="q"]');
        searchForm.addEventListener('submit', function(e) {
            if (searchInput && !validateSearch(searchInput.value)) {
                e.preventDefault();
            }
        });
    }

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
}); 