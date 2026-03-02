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
 * Processes the messages in search of maintenance requests that should be automatically
 * reported within the task manager.
 * 
 * @since 1.9.14
 */
final class VCMChatAsyncProcessorTaskmanager implements VCMChatAsyncProcessor, VCMLogAwareInterface
{
    use VCMLogAwareTrait;

    /**
     * @inheritDoc
     */
    public function process(VCMChatHandler $chat, VCMChatAsyncJob $job)
    {
        // make sure the AI channel is supported
        if (!VikChannelManager::getChannel(VikChannelManagerConfig::AI)) {
            // prevent a useless call to e4jconnect
            $this->logger->warning('AI channel not supported... Skipping the tasks extraction job.');
            return;
        }

        /** @var array (associative) */
        $booking = $chat->getBooking();

        if ($booking['checkin'] > strtotime('+2 weeks')) {
            // Immediately abort if the difference between the check-in and the current
            // date is higher than 14 days. This way we can avoid to track requests that are
            // not related to maintenance tasks.
            $this->logger->info('The check-in of the booking is more than 2 weeks in the future ({checkin}).', [
                'checkin' => date('Y-m-d', $booking['checkin']),
            ]);

            return;
        }

        /** @var object[] */
        $messages = $this->getThreadMessages($chat, $job);

        if (!$messages) {
            // nothing to process here...
            $this->logger->info('No eligible messages under the specified thread.');
            return;
        }

        /** @var array[] */
        $rooms = VikBooking::loadOrdersRoomsData($booking['id'] ?? 0);

        $this->logger->debug("Trying to extract some tasks from the specified thread.\n\n> " . implode("\n> ", $messages));

        // extract maintenance task from the messages
        $task = (new VCMAiModelService)->extractTasks($messages, $rooms[0]['idroom'] ?? 0);

        if (!$task) {
            // no task detected
            $this->logger->info('There are no extractable tasks.');
            return;
        }

        // inject booking ID and room ID within the task details
        $task->id_order = $booking['id'] ?? null;
        $task->id_room = $rooms[0]['idroom'] ?? null;

        // the dates must be forced on the local timezone
        $checkin = date('Y-m-d H:i:00', $booking['checkin']);
        $now = JFactory::getDate('now', JFactory::getApplication()->get('offset'))->format('Y-m-d H:i:00', true);

        // task is expected for the highest date between the check-in and the current time
        $task->dueon = max($checkin, $now);

        $this->logger->debug("Task extracted successfully.\n\n```\n{task}\n```", [
            'task' => json_encode($task, JSON_PRETTY_PRINT),
        ]);

        // save task within the database
        VBOTaskModelTask::getInstance()->save($task);
    }

    /**
     * Returns the messages eligible for the job requirements.
     * 
     * @param   VCMChatHandler   $chat
     * @param   VCMChatAsyncJob  $job
     * 
     * @return  string[]  The messages list.
     */
    protected function getThreadMessages(VCMChatHandler $chat, VCMChatAsyncJob $job)
    {
        // load the eligible messages
        $messages = (new VCMChatFinder($chat))
            ->forThread($job->getThreadID())
            ->fromGuest()
            ->withContent()
            ->after('id', $job->getMessageID(), $inclusive = true)
            ->list();

        // reverse the messages to display the oldest one first
        $messages = array_reverse($messages);

        // map the messages to keep only the content
        return array_map(fn($msg) => $msg->content, $messages);
    }
}
