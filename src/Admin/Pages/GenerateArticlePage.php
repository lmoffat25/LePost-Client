<?php
/**
 * Generate Article Page Controller
 *
 * Handles article generation with simplified confirmation pages.
 * Replaces JavaScript modals with server-side confirmation flow.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/Pages
 * @since      2.0.0
 */

namespace LePostClient\Admin\Pages;

use LePostClient\Api\Api;
use LePostClient\ContentType\Idee;
use LePostClient\ContentType\Article;

/**
 * GenerateArticlePage Class
 *
 * Manages article generation flow with clear confirmation steps.
 * No JavaScript dependencies - pure server-side processing.
 */
class GenerateArticlePage extends AbstractPage {

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
     * Constructor
     *
     * @since 2.0.0
     * @param Api $api API instance
     */
    public function __construct(Api $api) {
        parent::__construct($api, 'lepost-generate-article', __('Generate Article', 'lepost-client'));
        
        $this->idee_model = new Idee();
        $this->article_model = new Article();
    }

    /**
     * Render the generate article page
     *
     * @since 2.0.0
     */
    public function render() {
        // Check for URL-based notices
        $this->check_url_notices();

        // Check if API is connected
        if (!$this->api->is_api_key_set()) {
            $this->render_template('generate/api-required');
            return;
        }

        // Determine what to render based on parameters
        $idea_id = isset($_GET['idea']) ? absint($_GET['idea']) : 0;
        $bulk = isset($_GET['bulk']) ? true : false;
        $confirmed = isset($_POST['confirm_generation']) ? true : false;

        if ($bulk) {
            $this->handle_bulk_generation();
        } elseif ($idea_id) {
            if ($confirmed) {
                $this->process_single_generation($idea_id);
            } else {
                $this->render_single_confirmation($idea_id);
            }
        } else {
            $this->render_selection_page();
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
            case 'generate_single':
                $this->handle_generate_single();
                break;
            case 'generate_bulk':
                $this->handle_generate_bulk();
                break;
            case 'select_ideas':
                $this->handle_select_ideas();
                break;
            default:
                // No specific action
                break;
        }
    }

    /**
     * Render idea selection page
     *
     * @since 2.0.0
     */
    private function render_selection_page() {
        // Get all ideas for selection
        $ideas_result = $this->idee_model->get_all_with_filters([
            'page' => 1,
            'per_page' => 50,
            'orderby' => 'created_at',
            'order' => 'DESC',
        ]);

        $this->render_template('generate/select', [
            'ideas' => $ideas_result['idees'],
            'total_ideas' => $ideas_result['total'],
        ]);
    }

    /**
     * Render single idea generation confirmation
     *
     * @since 2.0.0
     * @param int $idea_id Idea ID
     */
    private function render_single_confirmation($idea_id) {
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

        // Check if article already exists
        $existing_article = $this->article_model->get_by_idee_id($idea_id);

        $this->render_template('generate/confirm-single', [
            'idea' => $idea,
            'existing_article' => $existing_article,
        ]);
    }

    /**
     * Handle bulk generation
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
            $this->redirect_with_notice(
                __('No ideas selected for generation.', 'lepost-client'),
                'error'
            );
        }

        // Get ideas data
        $ideas = [];
        foreach ($idea_ids as $idea_id) {
            $idea = $this->idee_model->get_by_id($idea_id);
            if ($idea) {
                $ideas[] = $idea;
            }
        }

        if (empty($ideas)) {
            $this->redirect_with_notice(
                __('No valid ideas found for generation.', 'lepost-client'),
                'error'
            );
        }

        // Check if confirmed
        if (isset($_POST['confirm_bulk_generation'])) {
            $this->process_bulk_generation($ideas);
        } else {
            $this->render_template('generate/confirm-bulk', [
                'ideas' => $ideas,
                'idea_ids' => $idea_ids,
            ]);
        }
    }

    /**
     * Process single article generation
     *
     * @since 2.0.0
     * @param int $idea_id Idea ID
     */
    private function process_single_generation($idea_id) {
        if (!$this->verify_nonce('generate_article_' . $idea_id)) {
            wp_die(__('Security check failed.', 'lepost-client'));
        }

        $idea = $this->idee_model->get_by_id($idea_id);
        
        if (!$idea) {
            $this->redirect_with_notice(
                __('Idea not found.', 'lepost-client'),
                'error'
            );
        }

        // Generate the article
        $result = $this->generate_article_from_idea($idea);

        if ($result['success']) {
            $this->redirect_with_notice(
                sprintf(
                    __('Article "%s" generated successfully!', 'lepost-client'),
                    $result['article_title']
                ),
                'success'
            );
        } else {
            $this->redirect_with_notice(
                sprintf(__('Failed to generate article: %s', 'lepost-client'), $result['message']),
                'error'
            );
        }
    }

