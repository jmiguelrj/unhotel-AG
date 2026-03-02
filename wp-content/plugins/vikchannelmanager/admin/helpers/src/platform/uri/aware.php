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
 * Adapter used to implement URI commons methods between the platform interfaces.
 * 
 * @since 	1.8.11
 */
abstract class VCMPlatformUriAware implements VCMPlatformUriInterface
{
	/**
	 * Converts the given absolute path into a reachable URL.
	 *
	 * @param 	string   $path      The absolute path.
	 * @param 	boolean  $relative  True to receive a relative path.
	 *
	 * @return 	mixed    The resulting URL on success, null otherwise.
	 */
	public function getUrlFromPath($path, $relative = false)
	{
		// get platform base path
		$base = $this->getAbsolutePath();

		if (strpos($path, $base) !== 0)
		{
			// The path doesn't start with the base path of the site...
			// Probably the path cannot be reached via URL.
			return null;
		}

		// remove initial path
		$path = str_replace($base, '', $path);
		// remove initial directory separator
		$path = preg_replace("/^[\/\\\\]/", '', $path);

		if (DIRECTORY_SEPARATOR === '\\')
		{
			// replace Windows DS
			$path = preg_replace("[\\\\]", '/', $path);
		}

		if ($relative)
		{
			return $path;
		}

		// rebuild URL
		return JUri::root() . $path;
	}

	/**
	 * Returns the platform base path.
	 *
	 * @return 	string
	 */
	abstract protected function getAbsolutePath();
}
