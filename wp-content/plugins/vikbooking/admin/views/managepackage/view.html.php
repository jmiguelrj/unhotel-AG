<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

// import Joomla view library
jimport('joomla.application.component.view');

class VikBookingViewManagepackage extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		if (!empty($cid[0])) {
			$pid = $cid[0];
		}

		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$rooms = array();
		$q = "SELECT `id`,`name` FROM `#__vikbooking_rooms` ORDER BY `name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$rooms = $dbo->loadAssocList();
		} else {
			VikError::raiseWarning('', JText::_('VBNOROOMSFOUND'));
			$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
			exit;
		}
		$package = array();
		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikbooking_packages` WHERE `id`=".(int)$pid.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$package = $dbo->loadAssoc();
			} else {
				VikError::raiseWarning('', 'Not Found');
				$mainframe->redirect("index.php?option=com_vikbooking&task=packages");
				exit;
			}
			$q = "SELECT * FROM `#__vikbooking_packages_rooms` WHERE `idpackage`=".(int)$pid.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$rooms_xref = $dbo->loadAssocList();
				foreach ($rooms_xref as $room_xref) {
					foreach ($rooms as $rk => $rv) {
						if ($rv['id'] != $room_xref['idroom']) {
							continue;
						}
						$rooms[$rk]['selected'] = 1;
						break;
					}
				}
			}
		}
		
		$this->package = $package;
		$this->rooms = $rooms;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		$cid = VikRequest::getVar('cid', array(0));
		
		if (!empty($cid[0])) {
			//edit
			JToolBarHelper::title(JText::_('VBMAINPACKAGESTITLE'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikbooking')) {
				JToolBarHelper::apply( 'updatepackagestay', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
				JToolBarHelper::save( 'updatepackage', JText::_('VBSAVECLOSE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelpackages', JText::_('VBBACK'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VBMAINPACKAGESTITLE'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.create', 'com_vikbooking')) {
				JToolBarHelper::apply( 'createpackagestay', JText::_('VBSAVE'));
				JToolBarHelper::save('createpackage', JText::_('VBSAVECLOSE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelpackages', JText::_('VBBACK'));
			JToolBarHelper::spacer();
		}
	}

}
