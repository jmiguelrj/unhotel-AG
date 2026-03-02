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

/**
 * The use of this View has been deprecated.
 * 
 * @deprecated 	1.8.3
 */
class VikChannelManagerViewroomsrar extends JViewUI {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();
		
		VCM::load_css_js();
		
		$dbo = JFactory::getDbo();

		$channel = VikChannelManager::getActiveModule(true);
		
		$q = "SELECT `vcmr`.*,`vbr`.`name`,`vbr`.`img`,`vbr`.`smalldesc` FROM `#__vikchannelmanager_roomsxref` AS `vcmr` LEFT JOIN `#__vikbooking_rooms` `vbr` ON `vcmr`.`idroomvb`=`vbr`.`id` WHERE `vcmr`.`idchannel`=".$channel['uniquekey']." ORDER BY `vbr`.`name` ASC, `vcmr`.`channel` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$roomsxref = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : array();
	
		$config = VikChannelManager::loadConfiguration();
		
		$this->config = $config;
		$this->rows = $roomsxref;
		$this->channel = $channel;
		
		// Display the template (default.php)
		parent::display($tpl);
		
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTROOMSRAR'), 'vikchannelmanager');
		JToolBarHelper::custom('sendrar', 'apply', 'apply', JText::_('VCMUPDRATESCHANNEL'), true);
		JToolBarHelper::spacer();
		JToolBarHelper::cancel('cancelsynch', JText::_('CANCEL'));
		
	}
}
