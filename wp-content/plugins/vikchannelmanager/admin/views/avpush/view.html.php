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

class VikChannelManagerViewAvpush extends JViewUI
{
	public function display($tpl = null)
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_vikbooking', (VCMPlatformDetection::isWordPress() ? VIKBOOKING_ADMIN_LANG : JPATH_ADMINISTRATOR), $lang->getTag(), true);
		
		$this->addToolBar();
		
		VCM::load_css_js();
		VCM::loadDatePicker();

		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$lim = $app->getUserStateFromRequest("com_vikchannelmanager.limit", 'limit', $app->get('list_limit'), 'int');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
		$navbut = '';
		$channels_mapped = false;

		/**
		 * If for some reasons, VBO contains several rooms per page, but the first rooms
		 * are not mapped to any channel but other rooms are mapped, then it is not possible
		 * to proceed. In this case we need to grab all rooms mapped in VCM.
		 * We now grab all room IDs mapped to at least one channel and then we apply the pagination
		 * only over those specific room IDs.
		 * 
		 * @since 	1.7.5
		 */
		$mapped_rids = [];
		$q = "SELECT `idroomvb` FROM `#__vikchannelmanager_roomsxref` GROUP BY `idroomvb`;";
		$dbo->setQuery($q);
		$mapped_rooms = $dbo->loadAssocList();
		if (!$mapped_rooms) {
			// useless to proceed
			VikError::raiseWarning('', JText::_('VCMNOROOMSASSOCFOUND'));
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}
		foreach ($mapped_rooms as $mp) {
			array_push($mapped_rids, (int) $mp['idroomvb']);
		}

		/**
		 * Allow filtering by listing or category IDs.
		 * 
		 * @since 	1.9.6
		 */
		$listingsfilter = (array) $app->input->get('listingsfilter', []);
		$filter_listing_ids = array_filter($listingsfilter, function($id) {
			return intval($id) > 0;
		});
		$filter_category_ids = array_filter($listingsfilter, function($id) {
			return intval($id) < 0;
		});
		if ($filter_category_ids) {
			$filter_listing_ids = array_map('intval', array_values(array_unique(array_merge($filter_listing_ids, VikBooking::getAvailabilityInstance(true)->filterRoomCategories($filter_category_ids)))));
		}
		if ($filter_listing_ids) {
			// attempt to overwrite mapped room IDs according to filters
			$filter_rids = array_intersect($mapped_rids, $filter_listing_ids);
			if ($filter_rids) {
				$mapped_rids = array_values($filter_rids);
			}
		}

		// load mapped room records
		$q = "SELECT SQL_CALC_FOUND_ROWS * FROM `#__vikbooking_rooms` WHERE `id` IN (" . implode(', ', $mapped_rids) . ") ORDER BY `#__vikbooking_rooms`.`name` ASC";
		$dbo->setQuery($q, $lim0, $lim);
		$rows = $dbo->loadAssocList();
		if ($rows) {
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
			$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
			foreach ($rows as $k => $r) {
				// the old query was modified to be compliant with the strict mode (ONLY_FULL_GROUP_BY)
				// $q = "SELECT `id`,`idroomvb`,`idroomota`,`idchannel`,`channel`,`otaroomname`,`otapricing`,`prop_name`,`prop_params` FROM `#__vikchannelmanager_roomsxref` WHERE `idroomvb`=".(int)$r['id']." GROUP BY `idchannel`;";
				$q = "SELECT MIN(`id`) as `id`, MIN(`idroomvb`) AS `idroomvb`, MIN(`idroomota`) AS `idroomota`, `idchannel`, `channel`, MIN(`otaroomname`) AS `otaroomname`, MIN(`otapricing`) AS `otapricing`, MIN(`prop_name`) AS `prop_name`, MIN(`prop_params`) AS `prop_params` FROM `#__vikchannelmanager_roomsxref` WHERE `idroomvb`=".(int)$r['id']." GROUP BY  `idchannel`,`channel`;";
				$dbo->setQuery($q);
				$rows[$k]['channels'] = $dbo->loadAssocList();
				if ($rows[$k]['channels']) {
					$channels_mapped = true;
				}
			}
		}

		if ($channels_mapped !== true || !$rows) {
			VikError::raiseWarning('', JText::_('VCMNOROOMSASSOCFOUND'));
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$this->rows = $rows;
		$this->lim0 = $lim0;
		$this->navbut = $navbut;

		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTAVPUSH'), 'vikchannelmanager');

		JToolBarHelper::save('avpushsubmit', JText::_('VCMAVPUSHSUBMIT'));
		JToolBarHelper::spacer();
		JToolBarHelper::cancel( 'cancel', JText::_('BACK'));
	}
}
