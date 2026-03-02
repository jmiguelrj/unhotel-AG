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

class VikBookingViewSeasons extends JViewVikBooking
{
	public function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();

		if (!JFactory::getUser()->authorise('core.vbo.pricing', 'com_vikbooking')) {
			VBOHttpDocument::getInstance()->close(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$rows = [];
		$navbut = "";
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();
		$session = JFactory::getSession();

		$pidroom = $app->getUserStateFromRequest("vbo.seasons.idroom", 'idroom', 0, 'int');
		$q = "SELECT `id`,`name` FROM `#__vikbooking_rooms` ORDER BY `#__vikbooking_rooms`.`name` ASC;";
		$dbo->setQuery($q);
		$all_rooms = $dbo->loadAssocList();
		$roomsel = '<select id="idroom" name="idroom" onchange="document.seasonsform.submit();"><option value="">'.JText::_('VBAFFANYROOM').'</option>';
		if ($all_rooms) {
			foreach ($all_rooms as $room) {
				$roomsel .= '<option value="'.$room['id'].'"'.($room['id'] == $pidroom ? ' selected="selected"' : '').'>- '.$room['name'].'</option>';
			}
			$all_rooms_copy = array();
			foreach ($all_rooms as $kp => $room) {
				$all_rooms_copy[$room['id']] = $room['name'];
			}
			$all_rooms = $all_rooms_copy;
		}
		$roomsel .= '</select>';

		$pidprice = $app->getUserStateFromRequest("vbo.seasons.idprice", 'idprice', 0, 'int');
		$q = "SELECT `id`,`name` FROM `#__vikbooking_prices` ORDER BY `#__vikbooking_prices`.`name` ASC;";
		$dbo->setQuery($q);
		$all_prices = $dbo->loadAssocList();
		$pricesel = '<select id="idprice" name="idprice" onchange="document.seasonsform.submit();"><option value="">'.JText::_('VBAFFANYPRICE').'</option>';
		if ($all_prices) {
			foreach ($all_prices as $price) {
				$pricesel .= '<option value="'.$price['id'].'"'.($price['id'] == $pidprice ? ' selected="selected"' : '').'>- '.$price['name'].'</option>';
			}
			$all_prices_copy = array();
			foreach ($all_prices as $kp => $price) {
				$all_prices_copy[$price['id']] = $price['name'];
			}
			$all_prices = $all_prices_copy;
		}
		$pricesel .= '</select>';

		$pispromotion = $app->getUserStateFromRequest("vbo.seasons.ispromotion", 'ispromotion', 0, 'int');
		$lim = $app->getUserStateFromRequest("com_vikbooking.limit", 'limit', $app->get('list_limit'), 'int');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
		$pvborderby = VikRequest::getString('vborderby', '', 'request');
		$pvbordersort = VikRequest::getString('vbordersort', '', 'request');
		$validorderby = array('id', 'spname', 'from', 'to', 'promo', 'diffcost');
		$orderby = $session->get('vbShowSeasonsOrderby', 'id');
		$ordersort = $session->get('vbShowSeasonsOrdersort', 'DESC');
		if (!empty($pvborderby) && in_array($pvborderby, $validorderby)) {
			$orderby = $pvborderby;
			$session->set('vbShowSeasonsOrderby', $orderby);
			if (!empty($pvbordersort) && in_array($pvbordersort, array('ASC', 'DESC'))) {
				$ordersort = $pvbordersort;
				$session->set('vbShowSeasonsOrdersort', $ordersort);
			}
		}

		$order_clause = "`s`.`{$orderby}` {$ordersort}";
		if ($orderby == 'from' || $orderby == 'to') {
			$order_clause = "`s`.`year` {$ordersort}, {$order_clause}";
		}

		$clauses = array();
		if (!empty($pidroom)) {
			$clauses[] = "`s`.`idrooms` LIKE '%-".$pidroom."-%'";
		}
		if (!empty($pidprice)) {
			$clauses[] = "(`s`.`idprices` LIKE '%-".$pidprice."-%' OR CHAR_LENGTH(`s`.`idprices`) = 0)";
		}
		if ($pispromotion !== 0) {
			$clauses[] = '`s`.`promo`=' . ($pispromotion > 0 ? 1 : 0);
		}
		$q = "SELECT SQL_CALC_FOUND_ROWS `s`.* FROM `#__vikbooking_seasons` AS `s`".(count($clauses) > 0 ? " WHERE ".implode(" AND ", $clauses) : "")." ORDER BY {$order_clause}";
		$dbo->setQuery($q, $lim0, $lim);
		$dbo->execute();

		/**
		 * Call assertListQuery() from the View class to make sure the filters set
		 * do not produce an empty result. This would reset the page in this case.
		 */
		$this->assertListQuery($lim0, $lim);
		//

		$global_records = 0;

		if ($dbo->getNumRows()) {
			$rows = $dbo->loadAssocList();
			$dbo->setQuery('SELECT FOUND_ROWS();');
			$global_records = (int) $dbo->loadResult();
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $global_records, $lim0, $lim );
			$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
		}
		
		$this->rows = $rows;
		$this->global_records = $global_records;
		$this->roomsel = $roomsel;
		$this->all_rooms = $all_rooms;
		$this->pricesel = $pricesel;
		$this->all_prices = $all_prices;
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
		JToolBarHelper::title(JText::_('VBMAINSEASONSTITLE'), 'vikbooking');
		if (JFactory::getUser()->authorise('core.create', 'com_vikbooking')) {
			JToolBarHelper::addNew('newseason', JText::_('VBMAINSEASONSNEW'));
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.edit', 'com_vikbooking')) {
			JToolBarHelper::editList('editseason', JText::_('VBMAINSEASONSEDIT'));
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.delete', 'com_vikbooking')) {
			JToolBarHelper::deleteList(JText::_('VBDELCONFIRM'), 'removeseasons', JText::_('VBMAINSEASONSDEL'));
			JToolBarHelper::spacer();
		}
	}
}
