<?php
global $homey_prefix, $hide_fields, $homey_local, $listing_data, $listing_meta_data, $homey_booking_type;
$min_book_days = homey_get_field_meta('min_book_days'); 
$min_book_hours = homey_get_field_meta('min_book_hours'); 
$max_book_days = homey_get_field_meta('max_book_days'); 
$min_book_weeks = homey_get_field_meta('min_book_weeks'); 
$max_book_weeks = homey_get_field_meta('max_book_weeks'); 
$min_book_months = homey_get_field_meta('min_book_months'); 
$max_book_months = homey_get_field_meta('max_book_months'); 
$checkin_after = homey_get_field_meta('checkin_after'); 
$checkout_before = homey_get_field_meta('checkout_before'); 
$get_start_hour = homey_get_field_meta('start_hour'); 
$get_end_hour = homey_get_field_meta('end_hour'); 
$smoke = homey_get_field_meta('smoke'); 
$pets = homey_get_field_meta('pets'); 
$party = homey_get_field_meta('party'); 
$children = homey_get_field_meta('children'); 
$additional_rules = isset($listing_meta_data['homey_additional_rules'][0]) ? $listing_meta_data['homey_additional_rules'][0] : ''; 
$cancellation_policy = isset($listing_meta_data['homey_cancellation_policy'][0]) ? $listing_meta_data['homey_cancellation_policy'][0] : ''; 


$checkin_after_before = homey_option('checkin_after_before');
$checkin_after_before_array = explode( ',', $checkin_after_before );

$start_end_hour_array = array();

$start_hour = strtotime('1:00');
$end_hour = strtotime('24:00');

