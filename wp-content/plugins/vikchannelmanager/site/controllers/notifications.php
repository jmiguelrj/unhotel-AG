<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      Alessio Gaggii - E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * VikChannelManager notifications controller endpoint.
 *
 * @since 	1.8.24
 */
class VikChannelManagerControllerNotifications extends JControllerAdmin
{
	/**
	 * Processes a list of notifications posted through a JSON payload.
	 * 
	 * @return 	void
	 */
	public function process()
	{
		$app = JFactory::getApplication();

		if (VCMAuthHelper::getBearer() !== md5(VikChannelManager::getApiKey())) {
			// 401 - Unauthorized
			VCMHttpDocument::getInstance($app)->close(401, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// access all notifications from the JSON request body
		$notifications = $app->input->json->get('notifications', [], 'array');

		if (!$notifications) {
			// 400 = Bad Request
			VCMHttpDocument::getInstance($app)->close(400, 'Bad Request');
		}

		if (!method_exists('VBOFactory', 'getNotificationCenter')) {
			// 501 = Not Implemented (VikBooking is outdated)
			VCMHttpDocument::getInstance($app)->close(501, 'Not Implemented');
		}

		// let VikBooking process and store the notifications
		try {
			VCMHttpDocument::getInstance($app)->json(
				VBOFactory::getNotificationCenter()
					->store($notifications)
			);
		} catch (Throwable $e) {
			// terminate the execution with the error caught
			VCMHttpDocument::getInstance($app)->close($e->getCode(), $e->getMessage());
		}
	}
}
