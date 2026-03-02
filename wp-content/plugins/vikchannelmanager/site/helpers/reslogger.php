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
 * Handler for storing logs about any reservations
 * with the main goal to keep track of all the
 * actions made for a specific day and room.
 *
 * @since 	1.6.8
 */
class VcmReservationsLogger
{	
	/**
	 * @var 	object 	db connection handler
	 */
	private $dbo;

	/**
	 * @var 	array 	log properties
	 */
	private $logTypes = array();

	/**
	 * @var 	array 	filters for loading logs
	 */
	private $filters = array();

	/**
	 * @var 	array 	clauses for loading logs
	 */
	private $clauses = array();

	/**
	 * Class constructor.
	 */
	public function __construct()
	{
		$this->dbo = JFactory::getDbo();
	}

	/**
	 * Magic method to handle various custom calls.
	 * Always return $this for chain-ability.
	 * 
	 * @param 	string 	$name
	 * @param 	array 	$arguments
	 *
	 * @return 	self
	 * 
	 * @uses 	setLogType()
	 * @uses 	setFilter()
	 **/
	public function __call($name, $arguments)
	{
		if (substr($name, 0, 4) == 'type') {
			// methods starting with 'type...' will be routed
			$type = strtolower(substr($name, 4));
			
			return $this->setLogType($type, $arguments[0]);
		}

		if (substr($name, 0, 6) == 'filter') {
			// methods starting with 'filter...' will be routed
			$filter = strtolower(substr($name, 6));

			// set the name of the filter as 1st argument
			array_unshift($arguments, $filter);

			return call_user_func_array(array($this, 'setFilter'), $arguments);
		}

		if (substr($name, 0, 6) == 'clause') {
			// methods starting with 'clause...' will be routed
			$clause = strtolower(substr($name, 6));

			// set the name of the clause as 1st argument
			array_unshift($arguments, $clause);

			return call_user_func_array(array($this, 'setClause'), $arguments);
		}

		if (substr($name, 0, 3) == 'get') {
			// methods starting with 'get...' will be routed
			$what = strtolower(substr($name, 3));

			// set the key of the filter as 1st argument
			array_unshift($arguments, $what);

			return call_user_func_array(array($this, 'getLoggerValue'), $arguments);
		}

		return $this;
	}

	/**
	 * Sets properties for the log, necessary to build
	 * the type (modification/cancellation) or the source.
	 * Called by the magic method __call().
	 * 
	 * Ex. $obj->typeFromChannels($val) will set the
	 * property 'fromchannels' in $this->logTypes to $val.
	 * 
	 * @param 	string 	$name 	The key of the log property
	 * @param 	string 	$value 	The value of the log property
	 *
	 * @return 	self
	 **/
	public function setLogType($name, $value)
	{
		$this->logTypes[$name] = $value;

		return $this;
	}

	/**
	 * Sets filters for loading the logs.
	 * Used to add conditions to the query.
	 * Called by the magic method __call().
	 * 
	 * Ex. $obj->filterLim(50) will set the
	 * query limit in $this->filters to 50.
	 * 
	 * @param 	string 	$name 			The log column name.
	 * @param 	string 	$value 			The value of the filter.
	 *
	 * @return 	self
	 **/
	public function setFilter($name, $value)
	{
		$this->filters[$name] = $value;

		return $this;
	}

	/**
	 * Sets clauses for loading the logs.
	 * Used to add conditions to the query.
	 * Called by the magic method __call().
	 * 
	 * Ex. $obj->filterDay('2018-05-23', '>=') will set the
	 * filter 'day' in $this->filters to '>= 2018-05-23'.
	 * 
	 * @param 	string 	$name 			The log column name.
	 * @param 	string 	$value 			The value to filter for.
	 * @param 	string 	[$condition] 	The condition operator (optional).
	 * @param 	mixed 	[$type] 		Whether a string or an array.
	 *
	 * @return 	self
	 **/
	public function setClause($name, $value, $condition = '=', $type = '')
	{
		if (is_scalar($type)) {
			$this->clauses[$name] = array($value, $condition);
		} else {
			// multi-dimensional values for the caluses (like `day` >= 'xxx' AND `day` <= 'yyy')
			if (!isset($this->clauses[$name]) || !is_array($this->clauses[$name])) {
				$this->clauses[$name] = array();
			}
			array_push($this->clauses[$name], array($value, $condition));
		}
		

		return $this;
	}

