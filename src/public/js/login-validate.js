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
    var form = document.getElementById('loginForm');
    if (!form) return;
    var usernameInput = document.getElementById('username');
    var passwordInput = document.getElementById('password');

    // Afficher/masquer le mot de passe
    var togglePassword = document.getElementById('togglePassword');
    var togglePasswordIcon = document.getElementById('togglePasswordIcon');
    if (togglePassword && passwordInput && togglePasswordIcon) {
        togglePassword.addEventListener('click', function () {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            togglePasswordIcon.classList.toggle('bi-eye');
            togglePasswordIcon.classList.toggle('bi-eye-slash');
        });
    }

    // Validation silencieuse : pas d'affichage d'erreur, juste blocage de la soumission
    form.addEventListener('submit', function (e) {
        var username = usernameInput.value;
        var password = passwordInput.value;
        if (!validateUsername(username) || !validatePassword(password)) {
            e.preventDefault();
        }
    });
}); 