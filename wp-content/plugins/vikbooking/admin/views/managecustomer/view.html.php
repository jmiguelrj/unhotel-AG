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

class VikBookingViewManagecustomer extends JViewVikBooking
{
	public function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();

		$dbo = JFactory::getDbo();

		$customer = [];

		$cid = VikRequest::getVar('cid', array(0));
		if (!empty($cid[0])) {
			$idcust = $cid[0];
		}

		if (!empty($cid[0])) {
			$q = "SELECT *, (SELECT COUNT(*) FROM `#__vikbooking_customers_orders` WHERE `#__vikbooking_customers_orders`.`idcustomer`=`#__vikbooking_customers`.`id`) AS `tot_bookings` FROM `#__vikbooking_customers` WHERE `id`=" . (int) $idcust . ";";
			$dbo->setQuery($q);
			$customer = (array) $dbo->loadAssoc();

			if (!$customer) {
				$mainframe = JFactory::getApplication();
				$mainframe->redirect("index.php?option=com_vikbooking&task=customers");
				exit;
			}
		}
		$q = "SELECT * FROM `#__vikbooking_countries` ORDER BY `#__vikbooking_countries`.`country_name` ASC;";
		$dbo->setQuery($q);
		$countries = $dbo->loadAssocList();

		$this->customer = $customer;
		$this->countries = $countries;

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
			JToolBarHelper::title(JText::_('VBMAINMANAGECUSTOMERTITLE'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikbooking')) {
				JToolBarHelper::apply( 'updatecustomerstay', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
				JToolBarHelper::save( 'updatecustomer', JText::_('VBSAVECLOSE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelcustomer', JText::_('VBBACK'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VBMAINMANAGECUSTOMERTITLE'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.create', 'com_vikbooking')) {
				JToolBarHelper::save('savecustomer', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelcustomer', JText::_('VBBACK'));
			JToolBarHelper::spacer();
		}
	}
}
