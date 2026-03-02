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
 * Declares the page router methods that may differ between every supported platform.
 * 
 * @since 	1.8.11
 */
interface VCMPlatformPagerouterInterface
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
	public function findProperPageId(array $views, $args = null);
}
