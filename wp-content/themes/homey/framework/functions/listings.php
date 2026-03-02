<?php
if( !function_exists('homey_get_listing_data')) {
    function homey_get_listing_data($field) {
        $prefix = 'homey_';
        $data = get_post_meta(get_the_ID(), $prefix.$field, true);

        if($data != '') {
            return $data;
        }
        return '';
    }
}

if( !function_exists('homey_listing_data')) {
    function homey_listing_data($field) {
        echo homey_get_listing_data($field);
    }
}

if( !function_exists('homey_affiliate_booking_link')) {
    function homey_affiliate_booking_link() {
        global $post;

        $affiliate_booking_link = get_post_meta( $post->ID, 'homey_affiliate_booking_link', true );

        if( !empty($affiliate_booking_link) ) {
            return esc_url($affiliate_booking_link);
        }
        return false;
    }
}

if( !function_exists('homey_get_listing_data_by_id')) {
    function homey_get_listing_data_by_id($field, $ID) {
        $prefix = 'homey_';
        $data = get_post_meta($ID, $prefix.$field, true);

        if($data != '') {
            return $data;
        }
        return '';
    }
}

if( !function_exists('homey_listing_data_by_id')) {
    function homey_listing_data_by_id($field, $ID) {
        echo homey_get_listing_data_by_id($field, $ID);
    }
}

if( !function_exists('homey_listing_permalink')) {
    function homey_listing_permalink($listing_id = null) {

        $link = esc_url(get_permalink());

        $homey_booking_type = homey_booking_type();

        if($homey_booking_type == 'per_hour') {

            if( isset($_REQUEST['arrive']) || isset($_REQUEST['start']) ){

                $guest = isset($_REQUEST['guest']) ? $_REQUEST['guest'] : '';
                $check_in = isset($_REQUEST['arrive']) ? $_REQUEST['arrive'] : '';

                $start = isset($_REQUEST['start']) ? $_REQUEST['start'] : '';
                $end = isset($_REQUEST['end']) ? $_REQUEST['end'] : '';

                $start = isset($_REQUEST['start_hour']) ? $_REQUEST['start_hour'] : $start;
                $end   = isset($_REQUEST['end_hour']) ? $_REQUEST['end_hour'] : $end;

                $check_in  =   sanitize_text_field ( $check_in );
                $start   =   sanitize_text_field ( $start );
                $end   =   sanitize_text_field ( $end );
                $guest   =   sanitize_text_field ( $guest );

                $link       =   add_query_arg( 'arrive', (trim($check_in)), $link);
                $link       =   add_query_arg( 'start',(trim($start)), $link);
                $link       =   add_query_arg( 'end',(trim($end)), $link);
                $link       =   add_query_arg( 'guest',(trim($guest )), $link);
            }

        } else {
            if( isset($_REQUEST['arrive']) || isset($_REQUEST['depart']) ){

                $guest = isset($_REQUEST['guest']) ? $_REQUEST['guest'] : '';
                $check_in = isset($_REQUEST['arrive']) ? $_REQUEST['arrive'] : '';
                $check_out = isset($_REQUEST['depart']) ? $_REQUEST['depart'] : '';

                $check_in  =   sanitize_text_field ( $check_in );
                $check_out   =   sanitize_text_field ( $check_out );
                $guest   =   sanitize_text_field ( $guest );
                if(!empty(trim($check_in))){
                    $link       =   add_query_arg( 'arrive', (trim($check_in)), $link);
                }
                if(!empty(trim($check_out))){
                    $link       =   add_query_arg( 'depart',(trim($check_out)), $link);
                }
                if(!empty(trim($guest))){
                    $link       =   add_query_arg( 'guest',(trim($guest )), $link);
                }
            }
        }


        return $link;
    }
}

if( !function_exists('homey_get_dates_for_booking')) {
    function homey_get_dates_for_booking() {
        $dates_array = array();
        $dates_array['arrive'] = isset($_GET['arrive']) ? $_GET['arrive'] : '';
        $dates_array['depart'] = isset($_GET['depart']) ? $_GET['depart'] : '';
        $dates_array['guest'] = isset($_GET['guest']) ? $_GET['guest'] : '';
        $dates_array['start'] = isset($_GET['start']) ? $_GET['start'] : '';
        $dates_array['end'] = isset($_GET['end']) ? $_GET['end'] : '';
        $dates_array['new_reser_request_user_email'] = isset($_GET['new_reser_request_user_email']) ? $_GET['new_reser_request_user_email'] : '';

        return $dates_array;
    }
}

if( !function_exists('homey_field_meta')) {
    function homey_field_meta($field_name) {
        global $listing_meta_data;

        $prefix = 'homey_';
        $field_name = $prefix.$field_name;

        if (isset($listing_meta_data[$field_name])) {
           echo sanitize_text_field($listing_meta_data[$field_name][0]);
        } else {
            return;
        }
    }
}

if( !function_exists('homey_get_field_meta')) {
    function homey_get_field_meta($field_name) {
        global $listing_meta_data;

        $prefix = 'homey_';
        $field_name = $prefix.$field_name;

        if (isset($listing_meta_data[$field_name])) {
           return sanitize_text_field($listing_meta_data[$field_name][0]);
        } else {
            return;
        }
    }
}

/*-----------------------------------------------------------------------------------*/
// Listing filter
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_listing_filter_callback') ) {
    function homey_listing_filter_callback( $lsiting_qry ) {
        global $paged;
        $prefix = 'homey_';

        $page_id = get_the_ID();

        $tax_query = array();
        $meta_query = array();


        $types = get_post_meta( $page_id, $prefix.'types', false );
        if ( ! empty( $types ) && is_array( $types ) ) {
            $tax_query[] = array(
                'taxonomy' => 'listing_type',
                'field' => 'slug',
                'terms' => $types
            );
        }

        $room_types = get_post_meta( $page_id, $prefix.'room_types', false );
        if ( ! empty( $room_types ) && is_array( $room_types ) ) {
            $tax_query[] = array(
                'taxonomy' => 'room_type',
                'field' => 'slug',
                'terms' => $room_types
            );
        }

        $amenities = get_post_meta( $page_id, $prefix.'amenities', false );
        if ( ! empty( $amenities ) && is_array( $amenities ) ) {
            $tax_query[] = array(
                'taxonomy' => 'listing_amenity',
                'field' => 'slug',
                'terms' => $amenities
            );
        }

        $facilities = get_post_meta( $page_id, $prefix.'facilities', false );
        if ( ! empty( $facilities ) && is_array( $facilities ) ) {
            $tax_query[] = array(
                'taxonomy' => 'listing_facility',
                'field' => 'slug',
                'terms' => $facilities
            );
        }

        $countries = get_post_meta( $page_id, $prefix.'countries', false );
        if ( ! empty( $countries ) && is_array( $countries ) ) {
            $tax_query[] = array(
                'taxonomy' => 'listing_country',
                'field' => 'slug',
                'terms' => $countries
            );
        }

        $states = get_post_meta( $page_id, $prefix.'states', false );
        if ( ! empty( $states ) && is_array( $states ) ) {
            $tax_query[] = array(
                'taxonomy' => 'listing_state',
                'field' => 'slug',
                'terms' => $states
            );
        }

        $cities = get_post_meta( $page_id, $prefix.'cities', false );
        if ( ! empty( $cities ) && is_array( $cities ) ) {
            $tax_query[] = array(
                'taxonomy' => 'listing_city',
                'field' => 'slug',
                'terms' => $cities
            );
        }

        $areas = get_post_meta( $page_id, $prefix.'areas', false );
        if ( ! empty( $areas ) && is_array( $areas ) ) {
            $tax_query[] = array(
                'taxonomy' => 'listing_area',
                'field' => 'slug',
                'terms' => $areas
            );
        }


        $listings_booking_type = get_post_meta( $page_id, $prefix.'listings_booking_type', true );
        if( !empty($listings_booking_type) ) {
            $meta_query[] = array(
                'key'     => $prefix.'booking_type',
                'value'   => $listings_booking_type,
                'compare' => '=',
                'type'    => 'CHAR'
            );
        }

        $tax_count = count( $tax_query );
        if( $tax_count > 1 ) {
            $tax_query['relation'] = 'AND';
        }
        if( $tax_count > 0 ) {
            $lsiting_qry['tax_query'] = $tax_query;
        }

        $meta_count = count($meta_query);
        if( $meta_count > 1 ) {
            $meta_query['relation'] = 'AND';
        }

        if( $meta_count > 0 ) {
            $lsiting_qry['meta_query'] = $meta_query;
        }

        //print_r($lsiting_qry);
        return $lsiting_qry;
    }
}
add_filter('homey_listing_filter', 'homey_listing_filter_callback');

/* -----------------------------------------------------------------------------------------------------------
*  Stripe upgrade to featured payment
-------------------------------------------------------------------------------------------------------------*/
if( !function_exists('homey_stripe_payment_for_featured') ) {
    function homey_stripe_payment_for_featured() {

        $userID = get_current_user_id();
        $listing_id     = isset($_GET['upgrade_id']) ? $_GET['upgrade_id'] : '';
        $amount = floatval( homey_option('price_featured_listing') );

        require_once( HOMEY_PLUGIN_PATH . '/classes/class-stripe.php' );

        $stripe_payments = new Homey_Stripe();

        $description = esc_html__( 'Upgrade to Featured, Lisiting ID','homey').' '.$listing_id;

        print '<div class="stripe-wrapper" id="homey_stripe_simple"> ';
            $metadata=array(
                'listing_id'        =>  $listing_id,
                'userID'            =>  $userID,
                'payment_type'      =>  'featured_fee',
                'message'           =>  esc_html__( 'Featured Fee','homey')
            );

            $stripe_payments->homey_stripe_form($amount, $metadata, $description);
        print'
        </div>';
    }
}

if( !function_exists('homey_stripe_payment_for_featured_old') ) {
    function homey_stripe_payment_for_featured_old() {

        require_once( HOMEY_PLUGIN_PATH . '/includes/stripe-php/init.php' );

        $stripe_secret_key = homey_option('stripe_secret_key');
        $stripe_publishable_key = homey_option('stripe_publishable_key');

        $stripe = array(
            "secret_key" => $stripe_secret_key,
            "publishable_key" => $stripe_publishable_key
        );

        \Stripe\Stripe::setApiKey($stripe['secret_key']);
        $submission_currency = homey_option('payment_currency');
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $user_email = $current_user->user_email;

        $listing_id     = isset($_GET['upgrade_id']) ? $_GET['upgrade_id'] : '';
        $upfront_payment = floatval( homey_option('price_featured_listing') );

        if( $submission_currency == 'JPY') {
            $upfront_payment = $upfront_payment;
        } else {
            $upfront_payment = $upfront_payment * 100;
        }


        print '
        <div class="homey_stripe_simple">
            <script src="https://checkout.stripe.com/checkout.js"
            class="stripe-button"
            data-key="' . $stripe_publishable_key . '"
            data-amount="' . $upfront_payment . '"
            data-email="' . $user_email . '"
            data-zip-code="true"
            data-billing-address="true"
            data-locale="'.get_locale().'"
            data-currency="' . $submission_currency . '"
            data-label="' . esc_html__('Pay with Credit Card', 'homey') . '"
            data-description="' . esc_html__('Featured Payment', 'homey') . '">
            </script>
        </div>
        <input type="hidden" id="listing_id" name="listing_id" value="' . $listing_id . '">
        <input type="hidden" id="reservation_pay" name="reservation_pay" value="0">
        <input type="hidden" id="featured_pay" name="featured_pay" value="1">
        <input type="hidden" name="userID" value="' . $userID . '">
        <input type="hidden" id="pay_ammout" name="pay_ammout" value="' . $upfront_payment . '">
        ';
    }
}

/* --------------------------------------------------------------------------
* Listings load more
* --------------------------------------------------------------------------- */
add_action( 'wp_ajax_nopriv_homey_loadmore_listings', 'homey_loadmore_listings' );
add_action( 'wp_ajax_homey_loadmore_listings', 'homey_loadmore_listings' );

if ( !function_exists( 'homey_loadmore_listings' ) ) {
    function homey_loadmore_listings() {
        global $post, $homey_prefix, $homey_local;
        $homey_prefix = 'homey_';
        $homey_local = homey_get_localization();

        $fake_loop_offset = 0;
        $tax_query = array();
        $meta_query = array();

        $type = sanitize_text_field($_POST['type']);
        $roomtype = sanitize_text_field($_POST['roomtype']);
        $country = sanitize_text_field($_POST['country']);
        $state = sanitize_text_field($_POST['state']);
        $city = sanitize_text_field($_POST['city']);
        $area = sanitize_text_field($_POST['area']);
        $booking_type = sanitize_text_field($_POST['booking_type']);

        $listing_style = sanitize_text_field($_POST['style']);
        $listing_type = homey_traverse_comma_string($type);
        $listing_roomtype = homey_traverse_comma_string($roomtype);
        $listing_country = homey_traverse_comma_string($country);
        $listing_state = homey_traverse_comma_string($state);
        $listing_city = homey_traverse_comma_string($city);
        $listing_area = homey_traverse_comma_string($area);
        $featured = sanitize_text_field($_POST['featured']);
        $posts_limit = sanitize_text_field($_POST['limit']);
        $sort_by = sanitize_text_field($_POST['sort_by']);
        $offset = sanitize_text_field($_POST['offset']);
        $paged = sanitize_text_field($_POST['paged']);
        //originally commented, but zahid removed after client bug report
        $author = isset($_POST['author']) ? sanitize_text_field($_POST['author']) : '';
        $authorid = isset($_POST['authorid']) ? sanitize_text_field($_POST['authorid']) : '';
        //originally commented, but zahid removed after client bug report
        $wp_query_args = array(
            'ignore_sticky_posts' => 1
        );


        if (!empty($listing_type)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_type',
                'field' => 'slug',
                'terms' => $listing_type
            );
        }

        if (!empty($listing_roomtype)) {
            $tax_query[] = array(
                'taxonomy' => 'room_type',
                'field' => 'slug',
                'terms' => $listing_roomtype
            );
        }

        if (!empty($listing_country)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_country',
                'field' => 'slug',
                'terms' => $listing_country
            );
        }
        if (!empty($listing_state)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_state',
                'field' => 'slug',
                'terms' => $listing_state
            );
        }
        if (!empty($listing_city)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_city',
                'field' => 'slug',
                'terms' => $listing_city
            );
        }
        if (!empty($listing_area)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_area',
                'field' => 'slug',
                'terms' => $listing_area
            );
        }

        //originally commented, but zahid removed after client bug report
        if($author == 'yes') {
            $wp_query_args['author'] = $authorid;
        }
        //originally commented, but zahid removed after client bug report

        if ( $sort_by == 'a_price' ) {
            $wp_query_args['orderby'] = 'meta_value_num';
            $wp_query_args['meta_key'] = 'homey_night_price';
            $wp_query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_price' ) {
            $wp_query_args['orderby'] = 'meta_value_num';
            $wp_query_args['meta_key'] = 'homey_night_price';
            $wp_query_args['order'] = 'DESC';
        } else if ( $sort_by == 'a_rating' ) {
            $wp_query_args['orderby'] = 'meta_value_num';
            $wp_query_args['meta_key'] = 'listing_total_rating';
            $wp_query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_rating' ) {
            $wp_query_args['orderby'] = 'meta_value_num';
            $wp_query_args['meta_key'] = 'listing_total_rating';
            $wp_query_args['order'] = 'DESC';
        } else if ( $sort_by == 'featured' ) {
            $wp_query_args['meta_key'] = 'homey_featured';
            $wp_query_args['meta_value'] = '1';
        } else if ( $sort_by == 'a_date' ) {
            $wp_query_args['orderby'] = 'date';
            $wp_query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_date' ) {
            $wp_query_args['orderby'] = 'date';
            $wp_query_args['order'] = 'DESC';
        } else if ( $sort_by == 'featured_top' ) {
            $wp_query_args['orderby'] = 'meta_value date';
            $wp_query_args['meta_key'] = 'homey_featured';
            $wp_query_args['order'] = 'DESC';
        }

        if( !empty($booking_type) ) {
            $meta_query[] = array(
                'key'     => 'homey_booking_type',
                'value'   => $booking_type,
                'compare' => '=',
                'type'    => 'CHAR'
            );
        }

        if (!empty($featured)) {

            if( $featured == "yes" ) {
                $wp_query_args['meta_key'] = 'homey_featured';
                $wp_query_args['meta_value'] = '1';
            } else {
                $wp_query_args['meta_key'] = 'homey_featured';
                $wp_query_args['meta_value'] = '0';
            }
        }

        $tax_count = count( $tax_query );


        if( $tax_count > 1 ) {
            $tax_query['relation'] = 'AND';
        }
        if( $tax_count > 0 ){
            $wp_query_args['tax_query'] = $tax_query;
        }

        $meta_count = count($meta_query);
        if( $meta_count > 1 ) {
            $meta_query['relation'] = 'AND';
        }

        if( $meta_count > 0 ) {
            $wp_query_args['meta_query'] = $meta_query;
        }

        $wp_query_args['post_status'] = 'publish';

        if (empty($posts_limit)) {
            $posts_limit = get_option('posts_per_page');
        }
        $wp_query_args['posts_per_page'] = $posts_limit;

        if (!empty($paged)) {
            $wp_query_args['paged'] = $paged;
        } else {
            $wp_query_args['paged'] = 1;
        }

        if (!empty($offset) and $paged > 1) {
            $wp_query_args['offset'] = $offset + ( ($paged - 1) * $posts_limit) ;
        } else {
            $wp_query_args['offset'] = $offset ;
        }

        $fake_loop_offset = $offset;
        $wp_query_args['post_type'] = 'listing';

        $the_query = new WP_Query($wp_query_args);

        if ($the_query->have_posts()) :
            while ($the_query->have_posts()) : $the_query->the_post();

                if($listing_style == 'card') {
                    get_template_part('template-parts/listing/listing-card');
                } else {
                    get_template_part('template-parts/listing/listing-item');
                }

            endwhile;
            wp_reset_postdata();
        else:
            echo 'no_result';
        endif;

        wp_die();
    }
}


