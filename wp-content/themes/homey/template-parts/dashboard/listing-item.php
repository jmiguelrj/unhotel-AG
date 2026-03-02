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
$featured    = get_post_meta( get_the_ID(), $homey_prefix.'featured', true );

$listing_price = homey_get_price_by_id($post_id);

$dashboard_listings = homey_get_template_link('template/dashboard-listing.php');
$edit_link  = add_query_arg( 'edit_listing', $post_id, $edit_link ) ;
$delete_link  = add_query_arg( 'listing_id', $post_id, $dashboard_listings ) ;
$property_status = get_post_status ( $post->ID );
$check_listing_status = $property_status;
$dashboard = homey_get_template_link('template/dashboard.php');
$price_separator = homey_option('currency_separator');
$make_featured = homey_option('make_featured');

if($property_status == 'publish') {
    $property_status = esc_html__('Published', 'homey');
    $status_class = "label-success";
} elseif($property_status == 'pending') {
    $status_class = "label-warning";
    $property_status = esc_html__('Waiting for Approval', 'homey');
} elseif($property_status == 'draft') {
    $status_class = 'label-default';
    $property_status = esc_html__('Draft', 'homey');
} elseif($property_status == 'disabled') {
    $status_class = 'label-danger';
    $property_status = esc_html__('Disabled', 'homey');
} else {
    $status_class = "label-success";
    $property_status = esc_html__(strtoupper($property_status), 'homey');

}

if($check_listing_status == 'publish') {
    $disable_list_text = esc_html__('Disable Listing', 'homey');
    $icon = 'fa-pause';
    $list_current_status = 'enabled';
} elseif($check_listing_status == 'disabled') {
    $disable_list_text = esc_html__('Enable Listing', 'homey');
    $list_current_status = 'disabled';
    $icon = 'fa-play';
}

$upgrade_link  = add_query_arg( array(
    'dpage' => 'upgrade_featured',
    'upgrade_id' => $post_id,
), $dashboard );
?>

<tr>
    <td data-label="<?php echo esc_attr($homey_local['thumb_label']); ?>">
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
    <td data-label="<?php echo esc_attr($homey_local['address']); ?>">
        <a href="<?php the_permalink(); ?>"><strong><?php the_title(); ?></strong></a>
        <?php if(!empty($address)) { ?>
            <address><?php echo esc_attr($address); ?></address>
        <?php } ?>
    </td>
    <!-- <td data-label="ID">HY01</td> -->
    <td data-label="<?php echo homey_option('sn_type_label'); ?>"><?php echo homey_taxonomy_simple('listing_type'); ?></td>
    <td data-label="<?php echo esc_attr($homey_local['price_label']); ?>">
        <?php if(!empty($listing_price)) { ?>
            <strong><?php echo homey_formatted_price($listing_price, false); ?><?php echo esc_attr($price_separator); ?><?php echo homey_get_price_label_by_id($post_id); ?></strong><br>
        <?php } ?>
    </td>
    <td data-label="<?php echo homey_option('glc_bedrooms_label');?>"><?php echo esc_attr($bedrooms); ?></td>
    <td data-label="<?php echo homey_option('glc_baths_label');?>"><?php echo esc_attr($baths); ?></td>
    <td data-label="<?php echo homey_option('glc_guests_label');?>"><?php echo esc_attr($guests); ?></td>
    <td data-label="<?php echo homey_option('sn_id_label');?>"><?php echo get_the_ID(); ?></td>
    <td data-label="<?php echo esc_attr($homey_local['status_label']); ?>">
        <span class="label <?php echo esc_attr($status_class); ?>"><?php echo esc_html($property_status); ?></span>
    </td>
    <td data-label="<?php echo esc_attr($homey_local['actions_label']); ?>">
        <div class="custom-actions">
            <button class="btn-action" onclick="location.href='<?php echo esc_url($edit_link);?>';" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['edit_btn']); ?>"><i class="fa fa-pencil"></i></button>
            <button class="btn-action" onclick="location.href='<?php echo esc_url($edit_link.'&duplication=1');?>';" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr('Duplicate', 'homey'); ?>"><i class="fa fa-copy"></i></button>

            <?php
            if($featured != 1 && $make_featured != 0) {

                if( homey_is_woocommerce() ) { ?>

                    <a href="#" data-listid="<?php echo intval($post_id); ?>" data-featured="1" class="homey-woocommerce-featured-pay btn-action" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['upgrade_btn']); ?>"><i class="fa fa-star-o"></i></a>

                    <?php
                } else if( in_array('homey-membership/homey-membership.php', apply_filters('active_plugins', get_option('active_plugins')))) { ?>

                    <a href="" class="membership-featured-js btn-action" data-id="<?php echo intval($post->ID); ?>" data-nonce="<?php echo wp_create_nonce('featured_listing_nonce') ?>" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['upgrade_btn']); ?>"><i class="fa fa-star-o"></i></a>

                    <?php
                } else { ?>

                    <a href="<?php echo esc_url($upgrade_link); ?>" class="btn-action" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['upgrade_btn']); ?>"><i class="fa fa-star-o"></i></a>

                    <?php
                }
            }
            ?>

            <button class="btn-action delete-listing" data-id="<?php echo intval($post->ID); ?>" data-nonce="<?php echo wp_create_nonce('delete_listing_nonce') ?>" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['delete_btn']);?>">
                <i class="fa fa-trash"></i>
            </button>

            <?php if($check_listing_status == 'publish' || $check_listing_status == 'disabled') { ?>
                <button class="btn-action put_on_hold" data-id="<?php echo intval($post->ID); ?>" data-current="<?php echo esc_attr($list_current_status);?>" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($disable_list_text);?>">
                    <i class="fa <?php echo esc_attr($icon); ?>"></i>
                </button>
            <?php } ?>

            <a href="<?php the_permalink(); ?>" target="_blank" class="btn-action" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['view_btn']); ?>"><i class="fa fa-arrow-right"></i></a>


        </div>
    </td>
</tr>