	/**
	 * Gets a specific logger value.
	 * Used to retrieve a filter or a log-type.
	 * Called by the magic method __call().
	 * 
	 * Ex. $obj->getDay('2018-05-23', false, 'clauses') will
	 * return the value for 'day' in $this->clauses.
	 * Ex. $obj->getDirection('DESC') will return the value
	 * for 'direction' in $this->filters, and will default to
	 * 'DESC' if the property is not set or is empty.
	 * 
	 * @param 	string 	$key 		The key of the logger value.
	 * @param 	mixed 	[$def] 		The default value to return.
	 * @param 	string 	[$context] 	Where to look for the value.
	 *
	 * @return 	mixed 	The content of the requested value.
	 **/
	public function getLoggerValue($key, $def = false, $context = 'filters')
	{
		if (!property_exists($this, $context) || !isset($this->{$context}[$key])) {
			return $def;
		}

		if (is_array($this->{$context}[$key]) && count($this->{$context}[$key]) > 1 && empty($this->{$context}[$key][1])) {
			// no need to return an array with the value and condition in case of clauses.
			return $this->{$context}[$key][0];
		}

		// return the plain value requested.
		return $this->{$context}[$key];
	}

	/**
	 * Tracks the update by storing one log for each date/room combination.
	 * If $rooms_data is empty, it means that the method was not called by
	 * the 'SynchVikBooking' Class, but rather by the Class 'NewBookingsVikBooking'
	 * during a BR_L event, maybe because the reservation could not be stored.
	 * In this case, we call another method to parse the e4jConnect booking array.
	 * 
	 * @param 	array 	$booking 		The current (updated) reservation array
	 *									or the raw reservations array from e4jConnect.
	 * @param 	array 	[$rooms_data] 	The list of rooms involved in the update.
	 * 									If empty, it means that the reservations could
	 *									not be stored by VCM.
	 *
	 * @return 	int 	the number of records stored, or false in case of failure.
	 **/
	public function trackLog($booking, $rooms_data = array())
	{
		// determine source (default to website)
		$idchannel = 0;
		$source = 'W';
		if (isset($this->logTypes['fromchannels']) && is_array($this->logTypes['fromchannels']) && count($this->logTypes['fromchannels'])) {
			// if this log property 'fromchannels' is not empty, it contains the uniquekey of the channel
			$idchannel = (int)$this->logTypes['fromchannels'][0];
			$source = 'O';
		}

		if (!count($rooms_data)) {
			/**
			 * The reservation could not be saved into VBO by 'NewBookingsVikBooking',
			 * so $booking is the plain reservation array transmitted by e4jConnect.
			 * Parse it to obtain a similar $booking and $rooms_data array to the ones
			 * composed by 'SynchVikBooking::sendRequest' that calls this same method trackLog().
			 */
			list($booking, $rooms_data) = $this->getRawBookingData($booking, $idchannel);
		}

		if (!$booking || !$rooms_data) {
			return false;
		}

		// determine the action type (default to new booking)
		$action_type = 'NB' . $source;
		if (isset($this->logTypes['modification']) && $this->logTypes['modification']) {
			// modified booking
			$action_type = 'MB' . $source;
		} elseif (isset($this->logTypes['cancellation']) && $this->logTypes['cancellation']) {
			// cancelled booking
			$action_type = 'CB' . $source;
		}

		// avoid loading the language file for VCM. If not running, it means we are on VBO
		$units_lbl = JText::_('VCMOSUNITSONDATE');
		if ($units_lbl == 'VCMOSUNITSONDATE') {
			$units_lbl = JText::_('VBPVIEWROOMSEVEN');
			if ($units_lbl == 'VBPVIEWROOMSEVEN') {
				$units_lbl = 'Units';
			}
		}

		// build the log rows
		$logrows = array();
		foreach ($rooms_data as $involved) {
			foreach ($involved['adates'] as $kd => $vd) {
				$logrecord = new stdClass;
				$logrecord->idorder = $booking['id'];
				$logrecord->idorderota = null;
				// seek the idorderota
				if (!empty($idchannel) && isset($booking['idorderota']) && !empty($booking['idorderota'])) {
					$logrecord->idorderota = $booking['idorderota'];
				}
				//
				$logrecord->idchannel = $idchannel;
				$logrecord->idroomvb = $involved['idroom'];
				$logrecord->idroomota = null;
				// seek the idroomota
				if (!empty($idchannel) && isset($involved['channels']) && count($involved['channels'])) {
					foreach ($involved['channels'] as $invch) {
						if ((int)$invch['idchannel'] == (int)$idchannel && !empty($invch['idroomota'])) {
							$logrecord->idroomota = $invch['idroomota'];
							break;
						}
					}
				}
				//
				$logrecord->dt = JFactory::getDate()->toSql(true);
				$logrecord->day = $kd;
				$logrecord->type = $action_type;
				$logrecord->descr = null;
				// build the description
				if (isset($vd['newavail'])) {
					$logrecord->descr = $units_lbl . ' ' . $vd['newavail'];
				} elseif (isset($booking['description']) && !empty($booking['description'])) {
					/**
					 * When VCM could not save the reservation, set a brief summary of the
					 * reservation (check-in/out dates, customer name etc..) for the log.
					 * Composed by getRawBookingData();
					 */
					$logrecord->descr = $booking['description'];
				}

				// push the log record to the rows
				array_push($logrows, $logrecord);
			}
		}

		$num_stored = 0;

		foreach ($logrows as $record) {
			// insert the log row in a try-catch to avoid exceptions to be thrown
			try {
				$this->dbo->insertObject('#__vikchannelmanager_reslogs', $record, 'id');
				$num_stored++;
			} catch (Exception $e) {
				return false;
			}

			if ($record->id <= 0) {
				return false;
			}
		}

		return $num_stored;
	}

