<?php
/**
 * Scripts JavaScript pour les paramètres de contenu
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/views/_parts
 */

// Protection directe
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- SECTION: SCRIPTS JAVASCRIPT -->
<!-- Scripts spécifiques aux paramètres de contenu et à la génération d'articles de test -->
<script type="text/javascript">
jQuery(document).ready(function($) {
    // Gestionnaire pour le formulaire de test de génération d'article
    $('#lepost-test-article-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $spinner = $form.find('.spinner');
        var $submitBtn = $form.find('button[type="submit"]');
        var $resultBox = $('#lepost-test-article-result');
        
        // Récupérer les données du formulaire
        var subject = $('#test_subject').val();
        var subject_explanation = $('#test_subject_explanation').val();
        
        // Vérification des champs obligatoires
        if (!subject) {
            $resultBox.removeClass('notice-success').addClass('notice-error')
                .html('<p><?php esc_html_e('Veuillez saisir un sujet pour l\'article.', 'lepost-client'); ?></p>')
                .show();
            return;
        }
        
        // Récupérer les paramètres de contenu du formulaire principal
        var company_info = $('#company_info').val();
        var writing_style_article = $('#writing_style_article').val();
        
        // Afficher le spinner et désactiver le bouton
        $spinner.addClass('is-active');
        $submitBtn.prop('disabled', true);
        $resultBox.html('').hide();
        
        // Préparer les données pour l'API
        var apiData = {
            action: 'lepost_test_article_generation',
            nonce: lepost_content_settings.nonce,
            subject: subject,
            subject_explanation: subject_explanation,
            company_info: company_info,
            writing_style: {
                article: writing_style_article
            },
            publication_type: ['article']
        };
        
        // Appel AJAX pour générer l'article de test
        $.ajax({
            url: lepost_content_settings.ajax_url,
            type: 'POST',
            data: apiData,
            dataType: 'json',
            success: function(response) {
                $spinner.removeClass('is-active');
                $submitBtn.prop('disabled', false);
                
                if (response.success) {
                    // Afficher le contenu généré
                    $resultBox.removeClass('notice-error').addClass('notice-success')
                        .html('<h4><?php esc_html_e('Article généré avec succès !', 'lepost-client'); ?></h4>' + 
                              '<div class="lepost-generated-content">' + 
                              response.data.html +
                              '</div>')
                        .show();
                } else {
                    // Afficher le message d'erreur
                    $resultBox.removeClass('notice-success').addClass('notice-error')
                        .html('<p>' + (response.data.message || '<?php esc_html_e('Une erreur est survenue lors de la génération de l\'article.', 'lepost-client'); ?>') + '</p>')
                        .show();
                }
            },
            error: function(xhr, status, error) {
                $spinner.removeClass('is-active');
                $submitBtn.prop('disabled', false);
                
                // Afficher le message d'erreur
                $resultBox.removeClass('notice-success').addClass('notice-error')
                    .html('<p><?php esc_html_e('Erreur de communication avec le serveur.', 'lepost-client'); ?> ' + error + '</p>')
                    .show();
            }
        });
    });
});
</script>
<!-- FIN SECTION: SCRIPTS JAVASCRIPT --> 