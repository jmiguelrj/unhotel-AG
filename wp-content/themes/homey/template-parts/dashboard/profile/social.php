<?php
global $current_user, $homey_local;
$current_user = wp_get_current_user();
$userID                 =   $current_user->ID;
$user_login             =   $current_user->user_login;

$facebook               =   get_the_author_meta( 'homey_author_facebook' , $userID );
$twitter                =   get_the_author_meta( 'homey_author_twitter' , $userID );
$linkedin               =   get_the_author_meta( 'homey_author_linkedin' , $userID );
$pinterest              =   get_the_author_meta( 'homey_author_pinterest' , $userID );
$instagram              =   get_the_author_meta( 'homey_author_instagram' , $userID );
$googleplus             =   get_the_author_meta( 'homey_author_googleplus' , $userID );
$youtube                =   get_the_author_meta( 'homey_author_youtube' , $userID );
$vimeo                  =   get_the_author_meta( 'homey_author_vimeo' , $userID );
$airbnb                 =   get_the_author_meta( 'homey_author_airbnb' , $userID );
$trip_advisor           =   get_the_author_meta( 'homey_author_trip_advisor' , $userID );
?>
<div class="block">
    <div class="block-title">
        <div class="block-left">
            <h2 class="title"><?php echo esc_html__('Social Media', 'homey'); ?></h2>
        </div>
    </div>
    <div class="block-body">
        <form>
            <div class="row">

                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="facebook"><?php esc_html_e( 'Facebook URL', 'homey' ); ?></label>
                        <input type="text" id="facebook" name="facebook" value="<?php echo esc_url( $facebook );?>"  class="form-control">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="twitter"><?php esc_html_e( 'Twitter URL', 'homey' ); ?></label>
                        <input type="text" id="twitter" class="form-control" value="<?php echo esc_url( $twitter );?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="linkedin"><?php esc_html_e( 'Linkedin URL', 'homey' ); ?></label>
                        <input type="text" id="linkedin" class="form-control" value="<?php echo esc_url( $linkedin );?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="googleplus"><?php esc_html_e('Google Plus Url','homey');?></label>
                        <input type="text" id="googleplus" class="form-control" value="<?php echo esc_url( $googleplus );?>" name="googleplus">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="instagram"><?php esc_html_e( 'Instagram URL', 'homey' ); ?></label>
                        <input type="text" id="instagram" class="form-control" value="<?php echo esc_url( $instagram );?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="pinterest"><?php esc_html_e('Pinterest Url','homey');?></label>
                        <input type="text" id="pinterest" class="form-control" value="<?php echo esc_url( $pinterest );?>" name="pinterest">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="youtube"><?php esc_html_e('Youtube Url','homey');?></label>
                        <input type="text" id="youtube" class="form-control" value="<?php echo esc_url( $youtube );?>" name="youtube">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="vimeo"><?php esc_html_e('Vimeo Url','homey');?></label>
                        <input type="text" id="vimeo" class="form-control" value="<?php echo esc_url( $vimeo );?>" name="vimeo">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="airbnb"><?php esc_html_e('Airbnb Url','homey');?></label>
                        <input type="text" id="airbnb" class="form-control" value="<?php echo esc_url( $airbnb );?>" name="airbnb">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="trip_advisor"><?php esc_html_e('Trip Advisor Url','homey');?></label>
                        <input type="text" id="trip_advisor" class="form-control" value="<?php echo esc_url( $trip_advisor );?>" name="trip_advisor">
                    </div>
                </div>
                <div class="col-sm-12 text-right">
                    <button type="submit" class="homey_profile_save btn btn-success btn-xs-full-width"><?php echo esc_html__('Save', 'homey'); ?></button>
                </div>
            </div>
        </form>
    </div>
</div><!-- block -->