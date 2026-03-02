<?php 
global $homey_prefix, $homey_local, $listing_data;
$services = get_post_meta($listing_data->ID, $homey_prefix.'services', true);
$class = '';
if(isset($_GET['tab']) && $_GET['tab'] == 'services') {
    $class = 'in active';
}
?>
<div id="services-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
    <div class="block-title visible-xs">
        <h3 class="title"><?php echo esc_attr(homey_option('ad_services_text')); ?></h3>
    </div>
    
    <div class="block-body">
        <div id="more_services_main">
            <?php 
            $count = 0;
            if(!empty($services)) {
                foreach($services as $acc):
                    $des = isset($acc['service_des']) ? $acc['service_des'] : ''; 
                ?>
                <div class="more_services_wrap">
                    <div class="row">
                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label for="service_name"><?php echo esc_attr(homey_option('ad_service_name')); ?></label>
                                <input type="text" name="homey_services[<?php echo intval($count); ?>][service_name]" class="form-control" value="<?php echo sanitize_text_field( $acc['service_name'] ); ?>" placeholder="<?php echo esc_attr(homey_option('ad_service_name_plac')); ?>">
                            </div>
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label for="service_price"> <?php echo esc_attr(homey_option('ad_service_price')); ?> </label>
                                <input type="text" name="homey_services[<?php echo intval($count); ?>][service_price]" class="form-control" value="<?php echo sanitize_text_field( $acc['service_price'] ); ?>" placeholder="<?php echo esc_attr(homey_option('ad_service_price_plac')); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label for="acc_no_of_beds"><?php echo esc_attr(homey_option('ad_service_des')); ?> </label>
                                <textarea placeholder="<?php echo esc_attr(homey_option('ad_service_des_plac')); ?>" name="homey_services[<?php echo intval($count); ?>][service_des]" rows="3" class="form-control"><?php echo trim($des); ?></textarea>
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
            <?php  $count++;
                endforeach; 
            } ?>
        </div>
        <div class="row">
            <div class="col-sm-12 col-xs-12 text-right">
                <button type="button" id="add_more_service" data-increment="<?php echo esc_attr( $count-1 ); ?>" class="btn btn-primary"><i class="fa fa-plus"></i> <?php echo esc_attr(homey_option('ad_btn_add_service')); ?></button>
            </div>
        </div>
    </div>
</div>