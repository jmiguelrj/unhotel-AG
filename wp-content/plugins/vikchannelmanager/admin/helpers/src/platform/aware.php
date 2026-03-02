<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2022 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Declares all the helper methods that may differ between every supported platform.
 * 
 * @since 	1.8.11
 */
abstract class VCMPlatformAware implements VCMPlatformInterface
{
	/**
	 * The platform URI handler.
	 * 
	 * @var VCMPlatformUriInterface
	 */
	private $uri;

	/**
	 * The event dispatcher handler.
	 * 
	 * @var VCMPlatformDispatcherInstance
	 */
	private $dispatcher;

	/**
	 * The platform page router handler.
	 * 
	 * @var VCMPlatformPagerouterInstance
	 */
	private $pagerouter;

	/**
	 * The platform cron environment handler.
	 * 
	 * @var VCMPlatformCronenvInstance
	 * @since 1.9.16
	 */
	private $cronEnv;

	/**
	 * Returns the URI helper instance.
	 *
	 * @return 	VCMPlatformUriInterface
	 */
	public function getUri()
	{
		if (is_null($this->uri))
		{
			// lazy creation
			$this->uri = $this->createUri();
		}

		// make sure we have a valid instance
		if (!$this->uri instanceof VCMPlatformUriInterface)
		{
			if (is_object($this->uri))
			{
				// extract class name from object
				$t = get_class($this->uri);
			}
			else
			{
				// fetch the type of the property
				$t = gettype($this->uri);
			}

			// nope, throw a "Not acceptable" 406 error
			throw new UnexpectedValueException(sprintf('The [%s] object is not a valid URI instance', $t), 406);
		}

		return $this->uri;
	}

	/**
	 * Returns the event dispatcher instance.
	 * 
	 * @return  VCMPlatformDispatcherInterface
	 */
	public function getDispatcher()
	{
		if (is_null($this->dispatcher))
		{
			// lazy creation
			$this->dispatcher = $this->createDispatcher();
		}

		// make sure we have a valid instance
		if (!$this->dispatcher instanceof VCMPlatformDispatcherInterface)
		{
			if (is_object($this->dispatcher))
			{
				// extract class name from object
				$t = get_class($this->dispatcher);
			}
			else
			{
				// fetch the type of the property
				$t = gettype($this->dispatcher);
			}

			// nope, throw a "Not acceptable" 406 error
			throw new UnexpectedValueException(sprintf('The [%s] object is not a valid dispatcher instance', $t), 406);
		}

		return $this->dispatcher;
	}

	/**
	 * Returns the page router instance.
	 * 
	 * @return  VCMPlatformPagerouterInterface
	 */
	public function getPagerouter()
	{
		if (is_null($this->pagerouter))
		{
			// lazy creation
			$this->pagerouter = $this->createPagerouter();
		}

		// make sure we have a valid instance
		if (!$this->pagerouter instanceof VCMPlatformPagerouterInterface)
		{
			if (is_object($this->pagerouter))
			{
				// extract class name from object
				$t = get_class($this->pagerouter);
			}
			else
			{
				// fetch the type of the property
				$t = gettype($this->pagerouter);
			}

			// nope, throw a "Not acceptable" 406 error
			throw new UnexpectedValueException(sprintf('The [%s] object is not a valid page router instance', $t), 406);
		}

		return $this->pagerouter;
	}

	/**
	 * Returns the cron environment instance.
	 * 
	 * @return  VCMPlatformCronenvInterface
	 * 
	 * @since   1.9.16
	 */
	public function getCronEnvironment()
	{
		if (is_null($this->cronEnv))
		{
			// lazy creation
			$this->cronEnv = $this->createCronEnvironment();
		}

		// make sure we have a valid instance
		if (!$this->cronEnv instanceof VCMPlatformCronenvInterface)
		{
			if (is_object($this->cronEnv))
			{
				// extract class name from object
				$t = get_class($this->cronEnv);
			}
			else
			{
				// fetch the type of the property
				$t = gettype($this->cronEnv);
			}

			// nope, throw a "Not acceptable" 406 error
			throw new UnexpectedValueException(sprintf('The [%s] object is not a valid cron environment instance', $t), 406);
		}

		return $this->cronEnv;
	}

	/**
	 * Creates a new URI helper instance.
	 *
	 * @return  VCMPlatformUriInterface
	 */
	abstract protected function createUri();

	/**
	 * Creates a new event dispatcher instance.
	 * 
	 * @return  VCMPlatformDispatcherInterface
	 */
	abstract protected function createDispatcher();

	/**
	 * Creates a new page router instance.
	 * 
	 * @return  VCMPlatformPagerouterInterface
	 */
	abstract protected function createPagerouter();

	/**
	 * Creates a new cron environment instance.
	 * 
	 * @return  VCMPlatformCronenvInterface
	 * 
	 * @since   1.9.16
	 */
	abstract protected function createCronEnvironment();
}
