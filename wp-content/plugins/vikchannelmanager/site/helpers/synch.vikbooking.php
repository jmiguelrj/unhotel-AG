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
 * This Class is used by VikChannelManager to send A Requests (A_RQ) to the E4jConnect
 * central servers to sync the availability of VikBooking with the various OTAs.
 */
class SynchVikBooking
{
	/**
	 * @var  int
	 */
	private $order_id = 0;

	/**
	 * @var  array
	 */
	private $exclude_ids = [];

	/**
	 * @var  int
	 */
	private $modified_order = [];

	/**
	 * @var  int
	 */
	private $cancelled_order = [];

	/**
	 * @var  bool 	true if the AV_RQ is for a pending reservation.
	 */
	private $is_pending_lock = false;

	/**
	 * @var  bool
	 */
	private $skip_check_auto_sync = false;

	/**
	 * @var  string
	 */
	private $push_type = '';

	/**
	 * @var  VCMConfigRegistry
	 */
	private $config;

	/**
	 * @var  JDatabase
	 */
	private $dbo;

	/**
	 * @var 	string 	the booking original status
	 * 
	 * @since 	1.8.0
	 */
	private $prev_status = null;

	/**
	 * Class constructor defines required properties and loads dependencies.
	 * 
	 * @param 	int 	$orderid 			the involved booking ID.
	 * @param 	array 	$exclude_channels 	list of channel identifiers to exclude.
	 */
	public function __construct($orderid, array $exclude_channels = [])
	{
		if (!class_exists('VikChannelManager')) {
			require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib.vikchannelmanager.php');
		}
		if (!class_exists('VikChannelManagerConfig')) {
			require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vcm_config.php');
		}

		$this->order_id    = (int)$orderid;
		$this->exclude_ids = $exclude_channels;
		$this->config 	   = VCMFactory::getConfig();
		$this->dbo 		   = JFactory::getDbo();
	}
	
	/**
	 * The visibility of this method must be public as it may be useful
	 * for who invokes the class to know if there is at least one API channel.
	 * Useful for the App to know if an error occurred only because there are
	 * no active API channels that require an update of the availability.
	 * 
	 * @since 	1.6.13
	 */
	public function isAvailabilityRequest()
	{
		$q = $this->dbo->getQuery(true)
			->select('COUNT(1)')
			->from($this->dbo->qn('#__vikchannelmanager_channel'))
			->where($this->dbo->qn('av_enabled') . ' = 1');

		if ($this->exclude_ids) {
			$q->where($this->dbo->qn('uniquekey') . ' NOT IN (' . implode(',', array_map('intval', $this->exclude_ids)) . ')');
		}

		$this->dbo->setQuery($q);

		return (bool)$this->dbo->loadResult();
	}
	
	/**
	 * Returns a list of channels available supporting availability updates.
	 * 
	 * @return 	array
	 */
	private function getAvChannelIds()
	{
		$ch_ids = [];

		$q = $this->dbo->getQuery(true)
			->select($this->dbo->qn(['id', 'name', 'uniquekey']))
			->from($this->dbo->qn('#__vikchannelmanager_channel'))
			->where($this->dbo->qn('av_enabled') . ' = 1');

		if ($this->exclude_ids) {
			$q->where($this->dbo->qn('uniquekey') . ' NOT IN (' . implode(',', array_map('intval', $this->exclude_ids)) . ')');
		}

		$this->dbo->setQuery($q);

		foreach ($this->dbo->loadAssocList() as $cha) {
			$ch_ids[] = $cha['uniquekey'];
		}

		return $ch_ids;
	}

	/**
	 * This method sets the original booking array of VBO before it was Updated.
	 * If called, the system will merge dates and room types of the original booking
	 * with the dates and room types of the new and updated order.
	 *
	 * @param 	array 	$m_order 	the modified (before modification) booking array
	 *
	 * @return 	self
	 */
	public function setFromModification($m_order)
	{
		if (is_array($m_order)) {
			$this->modified_order = $m_order;
		}

		return $this;
	}

	/**
	 * Sets the original booking array of VBO before it was Cancelled, or just the ID.
	 * If called, the system will fetch the dates and room types from the original booking
	 * and will notify the new availability to all channels.
	 *
	 * @param 	array 	$c_order 	the cancelled booking array (may contain just the ID).
	 *
	 * @return 	self
	 */
	public function setFromCancellation($c_order)
	{
		if (is_array($c_order)) {
			$this->cancelled_order = $c_order;
		}

		return $this;
	}

	/**
	 * If called before the main method, the system will execute the
	 * A_RQ to e4jConnect even if the Configuration setting Auto-Sync of VCM
	 * is disabled. This is useful in the back-end of VBO for Modifications and
	 * Cancellations to be executed no matter if the sync is disabled.
	 *
	 * @return 	self
	 */
	public function setSkipCheckAutoSync()
	{
		$this->skip_check_auto_sync = $this->skip_check_auto_sync === false ? true : false;

		return $this;
	}

	/**
	 * Registers what was the previous status of the booking. Maybe a booking that was
	 * set to confirmed from a pending state, can inject "standby" as previous status
	 * so that VCM will know that an additional action may be necessary for some channels.
	 * In fact, "Request to Book" reservations may need to accept or deny the request.
	 * 
	 * @param 	string 	$prev_status 	the booking original status before any updates.
	 * 
	 * @return 	self
	 */
	public function setBookingPreviousStatus($prev_status)
	{
		$this->prev_status = $prev_status;

		return $this;
	}

