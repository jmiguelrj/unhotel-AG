<?php
/** 
 * @package     VikUpdater
 * @subpackage  wordpress
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

namespace VikWP\VikUpdater\WordPress\System;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Helper class to handle the plugin execution and routing.
 *
 * @since 1.0
 */
class Controller
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
     * @return  void
     */
    public static function process()
    {
        // get default task
        $controller = null;
        $task = ($_REQUEST['task'] ?? '');

        if (strpos($task, '.') !== false)
        {
            // extract controller from task
            list($controller, $task) = explode('.', $task);
        }

        if ($controller)
        {
            // use the specified controller
            $controller = \VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.controller.' . $controller);
        }
        else
        {
            // use default controller
            $controller = \VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.controller');
        }

        // start capturing the buffer
        ob_start();
        
        do_action('vikupdater_before_dispatch');
        
        // execute the provided task
        $controller->execute($task);
        // attempt to redirect
        $controller->redirect();

        do_action('vikupdater_after_dispatch');

        // capture the response echoed by the controller
        static::$response = ob_get_contents();

        // clean the buffer
        ob_end_clean();
    }

    /**
     * Renders the obtained response in HTML format.
     * Note: HEADERS ALREADY SENT
     *
     * @param   bool  $return  True to return the contents, false to echo them directly.
     *
     * @return  void|string
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

        if ($return)
        {
            return $body;
        }
        else
        {
            echo $body;
        }
    }
}
