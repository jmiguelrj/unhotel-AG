<?php
global $current_user;
$current_user = wp_get_current_user();
$userID       = $current_user->ID;

$payment_type = get_user_meta($userID, 'host_reservation_payment', true);
$booking_percent = get_user_meta($userID, 'host_booking_percent', true);

if(!homey_is_renter()) {
?>
<div class="user-sidebar-inner">
    <div class="block">
        <div class="block-body">
            <h3><?php esc_html_e('Payment while booking', 'homey'); ?></h3>
            
            <div id="msg_alert" class="alert alert-success alert-dismissible" role="alert" style="display: none;">
                <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
                <span></span>
            </div>
            <form>  
                <div class="block-section-50">
                    <div class="form-group">
                        <select id="host_payment_option" class="selectpicker" data-live-search="false" data-live-search-style="begins">
                            <option value=""><?php echo esc_html__('Select', 'homey'); ?></option>
                            <option <?php selected($payment_type, 'full'); ?> value="full"><?php echo esc_html__('Full Payment', 'homey'); ?></option>
                            <option <?php selected($payment_type, 'percent'); ?> value="percent"><?php echo esc_html__('Percentage(%)', 'homey'); ?></option>
                            <option <?php selected($payment_type, 'only_security'); ?> value="only_security"><?php echo esc_html__('Security Deposit', 'homey'); ?></option>
                            <option <?php selected($payment_type, 'no_upfront'); ?> value="no_upfront"><?php echo esc_html__('No upfront, Take full payment locally', 'homey'); ?></option>
                        </select>
                    </div>
                    <div class="form-group host-percentage" <?php if($payment_type == 'percent'){?> style="display: block;"<?php }; ?>>
                        <input type="number" required class="form-control" name="payment_percent" id="payment_percent" value="<?php echo esc_attr($booking_percent); ?>" placeholder="<?php echo esc_html__('Enter how many % payment required while booking.', 'homey'); ?>">
                    </div>
                </div>
                             
            </form>

            <button id="save_host_payment_option" class="btn btn-success btn-full-width"><?php echo esc_html__('Save', 'homey'); ?></button>
        </div>
    </div>
</div>
<?php } ?>