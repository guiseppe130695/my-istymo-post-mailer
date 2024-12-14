// Initialize the postal mailer functionality
function initPostalMailer() {
    const button = document.getElementById('postal-mailer-button');
    const popup = document.getElementById('postal-mailer-popup');
    const closeBtn = document.querySelector('.postal-mailer-close');
    const backBtn = document.querySelector('.postal-mailer-back');
    const submitBtn = document.getElementById('postal-mailer-submit');
    const steps = document.querySelectorAll('.postal-mailer-step');
    const stepDots = document.querySelectorAll('.step');
    const loadTemplateBtn = document.getElementById('load-template');
    
    let currentStep = 1;
    
    function showStep(step) {
        steps.forEach((s, index) => {
            s.style.display = index + 1 === step ? 'block' : 'none';
        });
        
        stepDots.forEach((dot, index) => {
            dot.classList.toggle('active', index + 1 <= step);
        });
        
        backBtn.disabled = step === 1;
        submitBtn.textContent = step === 3 ? 'Envoyer' : 'Suivant';
        
        currentStep = step;
    }
    
    function loadTemplate() {
        const messageArea = document.getElementById('postal-message');
        messageArea.value = POSTAL_MAILER_CONFIG.DEFAULT_TEMPLATE;
    }
    
    async function handleSubmit() {
        if (currentStep < 3) {
            showStep(currentStep + 1);
            return;
        }
        
        try {
            const properties = POSTAL_MAILER_STORAGE.getSelectedProperties();
            const message = document.getElementById('postal-message').value;
            
            if (!properties.length) {
                alert('Veuillez sélectionner au moins un destinataire.');
                return;
            }
            
            if (!message.trim()) {
                alert('Veuillez saisir un message.');
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Envoi en cours...';
            
            const response = await POSTAL_MAILER_API.submitForm({
                recipients: properties,
                message: message
            });
            
            if (response.success) {
                // Clear localStorage after successful submission
                POSTAL_MAILER_STORAGE.clearSelectedProperties();
                window.location.href = response.data.redirect_url;
            } else {
                throw new Error(response.data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Une erreur est survenue. Veuillez réessayer.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Envoyer';
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
                showStep(1);
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
    
    if (submitBtn) {
        submitBtn.addEventListener('click', handleSubmit);
    }
    
    if (loadTemplateBtn) {
        loadTemplateBtn.addEventListener('click', loadTemplate);
    }
    
    // Initial setup
    POSTAL_MAILER_NOTIFICATION.updateNotificationCount();
    
    // Listen for storage changes
    window.addEventListener('selectedPropertiesChanged', () => {
        POSTAL_MAILER_NOTIFICATION.updateNotificationCount();
        if (popup && popup.style.display === 'flex') {
            const properties = POSTAL_MAILER_STORAGE.getSelectedProperties();
            POSTAL_MAILER_RECIPIENTS.populateRecipients(properties);
        }
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPostalMailer);
} else {
    initPostalMailer();
}