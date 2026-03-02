<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

jimport('joomla.application.component.view');

class VikChannelManagerViewOversight extends JViewUI {
	function display($tpl = null) {
		require_once(VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php");
		
		$lang = JFactory::getLanguage();
		$lang->load('com_vikbooking', VIKBOOKING_ADMIN_LANG, $lang->getTag(), true);
		
		$this->addToolBar();
		
		VCM::load_css_js();
		VCM::loadDatePicker();
		
		$max_days = 60;
		$max_days_to_display = 14;
		$this->maxDays = $max_days;
		$this->maxDaysToDisplay = $max_days_to_display;
		
		$dbo = JFactory::getDbo();
		$pmonth = VikRequest::getString('month', '', 'request');
		$datepicker = VikRequest::getString('datepicker', '', 'request');
		$oldest_checkin = 0;
		$furthest_checkout = 0;
		$q = "SELECT `checkin` FROM `#__vikbooking_busy` ORDER BY `checkin` ASC LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$oldest_arr = $dbo->loadAssocList();
			$oldest_checkin = $oldest_arr[0]['checkin'];
		}
		
		$q = "SELECT `checkout` FROM `#__vikbooking_busy` ORDER BY `checkout` DESC LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$furthest_arr = $dbo->loadAssocList();
			$furthest_checkout = $furthest_arr[0]['checkout'];
		}

		$session = JFactory::getSession();
		
		if( !empty($datepicker) ) {
			$tsstart = VikChannelManager::createTimestamp($datepicker, 0, 0);
			$oggid = getdate();
			if( $tsstart == mktime(0, 0, 0, $oggid['mon'], 1, $oggid['year']) ){
				$tsstart = mktime(0, 0, 0, $oggid['mon'], $oggid['mday'], $oggid['year']);
			}
		} else {
			if( !empty($pmonth) ) {
				$tsstart = $pmonth;
			} else {
				$tsstart = $session->get('vcm-datepicker', '', 'oversight');
			}
			if( empty($tsstart) ) {
				$oggid = getdate();
				$tsstart = mktime(0, 0, 0, $oggid['mon'], $oggid['mday'], $oggid['year']);
			}
		}
		
		$session->set('vcm-datepicker', $tsstart, 'oversight');
		
		$oggid = getdate($tsstart);
		$nextmon = $oggid['mon']+round($max_days/30);
		$year = $oggid['year'];
		if( $nextmon > 12 ) {
			$nextmon -= 12;
			$year++;
		}
		/*
		if( $oggid['mon'] == 12 ) {
			$nextmon = 1;
			$year = $oggid['year']+1;
		} else {
			$nextmon = $oggid['mon']+1;
			$year = $oggid['year'];
		}
		*/
		
		$tsend = mktime(0, 0, 0, $oggid['mon'], $oggid['mday']+$max_days, $oggid['year']);
		$today = getdate();
		$firstmonth = mktime(0, 0, 0, $today['mon'], 1, $today['year']);
		$wmonthsel = "<select name=\"month\" onchange=\"document.vboverview.submit();\">\n";
		if( !empty($oldest_checkin) ) {
			$oldest_date = getdate($oldest_checkin);
			$oldest_month = mktime(0, 0, 0, $oldest_date['mon'], 1, $oldest_date['year']);
			if( $oldest_month < $firstmonth ) {
				while( $oldest_month < $firstmonth ) {
					//$wmonthsel .= "<option value=\"".$oldest_month."\"".($oldest_month==$tsstart ? " selected=\"selected\"" : "").">".VikBooking::sayMonth($oldest_date['mon'])." ".$oldest_date['year']."</option>\n";
					$wmonthsel .= "<option value=\"".$oldest_month."\"".($oldest_date['mon']==$oggid['mon'] && $oldest_date['year']==$oggid['year'] ? " selected=\"selected\"" : "").">".VikBooking::sayMonth($oldest_date['mon'])." ".$oldest_date['year']."</option>\n";
					if( $oldest_date['mon'] == 12 ) {
						$nextmon = 1;
						$year = $oldest_date['year']+1;
					} else {
						$nextmon = $oldest_date['mon']+1;
						$year = $oldest_date['year'];
					}
					$oldest_month = mktime(0, 0, 0, $nextmon, 1, $year);
					$oldest_date = getdate($oldest_month);
				}
			}
		}
		//$wmonthsel .= "<option value=\"".$firstmonth."\"".($firstmonth==$tsstart ? " selected=\"selected\"" : "").">".VikBooking::sayMonth($today['mon'])." ".$today['year']."</option>\n";
		$wmonthsel .= "<option value=\"".$firstmonth."\"".($today['mon']==$oggid['mon'] && $today['year']==$oggid['year'] ? " selected=\"selected\"" : "").">".VikBooking::sayMonth($today['mon'])." ".$today['year']."</option>\n";
		$futuremonths = 12;
		if( !empty($furthest_checkout) ) {
			$furthest_date = getdate($furthest_checkout);
			$furthest_month = mktime(0, 0, 0, $furthest_date['mon'], 1, $furthest_date['year']);
			if( $furthest_month > $firstmonth ) {
				$monthsdiff = floor(($furthest_month - $firstmonth) / (86400 * 30));
				$futuremonths = $monthsdiff > $futuremonths ? $monthsdiff : $futuremonths;
			}
		}
		
