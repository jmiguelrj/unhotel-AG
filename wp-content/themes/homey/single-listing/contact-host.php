<?php
global $post, $homey_local;
$host_email = get_the_author_meta( 'email' );
$enable_forms_gdpr = homey_option('enable_forms_gdpr');
$forms_gdpr_text = homey_option('forms_gdpr_text');
$form_type = homey_option('form_type');
$single_listing_host_contact = homey_option('single_listing_host_contact');
?>
<div class="modal fade custom-modal-contact-host" id="modal-contact-host" tabindex="-1" role="dialog">
    <div class="modal-dialog clearfix" role="document">

        <div class="modal-body">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo esc_attr($homey_local['pr_cont_host']); ?></h4>
                </div>
                <div class="modal-body host-contact-wrap">

                    <div class="modal-contact-host-form">

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
                            <div class="homey_contact_messages"></div>
                            <button type="submit" class="contact_listing_host btn btn-primary btn-full-width"><?php echo esc_attr($homey_local['submit_btn']); ?></button>
                        </form>
                        <?php } ?>
                        
                    </div>

                    

                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
    </div>
</div><!-- /.modal -->