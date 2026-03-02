<?php

namespace Jet_Engine_Dynamic_Tables\Bricks_Views;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Manager {
	/**
	 * Bricks render instance
	 *
	 * @var null
	 */
	public $render = null;

	/**
	 * Constructor for the class
	 */
	function __construct() {
		if ( ! $this->has_bricks() ) {
			return;
		}

		add_action( 'init', array( $this, 'register_elements' ), 13 );
		add_filter( 'jet-smart-filters/bricks/allowed-providers', array( $this, 'add_content_provider' ), 10, 3 );

		require_once JET_ENGINE_DYNAMIC_TABLES_PATH . 'includes/bricks-views/render.php';
		$this->render = new Render();
	}

	public function register_elements() {
		if ( ! class_exists('\Jet_Engine\Bricks_Views\Elements\Base') ) {
			return;
		}

		\Bricks\Elements::register_element( JET_ENGINE_DYNAMIC_TABLES_PATH . 'includes/bricks-views/dynamic-table.php' );

		do_action( 'jet-engine/bricks-views/register-elements' );

	}

	public function has_bricks() {
		return defined( 'BRICKS_VERSION' );
	}

	public function add_content_provider( $provider_allowed ) {
		$provider_allowed['jet-data-table'] = true;
		return $provider_allowed;
	}
}