<?php
/*-----------------------------------------------------------------------------------*/
// Allowed HTML tags 
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_allowed_html')) {
	function homey_allowed_html() {
		$allowed_html_array = array(
		    'a' => array(
		        'href' => array(),
                'title' => array(),
		        'target' => array()
		    ),
            'strong' => array(),
            'th' => array(),
            'td' => array()
		); 
		return $allowed_html_array;
	}
}

if( !function_exists('homey_wpml_translate_single_string') ) {
    function homey_wpml_translate_single_string($string_name) {
        $translated_string = apply_filters('wpml_translate_single_string', $string_name, 'homey_fields_builder', $string_name );

        return $translated_string;
    }
}

if( !function_exists('homey_is_woocommerce')) {
    function homey_is_woocommerce() {

        if( homey_option('homey_payment_gateways', 'homey_custom_gw') == 'gw_woocommerce' && class_exists( 'WooCommerce' ) ) {
            return true;
        } else {
            return false;
        }
    }
}


if(!function_exists('homey_time_format')) {
    function homey_time_format() {
        $time_format = homey_option('homey_time_format');
        if($time_format == 12) {
            $format = "g:i a";
        } elseif($time_format == 24) {
            $format = "H:i";
        } else {
            $format = "G:i a";
        }
        return $format;
    }
}

if( !function_exists('homey_get_map_system') ) {
    function homey_get_map_system() {
        $homey_map_system = homey_option('homey_map_system');

        if($homey_map_system == 'open_street_map' || $homey_map_system == 'mapbox') {
            $map_system = 'open_street_map';
        } elseif($homey_map_system == 'google' && homey_option('map_api_key') != "") {
            $map_system = 'google';
        } else {
            $map_system = 'open_street_map';
        }
        return $map_system;
    }
}

if( !function_exists('homey_metabox_map_type') ) {
    function homey_metabox_map_type() {
        $homey_map_system = homey_option('homey_map_system');

        if($homey_map_system == 'open_street_map' || $homey_map_system == 'mapbox') {
            $map_system = 'osm';
        } elseif($homey_map_system == 'google') {
            $map_system = 'map';
        } else {
            $map_system = 'osm';
        }
        return $map_system;
    }
}

if( !function_exists('homey_map_api_key') ) {

    function homey_map_api_key() {

        $homey_map_system = homey_get_map_system();   
        $mapbox_api_key = homey_option('mapbox_api_key');   
        $googlemap_api_key = homey_option('map_api_key'); 

        if($homey_map_system == 'google') {
            $googlemap_api_key = urlencode( $googlemap_api_key );
            return $googlemap_api_key;

        } elseif($homey_map_system == 'open_street_map') {
            $mapbox_api_key = urlencode( $mapbox_api_key );
            return $mapbox_api_key;
        }
    }
}

/*-----------------------------------------------------------------------------------*/
// Register locations in theme for elementor templates 
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'homey_register_elementor_templates_locations' ) ) {

    function homey_register_elementor_templates_locations( $elementor_theme_manager ) {

        $elementor_theme_manager->register_location( 'header' );
        $elementor_theme_manager->register_location( 'footer' );
        $elementor_theme_manager->register_location( 'single' );
        $elementor_theme_manager->register_location( 'archive' );
    }

    add_action( 'elementor/theme/register_locations', 'homey_register_elementor_templates_locations' );
}

if(!function_exists('homey_calendar_months')) {
    function homey_calendar_months() {
        $homey_calendar_months = homey_option('homey_calendar_months');
        $homey_calendar_months = intval($homey_calendar_months);
        if(empty($homey_calendar_months)) {
            $homey_calendar_months = 12;
        }
        return $homey_calendar_months;
    }
}


if(!function_exists('homey_posts_count')) {
    function homey_posts_count($post_type) {
        if(empty($post_type)) {
            $count_posts = 0;
        }

        $count_posts = wp_count_posts($post_type);

        $count_posts = $count_posts->publish;
        return $count_posts;
    }
}

if(!function_exists('homey_get_listings_count_from_last_24h')) {
    function homey_get_listings_count_from_last_24h($post_type ='post') {
        global $wpdb;

        $numposts = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(ID) ".
                "FROM {$wpdb->posts} ".
                "WHERE ".
                    "post_status='publish' ".
                    "AND post_type= %s ".
                    "AND post_date> %s",
                $post_type, date('Y-m-d H:i:s', strtotime('-24 hours'))
            )
        );
        return $numposts;
    }
}

if(!function_exists('homey_get_users_count_from_last_24h')) {
    function homey_get_users_count_from_last_24h() {
        global $wpdb;

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(ID) ".
                "FROM {$wpdb->users} ".
                "WHERE ".
                    "user_registered > %s ",
                date('Y-m-d H:i:s', strtotime('-24 hours'))
            )
        );
        return $count;
    }
}

add_action( 'redux/construct', 'homey_remove_as_plugin_flag' );
/**
* Remove plugin flag from redux. Get rid of redirect
*
* @since 1.0.0
*/
function homey_remove_as_plugin_flag() {
    ReduxFramework::$_as_plugin = false;
}

if(!function_exists('homey_format_date_simple')) {
    function homey_format_date_simple($gdate) {
        $homey_date_format = homey_option('homey_date_format');

        if(empty($gdate)) {
            return $gdate;
        }
        $dateVal = strtotime($gdate);

        if($homey_date_format == 'yy-mm-dd') {
            $return_date = date("Y-m-d", $dateVal);

        } elseif($homey_date_format == 'yy-dd-mm') {
            $return_date = date("Y-d-m", $dateVal);

        } elseif($homey_date_format == 'mm-yy-dd') {
            $return_date = date("m-Y-d", $dateVal);
            
        } elseif($homey_date_format == 'dd-yy-mm') {
            $return_date = date("d-Y-m", $dateVal);
            
        } elseif($homey_date_format == 'mm-dd-yy') {
            $return_date = date("m-d-Y", $dateVal);
            
        } elseif($homey_date_format == 'dd-mm-yy') {
            $return_date = date("d-m-Y", $dateVal);
            
        } elseif($homey_date_format == 'dd.mm.yy') {
            $return_date = date("d.m.Y", $dateVal);

        } else {
            $return_date = date("Y-m-d", $dateVal);
            if (empty(trim($return_date))){
                $return_date = $dateVal;
            }
        }

        return $return_date;
    }
}


if(!function_exists('homey_get_formatted_date')) {
    function homey_get_formatted_date($year, $month, $day) {
        $homey_date_format = homey_option('homey_date_format');

        $day = $day < 10?'0'. (int) $day:$day;
        $month = $month < 10?'0'. (int) $month:$month;

        if($homey_date_format == 'yy-mm-dd') {
            $return_date = $year.'-'.$month.'-'.$day;

        } elseif($homey_date_format == 'yy-dd-mm') {
            $return_date = $year.'-'.$day.'-'.$month;

        } elseif($homey_date_format == 'mm-yy-dd') {
            $return_date = $month.'-'.$year.'-'.$day;
            
        } elseif($homey_date_format == 'dd-yy-mm') {
            $return_date = $day.'-'.$year.'-'.$month;
            
        } elseif($homey_date_format == 'mm-dd-yy') {
            $return_date = $month.'-'.$day.'-'.$year;
            
        } elseif($homey_date_format == 'dd-mm-yy') {
            $return_date = $day.'-'.$month.'-'.$year;
            
        }elseif($homey_date_format == 'dd.mm.yy') {
            $return_date = $day.'.'.$month.'.'.$year;

        } else {
            $return_date = $year.'-'.$month.'-'.$day;
        }

        return $return_date;
    }
}

if(!function_exists('homey_return_formatted_date')) {
    function homey_return_formatted_date($date_unix) {

        $return_date = '';
        if(!empty($date_unix)) {
            $return_date = date(get_option( 'date_format' ), $date_unix);
        }
        return $return_date;
        
    }
}

if(!function_exists('homey_get_formatted_time')) {
    function homey_get_formatted_time($date_unix) {

        $return_time = '';
        if(!empty($date_unix)) {
            $return_time = date(get_option( 'time_format' ), $date_unix);
        }
        return $return_time;
        
    }
}


/* --------------------------------------------------------------------------
 * Homey booking type
 ---------------------------------------------------------------------------*/
if ( ! function_exists( 'homey_booking_type' ) ) {

    function homey_booking_type() {
        global $post;
        $homey_prefix = 'homey_';
        $homey_site_mode = homey_option('homey_site_mode'); // per_hour, per_day, both
        $booking_type = get_post_meta( get_the_ID(), $homey_prefix.'booking_type', true ); //per_day, per_hour

        if($homey_site_mode == 'per_day_date') {
            $site_mode = 'per_day_date'; // This is per day

        }elseif($homey_site_mode == 'per_day') {
            $site_mode = 'per_day'; // This is per night 

        } elseif($homey_site_mode == 'per_hour') {
            $site_mode = 'per_hour';

        } elseif($homey_site_mode == 'per_week') {
            $site_mode = 'per_week';

        } elseif($homey_site_mode == 'per_month') {
            $site_mode = 'per_month';

        } elseif($homey_site_mode == 'both') {
            if($booking_type == 'per_day_date') {
                $site_mode = 'per_day_date'; // This is per day

            } elseif ($booking_type == 'per_day') {
                $site_mode = 'per_day'; // This is per night 

            } elseif ($booking_type == 'per_hour') {
               $site_mode = 'per_hour';

            } elseif ($booking_type == 'per_week') {
               $site_mode = 'per_week';

            } elseif ($booking_type == 'per_month') {
               $site_mode = 'per_month';

            } else {
                $site_mode = 'per_day';
            }
        } else {
            $site_mode = '';
        }
        return $site_mode;
        
    }
}

if ( ! function_exists( 'homey_search_type' ) ) {
    function homey_search_type() {
        global $post;
        $homey_site_mode = homey_option('homey_site_mode');

        if($homey_site_mode == 'per_day_date' || $homey_site_mode == 'per_day' || $homey_site_mode == 'per_week' || $homey_site_mode == 'per_month' ) {
            $site_mode = 'per_day';

        } elseif($homey_site_mode == 'per_hour') {
            $site_mode = 'per_hour';

        } elseif($homey_site_mode == 'both') {
            $site_mode = 'mixed';
        } else {
            $site_mode = 'per_day';
        }

        if( homey_is_halfmap_page() ) { 
            $site_mode = get_post_meta( $post->ID, 'homey_halfmap_booking_type', true );
            
        }

        if( homey_is_listing_page() ) { 
            $site_mode = get_post_meta( $post->ID, 'homey_listings_booking_type', true );
        }

        if( isset($_GET['booking_type']) && $_GET['booking_type'] != '' ) {
            $site_mode = $_GET['booking_type'];
        }

        return $site_mode;
    }
}

/* --------------------------------------------------------------------------
 * Homey booking type
 ---------------------------------------------------------------------------*/
if ( ! function_exists( 'homey_booking_type_by_id' ) ) {

    function homey_booking_type_by_id($listing_id) {
        global $post;
        $homey_prefix = 'homey_';
        $homey_site_mode = homey_option('homey_site_mode'); // per_hour, per_day, both
        $booking_type = get_post_meta( $listing_id, $homey_prefix.'booking_type', true ); //per_day, per_hour

        if($homey_site_mode == 'per_day_date') {
            $site_mode = 'per_day_date';

        } elseif($homey_site_mode == 'per_day') {
            $site_mode = 'per_day';

        } elseif($homey_site_mode == 'per_hour') {
            $site_mode = 'per_hour';

        } elseif($homey_site_mode == 'per_week') {
            $site_mode = 'per_week';

        } elseif($homey_site_mode == 'per_month') {
            $site_mode = 'per_month';

        } elseif($homey_site_mode == 'both') {
            if($booking_type == 'per_day_date') {
                $site_mode = 'per_day_date';

            } elseif($booking_type == 'per_day') {
                $site_mode = 'per_day';

            } elseif ($booking_type == 'per_hour') {
               $site_mode = 'per_hour';

            } elseif ($booking_type == 'per_week') {
               $site_mode = 'per_week';

            } elseif ($booking_type == 'per_month') {
               $site_mode = 'per_month';

            } else {
                $site_mode = 'per_day';
            }
        } else {
            $site_mode = '';
        }
        return $site_mode;
        
    }
}

/* --------------------------------------------------------------------------
 * after login redirect page
 ---------------------------------------------------------------------------*/
 if(!function_exists('homey_after_login_redirect_page')) {
    function homey_after_login_redirect_page() {
        global $post;
        $login_redirect = '';
        $after_login_redirect = homey_option('login_redirect');
        if ($after_login_redirect == 'same_page') {

            if (is_tax()) {
                $login_redirect = get_term_link(get_query_var('term'), get_query_var('taxonomy'));
            } else {
                if (is_home() || is_front_page()) {
                    $login_redirect = esc_url( home_url() );
                } else {
                    if (!is_404() && !is_search() && !is_author()) {
                        $login_redirect = get_permalink($post->ID);
                    }
                }
            }

        } else {
            $login_redirect = homey_option('login_redirect_link');
        }
        return $login_redirect;
    }
 }


/* --------------------------------------------------------------------------
 * Hex to RGB values
 ---------------------------------------------------------------------------*/

 if ( ! function_exists( 'homey_hex2rgb' ) ) {
     function homey_hex2rgb($hex) {

        $hex = preg_replace("/#/", "", $hex );

        $color = array();

        if(strlen($hex) == 3) {
            $color['r'] = hexdec(substr($hex, 0, 1) );
            $color['g'] = hexdec(substr($hex, 1, 1) );
            $color['b'] = hexdec(substr($hex, 2, 1) );
        } else {
            $color['r'] = hexdec(substr($hex, 0, 2) );
            $color['g'] = hexdec(substr($hex, 2, 2) );
            $color['b'] = hexdec(substr($hex, 4, 4) );
        }

        return $color;
     }
}

if(! function_exists('homey_body_classes')) {
    function homey_body_classes( $classes ) {

        if ( !is_page_template( array(
            'template/template-splash.php'
        ) ) ) {

            $classes[] = 'compare-property-active';
        }
        
          
        return $classes;
    }
}
add_filter( 'body_class','homey_body_classes' );

/* --------------------------------------------------------------------------
 * Removes version scripts number if enabled for better Google Page Speed Scores.
 ---------------------------------------------------------------------------*/
if ( ! function_exists( 'homey_remove_wp_ver_css_js' ) ) {
    function homey_remove_wp_ver_css_js( $src ) {
        if ( homey_option( 'remove_scripts_version', '1' ) ) {
            if ( strpos( $src, 'ver=' ) ) {
                $src = remove_query_arg( 'ver', $src );
            }
        }
        return $src;
    }
}
//add_filter( 'style_loader_src', 'homey_remove_wp_ver_css_js', 9999 );
//add_filter( 'script_loader_src', 'homey_remove_wp_ver_css_js', 9999 );

/* --------------------------------------------------------------------------
 * Get author by post id
 ---------------------------------------------------------------------------*/
if( !function_exists('homey_get_author_by_post_id') ):
    function homey_get_author_by_post_id( $post_id = 0 ){
        $post = get_post( $post_id );
        return $post->post_author;
    }
endif;

