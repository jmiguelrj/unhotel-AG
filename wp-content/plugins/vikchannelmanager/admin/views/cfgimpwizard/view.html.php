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

jimport('joomla.application.component.view');

class VikChannelManagerViewCfgimpwizard extends JViewUI {
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();
		
		VCM::load_css_js();

		$dbo = JFactory::getDbo();
		$session = JFactory::getSession();
		$mainframe = JFactory::getApplication();

		if (!class_exists('VikBooking') && file_exists(VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php')) {
			// we need the main vbo lib to access the application and render the yes/no buttons
			require_once (VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php");
		}

		$module = VikChannelManager::getActiveModule(true);
		$module['params'] = json_decode($module['params'], true);

		$req_channel = VikChannelManagerConfig::BOOKING;
		if ($module['av_enabled'] == 1) {
			$req_channel = $module['uniquekey'];
		}

		$hotelid = '';
		foreach ($module['params'] as $param_name => $param_value) {
			// grab the first channel parameter
			$hotelid = $param_value;
			break;
		}
		if (empty($hotelid)) {
			VikError::raiseWarning('', 'Please make sure to enter your channel account details before proceeding.');
			// redirect and exit
			$mainframe->redirect("index.php?option=com_vikchannelmanager&task=config");
			exit;
		}

		$config_data = '';
		$cfg_param_name = "cfgimp_{$req_channel}_{$hotelid}";
		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`=" . $dbo->quote($cfg_param_name) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$config_data = $dbo->loadResult();
		}
		if (!empty($config_data)) {
			$config_data = json_decode($config_data, true);
		}

		if (empty($config_data) || !is_array($config_data)) {
			VikError::raiseWarning('', 'No valid data found for importing the configuration from the channel.');
			$mainframe->redirect("index.php?option=com_vikchannelmanager");
			exit;
		}

		$this->config_data = $config_data;
		
		parent::display($tpl);
	}
	
	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		// Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMCONFIGIMPORTTITLE'), 'vikchannelmanager');
		
		JToolBarHelper::cancel( 'cancel', JText::_('BACK'));
	}

}
