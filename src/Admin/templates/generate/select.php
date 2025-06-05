<?php
/**
 * Generate Article Selection Template
 *
 * Simple interface for selecting ideas to generate articles from.
 * No JavaScript dependencies - pure form-based selection.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/templates
 * @since      2.0.0
 *
 * @var array $ideas Available ideas
 * @var int   $total_ideas Total number of ideas
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap lepost-generate-select">
    <h1 class="wp-heading-inline">
        <?php esc_html_e('Generate Articles', 'lepost-client'); ?>
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

    <?php if (empty($ideas)): ?>
        <!-- No Ideas Available -->
        <div class="card">
            <h2><?php esc_html_e('No Ideas Available', 'lepost-client'); ?></h2>
            <p><?php esc_html_e('You need to create some article ideas before you can generate articles.', 'lepost-client'); ?></p>
            
            <div class="lepost-no-ideas-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-ideas&action=add')); ?>" 
                   class="button button-primary">
                    <span class="dashicons dashicons-plus"></span>
                    <?php esc_html_e('Create Your First Idea', 'lepost-client'); ?>
                </a>
                
                <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-settings&tab=generate')); ?>" 
                   class="button">
                    <span class="dashicons dashicons-lightbulb"></span>
                    <?php esc_html_e('Generate Ideas with AI', 'lepost-client'); ?>
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Ideas Selection -->
        <div class="card">
            <h2><?php esc_html_e('Select Ideas for Article Generation', 'lepost-client'); ?></h2>
            <p><?php esc_html_e('Choose one or more ideas to generate articles from. Each generation will consume API credits.', 'lepost-client'); ?></p>
            
            <form method="post" id="generate-articles-form">
                <?php wp_nonce_field('bulk_generate_articles'); ?>
                <input type="hidden" name="action" value="generate_bulk">
                
                <!-- Selection Controls -->
                <div class="lepost-selection-controls">
                    <label>
                        <input type="checkbox" id="select-all-ideas"> 
                        <?php esc_html_e('Select All', 'lepost-client'); ?>
                    </label>
                    
                    <span class="lepost-selected-count">
                        <?php esc_html_e('0 ideas selected', 'lepost-client'); ?>
                    </span>
                </div>
                
                <!-- Ideas Table -->
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <td class="manage-column column-cb check-column">
                                <input type="checkbox" id="cb-select-all">
                            </td>
                            <th class="manage-column"><?php esc_html_e('Title', 'lepost-client'); ?></th>
                            <th class="manage-column"><?php esc_html_e('Description', 'lepost-client'); ?></th>
                            <th class="manage-column"><?php esc_html_e('Created', 'lepost-client'); ?></th>
                            <th class="manage-column"><?php esc_html_e('Status', 'lepost-client'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ideas as $idea): ?>
                            <?php $has_article = false; // TODO: Check if article exists ?>
                            <tr <?php echo $has_article ? 'class="idea-has-article"' : ''; ?>>
                                <th scope="row" class="check-column">
                                    <input type="checkbox" 
                                           name="idea_ids[]" 
                                           value="<?php echo esc_attr($idea->id); ?>"
                                           class="idea-checkbox"
                                           <?php echo $has_article ? 'disabled' : ''; ?>>
                                </th>
                                <td>
                                    <strong><?php echo esc_html($idea->titre); ?></strong>
                                    <div class="row-actions">
                                        <span class="edit">
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-ideas&action=edit&idea=' . $idea->id . '&_wpnonce=' . wp_create_nonce('edit_idea_' . $idea->id))); ?>">
                                                <?php esc_html_e('Edit', 'lepost-client'); ?>
                                            </a>
                                        </span>
                                        <?php if (!$has_article): ?>
                                            | <span class="generate">
                                                <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-generate-article&idea=' . $idea->id . '&_wpnonce=' . wp_create_nonce('generate_article_' . $idea->id))); ?>">
                                                    <?php esc_html_e('Generate Single', 'lepost-client'); ?>
                                                </a>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $description = esc_html($idea->description);
                                    echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                                    ?>
                                </td>
                                <td>
                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($idea->created_at))); ?>
                                </td>
                                <td>
                                    <?php if ($has_article): ?>
                                        <span class="lepost-status-badge lepost-status-generated">
                                            <?php esc_html_e('Generated', 'lepost-client'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="lepost-status-badge lepost-status-pending">
                                            <?php esc_html_e('Ready', 'lepost-client'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Generation Options -->
                <div class="lepost-generation-options">
                    <h3><?php esc_html_e('Generation Options', 'lepost-client'); ?></h3>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php esc_html_e('Content Settings', 'lepost-client'); ?>
                            </th>
                            <td>
                                <p class="description">
                                    <?php esc_html_e('Articles will be generated using your configured content settings.', 'lepost-client'); ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-settings&tab=content')); ?>">
                                        <?php esc_html_e('Modify content settings', 'lepost-client'); ?>
                                    </a>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <?php esc_html_e('WordPress Posts', 'lepost-client'); ?>
                            </th>
                            <td>
                                <p class="description">
                                    <?php 
                                    $settings = get_option('lepost_client_settings', []);
                                    $autopost = isset($settings['autopost_articles']) ? $settings['autopost_articles'] : false;
                                    
                                    if ($autopost) {
                                        esc_html_e('Generated articles will automatically be created as WordPress posts.', 'lepost-client');
                                    } else {
                                        esc_html_e('Generated articles will be saved but not automatically created as WordPress posts.', 'lepost-client');
                                    }
                                    ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-settings&tab=general')); ?>">
                                        <?php esc_html_e('Modify post settings', 'lepost-client'); ?>
                                    </a>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Submit Buttons -->
                <div class="lepost-submit-actions">
                    <button type="submit" 
                            class="button button-primary" 
                            id="generate-selected-btn"
                            disabled>
                        <span class="dashicons dashicons-edit"></span>
                        <?php esc_html_e('Generate Selected Articles', 'lepost-client'); ?>
                    </button>
                    
                    <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-ideas')); ?>" 
                       class="button">
                        <?php esc_html_e('Back to Ideas', 'lepost-client'); ?>
                    </a>
                </div>
            </form>
        </div>

        <!-- Information -->
        <div class="card">
            <h3><?php esc_html_e('Important Information', 'lepost-client'); ?></h3>
            <ul>
                <li><?php esc_html_e('Each article generation consumes API credits from your LePost account.', 'lepost-client'); ?></li>
                <li><?php esc_html_e('Generation may take a few moments per article. Please be patient.', 'lepost-client'); ?></li>
                <li><?php esc_html_e('Ideas that already have generated articles are disabled and shown in gray.', 'lepost-client'); ?></li>
                <li><?php esc_html_e('You can generate articles one at a time or in bulk using the selections above.', 'lepost-client'); ?></li>
            </ul>
        </div>
    <?php endif; ?>
</div>

<style>
/* Generate articles selection styles */
.lepost-generate-select .card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.lepost-generate-select .card h2,
.lepost-generate-select .card h3 {
    margin-top: 0;
}

