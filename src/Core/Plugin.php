<?php
/**
 * La classe principale du plugin
 *
 * Cette classe est responsable de l'initialisation de toutes les composantes 
 * du plugin LePost Client.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Core
 */

namespace LePostClient\Core;

/**
 * Classe principale du plugin
 *
 * Cette classe est responsable de:
 * - Charger les dépendances
 * - Définir les hooks d'administration
 * - Définir les hooks publics
 *
 * @package    LePostClient
 * @subpackage LePostClient/Core
 */
class Plugin {

    /**
     * Le chargeur responsable de maintenir et d'enregistrer tous les hooks du plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Loader    $loader    Maintient et enregistre tous les hooks du plugin.
     */
    protected $loader;

    /**
     * Le nom unique qui identifie ce plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    Le nom du plugin.
     */
    protected $plugin_name;

    /**
     * La version actuelle du plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    La version actuelle du plugin.
     */
    protected $version;

    /**
     * Définit les fonctionnalités de base du plugin.
     *
     * - Définit le nom et la version du plugin.
     * - Charge les dépendances nécessaires.
     * - Définit les hooks pour la partie admin.
     * - Définit les hooks pour la partie publique.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->plugin_name = 'lepost-client';
        $this->version = LEPOST_CLIENT_VERSION;

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_api_hooks();
    }

    /**
     * Charge les dépendances requises pour ce plugin.
     *
     * Crée une instance du chargeur qui sera utilisé pour enregistrer les hooks
     * avec WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        // La classe responsable d'orchestrer les actions et filtres du plugin
        require_once LEPOST_CLIENT_PLUGIN_DIR . 'src/Core/Loader.php';
        
        // La classe responsable de définir les fonctionnalités administratives
        require_once LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/Admin.php';
        
        // La classe responsable de gérer les appels à l'API LePost
        require_once LEPOST_CLIENT_PLUGIN_DIR . 'src/Api/Api.php';
        
        // Les modèles de données
        require_once LEPOST_CLIENT_PLUGIN_DIR . 'src/ContentType/Idee.php';
        require_once LEPOST_CLIENT_PLUGIN_DIR . 'src/ContentType/Article.php';

        $this->loader = new Loader();
    }

    /**
     * Enregistre tous les hooks liés à la fonctionnalité d'administration du plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $admin = new \LePostClient\Admin\Admin($this->get_plugin_name(), $this->get_version());

        // Hooks principaux de l'admin
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $admin, 'add_menu_pages');
        
        // AJAX hooks
        $this->loader->add_action('wp_ajax_lepost_save_api_key', $admin, 'save_api_key');
        $this->loader->add_action('wp_ajax_lepost_test_api_connection', $admin, 'test_api_connection');
        $this->loader->add_action('wp_ajax_lepost_generate_article', $admin, 'generate_article');

        // Initialiser et enregistrer les onglets
        $tabs = [
            new \LePostClient\Admin\TabsManager\GeneralTab(),
            new \LePostClient\Admin\TabsManager\DashboardTab(),
            new \LePostClient\Admin\TabsManager\IdeasManager(),
            new \LePostClient\Admin\TabsManager\ContentSettingsTab(),
        ];
        
        // Enregistrer les onglets auprès de l'admin
        $admin->register_tabs($tabs);
    }
    
    /**
     * Enregistre tous les hooks liés aux fonctionnalités de l'API.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_api_hooks() {
        $api = new \LePostClient\Api\Api($this->get_plugin_name(), $this->get_version());
        
    }

    /**
     * Exécute le chargeur pour exécuter tous les hooks avec WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * Le nom utilisé pour identifier de manière unique ce plugin.
     *
     * @since     1.0.0
     * @return    string    Le nom du plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * La référence à la classe qui orchestre les hooks du plugin.
     *
     * @since     1.0.0
     * @return    Loader    Orchestre les hooks du plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Récupère le numéro de version du plugin.
     *
     * @since     1.0.0
     * @return    string    Le numéro de version du plugin.
     */
    public function get_version() {
        return $this->version;
    }
}