		for( $i = 1; $i <= $futuremonths; $i++ ) {
			$newts = getdate($firstmonth);
			if( $newts['mon'] == 12 ) {
				$nextmon = 1;
				$year = $newts['year']+1;
			} else {
				$nextmon = $newts['mon'] + 1;
				$year = $newts['year'];
			}
			$firstmonth = mktime(0, 0, 0, $nextmon, 1, $year);
			$newts = getdate($firstmonth);
			//$wmonthsel .= "<option value=\"".$firstmonth."\"".($firstmonth==$tsstart ? " selected=\"selected\"" : "").">".VikBooking::sayMonth($newts['mon'])." ".$newts['year']."</option>\n";
			$wmonthsel .= "<option value=\"".$firstmonth."\"".($newts['mon']==$oggid['mon'] && $newts['year']==$oggid['year'] ? " selected=\"selected\"" : "").">".VikBooking::sayMonth($newts['mon'])." ".$newts['year']."</option>\n";
		}
		$wmonthsel .= "</select>\n";
		$mainframe = JFactory::getApplication();
		$lim = $mainframe->getUserStateFromRequest("com_vikchannelmanager.limit", 'limit', $mainframe->get('list_limit'), 'int');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
		$q = "SELECT SQL_CALC_FOUND_ROWS * FROM `#__vikbooking_rooms` ORDER BY `#__vikbooking_rooms`.`name` ASC";
		$dbo->setQuery($q, $lim0, $lim);
		$dbo->execute();
		if( $dbo->getNumRows() > 0 ) {
			$rows = $dbo->loadAssocList();
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
			$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
			$arrbusy = array();
			$actnow = time();
			foreach( $rows as $r ) {
				$q = "SELECT `b`.*,`ob`.`idorder` FROM `#__vikbooking_busy` AS `b`,`#__vikbooking_ordersbusy` AS `ob` WHERE `b`.`idroom`='".$r['id']."' AND `b`.`id`=`ob`.`idbusy` AND (`b`.`checkin`>=".$tsstart." OR `b`.`checkout`>=".$tsstart.") AND (`b`.`checkin`<=".$tsend." OR `b`.`checkout`<=".$tsstart.");";
				$dbo->setQuery($q);
				$dbo->execute();
				$cbusy = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
				$arrbusy[$r['id']] = $cbusy;
			}
		} else {
			$rows = array();
		}

		//Availability Comparison Request allowed and start date
		$acmp_rq_enabled = 0;
		$acmp_rq_start = date('Y-m-d', $tsstart);
		if($tsstart >= mktime(0, 0, 0, date('n'), date('j'), date('Y'))) {
			$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `av_enabled`=1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if( $dbo->getNumRows() > 0 ) {
				$acmp_rq_enabled = 1;
			}
		}
		$acmp_last_request = '';
		$sess_acmp = $session->get('vcmExecAcmpRs', '');
		if (!empty($sess_acmp) && @is_array($sess_acmp)) {
			$acmp_last_request = $sess_acmp['fromdate'];
		}
		//

		$this->rows = $rows;
		$this->arrbusy = $arrbusy;
		$this->wmonthsel = $wmonthsel;
		$this->tsstart = $tsstart;
		$this->acmp_rq_enabled = $acmp_rq_enabled;
		$this->acmp_rq_start = $acmp_rq_start;
		$this->acmp_last_request = $acmp_last_request;
		$this->lim0 = $lim0;
		$this->navbut = $navbut;
		
		parent::display($tpl);
	}
	
	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() {
		//Add menu title and some buttons to the page
		JToolBarHelper::title(JText::_('VCMMAINTOVERVIEW'), 'vikchannelmanager');
		
		JToolBarHelper::save('confirmcustoma', JText::_('VCMSAVECUSTA'));
		JToolBarHelper::spacer();
		JToolBarHelper::cancel( 'cancel', JText::_('BACK'));
		
	}

}
