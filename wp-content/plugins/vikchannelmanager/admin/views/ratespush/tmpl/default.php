<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

$app = JFactory::getApplication();

$max_nodes = VikRequest::getInt('max_nodes', '', 'request');
$max_nodes = empty($max_nodes) || $max_nodes <= 0 ? 10 : $max_nodes;
$cookie = JFactory::getApplication()->input->cookie;
$vcm_app = new VikApplication(VersionListener::getID());

$now = getdate();
$def_fromdate = date('Y-m-d');
$def_todate = date('Y-m-d', mktime(0, 0, 0, $now['mon'], $now['mday'], ($now['year'] + 1)));
if ($this->tot_rooms > 10) {
    $def_todate = date('Y-m-d', mktime(0, 0, 0, ($now['mon'] + 6), $now['mday'], $now['year']));
}
$def_roomids = [];
$bulk_rates_cache = (array) VikChannelManager::getBulkRatesCache();
$bulk_rates_adv_params = (array) VikChannelManager::getBulkRatesAdvParams();
$currencysymb = VikChannelManager::getCurrencySymb();

$av_helper = VikBooking::getAvailabilityInstance(true);

$channels_name_map = [];
$channels_logo_map = [];
$rate_plans_list = [];
if (method_exists($av_helper, 'loadRatePlans')) {
    $rate_plans_list = $av_helper->loadRatePlans(true);
}

if (is_array($this->updforvcm)) {
    $def_fromdate = date('Y-m-d', $this->updforvcm['dfrom']);
    $def_todate = date('Y-m-d', $this->updforvcm['dto']);
    $def_roomids = $this->updforvcm['rooms'];
}

$vbo_app = VikBooking::getVboApplication();
$filters_supported = method_exists($vbo_app, 'renderElementsDropDown');
if (method_exists($vbo_app, 'loadContextMenuAssets')) {
    $vbo_app->loadContextMenuAssets();
}

// Booking.com Derived prices for the occupancy
$need_occupancy_derived = false;

// Airbnb skip LOS records
$airbnb_can_skip_los = false;

// Vrbo skip LOS records
$vrbo_can_skip_los = false;

// ignore rates table restrictions (for rate plan level restrictions) for other channels
$channels_ignore_rpl_restr = [];

// JS lang vars
JText::script('VCMRATESPUSHRMODINCR');
JText::script('VCMRATESPUSHRMODDECR');
JText::script('VCMNO');
JText::script('VCM_APPLY_ALL_ROOMS');
JText::script('VCM_EXCHRATECURR');
JText::script('VCM_CURRCONV_SUGG_PCENT');
JText::script('VCM_PLEASE_SELECT');
JText::script('VCM_CURRCONV_ERR_GENERIC');
JText::script('VCM_FILTERS');
JText::script('VCMRARADDLOSAPPLY');
JText::script('CANCEL');

?>
<script type="text/javascript">
/* Loading Overlay */
function vcmShowLoading() {
    jQuery(".vcm-loading-overlay").show();
}
function vcmStopLoading() {
    jQuery(".vcm-loading-overlay").hide();
}
/* Show loading when sending RAR_RQ to prevent double submit */
Joomla.submitbutton = function(task) {
    if (task == 'ratespushsubmit') {
        vcmShowLoading();
    }
    Joomla.submitform(task, document.adminForm);
}
/* Check objects are not empty */
function vcmFullObject(obj) {
    var jk;
    for (jk in obj) {
        return obj.hasOwnProperty(jk);
    }
}
var def_roomids = <?php echo json_encode($def_roomids); ?>;
var def_ratescache = <?php echo json_encode($bulk_rates_cache); ?>;
</script>

<div class="vcm-loading-overlay">
    <div class="vcm-loading-dot vcm-loading-dot1"></div>
    <div class="vcm-loading-dot vcm-loading-dot2"></div>
    <div class="vcm-loading-dot vcm-loading-dot3"></div>
    <div class="vcm-loading-dot vcm-loading-dot4"></div>
    <div class="vcm-loading-dot vcm-loading-dot5"></div>
</div>

<div class="vcm-avpush-info">
    <h3><?php echo JText::_('VCMRATESPUSHTITLE'); ?></h3>
    <p><?php echo JText::_('VCMRATESPUSHINFO'); ?></p>
</div>

