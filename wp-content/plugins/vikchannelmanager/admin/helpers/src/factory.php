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
 * Factory application class.
 *
 * @since 	1.8.11
 */
final class VCMFactory
{
	/**
	 * Application configuration handler.
	 *
	 * @var VCMConfigRegistry
	 */
	private static $config;

	/**
	 * Application platform handler.
	 *
	 * @var VCMPlatformInterface
	 */
	private static $platform;

	/**
	 * Chat asynchronous processors mediator.
	 * 
	 * @var VCMChatAsyncMediator
	 */
	private static $chatAsyncMediator;

	/**
	 * Class constructor.
	 * @private This object cannot be instantiated. 
	 */
	private function __construct()
	{
		// never called
	}

	/**
	 * Class cloner.
	 * @private This object cannot be cloned.
	 */
	private function __clone()
	{
		// never called
	}

	/**
	 * Returns the current configuration object.
	 *
	 * @return 	VCMConfigRegistry
	 */
	public static function getConfig()
	{
		// check if config class is already instantiated
		if (is_null(static::$config))
		{
			// cache instantiation
			static::$config = new VCMConfigRegistryDatabase([
				'db' => JFactory::getDbo(),
			]);
		}

		return static::$config;
	}

	/**
	 * Returns the current platform handler.
	 *
	 * @return 	VCMPlatformInterface
	 */
	public static function getPlatform()
	{
		// check if platform class is already instantiated
		if (is_null(static::$platform))
		{
			if (VCMPlatformDetection::isWordPress())
			{
				// running WordPress platform
				static::$platform = new VCMPlatformOrgWordpress();
			}
			else
			{
				// running Joomla platform
				static::$platform = new VCMPlatformOrgJoomla();
			}
		}

		return static::$platform;
	}

	/**
	 * Returns the chat asynchronous processors mediator.
	 *
	 * @return 	VCMChatAsyncMediator
	 */
	public static function getChatAsyncMediator()
	{
		if (is_null(static::$chatAsyncMediator))
		{
			// save queue jobs within the database
			$asyncQueue = new VCMChatAsyncQueueDatabase;

			// create internal logger
			$logger = new VCMLogDriverJsonlines(
				// groups logs by day
				VBO_MEDIA_PATH . '/logs/ai/async-chat/' . JHtml::_('date', 'now', 'Y-m-d') . '.php',
				[
					// auto-delete log files that were updated more than 4 weeks ago
					'gc_threshold' => '-4 weeks',
				]
			);

			// create new mediator
			static::$chatAsyncMediator = new VCMChatAsyncMediator($asyncQueue, $logger);

			// attach processors
			static::$chatAsyncMediator->attachProcessor(new VCMChatAsyncProcessorTaskmanager);
			static::$chatAsyncMediator->attachProcessor(new VCMChatAsyncProcessorSelflearning);
		}

		return static::$chatAsyncMediator;
	}
}
