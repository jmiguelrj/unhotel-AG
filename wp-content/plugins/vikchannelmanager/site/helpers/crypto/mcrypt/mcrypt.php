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
 * Class used to cipher contents using mcrypt symmetric encryption.
 *
 * This class uses the php functions base64_encode/base64_decode 
 * to cypher correctly some sensible datas retrieved from the server.
 *
 * @since 1.3
 */
class Encryption implements CipherInterface
{
	/**
	 * Salt key used to encrypt/decrypt strings.
	 *
	 * @var string
	 */
	protected $salt = '';
	
	/**
	 * Class constructor.
	 *
	 * @param 	string 	$key  The salt key.
	 */
	public function __construct($key)
	{
		$this->salt = md5($key);
	}

	/**
	 * Encrypts the specified plain data.
	 *
	 * @param 	mixed 	$data 	The data to encrypt.
	 *
	 * @return  mixed 	The encrypted string, null on failure.
	 *
	 * @uses 	safeEncode()
	 */
	public function encrypt($data)
	{
		if (!$data || !function_exists('mcrypt_get_iv_size'))
		{
			return null;
		}

		$iv_size 	= mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv 		= mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$crypttext 	= mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->salt, $data, MCRYPT_MODE_ECB, $iv);

		return trim($this->safeEncode($crypttext));
	}

	/**
	 * Decrypts the specified encrypted string.
	 *
	 * @param 	string 	$data 	The string to decrypt.
	 *
	 * @return  mixed 	The decrypted string, null on failure.
	 *
	 * @uses 	safeDecode()
	 */
	public function decrypt($data)
	{
		if (!$data || !function_exists('mcrypt_get_iv_size'))
		{
			return null;
		}

		$crypttext 		= $this->safeDecode($data);
		$iv_size 		= mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv 			= mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$decrypttext 	= mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->salt, $crypttext, MCRYPT_MODE_ECB, $iv);

		return trim($decrypttext);
	}

	/**
	 * Encodes a string safely in base 64.
	 *
	 * @param 	string 	$string  The string to encode.
	 *
	 * @return 	string 	The encoded string.
	 */
	protected function safeEncode($string)
	{
		$data = base64_encode($string);
		$data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
		return $data;
	}

	/**
	 * Decodes a string safely from base 64.
	 *
	 * @param 	string 	$string  The string to decode.
	 *
	 * @return 	string 	The decoded string.
	 */
	protected function safeDecode($string)
	{
		$data = str_replace(array('-', '_'), array('+', '/'), $string);
		$mod4 = strlen($data) % 4;
		
		if ($mod4)
		{
			$data .= substr('====', $mod4);
		}

		return base64_decode($data);
	}
}
