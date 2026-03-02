<?php 
global $homey_local, $edit_listing_id;

$visisblty = 'hidden';
$class = '';
if(isset($_GET['tab']) && $_GET['tab'] == 'calendar') {
    $class = 'in active';
    $visisblty = 'visible';
}
$min_book_hours  = homey_get_listing_data_by_id('min_book_hours', $edit_listing_id);

if($min_book_hours > 1) {
    $min_book_hours_label = esc_html__('Hours', 'homey');
} else {
    $min_book_hours_label = esc_html__('Hour', 'homey');
}

?>

<div id="calendar-tab" style="visibility: <?php echo esc_attr($visisblty); ?>;" class="tab-pane11 fade <?php echo esc_attr($class); ?>">
    <div class="block-title visible-xs">
            <h3 class="title"><?php echo esc_attr($homey_local['cal_label']); ?></h3>
    </div>
    <div class="block-body">
        <div id="property-calendar" class="property-calendar">
            
            <div id="homey_hourly_calendar_edit_listing"></div>
        
        </div>
    </div>
</div>
