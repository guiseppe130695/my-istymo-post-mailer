window.POSTAL_MAILER_API = {
    submitForm: async function(data) {
        try {
            const formData = new FormData();
            formData.append('action', 'postal_mailer_submit');
            formData.append('nonce', postalMailerData.nonce);
            formData.append('recipients', JSON.stringify(data.recipients));
            formData.append('message', data.message);
            
            const response = await fetch(postalMailerData.ajaxurl, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.data?.message || 'Une erreur est survenue');
            }
            
            return result;
        } catch (error) {
            console.error('Form submission error:', error);
            throw error;
        }
    }
};