    /**
     * Process bulk article generation
     *
     * @since 2.0.0
     * @param array $ideas Array of idea objects
     */
    private function process_bulk_generation($ideas) {
        if (!$this->verify_nonce('bulk_generate_articles')) {
            wp_die(__('Security check failed.', 'lepost-client'));
        }

        $generated_count = 0;
        $skipped_count = 0;
        $error_count = 0;
        $results = [];

        foreach ($ideas as $idea) {
            // Check if article already exists
            $existing_article = $this->article_model->get_by_idee_id($idea->id);
            
            if ($existing_article) {
                $skipped_count++;
                $results[] = [
                    'idea_title' => $idea->titre,
                    'status' => 'skipped',
                    'message' => __('Article already exists', 'lepost-client'),
                ];
                continue;
            }

            // Generate article
            $result = $this->generate_article_from_idea($idea);
            
            if ($result['success']) {
                $generated_count++;
                $results[] = [
                    'idea_title' => $idea->titre,
                    'status' => 'success',
                    'message' => __('Generated successfully', 'lepost-client'),
                ];
            } else {
                $error_count++;
                $results[] = [
                    'idea_title' => $idea->titre,
                    'status' => 'error',
                    'message' => $result['message'],
                ];
            }

            // Small delay to avoid overwhelming the API
            usleep(500000); // 0.5 seconds
        }

        // Show results
        $this->render_template('generate/bulk-results', [
            'results' => $results,
            'generated_count' => $generated_count,
            'skipped_count' => $skipped_count,
            'error_count' => $error_count,
        ]);
    }

    /**
     * Generate article from idea
     *
     * @since 2.0.0
     * @param object $idea Idea object
     * @return array Result array with success/failure info
     */
    private function generate_article_from_idea($idea) {
        // Prepare API parameters
        $params = [
            'titre' => $idea->titre,
            'description' => $idea->description,
        ];

        // Add content settings if available
        $content_settings = get_option('lepost_content_settings', []);
        if (!empty($content_settings['company_info'])) {
            $params['company_info'] = $content_settings['company_info'];
        }
        if (!empty($content_settings['writing_style']['article'])) {
            $params['writing_style'] = $content_settings['writing_style']['article'];
        }

        // Generate article via API
        $api_result = $this->api->generate_article($params);

        if (!$api_result['success']) {
            return [
                'success' => false,
                'message' => $api_result['message'],
            ];
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
            return [
                'success' => false,
                'message' => __('Failed to save generated article to database.', 'lepost-client'),
            ];
        }

        // Create WordPress post if auto-posting is enabled
        $settings = get_option('lepost_client_settings', []);
        $autopost = isset($settings['autopost_articles']) ? $settings['autopost_articles'] : false;

        $post_id = null;
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

        return [
            'success' => true,
            'article_id' => $article_id,
            'article_title' => $api_result['article']['title'],
            'post_id' => $post_id,
        ];
    }

    /**
     * Create WordPress post from generated article
     *
     * @since 2.0.0
     * @param array $article Article data from API
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
            'meta_input'   => [
                '_lepost_generated' => '1',
                '_lepost_article_id' => isset($article['id']) ? $article['id'] : '',
            ],
        ];

        // Set default category if specified
        if (isset($settings['default_category']) && $settings['default_category'] > 0) {
            $post_data['post_category'] = [$settings['default_category']];
        }

        return wp_insert_post($post_data);
    }

    /**
     * Handle generate single action
     *
     * @since 2.0.0
     */
    private function handle_generate_single() {
        $idea_id = isset($_POST['idea_id']) ? absint($_POST['idea_id']) : 0;
        
        if (!$idea_id) {
            $this->redirect_with_notice(
                __('Invalid idea ID.', 'lepost-client'),
                'error'
            );
        }

        $this->process_single_generation($idea_id);
    }

    /**
     * Handle generate bulk action
     *
     * @since 2.0.0
     */
    private function handle_generate_bulk() {
        $idea_ids = isset($_POST['idea_ids']) ? array_filter(array_map('absint', $_POST['idea_ids'])) : [];
        
        if (empty($idea_ids)) {
            $this->redirect_with_notice(
                __('No ideas selected for generation.', 'lepost-client'),
                'error'
            );
        }

        // Redirect to bulk generation with selected ideas
        $redirect_url = add_query_arg([
            'page'   => 'lepost-generate-article',
            'bulk'   => '1',
            'ideas'  => implode(',', $idea_ids),
            '_wpnonce' => wp_create_nonce('bulk_generate_articles'),
        ], admin_url('admin.php'));

        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Handle select ideas action
     *
     * @since 2.0.0
     */
    private function handle_select_ideas() {
        // This redirects to the idea selection page
        wp_redirect(admin_url('admin.php?page=lepost-generate-article'));
        exit;
    }
} 