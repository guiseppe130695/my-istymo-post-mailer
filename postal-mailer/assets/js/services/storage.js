window.POSTAL_MAILER_STORAGE = {
    getSelectedProperties: function() {
        try {
            const properties = localStorage.getItem('selectedProperties');
            return properties ? JSON.parse(properties) : [];
        } catch (error) {
            console.error('Error parsing selectedProperties:', error);
            return [];
        }
    },

    setSelectedProperties: function(properties) {
        try {
            localStorage.setItem('selectedProperties', JSON.stringify(properties));
            window.dispatchEvent(new CustomEvent('selectedPropertiesChanged', {
                detail: { properties }
            }));
        } catch (error) {
            console.error('Error saving selectedProperties:', error);
        }
    },

    clearSelectedProperties: function() {
        try {
            localStorage.removeItem('selectedProperties');
            window.dispatchEvent(new CustomEvent('selectedPropertiesChanged', {
                detail: { properties: [] }
            }));
        } catch (error) {
            console.error('Error clearing selectedProperties:', error);
        }
    }
};