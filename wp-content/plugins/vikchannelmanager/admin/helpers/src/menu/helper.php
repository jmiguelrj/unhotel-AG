<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2023 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Menu helper class.
 * 
 * @since 	1.8.11
 */
final class VCMMenuHelper
{
	/**
	 * Returns the list of page identifiers allowed for registration.
	 * 
	 * @return 	array 	associative list of page identifiers.
	 */
	private static function getAllowedPages()
	{
		return [
			'avpush' 		  => JText::_('VCMMENUAVPUSH'),
			'ratespush' 	  => JText::_('VCMMENURATESPUSH'),
			'reviews' 		  => JText::_('VCMMENUREVIEWS'),
			'rooms' 		  => JText::_('VCMMENUEXPROOMSREL'),
			'bpromo' 		  => JText::_('VCMMENUBPROMOTIONS'),
			'egpromo' 		  => JText::_('VCMMENUBPROMOTIONS'),
			'airbnbpromo' 	  => JText::_('VCMMENUBPROMOTIONS'),
			'hoteldetails' 	  => JText::_('VCMMENUTACDETAILS'),
			'appconfig' 	  => JText::_('VCMMENUAPPGENSET'),
			'reslogs' 		  => JText::_('VCMRESLOGSBTN'),
			'airbnblistings'  => JText::_('VCMMENUAIRBMNGLST'),
			'expediaproducts' => JText::_('VCMBPROMOHROOMRATES'),
		];
	}

	/**
	 * Registers the currently visited page to the local storage by adding
	 * a script declaration to the document. Only certain pages will be
	 * actually registered, and the active channel detail will be kept.
	 * 
	 * @param 	string 	$page 	the current page (task/view name).
	 * @param 	array 	$module the active module record (channel).
	 * 
	 * @return 	boolean 		true if the page was registered or false.
	 */
	public static function registerPage($page, array $module)
	{
		$allowed_pages = self::getAllowedPages();

		if (!isset($allowed_pages[$page])) {
			return false;
		}

		if (empty($module['name']) || empty($module['av_enabled'])) {
			return false;
		}

		$admin_main_page = defined('ABSPATH') && function_exists('wp_die') ? 'admin.php' : 'index.php';

		// get current page query string for "goto" redirect
		$page_query = JUri::getInstance()->toString(['query']);
		$page_query = substr($page_query, 0, 1) != '?' ? '?' . $page_query : $page_query;
		$current_base_uri = $admin_main_page . $page_query;

		// build action href for current channel
		$action_href_vals = [
			'option' => 'com_vikchannelmanager',
			'task' 	 => 'setmodule',
			'id' 	 => $module['id'],
			'goto' 	 => base64_encode($current_base_uri),
		];
		$action_href = $admin_main_page . '?' . http_build_query($action_href_vals);

		// build menu action object
		$menu_action = [
			'name' 	 => $allowed_pages[$page],
			'href' 	 => $action_href,
			'target' => '_blank',
		];

		// attempt to get the channel tiny logo URL
		$ch_tiny_url = VikChannelManager::getLogosInstance($module['name'])->getTinyLogoURL();

		if ($ch_tiny_url) {
			$menu_action['img']    = $ch_tiny_url;
			$menu_action['origin'] = JUri::root();
		}

		$action_obj = json_encode($menu_action);

		JFactory::getDocument()->addScriptDeclaration(
<<<JS
;(function($) {
	$(function() {
		try {
			VBOCore.registerAdminMenuAction($action_obj, 'bookings');
		} catch(e) {
			console.error(e);
		}
	});
})(jQuery);
JS
		);

		return false;
	}

	/**
	 * Sorts the active channels properly, and attempts to set the tiny logo.
	 * 
	 * @param 	array 	&$modules 	list of active modules (channels).
	 * 
	 * @return 	void
	 */
	public static function prepareActiveChannels(array &$modules)
	{
		if (!$modules) {
			// nothing to sort
			return;
		}

		// list of popular channels
		$popular_ch_keys = [
			VikChannelManagerConfig::BOOKING,
			VikChannelManagerConfig::AIRBNBAPI,
			VikChannelManagerConfig::EXPEDIA,
			VikChannelManagerConfig::GOOGLEHOTEL,
			VikChannelManagerConfig::GOOGLEVR,
		];

		// count sorting score for each channel
		foreach ($modules as &$module) {
			$sort_score = 0;
			$tiny_logo  = null;

			if (in_array($module['uniquekey'], $popular_ch_keys)) {
				// channel is "popular"
				$sort_score += 2;
			}

			if ($module['av_enabled'] || $module['uniquekey'] == VikChannelManagerConfig::AI) {
				// channel based on API
				$sort_score += 1;
			}

			if ($module['uniquekey'] == VikChannelManagerConfig::ICAL) {
				if (preg_match("/[0-9]+-[0-9]+$/", $module['id'])) {
					// custom iCal channel
					$sort_score -= 1;
					// check if a custom logo was uploaded
					if (!empty($module['ical_channel']) && is_array($module['ical_channel']) && !empty($module['ical_channel']['logo'])) {
						$tiny_logo = $module['ical_channel']['logo'];
						if (!preg_match("/^https?:/i", $tiny_logo)) {
							// prepend website root URI
							$tiny_logo = JUri::root() . $tiny_logo;
						}
					}
				}
			}

			// set channel sort score
			$module['sort_score'] = $sort_score;

			// look for channel logo
			$tiny_logo = $tiny_logo ? $tiny_logo : VikChannelManager::getLogosInstance($module['name'])->getTinyLogoURL();
			if ($tiny_logo) {
				// set channel logo
				$module['img'] = $tiny_logo;
			}
		}

		// unset last reference
		unset($module);

		// apply custom sorting
		usort($modules, function($a, $b) {
			return $b['sort_score'] - $a['sort_score'];
		});
	}
}
