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

// autoload dependencies
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'driver.php';

/**
 * Mediator class used to handler notification drivers.
 * 
 * @since 1.6.13
 */
class VCMChatNotificationMediator extends JObject
{
	/**
	 * The singleton instance.
	 *
	 * @var VCMChatNotificationMediator
	 */
	protected static $instance = null;	

	/**
	 * A list of supported drivers.
	 *
	 * @var VCMChatNotificationDriver[]
	 */
	protected $drivers = array();

	/**
	 * Returns a new instance of this class, only creating it
	 * if it doesn't exist yet.
	 *
	 * @return 	self
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
	public function __construct()
	{
		// load all drivers
		$drivers = glob(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'drivers' . DIRECTORY_SEPARATOR . '*.php');

		// iterate all drivers
		foreach ($drivers as $driverPath)
		{
			// require driver file
			require_once $driverPath;

			// get driver name
			$name = basename($driverPath);
			$name = substr($name, 0, strrpos($name, '.'));

			// get driver classname
			$classname = 'VCMChatNotificationDriver' . ucwords($name);

			// make sure the class exists
			if (class_exists($classname))
			{
				// instantiate driver
				$driver = new $classname();

				// make sure we have a valid instance and it is supported
				if ($driver instanceof VCMChatNotificationDriver && $driver->isSupported())
				{
					// driver is supported, push it within the list
					$this->drivers[] = $driver;
				}
			}
		}
	}

	/**
	 * Invokes all the proper drivers in order to dispatch a notification
	 * to the specified user.
	 *
	 * @param 	VCMChatUser 	$user 	  The instance containing the user/order details.
	 * @param 	VCMChatMessage  $message  The instance containing the message details.
	 *
	 * @return 	boolean 		True in case at least one notification went fine, false otherwise.
	 */
	public function notify(VCMChatUser $user, VCMChatMessage $message)
	{
		// load plugins dispatcher
		JPluginHelper::importPlugin('e4j');
		if (class_exists('JEventDispatcher'))
		{
			$dispatcher   = JEventDispatcher::getInstance();
			$dispatch_met = 'trigger';
		}
		else
		{
			$dispatcher   = JFactory::getApplication();
			$dispatch_met = 'triggerEvent';
		}

		$success = false;

		// always clear errors list
		$this->_errors = array();

		// iterate all supported drivers
		foreach ($this->drivers as $driver)
		{
			try
			{
				/**
				 * Trigger event to allow the plugins to accept or deny the current driver for
				 * chat notifications. In case one ore more plugins return FALSE, the driver will
				 * be skipped.
				 *
				 * Example of usage (blocking mail notification for admin):
				 *
				 * if ($user->getClient() == 1 && $driver instanceof VCMChatNotificationDriverMail)
				 * {
				 * 	   return false;
				 * }
				 *
				 * @param 	mixed 	$driver   The driver that will be used to send the notification (VCMChatNotificationDriver).
				 * @param 	mixed 	$user 	  The user instance (VCMChatUser).
				 * @param 	mixed 	$message  The message instance (VCMChatMessage)
				 *
				 * @return 	mixed 	False to avoid dispatching the notification with this driver.
				 *
				 * @since 	1.6.13
				 */
				$res = $dispatcher->{$dispatch_met}('onBeforeChatNotificationDispatch', array($driver, $user, $message));

				// make sure the result array doesn't contain a FALSE element,
				// otherwise skip the driver
				if (!in_array(false, $res, true))
				{
					// try to send the notification
					$success = $driver->notify($user, $message) || $success;
				}
			}
			catch (Exception $e)
			{
				// use JObject methods to register the error caught
				$this->setError($e->getMessage());
			}
		}

		return $success;
	}
}
