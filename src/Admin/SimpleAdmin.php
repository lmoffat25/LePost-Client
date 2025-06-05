<?php
/**
 * Simplified Admin Class
 *
 * Replaces the complex tab-based admin system with standard WordPress patterns.
 * Focuses on essential functionality without over-engineering.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin
 * @since      2.0.0
 */

namespace LePostClient\Admin;

use LePostClient\Api\Api;
use LePostClient\Admin\Pages\DashboardPage;
use LePostClient\Admin\Pages\IdeasPage;
use LePostClient\Admin\Pages\SettingsPage;
use LePostClient\Admin\Pages\GenerateArticlePage;

/**
 * SimpleAdmin Class
 *
 * Simplified admin interface using standard WordPress admin patterns.
 * No complex tab system or excessive JavaScript dependencies.
 */
class SimpleAdmin {

    /**
     * Plugin name
     *
     * @since 2.0.0
     * @var string
     */
    private $plugin_name;

    /**
     * Plugin version
     *
     * @since 2.0.0
     * @var string
     */
    private $version;

    /**
     * API instance
     *
     * @since 2.0.0
     * @var Api
     */
    private $api;

    /**
     * Page controllers
     *
     * @since 2.0.0
     * @var array
     */
    private $page_controllers = [];

    /**
     * Constructor
     *
     * @since 2.0.0
     * @param string $plugin_name Plugin name
     * @param string $version Plugin version
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->api = new Api();
        
        // Only initialize page controllers when we're in admin and admin is fully loaded
        if (is_admin()) {
            add_action('admin_init', [$this, 'init_page_controllers']);
        }
        
        $this->init_settings();
    }

    /**
     * Initialize page controllers
     *
     * @since 2.0.0
     */
    public function init_page_controllers() {
        // Only initialize if we haven't already and we're in admin context
        if (empty($this->page_controllers) && is_admin()) {
            $this->page_controllers = [
                'dashboard' => new DashboardPage($this->api),
                'ideas' => new IdeasPage($this->api),
                'settings' => new SettingsPage($this->api),
                'generate-article' => new GenerateArticlePage($this->api),
            ];
        }
    }

