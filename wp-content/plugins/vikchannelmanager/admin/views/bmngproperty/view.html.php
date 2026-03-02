<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2023 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

// import Joomla view library
jimport('joomla.application.component.view');

class VikChannelManagerViewBmngproperty extends JViewUI
{
	function display($tpl = null) {
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
		if (empty($channel['params']['hotelid']) || $channel['uniquekey'] != VikChannelManagerConfig::BOOKING) {
			VikError::raiseWarning('', 'Empty Hotel ID for Booking.');
			$app->redirect("index.php?option=com_vikchannelmanager&task=config");
			exit;
		}
		$account_key = $channel['params']['hotelid'];

		// load the information about the rate plans from rooms mapping
		$otalistings = [];
		$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=".VikChannelManagerConfig::BOOKING.";";
		$dbo->setQuery($q);
		$otarooms = $dbo->loadAssocList();
		foreach ($otarooms as $otaroom) {
			$otalistings[$otaroom['idroomota']] = $otaroom['otaroomname'];
		}

		// listings retrieve counter
		$retrieve_count = (int)$session->get('vcmBookingPropdetRetCount', 0);

		// load property data from database, if anything available
		$property_data = new stdClass;

		$q = $dbo->getQuery(true)
			->select($dbo->qn('setting'))
			->from($dbo->qn('#__vikchannelmanager_otarooms_data'))
			->where($dbo->qn('idchannel') . ' = ' . (int)$channel['uniquekey'])
			->where($dbo->qn('account_key') . ' = ' . $dbo->q($account_key))
			->where($dbo->qn('param') . ' = ' . $dbo->q('hotel_content'))
			->order($dbo->qn('id') . ' DESC');

		$dbo->setQuery($q, 0, 1);
		$prop_settings = $dbo->loadResult();
		if ($prop_settings) {
			$property_data = json_decode($prop_settings);
			$property_data = is_object($property_data) ? $property_data : (new stdClass);
		}

		$this->channel 		  = $channel;
		$this->property_data  = $property_data;
		$this->retrieve_count = $retrieve_count;
		$this->otarooms 	  = $otarooms;
		$this->otalistings 	  = $otalistings;
		$this->vbrooms 		  = $vbrooms;

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		// add menu title and some buttons to the page
		JToolBarHelper::title('Booking.com - ' . JText::_('VCM_BCOM_PROP_DETAILS'), 'vikchannelmanager');
		JToolBarHelper::apply('bproperty.save', JText::_('SAVE'));
		JToolBarHelper::save('bproperty.saveclose', JText::_('SAVECLOSE'));
		JToolBarHelper::spacer();
		JToolBarHelper::cancel('cancel', JText::_('CANCEL'));
		JToolBarHelper::spacer();
	}
}
