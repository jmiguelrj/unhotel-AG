<?php
global $current_user, $reservation_page_link, $wallet_page_link, $earnings_page_link, $payout_request_link, $payouts_setup_page, $security_deposits_page, $payouts_page_link;
$current_user = wp_get_current_user();
$userID       = $current_user->ID;
$local = homey_get_localization();
$allowded_html = array();

if(isset($_GET['guest']) && $_GET['guest'] != '') {
    $guest_id = $_GET['guest'];
    $userID = $guest_id;
} else {
    $guest_id = null;
}

$guest_security_deposits = homey_get_security_deposit($limit = 5, $guest_id);
$payouts = homey_get_host_payouts($limit = 5, $guest_id);
$available_balance = homey_get_get_security_deposit($userID);
?>

<div class="wallet-box-wrap">
    <div class="row">
        <div class="col-sm-6 col-xs-12">
            <div class="wallet-box">
                <div class="block-big-text">
                    <?php 
                    if($available_balance != 0) {
                        echo homey_formatted_price($available_balance); 
                    } else {
                        echo homey_simple_currency_format($available_balance);
                    }
                    ?>
                </div>
                <h3><?php esc_html_e('Security Deposit', 'homey'); ?> </h3>
                <div class="wallet-box-info"><?php esc_html_e('Refundable Security deposit', 'homey'); ?></div>

                <?php if(empty($guest_id) && homey_is_renter()) { ?>
                <a class="btn btn-primary btn-slim" href="<?php echo esc_url($security_deposits_page); ?>"><?php esc_html_e('Details', 'homey'); ?></a>
                <?php } elseif(homey_is_admin()) { ?>
                    <a class="btn btn-primary btn-slim" href="#" data-toggle="modal" data-target="#modal-adjustment-guest"><?php esc_html_e('Add Adjustment', 'homey'); ?></a>
                        <?php get_template_part('template-parts/dashboard/wallet/adjustment-guest'); ?>
                <?php } ?>
            </div>
        </div>
        <div class="col-sm-6 col-xs-12">
            <div class="wallet-box">
                <div class="block-big-text"><?php echo homey_reservation_count($userID); ?></div>
                <h3><?php esc_html_e('Total reservations', 'homey'); ?></h3>
                <div class="wallet-box-info"><?php esc_html_e('Represents the total number of paid reservations you have received', 'homey'); ?></div>

                <?php if(empty($guest_id) && homey_is_renter()) { ?>
                <a class="btn btn-primary btn-slim" href="<?php echo esc_url($reservation_page_link); ?>"><?php esc_html_e('Manage', 'homey'); ?></a>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<div class="wallet-box-wrap">
    <div class="row">
        <div class="col-sm-6 col-xs-12">
            <div class="block table-block dashboard-withdraw-table dashboard-table">
                <div class="block-title">
                    <div class="block-left">
                        <h2 class="title"><?php esc_html_e('History', 'homey'); ?></h2>
                    </div>
                    
                    <?php if(!empty($guest_security_deposits)) { ?>
                    <?php if(empty($guest_id) && homey_is_renter()) { ?>
                    <div class="block-right">
                        <a class="btn btn-primary btn-slim" href="<?php echo esc_url($security_deposits_page); ?>"><?php esc_html_e('View All', 'homey'); ?></a>
                    </div>
                    <?php } ?>
                    <?php } ?>
                </div>

                <?php if(!empty($guest_security_deposits)) { ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Reservation id', 'homey'); ?></th>
                            <th><?php esc_html_e('Date', 'homey'); ?></th>
                            <th><?php esc_html_e('Listing', 'homey'); ?></th>
                            <th><?php esc_html_e('Security', 'homey'); ?></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        foreach ($guest_security_deposits as $data) { 
                            $ID = $data->id;
                            $reservation_id = $data->reservation_id;
                            $listing_id = $data->listing_id;
                            $security_deposit = $data->security_deposit;
                            $datetime = $data->time;

                            $datetime_unix = strtotime($datetime);
                            $date = homey_return_formatted_date($datetime_unix);
                            $time = homey_get_formatted_time($datetime_unix);

                            $resrv_link = add_query_arg( 'reservation_detail', $reservation_id, $reservation_page_link );

                            $earning_detail = add_query_arg( 'detail', $ID, $wallet_page_link );
                        ?>
                        <tr>
                            <td data-label="<?php esc_html_e('Reservation id', 'homey'); ?>">
                                <a href="<?php echo esc_url($resrv_link); ?>"><?php echo esc_attr($reservation_id); ?></a>
                            </td>
                            <td data-label="<?php esc_html_e('Date', 'homey'); ?>">
                                <?php echo homey_format_date_simple(esc_attr($date)); ?><br/>
                                <?php echo esc_html__('at', 'homey'); ?>
                                <?php echo esc_attr($time); ?>
                            </td>
                            <td data-label="<?php esc_html_e('Listing', 'homey'); ?>">
                                <a href="<?php echo esc_url(get_permalink($listing_id)); ?>"><?php echo get_the_title($listing_id); ?></a>
                            </td>
                            <td data-label="<?php esc_html_e('Security Deposit', 'homey'); ?>">
                                <strong><?php echo homey_formatted_price($security_deposit); ?></strong>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php } else { ?>
                    <div class="block-body">
                        <?php esc_html_e('At the moment there are no record.', 'homey'); ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="col-sm-6 col-xs-12">

            <div class="block table-block dashboard-withdraw-table dashboard-table">
                <div class="block-title">                           
                    <div class="block-left">
                        <h2 class="title"><?php esc_html_e('Payouts', 'homey'); ?></h2>
                    </div>
                    <?php if(empty($guest_id) && homey_is_renter()) { ?>
                    <div class="block-right">
                        <a class="btn btn-primary btn-slim" href="<?php echo esc_url($payouts_page_link); ?>"><?php esc_html_e('View All', 'homey'); ?></a>
                    </div>
                    <?php } ?>
                </div>
                <?php if(!empty($payouts)) { ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Date Requested', 'homey')?></th>
                            <th><?php esc_html_e('Amount', 'homey'); ?></th>
                            <th><?php esc_html_e('Status', 'homey'); ?></th>
                            <th><?php esc_html_e('Date Processed', 'homey'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($payouts as $payout) {
                            $amount = $payout->total_amount;
                            $payout_status = $payout->payout_status;
                            $transfer_fee = $payout->transfer_fee;
                            $date_requested = $payout->date_requested;
                            $date_processed = $payout->date_processed;

                            $date_requested_unix = strtotime($date_requested);
                            $request_date = homey_return_formatted_date($date_requested_unix);
                            $request_time = homey_get_formatted_time($date_requested_unix);

                            $date_processed_unix = strtotime($date_processed);
                            $processed_date = homey_return_formatted_date($date_processed_unix);
                            $processed_time = homey_get_formatted_time($date_processed_unix);

                            $price_prefix = '';
                            $payout_action = $payout->action;
                            if($payout_action == 'add_money') {
                                $price_prefix = '+';
                            } elseif($payout_action == 'deduct_money') {
                                $price_prefix = '-';
                            }

                            if($payout_status == 1) {
                                $class = 'warning';
                            } elseif($payout_status == 2) {
                                $class = 'default';
                            } elseif($payout_status == 3) {
                                $class = 'success';
                            }elseif($payout_status == 4) {
                                $class = 'danger';
                            }
                        ?>

                        <tr>
                            <td data-label="<?php esc_html_e('Date Requested', 'homey')?>">
                                <?php echo esc_attr($request_date); ?><br/>
                                <?php echo esc_html__('at', 'homey'); ?>
                                <?php echo esc_attr($request_time); ?>
                            </td>
                            <td data-label="<?php esc_html_e('Amount', 'homey'); ?>">
                                <?php echo esc_attr($price_prefix).' '.homey_formatted_price($amount); ?>
                                <?php if(!empty($transfer_fee)) { ?>
                                <br>
                                <span class="less-fee">
                                    (<?php esc_html_e('less', 'homey');  
                                    echo ' '.homey_formatted_price($transfer_fee).' '; 
                                    esc_html_e('transaction fee', 'homey'); ?>)
                                </span>
                                <?php } ?>
                            </td>
                            <td data-label="<?php esc_html_e('Status', 'homey'); ?>">
                                <span class="label label-<?php echo esc_attr($class); ?>">
                                    <?php echo homey_get_payout_status($payout_status); ?>        
                                </span>
                            </td>
                            <td data-label="<?php esc_html_e('Date Processed', 'homey'); ?>">
                                <?php 
                                if($date_processed == '0000-00-00 00:00:00') {
                                    echo '-';
                                } else {
                                    echo esc_attr($processed_date).'<br/>';
                                    echo esc_html__('at', 'homey'); 
                                    echo ' '.esc_attr($processed_time);
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                        }
                        ?>

                    </tbody>
                </table>
                <?php } else { ?>
                    <div class="block-body">
                        <?php esc_html_e('At the moment there are no payouts.', 'homey'); ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
