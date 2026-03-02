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
 * Implements the cron environment interface for the Joomla platform.
 * 
 * Do note that, at the moment, there is no way to detect whether a cron is running on Joomla,
 * as we don't support the scheduled task interface.
 * 
 * @since 1.9.16
 */
class VCMPlatformOrgJoomlaCronenv implements VCMPlatformCronenvInterface
{
	/**
	 * @inheritDoc
	 */
	public function isRunning()
	{
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function isServer()
	{
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function isUserVisit()
	{
		return true;
	}
}
