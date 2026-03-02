<?php
/**
 * @package     VikBooking
 * @subpackage  mod_vikbooking_channelrates
 * @author      Alessio Gaggii - E4J s.r.l
 * @copyright   Copyright (C) 2018 E4J s.r.l. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

class VikBookingChannelRatesHelper {
	
	public static function loadRestrictions ($filters = true, $rooms = array()) {
		$restrictions = array();
		$dbo = JFactory::getDBO();
		if (!$filters) {
			$q = "SELECT * FROM `#__vikbooking_restrictions`;";
		} else {
			if (count($rooms) == 0) {
				$q = "SELECT * FROM `#__vikbooking_restrictions` WHERE `allrooms`=1;";
			} else {
				$clause = array();
				foreach ($rooms as $idr) {
					if (empty($idr)) continue;
					$clause[] = "`idrooms` LIKE '%-".intval($idr)."-%'";
				}
				if (count($clause) > 0) {
					$q = "SELECT * FROM `#__vikbooking_restrictions` WHERE `allrooms`=1 OR (`allrooms`=0 AND (".implode(" OR ", $clause)."));";
				} else {
					$q = "SELECT * FROM `#__vikbooking_restrictions` WHERE `allrooms`=1;";
				}
			}
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$allrestrictions = $dbo->loadAssocList();
			foreach ($allrestrictions as $k => $res) {
				if (!empty($res['month'])) {
					$restrictions[$res['month']] = $res;
				} else {
					$restrictions['range'][$k] = $res;
				}
			}
		}
		return $restrictions;
	}
	
	public static function parseJsDrangeWdayCombo ($drestr) {
		$combo = array();
		if (strlen($drestr['wday']) > 0 && strlen($drestr['wdaytwo']) > 0 && !empty($drestr['wdaycombo'])) {
			$cparts = explode(':', $drestr['wdaycombo']);
			foreach ($cparts as $kc => $cw) {
				if (!empty($cw)) {
					$nowcombo = explode('-', $cw);
					$combo[intval($nowcombo[0])][] = intval($nowcombo[1]);
				}
			}
		}
		return $combo;
	}
	
	public static function getDateFormat($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='dateformat';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbgetDateFormat', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='dateformat';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbgetDateFormat', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}
	
	public static function getTimeOpenStore($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='timeopenstore';";
			$dbo->setQuery($q);
			$dbo->execute();
			$n = $dbo->loadAssocList();
			if (empty ($n[0]['setting']) && $n[0]['setting'] != "0") {
				return false;
			} else {
				$x = explode("-", $n[0]['setting']);
				if (!empty ($x[1]) && $x[1] != "0") {
					return $x;
				}
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbgetTimeOpenStore', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='timeopenstore';";
				$dbo->setQuery($q);
				$dbo->execute();
				$n = $dbo->loadAssocList();
				if (empty ($n[0]['setting']) && $n[0]['setting'] != "0") {
					return false;
				} else {
					$x = explode("-", $n[0]['setting']);
					if (!empty ($x[1]) && $x[1] != "0") {
						$session->set('vbgetTimeOpenStore', $x);
						return $x;
					}
				}
			}
		}
		return false;
	}

	public static function getHoursMinutes($secs) {
		if ($secs >= 3600) {
			$op = $secs / 3600;
			$hours = floor($op);
			$less = $hours * 3600;
			$newsec = $secs - $less;
			$optwo = $newsec / 60;
			$minutes = floor($optwo);
		} else {
			$hours = "0";
			$optwo = $secs / 60;
			$minutes = floor($optwo);
		}
		$x[] = $hours;
		$x[] = $minutes;
		return $x;
	}
	
	public static function showChildrenFront($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='showchildren';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$s = $dbo->loadAssocList();
				return (intval($s[0]['setting']) == 1 ? true : false);
			} else {
				return false;
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbshowChildrenFront', '');
			if (strlen($sval) > 0) {
				return (intval($sval) == 1 ? true : false);
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='showchildren';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$s = $dbo->loadAssocList();
					$session->set('vbshowChildrenFront', $s[0]['setting']);
					return (intval($s[0]['setting']) == 1 ? true : false);
				} else {
					return false;
				}
			}
		}
	}
	
	public static function getMinDaysAdvance() {
		return VikBooking::getMinDaysAdvance();
	}
	
	public static function getDefaultNightsCalendar($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='autodefcalnights';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return (int)$s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbdefaultNightsCalendar', '');
			if (!empty($sval)) {
				return (int)$sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='autodefcalnights';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbdefaultNightsCalendar', $s[0]['setting']);
				return (int)$s[0]['setting'];
			}
		}
	}
	
	public static function getSearchNumRooms($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='numrooms';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return (int)$s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbsearchNumRooms', '');
			if (!empty($sval)) {
				return (int)$sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='numrooms';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbsearchNumRooms', $s[0]['setting']);
				return (int)$s[0]['setting'];
			}
		}
	}
	
	public static function getSearchNumAdults($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='numadults';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbsearchNumAdults', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='numadults';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbsearchNumAdults', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}
	
	public static function getSearchNumChildren($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='numchildren';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbsearchNumChildren', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='numchildren';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbsearchNumChildren', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}
	
	public static function getMaxDateFuture($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='maxdate';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vbmaxDateFuture', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='maxdate';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbmaxDateFuture', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}

	public static function getCurrencySymb() {
		return VikBooking::getCurrencySymb();
	}

	public static function numberFormat($num) {
		return VikBooking::numberFormat($num);
	}

	/**
	 * Gets either the number of decimals, or the decimals/thousands separator.
	 * 
	 * @param 	int 	$index 	the formatting data index.
	 * 
	 * @return 	mixed 	int or string depending on what was requested, array otherwise.
	 * 
	 * @since 	1.13
	 */
	public static function gerNumberFormatValue($index = -1) {
		$format_data = VikBooking::getNumberFormatData();
		$format_parts = explode(':', $format_data);

		return isset($format_parts[$index]) ? $format_parts[$index] : $format_parts;
	}

	/**
	 * Returns a masked e-mail address. The e-mail are masked using 
	 * a technique to encode the bytes in hexadecimal representation.
	 * The chunk of the masked e-mail will be also encoded to be HTML readable.
	 *
	 * @param 	string 	 $email 	The e-mail to mask.
	 * @param 	boolean  $reverse 	True to reverse the e-mail address.
	 * 								Only if the e-mail is not contained into an attribute.
	 *
	 * @return 	string 	 The masked e-mail address.
	 */
	public static function maskMail($email, $reverse = false)
	{
		if ($reverse)
		{
			// reverse the e-mail address
			$email = strrev($email);
		}

		// converts the e-mail address from bin to hex
		$email = bin2hex($email);
		// append ;&#x sequence after every chunk of the masked e-mail
		$email = chunk_split($email, 2, ";&#x");
		// prepend &#x sequence before the address and trim the ending sequence
		$email = "&#x" . substr($email, 0, -3);

		return $email;
	}

	/**
	 * Returns a safemail tag to avoid the bots spoof a plain address.
	 *
	 * @param 	string 	 $email 	The e-mail address to mask.
	 * @param 	boolean  $mail_to 	True if the address should be wrapped
	 * 								within a "mailto" link.
	 * @param 	boolean  $get_mailto_only 	True to get just the encoded string with no HTML.
	 *
	 * @return 	string 	 The HTML tag containing the masked address.
	 *
	 * @uses 	maskMail()
	 */
	public static function safeMailTag($email, $mail_to = false, $get_mailto_only = false)
	{
		static $loaded = 0;

		if (!$loaded)
		{
			$loaded = 1;
			// include the CSS declaration to reverse the text contained in the <safemail> tags
			JFactory::getDocument()->addStyleDeclaration('safemail {direction: rtl;unicode-bidi: bidi-override;}');
		}

		// mask the reversed e-mail address
		$masked = self::maskMail($email, true);

		// include the address into a custom <safemail> tag
		$tag = "<safemail>$masked</safemail>";

		if ($mail_to)
		{
			// mask the address for mailto command (do not use reverse)
			$mailto = self::maskMail($email);

			if ($get_mailto_only)
			{
				// return just the mailto string to be used in the HTML
				return $mailto;
			}

			// wrap the safemail tag within a mailto link
			$tag = "<a href=\"mailto:$mailto\" class=\"mailto\">$tag</a>";
		}

		return $tag;
	}

	/**
	 * This method returns an array with the details
	 * of all channels in VCM that supports AV requests,
	 * and that have at least one room type mapped.
	 * The method invokes the Logos Class to return details
	 * about the name and logo URL of the channel.
	 *
	 * @param 	array 	$channels 	an array of channel IDs to be mapped on the VCM relations
	 *
	 * @return 	array
	 */
	public static function getChannelsMap($channels) {
		if (!self::vcmInstalled() && is_array($channels) && count($channels)) {
			// load sample data for the selected channels
			$dummy_map = array();
			foreach ($channels as $ch_id) {
				$ch_info = self::getSampleChannelInfo((int)$ch_id);
				if (is_array($ch_info)) {
					array_push($dummy_map, $ch_info);
				}
			}
			return $dummy_map;
		}

		return VikBooking::getChannelsMap($channels);
	}

	public static function parseJsClosingDates() {
		if (class_exists('vikbooking') && method_exists('vikbooking', 'parseJsClosingDates')) {
			return VikBooking::parseJsClosingDates();
		}
		return array();
	}

	public static function getTranslator() {
		return VikBooking::getTranslator();
	}

	/**
	 * This method returns an array with two timestamps
	 * for the default check-in and check-out dates,
	 * and a third value (int) for the number of nights.
	 * It checks what's the default MinLOS and the 
	 * minimum days in advance for booking.
	 *
	 * @param 	int 	$hcheckin 	check-in hours
	 * @param 	int 	$mcheckin 	check-in minutes
	 * @param 	int 	$hcheckout 	check-out hours
	 * @param 	int 	$mcheckout 	check-out minutes
	 *
	 * @return 	array
	 */
	public static function getDefaultDates($hcheckin, $mcheckin, $hcheckout, $mcheckout) {
		//calculate base check-in date depending on the current time
		$now_info = getdate();
		$actnow = $now_info[0];
		if (VikBooking::todayBookings()) {
			$actnow = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
		}
		$min_days_adv = VikBooking::getMinDaysAdvance();
		$first = mktime($hcheckin, $mcheckin, 0, $now_info['mon'], ($now_info['mday'] + (int)$min_days_adv), $now_info['year']);
		if ($first < $actnow) {
			//we need to go to tomorrow's date
			$first = mktime($hcheckin, $mcheckin, 0, $now_info['mon'], ($now_info['mday'] + 1), $now_info['year']);
		}
		//calculate base check-out date
		$glob_minlos = VikBooking::getDefaultNightsCalendar();
		$glob_minlos = $glob_minlos < 1 ? 1 : $glob_minlos;
		$tot_nights = $glob_minlos;
		$from_info = getdate($first);
		$second = mktime($hcheckout, $mcheckout, 0, $from_info['mon'], ($from_info['mday'] + (int)$glob_minlos), $from_info['year']);

		return array($first, $second, $tot_nights);
	}

	/**
	 * This method invokes the TAC class by first setting
	 * some values in the request for the TAC class to work,
	 * and to avoid a CURL request.
	 * 
	 * @param 	$checkin 	string 	Y-m-d date of the check-in date
	 * @param 	$checkout 	string 	Y-m-d date of the check-out date
	 * @param 	$nights 	int 	the number of nights of stay
	 * @param 	$adults 	array 	adults occupying each room of stay
	 * @param 	$children 	array 	children occupying each room of stay
	 *
	 * @return 	mixed 		array with success or error, or string with default error message
	 **/
	public static function fetchWebsiteRates($checkin, $checkout, $nights, $adults, $children) {
		if (!class_exists('TACVBO')) {
			require_once(VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "tac.vikbooking.php");
		}
		//set request variables
		VikRequest::setVar('e4jauth', md5('vbo.e4j.vbo'));
		VikRequest::setVar('req_type', 'hotel_availability');
		VikRequest::setVar('start_date', $checkin);
		VikRequest::setVar('end_date', $checkout);
		VikRequest::setVar('nights', $nights);
		VikRequest::setVar('num_rooms', count($adults));
		VikRequest::setVar('adults', $adults);
		VikRequest::setVar('children', $children);
		//make call to get the result
		TACVBO::$getArray = true;
		return TACVBO::tac_av_l();
	}

	/**
	 * This method takes the room-rate array with the lowest price
	 * that matches the preferred rate plan parameter (if available).
	 * The array $website_rates could not be an array, or it could be
	 * an array with the error string (response from the TACVBO Class).
	 *
	 * @param 	array  		$website_rates 		the array of the website rates returned by the method fetchWebsiteRates()
	 * @param 	int  		$def_rplan 			the id of the default type of price to take for display. If empty, take the lowest rate
	 *
	 * @return 	array
	 */
	public static function getBestRoomRate($website_rates, $def_rplan) {
		return VikBooking::getBestRoomRate($website_rates, $def_rplan);
	}

	/**
	 * This method returns a string to calculate the rates
	 * for the OTAs. Data is taken from the Bulk Rates Cache
	 * of Vik Channel Manager. The string returned contains
	 * the charge/discount operator at the position 0 (+ or -),
	 * and the percentage char (%) at the last position (if percent).
	 * Between the first and last position there is the float value.
	 *
	 * @param 	array  	$best_room_rate 	array containing a specific tariff returned by getBestRoomRate().
	 * @param 	object 	$params 			the module/widget instance parameters (@since 1.13).
	 * @param 	bool 	$per_channel 		whether to get a general alteration or the rates alteration per channel.
	 *
	 * @return 	mixed 						string or associative array depending on $per_channel.
	 */
	public static function getOtasRatesVal($best_room_rate, $params = null, $per_channel = false) {
		if (is_object($params)) {
			$cust_rmodpcent = $params->get('cust_rmodpcent', 'pcent');
			$cust_rmodval = $params->get('cust_rmodval', 0);
			if (!empty($cust_rmodval) && (float)$cust_rmodval > 0) {
				// we rather use what was defined from the module/widget parameters
				return '+' . (float)$cust_rmodval . ($cust_rmodpcent == 'pcent' ? '%' : '');
			}
		}

		return VikBooking::getOtasRatesVal($best_room_rate, $per_channel);
	}

	/**
	 * Checks whether VCM is installed. Joomla and WP compatible.
	 * 
	 * @return 	boolean
	 * 
	 * @since 	1.13
	 */
	public static function vcmInstalled()
	{
		if (defined('VCM_ADMIN_PATH')) {
			// WP or Joomla with VBO in current page
			return file_exists(VCM_ADMIN_PATH);
		}
		
		if (defined('JPATH_SITE')) {
			return is_file(JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_vikchannelmanager' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikchannelmanager.php');
		}

		return false;
	}

	/**
	 * Returns dummy information for a specific channel ID.
	 * 
	 * @param 	int 	$ch_id 	the channel unique identifier.
	 * 
	 * @return 	mixed 	false on failure, array otherwise.
	 * 
	 * @since 	1.13
	 */
	public static function getSampleChannelInfo($ch_id)
	{
		$logos_base_uri = defined('ABSPATH') && defined('VBO_MODULES_URI') ? VBO_MODULES_URI : JUri::root();
		$logos_base_uri .= 'modules/mod_vikbooking_channelrates/images/';

		$map = array(
			4  => array(
				'id'   => 4,
				'name' => 'Booking.com',
				'logo' => $logos_base_uri . 'channel_booking.png',
			),
			25  => array(
				'id'   => 25,
				'name' => 'Airbnb',
				'logo' => $logos_base_uri . 'channel_airbnb.png',
			),
			1  => array(
				'id'   => 1,
				'name' => 'Expedia',
				'logo' => $logos_base_uri . 'channel_expedia.png',
			),
			23 => array(
				'id'   => 23,
				'name' => 'Hostelworld',
				'logo' => $logos_base_uri . 'channel_hostelworld.png',
			),
			12 => array(
				'id'   => 12,
				'name' => 'Agoda',
				'logo' => $logos_base_uri . 'channel_agoda.png',
			),
			15 => array(
				'id'   => 15,
				'name' => 'Despegar',
				'logo' => $logos_base_uri . 'channel_despegar.png',
			),
			21 => array(
				'id'   => 21,
				'name' => 'Pitchup.com',
				'logo' => $logos_base_uri . 'channel_pitchup.png',
			),
		);

		return isset($map[$ch_id]) ? $map[$ch_id] : false;
	}
	
}