add_filter('homey_optimized_filter', 'homey_optimized_filter_callback', 10, 5);
if( !function_exists('homey_optimized_filter_callback') ) {
    function homey_optimized_filter_callback( $query_args, $north_east_lat, $north_east_lng, $south_west_lat, $south_west_lng ) {

        global $wpdb;
        $table_name  = $wpdb->prefix . 'homey_map';

        if ( ! ( $north_east_lat && $north_east_lng && $south_west_lat && $south_west_lng ) ) {
            return $query_args;
        }

        $sql = $wpdb->prepare(
            "
            SELECT listing_id
            FROM $table_name
            WHERE latitude <= %s
            AND latitude >= %s
            AND longitude <= %s
            AND longitude >= %s
            ",
            $north_east_lat,
            $south_west_lat,
            $south_west_lng,
            $north_east_lng
        );

        $post_ids = $wpdb->get_results( $sql, OBJECT_K );

        if ( empty( $post_ids ) || ! $post_ids ) {
            $post_ids = array(0);
        }

        $query_args[ 'post__in' ] = array_keys( (array) $post_ids );
        return $query_args;
    }
}

add_action( 'wp_ajax_nopriv_homey_header_map', 'homey_header_map' );
add_action( 'wp_ajax_homey_header_map', 'homey_header_map' );
if( !function_exists('homey_header_map') ) {
    function homey_header_map() {
        $local = homey_get_localization();
        $tax_query = array();
        $meta_query = array();
        $listings = array();

        $cgl_meta = homey_option('cgl_meta');
        $cgl_beds = homey_option('cgl_beds');
        $cgl_baths = homey_option('cgl_baths');
        $cgl_guests = homey_option('cgl_guests');
        $cgl_types = homey_option('cgl_types');
        $price_separator = homey_option('currency_separator');

        //check_ajax_referer('homey_map_ajax_nonce', 'security');

        $prefix = 'homey_';
        $query_args = array(
            'post_type' => 'listing',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        );

        if( !empty( $_POST["optimized_loading"] ) ) {
            $north_east_lat = sanitize_text_field($_POST['north_east_lat']);
            $north_east_lng = sanitize_text_field($_POST['north_east_lng']);
            $south_west_lat = sanitize_text_field($_POST['south_west_lat']);
            $south_west_lng = sanitize_text_field($_POST['south_west_lng']);

            $query_args = apply_filters('homey_optimized_filter', $query_args, $north_east_lat, $north_east_lng, $south_west_lat, $south_west_lng );
        }

        $map_cities = isset($_POST['map_cities']) ? $_POST['map_cities'] : '';

        if (!empty($map_cities)) {
            $tax_query[] = array(
                'taxonomy' => 'listing_city',
                'field' => 'slug',
                'terms' => $map_cities
            );
        }

        $tax_count = count($tax_query);

        $tax_query['relation'] = 'AND';

        if ($tax_count > 0) {
            $query_args['tax_query'] = $tax_query;
        }

        $query_args = new WP_Query( $query_args );

        while( $query_args->have_posts() ): $query_args->the_post();

            $listing_id = get_the_ID();
            $address        = get_post_meta( get_the_ID(), $prefix.'listing_address', true );
            $bedrooms       = get_post_meta( get_the_ID(), $prefix.'listing_bedrooms', true );
            $guests         = get_post_meta( get_the_ID(), $prefix.'guests', true );
            $beds           = get_post_meta( get_the_ID(), $prefix.'beds', true );
            $baths          = get_post_meta( get_the_ID(), $prefix.'baths', true );
            $night_price          = get_post_meta( get_the_ID(), $prefix.'night_price', true );
            $location = get_post_meta( get_the_ID(), $prefix.'listing_location',true);
            $lat_long = explode(',', $location);

            $listing_type = wp_get_post_terms( get_the_ID(), 'listing_type', array("fields" => "ids") );

            $listing_price = homey_get_price_by_id($listing_id);

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

            if( has_post_thumbnail( $listing_id ) ) {
                $listing->thumbnail = get_the_post_thumbnail( $listing_id, 'homey-listing-thumb',  array('class' => 'img-responsive' ) );
            }else{
                $listing->thumbnail = homey_get_image_placeholder( 'homey-listing-thumb' );
            }

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

        endwhile;

        wp_reset_postdata();

        if( count($listings) > 0 ) {
            echo json_encode( array( 'getListings' => true, 'listings' => $listings ) );
            exit();
        } else {
            echo json_encode( array( 'getListings' => false ) );
            exit();
        }
        die();
    }
}

add_action( 'wp_ajax_nopriv_homey_sticky_map', 'homey_sticky_map' );
add_action( 'wp_ajax_homey_sticky_map', 'homey_sticky_map' );
if( !function_exists('homey_sticky_map') ) {
    function homey_sticky_map() {
        $local = homey_get_localization();
        $tax_query = array();
        $meta_query = array();
        $listings = array();

        $cgl_meta = homey_option('cgl_meta');
        $cgl_beds = homey_option('cgl_beds');
        $cgl_baths = homey_option('cgl_baths');
        $cgl_guests = homey_option('cgl_guests');
        $cgl_types = homey_option('cgl_types');
        $price_separator = homey_option('currency_separator');

        check_ajax_referer('homey_map_ajax_nonce', 'security');

        $prefix = 'homey_';
        $query_args = array(
            'post_type' => 'listing',
            'posts_per_page' => homey_option('sticky_map_num_posts'),
            'post_status' => 'publish'
        );

        $tax_count = count($tax_query);

        $tax_query['relation'] = 'AND';

        if ($tax_count > 0) {
            $query_args['tax_query'] = $tax_query;
        }

        $paged = sanitize_text_field($_POST['paged']);
        if (!empty($paged)) {
            $query_args['paged'] = $paged;
        } else {
            $query_args['paged'] = 1;
        }

        $query_args = new WP_Query( $query_args );

        while( $query_args->have_posts() ): $query_args->the_post();

            $listing_id = get_the_ID();
            $address        = get_post_meta( get_the_ID(), $prefix.'listing_address', true );
            $bedrooms       = get_post_meta( get_the_ID(), $prefix.'listing_bedrooms', true );
            $guests         = get_post_meta( get_the_ID(), $prefix.'guests', true );
            $beds           = get_post_meta( get_the_ID(), $prefix.'beds', true );
            $baths          = get_post_meta( get_the_ID(), $prefix.'baths', true );
            $night_price          = get_post_meta( get_the_ID(), $prefix.'night_price', true );
            $location = get_post_meta( get_the_ID(), $prefix.'listing_location',true);
            $lat_long = explode(',', $location);

            $listing_type = wp_get_post_terms( get_the_ID(), 'listing_type', array("fields" => "ids") );

            $listing_price = homey_get_price_by_id($listing_id);

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

        endwhile;

        wp_reset_postdata();

        if( count($listings) > 0 ) {
            echo json_encode( array( 'getListings' => true, 'listings' => $listings ) );
            exit();
        } else {
            echo json_encode( array( 'getListings' => false ) );
            exit();
        }
        die();
    }
}


