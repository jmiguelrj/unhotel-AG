<?php
/**
 * @package     VikBooking
 * @subpackage  mod_vikbooking_otareviews
 * @author      Alessio Gaggii - E4J s.r.l
 * @copyright   Copyright (C) 2018 E4J s.r.l. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */
 
// no direct access
defined('ABSPATH') or die('No script kiddies please!');

class VikBookingOtaReviewsHelper
{
	public static function getDateFormat($skipsession = false)
	{
		if ($skipsession) {
			$dbo = JFactory::getDbo();
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
				$dbo = JFactory::getDbo();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='dateformat';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vbgetDateFormat', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}

	public static function importVboLib()
	{
		if (!class_exists('VikBooking') && file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikbooking'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'lib.vikbooking.php')) {
			require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikbooking'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'lib.vikbooking.php');
		}
	}

	public static function getCurrencySymb()
	{
		return VikBooking::getCurrencySymb();
	}

	public static function numberFormat($num)
	{
		return VikBooking::numberFormat($num);
	}

	public static function getTranslator()
	{
		return VikBooking::getTranslator();
	}

	public static function loadFontAwesome($force_load = false)
	{
		return VikBooking::loadFontAwesome($force_load);
	}

	/**
	 * Returns an instance of the VikBookingTracker Class.
	 * 
	 * @param 	boolean 	$require_only 	whether to return the object.
	 * 
	 * @return 	VikBookingTracker
	 * 
	 * @since 	1.11
	 */
	public static function getTracker($require_only = false)
	{
		if (!method_exists('VikBooking', 'getTracker')) {
			// this version of VikBooking does not support the Tracker Class
			return false;
		}
		return VikBooking::getTracker($require_only);
	}

	/**
	 * Returns a string with the class for the requested icon.
	 * This works better with the latest version of Vik Booking.
	 * 
	 * @param 	string 	$text 			the text to apply the substr onto.
	 * 
	 * @return 	string
	 * 
	 * @since 	1.11
	 */
	public static function icon($type)
	{
		if (class_exists('VikBookingIcons')) {
			return VikBookingIcons::i($type);
		}

		return 'fa fa-'.$type;
	}

	/**
	 * Invokes the loadRemoteAssets method of VikBookingIcons.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.11
	 */
	public static function loadIconRemoteAssets()
	{
		if (class_exists('VikBookingIcons')) {
			VikBookingIcons::loadRemoteAssets();
		}
	}

	/**
	 * Loads the reviews data or global score from VCM.
	 * 
	 * @param 	object 	$params  module params object by reference
	 * 
	 * @return 	array
	 */
	public static function loadReviewsData(&$params)
	{
		$dbo = JFactory::getDbo();
		// basic params
		$channelaccount = (string)$params->get('channelaccount', '');
		$revorscore = (int)$params->get('revsorscore', '1');
		$sorting = (string)$params->get('sorting', 'score');
		$ordering = (string)$params->get('ordering', 'DESC');
		$lim = (int)$params->get('lim', '10');

		$valid_sorting = array('score', 'date');
		$sorting = in_array($sorting, $valid_sorting) ? $sorting : 'score';

		$valid_ordering = array('ASC', 'DESC');
		$ordering = in_array($ordering, $valid_ordering) ? $ordering : 'DESC';

		if ($sorting == 'date' && $revorscore == 1) {
			$sort_clause = 'dt';
		} elseif ($sorting == 'date' && $revorscore == 2) {
			$sort_clause = 'last_updated';
		} else {
			$sort_clause = 'score';
		}

		// main container
		$revdata = array();

		if (empty($channelaccount)) {
			// unable to proceed
			return $revdata;
		}

		$chparts = explode('_', $channelaccount);
		if (count($chparts) < 2 && $chparts[0] != 'ibe') {
			// unable to proceed
			return $revdata;
		}

		// channel name is on the first position
		$channel_name = $chparts[0];

		// property name is after
		unset($chparts[0]);
		$prop_name = count($chparts) ? implode('_', $chparts) : '';

		$clauses = array();
		if ($channel_name == 'ibe') {
			array_push($clauses, "`channel` IS NULL");
		} else {
			array_push($clauses, "`channel`=" . $dbo->quote($channel_name));
		}
		if (!empty($prop_name)) {
			array_push($clauses, "`prop_name`=" . $dbo->quote($prop_name));
		}
		if ($revorscore === 1) {
			array_push($clauses, "`published`=1");
		}

		$q = "SELECT * FROM `#__vikchannelmanager_".($revorscore == 2 ? 'otascores' : 'otareviews')."` WHERE " . implode(' AND ', $clauses) . " ORDER BY `{$sort_clause}` {$ordering} LIMIT {$lim};";
		$dbo->setQuery($q);

		return $dbo->loadAssocList();
	}

	/**
	 * Parses a review array into HTML by loading
	 * the apposite template file for this channel.
	 * 
	 * @param 	array 	$data 	the OTA Review plain record
	 * @param 	object 	$params the module params object by reference
	 * 
	 * @return 	string 	the parsed HTML to display
	 */
	public static function parseReview($data, &$params)
	{
		if (empty($data['channel'])) {
			$tmpl_name = 'website';
		} else {
			$tmpl_name = preg_replace("/[^a-z0-9]/", '', strtolower($data['channel']));
		}

		if (!is_file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'channels' . DIRECTORY_SEPARATOR . $tmpl_name . '_review.php')) {
			$tmpl_name = 'generic';
		}
		
		// make sure the raw content is an object
		$data['content'] = !is_object($data['content']) ? json_decode($data['content']) : $data['content'];
		$data['content'] = !is_object($data['content']) ? new stdClass : $data['content'];

		// start output buffering
		ob_start();
		// include template for this channel
		include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'channels' . DIRECTORY_SEPARATOR . $tmpl_name . '_review.php';
		// get parsed HTML content
		$content = ob_get_contents();
		// stop output buffering
		ob_end_clean();

		return $content;
	}

	/**
	 * Parses the global score array into HTML by loading
	 * the apposite template file for this channel.
	 * 
	 * @param 	array 	$data 	the OTA Global Score plain record
	 * @param 	object 	$params the module params object by reference
	 * 
	 * @return 	string 	the parsed HTML to display
	 */
	public static function parseGlobalScore($data, &$params)
	{
		if (empty($data['channel'])) {
			$tmpl_name = 'website';
		} else {
			$tmpl_name = preg_replace("/[^a-z0-9]/", '', strtolower($data['channel']));
		}
		if (!is_file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'channels' . DIRECTORY_SEPARATOR . $tmpl_name . '_score.php')) {
			$tmpl_name = 'generic';
		}

		// make sure the raw content is an object
		$data['content'] = !is_object($data['content']) ? json_decode($data['content']) : $data['content'];
		$data['content'] = !is_object($data['content']) ? new stdClass : $data['content'];

		// start output buffering
		ob_start();
		// include template for this channel
		include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'channels' . DIRECTORY_SEPARATOR . $tmpl_name . '_score.php';
		// get parsed HTML content
		$content = ob_get_contents();
		// stop output buffering
		ob_end_clean();

		return $content;
	}

