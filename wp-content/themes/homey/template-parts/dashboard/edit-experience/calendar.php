<?php 
global $homey_local;

$class = '';
if(isset($_GET['tab']) && $_GET['tab'] == 'calendar') {
    $class = 'in active';
}

if(!function_exists('homeyEditListingCalendar')) {
    function homeyEditListingCalendar() {

        $numberOfMonths = 1;
        $timeNow  = current_time( 'timestamp' );
        $now = date('Y-m-d');
        $date = new DateTime();
        
        $currentMonth = gmdate('m', $timeNow);
        $currentYear  = gmdate('Y', $timeNow);             
        $unixMonth = mktime(0, 0 , 0, $currentMonth, 1, $currentYear);

        while( $numberOfMonths <= homey_calendar_months() ) {
            
            homeyEditListingGenerateMonth( $numberOfMonths, $unixMonth, $currentMonth, $currentYear );
          
            $date->modify( 'first day of next month' );
            $currentMonth = $date->format( 'm' );
            $currentYear  = $date->format( 'Y' );
            $unixMonth = mktime(0, 0 , 0, $currentMonth, 1, $currentYear);

            $numberOfMonths++;
        }
        
    }
}

if(!function_exists('homeyEditListingDaysInMonth')) {
    function homeyEditListingDaysInMonth($month = null, $year = null) {
         
        $timeNow  = current_time( 'timestamp' );    
        if(null == ($year)) {
            $year = gmdate('Y', $timeNow);
        }

        if(null == ($month)){
            $month = gmdate('m', $timeNow);
        }


        $unixMonth = mktime(0, 0 , 0, $month, 1, $year);

        return date('t', $unixMonth);
    }
}

