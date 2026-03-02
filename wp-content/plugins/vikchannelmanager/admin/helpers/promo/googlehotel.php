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

/**
 * Google Hotel handler for the Promotions.
 * This particular promotion handler does not come with any interface
 * to manage its promotions, unlike other handlers that have their own
 * management interface to create, update or delete promotions.
 * The promotions for Google Hotel are supposed to be synced automatically
 * with the website promotions, as long as this channel/handler is selected
 * during the creation of a new promotion ID on VBO.
 * 
 * @since 	1.8.4
 */
class VikChannelManagerPromoGooglehotel extends VikChannelManagerPromo
{
	/**
	 * Class constructor defines the name, the key and the logo.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->handler_name = 'Google Hotel';
		$this->handler_key = basename(__FILE__, '.php');
		$this->handler_logo = VCM_ADMIN_URI . 'assets/css/channels/googlehotel.png';
	}

	/**
	 * Whether this channel is active in VCM
	 * 
	 * @return 	boolean 	true if the channel has been configured, false otherwise.
	 */
	public function isActive()
	{
		$channel_info = VikChannelManager::getChannel(VikChannelManagerConfig::GOOGLEHOTEL);
		if (!is_array($channel_info) || !count($channel_info) || empty($channel_info['params'])) {
			return false;
		}
		
		$params = json_decode($channel_info['params'], true);

		if (!$params || empty($params['hotelid'])) {
			return false;
		}

		return true;
	}

	/**
	 * Gets the name of this promotion handler
	 * 
	 * @return 	string 	the name of this promotion handler.
	 */
	public function getName()
	{
		return $this->handler_name;
	}

	/**
	 * Gets the key of this promotion handler
	 * 
	 * @return 	string 	the key of this promotion handler.
	 */
	public function getKey()
	{
		return $this->handler_key;
	}

	/**
	 * Gets the URI to the logo of this promotion handler
	 * 
	 * @return 	string 	the logo URI of this promotion handler.
	 */
	public function getLogoUri()
	{
		return $this->handler_logo;
	}

	/**
	 * This channel requires to be invoked upon updating a VBO promotion.
	 * 
	 * @return 	bool 	true if trigger is needed, or false.
	 * 
	 * @since 	1.8.4
	 */
	public function triggerUpdate()
	{
		return true;
	}

	/**
	 * This channel requires to be invoked upon deleting a VBO promotion.
	 * 
	 * @return 	bool 	true if trigger is needed, or false.
	 * 
	 * @since 	1.8.4
	 */
	public function triggerDelete()
	{
		return true;
	}

