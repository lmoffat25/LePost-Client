<?php
/**
 * Gestionnaire des idées d'articles
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/TabsManager
 */

namespace LePostClient\Admin\TabsManager;

use LePostClient\ContentType\Idee;
use LePostClient\Api\Api;

/**
 * Classe IdeasManager
 *
 * Cette classe gère l'interface d'administration pour les idées d'articles.
 * Elle permet de créer, lire, mettre à jour et supprimer des idées d'articles.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/TabsManager
 */
class IdeasManager extends AbstractSettingsTab {

    /**
     * Instance du modèle Idee
     *
     * @since    1.0.0
     * @access   private
     * @var      Idee    $idee_model    Instance du modèle Idee
     */
    private $idee_model;

    /**
     * Instance de l'API
     *
     * @since    1.0.0
     * @access   private
     * @var      Api    $api    Instance de l'API
     */
    private $api;

    /**
     * Nombre d'idées par page pour la pagination
     *
     * @since    1.0.0
     * @access   private
     * @var      int    $per_page    Nombre d'idées par page
     */
    private $per_page = 10;

    /**
     * Constructeur de la classe
     *
     * @since    1.0.0
     */
    public function __construct() {
        parent::__construct('ideas', __('Gestionnaire d\'idées', 'lepost-client'), 10);
        
        $this->idee_model = new Idee();
        $this->api = new Api('lepost-client', LEPOST_CLIENT_VERSION);
        
        // Hooks AJAX pour la gestion des idées
        add_action('wp_ajax_lepost_get_idees', array($this, 'ajax_get_idees'));
        add_action('wp_ajax_lepost_save_idee', array($this, 'ajax_save_idee'));
        add_action('wp_ajax_lepost_delete_idee', array($this, 'ajax_delete_idee'));
        
        // Ajouter l'action pour charger les scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Hook pour le traitement du formulaire de génération d'idées
        add_action('admin_post_lepost_generate_ideas_submit', array($this, 'handle_generate_ideas_submission'));
        // Hook pour le traitement du formulaire d'importation d'idées
        add_action('admin_post_lepost_import_ideas_submit', array($this, 'handle_import_ideas_submission'));
    }

    /**
     * Rendu du contenu de l'onglet
     *
     * @since    1.0.0
     */
    public function render_content() {
        // Récupération des idées pour l'affichage
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        
        // Récupérer les idées avec pagination
        $result = $this->idee_model->get_all($current_page, $this->per_page);
        
        // Extraire les idées et le total du résultat
        $idees = $result['idees'];
        $total_idees = $result['total'];
        
        // Calculer le nombre total de pages
        $total_pages = ceil($total_idees / $this->per_page);
        
        // Inclure la vue avec les données
        include LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/tab-ideas-manager.php';
    }

    /**
     * Retourne l'icône à utiliser pour l'onglet
     * 
     * @since    1.0.0
     * @return   string    Classe CSS de l'icône Dashicons
     */
    public function get_icon() {
        return 'dashicons-lightbulb';
    }

