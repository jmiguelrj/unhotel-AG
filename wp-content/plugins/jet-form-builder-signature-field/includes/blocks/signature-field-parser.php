<?php
namespace JFB_Signature_Field\Blocks;

use JFB_Signature_Field\Upload_Dir_Adapter;
use Jet_Form_Builder\Classes\Resources\Has_Error_File;
use Jet_Form_Builder\Classes\Resources\Upload_Exception;
use JFB_Modules\Block_Parsers\Field_Data_Parser;
use Jet_Form_Builder\Classes\Resources\Uploaded_File;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Signature_Field_Parser extends Field_Data_Parser {

	public function type() {
		return 'signature-field';
	}

	/**
	 * Prepare response
	 *
	 * @return false|string|null
	 */
	public function get_response() {

		/*
			Unsure if required for this case.

			@see https://github.com/Crocoblock/issues-tracker/issues/4422

			if (
				empty( $this->get_file() ) ||
				(
					is_object( $this->get_file() ) &&
					is_a( $this->get_file(), Has_Error_File::class ) &&
					$this->get_file()->has_error()
				)
			) {
				// See the media field parser
				return $this->value;
			}
		*/

		$image_format = ! empty( $this->settings['image_format'] ) ? $this->settings['image_format'] : 'png';
		$storage_type = ! empty( $this->settings['storage_type'] ) ? $this->settings['storage_type'] : 'folder';

		if ( ! $this->get_value() || 'data_url' === $storage_type ) {
			return $this->get_value();
		}

		$file_content = false;

		if ( preg_match('/^data:(.*?);base64,(.*)$/', $this->get_value(), $matches ) ) {
			$mime_type   = $matches[1];
			$base64_data = $matches[2];
			$file_content = base64_decode( $base64_data );
			// sanitize SVG before upload
		}

		if ( ! $file_content ) {
			return $this->get_value();
		}

		$adapter = new Upload_Dir_Adapter();
		$adapter->apply_upload_dir();

		$filename = ! empty( $this->settings['name'] ) ? sanitize_file_name( $this->settings['name'] ) : 'signature';

		switch ( $image_format ) {
			case 'svg':
			case 'svg_filled':
				$filename .= '.svg';
				break;

			case 'jpg':
				$filename .= '.jpg';
				break;

			case 'png':
			case 'png_filled':
			default:
				$filename .= '.png';
				break;
		}

		$file = wp_upload_bits(
			$filename,
			null,
			$file_content
		);

		if ( $file ) {
			$uploaded_file = new Uploaded_File();
			$uploaded_file = $uploaded_file->set_from_array( $file );
		}

		if ( 'attachment' === $storage_type ) {
			$uploaded_file->add_attachment();
		}

		$adapter->remove_apply_upload_dir();

		$this->set_file( $uploaded_file );

		if ( $this->need_delete_file_on_error() ) {
			add_filter( 'jet-fb/response-handler/query-args', array( $this, 'delete_file_on_error' ), 0, 2 );
		}

		return $uploaded_file->get_url();
	}

	/**
	 * Check if we need to delete file on form error
	 *
	 * @return boolean
	 */
	public function need_delete_file_on_error() {
		// By default the option is enabled, in $this->settings only field options that was changed,
		// so if delete_file_on_error not presented in the settings - we need to use default value.
		if ( ! isset( $this->settings['delete_file_on_error'] ) ) {
			return true;
		}

		return true === $this->settings['delete_file_on_error'];
	}

	/**
	 * Check if form submission wasn't successfull and delete file if allowed
	 *
	 * @return array
	 */
	public function delete_file_on_error( $query_args, $response_handler ) {

		if ( 'success' !== $response_handler->args['status'] ) {
			$file = $this->get_file();
			if ( $file && $file->get_attachment_id() ) {
				wp_delete_attachment( $file->get_attachment_id(), true );
			} elseif ( $file && $file->get_file() ) {
				$real_file = realpath( wp_normalize_path( $file->get_file() ) );
				wp_delete_file( $real_file );
			}
		}

		return $query_args;
	}
}
