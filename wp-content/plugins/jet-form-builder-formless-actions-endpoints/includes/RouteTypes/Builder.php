<?php

namespace JFB_Formless\RouteTypes;

use Jet_Form_Builder\Blocks\Block_Helper;
use Jet_Form_Builder\Blocks\Module;
use Jet_Form_Builder\Classes\Tools;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use Jet_Form_Builder\Exceptions\Repository_Exception;
use Jet_Form_Builder\Request\Exceptions\Plain_Value_Exception;
use JFB_Formless\Plugin;
use JFB_Formless\Vendor\Auryn\InjectionException;
use JFB_Formless\Vendor\Auryn\Injector;
use JFB_Formless\RouteTypes\Interfaces\RouteInterface;
use JFB_Formless\Adapters\RouteToDatabase;
use JFB_Formless\Services\RouteMeta;
use JFB_Formless\Services\ValidateException;

class Builder {

	const TYPES = array(
		WPAjax::class,
		RestAPI::class,
		URLQuery::class,
	);

	/**
	 * @var RouteToDatabase
	 */
	private $route_to_db;
	/**
	 * @var RouteMeta
	 */
	private $route_meta;

	private $plugin;

	public function __construct(
		Plugin $plugin
	) {
		$this->plugin = $plugin;
	}

	/**
	 * @param int $route_id
	 *
	 * @return RouteInterface
	 * @throws ValidateException|Query_Builder_Exception|InjectionException
	 */
	public function create( int $route_id ): RouteInterface {
		$this->get_route_to_db()->get_route()->set_id( $route_id );
		$this->get_route_to_db()->find();

		$type = $this->create_from_action_type(
			$this->get_route_to_db()->get_route()->get_action_type()
		);

		$type->set_route_meta( $this->get_route_meta() );
		$type->fetch_meta();

		return $type;
	}

	/**
	 * Applying shortcodes, presets for all fields in the body.
	 * Also removes fields from body, which weren't found in the form
	 *
	 * @param array|null $raw_body
	 *
	 * @return void
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function generate_rich_body( $raw_body ): \Generator {
		if ( ! $raw_body || ! is_array( $raw_body ) ) {
			return;
		}
		$form_id = $this->get_route_to_db()->get_route()->get_form_id();

		// for the preset compatibility
		jet_fb_live()->set_form_id( $form_id );
		// clear parsers (fields)
		jet_fb_context()->parsers = array();
		jet_fb_context()->set_parsers( Block_Helper::get_blocks_by_post( $form_id ) );

		foreach ( $raw_body as $field_name => $field_value ) {
			$use_default = is_array( $field_value ) && ! empty( $field_value['use_default'] );

			if ( ! $use_default ) {
				yield $field_name => $field_value;
				continue;
			}

			try {
				$field = jet_fb_context()->resolve_parser( $field_name );
			} catch ( Repository_Exception $exception ) {
				continue;
			} catch ( Plain_Value_Exception $exception ) {
				continue;
			}

			/** @var Module $blocks */
			/** @noinspection PhpUnhandledExceptionInspection */
			$blocks = jet_form_builder()->module( 'blocks' );
			$block  = $blocks->get_field_by_name( $field->get_type() );

			/** @noinspection PhpUnhandledExceptionInspection */
			$block->set_block_data(
				array(
					'name'    => $field->get_setting( 'name' ),
					'default' => $field->get_setting( 'default' ),
				)
			);
			$block->set_preset();

			yield $field_name => ( $block->block_attrs['default'] ?? null );
		}
	}

	/**
	 * @param int $action_type
	 *
	 * @return RouteInterface
	 * @throws InjectionException
	 */
	public function create_from_action_type( int $action_type ): RouteInterface {
		return $this->plugin->get_injector()->make( self::TYPES[ $action_type ] );
	}

	/**
	 * @param RouteToDatabase $route_to_db
	 */
	public function set_route_to_db( RouteToDatabase $route_to_db ) {
		$this->route_to_db = $route_to_db;
	}

	/**
	 * @param RouteMeta $route_meta
	 */
	public function set_route_meta( RouteMeta $route_meta ) {
		$this->route_meta = $route_meta;
	}

	/**
	 * @return RouteToDatabase
	 */
	public function get_route_to_db(): RouteToDatabase {
		return $this->route_to_db;
	}

	/**
	 * @return RouteMeta
	 */
	public function get_route_meta(): RouteMeta {
		return $this->route_meta;
	}

}
