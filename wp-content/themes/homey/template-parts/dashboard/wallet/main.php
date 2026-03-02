<?php
global $current_user, $reservation_page_link, $wallet_page_link, $earnings_page_link, $payout_request_link;
$current_user = wp_get_current_user();
$userID       = $current_user->ID;
$local = homey_get_localization();
$allowded_html = array();

$dashboard_profile = homey_get_template_link_dash('template/dashboard-profile.php');
$payment_method_setup = add_query_arg( 'dpage', 'payment-method', $dashboard_profile );

if(isset($_GET['host']) && $_GET['host'] != '') {
    $host_id = $_GET['host'];
    $userID = $host_id;
} else {
    $host_id = null;
}

$host_earnings = homey_get_earnings($limit = 5, $host_id);
$payouts = homey_get_host_payouts($limit = 5, $host_id);
$available_balance = homey_get_host_available_earnings($userID);
$total_earnings = homey_get_host_total_earnings($userID);
?>

<div class="wallet-box-wrap">
    <div class="row">
        <div class="col-sm-4 col-xs-12">
            <div class="wallet-box">
                <div class="block-big-text">
                    <?php 
                    if($total_earnings != 0) {
                        echo homey_formatted_price($total_earnings); 
                    } else {
                        echo homey_simple_currency_format($total_earnings);
                    }
                    ?>
                </div>
                <h3><?php esc_html_e('Total Earnings', 'homey'); ?> <span class="wallet-label"><?php esc_html_e('Host Fee:', 'homey'); ?> <?php homey_host_fee_percent(); ?>%</span></h3>
                <div class="wallet-box-info"><?php esc_html_e('Excluding the service fee, the host fee and the security deposit', 'homey'); ?></div>

                <?php if(empty($host_id) && homey_is_host()) { ?>
                <a class="btn btn-primary btn-slim" href="<?php echo esc_url($earnings_page_link); ?>"><?php esc_html_e('Details', 'homey'); ?></a>
                <?php } ?>
            </div>
        </div>
        <div class="col-sm-4 col-xs-12">
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
                <h3><?php esc_html_e('Available Balance', 'homey'); ?></h3>
                <div class="wallet-box-info"><?php esc_html_e('Represents the available amount you can currently withdraw to your account.', 'homey'); ?></div>

                <?php if(empty($host_id) && homey_is_host()) { ?>
                <a class="btn btn-primary btn-slim" href="<?php echo esc_url($payout_request_link); ?>"><?php esc_html_e('Request a Payout', 'homey'); ?></a>
                <?php } elseif(homey_is_admin()) { ?>
                    <a class="btn btn-primary btn-slim" href="#" data-toggle="modal" data-target="#modal-adjustment"><?php esc_html_e('Add Adjustment', 'homey'); ?></a>
                        <?php get_template_part('template-parts/dashboard/wallet/adjustment-form'); ?>
                <?php } ?>
            </div>
        </div>
        <div class="col-sm-4 col-xs-12">
            <div class="wallet-box">
                <div class="block-big-text"><?php echo homey_reservation_count($userID); ?></div>
                <h3><?php esc_html_e('Total reservations', 'homey'); ?></h3>
                <div class="wallet-box-info"><?php esc_html_e('Represents the total number of paid reservations you have received', 'homey'); ?></div>

                <?php if(empty($host_id) && homey_is_host()) { ?>
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
                        <h2 class="title"><?php esc_html_e('Earnings', 'homey'); ?></h2>
                    </div>
                    
                    <?php if(!empty($host_earnings)) { ?>
                    <?php if(empty($host_id) && homey_is_host()) { ?>
                    <div class="block-right">
                        <a class="btn btn-primary btn-slim" href="<?php echo esc_url($earnings_page_link); ?>"><?php esc_html_e('View All', 'homey'); ?></a>
                    </div>
                    <?php } ?>
                    <?php } ?>
                </div>

                <?php if(!empty($host_earnings)) { ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Order id', 'homey'); ?></th>
                            <th><?php esc_html_e('Date', 'homey'); ?></th>
                            <th><?php esc_html_e('Listing', 'homey'); ?></th>
                            <th><?php esc_html_e('Net Amount', 'homey'); ?></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        foreach ($host_earnings as $data) { 
                            $ID = $data->id;
                            $reservation_id = $data->reservation_id;
                            $listing_id = $data->listing_id;
                            $services_fee = $data->services_fee;
                            $host_fee = $data->host_fee;
                            $net_earnings = $data->net_earnings;
                            $datetime = $data->time;

                            $datetime_unix = strtotime($datetime);
                            $date = homey_return_formatted_date($datetime_unix);
                            $time = homey_get_formatted_time($datetime_unix);

                            $resrv_link = add_query_arg( 'reservation_detail', $reservation_id, $reservation_page_link );

                            $earning_detail = add_query_arg( 'detail', $ID, $wallet_page_link );
                        ?>
                        <tr>
                            <td data-label="<?php esc_html_e('Order id', 'homey'); ?>">
                                <a href="#"><?php echo esc_attr($ID); ?></a>
                            </td>
                            <td data-label="<?php esc_html_e('Date', 'homey'); ?>">
                                <?php echo homey_format_date_simple(esc_attr($date)); ?>
                            </td>
                            <td data-label="<?php esc_html_e('Listing', 'homey'); ?>">
                                <a href="<?php echo esc_url(get_permalink($listing_id)); ?>"><?php echo get_the_title($listing_id); ?></a>
                            </td>
                            <td data-label="<?php esc_html_e('Net Amount', 'homey'); ?>">
                                <strong><?php echo homey_formatted_price($net_earnings); ?></strong>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php } else { ?>
                    <div class="block-body">
                        <?php esc_html_e('At the moment there are no earnings.', 'homey'); ?>
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
                    <?php if(empty($host_id) && homey_is_host()) { ?>
                    <div class="block-right">
                        <a class="btn btn-primary btn-slim" href="<?php echo esc_url($payment_method_setup); ?>"><?php esc_html_e('Setup Payout Method', 'homey'); ?></a>
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
                                <?php echo homey_format_date_simple(esc_attr($date_requested)); ?><br/>
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
                                    echo homey_format_date_simple($date_processed);
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
