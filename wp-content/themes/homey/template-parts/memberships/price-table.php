<?php
$billing_period = get_post_meta( get_the_ID(), 'hm_settings_bill_period', true );
$billing_frequency = get_post_meta( get_the_ID(), 'hm_settings_billing_frequency', true );

$listings_included = get_post_meta( get_the_ID(), 'hm_settings_listings_included', true );
$unlimited_listings = get_post_meta( get_the_ID(), 'hm_settings_unlimited_listings', true );

$featured_listings = get_post_meta( get_the_ID(), 'hm_settings_featured_listings', true );
$package_price = get_post_meta( get_the_ID(), 'hm_settings_package_price', true );
$stripe_id = get_post_meta( get_the_ID(), 'hm_settings_stripe_id', true );
$visibility = get_post_meta( get_the_ID(), 'hm_settings_visibility', true );
$images_per_listing = get_post_meta( get_the_ID(), 'hm_settings_images_per_listing', true );
$unlimited_images = get_post_meta( get_the_ID(), 'hm_settings_unlimited_images', true );
$taxes = get_post_meta( get_the_ID(), 'hm_settings_taxes', true );
$popular_featured = get_post_meta( get_the_ID(), 'hm_settings_popular_featured', true );
$custom_link = get_post_meta( get_the_ID(), 'hm_settings_custom_link', true );
$detail_link = get_post_permalink(get_the_ID());
$membership_settings = get_option('hm_memberships_options');
$currency = isset($membership_settings['currency'])?$membership_settings['currency']:'USD';
?>
<div class="price-table-module">
    <div class="price-table-title"><?php the_title(); ?></div><!-- price-table-title -->
    <div class="price-table-price-wrap">
        <span class="price-table-currency"><?php echo esc_attr($currency); ?></span>
        <span class="price-table-price"><?php echo esc_attr($package_price); ?></span>
    </div><!-- price-table-price-wrap -->
    <div class="price-table-description">
        <ul class="list-unstyled">
            <li>
                <i class="fa fa-check" aria-hidden="true"></i> <?php esc_html_e('Time Period', 'homey'); ?>: <strong><?php echo esc_attr($billing_frequency).' '.esc_html__(esc_attr($billing_period), 'homey'); ?></strong>
            </li>
            <li>
                <i class="fa fa-check" aria-hidden="true"></i> <?php esc_html_e('Listings', 'homey'); ?>: <strong><?php echo esc_attr($unlimited_listings) == 'on' ? esc_html_e__('Unlimited Listings', 'homey') : esc_attr($listings_included); ?></strong>
            </li>
            <li>
                <i class="fa fa-check" aria-hidden="true"></i> <?php esc_html_e('Featured Listings:', 'homey'); ?> <strong><?php echo esc_attr($featured_listings) < 1 ? 0 : esc_attr($featured_listings); ?></strong>
            </li>
            <?php $button_title = ''; if($args['currently_subscribed_id'] > -1 ){ ?>
                <?php  $expiry_date = get_post_meta($args['currently_subscribed_id'], 'hm_subscription_detail_expiry_date',true);
                $button_title = esc_html__("Expiry Date:", 'homey').$expiry_date;
            } ?>
        </ul>
    </div><!-- price-table-description -->
    <div class="price-table-button">
        <?php $subscription_info = get_active_membership_plan();

        $is_expired_package = 0;
        if(isset($subscription_info['subscriptionExpiryDate'])){
            if(!empty($subscription_info['subscriptionExpiryDate'])){
                $expiry_date_unix = strtotime($subscription_info['subscriptionExpiryDate']);
                if($expiry_date_unix < strtotime(date('d-m-Y'))){
                    $is_expired_package = 1;
                }
            }
        }

        ?>
        <?php $plan_message = $args['currently_subscribed_plan'] > 0 && $is_expired_package < 1 ? esc_html__('Your Active Plan', 'homey') : esc_html__('Get Started', 'homey');?>
        <?php $detail_link = $args['currently_subscribed_plan'] > 0 && $is_expired_package < 1 ? 'javascript:void(0)': $detail_link;?>
        <?php $button_class = $args['currently_subscribed_plan'] > 0 && $is_expired_package < 1 ? 'success': 'primary';?>
        <a class="btn btn-<?php echo esc_attr($button_class); ?>" title="<?php echo esc_attr($button_title); ?>" href="<?php echo esc_url($detail_link); ?>"><?php echo esc_attr($plan_message); ?></a>
    </div><!-- price-table-button -->
</div><!-- taxonomy-grids-module -->