/**
 * Système de validation des publications
 */
document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les boutons de validation
    const validateButtons = document.querySelectorAll('.validate-btn');
    const invalidateButtons = document.querySelectorAll('.invalidate-btn');
    
    // Ajouter des écouteurs d'événements pour les boutons de validation
    validateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const publicationId = this.getAttribute('data-publication-id');
            validatePublication(publicationId, true);
        });
    });
    
    // Ajouter des écouteurs d'événements pour les boutons d'invalidation
    invalidateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const publicationId = this.getAttribute('data-publication-id');
            validatePublication(publicationId, false);
        });
    });
    
    // Fonction pour valider une publication
    function validatePublication(publicationId, isValid) {
        // Désactiver les boutons pendant le traitement
        toggleValidationButtons(publicationId, true);
        
        // Envoyer la requête au serveur
        fetch('/publication/validate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                publication_id: publicationId,
                is_valid: isValid
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Mettre à jour les compteurs
                updateValidationStats(publicationId, data.stats);
                
                // Mettre à jour l'état des boutons
                updateValidationButtonsState(publicationId, isValid);
                
                // Afficher le message de succès
                showMessage(data.message, 'success');
            } else {
                showMessage(data.message || 'Une erreur est survenue', 'danger');
            }
        })
        .catch(error => {
            console.error('Erreur lors de la validation:', error);
            showMessage('Une erreur est survenue lors de la validation', 'danger');
        })
        .finally(() => {
            // Réactiver les boutons
            toggleValidationButtons(publicationId, false);
        });
    }
    
    // Fonction pour mettre à jour les statistiques de validation
    function updateValidationStats(publicationId, stats) {
        console.log('Mise à jour des statistiques:', stats);
        
        // Mettre à jour les compteurs sur la carte
        const validCount = document.getElementById(`validCount-${publicationId}`);
        const invalidCount = document.getElementById(`invalidCount-${publicationId}`);
        
        if (validCount) validCount.textContent = stats.valid_count;
        if (invalidCount) invalidCount.textContent = stats.invalid_count;
        
        // Mettre à jour les statistiques dans la modale
        const validPercentage = document.getElementById('validPercentage');
        const invalidPercentage = document.getElementById('invalidPercentage');
        const validCountModal = document.getElementById('validCount');
        const invalidCountModal = document.getElementById('invalidCount');
        
        if (validPercentage && invalidPercentage && validCountModal && invalidCountModal) {
            validCountModal.textContent = stats.valid_count;
            invalidCountModal.textContent = stats.invalid_count;
            
            validPercentage.style.width = stats.valid_percentage + '%';
            validPercentage.setAttribute('aria-valuenow', stats.valid_percentage);
            validPercentage.textContent = stats.valid_percentage + '%';
            
            invalidPercentage.style.width = stats.invalid_percentage + '%';
            invalidPercentage.setAttribute('aria-valuenow', stats.invalid_percentage);
            invalidPercentage.textContent = stats.invalid_percentage + '%';
        }
    }
    
    // Fonction pour désactiver/activer les boutons de validation
    function toggleValidationButtons(publicationId, disabled) {
        const validateBtn = document.querySelector(`.validate-btn[data-publication-id="${publicationId}"]`);
        const invalidateBtn = document.querySelector(`.invalidate-btn[data-publication-id="${publicationId}"]`);
        
        if (validateBtn) validateBtn.disabled = disabled;
        if (invalidateBtn) invalidateBtn.disabled = disabled;
    }
    
    // Fonction pour mettre à jour l'état des boutons après une validation
    function updateValidationButtonsState(publicationId, isValid) {
        const validateBtn = document.querySelector(`.validate-btn[data-publication-id="${publicationId}"]`);
        const invalidateBtn = document.querySelector(`.invalidate-btn[data-publication-id="${publicationId}"]`);
        
        if (validateBtn && invalidateBtn) {
            // Marquer le bouton actif comme sélectionné
            validateBtn.classList.toggle('btn-success', isValid);
            validateBtn.classList.toggle('btn-outline-success', !isValid);
            
            invalidateBtn.classList.toggle('btn-danger', !isValid);
            invalidateBtn.classList.toggle('btn-outline-danger', isValid);
        }
    }
    
    // Fonction pour afficher un message
    function showMessage(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed bottom-0 end-0 m-3`;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Supprimer l'alerte après 3 secondes
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => {
                alertDiv.remove();
            }, 150);
        }, 3000);
    }
}); 

document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les boutons de validation
    const validateButtons = document.querySelectorAll('.validate-btn');
    const invalidateButtons = document.querySelectorAll('.invalidate-btn');
    
    // Ajouter des écouteurs d'événements pour les boutons de validation
    validateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const publicationId = this.getAttribute('data-publication-id');
            validatePublication(publicationId, true);
        });
    });
    
    // Ajouter des écouteurs d'événements pour les boutons d'invalidation
    invalidateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const publicationId = this.getAttribute('data-publication-id');
            validatePublication(publicationId, false);
        });
    });
    
    // Fonction pour valider une publication
    function validatePublication(publicationId, isValid) {
        // Désactiver les boutons pendant le traitement
        toggleValidationButtons(publicationId, true);
        
        // Envoyer la requête au serveur
        fetch('/publication/validate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                publication_id: publicationId,
                is_valid: isValid
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Mettre à jour les compteurs
                updateValidationStats(publicationId, data.stats);
                
                // Mettre à jour l'état des boutons
                updateValidationButtonsState(publicationId, isValid);
                
                // Afficher le message de succès
                showMessage(data.message, 'success');
            } else {
                showMessage(data.message || 'Une erreur est survenue', 'danger');
            }
        })
        .catch(error => {
            console.error('Erreur lors de la validation:', error);
            showMessage('Une erreur est survenue lors de la validation', 'danger');
        })
        .finally(() => {
            // Réactiver les boutons
            toggleValidationButtons(publicationId, false);
        });
    }
    
    // Fonction pour mettre à jour les statistiques de validation
    function updateValidationStats(publicationId, stats) {
        console.log('Mise à jour des statistiques:', stats);
        
        // Mettre à jour les compteurs sur la carte
        const validCount = document.getElementById(`validCount-${publicationId}`);
        const invalidCount = document.getElementById(`invalidCount-${publicationId}`);
        
        if (validCount) validCount.textContent = stats.valid_count;
        if (invalidCount) invalidCount.textContent = stats.invalid_count;
        
        // Mettre à jour les statistiques dans la modale
        const validPercentage = document.getElementById('validPercentage');
        const invalidPercentage = document.getElementById('invalidPercentage');
        const validCountModal = document.getElementById('validCount');
        const invalidCountModal = document.getElementById('invalidCount');
        
        if (validPercentage && invalidPercentage && validCountModal && invalidCountModal) {
            validCountModal.textContent = stats.valid_count;
            invalidCountModal.textContent = stats.invalid_count;
            
            validPercentage.style.width = stats.valid_percentage + '%';
            validPercentage.setAttribute('aria-valuenow', stats.valid_percentage);
            validPercentage.textContent = stats.valid_percentage + '%';
            
            invalidPercentage.style.width = stats.invalid_percentage + '%';
            invalidPercentage.setAttribute('aria-valuenow', stats.invalid_percentage);
            invalidPercentage.textContent = stats.invalid_percentage + '%';
        }
    }
    
    // Fonction pour désactiver/activer les boutons de validation
    function toggleValidationButtons(publicationId, disabled) {
        const validateBtn = document.querySelector(`.validate-btn[data-publication-id="${publicationId}"]`);
        const invalidateBtn = document.querySelector(`.invalidate-btn[data-publication-id="${publicationId}"]`);
        
        if (validateBtn) validateBtn.disabled = disabled;
        if (invalidateBtn) invalidateBtn.disabled = disabled;
    }
    
    // Fonction pour mettre à jour l'état des boutons après une validation
    function updateValidationButtonsState(publicationId, isValid) {
        const validateBtn = document.querySelector(`.validate-btn[data-publication-id="${publicationId}"]`);
        const invalidateBtn = document.querySelector(`.invalidate-btn[data-publication-id="${publicationId}"]`);
        
        if (validateBtn && invalidateBtn) {
            // Marquer le bouton actif comme sélectionné
            validateBtn.classList.toggle('btn-success', isValid);
            validateBtn.classList.toggle('btn-outline-success', !isValid);
            
            invalidateBtn.classList.toggle('btn-danger', !isValid);
            invalidateBtn.classList.toggle('btn-outline-danger', isValid);
        }
    }
    
    // Fonction pour afficher un message
    function showMessage(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed bottom-0 end-0 m-3`;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Supprimer l'alerte après 3 secondes
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => {
                alertDiv.remove();
            }, 150);
        }, 3000);
    }
}); 
 /* Système de validation des publications
 */
