<?php
/*-----------------------------------------------------------------------------------*/
// Availability Search Filter
/*-----------------------------------------------------------------------------------*/
add_filter('homey_check_search_availability_filter', 'homey_check_search_availability_callback', 10, 3);
if( !function_exists('homey_check_search_availability_callback') ) {
    function homey_check_search_availability_callback( $query_args, $arrive, $depart ) {

        global $wpdb, $post;
        $allowed_html =  array();
        $post_ids = array();

        if ( empty($arrive) && empty($depart) ) {
            
            if( homey_option('enable_radius') ) {
                return '';
            } else {
                return $query_args;
            }
        }

        $check_in_date = sanitize_text_field ( wp_kses ( $arrive, $allowed_html) );
        $check_out_date = sanitize_text_field ( wp_kses ( $depart, $allowed_html) );


        $args = array(
            'post_type' => 'listing',
            'posts_per_page' => '-1',
            'post_status' => 'publish'
        );

        $wpQry = new WP_Query( $args );

        if ( $wpQry->have_posts() ) :
            while ( $wpQry->have_posts() ) : $wpQry->the_post();
                $list_id = $post->ID;
                $check = check_listing_availability_for_search($check_in_date, $check_out_date, $list_id);
                if($check) {
                    $post_ids[] = $list_id;
                }

            endwhile;
        endif;

        if ( empty( $post_ids ) || ! $post_ids ) {
            $post_ids = array(0);
        }

        if( homey_option('enable_radius') ) {
            return $post_ids;

        } else {
            $query_args[ 'post__in' ] = $post_ids;
            return $query_args;
        }
    }
}

add_filter('homey_check_hourly_search_availability_filter', 'homey_check_hourly_search_availability_callback', 10, 4);
if( !function_exists('homey_check_hourly_search_availability_callback') ) {
    function homey_check_hourly_search_availability_callback( $query_args, $arrive, $start_time, $end_time ) {

        global $wpdb, $post;
        $allowed_html =  array();
        $post_ids = array();

        if ( empty($arrive) && empty($depart) ) {
            if( homey_option('enable_radius') ) {
                return '';
            } else {
                return $query_args;
            }
        }

        $check_in_date = sanitize_text_field ( wp_kses ( $arrive, $allowed_html) );
        $start_time = sanitize_text_field ( wp_kses ( $start_time, $allowed_html) );
        $end_time = sanitize_text_field ( wp_kses ( $end_time, $allowed_html) );
        
        $check_in_hour = $check_in_date.' '.$start_time;
        $check_out_hour = $check_in_date.' '.$end_time;

        


        $args = array(
            'post_type' => 'listing',
            'posts_per_page' => '-1',
            'post_status' => 'publish'
        );

        $wpQry = new WP_Query( $args );

        if ( $wpQry->have_posts() ) :
            while ( $wpQry->have_posts() ) : $wpQry->the_post();
                $list_id = $post->ID;
                $check = check_listing_availability_for_hourly_search($check_in_hour, $check_out_hour, $list_id);
                if($check) {
                    $post_ids[] = $list_id;
                }

            endwhile;
        endif;

        if ( empty( $post_ids ) || ! $post_ids ) {
            $post_ids = array(0);
        }

        if( homey_option('enable_radius') ) {
            return $post_ids;

        } else {
            $query_args[ 'post__in' ] = $post_ids;
            return $query_args;
        }
    }
}


if(!function_exists('homey_search_date_format')) {
    function homey_search_date_format($gdate) {

        $homey_date_format = homey_option('homey_date_format');

        if(empty($gdate)) {
            return '';
        }

        if($homey_date_format == 'yy-mm-dd') {
            $get_date = explode('-', $gdate);
            $year = $get_date[0];
            $month = $get_date[1];
            $day = $get_date[2];

        } elseif($homey_date_format == 'yy-dd-mm') {
            $get_date = explode('-', $gdate);
            $year = $get_date[0];
            $month = $get_date[2];
            $day = $get_date[1];

        } elseif($homey_date_format == 'mm-yy-dd') {
            $get_date = explode('-', $gdate);
            $year = $get_date[1];
            $month = $get_date[0];
            $day = $get_date[2];
            
        } elseif($homey_date_format == 'dd-yy-mm') {
            $get_date = explode('-', $gdate);
            $year = $get_date[1];
            $month = $get_date[2];
            $day = $get_date[0];
            
        } elseif($homey_date_format == 'mm-dd-yy') {
            $get_date = explode('-', $gdate);
            $year = $get_date[2];
            $month = $get_date[0];
            $day = $get_date[1];
            
        } elseif($homey_date_format == 'dd-mm-yy') {
            $get_date = explode('-', $gdate);
            $year = $get_date[2];
            $month = $get_date[1];
            $day = $get_date[0];
            
        } elseif($homey_date_format == 'dd.mm.yy') {
            $get_date = explode('.', $gdate);
            $year = $get_date[2];
            $month = $get_date[1];
            $day = $get_date[0];

        } else {
            $return_date = $gdate;
        }

        $return_date = $year.'-'.$month.'-'.$day;
        return $return_date;
    }
}

