// Initialize the postal mailer functionality
function initPostalMailer() {
    const button = document.getElementById('postal-mailer-button');
    const popup = document.getElementById('postal-mailer-popup');
    const closeBtn = document.querySelector('.postal-mailer-close');
    const backBtn = document.querySelector('.postal-mailer-back');
    const nextBtn = document.querySelector('.postal-mailer-next');
    const steps = document.querySelectorAll('.postal-mailer-step');
    const stepDots = document.querySelectorAll('.step');
    const loadTemplateBtn = document.getElementById('load-template');
    
    let currentStep = 1;
    
    function showStep(step) {
        steps.forEach((s, index) => {
            if (s) s.style.display = index + 1 === step ? 'block' : 'none';
        });
        
        stepDots.forEach((dot, index) => {
            if (dot) dot.classList.toggle('active', index + 1 <= step);
        });
        
        if (backBtn) backBtn.disabled = step === 1;
        if (nextBtn) nextBtn.textContent = step === 3 ? 'Envoyer' : 'Suivant';
        
        currentStep = step;
    }
    
    function loadTemplate() {
        const messageArea = document.getElementById('postal-message');
        if (messageArea) {
            messageArea.value = POSTAL_MAILER_CONFIG.DEFAULT_TEMPLATE;
        }
    }
    
    async function handleSubmit() {
        if (currentStep < 3) {
            showStep(currentStep + 1);
            return;
        }
        
        try {
            const properties = POSTAL_MAILER_STORAGE.getSelectedProperties();
            const message = document.getElementById('postal-message')?.value;
            
            if (!properties.length) {
                alert('Veuillez sélectionner au moins un destinataire.');
                return;
            }
            
            if (!message?.trim()) {
                alert('Veuillez saisir un message.');
                return;
            }
            
            if (nextBtn) {
                nextBtn.disabled = true;
                nextBtn.textContent = 'Envoi en cours...';
            }
            
            const formData = new FormData();
            formData.append('action', 'postal_mailer_submit');
            formData.append('nonce', postalMailerData.nonce);
            formData.append('recipients', JSON.stringify(properties));
            formData.append('message', message);
            
            const response = await fetch(postalMailerData.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            
            const result = await response.json();
            
            if (result.success) {
                window.location.href = result.data.redirect_url;
            } else {
                throw new Error(result.data.message || 'Une erreur est survenue');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Une erreur est survenue. Veuillez réessayer.');
            if (nextBtn) {
                nextBtn.disabled = false;
                nextBtn.textContent = 'Envoyer';
            }
        }
    }
    
    // Event Listeners
    if (button) {
        button.addEventListener('click', () => {
            if (popup) {
                popup.style.display = 'flex';
                const properties = POSTAL_MAILER_STORAGE.getSelectedProperties();
                POSTAL_MAILER_RECIPIENTS.populateRecipients(properties);
                showStep(1);
            }
        });
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            if (popup) {
                popup.style.display = 'none';
                showStep(1); // Reset to first step when closing
            }
        });
    }
    
    if (backBtn) {
        backBtn.addEventListener('click', () => {
            if (currentStep > 1) {
                showStep(currentStep - 1);
            }
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', handleSubmit);
    }
    
    if (loadTemplateBtn) {
        loadTemplateBtn.addEventListener('click', loadTemplate);
    }
    
    // Initial setup
    if (typeof POSTAL_MAILER_NOTIFICATION !== 'undefined') {
        POSTAL_MAILER_NOTIFICATION.updateNotificationCount();
    }
    
    // Listen for storage changes
    window.addEventListener('selectedPropertiesChanged', () => {
        if (typeof POSTAL_MAILER_NOTIFICATION !== 'undefined') {
            POSTAL_MAILER_NOTIFICATION.updateNotificationCount();
        }
        if (popup && popup.style.display === 'flex') {
            const properties = POSTAL_MAILER_STORAGE.getSelectedProperties();
            if (typeof POSTAL_MAILER_RECIPIENTS !== 'undefined') {
                POSTAL_MAILER_RECIPIENTS.populateRecipients(properties);
            }
        }
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPostalMailer);
} else {
    initPostalMailer();
}