<?php
global $homey_local, $dashboard_invoices, $current_user;
wp_get_current_user();
$userID         = $current_user->ID;
$user_login     = $current_user->user_login;
$user_address = get_user_meta( $userID, 'homey_street_address', true);

$invoice_id = $_GET['invoice_id'];
$post = get_post( $invoice_id );
$invoice_data = homey_get_invoice_meta( $invoice_id );
$invoice_item_id = $invoice_data['invoice_item_id'];

$publish_date = $post->post_date;
$publish_date = date_i18n( homey_convert_date(homey_option('homey_date_format')), strtotime( $publish_date ) );
$invoice_logo = homey_option( 'invoice_logo', false, 'url' );
$invoice_company_name = homey_option( 'invoice_company_name' );
$invoice_additional_info = homey_option( 'invoice_additional_info' );

$homey_invoice_buyer = get_post_meta( $invoice_id, 'homey_invoice_buyer', true );

$user_info = get_userdata($homey_invoice_buyer);
$user_phone = get_user_meta( $homey_invoice_buyer, 'phone', true);

$user_email     = isset($user_info->user_email)?$user_info->user_email:'-';
$first_name     = isset($user_info->first_name)?$user_info->first_name:'-';
$last_name      = isset($user_info->last_name)?$user_info->last_name:'-';

if( !empty($first_name) && !empty($last_name) ) {
    $fullname = $first_name.' '.$last_name;
} else {
    $fullname = $user_info->display_name;
}

$is_reservation_invoice = false;
if($invoice_data['invoice_billion_for'] == 'reservation') {
    $is_reservation_invoice = true;
}

if($invoice_data['invoice_billion_for'] == 'reservation') {
            
    $billing_for_text = $homey_local['resv_fee_text'];

} elseif($invoice_data['invoice_billion_for'] == 'listing') {
    if( $invoice_data['upgrade'] == 1 ) {
        $billing_for_text =  $homey_local['upgrade_text'];

    } else {
        $billing_for_text =  get_the_title( get_post_meta( get_the_ID(), 'homey_invoice_item_id', true) );
    }
} elseif($invoice_data['invoice_billion_for'] == 'upgrade_featured') {
        $billing_for_text =  $homey_local['upgrade_text'];
        
} elseif($invoice_data['invoice_billion_for'] == 'package') {
    $billing_for_text =  $homey_local['inv_package'];
}
  $logged_in_user = get_current_user_id();
?>
<div class="invoice-detail block">
    <?php
    if(homey_is_admin() || $invoice_data['invoice_resv_owner'] == $logged_in_user
            || $invoice_data['invoice_buyer_id'] == $logged_in_user){ ?>
    <div class="invoice-header clearfix">
        <div class="block-left">
            <div class="invoice-logo">
                <?php if( !empty($invoice_logo) ) { ?>
                    <img src="<?php echo esc_url($invoice_logo); ?>" alt="<?php esc_attr_e('logo', 'homey');?>">
                <?php } ?>
            </div>
            <ul class="list-unstyled">
                <?php if( !empty($invoice_company_name) ) { ?>
                    <li><strong><?php echo esc_attr($invoice_company_name); ?></strong></li>
                <?php } ?>
                <li><?php echo homey_option( 'invoice_address' ); ?></li>
            </ul>
        </div>
        <div class="block-right">
            <ul class="list-unstyled">
                <li><strong><?php esc_html_e('Invoice:', 'homey'); ?></strong> <?php echo esc_attr($invoice_id); ?></li>
                <li><strong><?php esc_html_e('Date:', 'homey'); ?></strong> <?php echo esc_attr($publish_date); ?></li>

                <?php if($is_reservation_invoice) { ?>
                    <li><strong><?php esc_html_e('Reservation ID:', 'homey'); ?></strong> <?php echo esc_attr($invoice_item_id); ?></li>
                <?php } ?>
            </ul>
        </div>
    </div><!-- invoice-header -->

    <div class="invoice-body clearfix">
        <ul class="list-unstyled">
            <li><strong><?php echo esc_html__('To:', 'homey'); ?></strong></li>
            <li><?php echo esc_attr($fullname); ?></li>
            <li><?php echo esc_html__('Email:', 'homey'); ?> <?php echo esc_attr($user_email);?></li>
            <li><?php echo esc_html__('Phone:', 'homey'); ?> <?php echo esc_attr($user_phone);?></li>
        </ul>
        <h2 class="title"><?php esc_html_e('Details', 'homey'); ?></h2> 

        <?php 
        if($is_reservation_invoice) { 
            $resv_id = $invoice_item_id;
            $is_hourly = get_post_meta($resv_id, 'is_hourly', true);
            if($is_hourly == 'yes') {
                echo homey_calculate_hourly_reservation_cost($resv_id);
            } else {
                echo homey_calculate_reservation_cost($resv_id); 
            }
            
        } else {
            echo '<div class="payment-list"><ul>';
                echo '<li>'.$homey_local['billing_for'].' <span>'.$billing_for_text.'</span></li>';
                echo '<li>'.$homey_local['billing_type'].' <span>'.esc_html( $invoice_data['invoice_billing_type'] ).'</span></li>';
                echo '<li>'.$homey_local['inv_pay_method'].' <span>'.esc_html($invoice_data['invoice_payment_method']).'</span></li>';
            $price_is_zero = homey_formatted_price( $invoice_data['invoice_item_price'] );
            echo '<li class="payment-due gf">'.$homey_local['inv_total'].' <span>'.$price_is_zero != '' ? $price_is_zero : "0".'</span></li>';
            echo '<input type="hidden" name="is_valid_upfront_payment" id="is_valid_upfront_payment" value="'.$invoice_data['invoice_item_price'].'">';

            echo '</ul></div>';
        }
        ?>

    </div><!-- invoice-body -->

    <?php if( !empty($invoice_additional_info)) { ?>
    <div class="invoice-footer clearfix">
        <dl>
            <dt><?php echo esc_html__('Additional Information:', 'homey'); ?></dt>
            <dd><?php echo homey_option( 'invoice_additional_info' ); ?></dd>
        </dl>
    </div><!-- invoice-footer -->
    <?php } ?>
<?php }else{ ?>
        <div class="invoice-body clearfix">
            <h3><?php echo __("You are not allowed to see this."); ?></h3>
        </div>
<?php } ?>
</div>
