<?php
namespace Jet_Reviews\Reviews;

use Jet_Reviews\Reviews\Data as Reviews_Data;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Media {

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
	}

	/**
	 * [load_files description]
	 * @return [type] [description]
	 */
	public function load_files() {}

	/**
	 * @param $review_id
	 * @param $files
	 * @param $prepared_data
	 * @return void
	 */
	public function add_media_for_review( $review_id = false, $files = false, $prepared_data = false ) {
		$this->upload_media( $review_id, $files );
	}

	/**
	 * @param $review_id
	 * @param $files
	 * @param $max_size
	 * @return false|void
	 */
	public function upload_media( $review_id = false, $files = false, $max_size = 5 ) {
		$year  = date( 'Y' );
		$month = date( 'm' );
		$upload_dir = wp_upload_dir();
		$target_dir = $upload_dir['basedir'] . "/jet-reviews-media/$year/$month/";
		$target_url = $upload_dir['baseurl'] . "/jet-reviews-media/$year/$month/";

		if ( ! file_exists( $target_dir ) ) {
			mkdir( $target_dir, 0755, true );
		}

		if ( empty( $files ) ) {
			return false;
		}

		$allowed_types = [ 'image/jpeg', 'image/png', 'image/gif' ];
		$allowed_exts  = [ 'jpg', 'jpeg', 'png', 'gif' ];
		$max_size      = $max_size * 1024 * 1024;

		foreach ( $files['name'] as $key => $name ) {

			if ( $files['error'][ $key ] !== 0 ) continue;

			$tmp_name = $files[ 'tmp_name' ][ $key ];
			$size     = $files[ 'size' ][ $key ];
			$type     = mime_content_type( $tmp_name );
			$ext      = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );

			if ( ! in_array( $type, $allowed_types ) || ! in_array( $ext, $allowed_exts ) || $size > $max_size || false === @getimagesize( $tmp_name ) ) {
				continue;
			}

			$filename    = uniqid( 'jet-review-media-', true ) . '.' . $ext;
			$destination = $target_dir . $filename;
			$public_url  = $target_url . $filename;

			$image = imagecreatefromstring( file_get_contents( $tmp_name ) );

			if ( ! $image ) continue;

			switch ( $type ) {
				case 'image/jpeg': imagejpeg( $image, $destination, 90 ); break;
				case 'image/png':  imagepng( $image, $destination ); break;
				case 'image/gif':  imagegif( $image, $destination ); break;
			}

			imagedestroy( $image );

			$table_name = jet_reviews()->db->tables( 'review_media', 'name' );
			jet_reviews()->db->wpdb()->insert( $table_name, [
				'review_id' => $review_id,
				'media_type' => $type,
				'media_url' => $public_url,
			] );
		}
	}

	/**
	 * @param $review_id
	 * @return mixed
	 */
	public function get_media_by_review_id( $review_id = false ) {
		$table_name = jet_reviews()->db->tables( 'review_media', 'name' );
		$query = jet_reviews()->db->wpdb()->prepare(
			"SELECT * FROM $table_name WHERE review_id = %s ORDER BY id DESC",
			$review_id
		);
		$raw_result = jet_reviews()->db->wpdb()->get_results( $query, ARRAY_A );

		return $raw_result;
	}

	/**
	 * @param $review_id
	 * @return void
	 */
	public function delete_media_by_review_id( $review_id = false ) {
		$table_name = jet_reviews()->db->tables( 'review_media', 'name' );
		$query = jet_reviews()->db->wpdb()->prepare(
			"SELECT * FROM $table_name WHERE review_id = %s ORDER BY id DESC",
			$review_id
		);
		$raw_result = jet_reviews()->db->wpdb()->get_results( $query, ARRAY_A );

		if ( empty( $raw_result ) ) {
			return false;
		}

		foreach ( $raw_result as $key => $media_data ) {
			$file_path = str_replace( wp_get_upload_dir()['baseurl'], wp_get_upload_dir()['basedir'], $media_data['media_url'] );

			if ( ! file_exists( $file_path ) ) {
				continue;
			}

			unlink( $file_path );
		}

		$deleted_media = jet_reviews()->db->wpdb()->delete( $table_name, array( 'review_id' => $review_id ) );

		return true;
	}

	/**
	 * @param $media_ids
	 * @return bool
	 */
	public function delete_media_by_id( $media_ids = [] ) {

		if ( empty( $media_ids ) ) {
			return false;
		}

		$table_name = jet_reviews()->db->tables( 'review_media', 'name' );
		$placeholders = implode(',', array_fill(0, count( $media_ids ), '%d' ) );
		$query = jet_reviews()->db->wpdb()->prepare(
			"SELECT * FROM $table_name WHERE id IN ($placeholders) ORDER BY id DESC",
			...$media_ids
		);
		$raw_result = jet_reviews()->db->wpdb()->get_results( $query, ARRAY_A );

		if ( empty( $raw_result ) ) {
			return false;
		}

		foreach ( $raw_result as $key => $media_data ) {
			$file_path = str_replace( wp_get_upload_dir()['baseurl'], wp_get_upload_dir()['basedir'], $media_data['media_url'] );

			if ( ! file_exists( $file_path ) ) {
				continue;
			}

			unlink( $file_path );
		}

		$query = jet_reviews()->db->wpdb()->prepare(
			"DELETE FROM $table_name WHERE id IN ($placeholders)",
			...$media_ids
		);

		jet_reviews()->db->wpdb()->query( $query );

		return true;
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
