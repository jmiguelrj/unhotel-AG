<?php
global $homey_local;
if(isset($_GET['host']) && $_GET['host'] != '') {
    $host_id = $_GET['host'];
} else {
    $host_id = null;
}
?>
<div class="modal fade custom-modal-adjustment" id="modal-adjustment" tabindex="-1" role="dialog">
    <div class="modal-dialog clearfix" role="document">

        <div class="modal-body">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php esc_html_e('Make Adjustment', 'homey'); ?></h4>
                </div>
                <div class="modal-body">


                    <form method="POST">


                        <input type="hidden" name="adjustment_security" value="<?php echo wp_create_nonce('adjustment_security-nonce'); ?>"/>
                        <input type="hidden" name="action" value="homey_make_host_adjustments">
                        <input type="hidden" name="host_id" value="<?php echo esc_attr($host_id); ?>">
                        <div class="form-group">
                            <input type="text" name="adj_title" class="form-control" placeholder="<?php esc_html_e('Title', 'homey'); ?>">
                        </div>

                        <div class="form-group">
                            <input type="number" name="adj_amount" class="form-control" placeholder="<?php esc_html_e('Amount (only number)', 'homey'); ?>">
                        </div>

                        <div class="form-group">
                            <select name="adj_action" class="selectpicker" title="<?php esc_html_e('Action', 'homey'); ?>">
                                <option value=""><?php esc_html_e('Action', 'homey'); ?></option>
                                <option value="add_money"><?php esc_html_e('Add Money', 'homey'); ?></option>
                                <option value="deduct_money"><?php esc_html_e('Deduct Money', 'homey'); ?></option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <textarea name="adj_reason" class="form-control" placeholder="<?php esc_html_e('Reason', 'homey'); ?>" rows="5"></textarea>
                        </div>

                        
                        <div class="homey_messages"></div>
                        <button id="btn_make_adjustment" type="submit" class="btn btn-primary btn-full-width"><?php echo esc_attr($homey_local['submit_btn']); ?></button>
                    </form>
                        
                     

                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
    </div>
</div><!-- /.modal -->