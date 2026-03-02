<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2019 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * This class is used to store, read and retrieve information about
 * the various opportunities made available by certain OTAs.
 * 
 * @since 	1.6.13
 */
class VCMOpportunityHandler
{
	/**
	 * The singleton instance of the class.
	 *
	 * @var 	VCMOpportunityHandler
	 */
	protected static $instance = null;

	/**
	 * The list of supported channel unique keys.
	 * 
	 * @var 	array
	 * 
	 * @since 	1.8.3 	Airbnb API was added.
	 */
	protected $idchannels = array(
		4  => 'booking.com',
		25 => 'airbnbapi',
	);

	/**
	 * An array of OTA room ID + VBO room ID
	 * key-value pairs. Used to deal with VBO.
	 * 
	 * @var 	array
	 * 
	 * @since 	1.7.3
	 */
	protected $rooms_mapping = array();

	/**
	 * The currently parsed channel unique-key.
	 * 
	 * @var 	int
	 * 
	 * @since 	1.7.3
	 */
	protected $unique_key = null;

	/**
	 * The channel unique-key that was used to download
	 * the opportunities the last time. This is to skip
	 * automatically this unique key on the next retrieval.
	 * 
	 * @var 	int
	 * 
	 * @since 	1.8.3
	 */
	protected $last_downloaded_key = null;

	/**
	 * The error occurred
	 * 
	 * @var 	string
	 */
	protected $error = '';

	/**
	 * Class constructor is protected.
	 *
	 * @see 	getInstance()
	 */
	protected function __construct()
	{
		if (!class_exists('VikChannelManager')) {
			// require the main VCM library as the class is probably being invoked by VBO
			require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikchannelmanager.php';
		}

		if (!class_exists('VikChannelManagerConfig')) {
			// require the config library as the class is probably being invoked by VBO and errors may occur
			require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'vcm_config.php';
		}
	}

