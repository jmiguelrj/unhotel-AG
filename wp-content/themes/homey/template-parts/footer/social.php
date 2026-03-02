<?php if( homey_option('social-footer') != '0' ) {
 if( homey_option('fs-facebook') != '' || homey_option('fs-twitter') != '' || homey_option('fs-linkedin') != '' || homey_option('fs-googleplus') != '' || homey_option('fs-instagram') != '' || homey_option('fs-pinterest') != '' ) { ?>

    <div class="social-icons social-round">
        
        <?php if( homey_option('fs-facebook') != '' ){ ?>
        	<a class="btn-bg-facebook" target="_blank" href="<?php echo esc_url(homey_option('fs-facebook')); ?>"><i class="fa fa-facebook"></i></a>
        <?php } ?>

        <?php if( homey_option('fs-twitter') != '' ){ ?>
            <a class="btn-bg-twitter" target="_blank" href="<?php echo esc_url(homey_option('fs-twitter')); ?>"><i class="fa fa-twitter"></i></a>
        <?php } ?>

        <?php if( homey_option('fs-linkedin') != '' ){ ?>
            <a class="btn-bg-linkedin" target="_blank" href="<?php echo esc_url(homey_option('fs-linkedin')); ?>"><i class="fa fa-linkedin"></i></a>
        <?php } ?>

        <?php if( homey_option('fs-googleplus') != '' ){ ?>
            <a class="btn-bg-google" target="_blank" href="<?php echo esc_url(homey_option('fs-googleplus')); ?>"><i class="fa fa-google"></i></a>
        <?php } ?>

        <?php if( homey_option('fs-instagram') != '' ){ ?>
            <a class="btn-bg-instagram" target="_blank" href="<?php echo esc_url(homey_option('fs-instagram')); ?>"><i class="fa fa-instagram"></i></a>
        <?php } ?>

        <?php if( homey_option('fs-pinterest') != '' ){ ?>
            <a class="btn-bg-pinterest" target="_blank" href="<?php echo esc_url(homey_option('fs-pinterest')); ?>"><i class="fa fa-pinterest"></i></a>
        <?php } ?>

        <?php if( homey_option('fs-yelp') != '' ){ ?>
            <a class="btn-bg-yelp" target="_blank" href="<?php echo esc_url(homey_option('fs-yelp')); ?>"><i class="fa fa-yelp"></i></a>
        <?php } ?>
        <?php if( homey_option('fs-youtube') != '' ){ ?>
            <a class="btn-bg-youtube" target="_blank" href="<?php echo esc_url(homey_option('fs-youtube')); ?>"><i class="fa fa-youtube"></i></a>
        <?php } ?>
        
    </div>
<?php }
} ?>