<?php
/**
 * Settings Page Controller
 *
 * Simplified settings page using standard WordPress settings patterns.
 * Replaces the complex tab-based settings with clean, organized forms.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/Pages
 * @since      2.0.0
 */

namespace LePostClient\Admin\Pages;

use LePostClient\Api\Api;
use LePostClient\ContentType\Idee;

/**
 * SettingsPage Class
 *
 * Manages all plugin settings with proper WordPress settings API integration.
 * Focuses on essential settings without complex JavaScript interactions.
 */
class SettingsPage extends AbstractPage {

    /**
     * Idee model instance
     *
     * @since 2.0.0
     * @var Idee
     */
    private $idee_model;

    /**
     * Constructor
     *
     * @since 2.0.0
     * @param Api $api API instance
     */
    public function __construct(Api $api) {
        parent::__construct($api, 'lepost-settings', __('LePost Settings', 'lepost-client'));
        
        $this->idee_model = new Idee();
    }

    /**
     * Initialize settings (register settings fields)
     *
     * @since 2.0.0
     */
    public function init_settings() {
        // Register API settings
        register_setting(
            'lepost_api_settings_group',
            'lepost_client_api_key',
            [
                'sanitize_callback' => 'sanitize_text_field',
                'description' => __('LePost API Key', 'lepost-client'),
            ]
        );

        // Register general settings
        register_setting(
            'lepost_general_settings_group',
            'lepost_client_settings',
            [
                'sanitize_callback' => [$this, 'sanitize_general_settings'],
                'default' => [
                    'autopost_articles' => true,
                    'default_status' => 'draft',
                    'default_category' => 0,
                    'enable_auto_updates' => '0',
                ],
            ]
        );

        // Register content settings
        register_setting(
            'lepost_content_settings_group',
            'lepost_content_settings',
            [
                'sanitize_callback' => [$this, 'sanitize_content_settings'],
                'default' => [
                    'company_info' => '',
                    'writing_style' => [
                        'article' => '',
                    ],
                ],
            ]
        );
    }

    /**
     * Render the settings page
     *
     * @since 2.0.0
     */
    public function render() {
        // Check for URL-based notices
        $this->check_url_notices();

        // Get current tab
        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'api';

        // Get settings data
        $settings_data = $this->get_settings_data();

        // Render the appropriate tab
        switch ($current_tab) {
            case 'general':
                $this->render_template('settings/general', $settings_data);
                break;
            case 'content':
                $this->render_template('settings/content', $settings_data);
                break;
            case 'generate':
                $this->render_template('settings/generate', $settings_data);
                break;
            case 'api':
            default:
                $this->render_template('settings/api', $settings_data);
                break;
        }
    }

