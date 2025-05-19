<?php
/**
 * Scripts JavaScript du gestionnaire d'idées d'articles
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
<!-- Scripts spécifiques au gestionnaire d'idées d'articles -->
<script type="text/javascript">
jQuery(document).ready(function($) {
    // Gestion de l'input file personnalisé
    $('#lepost-file-upload-btn').on('click', function(e) {
        e.preventDefault();
        $('#lepost-ideas-file').trigger('click');
    });
    
    $('#lepost-ideas-file').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        if (fileName) {
            $('#lepost-file-selected').text(fileName);
        } else {
            $('#lepost-file-selected').text('<?php esc_html_e('Aucun fichier sélectionné', 'lepost-client'); ?>');
        }
    });

    // Gestion de la modale de génération d'idées IA
    var generateIdeasModal = $('#lepost-generate-ideas-modal');
    var openGenerateModalBtn = $('#lepost-open-generate-modal');
    var closeGenerateModalBtn = $('#lepost-close-generate-modal');

    if (openGenerateModalBtn.length && generateIdeasModal.length) {
        openGenerateModalBtn.on('click', function() {
            generateIdeasModal.show();
        });
    }

    if (closeGenerateModalBtn.length && generateIdeasModal.length) {
        closeGenerateModalBtn.on('click', function() {
            generateIdeasModal.hide();
        });
    }

    // Fermer la modale si on clique en dehors
    $(window).on('click', function(event) {
        if (generateIdeasModal.length && $(event.target).is(generateIdeasModal)) {
            generateIdeasModal.hide();
        }
    });

    // Gestion de la modale de description
    var ideeModal = $('#lepost-idee-modal');
    var closeIdeeModalBtn = ideeModal.find('.lepost-modal-close');

    $(document).on('click', '.lepost-voir-plus', function() {
        var row = $(this).closest('tr');
        var title = row.find('.lepost-idee-titre strong').text();
        var description = row.find('.lepost-idee-description').data('full-description');

        ideeModal.find('#lepost-modal-title').text(title);
        ideeModal.find('#lepost-modal-description').text(description);
        ideeModal.show();
    });

    if (closeIdeeModalBtn.length) {
        closeIdeeModalBtn.on('click', function() {
            ideeModal.hide();
        });
    }
    
    $(window).on('click', function(event) {
        if (ideeModal.length && $(event.target).is(ideeModal)) {
            ideeModal.hide();
        }
    });

    // Gestion de la modification d'une idée
    $(document).on('click', '.lepost-edit-idee', function() {
        var row = $(this).closest('tr');
        var ideeId = row.data('id');
        var titre = row.find('.lepost-idee-titre strong').text();
        var description = row.find('.lepost-idee-description').data('full-description');
        
        // Remplir le formulaire
        $('#idee-id').val(ideeId);
        $('#idee-titre').val(titre);
        $('#idee-description').val(description);
        
        // Changer le texte du bouton
        $('#lepost-idee-form').find('button[type="submit"]').html('<span class="dashicons dashicons-update"></span> <?php esc_html_e('Mettre à jour', 'lepost-client'); ?>');
        
        // Scroller jusqu'au formulaire
        $('html, body').animate({
            scrollTop: $('#lepost-idee-form').offset().top - 50
        }, 500);
    });

    // NOTE: La gestion de la suppression d'idées est implémentée dans lepost-client-admin.js
    // via la méthode setupIdeeActions(). Ne pas ajouter de gestionnaire ici pour éviter la duplication.

    // Amélioration de l'expérience utilisateur pour la génération d'idées
    $('#lepost-ideas-generation-form').on('submit', function() {
        var $form = $(this);
        var $spinner = $form.find('.spinner');
        var $submitBtn = $form.find('#generate-ideas-btn');
        var $infoText = $form.find('.lepost-generation-info');
        var theme = $form.find('#modal-idee-theme').val();
        
        if (!theme) {
            return false;
        }
        
        // Afficher le spinner et désactiver le bouton
        $spinner.addClass('is-active');
        $submitBtn.prop('disabled', true);
        $infoText.show();
        
        // Revenir à l'état initial si l'utilisateur ferme la modale
        $('.lepost-modal-close').on('click', function() {
            $spinner.removeClass('is-active');
            $submitBtn.prop('disabled', false);
            $infoText.hide();
        });
        
        return true;
    });
    
    // Gestion du bouton de génération d'article
    $(document).on('click', '.lepost-generate-article', function() {
        var $button = $(this);
        var ideeId = $button.data('id');
        
        if (!ideeId) {
            alert('<?php esc_html_e('ID d\'idée manquant. Impossible de générer l\'article.', 'lepost-client'); ?>');
            return;
        }
        
        // Ajouter un spinner à côté du bouton
        $button.prop('disabled', true);
        var $row = $button.closest('tr');
        var $spinner = $('<span class="spinner is-active" style="float: none; margin-left: 5px;"></span>');
        $button.after($spinner);
        
        // Appel AJAX pour générer l'article
        $.ajax({
            url: lepost_ideas_manager.ajax_url,
            type: 'POST',
            data: {
                action: 'lepost_generate_article',
                nonce: lepost_ideas_manager.nonce,
                idee_id: ideeId
            },
            success: function(response) {
                $spinner.remove();
                $button.prop('disabled', false);
                
                if (response.success) {
                    // Afficher un message de succès
                    var $successMessage = $('<div class="notice notice-success is-dismissible"><p>' + 
                                           response.data.message + '</p></div>');
                    $('.lepost-admin-section').first().prepend($successMessage);
                    
                    // Mettre à jour le statut de l'idée dans l'UI
                    $row.find('.lepost-idee-status').html('<span class="lepost-status-badge lepost-status-completed">' + 
                                                         lepost_ideas_manager.i18n.status_completed + '</span>');
                    
                    // Faire défiler vers le haut pour voir le message
                    $('html, body').animate({
                        scrollTop: $successMessage.offset().top - 50
                    }, 500);
                    
                    // Enlever le message après 3 secondes
                    setTimeout(function() {
                        $successMessage.fadeOut(500, function() {
                            $(this).remove();
                        });
                    }, 3000);
                } else {
                    // Afficher un message d'erreur
                    alert(response.data || lepost_ideas_manager.i18n.generate_error);
                }
            },
            error: function() {
                $spinner.remove();
                $button.prop('disabled', false);
                alert(lepost_ideas_manager.i18n.generate_error);
            }
        });
    });
});
</script>
<!-- FIN SECTION: SCRIPTS JAVASCRIPT --> 