	/**
	 * This method sets the push-type of the A Request.
	 * New bookings generated via VBO front-end will set the
	 * type to 'new' to send e4jConnect additional information.
	 * When $type = 'new', e4jConnect may send push notifications
	 * to the enabled mobile devices.
	 *
	 * @param 	mixed  		$type
	 *
	 * @return 	self
	 */
	public function setPushType($type)
	{
		$set_type = '';

		if ($type == 'new') {
			$q = $this->dbo->getQuery(true)
				->select('COUNT(1)')
				->from($this->dbo->qn('#__vikchannelmanager_channel'))
				->where($this->dbo->qn('uniquekey') . ' = ' . (int)VikChannelManagerConfig::MOBILEAPP);

			$this->dbo->setQuery($q, 0, 1);

			if ($this->dbo->loadResult()) {
				$app_settings_raw = $this->config->get('app_settings');
				if ($app_settings_raw) {
					$app_settings = json_decode($app_settings_raw, true);
					if (is_array($app_settings) && isset($app_settings['vbBookings']['on']) && intval($app_settings['vbBookings']['on']) > 0) {
						$set_type = $type;
					}
				}
			}
		}

		$this->push_type = $set_type;

		return $this;
	}
	
	/**
	 * Sends A(U)_RQ to E4jConnect.com.
	 * Called by VBO every time the availability is modified for certain rooms.
	 * The same method can also be called by VCM newbookings.vikbooking.php for a
	 * Cancellation of a booking or a Modification or for other channels like TripConnect.
	 * Calls the ReservationsLogger Class to log the updates of any date/room combination.
	 *
	 * @return 	boolean
	 */
	public function sendRequest()
	{
		$result = false;

		if (($this->config->get('vikbookingsynch') || $this->skip_check_auto_sync === true) && $this->isAvailabilityRequest()) {
			// gather the booking details
			$arr_order = $this->getOrderDetails();

			// extract information
			$order = array_key_exists('vikbooking_order', $arr_order) ? $arr_order['vikbooking_order'] : [];

			// unset no needed property
			unset($arr_order['vikbooking_order']);

			if ($order && $arr_order) {
				// VCM 1.6.8 - ReservationsLogger
				$res = VikChannelManager::getResLoggerInstance()
					->typeModification(($this->modified_order ? true : false))
					->typeCancellation(($this->cancelled_order ? true : false))
					/**
					 * if sendRequest() is called from newbookings.vikbooking.php
					 * the channel uniquekey is passed to the constructor, so we
					 * know the update command was started from an OTA reservation.
					 */
					->typeFromChannels($this->exclude_ids)
					->trackLog($order, $arr_order);

				// build the request
				$xml = $this->composeXmlARequest($order, $arr_order);

				// return the result of the request execution or false if some errors occurred with the XML building
				$result = $xml ? $this->executeARequest($xml) : false;
			} else {
				// no channels probably need to be updated or the booking was not found. Return true anyway.
				$result = true;
			}
		}

		/**
		 * Trigger the execution of the method responsible for the booking conversion tracking
		 * for some meta-search channels that may require this additional request to update values.
		 * 
		 * @since 	1.8.3
		 */
		$order = isset($order) ? $order : null;
		$this->dispatchBookingConversion($order);

		// return false if the request could not be executed
		return $result;
	}

	/**
	 * This method checks if booking conversion requests should be performed.
	 * Originally introduced to perform booking conversion requests for trivago
	 * in case of booking cancellations.
	 * 
	 * @param 	mixed 	$order 	null or array record of current reservation.
	 * 
	 * @return 	bool 			true if booking conversion performed or false.
	 * 
	 * @since 	1.8.3
	 */
	protected function dispatchBookingConversion($order)
	{
		if (!$this->cancelled_order) {
			// an update request due to a booking cancellation is necessary
			return false;
		}

		if (!$order || !is_array($order)) {
			$order = $this->getOrderDetails();
			if (isset($order['vikbooking_order'])) {
				$order = $order['vikbooking_order'];
			}
		}

		if (!$order) {
			return false;
		}

		// check if conversion is necessary
		if (empty($order['channel']) || stripos($order['channel'], 'trivago') === false || empty($order['idorderota'])) {
			// no conversion needed
			return false;
		}

		// get the tracking reference object from history
		$trk_reference = null;
		try {
			if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
				$history_obj = VikBooking::getBookingHistoryInstance();
				$history_obj->setBid($order['id']);
				// history data validation callback
				$data_callback = function($data) {
					if (!is_object($data) || !isset($data->channel) || !isset($data->bconv_type)) {
						return false;
					}
					return (stripos($data->channel, 'trivago') !== false && stripos($data->bconv_type, 'Confirmation') !== false);
				};
				$prev_data = $history_obj->getEventsWithData('CM', $data_callback);
				if (is_array($prev_data) && $prev_data) {
					// grab the last event data (object)
					$trk_reference = $prev_data[0];
				}
			}
		} catch (Throwable $t) {
			// do nothing
		}

		if (!is_object($trk_reference)) {
			// if nothing was saved, then tracking would be useless
			return false;
		}
		if (!isset($trk_reference->bconv_data)) {
			// this should never happen, but we cannot stop the conversion tracking request at this point
			$trk_reference->bconv_data = new stdClass;
		}

		// prepare the request for e4jConnect
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=bconv&c=trivago";
		$api_key  = VikChannelManager::getApiKey(true);
		if (empty($api_key)) {
			return false;
		}

		// trivago settings
		$account_id = VikChannelManager::getTrivagoAccountID();
		$partner_id = VikChannelManager::getTrivagoPartnerID();
		$curr_name  = VikChannelManager::getCurrencyName();

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager BCONV Request e4jConnect.com - VikBooking -->
<BookingConversionRQ xmlns="http://www.e4jconnect.com/schemas/bconvrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Information>' . "\n";

