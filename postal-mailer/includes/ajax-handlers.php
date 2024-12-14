<?php
if (!defined('ABSPATH')) {
    exit;
}

function postal_mailer_save_recipients() {
    check_ajax_referer('postal-mailer-nonce', 'nonce');

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['recipients']) || !isset($data['message'])) {
        wp_send_json_error(array(
            'message' => __('Données manquantes.', 'postal-mailer')
        ));
        return;
    }

    $db = Postal_Mailer_DB::get_instance();
    $user_id = get_current_user_id();
    $message = sanitize_textarea_field($data['message']);
    $success_count = 0;
    $errors = array();
    
    foreach ($data['recipients'] as $recipient) {
        $result = $db->save_recipient(array(
            'user_id' => $user_id,
            'name' => sanitize_text_field($recipient['name']),
            'denomination' => sanitize_text_field($recipient['denomination'] ?? ''),
            'address' => sanitize_text_field($recipient['address']),
            'postal' => sanitize_text_field($recipient['postal']),
            'city' => sanitize_text_field($recipient['city']),
            'message' => $message,
            'status' => 'Non Envoyé',
            'status_paiement' => 'pending'
        ));
        
        if (is_wp_error($result)) {
            $errors[] = sprintf(
                __('Erreur pour %s: %s', 'postal-mailer'),
                $recipient['name'],
                $result->get_error_message()
            );
        } else {
            $success_count++;
        }
    }
    
    if (!empty($errors)) {
        wp_send_json_error(array(
            'message' => __('Certains destinataires n\'ont pas pu être enregistrés.', 'postal-mailer'),
            'errors' => $errors
        ));
        return;
    }
    
    wp_send_json_success(array(
        'message' => sprintf(
            __('%d destinataires enregistrés avec succès.', 'postal-mailer'),
            $success_count
        )
    ));
}
add_action('wp_ajax_postal_mailer_save_recipients', 'postal_mailer_save_recipients');
add_action('wp_ajax_nopriv_postal_mailer_save_recipients', 'postal_mailer_save_recipients');

function postal_mailer_submit() {
    check_ajax_referer('postal-mailer-nonce', 'nonce');

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!class_exists('WooCommerce')) {
        wp_send_json_error(array(
            'message' => __('WooCommerce n\'est pas installé.', 'postal-mailer')
        ));
        return;
    }
    
    $product_id = get_option('postal_mailer_product_id', 26965);
    $quantity = count($data['recipients'] ?? array());
    
    if (!$quantity) {
        wp_send_json_error(array(
            'message' => __('Aucun destinataire sélectionné.', 'postal-mailer')
        ));
        return;
    }
    
    // Verify product exists and is purchasable
    $product = wc_get_product($product_id);
    if (!$product || !$product->is_purchasable()) {
        wp_send_json_error(array(
            'message' => __('Produit non disponible.', 'postal-mailer')
        ));
        return;
    }
    
    // Empty cart first
    WC()->cart->empty_cart();
    
    // Add product to cart
    $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity);
    
    if ($cart_item_key) {
        wp_send_json_success(array(
            'message' => __('Produit ajouté au panier.', 'postal-mailer'),
            'redirect_url' => wc_get_cart_url()
        ));
    } else {
        wp_send_json_error(array(
            'message' => __('Erreur lors de l\'ajout au panier.', 'postal-mailer')
        ));
    }
}
add_action('wp_ajax_postal_mailer_submit', 'postal_mailer_submit');
add_action('wp_ajax_nopriv_postal_mailer_submit', 'postal_mailer_submit');