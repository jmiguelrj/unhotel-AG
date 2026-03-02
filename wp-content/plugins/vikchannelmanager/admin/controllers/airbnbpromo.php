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

JLoader::import('adapter.mvc.controllers.admin');

class VikChannelManagerControllerAirbnbpromo extends JControllerAdmin
{
	/**
	 * Task airbnbpromo.savepromo to save a new promotion
	 * 
	 * @uses 	_sendPromoUpdate()
	 */
	public function savepromo()
	{
		$this->_sendPromoUpdate();
	}

	/**
	 * Task airbnbpromo.updatepromo to update a promotion
	 * 
	 * @uses 	_sendPromoUpdate()
	 */
	public function updatepromo()
	{
		$this->_sendPromoUpdate('edit');
	}

	/**
	 * Main private method used to notify to the e4jConnect servers
	 * the creation or the update of a Promotion.
	 * These tasks are called via AJAX, so the response must be echoed
	 * and the process should exit upon completion.
	 * 
	 * @param 	boolean 	$updaction 	Whether the action is to create or update a promotion.
	 * 									For updatepromo this must be non-false, false for new.
	 * 
	 * @return 	void
	 */
	private function _sendPromoUpdate($updaction = false)
	{
		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (empty($channel['params']['user_id']) || $channel['uniquekey'] != VikChannelManagerConfig::AIRBNBAPI) {
			echo 'e4j.error.Empty Host User ID for Airbnb.';
			exit;
		}

		$obj = VikChannelManager::getPromotionHandlers('airbnbapi');
		if (!$obj || !is_object($obj)) {
			echo 'e4j.error.Could not instantiate promotions object';
			exit;
		}

		$result = $obj->createPromotion($channel['params'], $updaction);

		if (!$result) {
			echo 'e4j.error.' . $obj->getError();
			exit;
		}

		$rs = $obj->getResponse();
		if (!$rs) {
			echo 'e4j.error.Empty response received';
			exit;
		}

		// in case of success, unset the current session values
		$session->set('vcmAirbnbPromo', '');
		// we set a new session value to store the ID of the promotion just created/updated
		$session->set('vcmAirbnbPData', str_replace('e4j.ok.', '', $rs));

		// print the e4jConnect response either way
		echo $rs;
		exit;
	}
}
