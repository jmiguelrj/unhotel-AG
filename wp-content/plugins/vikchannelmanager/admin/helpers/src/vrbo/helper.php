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
 * Vrbo helper class to migrate to the API version of this channel and related network.
 * 
 * @since 	1.8.16
 */
final class VCMVrboHelper
{
	/**
	 * @var  array  URL hot keys that belong to the Vrbo family.
	 */
	public static $url_hotkeys = [
		'vrbo.com',
		'homeaway.',
		'abritel.fr',
		'fewo-direkt.de',
		'stayz.com.au',
		'bookabach.co.nz',
	];

	/**
	 * Checks whether the system is running an outdated version of Vrbo (iCal)
	 * which will have to be dismissed soon, due to our certification with the
	 * new Vrbo API integration that supports two-way sync. All iCal calendars
	 * connected with Vrbo will have to be dismissed as soon as possible in
	 * accordance with the contract stipulated between e4jConnect and Vrbo.
	 * 
	 * @return 	int 	0 if all is okay, 1 if all is bad, -1 if action is needed.
	 */
	public static function hasDeprecatedCalendars()
	{
		$dbo = JFactory::getDbo();

		$eligible_channels = [
			VikChannelManagerConfig::HOMEAWAY,
			VikChannelManagerConfig::VRBO,
			VikChannelManagerConfig::ICAL,
			VikChannelManagerConfig::VRBOAPI,
		];

		$q = $dbo->getQuery(true)
			->select($dbo->qn(['name', 'uniquekey']))
			->from($dbo->qn('#__vikchannelmanager_channel'))
			->where($dbo->qn('uniquekey') . ' IN (' . implode(', ', array_map([$dbo, 'q'], $eligible_channels)) . ')');

		$dbo->setQuery($q);
		$channels = $dbo->loadAssocList();

		if (!$channels) {
			// none of the involved channels is actually available
			return 0;
		}

		$list = [];
		foreach ($channels as $channel) {
			$list[$channel['uniquekey']] = $channel['name'];
		}

		$has_api_version   = isset($list[VikChannelManagerConfig::VRBOAPI]);
		$dismissible_icals = 0;

		if (!$has_api_version || count($list) > 1) {
			// count iCal calendars that should be dismissed
			$dismissible_icals = static::countDismissibleCalendars();
		}

		if (!$has_api_version && ($dismissible_icals || isset($list[VikChannelManagerConfig::HOMEAWAY]) || isset($list[VikChannelManagerConfig::VRBO]))) {
			// this is no good, iCal calendars should be removed, or iCal channels should be replaced with the API integration
			return 1;
		}

		if ($dismissible_icals && $has_api_version) {
			// there are still iCal calendars that should be removed
			return -1;
		}

		// nothing to do
		return 0;
	}

	/**
	 * Counts the number of dismissible iCal calendars involving the Vrbo network.
	 * 
	 * @param 	bool 	$get_records 	if true, the records will actually be returned, not counted.
	 * 
	 * @return 	mixed 	the number of iCal calendars that may belong to Vrbo or an array of records.
	 */
	public static function countDismissibleCalendars($get_records = false)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn(['id', 'retrieval_url', 'channel']))
			->from($dbo->qn('#__vikchannelmanager_listings'));

		$dbo->setQuery($q);
		$calendars = $dbo->loadAssocList();

		if (!$calendars) {
			return $get_records ? [] : 0;
		}

		$dismissible_count = 0;
		$dismissible_list  = [];
		foreach ($calendars as $calendar) {
			if (empty($calendar['retrieval_url'])) {
				continue;
			}
			foreach (static::$url_hotkeys as $url_hotkey) {
				if (stripos($calendar['retrieval_url'], $url_hotkey) !== false) {
					// increase counter and populate list
					$dismissible_count++;
					$dismissible_list[] = $calendar;
				}
			}
		}

		return $get_records ? $dismissible_list : $dismissible_count;
	}

	/**
	 * Cleans up the iCal calendars that belong to the Vrbo family.
	 * Should be used to parse the iCal calendar URLs for a specific
	 * channel identifer, such as the "Generic iCal" channel.
	 * 
	 * @param 	string 	$uniquekey 	the channel unique key identifier.
	 * 
	 * @return 	int 				number of iCal calendars removed.
	 */
	public static function deleteiCalCalendars($uniquekey)
	{
		if (empty($uniquekey)) {
			return 0;
		}

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn(['id', 'retrieval_url', 'channel']))
			->from($dbo->qn('#__vikchannelmanager_listings'))
			->where($dbo->qn('channel') . ' LIKE ' . $dbo->q("{$uniquekey}%"));

		$dbo->setQuery($q);
		$calendars = $dbo->loadAssocList();

		if (!$calendars) {
			return 0;
		}

		$deleted = [];
		foreach ($calendars as $calendar) {
			if (empty($calendar['retrieval_url'])) {
				continue;
			}
			foreach (static::$url_hotkeys as $url_hotkey) {
				if (stripos($calendar['retrieval_url'], $url_hotkey) !== false) {
					// register record ID to be removed
					$deleted[] = (int)$calendar['id'];
				}
			}
		}

		if ($deleted) {
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikchannelmanager_listings'))
				->where($dbo->qn('id') . ' IN (' . implode(', ', $deleted) . ')');

			$dbo->setQuery($q);
			$dbo->execute();
		}

		return count($deleted);
	}
}
