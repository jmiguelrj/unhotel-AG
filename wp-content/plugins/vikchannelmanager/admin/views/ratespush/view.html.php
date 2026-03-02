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

class VikChannelManagerViewRatespush extends JViewUI
{
	public function display($tpl = null)
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_vikbooking', (VCMPlatformDetection::isWordPress() ? VIKBOOKING_LANG : JPATH_ADMINISTRATOR), $lang->getTag(), true);
		
		$this->addToolBar();
		
		VCM::load_css_js();
		VCM::load_complex_select();
		VCM::loadDatePicker();

		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();

		/**
		 * It's important to empty the session value that would stop RAR requests from
		 * being executed due to previous API errors caused by outdated mapping information.
		 * This way we let the admin choose the updated and correct rate plan.
		 * 
		 * @since 	1.8.3
		 */
		$session->set('vcmRatespushNewmapping', array());

		$updforvcm = '';
		$vbosess = VikRequest::getInt('vbosess', '', 'request');
		if (!empty($vbosess)) {
			$updforvcm = $session->get('vbVcmRatesUpd', '');
			if (empty($updforvcm) || !is_array($updforvcm) || !(count($updforvcm) > 0)) {
				$updforvcm = '';
			}
		}
		$rmcache = VikRequest::getInt('rmcache', '', 'request');
		if (!empty($rmcache)) {
			$q = "UPDATE `#__vikchannelmanager_config` SET `setting`='' WHERE `param`='bulkratescache';";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		// get the number of occupancy pricing rules defined in VBO to auto-select B.com occupancy pricing
		$q = "SELECT `id` FROM `#__vikbooking_adultsdiff`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$occupancyrules = (int)$dbo->getNumRows();
		//
		
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
		 * Store the original full list of mapped room IDs and obtain their details.
		 * 
		 * @since 	1.9.16
		 */
		$full_mapped_rids = $mapped_rids;
		$global_rooms_info = [];
		try {
			if ($full_mapped_rids) {
				$global_rooms_info = VikBooking::getAvailabilityInstance(true)->loadRooms($full_mapped_rids, 0, true);
			}
		} catch (Throwable $e) {
			// prevent errors from occurring with VikBooking
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

		// count total rooms
		$tot_rooms = 0;

		$q = "SELECT SQL_CALC_FOUND_ROWS * FROM `#__vikbooking_rooms` WHERE `id` IN (" . implode(', ', $mapped_rids) . ") ORDER BY `#__vikbooking_rooms`.`name` ASC";
		$dbo->setQuery($q, $lim0, $lim);
		$rows = $dbo->loadAssocList();
		if ($rows) {
			$dbo->setQuery('SELECT FOUND_ROWS();');
			$tot_rooms = $dbo->loadResult();
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $tot_rooms, $lim0, $lim );
			$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
			foreach( $rows as $k => $r ) {
				$rows[$k]['channels'] = [];
				$rows[$k]['pricetypes'] = [];
				$rows[$k]['defaultrates'] = [];
				// the old query was modified to be compliant with the strict mode (ONLY_FULL_GROUP_BY)
				// $q = "SELECT `id`,`idroomvb`,`idroomota`,`idchannel`,`channel`,`otaroomname`,`otapricing`,`prop_name`,`prop_params` FROM `#__vikchannelmanager_roomsxref` WHERE `idroomvb`=".(int)$r['id']." GROUP BY `idchannel`;";
				$q = "SELECT MIN(`id`) as `id`, MIN(`idroomvb`) AS `idroomvb`, MIN(`idroomota`) AS `idroomota`, `idchannel`, `channel`, MIN(`otaroomname`) AS `otaroomname`, MIN(`otapricing`) AS `otapricing`, MIN(`prop_name`) AS `prop_name`, MIN(`prop_params`) AS `prop_params` FROM `#__vikchannelmanager_roomsxref` WHERE `idroomvb`=".(int)$r['id']." GROUP BY  `idchannel`,`channel`;";
				$dbo->setQuery($q);
				$rows[$k]['channels'] = $dbo->loadAssocList();
				if ($rows[$k]['channels']) {
					$channels_mapped = true;
				}
				// $q = "SELECT `d`.`idroom`,`d`.`idprice`,`p`.`name` FROM `#__vikbooking_dispcost` AS `d` LEFT JOIN `#__vikbooking_prices` `p` ON `d`.`idprice`=`p`.`id` WHERE `d`.`idroom`=".(int)$r['id']." GROUP BY `d`.`idprice` ORDER BY `p`.`name` ASC;";
				$q = "SELECT DISTINCT `d`.`idroom`,`d`.`idprice`,`p`.`name` FROM `#__vikbooking_dispcost` AS `d` LEFT JOIN `#__vikbooking_prices` `p` ON `d`.`idprice`=`p`.`id` WHERE `d`.`idroom`=".(int)$r['id']." ORDER BY `p`.`name` ASC;";
				$dbo->setQuery($q);
				$pricetypes = $dbo->loadAssocList();
				if ($pricetypes) {
					/**
					 * We need to apply a custom ordering of the types of price so that the "Standard Rate" will
					 * always come first and not after an hipothetical "Non Refundable Rate" which would come
					 * first if we were to use the default alphabetical ordering. This is to pre-select it in the
					 * Bulk Action as the first element, because most of the times non refundable rates are derived.
					 * 
					 * @since 	1.8.3
					 */
					if (count($pricetypes) > 1) {
						// we need at least two rate plans
						$first_rplan = [];
						foreach ($pricetypes as $ptk => $ptv) {
							$check_nonref_name = (stripos($ptv['name'], 'Non') === false && stripos($ptv['name'], 'Not') === false);
							if ($ptk > 0 && (stripos($ptv['name'], 'Standard') !== false || stripos($ptv['name'], 'Base') !== false) && $check_nonref_name) {
								// this has to be a "Standard Rate" or similar
								$first_rplan = $ptv;
								// unset it from the current order
								unset($pricetypes[$ptk]);
								break;
							}
						}
						if ($first_rplan) {
							// unshift the array to prepend the "Standard Rate" just found
							array_unshift($pricetypes, $first_rplan);
						}
					}
					// push website rate plans
					$rows[$k]['pricetypes'] = $pricetypes;
					$defaultrates = [];
					foreach ($pricetypes as $pricetype) {
						$q = "SELECT `days`,`cost` FROM `#__vikbooking_dispcost` WHERE `idroom`=".(int)$r['id']." AND `idprice`=".(int)$pricetype['idprice']." ORDER BY `days` ASC LIMIT 1;";
						$dbo->setQuery($q);
						$pricetrates = $dbo->loadAssoc();
						if ($pricetrates) {
							if ($pricetrates['days'] > 1) {
								$pricetrates['cost'] = $pricetrates['cost'] / $pricetrates['days'];
							}
							$defaultrates[] = $pricetrates['cost'];
						}
					}
					if (count($defaultrates) == count($pricetypes)) {
						$rows[$k]['defaultrates'] = $defaultrates;
					}
				}
			}
		}

		if ($channels_mapped !== true || !$rows) {
			VikError::raiseWarning('', JText::_('VCMNOROOMSASSOCFOUND'));
			$app->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$this->rows = $rows;
		$this->tot_rooms = $tot_rooms;
		$this->updforvcm = $updforvcm;
		$this->occupancyrules = $occupancyrules;
		$this->global_rooms_info = $global_rooms_info;
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
		JToolBarHelper::title(JText::_('VCMMAINTRATESPUSH'), 'vikchannelmanager');

		JToolBarHelper::save('ratespushsubmit', JText::_('VCMRATESPUSHSUBMIT'));
		JToolBarHelper::spacer();
		JToolBarHelper::cancel( 'cancel', JText::_('BACK'));	
	}
}
