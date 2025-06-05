<?php
/**
 * Abstract Page Controller
 *
 * Base class for admin page controllers providing common functionality
 * and enforcing consistent patterns across all admin pages.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/Pages
 * @since      2.0.0
 */

namespace LePostClient\Admin\Pages;

use LePostClient\Api\Api;

/**
 * AbstractPage Class
 *
 * Provides common functionality for admin page controllers including
 * form handling, nonce verification, and admin notices.
 */
abstract class AbstractPage {

    /**
     * API instance
     *
     * @since 2.0.0
     * @var Api
     */
    protected $api;

    /**
     * Page slug
     *
     * @since 2.0.0
     * @var string
     */
    protected $page_slug;

    /**
     * Page title
     *
     * @since 2.0.0
     * @var string
     */
    protected $page_title;

    /**
     * Admin notices to display
     *
     * @since 2.0.0
     * @var array
     */
    protected $admin_notices = [];

    /**
     * Constructor
     *
     * @since 2.0.0
     * @param Api    $api        API instance
     * @param string $page_slug  Page slug
     * @param string $page_title Page title
     */
    public function __construct(Api $api, $page_slug, $page_title) {
        $this->api = $api;
        $this->page_slug = $page_slug;
        $this->page_title = $page_title;
    }

    /**
     * Render the page
     *
     * @since 2.0.0
     */
    abstract public function render();

    /**
     * Handle admin actions (form submissions, etc.)
     *
     * @since 2.0.0
     */
    public function handle_admin_actions() {
        // Check if this is our page
        if (!$this->is_current_page()) {
            return;
        }

        // Process any pending actions
        $this->process_actions();
    }

    /**
     * Process page-specific actions
     *
     * @since 2.0.0
     */
    protected function process_actions() {
        // Get the current action
        $action = $this->get_current_action();

        if (!$action) {
            return;
        }

        // Handle the action
        $this->handle_action($action);
    }

    /**
     * Handle a specific action
     *
     * @since 2.0.0
     * @param string $action Action to handle
     */
    abstract protected function handle_action($action);

    /**
     * Get the current action from REQUEST
     *
     * @since 2.0.0
     * @return string|false
     */
    protected function get_current_action() {
        if (isset($_REQUEST['action']) && $_REQUEST['action'] !== '-1') {
            return sanitize_key($_REQUEST['action']);
        }

        if (isset($_REQUEST['action2']) && $_REQUEST['action2'] !== '-1') {
            return sanitize_key($_REQUEST['action2']);
        }

        return false;
    }

    /**
     * Check if we're on the current page
     *
     * @since 2.0.0
     * @return bool
     */
    protected function is_current_page() {
        return isset($_GET['page']) && $_GET['page'] === $this->page_slug;
    }

    /**
     * Verify nonce for an action
     *
     * @since 2.0.0
     * @param string $action  Action name
     * @param string $context Optional context (defaults to action)
     * @return bool
     */
    protected function verify_nonce($action, $context = null) {
        if ($context === null) {
            $context = $action;
        }

        $nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : '';
        
        return wp_verify_nonce($nonce, $context);
    }

    /**
     * Check user permissions
     *
     * @since 2.0.0
     * @param string $capability Required capability (defaults to manage_options)
     * @return bool
     */
    protected function check_permissions($capability = 'manage_options') {
        return current_user_can($capability);
    }

    /**
     * Add an admin notice
     *
     * @since 2.0.0
     * @param string $message Notice message
     * @param string $type    Notice type (success, error, warning, info)
     * @param bool   $dismissible Whether the notice is dismissible
     */
    protected function add_admin_notice($message, $type = 'info', $dismissible = true) {
        $this->admin_notices[] = [
            'message'     => $message,
            'type'        => $type,
            'dismissible' => $dismissible,
        ];
    }

    /**
     * Display admin notices
     *
     * @since 2.0.0
     */
    protected function display_admin_notices() {
        foreach ($this->admin_notices as $notice) {
            $classes = ['notice', 'notice-' . $notice['type']];
            
            if ($notice['dismissible']) {
                $classes[] = 'is-dismissible';
            }

            printf(
                '<div class="%s"><p>%s</p></div>',
                esc_attr(implode(' ', $classes)),
                esc_html($notice['message'])
            );
        }
    }

