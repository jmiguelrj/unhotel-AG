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

class VikBookingViewCrons extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$lim = $mainframe->getUserStateFromRequest("com_vikbooking.limit", 'limit', $mainframe->get('list_limit'), 'int');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
		$session = JFactory::getSession();
		$pvborderby = VikRequest::getString('vborderby', '', 'request');
		$pvbordersort = VikRequest::getString('vbordersort', '', 'request');
		$validorderby = array('id', 'cron_name', 'last_exec');
		$orderby = $session->get('vbViewCronsOrderby', 'id');
		$ordersort = $session->get('vbViewCronsOrdersort', 'DESC');
		if (!empty($pvborderby) && in_array($pvborderby, $validorderby)) {
			$orderby = $pvborderby;
			$session->set('vbViewCronsOrderby', $orderby);
			if (!empty($pvbordersort) && in_array($pvbordersort, array('ASC', 'DESC'))) {
				$ordersort = $pvbordersort;
				$session->set('vbViewCronsOrdersort', $ordersort);
			}
		}
		$rows = "";
		$navbut = "";
		$q = "SELECT SQL_CALC_FOUND_ROWS `c`.* FROM `#__vikbooking_cronjobs` AS `c` ORDER BY `c`.`".$orderby."` ".$ordersort;
		$dbo->setQuery($q, $lim0, $lim);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$rows = $dbo->loadAssocList();
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
			$navbut="<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
		}
		
		$this->rows = $rows;
		$this->lim0 = $lim0;
		$this->navbut = $navbut;
		$this->orderby = $orderby;
		$this->ordersort = $ordersort;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('VBMAINCRONSTITLE'), 'vikbooking');

		if (JFactory::getUser()->authorise('core.create', 'com_vikbooking'))
		{
			JToolBarHelper::addNew('cronjob.add', JText::_('VBMAINCRONNEW'));
		}

		if (JFactory::getUser()->authorise('core.edit', 'com_vikbooking'))
		{
			JToolBarHelper::editList('cronjob.edit', JText::_('VBMAINCRONEDIT'));
		}

		if (JFactory::getUser()->authorise('core.delete', 'com_vikbooking'))
		{
			JToolBarHelper::deleteList(JText::_('VBDELCONFIRM'), 'cronjob.delete', JText::_('VBMAINCRONDEL'));
		}
	}
}
