<?php
/**
 * JetGallery Grid layout content.
 */

if ( 'content' === $video_display_in && $first_place_video ) {
	include $this->get_global_template( 'video' );
}

if ( ! $is_primary_image && $with_featured_image ) {
	if ( has_post_thumbnail( $post_id ) ) {
		include $this->get_global_template( 'image' );
	} else {
		$this->render_placeholder_image();
	}
}

if ( ! empty( $attachment_ids ) ) {
	$visibility = $this->get_grid_visibility( $attachment_ids, $grid_items_count );
	$visible_count = $visibility['visible_count'];
	$hidden_count  = $visibility['hidden_count'];

	$index = 0;
	foreach ( $attachment_ids as $attachment_id ) {
		$index++;

		$vars = $this->prepare_thumbnail_data( $attachment_id, $index, $visible_count, $hidden_count );
		extract( $vars );

		include $this->get_global_template( 'thumbnails' );
	}
}

if ( 'content' === $video_display_in && ! $first_place_video ) {
	include $this->get_global_template( 'video' );
}

if ( 'popup' === $video_display_in ) {
	include $this->get_global_template( 'popup-video' );
}