    /**
     * Initialize WordPress settings
     *
     * @since 2.0.0
     */
    private function init_settings() {
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Register WordPress settings
     *
     * @since 2.0.0
     */
    public function register_settings() {
        // Initialize settings for each page controller
        if (isset($this->page_controllers['settings'])) {
            $this->page_controllers['settings']->init_settings();
        }
    }

    /**
     * Add admin menu pages
     *
     * @since 2.0.0
     */
    public function add_admin_menu() {
        // Main menu page - Dashboard
        add_menu_page(
            __('LePost Client', 'lepost-client'),
            __('LePost', 'lepost-client'),
            'manage_options',
            'lepost-client',
            [$this, 'render_dashboard_page'],
            'dashicons-edit-large',
            30
        );

        // Dashboard submenu
        add_submenu_page(
            'lepost-client',
            __('Dashboard', 'lepost-client'),
            __('Dashboard', 'lepost-client'),
            'manage_options',
            'lepost-client',
            [$this, 'render_dashboard_page']
        );

        // Ideas submenu
        add_submenu_page(
            'lepost-client',
            __('Ideas Manager', 'lepost-client'),
            __('Ideas', 'lepost-client'),
            'manage_options',
            'lepost-ideas',
            [$this, 'render_ideas_page']
        );

        // Generate Articles submenu
        add_submenu_page(
            'lepost-client',
            __('Generate Articles', 'lepost-client'),
            __('Generate Articles', 'lepost-client'),
            'manage_options',
            'lepost-generate-article',
            [$this, 'render_generate_article_page']
        );

        // Settings submenu
        add_submenu_page(
            'lepost-client',
            __('Settings', 'lepost-client'),
            __('Settings', 'lepost-client'),
            'manage_options',
            'lepost-settings',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Render dashboard page
     *
     * @since 2.0.0
     */
    public function render_dashboard_page() {
        $this->ensure_page_controllers_loaded();
        if (isset($this->page_controllers['dashboard'])) {
            $this->page_controllers['dashboard']->render();
        }
    }

    /**
     * Render ideas page
     *
     * @since 2.0.0
     */
    public function render_ideas_page() {
        $this->ensure_page_controllers_loaded();
        if (isset($this->page_controllers['ideas'])) {
            $this->page_controllers['ideas']->render();
        }
    }

    /**
     * Render generate article page
     *
     * @since 2.0.0
     */
    public function render_generate_article_page() {
        $this->ensure_page_controllers_loaded();
        if (isset($this->page_controllers['generate-article'])) {
            $this->page_controllers['generate-article']->render();
        }
    }

    /**
     * Render settings page
     *
     * @since 2.0.0
     */
    public function render_settings_page() {
        $this->ensure_page_controllers_loaded();
        if (isset($this->page_controllers['settings'])) {
            $this->page_controllers['settings']->render();
        }
    }

    /**
     * Ensure page controllers are loaded
     *
     * @since 2.0.0
     */
    private function ensure_page_controllers_loaded() {
        if (empty($this->page_controllers)) {
            $this->init_page_controllers();
        }
    }

    /**
     * Enqueue admin styles
     *
     * @since 2.0.0
     */
    public function enqueue_styles() {
        $current_screen = get_current_screen();
        
        // Only load on our plugin pages
        if (!$current_screen || strpos($current_screen->id, 'lepost') === false) {
            return;
        }

        // Main admin styles
        wp_enqueue_style(
            $this->plugin_name . '-admin',
            LEPOST_CLIENT_PLUGIN_URL . 'assets/css/lepost-admin-simple.css',
            [],
            $this->version,
            'all'
        );

        // WordPress admin styles for consistency
        wp_enqueue_style('wp-list-table');
        wp_enqueue_style('common');
        wp_enqueue_style('wp-admin');
    }

    /**
     * Enqueue admin scripts
     *
     * @since 2.0.0
     */
    public function enqueue_scripts() {
        $current_screen = get_current_screen();
        
        // Only load on our plugin pages
        if (!$current_screen || strpos($current_screen->id, 'lepost') === false) {
            return;
        }

        // Simplified admin script
        wp_enqueue_script(
            $this->plugin_name . '-admin',
            LEPOST_CLIENT_PLUGIN_URL . 'assets/js/lepost-admin-simple.js',
            ['jquery'],
            $this->version,
            true
        );

        // Localize script for AJAX
        wp_localize_script(
            $this->plugin_name . '-admin',
            'lepost_admin_ajax',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('lepost_admin_nonce'),
                'strings' => [
                    'confirm_delete' => __('Are you sure you want to delete this item?', 'lepost-client'),
                    'processing' => __('Processing...', 'lepost-client'),
                    'error' => __('An error occurred. Please try again.', 'lepost-client'),
                ],
            ]
        );
    }

    /**
     * Handle admin-post.php actions
     *
     * @since 2.0.0
     */
    public function handle_admin_actions() {
        // Check if this is an admin-post request for our plugin
        if (!isset($_POST['action']) || strpos($_POST['action'], 'lepost_') !== 0) {
            return;
        }

        $action = sanitize_key($_POST['action']);
        $page = isset($_POST['page']) ? sanitize_key($_POST['page']) : '';

        // Route to appropriate page controller
        if (isset($this->page_controllers[$page])) {
            $controller = $this->page_controllers[$page];
            $action_name = str_replace('lepost_', '', $action);
            
            if (method_exists($controller, 'handle_action')) {
                $controller->handle_action($action_name);
            }
        }
    }

    /**
     * Add plugin action links
     *
     * @since 2.0.0
     * @param array $links Existing links
     * @return array Modified links
     */
    public function add_action_links($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=lepost-settings'),
            __('Settings', 'lepost-client')
        );
        
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Initialize default settings
     *
     * @since 2.0.0
     */
    public function init_default_settings() {
        // General settings
        $default_general_settings = [
            'autopost_articles' => true,
            'default_status' => 'draft',
            'default_category' => 0,
            'enable_auto_updates' => '0',
        ];
        
        if (false === get_option('lepost_client_settings')) {
            update_option('lepost_client_settings', $default_general_settings);
        }

        // Content settings
        $default_content_settings = [
            'company_info' => '',
            'writing_style' => [
                'article' => '',
            ],
        ];
        
        if (false === get_option('lepost_content_settings')) {
            update_option('lepost_content_settings', $default_content_settings);
        }
    }

    /**
     * Get plugin name
     *
     * @since 2.0.0
     * @return string Plugin name
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Get plugin version
     *
     * @since 2.0.0
     * @return string Plugin version
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Get API instance
     *
     * @since 2.0.0
     * @return Api API instance
     */
    public function get_api() {
        return $this->api;
    }
} 