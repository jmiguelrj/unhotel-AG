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

// require the Converter class for bin-hex conversion
require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'crypto' . DIRECTORY_SEPARATOR . 'converter.php';

/**
 * Class used to cipher contents using OpenSSL asymmetric encryption.
 *
 * OpenSSL needs the generation of 2 related keys (one public and the other private).
 * The PUBLIC key can be used by any customer to encrypt messages.
 * That message will be decrypted only thanks to the PRIVATE key.
 *
 * USAGE:
 * Alice needs to send a crypted message to Bob. Alice asks to Bob
 * to share with her his PUBLIC key. Alice encrypts the message for
 * Bob using the PUBLIC key and send it to Bob. Bob receives the
 * message and decrypts it using its own PRIVATE key.
 * Only Bob was able to decrypt the message because the PRIVATE key
 * must not be shared with other users.
 *
 * What do they need to know for encryption and decryption?
 * Alice: 	Alice Public Key, Alice Private Key, Bob Public Key
 * Bob: 	Bob Public Key, Bob Private Key, Alice Public Key
 *
 * @since 1.6.8
 */
abstract class CipherOpenSSL implements CipherInterface
{
	/**
	 * A list of instances.
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * The OpenSSL private key.
	 *
	 * @var string
	 */
	protected $privateKey;

	/**
	 * The OpenSSL public key.
	 *
	 * @var string
	 */
	protected $publicKey;

	/**
	 * The OpenSSL configuration array.
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * Flag used to check if the keys has been generated
	 * during the runtime.
	 *
	 * @var boolean
	 */
	protected $isNew = false;

	/**
	 * The OpenSSL errors array.
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Returns an instance of the chiper, only creating it
	 * if it doesn't already exist.
	 *
	 * @param 	array 	$options 	A configuration array.
	 *
	 * @return 	self 	A new instance of this object.
	 *
	 * @throws 	RuntimeException 	In case the specified handler doesn't exist.
	 */
	public static function getInstance(array $options = array())
	{
		$sign = serialize($options);

		if (!isset(static::$instances[$sign]))
		{
			if (isset($options['handler']))
			{
				// instantiate the handler directly
				$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'handlers' . DIRECTORY_SEPARATOR . $options['handler'] . '.php';

				if (!is_file($file))
				{
					// file not found
					throw new RuntimeException('The specified OpenSSL handler [' . $options['handler'] . '] does not exist', 404);
				}

				// require handler file
				require_once $file;

				$classname = 'CipherOpenSSL' . ucwords($options['handler']);

				if (!class_exists($classname))
				{
					// classname not found
					throw new RuntimeException('The specified OpenSSL class [' . $classname . '] does not exist', 404);
				}

				// instantiate
				$cipher = new $classname($options);

				if (!$cipher instanceof CipherOpenSSL)
				{
					// not a valid instance
					throw new RuntimeException('The specified OpenSSL handler [' . $classname . '] is not a valid instance', 500);
				}

				static::$instances[$sign] = $cipher;
			}
			else
			{
				// otherwise use default declaration that will be used
				// by the classes that inherits this abstract class
				static::$instances[$sign] = new static($options);
			}
		}

		return static::$instances[$sign];
	}

	/**
	 * Class constructor.
	 *
	 * @param 	array 	$options 	A configuration array.
	 * 								It should also contain a list of data
	 * 								to retrieve the OpenSSL details of a specific user.
	 *
	 * 								The array supports these functions:
	 * 								- config 	the OpenSSL config array.
	 */
	protected function __construct(array $options = array())
	{
		if (isset($options['config']))
		{
			$this->config = (array) $options['config'];
		}
		else
		{
			// use default configuration data
			$this->config = array(
				'digest_alg' 		=> 'sha512',
				'private_key_bits' 	=> 512,
				'private_key_type' 	=> OPENSSL_KEYTYPE_RSA,
			);
		}

		// load keys related to the given options
		if (!$this->load($options))
		{
			// Impossible to load the keys, probably the options data
			// didn't assign to any user/entity.
			// Generate a new pair of keys and store them.
			$this->generateKeys()->store($options);

			// mark the keys as new
			$this->isNew = true;
		}
	}

	/**
	 * Encrypts the specified data using OpenSSL Asymmetric encryption.
	 * 
	 * @param 	mixed 	 $data 	The string to encrypt. If a non scalar value
	 * 							is provided, it will be serialized.
	 * @param 	boolean  $safe  True to encode the encrypted message in base64.
	 * 							False to return the plain encrypted string.
	 *
	 * @return 	string 	The encrypted message.
	 *
	 * @uses 	_encrypt()
	 */
	public function encrypt($data, $safe = true)
	{
		if (!is_scalar($data))
		{
			$data = serialize($data);
		}

		// make sure the data to encrypt doesn't exceed 
		// the maximum number of bytes
		if (strlen($data) > static::MAX_DATA_LENGTH)
		{
			// split the data in chunks
			$chunks = str_split($data, static::MAX_DATA_LENGTH);

			$encryption = array();

			// iterate the chunks and encrypt them
			foreach ($chunks as $chunk)
			{
				$encryption[] = $this->_encrypt($chunk, $safe);
			}

			// implode the encrypted chunks with a separator
			$encryption = implode(static::CHUNKS_SEPARATOR, $encryption);
		}
		else
		{
			$encryption = $this->_encrypt($data, $safe);
		}

		return VCMConverter::bin2hex($encryption);
	}

