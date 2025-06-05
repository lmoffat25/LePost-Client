<?php
/**
 * API Settings Template
 *
 * Simple API key configuration without complex JavaScript interactions.
 * Uses standard WordPress settings patterns.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/templates
 * @since      2.0.0
 *
 * @var string $api_key Current API key
 * @var array  $api_status API status information
 * @var string $current_tab Current tab
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap lepost-settings">
    <h1 class="wp-heading-inline">
        <?php echo esc_html($page_title); ?>
    </h1>
    
    <hr class="wp-header-end">

    <?php
    // Display admin notices
    if (method_exists($this, 'display_admin_notices')) {
        $this->display_admin_notices();
    }
    ?>

    <!-- Settings Tabs -->
    <nav class="nav-tab-wrapper">
        <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-settings&tab=api')); ?>" 
           class="nav-tab <?php echo $current_tab === 'api' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('API Configuration', 'lepost-client'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-settings&tab=general')); ?>" 
           class="nav-tab <?php echo $current_tab === 'general' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('General Settings', 'lepost-client'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-settings&tab=content')); ?>" 
           class="nav-tab <?php echo $current_tab === 'content' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Content Settings', 'lepost-client'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-settings&tab=generate')); ?>" 
           class="nav-tab <?php echo $current_tab === 'generate' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e('Generate Ideas', 'lepost-client'); ?>
        </a>
    </nav>

    <div class="tab-content">
        <!-- API Configuration -->
        <div class="card">
            <h2><?php esc_html_e('LePost API Configuration', 'lepost-client'); ?></h2>
            
            <form method="post" action="options.php">
                <?php settings_fields('lepost_api_settings_group'); ?>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="api_key">
                                    <?php esc_html_e('API Key', 'lepost-client'); ?>
                                    <span class="required" aria-label="required">*</span>
                                </label>
                            </th>
                            <td>
                                <input type="password" 
                                       id="api_key" 
                                       name="lepost_client_api_key" 
                                       value="<?php echo esc_attr($api_key); ?>" 
                                       class="regular-text" 
                                       autocomplete="off"
                                       placeholder="<?php esc_attr_e('Enter your LePost API key...', 'lepost-client'); ?>">
                                <button type="button" 
                                        id="toggle-api-key" 
                                        class="button button-secondary"
                                        style="margin-left: 5px;">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <p class="description">
                                    <?php esc_html_e('Your LePost API key. You can find this in your LePost account dashboard.', 'lepost-client'); ?>
                                    <br>
                                    <a href="https://lepost.fr/account" target="_blank" rel="noopener">
                                        <?php esc_html_e('Get your API key →', 'lepost-client'); ?>
                                    </a>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <p class="submit">
                    <?php submit_button(__('Save API Key', 'lepost-client'), 'primary', 'submit', false); ?>
                    
                    <?php if (!empty($api_key)): ?>
                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=lepost-settings&tab=api&action=test_api'), 'test_api')); ?>" 
                           class="button button-secondary" 
                           style="margin-left: 10px;">
                            <span class="dashicons dashicons-admin-network"></span>
                            <?php esc_html_e('Test Connection', 'lepost-client'); ?>
                        </a>
                    <?php endif; ?>
                </p>
            </form>
        </div>

        <!-- API Status -->
        <?php if (!empty($api_key)): ?>
            <div class="card">
                <h2><?php esc_html_e('Connection Status', 'lepost-client'); ?></h2>
                
                <?php if ($api_status['is_connected']): ?>
                    <div class="lepost-status-success">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php esc_html_e('API key is configured and working!', 'lepost-client'); ?>
                    </div>

                    <?php if (isset($api_status['connection_test']) && $api_status['connection_test']): ?>
                        <div class="lepost-connection-details">
                            <?php if ($api_status['connection_test']['success']): ?>
                                <div class="lepost-test-success">
                                    <h4><?php esc_html_e('Latest Connection Test', 'lepost-client'); ?></h4>
                                    <p>
                                        <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                                        <?php esc_html_e('Connection successful', 'lepost-client'); ?>
                                    </p>
                                    
                                    <?php if ($api_status['credits'] !== null): ?>
                                        <p>
                                            <span class="dashicons dashicons-chart-bar"></span>
                                            <?php 
                                            printf(
                                                esc_html__('Available credits: %d', 'lepost-client'),
                                                $api_status['credits']
                                            );
                                            ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="lepost-test-error">
                                    <h4><?php esc_html_e('Connection Test Failed', 'lepost-client'); ?></h4>
                                    <p>
                                        <span class="dashicons dashicons-dismiss" style="color: #d63638;"></span>
                                        <?php echo esc_html($api_status['connection_test']['message']); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="lepost-next-steps">
                        <h4><?php esc_html_e('Next Steps', 'lepost-client'); ?></h4>
                        <ul>
                            <li>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-ideas&action=add')); ?>">
                                    <?php esc_html_e('Create your first article idea', 'lepost-client'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-settings&tab=generate')); ?>">
                                    <?php esc_html_e('Generate ideas automatically with AI', 'lepost-client'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-settings&tab=content')); ?>">
                                    <?php esc_html_e('Configure content generation settings', 'lepost-client'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="lepost-status-error">
                        <span class="dashicons dashicons-warning"></span>
                        <?php esc_html_e('API key is set but connection could not be verified.', 'lepost-client'); ?>
                        <br>
                        <?php esc_html_e('Please test the connection or check your API key.', 'lepost-client'); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- No API Key Set -->
            <div class="card">
                <h2><?php esc_html_e('Getting Started', 'lepost-client'); ?></h2>
                <p><?php esc_html_e('To use LePost Client, you need an API key from LePost.', 'lepost-client'); ?></p>
                
                <div class="lepost-getting-started">
                    <h4><?php esc_html_e('How to get your API key:', 'lepost-client'); ?></h4>
                    <ol>
                        <li><?php esc_html_e('Visit your LePost account dashboard', 'lepost-client'); ?></li>
                        <li><?php esc_html_e('Navigate to the API section', 'lepost-client'); ?></li>
                        <li><?php esc_html_e('Copy your API key', 'lepost-client'); ?></li>
                        <li><?php esc_html_e('Paste it in the field above and save', 'lepost-client'); ?></li>
                    </ol>
                    
                    <p>
                        <a href="https://lepost.fr/account" 
                           target="_blank" 
                           rel="noopener" 
                           class="button button-primary">
                            <?php esc_html_e('Get Your API Key', 'lepost-client'); ?>
                        </a>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Help Information -->
        <div class="card">
            <h2><?php esc_html_e('API Usage Information', 'lepost-client'); ?></h2>
            <p><?php esc_html_e('Your API key is used to authenticate requests to the LePost service for:', 'lepost-client'); ?></p>
            <ul>
                <li><?php esc_html_e('Generating article ideas from topics', 'lepost-client'); ?></li>
                <li><?php esc_html_e('Creating full articles from ideas', 'lepost-client'); ?></li>
                <li><?php esc_html_e('Customizing content based on your brand voice', 'lepost-client'); ?></li>
            </ul>
            
            <p>
                <strong><?php esc_html_e('Security Note:', 'lepost-client'); ?></strong>
                <?php esc_html_e('Your API key is stored securely in your WordPress database and never transmitted to third parties.', 'lepost-client'); ?>
            </p>
        </div>
    </div>
</div>

<style>
/* Settings page styles */
.lepost-settings .tab-content {
    margin-top: 20px;
}

