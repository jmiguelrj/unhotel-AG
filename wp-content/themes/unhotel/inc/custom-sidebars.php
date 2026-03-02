<?php
/**
 * Custom Sidebars Manager
 * Allows creating dynamic sidebars from WordPress admin
 *
 * @package Unhotel
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add admin menu for managing sidebars
 */
add_action('admin_menu', 'unhotel_custom_sidebars_menu');
function unhotel_custom_sidebars_menu() {
    add_theme_page(
        'Custom Sidebars',
        'Custom Sidebars',
        'manage_options',
        'unhotel-custom-sidebars',
        'unhotel_custom_sidebars_page'
    );
}

/**
 * Admin page interface
 */
function unhotel_custom_sidebars_page() {
    // Handle sidebar creation
    if (isset($_POST['create_sidebar']) && check_admin_referer('unhotel_create_sidebar')) {
        $sidebar_name = sanitize_text_field($_POST['sidebar_name']);
        $sidebar_id = sanitize_title($_POST['sidebar_name']);
        
        if (!empty($sidebar_name)) {
            $custom_sidebars = get_option('unhotel_custom_sidebars', array());
            $custom_sidebars[$sidebar_id] = $sidebar_name;
            update_option('unhotel_custom_sidebars', $custom_sidebars);
            echo '<div class="notice notice-success"><p>Sidebar "' . esc_html($sidebar_name) . '" created!</p></div>';
        }
    }
    
    // Handle sidebar deletion
    if (isset($_GET['delete']) && check_admin_referer('unhotel_delete_sidebar_' . $_GET['delete'])) {
        $custom_sidebars = get_option('unhotel_custom_sidebars', array());
        $sidebar_name = $custom_sidebars[$_GET['delete']];
        unset($custom_sidebars[$_GET['delete']]);
        update_option('unhotel_custom_sidebars', $custom_sidebars);
        echo '<div class="notice notice-success"><p>Sidebar "' . esc_html($sidebar_name) . '" deleted!</p></div>';
    }
    
    $custom_sidebars = get_option('unhotel_custom_sidebars', array());
    ?>
    <div class="wrap">
        <h1>Custom Sidebars</h1>
        
        <div class="card">
            <h2>Create New Sidebar</h2>
            <form method="post" action="">
                <?php wp_nonce_field('unhotel_create_sidebar'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="sidebar_name">Sidebar Name</label></th>
                        <td>
                            <input type="text" id="sidebar_name" name="sidebar_name" class="regular-text" required>
                            <p class="description">Enter a name for your custom sidebar (e.g., "Product Sidebar", "Blog Sidebar")</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Create Sidebar', 'primary', 'create_sidebar'); ?>
            </form>
        </div>
        
        <h2>Existing Custom Sidebars</h2>
        <?php if (!empty($custom_sidebars)) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Sidebar Name</th>
                        <th>Sidebar ID</th>
                        <th>Shortcode</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($custom_sidebars as $id => $name) : ?>
                        <tr>
                            <td><strong><?php echo esc_html($name); ?></strong></td>
                            <td><code><?php echo esc_html($id); ?></code></td>
                            <td><code>[sidebar id="<?php echo esc_attr($id); ?>"]</code></td>
                            <td>
                                <a href="<?php echo wp_nonce_url(admin_url('themes.php?page=unhotel-custom-sidebars&delete=' . $id), 'unhotel_delete_sidebar_' . $id); ?>" 
                                   class="button button-small" 
                                   onclick="return confirm('Are you sure you want to delete this sidebar?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No custom sidebars created yet.</p>
        <?php endif; ?>
        
        <div class="card" style="margin-top: 20px;">
            <h3>How to Use Custom Sidebars</h3>
            <ol>
                <li>Create a sidebar using the form above</li>
                <li>Go to <strong>Appearance → Widgets</strong> to add widgets to your new sidebar</li>
                <li>Display the sidebar in your templates using: <code>&lt;?php dynamic_sidebar('sidebar-id'); ?&gt;</code></li>
                <li>Or use the shortcode anywhere: <code>[sidebar id="sidebar-id"]</code></li>
            </ol>
        </div>
    </div>
    <?php
}

/**
 * Register custom sidebars
 */
add_action('widgets_init', 'unhotel_register_custom_sidebars', 20);
function unhotel_register_custom_sidebars() {
    $custom_sidebars = get_option('unhotel_custom_sidebars', array());
    
    if (!empty($custom_sidebars)) {
        foreach ($custom_sidebars as $id => $name) {
            register_sidebar(array(
                'name'          => esc_html($name),
                'id'            => $id,
                'description'   => esc_html__('Custom sidebar: ', 'unhotel') . $name,
                'before_widget' => '<section id="%1$s" class="widget %2$s">',
                'after_widget'  => '</section>',
                'before_title'  => '<h2 class="widget-title">',
                'after_title'   => '</h2>',
            ));
        }
    }
}

/**
 * Shortcode to display custom sidebars anywhere
 */
add_shortcode('sidebar', 'unhotel_sidebar_shortcode');
function unhotel_sidebar_shortcode($atts) {
    $atts = shortcode_atts(array('id' => ''), $atts);
    
    if (empty($atts['id']) || !is_active_sidebar($atts['id'])) {
        return '';
    }
    
    ob_start();
    dynamic_sidebar($atts['id']);
    return ob_get_clean();
}