<?php
if(!function_exists('homeySearchCalendar')) {
	function homeySearchCalendar() {

		$numberOfMonths = 1;
		$timeNow  = current_time( 'timestamp' );
	    $now = date('Y-m-d');
	    $date = new DateTime();
	    
	    $currentMonth = gmdate('m', $timeNow);
	    $currentYear  = gmdate('Y', $timeNow);             
	    $unixMonth = mktime(0, 0 , 0, $currentMonth, 1, $currentYear);

        while( $numberOfMonths <= homey_calendar_months() ) {
         	
         	homeySearchGenerateMonth( $numberOfMonths, $unixMonth, $currentMonth, $currentYear );
          
            $date->modify( 'first day of next month' );
            $currentMonth = $date->format( 'm' );
            $currentYear  = $date->format( 'Y' );
            $unixMonth = mktime(0, 0 , 0, $currentMonth, 1, $currentYear);

            $numberOfMonths++;
        }
	    
	}
}

if(!function_exists('homeySearchDaysInMonth')) {
	function homeySearchDaysInMonth($month = null, $year = null) {
	     
	    $timeNow  = current_time( 'timestamp' );    
	    if(null == ($year)) {
	        $year = gmdate('Y', $timeNow);
	    }

	    if(null == ($month)){
	        $month = gmdate('m', $timeNow);
	    }

	    $unixMonth = mktime(0, 0 , 0, $month, 1, $year);
	         
	    return date('t', $unixMonth);;
	}
}

if(!function_exists('homeySearchGenerateMonth')) {
	function homeySearchGenerateMonth( $numberOfMonths, $unixMonth, $currentMonth, $currentYear ) {
		global $wpdb, $post, $wp_locale;

		$bookedDays  = get_post_meta($post->ID, 'reservation_dates',true  ); 
		$pending_dates  = get_post_meta($post->ID, 'reservation_pending_dates',true  );

		if(empty($bookedDays)) {
			$bookedDays = array();
		}

		if(empty($pending_dates)) {
			$pending_dates = array(); 
		}
        

		$daysInMonth = homeySearchDaysInMonth($currentMonth, $currentYear);
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

        if(homey_search_type() == 'per_hour') {
        	$main_class = 'hourly-calendar';

        	if( $numberOfMonths > 1 ) {
	            $style = 'style="display:none;"';
	        }
	        $main_search_calendar_wrap_class = 'main-search-hourly-calendar-wrap';

        } else {

        	if( $numberOfMonths % 2 == 1 ) {
				$main_class = 'left-calendar';
			} else {
				$main_class = 'right-calendar';
			}

			if( $numberOfMonths > 2 ) {
	            $style = 'style="display:none;"';
	        }

	        $main_search_calendar_wrap_class = 'main-search-calendar-wrap';
        }
		

		
        

		for ( $wCount = 0; $wCount <= 6; $wCount++ ) {
			$weekArray[] = $wp_locale->get_weekday(($wCount + $weekBegins)%7);
		}

		foreach ( $weekArray as $weekDay ) {
			$dayName = (true == $weekDayInitial) ? $wp_locale->get_weekday_initial($weekDay) : $wp_locale->get_weekday_abbrev($weekDay);
			$weekDays .= '<li data-dayName = "'.esc_attr($weekDay).'">'.esc_attr($dayName).'</li>';
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
                $dayClass = "day-disabled past-day";
            } else {
                $dayClass = "future-day";
            }

            $dateTimeStamp  = new DateTime($currentYear.'-'.$currentMonth.'-'.$day);
        	$dateTimeStamp = $dateTimeStamp->getTimestamp();

        	$homey_get_formatted_date = homey_get_formatted_date($currentYear, $currentMonth, $day);

	        if ( $day == gmdate('j', current_time('timestamp')) && $currentMonth == gmdate('m', current_time('timestamp')) && $currentYear == gmdate('Y', current_time('timestamp')) ) {

            	$monthDays .= '<li data-timestamp="'.esc_attr($dateTimeStamp).'" data-formatted-date="'.$homey_get_formatted_date.'" class="current-month current-day '.esc_attr($dayClass).'"><span class="day-number">'.esc_attr($day).'</span></li>';
            } else {
 
	            $monthDays .= '<li data-timestamp="'.esc_attr($dateTimeStamp).'" data-formatted-date="'.$homey_get_formatted_date.'" class="current-month '.esc_attr($dayClass).'">
	            	<span class="day-number">'.esc_attr($day).'</span>
	            </li>';
	        }
            
		}

        $output = '<div class="'.esc_attr($main_search_calendar_wrap_class).' '.esc_attr($main_class).'" data-month = "'.esc_attr($numberOfMonths).'" '.$style.'>';

        	$output .= '<div class="month clearfix">';
            	$output .= '<h4>'.date_i18n("F", mktime(0, 0, 0, $currentMonth, 10)).' <span>'.esc_attr($currentYear).'</span></h4>';
            $output .= '</div>';


            $output .= '<ul class="weekdays clearfix">';
                $output .= $weekDays;
            $output .= '</ul>';

            $output .= '<ul class="days clearfix">';
            	$output .= $prevMonthDays;

            	$output .= $monthDays;

            $output .= '</ul>';


        $output .= '</div>'; // end main div

        $output_escaped = $output;
        echo ''.$output_escaped;

	} //homeySearchGenerateMonth
} // function_exists


$homey_search_type = homey_search_type();
if($homey_search_type == 'per_hour') {
	$search_calendar_main = 'search-hourly-calendar-main';
} else {
    $search_calendar_main = 'search-calendar-main';
}
?>

<div class="search-calendar <?php echo esc_attr($search_calendar_main); ?> clearfix">
	<div class="calendar-arrow"></div>
	<?php homeySearchCalendar(); ?>

	<button type="button" style="z-index: 99;" class="btn-link btn-clear-calendar"><?php echo esc_html__('Clear', 'homey'); ?></button>

	<div class="calendar-navigation custom-actions">
		<button class="search-cal-prev btn btn-action pull-left disabled"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
		<button class="search-cal-next btn btn-action pull-right"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
	</div><!-- calendar-navigation -->
</div>
<!-- On mobile: display this button below when  the user selected arrival and depart dates -->
<button style="display: none;" class="btn btn-primary search-calendar-btn"><?php echo esc_html__('Done', 'homey'); ?></button>