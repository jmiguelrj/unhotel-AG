<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Ignores the usage of the storage.
 * 
 * @since 1.9.2
 */
final class VCMOtaOnboardingStorageNull implements VCMOtaOnboardingStorage
{
	/**
	 * @inheritDoc
	 */
	public function load(int $roomId, string $processor)
	{
		throw new VCMOtaOnboardingExceptionStoragenotfound;
	}

	/**
	 * @inheritDoc
	 */
	public function save(VCMOtaOnboardingProcessor $processor)
	{
		// do nothing
	}

	/**
	 * @inheritDoc
	 */
	public function clean(VCMOtaOnboardingProcessor $processor)
	{
		// do nothing
	}
}
