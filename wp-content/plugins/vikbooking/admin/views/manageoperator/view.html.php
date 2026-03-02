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

class VikBookingViewManageoperator extends JViewVikBooking
{
	public function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', [0]);
		if (!empty($cid[0])) {
			$idoper = $cid[0];
		}

		$operator = [];
		$dbo = JFactory::getDBO();
		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikbooking_operators` WHERE `id`=" . (int) $idoper . ";";
			$dbo->setQuery($q);
			$operator = $dbo->loadAssoc();
			if (!$operator) {
				$mainframe = JFactory::getApplication();
				$mainframe->redirect("index.php?option=com_vikbooking&task=operators");
				exit;
			}

			// decode JSON-encoded strings
			if (!empty($operator['work_days_week'])) {
				$operator['work_days_week'] = json_decode($operator['work_days_week'], true);
			}
			if (!empty($operator['work_days_exceptions'])) {
				$operator['work_days_exceptions'] = json_decode($operator['work_days_exceptions'], true);
			}
			$operator['work_days_week'] = (array) ($operator['work_days_week'] ?? []);
			$operator['work_days_exceptions'] = (array) ($operator['work_days_exceptions'] ?? []);
		}

		$this->operator = $operator;

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar()
	{
		$cid = VikRequest::getVar('cid', [0]);

		if (!empty($cid[0])) {
			// edit
			JToolBarHelper::title(JText::_('VBMAINMANAGEOPERATORTITLE'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikbooking')) {
				JToolBarHelper::apply( 'updateoperatorstay', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
				JToolBarHelper::save( 'updateoperator', JText::_('VBSAVECLOSE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'canceloperator', JText::_('VBBACK'));
			JToolBarHelper::spacer();
		} else {
			// new
			JToolBarHelper::title(JText::_('VBMAINMANAGEOPERATORTITLE'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.create', 'com_vikbooking')) {
				JToolBarHelper::save('saveoperator', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'canceloperator', JText::_('VBBACK'));
			JToolBarHelper::spacer();
		}
	}
}