	/**
	 * Prepares the method that will save the promotion.
	 * Useful when VBO is creating a new promo also for this channel.
	 * Google Hotel also supports live updates of website promotions, in this case the
	 * "method" key will be set to "update".
	 * 
	 * @param 	array 	$params 	associative instructions for preparing the request vars.
	 * 
	 * @return 	boolean 			if false, the promotion should not be created.
	 */
	protected function prepareSavePromotion($params)
	{
		if ($params['method'] != 'new' && $params['method'] != 'update') {
			// we do not need to do anything in this case, the delete request doesn't need anything but the VBO promo id
			return true;
		}

		// params to be injected
		$inject_params = array();

		if (!empty($params['data']) && !empty($params['data']['vbo_promo_id'])) {
			// both "new" and "update" methods will set this property
			$inject_params['vbo_promo_id'] = $params['data']['vbo_promo_id'];
		} else {
			// critical: VBO must be updated and must define the newly created or updated promotion ID
			$this->setError('Vik Booking must be updated so that it defines the ID of the newly created or updated promotion on the website');
			return false;
		}

		// adjust the VBO request vars to the createPromotion structure
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$ptype = VikRequest::getInt('type', 2, 'request');
		$pdiffcost = VikRequest::getFloat('diffcost', '', 'request');
		$pidrooms = VikRequest::getVar('idrooms', array(), 'request', 'array');
		$pidprices = VikRequest::getVar('idprices', array(), 'request', 'array');
		$pwdays = VikRequest::getVar('wdays', array(), 'request', 'array');
		$pspname = VikRequest::getString('spname', '', 'request');
		$pval_pcent = VikRequest::getInt('val_pcent', 2, 'request');
		$ppromodaysadv = VikRequest::getInt('promodaysadv', 0, 'request');
		$ppromominlos = VikRequest::getInt('promominlos', 0, 'request');
		$ppromolastmind = VikRequest::getInt('promolastmind', 0, 'request');
		$ppromolastminh = VikRequest::getInt('promolastminh', 0, 'request');

		// make sure to check the value overrides
		$min_ovr_nights   = 0;
		$min_ovr_discount = 0;
		$max_ovr_nights   = 0;
		$max_ovr_discount = 0;
		if ($ptype == 2 && $pdiffcost <= 0) {
			// it's a discount promotion with an amount of 0, check value overrides
			$pnightsoverrides = VikRequest::getVar('nightsoverrides', array());
			$pvaluesoverrides = VikRequest::getVar('valuesoverrides', array());
			$pandmoreoverride = VikRequest::getVar('andmoreoverride', array());

			$list_ovr_nights  = array();
			$list_ovr_amounts = array();
			if (is_array($pnightsoverrides) && count($pnightsoverrides)) {
				foreach ($pnightsoverrides as $k => $ovr_nights) {
					if ($ovr_nights < 1 || !isset($pvaluesoverrides[$k]) || $pvaluesoverrides[$k] <= 0) {
						continue;
					}
					// push override with a discount amount greater than zero
					$list_ovr_nights[] = (int)$ovr_nights;
					$list_ovr_amounts[] = (float)$pvaluesoverrides[$k];
					// we are unable to determine a maximum stay for the promotion value overrides
				}
			}

			if (count($list_ovr_nights)) {
				$min_ovr_nights = min($list_ovr_nights);
				$min_ovr_discount = min($list_ovr_amounts);
				// the maximum discount amount will need to be ignored as we can only pass one discount amount per promotion
				$max_ovr_discount = max($list_ovr_amounts);
			}

			if ($min_ovr_nights > 0 && $min_ovr_discount > 0) {
				// we take the minimum discount value from the overrides
				$pdiffcost = $min_ovr_discount;
				// make sure to adjust the minimum stay if necessary
				$ppromominlos = $ppromominlos < $min_ovr_nights ? $min_ovr_nights : $ppromominlos;
			}
		}

		// validation and params injection
		if ($ptype != 2 || $pdiffcost <= 0) {
			// only discount promotions with values > 0
			$this->setError('Promotions for Google Hotel must apply discounts in percent or fixed values per night.');
			return false;
		}
		$inject_params['discount'] = $pdiffcost;
		// discount type 1 = fixed_amount_per_night, 2 = percent
		$inject_params['discount_type'] = $pval_pcent === 1 ? 1 : 2;
		
		// minimum stay
		if ($ppromominlos < 2) {
			// 1 means no minimum stay
			$ppromominlos = 1;
		}
		$inject_params['min_los'] = $ppromominlos;

		// stay dates
		if (empty($pfrom) || empty($pto)) {
			// dates are required
			$this->setError('Validity dates cannot be empty.');
			return false;
		}
		$tsfrom = VikBooking::getDateTimestamp($pfrom, 0, 0, 0);
		$tsto = VikBooking::getDateTimestamp($pto, 23, 59, 59);
		$inject_params['stay_from_date'] = date('Y-m-d', $tsfrom);
		$inject_params['stay_to_date'] = date('Y-m-d', $tsto);

		// rooms and rate plans are set from the VBO selection
		$inject_params['rooms'] = $pidrooms;
		$inject_params['rplans'] = $pidprices;

		// active week days (Sunday = U, Thursday = H)
		$active_wdays = array(
			'U',
			'M',
			'T',
			'W',
			'H',
			'F',
			'S',
		);
		if (count($pwdays) && strlen($pwdays[0]) && count($pwdays) < 7) {
			// some week days have been selected in VBO, unset the non-selected ones
			for ($i = 0; $i < 7 ; $i++) { 
				if (!in_array($i, $pwdays) && isset($active_wdays[$i])) {
					unset($active_wdays[$i]);
				}
			}
		}
		$inject_params['stay_wdays'] = $active_wdays;

		// guess the promotion type (basic by default)
		$promo_type = 'basic';
		if ($ppromodaysadv > 0) {
			// this will be an early bird promotion
			$promo_type = 'early_booker';
			$inject_params['book_window_min_days'] = $ppromodaysadv;
			if (empty($pspname)) {
				// we use a generic name
				$pspname = 'Early Booker Promotion';
			}
		} elseif ($ppromolastmind > 0 || $ppromolastminh > 0) {
			// this is a last minute promotion, but hours are not supported
			$ppromolastmind = $ppromolastmind < 1 ? 1 : $ppromolastmind;
			$promo_type = 'last_minute';
			$inject_params['book_window_max_days'] = $ppromolastmind;
			if (empty($pspname)) {
				// we use a generic name
				$pspname = 'Last Minute Promotion';
			}
		}
		// the type of promotion is not really needed, but we inject it for completion
		$inject_params['promo_type'] = $promo_type;

		// promotion name
		if (empty($pspname)) {
			// we use a generic name
			$pspname = 'Promotion';
		}
		$inject_params['name'] = $pspname;

		// inject the preparred params and return true
		$this->injectParams($inject_params);

		return true;
	}

