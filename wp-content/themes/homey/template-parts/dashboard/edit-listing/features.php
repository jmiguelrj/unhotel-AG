<?php
global $homey_local, $hide_fields, $listing_data;
$class = '';
if(isset($_GET['tab']) && $_GET['tab'] == 'features') {
    $class = 'in active';
}
?>

<div id="features-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
    <div class="block-title visible-xs">
        <h3 class="title"><?php echo esc_attr(homey_option('ad_features')); ?></h3>
    </div>
    <div class="block-body">

        <?php if($hide_fields['amenities'] != 1) { ?>
        <div class="listing-form-row">
            <div class="house-features-list">
                <label class="label-title"><?php echo esc_attr(homey_option('ad_amenities')); ?></label>

                <?php
                $amenities_terms_id = array();
                $amenities_terms = get_the_terms( $listing_data->ID, 'listing_amenity' );
                if ( $amenities_terms && ! is_wp_error( $amenities_terms ) ) {
                    foreach( $amenities_terms as $amenity ) {
                        $amenities_terms_id[] = intval( $amenity->term_id );
                    }
                }

                $amenities = get_terms( 'listing_amenity', array( 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false ) );

                if (!empty($amenities)) {
                    
                    foreach ($amenities as $amenity) {

                        if ( in_array( $amenity->term_id, $amenities_terms_id ) ) {
                            echo '<label class="control control--checkbox">';
                                echo '<input type="checkbox" name="listing_amenity[]" id="amenity-' . esc_attr( $amenity->slug ). '" value="' . esc_attr( $amenity->term_id ). '" checked />';
                                echo '<span class="contro-text">'.esc_attr( $amenity->name ).'</span>';
                                echo '<span class="control__indicator"></span>';
                            echo '</label>';
                        } else {
                            echo '<label class="control control--checkbox">';
                                echo '<input type="checkbox" name="listing_amenity[]" id="amenity-' . esc_attr( $amenity->slug ). '" value="' . esc_attr( $amenity->term_id ). '">';
                                echo '<span class="contro-text">'.esc_attr( $amenity->name ).'</span>';
                                echo '<span class="control__indicator"></span>';
                            echo '</label>';
                        }
                    }
                }
                ?>

            </div>
        </div>
        <?php } ?>

        <?php if($hide_fields['facilities'] != 1) { ?>
        <div class="listing-form-row">
            <div class="house-features-list">
                <label class="label-title"><?php echo esc_attr(homey_option('ad_facilities')); ?></label>
                <?php
                $facilities_terms_id = array();
                $facilities_terms = get_the_terms( $listing_data->ID, 'listing_facility' );
                if ( $facilities_terms && ! is_wp_error( $facilities_terms ) ) {
                    foreach( $facilities_terms as $facility ) {
                        $facilities_terms_id[] = intval( $facility->term_id );
                    }
                }

                $facilities = get_terms( 'listing_facility', array( 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false ) );

                if (!empty($facilities)) {
                    
                    foreach ($facilities as $facility) {
                        if ( in_array( $facility->term_id, $facilities_terms_id ) ) {
                            echo '<label class="control control--checkbox">';
                                echo '<input type="checkbox" name="listing_facility[]" id="facility-' . esc_attr( $facility->slug ). '" value="' . esc_attr( $facility->term_id ). '" checked />';
                                echo '<span class="contro-text">'.esc_attr( $facility->name ).'</span>';
                                echo '<span class="control__indicator"></span>';
                            echo '</label>';
                        } else {
                            echo '<label class="control control--checkbox">';
                                echo '<input type="checkbox" name="listing_facility[]" id="facility-' . esc_attr( $facility->slug ). '" value="' . esc_attr( $facility->term_id ). '">';
                                echo '<span class="contro-text">'.esc_attr( $facility->name ).'</span>';
                                echo '<span class="control__indicator"></span>';
                            echo '</label>';
                            
                        }
                    }
                }
                ?>
            </div>
        </div>
        <?php } ?>
        
    </div>
</div>