<?php 
global $homey_local;
?>
<div class="form-step">
    <!--step information-->
    <div class="block">
        <div class="block-title">
            <div class="block-left">
                <h2 class="title"><?php echo esc_attr(homey_option('ad_services_text')); ?></h2>
            </div><!-- block-left -->
        </div>
        <div class="block-body">
            <div id="more_services_main">
                <div class="more_services_wrap">
                    <div class="row">
                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label for="service_name"><?php echo esc_attr(homey_option('ad_service_name')); ?></label>
                                <input type="text" name="homey_services[0][service_name]" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_service_name_plac')); ?>">
                            </div>
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label for="service_price"> <?php echo esc_attr(homey_option('ad_service_price')); ?> </label>
                                <input type="text" name="homey_services[0][service_price]" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_service_price_plac')); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label for="acc_no_of_beds"><?php echo esc_attr(homey_option('ad_service_des')); ?> </label>
                                <textarea placeholder="<?php echo esc_attr(homey_option('ad_service_des_plac')); ?>" name="homey_services[0][service_des]" rows="3" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <button type="button" data-remove="0" class="btn btn-primary remove-service"><?php echo esc_attr(homey_option('ad_btn_remove_service'));?></button>
                        </div>
                    </div>
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-xs-12 text-right">
                    <button type="button" id="add_more_service" data-increment="0" class="btn btn-primary"><i class="fa fa-plus"></i> <?php echo esc_attr(homey_option('ad_btn_add_service')); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>