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

class VikChannelManagerViewconfirmcustomr extends JViewUI {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();
		
		VCM::load_css_js();
		
		$dbo = JFactory::getDbo();
		
		$cust_a_req = VikRequest::getVar('cust_av', array());
		
		foreach( $cust_a_req as $i => $c ) {
			list($ts, $day, $month, $year, $idroom, $rate, $type, $minlos, $maxlos, $cta, $ctd) = explode('-', $c);
			$ts = mktime(0, 0, 0, $month, $day, $year);
			$cust_a_req[$i] = "$idroom-$ts-$day-$month-$year-$rate-$type-$minlos-$maxlos-$cta-$ctd";
		}
		
		if(!(count($cust_a_req) > 0)) {
			VikError::raiseWarning('', JText::_('VCMNOCUSTOMAMODS'));
			$mainframe = JFactory::getApplication();
			$mainframe->redirect('index.php?option=com_vikchannelmanager&task=oversight');
			exit;
		}

		sort($cust_a_req);
		
		$cust_a = array();
		//$last_index_used = -1;
		$last_details = array();
		foreach( $cust_a_req as $i => $c ) {
			list($idroom, $ts, $day, $month, $year, $rate, $type, $minlos, $maxlos, $cta, $ctd) = explode('-', $c);
			
			if( empty($cust_a[$idroom]) ) {
				$cust_a[$idroom]['details'] = array();
				$cust_a[$idroom]['channels'] = array();
			}
			
			$details = array(
				'day' => $day,
				'month' => $month,
				'year' => $year,
				'fromts' => $ts,
				'endts' => 0, 
				'rate' => $rate*($type[0] == 'I' || $type[0] == 'E' ? 1 : -1),
				'percentot' => $type[1],
				'exactcost' => ($type[0] == 'E' ? 1 : 0),
				'restrictions' => "$minlos-$maxlos-$cta-$ctd"
			);
			
			$last_index_used = count($cust_a[$idroom]['details'])-1;
			
			if( 
				$last_index_used != -1 && 
				$cust_a[$idroom]['details'][$last_index_used]['rate'] == $details['rate'] &&
				$cust_a[$idroom]['details'][$last_index_used]['percentot'] == $details['percentot'] &&
				$cust_a[$idroom]['details'][$last_index_used]['exactcost'] == $details['exactcost'] &&
				$cust_a[$idroom]['details'][$last_index_used]['restrictions'] == $details['restrictions'] &&
				$this->getNextDayTimestamp($last_details) == $details['fromts'] ) {
				
				$cust_a[$idroom]['details'][$last_index_used]['endts'] = $details['fromts'];
			} else {
				array_push( $cust_a[$idroom]['details'], $details );
			}
			
			$last_details = $details;
			
		}

		$rooms_xref = array();
		
