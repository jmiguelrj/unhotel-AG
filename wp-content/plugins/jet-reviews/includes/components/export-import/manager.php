<?php
namespace Jet_Reviews\Export_Import;

// If this file is called directly, abort.
use Jet_Reviews\Reviews\Data as Reviews_Data;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Compatibility Manager
 */
class Manager {

	/**
	 * @var string
	 */
	public $export_reviews_hook = 'jet_reviews_export_reviews_action';

	/**
	 * [load_files description]
	 * @return [type] [description]
	 */
	public function load_files() {}

	/**
	 * @return void
	 */
	public function export_reviews_action() {

		if ( ! isset( $_GET['action'] ) ) {
			return;
		}

		if ( 'jet_reviews_export_reviews_action' !== $_GET['action'] || ! isset( $_GET['source'] ) || ! isset( $_GET['sourceType'] ) ) {
			return;
		}

		$items = Reviews_Data::get_instance()->get_reviews_items_by_source( $_GET['source'], $_GET['sourceType'] );
		$this->send_items( $items );
	}

	/**
	 * @param $items
	 * @param $content_type
	 *
	 * @return void
	 */
	public function send_items( $items = array(), $content_type = null ) {

		$filename = 'export-jet-reviews-' . date( 'd-m-Y' ) . '.csv';
		$headers = [
			'source',
			'post_type',
			'post_id',
			'author',
			'date',
			'title',
			'content',
			'type_slug',
			'rating_data',
			'rating',
			'likes',
			'dislikes',
			'approved',
			'pinned',
		];

		$separator = apply_filters( 'jet-reviews/export-reviews/cvs-separator', ',' );

		$file = implode( $separator, $headers ) . PHP_EOL;

		foreach ( $items as $item ) {

			$preapred_item = array();

			foreach ( $headers as $key ) {
				$value = isset( $item[ $key ] ) ? $item[ $key ] : '';

				if ( $value ) {
					// Escaping a double quote.
					$value = str_replace( '"', '""', $value );

				} elseif ( is_array( $value ) && empty( $value ) ) {
					$value = null;
				}

				$preapred_item[] = $value;
			}

			$file .= '"' . implode( '"' . $separator . '"', $preapred_item ) . '"' . PHP_EOL;
		}

		$this->file_download( $filename, $file, 'text/csv' );

	}

	/**
	 * Process
	 * @param  [type] $filename [description]
	 * @param  string $file     [description]
	 * @return [type]           [description]
	 */
	public static function file_download( $filename = null, $file = '', $type = 'application/json' ) {

		if ( false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) ) {
			set_time_limit( 0 );
		}

		@session_write_close();

		if( function_exists( 'apache_setenv' ) ) {
			$variable = 'no-gzip';
			$value = 1;
			@apache_setenv($variable, $value);
		}

		@ini_set( 'zlib.output_compression', 'Off' );

		nocache_headers();

		$file = apply_filters( 'jet-reviews/export-reviews/file-download', $file, $type, $filename );

		header( "Robots: none" );
		header( "Content-Type: " . $type );
		header( "Content-Description: File Transfer" );
		header( "Content-Disposition: attachment; filename=\"" . $filename . "\";" );
		header( "Content-Transfer-Encoding: binary" );

		// Set the file size header
		header( "Content-Length: " . strlen( $file ) );

		echo $file;
		die();
	}

	/**
	 * [import_popup_preset description]
	 * @return [type] [description]
	 */
	public function parse_import_file_action() {

		if ( ! current_user_can( 'import' ) ) {
			wp_send_json_error( [
				'message' => __( 'You don\'t have permissions to do this', 'jet-reviews' ),
			] );
		}

		if ( empty( $_FILES['_file'] ) ) {
			wp_send_json_error( [
				'message' => __( 'File not passed', 'jet-reviews' ),
			] );
		}

		$file = $_FILES['_file'];

		if ( 'text/csv' !== $file['type'] ) {
			wp_send_json_error( [
				'message' => __( 'Format not allowed', 'jet-reviews' ),
			] );
		}

		$handle = fopen( $file['tmp_name'], 'r' );
		$row = 1;
		$headers = [];
		$raw_parsed_data = [];

		if ( ! empty( $handle ) ) {

			while ( FALSE !== ( $data = fgetcsv( $handle, 1000, ',' ) ) ) {
				$length = count( $data );

				$fields_data = [];

				for ( $field = 0; $field < $length; $field++ ) {

					if ( 1 == $row ) {
						$headers[] = $data[ $field ];
					} else {
						$fields_data[] = $data[ $field ];
					}
				}

				if ( ! empty( $headers ) && ! empty( $fields_data ) ) {
					$row_data = [];

					foreach ( $headers as $key => $header_item ) {
						$row_data[ $header_item ] = $fields_data[ $key ];
					}

					$raw_parsed_data[] = $row_data;
				}

				$row++;
			}

			fclose( $handle );
		} else {
			wp_send_json_error( [
				'message'       => __( 'No data found in file', 'jet-reviews' ),
			] );
		}

		$raw_parsed_data = apply_filters( 'jet-reviews/import-reviews/raw-parsed-data', $raw_parsed_data, $headers );
		$headers = apply_filters( 'jet-reviews/import-reviews/raw-parsed-headers', $headers );

		wp_send_json_success( [
			'headers'       => $headers,
			'rawParsedData' => $raw_parsed_data,
		] );
	}

	/**
	 * [__construct description]
	 */
	public function __construct() {
		$this->load_files();

		add_action( 'admin_init', [ $this, 'export_reviews_action' ] );
		add_action( 'wp_ajax_jet_reviews_parse_import_file', [ $this, 'parse_import_file_action' ] );
	}

}
