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
 * Implements the event dispatcher interface for the Joomla platform.
 * 
 * @since 	1.8.11
 */
class VCMPlatformOrgJoomlaDispatcher implements VCMPlatformDispatcherInterface
{
	/**
	 * Make sure to load all the plugins attached to Vik Channel Manager or E4J.
	 * This is necessary with Joomla in order to let plugins work.
	 */
	public function __construct()
	{
		JPluginHelper::importPlugin('vikchannelmanager');
		JPluginHelper::importPlugin('e4j');
	}

	/**
	 * Triggers the specified event by passing the given argument.
	 * No return value is expected here.
	 * 
	 * @param   string  $event  The event to trigger.
	 * @param   array   $args   The event arguments.
	 * 
	 * @return  void
	 */
	public function trigger($event, array $args = [])
	{
		$this->filter($event, $args);
	}

	/**
	 * Triggers the specified event by passing the given argument.
	 * At least a return value is expected here.
	 * 
	 * @param   string  $event  The event to trigger.
	 * @param   array   $args   The event arguments.
	 * 
	 * @return  array   A list of returned values.
	 */
	public function filter($event, array $args = [])
	{
		return JFactory::getApplication()->triggerEvent($event, $args);
	}
}
