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

class VikChannelManagerViewbcarcont extends JViewUI {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();
		
		VCM::load_css_js();
		VCM::load_complex_select();
		
		$dbo = JFactory::getDbo();

		$channel = VikChannelManager::getActiveModule(true);
		$channel['params'] = json_decode($channel['params'], true);

		$roomID = 0;

		$xmlRequest = "";

		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=bcarn&c=".$channel['name'];

		$roomsNames = array();

		$currentTimestamp = time();

		$module = VikChannelManager::getActiveModule(true);
	
		$config = VikChannelManager::loadConfiguration();

		$actionSelected = VikRequest::getString('action-option');

		$e4j_debug = VikRequest::getInt('e4j_debug');

		$hotelId = $channel['params']['hotelid'];

		$xmlRequest = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
		<!-- BCARN Request e4jConnect.com - VikChannelManager - VikBooking -->
		<BCARoomsNamesRQ xmlns=\"http://www.e4jconnect.com/avail/bcarnrq\">
			<Notify client=\"".JUri::root()."\"/>
			<Api key=\"".VikChannelManager::getApiKey()."\"/>
			<BCARoomsNames>
				<roomtypes>
		    		<roomtype code=\"1\"/>
		    		<roomtype code=\"4\"/>
					<roomtype code=\"5\"/>
					<roomtype code=\"7\"/>
					<roomtype code=\"8\"/>
					<roomtype code=\"9\"/>
					<roomtype code=\"10\"/>
					<roomtype code=\"12\"/>
					<roomtype code=\"13\"/>
					<roomtype code=\"25\"/>
					<roomtype code=\"26\"/>
					<roomtype code=\"27\"/>
					<roomtype code=\"28\"/>
					<roomtype code=\"29\"/>
					<roomtype code=\"31\"/>
					<roomtype code=\"32\"/>
					<roomtype code=\"33\"/>
		  	  	</roomtypes>
			</BCARoomsNames>
		</BCARoomsNamesRQ>";

		$oldData = array();

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

