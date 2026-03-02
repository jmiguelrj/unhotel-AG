<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2026 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Bulk Action Processor implementation.
 * 
 * @since   1.9.16
 */
final class VCMBulkactionProcessor
{
    /**
     * @var  array
     */
    private array $options = [];

    /**
     * @var  array
     */
    private array $bulkRatesCache = [];

    /**
     * @var  array
     */
    private array $rooms_data = [];

    /**
     * @var  int
     */
    private int $max_rooms_per_rq = 5;

    /**
     * @var  int
     */
    private int $next_paging_offset = 0;

    /**
     * @var  bool
     */
    private bool $debug = false;

    /**
     * Constructor will bind processing options and prepare data for distribution.
     * 
     * @param   array   $options    Processing options to bind.
     * 
     * @throws  Exception
     */
    public function __construct(array $options = [])
    {
        // bind processing options
        $this->options = $options;

        // load bulk rates cache
        $this->bulkRatesCache = VikChannelManager::getBulkRatesCache();

        // prepare data for distribution
        $this->prepareDistrubution();
    }

    /**
     * Prepares data for distribution according to options set.
     * 
     * @return  void
     * 
     * @throws  Exception
     */
    private function prepareDistrubution()
    {
        $dbo = JFactory::getDbo();

        // validate dates
        if (empty($this->options['from']) || empty($this->options['to'])) {
            throw new InvalidArgumentException('Missing dates', 400);
        }

        $this->options['from_ts'] = strtotime($this->options['from']);
        $this->options['to_ts'] = strtotime($this->options['to']);
        if ($this->options['from_ts'] >= $this->options['to_ts']) {
            throw new InvalidArgumentException('Invalid dates', 400);
        }

        // set the end timestamp to midnight - 1 second
        $to_info = getdate($this->options['to_ts']);
        $this->options['to_ts'] = mktime(23, 59, 59, $to_info['mon'], $to_info['mday'], $to_info['year']);

        // normalize data to distribute ("rates" and/or "availability")
        $this->options['update'] = $this->options['update'] ?? null;

        // normalize forced rooms to update
        if ($this->options['forced_rooms']) {
            $this->options['forced_rooms'] = array_values(array_filter(array_map('intval', (array) $this->options['forced_rooms'])));
        }

        // check if the bulk rates cache should be filtered by channel identifier
        if (!empty($this->options['uniquekey'])) {
            foreach ($this->bulkRatesCache as $idr => $rplans) {
                foreach ($rplans as $idrp => $rplan) {
                    if (empty($rplan['channels'])) {
                        continue;
                    }
                    if (!in_array($this->options['uniquekey'], $rplan['channels'])) {
                        // unset room-rate-plan as not assigned to the requested channel
                        unset($this->bulkRatesCache[$idr][$idrp]);
                        continue;
                    }
                    // make sure the only channel is the requested one
                    $this->bulkRatesCache[$idr][$idrp]['channels'] = [$this->options['uniquekey']];
                }
                if (!$this->bulkRatesCache[$idr]) {
                    // unset room as not assigned to the requested channel
                    unset($this->bulkRatesCache[$idr]);
                }
            }
        }

        if (!$this->bulkRatesCache && $this->options['update'] != 'availability') {
            throw new Exception('No bulk rates cache for the requested channel identifier', 400);
        }

        // collect all VBO room IDs involved
        $vbo_rooms_involved = array_keys($this->bulkRatesCache);

        // if a specific channel identifier is requested, we attempt to fetch all rooms of this channel
        if (!empty($this->options['uniquekey'])) {
            $q = "SELECT DISTINCT `idroomvb` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . $this->options['uniquekey'] . ";";
            $dbo->setQuery($q);
            $channel_rooms = $dbo->loadAssocList();
            if (!$channel_rooms) {
                throw new Exception('No rooms found for the requested channel identifier', 400);
            }
            // reset rooms involved
            $vbo_rooms_involved = [];
            foreach ($channel_rooms as $ch_room) {
                // push room involved
                $vbo_rooms_involved[] = $ch_room['idroomvb'];
            }
        }

        // check if only some specific room ids were requested
        if (!empty($this->options['forced_rooms'])) {
            $vbo_rooms_involved = (array) $this->options['forced_rooms'];
        }

        // collect rooms data to process
        $this->rooms_data = [];

        $q = "SELECT `id`,`name`,`units` FROM `#__vikbooking_rooms` WHERE `id` IN (" . implode(',', array_map('intval', $vbo_rooms_involved)) . ");";
        $dbo->setQuery($q);
        $this->rooms_data = $dbo->loadAssocList();
        if (!$this->rooms_data) {
            throw new Exception('No rooms found', 404);
        }

        // load channels mapped for every room involved
        foreach ($this->rooms_data as $k => $r) {
            // start room channels container
            $this->rooms_data[$k]['channels'] = [];

            $q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idroomvb`=" . (int) $r['id'] . (!empty($this->options['uniquekey']) ? ' AND `idchannel`=' . $this->options['uniquekey'] : '') . ";";
            $dbo->setQuery($q);
            $channels_data = $dbo->loadAssocList();
            if (!$channels_data) {
                // unset room as it's no longer mapped to any channels
                unset($this->rooms_data[$k]);
                continue;
            }

            foreach ($channels_data as $ch_data) {
                if (isset($this->rooms_data[$k]['channels'][$ch_data['idchannel']])) {
                    // make sure to push unique channel IDs
                    continue;
                }
                $this->rooms_data[$k]['channels'][$ch_data['idchannel']] = $ch_data;
            }
        }

        if (!$this->rooms_data) {
            throw new Exception('No mapped rooms found', 404);
        }

        /**
         * Take care of paging to let all servers breath between the requests,
         * even though they are not consuming in terms of resources, they are
         * just POST-HTTP requests that may take seconds in total to complete.
         * What's important is to not surpass the size limit of POST requests,
         * and so paging is fundamental for the auto bulk actions.
         */
        $this->next_paging_offset = $this->options['paging'] ?? 0;
        $total_rooms_mapped = count($this->rooms_data);

        if ($total_rooms_mapped > $this->max_rooms_per_rq) {
            $slice_offset = $this->next_paging_offset * $this->max_rooms_per_rq;
            $this->rooms_data = array_slice($this->rooms_data, $slice_offset, $this->max_rooms_per_rq);
            if (($slice_offset + count($this->rooms_data)) < $total_rooms_mapped) {
                // slicing will require a next page
                $this->next_paging_offset++;
            } else {
                // slicing will NOT require a next page
                $this->next_paging_offset = 0;
            }
        } else {
            // no next page required
            $this->next_paging_offset = 0;
        }

        if (!$this->rooms_data) {
            throw new Exception('No rooms found after slicing', 404);
        }
    }

    /**
     * Sets the current debug status.
     * 
     * @param   bool    $status     True to enable debugging.
     * 
     * @return  self
     */
    public function setDebug(bool $status)
    {
        $this->debug = $status;

        return $this;
    }

    /**
     * Returns the next paging offset value.
     * 
     * @return  int
     */
    public function getNextPagingOffset()
    {
        return $this->next_paging_offset;
    }

    /**
     * Access a normalized processing option by key.
     * 
     * @param   string  $key        The processing option key.
     * @param   mixed   $default    Default value to return.
     * 
     * @return  mixed
     */
    public function getOption(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Performs a bulk action of type availability.
     * 
     * @return  true
     * 
     * @throws  Exception
     */
    public function distributeAvailability()
    {
        $dbo = JFactory::getDbo();

        // prepare environmental variables
        $from    = $this->options['from'];
        $to      = $this->options['to'];
        $from_ts = $this->options['from_ts'];
        $to_ts   = $this->options['to_ts'];

        /**
         * Global closing dates defined in Vik Booking are also considered. This is valid for the
         * "Bulk Action - Copy Availability" (CustAvailUpdateRQ + AutoBulk AV) requests only.
         */
        $glob_closing_dates = VikBooking::getClosingDates();
        $glob_closing_dates = !is_array($glob_closing_dates) ? [] : $glob_closing_dates;

        // compose availability intervals for each room in the set of dates
        foreach ($this->rooms_data as $k => $r) {
            $availability = [];

            // query the database to find the busy records
            $q = "SELECT `b`.*,`ob`.`idorder` FROM `#__vikbooking_busy` AS `b`,`#__vikbooking_ordersbusy` AS `ob` WHERE `b`.`idroom`=" . (int)$r['id'] . " AND `b`.`id`=`ob`.`idbusy` AND (`b`.`checkin`>={$from_ts} OR `b`.`checkout`>={$from_ts}) AND (`b`.`checkin`<={$to_ts} OR `b`.`checkout`<={$from_ts});";
            $dbo->setQuery($q);
            $arrbusy = $dbo->loadAssocList();

            // loop through all dates to build the availability nodes
            $nowts = getdate($from_ts);
            $node_from = date('Y-m-d', $nowts[0]);
            $node_to = '';
            $last_av = '';
            while ($nowts[0] < $to_ts) {
                // count units booked
                $totfound = 0;
                foreach ($arrbusy as $b) {
                    $tmpone = getdate($b['checkin']);
                    $ritts = mktime(0, 0, 0, $tmpone['mon'], $tmpone['mday'], $tmpone['year']);
                    $tmptwo = getdate($b['checkout']);
                    $conts = mktime(0, 0, 0, $tmptwo['mon'], $tmptwo['mday'], $tmptwo['year']);
                    if ($nowts[0] >= $ritts && $nowts[0] < $conts) {
                        $totfound++;
                    }
                }

                // check global closing dates
                foreach ($glob_closing_dates as $glob_closing_date) {
                    if (!is_array($glob_closing_date) || empty($glob_closing_date['from']) || empty($glob_closing_date['to'])) {
                        continue;
                    }
                    if ($nowts[0] >= $glob_closing_date['from'] && $nowts[0] <= $glob_closing_date['to']) {
                        // this date is closed at property-level
                        $totfound = $r['units'];
                        break;
                    }
                }

                // set remaining units
                $remaining = $r['units'] - $totfound;
                $remaining = $remaining < 0 ? 0 : $remaining;
                $last_av = strlen($last_av) <= 0 ? $remaining : $last_av;
                $node_to = empty($node_to) ? $node_from : $node_to;
                $nextdayts = mktime(0, 0, 0, $nowts['mon'], ($nowts['mday'] + 1), $nowts['year']);
                if ($last_av != $remaining) {
                    // push availability node
                    array_push($availability, $node_from . '_' . $node_to . '_' . $last_av);
                    // set vars for next loop
                    $last_av = $remaining;
                    $node_from = date('Y-m-d', $nowts[0]);
                }
                // next loop
                $node_to = date('Y-m-d', $nowts[0]);
                $nowts = getdate($nextdayts);
            }

            // push and close last node
            array_push($availability, $node_from . '_' . $node_to . '_' . $remaining);

            // set availability nodes for this room ID
            $this->rooms_data[$k]['availability'] = $availability;
        }

        /**
         * Build the CUSTA request for the e4jConnect servers like if
         * this was a regular Bulk Action - Copy Availability request.
         */
        $room_type_av_nodes = [];
        $autobulk_details = [];
        foreach ($this->rooms_data as $k => $r) {
            // collect the names of the channels updated for this room
            $upd_channels = [];
            foreach ($r['availability'] as $av_node) {
                list($from_date, $to_date, $tot_units) = explode('_', $av_node);
                foreach ($r['channels'] as $ch_id => $ch_data) {
                    // some channels may require the availability to be updated at rate-plan-level
                    $rateplanid = '0';
                    if ((int)$ch_id == (int)VikChannelManagerConfig::AGODA && !empty($ch_data['otapricing'])) {
                        $ota_pricing = json_decode($ch_data['otapricing'], true);
                        if (is_array($ota_pricing) && isset($ota_pricing['RatePlan'])) {
                            foreach ($ota_pricing['RatePlan'] as $rp_id => $rp_val) {
                                $rateplanid = $rp_id;
                                break;
                            }
                        }
                    }
                    // find the OTA account id for this room
                    $hotelid = '';
                    if (!empty($ch_data['prop_params'])) {
                        $prop_info = json_decode($ch_data['prop_params'], true);
                        if (isset($prop_info['hotelid'])) {
                            $hotelid = $prop_info['hotelid'];
                        } elseif (isset($prop_info['id'])) {
                            // useful for Pitchup.com to identify multiple accounts
                            $hotelid = $prop_info['id'];
                        } elseif (isset($prop_info['apikey'])) {
                            // useful for Pitchup.com, but it may be a good backup field for future channels to identify multiple accounts
                            $hotelid = $prop_info['apikey'];
                        } elseif (isset($prop_info['property_id'])) {
                            // useful for Hostelworld
                            $hotelid = $prop_info['property_id'];
                        } elseif (isset($prop_info['user_id'])) {
                            // useful for Airbnb API
                            $hotelid = $prop_info['user_id'];
                        }
                    }
                    // build RoomType node attributes
                    $rtype_attr = [
                        'id="' . $ch_data['idroomota'] . '"',
                        'rateplanid="' . $rateplanid . '"',
                        'idchannel="' . $ch_id . '"',
                        'newavail="' . $tot_units . '"',
                    ];
                    if (!empty($hotelid)) {
                        array_push($rtype_attr, 'hotelid="' . $hotelid . '"');
                    }
                    // push room type availability node
                    array_push($room_type_av_nodes, "\t\t" . '<RoomType ' . implode(' ', $rtype_attr) . '>');
                    array_push($room_type_av_nodes, "\t\t\t" . '<Day from="' . $from_date . '" to="' . $to_date . '"/>');
                    array_push($room_type_av_nodes, "\t\t" . '</RoomType>');
                    // push channel name
                    $channel_name = $ch_id == VikChannelManagerConfig::AIRBNBAPI ? 'Airbnb' : ucwords($ch_data['channel']);
                    $channel_name = $ch_id == VikChannelManagerConfig::GOOGLEHOTEL ? 'Google Hotel' : $channel_name;
                    $channel_name = $ch_id == VikChannelManagerConfig::GOOGLEVR ? 'Google VR' : $channel_name;
                    if (!in_array($channel_name, $upd_channels)) {
                        array_push($upd_channels, $channel_name);
                    }
                }
            }

            // push auto-bulk detail
            array_push($autobulk_details, $r['name'] . ': ' . $from . ' - ' . $to . ' (' . implode(', ', $upd_channels) . ')');
        }

        // make the request to the e4jConnect servers
        $e4jc_url = "https://e4jconnect.com/channelmanager/?r=custa&c=channels";

        // generate a new notification key
        $nkey = VikChannelManager::generateNKey(0);

        // store notification
        $result_str = 'e4j.OK.Channels.AUTOBULKCUSTAR_RQ' . "\n" . implode("\n", $autobulk_details);
        $q = "INSERT INTO `#__vikchannelmanager_notifications` (`ts`,`type`,`from`,`cont`,`read`) VALUES(" . $dbo->q(time()) . ", '1', 'VCM', " . $dbo->q($result_str) . ", 0);";
        $dbo->setQuery($q);
        $dbo->execute();
        $id_notification = $dbo->insertId();

        // update notification key
        VikChannelManager::updateNKey($nkey, $id_notification);

        // compose XML request
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager AUTOBULK CUSTA Request e4jConnect.com - Channels Module -->
<CustAvailUpdateRQ xmlns="http://www.e4jconnect.com/channels/custarq">
    <Notify client="' . JUri::root() . '" nkey="' . $nkey . '"/>
    <Api key="' . VikChannelManager::getApiKey() . '"/>
    <AvailUpdate>
' . implode("\n", $room_type_av_nodes) . '
    </AvailUpdate>
</CustAvailUpdateRQ>';

        $e4jC = new E4jConnectRequest($e4jc_url);
        $e4jC->setPostFields($xml);
        $e4jC->slaveEnabled = true;
        $rs = $e4jC->exec();
        if ($e4jC->getErrorNo()) {
            throw new Exception(VikChannelManager::getErrorFromMap('e4j.error.Curl:Error #' . $e4jC->getErrorNo() . ' ' . $e4jC->getErrorMsg()), 500);
        }

        if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
            throw new Exception(preg_replace('/^e4j\.(error|warning)\.?/', '', $rs), 500);
        }

