<div class="block">
    <div class="block-title">
        <div class="block-left">
            <h2 class="title"><?php esc_html_e('Change Password','homey');?></h2>
        </div>
    </div>
    <div class="block-body">
        <form>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="newpass"><?php esc_html_e('New Password','homey');?></label>
                        <input type="password" id="newpass" class="form-control" placeholder="<?php esc_attr__('Enter new password','homey');?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="confirmpass"><?php esc_html_e('Confirm Password','homey');?></label>
                        <input type="password" id="confirmpass" class="form-control" placeholder="<?php esc_attr__('Confirm new password','homey');?>">
                    </div>
                </div>
                <div class="col-sm-12 text-right">
                    <?php wp_nonce_field( 'homey_pass_ajax_nonce', 'homey-security-pass' );   ?>
                    <button class="btn btn-success btn-xs-full-width" id="homey_change_pass"><?php esc_html_e('Update Password','homey');?></button>
                    <div id="password_reset_msgs" class="homey_messages message"></div>
                </div>
            </div>
        </form>
    </div>
</div><!-- .block -->