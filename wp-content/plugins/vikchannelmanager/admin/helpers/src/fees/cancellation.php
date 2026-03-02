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
 * VCM fees cancellation handler to detect if specific OTA reservations have constraints.
 * 
 * @since 	1.8.12
 */
final class VCMFeesCancellation extends JObject
{
	/**
	 * The singleton instance of the class.
	 *
	 * @var  VCMFeesCancellation
	 */
	private static $instance = null;

	/**
	 * Proxy for immediately getting the object and bind data.
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
	 * Tells whether the current reservation is constrained. The whole
	 * booking record is expected to be passed to the constructor.
	 * 
	 * @return 	bool 	true if cancellation is not permitted.
	 */
	public function isBookingConstrained()
	{
		$bid 	  = $this->get('id', 0);
		$status   = $this->get('status', '');
		$checkout = $this->get('checkout', 0);
		$ota_bid  = $this->get('idorderota', '');
		$channel  = $this->get('channel', '');

		if (!$bid || !$status || !$checkout || !$ota_bid || !$channel) {
			// nothing to validate
			return false;
		}

		if (strcasecmp($status, 'cancelled')) {
			// no constraints if the current status is NOT cancelled
			return false;
		}

		if (stripos($channel, 'Vrboapi') === 0) {
			// this channel is subjected to restrictions (bookings must remain for 45 days after the checkout)
			$lim_canc_ts = strtotime('+45 days', $checkout);

			if ($lim_canc_ts > time()) {
				// this booking cannot be purge removed yet
				$this->setError(sprintf('The reservation ID %d cannot be completely removed until %s as for requirements of Vrbo.', $bid, date('Y-m-d', $lim_canc_ts)));

				// prevent the complete removal
				return true;
			}
		}

		// nothing to restrict
		return false;
	}
}