$class = '';
if(isset($_GET['tab']) && $_GET['tab'] == 'rules') {
    $class = 'in active';
}
?>
<div id="rules-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
    <div class="block-title visible-xs">
            <h3 class="title"><?php echo esc_attr(homey_option('ad_terms_rules')); ?></h3>
    </div>
    <div class="block-body">

        <?php if($hide_fields['cancel_policy'] != 1) { ?>
        <div class="row">
            <div class="col-sm-12 col-xs-12">
                <div class="form-group">
                    <label for="cancel"><?php echo esc_attr(homey_option('ad_cancel_policy')).homey_req('cancellation_policy'); ?></label>
                    <textarea name="cancellation_policy" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_cancel_policy_plac'), 'homey'); ?>" <?php homey_required('cancellation_policy'); ?>><?php echo $cancellation_policy; ?></textarea>
                </div>
            </div>
        </div>
        <?php } ?>

        <div class="row">

            <?php if($homey_booking_type == 'per_hour') { ?>

                <?php if($hide_fields['min_book_days'] != 1) { ?>
                <div class="col-sm-12 col-xs-12">
                    <div class="form-group">
                        <label for="min_book_hours"><?php echo esc_attr(homey_option('ad_min_hours_booking')).homey_req('min_book_days'); ?></label>
                        <input type="text" name="min_book_hours" <?php homey_required('min_book_days'); ?> value="<?php echo esc_attr($min_book_hours); ?>" class="form-control" id="min_book_hours" placeholder="<?php echo esc_attr(homey_option('ad_min_hours_booking_plac')); ?>">
                    </div>
                </div>
                <?php } ?>

                <?php } elseif($homey_booking_type == 'per_week') {

                    if($hide_fields['min_book_weeks'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="min_book_weeks"><?php echo esc_attr(homey_option('ad_min_weeks_booking')).homey_req('min_book_weeks'); ?></label>
                            <input type="text" name="min_book_weeks" value="<?php echo esc_attr($min_book_weeks); ?>" class="form-control" <?php homey_required('min_book_weeks'); ?> id="min_book_weeks" placeholder="<?php echo esc_attr(homey_option('ad_min_weeks_booking_plac')); ?>">
                        </div>
                    </div>
                    <?php }

                    if($hide_fields['max_book_weeks'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="max_book_weeks"><?php echo esc_attr(homey_option('ad_max_weeks_booking')).homey_req('max_book_weeks'); ?></label>
                            <input type="text" name="max_book_weeks" value="<?php echo esc_attr($max_book_weeks); ?>" class="form-control" <?php homey_required('max_book_weeks'); ?> id="max_book_weeks" placeholder="<?php echo esc_attr(homey_option('ad_max_weeks_booking_plac')); ?>">
                        </div>
                    </div>
                    <?php }


            } elseif($homey_booking_type == 'per_month') {

                    if($hide_fields['min_book_months'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="min_book_months"><?php echo esc_attr(homey_option('ad_min_months_booking')).homey_req('min_book_months'); ?></label>
                            <input type="text" name="min_book_months" value="<?php echo esc_attr($min_book_months); ?>" class="form-control" <?php homey_required('min_book_months'); ?> id="min_book_months" placeholder="<?php echo esc_attr(homey_option('ad_min_months_booking_plac')); ?>">
                        </div>
                    </div>
                    <?php }

                    if($hide_fields['max_book_months'] != 1) { ?>
                    <div class="col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label for="max_book_months"><?php echo esc_attr(homey_option('ad_max_months_booking')).homey_req('max_book_months'); ?></label>
                            <input type="text" name="max_book_months" value="<?php echo esc_attr($max_book_months); ?>" class="form-control" <?php homey_required('max_book_months'); ?> id="max_book_months" placeholder="<?php echo esc_attr(homey_option('ad_max_months_booking_plac')); ?>">
                        </div>
                    </div>
                    <?php }


            } else { ?>

                <?php if($hide_fields['min_book_days'] != 1) { ?>
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="min_book_days"><?php echo esc_attr(homey_option('ad_min_days_booking')).homey_req('min_book_days'); ?></label>
                        <input type="text" name="min_book_days" <?php homey_required('min_book_days'); ?> value="<?php echo esc_attr($min_book_days); ?>" class="form-control" id="min_book_days" placeholder="<?php echo esc_attr(homey_option('ad_min_days_booking_plac')); ?>">
                    </div>
                </div>
                <?php } ?>

                <?php if($hide_fields['max_book_days'] != 1) { ?>
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="max_book_days"><?php echo esc_attr(homey_option('ad_max_days_booking')).homey_req('max_book_days'); ?></label>
                        <input type="text" name="max_book_days" <?php homey_required('max_book_days'); ?> value="<?php echo esc_attr($max_book_days); ?>" class="form-control" id="max_book_days" placeholder="<?php echo esc_attr(homey_option('ad_max_days_booking_plac')); ?>">
                    </div>
                </div>
                <?php } ?>
            <?php } ?>

        </div>

        <?php if($homey_booking_type == 'per_hour') { ?>
            <hr class="row-separator">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <h3 class="sub-title"><?php echo esc_html__('Business Hours', 'homey'); ?></h3>
                </div>
                
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="start_hour"><?php echo esc_html__('Start Hour', 'homey').homey_req('start_hour'); ?></label>
                        <select name="start_hour" class="selectpicker" <?php homey_required('start_hour'); ?> id="start_hour" data-live-search="false" title="<?php echo esc_attr(homey_option('ad_text_select')); ?>">
                                <option value=""><?php echo esc_attr(homey_option('ad_text_select')); ?></option>
                                <?php 
                                for ($halfhour = $start_hour;$halfhour <= $end_hour; $halfhour = $halfhour+30*60) {
                                    echo '<option '.selected(date('H:i',$halfhour), $get_start_hour, false).' value="'.date('H:i',$halfhour).'">'.date(homey_time_format(),$halfhour).'</option>';
                                }
                                ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label for="end_hour"><?php echo esc_html__('End Hour', 'homey').homey_req('end_hour'); ?></label>
                        <select name="end_hour" class="selectpicker" <?php homey_required('end_hour'); ?> id="end_hour" data-live-search="false" title="<?php echo esc_attr(homey_option('ad_text_select')); ?>">
                            <option value=""><?php echo esc_attr(homey_option('ad_text_select')); ?></option>
                            <?php 
                            for ($halfhour = $start_hour;$halfhour <= $end_hour; $halfhour = $halfhour+30*60) {
                                echo '<option '.selected(date('H:i',$halfhour), $get_end_hour, false).' value="'.date('H:i',$halfhour).'">'.date(homey_time_format(),$halfhour).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
            </div>

        <?php } elseif( $homey_booking_type == 'per_day_date' || $homey_booking_type == 'per_day' ) { ?>
        <div class="row">
            <?php if($hide_fields['checkin_after'] != 1) { ?>
            <div class="col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="checkin_after"><?php echo esc_attr(homey_option('ad_check_in_after')).homey_req('checkin_after'); ?></label>
                    <select name="checkin_after" class="selectpicker" <?php homey_required('checkin_after'); ?> id="checkin_after" data-live-search="false" title="<?php echo esc_attr(homey_option('ad_text_select')); ?>">
                        <?php 
                        foreach ($checkin_after_before_array as $hour) {
                            echo '<option '.selected( homey_get_field_meta('checkin_after'), trim($hour), false).' value="'.trim($hour).'">'.trim($hour).'</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['checkout_before'] != 1) { ?>
            <div class="col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="checkout_before"><?php echo esc_attr(homey_option('ad_check_out_before')).homey_req('checkout_before'); ?></label>
                    <select name="checkout_before" class="selectpicker" <?php homey_required('checkout_before'); ?> id="checkout_before" data-live-search="false" title="<?php echo esc_attr(homey_option('ad_text_select')); ?>">
                        <?php 
                        foreach ($checkin_after_before_array as $hour) {
                            echo '<option '.selected( homey_get_field_meta('checkout_before'), trim($hour), false).' value="'.trim($hour).'">'.trim($hour).'</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>

        <div class="row">

            <!--Smoking-->
            <?php if($hide_fields['smoking_allowed'] != 1) { ?>
            <div class="col-sm-6 col-xs-12">
                <label class="label-condition"><?php echo esc_attr(homey_option('ad_smoking_allowed')); ?>?</label>
            </div>
            <div class="col-sm-6 col-xs-12">
                <div class="row">
                    <div class="col-sm-6 col-xs-6">
                        <div class="form-group">
                            <label class="control control--radio radio-tab">
                                <input <?php checked($smoke, '1'); ?> name="smoke" value="1" type="radio">
                                <span class="control-text"><?php echo esc_attr(homey_option('ad_text_yes')); ?></span>
                                <span class="control__indicator"></span>
                                <span class="radio-tab-inner"></span>
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-6">
                        <div class="form-group">
                            <label class="control control--radio radio-tab">
                                <input <?php checked($smoke, '0'); ?> name="smoke" value="0" type="radio">
                                <span class="control-text"><?php echo esc_attr(homey_option('ad_text_no')); ?></span>
                                <span class="control__indicator"></span>
                                <span class="radio-tab-inner"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!--Pets-->
            <?php if($hide_fields['pets_allowed'] != 1) { ?>
            <div class="col-sm-6 col-xs-12">
                <label class="label-condition"><?php echo esc_attr(homey_option('ad_pets_allowed')); ?>?</label>
            </div>
            <div class="col-sm-6 col-xs-12">
                <div class="row">
                    <div class="col-sm-6 col-xs-6">
                        <div class="form-group">
                            <label class="control control--radio radio-tab">
                                <input name="pets" <?php checked($pets, '1'); ?> value="1" type="radio">
                                <span class="control-text"><?php echo esc_attr(homey_option('ad_text_yes')); ?></span>
                                <span class="control__indicator"></span>
                                <span class="radio-tab-inner"></span>
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-6">
                        <div class="form-group">
                            <label class="control control--radio radio-tab">
                                <input name="pets" <?php checked($pets, '0'); ?> value="0" type="radio">
                                <span class="control-text"><?php echo esc_attr(homey_option('ad_text_no')); ?></span>
                                <span class="control__indicator"></span>
                                <span class="radio-tab-inner"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!--Party-->
            <?php if($hide_fields['party_allowed'] != 1) { ?>
            <div class="col-sm-6 col-xs-12">
                <label class="label-condition"><?php echo esc_attr(homey_option('ad_party_allowed')); ?>?</label>
            </div>
            <div class="col-sm-6 col-xs-12">
                <div class="row">
                    <div class="col-sm-6 col-xs-6">
                        <div class="form-group">
                            <label class="control control--radio radio-tab">
                                <input name="party" <?php checked($party, '1'); ?> value="1" type="radio">
                                <span class="control-text"><?php echo esc_attr(homey_option('ad_text_yes')); ?></span>
                                <span class="control__indicator"></span>
                                <span class="radio-tab-inner"></span>
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-6">
                        <div class="form-group">
                            <label class="control control--radio radio-tab">
                                <input name="party" <?php checked($party, '0'); ?> value="0" type="radio">
                                <span class="control-text"><?php echo esc_attr(homey_option('ad_text_no')); ?></span>
                                <span class="control__indicator"></span>
                                <span class="radio-tab-inner"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!--Children-->
            <?php if($hide_fields['children_allowed'] != 1) { ?>
            <div class="col-sm-6 col-xs-12">
                <label class="label-condition"><?php echo esc_attr(homey_option('ad_children_allowed')); ?>?</label>
            </div>
            <div class="col-sm-6 col-xs-12">
                <div class="row">
                    <div class="col-sm-6 col-xs-6">
                        <div class="form-group">
                            <label class="control control--radio radio-tab">
                                <input name="children" <?php checked($children, '1'); ?> value="1" type="radio">
                                <span class="control-text"><?php echo esc_attr(homey_option('ad_text_yes')); ?></span>
                                <span class="control__indicator"></span>
                                <span class="radio-tab-inner"></span>
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-6">
                        <div class="form-group">
                            <label class="control control--radio radio-tab">
                                <input name="children" <?php checked($children, '0'); ?> value="0" type="radio">
                                <span class="control-text"><?php echo esc_attr(homey_option('ad_text_no')); ?></span>
                                <span class="control__indicator"></span>
                                <span class="radio-tab-inner"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>


        <?php if($hide_fields['additional_rules'] != 1) { ?>
        <div class="row">
            <div class="col-sm-12 col-sm-12">
                <div class="form-group">
                    <label for="additional_rules"><?php echo esc_attr(homey_option('ad_add_rules_info_optional')); ?></label>
                    <textarea name="additional_rules" class="form-control" id="rules" rows="3"><?php echo ''.($additional_rules); ?></textarea>
                </div>
            </div>
        </div>
        <?php } ?>
        
    </div>
</div>

