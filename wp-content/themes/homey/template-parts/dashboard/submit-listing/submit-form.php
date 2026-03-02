<?php
global $homey_local, $hide_fields, $homey_booking_type;
$layout_order = homey_option('listing_form_sections');
$layout_order = $layout_order['enabled'];
$homey_booking_type = homey_booking_type();

if( isset($_GET['mode']) && $_GET['mode'] != '' ) {
    $homey_booking_type = $_GET['mode'];
}

?>
<form autocomplete="off" id="submit_listing_form" name="new_post" method="post" action="#" enctype="multipart/form-data" class="add-frontend-property">
                                
    <?php
    if ($layout_order) { 
        foreach ($layout_order as $key=>$value) {
            
            switch($key) { 
                case 'information':
                    get_template_part('template-parts/dashboard/submit-listing/information');
                break;

                case 'pricing':
                    get_template_part('template-parts/dashboard/submit-listing/pricing');
                break;

                case 'media':
                    get_template_part('template-parts/dashboard/submit-listing/media');
                break;

                case 'features':
                    get_template_part('template-parts/dashboard/submit-listing/features');
                break;

                case 'location':
                    get_template_part('template-parts/dashboard/submit-listing/location');
                break;

                case 'bedrooms':
                    get_template_part('template-parts/dashboard/submit-listing/bedrooms');
                break;

                case 'services':
                    get_template_part('template-parts/dashboard/submit-listing/services');
                break;

                case 'term_rules':
                    get_template_part('template-parts/dashboard/submit-listing/terms');
                break;
            }
        }
    }
    ?>

    <div class="steps-nav">
        <button type="button" class="btn btn-grey-outlined btn-step-back btn-xs-full-width action"><?php echo esc_attr($homey_local['back_btn']); ?></button>

        <button id="save_as_draft" type="button" class="btn btn-grey-outlined btn-xs-full-width"><?php esc_html_e('Save as Draft', 'homey'); ?></button>
        
        <button type="button" class="btn btn-success btn-step-next btn-xs-full-width action"><?php echo esc_attr($homey_local['continue_btn']); ?></button>
        <button type="submit" class="btn btn-success btn-step-submit btn-xs-full-width action"><?php echo esc_attr($homey_local['submit_btn']); ?></button>
    </div><!-- steps-nav -->

    <?php wp_nonce_field('submit_listing', 'homey_add_listing_nonce'); ?>

    <input type="hidden" name="listing_featured" value="0"/>
    <input type="hidden" name="booking_type" value="<?php echo esc_attr($homey_booking_type); ?>"/>
    <input type="hidden" name="action" value="homey_add_listing"/>

</form><!-- #add-property-form -->