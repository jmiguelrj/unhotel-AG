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

class VikChannelManagerViewappconfig extends JViewUI 
{
	
	function display($tpl = null) 
	{
		// Set the toolbar
		$this->addToolBar();
		
		VCM::load_css_js();
		
		$dbo = JFactory::getDbo();

		$appParams = array();

		$module = VikChannelManager::getActiveModule(true);
	
		$config = VikChannelManager::loadConfiguration();

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);

		$q = "SELECT `setting`
			FROM `#__vikchannelmanager_config`
			WHERE `param` = 'app_settings';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$appParams = $dbo->loadAssoc();
			$appParams = json_decode($appParams['setting'], true);
		} else {
			$appParams = array();
		}

		$q = "SELECT `setting` 
			FROM `#__vikchannelmanager_config` 
			WHERE `param` = 'app_accounts'";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$app_accounts = $dbo->loadAssoc();
			$app_accounts = json_decode($app_accounts['setting'],true);
			$accountsNumber = count($app_accounts);
		} else {
			$app_accounts = array();
			$accountsNumber = 0;
		}

		$q = "SELECT `setting` 
			FROM `#__vikchannelmanager_config` 
			WHERE `param` = 'app_acl'";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$app_acl = $dbo->loadAssoc();
			$app_acl = json_decode($app_acl['setting'],true);
		} else {
			$app_acl = array();
		}


		$this->config = $config;
		$this->appParams = $appParams;
		$this->accountsNumber = $accountsNumber;
		$this->app_accounts = $app_accounts;
		$this->app_acl = $app_acl;
		
		// Display the template (default.php)
		parent::display($tpl);
		
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMAPPCONFTIT'), 'vikchannelmanager');
		JToolBarHelper::apply('saveAppConfig', JText::_('SAVE'));
		JToolBarHelper::spacer();
		JToolBarHelper::cancel('cancel', JText::_('CANCEL'));
		JToolBarHelper::spacer();	
	}
}