	/**
	 * Creates or updates a promotion. This method can be used by VBO
	 * when creating a promotion also on this channel, or by the VCM
	 * controller dedicated to the promotions for this channel.
	 * VCM can create or update, while VBO only creates.
	 * VCM may call this method during an AJAX request. If this channel
	 * was not selected in VBO, then this method will not be called.
	 * 
	 * @param 	mixed 	$data 		array of information or null.
	 * @param 	mixed 	$method 	"new", "update" or "delete" ("edit" not supported)
	 * 
	 * @return 	boolean 			true on success, false otherwise.
	 */
	public function createPromotion($data, $method)
	{
		$proceed = $this->prepareSavePromotion(array(
			'data' 	 => $data,
			'method' => $method,
		));
		if (!$proceed) {
			// we expect some errors to be set, but the promotion cannot be created
			return false;
		}

		// configuration fields validation
		if (!function_exists('curl_init')) {
			$this->setError(VikChannelManager::getErrorFromMap('e4j.error.Curl'));
			return false;
		}
		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			$this->setError(VikChannelManager::getErrorFromMap('e4j.error.Settings'));
			return false;
		}

		// make sure some rooms for this channel have been mapped
		$mapped_rooms = [];
		$mapped_rooms_acc = [];
		$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . VikChannelManagerConfig::GOOGLEHOTEL . ";";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows()) {
			$mapped_rooms_ota = $this->dbo->loadAssocList();
			foreach ($mapped_rooms_ota as $mapped_room) {
				if (in_array($mapped_room['idroomvb'], $mapped_rooms)) {
					continue;
				}
				$prop_params = json_decode($mapped_room['prop_params'], true);
				$prop_params = !is_array($prop_params) ? [] : $prop_params;
				if (!empty($prop_params['hotelid'])) {
					$now_hid = preg_replace("/[^0-9]+/", '', $prop_params['hotelid']);
					if (!isset($mapped_rooms_acc[$now_hid])) {
						$mapped_rooms_acc[$now_hid] = [];
					}
					$mapped_rooms_acc[$now_hid][] = $mapped_room['idroomvb'];
				}
				// push room
				$mapped_rooms[] = $mapped_room['idroomvb'];
			}
		}
		if (!count($mapped_rooms)) {
			$this->setError('No rooms mapped for ' . $this->getName());
			return false;
		}

		// promotion fields
		$vbo_promo_id = VikRequest::getString('vbo_promo_id', '', 'request');
		$name = VikRequest::getString('name', '', 'request');
		$type = VikRequest::getString('promo_type', 'basic', 'request');
		$min_los = VikRequest::getInt('min_los', 0, 'request');

		// additional and optional fields
		$bookfromdate = VikRequest::getString('bookfromdate', '', 'request');
		$booktodate = VikRequest::getString('booktodate', '', 'request');
		// last minute promo only
		$last_minute_days = VikRequest::getInt('book_window_max_days', 0, 'request');
		// early booker promo only
		$early_booker_days = VikRequest::getInt('book_window_min_days', 0, 'request');
		
		// stay dates
		$stayfromdate = VikRequest::getString('stay_from_date', '', 'request');
		$staytodate = VikRequest::getString('stay_to_date', '', 'request');
		$wdays = VikRequest::getVar('stay_wdays', array(), 'request', 'array');
		$wdays = !count($wdays) || empty($wdays[0]) ? array() : $wdays;
		// rooms and rate plans
		$rooms = VikRequest::getVar('rooms', array(), 'request', 'array');
		$rplans = VikRequest::getVar('rplans', array(), 'request', 'array');
		// discount
		$discount = VikRequest::getInt('discount', 0, 'request');
		$discount_type = VikRequest::getInt('discount_type', 2, 'request');

		if (empty($vbo_promo_id) && is_array($data) && isset($data['vbo_promo_id'])) {
			// for the delete promotion event, no vars need to be prepared
			$vbo_promo_id = $data['vbo_promo_id'];
		}
		if (empty($vbo_promo_id)) {
			// VBO promotion ID is mandatory for Google Hotel
			$this->setError('Missing website promotion ID');
			return false;
		}

		if (($method == 'update' || $method == 'delete') && !$this->findPromotionID($vbo_promo_id)) {
			$this->setError("Original promotion ID {$vbo_promo_id} was not created for Google Hotel, and so updating it is impossible");
			return false;
		}

		// some rooms must be set, while it is not necessary to set some rate plans for VBO
		if (!count($rooms) || empty($rooms[0])) {
			// get all mapped VBO room IDs by default
			$rooms = array();
			foreach ($mapped_rooms as $mapped_room) {
				if (in_array($mapped_room, $rooms)) {
					continue;
				}
				$rooms[] = $mapped_room;
			}
		} else {
			// make sure all involved VBO rooms have been mapped to Google Hotel
			foreach ($rooms as $k => $rid) {
				if (!in_array($rid, $mapped_rooms)) {
					unset($rooms[$k]);
				}
			}
		}
		if (!count($rooms)) {
			$this->setError('No valid rooms found');
			return false;
		}
		$rplans = !is_array($rplans) ? array() : $rplans;

		// get the hotel inventory ID
		$ghotel_travel = new VCMGhotelTravel;
		$hinv_id = $ghotel_travel->getPropertyID();
		if (empty($hinv_id)) {
			$this->setError('No hotel inventory ID for ' . $this->getName());
			return false;
		}

		if ($method != 'delete') {
			// validation for the creation and modification of the promotions
			if (empty($discount) || $discount <= 0) {
				$this->setError('Please enter a discount amount.');
				return false;
			}
			if (empty($name)) {
				$name = 'Promotion ' . date('Ymd', strtotime($stayfromdate));
			}
			// sanitize promotion name
			$name = preg_replace("/[^a-zA-Z0-9 ]+/", '', $name);
			$name = htmlspecialchars((strlen($name) > 20 ? substr($name, 0, 20) : $name));
			if (empty($stayfromdate) || empty($staytodate)) {
				$this->setError('Dates of stay cannot be empty.');
				return false;
			}
			$from = strtotime($stayfromdate);
			$to = strtotime($staytodate);
			if ($from > $to || empty($from)) {
				$this->setError('Invalid stay dates.');
				return false;
			}
		}

		/**
		 * In order to support multiple accounts, we need to split the request
		 * per number of account depending on the rooms mapping information.
		 * 
		 * @since 	1.8.6
		 */
		$accounts_involved = [];
		if (count($mapped_rooms_acc) > 1) {
			// rooms mapped for multiple accounts, count the number of rooms involved
			foreach ($rooms as $vbo_rid) {
				foreach ($mapped_rooms_acc as $now_hid => $hotel_rooms) {
					if (in_array($vbo_rid, $hotel_rooms)) {
						// room found
						if (!isset($accounts_involved[$now_hid])) {
							$accounts_involved[$now_hid] = [];
						}
						$accounts_involved[$now_hid][] = $vbo_rid;
						// parse next vbo room
						break;
					}
				}
			}
		} else {
			// just one account
			$accounts_involved[$hinv_id] = $rooms;
		}

		if ($method == 'update' || $method == 'delete') {
			// we need to check if the previous actions were made on multiple accounts
			$prev_data = $this->findPreviousOTAData($vbo_promo_id);
			if (count($prev_data)) {
				// force the update on the previous hotel IDs
				$forced_accounts = [];
				foreach ($prev_data as $prev_promo) {
					if (empty($prev_promo['data'])) {
						continue;
					}
					$prev_promo['data'] = json_decode($prev_promo['data'], true);
					$prev_promo['data'] = !is_array($prev_promo['data']) ? [] : $prev_promo['data'];
					if (!empty($prev_promo['data']['hotelid'])) {
						// push account
						$num_hid = preg_replace("/[^0-9]+/", '', $prev_promo['data']['hotelid']);
						if (isset($accounts_involved[$num_hid])) {
							$forced_accounts[$num_hid] = $accounts_involved[$num_hid];
						} else {
							$forced_accounts[$num_hid] = $rooms;
						}
					}
				}
				if (count($forced_accounts)) {
					// replace list of accounts to parse
					$accounts_involved = $forced_accounts;
				}
			}
		}

		// loop through all involved accounts
		$successes = [];
		foreach ($accounts_involved as $use_hinv_id => $use_rooms) {
			// inject account ID in data to be stored
			$data['hotelid'] = $use_hinv_id;

			// make the request to e4jConnect to write the Promotion
			$write_promo_node = '';
				
			// required filter by hotel ID
			$filters = ['hotelid="' . $use_hinv_id . '"'];
			// other mandatory filters
			$filters[] = 'action="' . $method . '"';
			$filters[] = 'promoid="' . $vbo_promo_id . '"';

			if ($method != 'delete') {
				// promotion attributes
				$promo_attr = [];
				$promo_attr[] = 'id="' . $vbo_promo_id . '"';
				$promo_attr[] = 'name="' . $name . '"';
				$promo_attr[] = 'type="' . $discount_type . '"';
				if (!empty($min_los) && $min_los > 0) {
					$promo_attr[] = 'min_stay_through="' . $min_los . '"';
				}

				// conditional nodes
				$condit_nodes = array();
				if ($type == 'last_minute' || $last_minute_days > 0) {
					array_push($condit_nodes, '<last_minute unit="day" value="' . $last_minute_days . '" />');
				} elseif ($type == 'early_booker' || $early_booker_days > 0) {
					array_push($condit_nodes, '<early_booker value="' . $early_booker_days . '" />');
				}

				// stay dates can be a single node, or it contains children nodes for active_weekdays and excluded_dates
				$stay_dates = '<stay_date start="' . $stayfromdate . '" end="' . $staytodate . '"';
				if (count($wdays)) {
					// children nodes
					$stay_dates .= '>'."\n";
					if (count($wdays)) {
						$stay_dates .= '<active_weekdays>'."\n";
						foreach ($wdays as $wday) {
							$stay_dates .= '<active_weekday>' . $wday . '</active_weekday>'."\n";
						}
						$stay_dates .= '</active_weekdays>'."\n";
					}
					// close node
					$stay_dates .= '</stay_date>';
				} else {
					// single node
					$stay_dates .= ' />';
				}

				// rooms and rate plans
				$rooms_rates = '<rooms>'."\n";
				foreach ($use_rooms as $r) {
					$rooms_rates .= '<room id="' . $r . '"/>'."\n";
				}
				$rooms_rates .= '</rooms>'."\n";
				$rooms_rates .= '<parent_rates>'."\n";
				foreach ($rplans as $r) {
					$rooms_rates .= '<parent_rate id="' . $r . '"/>'."\n";
				}
				$rooms_rates .= '</parent_rates>';

				// build write promotion node (only if we are creating or updating a promotion)
				$write_promo_node = "\t" . '<WritePromotion ' . implode(' ', $promo_attr) . '>
					'.implode("\n", $condit_nodes).'
					'.$stay_dates.'
					'.$rooms_rates.'
					<discount value="' . $discount . '" />
				</WritePromotion>';
			}

			// endpoint URL for the request
			$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=wprom&c=googlehotel";
			
			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager WPROM Request e4jConnect.com - Google Hotel -->
<WritePromotionRQ xmlns="http://www.e4jconnect.com/channels/wpromrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $apikey . '"/>
	<Fetch ' . implode(' ', $filters) . '/>
	' . $write_promo_node . '
</WritePromotionRQ>';

			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$e4jC->slaveEnabled = true;
			$rs = $e4jC->exec();

			if ($e4jC->getErrorNo()) {
				$this->setError(@curl_error($e4jC->getCurlHeader()));
				return false;
			}
			if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				$this->setError(VikChannelManager::getErrorFromMap($rs));
				return false;
			}
			if (strpos($rs, 'e4j.ok') === false) {
				$this->setError('Invalid response received. ' . $rs);
				if (!count($successes)) {
					return false;
				} else {
					continue;
				}
			}

			// store/update the record on the db for this promotion
			$this->channelPromotionCompleted($vbo_promo_id, $method, $data);

			// set the response from e4jConnect
			$this->setResponse($rs);

			// push success
			$successes[] = $rs;
		}

		return (bool)count($successes);
	}
}
