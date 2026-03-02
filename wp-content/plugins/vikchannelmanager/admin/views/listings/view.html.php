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

class VikChannelManagerViewlistings extends JViewUI
{
	public function display($tpl = null)
	{
		$module = VikChannelManager::getActiveModule(true);

		$app = JFactory::getApplication();
		$lim = $app->getUserStateFromRequest("com_vikchannelmanager.limit", 'limit', $app->get('list_limit'), 'int');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
		$navbut = '';

		// Set the toolbar
		$this->addToolBar($module);

		VCM::load_css_js();

		$api_key = VikChannelManager::getApiKey(true);

		$dbo = JFactory::getDbo();

		$q = "SELECT * FROM `#__vikchannelmanager_listings` WHERE `channel`=".$dbo->quote($module['uniquekey'] . (isset($module['ical_channel']) ? '-' . $module['ical_channel']['id'] : '')).";";
		$dbo->setQuery($q);
		$properties = $dbo->loadAssocList();

		$q = "SELECT SQL_CALC_FOUND_ROWS `id`, `name`, `units`, `smalldesc`, `img` FROM `#__vikbooking_rooms` ORDER BY `name`";
		$dbo->setQuery($q, $lim0, $lim);
		$vb_rooms = $dbo->loadAssocList();
		if ($vb_rooms) {
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
			$navbut="<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
		}

		foreach ($vb_rooms as $index => $r) {
			$vb_rooms[$index]['retrieval_url'] = "";
			$vb_rooms[$index]['id_assoc'] = -1;
			/**
			 * The download URL points internally.
			 * 
			 * @since 	1.8.23
			 */
			$vb_rooms[$index]['prev_download_url'] = "https://e4jconnect.com/ical/" . $module['name'] . "/$api_key/" . $r['id'];
			$vb_rooms[$index]['download_url'] = JUri::root() . 'index.php?option=com_vikchannelmanager&task=get_ical&id_room=' . $r['id'] . '&auth=' . md5($api_key);

			foreach ($properties as $p) {
				if ($r['id'] == $p['id_vb_room']) {
					$vb_rooms[$index]['retrieval_url'] = $p['retrieval_url'];
					$vb_rooms[$index]['id_assoc'] = $p['id'];
				}
			}
		}

		$this->listings = $vb_rooms;
		$this->module = $module;
		$this->navbut = $navbut;

		// Display the template (default.php)
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar($module)
	{
		//Add menu title and some buttons to the page

		$unique_key = $module['uniquekey'];

		$title = "";

		switch($unique_key) {
			case VikChannelManagerConfig::AIRBNB: $title = JText::_('VCMMAINAIRBNBLISTINGS'); break;
			case VikChannelManagerConfig::FLIPKEY: $title = JText::_('VCMMAINFLIPKEYLISTINGS'); break;
			case VikChannelManagerConfig::HOLIDAYLETTINGS: $title = JText::_('VCMMAINHOLIDAYLETTINGSLISTINGS'); break;
			case VikChannelManagerConfig::WIMDU: $title = JText::_('VCMMAINWIMDULISTINGS'); break;
			case VikChannelManagerConfig::HOMEAWAY: $title = JText::_('VCMMAINHOMEAWAYLISTINGS'); break;
			case VikChannelManagerConfig::VRBO: $title = JText::_('VCMMAINVRBOLISTINGS'); break;
			default : $title = JText::sprintf('VCMMAINICALLISTINGS', ucwords((isset($module['ical_channel']) ? $module['ical_channel']['name'] : $module['name'])));
		}

		JToolBarHelper::title($title, 'vikchannelmanager');
		JToolBarHelper::apply('saveListings', JText::_('SAVE'));
		JToolBarHelper::spacer();
	}
}
