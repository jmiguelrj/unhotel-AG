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
 * OTA Onboarding implementation.
 * 
 * @since 	1.9.2
 */
final class VCMOtaOnboarding
{
	/**
	 * The singleton instance of the class.
	 *
	 * @var  VCMOtaOnboarding
	 */
	private static $instance = null;

	/**
	 * @var  bool
	 */
	private $has_bookingcom = false;

	/**
	 * @var  bool
	 */
	private $has_airbnb = false;

	/**
	 * Proxy to construct the object.
	 * 
	 * @return 	self
	 */
	public static function getInstance()
	{
		if (is_null(static::$instance)) {
			static::$instance = new static;
		}

		return static::$instance;
	}

	/**
	 * Class constructor is private.
	 */
	private function __construct()
	{
		$dbo = JFactory::getDbo();

		// check whether Booking.com is available
		$this->has_bookingcom = (bool) VikChannelManager::getChannel(VikChannelManagerConfig::BOOKING);

		if ($this->has_bookingcom) {
			// check if any rooms were ever mapped
			$dbo->setQuery(
				$dbo->getQuery(true)
					->select($dbo->qn('prop_params'))
					->from($dbo->qn('#__vikchannelmanager_roomsxref'))
					->where($dbo->qn('idchannel') . ' = ' . (int) VikChannelManagerConfig::BOOKING)
			);

			if (!$dbo->loadResult()) {
				// we need at least one mapped Hotel ID
				$this->has_bookingcom = false;
			}
		}

		// check whether Airbnb is available
		$this->has_airbnb = (bool) VikChannelManager::getChannel(VikChannelManagerConfig::AIRBNBAPI);

		if ($this->has_airbnb) {
			// check if any rooms were ever mapped
			$dbo->setQuery(
				$dbo->getQuery(true)
					->select($dbo->qn('prop_params'))
					->from($dbo->qn('#__vikchannelmanager_roomsxref'))
					->where($dbo->qn('idchannel') . ' = ' . (int) VikChannelManagerConfig::AIRBNBAPI)
			);

			if (!$dbo->loadResult()) {
				// we need at least one mapped Host ID
				$this->has_airbnb = false;
			}
		}
	}

	/**
	 * Returns the eligible channels where this room could be onboarded.
	 * 
	 * @param 	int 	$id_room 	The VikBooking room ID.
	 * @param 	array 	$relations 	The current room/OTA-ID relations.
	 * 
	 * @return 	array 				Associative list of eligible channel IDs for onboarding.
	 */
	public function getEligibleChannels($id_room, array $relations = [])
	{
		if (!$this->has_bookingcom && !$this->has_airbnb) {
			return [];
		}

		if (!$relations) {
			// gather the OTA relations for the given room, if any
			$dbo = JFactory::getDbo();

			$dbo->setQuery(
				$dbo->getQuery(true)
					->select($dbo->qn('idchannel'))
					->from($dbo->qn('#__vikchannelmanager_roomsxref'))
					->where($dbo->qn('idroomvb') . ' = ' . (int) $id_room)
					->group($dbo->qn('idchannel'))
			);

			$relations = array_column($dbo->loadAssocList(), 'idchannel');
		}

		if ($this->has_bookingcom && in_array(VikChannelManagerConfig::AIRBNBAPI, $relations) && !in_array(VikChannelManagerConfig::BOOKING, $relations)) {
			// onboarding onto Booking.com requires a corresponding Airbnb listing and none mapped on Booking.com
			return [
				VikChannelManagerConfig::BOOKING => 'Booking.com',
			];
		}

		if ($this->has_airbnb && in_array(VikChannelManagerConfig::BOOKING, $relations) && !in_array(VikChannelManagerConfig::AIRBNBAPI, $relations)) {
			// onboarding onto Airbnb requires a corresponding Booking.com listing and none mapped on Airbnb
			return [
				VikChannelManagerConfig::AIRBNBAPI => 'Airbnb',
			];
		}

		return [];
	}

	/**
	 * Returns a list of unlisted channels for the given room ID on which
	 * the listing could be onboarded. Should be called after having checked
	 * that the listing does NOT have an eligible channel for the onboarding.
	 * It is sufficient for either Booking.com or Airbnb to have one listing mapped.
	 * 
	 * @param 	int 	$id_room 	The VikBooking room ID.
	 * @param 	array 	$relations 	The current room/OTA-ID relations.
	 * 
	 * @return 	array 				List of channels unlisted for the onboarding.
	 * 
	 * @since 	1.9.10 				Introduced to faciliate the onboarding of new listings.
	 */
	public function getUnlistedChannels($id_room, array $relations = [])
	{
		$unlisted = [];

		if ($this->has_bookingcom && !in_array(VikChannelManagerConfig::BOOKING, $relations)) {
			// push Booking.com as an unlisted channel for the possible onboarding
			$unlisted[VikChannelManagerConfig::BOOKING] = 'Booking.com';
		}

		if ($this->has_airbnb && !in_array(VikChannelManagerConfig::AIRBNBAPI, $relations)) {
			// push Booking.com as an unlisted channel for the possible onboarding
			$unlisted[VikChannelManagerConfig::AIRBNBAPI] = 'Airbnb';
		}

		return $unlisted;
	}
}
