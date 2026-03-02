<?php
$custom_logo = homey_option( 'custom_logo', false, 'url' );
$splash_logo = homey_option( 'custom_logo_splash', false, 'url' );
if(homey_is_transparent_logo()) {
	$custom_logo = $splash_logo;
}
?>

<a class="homey_logo" href="<?php echo esc_url(home_url('/')); ?>">
	<?php if( !empty( $custom_logo ) ) { ?>
		<img src="<?php echo esc_url( $custom_logo ); ?>" alt="<?php bloginfo( 'name' );?>" title="<?php bloginfo( 'name' ); ?> - <?php bloginfo( 'description' ); ?>">
	<?php } else {
		echo '<h1>';
		bloginfo( 'name' );
		echo '</h1>';
	} ?>
</a>
