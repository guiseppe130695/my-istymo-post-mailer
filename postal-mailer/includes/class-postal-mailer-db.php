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
            PRIMARY KEY  (id),
            KEY user_id (user_id)
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
            'status_paiement' => 'pending'
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
                : 'pending'
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
            '%s'  // status_paiement
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
    
    public function get_recipients($per_page = 10, $current_page = 1, $args = array()) {
        $defaults = array(
            'user_id' => 0,
            'status' => '',
            'status_paiement' => '',
            'orderby' => 'created_at',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        $offset = ($current_page - 1) * $per_page;
        
        $where = array('1=1');
        $prepare = array();
        
        if (!empty($args['user_id'])) {
            $where[] = 'user_id = %d';
            $prepare[] = $args['user_id'];
        }
        
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $prepare[] = $args['status'];
        }
        
        if (!empty($args['status_paiement'])) {
            $where[] = 'status_paiement = %s';
            $prepare[] = $args['status_paiement'];
        }
        
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        
        $prepare[] = absint($per_page);
        $prepare[] = absint($offset);
        
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
            WHERE " . implode(' AND ', $where) . "
            ORDER BY {$orderby}
            LIMIT %d OFFSET %d",
            $prepare
        );
        
        return $this->wpdb->get_results($sql, ARRAY_A);
    }
    
    public function get_total_recipients($args = array()) {
        $defaults = array(
            'user_id' => 0,
            'status' => '',
            'status_paiement' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array('1=1');
        $prepare = array();
        
        if (!empty($args['user_id'])) {
            $where[] = 'user_id = %d';
            $prepare[] = $args['user_id'];
        }
        
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $prepare[] = $args['status'];
        }
        
        if (!empty($args['status_paiement'])) {
            $where[] = 'status_paiement = %s';
            $prepare[] = $args['status_paiement'];
        }
        
        $sql = $this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} 
            WHERE " . implode(' AND ', $where),
            $prepare
        );
        
        return (int) $this->wpdb->get_var($sql);
    }
    
    public function update_status($id, $status) {
        return $this->wpdb->update(
            $this->table_name,
            array('status' => sanitize_text_field($status)),
            array('id' => absint($id)),
            array('%s'),
            array('%d')
        );
    }
    
    public function update_payment_status($id, $status) {
        if (!in_array($status, ['pending', 'completed', 'failed'])) {
            return false;
        }
        
        return $this->wpdb->update(
            $this->table_name,
            array('status_paiement' => $status),
            array('id' => absint($id)),
            array('%s'),
            array('%d')
        );
    }
    
    public function delete_recipient($id) {
        return $this->wpdb->delete(
            $this->table_name,
            array('id' => absint($id)),
            array('%d')
        );
    }
}