		// include private information node
		$xml .= "\t\t" . '<PrivateDetails>
		<PrivateDetail type="_trackmethod" value="Cancellation" />
		<PrivateDetail type="ref" value="' . $account_id . '" />
		<PrivateDetail type="hotel" value="' . $partner_id . '" />
		<PrivateDetail type="trv_reference" value="' . (isset($trk_reference->bconv_data->trv_reference) ? $trk_reference->bconv_data->trv_reference : '') . '" />
		<PrivateDetail type="tebadv" value="' . (isset($trk_reference->bconv_data->tebadv) && (int)$trk_reference->bconv_data->tebadv > 0 ? '1' : '0') . '" />
	</PrivateDetails>' . "\n";

		// calculate the locale
		$locale = strtoupper(substr(JFactory::getLanguage()->getTag(), -2));
		if (!empty($trk_reference->bconv_data->trv_locale)) {
			$locale = $trk_reference->bconv_data->trv_locale;
		} elseif (!empty($trk_reference->bconv_data->locale)) {
			$locale = $trk_reference->bconv_data->locale;
		}

		// build public booking information
		$pubinfo = [
			'arrival' 		  => date('Y-m-d', $order['checkin']),
			'departure' 	  => date('Y-m-d', $order['checkout']),
			'created_on' 	  => date('Y-m-d H:i:s', $order['ts']),
			'currency' 		  => $curr_name,
			'volume' 		  => number_format($order['total'], 2, '.', ''),
			'booking_id' 	  => $order['id'],
			'locale' 		  => $locale,
			'number_of_rooms' => (isset($order['roomsnum']) ? (int)$order['roomsnum'] : 1),
			'cancelled_on' 	  => date('Y-m-d H:i:s P'),
			'refund_amount'   => (isset($order['refund']) ? (float)$order['refund'] : ''),
		];

		$xml .= "\t\t" . '<PublicDetails>' . "\n";
		foreach ($pubinfo as $k => $v) {
			$xml .= "\t\t\t" . '<PublicDetail type="' . htmlentities($k) . '" value="' . htmlentities($v) . '" />' . "\n";
		}
		$xml .= "\t\t" . '</PublicDetails>' . "\n";
		//
		$xml .= "\t" . '</Information>
</BookingConversionRQ>';

		// prepare the event data object
		$ev_data = new stdClass;
		$ev_data->channel 	 = 'trivago';
		$ev_data->bconv_type = 'Cancellation';
		$ev_data->bconv_data = $trk_reference->bconv_data;
		// set event description
		$ev_descr = $ev_data->channel . ' - Booking Conversion Tracking (' . $ev_data->bconv_type . ')';