	/**
	 * Returns the global class object, either
	 * a new instance or the existing instance
	 * if the class was already instantiated.
	 *
	 * @return 	self 	A new instance of the class.
	 */
	public static function getInstance()
	{
		if (is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Returns all the supported channels for the opportunities.
	 * 
	 * @return 	array 	the list of supported channel unique keys.
	 */
	public function getSupportedChannels()
	{
		return $this->idchannels;
	}

	/**
	 * Checks whether it's time to request the opportunities to the
	 * e4jConnect servers, depending on when the last request was made.
	 * 
	 * @return 	boolean 	true if the request can be made, false otherwise.
	 */
	public function shouldRequestOpportunities()
	{
		$dbo = JFactory::getDbo();

		/**
		 * We need to be able to turn off the download of new opportunities forever
		 * as some clients with 28 different Hotel IDs ran into an execution timeout
		 * issue by causing a blank page in the back-end Dashboard of Vik Booking.
		 * Such cases should download the opportunities manually for each account,
		 * and never globally for all accounts or the process will take too long to complete.
		 * 
		 * @since 	1.7.2
		 */
		if (class_exists('VikRequest') && VikRequest::getInt('enable_vcm_opportunities', 0, 'request')) {
			$action = VikRequest::getInt('enable_vcm_opportunities', 0, 'request');
			// clean up any previously set value in config
			$q = "DELETE FROM `#__vikchannelmanager_config` WHERE `param`='enable_vcm_opportunities';";
			$dbo->setQuery($q);
			$dbo->execute();
			// check action requested
			if ($action < 0) {
				// negative values means no opportunities should ever be downloaded
				$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('enable_vcm_opportunities', '-1');";
				$dbo->setQuery($q);
				$dbo->execute();

				// return false and exit without looking any further
				return false;
			}
			if ($action > 0) {
				// with a positive value we restore the possibility of downloading the opportunities
				$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('enable_vcm_opportunities', '1');";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}

		// we now check if opportunities download has been disabled
		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='enable_vcm_opportunities'";
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$forced_action = (int)$dbo->loadResult();
			if ($forced_action < 0) {
				// opportunities download is disabled
				return false;
			}
		}

		// grab the last execution timestamp
		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='opportunities_last_check';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// always request opportunities at the first check
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('opportunities_last_check', ".$dbo->quote(time()).");";
			$dbo->setQuery($q);
			$dbo->execute();
			
			return true;
		}
		$last_check_ts = $dbo->loadResult();

		/**
		 * In order to determine the best retrieval limit per channel,
		 * we count how many of the supported channels are available.
		 * 
		 * @since 	1.8.3
		 */
		$q = "SELECT `uniquekey` FROM `#__vikchannelmanager_channel` WHERE `uniquekey` IN (" . implode(', ', array_keys($this->idchannels)) . ");";
		$dbo->setQuery($q);
		$dbo->execute();
		$ch_available = (int)$dbo->getNumRows();
		$ch_available = $ch_available > 0 ? $ch_available : 1;

		// limit set to every week (per channel)
		$lim_seconds = 86400 * 7 / $ch_available;
		
		return ((time() - $last_check_ts) > $lim_seconds);
	}

	/**
	 * Loads the various opportunities stored in the database.
	 * 
	 * @param 	array 	$filters 	associative array with a filters map.
	 * @param 	int 	$lim_start 	the query start limit.
	 * @param 	int 	$lim_list 	the query list limit.
	 * 
	 * @return 	array 	an array of objects loaded for each record
	 */
	public function loadOpportunities($filters = array(), $lim_start = 0, $lim_list = 20)
	{
		$dbo = JFactory::getDbo();
		$clauses = array();
		foreach ($filters as $field => $value) {
			array_push($clauses, "`{$field}`=" . $dbo->quote($value));
		}

		$q = "SELECT * FROM `#__vikchannelmanager_opportunities`".(count($clauses) ? " WHERE " . implode(' AND ', $clauses) : "")." ORDER BY `status` ASC, `dt` DESC";
		$dbo->setQuery($q, $lim_start, $lim_list);
		$dbo->execute();

		if (!$dbo->getNumRows()) {
			return array();
		}

		return $dbo->loadObjectList();
	}

	/**
	 * Downloads the opportunities from the e4jConnect servers by making
	 * a request with a higher priority for the Slave rather than the Master.
	 * The request is made for all the mapped hotel IDs of the supported channels.
	 * If no rooms have been mapped, false is returned without making the request.
	 * 
	 * @param 	int 	$unique_key 	the channel unique key.
	 * 
	 * @return 	mixed 	false on failure, number of stored records otherwise.
	 */
	public function downloadOpportunities($unique_key = 0)
	{
		$dbo = JFactory::getDbo();
		if (empty($unique_key)) {
			/**
			 * Check what was the last channel unique key used to download opportunities
			 * so that we can calculate what will be the next one for this current loop.
			 * 
			 * @since 	1.8.3
			 */
			$last_key_used = $this->checkLastKeyDownloaded();
			if (!empty($last_key_used) && count($this->idchannels) > 1) {
				// more than one channel supporting opportunities, so get the next unique key to be parsed
				$only_keys = array_keys($this->idchannels);
				$last_key_pos = array_search($last_key_used, $only_keys);
				if ($last_key_pos === false) {
					// should never happen unless we remove the opportunities capability from a channel
					$unique_key = key($this->idchannels);
				} else {
					// if last key position is last, get the 0th key, else get the next key
					$unique_key = $last_key_pos == (count($only_keys) - 1) ? $only_keys[0] : $only_keys[($last_key_pos + 1)];
				}
			} else {
				// default to first channel key supported
				$unique_key = key($this->idchannels);
			}
		}

		if (!isset($this->idchannels[$unique_key])) {
			// unsupported channel unique key
			return false;
		}

		// load & validate configuration
		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			// missing API Key
			return false;
		}

		// set current unique key immediately before starting
		$this->unique_key = $unique_key;

		// immediately update the last key used
		$this->updateLastKeyDownloaded();
		
		// build a map of property ID (first param) and property name
		$accounts_map = array();
		$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . $dbo->quote($unique_key) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// no mapping records found
			return false;
		}
		$xref = $dbo->loadAssocList();
		foreach ($xref as $ref) {
			$params = json_decode($ref['prop_params']);
			if (empty($params) || is_scalar($params)) {
				continue;
			}
			$main_param = '';
			foreach ($params as $pk => $pp) {
				if (strpos($pk, 'hotelid') !== false || strpos($pk, 'user_id') !== false) {
					// take this parameter, which should be the hotel ID or the user (host) ID
					$main_param = (string)$pp;
					break;
				}
			}
			if (empty($main_param) || isset($accounts_map[$main_param])) {
				continue;
			}
			// push parameter and property name
			$accounts_map[$main_param] = $ref['prop_name'];
		}

		if (!count($accounts_map)) {
			// no valid hotel IDs found for the request
			return false;
		}

		$xml_props = '';
		foreach ($accounts_map as $hid => $hname) {
			$xml_props .= "<Property>{$hid}</Property>\n";
		}

		// build the XML request for the Opportunity Read request (defaults on Slave)
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=oppr&c=" . $this->idchannels[$unique_key];
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager OPPR Request e4jConnect.com - '.ucwords($this->idchannels[$unique_key]).' -->
<OpportunityReadRQ xmlns="http://www.e4jconnect.com/channels/opprrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$apikey.'"/>
	<Properties lang="' . substr(strtoupper(JFactory::getLanguage()->getTag()), 0, 2) . '">
		' . $xml_props . '
	</Properties>
</OpportunityReadRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			$this->setError(@curl_error($e4jC->getCurlHeader()));
			return false;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			// update last time checked, trigger error and return false to avoid multiple API errors
			$this->updateLastCheck();
			$this->setError(VikChannelManager::getErrorFromMap($rs));

			return false;
		}

