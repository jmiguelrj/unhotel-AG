<!-- start footer -->
<?php
$copy_rights = homey_option('copy_rights');
global $homey_local;
?>
<div class="footer-dashboard">
    <footer class="footer">
        <div class="footer-bottom-wrap">
            <div class="container-fluid">
                <div class="row">

                    <?php if( homey_option('social-footer') != '0' ) { ?>
                    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                    <?php } else { ?>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <?php } ?>
                        <div class="footer-copyright">
                            <?php echo esc_html($copy_rights); ?> 
                        </div>
                    </div><!-- col-xs-12 col-sm-6 col-md-6 col-lg-6 -->

                    <?php if( homey_option('social-footer') != '0' ) { ?>
                    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                        <div class="social-footer">
                            <?php get_template_part('template-parts/footer/social');?>
                        </div>
                    </div><!-- col-xs-12 col-sm-6 col-md-6 col-lg-6 -->
                    <?php } ?>

                </div><!-- row -->
            </div><!-- container -->
        </div><!-- footer-bottom-wrap -->
    </footer>
</div>
<!-- end footer -->