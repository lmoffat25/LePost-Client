/**
 * Scripts pour l'administration du plugin LePost Client
 */
(function($) {
    'use strict';

    // Objet principal du plugin
    const LePostClient = {
        /**
         * Initialisation
         */
        init: function() {
            this.setupTabs();
            this.setupIdeeForm();
            this.setupIdeeActions();
            this.setupArticleActions();
            this.setupApiKeyForm();
            this.setupSettings();
        },

        /**
         * Configuration des onglets d'administration
         */
        setupTabs: function() {
            // Vérification du problème de navigation des onglets
            $('.lepost-admin-tabs a.nav-tab').on('click', function() {
                console.log('Tab clicked: ' + $(this).attr('href'));
                // Forcer explicitement la navigation vers cet URL
                window.location.href = $(this).attr('href');
                return true; // Laisser le navigateur traiter normalement le clic
            });
        },

        /**
         * Configuration du formulaire d'idée d'article
         */
        setupIdeeForm: function() {
            $('#lepost-idee-form').on('submit', function(e) {
                e.preventDefault();
                const $form = $(this);
                const $submitBtn = $form.find('button[type="submit"]');
                const $spinner = $submitBtn.next('.spinner');
                const $message = $('#lepost-idee-message');
                
                // Validation de base
                const titre = $form.find('#idee-titre').val();
                if (!titre) {
                    $message.removeClass().addClass('lepost-admin-message lepost-admin-message-error')
                        .html('Le titre est obligatoire.').show();
                    return;
                }
                
                // Préparation pour l'envoi AJAX
                const formData = $form.serialize();
                $submitBtn.prop('disabled', true);
                $spinner.addClass('is-active');
                $message.hide();
                
                // Envoi du formulaire via AJAX
                $.ajax({
                    url: lepost_client_params.ajax_url,
                    type: 'POST',
                    data: formData + '&action=lepost_save_idee&nonce=' + lepost_client_params.nonce,
                    success: function(response) {
                        if (response.success) {
                            $message.removeClass().addClass('lepost-admin-message lepost-admin-message-success')
                                .html(response.data.message).show();
                            
                            // Réinitialiser le formulaire si c'est une nouvelle idée
                            if (!$form.find('#idee-id').val()) {
                                $form.trigger('reset');
                            }
                            
                            // Recharger la liste des idées si elle existe
                            if ($('#lepost-idees-list').length) {
                                setTimeout(function() {
                                    window.location.reload();
                                }, 1500);
                            }
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
        },

        /**
         * Configuration des actions pour les idées d'articles
         */
        setupIdeeActions: function() {
            // Création d'un modal de confirmation personnalisé s'il n'existe pas déjà
            if ($('#lepost-confirm-delete-modal').length === 0) {
                $('body').append(`
                    <div id="lepost-confirm-delete-modal" class="lepost-modal" style="display:none;">
                        <div class="lepost-modal-content">
                            <span class="lepost-modal-close">&times;</span>
                            <h3>Confirmation de suppression</h3>
                            <p>Êtes-vous sûr de vouloir supprimer cette idée d'article ?</p>
                            <div class="lepost-admin-actions">
                                <button type="button" id="lepost-confirm-delete-btn" class="button button-primary">
                                    <span class="dashicons dashicons-yes"></span> Confirmer
                                </button>
                                <button type="button" id="lepost-cancel-delete-btn" class="button">
                                    <span class="dashicons dashicons-no"></span> Annuler
                                </button>
                            </div>
                        </div>
                    </div>
                `);
            }

            // Référence au modal et ses boutons
            const $deleteModal = $('#lepost-confirm-delete-modal');
            const $confirmDeleteBtn = $('#lepost-confirm-delete-btn');
            const $cancelDeleteBtn = $('#lepost-cancel-delete-btn');
            const $closeModalBtn = $deleteModal.find('.lepost-modal-close');
            
            // Fermeture du modal
            $closeModalBtn.add($cancelDeleteBtn).on('click', function() {
                $deleteModal.hide();
            });
            
            // Clic en dehors du modal pour fermer
            $(window).on('click', function(e) {
                if ($(e.target).is($deleteModal)) {
                    $deleteModal.hide();
                }
            });

            // Variables pour stocker les infos sur l'idée à supprimer
            let currentIdeeId = null;
            let currentButton = null;

            // Suppression d'une idée
            $('.lepost-delete-idee').on('click', function(e) {
                e.preventDefault();
                
                currentButton = $(this);
                currentIdeeId = currentButton.data('id');
                
                if (!currentIdeeId) return;
                
                // Afficher le modal de confirmation
                $deleteModal.show();
            });
            
            // Action de confirmation de suppression
            $confirmDeleteBtn.on('click', function() {
                if (!currentIdeeId || !currentButton) {
                    $deleteModal.hide();
                    return;
                }
                
                currentButton.prop('disabled', true);
                
                $.ajax({
                    url: lepost_client_params.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'lepost_delete_idee',
                        nonce: lepost_client_params.nonce,
                        id: currentIdeeId
                    },
                    success: function(response) {
                        if (response.success) {
                            // Supprimer la ligne ou la carte de l'idée
                            currentButton.closest('.lepost-idea-card, tr').fadeOut(400, function() {
                                $(this).remove();
                                
                                // Si c'était la dernière idée, afficher le message "Aucune idée"
                                if ($('.lepost-admin-table tbody tr').length === 0) {
                                    $('#lepost-idees-list').html(`
                                        <div class="lepost-admin-empty-state">
                                            <div class="lepost-admin-empty-icon">
                                                <span class="dashicons dashicons-format-status"></span>
                                            </div>
                                            <h4>Aucune idée d'article trouvée</h4>
                                            <p>Commencez par créer une nouvelle idée d'article ci-dessus ou générez-en automatiquement.</p>
                                        </div>
                                    `);
                                }
                            });
                        } else {
                            alert(response.data || 'Erreur lors de la suppression.');
                            currentButton.prop('disabled', false);
                        }
                    },
                    error: function() {
                        alert('Erreur de communication avec le serveur.');
                        currentButton.prop('disabled', false);
                    },
                    complete: function() {
                        $deleteModal.hide();
                        currentIdeeId = null;
                        currentButton = null;
                    }
                });
            });
            
            // Génération d'article à partir d'une idée
            $('.lepost-generate-article').on('click', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const ideeId = $button.data('id');
                
                if (!confirm('Voulez-vous générer un article à partir de cette idée ? Cela peut prendre quelques instants.')) {
                    return;
                }
                
                $button.prop('disabled', true);
                $button.next('.spinner').addClass('is-active');
                
                $.ajax({
                    url: lepost_client_params.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'lepost_generate_article',
                        nonce: lepost_client_params.nonce,
                        idee_id: ideeId
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            // Rediriger vers le post WordPress créé ou refresher la page
                            if (response.data.post_id) {
                                // Rediriger vers l'édition du post WordPress créé
                                window.location.href = 'post.php?post=' + response.data.post_id + '&action=edit';
                            } else {
                                // Fallback: simplement recharger la page des idées
                                window.location.reload();
                            }
                        } else {
                            alert(response.data);
                        }
                    },
                    error: function() {
                        alert('Erreur de communication avec le serveur.');
                    },
                    complete: function() {
                        $button.prop('disabled', false);
                        $button.next('.spinner').removeClass('is-active');
                    }
                });
            });
        },

        /**
         * Configuration des actions pour les articles générés
         */
        setupArticleActions: function() {
            // ... Similaire à setupIdeeActions, à implémenter selon les besoins
        },

        /**
         * Configuration du formulaire de clé API
         */
        setupApiKeyForm: function() {
            // Déjà géré dans l'écran initial-screen.php
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