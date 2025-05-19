<?php
/**
 * La classe d'administration du plugin
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin
 */

namespace LePostClient\Admin;

use LePostClient\Api\Api;
use LePostClient\ContentType\Idee;
use LePostClient\ContentType\Article;
use LePostClient\Admin\TabsManager\IdeasManager;
use LePostClient\Admin\TabsManager\DashboardTab;
use LePostClient\Admin\TabsManager\SettingsTab;

/**
 * Classe Admin
 *
 * Cette classe définit toutes les fonctionnalités de l'interface d'administration.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin
 */
class Admin {

    /**
     * Le nom unique qui identifie ce plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    Le nom du plugin.
     */
    private $plugin_name;

    /**
     * La version actuelle du plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    La version actuelle du plugin.
     */
    private $version;

    /**
     * Instance de la classe API
     *
     * @since    1.0.0
     * @access   private
     * @var      Api       $api        Instance de la classe API.
     */
    private $api;

    /**
     * Tableau des onglets disponibles
     *
     * @since    1.0.0
     * @access   private
     * @var      array     $tabs       Tableau des onglets.
     */
    private $tabs = [];

    /**
     * Initialise la classe et définit ses propriétés.
     *
     * @since    1.0.0
     * @param    string    $plugin_name    Le nom du plugin.
     * @param    string    $version        La version du plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->api = new Api();
        

        // Vérifier et initialiser les paramètres par défaut si nécessaire
        $default_settings = [
            'autopost_articles' => true, // Toujours activé, mais gardé pour compatibilité
            'default_status' => 'draft',
            'default_category' => 0
        ];
        
        if (false === get_option('lepost_client_settings')) {
            update_option('lepost_client_settings', $default_settings);
        }
        
        // Enregistrer les paramètres
        add_action('admin_init', array($this, 'register_settings'));
        
        // Action AJAX pour tester la génération d'article
        add_action('wp_ajax_lepost_test_article_generation', array($this, 'test_article_generation'));
        
        // Initialisation des onglets
        add_action('init', array($this, 'init_tabs'));
        
        // Filtrer les onglets disponibles
        add_filter('lepost_client_admin_tabs', array($this, 'register_tabs'));
    }

    /**
     * Debug du problème de autopost pour comprendre pourquoi la valeur n'est pas utilisée
     */
    private function debug_autopost_settings() {
        global $wpdb;
        
        // 1. Vérifier directement dans la table des options
        $option_value = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
                'lepost_client_settings'
            )
        );
        
        
        // 2. Vérifier avec get_option
        $settings = get_option('lepost_client_settings', []);
        
        // 3. Vérification manuelle de la valeur
        $autopost_value = isset($settings['autopost_articles']) ? ($settings['autopost_articles'] ? 'true' : 'false') : 'non défini';
        
        // 4. Force la mise à jour et re-vérification
        $settings['autopost_articles'] = true;
        
        $updated_settings = get_option('lepost_client_settings', []);
    }

    /**
     * Force la mise à jour des paramètres pour activer la création automatique de posts
     *
     * @since    1.0.0
     */
    private function force_update_autopost_setting() {
        $current_settings = get_option('lepost_client_settings', []);
        
        // Assurer que autopost_articles est toujours activé
        if (!isset($current_settings['autopost_articles']) || $current_settings['autopost_articles'] !== true) {
            $current_settings['autopost_articles'] = true;
            update_option('lepost_client_settings', $current_settings);
        }
    }

    /**
     * Initialise les onglets disponibles.
     *
     * @since    1.0.0
     */
    public function init_tabs() {
        // Onglet du tableau de bord
        if (class_exists('\\LePostClient\\Admin\\TabsManager\\DashboardTab')) {
            $this->tabs['dashboard'] = new DashboardTab();
        }
        
        // Onglet du gestionnaire d'idées
        $this->tabs['ideas'] = new IdeasManager();
        
        // Onglet unifié des paramètres
        if (class_exists('\\LePostClient\\Admin\\TabsManager\\SettingsTab')) {
            $this->tabs['settings'] = new SettingsTab();
        }
    }

    /**
     * Enregistre les onglets pour le filtre.
     *
     * @since    1.0.0
     * @param    array    $tabs    Tableau des onglets existants.
     * @return   array             Tableau des onglets mis à jour.
     */
    public function register_tabs($tabs) {
        return array_merge($tabs, $this->tabs);
    }

    /**
     * Enregistre les styles pour l'administration.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        // Styles principales de l'admin
        wp_enqueue_style($this->plugin_name, LEPOST_CLIENT_PLUGIN_URL . 'assets/css/lepost-client-admin.css', array(), $this->version, 'all');
        
        $current_page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'dashboard';

        if ($current_page === $this->plugin_name || $current_page === 'lepost-client') {
            switch ($current_tab) {
                case 'dashboard':
                    wp_enqueue_style($this->plugin_name . '-dashboard', LEPOST_CLIENT_PLUGIN_URL . 'assets/css/lepost-dashboard.css', array($this->plugin_name), $this->version, 'all');
                    break;
                case 'settings':
                    // wp_enqueue_style($this->plugin_name . '-content-settings', LEPOST_CLIENT_PLUGIN_URL . 'assets/css/lepost-content-settings.css', array($this->plugin_name), $this->version, 'all'); // Commented out as redundant
                    wp_enqueue_style($this->plugin_name . '-settings-tabs', LEPOST_CLIENT_PLUGIN_URL . 'assets/css/lepost-settings-tabs.css', array($this->plugin_name), $this->version, 'all');
                    break;
            }
        }
    }

    /**
     * Enregistre les scripts pour l'administration.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Script principal
        wp_enqueue_script($this->plugin_name, LEPOST_CLIENT_PLUGIN_URL . 'assets/js/lepost-client-admin.js', array('jquery'), $this->version, false);
        
        wp_localize_script($this->plugin_name, 'lepost_client_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lepost_client_nonce'),
            'is_api_key_set' => $this->api->is_api_key_set()
        ));
        
        $current_page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'dashboard'; 

        if ($current_page === $this->plugin_name || $current_page === 'lepost-client') { 
            switch ($current_tab) {
                case 'dashboard':
                    wp_enqueue_script($this->plugin_name . '-dashboard', LEPOST_CLIENT_PLUGIN_URL . 'assets/js/lepost-dashboard.js', array('jquery'), $this->version, true);
                    wp_localize_script($this->plugin_name . '-dashboard', 'lepost_dashboard_params', array(
                        'urls' => array(
                            'ideas_page' => admin_url('admin.php?page=lepost-client&tab=ideas')
                        ),
                        'i18n' => array(
                            'confirm_generate_article' => __('Voulez-vous générer un article à partir de cette idée?', 'lepost-client')
                        )
                    ));
                    break;
                
                case 'settings':
                    wp_enqueue_script($this->plugin_name . '-settings-tabs', LEPOST_CLIENT_PLUGIN_URL . 'assets/js/lepost-settings-tabs.js', array('jquery'), $this->version, true);
                    wp_localize_script($this->plugin_name . '-settings-tabs', 'lepost_settings_tabs_params', array(
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'nonce_test_generation' => wp_create_nonce('lepost_content_nonce'),
                        'i18n' => array(
                            'subject_required' => __('Le sujet de l\'article est requis.', 'lepost-client'),
                            'error_generating' => __('Une erreur est survenue lors de la tentative de génération de l\'article.', 'lepost-client'),
                            'network_error' => __('Erreur réseau. Veuillez vérifier votre connexion et réessayer.', 'lepost-client')
                        )
                    ));
                    break;
            }
        }
    }

    /**
     * Ajoute les pages de menu dans l'administration.
     *
     * @since    1.0.0
     */
    public function add_menu_pages() {
        // Menu principal unique
        add_menu_page(
            __('LePost Client', 'lepost-client'),
            __('LePost Client', 'lepost-client'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_admin_page'),
            'dashicons-edit',
            20
        );
    }

    /**
     * Affiche la page principale d'administration.
     *
     * @since    1.0.0
     */
    public function display_admin_page() {
        if (!$this->api->is_api_key_set()) {
            include_once LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/initial-screen.php';
        } else {
            include_once LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/wrapper.php';
        }
    }

    /**
     * Enregistre les paramètres du plugin dans WordPress.
     * 
     * @since    1.0.0
     */
    public function register_settings() {
        // Enregistrer les paramètres de génération de contenu
        register_setting(
            'lepost_content_settings_group',  // Option group
            'lepost_content_settings',       // Option name
            [
                'sanitize_callback' => [$this, 'sanitize_content_settings']
            ]
        );
        
        // Enregistrer les paramètres généraux du plugin
        register_setting(
            'lepost_client_settings_group',  // Option group
            'lepost_client_settings',        // Option name
            [
                'sanitize_callback' => [$this, 'sanitize_plugin_settings']
            ]
        );
        
        // Remarque: lepost_client_advanced_settings_group n'était pas explicitement enregistré ici.
        // Les options (lepost_client_api_url, lepost_client_api_timeout, lepost_client_disable_ssl_verify)
        // ne seront plus sauvegardées via un formulaire.

        // Log des paramètres actuels
        $current_settings = get_option('lepost_client_settings', []);
    }

    /**
     * Sanitize les paramètres de contenu.
     * 
     * @since    1.0.0
     * @param    array    $input    Les valeurs à sanitizer.
     * @return   array              Les valeurs sanitisées.
     */
    public function sanitize_content_settings($input) {
        $sanitized = [];
        
        if (isset($input['company_info'])) {
            $sanitized['company_info'] = sanitize_textarea_field($input['company_info']);
        }
        
        if (isset($input['writing_style']) && is_array($input['writing_style'])) {
            foreach ($input['writing_style'] as $key => $value) {
                $sanitized['writing_style'][$key] = sanitize_textarea_field($value);
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitize les paramètres généraux du plugin.
     * 
     * @since    1.0.0
     * @param    array    $input    Les valeurs à sanitizer.
     * @return   array              Les valeurs sanitisées.
     */
    public function sanitize_plugin_settings($input) {
        $sanitized = [];
        
        // Option pour créer automatiquement des articles WordPress (toujours activé)
        $sanitized['autopost_articles'] = true;
        
        // Statut par défaut des articles
        if (isset($input['default_status'])) {
            $sanitized['default_status'] = in_array($input['default_status'], ['draft', 'publish', 'pending', 'private'])
                ? $input['default_status']
                : 'draft';
        } else {
            $sanitized['default_status'] = 'draft';
        }
        
        // Catégorie par défaut
        if (isset($input['default_category'])) {
            $sanitized['default_category'] = (int) $input['default_category'];
        }
        
        // Option de mise à jour automatique
        if (isset($input['enable_auto_updates'])) {
            $sanitized['enable_auto_updates'] = $input['enable_auto_updates'] ? '1' : '0';
        } else {
            $sanitized['enable_auto_updates'] = '0';
        }
        
        return $sanitized;
    }

    /**
     * Teste la génération d'article (AJAX).
     *
     * @since    1.0.0
     */
    public function test_article_generation() {
        check_ajax_referer('lepost_content_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Vous n\'avez pas les permissions nécessaires.', 'lepost-client'));
        }
        
        $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
        $subject_explanation = isset($_POST['subject_explanation']) ? sanitize_textarea_field($_POST['subject_explanation']) : '';
        
        if (empty($subject)) {
            wp_send_json_error(__('Le sujet est requis.', 'lepost-client'));
        }
        
        // Récupérer les paramètres de contenu
        $content_settings = get_option('lepost_content_settings', []);
        
        // Paramètres pour la génération
        $params = [
            'titre' => $subject,
            'description' => $subject_explanation,
            'company_info' => isset($content_settings['company_info']) ? $content_settings['company_info'] : '',
            'writing_style' => isset($content_settings['writing_style']) ? $content_settings['writing_style'] : []
        ];
        
        // Appel à l'API pour générer l'article
        $api_result = $this->api->generate_article($params);
        
        if (!$api_result['success']) {
            wp_send_json_error(esc_html($api_result['message']));
        }
        
        wp_send_json_success([
            'message' => __('Article généré avec succès!', 'lepost-client'),
            'article' => $api_result['article']
        ]);
    }

    /**
     * Génère un article à partir d'une idée (AJAX).
     *
     * @since    1.0.0
     */
    public function generate_article() {
        check_ajax_referer('lepost_client_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Vous n\'avez pas les permissions nécessaires.', 'lepost-client'));
        }
        
        
        $idee_id = intval($_POST['idee_id']);
        
        if (empty($idee_id)) {
            wp_send_json_error(__('ID d\'idée invalide.', 'lepost-client'));
        }
        
        // Récupérer l'idée
        $idee_model = new Idee();
        $idee = $idee_model->get_by_id($idee_id);
        
        if (!$idee) {
            wp_send_json_error(__('Idée d\'article non trouvée.', 'lepost-client'));
        }
        
        // === NOUVELLE VÉRIFICATION COMMENCE ICI ===
        $article_model_check = new Article(); // Utiliser un nom de variable différent pour éviter conflit si $article_model est utilisé plus bas

        // Vérifier si un article (table custom) existe déjà pour cette idée
        $existing_custom_article = $article_model_check->get_by_idee_id($idee_id);

        if ($existing_custom_article && !empty($existing_custom_article->post_id)) {
            // Vérifier si le post WordPress existe réellement et n'est pas à la corbeille
            $post_status = get_post_status($existing_custom_article->post_id);
            if ($post_status && $post_status !== 'trash' && $post_status !== false) {
                $post_edit_link = get_edit_post_link($existing_custom_article->post_id);
                $message = sprintf(
                    // translators: %s is an URL, %d is a post ID.
                    __('Un article WordPress (<a href="%s" target="_blank">ID: %d</a>) existe déjà pour cette idée. Veuillez le modifier ou le supprimer si vous souhaitez générer un nouveau contenu.', 'lepost-client'),
                    esc_url($post_edit_link ?: '#'),
                    $existing_custom_article->post_id
                );
                wp_send_json_error(['message' => $message, 'existing_post_id' => $existing_custom_article->post_id]);
                return; // Arrêter le traitement
            }
        }
        // === FIN DE LA NOUVELLE VÉRIFICATION ===
        
        // Récupérer les paramètres de contenu
        $content_settings = get_option('lepost_content_settings', []);
        
        // Paramètres pour la génération
        $params = [
            'titre' => $idee->titre,
            'description' => $idee->description,
            'company_info' => isset($content_settings['company_info']) ? $content_settings['company_info'] : '',
            'writing_style' => isset($content_settings['writing_style']) ? $content_settings['writing_style'] : []
        ];
        
        
        // Appel à l'API pour générer l'article
        $api_result = $this->api->generate_article($params);
        
        // Vérifier si le résultat est une erreur WordPress
        if (is_wp_error($api_result)) {
            wp_send_json_error(esc_html($api_result->get_error_message()));
        }
        
        // Vérifier si l'opération a réussi
        if (!isset($api_result['success']) || !$api_result['success']) {
            $error_message = isset($api_result['message']) ? $api_result['message'] : __('Erreur inconnue lors de la génération de l\'article.', 'lepost-client');
            wp_send_json_error(esc_html($error_message));
        }
        
        
        // Vérification complète de la structure attendue
        if (!isset($api_result['article'])) {
            wp_send_json_error(__('Format de réponse API inattendu: clé "article" manquante.', 'lepost-client'));
        }
        
        if (!isset($api_result['article']['content'])) {
            wp_send_json_error(__('Format de réponse API inattendu: clé "content" manquante.', 'lepost-client'));
        }
        
        
        // Enregistrer l'article généré
        $article_data = [
            'idee_id' => $idee_id,
            'titre' => isset($api_result['article']['title']) ? $api_result['article']['title'] : $idee->titre,
            'contenu' => $api_result['article']['content'],
            'statut' => 'draft'
        ];
        

        $article_model = new Article();
        $article_id = $article_model->create($article_data);
        
        if (!$article_id) {
            wp_send_json_error(__('Erreur lors de l\'enregistrement de l\'article généré.', 'lepost-client'));
        }
        
        
        // Créer un article WordPress (toujours activé)
        $settings = get_option('lepost_client_settings', []);
        
        // $post_id = $this->create_wp_post($article_data, $settings); // Ancien appel
        // Utiliser la méthode statique de la classe Article
        $post_id = Article::createWpPost($article_data, $settings);
        
        if ($post_id) {

            // Mettre à jour l'article custom avec le post_id et un nouveau statut
            $update_data = [
                'id'      => $article_id,
                'post_id' => $post_id,
                'statut'  => 'published_wp' // Ou un autre statut pertinent comme 'autoposted'
            ];
            $update_article_result = $article_model->update($update_data);

            if (!$update_article_result) {
                // Logguer une erreur si la mise à jour du statut échoue, mais continuer car le post WP est créé.
                error_log("LePost Client: Échec de la mise à jour du statut de l'article custom ID {$article_id} après création du post WP ID {$post_id}.");
            }
            
            // Supprimer l'idée après la création réussie du post WordPress
            $delete_result = $idee_model->delete($idee_id);
            // Vous pourriez vouloir vérifier $delete_result ici aussi
            
        } 
        
        
        wp_send_json_success([
            'message' => __('Article généré avec succès!', 'lepost-client'),
            'article_id' => $article_id,
            'post_id' => $post_id ?: null
        ]);
    }

    /**
     * Sauvegarde la clé API (AJAX).
     *
     * @since    1.0.0
     */
    public function save_api_key() {
        check_ajax_referer('lepost_client_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Vous n\'avez pas les permissions nécessaires pour effectuer cette action.', 'lepost-client'));
        }

        $api_key = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';

        if (empty($api_key)) {
            wp_send_json_error(__('Veuillez fournir une clé API.', 'lepost-client'));
        }
        
        // Ici, vous devriez probablement appeler une méthode de votre classe Api pour stocker la clé
        // Par exemple: $this->api->set_api_key($api_key);
        // Et ensuite vérifier le résultat de cette opération.
        
        // Pour l'exemple, nous allons juste simuler une sauvegarde réussie
        // update_option('lepost_client_api_key', $api_key); // La classe API s'en charge déjà

        $this->api->set_api_key($api_key); // Utilise la méthode existante dans Api.php
        
        // Vérifier si la clé est bien enregistrée (optionnel, set_api_key le fait déjà)
        if ($this->api->get_api_key() === $api_key) {
             wp_send_json_success([
                'message' => __('Clé API enregistrée avec succès.', 'lepost-client'),
                'api_key_set' => true
            ]);
        } else {
            wp_send_json_error(__('Erreur lors de l\'enregistrement de la clé API.', 'lepost-client'));
        }
    }

    /**
     * Teste la connexion à l'API (AJAX).
     *
     * @since    1.0.0
     */
    public function test_api_connection() {
        check_ajax_referer('lepost_client_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Vous n\'avez pas les permissions nécessaires pour effectuer cette action.', 'lepost-client'));
        }

        // Assurez-vous que la clé API est configurée avant de tester
        if (!$this->api->is_api_key_set()) {
            wp_send_json_error([
                'message' => __('Veuillez d\'abord enregistrer une clé API.', 'lepost-client'),
                'api_key_set' => false // Ajout pour que le JS puisse réagir
            ]);
        }

        $result = $this->api->verify_connection();

        if ($result['success']) {
            wp_send_json_success([
                'message' => esc_html($result['message']),
                'api_key_set' => true // Confirmer que la clé est (ou était) là
            ]);
        } else {
            wp_send_json_error([
                'message' => esc_html($result['message']),
                'api_key_set' => $this->api->is_api_key_set() // Indiquer l'état actuel de la clé
            ]);
        }
    }
} 