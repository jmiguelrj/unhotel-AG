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
 * Declares all the cron environment methods that may differ between every supported platform.
 * 
 * @since 1.9.16
 */
interface VCMPlatformCronenvInterface
{
	/**
	 * Checks whether the cron is executing.
	 * 
	 * @return  bool
	 */
	public function isRunning();

	/**
	 * Checks whether the cron is executing from crontab.
	 * 
	 * @return  bool
	 */
	public function isServer();

	/**
	 * Check whether the cron is executing as a result of a user visit.
	 * 
	 * @return   bool
	 */
	public function isUserVisit();
}
