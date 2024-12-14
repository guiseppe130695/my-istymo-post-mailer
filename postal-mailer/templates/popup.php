<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="postal-mailer-popup" class="postal-mailer-popup">
    <div class="postal-mailer-popup-content">
        <button class="postal-mailer-close">&times;</button>
        
        <div class="postal-mailer-header">
            <h2>Envoi Postal</h2>
            <div class="postal-mailer-steps">
                <span class="step active"></span>
                <span class="step"></span>
                <span class="step"></span>
            </div>
        </div>

        <div class="postal-mailer-body">
            <!-- Step 1: Recipients -->
            <div class="postal-mailer-step" id="step-1">
                <h3>Destinataires</h3>
                <div class="recipients-list"></div>
            </div>

            <!-- Step 2: Message -->
            <div class="postal-mailer-step" id="step-2" style="display: none;">
                <h3>Votre Message</h3>
                <textarea id="postal-message" placeholder="Rédigez votre message ici..."></textarea>
                <div class="template-actions">
                    <button id="load-template" class="template-button">Charger le template</button>
                </div>
            </div>

            <!-- Step 3: Cost Summary -->
            <div class="postal-mailer-step" id="step-3" style="display: none;">
                <h3>Résumé des Coûts</h3>
                <div class="cost-summary">
                    <div class="cost-row">
                        <span>Nombre de destinataires:</span>
                        <span id="recipient-count">0</span>
                    </div>
                    <div class="cost-row">
                        <span>Coût par lettre:</span>
                        <span>1.16€</span>
                    </div>
                    <div class="cost-row total">
                        <span>Coût total:</span>
                        <span id="total-cost">0.00€</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="postal-mailer-footer">
            <button class="postal-mailer-back" disabled>Retour</button>
            <button id="postal-mailer-submit" class="postal-mailer-next">Suivant</button>
        </div>
    </div>
</div>