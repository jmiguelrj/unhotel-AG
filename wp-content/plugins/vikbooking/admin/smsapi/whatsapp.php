<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - E4J srl
 * @copyright   Copyright (C) 2022 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Provides support for a Meta Developer App producting Whatsapp.
 * 
 * @link 	https://developers.facebook.com/docs/whatsapp/cloud-api/get-started
 * @link 	https://developers.facebook.com/docs/whatsapp/business-management-api/get-started
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
	private $log = '';

	/**
	 * Defines the settings required for the integration.
	 * 
	 * @return 	array 	The settings to fill.
	 */
	public static function getAdminParameters()
	{
		// start by loading the necessary assets
		VikBookingIcons::loadRemoteAssets();

		return [
			/**
			 * Setup instructions.
			 */
			'cron_lbl' => [
				'type'  => 'custom',
				'label' => '',
				'html'  => '<h4><i class="' . VikBookingIcons::i('fab fa-whatsapp-square') . '"></i> Whatsapp Messaging - Installation requirements</h4>' . 
						   '<p>In order to be able to send Whatsapp messages, it is necessary to set up a Meta developer account and a Meta developer app. Please complete the following steps:</p>' . 
						   '<ol>' . 
						   '<li>Register as a Meta Developer</li>' . 
						   '<li>Create a new Meta App</li>' . 
						   '<li>Add the Whatsapp product to your App</li>' . 
						   '<li>Configure the Whatsapp product and get your access token and phone number id.</li>' . 
						   '</ol>' . 
						   '<p>Your Meta App will have to be published by Facebook (Meta) in order to go live on production, but this integration also works for a test App for development.</p>' . 
						   '<p>We <strong>strongly</strong> recommend following the official Whatsapp Cloud Messaging API documentation at <a href="https://developers.facebook.com/docs/whatsapp/cloud-api/get-started" target="_blank">https://developers.facebook.com/docs/whatsapp/cloud-api/get-started</a> for more instructions on this activation process.</p>',
			],

			/**
			 * Meta App - Whatsapp Access Token (Temporary or Permanent).
			 *
			 * @var 	string
			 */
			'access_token' => [
				'label' => 'Access Token',
				'help' 	=> 'Use either the Temporary Access Token or <a href="https://developers.facebook.com/docs/whatsapp/business-management-api/get-started" target="_blank">learn how to get a Permanent Token</a>. This information should be visible in your <i>Meta Developer Account</i>, under your <i>App</i>, in the <i>Whatsapp</i> product, under the section <i>Getting Started</i> or <i>Configuration</i>.',
				'type' 	=> 'text',
			],

			/**
			 * Phone number ID, required to build the endpoint path parameter.
			 *
			 * @var 	string
			 */
			'phone_number_id' => [
				'label' => 'Phone number ID',
				'help' 	=> 'Required field to send messages. Get it from your <i>Meta Developer Account</i>, select your <i>App</i>, click the <i>Whatsapp</i> product, click on <i>Getting Started</i>, and then look for your <i>Phone number ID</i>.',
				'type' 	=> 'text',
			],
		];
	}
	
	/**
	 * Class constructor.
	 *
	 * @param 	array 	$order 	 The order info array.
	 * @param 	array 	$params  The settings for the integration. 
	 */
	public function __construct($order, $params = [])
	{
		$this->order_info = $order;
		$this->params 	  = (array) $params;
	}
	
	/**
	 * Sends the provided message to a specific Whatsapp phone number
	 * by using the Facebook (Meta) Graph API.
	 *
	 * @param 	string 	$phone_number 	The destination phone number.
	 * @param 	string 	$message 		The plain message to send.
	 *
	 * @return 	mixed 	Response object or false in case of failure.
	 */
	public function sendMessage($phone_number, $message)
	{
		if (empty($phone_number) || empty($message)) {
			$this->log = 'Missing arguments';

			return null;
		}
		
		// sanitize phone number
		$phone_number = $this->sanitize($phone_number);

		// build the endpoint URI
		$endpoint = "https://graph.facebook.com/v13.0/{$this->params['phone_number_id']}/messages";

		// init HTTP transporter
		$http = new JHttp();

		// build post data
		$data = [
			'messaging_product' => 'whatsapp',
			'recipient_type' 	=> 'individual',
			'to' 				=> $phone_number,
			'type' 				=> 'text',
			'text' 				=> [
				'preview_url' 	=> false,
				'body' 			=> $message,
			],
		];

		// build request headers
		$headers = [
			'Authorization' => 'Bearer ' . $this->params['access_token'],
			'Content-Type' => 'application/json',
		];

		// execute the request
		$response = $http->post($endpoint, json_encode($data), $headers);

		// attempt to decode the response
		$response_obj = json_decode($response->body);

		if ($response->code != 200) {
			// log the request error
			$this->log = sprintf("Error (%s)\n<pre>%s</pre>", $response->code, (is_object($response_obj) ? json_encode($response_obj, JSON_PRETTY_PRINT) : $response->body));

			return false;
		}

		// return the decoded response
		return $response_obj;
	}

	/**
	 * Evaluates the response retrieved after sending a message.
	 *
	 * @param 	mixed 		$response_obj 	The response to check.
	 *
	 * @return 	boolean 	True if the message has been sent, otherwise false.
	 */
	public function validateResponse($response_obj)
	{
		return is_object($response_obj) && empty($response_obj->error);
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
	 * Helper method used to sanitize phone numbers.
	 *
	 * @param 	string 	$phone 	The phone number to sanitize.
	 *
	 * @return 	string 	The clean number.
	 */
	protected function sanitize($phone)
	{
		if (substr($phone, 0, 2) == '00') {
			// no 00 for country prefix
			$phone = substr($phone, 2);
		}

		// no spaces, nor "+" for country prefix
		return preg_replace("/[^0-9]/", '', $phone);
	}
}
