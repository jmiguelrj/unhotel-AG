<?php
global $post,
       $edit_link,
       $homey_local,
       $homey_prefix,
       $prop_address,
       $prop_featured,
       $payment_status;

$post_id    = get_the_ID();
$listing_images = get_post_meta( get_the_ID(), $homey_prefix.'listing_images', false );
$address        = get_post_meta( get_the_ID(), $homey_prefix.'listing_address', true );
$bedrooms       = get_post_meta( get_the_ID(), $homey_prefix.'listing_bedrooms', true );
$guests         = get_post_meta( get_the_ID(), $homey_prefix.'guests', true );
$beds           = get_post_meta( get_the_ID(), $homey_prefix.'beds', true );
$baths          = get_post_meta( get_the_ID(), $homey_prefix.'baths', true );
$night_price    = get_post_meta( get_the_ID(), $homey_prefix.'night_price', true );
$listing_price  = homey_get_price();

$dashboard_listings = homey_get_template_link('template/dashboard-listing.php');
$edit_link  = add_query_arg( 'edit_listing', $post_id, $edit_link ) ;
$delete_link  = add_query_arg( 'listing_id', $post_id, $dashboard_listings ) ;
$property_status = get_post_status ( $post->ID );
$price_separator = homey_option('currency_separator');


$cgl_beds = homey_option('cgl_beds');
$cgl_baths = homey_option('cgl_baths');
$cgl_guests = homey_option('cgl_guests');
?>

<tr>
    <td data-label="<?php echo esc_attr($homey_local['thumb_label']) ;?>">
        <a href="<?php the_permalink(); ?>">
        <?php
        if( has_post_thumbnail( $post->ID ) ) {
            the_post_thumbnail( 'homey-listing-thumb',  array('class' => 'img-responsive dashboard-listing-thumbnail' ) );
        }else{
            homey_image_placeholder( 'homey-listing-thumb' );
        }
        ?>
        </a>
    </td>
    <td data-label="<?php echo esc_attr($homey_local['address']) ;?>">
        <a href="<?php the_permalink(); ?>"><strong><?php the_title(); ?></strong></a>
        <?php if(!empty($address)) { ?>
            <address><?php echo esc_attr($address); ?></address>
        <?php } ?>
    </td>
    <td data-label="<?php echo homey_option('sn_type_label') ;?>"><?php echo homey_taxonomy_simple('listing_type'); ?></td>
    <td data-label="<?php echo esc_attr($homey_local['price_label']) ;?>">
        <?php if(!empty($listing_price)) { ?>
        <strong><?php echo homey_formatted_price($listing_price, false); ?><?php echo esc_attr($price_separator); ?><?php echo homey_get_price_label(); ?></strong><br>
        <?php } ?>
    </td>

    <?php if($cgl_beds != 0) { ?>
        <td data-label="<?php echo homey_option('glc_bedrooms_label') ;?>"><?php echo esc_attr($bedrooms); ?></td>
    <?php } ?>

    <?php if($cgl_baths != 0) { ?>
        <td data-label="<?php echo homey_option('glc_baths_label') ;?>"><?php echo esc_attr($baths); ?></td>
    <?php } ?>

    <?php if($cgl_guests != 0) { ?>
        <td data-label="<?php echo homey_option('glc_guests_label') ;?>"><?php echo esc_attr($guests); ?></td>
    <?php } ?>

    <td data-label="<?php echo homey_option('actions_label') ;?>">
        <div class="custom-actions">
            <button data-listid="<?php echo intval( $post->ID ); ?>" class="remove_fav btn-action" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['delete_btn']); ?>"><i class="fa fa-trash"></i></button>
            <a href="<?php the_permalink(); ?>" target="_blank" class="btn-action" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['view_btn']); ?>"><i class="fa fa-arrow-right"></i></a>
        </div>
    </td>
</tr>