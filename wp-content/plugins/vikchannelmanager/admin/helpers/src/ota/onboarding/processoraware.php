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
 * Onboarding processor aware to define all the common methods.
 * 
 * @since 1.9.2
 */
abstract class VCMOtaOnboardingProcessoraware implements VCMOtaOnboardingProcessor
{
	/** @var object */
	protected $room;

	/** @var object */
	protected $progress;

	/** @var bool */
	private $completed = false;

	/**
	 * Class constructor.
	 * 
	 * @param  object  $room
	 */
	public function __construct(object $room)
	{
		$this->room = $room;
		$this->progress = new stdClass;
	}

	/**
	 * Update the room object upon de-serialization completed.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.9.16
	 */
	public function __wakeup()
	{
		$this->room = (object) VikBooking::getRoomInfo($this->room->id ?? 0);
	}

	/**
	 * @inheritDoc
	 */
	final public function getRoomID()
	{
		return $this->room->id;
	}

	/**
	 * @inheritDoc
	 */
	final public function getProgressData()
	{
		return $this->progress;
	}

	/**
	 * @inheritDoc
	 */
	final public function isCompleted()
	{
		return $this->completed;
	}

	/**
	 * Sets a progress data value.
	 * 
	 * @param 	string 	$key 	The progress property.
	 * @param 	mixed 	$data 	The progress property value.
	 * 
	 * @return  void
	 */
	final protected function setProgressData(string $key, $data)
	{
		$this->progress->{$key} = $data;
	}

	/**
	 * Flags the process as completed.
	 * 
	 * @return  void
	 */
	final protected function complete()
	{
		$this->completed = true;
	}
}
