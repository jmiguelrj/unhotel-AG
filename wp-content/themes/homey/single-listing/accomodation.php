<?php
global $post, $homey_prefix, $homey_local;
$accomodation = homey_get_listing_data('accomodation');
$guests = homey_option('sn_acc_guest_label');

$icon_type = homey_option('detail_icon_type');

if(!empty($accomodation)) {
?>
<div id="accomodation-section" class="accomodation-section">
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div class="block-left">
                    <h3 class="title"><?php echo esc_attr(homey_option('sn_accomodation_text')); ?></h3>
                </div><!-- block-left -->
                <div class="block-right">
                    
                    <?php foreach($accomodation as $acc): 

                        $acc_bedroom_name = isset($acc['acc_bedroom_name']) ? $acc['acc_bedroom_name'] : '';
                        $acc_bedroom_type = isset($acc['acc_bedroom_type']) ? $acc['acc_bedroom_type'] : '';
                        $acc_guests = isset($acc['acc_guests']) ? $acc['acc_guests'] : '';
                        $acc_no_of_beds = isset($acc['acc_no_of_beds']) ? $acc['acc_no_of_beds'] : '';
                        ?>
                    <div class="block-col block-col-33 block-accomodation">
                        <div class="block-icon">
                            <?php
                            if($icon_type == 'fontawesome_icon') {
                                echo '<i class="'.esc_attr(homey_option('de_acco_sec_icon')).'"></i>';

                            } elseif($icon_type == 'custom_icon') {
                                echo '<img src="'.esc_url(homey_option( 'de_cus_acco_sec_icon', false, 'url' )).'" alt="'.esc_attr__('icon', 'homey').'">';
                            }
                            ?>
                        </div>
                        <dl>
                            <?php 
                            if($acc_guests > 1) { $guests = esc_attr(homey_option('sn_acc_guests_label')); } else { $guests = esc_attr(homey_option('sn_acc_guest_label')); }

                            if(!empty($acc_bedroom_name)) {
                                echo '<dt>'.esc_attr($acc_bedroom_name).'</dt>';
                            }
                            if(!empty($acc_no_of_beds) || !empty($acc_bedroom_type)) {
                                echo '<dd>'.esc_attr($acc_no_of_beds).' '.esc_attr($acc_bedroom_type).'</dd>';
                            }
                            if(!empty($acc_guests)) {
                                //echo '<dt>'.$acc_guests.' '.$guests.'/dt>';
                                echo '<dd>'.esc_attr($acc_guests).' '.esc_attr($guests).'</dd>';
                            }
                            ?>
                        </dl>                    
                    </div>
                    <?php endforeach; ?>
                </div><!-- block-right -->
            </div><!-- block-body -->
        </div><!-- block-section -->
    </div><!-- block -->
</div><!-- accomodation-section -->
<?php } ?>