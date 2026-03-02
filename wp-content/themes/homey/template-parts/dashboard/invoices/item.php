<?php
/**
 * Invoices - template/user_dashboard_invoices
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 07/04/16
 * Time: 11:34 PM
 */
global $homey_local, $dashboard_invoices;
$invoice_data = homey_get_invoice_meta( get_the_ID() );
$user_info = get_userdata($invoice_data['invoice_buyer_id']);
$invoice_detail = add_query_arg( 'invoice_id', get_the_ID(), $dashboard_invoices );
?>
<tr>
    <td data-label="<?php esc_html_e('Order', 'homey'); ?>">
        #<?php echo get_the_ID(); ?>
        <?php $wc_reference_order_id = get_post_meta( get_the_ID(), 'wc_reference_order_id', true); 
                echo $wc_reference_order_id > 0 ? 'wc#'.$wc_reference_order_id : ''; ?>

        <?php
        if(homey_is_renter()) {
            $reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
        } else {
        
            if(!homey_listing_guest(get_the_ID())) {
                $reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
            } else {
                $reservation_page_link = homey_get_template_link('template/dashboard-reservations2.php');
            }
        }     

        $wc_reservation_reference_id = get_post_meta( get_the_ID(), 'wc_reservation_reference_id', true); 
        $detail_link = '';
        if($wc_reservation_reference_id > 0) {
            $detail_link = add_query_arg( 'reservation_detail', $wc_reservation_reference_id, $reservation_page_link );
        }
                echo  $wc_reservation_reference_id > 0 ? '<a href="' .$detail_link.'" title="Reservation">resvr#'.$wc_reservation_reference_id.'</a>' : ''; ?>

        <?php $wc_order_id = get_wc_order_id(get_the_ID()); if($wc_order_id > 0) echo 'wc#'.$wc_order_id; ?>

    </td>
    <td data-label="<?php esc_html_e('Date', 'homey'); ?>">
        <?php echo get_the_date(homey_convert_date(homey_option('homey_date_format'))); ?>
    </td>
    <td data-label="<?php echo esc_attr($homey_local['billing_for']); ?>">
        <?php

        if($invoice_data['invoice_billion_for'] == 'reservation') {
            
            echo esc_attr($homey_local['resv_fee_text']);

        } elseif($invoice_data['invoice_billion_for'] == 'listing') {
            if( $invoice_data['upgrade'] == 1 ) {
                echo esc_attr($homey_local['upgrade_text']);

            } else {
                echo get_the_title( get_post_meta( get_the_ID(), 'homey_invoice_item_id', true) );
            }
        } elseif($invoice_data['invoice_billion_for'] == 'upgrade_featured') {
                echo esc_attr($homey_local['upgrade_text']);
                
        } elseif($invoice_data['invoice_billion_for'] == 'package') {
            echo esc_attr($homey_local['inv_package']);
        }

        ?>
    </td>
    <td data-label="<?php esc_html_e('Billing Type', 'homey'); ?>">
        <?php echo esc_html_e( $invoice_data['invoice_billing_type'], 'homey' ); ?>
    </td>
    <td data-label="<?php esc_html_e('Status', 'homey'); ?>">
        <?php
        $invoice_status = get_post_meta(  get_the_ID(), 'invoice_payment_status', true );
        if( $invoice_status == 0 ) {
            echo '<span class="label label-warning">'.esc_attr($homey_local['not_paid']).'</span>';
        } else {
            echo '<span class="label label-success">'.esc_attr($homey_local['paid']).'</span>';
        }
        ?>
    </td>
    
    
    <td data-label="<?php esc_html_e('Payment Method', 'homey'); ?>">
        <?php echo esc_html__($invoice_data['invoice_payment_method'], 'homey');?>
    </td>
    <td data-label="<?php esc_html_e('Total', 'homey'); ?>">
        <strong><?php $reservation_meta = get_post_meta($invoice_data['invoice_item_id'], 'reservation_meta', true);
      
        $upfront_payment = isset($reservation_meta['upfront'])?$reservation_meta['upfront']:0;

        $services_fee = isset($reservation_meta['services_fee'])?$reservation_meta['services_fee']:0;
        
        $is_host = false;
        $homey_invoice_buyer = get_post_meta($invoice_data['invoice_item_id'], 'listing_renter', true);

        if( homey_is_host() && $homey_invoice_buyer != get_current_user_id() ) {
            $is_host = true;
        }

        if($is_host && !empty($services_fee)) {
                $upfront_payment = $upfront_payment - $services_fee;
            }

            $extra_expenses = homey_get_extra_expenses($invoice_data['invoice_item_id']);
        $extra_discount = homey_get_extra_discount($invoice_data['invoice_item_id']);

        if($is_host && !empty($services_fee)) {
            $upfront_payment = $upfront_payment - $services_fee;
        }

        if(!empty($extra_expenses)) {
            $expenses_total_price = $extra_expenses['expenses_total_price'];
            $upfront_payment = $upfront_payment + $expenses_total_price;
        }

        if(!empty($extra_discount)) {
            $discount_total_price = $extra_discount['discount_total_price'];
            //zahid.k added for discount
            $upfront_payment = $upfront_payment - $discount_total_price;
            //zahid.k added for discount
        }

        if(isset($invoice_data['invoice_billion_for']) && 'upgrade_featured' == $invoice_data['invoice_billion_for']){
            $upfront_payment = $invoice_data['invoice_item_price'];
        }

    echo homey_formatted_price( $upfront_payment );?></strong>
    </td>
    <td data-label="<?php esc_html_e('Actions', 'homey'); ?>">
        <div class="custom-actions">
            <button class="btn btn-secondary" onclick="location.href='<?php echo esc_url($invoice_detail); ?>';">
                <?php echo esc_attr($homey_local['inv_btn_details']);?>
            </button>
        </div>
    </td>
</tr>