	/**
	 * Composes two arrays similar to SynchVikBooking::sendRequest
	 * by parsing the raw reservation array transmitted by e4jConnect,
	 * that could not be stored by VCM. The contents of the arrays
	 * returned is not the same as what SynchVikBooking::sendRequest
	 * returns, because the booking ID may not exist and some details
	 * are ignored to just contain the necessary details for trackLog().
	 * 
	 * @param 	array 	$raw 		the plain array transmitted by e4jConnect
	 * @param 	int 	$idchannel 	the channel uniquekey
	 * 
	 * @return 	array 	 		2-count array with $booking and $rooms_data
	 *
	 * @uses 	NewBookingsVikBooking::otaBookingExists()
	 * @uses 	mapIdroomVbFromOtaId()
	 */
	private function getRawBookingData($raw, $idchannel)
	{
		// import the NewBookingsVikBooking Class to use its static methods
		if (!class_exists('NewBookingsVikBooking')) {
			require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'newbookings.vikbooking.php');
		}

		$booking = array(
			'id' => null,
			'idorderota' => $raw['info']['idorderota']
		);
		$rooms_data = array();

		$need_cancelled = false;
		$customer_data = null;
		switch ($raw['info']['ordertype']) {
			case 'Download':
				$need_cancelled = true;
				$customer_data = '';
				foreach ($raw['customerinfo'] as $what => $cinfo) {
					$customer_data .= ucwords($what).": ".$cinfo."\n";
				}
				$customer_data = rtrim($customer_data, "\n");
				break;
		}

		if ($vborderinfo = NewBookingsVikBooking::otaBookingExists($raw['info']['idorderota'], true, $need_cancelled, $customer_data)) {
			/**
			 * this reservation ID exists in VBO, so we take some values needed by trackLog()
			 */
			$booking['id'] = $vborderinfo['id'];
		}

		// attempt to find the rooms booked
		$idroomvb = $this->mapIdroomVbFromOtaId($raw['roominfo'], $idchannel);
		if ($idroomvb !== false && !is_array($idroomvb) && intval($idroomvb) > 0) {
			$idroomvb = array((int)$idroomvb);
		}

