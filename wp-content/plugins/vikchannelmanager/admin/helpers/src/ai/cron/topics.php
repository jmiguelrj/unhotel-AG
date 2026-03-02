<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// Restricted access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Topics cron AI helper.
 * 
 * @since 1.9
 */
class VCMAiCronTopics
{
    /**
     * Periodically extracts the topics from the messages wrote by the customers.
     * The topics are used to summarize the threads and to elaborate a ranking for
     * the most frequently asked questions.
     * 
     * @param   int   The maximum number of threads to read per time.
     * 
     * @return  void
     */
    public function extract(int $max = 3)
    {
        $db = JFactory::getDbo();

        // randomize the maximum number of threads to take
        $max = rand(1, $max);

        // load the next thread(s) to parse
        $query = $db->getQuery(true)
            ->select($db->qn('id'))
            ->select($db->qn('idorder'))
            ->select($db->qn('channel'))
            ->from($db->qn('#__vikchannelmanager_threads'))
            ->where($db->qn('ai_processed') . ' = 0')
            ->order($db->qn('id') . ' ASC');

        $db->setQuery($query, 0, $max);
        $threads = $db->loadObjectList();
        
        foreach ($threads as $thread) {
            // extract topics from the current thread
            if (!$this->extractFromThread($thread)) {
                return;
            }
        }

        if (!$threads) {
            // Reset the processed threads without a saved topic.
            // This happens when the system processes a thread without
            // at least a customer message
            $db->setQuery(
                $db->getQuery(true)
                    ->update($db->qn('#__vikchannelmanager_threads'))
                    ->set($db->qn('ai_processed') . ' = 0')
                    ->where($db->qn('ai_processed') . ' = 1')
                    ->andWhere([
                        $db->qn('topic') . ' IS NULL',
                        $db->qn('topic') . ' = \'\'',
                    ], 'OR')
            );
            $db->execute();
        }
    }

    /**
     * Extracts the topics from the messages of the provided thread.
     * 
     * @param   object  $thread  The thread object (id, idorder, channel).
     * 
     * @return  bool    True on success, false on failure (credit exhausted?).
     */
    public function extractFromThread($thread)
    {
        $db = JFactory::getDbo();

        // load the messages of the thread
        $query = $db->getQuery(true)
            ->select('content')
            ->from($db->qn('#__vikchannelmanager_threads_messages'))
            ->where($db->qn('idthread') . ' = ' . (int) $thread->id)
            ->where($db->qn('sender_type') . ' = ' . $db->q('guest'))
            ->where('LENGTH(' . $db->qn('content') . ') > 19')
            ->order($db->qn('dt') . ' ASC');

        $db->setQuery($query);
        $messages = $db->loadColumn();

        if ($messages)
        {
            try
            {
                // extract the topics for each guest message
                $topics = (new VCMAiModelService)->extractTopics($messages);
            }
            catch (Exception $error)
            {
                if (in_array($error->getCode(), [402, 403]))
                {
                    // Do not proceed in case the user is no longer authorized to use the AI service.
                    // 402: credit exhausted
                    // 403: doesn't own AI channel
                    return false;
                }

                $topics = [];
            }

            try
            {
                // add topic to thread (if any) and flag as processed
                $thread->topic = $topics[0] ?? null;
                $thread->ai_processed = 1;
                $db->updateObject('#__vikchannelmanager_threads', $thread, 'id');
            }
            catch (Exception $error)
            {
                // something went wrong...
            }

            if ($topics)
            {
                // hit all the topics found
                (new VCMChatTopics)->hit($topics, $thread->id);
            }
        }
        else
        {
            // flag as processed
            $thread->ai_processed = 1;
            $db->updateObject('#__vikchannelmanager_threads', $thread, 'id');
        }

        return true;
    }
}
