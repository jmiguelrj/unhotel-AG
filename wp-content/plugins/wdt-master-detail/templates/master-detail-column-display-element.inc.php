<div class="col-sm-6 wdt-md-column-block hidden">
    <h4 class="c-title-color m-b-4">
        <?php esc_html_e('Master-detail column', 'wpdatatables'); ?>
        <i class="wpdt-icon-info-circle-thin" data-popover-content="#master-detail-column"
           data-toggle="html-popover" data-trigger="hover" data-placement="right"></i>
    </h4>

    <!-- Hidden popover with image hint -->
    <div class="hidden" id="master-detail-column">
        <div class="popover-heading">
            <?php esc_html_e('Add to the details section', 'wpdatatables'); ?>
        </div>

        <div class="popover-body">
            <?php esc_html_e('If you turn on this option, values from this column will appear in the Details section in the Master-Detail popup or post/page', 'wpdatatables'); ?>
        </div>
    </div>
    <!-- /Hidden popover with image hint -->

    <div class="form-group">
        <div class="toggle-switch" data-ts-color="blue">
            <input id="wdt-md-column" type="checkbox" hidden="hidden">
            <label for="wdt-md-column"
                   class="ts-label"><?php esc_html_e('Add to the details section', 'wpdatatables'); ?></label>
        </div>
    </div>

</div>

