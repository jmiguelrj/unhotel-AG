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
 * Factory Error class.
 *
 * @since 1.6.13
 */
final class VCMHttpError
{
	/**
	 * Class constructor.
	 * @private This object cannot be instantiated. 
	 */
	private function __construct()
	{
		// never called
	}

	/**
	 * Class cloner.
	 * @private This object cannot be cloned.
	 */
	private function __clone()
	{
		// never called
	}

	/**
	 * Raises an error by sending the header.
	 *
	 * @param 	integer 	$code 	The error code.
	 * @param 	string 		$error 	An optional error message.
	 *
	 * @return 	void
	 *
	 * @uses 	getErrorMessage()
	 */
	public static function raiseError($code, $error = null)
	{
		// get error message from code
		$text = static::getErrorMessage($code);

		// send the headers
		header($_SERVER['SERVER_PROTOCOL'] . ' ' . $text);

		// if custom error set, display it
		if ($error)
		{
			echo $error;
		}

		// break all
		exit;
	}

	/**
	 * Throws an exception by using the passed data.
	 *
	 * @param 	mixed 	$error 	The error code or the error message
	 * 							or the exception to throw.
	 *
	 * @return 	void
	 *
	 * @uses 	getErrorMessage()
	 *
	 * @throws 	Exception
	 */
	public static function throwException($error)
	{
		// if error is an integer
		if (is_integer($error))
		{
			// create an exception with the error related to the specified code
			$error = new Exception(static::getErrorMessage($error), $error);
		}
		// else if the error is not an exception 
		else if ($error instanceof Exception === false)
		{
			// create an exception with the specified string
			$error = new Exception((string) $error);
		}
		// otherwise it means that the error is already an exception to throw

		// throw the exception
		throw $error;
	}

	/**
	 * Get the error message related to the specified code.
	 *
	 * @param 	integer 	$code 	The error code.
	 *
	 * @return 	string 	The error message.
	 */
	public static function getErrorMessage($code)
	{
		$code = (string) $code;

		return $code . isset(static::$lookup[$code]) ? ' ' . $code . ' ' . static::$lookup[$code] : '';
	}

	/**
	 * The lookup array to retrieve a message starting from the error code.
	 *
	 * @var array
	 */
	private static $lookup = array(
		'100' => 'Continue',
		'101' => 'Switching Protocols',
		'102' => 'Processing',

		'200' => 'OK',
		'201' => 'Created',
		'202' => 'Accepted',
		'203' => 'Non-Authoritative Information',
		'204' => 'No Content',
		'205' => 'Reset Content',
		'206' => 'Partial Content',
		'207' => 'Multi-Status',
		'208' => 'Already Reported',
		'226' => 'IM Used',

		'300' => 'Multiple Choices',
		'301' => 'Moved Permanently',
		'302' => 'Found',
		'303' => 'See Other',
		'304' => 'Not Modified',
		'305' => 'Use Proxy',
		'306' => 'Switch Proxy',
		'307' => 'Temporary Redirect',
		'308' => 'Permanent Redirect',

		'400' => 'Bad Request',
		'401' => 'Unauthorized',
		'402' => 'Payment Required',
		'403' => 'Forbidden',
		'404' => 'Not Found',
		'405' => 'Method Not Allowed',
		'406' => 'Not Acceptable',
		'407' => 'Proxy Authentication Required',
		'408' => 'Request Timeout',
		'409' => 'Conflict',
		'410' => 'Gone',
		'411' => 'Length Required',
		'412' => 'Precondition Failed',
		'413' => 'Request Entity Too Large',
		'414' => 'Request-URI Too Long',
		'415' => 'Unsupported Media Type',
		'416' => 'Requested Range Not Satisfiable',
		'417' => 'Expectation Failed',
		'418' => 'I\'m a teapot',
		'420' => 'Enhance your calm',
		'421' => 'Misdirected Request',
		'422' => 'Unprocessable Entity',
		'423' => 'Locked',
		'424' => 'Failed Dependency',
		'426' => 'Upgrade Required',
		'428' => 'Precondition Required',
		'429' => 'Too Many Requests',
		'431' => 'Request Header Fields Too Large',
		'444' => 'Connection Closed Without Response',
		'449' => 'Retry With',
		'451' => 'Unavailable For Legal Reasons',
		'499' => 'Client Closed Request',

		'500' => 'Internal Server Error',
		'501' => 'Not Implemented',
		'502' => 'Bad Gateway',
		'503' => 'Service Unavailable',
		'504' => 'Gateway Timeout',
		'505' => 'HTTP Version Not Supported',
		'506' => 'Variant Also Negotiates',
		'507' => 'Insufficient Storage',
		'508' => 'Loop Detected',
		'509' => 'Bandwidth Limit Exceeded',
		'510' => 'Not Extended',
		'511' => 'Network Authentication Required',
		'599' => 'Network Connect Timeout Error',
	);
}
