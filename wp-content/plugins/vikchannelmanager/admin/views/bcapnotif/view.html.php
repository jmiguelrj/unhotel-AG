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

class VikChannelManagerViewbcapnotif extends JViewUI {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();
		
		VCM::load_css_js();
		
		$dbo = JFactory::getDbo();

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);
	
		$config = VikChannelManager::loadConfiguration();
		$module = VikChannelManager::getActiveModule(true);
		$newRoom = VikRequest::getInt('newRoom');
		$e4j_debug = VikRequest::getInt('e4j_debug');

		$actionSelected = VikRequest::getString("selected-option");


		$roomID = "";
		$roomName = "";
		$hotelID = "";

		if(!empty($actionSelected)){
			$actionSelected = explode("-", $actionSelected);
			$roomID = $actionSelected[0];
			$roomName = $actionSelected[1];
			$hotelID = $actionSelected[2];
		}

		$validPolicies = array();
		$validRatePlans = array();


		if($module['av_enabled'] == 1) {
			$q = "SELECT `otaroomname`,`idroomota`,`prop_params` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=".(int)$module['uniquekey'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			if( $dbo->getNumRows() > 0 ) {
				$result = $dbo->loadAssocList();
				foreach ($result as $roomc) {
					if(!empty($roomc['idroomota'])) {
						$roomsInfo['RoomName'][] = $roomc['otaroomname'];
						$roomsInfo['RoomId'][] = $roomc['idroomota'];
						$roomsInfo['HotelId'][] = json_decode($roomc['prop_params'],true)['hotelid'];
					}
				}
			}
		}

		if(empty($hotelID)){
			$hotelID = $channel['params']['hotelid'];
		}

		$session = JFactory::getSession();
		if($session->has('vcmbcapnotif'.$roomID)){
			$sessionValues = json_decode($session->get('vcmbcapnotif'.$roomID),true);
		}

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);

		if(!empty($actionSelected)){
			$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='bcahcont".$hotelID."';";
			$dbo->setQuery($q);
			$dbo->execute();
			//echo "<pre>".print_r($_REQUEST, true)."</pre>";die;
			if ($dbo->getNumRows() > 0) {
				$hotelData = json_decode($dbo->loadAssoc()['setting'],true);
				//echo "<pre>".print_r($hotelData,true)."</pre>";
				if(count($hotelData)){
					foreach ($hotelData['cancelpoliciesIndexes'] as $index) {
						$validPolicies[] = $hotelData['cancelpolicies'][$index]['cancelpolicy'];
					}
				}
			}

			$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param` = 'bcarplans".$hotelID."';";
			$dbo->setQuery($q);
			$dbo->execute();
			//echo "<pre>".print_r($_REQUEST, true)."</pre>";die;
			if ($dbo->getNumRows() > 0) {
				$ratePlanData = json_decode($dbo->loadAssoc()['setting'],true);
				if(count($ratePlanData)){
					foreach ($ratePlanData['ratePlans'] as $index => $ratePlan) {
						$validRatePlans[$index]['id'] = $ratePlan['hiddenID'];
						$validRatePlans[$index]['name'] = $ratePlan['name'];
					}
				}
			}

			$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='bcapnotif".$roomID."';";
			$dbo->setQuery($q);
			$dbo->execute();
			//echo "<pre>".print_r($_REQUEST, true)."</pre>";die;
			if ($dbo->getNumRows() > 0) {
				$oldData = json_decode($dbo->loadAssoc()['setting'],true);
			}
		}

		$possiblePolicies = array();

		$possiblePolicies[1]['value'] = 1;
		$possiblePolicies[12]['value'] = 12;
		$possiblePolicies[14]['value'] = 14;
		$possiblePolicies[15]['value'] = 15;
		$possiblePolicies[16]['value'] = 16;
		$possiblePolicies[29]['value'] = 29;
		$possiblePolicies[30]['value'] = 30;
		$possiblePolicies[31]['value'] = 31;
		$possiblePolicies[33]['value'] = 33;
		$possiblePolicies[34]['value'] = 34;
		$possiblePolicies[36]['value'] = 36;
		$possiblePolicies[37]['value'] = 37;
		$possiblePolicies[38]['value'] = 38;
		$possiblePolicies[41]['value'] = 41;
		$possiblePolicies[42]['value'] = 42;
		$possiblePolicies[43]['value'] = 43;
		$possiblePolicies[44]['value'] = 44;
		$possiblePolicies[45]['value'] = 45;
		$possiblePolicies[46]['value'] = 46;
		$possiblePolicies[47]['value'] = 47;
		$possiblePolicies[48]['value'] = 48;
		$possiblePolicies[49]['value'] = 49;
		$possiblePolicies[50]['value'] = 50;
		$possiblePolicies[51]['value'] = 51;
		$possiblePolicies[52]['value'] = 52;
		$possiblePolicies[53]['value'] = 53;
		$possiblePolicies[54]['value'] = 54;
		$possiblePolicies[55]['value'] = 55;
		$possiblePolicies[56]['value'] = 56;
		$possiblePolicies[57]['value'] = 57;
		$possiblePolicies[58]['value'] = 58;
		$possiblePolicies[59]['value'] = 59;
		$possiblePolicies[60]['value'] = 60;
		$possiblePolicies[61]['value'] = 61;
		$possiblePolicies[62]['value'] = 62;
		$possiblePolicies[63]['value'] = 63;
		$possiblePolicies[64]['value'] = 64;
		$possiblePolicies[65]['value'] = 65;
		$possiblePolicies[66]['value'] = 66;
		$possiblePolicies[67]['value'] = 67;
		$possiblePolicies[68]['value'] = 68;
		$possiblePolicies[69]['value'] = 69;
		$possiblePolicies[70]['value'] = 70;
		$possiblePolicies[71]['value'] = 71;
		$possiblePolicies[72]['value'] = 72;
		$possiblePolicies[73]['value'] = 73;
		$possiblePolicies[74]['value'] = 74;
		$possiblePolicies[113]['value'] = 113;
		$possiblePolicies[114]['value'] = 114;
		$possiblePolicies[115]['value'] = 115;
		$possiblePolicies[116]['value'] = 116;
		$possiblePolicies[117]['value'] = 117;
		$possiblePolicies[118]['value'] = 118;
		$possiblePolicies[119]['value'] = 119;
		$possiblePolicies[120]['value'] = 120;
		$possiblePolicies[121]['value'] = 121;
		$possiblePolicies[2]['value'] = 2;
		$possiblePolicies[4]['value'] = 4;
		$possiblePolicies[5]['value'] = 5;
		$possiblePolicies[6]['value'] = 6;
		$possiblePolicies[7]['value'] = 7;
		$possiblePolicies[9]['value'] = 9;
		$possiblePolicies[10]['value'] = 10;
		$possiblePolicies[11]['value'] = 11;
		$possiblePolicies[75]['value'] = 75;
		$possiblePolicies[76]['value'] = 76;
		$possiblePolicies[77]['value'] = 77;
		$possiblePolicies[78]['value'] = 78;
		$possiblePolicies[79]['value'] = 79;
		$possiblePolicies[80]['value'] = 80;
		$possiblePolicies[81]['value'] = 81;
		$possiblePolicies[82]['value'] = 82;
		$possiblePolicies[83]['value'] = 83;
		$possiblePolicies[84]['value'] = 84;
		$possiblePolicies[85]['value'] = 85;
		$possiblePolicies[86]['value'] = 86;
		$possiblePolicies[87]['value'] = 87;
		$possiblePolicies[88]['value'] = 88;
		$possiblePolicies[89]['value'] = 89;
		$possiblePolicies[90]['value'] = 90;
		$possiblePolicies[91]['value'] = 91;
		$possiblePolicies[92]['value'] = 92;
		$possiblePolicies[93]['value'] = 93;
		$possiblePolicies[94]['value'] = 94;
		$possiblePolicies[95]['value'] = 95;
		$possiblePolicies[96]['value'] = 96;
		$possiblePolicies[97]['value'] = 97;
		$possiblePolicies[98]['value'] = 98;
		$possiblePolicies[99]['value'] = 99;
		$possiblePolicies[100]['value'] = 100;
		$possiblePolicies[101]['value'] = 101;
		$possiblePolicies[102]['value'] = 102;
		$possiblePolicies[122]['value'] = 122;
		$possiblePolicies[123]['value'] = 123;
		$possiblePolicies[124]['value'] = 124;
		$possiblePolicies[125]['value'] = 125;
		$possiblePolicies[126]['value'] = 126;
		$possiblePolicies[127]['value'] = 127;
		$possiblePolicies[128]['value'] = 128;
		$possiblePolicies[129]['value'] = 129;
		$possiblePolicies[130]['value'] = 130;
		$possiblePolicies[131]['value'] = 131;
		$possiblePolicies[132]['value'] = 132;
		$possiblePolicies[133]['value'] = 133;
		$possiblePolicies[134]['value'] = 134;
		$possiblePolicies[135]['value'] = 135;
		$possiblePolicies[136]['value'] = 136;
		$possiblePolicies[137]['value'] = 137;
		$possiblePolicies[138]['value'] = 138;
		$possiblePolicies[139]['value'] = 139;
		$possiblePolicies[1]['text'] = JText::_('VCMBCAHPOLTYPE1');
		$possiblePolicies[12]['text'] = JText::_('VCMBCAHPOLTYPE2');
		$possiblePolicies[14]['text'] = JText::_('VCMBCAHPOLTYPE3');
		$possiblePolicies[15]['text'] = JText::_('VCMBCAHPOLTYPE4');
		$possiblePolicies[16]['text'] = JText::_('VCMBCAHPOLTYPE5');
		$possiblePolicies[29]['text'] = JText::_('VCMBCAHPOLTYPE6');
		$possiblePolicies[30]['text'] = JText::_('VCMBCAHPOLTYPE7');
		$possiblePolicies[31]['text'] = JText::_('VCMBCAHPOLTYPE8');
		$possiblePolicies[33]['text'] = JText::_('VCMBCAHPOLTYPE9');
		$possiblePolicies[34]['text'] = JText::_('VCMBCAHPOLTYPE10');
		$possiblePolicies[36]['text'] = JText::_('VCMBCAHPOLTYPE11');
		$possiblePolicies[37]['text'] = JText::_('VCMBCAHPOLTYPE12');
		$possiblePolicies[38]['text'] = JText::_('VCMBCAHPOLTYPE13');
		$possiblePolicies[41]['text'] = JText::_('VCMBCAHPOLTYPE14');
		$possiblePolicies[42]['text'] = JText::_('VCMBCAHPOLTYPE15');
		$possiblePolicies[43]['text'] = JText::_('VCMBCAHPOLTYPE16');
		$possiblePolicies[44]['text'] = JText::_('VCMBCAHPOLTYPE17');
		$possiblePolicies[45]['text'] = JText::_('VCMBCAHPOLTYPE18');
		$possiblePolicies[46]['text'] = JText::_('VCMBCAHPOLTYPE19');
		$possiblePolicies[47]['text'] = JText::_('VCMBCAHPOLTYPE20');
		$possiblePolicies[48]['text'] = JText::_('VCMBCAHPOLTYPE21');
		$possiblePolicies[49]['text'] = JText::_('VCMBCAHPOLTYPE22');
		$possiblePolicies[50]['text'] = JText::_('VCMBCAHPOLTYPE23');
		$possiblePolicies[51]['text'] = JText::_('VCMBCAHPOLTYPE24');
		$possiblePolicies[52]['text'] = JText::_('VCMBCAHPOLTYPE25');
		$possiblePolicies[53]['text'] = JText::_('VCMBCAHPOLTYPE26');
		$possiblePolicies[54]['text'] = JText::_('VCMBCAHPOLTYPE27');
		$possiblePolicies[55]['text'] = JText::_('VCMBCAHPOLTYPE28');
		$possiblePolicies[56]['text'] = JText::_('VCMBCAHPOLTYPE29');
		$possiblePolicies[57]['text'] = JText::_('VCMBCAHPOLTYPE30');
		$possiblePolicies[58]['text'] = JText::_('VCMBCAHPOLTYPE31');
		$possiblePolicies[59]['text'] = JText::_('VCMBCAHPOLTYPE32');
		$possiblePolicies[60]['text'] = JText::_('VCMBCAHPOLTYPE33');
		$possiblePolicies[61]['text'] = JText::_('VCMBCAHPOLTYPE34');
		$possiblePolicies[62]['text'] = JText::_('VCMBCAHPOLTYPE35');
		$possiblePolicies[63]['text'] = JText::_('VCMBCAHPOLTYPE36');
		$possiblePolicies[64]['text'] = JText::_('VCMBCAHPOLTYPE37');
		$possiblePolicies[65]['text'] = JText::_('VCMBCAHPOLTYPE38');
		$possiblePolicies[66]['text'] = JText::_('VCMBCAHPOLTYPE39');
		$possiblePolicies[67]['text'] = JText::_('VCMBCAHPOLTYPE40');
		$possiblePolicies[68]['text'] = JText::_('VCMBCAHPOLTYPE41');
		$possiblePolicies[69]['text'] = JText::_('VCMBCAHPOLTYPE42');
		$possiblePolicies[70]['text'] = JText::_('VCMBCAHPOLTYPE43');
		$possiblePolicies[71]['text'] = JText::_('VCMBCAHPOLTYPE44');
		$possiblePolicies[72]['text'] = JText::_('VCMBCAHPOLTYPE45');
		$possiblePolicies[73]['text'] = JText::_('VCMBCAHPOLTYPE46');
		$possiblePolicies[74]['text'] = JText::_('VCMBCAHPOLTYPE47');
		$possiblePolicies[113]['text'] = JText::_('VCMBCAHPOLTYPE48');
		$possiblePolicies[114]['text'] = JText::_('VCMBCAHPOLTYPE49');
		$possiblePolicies[115]['text'] = JText::_('VCMBCAHPOLTYPE50');
		$possiblePolicies[116]['text'] = JText::_('VCMBCAHPOLTYPE51');
		$possiblePolicies[117]['text'] = JText::_('VCMBCAHPOLTYPE52');
		$possiblePolicies[118]['text'] = JText::_('VCMBCAHPOLTYPE53');
		$possiblePolicies[119]['text'] = JText::_('VCMBCAHPOLTYPE54');
		$possiblePolicies[120]['text'] = JText::_('VCMBCAHPOLTYPE55');
		$possiblePolicies[121]['text'] = JText::_('VCMBCAHPOLTYPE56');
		$possiblePolicies[2]['text'] = JText::_('VCMBCAHPOLTYPE57');
		$possiblePolicies[4]['text'] = JText::_('VCMBCAHPOLTYPE58');
		$possiblePolicies[5]['text'] = JText::_('VCMBCAHPOLTYPE59');
		$possiblePolicies[6]['text'] = JText::_('VCMBCAHPOLTYPE60');
		$possiblePolicies[7]['text'] = JText::_('VCMBCAHPOLTYPE61');
		$possiblePolicies[9]['text'] = JText::_('VCMBCAHPOLTYPE62');
		$possiblePolicies[10]['text'] = JText::_('VCMBCAHPOLTYPE63');
		$possiblePolicies[11]['text'] = JText::_('VCMBCAHPOLTYPE64');
		$possiblePolicies[75]['text'] = JText::_('VCMBCAHPOLTYPE65');
		$possiblePolicies[76]['text'] = JText::_('VCMBCAHPOLTYPE66');
		$possiblePolicies[77]['text'] = JText::_('VCMBCAHPOLTYPE67');
		$possiblePolicies[78]['text'] = JText::_('VCMBCAHPOLTYPE68');
		$possiblePolicies[79]['text'] = JText::_('VCMBCAHPOLTYPE69');
		$possiblePolicies[80]['text'] = JText::_('VCMBCAHPOLTYPE70');
		$possiblePolicies[81]['text'] = JText::_('VCMBCAHPOLTYPE71');
		$possiblePolicies[82]['text'] = JText::_('VCMBCAHPOLTYPE72');
		$possiblePolicies[83]['text'] = JText::_('VCMBCAHPOLTYPE73');
		$possiblePolicies[84]['text'] = JText::_('VCMBCAHPOLTYPE74');
		$possiblePolicies[85]['text'] = JText::_('VCMBCAHPOLTYPE75');
		$possiblePolicies[86]['text'] = JText::_('VCMBCAHPOLTYPE76');
		$possiblePolicies[87]['text'] = JText::_('VCMBCAHPOLTYPE77');
		$possiblePolicies[88]['text'] = JText::_('VCMBCAHPOLTYPE78');
		$possiblePolicies[89]['text'] = JText::_('VCMBCAHPOLTYPE79');
		$possiblePolicies[90]['text'] = JText::_('VCMBCAHPOLTYPE80');
		$possiblePolicies[91]['text'] = JText::_('VCMBCAHPOLTYPE81');
		$possiblePolicies[92]['text'] = JText::_('VCMBCAHPOLTYPE82');
		$possiblePolicies[93]['text'] = JText::_('VCMBCAHPOLTYPE83');
		$possiblePolicies[94]['text'] = JText::_('VCMBCAHPOLTYPE84');
		$possiblePolicies[95]['text'] = JText::_('VCMBCAHPOLTYPE85');
		$possiblePolicies[96]['text'] = JText::_('VCMBCAHPOLTYPE86');
		$possiblePolicies[97]['text'] = JText::_('VCMBCAHPOLTYPE87');
		$possiblePolicies[98]['text'] = JText::_('VCMBCAHPOLTYPE88');
		$possiblePolicies[99]['text'] = JText::_('VCMBCAHPOLTYPE89');
		$possiblePolicies[100]['text'] = JText::_('VCMBCAHPOLTYPE90');
		$possiblePolicies[101]['text'] = JText::_('VCMBCAHPOLTYPE91');
		$possiblePolicies[102]['text'] = JText::_('VCMBCAHPOLTYPE92');
		$possiblePolicies[122]['text'] = JText::_('VCMBCAHPOLTYPE93');
		$possiblePolicies[123]['text'] = JText::_('VCMBCAHPOLTYPE94');
		$possiblePolicies[124]['text'] = JText::_('VCMBCAHPOLTYPE95');
		$possiblePolicies[125]['text'] = JText::_('VCMBCAHPOLTYPE96');
		$possiblePolicies[126]['text'] = JText::_('VCMBCAHPOLTYPE97');
		$possiblePolicies[127]['text'] = JText::_('VCMBCAHPOLTYPE98');
		$possiblePolicies[128]['text'] = JText::_('VCMBCAHPOLTYPE99');
		$possiblePolicies[129]['text'] = JText::_('VCMBCAHPOLTYPE100');
		$possiblePolicies[130]['text'] = JText::_('VCMBCAHPOLTYPE101');
		$possiblePolicies[131]['text'] = JText::_('VCMBCAHPOLTYPE102');
		$possiblePolicies[132]['text'] = JText::_('VCMBCAHPOLTYPE103');
		$possiblePolicies[133]['text'] = JText::_('VCMBCAHPOLTYPE104');
		$possiblePolicies[134]['text'] = JText::_('VCMBCAHPOLTYPE105');
		$possiblePolicies[135]['text'] = JText::_('VCMBCAHPOLTYPE106');
		$possiblePolicies[136]['text'] = JText::_('VCMBCAHPOLTYPE107');
		$possiblePolicies[137]['text'] = JText::_('VCMBCAHPOLTYPE108');
		$possiblePolicies[138]['text'] = JText::_('VCMBCAHPOLTYPE109');
		$possiblePolicies[139]['text'] = JText::_('VCMBCAHPOLTYPE110');

		if($session->has('newroominfo')){
			$newRoomInfo = $session->get('newroominfo');
		}

		$this->config = $config;
		$this->validPolicies = $validPolicies;
		$this->validRatePlans = $validRatePlans;
		$this->roomsInfo = $roomsInfo;
		$this->oldData = $oldData;
		$this->actionSelected = $actionSelected;
		$this->possiblePolicies = $possiblePolicies;
		$this->e4j_debug = $e4j_debug;
		$this->sessionValues = $sessionValues;
		$this->newRoom = $newRoom;
		$this->newRoomInfo = $newRoomInfo;

		
		// Display the template (default.php)
		parent::display($tpl);
		
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTBCAPNOTIF'), 'vikchannelmanager');
		JToolBarHelper::save('bca.makeProductXML', JText::_('SAVE'));
		JToolBarHelper::spacer();
		JToolBarHelper::cancel( 'cancel', JText::_('CANCEL'));
		JToolBarHelper::spacer();
	}
}
