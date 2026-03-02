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
 * Declares all the helper methods that may differ between every supported platform.
 * 
 * @since 	1.8.11
 */
interface VCMPlatformInterface
{
	/**
	 * Returns the URI helper instance.
	 *
	 * @return 	VCMPlatformUriInterface
	 */
	public function getUri();

	/**
	 * Returns the event dispatcher instance.
	 * 
	 * @return  VCMPlatformDispatcherInterface
	 */
	public function getDispatcher();

	/**
	 * Returns the page-router instance.
	 * 
	 * @return  VCMPlatformPagerouterInterface
	 */
	public function getPagerouter();

	/**
	 * Returns the cron environment instance.
	 * 
	 * @return  VCMPlatformCronenvInterface
	 * 
	 * @since   1.9.16
	 */
	public function getCronEnvironment();
}
