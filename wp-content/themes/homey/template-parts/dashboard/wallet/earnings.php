<?php
global $reservation_page_link, $wallet_page_link, $earnings_page_link, $payout_request_link;
$limit = 10;
$host_earnings = homey_get_earnings($limit);
?>
<div class="block">
    <div class="block-title">
        <div class="block-left">
            <h2 class="title"><?php esc_html_e('History', 'homey'); ?></h2>
        </div>
        <div class="block-right">
            <div class="btn btn-primary btn-slim"><?php esc_html_e('Fee:', 'homey'); ?> <?php homey_host_fee_percent(); ?>%</div>
        </div>
    </div>
    <div class="table-block dashboard-withdraw-table dashboard-table">
        <?php if(!empty($host_earnings)) { ?>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><?php esc_html_e('Order id', 'homey'); ?></th>
                    <th><?php esc_html_e('Reservation id', 'homey'); ?></th>
                    <th><?php esc_html_e('Date', 'homey'); ?></th>
                    <th><?php esc_html_e('Listing', 'homey'); ?></th>
                    <th><?php esc_html_e('Service Fee', 'homey'); ?></th>
                    <th><?php esc_html_e('Host Fee', 'homey'); ?></th>
                    <th><?php esc_html_e('Net Amount', 'homey'); ?></th>
                    <th><?php esc_html_e('Actions', 'homey'); ?></th>
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
                        <?php echo esc_attr($ID); ?>
                    </td>
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
                    <td data-label="<?php esc_html_e('Service Fee', 'homey'); ?>">
                        <?php 
                        if(!empty($services_fee)) {
                            echo homey_formatted_price($services_fee); 
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td data-label="<?php esc_html_e('Host Fee', 'homey'); ?>">
                        <?php echo homey_formatted_price($host_fee); ?>
                    </td>
                    <td data-label="<?php esc_html_e('Net Amount', 'homey'); ?>">
                        <strong><?php echo homey_formatted_price($net_earnings); ?></strong>
                    </td>
                    <td data-label="<?php esc_html_e('Actions', 'homey'); ?>">
                        <div class="custom-actions">
                            <button class="btn btn-secondary" onclick="window.location.href='<?php echo esc_url($earning_detail); ?>'">  <?php esc_html_e('Details', 'homey'); ?>
                            </button>
                        </div>
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
</div><!-- .block -->
