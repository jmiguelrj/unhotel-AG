<?php
global $current_user, $homey_local, $homey_prefix, $reservationID, $owner_info, $renter_info, $renter_id, $owner_id;
$blogInfo = esc_url( home_url('/') );
wp_get_current_user();
$userID =   $current_user->ID;

$messages_page = homey_get_template_link_2('template/dashboard-messages.php');
$booking_hide_fields = homey_option('booking_hide_fields');
$booking_detail_hide_fields = homey_option('booking_detail_hide_fields');

$reservationID = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
$reservation_status = $notification = $status_label = $notification = '';
$upfront_payment = $check_in = $check_out = $guests = $pets = $renter_msg = '';
$payment_link = '';
if(!empty($reservationID)) {
    if(homey_is_renter()) {
        $back_to_list = homey_get_template_link('template/dashboard-reservations.php');
    } else {
        if(!homey_listing_guest($reservationID)) {
            $back_to_list = homey_get_template_link_2('template/dashboard-reservations.php');
        } else {
            $back_to_list = homey_get_template_link_2('template/dashboard-reservations2.php');
        }
    }

    $post = get_post($reservationID);    
    $current_date = date( 'Y-m-d', current_time( 'timestamp', 0 ));
    $current_date_unix = strtotime($current_date );

    $reservation_status = get_post_meta($reservationID, 'reservation_status', true);
    $total_price = get_post_meta($reservationID, 'reservation_total', true);
    $upfront_payment = get_post_meta($reservationID, 'reservation_upfront', true);
    $upfront_payment = homey_formatted_price($upfront_payment);
    $payment_link = homey_get_template_link_2('template/dashboard-payment.php');

    $check_in = get_post_meta($reservationID, 'reservation_checkin_date', true);
    $check_out = get_post_meta($reservationID, 'reservation_checkout_date', true);
    $guests = get_post_meta($reservationID, 'reservation_guests', true);
    $listing_id = get_post_meta($reservationID, 'reservation_listing_id', true);
    $pets   = get_post_meta($listing_id, $homey_prefix.'pets', true);
    $res_meta   = get_post_meta($reservationID, 'reservation_meta', true);

    $booking_type = homey_booking_type_by_id($listing_id);

    $extra_expenses = homey_get_extra_expenses($reservationID);
    $extra_discount = homey_get_extra_discount($reservationID);

    if(!empty($extra_expenses)) {
        $expenses_total_price = $extra_expenses['expenses_total_price'];
        $total_price = $total_price + $expenses_total_price;
    }

    if(!empty($extra_discount)) {
        $discount_total_price = $extra_discount['discount_total_price'];
        $total_price = $total_price - $discount_total_price;
    }

    if(homey_option('reservation_payment') == 'full') {
        $upfront_payment = homey_formatted_price($total_price); 
    }

    $renter_msg = isset($res_meta['renter_msg']) ? $res_meta['renter_msg'] : '';

    $renter_id = get_post_meta($reservationID, 'listing_renter', true);
    $renter_info = homey_get_author_by_id('60', '60', 'reserve-detail-avatar img-circle', $renter_id);

    $renter_name_while_booking  = get_user_meta($renter_id, 'first_name', true);
    $renter_name_while_booking .= ' '.get_user_meta($renter_id, 'last_name', true);
    $renter_phone = get_user_meta($renter_id, 'phone', true);

    $owner_id = get_post_meta($reservationID, 'listing_owner', true);
    $owner_info = homey_get_author_by_id('60', '60', 'reserve-detail-avatar img-circle', $owner_id);

    $payment_link = add_query_arg( array(
            'reservation_id' => $reservationID,
        ), $payment_link );

    $chcek_reservation_thread = homey_chcek_reservation_thread($reservationID);

    if($chcek_reservation_thread != '') {
        $messages_page_link = add_query_arg( array(
            'thread_id' => $chcek_reservation_thread
        ), $messages_page );
    } else {
        $messages_page_link = add_query_arg( array(
            'reservation_id' => $reservationID,
            'message' => 'new',
        ), $messages_page );
    }
    

    $guests_label = homey_option('cmn_guest_label');
    if($guests > 1) {
        $guests_label = homey_option('cmn_guests_label');
    }

}

