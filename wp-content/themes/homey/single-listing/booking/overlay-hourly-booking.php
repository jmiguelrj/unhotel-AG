<?php 
global $post, $current_user, $homey_prefix, $homey_local;
wp_get_current_user();

$listing_id = $post->ID;
$price_per_night = get_post_meta($listing_id, $homey_prefix.'night_price', true);
$instant_booking = get_post_meta($listing_id, $homey_prefix.'instant_booking', true);
$start_hour = get_post_meta($listing_id, $homey_prefix.'start_hour', true);
$end_hour = get_post_meta($listing_id, $homey_prefix.'end_hour', true);
$offsite_payment = homey_option('off-site-payment');

$listing_price = homey_get_price();

$rating = homey_option('rating');
$total_rating = get_post_meta( $listing_id, 'listing_total_rating', true );

$key = '';
$userID      =   $current_user->ID;
$fav_option = 'homey_favorites-'.$userID;
$fav_option = get_option( $fav_option );
if( !empty($fav_option) ) {
    $key = array_search($post->ID, $fav_option);
}

$price_separator = homey_option('currency_separator');

if( $key != false || $key != '' ) {
    $favorite = $homey_local['remove_favorite'];
    $heart = 'fa-heart';
} else {
    $favorite = $homey_local['add_favorite'];
    $heart = 'fa-heart-o';
}

if($instant_booking) { 
	$btn_name = esc_html__('Instant Booking', 'homey');
} else {
	$btn_name = esc_html__('Request to Book', 'homey');
}
$booking_or_contact_theme_options = homey_option('what_to_show');
$booking_or_contact = homey_get_listing_data('booking_or_contact');
if(empty($booking_or_contact)) {
    $what_to_show = $booking_or_contact_theme_options;
} else {
    $what_to_show = $booking_or_contact;
}

if(empty($start_hour)) {
	$start_hour = '01:00';
}

if(empty($end_hour)) {
	$end_hour = '24:00';
}

$prefilled = homey_get_dates_for_booking();
$pre_start_hour = $prefilled['start'];
$pre_end_hour = $prefilled['end'];

$start_hours_list = '';
$end_hours_list = '';
$start_hour = strtotime($start_hour);
$end_hour = strtotime($end_hour);
for ($halfhour = $start_hour; $halfhour <= $end_hour; $halfhour = $halfhour+30*60) {
    $start_hours_list .= '<option '.selected($pre_start_hour, date('H:i',$halfhour)).' value="'.date('H:i',$halfhour).'">'.date(homey_time_format(),$halfhour).'</option>';
    $end_hours_list .= '<option '.selected($pre_end_hour, date('H:i',$halfhour)).' value="'.date('H:i',$halfhour).'">'.date(homey_time_format(),$halfhour).'</option>';
}

$no_login_needed_for_booking = homey_option('no_login_needed_for_booking');

