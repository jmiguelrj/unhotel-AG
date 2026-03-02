<?php
global $current_user, $userID, $homey_prefix, $homey_local;
$homey_prefix = 'homey_';

$em_contact_name = get_the_author_meta( $homey_prefix.'em_contact_name' , $userID );
$em_relationship  = get_the_author_meta( $homey_prefix.'em_relationship' , $userID );
$em_email  = get_the_author_meta( $homey_prefix.'em_email' , $userID );
$em_phone  = get_the_author_meta( $homey_prefix.'em_phone' , $userID );
?>
<div class="block">
    <div class="block-title">
        <div class="block-left">
            <h2 class="title"><?php echo esc_html__('Emergency Contact', 'homey'); ?></h2>
        </div>
    </div>
    <div class="block-body">
        <form>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="em_contact_name"><?php echo esc_html__('Contact Name', 'homey'); ?></label>
                        <input type="text" id="em_contact_name" class="form-control" value="<?php echo esc_attr($em_contact_name); ?>" placeholder="<?php echo esc_attr__('Enter Name', 'homey'); ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="em_relationship"><?php echo esc_html__('Relationship', 'homey'); ?></label>
                        <input type="text" id="em_relationship" class="form-control" value="<?php echo esc_attr($em_relationship); ?>" placeholder="<?php echo esc_attr__('Enter Relationship', 'homey'); ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="em_email"><?php echo esc_html__('Email', 'homey'); ?></label>
                        <input class="form-control" id="em_email" value="<?php echo esc_attr($em_email); ?>" placeholder="<?php echo esc_attr__('Enter contact email address', 'homey'); ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="em_phone"><?php echo esc_html__('Phone', 'homey'); ?></label>
                        <input type="tel" id="em_phone" class="form-control" value="<?php echo esc_attr($em_phone); ?>" placeholder="<?php echo esc_attr__('Enter the phone number', 'homey'); ?>">
                    </div>
                </div>
                <div class="col-sm-12 text-right">
                    <button type="submit" class="homey_profile_save btn btn-success btn-xs-full-width"><?php echo esc_attr($homey_local['save_btn']); ?></button>
                </div>
            </div>
        </form>
    </div>
</div><!-- block -->