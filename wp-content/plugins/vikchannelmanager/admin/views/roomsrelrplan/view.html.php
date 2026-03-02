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

class VikChannelManagerViewroomsrelrplan extends JViewUI {
	
	function display($tpl = null) {
		
		$dbo = JFactory::getDbo();

		$relid = VikRequest::getInt('relid', '', 'request');
		
		$q = "SELECT `vcmr`.*,`vbr`.`name`,`vbr`.`img`,`vbr`.`smalldesc` FROM `#__vikchannelmanager_roomsxref` AS `vcmr` LEFT JOIN `#__vikbooking_rooms` `vbr` ON `vcmr`.`idroomvb`=`vbr`.`id` WHERE `vcmr`.`id`=".(int)$relid." ORDER BY `vbr`.`name` ASC, `vcmr`.`channel` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$roomsrel = $dbo->getNumRows() > 0 ? $dbo->loadAssoc() : array();

		$this->roomsrel = $roomsrel;
		
		// Display the template (default.php)
		parent::display($tpl);
		
	}
}
