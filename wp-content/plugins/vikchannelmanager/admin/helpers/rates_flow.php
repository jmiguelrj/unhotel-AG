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
 * This helper class is used to manage the rates flow.
 * Any rates modification is part of the rates flow.
 * 
 * @since 	1.8.3
 */
class VCMRatesFlow
{
	/**
	 * The singleton instance of the class.
	 *
	 * @var 	VCMRatesFlow
	 */
	protected static $instance = null;

	/**
	 * The list of rates flow records.
	 * 
	 * @var 	array
	 */
	protected $records = array();

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
		// load VCMRatesFlowRecord class
		require_once VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'rates_flow_record.php';
	}

	/**
	 * Returns the singleton object instance, unless a new instance is requested.
	 * 
	 * @param 	bool 	$anew 	True to re-build the object.
	 *
	 * @return 	VCMRatesFlow 	A new instance of the class.
	 */
	public static function getInstance($anew = false)
	{
		if (is_null(static::$instance) || $anew) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Gets a new or an existing Rates Flow Record.
	 * 
	 * @param 	mixed 	$data 	array/object to get an existing record.
	 * 
	 * @return 	VCMRatesFlowRecord
	 */
	public function getRecord($data = [])
	{
		return new VCMRatesFlowRecord($data);
	}

	/**
	 * Push a new Rates Flow Record to the list.
	 * 
	 * @param 	VCMRatesFlowRecord 	$record  the object to push.
	 * 
	 * @return 	self
	 */
	public function pushRecord(VCMRatesFlowRecord $record)
	{
		if ($record instanceof VCMRatesFlowRecord) {
			array_push($this->records, $record);
		}

		return $this;
	}

	/**
	 * Tells whether rates flow records should be always stored, no matter what.
	 * 
	 * @return 	bool 	True if storing the records is forced, or false.
	 * 
	 * @since 	1.9.16
	 */
	public function forcedRecordsStoring()
	{
		static $forced = null;

		if ($forced === null) {
			// read preference from configuration settings
			$forced = VCMFactory::getConfig()->getBool('rates_flow_force_storing', false);
		}

		return (bool) $forced;
	}

	/**
	 * Stores the list of rates flow records pushed on the db.
	 * It can be used to create and/or update records.
	 * 
	 * @return 	bool 	true on success, or false.
	 */
	public function storeRecords()
	{
		if (!$this->records) {
			$this->setError('No rates flow records pushed');
			return false;
		}

		$dbo = JFactory::getDbo();

		$tot_stored = 0;

		foreach ($this->records as $k => $record) {
			if (!$record instanceof VCMRatesFlowRecord) {
				$this->setError('Invalid object record at index ' . $k);
				continue;
			}

			$record_vars = $record->getProperties();
			if (!is_array($record_vars) || !$record_vars) {
				$this->setError('Empty properties for record at index ' . $k);
				continue;
			}

			// make sure the record has got at least dates defined
			$dates = $record->getDates();
			if (empty($dates[0]) || empty($dates[1])) {
				$this->setError('Empty dates for record at index ' . $k);
				continue;
			}

			/**
			 * Trigger event to allow third-party plugins to determine whether the record should be stored.
			 * 
			 * @since 	1.9.4
			 * @since 	1.9.16 even if third-party plugins for dynamic pricing disable the storing, it is possible to force it.
			 */
			$check_storing = VCMFactory::getPlatform()->getDispatcher()->filter('onCheckRatesFlowStoreRecord', [$record_vars]);
			if (!$this->forcedRecordsStoring() && in_array(false, $check_storing, true)) {
				// skip record
				continue;
			}

			// attempt to store the record
			$res = false;
			try {
				// cast the record to object
				$record_vars = (object) $record_vars;
				if ($record->isNewRecord()) {
					// create a new record on the db
					$res = $dbo->insertObject('#__vikchannelmanager_rates_flow', $record_vars, 'id');
				} else {
					// update an existing record on the db
					$res = $dbo->updateObject('#__vikchannelmanager_rates_flow', $record_vars, 'id');
				}
			} catch (Exception $e) {
				// set the db error
				$this->setError('Could not store record at index ' . $k);
				$this->setError($e->getMessage());
			}

			if ($res) {
				$tot_stored++;
			}
		}

		// all the valid records have been stored, so we need to empty the list for other's usage
		$this->records = [];

		return ($tot_stored > 0);
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
