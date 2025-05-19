<?php
/**
 * Formulaire des paramètres généraux
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/views/_parts
 */

// Protection directe
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- SECTION: FORMULAIRE DES PARAMÈTRES GÉNÉRAUX -->
<div class="lepost-admin-card">
    <h3><?php esc_html_e('Paramètres généraux', 'lepost-client'); ?></h3>
    
    <form method="post" action="options.php" class="lepost-admin-form">
        <?php settings_fields('lepost_client_settings_group'); ?>
        
        
        <!-- Statut de publication par défaut -->
        <div class="lepost-admin-form-group">
            <label for="default_status"><?php esc_html_e('Statut par défaut des articles', 'lepost-client'); ?></label>
            <select name="lepost_client_settings[default_status]" id="default_status">
                <option value="draft" <?php selected(isset($settings['default_status']) ? $settings['default_status'] : 'draft', 'draft'); ?>>
                    <?php esc_html_e('Brouillon', 'lepost-client'); ?>
                </option>
                <option value="pending" <?php selected(isset($settings['default_status']) ? $settings['default_status'] : 'draft', 'pending'); ?>>
                    <?php esc_html_e('En attente de relecture', 'lepost-client'); ?>
                </option>
                <option value="publish" <?php selected(isset($settings['default_status']) ? $settings['default_status'] : 'draft', 'publish'); ?>>
                    <?php esc_html_e('Publié', 'lepost-client'); ?>
                </option>
                <option value="private" <?php selected(isset($settings['default_status']) ? $settings['default_status'] : 'draft', 'private'); ?>>
                    <?php esc_html_e('Privé', 'lepost-client'); ?>
                </option>
            </select>
            <p class="description">
                <?php esc_html_e('Le statut par défaut des articles WordPress créés.', 'lepost-client'); ?>
            </p>
        </div>
        
        <!-- Catégorie par défaut -->
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
            <p class="description">
                <?php esc_html_e('La catégorie par défaut pour les articles WordPress créés.', 'lepost-client'); ?>
            </p>
        </div>
        
        <!-- Bouton de soumission -->
        <div class="lepost-admin-form-group">
            <?php submit_button(__('Enregistrer les paramètres', 'lepost-client'), 'primary', 'submit', false); ?>
        </div>
    </form>
</div>
<!-- FIN SECTION: FORMULAIRE DES PARAMÈTRES GÉNÉRAUX --> 