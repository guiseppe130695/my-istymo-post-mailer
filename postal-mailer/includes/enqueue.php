<?php
if (!defined('ABSPATH')) {
    exit;
}

function postal_mailer_enqueue_scripts() {
    // Styles
    wp_enqueue_style(
        'postal-mailer-styles',
        plugins_url('assets/css/styles.css', dirname(__FILE__)),
        [],
        POSTAL_MAILER_VERSION
    );

    // Scripts
    wp_enqueue_script(
        'postal-mailer-config',
        plugins_url('assets/js/config.js', dirname(__FILE__)),
        [],
        POSTAL_MAILER_VERSION,
        true
    );

    wp_enqueue_script(
        'postal-mailer-storage',
        plugins_url('assets/js/services/storage.js', dirname(__FILE__)),
        ['postal-mailer-config'],
        POSTAL_MAILER_VERSION,
        true
    );

    wp_enqueue_script(
        'postal-mailer-notification',
        plugins_url('assets/js/components/notification.js', dirname(__FILE__)),
        ['postal-mailer-storage'],
        POSTAL_MAILER_VERSION,
        true
    );

    wp_enqueue_script(
        'postal-mailer-recipients',
        plugins_url('assets/js/components/recipients.js', dirname(__FILE__)),
        ['postal-mailer-config'],
        POSTAL_MAILER_VERSION,
        true
    );

    wp_enqueue_script(
        'postal-mailer-script',
        plugins_url('assets/js/postal-mailer.js', dirname(__FILE__)),
        ['postal-mailer-config', 'postal-mailer-storage', 'postal-mailer-notification', 'postal-mailer-recipients'],
        POSTAL_MAILER_VERSION,
        true
    );

    wp_localize_script('postal-mailer-script', 'postalMailerData', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('postal-mailer-nonce')
    ]);
}
add_action('wp_enqueue_scripts', 'postal_mailer_enqueue_scripts');