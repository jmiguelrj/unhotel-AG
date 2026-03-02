<?php
global $homey_local, $hide_fields, $listing_data, $listing_meta_data;
$listing_id = $listing_data->ID;
$geo_country_limit = homey_option('geo_country_limit');
$geocomplete_country = '';
if( $geo_country_limit != 0 ) {
    $geocomplete_country = homey_option('geocomplete_country');
}
$add_location_lat = homey_get_field_meta('geolocation_lat');
$add_location_long = homey_get_field_meta('geolocation_long');

if( empty($add_location_lat) ) {
    $add_location_lat = homey_option('add_location_lat');
}

if( empty($add_location_long) ) {
    $add_location_long = homey_option('add_location_long');
}

$class = '';
if(isset($_GET['tab']) && $_GET['tab'] == 'location') {
    $class = 'in active';
}
?>

<div id="location-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
    <div class="block-title visible-xs">
        <h3 class="title"><?php echo esc_attr(homey_option('ad_location')); ?></h3>
    </div>
    <div class="block-body">

        <div class="row">
            <?php if($hide_fields['listing_address'] != 1) { ?>
            <div class="col-sm-8">
                <div class="form-group">
                    <label for="listing_address"><?php echo esc_attr(homey_option('ad_address')).homey_req('listing_address'); ?></label>
                    <input type="text" autocomplete="false" name="listing_address" <?php homey_required('listing_address'); ?> class="form-control" value="<?php homey_field_meta('listing_address'); ?>" id="listing_address" placeholder="<?php echo esc_attr(homey_option('ad_address_placeholder')); ?>">
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['aptSuit'] != 1) { ?>
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="aptSuit"> <?php echo esc_attr(homey_option('ad_aptSuit')).homey_req('aptSuit'); ?> </label>
                    <input type="text" autocomplete="false" name="aptSuit" <?php homey_required('aptSuit'); ?> class="form-control" value="<?php homey_field_meta('aptSuit'); ?>" id="aptSuit" placeholder="<?php echo esc_attr(homey_option('ad_aptSuit_placeholder')); ?>">
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['city'] != 1) { ?>
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="city"><?php echo esc_attr(homey_option('ad_city')).homey_req('city'); ?></label>
                    <input type="text" autocomplete="false" name="locality" <?php homey_required('city'); ?> value="<?php echo homey_get_taxonomy_title($listing_id, 'listing_city'); ?>" id="city" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_city_placeholder')); ?>">
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['state'] != 1) { ?>
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="state"><?php echo esc_attr(homey_option('ad_state')).homey_req('state'); ?></label>
                    <input type="text" autocomplete="false" name="administrative_area_level_1" <?php homey_required('state'); ?> value="<?php echo homey_get_taxonomy_title($listing_id, 'listing_state'); ?>" id="countyState"  class="form-control" id="state" placeholder="<?php echo esc_attr(homey_option('ad_state_placeholder')); ?>">

                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['zipcode'] != 1) { ?>
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="zip"><?php echo esc_attr(homey_option('ad_zipcode')).homey_req('zip'); ?></label>
                    <input type="text" autocomplete="false" name="zip" <?php homey_required('zip'); ?> class="form-control" value="<?php homey_field_meta('zip'); ?>" id="zip" placeholder="<?php echo esc_attr(homey_option('ad_zipcode_placeholder')); ?>">
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['area'] != 1) { ?>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="neighborhood"><?php echo esc_attr(homey_option('ad_area')).homey_req('area'); ?></label>
                    <input class="form-control" autocomplete="false" name="neighborhood" <?php homey_required('area'); ?> value="<?php echo homey_get_taxonomy_title($listing_id, 'listing_area'); ?>" id="area" placeholder="<?php echo esc_attr(homey_option('ad_area_placeholder')); ?>">
                </div>
            </div>
            <?php } ?>

            <?php if($hide_fields['country'] != 1) { ?>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="country"><?php echo esc_attr(homey_option('ad_country')).homey_req('country'); ?></label>
                    <input class="form-control" autocomplete="false" name="country" <?php homey_required('country'); ?> value="<?php echo homey_get_taxonomy_title($listing_id, 'listing_country'); ?>" id="homey_country" placeholder="<?php echo esc_attr(homey_option('ad_country_placeholder')); ?>">
                    <input name="country_short" type="hidden" value="">
                </div>
            </div>
            <?php } ?>
            
        </div>
        <div id="homey_edit_map" class="row add-listing-map">
            <div class="col-sm-12">
                <div class="form-group">
                    <label><?php echo esc_attr(homey_option('ad_drag_pin')); ?></label>
                    <div class="map_canvas" data-add-lat="<?php echo esc_attr($add_location_lat); ?>" data-add-long="<?php echo esc_attr($add_location_long); ?>" id="map">
                    </div>
                </div>
            </div>
        </div>
        <div class="row add-listing-map">
            <div class="col-sm-12">
                <label><?php echo esc_attr(homey_option('ad_find_address')); ?></label>
            </div>
            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                <div class="form-group">
                    <input type="text" name="lat" id="lat" value="<?php homey_field_meta('geolocation_lat'); ?>" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_lat')); ?>">
                </div>
            </div>
            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                <div class="form-group">
                    <input type="text" name="lng" id="lng" value="<?php homey_field_meta('geolocation_long'); ?>" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_long')); ?>">
                </div>
            </div>
            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                <span id="find" class="btn btn-primary btn-full-width"><?php echo esc_attr(homey_option('ad_find_address_btn')); ?></span>
            </div>
        </div>
        
    </div>
</div>