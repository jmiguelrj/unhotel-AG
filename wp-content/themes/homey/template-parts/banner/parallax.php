<?php 
global $post, $homey_prefix;
$page_id = $post->ID;
$image_id = get_post_meta($post->ID, $homey_prefix.'header_image', true);
$img_url = wp_get_attachment_image_src( $image_id, 'full' );
$image = '';
if( $img_url ) {
    $image = esc_url($img_url[0]);
}
?>
<section class="top-banner-wrap <?php homey_banner_fullscreen(); ?>">

	<div class="banner-inner parallax" data-parallax-bg-image="<?php echo $image; ?>"></div><!-- banner-inner parallax -->

	<div class="banner-caption <?php homey_banner_search_class(); ?>">

		<?php 
		homey_banner_search_div_start(); 

		get_template_part('template-parts/banner/caption'); 
    	
    	if(homey_banner_search()) {
    		get_template_part ('template-parts/search/banner-'.homey_banner_search_style()); 
    	}
    	
    	homey_banner_search_div_end(); 
    	?>

	</div><!-- banner-caption -->

</section><!-- header-parallax -->