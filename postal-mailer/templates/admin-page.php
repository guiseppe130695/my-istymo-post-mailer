<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap postal-mailer-wrap">
    <div class="postal-mailer-header">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    </div>

    <table class="postal-mailer-table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Dénomination</th>
                <th>Adresse</th>
                <th>Code Postal</th>
                <th>Ville</th>
                <th>Statut</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($recipients)) : ?>
                <?php foreach ($recipients as $recipient) : ?>
                    <tr>
                        <td><?php echo esc_html($recipient['name']); ?></td>
                        <td><?php echo esc_html($recipient['denomination']); ?></td>
                        <td><?php echo esc_html($recipient['address']); ?></td>
                        <td><?php echo esc_html($recipient['postal']); ?></td>
                        <td><?php echo esc_html($recipient['city']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo sanitize_html_class(strtolower(str_replace(' ', '-', $recipient['status']))); ?>">
                                <?php echo esc_html($recipient['status']); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($recipient['created_at']))); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7" style="text-align: center;">Aucun destinataire trouvé.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($total_pages > 1) : ?>
        <div class="postal-mailer-pagination">
            <?php
            echo paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'total' => $total_pages,
                'current' => $current_page
            ));
            ?>
        </div>
    <?php endif; ?>
</div>