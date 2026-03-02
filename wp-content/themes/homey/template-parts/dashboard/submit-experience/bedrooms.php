<?php 
global $homey_local;
?>
<div class="form-step">
    <!--step information-->
    <div class="block">
        <div class="block-title">
            <div class="block-left">
                <h2 class="title"><?php echo esc_attr(homey_option('ad_bedrooms_text')); ?></h2>
            </div><!-- block-left -->
        </div>
        <div class="block-body">
            <div id="more_bedrooms_main">
                <div class="more_rooms_wrap">
                    <div class="row">
                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label for="acc_bedroom_name"><?php echo esc_attr(homey_option('ad_acc_bedroom_name')); ?></label>
                                <input type="text" name="homey_accomodation[0][acc_bedroom_name]" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_acc_bedroom_name_plac')); ?>">
                            </div>
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label for="acc_guests"> <?php echo esc_attr(homey_option('ad_acc_guests')); ?> </label>
                                <input type="text" name="homey_accomodation[0][acc_guests]" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_acc_guests_plac')); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label for="acc_no_of_beds"><?php echo esc_attr(homey_option('ad_acc_no_of_beds')); ?> </label>
                                <input type="text" name="homey_accomodation[0][acc_no_of_beds]" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_acc_no_of_beds_plac')); ?>">
                            </div>
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label for="acc_bedroom_type"><?php echo esc_attr(homey_option('ad_acc_bedroom_type')); ?></label>
                                <input type="text" name="homey_accomodation[0][acc_bedroom_type]" class="form-control" placeholder="<?php echo esc_attr(homey_option('ad_acc_bedroom_type_plac')); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <button type="button" data-remove="0" class="btn btn-primary remove-beds"><?php echo esc_attr(homey_option('ad_acc_btn_remove_room'));?></button>
                        </div>
                    </div>
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-xs-12 text-right">
                    <button type="button" id="add_more_bedrooms" data-increment="0" class="btn btn-primary"><i class="fa fa-plus"></i> <?php echo esc_attr(homey_option('ad_acc_btn_add_other')); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>