if( !function_exists('listing_submission_filter')) {
    function listing_submission_filter($new_listing) {
        global $current_user;

        wp_get_current_user();
        $userID = $current_user->ID;
        $user_email   =   $current_user->user_email;
        $admin_email  =  get_bloginfo('admin_email');
        $totalGuestsPlusAddtionalGuests = 0;

        $listings_admin_approved = homey_option('listings_admin_approved');
        $edit_listings_admin_approved = homey_option('edit_listings_admin_approved');

        // Title
        if( isset( $_POST['listing_title']) ) {
            $new_listing['post_title'] = sanitize_text_field( $_POST['listing_title'] );
        }

        // Description
        if( isset( $_POST['description'] ) ) {
            $new_listing['post_content'] = wp_kses_post( $_POST['description'] );
        }


        if(isset($_POST['post_author_id']) && !empty($_POST['post_author_id']) ) {
            $new_listing['post_author'] = intval($_POST['post_author_id']);
        } else {
            $new_listing['post_author'] = $userID;
        }

        $submission_action = sanitize_text_field($_POST['action']);
        $listing_id = 0;

        $draft_listing_id = isset($_POST['draft_listing_id']) ? $_POST['draft_listing_id'] : '';
        $draft_listing_id = intval($draft_listing_id);

        if(!empty($draft_listing_id)) {
            $submission_action = 'update_listing';
        }

        $first_owner_userID = 0;
        if( $submission_action == 'homey_add_listing' || isset($_GET['duplication']) ) {
            $first_owner_userID = $current_user->ID;

            if( ($listings_admin_approved != 0 && !homey_is_admin())) {
                $new_listing['post_status'] = 'pending';
            } else {
                $new_listing['post_status'] = 'publish';
            }

            /*
             * Filter submission arguments before insert into database.
             */
            $new_listing = apply_filters( 'homey_before_submit_listing', $new_listing );

            do_action( 'homey_before_listing_submit', $new_listing);

            $listing_id = wp_insert_post( $new_listing );
        } else if( $submission_action == 'update_listing' ) {
            if(!empty($draft_listing_id)) {
                $new_listing['ID'] = $draft_listing_id;
            } else {
                $new_listing['ID'] = intval( $_POST['listing_id'] );
            }

//            $check_is_approved = get_post_meta( $new_listing['ID'], 'homey_firsttime_is_admin_approved', true );
//            that is removed because was not standard but one clients request.
            $check_is_approved = 1;

            if(($check_is_approved == 0 || $edit_listings_admin_approved != 0) && !homey_is_admin()) {
                $new_listing['post_status'] = 'pending';
            } else {
                $new_listing['post_status'] = 'publish';
                // is in need to be first time approved from admin?
                update_post_meta( $new_listing['ID'], 'homey_firsttime_is_admin_approved', 1 );
            }

            /*
             * Filter submission arguments before update into database.
             */
            $new_listing = apply_filters( 'homey_before_update_listing', $new_listing );

            do_action( 'homey_before_listing_update');

            $listing_id = wp_update_post( $new_listing );

        }

        if( $listing_id > 0 ) {

            $prefix = 'homey_';

            //Custom Fields
            if(class_exists('Homey_Fields_Builder')) {
                $fields_array = Homey_Fields_Builder::get_form_fields();
                if(!empty($fields_array)):
                    foreach ( $fields_array as $value ):
                        $field_name = $value->field_id;
                        $field_type = $value->type;

                        if( isset( $_POST[$field_name] ) ) {
                            if($field_type=='textarea') {
                                update_post_meta( $listing_id, 'homey_'.$field_name, $_POST[$field_name] );
                            } else {
                                update_post_meta( $listing_id, 'homey_'.$field_name, sanitize_text_field( $_POST[$field_name] ) );
                            }

                        }

                    endforeach; endif;
            }

            $listing_total_rating = get_post_meta( $listing_id, 'listing_total_rating', true );
            if( $listing_total_rating === '') {
                update_post_meta($listing_id, 'listing_total_rating', '0');
            }

            // First owner field
            if( $first_owner_userID > 0 ) {
                update_post_meta( $listing_id, $prefix.'first_owner_user_id', sanitize_text_field($first_owner_userID) );
            }

            // Booking type
            if( isset( $_POST['booking_type'] ) ) {
                update_post_meta( $listing_id, $prefix.'booking_type', sanitize_text_field( $_POST['booking_type'] ) );
            }

            // Instance
            if( isset( $_POST['instant_booking'] ) ) {
                $instance_bk = $_POST['instant_booking'];
                if($instance_bk == 'on') {
                    $instance_bk = 1;
                }
                update_post_meta( $listing_id, $prefix.'instant_booking', sanitize_text_field( $instance_bk ) );
            } else {
                update_post_meta( $listing_id, $prefix.'instant_booking', 0 );
            }



            // Bedrooms
            if( isset( $_POST['listing_bedrooms'] ) ) {
                update_post_meta( $listing_id, $prefix.'listing_bedrooms', sanitize_text_field( $_POST['listing_bedrooms'] ) );
            }

            // Guests
            if( isset( $_POST['guests'] ) ) {
                update_post_meta( $listing_id, $prefix.'guests', sanitize_text_field( $_POST['guests'] ) );

                $totalGuestsPlusAddtionalGuests =  $_POST['guests'];
            }

            // Beds
            if( isset( $_POST['beds'] ) ) {
                update_post_meta( $listing_id, $prefix.'beds', sanitize_text_field( $_POST['beds'] ) );
            }

            // Baths
            if( isset( $_POST['baths'] ) ) {
                update_post_meta( $listing_id, $prefix.'baths', sanitize_text_field( $_POST['baths'] ) );
            }

            // Rooms
            if( isset( $_POST['listing_rooms'] ) ) {
                update_post_meta( $listing_id, $prefix.'listing_rooms', sanitize_text_field( $_POST['listing_rooms'] ) );
            }

            // affiliate_booking_link
            if( isset( $_POST['affiliate_booking_link'] ) ) {
                update_post_meta( $listing_id, $prefix.'affiliate_booking_link', sanitize_text_field( $_POST['affiliate_booking_link'] ) );
            }

            // Day Date Price
            if( isset( $_POST['day_date_price'] ) ) {
                update_post_meta( $listing_id, $prefix.'day_date_price', sanitize_text_field( $_POST['day_date_price'] ) );
            }

            // Day Date Weekend Price
            if( isset( $_POST['day_date_weekends_price'] ) ) {
                update_post_meta( $listing_id, $prefix.'day_date_weekends_price', sanitize_text_field( $_POST['day_date_weekends_price'] ) );
            }

            // Night Price
            if( isset( $_POST['night_price'] ) ) {
                update_post_meta( $listing_id, $prefix.'night_price', sanitize_text_field( $_POST['night_price'] ) );
            }

            // Weekend Price
            if( isset( $_POST['weekends_price'] ) ) {
                update_post_meta( $listing_id, $prefix.'weekends_price', sanitize_text_field( $_POST['weekends_price'] ) );
            }

            // Hourly Price
            if( isset( $_POST['hour_price'] ) ) {
                update_post_meta( $listing_id, $prefix.'hour_price', sanitize_text_field( $_POST['hour_price'] ) );
            }

            // After Price label
            if( isset( $_POST['price_postfix'] ) ) {
                update_post_meta( $listing_id, $prefix.'price_postfix', sanitize_text_field( $_POST['price_postfix'] ) );
            }

            // Hourly Weekend Price
            if( isset( $_POST['hourly_weekends_price'] ) ) {
                update_post_meta( $listing_id, $prefix.'hourly_weekends_price', sanitize_text_field( $_POST['hourly_weekends_price'] ) );
            }

            // Min book Hours
            if( isset( $_POST['min_book_hours'] ) ) {
                update_post_meta( $listing_id, $prefix.'min_book_hours', sanitize_text_field( $_POST['min_book_hours'] ) );
            }

            // Start Hours
            if( isset( $_POST['start_hour'] ) ) {
                update_post_meta( $listing_id, $prefix.'start_hour', sanitize_text_field( $_POST['start_hour'] ) );
            }

            // End Hours
            if( isset( $_POST['end_hour'] ) ) {
                update_post_meta( $listing_id, $prefix.'end_hour', sanitize_text_field( $_POST['end_hour'] ) );
            }

            if( isset( $_POST['weekends_days'] ) ) {
                update_post_meta( $listing_id, $prefix.'weekends_days', sanitize_text_field( $_POST['weekends_days'] ) );
            }

            // Week( 7 Nights ) Price
            if( isset( $_POST['priceWeek'] ) ) {
                update_post_meta( $listing_id, $prefix.'priceWeek', sanitize_text_field( $_POST['priceWeek'] ) );
            }

            // Monthly ( 30 Nights ) Price
            if( isset( $_POST['priceMonthly'] ) ) {
                update_post_meta( $listing_id, $prefix.'priceMonthly', sanitize_text_field( $_POST['priceMonthly'] ) );
            }

            // Additional Guests price
            if( isset( $_POST['additional_guests_price'] ) ) {
                update_post_meta( $listing_id, $prefix.'additional_guests_price', sanitize_text_field( $_POST['additional_guests_price'] ) );
            }

            // Additional Guests allowed
            if( isset( $_POST['num_additional_guests'] ) ) {
                update_post_meta( $listing_id, $prefix.'num_additional_guests', sanitize_text_field( $_POST['num_additional_guests'] ) );

                //If additional guests set lets add them to the total count
                $totalGuestsPlusAddtionalGuests += (int) $_POST['num_additional_guests'];
            }

            //Now update the meta data with the total guest count
            update_post_meta( $listing_id, $prefix.'total_guests_plus_additional_guests', $totalGuestsPlusAddtionalGuests );

            // Security Deposit
            if( isset( $_POST['allow_additional_guests'] ) ) {
                update_post_meta( $listing_id, $prefix.'allow_additional_guests', sanitize_text_field( $_POST['allow_additional_guests'] ) );
            }

            // Cleaning fee
            if( isset( $_POST['cleaning_fee'] ) ) {
                update_post_meta( $listing_id, $prefix.'cleaning_fee', sanitize_text_field( $_POST['cleaning_fee'] ) );
            }

            // Cleaning fee
            if( isset( $_POST['cleaning_fee_type'] ) ) {
                update_post_meta( $listing_id, $prefix.'cleaning_fee_type', sanitize_text_field( $_POST['cleaning_fee_type'] ) );
            }

            // City fee
            if( isset( $_POST['city_fee'] ) ) {
                update_post_meta( $listing_id, $prefix.'city_fee', sanitize_text_field( $_POST['city_fee'] ) );
            }

            // City fee
            if( isset( $_POST['city_fee_type'] ) ) {
                update_post_meta( $listing_id, $prefix.'city_fee_type', sanitize_text_field( $_POST['city_fee_type'] ) );
            }

            // securityDeposit
            if( isset( $_POST['security_deposit'] ) ) {
                update_post_meta( $listing_id, $prefix.'security_deposit', sanitize_text_field( $_POST['security_deposit'] ) );
            }

            // securityDeposit
            if( isset( $_POST['tax_rate'] ) ) {
                update_post_meta( $listing_id, $prefix.'tax_rate', sanitize_text_field( $_POST['tax_rate'] ) );
            }

            // Listing size
            if( isset( $_POST['listing_size'] ) ) {
                update_post_meta( $listing_id, $prefix.'listing_size', sanitize_text_field( $_POST['listing_size'] ) );
            }

            // Listing size
            if( isset( $_POST['listing_size_unit'] ) ) {
                update_post_meta( $listing_id, $prefix.'listing_size_unit', sanitize_text_field( $_POST['listing_size_unit'] ) );
            }

            // Address
            if( isset( $_POST['listing_address'] ) ) {
                update_post_meta( $listing_id, $prefix.'listing_address', sanitize_text_field( $_POST['listing_address'] ) );
            }

            //AptSuit
            if( isset( $_POST['aptSuit'] ) ) {
                update_post_meta( $listing_id, $prefix.'aptSuit', sanitize_text_field( $_POST['aptSuit'] ) );
            }


            // Cancellation Policy
            if( isset( $_POST['cancellation_policy'] ) ) {
                update_post_meta( $listing_id, $prefix.'cancellation_policy', sanitize_textarea_field( $_POST['cancellation_policy'] ) );
            }

            // Minimum Stay
            if( isset( $_POST['min_book_days'] ) ) {
                update_post_meta( $listing_id, $prefix.'min_book_days', sanitize_text_field( $_POST['min_book_days'] ) );
            }

            if( isset( $_POST['min_book_weeks'] ) ) {
                update_post_meta( $listing_id, $prefix.'min_book_weeks', sanitize_text_field( $_POST['min_book_weeks'] ) );
            }

            if( isset( $_POST['min_book_months'] ) ) {
                update_post_meta( $listing_id, $prefix.'min_book_months', sanitize_text_field( $_POST['min_book_months'] ) );
            }

            // Maximum Stay
            if( isset( $_POST['max_book_days'] ) ) {
                update_post_meta( $listing_id, $prefix.'max_book_days', sanitize_text_field( $_POST['max_book_days'] ) );
            }
            if( isset( $_POST['max_book_weeks'] ) ) {
                update_post_meta( $listing_id, $prefix.'max_book_weeks', sanitize_text_field( $_POST['max_book_weeks'] ) );
            }
            if( isset( $_POST['max_book_months'] ) ) {
                update_post_meta( $listing_id, $prefix.'max_book_months', sanitize_text_field( $_POST['max_book_months'] ) );
            }

            // Check in After
            if( isset( $_POST['checkin_after'] ) ) {
                update_post_meta( $listing_id, $prefix.'checkin_after', sanitize_text_field( $_POST['checkin_after'] ) );
            }

            // Check Out After
            if( isset( $_POST['checkout_before'] ) ) {
                update_post_meta( $listing_id, $prefix.'checkout_before', sanitize_text_field( $_POST['checkout_before'] ) );
            }

            // Allow Smoke
            if( isset( $_POST['smoke'] ) ) {
                update_post_meta( $listing_id, $prefix.'smoke', sanitize_text_field( $_POST['smoke'] ) );
            }

            // Allow Pets
            if( isset( $_POST['pets'] ) ) {
                update_post_meta( $listing_id, $prefix.'pets', sanitize_text_field( $_POST['pets'] ) );
            }

            // Allow Party
            if( isset( $_POST['party'] ) ) {
                update_post_meta( $listing_id, $prefix.'party', sanitize_text_field( $_POST['party'] ) );
            }

            // Allow Childred
            if( isset( $_POST['children'] ) ) {
                update_post_meta( $listing_id, $prefix.'children', sanitize_text_field( $_POST['children'] ) );
            }

            // Additional Rules
            if( isset( $_POST['additional_rules'] ) ) {
                update_post_meta( $listing_id, $prefix.'additional_rules', sanitize_textarea_field( $_POST['additional_rules'] ) );
            }

            if( isset( $_POST['homey_accomodation'] ) ) {
                $homey_accomodation = $_POST['homey_accomodation'];
                if( ! empty( $homey_accomodation ) ) {
                    update_post_meta( $listing_id, $prefix.'accomodation', $homey_accomodation );
                }
            } else {
                update_post_meta( $listing_id, $prefix.'accomodation', '' );
            }

            if( isset( $_POST['homey_services'] ) ) {
                $homey_services = $_POST['homey_services'];
                if( ! empty( $homey_services ) ) {
                    update_post_meta( $listing_id, $prefix.'services', $homey_services );
                }
            } else {
                update_post_meta( $listing_id, $prefix.'services', '' );
            }

            if( isset( $_POST['extra_price'] ) ) {
                $extra_price = $_POST['extra_price'];
                if( ! empty( $extra_price ) ) {
                    update_post_meta( $listing_id, $prefix.'extra_prices', $extra_price );
                }
            } else {
                update_post_meta( $listing_id, $prefix.'extra_prices', '' );
            }

            // Openning Hours
            if( isset( $_POST['mon_fri_open'] ) ) {
                update_post_meta( $listing_id, $prefix.'mon_fri_open', sanitize_text_field( $_POST['mon_fri_open'] ) );
            }
            if( isset( $_POST['mon_fri_close'] ) ) {
                update_post_meta( $listing_id, $prefix.'mon_fri_close', sanitize_text_field( $_POST['mon_fri_close'] ) );
            }
            if( isset( $_POST['mon_fri_closed'] ) ) {
                update_post_meta( $listing_id, $prefix.'mon_fri_closed', sanitize_text_field( $_POST['mon_fri_closed'] ) );
            } else {
                update_post_meta( $listing_id, $prefix.'mon_fri_closed', 0 );
            }

            if( isset( $_POST['sat_open'] ) ) {
                update_post_meta( $listing_id, $prefix.'sat_open', sanitize_text_field( $_POST['sat_open'] ) );
            }
            if( isset( $_POST['sat_close'] ) ) {
                update_post_meta( $listing_id, $prefix.'sat_close', sanitize_text_field( $_POST['sat_close'] ) );
            }
            if( isset( $_POST['sat_closed'] ) ) {
                update_post_meta( $listing_id, $prefix.'sat_closed', sanitize_text_field( $_POST['sat_closed'] ) );
            } else {
                update_post_meta( $listing_id, $prefix.'sat_closed', 0 );
            }


            if( isset( $_POST['sun_open'] ) ) {
                update_post_meta( $listing_id, $prefix.'sun_open', sanitize_text_field( $_POST['sun_open'] ) );
            }
            if( isset( $_POST['sun_close'] ) ) {
                update_post_meta( $listing_id, $prefix.'sun_close', sanitize_text_field( $_POST['sun_close'] ) );
            }
            if( isset( $_POST['sun_closed'] ) ) {
                update_post_meta( $listing_id, $prefix.'sun_closed', sanitize_text_field( $_POST['sun_closed'] ) );
            } else {
                update_post_meta( $listing_id, $prefix.'sun_closed', 0 );
            }


            // Postal Code
            if( isset( $_POST['zip'] ) ) {
                update_post_meta( $listing_id, $prefix.'zip', sanitize_text_field( $_POST['zip'] ) );
            }

            // Country
            if( isset( $_POST['country'] ) ) {
                $listing_country = sanitize_text_field( $_POST['country'] );
                $country_id = wp_set_object_terms( $listing_id, $listing_country, 'listing_country' );
            }

            // State
            if( isset( $_POST['administrative_area_level_1'] ) ) {
                $listing_state = sanitize_text_field( $_POST['administrative_area_level_1'] );
                $state_id = wp_set_object_terms( $listing_id, $listing_state, 'listing_state' );

                $homey_meta = array();
                $homey_meta['parent_country'] = isset( $_POST['country'] ) ? $_POST['country'] : '';
                if( !empty( $state_id) ) {
                    update_option('_homey_listing_state_' . $state_id[0], $homey_meta);
                }
            }

            // City
            if( isset( $_POST['locality'] ) ) {
                $listing_city = sanitize_text_field( $_POST['locality'] );
                $city_id = wp_set_object_terms( $listing_id, $listing_city, 'listing_city' );

                $homey_meta = array();
                $homey_meta['parent_state'] = isset( $_POST['administrative_area_level_1'] ) ? $_POST['administrative_area_level_1'] : '';
                if( !empty( $city_id) ) {
                    update_option('_homey_listing_city_' . $city_id[0], $homey_meta);
                }
            }

            // Area
            if( isset( $_POST['neighborhood'] ) ) {
                $listing_area = sanitize_text_field( $_POST['neighborhood'] );
                $area_id = wp_set_object_terms( $listing_id, $listing_area, 'listing_area' );

                $homey_meta = array();
                $homey_meta['parent_city'] = isset( $_POST['locality'] ) ? $_POST['locality'] : '';
                if( !empty( $area_id) ) {
                    update_option('_homey_listing_area_' . $area_id[0], $homey_meta);
                }
            }

            // Make featured
            if( isset( $_POST['listing_featured'] ) ) {
                $featured = intval( $_POST['listing_featured'] );
                update_post_meta( $listing_id, 'homey_featured', $featured );
            }


            if( ( isset($_POST['lat']) && !empty($_POST['lat']) ) && (  isset($_POST['lng']) && !empty($_POST['lng'])  ) ) {
                $lat = sanitize_text_field( $_POST['lat'] );
                $lng = sanitize_text_field( $_POST['lng'] );
                $lat_lng = $lat.','.$lng;

                update_post_meta( $listing_id, $prefix.'geolocation_lat', $lat );
                update_post_meta( $listing_id, $prefix.'geolocation_long', $lng );
                update_post_meta( $listing_id, $prefix.'listing_location', $lat_lng );
                update_post_meta( $listing_id, $prefix.'listing_map', '1' );
                update_post_meta( $listing_id, $prefix.'show_map', 1 );


                if( $submission_action == 'homey_add_listing' ) {
                    homey_insert_lat_long($lat, $lng, $listing_id);
                } elseif ( $submission_action == 'update_listing' ) {
                    homey_update_lat_long($lat, $lng, $listing_id);
                }



            }

            // Room Type
            if( isset( $_POST['room_type'] ) && ( $_POST['room_type'] != '-1' ) ) {
                wp_set_object_terms( $listing_id, intval( $_POST['room_type'] ), 'room_type' );
            }

            // Listing Type
            if( isset( $_POST['listing_type'] ) && ( $_POST['listing_type'] != '-1' ) ) {
                wp_set_object_terms( $listing_id, intval( $_POST['listing_type'] ), 'listing_type' );
            }

            // Amenities
            if( isset( $_POST['listing_amenity'] ) ) {
                $amenities_array = array();
                foreach( $_POST['listing_amenity'] as $amenity_id ) {
                    $amenities_array[] = intval( $amenity_id );
                }
                wp_set_object_terms( $listing_id, $amenities_array, 'listing_amenity' );
            }

            // Facilities
            if( isset( $_POST['listing_facility'] ) ) {
                $facilities_array = array();
                foreach( $_POST['listing_facility'] as $facility_id ) {
                    $facilities_array[] = intval( $facility_id );
                }
                wp_set_object_terms( $listing_id, $facilities_array, 'listing_facility' );
            }


            // clean up the old meta information related to images when listing update
            if( $submission_action == "update_listing" ){
                delete_post_meta( $listing_id, 'homey_listing_images' );
                delete_post_meta( $listing_id, '_thumbnail_id' );
            }

            if( isset( $_POST['video_url'] ) ) {
                update_post_meta( $listing_id, $prefix.'video_url', sanitize_text_field( $_POST['video_url'] ) );
            }

            // Listing Images
            if( isset( $_POST['listing_image_ids'] ) ) {
                if (!empty($_POST['listing_image_ids']) && is_array($_POST['listing_image_ids'])) {
                    $listing_image_ids = array();
                    foreach ($_POST['listing_image_ids'] as $img_id ) {
                        $listing_image_ids[] = intval( $img_id );
                        add_post_meta($listing_id, 'homey_listing_images', $img_id);
                    }

                    // featured image
                    if( isset( $_POST['featured_image_id'] ) ) {
                        $featured_image_id = intval( $_POST['featured_image_id'] );
                        if( in_array( $featured_image_id, $listing_image_ids ) ) {
                            update_post_meta( $listing_id, '_thumbnail_id', $featured_image_id );
                        }
                    } elseif ( ! empty ( $listing_image_ids ) ) {
                        update_post_meta( $listing_id, '_thumbnail_id', $listing_image_ids[0] );
                    }
                }
            }

            apply_filters('listing_submission_filter_filter', $listing_id);

            if( $submission_action == 'homey_add_listing' ) {
                $post_status_text_user = esc_html__("Your listing status is published", 'homey');
                $post_status_text_admin = esc_html__("This listing status is published", 'homey');

                if( ($listings_admin_approved != 0 && !homey_is_admin())) {
                    $post_status_text_user = esc_html__("Your listing status is pending for admin approval", 'homey');
                    $post_status_text_admin = esc_html__("This listing status is in need to be approved from you.", 'homey');
                }

                $args = array(
                    'listing_title'  =>  get_the_title($listing_id),
                    'listing_id'     =>  $listing_id,
                    'post_status_user' =>  $post_status_text_user,
                    'post_status_admin' =>  $post_status_text_admin,
                );
                /*
                 * Send email
                 * */
                if( ($listings_admin_approved != 0 && !homey_is_admin())) {
                    homey_email_composer( $user_email, 'new_submission_listing', $args );
                }

                homey_email_composer( $admin_email, 'admin_new_submission_listing', $args );

                do_action( 'homey_after_listing_submit', $listing_id );

            } else if ( $submission_action == 'update_listing' ) {

                $post_status_text_user = esc_html__("Your listing status is published", 'homey');
                $post_status_text_admin = esc_html__("This listing status is published", 'homey');

                if($edit_listings_admin_approved != 0 && !homey_is_admin()) {
                    $post_status_text_user = esc_html__("Your listing status is pending for admin approval", 'homey');
                    $post_status_text_admin = esc_html__("This listing status is in need to be approved from you.", 'homey');
                }

                $args = array(
                    'listing_title'  =>  get_the_title($listing_id),
                    'listing_id'     =>  $listing_id,
                    'post_status_user' =>  $post_status_text_user,
                    'post_status_admin' =>  $post_status_text_admin,
                );
                /*
                 * Send email
                 * */
                if($edit_listings_admin_approved != 0 && !homey_is_admin()) {
                    homey_email_composer( $user_email, 'update_submission_listing', $args );
                }

                homey_email_composer( $admin_email, 'admin_update_submission_listing', $args );


                do_action( 'houmey_after_listing_update', $listing_id );
            }

            return $listing_id;
        }

    } //listing_submission_filter

    add_filter('listing_submission_filter', 'listing_submission_filter');
}