/*-----------------------------------------------------------------------------------*/
// Get required *
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_req') ) {
    function homey_req( $field ) {
        $required_fields = homey_option('add_listing_required_fields');
        if( $required_fields[$field] != 0 ) {
            return '*';
        }
        return '';
    }
}

if( !function_exists('homey_get_required') ) {
    function homey_get_required( $field ) {
        $required_fields = homey_option('add_listing_required_fields');
        if( $required_fields[$field] != 0 ) {
            return 'required';
        }
        return '';
    }
}

if( !function_exists('homey_required') ) {
    function homey_required( $field ) {
        echo homey_get_required( $field );
    }
}

/* --------------------------------------------------------------------------
 * Get excerpt limit 
 ---------------------------------------------------------------------------*/
if( !function_exists('homey_get_excerpt') ) {
    function homey_get_excerpt($limit)
    {
        $excerpt = explode(' ', get_the_excerpt(), $limit);
        if (count($excerpt) >= $limit) {
            array_pop($excerpt);
            $excerpt = implode(" ", $excerpt) . '...';
        } else {
            $excerpt = implode(" ", $excerpt);
        }
        $excerpt = preg_replace('`\[[^\]]*\]`', '', $excerpt);
        return $excerpt;
    }
}

if(!function_exists('homey_filter_body_class')) {
    function homey_filter_body_class( $classes, $class ) { 
        $header_type = homey_option('header_type');
        if($header_type == '4') {
            $classes[] = 'side-nav-active';
        }
        return $classes; 
    }
    add_filter( 'body_class', 'homey_filter_body_class', 10, 2 );
} 

if(!function_exists('homey_thread_link_after_reservation')) {
    function homey_thread_link_after_reservation($reservationID) {
        $chcek_reservation_thread = homey_chcek_reservation_thread($reservationID);

        $messages_page = homey_get_template_link_2('template/dashboard-messages.php');
        
        if( ! empty( $chcek_reservation_thread ) ) {
            $messages_page_link = add_query_arg( array(
                'thread_id' => $chcek_reservation_thread
            ), $messages_page );
            return $messages_page_link;
        }
        return '';
        
    }
}

/* --------------------------------------------------------------------------
 * Get content limit 
 ---------------------------------------------------------------------------*/
if( !function_exists('homey_get_content') ) {
    function homey_get_content($limit)
    {
        $content = explode(' ', get_the_content(), $limit);
        if (count($content) >= $limit) {
            array_pop($content);
            $content = implode(" ", $content) . '...';
        } else {
            $content = implode(" ", $content);
        }
        $content = preg_replace('/\[.+\]/', '', $content);
        $content = apply_filters('the_content', $content);
        $content = str_replace(']]>', ']]&gt;', $content);
        return $content;
    }
}

if(!function_exists('homey_listing_host')) {
    function homey_listing_host($reservationID) {
        $listing_owner_id = get_post_meta($reservationID, 'listing_owner', true);
        return $listing_owner_id;
    }
}

if(!function_exists('homey_give_access')) {
    function homey_give_access($reservationID) {
        global $current_user;
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        $listing_renter_id = get_post_meta($reservationID, 'listing_renter', true);
        $listing_owner_id = get_post_meta($reservationID, 'listing_owner', true);

        if( ( $user_id == $listing_owner_id ) || ( $user_id == $listing_renter_id ) || homey_is_admin() ) {
            return true;
        }

        return false;
    }
}

if(!function_exists('homey_listing_guest')) {
    function homey_listing_guest($reservationID) {
        global $current_user;
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        $listing_renter_id = get_post_meta($reservationID, 'listing_renter', true);
        $listing_owner_id = get_post_meta($reservationID, 'listing_owner', true);

        if( ($user_id == $listing_renter_id) || homey_is_renter()) {
            return true;
        } 

        return false;
    }
}

if( !function_exists('homey_is_renter') ) {
    function homey_is_renter($user_id = null) {
        global $current_user;
        $current_user = wp_get_current_user();

        if(!empty($user_id)) {
            $current_user = get_userdata($user_id);
        }

        if (in_array('homey_renter', (array)$current_user->roles) || in_array('subscriber', (array)$current_user->roles)) {
            return true;
        }
        return false;
    }
}

if( !function_exists('homey_is_host') ) {
    function homey_is_host() {
        global $current_user;
        $current_user = wp_get_current_user();

        if (in_array('homey_host', (array)$current_user->roles) || in_array('author', (array)$current_user->roles)) {
            return true;
        }
        return false;
    }
}

if( !function_exists('homey_is_admin') ) {
    function homey_is_admin() {
        global $current_user;
        $current_user = wp_get_current_user();

        if (in_array('administrator', (array)$current_user->roles)) {
            return true;
        }
        return false;
    }
}

if( !function_exists('homey_user_role_by_post_id')) {
    function homey_user_role_by_post_id($the_id) {

        $user_id = get_post_field( 'post_author', $the_id );
        $user = new WP_User($user_id); //administrator
        $user_role = $user->roles[0];
        return $user_role;
    }
}

if( !function_exists('homey_user_role_by_user_id')) {
    function homey_user_role_by_user_id($user_id) {

        $user = new WP_User($user_id);
        $user_role = $user->roles[0];
        return $user_role;
    }
}


if( !function_exists('homey_postid_needed') ) {
    function homey_postid_needed() {
        if( is_search() ) {
            return false;
        } elseif( is_author() ) {
            return false;
        } elseif( is_404() ) {
            return false;
        }
        return true;
    }
}

if(!function_exists('homey_convert_date_to_cal')) {
    function homey_convert_date_to_cal($timestamp) {
      return date('Ymd\THis\Z', $timestamp);
    }
}

if(!function_exists('homey_string_escaped')) {
    function homey_string_escaped($string) {
      return preg_replace('/([\,;])/','\\\$1', $string);
    }
}

if(!function_exists('homey_is_login_register')) {
    function homey_is_login_register() {
        $nav_login = homey_option('nav_login');
        $nav_register = homey_option('nav_register');
        $become_host_btn = homey_option('become_host_btn');
        if($nav_login || $nav_register || $become_host_btn) {
            return true;
        }
        return false;
    }
}

if(!function_exists('homey_is_dashboard')) {
    function homey_is_dashboard() {

        $files = apply_filters( 'homey_is_dashboard_filter', array(
            'template/dashboard.php',
            'template/dashboard-profile.php',
            'template/dashboard-submission.php',
            'template/dashboard-listing-submitted.php',
            'template/dashboard-favorites.php',
            'template/dashboard-listings.php',
            'template/dashboard-messages.php',
            'template/dashboard-reservations.php',
            'template/dashboard-reservations2.php',
            'template/dashboard-saved-searches.php',
            'template/dashboard-payment.php',
            'template/dashboard-invoices.php',
            'template/dashboard-wallet.php',
            'template/dashboard-membership-host.php',
            
        ) );

        if ( is_page_template( $files ) ) {

            return true;
        }
        return false;
    }
}

if(!function_exists('homey_is_listing_page')) {
    function homey_is_listing_page() {

        $files = apply_filters( 'homey_is_listing_page_filter', array(
            'template/template-listing-card.php',
            'template/template-listing-grid.php',
            'template/template-listing-list.php',
            'template/template-listing-sticky-map.php',
            
        ) );

        if ( is_page_template( $files ) ) {

            return true;
        }
        return false;
    }
}

if(!function_exists('homey_is_halfmap_page')) {
    function homey_is_halfmap_page() {

        if ( is_page_template( array(
            'template/template-half-map.php',
        ) ) ) {

            return true;
        }
        return false;
    }
}

if(!function_exists('homey_is_search_page')) {
    function homey_is_search_page() {
        $search_result_page = homey_option('search_result_page');

        if ( is_page_template(array('template/template-search.php')) && $search_result_page == 'half_map') {

            return true;
        }
        return false;
    }
}

if(!function_exists('homey_is_dashboard_footer')) {
    function homey_is_dashboard_footer() {

        $files = apply_filters( 'homey_is_dashboard_footer_filter', array(
            'template/dashboard.php',
            'template/dashboard-profile.php',
            'template/dashboard-submission.php',
            'template/dashboard-listing-submitted.php',
            'template/dashboard-favorites.php',
            'template/dashboard-listings.php',
            'template/dashboard-messages.php',
            'template/dashboard-reservations.php',
            'template/dashboard-reservations2.php',
            'template/dashboard-saved-searches.php',
            'template/dashboard-payment.php',
            'template/dashboard-invoices.php',
            'template/dashboard-wallet.php',
            'template/template-splash.php',
            'template/dashboard-membership-host.php',
            
        ) );
        if ( is_page_template( $files ) ) {

            return true;
        }
        return false;
    }
}


if( !function_exists('homey_search_needed') ) {
    function homey_search_needed() {
        $transparent = get_post_meta(get_the_ID(), 'homey_header_trans', true);
        $header_type = get_post_meta(get_the_ID(), 'homey_header_type', true);
        $banner_search = get_post_meta(get_the_ID(), 'homey_header_search', true);
        $enable_search_single = 0;//homey_option('enable_search_single');
        $header_style = homey_option('header_type');
        $search_result_page = homey_option('search_result_page');
        $search_position = homey_option('search_position');

        if( is_singular( 'listing' ) ) {
            if($enable_search_single != 0) {
                return true;
            }
            return false;
        } elseif( is_search() ) {
            return false;
        }  elseif( is_author() ) {
            return false;
        } elseif( is_404() ) {
            return false;
        } elseif ( is_page_template( array(
            'template/dashboard.php',
            'template/dashboard-profile.php',
            'template/dashboard-wallet.php',
            'template/dashboard-submission.php',
            'template/dashboard-listing-submitted.php',
            'template/dashboard-favorites.php',
            'template/dashboard-listings.php',
            'template/dashboard-messages.php',
            'template/dashboard-reservations.php',
            'template/dashboard-reservations2.php',
            'template/dashboard-saved-searches.php',
            'template/dashboard-payment.php',
            'template/dashboard-invoices.php',
            'template/dashboard-membership-host.php',
            'template/template-half-map.php',
            'template/template-instance-booking.php',
            'template/template-splash.php',
        ) )
        ) {
            return false;
        } elseif( $transparent != 0 && $header_type != 'none' && ($header_style == '1' || $header_style == '4') && $search_position == 'under_nav') {
            return false;
        } elseif (is_page_template( array('template/template-search.php')) && $search_result_page == 'half_map') {
            return false;
        } elseif($header_type == 'half_search') {
            return false;
        } elseif($banner_search == 1) {
            return false;
        }
        return true;
    }
}

if( !function_exists('homey_banner_needed') ) {
    function homey_banner_needed() {
        if( is_singular( 'listing' ) ) {
            return false;
        } elseif( is_search() ) {
            return false;
        }  elseif( is_author() ) {
            return false;
        } elseif( is_404() ) {
            return false;
        } elseif ( is_page_template( array(
            'template/dashboard.php',
            'template/dashboard-profile.php',
            'template/dashboard-wallet.php',
            'template/dashboard-submission.php',
            'template/dashboard-listing-submitted.php',
            'template/dashboard-favorites.php',
            'template/dashboard-listings.php',
            'template/dashboard-messages.php',
            'template/dashboard-reservations.php',
            'template/dashboard-reservations2.php',
            'template/dashboard-saved-searches.php',
            'template/dashboard-payment.php',
            'template/dashboard-invoices.php',
            'template/dashboard-membership-host.php',
            'template/template-half-map.php',
            'template/template-instance-booking.php',
            'template/template-splash.php',
        ) )
        ) {
            return false;
        }
        return true;
    }
}

if( !function_exists('homey_search_position') ) {
    function homey_search_position() {
        $header_type = get_post_meta(get_the_ID(), 'homey_header_type', true);
        if( $header_type == 'none' ) {
            return true;
        }
        return false;
    }
}

if( !function_exists('homey_is_splash') ) {
    function homey_is_splash() {
        if ( is_page_template( array(
            'template/template-splash.php',
        ) )
        ) {
            return true;
        }
        return false;
    }
}

if( !function_exists('homey_is_map_needed') ) {
    function homey_is_map_needed() {
        global $post;

        $header_type = get_post_meta($post->ID, 'homey_header_type', true); //map //half_search
        $enable_search = homey_option('enable_search');
        if ( is_page_template( array(
            'template/dashboard-submission.php',
            'template/template-half-map.php',
        ) ) 
            || is_singular('listing') 
            || $header_type == 'map'
            || $enable_search != 0
        ) {
            return true;
        }
        return false;
    }
}

if( !function_exists('homey_is_instance_page') ) {
    function homey_is_instance_page() {
        if ( is_page_template( array(
            'template/template-instance-booking.php',
        ) )
        ) {
            return true;
        }
        return false;
    }
}

if( !function_exists('homey_topbar_needed') ) {
    function homey_topbar_needed() {
        $top_bar = homey_option('top_bar');
        $transparent = get_post_meta(get_the_ID(), 'homey_header_trans', true);
        $header_type = get_post_meta(get_the_ID(), 'homey_header_type', true);

        if ( is_page_template( array(
            'template/template-splash.php',
        ) )
        ) {
            return false;
        }
        
        if( $transparent != 0 && $header_type != 'none') {
            return false;
        }

        if($top_bar != 1) {
            return false;
        }

        return true;
    }
}

if( !function_exists('homey_banner_search') ) {
    function homey_banner_search() {
        $banner_search = get_post_meta(get_the_ID(), 'homey_header_search', true);
        if( $banner_search != 0 ) {
            return true;
        }
        return false;
    }
}

if( !function_exists('homey_banner_search_style') ) {
    function homey_banner_search_style() {
        $style = '';
        $banner_search = get_post_meta(get_the_ID(), 'homey_head_search_style', true);
        if( !empty($banner_search) ) {
            $style = $banner_search;
        }

        if(homey_is_splash()) {
            $style = homey_option('splash_search_style');   
        }

        if(isset($_GET['banner_search_style'])) {
            $style = $_GET['banner_search_style'];
        }
        
        return $style;
    }
}

if( !function_exists('homey_banner_search_class') ) {
    function homey_banner_search_class() {
        $css = '';
        $banner_search = get_post_meta(get_the_ID(), 'homey_head_search_style', true);
        $style = isset($_GET['banner_search_style']) ? $_GET['banner_search_style'] : '';
        if( $banner_search == 'vertical' || $style == 'vertical') {
            $css = 'banner-caption-side-search';
        }

        if(homey_is_splash()) {
            $search_type = homey_option('splash_search_style');
            if( $search_type == 'vertical' ) {
                $css = 'banner-caption-side-search';
            }   
        }
        
        echo ''.$css;
    }
}

if( !function_exists('homey_is_vertical') ) {
    function homey_is_vertical() {
        $style = isset($_GET['banner_search_style']) ? $_GET['banner_search_style'] : '';
        $banner_search = get_post_meta(get_the_ID(), 'homey_head_search_style', true);
        if( $banner_search == 'vertical' || $style == 'vertical') {
            return true;
        }

        if(homey_is_splash()) {
            $search_type = homey_option('splash_search_style');
            if( $search_type == 'vertical' ) {
                return true;
            }   
        }
        return false;
    }
}

if(!function_exists('homey_banner_search_div_start')) {
    function homey_banner_search_div_start() {
        if(homey_is_vertical()) {
            echo '<div class="side-search-wrap">';
        }
    }
}

