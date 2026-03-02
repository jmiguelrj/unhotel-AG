<!-- Panel Group -->
<div class="col-sm-6 m-b-30">
    <div class="wdt-activation-section">

        <div class="wpdt-plugins-desc">
            <img class="img-responsive" src="<?php echo WDT_ASSETS_PATH; ?>img/addons/master-detail-logo.png" alt="">
            <h4> <?php esc_html_e('Master-Detail', 'wpdatatables'); ?></h4>
        </div>

        <!-- Panel Body -->
        <div class="panel-body">

            <!-- Melograno Store Purchase Code -->
            <div class="col-sm-10 wdt-purchase-code-master-detail p-l-0">

                <!-- Melograno Store Purchase Code Heading-->
                <h4 class="c-title-color m-b-4 m-t-0">
                    <?php esc_html_e('Melograno Store Purchase Code', 'wpdatatables'); ?>
                    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                       title="<?php esc_attr_e('If your brought the plugin directly on our website, enter Melograno Store purchase code to enable auto updates.', 'wpdatatables'); ?>"></i>
                </h4>
                <!-- /Melograno Store Purchase Code Heading -->

                <!-- Melograno Store Purchase Code Form -->
                <div class="form-group">
                    <div class="row">

                        <!-- Melograno Store Purchase Code Input -->
                        <div class="col-sm-11 p-r-0 wdt-purchase-code-store-master-detail-wrapper">
                            <div class="fg-line">
                                <input type="text" name="wdt-purchase-code-store-master-detail"
                                       id="wdt-purchase-code-store-master-detail"
                                       class="form-control input-sm"
                                       placeholder="<?php esc_attr_e('Please enter your Master-Detail Melograno Store Purchase Code', 'wpdatatables'); ?>"
                                       value=""
                                />
                            </div>
                        </div>
                        <!-- Melograno Store Purchase Code Input -->

                        <!-- Melograno Store Security massage -->
                        <div class="col-sm-11 p-r-0 wdt-security-massage-wrapper hidden">
                            <div class="fg-line">
                                <div class="alert alert-info" role="alert">
                                    <i class="wpdt-icon-info-circle-full"></i>
                                    <span class="wdt-alert-title f-600">
                                        <?php esc_html_e('Your purchase code has been hidden for security reasons. You can find it on your', 'wpdatatables'); ?>
                                        <a href="https://store.melograno.io/login" target="_blank"><?php esc_html_e('store page', 'wpdatatables'); ?></a>.
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!-- Melograno Store Security massage -->

                        <!-- Melograno Store Purchase Code Activate Button -->
                        <div class="col-sm-1">
                            <button class="btn btn-primary wdt-store-activate-plugin" id="wdt-activate-plugin-master-detail">
                                <i class="wpdt-icon-check-circle-full"></i><?php esc_html_e('Activate ', 'wpdatatables'); ?>
                            </button>
                        </div>
                        <!-- /Melograno Store Purchase Code Activate Button -->

                    </div>
                </div>
                <!-- /Melograno Store Purchase Code Form -->

            </div>
            <!-- /Melograno Store Purchase Code -->

        </div>
        <!-- /Panel Body -->
    </div>
</div>
<!-- /Panel Group -->

