(function ($) {
    'use strict';

    // Listen for JetFormBuilder successful submit
    $(document).on('ready', function () {
        if (window.location.search.includes('status=success')) {
            // Show global success message
            const $globalMessage = $('#global-jfb-success');

            if ($globalMessage.length) {
                $globalMessage.show();
                // Scroll to message
                $('html, body').animate({
                    scrollTop: $globalMessage.offset().top - 40
                }, 500);

                setTimeout(function () {
                    $globalMessage.fadeOut();
                }, 10*1000);
            }
        }
    });

})(jQuery);



const vkSearchBoxInterval = setInterval(function () {
    // Old search box
    if (jQuery('.vbdivsearch.vbo-search-mainview form').length) {
        // Change form action
        // If english param in url, change action to /en/search
        if (window.location.href.indexOf('/en/') > -1) {
            jQuery('.vbdivsearch.vbo-search-mainview form').attr('action', '/en/search');
        } else {
            jQuery('.vbdivsearch.vbo-search-mainview form').attr('action', '/busca');
        }
        // Add close button
        jQuery('.vbdivsearch').prepend('<div class="close-search"></div>');
        // Clear interval
        clearInterval(vkSearchBoxInterval);
    }

    if (jQuery('a.vbo-pref-color-btn-secondary').length) {
        let link = jQuery('a.vbo-pref-color-btn-secondary').attr('href');
        if (window.location.href.indexOf('/en/') > -1 && link.indexOf('/en/') === -1) {
            link = link.replace('/search', '/en/search');
            jQuery('a.vbo-pref-color-btn-secondary').attr('href', link);
        }
        // Clear interval
        clearInterval(vkSearchBoxInterval);
    }
    // New search box - VikBooking search widget
    if (jQuery('.vbmodhorsearchmaindiv form').length) {
        // Change form action
        // If english param in url, change action to /en/search
        if (window.location.href.indexOf('/en/') > -1) {
            jQuery('.vbmodhorsearchmaindiv form').attr('action', '/en/search');
            // Change label text
            jQuery('.vbmodhorsearchmaindiv form .vbmodhscategories').text('Area');
        } else {
            jQuery('.vbmodhorsearchmaindiv form').attr('action', '/busca');
        }
        // Group 3 elements in a div
        jQuery('.vbmodhorsearchmaindiv .vbmodhorsearchtotnights, .vbmodhorsearchmaindiv .vbmodhorsearchrac').wrapAll('<div class="vbpeoplenights-wrap"></div>');
        // Remove unwanted categories
        if (jQuery('.vbmodhorsearchmaindiv select[name=categories]').length) {
            // Remove any option that does not begin with #
            jQuery('.vbmodhorsearchmaindiv select[name=categories] option').each(function () {
                if (jQuery(this).text().indexOf('#') !== 0) {
                    jQuery(this).remove();
                } else {
                    // Remove # from the beginning of the text
                    jQuery(this).text(jQuery(this).text().substring(1));
                }
            });
            // Add empty default option to select
            jQuery('.vbmodhorsearchmaindiv select[name=categories]').prepend('<option value="" selected="selected">--</option>');
        }
        // Add close button
        jQuery('.vbmodhorsearchmaindiv form').prepend('<div class="close-search"></div>');
        // Clear interval
        clearInterval(vkSearchBoxInterval);
    }
}, 200);

