<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2022 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.mvc.controllers.admin');

class VikChannelManagerControllerGhotel extends JControllerAdmin
{
	/**
	 * Triggers the Google Travel Partner API request Set LiveOnGoogle.
	 * 
	 * @param 	int 	$force_mode 	0 for turning off the status or 1 to turn it on.
	 * 
	 * @return 	mixed 					void by redirecting, or boolean if return=true.
	 */
	public function toggle_live_on_google($force_mode = null)
	{
		$app = JFactory::getApplication();

		// invoke the VCMGhotelTravel helper class
		$gtravel = new VCMGhotelTravel();

		$set_live_status = false;
		if (!is_null($force_mode)) {
			$set_live_status = (int)$force_mode;
		} else {
			$current_live_status = $gtravel->getLiveStatus();
			if ($current_live_status !== false) {
				$set_live_status = (int)(!$current_live_status);
			}
		}

		// make sure the hotel inventory ID has been received
		$hinv_id = $gtravel->getPropertyID();
		if (empty($hinv_id) || $set_live_status === false) {
			$app->enqueueMessage(JText::_('VCM_NO_HOTELDETAILS'), 'error');
			$app->redirect('index.php?option=com_vikchannelmanager');
			$app->close();
		}

		// build the JSON request body
		$rq_body = new stdClass;
		$rq_body->api_key  	  = VikChannelManager::getApiKey(true);
		$rq_body->property_id = $hinv_id;
		$rq_body->notify_url  = JUri::root();
		$rq_body->rq_type 	  = 'setLiveOnGoogle';
		$rq_body->live_status = $set_live_status;

		// execute the JSON request
		$e4jc_url = "https://slave.e4jconnect.com/google-hotel/travel-partner";

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields(json_encode($rq_body));
		$e4jC->setHttpHeader(['Content-Type: application/json']);
		$e4jC->setTimeout(600);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();

		if ($e4jC->getErrorNo()) {
			$app->enqueueMessage('cURL error: ' . @curl_error($e4jC->getCurlHeader()), 'error');
			$app->redirect('index.php?option=com_vikchannelmanager');
			$app->close();
		}

		// decode the response
		$result = json_decode($rs);
		if (!is_object($result) || !$result->status) {
			$app->enqueueMessage('(' . $e4jC->getResultInfo('http_code', 0) . ') ' . (is_object($result) ? $result->error : 'Invalid response'), 'error');
			$app->redirect('index.php?option=com_vikchannelmanager');
			$app->close();
		}

		// request was successful, update the internal data status for the scorecard
		$gtravel->updateLiveStatus($set_live_status);

		$app->enqueueMessage(JText::_('MSG_BASE_SUCCESS'));
		$app->redirect('index.php?option=com_vikchannelmanager');
		$app->close();
	}
}
