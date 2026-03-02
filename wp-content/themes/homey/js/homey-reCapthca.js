/**
 * Created by waqasriaz on 11/04/17.
 */
if (typeof homey_reCaptcha !== "undefined") {
    var reCaptchaIDs = [];
    var site_key = homey_reCaptcha.site_key;
    var secret_key = homey_reCaptcha.secret_key;
    var is_singular_listing = homey_reCaptcha.is_singular_listing;
    var homey_logged_in = homey_reCaptcha.homey_logged_in;
    
    var homeyReCaptchaLoad = function() {
        jQuery( '.homey_google_reCaptcha' ).each( function( index, el ) {
            var tempID = grecaptcha.render( el, {
                'sitekey' : site_key
            } );
            reCaptchaIDs.push( tempID );
        } );
    };

    //Reset reCaptcha
    var homeyReCaptchaReset = function() {
        if( typeof reCaptchaIDs != 'undefined' ) {
            var arrayLength = reCaptchaIDs.length;
            for( var i = 0; i < arrayLength; i++ ) {
                grecaptcha.reset( reCaptchaIDs[i] );
            }
        }
    };     
}