	/**
	 * Encrypts the specified data using OpenSSL Asymmetric encryption.
	 * Implements the algorhitm used to do the encryption.
	 * 
	 * @param 	string 	 $data 	The string to encrypt.
	 * @param 	boolean  $safe  True to encode the encrypted message in base64.
	 * 							False to return the plain encrypted string.
	 *
	 * @return 	string 	The encrypted message.
	 */
	protected function _encrypt($data, $safe)
	{
		// encrypt the data to $encrypted using the public key
		openssl_public_encrypt($data, $encrypted, $this->publicKey);

		if ($safe)
		{
			$encrypted = base64_encode($encrypted);
		}

		return $encrypted;
	}

	/**
	 * Decrypts the encrypted string using OpenSSL Asymmetric decryption.
	 * 
	 * @param 	string 	 $encrypted  The encrypted string.
	 * @param 	boolean  $safe  	 True if the message to decrypt was encoded in base64.
	 * 								 False if it is a plain encrypted string.
	 *
	 * @return 	string 	The decrypted string.
	 *
	 * @uses 	_decrypt()
	 */
	public function decrypt($encrypted, $safe = true)
	{
		if (preg_match("/^[0-9abcdef]+$/i", $encrypted))
		{
			// string is in hexadecimal format
			$encrypted = VCMConverter::hex2bin($encrypted);
		}

		// explode the encrypted string
		$chunks = explode(static::CHUNKS_SEPARATOR, $encrypted);

		$decrypted = '';

		// iterate all the chunks and decrypt them separately
		foreach ($chunks as $chunk)
		{
			$decrypted .= $this->_decrypt($chunk, $safe);
		}

		return $decrypted;
	}

	/**
	 * Decrypts the encrypted string using OpenSSL Asymmetric decryption.
	 * Implements the algorhitm used to do the decryption.
	 * 
	 * @param 	string 	 $encrypted  The encrypted string.
	 * @param 	boolean  $safe  	 True if the message to decrypt was encoded in base64.
	 * 								 False if it is a plain encrypted string.
	 *
	 * @return 	string 	The decrypted string.
	 */
	protected function _decrypt($encrypted, $safe = true)
	{
		if ($safe)
		{
			$encrypted = base64_decode($encrypted);
		}

		// decrypt the data using the private key and store the results in $decrypted
		openssl_private_decrypt($encrypted, $decrypted, $this->privateKey);

		return $decrypted;
	}

	/**
	 * Generates a new pair of keys to use for OpenSSL Asymmetric
	 * encryption/decryption.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	protected function generateKeys()
	{
		// create the private and public key
		$res = openssl_pkey_new($this->config);

		if (!$res)
		{
			// get the OpenSSL errors list
			while ($msg = openssl_error_string()) {
				array_push($this->errors, $msg);
			}

			return $this;
		}

		// extract the private key from $res to $privKey
		@openssl_pkey_export($res, $privKey);

		$this->privateKey = $privKey;

		// extract the public key from $res to $pubKey
		$pubKey = @openssl_pkey_get_details($res);
		$pubKey = $pubKey['key'];

		$this->publicKey = $pubKey;

		return $this;
	}

	/**
	 * Returns the OpenSSL public key.
	 *
	 * @return 	string 	The public key.
	 */
	public function getPublicKey()
	{
		return $this->publicKey;
	}

	/**
	 * Returns true if the pair of keys have been 
	 * generated during this runtim.
	 *
	 * @return 	boolean  True if there are new keys, otherwise false.
	 */
	public function hasNewKeys()
	{
		return $this->isNew;
	}

	/**
	 * Returns the array of errors.
	 *
	 * @return 	array
	 */
	public function getErrors()
	{
		$errors = $this->errors;
		$this->errors = array();
		
		return $errors;
	}

	/**
	 * Abstract method used to load the pair of keys related
	 * to the details specified in the $options array.
	 * In case the keys cannot be retrieved (maybe because
	 * they haven't been created yet), false will be returned.
	 *
	 * @param 	array 	 $options 	A configuration array.
	 *
	 * @return 	boolean  True on success, otherwise false.
	 */
	abstract protected function load(array $options = array());

	/**
	 * Abstract method used to register the pair of keys related
	 * to the details specified in the $options array.
	 *
	 * @param 	array 	$options 	A configuration array.
	 *
	 * @return 	self 	This object to support chaining.
	 *
	 * @throws 	RuntimeException 	In case it wasn't possible to register the keys.
	 */
	protected function store(array $options = array())
	{
		// inherit this class if you need to store the keys

		return $this;
	}

	/**
	 * Abstract method used to drop the registered keys.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function drop()
	{
		// inherit this class if you need to drop the keys

		return $this;
	}

	/**
	 * The maximum number of bytes that can be encrypted.
	 *
	 * @var integer
	 */
	const MAX_DATA_LENGTH = 50;

	/**
	 * The separator used to explode/implode the chunks.
	 *
	 * @var string
	 */
	const CHUNKS_SEPARATOR = "||";
}
