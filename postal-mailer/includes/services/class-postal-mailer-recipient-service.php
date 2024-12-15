<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Service class for handling recipient operations
 */
class Postal_Mailer_Recipient_Service {
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
    }
    
    /**
     * Save recipients to database
     * 
     * @param array $recipients List of recipients
     * @param string $message Message content
     * @param int $user_id User ID
     * @param int $order_id Order ID
     * @param string $status_paiement Payment status
     * @return array Result with success status and messages
     */
    public function save_recipients($recipients, $message, $user_id, $order_id = null, $status_paiement = 'pending') {
        $this->logger->log('Starting to save recipients');
        
        $results = [
            'success' => true,
            'saved' => 0,
            'errors' => []
        ];
        
        foreach ($recipients as $recipient) {
            $result = $this->db->save_recipient([
                'user_id' => $user_id,
                'name' => sanitize_text_field($recipient['name']),
                'denomination' => sanitize_text_field($recipient['denomination'] ?? ''),
                'address' => sanitize_text_field($recipient['address']),
                'postal' => sanitize_text_field($recipient['postal']),
                'city' => sanitize_text_field($recipient['city']),
                'message' => sanitize_textarea_field($message),
                'status' => 'Non Envoyé',
                'status_paiement' => $status_paiement,
                'order_id' => $order_id
            ]);
            
            if (is_wp_error($result)) {
                $results['success'] = false;
                $results['errors'][] = sprintf(
                    'Erreur pour %s: %s',
                    $recipient['name'],
                    $result->get_error_message()
                );
                $this->logger->log('Error saving recipient: ' . $result->get_error_message(), 'error');
            } else {
                $results['saved']++;
                $this->logger->log('Successfully saved recipient with ID: ' . $result);
            }
        }
        
        return $results;
    }
}