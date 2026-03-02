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
 * Class used to encrypt messages for e4jConnect end-point.
 *
 * @since 1.6.8
 */
class CipherOpenSSLE4jConnect extends CipherOpenSSL
{
	/**
	 * @override
	 * Method used to load the pair of keys related
	 * to the details specified in the $options array.
	 * In case the keys cannot be retrieved (maybe because
	 * they haven't been created yet), false will be returned.
	 *
	 * @param 	array 	 $options 	A configuration array.
	 *
	 * @return 	boolean  True on success, otherwise false.
	 */
	protected function load(array $options = array())
	{
		$this->publicKey  = "-----BEGIN PUBLIC KEY-----
MFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBANuhxa2w3M3eW1j3I4WpnAB33hT1pu5R
PhlZFhjg9XWYIuCvJz2ZQNvEdTXKclhvJ2Mt9F/9zPo5il6q93abJ9MCAwEAAQ==
-----END PUBLIC KEY-----
";
		$this->privateKey = null;

		return true;
	}
}