.lepost-settings .card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.lepost-settings .card h2 {
    margin-top: 0;
}

.lepost-settings .required {
    color: #d63638;
    font-weight: bold;
}

.lepost-status-success {
    background: #e8f5e8;
    border: 1px solid #4caf50;
    border-radius: 4px;
    padding: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #2e7d32;
    font-weight: 500;
}

.lepost-status-error {
    background: #ffeaa7;
    border: 1px solid #fdcb6e;
    border-radius: 4px;
    padding: 12px;
    display: flex;
    align-items: flex-start;
    gap: 8px;
    color: #8b4513;
}

.lepost-connection-details {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #ddd;
}

.lepost-test-success p,
.lepost-test-error p {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 8px 0;
}

.lepost-next-steps,
.lepost-getting-started {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #ddd;
}

.lepost-next-steps ul,
.lepost-getting-started ol {
    margin-left: 20px;
}

.lepost-next-steps li,
.lepost-getting-started li {
    margin-bottom: 8px;
}

#toggle-api-key {
    padding: 3px 8px;
    height: auto;
    vertical-align: middle;
}
</style>

<script>
// Simple API key visibility toggle
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggle-api-key');
    const apiKeyInput = document.getElementById('api_key');
    
    if (toggleButton && apiKeyInput) {
        toggleButton.addEventListener('click', function() {
            const isPassword = apiKeyInput.type === 'password';
            apiKeyInput.type = isPassword ? 'text' : 'password';
            
            const icon = toggleButton.querySelector('.dashicons');
            if (icon) {
                icon.className = isPassword ? 'dashicons dashicons-hidden' : 'dashicons dashicons-visibility';
            }
        });
    }
});
</script> 