<?php
/**
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 08/12/16
 * Time: 8:13 PM
 */

global $wpdb, $current_user;

wp_get_current_user();
$current_user_id =  get_current_user_id();

if(isset($_GET['reservation_id']) && !empty($_GET['reservation_id'])) {
    $reservationID = $_GET['reservation_id'];
} else {
    wp_die('Are you Kidding?'); 
}

if( !homey_give_access($reservationID) ) {
    wp_die('Are you Kidding?'); 
}

$renter_id = get_post_meta($reservationID, 'listing_renter', true);
$renter_info = homey_get_author_by_id('60', '60', 'img-circle', $renter_id);

$owner_id = get_post_meta($reservationID, 'listing_owner', true);
$owner_info = homey_get_author_by_id('60', '60', 'img-circle', $owner_id);

if( $current_user_id == $owner_id ) {
    $receiver_id = $renter_id;
} else {
    $receiver_id = $owner_id;
}
if(homey_is_renter()) {
    $sendTo = $owner_info['name'];
    $photo = $renter_info['photo'];
    //$receiver_id = $owner_id;
} else {
    $sendTo = $renter_info['name'];
    $photo = $owner_info['photo'];
    //$receiver_id = $renter_id;
}
?>

<?php if ( isset( $_GET['success'] ) && $_GET['success'] == true ) { ?>
    <div class="alert alert-success alert-dismissible" role="alert">
        <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
        <?php esc_html_e( 'The message has been sent.', 'homey' ); ?>
    </div>
<?php } ?>

<?php if ( isset( $_GET['success'] ) && $_GET['success'] == false ) { ?>
    <div class="alert alert-error alert-dismissible" role="alert">
        <button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>
        <?php esc_html_e( 'Oopps something getting wrong, please try again!', 'homey' ); ?>
    </div>
<?php } ?>

<div class="messages-area">
    
    <div class="media msg-send-block">
        
        <div class="media-left">
            <div class="media-object">
                <?php echo ''.$photo; ?>
            </div>
        </div>
        <div class="media-body">
            <h4 class="media-heading"><?php echo esc_html__('New Message', 'homey'); ?></h4>
            <form class="form-msg" method="post">
                <input type="hidden" name="start_thread_form_ajax" value="<?php echo wp_create_nonce('start-thread-form-nonce'); ?>"/>
                <input type="hidden" name="listing_id" value="<?php echo intval($reservationID); ?>"/>
                <input type="hidden" name="receiver_id" value="<?php echo intval($receiver_id); ?>">
                <input type="hidden" name="action" value="homey_start_thread">

                <div class="msg-type-block">
                    <div class="arrow"></div>
                    <textarea name="message" rows="5" class="form-control" placeholder="<?php esc_attr_e( 'Type your message here...', 'homey' ); ?>"></textarea>

                    <div class="msg-attachment-row">
                        <div class="msg-attachment">
                            <ul id="listing-thumbs-container" class="list-inline">
                                <li class="new-attach" id="thread-message-attachment">
                                    <div class="attach-icon new-attachment">
                                        <i class="fa fa-paperclip"></i>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div id="plupload-container"></div>
                        <div id="errors-log"></div>
                    </div>

                </div>
                <div class="form-msg-btns">
                    <button class="btn btn-success start_thread_form"><?php echo esc_html__('Send', 'homey'); ?></button>
                </div>
                <div class="messages-notification text-right"></div>
            </form>
        </div>
    </div><!-- end .msg-send-block -->

</div>