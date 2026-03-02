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
		$mainframe = JFactory::getApplication();

		// configuration fields validation
		if (!function_exists('curl_init')) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Curl');
			exit;
		}

		$config = VikChannelManager::loadConfiguration();
		$validate = array('apikey');
		foreach ($validate as $v) {
			if (empty($config[$v])) {
				echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Settings');
				exit;
			}
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (!isset($channel['params']['hotelid']) || empty($channel['params']['hotelid']) || $channel['uniquekey'] != VikChannelManagerConfig::BOOKING) {
			echo 'e4j.error.Empty Hotel ID for Booking.com.';
			exit;
		}

		// promotion fields
		$promoid = VikRequest::getString('promoid', '', 'request');
		$name = VikRequest::getString('name', '', 'request');
		$type = VikRequest::getString('type', 'basic', 'request');
		$target_channel = VikRequest::getString('target_channel', 'public', 'request');
		$min_stay_through = VikRequest::getInt('min_stay_through', 0, 'request');
		$non_ref = VikRequest::getInt('non_ref', 0, 'request');
		$no_cc = VikRequest::getInt('no_cc', 0, 'request');

		// basic promo only
		$bookfromdate = VikRequest::getString('bookfromdate', '', 'request');
		$booktodate = VikRequest::getString('booktodate', '', 'request');
		$book_time = VikRequest::getInt('book_time', 0, 'request');
		$book_time_start = VikRequest::getInt('book_time_start', 0, 'request');
		$book_time_end = VikRequest::getInt('book_time_end', 0, 'request');
		// last minute promo only
		$last_minute_unit = VikRequest::getString('last_minute_unit', 'hour', 'request');
		$last_minute_days = VikRequest::getInt('last_minute_days', 0, 'request');
		$last_minute_hours = VikRequest::getInt('last_minute_hours', 0, 'request');
		// early booker promo only
		$early_booker_days = VikRequest::getInt('early_booker_days', 0, 'request');
		
		// stay dates are for all promos
		$stayfromdate = VikRequest::getString('stayfromdate', '', 'request');
		$staytodate = VikRequest::getString('staytodate', '', 'request');
		$wdays = VikRequest::getVar('wdays', array());
		$wdays = !count($wdays) || empty($wdays[0]) ? array() : $wdays;
		//excluded dates
		$pexcluded_dates = explode(';', VikRequest::getString('excluded_dates', '', 'request'));
		$excluded_dates = array();
		foreach ($pexcluded_dates as $v) {
			if (!empty($v) && strlen($v) == 10) {
				array_push($excluded_dates, $v);
			}
		}
		// rooms and rate plans
		$rooms = VikRequest::getVar('rooms', array());
		$rplans = VikRequest::getVar('rplans', array());
		// discount
		$discount = VikRequest::getInt('discount', 0, 'request');

		// validation for the creation and modification of the promotions
		if (empty($discount) || $discount <= 0) {
			echo 'e4j.error.Please enter a discount amount.';
			exit;
		}
		if (empty($name)) {
			$name = date('Y-m-d H:i:s') . ' -' . $discount . '%';
		}
		$name = htmlspecialchars((strlen($name) > 20 ? substr($name, 0, 20) : $name));
		if (!count($rooms) || empty($rooms[0])) {
			echo 'e4j.error.Please select at least one room.';
			exit;
		}
		if (!count($rplans) || empty($rplans[0])) {
			echo 'e4j.error.Please select at least one rate plan to discount.';
			exit;
		}
		if ($type == 'basic' && !empty($bookfromdate) && !empty($booktodate)) {
			$from = strtotime($bookfromdate);
			$to = strtotime($booktodate);
			if ($from > $to || empty($from)) {
				echo 'e4j.error.Invalid bookable dates.';
				exit;
			}
		}
		if (empty($stayfromdate) || empty($staytodate)) {
			echo 'e4j.error.Dates of stay cannot be empty.';
			exit;
		}
		$from = strtotime($stayfromdate);
		$to = strtotime($staytodate);
		if ($from > $to || empty($from)) {
			echo 'e4j.error.Invalid stay dates.';
			exit;
		}
		if ($updaction !== false && empty($promoid)) {
			// update (edit) must provide the Promotion ID
			echo 'e4j.error.Missing Promotion ID for update.';
			exit;
		}

		// make the request to e4jConnect to write the Promotion
			
		// required filter by hotel ID
		$filters = array('hotelid="'.$channel['params']['hotelid'].'"');
		// other filters
		if (!empty($promoid)) {
			$filters[] = 'action="'.$updaction.'"';
			$filters[] = 'promoid="'.$promoid.'"';
		} else {
			$filters[] = 'action="new"';
		}

		// promotion attributes
		$promo_attr = array();
		if (!empty($promoid)) {
			$promo_attr[] = 'id="'.$promoid.'"';
		}
		$promo_attr[] = 'name="'.$name.'"';
		$promo_attr[] = 'type="'.$type.'"';
		$promo_attr[] = 'target_channel="'.$target_channel.'"';
		if (!empty($min_stay_through) && $min_stay_through > 0) {
			$promo_attr[] = 'min_stay_through="'.$min_stay_through.'"';
		}
		$promo_attr[] = 'non_refundable="'.$non_ref.'"';
		$promo_attr[] = 'no_cc_promotion="'.$no_cc.'"';

		// conditional nodes
		$condit_nodes = array();
		if ($type == 'basic' && !empty($bookfromdate) && !empty($booktodate)) {
			array_push($condit_nodes, '<book_date start="'.$bookfromdate.'" end="'.$booktodate.'" />');
		}
		if ($type == 'basic' && $book_time > 0) {
			array_push($condit_nodes, '<book_time start="'.$book_time_start.'" end="'.$book_time_end.'" />');
		}
		if ($type == 'last_minute') {
			array_push($condit_nodes, '<last_minute unit="'.$last_minute_unit.'" value="'.($last_minute_unit == 'hour' ? $last_minute_hours : $last_minute_days).'" />');
		}
		if ($type == 'early_booker') {
			array_push($condit_nodes, '<early_booker value="'.$early_booker_days.'" />');
		}

		// stay dates can be a single node, or it contain children nodes for active_weekdays and excluded_dates
		$stay_dates = '<stay_date start="'.$stayfromdate.'" end="'.$staytodate.'"';
		if (count($wdays) || count($excluded_dates)) {
			// children nodes
			$stay_dates .= '>'."\n";
			if (count($wdays)) {
				$stay_dates .= '<active_weekdays>'."\n";
				foreach ($wdays as $wday) {
					$stay_dates .= '<active_weekday>'.$wday.'</active_weekday>'."\n";
				}
				$stay_dates .= '</active_weekdays>'."\n";
			}
			if (count($excluded_dates)) {
				$stay_dates .= '<excluded_dates>'."\n";
				foreach ($excluded_dates as $exd) {
					$stay_dates .= '<excluded_date>'.$exd.'</excluded_date>'."\n";
				}
				$stay_dates .= '</excluded_dates>'."\n";
			}
			// close node
			$stay_dates .= '</stay_date>';
		} else {
			// single node
			$stay_dates .= ' />';
		}

		// rooms and rate plans
		$rooms_rates = '<rooms>'."\n";
		foreach ($rooms as $r) {
			$rooms_rates .= '<room id="'.$r.'"/>'."\n";
		}
		$rooms_rates .= '</rooms>'."\n";
		$rooms_rates .= '<parent_rates>'."\n";
		foreach ($rplans as $r) {
			$rooms_rates .= '<parent_rate id="'.$r.'"/>'."\n";
		}
		$rooms_rates .= '</parent_rates>';

		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=wprom&c=".$channel['name'];
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager WPROM Request e4jConnect.com - '.ucwords($channel['name']).' -->
<WritePromotionRQ xmlns="http://www.e4jconnect.com/channels/wpromrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$config['apikey'].'"/>
	<Fetch '.implode(' ', $filters).'/>
	<WritePromotion '.implode(' ', $promo_attr).'>
		'.implode("\n", $condit_nodes).'
		'.$stay_dates.'
		'.$rooms_rates.'
		<discount value="'.$discount.'" />
	</WritePromotion>
</WritePromotionRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();

		if ($e4jC->getErrorNo()) {
			echo 'e4j.error.'.(@curl_error($e4jC->getCurlHeader()));
			exit;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap($rs);
			exit;
		}
		if (strpos($rs, 'e4j.ok') === false) {
			echo 'e4j.error.Invalid response received. '.$rs;
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

		$config = VikChannelManager::loadConfiguration();
		$validate = array('apikey');
		foreach ($validate as $v) {
			if (empty($config[$v])) {
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Settings'));
				$mainframe->redirect('index.php?option=com_vikchannelmanager&task=bpromo');
				exit;
			}
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
	<Api key="'.$config['apikey'].'"/>
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

		// put the ID of the promotion that was deactivated in the success message
		$mainframe->enqueueMessage(str_replace('e4j.ok.', '', $rs));
		// redirect
		$mainframe->redirect('index.php?option=com_vikchannelmanager&task=bpromo');
	}
}
