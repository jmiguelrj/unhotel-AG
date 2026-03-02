<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2022 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Google Hotel and Google Vacation Rentals check status and perform operations.
 * 
 * @since 	1.8.9
 * @since 	1.9.4 	added support for Google Vacation Rentals.
 */
class VCMGhotelStatus
{
	/**
	 * @var 	array
	 */
	protected $channel = [];

	/**
	 * @var 	array
	 * 
	 * @since 	1.9.4
	 */
	protected $options = [];

	/**
	 * Proxy used to construct the object.
	 * 
	 * @param 	array  $channel  the channel record for Google Hotel.
	 * 
	 * @return 	self   			 a new instance of this class.
	 */
	public static function getInstance(?array $channel = null)
	{
		if (!$channel) {
			// default to Google Hotel
			$channel = VikChannelManager::getChannel(VikChannelManagerConfig::GOOGLEHOTEL);
		}

		return new static($channel);
	}

	/**
	 * Class constructor.
	 * 
	 * @param 	array  $channel  the channel record for Google Hotel or VR.
	 */
	public function __construct(array $channel)
	{
		$this->channel = $channel;
	}

	/**
	 * Binds a list of given options.
	 * 
	 * @param 	array 	$options 	The options to bind.
	 * 
	 * @return 	VCMGhotelStatus
	 * 
	 * @since 	1.9.4
	 */
	public function bindOptions(array $options)
	{
		$this->options = $options;

		return $this;
	}

	/**
	 * Displays the current mapping information for Google Hotel or VR.
	 * 
	 * @return 	array
	 */
	public function showMapping()
	{
		$accounts = [];

		$result = [
			'channel_uniquekey'  => (int) ($this->channel['uniquekey'] ?? 0),
			'number_of_accounts' => 0,
			'rooms_mapping' 	 => [],
		];

		$dbo = JFactory::getDbo();

		$dbo->setQuery(
			$dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikchannelmanager_roomsxref'))
				->where($dbo->qn('idchannel') . ' = ' . $result['channel_uniquekey'])
		);

		$result['rooms_mapping'] = $dbo->loadAssocList();

		if (!$result['rooms_mapping']) {
			return $result;
		}

		foreach ($result['rooms_mapping'] as $room_mapped) {
			if (!in_array($room_mapped['prop_params'], $accounts)) {
				$accounts[] = $room_mapped['prop_params'];
			}
		}

		$result['number_of_accounts'] = count($accounts);

		return $result;
	}

	/**
	 * Prepares and transmits the mapping information to update any possible
	 * outdated detail about VAT/GST or room-types and rate plans relations.
	 * 
	 * @return 	array
	 */
	public function transmitPropertyData()
	{
		// check if the option "hotel_id" is available
		$hotel_id = $this->options['hotel_id'] ?? '';

		if (!empty($hotel_id) && $this->channel['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL) {
			// convert the value to a number
			$hotel_id = preg_replace("/[^0-9]+/", '', $hotel_id);

			// take care of the channel params
			if (is_string($this->channel['params'])) {
				$this->channel['params'] = json_decode($this->channel['params'], true);
				$this->channel['params'] = is_array($this->channel['params']) ? $this->channel['params'] : [];
			}

			// validate params structure
			if (!is_array($this->channel['params']) || empty($this->channel['params']['hotelid'])) {
				VCMHttpDocument::getInstance()->close(500, 'Invalid hotel ID in channel params');
			}

			// inject requested hotel ID in case of multiple accounts
			$this->channel['params']['hotelid'] = $hotel_id;
		}

		// list of room IDs involved
		$room_ids = $this->options['room_id'] ?? [];
		if ($room_ids && is_string($room_ids)) {
			// turn it into an array
			$room_ids = [$room_ids];
		}

		if (!$room_ids && $this->channel['uniquekey'] == VikChannelManagerConfig::GOOGLEVR) {
			// for Google Vacation Rentals we require the transaction to be for a given listing
			VCMHttpDocument::getInstance()->close(400, 'Listing transactions for Google Hotel require one listing ID as -room_id-.');
		}

		// re-transmit the mapping information for this account
		$re_transmit = VikChannelManager::transmitPropertyData($this->channel, $room_ids);

		if ($re_transmit === false) {
			// generic error
			VCMHttpDocument::getInstance()->close(500, 'Could not perform the request due to a generic error');
		}

		if (is_string($re_transmit)) {
			// error explanation
			VCMHttpDocument::getInstance()->close(500, $re_transmit);
		}

		if (is_object($re_transmit) || is_array($re_transmit) || $re_transmit === true) {
			// execution was successful
			return [
				'success' => 1,
				'channel' => $this->channel,
				'result'  => $re_transmit,
			];
		}

		// unexpected result
		VCMHttpDocument::getInstance()->close(500, 'Unexpected result');
	}
}
