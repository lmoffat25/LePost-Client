<?php
/**
 * Add Idea Form Template
 *
 * Simple form for adding new ideas using standard WordPress patterns.
 * No JavaScript dependencies - pure HTML form submission.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/templates
 * @since      2.0.0
 *
 * @var Api    $api        API instance
 * @var string $page_title Page title
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap lepost-add-idea-page">
    <h1 class="wp-heading-inline">
        <?php esc_html_e('Add New Idea', 'lepost-client'); ?>
    </h1>
    
    <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-ideas')); ?>" 
       class="page-title-action">
        <?php esc_html_e('Back to Ideas List', 'lepost-client'); ?>
    </a>
    
    <hr class="wp-header-end">

    <?php
    // Display admin notices
    if (method_exists($this, 'display_admin_notices')) {
        $this->display_admin_notices();
    }
    ?>

    <div class="lepost-form-container">
        <div class="card">
            <form method="post" data-validate>
                <?php wp_nonce_field('add_idea'); ?>
                <input type="hidden" name="action" value="add_idea">
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="title">
                                    <?php esc_html_e('Title', 'lepost-client'); ?>
                                    <span class="required" aria-label="required">*</span>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="title" 
                                       name="title" 
                                       class="regular-text" 
                                       required 
                                       maxlength="255"
                                       placeholder="<?php esc_attr_e('Enter idea title...', 'lepost-client'); ?>">
                                <p class="description">
                                    <?php esc_html_e('A clear, descriptive title for your article idea.', 'lepost-client'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="description">
                                    <?php esc_html_e('Description', 'lepost-client'); ?>
                                </label>
                            </th>
                            <td>
                                <textarea id="description" 
                                          name="description" 
                                          rows="6" 
                                          cols="50" 
                                          maxlength="1000"
                                          placeholder="<?php esc_attr_e('Describe your article idea in detail...', 'lepost-client'); ?>"></textarea>
                                <p class="description">
                                    <?php esc_html_e('Detailed description to help with article generation. Optional but recommended.', 'lepost-client'); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <?php submit_button(__('Add Idea', 'lepost-client'), 'primary', 'submit', false, [
                    'id' => 'add-idea-submit'
                ]); ?>
                
                <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-ideas')); ?>" 
                   class="button">
                    <?php esc_html_e('Cancel', 'lepost-client'); ?>
                </a>
            </form>
        </div>
    </div>

    <!-- AI Generation Options (if API connected) -->
    <?php if ($api->is_api_key_set()): ?>
        <div class="lepost-ai-options">
            <div class="card">
                <h3><?php esc_html_e('AI-Powered Idea Generation', 'lepost-client'); ?></h3>
                <p>
                    <?php esc_html_e('Want to generate multiple ideas automatically?', 'lepost-client'); ?>
                </p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-settings&tab=generate')); ?>" 
                   class="button">
                    <span class="dashicons dashicons-lightbulb"></span>
                    <?php esc_html_e('Generate Ideas with AI', 'lepost-client'); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Tips for Good Ideas -->
    <div class="lepost-tips">
        <div class="card">
            <h3><?php esc_html_e('Tips for Creating Good Ideas', 'lepost-client'); ?></h3>
            <ul>
                <li>
                    <strong><?php esc_html_e('Be Specific:', 'lepost-client'); ?></strong>
                    <?php esc_html_e('Use descriptive titles that clearly indicate the topic or angle.', 'lepost-client'); ?>
                </li>
                <li>
                    <strong><?php esc_html_e('Add Context:', 'lepost-client'); ?></strong>
                    <?php esc_html_e('Include relevant details in the description to guide article generation.', 'lepost-client'); ?>
                </li>
                <li>
                    <strong><?php esc_html_e('Consider Your Audience:', 'lepost-client'); ?></strong>
                    <?php esc_html_e('Think about what your readers want to know or learn.', 'lepost-client'); ?>
                </li>
                <li>
                    <strong><?php esc_html_e('Use Keywords:', 'lepost-client'); ?></strong>
                    <?php esc_html_e('Include relevant keywords that people might search for.', 'lepost-client'); ?>
                </li>
            </ul>
        </div>
    </div>
</div>

<style>
/* Simple styling for the add idea form */
.lepost-add-idea-page .lepost-form-container {
    max-width: 800px;
    margin: 20px 0;
}

.lepost-add-idea-page .card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.lepost-add-idea-page .card h3 {
    margin-top: 0;
}

.lepost-add-idea-page .required {
    color: #d63638;
    font-weight: bold;
}

.lepost-add-idea-page .form-table th {
    width: 150px;
    vertical-align: top;
    padding-top: 15px;
}

.lepost-add-idea-page .form-table td {
    padding-bottom: 20px;
}

.lepost-add-idea-page .regular-text {
    width: 100%;
    max-width: 500px;
}

.lepost-add-idea-page textarea {
    width: 100%;
    max-width: 500px;
    resize: vertical;
}

.lepost-add-idea-page .character-counter {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

.lepost-add-idea-page .character-counter.over-limit {
    color: #d63638;
    font-weight: bold;
}

.lepost-add-idea-page .error {
    border-color: #d63638 !important;
    box-shadow: 0 0 2px rgba(214, 54, 56, 0.8);
}

.lepost-add-idea-page .error-message {
    color: #d63638;
    font-size: 12px;
    margin-top: 5px;
    display: block;
}

.lepost-add-idea-page .button .dashicons {
    line-height: 1.2;
    margin-right: 5px;
}

.lepost-add-idea-page .lepost-ai-options,
.lepost-add-idea-page .lepost-tips {
    max-width: 600px;
}

.lepost-add-idea-page .lepost-tips ul {
    margin-left: 0;
}

.lepost-add-idea-page .lepost-tips li {
    margin-bottom: 10px;
}
</style> 