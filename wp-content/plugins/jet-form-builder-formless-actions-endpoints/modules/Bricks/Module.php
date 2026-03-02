<?php

namespace JFB_Formless\Modules\Bricks;

use Bricks\Element;
use JFB_Formless\Adapters\RouteToDatabase;
use JFB_Formless\RouteTypes\Builder;
use JFB_Formless\RouteTypes\Interfaces\RouteInterface;
use JFB_Formless\Services\Route;
use JFB_Formless\Services\RouteMeta;
use JFB_Formless\Services\RoutesList;
use JFB_Formless\Modules\BlockEditor;

class Module {

	const GROUP = 'jfb_submit';

	const TOGGLE_NAME  = 'jfb_submit__enabled';
	const ROUTE_NAME   = 'jfb_submit__route';
	const REQUEST_NAME = 'jfb_submit__request';

	private $routes_list;
	private $builder;
	private $block_editor_module;

	public function __construct(
		RoutesList $routes_list,
		Builder $builder,
		Route $route,
		RouteMeta $route_meta,
		RouteToDatabase $route_to_database,
		BlockEditor\Module $block_editor_module
	) {
		$this->routes_list         = $routes_list;
		$this->builder             = $builder;
		$this->block_editor_module = $block_editor_module;

		$this->routes_list->set_format_list( false );
		$route_meta->set_route( $route );
		$route_to_database->set_route( $route );
		$this->builder->set_route_meta( $route_meta );
		$this->builder->set_route_to_db( $route_to_database );

		add_filter(
			'bricks/elements/button/control_groups',
			array( $this, 'handle_button_control_groups' )
		);
		add_filter(
			'bricks/elements/button/controls',
			array( $this, 'handle_button_controls' )
		);
		add_filter(
			'bricks/element/render_attributes',
			array( $this, 'element_render' ),
			10,
			3
		);
	}

	public function element_render( $attributes, $key, Element $element ) {
		if (
			'core/button' !== $element->block ||
			'_root' !== $key ||
			empty( $element->settings[ self::TOGGLE_NAME ] )
		) {
			return $attributes;
		}
		try {
			$route_type = $this->builder->create( (int) ( $element->settings[ self::ROUTE_NAME ] ?? 0 ) );
			$route_type->set_body( $element->settings[ self::REQUEST_NAME ] ?? '' );
			$route_type->set_body(
				iterator_to_array( $this->builder->generate_rich_body( $route_type->get_body() ) )
			);
		} catch ( \Exception $exception ) {
			return $attributes;
		}
		$this->block_editor_module->enqueue_frontend_assets();
		$attributes[ $key ][ RouteInterface::ATTRIBUTE ] = wp_json_encode(
			iterator_to_array( $route_type->generate_attributes() )
		);

		return $attributes;
	}

	public function handle_button_control_groups( array $control_groups ): array {
		$control_groups[ self::GROUP ] = array(
			'tab'   => 'content', // or 'style'
			'title' => __( 'Submit JetFormBuilder form', 'jet-form-builder-formless-actions-endpoints' ),
		);

		return $control_groups;
	}

	public function handle_button_controls( array $controls ): array {
		$routes = $this->routes_list->get_list();

		if ( ! $routes ) {
			return $controls;
		}

		$controls[ self::TOGGLE_NAME ] = array(
			'tab'    => 'content',
			'group'  => self::GROUP,
			'label'  => __( 'Enable form submission', 'jet-form-builder-formless-actions-endpoints' ),
			'type'   => 'checkbox',
			'inline' => true,
			'small'  => true,
		);

		$controls[ self::ROUTE_NAME ] = array(
			'tab'         => 'content',
			'group'       => self::GROUP,
			'label'       => __( 'Choose route', 'jet-form-builder-formless-actions-endpoints' ),
			'type'        => 'select',
			'options'     => $routes,
			'inline'      => true,
			'placeholder' => __( '-- Select route --', 'jet-form-builder-formless-actions-endpoints' ),
			'required'    => array( self::TOGGLE_NAME, '=', true ),
		);

		$controls[ self::REQUEST_NAME ] = array(
			'tab'           => 'content',
			'group'         => self::GROUP,
			'label'         => __( 'Data for the request', 'jet-form-builder-formless-actions-endpoints' ),
			'type'          => 'textarea',
			'rows'          => 8,
			'inlineEditing' => true,
			'required'      => array( self::TOGGLE_NAME, '=', true ),
			'description'   => __(
				'Here should be a JSON object. It is better to copy it from on JetFormBuilder -> Routes page, to avoid invalid JSON',
				'jet-form-builder-formless-actions-endpoints'
			),
		);

		return $controls;
	}

}
