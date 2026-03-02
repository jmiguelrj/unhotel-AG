<?php
global $post, $homey_prefix, $homey_local, $hide_labels;
$guests     = homey_get_listing_data('guests');

$allow_additional_guests = get_post_meta( get_the_ID(), $homey_prefix.'allow_additional_guests', true );
$num_additional_guests = get_post_meta( get_the_ID(), $homey_prefix.'num_additional_guests', true );

if( $allow_additional_guests == 'yes' && ! empty( $num_additional_guests ) ) {
    $guests = $guests + $num_additional_guests;
}

$num_additional_guests = homey_get_field_meta('num_additional_guests');

$bedrooms   = homey_get_listing_data('listing_bedrooms');
$beds       = homey_get_listing_data('beds');
$bathrooms      = homey_get_listing_data('baths');
$room_type  = homey_taxonomy_simple('room_type');
$listing_type = homey_taxonomy_simple('listing_type');

$full_bath = $half_bath = $type_icon = $acco_icon = $bedroom_icon = $bathroom_icon = '';
if($bathrooms != '' && $bathrooms != '0') {
    $baths = explode('.', $bathrooms);
    $full_bath = $baths[0].' '.homey_option('sn_fullbath_label'); 
    if(!empty($baths[1]) && $baths[1] == '5') {
        $half_bath = '1'.' '.homey_option('sn_halfbath_label');
    }
} else {
    $full_bath = $bathrooms;
}

$slash = '';
if(!empty($room_type) && !empty($listing_type)) {
    $slash = '/';
}
$icon_type = homey_option('detail_icon_type');

if($icon_type == 'fontawesome_icon') {
    $type_icon = '<i class="'.esc_attr(homey_option('de_type_icon')).'"></i>';
    $acco_icon = '<i class="'.esc_attr(homey_option('de_acco_icon')).'"></i>';
    $bedroom_icon = '<i class="'.esc_attr(homey_option('de_bedroom_icon')).'"></i>';
    $bathroom_icon = '<i class="'.esc_attr(homey_option('de_bathroom_icon')).'"></i>';

} elseif($icon_type == 'custom_icon') {
    $type_icon = '<img src="'.esc_url(homey_option( 'de_cus_type_icon', false, 'url' )).'" alt="'.esc_attr__('type_icon', 'homey').'">';
    $acco_icon = '<img src="'.esc_url(homey_option( 'de_cus_acco_icon', false, 'url' )).'" alt="'.esc_attr__('acco_icon', 'homey').'">';
    $bedroom_icon = '<img src="'.esc_url(homey_option( 'de_cus_bedroom_icon', false, 'url' )).'" alt="'.esc_attr__('bedroom_icon', 'homey').'">';
    $bathroom_icon = '<img src="'.esc_url(homey_option( 'de_cus_bathroom_icon', false, 'url' )).'" alt="'.esc_attr__('bathroom_icon', 'homey').'">';
}
?>
<div id="about-section" class="about-section">

    <?php if($guests != '' || $bedrooms != '' || $bathrooms != '' || $listing_type != '') { ?>
    <div class="block-bordered">
        
        <?php if($hide_labels['sn_type_label'] != 1 && $listing_type != '') { ?>
        <div class="block-col block-col-25">
            <div class="block-icon">
                <?php echo ''.$type_icon; ?>
            </div>
            <div><?php echo esc_attr(homey_option('sn_type_label')); ?></div>
            <div>
                <strong>
                <?php echo esc_attr($room_type).' '.$slash.' '.esc_attr($listing_type); ?>     
                </strong>
            </div>
        </div>
        <?php } ?>

        <?php if($hide_labels['sn_accom_label'] != 1 && $guests != '') { ?>
        <div class="block-col block-col-25">
            <div class="block-icon">
                <?php echo ''.$acco_icon; ?>
            </div>
            <div><?php echo esc_attr(homey_option('sn_accom_label')); ?></div>
            <div><strong><?php echo esc_attr($guests).' > '.$num_additional_guests; ?> <?php echo esc_attr(homey_option('sn_guests_label')); ?></strong></div>
        </div>
        <?php } ?>

        <?php if($hide_labels['sn_bedrooms_label'] != 1 && $bedrooms != '') { ?>
        <div class="block-col block-col-25">
            <div class="block-icon">
                <?php echo ''.$bedroom_icon; ?>
            </div>
            <div><?php echo esc_attr(homey_option('sn_bedrooms_label')); ?></div>
            <div><strong><?php echo esc_attr($bedrooms); ?> <?php echo esc_attr(homey_option('sn_bedrooms_label')); ?> / <?php echo esc_attr($beds); ?> <?php echo esc_attr(homey_option('sn_beds_label')); ?></strong></div>
        </div>
        <?php } ?>

        <?php if($hide_labels['sn_bathrooms_label'] != 1 && $bathrooms != '') { ?>
        <div class="block-col block-col-25">
            <div class="block-icon">
                <?php echo ''.$bathroom_icon; ?>
            </div>
            <div><?php echo esc_attr(homey_option('sn_bathrooms_label')); ?></div>
            <div><strong><?php echo esc_attr($full_bath).' '.esc_attr($half_bath); ?></strong></div>
        </div>
        <?php } ?>

    </div><!-- block-bordered -->
    <?php } ?>  

    <?php if($hide_labels['sn_about_listing_title'] != 1) { ?>
    <div class="block">
        <div class="block-body">    
            <h2><?php echo esc_attr(homey_option('sn_about_listing_title')); ?></h2>
            <?php the_content(); ?>
        </div>
    </div><!-- block-body -->   
    <?php } ?> 

    <?php
    //Custom Fields
    if(class_exists('Homey_Fields_Builder')) {
    $fields_array = Homey_Fields_Builder::get_form_fields(); 

        if(!empty($fields_array)) {
            foreach ( $fields_array as $value ) {
                $data_value = get_post_meta( get_the_ID(), 'homey_'.$value->field_id, true );
                $field_title = $value->label;
                $field_type = $value->type;
                
                $field_title = homey_wpml_translate_single_string($field_title);
                $data_value = homey_wpml_translate_single_string($data_value);

                if($field_type == 'textarea') {
                    if(!empty($data_value) && $hide_labels[$value->field_id] != 1) {
                        echo '
                        <div class="block">
                            <div class="block-body">
                                <h2>'.esc_attr($field_title).'</h2>
                                '.$data_value.'
                            </div>
                        </div>
                        ';
                    }
                }
            }
        }
    }
    ?>

</div>