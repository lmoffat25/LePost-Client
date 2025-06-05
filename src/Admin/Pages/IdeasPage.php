<?php
/**
 * Ideas Page Controller
 *
 * Handles the ideas management page using standard WordPress patterns.
 * Replaces the complex JavaScript-heavy approach with simple forms.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/Pages
 * @since      2.0.0
 */

namespace LePostClient\Admin\Pages;

use LePostClient\Api\Api;
use LePostClient\ContentType\Idee;
use LePostClient\ContentType\Article;
use LePostClient\Admin\Tables\IdeasListTable;

/**
 * IdeasPage Class
 *
 * Manages the ideas interface with standard WordPress list table,
 * forms, and bulk operations without relying on JavaScript.
 */
class IdeasPage extends AbstractPage {

    /**
     * Idee model instance
     *
     * @since 2.0.0
     * @var Idee
     */
    private $idee_model;

    /**
     * Article model instance
     *
     * @since 2.0.0
     * @var Article
     */
    private $article_model;

    /**
     * List table instance
     *
     * @since 2.0.0
     * @var IdeasListTable
     */
    private $list_table;

    /**
     * Constructor
     *
     * @since 2.0.0
     * @param Api $api API instance
     */
    public function __construct(Api $api) {
        parent::__construct($api, 'lepost-ideas', __('Ideas Manager', 'lepost-client'));
        
        $this->idee_model = new Idee();
        $this->article_model = new Article();
        $this->list_table = null;
    }

    /**
     * Get list table instance (lazy loading)
     *
     * @since 2.0.0
     * @return IdeasListTable
     */
    private function get_list_table() {
        if ($this->list_table === null) {
            // Make sure we're in admin context and admin functions are loaded
            if (!function_exists('convert_to_screen')) {
                require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
                require_once ABSPATH . 'wp-admin/includes/screen.php';
            }
            
            // Load WP_List_Table BEFORE loading our class file since we extend it
            if (!class_exists('WP_List_Table')) {
                require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
            }
            
            // Load the IdeasListTable class file
            if (!class_exists('LePostClient\Admin\Tables\IdeasListTable')) {
                require_once LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/Tables/IdeasListTable.php';
            }
            
            $this->list_table = new IdeasListTable();
        }
        
        return $this->list_table;
    }

    /**
     * Render the ideas page
     *
     * @since 2.0.0
     */
    public function render() {
        // Check for URL-based notices
        $this->check_url_notices();

        // Determine what to render based on current action
        $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : 'list';
        $idea_id = isset($_GET['idea']) ? absint($_GET['idea']) : 0;

        switch ($action) {
            case 'add':
                $this->render_add_idea_form();
                break;
            case 'edit':
                $this->render_edit_idea_form($idea_id);
                break;
            default:
                $this->render_ideas_list();
                break;
        }
    }