		/**
		 * Try to instantiate the history object from VBO.
		 * Logs and event data may need to be stored.
		 */
		$history_obj = null;
		try {
			if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
				$history_obj = VikBooking::getBookingHistoryInstance();
				$history_obj->setBid($order['id']);
			}
		} catch (Throwable $t) {
			// do nothing
		}
		
		// start the request
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->slaveEnabled = true;
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();

		// check any possible communication error
		if ($e4jC->getErrorNo()) {
			$error = VikChannelManager::getErrorFromMap($e4jC->getErrorMsg());
			if ($history_obj) {
				// log the error
				$ev_data->bconv_type = 'Error';
				$history_obj->setExtraData($ev_data)->store('CM', $ev_descr . "\n" . $error);
			}
			return false;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			$error = VikChannelManager::getErrorFromMap($rs);
			if ($history_obj) {
				// log the error
				$ev_data->bconv_type = 'Error';
				$history_obj->setExtraData($ev_data)->store('CM', $ev_descr . "\n" . $error);
			}
			return false;
		}

		if ($history_obj) {
			// log the successful operation
			$history_obj->setExtraData($ev_data)->store('CM', $ev_descr);
		}
		
		return true;
	}
	
	/**
	 * Executes the A(U)_RQ sending the XML to e4jConnect
	 *
	 * @param 	string 		$xml 	the composed XML request string.
	 *
	 * @return 	boolean
	 */
	private function executeARequest($xml)
	{
		if (!function_exists('curl_init')) {
			$this->saveNotify('0', 'VCM', 'e4j.error.Curl', '');
			return false;
		}

		$e4jC = new E4jConnectRequest("https://e4jconnect.com/channelmanager/?r=a&c=channels");
		$e4jC->setPostFields($xml)
			->setConnectTimeout(20)
			->setTimeout(40);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			// store erroneous notification with data transmission error message
			$this->saveNotify('0', 'VCM', $e4jC->getErrorMsg(), $this->order_id);

			/**
			 * Data transmission errors are usually caused by "Operation timed out" or
			 * "Resolving timed out after 10000 milliseconds" CURL errors of number 28.
			 * Such errors can be caused by a temporary outage of service or by temporary
			 * DNS resolving issues, hence connectivity issues on this website/server.
			 * In this case we need to internally re-schedule the execution of this request
			 * for later so that we won't risk to leave an availability update request undone.
			 * 
			 * @since 	1.8.20
			 */
			VCMRequestScheduler::getInstance([
				'order_id' => $this->order_id,
				'payload'  => $xml,
				'request'  => 'a',
				'channels' => 'channels',
				'errno'    => $e4jC->getErrorNo(),
				'errmsg'   => $e4jC->getErrorMsg(),
			])->store();

			return false;
		}

		if (substr($rs, 0, 4) == 'e4j.') {
			// response for a single channel request
			if (substr($rs, 0, 9) == 'e4j.error') {
				if ($rs != 'e4j.error.Skip') {
					$this->saveNotify('0', 'VCM', $rs, $this->order_id);
				}
				return false;
			}
			$this->saveNotify('1', 'VCM', 'e4j.OK.Channels.AR_RQ', $this->order_id);
		} else {
			// JSON Response for multiple channels request
			$arr_rs = json_decode($rs, true);
			if (is_array($arr_rs) && $arr_rs) {
				$this->saveMultipleNotifications($arr_rs);
			}
		}

		return true;
	}
	
	/**
	 * Generates the XML string for the A(U)_RQ.
	 * If a specific "order" (push) type is available,
	 * this is passed to e4jConnect for the PND.
	 *
	 * @param 	array 	$order
	 * @param 	array 	$rooms
	 *
	 * @return  string 	the XML message for the request. Empty string in case of errors.
	 */
	private function composeXmlARequest($order, $rooms)
	{
		$build = [];
		$vbrooms_parsed = [];

		foreach ($rooms as $k => $room) {
			// start room container
			$build[$k] = $room;

			/**
			 * We need to optimize the size of the "A" XML request by grouping
			 * consecutive date intervals with an equal remaining availability.
			 * 
			 * @since 	1.8.25
			 */
			$build[$k]['av_intervals'] = [];
			$compact_av_signature      = [];

			// obtain the last inventory day
			end($room['adates']);
			$last_av_day = key($room['adates']);
			reset($room['adates']);

			// loop through the inventory details for this room
			foreach ($room['adates'] as $day => $daydet) {
				/**
				 * Always store the availability for this day ("un-compact" way)
				 * as an associative and linear array built as $day => $availability.
				 * 
				 * @deprecated 	1.8.25
				 */
				$build[$k]['newavail'][$day] = $daydet['newavail'];

				if (!$compact_av_signature) {
					// start interval container
					$compact_av_signature = [
						'fday' => $day,
						'tday' => $day,
						'av'   => $daydet['newavail'],
					];

					// parse the next day to check for differences
					continue;
				}

				// check if data should be stored in the "compact" way
				if ($compact_av_signature['av'] != $daydet['newavail']) {
					// push previous interval
					$build[$k]['av_intervals'][] = $compact_av_signature;

					if ($last_av_day === $day) {
						// push last interval
						$build[$k]['av_intervals'][] = [
							'fday' => $day,
							'tday' => $day,
							'av'   => $daydet['newavail'],
						];

						// empty interval container before the foreach will auto-break
						$compact_av_signature = [];
					} else {
						// prepare the new interval container
						$compact_av_signature = [
							'fday' => $day,
							'tday' => $day,
							'av'   => $daydet['newavail'],
						];
					}
				} else {
					// update to-date on current interval container
					$compact_av_signature['tday'] = $day;
				}
			}

			// check for last node in the "compact" way
			if ($compact_av_signature) {
				// push last interval
				$build[$k]['av_intervals'][] = [
					'fday' => $compact_av_signature['fday'],
					'tday' => $compact_av_signature['tday'],
					'av'   => $compact_av_signature['av'],
				];
			}
		}

		// proceed with building the request
		if ($build) {
			$nkey = $this->generateNKey($order['id']);

			if ($order['status'] == 'standby') {
				$order['confirmnumber'] = $order['id'] . 'PENDING';
			}

			$order_node_attr = [
				'id="' . $order['id'] . '"',
				'confirmnumb="' . $order['confirmnumber'] . '"',
			];

			if ($order['status'] != 'standby' && !empty($this->push_type)) {
				array_push($order_node_attr, 'type="' . $this->push_type . '"');
			}

			/**
			 * We pass along some extra information because some channels may need
			 * to support Request to Book reservations that require additional updates.
			 * 
			 * @since 	1.8.0
			 */
			if (!empty($order['idorderota']) && !empty($order['channel'])) {
				if (defined('ENT_XML1')) {
					// only available from PHP 5.4 and on
					$order['idorderota'] = htmlspecialchars($order['idorderota'], ENT_XML1 | ENT_COMPAT, 'UTF-8');
					$order['channel'] = htmlspecialchars($order['channel'], ENT_XML1 | ENT_COMPAT, 'UTF-8');
				} else {
					// fallback to plain all html entities
					$order['idorderota'] = htmlentities($order['idorderota']);
					$order['channel'] = htmlentities($order['channel']);
				}
				array_push($order_node_attr, 'otaid="' . $order['idorderota'] . '"');
				array_push($order_node_attr, 'otaname="' . $order['channel'] . '"');
			}
			if (!empty($this->prev_status)) {
				array_push($order_node_attr, 'prevstatus="' . $this->prev_status . ':' . $order['status'] . '"');
			}

			$xmlstr = '<?xml version="1.0" encoding="UTF-8"?>
<!-- A Request e4jConnect.com - VikChannelManager - VikBooking -->
<AvailUpdateRQ xmlns="http://www.e4jconnect.com/avail/arq">
	<Notify client="' . JUri::root() . '" nkey="' . $nkey . '"/>
	<Api key="' . $this->config->get('apikey', '') . '"/>
	<AvailUpdate>
		<Order ' . implode(' ', $order_node_attr) . '>
			<DateRange from="' . date('Y-m-d', $order['checkin']) . '" to="' . date('Y-m-d', $order['checkout']) . '"/>
		</Order>'."\n";

			// scan all rooms involved in the update request
			foreach ($build as $k => $data) {
				if (in_array($data['idroom'], $vbrooms_parsed)) {
					// duplicate room ID
					continue;
				}

				// turn flag on for this room
				$vbrooms_parsed[] = $data['idroom'];

				/**
				 * Scan all the availability inventory intervals in the "compact" way. This adds the
				 * support for the "until" attribute in <Day @date @until /> node to allow a range.
				 * We no longer scan `$data['newavail'] as $day => $avail` that only supported "@date".
				 * 
				 * @since 	1.8.25
				 */
				foreach ($data['av_intervals'] as $av_interval) {
					$xmlstr .= "\t\t" . '<RoomType newavail="' . $av_interval['av'] . '">
			<Channels>' . "\n";
					foreach ($data['channels'] as $channel) {
						// attempt to seek for the rate plan ID, if needed
						$rateplanid = '0';
						if (((int)$channel['idchannel'] == (int)VikChannelManagerConfig::AGODA || (int)$channel['idchannel'] == (int)VikChannelManagerConfig::YCS50) && !empty($channel['otapricing'])) {
							$ota_pricing = json_decode($channel['otapricing'], true);
							if ($ota_pricing && array_key_exists('RatePlan', $ota_pricing)) {
								foreach ($ota_pricing['RatePlan'] as $rp_id => $rp_val) {
									$rateplanid = $rp_id;
									break;
								}
							}
						}

						/**
						 * We also support 'property_id' or 'user_id' for channels like
						 * Hostelworld or Airbnb API beside the classic parameter 'hotelid'.
						 * 
						 * @see 	getOrderDetails()
						 * 
						 * @since 	1.6.22 (Hostelworld) - 1.8.0 (Airbnb API)
						 */
						$xmlstr .= "\t\t\t\t" . '<Channel id="' . $channel['idchannel'] . '" roomid="' . $channel['idroomota'] . '" rateplanid="' . $rateplanid . '"' . (array_key_exists('hotelid', $channel) ? ' hotelid="' . $channel['hotelid'] . '"' : '') . '/>' . "\n";
					}

					// finalize channels and inventory for this range of date
					$xmlstr .= "\t\t\t" . '</Channels>
			<Adults num="' . $data['adults'] . '"/>
			<Children num="' . $data['children'] . '"/>
			<Day date="' . $av_interval['fday'] . '" until="' . $av_interval['tday'] . '" />
		</RoomType>' . "\n";
				}
			}

			// close the main nodes
			$xmlstr .= "\t" . '</AvailUpdate>
</AvailUpdateRQ>';

			// return the XML string for the request
			return $xmlstr;
		}

		// default to an empty string
		return '';
	}
	
	/**
	 * Get one availability number for the room of the OTA
	 * In case one room of the OTA is linked to more than one room
	 * of VikBooking, this method returns the highest value for the
	 * availability of the rooms in VikBooking for these dates.
	 * It also returns the number of Children and Adults in the first room assigned.
	 *
	 * @param 	array 	$room
	 *
	 * @return 	array
	 */
	private function getUniqueRoomAvailabilityAndPeople($room)
	{
		$values = [];
		$ret = [];
		foreach ($room as $k => $r) {
			foreach ($r['adates'] as $day => $daydet) {
				$values[$day][] = $daydet['newavail'];
			}
		}
		foreach ($values as $k => $v) {
			$values[$k] = max($v);
		}
		$ret['newavail'] = $values;
		$ret['adults'] = $room[key($room)]['adults'];
		$ret['children'] = $room[key($room)]['children'];

		return $ret;
	}
	
	/**
	 * Returns an associative list of room records with the remaining unit details.
	 * 
	 * @return 	array 	empty array in case of errors, associative list otherwise.
	 */
	private function getOrderDetails()
	{
		$rooms = [];

		$q = $this->dbo->getQuery(true)
			->select('*')
			->from($this->dbo->qn('#__vikbooking_orders'))
			->where($this->dbo->qn('id') . ' = ' . (int)$this->order_id);

		$this->dbo->setQuery($q);
		$reservation = $this->dbo->loadAssoc();

		if (!$reservation) {
			// booking not found
			return [];
		}

		if (($reservation['status'] == 'cancelled' && !$this->cancelled_order) || ($reservation['status'] == 'standby' && !VCMRequestAvailability::syncWhenPending())) {
			// do not run if booking cancelled and no cancellation data, or if pending and sync-pending is disabled
			return [];
		}

		if ($reservation['status'] == 'standby' && !$this->cancelled_order) {
			// turn flag on
			$this->is_pending_lock = true;
		}

		// set reserved key for the reservation record
		$rooms['vikbooking_order'] = $reservation;

		// fetch room details
		$q = $this->dbo->getQuery(true)
			->select($this->dbo->qn([
				'or.idroom',
				'or.adults',
				'or.children',
				'or.idtar',
				'or.optionals',
				'r.name',
				'r.units',
				'r.fromadult',
				'r.toadult',
			]))
			->from($this->dbo->qn('#__vikbooking_ordersrooms', 'or'))
			->leftJoin($this->dbo->qn('#__vikbooking_rooms', 'r') . ' ON ' . $this->dbo->qn('or.idroom') . ' = ' . $this->dbo->qn('r.id'))
			->where($this->dbo->qn('or.idorder') . ' = ' . (int)$reservation['id'])
			->order($this->dbo->qn('or.id') . ' ASC');

		$this->dbo->setQuery($q);
		$orderrooms = $this->dbo->loadAssocList();

		if (!$orderrooms) {
			return [];
		}

		// in case of modification, if the rooms were different in $this->modified_order['rooms_info'], the new availability should be taken also for the previous rooms
		if ($this->modified_order) {
			$new_room_ids = [];
			foreach ($orderrooms as $orderroom) {
				$new_room_ids[] = $orderroom['idroom'];
			}
			if ($this->modified_order['rooms_info']) {
				$or_next_index = count($orderrooms);
				foreach ($this->modified_order['rooms_info'] as $mod_orderroom) {
					if (!in_array($mod_orderroom['idroom'], $new_room_ids)) {
						$mod_orderroom['modification'] = 1;
						$orderrooms[$or_next_index] = $mod_orderroom;
						$or_next_index++;
					}
				}
			}
		}

		/**
		 * Merge the current rooms with all the ones affected by a shared calendar.
		 * 
		 * @since 	1.7.1
		 */
		$rooms_shared_cals = $this->getRoomsSharedCalsInvolved($orderrooms);
		$orderrooms = array_merge($orderrooms, $rooms_shared_cals);

		// build channels relations with VB Rooms
		$av_ch_ids = $this->getAvChannelIds();
		foreach ($orderrooms as $kor => $or) {
			// start container
			$orderrooms[$kor]['channels'] = [];

			$q = $this->dbo->getQuery(true)
				->select('*')
				->from($this->dbo->qn('#__vikchannelmanager_roomsxref'))
				->where($this->dbo->qn('idroomvb') . ' = ' . (int)$or['idroom']);

			$this->dbo->setQuery($q);
			$ch_rooms = $this->dbo->loadAssocList();

			if (!$ch_rooms) {
				// room is not on any channel
				unset($orderrooms[$kor]);
				continue;
			}

			foreach ($ch_rooms as $ch_room) {
				/**
				 * Before pushing a channel to be updated, ensure this was not a request-to-book reservation
				 * coming from the same channel, as locking the availability would harm the confirmation.
				 * 
				 * @since 	1.8.22
				 */
				if ($this->is_pending_lock === true && !empty($reservation['type']) && !strcasecmp($reservation['type'], 'request') && count($orderrooms) === 1) {
					if (!empty($reservation['channel']) && !empty($ch_room['channel']) && stripos($reservation['channel'], $ch_room['channel']) !== false) {
						// do not include the channel where the pending RTB reservation came from
						continue;
					}
				}

				if (strlen((string)$ch_room['idroomota']) && strlen((string)$ch_room['idchannel']) && in_array($ch_room['idchannel'], $av_ch_ids)) {
					$ch_r_info = [
						'idroomota'  => $ch_room['idroomota'],
						'idchannel'  => $ch_room['idchannel'],
						'otapricing' => $ch_room['otapricing'],
					];

					if (!empty($ch_room['prop_params'])) {
						$prop_params_info = json_decode($ch_room['prop_params'], true);
						$prop_params_info = is_array($prop_params_info) ? $prop_params_info : [];
						if (!empty($prop_params_info['hotelid'])) {
							$ch_r_info['hotelid'] = $prop_params_info['hotelid'];
						} elseif (!empty($prop_params_info['property_id'])) {
							$ch_r_info['hotelid'] = $prop_params_info['property_id'];
						} elseif (!empty($prop_params_info['id'])) {
							$ch_r_info['hotelid'] = $prop_params_info['id'];
						} elseif (!empty($prop_params_info['user_id'])) {
							$ch_r_info['hotelid'] = $prop_params_info['user_id'];
						}
					}

					// set channel details to be updated
					$orderrooms[$kor]['channels'][] = $ch_r_info;
				} elseif (strlen((string)$ch_room['idroomota']) && strlen((string)$ch_room['idchannel']) && in_array($ch_room['idchannel'], $this->exclude_ids)) {
					/**
					 * Smart Balancer - booking coming from a channel may need to update the availability also for this OTA without excluding it, if a rule is in place.
					 * The Smart Balancer should then consider the relations with 'excluded' => 1 if no rules must apply for these dates and unset them.
					 * 
					 * Updated @since 1.7.2
					 * The Shared Calendars feature was in conflict with the property 'excluded' => 1, because the SmartBalancer method cleanAvailabilityExcludedRooms()
					 * was excluding the rooms we added through the merge with $this->getRoomsSharedCalsInvolved(). Therefore, the 'excluded' property should not always be 1.
					 */
					$smartbal_excluded = 1;
					foreach ($rooms_shared_cals as $shared_room_cal) {
						if (isset($shared_room_cal['idroom']) && $shared_room_cal['idroom'] == $or['idroom']) {
							// never exclude this room ID through the SmartBalancer
							$smartbal_excluded = 0;
							break;
						}
					}

					$ch_r_info = [
						'excluded'   => $smartbal_excluded, 
						'idroomota'  => $ch_room['idroomota'], 
						'idchannel'  => $ch_room['idchannel'], 
						'otapricing' => $ch_room['otapricing'],
					];

					if (!empty($ch_room['prop_params'])) {
						$prop_params_info = json_decode($ch_room['prop_params'], true);
						if (!empty($prop_params_info['hotelid'])) {
							$ch_r_info['hotelid'] = $prop_params_info['hotelid'];
						} elseif (!empty($prop_params_info['property_id'])) {
							$ch_r_info['hotelid'] = $prop_params_info['property_id'];
						} elseif (!empty($prop_params_info['id'])) {
							$ch_r_info['hotelid'] = $prop_params_info['id'];
						} elseif (!empty($prop_params_info['user_id'])) {
							$ch_r_info['hotelid'] = $prop_params_info['user_id'];
						}
					}

					// set channel details to be updated
					$orderrooms[$kor]['channels'][] = $ch_r_info;
				}
			}

			if (!$orderrooms[$kor]['channels']) {
				// room is not on any channel
				unset($orderrooms[$kor]);
			}
		}

		if (!$orderrooms) {
			return [];
		}

		$earliest_checkin = $reservation['checkin'];
		$prev_groupdays = [];
		// in case of modification, if the check-in/out dates were different in $this->modified_order, the new availability should be calculated also for the previous dates
		if ($this->modified_order) {
			if ($this->modified_order['checkin'] != $reservation['checkin'] || $this->modified_order['checkout'] != $reservation['checkout']) {
				$prev_groupdays = $this->getGroupDays($this->modified_order['checkin'], $this->modified_order['checkout'], $this->modified_order['days']);
				if ($this->modified_order['checkin'] < $earliest_checkin) {
					$earliest_checkin = $this->modified_order['checkin'];
				}
			}
		}
		$groupdays = $this->getGroupDays($reservation['checkin'], $reservation['checkout'], $reservation['days']);
		if ($prev_groupdays) {
			$groupdays = array_merge($groupdays, $prev_groupdays);
			$groupdays = array_unique($groupdays);
		}
		$morehst = $this->getHoursRoomAvail() * 3600;
		foreach ($orderrooms as $kor => $or) {
			if (!$or['channels']) {
				continue;
			}

			// set room booked record
			$rooms[$kor] = $or;

			// fetch busy records
			$q = $this->dbo->getQuery(true)
				->select($this->dbo->qn(['id', 'checkin', 'checkout']))
				->from($this->dbo->qn('#__vikbooking_busy'))
				->where($this->dbo->qn('idroom') . ' = ' . (int)$or['idroom'])
				->where($this->dbo->qn('checkout') . ' > ' . $earliest_checkin);

			$this->dbo->setQuery($q);
			$busy = $this->dbo->loadAssocList();

			/**
			 * In case pending reservations should keep the rooms occupied, merge the temporary locked room records.
			 * 
			 * @since 	1.8.20
			 */
			if (VCMRequestAvailability::syncWhenPending()) {
				$busy = array_merge($busy, VCMRequestAvailability::getInstance($reservation)->fetchTemporaryBusyRecords($or['idroom'], $earliest_checkin, $store_lock = true));
			}

			if ($busy) {
				foreach ($groupdays as $gday) {
					$oday = date('Y-m-d', $gday);
					$gday_info = getdate($gday);
					$midn_gday = mktime(0, 0, 0, $gday_info['mon'], $gday_info['mday'], $gday_info['year']);
					$bfound = 0;
					foreach ($busy as $bu) {
						$checkin_info = getdate($bu['checkin']);
						$checkout_info = getdate($bu['checkout']);
						$midn_checkin = mktime(0, 0, 0, $checkin_info['mon'], $checkin_info['mday'], $checkin_info['year']);
						$midn_checkout = mktime(0, 0, 0, $checkout_info['mon'], $checkout_info['mday'], $checkout_info['year']);
						if ($midn_gday >= $midn_checkin && $midn_gday < $midn_checkout) {
							// increase units booked
							$bfound++;
						}
					}
					if ($bfound >= $or['units']) {
						$rooms[$kor]['adates'][$oday]['newavail'] = 0;
					} else {
						$rooms[$kor]['adates'][$oday]['newavail'] = ($or['units'] - $bfound);
					}
				}
			} else {
				foreach ($groupdays as $gday) {
					$oday = date('Y-m-d', $gday);
					$rooms[$kor]['adates'][$oday]['newavail'] = $or['units'];
				}
			}
		}

		if ($rooms) {
			/**
			 * Make sure to sort the availability dates in ascending order to comply with the "compact" XML structure.
			 * 
			 * @since 	1.8.26
			 */
			foreach ($rooms as $kor => $room_data) {
				if (!isset($room_data['adates'])) {
					continue;
				}
				// sort by key (Y-m-d date) in ascending order
				ksort($room_data['adates']);
				// overwrite values
				$rooms[$kor]['adates'] = $room_data['adates'];
			}

			// invoke the Smart Balancer to adjust the remaining availability for the channels
			$rooms = VikChannelManager::getSmartBalancerInstance()->applyAvailabilityRulesOnSync($rooms, $reservation);

			return $rooms;
		}

		// store erroneous notification and return an empty array
		$this->saveNotify('0', 'VCM', 'e4j.error.Channels.NoSynchRooms', $this->order_id);

		return [];
	}
	
	private function getGroupDays($first, $second, $daysdiff)
	{
		$ret = [];
		$ret[] = $first;
		if ($daysdiff > 1) {
			$start = getdate($first);
			$end = getdate($second);
			$endcheck = mktime(0, 0, 0, $end['mon'], $end['mday'], $end['year']);
			for ($i = 1; $i < $daysdiff; $i++) {
				$checkday = $start['mday'] + $i;
				$dayts = mktime(0, 0, 0, $start['mon'], $checkday, $start['year']);
				if ($dayts != $endcheck) {
					$ret[] = $dayts;
				}
			}
		}
		//do not send the availability information about the checkout day
		//$ret[] = $second;

		return $ret;
	}

	private function getHoursRoomAvail()
	{
		try {
			$hours = (int)VBOFactory::getConfig()->get('hoursmoreroomavail', 0);
		} catch (Throwable $t) {
			$hours = 0;
		}

		return $hours;
	}

	/**
	 * Stores a notification in the db for VikChannelManager.
	 * 
	 * @param 	int 	$type 		type 0 (Error), 1 (Success), 2 (Warning).
	 * @param 	string 	$from 		the notification issuer.
	 * @param 	string 	$cont 		the notification message.
	 * @param 	int 	$idordervb 	the involved VBO booking ID.
	 * 
	 * @return 	mixed 				false in case of failure, notification ID stored otherwise.
	 */
	private function saveNotify($type, $from, $cont, $idordervb = 0)
	{
		$notif = new stdClass;
		$notif->ts 		  = time();
		$notif->type 	  = (int)$type;
		$notif->from 	  = $from;
		$notif->cont 	  = $cont;
		$notif->idordervb = (int)$idordervb;
		$notif->read 	  = 0;

		$this->dbo->insertObject('#__vikchannelmanager_notifications', $notif, 'id');

		return isset($notif->id) ? $notif->id : false;
	}

	/**
	 * Stores multiple notifications in the db for VikChannelManager.
	 * 
	 * @param 	array 	$arr_rs 	list of response strings.
	 * 
	 * @return 	bool
	 */
	private function saveMultipleNotifications($arr_rs)
	{
		$gen_type = 1;
		foreach ($arr_rs as $chid => $chrs) {
			if (substr($chrs, 0, 9) == 'e4j.error') {
				$gen_type = 0;
				break;
			} elseif (substr($chrs, 0, 11) == 'e4j.warning') {
				$gen_type = 2;
			}
		}

		// default operation result
		$result = false;

		// store parent notification
		$id_parent = $this->saveNotify($gen_type, 'VCM', 'Availability Update RQ', $this->order_id);

		// check for an extra content description for each child notification
		$extra_cont = '';
		if ($this->is_pending_lock) {
			$extra_cont = ' (' . VCMRequestAvailability::getInstance()->describePendingLockSync() . ')';
		} elseif ($this->cancelled_order && !empty($this->cancelled_order['notif_content'])) {
			$extra_cont = ' (' . $this->cancelled_order['notif_content'] . ')';
		}

		if ($id_parent) {
			// store children notifications
			foreach ($arr_rs as $chid => $chrs) {
				// build child notification object
				$child_notif = new stdClass;
				$child_notif->id_parent = $id_parent;
				if (substr($chrs, 0, 9) == 'e4j.error') {
					$child_notif->type = 0;
				} elseif (substr($chrs, 0, 11) == 'e4j.warning') {
					$child_notif->type = 2;
				} else {
					$child_notif->type = 1;
				}
				$child_notif->cont = $chrs . $extra_cont;
				$child_notif->channel = (int)$chid;

				// store child notification object
				$result = $this->dbo->insertObject('#__vikchannelmanager_notification_child', $child_notif, 'id') || $result;
			}
		}

		return $result;
	}

	/**
	 * Generates and Saves a notification key for e4jConnect and VikChannelManager
	 */
	private function generateNKey($idordervb)
	{
		$nkey = rand(1000, 9999);

		$q = "INSERT INTO `#__vikchannelmanager_keys` (`idordervb`,`key`) VALUES(" . (int)$idordervb . ", " . (int)$nkey . ");";
		$this->dbo->setQuery($q);
		$this->dbo->execute();

		return $nkey;
	}

	/**
	 * Finds all the rooms involved with a shared calendar.
	 * 
	 * @param 		array 	$orderrooms 	all rooms booked and modified.
	 * 
	 * @return 		array 	list of room IDs, names, units involved (or empty array).
	 * 
	 * @since 		VCM 1.7.1 (February 2020) - VBO (J)1.13/(WP)1.3.0 (February 2020)
	 *
	 * @requires 	VCM 1.7.1 - VBO (J)1.13/(WP)1.3.0
	 * 
	 * @uses 		VikBooking::updateSharedCalendars()
	 */
	private function getRoomsSharedCalsInvolved($orderrooms)
	{
		if (!class_exists('VikBooking')) {
			require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';
		}
		if (!method_exists('VikBooking', 'updateSharedCalendars')) {
			// VBO >= 1.13 (Joomla) - 1.3.0 (WordPress) is required.
			return [];
		}

		// gather all rooms booked or modified by this booking
		$roomids = [];
		foreach ($orderrooms as $or) {
			if (!in_array($or['idroom'], $roomids)) {
				array_push($roomids, $or['idroom']);
			}
		}

		// build room IDs not already involved
		$involved = [];
		try {
			$q = $this->dbo->getQuery(true)
				->select('*')
				->from($this->dbo->qn('#__vikbooking_calendars_xref'))
				->where([
					$this->dbo->qn('mainroom') . ' IN (' . implode(', ', array_map('intval', $roomids)) . ')',
					$this->dbo->qn('childroom') . ' IN (' . implode(', ', array_map('intval', $roomids)) . ')',
				], 'OR');

			$this->dbo->setQuery($q);
			$rooms_found = $this->dbo->loadAssocList();
			if ($rooms_found) {
				foreach ($rooms_found as $rf) {
					if (!in_array($rf['mainroom'], $roomids)) {
						array_push($involved, $rf['mainroom']);
					}
					if (!in_array($rf['childroom'], $roomids)) {
						array_push($involved, $rf['childroom']);
					}
				}
			}
		} catch (Throwable $t) {
			return [];
		}

		if (!$involved) {
			// do not proceed any further
			return [];
		}

		// make sure we do not have duplicate values
		$involved = array_unique($involved);

		// build extra rooms information
		$shared_rooms = [];

		// get information about names and units
		$q = $this->dbo->getQuery(true)
			->select($this->dbo->qn(['id', 'name', 'units']))
			->from($this->dbo->qn('#__vikbooking_rooms'))
			->where($this->dbo->qn('id') . ' IN (' . implode(', ', array_map('intval', $involved)) . ')');

		$this->dbo->setQuery($q);
		$extarooms = $this->dbo->loadAssocList();

		if ($extarooms) {
			foreach ($extarooms as $v) {
				$clone = $orderrooms[0];
				$clone['idroom'] = $v['id'];
				$clone['units'] = $v['units'];
				if (isset($clone['name'])) {
					$clone['name'] = $v['name'];
				} elseif (isset($clone['room_name'])) {
					$clone['room_name'] = $v['name'];
				}
				array_push($shared_rooms, $clone);
			}
		}

		return $shared_rooms;
	}
}