		if ($idroomvb !== false) {
			// booked dates
			$adates = array();
			$checkin_info = getdate(strtotime($raw['info']['checkin']));
			$checkout_ts = strtotime($raw['info']['checkout']);
			if ($checkin_info[0] > $checkout_ts) {
				return array(false, false);
			}
			while ($checkin_info[0] < $checkout_ts) {
				$day = date('Y-m-d', $checkin_info[0]);
				// we do not set any value for the array corresponding to the key Y-m-d
				$adates[$day] = array();
				// go to next day, and if check-out day, while loop will break
				$checkin_info = getdate(mktime(0, 0, 0, $checkin_info['mon'], ($checkin_info['mday'] + 1), $checkin_info['year']));
			}

			// compose a brief description of the untrasnmitted booking
			$descr = '';
			if (array_key_exists('customerinfo', $raw)) {
				foreach ($raw['customerinfo'] as $what => $cinfo) {
					$descr .= ucwords($what) . ": " . $cinfo . "\n";
				}
				if (array_key_exists('email', $raw['customerinfo'])) {
					$descr .= $raw['customerinfo']['email'] . "\n";
				}
			}
			$descr .= $raw['info']['checkin'] . ' ' . $raw['info']['checkout'] . "\n";
			if (strlen($raw['info']['adults']) > 0) {
				$descr .= 'Adults: ' . $raw['info']['adults'];
				if (strlen($raw['info']['children']) > 0) {
					$descr .= ', Children: ' . $raw['info']['children'];
				}
				$descr .= "\n";
			}
			if (strlen($raw['info']['total']) > 0) {
				$descr .= (isset($raw['info']['currency']) && !empty($raw['info']['currency']) ? $raw['info']['currency'] . ' ' : '') . (float)$raw['info']['total'] . "\n";
			}
			$booking['description'] = rtrim($descr, "\n");

			/**
			 * we do not populate the 'channels' key in $rooms_data
			 * with the information of the idroomota for each room booked
			 * because mapIdroomVbFromOtaId() only returns the IDs of VBO.
			 * Therefore, the logs search function should count more on the 
			 * IDs of the room in VBO rather than the IDs of the rooms for the OTAs.
			 */

			// compose rooms_data array with basic info
			foreach ($idroomvb as $room_id) {
				array_push($rooms_data, array(
					'idroom' => $room_id,
					'adates' => $adates
				));
			}
		}