if(!function_exists('homey_banner_search_div_end')) {
    function homey_banner_search_div_end() {
        if(homey_is_vertical()) {
            echo '</div>';
        }
    }
}

if( !function_exists('homey_banner_fullscreen') ) {
    function homey_banner_fullscreen() {
        $banner_height = get_post_meta(get_the_ID(), 'homey_banner_full', true);
        if( $banner_height != 0 ) {
            echo 'top-banner-wrap-fullscreen';
        }
        return '';
    }
}

if( !function_exists('homey_get_transparent') ) {
    function homey_get_transparent() {
        $css_class = '';
        $transparent = get_post_meta(get_the_ID(), 'homey_header_trans', true);
        $header_type = get_post_meta(get_the_ID(), 'homey_header_type', true);

        if( $transparent != 0 && $header_type != 'none' ) {
            $css_class = 'transparent-header';
        }

        if(homey_is_splash()) {
            $css_class = 'transparent-header';
        }
        return $css_class;
    }
}

if( !function_exists('homey_transparent') ) {
    function homey_transparent() {
        echo homey_get_transparent();
    }
}

if( !function_exists('homey_is_top_header') ) {
    function homey_is_top_header() {
        $header_type = get_post_meta(get_the_ID(), 'homey_header_type', true);

        if($header_type == 'none' || $header_type == 'parallax' || $header_type == 'elementor' ) {
            return false;
        }
        return true;
    }
}

if( !function_exists('homey_is_transparent_logo') ) {
    function homey_is_transparent_logo() {
        $css_class = '';
        $header_type = homey_option('header_type');
        $transparent = get_post_meta(get_the_ID(), 'homey_header_trans', true);

        if( $transparent != 0 && ($header_type == '1' || $header_type == '4') ) {
            return true;
        }

        if(homey_is_splash()) {
            return true;
        }
        return false;
    }
}

if( !function_exists('homey_is_transparent') ) {
    function homey_is_transparent() {
        $transparent = get_post_meta(get_the_ID(), 'homey_header_trans', true);

        if( $transparent != 0 ) {
            return true;
        }
        return false;
    }
}


if(!function_exists('homey_get_header_container')) {
    function homey_get_header_container() {


        if(homey_is_dashboard() || ( is_page_template(array('template/template-search.php')) && homey_option('search_result_page') == 'half_map' ) ) {
            $css_class = 'container-fluid';
            return $css_class;
        }
        $css_class = homey_option('header_width');
        if(homey_is_splash()) {
            $css_class = homey_option('splash_layout');
        }
        return $css_class;
    }
}

if(!function_exists('homey_header_container')) {
    function homey_header_container() {
        echo homey_get_header_container();
    }
}

if(!function_exists('homey_get_header_menu_align')) {
	function homey_get_header_menu_align() {

		$css_class = homey_option('header_menu_align');
        if(homey_is_splash()) {
            $css_class = homey_option('splash_menu_align');
        }
		return $css_class;
	}
}

if(!function_exists('homey_header_menu_align')) {
    function homey_header_menu_align() {
        echo homey_get_header_menu_align();
    }
}

if(!function_exists('reservation_detail_link')) {
    function reservation_detail_link($resv_id) {
        $resversation_page = homey_get_template_link_dash('template/dashboard-reservations.php');
        $link = add_query_arg( 'reservation_detail', $resv_id, $resversation_page );

        return $link;
    }
}

if(!function_exists('homey_get_array_to_comma_string')) {
    function homey_get_array_to_comma_string($data_array) {
        
        if($data_array != '') {
            $result = implode(', ', $data_array);
            if(!empty($result)) {
                return $result;
            }
        }
        return '';
    }
}

if(!function_exists('homey_array_to_comma_string')) {
    function homey_array_to_comma_string($data_array) {
        
        echo homey_get_array_to_comma_string($data_array);
    }
}

if(!function_exists('homey_traverse_comma_string')) {
    function homey_traverse_comma_string($string) {
        if(!empty($string)) {
            $string_array = explode(',', $string);
            
            if(!empty($string_array[0])) {
                return $string_array;
            }
        }
        return '';
    }
}

if(!function_exists('homey_show_google_reCaptcha')) {
    function homey_show_google_reCaptcha() {
        $enable_reCaptcha = homey_option('enable_reCaptcha');
        $recaptha_site_key = homey_option('recaptha_site_key');
        $recaptha_secret_key = homey_option('recaptha_secret_key');

        if( $enable_reCaptcha != 0 && !empty($recaptha_site_key) && !empty($recaptha_secret_key) ) {
            return true;
        }
        return false;

    }
}


/* --------------------------------------------------------------------------
 * Remove spaces and chars from string
 ---------------------------------------------------------------------------*/
if ( ! function_exists( 'homey_clean' ) ) {
    function homey_clean($string)
    {
        $string = preg_replace('/&#36;/', '', $string);
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
        $string = preg_replace('/\D/', '', $string);
        return $string;
    }
}

/* --------------------------------------------------------------------------
 * Get term
 ---------------------------------------------------------------------------*/
if ( ! function_exists( 'homey_taxonomy_simple' ) ) {
    function homey_taxonomy_simple( $tax_name )
    {
        $terms = wp_get_post_terms( get_the_ID(), $tax_name, array("fields" => "names"));
        $t = '';
        if (!empty($terms)):
            foreach( $terms as $term ):
                $t .= $term.', ';
            endforeach;
            $trimed = rtrim ( $t, ', ' );
            return $trimed;
        endif;
        return '';
    }
}

if ( ! function_exists( 'homey_taxonomy_simple_by_ID' ) ) {
    function homey_taxonomy_simple_by_ID( $tax_name, $propID )
    {
        $terms = wp_get_post_terms( $propID, $tax_name, array("fields" => "names"));
        $t = '';
        if (!empty($terms)):
            foreach( $terms as $term ):
                $t .= $term.', ';
            endforeach;
            $trimed = rtrim ( $t, ', ' );
            return $trimed;
        endif;
        return '';
    }
}

if ( ! function_exists( 'homey_get_taxonomy_id' ) ) {
    function homey_get_taxonomy_id( $tax_name )
    {
        $terms = wp_get_post_terms( get_the_ID(), $tax_name, array("fields" => "ids"));
        $term_id = '';
        if (!empty($terms)):
            foreach( $terms as $term ):
                $term_id = $term;
            endforeach;
            return $term_id;
        endif;
        return '';
    }
}

if ( ! function_exists( 'homey_get_taxonomy' ) ) {
    function homey_get_taxonomy($tax_name)
    {
        $terms = wp_get_post_terms( get_the_ID(), $tax_name, array("fields" => "all"));
        if (!empty($terms)):
            foreach ($terms as $term):
                $term_link = get_term_link($term, $tax_name);
                if (is_wp_error($term_link))
                    continue;
                $taxonomy = '<a href="' . esc_url( $term_link ) . '">' . esc_attr( $term->name ) . '</a>&nbsp';
                return $taxonomy;
            endforeach;
        endif;
        return '';
    }
}


if ( ! function_exists( 'homey_get_listing_tax_id' ) ) {
    function homey_get_listing_tax_id($listing_id, $tax_name) {
        $terms = wp_get_post_terms( $listing_id, $tax_name, array("fields" => "all"));
        if (!empty($terms)):
            foreach ($terms as $term):
                $taxonomy = $term->term_id;
                return $taxonomy;
            endforeach;
        endif;
        return '';
    }
}


/*-----------------------------------------------------------------------------------*/
// Get template link
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_get_template_link') ) {
    function homey_get_template_link($template) {
        $args = array(
            'meta_key' => '_wp_page_template',
            'meta_value' => $template
        );
        $pages = get_pages($args);
        if( $pages ) {
            $add_link = get_permalink( $pages[0]->ID );
        } else {
            $add_link = home_url('/');
        }
        return $add_link;
    }
}

if( !function_exists('homey_get_template_link_2') ) {
    function homey_get_template_link_2($template) {
        global $wpdb;
        $page_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT post_id FROM $wpdb->postmeta 
            INNER JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->postmeta.post_id
            WHERE meta_key = '_wp_page_template' AND meta_value = %s 
            AND $wpdb->posts.post_type = 'page' AND $wpdb->posts.post_status = 'publish'
            LIMIT 1",
            $template
        ) );

        if( $page_id ) {
            $add_link = get_permalink( $page_id );
        } else {
            $add_link = '';
        }
        return $add_link;
    }
}

/*-----------------------------------------------------------------------------------*/
// Get template link
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_get_template_link_dash') ) {
    function homey_get_template_link_dash($template) {
        $args = array(
            'meta_key' => '_wp_page_template',
            'meta_value' => $template
        );
        $pages = get_pages($args);
        if( $pages ) {
            $add_link = get_permalink( $pages[0]->ID );
        } else {
            $add_link = '';
        }
        return $add_link;
    }
}

if(!function_exists('homey_get_search_result_page')) {
    function homey_get_search_result_page() {
        $link = homey_get_template_link('template/template-search.php');
        return $link;
    }
}

/* --------------------------------------------------------------------------
 * Get sidebar meta with default values
 ---------------------------------------------------------------------------*/