/*-----------------------------------------------------------------------------------*/
// validate Email
/*-----------------------------------------------------------------------------------*/
add_action('wp_ajax_save_as_draft', 'save_listing_as_draft');
if( !function_exists('save_listing_as_draft') ) {
    function save_listing_as_draft() {

        global $current_user;

        wp_get_current_user();
        $userID = $current_user->ID;

        $new_listing = array(
            'post_type' => 'listing'
        );

        $listings_admin_approved = homey_option('listings_admin_approved');
        $edit_listings_admin_approved = homey_option('edit_listings_admin_approved');

        // Title
        if( isset( $_POST['listing_title']) ) {
            $new_listing['post_title'] = sanitize_text_field( $_POST['listing_title'] );
        }

        // Description
        if( isset( $_POST['description'] ) ) {
            $new_listing['post_content'] = wp_kses_post( $_POST['description'] );
        }

        $new_listing['post_author'] = $userID;

        $listing_id = 0;
        $new_listing['post_status'] = 'draft';

        if( isset($_POST['draft_listing_id']) && !empty( $_POST['draft_listing_id'] ) ) {
            $new_listing['ID'] = $_POST['draft_listing_id'];
            $listing_id = wp_update_post( $new_listing );
        } else {
            $listing_id = wp_insert_post( $new_listing );
        }

        if( $listing_id > 0 ) {

            $prefix = 'homey_';

            //Custom Fields
            if(class_exists('Homey_Fields_Builder')) {
                $fields_array = Homey_Fields_Builder::get_form_fields();
                if(!empty($fields_array)):
                    foreach ( $fields_array as $value ):
                        $field_name = $value->field_id;
                        $field_type = $value->type;

                        if( isset( $_POST[$field_name] ) ) {
                            if($field_type=='textarea') {
                                update_post_meta( $listing_id, 'homey_'.$field_name, $_POST[$field_name] );
                            } else {
                                update_post_meta( $listing_id, 'homey_'.$field_name, sanitize_text_field( $_POST[$field_name] ) );
                            }

                        }

                    endforeach; endif;
            }

            $listing_total_rating = get_post_meta( $listing_id, 'listing_total_rating', true );
            if( $listing_total_rating === '') {
                update_post_meta($listing_id, 'listing_total_rating', '0');
            }

            // Booking type
            if( isset( $_POST['booking_type'] ) ) {
                update_post_meta( $listing_id, $prefix.'booking_type', sanitize_text_field( $_POST['booking_type'] ) );
            }

            // Instance
            if( isset( $_POST['instant_booking'] ) ) {
                $instance_bk = $_POST['instant_booking'];
                if($instance_bk == 'on') {
                    $instance_bk = 1;
                }
                update_post_meta( $listing_id, $prefix.'instant_booking', sanitize_text_field( $instance_bk ) );
            } else {
                update_post_meta( $listing_id, $prefix.'instant_booking', 0 );
            }



            // Bedrooms
            if( isset( $_POST['listing_bedrooms'] ) ) {
                update_post_meta( $listing_id, $prefix.'listing_bedrooms', sanitize_text_field( $_POST['listing_bedrooms'] ) );
            }

            // Guests
            if( isset( $_POST['guests'] ) ) {
                update_post_meta( $listing_id, $prefix.'guests', sanitize_text_field( $_POST['guests'] ) );
            }

            // Beds
            if( isset( $_POST['beds'] ) ) {
                update_post_meta( $listing_id, $prefix.'beds', sanitize_text_field( $_POST['beds'] ) );
            }

            // Baths
            if( isset( $_POST['baths'] ) ) {
                update_post_meta( $listing_id, $prefix.'baths', sanitize_text_field( $_POST['baths'] ) );
            }

            // Rooms
            if( isset( $_POST['listing_rooms'] ) ) {
                update_post_meta( $listing_id, $prefix.'listing_rooms', sanitize_text_field( $_POST['listing_rooms'] ) );
            }

            // Night Price
            if( isset( $_POST['night_price'] ) ) {
                update_post_meta( $listing_id, $prefix.'night_price', sanitize_text_field( $_POST['night_price'] ) );
            }

            // Weekend Price
            if( isset( $_POST['weekends_price'] ) ) {
                update_post_meta( $listing_id, $prefix.'weekends_price', sanitize_text_field( $_POST['weekends_price'] ) );
            }

            // Hourly Price
            if( isset( $_POST['hour_price'] ) ) {
                update_post_meta( $listing_id, $prefix.'hour_price', sanitize_text_field( $_POST['hour_price'] ) );
            }

            // After Price label
            if( isset( $_POST['price_postfix'] ) ) {
                update_post_meta( $listing_id, $prefix.'price_postfix', sanitize_text_field( $_POST['price_postfix'] ) );
            }

            // Hourly Weekend Price
            if( isset( $_POST['hourly_weekends_price'] ) ) {
                update_post_meta( $listing_id, $prefix.'hourly_weekends_price', sanitize_text_field( $_POST['hourly_weekends_price'] ) );
            }

            // Min book Hours
            if( isset( $_POST['min_book_hours'] ) ) {
                update_post_meta( $listing_id, $prefix.'min_book_hours', sanitize_text_field( $_POST['min_book_hours'] ) );
            }

            // Start Hours
            if( isset( $_POST['start_hour'] ) ) {
                update_post_meta( $listing_id, $prefix.'start_hour', sanitize_text_field( $_POST['start_hour'] ) );
            }

            // End Hours
            if( isset( $_POST['end_hour'] ) ) {
                update_post_meta( $listing_id, $prefix.'end_hour', sanitize_text_field( $_POST['end_hour'] ) );
            }

            if( isset( $_POST['weekends_days'] ) ) {
                update_post_meta( $listing_id, $prefix.'weekends_days', sanitize_text_field( $_POST['weekends_days'] ) );
            }

            // Week( 7 Nights ) Price
            if( isset( $_POST['priceWeek'] ) ) {
                update_post_meta( $listing_id, $prefix.'priceWeek', sanitize_text_field( $_POST['priceWeek'] ) );
            }

            // Monthly ( 30 Nights ) Price
            if( isset( $_POST['priceMonthly'] ) ) {
                update_post_meta( $listing_id, $prefix.'priceMonthly', sanitize_text_field( $_POST['priceMonthly'] ) );
            }

            // Additional Guests price
            if( isset( $_POST['additional_guests_price'] ) ) {
                update_post_meta( $listing_id, $prefix.'additional_guests_price', sanitize_text_field( $_POST['additional_guests_price'] ) );
            }

            // Security Deposit
            if( isset( $_POST['allow_additional_guests'] ) ) {
                update_post_meta( $listing_id, $prefix.'allow_additional_guests', sanitize_text_field( $_POST['allow_additional_guests'] ) );
            }

            // Cleaning fee
            if( isset( $_POST['cleaning_fee'] ) ) {
                update_post_meta( $listing_id, $prefix.'cleaning_fee', sanitize_text_field( $_POST['cleaning_fee'] ) );
            }

            // Cleaning fee
            if( isset( $_POST['cleaning_fee_type'] ) ) {
                update_post_meta( $listing_id, $prefix.'cleaning_fee_type', sanitize_text_field( $_POST['cleaning_fee_type'] ) );
            }

            // City fee
            if( isset( $_POST['city_fee'] ) ) {
                update_post_meta( $listing_id, $prefix.'city_fee', sanitize_text_field( $_POST['city_fee'] ) );
            }

            // City fee
            if( isset( $_POST['city_fee_type'] ) ) {
                update_post_meta( $listing_id, $prefix.'city_fee_type', sanitize_text_field( $_POST['city_fee_type'] ) );
            }

            // securityDeposit
            if( isset( $_POST['security_deposit'] ) ) {
                update_post_meta( $listing_id, $prefix.'security_deposit', sanitize_text_field( $_POST['security_deposit'] ) );
            }

            // securityDeposit
            if( isset( $_POST['tax_rate'] ) ) {
                update_post_meta( $listing_id, $prefix.'tax_rate', sanitize_text_field( $_POST['tax_rate'] ) );
            }

            // Listing size
            if( isset( $_POST['listing_size'] ) ) {
                update_post_meta( $listing_id, $prefix.'listing_size', sanitize_text_field( $_POST['listing_size'] ) );
            }

            // Listing size
            if( isset( $_POST['listing_size_unit'] ) ) {
                update_post_meta( $listing_id, $prefix.'listing_size_unit', sanitize_text_field( $_POST['listing_size_unit'] ) );
            }

            // Address
            if( isset( $_POST['listing_address'] ) ) {
                update_post_meta( $listing_id, $prefix.'listing_address', sanitize_text_field( $_POST['listing_address'] ) );
            }

            //AptSuit
            if( isset( $_POST['aptSuit'] ) ) {
                update_post_meta( $listing_id, $prefix.'aptSuit', sanitize_text_field( $_POST['aptSuit'] ) );
            }


            // Cancellation Policy
            if( isset( $_POST['cancellation_policy'] ) ) {
                update_post_meta( $listing_id, $prefix.'cancellation_policy', sanitize_text_field( $_POST['cancellation_policy'] ) );
            }

            // Minimum Stay
            if( isset( $_POST['min_book_days'] ) ) {
                update_post_meta( $listing_id, $prefix.'min_book_days', sanitize_text_field( $_POST['min_book_days'] ) );
            }

            // Maximum Stay
            if( isset( $_POST['max_book_days'] ) ) {
                update_post_meta( $listing_id, $prefix.'max_book_days', sanitize_text_field( $_POST['max_book_days'] ) );
            }

            // Check in After
            if( isset( $_POST['checkin_after'] ) ) {
                update_post_meta( $listing_id, $prefix.'checkin_after', sanitize_text_field( $_POST['checkin_after'] ) );
            }

            // Check Out After
            if( isset( $_POST['checkout_before'] ) ) {
                update_post_meta( $listing_id, $prefix.'checkout_before', sanitize_text_field( $_POST['checkout_before'] ) );
            }

            // Allow Smoke
            if( isset( $_POST['smoke'] ) ) {
                update_post_meta( $listing_id, $prefix.'smoke', sanitize_text_field( $_POST['smoke'] ) );
            }

            // Allow Pets
            if( isset( $_POST['pets'] ) ) {
                update_post_meta( $listing_id, $prefix.'pets', sanitize_text_field( $_POST['pets'] ) );
            }

            // Allow Party
            if( isset( $_POST['party'] ) ) {
                update_post_meta( $listing_id, $prefix.'party', sanitize_text_field( $_POST['party'] ) );
            }

            // Allow Childred
            if( isset( $_POST['children'] ) ) {
                update_post_meta( $listing_id, $prefix.'children', sanitize_text_field( $_POST['children'] ) );
            }

            // Additional Rules
            if( isset( $_POST['additional_rules'] ) ) {
                update_post_meta( $listing_id, $prefix.'additional_rules', sanitize_text_field( $_POST['additional_rules'] ) );
            }

            if( isset( $_POST['homey_accomodation'] ) ) {
                $homey_accomodation = $_POST['homey_accomodation'];
                if( ! empty( $homey_accomodation ) ) {
                    update_post_meta( $listing_id, $prefix.'accomodation', $homey_accomodation );
                }
            } else {
                update_post_meta( $listing_id, $prefix.'accomodation', '' );
            }

            if( isset( $_POST['homey_services'] ) ) {
                $homey_services = $_POST['homey_services'];
                if( ! empty( $homey_services ) ) {
                    update_post_meta( $listing_id, $prefix.'services', $homey_services );
                }
            } else {
                update_post_meta( $listing_id, $prefix.'services', '' );
            }

            // Openning Hours
            if( isset( $_POST['mon_fri_open'] ) ) {
                update_post_meta( $listing_id, $prefix.'mon_fri_open', sanitize_text_field( $_POST['mon_fri_open'] ) );
            }
            if( isset( $_POST['mon_fri_close'] ) ) {
                update_post_meta( $listing_id, $prefix.'mon_fri_close', sanitize_text_field( $_POST['mon_fri_close'] ) );
            }
            if( isset( $_POST['mon_fri_closed'] ) ) {
                update_post_meta( $listing_id, $prefix.'mon_fri_closed', sanitize_text_field( $_POST['mon_fri_closed'] ) );
            } else {
                update_post_meta( $listing_id, $prefix.'mon_fri_closed', 0 );
            }

            if( isset( $_POST['sat_open'] ) ) {
                update_post_meta( $listing_id, $prefix.'sat_open', sanitize_text_field( $_POST['sat_open'] ) );
            }
            if( isset( $_POST['sat_close'] ) ) {
                update_post_meta( $listing_id, $prefix.'sat_close', sanitize_text_field( $_POST['sat_close'] ) );
            }
            if( isset( $_POST['sat_closed'] ) ) {
                update_post_meta( $listing_id, $prefix.'sat_closed', sanitize_text_field( $_POST['sat_closed'] ) );
            } else {
                update_post_meta( $listing_id, $prefix.'sat_closed', 0 );
            }


            if( isset( $_POST['sun_open'] ) ) {
                update_post_meta( $listing_id, $prefix.'sun_open', sanitize_text_field( $_POST['sun_open'] ) );
            }
            if( isset( $_POST['sun_close'] ) ) {
                update_post_meta( $listing_id, $prefix.'sun_close', sanitize_text_field( $_POST['sun_close'] ) );
            }
            if( isset( $_POST['sun_closed'] ) ) {
                update_post_meta( $listing_id, $prefix.'sun_closed', sanitize_text_field( $_POST['sun_closed'] ) );
            } else {
                update_post_meta( $listing_id, $prefix.'sun_closed', 0 );
            }


            // Postal Code
            if( isset( $_POST['zip'] ) ) {
                update_post_meta( $listing_id, $prefix.'zip', sanitize_text_field( $_POST['zip'] ) );
            }

            // Country
            if( isset( $_POST['country'] ) ) {
                $listing_country = sanitize_text_field( $_POST['country'] );
                $country_id = wp_set_object_terms( $listing_id, $listing_country, 'listing_country' );
            }

            // State
            if( isset( $_POST['administrative_area_level_1'] ) ) {
                $listing_state = sanitize_text_field( $_POST['administrative_area_level_1'] );
                $state_id = wp_set_object_terms( $listing_id, $listing_state, 'listing_state' );

                $homey_meta = array();
                $homey_meta['parent_country'] = isset( $_POST['country'] ) ? $_POST['country'] : '';
                if( !empty( $state_id) ) {
                    update_option('_homey_listing_state_' . $state_id[0], $homey_meta);
                }
            }

            // City
            if( isset( $_POST['locality'] ) ) {
                $listing_city = sanitize_text_field( $_POST['locality'] );
                $city_id = wp_set_object_terms( $listing_id, $listing_city, 'listing_city' );

                $homey_meta = array();
                $homey_meta['parent_state'] = isset( $_POST['administrative_area_level_1'] ) ? $_POST['administrative_area_level_1'] : '';
                if( !empty( $city_id) ) {
                    update_option('_homey_listing_city_' . $city_id[0], $homey_meta);
                }
            }

            // Area
            if( isset( $_POST['neighborhood'] ) ) {
                $listing_area = sanitize_text_field( $_POST['neighborhood'] );
                $area_id = wp_set_object_terms( $listing_id, $listing_area, 'listing_area' );

                $homey_meta = array();
                $homey_meta['parent_city'] = isset( $_POST['locality'] ) ? $_POST['locality'] : '';
                if( !empty( $area_id) ) {
                    update_option('_homey_listing_area_' . $area_id[0], $homey_meta);
                }
            }

            // Make featured
            if( isset( $_POST['listing_featured'] ) ) {
                $featured = intval( $_POST['listing_featured'] );
                update_post_meta( $listing_id, 'homey_featured', $featured );
            }


            if( ( isset($_POST['lat']) && !empty($_POST['lat']) ) && (  isset($_POST['lng']) && !empty($_POST['lng'])  ) ) {
                $lat = sanitize_text_field( $_POST['lat'] );
                $lng = sanitize_text_field( $_POST['lng'] );
                $lat_lng = $lat.','.$lng;

                update_post_meta( $listing_id, $prefix.'geolocation_lat', $lat );
                update_post_meta( $listing_id, $prefix.'geolocation_long', $lng );
                update_post_meta( $listing_id, $prefix.'listing_location', $lat_lng );
                update_post_meta( $listing_id, $prefix.'listing_map', '1' );


                if( $submission_action == 'homey_add_listing' ) {
                    homey_insert_lat_long($lat, $lng, $listing_id);
                } elseif ( $submission_action == 'update_listing' ) {
                    homey_update_lat_long($lat, $lng, $listing_id);
                }



            }

            // Room Type
            if( isset( $_POST['room_type'] ) && ( $_POST['room_type'] != '-1' ) ) {
                wp_set_object_terms( $listing_id, intval( $_POST['room_type'] ), 'room_type' );
            }

            // Listing Type
            if( isset( $_POST['listing_type'] ) && ( $_POST['listing_type'] != '-1' ) ) {
                wp_set_object_terms( $listing_id, intval( $_POST['listing_type'] ), 'listing_type' );
            }

            // Amenities
            if( isset( $_POST['listing_amenity'] ) ) {
                $amenities_array = array();
                foreach( $_POST['listing_amenity'] as $amenity_id ) {
                    $amenities_array[] = intval( $amenity_id );
                }
                wp_set_object_terms( $listing_id, $amenities_array, 'listing_amenity' );
            }

            // Facilities
            if( isset( $_POST['listing_facility'] ) ) {
                $facilities_array = array();
                foreach( $_POST['listing_facility'] as $facility_id ) {
                    $facilities_array[] = intval( $facility_id );
                }
                wp_set_object_terms( $listing_id, $facilities_array, 'listing_facility' );
            }


            // clean up the old meta information related to images when listing update
            if( $submission_action == "update_listing" ){
                delete_post_meta( $listing_id, 'homey_listing_images' );
                delete_post_meta( $listing_id, '_thumbnail_id' );
            }

            if( isset( $_POST['video_url'] ) ) {
                update_post_meta( $listing_id, $prefix.'video_url', sanitize_text_field( $_POST['video_url'] ) );
            }

            // Listing Images
            if( isset( $_POST['listing_image_ids'] ) ) {
                if (!empty($_POST['listing_image_ids']) && is_array($_POST['listing_image_ids'])) {
                    $listing_image_ids = array();
                    foreach ($_POST['listing_image_ids'] as $img_id ) {
                        $listing_image_ids[] = intval( $img_id );
                        add_post_meta($listing_id, 'homey_listing_images', $img_id);
                    }

                    // featured image
                    if( isset( $_POST['featured_image_id'] ) ) {
                        $featured_image_id = intval( $_POST['featured_image_id'] );
                        if( in_array( $featured_image_id, $listing_image_ids ) ) {
                            update_post_meta( $listing_id, '_thumbnail_id', $featured_image_id );
                        }
                    } elseif ( ! empty ( $listing_image_ids ) ) {
                        update_post_meta( $listing_id, '_thumbnail_id', $listing_image_ids[0] );
                    }
                }
            }
        }

        echo json_encode( array( 'success' => true, 'listing_id' => $listing_id, 'msg' => esc_html__('Successfull', 'homey') ) );
        wp_die();
    }
}

if(!function_exists('homey_insert_lat_long')) {
    function homey_insert_lat_long($lat, $long, $list_id) {
        global $wpdb;
        $table_name  = $wpdb->prefix . 'homey_map';

        $wpdb->insert(
            $table_name,
            array(
                'latitude' => $lat,
                'longitude' => $long,
                'listing_id' => $list_id
            ),
            array(
                '%s',
                '%s',
                '%s'
            )
        );
        return true;
    }
}

if(!function_exists('homey_update_lat_long')) {
    function homey_update_lat_long($lat, $long, $list_id) {
        global $wpdb;
        $table_name  = $wpdb->prefix . 'homey_map';

        $wpdb->update(
            $table_name,
            array(
                'latitude' => $lat,  // string
                'longitude' => $long   // integer (number)
            ),
            array( 'listing_id' => $list_id ),
            array(
                '%s',   // value1
                '%s'    // value2
            ),
            array( '%d' )
        );
        return true;
    }
}

/* --------------------------------------------------------------------------
* Make listing featured for membership
* --------------------------------------------------------------------------- */
add_action( 'wp_ajax_homey_membership_featured_listing', 'homey_membership_featured_listing' );
if( ! function_exists( 'homey_membership_featured_listing' ) ) {
    function homey_membership_featured_listing() {
        $nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $nonce, 'featured_listing_nonce' ) ) {
            $ajax_response = array( 'success' => false , 'reason' => esc_html__( 'Security check failed!', 'homey' ) );
            echo json_encode( $ajax_response );
            die;
        }

        if ( !isset( $_REQUEST['listing_id'] ) ) {
            $ajax_response = array( 'success' => false , 'reason' => esc_html__( 'No listing ID found', 'homey' ) );
            echo json_encode( $ajax_response );
            die;
        }

        $listing_id = $_REQUEST['listing_id'];
        $post_author = get_post_field( 'post_author', $listing_id );

        global $current_user;
        wp_get_current_user();
        $userID      =   $current_user->ID;
        $date = date( 'Y-m-d G:i:s', current_time( 'timestamp', 0 ));

        $subscription_info = get_active_membership_plan();
        $membership_package_meta = isset($subscription_info['subscriptionObj']->ID) ?  get_post_meta($subscription_info['subscriptionObj']->ID): array();

        $total_available_featured_listings = isset($membership_package_meta['hm_settings_featured_listings'][0]) ? $membership_package_meta['hm_settings_featured_listings'][0] : 0;

        $total_used_featured_listings = homey_featured_listing_count($userID);

        if( ! homey_check_membershop_status() ) {
            $ajax_response = array( 'success' => false , 'reason' => esc_html__( "You do not have any package or your package expired.", 'homey' ) );
            echo json_encode( $ajax_response );
            die;
        }

        if( $total_available_featured_listings ==  $total_used_featured_listings ) {
          $ajax_response = array( 'success' => false , 'reason' => esc_html__( 'You have used all your featured listings.', 'homey' ) );
            echo json_encode( $ajax_response );
            die;
        }

        if ( ($post_author == $userID) || homey_is_admin() ) {

            update_post_meta( $listing_id, 'homey_featured', 1 );
            update_post_meta( $listing_id, 'homey_featured_datetime', $date );

            $ajax_response = array( 'success' => true , 'reason' => esc_html__( 'listing Deleted', 'homey' ) );
            echo json_encode( $ajax_response );
            die;
        } else {
            $ajax_response = array( 'success' => false , 'reason' => esc_html__( 'Permission denied', 'homey' ) );
            echo json_encode( $ajax_response );
            die;
        }

    }
}

/* --------------------------------------------------------------------------
* Listing delete ajax
* --------------------------------------------------------------------------- */
add_action( 'wp_ajax_nopriv_homey_delete_listing', 'homey_delete_listing' );
add_action( 'wp_ajax_homey_delete_listing', 'homey_delete_listing' );

