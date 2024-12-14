window.POSTAL_MAILER_DATABASE = {
    saveRecipientsToDatabase: async function(recipients, message) {
        try {
            const response = await fetch(postalMailerData.ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'postal_mailer_save_recipients',
                    nonce: postalMailerData.nonce,
                    recipients: recipients,
                    message: message
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.data.message || 'Une erreur est survenue');
            }
            
            return data;
        } catch (error) {
            console.error('Database save error:', error);
            throw new Error('Erreur lors de la sauvegarde: ' + error.message);
        }
    }
};