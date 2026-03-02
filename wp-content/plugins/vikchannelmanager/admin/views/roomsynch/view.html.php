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

class VikChannelManagerViewroomsynch extends JViewUI {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();
		
		VCM::load_css_js();
	
		$config = VikChannelManager::loadConfiguration();
		$channel = VikChannelManager::getActiveModule(true);
		
		$this->config = $config;
		$this->channel = $channel;
		
		// Display the template (default.php)
		parent::display($tpl);
		
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTROOMSYNCH'), 'vikchannelmanager');
		JToolBarHelper::save('savesynch', JText::_('SAVE'));
		JToolBarHelper::spacer();
		JToolBarHelper::cancel('cancelsynch', JText::_('CANCEL'));
		JToolBarHelper::spacer();
		
	}
}
