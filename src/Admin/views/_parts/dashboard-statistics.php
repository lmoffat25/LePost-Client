<?php
/**
 * Section des cartes de statistiques du tableau de bord
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/views/_parts
 */

// Protection directe
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- SECTION: CARTES DE STATISTIQUES -->
<!-- Cette section affiche les principales statistiques sous forme de cartes: 
     - Nombre d'idées d'articles
     - Nombre d'articles générés
     - Crédits API disponibles -->
<div class="lepost-dashboard-cards">
    <!-- Carte: Idées d'articles -->
    <div class="lepost-dashboard-card">
        <div class="lepost-dashboard-card-icon">
            <span class="dashicons dashicons-lightbulb"></span>
        </div>
        <div class="lepost-dashboard-card-content">
            <div class="lepost-dashboard-card-value"><?php echo esc_html($idees_count); ?></div>
            <div class="lepost-dashboard-card-label"><?php esc_html_e('Idées d\'articles', 'lepost-client'); ?></div>
        </div>
        <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-client&tab=ideas')); ?>" class="lepost-dashboard-card-action">
            <span class="dashicons dashicons-arrow-right-alt2"></span>
        </a>
    </div>

    <!-- Carte: Articles générés -->
    <div class="lepost-dashboard-card">
        <div class="lepost-dashboard-card-icon">
            <span class="dashicons dashicons-welcome-write-blog"></span>
        </div>
        <div class="lepost-dashboard-card-content">
            <div class="lepost-dashboard-card-value"><?php echo esc_html($articles_count); ?></div>
            <div class="lepost-dashboard-card-label"><?php esc_html_e('Articles générés', 'lepost-client'); ?></div>
        </div>
        <div class="lepost-dashboard-card-action">
            <span class="dashicons dashicons-arrow-right-alt2"></span>
        </div>
    </div>

    <!-- Carte: Crédits API -->
    <div class="lepost-dashboard-card">
        <div class="lepost-dashboard-card-icon">
            <span class="dashicons dashicons-tickets-alt"></span>
        </div>
        <div class="lepost-dashboard-card-content">
            <div class="lepost-dashboard-card-value"><?php echo esc_html($api_credits); ?></div>
            <div class="lepost-dashboard-card-label">
                <?php esc_html_e('Crédits API disponibles', 'lepost-client'); ?>

                <div class="lepost-credits-refresh">
                    <?php 
                    printf(
                        esc_html__('Mis à jour: %s', 'lepost-client'),
                        date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($refresh_time))
                    ); 
                    ?>
                    <a href="<?php echo esc_url(add_query_arg('refresh_credits', '1')); ?>" class="lepost-refresh-credits" title="<?php esc_attr_e('Actualiser les crédits', 'lepost-client'); ?>">
                        <span class="dashicons dashicons-update"></span>
                    </a>
                </div>
                <?php if ($show_debug): ?>
                <div class="lepost-credits-debug">
                    <pre><?php print_r($account_info); ?></pre>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="lepost-dashboard-card-actions">
            <a href="<?php echo esc_url('https://lepost.ai/account'); ?>" target="_blank" class="lepost-dashboard-card-action">
                <span class="dashicons dashicons-external"></span>
            </a>
        </div>
    </div>
</div>
<!-- FIN SECTION: CARTES DE STATISTIQUES --> 