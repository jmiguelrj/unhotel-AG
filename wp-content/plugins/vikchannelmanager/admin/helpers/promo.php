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
 * Parent Class to manage all the promotions handlers.
 */
abstract class VikChannelManagerPromo
{
	protected $handler_name = '';
	protected $handler_key = '';
	protected $handler_logo = '';
	protected $response = null;
	protected $error = '';
	protected $dbo;

	/**
	 * Class constructor should define the name, key and logo.
	 */
	public function __construct()
	{
		$this->dbo = JFactory::getDbo();
	}

	/**
	 * Extending Classes should define this method.
	 */
	abstract public function isActive();

	/**
	 * Extending Classes should define this method.
	 */
	abstract public function getName();

	/**
	 * Extending Classes should define this method.
	 */
	abstract public function getKey();

	/**
	 * Extending Classes should define this method.
	 */
	abstract public function getLogoUri();

	/**
	 * Extending Classes should define this method.
	 * 
	 * @param 	array 	$params 	List of promotion parameters.
	 */
	abstract protected function prepareSavePromotion($params);

	/**
	 * Extending Classes should define this method.
	 * 
	 * @param 	?array 	$data 	 The promotion details or null.
	 * @param 	string 	$method  Either "new", "update" or "delete".
	 */
	abstract public function createPromotion($data, $method);

	/**
	 * Extending classes could override this method to tell whether
	 * they require to be invoked upon updating a VBO promotion.
	 * 
	 * @return 	bool 	true if trigger is needed, or false.
	 * 
	 * @since 	1.8.4
	 */
	public function triggerUpdate()
	{
		return false;
	}

	/**
	 * Extending classes could override this method to tell whether
	 * they require to be invoked upon deleting a VBO promotion.
	 * 
	 * @return 	bool 	true if trigger is needed, or false.
	 * 
	 * @since 	1.8.4
	 */
	public function triggerDelete()
	{
		return false;
	}

	/**
	 * Extending classes could use this method to store information about
	 * the promotions created, updated or deleted. It uses the internal db
	 * table of VCM for the OTA promotions. Note that the visibility must
	 * be public for those channels that have a dedicated interface or 
	 * controller that performs requests to like activate/deactivate promos.
	 * 
	 * @param 	string 	$ota_promo_id 	the ID of the OTA promotion.
	 * @param 	string 	$method 		the name of the method, usually "new", "update", 
	 * 									"edit", "delete", "activate" or "deactivate".
	 * @param 	array 	$data 			associative array of promotion data.
	 * 
	 * @return 	int 					false on failure or record ID.
	 * 
	 * @since 	1.8.4
	 */
	public function channelPromotionCompleted($ota_promo_id, $method, $data = array())
	{
		// current SQL date
		$date = JFactory::getDate();
		$date->setTimezone(new DateTimeZone(date_default_timezone_get()));
		$now_date = $date->toSql($local = true);

		// build promotion object record
		$promo_record = new stdClass;
		if ($method != 'new') {
			// we update the record
			if (!empty($data['vcm_promo_id'])) {
				// exact record ID given
				$promo_record->id = (int)$data['vcm_promo_id'];
			} else {
				// find VCM promotion record ID (null if not found)
				$promo_record->id = $this->findPromotionID($ota_promo_id);
			}
		}
		$promo_record->vbo_promo_id = !empty($data['vbo_promo_id']) ? (int)$data['vbo_promo_id'] : 0;
		if (isset($promo_record->id) && empty($promo_record->vbo_promo_id)) {
			// when updating a record, if we don't have the VBO promo ID, we keep the previous value
			unset($promo_record->vbo_promo_id);
		}
		$promo_record->ota_promo_id = (string)$ota_promo_id;
		$promo_record->channel = $this->getKey();
		$promo_record->method = (string)$method;
		$promo_record->data = !is_scalar($data) && !is_null($data) ? json_encode($data) : $data;
		$promo_record->dt = $now_date;

		try {
			if (isset($promo_record->id)) {
				// update record
				$this->dbo->updateObject('#__vikchannelmanager_otapromotions', $promo_record, 'id');
			} else {
				// create record
				$this->dbo->insertObject('#__vikchannelmanager_otapromotions', $promo_record, 'id');
			}
		} catch (Exception $e) {
			// set error and return false
			$this->setError($e->getMessage());
			return false;
		}

		return isset($promo_record->id) ? $promo_record->id : false;
	}

