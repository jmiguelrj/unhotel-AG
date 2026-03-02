<?php

function homey_admin_css() {
  
    $homey_site_mode = homey_option('homey_site_mode');

    if($homey_site_mode == 'per_hour') {
      $make_css = "
      .homey_hourly {
        display:none !important;
      }
      ";
    } 

    if($homey_site_mode == 'per_day_date') {
      $make_css = "
      .homey_daily {
        display:none !important;
      }
      ";
    }

    if($homey_site_mode == 'per_day') {
      $make_css = "
      .homey_daily {
        display:none !important;
      }
      ";
    }

    wp_add_inline_style( 'homey-admin-css',
      $make_css
    );

}
add_action( 'wp_enqueue_scripts', 'homey_admin_css', 21 );
?>
