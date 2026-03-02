<?php
/** 
 * @package   	VikChannelManager - Libraries
 * @subpackage 	system
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class used to provide support for the <head> of the page.
 *
 * @since 1.0
 */
class VikChannelManagerAssets
{
	/**
	 * A list containing all the methods already used.
	 *
	 * @var array
	 */
	protected static $loaded = array();

	/**
	 * Loads all the assets required for the plugin.
	 *
	 * @return 	void
	 */
	public static function load()
	{
		// loads only once
		if (static::isLoaded(__METHOD__))
		{
			return;
		}

		$document = JFactory::getDocument();

		$internalFilesOptions = array('version' => VIKCHANNELMANAGER_SOFTWARE_VERSION);

		if (JFactory::getApplication()->isAdmin())
		{
			// load assets for CSS and JS
			$document->addStyleSheet(VIKBOOKING_ADMIN_ASSETS_URI . 'css/system.css', $internalFilesOptions, array('id' => 'vbo-sys-style'));
			$document->addScript(VIKBOOKING_ADMIN_ASSETS_URI . 'js/system.js', $internalFilesOptions, array('id' => 'vbo-sys-script'));
			$document->addStyleSheet(VIKBOOKING_ADMIN_ASSETS_URI . 'css/bootstrap.lite.css', $internalFilesOptions, array('id' => 'bootstrap-lite-style'));
			$document->addScript(VIKBOOKING_ADMIN_ASSETS_URI . 'js/bootstrap.min.js', $internalFilesOptions, array('id' => 'bootstrap-script'));

			// load plugin assets
			VCM::load_css_js();

			$document->addScriptDeclaration(
<<<JS
if (typeof Joomla === 'undefined') {
	var Joomla = new JoomlaCore();
}
JS
			);

			/**
			 * Load the Web App Manifest JSON file.
			 * 
			 * @requires 	VBO >= 1.6.5
			 * 
			 * @since 		1.8.20
			 */
			if (class_exists('VBOWebappManifest')) {
				VBOWebappManifest::load();
			}
		}
	}

	/**
	 * Checks if the method has been already loaded.
	 * This function assumes that after this check we are going
	 * to use the specified method.
	 *
	 * A method is considered loaded only if the arguments used are the same.
	 *
	 * @param 	string 	 $method 	The method to check for.
	 * @param 	array 	 $args 		The list of arguments.
	 * 
	 * @return 	boolean  True if already used, otherwise false.
	 */
	protected static function isLoaded($method, array $args = array())
	{
		// generate a unique signature containing the method name
		// and the list of arguments to use
		$sign = serialize(array($method, $args));

		// check if the method has been already loaded
		if (isset(static::$loaded[$sign]))
		{
			// already loaded
			return true;
		}

		// mark the method as loaded
		static::$loaded[$sign] = 1;

		// not loaded
		return false;
	}
}
