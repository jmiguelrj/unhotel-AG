<?php
namespace UnhotelFD;

if (!defined('ABSPATH')) exit;

class Shortcode {
    public static function init() {
        add_shortcode('unhotel_frontdesk', [__CLASS__, 'render']);
    }

    public static function render($atts) {
        if (!current_user_can('manage_unhotel_frontdesk')) return '<p>No permission.</p>';

        $atts = shortcode_atts([
            'window' => 'today',
            'limit'  => 50
        ], $atts, 'unhotel_frontdesk');

        // enqueue assets
        wp_enqueue_style('unhotel-fd-app', UNHOTEL_FD_URL.'assets/app.css', [], UNHOTEL_FD_VERSION);
        wp_enqueue_script('unhotel-fd-app', UNHOTEL_FD_URL.'assets/app.js', ['wp-i18n'], UNHOTEL_FD_VERSION, true);
        wp_localize_script('unhotel-fd-app', 'UNFD', [
            'rest'   => esc_url_raw( rest_url('unhotel/v1/') ),
            'nonce'  => wp_create_nonce('wp_rest'),
            'window' => $atts['window'],
            'limit'  => (int)$atts['limit'],
        ]);

        ob_start(); ?>
        <div class="unfd-wrap">
          <div class="unfd-toolbar">
            <select id="unfd-window">
              <option value="lastminute">Last Minute</option>
              <option value="today">Hoje</option>
              <option value="tomorrow">Amanhã</option>
              <option value="upcoming">Próximas</option>
            </select>
            <input id="unfd-search" placeholder="Search name/apt/no/phone" />
          </div>
          <div id="unfd-table"></div>
          <div class="unfd-pager"><button id="unfd-prev">Prev</button><span id="unfd-page">1</span><button id="unfd-next">Next</button></div>
        </div>
        <?php
        return ob_get_clean();
    }
}
Shortcode::init();
