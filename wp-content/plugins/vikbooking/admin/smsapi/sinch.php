<?php
/** 
 * @package   	VikBooking
 * @subpackage 	com_vikbooking
 * @author    	Alessio Gaggii - e4j
 * @copyright 	Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Integration between VikBooking and Sinch.com provider.
 *
 * @since 1.14.3
 */
class VikSmsApi
{
	/**
	 * Order info array (currently never used).
	 *
	 * @var array
	 */
	private $order_info;

	/**
	 * The settings of the integration.
	 *
	 * @var array
	 */
	private $params;

	/**
	 * The registered logs for debug purposes.
	 *
	 * @var string
	 */
	private $log = '1';

	/**
	 * List of accepted HTTP codes.
	 * Each code must be separated by a comma.
	 *
	 * @var 	string
	 * @since 	1.14.3
	 */
	const ACCEPTED_CODES = '200, 201, 202';

	/**
	 * Defines the settings required for the integration.
	 *
	 * The supported settings are:
	 *
	 * @property 	string 	apis 	API Token.
	 * @property 	string  from    The source phone number.
	 * @property 	string  prefix 	Defualt Prefix (+ included).
	 *
	 * @return 	array 	The settings to fill.
	 */
	public static function getAdminParameters()
	{
		return array(
			/**
			 * API secret token.
			 * It replaces the old username and password.
			 *
			 * @var 	string
			 * @since 	1.14.3
			 */
			'apis' => array(
				'label' 	=> 'API Token',
				'type' 		=> 'password',
				'required' 	=> 1,
			),
			
			/**
			 * Service Plan ID (Username).
			 *
			 * @var 	string
			 * @since 	1.14.3
			 */
			'service_plan_id' => array(
				'label' 	=> 'Service Plan ID',
				'type' 		=> 'text',
				'required' 	=> 1,
			),

			/**
			 * Endpoint location (eu, us, au, gb or ca).
			 *
			 * @var 	string
			 * @since 	1.14.3
			 */
			'geo_location' => array(
				'label' 	=> 'Endpoint location (eu, us, au, gb or ca)',
				'type' 		=> 'text',
				'required' 	=> 1,
			),


			/**
			 * From address.
			 *
			 * @var 	string
			 * @since 	1.14.3
			 */
			'from' => array(
				'label' 	=> 'From',
				'type' 		=> 'text',
				'required' 	=> 1,
			),

			/**
			 * The default phone prefix.
			 *
			 * @var string
			 */
			'prefix' => array(
				'label' 	=> 'Default Prefix',
				'type' 		=> 'text',
				'required' 	=> 1,
			),
		);
	}
	
	/**
	 * Class constructor.
	 *
	 * @param 	array 	$order 	 The order info array.
	 * @param 	array 	$params  The settings for the integration. 
	 */
	public function __construct($order, $params = array())
	{
		$this->order_info = $order;
		
		$this->params = !empty($params) ? $params : $this->params;
	}
	
	/**
	 * Send the provided message to a specific phone number
	 * using the sinch.com specifics.
	 *
	 * @param 	string 	$phone_number 	The destination phone number.
	 * @param 	string 	$message 		The plain message to send.
	 *
	 * @return 	object  The response caught.
	 *
	 * @uses 	VikSmsApi::curl() 	Provides a cURL connection with sinch.com.
	 */
	public function sendMessage($phone_number, $message)
	{
		if (empty($phone_number) || empty($message))
		{
			return null;
		}
		
		// sanitize phone number
		$phone_number = $this->sanitize($phone_number);
		
		$data = array();

		$data['to'] 				= array($phone_number);
		$data['from'] 				= $this->params['from'];
		$data['body'] 				= $message;
		
		return $this->curl('messages', $data);
	}

	/**
	 * Evaluates the response retrieved after sending a message.
	 *
	 * @param 	object 	$response_obj 	The response to check.
	 *
	 * @return 	boolean 	True if the message has been sent, otherwise false.
	 */
	public function validateResponse($response_obj)
	{
		if (!$response_obj || !isset($response_obj->httpCode))
		{
			return false;
		}

		// parse HTTP codes list
		$codes = array_map(function($code){
			return trim($code);
		}, explode(",", static::ACCEPTED_CODES));

		// check for non-OK statuses
		if (!in_array($response_obj->httpCode, $codes)
			|| (isset($response_obj->result->error) && !empty($response_obj->result->error)))
		{
			$this->log = '<pre>' . print_r($response_obj, true) . '</pre>';

			return false;
		}

		return true;
	}
	
	/**
	 * Returns the logs registered by this class.
	 *
	 * @return 	string 	The registered logs.
	 */
	public function getLog()
	{
		return $this->log;
	}

	/**
	 * Check if the specified message contains UTF-8 characters.
	 *
	 * @param 	string 	$message 	The string to check.
	 *
	 * @return 	boolean 	True if unicode, otherwise false.
	 *
	 * @since 	1.14.3
	 */
	protected function isUnicodeContent($message)
	{
		if (function_exists('iconv'))
		{
			$latin = @iconv('UTF-8', 'ISO-8859-1', $message);

			if (strcmp($latin, $message))
			{
				$arr = unpack('H*hex', @iconv('UTF-8', 'UCS-2BE', $message));
				//return strtoupper($arr['hex']);
				return true;
			}
		}

		return false;
	}

	/**
	 * Abstract CURL usage.
	 *
	 * @param 	string 	$uri 	The endpoint.
	 * @param 	array 	$data 	Array of parameters.
	 *
	 * @return object 	The result of the call.
	 *
	 * @since 1.14.3
	 */
	protected function curl($uri, $data = array())
	{
		$data = $data ? (array) $data : $data;

		$headers = array(
			'Authorization: Bearer ' . $this->params['apis'],
			'Content-Type: application/json',
		);
		
		$endpoint = "https://{$this->params['geo_location']}.sms.api.sinch.com/xms/v1/{$this->params['service_plan_id']}/batches";

		$curlInfo = curl_version();

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		if ($data)
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		}
		curl_setopt($ch, CURLOPT_URL, $endpoint);

		$result = curl_exec($ch);

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		$obj = new stdClass;
		$obj->httpCode 	= $httpCode;
		$obj->result 	= json_decode($result);

		return $obj;
	}

	/**
	 * Helper method used to sanitize phone numbers.
	 *
	 * @param 	string 	$phone 	The phone number to sanitize.
	 *
	 * @return 	string 	The cleansed number.
	 *
	 * @since 	1.14.3
	 */
	public function sanitize($phone)
	{
			$phone = preg_replace("/[^0-9\+]+/", '', $phone);
			$phone = preg_replace("/^(?:\+?44)?0([1-9]\d+)/", "44$1", $phone);
			$phone = preg_replace("/^(00|\+)([1-9]\d+)/", "$2", $phone);
		return $phone;
	}
	
}

