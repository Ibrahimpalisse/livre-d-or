/**
 * Fichier contenant les expressions régulières et les fonctions de validation communes
 */

// Expressions régulières
const PATTERNS = {
    // Nom d'utilisateur : entre 3 et 20 caractères alphanumériques et tirets bas
    USERNAME: /^[a-zA-Z0-9_]{3,20}$/,
    
    // Mot de passe : au moins 8 caractères, dont au moins 1 majuscule, 1 minuscule et 1 chiffre
    PASSWORD: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/,
    
    // Email : format valide d'email
    EMAIL: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
    
    // Date : format JJ/MM/AAAA
    DATE: /^(\d{2})\/(\d{2})\/(\d{4})$/,
    
    // Numéro de téléphone : format international avec +, ou format français avec 0
    PHONE: /^(\+\d{1,3}\s?)?(\d{9,10})$/,
    
    // Code postal français : 5 chiffres
    POSTAL_CODE: /^\d{5}$/,
    
    // Numéro de carte de crédit (sans espaces ou tirets)
    CREDIT_CARD: /^\d{16}$/,
    
    // URL : protocole facultatif, domaine obligatoire
    URL: /^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/.*)?$/
};

/**
 * Valide un nom d'utilisateur
 * @param {string} username - Le nom d'utilisateur à valider
 * @returns {boolean} - True si le nom d'utilisateur est valide, false sinon
 */
function validateUsername(username) {
    return PATTERNS.USERNAME.test(username);
}

/**
 * Valide un mot de passe
 * @param {string} password - Le mot de passe à valider
 * @returns {boolean} - True si le mot de passe est valide, false sinon
 */
function validatePassword(password) {
    return PATTERNS.PASSWORD.test(password);
}

/**
 * Valide un email
 * @param {string} email - L'email à valider
 * @returns {boolean} - True si l'email est valide, false sinon
 */
function validateEmail(email) {
    return PATTERNS.EMAIL.test(email);
}

/**
 * Valide une date au format JJ/MM/AAAA
 * @param {string} date - La date à valider
 * @returns {boolean} - True si la date est valide, false sinon
 */
function validateDate(date) {
    if (!PATTERNS.DATE.test(date)) {
        return false;
    }
    
    const parts = date.split('/');
    const day = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10);
    const year = parseInt(parts[2], 10);
    
    // Vérifier que le mois est valide
    if (month < 1 || month > 12) {
        return false;
    }
    
    // Vérifier que le jour est valide pour le mois
    const daysInMonth = [31, (isLeapYear(year) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    return day > 0 && day <= daysInMonth[month - 1];
}

/**
 * Vérifie si une année est bissextile
 * @param {number} year - L'année à vérifier
 * @returns {boolean} - True si l'année est bissextile, false sinon
 */
function isLeapYear(year) {
    return (year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0);
}

// Exporter les fonctions si on est dans un environnement Node.js
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        PATTERNS,
        validateUsername,
        validatePassword,
        validateEmail,
        validateDate,
        isLeapYear
    };
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