<?php
/*-----------------------------------------------------------------------------------*/
// get user define currency from theme options, if empty return default
/*-----------------------------------------------------------------------------------*/
if(!function_exists('homey_get_currency')){
    function homey_get_currency($sup){
        //get default currency from theme options
        $homey_default_currency = homey_option( 'currency_symbol' );
        if(empty($homey_default_currency)){
            $homey_default_currency = esc_html__( '$' , 'homey' );
        }
        if($sup) {
            $homey_default_currency = '<sup>'.$homey_default_currency.'</sup>';
        }
        return $homey_default_currency;
    }
}

if(!function_exists('homey_simple_currency_format')) {
    function homey_simple_currency_format($price) {
        $site_currency = homey_get_currency($sup = false);
        $currency_position = homey_option( 'currency_position' );
        
        if( $currency_position == 'before' ) {
            return $site_currency . $price;
        } else {
            return $price . $site_currency;
        }
        return '0';
    }
}

/*-----------------------------------------------------------------------------------*/
// Get price
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'homey_get_price' ) ) {

    function homey_get_price() {

        $homey_prefix = 'homey_';
        $homey_site_mode = homey_option('homey_site_mode'); // per_hour, per_day, both
        $day_date_price = get_post_meta( get_the_ID(), $homey_prefix.'day_date_price', true );
        $night_price = get_post_meta( get_the_ID(), $homey_prefix.'night_price', true );
        $hour_price = get_post_meta( get_the_ID(), $homey_prefix.'hour_price', true );
        $booking_type = get_post_meta( get_the_ID(), $homey_prefix.'booking_type', true ); //per_day, per_hour

        if($homey_site_mode == 'per_day_date' || $homey_site_mode == 'per_day' || $homey_site_mode == 'per_week' || $homey_site_mode == 'per_month') {
            $price = $night_price;

        } elseif($homey_site_mode == 'per_hour') {
            $price = $hour_price;

        } elseif($homey_site_mode == 'both') {
            if($booking_type == 'per_day_date') {
                $price = $day_date_price;

            } elseif($booking_type == 'per_day') {
                $price = $night_price;

            } elseif ($booking_type == 'per_hour') {
               $price = $hour_price;

            } else {
                $price = $night_price;
            }
        } else {
            $price = '';
        }
        return $price;
        
    }
}

if ( ! function_exists( 'homey_get_price_by_id' ) ) {

    function homey_get_price_by_id($listing_id) {

        $homey_prefix = 'homey_';
        $homey_site_mode = homey_option('homey_site_mode'); // per_hour, per_day, both
        $day_date_price = get_post_meta( $listing_id, $homey_prefix.'day_date_price', true );
        $night_price = get_post_meta( $listing_id, $homey_prefix.'night_price', true );
        $hour_price = get_post_meta( $listing_id, $homey_prefix.'hour_price', true );
        $booking_type = get_post_meta( $listing_id, $homey_prefix.'booking_type', true ); //per_day, per_hour

        if($homey_site_mode == 'per_day_date' || $homey_site_mode == 'per_day' || $homey_site_mode == 'per_week' || $homey_site_mode == 'per_month') {
            $price = $night_price;

        } elseif($homey_site_mode == 'per_hour') {
            $price = $hour_price;

        } elseif($homey_site_mode == 'both') {
            if($booking_type == 'per_day_date') {
                $price = $day_date_price;

            } elseif($booking_type == 'per_day') {
                $price = $night_price;

            } elseif ($booking_type == 'per_hour') {
               $price = $hour_price;

            } else {
                $price = $night_price;
            }
        } else {
            $price = '';
        }
        return $price;
        
    }
}

