<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2022 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Internal libraries autoloader.
 * 
 * @since 	1.8.4
 */
spl_autoload_register(function($class)
{
	// handle base VBO library
	if ($class === 'VikBooking')
	{
		require_once implode(DIRECTORY_SEPARATOR, [VBO_SITE_PATH, 'helpers', 'lib.vikbooking.php']);

		return true;
	}

	// handle base VCM library
	if ($class === 'VikChannelManager')
	{
		require_once implode(DIRECTORY_SEPARATOR, [VCM_SITE_PATH, 'helpers', 'lib.vikchannelmanager.php']);

		return true;
	}

	// handle config VCM library
	if ($class === 'VikChannelManagerConfig')
	{
		require_once implode(DIRECTORY_SEPARATOR, [VCM_SITE_PATH, 'helpers', 'vcm_config.php']);

		return true;
	}

	$guess_vcm = stripos($class, 'VCM');
	$guess_vbo = stripos($class, 'VBO');

	if ($guess_vcm !== 0 && $guess_vbo !== 0)
	{
		// ignore if we are loading an outsider
		return false;
	}

	// get the class prefix and base path
	if ($guess_vcm === 0)
	{
		$class_prefix = 'VCM';
		$class_bpath  = dirname(__FILE__);
	}
	else
	{
		$class_prefix = 'VBO';
		$class_bpath  = str_replace('vikchannelmanager', 'vikbooking', dirname(__FILE__));
	}

	// remove prefix from class
	$tmp = preg_replace("/^$class_prefix/", '', $class);
	// separate camel-case intersections
	$tmp = preg_replace("/([a-z])([A-Z])/", addslashes('$1' . DIRECTORY_SEPARATOR . '$2'), $tmp);

	// build path from which the class should be loaded
	$path = $class_bpath . DIRECTORY_SEPARATOR . strtolower($tmp) . '.php';

	// make sure the file exists
	if (is_file($path))
	{
		// include file and check if the class is now available
		if ((include_once $path) && (class_exists($class) || interface_exists($class) || trait_exists($class)))
		{
			return true;
		}
	}

	return false;
});
