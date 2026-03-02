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

class VikBookingViewRestrictions extends JViewVikBooking
{
	public function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();

		if (!JFactory::getUser()->authorise('core.vbo.pricing', 'com_vikbooking')) {
			VBOHttpDocument::getInstance()->close(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$app = JFactory::getApplication();

		$lim = $app->getUserStateFromRequest("com_vikbooking.limit", 'limit', $app->get('list_limit'), 'int');
		$pidroom = $app->getUserStateFromRequest("vbo.restrictions.idroom", 'idroom', 0, 'int');
		$pdatefrom = $app->getUserStateFromRequest("vbo.restrictions.datefrom", 'datefrom', '', 'string');
		$pdateto = $app->getUserStateFromRequest("vbo.restrictions.dateto", 'dateto', '', 'string');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');

		$q = "SELECT `id`,`name` FROM `#__vikbooking_rooms` ORDER BY `#__vikbooking_rooms`.`name` ASC;";
		$dbo->setQuery($q);
		$get_rooms = $dbo->loadAssocList();
		$all_rooms = array();
		foreach ($get_rooms as $rk => $rv) {
			$all_rooms[$rv['id']] = $rv['name'];
		}

		$pvborderby = VikRequest::getString('vborderby', '', 'request');
		$pvbordersort = VikRequest::getString('vbordersort', '', 'request');
		$validorderby = array('id', 'name', 'dfrom', 'minlos', 'maxlos', 'ctad', 'ctdd', 'allrooms');
		$orderby = $session->get('vbViewRestrictionsOrderby', 'id');
		$ordersort = $session->get('vbViewRestrictionsOrdersort', 'DESC');
		if (!empty($pvborderby) && in_array($pvborderby, $validorderby)) {
			$orderby = $pvborderby;
			$session->set('vbViewRestrictionsOrderby', $orderby);
			if (!empty($pvbordersort) && in_array($pvbordersort, array('ASC', 'DESC'))) {
				$ordersort = $pvbordersort;
				$session->set('vbViewRestrictionsOrdersort', $ordersort);
			}
		}

		$navbut = "";

		/**
		 * Query the restrictions by applying the given filters, if any.
		 * 
		 * @since 	1.16.10 (J) - 1.6.10 (WP)
		 */
		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS *')
			->from($dbo->qn('#__vikbooking_restrictions'))
			->order($dbo->qn($orderby) . ' ' . $ordersort);

		if ($orderby != 'id') {
			// always sort by ID descending to show who's got higher priority first
			$q->order($dbo->qn('id') . ' DESC');
		}

		if ($pidroom) {
			// make sure we've got a where clause
			$q->where(1);

			// filter by room ID or global restriction
			$q->andWhere([
				// exact room involved
				$dbo->qn('idrooms') . ' LIKE ' . $dbo->q('%-' . $pidroom . '-%'),
				// all rooms
				$dbo->qn('allrooms') . ' = 1',
			], $glue = 'OR');
		}

		if (!empty($pdatefrom) && !empty($pdateto)) {
			// combined date filters
			if (!$pidroom) {
				// make sure we've got a where clause
				$q->where(1);
			}
			$q->andWhere([
				// restrictions with a wider range than the filter range
				$dbo->qn('dfrom') . ' <= ' . VikBooking::getDateTimestamp($pdatefrom) . ' AND ' . $dbo->qn('dto') . ' >= ' . VikBooking::getDateTimestamp($pdateto),
				// restrictions with a shorter range than the filter range
				$dbo->qn('dfrom') . ' >= ' . VikBooking::getDateTimestamp($pdatefrom) . ' AND ' . $dbo->qn('dto') . ' <= ' . VikBooking::getDateTimestamp($pdateto),
				// restrictions intersecting the filter range on end date
				$dbo->qn('dfrom') . ' <= ' . VikBooking::getDateTimestamp($pdatefrom) . ' AND ' . $dbo->qn('dto') . ' <= ' . VikBooking::getDateTimestamp($pdateto) . ' AND ' . $dbo->qn('dto') . ' >= ' . VikBooking::getDateTimestamp($pdatefrom),
				// restrictions intersecting the filter range on start date
				$dbo->qn('dfrom') . ' >= ' . VikBooking::getDateTimestamp($pdatefrom) . ' AND ' . $dbo->qn('dto') . ' >= ' . VikBooking::getDateTimestamp($pdateto) . ' AND ' . $dbo->qn('dfrom') . ' <= ' . VikBooking::getDateTimestamp($pdateto),
			], $glue = 'OR');
		} else {
			// individual date filters
			if (!empty($pdatefrom)) {
				$q->where($dbo->qn('dfrom') . ' >= ' . VikBooking::getDateTimestamp($pdatefrom));
			}
			if (!empty($pdateto)) {
				$q->where($dbo->qn('dto') . ' <= ' . VikBooking::getDateTimestamp($pdateto));
			}
		}

		$dbo->setQuery($q, $lim0, $lim);
		$rows = $dbo->loadAssocList();

		if ($rows) {
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
			$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
		}

		$this->rows = $rows;
		$this->all_rooms = $all_rooms;
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
		JToolBarHelper::title(JText::_('VBMAINRESTRICTIONSTITLE'), 'vikbooking');
		if (JFactory::getUser()->authorise('core.create', 'com_vikbooking')) {
			JToolBarHelper::addNew('newrestriction', JText::_('VBMAINRESTRICTIONNEW'));
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.edit', 'com_vikbooking')) {
			JToolBarHelper::editList('editrestriction', JText::_('VBMAINRESTRICTIONEDIT'));
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.delete', 'com_vikbooking')) {
			JToolBarHelper::deleteList(JText::_('VBDELCONFIRM'), 'removerestrictions', JText::_('VBMAINRESTRICTIONDEL'));
			JToolBarHelper::spacer();
		}
	}
}
