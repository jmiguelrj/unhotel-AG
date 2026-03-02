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

class VikChannelManagerViewBpromomanage extends JViewUI {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		VCM::loadDatePicker();

		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();
		$mainframe = JFactory::getApplication();
		
		$config = VikChannelManager::loadConfiguration();
		$validate = array('apikey');
		foreach ($validate as $v) {
			if (empty($config[$v])) {
				VikError::raiseWarning('', VikChannelManager::getErrorFromMap('e4j.error.Settings'));
				$mainframe->redirect("index.php?option=com_vikchannelmanager");
				exit;
			}
		}
		
		$q = "SELECT `vbr`.`id`,`vbr`.`name`,`vbr`.`img`,`vbr`.`smalldesc` FROM `#__vikbooking_rooms` AS `vbr` ORDER BY `vbr`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$vbrooms = $dbo->loadAssocList();
		} else {
			VikError::raiseWarning('', 'There are no rooms in VikBooking.');
			$mainframe->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		if (!isset($channel['params']['hotelid']) || empty($channel['params']['hotelid'])) {
			VikError::raiseWarning('', 'Empty Hotel ID for Booking.com.');
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=config");
			exit;
		}

		// load the information about the rate plans from rooms mapping
		$otarooms = array();
		$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=".VikChannelManagerConfig::BOOKING.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$otarooms = $dbo->loadAssocList();
		}
		
		// empty string means that the request has to be performed
		$promotions = $session->get('vcmBPromo', '');
		//

		$cid = VikRequest::getVar('cid', array(0));
		$idpromo = null;
		if (!empty($cid[0])) {
			$idpromo = (string)$cid[0];
		}

		$promotion = new stdClass;
		if (!empty($cid[0])) {
			if (@count($promotions)) {
				foreach ($promotions as $k => $promo) {
					if (isset($promo->id) && (string)$promo->id == $idpromo) {
						// promotion requested found in session array. It's a stdClass object.
						$promotion = $promo;
						break;
					}
				}
			}
			$totvars = get_object_vars($promotion);
			if (!$totvars || !count($totvars)) {
				// the promotion has not been found in the session array. Terminate the process
				VikError::raiseWarning('', 'Could not find the requested promotion.');
				$mainframe->redirect("index.php?option=com_vikbooking&task=bpromo");
				exit;
			}
		}

		$this->config = $config;
		$this->channel = $channel;
		$this->promotion = $promotion;
		$this->otarooms = $otarooms;
		
		// Display the template (default.php)
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		$cid = VikRequest::getVar('cid', array(0));
		
		if (!empty($cid[0])) {
			//edit
			JToolBarHelper::title(JText::_('VCMMAINTBEDITPROMOTION'), 'vikchannelmanager');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikchannelmanager')) {
				JToolBarHelper::save( 'bpromo.updatepromo', JText::_('SAVECLOSE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelbpromo', JText::_('CANCEL'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VCMMAINTBNEWPROMOTION'), 'vikchannelmanager');
			if (JFactory::getUser()->authorise('core.create', 'com_vikchannelmanager')) {
				JToolBarHelper::save('bpromo.savepromo', JText::_('SAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelbpromo', JText::_('CANCEL'));
			JToolBarHelper::spacer();
		}
	}

}
