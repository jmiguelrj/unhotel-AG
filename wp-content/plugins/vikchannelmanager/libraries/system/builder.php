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
 * Helper class to setup the plugin.
 *
 * @since 1.0
 */
class VikChannelManagerBuilder
{
	/**
	 * Loads the .mo language related to the current locale.
	 *
	 * @return 	void
	 */
	public static function loadLanguage()
	{
		$app = JFactory::getApplication();

		// use the correct path depending on the current section
		if ($app->isAdmin())
		{
			$path = VIKCHANNELMANAGER_ADMIN_LANG;
		}
		else
		{
			$path = VIKCHANNELMANAGER_SITE_LANG;
		}

		$handler = VIKCHANNELMANAGER_LIBRARIES . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
		$domain  = 'vikchannelmanager';

		// init language
		$lang = JFactory::getLanguage();
		
		if ($app->isAdmin())
		{
			$lang->attachHandler($handler . 'adminsys.php', $domain);
			$lang->attachHandler($handler . 'admin.php', $domain);
		}
		else
		{
			$lang->attachHandler($handler . 'site.php', $domain);
		}

		$lang->attachHandler($handler . 'general.php', $domain);

		$lang->load($domain, $path);
	}

	/**
	 * Setup the pagination layout to use.
	 *
	 * @return 	void
	 */
	public static function setupPaginationLayout()
	{
		// let the pagination be registered by Vik Booking
	}

	/**
	 * Pushes the plugin pages into the WP admin menu.
	 *
	 * @return 	void
	 *
	 * @link 	https://developer.wordpress.org/resource/dashicons/#star-filled
	 */
	public static function setupAdminMenu()
	{
		JLoader::import('adapter.acl.access');
		$capability = JAccess::adjustCapability('core.manage', 'com_vikchannelmanager');

		add_menu_page(
			JText::_('COM_VIKCHANNELMANAGER'), 	// page title
			JText::_('COM_VIKCHANNELMANAGER_MENU'), 	// menu title
			$capability,						// capability
			'vikchannelmanager', 						// slug
			array('VikChannelManagerBody', 'getHtml'),	// callback
			'dashicons-share',				// icon
			71									// ordering
		);
	}

	/**
	 * Setup HTML helper classes.
	 * This method should be used to register custom function
	 * for example to render own layouts.
	 *
	 * @return 	void
	 */
	public static function setupHtmlHelpers()
	{
		// let the helpers be registered by Vik Booking
	}

	/**
	 * Registers all the widget contained within the modules folder.
	 *
	 * @return 	void
	 */
	public static function setupWidgets()
	{
		JLoader::import('adapter.module.factory');

		// load all the modules
		JModuleFactory::load(VIKCHANNELMANAGER_BASE . DIRECTORY_SEPARATOR . 'modules');
	}
}