add_filter('homey_radius_filter', 'homey_radius_filter_callback', 10, 4);
if( !function_exists('homey_radius_filter_callback') ) {
    function homey_radius_filter_callback( $query_args, $search_lat, $search_long, $search_radius ) {

        global $wpdb;

        if ( ! ( $search_lat && $search_long && $search_radius ) ) {
            return '';
        }

        $radius_unit = homey_option('radius_unit');
        if( $radius_unit == 'km' ) {
            $earth_radius = 6371;
        } elseif ( $radius_unit == 'mi' ) {
            $earth_radius = 3959;
        } else {
            $earth_radius = 6371;
        }

        $sql = $wpdb->prepare( "SELECT $wpdb->posts.ID,
                ( %s * acos(
                    cos( radians(%s) ) *
                    cos( radians( latitude.meta_value ) ) *
                    cos( radians( longitude.meta_value ) - radians(%s) ) +
                    sin( radians(%s) ) *
                    sin( radians( latitude.meta_value ) )
                ) )
                AS distance, latitude.meta_value AS latitude, longitude.meta_value AS longitude
                FROM $wpdb->posts
                INNER JOIN $wpdb->postmeta
                    AS latitude
                    ON $wpdb->posts.ID = latitude.post_id
                INNER JOIN $wpdb->postmeta
                    AS longitude
                    ON $wpdb->posts.ID = longitude.post_id
                WHERE 1=1
                    AND ($wpdb->posts.post_status = 'publish' )
                    AND latitude.meta_key='homey_geolocation_lat'
                    AND longitude.meta_key='homey_geolocation_long'
                HAVING distance < %s
                ORDER BY $wpdb->posts.menu_order ASC, distance ASC",
            $earth_radius,
            $search_lat,
            $search_long,
            $search_lat,
            $search_radius
        );

        $post_ids = $wpdb->get_results( $sql, OBJECT_K );

        if ( empty( $post_ids ) || ! $post_ids ) {
            $post_ids = array(0);
        }

        $post_ids = array_keys( (array) $post_ids );
        return $post_ids;
    }
}