/*-----------------------------------------------------------------------------------*/
// Get price label
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'homey_get_price_label' ) ) {

    function homey_get_price_label($number = 1) {

        $homey_prefix = 'homey_';
        $homey_site_mode = homey_option('homey_site_mode'); // per_hour, per_day, both
        $booking_type = get_post_meta( get_the_ID(), $homey_prefix.'booking_type', true ); //per_day, per_hour
        $price_postfix = get_post_meta( get_the_ID(), $homey_prefix.'price_postfix', true );
        $price = '';

        if(!empty($price_postfix)) {
            $price = $price_postfix;
        } else {
            if($homey_site_mode == 'per_day_date') {
                $price = $number > 1 ? homey_option('glc_day_dates_label') : homey_option('glc_day_date_label');

            } elseif($homey_site_mode == 'per_day') {
                $price = $number > 1 ? homey_option('glc_day_nights_label') : homey_option('glc_day_night_label');

            } elseif($homey_site_mode == 'per_hour') {
                $price = $number > 1 ? homey_option('glc_hours_label') : homey_option('glc_hour_label');

            } elseif($homey_site_mode == 'per_week') {
                $price = $number > 1 ? homey_option('glc_weeks_label') : homey_option('glc_week_label');

            } elseif($homey_site_mode == 'per_month') {
                $price = $number > 1 ? homey_option('glc_months_label') : homey_option('glc_month_label');

            } elseif($homey_site_mode == 'both') {
                if($booking_type == 'per_day_date') {
                    $price = $number > 1 ? homey_option('glc_day_dates_label') : homey_option('glc_day_date_label');

                } elseif($booking_type == 'per_day') {
                    $price = $number > 1 ? homey_option('glc_day_nights_label') : homey_option('glc_day_night_label');

                } elseif ($booking_type == 'per_hour') {
                    $price = $number > 1 ? homey_option('glc_hours_label') : homey_option('glc_hour_label');

                } elseif ($booking_type == 'per_week') {
                    $price = $number > 1 ? homey_option('glc_weeks_label') : homey_option('glc_week_label');

                } elseif ($booking_type == 'per_month') {
                    $price = $number > 1 ? homey_option('glc_months_label') : homey_option('glc_month_label');

                } else {
                    $price = $number > 1 ? homey_option('glc_day_nights_label') : homey_option('glc_day_night_label');

                }
            } else {
                $price = '';
            }
        }
        return $price;
        
    }
}

if ( ! function_exists( 'homey_get_availability_label' ) ) {

    function homey_get_availability_label($number = 1) {

        $homey_prefix = 'homey_';
        $homey_site_mode = homey_option('homey_site_mode'); // per_hour, per_day, both
        $booking_type = get_post_meta( get_the_ID(), $homey_prefix.'booking_type', true ); //per_day, per_hour;
        $price = '';

        if($homey_site_mode == 'per_day_date') {
            $price = $number > 1 ? homey_option('glc_day_dates_label') : homey_option('glc_day_date_label');

        } elseif($homey_site_mode == 'per_day') {
            $price = $number > 1 ? homey_option('glc_day_nights_label') : homey_option('glc_day_night_label');

        } elseif($homey_site_mode == 'per_hour') {
            $price = $number > 1 ? homey_option('glc_hours_label') : homey_option('glc_hour_label');

        } elseif($homey_site_mode == 'per_week') {
            $price = $number > 1 ? homey_option('glc_weeks_label') : homey_option('glc_week_label');

        } elseif($homey_site_mode == 'per_month') {
            $price = $number > 1 ? homey_option('glc_months_label') : homey_option('glc_month_label');

        } elseif($homey_site_mode == 'both') {
            if($booking_type == 'per_day_date') {
                $price = $number > 1 ? homey_option('glc_day_dates_label') : homey_option('glc_day_date_label');

            } elseif($booking_type == 'per_day') {
                $price = $number > 1 ? homey_option('glc_day_nights_label') : homey_option('glc_day_night_label');

            } elseif ($booking_type == 'per_hour') {
                $price = $number > 1 ? homey_option('glc_hours_label') : homey_option('glc_hour_label');

            } elseif ($booking_type == 'per_week') {
                $price = $number > 1 ? homey_option('glc_weeks_label') : homey_option('glc_week_label');

            } elseif ($booking_type == 'per_month') {
                $price = $number > 1 ? homey_option('glc_months_label') : homey_option('glc_month_label');

            } else {
                $price = $number > 1 ? homey_option('glc_day_nights_label') : homey_option('glc_day_night_label');

            }
        } else {
            $price = '';
        }
        return $price;
        
    }
}