document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les boutons de validation
    const validateButtons = document.querySelectorAll('.validate-btn');
    const invalidateButtons = document.querySelectorAll('.invalidate-btn');
    
    // Ajouter des écouteurs d'événements pour les boutons de validation
    validateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const publicationId = this.getAttribute('data-publication-id');
            validatePublication(publicationId, true);
        });
    });
    
    // Ajouter des écouteurs d'événements pour les boutons d'invalidation
    invalidateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const publicationId = this.getAttribute('data-publication-id');
            validatePublication(publicationId, false);
        });
    });
    
    // Fonction pour valider une publication
    function validatePublication(publicationId, isValid) {
        // Désactiver les boutons pendant le traitement
        toggleValidationButtons(publicationId, true);
        
        // Envoyer la requête au serveur
        fetch('/publication/validate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                publication_id: publicationId,
                is_valid: isValid
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Mettre à jour les compteurs
                updateValidationStats(publicationId, data.stats);
                
                // Mettre à jour l'état des boutons
                updateValidationButtonsState(publicationId, isValid);
                
                // Afficher le message de succès
                showMessage(data.message, 'success');
            } else {
                showMessage(data.message || 'Une erreur est survenue', 'danger');
            }
        })
        .catch(error => {
            console.error('Erreur lors de la validation:', error);
            showMessage('Une erreur est survenue lors de la validation', 'danger');
        })
        .finally(() => {
            // Réactiver les boutons
            toggleValidationButtons(publicationId, false);
        });
    }
    
    // Fonction pour mettre à jour les statistiques de validation
    function updateValidationStats(publicationId, stats) {
        console.log('Mise à jour des statistiques:', stats);
        
        // Mettre à jour les compteurs sur la carte
        const validCount = document.getElementById(`validCount-${publicationId}`);
        const invalidCount = document.getElementById(`invalidCount-${publicationId}`);
        
        if (validCount) validCount.textContent = stats.valid_count;
        if (invalidCount) invalidCount.textContent = stats.invalid_count;
        
        // Mettre à jour les statistiques dans la modale
        const validPercentage = document.getElementById('validPercentage');
        const invalidPercentage = document.getElementById('invalidPercentage');
        const validCountModal = document.getElementById('validCount');
        const invalidCountModal = document.getElementById('invalidCount');
        
        if (validPercentage && invalidPercentage && validCountModal && invalidCountModal) {
            validCountModal.textContent = stats.valid_count;
            invalidCountModal.textContent = stats.invalid_count;
            
            validPercentage.style.width = stats.valid_percentage + '%';
            validPercentage.setAttribute('aria-valuenow', stats.valid_percentage);
            validPercentage.textContent = stats.valid_percentage + '%';
            
            invalidPercentage.style.width = stats.invalid_percentage + '%';
            invalidPercentage.setAttribute('aria-valuenow', stats.invalid_percentage);
            invalidPercentage.textContent = stats.invalid_percentage + '%';
        }
    }
    
    // Fonction pour désactiver/activer les boutons de validation
    function toggleValidationButtons(publicationId, disabled) {
        const validateBtn = document.querySelector(`.validate-btn[data-publication-id="${publicationId}"]`);
        const invalidateBtn = document.querySelector(`.invalidate-btn[data-publication-id="${publicationId}"]`);
        
        if (validateBtn) validateBtn.disabled = disabled;
        if (invalidateBtn) invalidateBtn.disabled = disabled;
    }
    
    // Fonction pour mettre à jour l'état des boutons après une validation
    function updateValidationButtonsState(publicationId, isValid) {
        const validateBtn = document.querySelector(`.validate-btn[data-publication-id="${publicationId}"]`);
        const invalidateBtn = document.querySelector(`.invalidate-btn[data-publication-id="${publicationId}"]`);
        
        if (validateBtn && invalidateBtn) {
            // Marquer le bouton actif comme sélectionné
            validateBtn.classList.toggle('btn-success', isValid);
            validateBtn.classList.toggle('btn-outline-success', !isValid);
            
            invalidateBtn.classList.toggle('btn-danger', !isValid);
            invalidateBtn.classList.toggle('btn-outline-danger', isValid);
        }
    }
    
    // Fonction pour afficher un message
    function showMessage(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed bottom-0 end-0 m-3`;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Supprimer l'alerte après 3 secondes
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => {
                alertDiv.remove();
            }, 150);
        }, 3000);
    }
}); 