	/**
	 * Checks if a precise promotion ID for the current channel exists.
	 * 
	 * @param 	mixed 	$ota_promo_id 	the ID of the OTA promotion, or array for the VBO promotion ID.
	 * 
	 * @return 	mixed 					null on failure or record ID integer.
	 * 
	 * @since 	1.8.4
	 */
	protected function findPromotionID($ota_promo_id)
	{
		if (empty($ota_promo_id)) {
			return null;
		}

		$promo_id_type = 'ota_promo_id';
		if (is_array($ota_promo_id)) {
			if (isset($ota_promo_id['vbo_promo_id'])) {
				$promo_id_type = 'vbo_promo_id';
				$ota_promo_id = $ota_promo_id['vbo_promo_id'];
			} else {
				// grab the first key
				foreach ($ota_promo_id as $promo_id) {
					$ota_promo_id = $promo_id;
					break;
				}
			}
		}

		// check again that the promotion ID is not empty
		if (empty($ota_promo_id)) {
			return null;
		}

		$q = "SELECT `id` FROM `#__vikchannelmanager_otapromotions` WHERE `{$promo_id_type}`=" . $this->dbo->quote($ota_promo_id) . " AND `channel`=" . $this->dbo->quote($this->getKey()) . " ORDER BY `id` ASC";
		$this->dbo->setQuery($q, 0, 1);
		$this->dbo->execute();
		if (!$this->dbo->getNumRows()) {
			return null;
		}

		return $this->dbo->loadResult();
	}

	/**
	 * Returns a list of OTA promotion records for the current channel depending
	 * on how many hotel accounts were used to create or update a promotion.
	 * 
	 * @param 	int 	$vo_promo_id 	the VBO promotion ID.
	 * 
	 * @return 	array 					list of records or empty array.
	 * 
	 * @since 	1.8.6
	 */
	protected function findPreviousOTAData($vbo_promo_id)
	{
		if (empty($vbo_promo_id) || !is_scalar($vbo_promo_id)) {
			return [];
		}

		$this->dbo->setQuery(
			$this->dbo->getQuery(true)
				->select('*')
				->from($this->dbo->qn('#__vikchannelmanager_otapromotions'))
				->where($this->dbo->qn('vbo_promo_id') . ' = ' . $this->dbo->q($vbo_promo_id))
				->where($this->dbo->qn('channel') . ' = ' . $this->dbo->q($this->getKey()))
				->where($this->dbo->qn('method') . ' != ' . $this->dbo->q('delete'))
				->group($this->dbo->qn('method'))
				->group($this->dbo->qn('data'))
				->order($this->dbo->qn('id') . ' DESC')
		);

		return $this->dbo->loadAssocList();
	}

	/**
	 * Injects request parameters for the report like if some filters were set.
	 * 
	 * @param 	array 	$params 	associative list of request vars to inject.
	 *
	 * @return 	self
	 */
	public function injectParams($params)
	{
		if (is_array($params) && count($params)) {
			foreach ($params as $key => $value) {
				/**
				 * For more safety across different platforms and versions (J3/J4 or WP)
				 * we inject values in the super global array as well as in the input object.
				 */
				VikRequest::setVar($key, $value, 'request');
				VikRequest::setVar($key, $value);
			}
		}

		return $this;
	}

	/**
	 * Sets the execution response.
	 * Method used only by sub-classes.
	 *
	 * @param 	mixed 	$response 	the result of the operation.
	 *
	 * @return 	self
	 */
	protected function setResponse($response)
	{
		$this->response = $response;

		return $this;
	}

	/**
	 * Gets the execution response.
	 *
	 * @return 	mixed 	the response previously set.
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * Sets errors by concatenating the existing ones.
	 * Method used only by sub-classes.
	 *
	 * @param 	string 		$str
	 *
	 * @return 	self
	 */
	protected function setError($str)
	{
		$this->error .= $str."\n";

		return $this;
	}

	/**
	 * Gets the current error string.
	 *
	 * @return 	string
	 */
	public function getError()
	{
		return rtrim($this->error, "\n");
	}

	/**
	 * Gets the base path where all handlers are located.
	 * 
	 * @return 	string 	the path to the base dir of the handlers.
	 */
	protected static function getHandlersBasePath()
	{
		return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'promo';
	}