		if($actionSelected!="0"&&!empty($actionSelected)&&$actionSelected!="-1"){
			$roomID = explode("-",$actionSelected)[0];
			$roomName = explode("-",$actionSelected)[1];
			$hotelID = explode("-",$actionSelected)[2];
			$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='bcarcont".$roomID."';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$oldData = $dbo->loadAssoc();
				$oldData = json_decode($oldData['setting'],true);
			}
		}

		$q = "SELECT `setting` from `#__vikchannelmanager_config` WHERE `param`='bcarnames'";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$roomsNames = $dbo->loadAssoc();
			$roomsNames = json_decode($roomsNames['setting'],true);
			/*echo "<strong>Database Data: </strong><pre>".print_r($roomsNames,true)."</pre>";
			echo "<strong>Database Timestamp: </strong><pre>".print_r($roomsNames['timestamp'],true)."</pre>";
			echo "<strong>Current Timestamp: </strong><pre>".print_r($currentTimestamp,true)."</pre>";
			echo "<strong>Timestamp Difference: </strong><pre>".print_r($currentTimestamp-$roomsNames['timestamp'],true)."</pre>";
			echo "<strong>A Week in seconds: </strong><pre>".print_r((60*60*24*7),true)."</pre>";*/
			if($e4j_debug){
				if($currentTimestamp-$roomsNames['timestamp']>=(60*60*24*7)){
					echo "<strong>It has been more than a week!</strong><pre>".($currentTimestamp-$roomsNames['timestamp']).">".(60*60*24*7)."</pre>";
				}
				else if($currentTimestamp-$roomsNames['timestamp']<=(60*60*24*7)){
					echo "<strong>It has been less than a week!</strong><pre>".($currentTimestamp-$roomsNames['timestamp'])."<".(60*60*24*7)."</pre>";
				}
			}
			if($currentTimestamp-$roomsNames['timestamp']>=(60*60*24*7)){
				$e4jC = new E4jConnectRequest($e4jc_url);
				$e4jC->setPostFields($xmlRequest);
				$rs = $e4jC->exec();

				if($e4jC->getErrorNo()) {
					VikError::raiseWarning('', @curl_error($e4jC->getCurlHeader()));
				}else {
					if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
						VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
					}
					else {
						$roomsNames = unserialize($rs);
						$roomsNames['timestamp'] = $currentTimestamp;
						$q="UPDATE `#__vikchannelmanager_config` SET `setting`=".$dbo->quote(json_encode($roomsNames))." WHERE `param`='bcarnames';";
						$dbo->setQuery($q);
						$dbo->execute();
						if($e4j_debug){
							echo "<strong>Response: </strong><pre>".print_r($roomsNames,true)."</pre>";
						}
					}
				}
			}
		}
		else{
			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xmlRequest);
			$rs = $e4jC->exec();

			if($e4jC->getErrorNo()) {
				VikError::raiseWarning('', @curl_error($e4jC->getCurlHeader()));
			}else {
				if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
					VikError::raiseWarning('', VikChannelManager::getErrorFromMap($rs));
				}else {
					$roomsNames = unserialize($rs);
					$roomsNames['timestamp'] = $currentTimestamp;
					$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES ('bcarnames',".$dbo->quote(json_encode($roomsNames)).");";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
		}
		$session = JFactory::getSession();
		if($session->has('vcmbcarcontents'.$roomID) && $actionSelected != -1){
			$sessionValues = json_decode($session->get('vcmbcarcontents'.$roomID),true);
		}
		else if($actionSelected==-1){

		}

		$amenityIndexes = array(
			JText::_('VCMBCAHAMENTYPE2') => 2,
			JText::_('VCMBCAHAMENTYPE3') => 3,
			JText::_('VCMBCAHAMENTYPE4') => 5,
			JText::_('VCMBCAHAMENTYPE5') => 8,
			JText::_('VCMBCAHAMENTYPE6') => 9,
			JText::_('VCMBCAHAMENTYPE7') => 10,
			JText::_('VCMBCAHAMENTYPE8') => 11,
			JText::_('VCMBCAHAMENTYPE9') => 13,
			JText::_('VCMBCAHAMENTYPE10') => 14,
			JText::_('VCMBCAHAMENTYPE11') => 16,
			JText::_('VCMBCAHAMENTYPE12') => 18,
			JText::_('VCMBCAHAMENTYPE13') => 19,
			JText::_('VCMBCAHAMENTYPE14') => 20,
			JText::_('VCMBCAHAMENTYPE15') => 21,
			JText::_('VCMBCAHAMENTYPE16') => 22,
			JText::_('VCMBCAHAMENTYPE17') => 25,
			JText::_('VCMBCAHAMENTYPE19') => 28,
			JText::_('VCMBCAHAMENTYPE20') => 29,
			JText::_('VCMBCAHAMENTYPE21') => 32,
			JText::_('VCMBCAHAMENTYPE23') => 38,
			JText::_('VCMBCAHAMENTYPE24') => 41,
			JText::_('VCMBCAHAMENTYPE25') => 46,
			JText::_('VCMBCAHAMENTYPE26') => 47,
			JText::_('VCMBCAHAMENTYPE27') => 49,
			JText::_('VCMBCAHAMENTYPE28') => 50,
			JText::_('VCMBCAHAMENTYPE29') => 54,
			JText::_('VCMBCAHAMENTYPE30') => 55,
			JText::_('VCMBCAHAMENTYPE31') => 56,
			JText::_('VCMBCAHAMENTYPE32') => 57,
			JText::_('VCMBCAHAMENTYPE34') => 59,
			JText::_('VCMBCAHAMENTYPE35') => 60,
			JText::_('VCMBCAHAMENTYPE36') => 61,
			JText::_('VCMBCAHAMENTYPE37') => 63,
			JText::_('VCMBCAHAMENTYPE38') => 64,
			JText::_('VCMBCAHAMENTYPE39') => 68,
			JText::_('VCMBCAHAMENTYPE40') => 69,
			JText::_('VCMBCAHAMENTYPE41') => 72,
			JText::_('VCMBCAHAMENTYPE42') => 77,
			JText::_('VCMBCAHAMENTYPE43') => 78,
			JText::_('VCMBCAHAMENTYPE44') => 80,
			JText::_('VCMBCAHAMENTYPE45') => 81,
			JText::_('VCMBCAHAMENTYPE46') => 85,
			JText::_('VCMBCAHAMENTYPE48') => 88,
			JText::_('VCMBCAHAMENTYPE49') => 89,
			JText::_('VCMBCAHAMENTYPE51') => 92,
			JText::_('VCMBCAHAMENTYPE52') => 94,
			JText::_('VCMBCAHAMENTYPE53') => 97,
			JText::_('VCMBCAHAMENTYPE54') => 98,
			JText::_('VCMBCAHAMENTYPE55') => 99,
			JText::_('VCMBCAHAMENTYPE56') => 103,
			JText::_('VCMBCAHAMENTYPE57') => 105,
			JText::_('VCMBCAHAMENTYPE58') => 108,
			JText::_('VCMBCAHAMENTYPE60') => 115,
			JText::_('VCMBCAHAMENTYPE61') => 117,
			JText::_('VCMBCAHAMENTYPE62') => 119,
			JText::_('VCMBCAHAMENTYPE63') => 123,
			JText::_('VCMBCAHAMENTYPE64') => 126,
			JText::_('VCMBCAHAMENTYPE65') => 127,
			JText::_('VCMBCAHAMENTYPE66') => 129,
			JText::_('VCMBCAHAMENTYPE67') => 133,
			JText::_('VCMBCAHAMENTYPE68') => 138,
			JText::_('VCMBCAHAMENTYPE69') => 139,
			JText::_('VCMBCAHAMENTYPE70') => 141,
			JText::_('VCMBCAHAMENTYPE71') => 142,
			JText::_('VCMBCAHAMENTYPE72') => 144,
			JText::_('VCMBCAHAMENTYPE73') => 146,
			JText::_('VCMBCAHAMENTYPE74') => 147,
			JText::_('VCMBCAHAMENTYPE75') => 149,
			JText::_('VCMBCAHAMENTYPE76') => 155,
			JText::_('VCMBCAHAMENTYPE77') => 157,
			JText::_('VCMBCAHAMENTYPE78') => 158,
			JText::_('VCMBCAHAMENTYPE79') => 162,
			JText::_('VCMBCAHAMENTYPE80') => 163,
			JText::_('VCMBCAHAMENTYPE81') => 164,
			JText::_('VCMBCAHAMENTYPE82') => 166,
			JText::_('VCMBCAHAMENTYPE83') => 167,
			JText::_('VCMBCAHAMENTYPE84') => 193,
			JText::_('VCMBCAHAMENTYPE85') => 194,
			JText::_('VCMBCAHAMENTYPE88') => 210,
			JText::_('VCMBCAHAMENTYPE89') => 214,
			JText::_('VCMBCAHAMENTYPE90') => 217,
			JText::_('VCMBCAHAMENTYPE91') => 218,
			JText::_('VCMBCAHAMENTYPE92') => 220,
			JText::_('VCMBCAHAMENTYPE93') => 223,
			JText::_('VCMBCAHAMENTYPE94') => 224,
			JText::_('VCMBCAHAMENTYPE95') => 228,
			JText::_('VCMBCAHAMENTYPE96') => 230,
			JText::_('VCMBCAHAMENTYPE97') => 234,
			JText::_('VCMBCAHAMENTYPE98') => 245,
			JText::_('VCMBCAHAMENTYPE99') => 246,
			JText::_('VCMBCAHAMENTYPE101') => 251,
			JText::_('VCMBCAHAMENTYPE102') => 254,
			JText::_('VCMBCAHAMENTYPE103') => 256,
			JText::_('VCMBCAHAMENTYPE104') => 258,
			JText::_('VCMBCAHAMENTYPE105') => 259,
			JText::_('VCMBCAHAMENTYPE106') => 260,
			JText::_('VCMBCAHAMENTYPE107') => 262,
			JText::_('VCMBCAHAMENTYPE108') => 265,
			JText::_('VCMBCAHAMENTYPE109') => 268,
			JText::_('VCMBCAHAMENTYPE110') => 270,
			JText::_('VCMBCAHAMENTYPE111') => 271,
			JText::_('VCMBCAHAMENTYPE112') => 273,
			JText::_('VCMBCAHAMENTYPE113') => 276,
			JText::_('VCMBCAHAMENTYPE114') => 5001,
			JText::_('VCMBCAHAMENTYPE115') => 5013,
			JText::_('VCMBCAHAMENTYPE116') => 5015,
			JText::_('VCMBCAHAMENTYPE117') => 5017,
			JText::_('VCMBCAHAMENTYPE118') => 5034,
			JText::_('VCMBCAHAMENTYPE119') => 5037,
			JText::_('VCMBCAHAMENTYPE120') => 5039,
			JText::_('VCMBCAHAMENTYPE121') => 5041,
			JText::_('VCMBCAHAMENTYPE122') => 5042,
			JText::_('VCMBCAHAMENTYPE123') => 5070,
			JText::_('VCMBCAHAMENTYPE124') => 5072,
			JText::_('VCMBCAHAMENTYPE125') => 5076,
			JText::_('VCMBCAHAMENTYPE126') => 5077,
			JText::_('VCMBCAHAMENTYPE127') => 5080,
			JText::_('VCMBCAHAMENTYPE128') => 5081,
			JText::_('VCMBCAHAMENTYPE129') => 5082,
			JText::_('VCMBCAHAMENTYPE130') => 5086,
			JText::_('VCMBCAHAMENTYPE131') => 5087,
			JText::_('VCMBCAHAMENTYPE132') => 5090,
			JText::_('VCMBCAHAMENTYPE133') => 5091,
			JText::_('VCMBCAHAMENTYPE134') => 5092,
			JText::_('VCMBCAHAMENTYPE135') => 5102,
			JText::_('VCMBCAHAMENTYPE136') => 5104,
			JText::_('VCMBCAHAMENTYPE137') => 5105,
			JText::_('VCMBCAHAMENTYPE138') => 5106,
			JText::_('VCMBCAHAMENTYPE139') => 5107,
			JText::_('VCMBCAHAMENTYPE140') => 5109,
			JText::_('VCMBCAHAMENTYPE141') => 5110,
			JText::_('VCMBCAHAMENTYPE142') => 5111,
			JText::_('VCMBCAHAMENTYPE143') => 5113,
			JText::_('VCMBCAHAMENTYPE144') => 5116,
			JText::_('VCMBCAHAMENTYPE145') => 5117,
			JText::_('VCMBCAHAMENTYPE146') => 5118,
			JText::_('VCMBCAHAMENTYPE147') => 5121,
			JText::_('VCMBCAHAMENTYPE148') => 5122,
			JText::_('VCMBCAHAMENTYPE149') => 5124,
			JText::_('VCMBCAHAMENTYPE150') => 5126,
			JText::_('VCMBCAHAMENTYPE151') => 5127,
			JText::_('VCMBCAHAMENTYPE152') => 5129,
			JText::_('VCMBCAHAMENTYPE153') => 5130,
			JText::_('VCMBCAHAMENTYPE154') => 5131,
			JText::_('VCMBCAHAMENTYPE155') => 5132,
			JText::_('VCMBCAHAMENTYPE156') => 5133,
			JText::_('VCMBCAHAMENTYPE157') => 5134,
			JText::_('VCMBCAHAMENTYPE158') => 5135,
			JText::_('VCMBCAHAMENTYPE159') => 5136,
			JText::_('VCMBCAHAMENTYPE160') => 5137,
			JText::_('VCMBCAHAMENTYPE161') => 5138,
			JText::_('VCMBCAHAMENTYPE162') =>  5146
		);

		$imageTagCodes = array(
			JText::_('VCMBCAHIMGTAG1') => 1,
			JText::_('VCMBCAHIMGTAG2') => 2,
			JText::_('VCMBCAHIMGTAG3') => 3,
			JText::_('VCMBCAHIMGTAG4') => 4,
			JText::_('VCMBCAHIMGTAG5') => 5,
			JText::_('VCMBCAHIMGTAG6') => 6,
			JText::_('VCMBCAHIMGTAG7') => 7,
			JText::_('VCMBCAHIMGTAG8') => 8,
			JText::_('VCMBCAHIMGTAG9') => 10,
			JText::_('VCMBCAHIMGTAG10') => 11,
			JText::_('VCMBCAHIMGTAG11') => 13,
			JText::_('VCMBCAHIMGTAG12') => 14,
			JText::_('VCMBCAHIMGTAG13') => 37,
			JText::_('VCMBCAHIMGTAG14') => 41,
			JText::_('VCMBCAHIMGTAG15') => 42,
			JText::_('VCMBCAHIMGTAG16') => 43,
			JText::_('VCMBCAHIMGTAG17') => 50,
			JText::_('VCMBCAHIMGTAG18') => 55,
			JText::_('VCMBCAHIMGTAG19') => 61,
			JText::_('VCMBCAHIMGTAG20') => 70,
			JText::_('VCMBCAHIMGTAG21') => 74,
			JText::_('VCMBCAHIMGTAG22') => 81,
			JText::_('VCMBCAHIMGTAG23') => 82,
			JText::_('VCMBCAHIMGTAG24') => 87,
			JText::_('VCMBCAHIMGTAG25') => 89,
			JText::_('VCMBCAHIMGTAG26') => 90,
			JText::_('VCMBCAHIMGTAG27') => 94,
			JText::_('VCMBCAHIMGTAG28') => 95,
			JText::_('VCMBCAHIMGTAG29') => 96,
			JText::_('VCMBCAHIMGTAG30') => 97,
			JText::_('VCMBCAHIMGTAG31') => 100,
			JText::_('VCMBCAHIMGTAG32') => 102,
			JText::_('VCMBCAHIMGTAG33') => 103,
			JText::_('VCMBCAHIMGTAG34') => 104,
			JText::_('VCMBCAHIMGTAG35') => 106,
			JText::_('VCMBCAHIMGTAG36') => 107,
			JText::_('VCMBCAHIMGTAG37') => 108,
			JText::_('VCMBCAHIMGTAG38') => 112,
			JText::_('VCMBCAHIMGTAG39') => 113,
			JText::_('VCMBCAHIMGTAG40') => 114,
			JText::_('VCMBCAHIMGTAG41') => 115,
			JText::_('VCMBCAHIMGTAG42') => 116,
			JText::_('VCMBCAHIMGTAG43') => 124,
			JText::_('VCMBCAHIMGTAG44') => 125,
			JText::_('VCMBCAHIMGTAG45') => 128,
			JText::_('VCMBCAHIMGTAG46') => 131,
			JText::_('VCMBCAHIMGTAG47') => 133,
			JText::_('VCMBCAHIMGTAG48') => 134,
			JText::_('VCMBCAHIMGTAG49') => 137,
			JText::_('VCMBCAHIMGTAG50') => 141,
			JText::_('VCMBCAHIMGTAG51') => 143,
			JText::_('VCMBCAHIMGTAG52') => 153,
			JText::_('VCMBCAHIMGTAG53') => 154,
			JText::_('VCMBCAHIMGTAG54') => 155,
			JText::_('VCMBCAHIMGTAG55') => 156,
			JText::_('VCMBCAHIMGTAG56') => 157,
			JText::_('VCMBCAHIMGTAG57') => 158,
			JText::_('VCMBCAHIMGTAG58') => 159,
			JText::_('VCMBCAHIMGTAG59') => 160,
			JText::_('VCMBCAHIMGTAG60') => 161,
			JText::_('VCMBCAHIMGTAG61') => 164,
			JText::_('VCMBCAHIMGTAG62') => 165,
			JText::_('VCMBCAHIMGTAG63') => 167,
			JText::_('VCMBCAHIMGTAG64') => 172,
			JText::_('VCMBCAHIMGTAG65') => 173,
			JText::_('VCMBCAHIMGTAG66') => 177,
			JText::_('VCMBCAHIMGTAG67') => 178,
			JText::_('VCMBCAHIMGTAG68') => 179,
			JText::_('VCMBCAHIMGTAG69') => 182,
			JText::_('VCMBCAHIMGTAG70') => 183,
			JText::_('VCMBCAHIMGTAG71') => 184,
			JText::_('VCMBCAHIMGTAG72') => 185,
			JText::_('VCMBCAHIMGTAG73') => 186,
			JText::_('VCMBCAHIMGTAG74') => 187,
			JText::_('VCMBCAHIMGTAG75') => 188,
			JText::_('VCMBCAHIMGTAG76') => 189,
			JText::_('VCMBCAHIMGTAG77') => 190,
			JText::_('VCMBCAHIMGTAG78') => 191,
			JText::_('VCMBCAHIMGTAG79') => 192,
			JText::_('VCMBCAHIMGTAG80') => 193,
			JText::_('VCMBCAHIMGTAG81') => 194,
			JText::_('VCMBCAHIMGTAG82') => 197,
			JText::_('VCMBCAHIMGTAG83') => 198,
			JText::_('VCMBCAHIMGTAG84') => 199,
			JText::_('VCMBCAHIMGTAG85') => 204,
			JText::_('VCMBCAHIMGTAG86') => 205,
			JText::_('VCMBCAHIMGTAG87') => 240,
			JText::_('VCMBCAHIMGTAG88') => 241,
			JText::_('VCMBCAHIMGTAG89') => 242,
			JText::_('VCMBCAHIMGTAG90') => 245,
			JText::_('VCMBCAHIMGTAG91') => 246,
			JText::_('VCMBCAHIMGTAG92') => 247,
			JText::_('VCMBCAHIMGTAG93') => 248,
			JText::_('VCMBCAHIMGTAG94') => 249,
			JText::_('VCMBCAHIMGTAG95') => 250,
			JText::_('VCMBCAHIMGTAG96') => 251,
			JText::_('VCMBCAHIMGTAG97') => 252,
			JText::_('VCMBCAHIMGTAG98') => 253,
			JText::_('VCMBCAHIMGTAG99') => 254,
			JText::_('VCMBCAHIMGTAG100') => 255,
			JText::_('VCMBCAHIMGTAG101') => 256,
			JText::_('VCMBCAHIMGTAG102') => 257,
			JText::_('VCMBCAHIMGTAG103') => 258,
			JText::_('VCMBCAHIMGTAG104') => 259,
			JText::_('VCMBCAHIMGTAG105') => 260,
			JText::_('VCMBCAHIMGTAG106') => 261,
			JText::_('VCMBCAHIMGTAG107') => 262,
			JText::_('VCMBCAHIMGTAG108') => 263,
			JText::_('VCMBCAHIMGTAG109') => 264,
			JText::_('VCMBCAHIMGTAG110') => 265,
			JText::_('VCMBCAHIMGTAG111') => 266,
			JText::_('VCMBCAHIMGTAG112') => 267,
			JText::_('VCMBCAHIMGTAG113') => 268,
			JText::_('VCMBCAHIMGTAG114') => 269,
			JText::_('VCMBCAHIMGTAG115') => 270,
			JText::_('VCMBCAHIMGTAG116') => 271,
			JText::_('VCMBCAHIMGTAG117') => 272,
			JText::_('VCMBCAHIMGTAG118') => 273,
			JText::_('VCMBCAHIMGTAG119') => 274,
			JText::_('VCMBCAHIMGTAG120') => 275,
			JText::_('VCMBCAHIMGTAG121') => 276,
			JText::_('VCMBCAHIMGTAG122') => 277,
			JText::_('VCMBCAHIMGTAG123') => 278,
			JText::_('VCMBCAHIMGTAG124') => 279,
			JText::_('VCMBCAHIMGTAG125') => 280,
			JText::_('VCMBCAHIMGTAG126') => 281,
			JText::_('VCMBCAHIMGTAG127') => 282,
			JText::_('VCMBCAHIMGTAG128') => 283,
			JText::_('VCMBCAHIMGTAG129') => 284,
			JText::_('VCMBCAHIMGTAG130') => 285,
			JText::_('VCMBCAHIMGTAG131') => 286,
			JText::_('VCMBCAHIMGTAG132') => 287,
			JText::_('VCMBCAHIMGTAG133') => 288,
			JText::_('VCMBCAHIMGTAG134') => 289,
			JText::_('VCMBCAHIMGTAG135') => 290,
			JText::_('VCMBCAHIMGTAG136') => 291
		);

		/*echo "<strong>Sent XML: </strong><pre>".print_r(htmlentities($xmlRequest),true)."</pre>";
		echo "<strong>Endpoint: </strong><pre>".print_r($e4jc_url,true)."</pre>";
		echo "<strong>XML Request Response: </strong><pre>".print_r($roomsNames,true)."</pre>";
		die;*/

		$this->config = $config;
		$this->imageTagCodes = $imageTagCodes;
		$this->oldData = $oldData;
		$this->roomsInfo = $roomsInfo;
		$this->roomsNames = $roomsNames;
		$this->actionSelected = $actionSelected;
		$this->amenityIndexes = $amenityIndexes;
		$this->hotelId = $hotelId;
		$this->sessionValues = $sessionValues;
		$this->e4j_debug = $e4j_debug;
		
		// Display the template (default.php)
		parent::display($tpl);
		
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTBCARCONT'), 'vikchannelmanager');
		JToolBarHelper::save('bca.makeRoomsXml', JText::_('SAVE'));
		JToolBarHelper::spacer();
		JToolBarHelper::cancel('cancel', JText::_('CANCEL'));
		JToolBarHelper::spacer();	
	}
}
