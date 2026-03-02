<?php global $reservationID; ?>
<div class="collapse" id="decline-reservation">
    <div class="reason-msg-block">
        <h3 class="title"><?php esc_html_e('Decline Reason', 'homey'); ?></h3>
        <form>
            <div class="text-area-block">
                <textarea name="reason" id="reason" class="form-control" rows="5"></textarea>
            </div>
            <div class="text-right">
                <input type="hidden" name="reservationID" id="reservationID" value="<?php echo intval($reservationID)?>">
                <button id="decline_hourly" class="btn btn-grey-light btn-xs-full-width"><?php esc_html_e('Submit', 'homey'); ?></button>
            </div>
        </form>
    </div><!-- reason-msg-block -->
</div><!-- collapse -->