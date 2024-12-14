window.POSTAL_MAILER_RECIPIENTS = {
    formatAddress: function(recipient) {
        const parts = [];
        if (recipient.denomination) parts.push(recipient.denomination);
        if (recipient.address) parts.push(recipient.address);
        if (recipient.postal && recipient.city) {
            parts.push(`${recipient.postal} ${recipient.city}`);
        }
        return parts.join('\n');
    },

    getStatusClass: function(status) {
        return status === 'Non Envoyé' ? 'status-non-envoye' : '';
    },

    populateRecipients: function(properties) {
        const list = document.querySelector('.recipients-list');
        list.innerHTML = '';
        
        if (properties.length > 0) {
            properties.forEach(recipient => {
                const div = document.createElement('div');
                div.className = 'recipient-item';
                div.innerHTML = `
                    <div class="recipient-name">${recipient.name}</div>
                    <div class="recipient-details">
                        ${recipient.denomination ? `<div>${recipient.denomination}</div>` : ''}
                        <div>${recipient.address}</div>
                        <div>${recipient.postal} ${recipient.city}</div>
                    </div>
                    <span class="recipient-status ${this.getStatusClass(recipient.status)}">
                        ${recipient.status || 'Non Envoyé'}
                    </span>
                `;
                list.appendChild(div);
            });
            
            this.updateCostSummary(properties.length);
        } else {
            list.innerHTML = '<div class="no-recipients">Aucun destinataire sélectionné</div>';
        }
    },

    updateCostSummary: function(count) {
        document.getElementById('recipient-count').textContent = count;
        document.getElementById('total-cost').textContent = 
            (count * POSTAL_MAILER_CONFIG.COST_PER_LETTER).toFixed(2) + '€';
    }
};