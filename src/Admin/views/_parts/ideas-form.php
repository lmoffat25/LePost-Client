<?php
/**
 * Formulaire d'ajout/édition d'idées d'articles
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/views/_parts
 */

// Protection directe
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- SECTION: FORMULAIRE D'IDÉE -->
<!-- Cette section contient le formulaire d'ajout/édition d'idée d'article
     et le panneau d'importation CSV -->
<div class="lepost-admin-card">
    <h3>
        <?php esc_html_e('Nouvelle idée d\'article', 'lepost-client'); ?>
    </h3>
    
    <div class="lepost-flex-container">
        <!-- Colonne principale: formulaire d'ajout/édition -->
        <div class="lepost-flex-column lepost-flex-main">
            <form id="lepost-idee-form" class="lepost-admin-form">
                <input type="hidden" id="idee-id" name="id" value="0">
                
                <div class="lepost-admin-form-group">
                    <label for="idee-titre">
                        <?php esc_html_e('Titre', 'lepost-client'); ?> <span class="required">*</span>
                    </label>
                    <input type="text" id="idee-titre" name="titre" required>
                </div>
                
                <div class="lepost-admin-form-group">
                    <label for="idee-description">
                        <?php esc_html_e('Description', 'lepost-client'); ?>
                    </label>
                    <textarea id="idee-description" name="description" rows="4"></textarea>
                </div>
                
                <div class="lepost-admin-actions">
                    <button type="submit" class="button button-primary">
                        <span class="dashicons dashicons-plus"></span>
                        <?php esc_html_e('Ajouter l\'idée', 'lepost-client'); ?>
                    </button>
                    <?php if ($this->api->is_api_key_set()): ?>
                    <button type="button" id="lepost-open-generate-modal" class="button">
                        <span class="dashicons dashicons-lightbulb"></span>
                        <?php esc_html_e('Générer avec l\'IA', 'lepost-client'); ?>
                    </button>
                    <?php endif; ?>
                    <span class="spinner"></span>
                </div>
                
                <div id="lepost-idee-message" class="lepost-admin-message" style="display: none;"></div>
            </form>
        </div>
        
        <!-- Colonne latérale: importation CSV -->
        <div class="lepost-flex-column lepost-flex-side">
            <h4 class="lepost-admin-subsection-title"><?php esc_html_e('Importer depuis un fichier CSV', 'lepost-client'); ?></h4>
            
            <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="lepost-admin-form lepost-import-form">
                <input type="hidden" name="action" value="lepost_import_ideas_submit">
                <?php wp_nonce_field('lepost_import_ideas_nonce', 'lepost_import_ideas_nonce_field'); ?>
                
                <div class="lepost-admin-form-group">
                    <label>
                        <?php esc_html_e('Fichier CSV', 'lepost-client'); ?> <span class="required">*</span>
                        <span class="dashicons dashicons-info-outline lepost-tooltip"
                            title="<?php esc_attr_e('Fichier CSV attendu. Colonne 1: Titre, Colonne 2: Description. La première ligne (en-tête) sera ignorée.', 'lepost-client'); ?>"></span>
                    </label>
                    
                    <div class="lepost-file-upload-wrapper">
                        <input type="file" id="lepost-ideas-file" name="ideas_file" accept=".csv" required style="display:none;">
                        <div class="lepost-file-upload-info" id="lepost-file-selected">
                            <?php esc_html_e('Aucun fichier sélectionné', 'lepost-client'); ?>
                        </div>
                        <button type="button" id="lepost-file-upload-btn" class="button">
                            <span class="dashicons dashicons-upload"></span>
                            <?php esc_html_e('Parcourir...', 'lepost-client'); ?>
                        </button>
                    </div>
                </div>
                
                <div class="lepost-admin-actions">
                    <button type="submit" class="button button-primary">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php esc_html_e('Importer le fichier', 'lepost-client'); ?>
                    </button>
                    <span class="spinner"></span>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- FIN SECTION: FORMULAIRE D'IDÉE --> 