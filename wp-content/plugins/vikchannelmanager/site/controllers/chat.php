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

// load chat controller implementor
require_once VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'implementors' . DIRECTORY_SEPARATOR . 'chat.php';

/**
 * VikChannelManager chat controller.
 *
 * @since 1.6.13
 */
class VikChannelManagerControllerChat extends VCMControllerImplementorChat
{
	/**
	 * AJAX end-point used to send a reaction to an existing guest message.
	 * This task is always submitted by the Hotel/Host or operator.
	 *
	 * @return 	void
	 * 
	 * @since 	1.9.18
	 */
	public function thread_message_reaction()
	{
		$app = JFactory::getApplication();

		// make sure the user is authorised to send message reactions to this thread
		if (!$this->isAuthenticated()) {
			// only operators can submit message reactions through front-site
			VCMHttpDocument::getInstance($app)->close(403, 'Forbidden endpoint');
		}

		// process the request
		parent::thread_message_reaction();
	}
}
