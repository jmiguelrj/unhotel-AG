<?php
global $userID, $user_email, $homey_local, $author_info;
$author_picture_id = get_the_author_meta( 'homey_author_picture_id' , $userID );
?>
<div class="block">
    <div class="block-title">
        <div class="block-left">
            <h2 class="title"><?php echo esc_html__('Delete Account', 'homey'); ?></h2>
        </div>
    </div>
    <div class="block-body">
        <div class="row">
            <p class="profile-image-note"><strong><?php echo esc_html__('Your account and all posts will be deleted permanently!', 'homey'); ?></strong></p>
            <button type="button" class="btn btn-grey-outlined btn-xs-full-width delete_user_account"><?php echo esc_html__('Delete', 'homey'); ?></button>
            <div id="delete_errors"></div>

            <div id="delete_account_warning" class="col-md-4" style="display: none;">
                <p class="profile-image-note"><strong><?php echo esc_html__('Are you sure to delete the account?', 'homey'); ?></strong><br></p>
                <button id="hide_delete_confirmation_wrap" type="button" class="btn btn-primary btn-xs-full-width"><?php echo esc_html__('Not Now', 'homey'); ?></button>
                <button type="button" class="btn btn-grey-outlined btn-xs-full-width delete_user_account_confirmed"><?php echo esc_html__('Delete', 'homey'); ?></button>
            </div>
        </div>
    </div>
</div>