		// attempt to JSON-decode the response obtained
		$pool = json_decode($rs);
		if (!is_object($pool) && !is_array($pool)) {
			// unexpected response
			$this->setError("Invalid response:\n" . $rs);
			return false;
		}

		// store and count opportunities
		$tot_stored = 0;
		foreach ($pool as $hid => $opportunities) {
			foreach ($opportunities as $opportunity) {
				// clone opportunity object returned
				$opp = (object)$opportunity;
				// push the property name
				$opp->prop_name = $accounts_map[(string)$hid];
				if ($this->storeOpportunity($opp) !== false) {
					$tot_stored++;
				}
			}
		}

		// update last time checked and return the number of records stored
		$this->updateLastCheck();

		return $tot_stored;
	}

	/**
	 * Checks whether an opportunity exists with the
	 * given identifier and channel source.
	 * 
	 * @param 	string 	$identifier 	the channel identifier string for the opportunity.
	 * @param 	steing 	$hid 			the hotel ID.
	 * @param 	string 	$channel 		the name of the source channel.
	 * 
	 * @return 	mixed 					existing opportunity ID or false.
	 */
	public function opportunityExists($identifier, $hid, $channel = '')
	{
		if (empty($identifier) || empty($hid)) {
			// cannot look for a similar opportunity without this information
			return true;
		}

		// database handler
		$dbo = JFactory::getDbo();

		$q = "SELECT `id` FROM `#__vikchannelmanager_opportunities` WHERE `prop_first_param`=" . $dbo->quote($hid) . " AND `identifier`=" . $dbo->quote($identifier) . (!empty($channel) ? " AND `channel`=" . $dbo->quote($channel) : "");
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows()) {
			return $dbo->loadResult();
		}

		return false;
	}

	/**
	 * Stores an opportunity object onto the database if it
	 * does not exist already, by checking all fields available.
	 * 
	 * @param 	object 	$opp 	the opportunity object to parse.
	 * @param 	string 	$hid 	the hotel ID.
	 * 
	 * @return 	mixed 			false if the record could not be stored, the ID of the record otherwise.
	 */
	public function storeOpportunity($opp)
	{
		if (!$opp instanceof stdClass || !count(get_object_vars($opp)) || !isset($opp->data) || !isset($opp->identifier)) {
			// invalid object structure
			return false;
		}

		// source channel
		$source_channel = isset($opp->channel) ? $opp->channel : 'vikbooking';

		// hotel/host (user) ID (we allow multiple values)
		$hid = isset($opp->hotel_id) ? $opp->hotel_id : null;
		$hid = empty($hid) && isset($opp->user_id) ? $opp->user_id : $hid;
		$hid = empty($hid) && isset($opp->host_id) ? $opp->host_id : $hid;

		/**
		 * Opportunities with identifier like "high_demand_dates_inventory" should always be updated.
		 * 
		 * @since 	1.7.2
		 */
		$old_opp_id = $this->opportunityExists($opp->identifier, $hid, $source_channel);
		if ($old_opp_id !== false && strpos($opp->identifier, 'high_demand_dates') === false) {
			// do not proceed because this type of opportunity exists
			return false;
		}

		/**
		 * Opportunities for "high_demand_dates" will be recorded on Vik Booking
		 * either as festivities or as room-day notes.
		 * 
		 * @since 	1.7.3
		 */
		if (strpos($opp->identifier, 'high_demand_dates') !== false) {
			$this->storeHighDemandDates($opp);
		}

		// database handler
		$dbo = JFactory::getDbo();

		// record data
		$row = new stdClass;

		// prepare fields
		if ($old_opp_id) {
			// record will be updated
			$row->id = $old_opp_id;
		}
		$row->dt 		 		= JFactory::getDate()->toSql(true);
		$row->prop_first_param 	= $hid;
		$row->prop_name  		= isset($opp->prop_name) ? $opp->prop_name : null;
		$row->identifier 		= isset($opp->identifier) ? $opp->identifier : null;
		$row->channel 	 		= $source_channel;
		$row->title  	 		= isset($opp->title) ? $opp->title : null;
		$row->data 		 		= json_encode($opp->data);

		if (!$old_opp_id && !$dbo->insertObject('#__vikchannelmanager_opportunities', $row, 'id')) {
			// create new opportunity record
			return false;
		} elseif ($old_opp_id && !$dbo->updateObject('#__vikchannelmanager_opportunities', $row, 'id')) {
			// update opportunity record
			return false;
		}

		return isset($row->id) ? (int)$row->id : false;
	}

	/**
	 * Updates an opportunity record. Requires an object with
	 * the proper properties set for the update-query.
	 * 
	 * @param 	object 	$opp 	stdClass object of the record.
	 * 
	 * @return 	boolean 		true on success, false otherwise.
	 */
	public function updateOpportunity($opp)
	{
		if (!$opp instanceof stdClass || !isset($opp->id)) {
			// invalid object structure
			return false;
		}

		// database handler
		$dbo = JFactory::getDbo();

		if ($dbo->updateObject('#__vikchannelmanager_opportunities', $opp, 'id')) {
			return false;
		}

		return true;
	}

	/**
	 * Attempts to store the high demand dates in Vik Booking.
	 * 
	 * @param 	object 	$opp 	stdClass object for the opportunity downloaded/passed.
	 * 
	 * @return 	boolean
	 * 
	 * @since 	1.7.3
	 */
	public function storeHighDemandDates($opp)
	{
		if (!$opp instanceof stdClass || !count(get_object_vars($opp)) || !isset($opp->data) || !isset($opp->identifier)) {
			// invalid object structure
			return false;
		}

		if (!is_object($opp->data) || !isset($opp->data->implementation_data) || !is_array($opp->data->implementation_data) || !count($opp->data->implementation_data)) {
			// no high demand dates
			return false;
		}

		// build the rooms mapping information for the current channel
		if (!$this->buildRoomsMapping()) {
			// no rooms mapped, unable to proceed
			return false;
		}

		// get the high demand dates for all room IDs
		$otarooms_dates = array();
		$all_hd_dates 	= array();
		$dates_roomsref = array();
		foreach ($opp->data->implementation_data as $hdd) {
			if (!is_object($hdd) || empty($hdd->entity_id)) {
				continue;
			}
			$entity_parts = explode('_', $hdd->entity_id);
			if (count($entity_parts) < 2) {
				// invalid structure, which needs to be like 210766504_2020-07-11
				continue;
			}
			if (!isset($this->rooms_mapping[$entity_parts[0]])) {
				// this OTA room ID was not mapped
				continue;
			}
			if (!strtotime($entity_parts[1])) {
				// invalid date provided
				continue;
			}

			// push the high demand date in the global pool
			if (!in_array($entity_parts[1], $all_hd_dates)) {
				array_push($all_hd_dates, $entity_parts[1]);
			}

			// push room to date reference
			if (!isset($dates_roomsref[$entity_parts[1]])) {
				$dates_roomsref[$entity_parts[1]] = array();
			}
			if (!in_array($this->rooms_mapping[$entity_parts[0]]['name'], $dates_roomsref[$entity_parts[1]])) {
				// room not added to the references for this high demand date, push it
				array_push($dates_roomsref[$entity_parts[1]], $this->rooms_mapping[$entity_parts[0]]['name']);
			}

			// push the high demand date for this OTA room ID
			if (!isset($otarooms_dates[$entity_parts[0]])) {
				$otarooms_dates[$entity_parts[0]] = array();
			}
			if (!in_array($entity_parts[1], $otarooms_dates[$entity_parts[0]])) {
				// date not added to this room, push it
				array_push($otarooms_dates[$entity_parts[0]], $entity_parts[1]);
			}
		}

		if (!count($otarooms_dates)) {
			// no valid high demand dates found
			return false;
		}

		/**
		 * We store in VBO the room-day notes as well as the "festivities" for the HDD.
		 * This requires a recent version of Vik Booking, at least the 1.13.5/1.3.5.
		 * To prevent Fatal Errors from happening, we wrap the execution in a try-catch.
		 */
		$hdd_stored = 0;
		$note_type  = 'high_demand_dates';
		try {
			if (!class_exists('VikBooking')) {
				// require the main VBO library if not available
				require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';
			}

			// build critical date object (equal for every room-day note as it's the same opportunity)
			$rdaynote_name  = isset($opp->data->title) ? $opp->data->title : 'High Demand Date';
			$rdaynote_intro = (isset($this->idchannels[(int)$this->unique_key]) ? ucwords($this->idchannels[(int)$this->unique_key]) . ' - ' : '') . date('c');
			$rdaynote_descr = isset($opp->data->description) ? $opp->data->description : '';
			$rdaynote_descr = $rdaynote_intro . "\n" . $rdaynote_descr;
			$rdaynote_type  = $note_type;
			$new_note = array(
				'name'  => $rdaynote_name,
				'type'  => $rdaynote_type,
				'descr' => $rdaynote_descr,
			);

			// parse room-day notes
			$notes = VikBooking::getCriticalDatesInstance();
			foreach ($otarooms_dates as $otarid => $hdds) {
				// VBO Room ID
				$vborid = $this->rooms_mapping[$otarid]['idroomvb'];
				foreach ($hdds as $hdd) {
					// make sure this room-day note does not exist already
					if ($notes->dayNoteExists($hdd, $vborid, 0, $rdaynote_type)) {
						// already stored
						continue;
					}
					// store room-day note
					$result = $notes->storeDayNote($new_note, $hdd, $vborid, 0);
					if ($result) {
						$hdd_stored++;
					}
				}
			}

			// build fest array (equal for each date as it's the same opportunity)
			$new_fest = array(
				'trans_name' => $rdaynote_name,
			);

			// parse festivities
			$fests = VikBooking::getFestivitiesInstance();
			foreach ($all_hd_dates as $hdd) {
				if ($fests->festivityExists($hdd, $note_type)) {
					// this fest type already exists, but we want to have it updated with the new rooms information
					$fests->deleteFestivity($hdd, -1, $note_type);
				}
				$fest_descr = $rdaynote_descr;
				if (isset($dates_roomsref[$hdd]) && count($dates_roomsref[$hdd])) {
					$fest_descr .= "\n\n" . implode(', ', $dates_roomsref[$hdd]);
				}
				$result = $fests->storeFestivity($hdd, $new_fest, $note_type, $fest_descr);
				if ($result) {
					$hdd_stored++;
				}
			}
		} catch (Exception $e) {
			// VBO is probably outdated
			$hdd_stored = 0;
		}

		return ($hdd_stored > 0);
	}

	/**
	 * Updates the last time the opportunities were retrieved from e4jConnect.
	 * 
	 * @param 	int 	$time 	the last check timestamp to set. Defaults to now.
	 * 
	 * @return 	void
	 */
	public function updateLastCheck($time = 0)
	{
		$dbo  = JFactory::getDbo();
		$time = empty($time) ? time() : $time;
		
		$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=".$dbo->quote($time)." WHERE `param`='opportunities_last_check';";
		$dbo->setQuery($q);
		$dbo->execute();
	}

	/**
	 * Retrieves the channel key used the last time to download the
	 * opportunities from e4jConnect, and updates the class property.
	 * 
	 * @return 	mixed 	int for channel key or null
	 * 
	 * @since 	1.8.3
	 */
	protected function checkLastKeyDownloaded()
	{
		if (!empty($this->last_downloaded_key)) {
			return $this->last_downloaded_key;
		}

		$dbo = JFactory::getDbo();

		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='opportunities_last_key'";
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// no keys previously downloaded
			return null;
		}

		// set last key used and return it
		$this->last_downloaded_key = (int)$dbo->loadResult();

		return $this->last_downloaded_key;
	}

	/**
	 * Updates the last key used to retrieve the opportunities.
	 * Should be called when $this->unique_key has been set.
	 * 
	 * @return 	bool 	false on failure, true otherwise.
	 * 
	 * @since 	1.8.3
	 */
	protected function updateLastKeyDownloaded()
	{
		if (empty($this->unique_key)) {
			return false;
		}

		$dbo = JFactory::getDbo();

		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='opportunities_last_key';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('opportunities_last_key', " . $dbo->quote($this->unique_key) . ");";
			$dbo->setQuery($q);
			$dbo->execute();

			return true;
		}

		$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=" . $dbo->quote($this->unique_key) . " WHERE `param`='opportunities_last_key';";
		$dbo->setQuery($q);
		$dbo->execute();

		return true;
	}

	/**
	 * Builds the rooms mapping information for the current channel.
	 * 
	 * @return 	boolean 		true on success, false otherwise.
	 * 
	 * @since 	1.7.3
	 */
	protected function buildRoomsMapping()
	{
		if (count($this->rooms_mapping)) {
			// rooms already mapped
			return true;
		}

		if (empty($this->unique_key)) {
			// channel unique key must be set before executing this method
			return false;
		}

		$dbo = JFactory::getDbo();
		$q = "SELECT `r`.*, `vr`.`name` FROM `#__vikchannelmanager_roomsxref` AS `r` LEFT JOIN `#__vikbooking_rooms` AS `vr` ON `r`.`idroomvb`=`vr`.`id` WHERE `r`.`idchannel`=" . $dbo->quote($this->unique_key) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// no rooms mapped for this channel
			return false;
		}
		$records = $dbo->loadAssocList();

		// set mapping information for this channel
		foreach ($records as $v) {
			$this->rooms_mapping[$v['idroomota']] = $v;
		}

		return true;
	}

	/**
	 * Sets an error during the execution.
	 * 
	 * @param 	string 	$mess 	the error string.
	 * 
	 * @return 	void
	 */
	protected function setError($mess)
	{
		$this->error .= (string)$mess . "\n";
	}

	/**
	 * Returns whether errors occurred.
	 * 
	 * @return 	boolean
	 */
	public function hasError()
	{
		return !empty($this->error);
	}

	/**
	 * Returns the error message set.
	 * 
	 * @return 	string 	the error message string.
	 */
	public function getError()
	{
		return rtrim($this->error, "\n");
	}

}