/*-----------------------------------------------------------------------------------*/
// Listing Search filter
/*-----------------------------------------------------------------------------------*/
add_filter('homey_search_filter', 'homey_listing_search');
if( !function_exists('homey_listing_search') ) {
    function homey_listing_search($search_query)
    {

        $tax_query = array();
        $meta_query = array();
        $allowed_html = array();
        $query_ids = '';
        $homey_search_type = homey_search_type();

        $arrive = isset($_GET['arrive']) ? $_GET['arrive'] : '';
        $depart = isset($_GET['depart']) ? $_GET['depart'] : '';
        $start = isset($_GET['start']) ? $_GET['start'] : '';
        $end = isset($_GET['end']) ? $_GET['end'] : '';
        $guests = isset($_GET['guest']) ? $_GET['guest'] : '';
        $pets = isset($_GET['pets']) ? $_GET['pets'] : -1;
        $bedrooms = isset($_GET['bedrooms']) ? $_GET['bedrooms'] : '';
        $rooms = isset($_GET['rooms']) ? $_GET['rooms'] : '';
        $room_size = isset($_GET['room_size']) ? $_GET['room_size'] : '';
        $search_country = isset($_GET['search_country']) ? $_GET['search_country'] : '';
        $search_city = isset($_GET['search_city']) ? $_GET['search_city'] : '';
        $search_area = isset($_GET['search_area']) ? $_GET['search_area'] : '';
        $listing_type = isset($_GET['listing_type']) ? $_GET['listing_type'] : '';
        $lat = isset($_GET['lat']) ? $_GET['lat'] : '';
        $lng = isset($_GET['lng']) ? $_GET['lng'] : '';
        $search_radius = isset($_GET['radius']) ? $_GET['radius'] : 20;

        $country = isset($_GET['country']) ? $_GET['country'] : '';
        $state = isset($_GET['state']) ? $_GET['state'] : '';
        $city = isset($_GET['city']) ? $_GET['city'] : '';
        $area = isset($_GET['area']) ? $_GET['area'] : '';
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

        $arrive = homey_search_date_format($arrive);
        $depart = homey_search_date_format($depart);
        

        if( homey_option('enable_radius') ) {

            if($homey_search_type == 'per_hour') {
                $available_listings_ids = apply_filters('homey_check_hourly_search_availability_filter', $search_query, $arrive, $start, $end);
            } else {
                $available_listings_ids = apply_filters('homey_check_search_availability_filter', $search_query, $arrive, $depart);
            }
            $radius_ids = apply_filters('homey_radius_filter', $search_query, $lat, $lng, $search_radius);

            if(!empty($available_listings_ids) && !empty($radius_ids)) {
                $query_ids =  array_intersect($available_listings_ids, $radius_ids);

                if(empty($query_ids)) {
                    $query_ids = array(0);
                }

            } elseif(empty($available_listings_ids)) {
                $query_ids = $radius_ids;

            } elseif(empty($radius_ids)) {
                $query_ids = $available_listings_ids;
            }

            if(!empty($query_ids)) {
                $search_query['post__in'] = $query_ids;
            }

        } else {

            if($homey_search_type == 'per_hour') {
                $search_query = apply_filters('homey_check_hourly_search_availability_filter', $search_query, $arrive, $start, $end);
            } else {
                $search_query = apply_filters('homey_check_search_availability_filter', $search_query, $arrive, $depart);
            }

            if(!empty($search_city) || !empty($search_area)) {
                $_tax_query = Array();

                if(!empty($search_city) && !empty($search_area)) {
                    $_tax_query['relation'] = 'AND';
                }

                if(!empty($search_city)) {
                    $_tax_query[] = array(
                        'taxonomy' => 'listing_city',
                        'field' => 'slug',
                        'terms' => sanitize_text_field($search_city)
                    );
                }

                if(!empty($search_area)) {
                    $_tax_query[] = array(
                        'taxonomy' => 'listing_area',
                        'field' => 'slug',
                        'terms' => sanitize_text_field($search_area)
                    );
                }

                $tax_query[] = $_tax_query;
            }

            if(!empty($search_country)) {
                $tax_query[] = array(
                    'taxonomy' => 'listing_country',
                    'field' => 'slug',
                    'terms' => sanitize_text_field($search_country)
                );
            }

        }


        $keyword = trim($keyword);
        if (!empty($keyword)) {
            $search_query['s'] = $keyword;
        }
        

        $beds_baths_rooms_search = homey_option('beds_baths_rooms_search');
        $search_criteria = '=';
        if( $beds_baths_rooms_search == 'greater') {
            $search_criteria = '>=';
        } elseif ($beds_baths_rooms_search == 'lessthen') {
            $search_criteria = '<=';
        }

        if(!empty($listing_type)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_type',
                'field' => 'slug',
                'terms' => sanitize_text_field($listing_type)
            );
        }

        if(!empty($country)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_country',
                'field' => 'slug',
                'terms' => sanitize_text_field($country)
            );
        }

        if(!empty($state)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_state',
                'field' => 'slug',
                'terms' => sanitize_text_field($state)
            );
        }

        if(!empty($city)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_city',
                'field' => 'slug',
                'terms' => sanitize_text_field($city)
            );
        }

        if(!empty($area)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_area',
                'field' => 'slug',
                'terms' => sanitize_text_field($area)
            );
        }

        // min and max price logic
        if (isset($_GET['min-price']) && !empty($_GET['min-price']) && $_GET['min-price'] != 'any' && isset($_GET['max-price']) && !empty($_GET['max-price']) && $_GET['max-price'] != 'any') {
            $min_price = doubleval(homey_clean($_GET['min-price']));
            $max_price = doubleval(homey_clean($_GET['max-price']));

            if ($min_price > 0 && $max_price > $min_price) {
                $meta_query[] = array(
                    'key' => 'homey_night_price',
                    'value' => array($min_price, $max_price),
                    'type' => 'NUMERIC',
                    'compare' => 'BETWEEN',
                );
            }
        } else if (isset($_GET['min-price']) && !empty($_GET['min-price']) && $_GET['min-price'] != 'any') {
            $min_price = doubleval(homey_clean($_GET['min-price']));
            if ($min_price > 0) {
                $meta_query[] = array(
                    'key' => 'homey_night_price',
                    'value' => $min_price,
                    'type' => 'NUMERIC',
                    'compare' => '>=',
                );
            }
        } else if (isset($_GET['max-price']) && !empty($_GET['max-price']) && $_GET['max-price'] != 'any') {
            $max_price = doubleval(homey_clean($_GET['max-price']));
            if ($max_price > 0) {
                $meta_query[] = array(
                    'key' => 'homey_night_price',
                    'value' => $max_price,
                    'type' => 'NUMERIC',
                    'compare' => '<=',
                );
            }
        }

        if(!empty($guests)) {
            $meta_query[] = array(
                'key' => 'homey_total_guests_plus_additional_guests',
                'value' => intval($guests),
                'type' => 'NUMERIC',
                'compare' => $search_criteria,
            );
        }

        //because this is boolean, no other option other than yes or no
