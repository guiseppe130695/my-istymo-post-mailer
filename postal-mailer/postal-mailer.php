<?php
/*
Plugin Name: Postal Mailer
Description: A step-by-step postal mailing system for WordPress
Version: 1.0.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('POSTAL_MAILER_VERSION', '1.0.0');
define('POSTAL_MAILER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('POSTAL_MAILER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Activation hook
register_activation_hook(__FILE__, array('Postal_Mailer_Activator', 'activate'));

// Load required files
require_once POSTAL_MAILER_PLUGIN_DIR . 'includes/class-postal-mailer-activator.php';
require_once POSTAL_MAILER_PLUGIN_DIR . 'includes/class-postal-mailer-cart.php';
require_once POSTAL_MAILER_PLUGIN_DIR . 'includes/class-postal-mailer-form.php';
require_once POSTAL_MAILER_PLUGIN_DIR . 'includes/class-postal-mailer-db.php';
require_once POSTAL_MAILER_PLUGIN_DIR . 'includes/class-postal-mailer-admin.php';
require_once POSTAL_MAILER_PLUGIN_DIR . 'includes/class-postal-mailer-woocommerce.php';
require_once POSTAL_MAILER_PLUGIN_DIR . 'includes/class-postal-mailer-logger.php';
require_once POSTAL_MAILER_PLUGIN_DIR . 'includes/enqueue.php';
require_once POSTAL_MAILER_PLUGIN_DIR . 'includes/template-loader.php';

// Initialize classes
add_action('plugins_loaded', function() {
    Postal_Mailer_Cart::get_instance();
    Postal_Mailer_Form::get_instance();
    Postal_Mailer_Admin::get_instance();
    Postal_Mailer_WooCommerce::get_instance();
    Postal_Mailer_Logger::get_instance();
});