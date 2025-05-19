<?php
/**
 * Modales du gestionnaire d'idées d'articles
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/views/_parts
 */

// Protection directe
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- SECTION: MODALES -->
<!-- Cette section contient les différentes modales utilisées par le gestionnaire d'idées -->

<!-- Modale pour la génération d'idées par IA -->
<?php if ($this->api->is_api_key_set()): ?>
<div id="lepost-generate-ideas-modal" class="lepost-modal">
    <div class="lepost-modal-content">
        <span class="lepost-modal-close" id="lepost-close-generate-modal">&times;</span>
        <h3>
            <?php esc_html_e('Générer des idées avec l\'IA', 'lepost-client'); ?>
        </h3>
        
        <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="lepost-admin-form" id="lepost-ideas-generation-form">
            <input type="hidden" name="action" value="lepost_generate_ideas_submit">
            <?php wp_nonce_field('lepost_generate_ideas_nonce', 'lepost_generate_ideas_nonce_field'); ?>
            
            <div class="lepost-admin-form-group">
                <label for="modal-idee-theme">
                    <?php esc_html_e('Thème', 'lepost-client'); ?> <span class="required">*</span>
                </label>
                <input type="text" id="modal-idee-theme" name="theme" required 
                       placeholder="<?php esc_attr_e('Ex: Marketing digital, Cuisine italienne, Développement personnel...', 'lepost-client'); ?>">
                <p class="description"><?php esc_html_e('Saisissez un thème précis pour obtenir des idées pertinentes.', 'lepost-client'); ?></p>
            </div>
            
            <div class="lepost-admin-form-group">
                <label for="modal-idee-nombre">
                    <?php esc_html_e('Nombre d\'idées à générer', 'lepost-client'); ?>
                </label>
                <select id="modal-idee-nombre" name="nombre">
                    <option value="3">3</option>
                    <option value="5" selected>5</option>
                    <option value="10">10</option>
                    <option value="15">15</option>
                </select>
                <p class="description"><?php esc_html_e('La génération de plus d\'idées prendra plus de temps et consommera plus de crédits.', 'lepost-client'); ?></p>
            </div>
            
            <div class="lepost-admin-actions">
                <button type="submit" class="button button-primary" id="generate-ideas-btn">
                    <span class="dashicons dashicons-update"></span>
                    <?php esc_html_e('Générer des idées', 'lepost-client'); ?>
                </button>
                <span class="spinner" style="float: none;"></span>
                <div class="lepost-generation-info" style="display: none;">
                    <?php esc_html_e('La génération d\'idées peut prendre quelques instants. Merci de patienter...', 'lepost-client'); ?>
                </div>
            </div>
            
            <div id="lepost-modal-generate-message" class="lepost-admin-message" style="display: none;"></div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Modale pour afficher la description complète d'une idée -->
<div id="lepost-idee-modal" class="lepost-modal">
    <div class="lepost-modal-content">
        <span class="lepost-modal-close">&times;</span>
        <h3 id="lepost-modal-title"></h3>
        <div id="lepost-modal-description"></div>
    </div>
</div>
<!-- FIN SECTION: MODALES --> 