//        $pets = $pets == '' ? 1 : $pets;
        //if(!empty($pets) && $pets != '0') {
        if(!empty($pets) && $pets != -1) {
            $meta_query[] = array(
                'key' => 'homey_pets',
                'value' => sanitize_text_field($pets),
                'type' => 'NUMERIC',
                'compare' => '=',
            );
        }

        if (!empty($bedrooms)) {
            $bedrooms = sanitize_text_field($bedrooms);
            $meta_query[] = array(
                'key' => 'homey_listing_bedrooms',
                'value' => $bedrooms,
                'type' => 'CHAR',
                'compare' => $search_criteria,
            );
        }

        if (!empty($rooms)) {
            $rooms = sanitize_text_field($rooms);
            $meta_query[] = array(
                'key' => 'homey_listing_rooms',
                'value' => $rooms,
                'type' => 'CHAR',
                'compare' => $search_criteria,
            );
        }


        if (isset($_GET['area']) && !empty($_GET['area'])) {
            if (is_array($_GET['area'])) {
                $areas = sanitize_text_field($_GET['area']);

                foreach ($areas as $area):
                    $tax_query[] = array(
                        'taxonomy' => 'listing_area',
                        'field' => 'slug',
                        'terms' => $area
                    );
                endforeach;
            }
        }

        if (isset($_GET['amenity']) && !empty($_GET['amenity'])) {
            if (is_array($_GET['amenity'])) {
                $amenities = $_GET['amenity'];

                foreach ($amenities as $amenity):
                    $tax_query[] = array(
                        'taxonomy' => 'listing_amenity',
                        'field' => 'slug',
                        'terms' => sanitize_text_field($amenity)
                    );
                endforeach;
            }
        }

        if (isset($_GET['facility']) && !empty($_GET['facility'])) {
            if (is_array($_GET['facility'])) {
                $facilities = $_GET['facility'];

                foreach ($facilities as $facility):
                    $tax_query[] = array(
                        'taxonomy' => 'listing_facility',
                        'field' => 'slug',
                        'terms' => sanitize_text_field($facility)
                    );
                endforeach;
            }
        }

        if(!empty($room_size)) {
            $tax_query[] = array(
                'taxonomy' => 'room_type',
                'field' => 'slug',
                'terms' => sanitize_text_field($room_size)
            );
        }

        $meta_count = count($meta_query);

        if( $meta_count > 1 ) {
            $meta_query['relation'] = 'AND';
        }
        if( $meta_count > 0 ){
            $search_query['meta_query'] = $meta_query;
        }


        $tax_count = count( $tax_query );

        if( $tax_count > 1 ) {
            $tax_query['relation'] = 'AND';
        }
        if( $tax_count > 0 ){
            $search_query['tax_query'] = $tax_query;
        }
        
        //print_r($search_query);
        return $search_query;
    }
}

if(!function_exists('check_listing_availability_for_search')) {
    function check_listing_availability_for_search($check_in_date, $check_out_date, $listing_id) {
        $return_array = array();
        $local = homey_get_localization();

        $reservation_booked_array = get_post_meta($listing_id, 'reservation_dates', true);
        if(empty($reservation_booked_array)) {
            $reservation_booked_array = homey_get_booked_days($listing_id);
        }

        $reservation_pending_array = get_post_meta($listing_id, 'reservation_pending_dates', true);
        if(empty($reservation_pending_array)) {
            $reservation_pending_array = homey_get_booking_pending_days($listing_id);
        }

        $reservation_unavailable_array = get_post_meta($listing_id, 'reservation_unavailable', true);
        if(empty($reservation_unavailable_array)) {
            $reservation_unavailable_array = array();
        }

        $check_in      = new DateTime($check_in_date);
        $check_in_unix = $check_in->getTimestamp();

        $check_out     = new DateTime($check_out_date);
        $check_out->modify('yesterday');
        $check_out_unix = $check_out->getTimestamp();

        while ($check_in_unix <= $check_out_unix) {
            
            if( array_key_exists($check_in_unix, $reservation_booked_array)  || array_key_exists($check_in_unix, $reservation_pending_array) || array_key_exists($check_in_unix, $reservation_unavailable_array) ) {
                
                return false; //dates are not available

            }
            $check_in->modify('tomorrow');
            $check_in_unix =   $check_in->getTimestamp();
        }

        return true; //dates are available
        
    }
}

