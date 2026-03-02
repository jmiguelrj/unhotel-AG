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

class VikChannelManagerViewrooms extends JViewUI
{
	public function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();

		VCM::load_css_js();

		$dbo = JFactory::getDbo();

		$q = "SELECT `vcmr`.*,`vbr`.`name`,`vbr`.`img`,`vbr`.`smalldesc` FROM `#__vikchannelmanager_roomsxref` AS `vcmr` LEFT JOIN `#__vikbooking_rooms` `vbr` ON `vcmr`.`idroomvb`=`vbr`.`id` ORDER BY `vbr`.`name` ASC, `vcmr`.`channel` ASC;";
		$dbo->setQuery($q);
		$roomsxrefmap = $dbo->loadAssocList();

		$q = "SELECT `vcmr`.*,`vbr`.`name`,`vbr`.`img`,`vbr`.`smalldesc` FROM `#__vikchannelmanager_roomsxref` AS `vcmr` LEFT JOIN `#__vikbooking_rooms` `vbr` ON `vcmr`.`idroomvb`=`vbr`.`id` ORDER BY `vcmr`.`channel` ASC, `vbr`.`name` ASC;";
		$dbo->setQuery($q);
		$roomsxref = $dbo->loadAssocList();

		/**
		 * Check for conflicting rooms relations one-to-multi
		 * 
		 * @since 	1.6.13
		 */
		$ibe_otas 	= array();
		$otas_ibe 	= array();
		foreach ($roomsxref as $xref) {
			$ibe_key = $xref['idroomvb'];
			$ota_key = $xref['idchannel'] . '_' . $xref['idroomota'];
			$ota_nm  = $xref['channel'];
			if (!isset($ibe_otas[$ibe_key])) {
				$ibe_otas[$ibe_key] = array();
			}
			if (!isset($ibe_otas[$ibe_key][$ota_nm])) {
				$ibe_otas[$ibe_key][$ota_nm] = 0;
			}
			if (!isset($otas_ibe[$ota_key])) {
				$otas_ibe[$ota_key] = array();
			}
			$ibe_otas[$ibe_key][$ota_nm]++;
			array_push($otas_ibe[$ota_key], $ibe_key);
		}
		// OTAs rooms should be mapped to just one corresponding VBO room
		foreach ($otas_ibe as $ota_key => $ota_rooms) {
			if (count($ota_rooms) > 1) {
				// this OTA room is linked to more than one VBO rooms
				list($idchannel, $idroomota) = explode('_', $ota_key);
				foreach ($roomsxref as $xref) {
					if ((string)$xref['idchannel'] == $idchannel && (string)$xref['idroomota'] == $idroomota) {
						$idchannel = ucfirst($xref['channel']);
						$idroomota = $xref['otaroomname'];
						break;
					}
				}
				// raise warning and continue
				VikError::raiseWarning('', JText::sprintf('VCMRMAPCONFLRELOTA', $idchannel, $idroomota));
				continue;
			}
		}
		// Website rooms should be mapped to just one corresponding room per channel
		foreach ($ibe_otas as $idroomvb => $chs) {
			foreach ($chs as $chname => $chcount) {
				if ($chcount > 1) {
					// this website room is linked to more than one room of this channel
					$rname = $idroomvb;
					foreach ($roomsxref as $xref) {
						if ((string)$xref['idroomvb'] == $idroomvb) {
							$rname = $xref['name'];
							break;
						}
					}
					// raise warning and continue
					VikError::raiseWarning('', JText::sprintf('VCMRMAPCONFLRELIBE', $rname, ucfirst($chname)));
					continue;
				}
			}
		}

		$first_summary = 0;
		$module = VikChannelManager::getActiveModule(true);
		/**
		 * When saving the rooms mapping, checkfs=1 is passed via query string
		 * in the redirection to this View. This is to check if there are multiple
		 * Hotel IDs/Accounts mapped, to re-prompt to import the active bookings.
		 * 
		 * @since 	1.6.13
		 */
		$checkfs = VikRequest::getInt('checkfs', 0, 'request');
		//
		if (in_array($module['uniquekey'], array(VikChannelManagerConfig::BOOKING, VikChannelManagerConfig::EXPEDIA, VikChannelManagerConfig::AIRBNBAPI))) {
			$first_summary = VikChannelManager::checkFirstBookingSummary($module['uniquekey'], -1, $checkfs);
		}

		$this->rowsmap = $roomsxrefmap;
		$this->rows = $roomsxref;
		$this->first_summary = $first_summary;
		
		// Display the template (default.php)
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTROOMSLIST'), 'vikchannelmanager');

		JToolBarHelper::deleteList(JText::_('VCMREMOVECONFIRM'), 'removeroomsxref', JText::_('REMOVE'));
	}
}
