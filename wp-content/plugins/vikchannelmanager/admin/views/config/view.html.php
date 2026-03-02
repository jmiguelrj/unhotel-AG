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

class VikChannelManagerViewconfig extends JViewUI
{
	public function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();
		
		VCM::load_css_js();
		
		$app = JFactory::getApplication();
		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();

		if ($app->input->getInt('ota_data_received')) {
			// make sure to clear the user state in order to see the latest channel account data
			$app->setUserState('vcm.moduleactive', null);
		}
		
		$config = VikChannelManager::loadConfiguration();
		
		$module = VikChannelManager::getActiveModule(true);

		$fromwizard = VikRequest::getInt('fromwizard', 0, 'request');
		if ($fromwizard > 0) {
			VikError::raiseNotice('', JText::_('VCMWIZARDCONFIGCHANNEL'));
		}

		$more_accounts = array();
		if ($module['av_enabled'] == 1) {
			//this query below is safe with the error #1055 when sql_mode=only_full_group_by for the aggregate functions
			//Important: do not change the order by clause becuase the task rmchaccount (for the account removal) takes the index of the associative array returned by this query
			$q = "SELECT `prop_name`,`prop_params`, COUNT(DISTINCT `idroomota`) AS `tot_rooms` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=".(int)$module['uniquekey']." GROUP BY `prop_name`,`prop_params` ORDER BY `prop_name` ASC;";
			$dbo->setQuery($q);
			$other_accounts = $dbo->loadAssocList();
			if ($other_accounts && count($other_accounts) > 1) {
				foreach ($other_accounts as $oacc) {
					if (!empty($oacc['prop_params'])) {
						$oacc['active'] = $oacc['prop_params'] == $module['params'] ? 1 : 0;
						$more_accounts[] = $oacc;
					}
				}
				if (!(count($more_accounts) > 1)) {
					$more_accounts = array();
				}
			}
		}

		if (!empty($module['id'])) {
			$module['params'] 	= (array)json_decode($module['params'], true);
			$module['settings'] = (array)json_decode($module['settings'], true);
		}

		$q = "SELECT `id`, `name`, `published` FROM `#__vikbooking_gpayments` ORDER BY `published` DESC, `name` ASC;";
		$dbo->setQuery($q);
		$vb_payments = $dbo->loadAssocList();

		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param` = 'app_accounts'";
		$dbo->setQuery($q);
		$app_accounts = $dbo->loadAssoc();
		$app_accounts = $app_accounts ? $app_accounts : [];

		$force_insert = '';
		$newbcahid = VikRequest::getString('newbcahid');
		if ($module['uniquekey'] == VikChannelManagerConfig::BOOKING && !empty($newbcahid)) {
			$force_insert = $newbcahid;
		}

		$last_endpoint = VikChannelManager::getLastEndpoint();
		$first_summary = 0;
		if (in_array($module['uniquekey'], array(VikChannelManagerConfig::BOOKING, VikChannelManagerConfig::EXPEDIA, VikChannelManagerConfig::AIRBNBAPI))) {
			$first_summary = VikChannelManager::checkFirstBookingSummary($module['uniquekey']);
		}

		$config_import = 0;
		if ($module['uniquekey'] == VikChannelManagerConfig::BOOKING && VikChannelManager::checkImportChannelConfig($module['uniquekey']) > 0) {
			$config_import = 1;
		}

		/**
		 * Some channels, like Airbnb API, may need to request custom params.
		 * 
		 * @since 	1.8.0
		 */
		$ch_custom_params = $module['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? VikChannelManager::getCustomChParams($module['uniquekey']) : array();
		$force_custom_ch_params = VikRequest::getInt('force_custom_ch_params', 0, 'request');
		if ($module['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI && (VikChannelManager::shouldReloadCustomChParams($module['uniquekey']) || $force_custom_ch_params)) {
			$ch_custom_params = VikChannelManager::downloadCustomChParams($module['uniquekey']);
		}
		//
		
		$this->config = $config;
		$this->module = $module;
		$this->more_accounts = $more_accounts;
		$this->vb_payments = $vb_payments;
		$this->force_insert = $force_insert;
		$this->app_accounts = $app_accounts;
		$this->last_endpoint = $last_endpoint;
		$this->first_summary = $first_summary;
		$this->config_import = $config_import;
		$this->ch_custom_params = $ch_custom_params;
		
		// Display the template (default.php)
		parent::display($tpl);
		
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTCONFIG'), 'vikchannelmanager');
		JToolBarHelper::apply('saveconfig', JText::_('SAVE'));
		JToolBarHelper::spacer();
		JToolBarHelper::cancel('cancel', JText::_('CANCEL'));
		JToolBarHelper::spacer();
	}
}