	/**
	 * Loads all the active promotion handlers available or the one requested.
	 * 
	 * @param 	string 	$key 	the key of the handler to invoke.
	 * 
	 * @return 	mixed 	array list of handlers available, object found or false.
	 */
	public static function loadHandlers($key = null)
	{
		$handlers_base_path = self::getHandlersBasePath();
		if (!is_dir($handlers_base_path)) {
			// this directory may not have been created by installing the update
			return array();
		}
		
		// pool
		$handlers = array();

		// read all extending class files
		$path_name = $handlers_base_path . DIRECTORY_SEPARATOR;
		$pool = glob($path_name . '*.php');

		foreach ($pool as $promo_handler) {
			// load class file
			require_once $promo_handler;

			// handler key = basename PHP file with no extension
			$handler_key = basename($promo_handler, '.php');

			// the name of the extending class
			$classname = 'VikChannelManagerPromo' . ucfirst(preg_replace("/[^a-zA-Z0-9]+/", '', $handler_key));

			// get object instance
			$handler_instance = new $classname;

			// make sure this channel is active
			if (!$handler_instance->isActive()) {
				// skip this channel because it is not active or not configured
				continue;
			}

			// create handler information object
			$handler_obj = new stdClass;
			$handler_obj->key = $handler_instance->getKey();
			$handler_obj->name = $handler_instance->getName();
			$handler_obj->logo = $handler_instance->getLogoUri();
			$handler_obj->instance = $handler_instance;

			if ($key && $key == $handler_instance->getKey()) {
				return $handler_instance;
			}

			array_push($handlers, $handler_obj);
		}

		if (!empty($key)) {
			// requested handler not found
			return false;
		}

		return $handlers;
	}

	/**
	 * Gets the factors for suggesting the application of the promotions.
	 * 
	 * @param 	mixed 	$data 	optional instructions argument.
	 * 
	 * @return 	mixed 	the factors array for applying the promotions and related
	 * 					discounts, or the requested corresponding value from $data.
	 */
	public static function getFactors($data = null)
	{
		$factors = array(
			'compare' 	=> array(
				// greater than, equal to
				'occupancy' => 'gtet',
				// less than, equal to
				'in_days' 	=> 'ltet'
			),
			'occupancy' => array(
				// >= 80% = high
				'high'  => 80,
				// >= 50% = medium
				'med'   => 50,
				// >= 0% = low
				'low'   => 0,
			),
			'in_days' 	=> array(
				// <= 10d = low (near dates)
				'low'   => 10,
				// <= 60d = medium (medium far dates)
				'med'   => 60,
				// <= 365d = high (far dates)
				'high'  => 365,
			),
			'discount' 	=> array(
				'in_days_low'  => array(
					'occupancy_high' => 10,
					'occupancy_med'  => 20,
					'occupancy_low'  => 30,
				),
				'in_days_med'  => array(
					'occupancy_high' => 10,
					'occupancy_med'  => 10,
					'occupancy_low'  => 20,
				),
				'in_days_high' => array(
					'occupancy_high' => 0,
					'occupancy_med'  => 10,
					'occupancy_low'  => 10,
				),
			),
		);

		return $factors;
	}

	/**
	 * Static method to check if and when the last OTA promotion was created.
	 * 
	 * @return 	mixed 	string with last creation date or null.
	 * 
	 * @since 	1.8.4
	 */
	public static function getLastOTAPromotionDate()
	{
		$dbo = JFactory::getDbo();

		$q = "SELECT `dt` FROM `#__vikchannelmanager_otapromotions` ORDER BY `id` DESC";
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			return null;
		}

		return $dbo->loadResult();
	}

	/**
	 * Returns a list of OTAs on which the given promotion was created.
	 * 
	 * @param 	int 	$vo_promo_id 	the VBO promotion ID.
	 * 
	 * @return 	array 					list of records or empty array.
	 * 
	 * @since 	1.8.11
	 */
	public static function getPromoChannelsInvolved($vbo_promo_id)
	{
		if (empty($vbo_promo_id) || !is_scalar($vbo_promo_id)) {
			return [];
		}

		$dbo = JFactory::getDbo();

		$q = "SELECT * FROM `#__vikchannelmanager_otapromotions` WHERE `vbo_promo_id`=" . $dbo->quote($vbo_promo_id) . " AND `method`!='delete' GROUP BY `channel` ORDER BY `dt` ASC;";
		$dbo->setQuery($q);

		return $dbo->loadAssocList();
	}

	/**
	 * Returns a list of promotions that were created on the OTAs.
	 * 
	 * @param 	string 	$col 	the unique column to fetch (i.e. `vbo_promo_id`).
	 * 
	 * @return 	array 	list of records/special price IDs or empty array.
	 * 
	 * @since 	1.8.19
	 */
	public static function getPromosOnChannels($col = '')
	{
		static $promos_on_channels = [];

		$signature = __METHOD__ . $col;

		if (isset($promos_on_channels[$signature])) {
			return $promos_on_channels[$signature];
		}

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select(($col ? $dbo->qn($col) : '*'))
			->from($dbo->qn('#__vikchannelmanager_otapromotions'))
			->where($dbo->qn('method') . ' != ' . $dbo->q('delete'))
			->order($dbo->qn('dt') . ' ASC');

		$dbo->setQuery($q);

		$promos_on_channels[$signature] = $col ? array_unique($dbo->loadColumn()) : $dbo->loadAssocList();

		return $promos_on_channels[$signature];
	}
}
