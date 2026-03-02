<?php
global $userID, $homey_prefix, $homey_local;
$street_address  =  get_the_author_meta( $homey_prefix.'street_address' , $userID );
$country  =  get_the_author_meta( $homey_prefix.'country' , $userID );
$neighborhood  =  get_the_author_meta( $homey_prefix.'neighborhood' , $userID );
$zipcode  =  get_the_author_meta( $homey_prefix.'zipcode' , $userID );
$state  =  get_the_author_meta( $homey_prefix.'state' , $userID );
$city  =  get_the_author_meta( $homey_prefix.'city' , $userID );
$apt_suit  =  get_the_author_meta( $homey_prefix.'apt_suit' , $userID );
?>
<div class="block">
    <div class="block-title">
        <div class="block-left">
            <h2 class="title"><?php echo esc_html__('Address', 'homey'); ?></h2>
        </div>
    </div>
    <div class="block-body">
        <div class="row">
            <div class="col-sm-9">
                <div class="form-group">
                    <label for="street_address"><?php echo esc_html__('Street Address', 'homey'); ?></label>
                    <input type="text" id="street_address" class="form-control" value="<?php echo esc_attr($street_address); ?>" placeholder="<?php echo esc_attr__('Enter street address', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    <label for="apt_suit"> <?php echo esc_html__('Apt, Suite', 'homey'); ?> </label>
                    <input type="text" id="apt_suit" class="form-control" value="<?php echo esc_attr($apt_suit); ?>" placeholder=" <?php echo esc_attr__('Ex. #123', 'homey'); ?> ">
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="city"><?php echo esc_html__('City', 'homey'); ?></label>
                    <input type="text" id="city" class="form-control" value="<?php echo esc_attr($city); ?>" placeholder="<?php echo esc_attr__('Enter your city', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="state"><?php echo esc_html__('State', 'homey'); ?></label>
                    <input type="text" id="state" class="form-control" value="<?php echo esc_attr($state); ?>" placeholder="<?php echo esc_attr__('Enter your state', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="zipcode"><?php echo esc_html__('Zip Code', 'homey'); ?></label>
                    <input type="text" id="zipcode" class="form-control" value="<?php echo esc_attr($zipcode); ?>" placeholder="<?php echo esc_attr__('Enter zip code', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="neighborhood"><?php echo esc_html__('Neighborhood', 'homey'); ?></label>
                    <input type="text" id="neighborhood" class="form-control" value="<?php echo esc_attr($neighborhood); ?>" placeholder="<?php echo esc_attr__('Neighborhood', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="counter"><?php echo esc_html__('Country', 'homey'); ?></label>
                    <input type="text" id="country" class="form-control" value="<?php echo esc_attr($country); ?>" placeholder="<?php echo esc_attr__('country', 'homey'); ?>">
                </div>
            </div>
            <div class="col-sm-12 text-right">
                <button type="submit" class="homey_profile_save btn btn-success btn-xs-full-width"><?php echo esc_attr($homey_local['save_btn']); ?></button>
            </div>
        </div>
    </div>
</div><!-- block -->