<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

class VikSmsApi {
	
	private $order_info;
	private $params;
	private $log = '';
	
	private $BASE_URI = 'https://api.smshosting.it/rest/api';
	
	public static function getAdminParameters() {
		return array(
			'apikey' => array(
				'label' => 'API Key',
				'type' => 'text'
			),
			
			'apisecret' => array(
				'label' => 'API Secret',
				'type' => 'text'
			),
			
			'sender' => array(
				'label' => 'Sender Name//Max 11 characters',
				'type' => 'text'
			),
			
			'prefix' => array(
				'label' => 'Default Phone Prefix//This will be used only in case the prefix will be missing. It should be the prefix of your country of residence.',
				'type' => 'text'
			),
			
			'sandbox' => array(
				'label' => 'Sandbox',
				'type' => 'select',
				'options' => array('NO', 'YES')
			)
		);
	}
	
	public function __construct ($order, $params=array()) {
		$this->order_info=$order;
		
		$this->params = ( !empty($params) ) ? $params : $this->params;
		
		if( !empty($this->params['sandbox']) ) {
			if( $this->params['sandbox'] == 'NO' ) {
				$this->params['sandbox'] = false;
			} else {
				$this->params['sandbox'] = true;
			}
		}
	}
	
	///// SEND MESSAGE /////
	
	//$date = new DateTime ( "2014-02-08 11:14:15" );
	//$when = $date->format ( DateTime::ISO8601 );

	public function sendMessage( $phone_number, $msg_text, $when=NULL ) {
		if( empty($phone_number) || empty($msg_text) ) return;
		
		return $this->_send( '/sms/send', $phone_number, $msg_text, $when );
	}

	///// ESTIMATE CREDIT /////
	
	public function estimate( $phone_number, $msg_text ) {
		return $this->_send('/sms/estimate', $phone_number, $msg_text, NULL);
	}

	private function _send( $dir_uri, $phone_number, $msg_text, $when=NULL ) {
		$this->log = '';

		$unicode = $this->containsUnicode($msg_text);
		
		if( strlen($this->params['sender']) > 11 ) {
			$start = 0;
			if( substr($this->params['sender'], 0, strlen($this->params['prefix'])) == $this->params['prefix'] ) {
				$start = strlen($this->params['prefix']);
			}
			$this->params['sender'] = trim(substr($this->params['sender'], $start, 11));
		}
		
		$phone_number = trim(str_replace(" ", "", $phone_number));

		/**
		 *  @since 11th October 2019
		 *  enhanced checks on prefix: if the lenght of the number is greater than 10 characters, then I assume that the prefix is already included in the number. 
		*/
		if( substr($phone_number, 0, 1) != '+' ) {
			if( substr($phone_number, 0, 2) == '00' ) {
				$phone_number = '+'.substr($phone_number, 2);
			} else {
				if( strlen($phone_number) > 10 ) {
					$phone_number = '+' . $phone_number;
				} else {
					$phone_number = $this->params['prefix'].$phone_number;
				}
			}
		}
		
		/**
		 *  @since 3rd October 2018
		 *  double prefix prevention
		*/
		if (substr($phone_number, 0, 1) == '+' && substr($phone_number, 0, 3) == substr($phone_number, 3, 3)) {
			$phone_number = substr($phone_number, 3);
		} else if(substr($phone_number, 0, 1) == '+' && substr($phone_number, 0, 2) == substr($phone_number, 2, 2)) {
			$phone_number = substr($phone_number, 2);
		} else if(substr($phone_number, 0, 1) == '+' && substr($phone_number, 0, 4) == substr($phone_number, 4, 4)) {
			$phone_number = substr($phone_number, 4);
		}

		$post = array (
			'to' => urlencode($phone_number),
			'from' => urlencode($this->params['sender']),
			'group' => urlencode(NULL),
			'text' => urlencode($msg_text),
			'date' => urlencode($when),
			'transactionId' => urlencode(NULL),
			'sandbox' => urlencode( $this->params['sandbox'] ),
			'statusCallback' => urlencode(NULL),
			'type' => $unicode ? 'unicode' : 'text',
			/**
			 * For unicode chars, like with Greek, we now need to pass "encoding=AUTO"
			 * as updated on their official documentation at https://apirest.cloud/en/api.pdf.
			 * Available options: 7BIT (default if param is omitted), UCS2, AUTO.
			 * We do not use the variable $unicode, as AUTO seems the best parameter value.
			 * 
			 * @since 	January 18th 2021
			 */
			'encoding' => 'AUTO',
		);
		
		if( $this->params['sandbox'] ) {
			$this->log .= '<pre>'.print_r($this->params, true)."</pre>\n\n";
			$this->log .= '<pre>'.print_r($post, true)."</pre>\n\n";
		}

		$complete_uri = $this->BASE_URI.$dir_uri;
		
		$array_result = $this->sendPost( $complete_uri, $post );
		
		if( $array_result['from_smsh'] ) {
			return $this->parseResponse( $array_result );
		} else {
			return false;
		}
	} 
	
