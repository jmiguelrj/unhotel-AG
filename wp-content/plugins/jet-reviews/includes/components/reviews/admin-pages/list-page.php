<?php
namespace Jet_Reviews\Reviews;

use Jet_Reviews\Admin as Admin;
use Jet_Reviews\Base_Page as Base_Page;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class List_Page extends Base_Page {

	/**
	 * Returns module slug
	 *
	 * @return void
	 */
	public function get_slug() {
		return $this->base_slug . '-list-page';
	}

	/**
	 * [get_current_page description]
	 * @return [type] [description]
	 */
	public function get_current_page() {

		if ( isset( $_REQUEST['current_page'] ) ) {
			return $_REQUEST['current_page'];
		}

		return 1;
	}

	/**
	 * [get_per_page_value description]
	 * @return [type] [description]
	 */
	public function get_page_size() {

		if ( isset( $_REQUEST['page_size'] ) ) {
			return $_REQUEST['page_size'];
		}

		return 20;
	}

	/**
	 * [init description]
	 * @return [type] [description]
	 */
	public function init() {
		add_action( 'admin_menu', [ $this, 'register_page' ], 11 );
		add_action( 'admin_footer', [ $this, 'print_vue_templates' ], 998 );
	}

	/**
	 * [register_page description]
	 * @return [type] [description]
	 */
	public function register_page() {

		add_submenu_page(
			Admin::get_instance()->admin_page_slug,
			esc_html__( 'All Reviews', 'jet-reviews' ),
			esc_html__( 'All Reviews', 'jet-reviews' ),
			'manage_options',
			$this->get_slug(),
			array( $this, 'render_page' )
		);

	}

	/**
	 * [render_page description]
	 * @return [type] [description]
	 */
	public function render_page() {
		include jet_reviews()->get_template( 'admin/pages/reviews/list-page.php' );
	}

	/**
	 * [print_vue_templates description]
	 * @return [type] [description]
	 */
	public function print_vue_templates() {

		$map = [
			'export-reviews-form',
			'import-reviews-form',
		];

		foreach ( glob( jet_reviews()->plugin_path( 'templates/admin/vue-templates/' )  . '*.php' ) as $file ) {
			$name = basename( $file, '.php' );

			if ( ! in_array( $name,  $map ) ) {
				continue;
			}

			ob_start();
			include $file;
			printf( '<script type="x-template" id="tmpl-jet-reviews-%1$s">%2$s</script>', $name, ob_get_clean() );
		}

	}

	/**
	 * Enqueue module-specific assets
	 *
	 * @return void
	 */
	public function enqueue_module_assets() {

		wp_enqueue_script(
			'jet-reviews-list-page',
			jet_reviews()->plugin_url( 'assets/js/admin/review-list-page.js' ),
			array( 'cx-vue-ui', 'wp-api-fetch' ),
			jet_reviews()->get_version(),
			true
		);

		wp_localize_script( 'jet-reviews-list-page', 'JetReviewsListPageConfig', $this->localize_config() );

	}

	/**
	 * License page config
	 *
	 * @param  array  $config  [description]
	 * @param  string $subpage [description]
	 * @return [type]          [description]
	 */
	public function localize_config() {

		$current_page = $this->get_current_page();
		$page_size = $this->get_page_size();

		$raw_export_reviews_url = add_query_arg( [ 'action' => 'jet_reviews_export_reviews_action' ], admin_url( 'admin-ajax.php' ) );

		$config = array(
			'getReviewsRoute'          => '/jet-reviews-api/v1/get-admin-reviews-list',
			'updateReviewRoute'        => '/jet-reviews-api/v1/update-review',
			'deleteReviewRoute'        => '/jet-reviews-api/v1/delete-review',
			'deleteReviewMediaRoute'   => '/jet-reviews-api/v1/delete-review-media',
			'toggleReviewApproveRoute' => '/jet-reviews-api/v1/toggle-review-approve',
			'exportReviewsRoute'       => '/jet-reviews-api/v1/export-reviews',
			'importReviewsRoute'       => '/jet-reviews-api/v1/import-reviews',
			'rawExportReviewsUrl'      => add_query_arg( [
					'action' => 'jet_reviews_export_reviews_action'
				],
				admin_url( 'admin-ajax.php' )
			),
			'reviewsList'              => [],
			'currentPage'              => $current_page,
			'pageSize'                 => $page_size,
			'reviewsCount'             => 0,
			'sourceTypeOptions'        => jet_reviews()->reviews_manager->sources->get_source_type_options(),
			'postTypeOptions'          => jet_reviews_tools()->get_post_types_options(),
			'commentsPageUrl'          => add_query_arg( array( 'page' => 'jet-reviews-comments-list-page' ), admin_url( 'admin.php' ) ),
		);


		return $config;

	}
}
