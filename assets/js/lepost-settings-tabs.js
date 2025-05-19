/**
 * Scripts pour les onglets des paramètres LePost Client
 */
jQuery(document).ready(function($) {
    // Le contenu sera ajouté ici une fois le fichier tab-settings.php complet sera disponible.

    // Test de génération d'article (depuis l'onglet Contenu)
    // lepost_settings_tabs_params est localisé par Admin.php et contient maintenant les clés pour cela
    if (typeof lepost_settings_tabs_params !== 'undefined' && lepost_settings_tabs_params.nonce_test_generation) {
        $('body').on('click', '#lepost-test-generation', function() {
            var subject = $('#test_subject').val();
            var explanation = $('#test_explanation').val();
            
            if (!subject) {
                alert(lepost_settings_tabs_params.i18n.subject_required || 'Le sujet de l\'article est requis.');
                return;
            }
            
            var $button = $(this);
            var $loader = $('#lepost-generation-loader');
            var $resultDiv = $('#lepost-test-result');
            var $previewDiv = $('#lepost-article-preview');
            
            $button.prop('disabled', true);
            $loader.addClass('is-active').css('visibility', 'visible');
            $resultDiv.addClass('lepost-hidden').hide();
            
            $.ajax({
                url: lepost_settings_tabs_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'lepost_test_article_generation',
                    nonce: lepost_settings_tabs_params.nonce_test_generation, // Updated to use the unified params object
                    subject: subject,
                    subject_explanation: explanation
                },
                success: function(response) {
                    if (response.success) {
                        $previewDiv.html(response.data.html || 'Contenu non disponible.');
                        $resultDiv.removeClass('lepost-hidden').show();
                    } else {
                        alert(response.data.message || response.data || lepost_settings_tabs_params.i18n.error_generating || 'Erreur de génération.');
                    }
                },
                error: function() {
                    alert(lepost_settings_tabs_params.i18n.network_error || 'Erreur réseau.');
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $loader.removeClass('is-active').css('visibility', 'hidden');
                }
            });
        });
    }

    // Test de connexion à l'API (REMOVED)
    /*
    if (typeof lepost_settings_tabs_params !== 'undefined') {
        $('body').on('click', '#lepost-test-api-connection', function() {
            var $button = $(this);
            var $resultSpan = $('#lepost-api-connection-result');
            var apiUrl = $('#lepost_client_api_url').val();

            $button.prop('disabled', true);
            $resultSpan.html('<span class="spinner is-active" style="vertical-align: middle;"></span> ' + (lepost_settings_tabs_params.i18n.testing_connection || 'Test en cours...'));
            
            $.ajax({
                url: lepost_settings_tabs_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'lepost_client_test_api_connection',
                    nonce: lepost_settings_tabs_params.nonce_test_connection,
                    api_url: apiUrl 
                },
                success: function(response) {
                    if (response.success) {
                        $resultSpan.html('<span class="dashicons dashicons-yes-alt" style="color:green;margin-right:5px;"></span> ' + (response.data.message || 'Connexion réussie!'));
                    } else {
                        $resultSpan.html('<span class="dashicons dashicons-warning" style="color:red;margin-right:5px;"></span> ' + (response.data.message || 'Échec de la connexion.'));
                    }
                },
                error: function() {
                    $resultSpan.html('<span class="dashicons dashicons-warning" style="color:red;margin-right:5px;"></span> ' + (lepost_settings_tabs_params.i18n.error_testing_connection || 'Erreur lors du test.'));
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        });
    }
    */
}); 