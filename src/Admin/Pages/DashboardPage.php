<?php
/**
 * Dashboard Page Controller
 *
 * Simplified dashboard page using standard WordPress patterns.
 * Replaces the complex tab-based dashboard with a clean, focused interface.
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
 * DashboardPage Class
 *
 * Provides a clean, informative dashboard without complex JavaScript interactions.
 * Focuses on essential information and quick actions.
 */
class DashboardPage extends AbstractPage {

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
        parent::__construct($api, 'lepost-dashboard', __('LePost Dashboard', 'lepost-client'));
        
        $this->idee_model = new Idee();
        $this->article_model = new Article();
    }

    /**
     * Render the dashboard page
     *
     * @since 2.0.0
     */
    public function render() {
        // Check for URL-based notices
        $this->check_url_notices();

        // Get dashboard data
        $dashboard_data = $this->get_dashboard_data();

        // Render the dashboard template
        $this->render_template('dashboard/main', $dashboard_data);
    }

    /**
     * Handle dashboard actions
     *
     * @since 2.0.0
     * @param string $action Action to handle
     */
    protected function handle_action($action) {
        if (!$this->check_permissions()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'lepost-client'));
        }

        switch ($action) {
            case 'refresh_credits':
                $this->handle_refresh_credits();
                break;
            case 'generate_ideas':
                $this->handle_generate_ideas();
                break;
            default:
                // No action needed for dashboard
                break;
        }
    }

    /**
     * Get dashboard data
     *
     * @since 2.0.0
     * @return array Dashboard data
     */
    private function get_dashboard_data() {
        $data = [];

        // Statistics
        $data['stats'] = $this->get_statistics();

        // Recent ideas
        $data['recent_ideas'] = $this->get_recent_ideas();

        // Recent articles
        $data['recent_articles'] = $this->get_recent_articles();

        // API status and credits
        $data['api_status'] = $this->get_api_status();

        // Notifications
        $data['notifications'] = $this->get_notifications();

        return $data;
    }

    /**
     * Get statistics for dashboard cards
     *
     * @since 2.0.0
     * @return array Statistics data
     */
    private function get_statistics() {
        return [
            'ideas_count' => $this->idee_model->count_all(),
            'articles_count' => $this->article_model->count_all(),
            'posts_count' => $this->get_lepost_posts_count(),
        ];
    }

    /**
     * Get recent ideas
     *
     * @since 2.0.0
     * @return array Recent ideas
     */
    private function get_recent_ideas() {
        $result = $this->idee_model->get_all(1, 5);
        return $result['idees'];
    }

    /**
     * Get recent articles
     *
     * @since 2.0.0
     * @return array Recent articles
     */
    private function get_recent_articles() {
        return $this->article_model->get_recent(5);
    }

    /**
     * Get API status and credits
     *
     * @since 2.0.0
     * @return array API status data
     */
    private function get_api_status() {
        $status = [
            'is_connected' => $this->api->is_api_key_set(),
            'credits' => null,
            'last_check' => null,
        ];

        if ($status['is_connected']) {
            // Get cached credits or fetch fresh ones
            $credits_cache = get_option('lepost_client_credits');
            
            if ($credits_cache && isset($credits_cache['timestamp']) && 
                (time() - $credits_cache['timestamp']) < 3600) { // Cache for 1 hour
                $status['credits'] = $credits_cache['credits'];
                $status['last_check'] = $credits_cache['timestamp'];
            } else {
                // Try to fetch fresh credits (but don't block the page if it fails)
                $fresh_credits = $this->api->get_credits();
                if ($fresh_credits['success']) {
                    $status['credits'] = $fresh_credits['credits'];
                    $status['last_check'] = time();
                    
                    // Update cache
                    update_option('lepost_client_credits', [
                        'credits' => $fresh_credits['credits'],
                        'timestamp' => time(),
                    ]);
                }
            }
        }

        return $status;
    }

    /**
     * Get dashboard notifications
     *
     * @since 2.0.0
     * @return array Notifications
     */
    private function get_notifications() {
        $notifications = [];

        // API key not set
        if (!$this->api->is_api_key_set()) {
            $notifications[] = [
                'type' => 'warning',
                'message' => sprintf(
                    __('API key not configured. <a href="%s">Set it up</a> to use generation features.', 'lepost-client'),
                    admin_url('admin.php?page=lepost-settings')
                ),
            ];
        }

        // No ideas yet
        if ($this->idee_model->count_all() === 0) {
            $notifications[] = [
                'type' => 'info',
                'message' => sprintf(
                    __('No article ideas created yet. <a href="%s">Create your first idea</a> to get started.', 'lepost-client'),
                    admin_url('admin.php?page=lepost-ideas&action=add')
                ),
            ];
        }

        // Low credits warning
        $api_status = $this->get_api_status();
        if ($api_status['is_connected'] && $api_status['credits'] !== null && $api_status['credits'] < 10) {
            $notifications[] = [
                'type' => 'warning',
                'message' => sprintf(
                    __('Low API credits remaining: %d. Consider upgrading your plan.', 'lepost-client'),
                    $api_status['credits']
                ),
            ];
        }

        return $notifications;
    }

    /**
     * Get count of WordPress posts created by LePost
     *
     * @since 2.0.0
     * @return int Posts count
     */
    private function get_lepost_posts_count() {
        $query = new \WP_Query([
            'post_type' => 'post',
            'post_status' => ['publish', 'draft', 'pending', 'private'],
            'meta_query' => [
                [
                    'key' => '_lepost_generated',
                    'value' => '1',
                    'compare' => '='
                ]
            ],
            'fields' => 'ids',
            'posts_per_page' => -1,
        ]);

        return $query->found_posts;
    }

    /**
     * Handle refresh credits action
     *
     * @since 2.0.0
     */
    private function handle_refresh_credits() {
        if (!$this->verify_nonce('refresh_credits')) {
            wp_die(__('Security check failed.', 'lepost-client'));
        }

        // Clear credits cache
        delete_option('lepost_client_credits');

        $this->redirect_with_notice(
            __('Credits refreshed successfully.', 'lepost-client'),
            'success'
        );
    }

    /**
     * Handle generate ideas action (redirect to settings)
     *
     * @since 2.0.0
     */
    private function handle_generate_ideas() {
        if (!$this->verify_nonce('generate_ideas')) {
            wp_die(__('Security check failed.', 'lepost-client'));
        }

        // Redirect to settings page with generate tab
        wp_redirect(admin_url('admin.php?page=lepost-settings&tab=generate'));
        exit;
    }
} 