if ( ! function_exists( 'homey_get_price_label_by_id' ) ) {

    function homey_get_price_label_by_id($listing_id) {

        $homey_prefix = 'homey_';
        $homey_site_mode = homey_option('homey_site_mode'); // per_hour, per_day, both
        $booking_type = get_post_meta( $listing_id, $homey_prefix.'booking_type', true ); //per_day, per_hour
        $price_postfix = get_post_meta( $listing_id, $homey_prefix.'price_postfix', true );
        $price = '';

        if(!empty($price_postfix)) {
            $price = $price_postfix;
        } else {
            if($homey_site_mode == 'per_day_date') {
                $price = homey_option('glc_day_date_label');

            } elseif($homey_site_mode == 'per_day') {
                $price = homey_option('glc_day_night_label');

            } elseif($homey_site_mode == 'per_hour') {
                $price = homey_option('glc_hour_label');

            } elseif($homey_site_mode == 'per_week') {
                $price = homey_option('glc_week_label');

            } elseif($homey_site_mode == 'per_month') {
                $price = homey_option('glc_month_label');

            } elseif($homey_site_mode == 'both') {
                if($booking_type == 'per_day_date') {
                    $price = homey_option('glc_day_date_label');

                } elseif($booking_type == 'per_day') {
                    $price = homey_option('glc_day_night_label');

                } elseif ($booking_type == 'per_hour') {
                   $price = homey_option('glc_hour_label');

                } elseif ($booking_type == 'per_week') {
                   $price = homey_option('glc_week_label');

                } elseif ($booking_type == 'per_month') {
                   $price = homey_option('glc_month_label');

                } else {
                    $price = homey_option('glc_day_night_label');
                }
            } else {
                $price = '';
            }
        }
        return $price;
        
    }
}

if ( ! function_exists( 'homey_get_price_label_by_mode' ) ) {

    function homey_get_price_label_by_mode($mode) {

        if($mode == 'per_day_date') {
            $postfix = homey_option('glc_day_date_label');

        } elseif($mode == 'per_day') {
            $postfix = homey_option('glc_day_night_label');

        } elseif($mode == 'per_hour') {
            $postfix = homey_option('glc_hour_label');

        } elseif($mode == 'per_week') {
            $postfix = homey_option('glc_week_label');

        } elseif($mode == 'per_month') {
            $postfix = homey_option('glc_month_label');

        } else {
            $postfix = '';
        }
        return $postfix;
        
    }
}

/*-----------------------------------------------------------------------------------*/
// get default based currecncy for currency conversion
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'homey_default_currency_for_switcher' ) ) {

    function homey_default_currency_for_switcher() {

        $default_currency = homey_option('default_currency');
        if ( !empty( $default_currency ) ) {
            return $default_currency;
        } else {
            $default_currency = 'USD';
        }

        return $default_currency;
    }
}

/*-----------------------------------------------------------------------------------*/
// get current currency for currencies switcher
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'homey_get_wpc_current_currency' ) ) {

    function homey_get_wpc_current_currency() {

        if ( isset( $_COOKIE[ "homey_set_current_currency" ] ) ) {
            $get_current_currency = $_COOKIE[ "homey_set_current_currency" ];
            if ( Fcc_currency_exists( $get_current_currency ) ) {
                $current_currency = $get_current_currency;
            } else {
                $current_currency = homey_default_currency_for_switcher();
            }
        } else {
            $current_currency = homey_default_currency_for_switcher();
        }

        return $current_currency;
    }
}

