<?php

namespace JFB_Formless\Modules\BlockEditor;

use JFB_Formless\Plugin;
use JFB_Formless\RouteTypes\Builder;
use JFB_Formless\RouteTypes\Interfaces\RouteInterface;
use JFB_Formless\Services;
use JFB_Formless\Adapters;
use JFB_Formless\Modules\FrontComponents;

class Module {

	private $routes_list;
	private $builder;

	public function __construct(
		Services\RoutesList $routes_list,
		Builder $builder,
		Services\Route $route,
		Services\RouteMeta $route_meta,
		Adapters\RouteToDatabase $route_to_database
	) {
		$this->routes_list = $routes_list;
		$this->builder     = $builder;

		$route_meta->set_route( $route );
		$route_to_database->set_route( $route );
		$this->builder->set_route_meta( $route_meta );
		$this->builder->set_route_to_db( $route_to_database );

		// controls for Button block
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ), 11 );
		add_filter( 'render_block_core/button', array( $this, 'on_button_render' ), 10, 2 );
	}

	public function enqueue_editor_assets() {
		$script_url   = $this->get_url( 'assets/build/editor.js' );
		$script_asset = require_once $this->get_path( 'assets/build/editor.asset.php' );

		wp_enqueue_script(
			Plugin::SLUG . '-block-editor',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_localize_script(
			Plugin::SLUG . '-block-editor',
			'JetFBSubmitEndpointConfig',
			array(
				'items' => $this->routes_list->get_list(),
			)
		);
	}

	public function on_button_render( string $block_content, array $parsed_block ): string {
		$route_attrs = $parsed_block['attrs']['jfbSubmitEndpoint'] ?? array();

		if ( empty( $route_attrs['enabled'] ) ) {
			return $block_content;
		}

		try {
			$route_type = $this->builder->create( $route_attrs['routeId'] ?? 0 );
			$route_type->set_body( $route_attrs['request'] ?? '' );
			$route_type->set_body(
				iterator_to_array( $this->builder->generate_rich_body( $route_type->get_body() ) )
			);
		} catch ( \Exception $exception ) {
			return $block_content;
		}

		$this->enqueue_frontend_assets();
		$html = new \WP_HTML_Tag_Processor( $block_content );
		$html->next_tag( 'a' );
		$html->set_attribute(
			RouteInterface::ATTRIBUTE,
			wp_json_encode( iterator_to_array( $route_type->generate_attributes() ) )
		);

		return $html->get_updated_html();
	}

	public function enqueue_frontend_assets() {
		do_action( 'jet-form-builder-formless/frontend-assets' );
		$script_asset = require_once $this->get_path( 'assets/build/frontend.asset.php' );

		// if it had already executed before
		if ( true === $script_asset ) {
			return;
		}

		$script_url = $this->get_url( 'assets/build/frontend.js' );

		array_push(
			$script_asset['dependencies'],
			FrontComponents\Module::HANDLE_REDIRECT,
			FrontComponents\Module::HANDLE
		);

		wp_enqueue_script(
			Plugin::SLUG . '-block-editor-front',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		wp_enqueue_style( FrontComponents\Module::HANDLE );
	}


	public function get_url( string $url = '' ): string {
		return JFB_FORMLESS_URL . 'modules/BlockEditor/' . $url;
	}

	public function get_path( string $path = '' ): string {
		return JFB_FORMLESS_PATH . 'modules/BlockEditor/' . $path;
	}

}
