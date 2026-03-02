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

class VikChannelManagerViewVrbomnglisting extends JViewUI
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

		// requested ID for editing
		$idroomota = VikRequest::getString('idroomota', '', 'request');

		// original listing in Vik Booking
		$vbo_listing = new stdClass;

		// Vik Booking rate plans for this listing (if any)
		$vbo_listing_rplans = [];

		// load record from database (if editing)
		$listing = new stdClass;

		if (!empty($idroomota)) {
			$q = "SELECT * FROM `#__vikchannelmanager_otarooms_data` WHERE `idchannel`=" . (int)$channel['uniquekey'] . " AND `account_key`=" . $dbo->quote($account_key) . " AND `idroomota`=" . $dbo->quote($idroomota) . " AND `param`=" . $dbo->quote('listing_content');
			$dbo->setQuery($q, 0, 1);
			$listing_record = $dbo->loadObject();
			if (!$listing_record) {
				// record not found
				VikError::raiseWarning('', 'Room not found');
				$app->redirect("index.php?option=com_vikchannelmanager&view=vrbolistings");
				$app->close();
			}

			$listing = json_decode($listing_record->setting);
			if (!is_object($listing)) {
				// could not decode record
				VikError::raiseWarning('', 'Room record is broken');
				$app->redirect("index.php?option=com_vikchannelmanager&view=vrbolistings");
				$app->close();
			}

			// load VBO listing data
			$q = "SELECT * FROM `#__vikbooking_rooms` WHERE `id`=" . $dbo->quote($listing_record->idroomota);
			$dbo->setQuery($q, 0, 1);
			$vbo_listing_data = $dbo->loadObject();
			if ($vbo_listing_data) {
				$vbo_listing = $vbo_listing_data;

				// load the rate plans for this room
				$q = "SELECT `p`.`name`, `r`.`idprice` FROM `#__vikbooking_dispcost` AS `r`
					LEFT JOIN `#__vikbooking_prices` AS `p` ON `r`.`idprice`=`p`.`id`
					WHERE `r`.`idroom`={$vbo_listing_data->id} AND `r`.`cost` > 0 AND `p`.`name` IS NOT NULL
					GROUP BY `p`.`name`, `r`.`idprice`
					ORDER BY `r`.`idprice` ASC;";
				$dbo->setQuery($q);
				$vbo_listing_rplans = $dbo->loadAssocList();
			}
		}

		// load countries
		$q = "SELECT * FROM `#__vikbooking_countries` ORDER BY `#__vikbooking_countries`.`country_name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$countries = $dbo->loadAssocList();

		$this->channel = $channel;
		$this->listing = $listing;
		$this->vbo_listing = $vbo_listing;
		$this->vbo_listing_rplans = $vbo_listing_rplans;
		$this->otarooms = $otarooms;
		$this->otalistings = $otalistings;
		$this->countries = $countries;
		
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
			JToolBarHelper::title('Vrbo - ' . JText::_('VCM_MNGLISTING_EDIT'), 'vikchannelmanager');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikchannelmanager')) {
				JToolBarHelper::apply('vrbolst.updatelisting_stay', JText::_('SAVE'));
				JToolBarHelper::save('vrbolst.updatelisting', JText::_('SAVECLOSE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel('vrbolst.cancel', JText::_('BACK'));
			JToolBarHelper::spacer();
		} else {
			// new
			JToolBarHelper::title('Vrbo - ' . JText::_('VCM_MNGLISTING_NEW'), 'vikchannelmanager');
			if (JFactory::getUser()->authorise('core.create', 'com_vikchannelmanager')) {
				JToolBarHelper::save('vrbolst.savelisting', JText::_('SAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel('vrbolst.cancel', JText::_('BACK'));
			JToolBarHelper::spacer();
		}
	}
}