if(!function_exists('homeyEditListingGenerateMonth')) {
    function homeyEditListingGenerateMonth( $numberOfMonths, $unixMonth, $currentMonth, $currentYear ) {
        global $homey_local, $wpdb, $post, $wp_locale;

        $listing_id = isset($_GET['edit_listing']) ? $_GET['edit_listing'] : '';

        $bookedDays  = get_post_meta($listing_id, 'reservation_dates',true  ); 
        $pending_dates  = get_post_meta($listing_id, 'reservation_pending_dates',true  );
        $unavailable_dates  = get_post_meta($listing_id, 'reservation_unavailable',true  );

        $reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');

        if(empty($bookedDays)) {
            $bookedDays = array();
        }

        if(empty($pending_dates)) {
            $pending_dates = array(); 
        }

        if(empty($unavailable_dates)) {
            $unavailable_dates = array(); 
        }
        

        $daysInMonth = homeyEditListingDaysInMonth($currentMonth, $currentYear);
        $weekBegins = intval(1);
        $weekArray = array();
        $weekDays = '';
        $monthDays = '';
        $weekDayInitial = true;
        $prevMonthDays = '';
        $calendar_day_class = '';
        $resv_class = '';
        $resv_start = '';
        $resv_end = '';


        $style = "";
        if( $numberOfMonths > 1 ) {
            $style = 'style="display:none;"';
        }

        for ( $wCount = 0; $wCount <= 6; $wCount++ ) {
            $weekArray[] = $wp_locale->get_weekday(($wCount + $weekBegins)%7);
        }

        foreach ( $weekArray as $weekDay ) {
            $dayName = (true == $weekDayInitial) ? $wp_locale->get_weekday_initial($weekDay) : $wp_locale->get_weekday_abbrev($weekDay);
            $weekDays .= '<li data-dayName = "'.esc_attr($weekDay).'">'.$dayName.'</li>';
        }


        $weekMod = calendar_week_mod(date('w', $unixMonth) - $weekBegins); // Get number of days since the start of the week.
        if( $weekMod != 0 ) {
            for( $wm = 1; $wm <= $weekMod; $wm++ ) {
                $prevMonthDays .= '<li class="prev-month"></li>';
            }
        }

        for ( $day = 1; $day <= $daysInMonth; ++$day ) {
            $timestamp = strtotime( $day.'-'.$currentMonth.'-'.$currentYear);

            $dayClass = '';
            $resv_class='';

            if( $timestamp < (time()-24*60*60) ) {
                $dayClass = "past-day";
            } else {
                $dayClass = "future-day";
            }

            if( array_key_exists($timestamp, $bookedDays) ) {
                $calendar_day_class = 'booked';
                $booked_id = $bookedDays[$timestamp];
                $resv_end=1;
                if($resv_start == 1){
                    $resv_class  = 'reservation_start';
                    $resv_start  = 0;
                }
                
         
                if(is_numeric($booked_id)) {
                    $booked_detail_link = add_query_arg( 'reservation_detail', $booked_id, $reservation_page_link );

                    $day_status = '<span class="day-status"><a href="'.esc_url($booked_detail_link).'">'.esc_attr($homey_local['booking_id_label']).': '.$booked_id.'</a></span>';

                    $resv_renter = get_post_meta($booked_id, 'listing_renter', true);
                    $renter_meta = homey_get_author_by_id('24', '24', 'img-circle', $resv_renter);
                    $day_pic = $renter_meta['photo'];
                } else {
                    $booked_detail_link = '';

                    $day_status = '<span class="day-status"><a>'.$booked_id.'</a></span>';
                    $day_pic = '';
                }

            } elseif( array_key_exists($timestamp, $pending_dates) ) {
                $calendar_day_class = 'pending';
                $pending_id = $pending_dates[$timestamp];
                $resv_end=1;
                if($resv_start == 1){
                    $resv_class  = 'reservation_start';
                    $resv_start  = 0;
                }

                $pending_detail_link = add_query_arg( 'reservation_detail', $pending_id, $reservation_page_link );
                $day_status = '<span class="day-status"><a href="'.esc_url($pending_detail_link).'">'.esc_attr($homey_local['pending_id_label']).': '.$pending_id.'</a></span>';

                $resv_renter = get_post_meta($pending_id, 'listing_renter', true);
                $renter_meta = homey_get_author_by_id('24', '24', 'img-circle', $resv_renter);
                $day_pic = $renter_meta['photo'];

            } elseif(array_key_exists($timestamp, $unavailable_dates)) {
                $calendar_day_class = 'unavailable';
                $day_status = '<span class="day-status">'.esc_attr($homey_local['unavail_label']).'</span>';
                $day_pic = '';
            } else {

                if( $timestamp < (time()-24*60*60) ) {
                    $calendar_day_class = 'unavailable';
                    $day_status = '<span class="day-status"></span>';
                    $resv_class = '';
                } else {
                    $calendar_day_class = 'available';
                    $resv_start=1;
                    if($resv_end===1){
                        $resv_class=' reservation_end ';
                        $resv_end=0;
                    }
                    $day_status = '<span class="day-status">'.esc_attr($homey_local['avail_label']).'</span>';
                }
                $day_pic = '';
                
            }


            if ( $day == gmdate('j', current_time('timestamp')) && $currentMonth == gmdate('m', current_time('timestamp')) && $currentYear == gmdate('Y', current_time('timestamp')) ) {

                $monthDays .= '<li data-formatted-date="'.esc_attr($currentYear).'-'.esc_attr($currentMonth).'-'.esc_attr($day).'" class="current-month '.esc_attr($resv_class).' '.esc_attr($calendar_day_class).' '.esc_attr($dayClass).'">
                <span class="day-number current-day">'.esc_attr($day).'</span>'.$day_status.'</li>';

            } else {
 
                $monthDays .= '<li data-formatted-date="'.esc_attr($currentYear).'-'.esc_attr($currentMonth).'-'.esc_attr($day).'" class="current-month '.esc_attr($resv_class).' '.esc_attr($calendar_day_class).' '.esc_attr($dayClass).'">
                    <span class="day-number">'.esc_attr($day).'</span>
                    '.$day_pic.'
                    '.$day_status.'
                </li>';
            }
            
        } 

        $output = '<div class="homey_month_wrap" data-month = "'.esc_attr($numberOfMonths).'" '.$style.'>';

            $output .= '<div class="month clearfix">';

                $output .= '<h4>'.date_i18n("F", mktime(0, 0, 0, $currentMonth, 10)).'<br>';
                    $output .= '<span>'.esc_attr($currentYear).'</span>';
                $output .= '</h4>';


            $output .= '</div>'; // end month
            
            $output .= '<div class="available-text">'.$homey_local['make_available_text'].'</div>';

            $output .= '<ul class="weekdays clearfix">';
                $output .= $weekDays;
            $output .= '</ul>';

            $output .= '<ul class="days clearfix">';
                $output .= $prevMonthDays;

                $output .= $monthDays;
            $output .= '</ul>';

        $output .= '</div>'; // end homey_month_wrap div

        echo ''.$output;

    } //homeyGenerateMonth
} // function_exists
?>

<div id="calendar-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
    <div class="block-title visible-xs">
            <h3 class="title"><?php echo esc_attr($homey_local['cal_label']); ?></h3>
    </div>
    <div class="block-body">
        <div class="calendar-navigation custom-actions">
            <div class="btn-group">
                <button type="button" class="btn btn-secondary-outlined btn-reserve-period dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?php echo esc_attr($homey_local['sync_ical_label']); ?> <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="#" data-toggle="modal" data-target="#modal-calendar-import"><?php echo esc_attr($homey_local['ical_import']); ?></a></li>
                    <li><a href="#" data-toggle="modal" data-target="#modal-calendar-export"><?php echo esc_attr($homey_local['ical_export']); ?></a></li>
                </ul>
            </div>

            <a class="btn btn-secondary-outlined btn-reserve-period" data-toggle="modal" data-target="#modal-calendar"><?php echo esc_attr($homey_local['reserve_period_label']); ?></a>
            <a class="btn btn-action btn-reserve-period-mobile" data-toggle="modal" data-target="#modal-calendar"><i class="fa fa-cog" aria-hidden="true"></i></a>

            <button class="homey-prev-month btn btn-action disabled"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
            <button class="homey-next-month btn btn-action"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
        </div> 
        
        <div id="property-calendar" class="property-calendar">
            
            <?php echo homeyEditListingCalendar(); ?>
            
        </div>
    </div>
</div>
<?php get_template_part('template-parts/dashboard/edit-listing/modal-calendar'); ?>
<?php get_template_part('template-parts/dashboard/ical-import'); ?>
<?php get_template_part('template-parts/dashboard/ical-export'); ?>
