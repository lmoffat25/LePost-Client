<?php
/**
 * Ideas List Table
 *
 * Standard WordPress list table implementation for managing ideas.
 * Provides built-in pagination, sorting, bulk actions without JavaScript.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/Tables
 * @since      2.0.0
 */

namespace LePostClient\Admin\Tables;

use LePostClient\ContentType\Idee;

/**
 * IdeasListTable Class
 *
 * Extends WP_List_Table to provide a native WordPress experience
 * for managing ideas with pagination, sorting, and bulk actions.
 */
class IdeasListTable extends \WP_List_Table {

    /**
     * Idee model instance
     *
     * @since 2.0.0
     * @var Idee
     */
    private $idee_model;

    /**
     * Total items count
     *
     * @since 2.0.0
     * @var int
     */
    private $total_items = 0;

    /**
     * Constructor
     *
     * @since 2.0.0
     */
    public function __construct() {
        // Ensure we're in admin context and all required functions are loaded
        if (!is_admin()) {
            wp_die(__('This feature is only available in the WordPress admin area.', 'lepost-client'));
        }

        // Make sure all required WordPress admin functions are loaded
        if (!function_exists('convert_to_screen')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
            require_once ABSPATH . 'wp-admin/includes/screen.php';
        }

        if (!class_exists('WP_List_Table')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
        }

        parent::__construct([
            'singular' => 'idea',
            'plural'   => 'ideas',
            'ajax'     => false, // No AJAX - we want standard WordPress behavior
        ]);

        $this->idee_model = new Idee();
    }

    /**
     * Get list of columns
     *
     * @since 2.0.0
     * @return array
     */
    public function get_columns() {
        return [
            'cb'          => '<input type="checkbox" />',
            'title'       => __('Title', 'lepost-client'),
            'description' => __('Description', 'lepost-client'),
            'created_at'  => __('Created', 'lepost-client'),
            'actions'     => __('Actions', 'lepost-client'),
        ];
    }

    /**
     * Get sortable columns
     *
     * @since 2.0.0
     * @return array
     */
    public function get_sortable_columns() {
        return [
            'title'      => ['title', false],
            'created_at' => ['created_at', true], // Default sort
        ];
    }

    /**
     * Get bulk actions
     *
     * @since 2.0.0
     * @return array
     */
    public function get_bulk_actions() {
        return [
            'delete'   => __('Delete', 'lepost-client'),
            'generate' => __('Generate Articles', 'lepost-client'),
            'export'   => __('Export to CSV', 'lepost-client'),
        ];
    }

