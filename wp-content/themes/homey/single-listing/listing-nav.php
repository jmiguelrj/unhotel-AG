<?php
global $post, $layout_order, $homey_local;
?>
<div class="listing-nav hidden-xs">
	<div class="container">
		<nav class="listing-navi">
			<ul class="main-menu">
				<?php
                if ($layout_order) { 
                    foreach ($layout_order as $key=>$value) {

                        switch($key) { 
                            case 'about':
                                echo '<li><a href="#about-section">'.esc_html__('About', 'homey').'</a></li>';
                            break;

                            case 'about_commercial':
                                echo '<li><a href="#details-section">'.esc_html__('About', 'homey').'</a></li>';
                            break;

                            case 'services':
                                echo '<li><a href="#additional-services">'.esc_attr(homey_option('sn_services_text')).'</a></li>';
                            break;
                            
                            case 'details':
                                echo '<li><a href="#details-section">'.esc_attr(homey_option('sn_detail_heading')).'</a></li>';
                            break;

                            case 'gallery':
                                echo '<li><a href="#gallery-section">'.esc_html__('Gallery', 'homey').'</a></li>';
                            break;

                            case 'prices':
                                echo '<li><a href="#price-section">'.esc_attr(homey_option('sn_prices_heading')).'</a></li>';
                            break;

                            case 'accomodation':
                                echo '<li><a href="#accomodation-section">'.esc_attr(homey_option('sn_accomodation_text')).'</a></li>';
                            break;

                            case 'map':
                                echo '<li><a href="#map-section">'.esc_html__('Map', 'homey').'</a></li>';
                            break;

                            case 'nearby':
                                echo '<li><a href="#nearby-section">'.esc_attr(homey_option('sn_nearby_label')).'</a></li>';
                            break;

                            case 'features':
                                echo '<li><a href="#features-section">'.esc_attr(homey_option('sn_features')).'</a></li>';
                            break;

                            case 'video':
                                echo '<li><a href="#video-section">'.esc_attr(homey_option('sn_video_heading')).'</a></li>';
                            break;

                            case 'rules':
                                echo '<li><a href="#rules-section">'.esc_attr(homey_option('sn_terms_rules')).'</a></li>';
                            break;

                            case 'custom-periods':
                                echo '<li><a href="#custom-price-section">'.$homey_local['custom_period_prices'].'</a></li>';
                            break;

                            case 'availability':
                                echo '<li><a href="#availability-section">'.esc_attr(homey_option('sn_availability_label')).'</a></li>';
                            break;

                            case 'host':
                                echo '<li><a href="#host-section">'.esc_html__('Host', 'homey').'</a></li>';
                            break;

                            case 'reviews':
                                echo '<li><a href="#reviews-section">'.esc_html__('Reviews', 'homey').'</a></li>';
                            break;

                            case 'similar-listing':
                                echo '<li><a href="#similar-listing-section">'.esc_attr(homey_option('sn_similar_label')).'</a></li>';
                            break;
                        }
                    }
                }
                ?>

			</ul><!-- main-menu -->
		</nav><!-- navi -->
	</div><!-- container -->
</div><!-- listing-nav -->	
