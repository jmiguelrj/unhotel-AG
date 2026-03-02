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
 * Driver used to send chat notifications to the Mobile APP.
 * 
 * @since 1.6.13
 */
class VCMChatNotificationDriverMobileapp implements VCMChatNotificationDriver
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
		if ($user->getClient() == 0)
		{
			// do not proceed in case we are going to notify the customer,
			// because it cannot own our Mobile APP
			return false;
		}

		// load configuration
		$apikey = VikChannelManager::getApiKey(true);
		if (!function_exists('curl_init') || empty($apikey))
		{
			return false;
		}

		// get booking object information
		$booking = $user->getOrder();

		// send the request to e4jConnect
		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=chtnf&c=generic";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager CHTNF Request e4jConnect.com - Mobile App -->
<ChatNotifRQ xmlns="http://www.e4jconnect.com/avail/chtnfrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$apikey.'"/>
	<ChatNotif>
		<Fetch bid="'.$booking->id.'"/>
		<Customer><![CDATA['.$user->getCustomerName().']]></Customer>
		<Message><![CDATA['.$message->getContent().']]></Message>
	</ChatNotif>
</ChatNotifRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo() || strpos($rs, 'e4j.ok') === false)
		{
			throw new Exception(sprintf('Invalid response [%s]: %s', __METHOD__, $rs), 502);
		}

		return true;
	}

	/**
	 * Checks whether this driver is supported or not.
	 * 
	 * @return 	boolean  True in case this driver can be used, false otherwise.
	 */
	public function isSupported()
	{
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".(int)VikChannelManagerConfig::MOBILEAPP;
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if ($dbo->getNumRows())
		{
			return true;
		}

		return false;
	}
}