		//this query was modified to be compliant with the strict mode
		//$q = "SELECT `r`.*, `c`.`name` AS `chname`, `c`.`uniquekey`, `b`.`name` AS `roomname` FROM `#__vikchannelmanager_roomsxref` AS `r`, `#__vikchannelmanager_channel` AS `c`, `#__vikbooking_rooms` AS `b` WHERE `b`.`id`=`r`.`idroomvb` AND `r`.`idchannel`=`c`.`uniquekey` AND `c`.`av_enabled`=1 GROUP BY `r`.`idroomvb`, `r`.`idchannel`;";
		$q = "SELECT `r`.`idroomvb`, MIN(`r`.`idroomota`) AS `idroomota`, `r`.`idchannel`, MIN(`r`.`otaroomname`) AS `otaroomname`, MIN(`r`.`otapricing`) AS `otapricing`, MIN(`c`.`name`) AS `chname`, MIN(`c`.`uniquekey`) AS `uniquekey`, MIN(`b`.`name`) AS `roomname` FROM `#__vikchannelmanager_roomsxref` AS `r`, `#__vikchannelmanager_channel` AS `c`, `#__vikbooking_rooms` AS `b` WHERE `b`.`id`=`r`.`idroomvb` AND `r`.`idchannel`=`c`.`uniquekey` AND `c`.`av_enabled`=1 GROUP BY `r`.`idroomvb`, `r`.`idchannel`;";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$rooms_xref = $dbo->loadAssocList();
		}
		
		foreach ($cust_a as $idroom => $v) {
			for ($j = 0; $j < count($rooms_xref); $j++) {
				if ($rooms_xref[$j]['idroomvb'] == $idroom) {
					$channel = array(
						'name' => $rooms_xref[$j]['chname'],
						'idchannel' => $rooms_xref[$j]['idchannel'],
						'idroomota' => $rooms_xref[$j]['idroomota'],
						'otaroomname' => $rooms_xref[$j]['otaroomname'],
						'otapricing' => $rooms_xref[$j]['otapricing'],
						'uniquekey' => $rooms_xref[$j]['uniquekey']
					);
					array_push($cust_a[$idroom]['channels'], $channel);
					
					$cust_a[$idroom]['rname'] = $rooms_xref[$j]['roomname'];
				}
			}

			$cust_a[$idroom]['pricetypes'] = array();
			$cust_a[$idroom]['defaultrates'] = array();
			//sql strict mode safe
			//$q = "SELECT `d`.`idroom`,`d`.`idprice`,`p`.`name` FROM `#__vikbooking_dispcost` AS `d` LEFT JOIN `#__vikbooking_prices` `p` ON `d`.`idprice`=`p`.`id` WHERE `d`.`idroom`=".(int)$idroom." GROUP BY `d`.`idprice` ORDER BY `p`.`name` ASC;";
			$q = "SELECT DISTINCT `d`.`idroom`,`d`.`idprice`,`p`.`name` FROM `#__vikbooking_dispcost` AS `d` LEFT JOIN `#__vikbooking_prices` `p` ON `d`.`idprice`=`p`.`id` WHERE `d`.`idroom`=".(int)$idroom." ORDER BY `p`.`name` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$pricetypes = $dbo->loadAssocList();
				/**
				 * We need to apply a custom ordering of the types of price so that the "Standard Rate" will
				 * always come first and not after an hipothetical "Non Refundable Rate" which would come
				 * first if we were to use the default alphabetical ordering. This is to pre-select it in the
				 * Bulk Action as the first element, because most of the times non refundable rates are derived.
				 * 
				 * @since 	1.8.3
				 */
				if (count($pricetypes) > 1) {
					// we need at least two rate plans
					$first_rplan = array();
					foreach ($pricetypes as $ptk => $ptv) {
						$check_nonref_name = (stripos($ptv['name'], 'Non') === false && stripos($ptv['name'], 'Not') === false);
						if ($ptk > 0 && (stripos($ptv['name'], 'Standard') !== false || stripos($ptv['name'], 'Base') !== false) && $check_nonref_name) {
							// this has to be a "Standard Rate" or similar
							$first_rplan = $ptv;
							// unset it from the current order
							unset($pricetypes[$ptk]);
							break;
						}
					}
					if (count($first_rplan)) {
						// unshift the array to prepend the "Standard Rate" just found
						array_unshift($pricetypes, $first_rplan);
					}
				}
				// push website rate plans
				$cust_a[$idroom]['pricetypes'] = $pricetypes;
				$defaultrates = array();
				foreach ($pricetypes as $pricetype) {
					$q = "SELECT `days`,`cost` FROM `#__vikbooking_dispcost` WHERE `idroom`=".(int)$idroom." AND `idprice`=".(int)$pricetype['idprice']." ORDER BY `days` ASC LIMIT 1;";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$pricetrates = $dbo->loadAssoc();
						if ($pricetrates['days'] > 1) {
							$pricetrates['cost'] = $pricetrates['cost'] / $pricetrates['days'];
						}
						$defaultrates[] = $pricetrates['cost'];
					}
				}
				if (count($defaultrates) == count($pricetypes)) {
					$cust_a[$idroom]['defaultrates'] = $defaultrates;
				}
			}
			
			if (empty($cust_a[$idroom]['rname'])) {
				$q = "SELECT `name` FROM `#__vikbooking_rooms` WHERE `id`=$idroom LIMIT 1;";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$cust_a[$idroom]['rname'] = $dbo->loadResult();
				} else {
					$cust_a[$idroom]['rname'] = JText::_('VCMOSUNDEFINEDROOM');
				}
				$cust_a[$idroom]['rdesc'] = JText::_('VCMOSUNDEFINEDDESC');
			}
			
		}

		$this->cust_a = $cust_a;
		
		// Display the template (default.php)
		parent::display($tpl);
		
	}

	protected function getNextDayTimestamp($details) {
		return mktime(0, 0, 0, $details['month'], $details['day']+1, $details['year']);
	} 

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTCONFIRMCUSTOMR'), 'vikchannelmanager');
		
		JToolbarHelper::save('ratespushsubmit', JText::_('VCMAPPLYCUSTRATES'));
		JToolBarHelper::spacer();
		JToolBarHelper::cancel( 'canceloversight', JText::_('CANCEL'));
		
	}
}
