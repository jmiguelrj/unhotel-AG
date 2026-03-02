<?php 
global $homey_local, $homey_prefix, $reservationID, $owner_info, $renter_info, $renter_id, $owner_id;

if(homey_is_renter()) {
    $sendTo = $owner_info['name'];
    $receiver_id = $owner_id;
} else {
    $sendTo = $renter_info['name'];
    $receiver_id = $renter_id;
}
?>
<div class="modal fade custom-modal-send-msg" id="message-form-popup" tabindex="-1" role="dialog">
    <div class="modal-dialog clearfix" role="document">

        <div class="modal-body">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo esc_html__('Send Message to', 'homey').' '.esc_attr($sendTo); ?></h4>
                </div>
                <div class="modal-body">

                    <div class="form-group messages-notification"></div>

                    <div class="modal-send-message-form">
                        <form method="POST">
                            <input type="hidden" name="start_thread_form_ajax"
                                   value="<?php echo wp_create_nonce('start-thread-form-nonce'); ?>"/>
                            <input type="hidden" name="listing_id" value="<?php echo intval($reservationID); ?>"/>
                            <input type="hidden" name="receiver_id" value="<?php echo intval($receiver_id); ?>">
                            <input type="hidden" name="action" value="homey_start_thread">

                            <div class="form-group">
                               <textarea class="form-control" name="message" rows="5" placeholder="<?php esc_attr_e('Type your message...', 'homey'); ?>"></textarea>
                            </div>

                            <button type="submit" class="start_thread_form btn btn-primary btn-full-width"><?php echo esc_attr($homey_local['msg_send_btn']); ?></button>
                        </form>
                    </div>

                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
    </div>
</div><!-- /.modal -->