    /**
     * Récupère les idées via AJAX
     *
     * @since    1.0.0
     */
    public function ajax_get_idees() {
        check_ajax_referer('lepost_client_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Vous n\'avez pas les permissions nécessaires.', 'lepost-client'));
        }
        
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        
        // Récupérer les idées avec pagination
        $result = $this->idee_model->get_all($page, $this->per_page);
        
        wp_send_json_success([
            'idees' => $result['idees'],
            'total' => $result['total'],
            'pages' => ceil($result['total'] / $this->per_page)
        ]);
    }

    /**
     * Sauvegarde une idée via AJAX
     *
     * @since    1.0.0
     */
    public function ajax_save_idee() {
        check_ajax_referer('lepost_client_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Vous n\'avez pas les permissions nécessaires.', 'lepost-client'));
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $titre = isset($_POST['titre']) ? sanitize_text_field($_POST['titre']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        
        if (empty($titre)) {
            wp_send_json_error(__('Le titre est requis.', 'lepost-client'));
        }
        
        $idee_data = array(
            'titre' => $titre,
            'description' => $description,
        );
        
        if ($id > 0) {
            // Mise à jour d'une idée existante
            $idee_data['id'] = $id;
            $result = $this->idee_model->update($idee_data);
            $message = __('Idée mise à jour avec succès.', 'lepost-client');
        } else {
            // Création d'une nouvelle idée
            $result = $this->idee_model->save($idee_data);
            $message = __('Idée créée avec succès.', 'lepost-client');
        }
        
        if ($result) {
            wp_send_json_success([
                'message' => $message,
                'id' => is_numeric($result) ? $result : $id
            ]);
        } else {
            wp_send_json_error(__('Une erreur est survenue lors de la sauvegarde de l\'idée.', 'lepost-client'));
        }
    }

    /**
     * Supprime une idée via AJAX
     *
     * @since    1.0.0
     */
    public function ajax_delete_idee() {
        check_ajax_referer('lepost_client_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Vous n\'avez pas les permissions nécessaires.', 'lepost-client'));
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if ($id <= 0) {
            wp_send_json_error(__('ID d\'idée invalide.', 'lepost-client'));
        }
        
        $result = $this->idee_model->delete($id);
        
        if ($result) {
            wp_send_json_success([
                'message' => __('Idée supprimée avec succès.', 'lepost-client')
            ]);
        } else {
            wp_send_json_error(__('Une erreur est survenue lors de la suppression de l\'idée.', 'lepost-client'));
        }
    }

    /**
     * Ajout de scripts spécifiques à l'onglet
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Script principal du gestionnaire d'idées
        wp_enqueue_script(
            'lepost-ideas-manager',
            LEPOST_CLIENT_PLUGIN_URL . 'assets/js/lepost-ideas-manager.js',
            array('jquery'),
            LEPOST_CLIENT_VERSION,
            true
        );
        
        wp_localize_script('lepost-ideas-manager', 'lepost_ideas_manager', array(
            'nonce' => wp_create_nonce('lepost_client_nonce'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'i18n' => array(
                'confirm_delete' => __('Êtes-vous sûr de vouloir supprimer cette idée ?', 'lepost-client'),
                'no_title' => __('Veuillez saisir un titre pour l\'idée.', 'lepost-client'),
                'error_saving' => __('Erreur lors de la sauvegarde de l\'idée.', 'lepost-client'),
                'error_loading' => __('Erreur lors du chargement des idées.', 'lepost-client'),
                'generate_success' => __('Idées générées avec succès !', 'lepost-client'),
                'generate_error' => __('Erreur lors de la génération des idées.', 'lepost-client'),
                'confirm_generate' => __('Voulez-vous générer des idées avec ce thème ?', 'lepost-client'),
                'no_ideas' => __('Aucune idée trouvée', 'lepost-client'),
                'create_idea' => __('Créez ou générez de nouvelles idées.', 'lepost-client'),
                'error_delete' => __('Erreur lors de la suppression.', 'lepost-client'),
                'idea_saved' => __('Idée sauvegardée avec succès.', 'lepost-client'),
                'idea_updated' => __('Idée mise à jour avec succès.', 'lepost-client'),
                'idea_deleted' => __('Idée supprimée avec succès.', 'lepost-client'),
                'confirm_generate_article' => __('Voulez-vous générer un article à partir de cette idée ?', 'lepost-client')
            )
        ));
    }

    /**
     * Obtient les notifications pour cet onglet
     *
     * @since    1.0.0
     * @return   array    Les notifications à afficher
     */
    public function get_notifications() {
        $notifications = [];
        
        // Vérifier si l'API key est configurée
        if (!$this->api->is_api_key_set()) {
            $notifications[] = [
                'type' => 'error',
                'message' => __('La clé API n\'est pas configurée. La génération d\'idées ne sera pas disponible.', 'lepost-client')
            ];
        }
        
        // Vérifier le nombre d'idées
        $total_idees = $this->idee_model->count_all();
        
        if ($total_idees === 0) {
            $notifications[] = [
                'type' => 'info',
                'message' => __('Vous n\'avez pas encore d\'idées d\'articles. Utilisez le formulaire ci-dessous pour en créer ou générer.', 'lepost-client')
            ];
        }
        
        return $notifications;
    }

    /**
     * Récupère les informations d'usage gratuit
     *
     * @return array|null Informations d'usage gratuit ou null en cas d'erreur
     */
    private function get_free_usage_info() {
        try {
            return $this->api->get_free_usage_info(false); // Use cache
        } catch (Exception $e) {
            error_log('Erreur lors de la récupération des informations d\'usage gratuit: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère les données d'usage gratuit pour JavaScript
     *
     * @return array Données d'usage gratuit formatées pour JS
     */
    public function get_free_usage_data_for_js() {
        $free_usage_info = $this->get_free_usage_info();
        
        if ($free_usage_info && isset($free_usage_info['free_usage'])) {
            $usage = $free_usage_info['free_usage'];
            return [
                'has_free_usage' => true,
                'ideas_used_this_month' => $usage['ideas_used_this_month'],
                'ideas_remaining_free' => $usage['ideas_remaining_free'],
                'total_free_per_month' => $usage['total_free_per_month'],
                'percentage_used' => $usage['percentage_used'],
                'next_reset_date' => $usage['next_reset_date']
            ];
        }
        
        return [
            'has_free_usage' => false,
            'ideas_used_this_month' => 0,
            'ideas_remaining_free' => 0,
            'total_free_per_month' => 50,
            'percentage_used' => 0,
            'next_reset_date' => date('Y-m-01', strtotime('first day of next month'))
        ];
    }

    /**
     * Gère la soumission du formulaire de génération d'idées.
     *
     * @since 1.0.0
     */
    public function handle_generate_ideas_submission() {
        if (!isset($_POST['lepost_generate_ideas_nonce_field']) || !wp_verify_nonce($_POST['lepost_generate_ideas_nonce_field'], 'lepost_generate_ideas_nonce')) {
            wp_die(__('Échec de la vérification de sécurité (nonce). Veuillez réessayer.', 'lepost-client'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour effectuer cette action.', 'lepost-client'));
        }

        $theme = isset($_POST['theme']) ? sanitize_text_field($_POST['theme']) : '';
        $nombre = isset($_POST['nombre']) ? intval($_POST['nombre']) : 5;

        if (empty($theme)) {
            wp_redirect(add_query_arg('lepost_message', 'theme_required', wp_get_referer()));
            exit;
        }

        if (!$this->api->is_api_key_set()) {
            wp_redirect(add_query_arg('lepost_message', 'api_key_missing', wp_get_referer()));
            exit;
        }
        
        // Log la demande de génération d'idées
        error_log(sprintf(
            'Demande de génération de %d idées sur le thème "%s"',
            $nombre,
            $theme
        ));

        // Utiliser la méthode de l'API pour générer les idées
        $result = $this->api->generate_ideas($theme, $nombre);

        // Check if result is a WP_Error object first
        if (is_wp_error($result)) {
            error_log('Erreur WP_Error lors de la génération d\'idées: ' . $result->get_error_message());
            wp_redirect(add_query_arg('lepost_message', 'api_error_communication', wp_get_referer()));
            exit;
        }

        // Now we can safely check the array
        if (!$result['success']) {
            error_log('Erreur lors de la génération d\'idées: ' . $result['message']);
            
            if (isset($result['raw_response'])) {
                error_log('Réponse brute de l\'API: ' . $result['raw_response']);
            }
            
            if (isset($result['status_code'])) {
                wp_redirect(add_query_arg([
                    'lepost_message' => 'api_error_response', 
                    'api_response_code' => $result['status_code']
                ], wp_get_referer()));
                exit;
            }
            
            wp_redirect(add_query_arg('lepost_message', 'api_error_communication', wp_get_referer()));
            exit;
        }

        $generated_ideas = $result['ideas'] ?? [];
        
        if (empty($generated_ideas)) {
            wp_redirect(add_query_arg('lepost_message', 'no_ideas_generated', wp_get_referer()));
            exit;
        }
        
        // Utiliser la méthode statique de la classe Idee pour sauvegarder les idées
        $save_result = Idee::createManyFromApiData($generated_ideas, $this->idee_model);
        $idees_saved_count = $save_result['saved_count'];
        $skipped_count = $save_result['skipped_count'];
        
        if ($idees_saved_count > 0 && $skipped_count > 0) {
            wp_redirect(add_query_arg([
                'lepost_message' => 'ideas_partially_saved',
                'count' => $idees_saved_count,
                'skipped' => $skipped_count
            ], wp_get_referer()));
            exit;
        } else if ($idees_saved_count > 0) {
            wp_redirect(add_query_arg('lepost_message', 'ideas_generated_successfully', wp_get_referer()));
            exit;
        } else {
            wp_redirect(add_query_arg('lepost_message', 'no_ideas_saved', wp_get_referer()));
            exit;
        }
    }

    /**
     * Gère la soumission du formulaire d'importation d'idées depuis un fichier CSV.
     *
     * @since 1.0.0
     */
    public function handle_import_ideas_submission() {
        if (!isset($_POST['lepost_import_ideas_nonce_field']) || !wp_verify_nonce($_POST['lepost_import_ideas_nonce_field'], 'lepost_import_ideas_nonce')) {
            wp_die(__( 'Échec de la vérification de sécurité (nonce). Veuillez réessayer.', 'lepost-client'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__( 'Vous n\'avez pas les permissions nécessaires pour effectuer cette action.', 'lepost-client'));
        }

        if (!isset($_FILES['ideas_file']) || $_FILES['ideas_file']['error'] === UPLOAD_ERR_NO_FILE) {
            wp_redirect(add_query_arg('lepost_message', 'import_no_file', wp_get_referer()));
            exit;
        }

        if ($_FILES['ideas_file']['error'] !== UPLOAD_ERR_OK) {
            wp_redirect(add_query_arg('lepost_message', 'import_error_upload', wp_get_referer()));
            exit;
        }

        $file_type = mime_content_type($_FILES['ideas_file']['tmp_name']);
        if ($file_type !== 'text/csv' && $file_type !== 'application/csv') { // Autoriser les deux mimetypes courants pour CSV
            // On pourrait aussi vérifier l'extension .csv $_FILES['ideas_file']['name']
            wp_redirect(add_query_arg('lepost_message', 'import_invalid_file_type', wp_get_referer()));
            exit;
        }

        $file_path = $_FILES['ideas_file']['tmp_name'];
        $imported_count = 0;
        $skipped_count = 0;
        $csv_rows = [];

        if (($handle = fopen($file_path, 'r')) !== FALSE) {
            $is_first_row = true; // Pour ignorer l'en-tête
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($is_first_row) {
                    $is_first_row = false;
                    continue;
                }
                $csv_rows[] = $data; // Collecter toutes les lignes de données
            }
            fclose($handle);

            if (!empty($csv_rows)) {
                // Utiliser la méthode statique de la classe Idee pour importer les idées
                $import_result = Idee::createManyFromCsv($csv_rows, $this->idee_model);
                $imported_count = $import_result['imported_count'];
                $skipped_count = $import_result['skipped_count'];
            }

            if ($imported_count > 0 && $skipped_count > 0) {
                wp_redirect(add_query_arg(['lepost_message' => 'import_partial_success', 'count' => $imported_count, 'skipped' => $skipped_count], wp_get_referer()));
            } elseif ($imported_count > 0) {
                wp_redirect(add_query_arg(['lepost_message' => 'import_success', 'count' => $imported_count], wp_get_referer()));
            } elseif ($skipped_count > 0) {
                wp_redirect(add_query_arg(['lepost_message' => 'import_nothing_new', 'skipped' => $skipped_count], wp_get_referer())); // ou un message plus spécifique sur les erreurs/doublons
            } else {
                 wp_redirect(add_query_arg('lepost_message', 'import_nothing_new', wp_get_referer())); // Fichier vide ou que des en-têtes
            }
        } else {
            wp_redirect(add_query_arg('lepost_message', 'import_error_processing', wp_get_referer()));
        }
        exit;
    }
}