		return count($booking) && count($rooms_data) ? array($booking, $rooms_data) : array(false, false);
	}

	/**
	 * Finds the corresponding room ids in VBO from the OTA room ids.
	 * Method similar to NewBookingsVikBooking::mapIdroomVbFromOtaId(),
	 * which cannot be converted to a static method for use in this class.
	 * 
	 * @param 	array 	$roomsinfo 		raw rooms information array
	 * @param 	int 	$idchannel 		the channel uniquekey
	 * 
	 * @return 	mixed 	string 			idroomvb or array idroomvb
	 *
	 * @see 	NewBookingsVikBooking::mapIdroomVbFromOtaId()
	 */
	private function mapIdroomVbFromOtaId($roomsinfo, $idchannel)
	{
		if (array_key_exists(0, $roomsinfo)) {
			if (count($roomsinfo) > 1) {
				//multiple rooms
				$idroomota = array();
				foreach ($roomsinfo as $rk => $ordr) {
					$idroomota[] = $ordr['idroomota'];
				}
			} else {
				//single room
				$idroomota = $roomsinfo[0]['idroomota'];
			}
		} else {
			//single room
			$idroomota = $roomsinfo['idroomota'];
		}

		if (!is_array($idroomota) && intval($idroomota) < 0) {
			$pos_id = (int)abs((float)$idroomota);
			$q = "SELECT `id`,`name`,`units` FROM `#__vikbooking_rooms` WHERE `id`=".$pos_id.";";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
			if ($this->dbo->getNumRows() > 0) {
				$assocs = $this->dbo->loadAssocList();
				return $assocs[0]['id'];
			}
		}
		if (!is_array($idroomota)) {
			$q = "SELECT `x`.`idroomvb`,`vbr`.`name`,`vbr`.`units` FROM `#__vikchannelmanager_roomsxref` AS `x` " .
				"LEFT JOIN `#__vikbooking_rooms` `vbr` ON `x`.`idroomvb`=`vbr`.`id` " .
				"WHERE `x`.`idroomota`=".$this->dbo->quote($idroomota)." AND `x`.`idchannel`='".$idchannel."' " .
				"ORDER BY `x`.`id` ASC;";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
			if ($this->dbo->getNumRows() > 0) {
				$assocs = $this->dbo->loadAssocList();
				return $assocs[0]['idroomvb'];
			}
		} else {
			if (!(count($idroomota) > 0)) {
				return false;
			}
			$roomsota_count_map = array();
			$in_clause = array();
			foreach ($idroomota as $k => $v) {
				$in_clause[$k] = $this->dbo->quote($v);
				$roomsota_count_map[$v] = empty($roomsota_count_map[$v]) ? 1 : ($roomsota_count_map[$v] + 1);
			}
			$q = "SELECT DISTINCT `x`.`idroomvb`,`x`.`idroomota`,`vbr`.`name`,`vbr`.`units` FROM `#__vikchannelmanager_roomsxref` AS `x` " .
				"LEFT JOIN `#__vikbooking_rooms` `vbr` ON `x`.`idroomvb`=`vbr`.`id` " .
				"WHERE `x`.`idroomota` IN (".implode(', ', array_unique($in_clause)).") AND `x`.`idchannel`='".$idchannel."' " .
				"ORDER BY `x`.`id` ASC LIMIT ".count($in_clause).";";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
			if ($this->dbo->getNumRows() > 0) {
				$idroomvb = array();
				$assocs = $this->dbo->loadAssocList();
				$idroomota = array_unique($idroomota);
				foreach ($idroomota as $k => $v) {
					foreach ($assocs as $rass) {
						if ($rass['idroomota'] != $v) {
							continue;
						}
						$idroomvb[] = $rass['idroomvb'];
						if ($roomsota_count_map[$rass['idroomota']] > 1) {
							for ($i = 1; $i < $roomsota_count_map[$rass['idroomota']]; $i++) {
								$idroomvb[] = $rass['idroomvb'];
							}
						}
					}
				}
				//
				return count($idroomvb) > 0 ? $idroomvb : false;
			}
		}

		return false;
	}

	/**
	 * Returns an array of types mapped to
	 * the corresponding language definition.
	 * All the log types should be listed here.
	 *
	 * @return 	array
	 */
	public function getTypesMap()
	{
		return array(
			// New booking from website
			'NBW' => JText::_('VCMLOGTYPENBW'),
			// Modified booking from website
			'MBW' => JText::_('VCMLOGTYPEMBW'),
			// Cancelled booking from website
			'CBW' => JText::_('VCMLOGTYPECBW'),
			// New booking from OTA
			'NBO' => JText::_('VCMLOGTYPENBO'),
			// Modified booking from OTA
			'MBO' => JText::_('VCMLOGTYPEMBO'),
			// Cancelled booking from OTA
			'CBO' => JText::_('VCMLOGTYPECBO')
		);
	}

	/**
	 * Removes all the expired logs from the database.
	 * All the records with a 'day' in the past will
	 * be erased. We keep the logs for 2 extra months.
	 *
	 * @return 	self
	 */
	public function removeExpired()
	{
		$limit = date('Y-m-d', strtotime('-2 months'));

		try {
			$q = "DELETE FROM `#__vikchannelmanager_reslogs` WHERE `day` < ".$this->dbo->quote($limit).";";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
		} catch (Exception $e) {
			// do nothing
		}

		return $this;
	}

	/**
	 * Loads all the log records with various criterias.
	 * Filtering values should be passed before calling this method.
	 * Returns a count-2 array with the rows as 0th and tot rows as 1st.
	 *
	 * @return 	array 	[0] => associative list of reservations
	 * 					[1] => total number of rows for the pagination
	 */
	public function load()
	{
		$logs = array(array(), 0);

		$lim0 = isset($this->filters['lim0']) ? (int)$this->filters['lim0'] : VikRequest::getVar('limitstart', 0, '', 'int');
		$lim = isset($this->filters['lim']) ? (int)$this->filters['lim'] : 50;
		$ordering = isset($this->filters['ordering']) ? $this->filters['ordering'] : 'dt';
		$direction = isset($this->filters['direction']) ? $this->filters['direction'] : 'DESC';

		$wheres = array();
		foreach ($this->clauses as $field => $vals) {
			if ($field == 'custom') {
				// custom clauses are not parsed
				array_push($wheres, $vals[0]);
				continue;
			}
			if (is_scalar($vals[0])) {
				// single-dimension clause
				array_push($wheres, '`'.$field.'` ' . $vals[1] . ' ' . ($vals[1] != 'IN' ? $this->dbo->quote($vals[0]) : $vals[0]));
			} else {
				// multi-dimension clause
				foreach ($vals as $subvals) {
					array_push($wheres, '`'.$field.'` ' . $subvals[1] . ' ' . ($subvals[1] != 'IN' ? $this->dbo->quote($subvals[0]) : $subvals[0]));
				}
			}
		}

		$q = "SELECT SQL_CALC_FOUND_ROWS * FROM `#__vikchannelmanager_reslogs`".(count($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '')." ORDER BY `#__vikchannelmanager_reslogs`.`".$ordering."` ".$direction;
		try {
			$this->dbo->setQuery($q, $lim0, $lim);
			$this->dbo->execute();
			if ($this->dbo->getNumRows() > 0) {
				$logs[0] = $this->dbo->loadAssocList();
				$this->dbo->setQuery('SELECT FOUND_ROWS();');
				$logs[1] = $this->dbo->loadResult();
			}
		} catch (Exception $e) {
			VikError::raiseWarning('', 'The following query returned an error:<br/>'.$q);
		}

		return $logs;
	}

}
