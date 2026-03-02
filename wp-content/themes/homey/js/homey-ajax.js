jQuery(document).ready(function ($) {
  'use strict';

  if (typeof HOMEY_ajax_vars !== 'undefined') {
    var ajaxurl = HOMEY_ajax_vars.admin_url + 'admin-ajax.php';
    var login_redirect_type = HOMEY_ajax_vars.redirect_type;
    var login_redirect = HOMEY_ajax_vars.login_redirect;
    var is_singular_listing = HOMEY_ajax_vars.is_singular_listing;
    var paypal_connecting = HOMEY_ajax_vars.paypal_connecting;
    var login_sending = HOMEY_ajax_vars.login_loading;
    var process_loader_spinner = HOMEY_ajax_vars.process_loader_spinner;
    var currency_updating_msg = HOMEY_ajax_vars.currency_updating_msg;
    var homey_date_format = HOMEY_ajax_vars.homey_date_format;
    var userID = HOMEY_ajax_vars.user_id;
    var homey_reCaptcha = HOMEY_ajax_vars.homey_reCaptcha;
    var processing_text = HOMEY_ajax_vars.processing_text;
    var is_listing_detail = HOMEY_ajax_vars.is_listing_detail;
    var booked_hours_array = HOMEY_ajax_vars.booked_hours_array;
    var pending_hours_array = HOMEY_ajax_vars.pending_hours_array;
    var booking_start_hour = HOMEY_ajax_vars.booking_start_hour;
    var booking_end_hour = HOMEY_ajax_vars.booking_end_hour;
    var homey_min_book_days = HOMEY_ajax_vars.homey_min_book_days;

    if (booked_hours_array !== '' && booked_hours_array.length !== 0) {
      booked_hours_array = JSON.parse(booked_hours_array);
    }

    if (pending_hours_array !== '' && pending_hours_array.length !== 0) {
      pending_hours_array = JSON.parse(pending_hours_array);
    }

    var is_tansparent = HOMEY_ajax_vars.homey_tansparent;
    var retina_logo = HOMEY_ajax_vars.retina_logo;
    var retina_logo_splash = HOMEY_ajax_vars.retina_logo_splash;
    var retina_logo_mobile = HOMEY_ajax_vars.retina_logo_mobile;
    var retina_logo_mobile_splash = HOMEY_ajax_vars.retina_logo_mobile_splash;
    var no_more_listings = HOMEY_ajax_vars.no_more_listings;
    var allow_additional_guests = HOMEY_ajax_vars.allow_additional_guests;
    var allowed_guests_num = HOMEY_ajax_vars.allowed_guests_num;
    var num_additional_guests = HOMEY_ajax_vars.num_additional_guests;
    var agree_term_text = HOMEY_ajax_vars.agree_term_text;
    var choose_gateway_text = HOMEY_ajax_vars.choose_gateway_text;
    var success_icon = HOMEY_ajax_vars.success_icon;
    var calendar_link = HOMEY_ajax_vars.calendar_link;
    var focusedInput_2 = null;

    var allowed_guests_plus_additional =
      parseInt(allowed_guests_num) + parseInt(num_additional_guests);

    var compare_url = HOMEY_ajax_vars.compare_url;
    var add_compare = HOMEY_ajax_vars.add_compare;
    var remove_compare = HOMEY_ajax_vars.remove_compare;
    var compare_limit = HOMEY_ajax_vars.compare_limit;
    var homey_booking_type = HOMEY_ajax_vars.homey_booking_type;

    var homey_is_rtl = HOMEY_ajax_vars.homey_is_rtl;

    if (homey_is_rtl == 'yes') {
      homey_is_rtl = true;
    } else {
      homey_is_rtl = false;
    }

    var homey_is_mobile = false;
    if (
      /Android|webOS|iPhone|iPad|iPod|tablet|BlackBerry|IEMobile|Opera Mini/i.test(
        navigator.userAgent
      )
    ) {
      homey_is_mobile = true;
    }

    var homey_window_width = $(window).width();

    /*var homey_timeStamp_2 = function(str) {
          return new Date(str.replace(/^(\d{2}\-)(\d{2}\-)(\d{4})$/,
            '$2$1$3')).getTime();
        };*/

    var homey_processing_modal = function (msg) {
      var process_modal =
        '<div class="modal fade" id="homey_modal" tabindex="-1" role="dialog" aria-labelledby="homeyModalLabel" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-body homey_messages_modal">' +
        msg +
        '</div></div></div></div></div>';
      $('body').append(process_modal);
      $('#homey_modal').modal();
    };

    var homey_processing_modal_close = function () {
      $('#homey_modal').modal('hide');
    };

    var homey_timeStamp_2 = function (str) {
      var myDate = str.split('-');
      var newDate = myDate[1] + '/' + myDate[0] + '/' + myDate[2];
      return new Date(newDate).getTime();
    };
    /*--------------------------------------------------------------------------
     *   Retina Logo
     * -------------------------------------------------------------------------*/
    if (window.devicePixelRatio == 2) {
      if (is_tansparent) {
        if (retina_logo_splash != '') {
          $('.transparent-header .homey_logo img').attr('src', retina_logo_splash);
        }

        if (retina_logo_mobile_splash != '') {
          $('.mobile-logo img').attr('src', retina_logo_mobile_splash);
        }
      } else {
        if (retina_logo != '') {
          $('.homey_logo img').attr('src', retina_logo);
        }

        if (retina_logo_mobile != '') {
          $('.mobile-logo img').attr('src', retina_logo_mobile);
        }
      }
    }

    /*--------------------------------------------------------------------------
     *  Currency Switcher
     * -------------------------------------------------------------------------*/
    var currencySwitcherList = $('#homey-currency-switcher-list');
    if (currencySwitcherList.length > 0) {
      $('#homey-currency-switcher-list > li').on('click', function (e) {
        e.stopPropagation();
        currencySwitcherList.slideUp(200);

        var selectedCurrencyCode = $(this).data('currency-code');

        if (selectedCurrencyCode) {
          $('.homey-selected-currency span').html(selectedCurrencyCode);
          homey_processing_modal(
            '<i class="' + process_loader_spinner + '"></i> ' + currency_updating_msg
          );

          $.ajax({
            url: ajaxurl,
            dataType: 'JSON',
            method: 'POST',
            data: {
              action: 'homey_currency_converter',
              currency_to_converter: selectedCurrencyCode,
            },
            success: function (res) {
              if (res.success) {
                window.location.reload();
              } else {
                console.log(res);
              }
            },
            error: function (xhr, status, error) {
              var err = eval('(' + xhr.responseText + ')');
              console.log(err.Message);
            },
          });
        }
      });
    }

    $('.homey-currency-switcher').on('change', function (e) {
      var selectedCurrencyCode = $(this).val();

      if (selectedCurrencyCode) {
        homey_processing_modal(
          '<i class="' + process_loader_spinner + '"></i> ' + currency_updating_msg
        );

        $.ajax({
          url: ajaxurl,
          dataType: 'JSON',
          method: 'POST',
          data: {
            action: 'homey_currency_converter',
            currency_to_converter: selectedCurrencyCode,
          },
          success: function (res) {
            if (res.success) {
              window.location.reload();
            } else {
              console.log(res);
            }
          },
          error: function (xhr, status, error) {
            var err = eval('(' + xhr.responseText + ')');
            console.log(err.Message);
          },
        });
      }
    });

    /*--------------------------------------------------------------------------
     *  Module Ajax Pagination
     * -------------------------------------------------------------------------*/
    var listings_module_section = $('#listings_module_section');
    if (listings_module_section.length > 0) {
      $('body').on('click', '.homey-loadmore a', function (e) {
        e.preventDefault();
        var $this = $(this);
        var $wrap = $this.closest('#listings_module_section').find('#module_listings');

        var limit = $this.data('limit');
        var paged = $this.data('paged');
        var style = $this.data('style');
        var type = $this.data('type');
        var roomtype = $this.data('roomtype');
        var country = $this.data('country');
        var state = $this.data('state');
        var city = $this.data('city');
        var area = $this.data('area');
        var featured = $this.data('featured');
        var offset = $this.data('offset');
        var sortby = $this.data('sortby');
        var booking_type = $this.data('booking_type');
        var author = $this.data('author');
        var authorid = $this.data('authorid');

        $.ajax({
          type: 'POST',
          url: ajaxurl,
          data: {
            action: 'homey_loadmore_listings',
            limit: limit,
            paged: paged,
            style: style,
            type: type,
            roomtype: roomtype,
            country: country,
            state: state,
            city: city,
            area: area,
            featured: featured,
            sort_by: sortby,
            offset: offset,
            booking_type: booking_type,
            author: author,
            authorid: authorid,
          },
          beforeSend: function () {
            $this.find('i').css('display', 'inline-block');
          },
          success: function (data) {
            if (data == 'no_result') {
              $this
                .closest('#listings_module_section')
                .find('.homey-loadmore')
                .text(no_more_listings);
              return;
            }
            $wrap.append(data);
            $this.data('paged', paged + 1);

            homey_init_add_favorite(ajaxurl, userID, is_singular_listing);
            homey_init_remove_favorite(ajaxurl, userID, is_singular_listing);
            compare_for_ajax();
          },
          complete: function () {
            $this.find('i').css('display', 'none');
          },
          error: function (xhr, status, error) {
            var err = eval('(' + xhr.responseText + ')');
            console.log(err.Message);
          },
        });
      });
    }

    /*--------------------------------------------------------------------------
     *   Add or remove favorites
     * -------------------------------------------------------------------------*/
    homey_init_add_favorite(ajaxurl, userID, is_singular_listing);
    homey_init_remove_favorite(ajaxurl, userID, is_singular_listing);

    /*--------------------------------------------------------------------------
     *   Compare for ajax
     * -------------------------------------------------------------------------*/
    var compare_for_ajax = function () {
      var listings_compare = homeyGetCookie('homey_compare_listings');
      var limit_item_compare = 4;
      add_to_compare(
        compare_url,
        add_compare,
        remove_compare,
        compare_limit,
        listings_compare,
        limit_item_compare
      );
      remove_from_compare(listings_compare, add_compare, remove_compare);
    };

    /* ------------------------------------------------------------------------ */
    /*  Paypal single listing payment
         /* ------------------------------------------------------------------------ */
    $('#homey_complete_order').on('click', function (e) {
      e.preventDefault();
      var hform, payment_gateway, listing_id, is_upgrade;

      payment_gateway = $("input[name='homey_payment_type']:checked").val();
      is_upgrade = $("input[name='is_upgrade']").val();

      listing_id = $('#listing_id').val();

      if (payment_gateway == 'paypal') {
        homey_processing_modal(paypal_connecting);
        homey_paypal_payment(listing_id, is_upgrade);
      } else if (payment_gateway == 'stripe') {
        var hform = $(this).parents('.dashboard-area');
        hform.find('.homey_stripe_simple button').trigger('click');
      }
      return;
    });

    /* ------------------------------------------------------------------------ */
    /*  Paypal payment function
         /* ------------------------------------------------------------------------ */
    var homey_paypal_payment = function (listing_id, is_upgrade) {
      $.ajax({
        type: 'post',
        url: ajaxurl,
        data: {
          action: 'homey_listing_paypal_payment',
          listing_id: listing_id,
          is_upgrade: is_upgrade,
        },
        success: function (response) {
          window.location.href = response;
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
      });
    };

    if ($('#add_review').length > 0) {
      $('#add_review').on('click', function (e) {
        e.preventDefault();

        var $this = $(this);
        var rating = $('#rating').val();
        var review_action = $('#review_action').val();
        var review_content = $('#review_content').val();
        var review_reservation_id = $('#review_reservation_id').val();
        var security = $('#review-security').val();
        var parentDIV = $this.parents('.user-dashboard-right');

        $.ajax({
          type: 'post',
          url: ajaxurl,
          dataType: 'json',
          data: {
            action: 'homey_add_review',
            rating: rating,
            review_action: review_action,
            review_content: review_content,
            review_reservation_id: review_reservation_id,
            security: security,
          },
          beforeSend: function () {
            $this.children('i').remove();
            $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
          },
          success: function (data) {
            parentDIV.find('.alert').remove();
            if (data.success) {
              $this.attr('disabled', true);
              window.location.reload();
            } else {
              parentDIV
                .find('.dashboard-area')
                .prepend(
                  '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>' +
                    data.message +
                    '</div>'
                );
            }
          },
          error: function (xhr, status, error) {
            var err = eval('(' + xhr.responseText + ')');
            console.log(err.Message);
          },
          complete: function () {
            $this.children('i').removeClass(process_loader_spinner);
          },
        });
      });
    }

    if ($('#add_guest_review').length > 0) {
      $('#add_guest_review').on('click', function (e) {
        e.preventDefault();

        var $this = $(this);
        var rating = $('#rating').val();
        var review_action = $('#review_guest_action').val();
        var review_content = $('#review_content').val();
        var review_guest_reservation_id = $('#review_guest_reservation_id').val();
        var security = $('#review-security').val();
        var parentDIV = $this.parents('.user-dashboard-right');

        $.ajax({
          type: 'post',
          url: ajaxurl,
          dataType: 'json',
          data: {
            action: 'homey_add_guest_review',
            rating: rating,
            review_action: review_action,
            review_content: review_content,
            review_guest_reservation_id: review_guest_reservation_id,
            security: security,
          },
          beforeSend: function () {
            $this.children('i').remove();
            $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
          },
          success: function (data) {
            parentDIV.find('.alert').remove();
            if (data.success) {
              $this.attr('disabled', true);
              window.location.reload();
            } else {
              parentDIV
                .find('.dashboard-area')
                .prepend(
                  '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>' +
                    data.message +
                    '</div>'
                );
            }
          },
          error: function (xhr, status, error) {
            var err = eval('(' + xhr.responseText + ')');
            console.log(err.Message);
          },
          complete: function () {
            $this.children('i').removeClass(process_loader_spinner);
          },
        });
      });
    }

    var listing_review_ajax = function (sortby, listing_id, paged) {
      var review_container = $('#homey_reviews');
      $.ajax({
        type: 'post',
        url: ajaxurl,
        data: {
          action: 'homey_ajax_review',
          sortby: sortby,
          listing_id: listing_id,
          paged: paged,
        },
        beforeSend: function () {},
        success: function (data) {
          review_container.empty();
          review_container.html(data);
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {},
      });
    };

    if ($('#sort_review').length > 0) {
      $('#sort_review').on('change', function () {
        var sortby = $(this).val();
        var listing_id = $('#review_listing_id').val();
        var paged = $('#review_paged').val();
        listing_review_ajax(sortby, listing_id, paged);
        return;
      });
    }

    if ($('#review_next').length > 0) {
      $('#review_next').on('click', function (e) {
        e.preventDefault();
        $('#review_prev').removeAttr('disabled');
        var sortby = $('#page_sort').val();
        var total_pages = $('#total_pages').val();
        var listing_id = $('#review_listing_id').val();
        var paged = $('#review_paged').val();
        paged = Number(paged) + 1;
        $('#review_paged').val(paged);

        if (paged == total_pages) {
          $(this).attr('disabled', true);
        }
        listing_review_ajax(sortby, listing_id, paged);
        return;
      });
    }

    if ($('#review_prev').length > 0) {
      $('#review_prev').on('click', function (e) {
        e.preventDefault();
        $('#review_next').removeAttr('disabled');
        var sortby = $('#page_sort').val();
        var listing_id = $('#review_listing_id').val();
        var paged = $('#review_paged').val();
        paged = Number(paged) - 1;
        $('#review_paged').val(paged);
        if (paged <= 1) {
          $(this).attr('disabled', true);
        }
        listing_review_ajax(sortby, listing_id, paged);
        return;
      });
    }

    /* ------------------------------------------------------------------------ */
    /* Set date format
        /* ------------------------------------------------------------------------ */
    var homey_convert_date = function (date) {
      if (date == '') {
        return '';
      }

      var d_format, return_date;

      d_format = homey_date_format.toUpperCase();

      var changed_date_format = d_format.replace('YY', 'YYYY');
      var return_date = moment(date, changed_date_format).format('YYYY-MM-DD');

      return return_date;
    };

    var homey_calculate_booking_cost = function (
      check_in_date,
      check_out_date,
      guests,
      listing_id,
      security,
      extra_options
    ) {
      var $this = $(this);
      var notify = $('.homey_notification');
      notify.find('.notify').remove();

      if (check_in_date === '' || check_out_date === '') {
        $('#homey_booking_cost, .payment-list').empty();
        return;
      }

      $.ajax({
        type: 'post',
        url: ajaxurl,
        data: {
          action: 'homey_calculate_booking_cost',
          check_in_date: check_in_date,
          check_out_date: check_out_date,
          guests: guests,
          extra_options: extra_options,
          listing_id: listing_id,
          security: security,
        },
        beforeSend: function () {
          $('#homey_booking_cost, .payment-list').empty();
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
          notify.find('.homey_preloader').show();
        },
        success: function (data) {
          var price_tag_open = data.indexOf('<price>');

          if (price_tag_open > -1) {
            var price_tag_close = data.indexOf('</price>');
            var price_tag_open_length = '<price>'.length;

            // Get nightly value between <price></price> tag:
            var dyn_nightly_price = data.substring(
              price_tag_open + price_tag_open_length,
              price_tag_close
            );

            // Remove the price from the string:
            data = data.split('<price>')[0];

            var dyn_price_str =
              dyn_nightly_price.indexOf('.') > -1
                ? parseFloat(dyn_nightly_price).toFixed(2).split('.').join(',')
                : parseFloat(dyn_nightly_price);

            var dyn_nightly_price_str = '<sup>R$</sup>' + dyn_price_str + '<sub>/por noite</sub>';

            $('.block-body-sidebar .item-price, .overlay-booking-btn .overlay-booking-price')
              .empty()
              .html(dyn_nightly_price_str);
          }

          $('#homey_booking_cost, .payment-list').empty().html(data);
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          $this.children('i').removeClass(process_loader_spinner);
          notify.find('.homey_preloader').hide();
        },
      });
    };

    var homey_calculate_hourly_booking_cost = function (
      check_in_date,
      start_hour,
      end_hour,
      guests,
      listing_id,
      security,
      extra_options
    ) {
      var $this = $(this);
      var notify = $('.homey_notification');
      notify.find('.notify').remove();

      if (check_in_date === '' || start_hour === '' || end_hour === '') {
        $('#homey_booking_cost, .payment-list').empty();
        return;
      }

      $.ajax({
        type: 'post',
        url: ajaxurl,
        data: {
          action: 'homey_calculate_hourly_booking_cost',
          check_in_date: check_in_date,
          start_hour: start_hour,
          end_hour: end_hour,
          guests: guests,
          extra_options: extra_options,
          listing_id: listing_id,
          security: security,
        },
        beforeSend: function () {
          $('#homey_booking_cost, .payment-list').empty();
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
          notify.find('.homey_preloader').show();
        },
        success: function (data) {
          $('#homey_booking_cost, .payment-list').empty().html(data);
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          $this.children('i').removeClass(process_loader_spinner);
          notify.find('.homey_preloader').hide();
        },
      });
    };

    var check_booking_availability_on_date_change = function (
      check_in_date,
      check_out_date,
      listing_id,
      security
    ) {
      var $this = $(this);

      var notify = $('.homey_notification');
      notify.find('.notify').remove();

      $('.homey_extra_price input').each(function () {
        $(this).prop('checked', false);
      });

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'check_booking_availability_on_date_change',
          check_in_date: check_in_date,
          check_out_date: check_out_date,
          listing_id: listing_id,
          security: security,
        },
        beforeSend: function () {
          $('#homey_booking_cost, .payment-list').empty();
          notify.find('.homey_preloader').show();
        },
        success: function (data) {
          if (data.success) {
            $('#request_for_reservation, #request_for_reservation_mobile').removeAttr('disabled');
            $('#instance_reservation, #instance_reservation_mobile').removeAttr('disabled');
            notify.prepend(
              '<div class="notify text-success text-center btn-success-outlined btn btn-full-width">' +
                data.message +
                '</div>'
            );
          } else {
            notify.prepend(
              '<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">' +
                data.message +
                '</div>'
            );
            $('#request_for_reservation, #request_for_reservation_mobile').attr('disabled', true);
            $('#instance_reservation, #instance_reservation_mobile').attr('disabled', true);
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          notify.find('.homey_preloader').hide();
        },
      });
    };

    var check_booking_availability_on_hour_change = function (
      check_in_date,
      start_hour,
      end_hour,
      listing_id,
      security
    ) {
      var $this = $(this);

      var notify = $('.homey_notification');
      notify.find('.notify').remove();

      $('.homey_extra_price input').each(function () {
        $(this).prop('checked', false);
      });

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'check_booking_availability_on_hour_change',
          check_in_date: check_in_date,
          start_hour: start_hour,
          end_hour: end_hour,
          listing_id: listing_id,
          security: security,
        },
        beforeSend: function () {
          $('#homey_booking_cost, .payment-list').empty();
          notify.find('.homey_preloader').show();
        },
        success: function (data) {
          if (data.success) {
            $('#request_hourly_reservation, #request_hourly_reservation_mobile').removeAttr(
              'disabled'
            );
            $('#instance_hourly_reservation, #instance_hourly_reservation_mobile').removeAttr(
              'disabled'
            );
            notify.prepend(
              '<div class="notify text-success text-center btn-success-outlined btn btn-full-width">' +
                data.message +
                '</div>'
            );
          } else {
            notify.prepend(
              '<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">' +
                data.message +
                '</div>'
            );
            $('#request_hourly_reservation, #request_hourly_reservation_mobile').attr(
              'disabled',
              true
            );
            $('#instance_hourly_reservation, #instance_hourly_reservation_mobile').attr(
              'disabled',
              true
            );
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          notify.find('.homey_preloader').hide();
        },
      });
    };

    // Single listing booking form
    $('#single-listing-date-range input').on('focus', function () {
      $('.single-listing-booking-calendar-js').css('display', 'block');
      $('.single-listing-booking-calendar-js').addClass('arrive_active');
      $('.single-form-guests-js').css('display', 'none');
      focusedInput_2 = $(this).attr('name');
      $('.single-listing-booking-calendar-js')
        .removeClass('arrive_active depart_active')
        .addClass(focusedInput_2 + '_active');
    });

    $('.single-guests-js input').on('focus', function () {
      $(this).prev('label').css('display', 'block');
      $(this).addClass('on-focus');
      $('.single-form-guests-js').css('display', 'block');
    });

    var numClicks = 0;
    var fromTimestamp_2,
      toTimestamp_2 = 0; // init start and end timestamps

    var homey_booking_dates = function () {
      $('.single-listing-booking-calendar-js ul li').on('click', function () {
        var $this = $(this);
        $this.show();
        if ($this.hasClass('past-day') || $this.hasClass('homey-not-available-for-booking')) {
          if (!$this.hasClass('reservation_start')) {
            return false;
          }
        }

        numClicks += 1;
        var vl = $this.data('formatted-date');
        var timestamp = $this.data('timestamp');

        // if modify days after selecting once
        if (focusedInput_2 == 'depart' && timestamp > fromTimestamp_2) {
          $('.single-listing-calendar-wrap ul')
            .find('li.to-day')
            .removeClass('selected')
            .siblings()
            .removeClass('to-day in-between');

          numClicks = 2;
        }

        if (numClicks == 1) {
          fromTimestamp_2 = timestamp;

          //day nodes
          $('.single-listing-calendar-wrap ul li').removeClass(
            'to-day from-day selected in-between'
          );
          $this.addClass('from-day selected');
          // move caret
          $('.single-listing-booking-calendar-js')
            .removeClass('arrive_active')
            .addClass('depart_active');

          $('input[name="arrive"]').val(vl);
          $('input[name="depart"]').val('');

          if (homey_booking_type != 'per_hour') {
            homey_calculate_price_checkin();
          }
        } else if (numClicks == 2) {
          toTimestamp_2 = timestamp;
          //day end node
          $this.addClass('to-day selected');
          $('.single-listing-booking-calendar-js')
            .removeClass('depart_active')
            .addClass('arrive_active');

          var check_in_date = $('input[name="arrive"]').val();
          check_in_date = homey_timeStamp_2(check_in_date);
          var check_out_date = homey_timeStamp_2(vl);

          if (check_in_date >= check_out_date) {
            fromTimestamp_2 = timestamp;
            toTimestamp_2 = 0;
            //day nodes
            $('.single-listing-calendar-wrap ul li').removeClass(
              'to-day from-day selected in-between'
            );
            $this.addClass('from-day selected');

            // move caret
            $('.single-listing-booking-calendar-js')
              .removeClass('arrive_active')
              .addClass('depart_active');

            $('input[name="arrive"]').val(vl);
            numClicks = 1;
          } else {
            setInBetween_2(fromTimestamp_2, toTimestamp_2);
            $('input[name="depart"]').val(vl);
            $('#single-booking-search-calendar, #single-overlay-booking-search-calendar').hide();

            if (homey_booking_type != 'per_hour') {
              homey_calculate_price_checkout();
            }
          }
        }
        if (numClicks == 2) {
          numClicks = 0;
        }
      });
    };

    //Run only for daily/nighty booking

    if (homey_booking_type != 'per_hour') {
      homey_booking_dates();

      $('.single-listing-calendar-wrap ul li').on('hover', function () {
        var ts = $(this).data('timestamp');
        if (numClicks == 1) {
          setInBetween_2(fromTimestamp_2, ts);
        }
      });
      /*
       * method to send in-between days
       * */
      var setInBetween_2 = function (fromTime, toTime) {
        $('.single-listing-calendar-wrap ul li')
          .removeClass('in-between')
          .filter(function () {
            var currentTs = $(this).data('timestamp');
            return currentTs > fromTime && currentTs < toTime;
          })
          .addClass('in-between');
      };

      var homey_calculate_price_checkin = function () {
        var check_in_date = $('input[name="arrive"]').val();
        check_in_date = homey_convert_date(check_in_date);

        var check_out_date = $('input[name="depart"]').val();
        check_out_date = homey_convert_date(check_out_date);

        var guests = $('input[name="guests"]').val();
        var listing_id = $('#listing_id').val();
        var security = $('#reservation-security').val();
        homey_calculate_booking_cost(check_in_date, check_out_date, guests, listing_id, security);
      };

      if (is_singular_listing == 'yes') {
        homey_calculate_price_checkin();
      }

      var homey_calculate_price_checkout = function () {
        var check_in_date = $('input[name="arrive"]').val();
        check_in_date = homey_convert_date(check_in_date);

        var check_out_date = $('input[name="depart"]').val();
        check_out_date = homey_convert_date(check_out_date);

        var guests = $('input[name="guests"]').val();
        var listing_id = $('#listing_id').val();
        var security = $('#reservation-security').val();
        homey_calculate_booking_cost(check_in_date, check_out_date, guests, listing_id, security);
        check_booking_availability_on_date_change(
          check_in_date,
          check_out_date,
          listing_id,
          security
        );
      };

      // Run price checkout valition on page lod
      // to prevent links with GET params from search result
      // to skip validation:
      if ($('input[name="arrive"]').val() && $('input[name="depart"]').val()) {
        homey_calculate_price_checkout();
      }

      $('.apply_guests').on('click', function () {
        var check_in_date = $('input[name="arrive"]').val();
        check_in_date = homey_convert_date(check_in_date);

        var check_out_date = $('input[name="depart"]').val();
        check_out_date = homey_convert_date(check_out_date);

        var guests = $('input[name="guests"]').val();
        var listing_id = $('#listing_id').val();
        var security = $('#reservation-security').val();
        homey_calculate_booking_cost(check_in_date, check_out_date, guests, listing_id, security);
        check_booking_availability_on_date_change(
          check_in_date,
          check_out_date,
          listing_id,
          security
        );
      });

      $('.homey_extra_price input').on('click', function () {
        var extra_options = [];
        var temp_opt;

        $('.homey_extra_price input').each(function () {
          if ($(this).is(':checked')) {
            var extra_name = $(this).data('name');
            var extra_price = $(this).data('price');
            var extra_type = $(this).data('type');
            temp_opt = '';
            temp_opt = extra_name;
            temp_opt = temp_opt + '|' + extra_price;
            temp_opt = temp_opt + '|' + extra_type;
            extra_options.push(temp_opt);
          }
        });

        var check_in_date = $('input[name="arrive"]').val();
        check_in_date = homey_convert_date(check_in_date);

        var check_out_date = $('input[name="depart"]').val();
        check_out_date = homey_convert_date(check_out_date);

        var guests = $('input[name="guests"]').val();
        var listing_id = $('#listing_id').val();
        var security = $('#reservation-security').val();
        homey_calculate_booking_cost(
          check_in_date,
          check_out_date,
          guests,
          listing_id,
          security,
          extra_options
        );
      });
    }

    if (homey_booking_type == 'per_hour') {
      $('.hourly-js-desktop ul li').on('click', function () {
        var $this = $(this);
        var vl = $this.data('formatted-date');
        $('input[name="arrive"]').val(vl);

        $('.single-listing-hourly-calendar-wrap ul li').removeClass('selected');
        $this.addClass('selected');

        $('#single-booking-search-calendar, #single-overlay-booking-search-calendar').hide();

        var check_in_date = $('input[name="arrive"]').val();
        check_in_date = homey_convert_date(check_in_date);

        var start_hour = $('select[name="start_hour"]').val();
        var end_hour = $('select[name="end_hour"]').val();
        var guests = $('input[name="guests"]').val();
        var listing_id = $('#listing_id').val();
        var security = $('#reservation-security').val();
        homey_calculate_hourly_booking_cost(
          check_in_date,
          start_hour,
          end_hour,
          guests,
          listing_id,
          security
        );

        if (check_in_date === '' || start_hour === '' || end_hour === '') return;
        check_booking_availability_on_hour_change(
          check_in_date,
          start_hour,
          end_hour,
          listing_id,
          security
        );
      });

      $('#start_hour').on('change', function () {
        var check_in_date = $('input[name="arrive"]').val();
        check_in_date = homey_convert_date(check_in_date);

        var start_hour = $('select[name="start_hour"]').val();
        var end_hour = $('select[name="end_hour"]').val();
        var guests = $('input[name="guests"]').val();
        var listing_id = $('#listing_id').val();
        var security = $('#reservation-security').val();
        homey_calculate_hourly_booking_cost(
          check_in_date,
          start_hour,
          end_hour,
          guests,
          listing_id,
          security
        );

        if (check_in_date === '' || start_hour === '' || end_hour === '') return;
        check_booking_availability_on_hour_change(
          check_in_date,
          start_hour,
          end_hour,
          listing_id,
          security
        );
      });

      $('#end_hour').on('change', function () {
        var check_in_date = $('input[name="arrive"]').val();
        check_in_date = homey_convert_date(check_in_date);

        var start_hour = $('select[name="start_hour"]').val();
        var end_hour = $('select[name="end_hour"]').val();
        var guests = $('input[name="guests"]').val();
        var listing_id = $('#listing_id').val();
        var security = $('#reservation-security').val();
        homey_calculate_hourly_booking_cost(
          check_in_date,
          start_hour,
          end_hour,
          guests,
          listing_id,
          security
        );
        check_booking_availability_on_hour_change(
          check_in_date,
          start_hour,
          end_hour,
          listing_id,
          security
        );
      });

      $('.hourly-js-mobile ul li').on('click', function () {
        var $this = $(this);
        var vl = $this.data('formatted-date');
        $('input[name="arrive"]').val(vl);

        $('.single-listing-hourly-calendar-wrap ul li').removeClass('selected');
        $this.addClass('selected');

        $('#single-booking-search-calendar, #single-overlay-booking-search-calendar').hide();

        var check_in_date = $('input[name="arrive"]').val();
        check_in_date = homey_convert_date(check_in_date);

        var start_hour = $('#start_hour_overlay').val();
        var end_hour = $('#end_hour_overlay').val();
        var guests = $('input[name="guests"]').val();
        var listing_id = $('#listing_id').val();
        var security = $('#reservation-security').val();
        homey_calculate_hourly_booking_cost(
          check_in_date,
          start_hour,
          end_hour,
          guests,
          listing_id,
          security
        );

        if (check_in_date === '' || start_hour === '' || end_hour === '') return;
        check_booking_availability_on_hour_change(
          check_in_date,
          start_hour,
          end_hour,
          listing_id,
          security
        );
      });

      $('#start_hour_overlay').on('change', function () {
        var check_in_date = $('input[name="arrive"]').val();
        check_in_date = homey_convert_date(check_in_date);

        var start_hour = $('#start_hour_overlay').val();
        var end_hour = $('#end_hour_overlay').val();
        var guests = $('input[name="guests"]').val();
        var listing_id = $('#listing_id').val();
        var security = $('#reservation-security').val();
        homey_calculate_hourly_booking_cost(
          check_in_date,
          start_hour,
          end_hour,
          guests,
          listing_id,
          security
        );

        if (check_in_date === '' || start_hour === '' || end_hour === '') return;
        check_booking_availability_on_hour_change(
          check_in_date,
          start_hour,
          end_hour,
          listing_id,
          security
        );
      });

      $('#end_hour_overlay').on('change', function () {
        var check_in_date = $('input[name="arrive"]').val();
        check_in_date = homey_convert_date(check_in_date);

        var start_hour = $('#start_hour_overlay').val();
        var end_hour = $('#end_hour_overlay').val();
        var guests = $('input[name="guests"]').val();
        var listing_id = $('#listing_id').val();
        var security = $('#reservation-security').val();
        homey_calculate_hourly_booking_cost(
          check_in_date,
          start_hour,
          end_hour,
          guests,
          listing_id,
          security
        );
        check_booking_availability_on_hour_change(
          check_in_date,
          start_hour,
          end_hour,
          listing_id,
          security
        );
      });

      $('.apply_guests').on('click', function () {
        var check_in_date = $('input[name="arrive"]').val();
        check_in_date = homey_convert_date(check_in_date);

        var start_hour = $('select[name="start_hour"]').val();
        var end_hour = $('select[name="end_hour"]').val();
        var guests = $('input[name="guests"]').val();
        var listing_id = $('#listing_id').val();
        var security = $('#reservation-security').val();
        homey_calculate_hourly_booking_cost(
          check_in_date,
          start_hour,
          end_hour,
          guests,
          listing_id,
          security
        );
        check_booking_availability_on_hour_change(
          check_in_date,
          start_hour,
          end_hour,
          listing_id,
          security
        );
      });

      $('#apply_guests_hourly').on('click', function () {
        var check_in_date = $('input[name="arrive"]').val();
        check_in_date = homey_convert_date(check_in_date);

        var start_hour = $('#start_hour_overlay').val();
        var end_hour = $('#end_hour_overlay').val();
        var guests = $('input[name="guests"]').val();
        var listing_id = $('#listing_id').val();
        var security = $('#reservation-security').val();
        homey_calculate_hourly_booking_cost(
          check_in_date,
          start_hour,
          end_hour,
          guests,
          listing_id,
          security
        );
        check_booking_availability_on_hour_change(
          check_in_date,
          start_hour,
          end_hour,
          listing_id,
          security
        );
      });

      $('.homey_extra_price input').on('click', function () {
        var extra_options = [];
        var temp_opt;

        $('.homey_extra_price input').each(function () {
          if ($(this).is(':checked')) {
            var extra_name = $(this).data('name');
            var extra_price = $(this).data('price');
            var extra_type = $(this).data('type');
            temp_opt = '';
            temp_opt = extra_name;
            temp_opt = temp_opt + '|' + extra_price;
            temp_opt = temp_opt + '|' + extra_type;
            extra_options.push(temp_opt);
          }
        });

        var check_in_date = $('input[name="arrive"]').val();
        check_in_date = homey_convert_date(check_in_date);

        var start_hour = $('select[name="start_hour"]').val();
        var end_hour = $('select[name="end_hour"]').val();

        var guests = $('input[name="guests"]').val();
        var listing_id = $('#listing_id').val();
        var security = $('#reservation-security').val();
        homey_calculate_hourly_booking_cost(
          check_in_date,
          start_hour,
          end_hour,
          guests,
          listing_id,
          security,
          extra_options
        );
      });
    }

    /* ------------------------------------------------------------------------ */
    /*  Guests count
        /* ------------------------------------------------------------------------ */

    var single_listing_guests = function () {
      $('.adult_plus').on('click', function (e) {
        e.preventDefault();
        var guests = parseInt($('input[name="guests"]').val()) || 0;
        var adult_guest = parseInt($('input[name="adult_guest"]').val());
        var child_guest = parseInt($('input[name="child_guest"]').val());

        adult_guest++;
        $('.homey_adult').text(adult_guest);
        $('input[name="adult_guest"]').val(adult_guest);

        var total_guests = adult_guest + child_guest;

        if (allow_additional_guests != 'yes' && total_guests == allowed_guests_num) {
          $('.adult_plus').attr('disabled', true);
          $('.child_plus').attr('disabled', true);
        } else if (
          allow_additional_guests == 'yes' &&
          total_guests == allowed_guests_plus_additional
        ) {
          if (num_additional_guests !== '') {
            $('.adult_plus').attr('disabled', true);
            $('.child_plus').attr('disabled', true);
          }
        }

        $('input[name="guests"]').val(total_guests);
      });

      $('.adult_minus').on('click', function (e) {
        e.preventDefault();
        var guests = parseInt($('input[name="guests"]').val()) || 0;
        var adult_guest = parseInt($('input[name="adult_guest"]').val());
        var child_guest = parseInt($('input[name="child_guest"]').val());

        if (adult_guest == 0) return;
        adult_guest--;
        $('.homey_adult').text(adult_guest);
        $('input[name="adult_guest"]').val(adult_guest);

        var total_guests = adult_guest + child_guest;
        $('input[name="guests"]').val(total_guests);

        $('.adult_plus').removeAttr('disabled');
        $('.child_plus').removeAttr('disabled');
      });

      $('.child_plus').on('click', function (e) {
        e.preventDefault();
        var guests = parseInt($('input[name="guests"]').val());
        var child_guest = parseInt($('input[name="child_guest"]').val());
        var adult_guest = parseInt($('input[name="adult_guest"]').val());

        child_guest++;
        $('.homey_child').text(child_guest);
        $('input[name="child_guest"]').val(child_guest);

        var total_guests = child_guest + adult_guest;

        if (allow_additional_guests != 'yes' && total_guests == allowed_guests_num) {
          $('.adult_plus').attr('disabled', true);
          $('.child_plus').attr('disabled', true);
        } else if (
          allow_additional_guests == 'yes' &&
          total_guests == allowed_guests_plus_additional
        ) {
          if (num_additional_guests !== '') {
            $('.adult_plus').attr('disabled', true);
            $('.child_plus').attr('disabled', true);
          }
        }

        $('input[name="guests"]').val(total_guests);
      });

      $('.child_minus').on('click', function (e) {
        e.preventDefault();
        var guests = parseInt($('input[name="guests"]').val());
        var child_guest = parseInt($('input[name="child_guest"]').val());
        var adult_guest = parseInt($('input[name="adult_guest"]').val());

        if (child_guest == 0) return;
        child_guest--;
        $('.homey_child').text(child_guest);
        $('input[name="child_guest"]').val(child_guest);

        var total_guests = child_guest + adult_guest;

        $('input[name="guests"]').val(total_guests);

        $('.adult_plus').removeAttr('disabled');
        $('.child_plus').removeAttr('disabled');
      });
    };
    single_listing_guests();

    /* ------------------------------------------------------------------------ */
    /*  Reservation Request
         /* ------------------------------------------------------------------------ */
    $('#request_for_reservation, #request_for_reservation_mobile').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var extra_options = [];
      var temp_opt;
      var check_in_date = $('input[name="arrive"]').val();
      check_in_date = homey_convert_date(check_in_date);

      var check_out_date = $('input[name="depart"]').val();
      check_out_date = homey_convert_date(check_out_date);

      var guest_message = $('textarea[name="guest_message"]').val();

      var guests = $('input[name="guests"]').val();
      var listing_id = $('#listing_id').val();
      var security = $('#reservation-security').val();
      var notify = $this.parents('.homey_notification');
      notify.find('.notify').remove();

      save_booking_details(
        $('input[name="arrive"]').val(),
        $('input[name="depart"]').val(),
        guests,
        guest_message
      );

      $('.homey_extra_price input').each(function () {
        if ($(this).is(':checked')) {
          var extra_name = $(this).data('name');
          var extra_price = $(this).data('price');
          var extra_type = $(this).data('type');
          temp_opt = '';
          temp_opt = extra_name;
          temp_opt = temp_opt + '|' + extra_price;
          temp_opt = temp_opt + '|' + extra_type;
          extra_options.push(temp_opt);
        }
      });

      if (parseInt(userID, 10) === 0) {
        $('#modal-login').modal('show');
      } else {
        $.ajax({
          type: 'post',
          url: ajaxurl,
          dataType: 'json',
          data: {
            action: 'homey_add_reservation',
            check_in_date: check_in_date,
            check_out_date: check_out_date,
            guests: guests,
            listing_id: listing_id,
            extra_options: extra_options,
            guest_message: guest_message,
            security: security,
          },
          beforeSend: function () {
            $this.children('i').remove();
            $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
          },
          success: function (data) {
            if (data.success) {
              $('.check_in_date, .check_out_date').val('');
              notify.prepend(
                '<div class="notify text-success text-center btn-success-outlined btn btn-full-width">' +
                  data.message +
                  '</div>'
              );
            } else {
              notify.prepend(
                '<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">' +
                  data.message +
                  '</div>'
              );
            }
          },
          error: function (xhr, status, error) {
            var err = eval('(' + xhr.responseText + ')');
            console.log(err.Message);
          },
          complete: function () {
            $this.children('i').removeClass(process_loader_spinner);
          },
        }); //
      }
    });

    /* ------------------------------------------------------------------------ */
    /*  Hourly Reservation Request
         /* ------------------------------------------------------------------------ */
    $('#request_hourly_reservation').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var check_in_date = $('input[name="arrive"]').val();
      check_in_date = homey_convert_date(check_in_date);

      var guest_message = $('textarea[name="guest_message"]').val();

      var start_hour = $('select[name="start_hour"]').val();
      var end_hour = $('select[name="end_hour"]').val();
      var guests = $('input[name="guests"]').val();
      var listing_id = $('#listing_id').val();
      var security = $('#reservation-security').val();
      var notify = $this.parents('.homey_notification');
      notify.find('.notify').remove();
      var extra_options = [];
      var temp_opt;
      $('.homey_extra_price input').each(function () {
        if ($(this).is(':checked')) {
          var extra_name = $(this).data('name');
          var extra_price = $(this).data('price');
          var extra_type = $(this).data('type');
          temp_opt = '';
          temp_opt = extra_name;
          temp_opt = temp_opt + '|' + extra_price;
          temp_opt = temp_opt + '|' + extra_type;
          extra_options.push(temp_opt);
        }
      });

      if (parseInt(userID, 10) === 0) {
        $('#modal-login').modal('show');
      } else {
        $.ajax({
          type: 'post',
          url: ajaxurl,
          dataType: 'json',
          data: {
            action: 'homey_add_hourly_reservation',
            check_in_date: check_in_date,
            start_hour: start_hour,
            end_hour: end_hour,
            guests: guests,
            extra_options: extra_options,
            guest_message: guest_message,
            listing_id: listing_id,
            security: security,
          },
          beforeSend: function () {
            $this.children('i').remove();
            $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
          },
          success: function (data) {
            if (data.success) {
              $('.check_in_date, .check_out_date').val('');
              notify.prepend(
                '<div class="notify text-success text-center btn-success-outlined btn btn-full-width">' +
                  data.message +
                  '</div>'
              );
            } else {
              notify.prepend(
                '<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">' +
                  data.message +
                  '</div>'
              );
            }
          },
          error: function (xhr, status, error) {
            var err = eval('(' + xhr.responseText + ')');
            console.log(err.Message);
          },
          complete: function () {
            $this.children('i').removeClass(process_loader_spinner);
          },
        }); // end ajax
      }
    });

    /* ------------------------------------------------------------------------ */
    /*  Hourly Reservation Request
         /* ------------------------------------------------------------------------ */
    $('#request_hourly_reservation_mobile').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var check_in_date = $('input[name="arrive"]').val();
      check_in_date = homey_convert_date(check_in_date);

      var guest_message = $('textarea[name="guest_message"]').val();

      var start_hour = $('#start_hour_overlay').val();
      var end_hour = $('#end_hour_overlay').val();
      var guests = $('input[name="guests"]').val();
      var listing_id = $('#listing_id').val();
      var security = $('#reservation-security').val();
      var notify = $this.parents('.homey_notification');
      notify.find('.notify').remove();

      var extra_options = [];
      var temp_opt;
      $('.homey_extra_price input').each(function () {
        if ($(this).is(':checked')) {
          var extra_name = $(this).data('name');
          var extra_price = $(this).data('price');
          var extra_type = $(this).data('type');
          temp_opt = '';
          temp_opt = extra_name;
          temp_opt = temp_opt + '|' + extra_price;
          temp_opt = temp_opt + '|' + extra_type;
          extra_options.push(temp_opt);
        }
      });

      if (parseInt(userID, 10) === 0) {
        $('#modal-login').modal('show');
      } else {
        $.ajax({
          type: 'post',
          url: ajaxurl,
          dataType: 'json',
          data: {
            action: 'homey_add_hourly_reservation',
            check_in_date: check_in_date,
            start_hour: start_hour,
            end_hour: end_hour,
            guests: guests,
            extra_options: extra_options,
            guest_message: guest_message,
            listing_id: listing_id,
            security: security,
          },
          beforeSend: function () {
            $this.children('i').remove();
            $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
          },
          success: function (data) {
            if (data.success) {
              $('.check_in_date, .check_out_date').val('');
              notify.prepend(
                '<div class="notify text-success text-center btn-success-outlined btn btn-full-width">' +
                  data.message +
                  '</div>'
              );
            } else {
              notify.prepend(
                '<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">' +
                  data.message +
                  '</div>'
              );
            }
          },
          error: function (xhr, status, error) {
            var err = eval('(' + xhr.responseText + ')');
            console.log(err.Message);
          },
          complete: function () {
            $this.children('i').removeClass(process_loader_spinner);
          },
        }); // end ajax
      }
    });

    /* ------------------------------------------------------------------------ */
    /*  Reserve a period host
         /* ------------------------------------------------------------------------ */
    $('#reserve_period_host').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var check_in_date = $('#period_start_date').val();
      //check_in_date = homey_convert_date(check_in_date);

      var check_out_date = $('#period_end_date').val();
      //check_out_date = homey_convert_date(check_out_date);

      var listing_id = $('#period_listing_id').val();
      var period_note = $('#period_note').val();
      var security = $('#period-security').val();
      var notify = $('.homey_notification');
      notify.find('.notify').remove();

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_reserve_period_host',
          check_in_date: check_in_date,
          check_out_date: check_out_date,
          period_note: period_note,
          listing_id: listing_id,
          security: security,
        },
        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (data) {
          if (data.success) {
            notify.prepend(
              '<div class="notify text-success text-center btn-success-outlined btn btn-full-width">' +
                data.message +
                '</div>'
            );
            window.location.href = calendar_link;
          } else {
            notify.prepend(
              '<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">' +
                data.message +
                '</div>'
            );
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          $this.children('i').removeClass(process_loader_spinner);
        },
      });
    });
    //alert(HOMEY_ajax_vars.homey_timezone);
    /* ------------------------------------------------------------------------ */
    /* Per Hour availability calendar
        /* ------------------------------------------------------------------------ */
    var homey_hourly_availability_calendar = function () {
      var today = new Date();
      var listing_booked_dates = [];
      var listing_pending_dates = [];
      var html_booked_info = "<div id='html_booked_info' style='display:none;'>";
      for (var key in booked_hours_array) {
        if (booked_hours_array.hasOwnProperty(key) && key !== '') {
          var temp_book = [];
          (temp_book['title'] = HOMEY_ajax_vars.hc_reserved_label),
            (temp_book['start'] = moment.unix(key).utc().format()),
            (temp_book['end'] = moment.unix(booked_hours_array[key]).utc().format()),
            (temp_book['editable'] = false);
          temp_book['color'] = '#fdd2d2';
          temp_book['textColor'] = '#444444';
          html_booked_info +=
            "<span data-start-hours='" +
            moment.unix(key).utc().format('h:mm') +
            "' data-end-hours='" +
            moment.unix(booked_hours_array[key]).utc().format('h:mm') +
            "' data-date='" +
            moment.unix(key).utc().format('D-M-Y') +
            "'></span>";
          listing_booked_dates.push(temp_book);
        }
      }
      html_booked_info += '</div>';
      $('body').append(html_booked_info);
      for (var key_pending in pending_hours_array) {
        if (pending_hours_array.hasOwnProperty(key_pending) && key_pending !== '') {
          var temp_pending = [];
          (temp_pending['title'] = HOMEY_ajax_vars.hc_pending_label),
            (temp_pending['start'] = moment.unix(key_pending).utc().format()),
            (temp_pending['end'] = moment.unix(pending_hours_array[key_pending]).utc().format()),
            (temp_pending['editable'] = false);
          temp_pending['color'] = '#ffeedb';
          temp_pending['textColor'] = '#333333';
          listing_pending_dates.push(temp_pending);
        }
      }

      var hours_slot = $.merge(listing_booked_dates, listing_pending_dates);
      var calendarEl = document.getElementById('homey_hourly_calendar');

      var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: HOMEY_ajax_vars.homey_current_lang,
        timeZone: HOMEY_ajax_vars.homey_timezone,
        plugins: ['timeGrid'],
        defaultView: 'timeGridWeek',
        slotDuration: '00:30:00',
        minTime: booking_start_hour,
        maxTime: booking_end_hour,
        events: hours_slot,
        defaultDate: today,
        selectHelper: true,
        selectOverlap: false,
        footer: false,
        nowIndicator: true,
        allDayText: HOMEY_ajax_vars.hc_hours_label,
        weekNumbers: false,
        weekNumbersWithinDays: true,
        weekNumberCalculation: 'ISO',
        editable: false,
        eventLimit: true,
        unselectAuto: false,
        isRTL: homey_is_rtl,
        buttonText: {
          today: HOMEY_ajax_vars.hc_today_label,
        },
      });

      calendar.render();
    };

    if (homey_booking_type == 'per_hour' && is_listing_detail == 'yes') {
      if ($('#homey_hourly_calendar').length > 0) {
        homey_hourly_availability_calendar();
      }
    }

    var prev_date = $('#hourly_check_inn').val();

    function disable_hourlystart_and_end() {
      if ($('#hourly_check_inn').val() != '') {
        $('span').removeClass('already-booked');

        var all_booked = $('#post_already_bookd_dates').find('span');
        var start_hours = $('.start_hour').find('.text');
        var end_hours = $('.end_hour').find('.text');
        var homey_date_format_moment = homey_date_format.replace('mm', 'M');
        homey_date_format_moment = homey_date_format_moment.replace('dd', 'D');
        homey_date_format_moment = homey_date_format_moment.replace('yy', 'Y');
        var date_selected = moment($('#hourly_check_inn').val(), homey_date_format_moment).format(
          'D-M-Y'
        );

        $(start_hours).each(function (i, start_itm) {
          $(start_itm).closest('li').removeClass('disabled');
        });

        $(end_hours).each(function (i, end_itm) {
          $(end_itm).closest('li').removeClass('disabled');
        });

        $(start_hours).each(function (i, start_itm) {
          var current_time = '';
          if ($(start_itm).text().indexOf(':') > -1) {
            current_time = $(start_itm).text();
            current_time = current_time.replace(' am', '');
            current_time = current_time.replace(' pm', '');

            current_time = date_selected + ' ' + current_time + ':00';
            $(all_booked).each(function (i, itm) {
              var already_booked_date = $(itm).data('datetime');
              // console.log(already_booked_date+' kki'+ current_time);

              var ms = moment(already_booked_date, 'DD/MM/YYYY HH:mm:ss').diff(
                moment(current_time, 'DD/MM/YYYY HH:mm:ss')
              );
              var d = moment.duration(ms);

              if (d == 0) {
                $(start_itm).addClass('already-booked');
                $(start_itm).closest('li').addClass('disabled');
              }
              // else {
              //     $(start_itm).closest('li').removeClass('disabled');
              //     $(start_itm).removeClass("already-booked");
              // }
            });
          }
        });

        $(end_hours).each(function (i, end_itm) {
          var current_time = '';
          if ($(end_itm).text().indexOf(':') > -1) {
            current_time = $(end_itm).text();
            current_time = current_time.replace(' am', '');
            current_time = current_time.replace(' pm', '');

            current_time = date_selected + ' ' + current_time + ':00';
            $(all_booked).each(function (i, itm) {
              var already_booked_date = $(itm).data('datetime');
              // console.log(already_booked_date+' kki'+ current_time);

              var ms = moment(already_booked_date, 'DD/MM/YYYY HH:mm:ss').diff(
                moment(current_time, 'DD/MM/YYYY HH:mm:ss')
              );
              var d = moment.duration(ms);

              if (d == 0) {
                $(end_itm).addClass('already-booked');
                $(end_itm).closest('li').addClass('disabled');
              }
              // else {
              //     $(end_itm).closest('li').removeClass('disabled');
              //     $(end_itm).removeClass("already-booked");
              // }
            });
          }
        });
      }

      prev_date = $('#hourly_check_inn').val();
    }

    setInterval(function () {
      if (prev_date != $('#hourly_check_inn').val()) {
        disable_hourlystart_and_end();
      }
    }, 500);

    /* ------------------------------------------------------------------------ */
    /*  Instace Booking
         /* ------------------------------------------------------------------------ */
    $('#instance_reservation, #instance_reservation_mobile').on('click', function (e) {
      e.preventDefault();

      var extra_options = [];
      var temp_opt;
      var $this = $(this);
      var check_in_date = $('input[name="arrive"]').val();
      check_in_date = homey_convert_date(check_in_date);

      var check_out_date = $('input[name="depart"]').val();
      check_out_date = homey_convert_date(check_out_date);

      var guests = $('input[name="guests"]').val();
      var guest_message = $('textarea[name="guest_message"]').val();
      var listing_id = $('#listing_id').val();
      var security = $('#reservation-security').val();
      var notify = $this.parents('.homey_notification');
      notify.find('.notify').remove();

      save_booking_details(
        $('input[name="arrive"]').val(),
        $('input[name="depart"]').val(),
        guests,
        guest_message
      );

      $('.homey_extra_price input').each(function () {
        if ($(this).is(':checked')) {
          var extra_name = $(this).data('name');
          var extra_price = $(this).data('price');
          var extra_type = $(this).data('type');
          temp_opt = '';
          temp_opt = extra_name;
          temp_opt = temp_opt + '|' + extra_price;
          temp_opt = temp_opt + '|' + extra_type;
          extra_options.push(temp_opt);
        }
      });

      if (parseInt(userID, 10) === 0) {
        $('#modal-login').modal('show');
      } else {
        $.ajax({
          type: 'POST',
          url: ajaxurl,
          dataType: 'json',
          data: {
            action: 'homey_instance_booking',
            check_in_date: check_in_date,
            check_out_date: check_out_date,
            guests: guests,
            extra_options: extra_options,
            guest_message: guest_message,
            listing_id: listing_id,
            security: security,
          },
          beforeSend: function () {
            $this.children('i').remove();
            $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
          },
          success: function (data) {
            if (data.success) {
              $('.check_in_date, .check_out_date').val('');
              notify.prepend(
                '<div class="notify text-success text-center btn-success-outlined btn btn-full-width">' +
                  data.message +
                  '</div>'
              );
              window.location.href = data.instance_url;
            } else {
              notify.prepend(
                '<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">' +
                  data.message +
                  '</div>'
              );
            }
          },
          error: function (xhr, status, error) {
            var err = eval('(' + xhr.responseText + ')');
            console.log(err.Message);
          },
          complete: function () {
            $this.children('i').removeClass(process_loader_spinner);
          },
        });
      }
    });

    /* ------------------------------------------------------------------------ */
    /*  Hourly instace Booking
         /* ------------------------------------------------------------------------ */
    $('#instance_hourly_reservation, #instance_hourly_reservation_mobile').on(
      'click',
      function (e) {
        e.preventDefault();

        var $this = $(this);
        var start_hour;
        var end_hour;
        var check_in_date = $('input[name="arrive"]').val();
        check_in_date = homey_convert_date(check_in_date);

        start_hour = $('select[name="start_hour"]').val();
        end_hour = $('select[name="end_hour"]').val();
        var guests = $('input[name="guests"]').val();
        var guest_message = $('textarea[name="guest_message"]').val();
        var listing_id = $('#listing_id').val();
        var security = $('#reservation-security').val();
        var notify = $this.parents('.homey_notification');
        notify.find('.notify').remove();
        //saving booking details and adding to referer URL
        save_hourl_booking_details(
          $('input[name="arrive"]').val(),
          start_hour,
          end_hour,
          guests,
          guest_message
        );
        if (homey_is_mobile || homey_window_width < 991) {
          start_hour = $('#start_hour_overlay').val();
          end_hour = $('#end_hour_overlay').val();
        }

        var extra_options = [];
        var temp_opt;
        $('.homey_extra_price input').each(function () {
          if ($(this).is(':checked')) {
            var extra_name = $(this).data('name');
            var extra_price = $(this).data('price');
            var extra_type = $(this).data('type');
            temp_opt = '';
            temp_opt = extra_name;
            temp_opt = temp_opt + '|' + extra_price;
            temp_opt = temp_opt + '|' + extra_type;
            extra_options.push(temp_opt);
          }
        });

        $.ajax({
          type: 'POST',
          url: ajaxurl,
          dataType: 'json',
          data: {
            action: 'homey_instance_hourly_booking',
            check_in_date: check_in_date,
            start_hour: start_hour,
            end_hour: end_hour,
            guests: guests,
            extra_options: extra_options,
            listing_id: listing_id,
            guest_message: guest_message,
            security: security,
          },
          beforeSend: function () {
            $this.children('i').remove();
            $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
          },
          success: function (data) {
            if (data.success) {
              $('.check_in_date, .start_hour, .end_hour').val('');
              notify.prepend(
                '<div class="notify text-success text-center btn-success-outlined btn btn-full-width">' +
                  data.message +
                  '</div>'
              );
              window.location.href = data.instance_url;
            } else {
              notify.prepend(
                '<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">' +
                  data.message +
                  '</div>'
              );
            }
          },
          error: function (xhr, status, error) {
            var err = eval('(' + xhr.responseText + ')');
            console.log(err.Message);
          },
          complete: function () {
            $this.children('i').removeClass(process_loader_spinner);
          },
        });
      }
    );

    /* ------------------------------------------------------------------------ */
    /*  Confirm Reservation
         /* ------------------------------------------------------------------------ */
    $('.confirm-reservation').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var reservation_id = $this.data('reservation_id');
      var parentDIV = $this.parents('.user-dashboard-right');

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_confirm_reservation',
          reservation_id: reservation_id,
        },
        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (data) {
          parentDIV.find('.alert').remove();
          if (data.success) {
            parentDIV.find('.dashboard-area').prepend(data.message);
            $this.remove();
          } else {
            parentDIV.find('.dashboard-area').prepend(data.message);
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          $this.children('i').removeClass(process_loader_spinner);
        },
      });
    });

    /* ------------------------------------------------------------------------ */
    /*  Confirm Reservation
         /* ------------------------------------------------------------------------ */
    $('.confirm-offsite-reservation').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var reservation_id = $this.data('reservation_id');
      var parentDIV = $this.parents('.user-dashboard-right');

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_confirm_offsite_reservation',
          reservation_id: reservation_id,
        },
        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (data) {
          parentDIV.find('.alert').remove();
          if (data.success) {
            parentDIV.find('.dashboard-area').prepend(data.message);
            $this.remove();
          } else {
            parentDIV.find('.dashboard-area').prepend(data.message);
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          $this.children('i').removeClass(process_loader_spinner);
        },
      });
    });

    /* ------------------------------------------------------------------------ */
    /*  Confirm Reservation
         /* ------------------------------------------------------------------------ */
    $('#guest_paid_button').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var parentDIV = $this.parents('.user-dashboard-right');
      var reservation_id = $('#reservation_id').val();

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_guest_made_payment',
          reservation_id: reservation_id,
        },
        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (data) {
          parentDIV.find('.alert').remove();
          if (data.success) {
            parentDIV.find('.dashboard-area').prepend(data.message);
            $this.remove();
          } else {
            parentDIV.find('.dashboard-area').prepend(data.message);
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          $this.children('i').removeClass(process_loader_spinner);
        },
      });
    });

    /* ------------------------------------------------------------------------ */
    /*  Decline Reservation
         /* ------------------------------------------------------------------------ */
    $('#decline').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var reservation_id = $('#reservationID').val();
      var reason = $('#reason22').val();
      var parentDIV = $this.parents('.user-dashboard-right');

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_decline_reservation',
          reservation_id: reservation_id,
          reason: reason,
        },
        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (data) {
          parentDIV.find('.alert').remove();
          if (data.success) {
            $this.attr('disabled', true);
            window.location.reload();
          } else {
            parentDIV
              .find('.dashboard-area')
              .prepend(
                '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>' +
                  data.message +
                  '</div>'
              );
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          $this.children('i').removeClass(process_loader_spinner);
        },
      });
    });

    /* ------------------------------------------------------------------------ */
    /*  Decline Reservation
         /* ------------------------------------------------------------------------ */
    $('#decline_hourly').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var reservation_id = $('#reservationID').val();
      var reason = $('#reason').val();
      var parentDIV = $this.parents('.user-dashboard-right');

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_decline_hourly_reservation',
          reservation_id: reservation_id,
          reason: reason,
        },
        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (data) {
          parentDIV.find('.alert').remove();
          if (data.success) {
            $this.attr('disabled', true);
            window.location.reload();
          } else {
            parentDIV
              .find('.dashboard-area')
              .prepend(
                '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>' +
                  data.message +
                  '</div>'
              );
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          $this.children('i').removeClass(process_loader_spinner);
        },
      });
    });

    $('#decline-reservation-btn').click(function () {
      $('html, body').animate(
        {
          scrollTop: $('#decline-reservation').offset().top + 150,
        },
        'slow'
      );
    });

    $('#cancel-reservation-btn').click(function () {
      $('html, body').animate(
        {
          scrollTop: $('#cancel-reservation').offset().top + 150,
        },
        'slow'
      );
    });

    /* ------------------------------------------------------------------------ */
    /*  Cancel Reservation
         /* ------------------------------------------------------------------------ */
    $('#cancelled').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var reservation_id = $('#reservationID').val();
      var reason = $('#reason').val();
      var host_cancel = $('#host_cancel').val();
      var parentDIV = $this.parents('.user-dashboard-right');

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_cancelled_reservation',
          reservation_id: reservation_id,
          host_cancel: host_cancel,
          reason: reason,
        },
        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (data) {
          parentDIV.find('.alert').remove();
          if (data.success) {
            $this.attr('disabled', true);
            window.location.reload();
          } else {
            parentDIV
              .find('.dashboard-area')
              .prepend(
                '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>' +
                  data.message +
                  '</div>'
              );
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          $this.children('i').removeClass(process_loader_spinner);
        },
      });
    });

    /* ------------------------------------------------------------------------ */
    /*  Cancel Hourly Reservation
         /* ------------------------------------------------------------------------ */
    $('#cancelled_hourly').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var reservation_id = $('#reservationID').val();
      var reason = $('#reason').val();
      var host_cancel = $('#host_cancel').val();
      var parentDIV = $this.parents('.user-dashboard-right');

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_cancelled_hourly_reservation',
          reservation_id: reservation_id,
          host_cancel: host_cancel,
          reason: reason,
        },
        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (data) {
          parentDIV.find('.alert').remove();
          if (data.success) {
            $this.attr('disabled', true);
            window.location.reload();
          } else {
            parentDIV
              .find('.dashboard-area')
              .prepend(
                '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>' +
                  data.message +
                  '</div>'
              );
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          $this.children('i').removeClass(process_loader_spinner);
        },
      });
    });

    var homey_booking_paypal_payment = function ($this, reservation_id, security) {
      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_booking_paypal_payment',
          reservation_id: reservation_id,
          security: security,
        },
        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
          $('#homey_notify').html(
            '<div class="alert alert-success alert-dismissible" role="alert">' +
              paypal_connecting +
              '</div>'
          );
        },
        success: function (data) {
          if (data.success) {
            window.location.href = data.payment_execute_url;
          } else {
            $('#homey_notify').html(
              '<div class="alert alert-danger alert-dismissible" role="alert">' +
                data.message +
                '</div>'
            );
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
      });
    };

    var homey_hourly_booking_paypal_payment = function ($this, reservation_id, security) {
      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_hourly_booking_paypal_payment',
          reservation_id: reservation_id,
          security: security,
        },
        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
          $('#homey_notify').html(
            '<div class="alert alert-success alert-dismissible" role="alert">' +
              paypal_connecting +
              '</div>'
          );
        },
        success: function (data) {
          if (data.success) {
            window.location.href = data.payment_execute_url;
          } else {
            $('#homey_notify').html(
              '<div class="alert alert-danger alert-dismissible" role="alert">' +
                data.message +
                '</div>'
            );
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
      });
    };

    $('#make_booking_payment').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var reservation_id = $('#reservation_id').val();
      var security = $('#checkout-security').val();

      var payment_gateway = $("input[name='payment_gateway']:checked").val();
      if (payment_gateway == undefined) {
        $('#homey_notify').html(
          '<div class="alert alert-danger alert-dismissible" role="alert">' +
            choose_gateway_text +
            '</div>'
        );
      }

      if (payment_gateway === 'paypal') {
        homey_booking_paypal_payment($this, reservation_id, security);
      } else if (payment_gateway === 'stripe') {
        var hform = $(this).parents('.dashboard-area');
        hform.find('.homey_stripe_simple button').trigger('click');
        $('#homey_notify').html('');
      }
      return;
    });

    $('#make_hourly_booking_payment').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var reservation_id = $('#reservation_id').val();
      var security = $('#checkout-security').val();

      var payment_gateway = $("input[name='payment_gateway']:checked").val();
      if (payment_gateway == undefined) {
        $('#homey_notify').html(
          '<div class="alert alert-danger alert-dismissible" role="alert">' +
            choose_gateway_text +
            '</div>'
        );
      }

      if (payment_gateway === 'paypal') {
        homey_hourly_booking_paypal_payment($this, reservation_id, security);
      } else if (payment_gateway === 'stripe') {
        var hform = $(this).parents('.dashboard-area');
        hform.find('.homey_stripe_simple button').trigger('click');
        $('#homey_notify').html('');
      }
      return;
    });

    var homey_instance_booking_paypal_payment = function (
      $this,
      check_in,
      check_out,
      guests,
      extra_options,
      listing_id,
      renter_message,
      security
    ) {
      //alert(extra_options); return;
      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_instance_booking_paypal_payment',
          check_in: check_in,
          check_out: check_out,
          guests: guests,
          extra_options: extra_options,
          listing_id: listing_id,
          renter_message: renter_message,
          security: security,
        },
        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
          $('#instance_noti').html(
            '<div class="alert alert-success alert-dismissible" role="alert">' +
              paypal_connecting +
              '</div>'
          );
        },
        success: function (data) {
          if (data.success) {
            window.location.href = data.payment_execute_url;
          } else {
            $('#instance_noti').html(
              '<div class="alert alert-danger alert-dismissible" role="alert">' +
                data.message +
                '</div>'
            );
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
      });
    };

    var homey_hourly_instance_booking_paypal_payment = function (
      $this,
      check_in,
      check_in_hour,
      check_out_hour,
      start_hour,
      end_hour,
      guests,
      extra_options,
      listing_id,
      renter_message,
      security
    ) {
      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_hourly_instance_booking_paypal_payment',
          check_in: check_in,
          check_in_hour: check_in_hour,
          check_out_hour: check_out_hour,
          start_hour: start_hour,
          end_hour: end_hour,
          guests: guests,
          extra_options: extra_options,
          listing_id: listing_id,
          renter_message: renter_message,
          security: security,
        },
        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
          $('#instance_noti').html(
            '<div class="alert alert-success alert-dismissible" role="alert">' +
              paypal_connecting +
              '</div>'
          );
        },
        success: function (data) {
          if (data.success) {
            window.location.href = data.payment_execute_url;
          } else {
            $('#instance_noti').html(
              '<div class="alert alert-danger alert-dismissible" role="alert">' +
                data.message +
                '</div>'
            );
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
      });
    };

    $('#make_instance_booking_payment').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var check_in = $('#check_in_date').val();
      //check_in = homey_convert_date(check_in);

      var check_out = $('#check_out_date').val();
      //check_out = homey_convert_date(check_out);

      var guests = $('#guests').val();
      var listing_id = $('#listing_id').val();
      var renter_message = $('#renter_message').val();
      var security = $('#checkout-security').val();

      var extra_options = [];
      var temp_opt;
      $('.homey_extra_price').each(function () {
        var extra_name = $(this).data('name');
        var extra_price = $(this).data('price');
        var extra_type = $(this).data('type');
        temp_opt = '';
        temp_opt = extra_name;
        temp_opt = temp_opt + '|' + extra_price;
        temp_opt = temp_opt + '|' + extra_type;
        extra_options.push(temp_opt);
      });

      $('#instance_noti').empty();

      var payment_gateway = $("input[name='payment_gateway']:checked").val();
      if (payment_gateway == undefined) {
        $('#instance_noti').html(
          '<div class="alert alert-danger alert-dismissible" role="alert">' +
            choose_gateway_text +
            '</div>'
        );
      }

      if (payment_gateway === 'paypal') {
        homey_instance_booking_paypal_payment(
          $this,
          check_in,
          check_out,
          guests,
          extra_options,
          listing_id,
          renter_message,
          security
        );
      } else if (payment_gateway === 'stripe') {
        var hform = $(this).parents('form');
        hform.find('.homey_stripe_simple button').trigger('click');
      }
      return;
    });

    $('#make_hourly_instance_booking_payment').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var check_in = $('#check_in_date').val();

      var check_in_hour = $('#check_in_hour').val();
      var check_out_hour = $('#check_out_hour').val();
      var start_hour = $('#start_hour').val();
      var end_hour = $('#end_hour').val();
      var guests = $('#guests').val();
      var listing_id = $('#listing_id').val();
      var renter_message = $('#renter_message').val();
      var security = $('#checkout-security').val();

      var extra_options = [];
      var temp_opt;
      $('.homey_extra_price').each(function () {
        var extra_name = $(this).data('name');
        var extra_price = $(this).data('price');
        var extra_type = $(this).data('type');
        temp_opt = '';
        temp_opt = extra_name;
        temp_opt = temp_opt + '|' + extra_price;
        temp_opt = temp_opt + '|' + extra_type;
        extra_options.push(temp_opt);
      });

      $('#instance_noti').empty();

      var payment_gateway = $("input[name='payment_gateway']:checked").val();
      if (payment_gateway == undefined) {
        $('#instance_noti').html(
          '<div class="alert alert-danger alert-dismissible" role="alert">' +
            choose_gateway_text +
            '</div>'
        );
      }

      if (payment_gateway === 'paypal') {
        homey_hourly_instance_booking_paypal_payment(
          $this,
          check_in,
          check_in_hour,
          check_out_hour,
          start_hour,
          end_hour,
          guests,
          extra_options,
          listing_id,
          renter_message,
          security
        );
      } else if (payment_gateway === 'stripe') {
        var hform = $(this).parents('form');
        hform.find('.homey_stripe_simple button').trigger('click');
      }
      return;
    });

    $('button.homey-booking-step-1').on('click', function (e) {
      e.preventDefault();
      var $this = $(this);

      var first_name = $('#first-name').val();
      var last_name = $('#last-name').val();
      var phone = $('#phone').val();
      var renter_message = $('#renter_message').val();

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_instance_step_1',
          first_name: first_name,
          last_name: last_name,
          phone: phone,
        },
        beforeSend: function () {
          $this.children('i').remove();
          $('.homey-booking-block-body-1 .continue-block-button p.error').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (data) {
          if (data.success) {
            $('.homey-booking-block-title-2').removeClass('inactive mb-0');
            $('.homey-booking-block-body-2').slideDown('slow');

            $('.homey-booking-block-title-1').addClass('mb-0');
            $('.homey-booking-block-body-1').slideUp('slow');
            $(
              '.homey-booking-block-title-1 .text-success, .homey-booking-block-title-1 .edit-booking-form'
            ).removeClass('hidden');
            $(
              '.homey-booking-block-title-1 .text-success, .homey-booking-block-title-1 .edit-booking-form'
            ).show();
            $('#guest_message').val(renter_message);
          } else {
            $('.homey-booking-block-body-1 .continue-block-button').prepend(
              '<p class="error text-danger"><i class="fa fa-close"></i> ' + data.message + '</p>'
            );
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          $this.children('i').removeClass(process_loader_spinner);
        },
      });
    });

    $('button.homey-booking-step-2').on('click', function (e) {
      e.preventDefault();

      var agreement = $("input[name='agreement']:checked").val();

      $('.homey-booking-block-body-2 .continue-block-button p.error').remove();

      if (agreement != undefined) {
        $('.homey-booking-block-title-3').removeClass('inactive mb-0');
        $('.homey-booking-block-body-3').slideDown('slow');

        $('.homey-booking-block-title-2').addClass('mb-0');
        $('.homey-booking-block-body-2').slideUp('slow');
        $(
          '.homey-booking-block-title-2 .text-success, .homey-booking-block-title-2 .edit-booking-form'
        ).removeClass('hidden');
        $(
          '.homey-booking-block-title-2 .text-success, .homey-booking-block-title-2 .edit-booking-form'
        ).show();
      } else {
        $('.homey-booking-block-body-2 .continue-block-button').prepend(
          '<p class="error text-danger"><i class="fa fa-close"></i> ' + agree_term_text + '</p>'
        );
      }
    });

    $('.homey-booking-block-title-1 .edit-booking-form').on('click', function (e) {
      e.preventDefault();

      $('.homey-booking-block-title-2, .homey-booking-block-title-3').addClass('mb-0');
      $('.homey-booking-block-body-2, .homey-booking-block-body-3').slideUp('slow');

      $('.homey-booking-block-title-1').removeClass('mb-0');
      $('.homey-booking-block-body-1').slideDown('slow');
    });

    $('.homey-booking-block-title-2 .edit-booking-form').on('click', function (e) {
      e.preventDefault();

      $('.homey-booking-block-title-1, .homey-booking-block-title-3').addClass('mb-0');
      $('.homey-booking-block-body-1, .homey-booking-block-body-3').slideUp('slow');

      $('.homey-booking-block-title-2').removeClass('mb-0');
      $('.homey-booking-block-body-2').slideDown('slow');
    });

    /*--------------------------------------------------------------------------
     *  Contact listing host
     * -------------------------------------------------------------------------*/
    $('.contact_listing_host').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var $host_contact_wrap = $this.parents('.host-contact-wrap');
      var $form = $this.parents('form');
      var $messages = $host_contact_wrap.find('.homey_contact_messages');

      $.ajax({
        url: ajaxurl,
        data: $form.serialize(),
        method: $form.attr('method'),
        dataType: 'JSON',

        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (response) {
          if (response.success) {
            $messages.empty().append(response.msg);
            $form.find('input').val('');
            $form.find('textarea').val('');
          } else {
            $messages.empty().append(response.msg);
            $this.children('i').removeClass(process_loader_spinner);
          }
          if (homey_reCaptcha == 1) {
            homeyReCaptchaReset();
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          $this.children('i').removeClass(process_loader_spinner);
          $this.children('i').addClass(success_icon);
        },
      });
    });

    /*--------------------------------------------------------------------------
     *   Contact host form on host detail page
     * -------------------------------------------------------------------------*/
    $('#host_detail_contact').on('click', function (e) {
      e.preventDefault();
      var current_element = $(this);
      var $this = $(this);
      var $form = $this.parents('form');

      $.ajax({
        type: 'post',
        url: ajaxurl,
        data: $form.serialize(),
        method: $form.attr('method'),
        dataType: 'JSON',

        beforeSend: function () {
          current_element.children('i').remove();
          current_element.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (res) {
          current_element.children('i').removeClass(process_loader_spinner);
          if (res.success) {
            $('#form_messages').empty().append(res.msg);
            current_element.children('i').addClass(success_icon);
          } else {
            $('#form_messages').empty().append(res.msg);
          }
          if (homey_reCaptcha == 1) {
            homeyReCaptchaReset();
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
      });
    });

    /*--------------------------------------------------------------------------
     *   Print Property
     * -------------------------------------------------------------------------*/
    if ($('#homey-print').length > 0) {
      $('#homey-print').on('click', function (e) {
        e.preventDefault();
        var listingID, printWindow;

        listingID = $(this).attr('data-listing-id');

        printWindow = window.open('', 'Print Me', 'width=850 ,height=842');
        $.ajax({
          type: 'POST',
          url: ajaxurl,
          data: {
            action: 'homey_create_print',
            listing_id: listingID,
          },
          success: function (data) {
            printWindow.document.write(data);
            printWindow.document.close();
            printWindow.print();
            printWindow.focus();
          },
          error: function (xhr, status, error) {
            var err = eval('(' + xhr.responseText + ')');
            console.log(err.Message);
          },
        });
      });
    }

    /* ------------------------------------------------------------------------ */
    /*  Homey login and regsiter
         /* ------------------------------------------------------------------------ */
    $('.homey_login_button').on('click', function (e) {
      e.preventDefault();
      var current = $(this);
      homey_login(current);
    });

    $('.homey-register-button').on('click', function (e) {
      e.preventDefault();
      var current = $(this);
      homey_register(current);
    });

    var homey_login = function (current) {
      var $form = current.parents('form');
      var $messages = $('.homey_login_messages');

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: $form.serialize(),
        beforeSend: function () {
          $messages.empty().append('<p class="success text-success"> ' + login_sending + '</p>');
        },
        success: function (response) {
          if (response.success) {
            $messages
              .empty()
              .append(
                '<p class="success text-success"><i class="fa fa-check"></i> ' +
                  response.msg +
                  '</p>'
              );

            var wp_http_referer_value = jQuery(document)
              .find("input[name='_wp_http_referer']")
              .val();

            if (wp_http_referer_value != '/' && login_redirect_type == 'same_page') {
              if (wp_http_referer_value.indexOf('?') !== -1) {
                window.location.href = wp_http_referer_value + '&n=' + new Date().getTime();
              } else {
                window.location.href = wp_http_referer_value + '?n=' + new Date().getTime();
              }
              return false;
            }

            if (login_redirect_type == 'same_page') {
              window.location.reload(true);
              return false;
            } else {
              if (wp_http_referer_value.indexOf('?') !== -1) {
                window.location.href = login_redirect + '&n=' + new Date().getTime();
              } else {
                window.location.href = login_redirect + '?n=' + new Date().getTime();
              }
              return false;
            }
          } else {
            $messages
              .empty()
              .append(
                '<p class="error text-danger"><i class="fa fa-close"></i> ' + response.msg + '</p>'
              );
          }

          if (homey_reCaptcha == 1) {
            homeyReCaptchaReset();
          }
        },
        error: function (xhr, status, error) {
          $messages
            .empty()
            .append(
              '<p class="error text-danger"><i class="fa fa-close"></i>' +
                HOMEY_ajax_vars.homey_login_register_msg_text +
                '</p>'
            );
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
      });
    }; // end homey_login

    var homey_register = function (currnt) {
      var $form = currnt.parents('form');
      var $messages = $('.homey_register_messages');

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: $form.serialize(),
        beforeSend: function () {
          $messages.empty().append('<p class="success text-success"> ' + login_sending + '</p>');
        },
        success: function (response) {
          if (response.success) {
            $messages
              .empty()
              .append(
                '<p class="success text-success"><i class="fa fa-check"></i> ' +
                  response.msg +
                  '</p>'
              );
            $('.homey_login_messages')
              .empty()
              .append(
                '<p class="success text-success"><i class="fa fa-check"></i> ' +
                  response.msg +
                  '</p>'
              );
            $('#modal-register').modal('hide');
            $('#modal-login').modal('show');
          } else {
            $messages
              .empty()
              .append(
                '<p class="error text-danger"><i class="fa fa-close"></i> ' + response.msg + '</p>'
              );
          }
          if (homey_reCaptcha == 1) {
            homeyReCaptchaReset();
          }
          if (homey_reCaptcha == 1) {
            homeyReCaptchaReset();
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
      });
    };

    /* ------------------------------------------------------------------------ */
    /*  Reset Password
         /* ------------------------------------------------------------------------ */
    $('#homey_forgetpass').on('click', function (e) {
      e.preventDefault();
      var user_login = $('#user_login_forgot').val(),
        security = $('#homey_resetpassword_security').val();

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_reset_password',
          user_login: user_login,
          security: security,
        },
        beforeSend: function () {
          $('#homey_msg_reset')
            .empty()
            .append('<p class="success text-success"> ' + login_sending + '</p>');
        },
        success: function (response) {
          if (response.success) {
            $('#homey_msg_reset')
              .empty()
              .append(
                '<p class="success text-success"><i class="fa fa-check"></i> ' +
                  response.msg +
                  '</p>'
              );
          } else {
            $('#homey_msg_reset')
              .empty()
              .append(
                '<p class="error text-danger"><i class="fa fa-close"></i> ' + response.msg + '</p>'
              );
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
      });
    });

    if ($('#homey_reset_password').length > 0) {
      $('#homey_reset_password').on('click', function (e) {
        e.preventDefault();

        var $this = $(this);
        var rg_login = $('input[name="rp_login"]').val();
        var rp_key = $('input[name="rp_key"]').val();
        var new_pass = $('input[name="new_password"]').val();
        var security = $('input[name="homey_resetpassword_security"]').val();

        $.ajax({
          type: 'post',
          url: ajaxurl,
          dataType: 'json',
          data: {
            action: 'homey_reset_password_2',
            rq_login: rg_login,
            password: new_pass,
            rp_key: rp_key,
            security: security,
          },
          beforeSend: function () {
            $this.children('i').remove();
            $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
          },
          success: function (data) {
            if (data.success) {
              $('#password_reset_msgs, .homey_login_messages')
                .empty()
                .append(
                  '<p class="success text-success"><i class="fa fa-check"></i> ' + data.msg + '</p>'
                );
              $('#new_password').val('');
              $('#modal-login').modal('show');
            } else {
              $('#password_reset_msgs')
                .empty()
                .append(
                  '<p class="error text-danger"><i class="fa fa-close"></i> ' + data.msg + '</p>'
                );
            }
          },
          error: function (errorThrown) {},
          complete: function () {
            $this.children('i').removeClass(process_loader_spinner);
          },
        });
      });
    }

    /*--------------------------------------------------------------------------
     *   Facebook login
     * -------------------------------------------------------------------------*/
    $('.homey-facebook-login').on('click', function () {
      var current = $(this);
      homey_login_via_facebook(current);
    });

    var homey_login_via_facebook = function (current) {
      var $form = current.parents('form');
      var $messages = $('.homey_login_messages');

      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'homey_facebook_login_oauth',
        },
        beforeSend: function () {
          $messages.empty().append('<p class="success text-success"> ' + login_sending + '</p>');
        },
        success: function (data) {
          window.location.href = data;
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
      });
    };

    /*--------------------------------------------------------------------------
     *  Social Logins
     * -------------------------------------------------------------------------*/
    $('.homey-yahoo-login').on('click', function () {
      var current = $(this);
      homey_login_via_yahoo(current);
    });

    var homey_login_via_yahoo = function (current) {
      var $form = current.parents('form');
      var $messages = $('.homey_login_messages');

      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'homey_yahoo_login',
        },
        beforeSend: function () {
          $messages.empty().append('<p class="success text-success"> ' + login_sending + '</p>');
        },
        success: function (data) {
          window.location.href = data;
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
      });
    };

    $('.homey-google-login').on('click', function () {
      var current = $(this);
      homey_login_via_google(current);
    });

    var homey_login_via_google = function (current) {
      var $form = current.parents('form');
      var $messages = $('.homey_login_messages');

      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'homey_google_login_oauth',
        },
        beforeSend: function () {
          $messages.empty().append('<p class="success text-success"> ' + login_sending + '</p>');
        },
        success: function (data) {
          window.location.href = data;
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
      });
    };

    /*
     * Property Message Notifications
     * -----------------------------*/
    var homey_message_notifications = function () {
      $.ajax({
        url: ajaxurl,
        data: {
          action: 'homey_chcek_messages_notifications',
        },
        method: 'POST',
        dataType: 'JSON',

        beforeSend: function () {
          // code here...
        },
        success: function (response) {
          if (response.success) {
            if (response.notification) {
              $('.user-alert').show();
              $('.msg-alert').show();
            } else {
              $('.user-alert').hide();
              $('.msg-alert').hide();
            }
          }
        },
      });
    };

    /*
     * Property Booking Notifications
     * -----------------------------*/
    var homey_booking_notification = function () {
      $.ajax({
        url: ajaxurl,
        data: {
          action: 'homey_booking_notification',
        },
        method: 'POST',
        dataType: 'JSON',

        beforeSend: function () {
          // code here...
        },
        success: function (response) {
          if (response.success) {
            if (response.notification != 0) {
              $('.new-booking-alert').show();
            } else {
              $('.new-booking-alert').hide();
            }
          }
        },
      });
    };

    $(document).ready(function () {
      homey_message_notifications();
      if (parseInt(userID, 10) != 0) {
        setInterval(function () {
          homey_message_notifications();
        }, 60000);
      }

      homey_booking_notification();
      if (parseInt(userID, 10) != 0) {
        setInterval(function () {
          homey_booking_notification();
        }, 60000);
      }
    });

    $('.btn_extra_expense').on('click', function (e) {
      e.preventDefault();
      var reservation_id = $('#resrv_id').val();
    });

    /* ------------------------------------------------------------------------ */
    /* WooCommerce Pay
        /* ------------------------------------------------------------------------ */
    $('.homey-woocommerce-featured-pay').on('click', function (e) {
      e.preventDefault();

      let listID = $(this).data('listid');
      let is_featured = $(this).data('featured');

      homey_processing_modal(processing_text);

      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'homey_featured_woo_pay',
          listing_id: listID,
          is_featured: is_featured,
        },
        success: function (data) {
          if (data.success != false) {
            var urlWithGetVars =
              HOMEY_ajax_vars.woo_checkout_url +
              '?listing_id=' +
              listing_id +
              '&is_featured=' +
              is_featured;
            window.location.href = urlWithGetVars;
          } else {
            $('#homey_modal').modal('hide');
          }
        },
        error: function (errorThrown) {},
      }); // $.ajax
    });

    /* ------------------------------------------------------------------------ */
    /* WooCommerce Reservation Pay
        /* ------------------------------------------------------------------------ */
    $('.homey-woo-reservation-pay').on('click', function (e) {
      e.preventDefault();

      let reservation_id = $(this).data('reservation_id');

      homey_processing_modal(processing_text);

      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'homey_reservation_woo_pay',
          reservation_id: reservation_id,
        },
        success: function (data) {
          if (data.success != false) {
            var urlWithGetVars =
              HOMEY_ajax_vars.woo_checkout_url + '?reservation_id=' + reservation_id;
            window.location.href = urlWithGetVars;
          } else {
            $('#homey_modal').modal('hide');
          }
        },
        error: function (errorThrown) {},
      }); // $.ajax
    });

    $('#make_woocommerce_instant_booking_payment').on('click', function (e) {
      e.preventDefault();

      homey_processing_modal(processing_text);

      var $this = $(this);
      var check_in = $('#check_in_date').val();

      var check_out = $('#check_out_date').val();

      var guests = $('#guests').val();
      var listing_id = $('#listing_id').val();
      var renter_message = $('#renter_message').val();
      var security = $('#checkout-security').val();

      var extra_options = [];
      var temp_opt;
      $('.homey_extra_price').each(function () {
        var extra_name = $(this).data('name');
        var extra_price = $(this).data('price');
        var extra_type = $(this).data('type');
        temp_opt = '';
        temp_opt = extra_name;
        temp_opt = temp_opt + '|' + extra_price;
        temp_opt = temp_opt + '|' + extra_type;
        extra_options.push(temp_opt);
      });

      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'homey_instant_reservation_woo_pay',
          check_in: check_in,
          check_out: check_out,
          guests: guests,
          extra_options: extra_options,
          listing_id: listing_id,
          renter_message: renter_message,
        },
        success: function (data) {
          if (data.success != false) {
            var urlWithGetVars =
              HOMEY_ajax_vars.woo_checkout_url +
              '?check_in=' +
              check_in +
              '&check_out=' +
              check_out +
              '&guests=' +
              guests +
              '&extra_options=' +
              extra_options +
              '&listing_id=' +
              listing_id +
              '&renter_message=' +
              renter_message;
            window.location.href = urlWithGetVars;
          } else {
            $('#homey_modal').modal('hide');
          }
        },
        error: function (errorThrown) {},
      }); // $.ajax
    });

    $('#make_hourly_woocommerce_instant_booking_payment').on('click', function (e) {
      e.preventDefault();

      homey_processing_modal(processing_text);

      var $this = $(this);
      var check_in = $('#check_in_date').val();

      var check_in_hour = $('#check_in_hour').val();
      var check_out_hour = $('#check_out_hour').val();
      var start_hour = $('#start_hour').val();
      var end_hour = $('#end_hour').val();
      var guests = $('#guests').val();
      var listing_id = $('#listing_id').val();
      var renter_message = $('#renter_message').val();
      var security = $('#checkout-security').val();

      var extra_options = [];
      var temp_opt;
      $('.homey_extra_price').each(function () {
        var extra_name = $(this).data('name');
        var extra_price = $(this).data('price');
        var extra_type = $(this).data('type');
        temp_opt = '';
        temp_opt = extra_name;
        temp_opt = temp_opt + '|' + extra_price;
        temp_opt = temp_opt + '|' + extra_type;
        extra_options.push(temp_opt);
      });

      $('#instance_noti').empty();

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_instant_hourly_reservation_woo_pay',
          check_in: check_in,
          check_in_hour: check_in_hour,
          check_out_hour: check_out_hour,
          start_hour: start_hour,
          end_hour: end_hour,
          guests: guests,
          extra_options: extra_options,
          listing_id: listing_id,
          renter_message: renter_message,
          security: security,
        },

        success: function (data) {
          if (data.success != false) {
            var urlWithGetVars =
              HOMEY_ajax_vars.woo_checkout_url +
              '?start_hour=' +
              start_hour +
              '&end_hour=' +
              end_hour +
              '&check_in=' +
              check_in +
              '&check_in_hour=' +
              check_in_hour +
              '&check_out_hour=' +
              check_out_hour +
              '&guests=' +
              guests +
              '&extra_options=' +
              extra_options +
              '&listing_id=' +
              listing_id +
              '&renter_message=' +
              renter_message;
            window.location.href = urlWithGetVars;
          } else {
            $('#homey_modal').modal('hide');
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
      });
    });

    if ($('#has_social_account').data('hasSocialAccount') > 0) {
      $('#modal-login').modal('show');
      $('.homey_login_messages')
        .empty()
        .append(
          '<p class="error text-danger"><i class="fa fa-close"></i>You are already registered, please login here.</p>'
        );
    }
  } // typeof HOMEY_ajax_vars

  function save_booking_details(arrive, depart, guest, guest_message) {
    var currentRefererUrl = $("input[name='_wp_http_referer']").val();
    if (typeof currentRefererUrl != 'undefined') {
      var newRefereUrl =
        currentRefererUrl.split('?')[0] +
        '?arrive=' +
        arrive +
        '&depart=' +
        depart +
        '&guest=' +
        guest +
        '&guest_message=' +
        guest_message;
      $("input[name='_wp_http_referer']").val(newRefereUrl);
    }
  }

  function save_hourl_booking_details(arrive, start, end, guest, guest_message) {
    var currentRefererUrl = $("input[name='_wp_http_referer']").val();
    if (typeof currentRefererUrl != 'undefined') {
      var newRefereUrl =
        currentRefererUrl.split('?')[0] +
        '?arrive=' +
        arrive +
        '&start=' +
        start +
        '&end=' +
        end +
        '&guest=' +
        guest +
        '&guest_message=' +
        guest_message;
      $("input[name='_wp_http_referer']").val(newRefereUrl);
    }
  }

  // to complete auto fill the price zahid.k
  if (jQuery('body').hasClass('single-listing')) {
    var extra_options = [];
    var temp_opt;

    jQuery('.homey_extra_price input').each(function () {
      if (jQuery(this).is(':checked')) {
        var extra_name = jQuery(this).data('name');
        var extra_price = jQuery(this).data('price');
        var extra_type = jQuery(this).data('type');
        temp_opt = '';
        temp_opt = extra_name;
        temp_opt = temp_opt + '|' + extra_price;
        temp_opt = temp_opt + '|' + extra_type;
        extra_options.push(temp_opt);
      }
    });

    var check_in_date = jQuery('input[name="arrive"]').val();
    check_in_date = homey_convert_date(check_in_date);

    var start_hour = jQuery('select[name="start_hour"]').val();
    var end_hour = jQuery('select[name="end_hour"]').val();

    var guests = jQuery('input[name="guests"]').val();
    var listing_id = jQuery('#listing_id').val();
    var security = jQuery('#reservation-security').val();

    if (typeof start_hour != 'undefined' && typeof end_hour != 'undefined') {
      homey_calculate_hourly_booking_cost(
        check_in_date,
        start_hour,
        end_hour,
        guests,
        listing_id,
        security,
        extra_options
      );
    }
  }

  jQuery('#place_order').click(function () {
    jQuery('html, body').animate(
      {
        scrollTop: jQuery('.woocommerce-info').offset().top,
      },
      'slow'
    );
  });
  // to complete auto fill the price zahid.k
}); // end document ready
