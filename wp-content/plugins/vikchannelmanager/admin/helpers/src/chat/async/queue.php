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
 * Queue interface used to register asynchronous jobs, with the task of
 * processing the latest received messages.
 * 
 * @since 1.9.14
 */
interface VCMChatAsyncQueue
{
    /**
     * Pushes a new job within the queue.
     * 
     * @param   VCMChatAsyncJob  $job
     * 
     * @return  self
     */
    public function push(VCMChatAsyncJob $job);

    /**
     * Pops the next job from the queue.
     * 
     * @return  VCMChatAsyncJob|null
     */
    public function pop();
}