if( !homey_give_access($reservationID) ) {
    echo('Are you kidding?');
    
} else {
?>
<div class="user-dashboard-right dashboard-with-sidebar">
    <div class="dashboard-content-area">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="dashboard-area">
                        <input type="hidden" id="resrv_id" value="<?php echo intval($reservationID); ?>">
                        <?php homey_reservation_notification($reservation_status, $reservationID); ?>

                        <div class="block">
                            <div class="block-head">
                                <div class="block-left">
                                    <h2 class="title"><?php echo esc_attr($homey_local['reservation_label']); ?>
                                        <?php $wc_order_id = get_wc_order_id(get_the_ID()); $wc_order_id_txt = $wc_order_id > 0 ? ', wc#'.$wc_order_id.' ' : ' '; ?>
                                        <?php echo '#'.$reservationID.$wc_order_id_txt.' '.homey_get_reservation_label($reservation_status, $reservationID); ?></h2>
                                </div><!-- block-left -->
                                <div class="block-right">
                                    <div class="custom-actions">
                                        
                                        <?php //if($reservation_status == 'booked' && $current_date_unix >= strtotime($check_in)) { ?>
                                        <?php if($reservation_status == 'booked') { ?>
                                        <button class="btn-action" data-toggle="collapse" data-target="#review-form" aria-expanded="false" aria-controls="collapseExample" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['review_btn']); ?>">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <?php } ?>

                                        <button onclick="window.print();" class="btn-action" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['print_btn']); ?>"><i class="fa fa-print"></i></button>

                                        <a href="<?php echo esc_url($messages_page_link); ?>" class="btn-action" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['msg_send_btn']); ?>"><i class="fa fa-envelope-open-o"></i></a>

                                        <?php if(is_invoice_paid_for_reservation($reservationID) == 0 || ($reservation_status != 'booked' && $reservation_status != 'cancelled' && $reservation_status != 'declined' && !homey_listing_guest($reservationID))) { ?>
                                            <a href="#" class="mark-as-paid btn-action" data-id="<?php echo esc_attr($reservationID); ?>" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_html__('Mark as Paid', 'homey'); ?>"><i class="fa fa-money"></i></a>
                                        <?php } ?>

                                        <?php if(!homey_listing_guest($reservationID)) { ?>
                                        <a href="#" class="reservation-delete btn-action" data-id="<?php echo esc_attr($reservationID); ?>" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_html__('Delete', 'homey'); ?>"><i class="fa fa-trash"></i></a>
                                        <?php } ?>

                                        <a href="<?php echo esc_url($back_to_list); ?>" class="btn-action" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr($homey_local['back_btn']); ?>"><i class="fa fa-mail-reply"></i></a>
                                    </div><!-- custom-actions -->
                                </div><!-- block-right -->
                            </div><!-- block-head -->

                            <?php 
                            if($reservation_status == 'booked' && homey_listing_guest($reservationID)) {
                                get_template_part('template-parts/dashboard/reservation/review-form'); 
                            } elseif($reservation_status == 'booked') {
                                get_template_part('template-parts/dashboard/reservation/review-host');
                            }
                            
                            get_template_part('template-parts/dashboard/reservation/add-extra-expenses');
                            get_template_part('template-parts/dashboard/reservation/discount');

                            if($reservation_status == 'declined') {
                                get_template_part('template-parts/dashboard/reservation/declined');

                            } elseif($reservation_status == 'cancelled') {
                                get_template_part('template-parts/dashboard/reservation/cancelled');
                            } else {

                                
                                    get_template_part('template-parts/dashboard/reservation/cancel-form');
                                
                                    if(!homey_listing_guest($reservationID)) {
                                        get_template_part('template-parts/dashboard/reservation/decline-form');
                                    }
                                
                            }

                            if($res_meta['no_of_days'] > 1) {
                                $night_label = ($booking_type == 'per_day_date') ? homey_option('glc_day_dates_label') : homey_option('glc_day_nights_label');
                            } else {
                                $night_label = ($booking_type == 'per_day_date') ? homey_option('glc_day_date_label') : homey_option('glc_day_night_label');
                            }

                            $no_of_weeks = isset($res_meta['total_weeks_count']) ? $res_meta['total_weeks_count'] : 0;
                            $no_of_months = isset($res_meta['total_months_count']) ? $res_meta['total_months_count'] : 0;

                            if($no_of_weeks > 1) {
                                $week_label = homey_option('glc_weeks_label');
                            } else {
                                $week_label = homey_option('glc_week_label');
                            }

                            if($no_of_months > 1) {
                                $month_label = homey_option('glc_months_label');
                            } else {
                                $month_label = homey_option('glc_month_label');
                            }
                                  
                            ?>
                            
                            <div class="block-section">
                                <div class="block-body">
                                    <div class="block-left">
                                        <ul class="detail-list">
                                            <li><strong><?php echo esc_attr($homey_local['date_label']); ?>:</strong></li>
                                            <li><?php echo translate_month_names(esc_attr( get_the_date( get_option( 'date_format' ), $reservationID )));?>
                                            <br>
                                            <?php echo esc_attr( get_the_date( homey_time_format(), $reservationID ));?> </li>
                                        </ul>
                                    </div><!-- block-left -->
                                    <div class="block-right">
                                        <?php if(!empty($renter_info['photo'])) {
                                            echo '<a href="'.esc_url($renter_info['link']).'" target="_blank">'.$renter_info['photo'].'</a>';
                                        }?>
                                        <ul class="detail-list">
                                            <li><strong><?php esc_html_e('From', 'homey'); ?>:</strong>
                                                <a href="<?php echo esc_url($renter_info['link']); ?>" target="_blank">
                                                    <?php echo esc_attr($renter_info['name']); ?>
                                                </a>
                                            </li>
                                            <?php if($booking_detail_hide_fields['renter_information_on_detail'] == 0){ ?>
                                                <li><strong><?php esc_html_e('Renter Detail', 'homey'); ?>:&nbsp;</strong><?php echo esc_attr($renter_name_while_booking).' <a title="'.esc_html__('Click to call', 'homey').'" href="tel:'.$renter_phone.'">'. $renter_phone; ?></a></li>
                                            <?php } ?>
                                            <li><strong><?php esc_html_e('Listing Name', 'homey'); ?>:&nbsp;</strong><?php echo get_the_title($listing_id); ?></li>
                                        </ul>
                                    </div><!-- block-right -->
                                </div><!-- block-body -->
                            </div><!-- block-section -->

                            <div class="block-section">
                                <div class="block-body">
                                    <div class="block-left">
                                        <h2 class="title"><?php esc_html_e('Details', 'homey'); ?></h2>
                                    </div><!-- block-left -->
                                    <div class="block-right">
                                        <ul class="detail-list detail-list-2-cols">
                                            <li>
                                                <?php echo esc_attr($homey_local['check_In']); ?>: 
                                                <strong><?php echo homey_format_date_simple($check_in); ?></strong>
                                            </li>
                                            <li>
                                                <?php echo esc_attr($homey_local['check_Out']); ?>: 
                                                <strong><?php echo homey_format_date_simple($check_out); ?></strong>
                                            </li>

                                            <?php 
                                            if( $booking_type == 'per_week' ) { ?>

                                                <li>
                                                <?php echo esc_attr($week_label); ?>: 
                                                <strong><?php echo esc_attr($no_of_weeks); ?>
                                                    
                                                    <?php 
                                                    if( $res_meta['no_of_days'] > 0 ) { 
                                                        echo esc_html__('and', 'homey').' '.esc_attr($res_meta['no_of_days']).' '.esc_attr($night_label); 
                                                    }?>

                                                </strong>
                                            </li>

                                            <?php } else if( $booking_type == 'per_month' ) { ?>

                                                <li>
                                                <?php echo esc_attr($month_label); ?>: 
                                                <strong><?php echo esc_attr($no_of_months); ?>
                                                    
                                                    <?php 
                                                    if( $res_meta['no_of_days'] > 0 ) { 
                                                        echo esc_html__('and', 'homey').' '.esc_attr($res_meta['no_of_days']).' '.esc_attr($night_label); 
                                                    }?>

                                                </strong>
                                            </li>

                                            <?php } else if( $booking_type == 'per_day_date' ) { ?>
                                            <li>
                                                <?php echo esc_attr($night_label); ?>:
                                                <strong><?php echo esc_attr($res_meta['no_of_days']); ?></strong>
                                            </li>
                                        <?php } else { ?>
                                            <li>
                                                <?php echo esc_attr($night_label); ?>: 
                                                <strong><?php echo esc_attr($res_meta['no_of_days']); ?></strong>
                                            </li>
                                            <?php } ?>

                                            <?php if($booking_hide_fields['guests'] != 1) {?>
                                            <li>
                                                <?php echo esc_attr($guests_label); ?>: 
                                                <strong><?php echo esc_attr($guests); ?></strong>
                                            </li>
                                            <?php } ?>
                                            
                                            <?php if(!empty($res_meta['additional_guests'])) { ?>
                                            <li>
                                                <?php echo esc_attr($homey_local['addinal_guest_text']); ?>: 
                                                <strong><?php echo esc_attr($res_meta['additional_guests']); ?></strong>
                                            </li>
                                            <?php } ?>
                                            
                                        </ul>
                                    </div><!-- block-right -->
                                </div><!-- block-body -->
                            </div><!-- block-section -->    

                            <?php if(!empty($renter_msg)) { ?>
                            <div class="block-section">
                                <div class="block-body">
                                    <div class="block-left">
                                        <h2 class="title"><?php esc_html_e('Notes', 'homey'); ?></h2>
                                    </div><!-- block-left -->
                                    <div class="block-right">
                                        <p><?php echo esc_attr($renter_msg); ?></p>
                                    </div><!-- block-right -->
                                </div><!-- block-body -->
                            </div><!-- block-section --> 
                            <?php } ?>
                            
                            <div class="block-section">
                                <div class="block-body">
                                    <div class="block-left">
                                        <h2 class="title"><?php echo esc_attr($homey_local['payment_label']); ?></h2>
                                    </div><!-- block-left -->
                                    <div class="block-right">
                                        <?php
                                        if($booking_type == 'per_day_date'){
                                            echo homey_calculate_reservation_cost_day_date($reservationID);
                                        }else{
                                            echo homey_calculate_reservation_cost($reservationID);
                                        }
                                         ?>
                                    </div><!-- block-right -->
                                </div><!-- block-body -->
                            </div><!-- block-section -->
                        </div><!-- .block -->
                        <div class="payment-buttons">
                            <?php homey_reservation_action($reservation_status, $upfront_payment, $payment_link, $reservationID, 'btn-half-width'); ?>
                        </div>
                    </div><!-- .dashboard-area -->
                </div><!-- col-lg-12 col-md-12 col-sm-12 -->
            </div>
        </div><!-- .container-fluid -->
    </div><!-- .dashboard-content-area -->    
    <aside class="dashboard-sidebar">
        <?php get_template_part('template-parts/dashboard/reservation/payment-sidebar', '', array("booking_type", $booking_type)); ?>

        <?php homey_reservation_action($reservation_status, $upfront_payment, $payment_link, $reservationID, 'btn-full-width'); ?>

    </aside><!-- .dashboard-sidebar -->
</div><!-- .user-dashboard-right -->
<?php get_template_part('template-parts/dashboard/reservation/message'); ?>
<?php } ?>