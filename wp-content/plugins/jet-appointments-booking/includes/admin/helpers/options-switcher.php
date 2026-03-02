<?php
namespace JET_APB\Admin\Helpers;

use JET_APB\Plugin;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Options_Switcher {

    /**
	 * Instance.
	 *
	 * Holds the plugin instance.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @var Plugin
	 */
    public static $instance = null;

    private function __construct() {
        add_action( 'admin_init', array( $this, 'same_group_token_switcher' ) );
    }

    /**
	 * Сhecks once if token links macros are used and updates option same_group_token
	 *
	 * @return [type] [description]
	 */
    public function same_group_token_switcher() {

        $option_name = 'jet_apb_token_option_switched';

        if( get_option( $option_name ) ) {
            return;
        }
        
        if( Plugin::instance()->settings->get( 'allow_action_links' ) && 
            ! Plugin::instance()->settings->get( 'multi_booking' ) &&
            Plugin::instance()->settings->get( 'manage_capacity' ) &&
            Plugin::instance()->settings->get( 'allow_manage_count' ) &&
            Plugin::instance()->workflows->collection->to_array()[0]['enabled']
        ) {

            foreach ( Plugin::instance()->workflows->collection->to_array()[0]['items'] as $item ) {
                foreach( $item['actions'] as $actions_args ) {
                    foreach( $actions_args as $value ) {
                        if( preg_match( '/%(confirm_url|cancel_url)%/', $value ) ) {
                            Plugin::instance()->settings->update( 'same_group_token', true, true );
                            break;
                        } 
                    }
                }
            }

        }

        update_option( $option_name, true );

    }

    /**
	 * Instance.
	 *
	 * Ensures only one instance of the plugin class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return Plugin An instance of the class.
	 */
    public static function instance() {

		if ( is_null( self::$instance ) ) {

			self::$instance = new self();

		}

		return self::$instance;
	}

}
