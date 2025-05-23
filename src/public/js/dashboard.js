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
}); 