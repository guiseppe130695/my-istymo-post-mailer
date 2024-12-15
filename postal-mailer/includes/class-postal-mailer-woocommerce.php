<?php
if (!defined('ABSPATH')) {
    exit;
}

class Postal_Mailer_WooCommerce {
    private static $instance = null;
    private $recipient_service;
    private $logger;
    private $processed_orders = array();
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->recipient_service = Postal_Mailer_Recipient_Service::get_instance();
        $this->logger = Postal_Mailer_Logger::get_instance();
        
        // Cart and checkout hooks
        add_filter('woocommerce_get_cart_item_data', array($this, 'get_cart_item_data'), 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'add_order_item_meta'), 10, 4);
        
        // Un seul hook pour le traitement du paiement
        add_action('woocommerce_payment_complete', array($this, 'handle_completed_order'));
        
        add_action('woocommerce_thankyou', array($this, 'clear_storage_after_payment'));
    }
    
    public function get_cart_item_data($cart_data, $cart_item) {
        if (isset($cart_item['_postal_mailer_recipients'])) {
            $recipients = $cart_item['_postal_mailer_recipients'];
            $count = count($recipients);
            
            $cart_data[] = array(
                'name' => __('Nombre de destinataires', 'postal-mailer'),
                'value' => $count
            );
        }
        
        return $cart_data;
    }
    
    public function add_order_item_meta($item, $cart_item_key, $values, $order) {
        if (isset($values['_postal_mailer_recipients'])) {
            $item->add_meta_data('_postal_mailer_recipients', $values['_postal_mailer_recipients']);
            $this->logger->log('Added recipients meta to order item: ' . $item->get_id());
        }
        if (isset($values['_postal_mailer_message'])) {
            $item->add_meta_data('_postal_mailer_message', $values['_postal_mailer_message']);
            $this->logger->log('Added message meta to order item: ' . $item->get_id());
        }
    }
    
    public function handle_completed_order($order_id) {
        // Vérifier si la commande a déjà été traitée
        if (in_array($order_id, $this->processed_orders)) {
            $this->logger->log('Order already processed: ' . $order_id);
            return;
        }
        
        $this->logger->log('Processing completed order: ' . $order_id);
        
        $order = wc_get_order($order_id);
        if (!$order || !$order->is_paid()) {
            $this->logger->log('Order not found or not paid: ' . $order_id, 'error');
            return;
        }
        
        $postal_product_id = get_option('postal_mailer_product_id', 26965);
        
        foreach ($order->get_items() as $item) {
            if ($item->get_product_id() != $postal_product_id) {
                continue;
            }
            
            $recipients = $item->get_meta('_postal_mailer_recipients');
            $message = $item->get_meta('_postal_mailer_message');
            
            if (!$recipients || !$message) {
                $this->logger->log('Missing recipients or message data for item: ' . $item->get_id(), 'error');
                continue;
            }
            
            if (is_string($recipients)) {
                $recipients = json_decode($recipients, true);
            }
            
            $this->logger->log('Processing recipients for paid order: ' . $order_id);
            
            // Enregistrer les destinataires uniquement après paiement réussi
            $result = $this->recipient_service->save_recipients(
                $recipients,
                $message,
                $order->get_user_id(),
                $order_id,
                'completed'
            );
            
            if (!$result['success']) {
                $this->logger->log('Errors saving recipients: ' . implode(', ', $result['errors']), 'error');
            } else {
                $this->logger->log('Successfully saved ' . $result['saved'] . ' recipients');
            }
        }
        
        // Marquer la commande comme traitée
        $this->processed_orders[] = $order_id;
    }
    
    public function clear_storage_after_payment($order_id) {
        $order = wc_get_order($order_id);
        if (!$order || !$order->is_paid()) {
            return;
        }
        
        $postal_product_id = get_option('postal_mailer_product_id', 26965);
        $has_postal_product = false;
        
        foreach ($order->get_items() as $item) {
            if ($item->get_product_id() == $postal_product_id) {
                $has_postal_product = true;
                break;
            }
        }
        
        if ($has_postal_product) {
            ?>
            <script>
            if (localStorage.getItem('selectedProperties')) {
                localStorage.removeItem('selectedProperties');
                if (typeof POSTAL_MAILER_NOTIFICATION !== 'undefined') {
                    POSTAL_MAILER_NOTIFICATION.updateNotificationCount();
                }
            }
            </script>
            <?php
        }
    }
}