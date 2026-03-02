<?php
global $post, $homey_prefix, $homey_local, $hide_labels;
$min_book_days  = homey_get_listing_data('min_book_days');
$max_book_days  = homey_get_listing_data('max_book_days');

if($min_book_days > 1) {
    $min_book_days_label = homey_option('sn_nights_label');
} else {
    $min_book_days_label = homey_option('sn_night_label');
}
?>
<div id="availability-section" class="availability-section">
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div class="block-left">
                    <h3 class="title"><?php echo esc_attr(homey_option('sn_availability_label')); ?></h3>
                </div><!-- block-left -->
                
                <?php if($hide_labels['sn_min_stay_is'] != 1 || $hide_labels['sn_max_stay_is'] != 1) { ?>
                <div class="block-right">
                    <ul class="detail-list detail-list-2-cols">
                        <?php if($hide_labels['sn_min_stay_is'] != 1 && !empty($min_book_days)) { ?>
                        <li>
                            <i class="fa fa-calendar-o" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_min_stay_is'));?> <strong><?php echo esc_attr($min_book_days); ?> <?php echo esc_attr(homey_get_availability_label($min_book_days));?></strong>
                        </li>
                        <?php } ?>

                        <?php if($hide_labels['sn_max_stay_is'] != 1 && !empty($max_book_days)) { ?>
                        <li>
                            <i class="fa fa-calendar-o" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_max_stay_is'));?> <strong><?php echo esc_attr($max_book_days); ?> <?php echo esc_attr(homey_get_availability_label($max_book_days));?></strong>
                        </li>
                        <?php } ?>
                    </ul>
                </div><!-- block-right -->
                <?php } ?>

            </div><!-- block-body -->
            <div class="block-availability-calendars">
                <div class="single-listing-calendar search-calendar clearfix">

                    <div class="calendar-arrow"></div>

                    <?php
                        $homey_booking_type = homey_booking_type_by_id(get_the_ID());
                        if($homey_booking_type == "per_day_date"){
                            homeyAvailabilityCalendarDayDate();
                        }else{
                            homeyAvailabilityCalendar();
                        }
                     ?>

                    <div class="availability-notes">
                        <ul class="list-inline">
                            <li class="day-available">
                                <span><?php echo esc_attr(homey_option('sn_avail_label')); ?></span>
                            </li>
                            <li class="day-pending">
                                <span><?php echo esc_attr(homey_option('sn_pending_label')); ?></span>
                            </li>
                            <li class="day-booked">
                                <span><?php echo esc_attr(homey_option('sn_booked_label')); ?></span>
                            </li>
                        </ul>
                    </div>

                    <div class="calendar-navigation custom-actions">
                        <button class="listing-cal-prev btn btn-action pull-left disabled"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
                        <button class="listing-cal-next btn btn-action pull-right"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
                    </div><!-- calendar-navigation -->

                </div>
            </div><!-- block-availability-calendars -->
        </div><!-- block-section -->
    </div><!-- block -->
</div>