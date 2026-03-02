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
 * This class implements the mediator pattern to coordinate
 * the usage of the supported cipher methods.
 *
 * USAGE:
 * $mediator = CryptoMediator::getInstance();
 * $mediator->addCipher(new Encryption('API_KEY'));
 * $mediator->addCipher(CipherOpenSSL::getInstance($options));
 *
 * $data = $mediator->decrypt($encrypted);
 *
 * @since 1.6.8
 */
class CryptoMediator
{
	/**
	 * Static instance used to make this class a Singleton.
	 *
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * A list of cipher objects that will be used to decrypt data.
	 *
	 * @var CipherInterface[]
	 */
	protected $decryptCiphers = array();

	/**
	 * A list of cipher objects that will be used to encrypt data.
	 *
	 * @var CipherInterface[]
	 */
	protected $encryptCiphers = array();

	/**
	 * Returns a new instance of this object, only
	 * creating it if it doesn't exist yet.
	 *
	 * @return 	self 	This object.
	 */
	public static function getInstance()
	{
		if (static::$instance === null)
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Class constructor.
	 */
	protected function __construct()
	{
		// this class cannot be instantiated externally
	}

	/**
	 * Class cloner.
	 */
	protected function __clone()
	{
		// this class cannot be cloned externally
	}

	/**
	 * Adds a new cipher to the crypto mediator.
	 *
	 * @param 	mixed 	$cipher  The cipher object to push.
	 * @param 	string 	$type  	 The type of the cipher:
	 * 							 - "encrypt" if it will be used to encrypt data;
	 * 							 - "decrypt" if it will be used to decrypt data.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function addCipher(CipherInterface $cipher, $type = 'decrypt')
	{
		$pool = $type . 'Ciphers';

		if (!in_array($cipher, $this->{$pool}))
		{
			$this->{$pool}[] = $cipher;
		}

		return $this;
	}

	/**
	 * Removes an existing cipher from the crypto mediator.
	 *
	 * @param 	mixed 	$cipher  The cipher object to remove.
	 * @param 	string 	$type  	 The type of the cipher:
	 * 							 - "encrypt" if it was used to encrypt data;
	 * 							 - "decrypt" if it was used to decrypt data.
	 *
	 * @return 	mixed 	The old cipher object on success, otherwise null.
	 */
	public function removeCipher(CipherInterface $cipher, $type = 'decrypt')
	{
		$pool = $type . 'Ciphers';

		$index = array_search($cipher, $this->{$pool});

		if ($index)
		{
			return array_splice($this->{$pool}, $ciphers, $index, 1);
		}

		return null;
	}

	/**
	 * Searches for a cipher instance used by the mediator.
	 * The cipher must be an instance of the specified classname.
	 *
	 * @param 	mixed 	$class   The classname to search.
	 * @param 	string 	$type  	 The type of the cipher:
	 * 							 - "encrypt" if it is used to encrypt data;
	 * 							 - "decrypt" if it is used to decrypt data.
	 *
	 * @return 	mixed 	The cipher on success, otherwise null.
	 */
	public function getInstanceOf($class, $type = 'decrypt')
	{
		$pool = $type . 'Ciphers';

		foreach ($this->{$pool} as $cipher)
		{
			if ($cipher instanceof $class)
			{
				return $cipher;
			}
		}

		return null;
	}

	/**
	 * Decrypts the given data using the correct method.
	 *
	 * @param 	string 	$data 	The data to decrypt.
	 *
	 * @return 	mixed 	The decrypted data, null on failure.
	 */
	public function decrypt($data)
	{
		$i = 0;

		do
		{
			$cipher = $this->decryptCiphers[$i++];

			try
			{
				$result = $cipher->decrypt($data);
			}
			catch (Exception $e)
			{
				$result = null;
			}

		} while (!$result && $i < count($this->decryptCiphers));

		return $result;
	}
}
