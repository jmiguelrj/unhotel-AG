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
 * Implements the page router interface for the WordPress platform.
 * 
 * @since 	1.8.11
 */
class VCMPlatformOrgWordpressPagerouter implements VCMPlatformPagerouterInterface
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
		$model = JModel::getInstance('vikbooking', 'shortcodes', 'admin');

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

		$itemid = $model->best($views, $lang);

		if ($itemid)
		{
			return $itemid;
		}

		return 0;
	}
}
