<?php
global $homey_local;
$terms_conditions = homey_option('login_terms_condition');
$register_image = homey_option('register_image', false, 'url' );
$register_text = esc_html__(homey_option('register_text'), 'homey');
$facebook_login = homey_option('facebook_login');
$google_login = homey_option('google_login');
$show_roles = homey_option('show_roles');
$enable_password = homey_option('enable_password');
$enable_forms_gdpr = homey_option('enable_forms_gdpr');
$forms_gdpr_text = esc_html__(homey_option('forms_gdpr_text'), 'homey');

?>
<div class="modal fade custom-modal-login" id="modal-register" tabindex="-1" role="dialog">
    <div class="modal-dialog clearfix" role="document">
        <?php if(!empty($register_image)) { ?>
        <div class="modal-body-left pull-left" style="background-image: url(<?php echo esc_url($register_image); ?>); background-size: cover; background-repeat: no-repeat; background-position: 50% 50%;">
            <div class="login-register-title">
                <?php echo esc_attr($register_text); ?>
            </div>
        </div>
        <?php } ?>

        <div class="modal-body-right pull-right">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo esc_html__('Register', 'homey'); ?></h4>
                </div>

                <?php if( get_option('users_can_register') ) { ?>
                <div class="modal-body">

                    <div class="homey_register_messages homey_login_messages message"></div>

                    <?php if($facebook_login == 'yes') { ?>
                    <button type="button" class="homey-facebook-login btn btn-facebook-lined btn-full-width"><i class="fa fa-facebook" aria-hidden="true"></i> <?php echo esc_html__('Login with Facebook', 'homey'); ?></button>
                    <?php } ?>
                    
                    <?php if($google_login == 'yes') { ?>
                    <button type="button" class="homey-google-login btn btn-google-lined btn-full-width"><i class="fa fa-google" aria-hidden="true"></i> <?php echo esc_html__('Login with Google', 'homey'); ?></button>
                    <?php } ?>

                    <div class="modal-login-form">
                        <?php if($facebook_login == 'yes' || $google_login == 'yes') { ?>
                        <p class="text-center"><strong><?php echo esc_html__('Register with your email', 'homey'); ?></strong></p>
                        <?php } ?>
                        <form>
                            <div class="form-group">
                                <input name="username" type="text" class="form-control email-input-1" placeholder="<?php esc_attr_e('Username','homey'); ?>" />
                            </div>
                            <div class="form-group">
                                <input type="email" name="useremail" class="form-control email-input-1" placeholder="<?php echo esc_attr__('Email', 'homey'); ?>">
                            </div>

                            <?php if( $enable_password == 'yes' ) { ?>
                            <div class="form-group">
                                <input type="password" name="register_pass" class="form-control password-input-1" placeholder="<?php echo esc_attr__('Password', 'homey'); ?>">
                            </div>
                            <div class="form-group">
                                <input type="password" name="register_pass_retype" class="form-control password-input-2" placeholder="<?php echo esc_attr__('Repeat Password', 'homey'); ?>">
                            </div>
                            <?php } ?>

                            <?php if($show_roles) { ?>
                            <select name="role" class="selectpicker" title="<?php echo esc_attr__('Select', 'homey'); ?>">
                                <option value=""><?php echo esc_attr__('Select', 'homey'); ?></option>
                                <option value="homey_renter"><?php echo esc_attr__(homey_option('renter_role'), 'homey'); ?></option>
                                <option value="homey_host"><?php echo esc_attr__(homey_option('host_role'), 'homey'); ?></option>
                            </select>
                            <?php } ?>

                            <?php if(homey_show_google_reCaptcha()){ ?>
                            <div class="bootstrap-select">
                                <?php get_template_part('template-parts/google', 'reCaptcha'); ?>
                            </div>
                            <?php } ?>

                            <div class="checkbox">
                                <label>
                                    <input required name="term_condition" type="checkbox"> <?php echo sprintf( wp_kses(__( 'I agree with your <a href="%s">Terms & Conditions</a>', 'homey' ), homey_allowed_html()), get_permalink($terms_conditions) ); ?>
                                </label>
                            </div>

                            <?php if($enable_forms_gdpr != 0) { ?>
                            <div class="checkbox">
                                <label>
                                    <input name="privacy_policy" type="checkbox">
                                    <?php echo wp_kses($forms_gdpr_text, homey_allowed_html()); ?>
                                </label>
                            </div>
                            <?php } ?>

                            <?php wp_nonce_field( 'homey_register_nonce', 'homey_register_security' ); ?>
                            <input type="hidden" name="action" value="homey_register">
                            <button type="submit" class="homey-register-button btn btn-primary btn-full-width"><?php echo esc_html__('Register', 'homey'); ?></button>
                        </form>
                        <p class="text-center"><?php echo esc_html__('Do you already have an account?', 'homey'); ?> <a href="#" data-toggle="modal" data-target="#modal-login" data-dismiss="modal"><?php echo esc_html__('Log In', 'homey'); ?></a></p>
                    </div>
                </div><!-- /.modal-content -->
                <?php } else {
                    echo '<div class="modal-body">';
                    esc_html_e('User registration is disabled in this website.', 'homey');
                    echo '</div>';
                } ?>
            </div><!-- /.modal-dialog -->
        </div>
    </div>
</div><!-- /.modal -->