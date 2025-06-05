<?php
/**
 * Ideas List Template
 *
 * Displays the ideas list using standard WordPress list table.
 * Replaces the complex JavaScript interface with simple, maintainable HTML.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/templates
 * @since      2.0.0
 *
 * @var IdeasListTable $list_table List table instance
 * @var Api            $api        API instance
 * @var string         $page_title Page title
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap lepost-ideas-page">
    <h1 class="wp-heading-inline">
        <?php echo esc_html($page_title); ?>
    </h1>
    
    <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-ideas&action=add')); ?>" 
       class="page-title-action">
        <?php esc_html_e('Add New Idea', 'lepost-client'); ?>
    </a>
    
    <hr class="wp-header-end">

    <?php
    // Display admin notices
    if (method_exists($this, 'display_admin_notices')) {
        $this->display_admin_notices();
    }
    ?>

    <!-- Quick Actions Bar -->
    <div class="lepost-quick-actions">
        <div class="alignleft actions">
            <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-ideas&action=add')); ?>" 
               class="button">
                <span class="dashicons dashicons-plus-alt"></span>
                <?php esc_html_e('Add New Idea', 'lepost-client'); ?>
            </a>
            
            <?php if ($api->is_api_key_set()): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-settings&tab=generate')); ?>" 
                   class="button">
                    <span class="dashicons dashicons-lightbulb"></span>
                    <?php esc_html_e('Generate Ideas with AI', 'lepost-client'); ?>
                </a>
            <?php endif; ?>
        </div>
        
        <div class="alignright actions">
            <a href="#" class="button" onclick="document.getElementById('import-form').style.display='block';">
                <span class="dashicons dashicons-upload"></span>
                <?php esc_html_e('Import CSV', 'lepost-client'); ?>
            </a>
        </div>
        
        <div class="clear"></div>
    </div>

    <!-- Import Form (hidden by default) -->
    <div id="import-form" class="lepost-import-form" style="display: none;">
        <div class="card">
            <h3><?php esc_html_e('Import Ideas from CSV', 'lepost-client'); ?></h3>
            
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('import_csv'); ?>
                <input type="hidden" name="action" value="import_csv">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="csv_file">
                                <?php esc_html_e('CSV File', 'lepost-client'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="file" 
                                   id="csv_file" 
                                   name="csv_file" 
                                   accept=".csv" 
                                   required 
                                   class="file-upload-input">
                            <p class="description">
                                <?php esc_html_e('CSV format: Title, Description (first row will be skipped as header)', 'lepost-client'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php esc_html_e('Import Ideas', 'lepost-client'); ?>
                    </button>
                    <button type="button" 
                            class="button" 
                            onclick="document.getElementById('import-form').style.display='none';">
                        <?php esc_html_e('Cancel', 'lepost-client'); ?>
                    </button>
                </p>
            </form>
        </div>
    </div>

    <!-- Ideas List Table -->
    <div class="lepost-list-table-container">
        <?php
        // Process bulk actions before displaying table
        $list_table->process_bulk_action();
        
        // Display the table with search
        $list_table->display_with_search();
        ?>
    </div>

    <!-- Usage Information (if API connected) -->
    <?php if ($api->is_api_key_set()): ?>
        <div class="lepost-usage-info">
            <div class="card">
                <h3><?php esc_html_e('API Usage', 'lepost-client'); ?></h3>
                <p>
                    <?php esc_html_e('Connected to LePost API', 'lepost-client'); ?>
                    <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                </p>
                <p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-settings')); ?>">
                        <?php esc_html_e('Manage API Settings', 'lepost-client'); ?>
                    </a>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Help Information -->
    <div class="lepost-help-info">
        <div class="card">
            <h3><?php esc_html_e('How to Use Ideas Manager', 'lepost-client'); ?></h3>
            <ul>
                <li><?php esc_html_e('Add ideas manually using the "Add New Idea" button', 'lepost-client'); ?></li>
                <li><?php esc_html_e('Import multiple ideas from a CSV file', 'lepost-client'); ?></li>
                <li><?php esc_html_e('Generate articles from ideas using the "Generate" button', 'lepost-client'); ?></li>
                <li><?php esc_html_e('Use bulk actions to manage multiple ideas at once', 'lepost-client'); ?></li>
                <li><?php esc_html_e('Export ideas to CSV for backup or sharing', 'lepost-client'); ?></li>
            </ul>
        </div>
    </div>
</div>

<style>
/* Simple styling for the new interface */
.lepost-ideas-page .lepost-quick-actions {
    margin: 20px 0;
    background: #f1f1f1;
    padding: 15px;
    border-radius: 3px;
}

.lepost-ideas-page .lepost-import-form {
    margin: 20px 0;
}

.lepost-ideas-page .lepost-usage-info,
.lepost-ideas-page .lepost-help-info {
    margin-top: 20px;
    max-width: 600px;
}

.lepost-ideas-page .card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.lepost-ideas-page .card h3 {
    margin-top: 0;
}

.lepost-ideas-page .button .dashicons {
    line-height: 1.2;
    margin-right: 5px;
}

.lepost-list-table-container {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 0;
    margin: 20px 0;
}
</style> 