<div class="block">
    <div class="block-title">
        <div class="block-left">
            <h2 class="title"><?php echo esc_html(homey_option('ad_section_mode', 'Listing Mode'));?></h2>
        </div><!-- block-left -->
    </div>
    <div class="block-body">

        <div class="col-sm-12 col-xs-12">
            <div class="form-group">
                <select id="homey_listing_mode" class="selectpicker" data-live-search="false" data-live-search-style="begins" title="">
                    <option value=""><?php esc_html_e('Select', 'homey'); ?></option>
                    <option value="per_day_date"><?php esc_html_e('Per Day', 'homey'); ?></option>
                    <option value="per_day"><?php esc_html_e('Per Night', 'homey'); ?></option>
                    <option value="per_week"><?php esc_html_e('Per Week', 'homey'); ?></option>
                    <option value="per_month"><?php esc_html_e('Per Month', 'homey'); ?></option>
                    <option value="per_hour"><?php esc_html_e('Per Hour', 'homey'); ?></option>
                </select>
            </div>
        </div>
    </div>
</div>
