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
 * Serializes the processor state on disk.
 * 
 * @since 1.9.2
 */
final class VCMOtaOnboardingStorageFile implements VCMOtaOnboardingStorage
{
	/** @var string */
	protected $path;

	/**
	 * Class constructor.
	 * 
	 * @param  string|null  $path  The folder where the files should be located. If not
	 *                             specified, the default path will be used.
	 */
	public function __construct($path = null)
	{
		if (!$path) {
			$path = dirname(__FILE__);
		}

		$this->path = $path;

		if (!JFolder::exists($this->path) && !JFolder::create($this->path)) {
			// the folder doesn't exist and the system is unable to create it
			throw new RuntimeException('Unable to create folder for onboarding serialization: ' . $this->path, 403);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function load(int $roomId, string $processor)
	{
		$path = $this->getPath($roomId, $processor);

		if (!JFile::exists($path)) {
			throw new VCMOtaOnboardingExceptionStoragenotfound;
		}
		
		$buffer = file_get_contents($path);

		if (!$buffer) {
			throw new VCMOtaOnboardingExceptionStoragenotfound;
		}

		$instance = @unserialize($buffer);

		if (!$instance instanceof VCMOtaOnboardingProcessor) {
			throw new VCMOtaOnboardingExceptionStoragenotfound;
		}

		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	public function save(VCMOtaOnboardingProcessor $processor)
	{
		JFile::write(
			$this->getPath($processor->getRoomID(), get_class($processor)),
			serialize($processor)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function clean(VCMOtaOnboardingProcessor $processor)
	{
		JFile::delete(
			$this->getPath($processor->getRoomID(), get_class($processor))
		);
	}

	/**
	 * Helper method used to return the full path of the serialized file.
	 * 
	 * @param   int     $roomId     The ID of the involved room.
	 * @param   string  $processor  The processor class identifier.
	 * 
	 * @return  string  The absolute path.
	 */
	protected function getPath(int $roomId, string $processor)
	{
		// get rid of the class prefix, if any
		return JPath::clean($this->path . '/onboarding_' . strtolower(preg_replace("/^VCMOtaOnboardingProcessor/i", '', $processor)) . '_' . $roomId);
	}
}