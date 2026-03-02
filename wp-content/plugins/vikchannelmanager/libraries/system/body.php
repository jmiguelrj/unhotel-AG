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
 * Helper class to handle the body of the page.
 *
 * @since 1.0
 */
class VikChannelManagerBody
{
	/**
	 * The response processed by the request.
	 *
	 * @var string
	 */
	protected static $response = null;

	/**
	 * Executes the MVC internal framework to obtain 
	 * the requested HTML page body.
	 *
	 * Note: HEADERS NOT SENT
	 *
	 * @return 	void
	 */
	public static function process()
	{
		jimport('joomla.application.component.controller');

		$task = JFactory::getApplication()->input->get('task');

		// start capturing the buffer
		ob_start();
		
		do_action('vikchannelmanager_before_dispatch');
		
		// execute the controller
		$controller = JController::getInstance('VikChannelManager', VIKCHANNELMANAGER_BASE);
		$controller->execute($task);

		// redirect if set by the controller
		$controller->redirect();

		do_action('vikchannelmanager_after_dispatch');

		// capture the response echoed by the controller
		static::$response = ob_get_contents();

		// clean the buffer
		ob_end_clean();

		/**
		 * We prepend the system messages to the body as we are displaying HTML contents.
		 * We no longer do this in the getHtml() method to avoid issues with the PHP session.
		 * 
		 * @since 	VCM 1.7.3 and VBO 1.3.5
		 */
		static::$response = VikBookingLayoutHelper::renderSystemMessages($queue = null, $echo = false) . static::$response;
	}

	/**
	 * Renders the obtained response in HTML format.
	 * Note: HEADERS ALREADY SENT
	 *
	 * @param 	boolean  $return 	True to return the contents.
	 * 								False to echo them directly.
	 *
	 * @return 	void|string
	 *
	 * @uses 	process()
	 */
	public static function getHtml($return = false)
	{
		// check if the response is set
		if (is_null(static::$response))
		{
			// obtain the response
			static::process();
		}

		// get response
		$body = static::$response;

		if (wp_doing_ajax())
		{
			/**
			 * Include the AJAX scripts.
			 *
			 * @since 1.7.5
			 */
			$body .= JFactory::getDocument()->getAjaxScripts();

			// if we are doing AJAX, encode the response in JSON format
			$body = json_encode(array($body));
		}
		// otherwise render the body
		else
		{
			$body = VikBookingLayoutHelper::renderBody(static::$response, false);
		}

		if ($return)
		{
			return $body;
		}
		else
		{
			echo $body;
		}
	}

	/**
	 * Returns the HTML response without altering it.
	 *
	 * @return 	string 	The HTML response.
	 *
	 * @uses 	process()
	 */
	public static function getResponse()
	{
		// check if the response is set
		if (is_null(static::$response))
		{
			// obtain the response
			static::process();
		}

		return static::$response;
	}
}
