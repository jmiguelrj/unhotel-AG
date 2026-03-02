<?php
/**
 * Template Name1: Unit Testing
 */
get_header();


$listing_id = 332;
$custom_price_array = get_post_meta( $listing_id,'homey_custom_price_array', true );

$check_in_date = '2022-03-13';
$check_out_date = '2022-06-13';

/*echo '<pre>';
print_r($custom_price_array);*/

//$test = homey_get_per_month_custom_price($listing_id, $custom_price_array, $check_in_date, $check_out_date);


function homey_get_per_month_custom_price( $listing_id, $custom_price_array, $check_in_date, $check_out_date ) {
    
    if (empty($custom_price_array)) {
        return;
    }

    $month_and_days = homey_get_month_name_and_days( $listing_id, $custom_price_array, $check_in_date, $check_out_date );
    $total_custom_price = 0;
    foreach( $month_and_days as $month => $days ) {
        
        if( isset($custom_price_array[$month]) && $custom_price_array[$month] != "" ) {
            $month_price = $custom_price_array[$month]['price'];
        } else {
            $month_price = get_post_meta( $listing_id, 'homey_night_price', true );
        }

        $month_calculated_price = $month_price/30 * $days;

        echo $month.' = '.$month_price.' = '.$days.' = '.$month_calculated_price.'<br>';

        $total_custom_price = $total_custom_price + $month_calculated_price;
    }

    echo 'Total = ' .$total_custom_price;
}


//$month_days = homey_get_month_name_and_days($listing_id, $custom_price_array, $check_in_date, $check_out_date);

function homey_get_month_name_and_days($listing_id, $custom_price_array, $check_in_date, $check_out_date) {

    $array_months = array();

    $check_in        =  new DateTime($check_in_date);
    $check_in_unix   =  $check_in->getTimestamp();
    $check_in_unix_first_day   =  $check_in->getTimestamp();
    $check_out       =  new DateTime($check_out_date);
    $check_out_unix  =  $check_out->getTimestamp();

    while ($check_in_unix < $check_out_unix) {

        $month_name = date( 'F', $check_in_unix );

        $array_months[] = $month_name;

        $check_in->modify('tomorrow');
        $check_in_unix =   $check_in->getTimestamp();
    }

    $new_array = array_count_values($array_months);

    return $new_array;
}



get_footer(); 
?>