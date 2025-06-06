<?php
/**
 * Provide a admin area view for the submissions page
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

// Get submissions
$submissions_query = new WP_Query(array(
    'post_type' => 'post',
    'post_status' => array('pending', 'draft'),
    'posts_per_page' => 20,
    'orderby' => 'date',
    'order' => 'DESC',
    'meta_query' => array(
        array(
            'key' => '_gpfs_author_name',
            'compare' => 'EXISTS',
        ),
    ),
    'paged' => get_query_var('paged') ? get_query_var('paged') : 1
));

$submissions = $submissions_query->posts;
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="gpfs-admin-container">
        <div class="gpfs-admin-main">
            <div class="gpfs-submissions-list">
                <h2><?php _e('Guest Post Submissions', 'guest-post-frontend-submitter'); ?></h2>
                
                <?php if (empty($submissions)) : ?>
                    <p><?php _e('No submissions found.', 'guest-post-frontend-submitter'); ?></p>
                <?php else : ?>
                    <table class="wp-list-table widefat fixed striped posts">
                        <thead>
                            <tr>
                                <th scope="col" class="manage-column column-title column-primary"><?php _e('Title', 'guest-post-frontend-submitter'); ?></th>
                                <th scope="col" class="manage-column"><?php _e('Author', 'guest-post-frontend-submitter'); ?></th>
                                <th scope="col" class="manage-column"><?php _e('Category', 'guest-post-frontend-submitter'); ?></th>
                                <th scope="col" class="manage-column"><?php _e('Date', 'guest-post-frontend-submitter'); ?></th>
                                <th scope="col" class="manage-column"><?php _e('Status', 'guest-post-frontend-submitter'); ?></th>
                                <th scope="col" class="manage-column"><?php _e('Actions', 'guest-post-frontend-submitter'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $post) : 
                                $author_name = get_post_meta($post->ID, '_gpfs_author_name', true);
                                $author_email = get_post_meta($post->ID, '_gpfs_author_email', true);
                                $categories = get_the_category($post->ID);
                                $category_name = !empty($categories) ? $categories[0]->name : __('Uncategorized', 'guest-post-frontend-submitter');
                                
                                // Create nonces for quick actions
                                $approve_nonce = wp_create_nonce('gpfs_approve_post_' . $post->ID);
                                $reject_nonce = wp_create_nonce('gpfs_reject_post_' . $post->ID);
                                
                                // Create action URLs
                                $admin_url = admin_url('admin-ajax.php');
                                $approve_url = add_query_arg(array(
                                    'action' => 'gpfs_approve_post',
                                    'post_id' => $post->ID,
                                    'nonce' => $approve_nonce,
                                    'redirect' => 'submissions'
                                ), $admin_url);
                                
                                $reject_url = add_query_arg(array(
                                    'action' => 'gpfs_reject_post',
                                    'post_id' => $post->ID,
                                    'nonce' => $reject_nonce,
                                    'redirect' => 'submissions'
                                ), $admin_url);
                            ?>
                                <tr>
                                    <td class="title column-title column-primary">
                                        <strong><a href="<?php echo get_edit_post_link($post->ID); ?>"><?php echo esc_html($post->post_title); ?></a></strong>
                                        <div class="row-actions">
                                            <span class="edit"><a href="<?php echo get_edit_post_link($post->ID); ?>"><?php _e('Edit', 'guest-post-frontend-submitter'); ?></a> | </span>
                                            <span class="view"><a href="<?php echo get_permalink($post->ID); ?>"><?php _e('Preview', 'guest-post-frontend-submitter'); ?></a></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo esc_html($author_name); ?>
                                        <div class="row-actions">
                                            <span class="email"><?php echo esc_html($author_email); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo esc_html($category_name); ?></td>
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
                                        <a href="<?php echo esc_url($approve_url); ?>" class="button button-primary gpfs-approve-button"><?php _e('Approve', 'guest-post-frontend-submitter'); ?></a>
                                        <a href="<?php echo esc_url($reject_url); ?>" class="button gpfs-reject-button"><?php _e('Reject', 'guest-post-frontend-submitter'); ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php
                    // Pagination
                    $big = 999999999;
                    echo '<div class="tablenav"><div class="tablenav-pages">';
                    echo paginate_links(array(
                        'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                        'format' => '?paged=%#%',
                        'current' => max(1, get_query_var('paged')),
                        'total' => $submissions_query->max_num_pages
                    ));
                    echo '</div></div>';
                    ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="gpfs-admin-sidebar">
            <div class="gpfs-admin-box">
                <h3><?php _e('Submission Statistics', 'guest-post-frontend-submitter'); ?></h3>
                <?php
                $pending_count = wp_count_posts('post')->pending;
                $draft_count = wp_count_posts('post')->draft;
                $published_count = wp_count_posts('post')->publish;
                
                // Count only guest posts (with author meta)
                global $wpdb;
                $guest_posts_count = $wpdb->get_var(
                    "SELECT COUNT(*) FROM {$wpdb->posts} p
                    JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                    WHERE p.post_type = 'post'
                    AND pm.meta_key = '_gpfs_author_name'"
                );
                ?>
                <ul class="gpfs-stats">
                    <li><strong><?php _e('Total Guest Posts:', 'guest-post-frontend-submitter'); ?></strong> <?php echo $guest_posts_count; ?></li>
                    <li><strong><?php _e('Pending Review:', 'guest-post-frontend-submitter'); ?></strong> <?php echo $pending_count; ?></li>
                    <li><strong><?php _e('Drafts:', 'guest-post-frontend-submitter'); ?></strong> <?php echo $draft_count; ?></li>
                </ul>
            </div>
            
            <div class="gpfs-admin-box">
                <h3><?php _e('Quick Links', 'guest-post-frontend-submitter'); ?></h3>
                <ul>
                    <li><a href="<?php echo admin_url('admin.php?page=guest-post-plugin'); ?>"><?php _e('Plugin Settings', 'guest-post-frontend-submitter'); ?></a></li>
                    <li><a href="<?php echo admin_url('edit.php?post_status=pending&post_type=post'); ?>"><?php _e('All Pending Posts', 'guest-post-frontend-submitter'); ?></a></li>
                    <li><a href="<?php echo admin_url('edit.php?post_status=draft&post_type=post'); ?>"><?php _e('All Draft Posts', 'guest-post-frontend-submitter'); ?></a></li>
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
    
    .gpfs-stats {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    .gpfs-stats li {
        margin-bottom: 8px;
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
    }
</style>
