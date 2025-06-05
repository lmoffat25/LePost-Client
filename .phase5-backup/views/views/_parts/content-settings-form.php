<?php
/**
 * Formulaire de configuration des paramètres de génération de contenu
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/views/_parts
 */

// Protection directe
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- SECTION: FORMULAIRE DE PARAMÈTRES DE CONTENU -->
<!-- Cette section contient le formulaire de configuration pour personnaliser 
     la génération de contenu selon la marque et l'audience -->
<div class="lepost-admin-card">
    <h3><?php esc_html_e('Configuration de la génération de contenu', 'lepost-client'); ?></h3>
    
    <p class="description">
        <?php esc_html_e('Personnalisez les paramètres utilisés lors de la génération d\'articles pour obtenir un contenu plus adapté à votre marque et à votre audience.', 'lepost-client'); ?>
    </p>
    
    <form id="lepost-content-settings-form" method="post" action="options.php">
        <?php settings_fields('lepost_content_settings_group'); ?>
        
        <!-- Informations sur l'entreprise -->
        <div class="lepost-admin-form-group">
            <label for="company_info">
                <?php esc_html_e('Informations sur l\'entreprise', 'lepost-client'); ?>
                <span class="lepost-tooltip dashicons dashicons-info-outline" 
                      title="<?php esc_attr_e('Ces informations seront utilisées pour contextualiser le contenu généré et le rendre plus pertinent pour votre marque.', 'lepost-client'); ?>">
                </span>
            </label>
            <textarea id="company_info" name="lepost_content_settings[company_info]" rows="3" class="large-text"><?php echo esc_textarea($content_settings['company_info']); ?></textarea>
            <p class="description">
                <?php esc_html_e('Exemple : "Agence Web Prism, spécialisée en marketing digital depuis 2015, aide les PME à développer leur présence en ligne."', 'lepost-client'); ?>
            </p>
        </div>
        
        <!-- Style d'écriture -->
        <div class="lepost-admin-form-group">
            <label for="writing_style_article">
                <?php esc_html_e('Style d\'écriture pour les articles', 'lepost-client'); ?>
                <span class="lepost-tooltip dashicons dashicons-info-outline" 
                      title="<?php esc_attr_e('Définissez le ton et le style que vous souhaitez pour vos articles générés.', 'lepost-client'); ?>">
                </span>
            </label>
            <textarea id="writing_style_article" name="lepost_content_settings[writing_style][article]" rows="3" class="large-text"><?php echo esc_textarea($content_settings['writing_style']['article']); ?></textarea>
            <p class="description">
                <?php esc_html_e('Exemple : "Professionnel et pédagogique, avec des exemples concrets et des explications détaillées. Ton conversationnel mais expert."', 'lepost-client'); ?>
            </p>
        </div>
        
        <!-- Bouton d'enregistrement -->
        <div class="lepost-admin-form-group">
            <button type="submit" class="button button-primary">
                <span class="dashicons dashicons-saved"></span>
                <?php esc_html_e('Enregistrer les paramètres', 'lepost-client'); ?>
            </button>
        </div>
    </form>
</div>
<!-- FIN SECTION: FORMULAIRE DE PARAMÈTRES DE CONTENU --> 