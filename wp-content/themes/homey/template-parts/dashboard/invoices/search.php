<?php global $homey_local; ?>
<div class="invoice-search-block">
    <div class="row">   
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
            <div class="form-group">
                <label><?php echo esc_attr($homey_local['start_date']); ?></label>
                <input id="startDate" type="text" class="input_date form-control" placeholder="<?php echo esc_html__(strtoupper(homey_option('homey_date_format')), 'homey'); ?>">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
            <div class="form-group">
                <label><?php echo esc_attr($homey_local['end_date']); ?></label>
                <input id="endDate" type="text" class="input_date form-control" placeholder="<?php echo esc_html__(strtoupper(homey_option('homey_date_format')), 'homey'); ?>">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
            <div class="form-group">
                <label><?php echo esc_attr($homey_local['inv_type']); ?></label>
                <select id="invoice_type" class="form-control selectpicker">
                    <option value=""><?php echo esc_attr($homey_local['any']); ?></option>
                    <option value="reservation"><?php echo esc_attr($homey_local['resv_fee_text']); ?></option>
                    <option value="upgrade_featured"><?php echo esc_attr($homey_local['upgrade_text']); ?></option>
              </select>
          </div>
      </div>
      <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
        <div class="form-group">
                <label><?php echo esc_attr($homey_local['inv_status']); ?></label>
                <select id="invoice_status" class="form-control selectpicker">
                    <option value=""><?php echo esc_attr($homey_local['any']); ?></option>
                    <option value="1"><?php echo esc_attr($homey_local['paid']); ?></option>
                    <option value="0"><?php echo esc_attr($homey_local['not_paid']); ?></option>
              </select>
          </div>
    </div>
</div>
</div>
