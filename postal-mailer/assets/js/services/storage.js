window.POSTAL_MAILER_STORAGE = {
    STORAGE_KEY: 'selectedProperties',
    
    getSelectedProperties: function() {
        try {
            const properties = localStorage.getItem(this.STORAGE_KEY);
            return properties ? JSON.parse(properties) : [];
        } catch (error) {
            console.error('Error parsing selectedProperties:', error);
            return [];
        }
    },

    setSelectedProperties: function(properties) {
        try {
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(properties));
            this.notifyChange(properties);
        } catch (error) {
            console.error('Error saving selectedProperties:', error);
        }
    },

    clearSelectedProperties: function() {
        try {
            localStorage.removeItem(this.STORAGE_KEY);
            this.notifyChange([]);
        } catch (error) {
            console.error('Error clearing selectedProperties:', error);
        }
    },
    
    notifyChange: function(properties) {
        window.dispatchEvent(new CustomEvent('selectedPropertiesChanged', {
            detail: { properties }
        }));
    }
};