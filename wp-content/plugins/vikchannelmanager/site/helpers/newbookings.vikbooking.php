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
 * This Class is used by VikChannelManager to process the new bookings
 * received from e4jConnect in the BR_L task.
 * Saves the new bookings into VikBooking and returns a response for
 * e4jConnect, which is hanging via CURL for the process to complete.
 */
class NewBookingsVikBooking
{	
	private $config;
	private $arrbookings;
	private $cypher;
	private $roomsinfomap;
	private $totbookings;
	private $savedbookings;
	private $arrconfirmnumbers;
	private $errorString;
	private $response;

	/**
	 * @var  	string  	The name of the channel for when in object context.
	 * 						This class can also be accessed statically to call
	 * 						some methods, so this serves for extra precision in
	 * 						finding an existing OTA reservation when in object
	 * 						context (BR_L), so not when in static context.
	 * @since 	1.6.8
	 */
	static $channelName = '';

	/**
	 * Flag to consider the processing of a booking as pending.
	 * 
	 * @var 	bool
	 * @since 	1.8.0
	 */
	private $pending_booking = false;

	/**
	 * Indicates a particular type of booking (Request to Book/Inquiry).
	 * 
	 * @var 	string
	 * @since 	1.8.0
	 */
	private $booking_type = null;

	/**
	 * Contains the overbooking details.
	 * 
	 * @var 	string
	 * @since 	1.8.20
	 */
	private $overbooking_info = '';

	/**
	 * Flag to detect if a booking modification was reverted to a new booking.
	 * 
	 * @var 	bool
	 * @since 	1.8.23
	 */
	private $modification_not_found = false;

	/**
	 * Pool of iCal bookings downloaded to check for cancellations.
	 * 
	 * @var 	array
	 * @since 	1.8.9
	 */
	private $ical_signature_map = [];

	/**
	 * Class constructor requires the VCM configuration array and list of bookings to parse.
	 * 
	 * @param 	array 	$config
	 * @param 	array 	$arrbookings
	 */
	public function __construct($config, $arrbookings)
	{
		$this->config = $config;
		$this->arrbookings = $arrbookings;

		if (!is_array($this->arrbookings)) {
			$this->arrbookings = [];
		}

		if (empty($this->arrbookings['orders'])) {
			$this->arrbookings['orders'] = [];
		}

		// load dependencies
		if (!class_exists('VikChannelManager') || !class_exists('VikApplication')) {
			require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikchannelmanager.php');
		}
		if (!class_exists('VikChannelManagerConfig')) {
			require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'vcm_config.php');
		}
		if (!class_exists('VikBooking')) {
			require_once(VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php");
		}
		if (!class_exists('SynchVikBooking')) {
			require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "synch.vikbooking.php");
		}

		// the salt is hashed twice
		$this->cypher = VikChannelManager::loadCypherFramework(md5($this->config['apikey']));

		$this->roomsinfomap = [];
		$this->totbookings = count($this->arrbookings['orders']);
		$this->savedbookings = 0;
		$this->arrconfirmnumbers = [];
		$this->errorString = '';
		$this->response = 'e4j.error';

