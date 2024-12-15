<?php
if (!defined('ABSPATH')) {
    exit;
}

class Postal_Mailer_Form {
    private static $instance = null;
    private $logger;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->logger = Postal_Mailer_Logger::get_instance();
        add_action('wp_ajax_postal_mailer_submit', array($this, 'handle_submission'));
        add_action('wp_ajax_nopriv_postal_mailer_submit', array($this, 'handle_submission'));
    }
    
    public function handle_submission() {
        try {
            check_ajax_referer('postal-mailer-nonce', 'nonce');
            
            if (!isset($_POST['recipients']) || !isset($_POST['message'])) {
                throw new Exception('Données manquantes.');
            }
            
            $recipients = json_decode(stripslashes($_POST['recipients']), true);
            $message = sanitize_textarea_field($_POST['message']);
            
            if (!is_array($recipients)) {
                throw new Exception('Format de données invalide.');
            }
            
            if (!class_exists('WooCommerce')) {
                throw new Exception('WooCommerce n\'est pas installé.');
            }
            
            $product_id = get_option('postal_mailer_product_id', 26965);
            $quantity = count($recipients);
            
            // Verify product exists and is purchasable
            $product = wc_get_product($product_id);
            if (!$product || !$product->is_purchasable()) {
                throw new Exception('Produit non disponible.');
            }
            
            // Empty cart first
            WC()->cart->empty_cart();
            
            // Add product to cart with recipients and message as custom data
            $cart_item_data = array(
                '_postal_mailer_recipients' => $recipients,
                '_postal_mailer_message' => $message
            );
            
            $cart_item_key = WC()->cart->add_to_cart(
                $product_id, 
                $quantity, 
                0, 
                array(), 
                $cart_item_data
            );
            
            if (!$cart_item_key) {
                throw new Exception('Erreur lors de l\'ajout au panier.');
            }
            
            wp_send_json_success(array(
                'message' => 'Ajouté au panier avec succès.',
                'redirect_url' => wc_get_cart_url()
            ));
            
        } catch (Exception $e) {
            $this->logger->log('Form submission error: ' . $e->getMessage(), 'error');
            wp_send_json_error(array(
                'message' => $e->getMessage()
            ));
        }
    }
}