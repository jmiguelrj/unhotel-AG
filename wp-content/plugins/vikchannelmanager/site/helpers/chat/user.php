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
 * Class handler for the chat users.
 * 
 * @since 1.6.13
 */
class VCMChatUser
{
	/**
	 * The order ID used by VikBooking.
	 *
	 * @var integer
	 */
	protected $idOrder;

	/**
	 * The client code (1: admin, 0: site).
	 *
	 * @var integer
	 */
	protected $client;

	/**
	 * The booking details.
	 *
	 * @var object
	 */
	protected $order = null;

	/**
	 * Returns a new instance of the client that is using the
	 * chat related to the specified order ID.
	 *
	 * @param 	integer  $id_order  The order ID.
	 * @param 	mixed    $client 	The client code.
	 *
	 * @return 	self 	 A new instance.
	 */
	public static function getInstance($id_order, $client = null)
	{
		if (is_null($client))
		{
			// fetch client
			$app = JFactory::getApplication();
			if (method_exists($app, 'isClient'))
			{
				$client = $app->isClient('administrator') ? 1 : 0;
			}
			else
			{
				$client = $app->isAdmin() ? 1 : 0;
			}
		}

		// instantiate new object
		return new static((int) $id_order, (int) $client);
	}

	/**
	 * Class constructor.
	 *
	 * @param 	integer  $id_order  The order ID.
	 * @param 	integer  $client 	The client code.
	 */
	protected function __construct($id_order, $client)
	{
		$this->idOrder = $id_order;
		$this->client  = $client;
	}

	/**
	 * Returns the user client.
	 *
	 * @return 	integer  The user client.
	 */
	public function getClient()
	{
		return $this->client;
	}

	/**
	 * Registers the ping made from this client by updating its
	 * last login date and time.
	 *
	 * @return 	self 	This object to support chaining.
	 *
	 * @uses 	getRecord()
	 */
	public function ping()
	{
		$dbo = JFactory::getDbo();

		// get current datetime
		$ping = JDate::getInstance()->toSql();

		// Search for a record that matches our query.
		// Retrieve only ID in order to minimize UPDATE.
		$data = $this->getRecord('id');

		if ($data)
		{
			// inject updated datetime
			$data->ping = $ping;

			// update record
			$dbo->updateObject('#__vikchannelmanager_messaging_users_pings', $data, 'id');
		}
		else
		{
			// build object to insert
			$data = new stdClass;
			$data->ping 	= $ping;
			$data->idorder  = $this->idOrder;
			$data->client 	= $this->client;

			// insert new record
			$dbo->insertObject('#__vikchannelmanager_messaging_users_pings', $data, 'id');
		}

		return 	$this;
	}

	/**
	 * Checks whether this user is currently online.
	 *
	 * @param 	integer  $threshold  A threshold to be applied to the
	 * 								 last ping made by the user (in seconds).
	 *
	 * @return 	boolean  True whether the user is online, false otherwise.
	 *
	 * @uses 	getRecord()
	 */
	public function isOnline($threshold = 30)
	{
		// get last ping made by the client
		$data = $this->getRecord('ping');

		if (!$data)
		{
			// the user never pinged this order
			return false;
		}

		$ping = JDate::getInstance($data->ping);
		$now  = JDate::getInstance();

		// make sure the difference in seconds between the last ping and the current
		// time is equals or lower than the specified threshold
		return abs($ping->getTimestamp() - $now->getTimestamp()) <= $threshold;
	}

	/**
	 * Checks whether this user is currently offline.
	 *
	 * @param 	integer  $threshold  A threshold to be applied to the
	 * 								 last ping made by the user (in seconds).
	 *
	 * @return 	boolean  True whether the user is offline, false otherwise.
	 *
	 * @uses 	isOnline()
	 */
	public function isOffline($threshold = 30)
	{
		// check if online and negate returned value
		return !$this->isOnline($threshold);
	}

