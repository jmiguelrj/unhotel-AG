<div class="modal fade custom-modal-login" id="modal-login-forgot-password" tabindex="-1" role="dialog">
    <div class="modal-dialog clearfix" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php esc_html_e( 'Forgot Password', 'homey' ); ?></h4>
            </div>
            <div class="modal-body">
                <div class="modal-login-form">
                    <p><?php esc_html_e( 'Please enter your username or email address. You will receive a link to create a new password via email.', 'homey' ); ?></p>
                    <form>
                        <div class="form-group">
                            <input type="email" name="user_login_forgot" id="user_login_forgot" class="form-control forgot-password" placeholder="<?php esc_attr_e( 'Enter your username or email', 'homey' ); ?>">
                        </div>

                        <?php wp_nonce_field( 'homey_resetpassword_nonce', 'homey_resetpassword_security' ); ?>
                        <button type="submit" id="homey_forgetpass" class="btn btn-primary btn-full-width"><?php esc_html_e( 'Submit', 'homey' ); ?></button>
                        <div id="homey_msg_reset" class="message"></div>
                    </form>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
</div><!-- /.modal -->
