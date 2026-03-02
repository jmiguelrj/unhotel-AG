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

class VikChannelManagerViewcustoma extends JViewUI {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();
		
		VCM::load_css_js();
		
		$dbo = JFactory::getDbo();
		
		$otarooms = "";
		//query modified for sql strict mode
		//SELECT * FROM `#__vikchannelmanager_roomsxref` GROUP BY `#__vikchannelmanager_roomsxref`.`idroomota`, `#__vikchannelmanager_roomsxref`.`idchannel` ORDER BY `#__vikchannelmanager_roomsxref`.`channel` ASC, `#__vikchannelmanager_roomsxref`.`otaroomname` ASC;
		$q = "SELECT MIN(`r`.`id`) AS `id`, MIN(`r`.`idroomvb`) AS `idroomvb`, `r`.`idroomota`, `r`.`idchannel`, MIN(`r`.`channel`) AS `channel`, MIN(`r`.`otaroomname`) AS `otaroomname`, MIN(`r`.`otapricing`) AS `otapricing`, MIN(`r`.`prop_name`) AS `prop_name`, MIN(`r`.`prop_params`) AS `prop_params` FROM `#__vikchannelmanager_roomsxref` AS `r` GROUP BY `r`.`idroomota`, `r`.`idchannel` ORDER BY `r`.`channel` ASC, `r`.`otaroomname` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$otarooms = $dbo->loadAssocList();
		}
		
		$this->otarooms = $otarooms;
		
		// Display the template (default.php)
		parent::display($tpl);
		
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTCUSTOMA'), 'vikchannelmanager');
		JToolBarHelper::custom( 'confirmcustoma', 'refresh', 'refresh', JText::_('VCMTLBCUSTOMA'), false, false);
		JToolBarHelper::spacer();
		JToolBarHelper::cancel( 'cancel', JText::_('CANCEL'));
		JToolBarHelper::spacer();
		
	}
}
