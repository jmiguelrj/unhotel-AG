<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Clicksend.com
 * @copyright   Copyright (C) 2021 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

class VikSmsApi {
	
	private $order_info;
	private $params;
	private $log = '';
	
	const BASE_URI = 'https://rest.clicksend.com/v3/sms/send';
	
	public static function getAdminParameters() {
		return array(
			'username' => array(
				'label' => 'Username',
				'type' => 'text'
			),
			
			'password' => array(
				'label' => 'API Key',
				'type' => 'text'
			),
			
			'sender' => array(
				'label' => 'Sender ID',
				'type' => 'text'
			),
		);
	}
	
	public function __construct($order, $params = array()) {
		$this->order_info = $order;
		
		$this->params = !empty($params) ? $params : $this->params;
	}

	public function sendMessage($phone_number, $msg_text) {
		if (empty($phone_number) || empty($msg_text)) {
			return false;
		}
		
		return $this->_send($phone_number, $msg_text);
	}

	private function _send($phone_number, $msg_text) {
		$this->log = '';
		
		// sanitize phone number, if necessary
		$phone_number = trim(str_replace(" ", "", $phone_number));

		/**
		 *  If the length of the number is greater than 10 characters, then we assume that the country prefix is already included in the number. 
		 */
		if (substr($phone_number, 0, 1) != '+') {
			if (substr($phone_number, 0, 2) == '00') {
				$phone_number = '+'.substr($phone_number, 2);
			} else {
				if (strlen($phone_number) > 10) {
					$phone_number = '+' . $phone_number;
				} else {
					$phone_number = $this->params['prefix'].$phone_number;
				}
			}
		}
		
		/**
		 * Double prefix prevention
		 */
		if (substr($phone_number, 0, 1) == '+' && substr($phone_number, 0, 3) == substr($phone_number, 3, 3)) {
			$phone_number = substr($phone_number, 3);
		} else if(substr($phone_number, 0, 1) == '+' && substr($phone_number, 0, 2) == substr($phone_number, 2, 2)) {
			$phone_number = substr($phone_number, 2);
		} else if(substr($phone_number, 0, 1) == '+' && substr($phone_number, 0, 4) == substr($phone_number, 4, 4)) {
			$phone_number = substr($phone_number, 4);
		}

		// compose message
		$message = new stdClass;
		$message->to = $phone_number;
		$message->body = $msg_text;
		$message->from = $this->params['sender'];
		$message->source = 'VikWP';
		// compose payload
		$payload = new stdClass;
		$payload->messages = array($message);
		
		return $this->sendPost($payload);
	} 

	private function sendPost($payload) {
		$array_result = array();
		
		// make the POST request through CURL
		$to_smsh = curl_init(self::BASE_URI);
		curl_setopt($to_smsh, CURLOPT_POST, true);
		curl_setopt($to_smsh, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($to_smsh, CURLOPT_POSTFIELDS, json_encode($payload)); 
		curl_setopt($to_smsh, CURLOPT_HTTPHEADER, array(
			'Content-Type:application/json',
			'Authorization: Basic '. base64_encode($this->params['username'] . ":" . $this->params['password'])
		));
		
		$array_result['response'] = curl_exec($to_smsh);
		$array_result['status'] = curl_getinfo($to_smsh);
		
		curl_close($to_smsh);

		// set log
		$this->log = $array_result;

		return $array_result;
	}

	public function validateResponse($arr) {
		if (empty($arr) || !is_array($arr) || empty($arr['response'])) {
			return false;
		}

		if (is_string($arr['response'])) {
			$arr['response'] = json_decode($arr['response']);
		}

		if (!is_object($arr['response'])) {
			return false;
		}

		if (isset($arr['response']->http_code) && $arr['response']->http_code == 200) {
			return true;
		}

		if (isset($arr['response']->response_code) && $arr['response']->response_code == 'SUCCESS') {
			return true;
		}
		
		return false;
	}

	public function getLog() {
		return $this->log;
	}
		
}
