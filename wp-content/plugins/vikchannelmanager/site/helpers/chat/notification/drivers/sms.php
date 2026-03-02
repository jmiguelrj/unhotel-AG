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
 * Driver used to send chat notifications via SMS.
 * 
 * @since 1.6.13
 */
class VCMChatNotificationDriverSms implements VCMChatNotificationDriver
{
	/**
	 * Performs the notification event.
	 *
	 * @param 	VCMChatUser 	$user 	  The instance containing the user/order details.
	 * @param 	VCMChatMessage  $message  The instance containing the message details.
	 *
	 * @return 	boolean 		True on success, false otherwise.
	 *
	 * @throws 	Exception 		In case something went wrong while dispatching the notification.
	 */
	public function notify(VCMChatUser $user, VCMChatMessage $message)
	{
		if (!$user->getPhone())
		{
			// phone number is empty
			return false;
		}

		$send_sms_to = VikBooking::getSendSMSTo();
		if (!count($send_sms_to))
		{
			// no recipients enabled (not the guest, nor the hotel)
			return false;
		}
		
		// instantiate SMS API object
		$sms_obj = new VikSmsApi($user->getOrder(), (isset($this->sms_api_params) ? $this->sms_api_params : VikBooking::getSMSParams()));
		$result  = false;

		if ($user->getClient() == 1)
		{
			if (!in_array('admin', $send_sms_to))
			{
				// the admin is not supposed to receive SMS messages
				return false;
			}

			// SMS text for admin
			$sms_text = JText::sprintf('VCM_CHAT_SMS_TEXT_HOTEL', $user->getCustomerName(), $message->getContent());

			foreach ($user->getPhone() as $admin_phone)
			{
				// send the SMS message
				$response = $sms_obj->sendMessage($admin_phone, $sms_text);
				$result   = $sms_obj->validateResponse($response) || $result;
			}
		}
		else
		{
			if (!in_array('customer', $send_sms_to))
			{
				// the client is not supposed to receive SMS messages
				return false;
			}

			// SMS text for client
			$sms_text = JText::sprintf('VCM_CHAT_SMS_TEXT_GUEST', VikBooking::getFrontTitle(), $message->getContent());

			// send the SMS message
			$response = $sms_obj->sendMessage($user->getPhone(), $sms_text);
			$result   = $sms_obj->validateResponse($response);
		}

		return $result;
	}

	/**
	 * Checks whether this driver is supported or not.
	 * 
	 * @return 	boolean  True in case this driver can be used, false otherwise.
	 */
	public function isSupported()
	{
		$sms_api  = VikBooking::getSMSAPIClass();
		$sms_path = VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . 'smsapi' . DIRECTORY_SEPARATOR . $sms_api;
		if (empty($sms_api) || !is_file($sms_path) || !VikBooking::autoSendSMSEnabled())
		{
			// return false if no SMS API file selected, or auto-send is disabled
			return false;
		}
		
		$this->sms_api_params = VikBooking::getSMSParams();
		if (!count($this->sms_api_params))
		{
			// return false if no SMS API set up
			return false;
		}

		// require the SMS API class file and return true
		require_once $sms_path;

		return true;
	}
}
