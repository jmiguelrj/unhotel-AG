<?php
if(!function_exists('homeyHourlyAvailabilityCalendar')) {
	function homeyHourlyAvailabilityCalendar() {

		$numberOfMonths = 1;
		$timeNow  = current_time( 'timestamp' );
	    $now = date('Y-m-d');
	    $date = new DateTime();
	    
	    $currentMonth = gmdate('m', $timeNow);
	    $currentYear  = gmdate('Y', $timeNow);             
	    $unixMonth = mktime(0, 0 , 0, $currentMonth, 1, $currentYear);

        while( $numberOfMonths <= homey_calendar_months() ) {
         	
         	homeyHourlyGenerateMonth( $numberOfMonths, $unixMonth, $currentMonth, $currentYear );
          
            $date->modify( 'first day of next month' );
            $currentMonth = $date->format( 'm' );
            $currentYear  = $date->format( 'Y' );
            $unixMonth = mktime(0, 0 , 0, $currentMonth, 1, $currentYear);

            $numberOfMonths++;
        }
	    
	}
}

if(!function_exists('homeyHourlyDaysInMonth')) {
	function homeyHourlyDaysInMonth($month = null, $year = null) {
	     
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

if(!function_exists('homeyHourlyGenerateMonth')) {
	function homeyHourlyGenerateMonth( $numberOfMonths, $unixMonth, $currentMonth, $currentYear ) {
		global $wpdb, $post, $wp_locale;

		$bookedDays  = get_post_meta($post->ID, 'reservation_dates',true  ); 
		$pending_dates  = get_post_meta($post->ID, 'reservation_pending_dates',true  );

		if(empty($bookedDays)) {
			$bookedDays = array();
		}

		if(empty($pending_dates)) {
			$pending_dates = array(); 
		}
        

		$daysInMonth = homeyHourlyDaysInMonth($currentMonth, $currentYear);
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


		$main_class = 'hourly-calendar';

		$style = "";
        if( $numberOfMonths > 1 ) {
            $style = 'style="display:none;"';
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

            if( array_key_exists($timestamp, $bookedDays) ) {
            	$calendar_day_class = 'day-booked homey-not-available-for-booking';
            	$resv_end=1;
                if($resv_start == 1){
                    $resv_class  = 'reservation_start';
                    $resv_start  = 0;
                }

            } elseif( array_key_exists($timestamp, $pending_dates) ) {
            	$calendar_day_class = 'day-pending homey-not-available-for-booking';
            	$resv_end=1;
                if($resv_start == 1){
                    $resv_class  = 'reservation_start';
                    $resv_start  = 0;
                }

            } else {
				$calendar_day_class = 'day-available';
				$resv_start=1;
                if($resv_end===1){
                    $resv_class=' reservation_end ';
                    $resv_end=0;
                }
            }

            $dateTimeStamp  = new DateTime($currentYear.'-'.$currentMonth.'-'.$day);
        	$dateTimeStamp = $dateTimeStamp->getTimestamp();

            $homey_get_formatted_date = homey_get_formatted_date($currentYear, $currentMonth, $day);

            if ( $day == gmdate('j', current_time('timestamp')) && $currentMonth == gmdate('m', current_time('timestamp')) && $currentYear == gmdate('Y', current_time('timestamp')) ) {

            	$monthDays .= '<li data-timestamp="'.esc_attr($dateTimeStamp).'" data-formatted-date="'.$homey_get_formatted_date.'" class="current-month current-day '.esc_attr($resv_class).' '.esc_attr($calendar_day_class).' '.esc_attr($dayClass).'"><span class="day-number">'.esc_attr($day).'</span></li>';
            } else {
 
	            $monthDays .= '<li data-timestamp="'.esc_attr($dateTimeStamp).'" data-formatted-date="'.$homey_get_formatted_date.'" class="current-month '.esc_attr($resv_class).' '.esc_attr($calendar_day_class).' '.esc_attr($dayClass).'">
	            	<span class="day-number">'.esc_attr($day).'</span>
	            </li>';
	        }
            
		}

        $output = '<div class="single-listing-hourly-calendar-wrap '.esc_attr($main_class).'" data-month = "'.esc_attr($numberOfMonths).'" '.$style.'>';

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

        echo ''.$output;

	} //homeyHourlyGenerateMonth
} // function_exists