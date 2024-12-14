<?php
if (!defined('ABSPATH')) {
    exit;
}

class Postal_Mailer_DB {
    private static $instance = null;
    private $table_name;
    private $wpdb;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'postal_mailer_recipients';
    }
    
    public function create_tables() {
        $charset_collate = $this->wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            name varchar(255) NOT NULL,
            denomination varchar(255),
            address text NOT NULL,
            postal varchar(5) NOT NULL,
            city varchar(255) NOT NULL,
            status varchar(50) DEFAULT 'Non Envoyé',
            message text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            status_paiement enum('pending','completed','failed') DEFAULT 'pending',
            order_id bigint(20),
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY order_id (order_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function save_recipient($data) {
        $defaults = array(
            'user_id' => 0,
            'name' => '',
            'denomination' => '',
            'address' => '',
            'postal' => '',
            'city' => '',
            'status' => 'Non Envoyé',
            'message' => '',
            'status_paiement' => 'pending',
            'order_id' => null
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Sanitize data
        $data = array(
            'user_id' => absint($data['user_id']),
            'name' => sanitize_text_field($data['name']),
            'denomination' => sanitize_text_field($data['denomination']),
            'address' => sanitize_text_field($data['address']),
            'postal' => sanitize_text_field($data['postal']),
            'city' => sanitize_text_field($data['city']),
            'status' => sanitize_text_field($data['status']),
            'message' => sanitize_textarea_field($data['message']),
            'status_paiement' => in_array($data['status_paiement'], ['pending', 'completed', 'failed']) 
                ? $data['status_paiement'] 
                : 'pending',
            'order_id' => absint($data['order_id'])
        );
        
        $format = array(
            '%d', // user_id
            '%s', // name
            '%s', // denomination
            '%s', // address
            '%s', // postal
            '%s', // city
            '%s', // status
            '%s', // message
            '%s', // status_paiement
            '%d'  // order_id
        );
        
        $result = $this->wpdb->insert($this->table_name, $data, $format);
        
        if (false === $result) {
            return new WP_Error('db_insert_error', 
                __('Could not insert recipient into the database.', 'postal-mailer'),
                $this->wpdb->last_error
            );
        }
        
        return $this->wpdb->insert_id;
    }
}