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

class VikBookingViewManagemaninvoice extends JViewVikBooking
{
	public function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();

		$dbo = JFactory::getDbo();

		$cid = VikRequest::getVar('cid', array(0));
		if (!empty($cid[0])) {
			$idinv = $cid[0];
		}

		$invoice = array();
		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikbooking_invoices` WHERE `id`=".(int)$idinv." AND `idorder`<0;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$invoice = $dbo->loadAssoc();
			} else {
				JFactory::getApplication()->redirect("index.php?option=com_vikbooking&task=invoices");
				exit;
			}
		}

		$taxrates = array();
		$q = "SELECT * FROM `#__vikbooking_iva`;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$taxrates = $dbo->loadAssocList();
		}
		
		$this->invoice = $invoice;
		$this->taxrates = $taxrates;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar()
	{
		$cid = VikRequest::getVar('cid', array(0));
		
		if (!empty($cid[0])) {
			//edit
			JToolBarHelper::title(JText::_('VBMAINMANAGEMANINVTITLE'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikbooking')) {
				JToolBarHelper::apply( 'updatemaninvoicestay', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
				JToolBarHelper::save( 'updatemaninvoice', JText::_('VBSAVECLOSE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelinvoice', JText::_('VBBACK'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VBMAINMANAGEMANINVTITLE'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.create', 'com_vikbooking')) {
				JToolBarHelper::save('savemaninvoice', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelinvoice', JText::_('VBBACK'));
			JToolBarHelper::spacer();
		}
	}
}
