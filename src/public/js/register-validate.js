function showValidation(input, isValid, message) {
    const feedback = document.getElementById(input.id + 'Error');
    if (isValid) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        feedback.textContent = '';
    } else {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        feedback.textContent = message;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const registerForm = document.getElementById('registerForm');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirm');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const togglePasswordIcon = document.getElementById('togglePasswordIcon');

    // Regex pour la validation
    const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;

    // Afficher/masquer le mot de passe
    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Changer l'icône
            if (type === 'text') {
                togglePasswordIcon.classList.remove('bi-eye');
                togglePasswordIcon.classList.add('bi-eye-slash');
            } else {
                togglePasswordIcon.classList.remove('bi-eye-slash');
                togglePasswordIcon.classList.add('bi-eye');
            }
        });
    }

    // Validation en temps réel du nom d'utilisateur
    usernameInput.addEventListener('input', function() {
        const username = usernameInput.value.trim();
        const usernameError = document.getElementById('usernameError');
        
        if (username === '') {
            setInvalid(usernameInput, usernameError, 'Le nom d\'utilisateur est requis.');
        } else if (!usernameRegex.test(username)) {
            setInvalid(usernameInput, usernameError, 'Le nom d\'utilisateur doit contenir entre 3 et 20 caractères alphanumériques.');
        } else {
            setValid(usernameInput, usernameError);
        }
    });

    // Validation en temps réel du mot de passe
    passwordInput.addEventListener('input', function() {
        const password = passwordInput.value;
        const passwordError = document.getElementById('passwordError');
        
        if (password === '') {
            setInvalid(passwordInput, passwordError, 'Le mot de passe est requis.');
        } else if (!passwordRegex.test(password)) {
            setInvalid(passwordInput, passwordError, 'Le mot de passe doit contenir au moins 8 caractères, dont une majuscule, une minuscule et un chiffre.');
        } else {
            setValid(passwordInput, passwordError);
        }
        
        // Vérifier la correspondance avec la confirmation
        if (passwordConfirmInput.value !== '') {
            const passwordConfirmError = document.getElementById('password_confirmError');
            
            if (passwordConfirmInput.value !== password) {
                setInvalid(passwordConfirmInput, passwordConfirmError, 'Les mots de passe ne correspondent pas.');
            } else {
                setValid(passwordConfirmInput, passwordConfirmError);
            }
        }
    });

    // Validation en temps réel de la confirmation du mot de passe
    passwordConfirmInput.addEventListener('input', function() {
        const passwordConfirm = passwordConfirmInput.value;
        const password = passwordInput.value;
        const passwordConfirmError = document.getElementById('password_confirmError');
        
        if (passwordConfirm === '') {
            setInvalid(passwordConfirmInput, passwordConfirmError, 'La confirmation du mot de passe est requise.');
        } else if (passwordConfirm !== password) {
            setInvalid(passwordConfirmInput, passwordConfirmError, 'Les mots de passe ne correspondent pas.');
        } else {
            setValid(passwordConfirmInput, passwordConfirmError);
        }
    });

    // Soumission du formulaire
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = usernameInput.value.trim();
            const password = passwordInput.value;
            const passwordConfirm = passwordConfirmInput.value;
            
            // Validation finale
            let isValid = true;
            
            // Validation du nom d'utilisateur
            if (username === '' || !usernameRegex.test(username)) {
                isValid = false;
            }
            
            // Validation du mot de passe
            if (password === '' || !passwordRegex.test(password)) {
                isValid = false;
            }
            
            // Validation de la confirmation du mot de passe
            if (passwordConfirm === '' || passwordConfirm !== password) {
                isValid = false;
            }
            
            if (isValid) {
                // Création de FormData pour envoyer les données
                const formData = new FormData(registerForm);
                
                // Envoi des données au serveur par AJAX
                fetch('/register', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        throw new Error('La réponse du serveur n\'est pas au format JSON');
                    }
                })
                .then(data => {
                    if (data.success) {
                        // Inscription réussie
                        showAlert('success', data.message || 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
                        
                        // Redirection après 1.5 secondes
                        setTimeout(() => {
                            window.location.href = data.redirect || '/login';
                        }, 1500);
                    } else {
                        // Erreurs spécifiques aux champs
                        if (data.errors) {
                            Object.keys(data.errors).forEach(field => {
                                const input = document.getElementById(field);
                                const errorElement = document.getElementById(field + 'Error');
                                
                                if (input && errorElement) {
                                    setInvalid(input, errorElement, data.errors[field]);
                                }
                            });
                        }
                        
                        // Message d'erreur général
                        if (data.message) {
                            showAlert('danger', data.message);
                        }
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showAlert('danger', 'Une erreur est survenue lors de l\'inscription.');
                });
            }
        });
    }

    // Fonctions utilitaires
    function setInvalid(input, errorElement, message) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        if (errorElement) {
            errorElement.textContent = message;
        }
    }

    function setValid(input, errorElement) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        if (errorElement) {
            errorElement.textContent = '';
        }
    }

    function showAlert(type, message) {
        // Supprimer les alertes existantes
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Créer une nouvelle alerte
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Insérer l'alerte avant le formulaire
        registerForm.parentNode.insertBefore(alertDiv, registerForm);
        
        // Faire disparaître l'alerte après 5 secondes
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, 5000);
    }
}); 