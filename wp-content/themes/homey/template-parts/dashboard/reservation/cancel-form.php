<?php global $reservationID; ?>
<div class="collapse" id="cancel-reservation">
    <div class="reason-msg-block">
        <h3 class="title"><?php esc_html_e('Cancel Reason', 'homey'); ?></h3>
        <form>
            <div class="text-area-block">
                <textarea name="reason" id="reason" class="form-control" rows="5"></textarea>
            </div>
            <div class="text-right">
                <input type="hidden" name="reservationID" id="reservationID" value="<?php echo intval($reservationID)?>">
                <?php if(!homey_is_renter()) { ?>
                    <input type="hidden" name="host_cancel" id="host_cancel" value="cancelled_by_host">
                <?php } ?>
                <button id="cancelled" class="btn btn-grey-light btn-xs-full-width"><?php esc_html_e('Submit', 'homey'); ?></button>
            </div>
        </form>
    </div><!-- reason-msg-block -->
</div><!-- collapse -->