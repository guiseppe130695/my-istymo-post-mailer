<?php
if (!defined('ABSPATH')) {
    exit;
}

class Postal_Mailer_Form {
    private static $instance = null;
    private $db;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->db = Postal_Mailer_DB::get_instance();
        add_action('wp_ajax_postal_mailer_submit', array($this, 'handle_submission'));
        add_action('wp_ajax_nopriv_postal_mailer_submit', array($this, 'handle_submission'));
    }
    
    public function handle_submission() {
        check_ajax_referer('postal-mailer-nonce', 'nonce');
        
        if (!isset($_POST['recipients']) || !isset($_POST['message'])) {
            wp_send_json_error(array(
                'message' => 'Données manquantes.'
            ));
            return;
        }
        
        $recipients = json_decode(stripslashes($_POST['recipients']), true);
        $message = sanitize_textarea_field($_POST['message']);
        
        if (!is_array($recipients)) {
            wp_send_json_error(array(
                'message' => 'Format de données invalide.'
            ));
            return;
        }
        
        // Save recipients to database first
        $saved_recipients = $this->save_recipients($recipients, $message);
        
        if (is_wp_error($saved_recipients)) {
            wp_send_json_error(array(
                'message' => $saved_recipients->get_error_message()
            ));
            return;
        }
        
        // If database save was successful, proceed with WooCommerce cart
        if (!class_exists('WooCommerce')) {
            wp_send_json_error(array(
                'message' => 'WooCommerce n\'est pas installé.'
            ));
            return;
        }
        
        $product_id = get_option('postal_mailer_product_id', 26965);
        $quantity = count($recipients);
        
        // Verify product exists and is purchasable
        $product = wc_get_product($product_id);
        if (!$product || !$product->is_purchasable()) {
            wp_send_json_error(array(
                'message' => 'Produit non disponible.'
            ));
            return;
        }
        
        // Empty cart first
        WC()->cart->empty_cart();
        
        // Add product to cart
        $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity);
        
        if ($cart_item_key) {
            wp_send_json_success(array(
                'message' => 'Demande enregistrée avec succès.',
                'redirect_url' => wc_get_cart_url()
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Erreur lors de l\'ajout au panier.'
            ));
        }
    }
    
    private function save_recipients($recipients, $message) {
        $user_id = get_current_user_id();
        $success_count = 0;
        $errors = array();
        
        foreach ($recipients as $recipient) {
            $result = $this->db->save_recipient(array(
                'user_id' => $user_id,
                'name' => sanitize_text_field($recipient['name']),
                'denomination' => sanitize_text_field($recipient['denomination'] ?? ''),
                'address' => sanitize_text_field($recipient['address']),
                'postal' => sanitize_text_field($recipient['postal']),
                'city' => sanitize_text_field($recipient['city']),
                'message' => $message,
                'status' => 'Non Envoyé',
                'status_paiement' => 'pending'
            ));
            
            if (is_wp_error($result)) {
                $errors[] = $result;
            } else {
                $success_count++;
            }
        }
        
        if (!empty($errors)) {
            return new WP_Error(
                'save_error',
                sprintf(
                    'Erreur lors de l\'enregistrement de %d destinataires sur %d.',
                    count($errors),
                    count($recipients)
                )
            );
        }
        
        return true;
    }
}