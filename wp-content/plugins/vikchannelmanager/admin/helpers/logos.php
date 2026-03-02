<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * This class is mainly used by Vik Booking to check if a
 * specific channel has a logo defined to be displayed.
 */
class VikChannelManagerLogos
{
	/**
	 * The (raw) 'channel' string stored 
	 * for the booking ID in Vik Booking.
	 *
	 * @var string
	 */
	public 	$provenience;

	/**
	 * The URL to the base path where the logos
	 * (images) of the channels are locate.
	 *
	 * @var string
	 */
	private $baseurl;

	/**
	 * Full channel name before exploding the underscore.
	 * Useful for displaying the custom iCal channel name.
	 *
	 * @var 		string
	 * 
	 * @since 		1.7.0
	 * @requires 	VBO 1.13 (J) - 1.3.0 (WP)
	 */
	private $raw_ota_name;

	/**
	 * An array to map the channels keys (names)
	 * to their corresponding value (logo).
	 *
	 * @var array
	 */
	private $chmap = array(
		'agoda' => 'channel_agoda.png',
		'ycs' => 'channel_agoda.png',
		'ycs50' => 'channel_agoda.png',
		'airbnb' => 'channel_airbnb.png',
		'airbnbapi' => 'channel_airbnb.png',
		'googlehotel' => 'channel_googlehotel.png',
		'googlevr' => 'channel_googlevr.png',
		'bed-and-breakfast' => 'channel_bed-and-breakfast.png',
		'booking' => 'channel_booking.png',
		'despegar' => 'channel_despegar.png',
		'ebookers' => 'channel_ebookers.png',
		'egencia' => 'channel_egencia.png',
		'expedia' => 'channel_expedia.png',
		'flipkey' => 'channel_flipkey.png',
		'holidaylettings' => 'channel_holidaylettings.png',
		'homeaway' => 'channel_homeaway.png',
		'hotels' => 'channel_hotels.png',
		'lastminute' => 'channel_lastminute.png',
		'orbitz' => 'channel_orbitz.png',
		'otelz' => 'channel_otelz.png',
		'tripadvisor' => 'channel_tripadvisor.png',
		'tripconnect' => 'channel_tripadvisor.png',
		'trivago' => 'channel_trivago.png',
		'venere' => 'channel_venere.png',
		'vrbo' => 'channel_vrbo.png',
		'vrboapi' => 'channel_vrbo.png',
		'wimdu' => 'channel_wimdu.png',
		'bedandbreakfast' => 'channel_bedandbreakfasteu.png',
		'feratel' => 'channel_feratel.png',
		'pitchup' => 'channel_pitchup.png',
		'campsitescouk' => 'channel_campsitescouk.png',
		'hostelworld' => 'channel_hostelworld.png',
		'ical' => 'channel_ical.png',
		'ai' => 'channel_ai.png',
		'dac' => 'channel_dac.png',
		'ctrip' => 'channel_ctrip.png',
	);

	/**
	 * Associative map of VBO room IDs and active OTA ids.
	 *
	 * @var 	array
	 * 
	 * @since 	1.9.2
	 */
	private $room_ota_ids = [];

	/**
	 * Associative map of VBO room IDs and OTA account details.
	 *
	 * @var 	array
	 * 
	 * @since 	1.9.2
	 */
	private $room_ota_accounts = [];

	/**
	 * Class constructor.
	 * 
	 * @param 	string 	$provenience 	The channel (source) name.
	 */
	public function __construct($provenience)
	{
		$this->provenience = $provenience;
		$this->setBaseUri();
	}

	/**
	 * Method to set the 'from channel' (raw) name
	 * used to check whether a logo exists.
	 *
	 * @param 	string  $val 			channel main source ([0]).
	 * @param 	string 	$raw_ota_name 	full channel source before exploding the "_".
	 *
	 * @return 	self
	 * 
	 * @see 	param $raw_ota_name was introduced in the version 1.7.0 and VBO 1.13 (J) - 1.3.0 (WP).
	 */
	public function setProvenience($val, $raw_ota_name = '')
	{
		$this->provenience = $val;
		$this->raw_ota_name = $raw_ota_name;

		return $this;
	}

	/**
	 * Main public method that should be called
	 * to retrieve just the name of the logo.
	 *
	 * @return 	boolean
	 */
	public function findLogo()
	{
		return $this->findLogoFromProvenience();
	}

	/**
	 * Main public method that should be called
	 * to retrieve the full logo URL.
	 *
	 * @return 	mixed 	false on failure, string otherwise.
	 */
	public function getLogoURL()
	{
		// always restore the base URI as it could be unset
		$this->setBaseUri();

		if ($fname = $this->findLogoFromProvenience()) {
			return $this->baseurl.$fname;
		}

		return false;
	}

