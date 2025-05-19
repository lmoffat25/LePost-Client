<?php
/**
 * Template principal pour l'administration du plugin
 *
 * Ce fichier sert de wrapper pour toutes les pages d'administration.
 * Il fournit une structure cohérente avec en-tête, onglets et pied de page.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/views
 */

// Protection directe
if (!defined('ABSPATH')) {
    exit;
}

// Récupérer la liste des onglets actifs
$tabs = apply_filters('lepost_client_admin_tabs', []);
$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : '';

// Si aucun onglet n'est spécifié ou si l'onglet spécifié n'existe pas, prendre le premier
if (empty($active_tab) || !isset($tabs[$active_tab])) {
    if (!empty($tabs)) {
        $active_tab = array_key_first($tabs);
    }
}
?>

<div class="wrap lepost-admin-container">
    <!-- En-tête -->
    <div class="lepost-admin-header">
        <h1>
            <?php echo esc_html(get_admin_page_title()); ?>
        </h1>
        
        <?php if ($this->api->is_api_key_set()): ?>
            <div class="lepost-admin-header-actions">
                <span class="lepost-api-status lepost-api-status-connected">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('API connectée', 'lepost-client'); ?>
                </span>
            </div>
        <?php else: ?>
            <div class="lepost-admin-header-actions">
                <span class="lepost-api-status lepost-api-status-disconnected">
                    <span class="dashicons dashicons-warning"></span>
                    <?php esc_html_e('API non configurée', 'lepost-client'); ?>
                </span>
                <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-client')); ?>" class="button button-primary">
                    <span class="dashicons dashicons-admin-network"></span>
                    <?php esc_html_e('Configurer l\'API', 'lepost-client'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Onglets de navigation -->
    <?php if (!empty($tabs)): ?>
        <div class="nav-tab-wrapper lepost-admin-tabs">
            <?php foreach ($tabs as $tab_id => $tab): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=' . esc_attr($_GET['page']) . '&tab=' . esc_attr($tab_id))); ?>" 
                   class="nav-tab <?php echo $active_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
                    <?php if (!empty($tab->get_icon())): ?>
                        <span class="dashicons <?php echo esc_attr($tab->get_icon()); ?>"></span>
                    <?php endif; ?>
                    <?php echo esc_html($tab->get_title()); ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Contenu principal -->
    <div class="lepost-admin-content">
        <?php
        if (!empty($active_tab) && isset($tabs[$active_tab])) {
            // Affichage des notifications
            $notifications = $tabs[$active_tab]->get_notifications();
            
            if (!empty($notifications)) {
                foreach ($notifications as $notification) {
                    $type = isset($notification['type']) ? $notification['type'] : 'info';
                    $class = 'lepost-admin-message lepost-admin-message-' . $type;
                    ?>
                    <div class="<?php echo esc_attr($class); ?>">
                        <?php echo wp_kses_post($notification['message']); ?>
                    </div>
                    <?php
                }
            }
            
            // Affichage du contenu de l'onglet
            $tabs[$active_tab]->render_content();
        } else {
            // Cas où aucun onglet n'est disponible
            echo '<div class="lepost-admin-card">';
            echo '<p>' . esc_html__('Aucun onglet de contenu disponible.', 'lepost-client') . '</p>';
            echo '</div>';
        }
        ?>
    </div>

    <!-- Pied de page -->
    <div class="lepost-admin-footer">
        <p>
            <?php 
            printf(
                esc_html__('LePost Client v%s | %sDocumentation%s | %sSupport%s', 'lepost-client'),
                LEPOST_CLIENT_VERSION,
                '<a href="https://docs.lepost.ai/" target="_blank">',
                '</a>',
                '<a href="https://support.lepost.ai/" target="_blank">',
                '</a>'
            ); 
            ?>
        </p>
    </div>
</div>