if ( !function_exists( 'homey_delete_listing' ) ) {

    function homey_delete_listing()
    {

        $nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $nonce, 'delete_listing_nonce' ) ) {
            $ajax_response = array( 'success' => false , 'reason' => esc_html__( 'Security check failed!', 'homey' ) );
            echo json_encode( $ajax_response );
            die;
        }

        if ( !isset( $_REQUEST['listing_id'] ) ) {
            $ajax_response = array( 'success' => false , 'reason' => esc_html__( 'No listing ID found', 'homey' ) );
            echo json_encode( $ajax_response );
            die;
        }

        $listing_id = $_REQUEST['listing_id'];
        $post_author = get_post_field( 'post_author', $listing_id );

        global $current_user;
        wp_get_current_user();
        $userID      =   $current_user->ID;

        if ( ($post_author == $userID) || homey_is_admin() ) {
            homey_delete_attachments_for_frontend_listing_delete($listing_id);
            wp_delete_post( $listing_id );
            $ajax_response = array( 'success' => true , 'reason' => esc_html__( 'listing Deleted', 'homey' ) );
            echo json_encode( $ajax_response );
            homey_delete_property_attachments_frontend($listing_id);
            die;
        } else {
            $ajax_response = array( 'success' => false , 'reason' => esc_html__( 'Permission denied', 'homey' ) );
            echo json_encode( $ajax_response );
            die;
        }

    }
}

if(!function_exists('homey_delete_attachments_for_frontend_listing_delete')) {
    function homey_delete_attachments_for_frontend_listing_delete($listing_id) {
        $media = get_children(array(
            'post_parent' => $listing_id,
            'post_type' => 'attachment'
        ));

        if (!empty($media)) {
            foreach ($media as $file) {
                // pick what you want to do
                //unlink(get_attached_file($file->ID));
                wp_delete_attachment($file->ID);
            }
        }
        $attachment_ids = get_post_meta($listing_id, 'homey_listing_images', false);

        if (!empty($attachment_ids)) {
            foreach ($attachment_ids as $id) {
                wp_delete_attachment($id);
            }
        }
    }
}

if(!function_exists('homey_get_listing_featured')) {
    function homey_get_listing_featured($listing_id) {
        $homey_local = homey_get_localization();
        $featured = get_post_meta($listing_id, 'homey_featured', true);
        $html_output = '';

        if($featured == 1) {

            if(is_singular('listing')) {
                $html_output = '<span class="label label-success label-featured">'.$homey_local['featured_label'].'</span>';
            } else {
                $html_output = '<span class="label-wrap top-left">
                    <span class="label label-success label-featured">'.$homey_local['featured_label'].'</span>
                </span>';
            }
        }
        return $html_output;
    }
}

if(!function_exists('homey_listing_featured')) {
    function homey_listing_featured($listing_id) {
        echo homey_get_listing_featured($listing_id);
    }
}


