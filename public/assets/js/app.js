/**
 * Fichier JavaScript principal de l'application
 */

// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {
    
    // Gestion des messages flash avec auto-hide
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        // Auto-hide après 5 secondes
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
        
        // Permettre de fermer manuellement
        alert.addEventListener('click', function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 300);
        });
    });
    
    // Validation de formulaire côté client
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
    });
    
    // // Amélioration UX pour les boutons de soumission
    // const submitButtons = document.querySelectorAll('button[type="submit"]');
    // submitButtons.forEach(function(button) {
    //     button.addEventListener('click', function() {
    //         const form = button.closest('form');
    //         if (form && validateForm(form)) {
    //             button.disabled = true;
    //             button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
                
    //             // Réactiver après 5 secondes en cas de problème
    //             setTimeout(function() {
    //                 button.disabled = false;
    //                 button.innerHTML = button.getAttribute('data-original-text') || 'Envoyer';
    //             }, 5000);
    //         }
    //     });
    // });
    
    // Smooth scroll pour les ancres
    const anchors = document.querySelectorAll('a[href^="#"]');
    anchors.forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Confirmation pour les actions de suppression
    const deleteLinks = document.querySelectorAll('a[href*="delete"], button[data-action="delete"]');
    deleteLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                e.preventDefault();
            }
        });
    });
    
    // Animation d'entrée pour les cartes
    const cards = document.querySelectorAll('.images-card, .step, .info-box');
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    cards.forEach(function(card) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});

/**
 * Valide un formulaire
 */
function validateForm(form) {
    let isValid = true;
    
    // Validation des champs requis
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(function(field) {
        if (!field.value.trim()) {
            showFieldError(field, 'Ce champ est obligatoire');
            isValid = false;
        } else {
            hideFieldError(field);
        }
    });
    
    // Validation des emails
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(function(field) {
        if (field.value && !isValidEmail(field.value)) {
            showFieldError(field, 'Adresse email invalide');
            isValid = false;
        }
    });
    
    // Validation des mots de passe
    const passwordField = form.querySelector('input[name="password"]');
    const confirmPasswordField = form.querySelector('input[name="confirm_password"]');
    
    if (passwordField && passwordField.value.length < 6) {
        showFieldError(passwordField, 'Le mot de passe doit contenir au moins 6 caractères');
        isValid = false;
    }
    
    if (confirmPasswordField && passwordField && 
        confirmPasswordField.value !== passwordField.value) {
        showFieldError(confirmPasswordField, 'Les mots de passe ne correspondent pas');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Affiche une erreur sur un champ
 */
function showFieldError(field, message) {
    hideFieldError(field);
    
    const error = document.createElement('div');
    error.className = 'field-error';
    error.textContent = message;
    error.style.color = '#ef4444';
    error.style.fontSize = '0.875rem';
    error.style.marginTop = '0.25rem';
    
    field.style.borderColor = '#ef4444';
    field.parentNode.appendChild(error);
}

/**
 * Cache l'erreur d'un champ
 */
function hideFieldError(field) {
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    field.style.borderColor = '';
}

/**
 * Valide une adresse email
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Affiche un message de notification
 */
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '1000';
    notification.style.minWidth = '300px';
    notification.style.cursor = 'pointer';
    
    document.body.appendChild(notification);
    
    // Auto-hide
    setTimeout(function() {
        notification.style.opacity = '0';
        setTimeout(function() {
            notification.remove();
        }, 300);
    }, 5000);
    
    // Click to hide
    notification.addEventListener('click', function() {
        notification.remove();
    });
}

/**
 * Utilitaire AJAX simple
 */
function ajax(url, options = {}) {
    const defaults = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    const config = Object.assign({}, defaults, options);
    
    return fetch(url, config)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            return response.json();
        })
        .catch(error => {
            console.error('Erreur AJAX:', error);
            showNotification('Une erreur est survenue', 'error');
            throw error;
        });
} 
/* Main Content */

const matrixContainer = document.querySelector('.matrix');
const characters = 'FILM LIVRE JEUX VIDEO'; // Caractères à afficher

function createColumn() {
    const column = document.createElement('div');
    column.classList.add('column');

    // Crée un nombre aléatoire de caractères
    const columnLength = Math.floor(Math.random() * 10) + 2; // Entre 10 et 30 caractères

    let columnText = '';
    for (let i = 0; i < columnLength; i++) {
        columnText += characters.charAt(Math.floor(Math.random() * characters.length)) + '<br>';
    }

    column.innerHTML = columnText;
    column.style.left = Math.random() * 100 + 'vw'; // Position horizontale aléatoire

    matrixContainer.appendChild(column);

    // Définir la durée de l'animation en fonction de la hauteur de la colonne
    const fallDuration = Math.random() * 2 + 3; // Entre 3 et 5 secondes
    column.style.animationDuration = fallDuration + 's';
}

// Crée plusieurs colonnes
setInterval(createColumn, 500); // Crée une nouvelle colonne toutes les 500 ms