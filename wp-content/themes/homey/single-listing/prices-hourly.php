<?php
global $post, $homey_local, $homey_prefix, $hide_labels;

$hour_price = homey_get_listing_data('hour_price');
$weekends_price = homey_get_listing_data('hourly_weekends_price');
$weekends_days = homey_get_listing_data('weekends_days');
$min_book_hours = homey_get_listing_data('min_book_hours');
$security_deposit = homey_get_listing_data('security_deposit');
$cleaning_fee = homey_get_listing_data('cleaning_fee');
$cleaning_fee_type = homey_get_listing_data('cleaning_fee_type');
$city_fee = homey_get_listing_data('city_fee');
$city_fee_type = homey_get_listing_data('city_fee_type');
$additional_guests_price = homey_get_listing_data('additional_guests_price');
$allow_additional_guests = homey_get_listing_data('allow_additional_guests');

if($allow_additional_guests == 'yes') {
    $allow_additional_guests = esc_html__('Yes', 'homey');
} else {
    $allow_additional_guests = esc_html__('No', 'homey');
}

$cleaning_fee_period = $city_fee_period = '';

if($cleaning_fee_type == 'per_stay') {
    $cleaning_fee_period = esc_html__('Per Stay', 'homey');
} elseif($cleaning_fee_type == 'daily') {
    $cleaning_fee_period = esc_html__('Hourly', 'homey');
}

if($city_fee_type == 'per_stay') {
    $city_fee_period = esc_html__('Per Stay', 'homey');
} elseif($city_fee_type == 'daily') {
    $city_fee_period = esc_html__('Hourly', 'homey');
}

if($weekends_days == 'sat_sun') {
    $weekendDays = esc_html__('Sat & Sun', 'homey');

} elseif($weekends_days == 'fri_sat') {
    $weekendDays = esc_html__('Fri & Sat', 'homey');

} elseif($weekends_days == 'fri_sat_sun') {
    $weekendDays = esc_html__('Fri, Sat & Sun', 'homey');
}

?>
<div id="price-section" class="price-section">
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div class="block-left">
                    <h3 class="title"><?php echo esc_attr(homey_option('sn_prices_heading')); ?></h3>
                </div><!-- block-left -->
                <div class="block-right">
                    <ul class="detail-list detail-list-2-cols">
                        <?php if(!empty($hour_price) && $hide_labels['sn_hourly_label'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_hourly_label'));?>: 
                            <strong><?php echo homey_formatted_price($hour_price, false); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($weekends_price) && $hide_labels['sn_weekends_label'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_weekends_label'));?> (<?php echo esc_attr($weekendDays); ?>): 
                            <strong><?php echo homey_formatted_price($weekends_price, false); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($security_deposit) && $hide_labels['sn_security_deposit_label'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_security_deposit_label'));?>: 
                            <strong><?php echo homey_formatted_price($security_deposit, true); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($additional_guests_price) && $hide_labels['sn_addinal_guests_label'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_addinal_guests_label'));?>: 
                            <strong><?php echo homey_formatted_price($additional_guests_price, false); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($allow_additional_guests) && $hide_labels['sn_allow_additional_guests'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_allow_additional_guests'));?>: 
                            <strong><?php echo esc_attr($allow_additional_guests); ?></strong>
                        </li>
                        <?php } ?>

                        <?php if(!empty($cleaning_fee) && $hide_labels['sn_cleaning_fee'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_cleaning_fee'));?>: 
                            <strong><?php echo homey_formatted_price($cleaning_fee, true); ?></strong> <?php echo esc_attr($cleaning_fee_period); ?>
                        </li>
                        <?php } ?>

                        <?php if(!empty($city_fee) && $hide_labels['sn_city_fee'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_city_fee'));?>: 
                            <strong><?php echo homey_formatted_price($city_fee, true); ?></strong> <?php echo esc_attr($city_fee_period); ?>
                        </li>
                        <?php } ?>

                        <?php if(!empty($min_book_hours) && $hide_labels['sn_min_no_of_hours'] != 1) { ?>
                        <li>
                            <i class="fa fa-angle-right" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_min_no_of_hours'));?>: 
                            <strong><?php echo esc_attr($min_book_hours); ?></strong>
                        </li>
                        <?php } ?>

                    </ul>
                </div><!-- block-right -->
            </div><!-- block-body -->

            <?php get_template_part('single-listing/extra-prices'); ?>
        </div><!-- block-section -->
    </div><!-- block -->
</div>