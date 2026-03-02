<?php
global $homey_local, $edit_listing_id, $hide_fields, $homey_booking_type;
$custom_weekend_price = isset($hide_fields['custom_weekend_price']) ? $hide_fields['custom_weekend_price'] : 0;

$homey_local_label = $homey_booking_type == 'per_day_date' ? $homey_local['day_daily_label'] : $homey_local['nightly_label'];
$homey_local_label_plac = $homey_booking_type == 'per_day_date' ? $homey_local['day_daily_plac'] : $homey_local['nightly_plac'];
?>
<hr id="custom_period_prices" class="row-separator">
        <!-- Custom Period Prices -->

<h3 class="sub-title"><?php echo esc_attr($homey_local['setup_period_prices']); ?></h3>
<div class="custom-prices-dates">
    <div class="row">
        <div class="col-sm-6 col-xs-12">
        <div class="form-group">
            <label><?php echo esc_attr($homey_local['start_date']); ?></label>
            <input type="text" readonly name="cus_start_date" id="cus_start_date" class="form-control" placeholder="<?php echo esc_attr($homey_local['start_date_plac']); ?>">
        </div>
    </div>
        <div class="col-sm-6 col-xs-12">
        <div class="form-group">
            <label><?php echo esc_attr($homey_local['end_date']); ?></label>
            <input type="text" readonly name="cus_end_date" id="cus_end_date" class="form-control" placeholder="<?php echo esc_attr($homey_local['end_date_plac']); ?>">
        </div>
    </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-6 col-xs-12">
        <div class="form-group">
            <label><?php echo esc_attr($homey_local_label); ?></label>
            <input type="text" name="cus_night_price" id="cus_night_price" class="form-control" placeholder="<?php echo esc_attr($homey_local_label_plac); ?>">
        </div>
    </div>

    <?php
    $booking_hide_fields = homey_option('booking_hide_fields');
    if ( $booking_hide_fields['guests'] != 1 ) {
?>
    <div class="col-sm-6 col-xs-12">
        <div class="form-group">
            <label><?php echo esc_attr($homey_local['addinal_guests_label']); ?></label>
            <input type="text" name="cus_additional_guest_price" id="cus_additional_guest_price" class="form-control" placeholder="<?php echo esc_attr($homey_local['addinal_guests_plac']); ?>">
        </div>
    </div>
<?php } ?>
    <?php if($custom_weekend_price != 1) { ?>
    <div class="col-sm-6 col-xs-12">
        <div class="form-group">
            <label><?php echo esc_attr($homey_local['weekends_label']); ?></label>
            <input type="text" name="cus_weekend_price" id="cus_weekend_price" class="form-control" placeholder="<?php echo esc_attr($homey_local['weekends_plac']); ?>">
        </div>
    </div>
    <?php } ?>

    <div class="col-sm-12 col-xs-12">
        <input type="hidden" name="listing_id_for_custom" id="listing_id_for_custom" value="<?php echo intval($edit_listing_id); ?>">
        <button id="cus_btn_save" type="button" class="btn btn-primary mt-20"><?php echo esc_attr($homey_local['save_btn']); ?></button>
    </div>
</div>

<hr class="row-separator">

<h3 class="sub-title"><?php echo esc_attr($homey_local['custom_period_prices']); ?></h3>
<div class="table-block dashboard-reservation-table dashboard-table">
    <table class="table table-hover">
        <thead>
            <tr>
                <th><?php echo esc_attr($homey_local['start_date']); ?></th>
                <th><?php echo esc_attr($homey_local['end_date']); ?></th>
                <th><?php echo esc_attr($homey_local_label); ?></th>
                <?php if($custom_weekend_price != 1) { ?>
                    <th><?php echo esc_attr($homey_local['weekends_label']); ?></th>
                <?php } ?>

                <?php if ( $booking_hide_fields['guests'] != 1 ) { ?>
                <th><?php echo esc_attr($homey_local['addinal_guests_label']); ?></th>
                <?php } ?>

                <th><?php echo esc_attr($homey_local['actions_label']); ?></th>
            </tr>
        </thead>
        <tbody>

            <?php echo homey_get_custom_period($edit_listing_id); ?>

        </tbody>
    </table>
</div>