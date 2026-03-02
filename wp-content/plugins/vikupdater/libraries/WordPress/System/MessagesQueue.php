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
 * Helpers class used to collect messages and display them according to
 * the WordPress notices standards.
 * 
 * @since 2.0
 */
class MessagesQueue
{
    /**
     * The queue containing the messages to display, grouped per message type:
     * info, success, error, warning.
     * 
     * @var array
     */
    protected $queue = [];

    /**
     * Class contructor.
     */
    public function __construct()
    {
        // get system messages saved within the transient
        $messages = get_transient('vikupdater_system_messages', null);

        // init queue with the messages saved in the WP options
        $this->queue = $messages ? json_decode($messages, true) : [];
    }

    /**
     * Class destructor.
     */
    public function __destruct()
    {
        if ($this->queue)
        {
            // The system wasn't able to display the message, register it to be displayed at the next page loading.
            // The messages will be preserved only for 15 minutes.
            set_transient('vikupdater_system_messages', json_encode($this->queue), 15 * MINUTE_IN_SECONDS);
        }
        else
        {
            // delete transient, if any
            delete_transient('vikupdater_system_messages');
        }
    }

    /**
     * Registers an information message.
     * 
     * @see add()
     */
    public function info(string $message)
    {
        $this->add($message, 'info');
    }

    /**
     * Registers a successful message.
     * 
     * @see add()
     */
    public function success(string $message)
    {
        $this->add($message, 'success');
    }

    /**
     * Registers an error message.
     * 
     * @see add()
     */
    public function error(string $message)
    {
        $this->add($message, 'error');
    }

    /**
     * Registers a warning message.
     * 
     * @see add()
     */
    public function warning(string $message)
    {
        $this->add($message, 'warning');
    }

    /**
     * Register a message within the queue.
     * 
     * @param   string  $message  The message to enqueue.
     * @param   string  $type     The message type.
     * 
     * @return  bool    True if added, false if duplicate.
     */
    public function add(string $message, string $type)
    {
        if (!isset($this->queue[$type]))
        {
            // create category for the first time
            $this->queue[$type] = [];
        }

        // make sure the same message hasn't been registered yet
        if (in_array($message, $this->queue[$type]))
        {
            // duplicate message
            return false;
        }

        $this->queue[$type][] = $message;

        return true;
    }

    /**
     * Displays all the messages and flushes the queue.
     * 
     * @param   bool  $return  True to return the messages as a string,
     *                         false to directly echo them.
     * 
     * @return  string|void
     */
    public function display($return = false)
    {
        $html = '';

        // iterate the messages queue, per group
        foreach ($this->queue as $group => $messages)
        {
            foreach ($messages as $message)
            {
                // in case the message is plain text, wrap message in a paragraph
                if (strip_tags($message) === $message)
                {
                    $message = '<p>' . $message . '</p>';
                }

                // display message
                $html .= '<div class="notice notice-' . $group . ' is-dismissible">' . $message . '</div>';
            }
        }

        // clear the queue
        $this->queue = [];

        if ($return)
        {
            return $html;
        }

        echo $html;
    }
}
