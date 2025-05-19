/**
 * LePost Client Ideas Manager - Fonctionnalités JavaScript pour le gestionnaire d'idées
 */

(function($) {
    'use strict';

    /**
     * Gestionnaire d'idées d'articles
     * @type {Object}
     */
    var LePostIdeasManager = {

        /**
         * Initialise le gestionnaire d'idées
         */
        init: function() {
            this.setupEventListeners();
            this.setupConfirmations();
        },

        /**
         * Configure les écouteurs d'événements
         */
        setupEventListeners: function() {
            // Évènements pour le filtre de statut
            $('#lepost-filter-idees-form').on('submit', this.handleFilterSubmit);
            
            // Évènements pour la suppression d'idée
            $('.lepost-delete-idee').on('click', this.handleDeleteIdea);
            
            // Évènements pour la génération d'article à partir d'une idée
            $('.lepost-generate-article').on('click', this.handleGenerateArticle);
            
            // Gestion de la pagination
            $('.lepost-admin-pagination a').on('click', this.handlePagination);
            
            // Mise à jour des boutons du formulaire lors de l'édition
            $('#lepost-reset-form').on('click', this.handleResetForm);
        },

        /**
         * Configure les boîtes de dialogue de confirmation
         */
        setupConfirmations: function() {
            // On utilise le système de confirmation natif du navigateur pour plus de simplicité
            // mais on pourrait utiliser une bibliothèque de dialogue plus avancée
        },

        /**
         * Gère la soumission du formulaire de filtre
         * @param {Event} e Événement de soumission
         */
        handleFilterSubmit: function(e) {
            e.preventDefault();
            
            var status = $('#idee-filter-statut').val();
            var currentUrl = window.location.href;
            var baseUrl = currentUrl.split('?')[0];
            var queryParams = new URLSearchParams(window.location.search);
            
            // Mise à jour des paramètres
            if (status) {
                queryParams.set('status', status);
            } else {
                queryParams.delete('status');
            }
            
            // Réinitialisation de la pagination
            queryParams.delete('paged');
            
            // Redirection
            window.location.href = baseUrl + '?' + queryParams.toString();
        },

        /**
         * Gère la suppression d'une idée
         * @param {Event} e Événement de clic
         */
        handleDeleteIdea: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var id = $button.data('id');
            
            if (!id) {
                return;
            }
            
            if (!confirm(lepost_ideas_manager.i18n.confirm_delete)) {
                return;
            }
            
            $button.prop('disabled', true);
            
            $.ajax({
                url: lepost_client_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'lepost_delete_idee',
                    nonce: lepost_client_params.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        // Animation de suppression
                        $button.closest('tr').fadeOut(300, function() {
                            $(this).remove();
                            
                            // Si plus d'idées, afficher l'état vide
                            if ($('.lepost-admin-table tbody tr').length === 0) {
                                $('.lepost-admin-table').replaceWith(
                                    '<div class="lepost-admin-empty-state">' +
                                    '<div class="lepost-admin-empty-icon">' +
                                    '<span class="dashicons dashicons-format-status"></span>' +
                                    '</div>' +
                                    '<h4>' + lepost_ideas_manager.i18n.no_ideas + '</h4>' +
                                    '<p>' + lepost_ideas_manager.i18n.create_idea + '</p>' +
                                    '</div>'
                                );
                            }
                        });
                    } else {
                        alert(response.data);
                        $button.prop('disabled', false);
                    }
                },
                error: function() {
                    alert(lepost_ideas_manager.i18n.error_delete);
                    $button.prop('disabled', false);
                }
            });
        },

        /**
         * Gère la génération d'un article à partir d'une idée
         * @param {Event} e Événement de clic
         */
        handleGenerateArticle: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var id = $button.data('id');
            
            if (!id) {
                return;
            }
            
            if (!confirm(lepost_ideas_manager.i18n.confirm_generate)) {
                return;
            }
            
            // Désactiver le bouton et afficher l'indicateur de chargement
            $button.prop('disabled', true);
            $button.html('<span class="dashicons dashicons-update spinning"></span>');
            
            $.ajax({
                url: lepost_client_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'lepost_generate_article_from_idee',
                    nonce: lepost_client_params.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        // Mettre à jour l'interface
                        $button.closest('tr').find('.lepost-idee-statut .lepost-status-badge')
                            .removeClass()
                            .addClass('lepost-status-badge lepost-status-completed')
                            .text(lepost_ideas_manager.i18n.status_completed);
                        
                        // Masquer le bouton de génération
                        $button.remove();
                        
                        // Afficher un message de succès
                        alert(response.data.message);
                        
                        // Rediriger vers l'onglet des articles si l'option est activée
                        if (response.data.redirect_url) {
                            window.location.href = response.data.redirect_url;
                        }
                    } else {
                        // Check for our custom JSON error response
                        if (response.data && typeof response.data === 'string') {
                            // Existing simple string error
                            alert(response.data);
                        } else if (response.data && response.data.message) {
                            // Our new structured error with an HTML message
                            // For now, we'll alert the message. Consider using a modal or a dedicated notification area.
                            alert(response.data.message); 
                        } else {
                            // Fallback for unexpected error format
                            alert(lepost_ideas_manager.i18n.error_generate || 'An unknown error occurred.');
                        }
                        // Restaurer le bouton
                        $button.html('<span class="dashicons dashicons-welcome-write-blog"></span>');
                        $button.prop('disabled', false);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var errorMessage = lepost_ideas_manager.i18n.error_generate || 'Error generating article.';
                    if (jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message) {
                        // Our custom error message from wp_send_json_error
                        // The message from PHP is expected to be HTML, so we need to display it appropriately.
                        // For now, we'll use a simple alert. 
                        // A better approach would be to inject this HTML into a dedicated notification area on the page.
                        var decodedMessage = $('<div>').html(jqXHR.responseJSON.data.message).text();
                        alert(decodedMessage);
                    } else {
                        alert(errorMessage);
                    }
                    // Restaurer le bouton
                    $button.html('<span class="dashicons dashicons-welcome-write-blog"></span>');
                    $button.prop('disabled', false);
                }
            });
        },

        /**
         * Gère la pagination
         * @param {Event} e Événement de clic
         */
        handlePagination: function(e) {
            // La pagination est gérée nativement par WordPress via les liens GET
            // Cette fonction est incluse pour permettre d'ajouter des fonctionnalités
            // personnalisées à la pagination si nécessaire
        },

        /**
         * Gère la réinitialisation du formulaire
         * @param {Event} e Événement de clic
         */
        handleResetForm: function(e) {
            e.preventDefault();
            
            // Réinitialisation déjà gérée dans le fichier PHP
            // Cette fonction est incluse pour permettre d'ajouter des comportements
            // supplémentaires si nécessaire
        }
    };

    /**
     * Initialisation au chargement du document
     */
    $(document).ready(function() {
        LePostIdeasManager.init();
        
        // Ajout de CSS pour l'animation de rotation
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .dashicons.spinning {
                    animation: lepost-spin 2s linear infinite;
                }
                @keyframes lepost-spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `)
            .appendTo('head');
    });

})(jQuery); 