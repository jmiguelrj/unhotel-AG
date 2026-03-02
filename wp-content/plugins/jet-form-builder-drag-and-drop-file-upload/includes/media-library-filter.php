<?php
namespace JFB_Advanced_Media;

// Prevent direct access to the file.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use JFB_Advanced_Media\Upload_Dir_Adapter;

/**
 * Handles media library filtering for the Advanced Media plugin.
 *
 * This class manages filtering of media library content to show
 * only relevant attachments to users.
 *
 * @since 1.0.0
 */
class Media_Library_Filter {

	/**
	 * Initialize media library filter hooks.
	 *
	 * @since 1.0.0
	 */
	public function init(): void {
		add_filter( 'ajax_query_attachments_args', array( $this, 'filter_media_query' ) );
	}

	/**
	 * Filter media library query arguments.
	 *
	 * This method filters the media library to show only relevant attachments
	 * for the current user and context.
	 *
	 * @param array $query WP_Query arguments for attachments.
	 * @return array Modified query arguments.
	 * @since 1.0.0
	 */
	public function filter_media_query( array $query ): array {

		// Administrators always see everything
		if ( current_user_can( 'manage_options' ) ) {
			return $query;
		}

		// Check if this is an Advanced Media field request
		$request_query = isset( $_REQUEST['query'] ) ? (array) sanitize_text_field( wp_unslash( $_REQUEST['query'] ) ) : array();

		$is_advanced_media = false;

		// Check for our marker in different places
		if ( isset( $request_query['jfb_advanced_media'] ) && $request_query['jfb_advanced_media'] ) {
			$is_advanced_media = true;
		}

		// If not an Advanced Media request, don't filter
		if ( ! $is_advanced_media ) {
			return $query;
		}

		// Filter by current user for logged-in users
		if ( is_user_logged_in() ) {
			$query['author'] = get_current_user_id();
		}

		// Handle custom library filtering
		if ( isset( $query['meta_key'] ) && '_jfb_user_hash' === $query['meta_key'] ) {
			unset( $query['meta_key'] );
			unset( $query['meta_value'] );

			$query['meta_query'][] = array(
				'key'   => '_jfb_user_hash',
				'value' => ( new Upload_Dir_Adapter() )->user_dir(),
			);

			return $query;
		}

		// Return query for standard media library
		return $query;
	}
}
