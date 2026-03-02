<?php
$edit_listing = isset($_GET['edit_listing']) ? $_GET['edit_listing'] : '';
$ical_feeds_meta = get_post_meta($edit_listing, 'homey_ical_feeds_meta', true);
$i = 0;
?>
<div class="modal fade custom-modal" id="modal-calendar-import" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <p><strong><?php echo esc_html__('Import iCal', 'homey'); ?></strong></p>
                <div class="modal-calendar-availability clearfix">
                    <div id="ical-feeds-container">
                        <?php if(!empty($ical_feeds_meta)) { ?>
                            <?php foreach($ical_feeds_meta as $ical) { ?>
                                <div class="imported-calendar-row clearfix">
                                    <div class="imported-calendar-50">
                                        <input type="text" name="ical_feed_name[]" class="form-control ical_feed_name" value="<?php echo esc_attr($ical['feed_name']); ?>" readonly>
                                    </div>
                                    <div class="imported-calendar-50">
                                        <input type="text" name="ical_feed_url[]" class="form-control ical_feed_url" value="<?php echo esc_url($ical['feed_url']); ?>" readonly>
                                    </div>
                                    <div class="imported-calendar-delete-button">
                                        <button data-remove="<?php echo intval($i); ?>" class="remove-ical-feed btn btn-secondary-outlined btn-action"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                    </div>
                                </div>
                                <?php $i++; ?>
                            <?php } ?>
                        <?php } ?>

                    </div>
                    <div class="form-group">
                        <label><?php echo esc_html__('Feed Name', 'homey'); ?></label>
                        <input type="text" class="form-control enter_ical_feed_name ical-dummy" placeholder="<?php echo esc_html__('Enter the feed name', 'homey'); ?>">
                    </div>
                    <div class="form-group">
                        <label><?php echo esc_html__('Feed URL', 'homey'); ?></label>
                        <input type="text" class="form-control enter_ical_feed_url ical-dummy" placeholder="<?php echo esc_html__('Enter the feed url', 'homey'); ?>">
                    </div>
                </div>
            </div>
            <div class="modal-footer text-center">
                <button id="add_more_feed" type="button" data-increment="<?php echo esc_attr( $i-1 ); ?>" class="btn btn-primary btn-full-width"><?php echo esc_html__('Add Feed', 'homey'); ?></button>
                <button id="import_ical_feeds" type="button" class="btn btn-primary btn-full-width mb-10"><?php echo esc_html__('Save Feeds', 'homey'); ?></button>
                <button type="button" class="btn btn-grey-outlined btn-full-width" data-dismiss="modal"><?php echo esc_html__('Cancel', 'homey'); ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->