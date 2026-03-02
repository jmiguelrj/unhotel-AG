<?php

namespace JFB_Formless\Modules\Routes;

use Jet_Form_Builder\Admin\Pages\Pages_Manager;
use JFB_Formless\Modules\Routes\AJAX\CheckIsUniqueAjaxEndpoint;
use JFB_Formless\Modules\Routes\Pages\Routes;
use JFB_Formless\Modules\Routes\Pages\RouteSingle;
use JFB_Formless\Modules\Routes\REST\FetchPreset;
use JFB_Formless\Modules\Routes\REST\Protection;
use JFB_Formless\Vendor\Auryn\InjectionException;
use JFB_Formless\Vendor\Auryn\Injector;
use JFB_Formless\Modules\Routes\REST\GetAllFieldsRoute;
use JFB_Formless\Modules\Routes\REST\RequestLogRoute;
use JFB_Formless\Modules\Routes\REST\RequestLogsCountRoute;
use JFB_Formless\Modules\Routes\REST\RequestLogsRoute;
use JFB_Formless\Modules\Routes\REST\RouteRoute;
use JFB_Formless\Modules\Routes\REST\RoutesCountRoute;
use JFB_Formless\Modules\Routes\REST\RoutesRoute;
use JFB_Formless\Plugin;

class Module {

	const REST_NAMESPACE = 'jet-form-builder/v1';

	private $plugin;

	/**
	 * @param Plugin $plugin
	 *
	 * @throws InjectionException
	 */
	public function __construct(
		Plugin $plugin
	) {
		$this->plugin = $plugin;

		add_action( 'rest_api_init', array( $this, 'add_routes' ) );

		add_filter(
			'jet-form-builder/admin/pages',
			array( $this, 'add_admin_pages' )
		);
		add_filter(
			'jet-form-builder/admin/single-pages',
			array( $this, 'add_single_admin_pages' )
		);
		add_action(
			'admin_enqueue_scripts',
			array( $this, 'remove_default_assets' ),
			0
		);

		if ( ! wp_doing_ajax() ) {
			return;
		}
		$this->plugin->get_injector()->make( CheckIsUniqueAjaxEndpoint::class );
	}

	/**
	 * @return void
	 * @throws InjectionException
	 */
	public function add_routes() {
		/** @var RoutesRoute $routes */
		$routes = $this->plugin->get_injector()->make( RoutesRoute::class );
		$routes->register();

		/** @var RouteRoute $route */
		$route = $this->plugin->get_injector()->make( RouteRoute::class );
		$route->set_parent_route( $routes );
		$route->register();

		/** @var RoutesCountRoute $count_routes */
		$count_routes = $this->plugin->get_injector()->make( RoutesCountRoute::class );
		$count_routes->set_parent_route( $routes );
		$count_routes->register();

		/** @var RequestLogsRoute $logs */
		$logs = $this->plugin->get_injector()->make( RequestLogsRoute::class );
		$logs->set_parent_route( $route );
		$logs->register();

		/** @var RequestLogRoute $log */
		$log = $this->plugin->get_injector()->make( RequestLogRoute::class );
		$log->set_parent_route( $routes );
		$log->register();

		/** @var RequestLogsCountRoute $count_routes */
		$count_logs = $this->plugin->get_injector()->make( RequestLogsCountRoute::class );
		$count_logs->set_parent_route( $logs );
		$count_logs->register();

		/** @var GetAllFieldsRoute $fields */
		$fields = $this->plugin->get_injector()->make( GetAllFieldsRoute::class );
		$fields->register();

		/** @var FetchPreset $preset */
		$preset = $this->plugin->get_injector()->make( FetchPreset::class );
		$preset->register();

		/** @var Protection $preset */
		$protect = $this->plugin->get_injector()->make( Protection::class );
		$protect->register();
	}

	public function remove_default_assets() {
		$page = jet_fb_current_page();

		if ( ! $page ||
		     ! in_array( get_class( $page ), array( Routes::class, RouteSingle::class ), true )
		) {
			return;
		}
		remove_action(
			'admin_enqueue_scripts',
			array( Pages_Manager::instance(), 'assets' )
		);
		add_action(
			'admin_enqueue_scripts',
			array( $page, 'assets' )
		);
	}

	/**
	 * @param array $pages
	 *
	 * @return array
	 * @throws InjectionException
	 */
	public function add_admin_pages( array $pages ): array {
		$pages[] = $this->plugin->get_injector()->make( Routes::class );

		return $pages;
	}

	/**
	 * @param array $pages
	 *
	 * @return array
	 * @throws InjectionException
	 */
	public function add_single_admin_pages( array $pages ): array {
		$pages[] = $this->plugin->get_injector()->make( RouteSingle::class );

		return $pages;
	}

	public function get_url( string $url = '' ): string {
		return JFB_FORMLESS_URL . 'modules/Routes/' . $url;
	}

	public function get_path( string $path = '' ): string {
		return JFB_FORMLESS_PATH . 'modules/Routes/' . $path;
	}

}
