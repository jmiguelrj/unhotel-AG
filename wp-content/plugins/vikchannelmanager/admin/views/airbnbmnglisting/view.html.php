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

class VikChannelManagerViewAirbnbmnglisting extends JViewUI {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		VCM::load_css_js();
		VCM::load_complex_select();

		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();
		$mainframe = JFactory::getApplication();
		
		if (!function_exists('curl_init')) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Curl'));
			$mainframe->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}
		
		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Settings'));
			$mainframe->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}
		
		$vbrooms = array();
		$q = "SELECT `vbr`.`id`,`vbr`.`name`,`vbr`.`img`,`vbr`.`smalldesc` FROM `#__vikbooking_rooms` AS `vbr` ORDER BY `vbr`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$vbrooms = $dbo->loadAssocList();
		} else {
			VikError::raiseWarning('', 'There are no rooms in VikBooking.');
			$mainframe->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (empty($channel['params']['user_id'])) {
			VikError::raiseWarning('', 'Empty User ID for Airbnb.');
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=config");
			exit;
		}
		$account_key = $channel['params']['user_id'];

		// load the information about the rate plans from rooms mapping
		$otarooms = array();
		$otalistings = array();
		$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=".VikChannelManagerConfig::AIRBNBAPI.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$otarooms = $dbo->loadAssocList();
			foreach ($otarooms as $otaroom) {
				$otalistings[$otaroom['idroomota']] = $otaroom['otaroomname'];
			}
		}

		// load record from database (if editing)
		$listing = new stdClass;
		$idroomota = VikRequest::getString('idroomota', '', 'request');
		
		if (!empty($idroomota)) {
			$q = "SELECT `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($idroomota) . " AND `param`=" . $dbo->quote('listing_content');
			$dbo->setQuery($q, 0, 1);
			$dbo->execute();
			if (!$dbo->getNumRows()) {
				// record not found
				VikError::raiseWarning('', 'Listing not found');
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
				exit;
			}
			$listing = json_decode($dbo->loadResult());
			if (!is_object($listing)) {
				// could not decode record
				VikError::raiseWarning('', 'Listing record is broken');
				$mainframe->redirect("index.php?option=com_vikchannelmanager&task=airbnblistings");
				exit;
			}
		}

		// make sure the class JObject is available
		if (!class_exists('JObject')) {
			try {
				JLoader::import('adapter.application.object');
			} catch (Exception $e) {
				// do nothing
			}
		}

		$this->channel = $channel;
		$this->listing = $listing;
		$this->otarooms = $otarooms;
		$this->otalistings = $otalistings;
		$this->vbrooms = $vbrooms;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		$idroomota = VikRequest::getString('idroomota', '', 'request');
		
		if (!empty($idroomota)) {
			// edit
			JToolBarHelper::title('Airbnb - ' . JText::_('VCM_MNGLISTING_EDIT'), 'vikchannelmanager');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikchannelmanager')) {
				JToolBarHelper::apply('airbnblst.updatelisting_stay', JText::_('SAVE'));
				JToolBarHelper::save('airbnblst.updatelisting', JText::_('SAVECLOSE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel('cancel_airbnbmnglisting', JText::_('BACK'));
			JToolBarHelper::spacer();
		} else {
			// new
			JToolBarHelper::title('Airbnb - ' . JText::_('VCM_MNGLISTING_NEW'), 'vikchannelmanager');
			if (JFactory::getUser()->authorise('core.create', 'com_vikchannelmanager')) {
				JToolBarHelper::save('airbnblst.savelisting', JText::_('SAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel('cancel_airbnbmnglisting', JText::_('BACK'));
			JToolBarHelper::spacer();
		}
	}
}
