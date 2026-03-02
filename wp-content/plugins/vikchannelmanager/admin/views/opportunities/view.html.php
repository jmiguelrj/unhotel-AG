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

class VikChannelManagerViewopportunities extends JViewUI {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();
		
		VCM::load_css_js();

		$debug_val = VikRequest::getInt('e4j_debug', 0, 'request');
		$opp = VikChannelManager::getOpportunityInstance();

		// get active module
		$channel = VikChannelManager::getActiveModule(true);

		// download opportunities if it's time to do it
		if ($opp->shouldRequestOpportunities() || $debug_val > 0) {
			$opp->downloadOpportunities($channel['uniquekey']);
			if ($opp->hasError()) {
				VikError::raiseWarning('', $opp->getError());
			}
		}

		// load all opportunities by default
		$rows = $opp->loadOpportunities(array(), null, null);

		// load rooms mapped for each account for all channels
		$ch_acc_rooms  = array();
		$ch_av_enabled = VikChannelManager::getAllAvChannels();
		foreach ($ch_av_enabled as $ch_key => $ch_name) {
			$ch_acc_rooms[$ch_name] = VikChannelManager::getChannelAccountsMapped($ch_key, true);
		}
		
		$this->rows = $rows;
		$this->ch_acc_rooms = $ch_acc_rooms;
		
		// Display the template (default.php)
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		// add menu title and buttons
		JToolBarHelper::title(JText::_('VCMMAINTOPPORTUNITIES'), 'vikchannelmanager');
		JToolBarHelper::cancel('cancel', JText::_('CANCEL'));
		JToolBarHelper::spacer();
	}
}