if( !function_exists('homey_listing_sort')) {
    function homey_listing_sort($query_args) {
        $sort_by = '';

        if ( isset( $_GET['sortby'] ) ) {
            $sort_by = $_GET['sortby'];
        } else {

            if ( is_page_template( array( 'template/template-listing-list.php', 'template/template-listing-grid.php', 'template/template-listing-card.php', 'template/template-listing-sticky-map.php' ))) {
                $sort_by = get_post_meta( get_the_ID(), 'homey_listings_sort', true );

            } else if ( is_page_template( array( 'template/template-half-map.php' ))) {
                $sort_by = get_post_meta( get_the_ID(), 'homey_listings_halfmap_sort', true );

            } else if( is_page_template( array( 'template/template-search.php' )) ) {

                $sort_by = homey_option('search_default_order');

            } else if ( is_tax() ) {
                $sort_by = homey_option('taxonomy_default_order');
            }
        }

        if ( $sort_by == 'a_price' ) {
            $query_args['orderby'] = 'meta_value_num';
            $query_args['meta_key'] = 'homey_night_price';
            $query_args['meta_key'] = 'homey_night_minimum';
            $query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_price' ) {
            $query_args['orderby'] = 'meta_value_num';
            $query_args['meta_key'] = 'homey_night_price';
            $query_args['meta_key'] = 'homey_night_minimum';
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

        return $query_args;
    }
}

/*-----------------------------------------------------------------------------------*/
/*   Listing gallery images upload
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_homey_listing_gallery_upload', 'homey_listing_gallery_upload' );    // only for logged in user
add_action( 'wp_ajax_nopriv_homey_listing_gallery_upload', 'homey_listing_gallery_upload' );
if( !function_exists( 'homey_listing_gallery_upload' ) ) {
    function homey_listing_gallery_upload( ) {

        // Check security Nonce
        $verify_nonce = $_REQUEST['verify_nonce'];
        if ( ! wp_verify_nonce( $verify_nonce, 'verify_gallery_nonce' ) ) {
            echo json_encode( array( 'success' => false , 'reason' => 'Invalid nonce!' ) );
            die;
        }

        $submitted_file = $_FILES['listing_upload_file'];
        $is_dimension_valid = homey_listing_image_dimension($submitted_file);
        $uploaded_image = wp_handle_upload( $submitted_file, array( 'test_form' => false ) );

        if ( isset( $uploaded_image['file'] ) && $is_dimension_valid != -1 ) {
            $file_name          =   basename( $submitted_file['name'] );
            $file_type          =   wp_check_filetype( $uploaded_image['file'] );

            // Prepare an array of post data for the attachment.
            $attachment_details = array(
                'guid'           => $uploaded_image['url'],
                'post_mime_type' => $file_type['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            $attach_id      =   wp_insert_attachment( $attachment_details, $uploaded_image['file'] );
            $attach_data    =   wp_generate_attachment_metadata( $attach_id, $uploaded_image['file'] );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            $thumbnail_url = wp_get_attachment_image_src( $attach_id, 'thumbnail' );
            $listing_thumb = wp_get_attachment_image_src( $attach_id, 'homey-listing-thumb' );
            $feat_image_url = wp_get_attachment_url( $attach_id );

            $ajax_response = array(
                'success'   => true,
                'url' => $thumbnail_url[0],
                'attachment_id'    => $attach_id,
                'full_image'    => $feat_image_url,
                'thumb'    => $listing_thumb[0],
            );

            echo json_encode( $ajax_response );
            die;

        } else {
            $reason = esc_html__('Image upload failed!','homey');
            if($is_dimension_valid == -1){
               $reason = esc_html__('Image Dimensions Error','homey');
            }

            $ajax_response = array( 'success' => false, 'reason' => $reason );
            echo json_encode( $ajax_response );
            die;
        }

    }
}

/*-----------------------------------------------------------------------------------*/
// Remove listing attachments
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_homey_remove_listing_thumbnail', 'homey_remove_listing_thumbnail' );
add_action( 'wp_ajax_nopriv_homey_remove_listing_thumbnail', 'homey_remove_listing_thumbnail' );
if( !function_exists('homey_remove_listing_thumbnail') ) {
    function homey_remove_listing_thumbnail() {

        $nonce = sanitize_text_field($_POST['removeNonce']);
        $remove_attachment = false;
        if (!wp_verify_nonce($nonce, 'verify_gallery_nonce')) {

            echo json_encode(array(
                'remove_attachment' => false,
                'reason' => esc_html__('Invalid Nonce', 'homey')
            ));
            wp_die();
        }

        if (isset($_POST['thumb_id']) && isset($_POST['listing_id'])) {
            $thumb_id = intval($_POST['thumb_id']);
            $listing_id = intval($_POST['listing_id']);

            if ( $thumb_id > 0 && $listing_id > 0 ) {
                delete_post_meta($listing_id, 'homey_listing_images', $thumb_id);
                $remove_attachment = wp_delete_attachment($thumb_id);
            } elseif ($thumb_id > 0) {
                if( false == wp_delete_attachment( $thumb_id )) {
                    $remove_attachment = false;
                } else {
                    $remove_attachment = true;
                }
            }
        }

        echo json_encode(array(
            'remove_attachment' => $remove_attachment,
        ));
        wp_die();

    }
}

/*-----------------------------------------------------------------------------------*/
// Listing upgrade paypal payment
/*-----------------------------------------------------------------------------------*/
add_action('wp_ajax_homey_listing_paypal_payment', 'homey_listing_paypal_payment');
if( !function_exists('homey_listing_paypal_payment') ) {
    function homey_listing_paypal_payment() {
        global $current_user;
        $listing_id        =   intval($_POST['listing_id']);
        $is_upgrade    =   intval($_POST['is_upgrade']);
        $price_featured_submission = homey_option('price_featured_listing');
        $currency = homey_option('payment_currency');

        $blogInfo = esc_url( home_url('/') );

        wp_get_current_user();
        $userID =   $current_user->ID;
        $post   =   get_post($listing_id);

        if( $post->post_author != $userID ){
            wp_die('Are you kidding?');
        }

        $is_paypal_live             =   homey_option('paypal_api');
        $host                       =   'https://api.sandbox.paypal.com';
        $price_featured_submission  =   floatval( $price_featured_submission );
        $submission_curency         =   esc_html( $currency );
        $payment_description        =   esc_html__('Listing payment on ','homey').$blogInfo;

        if ( $is_upgrade == 1 ) {
            $total_price     =  number_format($price_featured_submission, 2, '.','');
            $payment_description =   esc_html__('Upgrade to featured listing on ','homey').$blogInfo;
        }

        // Check if payal live
        if( $is_paypal_live =='live'){
            $host='https://api.paypal.com';
        }

        $url             =   $host.'/v1/oauth2/token';
        $postArgs        =   'grant_type=client_credentials';

        // Get Access token
        $paypal_token    =   homey_get_paypal_access_token( $url, $postArgs );
        $url             =   $host.'/v1/payments/payment';

        $dashboard     =   homey_get_template_link('template/dashboard.php');
        $cancel_link   =   $dashboard;

        $return_link  = add_query_arg( array(
            'dpage' => 'featured_success',
         ), $dashboard );

        $payment = array(
            'intent' => 'sale',
            "redirect_urls" => array(
                "return_url" => $return_link,
                "cancel_url" => $cancel_link
            ),
            'payer' => array("payment_method" => "paypal"),
        );

        /* Prepare basic payment details
        *--------------------------------------*/
        $payment['transactions'][0] = array(
            'amount' => array(
                'total' => $total_price,
                'currency' => $submission_curency,
                'details' => array(
                    'subtotal' => $total_price,
                    'tax' => '0.00',
                    'shipping' => '0.00'
                )
            ),
            'description' => $payment_description
        );


        /* Prepare individual items
        *--------------------------------------*/
        $payment['transactions'][0]['item_list']['items'][] = array(
            'quantity' => '1',
            'name' => esc_html__('Upgrade to Featured Listing','homey'),
            'price' => $total_price,
            'currency' => $submission_curency,
            'sku' => 'Upgrade Listing',
        );



        /* Convert PHP array into json format
        *--------------------------------------*/
        $jsonEncode = json_encode($payment);
        $json_response = homey_execute_paypal_request( $url, $jsonEncode, $paypal_token );

        //print_r($json_response);
        foreach ($json_response['links'] as $link) {
            if($link['rel'] == 'execute'){
                $payment_execute_url = $link['href'];
            } else  if($link['rel'] == 'approval_url'){
                $payment_approval_url = $link['href'];
            }
        }

        // Save data in database for further use on processor page
        $output['payment_execute_url'] = $payment_execute_url;
        $output['paypal_token']        = $paypal_token;
        $output['listing_id']          = $listing_id;
        $output['is_listing_upgrade']  = $is_upgrade;

        $save_output[$current_user->ID]   =   $output;
        update_option('homey_featured_paypal_transfer',$save_output);

        print ''.$payment_approval_url;

        wp_die();

    }
}

/*-----------------------------------------------------------------------------------*/
// Add to favorite
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_homey_add_to_favorite', 'homey_favorites' );
if( !function_exists( 'homey_favorites' ) ) {
    // a:1:{i:0;i:543;}
    function homey_favorites () {
        global $current_user;
        wp_get_current_user();
        $userID      =   $current_user->ID;
        $fav_option = 'homey_favorites-'.$userID;
        $listing_id = intval( $_POST['listing_id'] );
        $current_prop_fav = get_option( 'homey_favorites-'.$userID );

        $local = homey_get_localization();

        // Check if empty or not
        if( empty( $current_prop_fav ) ) {
            $prop_fav = array();
            $prop_fav['1'] = $listing_id;
            update_option( $fav_option, $prop_fav );
            $arr = array( 'added' => true, 'response' => $local['remove_favorite'] );
            echo json_encode($arr);
            wp_die();
        } else {
            if(  ! in_array ( $listing_id, $current_prop_fav )  ) {
                $current_prop_fav[] = $listing_id;
                update_option( $fav_option,  $current_prop_fav );
                $arr = array( 'added' => true, 'response' => $local['remove_favorite'] );
                echo json_encode($arr);
                wp_die();
            } else {
                $key = array_search( $listing_id, $current_prop_fav );

                if( $key != false ) {
                    unset( $current_prop_fav[$key] );
                }

                update_option( $fav_option, $current_prop_fav );
                $arr = array( 'added' => false, 'response' => $local['add_favorite'] );
                echo json_encode($arr);
                wp_die();
            }
        }
        wp_die();
    }
}

/* --------------------------------------------------------------------------
 * Get invoice post type meta with default values
 ---------------------------------------------------------------------------*/
if ( !function_exists( 'homey_get_invoice_meta' ) ):
    function homey_get_invoice_meta( $post_id, $field = false ) {

        $defaults = array(
            'invoice_billion_for' => '',
            'invoice_billing_type' => '',
            'invoice_item_id' => '',
            'invoice_item_price' => '',
            'invoice_payment_method' => '',
            'invoice_purchase_date' => '',
            'invoice_buyer_id' => ''
        );

        $meta = get_post_meta( $post_id, '_homey_invoice_meta', true );
        $meta = wp_parse_args( (array) $meta, $defaults );

        if ( $field ) {
            if ( isset( $meta[$field] ) ) {
                return $meta[$field];
            } else {
                return false;
            }
        }
        return $meta;
    }
endif;

/*-----------------------------------------------------------------------------------*/
/*  Homey Invoice
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_generate_invoice') ):
    function homey_generate_invoice( $billingFor, $billionType, $list_pack_resv_ID, $invoiceDate, $userID, $featured, $upgrade, $paypalTaxID, $paymentMethod ) {
        $total_price = 0;
        $listing_owner = '';
        $local = homey_get_localization();

        $price_featured_submission = homey_option('price_featured_listing');
        $price_featured_submission = floatval( $price_featured_submission );

        $args = array(
            'post_title'    => 'Invoice ',
            'post_status'   => 'publish',
            'post_type'     => 'homey_invoice'
        );
        $inserted_post_id =  wp_insert_post( $args );

        if( $billionType != 'one_time' ) {
            $billionType = $local['recurring_text'];;
        } else {
            $billionType = $local['one_time_text'];
        }

        // reservation || package || listing || upgrade_featured
        if($billingFor == 'reservation') {
            $total_price = get_post_meta($list_pack_resv_ID, 'reservation_upfront', true);
            $listing_owner = get_post_meta($list_pack_resv_ID, 'listing_owner', true);

        } elseif($billingFor == 'listing') {
            if( $upgrade == 1 ) {
                $total_price = $price_featured_submission;

            }
        } elseif($billingFor == 'upgrade_featured') {
            $total_price = $price_featured_submission;

        } elseif($billingFor == 'package') {

        }


        $fave_meta = array();

        $fave_meta['invoice_billion_for'] = $billingFor;
        $fave_meta['invoice_billing_type'] = $billionType;
        $fave_meta['invoice_item_id'] = $list_pack_resv_ID;
        $fave_meta['invoice_item_price'] = $total_price;
        $fave_meta['invoice_purchase_date'] = $invoiceDate;
        $fave_meta['invoice_buyer_id'] = $userID;
        $fave_meta['invoice_resv_owner'] = $listing_owner;
        $fave_meta['upgrade'] = $upgrade;
        $fave_meta['paypal_txn_id'] = $paypalTaxID;
        $fave_meta['invoice_payment_method'] = $paymentMethod;

        update_post_meta( $inserted_post_id, 'homey_invoice_buyer', $userID );
        update_post_meta( $inserted_post_id, 'invoice_resv_owner', $listing_owner );
        update_post_meta( $inserted_post_id, 'homey_invoice_type', $billionType );
        update_post_meta( $inserted_post_id, 'homey_invoice_for', $billingFor );
        update_post_meta( $inserted_post_id, 'homey_invoice_item_id', $list_pack_resv_ID );
        //check if listing_renter == 0
        $listing_renter = get_post_meta( $list_pack_resv_ID, 'listing_renter', true );

        if($listing_renter < 1){
            update_post_meta( $list_pack_resv_ID, 'listing_renter', $userID);
        }
        //end of check if listing renter == 0
        update_post_meta( $inserted_post_id, 'homey_invoice_item_id', $list_pack_resv_ID );

        update_post_meta( $inserted_post_id, 'homey_invoice_price', $total_price );
        update_post_meta( $inserted_post_id, 'homey_invoice_date', $invoiceDate );
        update_post_meta( $inserted_post_id, 'homey_paypal_txn_id', $paypalTaxID );
        update_post_meta( $inserted_post_id, 'homey_invoice_payment_method', $paymentMethod );

        update_post_meta( $inserted_post_id, '_homey_invoice_meta', $fave_meta );

        // Update post title
        $update_post = array(
            'ID'         => $inserted_post_id,
            'post_title' => 'Invoice '.$inserted_post_id,
        );
        wp_update_post( $update_post );
        return $inserted_post_id;
    }
endif;

/*-----------------------------------------------------------------------------------*/
/*  Homey Invoice Filter
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_homey_invoices_ajax_search', 'homey_invoices_ajax_search' );
add_action( 'wp_ajax_homey_invoices_ajax_search', 'homey_invoices_ajax_search' );

if( !function_exists('homey_invoices_ajax_search') ){
    function homey_invoices_ajax_search() {
        global $current_user, $homey_local;
        wp_get_current_user();
        $userID = $current_user->ID;

        $homey_local = homey_get_localization();

        $meta_query = array();
        $date_query = array();

        if( isset($_POST['invoice_status']) &&  $_POST['invoice_status'] !='' ){
            $temp_array = array();
            $temp_array['key'] = 'invoice_payment_status';
            $temp_array['value'] = sanitize_text_field( $_POST['invoice_status'] );
            $temp_array['compare'] = '=';
            $temp_array['type'] = 'NUMERIC';
            $meta_query[] = $temp_array;
        }

        if( isset($_POST['invoice_type']) &&  $_POST['invoice_type'] !='' ){
            $temp_array = array();
            $temp_array['key'] = 'homey_invoice_for';
            $temp_array['value'] = sanitize_text_field( $_POST['invoice_type'] );
            $temp_array['compare'] = 'LIKE';
            $temp_array['type'] = 'CHAR';
            $meta_query[] = $temp_array;
        }

        if( isset($_POST['startDate']) &&  $_POST['startDate'] !='' ){
            $temp_array = array();
            $temp_array['after'] = sanitize_text_field( $_POST['startDate'] );
            $date_query[] = $temp_array;
        }

        if( isset($_POST['endDate']) &&  $_POST['endDate'] !='' ){
            $temp_array = array();
            $temp_array['before'] = sanitize_text_field( $_POST['endDate'] );
            $date_query[] = $temp_array;
        }

        if(homey_is_renter()) {
            $meta_query[] = array(
                'key' => 'homey_invoice_buyer',
                'value' => $userID,
                'compare' => '='
            );
        } else {

            $_meta_query[] = array(
                'key' => 'homey_invoice_buyer',
                'value' => $userID,
                'compare' => '='
            );
            $_meta_query[] = array(
                'key' => 'invoice_resv_owner',
                'value' => $userID,
                'compare' => '='
            );
            $_meta_query['relation'] = 'OR';

            $meta_query[] = $_meta_query;
        }

        $meta_count = count($meta_query);

        if( $meta_count > 1 ) {
            $meta_query['relation'] = 'AND';
        }


        $invoices_args = array(
            'post_type' => 'homey_invoice',
            'posts_per_page' => -1,
            'meta_query' => $meta_query,
            'date_query' => $date_query,
            'order' => 'ID ASC',

        );

        add_filter( 'posts_orderby', 'filter_post_sort_query' );
        $invoices = new WP_Query( $invoices_args );
        remove_filter( 'posts_orderby', 'filter_post_sort_query' );

        $total_price = 0;

        ob_start();

        while ( $invoices->have_posts()): $invoices->the_post();
            $fave_meta = homey_get_invoice_meta( get_the_ID() );
            get_template_part('template-parts/dashboard/invoices/item');

            $total_price += $fave_meta['invoice_item_price'];
        endwhile;

        $result = ob_get_contents();
        ob_end_clean();

        echo json_encode( array( 'success' => true, 'result' => $result, 'total_price' => '' ) );
        wp_die();
    }
}

if(!function_exists('filter_post_sort_query')) {
    function filter_post_sort_query( $query ) {
        global $wpdb;
        $query = " $wpdb->posts.ID DESC";
        $query = " $wpdb->posts.ID DESC";
        return $query;
    }
}

/*-----------------------------------------------------------------------------------*/
/*  Save listing custom periods
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_homey_add_custom_period', 'homey_add_custom_period' );
if(!function_exists('homey_add_custom_period')) {
    function homey_add_custom_period() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;
        $local = homey_get_localization();
        $allowded_html = array();
        $period_meta = array();
        $listing_id     = intval(isset($_POST['listing_id']) ? $_POST['listing_id'] : 0);
        $start_date     =  wp_kses ( $_POST['start_date'], $allowded_html );
        $end_date       =  wp_kses ( $_POST['end_date'], $allowded_html );
        $night_price  =  floatval ( isset($_POST['night_price']) ? $_POST['night_price'] : 0 );
        $guest_price  =  floatval ( isset($_POST['additional_guest_price']) ? $_POST['additional_guest_price'] : 0 );
        $weekend_price  =  floatval ( isset($_POST['weekend_price']) ? $_POST['weekend_price'] : 0 );
        $night_minimum  =  intval ( isset($_POST['night_minimum']) ? $_POST['night_minimum'] : 0 );
        $period_name     =  $_POST['period_name'];
        $the_post= get_post( $listing_id);
        $period_meta['night_price'] = $night_price;
        $period_meta['weekend_price'] = $weekend_price;
        $period_meta['night_minimum'] = $night_minimum;
        $period_meta['period_name'] = $period_name;
        $period_meta['guest_price'] = $guest_price;
        $current_period_meta_array = get_post_meta($listing_id, 'homey_custom_period', true);
        if(empty($current_period_meta_array)) {
            $current_period_meta_array = array();
        }
        if ( !is_user_logged_in() ) {
            echo json_encode(array(
                'success' => false,
                'message' => $local['kidding_text']
            ));
            wp_die();
        }
        if($userID === 0 ) {
            echo json_encode(array(
                'success' => false,
                'message' => $local['kidding_text']
            ));
            wp_die();
        }

        $start_date     =  date('d-m-Y', custom_strtotime($start_date));
        $end_date       =  date('d-m-Y', custom_strtotime($end_date));

        $start_date      =   new DateTime($start_date);
        $start_date_unix =   $start_date->getTimestamp();

        $end_date        =   new DateTime($end_date);
        $end_date_unix   =   $end_date->getTimestamp();

        $current_period_meta_array[$start_date_unix] = $period_meta;

        $start_date->modify('tomorrow');
        $start_date_unix =   $start_date->getTimestamp();

        while ($start_date_unix <= $end_date_unix) {
            $current_period_meta_array[$start_date_unix] = $period_meta;
            //print 'memx '.memory_get_usage ().' </br>/';
            $start_date->modify('tomorrow');
            $start_date_unix =   $start_date->getTimestamp();
        }

        update_post_meta($listing_id, 'homey_custom_period', $current_period_meta_array );
        echo json_encode(array(
            'success' => true,
            'message' => 'success'
        ));
        wp_die();
    }
}


if(!function_exists('homey_add_custom_period_old')) {
    function homey_add_custom_period_old() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;

        $local = homey_get_localization();
        $allowded_html = array();
        $period_meta = array();


        $listing_id     = intval($_POST['listing_id']);
        $start_date     =  wp_kses ( $_POST['start_date'], $allowded_html );
        $end_date       =  wp_kses ( $_POST['end_date'], $allowded_html );
        $night_price    =  floatval ( $_POST['night_price']);
        $guest_price    =  floatval ( $_POST['additional_guest_price'] );
        $weekend_price  =  floatval ( $_POST['weekend_price'] );
        $night_minimum  =  intval ( isset($_POST['night_minimum']) ? $_POST['night_minimum'] : 0 );
        $period_name  =  $_POST['period_name'];
        $the_post= get_post( $listing_id);

        $period_meta['night_price'] = $night_price;
        $period_meta['weekend_price'] = $weekend_price;
        $period_meta['night_minimum'] = $night_minimum;
        $period_meta['period_name'] = $period_name;
        $period_meta['guest_price'] = $guest_price;

        $current_period_meta_array = get_post_meta($listing_id, 'homey_custom_period', true);

        if(empty($current_period_meta_array)) {
            $current_period_meta_array = array();
        }

        if ( !is_user_logged_in() ) {
            echo json_encode(array(
                'success' => false,
                'message' => $local['kidding_text']
            ));
            wp_die();
        }

        if($userID === 0 ) {
            echo json_encode(array(
                'success' => false,
                'message' => $local['kidding_text']
            ));
            wp_die();
        }


        $start_date      =   new DateTime($start_date);
        $start_date_unix =   $start_date->getTimestamp();

        $end_date        =   new DateTime($end_date);
        $end_date_unix   =   $end_date->getTimestamp();

        $current_period_meta_array[$start_date_unix] = $period_meta;

        $start_date->modify('tomorrow');
        $start_date_unix =   $start_date->getTimestamp();

        while ($start_date_unix <= $end_date_unix) {

            $current_period_meta_array[$start_date_unix] = $period_meta;
            //print 'memx '.memory_get_usage ().' </br>/';
            $start_date->modify('tomorrow');
            $start_date_unix =   $start_date->getTimestamp();
        }

        update_post_meta($listing_id, 'homey_custom_period', $current_period_meta_array );
        echo json_encode(array(
            'success' => true,
            'message' => 'success'
        ));
        wp_die();
    }
}

/*-----------------------------------------------------------------------------------*/
/*  Delete listing custom periods
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_homey_delete_custom_period', 'homey_delete_custom_period' );
if(!function_exists('homey_delete_custom_period')) {
    function homey_delete_custom_period() {
        global $current_user;
        $current_user = wp_get_current_user();
        $userID       = $current_user->ID;

        $local = homey_get_localization();
        $allowded_html = array();
        $period_meta = array();


        $listing_id     = intval($_POST['listing_id']);
        $start_date     =  wp_kses ( $_POST['start_date'], $allowded_html );
        $end_date       =  wp_kses ( $_POST['end_date'], $allowded_html );
        $the_post= get_post( $listing_id);

        $current_period_meta_array = get_post_meta($listing_id, 'homey_custom_period', true);


        if( !is_array($current_period_meta_array)) {
            $current_period_meta_array = array();
        }

        if ( !is_user_logged_in() ) {
            echo json_encode(array(
                'success' => false,
                'message' => $local['kidding_text']
            ));
            wp_die();
        }

        if($userID === 0 ) {
            echo json_encode(array(
                'success' => false,
                'message' => $local['kidding_text']
            ));
            wp_die();
        }



        $start_date      =   new DateTime("@".$start_date);
        $start_date_unix =   $start_date->getTimestamp();
        $end_date        =   new DateTime("@".$end_date);
        $end_date_unix   =   $end_date->getTimestamp();

        unset($current_period_meta_array[$start_date_unix]);

        $start_date->modify('tomorrow');
        $start_date_unix =   $start_date->getTimestamp();

        while ($start_date_unix <= $end_date_unix) {

            if($current_period_meta_array[$start_date_unix]){
                unset($current_period_meta_array[$start_date_unix]);
            }

            $start_date->modify('tomorrow');
            $start_date_unix =   $start_date->getTimestamp();
        }

        update_post_meta($listing_id, 'homey_custom_period', $current_period_meta_array );
        echo json_encode(array(
            'success' => true,
            'message' => 'success'
        ));
        wp_die();
    }
}

/*-----------------------------------------------------------------------------------*/
/*  Get Custom periods
/*-----------------------------------------------------------------------------------*/
if(!function_exists('homey_get_custom_period')) {
    function homey_get_custom_period($listing_id, $actions = true ) {
        if(empty($listing_id)) {
            return;
        }

        $homey_date_format = homey_option('homey_date_format');

        if($homey_date_format == 'yy-mm-dd') {
            $h_date_format = 'Y-m-d';

        } elseif($homey_date_format == 'yy-dd-mm') {
            $h_date_format = 'Y-d-m';

        } elseif($homey_date_format == 'mm-yy-dd') {
            $h_date_format = 'm-Y-d';

        } elseif($homey_date_format == 'dd-yy-mm') {
            $h_date_format = 'd-Y-m';

        } elseif($homey_date_format == 'mm-dd-yy') {
            $h_date_format = 'm-d-Y';

        } elseif($homey_date_format == 'dd-mm-yy') {
            $h_date_format = 'd-m-Y';

        }elseif($homey_date_format == 'dd.mm.yy') {
            $h_date_format = 'd.m.Y';

        } else {
            $h_date_format = 'Y-m-d';
        }

        $output = '';
        $i = 0;
        $night_price = '';
        $weekend_price = '';
        $night_minimum = '';
        $period_name = '';
        $guest_price = '';

        $local = homey_get_localization();

        $hide_fields = homey_option('add_hide_fields');
        $custom_weekend_price = isset($hide_fields['custom_weekend_price']) ? $hide_fields['custom_weekend_price'] : 0;

        $period_array = get_post_meta($listing_id, 'homey_custom_period', true);

        if(empty($period_array)) {
            return;
        }

        if(is_array($period_array)) {
            ksort($period_array);
        }

        foreach ($period_array as $timestamp => $data) {

            $is_consecutive_day = 0;
            $from_date          = new DateTime("@".$timestamp);
            $to_date            = new DateTime("@".$timestamp);
            $tomorrrow_date     = new DateTime("@".$timestamp);

            $tomorrrow_date->modify('tomorrow');
            $tomorrrow_date = $tomorrrow_date->getTimestamp();


            if ( $i == 0 ) {
                $i = 1;


                $night_price   = $data['night_price'];
                $weekend_price = $data['weekend_price'];
                $night_minimum = $data['night_minimum'];
                $period_name = $data['period_name'];
                $guest_price   = $data['guest_price'];


                $from_date_unix = $from_date->getTimestamp();

                echo '<tr>';

                echo '<td data-label="'.esc_attr($local['period_name_label']).'">
                <strong>'.$period_name.'</strong>
            </td>';

                echo '<td dfgdgd data-label="'.esc_attr($local['start_date']).'">
                    '.$from_date->format($h_date_format).'
                </td>';
            }

            if ( !array_key_exists ($tomorrrow_date, $period_array) ) {
                $is_consecutive_day = 1;

            } else {

                if( $period_array[$tomorrrow_date]['night_price']   !=  $night_price ||
                    $period_array[$tomorrrow_date]['weekend_price'] !=  $weekend_price ||
                    $period_array[$tomorrrow_date]['guest_price']   !=  $guest_price ) {
                        $is_consecutive_day = 1;
                }
            }

            if( $is_consecutive_day == 1 ) {

                if( $i == 1 ) {

                    $to_date_unix = $from_date->getTimestamp();
                    echo '<td data-label="'.esc_attr($local['end_date']).'">
                        '.$from_date->format($h_date_format).'
                    </td>';


                    echo '<td data-label="'.esc_attr($local['nightly_label']).'">
                        <strong>'.homey_formatted_price($night_price, false).'</strong>
                    </td>';

                    if($custom_weekend_price != 1) {
                        echo '<td data-label="'.esc_attr($local['weekends_label']).'">
                            <strong>'.homey_formatted_price($weekend_price, false).'</strong>
                        </td>';
                    }


                    echo '<td data-label="'.esc_attr($local['night_minimum_label']).'">
                        <strong>'.$night_minimum.'</strong>
                    </td>';


                    echo '<td data-label="'.esc_attr($local['addinal_guests_label']).'">
                        <strong>'.homey_formatted_price($guest_price, false).'</strong>
                    </td>';

                    // Commenting duplicated column value:
                    // $booking_hide_fields = homey_option('booking_hide_fields');
                    // if ( $booking_hide_fields['guests'] != 1 ) {
                    //     echo '<td data-label="' . esc_attr($local['addinal_guests_label']) . '">
                    //     <strong>' . homey_formatted_price($guest_price, false) . '</strong>
                    // </td>';
                    // }

                    if($actions) {
                    echo '
                    <td data-label="'.esc_html__('Actions', 'homey').'">
                    <div class="custom-actions">
                        <button class="homey_delete_period btn btn-primary" data-listingid="'.$listing_id.'" data-startdate="'.$from_date_unix.'" data-enddate="'.$to_date_unix.'">'.$local['delete_btn'].'</button>
                    </div>
                    </td>';
                    }

                    echo '</tr>';
                }
                $i = 0;
                $night_price   = $data['night_price'];
                $weekend_price = $data['weekend_price'];
                $night_minimum = $data['night_minimum'];
                $period_name = $data['period_name'];
                $guest_price   = $data['guest_price'];


            }

        } // End foreach

    }
}

/*-----------------------------------------------------------------------------------*/
/*  Homey Invoice Print listing
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_homey_create_invoice_print', 'homey_create_invoice_print' );
add_action( 'wp_ajax_homey_create_invoice_print', 'homey_create_invoice_print' );

if ( !function_exists( 'homey_create_invoice_print' ) ) {
    function homey_create_invoice_print() {

        if(!isset($_POST['invoice_id'])|| !is_numeric($_POST['invoice_id'])){
            exit();
        }

        $homey_local = homey_get_localization();
        $invoice_id = intval($_POST['invoice_id']);
        $the_post= get_post( $invoice_id );

        if( $the_post->post_type != 'homey_invoice' || $the_post->post_status != 'publish' ) {
            exit();
        }

        print  '<html><head><link href="'.get_stylesheet_uri().'" rel="stylesheet" type="text/css" />';
        print  '<link href="'.get_template_directory_uri().'/css/bootstrap.min.css" rel="stylesheet" type="text/css" />';
        print  '<link href="'.get_template_directory_uri().'/css/main.css" rel="stylesheet" type="text/css" />';

        if( is_rtl() ) {
            print '<link href="'.get_template_directory_uri().'/css/rtl.css" rel="stylesheet" type="text/css" />';
            print '<link href="'.get_template_directory_uri().'/css/bootstrap-rtl.min.css" rel="stylesheet" type="text/css" />';
        }
        print '</head>';
        print  '<body>';

        global $homey_local, $dashboard_invoices, $current_user;
        wp_get_current_user();
        /*$userID         = $current_user->ID;
        $user_login     = $current_user->user_login;
        $user_email     = $current_user->user_email;
        $first_name     = $current_user->first_name;
        $last_name     = $current_user->last_name;*/

        $homey_invoice_buyer = get_post_meta( $invoice_id, 'homey_invoice_buyer', true );
        $user_info = get_userdata($homey_invoice_buyer);
        $user_email     = $user_info->user_email;
        $first_name     = $user_info->first_name;
        $last_name     = $user_info->last_name;
        $user_login     = $user_info->user_login;

        $user_address = get_user_meta( $userID, 'homey_street_address', true);
        if( !empty($first_name) && !empty($last_name) ) {
            $fullname = $first_name.' '.$last_name;
        } else {
            $fullname = $user_info->display_name;
        }
        $post = get_post( $invoice_id );
        $invoice_data = homey_get_invoice_meta( $invoice_id );
        $invoice_item_id = $invoice_data['invoice_item_id'];

        $publish_date = $post->post_date;
        $publish_date = date_i18n( get_option('date_format'), strtotime( $publish_date ) );
        $invoice_logo = homey_option( 'invoice_logo', false, 'url' );
        $invoice_company_name = homey_option( 'invoice_company_name' );
        $invoice_address = homey_option( 'invoice_address' );
        $invoice_additional_info = homey_option( 'invoice_additional_info' );

        $is_reservation_invoice = false;
        if($invoice_data['invoice_billion_for'] == 'reservation') {
            $is_reservation_invoice = true;
        }

        if($invoice_data['invoice_billion_for'] == 'reservation') {

            $billing_for_text = $homey_local['resv_fee_text'];

        } elseif($invoice_data['invoice_billion_for'] == 'listing') {
            if( $invoice_data['upgrade'] == 1 ) {
                $billing_for_text =  $homey_local['upgrade_text'];

            } else {
                $billing_for_text =  get_the_title( get_post_meta( get_the_ID(), 'homey_invoice_item_id', true) );
            }
        } elseif($invoice_data['invoice_billion_for'] == 'package') {
            $billing_for_text =  $homey_local['inv_package'];
        }
        ?>
        <div class="invoice-detail block">
            <div class="invoice-header clearfix">
                <div class="block-left">
                    <div class="invoice-logo">
                        <?php if( !empty($invoice_logo) ) { ?>
                            <img src="<?php echo esc_url($invoice_logo); ?>" alt="<?php esc_attr_e('logo', 'homey');?>">
                        <?php } ?>
                    </div>
                    <ul class="list-unstyled">
                        <?php if( !empty($invoice_company_name) ) { ?>
                            <li><strong><?php echo esc_attr($invoice_company_name); ?></strong></li>
                        <?php } ?>
                        <li><?php echo homey_option( 'invoice_address' ); ?></li>
                    </ul>
                </div>
                <div class="block-right">
                    <ul class="list-unstyled">
                        <li><strong><?php esc_html_e('Invoice:', 'homey'); ?></strong> <?php echo esc_attr($invoice_id); ?></li>
                        <li><strong><?php esc_html_e('Date:', 'homey'); ?></strong> <?php echo esc_attr($publish_date); ?></li>
                        <?php if($is_reservation_invoice) { ?>
                            <li><strong><?php esc_html_e('Reservation ID:', 'homey'); ?></strong> <?php echo esc_attr($invoice_item_id); ?></li>
                        <?php } ?>
                    </ul>
                </div>
            </div><!-- invoice-header -->

            <div class="invoice-body clearfix">
                <ul class="list-unstyled">
                    <li><strong><?php echo esc_html__('To:', 'homey'); ?></strong></li>
                    <li><?php echo esc_attr($fullname); ?></li>
                    <li><?php echo esc_html__('Email:', 'homey'); ?> <?php echo esc_attr($user_email);?></li>
                </ul>
                <h2 class="title"><?php esc_html_e('Details', 'homey'); ?></h2>

                <?php
                if($is_reservation_invoice) {
                    $resv_id = $invoice_item_id;
                    $is_hourly = get_post_meta($resv_id, 'is_hourly', true);
                    if($is_hourly == 'yes') {
                        echo homey_calculate_hourly_reservation_cost($resv_id);
                    } else {
                        echo homey_calculate_reservation_cost($resv_id);
                    }
                } else {
                    echo '<div class="payment-list"><ul>';
                        echo '<li>'.esc_attr($homey_local['billing_for']).' <span>'.esc_attr($billing_for_text).'</span></li>';
                        echo '<li>'.esc_attr($homey_local['billing_type']).' <span>'.esc_html( $invoice_data['invoice_billing_type'] ).'</span></li>';
                        echo '<li>'.esc_attr($homey_local['inv_pay_method']).' <span>'.esc_html($invoice_data['invoice_payment_method']).'</span></li>';
                        echo '<li class="payment-due">'.esc_attr($homey_local['inv_total']).' <span>'.homey_formatted_price( $invoice_data['invoice_item_price'] ).'</span></li>';
                        echo '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$invoice_data['invoice_item_price'].'">';
                    echo '</ul></div>';
                }
                ?>

            </div><!-- invoice-body -->

            <?php if( !empty($invoice_additional_info)) { ?>
            <div class="invoice-footer clearfix">
                <dl>
                    <dt><?php echo esc_html__('Additional Information:', 'homey'); ?></dt>
                    <dd><?php echo homey_option( 'invoice_additional_info' ); ?></dd>
                </dl>
            </div><!-- invoice-footer -->
            <?php } ?>

        </div>
        <?php

        print '</body></html>';
        wp_die();
    }
}

