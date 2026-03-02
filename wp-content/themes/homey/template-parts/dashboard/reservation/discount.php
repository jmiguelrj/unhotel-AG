<?php
$reservationID = isset($_GET['reservation_detail']) ? $_GET['reservation_detail'] : '';
if(empty($reservationID)) {
    return;
}
$discount_meta = get_post_meta($reservationID, 'homey_reservation_discount', true);
$i = 0;
?>
<div class="modal fade" id="modal-discount" tabindex="-1" role="dialog">
    <div class="modal-dialog clearfix" role="document">

        <div class="modal-body">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php esc_html_e('Discount', 'homey'); ?></h4>
                </div>
                <div class="modal-body">

                    <div id="discount-container">
                        <?php if(!empty($discount_meta)) { ?>
                            <?php foreach($discount_meta as $discount) { ?>
                                <div class="imported-calendar-row clearfix">
                                    <div class="imported-calendar-50">
                                        <input type="text" name="discount_name[]" class="form-control discount_name" value="<?php echo esc_attr($discount['discount_name']); ?>" readonly>
                                    </div>
                                    <div class="imported-calendar-50">
                                        <input type="text" name="discount_value[]" class="form-control discount_value" value="<?php echo esc_attr($discount['discount_value']); ?>" readonly>
                                    </div>
                                    <div class="imported-calendar-delete-button">
                                        <button data-remove="<?php echo intval($i); ?>" class="remove-discount btn btn-secondary-outlined btn-action"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                    </div>
                                </div>
                                <?php $i++; ?>
                            <?php } ?>
                        <?php } ?>

                    </div>
                    <div class="form-group">
                        <label><?php echo esc_html__('Discount Name', 'homey'); ?></label>
                        <input type="text" class="form-control enter_discount_name discount-dummy" placeholder="<?php echo esc_html__('Enter discount name', 'homey'); ?>">
                    </div>
                    <div class="form-group">
                        <label><?php echo esc_html__('Discount Value', 'homey'); ?></label>
                        <input type="text" class="form-control enter_discount_value discount-dummy" placeholder="<?php echo esc_html__('Enter discount value', 'homey'); ?>">
                    </div>

                    <div class="form-group">
                            <input type="hidden" id="discount_rsv_id" value="<?php echo intval($reservationID); ?>">
                            <button id="add_more_discount" type="button" data-increment="<?php echo esc_attr( $i-1 ); ?>" class="btn btn-primary btn-full-width"><?php echo esc_html__('Add More', 'homey'); ?></button>
                            <button id="save_discounts" type="button" class="btn btn-primary btn-full-width mb-10"><?php echo esc_html__('Save Discounts', 'homey'); ?></button>
                            <button type="button" class="btn btn-grey-outlined btn-full-width" data-dismiss="modal"><?php echo esc_html__('Cancel', 'homey'); ?></button>
                       
                    </div>
                    

                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
    </div>
</div><!-- /.modal -->