<?php 
global $homey_prefix, $homey_local;
$listing_author = homey_get_author('40', '40', 'img-circle media-object avatar');


require get_template_directory().'/framework/functions/Carbon/autoload.php';
use Carbon\Carbon;

$check_in = get_post_meta(get_the_ID(), 'reservation_checkin_date', true);
$check_out = get_post_meta(get_the_ID(), 'reservation_checkout_date', true);
$reservation_guests = get_post_meta(get_the_ID(), 'reservation_guests', true);
$listing_id = get_post_meta(get_the_ID(), 'reservation_listing_id', true);
$listing_address    = get_post_meta( $listing_id, $homey_prefix.'listing_address', true );
$pets   = get_post_meta($listing_id, $homey_prefix.'pets', true);
$deposit = get_post_meta(get_the_ID(), 'reservation_upfront', true);
$total_amount = get_post_meta(get_the_ID(), 'reservation_total', true);
$reservation_status = get_post_meta(get_the_ID(), 'reservation_status', true);

if(homey_is_renter()) {
    $reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
} else {

    if(!homey_listing_guest(get_the_ID())) {
        $reservation_page_link = homey_get_template_link('template/dashboard-reservations.php');
    } else {
        $reservation_page_link = homey_get_template_link('template/dashboard-reservations2.php');
    }
}

$detail_link = add_query_arg( 'reservation_detail', get_the_ID(), $reservation_page_link );

$no_upfront = homey_option('reservation_payment');
$booking_hide_fields = homey_option('booking_hide_fields');

if($no_upfront == 'no_upfront') {
    $price = '';
} else {
    $price = $deposit;
}

if( empty($price) ) {
    $price = $total_amount;
}

$is_read = $status_label = '';

if($pets != 1) {
    $pets_allow = $homey_local['text_no'];
} else {
    $pets_allow = $homey_local['text_yes'];
}

if( !homey_is_renter() ) {
    if($reservation_status == 'under_review') {
        $is_read = 'msg-unread';
    }
}

if ( is_page_template( array('template/dashboard.php') ) ) {
    $is_read = '';
}

?>
<tr class="<?php echo esc_attr($is_read); ?>">
    <td data-label="<?php esc_html_e('Author', 'homey'); ?>">
        <?php if(!empty($listing_author['photo'])) { 
            echo '<a href="'.esc_url($listing_author['link']).'" target="_blank">'.$listing_author['photo'].'</a>';
        } 
        ?>
    </td>
    <td data-label="<?php echo esc_attr($homey_local['id_label']); ?>">
        <?php echo '#'.get_the_ID(); ?>
        <?php $wc_order_id = get_wc_order_id(get_the_ID()); if($wc_order_id > 0) echo 'wc#'.$wc_order_id; ?>
    </td>
    <td data-label="<?php echo esc_attr($homey_local['status_label']); ?>">
        <?php homey_reservation_label($reservation_status, get_the_ID()); ?>
    </td>
    <td data-label="<?php echo esc_attr($homey_local['date_label']); ?>">
        <?php esc_attr( the_time( homey_convert_date(homey_option( 'homey_date_format' ) ) ));?><br>
        <?php esc_attr( the_time( homey_time_format() ));?>
        
    </td>
    <td data-label="<?php echo esc_attr($homey_local['address']); ?>">
        <a href="<?php echo get_permalink($listing_id); ?>"><strong><?php echo get_the_title($listing_id); ?></strong></a>
        <?php if(!empty($listing_address)) { ?>
            <address><?php echo esc_attr($listing_address); ?></address>
        <?php } ?>
    </td>
    <td data-label="<?php echo esc_attr($homey_local['check_in']); ?>">
        <?php echo homey_format_date_simple($check_in); ?>
    </td>
    <td data-label="<?php echo esc_attr($homey_local['check_out']); ?>">
        <?php echo homey_format_date_simple($check_out); ?>
    </td>
    <?php if($booking_hide_fields['guests'] != 1 && 0 !=  homey_option('cgl_guests')) {?>
    <td data-label="<?php echo homey_option('glc_guests_label');?>">
        <?php echo esc_attr($reservation_guests); ?>
        <!-- 3 Adults<br>
        2 Children -->
    </td>
    <?php } ?>
    
    <td data-label="<?php echo esc_attr($homey_local['pets_label']);?>">
        <?php echo esc_attr($pets_allow); ?>
    </td>
    <td data-label="<?php echo esc_attr($homey_local['subtotal_label']); ?>">
        <strong><?php echo homey_formatted_price($price); ?></strong>
    </td>
    <td data-label="<?php echo esc_attr($homey_local['actions_label']); ?>">
        <div class="custom-actions">
            <?php 
            if( homey_listing_guest(get_the_ID()) ) {
                if($reservation_status == 'available') {
                    echo '<a href="'.esc_url($detail_link).'" class="btn btn-success">'.$homey_local['res_paynow_label'].'</a>';
                } else {
                    echo '<a href="'.esc_url($detail_link).'" class="btn btn-secondary">'.$homey_local['res_details_label'].'</a>';
                }
            } else {
                if($reservation_status == 'under_review') {
                    echo '<a href="'.esc_url($detail_link).'" class="btn btn-success">'.$homey_local['res_confirm_label'].'</a>';
                } else {
                    echo '<a href="'.esc_url($detail_link).'" class="btn btn-secondary">'.$homey_local['res_details_label'].'</a>';
                }
            }
            ?>
        </div>
    </td>
</tr>