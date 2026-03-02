<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

// import Joomla view library
jimport('joomla.application.component.view');

class VikChannelManagerViewExecpcid extends JViewUI
{
	public function display($tpl = null)
	{
		$dbo = JFactory::getDbo();

		if (!function_exists('curl_init')) {
			echo VikChannelManager::getErrorFromMap('e4j.error.Curl');
			exit;
		}

		$apikey = VCMFactory::getConfig()->get('apikey', '');
		if (empty($apikey)) {
			echo VikChannelManager::getErrorFromMap('e4j.error.Settings');
			exit;
		}

		$channel_source = VikRequest::getString('channel_source');
		$ota_id = VikRequest::getString('otaid');

		if (empty($ota_id)) {
			throw new Exception('Missing OTA reservation ID', 400);
		}

		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `idorderota`=" . $dbo->quote($ota_id);
		$dbo->setQuery($q, 0, 1);
		$order = $dbo->loadAssoc();
		if (!$order) {
			throw new Exception('Reservation not found', 404);
		}

		/**
		 * Use the OTA Booking helper class to request the decoded CC details.
		 * 
		 * @since 	1.8.19
		 */
		$cc_helper = VCMOtaBooking::getInstance([
			'channel_source' => $channel_source,
			'ota_id' 		 => $ota_id,
			'booking'        => $order,
		], $anew = true);

		$credit_card_response = $cc_helper->decodeCreditCardDetails();

		if (!$credit_card_response || !empty($credit_card_response['error'])) {
			echo '<p style="margin: 10px 0px; padding: 12px; text-align: center; color: #D8000C; background: #FFBABA;">' . ($credit_card_response ? $credit_card_response['error'] : '') . '</p>';
			exit;
		}

		$this->creditCardResponse = $credit_card_response;
		$this->order = $order;

		// Display the template
		parent::display($tpl);
	}
}
