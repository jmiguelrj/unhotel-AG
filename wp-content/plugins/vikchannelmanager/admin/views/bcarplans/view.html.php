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

class VikChannelManagerViewbcarplans extends JViewUI {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();
		
		VCM::load_css_js();
		
		$dbo = JFactory::getDbo();

		$module = VikChannelManager::getActiveModule(true);
	
		$config = VikChannelManager::loadConfiguration();

		$oldData = array();

		$e4j_debug = VikRequest::getInt('e4j_debug');

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);

		$correctPlans = array();

		if($module['av_enabled'] == 1) {
			$q = "SELECT `otapricing`,`prop_params` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=".(int)$module['uniquekey'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			if( $dbo->getNumRows() > 0 ) {
				$result = $dbo->loadAssocList();
				foreach ($result as $ratep) {
					if(!empty($ratep['otapricing'])) {
						$ratePlans = json_decode($ratep['otapricing'],true);
						$propertyParams = json_decode($ratep['prop_params'],true);
						$hotelID = $propertyParams['hotelid'];
						if($hotelID == $channel['params']['hotelid']){
							foreach($ratePlans['RatePlan'] as $key => $single){
								$correctPlans[$key] = $single;
							}
						}
					}
				}
			}
		}

		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='bcarplans".$channel['params']['hotelid']."';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$oldData = $dbo->loadAssoc();
			$oldData = json_decode($oldData['setting'],true);
			if(!empty($oldData) && $e4j_debug == 1){
				echo "</br><strong>Printing Saved Data</strong></br>";
				echo "<pre>".print_r($oldData,true)."</pre>";
			}
		}

		$session = JFactory::getSession();
		if($session->has('vcmbcarplans')){
			$sessionValues = json_decode($session->get('vcmbcarplans'),true);
		}

		$this->config = $config;
		$this->ratePlans = $correctPlans;
		$this->oldData = $oldData;
		$this->sessionValues = $sessionValues;
		
		// Display the template (default.php)
		parent::display($tpl);
		
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTBCARPLANS'), 'vikchannelmanager');
		JToolBarHelper::save('bca.makeRatesXml', JText::_('SAVE'));
		JToolBarHelper::spacer();
		JToolBarHelper::cancel( 'cancel', JText::_('CANCEL'));
		JToolBarHelper::spacer();	
	}
}
