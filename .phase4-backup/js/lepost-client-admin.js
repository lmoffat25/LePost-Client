/**
 * LePost Client - Administration JavaScript simple
 */

(function($) {
    'use strict';

    /**
     * Objet principal du plugin côté administration
     */
    const LePostClient = {
        
        /**
         * Initialisation de l'administration
         */
        init: function() {
            this.setupTabs();
            this.setupSettings();
        },

        /**
         * Configuration des onglets
         */
        setupTabs: function() {
            // Activation des onglets dans l'interface d'administration
            $('.lepost-admin-tabs .nav-tab').on('click', function(e) {
                e.preventDefault();
                
                const target = $(this).attr('href');
                const $tabContent = $(target);
                
                if ($tabContent.length) {
                    // Changer l'onglet actif
                    $('.lepost-admin-tabs .nav-tab').removeClass('nav-tab-active');
                    $(this).addClass('nav-tab-active');
                    
                    // Afficher le contenu correspondant
                    $('.lepost-tab-content').hide();
                    $tabContent.show();
                    
                    // Sauvegarder l'onglet actif dans localStorage
                    localStorage.setItem('lepost-active-tab', target);
                }
            });
            
            // Restaurer l'onglet actif depuis localStorage
            const savedTab = localStorage.getItem('lepost-active-tab');
            if (savedTab) {
                const $savedTabLink = $('.lepost-admin-tabs a[href="' + savedTab + '"]');
                if ($savedTabLink.length) {
                    $savedTabLink.trigger('click');
                }
            }
        },

        /**
         * Configuration des paramètres du plugin
         */
        setupSettings: function() {
            $('#lepost-settings-form').on('submit', function(e) {
                e.preventDefault();
                const $form = $(this);
                const $submitBtn = $form.find('button[type="submit"]');
                const $spinner = $submitBtn.next('.spinner');
                const $message = $('#lepost-settings-message');
                
                // Préparation pour l'envoi AJAX
                const formData = $form.serialize();
                $submitBtn.prop('disabled', true);
                $spinner.addClass('is-active');
                $message.hide();
                
                // Envoi du formulaire via AJAX
                $.ajax({
                    url: lepost_client_params.ajax_url,
                    type: 'POST',
                    data: formData + '&action=lepost_save_settings&nonce=' + lepost_client_params.nonce,
                    success: function(response) {
                        if (response.success) {
                            $message.removeClass().addClass('lepost-admin-message lepost-admin-message-success')
                                .html(response.data.message).show();
                        } else {
                            $message.removeClass().addClass('lepost-admin-message lepost-admin-message-error')
                                .html(response.data).show();
                        }
                    },
                    error: function() {
                        $message.removeClass().addClass('lepost-admin-message lepost-admin-message-error')
                                .html('Erreur de communication avec le serveur.').show();
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false);
                        $spinner.removeClass('is-active');
                    }
                });
            });
        }
    };
    
    // Initialiser le plugin au chargement du document
    $(document).ready(function() {
        LePostClient.init();
    });

})(jQuery); 