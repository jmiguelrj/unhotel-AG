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
 * This mediator can be used to handle the onboarding process
 * of a room through the preferred OTA channel.
 * 
 * @since 1.9.2
 */
class VCMOtaOnboardingMediator
{
	/** @var string */
	protected $processor;

	/** @var VCMOtaOnboardingStorage */
	protected $storage;

	/** @var VCMOtaOnboardingProcessor */
	protected $processorInstance;

	/**
	 * Class constructor.
	 * 
	 * @param  string                   $processor  The class name of the processor to invoke.
	 * @param  VCMOtaOnboardingStorage  $storage    The engine responsible of the processor serialization.
	 */
	public function __construct(string $processor, VCMOtaOnboardingStorage $storage)
	{
		$this->processor = $processor;
		$this->storage = $storage;
	}

	/**
	 * Starts or resume the onboarding precedure for the selected room and OTA processor.
	 * 
	 * @param   object  $room  The object holding the details of the room to onboard.
	 * @param   object  $data  The onboarding data object.
	 * 
	 * @return  void
	 */
	public function process(object $room, object $data)
	{
		try {
			// resume processor state
			$processor = $this->storage->load($room->id, $this->processor);
		} catch (VCMOtaOnboardingExceptionStoragenotfound $notStartedYet) {
			// onboarding procedure never started, create from scratch
			$processor = $this->createNewProcessor($room);
		}

		// register the processor instance
		$this->processorInstance = $processor;

		$error = null;

		try {
			// start/resume onboarding procedure
			$processor->onboard($data);
		} catch (Throwable $error) {
			// $error is now fulfilled
		}

		if ($processor->isCompleted()) {
			// clean up the current state
			$this->storage->clean($processor);
		} else {
			// save the processor state for later use
			$this->storage->save($processor);
		}

		if ($error) {
			// propagate error
			throw $error;
		}
	}

	/**
	 * Returns the active onboarding processor instance, if any.
	 * 
	 * @return 	VCMOtaOnboardingProcessor|null
	 */
	public function getProcessor()
	{
		return $this->processorInstance;
	}

	/**
	 * Creates a new processor from scratch.
	 * 
	 * @param   object  $room  The object holding the details of the room to onboard.
	 * 
	 * @return  VCMOtaOnboardingProcessor
	 */
	protected function createNewProcessor(object $room)
	{
		return (new ReflectionClass($this->processor))
			->newInstanceArgs([$room]);
	}
}