jQuery(document).ready(function () {

    // Homepage transparent header
    jQuery('body').addClass('custom-vk_page custom-vk_page_search-box');
    jQuery('body.home .nav-area').addClass('header-type-1 transparent-header');
    jQuery(window).resize();

    // Room details header
    jQuery('body.custom-vk_page_room-details > .nav-area').removeClass('header-type-1 transparent-header');
    jQuery('body.custom-vk_page_room-details #homey_nav_sticky').removeAttr('style');

    // Mobile menu and search popup
    // Mobile menu
    // Clone user menu
    if (jQuery('.user-nav-wrap').length) {
        jQuery('#user-nav').clone().removeClass('collapse').removeAttr('id').appendTo('.uh-menu-profile');
        jQuery('.uh-menu-profile').find('i').remove();
    }
    jQuery('.uh-mobile-menu').on('click', 'a', function () {
        // Remove active class from all elements
        jQuery('.uh-mobile-menu a').removeClass('active');

        // Toggle secondary menu
        if (jQuery(this).hasClass('uh-menu-link')) {
            jQuery('.uh-mobile-menu .uh-menu-secondary').slideToggle(function () {
                if (jQuery('.uh-mobile-menu .uh-menu-secondary').is(':visible')) {
                    jQuery('.uh-mobile-menu .uh-menu-link').addClass('active');
                } else {
                    jQuery('.uh-mobile-menu .uh-menu-link').removeClass('active');
                }
            });
        }
        // Toggle language switcher
        if (jQuery(this).hasClass('uh-language-link')) {
            jQuery('.uh-mobile-menu .uh-menu-language').slideToggle(function () {
                if (jQuery('.uh-mobile-menu .uh-menu-language').is(':visible')) {
                    jQuery('.uh-mobile-menu .uh-language-link').addClass('active');
                } else {
                    jQuery('.uh-mobile-menu .uh-language-link').removeClass('active');
                }
            });
        }
        // Toggle login popup
        if (jQuery(this).hasClass('uh-account-link')) {
            if (jQuery(this).hasClass('avatar')) {
                jQuery('.uh-mobile-menu .uh-menu-profile').slideToggle(function () {
                    if (jQuery('.uh-mobile-menu .uh-menu-profile').is(':visible')) {
                        jQuery('.uh-mobile-menu .uh-account-link').addClass('active');
                    } else {
                        jQuery('.uh-mobile-menu .uh-account-link').removeClass('active');
                    }
                });
            } else {
                jQuery('a[data-target="#modal-login"]').first().click();
            }
        }

        // When secondary menu is open, close language switcher
        if (jQuery(this).hasClass('uh-menu-link')) {
            jQuery('.uh-menu-language, .uh-menu-profile, .vbdivsearch, .vbmodhorsearchmaindiv').slideUp();
        }
        // When language switcher is open, close secondary menu
        if (jQuery(this).hasClass('uh-language-link')) {
            jQuery('.uh-menu-secondary, .uh-menu-profile, .vbdivsearch, .vbmodhorsearchmaindiv').slideUp();
        }
        // When profile menu is open, close secondary menu and language switcher
        if (jQuery(this).hasClass('uh-account-link')) {
            jQuery('.uh-menu-secondary, .uh-menu-language, .vbdivsearch, .vbmodhorsearchmaindiv').slideUp();
        }

        // When secondary menu or language switcher is open animate the logo
        if (jQuery(this).hasClass('uh-language-link') || jQuery(this).hasClass('uh-menu-link')) {
            jQuery('.uh-logo-link').toggleClass('menu-open');
        }
    });
    // Close mobile menu when clicking outside
    jQuery(document).on('click', function (event) {
        if (!jQuery(event.target).closest('.uh-mobile-menu').length) {
            jQuery('.uh-mobile-menu .uh-menu-secondary').slideUp();
            jQuery('.uh-mobile-menu .uh-menu-language').slideUp();
            jQuery('.uh-mobile-menu .uh-menu-link').removeClass('active');
            jQuery('.uh-mobile-menu .uh-language-link').removeClass('active');
            jQuery('.uh-logo-link').removeClass('menu-open');
        }
    });
    // Search popup
    // Open search popup
    jQuery('.vbdivsearch-toggle span').on('click', function () {
        // Close other menus
        jQuery('.uh-menu-secondary, .uh-menu-language, .uh-menu-profile').slideUp();
        // Toggle search
        jQuery('.vbdivsearch, .vbmodhorsearchmaindiv').slideToggle();
        jQuery('body').toggleClass('mobile-search-open');
    });
    // Close search popup
    jQuery('.close-search').on('click', function () {
        jQuery('.vbdivsearch, .vbmodhorsearchmaindiv').slideToggle();
        jQuery('body').toggleClass('mobile-search-open');
    });

    // Fix main menu login popup double open (because of 2 bootstrap included)
    jQuery('.login-register a').on('click', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        jQuery('#modal-login').modal('show');
        return false;
    });

    // After check-in date is filled, focus on check-out date
    jQuery('.vbmodhorsearchmaindiv .vbmodhorsearchcheckindiv').on('click', function () {
        var checkindate = jQuery('.vbmodhorsearchmaindiv .vbmodhorsearchcheckindiv input[name=checkindate]').val();
        var checkInInterval = setInterval(function () {
            if (jQuery('.vbmodhorsearchmaindiv .vbmodhorsearchcheckindiv input[name=checkindate]').val() != checkindate) {
                clearInterval(checkInInterval);
                jQuery('.vbmodhorsearchmaindiv input[name=checkoutdate]').focus();
            }
        }, 100);
    });

    // Mobile only
    if (window.matchMedia("(max-width: 650px)").matches) {
        // Add read more functionality to descriptions
        addReadMore('.vbo-rdet-descprice-block');
        // Add read more functionality to room caracteristics
        addReadMore('.vbo-room-carats');

        // Show more/less description
        jQuery(document).on('click', '.vbo-read-more', function () {
            var target = jQuery(jQuery(this).data('target'));
            target.toggleClass('vbo-collapsed');
            var caretDown = '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="6" viewBox="0 0 10 6" fill="none" aria-hidden="true"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            var caretUp = '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="6" viewBox="0 0 10 6" fill="none" aria-hidden="true"><path d="M1 5L5 1L9 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            if (target.hasClass('vbo-collapsed')) {
                jQuery(this).html(caretDown);
            } else {
                jQuery(this).html(caretUp);
            }
        });
    }
});

// Read more functionality
function addReadMore(element) {
    if (jQuery(element).length) {
        if (jQuery(element).height() > 100) {
            jQuery(element).addClass('vbo-collapsed');
            var caretDown = '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="6" viewBox="0 0 10 6" fill="none" aria-hidden="true"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            jQuery(element).after('<div class="vbo-read-more" data-target="' + element + '">' + caretDown + '</div>');
        }
    }
}