    /**
     * Prepare table items
     *
     * @since 2.0.0
     */
    public function prepare_items() {
        // Set up pagination
        $per_page = $this->get_items_per_page('ideas_per_page', 20);
        $current_page = $this->get_pagenum();

        // Get sorting parameters
        $orderby = isset($_REQUEST['orderby']) ? sanitize_sql_orderby($_REQUEST['orderby']) : 'created_at';
        $order = isset($_REQUEST['order']) && $_REQUEST['order'] === 'asc' ? 'ASC' : 'DESC';

        // Get search query
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

        // Fetch data
        $result = $this->idee_model->get_all_with_filters([
            'page'     => $current_page,
            'per_page' => $per_page,
            'orderby'  => $orderby,
            'order'    => $order,
            'search'   => $search,
        ]);

        $this->items = $result['idees'];
        $this->total_items = $result['total'];

        // Set up pagination info
        $this->set_pagination_args([
            'total_items' => $this->total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($this->total_items / $per_page),
        ]);

        // Set up column headers
        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns(),
        ];
    }

    /**
     * Render checkbox column
     *
     * @since 2.0.0
     * @param object $item Current item
     * @return string
     */
    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="idea[]" value="%s" />',
            $item->id
        );
    }

    /**
     * Render title column
     *
     * @since 2.0.0
     * @param object $item Current item
     * @return string
     */
    public function column_title($item) {
        $page = $_REQUEST['page'];
        
        $actions = [
            'edit' => sprintf(
                '<a href="?page=%s&action=%s&idea=%s&_wpnonce=%s">%s</a>',
                esc_attr($page),
                'edit',
                absint($item->id),
                wp_create_nonce('edit_idea_' . $item->id),
                __('Edit', 'lepost-client')
            ),
            'delete' => sprintf(
                '<a href="?page=%s&action=%s&idea=%s&_wpnonce=%s" onclick="return confirm(\'%s\')">%s</a>',
                esc_attr($page),
                'delete',
                absint($item->id),
                wp_create_nonce('delete_idea_' . $item->id),
                esc_js(__('Are you sure you want to delete this idea?', 'lepost-client')),
                __('Delete', 'lepost-client')
            ),
            'generate' => sprintf(
                '<a href="?page=lepost-generate-article&idea=%s&_wpnonce=%s" class="button-link">%s</a>',
                absint($item->id),
                wp_create_nonce('generate_article_' . $item->id),
                __('Generate Article', 'lepost-client')
            ),
        ];

        return sprintf(
            '<strong><a href="?page=%s&action=%s&idea=%s&_wpnonce=%s">%s</a></strong>%s',
            esc_attr($page),
            'edit',
            absint($item->id),
            wp_create_nonce('edit_idea_' . $item->id),
            esc_html($item->titre),
            $this->row_actions($actions)
        );
    }

    /**
     * Render description column
     *
     * @since 2.0.0
     * @param object $item Current item
     * @return string
     */
    public function column_description($item) {
        $description = esc_html($item->description);
        
        // Truncate long descriptions
        if (strlen($description) > 100) {
            $description = substr($description, 0, 100) . '...';
        }

        return $description;
    }

    /**
     * Render created_at column
     *
     * @since 2.0.0
     * @param object $item Current item
     * @return string
     */
    public function column_created_at($item) {
        return date_i18n(
            get_option('date_format') . ' ' . get_option('time_format'),
            strtotime($item->created_at)
        );
    }

    /**
     * Render actions column
     *
     * @since 2.0.0
     * @param object $item Current item
     * @return string
     */
    public function column_actions($item) {
        $actions = [];

        $actions[] = sprintf(
            '<a href="?page=lepost-generate-article&idea=%s&_wpnonce=%s" class="button button-primary button-small">%s</a>',
            absint($item->id),
            wp_create_nonce('generate_article_' . $item->id),
            __('Generate', 'lepost-client')
        );

        $actions[] = sprintf(
            '<a href="?page=%s&action=%s&idea=%s&_wpnonce=%s" class="button button-small">%s</a>',
            esc_attr($_REQUEST['page']),
            'edit',
            absint($item->id),
            wp_create_nonce('edit_idea_' . $item->id),
            __('Edit', 'lepost-client')
        );

        return implode(' ', $actions);
    }

    /**
     * Handle default column rendering
     *
     * @since 2.0.0
     * @param object $item        Current item
     * @param string $column_name Column name
     * @return string
     */
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'title':
            case 'description':
            case 'created_at':
            case 'actions':
                return $this->{"column_$column_name"}($item);
            default:
                return print_r($item, true); // For debugging
        }
    }

    /**
     * Process bulk actions
     *
     * @since 2.0.0
     */
    public function process_bulk_action() {
        $action = $this->current_action();

        if (!$action) {
            return;
        }

        // Get selected ideas
        $ideas = isset($_REQUEST['idea']) ? array_map('absint', $_REQUEST['idea']) : [];

        if (empty($ideas)) {
            return;
        }

        // Verify nonce
        if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'])) {
            wp_die(__('Security check failed.', 'lepost-client'));
        }

        switch ($action) {
            case 'delete':
                $this->bulk_delete($ideas);
                break;
            case 'generate':
                $this->bulk_generate($ideas);
                break;
            case 'export':
                $this->bulk_export($ideas);
                break;
        }
    }

    /**
     * Bulk delete ideas
     *
     * @since 2.0.0
     * @param array $ideas Array of idea IDs
     */
    private function bulk_delete($ideas) {
        $deleted_count = 0;

        foreach ($ideas as $idea_id) {
            if ($this->idee_model->delete($idea_id)) {
                $deleted_count++;
            }
        }

        if ($deleted_count > 0) {
            $message = sprintf(
                _n(
                    '%d idea deleted successfully.',
                    '%d ideas deleted successfully.',
                    $deleted_count,
                    'lepost-client'
                ),
                $deleted_count
            );
            
            $this->add_admin_notice($message, 'success');
        }
    }

    /**
     * Bulk generate articles
     *
     * @since 2.0.0
     * @param array $ideas Array of idea IDs
     */
    private function bulk_generate($ideas) {
        // Redirect to bulk generation page
        $redirect_url = add_query_arg([
            'page'   => 'lepost-generate-article',
            'bulk'   => '1',
            'ideas'  => implode(',', $ideas),
            '_wpnonce' => wp_create_nonce('bulk_generate_articles'),
        ], admin_url('admin.php'));

        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Bulk export to CSV
     *
     * @since 2.0.0
     * @param array $ideas Array of idea IDs
     */
    private function bulk_export($ideas) {
        $filename = 'lepost-ideas-' . date('Y-m-d-H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, [
            __('ID', 'lepost-client'),
            __('Title', 'lepost-client'),
            __('Description', 'lepost-client'),
            __('Created At', 'lepost-client'),
        ]);

        // Export data
        foreach ($ideas as $idea_id) {
            $idea = $this->idee_model->get_by_id($idea_id);
            if ($idea) {
                fputcsv($output, [
                    $idea->id,
                    $idea->titre,
                    $idea->description,
                    $idea->created_at,
                ]);
            }
        }

        fclose($output);
        exit;
    }

    /**
     * Add admin notice
     *
     * @since 2.0.0
     * @param string $message Notice message
     * @param string $type    Notice type (success, error, warning, info)
     */
    private function add_admin_notice($message, $type = 'info') {
        add_action('admin_notices', function () use ($message, $type) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr($type),
                esc_html($message)
            );
        });
    }

    /**
     * Display the table with search box
     *
     * @since 2.0.0
     */
    public function display_with_search() {
        ?>
        <div class="wrap">
            <?php $this->search_box(__('Search Ideas', 'lepost-client'), 'idea'); ?>
            <form method="post">
                <?php
                $this->prepare_items();
                $this->display();
                ?>
            </form>
        </div>
        <?php
    }
} 