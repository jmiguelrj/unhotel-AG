<?php global $reservationID; ?>
<div id="decline-reservation">
    <div class="reason-msg-block">
        <h3 class="title"><?php echo esc_html__('Decline Reason', 'homey'); ?></h3>
        <p><?php echo get_post_meta($reservationID, 'res_decline_reason', true); ?></p>
    </div><!-- reason-msg-block -->
</div>