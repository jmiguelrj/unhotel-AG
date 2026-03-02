<?php 
global $homey_prefix, $homey_local, $listing_data;
$accomodation = get_post_meta($listing_data->ID, $homey_prefix.'accomodation', true);
$class = '';
if(isset($_GET['tab']) && $_GET['tab'] == 'bedrooms') {
    $class = 'in active';
}
?>

<div id="bedrooms-tab" class="tab-pane fade <?php echo esc_attr($class); ?>">
    <div class="block-title visible-xs">
        <h3 class="title"><?php echo esc_html(homey_option('ad_bedrooms_text')); ?></h3>
    </div>
    <div class="block-body">
        <div id="more_bedrooms_main">
            <?php 
            $count = 0;
            if(!empty($accomodation)) {
                foreach($accomodation as $acc):
                ?>
                    <div class="more_rooms_wrap">
                        <div class="row">
                            <div class="col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label for="acc_bedroom_name"><?php echo esc_attr(homey_option('ad_acc_bedroom_name')); ?></label>
                                    <input type="text" name="homey_accomodation[<?php echo intval($count); ?>][acc_bedroom_name]" value="<?php echo sanitize_text_field( $acc['acc_bedroom_name'] ); ?>" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_acc_bedroom_name_plac')); ?>">
                                </div>
                            </div>
                            <div class="col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label for="acc_guests"> <?php echo esc_attr(homey_option('ad_acc_guests')); ?> </label>
                                    <input type="text" name="homey_accomodation[<?php echo intval($count); ?>][acc_guests]" value="<?php echo sanitize_text_field( $acc['acc_guests'] ); ?>" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_acc_guests_plac')); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label for="acc_no_of_beds"><?php echo esc_attr(homey_option('ad_acc_no_of_beds')); ?> </label>
                                    <input type="text" name="homey_accomodation[<?php echo intval($count); ?>][acc_no_of_beds]" value="<?php echo sanitize_text_field( $acc['acc_no_of_beds'] ); ?>" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_acc_no_of_beds_plac')); ?>">
                                </div>
                            </div>
                            <div class="col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label for="acc_bedroom_type"><?php echo esc_attr(homey_option('ad_acc_bedroom_type')); ?></label>
                                    <input type="text" name="homey_accomodation[<?php echo intval($count); ?>][acc_bedroom_type]" value="<?php echo sanitize_text_field( $acc['acc_bedroom_type'] ); ?>" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_acc_bedroom_type_plac')); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-xs-12">
                                <button type="button" data-remove="<?php echo esc_attr( $count-1 ); ?>" class="btn btn-primary remove-beds"><?php echo esc_attr(homey_option('ad_acc_btn_remove_room'));?></button>
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
                <button type="button" id="add_more_bedrooms" data-increment="<?php echo esc_attr( $count-1 ); ?>" class="btn btn-primary"><i class="fa fa-plus"></i> <?php echo esc_attr(homey_option('ad_acc_btn_add_other')); ?></button>
            </div>
        </div>
    </div>
</div>