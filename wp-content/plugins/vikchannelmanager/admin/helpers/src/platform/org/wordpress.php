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
 * Implements the WordPress platform interface.
 * 
 * @since 	1.8.11
 */
class VCMPlatformOrgWordpress extends VCMPlatformAware
{
	/**
	 * Creates a new URI helper instance.
	 *
	 * @return  VCMPlatformUriInterface
	 */
	protected function createUri()
	{
		return new VCMPlatformOrgWordpressUri;
	}

	/**
	 * Creates a new event dispatcher instance.
	 * 
	 * @return  VCMPlatformDispatcherInterface
	 */
	protected function createDispatcher()
	{
		return new VCMPlatformOrgWordpressDispatcher;
	}

	/**
	 * Creates a new page router instance.
	 *
	 * @return  VCMPlatformPagerouterInterface
	 */
	protected function createPagerouter()
	{
		return new VCMPlatformOrgWordpressPagerouter;
	}

	/**
	 * Creates a new cron environment instance.
	 *
	 * @return  VCMPlatformCronenvInterface
	 * 
	 * @since   1.9.16
	 */
	protected function createCronEnvironment()
	{
		return new VCMPlatformOrgWordpressCronenv;
	}
}
