window.POSTAL_MAILER_NOTIFICATION = {
    updateNotificationCount: function() {
        try {
            const localProperties = POSTAL_MAILER_STORAGE.getSelectedProperties();
            const countElement = document.getElementById('postal-mailer-count');
            
            if (!countElement) {
                console.warn('Notification count element not found');
                return;
            }
            
            const count = localProperties.length;
            
            if (count > 0) {
                countElement.style.display = 'block';
                countElement.textContent = count.toString();
            } else {
                countElement.style.display = 'none';
            }
        } catch (error) {
            console.error('Error updating notification count:', error);
        }
    }
};