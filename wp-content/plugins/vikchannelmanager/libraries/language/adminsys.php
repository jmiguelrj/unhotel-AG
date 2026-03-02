<?php
/** 
 * @package   	VikChannelManager - Libraries
 * @subpackage 	language
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.language.handler');

/**
 * Switcher class to translate the VikChannelManager plugin common languages.
 *
 * @since 	1.0
 */
class VikChannelManagerLanguageAdminSys implements JLanguageHandler
{
	/**
	 * Checks if exists a translation for the given string.
	 *
	 * @param 	string 	$string  The string to translate.
	 *
	 * @return 	string 	The translated string, otherwise null.
	 */
	public function translate($string)
	{
		$result = null;

		/**
		 * Translations go here.
		 * @tip Use 'TRANSLATORS:' comment to attach a description of the language.
		 */

		switch ($string)
		{
			/**
			 * Do not touch the first definition as it gives the title to the pages of the back-end
			 */
			case 'COM_VIKCHANNELMANAGER':
				$result = __('Vik Channel Manager', 'vikchannelmanager');
				break;
				
			/**
			 * Definitions
			 */
			case 'COM_VIKCHANNELMANAGER_MENU':
				$result = __('Channel Manager', 'vikchannelmanager');
				break;
			case 'VCM_APP_CANCEL_BOOKING':
				$result = __('Cancel Bookings via App', 'vikchannelmanager');
				break;
			case 'VCM_APP_CANCEL_BOOKING_DESC':
				$result = __('Allow to cancel bookings through the e4jConnect mobile App. This requires the Mobile App channel to assign the device accounts to a specific User Group.', 'vikchannelmanager');
				break;
			case 'VCM_APP_CONFIRM_BOOKING':
				$result = __('Confirm Bookings via App', 'vikchannelmanager');
				break;
			case 'VCM_APP_CONFIRM_BOOKING_DESC':
				$result = __('Allow to confirm bookings through the e4jConnect mobile App. This requires the Mobile App channel to assign the device accounts to a specific User Group.', 'vikchannelmanager');
				break;
			case 'VCM_APP_MODIFY_BOOKING':
				$result = __('Modify Bookings via App', 'vikchannelmanager');
				break;
			case 'VCM_APP_MODIFY_BOOKING_DESC':
				$result = __('Allow to modify bookings through the e4jConnect mobile App. This requires the Mobile App channel to assign the device accounts to a specific User Group.', 'vikchannelmanager');
				break;
			case 'VCM_APP_CREATE_BOOKING':
				$result = __('Create Bookings via App', 'vikchannelmanager');
				break;
			case 'VCM_APP_CREATE_BOOKING_DESC':
				$result = __('Allow to create bookings through the e4jConnect mobile App. This requires the Mobile App channel to assign the device accounts to a specific User Group.', 'vikchannelmanager');
				break;
			case 'VCM_APP_MODIFY_ROOM_RATES':
				$result = __('Modify Rates via App', 'vikchannelmanager');
				break;
			case 'VCM_APP_MODIFY_ROOM_RATES_DESC':
				$result = __('Allow to modify rates and restrictions through the e4jConnect mobile App. This requires the Mobile App channel to assign the device accounts to a specific User Group.', 'vikchannelmanager');
				break;
			case 'VCM_APP_CLOSE_ROOM':
				$result = __('Close Rooms via App', 'vikchannelmanager');
				break;
			case 'VCM_APP_CLOSE_ROOM_DESC':
				$result = __('Allow to close rooms through the e4jConnect mobile App. This requires the Mobile App channel to assign the device accounts to a specific User Group.', 'vikchannelmanager');
				break;
			case 'VCM_APP_GET_GRAPHS':
				$result = __('View Graphs via App', 'vikchannelmanager');
				break;
			case 'VCM_APP_GET_GRAPHS_DESC':
				$result = __('Allow to view graphs through the e4jConnect mobile App. This requires the Mobile App channel to assign the device accounts to a specific User Group.', 'vikchannelmanager');
				break;
			case 'JGLOBAL_NO_MATCHING_RESULTS':
				$result = __('No matching results', 'vikchannelmanager');
				break;
			case 'JSEARCH_FILTER_CLEAR':
				$result = __('Clear', 'vikchannelmanager');
				break;
			case 'JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT':
				$result = __('Maximum upload size: <strong>%s</strong>', 'vikchannelmanager');
				break;
		}

		return $result;
	}
}
