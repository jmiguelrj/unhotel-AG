<?php

namespace JFB_Formless\Modules\Elementor;

use Elementor\Controls_Stack;
use Elementor\Widget_Base;
use JFB_Formless\Plugin;
use JFB_Formless\RouteTypes\Builder;
use JFB_Formless\RouteTypes\Interfaces\RouteInterface;
use JFB_Formless\Services\RoutesList;
use JFB_Formless\Services\Route;
use JFB_Formless\Services\RouteMeta;
use JFB_Formless\Adapters\RouteToDatabase;
use JFB_Formless\Modules\FrontComponents;

class Module {

	private $routes_list;
	private $builder;

	const TOGGLE_NAME  = 'jfb_submit__enabled';
	const ROUTE_NAME   = 'jfb_submit__route';
	const REQUEST_NAME = 'jfb_submit__request';

	public function __construct(
		RoutesList $routes_list,
		Builder $builder,
		Route $route,
		RouteMeta $route_meta,
		RouteToDatabase $route_to_database
	) {
		$this->routes_list = $routes_list;
		$this->routes_list->set_format_list( false );
		$this->builder = $builder;

		$route_meta->set_route( $route );
		$route_to_database->set_route( $route );
		$this->builder->set_route_meta( $route_meta );
		$this->builder->set_route_to_db( $route_to_database );

		// core button compatibility
		add_action(
			'elementor/element/button/section_button/after_section_end',
			array( $this, 'add_route_settings' )
		);
		// jet-elements compatibility
		add_action(
			'elementor/element/jet-button/section_settings/after_section_end',
			array( $this, 'add_route_settings' )
		);
		add_filter(
			'elementor/widget/render_content',
			array( $this, 'handle_render_widget' ),
			10,
			2
		);
	}

	/**
	 * @param Controls_Stack $element The element type.
	 */
	public function add_route_settings( $element ) {
		$routes = $this->routes_list->get_list();

		if ( ! $routes ) {
			return;
		}

		$element->start_controls_section(
			'jfb_submit_settings',
			array(
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
				'label' => __( 'Submit JetFormBuilder Form', 'jet-form-builder-formless-actions-endpoints' ),
			)
		);

		$element->add_control(
			self::TOGGLE_NAME,
			array(
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'label'   => __( 'Enable form submission', 'jet-form-builder-formless-actions-endpoints' ),
				'default' => '',
			)
		);
		$element->add_control(
			self::ROUTE_NAME,
			array(
				'type'      => \Elementor\Controls_Manager::SELECT,
				'label'     => __( 'Choose route', 'jet-form-builder-formless-actions-endpoints' ),
				'options'   => $routes,
				'condition' => array(
					self::TOGGLE_NAME => 'yes',
				),
			)
		);
		$element->add_control(
			self::REQUEST_NAME,
			array(
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'label'       => __( 'Data for the request', 'jet-form-builder-formless-actions-endpoints' ),
				'condition'   => array(
					self::TOGGLE_NAME => 'yes',
				),
				'description' => __(
					'Here should be a JSON object. It is better to copy it from on JetFormBuilder -> Routes page, to avoid invalid JSON',
					'jet-form-builder-formless-actions-endpoints'
				),
			)
		);

		$element->end_controls_section();
	}

	/**
	 * Filters heading widgets and change their content.
	 *
	 * @param string $content
	 * @param Widget_Base $widget The widget instance.
	 *
	 * @return string The changed widget content.
	 * @since 1.0.0
	 */
	public function handle_render_widget( string $content, $widget ): string {
		if ( ! in_array( $widget->get_name(), array( 'button', 'jet-button' ), true ) ||
		     'yes' !== $widget->get_settings( self::TOGGLE_NAME )
		) {
			return $content;
		}

		try {
			$route_type = $this->builder->create( (int) $widget->get_settings( self::ROUTE_NAME ) );
			$route_type->set_body( $widget->get_settings( self::REQUEST_NAME ) );
			$route_type->set_body(
				iterator_to_array( $this->builder->generate_rich_body( $route_type->get_body() ) )
			);
		} catch ( \Exception $exception ) {
			return $content;
		}

		$this->enqueue_frontend_assets();
		$html = new \WP_HTML_Tag_Processor( $content );
		$html->next_tag( 'a' );
		$html->set_attribute(
			RouteInterface::ATTRIBUTE,
			wp_json_encode(
				iterator_to_array( $route_type->generate_attributes() )
			)
		);

		return $html->get_updated_html();
	}

	private function enqueue_frontend_assets() {
		do_action( 'jet-form-builder-formless/frontend-assets' );
		$script_asset = require_once $this->get_path( 'assets/build/index.asset.php' );

		// if it had already executed before
		if ( true === $script_asset ) {
			return;
		}

		$script_url = $this->get_url( 'assets/build/index.js' );

		array_push(
			$script_asset['dependencies'],
			FrontComponents\Module::HANDLE_REDIRECT,
			FrontComponents\Module::HANDLE
		);

		wp_enqueue_script(
			Plugin::SLUG . '-elementor',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		wp_enqueue_style( FrontComponents\Module::HANDLE );
	}

	public function get_url( string $url = '' ): string {
		return JFB_FORMLESS_URL . 'modules/Elementor/' . $url;
	}

	public function get_path( string $path = '' ): string {
		return JFB_FORMLESS_PATH . 'modules/Elementor/' . $path;
	}

}
