<?php
if (!defined('ABSPATH')) {
    exit;
}

function postal_mailer_add_button() {
    include plugin_dir_path(dirname(__FILE__)) . 'templates/button.php';
    include plugin_dir_path(dirname(__FILE__)) . 'templates/popup.php';
}
add_action('wp_footer', 'postal_mailer_add_button');