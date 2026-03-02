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

class VikChannelManagerControllerBpromo extends JControllerAdmin
{
	/**
	 * Task bpromo.savepromo to save a new promotion
	 * 
	 * @uses 	_sendPromoUpdate()
	 */
	public function savepromo()
	{
		$this->_sendPromoUpdate();
	}

	/**
	 * Task bpromo.updatepromo to update a promotion
	 * 
	 * @uses 	_sendPromoUpdate()
	 */
	public function updatepromo()
	{
		$this->_sendPromoUpdate('edit');
	}

	/**
	 * Main private method used to notify to the e4jConnect servers
	 * the creation or the modification of a Promotion.
	 * These tasks are called via Ajax, so the response must be echoed
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
		if (!isset($channel['params']['hotelid']) || empty($channel['params']['hotelid']) || $channel['uniquekey'] != VikChannelManagerConfig::BOOKING) {
			echo 'e4j.error.Empty Hotel ID for Booking.com.';
			exit;
		}

		$obj = VikChannelManager::getPromotionHandlers('booking.com');
		if (!$obj || !is_object($obj)) {
			echo 'e4j.error.Could not instantiate promotions object';
			exit;
		}

		$result = $obj->createPromotion(array(
			'hotelid' => $channel['params']['hotelid'],
		), $updaction);

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
		$session->set('vcmBPromo', '');
		// we set a new session value to store the ID of the promotion just created/updated
		$session->set('vcmBPData', str_replace('e4j.ok.', '', $rs));

		// print the e4jConnect response either way
		echo $rs;
		exit;
	}

	/**
	 * Tasks bpromo.activate to activate a promotion.
	 * 
	 * @uses 	toggleActivate()
	 */
	public function activate()
	{
		$this->toggleActivate('activate');
	}

	/**
	 * Tasks bpromo.deactivate to deactivate a promotion.
	 * 
	 * @uses 	toggleActivate()
	 */
	public function deactivate()
	{
		$this->toggleActivate('deactivate');
	}

	/**
	 * Tasks bpromo.deactivate to deactivate a promotion,
	 * and bpromo.activate to activate an existing promo.
	 * Sends an XML request to the e4jConnect servers, and
	 * displays the response to VCM. This request is made
	 * via GET/POST, not through Ajax.
	 * 
	 * @param 	string 	$typeaction 	accepts activate and deactivate
	 * 
	 * @return 	void
	 */
	private function toggleActivate($typeaction = 'deactivate')
	{
		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();
		$mainframe = JFactory::getApplication();

		$promoid = VikRequest::getString('promoid', '', 'request');

		// configuration fields validation
		if (!function_exists('curl_init')) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Curl'));
			$mainframe->redirect('index.php?option=com_vikchannelmanager&task=bpromo');
			exit;
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (!isset($channel['params']['hotelid']) || empty($channel['params']['hotelid']) || $channel['uniquekey'] != VikChannelManagerConfig::BOOKING) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Empty Hotel ID for Bookingcom'));
			$mainframe->redirect('index.php?option=com_vikchannelmanager&task=bpromo');
			exit;
		}

		if (empty($promoid)) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Missing Promotion ID'));
			$mainframe->redirect('index.php?option=com_vikchannelmanager&task=bpromo');
			exit;
		}

		// required filters
		$filters = array(
			'hotelid="'.$channel['params']['hotelid'].'"',
			'action="'.$typeaction.'"',
			'promoid="'.$promoid.'"'
		);

		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=wprom&c=".$channel['name'];

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager WPROM Request e4jConnect.com - '.ucwords($channel['name']).' -->
<WritePromotionRQ xmlns="http://www.e4jconnect.com/channels/wpromrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.VikChannelManager::getApiKey(true).'"/>
	<Fetch '.implode(' ', $filters).'/>
</WritePromotionRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();

		if ($e4jC->getErrorNo()) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.'.(@curl_error($e4jC->getCurlHeader()))));
			$mainframe->redirect('index.php?option=com_vikchannelmanager&task=bpromo');
			exit;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
			/**
			 * Raise an additional error to inform the client that maybe this promotion
			 * cannot be activated or deactivated. Paolo said the activation does not
			 * work for promotions previously deactivated via API.
			 */
			if ($typeaction == 'activate') {
				VikError::raiseWarning('', JText::_('VCMBPROMOERRNOACTIVATE'));
			}
			//
			$mainframe->redirect('index.php?option=com_vikchannelmanager&task=bpromo');
			exit;
		}
		if (strpos($rs, 'e4j.ok') === false) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Invalid response received. '.$rs));
			$mainframe->redirect('index.php?option=com_vikchannelmanager&task=bpromo');
			exit;
		}

		// in case of success, unset the current session values
		$session->set('vcmBPromo', '');

		// update the OTA promotion information
		$obj = VikChannelManager::getPromotionHandlers('booking.com');
		if (is_object($obj)) {
			$obj->channelPromotionCompleted($promoid, $typeaction, array('hotelid' => $channel['params']['hotelid']));
		}

		// put the ID of the promotion that was deactivated in the success message
		$mainframe->enqueueMessage(str_replace('e4j.ok.', '', $rs));
		// redirect
		$mainframe->redirect('index.php?option=com_vikchannelmanager&task=bpromo');
	}
}
