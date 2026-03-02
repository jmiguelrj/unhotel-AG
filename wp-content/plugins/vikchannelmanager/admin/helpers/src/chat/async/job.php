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
 * Encapsulates the information of an asynchronous job for the chat messages.
 * 
 * @since 1.9.14
 */
class VCMChatAsyncJob
{
    /** @var int */
    protected $threadId;

    /** @var int */
    protected $messageId;

    /**
     * A list of processors that completed the job.
     * 
     * @var string[]
     */
    protected $completed = [];

    /**
     * Class constructor.
     * 
     * @param  int  $threadId
     * @param  int  $messageId
     */
    public function __construct(int $threadId, int $messageId)
    {
        $this->threadId = $threadId;
        $this->messageId = $messageId;
    }

    /**
     * Returns the thread ID.
     * 
     * @return  int
     */
    public function getThreadID()
    {
        return $this->threadId;
    }

    /**
     * Returns the message ID.
     * 
     * @return  int
     */
    public function getMessageID()
    {
        return $this->messageId;
    }

    /**
     * Checks whether the specified processor successfully completed this job.
     * 
     * @param   string  $processor  The processor identifier.
     * 
     * @return  bool
     */
    public function isComplete(string $processor)
    {
        return in_array($processor, $this->completed);
    }

    /**
     * Configures the job as completed by the specified processor.
     * 
     * @param   string  $processor  The processor identifier.
     * 
     * @return  void
     */
    public function complete(string $processor)
    {
        // do not add the same processor more than once
        if (!$this->isComplete($processor)) {
            $this->completed[] = $processor;
        }
    }
}
