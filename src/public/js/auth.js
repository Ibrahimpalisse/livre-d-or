/**
 * Fonction pour vider le cache lors de la déconnexion
 */
function clearCache() {
    // Essayer de vider le cache du navigateur
    if ('caches' in window) {
        caches.keys().then(function(names) {
            for (let name of names) caches.delete(name);
        });
    }
    
    // Supprimer tous les cookies d'authentification
    document.cookie.split(";").forEach(function(c) {
        document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
    });
    
    // Ajouter un timestamp à l'URL pour éviter les problèmes de cache
    const logoutUrl = "/logout?_=" + new Date().getTime();
    
    // Rediriger vers la déconnexion avec un rafraîchissement complet
    window.location.href = logoutUrl;
    
    // Empêcher la navigation par défaut du lien
    return false;
} 
 