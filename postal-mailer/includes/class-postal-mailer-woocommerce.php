<?php
if (!defined('ABSPATH')) {
    exit;
}

class Postal_Mailer_WooCommerce {
    private static $instance = null;
    private $db;
    private $logger;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->db = Postal_Mailer_DB::get_instance();
        $this->logger = Postal_Mailer_Logger::get_instance();
        
        // Hook into cart/checkout process
        add_filter('woocommerce_get_item_data', array($this, 'add_cart_item_data'), 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'add_order_item_meta'), 10, 4);
        
        // Hook into order completion
        add_action('woocommerce_payment_complete', array($this, 'handle_completed_order'));
        add_action('woocommerce_order_status_completed', array($this, 'handle_completed_order'));
        
        // Add custom script to thank you page
        add_action('woocommerce_thankyou', array($this, 'add_clear_storage_script'));
    }
    
    public function add_cart_item_data($cart_data, $cart_item) {
        if (isset($cart_item['_postal_mailer_recipients'])) {
            $recipients = $cart_item['_postal_mailer_recipients'];
            $count = count($recipients);
            
            $cart_data[] = array(
                'name' => 'Nombre de destinataires',
                'value' => $count
            );
        }
        
        return $cart_data;
    }
    
    public function add_order_item_meta($item, $cart_item_key, $values, $order) {
        if (isset($values['_postal_mailer_recipients'])) {
            $item->add_meta_data('_postal_mailer_recipients', json_encode($values['_postal_mailer_recipients']));
            $this->logger->log('Added recipients meta to order item: ' . $item->get_id());
        }
        if (isset($values['_postal_mailer_message'])) {
            $item->add_meta_data('_postal_mailer_message', $values['_postal_mailer_message']);
            $this->logger->log('Added message meta to order item: ' . $item->get_id());
        }
    }
    
    public function add_clear_storage_script($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            $this->logger->log('Order not found: ' . $order_id, 'error');
            return;
        }
        
        $has_postal_product = false;
        $postal_product_id = get_option('postal_mailer_product_id', 26965);
        
        foreach ($order->get_items() as $item) {
            if ($item->get_product_id() == $postal_product_id) {
                $has_postal_product = true;
                break;
            }
        }
        
        if ($has_postal_product && $order->is_paid()) {
            $this->logger->log('Clearing storage for paid order: ' . $order_id);
            ?>
            <script>
            if (typeof POSTAL_MAILER_STORAGE !== 'undefined') {
                POSTAL_MAILER_STORAGE.clearSelectedProperties();
            }
            </script>
            <?php
        }
    }
    
    public function handle_completed_order($order_id) {
        $this->logger->log('Processing completed order: ' . $order_id);
        
        $order = wc_get_order($order_id);
        if (!$order) {
            $this->logger->log('Order not found: ' . $order_id, 'error');
            return;
        }
        
        // Process each order item
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $postal_product_id = get_option('postal_mailer_product_id', 26965);
            
            if ($product_id != $postal_product_id) {
                continue;
            }
            
            // Get recipients and message from item meta
            $recipients = json_decode($item->get_meta('_postal_mailer_recipients'), true);
            $message = $item->get_meta('_postal_mailer_message');
            
            if (!$recipients || !$message) {
                $this->logger->log('Missing recipients or message data for item: ' . $item->get_id(), 'error');
                continue;
            }
            
            $this->logger->log('Found recipients data:', 'debug');
            $this->logger->log($recipients);
            
            // Save recipients to database
            $user_id = $order->get_user_id();
            $success = true;
            
            foreach ($recipients as $recipient) {
                $result = $this->db->save_recipient(array(
                    'user_id' => $user_id,
                    'name' => $recipient['name'],
                    'denomination' => $recipient['denomination'] ?? '',
                    'address' => $recipient['address'],
                    'postal' => $recipient['postal'],
                    'city' => $recipient['city'],
                    'message' => $message,
                    'status' => 'En attente d\'envoi',
                    'status_paiement' => 'completed',
                    'order_id' => $order_id
                ));
                
                if (is_wp_error($result)) {
                    $this->logger->log('Error saving recipient: ' . $result->get_error_message(), 'error');
                    $success = false;
                } else {
                    $this->logger->log('Successfully saved recipient with ID: ' . $result);
                }
            }
            
            if ($success) {
                $this->logger->log('Successfully processed order item and saved all recipients');
            }
        }
    }
}