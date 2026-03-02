<?php
$copy_rights = homey_option('copy_rights');
global $homey_local;    

$footer_cols = homey_option('footer_cols');
?>
<footer class="footer-wrap footer">
	<?php
	if ( is_active_sidebar( 'footer-sidebar-1' )
    || is_active_sidebar( 'footer-sidebar-2' )
    || is_active_sidebar( 'footer-sidebar-3' )
    || is_active_sidebar( 'footer-sidebar-4' ) ) { ?>

	<div class="footer-top-wrap">
		<div class="container">
			<div class="row">
				<?php
	            if( $footer_cols === 'one_col' ) {
	                if ( is_active_sidebar( 'footer-sidebar-1' ) ) {
	                    echo '<div class="col-md-12 col-sm-12">';
	                        dynamic_sidebar( 'footer-sidebar-1' );
	                    echo '</div>';
	                }
	            } elseif( $footer_cols === 'two_col' ) {
	                if ( is_active_sidebar( 'footer-sidebar-1' ) ) {
	                    echo '<div class="col-md-6 col-sm-12">';
	                        dynamic_sidebar( 'footer-sidebar-1' );
	                    echo '</div>';
	                }
	                if ( is_active_sidebar( 'footer-sidebar-2' ) ) {
	                    echo '<div class="col-md-6 col-sm-12">';
	                        dynamic_sidebar( 'footer-sidebar-2' );
	                    echo '</div>';
	                }
	            } elseif( $footer_cols === 'three_cols_middle' ) {
	                if ( is_active_sidebar( 'footer-sidebar-1' ) ) {
	                    echo '<div class="col-md-4 col-sm-12 col-xs-12">';
	                        dynamic_sidebar( 'footer-sidebar-1' );
	                    echo '</div>';
	                }
	                if ( is_active_sidebar( 'footer-sidebar-2' ) ) {
	                    echo '<div class="col-md-4 col-sm-12 col-xs-12">';
	                        dynamic_sidebar( 'footer-sidebar-2' );
	                    echo '</div>';
	                }
	                if ( is_active_sidebar( 'footer-sidebar-3' ) ) {
	                    echo '<div class="col-md-4 col-sm-12 col-xs-12">';
	                        dynamic_sidebar( 'footer-sidebar-3' );
	                    echo '</div>';
	                }
	            } elseif( $footer_cols === 'three_cols' ) {
	                if ( is_active_sidebar( 'footer-sidebar-1' ) ) {
	                    echo '<div class="col-md-3 col-sm-12 col-xs-12">';
	                        dynamic_sidebar( 'footer-sidebar-1' );
	                    echo '</div>';
	                }
	                if ( is_active_sidebar( 'footer-sidebar-2' ) ) {
	                    echo '<div class="col-md-3 col-sm-12 col-xs-12">';
	                        dynamic_sidebar( 'footer-sidebar-2' );
	                    echo '</div>';
	                }
	                if ( is_active_sidebar( 'footer-sidebar-3' ) ) {
	                    echo '<div class="col-md-6 col-sm-12 col-xs-12">';
	                        dynamic_sidebar( 'footer-sidebar-3' );
	                    echo '</div>';
	                }
	            } elseif( $footer_cols === 'four_cols' ) {
	                if ( is_active_sidebar( 'footer-sidebar-1' ) ) {
	                    echo '<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">';
	                        dynamic_sidebar( 'footer-sidebar-1' );
	                    echo '</div>';
	                }
	                if ( is_active_sidebar( 'footer-sidebar-2' ) ) {
	                    echo '<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">';
	                        dynamic_sidebar( 'footer-sidebar-2' );
	                    echo '</div>';
	                }
	                if ( is_active_sidebar( 'footer-sidebar-3' ) ) {
	                    echo '<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">';
	                        dynamic_sidebar( 'footer-sidebar-3' );
	                    echo '</div>';
	                }
	                if ( is_active_sidebar( 'footer-sidebar-4' ) ) {
	                    echo '<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">';
	                        dynamic_sidebar( 'footer-sidebar-4' );
	                    echo '</div>';
	                }
	            }
	            ?>
			</div><!-- row -->
		</div><!-- container -->
	</div><!-- footer-top-wrap -->
	<?php } ?>

    <?php if( homey_option('social-footer') != '0' || !empty(trim($copy_rights)) ) { ?>
    <div class="footer-bottom-wrap">
		<div class="container">
			<div class="row">
				<?php if( homey_option('social-footer') != '0' ) { ?>
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <?php } else { ?>
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <?php } ?>
					<div class="footer-copyright">
						<?php echo esc_html($copy_rights); ?> 
					</div>
				</div><!-- col-xs-12 col-sm-6 col-md-6 col-lg-6 -->
				<?php if( homey_option('social-footer') != '0' ) { ?>
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                    <div class="social-footer">
                        <?php get_template_part('template-parts/footer/social');?>
                    </div>
                </div><!-- col-xs-12 col-sm-6 col-md-6 col-lg-6 -->
                <?php } ?>
			</div><!-- row -->
		</div><!-- container -->
	</div><!-- footer-bottom-wrap -->
    <?php } ?>
</footer><!-- footer-wrap -->