if(!function_exists('homey_number_shorten')) {
    function homey_number_shorten($number, $precision = 0, $divisors = null) {
    $number = preg_replace('/[.,]/', '', $number);

        if (!isset($divisors)) {
            $divisors = array(
                pow(1000, 0) => '', // 1000^0 == 1
                pow(1000, 1) => 'K', // Thousand
                pow(1000, 2) => 'M', // Million
                pow(1000, 3) => 'B', // Billion
                pow(1000, 4) => 'T', // Trillion
                pow(1000, 5) => 'Qa', // Quadrillion
                pow(1000, 6) => 'Qi', // Quintillion
            );    
        }
        
        foreach ($divisors as $divisor => $shorthand) {
            if (abs($number) < ($divisor * 1000)) {
                // Match found
                break;
            }
        }
        //Match found or not found use the last defined value for divisor
        return number_format($number / $divisor, $precision) . $shorthand;
    }
}

/*-----------------------------------------------------------------------------------*/
// Formated price for payout
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_formatted_price_for_payout') ) {
    function homey_formatted_price_for_payout ( $listing_price ) {

        if( $listing_price ) {
        

            $listings_currency = homey_get_currency($sup = false);
            $price_decimals = intval(homey_option( 'decimals' ));
            $listing_currency_pos = homey_option( 'currency_position' );
            $price_thousands_separator = homey_option( 'thousands_separator' );
            $price_decimal_point_separator = homey_option( 'decimal_point_separator' );
        
            $listing_price = doubleval( $listing_price );
            
            $final_price = number_format ( $listing_price , $price_decimals , $price_decimal_point_separator , $price_thousands_separator );
            
            if(  $listing_currency_pos == 'before' ) {
                return $listings_currency . $final_price;
            } else {
                return $final_price . $listings_currency;
            }

        } else {
            $listings_currency = '';
        }

        return $listings_currency;
    }
}

/*-----------------------------------------------------------------------------------*/
// Get price
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_formatted_price') ) {
    function homey_formatted_price ( $listing_price, $decimals = false, $sup = false ) {

        if( $listing_price > -1) {
            $currency_maker = currency_maker($decimals, $sup);

            $listings_currency = $currency_maker['currency'];
            $price_decimals = $currency_maker['decimals'];
            $listing_currency_pos = $currency_maker['currency_position'];
            $price_thousands_separator = $currency_maker['thousands_separator'];
            $price_decimal_point_separator = $currency_maker['decimal_point_separator'];
        
            $short_prices = 0; //homey_option('short_prices');
            $currency_converter = homey_option('currency_converter');

            if($short_prices != 1 ) {

                $listing_price = doubleval( $listing_price );
                if ( class_exists( 'Favethemes_Currency_Converter' ) && isset( $_COOKIE[ "homey_set_current_currency" ] ) && $currency_converter != 0 ) {

                    $listing_price = apply_filters( 'homey_currency_switcher_filter', $listing_price );
                    return $listing_price;
                }
                //number_format() — Format a number with grouped thousands
                $final_price = number_format ( $listing_price , $price_decimals , $price_decimal_point_separator , $price_thousands_separator );
            } else {
                $final_price = homey_number_shorten($listing_price, $price_decimals);
            }
            if(  $listing_currency_pos == 'before' ) {
                return $listings_currency . $final_price;
            } else {
                return $final_price . $listings_currency;
            }

        } else {
            $listings_currency = '';
        }

        return $listings_currency;
    }
}

