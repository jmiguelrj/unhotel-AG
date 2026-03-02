<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2023 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * OTA Booking helper. Used to detect specific OTA reservations or to retrieve PCI data.
 * 
 * @since 	1.8.16
 */
final class VCMOtaBooking extends JObject
{
	/**
	 * The singleton instance of the class.
	 *
	 * @var  VCMOtaBooking
	 */
	private static $instance = null;

	/**
	 * Proxy to construct the object.
	 * 
	 * @param 	array|object  $data  optional data to bind.
	 * @param 	boolean 	  $anew  true for forcing a new instance.
	 * 
	 * @return 	self
	 */
	public static function getInstance($data = [], $anew = false)
	{
		if (is_null(static::$instance) || $anew) {
			static::$instance = new static($data);
		}

		return static::$instance;
	}

	/**
	 * Returns a list of channels that can be assigned to a specific booking.
	 * Originally introduced to allow admins to manually assign a website
	 * reservation to a channel like Vrbo API so that it will appear in the BUS.
	 * 
	 * @return 	array 	list of associative channels eligible for the booking.
	 */
	public function getChannelsAssignable()
	{
		$bid 	  = $this->get('id');
		$channel  = $this->get('channel');
		$ota_bid  = $this->get('idorderota');
		$status   = $this->get('status', '');
		$checkout = $this->get('checkout', 0);

		if (empty($bid) || !empty($channel) || !empty($ota_bid)) {
			// only website reservations are allowed
			return [];
		}

		if ($this->get('closure', 0) || $this->get('total', 0) <= 0) {
			// should not be a closure and the total amount should be greater than zero
			return [];
		}

		$max_checkout_past = strtotime('-46 days');

		if ($status != 'confirmed' || $checkout < $max_checkout_past) {
			// reservation not eligible
			return [];
		}

		if (!VikChannelManager::channelHasRoomsMapped(VikChannelManagerConfig::VRBOAPI)) {
			// the channel Vrbo API must be active and configured
			return [];
		}

		return [
			'vrboapi_vrboapi' => 'Vrbo',
		];
	}

