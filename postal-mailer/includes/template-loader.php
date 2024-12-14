<?php
if (!defined('ABSPATH')) {
    exit;
}

function postal_mailer_add_button() {
    // Check if we're on the SCI dashboard page
    if (!is_page('dashboard') || !isset($_GET['module']) || $_GET['module'] !== 'sci') {
        return;
    }
    
    include plugin_dir_path(dirname(__FILE__)) . 'templates/button.php';
    include plugin_dir_path(dirname(__FILE__)) . 'templates/popup.php';
}
add_action('wp_footer', 'postal_mailer_add_button');