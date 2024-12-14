<?php
if (!defined('ABSPATH')) {
    exit;
}

class Postal_Mailer_Form {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
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
        
        // Add product to cart with recipients and message as custom data
        $cart_item_data = array(
            '_postal_mailer_recipients' => $recipients,
            '_postal_mailer_message' => $message
        );
        
        $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, 0, array(), $cart_item_data);
        
        if ($cart_item_key) {
            wp_send_json_success(array(
                'message' => 'Ajouté au panier avec succès.',
                'redirect_url' => wc_get_cart_url()
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Erreur lors de l\'ajout au panier.'
            ));
        }
    }
}