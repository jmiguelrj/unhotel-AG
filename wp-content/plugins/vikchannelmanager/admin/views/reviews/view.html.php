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

// import Joomla view library
jimport('joomla.application.component.view');

class VikChannelManagerViewreviews extends JViewUI
{
	public function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();
		
		VCM::load_css_js();
		VCM::load_complex_select();

		$dbo = JFactory::getDbo();
		$mainframe = JFactory::getApplication();
		$navbut = '';
		$rows = array();
		
		$langs = array();
		$countries = array();
		$channels = array();
		$propnames = array();
		$filters = array(
			'fromdate' => VikRequest::getString('fromdate', '', 'request'),
			'todate' => VikRequest::getString('todate', '', 'request'),
			'lang' => VikRequest::getString('lang', '', 'request'),
			'country' => VikRequest::getString('country', '', 'request'),
			'channel' => VikRequest::getString('channel', '', 'request'),
			'prop_name' => VikRequest::getString('prop_name', '', 'request'),
		);

		$q = "SELECT `lang` FROM `#__vikchannelmanager_otareviews` GROUP BY `lang` ORDER BY `lang` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$all_langs = $dbo->loadAssocList();
			foreach ($all_langs as $v) {
				if (empty($v['lang'])) {
					continue;
				}
				array_push($langs, $v['lang']);
			}
		}

		$q = "SELECT `country` FROM `#__vikchannelmanager_otareviews` GROUP BY `country` HAVING `country` IS NOT NULL AND `country` <> '' ORDER BY `country` ASC;";
		$dbo->setQuery($q);
		$countries = $dbo->loadColumn();

		$q = "SELECT `channel` FROM `#__vikchannelmanager_otareviews` GROUP BY `channel` ORDER BY `channel` ASC;";
		$dbo->setQuery($q);

		$channels = array_map(function($ch) {
			return $ch ?: JText::_('VCMWEBSITE');
		}, $dbo->loadColumn());

		$q = "SELECT `prop_name` FROM `#__vikchannelmanager_otareviews` GROUP BY `prop_name` HAVING `prop_name` IS NOT NULL AND `prop_name` <> '' ORDER BY `prop_name` ASC;";
		$dbo->setQuery($q);
		$propnames = $dbo->loadColumn();

		$lim = $mainframe->getUserStateFromRequest("com_vikchannelmanager.limit", 'limit', $mainframe->get('list_limit'), 'int');
		$ordering = $mainframe->getUserStateFromRequest("reviews.ordering", 'filter_order', 'dt', 'string');
		$orderingDir = $mainframe->getUserStateFromRequest("reviews.direction", 'filter_order_Dir', 'DESC', 'string');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');

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

		$clauses = array();
		if (!empty($filters['fromdate'])) {
			array_push($clauses, "`dt`>=".$dbo->quote(date('Y-m-d H:i:s', strtotime($filters['fromdate']))));
		}
		if (!empty($filters['todate'])) {
			$to_info = getdate(strtotime($filters['todate']));
			array_push($clauses, "`dt`<=".$dbo->quote(date('Y-m-d H:i:s', mktime(23, 59, 59, $to_info['mon'], $to_info['mday'], $to_info['year']))));
		}
		if (!empty($filters['channel'])) {
			if (strtolower($filters['channel']) == strtolower(JText::_('VCMWEBSITE'))) {
				// reviews coming from the website have a null "channel" property
				array_push($clauses, "`channel` IS NULL");
			} else {
				array_push($clauses, "`channel`=".$dbo->quote($filters['channel']));
			}
		}
		if (!empty($filters['lang'])) {
			array_push($clauses, "`lang`=".$dbo->quote($filters['lang']));
		}
		if (!empty($filters['country'])) {
			array_push($clauses, "`country`=".$dbo->quote($filters['country']));
		}
		if (!empty($filters['prop_name'])) {
			array_push($clauses, "`prop_name`=".$dbo->quote($filters['prop_name']));
		}

		// specific review ID filter can be set by VBO
		$revid = VikRequest::getInt('revid', '', 'request');
		if (!empty($revid)) {
			array_push($clauses, "`id`=".$dbo->quote($revid));
		}

		$q = "SELECT SQL_CALC_FOUND_ROWS * FROM `#__vikchannelmanager_otareviews` ".(count($clauses) ? 'WHERE '.implode(' AND ', $clauses).' ' : '')."ORDER BY `{$ordering}` {$orderingDir}";
		$dbo->setQuery($q, $lim0, $lim);
		$rows = $dbo->loadAssocList();
		if ($rows) {
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
			$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
		}

		// active channel
		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
		// try to get the property name from rooms mapping
		$channel['prop_name'] = '';
		$q = "SELECT `prop_params`,`prop_name` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=".(int)$channel['uniquekey'].";";
		$dbo->setQuery($q);
		$prop_data = $dbo->loadAssocList();
		if ($prop_data > 0) {
			foreach ($prop_data as $pdata) {
				if (empty($pdata['prop_params']) || empty($pdata['prop_name'])) {
					continue;
				}
				$pdata['prop_params'] = json_decode($pdata['prop_params'], true);
				foreach ($pdata['prop_params'] as $paramk => $paramv) {
					foreach ($channel['params'] as $chk => $chv) {
						// the very first channel param key must match with the mapping data (i.e. 'hotelid')
						if ($paramk == $chk && $paramv == $chv) {
							$channel['prop_name'] = $pdata['prop_name'];
							// we break all loops as we've found what we need
							break 3;
						}
					}
				}
			}
		}

		// load all global scores without pagination
		$global_scores = array();
		$q = "SELECT * FROM `#__vikchannelmanager_otascores` ORDER BY `score` DESC, `channel` ASC;";
		$dbo->setQuery($q);
		$global_scores = $dbo->loadAssocList();
		
		$this->rows = $rows;
		$this->orderingDir = $orderingDir;
		$this->ordering = $ordering;
		$this->navbut = $navbut;
		$this->filters = $filters;
		$this->langs = $langs;
		$this->countries = $countries;
		$this->channels = $channels;
		$this->propnames = $propnames;
		$this->channel = $channel;
		$this->global_scores = $global_scores;
		
		// Display the template (default.php)
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTREVIEWS'), 'vikchannelmanager');
		JToolBarHelper::deleteList(JText::_('VCMREMOVECONFIRM'), 'removereviews', JText::_('REMOVE'));
		JToolBarHelper::spacer();
		JToolBarHelper::cancel('cancel', JText::_('CANCEL'));
		JToolBarHelper::spacer();
	}
}
