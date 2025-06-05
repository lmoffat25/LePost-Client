<?php
/**
 * Main Dashboard Template
 *
 * Clean, informative dashboard without complex JavaScript interactions.
 * Displays key statistics and recent activity.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/templates
 * @since      2.0.0
 *
 * @var array $stats Statistics data
 * @var array $recent_ideas Recent ideas
 * @var array $recent_articles Recent articles  
 * @var array $api_status API status information
 * @var array $notifications Dashboard notifications
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap lepost-dashboard">
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

    <!-- Dashboard Notifications -->
    <?php if (!empty($notifications)): ?>
        <div class="lepost-dashboard-notifications">
            <?php foreach ($notifications as $notification): ?>
                <div class="notice notice-<?php echo esc_attr($notification['type']); ?> is-dismissible">
                    <p><?php echo wp_kses_post($notification['message']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="lepost-dashboard-cards">
        <div class="lepost-dashboard-card">
            <div class="lepost-dashboard-card-icon">
                <span class="dashicons dashicons-lightbulb"></span>
            </div>
            <div class="lepost-dashboard-card-content">
                <div class="lepost-dashboard-card-value"><?php echo esc_html($stats['ideas_count']); ?></div>
                <div class="lepost-dashboard-card-label"><?php esc_html_e('Ideas Created', 'lepost-client'); ?></div>
            </div>
            <div class="lepost-dashboard-card-action">
                <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-ideas')); ?>" class="button">
                    <?php esc_html_e('Manage Ideas', 'lepost-client'); ?>
                </a>
            </div>
        </div>

        <div class="lepost-dashboard-card">
            <div class="lepost-dashboard-card-icon">
                <span class="dashicons dashicons-admin-post"></span>
            </div>
            <div class="lepost-dashboard-card-content">
                <div class="lepost-dashboard-card-value"><?php echo esc_html($stats['articles_count']); ?></div>
                <div class="lepost-dashboard-card-label"><?php esc_html_e('Articles Generated', 'lepost-client'); ?></div>
            </div>
            <div class="lepost-dashboard-card-action">
                <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-generate-article')); ?>" class="button">
                    <?php esc_html_e('Generate More', 'lepost-client'); ?>
                </a>
            </div>
        </div>

        <div class="lepost-dashboard-card">
            <div class="lepost-dashboard-card-icon">
                <span class="dashicons dashicons-wordpress"></span>
            </div>
            <div class="lepost-dashboard-card-content">
                <div class="lepost-dashboard-card-value"><?php echo esc_html($stats['posts_count']); ?></div>
                <div class="lepost-dashboard-card-label"><?php esc_html_e('WordPress Posts', 'lepost-client'); ?></div>
            </div>
            <div class="lepost-dashboard-card-action">
                <a href="<?php echo esc_url(admin_url('edit.php')); ?>" class="button">
                    <?php esc_html_e('View Posts', 'lepost-client'); ?>
                </a>
            </div>
        </div>

        <?php if ($api_status['is_connected']): ?>
            <div class="lepost-dashboard-card">
                <div class="lepost-dashboard-card-icon">
                    <span class="dashicons dashicons-cloud"></span>
                </div>
                <div class="lepost-dashboard-card-content">
                    <div class="lepost-dashboard-card-value">
                        <?php echo $api_status['credits'] !== null ? esc_html($api_status['credits']) : '?'; ?>
                    </div>
                    <div class="lepost-dashboard-card-label"><?php esc_html_e('API Credits', 'lepost-client'); ?></div>
                </div>
                <div class="lepost-dashboard-card-action">
                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=lepost-dashboard&action=refresh_credits'), 'refresh_credits')); ?>" 
                       class="button">
                        <?php esc_html_e('Refresh', 'lepost-client'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div class="lepost-quick-actions">
        <div class="card">
            <h2><?php esc_html_e('Quick Actions', 'lepost-client'); ?></h2>
            <div class="lepost-action-buttons">
                <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-ideas&action=add')); ?>" 
                   class="button button-primary">
                    <span class="dashicons dashicons-plus"></span>
                    <?php esc_html_e('Add New Idea', 'lepost-client'); ?>
                </a>

                <?php if ($api_status['is_connected']): ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-settings&tab=generate')); ?>" 
                       class="button">
                        <span class="dashicons dashicons-lightbulb"></span>
                        <?php esc_html_e('Generate Ideas with AI', 'lepost-client'); ?>
                    </a>

                    <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-generate-article')); ?>" 
                       class="button">
                        <span class="dashicons dashicons-edit"></span>
                        <?php esc_html_e('Generate Articles', 'lepost-client'); ?>
                    </a>
                <?php else: ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-settings&tab=api')); ?>" 
                       class="button">
                        <span class="dashicons dashicons-admin-network"></span>
                        <?php esc_html_e('Setup API Connection', 'lepost-client'); ?>
                    </a>
                <?php endif; ?>

                <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-settings')); ?>" 
                   class="button">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php esc_html_e('Settings', 'lepost-client'); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Ideas -->
    <?php if (!empty($recent_ideas)): ?>
        <div class="lepost-recent-section">
            <div class="card">
                <h2><?php esc_html_e('Recent Ideas', 'lepost-client'); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Title', 'lepost-client'); ?></th>
                            <th><?php esc_html_e('Created', 'lepost-client'); ?></th>
                            <th><?php esc_html_e('Actions', 'lepost-client'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_ideas as $idea): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($idea->titre); ?></strong>
                                    <?php if (!empty($idea->description)): ?>
                                        <div class="row-actions">
                                            <span class="description">
                                                <?php 
                                                $description = esc_html($idea->description);
                                                echo strlen($description) > 80 ? substr($description, 0, 80) . '...' : $description;
                                                ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($idea->created_at))); ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-ideas&action=edit&idea=' . $idea->id . '&_wpnonce=' . wp_create_nonce('edit_idea_' . $idea->id))); ?>" 
                                       class="button button-small">
                                        <?php esc_html_e('Edit', 'lepost-client'); ?>
                                    </a>
                                    <?php if ($api_status['is_connected']): ?>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-generate-article&idea=' . $idea->id . '&_wpnonce=' . wp_create_nonce('generate_article_' . $idea->id))); ?>" 
                                           class="button button-primary button-small">
                                            <?php esc_html_e('Generate', 'lepost-client'); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="card-footer">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-ideas')); ?>">
                        <?php esc_html_e('View All Ideas →', 'lepost-client'); ?>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Recent Articles -->
    <?php if (!empty($recent_articles)): ?>
        <div class="lepost-recent-section">
            <div class="card">
                <h2><?php esc_html_e('Recent Articles', 'lepost-client'); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Title', 'lepost-client'); ?></th>
                            <th><?php esc_html_e('Status', 'lepost-client'); ?></th>
                            <th><?php esc_html_e('Generated', 'lepost-client'); ?></th>
                            <th><?php esc_html_e('Actions', 'lepost-client'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_articles as $article): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($article->titre); ?></strong>
                                </td>
                                <td>
                                    <span class="lepost-status-badge lepost-status-<?php echo esc_attr($article->statut); ?>">
                                        <?php 
                                        switch ($article->statut) {
                                            case 'generated':
                                                esc_html_e('Generated', 'lepost-client');
                                                break;
                                            case 'published':
                                                esc_html_e('Published', 'lepost-client');
                                                break;
                                            default:
                                                echo esc_html($article->statut);
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($article->created_at))); ?>
                                </td>
                                <td>
                                    <?php if (!empty($article->post_id)): ?>
                                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $article->post_id . '&action=edit')); ?>" 
                                           class="button button-small">
                                            <?php esc_html_e('Edit Post', 'lepost-client'); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- API Status -->
    <?php if ($api_status['is_connected']): ?>
        <div class="lepost-api-status">
            <div class="card">
                <h2><?php esc_html_e('API Status', 'lepost-client'); ?></h2>
                <div class="lepost-status-item">
                    <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                    <?php esc_html_e('Connected to LePost API', 'lepost-client'); ?>
                </div>
                
                <?php if ($api_status['credits'] !== null): ?>
                    <div class="lepost-status-item">
                        <span class="dashicons dashicons-chart-bar"></span>
                        <?php 
                        printf(
                            esc_html__('Available Credits: %d', 'lepost-client'),
                            $api_status['credits']
                        );
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($api_status['last_check']): ?>
                    <div class="lepost-status-item">
                        <span class="dashicons dashicons-clock"></span>
                        <?php 
                        printf(
                            esc_html__('Last updated: %s', 'lepost-client'),
                            date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $api_status['last_check'])
                        );
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Dashboard Styles */
.lepost-dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.lepost-dashboard-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.lepost-dashboard-card-icon {
    font-size: 40px;
    color: #0073aa;
}

.lepost-dashboard-card-content {
    flex: 1;
}

.lepost-dashboard-card-value {
    font-size: 32px;
    font-weight: bold;
    color: #23282d;
    line-height: 1;
}

.lepost-dashboard-card-label {
    color: #666;
    font-size: 14px;
    margin-top: 5px;
}

.lepost-dashboard-card-action .button {
    font-size: 12px;
}

.lepost-quick-actions {
    margin: 20px 0;
}

.lepost-action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.lepost-action-buttons .button {
    display: flex;
    align-items: center;
    gap: 5px;
}

.lepost-recent-section {
    margin: 20px 0;
}

.lepost-recent-section .card-footer {
    padding: 15px 0 0;
    border-top: 1px solid #eee;
    margin-top: 15px;
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

.lepost-status-published {
    background: #e8f5e8;
    color: #2e7d32;
}

.lepost-api-status .lepost-status-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 10px 0;
}

.lepost-dashboard-notifications {
    margin: 20px 0;
}
</style> 