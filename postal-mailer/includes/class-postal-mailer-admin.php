<?php
if (!defined('ABSPATH')) {
    exit;
}

class Postal_Mailer_Admin {
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
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Postal Mailer',
            'Postal Mailer',
            'manage_options',
            'postal-mailer',
            array($this, 'render_admin_page'),
            'dashicons-email',
            30
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_postal-mailer' !== $hook) {
            return;
        }
        
        wp_enqueue_style(
            'postal-mailer-admin',
            plugins_url('assets/css/admin.css', dirname(__FILE__)),
            array(),
            POSTAL_MAILER_VERSION
        );
    }
    
    public function render_admin_page() {
        $per_page = 10;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $total_items = $this->db->get_total_recipients();
        $total_pages = ceil($total_items / $per_page);
        
        $recipients = $this->db->get_recipients($per_page, $current_page);
        
        include POSTAL_MAILER_PLUGIN_DIR . 'templates/admin-page.php';
    }
}