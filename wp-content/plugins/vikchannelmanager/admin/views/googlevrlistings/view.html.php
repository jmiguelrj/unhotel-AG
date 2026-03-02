<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

// import Joomla view library
jimport('joomla.application.component.view');

class VikChannelManagerViewGooglevrlistings extends JViewUI
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
			$app->close();
		}

		$vbrooms = [];
		$q = "SELECT `vbr`.`id`,`vbr`.`name`,`vbr`.`img`,`vbr`.`smalldesc` FROM `#__vikbooking_rooms` AS `vbr` ORDER BY `vbr`.`name` ASC;";
		$dbo->setQuery($q);
		$vbrooms = $dbo->loadAssocList();
		if (!$vbrooms) {
			VikError::raiseWarning('', 'There are no rooms in VikBooking.');
			$app->redirect("index.php?option=com_vikchannelmanager");
			$app->close();
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if ($channel['uniquekey'] != VikChannelManagerConfig::GOOGLEVR) {
			VikError::raiseWarning('', 'Please activate the dedicated channel to Google Vacation Rentals.');
			$app->redirect("index.php?option=com_vikchannelmanager&task=config");
			$app->close();
		}

		// account key
		$account_key = !empty($channel['params']['hotelid']) ? $channel['params']['hotelid'] : VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::GOOGLEVR, '');

		// load the information about the rate plans from rooms mapping
		$otalistings = [];
		$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . VikChannelManagerConfig::GOOGLEVR . ";";
		$dbo->setQuery($q);
		$otarooms = $dbo->loadAssocList();
		foreach ($otarooms as $otaroom) {
			$otalistings[$otaroom['idroomota']] = $otaroom['otaroomname'];
		}

		// load data from database (one record per listing/room)
		$listings_data = [];

		$q = $dbo->getQuery(true)
			->select($dbo->qn('setting'))
			->from($dbo->qn('#__vikchannelmanager_otarooms_data'))
			->where($dbo->qn('idchannel') . ' = ' . (int) $channel['uniquekey'])
			->where($dbo->qn('param') . ' = ' . $dbo->q('listing_content'))
			->order($dbo->qn('last_updated') . ' DESC')
			->order($dbo->qn('id') . ' DESC');

		if (!empty($account_key)) {
			$q->where($dbo->qn('account_key') . ' = ' . $dbo->q($account_key));
		}

		$dbo->setQuery($q);
		$records = $dbo->loadAssocList();

		foreach ($records as $record) {
			$listing_data = json_decode($record['setting']);
			if (is_object($listing_data)) {
				// push listing object
				array_push($listings_data, $listing_data);
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
		// Add menu title and some buttons to the page
		JToolBarHelper::title('Google Vacation Rentals - ' . JText::_('VCMMENUAIRBMNGLST'), 'vikchannelmanager');

		JToolBarHelper::cancel('googlevr.cancel', JText::_('CANCEL'));
		JToolBarHelper::spacer();
	}
}
