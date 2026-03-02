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
 * Helper class used to convert strings.
 *
 * @since 	1.6.9
 */
final class VCMConverter
{
	/**
	 * Converts binary data into hexadecimal representation.
	 *
	 * @param 	string 	$str 	The binary string to convert.
	 *
	 * @return 	mixed 	An ASCII string containing the hexadecimal representation of $str.
	 */
	public static function bin2hex($str)
	{
		$hex = '';

		// convert char by char
		for ($i = 0; $i < strlen($str); $i++)
		{
			// get ASCII value from character
			$ord = ord($str[$i]);
			// convert decimal integer in hexadecimal
			$hexCode = dechex($ord);
			// obtain last 2 chars of hex (prepend 0 in case the value is lower than f)
			$hex .= substr('0' . $hexCode, -2);
		}

		// return uppercase string
		return strtoupper($hex);
	}

	/**
	 * Decodes a hexadecimally encoded binary string.
	 *
	 * @param 	string 	$data 	Hexadecimal representation of data.
	 *
	 * @return 	mixed 	The binary representation of the given data or FALSE on failure.
	 */
	public static function hex2bin($data)
	{
		$string = '';

		// iterate every 2 chars
		for ($i = 0; $i < strlen($data) - 1; $i += 2)
		{
			// get hex chunk
			$chunk = $data[$i] . $data[$i + 1];
			// convert hexadecimal chunk into decimal
			$dec = hexdec($chunk);
			// get character from ASCII value
			$string .= chr($dec);
		}

		return $string;
	}
}
