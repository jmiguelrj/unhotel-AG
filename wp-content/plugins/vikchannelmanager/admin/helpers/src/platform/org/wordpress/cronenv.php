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
 * Implements the cron environment interface for the WordPress platform.
 * 
 * @since 1.9.16
 */
class VCMPlatformOrgWordpressCronenv implements VCMPlatformCronenvInterface
{
	/**
	 * @inheritDoc
	 */
	public function isRunning()
	{
		return wp_doing_cron();
	}

	/**
	 * @inheritDoc
	 */
	public function isServer()
	{
		/**
		 * When crontab executes the scheduled events, the wp-cron.php file is reached without
		 * passing the doing_wp_cron argument in query string.
		 */
		return $this->isRunning() && !JFactory::getApplication()->input->getBool('doing_wp_cron');
	}

	/**
	 * @inheritDoc
	 */
	public function isUserVisit()
	{
		/**
		 * When a cron simulation (user visit) executes the scheduled events, the wp-cron.php file
		 * is always reached by passing the doing_wp_cron argument in query string.
		 */
		return $this->isRunning() && JFactory::getApplication()->input->getBool('doing_wp_cron');
	}
}