if(!function_exists('check_listing_availability_for_hourly_search')) {

    function check_listing_availability_for_hourly_search($check_in_hour, $check_out_hour, $listing_id) {
        

        $reservation_booked_array = get_post_meta($listing_id, 'reservation_booked_hours', true);
        if(empty($reservation_booked_array)) {
            $reservation_booked_array = homey_get_booked_hours($listing_id);
        }

        $reservation_pending_array = get_post_meta($listing_id, 'reservation_pending_hours', true);
        if(empty($reservation_pending_array)) {
            $reservation_pending_array = homey_get_booking_pending_hours($listing_id);
        }

        $check_in_hour      = new DateTime($check_in_hour);
        $check_in_hour_unix = $check_in_hour->getTimestamp();

        $check_out_hour     = new DateTime($check_out_hour);
        $check_out_hour->modify('-30 minutes');
        $check_out_hour_unix = $check_out_hour->getTimestamp();

        while ($check_in_hour_unix <= $check_out_hour_unix) {
            
            //echo $start_hour_unix.' ===== <br/>';
            if( array_key_exists($check_in_hour_unix, $reservation_booked_array)  || array_key_exists($check_in_hour_unix, $reservation_pending_array) ) {    
                
                return false; //dates are not available

            }
            $check_in_hour->modify('+30 minutes');
            $check_in_hour_unix = $check_in_hour->getTimestamp();
        }

        return true; //dates are available
        
    }
}

