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
    var form = document.getElementById('registerForm');
    if (!form) return;
    var usernameInput = document.getElementById('username');
    var passwordInput = document.getElementById('password');
    var passwordConfirmInput = document.getElementById('password_confirm');

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

    usernameInput.addEventListener('input', function () {
        showValidation(this, validateUsername(this.value), "Nom d'utilisateur invalide (3-20 caractères, lettres, chiffres, tirets).");
    });
    passwordInput.addEventListener('input', function () {
        showValidation(this, validatePassword(this.value), "Mot de passe invalide (8 caractères, 1 majuscule, 1 minuscule, 1 chiffre).");
        // Valide aussi la confirmation si elle a déjà été saisie
        if (passwordConfirmInput.value.length > 0) {
            showValidation(passwordConfirmInput, passwordInput.value === passwordConfirmInput.value, "Les mots de passe ne correspondent pas.");
        }
    });
    passwordConfirmInput.addEventListener('input', function () {
        showValidation(this, passwordInput.value === this.value, "Les mots de passe ne correspondent pas.");
    });

    form.addEventListener('submit', function (e) {
        var username = usernameInput.value;
        var password = passwordInput.value;
        var password_confirm = passwordConfirmInput.value;
        var errors = [];
        if (!validateUsername(username)) {
            errors.push("Nom d'utilisateur invalide (3-20 caractères, lettres, chiffres, tirets).");
            showValidation(usernameInput, false, "Nom d'utilisateur invalide (3-20 caractères, lettres, chiffres, tirets).");
        }
        if (!validatePassword(password)) {
            errors.push("Mot de passe invalide (8 caractères, 1 majuscule, 1 minuscule, 1 chiffre).");
            showValidation(passwordInput, false, "Mot de passe invalide (8 caractères, 1 majuscule, 1 minuscule, 1 chiffre).");
        }
        if (password !== password_confirm) {
            errors.push("Les mots de passe ne correspondent pas.");
            showValidation(passwordConfirmInput, false, "Les mots de passe ne correspondent pas.");
        }
        if (errors.length > 0) {
            e.preventDefault();
        }
    });
}); 