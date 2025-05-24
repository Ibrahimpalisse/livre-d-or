function showValidation(input, isValid, message, errorId) {
    const feedback = document.getElementById(errorId);
    if (isValid) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        if (feedback) feedback.textContent = '';
    } else {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        if (feedback) feedback.textContent = message;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    var createForm = document.querySelector('#createForm form[action="/dashboard"][method="post"]');
    if (createForm) {
        var titleInput = createForm.querySelector('input[name="title"]');
        var imageInput = createForm.querySelector('input[name="image"]');
        var linkInput = createForm.querySelector('input[name="link"]');

        // Validation en temps réel pour le titre
        if (titleInput) {
            titleInput.addEventListener('input', function () {
                showValidation(
                    titleInput,
                    validateTitle(titleInput.value),
                    'Titre invalide (3-100 caractères, lettres, chiffres, ponctuation).',
                    'titleError'
                );
            });
        }

        // Validation en temps réel pour l'image
        if (imageInput) {
            imageInput.addEventListener('change', function () {
                let valid = true;
                let message = '';
                if (imageInput.files.length > 0) {
                    var file = imageInput.files[0];
                    if (!validateImage(file.name)) {
                        valid = false;
                        message = 'Format d\'image invalide (jpg, jpeg, png, gif, webp).';
                    } else if (file.size > 2 * 1024 * 1024) {
                        valid = false;
                        message = 'Image trop volumineuse (max 2 Mo).';
                    }
                }
                showValidation(imageInput, valid, message, 'imageError');
            });
        }

        // Validation en temps réel pour le lien
        if (linkInput) {
            linkInput.addEventListener('input', function () {
                showValidation(
                    linkInput,
                    validateURL(linkInput.value),
                    'Lien invalide (doit être une URL valide).',
                    'linkError'
                );
            });
        }

        createForm.addEventListener('submit', function(e) {
            var valid = true;
            // Titre
            if (titleInput && !validateTitle(titleInput.value)) {
                showValidation(titleInput, false, 'Titre invalide (3-100 caractères, lettres, chiffres, ponctuation).', 'titleError');
                valid = false;
            }
            // Image
            if (imageInput && imageInput.files.length > 0) {
                var file = imageInput.files[0];
                if (!validateImage(file.name)) {
                    showValidation(imageInput, false, 'Format d\'image invalide (jpg, jpeg, png, gif, webp).', 'imageError');
                    valid = false;
                } else if (file.size > 2 * 1024 * 1024) {
                    showValidation(imageInput, false, 'Image trop volumineuse (max 2 Mo).', 'imageError');
                    valid = false;
                }
            }
            // Lien
            if (linkInput && !validateURL(linkInput.value)) {
                showValidation(linkInput, false, 'Lien invalide (doit être une URL valide).', 'linkError');
                valid = false;
            }
            if (!valid) {
                e.preventDefault();
            }
        });
    }
}); 