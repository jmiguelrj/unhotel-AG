<?php 
global $homey_local; 
$homey_services = get_post_meta( get_the_ID(), 'homey_services', true );

if(!empty($homey_services)) {
?>
<div id="additional-services" class="additional-services-section">
    <div class="block">
        <div class="block-section">
            <div class="block-body">
                <div class="block-left">
                    <h3 class="title"><?php echo esc_attr(homey_option('sn_services_text')); ?></h3>
                </div><!-- block-left -->
                <div class="block-right">
                    <?php
                    foreach( $homey_services as $service ):
                        $service_name = isset($service['service_name']) ? $service['service_name'] : '';
                        $service_price = isset($service['service_price']) ? $service['service_price'] : '';
                        $service_des = isset($service['service_des']) ? $service['service_des'] : '';
                        
                        echo '<div class="block-col block-col-50 block-services">
                                <dl>
                                    <dt>'.esc_attr($service_name).' <span>'.homey_formatted_price($service_price, false).'</span></dt>
                                    <dd>'.esc_attr($service_des).'</dd>
                                </dl>                    
                            </div>';
                    endforeach;
                    ?>
                </div><!-- block-right -->
            </div><!-- block-body -->
        </div><!-- block-section -->
    </div><!-- block -->
</div><!-- accomodation-section -->
<?php } ?>