.lepost-no-ideas-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.lepost-no-ideas-actions .button {
    display: flex;
    align-items: center;
    gap: 5px;
}

.lepost-selection-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 15px 0;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 4px;
}

.lepost-selected-count {
    font-weight: 500;
    color: #0073aa;
}

.lepost-generation-options {
    margin: 20px 0;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.lepost-submit-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.lepost-submit-actions .button {
    display: flex;
    align-items: center;
    gap: 5px;
}

.idea-has-article {
    background-color: #f5f5f5;
    opacity: 0.6;
}

.lepost-status-badge {
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}

.lepost-status-generated {
    background: #e1f5fe;
    color: #0277bd;
}

.lepost-status-pending {
    background: #fff3e0;
    color: #ef6c00;
}

#generate-selected-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<script>
// Simple selection management without complex JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-ideas');
    const cbSelectAllCheckbox = document.getElementById('cb-select-all');
    const ideaCheckboxes = document.querySelectorAll('.idea-checkbox:not([disabled])');
    const selectedCountSpan = document.querySelector('.lepost-selected-count');
    const generateButton = document.getElementById('generate-selected-btn');
    
    function updateSelectedCount() {
        const selectedCount = document.querySelectorAll('.idea-checkbox:checked').length;
        const totalCount = ideaCheckboxes.length;
        
        selectedCountSpan.textContent = selectedCount === 1 
            ? '<?php esc_html_e('1 idea selected', 'lepost-client'); ?>'
            : selectedCount + ' <?php esc_html_e('ideas selected', 'lepost-client'); ?>';
            
        generateButton.disabled = selectedCount === 0;
        
        // Update select all checkboxes
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = selectedCount === totalCount && totalCount > 0;
            selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < totalCount;
        }
        
        if (cbSelectAllCheckbox) {
            cbSelectAllCheckbox.checked = selectedCount === totalCount && totalCount > 0;
            cbSelectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < totalCount;
        }
    }
    
    // Handle select all
    function handleSelectAll(checked) {
        ideaCheckboxes.forEach(checkbox => {
            checkbox.checked = checked;
        });
        updateSelectedCount();
    }
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            handleSelectAll(this.checked);
        });
    }
    
    if (cbSelectAllCheckbox) {
        cbSelectAllCheckbox.addEventListener('change', function() {
            handleSelectAll(this.checked);
        });
    }
    
    // Handle individual checkboxes
    ideaCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
    
    // Initial count update
    updateSelectedCount();
});
</script> 