if ( !function_exists( 'homey_get_sidebar_meta' ) ):
    function homey_get_sidebar_meta( $post_id, $field = false ) {

        $defaults = array(
            'homey_sidebar' => 'no',
            'sidebar_position' => 'right',
            'selected_sidebar' => 'default-sidebar',
        );

        $meta = get_post_meta( $post_id, '_homey_sidebar_meta', true );
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


if( !function_exists('homey_get_terms')) {
	function homey_get_terms($tax_name, $orderby = 'name', $order = 'ASC', $hide_empty = false, $parent = 0 ) {
		
		if(taxonomy_exists($tax_name)) {
			$tax_data = get_terms (
	            array($tax_name),
	            array(
	                'orderby' => $orderby,
	                'order' => $order,
	                'hide_empty' => $hide_empty,
	                'parent' => $parent
	            )
	        );
	        return $tax_data;
		}
        return '';
	}

}

/*-----------------------------------------------------------------------------------*/
// Get taxonomy by listing id and taxonomy name
/*-----------------------------------------------------------------------------------*/
if(!function_exists('homey_get_taxonomy_title')){
    function homey_get_taxonomy_title( $listing_id, $taxonomy_name ){

        $tax_terms = get_the_terms( $listing_id, $taxonomy_name );
        $tax_name = '';
        if( !empty($tax_terms) ){
            foreach( $tax_terms as $tax_term ){
                $tax_name = $tax_term->name;
                break;
            }
        }
        return $tax_name;
    }
}

if ( !function_exists( 'homey_get_listing_area_meta' ) ):
    function homey_get_listing_area_meta( $term_id = false, $field = false ) {
        $defaults = array(
            'parent_city' => ''
        );

        if ( $term_id ) {
            $meta = get_option( '_homey_listing_area_'.$term_id );
            $meta = wp_parse_args( (array) $meta, $defaults );
        } else {
            $meta = $defaults;
        }

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

if( !function_exists('homey_get_all_cities') ):
    function homey_get_all_cities( $selected = '' ) {
        $taxonomy       =   'listing_city';
        $args = array(
            'hide_empty'    => false
        );
        $tax_terms      =   get_terms($taxonomy,$args);
        $select_city    =   '';

        foreach ($tax_terms as $tax_term) {
            $select_city.= '<option value="' . $tax_term->slug.'" ';
            if($tax_term->slug == $selected){
                $select_city.= ' selected="selected" ';
            }
            $select_city.= ' >' . $tax_term->name . '</option>';
        }
        return $select_city;
    }
endif;

if ( !function_exists( 'homey_get_listing_city_meta' ) ):
    function homey_get_listing_city_meta( $term_id = false, $field = false ) {
        $defaults = array(
            'parent_state' => ''
        );

        if ( $term_id ) {
            $meta = get_option( '_homey_listing_city_'.$term_id );
            $meta = wp_parse_args( (array) $meta, $defaults );
        } else {
            $meta = $defaults;
        }

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

if ( !function_exists( 'homey_get_listing_state_meta' ) ):
    function homey_get_listing_state_meta( $term_id = false, $field = false ) {
        $defaults = array(
            'parent_country' => ''
        );

        if ( $term_id ) {
            $meta = get_option( '_homey_listing_state_'.$term_id );
            $meta = wp_parse_args( (array) $meta, $defaults );
        } else {
            $meta = $defaults;
        }

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

if( !function_exists('homey_get_all_states') ):
    function homey_get_all_states( $selected = '' ) {
        $taxonomy       =   'listing_state';
        $args = array(
            'hide_empty'    => false
        );
        $tax_terms      =   get_terms($taxonomy,$args);
        $select_state    =   '';

        foreach ($tax_terms as $tax_term) {
            $select_state.= '<option value="' . $tax_term->slug.'" ';
            if($tax_term->slug == $selected){
                $select_state.= ' selected="selected" ';
            }
            $select_state.= ' >' . $tax_term->name . '</option>';
        }
        return $select_state;
    }
endif;

if ( !function_exists( 'homey_get_listing_type_meta' ) ):
    function homey_get_listing_type_meta( $term_id = false, $field = false ) {
        $defaults = array(
            'color_type' => 'inherit',
            'color' => '#ffffff',
            'ppp' => ''
        );

        if ( $term_id ) {
            $meta = get_option( '_homey_listing_type_'.$term_id );
            $meta = wp_parse_args( (array) $meta, $defaults );
        } else {
            $meta = $defaults;
        }

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

if ( !function_exists( 'homey_update_recent_colors' ) ):
    function homey_update_recent_colors( $color, $num_col = 10 ) {
        if ( empty( $color ) )
            return false;

        $current = get_option( 'homey_recent_colors' );
        if ( empty( $current ) ) {
            $current = array();
        }

        $update = false;

        if ( !in_array( $color, $current ) ) {
            $current[] = $color;
            if ( count( $current ) > $num_col ) {
                $current = array_slice( $current, ( count( $current ) - $num_col ), ( count( $current ) - 1 ) );
            }
            $update = true;
        }

        if ( $update ) {
            update_option( 'homey_recent_colors', $current );
        }

    }
endif;

if ( !function_exists( 'homey_update_listing_type_colors' ) ):
    function homey_update_listing_type_colors( $cat_id, $color, $type ) {

        $colors = (array)get_option( 'homey_type_colors' );

        if ( array_key_exists( $cat_id, $colors ) ) {

            if ( $type == 'inherit' ) {
                unset( $colors[$cat_id] );
            } elseif ( $colors[$cat_id] != $color ) {
                $colors[$cat_id] = $color;
            }

        } else {

            if ( $type != 'inherit' ) {
                $colors[$cat_id] = $color;
            }
        }

        update_option( 'homey_listing_type_colors', $colors );

    }
endif;

if( !function_exists('homey_get_all_countries') ):
    function homey_get_all_countries( $selected = '' ) {
        $taxonomy  = 'listing_country';
        $args = array(
            'hide_empty'  => false
        );
        $tax_terms      =   get_terms($taxonomy,$args);
        $select_country    =   '';

        foreach ($tax_terms as $tax_term) {
            $select_country.= '<option value="' . $tax_term->slug.'" ';
            if($tax_term->slug == $selected){
                $select_country.= ' selected="selected" ';
            }
            $select_country.= ' >' . $tax_term->name . '</option>';
        }
        return $select_country;
    }
endif;

/**
 *   ----------------------------------------------------------------------
 *   Homey Pagination
 *   ----------------------------------------------------------------------
 */
if( !function_exists( 'homey_pagination' ) ){
    function homey_pagination($pages = '', $range = 2 ) {
        global $paged;

        if(empty($paged))$paged = 1;

        $prev = $paged - 1;
        $next = $paged + 1;
        $showitems = ( $range * 2 )+1;
        $range = 2; // change it to show more links

        if( $pages == '' ){
            global $wp_query;
            $pages = $wp_query->max_num_pages;
            if( !$pages ){
                $pages = 1;
            }
        }

        if( 1 != $pages ){

            echo '<nav aria-label="Page navigation">';
            echo '<ul class="pagination">';
            echo ''.( $paged > 2 && $paged > $range+1 && $showitems < $pages ) ? '<li><a data-homeypagi="1" rel="First" href="'.get_pagenum_link(1).'"><span aria-hidden="true"><i class="fa fa-angle-double-left"></i></span></a></li>' : '';
            
            echo ''.( $paged > 1 ) ? '<li><a data-homeypagi="'.$prev.'" rel="Prev" href="'.get_pagenum_link($prev).'"><span aria-hidden="true"><i class="fa fa-angle-left"></i></span></a></li>' : '<li class="disabled"><a aria-label="Previous"><span aria-hidden="true"><i class="fa fa-angle-left"></i></span></a></li>';

            for ( $i = 1; $i <= $pages; $i++ ) {
                if ( 1 != $pages &&( !( $i >= $paged+$range+1 || $i <= $paged-$range-1 ) || $pages <= $showitems ) )
                {
                    if ( $paged == $i ){
                        echo '<li class="active"><a data-homeypagi="'.$i.'" href="'.get_pagenum_link($i).'">'.$i.' </a></li>';
                    } else {
                        echo '<li><a data-homeypagi="'.$i.'" href="'.get_pagenum_link($i).'">'.$i.'</a></li>';
                    }
                }
            }
            echo ''.( $paged < $pages ) ? '<li><a data-homeypagi="'.$next.'" rel="Next" href="'.get_pagenum_link($next).'"><span aria-hidden="true"><i class="fa fa-angle-right"></i></span></a></li>' : '';

            echo ''.( $paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages ) ? '<li><a data-homeypagi="'.$pages.'" rel="Last" href="'.get_pagenum_link( $pages ).'"><span aria-hidden="true"><i class="fa fa-angle-double-right"></i></span></a></li>' : '';
            echo '</ul>';
            echo '</nav>';

        }
    }
}

if( !function_exists( 'homey_pagination_halfmap' ) ){
    function homey_pagination_halfmap($pages = '', $paged = '', $range = 2 ) {

        if(empty($paged))$paged = 1;

        $prev = $paged - 1;
        $next = $paged + 1;
        $showitems = ( $range * 2 )+1;
        $range = 2; // change it to show more links

        if( $pages == '' ){
            global $wp_query;
            $pages = $wp_query->max_num_pages;
            if( !$pages ){
                $pages = 1;
            }
        }

        if( 1 != $pages ){

            echo '<nav class="half_map_ajax_pagi" aria-label="Page navigation">';
            echo '<ul class="pagination">';
            echo ''.( $paged > 2 && $paged > $range+1 && $showitems < $pages ) ? '<li><a data-homeypagi="1" rel="First" href="'.get_pagenum_link(1).'"><span aria-hidden="true"><i class="fa fa-angle-double-left"></i></span></a></li>' : '';
            
            echo ''.( $paged > 1 ) ? '<li><a data-homeypagi="'.$prev.'" rel="Prev" href="#"><span aria-hidden="true"><i class="fa fa-angle-left"></i></span></a></li>' : '<li class="disabled"><a aria-label="Previous"><span aria-hidden="true"><i class="fa fa-angle-left"></i></span></a></li>';

            for ( $i = 1; $i <= $pages; $i++ ) {
                if ( 1 != $pages &&( !( $i >= $paged+$range+1 || $i <= $paged-$range-1 ) || $pages <= $showitems ) )
                {
                    if ( $paged == $i ){
                        echo '<li class="active"><a data-homeypagi="'.$i.'" href="#">'.$i.' </a></li>';
                    } else {
                        echo '<li><a data-homeypagi="'.$i.'" href="#">'.$i.'</a></li>';
                    }
                }
            }
            echo ''.( $paged < $pages ) ? '<li><a data-homeypagi="'.$next.'" rel="Next" href="'.get_pagenum_link($next).'"><span aria-hidden="true"><i class="fa fa-angle-right"></i></span></a></li>' : '';

            echo ''.( $paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages ) ? '<li><a data-homeypagi="'.$pages.'" rel="Last" href="#"><span aria-hidden="true"><i class="fa fa-angle-double-right"></i></span></a></li>' : '';
            echo '</ul>';
            echo '</nav>';

        }
    }
}


/*-----------------------------------------------------------------------------------*/
// get taxonomies with with id value
/*-----------------------------------------------------------------------------------*/
if(!function_exists('homey_get_taxonomies_with_id_value')){
    function homey_get_taxonomies_with_id_value($taxonomy, $parent_taxonomy, $taxonomy_id, $prefix = " " ){

        if (!empty($parent_taxonomy)) {
            foreach ($parent_taxonomy as $term) {
                if ($taxonomy_id != $term->term_id) {
                    echo '<option value="' . $term->term_id . '">' . $prefix . $term->name . '</option>';
                } else {
                    echo '<option value="' . $term->term_id . '" selected="selected">' . $prefix . $term->name . '</option>';
                }
                $get_child_terms = get_terms($taxonomy, array(
                    'hide_empty' => false,
                    'parent' => $term->term_id
                ));

                if (!empty($get_child_terms)) {
                    homey_get_taxonomies_with_id_value( $taxonomy, $get_child_terms, $taxonomy_id, "- ".$prefix );
                }
            }
        }
    }
}

/*-----------------------------------------------------------------------------------*/
// Listing Edit Form Hierarchichal Taxonomy Options
/*-----------------------------------------------------------------------------------*/
if(!function_exists('homey_get_taxonomies_for_edit_listing')){
    function homey_get_taxonomies_for_edit_listing( $listing_id, $taxonomy ){

        $taxonomy_id = '';
        $taxonomy_terms = get_the_terms( $listing_id, $taxonomy );

        if( !empty($taxonomy_terms) ){
            foreach( $taxonomy_terms as $term ){
                $taxonomy_id = $term->term_id;
                break;
            }
        }


        $taxonomy_id = intval($taxonomy_id);
        if( !empty($taxonomy_id)) {
            echo '<option value="-1">'.esc_html__( 'None', 'homey').'</option>';
        } else {
            echo '<option value="-1" selected="selected">'.esc_html__( 'None', 'homey').'</option>';
        }
        $parent_taxonomy = get_terms(
            array(
                $taxonomy
            ),
            array(
                'orderby'       => 'name',
                'order'         => 'ASC',
                'hide_empty'    => false,
                'parent' => 0
            )
        );
        homey_get_taxonomies_with_id_value( $taxonomy, $parent_taxonomy, $taxonomy_id );

    }
}

/*-----------------------------------------------------------------------------------*/
// Featured image place holder
/*-----------------------------------------------------------------------------------*/
if( !function_exists('homey_get_image_placeholder')){
    function homey_get_image_placeholder( $featured_image_size ){

        global $_wp_additional_image_sizes;
        $title_img_text = get_bloginfo('name');
        $feat_img_width = 0;
        $feat_img_height = 0;

        if ( in_array( $featured_image_size , array( 'thumbnail', 'medium', 'large' ) ) ) {

            $feat_img_width = get_option( $featured_image_size . '_size_w' );
            $feat_img_height = get_option( $featured_image_size . '_size_h' );

        } elseif ( isset( $_wp_additional_image_sizes[ $featured_image_size ] ) ) {

            $feat_img_width = $_wp_additional_image_sizes[ $featured_image_size ]['width'];
            $feat_img_height = $_wp_additional_image_sizes[ $featured_image_size ]['height'];

        }

        if( intval( $feat_img_width ) > 0 && intval( $feat_img_height ) > 0 ) {
            return '<img src="https://place-hold.it/' . esc_attr($feat_img_width) . 'x' . esc_attr($feat_img_height) . '&text=' . urlencode( $title_img_text ) . '" alt="'.esc_attr__('placeholder', 'homey').'" />';
        }

        return '';
    }
}

if( !function_exists('homey_get_image_placeholder_url')){
    function homey_get_image_placeholder_url( $image_size ){

        global $_wp_additional_image_sizes;
        $img_width = 0;
        $img_height = 0;
        $img_text = get_bloginfo('name');

        if ( in_array( $image_size , array( 'thumbnail', 'medium', 'large' ) ) ) {

            $img_width = get_option( $image_size . '_size_w' );
            $img_height = get_option( $image_size . '_size_h' );

        } elseif ( isset( $_wp_additional_image_sizes[ $image_size ] ) ) {

            $img_width = $_wp_additional_image_sizes[ $image_size ]['width'];
            $img_height = $_wp_additional_image_sizes[ $image_size ]['height'];

        }

        if( intval( $img_width ) > 0 && intval( $img_height ) > 0 ) {
            return 'https://place-hold.it/' . esc_attr($img_width) . 'x' . esc_attr($img_height) . '&text=' . urlencode( $img_text ) . '';
        }

        return '';
    }
}

if( !function_exists( 'homey_image_placeholder' ) ) {
    function homey_image_placeholder( $image_size ) {
        echo homey_get_image_placeholder( $image_size );
    }
}

if(!function_exists('homey_traverse_comma_string')) {
    function homey_traverse_comma_string($string) {
        $string_array = explode(',', $string);
        
        if(!empty($string_array[0])) {
            return $string_array;
        }
        return '';
    }
}


/**
 *   ----------------------------------------------------------------------
 *   Generates a category tree
 *   ----------------------------------------------------------------------
 */
if ( ! function_exists( 'homey_get_category_id_array' ) ) {
    function homey_get_category_id_array($add_all_category = true) {

        if (is_admin() === false) {
            return;
        }

        $categories = get_categories(array(
            'hide_empty' => 0
        ));

        $homey_category_id_array_walker = new homey_category_id_array_walker;
        $homey_category_id_array_walker->walk($categories, 4);

        if ($add_all_category === true) {
            $categories_buffer['- All categories -'] = '';
            return array_merge(
                $categories_buffer,
                $homey_category_id_array_walker->homey_array_buffer
            );
        } else {
            return $homey_category_id_array_walker->homey_array_buffer;
        }
    }
}

class homey_category_id_array_walker extends Walker {
    var $tree_type = 'category';
    var $db_fields = array ('parent' => 'parent', 'id' => 'term_id');

    var $homey_array_buffer = array();

    function start_lvl( &$output, $depth = 0, $args = array() ) {
    }

    function end_lvl( &$output, $depth = 0, $args = array() ) {
    }


    function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
        $this->homey_array_buffer[str_repeat(' - ', $depth) .  $category->name] = $category->term_id;
    }


    function end_el( &$output, $page, $depth = 0, $args = array() ) {
    }

}

/**
 *   -------------------------------------------------------------------------------------
 *   Get Taxonomy Slug Array
 *   -------------------------------------------------------------------------------------
 */
if ( ! function_exists( 'homey_get_taxonomies_slug_array' ) ) {
    function homey_get_taxonomies_slug_array($tax_name, $all_text, $add_all = true) {

        if (is_admin() === false) {
            return;
        }

        $types = get_categories(array(
            'hide_empty' => 0,
            'taxonomy'   => $tax_name,
        ));

        $homey_get_taxonomies_slug_array_walker = new homey_get_taxonomies_slug_array_walker;
        $homey_get_taxonomies_slug_array_walker->walk($types, 4);

        if ($add_all === true) {
            $types_buffer[$all_text] = '';
            return array_merge(
                $types_buffer,
                $homey_get_taxonomies_slug_array_walker->homey_array_buffer
            );
        } else {
            return $homey_get_taxonomies_slug_array_walker->homey_array_buffer;
        }
    }
}

class homey_get_taxonomies_slug_array_walker extends Walker_Nav_Menu {
    
    var $homey_array_buffer = array();

    function start_lvl( &$output, $depth = 0, $args = array() ) {
    }

    function end_lvl( &$output, $depth = 0, $args = array() ) {
    }


    function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
        $this->homey_array_buffer[str_repeat(' - ', $depth) .  $category->name] = $category->slug;
    }


    function end_el( &$output, $page, $depth = 0, $args = array() ) {
    }

}

/* --------------------------------------------------------------------------
 * Comment Walker
 ---------------------------------------------------------------------------*/
if ( ! function_exists( 'homey_comments_callback' ) ) {
    function homey_comments_callback( $comment, $args, $depth ) {
        $GLOBALS['comment'] = $comment;

        $allowed_html_array = array(
            'i' => array(
                'class' => array()
            )
        );
        
        $comment_author = homey_get_author_by_id('70', '70', 'img-responsive img-circle', $comment->user_id);
        ?>

        <li <?php comment_class('comment'); ?> id="comment-<?php comment_ID(); ?>">
            <div class="block-body">
                <div class="media">
                    <?php if(get_avatar( $comment, 60 )){ ?>
                    <div class="media-left">
                        <a class="media-object">
                            <?php echo get_avatar( $comment, 60 ); ?>
                        </a>
                    </div>
                    <?php } ?>
                    <div class="media-body">
                        <strong><?php comment_author(); ?></strong>
                        <div class="message-date">
                            <i class="fa fa-calendar"></i> <?php echo get_comment_date(); ?> <i class="fa fa-clock-o"></i> <?php echo get_comment_time(); ?>
                        </div>
                        <p><?php comment_text(); ?></p>

                        <?php edit_comment_link( esc_html__( 'Edit', 'homey' ), ' ' ); ?>
                        <?php comment_reply_link( array_merge( $args, array( 'reply_text' => wp_kses(__( 'Reply <i class="fa fa-angle-right"></i>', 'homey' ), $allowed_html_array ), 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
                        
                    </div>
                </div>
            </div>
        </li>

        <?php
    }
}

if( !function_exists('homey_countries_list') ) {
    function homey_countries_list() {
        $Countries = array(
            'US' => esc_html__('United States', 'homey'),
            'CA' => esc_html__('Canada', 'homey'),
            'AU' => esc_html__('Australia', 'homey'),
            'FR' => esc_html__('France', 'homey'),
            'DE' => esc_html__('Germany', 'homey'),
            'IS' => esc_html__('Iceland', 'homey'),
            'IE' => esc_html__('Ireland', 'homey'),
            'IT' => esc_html__('Italy', 'homey'),
            'ES' => esc_html__('Spain', 'homey'),
            'SE' => esc_html__('Sweden', 'homey'),
            'AT' => esc_html__('Austria', 'homey'),
            'BE' => esc_html__('Belgium', 'homey'),
            'FI' => esc_html__('Finland', 'homey'),
            'CZ' => esc_html__('Czech Republic', 'homey'),
            'DK' => esc_html__('Denmark', 'homey'),
            'NO' => esc_html__('Norway', 'homey'),
            'GB' => esc_html__('United Kingdom', 'homey'),
            'CH' => esc_html__('Switzerland', 'homey'),
            'NZ' => esc_html__('New Zealand', 'homey'),
            'RU' => esc_html__('Russian Federation', 'homey'),
            'PT' => esc_html__('Portugal', 'homey'),
            'NL' => esc_html__('Netherlands', 'homey'),
            'IM' => esc_html__('Isle of Man', 'homey'),
            'AF' => esc_html__('Afghanistan', 'homey'),
            'AX' => esc_html__('Aland Islands ', 'homey'),
            'AL' => esc_html__('Albania', 'homey'),
            'DZ' => esc_html__('Algeria', 'homey'),
            'AS' => esc_html__('American Samoa', 'homey'),
            'AD' => esc_html__('Andorra', 'homey'),
            'AO' => esc_html__('Angola', 'homey'),
            'AI' => esc_html__('Anguilla', 'homey'),
            'AQ' => esc_html__('Antarctica', 'homey'),
            'AG' => esc_html__('Antigua and Barbuda', 'homey'),
            'AR' => esc_html__('Argentina', 'homey'),
            'AM' => esc_html__('Armenia', 'homey'),
            'AW' => esc_html__('Aruba', 'homey'),
            'AZ' => esc_html__('Azerbaijan', 'homey'),
            'BS' => esc_html__('Bahamas', 'homey'),
            'BH' => esc_html__('Bahrain', 'homey'),
            'BD' => esc_html__('Bangladesh', 'homey'),
            'BB' => esc_html__('Barbados', 'homey'),
            'BY' => esc_html__('Belarus', 'homey'),
            'BZ' => esc_html__('Belize', 'homey'),
            'BJ' => esc_html__('Benin', 'homey'),
            'BM' => esc_html__('Bermuda', 'homey'),
            'BT' => esc_html__('Bhutan', 'homey'),
            'BO' => esc_html__('Bolivia, Plurinational State of', 'homey'),
            'BQ' => esc_html__('Bonaire, Sint Eustatius and Saba', 'homey'),
            'BA' => esc_html__('Bosnia and Herzegovina', 'homey'),
            'BW' => esc_html__('Botswana', 'homey'),
            'BV' => esc_html__('Bouvet Island', 'homey'),
            'BR' => esc_html__('Brazil', 'homey'),
            'IO' => esc_html__('British Indian Ocean Territory', 'homey'),
            'BN' => esc_html__('Brunei Darussalam', 'homey'),
            'BG' => esc_html__('Bulgaria', 'homey'),
            'BF' => esc_html__('Burkina Faso', 'homey'),
            'BI' => esc_html__('Burundi', 'homey'),
            'KH' => esc_html__('Cambodia', 'homey'),
            'CM' => esc_html__('Cameroon', 'homey'),
            'CV' => esc_html__('Cape Verde', 'homey'),
            'KY' => esc_html__('Cayman Islands', 'homey'),
            'CF' => esc_html__('Central African Republic', 'homey'),
            'TD' => esc_html__('Chad', 'homey'),
            'CL' => esc_html__('Chile', 'homey'),
            'CN' => esc_html__('China', 'homey'),
            'CX' => esc_html__('Christmas Island', 'homey'),
            'CC' => esc_html__('Cocos (Keeling) Islands', 'homey'),
            'CO' => esc_html__('Colombia', 'homey'),
            'KM' => esc_html__('Comoros', 'homey'),
            'CG' => esc_html__('Congo', 'homey'),
            'CD' => esc_html__('Congo, the Democratic Republic of the', 'homey'),
            'CK' => esc_html__('Cook Islands', 'homey'),
            'CR' => esc_html__('Costa Rica', 'homey'),
            'CI' => esc_html__("Cote d'Ivoire", 'homey'),
            'HR' => esc_html__('Croatia', 'homey'),
            'CU' => esc_html__('Cuba', 'homey'),
            'CW' => esc_html__('Curacao', 'homey'),
            'CY' => esc_html__('Cyprus', 'homey'),
            'DJ' => esc_html__('Djibouti', 'homey'),
            'DM' => esc_html__('Dominica', 'homey'),
            'DO' => esc_html__('Dominican Republic', 'homey'),
            'EC' => esc_html__('Ecuador', 'homey'),
            'EG' => esc_html__('Egypt', 'homey'),
            'SV' => esc_html__('El Salvador', 'homey'),
            'GQ' => esc_html__('Equatorial Guinea', 'homey'),
            'ER' => esc_html__('Eritrea', 'homey'),
            'EE' => esc_html__('Estonia', 'homey'),
            'ET' => esc_html__('Ethiopia', 'homey'),
            'FK' => esc_html__('Falkland Islands (Malvinas)', 'homey'),
            'FO' => esc_html__('Faroe Islands', 'homey'),
            'FJ' => esc_html__('Fiji', 'homey'),
            'GF' => esc_html__('French Guiana', 'homey'),
            'PF' => esc_html__('French Polynesia', 'homey'),
            'TF' => esc_html__('French Southern Territories', 'homey'),
            'GA' => esc_html__('Gabon', 'homey'),
            'GM' => esc_html__('Gambia', 'homey'),
            'GE' => esc_html__('Georgia', 'homey'),
            'GH' => esc_html__('Ghana', 'homey'),
            'GI' => esc_html__('Gibraltar', 'homey'),
            'GR' => esc_html__('Greece', 'homey'),
            'GL' => esc_html__('Greenland', 'homey'),
            'GD' => esc_html__('Grenada', 'homey'),
            'GP' => esc_html__('Guadeloupe', 'homey'),
            'GU' => esc_html__('Guam', 'homey'),
            'GT' => esc_html__('Guatemala', 'homey'),
            'GG' => esc_html__('Guernsey', 'homey'),
            'GN' => esc_html__('Guinea', 'homey'),
            'GW' => esc_html__('Guinea-Bissau', 'homey'),
            'GY' => esc_html__('Guyana', 'homey'),
            'HT' => esc_html__('Haiti', 'homey'),
            'HM' => esc_html__('Heard Island and McDonald Islands', 'homey'),
            'VA' => esc_html__('Holy See (Vatican City State)', 'homey'),
            'HN' => esc_html__('Honduras', 'homey'),
            'HK' => esc_html__('Hong Kong', 'homey'),
            'HU' => esc_html__('Hungary', 'homey'),
            'IN' => esc_html__('India', 'homey'),
            'ID' => esc_html__('Indonesia', 'homey'),
            'IR' => esc_html__('Iran, Islamic Republic of', 'homey'),
            'IQ' => esc_html__('Iraq', 'homey'),
            'IL' => esc_html__('Israel', 'homey'),
            'JM' => esc_html__('Jamaica', 'homey'),
            'JP' => esc_html__('Japan', 'homey'),
            'JE' => esc_html__('Jersey', 'homey'),
            'JO' => esc_html__('Jordan', 'homey'),
            'KZ' => esc_html__('Kazakhstan', 'homey'),
            'KE' => esc_html__('Kenya', 'homey'),
            'KI' => esc_html__('Kiribati', 'homey'),
            'KP' => esc_html__('Korea, Democratic People\'s Republic of', 'homey'),
            'KR' => esc_html__('Korea, Republic of', 'homey'),
            'KV' => esc_html__('kosovo', 'homey'),
            'KW' => esc_html__('Kuwait', 'homey'),
            'KG' => esc_html__('Kyrgyzstan', 'homey'),
            'LA' => esc_html__('Lao People\'s Democratic Republic', 'homey'),
            'LV' => esc_html__('Latvia', 'homey'),
            'LB' => esc_html__('Lebanon', 'homey'),
            'LS' => esc_html__('Lesotho', 'homey'),
            'LR' => esc_html__('Liberia', 'homey'),
            'LY' => esc_html__('Libyan Arab Jamahiriya', 'homey'),
            'LI' => esc_html__('Liechtenstein', 'homey'),
            'LT' => esc_html__('Lithuania', 'homey'),
            'LU' => esc_html__('Luxembourg', 'homey'),
            'MO' => esc_html__('Macao', 'homey'),
            'MK' => esc_html__('Macedonia', 'homey'),
            'MG' => esc_html__('Madagascar', 'homey'),
            'MW' => esc_html__('Malawi', 'homey'),
            'MY' => esc_html__('Malaysia', 'homey'),
            'MV' => esc_html__('Maldives', 'homey'),
            'ML' => esc_html__('Mali', 'homey'),
            'MT' => esc_html__('Malta', 'homey'),
            'MH' => esc_html__('Marshall Islands', 'homey'),
            'MQ' => esc_html__('Martinique', 'homey'),
            'MR' => esc_html__('Mauritania', 'homey'),
            'MU' => esc_html__('Mauritius', 'homey'),
            'YT' => esc_html__('Mayotte', 'homey'),
            'MX' => esc_html__('Mexico', 'homey'),
            'FM' => esc_html__('Micronesia, Federated States of', 'homey'),
            'MD' => esc_html__('Moldova, Republic of', 'homey'),
            'MC' => esc_html__('Monaco', 'homey'),
            'MN' => esc_html__('Mongolia', 'homey'),
            'ME' => esc_html__('Montenegro', 'homey'),
            'MS' => esc_html__('Montserrat', 'homey'),
            'MA' => esc_html__('Morocco', 'homey'),
            'MZ' => esc_html__('Mozambique', 'homey'),
            'MM' => esc_html__('Myanmar', 'homey'),
            'NA' => esc_html__('Namibia', 'homey'),
            'NR' => esc_html__('Nauru', 'homey'),
            'NP' => esc_html__('Nepal', 'homey'),
            'NC' => esc_html__('New Caledonia', 'homey'),
            'NI' => esc_html__('Nicaragua', 'homey'),
            'NE' => esc_html__('Niger', 'homey'),
            'NG' => esc_html__('Nigeria', 'homey'),
            'NU' => esc_html__('Niue', 'homey'),
            'NF' => esc_html__('Norfolk Island', 'homey'),
            'MP' => esc_html__('Northern Mariana Islands', 'homey'),
            'OM' => esc_html__('Oman', 'homey'),
            'PK' => esc_html__('Pakistan', 'homey'),
            'PW' => esc_html__('Palau', 'homey'),
            'PS' => esc_html__('Palestinian Territory, Occupied', 'homey'),
            'PA' => esc_html__('Panama', 'homey'),
            'PG' => esc_html__('Papua New Guinea', 'homey'),
            'PY' => esc_html__('Paraguay', 'homey'),
            'PE' => esc_html__('Peru', 'homey'),
            'PH' => esc_html__('Philippines', 'homey'),
            'PN' => esc_html__('Pitcairn', 'homey'),
            'PL' => esc_html__('Poland', 'homey'),
            'PR' => esc_html__('Puerto Rico', 'homey'),
            'QA' => esc_html__('Qatar', 'homey'),
            'RE' => esc_html__('Reunion', 'homey'),
            'RO' => esc_html__('Romania', 'homey'),
            'RW' => esc_html__('Rwanda', 'homey'),
            'BL' => esc_html__('Saint Barthelemy', 'homey'),
            'SH' => esc_html__('Saint Helena', 'homey'),
            'KN' => esc_html__('Saint Kitts and Nevis', 'homey'),
            'LC' => esc_html__('Saint Lucia', 'homey'),
            'MF' => esc_html__('Saint Martin (French part)', 'homey'),
            'PM' => esc_html__('Saint Pierre and Miquelon', 'homey'),
            'VC' => esc_html__('Saint Vincent and the Grenadines', 'homey'),
            'WS' => esc_html__('Samoa', 'homey'),
            'SM' => esc_html__('San Marino', 'homey'),
            'ST' => esc_html__('Sao Tome and Principe', 'homey'),
            'SA' => esc_html__('Saudi Arabia', 'homey'),
            'SN' => esc_html__('Senegal', 'homey'),
            'RS' => esc_html__('Serbia', 'homey'),
            'SC' => esc_html__('Seychelles', 'homey'),
            'SL' => esc_html__('Sierra Leone', 'homey'),
            'SG' => esc_html__('Singapore', 'homey'),
            'SX' => esc_html__('Sint Maarten (Dutch part)', 'homey'),
            'SK' => esc_html__('Slovakia', 'homey'),
            'SI' => esc_html__('Slovenia', 'homey'),
            'SB' => esc_html__('Solomon Islands', 'homey'),
            'SO' => esc_html__('Somalia', 'homey'),
            'ZA' => esc_html__('South Africa', 'homey'),
            'GS' => esc_html__('South Georgia and the South Sandwich Islands', 'homey'),
            'LK' => esc_html__('Sri Lanka', 'homey'),
            'SD' => esc_html__('Sudan', 'homey'),
            'SR' => esc_html__('Suriname', 'homey'),
            'SJ' => esc_html__('Svalbard and Jan Mayen', 'homey'),
            'SZ' => esc_html__('Swaziland', 'homey'),
            'SY' => esc_html__('Syrian Arab Republic', 'homey'),
            'TW' => esc_html__('Taiwan, Province of China', 'homey'),
            'TJ' => esc_html__('Tajikistan', 'homey'),
            'TZ' => esc_html__('Tanzania, United Republic of', 'homey'),
            'TH' => esc_html__('Thailand', 'homey'),
            'TL' => esc_html__('Timor-Leste', 'homey'),
            'TG' => esc_html__('Togo', 'homey'),
            'TK' => esc_html__('Tokelau', 'homey'),
            'TO' => esc_html__('Tonga', 'homey'),
            'TT' => esc_html__('Trinidad and Tobago', 'homey'),
            'TN' => esc_html__('Tunisia', 'homey'),
            'TR' => esc_html__('Turkey', 'homey'),
            'TM' => esc_html__('Turkmenistan', 'homey'),
            'TC' => esc_html__('Turks and Caicos Islands', 'homey'),
            'TV' => esc_html__('Tuvalu', 'homey'),
            'UG' => esc_html__('Uganda', 'homey'),
            'UA' => esc_html__('Ukraine', 'homey'),
            'UAE' => esc_html__('United Arab Emirates', 'homey'),
            'UM' => esc_html__('United States Minor Outlying Islands', 'homey'),
            'UY' => esc_html__('Uruguay', 'homey'),
            'UZ' => esc_html__('Uzbekistan', 'homey'),
            'VU' => esc_html__('Vanuatu', 'homey'),
            'VE' => esc_html__('Venezuela, Bolivarian Republic of', 'homey'),
            'VN' => esc_html__('Viet Nam', 'homey'),
            'VG' => esc_html__('Virgin Islands, British', 'homey'),
            'VI' => esc_html__('Virgin Islands, U.S.', 'homey'),
            'WF' => esc_html__('Wallis and Futuna', 'homey'),
            'EH' => esc_html__('Western Sahara', 'homey'),
            'YE' => esc_html__('Yemen', 'homey'),
            'ZM' => esc_html__('Zambia', 'homey'),
            'ZW' => esc_html__('Zimbabwe', 'homey')
        );
        return $Countries;
    }
}

/* --------------------------------------------------------------------------
 * Breadcrumb from http://dimox.net/wordpress-breadcrumbs-without-a-plugin/
 ---------------------------------------------------------------------------*/
if ( ! function_exists( 'homey_breadcrumbs' ) ) {
    function homey_breadcrumbs($options = array())
    {

        global $post;
        $allowed_html_array = array(
            'i' => array(
                'class' => array()
            )
        );

        $text['home']     = esc_html__('Home', 'homey'); // text for the 'Home' link
        $text['category'] = esc_html__('%s', 'homey'); // text for a category page
        $text['tax']      = esc_html__('%s', 'homey'); // text for a taxonomy page
        $text['search']   = esc_html__('Search Results for "%s" Query', 'homey'); // text for a search results page
        $text['tag']      = esc_html__('%s', 'homey'); // text for a tag page
        $text['author']   = esc_html__('%s', 'homey'); // text for an author page
        $text['404']      = esc_html__('Error 404', 'homey'); // text for the 404 page

        $defaults = array(
            'show_current' => 1, // 1 - show current post/page title in breadcrumbs, 0 - don't show
            'show_on_home' => 0, // 1 - show breadcrumbs on the homepage, 0 - don't show
            'delimiter' => '',
            'before' => '<li class="active">',
            'after' => '</li>',

            'home_before' => '',
            'home_after' => '',
            'home_link' => home_url('/'),

            'link_before' => '<li>',
            'link_after'  => '</li>',
            'link_attr'   => '',
            'link_in_before' => '',
            'link_in_after'  => ''
        );

        extract($defaults);

        $link = '<a href="%1$s"><span>' . $link_in_before . '%2$s' . $link_in_after . '</span></a>';

        // form whole link option
        $link = $link_before . $link . $link_after;

        if (isset($options['text'])) {
            $options['text'] = array_merge($text, (array) $options['text']);
        }

        // override defaults
        extract($options);

        // regex replacement
        $replace = $link_before . '<a' . esc_attr( $link_attr ) . '\\1>' . $link_in_before . '\\2' . $link_in_after . '</a>' . $link_after;

        /*
         * Use bbPress's breadcrumbs when available
         */
        if (function_exists('bbp_breadcrumb') && is_bbpress()) {

            $bbp_crumbs =
                bbp_get_breadcrumb(array(
                    'home_text' => $text['home'],
                    'sep' => '',
                    'sep_before' => '',
                    'sep_after'  => '',
                    'pad_sep' => 0,
                    'before' => $home_before,
                    'after' => $home_after,
                    'current_before' => $before,
                    'current_after'  => $after,
                ));

            if ($bbp_crumbs) {
                echo '<ul class="breadcrumb favethemes_bbpress_breadcrumb">' .$bbp_crumbs. '</ul>';
                return;
            }
        }

        // normal breadcrumbs
        if ((is_home() || is_front_page())) {

            if ($show_on_home == 1) {
                echo '<li>'. esc_attr( $home_before ) . '<a href="' . esc_url( $home_link ) . '">' . esc_attr( $text['home'] ) . '</a>'. esc_attr( $home_after ) .'</li>';
            }

        } else {

            echo '<ol class="breadcrumb">' .$home_before . sprintf($link, $home_link, $text['home']) . $home_after . $delimiter;

            if (is_category() || is_tax())
            {
                $term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );

                if( $term ) {

                    $taxonomy_object = get_taxonomy( get_query_var( 'taxonomy' ) );
                    //echo '<li><a>'.$taxonomy_object->rewrite['slug'].'</a></li>';

                    $parent = $term->parent;

                    while ($parent):
                        $parents[] = $parent;
                        $new_parent = get_term_by( 'id', $parent, get_query_var( 'taxonomy' ));
                        $parent = $new_parent->parent;
                    endwhile;
                    if(!empty($parents)):
                        $parents = array_reverse($parents);

                        // For each parent, create a breadcrumb item
                        foreach ($parents as $parent):
                            $item = get_term_by( 'id', $parent, get_query_var( 'taxonomy' ));

                            $term_link = get_term_link( $item );
                            if ( is_wp_error( $term_link ) ) {
                                continue;
                            }
                            echo '<li><a href="'.esc_url($term_link).'">'.esc_attr($item->name).'</a></li>';
                        endforeach;
                    endif;

                    // Display the current term in the breadcrumb
                    echo '<li>'.esc_attr($term->name).'</li>';

                } else {

                    $the_cat = get_category(get_query_var('cat'), false);

                    if(!empty($the_cat)) {
                        // have parents?
                        if ($the_cat->parent != 0) {

                            $cats = get_category_parents($the_cat->parent, true, $delimiter);
                            echo preg_replace('#<a([^>]+)>([^<]+)</a>#', $replace, $cats);
                        }
                    }

                    // print category
                    echo ''.$before . sprintf((is_category() ? $text['category'] : $text['tax']), single_cat_title('', false)) . $after;
                } // end terms else

            }
            else if (is_search()) {

                echo ''.$before . sprintf($text['search'], get_search_query()) . $after;

            }
            else if (is_day()) {

                echo  sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $delimiter
                    . sprintf($link, get_month_link(get_the_time('Y'),get_the_time('m')), get_the_time('F')) . $delimiter
                    . $before . get_the_time('d') . $after;

            }
            else if (is_month()) {

                echo  sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $delimiter
                    . $before . get_the_time('F') . $after;

            }
            else if (is_year()) {

                echo ''.$before . get_the_time('Y') . $after;

            }
            // single post or page
            else if (is_single() && !is_attachment()) {

                // custom post type
                if (get_post_type() != 'post' && get_post_type() != 'listing' ) {

                    $post_type = get_post_type_object(get_post_type());
                    //printf($link, get_post_type_archive_link(get_post_type()), $post_type->labels->name);

                    if ($show_current == 1) {
                        echo esc_attr($delimiter) . $before . get_the_title() . $after;
                    }
                }
                elseif( get_post_type() == 'listing' ){

                    $terms = get_the_terms( get_the_ID(), 'listing_type' );
                    if( !empty($terms) ) {
                        foreach ($terms as $term) {
                            $term_link = get_term_link($term);
                            // If there was an error, continue to the next term.
                            if (is_wp_error($term_link)) {
                                continue;
                            }
                            echo '<li><a href="' . esc_url($term_link) . '"> <span>' . esc_attr( $term->name ). '</span></a></li>';
                        }
                    }

                    if ($show_current == 1) {
                        echo esc_attr($delimiter) . $before . get_the_title() . $after;
                    }
                }
                else {

                    $cat = get_the_category();
                    $cats = get_category_parents($cat[0], true, esc_attr($delimiter));

                    if ($show_current == 0) {
                        $cats = preg_replace("#^(.+)esc_attr($delimiter)$#", "$1", $cats);
                    }

                    $cats = preg_replace('#<a([^>]+)>([^<]+)</a>#', $replace, $cats);

                    echo ''.$cats;

                    if ($show_current == 1) {
                        echo ''.$before . get_the_title() . $after;
                    }
                } // end else

            }
            elseif (!is_single() && !is_page() && get_post_type() != 'post' && !is_404() && !is_author() ) {

                $post_type = get_post_type_object(get_post_type());

                echo ''.$before . get_the_title() . $after;

            }
            elseif (is_attachment()) {

                $parent = get_post($post->post_parent);
                $cat = current(get_the_category($parent->ID));
                $cats = get_category_parents($cat, true, esc_attr($delimiter));

                if (!is_wp_error($cats)) {
                    $cats = preg_replace('#<a([^>]+)>([^<]+)</a>#', $replace, $cats);
                    echo ''.$cats;
                }

                printf($link, get_permalink($parent), $parent->post_title);

                if ($show_current == 1) {
                    echo esc_attr($delimiter) . $before . get_the_title() . $after;
                }

            }
            elseif (is_page() && !$post->post_parent && $show_current == 1) { 

                echo ''.$before . get_the_title() . $after;

            }
            elseif (is_page() && $post->post_parent) {

                $parent_id  = $post->post_parent;
                $breadcrumbs = array();

                while ($parent_id) {
                    $page = get_post($parent_id);
                    $breadcrumbs[] = sprintf($link, get_permalink($page->ID), get_the_title($page->ID));
                    $parent_id  = $page->post_parent;
                }

                $breadcrumbs = array_reverse($breadcrumbs);

                for ($i = 0; $i < count($breadcrumbs); $i++) {

                    echo ''.( $breadcrumbs[$i] );

                    if ($i != count($breadcrumbs)-1) {
                        echo esc_attr($delimiter);
                    }
                }

                if ($show_current == 1) {
                    echo esc_attr($delimiter) . $before . get_the_title() . $after;
                }

            }
            elseif (is_tag()) {
                echo ''.$before . sprintf($text['tag'], single_tag_title('', false)) . $after;

            }
            elseif (is_author()) {

                global $author;

                $userdata = get_userdata($author);
                echo ''.$before . sprintf($text['author'], $userdata->display_name) . $after;

            }
            elseif (is_404()) {
                echo ''.$before . esc_attr( $text['404'] ). $after;
            }

            // have pages?
            if (get_query_var('paged')) {

                if (is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author()) {
                    echo ' (' . esc_html__('Page', 'homey') . ' ' . get_query_var('paged') . ')';
                }
            }

            echo '</ol>';
        }

    } // breadcrumbs()
}

/*-----------------------------------------------------------------------------------*/
// Generate Hirarchical terms
/*-----------------------------------------------------------------------------------*/
if(!function_exists('homey_hirarchical_options')){
    function homey_hirarchical_options($taxonomy_name, $taxonomy_terms, $searched_term, $prefix = " " ){

        if (!empty($taxonomy_terms) && taxonomy_exists($taxonomy_name)) {
            foreach ($taxonomy_terms as $term) {

                if( $taxonomy_name == 'property_area' ) {
                    $term_meta= get_option( "_homey_property_area_$term->term_id");
                    $parent_city = sanitize_title($term_meta['parent_city']);

                    if ($searched_term == $term->slug) {
                        echo '<option data-tokens="'.$term->description.' '.str_replace('-', ' ', $term->slug).' '. strtoupper($prefix . $term->name) .' '.$prefix . $term->name.'" data-parentcity="'.urldecode($parent_city).'" value="' . urldecode($term->slug) . '" selected="selected">' . $prefix . $term->name . '</option>';
                    } else {
                        echo '<option  data-tokens="'.$term->description.' '.str_replace('-', ' ', $term->slug).' '. strtoupper($prefix . $term->name) .' '.$prefix . $term->name.'" data-parentcity="'.urldecode($parent_city).'" value="' . urldecode($term->slug) . '">' . $prefix . $term->name .'</option>';
                    }
                } elseif( $taxonomy_name == 'property_city' ) {
                    $term_meta= get_option( "_homey_property_city_$term->term_id");
                    $parent_state = sanitize_title($term_meta['parent_state']);

                    if ($searched_term == $term->slug) {
                        echo '<option  data-tokens="'.$term->description.' '.str_replace('-', ' ', $term->slug).' '. strtoupper($prefix . $term->name) .' '.$prefix . $term->name.'" data-parentstate="'.urldecode($parent_state).'" value="' . urldecode($term->slug) . '" selected="selected">' . $prefix . $term->name . '</option>';
                    } else {
                        echo '<option  data-tokens="'.$term->description.' '.str_replace('-', ' ', $term->slug).' '. strtoupper($prefix . $term->name) .' '.$prefix . $term->name.'" data-parentstate="'.urldecode($parent_state).'" value="' . urldecode($term->slug) . '">' . $prefix . $term->name .'</option>';
                    }
                }  elseif( $taxonomy_name == 'property_state' ) {
                    $term_meta= get_option( "_homey_property_state_$term->term_id");
                    $parent_country = sanitize_title($term_meta['parent_country']);

                    if ($searched_term == $term->slug) {
                        echo '<option data-tokens="'.$term->description.' '.str_replace('-', ' ', $term->slug).' '. strtoupper($prefix . $term->name) .' '.$prefix . $term->name.'" data-parentcountry="'.$parent_country.'" value="' . urldecode($term->slug) . '" selected="selected">' . $prefix . $term->name . '</option>';
                    } else {
                        echo '<option data-tokens="'.$term->description.' '.str_replace('-', ' ', $term->slug).' '. strtoupper($prefix . $term->name) .' '.$prefix . $term->name.'" data-parentcountry="'.$parent_country.'" value="' . urldecode($term->slug) . '">' . $prefix . $term->name .'</option>';
                    }
                } else {
                    if ($searched_term == $term->slug) {
                        echo '<option data-tokens="'.$term->description.' '.str_replace('-', ' ', $term->slug).' '. strtoupper($prefix . $term->name) .' '.$prefix . $term->name.'" value="' . urldecode($term->slug) . '" selected="selected">' . $prefix . $term->name . '</option>';
                    } else {
                        echo '<option data-tokens="'.$term->description.' '.str_replace('-', ' ', $term->slug).' '. strtoupper($prefix . $term->name) .' '.$prefix . $term->name.'" value="' . urldecode($term->slug) . '">' . $prefix . $term->name . '</option>';
                    }
                }


                $child_terms = get_terms($taxonomy_name, array(
                    'hide_empty' => false,
                    'parent' => $term->term_id
                ));

                if (!empty($child_terms)) {
                    homey_hirarchical_options( $taxonomy_name, $child_terms, $searched_term, "- ".$prefix );
                }
            }
        }
    }
}

// Filter to fix the Post Author Dropdown
if( !function_exists('homey_author_override')) {
    function homey_author_override($output)
    {
        global $post, $user_ID;

        // return if this isn't the theme author override dropdown
        if (!preg_match('/post_author_override/', $output)) return $output;

        // return if we've already replaced the list (end recursion)
        if (preg_match('/post_author_override_replaced/', $output)) return $output;

        // replacement call to wp_dropdown_users
        $output = wp_dropdown_users(array(
            'echo' => 0,
            'name' => 'post_author_override_replaced',
            'selected' => empty($post->ID) ? $user_ID : $post->post_author,
            'include_selected' => true
        ));

        // put the original name back
        $output = preg_replace('/post_author_override_replaced/', 'post_author_override', $output);

        return $output;
    }
}
add_filter('wp_dropdown_users', 'homey_author_override');

if(!function_exists('homey_edit_listing_active')) {
    function homey_edit_listing_active () {
        $edit_listing_id = intval( trim( $_GET['edit_listing'] ) );

        $cal_class = $info_class = $pricing_class = $media_class = $features_class = $location_class = $bedrooms_class = $services_class = $rules_class = '';
        if(!empty($edit_listing_id)) {
            if(isset($_GET['tab']) && $_GET['tab'] == 'calendar') {
                $cal_class = 'active';

            } elseif(isset($_GET['tab']) && $_GET['tab'] == 'pricing') {
                $pricing_class = 'active';

            } elseif(isset($_GET['tab']) && $_GET['tab'] == 'media') {
                $media_class = 'active';

            } elseif(isset($_GET['tab']) && $_GET['tab'] == 'features') {
                $features_class = 'active';

            } elseif(isset($_GET['tab']) && $_GET['tab'] == 'location') {
                $location_class = 'active';

            } elseif(isset($_GET['tab']) && $_GET['tab'] == 'bedrooms') {
                $bedrooms_class = 'active';

            } elseif(isset($_GET['tab']) && $_GET['tab'] == 'services') {
                $services_class = 'active';

            } elseif(isset($_GET['tab']) && $_GET['tab'] == 'rules') {
                $rules_class = 'active';

            } else {
                $info_class = 'active';
            }
        }
    }
}

if(!function_exists('homey_custom_pagination')) {
    function homey_custom_pagination($perpage, $totalres) {
        if(isset($_GET['page-num']) & !empty($_GET['page-num'])){
            $curpage = $_GET['page-num'];
        }else{
            $curpage = 1;
        }

        $start = ($curpage * $perpage) - $perpage;

        $endpage = ceil($totalres/$perpage);
        $startpage = 1;
        $nextpage = $curpage + 1;
        $previouspage = $curpage - 1;

        if($curpage != $startpage){ ?>
        <li class="page-item">
          <a class="page-link" href="&page-num=<?php echo esc_attr($startpage) ?>" tabindex="-1" aria-label="Previous">
            <span aria-hidden="true">&laquo;</span>
            <span class="sr-only">First</span>
          </a>
        </li>
        <?php }

        if($curpage >= 2){ ?>
        <li class="page-item"><a class="page-link" href="&page-num=<?php echo esc_attr($previouspage); ?>"><?php echo esc_attr($previouspage); ?></a></li>
        <?php } ?>

        <li class="page-item active"><a class="page-link" href="&page-num=<?php echo esc_attr($curpage); ?>"><?php echo esc_attr($curpage); ?></a></li>
        
        <?php if($curpage != $endpage){ ?>
            <li class="page-item"><a class="page-link" href="&page-num=<?php echo esc_attr($nextpage); ?>"><?php echo esc_attr($nextpage); ?></a></li>
        <?php }

        if($curpage != $endpage){ ?>
        <li class="page-item">
          <a class="page-link" href="&page-num=<?php echo esc_attr($endpage); ?>" aria-label="Next">
            <span aria-hidden="true">&raquo;</span>
            <span class="sr-only">Last</span>
          </a>
        </li>
        <?php }

    }
}

if(!function_exists('homey_is_stripe_id_used')) {
    function homey_is_stripe_id_used($session_id)
    {
        global $wpdb;
        $tbl = $wpdb->prefix.'postmeta';
        $prepare_guery = $wpdb->prepare( "SELECT post_id FROM $tbl where meta_key ='hm_subscription_detail_session_id' and meta_value = '%s'", $session_id );

        $get_values = $wpdb->get_col( $prepare_guery );
        $isAlreadySubscribed = 0;
        if(isset($get_values[0])){
            $lastIndex = count($get_values)-1;
            $isAlreadySubscribed = (int) $get_values[$lastIndex];
        }
        return $isAlreadySubscribed;
    }
}

if(!function_exists('homey_is_paypal_id_used')) {
    function homey_is_paypal_id_used($session_id)
    {
        global $wpdb;
        $tbl = $wpdb->prefix . 'postmeta';
        $prepare_guery = $wpdb->prepare("SELECT post_id FROM $tbl where meta_key ='hm_subscription_detail_session_id' and meta_value = '%s'", $session_id);

        $get_values = $wpdb->get_col($prepare_guery);
        $isAlreadySubscribed = 0;
        if (isset($get_values[0])) {
            $lastIndex = count($get_values) - 1;
            $isAlreadySubscribed = (int)$get_values[$lastIndex];
        }
        return $isAlreadySubscribed;
    }
}

if(!function_exists('hm_subscription_detail')) {
    function hm_subscription_detail($subscription_id)
    {
        global $wpdb;
        $tbl = $wpdb->prefix.'postmeta';
        $prepare_guery = $wpdb->prepare( "SELECT post_id FROM $tbl where meta_key ='hm_subscription_detail_sub_id' and meta_value = '%s'", $subscription_id );

        $get_values = $wpdb->get_col( $prepare_guery );
        $subscriptionInfo = '';
        $postID = -1;
        if(isset($get_values[0])){
            $lastIndex = count($get_values)-1;
            $postID = $get_values[$lastIndex];

            $subscriptionInfo = get_post($postID);
        }
        return $subscriptionInfo;
    }
}

if(!function_exists('homey_get_membership_detail')) {
    function homey_get_membership_detail($stripePlanId)
    {
        global $wpdb;
        $tbl = $wpdb->prefix.'postmeta';
        $prepare_guery = $wpdb->prepare( "SELECT post_id FROM $tbl where meta_key ='hm_settings_stripe_package_id' and meta_value = '%s'", $stripePlanId );

        $get_values = $wpdb->get_col( $prepare_guery );
        $membershipInfo = '';
        $postID = -1;
        if(isset($get_values[0])){
            $lastIndex = count($get_values)-1;
            $postID = $get_values[$lastIndex];

            $membershipInfo = get_post($postID);
        }
        return $membershipInfo;
    }
}

    if(!function_exists('homey_get_user_subscription')) {
    function homey_get_user_subscription($limit_record=-1, $userID=null, $subscription_status=null)
    {
        global $wpdb;

        if($userID == null){
            $userID = get_current_user_id();
        }

        $limit_record = $limit_record != -1 && $limit_record > 0 ? ' LIMIT '. $limit_record :  '';
        $sql = "SELECT ID FROM $wpdb->posts WHERE post_author = $userID AND post_type='hm_subscriptions' ORDER BY post_date DESC ".$limit_record;

        $subscriptions = $wpdb->get_results($sql);
        $allInfo = array();
        foreach ($subscriptions as $key => $subscription){
            if($subscription_status != null){
                $current_membershipStatus = get_post_meta($subscription->ID, 'hm_subscription_detail_status', true);

                if(strtolower($subscription_status) != strtolower($current_membershipStatus)){
                    continue;
                }
            }

            $allInfo[$key]['subscriptionID'] = $subscriptionID = $subscription->ID;
            $allInfo[$key]['planID'] = explode('-', get_post_meta($subscriptionID, 'hm_subscription_detail_plan_id', true))[0];
        }

        return $allInfo;
    }
}

if(!function_exists('homey_get_all_user_subscription')) {
    function homey_get_all_user_subscription($limit_record=-1, $subscription_status=null)
    {
        global $wpdb;

        $limit_record = $limit_record != -1 && $limit_record > 0 ? ' LIMIT '. $limit_record :  '';
        $sql = "SELECT ID FROM $wpdb->posts WHERE post_type='hm_subscriptions' ORDER BY post_date DESC ".$limit_record;

        $subscriptions = $wpdb->get_results($sql);
        $allInfo = array();
        foreach ($subscriptions as $key => $subscription){
            if($subscription_status != null){
                $current_membershipStatus = get_post_meta($subscription->ID, 'hm_subscription_detail_status', true);

                if(strtolower($subscription_status) != strtolower($current_membershipStatus)){
                    continue;
                }
            }

            $allInfo[$key]['subscriptionID'] = $subscriptionID = $subscription->ID;
            $allInfo[$key]['planID'] = explode('-', get_post_meta($subscriptionID, 'hm_subscription_detail_plan_id', true))[0];
        }

        return $allInfo;
    }
}

if(!function_exists('custom_strtotime')){
    function custom_strtotime($date_to_normal){
        $homey_date_format = homey_option('homey_date_format');
        if($homey_date_format == 'yy-mm-dd') {
            $date_arr = explode("-", str_replace('.', '-',$date_to_normal));
            $return_normal_date = $date_arr[2].'-'.$date_arr[1].'-'.$date_arr[0];
            return mktime(null, null, null, $date_arr[1], $date_arr[2], $date_arr[0]);
        } elseif($homey_date_format == 'yy-dd-mm') {
            $date_arr = explode("-", str_replace('.', '-',$date_to_normal));
            $return_normal_date = $date_arr[1].'-'.$date_arr[2].'-'.$date_arr[0];
            return mktime(null, null, null, $date_arr[2], $date_arr[1], $date_arr[0]);
        } elseif($homey_date_format == 'mm-yy-dd') {
            $date_arr = explode("-", str_replace('.', '-',$date_to_normal));
            $return_normal_date = $date_arr[2].'-'.$date_arr[0].'-'.$date_arr[1];
            return mktime(null, null, null, $date_arr[0], $date_arr[2], $date_arr[1]);
        } elseif($homey_date_format == 'dd-yy-mm') {
            $date_arr = explode("-", str_replace('.', '-',$date_to_normal));
            $return_normal_date = $date_arr[0].'-'.$date_arr[2].'-'.$date_arr[1];
            return mktime(null, null, null, $date_arr[2], $date_arr[0], $date_arr[1]);
        } elseif($homey_date_format == 'mm-dd-yy') {
            $date_arr = explode("-", str_replace('.', '-',$date_to_normal));
            $return_normal_date = $date_arr[1].'-'.$date_arr[0].'-'.$date_arr[2];
            return mktime(null, null, null, $date_arr[0], $date_arr[1], $date_arr[2]);
        } elseif($homey_date_format == 'dd-mm-yy') {
            $date_arr = explode("-", str_replace('.', '-',$date_to_normal));
            $return_normal_date = $date_arr[0].'-'.$date_arr[1].'-'.$date_arr[2];
            return mktime(null, null, null, $date_arr[1], $date_arr[0], $date_arr[2]);
        } elseif($homey_date_format == 'dd.mm.yy') {
            $date_arr = explode(".", str_replace('-', '.',$date_to_normal));
            $return_normal_date = $date_arr[0].'.'.$date_arr[1].'.'.$date_arr[2];
            return mktime(null, null, null, $date_arr[1], $date_arr[0], $date_arr[2]);
        } else {
            $date_arr = explode(".", str_replace('-', '.',$date_to_normal));
            $return_normal_date = $date_arr[0].'.'.$date_arr[1].'.'.$date_arr[2];
            return mktime(null, null, null, $date_arr[1], $date_arr[0], $date_arr[2]);
        }        
        return strtotime($return_normal_date);
    }
}

if(!function_exists('hm_validity_check')) {
    function hm_validity_check()
    {
        $hm_options = get_option('hm_memberships_options');
        $free_no_listing = $hm_options['free_numOf_listings'];

        if($free_no_listing < 1 ){
            $memberships_url = homey_get_template_link('template/template-membership-webhook.php');
            if( !homey_is_admin() && in_array('homey-membership/homey-membership.php', apply_filters('active_plugins', get_option('active_plugins')))){
                $subscriptions = homey_get_user_subscription(1);

                if(count($subscriptions) < 1){
                    $varText = 'new-user';
                    $message_string = esc_html__( 'Your should select package of your choice to add listing.', 'homey' );
                    ?>
                    <script>
                        document.getElementById("section-body").innerHTML = "<p class='error text-danger'><?php echo $message_string; ?></p>";
                        document.getElementById("section-body").style.padding = "10% 2%";
                        document.addEventListener('contextmenu', function(e) {
                            e.preventDefault();
                        });

                        setInterval(function(){
                            window.location.href = '<?php echo $memberships_url.'?'.$varText.'=1'; ?>';
                        }, 5000);
                    </script>
                    <?php
                    exit;
                }

                foreach($subscriptions as $subscription){
                    $subscriptionID = $subscription['subscriptionID'];
                    $membershipStatus = get_post_meta($subscriptionID, 'hm_subscription_detail_status', true);

                    if($membershipStatus == 'expired'){
                        $msgText = ($membershipStatus == 'expired')?'expired':'exceeded';
                        $varText = ($membershipStatus == 'expired')?'membership-expired':'limit-exceeded';

                        $message_string = esc_html__( 'Your listing limit is '.$msgText.' you will redirect to plan selection page.', 'homey' );
                        ?>
                        <script>
                            document.getElementById("section-body").innerHTML = "<p class='error text-danger'><?php echo $message_string; ?></p>";
                            document.getElementById("section-body").style.padding = "10% 2%";
                            document.addEventListener('contextmenu', function(e) {
                                e.preventDefault();
                            });

                            setInterval(function(){
                                window.location.href = '<?php echo $memberships_url.'?'.$varText.'=1'; ?>';
                            }, 5000);
                        </script>
                        <?php
                        exit;
                    }
                }
            }
        }

    }
}

if(!function_exists('homey_check_membershop_status')) {
    function homey_check_membershop_status()
    {
        $memberships_url = homey_get_template_link('template/template-membership-webhook.php');
        if( ! homey_is_admin() && in_array('homey-membership/homey-membership.php', apply_filters('active_plugins', get_option('active_plugins')))){
            $subscriptions = homey_get_user_subscription(1);

            foreach($subscriptions as $subscription){
                $subscriptionID = $subscription['subscriptionID'];
                $membershipStatus = get_post_meta($subscriptionID, 'hm_subscription_detail_status', true);

                if($membershipStatus == 'expired'){
                    return false;
                } else {
                    return true;
                }
            }
            return false;
        }

        return false;
    }
}

if(!function_exists('hm_check_subscriptions_status')) {
    function hm_check_subscriptions_status($user_login, $user)
    {
        global $wpdb;
        if(in_array('homey-membership/homey-membership.php', apply_filters('active_plugins', get_option('active_plugins')))){
            $subscriptions = homey_get_user_subscription(false, $user->ID);
            foreach ($subscriptions as $subscription){
                $expiryDate = get_post_meta($subscription->ID, "hm_subscription_detail_expiry_date", true);

                $now = strtotime(date('d-m-Y H:i:s'));
                $valid = strtotime($expiryDate);

                if($now > $valid ){
                    update_post_meta($subscription->ID, "hm_subscription_detail_status", "expired");
                }
            }

        }//no worries because plugin is not in action
        return true;
    }

    add_action('wp_login', 'hm_check_subscriptions_status', 10, 2);
}

if(!function_exists('hm_listing_limit_check')) {
    function hm_listing_limit_check($userID, $listing_status = '')
    {
        $memberships_url = homey_get_template_link('template/template-membership-webhook.php');
        $upgrade_feature_listing_url = homey_get_template_link('template/template-membership-webhook.php');
        $membership_settings = get_option('hm_memberships_options');

        if(in_array('homey-membership/homey-membership.php', apply_filters('active_plugins', get_option('active_plugins')))){
            $allSubsListingsLimit = 0;
            $subscriptions = homey_get_user_subscription(1, $userID);
            foreach ($subscriptions as $k => $subscription){

                $sub_status = get_post_meta($subscription['subscriptionID'], "hm_subscription_detail_status", true);
                $unlimitedListings = get_post_meta($subscription['planID'], 'hm_settings_unlimited_listings', true);
                if($unlimitedListings == "on"){
                    return array(
                        'is_allowed_membership' => 1,
                        'total_allowed_featured_listing' => 'unlimited',
                        'current_number_listing' => 'unlimited',
                        'remaining_number_listing' => 'unlimited'

                    );
                }

                $allSubsListingsLimit += get_post_meta($subscription['planID'], 'hm_settings_listings_included', true);
            }

           $currentListing = homey_hm_user_listing_count($userID);
            if ('publish' == $listing_status){
                $currentListing -= 1; //make able user to edit the listing
            }

            if(!homey_is_admin() && $allSubsListingsLimit <= $currentListing && $membership_settings['free_numOf_listings'] <= $currentListing){
                $message_string = esc_html__( "You consumed all listing limit or you don't have any subscription, now you will redirect to plan selection page.", 'homey' );
                $varText = 'listing-limit-completed';
                ?>
                <script>
                    var message_html = '<p style="margin-top: 10%; margin-left:15%" class=" error text-danger"><?php echo $message_string; ?></p>';
                    document.getElementsByTagName("body")[0].innerHTML = message_html;

                    document.addEventListener('contextmenu', function(e) {
                        e.preventDefault();
                    });

                    setInterval(function(){
                        window.location.href = '<?php echo $memberships_url.'?'.$varText.'=1'; ?>';
                    }, 5000);
                </script>
                <?php
            }else{
                return array(
                    'is_allowed_membership' => 1,
                    'total_allowed_featured_listing' => $allSubsListingsLimit,
                    'current_number_listing' => $currentListing,
                    'remaining_number_listing' => $allSubsListingsLimit - $currentListing,

                );
            }
        }//no worries because plugin is not in action
        return true;
    }
}

if(!function_exists('hm_featured_limit_check')) {
    function hm_featured_limit_check()
    {
        global $wpdb;
        $memberships_url = homey_get_template_link('template/template-membership-webhook.php');
        $upgrade_feature_listing_url = homey_get_template_link('template/template-membership-webhook.php');
        if(in_array('homey-membership/homey-membership.php', apply_filters('active_plugins', get_option('active_plugins')))){
            $subscriptionInfo = homey_get_user_subscription();
            $subscriptionID = @$subscriptionInfo['subscriptionID'];
            $planID = @$subscriptionInfo['planID'];
            $featuredListingsLimit = get_post_meta($planID, 'hm_settings_featured_listings', true);
            $currentFeaturedListing = homey_featured_listing_count(get_current_user_id());

            if(!homey_is_admin() && $featuredListingsLimit <= $currentFeaturedListing){
                $message_string = esc_html__( 'You consumed all listing limit, now you will redirect to plan selection page.', 'homey' );
                $varText = 'feature-limit-completed';
                ?>
                <script>
                    document.getElementById("section-body").innerHTML = "<p class='error text-danger'><?php echo $message_string; ?></p>";
                    document.getElementById("section-body").style.padding = "10% 2%";

                    document.addEventListener('contextmenu', function(e) {
                        e.preventDefault();
                    });

                    setInterval(function(){
                        window.location.href = '<?php echo $memberships_url.'?'.$varText.'=1'; ?>';
                    }, 5000);
                </script>
                <?php
                exit;
            }else{
                return array(
                    'is_allowed_membership' => 1,
                    'total_allowed_featured_listing' => $featuredListingsLimit,
                    'current_number_listing' => $currentFeaturedListing
                );
            }
        }//no worries because plugin is not in action
        return true;
    }
}

if( !function_exists('favethemes_datediff') ) {
    /**
     * @param $interval
     * @param $datefrom
     * @param $dateto
     * @param bool $using_timestamps
     * @return false|float|int|string
     */
    function favethemes_datediff($interval, $datefrom, $dateto, $using_timestamps = false) {
        /*
        $interval can be:
        yyyy - Number of full years
        q    - Number of full quarters
        m    - Number of full months
        y    - Difference between day numbers
               (eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".)
        d    - Number of full days
        w    - Number of full weekdays
        ww   - Number of full weeks
        h    - Number of full hours
        n    - Number of full minutes
        s    - Number of full seconds (default)
        */

        if (!$using_timestamps) {
            $datefrom = strtotime($datefrom, 0);
            $dateto   = strtotime($dateto, 0);
        }

        $difference        = $dateto - $datefrom; // Difference in seconds
        $months_difference = 0;

        switch ($interval) {
            case 'yyyy': // Number of full years
                $years_difference = floor($difference / 31536000);
                if (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom), date("j", $datefrom), date("Y", $datefrom)+$years_difference) > $dateto) {
                    $years_difference--;
                }

                if (mktime(date("H", $dateto), date("i", $dateto), date("s", $dateto), date("n", $dateto), date("j", $dateto), date("Y", $dateto)-($years_difference+1)) > $datefrom) {
                    $years_difference++;
                }

                $datediff = $years_difference;
            break;

            case "q": // Number of full quarters
                $quarters_difference = floor($difference / 8035200);

                while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($quarters_difference*3), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
                    $months_difference++;
                }

                $quarters_difference--;
                $datediff = $quarters_difference;
            break;

            case "m": // Number of full months
                $months_difference = floor($difference / 2678400);

                while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
                    $months_difference++;
                }

                $months_difference--;

                $datediff = $months_difference;
            break;

            case 'y': // Difference between day numbers
                $datediff = date("z", $dateto) - date("z", $datefrom);
            break;

            case "d": // Number of full days
                $datediff = floor($difference / 86400);
            break;

            case "w": // Number of full weekdays
                $days_difference  = floor($difference / 86400);
                $weeks_difference = floor($days_difference / 7); // Complete weeks
                $first_day        = date("w", $datefrom);
                $days_remainder   = floor($days_difference % 7);
                $odd_days         = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?

                if ($odd_days > 7) { // Sunday
                    $days_remainder--;
                }

                if ($odd_days > 6) { // Saturday
                    $days_remainder--;
                }

                $datediff = ($weeks_difference * 5) + $days_remainder;
            break;

            case "ww": // Number of full weeks
                $datediff = floor($difference / 604800);
            break;

            case "h": // Number of full hours
                $datediff = floor($difference / 3600);
            break;

            case "n": // Number of full minutes
                $datediff = floor($difference / 60);
            break;

            default: // Number of full seconds (default)
                $datediff = $difference;
            break;
        }

        return $datediff;
    }
}

if(!function_exists("homey_convert_date")){
    function homey_convert_date($date_format){
        $replacers = ["d", "m", "Y"];
        $replaced = ["dd", "mm", "yy"];
        return str_replace($replaced, $replacers, $date_format);
    }
}

if(!function_exists("get_minimum_currency")){
    function get_minimum_currency(){
        $payment_currency = esc_html( homey_option('payment_currency', 'USD') );
        $currency_limit = array(
                'USD' =>	0.50,
                'AED' =>	2.00,
                'AUD' =>	0.50,
                'BGN' =>	1.00,
                'BRL' =>	0.50,
                'CAD' =>	0.50,
                'CHF' =>	0.50,
                'CZK' =>	15.00,
                'DKK' =>	2.50,
                'EUR' =>	0.50,
                'GBP' =>	0.30,
                'HKD' =>	4.00,
                'HUF' =>	175.00,
                'INR' =>	0.50,
                'JPY' =>	50,
                'MXN' =>	10,
                'MYR' =>	2,
                'NOK' =>	3.00,
                'NZD' =>	0.50,
                'PLN' =>	2.00,
                'RON' =>	2.00,
                'SEK' =>	3.00,
                'SGD' =>	0.50
        );

        return isset($currency_limit[$payment_currency])?$currency_limit[$payment_currency]:-1;
    }
}

if(!function_exists("clearance_membership_plan")){
    function clearance_membership_plan(){
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $user_email = $current_user->user_email;

        $already_subscriptions = homey_get_user_subscription(10, null, 'active');
        if(count($already_subscriptions) > 0){//already have package then expire them
            //to expire subscriptions
            foreach($already_subscriptions as $subscription){
                $subscriptionID = $subscription['subscriptionID'];
                update_post_meta($subscriptionID, 'hm_subscription_detail_status', 'draft');
            }

            //to expire listings because of change in package
            $args = array(
                'post_type'   => 'listing',
                'author'      => $user_id,
                'post_status' => 'any'
            );

            $query = new WP_Query( $args );
            global $post;
            while( $query->have_posts()){
                $query->the_post();

                $listing = array(
                    'ID'          => $post->ID,
                    'post_type'   => 'listing',
                    'post_status' => 'expired'
                );

                wp_update_post( $listing );
                update_post_meta( $post->ID, 'homey_featured', 0 );
            }
            wp_reset_postdata();

            $donwgrade_message  = esc_html__('Account Downgraded,','homey') . "\r\n\r\n";
            $donwgrade_message .= sprintf( esc_html__("Hello, You downgraded your subscription on  %s. Because you changed you subscription, we set the status of all your listings to \"expired\". You will need to choose which listings you want live and send them again for approval if nedeed. Thank you!",'homey'), get_option('blogname')) . "\r\n\r\n";

            homey_send_emails($user_email,
                sprintf(esc_html__('[%s] Account Downgraded','homey'), get_option('blogname')),
                $donwgrade_message);
        }
    }
}

if(!function_exists("clearance_membership_plan_cron_job")){
    function clearance_membership_plan_cron_job($is_hm_wc_package = false){
        $already_subscriptions = homey_get_all_user_subscription(-1, null, 'active');
        if(count($already_subscriptions) > 0){//already have package then expire them
            //to expire subscriptions
            foreach($already_subscriptions as $subscription){
                if($is_hm_wc_package > 0){
                    $subscriptionID = $subscription['subscriptionID'];
                        if(strtotime(get_post_meta($subscriptionID, 'hm_subscription_detail_expiry_date', true)) < strtotime('now')){
                            update_post_meta($subscriptionID, 'hm_subscription_detail_status', 'expired');
                        }
                }else{
                    $subscriptionID = $subscription['subscriptionID'];
                    update_post_meta($subscriptionID, 'hm_subscription_detail_status', 'expired');
                }
            }
            

            $donwgrade_message  = esc_html__('Account Downgraded,','homey') . "\r\n\r\n";
            $donwgrade_message .= sprintf( esc_html__("Hello, Your subscription on  %s. is expired, to renew visit %s Thank you!",'homey'), get_option('blogname'), get_option('blogname')) . "\r\n\r\n";

            if(isset($user_email)){
                homey_send_emails($user_email,
                            sprintf(esc_html__('[%s] Account Downgraded','homey'), get_option('blogname')),
                            $donwgrade_message);
            }
           
        }
    }
}

if(!function_exists('custom_strtotime')){
    function custom_strtotime($date_to_normal){
        $homey_date_format = homey_option('homey_date_format');
        $get_hour_min_seconds = explode(' ', $date_to_normal);
        $date_with_homey_format = date(get_homey_to_std_date_format(), strtotime($get_hour_min_seconds[0]));
        $get_hour_min_seconds = isset($get_hour_min_seconds[1]) ? explode(':', $get_hour_min_seconds[0]) : array(null, null, null);

        if($homey_date_format == 'yy-mm-dd') {
            $date_arr = explode("-", str_replace('.', '-',$date_with_homey_format));
            return mktime($get_hour_min_seconds[0], $get_hour_min_seconds[1], $get_hour_min_seconds[2], $date_arr[1], $date_arr[2], $date_arr[0]);

        } elseif($homey_date_format == 'yy-dd-mm') {
            $date_arr = explode("-", str_replace('.', '-',$date_with_homey_format));
            return mktime($get_hour_min_seconds[0], $get_hour_min_seconds[1], $get_hour_min_seconds[2], $date_arr[2], $date_arr[1], $date_arr[0]);

        } elseif($homey_date_format == 'mm-yy-dd') {
            $date_arr = explode("-", str_replace('.', '-',$date_with_homey_format));
            return mktime($get_hour_min_seconds[0], $get_hour_min_seconds[1], $get_hour_min_seconds[2], $date_arr[0], $date_arr[2], $date_arr[1]);

        } elseif($homey_date_format == 'dd-yy-mm') {
            $date_arr = explode("-", str_replace('.', '-',$date_with_homey_format));
            return mktime($get_hour_min_seconds[0], $get_hour_min_seconds[1], $get_hour_min_seconds[2], $date_arr[2], $date_arr[0], $date_arr[1]);

        } elseif($homey_date_format == 'mm-dd-yy') {
            $date_arr = explode("-", str_replace('.', '-',$date_with_homey_format));
            return mktime($get_hour_min_seconds[0], $get_hour_min_seconds[1], $get_hour_min_seconds[2], $date_arr[0], $date_arr[1], $date_arr[2]);

        } elseif($homey_date_format == 'dd-mm-yy') {
            $date_arr = explode("-", str_replace('.', '-',$date_with_homey_format));
            return mktime($get_hour_min_seconds[0], $get_hour_min_seconds[1], $get_hour_min_seconds[2], $date_arr[1], $date_arr[0], $date_arr[2]);

        } elseif($homey_date_format == 'dd.mm.yy') {
            $date_arr = explode(".", str_replace('-', '.',$date_with_homey_format));
            return mktime($get_hour_min_seconds[0], $get_hour_min_seconds[1], $get_hour_min_seconds[2], $date_arr[1], $date_arr[0], $date_arr[2]);

        } else {
            $date_arr = explode(".", str_replace('-', '.',$date_with_homey_format));
            return mktime($get_hour_min_seconds[0], $get_hour_min_seconds[1], $get_hour_min_seconds[2], $date_arr[1], $date_arr[0], $date_arr[2]);

        }

        return strtotime($date_to_normal);
    }
}

if(!function_exists("get_homey_to_std_date_format")){
    function get_homey_to_std_date_format(){
        $homey_date_format = homey_option('homey_date_format');

        if($homey_date_format == 'yy-mm-dd') {
            return "Y-m-d";

        } elseif($homey_date_format == 'yy-dd-mm') {
            return "Y-d-m";

        } elseif($homey_date_format == 'mm-yy-dd') {
            return "m-Y-d";

        } elseif($homey_date_format == 'dd-yy-mm') {
            return "d-Y-m";

        } elseif($homey_date_format == 'mm-dd-yy') {
            return "m-d-Y";

        } elseif($homey_date_format == 'dd-mm-yy') {
            return "d-m-Y";

        } elseif($homey_date_format == 'dd.mm.yy') {
            return "d.m.Y";

        } else {
            return "Y-m-d";
        }
    }
}

if(!function_exists("get_active_membership_plan")){
    function get_active_membership_plan($userID=null){
        if($userID == null) {
            $userID = get_current_user_id();
        }

        global $wpdb;
        $sql = "SELECT ID FROM $wpdb->posts WHERE post_author = $userID AND post_type='hm_subscriptions' ORDER BY post_date DESC LIMIT 1";

        $subscriptions = $wpdb->get_results($sql);

        $info = ['subscriptionDate' => '', 'subscriptionExpiryDate' => '', 'active_subscription' => '' ];
        if(isset($subscriptions[0]->ID)){
            $planId = get_post_meta($subscriptions[0]->ID, 'hm_subscription_detail_plan_id', true);

            $info['subscriptionDate'] = get_post_meta($subscriptions[0]->ID, 'hm_subscription_detail_purchase_date', true);
            $info['subscriptionExpiryDate'] = get_post_meta($subscriptions[0]->ID, 'hm_subscription_detail_expiry_date', true);
            $info['subscriptionObj'] = get_post($planId);
        }

        return $info;
    }
}

if(!function_exists("get_wc_order_id")){
    function get_wc_order_id($reservation_id=null){
        if($reservation_id == null) {
            return 0;
        }

        global $wpdb;
        $sql = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'wc_reservation_reference_id' AND meta_value = $reservation_id
        ORDER BY meta_id DESC LIMIT 1";

        $is_invoice_id = $wpdb->get_results($sql, ARRAY_A);
        $invoice_id = isset($is_invoice_id[0]['post_id']) ? $is_invoice_id[0]['post_id'] : 0;
        $wc_reference_order_id = 0;
        if($invoice_id > 0){
            $wc_reference_order_id = get_post_meta($invoice_id, 'wc_reference_order_id', true);
        }

        return $wc_reference_order_id > 0 ? $wc_reference_order_id : 0;
    }
}
