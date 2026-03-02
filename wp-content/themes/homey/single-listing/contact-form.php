<?php
global $post, $current_user, $homey_prefix, $homey_local;
wp_get_current_user();
$host_email = get_the_author_meta( 'email' );
$listing_id = $post->ID;

$key = '';
$userID      =   $current_user->ID;
$fav_option = 'homey_favorites-'.$userID;
$fav_option = get_option( $fav_option );
if( !empty($fav_option) ) {
    $key = array_search($post->ID, $fav_option);
}

if( $key != false || $key != '' ) {
    $favorite = $homey_local['remove_favorite'];
    $heart = 'fa-heart';
} else {
    $favorite = $homey_local['add_favorite'];
    $heart = 'fa-heart-o';
}
$enable_forms_gdpr = homey_option('enable_forms_gdpr');
$forms_gdpr_text = homey_option('forms_gdpr_text');
$form_type = homey_option('form_type');
$single_listing_host_contact = homey_option('single_listing_host_contact');
?>
<div class="sidebar-booking-module">
    <div class="block">
        <div class="sidebar-booking-module-header">
            <div class="block-body-sidebar">
                <h4 class="modal-title"><?php echo esc_attr($homey_local['pr_cont_me']); ?></h4>
            </div><!-- block-body-sidebar -->
        </div><!-- sidebar-booking-module-header -->
        <div class="sidebar-booking-module-body">
            <div class="host-contact-wrap block-body-sidebar">

                <?php 
                if($form_type != 'custom_form') {

                    if( !empty($single_listing_host_contact) ) {
                        echo do_shortcode($single_listing_host_contact);
                    } else {
                        echo esc_html__('Shortcode missing', 'homey');
                    }

                } else { ?>

                <form method="POST">
                    <input type="hidden" name="target_email" value="<?php echo antispambot($host_email); ?>">
                    <input type="hidden" name="host_contact_security" value="<?php echo wp_create_nonce('host-contact-nonce'); ?>"/>
                    <input type="hidden" name="permalink" value="<?php echo esc_url(get_permalink($post->ID)); ?>"/>
                    <input type="hidden" name="listing_title" value="<?php echo esc_attr(get_the_title($post->ID)); ?>"/>
                    <input type="hidden" name="action" value="homey_host_contact">
                    <div class="form-group">
                        <input type="text" name="name" class="form-control" placeholder="<?php echo esc_attr($homey_local['con_name']); ?>">
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="<?php echo esc_attr($homey_local['con_email']); ?>">
                    </div>
                    <div class="form-group">
                        <input type="text" name="phone" class="form-control" placeholder="<?php echo esc_attr($homey_local['con_phone']); ?>">
                    </div>
                    <div class="form-group">
                        <textarea name="message" class="form-control" placeholder="<?php echo esc_attr($homey_local['con_message']); ?>" rows="5"></textarea>
                    </div>

                    <?php if($enable_forms_gdpr != 0) { ?>
                    <div class="form-group checkbox">
                        <label>
                            <input name="privacy_policy" type="checkbox">
                            <?php echo wp_kses($forms_gdpr_text, homey_allowed_html()); ?>
                        </label>
                    </div>
                    <?php } ?>
                    
                    <?php get_template_part('template-parts/google', 'reCaptcha'); ?>

                    <button type="submit" class="contact_listing_host btn btn-primary btn-full-width"><?php echo esc_attr($homey_local['submit_btn']); ?></button>
                </form>
                <?php } ?>

                <div class="homey_contact_messages"></div>
                
            </div><!-- block-body-sidebar -->
        </div><!-- sidebar-booking-module-body -->
        
    </div><!-- block -->
</div><!-- sidebar-booking-module -->

<div class="sidebar-booking-module-footer">
    <div class="block-body-sidebar">

        <?php if(homey_option('detail_favorite') != 0) { ?>
        <button type="button" data-listid="<?php echo intval($post->ID); ?>" class="add_fav btn btn-full-width btn-grey-outlined"><i class="fa <?php echo esc_attr($heart); ?>" aria-hidden="true"></i> <?php echo esc_attr($favorite); ?></button>
        <?php } ?>
        
        <?php if(homey_option('print_button') != 0) { ?>
        <button type="button" id="homey-print" class="btn btn-full-width btn-blank" data-listing-id="<?php echo intval($listing_id);?>">
            <i class="fa fa-print" aria-hidden="true"></i> <?php echo esc_attr($homey_local['print_label']); ?>
        </button>
        <?php } ?>
    </div><!-- block-body-sidebar -->
    
    <?php 
    if(homey_option('detail_share') != 0) {
        get_template_part('single-listing/share'); 
    }
    ?>
</div><!-- sidebar-booking-module-footer -->