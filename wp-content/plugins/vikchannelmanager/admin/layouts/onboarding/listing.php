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
 * @var  int     $room_id               The VikBooking room ID to onboard.
 * @var  array   $vbo_listing           The VikBooking room record and details to onboard.
 * @var  object  $listing_details       The OTA listing details to facilitate the onboarding on the new channel.
 * @var  int     $from_channel          The channel identifier from which copying data is made.
 * @var  array   $to_channel            The channel details where the listing should be onboarded.
 * @var  array   $active_accounts       List of active accounts for the channel where the listing should be onboarded.
 * @var  int     $create_new_prop       Whether adding a new property on the onboarding channel is allowed (i.e. on Airbnb it isn't, on Booking.com it is).
 * @var  object  $onboarding_progress   The onboarding processor progress data object.
 * @var  string  $caller                Identifier for who's calling the layout (i.e. "vikbooking").
 */
extract($displayData);

$css_class_prefix = !strcasecmp($caller, 'vikbooking') ? 'vbo' : 'vcm';
$channel_logo = VikChannelManager::getLogosInstance($to_channel['name'])->getTinyLogoURL();

$is_to_airbnb = $to_channel['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI;
$is_to_bcom = $to_channel['uniquekey'] == VikChannelManagerConfig::BOOKING;
$is_from_airbnb = $from_channel == VikChannelManagerConfig::AIRBNBAPI;
$is_from_bcom = $from_channel == VikChannelManagerConfig::BOOKING;
$is_same_channel = $to_channel['uniquekey'] == $from_channel;

$vbo_listing = $vbo_listing ?? [];

$config = VCMFactory::getConfig();
$account_logo_urls = [];

?>
<form action="#onboard-listing" method="post" name="vbo-vcm-onboard-listing-form" id="vbo-vcm-onboard-listing-form">

    <input type="hidden" name="channel_id" value="<?php echo $to_channel['uniquekey']; ?>" />
    <input type="hidden" name="channel_name" value="<?php echo JHtml::_('esc_attr', $to_channel['name']); ?>" />
    <input type="hidden" name="room_id" value="<?php echo $room_id; ?>" />

    <div class="vbo-vcm-onboard-listing-wrap <?php echo $css_class_prefix; ?>-admin-container <?php echo $css_class_prefix; ?>-admin-container-full <?php echo $css_class_prefix; ?>-admin-container-compact">
        <div class="<?php echo $css_class_prefix; ?>-params-wrap">
            <div class="<?php echo $css_class_prefix; ?>-params-container">

                <div class="<?php echo $css_class_prefix; ?>-params-block">

                    <div class="<?php echo $css_class_prefix; ?>-param-container">
                        <div class="<?php echo $css_class_prefix; ?>-param-label">
                            <label for="vbo-vcm-onboard-account"><?php echo JText::_(($create_new_prop ?? 0) ? 'VCM_ONBOARD_LIST_NEW_OR_EXIST' : 'VCM_ONBOARD_LIST_EXIST_ACCOUNT'); ?></label>
                        </div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <select id="vbo-vcm-onboard-account" name="onboarding[account]"<?php echo ($onboarding_progress->hostId ?? null) || ($onboarding_progress->hotelId ?? null) ? ' disabled="disabled"' : ''; ?>>
                            <?php
                            if ($create_new_prop ?? 0) {
                                // create a new account
                                ?>
                                <option value=""><?php echo JText::_('VCM_ONBOARD_LIST_NEW_ACCOUNT'); ?></option>
                                <optgroup label="<?php echo JHtml::_('esc_attr', JText::_('VCM_ONBOARD_LIST_EXIST_ACCOUNT')); ?>">
                                <?php
                            }

                            // count accounts with equal name
                            $equal_account_names = $active_accounts ? max(array_values(array_count_values(array_column($active_accounts, 'prop_name')))) : 1;

                            // list all the configured accounts for onboarding the listing
                            foreach ($active_accounts as $active_account) {
                                $account_data = (array) json_decode($active_account['prop_params'], true);
                                $account_id = $account_data['hotelid'] ?? $account_data['user_id'] ?? '0';
                                if ($is_to_airbnb) {
                                    $account_score = $config->getArray('propscore_' . $to_channel['uniquekey'] . '_' . $account_id, []);
                                    if ($account_score && ($account_score['data']['overall_rating']['summary']['logo_url'] ?? null)) {
                                        // push account logo URL
                                        $account_logo_urls[$account_id] = $account_score['data']['overall_rating']['summary']['logo_url'];
                                    }
                                }
                                $account_selected = (($onboarding_progress->hostId ?? null) == $account_id || ($onboarding_progress->hotelId ?? null) == $account_id);
                                ?>
                                <option value="<?php echo $account_id; ?>"<?php echo $account_selected ? ' selected="selected"' : ''; ?>><?php echo $active_account['prop_name'] . ($equal_account_names > 1 ? ' (ID ' . $account_id . ')' : ''); ?></option>
                                <?php
                            }

                            if ($create_new_prop ?? 0) {
                                // close option-group
                                ?>
                                </optgroup>
                                <?php
                            }
                            ?>
                            </select>
                        <?php
                        if (!empty($channel_logo)) {
                            ?>
                            <span class="vbo-vcm-onboard-ota-logo-wrap">
                                <img src="<?php echo $channel_logo; ?>" alt="<?php echo JHtml::_('esc_attr', $to_channel['name']); ?>" class="vbo-vcm-onboard-ota-logo" />
                            </span>
                            <?php
                        }
                        if ($create_new_prop ?? 0) {
                            ?>
                            <span class="<?php echo $css_class_prefix; ?>-param-setting-comment"><?php echo JText::_('VCM_ONBOARD_LIST_NEW_OR_EXIST_HELP'); ?></span>
                            <?php
                        }
                        ?>
                        </div>
                    </div>

                    <div
                        class="<?php echo $css_class_prefix; ?>-param-container vbo-vcm-onboard-progress-wrap"
                        style="<?php echo !($onboarding_progress->summary ?? null) ? 'display: none;' : ''; ?>"
                        data-progress-accountid="<?php echo $onboarding_progress->hostId ?? $onboarding_progress->hotelId ?? ''; ?>"
                        data-progress-listingid="<?php echo $onboarding_progress->listingId ?? $onboarding_progress->roomTypeId ?? ''; ?>"
                    >
                        <div class="<?php echo $css_class_prefix; ?>-param-label">
                            <?php VikBookingIcons::e('hourglass-half'); ?>
                            <?php echo JText::_('VCM_ONBOARD_LIST_CURRENT_PROGRESS'); ?>
                        </div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <span class="label label-info vbo-vcm-onboard-progress-status"><?php echo $onboarding_progress->summary ?? ''; ?></span>
                        </div>
                    </div>

                </div>

                <div class="<?php echo $css_class_prefix; ?>-params-block">
                
                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="property">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCMPROPNAME'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <input type="text" name="onboarding[prop_name]" value="<?php echo JHtml::_('esc_attr', $listing_details->name ?? ''); ?>" />
                        </div>
                    </div>

                <?php
                if ($is_to_bcom) {
                    // prepare data from an existing Booking.com account
                    $listing_model = VCMOtaListing::getInstance();

                    try {
                        // get the legal entity ID from any Booking.com active accounts
                        $bcom_prop_details = $listing_model->getBookingcomHotelDetails();
                        $bcom_legal_entity_id = $bcom_prop_details->legal_entity_id ?? '';
                        $bcom_legal_from_name = $bcom_prop_details->property_name ?? '';

                        // get the "general" contact details from an existing Booking.com active account
                        $general_contact_details = [$listing_model->getBookingcomContactInfos()];
                    } catch (Exception $e) {
                        // silently catch the error
                        $bcom_legal_entity_id = '';
                        $bcom_legal_from_name = '';
                        $general_contact_details = null;
                    }

                    // build matching category groups
                    $match_cat_group = [];
                    if ($listing_details->property_type_group ?? '') {
                        $match_cat_group[] = $listing_details->property_type_group;
                    }
                    if ($listing_details->property_type_category ?? '') {
                        $match_cat_group[] = $listing_details->property_type_category;
                    }
                    if ($listing_details->room_type_category ?? '') {
                        $match_cat_group[] = $listing_details->room_type_category;
                    }
                    ?>
                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="property">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCM_ONBOARD_PROPERTY_CATEGORY'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <select name="onboarding[prop_category_code]" class="vbo-vcm-onboard-select2">
                                <option></option>
                            <?php
                            $has_matched = false;
                            foreach (VCMBookingcomContent::getPropertyClassTypes() as $cat_group) {
                                ?>
                                <optgroup label="<?php echo JHtml::_('esc_attr', $cat_group['category']); ?>">
                                <?php
                                foreach ($cat_group['list'] as $cat_code => $cat_name) {
                                    $is_matching = false;
                                    foreach ($match_cat_group as $from_cat_group) {
                                        if (stripos($from_cat_group, $cat_name) !== false || stripos($cat_name, $from_cat_group) !== false) {
                                            $is_matching = true;
                                            break;
                                        }
                                    }
                                    ?>
                                    <option value="<?php echo $cat_code; ?>"<?php echo $is_matching && !$has_matched ? ' selected="selected"' : ''; ?>><?php echo $cat_name; ?></option>
                                    <?php
                                    $has_matched = $has_matched || $is_matching;
                                }
                                ?>
                                </optgroup>
                                <?php
                            }
                            ?>
                            </select>
                        </div>
                    </div>

                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="property">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCMBCAHLEGALID'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <input type="text" name="onboarding[legal_entity_id]" value="<?php echo $bcom_legal_entity_id; ?>" readonly />
                            <input type="hidden" name="onboarding[contact_details]" value="<?php echo JHtml::_('esc_attr', json_encode($general_contact_details ?: [])); ?>" />
                            <span class="<?php echo $css_class_prefix; ?>-param-setting-comment"><?php echo JText::_('VCM_BCOM_LEGAL_ID_HELP') . ' ' . ($bcom_legal_entity_id ? JText::sprintf('VCM_BCOM_LEGAL_ID_EXAMPLE', $bcom_legal_from_name, $bcom_legal_entity_id) : ''); ?></span>
                        </div>
                    </div>
                    <?php
                }
                ?>

                </div>

                <div class="<?php echo $css_class_prefix; ?>-params-block">

                    <?php
                    if ($is_to_bcom) {
                        // get the preferred cancellation policy
                        $pref_canc_policy = null;
                        if ($listing_details->_bookingsettings->cancellation_policy_settings->cancellation_policy_category ?? null) {
                            $pref_canc_policy = $listing_details->_bookingsettings->cancellation_policy_settings->cancellation_policy_category;
                        }
                        ?>
                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="any">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCMBCARCROOMTYPE'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <select name="onboarding[room_type_code]" class="vbo-vcm-onboard-select2">
                                <option></option>
                            <?php
                            foreach (VCMBookingcomContent::getRoomTypeCodes() as $room_code => $room_type) {
                                $is_matching = ($room_code == '1');
                                ?>
                                <option value="<?php echo $room_code; ?>"<?php echo $is_matching ? ' selected="selected"' : ''; ?>><?php echo $room_type; ?></option>
                                <?php
                            }
                            ?>
                            </select>
                        <?php
                        if ($pref_canc_policy) {
                            ?>
                            <input type="hidden" name="onboarding[pref_canc_policy]" value="<?php echo $pref_canc_policy; ?>" />
                            <?php
                        }
                        ?>
                        </div>
                    </div>
                        <?php
                    }
                    ?>

                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="any">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCM_LISTING_NAME'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <input type="text" name="onboarding[listing_name]" value="<?php echo JHtml::_('esc_attr', ($is_same_channel ? ($vbo_listing['name'] ?? '') : ($listing_details->name ?? $listing_details->listing->name ?? ''))); ?>" />
                        </div>
                    </div>

                    <?php
                    if ($is_to_airbnb) {
                        // display property type group, property type category and room type category
                        $bcom_group_name  = '';
                        $default_group    = 'apartments';
                        $default_category = 'apartment';
                        if ($is_from_bcom && $listing_details->property->property_category ?? null) {
                            // get the B.com property category name
                            $bcom_group_name = VCMBookingcomContent::getPropertyClassTypes($listing_details->property->property_category, $get_type = false);
                        }
                        ?>
                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="any">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCM_PROP_TYPE_GROUP'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <select name="onboarding[property_type_group]" class="vbo-vcm-onboard-select2">
                            <?php
                            $group_matched = false;
                            foreach (VCMAirbnbContent::getListingPropertyTypeGroups('*') as $group => $categories) {
                                $group_beauty = ucwords(str_replace('_', ' ', $group));
                                $group_selected = false;
                                if (!$group_matched && stripos($group, $bcom_group_name) !== false) {
                                    $group_selected = true;
                                    $group_matched = true;
                                }
                                ?>
                                <option value="<?php echo JHtml::_('esc_attr', $group); ?>"<?php echo $group_selected || $default_group == $group ? ' selected="selected"' : ''; ?> data-categories="<?php echo JHtml::_('esc_attr', implode(',', $categories)); ?>"><?php echo $group_beauty; ?></option>
                                <?php
                            }
                            ?>
                            </select>
                        </div>
                    </div>

                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="any">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCM_PROP_TYPE_CAT'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <select name="onboarding[property_type_category]" class="vbo-vcm-onboard-select2">
                            <?php
                            foreach (VCMAirbnbContent::getListingPropertyTypeCategories() as $category) {
                                $category_beauty = ucwords(str_replace('_', ' ', $category));
                                ?>
                                <option value="<?php echo JHtml::_('esc_attr', $category); ?>"<?php echo $default_category == $category ? ' selected="selected"' : ''; ?>><?php echo $category_beauty; ?></option>
                                <?php
                            }
                            ?>
                            </select>
                        </div>
                    </div>

                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="any">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCM_ROOM_TYPE_CAT'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <select name="onboarding[room_type_category]" class="vbo-vcm-onboard-select2">
                            <?php
                            foreach (VCMAirbnbContent::getListingRoomTypeCategories() as $category => $category_beauty) {
                                $tn_key = 'VCM_LISTING_' . strtoupper($category);
                                $tn_cat = JText::_($tn_key);
                                ?>
                                <option value="<?php echo JHtml::_('esc_attr', $category); ?>"<?php echo $category === 'entire_home' && !strcasecmp($bcom_group_name, 'apartment') ? ' selected="selected"' : ''; ?>><?php echo $tn_cat != $tn_key ? $tn_cat : $category_beauty; ?></option>
                                <?php
                            }
                            ?>
                            </select>
                        </div>
                    </div>
                        <?php
                    }
                    ?>

                </div>

                <?php
                if ($is_to_airbnb) {
                    // display bedrooms, bathrooms and beds
                ?>
                <div class="<?php echo $css_class_prefix; ?>-params-block">

                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="any">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCM_BEDROOMS'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <input type="number" name="onboarding[bedrooms]" value="1" min="0" max="99" />
                        </div>
                    </div>

                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="any">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('BATHROOMS'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <input type="number" name="onboarding[bathrooms]" value="1" min="0" max="99" />
                        </div>
                    </div>

                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="any">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCM_LISTING_BEDS'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <input type="number" name="onboarding[beds]" value="1" min="0" max="99" />
                        </div>
                    </div>

                </div>
                <?php
                }
                ?>

                <div class="<?php echo $css_class_prefix; ?>-params-block">

                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="<?php echo $is_to_bcom ? 'property' : 'any' ;?>">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCMBCAHADDRESS'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <input type="text" name="onboarding[address]" value="<?php echo JHtml::_('esc_attr', $listing_details->street ?? $listing_details->address ?? $listing_details->property->physical_address->address_line ?? ''); ?>" />
                        </div>
                    </div>

                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="<?php echo $is_to_bcom ? 'property' : 'any' ;?>">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCMTACHOTELCITY'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <input type="text" name="onboarding[city]" value="<?php echo JHtml::_('esc_attr', $listing_details->city ?? $listing_details->property->physical_address->city_name ?? ''); ?>" />
                        </div>
                    </div>

                <?php
                if ($is_to_airbnb) {
                    // display the state
                    ?>
                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="any">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCMTACHOTELSTATE'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <input type="text" name="onboarding[state]" value="" />
                        </div>
                    </div>
                    <?php
                }
                ?>

                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="<?php echo $is_to_bcom ? 'property' : 'any' ;?>">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCMBCAHPOSCODE'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <input type="text" name="onboarding[postcode]" value="<?php echo JHtml::_('esc_attr', $listing_details->zipcode ?? $listing_details->zip ?? $listing_details->postcode ?? $listing_details->property->physical_address->postal_code ?? ''); ?>" />
                        </div>
                    </div>

                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="<?php echo $is_to_bcom ? 'property' : 'any' ;?>">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCMBCAHCOUNTRY'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <input type="text" name="onboarding[country]" value="<?php echo JHtml::_('esc_attr', $listing_details->country_code ?? $listing_details->property->physical_address->country_code ?? ''); ?>" />
                        </div>
                    </div>

                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="<?php echo $is_to_bcom ? 'property' : 'any' ;?>">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCMBCAHLATITUDE'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <input type="number" step="any" name="onboarding[latitude]" value="<?php echo JHtml::_('esc_attr', ($is_same_channel ? ($vbo_listing['geo']['latitude'] ?? '') : ($listing_details->latitude ?? $listing_details->lat ?? $listing_details->property->position->latitude ?? ''))); ?>" />
                        </div>
                    </div>

                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="<?php echo $is_to_bcom ? 'property' : 'any' ;?>">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCMBCAHLONGITUDE'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <input type="number" step="any" name="onboarding[longitude]" value="<?php echo JHtml::_('esc_attr', ($is_same_channel ? ($vbo_listing['geo']['longitude'] ?? '') : ($listing_details->longitude ?? $listing_details->lng ?? $listing_details->property->position->longitude ?? ''))); ?>" />
                        </div>
                    </div>

                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="<?php echo $is_to_bcom ? 'property' : 'any' ;?>">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCM_CHECKIN_START'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <select name="onboarding[checkin_start]">
                            <?php
                            $other_checkin_start = '15';
                            if ($listing_details->_bookingsettings->check_in_time_start ?? '') {
                                $other_checkin_start = $listing_details->_bookingsettings->check_in_time_start;
                            } elseif ($listing_details->property->check_in->from ?? '') {
                                $other_checkin_start = preg_replace("/:[0-9]{2}$/", '', $listing_details->property->check_in->from);
                            }
                            for ($h = 0; $h < 24; $h++) {
                                $read_h = ($h < 10 ? '0' : '') . $h . ':00';
                                ?>
                                <option value="<?php echo $read_h; ?>"<?php echo strpos($read_h, $other_checkin_start) !== false ? ' selected="selected"' : ''; ?>><?php echo $read_h; ?></option>
                                <?php
                            }
                            ?>
                            </select>
                        </div>
                    </div>

                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="<?php echo $is_to_bcom ? 'property' : 'any' ;?>">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCM_CHECKIN_END'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <select name="onboarding[checkin_end]">
                            <?php
                            $other_checkin_end = '20';
                            if ($listing_details->_bookingsettings->check_in_time_end ?? '') {
                                $other_checkin_end = $listing_details->_bookingsettings->check_in_time_end;
                            } elseif ($listing_details->property->check_in->until ?? '') {
                                $other_checkin_end = preg_replace("/:[0-9]{2}$/", '', $listing_details->property->check_in->until);
                            }
                            for ($h = 0; $h < 24; $h++) {
                                $read_h = ($h < 10 ? '0' : '') . $h . ':00';
                                ?>
                                <option value="<?php echo $read_h; ?>"<?php echo strpos($read_h, $other_checkin_end) !== false ? ' selected="selected"' : ''; ?>><?php echo $read_h; ?></option>
                                <?php
                            }
                            ?>
                            </select>
                        </div>
                    </div>

                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="<?php echo $is_to_bcom ? 'property' : 'any' ;?>">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCM_CHECKOUT_END'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <select name="onboarding[checkout_end]">
                            <?php
                            $other_checkout_end = '11';
                            if ($listing_details->_bookingsettings->check_out_time ?? '') {
                                $other_checkout_end = $listing_details->_bookingsettings->check_out_time;
                            } elseif ($listing_details->property->check_out->until ?? '') {
                                $other_checkout_end = preg_replace("/:[0-9]{2}$/", '', $listing_details->property->check_out->until);
                            }
                            for ($h = 0; $h < 24; $h++) {
                                $read_h = ($h < 10 ? '0' : '') . $h . ':00';
                                ?>
                                <option value="<?php echo $read_h; ?>"<?php echo strpos($read_h, $other_checkout_end) !== false ? ' selected="selected"' : ''; ?>><?php echo $read_h; ?></option>
                                <?php
                            }
                            ?>
                            </select>
                        </div>
                    </div>

                    <div class="<?php echo $css_class_prefix; ?>-param-container" data-type="<?php echo $is_to_bcom ? 'property' : 'any' ;?>">
                        <div class="<?php echo $css_class_prefix; ?>-param-label"><?php echo JText::_('VCMCONFCURNAME'); ?></div>
                        <div class="<?php echo $css_class_prefix; ?>-param-setting">
                            <input type="text" name="onboarding[currency]" value="<?php echo JHtml::_('esc_attr', $listing_details->_pricingsettings->listing_currency ?? $listing_details->property->currency_code ?? VikBooking::getCurrencyCodePp()); ?>" />
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

</form>

<script type="text/javascript">
    jQuery(function() {

        // prepare channel/host logos
        let channel_logo_url = '<?php echo (string) $channel_logo; ?>';
        let account_logo_url = <?php echo json_encode($account_logo_urls); ?>;

        // listen to the change event for the main account selection
        jQuery('select#vbo-vcm-onboard-account').on('change', function() {
            let account = jQuery(this).val();
            if (account === '') {
                // handle related input fields
                jQuery('.<?php echo $css_class_prefix; ?>-param-container[data-type="property"]').each(function(ind, elem) {
                    let field = jQuery(elem);
                    let field_block = field.closest('.<?php echo $css_class_prefix; ?>-params-block');
                    field.show();
                    field.find('input, select, textarea').not('.protected').prop('disabled', false);
                    if (field_block.length) {
                        field_block.show();
                    }
                });

                if (channel_logo_url) {
                    // always restore original channel logo
                    jQuery('.vbo-vcm-onboard-ota-logo').attr('src', channel_logo_url);
                }
            } else {
                // handle related input fields
                jQuery('.<?php echo $css_class_prefix; ?>-param-container[data-type="property"]').each(function(ind, elem) {
                    let field = jQuery(elem);
                    let field_block = field.closest('.<?php echo $css_class_prefix; ?>-params-block');
                    field.hide();
                    field.find('input, select, textarea').not('.protected').prop('disabled', true);
                    if (field_block.length && !field_block.find('.<?php echo $css_class_prefix; ?>-param-container[data-type="property"]:visible').length) {
                        field_block.hide();
                    }
                });

                // handle account logo URL, if any
                if (account_logo_url.hasOwnProperty(account)) {
                    // set account logo
                    jQuery('.vbo-vcm-onboard-ota-logo').attr('src', account_logo_url[account]);
                } else if (channel_logo_url) {
                    // restore original channel logo
                    jQuery('.vbo-vcm-onboard-ota-logo').attr('src', channel_logo_url);
                }
            }
        });

        // trigger the main account selection
        jQuery('#vbo-vcm-onboard-account').trigger('change');

        // listen to the change event for the Airbnb listing property type group
        jQuery('select[name="onboarding[property_type_group]"]').on('change', function() {
            let categories = jQuery(this).find('option:selected').attr('data-categories');
            if (!categories) {
                categories = [];
            } else {
                categories = categories.split(',');
            }
            let current_category = jQuery('select[name="onboarding[property_type_category]"]').val();
            if (current_category && categories.length && !categories.includes(current_category)) {
                // unset the value of the property type category
                jQuery('select[name="onboarding[property_type_category]"]').val('');
            }
            jQuery('select[name="onboarding[property_type_category]"]').find('option').each(function(key, elem) {
                if (categories.length && !categories.includes(jQuery(elem).attr('value'))) {
                    jQuery(elem).prop('disabled', true);
                } else {
                    jQuery(elem).prop('disabled', false);
                }
            });
            if (categories.length) {
                // set the first category available under this group
                jQuery('select[name="onboarding[property_type_category]"]').val(categories[0]);
            }
            setTimeout(() => {
                // update the categories drop-down by re-building the select2
                let categories_ddown = jQuery('select[name="onboarding[property_type_category]"]');
                try {
                    categories_ddown.select2('destroy');
                    categories_ddown.select2();
                } catch (e) {
                    // do nothing
                }
            }, 200);
        });

        // listen to the event for onboarding the progress status
        document.addEventListener('vbo-vcm-onboard-progress-updated', (e) => {
            if (!e || !e.detail || !e.detail.summary) {
                return;
            }
            document.querySelector('.vbo-vcm-onboard-progress-status').innerText = e.detail.summary;
            document.querySelector('.vbo-vcm-onboard-progress-wrap').style.display = '';
            if (e.detail.hostId || e.detail.hotelId) {
                document.querySelector('.vbo-vcm-onboard-progress-wrap').setAttribute('data-progress-accountid', e.detail.hostId || e.detail.hotelId);
            }
            if (e.detail.listingId || e.detail.roomTypeId) {
                document.querySelector('.vbo-vcm-onboard-progress-wrap').setAttribute('data-progress-listingid', e.detail.listingId || e.detail.roomTypeId);
            }
        });

        try {
            // attempt to register the select2 elements
            jQuery('.vbo-vcm-onboard-select2').select2();

            // trigger the change event for the Airbnb listing property type group
            setTimeout(() => {
                jQuery('select[name="onboarding[property_type_group]"]').trigger('change');
            }, 300);
        } catch (e) {
            // do nothing
        }

    });
</script>
