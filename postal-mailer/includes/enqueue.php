<?php
if (!defined('ABSPATH')) {
    exit;
}

function postal_mailer_enqueue_scripts() {
    wp_enqueue_style(
        'postal-mailer-styles',
        plugins_url('assets/css/styles.css', dirname(__FILE__)),
        [],
        '1.0.0'
    );

    // Config must be loaded first
    wp_register_script(
        'postal-mailer-config',
        plugins_url('assets/js/config.js', dirname(__FILE__)),
        [],
        '1.0.0',
        true
    );

    wp_register_script(
        'postal-mailer-storage',
        plugins_url('assets/js/services/storage.js', dirname(__FILE__)),
        ['postal-mailer-config'],
        '1.0.0',
        true
    );

    wp_register_script(
        'postal-mailer-api',
        plugins_url('assets/js/services/api.js', dirname(__FILE__)),
        ['postal-mailer-config'],
        '1.0.0',
        true
    );

    wp_register_script(
        'postal-mailer-database',
        plugins_url('assets/js/services/database.js', dirname(__FILE__)),
        ['postal-mailer-config'],
        '1.0.0',
        true
    );

    wp_register_script(
        'postal-mailer-notification',
        plugins_url('assets/js/components/notification.js', dirname(__FILE__)),
        ['postal-mailer-storage'],
        '1.0.0',
        true
    );

    wp_register_script(
        'postal-mailer-recipients',
        plugins_url('assets/js/components/recipients.js', dirname(__FILE__)),
        ['postal-mailer-config'],
        '1.0.0',
        true
    );

    // Enqueue all scripts
    wp_enqueue_script('postal-mailer-config');
    wp_enqueue_script('postal-mailer-storage');
    wp_enqueue_script('postal-mailer-api');
    wp_enqueue_script('postal-mailer-database');
    wp_enqueue_script('postal-mailer-notification');
    wp_enqueue_script('postal-mailer-recipients');

    // Main script depends on all others
    wp_enqueue_script(
        'postal-mailer-script',
        plugins_url('assets/js/postal-mailer.js', dirname(__FILE__)),
        [
            'postal-mailer-config',
            'postal-mailer-storage',
            'postal-mailer-api',
            'postal-mailer-database',
            'postal-mailer-notification',
            'postal-mailer-recipients'
        ],
        '1.0.0',
        true
    );

    wp_localize_script('postal-mailer-script', 'postalMailerData', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('postal-mailer-nonce')
    ]);
}
add_action('wp_enqueue_scripts', 'postal_mailer_enqueue_scripts');