<form action="index.php?option=com_vikchannelmanager" method="post" name="adminForm" id="adminForm">
    <div class="vcm-table-responsive vcm-table-rounded">
        <table class="vcmavpushtable vcm-table">
            <thead class="vcm-pushtable-head">
                <tr>
                    <th class="vcm-pushtable-head-th-left">
                        <?php
                        echo JText::_('VCMBCARCROOMTYPE');
                        if ($filters_supported) {
                            ?>
                        <div class="vcm-bulkaction-filters-wrap<?php echo ((array) $app->input->get('listingsfilter', [])) ? ' vcm-bulkaction-filters-active' : ''; ?>">
                            <span class="vcm-bulkaction-filters-show vcm-tooltip vcm-tooltip-top" data-tooltiptext="<?php echo $this->escape(JText::_('VCM_FILTERS')); ?>"><?php VikBookingIcons::e('filter'); ?></span>
                        </div>
                            <?php
                        }
                        ?>
                    </th>
                    <th class="vcm-pushtable-head-th-left"><?php echo JText::_('VCMAVPUSHHEADRANGEDT'); ?></th>
                    <th class="vcm-pushtable-head-th-center"><?php echo JText::_('VCMAVPUSHHEADWEBRPLAN'); ?></th>
                    <th class="vcm-pushtable-head-th-center"><?php echo JText::_('VCMAVPUSHHEADRATESMOD'); ?></th>
                    <th class="vcm-pushtable-head-th-left"><?php echo JText::_('VCMAVPUSHHEADCHRPTOUPD'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ($this->rows as $room) {
                if (!(count($room['channels']) > 0) || !(count($room['pricetypes']) > 0)) {
                    continue;
                }
                ?>
                <tr>
                    <td class="vcmratespush-td-room">
                        <div class="vcmavpush-roominfo">
                            <span class="vcm-oversight-roomunits"><?php echo $room['units']; ?></span> 
                            <span class="vcm-oversight-roomname" data-mainroomid="<?php echo $room['id']; ?>"><?php echo $room['name']; ?></span>
                            <input type="hidden" name="rooms[]" id="room_<?php echo $room['id']; ?>" value="<?php echo $room['id']; ?>" />
                        </div>
                        <div class="vcmavpush-roomstatus">
                            <button type="button" class="btn vcmavpush-togglerstatus" data-roomid="<?php echo $room['id']; ?>"><?php echo JText::_('VCMDESELECT'); ?></button>
                        </div>
                    </td>
                    <td class="vcmratespush-td-dates"><span class="vcmavpush-dpick-cont"><label for="from_<?php echo $room['id']; ?>"><?php echo JText::_('VCMFROMDATE'); ?></label> <input type="text" name="from[]" size="13" value="<?php echo $def_fromdate; ?>" class="vcmdatepickerav" id="from_<?php echo $room['id']; ?>" autocomplete="off" /></span> <span class="vcmavpush-dpick-cont"><label for="to_<?php echo $room['id']; ?>"><?php echo JText::_('VCMTODATE'); ?></label> <input type="text" name="to[]" size="13" value="<?php echo $def_todate; ?>" class="vcmdatepickerav" id="to_<?php echo $room['id']; ?>" autocomplete="off" /></span> <span class="vcmavpush-dpick-totdays" id="totdays_<?php echo $room['id']; ?>">x</span></td>
                    <td class="vcmratespush-td-rplans rplans<?php echo $room['id']; ?>">
                        <div class="vcmratespush-rplans-wrap">
                            <label for="pricetypes_<?php echo $room['id']; ?>"><?php echo JText::_('VCMRATESPUSHVBORPLAN'); ?></label>
                            <select name="pricetypes[]" id="pricetypes_<?php echo $room['id']; ?>" onchange="vcmSetDefaultRates('<?php echo $room['id']; ?>', this);">
                            <?php
                            foreach ($room['pricetypes'] as $krp => $pricetype) {
                                echo '<option value="'.$pricetype['idprice'].'" data-defcost="'.(array_key_exists('defaultrates', $room) && count($room['defaultrates']) > 0 && array_key_exists($krp, $room['defaultrates']) ? $room['defaultrates'][$krp] : '0.00').'"'.($krp <= 0 ? ' selected="selected"' : '').'>'.$pricetype['name'].'</option>'."\n";
                            }
                            ?>
                            </select>
                        </div>
                        <div class="vcmratespush-rplanscosts-wrap vcmratespush-rplanscosts-wrap-current">
                            <?php
                            $nowbasecost = array_key_exists('defaultrates', $room) && count($room['defaultrates']) > 0 ? (float)$room['defaultrates'][0] : 0;
                            $nowbasecost = ($nowbasecost - intval($nowbasecost) > 0) ? $nowbasecost : intval($nowbasecost);
                            ?>
                            <span class="vcmratespush-rplanscosts-lbl">
                                <?php echo JText::_('VCMRATESPUSHPERNIGHT'); ?>
                                <span class="vcmratespush-warndefrate vcmratespush-warndefrate-cur" id="cur_warndefrate_<?php echo $room['id']; ?>" style="display: none;"><?php echo $vcm_app->createPopover(array('title' => JText::_('VCMRATESPUSHPERNIGHT'), 'content' => JText::_('VCMRATESPUSHCNWARNLAUNCH'))); ?></span>
                            </span>
                            <span class="vcmratespush-rplanscosts-currency"><?php echo $currencysymb; ?></span>
                            <span class="vcmratespush-rplanscosts-amount" id="curtxt_defrates_<?php echo $room['id']; ?>"><?php echo $nowbasecost; ?></span>
                            <input type="hidden" name="defrates[]" id="cur_defrates_<?php echo $room['id']; ?>" value="<?php echo $nowbasecost; ?>" />
                        </div>
                        <div class="vcmratespush-rplanscosts-wrap vcmratespush-rplanscosts-wrap-hidden" style="display: none;">
                            <span class="vcmratespush-rplanscosts-lbl">
                                <?php echo JText::_('VCMRATESPUSHPERNIGHT'); ?>
                                <span class="vcmratespush-warndefrate" id="warndefrate_<?php echo $room['id']; ?>" style="display: none;"><?php echo $vcm_app->createPopover(array('title' => JText::_('VCMRATESPUSHPERNIGHT'), 'content' => JText::_('VCMRATESPUSHPERNIGHTWARN'))); ?></span>
                            </span>
                            <span class="vcmratespush-rplanscosts-currency"><?php echo $currencysymb; ?></span>
                            <input type="number" min="0" step="any" class="vcm-inp-custdefrates" name="custdefrates[]" id="defrates_<?php echo $room['id']; ?>" value="<?php echo array_key_exists('defaultrates', $room) && count($room['defaultrates']) > 0 ? $room['defaultrates'][0] : '0.00'; ?>" />
                        </div>
                    </td>
                    <td class="vcmratespush-td-rmods rmods<?php echo $room['id']; ?>">
                        <div class="vcmratespush-rmods-wrap">
                            <select name="rmods[]" id="rmods_<?php echo $room['id']; ?>" class="vcmratespush-selrmods" data-roomtotchannels="<?php echo count($room['channels']); ?>">
                                <option value="0"><?php echo JText::_('VCMRATESPUSHRMODNO'); ?></option>
                                <option value="1"><?php echo JText::_('VCMRATESPUSHRMODYES'); ?></option>
                            </select>
                            <div class="vcmratespush-rmods-cont" id="contrmods_<?php echo $room['id']; ?>">
                                <div class="vcmratespush-rmods-forall">
                                    <select name="rmodsop[]" id="rmodsop_<?php echo $room['id']; ?>">
                                        <option value="1"><?php echo JText::_('VCMRATESPUSHRMODINCR'); ?></option>
                                        <option value="0"><?php echo JText::_('VCMRATESPUSHRMODDECR'); ?></option>
                                    </select>
                                    <input type="number" min="0" step="any" name="rmodsamount[]" id="rmodsamount_<?php echo $room['id']; ?>" value="" />
                                    <select name="rmodsval[]" id="rmodsval_<?php echo $room['id']; ?>">
                                        <option value="1">%</option>
                                        <option value="0"><?php echo $currencysymb; ?></option>
                                    </select>
                                </div>
                            <?php
                            if (count($room['channels']) > 1) {
                                ?>
                                <div class="vcmratespush-rmods-forsingle">
                                    <span class="vcmratespush-define-alterations">
                                        <a href="JavaScript: void(0);" onclick="vcmManageAlterationsPerChannel('<?php echo $room['id']; ?>');"><?php VikBookingIcons::e('edit'); ?> <?php echo JText::_('VCM_DIFF_ALTER_PERCHANNEL'); ?></a>
                                    </span>
                                </div>
                                <?php
                            }
                            ?>
                            </div>
                        </div>
                    </td>
                    <td class="vcmratespush-td-channels">
                        <div class="vcm-custa-channelhead">
                            <button type="button" class="btn btn-light" onclick="vcmBulkActionToggleChannels('.check-<?php echo $room['id']; ?>', true);"><?php echo JText::_('VCMOSCHECKALL'); ?></button>
                            <button type="button" class="btn btn-light" onclick="vcmBulkActionToggleChannels('.check-<?php echo $room['id']; ?>', false);"><?php echo JText::_('VCMOSUNCHECKALL'); ?></button>
                        </div>
                        <div class="vcmavpush-channels-wrap">
                        <?php
                        foreach ($room['channels'] as $kc => $channel) {
                            $channel_pricing = !empty($channel['otapricing']) ? json_decode($channel['otapricing'], true) : [];
                            $orig_ch_name = $channel['channel'];
                            if ($channel['idchannel'] == VikChannelManagerConfig::AIRBNBAPI) {
                                $channel['channel'] = 'Airbnb';
                                $airbnb_can_skip_los = true;
                            } elseif ($channel['idchannel'] == VikChannelManagerConfig::GOOGLEHOTEL) {
                                $channel['channel'] = 'Google Hotel';
                            } elseif ($channel['idchannel'] == VikChannelManagerConfig::GOOGLEVR) {
                                $channel['channel'] = 'Google VR';
                            } elseif ($channel['idchannel'] == VikChannelManagerConfig::VRBOAPI) {
                                $channel['channel'] = 'Vrbo';
                                $vrbo_can_skip_los = true;
                            }
                            if ($channel['idchannel'] == VikChannelManagerConfig::BOOKING && !in_array(VikChannelManagerConfig::BOOKING, $channels_ignore_rpl_restr)) {
                                $channels_ignore_rpl_restr[] = VikChannelManagerConfig::BOOKING;
                            }
                            if ($channel['idchannel'] == VikChannelManagerConfig::EXPEDIA && !in_array(VikChannelManagerConfig::EXPEDIA, $channels_ignore_rpl_restr)) {
                                $channels_ignore_rpl_restr[] = VikChannelManagerConfig::EXPEDIA;
                            }

                            // push channel details if not set
                            if (!isset($channels_name_map[$channel['idchannel']])) {
                                // set channel name
                                $channels_name_map[$channel['idchannel']] = ucwords($channel['channel']);
                                // get channel details
                                $channelData = VikChannelManager::getChannel($channel['idchannel']);
                                $channelLogo = VikChannelManager::getLogosInstance($channelData['name'] ?? $channel['idchannel']);
                                // set channel logo
                                $channels_logo_map[$channel['idchannel']] = $channelLogo->getTinyLogoURL();
                            }
                            ?>
                            <div class="vcmratespush-channel-wrap" data-roomid="<?php echo $room['id']; ?>">
                                <span class="vbotasp <?php echo $orig_ch_name; ?> vcmavpush-channel-cont vcmavpush-channel-cont-deactive" data-chname="<?php echo ucwords($channel['channel']); ?>" data-chkey="<?php echo $channel['idchannel']; ?>">
                                    <label for="ch<?php echo $room['id'] . '-' . $channel['idchannel']; ?>"><?php echo ucwords($channel['channel']); ?></label>
                                    <input type="checkbox" class="vcm-avpush-checkbox check-<?php echo $room['id']; ?>" name="channels[<?php echo $room['id']; ?>][]" id="ch<?php echo $room['id'] . '-' . $channel['idchannel']; ?>" value="<?php echo $channel['idchannel']; ?>" onchange="vcmBulkActionChkboxToggle(this);" />
                                </span>
                                <div class="vcmratespush-channel-rateplans chrplans<?php echo $room['id']; ?>" id="pricingch<?php echo $room['id'] . '-' . $channel['idchannel']; ?>">
                            <?php
                            /**
                             * We need to reorder the rate plans for Expedia
                             * to have the Derived ones at the bottom of the list.
                             * 
                             * @since   1.6.13
                             */
                            if ($channel['idchannel'] == VikChannelManagerConfig::EXPEDIA) {
                                $channel_pricing = VikChannelManager::sortExpediaChannelPricing($channel_pricing);
                            } else {
                                $channel_pricing = VikChannelManager::sortGenericChannelPricing($channel_pricing);
                            }
                            foreach ($channel_pricing as $chpk => $rateplans) {
                                if ($chpk != 'RatePlan') {
                                    continue;
                                }
                                ?>
                                    <select name="rplans[<?php echo $room['id']; ?>][<?php echo $channel['idchannel']; ?>]" id="rplan_pricingch<?php echo $room['id'] . '-' . $channel['idchannel']; ?>">
                                <?php
                                foreach ($rateplans as $rpid => $rateplan) {
                                    if ($channel['idchannel'] == VikChannelManagerConfig::AGODA || $channel['idchannel'] == VikChannelManagerConfig::YCS50) {
                                        echo '<option value="'.$rateplan['id'].'"'.(array_key_exists('vcm_default', $rateplan) ? ' selected="selected"' : '').'>'.$rateplan['name'].' '.(array_key_exists('rate_type', $rateplan) ? '('.$rateplan['rate_type'].')' : '').'</option>'."\n";
                                    } elseif ($channel['idchannel'] == VikChannelManagerConfig::EXPEDIA) {
                                        if (!isset($expedia_derived_group) && stripos($rateplan['rateAcquisitionType'], 'Derived') !== false) {
                                            $expedia_derived_group = 1;
                                            echo '<optgroup label="'.JText::_('VCMDERIVEDRATEPLANS').'">';
                                        }
                                        echo '<option value="'.$rateplan['id'].'"'.(array_key_exists('pricingModel', $rateplan) ? ' title="'.$rateplan['pricingModel'].'"' : '').(array_key_exists('vcm_default', $rateplan) ? ' selected="selected"' : '').'>'.$rateplan['name'].' '.(array_key_exists('distributionModel', $rateplan) ? '('.$rateplan['distributionModel'].')' : '').'</option>'."\n";
                                    } elseif ($channel['idchannel'] == VikChannelManagerConfig::BOOKING) {
                                        echo '<option value="'.$rateplan['id'].'" title="'.(array_key_exists('policy', $rateplan) ? 'Policy: '.$rateplan['policy'].', ' : '').(array_key_exists('max_persons', $rateplan) ? 'Max Persons: '.$rateplan['max_persons'].(array_key_exists('is_child_rate', $rateplan) && intval($rateplan['is_child_rate']) == 1 ? ' (Derived Rate)' : '') : '').'"'.(strtolower($rateplan['name']) == 'standard rate' || array_key_exists('vcm_default', $rateplan) ? ' selected="selected"' : '').'>'.$rateplan['name'].'</option>'."\n";
                                    } elseif ($channel['idchannel'] == VikChannelManagerConfig::DESPEGAR) {
                                        echo '<option value="'.$rateplan['id'].'" title="'.(array_key_exists('CurrencyCode', $rateplan) ? 'Currency: '.$rateplan['CurrencyCode'].', ' : '').(array_key_exists('ChargeTypeCode', $rateplan) ? 'Pricing: '.((int)$rateplan['ChargeTypeCode'] == 21 ? 'PerPersonPerNight' : 'PerRoomPerNight') : '').'"'.(array_key_exists('vcm_default', $rateplan) ? ' selected="selected"' : '').'>'.$rateplan['name'].'</option>'."\n";
                                    } elseif ($channel['idchannel'] == VikChannelManagerConfig::OTELZ) {
                                        echo '<option value="'.$rateplan['id'].'" title="ID '.$rateplan['id'].'"'.(array_key_exists('vcm_default', $rateplan) ? ' selected="selected"' : '').'>'.$rateplan['name'].'</option>'."\n";
                                    } elseif ($channel['idchannel'] == VikChannelManagerConfig::GARDAPASS) {
                                        echo '<option value="'.$rateplan['id'].'" title="ID '.$rateplan['id'].'"'.(array_key_exists('vcm_default', $rateplan) ? ' selected="selected"' : '').'>'.$rateplan['name'].'</option>'."\n";
                                    } elseif ($channel['idchannel'] == VikChannelManagerConfig::BEDANDBREAKFASTIT) {
                                        echo '<option value="-1">Standard</option>'."\n";
                                    } elseif ($channel['idchannel'] == VikChannelManagerConfig::BEDANDBREAKFASTEU) {
                                        echo '<option value="-1">Standard</option>'."\n";
                                    } elseif ($channel['idchannel'] == VikChannelManagerConfig::BEDANDBREAKFASTNL) {
                                        echo '<option value="-1">Standard</option>'."\n";
                                    } elseif ($channel['idchannel'] == VikChannelManagerConfig::FERATEL) {
                                        echo '<option value="'.$rateplan['id'].'" title="'.(array_key_exists('price_rule', $rateplan) ? 'Price Rule: '.$rateplan['price_rule'] : '').'"'.(array_key_exists('vcm_default', $rateplan) ? ' selected="selected"' : '').'>'.$rateplan['name'].'</option>'."\n";
                                    } elseif ($channel['idchannel'] == VikChannelManagerConfig::PITCHUP) {
                                        echo '<option value="'.$rateplan['id'].'" title="'.(array_key_exists('status', $rateplan) ? 'Status: '.$rateplan['status'] : '').'"'.(array_key_exists('vcm_default', $rateplan) ? ' selected="selected"' : '').'>'.$rateplan['name'].'</option>'."\n";
                                    } elseif ($channel['idchannel'] == VikChannelManagerConfig::HOSTELWORLD) {
                                        $title = '';
                                        if (!empty($rateplan['default'])) {
                                            $title .= 'Default: ' . $rateplan['default'];
                                        }
                                        if (!empty($rateplan['active'])) {
                                            $title .= ' Active: ' . $rateplan['active'];
                                        }
                                        if (!empty($rateplan['currency'])) {
                                            $title .= ' Currency: ' . $rateplan['currency'];
                                        }
                                        echo '<option value="'.$rateplan['id'].'"'.(!empty($title) ? ' title="' . $title . '"' : '').(array_key_exists('vcm_default', $rateplan) ? ' selected="selected"' : '').'>'.$rateplan['name'].'</option>'."\n";
                                    } elseif (isset($rateplan['id']) && isset($rateplan['name'])) {
                                        /**
                                         * Default statement for new channels or for those that
                                         * do not need to display any particular information.
                                         * 
                                         * @since   1.6.22
                                         */
                                        echo '<option value="'.$rateplan['id'].'"'.(array_key_exists('vcm_default', $rateplan) ? ' selected="selected"' : '').'>'.$rateplan['name'].'</option>'."\n";
                                    }
                                }
                                if (isset($expedia_derived_group)) {
                                    unset($expedia_derived_group);
                                    echo '</optgroup>';
                                }
                                ?>
                                    </select>
                                <?php
                            }
                            if ($channel['idchannel'] == VikChannelManagerConfig::BOOKING) {
                                $need_occupancy_derived = true;
                                $cookie_ariprmodel = $cookie->get('vcmAriPrModel'.$channel['idchannel'], '', 'string');
                                /**
                                 * To avoid confusion and errors, we hide this drop down menu for the pricing model of Booking.com in case
                                 * no selections were ever made before, or if an existing selection for the Default Pricing is available.
                                 * The same drop down menus will be displayed by clicking on the Advanced Options button.
                                 * 
                                 * @since   1.6.13
                                 */
                                ?>
                                    <select name="rplanarimode[<?php echo $room['id']; ?>][<?php echo $channel['idchannel']; ?>]" id="rplanarimode<?php echo $room['id'] . '-' . $channel['idchannel']; ?>" class="vcmratespush-channel-subsel"<?php echo empty($cookie_ariprmodel) || $cookie_ariprmodel == 'person' ? ' style="display: none;"' : ''; ?>>
                                        <option value="person" title="<?php echo addslashes(JText::_('VCMRARBCOMPERSONPMODELTIP')); ?>"<?php echo $cookie_ariprmodel == 'person' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMRARBCOMPERSONPMODEL'); ?></option>
                                        <option value="los" title="<?php echo addslashes(JText::_('VCMRARBCOMLOSPMODELTIP')); ?>"<?php echo $cookie_ariprmodel == 'los' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VCMRARBCOMLOSPMODEL'); ?></option>
                                        <option value="any"><?php echo JText::_('VCMRARBCOMANYPMODEL'); ?></option>
                                    </select>
                                <?php
                            }
                            ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        </div>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>

    <div class="vcm-ratespush-bottom">
        <div class="vcm-ratespush-advanced-left">
            <button type="button" id="vcm-ratespush-toggleall" class="btn btn-primary"><i class="vboicn-switch"></i><?php echo JText::_('VCMRATESPUSHTOGGLEALL'); ?></button>
        </div>

        <div class="vcm-ratespush-advanced-right">
            <div class="vcm-ratespush-advanced-right-toggle">
                <button type="button" id="vcm-ratespush-advancedopt" class="btn btn-primary"><i class="vboicn-cog"></i><?php echo JText::_('VCMRATESPUSHADVOPT'); ?></button>
            </div>
            <div class="vcm-ratespush-advanced-right-wrap" style="display: none;">
                <div class="vcm-ratespush-advanced-boxes">
                <?php
                /**
                 * We allow to choose to increase the default occupancy pricing rules
                 * by the same amount as the basic costs of the rooms are increased.
                 * By default this function is turned off for the occupancy pricing.
                 * Of course this will work only in case the occupancy pricing rules
                 * are defined with absolute values, not with percent values.
                 * 
                 * @since   1.6.17
                 */
                if ($this->occupancyrules > 0) {
                    ?>
                    <div class="vcm-ratespush-advdata">
                        <label for="alter-occ-rules"><?php echo JText::_('VCMRPUSHALTEROCCRULES'); ?></label> 
                        <span class="vcm-popover-colored"><?php echo $vcm_app->createPopover(array('title' => JText::_('VCMRPUSHALTEROCCRULES'), 'content' => JText::_('VCMRPUSHALTEROCCRULESHELP'), 'placement' => 'left')); ?></span>
                        <input type="checkbox" name="alter_occrules" id="alter-occ-rules" value="1" <?php echo isset($bulk_rates_adv_params['alter_occrules']) && intval($bulk_rates_adv_params['alter_occrules']) > 0 ? ' checked="checked"' : ''; ?>/>
                    </div>
                    <?php
                }
                //
                if ($need_occupancy_derived === true) {
                    // Booking.com is present if we enter here

                    /**
                     * We allow to submit the information about the min/max advance reservation time
                     * that Booking.com can apply at rate plan level during a RAR request.
                     * 
                     * @since   1.8.3
                     */
                    ?>
                    <div class="vcm-ratespush-advdata">
                        <label for="min-max-adv-res"><?php echo JText::_('VCMRPUSHMINMAXADVRES'); ?></label> 
                        <span class="vcm-popover-colored"><?php echo $vcm_app->createPopover(array('title' => JText::_('VCMRPUSHMINMAXADVRES'), 'content' => JText::_('VCMRPUSHMINMAXADVRESHELP'), 'placement' => 'left')); ?></span>
                        <input type="checkbox" name="min_max_adv_res" id="min-max-adv-res" value="1" <?php echo isset($bulk_rates_adv_params['min_max_adv_res']) && intval($bulk_rates_adv_params['min_max_adv_res']) > 0 ? ' checked="checked"' : ''; ?>/>
                    </div>
                    <?php

                    // occupancy derived rules
                    ?>
                    <div class="vcm-ratespush-advdata">
                        <label for="bcom-derived-occ-rules"><?php echo JText::_('VCMRPUSHBCOMDERIVEDOCCRULES'); ?></label> 
                        <input type="checkbox" name="bcom_derocc" id="bcom-derived-occ-rules" value="1" <?php echo (array_key_exists('bcom_derocc', $bulk_rates_adv_params) && intval($bulk_rates_adv_params['bcom_derocc']) > 0) || (!array_key_exists('bcom_derocc', $bulk_rates_adv_params) && $this->occupancyrules > 0) ? ' checked="checked"' : ''; ?>/>
                    </div>
                    <?php
                }

                /**
                 * With Airbnb we allow to ignore LOS records.
                 * 
                 * @since   1.8.6
                 * @since   1.9.12  added support to "Airbnb ignore rates-table-restrictions".
                 * @since   1.9.16  added support to "Airbnb no OBP" (hidden setting).
                 */
                if ($airbnb_can_skip_los) {
                    ?>
                    <div class="vcm-ratespush-advdata">
                        <label for="airbnb-no-los"><?php echo JText::_('VCM_AIRBNB_NO_LOS'); ?></label> 
                        <span class="vcm-popover-colored"><?php echo $vcm_app->createPopover(array('title' => JText::_('VCM_AIRBNB_NO_LOS'), 'content' => JText::_('VCM_AIRBNB_NO_LOS_HELP'), 'placement' => 'left')); ?></span>
                        <input type="checkbox" name="airbnb_no_los" id="airbnb-no-los" value="1" <?php echo !empty($bulk_rates_adv_params['airbnb_no_los']) ? ' checked="checked"' : ''; ?>/>
                    </div>
                    <div class="vcm-ratespush-advdata" style="<?php echo empty($bulk_rates_adv_params['airbnb_no_obp']) ? 'display: none;' : ''; ?>">
                        <label for="airbnb-no-obp">Airbnb no OBP</label> 
                        <input type="checkbox" name="airbnb_no_obp" id="airbnb-no-obp" value="1" <?php echo !empty($bulk_rates_adv_params['airbnb_no_obp']) ? ' checked="checked"' : ''; ?>/>
                    </div>
                    <div class="vcm-ratespush-advdata">
                        <label for="airbnb-no-ratestable-restr"><?php echo JText::_('VCM_AIRBNB_NO_RATES_TABLE_RESTR'); ?></label> 
                        <span class="vcm-popover-colored"><?php echo $vcm_app->createPopover(array('title' => JText::_('VCM_AIRBNB_NO_RATES_TABLE_RESTR'), 'content' => JText::_('VCM_AIRBNB_NO_RATES_TABLE_RESTR_HELP'), 'placement' => 'left')); ?></span>
                        <input type="checkbox" name="airbnb_no_ratestable_restr" id="airbnb-no-ratestable-restr" value="1" <?php echo !empty($bulk_rates_adv_params['airbnb_no_ratestable_restr']) ? ' checked="checked"' : ''; ?>/>
                    </div>
                    <?php
                }

                /**
                 * With Vrbo we allow to ignore LOS records.
                 * 
                 * @since   1.9.5
                 */
                if ($vrbo_can_skip_los) {
                    ?>
                    <div class="vcm-ratespush-advdata">
                        <label for="vrbo-no-los"><?php echo preg_replace('/airbnb/i', 'Vrbo', JText::_('VCM_AIRBNB_NO_LOS')); ?></label> 
                        <span class="vcm-popover-colored"><?php echo $vcm_app->createPopover(array('title' => preg_replace('/airbnb/i', 'Vrbo', JText::_('VCM_AIRBNB_NO_LOS')), 'content' => preg_replace('/airbnb/i', 'Vrbo', JText::_('VCM_AIRBNB_NO_LOS_HELP')), 'placement' => 'left')); ?></span>
                        <input type="checkbox" name="vrbo_no_los" id="vrbo-no-los" value="1" <?php echo !empty($bulk_rates_adv_params['vrbo_no_los']) ? ' checked="checked"' : ''; ?>/>
                    </div>
                    <?php
                }

                /**
                 * Other channels can ignore rate-plan level restrictions defined in the Rates Table.
                 * 
                 * @since   1.9.16
                 */
                if (in_array(VikChannelManagerConfig::BOOKING, $channels_ignore_rpl_restr)) {
                    ?>
                    <div class="vcm-ratespush-advdata">
                        <label for="vrbo-no-los"><?php echo preg_replace('/airbnb/i', 'Booking.com', JText::_('VCM_AIRBNB_NO_RATES_TABLE_RESTR')); ?></label> 
                        <span class="vcm-popover-colored"><?php echo $vcm_app->createPopover(array('title' => preg_replace('/airbnb/i', 'Booking.com', JText::_('VCM_AIRBNB_NO_RATES_TABLE_RESTR')), 'content' => preg_replace('/airbnb/i', 'Booking.com', JText::_('VCM_AIRBNB_NO_RATES_TABLE_RESTR_HELP')), 'placement' => 'left')); ?></span>
                        <input type="checkbox" name="bdc_no_ratestable_restr" id="bdc-no-ratestable-restr" value="1" <?php echo !empty($bulk_rates_adv_params['bdc_no_ratestable_restr']) ? ' checked="checked"' : ''; ?>/>
                    </div>
                    <?php
                }
                if (in_array(VikChannelManagerConfig::EXPEDIA, $channels_ignore_rpl_restr)) {
                    ?>
                    <div class="vcm-ratespush-advdata">
                        <label for="vrbo-no-los"><?php echo preg_replace('/airbnb/i', 'Expedia', JText::_('VCM_AIRBNB_NO_RATES_TABLE_RESTR')); ?></label> 
                        <span class="vcm-popover-colored"><?php echo $vcm_app->createPopover(array('title' => preg_replace('/airbnb/i', 'Expedia', JText::_('VCM_AIRBNB_NO_RATES_TABLE_RESTR')), 'content' => preg_replace('/airbnb/i', 'Expedia', JText::_('VCM_AIRBNB_NO_RATES_TABLE_RESTR_HELP')), 'placement' => 'left')); ?></span>
                        <input type="checkbox" name="expedia_no_ratestable_restr" id="expedia-no-ratestable-restr" value="1" <?php echo !empty($bulk_rates_adv_params['expedia_no_ratestable_restr']) ? ' checked="checked"' : ''; ?>/>
                    </div>
                    <?php
                }

                    /**
                     * We store another advanced setting for the bulk rates cache, which
                     * serves to determine whether currency conversion should be used.
                     * This is useful to apply conversion on additional elements, such
                     * as the children rates for various channels (Booking.com, Google, VRBO).
                     * 
                     * @since   1.8.16
                     */
                    $has_currency_conv_rate = !empty($bulk_rates_adv_params['currency_conversion_rate']);
                    ?>
                    <div class="vcm-ratespush-advdata vcm-do-curr-conv"<?php echo !$has_currency_conv_rate ? ' style="display:none;"' : ''; ?>>
                        <label for="vcm-do-curr-conv"><?php echo JText::_('VCM_APPLY_CURRENCY_CONVERSION') . ($has_currency_conv_rate ? ' (' . $bulk_rates_adv_params['currency_conversion_rate'] .  '%)' : ''); ?></label> 
                        <input type="checkbox" name="currency_conversion_rate" id="vcm-do-curr-conv" value="<?php echo $has_currency_conv_rate ? $bulk_rates_adv_params['currency_conversion_rate'] : '0'; ?>" <?php echo !$has_currency_conv_rate ? 'disabled="disabled"' : 'checked="checked"'; ?>/>
                    </div>

                    <div class="vcm-ratespush-advdata vcm-ratespush-advdata-elem">
                        <button type="button" class="btn btn-success" onclick="vcmCurrencyConvHelper();"><?php VikBookingIcons::e('funnel-dollar'); ?> <?php echo JText::_('VCM_CURRENCY_CONVERTER'); ?></button>
                    </div>
                </div>
            <?php
            if ($bulk_rates_cache) {
                ?>
                <div class="vcm-ratespush-advanced-elem vcm-ratespush-advanced-elem-last vcm-ratespush-editcache" style="display: none;">
                    <button type="button" class="btn btn-primary vcm-ratespush-editcache-btn"><?php VikBookingIcons::e('edit'); ?> <?php echo JText::_('VCM_EDIT_BULK_RATES_CACHE'); ?></a>
                </div>
                <?php
            }
            ?>
                <div class="vcm-ratespush-advanced-elem vcm-ratespush-advanced-elem-last">
                    <a id="vcm-ratespush-removecache" class="btn btn-danger hasTooltip" title="<?php echo addslashes(JText::_('VCMRATESPUSHRMRATESCACHEHELP')); ?>" href="index.php?option=com_vikchannelmanager&amp;task=ratespush&amp;rmcache=1"><i class="vboicn-cross"></i><?php echo JText::_('VCMRATESPUSHRMRATESCACHE'); ?></a>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="max_nodes" value="<?php echo $max_nodes; ?>" />
    <input type="hidden" name="task" value="ratespush" />
<?php
foreach ((array) $app->input->get('listingsfilter', []) as $flisting_id) {
    ?>
    <input type="hidden" name="listingsfilter[]" value="<?php echo (int) $flisting_id; ?>" />
    <?php
}
if ($app->input->getBool('e4j_debug')) {
    echo '<input type="hidden" name="e4j_debug" value="1" />'."\n";
}
?>
    <?php echo '<br/>'.$this->navbut; ?>
</form>

<?php
if ($filters_supported) {
    ?>
<div class="vcm-bulkaction-filters-helper" style="display: none;">
    <div class="vcm-bulkaction-filters-container">
        <form action="index.php?option=com_vikchannelmanager&task=ratespush" method="post" class="vcm-bulkaction-filters-form">
            <div class="vcm-admin-container vcm-admin-container-full vcm-admin-container-compact">
                <div class="vcm-params-wrap">
                    <div class="vcm-params-container">
                        <div class="vcm-params-block">

                            <div class="vcm-param-container">
                                <div class="vcm-param-label"><?php echo JText::_('VCM_FILTER_LISTINGS'); ?></div>
                                <div class="vcm-param-setting">
                                <?php
                                /**
                                 * Display listings dropdown.
                                 * 
                                 * @since   1.9.6
                                 */
                                echo $vbo_app->renderElementsDropDown([
                                    'id'              => 'listingsfilter',
                                    'elements'        => 'listings',
                                    'load_categories' => 1,
                                    'categories_lbl'  => JText::_('VCM_FILTER_BY_CATEGORY'),
                                    'placeholder'     => JText::_('VCM_FILTER_LISTINGS'),
                                    'attributes'      => [
                                        'name'     => 'listingsfilter[]',
                                        'multiple' => 'multiple',
                                    ],
                                    'selected_values' => (array) $app->input->get('listingsfilter', []),
                                ]);
                                ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
    <?php
}
?>

<div class="vcm-info-overlay-block vcm-info-overlay-diffalterch">
    <div class="vcm-info-overlay-content">
        <h3><?php echo JText::_('VCM_DIFF_ALTER_PERCHANNEL'); ?></h3>
        <div class="vcm-modal-overlay-content-body-scroll vcm-ratespush-chalterations-modal" data-roomid="" data-rplanid=""></div>
        <div class="vcm-info-overlay-footer vcm-ratespush-chalterations-modal-footer">
            <div class="vcm-info-overlay-footer-left">
                <button type="button" class="btn btn-danger" onclick="vcmCancelAlterationsPerChannel();"><?php VikBookingIcons::e('trash'); ?> <?php echo JText::_('REMOVE'); ?></button>
            </div>
            <div class="vcm-info-overlay-footer-right">
                <button type="button" class="btn btn-light" onclick="vcmCloseModal();"><?php echo JText::_('CANCEL'); ?></button>
                <button type="button" class="btn btn-success" onclick="vcmApplyAlterationsPerChannel();"><?php echo JText::_('VCMRARADDLOSAPPLY'); ?></button>
            </div>
        </div>
    </div>
</div>

<div class="vcm-info-overlay-block vcm-info-overlay-currconverter">
    <div class="vcm-info-overlay-content">
        <h3><?php VikBookingIcons::e('funnel-dollar'); ?> <?php echo JText::_('VCM_CURRENCY_CONVERTER'); ?></h3>
        <div class="vcm-info-overlay-scroll">
            <div class="vcm-ratespush-currconverter-modal">
                <?php
                // get the instance of the VCM currency converter helper class
                $vcm_currency_conv = VikChannelManager::getCurrencyConverterInstance();
                // get the website's default currency
                $def_currency = $vcm_currency_conv->getFromCurrency();
                $all_currency = $vcm_currency_conv->getAllCurrencies();
                ?>
                <div class="vcm-ratespush-currconverter-top">
                    <div class="vcm-ratespush-currconverter-top-elem">
                        <span class="vcm-ratespush-currconverter-lbl"><?php echo JText::_('VCM_DEFCURRENCY'); ?></span>
                        <span class="vcm-ratespush-currconverter-val">
                            <span class="badge badge-info"><?php echo $def_currency; ?></span>
                            <input type="number" id="vcm-defrate-conv" min="0" step="any" value="" />
                        </span>
                        <input type="hidden" id="vcm-defcurrency" value="<?php echo $def_currency; ?>" />
                    </div>
                    <div class="vcm-ratespush-currconverter-top-elem">
                        <span class="vcm-ratespush-currconverter-lbl"><?php echo JText::_('VCM_CONVTOCURRENCY'); ?></span>
                        <span class="vcm-ratespush-currconverter-val">
                            <select id="vcm-convertocurrency">
                                <option></option>
                            <?php
                            foreach ($all_currency as $curr_code => $curr_name) {
                                ?>
                                <option value="<?php echo $curr_code; ?>"><?php echo $curr_name; ?></option>
                                <?php
                            }
                            ?>
                            </select>
                        </span>
                    </div>
                    <div class="vcm-ratespush-currconverter-top-elem vcm-ratespush-currconverter-top-elem-submit">
                        <span class="vcm-ratespush-currconverter-lbl">
                            <button type="button" class="btn vcm-config-btn" onclick="vcmDoConversionRate();"><?php VikBookingIcons::e('chart-bar'); ?> <?php echo JText::_('VCM_GETCONVRATE'); ?></button>
                        </span>
                    </div>
                </div>
                <input type="hidden" id="vcm-pcent-alteration" value="" />
                <input type="hidden" id="vcm-pcent-alteration-oper" value="" />
                <div class="vcm-ratespush-currconverter-result"></div>
            </div>
        </div>
        <div class="vcm-info-overlay-footer">
            <div class="vcm-info-overlay-footer-left">
                <button type="button" class="btn btn-light" onclick="vcmCloseModal();"><?php echo JText::_('CANCEL'); ?></button>
            </div>
            <div class="vcm-info-overlay-footer-right">
                <button type="button" class="btn btn-success" onclick="vcmApplyConvAlterations();"><?php echo JText::_('VCMRARADDLOSAPPLY'); ?></button>
            </div>
        </div>
    </div>
</div>

<div class="vcm-edit-bulk-rates-cache-helper" style="display: none;">
    <div class="vcm-edit-bulk-rates-cache-wrap">
        <div class="vcm-admin-container vcm-admin-container-full vcm-admin-container-compact">
            <div class="vcm-params-wrap">
                <div class="vcm-params-container">
                <?php
                /**
                 * Sort bulk rates cache rooms data according to rooms displayed in the current page.
                 */
                $displayedRids = array_column($this->rows, 'id');
                uksort($bulk_rates_cache, function($a, $b) use ($displayedRids) {
                    if (in_array($a, $displayedRids) && !in_array($b, $displayedRids)) {
                        return -1;
                    }
                    if (!in_array($a, $displayedRids) && in_array($b, $displayedRids)) {
                        return 1;
                    }
                    return 0;
                });

                // display the current bulk rates cache information, if any
                foreach ($bulk_rates_cache as $roomId => $roomRates) {
                    $displayRoomName = ($this->global_rooms_info[$roomId]['name'] ?? '') ?: sprintf('ID %d', (int) $roomId);
                    ?>
                    <div class="vcm-params-block vcm-params-block-style-group">
                        <div class="vcm-param-container vcm-param-title">
                            <div class="vcm-param-setting">
                                <strong class="vcm-edit-bulk-rates-listing-name"><?php echo $displayRoomName; ?></strong>
                            <?php
                            if (count($roomRates) > 1) {
                                // display full-sync button
                                ?>
                                <span class="vcm-param-setting-right">
                                    <button type="button" class="btn btn-small btn-primary vcm-context-menu-btn vcm-roomrates-full-sync" data-room-id="<?php echo $roomId; ?>">
                                        <span class="vcm-context-menu-lbl"><?php VikBookingIcons::e('sync'); ?> <?php echo JText::_('VCM_FULL_SYNC'); ?></span>
                                        <span class="vcm-context-menu-ico"><?php VikBookingIcons::e('ellipsis-v'); ?></span>
                                    </button>
                                </span>
                                <?php
                            }
                            ?>
                            </div>
                        </div>
                    <?php
                    foreach ($roomRates as $rateId => $rateData) {
                        $rateInfo = VikBooking::getPriceInfo($rateId);
                        ?>
                        <div class="vcm-param-container">
                            <div class="vcm-param-label">
                                <?php VikBookingIcons::e('tag'); ?>
                                <strong><?php echo $rateInfo['name'] ?? $rateId; ?></strong>
                            </div>
                            <div class="vcm-param-setting">
                                <div class="vcm-flexlist-choices-wrap">
                                <?php
                                foreach ((array) ($rateData['channels'] ?? []) as $channelId) {
                                    $channelData = VikChannelManager::getChannel($channelId);
                                    $channelLogo = VikChannelManager::getLogosInstance($channelData['name'] ?? $channelId);
                                    $logoUrl = $channelLogo->getTinyLogoURL();
                                    $displayChannelName = ucfirst((string) (($channelData['name'] ?? '') ?: $channelId));
                                    if ($channelId == VikChannelManagerConfig::AIRBNBAPI) {
                                        $displayChannelName = 'Airbnb';
                                    } elseif ($channelId == VikChannelManagerConfig::GOOGLEHOTEL) {
                                        $displayChannelName = 'Google Hotel';
                                    } elseif ($channelId == VikChannelManagerConfig::GOOGLEVR) {
                                        $displayChannelName = 'Google VR';
                                    } elseif ($channelId == VikChannelManagerConfig::VRBOAPI) {
                                        $displayChannelName = 'Vrbo';
                                    }
                                    ?>
                                    <div class="vcm-flexlist-choice">
                                        <span class="vcm-flexlist-img"><?php echo $logoUrl ? '<img src="' . $logoUrl . '" />' : ''; ?></span>
                                        <span class="vcm-flexlist-lbl"><?php echo $displayChannelName; ?></span>
                                        <span class="vcm-flexlist-close" data-room-id="<?php echo $roomId; ?>" data-rate-id="<?php echo $rateId; ?>" data-channel-id="<?php echo $channelId; ?>"><?php VikBookingIcons::e('times'); ?></span>
                                    </div>
                                    <?php
                                }
                                ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    </div>
                    <?php
                }
                ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // global var for all modal windows
    var vcm_overlay_on = false;

    // channel names and logos map
    const channel_names = <?php echo json_encode($channels_name_map); ?>;
    const channel_logos = <?php echo json_encode($channels_logo_map); ?>;
    const rate_plans_list = <?php echo json_encode($rate_plans_list); ?>;

    /**
     * Currency converter helper methods.
     */
    function vcmCurrencyConvHelper() {
        // grab the first default cost per night for the first room (to be used as an example)
        var first_cost = jQuery('input[name="defrates[]"]').first().val();
        first_cost = !first_cost ? 100 : first_cost;
        // populate dummy cost
        jQuery('#vcm-defrate-conv').val(first_cost);
        // show modal
        jQuery(".vcm-info-overlay-currconverter").fadeIn();
        vcm_overlay_on = true;
    }

    function vcmDoConversionRate() {
        var from_currency = jQuery('#vcm-defcurrency').val();
        var to_currency = jQuery('#vcm-convertocurrency').val();
        if (!from_currency || !from_currency.length || !to_currency || !to_currency.length) {
            alert(Joomla.JText._('VCM_PLEASE_SELECT'));
            return false;
        }

        // make the currency converter result empty
        jQuery('.vcm-ratespush-currconverter-result').html('');

        // reset any previously calculated percent alteration
        jQuery('#vcm-pcent-alteration').val('');

        // grab the default cost per night (to be used as an example)
        var first_cost = jQuery('#vcm-defrate-conv').val();

        // show loading
        vcmShowLoading();
        
        // make the AJAX request to the get the conversion rate
        var jqxhr = jQuery.ajax({
            type: "POST",
            url: "index.php",
            data: {
                option: "com_vikchannelmanager",
                task: "currency_conversion",
                tmpl: "component",
                from_currency: from_currency,
                to_currency: to_currency,
                rates: [first_cost]
            }
        }).done(function(res) {
            // stop loading
            vcmStopLoading();

            // parse the JSON response object
            try {
                // parse encoded response
                var conversion_data = JSON.parse(res);

                // the calculated alteration values
                var calculated_alteration_val  = null;
                var calculated_alteration_oper = null;

                // compose exchange rate message
                var exchange_message = Joomla.JText._('VCM_EXCHRATECURR');
                exchange_message = exchange_message.replace('%s', from_currency).replace('%s', to_currency).replace('%s', conversion_data['exchange_rate']);

                // compose example message
                var example_message = '';
                if (typeof conversion_data['converted_rates'] == 'object' && conversion_data['converted_rates'].hasOwnProperty(0) && conversion_data['converted_rates'][0].hasOwnProperty('price')) {
                    // try to format the "first" cost
                    var readable_fcost = parseFloat(first_cost);
                    if (isNaN(readable_fcost)) {
                        readable_fcost = first_cost;
                    } else {
                        readable_fcost = readable_fcost.toLocaleString();
                    }
                    example_message = readable_fcost + ' ' + from_currency + ' = ' + conversion_data['converted_rates'][0]['price'] + ' ' + to_currency;
                } else {
                    // if we had an error during the conversion of the first room rate found, we log it
                    console.log('Converted rates unexpected response.', conversion_data['converted_rates']);
                }

                // compose alteration message
                var alteration_message = '';
                if (typeof conversion_data['pcent_alteration'] == 'object' && conversion_data['pcent_alteration'].hasOwnProperty(0) && conversion_data['pcent_alteration'][0] != 0) {
                    // we've got a valid response for the percent alteration
                    alteration_message = Joomla.JText._('VCM_CURRCONV_SUGG_PCENT');
                    alteration_message = alteration_message.replace('%s', conversion_data['pcent_alteration'][2]).replace('%s', from_currency).replace('%s', to_currency);
                    // update the calculated alteration values
                    calculated_alteration_val  = conversion_data['pcent_alteration'][0];
                    calculated_alteration_oper = conversion_data['pcent_alteration'][1];
                }

                // append messages
                jQuery('.vcm-ratespush-currconverter-result').append('<p class="info">' + exchange_message + '</p>');
                if (example_message.length) {
                    jQuery('.vcm-ratespush-currconverter-result').append('<p class="info">' + example_message + '</p>');
                }
                if (alteration_message.length) {
                    jQuery('.vcm-ratespush-currconverter-result').append('<p class="successmade">' + alteration_message + '</p>');
                }

                if (calculated_alteration_val != null) {
                    // update hidden values for the percent alteration
                    jQuery('#vcm-pcent-alteration').val(calculated_alteration_val);
                    jQuery('#vcm-pcent-alteration-oper').val(calculated_alteration_oper);
                }
            } catch (e) {
                // log the errors in the console
                console.error(res);
                console.error(e);
                // append error message
                jQuery('.vcm-ratespush-currconverter-result').append('<p class="err">' + Joomla.JText._('VCM_CURRCONV_ERR_GENERIC') + '</p>');
                // exit
                return false;
            }
        }).fail(function() {
            alert('Request failed');
            // stop loading
            vcmStopLoading();
        });
    }

    function vcmApplyConvAlterations() {
        var pcent_alteration = jQuery('#vcm-pcent-alteration').val();
        var pcent_alteration_oper = jQuery('#vcm-pcent-alteration-oper').val();
        if (!pcent_alteration || !pcent_alteration.length || pcent_alteration == '0' || !pcent_alteration_oper || !pcent_alteration_oper.length) {
            alert(Joomla.JText._('VCM_PLEASE_SELECT'));
            // reset the value in the advanced settings
            jQuery('input#vcm-do-curr-conv').val('0').prop('disabled', true).prop('checked', false);
            return false;
        }

        // apply this pricing modification to all rooms
        var rooms_involved  = new Array;
        // grab all room IDs
        jQuery('.vcmavpush-togglerstatus').each(function() {
            rooms_involved.push(jQuery(this).attr('data-roomid'));
        });

        // loop over all rooms involved
        for (var rind in rooms_involved) {
            if (!rooms_involved.hasOwnProperty(rind)) {
                continue;
            }
            var room_id = rooms_involved[rind];

            // get alteration rules target
            var alter_target = jQuery('#contrmods_' + room_id);
            if (!alter_target || !alter_target.length) {
                console.error('Alter target not found for room ' + room_id);
                // useless to proceed with the rest of the rooms
                return false;
            }

            // clean up any possible alteration per channel
            if (alter_target.find('.vcmratespush-rmods-forsingle').length) {
                // this room is mapped to more than one channel
                if (alter_target.find('.vcmratespush-rmods-forsingle').find('.vcmratespush-rmods-channels-list').length) {
                    // empty current rules for single channels by keeping the trigger element inside
                    alter_target.find('.vcmratespush-rmods-forsingle').find('.vcmratespush-rmods-channels-list').remove();
                }
            }

            // show rules for all channels
            alter_target.find('.vcmratespush-rmods-forall').show();

            // apply the calculated alteration
            jQuery('#rmods_' + room_id).val('1').trigger('change');
            jQuery('#rmodsop_' + room_id).val((pcent_alteration_oper == '+' ? '1' : '0'));
            jQuery('#rmodsamount_' + room_id).val(pcent_alteration);
            jQuery('#rmodsval_' + room_id).val('1');
        }

        // set and display the value in the advanced settings
        jQuery('input#vcm-do-curr-conv').val(pcent_alteration_oper + pcent_alteration).prop('disabled', false).prop('checked', true);
        jQuery('.vcm-do-curr-conv').show();

        // close modal
        vcmCloseModal();
    }
    //

    function vcmCheckDates(selectedDate, inst) {
        if (selectedDate !== null && inst !== null) {
            var inpidparts = jQuery(this).attr('id').split('_');
            var cur_from_date = jQuery(this).val();
            if (inpidparts[0] == 'from' && cur_from_date.length > 0) {
                var nowstart = jQuery(this).datepicker('getDate');
                var nowstartdate = new Date(nowstart.getTime());
                var nextyear = new Date(nowstart.getTime());
                nextyear.setFullYear((nextyear.getFullYear() + 1));
                nextyear.setMonth((nextyear.getMonth() + 6));
                jQuery('#to_'+inpidparts[1]).datepicker( 'option', { minDate: nowstartdate, maxDate: nextyear } );
            }
        }
        jQuery('.vcmdatepickerav:input').each(function(k, v) {
            var inpidparts = jQuery(this).attr('id').split('_');
            var cur_from_date = jQuery(this).val();
            var cur_to_date = jQuery('#to_'+inpidparts[1]).val();
            if (inpidparts[0] != 'from' || cur_from_date.length <= 0 || cur_to_date.length <= 0) {
                return true;
            }
            var fromd = new Date(cur_from_date);
            fromd.setMinutes(fromd.getMinutes() - fromd.getTimezoneOffset());
            var tod = new Date(cur_to_date);
            tod.setMinutes(tod.getMinutes() - tod.getTimezoneOffset());
            var daysdiff = Math.round((tod - fromd) / 86400000);
            if (parseInt(daysdiff) < 1) {
                daysdiff = 1;
                fromd.setDate(fromd.getDate() + 1);
                jQuery('#to_'+inpidparts[1]).datepicker( 'setDate', fromd );
            }
            jQuery('#totdays_'+inpidparts[1]).html(daysdiff+" <?php echo addslashes(JText::_('VCMDAYS')); ?>");
        });
    }

    function vcmSetDefaultRates(rid, sel) {
        var opt_val = jQuery('option:selected', sel).attr('data-defcost');
        opt_val = opt_val.length ? opt_val : '0.00';
        jQuery('#defrates_'+rid).val(opt_val);
        jQuery('#cur_defrates_'+rid).val(opt_val);
        jQuery('#curtxt_defrates_'+rid).text(opt_val);
        populateCachedRates(rid, sel.value);
    }

    function populateCachedRates(rid, sel_pricetype) {
        if (rid && jQuery('#warndefrate_'+rid).length) {
            // always hide warning icons
            jQuery('#warndefrate_'+rid).hide();
            jQuery('#cur_warndefrate_'+rid).hide();
        }
        if (!vcmFullObject(def_ratescache) || rid === undefined || !def_ratescache.hasOwnProperty(rid)) {
            // no cached values found for this room ID
            return;
        }
        if (sel_pricetype === undefined) {
            var rpricetypes = document.getElementById('pricetypes_' + rid);
            if (!rpricetypes) {
                // room has got no rate plans - should never happen
                return;
            }
            // use the currently selected room rate plan
            var sel_pricetype = rpricetypes.options[rpricetypes.selectedIndex].value;
        }
        if (!def_ratescache[rid].hasOwnProperty(sel_pricetype)) {
            // no cached values found for this room and rate plan IDs
            return;
        }

        // handle default rate
        if (def_ratescache[rid][sel_pricetype].hasOwnProperty('defrate')) {
            var curdefrate = parseFloat(jQuery('#defrates_'+rid).val());
            var setdefrate = def_ratescache[rid][sel_pricetype]['defrate'];
            jQuery('#defrates_'+rid).val(setdefrate);
            if (!isNaN(curdefrate) && curdefrate > 0 && parseFloat(setdefrate) != curdefrate) {
                var rawlbl = '<?php echo addslashes(JText::sprintf('VCMRATESPUSHPERNIGHTWARN', $currencysymb, '%s')); ?>';
                rawlbl = rawlbl.replace('%s', jQuery('select#pricetypes_'+rid).find('option:selected').attr('data-defcost'));
            <?php
            if (VCMPlatformDetection::isWordPress()) {
                ?>
                /**
                 * @wponly  we do not use the method .find('i').popover('destroy').popover({content: rawlbl});
                 * we rather update the data-content attribute of the i tag.
                 */
                jQuery('#warndefrate_'+rid).find('i').attr('data-content', rawlbl);
                <?php
            } else {
                ?>
                /**
                 * @joomlaonly  we do not update the data-content attribute, we use the .popover methods
                 */
                try {
                    jQuery('#warndefrate_'+rid).find('i').popover('destroy').popover({content: rawlbl});
                } catch (e) {
                    // do nothing
                }
                <?php
            }
            ?>
                jQuery('#warndefrate_'+rid).show();
                jQuery('#cur_warndefrate_'+rid).show();
            } else {
                jQuery('#warndefrate_'+rid).hide();
                jQuery('#cur_warndefrate_'+rid).hide();
            }
        }

        // in case of changes made before submitting the bulk action, we may want to remember the last settings
        var force_rmod = false;

        // check if the global bulk-rates-cache object has got values
        var single_ch_rules_defined = (def_ratescache.hasOwnProperty(rid) && def_ratescache[rid].hasOwnProperty(sel_pricetype));
        single_ch_rules_defined = (single_ch_rules_defined && def_ratescache[rid][sel_pricetype].hasOwnProperty('rmod_channels'));
        single_ch_rules_defined = (single_ch_rules_defined && vcmFullObject(def_ratescache[rid][sel_pricetype]['rmod_channels']));
        single_ch_rules_defined = (single_ch_rules_defined && jQuery('#contrmods_' + rid).length);
        single_ch_rules_defined = (single_ch_rules_defined && jQuery('#contrmods_' + rid).find('.vcmratespush-rmods-forsingle').length);
        // get element to define the alteration rules
        var alter_target = jQuery('#contrmods_' + rid);
        // show rules for all channels, maybe temporarily
        alter_target.find('.vcmratespush-rmods-forall').show();
        if (single_ch_rules_defined) {
            // build channels with their rate modification rules
            var ch_rules = {};
            for (var ch_key in def_ratescache[rid][sel_pricetype]['rmod_channels']) {
                if (!def_ratescache[rid][sel_pricetype]['rmod_channels'].hasOwnProperty(ch_key)) {
                    continue;
                }
                // get object properties
                var ch_rmod       = def_ratescache[rid][sel_pricetype]['rmod_channels'][ch_key]['rmod'] || '0';
                var ch_rmodop     = def_ratescache[rid][sel_pricetype]['rmod_channels'][ch_key]['rmodop'] || '1';
                var ch_rmodamount = def_ratescache[rid][sel_pricetype]['rmod_channels'][ch_key]['rmodamount'] || '0';
                var ch_rmodval    = def_ratescache[rid][sel_pricetype]['rmod_channels'][ch_key]['rmodval'] || '1';
                if (!ch_rmodamount.length || (!isNaN(ch_rmodamount) && parseInt(ch_rmodamount) < 1)) {
                    // no rate modification for this channel
                    ch_rmod = '0';
                }
                if (ch_rmod == '1') {
                    force_rmod = true;
                }
                // channel name
                var ch_name     = ch_key;
                var ch_classes  = '';
                jQuery('.vcmratespush-channel-wrap[data-roomid="' + rid + '"]').each(function() {
                    var ch_wrapper = jQuery(this).find('.vcmavpush-channel-cont[data-chkey="' + ch_key + '"]');
                    if (!ch_wrapper.length) {
                        return;
                    }
                    // name found
                    ch_name = ch_wrapper.attr('data-chname');
                    // clean and set class name for this channel
                    ch_classes = ch_wrapper.attr('class').replace(' vcmavpush-channel-cont-deactive', '').replace(' vcmavpush-channel-cont', '');
                    // break loop
                    return false;
                });
                // set object properties
                ch_rules[ch_key] = {
                    ch_name:    ch_name,
                    ch_classes: ch_classes,
                    rmod:       ch_rmod,
                    rmodamount: ch_rmodamount,
                    rmodop:     ch_rmodop,
                    rmodval:    ch_rmodval
                };
            }
            // we can now render the alteration rules per channel
            var current_chrules = vcmRenderChannelAlterations(rid, sel_pricetype, ch_rules);
            if (current_chrules !== false) {
                // hide rules for all channels
                alter_target.find('.vcmratespush-rmods-forall').hide();
                // empty current rules for single channels by keeping the trigger element inside
                if (alter_target.find('.vcmratespush-rmods-forsingle').find('.vcmratespush-rmods-channels-list').length) {
                    alter_target.find('.vcmratespush-rmods-forsingle').find('.vcmratespush-rmods-channels-list').remove();
                }
                // append previous channel rules
                alter_target.find('.vcmratespush-rmods-forsingle').append(current_chrules).show();
            }
        } else {
            // empty rules for single channels, if present
            var single_channels_cont = alter_target.find('.vcmratespush-rmods-forsingle');
            if (single_channels_cont.length && single_channels_cont.find('.vcmratespush-rmods-channels-list').length) {
                single_channels_cont.find('.vcmratespush-rmods-channels-list').remove();
            }
        }

        // handle rate modification rules for all channels
        if (def_ratescache[rid][sel_pricetype].hasOwnProperty('rmod')) {
            if (force_rmod && def_ratescache[rid][sel_pricetype]['rmod'] == '0') {
                def_ratescache[rid][sel_pricetype]['rmod'] = '1';
            }
            jQuery('#rmods_' + rid).val(def_ratescache[rid][sel_pricetype]['rmod']).trigger('change');
        }
        if (def_ratescache[rid][sel_pricetype].hasOwnProperty('rmodop')) {
            jQuery('#rmodsop_' + rid).val(def_ratescache[rid][sel_pricetype]['rmodop']).trigger('change');
        }
        if (def_ratescache[rid][sel_pricetype].hasOwnProperty('rmodamount')) {
            jQuery('#rmodsamount_' + rid).val(def_ratescache[rid][sel_pricetype]['rmodamount']);
        }
        if (def_ratescache[rid][sel_pricetype].hasOwnProperty('rmodval')) {
            if (def_ratescache[rid][sel_pricetype].hasOwnProperty('rmod') && def_ratescache[rid][sel_pricetype]['rmod'] != '0') {
                // when rates should be modified, we set the proper value for the operator (% or fixed)
                jQuery('#rmodsval_' + rid).val(def_ratescache[rid][sel_pricetype]['rmodval']).trigger('change');
            }
        }

        // handle the active/de-active channels
        if (def_ratescache[rid][sel_pricetype].hasOwnProperty('channels') && vcmFullObject(def_ratescache[rid][sel_pricetype]['channels'])) {
            var check_rplans = def_ratescache[rid][sel_pricetype].hasOwnProperty('rplans') && vcmFullObject(def_ratescache[rid][sel_pricetype]['rplans']) ? true : false;
            var check_cur_rplans = def_ratescache[rid][sel_pricetype].hasOwnProperty('cur_rplans') && vcmFullObject(def_ratescache[rid][sel_pricetype]['cur_rplans']) ? true : false;
            var check_rplanarimode = def_ratescache[rid][sel_pricetype].hasOwnProperty('rplanarimode') && vcmFullObject(def_ratescache[rid][sel_pricetype]['rplanarimode']) ? true : false;
            /* uncheck the checked channels */
            jQuery(".vcmratespush-channel-wrap[data-roomid='"+rid+"']").find("input.vcm-avpush-checkbox").each(function(k, v) {
                jQuery(v).prop('checked', false).trigger('change');
            });
            /* end uncheck channels */
            for (var chindex in def_ratescache[rid][sel_pricetype]['channels']) {
                if (def_ratescache[rid][sel_pricetype]['channels'].hasOwnProperty(chindex)) {
                    var ch_id = def_ratescache[rid][sel_pricetype]['channels'][chindex];
                    if (jQuery('#ch' + rid + '-' + ch_id).length) {
                        jQuery('#ch' + rid + '-' + ch_id).prop('checked', true).trigger('change');
                    }
                    if (check_rplans === true) {
                        if (def_ratescache[rid][sel_pricetype]['rplans'].hasOwnProperty(ch_id)) {
                            if (jQuery('#rplan_pricingch' + rid + '-' + ch_id).length) {
                                jQuery('#rplan_pricingch' + rid + '-' + ch_id).val(def_ratescache[rid][sel_pricetype]['rplans'][ch_id]).trigger('change');
                            }
                        }
                    }
                    if (check_cur_rplans === true) {
                        if (def_ratescache[rid][sel_pricetype]['cur_rplans'].hasOwnProperty(ch_id)) {
                            if (jQuery('#cur_pricingch' + rid + '-' + ch_id).length) {
                                jQuery('#cur_pricingch' + rid + '-' + ch_id).val(def_ratescache[rid][sel_pricetype]['cur_rplans'][ch_id]);
                            } else {
                                jQuery('#pricingch' + rid + '-' + ch_id).append('<input type="text" class="vcm-ratespush-smallinp" id="cur_pricingch' + rid + '-' + ch_id + '" value="'+def_ratescache[rid][sel_pricetype]['cur_rplans'][ch_id]+'" name="cur_rplans['+rid+']['+ch_id+']" />');
                            }
                        }
                    }
                    if (check_rplanarimode === true) {
                        if (def_ratescache[rid][sel_pricetype]['rplanarimode'].hasOwnProperty(ch_id)) {
                            if (jQuery('#rplanarimode' + rid + '-' + ch_id).length) {
                                jQuery('#rplanarimode' + rid + '-' + ch_id).val(def_ratescache[rid][sel_pricetype]['rplanarimode'][ch_id]).trigger('change');
                            }
                        }
                    }
                }
            }
        }
    }

    function vcmBulkActionChkboxToggle(ckbox) {
        var elem = jQuery(ckbox);
        if (!elem.length) {
            return;
        }
        var checked = elem.prop('checked');
        var container = elem.closest('.vcmavpush-channel-cont');
        if (!container.length) {
            return;
        }
        // check if a rule for this single channel is present
        var ch_identif = elem.attr('id').replace('ch', '');
        var ch_altered = jQuery('.vcm-ratespush-chaltered[data-chaltered="' + ch_identif + '"]');
        if (checked) {
            container.removeClass('vcmavpush-channel-cont-deactive');
            if (ch_altered.length) {
                ch_altered.removeClass('vcm-ratespush-chaltered-deactive');
            }
        } else {
            container.addClass('vcmavpush-channel-cont-deactive');
            if (ch_altered.length) {
                ch_altered.addClass('vcm-ratespush-chaltered-deactive');
            }
        }
    }

    function vcmBulkActionToggleChannels(selector, status) {
        var channel_ckboxes = jQuery(selector);
        if (!channel_ckboxes.length) {
            return;
        }
        channel_ckboxes.each(function() {
            var ckbox = jQuery(this);
            if (!ckbox.length) {
                return;
            }
            ckbox.prop('checked', status);
            // trigger "vcmBulkActionChkboxToggle" with change event
            ckbox.trigger('change');
        });
    }

    /**
     * Modal window to manage different alterations per channel
     */
    var vcm_chalteration_all = true;

    function vcmOpenModal() {
        jQuery(".vcm-info-overlay-diffalterch").fadeIn();
        vcm_overlay_on = true;
    }

    function vcmCloseModal() {
        jQuery(".vcm-info-overlay-block").fadeOut();
        vcm_overlay_on = false;
    }

    function vcmManageAlterationsPerChannel(room_id) {
        // grab current rate plan ID
        var rplan_id = jQuery('#pricetypes_' + room_id).val();

        // set room and rate plan id attributes for modal
        jQuery('.vcm-ratespush-chalterations-modal').attr('data-roomid', room_id).attr('data-rplanid', rplan_id);

        // grab channels for all rooms, no matter what channels this room is mapped to
        var room_channels = [];
        var chkeys_used   = [];
        jQuery('span.vcmavpush-channel-cont').each(function() {
            var ch_name = jQuery(this).attr('data-chname');
            var ch_key = jQuery(this).attr('data-chkey');
            var el_classes = jQuery(this).attr('class');
            if (chkeys_used.indexOf(ch_key) >= 0) {
                // channel taken already, continue
                return;
            }
            room_channels.push({
                name:    ch_name,
                key:     ch_key,
                classes: el_classes
            });
            chkeys_used.push(ch_key);
        });
        if (!room_channels.length) {
            return;
        }

        // grab default (current) rules for all channels
        var def_rmodsop = jQuery('#rmodsop_' + room_id).val();
        var def_rmodsamount = jQuery('#rmodsamount_' + room_id).val();
        var def_rmodsval = jQuery('#rmodsval_' + room_id).val();

        // grab room and rate plan names
        var room_name = jQuery('[data-mainroomid="' + room_id + '"]').text();
        var rplan_name = jQuery('#pricetypes_' + room_id + ' option:selected').text();
        
        // build modal HTML content for the room channels
        var html_alterations = '';
        html_alterations += '<h4>' + room_name + ' - ' + rplan_name + '</h4>' + "\n";
        html_alterations += '<div class="vcm-ratespush-chalteration-cont">' + "\n";
        for (var i = 0; i < room_channels.length; i++) {
            // create special span tag identifying the channel and its CSS classes
            var chspan = jQuery('<span></span>').
                            addClass(room_channels[i]['classes'] + ' vcm-ratespush-chaltering').
                            removeClass('vcmavpush-channel-cont vcmavpush-channel-cont-deactive').
                            text(room_channels[i]['name']);

            // define pre-selected values for this channel
            var use_rmodsop     = def_rmodsop;
            var use_rmodsamount = def_rmodsamount;
            var use_rmodsval    = def_rmodsval;

            // make sure to populate the right amount for this channel
            use_rmodsamount = room_channels[i]['key'] == '<?php echo VikChannelManagerConfig::GOOGLEHOTEL; ?>' ? '0' : use_rmodsamount;
            use_rmodsamount = room_channels[i]['key'] == '<?php echo VikChannelManagerConfig::GOOGLEVR; ?>' ? '0' : use_rmodsamount;

            // check if the global bulk-rates-cache object has got values
            var cached_rmods_vals = (def_ratescache.hasOwnProperty(room_id) && def_ratescache[room_id].hasOwnProperty(rplan_id));
            cached_rmods_vals = (cached_rmods_vals && def_ratescache[room_id][rplan_id].hasOwnProperty('rmod_channels'));
            cached_rmods_vals = (cached_rmods_vals && def_ratescache[room_id][rplan_id]['rmod_channels'].hasOwnProperty(room_channels[i]['key']));
            cached_rmods_vals = (cached_rmods_vals && def_ratescache[room_id][rplan_id]['rmod_channels'][room_channels[i]['key']].hasOwnProperty('rmod'));
            cached_rmods_vals = (cached_rmods_vals && def_ratescache[room_id][rplan_id]['rmod_channels'][room_channels[i]['key']].hasOwnProperty('rmodamount'));
            cached_rmods_vals = (cached_rmods_vals && def_ratescache[room_id][rplan_id]['rmod_channels'][room_channels[i]['key']].hasOwnProperty('rmodop'));
            cached_rmods_vals = (cached_rmods_vals && def_ratescache[room_id][rplan_id]['rmod_channels'][room_channels[i]['key']].hasOwnProperty('rmodval'));
            if (cached_rmods_vals) {
                // overwrite pre-selected values for this channel
                use_rmodsop     = def_ratescache[room_id][rplan_id]['rmod_channels'][room_channels[i]['key']]['rmodop'];
                use_rmodsamount = def_ratescache[room_id][rplan_id]['rmod_channels'][room_channels[i]['key']]['rmodamount'];
                use_rmodsval    = def_ratescache[room_id][rplan_id]['rmod_channels'][room_channels[i]['key']]['rmodval'];
            }

            // build default alteration filters wrapper
            var alter_filters_wrap = jQuery('<div></div>').addClass('vcm-ratespush-chalteration-filters');
            var filters_rmod = '';
            filters_rmod += '<select class="vcm-ratespush-rmodsop-singlech">' + "\n";
            filters_rmod += '   <option value="1"' + (use_rmodsop == '1' ? ' selected="selected"' : '') + '>' + Joomla.JText._('VCMRATESPUSHRMODINCR') + '</option>' + "\n";
            filters_rmod += '   <option value="0"' + (use_rmodsop == '0' ? ' selected="selected"' : '') + '>' + Joomla.JText._('VCMRATESPUSHRMODDECR') + '</option>' + "\n";
            filters_rmod += '</select>' + "\n";
            filters_rmod += '<input type="number" min="0" step="any" class="vcm-ratespush-rmodsamount-singlech" value="' + use_rmodsamount + '" />' + "\n";
            filters_rmod += '<select class="vcm-ratespush-rmodsval-singlech">' + "\n";
            filters_rmod += '   <option value="1"' + (use_rmodsval == '1' ? ' selected="selected"' : '') + '>%</option>' + "\n";
            filters_rmod += '   <option value="0"' + (use_rmodsval == '0' ? ' selected="selected"' : '') + '><?php echo $currencysymb; ?></option>' + "\n";
            filters_rmod += '</select>' + "\n";
            // append filters to wrapper
            jQuery(filters_rmod).appendTo(alter_filters_wrap);

            // set channel content
            var ch_classes = 'data-chclasses="' + chspan.attr('class') + '"';
            html_alterations += '<div class="vcm-ratespush-chalteration-wrap" data-chkey="' + room_channels[i]['key'] + '" data-chname="' + room_channels[i]['name'] + '" ' + ch_classes + '>' + "\n";
            html_alterations += '   <div class="vcm-ratespush-chalteration-head">' + "\n";
            html_alterations += '       ' + chspan[0].outerHTML + "\n";
            html_alterations += '   </div>' + "\n";
            html_alterations += '   <div class="vcm-ratespush-chalteration-body">' + "\n";
            html_alterations += '       ' + alter_filters_wrap[0].outerHTML + "\n";
            html_alterations += '   </div>' + "\n";
            html_alterations += '</div>' + "\n";
        }
        html_alterations += '</div>' + "\n";
        var ch_alter_all_cked = vcm_chalteration_all ? 'checked' : '';
        html_alterations += '<div class="vcm-ratespush-chalteration-cont vcm-ratespush-chalteration-bottom">' + "\n";
        html_alterations += '   <span class="vcm-ratespush-chalteration-applyall-cont">' + "\n";
        html_alterations += '       <label for="vcm-chalteration-applyall">' + Joomla.JText._('VCM_APPLY_ALL_ROOMS') + '</label>' + "\n";
        html_alterations += '       <input type="checkbox" id="vcm-chalteration-applyall" value="1" onchange="vcmToggleAlterationApplyAll(this);" ' + ch_alter_all_cked + '/>' + "\n";
        html_alterations += '   </span>' + "\n";
        html_alterations += '</div>' + "\n";
        // append modal content
        jQuery('.vcm-ratespush-chalterations-modal').html(html_alterations);
        // show modal
        vcmOpenModal();
    }

    function vcmToggleAlterationApplyAll(elem) {
        vcm_chalteration_all = this.checked;
    }

    function vcmApplyAlterationsPerChannel() {
        // first off, modify the global bulk-rates-cache object
        var modal_elem = jQuery('.vcm-ratespush-chalterations-modal');
        var cur_room_id = modal_elem.attr('data-roomid');
        var rplan_id = modal_elem.attr('data-rplanid');
        if (!cur_room_id || !rplan_id || !cur_room_id.length || !rplan_id.length) {
            console.error('Empty room or rate plan ID');
            return false;
        }
        // check if the alteration rules should be applied to all rooms
        var rooms_involved  = new Array;
        var apply_all_rooms = jQuery('#vcm-chalteration-applyall').prop('checked');
        if (apply_all_rooms === true) {
            // grab all room IDs
            jQuery('.vcmavpush-togglerstatus').each(function() {
                rooms_involved.push(jQuery(this).attr('data-roomid'));
            });
        } else {
            rooms_involved.push(cur_room_id);
        }

        // loop over all rooms involved
        for (var rind in rooms_involved) {
            if (!rooms_involved.hasOwnProperty(rind)) {
                continue;
            }
            var room_id = rooms_involved[rind];

            // get alteration rules target
            var alter_target = jQuery('#contrmods_' + room_id);
            if (!alter_target || !alter_target.length) {
                console.error('Alter target not found for room ' + room_id);
                // useless to proceed with the rest of the rooms
                return false;
            }

            // make sure the container for single channel rules is available
            if (!alter_target.find('.vcmratespush-rmods-forsingle').length) {
                // this room is probably mapped to just one channel
                var room_only_channel_id = jQuery('.vcmratespush-channel-wrap[data-roomid="' + room_id + '"]').find('.vcmavpush-channel-cont').attr('data-chkey');
                if (room_only_channel_id && room_only_channel_id.length) {
                    // check if an alteration has been specified for this channel
                    modal_elem.find('.vcm-ratespush-chalteration-wrap').each(function() {
                        var channel_wrap = jQuery(this);
                        var ch_key = channel_wrap.attr('data-chkey');
                        if (ch_key && ch_key == room_only_channel_id) {
                            // we've got info for this channel
                            var ch_rmod = '1';
                            var ch_rmodop = channel_wrap.find('.vcm-ratespush-rmodsop-singlech').val();
                            var ch_rmodamount = channel_wrap.find('.vcm-ratespush-rmodsamount-singlech').val();
                            var ch_rmodval = channel_wrap.find('.vcm-ratespush-rmodsval-singlech').val();
                            if (!ch_rmodamount.length || (!isNaN(ch_rmodamount) && parseInt(ch_rmodamount) < 1)) {
                                // no rate modification for this channel
                                ch_rmod = '0';
                            }
                            if (ch_rmod == '1') {
                                // we can set the alteration rule globally for this room with just one channel
                                jQuery('#rmods_' + room_id).val(ch_rmod).trigger('change');
                                jQuery('#rmodsop_' + room_id).val(ch_rmodop);
                                jQuery('#rmodsamount_' + room_id).val(ch_rmodamount);
                                jQuery('#rmodsval_' + room_id).val(ch_rmodval);
                            }
                            // break the loop
                            return false;
                        }
                    });
                }
                // we cannot do anything more to this room with just one channel
                continue;
            }

            // grab channels with their rate modification rules
            var ch_rules = {};
            modal_elem.find('.vcm-ratespush-chalteration-wrap').each(function() {
                var channel_wrap = jQuery(this);
                var ch_key = channel_wrap.attr('data-chkey');
                var ch_name = channel_wrap.attr('data-chname');
                var ch_classes = channel_wrap.attr('data-chclasses');
                if (!ch_key || !ch_key.length) {
                    return;
                }
                // get object properties
                var ch_rmod = '1';
                var ch_rmodop = channel_wrap.find('.vcm-ratespush-rmodsop-singlech').val();
                var ch_rmodamount = channel_wrap.find('.vcm-ratespush-rmodsamount-singlech').val();
                var ch_rmodval = channel_wrap.find('.vcm-ratespush-rmodsval-singlech').val();
                if (!ch_rmodamount.length || (!isNaN(ch_rmodamount) && parseInt(ch_rmodamount) < 1)) {
                    // no rate modification for this channel
                    ch_rmod = '0';
                }
                // set object properties
                var ch_properties = {
                    rmod:       ch_rmod,
                    rmodamount: ch_rmodamount,
                    rmodop:     ch_rmodop,
                    rmodval:    ch_rmodval
                };
                ch_rules[ch_key] = {
                    // set additional values
                    ch_name:    ch_name,
                    ch_classes: ch_classes,
                    rmod:       ch_rmod,
                    rmodamount: ch_rmodamount,
                    rmodop:     ch_rmodop,
                    rmodval:    ch_rmodval
                };
                // update global object, if necessary
                if (!def_ratescache.hasOwnProperty(room_id) || !def_ratescache[room_id].hasOwnProperty(rplan_id)) {
                    // continue, as bulk rates cache information is not yet available for this combo
                    return;
                }
                if (!def_ratescache[room_id][rplan_id].hasOwnProperty('rmod_channels')) {
                    def_ratescache[room_id][rplan_id]['rmod_channels'] = {};
                }
                // inject/overwrite channel properties
                def_ratescache[room_id][rplan_id]['rmod_channels'][ch_key] = ch_properties;
            });

            // make sure some choices were made
            if (!vcmFullObject(ch_rules)) {
                console.error('Empty object ch_rules');
                // useless to proceed with the rest of the rooms
                return false;
            }

            // hide rules for all channels for the current room
            alter_target.find('.vcmratespush-rmods-forall').hide();
            // empty current rules for single channels by keeping the trigger element inside
            if (alter_target.find('.vcmratespush-rmods-forsingle').find('.vcmratespush-rmods-channels-list').length) {
                alter_target.find('.vcmratespush-rmods-forsingle').find('.vcmratespush-rmods-channels-list').remove();
            }

            // we can now render the alteration rules per channel
            var current_chrules = vcmRenderChannelAlterations(room_id, rplan_id, ch_rules);
            if (current_chrules !== false) {
                // append new channel rules
                alter_target.find('.vcmratespush-rmods-forsingle').append(current_chrules);
                // make sure every room is on "upload modified rates"
                if (room_id != cur_room_id) {
                    jQuery('#rmods_' + room_id).val('1').trigger('change');
                }
            } else {
                // restore the rules for all channels just in case
                alter_target.find('.vcmratespush-rmods-forall').show();
            }
        }

        // close the modal and return
        vcmCloseModal();

        return true;
    }

    function vcmCancelAlterationsPerChannel() {
        // first off, modify the global bulk-rates-cache object
        var modal_elem = jQuery('.vcm-ratespush-chalterations-modal');
        var room_id = modal_elem.attr('data-roomid');
        var rplan_id = modal_elem.attr('data-rplanid');
        if (!room_id || !rplan_id || !room_id.length || !rplan_id.length) {
            console.error('Empty room or rate plan ID');
            return false;
        }
        // update global object, if necessary
        if (def_ratescache.hasOwnProperty(room_id) && def_ratescache[room_id].hasOwnProperty(rplan_id)) {
            if (def_ratescache[room_id][rplan_id].hasOwnProperty('rmod_channels')) {
                delete def_ratescache[room_id][rplan_id]['rmod_channels'];
            }
        }

        // get alteration rules target
        var alter_target = jQuery('#contrmods_' + room_id);
        if (!alter_target || !alter_target.length) {
            console.error('Alter target not found');
            return false;
        }

        // make sure the container for single channel rules is available
        if (!alter_target.find('.vcmratespush-rmods-forsingle').length) {
            // should not happen as PHP should write this element
            console.error('Single channel rules target not found');
            return false;
        }

        // empty current rules for single channels by keeping the trigger element inside
        if (alter_target.find('.vcmratespush-rmods-forsingle').find('.vcmratespush-rmods-channels-list').length) {
            alter_target.find('.vcmratespush-rmods-forsingle').find('.vcmratespush-rmods-channels-list').remove();
        }

        // show rules for all channels
        alter_target.find('.vcmratespush-rmods-forall').show();

        // close the modal and return
        vcmCloseModal();

        return true;
    }

    /**
     * Helper function to obtain the HTML code to render the current rules per channel.
     * This is used when setting/editing the rules through the apposite modal window,
     * and when the current rates/settings need to be populated from the bulk rates cache.
     * 
     * @param   int     room_id     the VBO room id.
     * @param   int     rplan_id    the VBO rate plan id.
     * @param   object  ch_rules    object indicating the rules for each channel.
     * 
     * @return  mixed               false on failure, string otherwise.
     */
    function vcmRenderChannelAlterations(room_id, rplan_id, ch_rules) {
        if (!vcmFullObject(ch_rules) || !room_id || !rplan_id) {
            return false;
        }

        // build the list of channel rules
        var current_chrules = '';
        current_chrules += '<div class="vcmratespush-rmods-channels-list">' + "\n";
        for (var ch_id in ch_rules) {
            if (!ch_rules.hasOwnProperty(ch_id)) {
                continue;
            }
            if (!jQuery('#ch' + room_id + '-' + ch_id).length) {
                // this room is not mapped to this channel, so continue
                continue;
            }
            // build the string describing the rate alteration
            var ch_rmod_display = '';
            if (ch_rules[ch_id]['rmod'] == '0') {
                ch_rmod_display = Joomla.JText._('VCMNO');
            } else {
                ch_rmod_display = (ch_rules[ch_id]['rmodop'] == '0' ? '-' : '+') + ' ';
                if (ch_rules[ch_id]['rmodval'] == '1') {
                    // percent
                    ch_rmod_display += ch_rules[ch_id]['rmodamount'] + '%';
                } else {
                    // fixed value
                    ch_rmod_display += '<?php echo $currencysymb; ?> ' + ch_rules[ch_id]['rmodamount'];
                }
            }
            // check if this channel is currently unchecked
            var ch_checked = jQuery('#ch' + room_id + '-' + ch_id).prop('checked');
            var ch_statcls = ch_checked !== true ? ' vcm-ratespush-chaltered-deactive' : '';
            var ch_dtalter = room_id + '-' + ch_id;
            // build channel HTML content
            current_chrules += '    <div class="vcmratespush-rmods-channels-det">' + "\n";
            current_chrules += '        <span class="' + ch_rules[ch_id]['ch_classes'].replace(' vcm-ratespush-chaltering', '') + ' vcm-ratespush-chaltered' + ch_statcls + '" data-chaltered="' + ch_dtalter + '">' + "\n";
            current_chrules += '            <span class="vcm-ratespush-chaltered-name">' + ch_rules[ch_id]['ch_name'] + '</span>' + "\n";
            current_chrules += '            <span class="vcm-ratespush-chaltered-rmod">' + ch_rmod_display + '</span>' + "\n";
            current_chrules += '        </span>' + "\n";
            current_chrules += '    </div>' + "\n";
            // define channel hidden input value
            current_chrules += '    <input type="hidden" name="rmod_channels[' + room_id + '][' + rplan_id + '][' + ch_id + '][rmod]" value="' + ch_rules[ch_id]['rmod'] + '" />' + "\n";
            current_chrules += '    <input type="hidden" name="rmod_channels[' + room_id + '][' + rplan_id + '][' + ch_id + '][rmodamount]" value="' + ch_rules[ch_id]['rmodamount'] + '" />' + "\n";
            current_chrules += '    <input type="hidden" name="rmod_channels[' + room_id + '][' + rplan_id + '][' + ch_id + '][rmodop]" value="' + ch_rules[ch_id]['rmodop'] + '" />' + "\n";
            current_chrules += '    <input type="hidden" name="rmod_channels[' + room_id + '][' + rplan_id + '][' + ch_id + '][rmodval]" value="' + ch_rules[ch_id]['rmodval'] + '" />' + "\n";
        }
        current_chrules += '</div>' + "\n";

        return current_chrules;
    }

    /**
     * Helper function to obtain the HTML element to render rates distribution result.
     * 
     * @param   string|object   response    AJAX operation response.
     * 
     * @return  string|HTMLElement
     */
    function vcmRenderRatesDistributionResult(response) {
        if (typeof response === 'string') {
            try {
                response = JSON.parse(response);
            } catch(e) {
                // do not proceed
                return 'Error decoding the response';
            }
        }

        if (!vcmFullObject(response)) {
            return 'Error with empty response';
        }

        // build the HTML element for rendering
        let htmlEl = document.createElement('div');
        htmlEl.classList.add('vcm-edit-bulk-rates-cache-response-wrap');

        if (Array.isArray(response?.xml_data)) {
            // debug mode response detected
            response.xml_data.forEach((data) => {
                // build XML request container
                let containerEl = document.createElement('div');
                containerEl.classList.add('vcm-xml-request-wrap');

                // build request head
                let headEl = document.createElement('div');
                headEl.classList.add('vcm-xml-request-head');
                if (data?.idchannel && channel_logos.hasOwnProperty(data.idchannel)) {
                    let imgEl = document.createElement('img');
                    imgEl.setAttribute('src', channel_logos[data.idchannel]);
                    headEl.append(imgEl);
                }
                if (data?.idchannel && channel_names.hasOwnProperty(data.idchannel)) {
                    let chNameEl = document.createElement('span');
                    chNameEl.classList.add('vcm-xml-request-chname');
                    chNameEl.textContent = channel_names[data.idchannel];
                    headEl.append(chNameEl);
                }
                if (data?.idprice && rate_plans_list.hasOwnProperty(data.idprice)) {
                    let rpNameEl = document.createElement('span');
                    rpNameEl.classList.add('vcm-xml-request-rpname');
                    rpNameEl.textContent = rate_plans_list[data.idprice]?.name || data.idprice;
                    headEl.append(rpNameEl);
                }

                // append head element
                containerEl.append(headEl);

                // build pre-formatted element
                let preEl = document.createElement('pre');
                preEl.classList.add('vcmpreerror');
                preEl.textContent = data?.xml;

                // append pre-formatted element
                containerEl.append(preEl);

                // append node
                htmlEl.append(containerEl);
            });
        } else {
            // production response detected
            for (const [idroomvb, channels] of Object.entries(response)) {
                // build channel response container
                let containerEl = document.createElement('div');
                containerEl.classList.add('vcm-bulka-response-wrap');

                // check if the breakdown is available
                let breakdownEl = null;
                if (channels.hasOwnProperty('breakdown')) {
                    breakdownEl = document.createElement('div');
                    breakdownEl.classList.add('vcm-bulka-response-breakdown');
                    // parse the breakdown
                    for (const [datenode, daterate] of Object.entries(channels.breakdown)) {
                        // build breakdown date and rate details
                        let breakdownNode = document.createElement('div');
                        breakdownNode.classList.add('vcm-bulka-response-breakdown-entry');
                        breakdownNode.innerHTML = datenode + " --&gt; " + daterate;
                        // append to breakdown element
                        breakdownEl.append(breakdownNode);
                    }
                    // unset this property as already processed
                    delete channels.breakdown;
                }

                // parse the channels involved
                for (const [idchannel, chresult] of Object.entries(channels)) {
                    // build channel response element
                    let channelEl = document.createElement('div');
                    channelEl.classList.add('vcm-bulka-response-channel');

                    let channelHeadEl = document.createElement('div');
                    channelHeadEl.classList.add('vcm-bulka-response-channel-head');

                    if (channel_logos.hasOwnProperty(idchannel)) {
                        let imgEl = document.createElement('img');
                        imgEl.setAttribute('src', channel_logos[idchannel]);
                        channelHeadEl.append(imgEl);
                    }
                    if (channel_names.hasOwnProperty(idchannel)) {
                        let chNameEl = document.createElement('span');
                        chNameEl.textContent = channel_names[idchannel];
                        channelHeadEl.append(chNameEl);
                    }

                    channelEl.append(channelHeadEl);

                    if (chresult.substr(0, 9) == 'e4j.error') {
                        let chReponseEl = document.createElement('p');
                        chReponseEl.classList.add('err');
                        chReponseEl.textContent = chresult.replace('e4j.error.', '');
                        channelEl.append(chReponseEl);
                    } else if (chresult.substr(0, 11) == 'e4j.warning') {
                        let chReponseEl = document.createElement('p');
                        chReponseEl.classList.add('warn');
                        chReponseEl.textContent = chresult.replace('e4j.warning.', '');
                        channelEl.append(chReponseEl);
                    } else if (chresult.substr(0, 6) == 'e4j.OK') {
                        let chReponseEl = document.createElement('p');
                        chReponseEl.classList.add('successmade');
                        chReponseEl.textContent = chresult.replace('e4j.OK.', '');
                        channelEl.append(chReponseEl);
                    }

                    // append channel result
                    containerEl.append(channelEl);
                }

                if (breakdownEl) {
                    // append breakdown element
                    containerEl.append(breakdownEl);
                }

                // append node
                htmlEl.append(containerEl);
            }
        }

        return htmlEl;
    }

    /**
     * DOM ready statement.
     */
    jQuery(function() {

        vcmCheckDates(null, null);

        jQuery('.vcmdatepickerav:input').datepicker({
            dateFormat: "yy-mm-dd",
            minDate: 0,
            maxDate: "+1y +6m",
            onSelect: vcmCheckDates
        });

        jQuery('.vcmavpush-togglerstatus').click(function() {
            var roomid = jQuery(this).attr('data-roomid');
            if (jQuery('#from_'+roomid).prop('disabled') === true) {
                jQuery('#from_'+roomid).prop('disabled', false);
                jQuery('#to_'+roomid).prop('disabled', false);
                jQuery('.check-'+roomid).prop('disabled', false);
                jQuery('#room_'+roomid).prop('disabled', false).attr('name', 'rooms[]');
                jQuery('.rplans'+roomid+' select, .rplans'+roomid+' input, .chrplans'+roomid+' select, .chrplans'+roomid+' input, .rmods'+roomid+' select, .rmods'+roomid+' input').prop('disabled', false);
                jQuery(this).text("<?php echo addslashes(JText::_('VCMDESELECT')); ?>");
                jQuery(this).closest('td').next().css('opacity', 1).next().css('opacity', 1).next().css('opacity', 1).next().css('opacity', 1);
            } else {
                jQuery('#from_'+roomid).prop('disabled', true);
                jQuery('#to_'+roomid).prop('disabled', true);
                jQuery('.check-'+roomid).prop('disabled', true);
                jQuery('#room_'+roomid).prop('disabled', true).attr('name', 'disabledrooms[]');
                jQuery('.rplans'+roomid+' select, .rplans'+roomid+' input, .chrplans'+roomid+' select, .chrplans'+roomid+' input, .rmods'+roomid+' select, .rmods'+roomid+' input').prop('disabled', true);
                jQuery(this).text("<?php echo addslashes(JText::_('VCMSELECT')); ?>");
                jQuery(this).closest('td').next().css('opacity', 0.5).next().css('opacity', 0.5).next().css('opacity', 0.5).next().css('opacity', 0.5);
            }
        });

        jQuery('.vcm-avpush-info').click(function() {
            jQuery(this).remove();
        });

        jQuery('.vcm-avpush-checkbox').change(function() {
            jQuery('.vcm-avpush-checkbox').each(function(k, v) {
                if (jQuery(this).prop('disabled') !== true && jQuery(this).prop('checked') === true) {
                    jQuery('#pricing'+jQuery(this).attr('id')).fadeIn();
                } else {
                    jQuery('#pricing'+jQuery(this).attr('id')).fadeOut();
                }
            });
        });

        jQuery('.vcmratespush-selrmods').change(function() {
            if (jQuery(this).val() == '1') {
                jQuery('#cont'+jQuery(this).attr('id')).fadeIn();
            } else {
                jQuery('#cont'+jQuery(this).attr('id')).fadeOut();
            }
        });

        jQuery('#vcm-ratespush-advancedopt').click(function() {
            // check if already clicked
            var already_applied = jQuery(this).attr('data-applied');
            if (!already_applied) {
                // show all sub-drop down menus for the pricing models
                var subdropdowns = jQuery('.vcmratespush-channel-subsel');
                if (subdropdowns.length) {
                    subdropdowns.show();
                }
                // append input field to define the currency for each channel
                jQuery('.vcmratespush-channel-rateplans').each(function(k, v) {
                    var rplan_name = jQuery(v).find('select').first().attr('name');
                    var pr_id = jQuery(v).attr('id');
                    if (rplan_name != null && rplan_name.length && !jQuery(v).find('.vcm-ratespush-smallinp').length) {
                        jQuery(v).append('<input type="text" class="vcm-ratespush-smallinp" id="cur_'+pr_id+'" value="<?php echo VikChannelManager::getCurrencyName(); ?>" name="cur_'+rplan_name+'" />');
                    }
                });
                // display link to remove/edit the bulk rates cache if not empty
                if (vcmFullObject(def_ratescache)) {
                    jQuery('#vcm-ratespush-removecache').show();
                    jQuery('.vcm-ratespush-editcache').show();
                }
                // make sure the next click will detect that it has been applied
                jQuery(this).attr('data-applied', '1');
            }
            // always toggle container of the advanced options
            jQuery('.vcm-ratespush-advanced-right-wrap').toggle();
            // animate scrolling to that position (either very bottom or very top if hidden)
            jQuery('html,body').animate({scrollTop: jQuery('.vcm-ratespush-advanced-right-wrap').offset().top}, {duration: 'fast'});
        });

        jQuery('#vcm-ratespush-toggleall').click(function() {
            jQuery('.vcmavpush-togglerstatus').trigger('click');
        });

        /* Populate data if session or cache values */
        var all_roomids = new Array();
        jQuery('.vcmavpush-togglerstatus').each(function() {
            all_roomids.push(parseInt(jQuery(this).attr('data-roomid')));
        });
        /* Cache */
        if (vcmFullObject(def_ratescache)) {
            for (var rid in def_ratescache) {
                populateCachedRates(rid);
            }
        }
        /* Session */
        if (vcmFullObject(def_roomids)) {
            for (var rid in all_roomids) {
                if (all_roomids.hasOwnProperty(rid)) {
                    if (jQuery.inArray(all_roomids[rid], def_roomids) < 0) {
                        jQuery(".vcmavpush-togglerstatus[data-roomid='"+all_roomids[rid]+"']").trigger("click");
                    }
                }
            }
        }
        /* end Populate Data */

        /* VCM 1.6.12 - default cost per night automatically taken from VBO */
        jQuery('.vcmratespush-rplanscosts-wrap-current .vcmratespush-rplanscosts-amount').click(function() {
            jQuery(this).closest('.vcmratespush-td-rplans').find('.vcmratespush-rplanscosts-wrap-hidden').fadeToggle();
        });

        jQuery('input.vcm-inp-custdefrates').change(function() {
            var elid = jQuery(this).attr('id');
            var elval = jQuery(this).val();
            jQuery('#cur_'+elid).val(elval);
            jQuery('#curtxt_'+elid).text(elval);
        });

        // handle modal
        jQuery(document).mouseup(function(e) {
            if (!vcm_overlay_on) {
                return false;
            }
            var vcm_overlay_cont = jQuery(".vcm-info-overlay-content");
            if (!vcm_overlay_cont.is(e.target) && vcm_overlay_cont.has(e.target).length === 0) {
                // make sure the click was not made on a select2 element
                var target_class = jQuery(e.target).attr('class');
                if (target_class && target_class.indexOf('select2') >= 0) {
                    // it's probably a select2 inside a modal
                    return false;
                }
                vcmCloseModal();
            }
        });

        // handle modal
        jQuery(document).keyup(function(e) {
            if (e.keyCode == 27 && vcm_overlay_on) {
                vcmCloseModal();
            }
        });

        // select2
        if (jQuery('#vcm-convertocurrency').length) {
            jQuery('#vcm-convertocurrency').select2({
                width: 200
            });
        }

        // unify the range of dates to update across all rooms
        jQuery('.vcmavpush-dpick-totdays').click(function() {
            let container = jQuery(this).closest('.vcmratespush-td-dates');
            let from_date = container.find('input[name="from[]"]').datepicker('getDate');
            let to_date   = container.find('input[name="to[]"]').datepicker('getDate');
            let all_elements = jQuery('.vcmavpush-dpick-totdays');
            if (all_elements.length > 1) {
                // apply the same dates to all entries
                all_elements.each(function() {
                    let cal_container = jQuery(this).closest('.vcmratespush-td-dates');
                    cal_container.find('input[name="from[]"]').datepicker('setDate', from_date);
                    cal_container.find('input[name="to[]"]').datepicker('setDate', to_date);
                });
                // calculate the new number of days in the range
                vcmCheckDates(null, null);

                // read the enabled and disabled channels for this listing and apply the same selection to the others
                let entry_channels_enabled = [];
                let entry_channels_disabled = [];
                let entry_channels_list = container.closest('tr').find('.vcmratespush-td-channels').find('.vcmratespush-channel-wrap');
                entry_channels_list.each(function() {
                    let ch_checkbox = jQuery(this).find('input.vcm-avpush-checkbox[type="checkbox"]');
                    if (!ch_checkbox.length) {
                        return;
                    }
                    let ck_channel_id = ch_checkbox.attr('value');
                    if (ch_checkbox.prop('checked')) {
                        entry_channels_enabled.push(ck_channel_id);
                    } else {
                        entry_channels_disabled.push(ck_channel_id);
                    }
                });
                if (!entry_channels_enabled.length && !entry_channels_disabled.length) {
                    // nothing to do
                    return;
                }
                document.querySelectorAll('td.vcmratespush-td-channels').forEach((td_channels) => {
                    td_channels.querySelectorAll('.vcmratespush-channel-wrap').forEach((channel_wrap) => {
                        let entry_ch_input = channel_wrap.querySelector('input.vcm-avpush-checkbox[type="checkbox"]');
                        if (!entry_ch_input) {
                            return;
                        }
                        let entry_channel_id = entry_ch_input.getAttribute('value');
                        if (entry_channels_enabled.includes(entry_channel_id)) {
                            entry_ch_input.checked = true;
                            entry_ch_input.dispatchEvent(new Event('change'));
                        } else if (entry_channels_disabled.includes(entry_channel_id)) {
                            entry_ch_input.checked = false;
                            entry_ch_input.dispatchEvent(new Event('change'));
                        }
                    });
                });
            }
        });

        /**
         * Display listings filtering modal.
         */
        jQuery('.vcm-bulkaction-filters-show').click(function() {
            let cancel_btn = jQuery('<button></button>')
                .addClass('btn')
                .text(Joomla.JText._('CANCEL'))
                .on('click', function() {
                    jQuery('select[name="listingsfilter[]"]').val('').trigger('change');
                    jQuery('form.vcm-bulkaction-filters-form').submit();
                    VBOCore.emitEvent('vcm-bulkaction-filter-listings-dismiss');
                });

            let apply_btn = jQuery('<button></button>')
                .addClass('btn btn-success')
                .text(Joomla.JText._('VCMRARADDLOSAPPLY'))
                .on('click', function() {
                    jQuery('form.vcm-bulkaction-filters-form').submit();
                    VBOCore.emitEvent('vcm-bulkaction-filter-listings-dismiss');
                });

            let modal_body = VBOCore.displayModal({
                title: Joomla.JText._('VCM_FILTERS'),
                footer_left: cancel_btn,
                footer_right: apply_btn,
                dismiss_event: 'vcm-bulkaction-filter-listings-dismiss',
                onDismiss: () => {
                    jQuery('.vcm-bulkaction-filters-container').appendTo(jQuery('.vcm-bulkaction-filters-helper'));
                },
            });

            jQuery('.vcm-bulkaction-filters-container').appendTo(modal_body);
        });

        /**
         * Edit bulk rates cache modal display.
         */
        document.querySelector('.vcm-ratespush-editcache-btn').addEventListener('click', () => {
            // display modal
            let modalBody = VBOCore.displayModal({
                suffix: 'vcm-edit-bulk-rates-cache',
                extra_class: 'vbo-modal-rounded vbo-modal-tall vbo-modal-nofooter',
                title: <?php echo json_encode(JText::_('VCM_EDIT_BULK_RATES_CACHE')); ?>,
                lock_scroll: true,
                dismiss_event: 'vcm-edit-bulk-rates-cache-dismiss',
                loading_event: 'vcm-edit-bulk-rates-cache-loading',
                onDismiss: () => {
                    const helper  = document.querySelector('.vcm-edit-bulk-rates-cache-helper');
                    const content = document.querySelector('.vcm-edit-bulk-rates-cache-wrap');
                    helper.append(content);
                },
            });

            // append content to modal body
            (modalBody[0] || modalBody).append(document.querySelector('.vcm-edit-bulk-rates-cache-wrap'));
        });

        /**
         * Edit bulk rates cache unset room-rate-channel relation.
         */
        document.querySelectorAll('.vcm-flexlist-close[data-room-id]').forEach((dismissBtn) => {
            dismissBtn.addEventListener('click', () => {
                const roomId = dismissBtn.getAttribute('data-room-id');
                const rateId = dismissBtn.getAttribute('data-rate-id');
                const channelId = dismissBtn.getAttribute('data-channel-id');
                if (!roomId || !rateId || !channelId || !confirm(<?php echo json_encode(JText::_('VCMREMOVECONFIRM')); ?>)) {
                    return;
                }
                const choiceElem = dismissBtn.closest('.vcm-flexlist-choice');

                // make the request
                VBOCore.doAjax(
                    "<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikchannelmanager&task=bulkaction.unset_cache_relation'); ?>",
                    {
                        room_id: roomId,
                        rate_id: rateId,
                        channel_id: channelId,
                    },
                    (resp) => {
                        // delete operation was successful, delete the entry in the interface
                        choiceElem.remove();
                    },
                    (err) => {
                        alert(err.responseText || err);
                    }
                );
            });
        });

        /**
         * Edit bulk rates cache trigger listing full-sync.
         */
        document.querySelectorAll('.vcm-roomrates-full-sync[data-room-id]').forEach((syncBtn) => {
            // access current listing ID and name
            const listingId = syncBtn.getAttribute('data-room-id');
            const listingName = syncBtn.closest('.vcm-param-setting').querySelector('.vcm-edit-bulk-rates-listing-name').innerText;

            // set debug-mode flag
            let canDebug = <?php echo $app->input->getBool('e4j_debug') ? 'true' : 'false'; ?>;

            // build context menu buttons for full-sync
            let contextMenuSyncBtns = [
                {
                    icon: '<?php echo VikBookingIcons::i('calendar'); ?>',
                    text: <?php echo json_encode(JText::sprintf('VCM_AV_WINDOW_XMONTHS', 3)); ?>,
                    separator: false,
                    action: (root, config) => {
                        // start loading
                        VBOCore.emitEvent('vcm-edit-bulk-rates-cache-loading');

                        // make the request
                        VBOCore.doAjax(
                            "<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikchannelmanager&task=bulkaction.triggerRates'); ?>",
                            {
                                from: '<?php echo date('Y-m-d'); ?>',
                                to: '<?php echo date('Y-m-d', strtotime('+3 months')); ?>',
                                room_id: listingId,
                            },
                            (resp) => {
                                // set success class to full-sync button
                                syncBtn.classList.remove('btn-primary');
                                syncBtn.classList.add('btn-success');

                                // stop loading
                                VBOCore.emitEvent('vcm-edit-bulk-rates-cache-loading');

                                // display modal with result information
                                VBOCore.displayModal({
                                    suffix: 'vcm-edit-bulk-rates-cache-response',
                                    extra_class: 'vbo-modal-rounded vbo-modal-tall vbo-modal-nofooter',
                                    title: listingName + ' - ' + <?php echo json_encode(JText::_('VCM_FULL_SYNC')); ?>,
                                    lock_scroll: true,
                                    body: vcmRenderRatesDistributionResult(resp),
                                });
                            },
                            (err) => {
                                // stop loading
                                VBOCore.emitEvent('vcm-edit-bulk-rates-cache-loading');

                                // display error
                                alert(err.responseText || err);
                            }
                        );
                    },
                },
                {
                    icon: '<?php echo VikBookingIcons::i('calendar'); ?>',
                    text: <?php echo json_encode(JText::sprintf('VCM_AV_WINDOW_XMONTHS', 6)); ?>,
                    separator: canDebug,
                    action: (root, config) => {
                        // start loading
                        VBOCore.emitEvent('vcm-edit-bulk-rates-cache-loading');

                        // make the request
                        VBOCore.doAjax(
                            "<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikchannelmanager&task=bulkaction.triggerRates'); ?>",
                            {
                                from: '<?php echo date('Y-m-d'); ?>',
                                to: '<?php echo date('Y-m-d', strtotime('+6 months')); ?>',
                                room_id: listingId,
                            },
                            (resp) => {
                                // set success class to full-sync button
                                syncBtn.classList.remove('btn-primary');
                                syncBtn.classList.add('btn-success');

                                // stop loading
                                VBOCore.emitEvent('vcm-edit-bulk-rates-cache-loading');

                                // display modal with result information
                                VBOCore.displayModal({
                                    suffix: 'vcm-edit-bulk-rates-cache-response',
                                    extra_class: 'vbo-modal-rounded vbo-modal-tall vbo-modal-nofooter',
                                    title: listingName + ' - ' + <?php echo json_encode(JText::_('VCM_FULL_SYNC')); ?>,
                                    lock_scroll: true,
                                    body: vcmRenderRatesDistributionResult(resp),
                                });
                            },
                            (err) => {
                                // stop loading
                                VBOCore.emitEvent('vcm-edit-bulk-rates-cache-loading');

                                // display error
                                alert(err.responseText || err);
                            }
                        );
                    },
                },
            ];

            if (canDebug) {
                contextMenuSyncBtns.push({
                    icon: '<?php echo VikBookingIcons::i('code'); ?>',
                    text: 'Debug',
                    separator: false,
                    action: (root, config) => {
                        // start loading
                        VBOCore.emitEvent('vcm-edit-bulk-rates-cache-loading');

                        // make the request
                        VBOCore.doAjax(
                            "<?php echo VikChannelManager::ajaxUrl('index.php?option=com_vikchannelmanager&task=bulkaction.triggerRates'); ?>",
                            {
                                from: '<?php echo date('Y-m-d'); ?>',
                                to: '<?php echo date('Y-m-d', strtotime('+3 months')); ?>',
                                room_id: listingId,
                                debug: 1,
                            },
                            (resp) => {
                                // set success class to full-sync button
                                syncBtn.classList.remove('btn-primary');
                                syncBtn.classList.add('btn-success');

                                // stop loading
                                VBOCore.emitEvent('vcm-edit-bulk-rates-cache-loading');

                                // display (large) modal with result information
                                VBOCore.displayModal({
                                    suffix: 'vcm-edit-bulk-rates-cache-response',
                                    extra_class: 'vbo-modal-rounded vbo-modal-large vbo-modal-nofooter',
                                    title: listingName + ' - ' + <?php echo json_encode(JText::_('VCM_FULL_SYNC')); ?>,
                                    lock_scroll: true,
                                    body: vcmRenderRatesDistributionResult(resp),
                                });
                            },
                            (err) => {
                                // stop loading
                                VBOCore.emitEvent('vcm-edit-bulk-rates-cache-loading');

                                // display error
                                alert(err.responseText || err);
                            }
                        );
                    },
                });
            }

            // render context menu on full-sync button
            jQuery(syncBtn).vboContextMenu({
                class: 'vcm-dropdown-cxmenu',
                placement: 'bottom-right',
                buttons: contextMenuSyncBtns,
            });
        });

    });
</script>
