<?php
global $post, $homey_prefix, $homey_local, $hide_labels;
$mon_fri_final = $sat_final = $sun_final = '';

$mon_fri_open     = homey_get_listing_data('mon_fri_open');
$mon_fri_close     = homey_get_listing_data('mon_fri_close');
$mon_fri_closed     = homey_get_listing_data('mon_fri_closed');
$sat_open     = homey_get_listing_data('sat_open');
$sat_close     = homey_get_listing_data('sat_close');
$sat_closed     = homey_get_listing_data('sat_closed');
$sun_open     = homey_get_listing_data('sun_open');
$sun_close     = homey_get_listing_data('sun_close');
$sun_closed     = homey_get_listing_data('sun_closed');
$bedrooms   = homey_get_listing_data('listing_bedrooms');
$listing_type = homey_taxonomy_simple('listing_type');
$size       = homey_get_listing_data('listing_size');
$size_unit       = homey_get_listing_data('listing_size_unit');
$guests     = homey_get_listing_data('guests');

if($mon_fri_closed == 1) {
    $mon_fri_final = homey_option('sn_closed_label');
} else {
    $mon_fri_final = $mon_fri_open.' - '.$mon_fri_close;
}
if($sat_closed == 1) {
    $sat_final = homey_option('sn_closed_label');
} else {
    $sat_final = $sat_open.' - '.$sat_close;
}
if($sun_closed == 1) {
    $sun_final = homey_option('sn_closed_label');
} else {
    $sun_final = $sun_open.' - '.$sun_close;
}
?>
<div id="details-section" class="details-section">

    <?php if($hide_labels['sn_accommodates_label'] != 1 || $hide_labels['sn_opening_hours_label'] != 1) { ?>
    <div class="block">
        <div class="block-section block-section-50">
            <div class="block-body">
                
                <?php if($listing_type != '' || !empty($guests) || !empty($size)) { ?>
                    <?php if($hide_labels['sn_accommodates_label'] != 1) { ?>
                    <div class="block-left">
                        <h3 class="title"><i class="fa fa-user-circle-o" aria-hidden="true"></i> <?php echo esc_attr(homey_option('sn_accommodates_label')); ?></h3>
                        <ul class="detail-list">
                            <?php if($hide_labels['sn_type_label'] != 1 && $listing_type != '') { ?>
                            <li><i class="fa fa-angle-right" aria-hidden="true"></i> <?php echo esc_attr(homey_option('sn_type_label')); ?>: <strong><?php echo esc_attr($listing_type); ?></strong></li>
                            <?php } ?>

                            <?php if( !empty($guests) && $hide_labels['sn_guests_label'] != 1) { ?>
                            <li><i class="fa fa-angle-right" aria-hidden="true"></i> <?php echo esc_attr(homey_option('sn_guests_label')); ?>: <strong><?php echo esc_attr($guests); ?></strong></li>
                            <?php } ?>

                            <?php if(!empty($size) && $hide_labels['sn_size_label'] != 1) { ?>
                            <li><i class="fa fa-angle-right" aria-hidden="true"></i> <?php echo esc_attr(homey_option('sn_size_label')); ?>: <strong><?php echo esc_attr($size).' '.$size_unit; ?></strong></li>
                            <?php } ?>
                        </ul>
                    </div><!-- block-left -->
                    <?php } 
                }?>

                <?php if($mon_fri_open != '' || $mon_fri_close != '' || $sat_open != '' || $sat_close != '' || $sun_open != '' || $sun_close != '') { ?>
                    <?php if($hide_labels['sn_opening_hours_label'] != 1) { ?>
                    <div class="block-right">
                        <h3 class="title"><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo esc_attr(homey_option('sn_opening_hours_label')); ?></h3>
                        <ul class="detail-list">
                            <?php if($mon_fri_open != '' || $mon_fri_close != '') { ?>
                            <li>
                                <i class="fa fa-angle-right" aria-hidden="true"></i> 
                                <?php echo esc_attr(homey_option('sn_mon_fri_label')); ?>: <strong><?php echo esc_attr($mon_fri_final); ?></strong>
                            </li>
                            <?php } ?>

                            <?php if($sat_open != '' || $sat_close != '') { ?>
                            <li>
                                <i class="fa fa-angle-right" aria-hidden="true"></i> 
                                <?php echo esc_attr(homey_option('sn_sat_label')); ?>: <strong><?php echo esc_attr($sat_final); ?></strong>
                            </li>
                            <?php } ?>

                            <?php if($sun_open != '' || $sun_close != '') { ?>
                            <li>
                                <i class="fa fa-angle-right" aria-hidden="true"></i> 
                                <?php echo esc_attr(homey_option('sn_sun_label')); ?>: <strong><?php echo esc_attr($sun_final); ?></strong>
                            </li>
                            <?php } ?>

                        </ul>
                    </div><!-- block-right -->
                    <?php } 
                }?>
            </div><!-- block-body -->
        </div><!-- block-section -->
    </div><!-- block -->
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