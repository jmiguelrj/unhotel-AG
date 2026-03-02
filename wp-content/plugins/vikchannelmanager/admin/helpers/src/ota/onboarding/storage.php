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
 * The onboarding storage can be used to serialize the processors into the disk
 * and resume them in a second time. This is useful to immediately resume the
 * processor from the last successful task reached.
 * 
 * @since 1.9.2
 */
interface VCMOtaOnboardingStorage
{
	/**
	 * Loads the serialized object for the specified room ID and processor.
	 * In case there isn't a state to resume, an exception will be thrown.
	 * 
	 * @param   int     $roomId     The ID of the room we are trying to onboard.
	 * @param   string  $processor  The identifier of the selected processor.
	 * 
	 * @return  VCMOtaOnboardingProcessor  The serialized processor.
	 * 
	 * @throws  VCMOtaOnboardingExceptionStoragenotfound  In case there is not a serialized processor.
	 */
	public function load(int $roomId, string $processor);

	/**
	 * Saves the current state of the processor.
	 * 
	 * @param   VCMOtaOnboardingProcessor  $processor  The processor to serialize.
	 * 
	 * @return  void
	 */
	public function save(VCMOtaOnboardingProcessor $processor);

	/**
	 * Cleans the current state of the processor.
	 * 
	 * @param   VCMOtaOnboardingProcessor  $processor  The processor to serialize.
	 * 
	 * @return  void
	 */
	public function clean(VCMOtaOnboardingProcessor $processor);
}
