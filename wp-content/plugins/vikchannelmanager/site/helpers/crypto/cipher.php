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
 * Basic interface to implement cipher classes.
 *
 * @since 1.6.8
 */
interface CipherInterface
{
	/**
	 * Encrypts the specified plain data.
	 *
	 * @param 	mixed 	$data 	The data to encrypt.
	 *
	 * @return  mixed 	The encrypted string, null on failure.
	 */
	public function encrypt($data);

	/**
	 * Decrypts the specified encrypted string.
	 *
	 * @param 	string 	$data 	The string to decrypt.
	 *
	 * @return  mixed 	The decrypted string, null on failure.
	 */
	public function decrypt($data);
}
