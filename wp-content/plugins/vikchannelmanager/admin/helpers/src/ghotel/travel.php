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
 * New Google Hotel Travel Partner helper class.
 * 
 * @since 	1.8.4
 */
class VCMGhotelTravel
{
	/**
	 * @var 	string
	 */
	protected $property_id = null;

	/**
	 * @var 	int
	 */
	protected $channel_id = 26;

	/**
	 * Class constructor.
	 */
	public function __construct()
	{
		// get the current property ID
		$this->property_id = VikChannelManager::getHotelInventoryID();

		/**
		 * Check if we are on a multiple account.
		 * 
		 * @since 	1.8.6
		 */
		$module = VikChannelManager::getActiveModule(true);
		$module['params'] = json_decode($module['params'], true);
		$module['params'] = !is_array($module['params']) ? [] : $module['params'];
		if (class_exists('VikChannelManagerConfig') && $module['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL) {
			$current_hid = null;
			foreach ($module['params'] as $param_name => $param_value) {
				// grab the first channel parameter
				$current_hid = $param_value;
				break;
			}

			if (!empty($current_hid)) {
				// use this account
				$this->property_id = preg_replace("/[^0-9]+/", '', $current_hid);
			}
		}
	}

	/**
	 * Retrieves (or sets) the current live status from the scorecard data.
	 * 
	 * @param 	int 	$set_status 	the new optional live status to set.
	 * 
	 * @return 	int|bool 	false on failure or int for current live status.
	 */
	public function getLiveStatus($set_status = null)
	{
		$dbo = JFactory::getDbo();

		$param_name = "propscore_{$this->channel_id}_{$this->property_id}";

		$scorecard_data = null;

		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`=" . $dbo->quote($param_name);
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$scorecard_data = $dbo->loadResult();
		}

		if (empty($scorecard_data)) {
			// try fetching the first useful scorecard
			$param_name = "propscore_{$this->channel_id}_";

			$q = "SELECT `param`, `setting` FROM `#__vikchannelmanager_config` WHERE `param` LIKE " . $dbo->quote($param_name . '%');
			$dbo->setQuery($q, 0, 1);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$record = $dbo->loadAssoc();
				$param_name = $record['param'];
				$scorecard_data = $record['setting'];
			}
		}

		if (empty($scorecard_data)) {
			return false;
		}

		$scorecard_data = json_decode($scorecard_data);

		if (!is_object($scorecard_data) || !isset($scorecard_data->data) || !isset($scorecard_data->data->hotel_status)) {
			return false;
		}

		if (!isset($scorecard_data->data->hotel_status->summary)) {
			return false;
		}

		if (!is_null($set_status)) {
			// update the current status
			$scorecard_data->data->hotel_status->summary->is_live = (int)$set_status;

			$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=" . $dbo->quote(json_encode($scorecard_data)) . " WHERE `param`=" . $dbo->quote($param_name) . ";";
			$dbo->setQuery($q);
			$dbo->execute();

			// update the session
			$session = JFactory::getSession();
			$scorecard = $session->get(str_replace('propscore', 'scorecard', $param_name), '', 'vcm-scorecard');
			if (is_object($scorecard)) {
				$scorecard->data->hotel_status->summary->is_live = $scorecard_data->data->hotel_status->summary->is_live;
			}
		}

		// return the current status
		return (int)$scorecard_data->data->hotel_status->summary->is_live;
	}

	/**
	 * Updates the current live status on the scorecard.
	 * 
	 * @param 	int 	$set_status 	the new live status to set.
	 * 
	 * @return 	bool 	false on failure, true otherwise.
	 */
	public function updateLiveStatus($set_status = 0)
	{
		return $this->getLiveStatus($set_status);
	}

	/**
	 * Returns the current property ID on the inventory.
	 * 
	 * @return 	string 	the current property ID.
	 */
	public function getPropertyID()
	{
		return $this->property_id;
	}

	/**
	 * Sets the current property ID on the inventory.
	 * 
	 * @param 	int 	$hid 	the hotel inventory ID to force.
	 * 
	 * @return 	self
	 * 
	 * @since 	1.8.6
	 */
	public function setPropertyID($hid = 0)
	{
		return $this->property_id;
	}
}
