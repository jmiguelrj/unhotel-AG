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

class VikChannelManagerViewExpediaproducts extends JViewUI
{
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		VCM::load_css_js();

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

		// listings retrieve counter
		$retrieve_count = (int)$session->get('vcmExpediaLstRetCount', 0);

		// load property data from database, if anything available
		$property_data = new stdClass;
		$q = "SELECT `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `param`=" . $dbo->quote('hotel_content');
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$property_data = json_decode($dbo->loadResult());
			$property_data = is_object($property_data) ? $property_data : (new stdClass);
		}

		// load data from database (one record per listing/room)
		$listings_data = [];
		$q = "SELECT `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `param`=" . $dbo->quote('room_content') . " ORDER BY `id` DESC";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$records = $dbo->loadAssocList();
			foreach ($records as $record) {
				$listing_data = json_decode($record['setting']);
				if (is_object($listing_data)) {
					// push listing object
					array_push($listings_data, $listing_data);
				}
			}
		}

		$this->channel = $channel;
		$this->property_data = $property_data;
		$this->listings_data = $listings_data;
		$this->retrieve_count = $retrieve_count;
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
		//Add menu title and some buttons to the page
		JToolBarHelper::title('Expedia - ' . JText::_('VCMMENUBCONTAPI'), 'vikchannelmanager');
		JToolBarHelper::addNew('expediaproduct.new', JText::_('VCM_MNGPRODUCT_NEW'));
		JToolBarHelper::spacer();
		JToolBarHelper::cancel('cancel', JText::_('CANCEL'));
		JToolBarHelper::spacer();
		
	}
}
