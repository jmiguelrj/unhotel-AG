<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2023 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

// import Joomla view library
jimport('joomla.application.component.view');

class VikChannelManagerViewVrbolistings extends JViewUI
{
	public function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();

		VCM::load_css_js();

		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Settings'));
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$vbrooms = [];
		$q = "SELECT `vbr`.`id`,`vbr`.`name`,`vbr`.`img`,`vbr`.`smalldesc` FROM `#__vikbooking_rooms` AS `vbr` ORDER BY `vbr`.`name` ASC;";
		$dbo->setQuery($q);
		$vbrooms = $dbo->loadAssocList();
		if (!$vbrooms) {
			VikError::raiseWarning('', 'There are no rooms in VikBooking.');
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if ($channel['uniquekey'] != VikChannelManagerConfig::VRBOAPI) {
			VikError::raiseWarning('', 'Please activate the dedicated channel to Vrbo API.');
			$app->redirect("index.php?option=com_vikchannelmanager&task=config");
			exit;
		}
		$account_key = !empty($channel['params']) && !empty($channel['params']['hotelid']) ? $channel['params']['hotelid'] : VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::VRBOAPI, '');

		// load the information about the rate plans from rooms mapping
		$otarooms = [];
		$otalistings = [];
		$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=".VikChannelManagerConfig::VRBOAPI.";";
		$dbo->setQuery($q);
		$otarooms = $dbo->loadAssocList();
		if ($otarooms) {
			foreach ($otarooms as $otaroom) {
				$otalistings[$otaroom['idroomota']] = $otaroom['otaroomname'];
			}
		}

		// load data from database (one record per listing/room)
		$listings_data = [];
		$q = "SELECT `setting` FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `param`=" . $dbo->quote('listing_content') . " ORDER BY `last_updated` DESC, `id` DESC";
		$dbo->setQuery($q);
		$records = $dbo->loadAssocList();
		if ($records) {
			foreach ($records as $record) {
				$listing_data = json_decode($record['setting']);
				if (is_object($listing_data)) {
					// push listing object
					array_push($listings_data, $listing_data);
				}
			}
		}

		$this->channel = $channel;
		$this->listings_data = $listings_data;
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
		//Add menu title and some buttons to the page
		JToolBarHelper::title('Vrbo - ' . JText::_('VCMMENUAIRBMNGLST'), 'vikchannelmanager');
		
		/**
		 * We no longer permit to create listings from scratch.
		 * 
		 * @since 	1.8.24
		 */
		// JToolBarHelper::addNew('vrbolst.new', JText::_('VCM_MNGLISTING_NEW'));
		// JToolBarHelper::spacer();

		JToolBarHelper::cancel('vrbolst.cancel', JText::_('CANCEL'));
		JToolBarHelper::spacer();
	}
}
