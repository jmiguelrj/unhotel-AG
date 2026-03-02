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

class VikChannelManagerViewtrinventory extends JViewUI {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();
		
		VCM::load_css_js();
		
		$dbo = JFactory::getDbo();
		
		$rooms = array();
		
		$module = VikChannelManager::getActiveModule(true);
		$module['settings'] = json_decode($module['settings'], true);
		
		$q = "SELECT * FROM `#__vikchannelmanager_tri_rooms`;";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$rooms = $dbo->loadAssocList();
		}
		
		$vb_rooms = array();
		
		$q = "SELECT `id`, `name`, `smalldesc`, `img` FROM `#__vikbooking_rooms` ORDER BY `name`;";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$vb_rooms = $dbo->loadAssocList();
		}
		
		for( $j = 0; $j < count($vb_rooms); $j++ ) {
			$found = false;
			for( $i = 0; $i < count($rooms) && !$found; $i++ ) {
				if( $rooms[$i]['id_vb_room'] == $vb_rooms[$j]['id'] ) {
					$found = true;
					$vb_rooms[$j]['tri_room_id'] = $rooms[$i]['id'];
					$vb_rooms[$j]['name'] = $rooms[$i]['name'];
					$vb_rooms[$j]['smalldesc'] = $rooms[$i]['desc'];
					$vb_rooms[$j]['img'] = substr($rooms[$i]['img'], strrpos($rooms[$i]['img'], DS)+1);
					$vb_rooms[$j]['codes'] = $rooms[$i]['codes'];
					$vb_rooms[$j]['cost'] = $rooms[$i]['cost'];
					$rooms[$i]['found'] = true;
				}
				
			}
			
			if( !$found ) {
				$vb_rooms[$j]['tri_room_id'] = 0;
				$vb_rooms[$j]['codes'] = '';
				$vb_rooms[$j]['cost'] = number_format(VikChannelManager::getRoomRatesCost($vb_rooms[$j]['id']), 2, ".", "");
			}

			// always refresh url
			/**
			 * @wponly 	we need to pass the rewritten version of the URL, as raw URLs won't work.
			 */
			$itemid = null;
			if (VCMPlatformDetection::isWordPress()) {
				try {
					$model 	= JModel::getInstance('vikbooking', 'shortcodes', 'admin');
					$itemid = $model->best(array('vikbooking', 'roomslist', 'roomdetails'));
				} catch (Exception $e) {
					// do nothing
				}
			}
			if ($module['settings']['url_type']['value'] == 'VCM_TRI_URL_TYPE_ROOM') {
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
		
		foreach( $rooms as $r ) {
			if( empty($r['found']) ) {
				$q = "DELETE FROM `#__vikchannelmanager_tri_rooms` WHERE `id`=".$r['id']." LIMIT 1;";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		
		$vb_rooms = $this->sortRooms($vb_rooms);
		
		$this->rooms = $vb_rooms;
		
		// Display the template (default.php)
		parent::display($tpl);
		
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTROOMINVENTORY'), 'vikchannelmanager');
		JToolBarHelper::apply( 'saveTrivagoRoomsInventory', JText::_('SAVE'));
		JToolBarHelper::spacer();
		
	}
	
	protected function sortRooms($rooms) {
		$arr_active = array();
		$arr_unactive = array();
		foreach( $rooms as $r ) {
			if( $r['tri_room_id'] != 0 ) {
				$arr_active[count($arr_active)] = $r;
			} else {
				$arr_unactive[count($arr_unactive)] = $r;
			}
		}
		
		foreach( $arr_unactive as $r ) {
			$arr_active[count($arr_active)] = $r;
		}
		
		return $arr_active;
	}
}
