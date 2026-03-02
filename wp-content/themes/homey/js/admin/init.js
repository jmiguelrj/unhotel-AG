(function($) {
    "use strict";  
    
    $(document).ready(function ($) {
    
        jQuery('#homey_page_sidebar').on('change', function(){
            Check_Page_Sidebar();
        });
        jQuery('#page_template').on('change', function(){
            checkTemplate();
        });
        
        function Check_Page_Sidebar() {
            var page_sidebar = jQuery('#homey_page_sidebar').val();
            if( page_sidebar == 'yes' ) {
                jQuery('.homey_selected_sidebar').stop(true,true).fadeIn(500);
            } else {
                jQuery('.homey_selected_sidebar').hide();
            }
        }

        function checkTemplate() {

            var template = jQuery('#page_template').attr('value');

            if( template == 'template/template-listing-list.php' || template == 'template/template-listing-grid.php' || template == 'template/template-listing-card.php' || template == 'template/template-listing-sticky-map.php' ) {
                jQuery('#homey_listing_template').stop(true,true).fadeIn(500);
        
            } else {
                jQuery('#homey_listing_template').hide();
            }

            if( template == 'template/template-half-map.php' ) {
                jQuery('#homey_listing_template_halfmap').stop(true,true).fadeIn(500);
        
            } else {
                jQuery('#homey_listing_template_halfmap').hide();
            }

        }

        jQuery(window).load(function(){ 
            Check_Page_Sidebar();
            checkTemplate();
            
        });

        // some unwanted spans after wp 5.6
        var un_wanted_spans = setInterval(function(){
            jQuery(".redux-field-container").find("span.ui-checkboxradio-icon").remove();
            // clearInterval(un_wanted_spans);
        }, 1500);

        // end of some unwanted spans after wp 5.6

        // document.getElementById('homey_options[custom_logo][url]').removeAttribute('readonly');

    });
        
})(jQuery);