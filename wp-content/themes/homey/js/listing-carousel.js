jQuery(document).ready( function($){

    $('.homey-carousel[id^="homey-listing-carousel-"]').each(function(){
        var $div = jQuery(this);
        var token = $div.data('token');
        var obj = window['listing_caoursel_' + token];

        var homey_is_rtl = HOMEY_ajax_vars.homey_is_rtl;

        if( homey_is_rtl == 'yes' ) {
            homey_is_rtl = true;
        } else {
            homey_is_rtl = false;
        }
        

        var columns = parseInt(obj.slides_to_show),
            slidesToShow = obj.slides_to_show,
            slidesToScroll = parseInt(obj.slides_to_scroll),
            autoplay = parseBool(obj.slide_auto),
            autoplaySpeed = parseInt(obj.auto_speed),
            dots = parseBool( obj.slide_dots),
            navigation = parseBool( obj.navigation),
            slide_infinite =  parseBool( obj.slide_infinite );
            listing_style =   obj.listing_style;
            next_text = HOMEY_ajax_vars.next_text;
            prev_text = HOMEY_ajax_vars.prev_text;

            var homey_carousel = $('#homey-listing-carousel-'+token);
           
            function parseBool(str) {
                if( str == 'true' ) { return true; } else { return false; }
            }

            homey_carousel.slick({
                rtl: homey_is_rtl,
                lazyLoad: 'ondemand',
                infinite: slide_infinite,
                autoplay: autoplay,
                autoplaySpeed: autoplaySpeed,
                speed: 300,
                slidesToShow: columns,
                slidesToScroll: slidesToScroll,
                arrows: navigation,
                adaptiveHeight: true,
                dots: dots,
                appendArrows: '.listing-carousel-next-prev-'+token,
                prevArrow: '<button type="button" class="slick-prev">'+prev_text+'</button>',
                nextArrow: '<button type="button" class="slick-next">'+next_text+'</button>',
                responsive: [
                {
                    breakpoint: 992,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
                },
                {
                    breakpoint: 769,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }]
            });
        
    });

});