/**
 * Unhotel Theme - Main JavaScript
 * 
 * @package Unhotel
 */

(function ($) {
    'use strict';

    // Document Ready
    $(document).ready(function () {

        // Mobile Menu Toggle (if needed in future)
        // $('.menu-toggle').on('click', function() {
        //     $('.main-navigation').toggleClass('toggled');
        // });

        // Smooth scroll for anchor links
        $('a[href^="#"]').on('click', function (e) {
            var target = $(this.hash);
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 800);
            }
        });

        // Add responsive class to tables
        $('table').wrap('<div class="table-responsive"></div>');

        // Add responsive class to iframes (for videos)
        $('iframe').wrap('<div class="iframe-responsive"></div>');

        // Skip link focus fix for accessibility
        var isWebkit = navigator.userAgent.toLowerCase().indexOf('webkit') > -1;
        var isOpera = navigator.userAgent.toLowerCase().indexOf('opera') > -1;
        var isIe = navigator.userAgent.toLowerCase().indexOf('msie') > -1;

        if ((isWebkit || isOpera || isIe) && document.getElementById && window.addEventListener) {
            window.addEventListener('hashchange', function () {
                var id = location.hash.substring(1);
                var element;

                if (!(/^[A-z0-9_-]+$/.test(id))) {
                    return;
                }

                element = document.getElementById(id);

                if (element) {
                    if (!(/^(?:a|select|input|button|textarea)$/i.test(element.tagName))) {
                        element.tabIndex = -1;
                    }
                    element.focus();
                }
            }, false);
        }

        // Theme initialized
    });

    // Window Load
    $(window).on('load', function () {
        // Any actions after full page load
    });

    // Window Scroll
    $(window).on('scroll', function () {
        // Parallax effect for header background
        var $header = $('.site-header[data-parallax]');
        if ($header.length) {
            var parallaxSpeed = parseFloat($header.data('parallax')) || 1.5;
            var scrollTop = $(window).scrollTop();
            var headerTop = $header.offset().top;
            var headerHeight = $header.outerHeight();
            var $parallaxInner = $header.find('.header-parallax-inner');

            if ($parallaxInner.length) {
                // Calculate parallax offset
                // When header is in viewport, move background slower than scroll
                var windowHeight = $(window).height();
                var headerBottom = headerTop + headerHeight;
                var viewportTop = scrollTop;
                var viewportBottom = scrollTop + windowHeight;

                // Only apply parallax when header is visible
                if (viewportBottom > headerTop && viewportTop < headerBottom) {
                    // Calculate scroll progress through the header
                    // When at top of header: offset = 0
                    // When scrolled past header: offset = max
                    var scrollProgress = (scrollTop - headerTop) / headerHeight;
                    scrollProgress = Math.max(0, Math.min(1, scrollProgress)); // Clamp between 0 and 1

                    // Parallax offset: move background up as we scroll down
                    // The speed factor makes it move slower (creating parallax effect)
                    // Very subtle effect: use smaller multiplier for gentle parallax
                    var parallaxOffset = scrollProgress * headerHeight * (1 - 1 / parallaxSpeed) * 0.2;

                    // Apply transform
                    $parallaxInner.css('transform', 'translateY(' + parallaxOffset + 'px)');
                } else if (scrollTop < headerTop) {
                    // Before header: reset to initial position
                    $parallaxInner.css('transform', 'translateY(0)');
                } else {
                    // After header: keep at max offset
                    var maxOffset = headerHeight * (1 - 1 / parallaxSpeed) * 0.2;
                    $parallaxInner.css('transform', 'translateY(' + maxOffset + 'px)');
                }
            }
        }
    });

    // Trigger parallax on page load
    $(window).trigger('scroll');

    // Window Resize
    $(window).on('resize', function () {
        // Resize-based actions can be added here
    });


    // $('.menu-item-has-children').on('hover', function() {
    //     $(this).find('.sub-menu').addClass('active');
    // });
    $('#site-navigation ul .menu-item-has-children').on({
        mouseenter: function () {
            $(this).addClass('active');
        },
        mouseleave: function () {
            $(this).removeClass('active');
        },
    });
    // $('.menu-item-has-children').on('mouseleave', function() {
    //     $(this).children('.sub-menu').first().removeClass('active');
    // });

})(jQuery);
