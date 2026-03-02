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
 * Class used to decrypt messages encrypted with the
 * public key provided by this class.
 *
 * @since 1.0
 */
class CipherOpenSSLClient extends CipherOpenSSL
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
		$dbo = JFactory::getDbo();

		$params = array('public_key', 'private_key');
		$params = array_map(array($dbo, 'q'), $params);

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikchannelmanager_config'))
			->where($dbo->qn('param') . ' IN (' . implode(', ', $params) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows() != 2)
		{
			return false;
		}

		foreach ($dbo->loadObjectList() as $row)
		{
			switch ($row->param)
			{
				case 'public_key':
					$this->publicKey = $row->setting;
					break;

				case 'private_key':
					$this->privateKey = $row->setting;
					break;
			}
		}

		return true;
	}

	/**
	 * @override
	 * Method used to register the pair of keys related
	 * to the details specified in the $options array.
	 *
	 * @param 	array 	$options 	A configuration array.
	 *
	 * @return 	self 	This object to support chaining.
	 *
	 * @throws 	RuntimeException 	In case of storing error.
	 */
	protected function store(array $options = array())
	{
		$dbo = JFactory::getDbo();

		$public = new stdClass;
		$public->param 	 = 'public_key';
		$public->setting = $this->publicKey;

		$dbo->insertObject('#__vikchannelmanager_config', $public, 'id');

		$private = new stdClass;
		$private->param   = 'private_key';
		$private->setting = $this->privateKey;

		$dbo->insertObject('#__vikchannelmanager_config', $private, 'id');

		if ($public->id <= 0 || $private->id <= 0)
		{
			throw new RuntimeException('It was not possible to store OpenSSL keys.', 500);
		}

		return $this;
	}

	/**
	 * @override
	 * Method used to drop the registered keys.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function drop()
	{
		$dbo = JFactory::getDbo();

		$params = array('public_key', 'private_key');
		$params = array_map(array($dbo, 'q'), $params);

		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikchannelmanager_config'))
			->where($dbo->qn('param') . ' IN (' . implode(', ', $params) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		return $this;
	}
}