    /**
     * Handle page actions
     *
     * @since 2.0.0
     * @param string $action Action to handle
     */
    protected function handle_action($action) {
        if (!$this->check_permissions()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'lepost-client'));
        }

        switch ($action) {
            case 'add_idea':
                $this->handle_add_idea();
                break;
            case 'edit_idea':
                $this->handle_edit_idea();
                break;
            case 'delete':
                $this->handle_delete_idea();
                break;
            case 'import_csv':
                $this->handle_import_csv();
                break;
            default:
                // Handle bulk actions
                $this->get_list_table()->process_bulk_action();
                break;
        }
    }

    /**
     * Render the ideas list
     *
     * @since 2.0.0
     */
    private function render_ideas_list() {
        $this->render_template('ideas/list', [
            'list_table' => $this->get_list_table(),
        ]);
    }

    /**
     * Render add idea form
     *
     * @since 2.0.0
     */
    private function render_add_idea_form() {
        $this->render_template('ideas/add', [
            'api' => $this->api,
        ]);
    }

    /**
     * Render edit idea form
     *
     * @since 2.0.0
     * @param int $idea_id Idea ID to edit
     */
    private function render_edit_idea_form($idea_id) {
        if (!$idea_id) {
            $this->redirect_with_notice(
                __('Invalid idea ID.', 'lepost-client'),
                'error'
            );
        }

        $idea = $this->idee_model->get_by_id($idea_id);
        
        if (!$idea) {
            $this->redirect_with_notice(
                __('Idea not found.', 'lepost-client'),
                'error'
            );
        }

        $this->render_template('ideas/edit', [
            'idea' => $idea,
            'api'  => $this->api,
        ]);
    }

    /**
     * Render generate article page
     *
     * @since 2.0.0
     */
    public function render_generate_article_page() {
        $idea_id = isset($_GET['idea']) ? absint($_GET['idea']) : 0;
        $bulk = isset($_GET['bulk']) ? true : false;

        if ($bulk) {
            $this->handle_bulk_generation();
        } else {
            $this->handle_single_generation($idea_id);
        }
    }

    /**
     * Handle adding a new idea
     *
     * @since 2.0.0
     */
    private function handle_add_idea() {
        if (!$this->verify_nonce('add_idea')) {
            wp_die(__('Security check failed.', 'lepost-client'));
        }

        // Validate form data
        $validation_rules = [
            'title' => [
                'sanitize' => 'text',
                'validate' => ['required' => true, 'max_length' => 255],
            ],
            'description' => [
                'sanitize' => 'textarea',
                'validate' => ['max_length' => 1000],
            ],
        ];

        $data = $this->validate_form_data($_POST, $validation_rules);
        
        if (is_wp_error($data)) {
            $this->redirect_with_notice($data->get_error_message(), 'error');
        }

        // Save the idea
        $idea_data = [
            'titre' => $data['title'],
            'description' => $data['description'],
        ];

        $result = $this->idee_model->save($idea_data);

        if ($result) {
            $this->redirect_with_notice(
                __('Idea created successfully.', 'lepost-client'),
                'success'
            );
        } else {
            $this->redirect_with_notice(
                __('Failed to create idea. Please try again.', 'lepost-client'),
                'error'
            );
        }
    }

    /**
     * Handle editing an idea
     *
     * @since 2.0.0
     */
    private function handle_edit_idea() {
        $idea_id = isset($_POST['idea_id']) ? absint($_POST['idea_id']) : 0;

        if (!$this->verify_nonce('edit_idea_' . $idea_id)) {
            wp_die(__('Security check failed.', 'lepost-client'));
        }

        if (!$idea_id) {
            $this->redirect_with_notice(__('Invalid idea ID.', 'lepost-client'), 'error');
        }

        // Validate form data
        $validation_rules = [
            'title' => [
                'sanitize' => 'text',
                'validate' => ['required' => true, 'max_length' => 255],
            ],
            'description' => [
                'sanitize' => 'textarea',
                'validate' => ['max_length' => 1000],
            ],
        ];

        $data = $this->validate_form_data($_POST, $validation_rules);
        
        if (is_wp_error($data)) {
            $this->redirect_with_notice($data->get_error_message(), 'error');
        }

        // Update the idea
        $idea_data = [
            'id' => $idea_id,
            'titre' => $data['title'],
            'description' => $data['description'],
        ];

        $result = $this->idee_model->update($idea_data);

        if ($result) {
            $this->redirect_with_notice(
                __('Idea updated successfully.', 'lepost-client'),
                'success'
            );
        } else {
            $this->redirect_with_notice(
                __('Failed to update idea. Please try again.', 'lepost-client'),
                'error'
            );
        }
    }

    /**
     * Handle deleting an idea
     *
     * @since 2.0.0
     */
    private function handle_delete_idea() {
        $idea_id = isset($_GET['idea']) ? absint($_GET['idea']) : 0;

        if (!$this->verify_nonce('delete_idea_' . $idea_id)) {
            wp_die(__('Security check failed.', 'lepost-client'));
        }

        if (!$idea_id) {
            $this->redirect_with_notice(__('Invalid idea ID.', 'lepost-client'), 'error');
        }

        $result = $this->idee_model->delete($idea_id);

        if ($result) {
            $this->redirect_with_notice(
                __('Idea deleted successfully.', 'lepost-client'),
                'success'
            );
        } else {
            $this->redirect_with_notice(
                __('Failed to delete idea. Please try again.', 'lepost-client'),
                'error'
            );
        }
    }

    /**
     * Handle CSV import
     *
     * @since 2.0.0
     */
    private function handle_import_csv() {
        if (!$this->verify_nonce('import_csv')) {
            wp_die(__('Security check failed.', 'lepost-client'));
        }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->redirect_with_notice(
                __('Please select a valid CSV file.', 'lepost-client'),
                'error'
            );
        }

        $file_path = $_FILES['csv_file']['tmp_name'];
        $imported_count = $this->import_ideas_from_csv($file_path);

        if ($imported_count > 0) {
            $this->redirect_with_notice(
                sprintf(
                    _n(
                        '%d idea imported successfully.',
                        '%d ideas imported successfully.',
                        $imported_count,
                        'lepost-client'
                    ),
                    $imported_count
                ),
                'success'
            );
        } else {
            $this->redirect_with_notice(
                __('No ideas were imported. Please check your CSV file format.', 'lepost-client'),
                'error'
            );
        }
    }

    /**
     * Handle single article generation
     *
     * @since 2.0.0
     * @param int $idea_id Idea ID
     */
    private function handle_single_generation($idea_id) {
        if (!$this->verify_nonce('generate_article_' . $idea_id)) {
            wp_die(__('Security check failed.', 'lepost-client'));
        }

        if (!$idea_id) {
            $this->redirect_with_notice(__('Invalid idea ID.', 'lepost-client'), 'error');
        }

        $idea = $this->idee_model->get_by_id($idea_id);
        
        if (!$idea) {
            $this->redirect_with_notice(__('Idea not found.', 'lepost-client'), 'error');
        }

        // Check if user confirmed generation
        if (isset($_POST['confirm_generation'])) {
            $this->process_article_generation($idea);
        } else {
            // Show confirmation page
            $this->render_template('ideas/generate-confirmation', [
                'idea' => $idea,
            ]);
        }
    }

    /**
     * Handle bulk article generation
     *
     * @since 2.0.0
     */
    private function handle_bulk_generation() {
        $ideas_param = isset($_GET['ideas']) ? sanitize_text_field($_GET['ideas']) : '';
        $idea_ids = array_filter(array_map('absint', explode(',', $ideas_param)));

        if (!$this->verify_nonce('bulk_generate_articles')) {
            wp_die(__('Security check failed.', 'lepost-client'));
        }

        if (empty($idea_ids)) {
            $this->redirect_with_notice(__('No ideas selected.', 'lepost-client'), 'error');
        }

        // Show bulk generation confirmation/progress page
        $this->render_template('ideas/bulk-generate', [
            'idea_ids' => $idea_ids,
        ]);
    }

    /**
     * Process article generation from idea
     *
     * @since 2.0.0
     * @param object $idea Idea object
     */
    private function process_article_generation($idea) {
        // Check if article already exists for this idea
        $existing_article = $this->article_model->get_by_idee_id($idea->id);
        
        if ($existing_article) {
            $this->redirect_with_notice(
                __('An article already exists for this idea.', 'lepost-client'),
                'warning'
            );
        }

        // Prepare API parameters
        $params = [
            'titre' => $idea->titre,
            'description' => $idea->description,
        ];

        // Generate article via API
        $api_result = $this->api->generate_article($params);

        if (!$api_result['success']) {
            $this->redirect_with_notice(
                sprintf(__('Failed to generate article: %s', 'lepost-client'), $api_result['message']),
                'error'
            );
        }

        // Save the generated article
        $article_data = [
            'idee_id' => $idea->id,
            'titre' => $api_result['article']['title'],
            'contenu' => $api_result['article']['content'],
            'statut' => 'generated',
        ];

        $article_id = $this->article_model->save($article_data);

        if (!$article_id) {
            $this->redirect_with_notice(
                __('Failed to save generated article.', 'lepost-client'),
                'error'
            );
        }

        // Optionally create WordPress post
        $settings = get_option('lepost_client_settings', []);
        $autopost = isset($settings['autopost_articles']) ? $settings['autopost_articles'] : false;

        if ($autopost) {
            $post_id = $this->create_wordpress_post($api_result['article'], $settings);
            
            if ($post_id) {
                // Update article with post ID
                $this->article_model->update([
                    'id' => $article_id,
                    'post_id' => $post_id,
                ]);
            }
        }

        $this->redirect_with_notice(
            __('Article generated successfully!', 'lepost-client'),
            'success'
        );
    }

    /**
     * Create WordPress post from generated article
     *
     * @since 2.0.0
     * @param array $article Article data
     * @param array $settings Plugin settings
     * @return int|false Post ID on success, false on failure
     */
    private function create_wordpress_post($article, $settings) {
        $post_data = [
            'post_title'   => $article['title'],
            'post_content' => $article['content'],
            'post_status'  => isset($settings['default_status']) ? $settings['default_status'] : 'draft',
            'post_author'  => get_current_user_id(),
            'post_type'    => 'post',
        ];

        // Set default category if specified
        if (isset($settings['default_category']) && $settings['default_category'] > 0) {
            $post_data['post_category'] = [$settings['default_category']];
        }

        return wp_insert_post($post_data);
    }

    /**
     * Import ideas from CSV file
     *
     * @since 2.0.0
     * @param string $file_path Path to CSV file
     * @return int Number of imported ideas
     */
    private function import_ideas_from_csv($file_path) {
        $imported_count = 0;
        
        if (($handle = fopen($file_path, 'r')) !== false) {
            // Skip header row
            fgetcsv($handle);
            
            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) >= 2 && !empty(trim($data[0]))) {
                    $idea_data = [
                        'titre' => sanitize_text_field(trim($data[0])),
                        'description' => sanitize_textarea_field(trim($data[1])),
                    ];
                    
                    if ($this->idee_model->save($idea_data)) {
                        $imported_count++;
                    }
                }
            }
            
            fclose($handle);
        }
        
        return $imported_count;
    }
} 