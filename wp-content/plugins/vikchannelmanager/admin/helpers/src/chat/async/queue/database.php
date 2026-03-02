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
 * Database queue implementation.
 * 
 * @since 1.9.14
 */
class VCMChatAsyncQueueDatabase implements VCMChatAsyncQueue
{
    /** @var JDatabaseDriver */
    protected $db;

    /**
     * Class constructor.
     * 
     * @param  JDatabaseDriver|null  $db
     */
    public function __construct($db = null)
    {
        $this->db = $db ?: JFactory::getDbo();
    }

    /**
     * @inheritDoc
     */
    public function push(VCMChatAsyncJob $job)
    {
        // check if we have already registered a job for the same thread
        $query = $this->db->getQuery(true)
            ->select(1)
            ->from($this->db->qn('#__vikchannelmanager_chat_async_jobs'))
            ->where($this->db->qn('id_thread') . ' = ' . (int) $job->getThreadID());

        $this->db->setQuery($query, 0, 1);

        if (!$this->db->loadResult()) {
            // no jobs found for this thread, create a new record
            $record = new stdClass;
            $record->id_thread = $job->getThreadID();
            $record->id_message = $job->getMessageID();
            $record->instance = serialize($job);
            $record->datetime = JFactory::getDate()->toSql();

            $this->db->insertObject('#__vikchannelmanager_chat_async_jobs', $record, 'id'); 
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function pop()
    {
        // load the next job from the queue
        $query = $this->db->getQuery(true)
            ->select('*')
            ->from($this->db->qn('#__vikchannelmanager_chat_async_jobs'))
            ->order($this->db->qn('datetime') . ' ASC');

        $this->db->setQuery($query, 0, 1);
        $job = $this->db->loadObject();

        if (!$job) {
            // no pending jobs
            return null;
        }

        // delete the job from the queue
        $this->db->setQuery(
            $this->db->getQuery(true)
                ->delete($this->db->qn('#__vikchannelmanager_chat_async_jobs'))
                ->where($this->db->qn('id') . ' = ' . $job->id)
        );
        
        $this->db->execute();

        // unserialize instance
        return unserialize($job->instance);
    }
}
