<?php
/**
 * Partiel pour les paramètres généraux
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/views/_parts
 */

// Protection directe
if (!defined('ABSPATH')) {
    exit;
}

?>
<!-- PARAMÈTRES GÉNÉRAUX -->
<div class="lepost-admin-card">
    <h3><?php esc_html_e('Paramètres des articles WordPress', 'lepost-client'); ?></h3>
    
    <div class="lepost-admin-form-group">
        <label for="default_status"><?php esc_html_e('Statut par défaut des articles', 'lepost-client'); ?></label>
        <select name="lepost_client_settings[default_status]" id="default_status">
            <option value="draft" <?php selected($settings['default_status'], 'draft'); ?>><?php esc_html_e('Brouillon', 'lepost-client'); ?></option>
            <option value="publish" <?php selected($settings['default_status'], 'publish'); ?>><?php esc_html_e('Publié', 'lepost-client'); ?></option>
            <option value="pending" <?php selected($settings['default_status'], 'pending'); ?>><?php esc_html_e('En attente de relecture', 'lepost-client'); ?></option>
            <option value="private" <?php selected($settings['default_status'], 'private'); ?>><?php esc_html_e('Privé', 'lepost-client'); ?></option>
        </select>
        <p class="description"><?php esc_html_e('Statut attribué aux articles WordPress créés automatiquement.', 'lepost-client'); ?></p>
    </div>
    
    <div class="lepost-admin-form-group">
        <label for="default_category"><?php esc_html_e('Catégorie par défaut', 'lepost-client'); ?></label>
        <?php
        wp_dropdown_categories([
            'name' => 'lepost_client_settings[default_category]',
            'id' => 'default_category',
            'selected' => isset($settings['default_category']) ? $settings['default_category'] : 0,
            'show_option_none' => __('-- Catégorie par défaut de WordPress --', 'lepost-client'),
            'option_none_value' => '0',
            'hide_empty' => 0
        ]);
        ?>
        <p class="description"><?php esc_html_e('Catégorie attribuée aux articles WordPress créés automatiquement.', 'lepost-client'); ?></p>
    </div>
</div>

<!-- PARAMÈTRES DE MISE À JOUR -->
<div class="lepost-admin-card">
    <h3><?php esc_html_e('Mises à jour automatiques', 'lepost-client'); ?></h3>
    
    <div class="lepost-admin-form-group">
        <label>
            <input type="checkbox" name="lepost_client_settings[enable_auto_updates]" value="1" <?php checked(isset($settings['enable_auto_updates']) ? $settings['enable_auto_updates'] : '0', '1'); ?> />
            <?php esc_html_e('Activer les mises à jour automatiques du plugin', 'lepost-client'); ?>
        </label>
        <p class="description">
            <?php esc_html_e('Lorsque cette option est activée, le plugin sera automatiquement mis à jour dès qu\'une nouvelle version est disponible.', 'lepost-client'); ?>
            <br>
            <?php esc_html_e('Cette option est synchronisée avec le système de mises à jour automatiques de WordPress, visible dans la liste des plugins.', 'lepost-client'); ?>
        </p>
    </div>
</div> 