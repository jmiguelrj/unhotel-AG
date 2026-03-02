<?php
global $current_user, $post, $homey_local, $reservation_page_link, $wallet_page_link, $earnings_page_link, $payout_request_link, $payouts_page_link;
$payouts = homey_get_host_payouts($limit = 100);
$dashboard_profile = homey_get_template_link_dash('template/dashboard-profile.php');
$payment_method_setup = add_query_arg( 'dpage', 'payment-method', $dashboard_profile );
?>
<div class="block">
    <div class="block-title">
        <div class="block-left">
            <h2 class="title"><?php esc_html_e('History', 'homey'); ?></h2>
        </div>
        <div class="block-right">
            <a href="<?php echo esc_url($payment_method_setup); ?>" class="btn btn-primary btn-slim"><?php esc_html_e('Setup Payout Method', 'homey'); ?></a>
        </div>
    </div>

    <div class="table-block dashboard-withdraw-table dashboard-table">
        <?php if(!empty($payouts)) { ?>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><?php esc_html_e('Date Requested', 'homey')?></th>
                    <th><?php esc_html_e('ID', 'homey'); ?></th>
                    <th><?php esc_html_e('Amount', 'homey'); ?></th>
                    <th><?php esc_html_e('Payout Method', 'homey'); ?></th>
                    <th><?php esc_html_e('Status', 'homey'); ?></th>
                    <th><?php esc_html_e('Date Processed', 'homey'); ?></th>
                    <th><?php esc_html_e('Actions', 'homey'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($payouts as $payout) {
                    $payout_id = $payout->payout_id;
                    $amount = $payout->total_amount;
                    $host = $payout->user_id;
                    $payout_method = $payout->payout_method;
                    $payout_status = $payout->payout_status;
                    $transfer_fee = $payout->transfer_fee;
                    $payout_method_data = $payout->payout_method_data;
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

                    $single_payout = add_query_arg( 
                        array(
                            'dpage' => 'payout-detail',
                            'payout_id' => $payout_id
                        ),$wallet_page_link 
                    );

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
                        <?php echo homey_format_date_simple(esc_attr($request_date)); ?><br/>
                        <?php echo esc_html__('at', 'homey'); ?>
                        <?php echo esc_attr($request_time); ?>
                    </td>
                    <td data-label="<?php esc_html_e('ID', 'homey'); ?>">
                        <?php echo esc_attr($payout_id); ?>
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
                    
                    <td data-label="<?php esc_html_e('Payout Method', 'homey'); ?>">
                        <?php 

                            if(!empty($payout_method)) {
                                echo homey_get_payout_method($payout_method).' '; 
                                if($payout_method == 'paypal' || $payout_method == 'skrill') {
                                    echo esc_html__('to', 'homey').' '.esc_attr($payout_method_data);
                                }
                            } else {
                                echo esc_html__('Payment Adjustment', 'homey');
                            }
                        ?>
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
                            echo homey_format_date_simple(esc_attr($processed_date)).'<br/>';
                            echo esc_html__('at', 'homey'); 
                            echo ' '.esc_attr($processed_time);
                        }
                        ?>
                    </td>
                    <td data-label="<?php esc_html_e('Actions', 'homey'); ?>">
                        <div class="custom-actions">
                            <button class="btn btn-secondary" onclick="window.location.href='<?php echo esc_url($single_payout); ?>'"><?php esc_html_e('Details', 'homey'); ?></button>
                        </div>
                    </td>
                </tr>

                <?php } ?>

            </tbody>
        </table>
        <?php } else { ?>
            <div class="block-body">
                <?php esc_html_e('At the moment there are no payouts.', 'homey'); ?>
            </div>
        <?php } ?>
    </div>
</div><!-- .block -->
