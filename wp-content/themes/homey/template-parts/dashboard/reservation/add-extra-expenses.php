<?php
$reservationID = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
if(empty($reservationID)) {
    return;
}
$expense_meta = get_post_meta($reservationID, 'homey_reservation_extra_expenses', true);
$i = 0;
?>
<div class="modal fade" id="modal-extra-expenses" tabindex="-1" role="dialog">
    <div class="modal-dialog clearfix" role="document">

        <div class="modal-body">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php esc_html_e('Extra Expense', 'homey'); ?></h4>
                </div>
                <div class="modal-body">

                    <div id="expenses-container">
                        <?php if(!empty($expense_meta)) { ?>
                            <?php foreach($expense_meta as $expense) { ?>
                                <div class="imported-calendar-row clearfix">
                                    <div class="imported-calendar-50">
                                        <input type="text" name="expense_name[]" class="form-control expense_name" value="<?php echo esc_attr($expense['expense_name']); ?>" readonly>
                                    </div>
                                    <div class="imported-calendar-50">
                                        <input type="text" name="expense_value[]" class="form-control expense_value" value="<?php echo esc_attr($expense['expense_value']); ?>" readonly>
                                    </div>
                                    <div class="imported-calendar-delete-button">
                                        <button data-remove="<?php echo intval($i); ?>" class="remove-expense btn btn-secondary-outlined btn-action"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                    </div>
                                </div>
                                <?php $i++; ?>
                            <?php } ?>
                        <?php } ?>

                    </div>
                    <div class="form-group">
                        <label><?php echo esc_html__('Expense Name', 'homey'); ?></label>
                        <input type="text" class="form-control enter_expense_name expense-dummy" placeholder="<?php echo esc_html__('Enter the expense name', 'homey'); ?>">
                    </div>
                    <div class="form-group">
                        <label><?php echo esc_html__('Expense Value', 'homey'); ?></label>
                        <input type="text" class="form-control enter_expense_value expense-dummy" placeholder="<?php echo esc_html__('Enter the expense price', 'homey'); ?>">
                    </div>

                    <div class="form-group">
                            <input type="hidden" id="expense_rsv_id" value="<?php echo intval($reservationID); ?>">
                            <button id="add_more_expense" type="button" data-increment="<?php echo esc_attr( $i-1 ); ?>" class="btn btn-primary btn-full-width"><?php echo esc_html__('Add More', 'homey'); ?></button>
                            <button id="save_expenses" type="button" class="btn btn-primary btn-full-width mb-10"><?php echo esc_html__('Save Expense', 'homey'); ?></button>
                            <button type="button" class="btn btn-grey-outlined btn-full-width" data-dismiss="modal"><?php echo esc_html__('Cancel', 'homey'); ?></button>
                       
                    </div>
                    

                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
    </div>
</div><!-- /.modal -->