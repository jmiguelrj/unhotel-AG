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

class VikChannelManagerViewbcahsummary extends JViewUI {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();
		
		VCM::load_css_js();
		
		$dbo = JFactory::getDbo();

		$module = VikChannelManager::getActiveModule(true);
	
		$config = VikChannelManager::loadConfiguration();

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);

		$hotelData = array();

		$e4j_debug = VikRequest::getInt('e4j_debug');

		if($module['av_enabled'] == 1) {
			$q = "SELECT `prop_params`,`prop_name` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=".(int)$module['uniquekey'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			if( $dbo->getNumRows() > 0 ) {
				$databaseData = $dbo->loadAssocList();
				foreach ($databaseData as $hotel) {
					$hotelID = json_decode($hotel['prop_params'],true)['hotelid'];
					$hotelName = $hotel['prop_name'];
					$hotelData[$hotelID] = $hotelName;
				}
			}
		}

		$session = JFactory::getSession();
		if($session->has('vcmbcahsum')){
			$sessionValues = json_decode($session->get('vcmbcahsum'),true);
		}

		$this->config = $config;
		$this->hotelData = $hotelData;
		$this->sessionValues = $sessionValues;
		$this->e4j_debug = $e4j_debug;
		
		// Display the template (default.php)
		parent::display($tpl);
		
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTBCAHSUM'), 'vikchannelmanager');
		JToolBarHelper::cancel( 'cancel', JText::_('CANCEL'));
		JToolBarHelper::spacer();	
	}
}
