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

class VikChannelManagerViewexecrarrq extends JViewUI {
	
	function display($tpl = null) {
		
		$session = JFactory::getSession();
		
		if(!function_exists('curl_init')) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Curl');
			exit;
		}
		
		$config = VikChannelManager::loadConfiguration();
		$validate = array('apikey');
		foreach($validate as $v) {
			if( empty($config[$v]) ) {
				echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Settings');
				exit;
			}
		}
		
		$dbo = JFactory::getDbo();

		if (!class_exists('VikBooking')) {
			require_once VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'lib.vikbooking.php';
		}

		$channel = VikChannelManager::getActiveModule(true);

		$q = "SELECT `vbr`.`id`,`vbr`.`name`,`vbr`.`img`,`vbr`.`units`,`vbr`.`smalldesc`,`vcmr`.`idroomvb`,`vcmr`.`idroomota`,`vcmr`.`channel`,`vcmr`.`otaroomname`,`vcmr`.`otapricing` FROM `#__vikbooking_rooms` AS `vbr` LEFT JOIN `#__vikchannelmanager_roomsxref` `vcmr` ON `vbr`.`id`=`vcmr`.`idroomvb` WHERE `vcmr`.`idchannel`=".$channel['uniquekey']." ORDER BY `vbr`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$vbrooms = $dbo->loadAssocList();
		}else {
			echo 'e4j.error.There are no rooms in VikBooking, fetching the rates from the OTA would be useless.';
			exit;
		}
		
		$fromdate = VikRequest::getString('from', '', 'request');
		$todate = VikRequest::getString('to', '', 'request');
		$roomtypeid = VikRequest::getString('roomtypeid', '', 'request');
		$rateplanid = VikRequest::getString('rateplanid', '', 'request');
		$limitts = strtotime(date('Y-m-d'));
		if ($channel['uniquekey'] == VikChannelManagerConfig::DESPEGAR) {
			//certification requires dates in the past
			$limitts = strtotime("-1 year");
		}
		$fromts = strtotime($fromdate);
		$tots = strtotime($todate);
		if ($tots < $fromts) {
			$tots = $fromts;
			$todate = $fromdate;
		}
		$fromdate = empty($fromts) || $fromts < $limitts ? date('Y-m-d') : $fromdate;
		$todate = empty($tots) || $tots < $limitts ? $fromdate : $todate;
		$fromts = strtotime($fromdate);
		$tots = strtotime($todate);
		
		//Max 31 days
		$max_date_span = 86400 * 31;
		if (($tots - $fromts) > $max_date_span) {
			echo 'e4j.error.'.VikChannelManager::getErrorFromMap('e4j.error.Max31days');
			exit;
		}
		//
		
		$channel['params'] = json_decode($channel['params'], true);
		$channel['settings'] = json_decode($channel['settings'], true);

		$taxincl_price_compare = false;
		$vbo_tax_included = VikBooking::ivaInclusa();
		if(@is_array($channel['settings']) && @array_key_exists('price_compare', $channel['settings'])) {
			if($channel['settings']['price_compare']['value'] == 'VCM_PRICE_COMPARE_TAX_INCL') {
				$taxincl_price_compare = true;
			}
		}
		
		//check old session value
		$skip_call = false;
		$sess_rar = $session->get('vcmExecRarRs', '');
		if (!empty($sess_rar) && @is_array($sess_rar)) {
			if ($fromdate == $sess_rar['fromdate'] && $todate == $sess_rar['todate'] && (string)$roomtypeid == (string)$sess_rar['roomtypeid'] && (string)$rateplanid == (string)$sess_rar['rateplanid'] && @is_array($sess_rar['rars']) && @count($sess_rar['rars']) > 0) {
				$skip_call = true;
			}
		}
		//
		
		if (!$skip_call) {
			$send_hotel_id = isset($channel['params']['hotelid']) ? $channel['params']['hotelid'] : '';
			if (!empty($roomtypeid)) {
				$q = "SELECT `prop_params` FROM `#__vikchannelmanager_roomsxref` WHERE `idroomota`=".$dbo->quote($roomtypeid)." AND `idchannel`=".$dbo->quote($channel['uniquekey']).";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$prop_params = $dbo->loadAssocList();
					$params_arr = json_decode($prop_params[0]['prop_params'], true);
					if (is_array($params_arr)) {
						if (isset($params_arr['hotelid'])) {
							$send_hotel_id = $params_arr['hotelid'];
						} elseif (isset($params_arr['id'])) {
							// useful for Pitchup.com to identify multiple accounts
							$send_hotel_id = $params_arr['id'];
						} elseif (isset($params_arr['property_id'])) {
							// useful for Hostelworld
							$send_hotel_id = $params_arr['property_id'];
						} elseif (isset($params_arr['user_id'])) {
							// useful for Airbnb API
							$send_hotel_id = $params_arr['user_id'];
						}
					}
				}
			}

			$eqc_url = "https://e4jconnect.com/channelmanager/?r=par&c=".$channel['name'];

			$xml = '<?xml version="1.0" encoding="UTF-8"?>
	<!-- VikChannelManager PAR Request e4jConnect.com - '.ucwords($channel['name']).' Module Extensionsforjoomla.com -->
	<ProductsAvailabilityRatesRQ xmlns="http://www.e4jconnect.com/channels/parrq">
		<Notify client="'.JUri::root().'"/>
		<Api key="'.$config['apikey'].'"/>
		<ProductsAvailabilityRates>
			<Fetch element="rates" hotelid="'.$send_hotel_id.'"'.(!empty($roomtypeid) ? ' roomtypeid="'.$roomtypeid.'"' : '').(!empty($rateplanid) ? ' rateplanid="'.$rateplanid.'"' : '').'/>
			<Dates from="'.$fromdate.'" to="'.$todate.'"/>
		</ProductsAvailabilityRates>
	</ProductsAvailabilityRatesRQ>';
			
			$e4jC = new E4jConnectRequest($eqc_url);
			$e4jC->setPostFields($xml);
			$e4jC->slaveEnabled = true;
			$rs = $e4jC->exec();
			if($e4jC->getErrorNo()) {
				echo 'e4j.error.'.@curl_error($e4jC->getCurlHeader());
				exit;
			}
			if(substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				echo 'e4j.error.'.VikChannelManager::getErrorFromMap($rs);
				exit;
			}
			
			//Debug:
			//echo '<br/><strong>From e4jConnect Call:</strong><br/><pre>'.print_r(unserialize($rs), true).'</pre><br/><br/>';

			$rars = unserialize($rs);
			if(count($rars) == 0 || count($rars['AvailRate']) == 0) {
				echo 'e4j.error.No Rates, Availability or Restrictions Returned. Check your Settings.';
				exit;
			}
			
			//Update session values
			$sess_rar = array('fromdate' => $fromdate, 'todate' => $todate, 'roomtypeid' => $roomtypeid, 'rateplanid' => $rateplanid, 'rars' => $rars);
			$session->set('vcmExecRarRs', $sess_rar);
			//
			
		}else {
			$rars = $sess_rar['rars'];
			//Debug:
			//echo '<br/><strong>From Session Call:</strong><br/><pre>'.print_r($rars, true).'</pre><br/><br/>';
		}
		
		//Calculate comparison values with IBE
		$currencysymb = '';
		$comparison = array();
		$vbotamap = array();
		$vbo_r_ids = array();
		foreach ($vbrooms as $vbroom) {
			if (!empty($vbroom['idroomota'])) {
				$vbotamap[$vbroom['idroomota']] = $vbroom;
				$vbo_r_ids[] = $vbroom['idroomvb'];
			}
		}
		$r_days = array_keys($rars['AvailRate']);
		if (count($vbotamap) > 0 && count($r_days) > 0) {
			$currencysymb = VikBooking::getCurrencySymb();
			$timeopst = VikBooking::getTimeOpenStore();
			if (is_array($timeopst)) {
				$opent = VikBooking::getHoursMinutes($timeopst[0]);
				$closet = VikBooking::getHoursMinutes($timeopst[1]);
				$checkinh = $opent[0];
				$checkinm = $opent[1];
				$checkouth = $closet[0];
				$checkoutm = $closet[1];
			}else {
				$checkinh = 0;
				$checkinm = 0;
				$checkouth = 0;
				$checkoutm = 0;
			}
			$start_ts = (strtotime($r_days[0]) + (3600 * $checkinh) + (60 * $checkinm));
			$allbusy = VikBooking::loadBusyRecords($vbo_r_ids, $start_ts);
			$morehst = VikBooking::getHoursRoomAvail() * 3600;
			$restrictions = VikBooking::loadRestrictions(true, $vbo_r_ids);
			foreach ($vbotamap as $idro => $idrv) {
				if(!empty($roomtypeid) && (string)$roomtypeid != (string)$idro) {
					//VCM 1.5.5 to avoid execution time to run out due to many requests for large mappings of rooms.
					continue;
				}
				$q = "SELECT `p`.*,`pr`.`name`,`pr`.`breakfast_included`,`pr`.`free_cancellation`,`pr`.`canc_deadline` FROM `#__vikbooking_dispcost` AS `p` LEFT JOIN `#__vikbooking_prices` `pr` ON `p`.`idprice`=`pr`.`id` WHERE `p`.`idroom`=" . $idrv['idroomvb'] . " ORDER BY `p`.`days` ASC, `p`.`cost` ASC;";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$base_tars = $dbo->loadAssocList();
					foreach ($r_days as $day) {
						$dts = strtotime($day);
						$dts_info = getdate($dts);
						$start_ts = ($dts + (3600 * $checkinh) + (60 * $checkinm));
						$end_ts = ($dts + 86400 + (3600 * $checkouth) + (60 * $checkoutm));
						$groupdays = VikBooking::getGroupDays($start_ts, $end_ts, 1);
						//Set prices considering the Special Prices in this day
						$tars = VikBooking::applySeasonsRoom($base_tars, $dts, ($dts + 86400));
						foreach ($tars as $k => $t) {
							if($channel['uniquekey'] == VikChannelManagerConfig::EXPEDIA) {
								//Cost is always meant per night
								$t['cost'] = $t['cost'] / $t['days'];
							}
							//Taxes included or excluded
							if($taxincl_price_compare === true) {
								if(!$vbo_tax_included) {
									$t['cost'] = VikBooking::sayCostPlusIva($t['cost'], $t['idprice']);
								}
							}else {
								if($vbo_tax_included) {
									$t['cost'] = VikBooking::sayCostMinusIva($t['cost'], $t['idprice']);
								}
							}
							//
							$comparison[$day][$idro][$t['days']][] = $t;
						}
						//Set Min and Max LOS for this day and this room
						$minlos = 1;
						$maxlos = 0;
						$minmaxlosfound = false;
						if (count($restrictions) > 0) {
							if (array_key_exists('range', $restrictions)) {
								foreach ($restrictions['range'] as $restr) {
									if ($dts >= $restr['dfrom'] && $dts <= $restr['dto']) {
										if ($restr['allrooms'] == 1 || strstr($restr['idrooms'], '-'.$idrv['idroomvb'].'-') !== false) {
											$minlos = $restr['minlos'];
											$maxlos = $restr['maxlos'];
											$minmaxlosfound = true;
											break;
										}
									}
								}
							}
							if(!$minmaxlosfound) {
								foreach ($restrictions as $rmon => $restr) {
									if ($rmon == 'range') {
										continue;
									}
									if ((int)$dts_info['mon'] == (int)$restr['month']) {
										if ($restr['allrooms'] == 1 || strstr($restr['idrooms'], '-'.$idrv['idroomvb'].'-') !== false) {
											$minlos = $restr['minlos'];
											$maxlos = $restr['maxlos'];
											$minmaxlosfound = true;
											break;
										}
									}
								}
							}
						}
						$comparison[$day][$idro]['minlos'] = $minlos;
						$comparison[$day][$idro]['maxlos'] = $maxlos;
						//Set units and unitsavail for this room on this day
						$comparison[$day][$idro]['units'] = $idrv['units'];
						$comparison[$day][$idro]['unitsavail'] = $idrv['units'];
						if (count($allbusy) > 0 && array_key_exists($idrv['idroomvb'], $allbusy) && count($allbusy[$idrv['idroomvb']]) > 0) {
							foreach ($groupdays as $gday) {
								$bfound = 0;
								foreach ($allbusy[$idrv['idroomvb']] as $bu) {
									if ($gday >= $bu['checkin'] && $gday <= ($morehst + $bu['checkout'])) {
										$bfound++;
									}
								}
								if ($bfound >= $idrv['units']) {
									$comparison[$day][$idro]['unitsavail'] = 0;
									break;
								}else {
									$comparison[$day][$idro]['unitsavail'] = ($idrv['units'] - $bfound);
								}
							}
						}
						//
					}
				}
			}
		}

		/**
		 * The use of this View as well as the db table '#__vikchannelmanager_rar_updates'
		 * has been deprecated. No more records should be written there.
		 * 
		 * @deprecated 	1.8.3
		 */
		// previous updates made
		$rar_updates = array();
		if(count($r_days) > 0) {
			//clean up expired updates
			$q = "DELETE FROM `#__vikchannelmanager_rar_updates` WHERE UNIX_TIMESTAMP(`date`) < ".(time() - 86400).";";
			$dbo->setQuery($q);
			$dbo->execute();
			//
			$r_days_q = array();
			foreach ($r_days as $rdk => $rdv) {
				$r_days_q[] = "'".$rdv."'";
			}
			$q = "SELECT * FROM `#__vikchannelmanager_rar_updates` WHERE `channel`=".$dbo->quote($channel['uniquekey'])." AND `date` IN (".implode(',', $r_days_q).") ORDER BY `#__vikchannelmanager_rar_updates`.`last_update` DESC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if( $dbo->getNumRows() > 0 ) {
				$all_upd = $dbo->loadAssocList();
				foreach ($all_upd as $rupd) {
					$data_arr = json_decode($rupd['data'], true);
					if(@is_array($data_arr) && @count($data_arr)) {
						$rupd['data'] = $data_arr;
						$rar_updates[$rupd['date']][$rupd['room_type_id']] = $rupd;
					}
				}
			}
		}
		
		$this->config = $config;
		$this->vbrooms = $vbrooms;
		$this->comparison = $comparison;
		$this->channel = $channel;
		$this->currencysymb = $currencysymb;
		$this->rars = $rars;
		$this->rar_updates = $rar_updates;
		
		// Set and Display the template
		if(!file_exists(VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'execrarrq'.DIRECTORY_SEPARATOR.'tmpl'.DIRECTORY_SEPARATOR.$channel['name'].'.php')) {
			die('Error, unable to read file '.$channel['name'].'.php');
		}
		$this->setLayout($channel['name']);

		parent::display($tpl);
		
	}

}
