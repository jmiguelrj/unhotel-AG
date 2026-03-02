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

class VikChannelManagerViewManagesmartbalancer extends JViewUI {
	function display($tpl = null) {
		//require the main lib of VBO
		require_once(VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php");
		//

		$this->addToolBar();
		
		VCM::load_css_js();
		VCM::load_complex_select();
		VCM::loadDatePicker();

		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();

		$type = VikRequest::getVar('type', 'new');
		$ids = VikRequest::getVar('cid', array(0));
		$row = array(
			'id' => (int)$ids[0],
			'name' => '',
			'type' => '',
			'from_ts' => 0,
			'to_ts' => 0,
			'rule' => new stdClass,
			'logs' => new stdClass,
			'rooms_aff' => array()
		);
		if ($type == 'edit' && !empty($ids[0])) {
			$q = "SELECT * FROM `#__vikchannelmanager_balancer_rules` WHERE `id`=".intval($ids[0]).";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$row = $dbo->loadAssoc();
				$row['rule'] = json_decode($row['rule']);
				$row['logs'] = json_decode($row['logs']);
				$row['rooms_aff'] = array();
				$q = "SELECT `rr`.`room_id`,`vb`.`name` FROM `#__vikchannelmanager_balancer_rooms` AS `rr` LEFT JOIN `#__vikbooking_rooms` AS `vb` ON `rr`.`room_id`=`vb`.`id` WHERE `rr`.`rule_id`=".$row['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$rels = $dbo->loadAssocList();
					foreach ($rels as $rel) {
						array_push($row['rooms_aff'], $rel['room_id']);
					}
				}
			} else {
				//Rule ID not found
				VikError::raiseWarning('', JText::_('VCMSMARTBALRULENOTFOUND'));
				$mainframe->redirect('index.php?option=com_vikchannelmanager&task=smartbalancer');
				exit;
			}
		}
		$all_rooms = array();
		$have_multi_units = 0;
		$q = "SELECT `id`,`name`,`units` FROM `#__vikbooking_rooms` WHERE `avail`=1 ORDER BY `name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$all_rooms = $dbo->loadAssocList();
			foreach ($all_rooms as $room) {
				if ($room['units'] > 1) {
					$have_multi_units = 1;
					break;
				}
			}
		}
		
		$this->rule_id = $ids[0];
		$this->row = $row;
		$this->all_rooms = $all_rooms;
		$this->have_multi_units = $have_multi_units;
		
		parent::display($tpl);
	}
	
	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		//Add menu title and some buttons to the page
		$type = VikRequest::getVar('type', 'new');
		JToolBarHelper::title(JText::_($type == 'new' ? 'VCMMAINTSMARTBALANCERNEWRULE' : 'VCMMAINTSMARTBALANCEREDITRULE'), 'vikchannelmanager');
		
		JToolBarHelper::apply( 'saveSmartBalancer', JText::_('SAVE'));
		JToolBarHelper::spacer();
		JToolbarHelper::save('saveCloseSmartBalancer', JText::_('SAVECLOSE'));
        JToolBarHelper::spacer();
		JToolBarHelper::cancel( 'cancelsmartbalancer', JText::_('CANCEL'));
		JToolBarHelper::spacer();
		
	}

}