	/**
	 * Method needed to fetch the small version of the logo.
	 *
	 * @return 	mixed 	false on failure, string otherwise.
	 * 
	 * @since 	1.8.24  custom iCal channels will revert to "getLogoURL()"
	 */
	public function getSmallLogoURL()
	{
		// clean provenience
		if (!$this->findLogoFromProvenience()) {
			return false;
		}

		// small logos are located in a different path
		$small_logo_path = implode(DIRECTORY_SEPARATOR, [VCM_ADMIN_PATH, 'assets', 'css', 'images', $this->provenience . '-logo.png']);
		$small_logo_uri  = VCM_ADMIN_URI . 'assets/css/images/' . $this->provenience . '-logo.png';

		return is_file($small_logo_path) ? $small_logo_uri : $this->getLogoURL();
	}

	/**
	 * Attempts to retrieve the tiny version of the channel logo URL.
	 * 
	 * @return 	mixed 	false on failure, URL string to logo otherwise.
	 * 
	 * @since 	1.8.11
	 */
	public function getTinyLogoURL()
	{
		$logo_name = $this->findLogoFromProvenience();

		if (!$logo_name) {
			return false;
		}

		// big logo was found, try to see if the tiny version exists (channel logo name with no dashes)
		$tiny_fname = str_replace(array('channel_', '-'), '', $logo_name);

		if (is_file(VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'channels' . DIRECTORY_SEPARATOR . $tiny_fname)) {
			return $this->baseurl . $tiny_fname;
		}

		return false;
	}

	/**
	 * Private method to clean up the raw
	 * channel name to make it match with
	 * the array map key.
	 *
	 * @return 	boolean
	 */
	private function cleanProvenience()
	{
		if (empty($this->provenience)) {
			return false;
		}

		//separate string from dot(s)
		if (strpos($this->provenience, '.') !== false) {
			$parts = explode('.', $this->provenience);
			/**
			 * If more than one dot replace it, otherwise take the first part of the string.
			 * 
			 * @since 	1.6.18
			 */
			$this->provenience = count($parts) > 2 ? str_replace('.', '', $this->provenience) : $parts[0];
		}

		$this->provenience = strtolower($this->provenience);

		//remove 'a-' for Affiliate Network
		$this->provenience = str_replace('a-', '', $this->provenience);

		return true;
	}

	/**
	 * Private method to check if the cleaned
	 * logo name matches with a key in the map.
	 *
	 * @return 	mixed 	boolean false in case of error, string in case of success
	 */
	private function findLogoFromProvenience()
	{
		if ($this->cleanProvenience() && isset($this->chmap[$this->provenience])) {
			// main channel found
			
			if ($this->provenience == 'ical' && !empty($this->raw_ota_name)) {
				// try to find the custom iCal channel logo
				$custom_ical_logo_uri = $this->findCustomIcalChLogoUri();
				if ($custom_ical_logo_uri !== false) {
					// make the base URI empty since we've got a full URI
					$this->baseurl = '';
					// return full URI to custom iCal channel logo
					return $custom_ical_logo_uri;
				}
			}

			// default channel pre-installed
			return $this->chmap[$this->provenience];
		}

		if (stripos((string)$this->raw_ota_name, 'ical_') !== false) {
			// try to find the custom iCal channel logo
			$custom_ical_logo_uri = $this->findCustomIcalChLogoUri();
			if ($custom_ical_logo_uri !== false) {
				// make the base URI empty since we've got a full URI
				$this->baseurl = '';
				// return full URI to custom iCal channel logo
				return $custom_ical_logo_uri;
			}
		}

		return false;
	}

	/**
	 * Finds the logo URI from the given full channel name
	 * when the main channel is the iCal.
	 * 
	 * @return 		mixed 		false on failure, string otherwise.
	 * 
	 * @since 		1.7.0
	 * @requires 	VBO 1.13 (J) - 1.3.0 (WP)
	 */
	private function findCustomIcalChLogoUri()
	{
		// get the custom channel name
		$parts = explode('_', $this->raw_ota_name);
		if (count($parts) < 2) {
			// no sub-channel found
			return false;
		}
		// get rid of the main channel name (ical)
		unset($parts[0]);
		// custom channel name
		$custom_ical_name = implode('_', $parts);

		// query the db
		$dbo = JFactory::getDbo();
		$record = [];
		$q = "SELECT * FROM `#__vikchannelmanager_ical_channels` WHERE `name` LIKE " . $dbo->quote('%' . $custom_ical_name . '%') . ";";
		try {
			$dbo->setQuery($q);
			$record = $dbo->loadAssoc();
			if (!$record) {
				$record = [];
			}
		} catch (Exception $e) {
			// do nothing
		}

		if ($record) {
			// record not found
			return false;
		}

		return !empty($record['logo']) ? JUri::root() . ltrim($record['logo'], "/") : false;
	}

