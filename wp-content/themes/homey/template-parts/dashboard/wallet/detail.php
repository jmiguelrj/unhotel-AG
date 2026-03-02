<?php
global $homey_local, $reservation_page_link, $wallet_page_link, $earnings_page_link, $payout_request_link;

$earning_id = isset($_GET['detail']) ? $_GET['detail'] : '';
$earning_detail = homey_get_earning_detail($earning_id);
$resrv_link = '';

if(!empty($earning_detail)) {
    $ID = $earning_detail->id;
    $user_id = $earning_detail->user_id;
    $reservation_id = $earning_detail->reservation_id;
    $listing_id = $earning_detail->listing_id;
    $services_fee = $earning_detail->services_fee;
    $host_fee = $earning_detail->host_fee;
    $upfront_payment = $earning_detail->upfront_payment;
    $paid_locally = $earning_detail->payment_due;
    $security_deposit = $earning_detail->security_deposit;
    $total_amount = $earning_detail->total_amount;
    $net_earnings = $earning_detail->net_earnings;
    $chargeable_amount = $earning_detail->chargeable_amount;
    $host_fee_percent = $earning_detail->host_fee_percent;
    $datetime = $earning_detail->time;

    $datetime_unix = strtotime($datetime);
    $date = homey_return_formatted_date($datetime_unix);
    $time = homey_get_formatted_time($datetime_unix);
    $resrv_link = add_query_arg( 'reservation_detail', $reservation_id, $reservation_page_link );

    $renter_id = get_post_meta($reservation_id, 'listing_renter', true);
    $is_hourly = get_post_meta($reservation_id, 'is_hourly', true);

    if(homey_is_host()) {
        $total_amount = $total_amount - $services_fee;
        $upfront_payment = $upfront_payment - $services_fee;
    }
}

?>
<div class="block">
    <div class="block-head">
        <div class="block-left">
            <h2 class="title"><?php esc_html_e('Details', 'homey'); ?></h2>
        </div><!-- block-left -->
        <div class="block-right">
            <div class="custom-actions">
                <button class="btn-action" data-toggle="tooltip" data-placement="top" title="<?php echo esc_attr($homey_local['back_btn']); ?>" data-original-title="<?php echo esc_attr($homey_local['back_btn']); ?>" onclick="window.location.href='<?php echo esc_url($wallet_page_link); ?>'"> <i class="fa fa-mail-reply"></i></button>
            </div><!-- custom-actions -->
        </div><!-- block-right -->
    </div><!-- block-head -->
    
    <?php if(!empty($earning_detail)) { ?>
    <div class="block-section">
        <div class="block-body">
            <div class="block-left">
                <h2 class="title"><?php echo esc_attr($homey_local['reservation_label']); ?></h2>
            </div><!-- block-left -->
            <div class="block-right">
                <div class="payment-list">
                <ul>
                    <li><strong><?php esc_html_e('Reservation ID', 'homey'); ?>:</strong> <span><a href="<?php echo esc_url($resrv_link); ?>"><?php echo esc_attr($reservation_id); ?></a></span></li>
                    <li>
                        <strong><?php esc_html_e('Date', 'homey'); ?>:</strong> 
                        <span>
                        <?php echo homey_format_date_simple(esc_attr($date)); ?>
                        <?php echo esc_html__('at', 'homey'); ?>
                        <?php echo esc_attr($time); ?>
                        </span>
                    </li>

                    <li>
                        <strong><?php esc_html_e('Listing', 'homey'); ?>:</strong> 
                        <span><a href="<?php echo esc_url(get_permalink($listing_id)); ?>">
                            <?php echo get_the_title($listing_id); ?></a>
                        </span>
                    </li>
                    <li>
                        <strong><?php esc_html_e('From', 'homey'); ?>:</strong> 
                        <span><?php echo get_the_author_meta('display_name' , $renter_id); ?></span>
                    </li>
                </ul>
                </div>
            </div><!-- block-right -->
        </div><!-- block-body -->
    </div><!-- block-section -->  
    
    <div class="block-section">
        <div class="block-body">
            <div class="block-left">
                <h2 class="title"><?php echo esc_html__('Earnings', 'homey'); ?></h2>
            </div><!-- block-left -->

            <div class="block-right">
                <div class="payment-list earnings-detail-list">
                    <ul>
                        
                        <?php 
                        if($is_hourly == 'yes') {
                            echo homey_calculate_hourly_cost_for_wallet($reservation_id);
                        } else {
                            echo homey_calculate_cost_for_wallet($reservation_id); 
                        }
                        ?>
                        
                        <li class="sub-total"><?php esc_html_e('Gross Payment', 'homey'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="<?php esc_html_e('Includes taxes and fees', 'homey'); ?>" aria-hidden="true"></i></li>
                        <li><?php esc_html_e('Total amount', 'homey'); ?> <span><?php echo homey_formatted_price($total_amount); ?></span></li>
                        
                        <li class="payment-due"><?php esc_html_e('Paid', 'homey'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="<?php esc_html_e('upfront payment while booking', 'homey'); ?>" aria-hidden="true"></i> <span><?php echo homey_formatted_price($upfront_payment); ?></span></li>
                        
                        <?php if(!empty($paid_locally)) { ?>
                        <li><i class="fa fa-info-circle"></i> <?php echo esc_html__('Balance due of', 'homey').' '.homey_formatted_price($paid_locally).' '.esc_html__('paid locally to the host', 'homey'); ?></li>
                        <?php } ?>

                        <li class="sub-total"><?php esc_html_e('Sub Total', 'homey'); ?><span><?php echo homey_formatted_price($chargeable_amount); ?></span></li>

                        <li class="sub-total"><?php echo esc_html__('Fees', 'homey'); ?></li>
                        
                        <?php if(!homey_is_host()) { ?>
                        <li><?php echo esc_html__('Service Fee', 'homey'); ?> <span>- <?php echo homey_formatted_price($services_fee); ?></span></li>
                        <?php } ?>

                        <li><?php echo esc_html__('Host Fee', 'homey'); ?> (<?php echo esc_attr($host_fee_percent); ?>%) <span>- <?php echo homey_formatted_price($host_fee); ?></span></li>

                        <li class="sub-total"><?php echo esc_html__('Net Earnings', 'homey'); ?> </li>
                        <li><?php echo esc_html__('Total amount', 'homey'); ?><span><?php echo homey_formatted_price($net_earnings); ?></span></li>
                    </ul>
                </div>
            </div><!-- block-right -->
        </div><!-- block-body -->
    </div><!-- block-section -->
    <?php } ?>
</div><!-- .block -->
