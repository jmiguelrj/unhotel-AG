<?php
global $homey_local;
$nav_register = homey_option('nav_register');
$login_image = homey_option('login_image', false, 'url' );
$login_text = esc_html__(homey_option('login_text'), 'homey');
$facebook_login = homey_option('facebook_login');
$google_login = homey_option('google_login');
?>
<div class="modal fade custom-modal-login" id="modal-login" tabindex="-1" role="dialog">
    <div class="modal-dialog clearfix" role="document">
        
        <?php if(!empty($login_image)) { ?>
        <div class="modal-body-left pull-left" style="background-image: url(<?php echo esc_url($login_image); ?>); background-size: cover; background-repeat: no-repeat; background-position: 50% 50%;">
            <div class="login-register-title">
                <?php echo esc_attr($login_text); ?>
            </div>
        </div>
        <?php } ?>

        <div class="modal-body-right pull-right">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo esc_html__('Login', 'homey'); ?></h4>
                </div>
                <div class="modal-body">

                    <div class="homey_login_messages message"></div>
                    
                    <?php if($facebook_login == 'yes') { ?>
                    <button type="button" class="homey-facebook-login btn btn-facebook-lined btn-full-width"><i class="fa fa-facebook" aria-hidden="true"></i> <?php echo esc_html__('Login with Facebook', 'homey'); ?></button>
                    <?php } ?>
                    
                    <?php if($google_login == 'yes') { ?>
                    <button type="button" class="homey-google-login btn btn-google-lined btn-full-width"><i class="fa fa-google" aria-hidden="true"></i> <?php echo esc_html__('Login with Google', 'homey'); ?></button>
                    <?php } ?>

                    <div class="modal-login-form">

                        <?php if($facebook_login == 'yes' || $google_login == 'yes') { ?>
                        <p class="text-center"><strong><?php echo esc_html__('Log in', 'homey'); ?></strong></p>
                        <?php } ?>

                        <form>
                            <div class="form-group">
                                <input type="text" name="username" class="form-control email-input-1" placeholder="<?php echo esc_attr__('Username or Email', 'homey'); ?>">
                            </div>
                            <div class="form-group">
                                <input type="password" name="password" class="form-control password-input-2" placeholder="<?php echo esc_attr__('Password', 'homey'); ?>">
                            </div>
                            
                            <?php if(homey_show_google_reCaptcha()){ ?>
                            <div class="bootstrap-select">
                                <?php get_template_part('template-parts/google', 'reCaptcha'); ?>
                            </div>
                            <?php } ?>

                            <div class="checkbox pull-left">
                                <label>
                                    <input name="remember" type="checkbox"> <?php echo esc_html__('Remember me', 'homey'); ?>
                                </label>
                            </div>
                            <div class="forgot-password-text pull-right">
                                <a href="#" data-toggle="modal" data-target="#modal-login-forgot-password" data-dismiss="modal"><?php echo esc_html__('Forgot password?', 'homey'); ?></a>
                            </div>

                            <?php wp_nonce_field( 'homey_login_nonce', 'homey_login_security' ); ?>
                            <input type="hidden" name="action" id="homey_login_action" value="homey_login">
                            <button type="submit" class="homey_login_button btn btn-primary btn-full-width"><?php echo esc_html__('Log In', 'homey'); ?></button>
                        </form>
                        <?php if($nav_register) { ?>
                            <p class="text-center"><?php echo esc_html__("Don't have an account?", 'homey'); ?> <a href="#" data-toggle="modal" data-target="#modal-register" data-dismiss="modal"><?php echo esc_html__('Register', 'homey'); ?></a></p>
                        <?php } ?>
                    </div>

                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
    </div>
</div><!-- /.modal -->