	/**
	 * Defines/resets the base URI for the logos.
	 * 
	 * @since 	1.7.0
	 */
	private function setBaseUri()
	{
		$this->baseurl = VCM_ADMIN_URI.'assets/css/channels/';
	}

	/**
	 * Gets a list of OTA small logos where the room is mapped.
	 * Useful to display the information of a specific room's mapping.
	 * 
	 * @param 	int 	$idroomvb 	the ID of the room in Vik Booking.
	 * 
	 * @return 	array 	list of OTAs where the room has been mapped.
	 * 
	 * @since 	1.7.1
	 * @since 	1.9.2 	the property map $room_ota_ids is populated.
	 */
	public function getVboRoomLogosMapped($idroomvb)
	{
		$dbo = JFactory::getDbo();
		$otalogos = [];
		$channelnames = [];

		if (($this->room_ota_ids[$idroomvb] ?? [])) {
			// start an empty container for the current room
			$this->room_ota_ids[$idroomvb] = [];
		}

		if (($this->room_ota_accounts[$idroomvb] ?? [])) {
			// start an empty container for the current room
			$this->room_ota_accounts[$idroomvb] = [];
		}

		// fetch first the logos of the API channels (if any)
		$dbo->setQuery(
			$dbo->getQuery(true)
				->select([
					$dbo->qn('r.idroomota'),
					$dbo->qn('r.idchannel'),
					$dbo->qn('r.channel'),
					$dbo->qn('r.prop_name'),
					$dbo->qn('r.prop_params'),
				])
				->from($dbo->qn('#__vikchannelmanager_roomsxref', 'r'))
				->where($dbo->qn('r.idroomvb') . ' = ' . (int) $idroomvb)
				->group($dbo->qn('r.idroomota'))
				->group($dbo->qn('r.idchannel'))
				->group($dbo->qn('r.channel'))
				->group($dbo->qn('r.prop_name'))
				->group($dbo->qn('r.prop_params'))
		);
		$mapping = $dbo->loadAssocList();
		foreach ($mapping as $i) {
			// push the found API channel name to the list
			$channelnames[] = $i['channel'];
			// push the mapped channel ID
			$this->room_ota_ids[$idroomvb][] = $i['idchannel'];
			// decode channel params
			$ch_params = (array) json_decode($i['prop_params'], true);
			$host_main_id = '';
			foreach ($ch_params as $ch_param) {
				$host_main_id = $ch_param;
				break;
			}
			// push channel account details
			$this->room_ota_accounts[$idroomvb][] = [
				'idroomota'    => $i['idroomota'],
				'idchannel'    => $i['idchannel'],
				'channel'      => $i['channel'],
				'account_name' => $i['prop_name'],
				'host_main_id' => $host_main_id,
			];
		}

		// fetch the logos of the iCal channels (if any)
		$ical_ch_ids = [];
		$dbo->setQuery(
			$dbo->getQuery(true)
				->select($dbo->qn('l.channel'))
				->from($dbo->qn('#__vikchannelmanager_listings', 'l'))
				->where($dbo->qn('l.id_vb_room') . ' = ' . (int) $idroomvb)
				->group($dbo->qn('l.channel'))
		);
		$data = $dbo->loadAssocList();	
		foreach ($data as $i) {
			$ch_parts = explode('-', $i['channel']);
			if (in_array($ch_parts[0], $ical_ch_ids)) {
				continue;
			}
			// push iCal channel data
			$ical_ch_ids[] = $ch_parts[0];
		}
		$ical_ch_ids = array_filter($ical_ch_ids);
		if ($ical_ch_ids) {
			$dbo->setQuery(
				$dbo->getQuery(true)
					->select($dbo->qn('c.name'))
					->from($dbo->qn('#__vikchannelmanager_channel', 'c'))
					->where($dbo->qn('c.uniquekey') . ' IN (' . implode(', ', $ical_ch_ids) . ')')
					->group($dbo->qn('c.name'))
					->order($dbo->qn('c.name') . ' ASC')
			);
			$data = $dbo->loadAssocList();
			foreach ($data as $d) {
				// push the found iCal channel name to the list
				$channelnames[] = $d['name'];
			}
		}

		// gather all logos found
		foreach ($channelnames as $chname) {
			// default fallback channel full name and logo (first letter)
			$channel_name = ucfirst($chname);
			$channel_logo = strtoupper(substr($chname, 0, 1));

			// try to find the channel logo
			$this->setProvenience($chname);
			if ($this->cleanProvenience() && isset($this->chmap[$this->provenience])) {
				// big logo was found, try to see if the tiny version exists (channel logo name with no dashes)
				$tiny_fname = str_replace(array('channel_', '-'), '', $this->chmap[$this->provenience]);
				if (is_file(VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'channels' . DIRECTORY_SEPARATOR . $tiny_fname)) {
					$channel_logo = $this->baseurl . $tiny_fname;
				} else {
					// we use the big logo version
					$channel_logo = $this->baseurl . $this->chmap[$this->provenience];
				}
			}

			// push the channel information
			$otalogos[$channel_name] = $channel_logo;
		}

		return $otalogos;
	}