	private function sendPost($complete_uri, $data) {
		$data_to_send = array();
		$post = '';
		foreach ( $data as $k => $v ) {
			$data_to_send[$k] = $v;
			$post .= "&$k=$v";
		}

		$array_result = array();
		
		// If available, use CURL
		if (function_exists('curl_version')) {
			
			$to_smsh = curl_init($complete_uri);
			curl_setopt($to_smsh, CURLOPT_POST, true);
			curl_setopt($to_smsh, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($to_smsh, CURLOPT_USERPWD, $this->params['apikey'] . ":" . $this->params['apisecret']);

			// we should not use http_build_query because values were already encoded above.

			// curl_setopt($to_smsh, CURLOPT_POSTFIELDS, http_build_query($data_to_send));
			curl_setopt($to_smsh, CURLOPT_POSTFIELDS, ltrim($post, "&"));
			
			$array_result['from_smsh'] = curl_exec($to_smsh);
			
			$array_result['smsh_response_status'] = curl_getinfo($to_smsh, CURLINFO_HTTP_CODE);
			
			curl_close($to_smsh);
			
		} elseif(ini_get('allow_url_fopen')) {
			// No CURL available so try the awesome file_get_contents
			
			$opts = array (
				'http' => array (
					'method' => 'POST',
					'ignore_errors' => true,
					'header' => "Authorization: Basic ".base64_encode( $this->params['apikey'] . ":" . $this->params['apisecret'] ) . "\r\nContent-type: application/x-www-form-urlencoded",
					'content' => $post 
				) 
			);
			$context = stream_context_create( $opts );
			$array_result['from_smsh'] = file_get_contents( $complete_uri, false, $context );
			
			list( $version, $status_code, $msg ) = explode( ' ', $http_response_header[0], 3 );
			
			$array_result['smsh_response_status'] = $status_code;
			
		} else {
			// No way of sending a HTTP post
			$array_result['from_smsh'] = false; 
		}
		return $array_result;
	}

	private function parseResponse($arr) {
		
		$response = json_decode( $arr['from_smsh'] );
		
		$response_obj;
		
		if( is_array($response) ){
			$response_obj = new stdClass();
			$response_obj->response = $response; 
		} else {
			$response_obj = $response;	 
		}
		
		
		if( $arr['smsh_response_status'] == 200 ) {
			$response_obj->errorCode = 0;
		}
		
		$this->log .= '<pre>'.print_r($response_obj, true)."</pre>\n\n";
		
		if( $response_obj ) {
			return $response_obj;
		} 
		
		return false;
	}
	
	public function validateResponse($response_obj) {
		return ($response_obj === NULL || $response_obj->errorCode == 0);
	}
	
	///// UTILS /////
	
	public function getLog() {
		return $this->log;
	}
	
	private function containsUnicode($msg_text) {
		return max ( array_map ( 'ord', str_split ( $msg_text ) ) ) > 127;
	}
		
}


?>