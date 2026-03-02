<?php
/** 
 * @package   	VikBooking
 * @subpackage 	com_vikbooking
 * @author    	Alessio Gaggii - e4j
 * @copyright 	Copyright (C) 2019 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');


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
	private $log = '';

	/**
	 * Determines the maximum length of the SMS messages (from 1 to 5).
	 *
	 * @var integer
	 */
	const MAX_SPLIT = 5;
	
	/**
	 * Defines the settings required for the integration.
	 *
	 * @return 	array 	The settings to fill.
	 */
	public static function getAdminParameters()
	{
		return array(
			'platform' => array(
				'label' 	=> 'Platform',
				'type'		=> 'select',
				'options' 	=> array(
					'Australia', 
					'United Kingdom',
				),
			),
			'username' => array(
				'label' => 'Username',
				'type' 	=> 'text',
			),
			'password' => array(
				'label' => 'Password',
				'type' 	=> 'text',
			),
			'sender' => array(
				'label' => 'Sender',
				'type' 	=> 'text',
			),
			'prefix' => array(
				'label' => 'Default Prefix',
				'type' 	=> 'text',
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
	 * using the SMS Broadcast specifics.
	 *
	 * @param 	string 	$phone_number 	The destination phone number.
	 * @param 	string 	$message 		The plain message to send.
	 *
	 * @return 	object  The response caught.
	 *
	 * @uses 	VikSmsApi::curl() 	Provides a cURL connection with smsbroadcast.co.uk.
	 */
	public function sendMessage($phone_number, $message)
	{
		if (empty($phone_number) || empty($message)) {
			return null;
		}
		
		$phone_number = trim(str_replace(" ", "", $phone_number));
		
		if (substr($phone_number, 0, 1) != '+') {
			if (substr($phone_number, 0, 2) == '00') {
				$phone_number = '+'.substr($phone_number, 2);
			} else {
				$phone_number = $this->params['prefix'].$phone_number;
			}
		}

		// remove leading + symbol
		$phone_number = str_replace('+', '', $phone_number);
		
		$data = array();
		$data['username'] 	= $this->params['username'];
		$data['password'] 	= $this->params['password'];
		$data['to'] 		= $phone_number;
		$data['from'] 		= $this->params['sender'];
		$data['message']	= $message;
		$data['maxsplit']	= self::MAX_SPLIT;

		return explode("\n", $this->curl($data));
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
		$ok = false;
		
		foreach ($response_obj as $data_line) {
			$message_data = explode(':', $data_line);

			if ($message_data[0] == "OK") {
				$ok = true;
			} else if ($message_data[0] == "BAD") {
				$this->log .= "The message to " . $message_data[1] . " was NOT sent successful. Reason: " . $message_data[2] . "\n";
			} else if ($message_data[0] == "ERROR") {
				$this->log .= "There was an error with this request. Reason: " . $message_data[1] . "\n";
			}
		}

		return $ok;
	}

	/**
	 * Used to lookup the number of credits left in your account.
	 *
	 * @param 	string 	$phone_number 	The destination phone number.
	 * @param 	string 	$message 		The plain message to send.
	 *
	 * @return 	object  The response caught.
	 *
	 * @uses 	VikSmsApi::curl() 	Provides a cURL connection with smsbroadcast.co.uk.
	 */
	public function estimate($phone_number, $message)
	{
		$obj = new stdClass;
		$obj->userCredit 	= 0;
		$obj->errorCode 	= 0;

		$data = array();
		$data['username'] 	= $this->params['username'];
		$data['password'] 	= $this->params['password'];
		$data['action']		= 'balance';

		$response = explode(':', $this->curl($data));

		if ($response[0] == 'OK') {
			$obj->userCredit = $response[1];
		} else if ($response[0] == 'ERROR') {
			$obj->errorCode = $response[1];
		}

		return $obj;
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
	 * Returns the end-point to use for the specified platform.
	 *
	 * @return 	string 	The base end-point.
	 *
	 * @since 	1.1 	Used to support different types of platform.
	 */
	protected function getEndPoint()
	{
		if (!strcasecmp($this->params['platform'], 'Australia'))
		{
			$domain = 'com.au';
		}
		else
		{
			$domain = 'co.uk';
		}

		return "https://api.smsbroadcast.{$domain}/api-adv.php";
	}

	/**
	 * Abstract CURL usage.
	 *
	 * @param 	array 	$data 	Array of parameters.
	 *
	 * @return object 	The result of the call.
	 *
	 * @since 1.0
	 */
	protected function curl($data = array())
	{
		$content = '';
		foreach ($data as $k => $v) {
			$content .= '&' . $k . '=' . rawurlencode($v);
		}
		$content = ltrim($content, '&');

		$ch = curl_init($this->getEndPoint());

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$output = curl_exec($ch);
		curl_close($ch);

		return $output;
	}
}
