console.log('login-validate.js chargé');
// Alerte supprimée car plus nécessaire

// Ajouter un style pour s'assurer que .invalid-feedback s'affiche
document.addEventListener('DOMContentLoaded', function() {
    // Créer une balise style
    const style = document.createElement('style');
    style.innerHTML = `
        .invalid-feedback {
            display: block !important;
            color: red !important;
            font-weight: bold !important;
            margin-top: 5px !important;
        }
        .is-invalid {
            border-color: red !important;
            background-color: rgba(255, 0, 0, 0.05) !important;
        }
    `;
    // Ajouter ce style au head
    document.head.appendChild(style);
});

function showValidation(input, isValid, message) {
    const errorElement = document.getElementById(input.id + 'Error');
    console.log('showValidation', input, isValid, message);
    console.log('errorElement', errorElement);
    if (!errorElement) return;
    
    if (isValid) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        errorElement.textContent = '';
    } else {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        errorElement.textContent = message;
        // Force l'affichage du message d'erreur
        errorElement.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const togglePasswordIcon = document.getElementById('togglePasswordIcon');

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
        
        if (username === '') {
            showValidation(usernameInput, false, 'Le nom d\'utilisateur est requis.');
        } else {
            showValidation(usernameInput, true, '');
        }
    });

    // Validation en temps réel du mot de passe
    passwordInput.addEventListener('input', function() {
        const password = passwordInput.value;
        
        if (password === '') {
            showValidation(passwordInput, false, 'Le mot de passe est requis.');
        } else {
            showValidation(passwordInput, true, '');
        }
    });

    // Soumission du formulaire
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = usernameInput.value.trim();
            const password = passwordInput.value;
            
            // Validation finale
            let isValid = true;
            
            // Validation du nom d'utilisateur
            if (username === '') {
                showValidation(usernameInput, false, 'Le nom d\'utilisateur est requis.');
                isValid = false;
            }
            
            // Validation du mot de passe
            if (password === '') {
                showValidation(passwordInput, false, 'Le mot de passe est requis.');
                isValid = false;
            }
            
            if (isValid) {
                // Création de FormData pour envoyer les données
                const formData = new FormData(loginForm);
                // S'assurer que le token CSRF est inclus
                const csrfToken = document.querySelector('input[name="csrf_token"]').value;
                formData.append('csrf_token', csrfToken);
                
                // Envoi des données au serveur par AJAX
                fetch('/login', {
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
                        return response.text().then(text => {
                            console.error('Réponse non-JSON:', text);
                            throw new Error('La réponse du serveur n\'est pas au format JSON');
                        });
                    }
                })
                .then(data => {
                    if (data.success) {
                        // Connexion réussie
                        showAlert('success', data.message || 'Connexion réussie !');
                        
                        // Redirection après 1 seconde
                        setTimeout(() => {
                            window.location.href = data.redirect || '/dashboard';
                        }, 1000);
                    } else {
                        // Erreurs spécifiques aux champs
                        if (data.errors) {
                            Object.keys(data.errors).forEach(field => {
                                const input = document.getElementById(field);
                                if (input) {
                                    showValidation(input, false, data.errors[field]);
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
                    showAlert('danger', 'Une erreur est survenue lors de la connexion.');
                });
            }
        });
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
        loginForm.parentNode.insertBefore(alertDiv, loginForm);
        
        // Faire disparaître l'alerte après 5 secondes
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, 5000);
    }
}); 