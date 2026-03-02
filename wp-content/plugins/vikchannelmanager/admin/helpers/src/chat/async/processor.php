<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2025 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Interface for rules interested in processing the jobs registered
 * within the asynchronous queue.
 * 
 * @since 1.9.14
 */
interface VCMChatAsyncProcessor
{
    /**
     * Processes the specified list of messages, always belonging to the same thread.
     * 
     * @param   VCMChatHandler   $chat
     * @param   VCMChatAsyncJob  $job
     * 
     * @return  void
     */
    public function process(VCMChatHandler $chat, VCMChatAsyncJob $job);
}
