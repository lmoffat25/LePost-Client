/**
 * LePost Ideas Manager - JavaScript simple
 */
(function($) {
    'use strict';

    const LePostIdeasManager = {
        init: function() {
            this.setupGenerateModal();
            this.setupDeleteConfirmation();
        },

        // Créer la modale de génération d'article
        setupGenerateModal: function() {
            // Créer la modale si elle n'existe pas
            if (!$('#lepost-generate-article-modal').length) {
                $('body').append(`
                    <div id="lepost-generate-article-modal" class="lepost-modal" style="display: none;">
                        <div class="lepost-modal-content">
                            <span class="lepost-modal-close">&times;</span>
                            <h3>Générer un article</h3>
                            <p>Voulez-vous générer un article à partir de cette idée ?</p>
                            <div class="lepost-modal-actions">
                                <button type="button" id="lepost-confirm-generate-article" class="button button-primary">
                                    Générer l'article
                                </button>
                                <button type="button" id="lepost-cancel-generate-article" class="button">
                                    Annuler
                                </button>
                            </div>
                        </div>
                    </div>
                `);
            }

            const $modal = $('#lepost-generate-article-modal');
            const $confirmBtn = $('#lepost-confirm-generate-article');
            const $cancelBtn = $('#lepost-cancel-generate-article');
            const $closeBtn = $modal.find('.lepost-modal-close');
            
            let currentIdeeId = null;

            // Ouvrir la modale au clic sur le bouton de génération - Sélecteur plus spécifique
            $(document).on('click', '.lepost-generate-article', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Empêcher la propagation de l'événement
                
                currentIdeeId = $(this).data('id');
                if (currentIdeeId) {
                    $modal.show();
                }
            });

            // Fermer la modale
            $closeBtn.add($cancelBtn).on('click', function() {
                $modal.hide();
                currentIdeeId = null;
            });

            // Clic en dehors de la modale pour fermer
            $(window).on('click', function(e) {
                if ($(e.target).is($modal)) {
                    $modal.hide();
                    currentIdeeId = null;
                }
            });

            // Confirmation de génération
            $confirmBtn.on('click', function() {
                if (!currentIdeeId) return;

                const $btn = $(this);
                $btn.prop('disabled', true).text('Génération...');

                $.ajax({
                    url: lepost_ideas_manager.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'lepost_generate_article_from_idee',
                        nonce: lepost_ideas_manager.nonce,
                        idee_id: currentIdeeId
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Article généré avec succès !');
                            window.location.reload();
                        } else {
                            alert(response.data || 'Erreur lors de la génération');
                        }
                    },
                    error: function() {
                        alert('Erreur de communication avec le serveur');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('Générer l\'article');
                        $modal.hide();
                        currentIdeeId = null;
                    }
                });
            });
        },

        // Gestion simple de la suppression
        setupDeleteConfirmation: function() {
            $(document).on('click', '.lepost-delete-idee', function(e) {
                e.preventDefault();
                
                if (confirm('Êtes-vous sûr de vouloir supprimer cette idée ?')) {
                    const $btn = $(this);
                    const ideeId = $btn.data('id');
                    
                    $btn.prop('disabled', true);
                    
                    $.ajax({
                        url: lepost_ideas_manager.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'lepost_delete_idee',
                            nonce: lepost_ideas_manager.nonce,
                            id: ideeId
                        },
                        success: function(response) {
                            if (response.success) {
                                $btn.closest('tr').fadeOut(400, function() {
                                    $(this).remove();
                                });
                            } else {
                                alert(response.data || 'Erreur lors de la suppression');
                                $btn.prop('disabled', false);
                            }
                        },
                        error: function() {
                            alert('Erreur de communication avec le serveur');
                            $btn.prop('disabled', false);
                        }
                    });
                }
            });
        }
    };

    // Initialiser au chargement
    $(document).ready(function() {
        LePostIdeasManager.init();
    });

})(jQuery); 