<?php
global $reservation_page_link, $wallet_page_link, $earnings_page_link, $payout_request_link;
$limit = 10;
$guest_security_deposits = homey_get_security_deposit($limit);
?>
<div class="block">
    <div class="block-title">
        <div class="block-left">
            <h2 class="title"><?php esc_html_e('History', 'homey'); ?></h2>
        </div>
    </div>
    <div class="table-block dashboard-withdraw-table dashboard-table">
        <?php if(!empty($guest_security_deposits)) { ?>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><?php esc_html_e('Reservation id', 'homey'); ?></th>
                    <th><?php esc_html_e('Date', 'homey'); ?></th>
                    <th><?php esc_html_e('Listing', 'homey'); ?></th>
                    <th><?php esc_html_e('Security Deposit', 'homey'); ?></th>
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
</div><!-- .block -->
