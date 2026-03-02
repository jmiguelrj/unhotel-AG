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

VikChannelManagerLoader::import('update.manager');

/**
 * Class used to handle the activation, deactivation and 
 * uninstallation of VikChannelManager plugin.
 *
 * @since 1.0
 */
class VikChannelManagerInstaller
{
	/**
	 * Flag used to init the class only once.
	 *
	 * @var boolean
	 */
	protected static $init = false;

	/**
	 * Initialize the class attaching wp actions.
	 *
	 * @return 	void
	 */
	public static function onInit()
	{
		// init only if not done yet
		if (static::$init === false)
		{
			// handle installation message
			add_action('admin_notices', array('VikChannelManagerInstaller', 'handleMessage'));

			/**
			 * Register hooks and actions here
			 */

			// mark flag as true to avoid init it again
			static::$init = true;
		}
	}

	/**
	 * Handles the activation of the plugin.
	 *
	 * @return 	void
	 */
	public static function activate()
	{
		// get installed software version
		$version = get_option('vikchannelmanager_software_version', null);

		// check if the plugin has been already installed
		if (is_null($version))
		{
			// dispatch UPDATER to launch installation queries
			VikChannelManagerUpdateManager::install();

			// mark the plugin has installed to avoid duplicated installation queries
			update_option('vikchannelmanager_software_version', VIKCHANNELMANAGER_SOFTWARE_VERSION);
		}

		// set activation flag to display a message
		add_option('vikchannelmanager_onactivate', 1);
	}

	/**
	 * Handles the deactivation of the plugin.
	 *
	 * @return 	void
	 */
	public static function deactivate()
	{
		// do nothing for the moment
	}

	/**
	 * Handles the uninstallation of the plugin.
	 *
	 * @return 	void
	 */
	public static function uninstall()
	{
		// dispatch UPDATER to drop database tables
		VikChannelManagerUpdateManager::uninstall();

		// delete installation flag
		delete_option('vikchannelmanager_software_version');
	}

	/**
	 * Checks if the current version should be updated
	 * and, eventually, processes it.
	 * 
	 * @return 	void
	 */
	public static function update()
	{
		// get installed software version
		$version = get_option('vikchannelmanager_software_version', null);

		// check if we are running an older version
		if (VikChannelManagerUpdateManager::shouldUpdate($version))
		{
			// process the update (we don't need to raise an error)
			VikChannelManagerUpdateManager::update($version);

			// update cached plugin version
			update_option('vikchannelmanager_software_version', VIKCHANNELMANAGER_SOFTWARE_VERSION);
		}
	}

	/**
	 * Method used to check for any installation message to show.
	 *
	 * @return 	void
	 */
	public static function handleMessage()
	{
		$app = JFactory::getApplication();

		// if we are in the admin section and the plugin has been activated
		if ($app->isAdmin() && get_option('vikchannelmanager_onactivate') == 1)
		{
			// delete the activation flag to avoid displaying the message more than once
			delete_option('vikchannelmanager_onactivate');

			?>
			<div class="notice is-dismissible activate-success">
				<p>
					<strong>Thank you for activating our plugin!</strong>
					<a href="https://vikwp.com">https://vikwp.com</a>
				</p>
			</div>
			<?php
		}

		// make sure VikBooking is active
		if (!is_plugin_active('vikbooking/vikbooking.php'))
		{
			?>
			<div class="notice is-dismissible notice-warning">
				<p>
					VikBooking plugin is not active!
				</p>
			</div>
			<?php
		}
	}
}
