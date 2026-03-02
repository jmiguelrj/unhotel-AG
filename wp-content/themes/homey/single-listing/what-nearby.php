<?php
global $post, $homey_local;

$post_id = $post->ID;
$hide_yelp = 1;
$homey_yelp_api_key = homey_option('homey_yelp_api_key');

$allowed_html_array = array(
    'i' => array(
        'class' => array()
    ),
    'span' => array(
        'class' => array()
    ),
    'a' => array(
        'href' => array(),
        'title' => array(),
        'target' => array()
    )
);

$yelp_categories = array (
    'active' => array( 'name' => esc_html__( 'Active Life', 'homey' ), 'icon' => 'fa fa-bicycle' ),
    'arts' => array( 'name' => esc_html__( 'Arts & Entertainment', 'homey' ), 'icon' => 'fa fa-picture-o' ),
    'auto' => array( 'name' => esc_html__( 'Automotive', 'homey' ), 'icon' => 'fa fa-car' ),
    'beautysvc' => array( 'name' => esc_html__( 'Beauty & Spas', 'homey' ), 'icon' => 'fa fa-cutlery' ),
    'education' => array( 'name' => esc_html__( 'Education', 'homey' ), 'icon' => 'fa fa-graduation-cap' ),
    'eventservices' => array( 'name' => esc_html__( 'Event Planning & Services', 'homey' ), 'icon' => 'fa fa-birthday-cake' ),
    'financialservices' => array( 'name' => esc_html__( 'Financial Services', 'homey' ), 'icon' => 'fa fa-money' ),
    'food' => array( 'name' => esc_html__( 'Food', 'homey' ), 'icon' => 'fa fa-shopping-basket' ),
    'health' => array( 'name' => esc_html__( 'Health & Medical', 'homey' ), 'icon' => 'fa fa-medkit' ),
    'homeservices' => array( 'name' => esc_html__( 'Home Services ', 'homey' ), 'icon' => 'fa fa-wrench' ),
    'hotelstravel' => array( 'name' => esc_html__( 'Hotels & Travel', 'homey' ), 'icon' => 'fa fa-bed' ),
    'localflavor' => array( 'name' => esc_html__( 'Local Flavor', 'homey' ), 'icon' => 'fa fa-coffee' ),
    'localservices' => array( 'name' => esc_html__( 'Local Services', 'homey' ), 'icon' => 'fa fa-dot-circle-o' ),
    'massmedia' => array( 'name' => esc_html__( 'Mass Media', 'homey' ), 'icon' => 'fa fa-television' ),
    'nightlife' => array( 'name' => esc_html__( 'Nightlife', 'homey' ), 'icon' => 'fa fa-glass' ),
    'pets' => array( 'name' => esc_html__( 'Pets', 'homey' ), 'icon' => 'fa fa-paw' ),
    'professional' => array( 'name' => esc_html__( 'Professional Services', 'homey' ), 'icon' => 'fa fa-suitcase' ),
    'publicservicesgovt' => array( 'name' => esc_html__( 'Public Services & Government', 'homey' ), 'icon' => 'fa fa-university' ),
    'realestate' => array( 'name' => esc_html__( 'Real Estate', 'homey' ), 'icon' => 'fa fa-building-o' ),
    'religiousorgs' => array( 'name' => esc_html__( 'Religious Organizations', 'homey' ), 'icon' => 'fa fa-universal-access' ),
    'restaurants' => array( 'name' => esc_html__( 'Restaurants', 'homey' ), 'icon' => 'fa fa-cutlery' ),
    'shopping' => array( 'name' => esc_html__( 'Shopping', 'homey' ), 'icon' => 'fa fa-shopping-bag' ),
    'transport' =>  array( 'name' => esc_html__( 'Transportation', 'homey' ), 'icon' => 'fa fa-bus' )
);

$yelp_data = homey_option( 'homey_yelp_term' );
$yelp_dist_unit = homey_option( 'homey_yelp_dist_unit' );
$prop_location = get_post_meta( get_the_ID(), 'homey_listing_location', true );
$prop_location = explode( ',', $prop_location );
if(!isset($prop_location[1])){
    return false;
}
$prop_location = $prop_location[0].','.$prop_location[1];


