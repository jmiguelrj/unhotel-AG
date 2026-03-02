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

class VikChannelManagerViewreslogs extends JViewUI {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();
		
		VCM::load_css_js();
		VCM::load_complex_select();

		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$navbut = '';
		$tot = 0;
		$rows = array();
		$rooms = array();
		$filters = array(
			'fromdate' => VikRequest::getString('fromdate', '', 'request'),
			'todate' => VikRequest::getString('todate', '', 'request'),
			'whatdate' => VikRequest::getString('whatdate', 'day', 'request'),
			'roomids' => VikRequest::getVar('roomids', array()),
			'reskey' => VikRequest::getString('reskey', '', 'request')
		);

		$q = "SELECT `id`,`name` FROM `#__vikbooking_rooms` ORDER BY `name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$all_rooms = $dbo->loadAssocList();
			foreach ($all_rooms as $r) {
				$rooms[$r['id']] = $r['name'];
			}
		}

		$lim = $mainframe->getUserStateFromRequest("com_vikchannelmanager.limit", 'limit', $mainframe->get('list_limit'), 'int');
		$ordering = $mainframe->getUserStateFromRequest("reslogs.ordering", 'filter_order', 'dt', 'string');
		$orderingDir = $mainframe->getUserStateFromRequest("reslogs.direction", 'filter_order_Dir', 'DESC', 'string');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
	
		$reslogger = VikChannelManager::getResLoggerInstance();

		// set filters and clauses
		$reslogger->filterLim0($lim0)
			->filterLim($lim)
			->filterOrdering($ordering)
			->filterDirection($orderingDir);
		if (!empty($filters['fromdate']) && strtotime($filters['fromdate']) <= 0) {
			// prevent default dates like 0000-00-00 00:00:00
			$filters['fromdate'] = '';
		}
		if (!empty($filters['todate']) && strtotime($filters['todate']) <= 0) {
			// prevent default dates like 0000-00-00 00:00:00
			$filters['todate'] = '';
		}
		if (!empty($filters['fromdate']) && empty($filters['todate'])) {
			// single dates are unified
			$filters['todate'] = $filters['fromdate'];
		} elseif (empty($filters['fromdate']) && !empty($filters['todate'])) {
			// single dates are unified
			$filters['fromdate'] = $filters['todate'];
		}
		$filters['whatdate'] = in_array($filters['whatdate'], array('day', 'dt')) ? $filters['whatdate'] : 'day';
		$daymethod = 'clause' . ucfirst($filters['whatdate']);
		if (!empty($filters['fromdate'])) {
			$reslogger->{$daymethod}($filters['fromdate'], '>=', array());
		}
		if (!empty($filters['todate'])) {
			// force the end date to be at the end
			$reslogger->{$daymethod}($filters['todate'] . ' 23:59:59', '<=', array());
		}
		if (count($filters['roomids']) && !empty($filters['roomids'][0])) {
			$reslogger->clauseIdRoomVb('('.implode(', ', $filters['roomids']).')', 'IN');
		}
		if (!empty($filters['reskey'])) {
			$reslogger->clauseCustom('(`idorder`='.(int)$filters['reskey'].' OR `idorderota`='.$dbo->quote($filters['reskey']).' OR `idroomota`='.(int)$filters['reskey'].')');
		}

		// load records
		$logsdata = $reslogger->removeExpired()
			->load();
		list($rows, $tot) = $logsdata;

		if (count($rows)) {
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $tot, $lim0, $lim );
			$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
		}
		
		$this->reslogger = $reslogger;
		$this->rows = $rows;
		$this->navbut = $navbut;
		$this->rooms = $rooms;
		$this->filters = $filters;
		
		// Display the template (default.php)
		parent::display($tpl);
		
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTRESLOGS'), 'vikchannelmanager');
		JToolBarHelper::cancel('cancel', JText::_('CANCEL'));
		JToolBarHelper::spacer();
		
	}
}
