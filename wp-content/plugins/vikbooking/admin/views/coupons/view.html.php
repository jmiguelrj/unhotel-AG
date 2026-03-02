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

class VikBookingViewCoupons extends JViewVikBooking
{
	public function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();

		$rows = [];
		$navbut = "";

		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$pcode = $app->getUserStateFromRequest("vbo.coupons.code", 'code', '', 'string');

		$lim = $app->getUserStateFromRequest("com_vikbooking.limit", 'limit', $app->get('list_limit'), 'int');

		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');

		$clauses = [];
		if (!empty($pcode)) {
			$clauses[] = "`c`.`code` LIKE " . $dbo->quote("%{$pcode}%");
		}

		$q = "SELECT SQL_CALC_FOUND_ROWS `c`.* FROM `#__vikbooking_coupons` AS `c`" . (count($clauses) ? " WHERE " . implode(" AND ", $clauses) : "") . " ORDER BY `c`.`code` ASC";
		$dbo->setQuery($q, $lim0, $lim);
		$dbo->execute();

		/**
		 * Call assertListQuery() from the View class to make sure the filters set
		 * do not produce an empty result. This would reset the page in this case.
		 */
		$this->assertListQuery($lim0, $lim);
		//

		if ($dbo->getNumRows() > 0) {
			$rows = $dbo->loadAssocList();
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
			$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
		}
		
		$this->rows = $rows;
		$this->lim0 = $lim0;
		$this->navbut = $navbut;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VBMAINCOUPONTITLE'), 'vikbooking');
		if (JFactory::getUser()->authorise('core.create', 'com_vikbooking')) {
			JToolBarHelper::addNew('newcoupon', JText::_('VBMAINCOUPONNEW'));
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.edit', 'com_vikbooking')) {
			JToolBarHelper::editList('editcoupon', JText::_('VBMAINCOUPONEDIT'));
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.delete', 'com_vikbooking')) {
			JToolBarHelper::deleteList(JText::_('VBDELCONFIRM'), 'removecoupons', JText::_('VBMAINCOUPONDEL'));
			JToolBarHelper::spacer();
		}
	}
}