	/**
	 * Sets the relation between a VBO room ID and the OTA channel IDs.
	 * 
	 * @param 	int 	$idroomvb 	The VikBooking room ID.
	 * @param 	array 	$relations 	List of mapped channel IDs.
	 * 
	 * @return 	self
	 * 
	 * @since 	1.9.2
	 */
	public function setRoomOtaRelations($idroomvb, array $relations)
	{
		$this->room_ota_ids[$idroomvb] = array_filter(array_map('intval', $relations));

		return $this;
	}

	/**
	 * Gets the relations between VBO room IDs and the OTA channel IDs.
	 * 
	 * @return 	array
	 * 
	 * @since 	1.9.2
	 */
	public function getRoomOtaRelations()
	{
		return $this->room_ota_ids;
	}

	/**
	 * Gets the relations between VBO room IDs and the OTA accounts.
	 * 
	 * @return 	array
	 * 
	 * @since 	1.9.2
	 */
	public function getRoomOtaAccounts()
	{
		return $this->room_ota_accounts;
	}

	/**
	 * Returns a list of eligible OTAs for onboarding the given room-type
	 * according to the relations that should be set beforehand.
	 * 
	 * @param 	int 	$idroomvb 	The VikBooking room ID.
	 * @param 	bool 	$unlisted 	True for including the unlisted channels without any mapping.
	 * 
	 * @return 	array
	 * 
	 * @since 	1.9.2 	Method introduced with eligible channels (one should be mapped).
	 * @since 	1.9.10 	Extended support to unlisted channels without requiring one OTA to be mapped.
	 */
	public function getRoomOnboardableChannels($idroomvb, $unlisted = true)
	{
		$eligible = [];
		if ($this->room_ota_ids[$idroomvb] ?? []) {
			// attempt read the eligible channel(s) from the current mapping information (either Booking.com or Airbnb required)
			$eligible = VCMOtaOnboarding::getInstance()->getEligibleChannels($idroomvb, $this->room_ota_ids[$idroomvb]);
		}

		if (!$eligible && $unlisted) {
			// attempt to get the unlisted channels (either Booking.com or Airbnb required as long as one or the other is mapped)
			$eligible = VCMOtaOnboarding::getInstance()->getUnlistedChannels($idroomvb, ($this->room_ota_ids[$idroomvb] ?? []));
		}

		$onboardingStorage = new VCMOtaOnboardingStorageConfig;

		// list of supported onboardable providers
		$supportedProviders = [
			'bookingcom' => [
				'key' => VikChannelManagerConfig::BOOKING,
				'name' => 'Booking.com',
			],
			'airbnbapi' => [
				'key' => VikChannelManagerConfig::AIRBNBAPI,
				'name' => 'Airbnb',
			],
		];

		/**
		 * In case there is a pending onboarding process, we should still consider the channel as eligible for this room.
		 * This because the existing relation in VCM would treat it as already mapped and onboarded, even if an issue
		 * occurred during the check/open process.
		 * 
		 * @since 1.9.14
		 */
		foreach ($supportedProviders as $providerClass => $provider) {
			$uniqueKey = $provider['key'];

			if (!isset($eligible[$uniqueKey])) {
				try {
					// check if we have a pending onboarding for this listing-channel relation
					$onboardingStorage->load($idroomvb, $providerClass);

					// pending onboarding, channel still eligible
					$eligible[$uniqueKey] = $provider['name'];
				} catch (VCMOtaOnboardingExceptionStoragenotfound $notStartedYet) {
					// onboarding procedure never started or completed
				}
			}
		}

		return $eligible;
	}
}