	/**
	 * Performs a request to the E4jConnect servers to decode the
	 * OTA credit card (partial) details that were previously
	 * stored upon receiving a new reservation.
	 * 
	 * @return 	array 	associative array with the CC details or error message.
	 * 
	 * @since 	1.8.19
	 */
	public function decodeCreditCardDetails()
	{
		$apikey = VCMFactory::getConfig()->get('apikey', '');

		$channel_source = $this->get('channel_source');
		$ota_id 		= $this->get('ota_id');
		$booking_info   = $this->get('booking', []);

		if (!$apikey || !$channel_source || !$ota_id) {
			return [
				'error' => 'Missing values to request the credit card details.',
			];
		}

		/**
		 * In case of Booking.com reservations, attempt to use their Payments API first.
		 * 
		 * @since 	1.9.10
		 */
		$remote_vcc_error = '';
		if (!strcasecmp((string) $channel_source, 'Booking.com')) {
			try {
				$vcc_payout_details = $this->getBookingDotComVccPayoutDetails([
					'ota_id'  => $ota_id,
					'booking' => $booking_info,
				]);
				if (!empty($vcc_payout_details->payout->virtual_credit_cards) && is_array($vcc_payout_details->payout->virtual_credit_cards)) {
					// we have at least one VCC payout details, convert into an associative array
					$vcc_details = (array) json_decode(json_encode($vcc_payout_details->payout->virtual_credit_cards[0]), true);
					if ($vcc_details) {
						// return the VCC reservation payout details obtained
						return $vcc_details;
					}
				}
			} catch (Exception $e) {
				// silently catch the error message and proceed with the remote CC details fetching and decoding
				$remote_vcc_error = $e->getMessage();
			}
		}

		// perform the request to obtain the remote (V)CC details
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=pcid&c=generic";

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager PCID Request e4jConnect.com - Module Extensionsforjoomla.com -->
<PCIDataRQ xmlns="http://www.e4jconnect.com/schemas/pcidrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $apikey . '"/>
	<Channel source="' . $channel_source . '"/>
	<Booking otaid="' . $ota_id . '"/>
</PCIDataRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			return [
				'error' => 'Error ' . @curl_error($e4jC->getCurlHeader()),
			];
		}

		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			return [
				'error' => VikChannelManager::getErrorFromMap($rs) . $remote_vcc_error,
			];
		}

		// the salt is hashed twice
		$cipher = VikChannelManager::loadCypherFramework(md5($apikey . "e4j" . $ota_id));

		// @array credit card response
		// [card_number] @string : 4242 4242 4242 ****
		// [cvv] @int : 123
		$credit_card_response = json_decode((string) $cipher->decrypt($rs), true);
		$credit_card_response = !is_array($credit_card_response) ? [] : $credit_card_response;

		if (!$credit_card_response) {
			return [
				'error' => 'Could not decode credit card details, which may not be available. ' . $remote_vcc_error,
			];
		}

		// return the whole credit card details associative array with decoded information
		return $credit_card_response;
	}

	/**
	 * Makes use of the Booking.com Payments API to retrieve the VCC Payout Details
	 * for a given OTA reservation number.
	 * 
	 * @param 	array 	$options 	List of fetching options.
	 * 
	 * @return 	object 				VCC reservation payout details, if any.
	 * 
	 * @throws 	Exception
	 * 
	 * @since 	1.9.10
	 * @since 	1.9.12 request is prevented in case the reservation is not on Payments By Booking (PBB) through VCC.
	 */
	public function getBookingDotComVccPayoutDetails(array $options)
	{
		$endpoint = sprintf('https://slave.e4jconnect.com/channelmanager/v2/bookingcom/payments/reservations/%s/vcc', $options['ota_id'] ?? '');

		$transporter = new E4jConnectRequest($endpoint, true);
		$transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json');
		$transporter->setHttpHeader(['Accept: application/json'], $replace = false);

		try {
			// ensure the reservation is on Payments By Booking (PBB) through VCC
			$ota_type_data = [];
			if (!empty($options['booking']['ota_type_data'])) {
				$ota_type_data = !is_scalar($options['booking']['ota_type_data']) ? $options['booking']['ota_type_data'] : (array) json_decode($options['booking']['ota_type_data'], true);
			}
			
			if (empty($ota_type_data['pay_type']) || stripos((string) $ota_type_data['pay_type'], 'vcc') === false) {
				// in order to avoid API errors, the VCC payout details request on the Payments API will be skipped completely
				throw new Exception('Reservation is not on Payments By Booking.com through VCC.', 406);
			}

			// obtain the reservation payout details in JSON format
			$vcc_payout_details = $transporter->fetch('GET', 'json');

			// normalize VCC properties
			foreach ((array) ($vcc_payout_details->payout->virtual_credit_cards ?? []) as &$virtual_credit_card) {
				if (!is_object($virtual_credit_card)) {
					continue;
				}

				// handle card number
				if ($virtual_credit_card->card_details->card_number ?? '') {
					$virtual_credit_card->card_number = $virtual_credit_card->card_details->card_number;
				}

				// handle card-holder name
				if ($virtual_credit_card->card_details->card_name ?? '') {
					$virtual_credit_card->name = $virtual_credit_card->card_details->card_name;
				}

				// handle card full expiratation date
				if (($virtual_credit_card->expiration_date ?? '') && ($virtual_credit_card->card_details->card_expiry ?? '')) {
					$virtual_credit_card->expiration_date_ymd = $virtual_credit_card->expiration_date;
				}

				// handle card expiry date (MM/YY)
				if ($virtual_credit_card->card_details->card_expiry ?? '') {
					if (preg_match('/^[0-9]{2}\/[0-9]{2}$/', $virtual_credit_card->card_details->card_expiry)) {
						// detected mm/yy format
						$virtual_credit_card->expiration_date = str_replace('/', '/' . substr(date('Y'), 0, 2), $virtual_credit_card->card_details->card_expiry);
					} else {
						// set raw date
						$virtual_credit_card->expiration_date = $virtual_credit_card->card_details->card_expiry;
					}
				}

				// handle CVV code
				if ($virtual_credit_card->card_details->cvc ?? '') {
					$virtual_credit_card->cvv = $virtual_credit_card->card_details->cvc;
				} elseif ($virtual_credit_card->card_details->cvv ?? '') {
					$virtual_credit_card->cvv = $virtual_credit_card->card_details->cvv;
				}
			}

			// unset last reference
			unset($virtual_credit_card);

			// normalize nested object properties by flattening them
			foreach ((array) ($vcc_payout_details->payout->virtual_credit_cards ?? []) as &$virtual_credit_card) {
				foreach ($virtual_credit_card as $prop => $data) {
					if (!is_object($data) && !is_array($data)) {
						// nothing to flatten
						continue;
					}
					foreach ($data as $sub_prop => $sub_data) {
						if (is_null($sub_data)) {
							continue;
						}
						$nested_prop_name = $prop . '_' . $sub_prop;
						$virtual_credit_card->{$nested_prop_name} = is_scalar($sub_data) ? $sub_data : json_encode($sub_data);
					}
					// get rid of the main property
					unset($virtual_credit_card->{$prop});
				}
			}

			// unset last reference
			unset($virtual_credit_card);

			// return the normalized object
			return $vcc_payout_details;
		} catch (Exception $e) {
			// propagate the error
			throw new Exception(sprintf('Reservation payout details - %s', $e->getMessage()), $e->getCode() ?: 500);
		}
	}
}
