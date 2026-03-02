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

class VikBookingViewInvoices extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$session = JFactory::getSession();
		$lim = $mainframe->getUserStateFromRequest("com_vikbooking.limit", 'limit', $mainframe->get('list_limit'), 'int');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
		$search_clauses = array();
		$pfilterinvoices = VikRequest::getString('filterinvoices', '', 'request');
		$pinvstatus = VikRequest::getInt('invstatus', 0, 'request');
		$pmonyear = VikRequest::getString('monyear', '', 'request');
		$ts_filter_start = 0;
		$ts_filter_end = 0;
		if (!empty($pmonyear)) {
			$monyear_parts = explode('_', trim($pmonyear));
			$ts_filter_start = mktime(0, 0, 0, (int)$monyear_parts[1], 1, (int)$monyear_parts[0]);
			if (!empty($ts_filter_start)) {
				$ts_filter_end = mktime(23, 59, 59, (int)$monyear_parts[1], date('t', $ts_filter_start), (int)$monyear_parts[0]);
				if (!empty($ts_filter_end)) {
					$search_clauses[] = "`i`.`for_date`>=".$ts_filter_start;
					$search_clauses[] = "`i`.`for_date`<=".$ts_filter_end;
				}
			}
		}
		if (!empty($pfilterinvoices)) {
			$filter_clause = "(`i`.`number` LIKE ".$dbo->quote('%'.$pfilterinvoices.'%')." OR CONCAT_WS(' ',`c`.`first_name`,`c`.`last_name`) LIKE ".$dbo->quote('%'.$pfilterinvoices.'%')." OR `o`.`custmail` LIKE ".$dbo->quote('%'.$pfilterinvoices.'%')."".(intval($pfilterinvoices) > 1 ? ' OR `o`.`id`='.intval($pfilterinvoices) : '').")";
			$search_clauses[] = $filter_clause;
		}

		$rows = "";
		$navbut = "";

		//get months archive
		$archive = array();
		$q = "SELECT `i`.`id`,`i`.`idorder`,`i`.`for_date`,`o`.`ts` FROM `#__vikbooking_invoices` AS `i` LEFT JOIN `#__vikbooking_orders` `o` ON `o`.`id`=`i`.`idorder` ORDER BY `i`.`for_date` DESC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$invoices = $dbo->loadAssocList();
			foreach ($invoices as $k => $invoice) {
				$info_date = getdate($invoice['for_date']);
				$key_str = $info_date['year'].'_'.$info_date['mon'];
				if (array_key_exists($key_str, $archive)) {
					$archive[$key_str] += 1;
				} else {
					$archive[$key_str] = 1;
				}
			}
		}

		if (!empty($pinvstatus)) {
			if ($pinvstatus === 1) {
				// paid invoices
				$search_clauses[] = "`o`.`total` > 0";
				$search_clauses[] = "`o`.`totpaid` >= `o`.`total`";
			} else {
				// unpaid invoices
				$search_clauses[] = "(`o`.`totpaid` < `o`.`total` OR `o`.`totpaid` <= 0)";
			}
		}

		$q = "SELECT SQL_CALC_FOUND_ROWS `i`.*,`o`.`custdata`,`o`.`ts`,`o`.`status`,`o`.`days`,`o`.`checkin`,`o`.`checkout`,`o`.`custmail`,`o`.`totpaid`,`o`.`total`,`o`.`adminnotes`,`o`.`country`,`o`.`phone`, CONCAT_WS(' ',`c`.`first_name`,`c`.`last_name`) AS `customer_name`,`c`.`country` AS `customer_country`,`nat`.`country_name` " .
			"FROM `#__vikbooking_invoices` AS `i` " .
			"LEFT JOIN `#__vikbooking_orders` `o` ON `o`.`id`=`i`.`idorder` " .
			"LEFT JOIN `#__vikbooking_customers` `c` ON `c`.`id`=`i`.`idcustomer` " .
			"LEFT JOIN `#__vikbooking_countries` `nat` ON `nat`.`country_3_code`=`c`.`country` " .
			(count($search_clauses) > 0 ? "WHERE ".implode(' AND ', $search_clauses)." " : "").
			"ORDER BY `i`.`for_date` DESC, `i`.`idorder` DESC";
		if (!empty($ts_filter_start) && !empty($ts_filter_end)) {
			//month-year filter
			$q .= ";";
			$lim0 = 0;
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$rows = $dbo->loadAssocList();
			}
		} else {
			$dbo->setQuery($q, $lim0, $lim);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$rows = $dbo->loadAssocList();
				$dbo->setQuery('SELECT FOUND_ROWS();');
				jimport('joomla.html.pagination');
				$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
				$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
			}
		}
		
		$this->rows = $rows;
		$this->archive = $archive;
		$this->lim0 = $lim0;
		$this->navbut = $navbut;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VBMAININVOICESTITLE'), 'vikbooking');
		if (JFactory::getUser()->authorise('core.create', 'com_vikbooking')) {
			JToolBarHelper::addNew('newmaninvoice', JText::_('VBOMANUALINVOICE'));
			JToolBarHelper::spacer();
		}
		JToolBarHelper::custom('downloadinvoices', 'download', 'download', JText::_('VBMAININVOICESDOWNLOAD'), true, false);
		JToolBarHelper::spacer();
		JToolBarHelper::custom('resendinvoices', 'mail', 'mail', JText::_('VBMAININVOICESRESEND'), true, false);
		JToolBarHelper::spacer();
		if (JFactory::getUser()->authorise('core.delete', 'com_vikbooking')) {
			JToolBarHelper::deleteList(JText::_('VBDELCONFIRM'), 'removeinvoices', JText::_('VBMAININVOICESDEL'));
			JToolBarHelper::spacer();
		}
	}

}
