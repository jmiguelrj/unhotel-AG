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
 * Driver used to send chat notifications via mail.
 * 
 * @since 1.6.13
 */
class VCMChatNotificationDriverMail implements VCMChatNotificationDriver
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
		if (!$user->getMail())
		{
			// e-mail address is empty
			return false;
		}

		// build display data
		$data = array(
			'user' 		=> $user,
			'message' 	=> $message,
		);

		// obtain mail layout
		$tmpl = $this->getLayout($data);

		// build mailer vars
		$from_address  = VikBooking::getSenderMail();
		$from_name     = VikBooking::getFrontTitle();
		$reply_address = 'no-reply@' . JUri::getInstance()->toString(array('host'));
		
		if ($user->getClient() == 1)
		{
			// subject for admin
			$subject = JText::sprintf('VCM_CHAT_MAIL_SUBJECT_HOTEL', $user->getCustomerName());
		}
		else
		{
			// subject for client
			$subject = JText::sprintf('VCM_CHAT_MAIL_SUBJECT_GUEST', $from_name);
		}

		// get mailer
		$mailer = JFactory::getMailer();

		$mailer->setSender(array($from_address, $from_name));
		$mailer->addRecipient($user->getMail());
		$mailer->addReplyTo($reply_address);
		$mailer->setSubject($subject);
		$mailer->setBody($tmpl);
		$mailer->isHTML(true);

		$mailer->Encoding = 'base64';

		// append attachments
		foreach ($message->getAttachments() as $attachment)
		{
			// convert URI to internal path
			$attachment = implode(DIRECTORY_SEPARATOR, [VCM_SITE_PATH, 'helpers', 'chat', 'attachments', basename($attachment)]);

			if (is_file($attachment))
			{
				$mailer->addAttachment($attachment);
			}
		}

		// send mail and return response
		return $mailer->Send();
	}

	/**
	 * Checks whether this driver is supported or not.
	 * 
	 * @return 	boolean  True in case this driver can be used, false otherwise.
	 */
	public function isSupported()
	{
		// mail driver should be always up and running
		return true;
	}

	/**
	 * Returns the parsed layout to be used within the notification e-mail.
	 *
	 * @param 	array 	$displayData 	An associative array contining display data.
	 *
	 * @return 	string 	The fetched layout.
	 *
	 * @uses 	getLayoutPath()
	 */
	protected function getLayout(array $displayData = array())
	{
		// start caching buffer
		ob_start();
		// try to include the layout file
		include $this->getLayoutPath();
		// obtain buffer caught
		$tmpl = ob_get_contents();
		// flush buffer
		ob_end_clean();

		return $tmpl;
	}

	/**
	 * Returns the path of the layout file to be used for notification e-mails.
	 *
	 * @return 	string 	The layout path.
	 */
	protected function getLayoutPath()
	{
		$layoutName = null;

		// load plugins dispatcher
		JPluginHelper::importPlugin('e4j');
		if (class_exists('JEventDispatcher'))
		{
			$dispatcher   = JEventDispatcher::getInstance();
			$dispatch_met = 'trigger';
		}
		else
		{
			$dispatcher   = JFactory::getApplication();
			$dispatch_met = 'triggerEvent';
		}

		/**
		 * Trigger event to allow the plugins to change the layout at runtime, which
		 * will be used to parse the template to be sent via mail as chat notification.
		 * 
		 * It is needed to assign a new file name to the passed argument.
		 * No path and extension are needed.
		 * The file must be included within the default layouts path:
		 * .../helpers/chat/notification/layouts/
		 *
		 * Example of usage:
		 *
		 * $layoutName = 'custom';
		 *
		 * @param 	string 	&$layoutName   The name of the layout file that will be used.
		 * 								   Arguments is passed by reference so that it can
		 * 								   be altered without returning it.
		 *
		 * @return 	void
		 *
		 * @since 	1.6.13
		 */
		$dispatcher->{$dispatch_met}('onBeforeChatNotificationMailLayout', array(&$layoutName));

		// always trim trailing .php
		$layoutName = preg_replace("/\.php$/", '', (string) $layoutName);

		// construct base path
		$base = implode(DIRECTORY_SEPARATOR, [VCM_SITE_PATH, 'helpers', 'chat', 'notification', 'layouts']);

		// check if we have a valid layout
		if (!$layoutName || !is_file($base . DIRECTORY_SEPARATOR . $layoutName . '.php'))
		{
			// use standard layout name
			$layoutName = 'mail';

			// make sure the default mail.php file hasn't been removed
			if (!is_file($base . DIRECTORY_SEPARATOR . $layoutName . '.php'))
			{
				throw new Exception(sprintf('Mail layout [%s] not found', $layoutName), 404);
			}
		}

		// return layout base path
		return $base . DIRECTORY_SEPARATOR . $layoutName . '.php';
	}
}
