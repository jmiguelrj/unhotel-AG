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
 * Chat asynchronous processors mediator.
 * 
 * @since 1.9.14
 */
final class VCMChatAsyncMediator
{
    /** @var VCMChatAsyncQueue */
    protected $queue;

    /** @var VCMChatAsyncProcessor[] */
    protected $processors = [];

    /** @var VCMLogInterface */
    protected $logger = null;

    /**
     * Class constructor.
     * 
     * @param  VCMChatAsyncQueue  $queue
     * @param  VCMLogInterface    $logger
     */
    public function __construct(VCMChatAsyncQueue $queue, ?VCMLogInterface $logger = null)
    {
        $this->queue = $queue;
        $this->logger = $logger ?: new VCMLogDriverNull;
    }

    /**
     * Attaches a new processor to the mediator.
     * 
     * @param   VCMChatAsyncProcessor  $processor
     * 
     * @return  self
     */
    public function attachProcessor(VCMChatAsyncProcessor $processor)
    {
        if ($processor instanceof VCMLogAwareInterface) {
            // propagate internal logger to the processor
            $processor->setLogger($this->logger);
        }

        $this->processors[] = $processor;

        return $this;
    }

    /**
     * Enqueues a new message to be processed asynchronously.
     * 
     * @param   object  $message
     * 
     * @return  self
     */
    public function enqueue($message)
    {
        $this->queue->push(new VCMChatAsyncJob($message->idthread, $message->id));

        return $this;
    }

    /**
     * Processes the next messages registered within the internal queue.
     * 
     * @param   int  $loop  The maximum number of jobs to pull.
     * 
     * @return  void
     */
    public function process(int $loop = 5)
    {
        $this->logger->info("Requested chat asynchronous processing for next {limit} queued threads...", [
            'limit' => $loop,
        ]);

        // manually boot the chat libraries
        require_once JPath::clean(VCM_SITE_PATH . '/helpers/chat/handler.php');

        $failed = [];

        for ($i = 1; $i <= $loop; $i++) {
            /** @var VCMChatAsyncJob|null */
            $job = $this->queue->pop();

            if (!$job) {
                // the queue is empty, immediately break the loop
                $this->logger->debug("The queue is empty.");
                break;
            }

            $db = JFactory::getDbo();

            // fetch booking ID and channel identifier
            $query = $db->getQuery(true)
                ->select($db->qn('idorder', 'id'))
                ->select($db->qn('channel'))
                ->from($db->qn('#__vikchannelmanager_threads'))
                ->where($db->qn('id') . ' = ' . $job->getThreadID());

            $db->setQuery($query, 0, 1);
            $booking = $db->loadObject();

            if (!$booking) {
                // booking not found, probably deleted
                $this->logger->warning("The booking assigned to the thread #{thread_id} does not exist any longer.", [
                    'thread_id' => $job->getThreadID(),
                ]);

                continue;
            }

            $this->logger->info("Processing queued job **#{index}** (booking id: **{booking_id}**; thread ID: **{thread_id}**).", [
                'index' => $i,
                'booking_id' => $booking->id,
                'thread_id' => $job->getThreadID(),
            ]);

            // instantiate the chat
            $chat = VCMChatHandler::getInstance($booking->id, $booking->channel);

            // iterate all the registered processors
            foreach ($this->processors as $processor) {
                // create a unique identifier for the processor
                $processorId = str_replace('vcmchatasyncprocessor', '', strtolower(get_class($processor)));

                $this->logger->info("Preparing `[{class}]` processor instance...", [
                    'class' => $processorId,
                ]);

                // check whether the rule already processed this job
                if ($job->isComplete($processorId)) {
                    $this->logger->notice("This instance already processed the current job.");
                    // job already processed by this rule, move on
                    continue;
                }

                try {
                    // process the messages
                    $processor->process($chat, $job);

                    // mark the job as processed by the current rule
                    $job->complete($processorId);

                    $this->logger->notice("`[{class}]` processed the job successfully.", [
                        'class' => $processorId,
                    ]);
                } catch (Throwable $error) {
                    // an error has occurred while processing the messages
                    $failed[] = $job;

                    $this->logger->error("`[{class}]` failed for the following reason.\n> {error}", [
                        'class' => $processorId,
                        'error' => $error->getMessage(),
                    ]);
                }
            }
        }

        if ($failed) {
            $this->logger->info("Re-adding **{failedCount}** failed jobs to the queue.", [
                'failedCount' => count($failed),
            ]);

            // re-enqueue the failed jobs to retry later
            foreach ($failed as $failedJob) {
                $this->queue->push($failedJob);
            }
        }
    }
}
