<?php
if (!defined('ABSPATH')) {
    exit;
}

class Postal_Mailer_Activator {
    public static function activate() {
        $db = Postal_Mailer_DB::get_instance();
        $db->create_tables();
        self::set_default_options();
    }
    
    private static function set_default_options() {
        add_option('postal_mailer_cost_per_letter', 1.16);
        add_option('postal_mailer_product_id', 26965);
    }
}