    /**
     * Handle settings actions
     *
     * @since 2.0.0
     * @param string $action Action to handle
     */
    protected function handle_action($action) {
        if (!$this->check_permissions()) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'lepost-client'));
        }

        switch ($action) {
            case 'test_api':
                $this->handle_test_api();
                break;
            case 'generate_ideas':
                $this->handle_generate_ideas();
                break;
            case 'test_article_generation':
                $this->handle_test_article_generation();
                break;
            default:
                // Standard WordPress settings handling
                break;
        }
    }

    /**
     * Get settings data for templates
     *
     * @since 2.0.0
     * @return array Settings data
     */
    private function get_settings_data() {
        return [
            'api_key' => get_option('lepost_client_api_key', ''),
            'general_settings' => get_option('lepost_client_settings', [
                'autopost_articles' => true,
                'default_status' => 'draft',
                'default_category' => 0,
                'enable_auto_updates' => '0',
            ]),
            'content_settings' => get_option('lepost_content_settings', [
                'company_info' => '',
                'writing_style' => [
                    'article' => '',
                ],
            ]),
            'api_status' => $this->get_api_status(),
            'current_tab' => isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'api',
        ];
    }

    /**
     * Get API status information
     *
     * @since 2.0.0
     * @return array API status
     */
    private function get_api_status() {
        $status = [
            'is_connected' => $this->api->is_api_key_set(),
            'connection_test' => null,
            'credits' => null,
        ];

        if ($status['is_connected']) {
            // Get cached connection test result
            $test_cache = get_transient('lepost_api_test_result');
            if ($test_cache) {
                $status['connection_test'] = $test_cache;
            }

            // Get credits info
            $credits_cache = get_option('lepost_client_credits');
            if ($credits_cache && isset($credits_cache['credits'])) {
                $status['credits'] = $credits_cache['credits'];
            }
        }

        return $status;
    }

    /**
     * Handle API test action
     *
     * @since 2.0.0
     */
    private function handle_test_api() {
        if (!$this->verify_nonce('test_api')) {
            wp_die(__('Security check failed.', 'lepost-client'));
        }

        $test_result = $this->api->test_connection();
        
        // Cache the result for 5 minutes
        set_transient('lepost_api_test_result', $test_result, 300);

        if ($test_result['success']) {
            $this->redirect_with_notice(
                __('API connection test successful!', 'lepost-client'),
                'success',
                ['tab' => 'api']
            );
        } else {
            $this->redirect_with_notice(
                sprintf(__('API connection failed: %s', 'lepost-client'), $test_result['message']),
                'error',
                ['tab' => 'api']
            );
        }
    }

    /**
     * Handle generate ideas action
     *
     * @since 2.0.0
     */
    private function handle_generate_ideas() {
        if (!$this->verify_nonce('generate_ideas')) {
            wp_die(__('Security check failed.', 'lepost-client'));
        }

        // Validate form data
        $validation_rules = [
            'topic' => [
                'sanitize' => 'text',
                'validate' => ['required' => true, 'max_length' => 255],
            ],
            'num_ideas' => [
                'sanitize' => 'int',
                'validate' => ['required' => true],
            ],
            'context' => [
                'sanitize' => 'textarea',
                'validate' => ['max_length' => 1000],
            ],
        ];

        $data = $this->validate_form_data($_POST, $validation_rules);
        
        if (is_wp_error($data)) {
            $this->redirect_with_notice($data->get_error_message(), 'error', ['tab' => 'generate']);
        }

        // Validate number of ideas
        if ($data['num_ideas'] < 1 || $data['num_ideas'] > 10) {
            $this->redirect_with_notice(
                __('Number of ideas must be between 1 and 10.', 'lepost-client'),
                'error',
                ['tab' => 'generate']
            );
        }

        // Generate ideas via API
        $api_result = $this->api->generate_ideas([
            'topic' => $data['topic'],
            'num_ideas' => $data['num_ideas'],
            'context' => $data['context'],
        ]);

        if (!$api_result['success']) {
            $this->redirect_with_notice(
                sprintf(__('Failed to generate ideas: %s', 'lepost-client'), $api_result['message']),
                'error',
                ['tab' => 'generate']
            );
        }

        // Save generated ideas
        $saved_count = 0;
        $skipped_count = 0;

        foreach ($api_result['ideas'] as $idea_data) {
            $existing_idea = \LePostClient\ContentType\Idee::existsByTitle($idea_data['title']);
            
            if ($existing_idea) {
                $skipped_count++;
                continue;
            }

            $idea_to_save = [
                'titre' => sanitize_text_field($idea_data['title']),
                'description' => sanitize_textarea_field($idea_data['explanation']),
            ];

            if ($this->idee_model->save($idea_to_save)) {
                $saved_count++;
            } else {
                $skipped_count++;
            }
        }

        // Prepare success message
        if ($saved_count > 0) {
            $message = sprintf(
                _n(
                    '%d idea generated and saved successfully.',
                    '%d ideas generated and saved successfully.',
                    $saved_count,
                    'lepost-client'
                ),
                $saved_count
            );

            if ($skipped_count > 0) {
                $message .= ' ' . sprintf(
                    _n(
                        '%d idea was skipped (already exists).',
                        '%d ideas were skipped (already exist).',
                        $skipped_count,
                        'lepost-client'
                    ),
                    $skipped_count
                );
            }

            $this->redirect_with_notice($message, 'success', ['tab' => 'generate']);
        } else {
            $this->redirect_with_notice(
                __('No new ideas were generated. All ideas already exist.', 'lepost-client'),
                'warning',
                ['tab' => 'generate']
            );
        }
    }

    /**
     * Handle test article generation
     *
     * @since 2.0.0
     */
    private function handle_test_article_generation() {
        if (!$this->verify_nonce('test_article_generation')) {
            wp_die(__('Security check failed.', 'lepost-client'));
        }

        // This is an AJAX action, return JSON response
        $validation_rules = [
            'subject' => [
                'sanitize' => 'text',
                'validate' => ['required' => true, 'max_length' => 255],
            ],
            'explanation' => [
                'sanitize' => 'textarea',
                'validate' => ['max_length' => 1000],
            ],
        ];

        $data = $this->validate_form_data($_POST, $validation_rules);
        
        if (is_wp_error($data)) {
            wp_send_json_error(['message' => $data->get_error_message()]);
        }

        // Generate test article
        $api_result = $this->api->generate_article([
            'titre' => $data['subject'],
            'description' => $data['explanation'],
        ]);

        if ($api_result['success']) {
            wp_send_json_success([
                'article' => $api_result['article'],
                'message' => __('Test article generated successfully!', 'lepost-client'),
            ]);
        } else {
            wp_send_json_error([
                'message' => sprintf(__('Failed to generate test article: %s', 'lepost-client'), $api_result['message']),
            ]);
        }
    }

    /**
     * Sanitize general settings
     *
     * @since 2.0.0
     * @param array $input Input data
     * @return array Sanitized data
     */
    public function sanitize_general_settings($input) {
        $sanitized = [];

        // Auto-post articles (always true for simplicity)
        $sanitized['autopost_articles'] = true;

        // Default status
        if (isset($input['default_status'])) {
            $sanitized['default_status'] = in_array($input['default_status'], ['draft', 'publish', 'pending', 'private'])
                ? $input['default_status']
                : 'draft';
        } else {
            $sanitized['default_status'] = 'draft';
        }

        // Default category
        if (isset($input['default_category'])) {
            $sanitized['default_category'] = (int) $input['default_category'];
        } else {
            $sanitized['default_category'] = 0;
        }

        // Auto-updates
        if (isset($input['enable_auto_updates'])) {
            $sanitized['enable_auto_updates'] = $input['enable_auto_updates'] ? '1' : '0';
        } else {
            $sanitized['enable_auto_updates'] = '0';
        }

        return $sanitized;
    }

    /**
     * Sanitize content settings
     *
     * @since 2.0.0
     * @param array $input Input data
     * @return array Sanitized data
     */
    public function sanitize_content_settings($input) {
        $sanitized = [];

        // Company info
        if (isset($input['company_info'])) {
            $sanitized['company_info'] = sanitize_textarea_field($input['company_info']);
        }

        // Writing style
        if (isset($input['writing_style']) && is_array($input['writing_style'])) {
            foreach ($input['writing_style'] as $key => $value) {
                $sanitized['writing_style'][$key] = sanitize_textarea_field($value);
            }
        }

        return $sanitized;
    }
} 