        // return the next paging offset value
        return $this->next_paging_offset;
    }

    /**
     * Performs a bulk action of type rates and restrictions.
     * 
     * @param   bool    $getObject  True to return the decoded result object, if success.
     * 
     * @return  mixed               False, JSON-encoded result string or decoded object.
     * 
     * @throws  Exception
     */
    public function distributeRates(bool $getObject = false)
    {
        $dbo = JFactory::getDbo();

        if (!$this->bulkRatesCache) {
            throw new Exception('No bulk rates cache', 400);
        }

        // prepare environmental variables
        $from    = $this->options['from'];
        $to      = $this->options['to'];
        $from_ts = $this->options['from_ts'];
        $to_ts   = $this->options['to_ts'];
        $rate_id = $this->options['rate_id'] ?? 0;

        /**
         * Auto-bulk actions for specific rooms should never exclude listings or channels
         * because of missing rate plans mapping data in the bulk rates cache.
         * 
         * @since   1.9.6
         */
        if ($this->options['forced_rooms']) {
            // scan all rooms data
            foreach ($this->rooms_data as $k => $r) {
                // determine if the bulk rates cache is available or should be set
                $set_listing_rates_cache = false;
                $listing_glob_rplans = VBORoomHelper::getInstance()->getRatePlans($r['id']);
                $listing_glob_rplans = VikBooking::sortRatePlans($listing_glob_rplans);
                $listing_glob_rplan_ids = array_column($listing_glob_rplans, 'id');
                $listing_assoc_rplans = [];
                foreach ($listing_glob_rplans as $listing_glob_rplan) {
                    $listing_assoc_rplans[$listing_glob_rplan['id']] = $listing_glob_rplan;
                }

                if (!isset($this->bulkRatesCache[$r['id']])) {
                    // cache is completely missing
                    $set_listing_rates_cache = true;
                } elseif (!empty($rate_id) && !isset($this->bulkRatesCache[$r['id']][$rate_id])) {
                    // cache missing for the forced rate plan
                    $set_listing_rates_cache = true;
                } else {
                    // check if cache is missing for some channels
                    $main_rate_id = $listing_glob_rplan_ids[0];
                    if (!isset($this->bulkRatesCache[$r['id']][$main_rate_id]['channels']) || count($this->bulkRatesCache[$r['id']][$main_rate_id]['channels']) != count($r['channels'])) {
                        // not all mapped channels are available in the bulk rates cache
                        $set_listing_rates_cache = true;
                    }
                }

                if ($set_listing_rates_cache === true) {
                    // no (or partial) bulk rates cache found for this forced room

                    // check if we can actually populate the bulk rates cache
                    if (!empty($rate_id)) {
                        // forced rate plan ID
                        if (!in_array($rate_id, $listing_glob_rplan_ids)) {
                            // this combination of room and rate plan ID cannot be updated
                            continue;
                        }
                        foreach ($listing_glob_rplans as $listing_glob_rplan) {
                            if ($listing_glob_rplan['id'] == $rate_id && !empty($listing_glob_rplan['derived_id'])) {
                                // this combination of room and rate plan ID cannot be updated, because derived
                                continue 2;
                            }
                        }
                    } elseif (!empty($listing_glob_rplans[0]['derived_id'])) {
                        // main rate plan is derived
                        continue;
                    }

                    // determine the rate plan for which cache should be populated (either the forced one or the main one)
                    $populate_rate_id = (int) (!empty($rate_id) ? $rate_id : $listing_glob_rplan_ids[0]);

                    // determine listing rate plan default rate
                    $lrplan_def_rate = (float) ($listing_assoc_rplans[$populate_rate_id]['cost'] ?? 0);
                    if ($lrplan_def_rate < 0) {
                        continue;
                    }

                    // check if we have a similar rates cache to copy some values from
                    $copy_rates_cache_data = [];
                    if (!isset($this->bulkRatesCache[$r['id']])) {
                        // start listing cache container
                        $this->bulkRatesCache[$r['id']] = [];
                    } elseif (isset($this->bulkRatesCache[$r['id']][$populate_rate_id]['channels'])) {
                        // some channels have been cached, but not all, copy some settings
                        $copy_rates_cache_data = $this->bulkRatesCache[$r['id']][$populate_rate_id];
                    }

                    // set listing rate plan cache container
                    $this->bulkRatesCache[$r['id']][$populate_rate_id] = [
                        'pricetype'     => $populate_rate_id,
                        'defrate'       => $lrplan_def_rate,
                        'rmod'          => ($copy_rates_cache_data['rmod'] ?? 0),
                        'rmodop'        => ($copy_rates_cache_data['rmodop'] ?? 1),
                        'rmodamount'    => ($copy_rates_cache_data['rmodamount'] ?? 0),
                        'rmodval'       => ($copy_rates_cache_data['rmodval'] ?? 0),
                        'rplans'        => ($copy_rates_cache_data['rplans'] ?? []),
                        'cur_rplans'    => ($copy_rates_cache_data['cur_rplans'] ?? []),
                        'rplanarimode'  => ($copy_rates_cache_data['rplanarimode'] ?? []),
                        'rmod_channels' => ($copy_rates_cache_data['rmod_channels'] ?? []),
                        'channels'      => array_keys($r['channels']),
                    ];

                    // determine for which channels we need to fetch the rate plan IDs
                    $populate_ota_rplans_cache = array_diff(array_keys($r['channels']), ($copy_rates_cache_data['channels'] ?? []));
                    foreach ($populate_ota_rplans_cache as $missing_ota_id) {
                        if (!isset($r['channels'][$missing_ota_id]) || empty($r['channels'][$missing_ota_id]['otapricing'])) {
                            // no channel pricing information
                            continue;
                        }
                        $missing_ota_pricing = $r['channels'][$missing_ota_id]['otapricing'];
                        if (!is_array($missing_ota_pricing)) {
                            $missing_ota_pricing = (array) json_decode($missing_ota_pricing, true);
                        }
                        if (!$missing_ota_pricing || empty($missing_ota_pricing['RatePlan'])) {
                            // unexpected format
                            continue;
                        }

                        // check if OTA pricing data should be sorted
                        if ($missing_ota_id == VikChannelManagerConfig::EXPEDIA) {
                            // make sure to sort the Expedia rate plans accordingly
                            $missing_ota_pricing = VikChannelManager::sortExpediaChannelPricing($missing_ota_pricing);
                        }

                        // check if OTA rate plan data should be set
                        if ($missing_ota_id == VikChannelManagerConfig::BOOKING && !($this->bulkRatesCache[$r['id']][$populate_rate_id]['rplanarimode'][$missing_ota_id] ?? [])) {
                            // Default Pricing is used by default, when no data available
                            $this->bulkRatesCache[$r['id']][$populate_rate_id]['rplanarimode'][$missing_ota_id] = 'person';
                        }

                        // use the very first OTA rate plan ID
                        $ota_first_rpid = '';
                        foreach ($missing_ota_pricing['RatePlan'] as $ota_rp_id => $ota_rp_data) {
                            $ota_first_rpid = $ota_rp_id;
                            break;
                        }

                        // set channel rate plan to be updated within the bulk rates cache
                        $this->bulkRatesCache[$r['id']][$populate_rate_id]['rplans'][$missing_ota_id] = $ota_first_rpid;
                    }
                }
            }
        }

        // build a map of room rate plan IDs in the proper ordering
        $room_rplan_ids_map = [];
        foreach ($this->rooms_data as $k => $r) {
            // ensure bulk rates cache is available for this room
            if (!isset($this->bulkRatesCache[$r['id']])) {
                // should not happen, as rooms get loaded exactly from the bulk rates cache
                unset($this->rooms_data[$k]);
                continue;
            }

            // gather and sort the room rate plan IDs by the highest number of channels involved
            $rplans_count_map = [];
            foreach ($this->bulkRatesCache[$r['id']] as $rplan_id => $rplan_cache) {
                $rplans_count_map[$rplan_id] = count($rplan_cache['channels']);
            }
            // sort map in reverse order and keep keys association
            arsort($rplans_count_map);

            // grab all rate plan IDs in the proper ordering
            $room_rplan_ids = array_keys($rplans_count_map);
            if (!empty($rate_id) && isset($this->bulkRatesCache[$r['id']][$rate_id])) {
                // forced rate-plan ID will be prepended and used
                array_unshift($room_rplan_ids, $rate_id);
            }

            // set sorted room rate plan IDs map
            $room_rplan_ids_map[$r['id']] = $room_rplan_ids;
        }

        /**
         * If one channel has got bulk rates cache for multiple rate plans, duplicate the
         * room data information so that the same channel will be updated once per rate plan.
         * 
         * @since   1.9.16
         */
        if (empty($rate_id)) {
            // we update all OTA rate plans, not only the "best" one, if we detect multiple rate plans
            $duplicate_rooms_data = [];
            foreach ($this->rooms_data as $r) {
                $tot_room_rplans = count($room_rplan_ids_map[$r['id']]);
                for ($d = 1; $d < $tot_room_rplans; $d++) {
                    // push duplicate room data for the additional rate plan to parse
                    $duplicate_rooms_data[] = $r;
                }
            }

            if ($duplicate_rooms_data) {
                // append the duplicate rooms data information
                $this->rooms_data = array_merge($this->rooms_data, $duplicate_rooms_data);

                // sort rooms data list by room ID to allow caching of
                // rates and restrictions for duplicate rooms
                usort($this->rooms_data, function($a, $b) {
                    return $a['id'] <=> $b['id'];
                });
            }
        }

        // ensure we've got a linear array of room data information
        // to avoid duplicate fetching of records for the same room
        $this->rooms_data = array_values($this->rooms_data);

        // scan all rooms involved
        $price_glob_minlos = VikBooking::getDefaultNightsCalendar();
        $price_glob_minlos = $price_glob_minlos < 1 ? 1 : $price_glob_minlos;
        foreach ($this->rooms_data as $k => $r) {
            // build rates inventory and push data information
            $ratesinventory = [];
            $pushdata = [];

            // determine the rate plan ID to use for the current loop
            $use_rplan_id = array_shift($room_rplan_ids_map[$r['id']]);

            // obtain the information about the current rate plan ID
            $pricetype_info = VikBooking::getPriceInfo((int) $use_rplan_id);
            if (!$pricetype_info) {
                // this could happen if rate plans get changed
                unset($this->rooms_data[$k]);
                continue;
            }

            // adopt Min LOS at rate-plan level, if set
            $glob_minlos = $price_glob_minlos;
            if (isset($pricetype_info['minlos']) && (int)$pricetype_info['minlos'] >= 1) {
                $glob_minlos = (int)$pricetype_info['minlos'];
            }

            // set data from current room rate plan in bulk rates cache
            $pushdata = $this->bulkRatesCache[$r['id']][$pricetype_info['id']];
            // inject the name of the website rate plan used for reading the rates (useful for logging the notification)
            $pushdata['rplan_name'] = $pricetype_info['name'];

            // set necessary vars
            $start_ts = $from_ts;
            $end_ts_base = strtotime($to);
            $end_ts = $to_ts;
            $cur_year = date('Y', $start_ts);

            // get room restriction and pricing records only once, to support duplicate room entries
            if (!$k || $r['id'] != ($this->rooms_data[($k - 1)]['id'] ?? 0)) {
                // load room restriction records
                $all_restrictions = VikBooking::loadRestrictions(true, [$r['id']]);

                // get seasonal prices to build the nodes (dates intervals)
                $all_seasons = [];

                /**
                 * Preload all seasonal rates and cache them internally. A lot of memory will be required, but
                 * it will significantly reduce the CPU usage. Useful for large sets of data.
                 * 
                 * @since   1.9.4  manual query with multiple where statements was changed to the native method.
                 * @since   1.9.10 unset preloaded and cached seasons to prevent conflicts with other processes.
                 */
                VikBooking::preloadSeasonRecords([$r['id']], false);
                $seasons = VikBooking::getDateSeasonRecords($from_ts, ($to_ts + (10 * 3600)), [$r['id']]);

                foreach ($seasons as $sk => $s) {
                    $now_year = !empty($s['year']) ? $s['year'] : $cur_year;
                    if (!empty($s['from']) || !empty($s['to'])) {
                        list($sfrom, $sto) = VikBooking::getSeasonRangeTs($s['from'], $s['to'], $now_year);
                    } else {
                        // only weekdays and no dates filter
                        list($sfrom, $sto) = [$start_ts, $end_ts];
                    }
                    $info_sfrom = getdate($sfrom);
                    $info_sto = getdate($sto);
                    $sfrom = mktime(0, 0, 0, $info_sfrom['mon'], $info_sfrom['mday'], $info_sfrom['year']);
                    $sto = mktime(0, 0, 0, $info_sto['mon'], $info_sto['mday'], $info_sto['year']);
                    if ($start_ts > $sfrom && $start_ts > $sto && $end_ts > $sto && empty($s['year'])) {
                        $now_year += 1;
                        list($sfrom, $sto) = VikBooking::getSeasonRangeTs($s['from'], $s['to'], $now_year);
                        $info_sfrom = getdate($sfrom);
                        $info_sto = getdate($sto);
                        $sfrom = mktime(0, 0, 0, $info_sfrom['mon'], $info_sfrom['mday'], $info_sfrom['year']);
                        $sto = mktime(0, 0, 0, $info_sto['mon'], $info_sto['mday'], $info_sto['year']);
                    }
                    if (($start_ts >= $sfrom && $start_ts <= $sto) || ($end_ts >= $sfrom && $end_ts <= $sto) || ($start_ts < $sfrom && $end_ts > $sto)) {
                        $s['info_from_ts'] = $info_sfrom;
                        $s['from_ts'] = $sfrom;
                        $s['to_ts'] = $sto;
                        // push season
                        array_push($all_seasons, $s);
                    }
                }

                // free up memory load
                unset($seasons);

                if (!$all_seasons && $all_restrictions) {
                    // when no valid special prices but only restrictions, add a fake node to the empty seasons array
                    $fake_season = [];
                    // the ID of this fake season must be negative for identification
                    $fake_season['id'] = -2;
                    $fake_season['diffcost'] = 0;
                    $fake_season['spname'] = 'Restrictions Placeholder';
                    $fake_season['wdays'] = '';
                    $fake_season['losoverride'] = '';
                    $fake_season['info_from_ts'] = getdate($start_ts);
                    $fake_season_start_ts = $start_ts;
                    $fake_season_end_ts = $end_ts_base;
                    // if one restriction only of type range, take its start/end dates rather than the ones of the update for the bulk action to avoid problems with shorter restrictions
                    $full_season_restr = VikBooking::parseSeasonRestrictions($start_ts, $end_ts_base, 1, $all_restrictions);
                    if (count($full_season_restr) && array_key_exists('range', $all_restrictions)) {
                        foreach ($all_restrictions['range'] as $restrs) {
                            if ($restrs['id'] == $full_season_restr['id']) {
                                if ($restrs['dfrom'] >= $start_ts && $restrs['dto'] <= $end_ts_base) {
                                    $fake_season_start_ts = $restrs['dfrom'];
                                    $fake_season_end_ts = $restrs['dto'];
                                }
                                break;
                            }
                        }
                    }
                    //
                    $fake_season['from_ts'] = $fake_season_start_ts;
                    $fake_season['to_ts'] = $fake_season_end_ts;
                    // push season
                    array_push($all_seasons, $fake_season);
                }

                // rate plan closing dates
                $room_rplan_closingd = [];
                if (method_exists('VikBooking', 'getRoomRplansClosingDates')) {
                    $room_rplan_closingd = VikBooking::getRoomRplansClosingDates($r['id']);
                }

                /**
                 * Attempt to preload and cache the week-day seasonal records with no dates.
                 * 
                 * @since   1.9.15
                 */
                $cached_wdayseasons = [];
                if (method_exists('VikBooking', 'getWdaySeasonRecords')) {
                    $cached_wdayseasons = VikBooking::getWdaySeasonRecords();
                }
            }

            // full scan of rates and restrictions, day by day from the start date to the end date
            $roomrate = [
                'idroom'   => $r['id'],
                'days'     => 1,
                'idprice'  => $pushdata['pricetype'],
                'cost'     => $pushdata['defrate'],
                'attrdata' => '',
                'name'     => $pushdata['pricetype']
            ];
            $nowts = getdate($start_ts);
            $node_from = date('Y-m-d', $nowts[0]);
            $last_node_to = '';
            $datecost_pool = [];
            // restrictions fix for inclusive end day of restriction, which requires check-in time to be at midnight for parseSeasonRestrictions()
            $hours_in = 12;
            $hours_out = 10;
            $secs_in = $hours_in * 3600; // fake checkin time set at 12:00:00 for seasonal rates, not for restrictions
            $secs_out = $hours_out * 3600; // fake checkout time set at 10:00:00 for seasonal rates, not for restrictions
            // compose the rates inventory nodes by calculating the cost and restriction for each day
            while ($nowts[0] <= $end_ts) {
                $datekey = date('Y-m-d', $nowts[0]);
                $prevdatekey = date('Y-m-d', mktime(0, 0, 0, $nowts['mon'], ($nowts['mday'] - 1), $nowts['year']));

                $today_tsin = mktime($hours_in, 0, 0, $nowts['mon'], $nowts['mday'], $nowts['year']);
                $today_tsout = mktime($hours_out, 0, 0, $nowts['mon'], ($nowts['mday'] + 1), $nowts['year']);
                // apply seasonal rates
                $tars = VikBooking::applySeasonsRoom([$roomrate], $today_tsin, $today_tsout, [], $all_seasons, $cached_wdayseasons);
                // parse restrictions
                $day_restr = VikBooking::parseSeasonRestrictions(($today_tsin - $secs_in), ($today_tsout - $secs_out), 1, $all_restrictions);
                $setminlos = $glob_minlos;
                $setmaxlos = 0;
                $cta_ctd_op = '';
                if ($day_restr) {
                    if (strlen($day_restr['minlos']) > 0) {
                        $setminlos = $day_restr['minlos'];
                    }
                    if (isset($day_restr['maxlos']) && strlen($day_restr['maxlos']) > 0) {
                        $setmaxlos = $day_restr['maxlos'];
                    }
                    // information about CTA and CTD will be transmitted next to Min LOS
                    if (isset($day_restr['cta']) && $day_restr['cta']) {
                        if (in_array("-{$nowts['wday']}-", $day_restr['cta'])) {
                            // use CTA only if the current weekday is affected
                            $cta_ctd_op .= 'CTA['.implode(',', $day_restr['cta']).']';
                        }
                    }
                    if (isset($day_restr['ctd']) && $day_restr['ctd']) {
                        if (in_array("-{$nowts['wday']}-", $day_restr['ctd'])) {
                            // use CTD only if the current weekday is affected
                            $cta_ctd_op .= 'CTD['.implode(',', $day_restr['ctd']).']';
                        }
                    }
                    $setminlos .= $cta_ctd_op;
                }

                // for array-comparison, both $setminlos and $setmaxlos should always be strings,
                // never use integers or a different type than string may not compare them correctly
                $setminlos = (string) $setminlos;
                $setmaxlos = (string) $setmaxlos;

                // if the rate plan is closed on this day, we use the maxlos to transmit
                // this information, and to compare the node with the other days
                if (isset($room_rplan_closingd[$pushdata['pricetype']]) && in_array($datekey, $room_rplan_closingd[$pushdata['pricetype']])) {
                    $setmaxlos .= 'closed';
                }

                // memorize restrictions values for this day even if the array was empty
                $day_restr['scan'] = [
                    $setminlos,
                    $setmaxlos,
                ];
                
                $datecost_pool[$datekey] = [
                    'c' => $tars[0]['cost'],
                    'r' => $day_restr,
                ];

                if (isset($datecost_pool[$prevdatekey])) {
                    if ($datecost_pool[$prevdatekey]['c'] != $datecost_pool[$datekey]['c'] || $datecost_pool[$prevdatekey]['r'] != $datecost_pool[$datekey]['r']) {
                        // cost or restriction has changed, so close previous node
                        array_push($ratesinventory, $node_from . '_' . $prevdatekey . '_' . $datecost_pool[$prevdatekey]['r']['scan'][0] . '_' . $datecost_pool[$prevdatekey]['r']['scan'][1] . '_' . $pushdata['rmod'] . '_' . $pushdata['rmodop'] . '_' . $pushdata['rmodamount'] . '_' . $pushdata['rmodval']);
                        // update variables for next loop
                        $node_from = $datekey;
                        $last_node_to = $prevdatekey;
                    }
                }

                // go to next loop
                $nowts = getdate(mktime(0, 0, 0, $nowts['mon'], ($nowts['mday'] + 1), $nowts['year']));
            }

            // finalize loop
            $datekeyend = date('Y-m-d', $end_ts);
            if ($node_from != $datekeyend || $last_node_to != $datekeyend) {
                array_push($ratesinventory, $node_from . '_' . $datekeyend . '_' . $datecost_pool[$node_from]['r']['scan'][0] . '_' . $datecost_pool[$node_from]['r']['scan'][1] . '_' . $pushdata['rmod'] . '_' . $pushdata['rmodop'] . '_' . $pushdata['rmodamount'] . '_' . $pushdata['rmodval']);
            }

            /**
             * Assign rates inventory and push data (rates cache) to room.
             * 
             * @since   1.9.10  added support to "pricing" associative array.
             */
            $this->rooms_data[$k]['ratesinventory'] = $ratesinventory;
            $this->rooms_data[$k]['pushdata'] = $pushdata;
            $this->rooms_data[$k]['pricing'] = array_combine(array_keys($datecost_pool), array_column($datecost_pool, 'c'));
        }

        // free up memory load
        unset($all_seasons, $all_restrictions);

        // loop again through all rooms to build the final data and push their rates to the various channels
        $channels = [];
        $chrplans = [];
        $nodes    = [];
        $room_ids = [];
        $pushvars = [];
        $pricing  = [];
        $autobulk_details = [];
        foreach ($this->rooms_data as $k => $r) {
            // push channels
            array_push($channels, implode(',', $r['pushdata']['channels']));
            // build channel rate plans
            $channels_rplans = [];
            foreach ($r['pushdata']['channels'] as $ch_id) {
                $ch_rplan = array_key_exists($ch_id, $r['pushdata']['rplans']) ? $r['pushdata']['rplans'][$ch_id] : '';
                $ch_rplan .= array_key_exists($ch_id, $r['pushdata']['rplanarimode']) ? '=' . $r['pushdata']['rplanarimode'][$ch_id] : '';
                $ch_rplan .= array_key_exists($ch_id, $r['pushdata']['cur_rplans']) && !empty($r['pushdata']['cur_rplans'][$ch_id]) ? ':' . $r['pushdata']['cur_rplans'][$ch_id] : '';
                // push channel rate plan string
                array_push($channels_rplans, $ch_rplan);
            }
            // push channel rate plans
            array_push($chrplans, implode(',', $channels_rplans));
            // push inventory nodes
            array_push($nodes, implode(';', $r['ratesinventory']));
            // push room
            array_push($room_ids, $r['id']);
            // push rate plan and default rate vars
            array_push($pushvars, implode(';', [$r['pushdata']['pricetype'], $r['pushdata']['defrate']]));
            // push pricing details for better caching purposes
            array_push($pricing, $r['pricing']);
            // push auto-bulk detail for the notification
            $upd_channels = [];
            foreach ($r['pushdata']['channels'] as $upd_ch_id) {
                foreach ($r['channels'] as $ch_id => $ch_data) {
                    if ($ch_id != $upd_ch_id) {
                        continue;
                    }
                    $channel_name = $ch_id == VikChannelManagerConfig::AIRBNBAPI ? 'Airbnb' : ucwords($ch_data['channel']);
                    $channel_name = $ch_id == VikChannelManagerConfig::GOOGLEHOTEL ? 'Google Hotel' : $channel_name;
                    $channel_name = $ch_id == VikChannelManagerConfig::GOOGLEVR ? 'Google VR' : $channel_name;
                    if (!in_array($channel_name, $upd_channels)) {
                        array_push($upd_channels, $channel_name);
                    }
                }
            }
            array_push($autobulk_details, $r['name'] . ' - ' . $r['pushdata']['rplan_name'] . ': ' . $from . ' - ' . $to . ' (' . implode(', ', $upd_channels) . ')');
        }

        // invoke the connector
        $vboConnector = VikChannelManager::getVikBookingConnectorInstance();

        if ($this->debug) {
            // turn debug mode on
            $vboConnector->setDebug(true);
        }

        // compose and make the request to e4jConnect by setting the cached pricing information
        $rates_result = $vboConnector
            ->setPricingData($pricing)
            ->channelsRatesPush($channels, $chrplans, $nodes, $room_ids, $pushvars);

        if ($this->debug) {
            // return the debug operation values
            return [
                'xml_data' => $vboConnector->getXmlData(),
                'channels' => $channels,
                'chrplans' => $chrplans,
                'nodes'    => $nodes,
                'room_ids' => $room_ids,
                'pushvars' => $pushvars,
                'pricing'  => $pricing,
            ];
        }

        // parse response
        $rates_response = null;
        if ($rates_result !== false) {
            // attempt to decode the response object
            $rates_response = json_decode($rates_result);
        }

        // check if errors were set
        $notif_type = 1;
        if ($vc_error = $vboConnector->getError(true)) {
            // push error description
            array_push($autobulk_details, $vc_error);
            // notification type should be error
            $notif_type = 0;
        }

        // check the response to build the child notifications
        $child_notifications = [];
        if (is_object($rates_response)) {
            // parse all channel results to store child notifications in VCM
            foreach ($rates_response as $room_id => $channels_res) {
                foreach ($channels_res as $ch_id => $ch_res) {
                    if (!is_numeric($ch_id) || !is_string($ch_res)) {
                        // this is probably the "breakdown" property for the first channel updated
                        continue;
                    }
                    $ch_res_type = (stripos($ch_res, 'e4j.OK') !== false ? 1 : 0);
                    // set one child notification per channel, not also per-room
                    if (!isset($child_notifications[$ch_id]) || !$ch_res_type) {
                        // define channel response or override channel response in case of error
                        $notif_cont = isset($child_notifications[$ch_id]) ? ($child_notifications[$ch_id]['cont'] . "\n" . $ch_res) : $ch_res;
                        // set values
                        $child_notifications[$ch_id] = [
                            'type' => $ch_res_type,
                            'cont' => $notif_cont,
                        ];
                    }
                    if (!$ch_res_type) {
                        // parent notification type should be "error"
                        $notif_type = 0;
                        // if it's actually a warning we should set the proper type
                        if (stripos($ch_res, 'e4j.warning') !== false) {
                            $notif_type = 2;
                        }
                    }
                }
            }
        }

        // rates distribution is a sync request, so notifications can be skipped
        if ($this->options['notifications'] ?? null) {
            // generate a new notification key
            $nkey = VikChannelManager::generateNKey(0);

            // always store a parent notification because the request was attempted
            $result_str = 'e4j.OK.Channels.AUTOBULKRAR_RQ' . "\n" . implode("\n", $autobulk_details);
            $q = "INSERT INTO `#__vikchannelmanager_notifications` (`ts`,`type`,`from`,`cont`,`read`) VALUES(" . $dbo->q(time()) . ", {$notif_type}, 'VCM', " . $dbo->q($result_str) . ", 0);";
            $dbo->setQuery($q);
            $dbo->execute();
            $id_notification = $dbo->insertId();

            // update notification key
            VikChannelManager::updateNKey($nkey, $id_notification);

            // store child notifications, if any
            foreach ($child_notifications as $ch_id => $child_notif) {
                $child_obj = new stdClass;
                $child_obj->id_parent = $id_notification;
                $child_obj->type = $child_notif['type'];
                $child_obj->cont = $child_notif['cont'];
                $child_obj->channel = $ch_id;
                $dbo->insertObject('#__vikchannelmanager_notification_child', $child_obj, 'id');
            }
        }

        // return the rates result variable
        return $getObject ? ($rates_response ?: false) : $rates_result;
    }
}
