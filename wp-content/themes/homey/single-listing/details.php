<?php
global $post, $homey_prefix, $homey_local, $hide_labels;
$guests     = homey_get_listing_data('guests');
$num_additional_guests    = homey_get_listing_data('num_additional_guests');

$bedrooms   = homey_get_listing_data('listing_bedrooms');
$beds       = homey_get_listing_data('beds');
$baths      = homey_get_listing_data('baths');
$listing_rooms       = homey_get_listing_data('listing_rooms');
$size       = homey_get_listing_data('listing_size');
$size_unit       = homey_get_listing_data('listing_size_unit');
$checkin_after   = homey_get_listing_data('checkin_after');
$checkout_before = homey_get_listing_data('checkout_before');
$room_type       = homey_taxonomy_simple('room_type');
$listing_type    = homey_taxonomy_simple('listing_type');

$slash = '';
if(!empty($room_type) && !empty($listing_type)) {
    $slash = '/';
}
?>
<div id="details-section" class="details-section">
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div class="block-left">
                    <h3 class="title"><?php echo esc_attr(homey_option('sn_detail_heading')); ?></h3>
                </div><!-- block-left -->
                <div class="block-right">
                    <ul class="detail-list detail-list-2-cols">
                        <?php if(@$hide_labels['sn_id_label'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_id_label')); ?>: <strong><?php echo esc_attr($post->ID); ?></strong>
                        </li> 
                        <?php } ?>

                        <?php if(!empty($guests) && $hide_labels['sn_guests_label'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_guests_label')); ?>: <strong><?php echo esc_attr($guests); ?></strong>
                        </li> 
                        <?php } ?>

                        <?php if(!empty($num_additional_guests)) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i>
                            <?php echo esc_html__('No of Guests', 'homey'); ?>: <strong><?php echo esc_attr($num_additional_guests); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($bedrooms) && $hide_labels['sn_bedrooms_label'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_bedrooms_label')); ?>: <strong><?php echo esc_attr($bedrooms); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($beds) && $hide_labels['sn_beds_label'] != 1) { ?>
                        <li><i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_beds_label')); ?>: <strong><?php echo esc_attr($beds); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($baths) && $hide_labels['sn_bathrooms_label'] != 1) { ?>
                        <li><i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_bathrooms_label')); ?>: <strong><?php echo esc_attr($baths); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($listing_rooms) && $hide_labels['sn_rooms_label'] != 1) { ?>
                        <li><i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_rooms_label')); ?>: <strong><?php echo esc_attr($listing_rooms); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($checkin_after) && $hide_labels['sn_check_in_after'] != 1) { ?>
                        <li><i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_check_in_after')); ?>: <strong><?php echo esc_attr($checkin_after); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($checkout_before) && $hide_labels['sn_check_out_before'] != 1) { ?>
                        <li><i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_check_out_before')); ?>: <strong><?php echo esc_attr($checkout_before); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if( (!empty($room_type) || !empty($listing_type)) && $hide_labels['sn_type_label'] != 1 ) { ?>
                        <li><i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_type_label')); ?>: <strong><?php echo esc_attr($room_type).' '.$slash.' '.esc_attr($listing_type); ?>  </strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($size) && $hide_labels['sn_size_label'] != 1) { ?>
                        <li><i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_size_label')); ?>: <strong><?php echo esc_attr($size).' '.esc_attr($size_unit); ?></strong>
                        </li>
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

                                    if($field_type != 'textarea') {
                                        if(!empty($data_value) && @$hide_labels[$value->field_id] != 1) {
                                            echo '<li class="'.esc_attr($value->field_id).'"><i class="fa fa-angle-right" aria-hidden="true"></i>'.esc_attr($field_title).': <strong>'.esc_attr( $data_value ).'</strong></li>';
                                        }
                                    }
                                }
                            }
                        }
                        ?>


                    </ul>
                </div><!-- block-right -->
            </div><!-- block-body -->
        </div><!-- block-section -->
    </div><!-- block -->
</div>