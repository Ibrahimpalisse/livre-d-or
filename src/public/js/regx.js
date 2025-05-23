// Validation du nom d'utilisateur : lettres, chiffres, tirets, 3 à 20 caractères
function validateUsername(username) {
    return /^[a-zA-Z0-9_-]{3,20}$/.test(username);
}

// Validation du mot de passe : au moins 8 caractères, une majuscule, une minuscule, un chiffre
function validatePassword(password) {
    return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(password);
}

// Validation d'une URL
function validateURL(url) {
    return /^(https?:\/\/)?([\w\-]+\.)+[\w\-]+(\/[\w\-._~:/?#[\]@!$&'()*+,;=]*)?$/.test(url);
}

// Validation d'un commentaire : 1 à 500 caractères, pas vide
function validateComment(comment) {
    return comment && comment.length > 0 && comment.length <= 500;
}

// Validation de la barre de recherche : lettres, chiffres, espaces, 2 à 50 caractères
function validateSearch(query) {
    return /^[a-zA-Z0-9À-ÿ'’"()\[\]{}.,:;!?\- ]{2,50}$/.test(query.trim());
}

// Validation du titre : lettres, chiffres, espaces, 3 à 100 caractères
function validateTitle(title) {
    return /^[a-zA-Z0-9À-ÿ'’"()\[\]{}.,:;!?\- ]{3,100}$/.test(title.trim());
}

// Validation de la description : 1 à 1000 caractères
function validateDescription(description) {
    return description && description.length > 0 && description.length <= 1000;
}

// Validation du type : roman, manhwa, anime uniquement
function validateType(type) {
    return ['roman', 'manhwa', 'anime'].includes(type);
}

// Validation de l'image : extensions jpg, jpeg, png, gif, webp
function validateImage(filename) {
    return /\.(jpg|jpeg|png|gif|webp)$/i.test(filename);
}

// Fonction pour échapper les caractères spéciaux HTML
function escapeHtml(text) {
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/\"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Exemple d'utilisation :
// if (!validateUsername(username)) { alert('Nom d\'utilisateur invalide'); }
// if (!validatePassword(password)) { alert('Mot de passe invalide'); }
// if (!validateURL(url)) { alert('Lien invalide'); }
// if (!validateComment(comment)) { alert('Commentaire invalide'); }
// if (!validateSearch(query)) { alert('Recherche invalide'); }
// if (!validateTitle(title)) { alert('Titre invalide'); }
// if (!validateDescription(description)) { alert('Description invalide'); }
// if (!validateType(type)) { alert('Type invalide'); }
// if (!validateImage(filename)) { alert('Image invalide'); } 