/*-----------------------------------------------------------------------------------*/
/*  Homey Print Property
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_homey_create_print', 'homey_create_print' );
add_action( 'wp_ajax_homey_create_print', 'homey_create_print' );

if( !function_exists('homey_create_print')) {
    function homey_create_print () {
        global $homey_prefix;
        $homey_prefix = 'homey_';
        if(!isset($_POST['listing_id'])|| !is_numeric($_POST['listing_id'])){
            exit();
        }

        $listing_id = intval($_POST['listing_id']);
        $the_post= get_post( $listing_id );

        if( $the_post->post_type != 'listing' || $the_post->post_status != 'publish' ) {
            exit();
        }

        print  '<html><head><link href="'.get_stylesheet_uri().'" rel="stylesheet" type="text/css" />';
        print  '<html><head><link href="'.get_template_directory_uri().'/css/bootstrap.css" rel="stylesheet" type="text/css" />';
        print  '<html><head><link href="'.get_template_directory_uri().'/css/font-awesome-min.css" rel="stylesheet" type="text/css" />';
        print  '<html><head><link href="'.get_template_directory_uri().'/css/main.css" rel="stylesheet" type="text/css" />';
        print  '<html><head><link href="'.get_template_directory_uri().'/css/styling-options.css" rel="stylesheet" type="text/css" />';

        if( is_rtl() ) {
            print '<link href="'.get_template_directory_uri().'/css/rtl.css" rel="stylesheet" type="text/css" />';
            print '<link href="'.get_template_directory_uri().'/css/bootstrap-rtl.min.css" rel="stylesheet" type="text/css" />';
        }
        print '</head>';
        print  '<body class="print-page">';

        $homey_local = homey_get_localization();
        $print_logo = homey_option( 'print_page_logo', false, 'url' );

        $image_id           = get_post_thumbnail_id( $listing_id );
        $full_img           = wp_get_attachment_image_src($image_id, 'homey-gallery');
        $full_img           = $full_img [0];

        $title              = get_the_title( $listing_id );
        $prop_excerpt       = $the_post->post_content;
        $prop_excerpt       = apply_filters('the_content', $prop_excerpt);
        $author_id          = $the_post->post_author;

        $rating = homey_option('rating');
        $total_rating = get_post_meta( $listing_id, 'listing_total_rating', true );

        $address = homey_get_listing_data_by_id('listing_address', $listing_id);
        $night_price = homey_get_listing_data_by_id('night_price', $listing_id);
        $hour_price = homey_get_listing_data_by_id('hour_price', $listing_id);

        $listing_author = homey_get_author_by_id('70', '70', 'img-circle media-object avatar', $author_id);
        $reviews = homey_get_host_reviews($author_id);
        $booking_type = homey_booking_type_by_id($listing_id);

        $doc_verified = $listing_author['doc_verified'];
        $verified = false;
        if($doc_verified) {
            $verified = true;
        }

        $guests     = homey_get_listing_data_by_id('guests', $listing_id);
        $bedrooms   = homey_get_listing_data_by_id('listing_bedrooms', $listing_id);
        $beds       = homey_get_listing_data_by_id('beds', $listing_id);
        $baths      = homey_get_listing_data_by_id('baths', $listing_id);
        $size       = homey_get_listing_data_by_id('listing_size', $listing_id);
        $size_unit       = homey_get_listing_data_by_id('listing_size_unit', $listing_id);
        $checkin_after   = homey_get_listing_data_by_id('checkin_after', $listing_id);
        $checkout_before = homey_get_listing_data_by_id('checkout_before', $listing_id);
        $room_type       = homey_taxonomy_simple_by_ID('room_type', $listing_id);
        $listing_type    = homey_taxonomy_simple_by_ID('listing_type', $listing_id);

        if($booking_type == 'per_hour') {
            $weekends_price = homey_get_listing_data_by_id('hourly_weekends_price', $listing_id);
        } else {
            $weekends_price = homey_get_listing_data_by_id('weekends_price', $listing_id);
        }


        $weekends_days = homey_get_listing_data_by_id('weekends_days', $listing_id);
        $priceWeekly = homey_get_listing_data_by_id('priceWeek', $listing_id);
        $priceMonthly = homey_get_listing_data_by_id('priceMonthly', $listing_id);
        $min_stay_days = homey_get_listing_data_by_id('min_book_days', $listing_id);
        $max_stay_days = homey_get_listing_data_by_id('max_book_days', $listing_id);
        $min_stay_hours = homey_get_listing_data_by_id('min_book_hours', $listing_id);
        $security_deposit = homey_get_listing_data_by_id('security_deposit', $listing_id);
        $cleaning_fee = homey_get_listing_data_by_id('cleaning_fee', $listing_id);
        $cleaning_fee_type = homey_get_listing_data_by_id('cleaning_fee_type', $listing_id);
        $city_fee = homey_get_listing_data_by_id('city_fee', $listing_id);
        $city_fee_type = homey_get_listing_data_by_id('city_fee_type', $listing_id);
        $additional_guests_price = homey_get_listing_data_by_id('additional_guests_price', $listing_id);
        $allow_additional_guests = homey_get_listing_data_by_id('allow_additional_guests', $listing_id);

        $smoke            = homey_get_listing_data_by_id('smoke', $listing_id);
        $pets             = homey_get_listing_data_by_id('pets', $listing_id);
        $party            = homey_get_listing_data_by_id('party', $listing_id);
        $children         = homey_get_listing_data_by_id('children', $listing_id);
        $additional_rules = homey_get_listing_data_by_id('additional_rules', $listing_id);

        $min_book_days  = homey_get_listing_data_by_id('min_book_days', $listing_id);
        $max_book_days  = homey_get_listing_data_by_id('max_book_days', $listing_id);

        $sn_text_yes = esc_html__(homey_option('sn_text_yes'), 'homey');
        $sn_text_no = esc_html__(homey_option('sn_text_no'), 'homey');

        if($smoke != 1) {
            $smoke_allow = 'fa fa-times';
            $smoke_text = $sn_text_no;
        } else {
            $smoke_allow = 'fa fa-check';
            $smoke_text = $sn_text_yes;
        }

        if($pets != 1) {
            $pets_allow = 'fa fa-times';
            $pets_text = $sn_text_no;
        } else {
            $pets_allow = 'fa fa-check';
            $pets_text = $sn_text_yes;
        }

        if($party != 1) {
            $party_allow = 'fa fa-times';
            $party_text = $sn_text_no;
        } else {
            $party_allow = 'fa fa-check';
            $party_text = $sn_text_yes;
        }

        if($children != 1) {
            $children_allow = 'fa fa-times';
            $children_text = $sn_text_no;
        } else {
            $children_allow = 'fa fa-check';
            $children_text = $sn_text_yes;
        }

        $hide_labels = homey_option('show_hide_labels');

        $cleaning_fee_period = $city_fee_period = '';

        if($cleaning_fee_type == 'per_stay') {
            $cleaning_fee_period = esc_html__('Per Stay', 'homey');
        } elseif($cleaning_fee_type == 'daily') {

            if($booking_type == 'per_hour') {
                $cleaning_fee_period = esc_html__('Hourly', 'homey');
            } else {
                $cleaning_fee_period = esc_html__('Daily', 'homey');
            }

        }

        if($city_fee_type == 'per_stay') {
            $city_fee_period = esc_html__('Per Stay', 'homey');
        } elseif($city_fee_type == 'daily') {

            if($booking_type == 'per_hour') {
                $city_fee_period = esc_html__('Hourly', 'homey');
            } else {
                $city_fee_period = esc_html__('Daily', 'homey');
            }
        }

        if($weekends_days == 'sat_sun') {
            $weekendDays = esc_html__('Sat & Sun', 'homey');

        } elseif($weekends_days == 'fri_sat') {
            $weekendDays = esc_html__('Fri & Sat', 'homey');

        } elseif($weekends_days == 'fri_sat_sun') {
            $weekendDays = esc_html__('Fri, Sat & Sun', 'homey');
        }

        $hide_labels = homey_option('show_hide_labels');

        $slash = '';
        if(!empty($room_type) && !empty($listing_type)) {
            $slash = '/';
        }
        ?>

        <div class="print-main-wrap">
            <div class="print-wrap">

                <div class="print-header">
                    <h1><img src="<?php echo esc_url($print_logo); ?>" width="128" height="30" alt="<?php bloginfo( 'name' ); ?>"></h1>
                    <?php if(homey_option('print_tagline')) { ?>
                    <span class="tag-line"><?php bloginfo( 'description' ); ?></span>
                    <?php } ?>
                </div>
                <div class="top-section">
                    <div class="block">
                        <div class="block-head">
                            <div class="block-left">
                                <h2 class="title"><?php echo esc_attr($title); ?></h2>
                                <?php if(!empty($address)) { ?>
                                    <address><?php echo esc_attr($address); ?></address>
                                <?php } ?>

                                <?php if($rating && ($total_rating != '' && $total_rating != 0 ) && homey_option('print_rating')) { ?>
                                <div class="rating">
                                    <?php echo homey_get_review_stars($total_rating, true, true); ?>
                                </div>
                                <?php } ?>
                            </div><!-- block-left -->
                            <div class="block-right">
                                <span class="item-price">
                                    <?php if($booking_type == 'per_hour') { ?>
                                        <?php echo homey_formatted_price($hour_price, false, true); ?><sub>/<?php echo homey_option('glc_hour_label');?></sub>
                                    <?php } else { ?>
                                        <?php echo homey_formatted_price($night_price, false, true); ?><sub>/<?php echo homey_option('glc_day_night_label');?></sub>
                                    <?php } ?>
                                </span>
                            </div><!-- block-right -->
                        </div><!-- block-head -->

                        <?php if( !empty($full_img) ) { ?>
                            <img class="img-responsive" src="<?php echo esc_url( $full_img ); ?>" alt="<?php echo esc_attr($title); ?>">

                            <?php if(homey_option('print_qr_code')) {?>
                            <img class="qr-code img-responsive" src="https://chart.googleapis.com/chart?chs=105x104&cht=qr&chl=<?php echo esc_url( get_permalink($listing_id) ); ?>&choe=UTF-8" title="<?php echo esc_attr($title); ?>" />
                            <?php } ?>
                        <?php } ?>

                    </div><!-- block -->
                </div>

                <?php if(homey_option('print_host')) { ?>
                <div class="host-section">
                    <div class="block">
                        <div class="block-head">
                            <div class="media">
                                <div class="media-left">
                                    <?php echo ''.$listing_author['photo']; ?>
                                </div>
                                <div class="media-body">
                                    <h2 class="title"><?php echo homey_option('sn_hosted_by'); ?> <span><?php echo esc_attr($listing_author['name']); ?></span></h2>

                                    <?php if(!empty($listing_author['city'])) { ?>
                                    <address><i class="fa fa-map-marker" aria-hidden="true"></i> <?php echo esc_attr($listing_author['city']); ?></address>
                                    <?php } ?>

                                    <div class="block-body">
                                        <div class="row">
                                            <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                                                <dl>
                                                    <dt><?php echo homey_option('sn_pr_lang'); ?></dt>
                                                    <dd><?php echo esc_attr($listing_author['languages']); ?></dd>
                                                </dl>
                                            </div>
                                            <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">


                                                <dl>

                                                    <dt><?php echo homey_option('sn_pr_profile_status'); ?> </dt>


                                                    <?php
                                                    $current_page_user = homey_user_role_by_user_id($author_id);

                                                    if($current_page_user == 'administrator') { ?>

                                                        <dd class="text-success">
                                                            <i class="fa fa-check-circle-o"></i>
                                                            <?php echo homey_option('sn_pr_verified'); ?>
                                                        </dd>

                                                    <?php
                                                    } else {
                                                    if($verified) { ?>
                                                        <dd class="text-success"><i class="fa fa-check-circle-o"></i> <?php esc_html_e('Verified', 'homey'); ?></dd>
                                                        <?php } else { ?>
                                                            <dd class="text-danger"><i class="fa fa-close"></i> <?php esc_html_e('Not Verified', 'homey'); ?></dd>
                                                        <?php }
                                                    }?>
                                                </dl>

                                            </div>

                                            <?php if($reviews['is_host_have_reviews']) { ?>
                                            <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                                                <dl>
                                                    <dt><?php echo homey_option('sn_pr_h_rating'); ?></dt>
                                                    <dd>
                                                        <div class="rating">
                                                            <?php echo ''.$reviews['host_rating']; ?>
                                                        </div>
                                                    </dd>
                                                </dl>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    </div><!-- block-body -->
                                </div>
                            </div>
                        </div><!-- block-head -->

                    </div><!-- block -->
                </div><!-- host-section -->
                <?php } ?>

                <?php if(homey_option('print_description')) { ?>
                <div id="about-section" class="about-section">
                    <div class="block">
                        <div class="block-body">
                            <h2><?php echo homey_option('sn_about_listing_title'); ?></h2>
                            <?php echo ''.$prop_excerpt; ?>
                        </div>
                    </div><!-- block-body -->
                </div>
                <?php } ?>


                <?php if(homey_option('print_details')) { ?>
                <div id="details-section" class="details-section">
                    <div class="block">
                        <div class="block-section">
                            <div class="block-body">
                                <div class="block-left">
                                    <h3 class="title"><?php echo homey_option('sn_detail_heading'); ?></h3>
                                </div><!-- block-left -->
                                <div class="block-right">
                                    <ul class="detail-list detail-list-2-cols">
                                        <?php if($hide_labels['sn_id_label'] != 1) { ?>
                                        <li>
                                            <i class="fa fa-angle-right" aria-hidden="true"></i>
                                            <?php echo esc_attr(homey_option('sn_id_label')); ?>: <strong><?php echo esc_attr($listing_id); ?></strong>
                                        </li>
                                        <?php } ?>

                                        <?php if(!empty($guests) && $hide_labels['sn_guests_label'] != 1) { ?>
                                        <li>
                                            <i class="fa fa-angle-right" aria-hidden="true"></i>
                                            <?php echo homey_option('sn_guests_label'); ?>: <strong><?php echo esc_attr($guests); ?></strong>
                                        </li>
                                        <?php } ?>

                                        <?php if(!empty($bedrooms) && $hide_labels['sn_bedrooms_label'] != 1) { ?>
                                        <li>
                                            <i class="fa fa-angle-right" aria-hidden="true"></i>
                                            <?php echo homey_option('sn_bedrooms_label'); ?>: <strong><?php echo esc_attr($bedrooms); ?></strong>
                                        </li>
                                        <?php } ?>

                                        <?php if(!empty($beds) && $hide_labels['sn_beds_label'] != 1) { ?>
                                        <li><i class="fa fa-angle-right" aria-hidden="true"></i>
                                            <?php echo homey_option('sn_beds_label'); ?>: <strong><?php echo esc_attr($beds); ?></strong>
                                        </li>
                                        <?php } ?>

                                        <?php if(!empty($baths) && $hide_labels['sn_bathrooms_label'] != 1) { ?>
                                        <li><i class="fa fa-angle-right" aria-hidden="true"></i>
                                            <?php echo homey_option('sn_bathrooms_label'); ?>: <strong><?php echo esc_attr($baths); ?></strong>
                                        </li>
                                        <?php } ?>

                                        <?php if($booking_type == 'per_day' || $booking_type == 'per_day_date') { ?>
                                            <?php if(!empty($checkin_after) && $hide_labels['sn_check_in_after'] != 1) { ?>
                                            <li><i class="fa fa-angle-right" aria-hidden="true"></i>
                                                <?php echo homey_option('sn_check_in_after'); ?>: <strong><?php echo esc_attr($checkin_after); ?></strong>
                                            </li>
                                            <?php } ?>

                                            <?php if(!empty($checkout_before) && $hide_labels['sn_check_out_before'] != 1) { ?>
                                            <li><i class="fa fa-angle-right" aria-hidden="true"></i>
                                                <?php echo homey_option('sn_check_out_before'); ?>: <strong><?php echo esc_attr($checkout_before); ?></strong>
                                            </li>
                                            <?php } ?>
                                        <?php } ?>

                                        <?php if( (!empty($room_type) || !empty($listing_type)) && $hide_labels['sn_type_label'] != 1  ) { ?>
                                        <li><i class="fa fa-angle-right" aria-hidden="true"></i>
                                            <?php echo homey_option('sn_type_label'); ?>: <strong><?php echo esc_attr($room_type).' '.$slash.' '.esc_attr($listing_type); ?></strong>
                                        </li>
                                        <?php } ?>

                                        <?php if(!empty($size) && $hide_labels['sn_size_label'] != 1) { ?>
                                        <li><i class="fa fa-angle-right" aria-hidden="true"></i>
                                            <?php echo homey_option('sn_size_label'); ?>: <strong><?php echo esc_attr($size).' '.esc_attr($size_unit); ?></strong>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                </div><!-- block-right -->
                            </div><!-- block-body -->
                        </div><!-- block-section -->
                    </div><!-- block -->
                </div>
                <?php } ?>


                <?php if(homey_option('print_pricing')) { ?>
                <div id="price-section" class="price-section">
                    <div class="block">
                        <div class="block-section">
                            <div class="block-body">
                                <div class="block-left">
                                    <h3 class="title"><?php echo homey_option('sn_prices_heading'); ?></h3>
                                </div><!-- block-left -->
                                <div class="block-right">
                                    <ul class="detail-list detail-list-2-cols">

                                        <?php if($booking_type == 'per_hour') { ?>

                                            <?php if(!empty($hour_price)  && $hide_labels['sn_hourly_label'] != 1) { ?>
                                            <li>
                                                <i class="fa fa-angle-right" aria-hidden="true"></i>
                                                <?php echo homey_option('sn_hourly_label');?>:
                                                <strong><?php echo homey_formatted_price($hour_price, false); ?></strong>
                                            </li>
                                            <?php } ?>

                                        <?php } else { ?>
                                            <?php if(!empty($night_price)) { ?>
                                            <li>
                                                <i class="fa fa-angle-right" aria-hidden="true"></i>
                                                <?php echo homey_option('sn_nightly_label');?>:
                                                <strong><?php echo homey_formatted_price($night_price, false); ?></strong>
                                            </li>
                                            <?php } ?>
                                        <?php } ?>

                                        <?php if(!empty($weekends_price)  && $hide_labels['sn_weekends_label'] != 1) { ?>
                                        <li>
                                            <i class="fa fa-angle-right" aria-hidden="true"></i>
                                            <?php echo homey_option('sn_weekends_label');?> (<?php echo esc_attr($weekendDays); ?>):
                                            <strong><?php echo homey_formatted_price($weekends_price, false); ?></strong>
                                        </li>
                                        <?php } ?>


                                        <?php if($booking_type == 'per_day' || $booking_type == 'per_day_date') { ?>
                                            <?php if(!empty($priceWeekly)) { ?>
                                            <li>
                                                <i class="fa fa-angle-right" aria-hidden="true"></i>
                                                <?php echo homey_option('sn_weekly7d_label');?>:
                                                <strong><?php echo homey_formatted_price($priceWeekly, true); ?></strong>
                                            </li>
                                            <?php } ?>

                                            <?php if(!empty($priceMonthly)) { ?>
                                            <li>
                                                <i class="fa fa-angle-right" aria-hidden="true"></i>
                                                <?php echo homey_option('sn_monthly30d_label');?>:
                                                <strong><?php echo homey_formatted_price($priceMonthly, true); ?></strong>
                                            </li>
                                            <?php } ?>
                                        <?php } ?>

                                        <?php if(!empty($security_deposit) && $hide_labels['sn_security_deposit_label'] != 1) { ?>
                                        <li>
                                            <i class="fa fa-angle-right" aria-hidden="true"></i>
                                            <?php echo homey_option('sn_security_deposit_label');?>:
                                            <strong><?php echo homey_formatted_price($security_deposit, true); ?></strong>
                                        </li>
                                        <?php } ?>

                                        <?php if(!empty($additional_guests_price) && $hide_labels['sn_addinal_guests_label'] != 1) { ?>
                                        <li>
                                            <i class="fa fa-angle-right" aria-hidden="true"></i>
                                            <?php echo homey_option('sn_addinal_guests_label');?>:
                                            <strong><?php echo homey_formatted_price($additional_guests_price, false); ?></strong>
                                        </li>
                                        <?php } ?>

                                        <?php if(!empty($allow_additional_guests) && $hide_labels['sn_allow_additional_guests'] != 1) { ?>
                                        <li>
                                            <i class="fa fa-angle-right" aria-hidden="true"></i>
                                            <?php echo homey_option('sn_allow_additional_guests');?>:
                                            <strong><?php echo esc_attr($allow_additional_guests); ?></strong>
                                        </li>
                                        <?php } ?>

                                        <?php if(!empty($cleaning_fee) && $hide_labels['sn_cleaning_fee'] != 1) { ?>
                                        <li>
                                            <i class="fa fa-angle-right" aria-hidden="true"></i>
                                            <?php echo homey_option('sn_cleaning_fee');?>:
                                            <strong><?php echo homey_formatted_price($cleaning_fee, true); ?></strong> <?php echo esc_attr($cleaning_fee_period); ?>
                                        </li>
                                        <?php } ?>

                                        <?php if(!empty($city_fee) && $hide_labels['sn_city_fee'] != 1) { ?>
                                        <li>
                                            <i class="fa fa-angle-right" aria-hidden="true"></i>
                                            <?php echo homey_option('sn_city_fee');?>:
                                            <strong><?php echo homey_formatted_price($city_fee, true); ?></strong> <?php echo esc_attr($city_fee_period); ?>
                                        </li>
                                        <?php } ?>

                                        <?php
                                        if($booking_type == 'per_hour') { ?>
                                            <?php if(!empty($min_stay_hours) && $hide_labels['sn_min_no_of_hours'] != 1) { ?>
                                            <li>
                                                <i class="fa fa-angle-right" aria-hidden="true"></i>
                                                <?php echo esc_attr(homey_option('sn_min_no_of_hours'));?>:
                                                <strong><?php echo esc_attr($min_stay_hours); ?></strong>
                                            </li>
                                            <?php }

                                        } else { ?>



                                            <?php if(!empty($min_stay_days)) { ?>
                                            <li>
                                                <i class="fa fa-angle-right" aria-hidden="true"></i>
                                                <?php echo homey_option('sn_min_no_of_days');?>:
                                                <strong><?php echo esc_attr($min_stay_days); ?></strong>
                                            </li>
                                            <?php } ?>

                                            <?php if(!empty($max_stay_days)) { ?>
                                            <li>
                                                <i class="fa fa-angle-right" aria-hidden="true"></i>
                                                <?php echo homey_option('sn_max_no_of_days');?>:
                                                <strong><?php echo esc_attr($max_stay_days); ?></strong>
                                            </li>
                                            <?php }
                                        }?>
                                    </ul>
                                </div><!-- block-right -->
                            </div><!-- block-body -->
                        </div><!-- block-section -->
                    </div><!-- block -->
                </div>
                <?php } ?>

                <?php
                $accomodation = homey_get_listing_data_by_id('accomodation', $listing_id);
                $guests = $homey_local['acc_guest_label'];
                $print_accomodation = homey_option('print_accomodation');
                $icon_type = homey_option('detail_icon_type');

                if(!empty($accomodation) && $print_accomodation) {
                ?>
                <div id="accomodation-section" class="accomodation-section">
                    <div class="block">
                        <div class="block-section">
                            <div class="block-body">
                                <div class="block-left">
                                    <h3 class="title"><?php echo homey_option('sn_accomodation_text'); ?></h3>
                                </div><!-- block-left -->
                                <div class="block-right">

                                    <?php foreach($accomodation as $acc): ?>
                                    <div class="block-col block-col-33 block-accomodation">
                                        <div class="block-icon">
                                            <?php
                                            if($icon_type == 'fontawesome_icon') {
                                                echo '<i class="'.esc_attr(homey_option('de_acco_sec_icon')).'"></i>';

                                            } elseif($icon_type == 'custom_icon') {
                                                echo '<img src="'.esc_url(homey_option( 'de_cus_acco_sec_icon', false, 'url' )).'" alt="'.esc_attr__('icon', 'homey').'">';
                                            }
                                            ?>
                                        </div>
                                        <dl>
                                            <?php
                                            if($acc['acc_guests'] > 1) { $guests = homey_option('sn_acc_guests_label'); } else { $guests = $homey_local['acc_guest_label']; }

                                            if(!empty($acc['acc_bedroom_name'])) {
                                                echo '<dt>'.$acc['acc_bedroom_name'].'</dt>';
                                            }
                                            if(!empty($acc['acc_no_of_beds']) || !empty($acc['acc_bedroom_type'])) {
                                                echo '<dt>'.$acc['acc_no_of_beds'].' '.$acc['acc_bedroom_type'].'</dt>';
                                            }
                                            if(!empty($acc['acc_guests'])) {

                                                echo '<dt>'.$acc['acc_guests'].' '.esc_attr($guests).'</dt>';
                                            }
                                            ?>
                                        </dl>
                                    </div>
                                    <?php endforeach; ?>
                                </div><!-- block-right -->
                            </div><!-- block-body -->
                        </div><!-- block-section -->
                    </div><!-- block -->
                </div><!-- accomodation-section -->
                <?php } ?>

                <?php
                $amenities   = wp_get_post_terms( $listing_id, 'listing_amenity', array("fields" => "all"));
                $facilities  = wp_get_post_terms( $listing_id, 'listing_facility', array("fields" => "all"));
                ?>
                <?php if(homey_option('print_features')) { ?>
                <div id="features-section" class="features-section">
                    <div class="block">
                        <div class="block-section">
                            <div class="block-body">
                                <div class="block-left">
                                    <h3 class="title"><?php echo homey_option('sn_features'); ?></h3>
                                </div><!-- block-left -->
                                <div class="block-right">
                                    <?php if(!empty($amenities)) { ?>
                                    <p><strong><?php echo esc_attr(homey_option('sn_amenities')); ?></strong></p>
                                    <ul class="detail-list detail-list-2-cols">
                                        <?php foreach($amenities as $amenity): ?>
                                            <li><i class="fa fa-angle-right" aria-hidden="true"></i> <?php echo esc_attr($amenity->name); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php } ?>

                                    <?php if(!empty($facilities)) { ?>
                                    <p><strong><?php echo homey_option('sn_facilities'); ?></strong></p>
                                    <ul class="detail-list detail-list-2-cols">
                                        <?php foreach($facilities as $facility): ?>
                                            <li><i class="fa fa-angle-right" aria-hidden="true"></i> <?php echo esc_attr($facility->name); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php } ?>

                                </div><!-- block-right -->
                            </div><!-- block-body -->
                        </div><!-- block-section -->
                    </div><!-- block -->
                </div>
                <?php } ?>

                <?php if(homey_option('print_rules')) { ?>
                <div id="rules-section" class="rules-section">
                    <div class="block">
                        <div class="block-section">
                            <div class="block-body">
                                <div class="block-left">
                                    <h3 class="title"><?php echo homey_option('sn_terms_rules'); ?></h3>
                                </div><!-- block-left -->
                                <div class="block-right">
                                    <ul class="rules_list detail-list">
                                        <?php if($hide_labels['sn_smoking_allowed'] != 1) { ?>
                                        <li>
                                            <i class="<?php echo esc_attr($smoke_allow); ?>" aria-hidden="true"></i>
                                            <?php echo homey_option('sn_smoking_allowed'); ?>:
                                            <strong><?php echo esc_attr($smoke_text); ?></strong>
                                        </li>
                                        <?php } ?>

                                        <?php if($hide_labels['sn_pets_allowed'] != 1) { ?>
                                        <li>
                                            <i class="<?php echo esc_attr($pets_allow); ?>" aria-hidden="true"></i>
                                            <?php echo homey_option('sn_pets_allowed'); ?>:
                                            <strong><?php echo esc_attr($pets_text); ?></strong>
                                        </li>
                                        <?php } ?>

                                        <?php if($hide_labels['sn_party_allowed'] != 1) { ?>
                                        <li>
                                            <i class="<?php echo esc_attr($party_allow); ?>" aria-hidden="true"></i>
                                            <?php echo homey_option('sn_party_allowed'); ?>:
                                            <strong><?php echo esc_attr($party_text); ?></strong>
                                        </li>
                                        <?php } ?>

                                        <?php if($hide_labels['sn_children_allowed'] != 1) { ?>
                                        <li>
                                            <i class="<?php echo esc_attr($children_allow); ?>" aria-hidden="true"></i>
                                            <?php echo homey_option('sn_children_allowed'); ?>:
                                            <strong><?php echo esc_attr($children_text); ?></strong>
                                        </li>
                                        <?php } ?>
                                    </ul>

                                    <?php if( !empty($additional_rules)) { ?>
                                    <ul class="detail-list">
                                        <li><strong><?php echo homey_option('sn_add_rules_info'); ?></strong></li>
                                        <li><?php echo esc_attr($additional_rules); ?></li>
                                    </Ul>
                                    <?php } ?>

                                </div><!-- block-right -->
                            </div><!-- block-body -->
                        </div><!-- block-section -->
                    </div><!-- block -->
                </div>
                <?php } ?>

                <?php if(homey_option('print_availability')) {
                    if($min_stay_hours > 1) {
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
                                    <h3 class="title"><?php echo homey_option('sn_availability_label'); ?></h3>
                                </div><!-- block-left -->
                                <div class="block-right">
                                    <ul class="detail-list detail-list-2-cols">
                                        <?php if($booking_type == 'per_hour') { ?>
                                            <li>
                                                <i class="fa fa-calendar-o" aria-hidden="true"></i>
                                                <?php echo homey_option('sn_min_stay_is');?> <strong><?php echo esc_attr($min_stay_hours); ?> <?php echo esc_attr($min_book_hours_label);?></strong>
                                            </li>
                                        <?php } else { ?>
                                            <li>
                                                <i class="fa fa-calendar-o" aria-hidden="true"></i>
                                                <?php echo homey_option('sn_min_stay_is');?> <strong><?php echo esc_attr($min_stay_days); ?> <?php echo homey_option('sn_night_label');?></strong>
                                            </li>
                                            <li>
                                                <i class="fa fa-calendar-o" aria-hidden="true"></i>
                                                <?php echo homey_option('sn_max_stay_is');?> <strong><?php echo esc_attr($max_stay_days); ?> <?php echo homey_option('sn_nights_label');?></strong>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div><!-- block-right -->
                            </div><!-- block-body -->
                        </div><!-- block-section -->
                    </div><!-- block -->
                </div>
                <?php } ?>


                <?php $prop_images = get_post_meta( $listing_id, 'homey_listing_images', false ); ?>
                <?php $print_gallery = homey_option('print_gallery'); ?>
                <?php if( !empty( $prop_images ) && $print_gallery) { ?>
                <div class="image-section">
                    <div class="block">
                        <div class="block-body gallery-block">
                            <?php foreach( $prop_images as $img_id ): ?>
                            <div class="block-left">
                                <?php echo wp_get_attachment_image( $img_id, 'homey-gallery', array( "class" => "img-responsive" ) ); ?>
                            </div><!-- block-left -->
                            <?php endforeach; ?>
                        </div><!-- block-body -->
                    </div>
                </div>
                <?php } ?>

            </div>
        </div>


<?php
        print '<script>window.print();</script></body></html>';
        wp_die();
    }
}



add_action( 'wp_ajax_homey_put_hold_listing', 'homey_put_hold_listing' );

if( !function_exists('homey_put_hold_listing') ) {

    function homey_put_hold_listing() {
        $current_user = wp_get_current_user();
        $userID       =   $current_user->ID;
        $user_login   =   $current_user->user_login;

        if ( !is_user_logged_in() ) {
            exit('ko');
        }

        $listing_id=intval($_POST['listing_id']);
        if(!is_numeric($listing_id)) {
            exit();
        }

        $the_post= get_post($listing_id);

        if($the_post->post_status=='disabled'){
            $new_status = 'publish';
        }else{
            $new_status = 'disabled';
        }
        $my_post = array(
            'ID'           => $listing_id,
            'post_status'  => $new_status
        );

        wp_update_post( $my_post );

        $ajax_response = array( 'success' => true , 'reason' => esc_html__( 'Success', 'homey' ) );
        echo json_encode( $ajax_response );
        wp_die();

    }
}


add_action( 'init', 'homey_my_custom_post_status' );
if( !function_exists('homey_my_custom_post_status') ):
    function homey_my_custom_post_status(){

        register_post_status( 'disabled', array(
                    'label'                     => esc_html__(  'disabled', 'homey' ),
                    'public'                    => false,
                    'exclude_from_search'       => false,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop( 'Disabled by user <span class="count">(%s)</span>', 'Disabled by user <span class="count">(%s)</span>','homey' ),
            ) );

    }
endif;
