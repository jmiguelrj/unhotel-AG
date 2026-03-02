<?php
/**
 * Template Name: Compare
 */
get_header();
global $homey_local, $homey_prefix;
$ids = explode(',', $_COOKIE['homey_compare_listings']);

$basic_info_escaped = $list_night_price = $listing_title = $list_bedrooms = $list_guests = $list_beds = $list_baths = $list_type = $list_size = '';
$counter   =  0;
$listings  =  array();
$hide_labels = homey_option('show_hide_labels');
?>

<section class="main-content-area listing-page listing-page-full-width">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="page-title">
                    <div class="block-top-title">
                        <h1 class="listing-title"><?php echo esc_html__(the_title('', '', false), 'homey'); ?></h1>
                    </div><!-- block-top-title -->
                </div><!-- page-title -->
            </div><!-- col-xs-12 col-sm-12 col-md-12 col-lg-12 -->
        </div><!-- .row -->
    </div><!-- .container -->

    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <?php
                if( !empty($ids) ) {
                $args = array(
                    'post_type' => 'listing',
                    'post__in' => $ids,
                    'post_status' => 'publish',
                    'order' => 'ASC',
                    'orderby' => 'post__in'
                );

                //do the query
                $the_query = New WP_Query($args);
                if( $the_query->have_posts() ): while( $the_query->have_posts() ): $the_query->the_post();

                    if( homey_booking_type() == 'per_hour') {
                        $night_price = get_post_meta( get_the_ID(), $homey_prefix.'hour_price', true );
                    } else {
                        $night_price = get_post_meta( get_the_ID(), $homey_prefix.'night_price', true );
                    }
                    
                    $bedrooms     = get_post_meta( get_the_ID(), $homey_prefix.'listing_bedrooms', true );
                    $guests       = get_post_meta( get_the_ID(), $homey_prefix.'guests', true );
                    $beds         = get_post_meta( get_the_ID(), $homey_prefix.'beds', true );
                    $baths        = get_post_meta( get_the_ID(), $homey_prefix.'baths', true );
                    $size         = get_post_meta( get_the_ID(), $homey_prefix.'listing_size', true );
                    $size_unit    = get_post_meta( get_the_ID(), $homey_prefix.'listing_size_unit', true );
                    $listing_type = homey_taxonomy_simple('listing_type');

                    $basic_info_escaped .= '
                            <th><a href="'.esc_url(get_permalink()).'">'.get_the_post_thumbnail( get_the_id(), 'homey-listing-thumb', array( 'class' => 'img-responsive' ) ).'</a></th>';

                    
                    $listing_title .= '<td>' . get_the_title() . '</td>';
                    
                    if( !empty($night_price) ) {

                        if( homey_booking_type() == 'per_hour') {
                            $list_night_price .= '<td>'.homey_formatted_price($night_price, false, true).'/'.homey_option('glc_hour_label').'</td>';
                        } else {
                            $list_night_price .= '<td>'.homey_formatted_price($night_price, false, true).'/'.homey_option('glc_day_night_label').'</td>';
                        }
                        
                    } else {
                        $list_night_price .= '<td>---</td>';
                    }

                    if( !empty($bedrooms) ) {
                        $list_bedrooms .= '<td>'.$bedrooms.'</td>';
                    } else {
                        $list_bedrooms .= '<td>---</td>';
                    }

                    if( !empty($guests) ) {
                        $list_guests .= '<td>'.$guests.'</td>';
                    } else {
                        $list_guests .= '<td>---</td>';
                    }

                    if( !empty($beds) ) {
                        $list_beds .= '<td>'.$beds.'</td>';
                    } else {
                        $list_beds .= '<td>---</td>';
                    }

                    if( !empty($baths) ) {
                        $list_baths .= '<td>'.$baths.'</td>';
                    } else {
                        $list_baths .= '<td>---</td>';
                    }

                    if( !empty($size) ) {
                        $list_size .= '<td>'.$size.' '.$size_unit.'</td>';
                    } else {
                        $list_size .= '<td>---</td>';
                    }

                    if( !empty($listing_type) ) {
                        $list_type .= '<td>'.$listing_type.'</td>';
                    } else {
                        $list_type .= '<td>---</td>';
                    }

                    $counter++;

                endwhile; endif;
                ?>

                <div class="compare-table">
                    <table class="table-striped table-hover">
                        <thead>
                            <tr>
                                <th><!-- empty --></th>
                                <?php 
                                // This variable has been safely escaped in the same file: line 54
                                echo ''.$basic_info_escaped; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong><?php esc_html_e('Title', 'homey'); ?></strong></td>
                                <?php echo wp_kses($listing_title, homey_allowed_html()); ?>
                            </tr>
                            
                            <tr>
                                <td><strong><?php esc_html_e('Price', 'homey'); ?></strong></td>
                                <?php echo wp_kses($list_night_price, homey_allowed_html()); ?>
                            </tr>
                            
                            <?php if($hide_labels['sn_type_label'] != 1) { ?>
                            <tr>
                                <td><strong><?php echo homey_option('sn_type_label'); ?></strong></td>
                                <?php echo wp_kses($list_type, homey_allowed_html()); ?> 
                            </tr>
                            <?php } ?>
                            
                            <?php if($hide_labels['sn_size_label'] != 1) { ?>
                            <tr>
                                <td><strong><?php echo homey_option('sn_size_label'); ?></strong></td>
                                <?php echo wp_kses($list_size, homey_allowed_html()); ?>
                                
                            </tr>
                            <?php } ?>
                            

                            <?php if($hide_labels['sn_bedrooms_label'] != 1) { ?>
                            <tr>
                                <td><strong><?php echo homey_option('sn_bedrooms_label'); ?></strong></td>
                                <?php echo wp_kses($list_bedrooms, homey_allowed_html()); ?>
                                
                            </tr>
                            <?php } ?>

                            <?php if($hide_labels['sn_bathrooms_label'] != 1) { ?>
                            <tr>
                                <td><strong><?php echo homey_option('sn_bathrooms_label'); ?></strong></td>
                                <?php echo wp_kses($list_baths, homey_allowed_html()); ?>
                                
                            </tr>
                            <?php } ?>

                            <?php if($hide_labels['sn_beds_label'] != 1) { ?>
                            <tr>
                                <td><strong><?php echo homey_option('sn_beds_label'); ?></strong></td>
                                <?php echo wp_kses($list_beds, homey_allowed_html()); ?>
                                
                            </tr>
                            <?php } ?>

                            
                            <?php
                            $all_amenities = get_terms( array( 'taxonomy' => 'listing_amenity', 'fields' => 'names' ) );
                            $compare_terms = array();

                            foreach ( $ids as $post_ID ) :

                            $compare_terms[ $post_ID ] = wp_get_post_terms( $post_ID, 'listing_amenity', array( 'fields' => 'names' ) );

                            endforeach;

                            foreach ( $all_amenities as $data ) :

                            ?>
                            <tr>
                                <td><strong><?php echo wp_kses($data, homey_allowed_html()); ?></strong></td>
                                <?php

                                foreach ( $ids as $post_ID ) :

                                    if ( in_array( $data, $compare_terms[ $post_ID ] ) ) :

                                        echo '<td><i class="fa fa-check text-success"></i></td>';

                                    else :

                                        echo '<td><i class="fa fa-times text-danger"></i></td>';

                                    endif;

                                endforeach;

                                ?>
                            </tr>
                            <?php
                            endforeach;
                            ?>
                            
                        </tbody>
                    </table>
                </div>

                <?php } ?>

            </div><!-- col-xs-12 col-sm-12 col-md-8 col-lg-8 -->
        </div><!-- .row -->
    </div>   <!-- .container -->
    
    
</section><!-- main-content-area listing-page grid-listing-page -->


<?php get_footer(); ?>
