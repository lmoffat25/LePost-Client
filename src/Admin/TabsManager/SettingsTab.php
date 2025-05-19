<?php
/**
 * Onglet des paramètres unifiés
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/TabsManager
 */

namespace LePostClient\Admin\TabsManager;

use LePostClient\Api\Api;

/**
 * Classe SettingsTab
 *
 * Cette classe gère l'onglet unifié des paramètres du plugin.
 * Elle combine les fonctionnalités de GeneralTab, ContentSettingsTab et AdvancedTab.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/TabsManager
 */
class SettingsTab extends AbstractSettingsTab {

    /**
     * Instance de la classe API
     *
     * @since    1.0.0
     * @access   private
     * @var      Api    $api    Instance de l'API
     */
    private $api;

    /**
     * Constructeur de la classe
     *
     * @since    1.0.0
     */
    public function __construct() {
        parent::__construct('settings', __('Paramètres', 'lepost-client'), 20);
        $this->api = new Api();
        
        // Enregistrer les paramètres
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Retourne l'icône à utiliser pour l'onglet
     * 
     * @since    1.0.0
     * @return   string    Classe CSS de l'icône Dashicons
     */
    public function get_icon() {
        return 'dashicons-admin-settings';
    }

    /**
     * Enregistre les paramètres pour tous les types de réglages
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // Paramètres généraux
        register_setting(
            'lepost_client_settings_group',
            'lepost_client_settings',
            [
                'sanitize_callback' => [$this, 'sanitize_plugin_settings']
            ]
        );

        // Paramètres de contenu
        register_setting(
            'lepost_content_settings_group',
            'lepost_content_settings',
            [
                'sanitize_callback' => [$this, 'sanitize_content_settings']
            ]
        );

        // Paramètres avancés
        register_setting(
            'lepost_client_advanced_settings_group',
            'lepost_client_api_url'
        );
        
        register_setting(
            'lepost_client_advanced_settings_group',
            'lepost_client_api_timeout',
            [
                'default' => 90,
                'sanitize_callback' => 'absint'
            ]
        );
        
        register_setting(
            'lepost_client_advanced_settings_group',
            'lepost_client_disable_ssl_verify',
            [
                'default' => false,
                'sanitize_callback' => function($value) {
                    return (bool) $value;
                }
            ]
        );
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
        
        error_log('LePost: Paramètres sanitisés - ' . json_encode($sanitized));
        
        return $sanitized;
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
     * Rendu du contenu de l'onglet
     *
     * @since    1.0.0
     */
    public function render_content() {
        // Inclure la vue unifiée des paramètres
        include LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/tab-settings.php';
    }

    /**
     * Ajoute des scripts spécifiques à l'onglet
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        
        // Scripts spécifiques pour les tests de génération d'article (Supprimés)
        /*
        wp_enqueue_script(
            'lepost-content-settings',
            LEPOST_CLIENT_PLUGIN_URL . 'assets/js/lepost-content-settings.js',
            array('jquery'),
            LEPOST_CLIENT_VERSION,
            true
        );
        
        wp_localize_script('lepost-content-settings', 'lepost_content_settings', array(
            'nonce' => wp_create_nonce('lepost_content_nonce'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'i18n' => array(
                'error_generating' => __('Erreur lors de la génération de l\'article.', 'lepost-client'),
                'network_error' => __('Erreur de connexion au serveur.', 'lepost-client'),
                'subject_required' => __('Le sujet est requis.', 'lepost-client')
            )
        ));
        */
    }

    /**
     * Méthode pour obtenir les messages de notification
     *
     * @since    1.0.0
     * @return   array    Tableau de messages de notification
     */
    public function get_notifications() {
        $notifications = [];
        
        // Notifications des paramètres généraux
        $notifications[] = [
            'type' => 'info',
            'message' => __('Les articles générés sont automatiquement créés comme articles WordPress.', 'lepost-client')
        ];
        
        // Notifications des paramètres de contenu
        if (!$this->api->is_api_key_set()) {
            $notifications[] = [
                'type' => 'error',
                'message' => __('La clé API n\'est pas configurée. Ces paramètres ne seront pas utilisés tant que la clé API n\'est pas configurée.', 'lepost-client')
            ];
        }
        
        // Notifications des paramètres avancés
        $current_api_url = get_option('lepost_client_api_url', LEPOST_API_BASE_URL);
        if ($current_api_url !== LEPOST_API_BASE_URL && $current_api_url !== 'https://dev-wordpress.agence-web-prism.fr') {
            $notifications[] = [
                'type' => 'warning',
                'message' => sprintf(
                    __('Vous utilisez une URL d\'API personnalisée : %s', 'lepost-client'),
                    '<code>' . esc_html($current_api_url) . '</code>'
                )
            ];
        }
        
        if (get_option('lepost_client_disable_ssl_verify', false)) {
            $notifications[] = [
                'type' => 'error',
                'message' => __('ATTENTION : La vérification SSL est désactivée. Cette configuration n\'est pas recommandée en production.', 'lepost-client')
            ];
        }
        
        return $notifications;
    }
} 