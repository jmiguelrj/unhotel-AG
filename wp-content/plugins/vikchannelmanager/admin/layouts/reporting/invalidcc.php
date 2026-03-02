<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      Alessio Gaggii - E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4jconnect.com | https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Obtain vars from arguments received in the layout file.
 * 
 * @var string  $caller   who's calling this layout file.
 * @var array 	$booking  the booking record.
 */
extract($displayData);

// load context menu assets
VikBooking::getVboApplication()->loadContextMenuAssets();

// determine if reporting the credit card to the OTA is allowed and how
$ota_reporting_allowed = false;
$ota_reporting_invalid = false;
$ota_reporting_cancel  = false;

if (stripos($booking['channel'], 'booking.com') !== false) {
    // access the VikBooking history to check if the card was already reported as invalid
    $history = VikBooking::getBookingHistoryInstance($booking['id']);

    // events data validation callback
    $tn_data_callback = function($data) {
        return (is_object($data) && isset($data->type) && !strcasecmp($data->type, 'bcom_invalid_cc'));
    };

    // load all history events for reporting the credit card as invalid
    // to check if it was previously reported with/out cancellation
    foreach ((array) $history->getEventsWithData('CM', $tn_data_callback) as $ev_data) {
        if (!$ev_data) {
            continue;
        }
        // turn flag on for the cc being reported as invalid already
        $ota_reporting_invalid = true;
        // set flag for the cc already being reported as invalid + cancellation
        $ota_reporting_cancel  = $ota_reporting_cancel || !empty($ev_data->cancel_reservation);
    }

    // check if reporting the cc is allowed
    $ota_reporting_allowed = !$ota_reporting_cancel;

    if ($ota_reporting_allowed) {
        // check if we should load the VCM admin language file
        if (!strcasecmp($caller, 'vikbooking')) {
            $vcm_admin_lang_path = '';
            if (VBOPlatformDetection::isJoomla()) {
                $vcm_admin_lang_path = JPATH_ADMINISTRATOR;
            } elseif (defined('VIKCHANNELMANAGER_ADMIN_LANG')) {
                $vcm_admin_lang_path = VIKCHANNELMANAGER_ADMIN_LANG;
            } elseif (VBOPlatformDetection::isWordPress()) {
                $vcm_admin_lang_path = str_replace('vikbooking', 'vikchannelmanager', VIKBOOKING_ADMIN_LANG);
            }
            if ($vcm_admin_lang_path) {
                $lang = JFactory::getLanguage();
                $lang->load('com_vikchannelmanager', $vcm_admin_lang_path);
                if (VBOPlatformDetection::isWordPress() && defined('VIKCHANNELMANAGER_LIBRARIES')) {
                    $lang->attachHandler(VIKCHANNELMANAGER_LIBRARIES . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . 'admin.php', 'vikchannelmanager');
                }
            }
        }
    }

    if ($ota_reporting_allowed) {
        // reporting is allowed
        ?>
<div class="vcm-pcid-otareporting-wrapper">
    <button type="button" class="btn btn-danger vbo-context-menu-btn vcm-context-menu-breporting">
        <span class="vbo-context-menu-lbl"><?php echo JText::_('VCMBCOMREPORTINVCARD'); ?></span>
        <span class="vbo-context-menu-ico"><?php VikBookingIcons::e('sort-down'); ?></span>
    </button>
</div>
        <?php
    }
}
?>

<?php
if ($ota_reporting_allowed) {
    // declare the JS function to handle the OTA CC reporting action(s)
    ?>

<script type="text/javascript">

    function vcmDoOtaCCReporting(opts) {
        if (!confirm('<?php echo addslashes(JText::_('VCMBCOMREPORTINVCARDCONF')); ?>')) {
            return false;
        }

        // show inner modal
        VBOCore.displayModal({
            title:         '.....',
            dismiss_event: 'vcm-report-invcc-ajax-dismiss',
            loading_event: 'vcm-report-invcc-ajax-loading',
            loading_body:  '<?php VikBookingIcons::e('circle-notch', 'fa-spin fa-fw'); ?>',
        });

        // start loading
        VBOCore.emitEvent('vcm-report-invcc-ajax-loading');

        // build request data
        let rq_data = {
            otaid:      '<?php echo $booking['idorderota']; ?>',
            cancel_res: 0,
        };

        if (typeof opts === 'object') {
            rq_data = Object.assign(rq_data, opts);
        }

        // perform the request
        VBOCore.doAjax(
            "<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikchannelmanager&task=breporting.invalid_credit_card'); ?>",
            rq_data,
            (success) => {
                // remove inner modal
                VBOCore.emitEvent('vcm-report-invcc-ajax-dismiss');

                // hide main modal
                try {
                    jQuery('#jmodal-vbo-vcm-pcid').modal('hide');
                } catch(e) {
                    // do nothing
                }

                // reload (parent/current) window
                (window.parent || window).location.reload();
            },
            (error) => {
                // remove inner modal
                VBOCore.emitEvent('vcm-report-invcc-ajax-dismiss');

                // log and display the error
                console.error(error);
                alert(error.responseText);
            }
        );
    }

    jQuery(function() {
        // handle invalid credit card OTA reporting
        var vcm_reporting_btns = [];

        // always push the button to report the credit card as invalid
        vcm_reporting_btns.push({
            icon: '<?php echo VikBookingIcons::i('exclamation-triangle'); ?>',
            text: '<?php echo JHtml::_('esc_attr', JText::_('VCMBCOMREPORTINVCARD')); ?>',
            class: 'vbo-context-menu-entry-warning',
            separator: true,
            action: (root, config) => {
                // report invalid credit card
                vcmDoOtaCCReporting({
                    cancel_res: 0,
                });
            }
        });

    <?php
    if ($ota_reporting_invalid) {
        // the credit card was already reported as invalid, so we allow to cancel the booking
        ?>
        // push button to request the booking cancellation for invalid credit card
        vcm_reporting_btns.push({
            icon: '<?php echo VikBookingIcons::i('ban'); ?>',
            text: '<?php echo JHtml::_('esc_attr', JText::_('VCM_CANC_RES_INV_CC')); ?>',
            class: 'vbo-context-menu-entry-danger',
            separator: true,
            action: (root, config) => {
                // report invalid credit card and request booking cancellation
                vcmDoOtaCCReporting({
                    cancel_res: 1,
                });
            }
        });
        <?php
    }
    ?>

        // set up context menu
        jQuery.vboContextMenu.defaults.darkMode = '<?php echo !strcasecmp($caller, 'vikbooking') ? VikBooking::getAppearancePref() : VikChannelManager::getAppearancePref(); ?>';
        jQuery.vboContextMenu.defaults.class    = 'vbo-dropdown-cxmenu';

        // render context menu for the reporting action
        jQuery('.vcm-context-menu-breporting').vboContextMenu({
            placement: 'bottom-left',
            buttons: vcm_reporting_btns,
        });
    });

</script>

    <?php
}