	/**
	 * Returns the e-mail address of the user.
	 *
	 * @return 	mixed 	The user e-mail address, if any.
	 *
	 * @uses 	getOrder()
	 */
	public function getMail()
	{
		// check if we are a SITE client
		if ($this->client == 0)
		{
			// get order (throws an exception is user doesn't exist)
			$order = $this->getOrder();

			// return customer e-mail address
			return $order->custmail;
		}

		// otherwise return the administrator e-mail address 
		// specified within the configuration of VikBooking
		$mail = VikBooking::getAdminMail();

		// explode mail list, trim empty spaces and remove empty addresses
		return array_filter(array_map('trim', explode(',', $mail)));
	}

	/**
	 * Returns the phone number of the user.
	 *
	 * @return 	mixed 	The user phone number, if any.
	 *
	 * @uses 	getOrder()
	 */
	public function getPhone()
	{
		// check if we are a SITE client
		if ($this->client == 0)
		{
			// get order (throws an exception is user doesn't exist)
			$order = $this->getOrder();

			// return customer phone number
			return $order->phone;
		}

		// otherwise return the administrator phone number 
		// specified within the configuration of VikBooking
		$phones = VikBooking::getSMSAdminPhone();

		// make sure multiple phone numbers are separated with a comma
		$phones = str_replace(';', ',', $phones);

		// explode phone numbers list, trim empty spaces and remove empty addresses
		return array_filter(array_map('trim', explode(',', $phones)));
	}

	/**
	 * Returns the name of the user.
	 *
	 * @return 	mixed 	The user name, if any.
	 *
	 * @uses 	getOrder()
	 */
	public function getCustomerName()
	{
		// get order (throws an exception is user doesn't exist)
		$order = $this->getOrder();

		// return customer e-mail address
		return trim($order->first_name . ' ' . $order->last_name);
	}

	/**
	 * Returns the record object of the current order.
	 *
	 * @return 	mixed 	The order if exists, null otherwise.
	 *
	 * @throws 	Exception  In case the order doesn't exist.
	 */
	public function getOrder()
	{
		if ($this->order === null)
		{
			$dbo = JFactory::getDbo();

			// search for a record that matches our query
			$q = $dbo->getQuery(true)
				->select('`o`.*')
				->select($dbo->qn('c.first_name'))
				->select($dbo->qn('c.last_name'))
				->from($dbo->qn('#__vikbooking_orders', 'o'))
				->leftjoin($dbo->qn('#__vikbooking_customers_orders', 'a') . ' ON ' . $dbo->qn('o.id') . ' = ' . $dbo->qn('a.idorder'))
				->leftjoin($dbo->qn('#__vikbooking_customers', 'c') . ' ON ' . $dbo->qn('c.id') . ' = ' . $dbo->qn('a.idcustomer'))
				->where($dbo->qn('o.id') . ' = ' . $this->idOrder);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if (!$dbo->getNumRows())
			{
				throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
			}

			// cache order object
			$this->order = $dbo->loadObject();
		}

		return $this->order;
	}

	/**
	 * Returns the record object of the current client.
	 *
	 * @param 	mixed 	$columns 	The columns to inject within the query.
	 * 								If not specified, all the columns will be retrieved.
	 *
	 * @return 	mixed 	The record if exists, null otherwise.
	 */
	protected function getRecord($columns = null)
	{
		$dbo = JFactory::getDbo();

		if (!$columns || $columns === '*')
		{
			// use plain STAR selector
			$columns = '*';
		}
		else
		{
			// quote name of specified column(s)
			$columns = array_map(array($dbo, 'qn'), (array) $columns);
		}

		// search for a record that matches our query
		$q = $dbo->getQuery(true)
			->select($columns)
			->from($dbo->qn('#__vikchannelmanager_messaging_users_pings'))
			->where($dbo->qn('idorder') . ' = ' . $this->idOrder)
			->where($dbo->qn('client') . ' = ' . $this->client);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			return $dbo->loadObject();
		}

		return null;
	}
}
