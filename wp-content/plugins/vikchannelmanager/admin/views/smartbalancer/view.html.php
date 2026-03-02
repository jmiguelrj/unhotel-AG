<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

jimport('joomla.application.component.view');

class VikChannelManagerViewSmartbalancer extends JViewUI {
	function display($tpl = null) {
		$this->addToolBar();
		
		VCM::load_css_js();

		/**
		 * we load the VBO main library for accessing the Application class
		 */
		if (!class_exists('VikBooking') && file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'lib.vikbooking.php')) {
			require_once (VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'lib.vikbooking.php');
		}

		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();

		$lim = $mainframe->getUserStateFromRequest("com_vikchannelmanager.limit", 'limit', $mainframe->get('list_limit'), 'int');
		$ordering = $mainframe->getUserStateFromRequest("smartbalancer.ordering", 'filter_order', 'id', 'string');
		$orderingDir = $mainframe->getUserStateFromRequest("smartbalancer.direction", 'filter_order_Dir', 'DESC', 'string');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
		$navbut = '';
		$rows = array();
		$channels_mapped = false;

		if ($lim0 < 1) {
			$q = "DELETE FROM `#__vikchannelmanager_balancer_rules` WHERE `to_ts` < ".time().";";
			$dbo->setQuery($q);
			$dbo->execute();
		}

		$q = "SELECT SQL_CALC_FOUND_ROWS * FROM `#__vikchannelmanager_balancer_rules` ORDER BY `#__vikchannelmanager_balancer_rules`.`".$ordering."` ".$orderingDir;
		$dbo->setQuery($q, $lim0, $lim);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$rows = $dbo->loadAssocList();
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
			$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
			//get rooms relations
			foreach ($rows as $key => $value) {
				$rows[$key]['rooms_aff'] = array();
				$q = "SELECT `rr`.`room_id`,`vb`.`name` FROM `#__vikchannelmanager_balancer_rooms` AS `rr` LEFT JOIN `#__vikbooking_rooms` AS `vb` ON `rr`.`room_id`=`vb`.`id` WHERE `rr`.`rule_id`=".$value['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$rels = $dbo->loadAssocList();
					foreach ($rels as $rel) {
						array_push($rows[$key]['rooms_aff'], $rel['name']);
					}
				}
			}
			//
		}
		
		$this->rows = $rows;
		$this->lim0 = $lim0;
		$this->navbut = $navbut;
		$this->ordering = $ordering;
		$this->orderingDir = $orderingDir;
		
		parent::display($tpl);
	}
	
	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTSMARTBALANCER'), 'vikchannelmanager');
		
		JToolBarHelper::addNew('newsmartbalancer', JText::_('NEW'));
		JToolBarHelper::spacer();
		JToolBarHelper::editList('editsmartbalancer', JText::_('EDIT'));
		JToolBarHelper::spacer();
		JToolBarHelper::deleteList(JText::_('VCMREMOVECONFIRM'), 'rmsmartbalancer', JText::_('REMOVE'));
		JToolBarHelper::spacer();
		
	}

}