	/**
	 * Calls the methods of VCM to get the logo of the requested channel.
	 * 
	 * @param 	int 	$uniquekey 	the channel unique key for VCM
	 * 
	 * @return 	string 	the source to the channel logo
	 */
	public static function getChannelLogo($uniquekey)
	{
		if (!class_exists('VikChannelManager')) {
			require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikchannelmanager.php';
		}

		if (!method_exists('VikChannelManager', 'getChannel') || empty($uniquekey)) {
			return '';
		}

		$channel_logo = '';
		$channel_info = VikChannelManager::getChannel($uniquekey);
		if (count($channel_info)) {
			$channel_logo = VikChannelManager::getLogosInstance($channel_info['name'])->getLogoURL();
		}

		return $channel_logo;
	}

	/**
	 * Formats a date according to the VBO settings
	 * 
	 * @param 	string 	$dt 	the date in Y-m-d H:i:s
	 * 
	 * @return 	string 	the formatted date
	 */
	public static function formatIsoDate($dt)
	{
		$ts = strtotime($dt);
		if (!$ts || $ts <= 0) {
			return $dt;
		}

		$vbodf = self::getDateFormat();
		if ($vbodf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($vbodf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}

		return date($df, $ts);
	}

	/**
	 * Attempts to translate a known review category name into a translated
	 * value. If no translation found, the plain string is returned.
	 * 
	 * @param 	string 	$rev_cat 	the plain review category name from OTA.
	 * 
	 * @return 	string
	 * 
	 * @since 	1.6.0
	 */
	public static function tnReviewCategory($rev_cat)
	{
		if (stripos($rev_cat, 'Comfort') !== false) {
			return JText::_('VBOGREVCOMFORT');
		}

		if (stripos($rev_cat, 'Location') !== false) {
			return JText::_('VBOGREVLOCATION');
		}

		if (stripos($rev_cat, 'Clean') !== false) {
			return JText::_('VBOGREVCLEAN');
		}

		if (stripos($rev_cat, 'Value') !== false) {
			return JText::_('VBOGREVVALUE');
		}

		if (stripos($rev_cat, 'Staff') !== false) {
			return JText::_('VBOGREVSTAFF');
		}

		if (stripos($rev_cat, 'Facilities') !== false) {
			return JText::_('VBOGREVFACILITIES');
		}

		/**
		 * Rely on database-level translations.
		 * 
		 * @since 	1.18.4 (J) - 1.8.4 (WP)
		 */
		$vbo_tn = VikBooking::getTranslator();
		$tn_guest_review_cats = $vbo_tn->getTableDefaultDbValues('#__vikbooking_greview_service');
		foreach ($tn_guest_review_cats as $tn_id => $tn_guest_review) {
			if (($tn_guest_review['service_name'] ?? '') == $rev_cat) {
				// matching original value found
				$tn_records = [
					[
						'id' => $tn_id,
						'service_name' => $rev_cat,
					],
				];

				// attempt to translate the record
				$vbo_tn->translateContents($tn_records, '#__vikbooking_greview_service');

				if ($tn_records[0]['service_name'] != $rev_cat) {
					// record was translated
					return $tn_records[0]['service_name'];
				}
			}
		}

		return ucwords(str_replace('_', ' ', $rev_cat));
	}
}
