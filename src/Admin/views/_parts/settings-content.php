<?php
/**
 * Partiel pour les paramètres de contenu
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/views/_parts
 */

// Protection directe
if (!defined('ABSPATH')) {
    exit;
}

?>
<!-- PARAMÈTRES DE CONTENU -->
<div class="lepost-admin-card">
    <h3><?php esc_html_e('Paramètres de génération de contenu', 'lepost-client'); ?></h3>
    
    <form method="post" action="options.php" class="lepost-admin-form">
        <?php settings_fields('lepost_content_settings_group'); ?>
        
        <div class="lepost-admin-form-group">
            <label for="company_info"><?php esc_html_e('Informations sur votre entreprise', 'lepost-client'); ?></label>
            <textarea name="lepost_content_settings[company_info]" id="company_info" rows="5" class="large-text"><?php echo esc_textarea($content_settings['company_info'] ?? ''); ?></textarea>
            <p class="description"><?php esc_html_e('Décrivez votre entreprise, ses valeurs, son secteur d\'activité, etc. Ces informations seront utilisées pour personnaliser le contenu généré.', 'lepost-client'); ?></p>
        </div>
        
        <div class="lepost-admin-form-group">
            <label for="writing_style_article"><?php esc_html_e('Style d\'écriture préféré pour les articles', 'lepost-client'); ?></label>
            <textarea name="lepost_content_settings[writing_style][article]" id="writing_style_article" rows="4" class="large-text"><?php echo esc_textarea($content_settings['writing_style']['article'] ?? ''); ?></textarea>
            <p class="description"><?php esc_html_e('Décrivez le style d\'écriture que vous souhaitez pour vos articles (ton, niveau de langage, structure préférée, etc.).', 'lepost-client'); ?></p>
        </div>
        
        <div class="lepost-admin-form-submit">
            <?php submit_button(__('Enregistrer les paramètres', 'lepost-client'), 'primary', 'submit_content_settings', false, ['id' => 'submit_content_settings']); ?>
        </div>
    </form>
</div>

<!-- La section suivante pour le test de génération d'article est supprimée
    
    <h3><?php esc_html_e('Tester la génération d\'article', 'lepost-client'); ?></h3>
    
    <div class="lepost-admin-form-group">
        <label for="test_subject"><?php esc_html_e('Sujet de l\'article', 'lepost-client'); ?> <span class="required">*</span></label>
        <input type="text" id="test_subject" name="test_subject" class="regular-text" required>
    </div>
    
    <div class="lepost-admin-form-group">
        <label for="test_explanation"><?php esc_html_e('Explication du sujet', 'lepost-client'); ?></label>
        <textarea id="test_explanation" name="test_explanation" rows="3" class="large-text"></textarea>
        <p class="description"><?php esc_html_e('Ajoutez des détails ou des précisions sur le sujet de l\'article à générer.', 'lepost-client'); ?></p>
    </div>
    
    <div class="lepost-admin-form-group">
        <button type="button" id="lepost-test-generation" class="button button-primary">
            <?php esc_html_e('Générer un exemple d\'article', 'lepost-client'); ?>
        </button>
        <span id="lepost-generation-loader" class="spinner lepost-loader"></span>
    </div>
    
    <div id="lepost-test-result" class="lepost-test-result lepost-hidden">
        <h4><?php esc_html_e('Aperçu de l\'article généré', 'lepost-client'); ?></h4>
        <div id="lepost-article-preview" class="lepost-article-preview"></div>
    </div>

--> 