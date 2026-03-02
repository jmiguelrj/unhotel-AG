<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

// import Joomla view library
jimport('joomla.application.component.view');

class VikChannelManagerViewinventory extends JViewUI
{
	public function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();

		VCM::load_css_js();
		VCM::load_complex_select();

		$app = JFactory::getApplication();
		$dbo = JFactory::getDbo();

		$module = VikChannelManager::getActiveModule(true);
		$module['params'] = (array) json_decode($module['params'], true);
		$module['settings'] = (array) json_decode($module['settings'], true);

		if ($module['uniquekey'] != VikChannelManagerConfig::TRIP_CONNECT) {
			$app->enqueueMessage('This page requires the channel TripConnect to be available and active.');
			$app->redirect('index.php?option=com_vikchannelmanager');
			$app->close();
		}

		/**
		 * Add support to multiple TripAdvisor (TripConnect) accounts.
		 * 
		 * @since 	1.9.10
		 */
		$active_account_id = $app->input->getString('active_account_id');
		$ta_account_id = (string) ($active_account_id ?: $module['params']['tripadvisorid'] ?? '');
		$multi_hotels = [];
		$q = "SELECT * FROM `#__vikchannelmanager_hotel_multi` WHERE `channel`=" . (int) $module['uniquekey'] . ";";
		$dbo->setQuery($q);
		foreach ($dbo->loadAssocList() as $multi_hotel) {
			$multi_hotel['hdata'] = !empty($multi_hotel['hdata']) ? ((array) json_decode($multi_hotel['hdata'], true)) : [];
			// push additional hotel record
			$multi_hotels[] = $multi_hotel;
		}

		// read rooms enabled for the current TA account ID
		$q = "SELECT * FROM `#__vikchannelmanager_tac_rooms` WHERE `account_id` IS NULL OR `account_id` = " . $dbo->q($ta_account_id) . ";";
		$dbo->setQuery($q);
		$rooms = $dbo->loadAssocList();

		// read the VikBooking room information
		$q = "SELECT `id`, `name`, `smalldesc`, `img` FROM `#__vikbooking_rooms` ORDER BY `name`;";
		$dbo->setQuery($q);
		$vb_rooms = $dbo->loadAssocList();

		for ($j = 0; $j < count($vb_rooms); $j++) {
			$found = false;
			for ($i = 0; $i < count($rooms) && !$found; $i++) {
				if ($rooms[$i]['id_vb_room'] == $vb_rooms[$j]['id']) {
					$found = true;
					$vb_rooms[$j]['tac_room_id'] = $rooms[$i]['id'];
					$vb_rooms[$j]['name'] = $rooms[$i]['name'];
					$vb_rooms[$j]['smalldesc'] = $rooms[$i]['desc'];
					$vb_rooms[$j]['img'] = substr($rooms[$i]['img'], strrpos($rooms[$i]['img'], DIRECTORY_SEPARATOR)+1);
					$vb_rooms[$j]['amenities'] = explode(',', $rooms[$i]['amenities']);
					$vb_rooms[$j]['codes'] = $rooms[$i]['codes'];
					$vb_rooms[$j]['cost'] = $rooms[$i]['cost'];
					$rooms[$i]['found'] = true;
				}
			}

			if (!$found) {
				$vb_rooms[$j]['tac_room_id'] = 0;
				$vb_rooms[$j]['amenities'] = array();
				$vb_rooms[$j]['codes'] = '';
				$vb_rooms[$j]['cost'] = number_format(VikChannelManager::getRoomRatesCost($vb_rooms[$j]['id']), 2, ".", "");
			}

			// always refresh url
			$itemid = null;

			/**
			 * @wponly 	we need to pass the rewritten version of the URL, as raw URLs won't work.
			 */
			if (VCMPlatformDetection::isWordPress()) {
				try {
					$model 	= JModel::getInstance('vikbooking', 'shortcodes', 'admin');
					$itemid = $model->best(array('vikbooking', 'roomslist', 'roomdetails'));
				} catch (Exception $e) {
					// do nothing
				}
			}

			if (($module['settings']['url_type']['value'] ?? '') == 'VCM_TA_URL_TYPE_ROOM') {
				if ($itemid) {
					$vb_rooms[$j]['url'] = JRoute::_("index.php?option=com_vikbooking&view=roomdetails&roomid={$vb_rooms[$j]['id']}&Itemid={$itemid}", false);
				} else {
					$vb_rooms[$j]['url'] = JUri::root().'index.php?option=com_vikbooking&view=roomdetails&roomid='.$vb_rooms[$j]['id'];
				}
			} else {
				if ($itemid) {
					$vb_rooms[$j]['url'] = JRoute::_("index.php?option=com_vikbooking&task=search&roomid={$vb_rooms[$j]['id']}&Itemid={$itemid}", false);
				} else {
					$vb_rooms[$j]['url'] = JUri::root().'index.php?option=com_vikbooking&task=search&roomid='.$vb_rooms[$j]['id'];
				}
			}
		}

		foreach ($rooms as $r) {
			if (empty($r['found'])) {
				$q = "DELETE FROM `#__vikchannelmanager_tac_rooms` WHERE `id`=".$r['id']." LIMIT 1;";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}

		$vb_rooms = $this->sortRooms($vb_rooms);

		$this->rooms = $vb_rooms;
		$this->module = $module;
		$this->multi_hotels = $multi_hotels;

		// Display the template (default.php)
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTROOMINVENTORY'), 'vikchannelmanager');
		JToolBarHelper::apply( 'saveRoomsInventory', JText::_('SAVE'));
		JToolBarHelper::spacer();
	}

	protected function sortRooms($rooms)
	{
		$arr_active = array();
		$arr_unactive = array();
		foreach ($rooms as $r) {
			if ($r['tac_room_id'] != 0) {
				$arr_active[count($arr_active)] = $r;
			} else {
				$arr_unactive[count($arr_unactive)] = $r;
			}
		}

		foreach ($arr_unactive as $r) {
			$arr_active[count($arr_active)] = $r;
		}

		return $arr_active;
	}
}
