<?php
/**
 * The template for displaying the footer
 *
 * Contains footer content.
 *
 * @package Homey
 * @since Homey 1.0
 */
 ?>
</div> <!-- End #section-body -->
<?php
if( !homey_is_dashboard_footer() && !homey_is_halfmap_page() && !homey_is_search_page()) {

	if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) {
		get_template_part('template-parts/footer/footer');
	}
} elseif(homey_is_splash()) {
	get_template_part('template-parts/footer/dashboard-footer');
}
?>

<?php get_template_part('template-parts/search/overlay-mobile-search'); ?>
<?php get_template_part('template-parts/modal-window-login'); ?>
<?php get_template_part('template-parts/modal-window-register');?>
<?php get_template_part('template-parts/modal-window-forgot-password');?>
<?php get_template_part('template-parts/listing/compare'); ?>

<?php wp_footer(); ?>

<?php if(isset($_POST['is_login'])){ ?>
<script>
    jQuery("input[name='_wp_http_referer']").val('<?php echo $_POST["referer_link"]; ?>');
    jQuery(".homey_login_messages").html('<p class="error text-danger"><?php echo esc_html__("Please login to visit the link.", 'homey') ?>.</p>');
    jQuery('#modal-login').modal('show');
</script>
<?php } ?>

</body>
</html>