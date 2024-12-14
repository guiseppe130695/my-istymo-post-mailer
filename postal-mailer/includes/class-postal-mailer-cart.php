<?php
if (!defined('ABSPATH')) {
    exit;
}

class Postal_Mailer_Cart {
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
        add_action('wp_ajax_postal_mailer_add_to_cart', array($this, 'add_to_cart'));
        add_action('wp_ajax_nopriv_postal_mailer_add_to_cart', array($this, 'add_to_cart'));
    }
    
    public function add_to_cart() {
        check_ajax_referer('postal-mailer-nonce', 'nonce');
        
        if (!class_exists('WooCommerce')) {
            wp_send_json_error(array(
                'message' => 'WooCommerce n\'est pas installé.'
            ));
            return;
        }
        
        $product_id = get_option('postal_mailer_product_id', 26965);
        $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 0;
        
        if (!$quantity) {
            wp_send_json_error(array(
                'message' => 'Quantité invalide.'
            ));
            return;
        }
        
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
                'message' => 'Produit ajouté au panier.',
                'redirect_url' => wc_get_cart_url()
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Erreur lors de l\'ajout au panier.'
            ));
        }
    }
}