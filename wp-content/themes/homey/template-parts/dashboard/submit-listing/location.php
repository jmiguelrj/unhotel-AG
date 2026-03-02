<?php
global $homey_local, $hide_fields;

$add_location_lat = homey_option('add_location_lat');
$add_location_long = homey_option('add_location_long');
$geo_country_limit = homey_option('geo_country_limit');
$geocomplete_country = '';
if( $geo_country_limit != 0 ) {
    $geocomplete_country = homey_option('geocomplete_country');
}
?>
<div class="form-step">
    <!--step information-->
    <div class="block">
        <div class="block-title">
            <div class="block-left">
                <h2 class="title"><?php echo esc_html(homey_option('ad_location')); ?></h2>
            </div><!-- block-left -->
        </div>
        <div class="block-body">

            <div class="row">

                <?php if($hide_fields['listing_address'] != 1) { ?>
                <div class="col-sm-8">
                    <div class="form-group">
                        <label for="listing_address"><?php echo esc_attr(homey_option('ad_address')).homey_req('listing_address'); ?></label>
                        <input type="text" autocomplete="false" data-input-title="<?php echo esc_html__(esc_attr(homey_option('ad_address')), 'homey'); ?>" name="listing_address" <?php homey_required('listing_address'); ?> class="form-control" id="listing_address" placeholder="<?php echo esc_attr(homey_option('ad_address_placeholder')); ?>">
                    </div>
                </div>
                <?php } ?>

                <?php if($hide_fields['aptSuit'] != 1) { ?>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="aptSuit"> <?php echo esc_attr(homey_option('ad_aptSuit')).homey_req('aptSuit'); ?> </label>
                        <input type="text" autocomplete="false" name="aptSuit" <?php homey_required('aptSuit'); ?> class="form-control" id="aptSuit" placeholder="<?php echo esc_attr(homey_option('ad_aptSuit_placeholder')); ?>">
                    </div>
                </div>
                <?php } ?>

                <?php if($hide_fields['city'] != 1) { ?>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="city"><?php echo esc_attr(homey_option('ad_city')).homey_req('city'); ?></label>
                        <input type="text" autocomplete="false" data-input-title="<?php echo esc_html__(esc_attr(homey_option('ad_city')), 'homey'); ?>" name="locality" id="city" <?php homey_required('city'); ?> class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_city_placeholder')); ?>">
                    </div>
                </div>
                <?php } ?>

                <?php if($hide_fields['state'] != 1) { ?>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="state"><?php echo esc_attr(homey_option('ad_state')).homey_req('state'); ?></label>
                        <input type="text" autocomplete="false" name="administrative_area_level_1" id="countyState"  class="form-control" id="state" <?php homey_required('state'); ?> placeholder="<?php echo esc_attr(homey_option('ad_state_placeholder')); ?>">

                    </div>
                </div>
                <?php } ?>

                <?php if($hide_fields['zipcode'] != 1) { ?>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="zip"><?php echo esc_attr(homey_option('ad_zipcode')).homey_req('zip'); ?></label>
                        <input type="text" autocomplete="false" name="zip" <?php homey_required('zip'); ?> class="form-control" id="zip" placeholder="<?php echo esc_attr(homey_option('ad_zipcode_placeholder')); ?>">
                    </div>
                </div>
                <?php } ?>

                <?php if($hide_fields['area'] != 1) { ?>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="neighborhood"><?php echo esc_attr(homey_option('ad_area')).homey_req('area'); ?></label>
                        <input class="form-control" autocomplete="false" name="neighborhood" id="area" <?php homey_required('area'); ?> placeholder="<?php echo esc_attr(homey_option('ad_area_placeholder')); ?>">
                    </div>
                </div>
                <?php } ?>

                <?php if($hide_fields['country'] != 1) { ?>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="country"><?php echo esc_attr(homey_option('ad_country')).homey_req('country'); ?></label>
                        <input class="form-control" autocomplete="false" name="country" id="homey_country" <?php homey_required('country'); ?> placeholder="<?php echo esc_attr(homey_option('ad_country_placeholder')); ?>">
                        <input name="country_short" type="hidden" value="">
                    </div>
                </div>
                <?php } ?>

            </div>
            <div class="row add-listing-map">
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
                        <input type="text" name="lat" id="lat" class="form-control" placeholder="<?php echo empty(esc_attr(homey_option('ad_lat'))) ? esc_attr($add_location_lat) :  esc_attr(homey_option('ad_lat')); ?>" value="<?php echo !empty(esc_attr(homey_option('ad_lat'))) ? esc_attr($add_location_lat) :  esc_attr(homey_option('ad_lat')); ?>">
                    </div>
                </div>
                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                    <div class="form-group">
                        <input type="text" name="lng" id="lng" class="form-control" placeholder="<?php echo empty(esc_attr(homey_option('ad_long'))) ? esc_attr($add_location_long) :  esc_attr(homey_option('ad_long')); ?>" value="<?php echo !empty(esc_attr(homey_option('ad_long'))) ? esc_attr($add_location_long) :  esc_attr(homey_option('ad_long')); ?>">
                    </div>
                </div>
                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                    <span id="find" class="btn btn-primary btn-full-width"><?php echo esc_attr(homey_option('ad_find_address_btn')); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo !empty(esc_attr(homey_option('ad_long'))) ? '<script type="text/javascript">jQuery(document).ready(function(){ jQuery("#find").click();})</script>' : ''; ?>
