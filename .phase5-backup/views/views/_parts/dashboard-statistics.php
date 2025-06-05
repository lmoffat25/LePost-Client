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
            <div class="lepost-dashboard-card-value">
                <?php if (isset($error) && $error): ?>
                    <span style="color: #d63638;">⚠️</span>
                <?php else: ?>
                    <?php echo esc_html($api_credits); ?>
                <?php endif; ?>
            </div>
            <div class="lepost-dashboard-card-label">
                <?php esc_html_e('Crédits API disponibles', 'lepost-client'); ?>

                <?php if (isset($error) && $error): ?>
                    <div class="lepost-credits-error" style="color: #d63638; font-size: 12px; margin-top: 5px;">
                        <?php echo esc_html($error_message ?? __('Erreur lors de la récupération des crédits', 'lepost-client')); ?>
                        <br>
                        <a href="<?php echo esc_url(add_query_arg('refresh_credits', '1')); ?>" class="lepost-refresh-credits" style="text-decoration: underline;">
                            <?php esc_html_e('Réessayer', 'lepost-client'); ?>
                        </a>
                    </div>
                <?php else: ?>
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
                <?php endif; ?>

                <?php if ($show_debug): ?>
                <div class="lepost-credits-debug">
                    <strong>Debug Information:</strong>
                    <pre style="background: #f0f0f0; padding: 10px; margin-top: 10px; font-size: 11px; max-height: 200px; overflow-y: auto;"><?php print_r($account_info); ?></pre>
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