<?php
/**
 * Notifications du gestionnaire d'idées d'articles
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/views/_parts
 */

// Protection directe
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- SECTION: MESSAGES DE NOTIFICATION -->
<!-- Cette section affiche les messages de feedback suite aux actions utilisateur -->
<?php 
// Affichage des messages de feedback
if (isset($_GET['lepost_message'])) {
    $message_type = $_GET['lepost_message'];
    $message_text = '';
    $message_class = 'notice-info'; // Classe par défaut

    switch ($message_type) {
        case 'theme_required':
            $message_text = __('Veuillez spécifier un thème pour la génération d\'idées.', 'lepost-client');
            $message_class = 'notice-error';
            break;
        case 'api_key_missing':
            $message_text = __('La clé API n\'est pas configurée. Veuillez la configurer dans les réglages.', 'lepost-client');
            $message_class = 'notice-error';
            break;
        case 'api_error_communication':
            $message_text = __('Erreur de communication avec le service de génération d\'idées.', 'lepost-client');
            $message_class = 'notice-error';
            break;
        case 'api_error_response':
            $message_text = sprintf(
                __('Erreur de l\'API lors de la génération d\'idées (Code: %s).', 'lepost-client'), 
                isset($_GET['api_response_code']) ? esc_html($_GET['api_response_code']) : 'N/A'
            );
            $message_class = 'notice-error';
            break;
        case 'api_unexpected_format':
            $message_text = __('Format de réponse inattendu du service de génération d\'idées.', 'lepost-client');
            $message_class = 'notice-error';
            break;
        case 'no_ideas_generated':
            $message_text = __('Aucune idée n\'a été générée par l\'API. Essayez avec un thème différent.', 'lepost-client');
            $message_class = 'notice-warning';
            break;
        case 'ideas_partially_saved':
            $count = isset($_GET['count']) ? intval($_GET['count']) : 0;
            $skipped = isset($_GET['skipped']) ? intval($_GET['skipped']) : 0;
            $message_text = sprintf(
                __('%d idées ont été sauvegardées avec succès et %d idées ont été ignorées (doublons ou erreurs).', 'lepost-client'),
                $count,
                $skipped
            );
            $message_class = 'notice-warning';
            break;
        case 'ideas_generated_successfully':
            $message_text = __('Idées générées et sauvegardées avec succès !', 'lepost-client');
            $message_class = 'notice-success';
            break;
        case 'no_ideas_saved':
            $message_text = __('Aucune nouvelle idée n\'a été sauvegardée (elles existaient peut-être déjà ou une erreur s\'est produite).', 'lepost-client');
            $message_class = 'notice-warning';
            break;
        case 'import_no_file':
            $message_text = __('Aucun fichier n\'a été sélectionné pour l\'importation.', 'lepost-client');
            $message_class = 'notice-error';
            break;
        case 'import_invalid_file_type':
            $message_text = __('Type de fichier invalide. Veuillez téléverser un fichier CSV.', 'lepost-client');
            $message_class = 'notice-error';
            break;
        case 'import_error_upload':
            $message_text = __('Une erreur s\'est produite lors du téléversement du fichier.', 'lepost-client');
            $message_class = 'notice-error';
            break;
        case 'import_error_processing':
            $message_text = __('Une erreur s\'est produite lors du traitement du fichier.', 'lepost-client');
            $message_class = 'notice-error';
            break;
        case 'import_success':
            $count = isset($_GET['count']) ? intval($_GET['count']) : 0;
            $message_text = sprintf(
                _n(
                    '%d idea imported successfully!',
                    '%d ideas imported successfully!',
                    $count,
                    'lepost-client'
                ),
                $count
            );
            $message_class = 'notice-success';
            break;
        case 'import_partial_success':
             $count = isset($_GET['count']) ? intval($_GET['count']) : 0;
             $skipped_count = isset($_GET['skipped']) ? intval($_GET['skipped']) : 0;
             $message_text = sprintf(esc_html__('%d ideas imported, %d ideas skipped (duplicates or errors).', 'lepost-client'), $count, $skipped_count);
             if ($count === 0 && $skipped_count > 0) {
                 $message_text = sprintf(esc_html__('No new ideas imported. %d ideas were skipped (duplicates or errors).', 'lepost-client'), $skipped_count);
             } else if ($count > 0 && $skipped_count === 0) {
                 $message_text = sprintf(esc_html__('%d ideas imported successfully. No ideas were skipped.', 'lepost-client'), $count);
             }
             // Si $count > 0 et $skipped_count > 0, le premier message est déjà correct.
             // Si $count === 0 et $skipped_count === 0, ce cas est géré par 'import_nothing_new'
            $message_class = 'notice-warning';
            break;
        case 'import_nothing_new':
            $message_text = __('Aucune nouvelle idée n\'a été importée. Les idées du fichier existaient peut-être déjà ou le fichier était vide.', 'lepost-client');
            $message_class = 'notice-info';
            break;
    }
    if ($message_text) {
        echo '<div class="notice '. esc_attr($message_class) .' is-dismissible"><p>' . esc_html($message_text) . '</p></div>';
    }
}
?>
<!-- FIN SECTION: MESSAGES DE NOTIFICATION --> 