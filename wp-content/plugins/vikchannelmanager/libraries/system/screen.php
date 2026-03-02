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
 * Helper class to setup the WordPress Screen.
 *
 * @since 1.7.5
 */
class VikChannelManagerScreen
{
	/**
	 * Creates the option section within the WP Screen for VikChannelManager.
	 *
	 * @return 	void
	 */
	public static function options()
	{
		$app = JFactory::getApplication();

		// make sure we are in VikChannelManager (back-end)
		if (!$app->isAdmin() || $app->input->get('page') != 'vikchannelmanager')
		{
			// abort
			return;
		}

		// extract view from request
		$view = $app->input->get('view', null);

		if (empty($view))
		{
			// no view, try to check 'task'
			$view = $app->input->get('task', 'dashboard');
		}

		// allowed views to display screen options
		$allowed_views = array(
			'dashboard',
			'avpush',
			'ratespush',
		);

		if (!in_array($view, $allowed_views))
		{
			// abort
			return;
		}
 	
 		// create pagination option
	    $args = array(
	        'label'   => __('Number of items per page:'),
	        'default' => 20,
	        'option'  => 'vikchannelmanager_list_limit',
	    );
	 
	    add_screen_option('per_page', $args);
	}

	/**
	 * Filters a screen option value before it is set.
	 *
	 * @param 	boolean  $skip    Whether to save or skip saving the screen option value. Default false.
	 * @param 	string   $option  The option name.
	 * @param 	mixed    $value   The option value.
	 *
	 * @return  mixed    Returning false to the filter will skip saving the current option.
	 */
	public static function saveOption($skip, $option, $value)
	{
		$lookup = array(
			'vikchannelmanager_list_limit',
		);

		if (in_array($option, $lookup))
		{
			// return value to save it
			return $value;
		}

		// skip otherwise
		return $skip;
	}

	/**
	 * Creates the Help tabs within the WP Screen for VikChannelManager.
	 *
	 * @param 	WP_Screen  $screen  The current screen instance.
	 *
	 * @return 	void
	 */
	public static function help($screen = null)
	{
		$app = JFactory::getApplication();

		// make sure we are in VikChannelManager (back-end)
		if (!$app->isAdmin() || $app->input->get('page') != 'vikchannelmanager')
		{
			// abort
			return;
		}

		// make sure $screen is a valid instance
		if (!class_exists('WP_Screen') || !$screen instanceof WP_Screen)
		{
			// abort
			return;
		}

		// extract view from request
		$view = $app->input->get('view', null);

		if (empty($view))
		{
			// no view, try to check 'task'
			$view = $app->input->get('task', 'dashboard');
		}

		// make sure the view is supported
		if (!isset(static::$lookup[$view]))
		{
			// view not supported
			return;
		}
	}

	/**
	 * Clears the cache for the specified view, if specified.
	 *
	 * @param 	string|null  $view  Clear the cache for the specified view (if specified)
	 * 								or for all the existing views.
	 *
	 * @return 	void
	 */
	public static function clearCache($view = null)
	{
		if ($view)
		{
			delete_transient('vikchannelmanager_screen_' . $view);
		}
		else
		{
			foreach (static::$lookup as $view => $args)
			{
				if (is_array($args))
				{
					delete_transient('vikchannelmanager_screen_' . $view);
				}
			}

			// delete settings too
			delete_option('vikchannelmanager_screen_failed_attempts');
			delete_option('vikchannelmanager_list_limit');
		}
	}

	/**
	 * Lookup used to retrieve the arguments for the HTTP request.
	 *
	 * @var array
	 */
	protected static $lookup = array(
		/**
		 * @todo define documentation lookup here
		 */	
	);
}
