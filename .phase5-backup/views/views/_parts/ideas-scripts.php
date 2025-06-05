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

    // NOTE: La gestion de la suppression d'idées est implémentée dans lepost-ideas-manager.js
    // via la méthode setupIdeeActions(). Ne pas ajouter de gestionnaire ici pour éviter la duplication.

    // Amélioration de l'expérience utilisateur pour la génération d'idées
    $('#lepost-ideas-generation-form').on('submit', function(e) {
        var $form = $(this);
        var $spinner = $form.find('.spinner');
        var $submitBtn = $form.find('#generate-ideas-btn');
        var $infoText = $form.find('.lepost-generation-info');
        var theme = $form.find('#modal-idee-theme').val();
        
        // Vérification que la soumission vient bien du bon bouton
        if (e.originalEvent && e.originalEvent.submitter) {
            var submitterId = e.originalEvent.submitter.id;
            if (submitterId !== 'generate-ideas-btn') {
                e.preventDefault();
                return false; // Empêcher la soumission si ce n'est pas le bon bouton
            }
        }
        
        if (!theme) {
            alert('<?php esc_html_e('Veuillez saisir un thème pour la génération d\'idées.', 'lepost-client'); ?>');
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
    
    // NOTE: La gestion de la génération d'articles est maintenant implémentée dans lepost-ideas-manager.js
    // avec le nouveau système de notifications modernes. Le code précédent a été supprimé pour éviter 
    // les conflits et la duplication de fonctionnalités.
});
</script>
<!-- FIN SECTION: SCRIPTS JAVASCRIPT --> 