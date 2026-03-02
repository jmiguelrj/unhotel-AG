<?php
/**
 * Template Name: Reset Password
 *
 */
if ( is_user_logged_in() ) {
	wp_redirect( home_url() );
}
get_header();

//get_template_part('template-parts/page-title');

$rp_key = '';
$rp_login = '';
$resetpass = false;

if ( isset( $_REQUEST['key'] ) && !empty( $_REQUEST['key'] ) ) :

	$rp_key = $_REQUEST['key'];

endif;

if ( isset( $_REQUEST['login'] ) && !empty( $_REQUEST['login'] ) ) :

	$rp_login = $_REQUEST['login'];

endif;

if ( !empty( $rp_key ) && !empty( $rp_login ) ) :

	$resetpass = true;

endif;

?>
<section class="main-content-area listing-page listing-page-full-width">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">

            	<div id="password_reset_msgs" class="homey_messages message"></div>

		        <?php if ( $rp_login == 'invalidkey' ) : $resetpass = false; ?>
					<div class="alert alert-danger" role="alert"> <?php esc_html_e('Oops something went wrong.', 'homey'); ?>  </div>
					<div class="login-register-title text-center">
		                <p class="text-danger"> <?php esc_html_e('Oops something went wrong.', 'homey'); ?> </p>
		            </div>
				<?php endif; ?>
				<?php if ( $rp_login == 'expiredkey' ) : $resetpass = false; ?>
		        	<div class="login-register-title text-center">
		                <p class="text-danger"> <?php esc_html_e('Session key expired.', 'homey'); ?> </p>
		            </div>
				<?php endif; ?>
                
                <div class="page-wrap">
                    <div class="reset-password">
                        <h1 class="page-title"><?php echo esc_html__(the_title('', '', false), 'homey'); ?></h1>
                        
                        <?php if ( $resetpass ) : ?>
			            <form action="#" method="post" autocomplete="off">
				            <input type="hidden" name="rp_login" value="<?php echo esc_attr($rp_login); ?>" autocomplete="off" />
							<input type="hidden" name="rp_key" value="<?php echo esc_attr($rp_key); ?>" />
							<?php wp_nonce_field( 'homey_resetpassword_nonce', 'homey_resetpassword_security' ); ?>
			                <div class="form-group">
			                    <input type="password" id="new_password" name="new_password" class="form-control" placeholder="<?php esc_html_e('New Password', 'homey'); ?>">
			                </div>
			                <button type="submit" id="homey_reset_password" class="btn btn-primary btn-block"><?php esc_html_e('Reset Password', 'homey'); ?></button>
			               
			            </form>
			        <?php endif; ?>

                    </div>   
                </div>

            </div><!-- col-xs-12 col-sm-12 col-md-8 col-lg-8 -->
        </div><!-- .row -->
    </div>   <!-- .container -->
</section><!-- main-content-area listing-page grid-listing-page -->
<?php
get_footer();
