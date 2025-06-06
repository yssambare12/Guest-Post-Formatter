<?php
/**
 * Provide a admin area view for the dashboard page
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Guest_Post_Frontend_Submitter
 * @subpackage Guest_Post_Frontend_Submitter/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get recent submissions
$recent_submissions = get_posts(array(
    'post_type' => 'post',
    'post_status' => array('pending', 'draft'),
    'posts_per_page' => 5,
    'orderby' => 'date',
    'order' => 'DESC',
    'meta_query' => array(
        array(
            'key' => '_gpfs_author_name',
            'compare' => 'EXISTS',
        ),
    ),
));

// Get statistics
global $wpdb;
$total_submissions = $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->posts} p
    JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
    WHERE p.post_type = 'post'
    AND pm.meta_key = '_gpfs_author_name'"
);

$pending_count = $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->posts} p
    JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
    WHERE p.post_type = 'post'
    AND p.post_status = 'pending'
    AND pm.meta_key = '_gpfs_author_name'"
);

$published_count = $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->posts} p
    JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
    WHERE p.post_type = 'post'
    AND p.post_status = 'publish'
    AND pm.meta_key = '_gpfs_author_name'"
);

$rejected_count = $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->posts} p
    JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
    WHERE p.post_type = 'post'
    AND p.post_status = 'trash'
    AND pm.meta_key = '_gpfs_author_name'"
);

// Get submissions from last 30 days
$submissions_last_30_days = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'post'
        AND p.post_date >= %s
        AND pm.meta_key = '_gpfs_author_name'",
        date('Y-m-d', strtotime('-30 days'))
    )
);
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="gpfs-admin-container">
        <div class="gpfs-admin-main">
            <div class="gpfs-dashboard-header">
                <div class="gpfs-dashboard-welcome">
                    <h2><?php _e('Welcome to Guest Post Submitter', 'guest-post-frontend-submitter'); ?></h2>
                    <p><?php _e('Manage guest post submissions, configure settings, and monitor activity from this dashboard.', 'guest-post-frontend-submitter'); ?></p>
                </div>
                
                <div class="gpfs-dashboard-actions">
                    <a href="<?php echo admin_url('admin.php?page=guest-post-submissions'); ?>" class="button button-primary"><?php _e('View All Submissions', 'guest-post-frontend-submitter'); ?></a>
                    <a href="<?php echo admin_url('admin.php?page=guest-post-settings'); ?>" class="button"><?php _e('Configure Settings', 'guest-post-frontend-submitter'); ?></a>
                </div>
            </div>
            
            <div class="gpfs-dashboard-stats">
                <div class="gpfs-stat-box">
                    <h3><?php _e('Total Submissions', 'guest-post-frontend-submitter'); ?></h3>
                    <div class="gpfs-stat-number"><?php echo $total_submissions; ?></div>
                </div>
                
                <div class="gpfs-stat-box">
                    <h3><?php _e('Pending Review', 'guest-post-frontend-submitter'); ?></h3>
                    <div class="gpfs-stat-number"><?php echo $pending_count; ?></div>
                </div>
                
                <div class="gpfs-stat-box">
                    <h3><?php _e('Published', 'guest-post-frontend-submitter'); ?></h3>
                    <div class="gpfs-stat-number"><?php echo $published_count; ?></div>
                </div>
                
                <div class="gpfs-stat-box">
                    <h3><?php _e('Rejected', 'guest-post-frontend-submitter'); ?></h3>
                    <div class="gpfs-stat-number"><?php echo $rejected_count; ?></div>
                </div>
            </div>
            
            <div class="gpfs-dashboard-recent">
                <h2><?php _e('Recent Submissions', 'guest-post-frontend-submitter'); ?></h2>
                
                <?php if (empty($recent_submissions)) : ?>
                    <p><?php _e('No recent submissions found.', 'guest-post-frontend-submitter'); ?></p>
                <?php else : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Title', 'guest-post-frontend-submitter'); ?></th>
                                <th><?php _e('Author', 'guest-post-frontend-submitter'); ?></th>
                                <th><?php _e('Date', 'guest-post-frontend-submitter'); ?></th>
                                <th><?php _e('Status', 'guest-post-frontend-submitter'); ?></th>
                                <th><?php _e('Actions', 'guest-post-frontend-submitter'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_submissions as $post) : 
                                $author_name = get_post_meta($post->ID, '_gpfs_author_name', true);
                                $author_email = get_post_meta($post->ID, '_gpfs_author_email', true);
                                
                                // Create nonces for quick actions
                                $approve_nonce = wp_create_nonce('gpfs_approve_post_' . $post->ID);
                                $reject_nonce = wp_create_nonce('gpfs_reject_post_' . $post->ID);
                                
                                // Create action URLs
                                $admin_url = admin_url('admin-ajax.php');
                                $approve_url = add_query_arg(array(
                                    'action' => 'gpfs_approve_post',
                                    'post_id' => $post->ID,
                                    'nonce' => $approve_nonce,
                                    'redirect' => 'dashboard'
                                ), $admin_url);
                                
                                $reject_url = add_query_arg(array(
                                    'action' => 'gpfs_reject_post',
                                    'post_id' => $post->ID,
                                    'nonce' => $reject_nonce,
                                    'redirect' => 'dashboard'
                                ), $admin_url);
                            ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo get_edit_post_link($post->ID); ?>">
                                            <strong><?php echo esc_html($post->post_title); ?></strong>
                                        </a>
                                    </td>
                                    <td><?php echo esc_html($author_name); ?></td>
                                    <td><?php echo get_the_date('', $post->ID); ?></td>
                                    <td>
                                        <?php 
                                        if ($post->post_status == 'pending') {
                                            echo '<span class="gpfs-status gpfs-status-pending">' . __('Pending', 'guest-post-frontend-submitter') . '</span>';
                                        } else {
                                            echo '<span class="gpfs-status gpfs-status-draft">' . __('Draft', 'guest-post-frontend-submitter') . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo esc_url($approve_url); ?>" class="button button-small"><?php _e('Approve', 'guest-post-frontend-submitter'); ?></a>
                                        <a href="<?php echo esc_url($reject_url); ?>" class="button button-small"><?php _e('Reject', 'guest-post-frontend-submitter'); ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="gpfs-admin-sidebar">
            <div class="gpfs-admin-box">
                <h3><?php _e('Quick Stats', 'guest-post-frontend-submitter'); ?></h3>
                <ul class="gpfs-stats">
                    <li><strong><?php _e('Last 30 Days:', 'guest-post-frontend-submitter'); ?></strong> <?php echo $submissions_last_30_days; ?> <?php _e('submissions', 'guest-post-frontend-submitter'); ?></li>
                    <li><strong><?php _e('Approval Rate:', 'guest-post-frontend-submitter'); ?></strong> <?php echo $total_submissions > 0 ? round(($published_count / $total_submissions) * 100) : 0; ?>%</li>
                </ul>
            </div>
            
            <div class="gpfs-admin-box">
                <h3><?php _e('Shortcode Usage', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('Add the submission form to any page or post:', 'guest-post-frontend-submitter'); ?></p>
                <code>[guest_post_form]</code>
                <p><?php _e('For the enhanced React form:', 'guest-post-frontend-submitter'); ?></p>
                <code>[guest_post_react_form]</code>
            </div>
            
            <div class="gpfs-admin-box">
                <h3><?php _e('Documentation', 'guest-post-frontend-submitter'); ?></h3>
                <p><?php _e('Need help? Check out our documentation:', 'guest-post-frontend-submitter'); ?></p>
                <ul>
                    <li><a href="<?php echo admin_url('admin.php?page=guest-post-documentation'); ?>"><?php _e('Plugin Documentation', 'guest-post-frontend-submitter'); ?></a></li>
                    <li><a href="https://github.com/yourusername/guest-post-frontend-submitter" target="_blank"><?php _e('GitHub Repository', 'guest-post-frontend-submitter'); ?></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .gpfs-admin-container {
        display: flex;
        flex-wrap: wrap;
        margin-top: 20px;
    }
    
    .gpfs-admin-main {
        flex: 1;
        min-width: 600px;
        margin-right: 20px;
    }
    
    .gpfs-admin-sidebar {
        width: 280px;
    }
    
    .gpfs-admin-box {
        background: #fff;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
        margin-bottom: 20px;
        padding: 15px;
    }
    
    .gpfs-admin-box h3 {
        margin-top: 0;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    
    .gpfs-dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .gpfs-dashboard-welcome h2 {
        margin-top: 0;
    }
    
    .gpfs-dashboard-stats {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    
    .gpfs-stat-box {
        background: #fff;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
        padding: 15px;
        margin-right: 15px;
        margin-bottom: 15px;
        flex: 1;
        min-width: 120px;
        text-align: center;
    }
    
    .gpfs-stat-box h3 {
        margin-top: 0;
        margin-bottom: 10px;
        font-size: 14px;
    }
    
    .gpfs-stat-number {
        font-size: 24px;
        font-weight: 600;
    }
    
    .gpfs-dashboard-recent {
        background: #fff;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
        padding: 15px;
    }
    
    .gpfs-dashboard-recent h2 {
        margin-top: 0;
    }
    
    .gpfs-status {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .gpfs-status-pending {
        background-color: #fcf8e3;
        color: #8a6d3b;
    }
    
    .gpfs-status-draft {
        background-color: #d9edf7;
        color: #31708f;
    }
    
    @media screen and (max-width: 782px) {
        .gpfs-admin-main {
            margin-right: 0;
            min-width: 100%;
        }
        
        .gpfs-admin-sidebar {
            width: 100%;
            margin-top: 20px;
        }
        
        .gpfs-dashboard-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .gpfs-dashboard-actions {
            margin-top: 15px;
        }
    }
</style>
