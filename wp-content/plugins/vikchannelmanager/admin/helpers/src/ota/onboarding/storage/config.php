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
 * Serializes the processor state on the channel manager configuration database table.
 * 
 * @since 1.9.2
 */
final class VCMOtaOnboardingStorageConfig implements VCMOtaOnboardingStorage
{
	/** @var VCMConfigRegistry */
	protected $config;

	/**
	 * Class constructor.
	 * 
	 * @param  VCMConfigRegistry|null  $config  The configuration registry. If not specified
	 *                                          the global one will be used.
	 */
	public function __construct($config = null)
	{
		if (!$config) {
			$config = VCMFactory::getConfig();
		}

		$this->config = $config;
	}

	/**
	 * @inheritDoc
	 */
	public function load(int $roomId, string $processor)
	{
		$param = $this->getParam($roomId, $processor);
		
		$buffer = $this->config->get($param);

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
		$this->config->set(
			$this->getParam($processor->getRoomID(), get_class($processor)),
			serialize($processor)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function clean(VCMOtaOnboardingProcessor $processor)
	{
		$this->config->remove(
			$this->getParam($processor->getRoomID(), get_class($processor))
		);
	}

	/**
	 * Helper method used to return the parameter name to use for the serialization
	 * of the specified processor.
	 * 
	 * @param   int     $roomId     The ID of the involved room.
	 * @param   string  $processor  The processor class identifier.
	 * 
	 * @return  string  The parameter name.
	 */
	protected function getParam(int $roomId, string $processor)
	{
		// get rid of the class prefix, if any
		return 'onboarding_' . strtolower(preg_replace("/^VCMOtaOnboardingProcessor/i", '', $processor)) . '_' . $roomId;
	}
}