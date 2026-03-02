<?php if( homey_option('social-header') != '0' ) {
 if( homey_option('hs-facebook') != '' || homey_option('hs-twitter') != '' || homey_option('hs-linkedin') != '' || homey_option('hs-googleplus') != '' || homey_option('hs-instagram') != '' || homey_option('hs-pinterest') != '' ) { ?>

    <div class="social-icons social-round">
        
        <?php if( homey_option('hs-facebook') != '' ){ ?>
        	<a class="btn-bg-facebook" target="_blank" href="<?php echo esc_url(homey_option('hs-facebook')); ?>"><i class="fa fa-facebook"></i></a>
        <?php } ?>

        <?php if( homey_option('hs-twitter') != '' ){ ?>
            <a class="btn-bg-twitter" target="_blank" href="<?php echo esc_url(homey_option('hs-twitter')); ?>"><i class="fa fa-twitter"></i></a>
        <?php } ?>

        <?php if( homey_option('hs-linkedin') != '' ){ ?>
            <a class="btn-bg-linkedin" target="_blank" href="<?php echo esc_url(homey_option('hs-linkedin')); ?>"><i class="fa fa-linkedin"></i></a>
        <?php } ?>

        <?php if( homey_option('hs-googleplus') != '' ){ ?>
            <a class="btn-bg-google" target="_blank" href="<?php echo esc_url(homey_option('hs-googleplus')); ?>"><i class="fa fa-google"></i></a>
        <?php } ?>

        <?php if( homey_option('hs-instagram') != '' ){ ?>
            <a class="btn-bg-instagram" target="_blank" href="<?php echo esc_url(homey_option('hs-instagram')); ?>"><i class="fa fa-instagram"></i></a>
        <?php } ?>

        <?php if( homey_option('hs-pinterest') != '' ){ ?>
            <a class="btn-bg-pinterest" target="_blank" href="<?php echo esc_url(homey_option('hs-pinterest')); ?>"><i class="fa fa-pinterest"></i></a>
        <?php } ?>

        <?php if( homey_option('hs-yelp') != '' ){ ?>
            <a class="btn-bg-yelp" target="_blank" href="<?php echo esc_url(homey_option('hs-yelp')); ?>"><i class="fa fa-yelp"></i></a>
        <?php } ?>
        <?php if( homey_option('hs-youtube') != '' ){ ?>
            <a class="btn-bg-youtube" target="_blank" href="<?php echo esc_url(homey_option('hs-youtube')); ?>"><i class="fa fa-youtube"></i></a>
        <?php } ?>
        
    </div>
<?php }
} ?>