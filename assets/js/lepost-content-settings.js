/**
 * JavaScript spécifique pour la page de paramètres de contenu de LePost
 */
(function($) {
    'use strict';

    // Objet pour les fonctionnalités des paramètres de contenu
    const LePostContentSettings = {
        /**
         * Initialisation des fonctionnalités
         */
        init: function() {
            this.setupTestArticleForm();
        },

        /**
         * Configuration du formulaire de test d'article
         */
        setupTestArticleForm: function() {
            $('#lepost-test-article-form').on('submit', function(e) {
                e.preventDefault();
                
                var $form = $(this);
                var $button = $form.find('button[type="submit"]');
                var $spinner = $form.find('.spinner');
                var $result = $('#lepost-test-article-result');
                
                // Désactiver le bouton et afficher le spinner
                $button.prop('disabled', true);
                $spinner.addClass('is-active');
                $result.hide();
                
                // Requête AJAX
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'lepost_test_article_generation',
                        nonce: lepost_content_settings.nonce,
                        subject: $('#test_subject').val(),
                        subject_explanation: $('#test_subject_explanation').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            $result.removeClass('notice-error').addClass('notice-success').html(response.data.message).show();
                            
                            // Afficher un aperçu du contenu généré
                            if (response.data.article && response.data.article.content) {
                                $result.append(
                                    '<div class="lepost-article-preview">' + 
                                    '<h4>' + response.data.article.title + '</h4>' + 
                                    '<div class="lepost-article-content">' + 
                                    response.data.article.content.substring(0, 500) + '...</div></div>'
                                );
                            }
                        } else {
                            $result.removeClass('notice-success').addClass('notice-error').html(response.data).show();
                        }
                    },
                    error: function() {
                        $result.removeClass('notice-success')
                               .addClass('notice-error')
                               .html(lepost_content_settings.i18n.error_communication)
                               .show();
                    },
                    complete: function() {
                        // Réactiver le bouton et masquer le spinner
                        $button.prop('disabled', false);
                        $spinner.removeClass('is-active');
                    }
                });
            });
        }
    };

    // Initialiser les fonctionnalités au chargement du document
    $(document).ready(function() {
        LePostContentSettings.init();
    });

})(jQuery); 