    /**
     * Redirect with admin notice
     *
     * @since 2.0.0
     * @param string $message Notice message
     * @param string $type    Notice type
     * @param array  $args    Additional URL args
     */
    protected function redirect_with_notice($message, $type = 'success', $args = []) {
        $args['notice'] = urlencode($message);
        $args['notice_type'] = $type;

        $redirect_url = add_query_arg($args, admin_url('admin.php?page=' . $this->page_slug));
        
        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Check for and display URL-based notices
     *
     * @since 2.0.0
     */
    protected function check_url_notices() {
        if (isset($_GET['notice'])) {
            $message = urldecode(sanitize_text_field($_GET['notice']));
            $type = isset($_GET['notice_type']) ? sanitize_key($_GET['notice_type']) : 'info';
            
            $this->add_admin_notice($message, $type);
        }
    }

    /**
     * Sanitize and validate form data
     *
     * @since 2.0.0
     * @param array $data Raw form data
     * @param array $rules Validation rules
     * @return array|WP_Error Sanitized data or WP_Error on validation failure
     */
    protected function validate_form_data($data, $rules) {
        $sanitized = [];
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = isset($data[$field]) ? $data[$field] : '';
            
            // Apply sanitization
            if (isset($rule['sanitize'])) {
                $value = $this->apply_sanitization($value, $rule['sanitize']);
            }

            // Apply validation
            if (isset($rule['validate'])) {
                $validation_result = $this->apply_validation($value, $rule['validate'], $field);
                if (is_wp_error($validation_result)) {
                    $errors[] = $validation_result->get_error_message();
                    continue;
                }
            }

            $sanitized[$field] = $value;
        }

        if (!empty($errors)) {
            return new \WP_Error('validation_failed', implode(' ', $errors));
        }

        return $sanitized;
    }

    /**
     * Apply sanitization to a value
     *
     * @since 2.0.0
     * @param mixed  $value Value to sanitize
     * @param string $type  Sanitization type
     * @return mixed Sanitized value
     */
    private function apply_sanitization($value, $type) {
        switch ($type) {
            case 'text':
                return sanitize_text_field($value);
            case 'textarea':
                return sanitize_textarea_field($value);
            case 'email':
                return sanitize_email($value);
            case 'url':
                return esc_url_raw($value);
            case 'int':
                return intval($value);
            case 'float':
                return floatval($value);
            case 'array':
                return is_array($value) ? array_map('sanitize_text_field', $value) : [];
            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * Apply validation to a value
     *
     * @since 2.0.0
     * @param mixed  $value Value to validate
     * @param array  $rules Validation rules
     * @param string $field Field name for error messages
     * @return true|WP_Error True on success, WP_Error on failure
     */
    private function apply_validation($value, $rules, $field) {
        foreach ($rules as $rule => $params) {
            switch ($rule) {
                case 'required':
                    if (empty($value)) {
                        return new \WP_Error(
                            'required_field',
                            sprintf(__('The %s field is required.', 'lepost-client'), $field)
                        );
                    }
                    break;

                case 'min_length':
                    if (strlen($value) < $params) {
                        return new \WP_Error(
                            'min_length',
                            sprintf(
                                __('The %s field must be at least %d characters long.', 'lepost-client'),
                                $field,
                                $params
                            )
                        );
                    }
                    break;

                case 'max_length':
                    if (strlen($value) > $params) {
                        return new \WP_Error(
                            'max_length',
                            sprintf(
                                __('The %s field must not exceed %d characters.', 'lepost-client'),
                                $field,
                                $params
                            )
                        );
                    }
                    break;

                case 'email':
                    if (!is_email($value)) {
                        return new \WP_Error(
                            'invalid_email',
                            sprintf(__('The %s field must be a valid email address.', 'lepost-client'), $field)
                        );
                    }
                    break;

                case 'url':
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        return new \WP_Error(
                            'invalid_url',
                            sprintf(__('The %s field must be a valid URL.', 'lepost-client'), $field)
                        );
                    }
                    break;
            }
        }

        return true;
    }

    /**
     * Render a template file
     *
     * @since 2.0.0
     * @param string $template Template name (without .php extension)
     * @param array  $vars     Variables to extract into template scope
     */
    protected function render_template($template, $vars = []) {
        $template_path = LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/templates/' . $template . '.php';

        if (!file_exists($template_path)) {
            wp_die(sprintf(__('Template not found: %s', 'lepost-client'), $template));
        }

        // Extract variables into template scope
        if (!empty($vars)) {
            extract($vars, EXTR_SKIP);
        }

        // Make common variables available
        $api = $this->api;
        $page_title = $this->page_title;
        $page_slug = $this->page_slug;

        include $template_path;
    }

    /**
     * Get the page slug
     *
     * @since 2.0.0
     * @return string
     */
    public function get_page_slug() {
        return $this->page_slug;
    }

    /**
     * Get the page title
     *
     * @since 2.0.0
     * @return string
     */
    public function get_page_title() {
        return $this->page_title;
    }
} 