<?php
namespace Jet_Reviews\Reviews;

use Jet_Reviews\Admin as Admin;
use Jet_Reviews\Base_Page as Base_Page;
use Jet_Reviews\Reviews\Data as Reviews_Data;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Type_Page extends Base_Page {

	/**
	 * Returns module slug
	 *
	 * @return void
	 */
	public function get_slug() {
		return $this->base_slug . '-type-page';
	}

	/**
	 * [init description]
	 * @return [type] [description]
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'register_page' ), 11 );
	}

	/**
	 * [register_page description]
	 * @return [type] [description]
	 */
	public function register_page() {
		add_submenu_page(
			'',
			esc_html__( 'Review Type', 'jet-reviews' ),
			esc_html__( 'Review Type', 'jet-reviews' ),
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
		include jet_reviews()->get_template( 'admin/pages/reviews/type-page.php' );
	}

	/**
	 * Enqueue module-specific assets
	 *
	 * @return void
	 */
	public function enqueue_module_assets() {

		wp_enqueue_script(
			'jet-reviews-type-page',
			jet_reviews()->plugin_url( 'assets/js/admin/review-type-page.js' ),
			array( 'cx-vue-ui', 'wp-api-fetch' ),
			jet_reviews()->get_version(),
			true
		);

		wp_localize_script( 'jet-reviews-type-page', 'JetReviewsTypePageConfig', $this->localize_config() );

	}

	/**
	 * @return false|string
	 */
	public function get_page_action() {
		return isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : false;
	}

	/**
	 * @return false|string
	 */
	public function get_type_slug() {
		return isset( $_GET['slug'] ) ? sanitize_key( $_GET['slug'] ) : false;
	}

	/**
	 * @return false|string
	 */
	public function get_page_title() {
		$action = $this->get_page_action();
		$page_title = '';

		switch ( $action ) {
			case 'add':
				$page_title = _e( 'Add New Review Type', 'jet-reviews' );
				break;
			case 'edit':
				$page_title = _e( 'Edit Review Type', 'jet-reviews' );
				break;
		}

		return $page_title;
	}

	/**
	 * License page config
	 *
	 * @param  array  $config  [description]
	 * @param  string $subpage [description]
	 * @return [type]          [description]
	 */
	public function localize_config() {
		$review_type_data = jet_reviews()->reviews_manager->types->get_review_type_data( $this->get_type_slug() );

		return [
			'addReviewType' => '/jet-reviews-api/v1/add-review-type',
			'updateReviewType' => '/jet-reviews-api/v1/update-review-type',
			'deleteReviewType' => '/jet-reviews-api/v1/delete-review-type',
			'syncReviewType' => '/jet-reviews-api/v1/sync-rating-data',
			'typesList'    => Reviews_Data::get_instance()->get_review_types_list(),
			'typeSettings' => jet_reviews()->reviews_manager->types->get_review_type_settings(),
			'typeTypeData' => $review_type_data,
			'allRolesOptions' => jet_reviews_tools()->get_roles_options(),
			'verificationOptions' => jet_reviews()->user_manager->get_verification_options(),
			'sourceTypeRawOptions' => jet_reviews()->reviews_manager->sources->get_source_type_options(),
			'structureDataTypesOptions' => jet_reviews_tools()->get_structure_data_types(),
			'allowedMediaOptions' => jet_reviews_tools()->allowed_media_options(),
			'defaultReviewFields' => jet_reviews_tools()->get_default_rating_fields(),
			'action' => $this->get_page_action(),
			'slug' => $this->get_type_slug(),
			'reviewTypesPageUrl' => add_query_arg( [
				'page' => 'jet-reviews-types-page',
			], admin_url( 'admin.php' ) ),
		];
	}
}
