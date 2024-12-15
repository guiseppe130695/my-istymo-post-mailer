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
            'order_id' => $data['order_id'] ? absint($data['order_id']) : null
        );
        
        $result = $this->wpdb->insert($this->table_name, $data);
        
        if (false === $result) {
            return new WP_Error('db_insert_error', 
                __('Could not insert recipient into the database.', 'postal-mailer'),
                $this->wpdb->last_error
            );
        }
        
        return $this->wpdb->insert_id;
    }

    public function get_recipients($per_page = 10, $page = 1) {
        $offset = ($page - 1) * $per_page;
        
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
            ORDER BY created_at DESC 
            LIMIT %d OFFSET %d",
            $per_page,
            $offset
        );
        
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    public function get_total_recipients() {
        return (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
    }

    public function update_recipient_status($recipient_id, $status) {
        return $this->wpdb->update(
            $this->table_name,
            ['status' => $status],
            ['id' => $recipient_id],
            ['%s'],
            ['%d']
        );
    }

    public function update_payment_status($order_id, $status) {
        return $this->wpdb->update(
            $this->table_name,
            ['status_paiement' => $status],
            ['order_id' => $order_id],
            ['%s'],
            ['%d']
        );
    }
}