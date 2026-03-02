 (function($) {
    "use strict";

    var payment_gateway = $(".homey_check_gateway:checked").val();

    $(".homey_check_gateway").on('click', function() {
        select_payment_gateway($(this).val());

    });

    function select_payment_gateway(payment_gateway) {
        if ( payment_gateway == 'stripe' ) {
            $('#without_stripe').hide();
            $('#stripe_main_wrap').show();
        } else {
            $('#without_stripe').show();
            $('#stripe_main_wrap').hide();
        }
    }
    select_payment_gateway(payment_gateway);

    

    function homey_stripe_payment_sca() {
        'use strict';
        var stripe = Stripe( HOMEY_stripe_vars.stripe_publishable_key );
        var elements = stripe.elements({
            fonts: [
              {
                cssSrc: 'https://fonts.googleapis.com/css?family=Roboto',
              },
            ],
            locale: 'auto'
        });


        var style = {
          base: {
            color: "#32325d",
            fontFamily: "-apple-system, BlinkMacSystemFont, sans-serif",
            fontSmoothing: "antialiased",
            fontSize: "16px",
            "::placeholder": {
              color: "#aab7c4"
            }
          },
          invalid: {
            color: "#fa755a",
            iconColor: "#fa755a"
          }
        };

        var card = elements.create('card', {
          iconStyle: 'solid',
          style: style
          
        });
        
        

        if(jQuery('#homey_stripe_card').length > 0 ){
            card.mount('#homey_stripe_card'); 
        }else{
            return;
        }


        card.addEventListener('change', function(event) {
                var displayError = document.getElementById('card-errors');
                if (event.error) {
                  displayError.textContent = event.error.message;
                } else {
                  displayError.textContent = '';
                }
        });

        var cardholderName = document.getElementById('stripe_cardholder_name');
        var cardholderEmail = document.getElementById('stripe_cardholder_email');
        var redirect_type = document.getElementById('redirect_type');
        var cardButton = document.getElementById('homey_stripe_submit_btn');
        var clientSecret = cardButton.dataset.secret;

        cardButton.addEventListener('click', function(ev) {
            ev.preventDefault();
            ev.stopPropagation();  
            jQuery('#homey_stripe_submit_btn').show();
            jQuery("#homey_stripe_submit_btn").prop('disabled', true);

            stripe.handleCardPayment(
              clientSecret, card, {
                  payment_method_data: {
                      billing_details: {
                        name:  cardholderName.value, 
                        email: cardholderEmail.value,
                        address: {
                            line1 : '510 Townsend St',
                            postal_code : '98140',
                            city : 'San Francisco',
                            state : 'CA',
                            country : 'US'
                        }
                      }
                  },


              }
            ).then(function(result) {
                jQuery("#homey_stripe_submit_btn").prop('disabled', false);
                if (result.error) {
                    jQuery('#homey_stripe_message').empty().show().html('<div class="alert alert-danger alert-dismissible" role="alert">'+HOMEY_stripe_vars.payment_failed+'</div>');
                
                } else {
                
                    setTimeout(function(){ 
                        jQuery('#homey_stripe_message').empty().show().html('<div class="alert alert-success alert-dismissible" role="alert">Successfully Paid, Redirecting...</div>');

                        
                        setTimeout(function(){ 
                            
                            if( redirect_type.value == 'back_to_listing_with_featured' ) {
                                window.location.href = HOMEY_stripe_vars.featured_return_link;

                            } else if( redirect_type.value == "reservation_detail_link" ) {
                                window.location.href = HOMEY_stripe_vars.reservation_return_link;
                            }

                            
                        }, 2800);

                    }, 6500);

                } // else
            });
          
        });

    };
    homey_stripe_payment_sca();   

    
})(jQuery); // End Document ready