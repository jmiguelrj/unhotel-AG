<?php
global $homey_local;
?>
<div class="modal fade custom-modal" id="modal-calendar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <p><strong><?php echo esc_attr($homey_local['reserve_period_label']); ?></strong></p>

                <div class="homey_notification"></div>

                <div class="modal-calendar-availability clearfix">
                    <div class="form-group">
                        <label><?php echo esc_html__('Start Date', 'homey'); ?></label>
                        <input type="text" id="period_start_date" class="form-control" placeholder="<?php echo homey_option('homey_date_format'); ?>">
                    </div>
                    <div class="form-group">
                        <label><?php echo esc_html__('End Date', 'homey'); ?></label>
                        <input type="text" id="period_end_date" class="form-control" placeholder="<?php echo homey_option('homey_date_format'); ?>">
                    </div>
                    <div class="form-group">
                        <label><?php echo esc_html__('Notes', 'homey'); ?></label>
                        <textarea class="form-control" id="period_note" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer text-center">
                <input type="hidden" name="period_listing_id" id="period_listing_id" value="<?php echo intval($_GET['edit_listing']); ?>">
                <input type="hidden" name="period-security" id="period-security" value="<?php echo wp_create_nonce('period-security-nonce'); ?>"/>
                <button id="reserve_period_host" type="button" class="btn btn-primary btn-half-width"><?php echo esc_attr($homey_local['reserve_btn']); ?></button>
                <button type="button" class="btn btn-grey-outlined btn-half-width" data-dismiss="modal"><?php echo esc_attr($homey_local['cancel_btn']); ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->