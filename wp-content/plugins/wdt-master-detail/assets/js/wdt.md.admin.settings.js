(function ($) {
  $(function () {

    // Handle Activation Settings
    handleActivationSettings();


    // Add event on "Activate"/"Deactivate" button
    $('#wdt-activate-plugin-master-detail').on('click', function () {
      if (typeof wdt_current_config.wdtActivatedMasterDetail === 'undefined' || wdt_current_config.wdtActivatedMasterDetail == 0 || wdt_current_config.wdtActivatedMasterDetail == '') {
        activatePlugin()
      } else {
        deactivatePlugin()
      }
    });

    // Activate plugin
    function activatePlugin() {
      $('#wdt-activate-plugin-master-detail').html('<i class="wpdt-icon-spinner9"></i>Loading...');

      let domain    = location.hostname;
      let subdomain = location.hostname;

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'wpdatatables_activate_plugin',
          purchaseCodeStore: $('#wdt-purchase-code-store-master-detail').val().trim(),
          wdtNonce: $('#wdtNonce').val(),
          slug: 'wdt-master-detail',
          domain: domain,
          subdomain: subdomain
        },
        success: function (response) {
          let valid = JSON.parse(response).valid;
          let domainRegistered = JSON.parse(response).domainRegistered;

          if (valid === true && domainRegistered === true) {
            wdt_current_config.wdtActivatedMasterDetail = 1;
            wdt_current_config.wdtPurchaseCodeStoreMasterDetail = 1;
            wdtNotify('Success!', 'Plugin has been activated', 'success');
            $('#wdt-purchase-code-store-master-detail').val('');
            $('.wdt-purchase-code-store-master-detail-wrapper').hide();
            $('.wdt-purchase-code-master-detail .wdt-security-massage-wrapper').removeClass('hidden');
            $('#wdt-activate-plugin-master-detail').removeClass('btn-primary').addClass('btn-danger').html('<i class="wpdt-icon-times-circle-full"></i>Deactivate');
          } else if (valid === false) {
            wdtNotify(wpdatatables_settings_strings.error, wpdatatables_settings_strings.purchaseCodeInvalid, 'danger');
            $('#wdt-activate-plugin-master-detail').html('<i class="wpdt-icon-check-circle-full"></i>Activate');
          } else {
            wdtNotify(wpdatatables_settings_strings.error, wpdatatables_settings_strings.activation_domains_limit, 'danger');
            jQuery('#wdt-activate-plugin-master-detail').html('<i class="wpdt-icon-check-circle-full"></i>Activate');
          }
        },
        error: function () {
          wdt_current_config.wdtActivatedMasterDetail = 0;
          wdtNotify('Error!', 'Unable to activate the plugin. Please try again.', 'danger');
          $('#wdt-activate-plugin-master-detail').html('<i class="wpdt-icon-check-circle-full"></i>Activate');
        }
      });
    }

    // Deactivate plugin
    function deactivatePlugin() {
      $('#wdt-activate-plugin-master-detail').html('<i class="wpdt-icon-spinner9"></i>Loading...');

      let domain    = location.hostname;
      let subdomain = location.hostname;
      let params = {
        action: 'wpdatatables_deactivate_plugin',
        wdtNonce: $('#wdtNonce').val(),
        domain: domain,
        subdomain: subdomain,
        slug: 'wdt-master-detail',
      };

      if (parseInt(wdt_current_config.wdtPurchaseCodeStoreMasterDetail)) {
        params.type = 'code';
        params.envatoTokenEmail = '';
      }

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: params,
        success: function (response) {
          var parsedResponse = JSON.parse(response);
          if (parsedResponse.deactivated === true) {
            wdt_current_config.wdtPurchaseCodeStoreMasterDetail = 0;
            wdt_current_config.wdtActivatedMasterDetail = 0;
            $('#wdt-purchase-code-store-master-detail').val('');
            $('.wdt-purchase-code-store-master-detail-wrapper').show();
            $('.wdt-purchase-code-master-detail .wdt-security-massage-wrapper').addClass('hidden');
            $('#wdt-activate-plugin-master-detail').removeClass('btn-danger').addClass('btn-primary').html('<i class="wpdt-icon-check-circle-full"></i>Activate');
            $('.wdt-preload-layer').animateFadeOut();
            $('.wdt-purchase-code-master-detail').show();
          } else {
            wdtNotify(wpdatatables_settings_strings.error, wpdatatables_settings_strings.unable_to_deactivate_plugin, 'danger');
            $('#wdt-activate-plugin-master-detail').html('<i class="wpdt-icon-times-circle-full"></i>Deactivate');
          }
        }
      });
    }


    function handleActivationSettings() {
      if (wdt_current_config.wdtActivatedMasterDetail == 1) {
        $('#wdt-purchase-code-store-master-detail').val('');
        $('.wdt-purchase-code-store-master-detail-wrapper').hide();
        $('.wdt-purchase-code-master-detail .wdt-security-massage-wrapper').removeClass('hidden');
        $('#wdt-activate-plugin-master-detail').removeClass('btn-primary').addClass('btn-danger').html('<i class="wpdt-icon-times-circle-full"></i>Deactivate');
      } else {
        $('#wdt-purchase-code-store-master-detail').val('');
        $('.wdt-purchase-code-store-master-detail-wrapper').show();
        $('.wdt-purchase-code-master-detail .wdt-security-massage-wrapper').addClass('hidden');
        $('#wdt-activate-plugin-master-detail').removeClass('btn-danger').addClass('btn-primary').html('<i class="wpdt-icon-check-circle-full"></i>Activate');
      }
    }
  });
})(jQuery);
