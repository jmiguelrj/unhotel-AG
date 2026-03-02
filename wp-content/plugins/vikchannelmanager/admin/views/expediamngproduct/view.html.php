<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2022 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

// import Joomla view library
jimport('joomla.application.component.view');

class VikChannelManagerViewExpediamngproduct extends JViewUI
{
	public function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();

		VCM::load_css_js();
		VCM::load_complex_select();

		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();
		$session = JFactory::getSession();

		if (!function_exists('curl_init')) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Curl'));
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Settings'));
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$vbrooms = [];
		$q = "SELECT `vbr`.`id`,`vbr`.`name`,`vbr`.`img`,`vbr`.`smalldesc` FROM `#__vikbooking_rooms` AS `vbr` ORDER BY `vbr`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$vbrooms = $dbo->loadAssocList();
		} else {
			VikError::raiseWarning('', 'There are no rooms in VikBooking.');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (empty($channel['params']['hotelid']) || $channel['uniquekey'] != VikChannelManagerConfig::EXPEDIA) {
			VikError::raiseWarning('', 'Empty Hotel ID for Expedia.');
			$app->redirect("index.php?option=com_vikchannelmanager&task=config");
			exit;
		}
		$account_key = $channel['params']['hotelid'];

		// load the information about the rate plans from rooms mapping
		$otarooms = [];
		$otalistings = [];
		$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=".VikChannelManagerConfig::EXPEDIA.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$otarooms = $dbo->loadAssocList();
			foreach ($otarooms as $otaroom) {
				$otalistings[$otaroom['idroomota']] = $otaroom['otaroomname'];
			}
		}

		// load property data from database, if anything available
		$property_data = new stdClass;
		$q = "SELECT `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `param`=" . $dbo->quote('hotel_content');
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$property_data = json_decode($dbo->loadResult());
			$property_data = is_object($property_data) ? $property_data : (new stdClass);
		}

		// load record from database (if editing)
		$listing = new stdClass;
		$idroomota = VikRequest::getString('idroomota', '', 'request');
		
		if (!empty($idroomota)) {
			$q = "SELECT `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($idroomota) . " AND `param`=" . $dbo->quote('room_content');
			$dbo->setQuery($q, 0, 1);
			$dbo->execute();
			if (!$dbo->getNumRows()) {
				// record not found
				VikError::raiseWarning('', 'Room not found');
				$app->redirect("index.php?option=com_vikchannelmanager&task=expediaproducts");
				exit;
			}
			$listing = json_decode($dbo->loadResult());
			if (!is_object($listing)) {
				// could not decode record
				VikError::raiseWarning('', 'Room record is broken');
				$app->redirect("index.php?option=com_vikchannelmanager&task=expediaproducts");
				exit;
			}
		}

		$this->channel = $channel;
		$this->property_data = $property_data;
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
	protected function addToolBar()
	{
		$idroomota = VikRequest::getString('idroomota', '', 'request');
		
		if (!empty($idroomota)) {
			// edit
			JToolBarHelper::title('Expedia - ' . JText::_('VCM_MNGPRODUCT_EDIT'), 'vikchannelmanager');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikchannelmanager')) {
				JToolBarHelper::apply('expediaproduct.updatelisting_stay', JText::_('SAVE'));
				JToolBarHelper::save('expediaproduct.updatelisting', JText::_('SAVECLOSE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel('expediaproduct.cancel', JText::_('BACK'));
			JToolBarHelper::spacer();
		} else {
			// new
			JToolBarHelper::title('Expedia - ' . JText::_('VCM_MNGPRODUCT_NEW'), 'vikchannelmanager');
			if (JFactory::getUser()->authorise('core.create', 'com_vikchannelmanager')) {
				JToolBarHelper::save('expediaproduct.savelisting', JText::_('SAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel('expediaproduct.cancel', JText::_('BACK'));
			JToolBarHelper::spacer();
		}
	}
}
