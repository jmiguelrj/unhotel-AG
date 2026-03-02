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
 * Interface used to define a processor able to onboard a local room
 * on a given OTA.
 * 
 * @since 1.9.2
 */
interface VCMOtaOnboardingProcessor
{
	/**
	 * Returns the ID of the room invoved in the onboarding procedure.
	 * 
	 * @return  int
	 */
	public function getRoomID();

	/**
	 * Executes the onboarding procedure.
	 * The processor should be responsible of resuming from the last failed step.
	 * 
	 * @param   object  $data  The onboarding details.
	 * 
	 * @return  void
	 * 
	 * @throws  Exception
	 */
	public function onboard(object $data);

	/**
	 * Returns the information about the onboarding progress.
	 * 
	 * @return  object
	 */
	public function getProgressData();

	/**
	 * Checks whether the onboarding procedure has been completed successfully.
	 * 
	 * @return  bool
	 */
	public function isCompleted();
}
