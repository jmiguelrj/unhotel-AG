jQuery(document).ready(function ($) {
  'use strict';

  if (typeof Homey_Listing !== 'undefined') {
    var dtGlobals = {}; // Global storage
    dtGlobals.isMobile =
      /(Android|BlackBerry|iPhone|iPad|Palm|Symbian|Opera Mini|IEMobile|webOS)/.test(
        navigator.userAgent
      );
    dtGlobals.isAndroid = /(Android)/.test(navigator.userAgent);
    dtGlobals.isiOS = /(iPhone|iPod|iPad)/.test(navigator.userAgent);
    dtGlobals.isiPhone = /(iPhone|iPod)/.test(navigator.userAgent);
    dtGlobals.isiPad = /(iPad|iPod)/.test(navigator.userAgent);

    var ajaxurl = Homey_Listing.ajaxURL;

    var homey_booking_type = Homey_Listing.homey_booking_type;
    var are_you_sure_text = Homey_Listing.are_you_sure_text;
    var delete_btn_text = Homey_Listing.delete_btn_text;
    var cancel_btn_text = Homey_Listing.cancel_btn_text;
    var confirm_btn_text = Homey_Listing.confirm_btn_text;
    var edit_tab = Homey_Listing.edit_tab;
    var process_loader_refresh = Homey_Listing.process_loader_refresh;
    var process_loader_spinner = Homey_Listing.process_loader_spinner;
    var process_loader_circle = Homey_Listing.process_loader_circle;
    var process_loader_cog = Homey_Listing.process_loader_cog;
    var btn_save = Homey_Listing.btn_save;
    var success_icon = Homey_Listing.success_icon;
    var verify_nonce = Homey_Listing.verify_nonce;
    var verify_file_type = Homey_Listing.verify_file_type;
    var add_listing_msg = Homey_Listing.add_listing_msg;
    var processing_text = Homey_Listing.processing_text;
    var acc_bedroom_name = Homey_Listing.acc_bedroom_name;
    var acc_bedroom_name_plac = Homey_Listing.acc_bedroom_name_plac;
    var acc_guests = Homey_Listing.acc_guests;
    var acc_guests_plac = Homey_Listing.acc_guests_plac;
    var acc_no_of_beds = Homey_Listing.acc_no_of_beds;
    var acc_no_of_beds_plac = Homey_Listing.acc_no_of_beds_plac;
    var acc_bedroom_type = Homey_Listing.acc_bedroom_type;
    var acc_bedroom_type_plac = Homey_Listing.acc_bedroom_type_plac;
    var acc_btn_remove_room = Homey_Listing.acc_btn_remove_room;

    var service_name = Homey_Listing.service_name;
    var service_name_plac = Homey_Listing.service_name_plac;
    var service_price = Homey_Listing.service_price;
    var service_price_plac = Homey_Listing.service_price_plac;
    var service_des = Homey_Listing.service_des;
    var service_des_plac = Homey_Listing.service_des_plac;
    var btn_remove_service = Homey_Listing.btn_remove_service;
    var pricing_link = Homey_Listing.pricing_link;
    var calendar_link = Homey_Listing.calendar_link;
    var geo_coding_msg = Homey_Listing.geo_coding;
    var avail_label = Homey_Listing.avail_label;
    var unavail_label = Homey_Listing.unavail_label;
    var geo_country_limit = Homey_Listing.geo_country_limit;
    var geocomplete_country = Homey_Listing.geocomplete_country;
    var homey_is_rtl = Homey_Listing.homey_is_rtl;

    var booked_hours_array = Homey_Listing.booked_hours_array;
    var pending_hours_array = Homey_Listing.pending_hours_array;
    var booking_start_hour = Homey_Listing.booking_start_hour;
    var booking_end_hour = Homey_Listing.booking_end_hour;

    if (booked_hours_array !== '' && booked_hours_array.length !== 0) {
      booked_hours_array = JSON.parse(booked_hours_array);
    }

    if (pending_hours_array !== '' && pending_hours_array.length !== 0) {
      pending_hours_array = JSON.parse(pending_hours_array);
    }

    setInterval(function () {
      $('select').each(function (e) {
        if ($(this).hasClass('error')) {
          $("label[for='" + $(this).attr('id') + "']").addClass('select_input_error');
        } else {
          $("label[for='" + $(this).attr('id') + "']").removeClass('select_input_error');
        }
      });
    }, 500);

    /*$(document).ready(function() {
          $(window).keydown(function(event){
            if(event.keyCode == 13) {
              event.preventDefault();
              return false;
            }
          });
        });*/

    /* ------------------------------------------------------------------------ */
    /*  Listing mode
        /* ------------------------------------------------------------------------ */
    function insertListingParam(key, value) {
      key = encodeURI(key);
      value = encodeURI(value);

      // get querystring , remove (?) and covernt into array
      var qrp = document.location.search.substr(1).split('&');

      // get qrp array length
      var i = qrp.length;
      var j;
      while (i--) {
        //covert query strings into array for check key and value
        j = qrp[i].split('=');

        // if find key and value then join
        if (j[0] == key) {
          j[1] = value;
          qrp[i] = j.join('=');
          break;
        }
      }

      if (i < 0) {
        qrp[qrp.length] = [key, value].join('=');
      }
      // reload the page
      document.location.search = qrp.join('&');
    }

    $('#homey_listing_mode').on('change', function () {
      var key = 'mode';
      var value = $(this).val();
      insertListingParam(key, value);
    });

    /* ------------------------------------------------------------------------ */
    /*  parseInt Radix 10
        /* ------------------------------------------------------------------------ */
    function parseInt10(val) {
      return parseInt(val, 10);
    }

    $('#form_tabs li').on('click', function () {
      $('#media-tab').addClass('tab-pane');
      if ($(this).hasClass('smb-media')) {
        $('#media-tab').css('visibility', 'visible');
        $('#media-tab').css('height', 'auto');
        $('#media-tab').css('position', 'unset');
        $('#media-tab').css('display', 'block');
      } else {
        $('#media-tab').css('visibility', 'hidden');
        $('#media-tab').css('height', '0');
        $('#media-tab').css('position', 'absolute');
        $('#media-tab').css('display', 'none');
      }
    });

    /* ------------------------------------------------------------------------ */
    /* Per Hour availability calendar
        /* ------------------------------------------------------------------------ */
    function homey_hourly_availability_calendar_dash() {
      var today = new Date();
      var listing_booked_dates = [];
      var listing_pending_dates = [];

      for (var key in booked_hours_array) {
        if (booked_hours_array.hasOwnProperty(key) && key !== '') {
          var temp_book = [];
          (temp_book['title'] = Homey_Listing.hc_reserved_label),
            (temp_book['start'] = moment.unix(key).utc().format()),
            (temp_book['end'] = moment.unix(booked_hours_array[key]).utc().format()),
            (temp_book['editable'] = false);
          temp_book['color'] = '#fdd2d2';
          temp_book['textColor'] = '#444444';
          listing_booked_dates.push(temp_book);
        }
      }

      for (var key_pending in pending_hours_array) {
        if (pending_hours_array.hasOwnProperty(key_pending) && key_pending !== '') {
          var temp_pending = [];
          (temp_pending['title'] = Homey_Listing.hc_pending_label),
            (temp_pending['start'] = moment.unix(key_pending).utc().format()),
            (temp_pending['end'] = moment.unix(pending_hours_array[key_pending]).utc().format()),
            (temp_pending['editable'] = false);
          temp_pending['color'] = '#ffeedb';
          temp_pending['textColor'] = '#333333';
          listing_pending_dates.push(temp_pending);
        }
      }

      var hours_slot = $.merge(listing_booked_dates, listing_pending_dates);
      var calendarEl = document.getElementById('homey_hourly_calendar_edit_listing');

      var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: Homey_Listing.homey_current_lang,
        timeZone: Homey_Listing.homey_timezone,
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
        allDayText: Homey_Listing.hc_hours_label,
        weekNumbers: false,
        weekNumbersWithinDays: true,
        weekNumberCalculation: 'ISO',
        editable: false,
        eventLimit: true,
        unselectAuto: false,
        isRTL: homey_is_rtl,
        buttonText: {
          today: Homey_Listing.hc_today_label,
        },
      });

      calendar.render();
    }

    if (homey_booking_type == 'per_hour') {
      if (document.getElementById('homey_hourly_calendar_edit_listing')) {
        homey_hourly_availability_calendar_dash();

        $('ul#form_tabs li').on('click', function (e) {
          e.preventDefault();

          if ($(this).hasClass('calendar-js')) {
            $('#calendar-tab').css('display', 'block');
            $('#calendar-tab').css('visibility', 'visible');
          } else {
            $('#calendar-tab').css('display', 'none');
            $('#calendar-tab').css('visibility', 'hidden');
          }
        });
      }
    }

    /* ------------------------------------------------------------------------ */
    /*  Custom Period Prices
        /* ------------------------------------------------------------------------ */
    $('#cus_night_price').on('keyup', function () {
      $('#cus_night_price').removeClass('error');
    });
    $('#cus_btn_save').on('click', function (e) {
      e.preventDefault();
      var $this = $(this);
      var cus_start_date = $('#cus_start_date').val();
      var cus_end_date = $('#cus_end_date').val();
      var cus_night_price = $('#cus_night_price').val();
      var cus_additional_guest_price = $('#cus_additional_guest_price').val();
      var cus_weekend_price = $('#cus_weekend_price').val();
      var cus_night_minimum = $('#cus_night_minimum').val();
      var cus_period_name = $('#cus_period_name').val();
      var listing_id = $('#listing_id_for_custom').val();
      if ($.trim(cus_night_price) != '') {
        $.ajax({
          type: 'post',
          url: ajaxurl,
          dataType: 'json',
          data: {
            action: 'homey_add_custom_period',
            start_date: cus_start_date,
            end_date: cus_end_date,
            night_price: cus_night_price,
            additional_guest_price: cus_additional_guest_price,
            weekend_price: cus_weekend_price,
            night_minimum: cus_night_minimum,
            period_name: cus_period_name,
            listing_id: listing_id,
          },
          beforeSend: function () {
            $this.children('i').remove();
            $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
          },
          success: function (data) {
            //alert(data.success); return false;
            if (data.success) {
              window.location.href = pricing_link;
            } else {
              alert(data.message);
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
      } else {
        $('#cus_night_price').focus();
        $('#cus_night_price').addClass('error');
      }
    });

    /* ------------------------------------------------------------------------ */
    /*  Delete Custom Period Prices
        /* ------------------------------------------------------------------------ */
    $('.homey_delete_period').on('click', function (e) {
      e.preventDefault();
      var $this = $(this);
      var startdate = $this.data('startdate');
      var enddate = $this.data('enddate');
      var listing_id = $this.data('listingid');

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_delete_custom_period',
          start_date: startdate,
          end_date: enddate,
          listing_id: listing_id,
        },
        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (data) {
          if (data.success) {
            $this.parents('tr').remove();
          } else {
            alert(data.message);
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

    var homey_validation = function (field_required) {
      if (field_required != 0) {
        return true;
      }
      return false;
    };

    $('ul#form_tabs li').on('click', function (e) {
      e.preventDefault();
      var current_tab = $(this).data('tab');
      $('#current_tab').val(current_tab);
    });

    /* ------------------------------------------------------------------------ */
    /*  START CREATE LISTING FORM STEPS AND VALIDATION
        /* ------------------------------------------------------------------------ */
    $('[data-hide]').on('click', function () {
      $(this)
        .closest('.' + $(this).attr('data-hide'))
        .hide();
    });

    var current = 1;

    var form = $('#submit_listing_form');
    var formStep = $('.form-step');
    var formStepGal = $('.form-step-gal');
    var btnnext = $('.btn-step-next');
    var btnback = $('.btn-step-back');
    var btnsubmitBlock = $('.btn-step-submit');
    var btnsubmit = btnsubmitBlock.find("button[type='submit']");
    var total_steps = $('#total-steps');
    var steps_counter = $('#step-counter');
    var nav_item = $('.steps-breadcrumb li');

    var errorBlock = $('.validate-errors');
    var errorBlockGal = $('.validate-errors-gal');
    var galThumbs = $('.listing-thumb');

    total_steps.html(formStep.length);
    steps_counter.html(current);

    // Init buttons and UI
    formStep.not(':eq(0), .form-step-gal1').hide();
    formStep.eq(0).addClass('active');
    hideButtons(current);

    $('ul#form_tabs li, .btn-save-listing').on('click', function () {
      var currentTab = $('#form_tabs li.active').index();

      if (form.valid()) {
        errorBlock.hide();
      } else {
        $('html, body').animate(
          {
            scrollTop: 0,
          },
          'slow'
        );

        setTimeout(function () {
          $('#form_tabs li, .tab-content div').removeClass('active in');
          $('#form_tabs li a').attr('aria-expanded', 'false');
          $('#form_tabs li').eq(currentTab).addClass('active');
          $('#form_tabs li a').attr('aria-expanded', 'true');
          $('.tab-content .tab-pane').eq(currentTab).addClass('active in');
        }, 200);

        errorBlock.show();
      }
    });

    // Next button click action
    btnnext.on('click', function (e) {
      if ($('.form-step-gal1').hasClass('active')) {
        if ($('.listing-thumb').length < 1) {
          e.preventDefault();
          return false;
        }
      }

      $('html, body').animate(
        {
          scrollTop: 0,
        },
        'slow'
      );

      if (dtGlobals.isiOS) {
        listing_gallery_images();
      }

      if (current < formStep.length) {
        // Check validation
        if ($(formStepGal).is(':visible')) {
          if (!$(galThumbs).length > 0) {
            errorBlockGal.show();
            return;
          } else {
            errorBlockGal.hide();
          }
        }
        //zahid.k
        if (current > 0) {
          formStep.removeClass('active').css({ display: 'none' });
          formStep
            .eq(current - 1)
            .addClass('active')
            .css({ display: 'block' });
        }
        //zahid.k

        if (form.valid()) {
          formStep.removeClass('active').css({ display: 'none' });
          formStep
            .eq(current++)
            .addClass('active')
            .css({ display: 'block' });

          $('.form-step-gal1').css('visibility', 'visible');
          $('.form-step-gal1').css('height', 'auto');

          errorBlock.hide();
        } else {
          errorBlock.show();
        }
      }
      hideButtons(current);
      steps_counter.html(current);
    });

    // Back button click action
    btnback.on('click', function () {
      $('html, body').animate(
        {
          scrollTop: 0,
        },
        'slow'
      );
      if (current > 1) {
        current = current - 2;
        if (current < formStep.length) {
          formStep.show();
          formStep.not(':eq(' + current++ + ')').hide();
          nav_item.eq(current).removeClass('active');
        }
      }
      hideButtons(current);
      steps_counter.html(current);
    });

    // Submit button click
    btnsubmit.on('click', function (event) {
      event.preventDefault();
      // Check validation
      if ($(formStepGal).is(':visible')) {
        if (!$(galThumbs).length > 0) {
          errorBlockGal.show();
          return;
        } else {
          errorBlockGal.hide();
        }
      }
      if (form.valid()) {
        errorBlock.hide();
        btnsubmit.attr('disabled', true);
      } else {
        errorBlock.show();
        $('html, body').animate(
          {
            scrollTop: 0,
          },
          'slow'
        );
      }
    });

    if (form.length > 0) {
      form.validate({
        // initialize plugin
        ignore: ':hidden:not(.form-step.active .selectpicker)',
        errorPlacement: function (error, element) {
          return false;
        },
        rules: {
          night_price: {
            number: true,
          },
        },
        submitHandler: function (form) {
          btnsubmitBlock.attr('disabled', true);
          form.submit();
        },
      });
    }

    // Hide buttons according to the current step
    function hideButtons(current) {
      var limit = parseInt10(formStep.length);

      $('.action').hide();

      if (current < limit) btnnext.show();
      if (current > 1) btnback.show();
      if (current === limit) {
        btnnext.hide();
        btnsubmitBlock.show();
      }
    }

    $(window).load(function () {
      if (edit_tab != 'media') {
        $('.form-step-gal1, #media-tab').css('display', 'none');
      }
    });

    /* ------------------------------------------------------------------------ */
    /*  Print Invoice
        /* ------------------------------------------------------------------------ */
    if ($('#invoice-print-button').length > 0) {
      $('#invoice-print-button').on('click', function (e) {
        e.preventDefault();
        var invoiceID, printWindow;
        invoiceID = $(this).attr('data-id');

        printWindow = window.open('', 'Print Me', 'width=700 ,height=842');
        $.ajax({
          type: 'POST',
          url: ajaxurl,
          data: {
            action: 'homey_create_invoice_print',
            invoice_id: invoiceID,
          },
          success: function (data) {
            printWindow.document.write(data);
            printWindow.document.close();
            printWindow.focus();
          },
          error: function (xhr, status, error) {
            var err = eval('(' + xhr.responseText + ')');
            console.log(err.Message);
          },
        });
      });
    }

    /*--------------------------------------------------------------------------
     *  Invoice Filter
     * -------------------------------------------------------------------------*/
    $('#invoice_status, #invoice_type').on('change', function () {
      homey_invoices_filter();
    });

    $('#startDate, #endDate').on('change', function () {
      var startDate = $('#startDate').val(),
        endDate = $('#endDate').val();

      if (startDate == '' || endDate == '') {
        return;
      }
      homey_invoices_filter();
    });

    var homey_invoices_filter = function () {
      var inv_status = $('#invoice_status').val(),
        inv_type = $('#invoice_type').val(),
        startDate = $('#startDate').val(),
        endDate = $('#endDate').val();

      $.ajax({
        url: ajaxurl,
        dataType: 'json',
        type: 'POST',
        data: {
          action: 'homey_invoices_ajax_search',
          invoice_status: inv_status,
          invoice_type: inv_type,
          startDate: startDate,
          endDate: endDate,
        },
        beforeSend: function () {
          $('#homey-map-loading').show();
          $('.pagination').hide();
          $('#invoices_content')
            .empty()
            .append(
              '' +
                '<div id="homey-map-loading">' +
                '<div class="mapPlaceholder">' +
                '<div class="loader-ripple spinner">' +
                '<div class="bounce1"></div>' +
                '<div class="bounce2"></div>' +
                '<div class="bounce3"></div>' +
                '</div>' +
                '</div>' +
                '</div>'
            );
        },
        success: function (res) {
          if (res.success) {
            $('#invoices_content').empty().append(res.result);
            $('#invoices_total_price').empty().append(res.total_price);
            $('.pagination').hide();
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
      });
    };

    /* ------------------------------------------------------------------------ */
    /*  Print Reservation
        /* ------------------------------------------------------------------------ */
    if ($('#printReservation').length > 0) {
      $('#printReservation').on('click', function (e) {
        e.preventDefault();
        var reservationID, printWindow;
        reservationID = $(this).attr('data-resvID');

        printWindow = window.open('', 'Print Me', 'width=700 ,height=842');
        $.ajax({
          type: 'POST',
          url: ajaxurl,
          data: {
            action: 'homey_create_reservation_print',
            reservation_id: reservationID,
          },
          success: function (data) {
            printWindow.document.write(data);
            printWindow.document.close();
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
    /*  START LISTING VIEW
        /* ------------------------------------------------------------------------ */
    var get_title = $('#listing_title');
    var view_title = $('#property-title-fill');
    var selected = null;

    function keyup_fill(ele, ele_place) {
      $(ele).on('keyup', function (event) {
        if ($(ele).attr('name') === 'night_price') {
          if (!$.isNumeric($(ele).val())) {
            return;
          }
        }

        if (
          $(ele).attr('name') === 'listing_bedrooms' ||
          $(ele).attr('name') === 'guests' ||
          $(ele).attr('name') === 'baths'
        ) {
          if (!$.isNumeric($(ele).val())) {
            return;
          }
        }

        var newText = event.target.value;
        $(ele_place).html(newText);
      });
    }

    keyup_fill('#listing_title', '#title-place');
    keyup_fill('#listing_address', '#address-place');
    keyup_fill('#day_date_price', '#price-place');
    keyup_fill('#night_price', '#price-place');
    keyup_fill('#price_postfix', '#price-postfix');
    keyup_fill('#hour_price', '#price-place');
    keyup_fill('#listing_bedrooms', '#total-beds');
    keyup_fill('#guests', '#total-guests');
    keyup_fill('#baths', '#total-baths');

    function amenities_selector(ele, view_ele, is_text) {
      $(ele).on('change', function () {
        if (is_text == 'yes') {
          var selected = $(this).find('option:selected').text();
        } else {
          var selected = $(this).find('option:selected').val();
        }
        $(view_ele).html(selected);
      });
    }
    amenities_selector('#listing_type', '#listing-type-view', 'yes');

    /*--------------------------------------------------------------------------
     *  Delete property
     * -------------------------------------------------------------------------*/
    $('.delete-listing').on('click', function () {
      var $this = $(this);
      var listing_id = $this.data('id');
      var nonce = $this.data('nonce');

      bootbox.confirm({
        message: '<p><strong>' + are_you_sure_text + '</strong></p>',
        buttons: {
          confirm: {
            label: delete_btn_text,
            className: 'btn btn-primary btn-half-width',
          },
          cancel: {
            label: cancel_btn_text,
            className: 'btn btn-grey-outlined btn-half-width',
          },
        },
        callback: function (result) {
          if (result == true) {
            $.ajax({
              type: 'POST',
              dataType: 'json',
              url: ajaxurl,
              data: {
                action: 'homey_delete_listing',
                listing_id: listing_id,
                security: nonce,
              },
              beforeSend: function () {
                $this.find('i').removeClass('fa-trash');
                $this.find('i').addClass('fa-spin fa-spinner');
              },
              success: function (data) {
                if (data.success == true) {
                  window.location.reload();
                } else {
                  jQuery('#homey_modal').modal('hide');
                  alert(data.reason);
                }
              },
              error: function (errorThrown) {},
            }); // $.ajax
          } // result
        }, // Callback
      });

      return false;
    });

    /*--------------------------------------------------------------------------
     *  Make property Featured
     * -------------------------------------------------------------------------*/
    $('.membership-featured-js').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var listing_id = $this.data('id');
      var nonce = $this.data('nonce');

      bootbox.confirm({
        message: '<p><strong>' + are_you_sure_text + '</strong></p>',
        buttons: {
          confirm: {
            label: confirm_btn_text,
            className: 'btn btn-primary btn-half-width',
          },
          cancel: {
            label: cancel_btn_text,
            className: 'btn btn-grey-outlined btn-half-width',
          },
        },
        callback: function (result) {
          if (result == true) {
            $.ajax({
              type: 'POST',
              dataType: 'json',
              url: ajaxurl,
              data: {
                action: 'homey_membership_featured_listing',
                listing_id: listing_id,
                security: nonce,
              },
              beforeSend: function () {
                $this.find('i').removeClass('fa-star-o');
                $this.find('i').addClass('fa-spin fa-spinner');
              },
              success: function (data) {
                if (data.success == true) {
                  window.location.reload();
                } else {
                  $this.find('i').removeClass('fa-spin fa-spinner');
                  $this.find('i').addClass('fa-star-o');
                  jQuery('#homey_modal').modal('hide');
                  alert(data.reason);
                }
              },
              error: function (errorThrown) {},
            }); // $.ajax
          } // result
        }, // Callback
      });

      return false;
    });

    /*--------------------------------------------------------------------------
     *  Delete reservtion
     * -------------------------------------------------------------------------*/
    $('.reservation-delete').on('click', function () {
      var $this = $(this);
      var reservation_id = $this.data('id');

      bootbox.confirm({
        message: '<p><strong>' + are_you_sure_text + '</strong></p>',
        buttons: {
          confirm: {
            label: delete_btn_text,
            className: 'btn btn-primary btn-half-width',
          },
          cancel: {
            label: cancel_btn_text,
            className: 'btn btn-grey-outlined btn-half-width',
          },
        },
        callback: function (result) {
          if (result == true) {
            $.ajax({
              type: 'POST',
              dataType: 'json',
              url: ajaxurl,
              data: {
                action: 'homey_reservation_del',
                reservation_id: reservation_id,
              },
              beforeSend: function () {
                $this.find('i').removeClass('fa-trash');
                $this.find('i').addClass('fa-spin fa-spinner');
              },
              success: function (data) {
                if (data.success == true) {
                  window.location.href = data.url;
                } else {
                  jQuery('#homey_modal').modal('hide');
                  alert(data.reason);
                }
              },
              error: function (errorThrown) {},
            }); // $.ajax
          } // result
        }, // Callback
      });

      return false;
    });

    /*--------------------------------------------------------------------------
     *  mark as paid
     * -------------------------------------------------------------------------*/
    $('.mark-as-paid').on('click', function () {
      var $this = $(this);
      var reservation_id = $this.data('id');

      bootbox.confirm({
        message: '<p><strong>' + are_you_sure_text + '</strong></p>',
        buttons: {
          confirm: {
            label: confirm_btn_text,
            className: 'btn btn-primary btn-half-width',
          },
          cancel: {
            label: cancel_btn_text,
            className: 'btn btn-grey-outlined btn-half-width',
          },
        },
        callback: function (result) {
          if (result == true) {
            $.ajax({
              type: 'POST',
              dataType: 'json',
              url: ajaxurl,
              data: {
                action: 'homey_reservation_mark_paid',
                reservation_id: reservation_id,
              },
              beforeSend: function () {
                $this.find('i').removeClass('fa-money');
                $this.find('i').addClass('fa-spin fa-spinner');
              },
              success: function (data) {
                if (data.success == true) {
                  window.location.reload();
                } else {
                  jQuery('#homey_modal').modal('hide');
                  alert(data.reason);
                }
              },
              error: function (errorThrown) {},
            }); // $.ajax
          } // result
        }, // Callback
      });

      return false;
    });

    /*--------------------------------------------------------------------------
     *  Put on Hold
     * -------------------------------------------------------------------------*/
    $('.put_on_hold').on('click', function () {
      var $this = $(this);
      var listing_id = $this.data('id');
      var current_status = $this.data('current');

      $.ajax({
        type: 'POST',
        dataType: 'json',
        url: ajaxurl,
        data: {
          action: 'homey_put_hold_listing',
          listing_id: listing_id,
          current_status: current_status,
        },
        beforeSend: function () {
          $this.find('i').removeClass('fa-pause');
          $this.find('i').addClass('fa-spin fa-spinner');
        },
        success: function (data) {
          if (data.success == true) {
            window.location.reload();
          } else {
            jQuery('#homey_modal').modal('hide');
            alert(data.reason);
          }
        },
        error: function (errorThrown) {},
      }); // $.ajax

      return false;
    });

    /*---------------------------------------------------------------------------
     *
     * Messaging system
     * -------------------------------------------------------------------------*/

    /*
     * Message Thread Form
     * -----------------------------*/
    $('.start_thread_form').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var $form = $this.parents('form');
      var $result = $('.messages-notification');

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
            $result.empty().append(response.msg);
            $form.find('input').val('');
            $form.find('textarea').val('');
            window.location.replace(response.redirect_link);
          } else {
            $result.empty().append(response.msg);
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

    /*
     * Property Message Notifications
     * -----------------------------*/
    var houzez_message_notifications = function () {
      $.ajax({
        url: ajaxurl,
        data: {
          action: 'houzez_chcek_messages_notifications',
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
     * Property Thread Message Form
     * -----------------------------*/
    $('.start_thread_message_form').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var $form = $this.parents('form');
      var $result = $('.messages-notification');

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
            window.location.replace(response.url);
          } else {
            $result.empty().append(response.msg);
          }
        },
        complete: function () {
          $this.children('i').removeClass(process_loader_spinner);
          $this.children('i').addClass(success_icon);
        },
      });
    });

    $('.homey_delete_msg_thread').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var thread_id = $this.data('thread-id');
      var sender_id = $this.data('sender-id');
      var receiver_id = $this.data('receiver-id');

      bootbox.confirm({
        message: '<p><strong>' + are_you_sure_text + '</strong></p>',
        buttons: {
          confirm: {
            label: delete_btn_text,
            className: 'btn btn-primary btn-half-width',
          },
          cancel: {
            label: cancel_btn_text,
            className: 'btn btn-grey-outlined btn-half-width',
          },
        },
        callback: function (result) {
          if (result == true) {
            $.ajax({
              type: 'POST',
              dataType: 'json',
              url: ajaxurl,
              data: {
                action: 'homey_delete_message_thread',
                thread_id: thread_id,
                sender_id: sender_id,
                receiver_id: receiver_id,
              },
              beforeSend: function () {
                $this.find('i').removeClass('fa-trash');
                $this.find('i').addClass('fa-spin fa-spinner');
              },
              success: function (data) {
                if (data.success == true) {
                  window.location.reload();
                } else {
                  jQuery('#homey_modal').modal('hide');
                }
              },
              error: function (errorThrown) {},
            }); // $.ajax
          } // result
        }, // Callback
      });
    });

    $('.homey_delete_message').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var message_id = $this.data('message-id');
      var created_by = $this.data('created-by');

      bootbox.confirm({
        message: '<p><strong>' + are_you_sure_text + '</strong></p>',
        buttons: {
          confirm: {
            label: delete_btn_text,
            className: 'btn btn-primary btn-half-width',
          },
          cancel: {
            label: cancel_btn_text,
            className: 'btn btn-grey-outlined btn-half-width',
          },
        },
        callback: function (result) {
          if (result == true) {
            $.ajax({
              type: 'POST',
              dataType: 'json',
              url: ajaxurl,
              data: {
                action: 'homey_delete_message',
                message_id: message_id,
                created_by: created_by,
              },
              beforeSend: function () {
                $this.find('i').removeClass('fa-trash');
                $this.find('i').addClass('fa-spin fa-spinner');
              },
              success: function (data) {
                if (data.success == true) {
                  window.location.reload();
                } else {
                  jQuery('#homey_modal').modal('hide');
                }
              },
              error: function (errorThrown) {},
            }); // $.ajax
          } // result
        }, // Callback
      });
    });

    var homey_processing_modal = function (msg) {
      var process_modal =
        '<div class="modal fade" id="homey_modal" tabindex="-1" role="dialog" aria-labelledby="faveModalLabel" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-body homey_messages_modal">' +
        msg +
        '</div></div></div></div></div>';
      jQuery('body').append(process_modal);
      jQuery('#homey_modal').modal();
    };

    var homey_processing_modal_close = function () {
      jQuery('#homey_modal').modal('hide');
    };

    /* ------------------------------------------------------------------------ */
    /*  Listing Thumbnails actions ( make features & delete )
         /* ------------------------------------------------------------------------ */
    var lisitng_thumbnail_event = function () {
      // Set Featured Image
      $('.icon-featured').on('click', function (e) {
        e.preventDefault();

        var $this = jQuery(this);
        var thumb_id = $this.data('attachment-id');
        var thumb = $this.data('thumb');
        var icon = $this.find('i');

        $('.upload-view-media .media-image img').attr('src', thumb);
        $('.upload-gallery-thumb-buttons .featured_image_id').remove();
        $('.upload-gallery-thumb-buttons .icon-featured i')
          .removeClass('fa-star')
          .addClass('fa-star-o');

        $this
          .closest('.upload-gallery-thumb-buttons')
          .append(
            '<input type="hidden" class="featured_image_id" name="featured_image_id" value="' +
              thumb_id +
              '">'
          );
        icon.removeClass('fa-star-o').addClass('fa-star');
      });

      //Remove Image
      $('.icon-delete').on('click', function (e) {
        e.preventDefault();

        var $this = $(this);
        var thumbnail = $this.closest('.listing-thumb');
        var loader = $this.siblings('.icon-loader');
        var listing_id = $this.data('listing-id');
        var thumb_id = $this.data('attachment-id');

        loader.show();

        var ajax_request = $.ajax({
          type: 'post',
          url: ajaxurl,
          dataType: 'json',
          data: {
            action: 'homey_remove_listing_thumbnail',
            listing_id: listing_id,
            thumb_id: thumb_id,
            removeNonce: verify_nonce,
          },
        });

        ajax_request.done(function (response) {
          if (response.remove_attachment) {
            thumbnail.remove();
          } else {
          }
        });

        ajax_request.fail(function (jqXHR, textStatus) {
          alert('Request failed: ' + textStatus);
        });
      });
    };

    lisitng_thumbnail_event();

    /*--------------------------------------------------------------------------
     *  Uplaod listing gallery
     * -------------------------------------------------------------------------*/
    var listing_gallery_images = function () {
      $(document).keypress(function (ev) {
        if ($('#listing_title').length > 0) {
          if ((ev.which && ev.which === 13) || (ev.keyCode && ev.keyCode === 13)) {
            ev.preventDefault();
            return false;
          }
        }
      });

      $('#homey_gallery_container').sortable({
        placeholder: 'sortable-placeholder',
      });

      var plup_uploader = new plupload.Uploader({
        browse_button: 'select_gallery_images',
        file_data_name: 'listing_upload_file',
        container: 'homey_gallery_dragDrop',
        drop_element: 'homey_gallery_dragDrop',
        url: ajaxurl + '?action=homey_listing_gallery_upload&verify_nonce=' + verify_nonce,
        filters: {
          mime_types: [{ title: verify_file_type, extensions: 'jpg,jpeg,gif,png' }],
          max_file_size: '10m', //image_max_file_size,
          prevent_duplicates: false,
        },
      });
      plup_uploader.init();

      plup_uploader.bind('FilesAdded', function (up, files) {
        var homey_thumbs = '';
        var maxfiles = '50'; //max_prop_images;
        if (up.files.length > maxfiles) {
          up.splice(maxfiles);
          alert('no more than ' + maxfiles + ' file(s)');
          return;
        }
        plupload.each(files, function (file) {
          homey_thumbs +=
            '<div id="thumb-holder-' +
            file.id +
            '" class="col-sm-2 col-xs-4 listing-thumb">' +
            '' +
            '</div>';
        });
        document.getElementById('homey_gallery_container').innerHTML += homey_thumbs;
        up.refresh();
        plup_uploader.start();
      });

      plup_uploader.bind('UploadProgress', function (up, file) {
        document.getElementById('thumb-holder-' + file.id).innerHTML =
          '<span>' + file.percent + '%</span>';
      });

      plup_uploader.bind('Error', function (up, err) {
        document.getElementById('homey_errors').innerHTML +=
          '<br/>' + 'Error #' + err.code + ': ' + err.message;
      });

      plup_uploader.bind('FileUploaded', function (up, file, ajax_response) {
        var response = $.parseJSON(ajax_response.response);

        if (response.success) {
          var gallery_thumbnail =
            '<figure class="upload-gallery-thumb">' +
            '<img src="' +
            response.url +
            '" alt="thumb">' +
            '</figure>' +
            '<div class="upload-gallery-thumb-buttons">' +
            '<a class="icon-featured" data-thumb="' +
            response.thumb +
            '" data-listing-id="' +
            0 +
            '"  data-attachment-id="' +
            response.attachment_id +
            '"><i class="fa fa-star-o"></i></a>' +
            '<button class="icon-delete" data-listing-id="' +
            0 +
            '"  data-attachment-id="' +
            response.attachment_id +
            '"><i class="fa fa-trash-o"></i></button>' +
            '<input type="hidden" class="listing-image-id" name="listing_image_ids[]" value="' +
            response.attachment_id +
            '"/>' +
            '</div>' +
            '<span style="display: none;" class="icon icon-loader"><i class="fa fa-spinner fa-spin"></i></span>';

          document.getElementById('thumb-holder-' + file.id).innerHTML = gallery_thumbnail;

          lisitng_thumbnail_event();
        } else {
          var gallery_thumbnail = '<span class="error-message">' + response.reason + '</span>';

          document.getElementById('thumb-holder-' + file.id).innerHTML = gallery_thumbnail;
          console.log(response);
        }
      });
    };
    listing_gallery_images();

    /* ------------------------------------------------------------------------ */
    /*  Bedrooms
         /* ------------------------------------------------------------------------ */

    $('#add_more_bedrooms').on('click', function (e) {
      e.preventDefault();

      var numVal = $(this).data('increment') + 1;
      $(this).data('increment', numVal);
      $(this).attr({
        'data-increment': numVal,
      });

      var newBedroom =
        '' +
        '<div class="more_rooms_wrap">' +
        '<div class="row">' +
        '<div class="col-sm-6 col-xs-12">' +
        '<div class="form-group">' +
        '<label for="acc_bedroom_name">' +
        acc_bedroom_name +
        '</label>' +
        '<input type="text" name="homey_accomodation[' +
        numVal +
        '][acc_bedroom_name]" class="form-control" placeholder="' +
        acc_bedroom_name_plac +
        '">' +
        '</div>' +
        '</div>' +
        '<div class="col-sm-6 col-xs-12">' +
        '<div class="form-group">' +
        '<label for="acc_guests">' +
        acc_guests +
        '</label>' +
        '<input type="text" name="homey_accomodation[' +
        numVal +
        '][acc_guests]" class="form-control" placeholder="' +
        acc_guests_plac +
        '">' +
        '</div>' +
        '</div>' +
        '</div>' +
        '<div class="row">' +
        '<div class="col-sm-6 col-xs-12">' +
        '<div class="form-group">' +
        '<label for="acc_no_of_beds">' +
        acc_no_of_beds +
        '</label>' +
        '<input type="text" name="homey_accomodation[' +
        numVal +
        '][acc_no_of_beds]" class="form-control" placeholder="' +
        acc_no_of_beds_plac +
        '">' +
        '</div>' +
        '</div>' +
        '<div class="col-sm-6 col-xs-12">' +
        '<div class="form-group">' +
        '<label for="acc_bedroom_type">' +
        acc_bedroom_type +
        '</label>' +
        '<input type="text" name="homey_accomodation[' +
        numVal +
        '][acc_bedroom_type]" class="form-control" placeholder="' +
        acc_bedroom_type_plac +
        '">' +
        '</div>' +
        '</div>' +
        '</div>' +
        '<div class="row">' +
        '<div class="col-sm-12 col-xs-12">' +
        '<button type="button" data-remove="' +
        numVal +
        '" class="btn btn-primary remove-beds">' +
        acc_btn_remove_room +
        '</button>' +
        ' </div>' +
        '</div>' +
        '<hr>';
      ('</div>');

      $('#more_bedrooms_main').append(newBedroom);
      removeBedroom();
    });

    var removeBedroom = function () {
      $('.remove-beds').on('click', function (event) {
        event.preventDefault();
        var $this = $(this);
        $this.closest('.more_rooms_wrap').remove();
      });
    };
    removeBedroom();

    /* ------------------------------------------------------------------------ */
    /*  Extra services pricing
         /* ------------------------------------------------------------------------ */

    $('#add_more_extra_services').on('click', function (e) {
      e.preventDefault();

      var numVal = $(this).data('increment') + 1;
      $(this).data('increment', numVal);
      $(this).attr({
        'data-increment': numVal,
      });

      var newOption =
        '' +
        '<div class="more_extra_services_wrap">' +
        '<div class="row">' +
        '<div class="col-sm-4 col-xs-12">' +
        '<div class="form-group">' +
        '<label for="name">' +
        Homey_Listing.ex_name +
        '</label>' +
        '<input type="text" name="extra_price[' +
        numVal +
        '][name]" class="form-control" placeholder="' +
        Homey_Listing.ex_name_plac +
        '">' +
        '</div>' +
        '</div>' +
        '<div class="col-sm-4 col-xs-12">' +
        '<div class="form-group">' +
        '<label for="price"> ' +
        Homey_Listing.ex_price +
        ' </label>' +
        '<input type="text" name="extra_price[' +
        numVal +
        '][price]" class="form-control" placeholder="' +
        Homey_Listing.ex_price_plac +
        '">' +
        '</div>' +
        '</div>' +
        '<div class="col-sm-4 col-xs-12">' +
        '<div class="form-group">' +
        '<label for="type"> ' +
        Homey_Listing.ex_type +
        ' </label>' +
        '<select name="extra_price[' +
        numVal +
        '][type]" class="type-select-picker selectpicker" data-live-search="false" data-live-search-style="begins">' +
        '<option value="single_fee">' +
        Homey_Listing.ex_single_fee +
        '</option>' +
        '<option value="per_night"> ' +
        Homey_Listing.ex_per_night +
        '</option>' +
        '<option value="per_guest">' +
        Homey_Listing.ex_per_guest +
        '</option>' +
        '<option value="per_night_per_guest">' +
        Homey_Listing.ex_per_night_per_guest +
        '</option>' +
        '</select>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '<div class="row">' +
        '<div class="col-sm-12 col-xs-12">' +
        '<button type="button" data-remove="' +
        numVal +
        '" class="remove-extra-services btn btn-primary btn-slim">' +
        Homey_Listing.delete_btn_text +
        '</button>' +
        '</div>' +
        '</div>' +
        '</div>';

      $('#more_extra_services_main').append(newOption);
      $('.type-select-picker').selectpicker('refresh');
      removeExtraServices();
    });

    var removeExtraServices = function () {
      $('.remove-extra-services').on('click', function (event) {
        event.preventDefault();
        var $this = $(this);
        $this.closest('.more_extra_services_wrap').remove();
      });
    };
    removeExtraServices();

    /* ------------------------------------------------------------------------ */
    /*  Services
         /* ------------------------------------------------------------------------ */

    $('#add_more_service').on('click', function (e) {
      e.preventDefault();

      var numVal = $(this).data('increment') + 1;
      $(this).data('increment', numVal);
      $(this).attr({
        'data-increment': numVal,
      });

      var newService =
        '' +
        '<div class="more_services_wrap">' +
        '<div class="row">' +
        '<div class="col-sm-6 col-xs-12">' +
        '<div class="form-group">' +
        '<label for="service_name">' +
        service_name +
        '</label>' +
        '<input type="text" name="homey_services[' +
        numVal +
        '][service_name]" class="form-control" placeholder="' +
        service_name_plac +
        '">' +
        '</div>' +
        '</div>' +
        '<div class="col-sm-6 col-xs-12">' +
        '<div class="form-group">' +
        '<label for="service_price">' +
        service_price +
        '</label>' +
        '<input type="text" name="homey_services[' +
        numVal +
        '][service_price]" class="form-control" placeholder="' +
        service_price_plac +
        '">' +
        '</div>' +
        '</div>' +
        '</div>' +
        '<div class="row">' +
        '<div class="col-sm-12 col-xs-12">' +
        '<div class="form-group">' +
        '<label for="service_des">' +
        service_des +
        '</label>' +
        '<textarea placeholder="' +
        service_des_plac +
        '" rows="3" name="homey_services[' +
        numVal +
        '][service_des]" class="form-control"></textarea>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '<div class="row">' +
        '<div class="col-sm-12 col-xs-12">' +
        '<button type="button" data-remove="' +
        numVal +
        '" class="btn btn-primary remove-service">' +
        btn_remove_service +
        '</button>' +
        ' </div>' +
        '</div>' +
        '<hr>';
      ('</div>');

      $('#more_services_main').append(newService);
      removeService();
    });

    var removeService = function () {
      $('.remove-service').on('click', function (event) {
        event.preventDefault();
        var $this = $(this);
        $this.closest('.more_services_wrap').remove();
      });
    };
    removeService();

    /*--------------------------------------------------------------------------
     *  Thread Message Attachment
     * -------------------------------------------------------------------------*/
    var thread_message_attachment = function () {
      /* initialize uploader */
      var uploader = new plupload.Uploader({
        browse_button: 'thread-message-attachment',
        file_data_name: 'messages_upload_file',
        container: 'listing-thumbs-container',
        multi_selection: true,
        url: ajaxurl + '?action=homey_message_attacment_upload&verify_nonce=' + verify_nonce,
        filters: {
          max_file_size: '20m',
          prevent_duplicates: true,
        },
      });
      uploader.init();

      uploader.bind('FilesAdded', function (up, files) {
        var html = '';
        var listingThumb = '';
        var maxfiles = '10';
        if (up.files.length > maxfiles) {
          up.splice(maxfiles);
          alert('no more than ' + maxfiles + ' file(s)');
          return;
        }
        plupload.each(files, function (file) {
          listingThumb +=
            '<li id="thumb-holder-' + file.id + '" class="listing-thumb">' + '' + '</li>';
        });
        document.getElementById('listing-thumbs-container').innerHTML += listingThumb;
        up.refresh();
        uploader.start();
      });

      uploader.bind('UploadProgress', function (up, file) {
        document.getElementById('thumb-holder-' + file.id).innerHTML =
          '<li><lable>' + file.name + '<span>' + file.percent + '%</span></lable></li>';
      });

      uploader.bind('Error', function (up, err) {
        document.getElementById('errors-log').innerHTML +=
          '<br/>' + 'Error #' + err.code + ': ' + err.message;
      });

      uploader.bind('FileUploaded', function (up, file, ajax_response) {
        var response = $.parseJSON(ajax_response.response);

        if (response.success) {
          console.log(ajax_response);

          var message_html =
            '<div class="attach-icon delete-attachment">' +
            '<i class="fa fa-trash remove-message-attachment" data-attachment-id="' +
            response.attachment_id +
            '"></i>' +
            '</div>' +
            '<span class="attach-text">' +
            response.file_name +
            '</span>' +
            '<input type="hidden" class="listing-image-id" name="listing_image_ids[]" value="' +
            response.attachment_id +
            '"/>';

          document.getElementById('thumb-holder-' + file.id).innerHTML = message_html;

          messageAttachment();
          thread_message_attachment();
        } else {
          console.log(response);
          alert('error');
        }
      });

      uploader.refresh();
    };
    thread_message_attachment();

    var messageAttachment = function () {
      $('.remove-message-attachment').on('click', function () {
        var $this = $(this);
        var thumbnail = $this.closest('li');
        var thumb_id = $this.data('attachment-id');
        $this.removeClass('fa-trash');
        $this.addClass('fa-spinner');

        var ajax_request = $.ajax({
          type: 'post',
          url: ajaxurl,
          dataType: 'json',
          data: {
            action: 'homey_remove_message_attachment',
            thumbnail_id: thumb_id,
          },
        });

        ajax_request.done(function (response) {
          if (response.attachment_remove) {
            thumbnail.remove();
          } else {
          }
          thread_message_attachment();
        });

        ajax_request.fail(function (jqXHR, textStatus) {
          alert('Request failed: ' + textStatus);
        });
      });
    };

    /*---------------------------------------------------------------------------
     *  iCalendar
     *--------------------------------------------------------------------------*/
    $('#import_ical_feeds').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var ical_feed_name = [];
      var ical_feed_url = [];

      var listing_id = $('input[name="listing_id"]').val();

      $('.ical_feed_name').each(function () {
        ical_feed_name.push($(this).val());
      });

      $('.ical_feed_url').each(function () {
        ical_feed_url.push($(this).val());
      });

      if (ical_feed_name == '' || ical_feed_url == '') {
        alert(Homey_Listing.add_ical_feeds);
        return;
      }

      $.ajax({
        url: ajaxurl,
        method: 'POST',
        dataType: 'JSON',
        data: {
          action: 'homey_add_ical_feeds',
          listing_id: listing_id,
          ical_feed_name: ical_feed_name,
          ical_feed_url: ical_feed_url,
        },

        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (response) {
          if (response.success) {
            window.location.href = response.url;
          } else {
            $this.children('i').remove();
          }
          alert(response.message);
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          //$this.children('i').removeClass(process_loader_spinner);
        },
      });
    }); // end #import_ical_feeds

    $('#add_more_feed').on('click', function (e) {
      e.preventDefault();

      var ical_feed_name = $('.enter_ical_feed_name').val();
      var ical_feed_url = $('.enter_ical_feed_url').val();

      if (ical_feed_name == '' || ical_feed_url == '') {
        alert(Homey_Listing.both_required);
        return;
      }

      var numVal = $(this).data('increment') + 1;
      $(this).data('increment', numVal);
      $(this).attr({
        'data-increment': numVal,
      });

      var newFeed =
        '' +
        '<div class="imported-calendar-row clearfix">' +
        '<div class="imported-calendar-50">' +
        '<input type="text" name="ical_feed_name[]" class="form-control ical_feed_name" value="' +
        ical_feed_name +
        '">' +
        '</div>' +
        '<div class="imported-calendar-50">' +
        '<input type="text" name="ical_feed_url[]" class="form-control ical_feed_url" value="' +
        ical_feed_url +
        '">' +
        '</div>';
      ('</div>');

      $('#ical-feeds-container').append(newFeed);
      removeICalFeed();
      $('.ical-dummy').val('');
    });

    var removeICalFeed = function () {
      $('.remove-ical-feed').on('click', function (event) {
        event.preventDefault();

        var $this = $(this);
        var listing_id = $('input[name="listing_id"]').val();
        var remove_index = $this.data('remove');

        $.ajax({
          url: ajaxurl,
          method: 'POST',
          dataType: 'JSON',
          data: {
            action: 'homey_remove_ical_feeds',
            listing_id: listing_id,
            remove_index: remove_index,
          },

          beforeSend: function () {
            $this.children('i').remove();
            $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
          },
          success: function (response) {
            console.log(response.message);
            if (response.success) {
              $this.closest('.imported-calendar-row').remove();
              var reloadWindow = setInterval(function () {
                window.location.reload();
                clearInterval(reloadWindow);
              }, 1000);
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

        var numVal = $('#add_more_feed').data('increment');
        $('#add_more_feed').attr({
          'data-increment': numVal - 1,
        });
      });
    };
    removeICalFeed();

    /*--------------------------------------------------------------------------
     * Extra expenses
     *---------------------------------------------------------------------------*/
    $('#save_expenses').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var expense_name = [];
      var expense_value = [];

      var reservation_id = $('#expense_rsv_id').val();

      $('.expense_name').each(function () {
        expense_name.push($(this).val());
      });

      $('.expense_value').each(function () {
        expense_value.push($(this).val());
      });

      if (expense_name == '' || expense_value == '') {
        alert(Homey_Listing.add_expense_msg);
        return;
      }

      $.ajax({
        url: ajaxurl,
        method: 'POST',
        dataType: 'JSON',
        data: {
          action: 'homey_save_extra_expenses',
          reservation_id: reservation_id,
          expense_name: expense_name,
          expense_value: expense_value,
        },

        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (response) {
          if (response.success) {
            window.location.href = response.url;
          } else {
            $this.children('i').remove();
            alert(response.message);
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
    }); // end #import_ical_feeds

    $('#add_more_expense').on('click', function (e) {
      e.preventDefault();

      var expense_name = $('.enter_expense_name').val();
      var expense_value = $('.enter_expense_value').val();

      if (expense_name == '' || expense_value == '') {
        alert(Homey_Listing.both_required);
        return;
      }

      var numVal = $(this).data('increment') + 1;
      $(this).data('increment', numVal);
      $(this).attr({
        'data-increment': numVal,
      });

      var newFeed =
        '' +
        '<div class="imported-calendar-row clearfix">' +
        '<div class="imported-calendar-50">' +
        '<input type="text" name="expense_name[]" class="form-control expense_name" value="' +
        expense_name +
        '">' +
        '</div>' +
        '<div class="imported-calendar-50">' +
        '<input type="text" name="expense_value[]" class="form-control expense_value" value="' +
        expense_value +
        '">' +
        '</div>';
      ('</div>');

      $('#expenses-container').append(newFeed);
      removeICalFeed();
      $('.expense-dummy').val('');
    });

    var removeExtraExpenses = function () {
      $('.remove-expense').on('click', function (event) {
        event.preventDefault();

        var $this = $(this);
        var reservation_id = $('#expense_rsv_id').val();
        var remove_index = $this.data('remove');

        $.ajax({
          url: ajaxurl,
          method: 'POST',
          dataType: 'JSON',
          data: {
            action: 'homey_remove_extra_expense',
            reservation_id: reservation_id,
            remove_index: remove_index,
          },

          beforeSend: function () {
            $this.children('i').remove();
            $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
          },
          success: function (response) {
            console.log(response.message);
            if (response.success) {
              $this.closest('.imported-calendar-row').remove();
              window.location.reload();
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

        var numVal = $('#add_more_expense').data('increment');
        $('#add_more_expense').attr({
          'data-increment': numVal - 1,
        });
      });
    };
    removeExtraExpenses();

    /*--------------------------------------------------------------------------
     * Discount
     *---------------------------------------------------------------------------*/
    $('#save_discounts').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var discount_name = [];
      var discount_value = [];

      var reservation_id = $('#expense_rsv_id').val();

      $('.discount_name').each(function () {
        discount_name.push($(this).val());
      });
      $('.discount_value').each(function () {
        discount_value.push($(this).val());
      });

      if (discount_name == '' || discount_value == '') {
        alert(Homey_Listing.add_expense_msg);
        return;
      }

      $.ajax({
        url: ajaxurl,
        method: 'POST',
        dataType: 'JSON',
        data: {
          action: 'homey_save_discounts',
          reservation_id: reservation_id,
          discount_name: discount_name,
          discount_value: discount_value,
        },

        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (response) {
          if (response.success) {
            window.location.href = response.url;
          } else {
            $this.children('i').remove();
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          //$this.children('i').removeClass(process_loader_spinner);
        },
      });
    }); // end #import_ical_feeds

    $('#add_more_discount').on('click', function (e) {
      e.preventDefault();

      var discount_name = $('.enter_discount_name').val();
      var discount_value = $('.enter_discount_value').val();

      if (discount_name == '' || discount_value == '') {
        alert(Homey_Listing.both_required);
        return;
      }

      var numVal = $(this).data('increment') + 1;
      $(this).data('increment', numVal);
      $(this).attr({
        'data-increment': numVal,
      });

      var newFeed =
        '' +
        '<div class="imported-calendar-row clearfix">' +
        '<div class="imported-calendar-50">' +
        '<input type="text" name="discount_name[]" class="form-control discount_name" value="' +
        discount_name +
        '">' +
        '</div>' +
        '<div class="imported-calendar-50">' +
        '<input type="text" name="discount_value[]" class="form-control discount_value" value="' +
        discount_value +
        '">' +
        '</div>';
      ('</div>');

      $('#discount-container').append(newFeed);
      removeICalFeed();
      $('.discount-dummy').val('');
    });

    var removeDiscount = function () {
      $('.remove-discount').on('click', function (event) {
        event.preventDefault();

        var $this = $(this);
        var reservation_id = $('#expense_rsv_id').val();
        var remove_index = $this.data('remove');

        $.ajax({
          url: ajaxurl,
          method: 'POST',
          dataType: 'JSON',
          data: {
            action: 'homey_remove_discount',
            reservation_id: reservation_id,
            remove_index: remove_index,
          },

          beforeSend: function () {
            $this.children('i').remove();
            $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
          },
          success: function (response) {
            console.log(response.message);
            if (response.success) {
              $this.closest('.imported-calendar-row').remove();
              window.location.reload();
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

        var numVal = $('#add_more_discount').data('increment');
        $('#add_more_discount').attr({
          'data-increment': numVal - 1,
        });
      });
    };
    removeDiscount();

    /*--------------------------------------------------------------------------
     * unavailable dates
     *---------------------------------------------------------------------------*/
    function homey_unavailable_dates() {
      $('.available, .unavailable').on('click', function () {
        var $this = $(this);
        var selected_date = $this.data('formatted-date');
        var listing_id = $('#period_listing_id').val();

        if ($this.hasClass('past-day')) {
          return;
        }

        $.ajax({
          type: 'post',
          url: ajaxurl,
          dataType: 'json',
          data: {
            action: 'homey_make_date_unavaiable',
            selected_date: selected_date,
            listing_id: listing_id,
          },
          beforeSend: function () {
            $this.children('i').remove();
            $this.prepend('<i class="icon-center ' + process_loader_spinner + '"></i>');
          },
          success: function (data) {
            if (data.success) {
              if (data.message == 'made_available') {
                $this.removeClass('unavailable');
                $this.addClass('available');
                $this.find('.day-status').text(avail_label);
              } else {
                $this.removeClass('available');
                $this.addClass('unavailable');
                $this.find('.day-status').text(unavail_label);
              }
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
    homey_unavailable_dates(); // end homey_unavailable_dates

    $('.choose_payout_method').on('click', function (e) {
      var $this = $(this);
      var current_method = $this.val();

      if (current_method == 'wire') {
        $('#wire_transfer').show();
        $('#paypal').hide();
        $('#skrill').hide();
      } else if (current_method == 'paypal') {
        $('#wire_transfer').hide();
        $('#paypal').show();
        $('#skrill').hide();
      } else if (current_method == 'skrill') {
        $('#wire_transfer').hide();
        $('#paypal').hide();
        $('#skrill').show();
      }
    });

    function homey_show_payout_method() {
      var current_method = $("input[name='payout_method']:checked").val();

      if (current_method == 'wire') {
        $('#wire_transfer').show();
        $('#paypal').hide();
        $('#skrill').hide();
      } else if (current_method == 'paypal') {
        $('#wire_transfer').hide();
        $('#paypal').show();
        $('#skrill').hide();
      } else if (current_method == 'skrill') {
        $('#wire_transfer').hide();
        $('#paypal').hide();
        $('#skrill').show();
      }
    }

    if (jQuery('.choose_payout_method').length > 0) {
      jQuery(window).load(function () {
        homey_show_payout_method();
      });
    }

    $('.define-payout-methods input').on('focusin', function () {
      $(this).removeClass('error');
    });

    $('.homey_save_payout_method').on('click', function (e) {
      e.preventDefault();
      var $this = $(this);
      errorBlock.hide();
      $('.date-saved-success').hide();

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_save_payout_method_info',
          ben_first_name: $('#ben_first_name').val(),
          ben_last_name: $('#ben_last_name').val(),
          ben_company_name: $('#ben_company_name').val(),
          ben_tax_number: $('#ben_tax_number').val(),
          ben_street_address: $('#ben_street_address').val(),
          ben_apt_suit: $('#ben_apt_suit').val(),
          ben_city: $('#ben_city').val(),
          ben_state: $('#ben_state').val(),
          ben_zip_code: $('#ben_zip_code').val(),
          bank_account: $('#bank_account').val(),
          swift: $('#swift').val(),
          bank_name: $('#bank_name').val(),
          wir_street_address: $('#wir_street_address').val(),
          wir_aptsuit: $('#wir_aptsuit').val(),
          wir_city: $('#wir_city').val(),
          wir_state: $('#wir_state').val(),
          wir_zip_code: $('#wir_zip_code').val(),
          paypal_email: $('#paypal_email').val(),
          skrill_email: $('#skrill_email').val(),
          payout_method: $("input[name='payout_method']:checked").val(),
          security: $('#homey_payout_method_security').val(),
        },
        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (data) {
          if (data.success) {
            $('.date-saved-success').show();
          } else {
            var i;
            var required_fields = data.req;
            for (i = 0; i < required_fields.length; i++) {
              $('#' + required_fields[i]).addClass('error');
            }
            errorBlock.show();
          }
          $('html, body').animate(
            {
              scrollTop: 0,
            },
            'slow'
          );
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          $this.children('i').remove();
        },
      });
    });

    $('#homey_change_payout_status').on('click', function (e) {
      e.preventDefault();
      var $this = $(this);

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_update_payout_status',
          payout_status: $('#payout_status').val(),
          payout_id: $('#payout_id').val(),
          transfer_fee: $('#transfer_fee').val(),
          transfer_note: $('#transfer_note').val(),
          security: $('#homey_payout_status_security').val(),
        },
        beforeSend: function () {
          $this.empty();
          $this.html(
            '<div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>'
          );
        },
        success: function (data) {
          if (data.success) {
            window.location.reload();
          } else {
            $this.empty();
            $this.text(btn_save);
            alert(data.msg);
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          //$this.children('i').remove();
        },
      });
    });

    $('#payout_status').on('change', function () {
      if ($(this).val() == 3) {
        $('.transfer_fee, .transfer_note').show();
      } else {
        $('.transfer_fee, .transfer_note').hide();
      }
    });

    $(window).load(function () {
      var payout_status = $('#payout_status').val();
      if (payout_status == 3) {
        $('.transfer_fee, .transfer_note').show();
      } else {
        $('.transfer_fee, .transfer_note').hide();
      }
    });

    $('#request_payout').on('click', function (e) {
      e.preventDefault();
      var $this = $(this);
      var payout_alert = $('#payout_alert');
      var payout_msg = $('#payout_alert span');
      payout_msg.empty();
      payout_alert.hide();

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_add_payout',
          payout_amount: $('#payout_amount').val(),
          security: $('#homey_payout_request_security').val(),
        },
        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (data) {
          if (data.success) {
            payout_alert.show();
            payout_alert.removeClass('alert-danger');
            payout_alert.addClass('alert-success');
            payout_msg.html(data.msg);
          } else {
            payout_alert.show();
            payout_alert.removeClass('alert-success');
            payout_alert.addClass('alert-danger');
            payout_msg.html(data.msg);
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          $this.children('i').remove();
        },
      });
    });

    /*--------------------------------------------------------------------------
     *  Admin adjust payment
     * -------------------------------------------------------------------------*/
    $('#btn_make_adjustment').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var $form = $this.parents('form');
      var $messages = $form.find('.homey_messages');

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
            window.location.reload();
          } else {
            $messages.empty().append(response.msg);
            $this.children('i').removeClass(process_loader_spinner);
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
     *  Admin adjust payment for guest
     * -------------------------------------------------------------------------*/
    $('#btn_guest_adjustment').on('click', function (e) {
      e.preventDefault();

      var $this = $(this);
      var $form = $this.parents('form');
      var $messages = $form.find('.homey_messages');

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
            window.location.reload();
          } else {
            $messages.empty().append(response.msg);
            $this.children('i').removeClass(process_loader_spinner);
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
     *  Save listing as draft
     * -------------------------------------------------------------------------*/
    $('#save_as_draft').click(function () {
      var $form = $('#submit_listing_form');
      var save_as_draft = $('#save_as_draft');
      var description = tinyMCE.get('description').getContent();

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: $form.serialize() + '&action=save_as_draft&description=' + description,
        beforeSend: function () {
          save_as_draft.children('i').remove();
          save_as_draft.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (response) {
          if (response.success) {
            save_as_draft.children('i').removeClass(process_loader_spinner);
            save_as_draft.children('i').addClass(success_icon);
            $('input[name=draft_listing_id]').remove();
            $('#submit_listing_form').prepend(
              '<input type="hidden" name="draft_listing_id" value="' + response.listing_id + '">'
            );
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
      });
    });

    /*--------------------------------------------------------------------------
     *  Local paymnet
     * -------------------------------------------------------------------------*/
    $('#host_payment_option').on('change', function () {
      var $this = $(this);

      if ($this.val() == 'percent') {
        $('.host-percentage').show();
      } else {
        $('.host-percentage').hide();
      }
    });

    $('#save_host_payment_option').on('click', function (e) {
      e.preventDefault();
      var $this = $(this);
      var payment_type = $('#host_payment_option').val();
      var percent = $('#payment_percent').val();

      var msg_alert = $('#msg_alert');
      var msg = $('#msg_alert span');
      msg.empty();
      msg_alert.hide();

      $.ajax({
        type: 'post',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'homey_add_host_payment_method',
          payment_type: payment_type,
          percent: percent,
        },
        beforeSend: function () {
          $this.children('i').remove();
          $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
        },
        success: function (data) {
          if (data.success) {
            msg_alert.show();
            msg_alert.removeClass('alert-danger');
            msg_alert.addClass('alert-success');
            msg.html(data.msg);
          } else {
            msg_alert.show();
            msg_alert.removeClass('alert-success');
            msg_alert.addClass('alert-danger');
            msg.html(data.msg);
          }
        },
        error: function (xhr, status, error) {
          var err = eval('(' + xhr.responseText + ')');
          console.log(err.Message);
        },
        complete: function () {
          $this.children('i').remove();
        },
      });
    });
  } // End Type Of
});