$dist_unit = 1.1515;
$unit_text = 'mi';
if ( $yelp_dist_unit == 'kilometers' ) {
    $dist_unit = 1.609344;
    $unit_text = 'km';
}

if( $hide_yelp ) {
?>
<div id="nearby-section" class="nearby-section">
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div class="block-left">
                    <h3 class="title"><?php echo esc_attr(homey_option('sn_nearby_label')); ?></h3>
                </div>
                <!-- block-left -->
                <div class="block-right">
                    <div class="what-nearby">
                        <?php
                        $link = site_url('wp-admin/admin.php?page=homey_options&tab=26');
                        if( empty( $homey_yelp_api_key ) ) {
                            echo '<div class="yelp-cat-block">';
                            echo esc_html__('Please supply your API key', 'homey').' ';
                            echo '<a target="_blank" href="'.esc_url($link).'">'.esc_html__('Click Here', 'homey').'</a>';
                            echo '</div>';
                        } else {

                            foreach ( $yelp_data as $value ) :

                                $term_id = $value;
                                $term_name = $yelp_categories[ $term_id ]['name'];
                                $term_icon = $yelp_categories[ $term_id ]['icon'];
                                $response = yelp_query_api( $term_id, $prop_location );

                                if ( isset( $response->businesses ) ) {
                                    $businesses = $response->businesses;
                                } else {
                                    $businesses = array( $response );
                                }
                                $distance = false;
                                $current_lat = '';
                                $current_lng = '';

                                if ( isset( $response->region->center ) ) {

                                    $current_lat = $response->region->center->latitude;
                                    $current_lng = $response->region->center->longitude;
                                    $distance = true;

                                }

                                if ( sizeof( $businesses ) != 0 ) {

                                ?>
                                <dl>
                                    <dt><i class="<?php echo esc_attr($term_icon); ?>"></i> <?php echo esc_attr($term_name); ?></dt>
                                    
                                    <?php
                                    foreach ( $businesses as $data ) :

                                        $location_distance = '';

                                        if ( $distance && isset( $data->coordinates ) ) {

                                            $location_lat = $data->coordinates->latitude;
                                            $location_lng = $data->coordinates->longitude;
                                            $theta = $current_lng - $location_lng;
                                            $dist = sin( deg2rad( $current_lat ) ) * sin( deg2rad( $location_lat ) ) +  cos( deg2rad( $current_lat ) ) * cos( deg2rad( $location_lat ) ) * cos( deg2rad( $theta ) );
                                            $dist = acos( $dist );
                                            $dist = rad2deg( $dist );
                                            $miles = $dist * 60 * $dist_unit;

                                            $location_distance = '<span class="time-review"> (' . round( $miles, 2 ) . ' ' . $unit_text . ') </span>';

                                        }
                                        ?>
                                    
                                        <dd>
                                            <div class="what-nearby-left">
                                                <?php echo esc_attr($data->name); ?> <?php echo ''.($location_distance); ?>
                                            </div>
                                            <div class="what-nearby-right">
                                                <div class="rating-wrap">
                                                    <div class="rating-container">
                                                        <div class="rating">                                            
                                                            <?php echo homey_get_review_stars($data->rating, true, true, false); ?>
                                                            <span class="time-review"><?php echo esc_attr($data->review_count); ?> <?php esc_html_e('reviews', 'homey');?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </dd>

                                        <?php

                                    endforeach;

                            ?>
                                </dl>

                        <?php

                                } //sizeof( $businesses )

                            endforeach;
                        } //homey_yelp_api_key
                        ?>
                        
                    </div>
                    <?php if( !empty( $homey_yelp_api_key ) ) { ?>
                    <div class="nearby-logo"><?php echo esc_attr($homey_local['pwb_label']); ?> <i class="fa fa-yelp" aria-hidden="true"></i> <strong><?php echo esc_attr($homey_local['yelp_label']); ?></strong></div>
                    <?php } ?>
                </div>
                <!-- block-right -->
            </div>
            <!-- block-body -->
        </div>
        <!-- block-section -->
    </div>
    <!-- block -->
</div>
<?php } ?>