if( !function_exists('currency_maker')) {
    function currency_maker($decimals, $sup) {

        $price_maker_array = array();
        $multi_currency = 0;//homey_option('multi_currency');
        $default_currency = homey_option('default_currency');
        if(empty($default_currency)) {
            $default_currency = 'USD';
        }

        if( $multi_currency == 1 ) {

            if(class_exists('FCC_Currencies')) {
                $currencies = FCC_Currencies::get_listing_currency(get_the_ID());
                if($currencies) {

                    foreach ($currencies as $currency) {
                        $price_maker_array['currency'] = $currency->currency_symbol;
                        $price_maker_array['decimals']  = $currency->currency_decimal;
                        $price_maker_array['currency_position']  = $currency->currency_position;
                        $price_maker_array['thousands_separator']  = $currency->currency_thousand_separator;
                        $price_maker_array['decimal_point_separator']  = $currency->currency_decimal_separator;
                    }

                } else {

                        $currency = FCC_Currencies::get_currency_by_code($default_currency);

                        $price_maker_array['currency'] = $currency['currency_symbol'];
                        $price_maker_array['decimals']  = $currency['currency_decimal'];
                        $price_maker_array['currency_position']  = $currency['currency_position'];
                        $price_maker_array['thousands_separator']  = $currency['currency_thousand_separator'];
                        $price_maker_array['decimal_point_separator']  = $currency['currency_decimal_separator'];
                }
            }

        } else {

            if( $decimals ) { $decimals = 0; } else { $decimals = intval(homey_option( 'decimals' )); }

            $price_maker_array['currency'] = homey_get_currency($sup);
            $price_maker_array['decimals']  = $decimals;
            $price_maker_array['currency_position']  = homey_option( 'currency_position' );
            $price_maker_array['thousands_separator']  = homey_option( 'thousands_separator' );
            $price_maker_array['decimal_point_separator']  = homey_option( 'decimal_point_separator' );

        }
        return $price_maker_array;
    }
}


/*-----------------------------------------------------------------------------------*/
// Currency switcher filter
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_currency_switcher_filter') ) {
    function homey_currency_switcher_filter($listing_price) {
        $current_currency = $_COOKIE[ "homey_set_current_currency" ];
        if ( Fcc_currency_exists( $current_currency ) ) {    // validate current currency
            $base_currency = homey_default_currency_for_switcher();
            $converted_price = Fcc_convert_currency( $listing_price, $base_currency, $current_currency );
            return Fcc_format_currency( $converted_price, $current_currency, true, true );
        }
    }
}
add_filter( 'homey_currency_switcher_filter', 'homey_currency_switcher_filter', 1, 9 );

/*-----------------------------------------------------------------------------------*/
// Ajax function for currency conversion
/*-----------------------------------------------------------------------------------*/
add_action('wp_ajax_nopriv_homey_currency_converter', 'homey_currency_converter');
add_action('wp_ajax_homey_currency_converter', 'homey_currency_converter');

if ( ! function_exists( 'homey_currency_converter' ) ) {

    function homey_currency_converter()
    {

        if (isset($_POST['currency_to_converter'])) {

            $current_currency_expire = '';

            if (class_exists('Favethemes_Currency_Converter')) {

                $currency_converter = $_POST['currency_to_converter'];

                // check current currency expiry time
                $currency_expiry_period = intval($current_currency_expire);
                if (!$currency_expiry_period) {
                    $currency_expiry_period = 60 * 60;
                }
                $current_currency_expiry = time() + $currency_expiry_period;

                if (Fcc_currency_exists($currency_converter) && setcookie('homey_set_current_currency', $currency_converter, $current_currency_expiry, '/')) {
                    echo json_encode(array(
                        'success' => true
                    ));
                } else {
                    echo json_encode(array(
                        'success' => false,
                        'msg' => __("Cookie update failed", 'homey')
                    ));
                }

            } else {
                echo json_encode(array(
                    'success' => false,
                    'msg' => __('Please install and activate favethemes-currency-converter plugin!', 'homey')
                ));
            }

        } else {
            echo json_encode(array(
                    'success' => false,
                    'msg' => __("Request not valid", 'homey')
                )
            );
        }

        wp_die();

    }
}

