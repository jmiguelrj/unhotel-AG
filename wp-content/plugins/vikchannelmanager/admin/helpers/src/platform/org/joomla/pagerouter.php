<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2022 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Implements the page router interface for the Joomla platform.
 * 
 * @since 	1.8.11
 */
class VCMPlatformOrgJoomlaPagerouter implements VCMPlatformPagerouterInterface
{
	/**
	 * Given a list of Views, finds the most appropriate page/menu item ID.
	 * 
	 * @param   array  	$views  The list of View names to match.
	 * @param   mixed 	$args   String for the language to which we should give higher preference, or
	 * 							Array for the View/Shortcode arguments to match (supporting the 'lang' key).
	 * 
	 * @return  int 			The post/menu item ID or 0.
	 * 
	 * @since 	1.8.12 			signature modified to allow View/Shortcode arguments to match.
	 */
	public function findProperPageId(array $views, $args = null)
	{
		$bestitemid = 0;

		$lang = null;
		if (is_string($args) && !empty($args))
		{
			// support for previous method signature
			$lang = $args;
		}
		elseif (is_array($args) && !empty($args['lang']))
		{
			$lang = $args['lang'];
			unset($args['lang']);
		}

		$args = is_array($args) ? $args : [];

		$current_lang = !empty($lang) ? $lang : JFactory::getLanguage()->getTag();

		$app = JFactory::getApplication();

		$menu = $app->getMenu('site');

		if (!$menu)
		{
			return 0;
		}

		$menu_items = $menu->getMenu();

		if (!$menu_items)
		{
			return 0;
		}

		if ($args)
		{
			// attempt to find the exactly requested menu item type
			foreach ($menu_items as $itemid => $item)
			{
				if (isset($item->query['option']) && isset($item->query['view']) && $item->query['option'] == 'com_vikbooking' && in_array($item->query['view'], $views))
				{
					// proper view found
					if ($item->language == '*' || $item->language == $current_lang)
					{
						// proper language found
						if ($this->matchItemArguments($item, $args))
						{
							// we found the exact menu item type for the given arguments
							return $itemid;
						}
					}
				}
			}
		}

		// fallback to regular processing of the menu items with no particular arguments
		foreach ($menu_items as $itemid => $item)
		{
			if (isset($item->query['option']) && isset($item->query['view']) && $item->query['option'] == 'com_vikbooking' && in_array($item->query['view'], $views))
			{
				// proper menu item type found
				$bestitemid = empty($bestitemid) ? $itemid : $bestitemid;

				if (isset($item->language) && $item->language == $current_lang)
				{
					// we found the exact menu item type for the given language
					return $itemid;
				}
			}
		}

		return $bestitemid;
	}

	/**
	 * Checks if the item matches all the specified arguments.
	 * The arguments must be contained within the query property.
	 *
	 * @param 	object 	 $item 	   The menu item object.
	 * @param 	array 	 $args 	   The associative array to check.
	 * @param 	array    $exclude  A list of attributes that should be empty on the searched items.
	 *
	 * @return 	boolean  True if the item matches, false otherwise.
	 * 
	 * @since 	1.8.12
	 */
	protected function matchItemArguments($item, array $args, array $exclude = [])
	{
		if (!$args && !$exclude)
		{
			// always compatible in case of empty arguments
			return true;
		}

		if (!isset($item->query))
		{
			// do not accept in case of empty query
			return false;
		}

		// iterate query to search for non scalar values
		foreach ($item->query as $k => $v)
		{
			/**
			 * Make sure the attribute specified by the menu item
			 * is not contained within the "excluded" list.
			 * 
			 * @since 1.8.5
			 */
			if (!empty($v) && in_array($k, $exclude))
			{
				return false;
			}

			if (is_array($v))
			{
				// validate array to make sure it matches the searched query
				if (!isset($args[$k]) || array_diff_assoc($args[$k], $v))
				{
					// missing match
					return false;
				}

				// unset value to avoid warnings in the next check
				unset($item->query[$k]);
				unset($args[$k]);
			}
		}

		// the difference between the array must return an empty array
		return !array_diff_assoc($args, $item->query);
	}
}
