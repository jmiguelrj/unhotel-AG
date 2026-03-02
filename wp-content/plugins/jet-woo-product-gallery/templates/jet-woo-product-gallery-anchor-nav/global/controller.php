<?php
/**
 * JetGallery Anchor Nav controller template.
 */
?>
<ul class="jet-woo-product-gallery-anchor-nav-controller">
	<?php
	foreach ( $anchor_nav_controller_ids as $anchor_nav_controller_id ) {
		printf(
			'<li class="controller-item"><a href="#%1$s" data-index="%1$s" data-role="gallery-controller"><span class="controller-item__bullet"></span></a></li>',
			esc_attr( $anchor_nav_controller_id )
		);
	}

	if ( 'popup' === $settings['video_display_in'] && $this->gallery_has_video() ) {
		echo '<li class="controller-item">';
		include $this->get_global_template( 'popup-video' );
		echo '</li>';
	}
	?>
</ul>