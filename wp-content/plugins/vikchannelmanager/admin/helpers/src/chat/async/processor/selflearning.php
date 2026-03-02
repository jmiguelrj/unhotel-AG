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
 * Processes the messages in search of conversations that require an auto-learning for the AI.
 * 
 * @since 1.9.16
 */
final class VCMChatAsyncProcessorSelflearning implements VCMChatAsyncProcessor, VCMLogAwareInterface
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
            $this->logger->warning('AI channel not supported... Skipping the self learning job.');
            return;
        }

        /** @var array (associative) */
        $booking = $chat->getBooking();

        /** @var object[] */
        $messages = $this->getThreadMessages($chat, $job);

        if (!$messages) {
            // nothing to process here...
            $this->logger->info('No eligible messages under the specified thread.');
            return;
        }

        $this->logger->debug("Eligible messages found.\n\n```\n{conversation}\n```", [
            'conversation' => json_encode($messages, JSON_PRETTY_PRINT),
        ]);

        /** @var array[] */
        $rooms = VikBooking::loadOrdersRoomsData($booking['id'] ?? 0);
        $roomId = $rooms[0]['idroom'] ?? null;

        // verify the knowledge of the AI
        if ($this->isTrained($messages, $booking['id'], $roomId)) {
            // don't need to train the AI again
            $this->logger->info('The AI has been already trained about this stuff.');
            return;
        }

        // ask the AI to prepare some training sets based on the conversation
        $trainingSets = $this->getTrainingDrafts($messages);

        if (!$trainingSets) {
            $this->logger->info('No eligible training set articles.');
            return;
        }

        foreach ($trainingSets as $set) {
            // save the training set
            $this->saveTrainingDraft($set, $messages, $roomId);
        }
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
        // prepare finder conditions
        $finder = (new VCMChatFinder($chat))
            ->forThread($job->getThreadID())
            ->after('id', $job->getMessageID(), $inclusive = true)
            ->withContent();

        // make sure we have at least a question from the guest, in order to avoid
        // analyzing host messages sent by cron jobs
        $hasGuestMessages = (clone $finder)->fromGuest()->some();

        if (!$hasGuestMessages) {
            // no guest messages, ignore the answers provided by the host
            return [];
        }

        // check whether under the new messages there's at least an answer from the host,
        // otherwise it would mean that no information has been provided to train the AI
        $hasNewHostReplies = (clone $finder)->fromHost()->some();

        if (!$hasNewHostReplies) {
            // The question of the user doesn't have an answer yet...
            // Check whether the last message wrote by the guest doesn't need a reply.
            $guestNeedsReply = (clone $finder)->fromGuest()->latest()->needs_reply ?? true;

            if ($guestNeedsReply) {
                // The guest message needs a reply. We can throw an exception to retry later.
                throw new Exception('No host answers yet. Reschedule for later processing.', 425);
            }
        }

        // make sure the answers haven't been provided by the AI
        $hasAiReplies = (clone $finder)->fromHost()->match('sender_name', 'AI')->some();

        if ($hasAiReplies) {
            // the AI seems to be already trained about this stuff
            return [];
        }

        // reverse the messages to display the oldest one first
        $messages = array_reverse($finder->list());

        // map the messages into a form readable by the AI
        return array_map(fn($msg) => [
            'role' => strcasecmp($msg->sender_type, 'guest') ? 'assistant' : 'user',
            'content' => $msg->content,
        ], $messages);
    }

    /**
     * Checks whether the AI actually owns a knowledge able to reply to the
     * questions wrote in the provided messages.
     * 
     * @param   array     $messages
     * @param   int       $bookingId
     * @param   int|null  $roomId
     * 
     * @return  bool
     */
    protected function isTrained(array $messages, int $bookingId, ?int $roomId = null)
    {
        // get rid of all the messages wrote by the assistant
        $messages = array_filter($messages, fn($msg) => $msg['role'] == 'user');

        try {
            // ask the AI whether it is able to reply to this conversation
            (new VCMAiModelService)->answer($messages, [
                'id_order' => $bookingId,
                'id_listing' => $roomId,
                // required to include the drafts under review as well
                'test' => true,
            ]);
        } catch (Exception $error) {
            // AI is unable to reply, missing training
            return false;
        }

        // the AI has been already trained about this stuff
        return true;
    }

    /**
     * Asks the AI to prepare a training draft according to the QAs under the
     * specified messages.
     * 
     * @param   array  $messages
     * 
     * @return  object[]
     */
    protected function getTrainingDrafts(array $messages)
    {
        $drafts = [];

        try {
            // extract articles from conversation
            $drafts = (new VCMAiModelService)->learn($messages);
        } catch (Exception $error) {
            // an error has occurred...
            $this->logger->error("Unable to create training drafts according to the specified messages.\n> {error}", [
                'error' => $error->getMessage(),
            ]);
        }

        return $drafts;
    }

    /**
     * Saves the provided training set as a new draft.
     * 
     * @param   object    $set       The training set data.
     * @param   array     $messages  The conversation so far.
     * @param   int|null  $roomId    An optional listing ID.
     * 
     * @return  void
     */
    protected function saveTrainingDraft(object $set, array $messages, ?int $roomId = null)
    {
        // fulfill the set with extra data
        $set->published = 0;
        $set->needsreview = 1;
        $set->reference = $messages;
        $set->language = 'en-GB';

        if ($roomId) {
            // available only for the specified listing
            $set->listing_selection = 0;
            $set->id_listing = $roomId;
        }

        $this->logger->debug("Saving the training set as a draft.\n\n```\n{draft}\n```", [
            'draft' => json_encode($set, JSON_PRETTY_PRINT),
        ]);

        try {
            // save the training set
            (new VCMAiModelTraining)->insert($set);
        } catch (Exception $error) {
            // an error has occurred...
            $this->logger->error("Training set creation failed for the following reason.\n> {error}", [
                'error' => $error->getMessage(),
            ]);
        }
    }
}
