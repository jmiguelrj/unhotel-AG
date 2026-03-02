<?php  
global $post, $homey_prefix, $homey_local;
$listing_images = get_post_meta( get_the_ID(), $homey_prefix.'listing_images', false );
$address        = get_post_meta( get_the_ID(), $homey_prefix.'listing_address', true );
$bedrooms       = get_post_meta( get_the_ID(), $homey_prefix.'listing_bedrooms', true );
$guests         = get_post_meta( get_the_ID(), $homey_prefix.'guests', true );

$allow_additional_guests = get_post_meta( get_the_ID(), $homey_prefix.'allow_additional_guests', true );
$num_additional_guests = get_post_meta( get_the_ID(), $homey_prefix.'num_additional_guests', true );

if( $allow_additional_guests == 'yes' && ! empty( $num_additional_guests ) ) {
    $guests = $guests + $num_additional_guests;
}


$beds           = get_post_meta( get_the_ID(), $homey_prefix.'beds', true );
$baths          = get_post_meta( get_the_ID(), $homey_prefix.'baths', true );
$night_price    = get_post_meta( get_the_ID(), $homey_prefix.'night_price', true );
$listing_author = homey_get_author();
$enable_host = homey_option('enable_host');
$compare_favorite = homey_option('compare_favorite');

$listing_price = homey_get_price();

$cgl_meta = homey_option('cgl_meta');
$cgl_beds = homey_option('cgl_beds');
$cgl_baths = homey_option('cgl_baths');
$cgl_guests = homey_option('cgl_guests');
$cgl_types = homey_option('cgl_types');
$rating = homey_option('rating');
$total_rating = get_post_meta( get_the_ID(), 'listing_total_rating', true );
$listing_rating = homey_get_review_stars($total_rating, false, true);

$bedrooms_icon = homey_option('lgc_bedroom_icon'); 
$bathroom_icon = homey_option('lgc_bathroom_icon'); 
$guests_icon = homey_option('lgc_guests_icon');
$price_separator = homey_option('currency_separator');

if(!empty($bedrooms_icon)) {
    $bedrooms_icon = '<i class="'.esc_attr($bedrooms_icon).'"></i>';
}
if(!empty($bathroom_icon)) {
    $bathroom_icon = '<i class="'.esc_attr($bathroom_icon).'"></i>';
}
if(!empty($guests_icon)) {
    $guests_icon = '<i class="'.esc_attr($guests_icon).'"></i>';
}

$homey_permalink = homey_listing_permalink();
?>
<div class="item-wrap infobox_trigger homey-matchHeight" data-id="<?php echo $post->ID; ?>">
    <div class="media property-item">
        
            <div class="item-media item-media-thumb">

                <?php homey_listing_featured(get_the_ID()); ?>

                <a class="hover-effect" href="<?php echo esc_url($homey_permalink); ?>">
                <?php
                if( has_post_thumbnail( $post->ID ) ) {
                    the_post_thumbnail( 'homey-listing-thumb',  array('class' => 'img-responsive' ) );
                }else{
                    homey_image_placeholder( 'homey-listing-thumb' );
                }
                ?>
                </a>

                <div class="title-head">

                    <?php if(!empty($listing_price)) { ?>
                    <span class="item-price">
                        <?php echo homey_formatted_price($listing_price, false, true); ?><sub><?php echo esc_attr($price_separator); ?><?php echo homey_get_price_label();?></sub>
                    </span>
                    <?php } ?>

                    <h2 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

                    <?php if($cgl_meta != 0) { ?>
                    <ul class="item-amenities">

                        <?php if($cgl_beds != 0 && $bedrooms != '') { ?>
                        <li>
                            <?php echo ''.$bedrooms_icon; ?>
                            <span class="total-beds"><?php echo esc_attr($bedrooms); ?></span>
                        </li>
                        <?php } ?>

                        <?php if($cgl_baths != 0 && $baths != '') { ?>
                        <li>
                            <?php echo ''.$bathroom_icon; ?>
                            <span class="total-baths"><?php echo esc_attr($baths); ?></span>
                        </li>
                        <?php } ?>

                        <?php if($cgl_guests!= 0 && $guests != '') { ?>
                        <li>
                            <?php echo ''.$guests_icon; ?>
                            <span class="total-guests"><?php echo esc_attr($guests); ?></span>
                        </li>
                        <?php } ?>
                    </ul>
                    <?php } ?>
                </div>
                

                <?php if($compare_favorite) { ?>
                <div class="item-tools">
                    <div class="btn-group dropup">
                        <?php get_template_part('template-parts/listing/compare-fav'); ?>
                    </div>
                </div>
                <?php } ?>

                <?php if($enable_host) { ?>
                <div class="item-user-image">
                    <?php echo ''.$listing_author['photo']; ?>
                </div>
                <?php } ?>

            </div>
        
    </div>
</div><!-- .item-wrap -->