add_action( 'wp_ajax_nopriv_homey_half_map', 'homey_half_map' );
add_action( 'wp_ajax_homey_half_map', 'homey_half_map' );
if( !function_exists('homey_half_map') ) {
    function homey_half_map() {

        global $homey_prefix, $homey_local;

        $homey_prefix = 'homey_';
        $homey_local = homey_get_localization();

        $homey_search_type = homey_search_type();

        $rental_text = $homey_local['rental_label'];
        
        check_ajax_referer('homey_map_ajax_nonce', 'security');

        $tax_query = array();
        $meta_query = array();
        $allowed_html = array();
        $query_ids = '';

        $cgl_meta = homey_option('cgl_meta');
        $cgl_beds = homey_option('cgl_beds');
        $cgl_baths = homey_option('cgl_baths');
        $cgl_guests = homey_option('cgl_guests');
        $cgl_types = homey_option('cgl_types');
        $price_separator = homey_option('currency_separator');

        $arrive = isset($_POST['arrive']) ? $_POST['arrive'] : '';
        $depart = isset($_POST['depart']) ? $_POST['depart'] : '';
        $guests = isset($_POST['guest']) ? $_POST['guest'] : '';
        $pets = isset($_POST['pets']) ? $_POST['pets'] : -1;
        $bedrooms = isset($_POST['bedrooms']) ? $_POST['bedrooms'] : '';
        $rooms = isset($_POST['rooms']) ? $_POST['rooms'] : '';
        $start_hour = isset($_POST['start_hour']) ? $_POST['start_hour'] : '';
        $end_hour = isset($_POST['end_hour']) ? $_POST['end_hour'] : '';
        $room_size = isset($_POST['room_size']) ? $_POST['room_size'] : '';
        $search_country = isset($_POST['search_country']) ? $_POST['search_country'] : '';
        $search_city = isset($_POST['search_city']) ? $_POST['search_city'] : '';
        $search_area = isset($_POST['search_area']) ? $_POST['search_area'] : '';
        $listing_type = isset($_POST['listing_type']) ? $_POST['listing_type'] : '';
        $search_lat = isset($_POST['search_lat']) ? $_POST['search_lat'] : '';
        $search_lng = isset($_POST['search_lng']) ? $_POST['search_lng'] : '';
        $search_radius = isset($_POST['radius']) ? $_POST['radius'] : 20;

        $paged = isset($_POST['paged']) ? ($_POST['paged']) : '';
        $sort_by = isset($_POST['sort_by']) ? ($_POST['sort_by']) : '';
        $layout = isset($_POST['layout']) ? ($_POST['layout']) : 'list';
        $num_posts = isset($_POST['num_posts']) ? ($_POST['num_posts']) : '9';

        $country = isset($_POST['country']) ? $_POST['country'] : '';
        $state = isset($_POST['state']) ? $_POST['state'] : '';
        $city = isset($_POST['city']) ? $_POST['city'] : '';
        $area = isset($_POST['area']) ? $_POST['area'] : '';
        $booking_type = isset($_POST['booking_type']) ? $_POST['booking_type'] : '';
        $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';

        $arrive = homey_search_date_format($arrive);
        $depart = homey_search_date_format($depart);

        $beds_baths_rooms_search = homey_option('beds_baths_rooms_search');
        $search_criteria = '=';
        if( $beds_baths_rooms_search == 'greater') {
            $search_criteria = '>=';
        } elseif ($beds_baths_rooms_search == 'lessthen') {
            $search_criteria = '<=';
        }

        if( !empty($booking_type) ) {
            $homey_search_type = $booking_type;
        }

        $query_args = array(
            'post_type' => 'listing',
            'posts_per_page' => $num_posts,
            'post_status' => 'publish',
            'paged' => $paged,
        );

        $keyword = trim($keyword);
        if (!empty($keyword)) {
            $query_args['s'] = $keyword;
        }
        
        if( !empty( $_POST["optimized_loading"] ) ) {
            $north_east_lat = sanitize_text_field($_POST['north_east_lat']);
            $north_east_lng = sanitize_text_field($_POST['north_east_lng']);
            $south_west_lat = sanitize_text_field($_POST['south_west_lat']);
            $south_west_lng = sanitize_text_field($_POST['south_west_lng']);

            $query_args = apply_filters('homey_optimized_filter', $query_args, $north_east_lat, $north_east_lng, $south_west_lat, $south_west_lng );
        }
        

        if( homey_option('enable_radius') ) {
            if($homey_search_type == 'per_hour') {
                $available_listings_ids = apply_filters('homey_check_hourly_search_availability_filter', $query_args, $arrive, $start_hour, $end_hour);
            } else {
                $available_listings_ids = apply_filters('homey_check_search_availability_filter', $query_args, $arrive, $depart);
            }

            $radius_ids = apply_filters('homey_radius_filter', $query_args, $search_lat, $search_lng, $search_radius);

            if(!empty($available_listings_ids) && !empty($radius_ids)) {
                $query_ids =  array_intersect($available_listings_ids, $radius_ids);

                if(empty($query_ids)) {
                    $query_ids = array(0);
                }

            } elseif(empty($available_listings_ids)) {
                $query_ids = $radius_ids;

            } elseif(empty($radius_ids)) {
                $query_ids = $available_listings_ids;
            }

            if(!empty($query_ids)) {
                $query_args['post__in'] = $query_ids;
            }
        } else {

            if($homey_search_type == 'per_hour') {
                $query_args = apply_filters('homey_check_hourly_search_availability_filter', $query_args, $arrive, $start_hour, $end_hour);
            } else {
                $query_args = apply_filters('homey_check_search_availability_filter', $query_args, $arrive, $depart);
            }

            if(!empty($search_city) || !empty($search_area)) {
                $_tax_query = Array();

                if(!empty($search_city) && !empty($search_area)) {
                    $_tax_query['relation'] = 'AND';
                }

                if(!empty($search_city)) {
                    $_tax_query[] = array(
                        'taxonomy' => 'listing_city',
                        'field' => 'slug',
                        'terms' => $search_city
                    );
                }

                if(!empty($search_area)) {
                    $_tax_query[] = array(
                        'taxonomy' => 'listing_area',
                        'field' => 'slug',
                        'terms' => $search_area
                    );
                }

                $tax_query[] = $_tax_query;
            }

            if(!empty($search_country)) {
                $tax_query[] = array(
                    'taxonomy' => 'listing_country',
                    'field' => 'slug',
                    'terms' => homey_traverse_comma_string($search_country)
                );
            }

        }

        if(!empty($listing_type)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_type',
                'field' => 'slug',
                'terms' => homey_traverse_comma_string($listing_type)
            );
        }

        if(!empty($country)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_country',
                'field' => 'slug',
                'terms' => $country
            );
        }

        if(!empty($state)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_state',
                'field' => 'slug',
                'terms' => $state
            );
        }

        if(!empty($city)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_city',
                'field' => 'slug',
                'terms' => $city
            );
        }

        if(!empty($area)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_area',
                'field' => 'slug',
                'terms' => $area
            );
        }

        // min and max price logic
        if (isset($_POST['min-price']) && !empty($_POST['min-price']) && $_POST['min-price'] != 'any' && isset($_POST['max-price']) && !empty($_POST['max-price']) && $_POST['max-price'] != 'any') {
            $min_price = doubleval(homey_clean($_POST['min-price']));
            $max_price = doubleval(homey_clean($_POST['max-price']));

            if ($min_price > 0 && $max_price > $min_price) {
                $meta_query[] = array(
                    'key' => 'homey_night_price',
                    'value' => array($min_price, $max_price),
                    'type' => 'NUMERIC',
                    'compare' => 'BETWEEN',
                );
            }
        } else if (isset($_POST['min-price']) && !empty($_POST['min-price']) && $_POST['min-price'] != 'any') {
            $min_price = doubleval(homey_clean($_POST['min-price']));
            if ($min_price > 0) {
                $meta_query[] = array(
                    'key' => 'homey_night_price',
                    'value' => $min_price,
                    'type' => 'NUMERIC',
                    'compare' => '>=',
                );
            }
        } else if (isset($_POST['max-price']) && !empty($_POST['max-price']) && $_POST['max-price'] != 'any') {
            $max_price = doubleval(homey_clean($_POST['max-price']));
            if ($max_price > 0) {
                $meta_query[] = array(
                    'key' => 'homey_night_price',
                    'value' => $max_price,
                    'type' => 'NUMERIC',
                    'compare' => '<=',
                );
            }
        }

        if(!empty($guests)) {
            $meta_query[] = array(
                'key' => 'homey_total_guests_plus_additional_guests',
                'value' => intval($guests),
                'type' => 'NUMERIC',
                'compare' => $search_criteria,
            );
        }

        //because this is boolean, no other option other than yes or no
        //$pets = $pets == '' ? 1 : $pets;
        //if(!empty($pets) && $pets != '0') {
        if(!empty($pets) && $pets != -1) {
            $meta_query[] = array(
                'key' => 'homey_pets',
                'value' => $pets,
                'type' => 'NUMERIC',
                'compare' => '=',
            );
        }