?>
<div id="overlay-booking-module" class="overlay-booking-module overlay-hourly-booking-module overlay-contentscale">
	<div class="overlay-search-title"><?php echo esc_html__('Request to book', 'homey'); ?></div>
	<button type="button" class="overlay-booking-module-close btn-blank"><i class="fa fa-times" aria-hidden="true"></i></button> 
	<div class="sidebar-booking-module">
		<div class="block">
			<div class="sidebar-booking-module-body">
				<div class="homey_notification search-wrap search-banner">

					<div id="single-listing-date-range" class="search-date-range">

						<div class="search-date-range-arrive search-date-hourly-arrive">
							<input name="arrive" value="<?php echo esc_attr($prefilled['arrive']); ?>" readonly type="text" class="form-control check_in_date" autocomplete="off" placeholder="<?php echo esc_attr(homey_option('srh_arrive_label')); ?>">
						</div>

						<div id="single-booking-search-calendar" class="search-calendar hourly-js-mobile single-listing-booking-calendar-js clearfix" style="display: none;">
							<?php homeyHourlyAvailabilityCalendar(); ?>	
							
							<div class="calendar-navigation custom-actions">
		                        <button class="listing-cal-prev btn btn-action pull-left disabled"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
		                        <button class="listing-cal-next btn btn-action pull-right"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
		                    </div><!-- calendar-navigation -->                
						</div>
					</div>

					<div class="search-hours-range clearfix">
						<div class="search-hours-range-left">
							<select name="start_hour" id="start_hour_overlay" class="selectpicker start_hour" data-live-search="true" title="<?php echo homey_option('srh_starts_label'); ?>">
								<option value=""><?php echo homey_option('srh_starts_label'); ?></option>
								<?php echo ''.$start_hours_list; ?>
							</select>
						</div>
						<div class="search-hours-range-right">
							<select name="end_hour" id="end_hour_overlay" class="selectpicker end_hour" data-live-search="true" title="<?php echo homey_option('srh_ends_label'); ?>">
								<option value=""><?php echo homey_option('srh_ends_label'); ?></option>
								<?php echo ''.$end_hours_list; ?>
							</select>
						</div>
					</div>
					
					<?php get_template_part('single-listing/booking/guests-overlay-hourly'); ?>

					<?php get_template_part('single-listing/booking/extra-prices'); ?>

					<?php //if(!$instant_booking) { ?>
					<div class="search-message">
						<textarea name="guest_message" class="form-control" rows="3" placeholder="<?php echo esc_html__('Introduce yourself to the host', 'homey'); ?>"></textarea>
					</div>
					<?php //} ?>

					<div class="homey_preloader">
						<?php get_template_part('template-parts/spinner'); ?>
					</div>	

					<div id="homey_booking_cost" class="payment-list"></div>	

					
					<?php if($instant_booking && $offsite_payment == 0) { ?>
						<button id="instance_hourly_reservation_mobile" type="button" class="btn btn-full-width btn-primary"><?php echo esc_html__('Instant Booking', 'homey'); ?></button>
					<?php } else { ?>
                        <?php if(!is_user_logged_in() && $no_login_needed_for_booking == 'yes'){ ?>
                            <div class="new_reser_request_user_email ">
                                <input id="new_reser_request_user_email" name="new_reser_request_user_email" required="required" value="<?php echo esc_attr($prefilled['new_reser_request_user_email']); ?>" type="email" class="form-control new_reser_request_user_email" placeholder="<?php echo esc_html__('Your email', 'homey'); ?>">
                            </div>
                        <?php } ?>
						<button id="request_hourly_reservation_mobile" type="button" class="btn btn-full-width btn-primary"><?php echo esc_html__('Request to Book', 'homey'); ?></button>
						<div class="text-center text-small"><i class="fa fa-info-circle"></i> <?php echo esc_html__("You won't be charged yet", 'homey'); ?></div>
					<?php } ?>

				</div><!-- block-body-sidebar -->
			</div><!-- sidebar-booking-module-body -->
		</div><!-- block -->
	</div><!-- sidebar-booking-module -->
</div><!-- overlay-booking-module -->

<div class="overlay-booking-btn visible-sm visible-xs">
	<div class="pull-left">
		<div class="overlay-booking-price">
			<?php echo homey_formatted_price($listing_price, false, false); ?><span><?php echo esc_attr($price_separator); ?><?php echo homey_get_price_label();?></span>
		</div>
		<?php 
        if($rating && ($total_rating != '' && $total_rating != 0 ) ) { ?>
		<div class="list-inline rating">
			<?php echo homey_get_review_stars($total_rating, false, true); ?>
		</div>
		<?php } ?>
	</div>
	
	<?php
	if( homey_affiliate_booking_link() ) { ?>
		
		<a href="<?php echo homey_affiliate_booking_link(); ?>" target="_blank" class="trigger-overlay btn btn-primary"><?php echo esc_html__(
			'Book Now', 'homey'); ?></a>

	<?php	
	} else { 
		if($what_to_show == 'booking_form') { ?>
	        <button id="trigger-overlay-booking-form" class="trigger-overlay btn btn-primary" type="button"><?php echo esc_attr($btn_name); ?></button>
	    <?php     
	    } elseif($what_to_show == 'contact_form') { ?>
	        <button type="button" data-toggle="modal" data-target="#modal-contact-host" class="trigger-overlay btn btn-primary"><?php echo esc_html__('Request Information', 'homey'); ?></button>
	    <?php    
	    }elseif($what_to_show == 'contact_form_to_guest') {
            if(is_user_logged_in()){ ?>
                    <button id="trigger-overlay-booking-form" class="trigger-overlay btn btn-primary" type="button"><?php echo esc_attr($btn_name); ?></button>
            <?php }else{ ?>
                    <button type="button" data-toggle="modal" data-target="#modal-contact-host" class="trigger-overlay btn btn-primary"><?php echo esc_html__('Request Information', 'homey'); ?></button>
            <?php }
        }
	}
	?>
</div>

