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

class VikChannelManagerViewtacstatus extends JViewUI {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();
		
		VCM::load_css_js();
		
		$dbo = JFactory::getDbo();
		
		$partner_id = VikChannelManager::getTripConnectPartnerID();
		$tac_api_key = VikChannelManager::getTripConnectApiKey();
		
		$_url = 'https://api.tripadvisor.com/api/partner/1.0/location_mappings/'.$partner_id.'?key='.$tac_api_key;
		
		$ch = curl_init($_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$rs = curl_exec($ch);
		if(curl_errno($ch)) {
			VikError::raiseWarning('', $rs);
			return;
		}
		curl_close($ch);
		
		$rs = json_decode($rs, true);
				
		$this->contents = $rs;
		
		// Display the template (default.php)
		parent::display($tpl);
		
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTSTATUSTAC'), 'vikchannelmanager');
		
	}
	
}
