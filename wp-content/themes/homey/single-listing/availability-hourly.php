<?php
global $post, $homey_prefix, $homey_local, $hide_labels;
$min_book_hours  = homey_get_listing_data('min_book_hours');

if($min_book_hours > 1) {
    $min_book_hours_label = esc_html__('Hours', 'homey');
} else {
    $min_book_hours_label = esc_html__('Hour', 'homey');
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
                        <?php if($hide_labels['sn_min_stay_is'] != 1 && !empty($min_book_hours)) { ?>
                        <li>
                            <i class="fa fa-calendar-o" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_min_stay_is'));?> <strong><?php echo esc_attr($min_book_hours); ?> <?php echo esc_attr($min_book_hours_label);?></strong>
                        </li>
                        <?php } ?>

                        <?php if($hide_labels['sn_max_stay_is'] != 1 && !empty($max_book_days)) { ?>
                        <li>
                            <i class="fa fa-calendar-o" aria-hidden="true"></i> 
                            <?php echo esc_attr(homey_option('sn_max_stay_is'));?> <strong><?php echo esc_attr($max_book_days); ?> <?php echo esc_attr(homey_option('sn_nights_label'));?></strong>
                        </li>
                        <?php } ?>
                    </ul>
                </div><!-- block-right -->
                <?php } ?>

            </div><!-- block-body -->
            <div class="block-availability-calendars">
                <div class="single-listing-calendar search-calendar clearfix">

                    <div class="calendar-arrow"></div>

                    <div id="homey_hourly_calendar"></div>

                </div>
            </div><!-- block-availability-calendars -->
        </div><!-- block-section -->
    </div><!-- block -->
</div>