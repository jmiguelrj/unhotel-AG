<?php
global $homey_local, $hide_fields, $listing_data, $listing_meta_data;
$ttab = isset($_GET['tab']) ? $_GET['tab'] : '';
$class = '';
if( (isset($_GET['tab']) && $_GET['tab'] == 'information') || ($ttab == '')) {
    $class = 'in active';
}
$openning_hours_list = homey_option('openning_hours_list');
$openning_hours_list_array = explode( ',', $openning_hours_list );
?>
<div id="information-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
    <div class="block-title visible-xs">
        <h3 class="title"><?php echo esc_attr(homey_option('ad_section_info'));?></h3>
        <p class="description-block mb-0"><?php echo esc_html__('Fill all the mandatory fields', 'homey'); ?></p>
    </div>
    <div class="block-body">

        <?php $room_type = homey_get_terms('room_type'); ?>

       <?php if($hide_fields['room_type'] != 1) { ?>
        <div class="row">
            <div class="col-sm-12 col-xs-12"><label> <?php echo esc_attr(homey_option('ad_room_type')).homey_req('room_type'); ?> </label></div>
            <?php if(!empty($room_type)) { ?>

                <?php foreach($room_type as $room) { ?>    
                <div class="col-sm-4 col-xs-12">
                    <div class="form-group">
                        <label class="control control--radio radio-tab">
                            <input type="radio" <?php checked( homey_get_listing_tax_id($listing_data->ID, 'room_type'), $room->term_id ); ?> name="room_type" <?php homey_required('room_type'); ?> value="<?php echo esc_attr($room->term_id); ?>">
                            <span class="control-text"><?php echo esc_attr($room->name); ?></span>
                            <span class="control__indicator"></span>
                            <span class="radio-tab-inner"></span>
                        </label>
                    </div>
                </div>
                <?php } ?>
            <?php } ?>
            
        </div>
        <?php } ?>

        <div class="row">
            <?php if($hide_fields['listing_title'] != 1) { ?>
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="listing_title"><?php echo esc_attr(homey_option('ad_title')).homey_req('listing_title'); ?></label>
                    <input type="text" value="<?php print sanitize_text_field( $listing_data->post_title ); ?>" name="listing_title" class="form-control" <?php homey_required('listing_title'); ?> id="listing_title" placeholder="<?php echo esc_attr(homey_option('ad_title_plac'));?>">
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['description'] != 1) { ?>
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="description"><?php echo esc_attr(homey_option('ad_des')); ?></label>
                    <?php 
                        // default settings - Kv_front_editor.php
                        $content = $listing_data->post_content;
                        $editor_id = 'description';
                        $settings =   array(
                            'wpautop' => true, // use wpautop?
                            'media_buttons' => false, // show insert/upload button(s)
                            'textarea_name' => $editor_id, // set the textarea name to something different, square brackets [] can be used here
                            'textarea_rows' => '10', // rows="..."
                            'tabindex' => '',
                            'editor_css' => '', //  extra styles for both visual and HTML editors buttons, 
                            'editor_class' => '', // add extra class(es) to the editor textarea
                            'teeny' => false, // output the minimal editor config used in Press This
                            'dfw' => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                            'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                            'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                        );
                        wp_editor( $content, $editor_id, $settings ); ?>
                </div>
            </div>
            <?php } ?>

            <?php
                if(class_exists('Homey_Fields_Builder')) {
                $fields_array = Homey_Fields_Builder::get_form_fields();
                
                if(!empty($fields_array)) {
                    foreach ( $fields_array as $value ) {
                        $field_title = $value->label;
                        $field_name = $value->field_id;
                        $field_type = $value->type;
                        $field_placeholder = $value->placeholder;

                        $field_title = homey_wpml_translate_single_string($field_title);

                        $field_placeholder = homey_wpml_translate_single_string($field_placeholder);

                        if($field_type == 'textarea') {

                            if($hide_fields[$field_name] != 1) {
                                
                                echo '<div class="col-sm-12">';
                                echo '<div class="form-group">';
                                echo '<label for="'.$field_name.'">'.$field_title.homey_req($field_name).'</label>';
                                // default settings - Kv_front_editor.php
                                $content1 = isset($listing_meta_data['homey_'.$field_name][0]) ? $listing_meta_data['homey_'.$field_name][0] : '';
                                $editor_id1= $field_name;
                                $settings1 =   array(
                                    'wpautop' => true, // use wpautop?
                                    'media_buttons' => false, // show insert/upload button(s)
                                    'textarea_name' => $editor_id1, // set the textarea name to something different, square brackets [] can be used here
                                    'textarea_rows' => '5', // rows="..."
                                    'tabindex' => '',
                                    'editor_css' => '', //  extra styles for both visual and HTML editors buttons, 
                                    'editor_class' => '', // add extra class(es) to the editor textarea
                                    'teeny' => false, // output the minimal editor config used in Press This
                                    'dfw' => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                                    'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                                    'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                                );
                                wp_editor( $content1, $editor_id1, $settings1 );
                                echo '</div>';
                                echo '</div>';
                            }

                        }
                    } 
                }
            }
            ?>

        </div>
        <div class="row">

            <?php if($hide_fields['listing_type'] != 1) { ?>
            <div class="col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="listing_type"> <?php echo esc_attr(homey_option('ad_listing_type')).homey_req('listing_type'); ?> </label>
                    <select name="listing_type" class="selectpicker" <?php homey_required('listing_type'); ?>  id="listing_type" data-live-search="false" data-live-search-style="begins">
                        <?php homey_get_taxonomies_for_edit_listing( $listing_data->ID, 'listing_type' ); ?>

                    </select>
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['listing_bedrooms'] != 1) { ?>
            <div class="col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="listing_bedrooms"> <?php echo esc_attr(homey_option('ad_no_of_bedrooms')).homey_req('listing_bedrooms'); ?> </label>
                    <input type="text" name="listing_bedrooms" <?php homey_required('listing_bedrooms'); ?> id="listing_bedrooms" value="<?php homey_field_meta('listing_bedrooms'); ?>" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_no_of_bedrooms_plac')); ?>">
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['guests'] != 1) { ?>
            <div class="col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="guests"> <?php echo esc_attr(homey_option('ad_no_of_guests')).homey_req('guests'); ?> </label>
                    <input type="text" name="guests" id="guests" <?php homey_required('guests'); ?> value="<?php homey_field_meta('guests'); ?>" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_no_of_guests_plac')); ?>">
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['beds'] != 1) { ?>
            <div class="col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="beds"> <?php echo esc_attr(homey_option('ad_no_of_beds')).homey_req('beds'); ?> </label>
                    <input type="text" name="beds" id="beds" <?php homey_required('beds'); ?> value="<?php homey_field_meta('beds'); ?>" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_no_of_beds_plac')); ?>">
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['baths'] != 1) { ?>
            <div class="col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="baths"> <?php echo esc_attr(homey_option('ad_no_of_bathrooms')).homey_req('baths'); ?> </label>
                    <input type="text" name="baths" id="baths" <?php homey_required('baths'); ?> value="<?php homey_field_meta('baths'); ?>" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_no_of_bathrooms_plac')); ?>">
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['listing_rooms'] != 1) { ?>
            <div class="col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="listing_rooms"> <?php echo esc_attr(homey_option('ad_listing_rooms')).homey_req('listing_rooms'); ?> </label>
                    <input type="text" class="form-control" value="<?php homey_field_meta('listing_rooms'); ?>" name="listing_rooms" id="listing_rooms" <?php homey_required('listing_rooms'); ?> placeholder="<?php echo esc_attr(homey_option('ad_listing_rooms_plac'));?>">
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['listing_size'] != 1) { ?>
            <div class="col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="listing_size"> <?php echo esc_attr(homey_option('ad_listing_size')).homey_req('listing_size'); ?> </label>
                    <input type="text" class="form-control" name="listing_size" value="<?php homey_field_meta('listing_size'); ?>" id="listing_size" <?php homey_required('listing_size'); ?> placeholder="<?php echo esc_attr(homey_option('ad_size_placeholder'));?>">
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['listing_size_unit'] != 1) { ?>
            <div class="col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="listing_size_unit"> <?php echo esc_attr(homey_option('ad_listing_size_unit')).homey_req('listing_size_unit'); ?> </label>
                    <input type="text" class="form-control" name="listing_size_unit" value="<?php homey_field_meta('listing_size_unit'); ?>" <?php homey_required('listing_size_unit'); ?> id="listing_size_unit" placeholder="<?php echo esc_attr(homey_option('ad_listing_size_unit_plac'));?>">
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['affiliate_booking_link'] != 1) { ?>
            <div class="col-sm-12 col-xs-12">
                <div class="form-group">
                    <label for="affiliate_booking_link"> <?php echo esc_attr(homey_option('ad_affiliate_booking_link', 'Affiliate Booking Link')); ?> </label>
                    <input type="text" class="form-control" name="affiliate_booking_link" value="<?php homey_field_meta('affiliate_booking_link'); ?>" id="affiliate_booking_link" placeholder="<?php echo esc_attr(homey_option('ad_affiliate_booking_link_plac', 'Enter Affiliate Booking Link'));?>">
                </div>
            </div>
            <?php } ?>

            <?php
            if(class_exists('Homey_Fields_Builder')) {
            $fields_array = Homey_Fields_Builder::get_form_fields();
            
                if(!empty($fields_array)) {
                    foreach ( $fields_array as $value ) {
                        $field_title = $value->label;
                        $field_name = $value->field_id;
                        $field_type = $value->type;
                        $field_placeholder = $value->placeholder;

                        $field_title = homey_wpml_translate_single_string($field_title);
                        $field_placeholder = homey_wpml_translate_single_string($field_placeholder);

                        if($field_type == 'select') {

                            $options = unserialize($value->fvalues);
                            $options_option = '';
                            foreach ($options as $key => $val) {

                                $val = homey_wpml_translate_single_string($val);
                                $options_option .= '<option '.selected( $listing_meta_data['homey_'.$field_name][0], $key, false ).' value="'.esc_attr($key).'">'.esc_attr($val).'</option>';
                            }

                            if($hide_fields[$field_name] != 1) {
                                echo '<div class="col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="'.esc_attr($field_name).'">'.esc_attr($field_title).homey_req($field_name).'</label>
                                        <select '.homey_get_required($field_name).' name="'.esc_attr($field_name).'" class="selectpicker" data-live-search="true" data-live-search-style="begins">
                                            <option value="">'.esc_html__('None', 'homey').'</option>
                                            '.$options_option.'
                                        </select>
                                    </div>
                                </div>';
                            }

                        } elseif($field_type == 'text') {

                            $input_field = '<input '.homey_get_required($field_name).' class="form-control" name="'.esc_attr($field_name).'"'; 

                            if( isset( $listing_meta_data['homey_'.$field_name] ) ) {
                                $input_field .= 'value="'.sanitize_text_field( $listing_meta_data['homey_'.$field_name][0] ).'"'; 
                            }
                            $input_field .= 'placeholder="'.$field_placeholder.'">';
                            
                            if($hide_fields[$field_name] != 1) {
                                echo '<div class="col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="'.$field_name.'">'.$field_title.homey_req($field_name).'</label>
                                        '.$input_field.'
                                    </div>
                                </div>';
                            }
                        }
                    } 
                }
            }
            ?>

        </div>
    </div>

    <?php if($hide_fields['section_openning'] != 1) { ?>
    <div class="block-title">
        <div class="block-left">
            <h2 class="title"><?php echo esc_attr(homey_option('ad_section_openning')); ?></h2>
        </div><!-- block-left -->
    </div>
    <div class="block-body">
        <div class="row">
            <div class="row">
                <div class="col-sm-3 col-xs-12">
                    <div class="form-group">
                        <label for="property-title"><?php echo esc_attr(homey_option('ad_mon_fri')); ?></label>
                    </div>
                </div>
                <div class="col-sm-3 col-xs-12">
                    <div class="form-group">
                        <select name="mon_fri_open" class="selectpicker" data-live-search="false" data-live-search-style="begins" title="<?php echo esc_attr($homey_local['open_time_label']); ?>">
                            <option value=""><?php echo esc_attr($homey_local['open_time_label']); ?></option>
                            <?php 
                            foreach ($openning_hours_list_array as $hour) {
                                echo '<option '.selected( homey_get_field_meta('mon_fri_open'), trim($hour), false).' value="'.trim($hour).'">'.trim($hour).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-3 col-xs-12">
                    <div class="form-group">
                        <select name="mon_fri_close" class="selectpicker" data-live-search="false" data-live-search-style="begins" title="<?php echo esc_attr($homey_local['close_time_label']); ?>">
                            <option value=""><?php echo esc_attr($homey_local['close_time_label']); ?></option>
                            <?php 
                            foreach ($openning_hours_list_array as $hour) {
                                echo '<option '.selected( homey_get_field_meta('mon_fri_close'), trim($hour), false).' value="'.trim($hour).'">'.trim($hour).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-3 col-xs-12">
                    <div class="form-group">
                        <label class="control control--checkbox radio-tab">
                            <input name="mon_fri_closed" value="1" <?php checked( homey_get_field_meta('mon_fri_closed'), 1); ?> type="checkbox">
                            <span class="contro-text"><?php echo esc_attr(homey_option('ad_close')); ?></span>
                            <span class="control__indicator"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3 col-xs-12">
                    <div class="form-group">
                        <label for="property-title"><?php echo esc_attr(homey_option('ad_sat')); ?></label>
                    </div>
                </div>
                <div class="col-sm-3 col-xs-12">
                    <div class="form-group">
                        <select name="sat_open" class="selectpicker" data-live-search="false" data-live-search-style="begins" title="<?php echo esc_attr($homey_local['open_time_label']); ?>">
                            <option value=""><?php echo esc_attr($homey_local['open_time_label']); ?></option>
                            <?php 
                            foreach ($openning_hours_list_array as $hour) {
                                echo '<option '.selected( homey_get_field_meta('sat_open'), trim($hour), false).' value="'.trim($hour).'">'.trim($hour).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-3 col-xs-12">
                    <div class="form-group">
                        <select name="sat_close" class="selectpicker" id="property-type" data-live-search="false" data-live-search-style="begins" title="<?php echo esc_attr($homey_local['close_time_label']); ?>">
                            <option value=""><?php echo esc_attr($homey_local['close_time_label']); ?></option>
                            <?php 
                            foreach ($openning_hours_list_array as $hour) {
                                echo '<option '.selected( homey_get_field_meta('sat_close'), trim($hour), false).' value="'.trim($hour).'">'.trim($hour).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-3 col-xs-12">
                    <div class="form-group">
                        <label class="control control--checkbox radio-tab">
                            <input name="sat_closed" value="1" <?php checked( homey_get_field_meta('sat_closed'), 1); ?> type="checkbox">
                            <span class="contro-text"><?php echo esc_attr(homey_option('ad_close')); ?></span>
                            <span class="control__indicator"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3 col-xs-12">
                    <div class="form-group">
                        <label for="property-title"><?php echo esc_attr(homey_option('ad_sun')); ?></label>
                    </div>
                </div>
                <div class="col-sm-3 col-xs-12">
                    <div class="form-group">
                        <select name="sun_open" class="selectpicker" data-live-search="false" data-live-search-style="begins" title="<?php echo esc_attr($homey_local['open_time_label']); ?>">
                            <option value=""><?php echo esc_attr($homey_local['open_time_label']); ?></option>
                            <?php 
                            foreach ($openning_hours_list_array as $hour) {
                                echo '<option '.selected( homey_get_field_meta('sun_open'), trim($hour), false).' value="'.trim($hour).'">'.trim($hour).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-3 col-xs-12">
                    <div class="form-group">
                        <select name="sun_close" class="selectpicker" data-live-search="false" data-live-search-style="begins" title="<?php echo esc_attr($homey_local['close_time_label']); ?>">
                            <option value=""><?php echo esc_attr($homey_local['close_time_label']); ?></option>
                            <?php 
                            foreach ($openning_hours_list_array as $hour) {
                                echo '<option '.selected( homey_get_field_meta('sun_close'), trim($hour), false).' value="'.trim($hour).'">'.trim($hour).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-3 col-xs-12">
                    <div class="form-group">
                        <label class="control control--checkbox radio-tab">
                            <input name="sun_closed" value="1" <?php checked( homey_get_field_meta('sun_closed'), 1); ?> type="checkbox">
                            <span class="contro-text"><?php echo esc_attr(homey_option('ad_close')); ?></span>
                            <span class="control__indicator"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php } // end Openning Hours?>
    
</div>