//print_r($meta_query);exit;
        if (!empty($bedrooms)) {
            $bedrooms = sanitize_text_field($bedrooms);
            $meta_query[] = array(
                'key' => 'homey_listing_bedrooms',
                'value' => $bedrooms,
                'type' => 'CHAR',
                'compare' => $search_criteria,
            );
        }

        if (!empty($rooms)) {
            $rooms = sanitize_text_field($rooms);
            $meta_query[] = array(
                'key' => 'homey_listing_rooms',
                'value' => $rooms,
                'type' => 'CHAR',
                'compare' => $search_criteria,
            );
        }

        if( !empty($booking_type) ) {
            $meta_query[] = array(
                'key'     => 'homey_booking_type',
                'value'   => $booking_type,
                'compare' => '=',
                'type'    => 'CHAR'
            );
        }

        if (isset($_POST['area']) && !empty($_POST['area'])) {
            if (is_array($_POST['area'])) {
                $areas = $_POST['area'];

                foreach ($areas as $area):
                    $tax_query[] = array(
                        'taxonomy' => 'listing_area',
                        'field' => 'slug',
                        'terms' => homey_traverse_comma_string($area)
                    );
                endforeach;
            }
        }

        if (isset($_POST['amenity']) && !empty($_POST['amenity'])) {
            if (is_array($_POST['amenity'])) {
                $amenities = $_POST['amenity'];

                foreach ($amenities as $amenity):
                    $tax_query[] = array(
                        'taxonomy' => 'listing_amenity',
                        'field' => 'slug',
                        'terms' => $amenity
                    );
                endforeach;
            }
        }

        if (isset($_POST['facility']) && !empty($_POST['facility'])) {
            if (is_array($_POST['facility'])) {
                $facilities = $_POST['facility'];

                foreach ($facilities as $facility):
                    $tax_query[] = array(
                        'taxonomy' => 'listing_facility',
                        'field' => 'slug',
                        'terms' => $facility
                    );
                endforeach;
            }
        }
        
        if(!empty($room_size)) {
            $tax_query[] = array(
                'taxonomy' => 'room_type',
                'field' => 'slug',
                'terms' => homey_traverse_comma_string($room_size)
            );
        }

        if ( $sort_by == 'a_price' ) {
            $query_args['orderby'] = 'meta_value_num';
            $query_args['meta_key'] = 'homey_night_price';
            $query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_price' ) {
            $query_args['orderby'] = 'meta_value_num';
            $query_args['meta_key'] = 'homey_night_price';
            $query_args['order'] = 'DESC';
        } else if ( $sort_by == 'a_rating' ) {
            $query_args['orderby'] = 'meta_value_num';
            $query_args['meta_key'] = 'listing_total_rating';
            $query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_rating' ) {
            $query_args['orderby'] = 'meta_value_num';
            $query_args['meta_key'] = 'listing_total_rating';
            $query_args['order'] = 'DESC';
        } else if ( $sort_by == 'featured' ) {
            $query_args['meta_key'] = 'homey_featured';
            $query_args['meta_value'] = '1';
        } else if ( $sort_by == 'a_date' ) {
            $query_args['orderby'] = 'date';
            $query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_date' ) {
            $query_args['orderby'] = 'date';
            $query_args['order'] = 'DESC';
        } else if ( $sort_by == 'featured_top' ) {
            $query_args['orderby'] = 'meta_value date';
            $query_args['meta_key'] = 'homey_featured';
            $query_args['order'] = 'DESC';
        }

        $meta_count = count($meta_query);

        if( $meta_count > 1 ) {
            $meta_query['relation'] = 'AND';
        }
        if( $meta_count > 0 ){
            $query_args['meta_query'] = $meta_query;
        }

        $tax_count = count( $tax_query );

        if( $tax_count > 1 ) {
            $tax_query['relation'] = 'AND';
        }
        if( $tax_count > 0 ){
            $query_args['tax_query'] = $tax_query;
        }

        $query_args = new WP_Query( $query_args );

        $listings = array();

        ob_start();

        $total_listings = $query_args->found_posts;

        if($total_listings > 1) {
            $rental_text = $homey_local['rentals_label'];
        }

        while( $query_args->have_posts() ): $query_args->the_post();

            $listing_id = get_the_ID();
            $address        = get_post_meta( get_the_ID(), $homey_prefix.'listing_address', true );
            $bedrooms       = get_post_meta( get_the_ID(), $homey_prefix.'listing_bedrooms', true );
            $guests         = get_post_meta( get_the_ID(), $homey_prefix.'guests', true );
            $beds           = get_post_meta( get_the_ID(), $homey_prefix.'beds', true );
            $baths          = get_post_meta( get_the_ID(), $homey_prefix.'baths', true );
            $night_price          = get_post_meta( get_the_ID(), $homey_prefix.'night_price', true );
            $location = get_post_meta( get_the_ID(), $homey_prefix.'listing_location',true);
            $lat_long = explode(',', $location);

            $listing_price = homey_get_price_by_id($listing_id);

            $listing_type = wp_get_post_terms( get_the_ID(), 'listing_type', array("fields" => "ids") );

            if($cgl_beds != 1) {
                $bedrooms = '';
            }

            if($cgl_baths != 1) {
                $baths = '';
            }

            if($cgl_guests != 1) {
                $guests = '';
            }

            $lat = $long = '';
            if(!empty($lat_long[0])) {
                $lat = $lat_long[0];
            }

            if(!empty($lat_long[1])) {
                $long = $lat_long[1];
            }

            $listing = new stdClass();

            $listing->id = $listing_id;
            $listing->title = get_the_title();
            $listing->lat = $lat;
            $listing->long = $long;
            $listing->price = homey_formatted_price($listing_price, false, true).'<sub>'.esc_attr($price_separator).homey_get_price_label_by_id($listing_id).'</sub>';
            $listing->address = $address;
            $listing->bedrooms = $bedrooms;
            $listing->guests = $guests;
            $listing->beds = $beds;
            $listing->baths = $baths;
            
            if($cgl_types != 1) {
                $listing->listing_type = '';
            } else {
                $listing->listing_type = homey_taxonomy_simple('listing_type');
            }
            $listing->thumbnail = get_the_post_thumbnail( $listing_id, 'homey-listing-thumb',  array('class' => 'img-responsive' ) );
            $listing->url = get_permalink();

            $listing->icon = get_template_directory_uri() . '/images/custom-marker.png';

            $listing->retinaIcon = get_template_directory_uri() . '/images/custom-marker.png';

            if(!empty($listing_type)) {
                foreach( $listing_type as $term_id ) {

                    $listing->term_id = $term_id;

                    $icon_id = get_term_meta($term_id, 'homey_marker_icon', true);
                    $retinaIcon_id = get_term_meta($term_id, 'homey_marker_retina_icon', true);

                    $icon = wp_get_attachment_image_src( $icon_id, 'full' );
                    $retinaIcon = wp_get_attachment_image_src( $retinaIcon_id, 'full' );

                    if( !empty($icon['0']) ) {
                        $listing->icon = $icon['0'];
                    } 
                    if( !empty($retinaIcon['0']) ) {
                        $listing->retinaIcon = $retinaIcon['0'];
                    } 
                }
            }

            array_push($listings, $listing);

            if($layout == 'card') {
                get_template_part('template-parts/listing/listing-card');
            } else {
                get_template_part('template-parts/listing/listing-item');
            }

        endwhile;

        wp_reset_postdata();

        homey_pagination_halfmap( $query_args->max_num_pages, $paged, $range = 2 );

        $listings_html = ob_get_contents();
        ob_end_clean();

        if( count($listings) > 0 ) {
            echo json_encode( array( 'getListings' => true, 'listings' => $listings, 'total_results' => $total_listings.' '.$rental_text, 'listingHtml' => $listings_html ) );
            exit();
        } else {
            echo json_encode( array( 'getListings' => false, 'total_results' => $total_listings.' '.$rental_text ) );
            exit();
        }
        die();
    }
}