/*-----------------------------------------------------------------------------------*/
// Minimum Price List
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_adv_searches_min_price') ) {
    function homey_adv_searches_min_price() {
        $prices_array = array( 0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 110, 120, 130, 140, 150, 160 );
        $searched_price = '';

        $local = homey_get_localization();

        $minimum_price_theme_options = homey_option('min_price');

        if( !empty($minimum_price_theme_options) ) {
            $minimum_prices_array = explode( ',', $minimum_price_theme_options );

            if( is_array( $minimum_prices_array ) && !empty( $minimum_prices_array ) ) {
                $temp_min_price_array = array();
                foreach( $minimum_prices_array as $min_price ) {
                    $min_price_integer = floatval( $min_price );
                    if( $min_price_integer > -1 ) {
                        $temp_min_price_array[] = $min_price_integer;
                    }
                }

                if( !empty( $temp_min_price_array ) ) {
                    $prices_array = $temp_min_price_array;
                }
            }
        }

        if( isset( $_GET['min-price'] ) ) {
            $searched_price = $_GET['min-price'];
        }

        if( $searched_price == '' )  {
            echo '<option value="" selected="selected">'.$local['search_min'].'</option>';
        } else {
            echo '<option value="">'.$local['search_min'].'</option>';
        }

        if( !empty( $prices_array ) ) {
            foreach( $prices_array as $min_price ) {
                if( $searched_price == $min_price ) {
                    echo '<option min="'.$min_price.'" value="'.esc_attr( $min_price ).'" selected="selected">'.homey_formatted_price( $min_price, false ).'</option>';
                } else {
                    echo '<option min="'.$min_price.'" value="'.esc_attr( $min_price ).'">'.homey_formatted_price( $min_price, false ).'</option>';
                }
            }
        }

    }
}
/*-----------------------------------------------------------------------------------*/
// Maximum Price List
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_adv_searches_max_price') ) {
    function homey_adv_searches_max_price() {
        $price_array = array( 50, 100, 125, 150, 160, 200, 250, 300, 400, 500, 600, 700, 800, 900, 1000, 1200 );
        $searched_price = '';

        $local = homey_get_localization();

        $maximum_price_theme_options = homey_option('max_price');

        if( !empty($maximum_price_theme_options) ) {
            $maximum_price_array = explode( ',', $maximum_price_theme_options );

            if( is_array( $maximum_price_array ) && !empty( $maximum_price_array ) ) {
                $temp_max_price_array = array();
                foreach( $maximum_price_array as $max_price ) {
                    $max_price_integer = floatval( $max_price );
                    if( $max_price_integer > 0 ) {
                        $temp_max_price_array[] = $max_price_integer;
                    }
                }

                if( !empty( $temp_max_price_array ) ) {
                    $price_array = $temp_max_price_array;
                }
            }
        }

        if( isset( $_GET['max-price'] ) ) {
            $searched_price = $_GET['max-price'];
        }

        if( $searched_price == '' )  {
            echo '<option value="" selected="selected">'.$local['search_max'].'</option>';
        } else {
            echo '<option value="">'.$local['search_max'].'</option>';
        }

        if( !empty( $price_array ) ) {
            foreach( $price_array as $max_price ) {
                if( $searched_price == $max_price ) {
                    echo '<option value="'.esc_attr( $max_price ).'" selected="selected">'.homey_formatted_price( $max_price, false ).'</option>';
                } else {
                    echo '<option value="'.esc_attr( $max_price ).'">'.homey_formatted_price( $max_price, false ).'</option>';
                }
            }
        }

    }
}

if(!function_exists('homey_available_currencies')) {
    function homey_available_currencies() {
        $currencies_array = array( '' => esc_html__('Choose Currency', 'homey'));
        if(class_exists('FCC_Currencies')) {
            $currencies = FCC_Currencies::get_currency_codes();
            if($currencies) {
                foreach ($currencies as $currency) {
                    $currencies_array[$currency->currency_code] = $currency->currency_code;
                }
            }
        }

        return $currencies_array;
    }
}

if(!function_exists('homey_reservation_paid_amount')) {
    function homey_reservation_paid_amount($reservation_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'homey_earnings';

        $sql_query = $wpdb->prepare(
            "
            SELECT sum(payment_due) as due_paid_amount 
            FROM $table_name 
            WHERE reservation_id = %d ORDER BY id DESC
            ",
            $reservation_id
        );

        $results = $wpdb->get_results($sql_query);

        if ( sizeof( $results ) != 0 ) {
            return $results[0]->due_paid_amount;
        }

        return 0;
    }
}
