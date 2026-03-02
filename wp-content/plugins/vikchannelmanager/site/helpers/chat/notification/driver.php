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
 * Base interface for chat messages notifications.
 * 
 * @since 1.6.13
 */
interface VCMChatNotificationDriver
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
	public function notify(VCMChatUser $user, VCMChatMessage $message);

	/**
	 * Checks whether this driver is supported or not.
	 * 
	 * @return 	boolean  True in case this driver can be used, false otherwise.
	 */
	public function isSupported();
}
