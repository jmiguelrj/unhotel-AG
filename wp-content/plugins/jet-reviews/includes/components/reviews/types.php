<?php
namespace Jet_Reviews\Reviews;

use Jet_Reviews\Reviews\Data as Reviews_Data;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Types {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * Constructor for the class
	 */
	function __construct() {
		$this->load_files();

		add_action( 'init', array( $this, 'maybe_convert_settings' ), 9999 );
	}

	/**
	 * [load_files description]
	 * @return [type] [description]
	 */
	public function load_files() {}

	/**
	 * @param $source
	 * @param $source_type
	 * @return void
	 */
	public function get_review_type_slug_by_source_type( $source = false, $source_type = false ) {

		if ( ! $source || ! $source_type ) {
			return false;
		}

		$table_name = jet_reviews()->db->tables( 'review_types', 'name' );

		$query = jet_reviews()->db->wpdb()->prepare(
			"SELECT * FROM $table_name WHERE source = %s AND source_type = %s ORDER BY id DESC",
			$source,
			$source_type
		);

		$raw_result = jet_reviews()->db->wpdb()->get_row( $query, ARRAY_A );

		if ( empty( $raw_result ) ) {
			return false;
		}

		return $raw_result['slug'] ?? false;
	}

	/**
	 * @param $type_slug
	 * @return array|false
	 */
	public function get_review_type_data( $type_slug = false, $only_settings = false ) {
		$type_data = Reviews_Data::get_instance()->get_review_type_by_slug( $type_slug );

		if ( ! $type_data ) {
			return [
				'name' => '',
				'slug' => '',
				'source' => 'post',
				'sourceType' =>  'post',
				'settings' => $this->get_review_type_settings(),
				'fields' => [],
			];
		}

		if ( $only_settings ) {
			return $this->get_review_type_settings( maybe_unserialize( $type_data['settings'] ) );
		}

		return [
			'name' => $type_data['name'] ?? '',
			'slug' => $type_data['slug'] ?? '',
			'source' => $type_data['source'] ?? 'post',
			'sourceType' =>  $type_data['source_type'] ?? 'post',
			'settings' => $this->get_review_type_settings( maybe_unserialize( $type_data['settings'] ) ),
			'fields' => maybe_unserialize( $type_data['fields'] ),
		];
	}

	/**
	 * [get_post_type_data description]
	 * @param  [type] $post_type [description]
	 * @return [type]            [description]
	 */
	public function get_review_type_settings( $settings = false ) {
		$defaults = [
			'source' => 'post',
			'source_type' => 'post',
			'fields' => [],
			'allowed_roles' => [
				'administrator',
				'editor',
				'author',
				'contributor',
				'subscriber',
			],
			'verifications' => [],
			'comment_verifications' => [],
			'need_approve' => false,
			'comments_allowed' => true,
			'comments_need_approve' => false,
			'approval_allowed' => true,
			'upload_media' => false,
			'allowed_media' => [ 'image/jpeg', 'image/png', 'image/gif' ],
			'maxsize_media' => 5,
			'metadata' => false,
			'metadata_rating_key' => '_jet_reviews_average_rating',
			'metadata_ratio_bound' => 5,
			'structuredata' => false,
			'structuredata_type' => 'WebPage',
		];

		if ( ! $settings ) {
			return $defaults;
		}

		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * @return void
	 */
	public function maybe_convert_settings() {
		//delete_option( 'jet-reviews-settings-to-types-converted' );
		$is_converted = get_option( 'jet-reviews-settings-to-types-converted', false );

		if ( $is_converted ) {
			return false;
		}

		update_option( 'jet-reviews-settings-to-types-converted', true );
		$post_types = jet_reviews_tools()->get_post_types();

		foreach ( $post_types as $slug => $name ) {
			$post_type_settings = jet_reviews()->settings->get_post_type_data( $slug );

			if ( ! $post_type_settings['allowed'] ) {
				continue;
			}

			$legacy_type_data = Reviews_Data::get_instance()->get_review_type_by_slug( $post_type_settings['review_type'] );
			$fields = 'default' !== $post_type_settings['review_type'] ? maybe_unserialize( $legacy_type_data['fields'] ) : [];

			$post_type_settings = wp_parse_args( [
				'source' => 'post',
				'source_type' => $slug,
				'fields' => $fields,
			], $post_type_settings );

			$settings = $this->get_review_type_settings( $post_type_settings );
			$review_type_slug = $slug . '-review-type';
			$is_exist = Reviews_Data::get_instance()->is_review_type_exist( $review_type_slug );

			if ( $is_exist ) {
				continue;
			}

			$prepared_data = [
				'name' => $name . ' Review Type',
				'slug' => $review_type_slug,
				'source' => 'post',
				'source_type' => $slug,
				'fields' => ! empty( $fields ) ? maybe_serialize( $fields ) : '',
				'settings' => maybe_serialize( $settings ),
			];

			$insert_id = Reviews_Data::get_instance()->add_new_review_type( $prepared_data );
		}

		// Maybe user source convert
		$user_source = jet_reviews()->settings->get_source_settings_data( 'user', 'wp-user' );

		if ( ! $user_source['allowed'] ) {
			return;
		}

		$legacy_type_data = Reviews_Data::get_instance()->get_review_type_by_slug( $user_source['review_type'] );
		$fields = 'default' !== $post_type_settings['review_type'] ? maybe_unserialize( $legacy_type_data['fields'] ) : [];

		$user_source_settings = wp_parse_args( [
			'source' => 'user',
			'source_type' => 'wp-user',
			'fields' => $fields,
		], $user_source );

		$settings = $this->get_review_type_settings( $user_source_settings );
		$is_exist = Reviews_Data::get_instance()->is_review_type_exist( 'wp-user-review-type' );

		if ( $is_exist ) {
			return;
		}

		$prepared_data = [
			'name' => 'WP User Review Type',
			'slug' => 'wp-user-review-type',
			'source' => 'user',
			'source_type' => 'wp-user',
			'fields' => ! empty( $fields ) ? maybe_serialize( $fields ) : '',
			'settings' => maybe_serialize( $settings ),
		];

		$insert_id = Reviews_Data::get_instance()->add_new_review_type( $prepared_data );
	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
}
