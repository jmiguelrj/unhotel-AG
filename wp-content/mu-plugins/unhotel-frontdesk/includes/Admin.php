<?php
namespace UnhotelFD;

if (!defined('ABSPATH')) exit;

class Admin {
    public static function menu() {
        add_menu_page(
            'Recepção (Unhotel)',
            'Recepção',
            'manage_unhotel_frontdesk',
            'unhotel-frontdesk',
            [__CLASS__,'render'],
            'dashicons-admin-users',
            30
        );
    }

    public static function register_settings() {
        register_setting('unhotel_fd', 'unhotel_fd_reg_status_options', [
            'type' => 'array',
            'sanitize_callback' => [__CLASS__,'sanitize_list']
        ]);
        register_setting('unhotel_fd', 'unhotel_fd_receiver_options', [
            'type' => 'array',
            'sanitize_callback' => [__CLASS__,'sanitize_list']
        ]);
        register_setting('unhotel_fd', 'unhotel_fd_cache_buster', ['type'=>'integer']);
        add_settings_section('unhotel_fd_lists', 'Dropdown lists', function(){}, 'unhotel_fd');

        add_settings_field('reg_status', 'Registration status options', [__CLASS__,'textarea_field'], 'unhotel_fd', 'unhotel_fd_lists', [
            'option' => 'unhotel_fd_reg_status_options',
            'help'   => 'One option per line. Order = display order.'
        ]);
        add_settings_field('receiver', 'Receiver options', [__CLASS__,'textarea_field'], 'unhotel_fd', 'unhotel_fd_lists', [
            'option' => 'unhotel_fd_receiver_options',
            'help'   => 'One option per line (e.g. SELF, PORTARIA, RECEPÇÃO).'
        ]);
    }

    public static function sanitize_list($val) {
        if (is_string($val)) {
            $lines = preg_split('/\r?\n/', $val);
        } elseif (is_array($val)) {
            $lines = $val;
        } else {
            $lines = [];
        }
        $out = [];
        foreach ($lines as $l) {
            $l = trim(wp_strip_all_tags($l));
            if ($l !== '') $out[] = $l;
        }
        return array_values(array_unique($out));
    }

    public static function textarea_field($args) {
        $opt = $args['option'];
        $val = get_option($opt, []);
        if (is_array($val)) $val = implode("\n", $val);
        printf('<textarea name="%s" rows="6" cols="50" class="large-text code">%s</textarea>', esc_attr($opt), esc_textarea($val));
        if (!empty($args['help'])) {
            printf('<p class="description">%s</p>', esc_html($args['help']));
        }
    }

    public static function render() {
        if (!current_user_can('manage_unhotel_frontdesk')) return;
        ?>
        <div class="wrap">
          <h1>Recepção – Settings</h1>
          <p>Configure dropdown lists and cache.</p>

          <form method="post" action="options.php">
            <?php settings_fields('unhotel_fd'); do_settings_sections('unhotel_fd'); submit_button('Save lists'); ?>
          </form>

          <hr/>
          <h2>Cache</h2>
          <p>Bump the cache buster to clear cached responses.</p>
          <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
            <?php wp_nonce_field('unhotel_fd_settings','unhotel_fd_nonce'); ?>
            <input type="hidden" name="action" value="unhotel_fd_clear_cache" />
            <?php submit_button('Clear cache now', 'secondary'); ?>
          </form>
        </div>
        <?php
    }
}

add_action('admin_post_unhotel_fd_clear_cache', function(){
    if (!current_user_can('manage_unhotel_frontdesk')) wp_die('No perms');
    check_admin_referer('unhotel_fd_settings','unhotel_fd_nonce');
    update_option('unhotel_fd_cache_buster', time());
    wp_safe_redirect( menu_page_url('unhotel-frontdesk', false) );
    exit;
});