		self::$channelName = $this->config['channel']['name'];
	}

	/**
	 * Main method called by VikChannelManager in the BR_L task.
	 * Processes the information received from e4jConnect.
	 * Returns the response for e4jConnect.
	 * 
	 * @return 	string
	 */
	public function processNewBookings()
	{
		if ((int)$this->config['vikbookingsynch'] < 1) {
			// VCM sync is disabled, so no bookings can come in
			$this->response = 'e4j.ok.vcmsynchdisabled';
			// store error notification so that the admin can see it
			$this->saveNotify(0, 'VCM', 'e4j.error.Channels.Auto-sync disabled');

			// return erroneous response
			return $this->response;
		}

		/**
		 * Container for the OTA reservation IDs that require an email notification for the guests.
		 * 
		 * @since 	1.8.12
		 */
		$email_scheduling = [];

		// process new bookings
		foreach ($this->arrbookings['orders'] as $bcount => $order) {
			if (!$this->checkOrderIntegrity($order)) {
				// could not validate the booking structure
				$this->saveNotify(0, ucwords($this->config['channel']['name']), "e4j.error.Channels.InvalidBooking\n" . $this->getError() . "\n" . print_r($order, true));
				// unset errors for next booking processing
				$this->errorString = '';

				// go to next booking
				continue;
			}

			/**
			 * We now support a flag to consider the booking status as pending.
			 * We also reset all flags indicating the particular type of booking.
			 * 
			 * @since 	1.8.0
			 */
			$this->pending_booking 		  = false;
			$this->booking_type    		  = null;
			$this->overbooking_info 	  = '';
			$this->modification_not_found = false;

			/**
			 * Listings on VRBO with a booking policy set to "QUOTE and HOLD" should be stored as pending.
			 * 
			 * @since 	1.8.20
			 */
			if ($this->config['channel']['uniquekey'] == VikChannelManagerConfig::VRBOAPI && !strcasecmp($order['info']['ordertype'], 'Book')) {
				// get listing's data
				$vrbo_listing_id   = preg_replace("/[^0-9]+/", '', (string)$order['roominfo'][0]['idroomota']);
				$vrbo_listing_data = VCMVrboListing::getListingObject($vrbo_listing_id);

				// access the listing's lodging configuration
				$vrbo_listing_lodging = (array)$vrbo_listing_data->get('lodging', []);
				if (isset($vrbo_listing_lodging['bookingPolicy']) && !strcasecmp($vrbo_listing_lodging['bookingPolicy'], 'QUOTEHOLD')) {
					/**
					 * The booking policy of this listing requires a manual approval for all bookings, hence
					 * the status will have to be pending (UNCONFIRMED). We overwrite the booking type.
					 */
					$order['info']['ordertype'] = 'Request';
					$this->arrbookings['orders'][$bcount]['info']['ordertype'] = 'Request';
				}
			}

			// process booking depending on its type
			switch ($order['info']['ordertype']) {
				case 'Book':
					/**
					 * In order to support accepted Inquiries (Special Offers) that are currently
					 * saved in VBO with a pending status, we check if an OTA booking with the same
					 * thread id already exists, to rather perform a booking modification request.
					 * 
					 * @since 	1.8.0
					 */
					$inquiry_booking = false;
					if (!empty($order['info']['thread_id'])) {
						if ($order['info']['idorderota'] == $order['info']['thread_id']) {
							// look for an ota booking with id equal to the thread id
							$inquiry_booking = self::otaBookingExists($order['info']['thread_id'], true);
						} else {
							/**
							 * In order to fully support Webhook notifications for Request to Book reservations
							 * becoming "confirmed" from "standby", we need to check if the OTA booking exists
							 * by using the confirmation code, something available for the "RtB" reservations,
							 * but not for the Booking Inquiries. In short, "RtB" reservations of Airbnb API
							 * have got identical payloads to regular "Instant Book" reservations. Therefore,
							 * we need to check the booking exists, so that we can update it rather than create it.
							 * Request to Book reservations are better to be confirmed through VBO rather than on Airbnb.
							 * Try to look for a "Booking Request" reservation previously stored with "standby" status,
							 * in this case the OTA reservation ID must be different than the Thread ID for the messaging.
							 * 
							 * @since 	1.8.2
							 */
							$inquiry_booking = self::otaBookingExists($order['info']['idorderota'], true);
						}
					}

					if ($inquiry_booking !== false && $inquiry_booking['status'] == 'standby') {
						// previous prending inquiry found, update it to a confirmed booking
						$result = $this->modifyBooking($order, $inquiry_booking);
					} else {
						// regular processing of a new booking
						$result = $this->saveBooking($order);
					}
					break;
				case 'Request':
					/**
					 * Request to Book reservation should create a new booking with pending status.
					 * 
					 * @since 	1.8.0
					 */
					$this->pending_booking = true;
					$this->booking_type = 'request';
					//
					$result = $this->saveBooking($order);
					break;
				case 'Inquiry':
					/**
					 * Booking Inquiry reservation should create a new booking with pending status.
					 * 
					 * @since 	1.8.0
					 */
					$this->pending_booking = true;
					$this->booking_type = 'inquiry';
					//
					$result = $this->saveBooking($order);
					break;
				case 'Modify':
					/**
					 * For any booking modification request we need to spend a query to
					 * determine if the previous reservation truly exists, or rather than
					 * sending an erroneous notification for the failed booking modification
					 * request, we should process the request as a new booking instead.
					 * 
					 * @since 	1.8.23
					 */
					$vbo_prev_booking = self::otaBookingExists($order['info']['idorderota'], true);
					if ($vbo_prev_booking) {
						// the original booking exists, so we can process a modification
						$result = $this->modifyBooking($order, $vbo_prev_booking);
					} else {
						// we need to process the modification as if it was a new booking

						// turn flag on
						$this->modification_not_found = true;

						// process the reservation
						$result = $this->saveBooking($order);
					}
					break;
				case 'Cancel':
					$result = $this->cancelBooking($order);
					break;
				case 'CancelRequest':
					/**
					 * Cancel Request to Book should cancel a booking with pending status.
					 * 
					 * @since 	1.8.0
					 */
					$this->pending_booking = true;
					$result = $this->cancelBooking($order);
					break;
				case 'CancelInquiry':
					/**
					 * Cancel Inquiry request should cancel a booking with pending status.
					 * 
					 * @since 	1.8.0
					 */
					$this->pending_booking = true;
					$result = $this->cancelBooking($order);
					break;
				case 'Download':
					$result = $this->downloadedBooking($order);
					break;
				default:
					break;
			}

			if ($result === true) {
				// increase number of bookings saved
				$this->savedbookings++;

				// check if a flag indicates we must notify the guest email address
				if (isset($order['info']['must_notify_guest']) && $order['info']['must_notify_guest']) {
					if (!in_array($order['info']['idorderota'], $email_scheduling)) {
						// schedule the email notification for this booking
						$email_scheduling[] = $order['info']['idorderota'];
					}
				}
			}

			if ($this->getError()) {
				/**
				 * Unset errors for next booking processing, do not store any error 
				 * notification as they must have been set already in case of errors.
				 */
				$this->errorString = '';
			}
		}
		
		// set the response for e4jConnect to the serialized array with the confirmation numbers or to an ok string
		if ($this->arrconfirmnumbers) {
			$this->arrconfirmnumbers['auth'] = md5($this->config['apikey'] . 'rs_e4j');
			$this->response = serialize($this->arrconfirmnumbers);
		} else {
			$this->response = 'e4j.ok.savedbookingsindb:0.savedbookings:' . $this->savedbookings;
		}

		/**
		 * Check if some iCal bookings were downloaded so that we can check for cancellations.
		 * No need to do it if some iCal bookings were saved into $this->ical_signature_map as
		 * it's safe to call this method also in case of API channels (nothing would happen).
		 * 
		 * @since 	1.8.9
		 */
		$this->iCalCheckNewCancellations();

		// dispatch the scheduled email notifications, if any
		foreach ($email_scheduling as $ota_res_id) {
			if (!isset($this->arrconfirmnumbers[$ota_res_id]) || empty($this->arrconfirmnumbers[$ota_res_id]['vborderid'])) {
				continue;
			}
			// send an email notification to the guest
			VikBooking::sendBookingEmail($this->arrconfirmnumbers[$ota_res_id]['vborderid'], ['guest']);
		}

		// return the response for e4jConnect
		return $this->response;
	}
	
	/**
	 * Checks that the single booking is valid and not
	 * missing some required values to be processed and saved.
	 * 
	 * @param 	array 	$order
	 * 
	 * @return 	bool
	 */
	public function checkOrderIntegrity($order)
	{
		$otype = '';
		switch ($order['info']['ordertype']) {
			case 'Book':
			case 'Request':
			case 'Inquiry':
				$otype = 'Book';
				break;
			case 'Modify':
				$otype = 'Modify';
				break;
			case 'Cancel':
			case 'CancelRequest':
			case 'CancelInquiry':
				$otype = 'Cancel';
				break;
			case 'Download':
				$otype = 'Download';
				break;
			default:
				$this->setError("1) checkOrderIntegrity: empty oType");
				return false;
		}
		
		// required properties: booking id, booking type and check-in, check-out for some channels
		$validate = [
			$order['info']['idorderota'], 
			$order['info']['ordertype'], 
			(isset($order['info']['checkin']) ? $order['info']['checkin'] : ''), 
			(isset($order['info']['checkout']) ? $order['info']['checkout'] : ''),
		];
		foreach ($validate as $k => $elem) {
			if (!strlen((string)$elem)) {
				if ($otype != 'Cancel') {
					$this->setError("2) checkOrderIntegrity: empty index " . $k);
					return false;
				}
				// booking cancellations may return empty checkin and checkout for some channels
				if ($k < 2) {
					$this->setError("3) checkOrderIntegrity: empty index " . $k);
					return false;
				}
			}
		}
		
		// make sure at least one room was passed
		$roomfound = false;
		if (isset($order['roominfo'])) {
			if (isset($order['roominfo']['idroomota']) && !empty($order['roominfo']['idroomota'])) {
				// array with single room structure
				$roomfound = true;
			} else {
				foreach ($order['roominfo'] as $elem) {
					if (isset($elem['idroomota']) && !empty($elem['idroomota'])) {
						// array with possible multiple rooms structure
						$roomfound = true;
						break;
					}
				}
			}
		}
		if ($otype != 'Cancel' && !$roomfound) {
			$this->setError("4) checkOrderIntegrity: empty IDRoomOTA");
			return false;
		}

		return true;
	}
	
	/**
	 * Saves a new booking into VikBooking.
	 * 
	 * @param 	array 	$order
	 */
	public function saveBooking($order)
	{
		if (self::otaBookingExists($order['info']['idorderota'])) {
			return false;
		}

		// idroomvb mapping the idroomota
		// check whether the room is one or more
		if (array_key_exists(0, $order['roominfo'])) {
			if (count($order['roominfo']) > 1) {
				// multiple rooms
				$check_idroomota = [];
				foreach ($order['roominfo'] as $rk => $ordr) {
					$check_idroomota[] = $ordr['idroomota'];
				}
			} else {
				// single room
				$check_idroomota = $order['roominfo'][0]['idroomota'];
			}
		} else {
			// single room
			$check_idroomota = $order['roominfo']['idroomota'];
		}

		// map OTA rooms to VBO rooms
		$idroomvb = $this->mapIdroomVbFromOtaId($check_idroomota);

		if (!(((!is_array($idroomvb) && intval($idroomvb) > 0) || (is_array($idroomvb) && $idroomvb)) && $idroomvb !== false)) {
			$this->setError("1) saveBooking: OTAid: ".$order['info']['idorderota']." - OTARoom ".$order['roominfo']['idroomota'].", not mapped");
			return false;
		}

		// check-in and check-out timestamps, num of nights for VikBooking
		$checkints = $this->getCheckinTimestamp($order['info']['checkin']);
		$checkoutts = $this->getCheckoutTimestamp($order['info']['checkout']);
		$numnights = $this->countNumberOfNights($checkints, $checkoutts);
		if (!($checkints > 0 && $checkoutts > 0 && $numnights > 0)) {
			$this->setError("2) saveBooking: OTAid: ".$order['info']['idorderota']." empty or invalid stay dates (".$order['info']['checkin']." - ".$order['info']['checkout'].")");
			return false;
		}

		// count num people, total order, compose customer info, purchaser email, special request
		$adults = 0;
		$children = 0;
		$pets = 0;
		if (isset($order['info']['adults'])) {
			$adults = (int)$order['info']['adults'];
		}
		if (isset($order['info']['children'])) {
			$children = (int)$order['info']['children'];
		}
		if (isset($order['info']['pets'])) {
			$pets = (int)$order['info']['pets'];
		}
		$total = 0;
		if (isset($order['info']['total'])) {
			$total = (float)$order['info']['total'];
		}
		$customerinfo = '';
		$purchaseremail = '';
		if (array_key_exists('customerinfo', $order)) {
			foreach ($order['customerinfo'] as $what => $cinfo) {
				if ($what == 'pic') {
					// the customer profile picture will be saved onto the database
					continue;
				}
				$customerinfo .= ucwords($what).": ".$cinfo."\n";
			}
			if (array_key_exists('email', $order['customerinfo'])) {
				$purchaseremail = $order['customerinfo']['email'];
			}
		}
		// add information about Breakfast, Extra-bed, IATA, Promotion and such
		if (array_key_exists('breakfast_included', $order['info'])) {
			$customerinfo .= 'Breakfast Included: '.$order['info']['breakfast_included']."\n";
		}
		if (array_key_exists('extrabed', $order['info'])) {
			$customerinfo .= 'Extra Bed: '.$order['info']['extrabed']."\n";
		}
		if (array_key_exists('IATA', $order['info'])) {
			$customerinfo .= 'IATA ID: '.$order['info']['IATA']."\n";
		}
		if (array_key_exists('promotion', $order['info'])) {
			$customerinfo .= 'Promotion: '.$order['info']['promotion']."\n";
		}
		if (array_key_exists('loyalty_id', $order['info'])) {
			$customerinfo .= 'Loyalty ID: '.$order['info']['loyalty_id']."\n";
		}
		$customerinfo = rtrim($customerinfo, "\n");

		// check if the room is available
		$room_available = false;
		if (is_array($idroomvb)) {
			// if $room_available is an array it means that some rooms were not available
			$room_available = $this->roomsAreAvailableInVb($idroomvb, $order, $checkints, $checkoutts, $numnights);
		} else {
			$check_idroomota_key = array_key_exists(0, $order['roominfo']) ? $order['roominfo'][0]['idroomota'] : $order['roominfo']['idroomota'];
			$room_available = $this->roomIsAvailableInVb($idroomvb, $this->roomsinfomap[$check_idroomota_key]['totunits'], $checkints, $checkoutts, $numnights);
		}

		/**
		 * New OTA bookings that generated an overbooking shall be saved as well. This check excludes
		 * iCal reservations (never saved) or pending bookings (they get saved). In case of overbooking
		 * we notify the admin and we turn the flags on before storing the new booking record in VikBooking.
		 * 
		 * @since 	1.8.20
		 */
		$is_overbooking = ($room_available === false && !isset($order['iCal']) && empty($this->booking_type));
		if ($is_overbooking) {
			// store an erroneous notification and notify the admin
			$errmsg = $this->notifyAdministratorRoomNotAvailable($order);
			$this->saveNotify('0', ucwords($this->config['channel']['name']), "e4j.error.Channels.BookingDownload\n" . $errmsg);

			// let the ReservationsLogger run even in case of overbooking, even if it will be a duplicate log after the sync
			VikChannelManager::getResLoggerInstance()
				->typeFromChannels([$this->config['channel']['uniquekey']])
				->trackLog($order);

			// turn overbooking flag on and set information
			$this->booking_type 	= 'overbooking';
			$this->overbooking_info = preg_replace("/^VikChannelManager\n*/", '', $errmsg);
		}

		// check if the booking can be saved
		if ($is_overbooking || $room_available === true || is_array($room_available) || ($this->pending_booking === true && in_array($this->booking_type, ['request', 'inquiry']))) {
			// decode credit card details
			$order['info']['credit_card'] = $this->processCreditCardDetails($order);

			// save the new order, set confirmnumber for the booking id in the class array arrconfirmnumbers and save notification in VCM
			$newdata = $this->saveNewVikBookingOrder($order, $idroomvb, $checkints, $checkoutts, $numnights, $adults, $children, $pets, $total, $customerinfo, $purchaseremail);

			/**
			 * Save an extra notification in the booking history in case this was saved
			 * as a pending request/inquiry reservation for dates with no availability.
			 * This way, VBO could check the event to display an additional alert in the
			 * booking details page.
			 * 
			 * @since 	1.8.3
			 */
			if (is_array($newdata) && !empty($newdata['newvborderid']) && $this->pending_booking && (!$room_available || is_array($room_available))) {
				// prepare extra data object for the event record
				$ev_data = new stdClass;
				$ev_data->pending_booking = 1;
				$ev_data->booking_type = $this->booking_type;
				$ev_data->no_availability = 1;
				$ev_data->unavailable_rooms = !$room_available ? array($idroomvb) : $room_available;

				// Booking History
				if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
					// store alert event to inform that there is no availability
					VikBooking::getBookingHistoryInstance()->setBid($newdata['newvborderid'])->setExtraData($ev_data)->store('CM', JText::_('VCM_EVALERT_PEND_NO_AV'));
				}
			}

			/**
			 * For the iCal bookings downloaded, we update the signature map.
			 * One iCal booking is always for one room only.
			 * 
			 * @since 	1.8.9
			 */
			if (is_array($newdata) && !empty($newdata['newvborderid']) && !empty($order['info']['ical_sign']) && !is_array($idroomvb)) {
				$this->ical_signature_map[$newdata['newvborderid']] = [
					'room_id' 	 => (int)$idroomvb,
					'channel_id' => $this->config['channel']['uniquekey'],
					'ota_bid' 	 => $order['info']['idorderota'],
					'signature'  => $order['info']['ical_sign'],
				];
			}

			// compose information about the RatePlan Name and the Payment
			$rateplan_info = $this->mapPriceVbFromRatePlanId($order);
			$notification_extra = '';
			if (!empty($rateplan_info)) {
				$notification_extra .= "\n".$rateplan_info;
			}
			if (isset($order['info']['price_breakdown']) && count($order['info']['price_breakdown'])) {
				$notification_extra .= "\nPrice Breakdown:\n";
				foreach ($order['info']['price_breakdown'] as $day => $cost) {
					$notification_extra .= $day." - ".$order['info']['currency'].' '.$cost."\n";
				}
				$notification_extra = rtrim($notification_extra, "\n");
			}
			if ($order['info']['credit_card']) {
				$notification_extra .= "\nCredit Card:\n";
				foreach ($order['info']['credit_card'] as $card_info => $card_data) {
					if ($card_info == 'card_number_pci') {
						//do not touch this part or you will lose any PCI-compliant function
						continue;
					}
					if (is_array($card_data)) {
						$notification_extra .= ucwords(str_replace('_', ' ', $card_info)).":\n";
						foreach ($card_data as $card_info_in => $card_data_in) {
							$notification_extra .= ucwords(str_replace('_', ' ', $card_info_in)).": ".$card_data_in."\n";
						}
					} else {
						$notification_extra .= ucwords(str_replace('_', ' ', $card_info)).": ".$card_data."\n";
					}
				}
				$notification_extra = rtrim($notification_extra, "\n");
			}

			if (!$is_overbooking) {
				// store successful notification
				$this->saveNotify('1', ucwords($this->config['channel']['name']), "e4j.OK.Channels.NewBookingDownloaded".$notification_extra, $newdata['newvborderid']);
			}

			// add values to be returned as serialized to e4jConnect as response
			if (!isset($this->arrconfirmnumbers[$order['info']['idorderota']])) {
				$this->arrconfirmnumbers[$order['info']['idorderota']] = [];
			}
			$this->arrconfirmnumbers[$order['info']['idorderota']]['ordertype'] = 'Book';
			$this->arrconfirmnumbers[$order['info']['idorderota']]['confirmnumber'] = $newdata['confirmnumber'];
			$this->arrconfirmnumbers[$order['info']['idorderota']]['vborderid'] = $newdata['newvborderid'];
			$this->arrconfirmnumbers[$order['info']['idorderota']]['nkey'] = $this->generateNKey($newdata['newvborderid']);

			/**
			 * For channels like Vrbo we must return a specific XML document and some additional properties.
			 * 
			 * @since 	1.8.12
			 */
			if ($this->config['channel']['uniquekey'] == VikChannelManagerConfig::VRBOAPI) {
				$this->arrconfirmnumbers[$order['info']['idorderota']]['xml_doc'] = VCMVrboXml::getInstance()->renderBookingUpdate($this->config['channel'], $render = false, $newdata['newvborderid']);
				$this->arrconfirmnumbers[$order['info']['idorderota']]['canc_policy'] = VCMVrboListing::describeCancellationPolicy((is_array($check_idroomota) ? $check_idroomota[0] : $check_idroomota));
			}

			/**
			 * Notify AV=1-Channels for the new booking, even if this was an overbooking scenario.
			 * 
			 * @since 	1.9.0  overbooking scenarios will also sync all channels,
			 * 				   and the property "overbooking: 1" is returned.
			 */
			$vcm = new SynchVikBooking($newdata['newvborderid'], [$this->config['channel']['uniquekey']]);
			$vcm->sendRequest();

			if ($is_overbooking) {
				// set property to identify the overbooking scenario
				$this->arrconfirmnumbers[$order['info']['idorderota']]['overbooking'] = 1;
			} else {
				// SMS
				VikBooking::sendBookingSMS($newdata['newvborderid']);
			}

			// always return true
			return true;
		}

		/**
		 * Rooms are not available in VikBooking, but if we reach this point it means we have parsed
		 * a duplicate iCal reservation, or yet an iCal reservation that could not be accommodated.
		 * We return true anyway for the E4jConnect central servers.
		 */
		return true;
	}
	
	/**
	 * Modifies an existing booking in VikBooking.
	 * 
	 * @param 	array 	$order 			the ota reservation array.
	 * @param 	array 	$prev_booking 	optional previous booking to modify.
	 * 
	 * @since 	1.8.0 	$prev_booking was added to modify a precise and existing reservation.
	 */
	public function modifyBooking($order, $prev_booking = null)
	{
		$dbo = JFactory::getDbo();

		if (is_array($prev_booking)) {
			$vbo_order_info = $prev_booking;
		} else {
			$vbo_order_info = self::otaBookingExists($order['info']['idorderota'], true);
		}

		if (!$vbo_order_info) {
			// the booking to modify does not exist in VikBooking or was cancelled before, notify VCM administrator (only if not iCal/ICS)
			if ($order['info']['ordertype'] != 'Download') {
				$message = JText::sprintf('VCMOTAMODORDERNOTFOUND', ucwords($this->config['channel']['name']), $order['info']['idorderota'], (is_array($check_idroomota) ? $check_idroomota[0] : $check_idroomota));
				$vik = new VikApplication(VersionListener::getID());
				$admail = $this->config['emailadmin'];
				$adsendermail = VikChannelManager::getSenderMail();
				$vik->sendMail(
					$adsendermail,
					$adsendermail,
					$admail,
					$admail,
					JText::_('VCMOTAMODORDERNOTFOUNDSUBJ'),
					$message,
					false
				);
				// VCM 1.6.8 - ReservationsLogger should run even when the SynchVikBooking Class is not called
				VikChannelManager::getResLoggerInstance()
					->typeModification(true)
					->typeFromChannels(array($this->config['channel']['uniquekey']))
					->trackLog($order);
				//
			}

			return false;
		}

		// idroomvb mapping the idroomota check whether the room is one or more
		if (array_key_exists(0, $order['roominfo'])) {
			if (count($order['roominfo']) > 1) {
				// multiple rooms
				$check_idroomota = array();
				foreach ($order['roominfo'] as $rk => $ordr) {
					$check_idroomota[] = $ordr['idroomota'];
				}
			} else {
				// single room
				$check_idroomota = $order['roominfo'][0]['idroomota'];
			}
		} else {
			// single room
			$check_idroomota = $order['roominfo']['idroomota'];
		}
		$idroomvb = $this->mapIdroomVbFromOtaId($check_idroomota);

		if ($idroomvb === false || !((!is_array($idroomvb) && intval($idroomvb) > 0) || (is_array($idroomvb) && $idroomvb))) {
			// room not mapped
			$this->setError("1) modifyBooking: OTAid: ".$order['info']['idorderota']." - OTARoom ".(is_array($check_idroomota) ? $check_idroomota[0] : $check_idroomota).", not mapped");
			return false;
		}

		// check-in and check-out timestamps, num of nights for VikBooking
		$checkints = $this->getCheckinTimestamp($order['info']['checkin']);
		$checkoutts = $this->getCheckoutTimestamp($order['info']['checkout']);
		$numnights = $this->countNumberOfNights($checkints, $checkoutts);
		if (!$checkints || !$checkoutts || $numnights <= 0) {
			// empty or invalid stay dates
			$this->setError("2) modifyBooking: OTAid: ".$order['info']['idorderota']." empty stay dates");
			return false;
		}

		// count num people, total order, compose customer info, purchaser email, special request
		$adults = 0;
		$children = 0;
		$pets = 0;
		if (isset($order['info']['adults'])) {
			$adults = (int)$order['info']['adults'];
		}
		if (isset($order['info']['children'])) {
			$children = (int)$order['info']['children'];
		}
		if (isset($order['info']['pets'])) {
			$pets = (int)$order['info']['pets'];
		}
		$total = 0;
		if (isset($order['info']['total'])) {
			$total = (float)$order['info']['total'];
		}
		$tot_taxes = 0;
		if (isset($order['info']['tax']) && floatval($order['info']['tax']) > 0) {
			$tot_taxes = floatval($order['info']['tax']);
		}
		/**
		 * Total city taxes can be collected from booking information.
		 * 
		 * @since 	1.8.0
		 */
		$tot_city_taxes = 0;
		if (isset($order['info']['city_tax']) && floatval($order['info']['city_tax']) > 0) {
			$tot_city_taxes = floatval($order['info']['city_tax']);
		}
		/**
		 * Total fees can be collected from booking information.
		 * 
		 * @since 	1.9.13
		 */
		$tot_fees = 0;
		if (isset($order['info']['fees']) && floatval($order['info']['fees']) > 0) {
			$tot_fees = floatval($order['info']['fees']);
		}
		$customerinfo = '';
		$purchaseremail = '';
		if (array_key_exists('customerinfo', $order)) {
			foreach ($order['customerinfo'] as $what => $cinfo) {
				if ($what == 'pic') {
					// the customer profile picture will be saved onto the database
					continue;
				}
				$customerinfo .= ucwords($what).": ".$cinfo."\n";
			}
			if (array_key_exists('email', $order['customerinfo'])) {
				$purchaseremail = $order['customerinfo']['email'];
			}
		}
		// add information about Breakfast, Extra-bed, IATA, Promotion and such
		if (array_key_exists('breakfast_included', $order['info'])) {
			$customerinfo .= 'Breakfast Included: '.$order['info']['breakfast_included']."\n";
		}
		if (array_key_exists('extrabed', $order['info'])) {
			$customerinfo .= 'Extra Bed: '.$order['info']['extrabed']."\n";
		}
		if (array_key_exists('IATA', $order['info'])) {
			$customerinfo .= 'IATA ID: '.$order['info']['IATA']."\n";
		}
		if (array_key_exists('promotion', $order['info'])) {
			$customerinfo .= 'Promotion: '.$order['info']['promotion']."\n";
		}
		if (array_key_exists('loyalty_id', $order['info'])) {
			$customerinfo .= 'Loyalty ID: '.$order['info']['loyalty_id']."\n";
		}
		//
		$customerinfo = rtrim($customerinfo, "\n");

		// get the busy ids for the order
		$q = "SELECT `idbusy` FROM `#__vikbooking_ordersbusy` WHERE `idorder`=" . (int) $vbo_order_info['id'] . ";";
		$dbo->setQuery($q);
		$excludebusyids = array_map('intval', array_column($dbo->loadAssocList(), 'idbusy'));

		// check if the room is available
		$room_available = false;
		if (is_array($idroomvb)) {
			$room_available = $this->roomsAreAvailableInVbModification($idroomvb, $order, $checkints, $checkoutts, $numnights, $excludebusyids);
			// TODO: if $room_available is an array it means that some rooms were not available:
			// administrator should be notified because one or more rooms, but not all, may be overbooked. Rare case.
		} else {
			$check_idroomota_key = array_key_exists(0, $order['roominfo']) ? $order['roominfo'][0]['idroomota'] : $order['roominfo']['idroomota'];
			$room_available = $this->roomIsAvailableInVbModification($idroomvb, $this->roomsinfomap[$check_idroomota_key]['totunits'], $checkints, $checkoutts, $numnights, $excludebusyids);
		}

		/**
		 * Modified OTA bookings that generated an overbooking shall be updated as well. In case of overbooking
		 * we notify the admin and we turn the flags on before updating the booking record in VikBooking.
		 * 
		 * @since 	1.9.12
		 */
		$is_overbooking = ($room_available === false && !isset($order['iCal']) && ($order['info']['ordertype'] ?? '') != 'Download' && empty($this->booking_type));

		if ($is_overbooking) {
			// store an erroneous notification and notify the admin
			$errmsg = $this->notifyAdministratorRoomNotAvailableModification($order, $vbo_order_info['id']);
			$this->saveNotify('0', ucwords($this->config['channel']['name']), "e4j.error.Channels.BookingModification\n".$errmsg);

			// let the ReservationsLogger run even in case of overbooking, even if it will be a duplicate log after the sync
			VikChannelManager::getResLoggerInstance()
				->typeModification(true)
				->typeFromChannels([$this->config['channel']['uniquekey']])
				->trackLog($order);

			// turn overbooking flag on and set information
			$this->booking_type 	= 'overbooking';
			$this->overbooking_info = preg_replace("/^VikChannelManager\n*/", '', $errmsg);
		}

		// check if the booking can be saved
		if ($is_overbooking || $room_available === true || is_array($room_available)) {
			// delete old booking-room relation record(s)
			$q = "DELETE FROM `#__vikbooking_ordersrooms` WHERE `idorder`=" . (int) $vbo_order_info['id'] . ";";
			$dbo->setQuery($q);
			$dbo->execute();

			// delete old booking-busy relation record(s)
			$q = "DELETE FROM `#__vikbooking_ordersbusy` WHERE `idorder`=" . (int) $vbo_order_info['id'] . ";";
			$dbo->setQuery($q);
			$dbo->execute();

			// always set $idroomvb to an array even if it is just a string
			$orig_idroomvb = $idroomvb;
			unset($idroomvb);
			if (is_array($orig_idroomvb)) {
				$idroomvb = array_values($orig_idroomvb);
			} else {
				$idroomvb = array($orig_idroomvb);
			}

			// number of rooms
			$num_rooms = 1;
			if (array_key_exists('num_rooms', $order['info']) && intval($order['info']['num_rooms']) > 1) {
				$num_rooms = intval($order['info']['num_rooms']);
			}

			// insert new busy records
			$busy_ids = [];
			for ($i = 1; $i <= $num_rooms; $i++) {
				// default room stay timestamps
				$room_checkints = $checkints;
				$room_checkoutts = $checkoutts;
				// set checkin and check out dates for each room if they are different than the check-in or check-out date of the booking (Booking.com)
				if (array_key_exists(($i - 1), $order['roominfo']) && array_key_exists('checkin', $order['roominfo'][($i - 1)]) && array_key_exists('checkout', $order['roominfo'][($i - 1)])) {
					if ($order['roominfo'][($i - 1)]['checkin'] != $order['info']['checkin'] || $order['roominfo'][($i - 1)]['checkout'] != $order['info']['checkout']) {
						$room_checkints = $this->getCheckinTimestamp($order['roominfo'][($i - 1)]['checkin']);
						$room_checkoutts = $this->getCheckinTimestamp($order['roominfo'][($i - 1)]['checkout']);
					}
				}

				// occupy room record
				$q = "INSERT INTO `#__vikbooking_busy` (`idroom`,`checkin`,`checkout`,`realback`) VALUES('" . $idroomvb[($i - 1)] . "', '" . $room_checkints . "', '" . $room_checkoutts . "','" . $room_checkoutts . "');";
				$dbo->setQuery($q);
				$dbo->execute();
				$busyid = $dbo->insertid();

				// push new record ID
				$busy_ids[$i] = $busyid;

				// store booking-busy relation record
				$q = "INSERT INTO `#__vikbooking_ordersbusy` (`idorder`,`idbusy`) VALUES(" . (int) $vbo_order_info['id'] . ", " . (int) $busyid . ");";
				$dbo->setQuery($q);
				$dbo->execute();
			}

			/**
			 * Delete old busy record ids after having occupied the listing(s) on the new dates.
			 * This is safer to cope with simultaneous requests and database latency.
			 * 
			 * @since 	1.9.18
			 */
			if ($excludebusyids) {
				$q = "DELETE FROM `#__vikbooking_busy` WHERE `id` IN (" . implode(", ", $excludebusyids) . ");";
				$dbo->setQuery($q);
				$dbo->execute();
			}

			// Adults and Children are returned as total by the OTA. If multiple rooms, dispose the Adults and Children accordingly
			$rooms_aduchild = [];
			if ($num_rooms > 1) {
				$adults_per_room = floor($adults / $num_rooms);
				$adults_per_room = $adults_per_room < 0 ? 0 : $adults_per_room;
				$spare_adults = ($adults - ($adults_per_room * $num_rooms));
				$children_per_room = floor($children / $num_rooms);
				$children_per_room = $children_per_room < 0 ? 0 : $children_per_room;
				$spare_children = ($children - ($children_per_room * $num_rooms));
				for ($i = 1; $i <= $num_rooms; $i++) {
					$adults_occupancy = $adults_per_room;
					$children_occupancy = $children_per_room;
					if ($i == 1 && ($spare_adults > 0 || $spare_children > 0)) {
						$adults_occupancy += $spare_adults;
						$children_occupancy += $spare_children;
					}
					$rooms_aduchild[$i]['adults'] = $adults_occupancy;
					$rooms_aduchild[$i]['children'] = $children_occupancy;
					if ($i === 1) {
						$rooms_aduchild[$i]['pets'] = $pets;
					}
				}
			} else {
				$rooms_aduchild[$num_rooms]['adults'] = $adults;
				$rooms_aduchild[$num_rooms]['children'] = $children;
				$rooms_aduchild[$num_rooms]['pets'] = $pets;
			}
			//
			$has_different_checkins_notif = false;

			// Phone Number and Customers Management (VikBooking 1.6 or higher, check if cpin.php exists - since v1.6)
			$phone = '';
			if (!empty($order['customerinfo']['telephone'])) {
				$phone = $order['customerinfo']['telephone'];
			} elseif (!empty($order['customerinfo']['phone'])) {
				$phone = $order['customerinfo']['phone'];
			}

			// country
			$country = '';
			if (isset($order['customerinfo']) && !empty($order['customerinfo']['country'])) {
				if (strlen($order['customerinfo']['country']) == 3) {
					$country = $order['customerinfo']['country'];
				} elseif (strlen($order['customerinfo']['country']) == 2) {
					$q = "SELECT `country_3_code` FROM `#__vikbooking_countries` WHERE `country_2_code`=".$dbo->quote($order['customerinfo']['country']).";";
					$dbo->setQuery($q);
					$country = $dbo->loadResult();
				} elseif (strlen($order['customerinfo']['country']) > 3) {
					$q = "SELECT `country_3_code` FROM `#__vikbooking_countries` WHERE `country_name` LIKE ".$dbo->quote('%'.$order['customerinfo']['country'].'%').";";
					$dbo->setQuery($q);
					$country = $dbo->loadResult();
				}
			}

			/**
			 * We need to format the phone number by prepending the country prefix if this is missing.
			 * 
			 * @since 	1.6.18
			 */
			if (!empty($phone) && !empty($country)) {
				// do not trim completely as the plus symbol may be a leading white-space
				$phone = rtrim($phone);

				if (substr($phone, 0, 1) == ' ' && strlen($phone) > 5) {
					/**
					 * Phone numbers inclusive of prefix with the plus symbol may be delivered by e4jConnect as a leading white space.
					 * The plus symbol gets printed as a white-space, and so this is what VCM gets. We should only right-trim until now.
					 * In these cases we apply the left trim to complete the trimming, then we prepend the plus symbol so that the phone
					 * number returned by the OTAs won't be touched as it's probably complete and inclusive of country prefix.
					 * 
					 * @since 	1.7.2
					 */
					$phone = ltrim($phone);
					$phone = '+' . $phone;
				}

				if (substr($phone, 0, 1) != '+' && substr($phone, 0, 2) != '00') {
					// try to find the country phone prefix since it's missing in the number
					$q = "SELECT `phone_prefix` FROM `#__vikbooking_countries` WHERE `country_" . (strlen($country) == 2 ? '2' : '3') . "_code`=" . $dbo->quote($country) . ";";
					$dbo->setQuery($q);
					$phone_prefix = $dbo->loadResult();
					if ($phone_prefix) {
						$country_prefix = str_replace(' ', '', $phone_prefix);
						$num_prefix = str_replace('+', '', $country_prefix);
						if (substr($phone, 0, strlen($num_prefix)) != $num_prefix) {
							// country prefix is completely missing
							$phone = $country_prefix . $phone;
						} else {
							// try to prepend the plus symbol because the phone number starts with the country prefix
							$phone = '+' . $phone;
						}
					}
				}
			}

			/**
			 * Customer Extra Info such as address, city, zip, company, vat are
			 * stored into an array that will be passed onto VikBookingCustomersPin.
			 * 
			 * @since 	1.6.12
			 * @since 	1.8.6 	added support for customer profile picture (avatar).
			 * @since 	1.8.12 	added support for customer state/province.
			 */
			$extra_info_keys = [
				'address',
				'city',
				'state',
				'zip',
				'company',
				'vat',
				'pic',
			];
			$customer_extra_info = [];
			foreach ($extra_info_keys as $extra_key) {
				if (isset($order['customerinfo']) && !empty($order['customerinfo'][$extra_key])) {
					$customer_extra_info[$extra_key] = $order['customerinfo'][$extra_key];
				}
			}
			
			$traveler_first_name = array_key_exists('traveler_first_name', $order['info']) ? $order['info']['traveler_first_name'] : '';
			$traveler_last_name = array_key_exists('traveler_last_name', $order['info']) ? $order['info']['traveler_last_name'] : '';
			
			// default Tax Rate ID
			$default_tax_rate = $this->getDefaultTaxID();

			// assign room specific unit
			$set_room_indexes = $this->autoRoomUnit();
			$room_indexes_usemap = array();

			// list of OTA rate plan IDs involved
			$ota_rplan_ids = [];

			// iterate the occupied room records
			foreach ($busy_ids as $num_room => $id_busy) {
				// traveler name for each room if available
				$room_t_first_name = $traveler_first_name;
				$room_t_last_name = $traveler_last_name;
				if (array_key_exists(($num_room - 1), $order['roominfo'])) {
					if (strlen($order['roominfo'][($num_room - 1)]['traveler_first_name'])) {
						$room_t_first_name = $order['roominfo'][($num_room - 1)]['traveler_first_name'];
						$room_t_last_name = $order['roominfo'][($num_room - 1)]['traveler_last_name'];
					}
				}

				// set checkin and check out dates next to traveler name if they are different than the check-in or check-out (Booking.com)
				if (array_key_exists(($num_room - 1), $order['roominfo']) && array_key_exists('checkin', $order['roominfo'][($num_room - 1)]) && array_key_exists('checkout', $order['roominfo'][($num_room - 1)])) {
					if ($order['roominfo'][($num_room - 1)]['checkin'] != $order['info']['checkin'] || $order['roominfo'][($num_room - 1)]['checkout'] != $order['info']['checkout']) {
						$room_t_last_name .= ' ('.$order['roominfo'][($num_room - 1)]['checkin'].' - '.$order['roominfo'][($num_room - 1)]['checkout'].')';
						// notification details (Booking.com) with guests, check-in and check-out dates for this room
						if (!is_array($has_different_checkins_notif)) {
							unset($has_different_checkins_notif);
							$has_different_checkins_notif = array();
						}
						$has_different_checkins_notif[] = $this->roomsinfomap[$order['roominfo'][($num_room - 1)]['idroomota']]['roomnamevb'].' - Check-in: '.$order['roominfo'][($num_room - 1)]['checkin'].' - Check-out: '.$order['roominfo'][($num_room - 1)]['checkout'].' - Guests: '.$order['roominfo'][($num_room - 1)]['guests'];
						//
					} else {
						// Maybe the check-in and check-out dates for the whole booking have now been set to the same ones as for this room, compare it with the old order with the date format Y-m-d
						$booking_prev_checkin = date('Y-m-d', $vbo_order_info['checkin']);
						$booking_prev_checkout = date('Y-m-d', $vbo_order_info['checkout']);
						if ($order['roominfo'][($num_room - 1)]['checkin'] != $booking_prev_checkin || $order['roominfo'][($num_room - 1)]['checkout'] != $booking_prev_checkout) {
							//notification details (Booking.com) with guests, check-in and check-out dates for this room
							if (!is_array($has_different_checkins_notif)) {
								unset($has_different_checkins_notif);
								$has_different_checkins_notif = array();
							}
							$has_different_checkins_notif[] = $this->roomsinfomap[$order['roominfo'][($num_room - 1)]['idroomota']]['roomnamevb'].' - Check-in: '.$order['roominfo'][($num_room - 1)]['checkin'].' - Check-out: '.$order['roominfo'][($num_room - 1)]['checkout'].' - Guests: '.$order['roominfo'][($num_room - 1)]['guests'];
							//
						}
					}
				}

				// assign room specific unit
				$room_indexes = $set_room_indexes === true ? $this->getRoomUnitNumsAvailable(array('id' => $vbo_order_info['id'], 'checkin' => $checkints, 'checkout' => $checkoutts), $idroomvb[($num_room - 1)]) : array();
				$use_ind_key = 0;
				if (count($room_indexes)) {
					if (!array_key_exists($idroomvb[($num_room - 1)], $room_indexes_usemap)) {
						$room_indexes_usemap[$idroomvb[($num_room - 1)]] = $use_ind_key;
					} else {
						$use_ind_key = $room_indexes_usemap[$idroomvb[($num_room - 1)]];
					}
					$rooms[$num]['roomindex'] = (int)$room_indexes[$use_ind_key];
				}

				// OTA Rate Plan for this room booked
				$otarplan_supported = $this->otaRplanSupported();
				$room_otarplan = '';
				if (isset($order['roominfo'][($num_room - 1)]) && isset($order['roominfo'][($num_room - 1)]['rateplanid'])) {
					$room_otarplan = $order['roominfo'][($num_room - 1)]['rateplanid'];
				} elseif (isset($order['roominfo']['rateplanid'])) {
					$room_otarplan = $order['roominfo']['rateplanid'];
				}
				if ($room_otarplan && !in_array($room_otarplan, $ota_rplan_ids)) {
					$ota_rplan_ids[] = $room_otarplan;
				}
				list($room_otarplan, $meals_included) = $this->getOtaRplanDataFromId($room_otarplan, (int)$idroomvb[($num_room - 1)]);

				/**
				 * Determine whether the application of taxes should be forced for some channels.
				 * 
				 * @since 	1.8.4
				 */
				$force_taxes = false;

				/**
				 * Set room exact cost (if available). Useful to print
				 * the cost of this room in case of multiple rooms booked.
				 * 
				 * @since 	1.6.13
				 */
				$now_room_cost = round(($total / $num_rooms), 2);
				if (isset($order['roominfo'][($num_room - 1)]) && isset($order['roominfo'][($num_room - 1)]['room_cost']) && floatval($order['roominfo'][($num_room - 1)]['room_cost']) > 0) {
					$now_room_cost = (float)$order['roominfo'][($num_room - 1)]['room_cost'];
					/**
					 * Hosts eligible for taxes working with Airbnb and using prices inclusive of tax
					 * in VBO may need to have the listing base cost inclusive of tax, as it's returned
					 * by the e4jConnect servers before taxes, due to the missing tax rate information
					 * and by other extra services that may still be subjected to tax. The same thing
					 * works for the channel Vrbo API introduced with VCM v1.8.12.
					 * 
					 * @since 	1.8.4
					 */
					$otas_before_tax = [
						VikChannelManagerConfig::AIRBNBAPI,
						VikChannelManagerConfig::VRBOAPI,
					];
					if (VikBooking::ivaInclusa() && $tot_taxes > 0 && $total > $now_room_cost && in_array($this->config['channel']['uniquekey'], $otas_before_tax)) {
						// room exact cost should be inclusive of taxes (force taxes to be applied)
						$now_room_cost = VikBooking::sayPackagePlusIva($now_room_cost, $default_tax_rate, true);
						// turn flag on
						$force_taxes = true;
					}
				}

				/**
				 * Expedia reservations now return the exact room cost, which is always before taxes.
				 * However, for those working with prices after tax, we should not use this value.
				 * 
				 * @since 	1.8.12
				 */
				if ($this->config['channel']['uniquekey'] == VikChannelManagerConfig::EXPEDIA && VikBooking::ivaInclusa()) {
					// Expedia bookings will always contain just one room-type
					$now_room_cost = round(($total / $num_rooms), 2);
				}

				/**
				 * We try to get the exact number of adults and children from 'roominfo'
				 * because some channels may support this obvious information. Pets are
				 * also supported by recent versions of VBO.
				 * 
				 * @since 	1.6.22
				 * @since 	1.8.12 added support for pets.
				 */
				if (isset($order['roominfo'][($num_room - 1)])) {
					if (isset($order['roominfo'][($num_room - 1)]['adults'])) {
						$rooms_aduchild[$num_room]['adults'] = (int)$order['roominfo'][($num_room - 1)]['adults'];
					}
					if (isset($order['roominfo'][($num_room - 1)]['children'])) {
						$rooms_aduchild[$num_room]['children'] = (int)$order['roominfo'][($num_room - 1)]['children'];
					}
					if (isset($order['roominfo'][($num_room - 1)]['pets'])) {
						$rooms_aduchild[$num_room]['pets'] = (int)$order['roominfo'][($num_room - 1)]['pets'];
					}
				}

				/**
				 * Extracosts for the reservation (AddOns) like Parking or Breakfast.
				 * VCM may get this information from some OTAs.
				 * 
				 * @since 	1.7.0
				 * @since 	1.8.12 added support for the property "type" to identify the extra service.
				 */
				$extracosts = [];
				if (isset($order['roominfo'][($num_room - 1)]) && isset($order['roominfo'][($num_room - 1)]['extracosts']) && is_array($order['roominfo'][($num_room - 1)]['extracosts'])) {
					foreach ($order['roominfo'][($num_room - 1)]['extracosts'] as $ec) {
						if (!is_array($ec) || !$ec) {
							continue;
						}
						// normalize extra cost type structure
						$ec['type'] = empty($ec['type']) && !empty($ec['info']['type']) ? $ec['info']['type'] : ($ec['type'] ?? null);
						// whether taxes should be added
						$use_force_taxes = $force_taxes;
						if (!empty($ec['type']) && is_string($ec['type'])) {
							if (!strcasecmp($ec['type'], 'TOURIST_TAX') || !strcasecmp($ec['type'], 'DEPOSIT') || !strcasecmp($ec['type'], 'ENV_FEE')) {
								/**
								 * It is okay to force taxes for Airbnb/VRBO reservations, but only certain extra costs
								 * should have taxes added. For example "Pet Fees", but not Tourist Tax, Deposit or Env Fee.
								 * 
								 * @since 	1.8.16
								 * @since 	1.9.17 added support to exclude the environamental fee from VAT/GST
								 */
								$use_force_taxes = false;
							}
						}
						// prepare extra-cost object
						$ecdata = new stdClass;
						$ecdata->name = $ec['name'];
						$ecdata->cost = (float)$ec['cost'];
						$ecdata->idtax = '';
						// apply taxes, if applicable
						if ($use_force_taxes) {
							$ecdata->cost = VikBooking::sayOptionalsPlusIva($ecdata->cost, $default_tax_rate, true);
							$ecdata->idtax = !empty($default_tax_rate) ? $default_tax_rate : $ecdata->idtax;
						}
						if (!empty($ec['type']) && is_string($ec['type'])) {
							$ecdata->type = $ec['type'];
						}
						if (isset($ec['ota_collected']) && is_scalar($ec['ota_collected'])) {
							$ecdata->ota_collected = $ec['ota_collected'];
						}
						array_push($extracosts, $ecdata);
					}
				}

				// insert object in "ordersrooms"
				$or_data = new stdClass;
				$or_data->idorder = (int)$vbo_order_info['id'];
				$or_data->idroom = (int)$idroomvb[($num_room - 1)];
				$or_data->adults = (int)$rooms_aduchild[$num_room]['adults'];
				$or_data->children = (int)$rooms_aduchild[$num_room]['children'];
				if ($this->petsSupported()) {
					$or_data->pets = isset($rooms_aduchild[$num_room]['pets']) ? (int)$rooms_aduchild[$num_room]['pets'] : 0;
				}
				$or_data->t_first_name = $room_t_first_name;
				$or_data->t_last_name = $room_t_last_name;
				if (count($room_indexes)) {
					$or_data->roomindex = (int)$room_indexes[$use_ind_key];
				}
				if ($this->setCommissions()) {
					$or_data->cust_cost = $now_room_cost;
					$or_data->cust_idiva = $default_tax_rate;
				}
				if (count($extracosts) && $otarplan_supported) {
					$or_data->extracosts = json_encode($extracosts);
				}
				if ($otarplan_supported) {
					$or_data->otarplan = $room_otarplan;
				}
				if ($meals_included && $this->mealPlansSupported()) {
					$or_data->meals = VBOMealplanManager::getInstance()->buildOTAMealPlans($meals_included);
				}
				$dbo->insertObject('#__vikbooking_ordersrooms', $or_data, 'id');

				// assign room specific unit
				if (count($room_indexes)) {
					$room_indexes_usemap[$idroomvb[($num_room - 1)]]++;
				}
			}

			/**
			 * Check if the ota type data should be updated as well.
			 * 
			 * @since 	1.8.0
			 * @since 	1.9.12 added support to "pay_type" to detect VCC payments like "PBB" (Payments by Booking.com).
			 * @since 	1.9.14 added support to "expected_payout" to store the expected OTA payment.
			 */
			$ota_type_data = null;
			if ($this->isBookingTypeSupported()) {
				$ota_type_data = !empty($vbo_order_info['ota_type_data']) ? json_decode($vbo_order_info['ota_type_data'], true) : [];
				$ota_type_data = !is_array($ota_type_data) ? [] : $ota_type_data;
				if (!empty($order['info']['thread_id'])) {
					$ota_type_data['thread_id'] = $order['info']['thread_id'];
				}
				if (!empty($order['info']['pay_type'])) {
					$ota_type_data['pay_type'] = $order['info']['pay_type'];
				}
				if (!empty($order['info']['expected_payout'])) {
					$ota_type_data['expected_payout'] = $order['info']['expected_payout'];
				}
				$ota_type_data['rateplan_ids'] = $ota_rplan_ids;
				$ota_type_data = json_encode($ota_type_data);
			}

			/**
			 * Update booking record.
			 * 
			 * @since 	1.8.24  phone number is updated in case Airbnb RtB reservations become confirmed.
			 * @since 	1.8.26  amount paid is also updated in case of no cc details (OTA Collect).
			 * @since 	1.8.27  commissions amount (service fees) are also updated, if available.
			 */
			$q = $dbo->getQuery(true)
				->update($dbo->qn('#__vikbooking_orders'))
				->set($dbo->qn('custdata') . ' = ' . $dbo->q($customerinfo))
				->set($dbo->qn('status') . ' = ' . $dbo->q('confirmed'))
				->set($dbo->qn('days') . ' = ' . $numnights)
				->set($dbo->qn('checkin') . ' = ' . $dbo->q($checkints))
				->set($dbo->qn('checkout') . ' = ' . $dbo->q($checkoutts))
				->set($dbo->qn('custmail') . ' = ' . $dbo->q($purchaseremail))
				->set($dbo->qn('roomsnum') . ' = ' . $num_rooms)
				->set($dbo->qn('total') . ' = ' . $dbo->q($total));

			if (isset($order['info']['total_paid']) && empty($vbo_order_info['paymentlog'])) {
				$q->set($dbo->qn('totpaid') . ' = ' . (float) $order['info']['total_paid']);
			}

			if ($tot_taxes > 0) {
				$q->set($dbo->qn('tot_taxes') . ' = ' . $dbo->q($tot_taxes));
			}

			if ($tot_city_taxes > 0) {
				$q->set($dbo->qn('tot_city_taxes') . ' = ' . $dbo->q($tot_city_taxes));
			}

			if ($tot_fees > 0) {
				$q->set($dbo->qn('tot_fees') . ' = ' . $dbo->q($tot_fees));
			}

			if (isset($phone) && $phone) {
				$q->set($dbo->qn('phone') . ' = ' . $dbo->q($phone));
			}

			if (isset($order['info']['commission_amount']) && $order['info']['commission_amount'] > 0) {
				$q->set($dbo->qn('cmms') . ' = ' . $dbo->q($order['info']['commission_amount']));
			}

			$q->set($dbo->qn('idorderota') . ' = ' . $dbo->q($order['info']['idorderota']));

			if ($this->isBookingTypeSupported() && !empty($this->booking_type)) {
				// set the booking type (i.e. "overbooking")
				$q->set($dbo->qn('type') . ' = ' . $dbo->q($this->booking_type));
			}

			if (!empty($ota_type_data)) {
				$q->set($dbo->qn('ota_type_data') . ' = ' . $dbo->q($ota_type_data));
			}

			$q->set($dbo->qn('channel') . ' = ' . $dbo->q($this->config['channel']['name'].'_'.$order['info']['source']));
			$q->set($dbo->qn('chcurrency') . ' = ' . $dbo->q($order['info']['currency']));

			$q->where($dbo->qn('id') . ' = ' . (int)$vbo_order_info['id']);

			/**
			 * Fallback to avoid queries to fail because of unsupported encoding of $customerinfo.
			 * It has happened that some bookings could not be saved because of Emoji characters causing
			 * an SQL error 1366 (Incorrect string value \xF0\x9F\x99\x82.\xE2). The database character set
			 * and collate should be set to utf8mb4 in order to support special characters such as Emoji.
			 * 
			 * @since 1.6.13
			 */
			try {
				// execute the query
				$dbo->setQuery($q);
				$dbo->execute();

				$upd_affect = $dbo->getAffectedRows();
			} catch (Exception $e) {
				$upd_affect = 0;
			}

			if (!$upd_affect) {
				// we try to update the booking with no customer information, as for sure that's the value that caused the error
				$q = $dbo->getQuery(true)
					->update($dbo->qn('#__vikbooking_orders'))
					->set($dbo->qn('status') . ' = ' . $dbo->q('confirmed'))
					->set($dbo->qn('days') . ' = ' . $numnights)
					->set($dbo->qn('checkin') . ' = ' . $dbo->q($checkints))
					->set($dbo->qn('checkout') . ' = ' . $dbo->q($checkoutts))
					->set($dbo->qn('custmail') . ' = ' . $dbo->q($purchaseremail))
					->set($dbo->qn('roomsnum') . ' = ' . $num_rooms)
					->set($dbo->qn('total') . ' = ' . $dbo->q($total));

				if (isset($order['info']['total_paid']) && empty($vbo_order_info['paymentlog'])) {
					$q->set($dbo->qn('totpaid') . ' = ' . (float) $order['info']['total_paid']);
				}

				if ($tot_taxes > 0) {
					$q->set($dbo->qn('tot_taxes') . ' = ' . $dbo->q($tot_taxes));
				}

				if ($tot_city_taxes > 0) {
					$q->set($dbo->qn('tot_city_taxes') . ' = ' . $dbo->q($tot_city_taxes));
				}

				if ($tot_fees > 0) {
					$q->set($dbo->qn('tot_fees') . ' = ' . $dbo->q($tot_fees));
				}

				if (isset($phone) && $phone) {
					$q->set($dbo->qn('phone') . ' = ' . $dbo->q($phone));
				}

				$q->set($dbo->qn('idorderota') . ' = ' . $dbo->q($order['info']['idorderota']));

				if ($this->isBookingTypeSupported() && !empty($this->booking_type)) {
					// set the booking type (i.e. "overbooking")
					$q->set($dbo->qn('type') . ' = ' . $dbo->q($this->booking_type));
				}

				if (!empty($ota_type_data)) {
					$q->set($dbo->qn('ota_type_data') . ' = ' . $dbo->q($ota_type_data));
				}

				$q->set($dbo->qn('channel') . ' = ' . $dbo->q($this->config['channel']['name'].'_'.$order['info']['source']));
				$q->set($dbo->qn('chcurrency') . ' = ' . $dbo->q($order['info']['currency']));

				$q->where($dbo->qn('id') . ' = ' . (int)$vbo_order_info['id']);

				$dbo->setQuery($q);
				$dbo->execute();

				$upd_affect = $dbo->getAffectedRows();
			}

			// save/update customer (VikBooking 1.6 or higher)
			if (!empty($traveler_first_name) && !empty($traveler_last_name) && !empty($purchaseremail)) {
				try {
					if (!class_exists('VikBookingCustomersPin')) {
						require_once(VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "cpin.php");
					}
					$cpin = new VikBookingCustomersPin;
					/**
					 * Customer Extra Info such as address, city, zip, company, vat.
					 * 
					 * @since 	1.6.12
					 * @since 	1.8.6 	added detection for VBO to support customer profile picture (avatar).
					 * @since 	1.8.12 	added detection for VBO to support customer state/province.
					 */
					if (!method_exists($cpin, 'supportsProfileAvatar') && isset($customer_extra_info['pic'])) {
						unset($customer_extra_info['pic']);
					}
					if (!method_exists($cpin, 'supportsStateProvince') && isset($customer_extra_info['state'])) {
						unset($customer_extra_info['state']);
					}
					$cpin->setCustomerExtraInfo($customer_extra_info);
					//
					$cpin->saveCustomerDetails($traveler_first_name, $traveler_last_name, $purchaseremail, $phone, $country, array());
					$cpin->saveCustomerBooking($vbo_order_info['id']);
				} catch (Exception $e) {
					// do nothing
				}
			}

			/**
			 * Take care of eventually shared calendars for the rooms involved.
			 * 
			 * @since 	1.7.1
			 */
			$this->updateSharedCalendars($vbo_order_info['id'], true);
			//

			// compose notification detail message
			$notifymess = "OTA Booking ID: ".$order['info']['idorderota']."\n";
			if ($has_different_checkins_notif === false) {
				// only if the check-in and check-out are the same for each room
				$notifymess .= "Check-in: ".$order['info']['checkin']." (Before Modification: ".date('Y-m-d', $vbo_order_info['checkin']).")\n";
				$notifymess .= "Check-out: ".$order['info']['checkout']." (Before Modification: ".date('Y-m-d', $vbo_order_info['checkout']).")\n";
			}
			$oldroomdata = "";
			if (array_key_exists('rooms_info', $vbo_order_info) && count($vbo_order_info['rooms_info']) > 0) {
				$prev_adults = 0;
				$prev_children = 0;
				$prev_rooms = array();
				foreach ($vbo_order_info['rooms_info'] as $room_info) {
					$prev_adults += $room_info['adults'];
					$prev_children += $room_info['children'];
					$prev_rooms[] = $room_info['roomnamevb'];
				}
				$oldroomdata = " (Before Modification: ".implode(", ", $prev_rooms)." - Adults: ".$prev_adults.($prev_children > 0 ? " - Children: ".$prev_children : "").")";
			}
			$all_vb_room_names = array();
			foreach ($this->roomsinfomap as $idrota => $room_det) {
				$all_vb_room_names[] = $room_det['roomnamevb'];
			}
			if ($has_different_checkins_notif === false) {
				$notifymess .= "Room: ".implode(', ', $all_vb_room_names)." - Adults: ".$adults.($children > 0 ? " - Children: ".$children : "").$oldroomdata."\n";
			} else {
				// only if the check-in and check-out are different for some rooms (Booking.com)
				$notifymess .= "Rooms:\n".implode("\n", $has_different_checkins_notif)."\n".ltrim($oldroomdata);
			}
			// decode credit card details
			$order['info']['credit_card'] = $this->processCreditCardDetails($order);
			$notification_extra = '';
			$price_breakdown = '';
			// price breakdown
			if (isset($order['info']['price_breakdown']) && count($order['info']['price_breakdown'])) {
				$price_breakdown .= "\nPrice Breakdown:\n";
				foreach ($order['info']['price_breakdown'] as $day => $cost) {
					$price_breakdown .= $day." - ".$order['info']['currency'].' '.$cost."\n";
				}
				$price_breakdown = rtrim($price_breakdown, "\n");
			}

			$payment_log = '';
			if (count($order['info']['credit_card']) > 0) {
				$notification_extra .= "\nCredit Card:\n";
				foreach ($order['info']['credit_card'] as $card_info => $card_data) {
					if ($card_info == 'card_number_pci') {
						//do not touch this part or you will lose any PCI-compliant function
						continue;
					}
					if (is_array($card_data)) {
						$notification_extra .= ucwords(str_replace('_', ' ', $card_info)).":\n";
						foreach ($card_data as $card_info_in => $card_data_in) {
							$notification_extra .= ucwords(str_replace('_', ' ', $card_info_in)).": ".$card_data_in."\n";
						}
					} else {
						$notification_extra .= ucwords(str_replace('_', ' ', $card_info)).": ".$card_data."\n";
					}
				}
				$payment_log = $notification_extra."\n\n";
			}

			// update payment log with credit card details
			if (!empty($payment_log)) {
				$q = "UPDATE `#__vikbooking_orders` SET `paymentlog`=CONCAT(".$dbo->quote($payment_log).", `paymentlog`) WHERE `id`=".(int)$vbo_order_info['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				$this->sendCreditCardDetails($order);
			}
			
			// Booking History
			if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
				// get the history object instance
				$history_obj = VikBooking::getBookingHistoryInstance()->setBid($vbo_order_info['id']);

				/**
				 * Before saving the regular history event about the booking modification, by letting VBO calculate the
				 * differences between the current and previous data, we store another event in the history in case the
				 * previous status of the reservation was "standby", because this was actually a booking confirmation.
				 * This is useful to understand when Webhook notifications come for acceptance of "RtB" or "Inquiries".
				 * 
				 * @since 	1.8.2
				 */
				if (!empty($vbo_order_info['status']) && $vbo_order_info['status'] == 'standby') {
					// store an extra event first
					$history_obj->store('MC', JText::_('VCM_OTACONFRES_FROM_PENDING'));
				}

				// store the OTA booking modification event
				$history_obj->setPrevBooking($vbo_order_info)->store('MC');

				if ($this->booking_type === 'overbooking') {
					/**
					 * Store an additional history record in case the booking modification generated an overbooking scenario.
					 * 
					 * @since 	1.9.12
					 */

					// history record for the overbooking event
					$history_obj->store('OB', $this->overbooking_info);
				}
			}

			// save notification
			$notifymess .= $price_breakdown.$notification_extra;
			$this->saveNotify('1', ucwords($this->config['channel']['name']), "e4j.OK.Channels.BookingModified\n".$notifymess, $vbo_order_info['id']);

			// add values to be returned as serialized to e4jConnect as response
			if (!isset($this->arrconfirmnumbers[$order['info']['idorderota']])) {
				$this->arrconfirmnumbers[$order['info']['idorderota']] = [];
			}
			$this->arrconfirmnumbers[$order['info']['idorderota']]['ordertype'] = 'Modify';
			$this->arrconfirmnumbers[$order['info']['idorderota']]['confirmnumber'] = $vbo_order_info['confirmnumber'].'mod';
			$this->arrconfirmnumbers[$order['info']['idorderota']]['vborderid'] = $vbo_order_info['id'];
			$this->arrconfirmnumbers[$order['info']['idorderota']]['nkey'] = $this->generateNKey($vbo_order_info['id']);
			
			// Notify AV=1-Channels for the booking modification
			$vcm = new SynchVikBooking($vbo_order_info['id'], array($this->config['channel']['uniquekey']));
			$vcm->setFromModification($vbo_order_info);
			$vcm->sendRequest();
			
			// SMS
			VikBooking::sendBookingSMS($vbo_order_info['id']);

			// booking was processed correctly
			return true;
		} else {
			// the room is not available for modification in VikBooking, notify Administrator but return true anyways for e4jConnect
			$errmsg = $this->notifyAdministratorRoomNotAvailableModification($order, $vbo_order_info['id']);

			// Booking History
			if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
				VikBooking::getBookingHistoryInstance()->setBid($vbo_order_info['id'])->store('MC', $errmsg);
			}

			// store erroneous notification
			$this->saveNotify('0', ucwords($this->config['channel']['name']), "e4j.error.Channels.BookingModification\n".$errmsg);

			// VCM 1.6.8 - ReservationsLogger should run even when the SynchVikBooking Class is not called
			VikChannelManager::getResLoggerInstance()
				->typeModification(true)
				->typeFromChannels(array($this->config['channel']['uniquekey']))
				->trackLog($order);

			// booking was processed correctly
			return true;
		}

		// a processing error occurred
		return false;
	}
	
	/**
	 * Cancels an OTA booking from VikBooking
	 * 
	 * @param 	array 	$order
	 * 
	 * @return 	boolean
	 */
	public function cancelBooking($order)
	{
		$dbo = JFactory::getDbo();
		if ($vbo_order_info = self::otaBookingExists($order['info']['idorderota'], true)) {
			$notifymess = "OTA Booking ID: ".$order['info']['idorderota']."\n";
			if (!empty($order['info']['checkin']) && !empty($order['info']['checkout'])) {
				$notifymess .= "Check-in: ".$order['info']['checkin']."\n";
				$notifymess .= "Check-out: ".$order['info']['checkout']."\n";
			}

			$q = "SELECT * FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$vbo_order_info['id'].";";
			$dbo->setQuery($q);
			$ordbusy = $dbo->loadAssocList();
			foreach ($ordbusy as $ob) {
				$q = "DELETE FROM `#__vikbooking_busy` WHERE `id`=" . (int)$ob['idbusy'] . ";";
				$dbo->setQuery($q);
				$dbo->execute();
			}

			// load room details
			$q = "SELECT `or`.*, `r`.`name` AS `room_name` FROM `#__vikbooking_ordersrooms` AS `or` LEFT JOIN `#__vikbooking_rooms` `r` ON `or`.`idroom`=`r`.`id` WHERE `or`.`idorder`=".(int)$vbo_order_info['id'].";";
			$dbo->setQuery($q);
			$orderrooms = $dbo->loadAssocList();
			foreach ($orderrooms as $or) {
				$notifymess .= "Room: ".$or['room_name']." - Adults: ".$or['adults'].($or['children'] > 0 ? " - Children: ".$or['children'] : "")."\n";
			}

			$notifymess .= $vbo_order_info['custdata']."\n";
			$notifymess .= $vbo_order_info['custmail'];

			$q = "DELETE FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$vbo_order_info['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();

			$q = "UPDATE `#__vikbooking_orders` SET `status`='cancelled' WHERE `id`=".(int)$vbo_order_info['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();

			// Booking History
			if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
				// access the history object
				$history_obj = VikBooking::getBookingHistoryInstance()->setBid($vbo_order_info['id']);

				/**
				 * Some channels may pass the cancellation reason that we use it as a description of the event.
				 * 
				 * @since 	1.6.22
				 */
				$canc_reason = isset($order['info']['canc_reason']) ? $order['info']['canc_reason'] : '';

				if (method_exists($history_obj, 'setBookingData')) {
					/**
					 * Reduce the number of queries in VikBooking for the booking history and related tools.
					 * 
					 * @since 		1.9.10
					 * @requires 	VBO >= 1.7.9
					 */
					$history_obj->setBookingData($vbo_order_info, $orderrooms);
				}

				$history_obj->store('CC', $canc_reason);
			}

			/**
			 * Even the notifications of type booking cancellation should store the original VBO ID
			 * that was cancelled, to permit the search functions to find also such notifications.
			 * 
			 * @since 	1.8.1 	the fourth argument is being passed to the method.
			 */
			$this->saveNotify('1', ucwords($this->config['channel']['name']), "e4j.OK.Channels.BookingCancelled\n".$notifymess, $vbo_order_info['id']);

			// add values to be returned as serialized to e4jConnect as response
			if (!isset($this->arrconfirmnumbers[$order['info']['idorderota']])) {
				$this->arrconfirmnumbers[$order['info']['idorderota']] = [];
			}
			$this->arrconfirmnumbers[$order['info']['idorderota']]['ordertype'] = 'Cancel';
			$this->arrconfirmnumbers[$order['info']['idorderota']]['confirmnumber'] = $vbo_order_info['confirmnumber'].'canc';
			$this->arrconfirmnumbers[$order['info']['idorderota']]['vborderid'] = $vbo_order_info['id'];
			$this->arrconfirmnumbers[$order['info']['idorderota']]['nkey'] = $this->generateNKey($vbo_order_info['id']);
			//

			/**
			 * Even if this was a CancelRequest reservation for a pending booking, we can still trigger the sync of the availability.
			 * 
			 * @since 	1.8.0
			 */

			// notify av=1-channels for the booking cancellation
			$vcm = new SynchVikBooking($vbo_order_info['id'], array($this->config['channel']['uniquekey']));
			$vcm->setFromCancellation($vbo_order_info);
			$vcm->sendRequest();
			//

			// SMS
			VikBooking::sendBookingSMS($vbo_order_info['id']);
			//

			return true;
		} else {
			// the booking to cancel does not exist in VikBooking or was cancelled before, notify VCM administrator
			// do not notify admin if the status is already cancelled. This can happen in case of double booking cancel transmissions by e4jConnect
			$q = "SELECT * FROM `#__vikbooking_orders` WHERE `status`='cancelled' AND `idorderota`=" . $dbo->quote($order['info']['idorderota']) . " AND `channel` LIKE " . $dbo->quote($this->config['channel']['name'] . '%');
			$dbo->setQuery($q, 0, 1);
			$current_canc_booking = $dbo->loadAssoc();
			if ($current_canc_booking) {
				// attach rooms booked details
				$current_canc_booking['rooms_info'] = self::loadBookingRoomsData($current_canc_booking['id']);

				/**
				 * Rather than notifying the admin, we should make sure a record in
				 * the booking history section is present, so that we can later keep
				 * track of this OTA cancellation, which could have come after a manual
				 * cancellation made by the admin. These scenarios may cause later cases
				 * of overbooking, due to auto-replenishments by some channels.
				 * 
				 * @since 	1.8.3
				 */
				try {
					// whether this cancellation has triggered an availability sync
					$has_synced = false;
					// get history object
					$history_obj = VikBooking::getBookingHistoryInstance()->setBid($current_canc_booking['id']);
					if ($history_obj->hasEvent('CC') === false) {
						// store history record because the channel never cancelled this booking before
						$history_obj->store('CC');
						if (stripos($this->config['channel']['name'], 'booking') !== false) {
							// try to prevent a possible (future) overbooking by triggering a synchronization of the availability
							$vcm = new SynchVikBooking($current_canc_booking['id'], array($this->config['channel']['uniquekey']));
							$vcm->setFromCancellation($current_canc_booking);
							$vcm->sendRequest();
							// turn flag on
							$has_synced = true;
						}
					}
					if (!$has_synced) {
						/**
						 * Call the reservations logger no matter what, as this event
						 * must be tracked for logging purposes. If this cancellation
						 * had triggered a sync, then the class SynchVikBooking would
						 * have invoked the class VcmReservationsLogger.
						 */
						VikChannelManager::getResLoggerInstance()
							->typeCancellation(true)
							->typeFromChannels(array($this->config['channel']['uniquekey']))
							->trackLog($order);
					}
				} catch (Exception $e) {
					// do nothing
				}

				// return false and do not notify the admin as the booking is already cancelled
				return false;
			}
			
			// prepare message for the VCM administrator
			$all_ota_room_ids = array();
			if (isset($order['roominfo'])) {
				if (array_key_exists(0, $order['roominfo'])) {
					foreach ($order['roominfo'] as $rinfo) {
						$all_ota_room_ids[] = $rinfo['idroomota'];
					}
				} else {
					$all_ota_room_ids[] = $order['roominfo']['idroomota'];
				}
			}
			$message = JText::sprintf('VCMOTACANCORDERNOTFOUND', ucwords($this->config['channel']['name']), $order['info']['idorderota'], implode(', ', $all_ota_room_ids));
			$vik = new VikApplication(VersionListener::getID());
			$admail = $this->config['emailadmin'];
			$adsendermail = VikChannelManager::getSenderMail();
			$vik->sendMail(
				$adsendermail,
				$adsendermail,
				$admail,
				$admail,
				JText::_('VCMOTACANCORDERNOTFOUNDSUBJ'),
				$message,
				false
			);
			
			// VCM 1.6.8 - ReservationsLogger should run even when the SynchVikBooking Class is not called
			VikChannelManager::getResLoggerInstance()
				->typeCancellation(true)
				->typeFromChannels(array($this->config['channel']['uniquekey']))
				->trackLog($order);
		}
		
		return false;
	}
	
	/**
	 * Checks whether the downloaded booking was already processed.
	 * This function is used for parsing bookings that were originally in ICS format
	 * so a lot of them may have been downloaded already.
	 * 
	 * @param 	array 	$order 	the current booking information array
	 * 
	 * @return 	mixed 			false on error, storing result otherwise.
	 */
	public function downloadedBooking($order)
	{
		/**
		 * ICS bookings for rooms with multiple units.
		 * Check if the customer information is identical because the 'idorderota'
		 * may be a random value in AirBnB or similar channels for the same booking.
		 * 
		 * @since 	1.6.3
		 */
		$customer_data = '';
		foreach ($order['customerinfo'] as $what => $cinfo) {
			if ($what == 'pic') {
				// the customer profile picture will be saved onto the database
				continue;
			}
			$customer_data .= ucwords($what).": ".$cinfo."\n";
		}
		$customer_data = rtrim($customer_data, "\n");
		//

		if ($vbo_order_info = self::otaBookingExists($order['info']['idorderota'], true, true, $customer_data)) {
			/**
			 * We do not allow certain channels to modify iCal reservations, where Vik Booking is the "Master".
			 * 
			 * @since 	1.7.5
			 */
			$ical_deny_mod_list = array(VikChannelManagerConfig::CAMPSITESCOUK);
			foreach ($ical_deny_mod_list as $deny_ukey) {
				$ical_cur_info = VikChannelManager::getChannel($deny_ukey);
				if (!$ical_cur_info || !count($ical_cur_info)) {
					continue;
				}
				if (stripos($ical_cur_info['name'], $order['info']['source']) !== false) {
					// no booking modification allowed for this iCal channel
					return false;
				}
			}
			//
			
			// booking previously downloaded, yet allowed to come in: check if the dates have changed
			if ($vbo_order_info['status'] != 'cancelled' && (date('Y-m-d', $vbo_order_info['checkin']) != $order['info']['checkin'] || date('Y-m-d', $vbo_order_info['checkout']) != $order['info']['checkout'])) {
				// perform the booking modification
				return $this->modifyBooking($order);
			}
		} else {
			// the booking was never downloaded, save it onto VikBooking
			return $this->saveBooking($order);
		}
		
		return false;
	}

	/**
	 * Updates the internal iCal signature map for cancellations.
	 * The method may be called externally, so it has to be public.
	 * 
	 * @param 	array 	$map 	the ical signature map value.
	 * 
	 * @return 	mixed 	the new ical signature map property.
	 * 
	 * @since 	1.8.9
	 */
	public function setiCalSignatureMap($map = null)
	{
		if ($map) {
			$this->ical_signature_map = $map;
		}

		return $this->ical_signature_map;
	}

	/**
	 * Stores the newly downloaded iCal bookings (if any) and checks
	 * if some were cancelled, because no longer present in the list.
	 * The apposite configuration setting must be enabled. It is safe
	 * to call this method even for API channels, and nothing will happen.
	 * 
	 * @see 	the visibility of this method must be public as the main
	 * 			site controller may call it for a ping with no iCal bookings.
	 * 
	 * @return 	int
	 * 
	 * @since 	1.8.9
	 */
	public function iCalCheckNewCancellations()
	{
		if (!$this->iCalCancellationsAllowed()) {
			// iCal booking cancellations is disabled through the Configuration settings
			return 0;
		}

		$dbo = JFactory::getDbo();

		if (is_array($this->ical_signature_map)) {
			// store new iCal bookings just downloaded
			foreach ($this->ical_signature_map as $vbo_bid => $bid_data) {
				// build record object
				$record = new stdClass;
				$record->bid = (int)$vbo_bid;
				$record->rid = (int)$bid_data['room_id'];
				$record->uniquekey = (string)$bid_data['channel_id'];
				$record->ota_bid   = (string)$bid_data['ota_bid'];
				$record->signature = (string)$bid_data['signature'];

				try {
					$dbo->insertObject('#__vikchannelmanager_ical_bookings', $record, 'id');
				} catch (Exception $e) {
					// do nothing
				}
			}
		}

		// check calendars from the delivered bookings
		$cal_active_bookings = [];
		foreach ($this->arrbookings['orders'] as $order) {
			if (!is_array($order) || empty($order['info']) || empty($order['info']['ical_sign'])) {
				continue;
			}

			// the calendar "signature" always includes the room ID, and also the calendar ID for some channels
			$cal_identifier = $order['info']['ical_sign'];

			if (!isset($cal_active_bookings[$cal_identifier])) {
				// whenever we have a calendar/booking signature, start the container
				$cal_active_bookings[$cal_identifier] = [];
			}

			if (!empty($order['info']['idorderota'])) {
				// push the active booking re-transmitted for this calendar identifier
				$cal_active_bookings[$cal_identifier][] = $order['info']['idorderota'];
			}
		}

		if (!count($cal_active_bookings)) {
			// nothing to cancel or to compare against, unable to proceed
			return 0;
		}

		$bookings_cancelled = [];

		// get all iCal bookings previously stored from each calendar identifier (signature)
		foreach ($cal_active_bookings as $cal_identifier => $ota_bids) {
			// query the database to find all iCal bookings previously downloaded from this room-calendar
			$q = "SELECT `ib`.*, `o`.`status` FROM `#__vikchannelmanager_ical_bookings` AS `ib` 
				LEFT JOIN `#__vikbooking_orders` AS `o` ON `ib`.`bid`=`o`.`id` 
				WHERE `ib`.`uniquekey`=" . $dbo->quote($this->config['channel']['uniquekey']) . " 
				AND `ib`.`signature`=" . $dbo->quote($cal_identifier) . " 
				AND `o`.`status`='confirmed' 
				AND `o`.`checkout`>=" . time() . ";";
			$dbo->setQuery($q);
			$prev_cal_bookings = $dbo->loadAssocList();
			if (!$prev_cal_bookings) {
				// no iCal bookings ever downloaded from this calendar
				continue;
			}

			// parse all iCal bookings previously downloaded from this calendar
			foreach ($prev_cal_bookings as $prev_booking) {
				if (in_array($prev_booking['ota_bid'], $ota_bids)) {
					// this booking is still in the calendar
					continue;
				}
				if ($this->deleteMissingiCalBooking($prev_booking)) {
					// cancellation was successful
					$bookings_cancelled[] = $prev_booking;
				}
			}
		}

		return count($bookings_cancelled);
	}

	/**
	 * Cancels a specific reservation that was previously downloaded via iCal.
	 * Triggers all the necessary update requests for all the other channels, if any.
	 * 
	 * @param 	array 	$booking 	the iCal booking record to cancel.
	 * 
	 * @return 	bool 				true on success, false otherwise.
	 * 
	 * @since 	1.8.9
	 */
	protected function deleteMissingiCalBooking($booking)
	{
		if (!is_array($booking) || empty($booking['bid'])) {
			return false;
		}

		$dbo = JFactory::getDbo();

		$notifymess = '';
		$notifymess .= "OTA Booking ID: " . $booking['ota_bid'] . "\n";
		$notifymess .= "iCal signature: " . $booking['signature'] . "\n";
		$notifymess .= "Reservation no longer present in iCal calendar for: " . $this->config['channel']['name'] . "\n";

		$q = "SELECT * FROM `#__vikbooking_ordersbusy` WHERE `idorder`=" . (int)$booking['bid'] . ";";
		$dbo->setQuery($q);
		$ordbusy = $dbo->loadAssocList();
		if (!$ordbusy) {
			return false;
		}

		// free room up
		foreach ($ordbusy as $ob) {
			$q = "DELETE FROM `#__vikbooking_busy` WHERE `id`=" . (int)$ob['idbusy'] . ";";
			$dbo->setQuery($q);
			$dbo->execute();
		}

		// delete occupied record relations and update booking status
		$q = "DELETE FROM `#__vikbooking_ordersbusy` WHERE `idorder`=" . (int)$booking['bid'] . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_orders` SET `status`='cancelled' WHERE `id`=" . (int)$booking['bid'] . ";";
		$dbo->setQuery($q);
		$dbo->execute();

		// booking history
		if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
			VikBooking::getBookingHistoryInstance()->setBid($booking['bid'])->store('CC', 'iCal ' . $booking['signature']);
		}

		/**
		 * Even the notifications of type booking cancellation should store the original VBO ID
		 * that was cancelled, to permit the search functions to find also such notifications.
		 */
		$this->saveNotify('1', ucwords($this->config['channel']['name']), "e4j.OK.Channels.BookingCancelled\n" . $notifymess, $booking['bid']);

		// notify av=1-channels for the booking cancellation
		$booking['id'] = $booking['bid'];
		$vcm = new SynchVikBooking($booking['bid'], array($this->config['channel']['uniquekey']));
		$vcm->setFromCancellation($booking);
		$vcm->sendRequest();

		return true;
	}

	/**
	 * Tells whether the iCal cancellations are enabled.
	 * 
	 * @return 	bool 	false by default.
	 * 
	 * @since 	1.8.9
	 */
	protected function iCalCancellationsAllowed()
	{
		$dbo = JFactory::getDbo();

		$dbo->setQuery("SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='ical_cancellations'", 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows()) {
			// store the record, but return false as default setting
			$dbo->setQuery("INSERT INTO `#__vikchannelmanager_config` (`param`, `setting`) VALUES ('ical_cancellations', '0');");
			$dbo->execute();
			return false;
		}

		$config_val = (int)$dbo->loadResult();

		return (bool)($config_val > 0);
	}

	/**
	 * Loads a list of records for the rooms booked and assigned to this
	 * booking. This is to have a unique method to obtain the necessary
	 * records and values.
	 * 
	 * @param 	int 	$bid 	the ID of the VBO reservation record.
	 * 
	 * @return 	array 	list of associative array records or empty array.
	 * 
	 * @since 	1.8.3
	 * 
	 * @see 	this is a static method because it can be accessed also
	 * 			from a static context by otaBookingExists().
	 */
	protected static function loadBookingRoomsData($bid)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('or') . '.*')
			->select($dbo->qn('r.name', 'roomnamevb'))
			->select($dbo->qn('r.units'))
			->from($dbo->qn('#__vikbooking_ordersrooms', 'or'))
			->leftJoin($dbo->qn('#__vikbooking_rooms', 'r') . ' ON ' . $dbo->qn('or.idroom') . ' = ' . $dbo->qn('r.id'))
			->where($dbo->qn('or.idorder') . ' = ' . (int) $bid)
			->order($dbo->qn('or.id') . ' ASC');

		$dbo->setQuery($q);

		return $dbo->loadAssocList();
	}

	/**
	 * Decodes the credit card details and returns an array with
	 * PCI-compliant values that can be stored in the database
	 * 
	 * @param 	array	$order
	 * 
	 * @return 	array
	 */
	private function processCreditCardDetails($order)
	{
		$credit_card = array();
		if (!empty($order['info']['credit_card'])) {
			$decoded_card = $this->cypher->decrypt($order['info']['credit_card']);
			$decoded_card = @unserialize($decoded_card);

			/**
			 * VCM 1.6.9 - attempt to urldecode the encrypted data
			 */
			if ($decoded_card === false) {
				$decoded_card = $this->cypher->decrypt(urldecode($order['info']['credit_card']));
				$decoded_card = @unserialize($decoded_card);
			}
			//

			if ($decoded_card !== false && is_array($decoded_card)) {
				if (strpos($decoded_card['card_number'], '*') === false) {
					//Mask credit card if not masked already
					$cc = str_replace(' ', '', trim($decoded_card['card_number']));
					$cc_num_len = strlen($cc);
					$cc_hidden = '';
					$cc_pci = '';
					if ($cc_num_len == 14) {
						// Diners Club
						$cc_hidden .= substr($cc, 0, 4)." **** **** **";
						$app = "****".substr($cc, 4, 10);
						for ($i = 1; $i <= $cc_num_len; $i++) {
							$cc_pci .= $app[$i-1].($i%4 == 0 ? ' ':'');
						}
					} elseif ($cc_num_len == 15) {
						// American Express
						$cc_hidden .= "**** ****** ".substr($cc, 10, 5);
						$app = substr($cc, 0, 10)."*****";
						for ($i = 1; $i <= $cc_num_len; $i++) {
							$cc_pci .= $app[$i-1].($i==4 || $i==10 ? ' ':'');
						}
					} else {
						// Master Card, Visa, Discover, JCB
						$cc_hidden .= "**** **** **** ".substr($cc, 12, 4);
						$app = substr($cc, 0, 12)."****";
						for ($i = 1; $i <= $cc_num_len; $i++) {
							$cc_pci .= $app[$i-1].($i%4 == 0 ? ' ':'');
						}
					}
					$decoded_card['card_number'] = $cc_hidden;
					$decoded_card['card_number_pci'] = $cc_pci;
					//
				}
				$credit_card = $decoded_card;
			}
		}
		
		return $credit_card;
	}
	
	/**
	 * Sends via email to the administrator email address
	 * the PCI-compliant and remaining number of the 
	 * credit card returned by the channel
	 * 
	 * @param 	array	$order
	 */
	private function sendCreditCardDetails($order)
	{
		if (!array_key_exists('card_number_pci', $order['info']['credit_card'])) {
			return false;
		}
		$vik = new VikApplication(VersionListener::getID());
		$admail = $this->config['emailadmin'];
		$adsendermail = VikChannelManager::getSenderMail();
		$vik->sendMail(
			$adsendermail,
			$adsendermail,
			$admail,
			$admail,
			JText::_('VCMCHANNELNEWORDERMAILSUBJECT'),
			JText::sprintf('VCMCHANNELNEWORDERMAILCONTENT', $order['info']['idorderota'], ucwords($this->config['channel']['name']), $order['info']['credit_card']['card_number_pci'], (isset($order['order_link']) ? $order['order_link'] : '')),
			false
		);
		return true;
	}
	
	/**
	 * Saves the new order from the OTA in the DB tables of VikBooking.
	 * 
	 * @param 	array 	$order
	 * @param 	mixed	$idroomvb
	 * @param 	int		$checkints
	 * @param 	int		$checkoutts
	 * @param 	int		$numnights
	 * @param 	int		$adults
	 * @param 	int		$children
	 * @param 	int 	$pets
	 * @param 	float	$total
	 * @param 	string	$customerinfo
	 * @param 	string	$purchaseremail
	 * 
	 * @return 	array
	 */
	public function saveNewVikBookingOrder($order, $idroomvb, $checkints, $checkoutts, $numnights, $adults, $children, $pets, $total, $customerinfo, $purchaseremail)
	{
		$dbo = JFactory::getDbo();

		// default number of adults
		if ((int)$adults == 0 && (int)$children == 0 && !is_array($idroomvb)) {
			$adults = 0;
			$q = "SELECT `fromadult` FROM `#__vikbooking_rooms` WHERE `id`=".(int)$idroomvb.";";
			$dbo->setQuery($q);
			$num_adults = $dbo->loadResult();
			if ($num_adults) {
				$adults = (int)$num_adults;
			}
		}

		$orderinfo = $order['info'];
		$tot_taxes = 0;
		if (!empty($orderinfo['tax']) && floatval($orderinfo['tax']) > 0) {
			$tot_taxes = floatval($orderinfo['tax']);
		}

		/**
		 * Total city taxes can be collected from booking information.
		 * 
		 * @since 	1.8.0
		 */
		$tot_city_taxes = 0;
		if (!empty($orderinfo['city_tax']) && floatval($orderinfo['city_tax']) > 0) {
			$tot_city_taxes = floatval($orderinfo['city_tax']);
		}

		/**
		 * Total fees can be collected from booking information.
		 * 
		 * @since 	1.9.13
		 */
		$tot_fees = 0;
		if (!empty($orderinfo['fees']) && floatval($orderinfo['fees']) > 0) {
			$tot_fees = floatval($orderinfo['fees']);
		}
		
		// compose payment log
		$payment_log = '';
		if (count($order['info']['credit_card']) > 0) {
			$payment_log .= "Credit Card Details:\n";
			foreach ($order['info']['credit_card'] as $card_info => $card_data) {
				if ($card_info == 'card_number_pci') {
					//do not touch this part or you will lose any PCI-compliance function
					continue;
				}
				if (is_array($card_data)) {
					$payment_log .= ucwords(str_replace('_', ' ', $card_info)).":\n";
					foreach ($card_data as $card_info_in => $card_data_in) {
						$payment_log .= ucwords(str_replace('_', ' ', $card_info_in)).": ".$card_data_in."\n";
					}
				} else {
					$payment_log .= ucwords(str_replace('_', ' ', $card_info)).": ".$card_data."\n";
				}
			}
			$payment_log = rtrim($payment_log, "\n");
		}
		
		// always set $idroomvb to an array even if it is just a string
		$orig_idroomvb = $idroomvb;
		unset($idroomvb);
		if (is_array($orig_idroomvb)) {
			$idroomvb = array_values($orig_idroomvb);
		} else {
			$idroomvb = array($orig_idroomvb);
		}

		// Phone Number and Customers Management (VikBooking 1.6 or higher, check if cpin.php exists - since v1.6)
		$phone = '';
		if (!empty($order['customerinfo']['telephone'])) {
			$phone = $order['customerinfo']['telephone'];
		} elseif (!empty($order['customerinfo']['phone'])) {
			$phone = $order['customerinfo']['phone'];
		}

		// country
		$country = '';
		if (isset($order['customerinfo']) && !empty($order['customerinfo']['country'])) {
			if (strlen($order['customerinfo']['country']) == 3) {
				$country = $order['customerinfo']['country'];
			} elseif (strlen($order['customerinfo']['country']) == 2) {
				$q = "SELECT `country_3_code` FROM `#__vikbooking_countries` WHERE `country_2_code`=".$dbo->quote($order['customerinfo']['country']).";";
				$dbo->setQuery($q);
				$country = $dbo->loadResult();
			} elseif (strlen($order['customerinfo']['country']) > 3) {
				$q = "SELECT `country_3_code` FROM `#__vikbooking_countries` WHERE `country_name` LIKE ".$dbo->quote('%'.$order['customerinfo']['country'].'%').";";
				$dbo->setQuery($q);
				$country = $dbo->loadResult();
			}
		}

		/**
		 * We need to format the phone number by prepending the country prefix if this is missing.
		 * 
		 * @since 	1.6.18
		 */
		if (!empty($phone) && !empty($country)) {
			// do not trim completely as the plus symbol may be a leading white-space
			$phone = rtrim($phone);

			if (substr($phone, 0, 1) == ' ' && strlen($phone) > 5) {
				/**
				 * Phone numbers inclusive of prefix with the plus symbol may be delivered by e4jConnect as a leading white space.
				 * The plus symbol gets printed as a white-space, and so this is what VCM gets. We should only right-trim until now.
				 * In these cases we apply the left trim to complete the trimming, then we prepend the plus symbol so that the phone
				 * number returned by the OTAs won't be touched as it's probably complete and inclusive of country prefix.
				 * 
				 * @since 	1.7.2
				 */
				$phone = ltrim($phone);
				$phone = '+' . $phone;
			}

			if (substr($phone, 0, 1) != '+' && substr($phone, 0, 2) != '00') {
				// try to find the country phone prefix since it's missing in the number
				$q = "SELECT `phone_prefix` FROM `#__vikbooking_countries` WHERE `country_" . (strlen($country) == 2 ? '2' : '3') . "_code`=" . $dbo->quote($country) . ";";
				$dbo->setQuery($q);
				$phone_prefix = $dbo->loadResult();
				if ($phone_prefix) {
					$country_prefix = str_replace(' ', '', $phone_prefix);
					$num_prefix = str_replace('+', '', $country_prefix);
					if (substr($phone, 0, strlen($num_prefix)) != $num_prefix) {
						// country prefix is completely missing
						$phone = $country_prefix . $phone;
					} else {
						// try to prepend the plus symbol because the phone number starts with the country prefix
						$phone = '+' . $phone;
					}
				}
			}
		}

		/**
		 * The status of new bookings can be pending.
		 * 
		 * @since 	1.8.0
		 */
		$new_book_status = $this->pending_booking ? 'standby' : 'confirmed';
		
		/**
		 * Customer Extra Info such as address, city, zip, company, vat are
		 * stored into an array that will be passed onto VikBookingCustomersPin.
		 * 
		 * @since 	1.6.12
		 * @since 	1.8.6 	added support for customer profile picture (avatar).
		 * @since 	1.8.12 	added support for customer state/province.
		 */
		$extra_info_keys = [
			'address',
			'city',
			'state',
			'zip',
			'company',
			'vat',
			'pic',
		];
		$customer_extra_info = [];
		foreach ($extra_info_keys as $extra_key) {
			if (isset($order['customerinfo']) && !empty($order['customerinfo'][$extra_key])) {
				$customer_extra_info[$extra_key] = $order['customerinfo'][$extra_key];
			}
		}

		// nominative
		$traveler_first_name = array_key_exists('traveler_first_name', $orderinfo) ? $orderinfo['traveler_first_name'] : '';
		$traveler_last_name = array_key_exists('traveler_last_name', $orderinfo) ? $orderinfo['traveler_last_name'] : '';

		// number of rooms
		$num_rooms = 1;
		if (array_key_exists('num_rooms', $order['info']) && intval($order['info']['num_rooms']) > 1) {
			$num_rooms = intval($order['info']['num_rooms']);
		}

		// store busy records, unless the booking is pending
		$busy_ids = array();
		for ($i = 1; $i <= $num_rooms; $i++) {
			if ($this->pending_booking) {
				// assign an empty value for the busy record which does not need to be created
				$busy_ids[$i] = 0;
			} else {
				$q = "INSERT INTO `#__vikbooking_busy` (`idroom`,`checkin`,`checkout`,`realback`) VALUES('" . $idroomvb[($i - 1)] . "', '" . $checkints . "', '" . $checkoutts . "','" . $checkoutts . "');";
				$dbo->setQuery($q);
				$dbo->execute();
				$busyid = $dbo->insertid();
				$busy_ids[$i] = $busyid;
			}
		}

		/**
		 * Default language for the reservations. Useful for cron jobs and later communications.
		 * This avoids to rely on the website's main language for back-end or front-end.
		 * 
		 * @since 	1.6.8
		 */
		$default_lang = VikChannelManager::getDefaultLanguage();

		/**
		 * We now accept the language to assign to the booking (if available), or we use a
		 * technique to determine the best available language to use according to the country.
		 * 
		 * @since 	1.8.3
		 */
		$guest_locale  = isset($order['customerinfo']) && !empty($order['customerinfo']['locale']) ? $order['customerinfo']['locale'] : null;
		$best_language = VikChannelManager::guessBookingLangFromCountry($country, $guest_locale);
		if (!empty($best_language)) {
			// override the default language to assign to the booking
			$default_lang = $best_language;
		}

		/**
		 * We check if a default payment option should be assigned in order to allow
		 * upselling of extras or Virtual Terminal charges for OTA reservations.
		 * 
		 * @since 	1.8.18
		 * @since 	1.9.18 improved detection with support to rooms filtering.
		 */
		$def_pay_id  = VCMFactory::getConfig()->get('defaultpayment', '');
		$def_pay_str = null;
		if ($def_pay_id) {
			$q = $dbo->getQuery(true)
				->select($dbo->qn(['id', 'name', 'file', 'idrooms']))
				->from($dbo->qn('#__vikbooking_gpayments'))
				->where($dbo->qn('id') . ' = ' . (int)$def_pay_id);
			$dbo->setQuery($q, 0, 1);
			$def_pay_data = $dbo->loadAssoc();
			if ($def_pay_data) {
				// assign default payment method to reservation record
				$def_pay_str = $def_pay_data['id'] . '=' . $def_pay_data['name'];
				// check for rooms filtering
				if (!empty($def_pay_data['idrooms']) && ($idroomvb[0] ?? 0)) {
					$pay_filter_rooms = (array) json_decode($def_pay_data['idrooms'], true);
					if ($pay_filter_rooms && !in_array($idroomvb[0], $pay_filter_rooms)) {
						// try to look for a better payment method
						$dbo->setQuery(
							$dbo->getQuery(true)
								->select($dbo->qn(['id', 'name', 'idrooms']))
								->from($dbo->qn('#__vikbooking_gpayments'))
								->where($dbo->qn('file') . ' = ' . $dbo->q($def_pay_data['file']))
								->where($dbo->qn('id') . ' != ' . (int) $def_pay_data['id'])
								->where($dbo->qn('published') . ' = 1')
								->order($dbo->qn('idrooms') . ' DESC')
						);
						$other_payments = $dbo->loadAssocList();
						foreach ($other_payments as $other_payment) {
							$pay_filter_rooms = $other_payment['idrooms'] ? (array) json_decode($other_payment['idrooms'], true) : [];
							if ($pay_filter_rooms && !in_array($idroomvb[0], $pay_filter_rooms)) {
								// not compatible
								continue;
							}
							// eligible payment method found for the first booked listing
							$def_pay_str = $other_payment['id'] . '=' . $other_payment['name'];
							// do not proceed
							break;
						}
					}
				}
				if (strlen($def_pay_str) > 128) {
					$def_pay_str = substr($def_pay_str, 0, 128);
				}
			}
		}

		// build reservation record
		$res_record = new stdClass;
		$res_record->custdata = $customerinfo;
		$res_record->ts = time();
		$res_record->status = $new_book_status;
		$res_record->days = $numnights;
		$res_record->checkin = $checkints;
		$res_record->checkout = $checkoutts;
		$res_record->custmail = $purchaseremail;
		$res_record->sid = '';
		$res_record->totpaid = (isset($orderinfo['total_paid']) ? (float)$orderinfo['total_paid'] : 0);
		$res_record->idpayment = $def_pay_str;
		$res_record->ujid = 0;
		$res_record->coupon = '';
		$res_record->roomsnum = $num_rooms;
		$res_record->total = $total;
		$res_record->idorderota = strlen($orderinfo['idorderota']) > 128 ? substr($orderinfo['idorderota'], 0, 128) : $orderinfo['idorderota'];
		$res_record->channel = $this->config['channel']['name'] . '_' . $orderinfo['source'];
		$res_record->chcurrency = (!empty($orderinfo['currency']) ? $orderinfo['currency'] : null);
		$res_record->paymentlog = $payment_log;
		$res_record->lang = (!empty($default_lang) ? $default_lang : null);
		$res_record->country = (!empty($country) ? $country : null);
		$res_record->tot_taxes = $tot_taxes;
		$res_record->tot_city_taxes = $tot_city_taxes;
		$res_record->tot_fees = $tot_fees;
		if (!empty($phone)) {
			$res_record->phone = $phone;
		}
		if ($this->setCommissions()) {
			$res_record->cmms = isset($order['info']['commission_amount']) ? $order['info']['commission_amount'] : null;
		}

		/**
		 * The booking_type is only stored for certain bookings, but the thread_id should always
		 * be stored if available, so that conversations through Messaging can be easily managed.
		 * 
		 * @since 	1.8.20
		 * @since 	1.9.12 added support to "pay_type" to detect VCC payments like "PBB" (Payments by Booking.com).
		 * @since 	1.9.14 added support to "expected_payout" to store the expected OTA payment.
		 */
		$ota_type_data = null;
		if ($this->isBookingTypeSupported()) {
			if (!empty($this->booking_type)) {
				// set the booking type
				$res_record->type = $this->booking_type;
			}

			if (!empty($orderinfo['thread_id'])) {
				// keep the variable $ota_type_data an array as it will be used later to append the rate plan IDs
				$ota_type_data = array_merge($ota_type_data ?? [], ['thread_id' => $orderinfo['thread_id']]);
			}

			if (!empty($orderinfo['pay_type'])) {
				// keep the variable $ota_type_data an array as it will be used later to append the rate plan IDs
				$ota_type_data = array_merge($ota_type_data ?? [], ['pay_type' => $orderinfo['pay_type']]);
			}

			if (!empty($orderinfo['expected_payout'])) {
				// keep the variable $ota_type_data an array as it will be used later to append the rate plan IDs
				$ota_type_data = array_merge($ota_type_data ?? [], ['expected_payout' => $orderinfo['expected_payout']]);
			}

			if ($ota_type_data) {
				// set the booking OTA type data
				$res_record->ota_type_data = json_encode($ota_type_data);
			}
		}

		/**
		 * Fallback to avoid queries to fail because of unsupported encoding of $customerinfo.
		 * It has happened that some bookings could not be saved because of Emoji characters causing
		 * an SQL error 1366 (Incorrect string value \xF0\x9F\x99\x82.\xE2). The database character set
		 * and collate should be set to utf8mb4 in order to support special characters such as Emoji.
		 * 
		 * @since 	1.6.13
		 * @since 	1.8.0 	we build an object-query rather than a string-query.
		 */
		$neworderid = 0;
		try {
			if (!$dbo->insertObject('#__vikbooking_orders', $res_record, 'id')) {
				$neworderid = 0;
			} else {
				$neworderid = $res_record->id;
			}
		} catch (Exception $e) {
			// make sure the process is not broken by a possible uncaught exception
			$neworderid = 0;
		}
		if (empty($neworderid)) {
			// we try to store the booking with no customer information, as for sure that's the value that caused the error
			$res_record->custdata = '...';
			if (!$dbo->insertObject('#__vikbooking_orders', $res_record, 'id')) {
				$neworderid = 0;
			} else {
				$neworderid = $res_record->id;
			}
		}

		// in case of pending reservation (excluding inquiries), room(s) should be set as temporarily locked
		if ($this->pending_booking && $this->booking_type != 'inquiry') {
			for ($i = 1; $i <= $num_rooms; $i++) {
				// build record
				$tmp_lock_record = [
					'idroom'   => (int) $idroomvb[($i - 1)],
					'checkin'  => $checkints,
					'checkout' => $checkoutts,
					'until'    => VikBooking::getMinutesLock(true),
					'realback' => $checkoutts,
					'idorder'  => (int) $neworderid,
				];

				// cast to object before inserting the object, as it's expected by reference
				$tmp_lock_record = (object) $tmp_lock_record;

				// store record
				$dbo->insertObject('#__vikbooking_tmplock', $tmp_lock_record, 'id');
			}
		}

		/**
		 * Notify the administrator with the credit credit card details for PCI-compliance.
		 * The back-end link for the payments log differs between platforms
		 */
		if (VCMPlatformDetection::isWordPress()) {
			$order['order_link'] = admin_url('admin.php?option=com_vikbooking&task=editorder&cid[]=' . $neworderid . '#paymentlog');
		} else {
			$order['order_link'] = JUri::root() . 'administrator/index.php?option=com_vikbooking&task=editorder&cid[]=' . $neworderid . '#paymentlog';
		}
		$this->sendCreditCardDetails($order);

		$confirmnumber = !$this->pending_booking ? $this->generateConfirmNumber($neworderid, true) : $neworderid;
		$rooms_aduchild = array();
		// Adults and Children are returned as total by the OTA. If multiple rooms, dispose the Adults and Children accordingly
		if ($num_rooms > 1) {
			$adults_per_room = floor($adults / $num_rooms);
			$adults_per_room = $adults_per_room < 0 ? 0 : $adults_per_room;
			$spare_adults = ($adults - ($adults_per_room * $num_rooms));
			$children_per_room = floor($children / $num_rooms);
			$children_per_room = $children_per_room < 0 ? 0 : $children_per_room;
			$spare_children = ($children - ($children_per_room * $num_rooms));
			for ($i = 1; $i <= $num_rooms; $i++) {
				$adults_occupancy = $adults_per_room;
				$children_occupancy = $children_per_room;
				if ($i == 1 && ($spare_adults > 0 || $spare_children > 0)) {
					$adults_occupancy += $spare_adults;
					$children_occupancy += $spare_children;
				}
				$rooms_aduchild[$i]['adults'] = $adults_occupancy;
				$rooms_aduchild[$i]['children'] = $children_occupancy;
				if ($i === 1) {
					$rooms_aduchild[$i]['pets'] = $pets;
				}
			}
		} else {
			$rooms_aduchild[$num_rooms]['adults'] = $adults;
			$rooms_aduchild[$num_rooms]['children'] = $children;
			$rooms_aduchild[$num_rooms]['pets'] = $pets;
		}

		// default Tax Rate ID
		$default_tax_rate = $this->getDefaultTaxID();

		// assign room specific unit
		$set_room_indexes = (!$this->pending_booking && $this->autoRoomUnit());
		$room_indexes_usemap = [];

		// list of booking room records created
		$booking_room_records = [];

		// list of OTA rate plan IDs involved
		$ota_rplan_ids = [];

		foreach ($busy_ids as $num_room => $id_busy) {
			if (!empty($id_busy)) {
				$q = "INSERT INTO `#__vikbooking_ordersbusy` (`idorder`,`idbusy`) VALUES(".(int)$neworderid.", ".(int)$id_busy.");";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			// traveler name for each room if available
			$room_t_first_name = $traveler_first_name;
			$room_t_last_name = $traveler_last_name;
			if (array_key_exists(($num_room - 1), $order['roominfo'])) {
				if (isset($order['roominfo'][($num_room - 1)]['traveler_first_name']) && strlen($order['roominfo'][($num_room - 1)]['traveler_first_name'])) {
					$room_t_first_name = $order['roominfo'][($num_room - 1)]['traveler_first_name'];
					$room_t_last_name = $order['roominfo'][($num_room - 1)]['traveler_last_name'];
				}
			}

			// assign room specific unit
			$room_indexes = $set_room_indexes === true ? $this->getRoomUnitNumsAvailable(array('id' => $neworderid, 'checkin' => $checkints, 'checkout' => $checkoutts), $idroomvb[($num_room - 1)]) : array();
			$use_ind_key = 0;
			if (count($room_indexes)) {
				if (!array_key_exists($idroomvb[($num_room - 1)], $room_indexes_usemap)) {
					$room_indexes_usemap[$idroomvb[($num_room - 1)]] = $use_ind_key;
				} else {
					$use_ind_key = $room_indexes_usemap[$idroomvb[($num_room - 1)]];
				}
				$rooms[$num]['roomindex'] = (int)$room_indexes[$use_ind_key];
			}

			// children age
			$children_ages = array();
			if ($num_room <= 1 && array_key_exists('children_ages', $orderinfo) && is_array($orderinfo['children_ages'])) {
				$children_ages = array('age' => $orderinfo['children_ages']);
			}

			// OTA Rate Plan for this room booked
			$otarplan_supported = $this->otaRplanSupported();
			$room_otarplan = '';
			if (isset($order['roominfo'][($num_room - 1)]) && isset($order['roominfo'][($num_room - 1)]['rateplanid'])) {
				$room_otarplan = $order['roominfo'][($num_room - 1)]['rateplanid'];
			} elseif (isset($order['roominfo']['rateplanid'])) {
				$room_otarplan = $order['roominfo']['rateplanid'];
			}
			if ($room_otarplan && !in_array($room_otarplan, $ota_rplan_ids)) {
				$ota_rplan_ids[] = $room_otarplan;
			}
			list($room_otarplan, $meals_included) = $this->getOtaRplanDataFromId($room_otarplan, (int)$idroomvb[($num_room - 1)]);

			/**
			 * Determine whether the application of taxes should be forced for some channels.
			 * 
			 * @since 	1.8.4
			 */
			$force_taxes = false;

			/**
			 * Set room exact cost (if available). Useful to print
			 * the cost of this room in case of multiple rooms booked.
			 * 
			 * @since 	1.6.13
			 */
			$now_room_cost = round(($total / $num_rooms), 2);
			if (isset($order['roominfo'][($num_room - 1)]) && isset($order['roominfo'][($num_room - 1)]['room_cost']) && floatval($order['roominfo'][($num_room - 1)]['room_cost']) > 0) {
				$now_room_cost = (float)$order['roominfo'][($num_room - 1)]['room_cost'];
				/**
				 * Hosts eligible for taxes working with Airbnb and using prices inclusive of tax
				 * in VBO may need to have the listing base cost inclusive of tax, as it's returned
				 * by the e4jConnect servers before taxes, due to the missing tax rate information
				 * and by other extra services that may still be subjected to tax. The same thing
				 * works for the channel Vrbo API introduced with VCM v1.8.12.
				 * 
				 * @since 	1.8.4
				 */
				$otas_before_tax = [
					VikChannelManagerConfig::AIRBNBAPI,
					VikChannelManagerConfig::VRBOAPI,
				];
				if (VikBooking::ivaInclusa() && $tot_taxes > 0 && $total > $now_room_cost && in_array($this->config['channel']['uniquekey'], $otas_before_tax)) {
					// room exact cost should be inclusive of taxes (force taxes to be applied)
					$now_room_cost = VikBooking::sayPackagePlusIva($now_room_cost, $default_tax_rate, true);
					// turn flag on
					$force_taxes = true;
				}
			}

			/**
			 * Expedia reservations now return the exact room cost, which is always before taxes.
			 * However, for those working with prices after tax, we should not use this value.
			 * 
			 * @since 	1.8.12
			 */
			if ($this->config['channel']['uniquekey'] == VikChannelManagerConfig::EXPEDIA && VikBooking::ivaInclusa()) {
				// Expedia bookings will always contain just one room-type
				$now_room_cost = round(($total / $num_rooms), 2);
			}

			/**
			 * We try to get the exact number of adults and children from 'roominfo'
			 * because some channels may support this obvious information. Pets are
			 * also supported by recent versions of VBO.
			 * 
			 * @since 	1.6.22
			 * @since 	1.8.12 added support for pets.
			 */
			if (isset($order['roominfo'][($num_room - 1)])) {
				if (isset($order['roominfo'][($num_room - 1)]['adults'])) {
					$rooms_aduchild[$num_room]['adults'] = (int)$order['roominfo'][($num_room - 1)]['adults'];
				}
				if (isset($order['roominfo'][($num_room - 1)]['children'])) {
					$rooms_aduchild[$num_room]['children'] = (int)$order['roominfo'][($num_room - 1)]['children'];
				}
				if (isset($order['roominfo'][($num_room - 1)]['pets'])) {
					$rooms_aduchild[$num_room]['pets'] = (int)$order['roominfo'][($num_room - 1)]['pets'];
				}
			}
			
			/**
			 * Extracosts for the reservation (AddOns) like Parking or Breakfast.
			 * VCM may get this information from some OTAs.
			 * 
			 * @since 	1.7.0
			 * @since 	1.8.12 added support for the property "type" to identify the extra service.
			 */
			$extracosts = [];
			if (isset($order['roominfo'][($num_room - 1)]) && isset($order['roominfo'][($num_room - 1)]['extracosts']) && is_array($order['roominfo'][($num_room - 1)]['extracosts'])) {
				foreach ($order['roominfo'][($num_room - 1)]['extracosts'] as $ec) {
					if (!is_array($ec) || !$ec) {
						continue;
					}
					// normalize extra cost type structure
					$ec['type'] = empty($ec['type']) && !empty($ec['info']['type']) ? $ec['info']['type'] : ($ec['type'] ?? null);
					// whether taxes should be added
					$use_force_taxes = $force_taxes;
					if (!empty($ec['type']) && is_string($ec['type'])) {
						if (!strcasecmp($ec['type'], 'TOURIST_TAX') || !strcasecmp($ec['type'], 'DEPOSIT') || !strcasecmp($ec['type'], 'ENV_FEE')) {
							/**
							 * It is okay to force taxes for Airbnb/VRBO reservations, but only certain extra costs
							 * should have taxes added. For example "Pet Fees", but not Tourist Tax, Deposit or Env Fee.
							 * 
							 * @since 	1.8.16
							 */
							$use_force_taxes = false;
						}
					}
					// prepare extra-cost object
					$ecdata = new stdClass;
					$ecdata->name = $ec['name'];
					$ecdata->cost = (float)$ec['cost'];
					$ecdata->idtax = '';
					if ($use_force_taxes) {
						$ecdata->cost = VikBooking::sayOptionalsPlusIva($ecdata->cost, $default_tax_rate, true);
						$ecdata->idtax = !empty($default_tax_rate) ? $default_tax_rate : $ecdata->idtax;
					}
					if (!empty($ec['type']) && is_string($ec['type'])) {
						$ecdata->type = $ec['type'];
					}
					if (isset($ec['ota_collected']) && is_scalar($ec['ota_collected'])) {
						$ecdata->ota_collected = $ec['ota_collected'];
					}
					array_push($extracosts, $ecdata);
				}
			}

			// insert object in "ordersrooms"
			$or_data = new stdClass;
			$or_data->idorder = (int)$neworderid;
			$or_data->idroom = (int)$idroomvb[($num_room - 1)];
			$or_data->adults = (int)$rooms_aduchild[$num_room]['adults'];
			$or_data->children = (int)$rooms_aduchild[$num_room]['children'];
			if ($this->petsSupported()) {
				$or_data->pets = isset($rooms_aduchild[$num_room]['pets']) ? (int)$rooms_aduchild[$num_room]['pets'] : 0;
			}
			$or_data->childrenage = (count($children_ages) ? json_encode($children_ages) : null);
			$or_data->t_first_name = $room_t_first_name;
			$or_data->t_last_name = $room_t_last_name;
			if (count($room_indexes)) {
				$or_data->roomindex = (int)$room_indexes[$use_ind_key];
			}
			if ($this->setCommissions()) {
				$or_data->cust_cost = $now_room_cost;
				$or_data->cust_idiva = $default_tax_rate;
			}
			if (count($extracosts) && $otarplan_supported) {
				$or_data->extracosts = json_encode($extracosts);
			}
			if ($otarplan_supported) {
				$or_data->otarplan = $room_otarplan;
			}
			if ($meals_included && $this->mealPlansSupported()) {
				$or_data->meals = VBOMealplanManager::getInstance()->buildOTAMealPlans($meals_included);
			}
			$dbo->insertObject('#__vikbooking_ordersrooms', $or_data, 'id');

			// push booking room record created
			$booking_room_records[] = (array) $or_data;

			// assign room specific unit
			if ($room_indexes) {
				$room_indexes_usemap[$idroomvb[($num_room - 1)]]++;
			}
		}

		$insertdata = [
			'newvborderid'  => $neworderid,
			'confirmnumber' => $confirmnumber,
		];

		// save customer (VikBooking 1.6 or higher)
		if (!empty($traveler_first_name) && !empty($traveler_last_name) && !empty($purchaseremail)) {
			try {
				if (!class_exists('VikBookingCustomersPin')) {
					require_once(VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "cpin.php");
				}
				$cpin = new VikBookingCustomersPin;
				/**
				 * Customer Extra Info such as address, city, zip, company, vat.
				 * 
				 * @since 	1.6.12
				 * @since 	1.8.6 	added detection for VBO to support customer profile picture (avatar).
				 * @since 	1.8.12 	added detection for VBO to support customer state/province.
				 */
				if (!method_exists($cpin, 'supportsProfileAvatar') && isset($customer_extra_info['pic'])) {
					unset($customer_extra_info['pic']);
				}
				if (!method_exists($cpin, 'supportsStateProvince') && isset($customer_extra_info['state'])) {
					unset($customer_extra_info['state']);
				}
				$cpin->setCustomerExtraInfo($customer_extra_info);
				//
				$cpin->saveCustomerDetails($traveler_first_name, $traveler_last_name, $purchaseremail, $phone, $country, array());
				$cpin->saveCustomerBooking($neworderid);
			} catch (Exception $e) {
				// do nothing
			}
		}

		/**
		 * Take care of eventually shared calendars for the rooms involved.
		 * 
		 * @since 	1.7.1
		 */
		if (!$this->pending_booking) {
			$this->updateSharedCalendars($neworderid);
		}

		/**
		 * Make sure to register any OTA type data related to rate plans.
		 * 
		 * @since 	1.8.12
		 */
		if ($this->isBookingTypeSupported() && $neworderid && $ota_rplan_ids) {
			$ota_type_data = is_array($ota_type_data) ? $ota_type_data : [];
			$ota_type_data['rateplan_ids'] = $ota_rplan_ids;

			$upd_record = new stdClass;
			$upd_record->id = $neworderid;
			$upd_record->ota_type_data = json_encode($ota_type_data);

			$dbo->updateObject('#__vikbooking_orders', $upd_record, 'id');
		}

		// Booking History
		if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
			// get the history object instance
			$history_obj = VikBooking::getBookingHistoryInstance()->setBid($neworderid);

			if (method_exists($history_obj, 'setBookingData')) {
				/**
				 * Reduce the number of queries in VikBooking for the booking history and related tools.
				 * 
				 * @since 		1.9.10
				 * @requires 	VBO >= 1.7.9
				 */
				$history_obj->setBookingData((array) $res_record, $booking_room_records);
			}

			// build the event description, defaults to an empty string
			$ev_typeno_descr = $this->modification_not_found ? 'Booking modification request was processed as a new booking.' : '';

			/**
			 * Use the expected payout amount as history description if empty and if available.
			 * 
			 * @since 	1.9.10
			 */
			if (!$ev_typeno_descr && !empty($order['info']['payout_amount'])) {
				$ev_typeno_descr = sprintf('Expected payout %d', (float) $order['info']['payout_amount']);
			}

			// store the event for a new OTA reservation received
			$history_obj->store('NO', $ev_typeno_descr);

			if ($this->booking_type === 'overbooking') {
				/**
				 * Store an additional history record in case the reservation generated an overbooking scenario.
				 * We also store a reminder within VikBooking with the "important" flag enabled.
				 * 
				 * @since 		1.8.20
				 * 
				 * @requires 	VBO >= 1.16.5 (J) - 1.6.5 (WP)
				 */

				// history record for the overbooking event
				$history_obj->store('OB', $this->overbooking_info);

				// attempt to store the reminder, if supported
				if (class_exists('VBORemindersHelper')) {
					// access the reminders helper class
					$reminders_helper = VBORemindersHelper::getInstance();

					// build readable channel name
					$raw_ch_name  = (string)$this->config['channel']['name'];
					$lower_name   = strtolower($raw_ch_name);
					$lower_name   = preg_replace("/hotel$/", ' hotel', $lower_name);
					$channel_name = ucwords(preg_replace("/api$/", '', $lower_name));

					// make sure VikBooking is up to date
					if (method_exists($reminders_helper, 'setDisplayed')) {
						// build reminder data
						$reminder_short_descr = $this->overbooking_info;
						if (strlen($reminder_short_descr) > 500) {
							$reminder_short_descr = function_exists('mb_substr') ? mb_substr($reminder_short_descr, 0, 500, 'UTF-8') : substr($reminder_short_descr, 0, 500);
							$reminder_short_descr .= '...';
						}

						$overbooking_reminder = new stdClass;
						$overbooking_reminder->title 	 = $channel_name;
						$overbooking_reminder->descr 	 = $reminder_short_descr;
						$overbooking_reminder->duedate 	 = date('Y-m-d H:i:s', strtotime("+1 minute"));
						$overbooking_reminder->usetime 	 = 1;
						$overbooking_reminder->idorder 	 = $neworderid;
						$overbooking_reminder->important = 1;

						// store the reminder
						$reminders_helper->saveReminder($overbooking_reminder);
					}
				}
			}
		}
		
		return $insertdata;
	}
	
	/**
	 * VikBooking v1.7 or higher.
	 * If the method exists, check whether the room specific unit should be assigned to the booking.
	 * 
	 * @return 	bool
	 */	
	private function autoRoomUnit()
	{
		if (method_exists('VikBooking', 'autoRoomUnit')) {
			return VikBooking::autoRoomUnit();
		}

		return false;
	}

	/**
	 * VikBooking v1.7 or higher.
	 * If the method exists, return the specific indexes available.
	 * 
	 * @param 	array	$order
	 * @param 	int 	$roomid
	 * 
	 * @return 	array
	 */	
	private function getRoomUnitNumsAvailable($order, $roomid)
	{
		if (method_exists('VikBooking', 'getRoomUnitNumsAvailable')) {
			return VikBooking::getRoomUnitNumsAvailable($order, $roomid);
		}

		return [];
	}

	/**
	 * VikBooking v1.7 or higher.
	 * The commissions amount is only supported by the v1.7 or higher.
	 * Check if a method of that version exists.
	 * 
	 * @return 	bool
	 */
	private function setCommissions()
	{
		return method_exists('VikBooking', 'autoRoomUnit');
	}

	/**
	 * VikBooking v1.10 or higher.
	 * The field 'otarplan' in '_ordersrooms' is only supported by the v1.10 or higher.
	 * Check if a method of that version exists.
	 * 
	 * @return 	bool
	 */
	private function otaRplanSupported()
	{
		return method_exists('VikBooking', 'getVcmChannelsLogo');
	}

	/**
	 * Tells whether pets are supported by Vik Booking.
	 * 
	 * @return 		bool
	 * 
	 * @requires 	VBO 1.16.1 (J) - 1.6.1 (WP)
	 * 
	 * @since 		1.8.12
	 */
	private function petsSupported()
	{
		return class_exists('VBOMealplanManager');
	}

	/**
	 * Tells whether meal plans are supported by Vik Booking.
	 * 
	 * @return 		bool
	 * 
	 * @requires 	VBO 1.16.1 (J) - 1.6.1 (WP)
	 * 
	 * @since 		1.8.12
	 */
	private function mealPlansSupported()
	{
		return class_exists('VBOMealplanManager');
	}

	/**
	 * Generates a confirmation number for the order and returns it.
	 * It can also update the order record with it.
	 * 
	 * @param 	int 	$oid
	 * @param 	bool 	$update
	 * 
	 * @return 	string
	 */
	public function generateConfirmNumber($oid, $update = true)
	{
		$confirmnumb = date('ym');
		$confirmnumb .= (string)rand(100, 999);
		$confirmnumb .= (string)rand(10, 99);
		$confirmnumb .= (string)$oid;
		if ($update) {
			$dbo = JFactory::getDbo();
			$q = "UPDATE `#__vikbooking_orders` SET `confirmnumber`=".$dbo->quote($confirmnumb)." WHERE `id`=".(int)$oid.";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		return $confirmnumb;
	}
	
	/**
	 * Checks if the given OTA booking ID exists in VikBooking.
	 * Since VCM 1.6.8, this method is accessible in a static context
	 * because it's used also by the ReservationsLogger Class.
	 * The static var $channelName is only defined when accessing the
	 * method in object context, so during a BR_L event.
	 * If the method is called statically by another class, then the
	 * static var $channelName will be empty, but it's okay.
	 * 
	 * @param 	string 	$idorderota 	the OTA Booking ID.
	 * @param 	bool 	$retvbid 		return the existing ID or boolean.
	 * @param 	bool 	$cancelled 	 	exclude/include cancelled bookings.
	 * @param 	string 	$customer_data 	customer information string for iCal channels duplicates when multiple units.
	 * 
	 * @return 	bool|array
	 */
	public static function otaBookingExists($idorderota, $retvbid = false, $cancelled = false, $customer_data = null)
	{
		$dbo = JFactory::getDbo();

		if (!strlen((string)$idorderota)) {
			return false;
		}

		/**
		 * For iCal bookings we need to make sure to properly check the exact
		 * customer name, in order to avoid detecting bookings made by guests
		 * with similar names. In this case, we expect to have "Reserved - Name".
		 * 
		 * @since 	1.8.7
		 */
		$customer_q_operator = 'LIKE';
		if (!empty($customer_data) && stripos($customer_data, 'Reserved - ')) {
			$customer_q_operator = '=';
		}

		/**
		 * While deprecating this iCal channel in exchange of the new API integration,
		 * we always allow to import manually blocked dates through Vrbo, which will
		 * have a booking summary set to "Blocked".
		 * 
		 * @since 	1.8.12
		 */
		if (!empty($customer_data) && stripos($customer_data, 'Name: Blocked') !== false) {
			// go ahead and store it
			return false;
		}

		$q = $dbo->getQuery(true);

		$q->select('*');
		$q->from($dbo->qn('#__vikbooking_orders'));
		if (!$cancelled) {
			$q->where($dbo->qn('status') . ' != ' . $dbo->q('cancelled'));
		}
		if ($customer_data !== null) {
			// fetch existing booking by customer data
			$q->where('(' . $dbo->qn('custdata') . " {$customer_q_operator} " . $dbo->q('%'.$customer_data.'%') . ' OR ' . $dbo->qn('idorderota') . ' = ' . $dbo->q($idorderota) . ')');
		} else {
			// fetch existing booking by OTA (or VBO) ID
			if (!empty(self::$channelName) && !strcasecmp(self::$channelName, 'vrboapi')) {
				// use both OTA and VBO reservation ID
				$q->where(1);
				$q->andWhere([
					$dbo->qn('id') . ' = ' . $dbo->q($idorderota),
					$dbo->qn('idorderota') . ' = ' . $dbo->q($idorderota),
				], 'OR');
			} else {
				// OTA reservation ID
				$q->where($dbo->qn('idorderota') . ' = ' . $dbo->q($idorderota));
			}
		}
		if (!empty(self::$channelName)) {
			$q->where($dbo->qn('channel') . ' LIKE ' . $dbo->q('%' . self::$channelName . '%'));
		}
		$q->order($dbo->qn('id') . ' DESC');

		$dbo->setQuery($q);

		$fetch = $dbo->loadAssocList();
		if (!$fetch) {
			return false;
		}

		if (!$retvbid) {
			return true;
		}

		// attach rooms booked data
		$fetch[0]['rooms_info'] = self::loadBookingRoomsData($fetch[0]['id']);

		return $fetch[0];
	}
	
	/**
	 * Maps the corresponding IdRoom in VikBooking to the IdRoomOta
	 * In case the room belongs to more than one room of VikBooking
	 * only the first active one is returned.
	 * It also stores some values in the class array roomsinfomap
	 * for later actions like room name, room total units.
	 * If the ID is negative then it's because the downloaded booking
	 * in ICS format is generic for the entire property. The absolute
	 * value of the number will be taken in that case.
	 * $idroomota could also be an array of room type id because some
	 * channels allow bookings of multiple rooms, different ones (Booking.com)
	 * 
	 * @param 	mixed 	$idroomota 		string idroomota, or array of strings idroomota
	 * 
	 * @return 	mixed 	string 			idroomvb or array idroomvb
	 */
	public function mapIdroomVbFromOtaId($idroomota)
	{
		$dbo = JFactory::getDbo();
		if (!is_array($idroomota) && intval($idroomota) < 0) {
			$pos_id = (int)abs((float)$idroomota);
			$q = "SELECT `id`,`name`,`units` FROM `#__vikbooking_rooms` WHERE `id`=".$pos_id.";";
			$dbo->setQuery($q);
			$assocs = $dbo->loadAssocList();
			if ($assocs) {
				$this->roomsinfomap[$idroomota]['idroomvb'] = $assocs[0]['id'];
				$this->roomsinfomap[$idroomota]['roomnamevb'] = $assocs[0]['name'];
				$this->roomsinfomap[$idroomota]['totunits'] = $assocs[0]['units'];

				return $assocs[0]['id'];
			}
		}
		if (!is_array($idroomota)) {
			$q = "SELECT `x`.`idroomvb`,`vbr`.`name`,`vbr`.`units` FROM `#__vikchannelmanager_roomsxref` AS `x` " .
				"LEFT JOIN `#__vikbooking_rooms` `vbr` ON `x`.`idroomvb`=`vbr`.`id` " .
				"WHERE `x`.`idroomota`=".$dbo->quote($idroomota)." AND `x`.`idchannel`='".$this->config['channel']['uniquekey']."' " .
				"ORDER BY `x`.`id` ASC;";
			$dbo->setQuery($q);
			$assocs = $dbo->loadAssocList();
			if ($assocs) {
				$this->roomsinfomap[$idroomota]['idroomvb'] = $assocs[0]['idroomvb'];
				$this->roomsinfomap[$idroomota]['roomnamevb'] = $assocs[0]['name'];
				$this->roomsinfomap[$idroomota]['totunits'] = $assocs[0]['units'];

				return $assocs[0]['idroomvb'];
			}
		} else {
			if (!$idroomota) {
				return false;
			}
			$roomsota_count_map = array();
			$in_clause = array();
			foreach ($idroomota as $k => $v) {
				$in_clause[$k] = $dbo->quote($v);
				$roomsota_count_map[$v] = empty($roomsota_count_map[$v]) ? 1 : ($roomsota_count_map[$v] + 1);
			}
			//the old query was modified to be compatible with the sql strict mode
			/*
			$q = "SELECT `x`.`idroomvb`,`x`.`idroomota`,`vbr`.`name`,`vbr`.`units` FROM `#__vikchannelmanager_roomsxref` AS `x` " .
				"LEFT JOIN `#__vikbooking_rooms` `vbr` ON `x`.`idroomvb`=`vbr`.`id` " .
				"WHERE `x`.`idroomota` IN (".implode(', ', array_unique($in_clause)).") AND `x`.`idchannel`='".$this->config['channel']['uniquekey']."' " .
				"GROUP BY `x`.`idroomota` ORDER BY `x`.`id` ASC LIMIT ".count($in_clause).";";
			*/
			$q = "SELECT DISTINCT `x`.`idroomvb`,`x`.`idroomota`,`vbr`.`name`,`vbr`.`units` FROM `#__vikchannelmanager_roomsxref` AS `x` " .
				"LEFT JOIN `#__vikbooking_rooms` `vbr` ON `x`.`idroomvb`=`vbr`.`id` " .
				"WHERE `x`.`idroomota` IN (".implode(', ', array_unique($in_clause)).") AND `x`.`idchannel`='".$this->config['channel']['uniquekey']."' " .
				"ORDER BY `x`.`id` ASC LIMIT ".count($in_clause).";";
			$dbo->setQuery($q);
			$assocs = $dbo->loadAssocList();
			if ($assocs) {
				$idroomvb = array();
				//VCM 1.6.5 - Bookings for two or more equal Room IDs get an invalid count of the returned $idroomvb if we do not make the array unique
				$idroomota = array_unique($idroomota);
				//
				//VCM 1.6.4 - do not rely on the SQL result ordering (or some dates could be assigned to the invalid room), so compose the array with the right ordering as returned by e4jConnect ($idroomota)
				foreach ($idroomota as $k => $v) {
					foreach ($assocs as $rass) {
						if ($rass['idroomota'] != $v) {
							continue;
						}
						$idroomvb[] = $rass['idroomvb'];
						if ($roomsota_count_map[$rass['idroomota']] > 1) {
							for ($i = 1; $i < $roomsota_count_map[$rass['idroomota']]; $i++) {
								$idroomvb[] = $rass['idroomvb'];
							}
						}
						$this->roomsinfomap[$rass['idroomota']]['idroomvb'] = $rass['idroomvb'];
						$this->roomsinfomap[$rass['idroomota']]['roomnamevb'] = $rass['name'];
						$this->roomsinfomap[$rass['idroomota']]['totunits'] = $rass['units'];
					}
				}
				//
				return count($idroomvb) > 0 ? $idroomvb : false;
			}
		}

		return false;
	}

	/**
	 * Finds the mapping relations between the Room ID in VBO and the
	 * OTA rate plan ID stored in the channel manager. Returns the name
	 * of the corresponding Rate Plan and any Meal Plans associated.
	 * Needed by VBO to store the proper information.
	 * 
	 * @param 	string	$rplan_id 	the OTA rate plan ID.
	 * @param 	int		$room_id 	the VBO room id.
	 * 
	 * @return 	array 	list of OTA rate plan name and meal plans.
	 * 
	 * @since 	1.8.12 	the method returns also the meal plans details, if any.
	 */
	private function getOtaRplanDataFromId($rplan_id, $room_id)
	{
		$dbo = JFactory::getDbo();

		$rplan_name = '';
		$meal_plans = '';

		if (empty($rplan_id) || empty($room_id)) {
			return [$rplan_name, $meal_plans];
		}

		$q = "SELECT `x`.`idroomvb`,`x`.`otapricing` FROM `#__vikchannelmanager_roomsxref` AS `x` " .
				"WHERE `x`.`idroomvb`=".(int)$room_id." AND `x`.`idchannel`='".$this->config['channel']['uniquekey']."';";
		$dbo->setQuery($q);
		$rels = $dbo->loadAssocList();
		if (!$rels) {
			return [$rplan_name, $meal_plans];
		}

		foreach ($rels as $k => $rp) {
			if (empty($rp['otapricing'])) {
				continue;
			}

			$otapricing = json_decode($rp['otapricing'], true);
			if (!is_array($otapricing) || !isset($otapricing['RatePlan'])) {
				continue;
			}

			foreach ($otapricing['RatePlan'] as $rpid => $orp) {
				if ((string)$rpid == (string)$rplan_id) {
					// matching rate plan found
					$rplan_name = $orp['name'];

					if (!empty($orp['meal_plans'])) {
						$meal_plans = $orp['meal_plans'];
					}

					// break the loops
					break 2;
				}
			}
		}

		return [$rplan_name, $meal_plans];
	}
	
	/**
	 * Maps the corresponding Price in VikBooking to the OTA RatePlanID.
	 * 
	 * @param 	array	$order
	 * 
	 * @return 	mixed 	false or string
	 */
	public function mapPriceVbFromRatePlanId($order)
	{
		$dbo = JFactory::getDbo();
		if (array_key_exists(0, $order['roominfo'])) {
			//multiple rooms or channel supporting multiple rooms
			$idroomota = array();
			$idroomota_plain = array();
			$otarateplanid = array();
			foreach ($order['roominfo'] as $rk => $rinfo) {
				$idroomota[$rk] = $dbo->quote($rinfo['idroomota']);
				$idroomota_plain[$rk] = (string)$rinfo['idroomota'];
				$otarateplanid[$rk] = $rinfo['rateplanid'];
			}
			//the old query was modified to be compatible with the sql strict mode
			/*
			$q = "SELECT `x`.`idroomota`,`x`.`otapricing`,`vbr`.`name` FROM `#__vikchannelmanager_roomsxref` AS `x` " .
				"LEFT JOIN `#__vikbooking_rooms` `vbr` ON `x`.`idroomvb`=`vbr`.`id` " .
				"WHERE `x`.`idroomota` IN (".implode(',', $idroomota).") AND `x`.`idchannel`='".$this->config['channel']['uniquekey']."' " .
				"GROUP BY `x`.`idroomota` ORDER BY `x`.`id` ASC;";
			*/
			//we don't actually need to group anything here because the foreach loop has a break-state when the idroomota is found
			$q = "SELECT `x`.`idroomota`,`x`.`otapricing`,`vbr`.`name` FROM `#__vikchannelmanager_roomsxref` AS `x` " .
				"LEFT JOIN `#__vikbooking_rooms` `vbr` ON `x`.`idroomvb`=`vbr`.`id` " .
				"WHERE `x`.`idroomota` IN (".implode(',', $idroomota).") AND `x`.`idchannel`='".$this->config['channel']['uniquekey']."' " .
				"ORDER BY `x`.`id` ASC;";
			$dbo->setQuery($q);
			$assocs = $dbo->loadAssocList();
			if ($assocs) {
				$rateplan_info = array();
				foreach ($idroomota_plain as $kk => $rota_id) {
					foreach ($assocs as $k => $rp) {
						if ($rota_id == (string)$rp['idroomota']) {
							if (!empty($rp['otapricing'])) {
								$otapricing = json_decode($rp['otapricing'], true);
								if (!is_null($otapricing) && @count($otapricing) > 0 && @count($otapricing['RatePlan']) > 0) {
									foreach ($otapricing['RatePlan'] as $rpid => $orp) {
										if ((string)$rpid == (string)$otarateplanid[$kk]) {
											$rateplan_info[] = $orp['name'];
											break;
										}
									}
								}
							}
							break;
						}
					}
				}
				if (count($rateplan_info) > 0) {
					return 'RatePlan: '.implode(', ', $rateplan_info);
				}
			}
		} else {
			//single room
			$idroomota = $order['roominfo']['idroomota'];
			$otarateplanid = $order['roominfo']['rateplanid'];
			$q = "SELECT `x`.`otapricing`,`vbr`.`name` FROM `#__vikchannelmanager_roomsxref` AS `x` " .
				"LEFT JOIN `#__vikbooking_rooms` `vbr` ON `x`.`idroomvb`=`vbr`.`id` " .
				"WHERE `x`.`idroomota`=".$dbo->quote($idroomota)." AND `x`.`idchannel`='".$this->config['channel']['uniquekey']."' " .
				"ORDER BY `x`.`id` ASC;";
			$dbo->setQuery($q);
			$assocs = $dbo->loadAssocList();
			if ($assocs) {
				if (!empty($assocs[0]['otapricing'])) {
					$otapricing = json_decode($assocs[0]['otapricing'], true);
					if (!is_null($otapricing) && @count($otapricing) > 0 && @count($otapricing['RatePlan']) > 0) {
						foreach ($otapricing['RatePlan'] as $rpid => $rp) {
							if ((string)$rpid == (string)$otarateplanid) {
								return 'RatePlan: '.$rp['name'];
							}
						}
					}
				}
			}
		}
		return false;
	}
	
	/**
	 * Calculates and returns the timestamp for the checkin date.
	 * 
	 * @param 	string	$checkindate 	Y-m-d date string.
	 * 
	 * @return 	int
	 */
	public function getCheckinTimestamp($checkindate)
	{
		$timestamp = (int) strtotime('00:00:00', strtotime($checkindate));

		$timeopst = $this->getTimeOpenStore();
		if ($timeopst) {
			$timestamp += (int) ($timeopst[0] ?? 0);
		}

		return $timestamp;
	}
	
	/**
	 * Calculates and returns the timestamp for the checkout date.
	 * 
	 * @param 	string	$checkoutdate 	Y-m-d date string.
	 * 
	 * @return 	int
	 */
	public function getCheckoutTimestamp($checkoutdate)
	{
		$timestamp = (int) strtotime('00:00:00', strtotime($checkoutdate));

		$timeopst = $this->getTimeOpenStore();
		if ($timeopst) {
			$timestamp += (int) ($timeopst[1] ?? 0);
		}

		return $timestamp;
	}
	
	/**
	 * Gets the configuration value of VikBooking for the
	 * opening time used by the check-in and the check-out
	 * Returns the values or false.
	 */
	public function getTimeOpenStore()
	{
		return VikBooking::getTimeOpenStore();
	}
	
	/**
	 * Counts and Returns the number of nights with the given
	 * Arrival and Departure timestamps previously calculated
	 * 
	 * @param 	int		$checkints
	 * @param 	int		$checkoutts
	 * 
	 * @return 	int
	 */
	public function countNumberOfNights($checkints, $checkoutts)
	{
		if (empty($checkints) || empty($checkoutts)) {
			return 0;
		}
		$secdiff = $checkoutts - $checkints;
		$daysdiff = $secdiff / 86400;
		if (is_int($daysdiff)) {
			if ($daysdiff < 1) {
				$daysdiff = 1;
			}
		} else {
			if ($daysdiff < 1) {
				$daysdiff = 1;
			} else {
				$sum = floor($daysdiff) * 86400;
				$newdiff = $secdiff - $sum;
				$maxhmore = $this->getHoursMoreRb() * 3600;
				if ($maxhmore >= $newdiff) {
					$daysdiff = floor($daysdiff);
				} else {
					$daysdiff = ceil($daysdiff);
				}
			}
		}
		return $daysdiff;
	}
	
	/**
	 * Returns the optional turnover time in hours.
	 */
	public function getHoursMoreRb()
	{
		return VikBooking::getHoursMoreRb();
	}
	
	/**
	 * Checks if at least one unit of the given room is available
	 * for the given checkin and checkout dates.
	 * 
	 * @param 	int		$idroomvb
	 * @param 	int 	$totunits
	 * @param 	int 	$checkin
	 * @param 	int 	$checkout
	 * @param 	int 	$numnights
	 * 
	 * @return 	bool
	 */
	public function roomIsAvailableInVb($idroomvb, $totunits, $checkin, $checkout, $numnights)
	{
		$dbo = JFactory::getDbo();

		$groupdays = $this->getGroupDays($checkin, $checkout, $numnights);
		$q = "SELECT `id`,`checkin`,`realback` FROM `#__vikbooking_busy` WHERE `idroom`=" . (int)$idroomvb . " AND `realback` > ".(int)$checkin.";";
		$dbo->setQuery($q);
		$busy = $dbo->loadAssocList();
		if ($busy) {
			foreach ($groupdays as $gday) {
				$bfound = 0;
				foreach ($busy as $bu) {
					if ($gday >= $bu['checkin'] && $gday <= $bu['realback']) {
						$bfound++;
					}
				}
				if ($bfound >= $totunits) {
					return false;
				}
			}
		}
		return true;
	}
	
	/**
	 * Checks if at least one unit of the given room is available
	 * for the given checkin and checkout dates excluding the 
	 * busy ids for the old VikBooking order.
	 * 
	 * @param 	int		$idroomvb
	 * @param 	int		$totunits
	 * @param 	int		$checkin
	 * @param 	int		$checkout
	 * @param 	int		$numnights
	 * @param 	array	$excludebusyids
	 * 
	 * @return 	bool
	 */
	public function roomIsAvailableInVbModification($idroomvb, $totunits, $checkin, $checkout, $numnights, $excludebusyids)
	{
		$dbo = JFactory::getDbo();
		$groupdays = $this->getGroupDays($checkin, $checkout, $numnights);
		$q = "SELECT `id`,`checkin`,`realback` FROM `#__vikbooking_busy` WHERE `idroom`=" . (int)$idroomvb . "".(count($excludebusyids) > 0 ? " AND `id` NOT IN (".implode(", ", $excludebusyids).")" : "")." AND `realback` > ".(int)$checkin.";";
		$dbo->setQuery($q);
		$busy = $dbo->loadAssocList();
		if ($busy) {
			foreach ($groupdays as $gday) {
				$bfound = 0;
				foreach ($busy as $bu) {
					if ($gday >= $bu['checkin'] && $gday <= $bu['realback']) {
						$bfound++;
					}
				}
				if ($bfound >= $totunits) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	* Checks if all the rooms booked (more than one) are available
	* for the given checkin and checkout dates.
	* 
	* @param 	array	$idroomsvb
	* @param 	array	$order
	* @param 	int 	$checkin
	* @param 	int 	$checkout
	* @param 	int 	$numnights
	* 
	* @return 	mixed bool true, false or array in case some of the rooms are not available but not all
	*/
	public function roomsAreAvailableInVb($idroomsvb, $order, $checkin, $checkout, $numnights)
	{
		if (!is_array($idroomsvb) || !$idroomsvb) {
			return false;
		}

		$groupdays = $this->getGroupDays($checkin, $checkout, $numnights);

		$dbo = JFactory::getDbo();
		
		$q = "SELECT `b`.*,`r`.`units` AS `room_tot_units` FROM `#__vikbooking_busy` AS `b` LEFT JOIN `#__vikbooking_rooms` `r` ON `r`.`id`=`b`.`idroom` WHERE `b`.`idroom` IN (" . implode(',', array_unique($idroomsvb)) . ") AND `b`.`realback` > ".(int)$checkin.";";
		$dbo->setQuery($q);
		$busy = $dbo->loadAssocList();

		if (!$busy) {
			return true;
		}

		$busy_rooms = [];
		foreach ($busy as $bu) {
			$busy_rooms[$bu['idroom']][] = $bu;
		}

		// check if multiple units of the same room were booked
		$rooms_count_map = [];
		$tot_rooms_booked = 0;
		foreach ($idroomsvb as $idr) {
			$rooms_count_map[(int)$idr] = empty($rooms_count_map[(int)$idr]) ? 1 : ($rooms_count_map[(int)$idr] + 1);
			$tot_rooms_booked++;
		}

		// now the array can be unique
		$idroomsvb = array_unique($idroomsvb);

		// rooms that are not available
		$rooms_not_available = [];

		foreach ($idroomsvb as $kr => $idr) {
			if (isset($busy_rooms[(int)$idr])) {
				foreach ($groupdays as $gday) {
					$bfound = 0;
					$totunits = 1;
					foreach ($busy_rooms[(int)$idr] as $bu) {
						$totunits = $bu['room_tot_units'];
						if ($gday >= $bu['checkin'] && $gday <= $bu['realback']) {
							$bfound++;
						}
					}
					if (($bfound + intval($rooms_count_map[$idr]) - 1) >= $totunits) {
						$rooms_not_available[] = (int)$idr;
					}
				}
			}
		}

		if ($rooms_not_available) {
			// some rooms are not available
			if (count($rooms_not_available) < $tot_rooms_booked) {
				// some rooms may still be available but not all, return the array in this case
				return $rooms_not_available;
			} else {
				// none of the rooms booked is available
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks if all the rooms booked (more than one) are available
	 * for the given checkin and checkout dates excluding the 
	 * busy ids for the old VikBooking order.
	 * 
	 * @param 	array	$idroomsvb
	 * @param 	array	$order
	 * @param 	int		$checkin
	 * @param 	int		$checkout
	 * @param 	int		$numnights
	 * @param 	array	$excludebusyids
	 * 
	 * @return mixed 	boolean true, false or array in case some of the rooms are not available, but not all.
	 */
	public function roomsAreAvailableInVbModification($idroomsvb, $order, $checkin, $checkout, $numnights, $excludebusyids)
	{
		if (!is_array($idroomsvb) || !$idroomsvb) {
			return false;
		}

		$groupdays = $this->getGroupDays($checkin, $checkout, $numnights);

		$dbo = JFactory::getDbo();

		$q = "SELECT `b`.*,`r`.`units` AS `room_tot_units` FROM `#__vikbooking_busy` AS `b` LEFT JOIN `#__vikbooking_rooms` `r` ON `r`.`id`=`b`.`idroom` WHERE `b`.`idroom` IN (" . implode(',', array_unique($idroomsvb)) . ")".(count($excludebusyids) > 0 ? " AND `b`.`id` NOT IN (".implode(", ", $excludebusyids).")" : "")." AND `b`.`realback` > ".(int)$checkin.";";
		$dbo->setQuery($q);
		$busy = $dbo->loadAssocList();

		if (!$busy) {
			return true;
		}

		$busy_rooms = [];
		foreach ($busy as $bu) {
			$busy_rooms[$bu['idroom']][] = $bu;
		}

		// check if multiple units of the same room were booked
		$rooms_count_map = [];
		$tot_rooms_booked = 0;
		foreach ($idroomsvb as $idr) {
			$rooms_count_map[(int)$idr] = empty($rooms_count_map[(int)$idr]) ? 1 : ($rooms_count_map[(int)$idr] + 1);
			$tot_rooms_booked++;
		}

		// now the array can be unique
		$idroomsvb = array_unique($idroomsvb);

		// rooms that are not available
		$rooms_not_available = [];

		foreach ($idroomsvb as $kr => $idr) {
			if (isset($busy_rooms[(int)$idr])) {
				$use_groupdays = $groupdays;
				// check if some rooms have a different check-in or check-out date than the booking information (Booking.com)
				foreach ($order['roominfo'] as $rcount => $ota_room) {
					if (array_key_exists($ota_room['idroomota'], $this->roomsinfomap)) {
						// room has been mapped, check if it is this one
						if ($this->roomsinfomap[$ota_room['idroomota']]['idroomvb'] == $idr) {
							if (array_key_exists('checkin', $ota_room) && array_key_exists('checkout', $ota_room)) {
								if ($ota_room['checkin'] != $order['info']['checkin'] || $ota_room['checkout'] != $order['info']['checkout']) {
									$use_checkints = $this->getCheckinTimestamp($ota_room['checkin']);
									$use_checkoutts = $this->getCheckoutTimestamp($ota_room['checkout']);
									$use_numnights = $this->countNumberOfNights($use_checkints, $use_checkoutts);
									$use_groupdays = $this->getGroupDays($use_checkints, $use_checkoutts, $use_numnights);
								}
							}
						}
					}
				}

				foreach ($use_groupdays as $gday) {
					$bfound = 0;
					$totunits = 1;
					foreach ($busy_rooms[(int)$idr] as $bu) {
						$totunits = $bu['room_tot_units'];
						if ($gday >= $bu['checkin'] && $gday <= $bu['realback']) {
							$bfound++;
						}
					}
					if (($bfound + intval($rooms_count_map[$idr]) - 1) >= $totunits) {
						$rooms_not_available[] = (int)$idr;
					}
				}
			}
		}

		if ($rooms_not_available) {
			// some rooms are not available
			if (count($rooms_not_available) < $tot_rooms_booked) {
				// some rooms may still be available but not all, return the array in this case
				return $rooms_not_available;
			} else {
				// none of the rooms booked is available
				return false;
			}
		}

		return true;
	}
	
	/**
	 * Gets all the days between the checkin and the checkout.
	 * Here the last day so the departure must be considered
	 * to see if the room is available in VikBooking.
	 * 
	 * @param 	int		$checkin 	checkin timestamp.
	 * @param 	int 	$checkout 	checkout timestamp.
	 * @param 	int 	$numnights 	number of nights of stay.
	 * 
	 * @return 	array 				list of timestamps involved.
	 */
	function getGroupDays($checkin, $checkout, $numnights)
	{
		$ret = array();
		$ret[] = $checkin;
		if ($numnights > 1) {
			$start = getdate($checkin);
			$end = getdate($checkout);
			$endcheck = mktime(0, 0, 0, $end['mon'], $end['mday'], $end['year']);
			for ($i = 1; $i < $numnights; $i++) {
				$checkday = $start['mday'] + $i;
				$dayts = mktime(0, 0, 0, $start['mon'], $checkday, $start['year']);
				if ($dayts != $endcheck) {				
					$ret[] = $dayts;
				}
			}
		}
		$ret[] = $checkout;
		return $ret;
	}
	
	/**
	 * Sends an email to the Administrator saying that the room was not
	 * available for the dates requested in the order received from the OTA.
	 * Returns the error message composed to be stored inside the VCM notifications.
	 * 
	 * @param 	array 	$order
	 * 
	 * @return 	string
	 */
	public function notifyAdministratorRoomNotAvailable($order)
	{
		$idroomota = '';
		$roomnamevb = '';
		if (array_key_exists(0, $order['roominfo'])) {
			// multiple Rooms Booked or channel supporting multiple rooms
			foreach ($order['roominfo'] as $rinfo) {
				$idroomota .= $rinfo['idroomota'].', ';
				$roomnamevb .= !empty($this->roomsinfomap[$rinfo['idroomota']]['roomnamevb']) ? $this->roomsinfomap[$rinfo['idroomota']]['roomnamevb'].', ' : '';
			}
			$idroomota = rtrim($idroomota, ', ');
			$roomnamevb = rtrim($roomnamevb, ', ');
		} else {
			$idroomota = $order['roominfo']['idroomota'];
			$roomnamevb = $this->roomsinfomap[$order['roominfo']['idroomota']]['roomnamevb'];
		}

		// build notification error message
		$message = JText::sprintf('VCMOTANEWORDERROOMNOTAVAIL', ucwords($this->config['channel']['name']), $order['info']['idorderota'], $idroomota, $roomnamevb, $order['info']['checkin'], $order['info']['checkout']);

		$vik = new VikApplication(VersionListener::getID());
		$admail = $this->config['emailadmin'];
		$adsendermail = VikChannelManager::getSenderMail();
		$vik->sendMail(
			$adsendermail,
			$adsendermail,
			$admail,
			$admail,
			JText::_('VCMOTANEWORDERROOMNOTAVAILSUBJ'),
			$message,
			false
		);

		return $message;
	}
	
	/**
	 * Sends an email to the Administrator saying that the room was not
	 * available for the dates requested in the order received from the OTA.
	 * Method used when the booking type is Modify.
	 * Returns the error message composed to be stored inside the VCM notifications.
	 * 
	 * @param 	array 	$order
	 * @param 	int 	$idordervb
	 * 
	 * @return 	string
	 */
	public function notifyAdministratorRoomNotAvailableModification($order, $idordervb)
	{
		$idroomota = '';
		$roomnamevb = '';
		if (array_key_exists(0, $order['roominfo'])) {
			//Multiple Rooms Booked or channel supporting multiple rooms
			foreach ($order['roominfo'] as $rinfo) {
				$idroomota .= $rinfo['idroomota'].', ';
				$roomnamevb .= !empty($this->roomsinfomap[$rinfo['idroomota']]['roomnamevb']) ? $this->roomsinfomap[$rinfo['idroomota']]['roomnamevb'].', ' : '';
			}
			$idroomota = rtrim($idroomota, ', ');
			$roomnamevb = rtrim($roomnamevb, ', ');
		} else {
			$idroomota = $order['roominfo']['idroomota'];
			$roomnamevb = $this->roomsinfomap[$order['roominfo']['idroomota']]['roomnamevb'];
		}
		$message = JText::sprintf('VCMOTAMODORDERROOMNOTAVAIL', ucwords($this->config['channel']['name']), $order['info']['idorderota'], $idroomota, $roomnamevb, $order['info']['checkin'], $order['info']['checkout'], $idordervb);
		
		$vik = new VikApplication(VersionListener::getID());
		$admail = $this->config['emailadmin'];
		$adsendermail = VikChannelManager::getSenderMail();
		$vik->sendMail(
			$adsendermail,
			$adsendermail,
			$admail,
			$admail,
			JText::_('VCMOTAMODORDERROOMNOTAVAILSUBJ'),
			$message,
			false
		);
		
		return $message;
	}
	
	/**
	 * Sets errors.
	 * 
	 * @param 	string 	$error
	 */
	public function setError($error)
	{
		$this->errorString .= $error;
	}
	
	/**
	 * Gets active errors.
	 * 
	 * @return 	string
	 */
	public function getError()
	{
		return $this->errorString;
	}
	
	/**
	 * Stores a notification in the db for VikChannelManager.
	 * Type can be: 0 (Error), 1 (Success), 2 (Warning).
	 * 
	 * @param 	int 	$type 		integer type of the notification.
	 * @param 	string 	$from 		the source of the notification.
	 * @param 	string 	$cont 		the content of the notification.
	 * @param 	int 	$idordervb 	the optional VBO booking ID.
	 * 
	 * @return 	void
	 */
	public function saveNotify($type, $from, $cont, $idordervb = 0)
	{
		$dbo = JFactory::getDbo();
		$from = empty($from) ? 'VCM' : $from;
		$q = "INSERT INTO `#__vikchannelmanager_notifications` (`ts`,`type`,`from`,`cont`,`idordervb`,`read`) VALUES('" . time() . "', " . (int)$type . ", " . $dbo->quote($from) . ", " . $dbo->quote($cont) . ", " . (!empty($idordervb) ? (int)$idordervb : 'NULL') . ", 0);";
		$dbo->setQuery($q);
		$dbo->execute();

		return;
	}
	
	/**
	 * Generates and saves a notification key for e4jConnect and VikChannelManager.
	 * 
	 * @param 	int 	$idordervb 	the VBO booking ID.
	 * 
	 * @return 	int 				a random notification key for follow-up/nested notifications.
	 */
	public function generateNKey($idordervb)
	{
		$nkey = rand(1000, 9999);
		$dbo = JFactory::getDbo();
		$q = "INSERT INTO `#__vikchannelmanager_keys` (`idordervb`,`key`) VALUES(" . (int)$idordervb . ", " . (int)$nkey . ");";
		$dbo->setQuery($q);
		$dbo->execute();
		
		return $nkey;
	}

	/**
	 * Attempts to see if any of the available rate plans are assigned to an existing
	 * tax rate, which can be used as default value when creating reservations with a
	 * custom rate. This way, the regular tax policies will be applied.
	 * 
	 * @param 	bool 		$get_record 	if true, the first record found will be returned.
	 * 
	 * @return 	array|int 	0 if not tax rates defined, record ID or record array.
	 * 
	 * @since 	1.8.16
	 */
	private function getDefaultTaxID($get_record = false)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);

		$q->select($dbo->qn([
			'p.id',
			'p.name',
			'p.idiva',
			't.aliq',
			't.breakdown',
			't.taxcap',
		]));
		$q->from($dbo->qn('#__vikbooking_prices', 'p'));
		$q->leftjoin($dbo->qn('#__vikbooking_iva', 't') . ' ON ' . $dbo->qn('p.idiva') . ' = ' . $dbo->qn('t.id'));
		$q->where($dbo->qn('p.idiva') . ' > 0');
		$q->where($dbo->qn('t.aliq') . ' > 0');

		$dbo->setQuery($q, 0, 1);

		$row = $dbo->loadAssoc();
		if (!$row) {
			return $get_record ? [] : 0;
		}

		return $get_record ? $row : $row['idiva'];
	}

	/**
	 * Checks whether a new or a modified booking should trigger Vik Booking
	 * to update the shared availability calendars for the rooms involed.
	 * 
	 * @param 		int 	$bid 	the newly created or modified booking ID.
	 * @param 		boolean $clean 	whether to run cleanSharedCalendarsBusy().
	 * 
	 * @return 		bool 	true if some other cals were occupied, false otherwise.
	 * 
	 * @since 		VCM 1.7.1 (February 2020) - VBO (J)1.13/(WP)1.3.0 (February 2020)
	 *
	 * @requires 	VCM 1.7.1 - VBO (J)1.13/(WP)1.3.0
	 * 
	 * @uses 		VikBooking::updateSharedCalendars()
	 * @uses 		VikBooking::cleanSharedCalendarsBusy()
	 */
	private function updateSharedCalendars($bid, $clean = false)
	{
		if (!class_exists('VikBooking')) {
			require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';
		}
		if (!method_exists('VikBooking', 'updateSharedCalendars')) {
			// VBO >= 1.13 (Joomla) - 1.3.0 (WordPress) is required.
			return false;
		}

		if ($clean) {
			// useful when modifying a booking to clean up the previously occupied shared cals.
			VikBooking::cleanSharedCalendarsBusy((int)$bid);
		}

		// let Vik Booking handle the involved calendars
		return VikBooking::updateSharedCalendars((int)$bid);
	}

	/**
	 * Tells whether VBO is updated enough to support the booking-type features.
	 * 
	 * @return 	bool
	 * 
	 * @since 	1.8.0
	 */
	private function isBookingTypeSupported()
	{
		if (!method_exists('VikBooking', 'isBookingTypeSupported')) {
			// VBO >= 1.14 (Joomla) - 1.4.0 (WordPress) is required.
			return false;
		}

		return VikBooking::isBookingTypeSupported();
	}
}
