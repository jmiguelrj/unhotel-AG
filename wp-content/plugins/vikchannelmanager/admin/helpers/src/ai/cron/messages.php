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
 * Messages cron AI helper.
 * 
 * @since 1.9
 */
class VCMAiCronMessages
{
    /** @var VCMAiModelSettings */
    protected $settingsModel;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->settingsModel = new VCMAiModelSettings;
    }

    /**
     * Periodically process the oldest message that requires a reply and asks
     * to the AI to write an answer.
     * 
     * @return  void
     */
    public function autoReply()
    {
        // check whether the auto-reply has been enabled
        if (!$this->isEnabled()) {
            // abort immediately
            return;
        }

        // require VCMChatHandler class file
        require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'chat' . DIRECTORY_SEPARATOR . 'handler.php';

        // fetch all the threads with a message posted by the guest in the previous 2 hours
        // that require a reply from the administrator
        $threads = VCMChatHandler::getLatestThreads([
            'sender' => 'guest',
            'join_sender' => true,
            'noreply' => true,
            'start_dt' => JFactory::getDate('-2 hours')->toISO8601(),
        ]);

        // process the threads from the oldest to the newest
        foreach (array_reverse($threads) as $thread) {
            // check if we should skip the current thread
            if ($this->shouldSkip($thread)) {
                continue;
            }

            // set up notification for VBO center
            $notification = [
                'sender' => 'ai',
                'idorder' => $thread->idorder,
                'widget' => 'guest_messages',
                'widget_options' => [
                    'bid' => $thread->idorder,
                ],
            ];

            // set message as "AI replied" to avoid processing it twice
            $this->setMessageReplied($thread->id_message, true);

            // create chat instance for this thread
            $chat = VCMChatHandler::getInstance($thread->idorder, $thread->channel);

            // gather the message contents and sender roles
            $messages = $this->getThreadMessages($chat, $thread);
                
            try {
                // ask the AI to continue the conversation
                $message = $this->fetchAnswer($chat, $thread, $messages);

                // check if we should directly send the message or if we should create a draft
                if ($this->shouldCreateDraft()) {
                    // create a draft for the elaborated answer
                    $this->createDraft($thread, $message);

                    // complete notification details for the created draft
                    $notification['type']    = 'message.reply.draft';
                    $notification['title']   = JText::_('VCM_AI_AUTO_RESPONDER_DRAFT_TITLE');
                    $notification['summary'] = JText::sprintf('VCM_AI_AUTO_RESPONDER_DRAFT_SUMMARY', $message->answer);
                } else {
                    // send the answer through the chat
                    $this->sendAnswer($chat, $thread, $message);

                    // complete notification details for the successful reply
                    $notification['type']    = 'message.reply.ok';
                    $notification['title']   = JText::_('VCM_AI_AUTO_RESPONDER_SUCCESS_TITLE');
                    $notification['summary'] = $message->answer;
                    $notification['label']   = JText::_('VCM_AI_AUTO_RESPONDER_BTN_SEE_REPLY');
                }
            } catch (Exception $error) {
                if ($error->getCode() == 423) {
                    // we received a 423 error (Locked), meaning that the customer prefers to
                    // talk with a human and doesn't want to receive further AI messages
                    $this->stopAutoResponder($thread->id_thread);
                } else if ($error->getCode() != 404) {
                    // we received an error code different than 404, revert the AI replied flag to retry
                    // elaborating an answer in a second time
                    $this->setMessageReplied($thread->id_message, false);
                }

                // complete notification details on failure
                $notification['type']    = 'message.reply.error';
                $notification['title']   = JText::_('VCM_AI_AUTO_RESPONDER_FAILURE_TITLE');
                $notification['summary'] = $error->getMessage();

                if ($error->getCode() == 404) {
                    // append to the notification summary the latest
                    // guest message for which an answer could not be found
                    foreach (array_reverse($messages) as $gmessage) {
                       if (!strcasecmp($gmessage['role'], 'user') && !empty($gmessage['content'])) {
                            $mlength = strlen($gmessage['content']);
                            $notification['summary'] .= "\n " . ($mlength < 192 ? '"' : '') . $gmessage['content'] . ($mlength < 192 ? '"' : '');
                            break;
                        }
                    }
                }
            }

            try {
                $notification['date'] = JFactory::getDate()->toISO8601();

                // register notification with the current date
                VBOFactory::getNotificationCenter()->store([$notification]);
            } catch (Throwable $error) {
                // notification center not supported
            }

            // do not process more than a message per execution
            return;
        }
    }

    /**
     * Checks whether the messaging auto-responder is enabled or not.
     * 
     * @return  bool  True if enabled, false otherwise.
     */
    protected function isEnabled()
    {
        return $this->settingsModel->isMessagingAutoResponderEnabled();
    }

    /**
     * Checks whether the AI should ignore the specified thread while looking
     * for an automatic answer.
     * 
     * @param   object  $thread  The thread details.
     * 
     * @return  bool    True if should be skipped, false otherwise.
     */
    protected function shouldSkip($thread)
    {
        if ($thread->ai_replied ?? null) {
            // skip in case the AI already replied to this message
            return true;
        }

        if ($thread->ai_stopped ?? null) {
            // skip in case the thread has been flagged to prevent auto-replies
            return true;
        }

        return false;
    }

    /**
     * Toggles the "AI replied" status for the provided message.
     * The auto-responder ignores the messages that have been marked as "AI replied".
     * 
     * @param   int   $messageId  The ID of the message to update.
     * @param   bool  $status     True in case it has been processed, false otherwise.
     * 
     * @return  void
     */
    protected function setMessageReplied(int $messageId, bool $status = true)
    {
        $msgUpdate = (object) [
            'id' => $messageId,
            'ai_replied' => (int) $status,
        ];

        JFactory::getDbo()->updateObject('#__vikchannelmanager_threads_messages', $msgUpdate, 'id');
    }

    /**
     * Toggles the "AI stopped" status for the provided thread.
     * The auto-responder ignores the threads that have been marked as "AI stopped".
     * 
     * @param   int   $threadId  The ID of the thread to update.
     * @param   bool  $status    True in case it has been stopped, false otherwise.
     * 
     * @return  void
     */
    protected function stopAutoResponder(int $threadId, bool $status = true)
    {
        $threadUpdate = (object) [
            'id' => $threadId,
            'ai_stopped' => (int) $status,
        ];

        JFactory::getDbo()->updateObject('#__vikchannelmanager_threads', $threadUpdate, 'id');
    }

    /**
     * Builds a list of thread messages by identifying the sender role.
     * 
     * @param   VCMChatHandler  $chat    The chat instance to load the thread conversation.
     * @param   object          $thread  An object holding the thread details.
     * 
     * @return  array  List of message contents and sender roles.
     */
    protected function getThreadMessages($chat, $thread)
    {
        $messages = [];

        // fetch the thread conversation (exclude any message wrote after the date time of the latest guest message found)
        $result = $chat->loadThreadsMessages(0, 20, $thread->id_thread, $thread->dt);
        
        // set up messages array for the AI service
        foreach ($result[0]->messages as $message) {
            // always add the message at the beginning because we received a list of messages from the
            // newest to the oldest and we need to send it to the AI service from the oldest to the newest
            array_unshift($messages, [
                'role'    => strcasecmp($message->sender_type, 'guest') ? 'assistant' : 'user',
                'content' => $message->content,
            ]);
        }

        return $messages;
    }

    /**
     * Asks the AI to elaborate an answer for the provided thread and messages.
     * 
     * @param   VCMChatHandler  $chat      The chat instance to load the thread conversation.
     * @param   object          $thread    An object holding the thread details.
     * @param   array           $messages  List of messages with related content and sender role.
     * 
     * @return  object  The AI response.
     * 
     * @throws  Exception
     */
    protected function fetchAnswer($chat, $thread, array $messages)
    {
        // fetch rooms assigned to this order
        $orderRooms = VikBooking::loadOrdersRoomsData($thread->idorder);

        // fetch user details
        $users = $chat->loadUsers();

        // set up AI additional instructions
        $options = [
            'id_order' => $thread->idorder,
            'id_listing' => $orderRooms[0]['idroom'] ?? null,
            'customer' => $users['Guest']['first_name'] ?? null,
            'owner' => $users['Hotel']['full_name'] ?? null,
        ];
            
        // ask the AI to continue the conversation
        $answer = (new VCMAiModelService)->answer($messages, $options);

        // check if we should append a signature for the AI to the end of the message
        if (($signature = $this->settingsModel->getMessagingSignature()) && stripos($answer->answer, $signature) === false) {
            $answer->answer = trim($answer->answer) . "\n" . $signature;
        }

        return $answer;
    }

    /**
     * Checks whether the system should create a draft instead of sending a direct reply.
     * 
     * @return  bool  True in case the draft should be created, false to send the reply.
     */
    protected function shouldCreateDraft()
    {
        // there must be at least 20 AI replies
        return !$this->settingsModel->canAutoRespond(20);
    }

    /**
     * Sends the answer generated by the AI to the user.
     * 
     * @param   VCMChatHandler  $chat    The chat instance to deliver the message.
     * @param   object          $thread  An object holding the thread details.
     * @param   object          $answer  An object holding the AI answer.
     * 
     * @return  void
     * 
     * @throws  Exception
     */
    protected function sendAnswer($chat, $thread, $answer)
    {
        $message = new VCMChatMessage($answer->answer);
        $message->set('idthread', (int) $thread->id_thread);
        $message->set('sender_name', 'AI');

        foreach ($answer->attachments as $attachment) {
            $message->addAttachment($attachment->url);
        }

        // dispatch the message to the guest through the OTA provider for this booking
        $sent = $chat->reply($message);

        if (!$sent) {
            // Something went wrong while sending the message.
            // Use 404 as code to prevent the system from fetching this thread at the next execution.
            throw new Exception($chat->getError(), 404);
        }
    }

    /**
     * Creates a draft for the administrator with the answer generated by the AI.
     * 
     * @param   object  $thread  An object holding the thread details.
     * @param   object  $answer  An object holding the AI answer.
     * 
     * @return  void
     * 
     * @throws  Exception
     */
    protected function createDraft($thread, $answer)
    {
        $attachments = $answer->attachments;

        if ($attachments) {
            // keep only the attachment file name
            $attachments = array_map(function($attachment) {
                return $attachment->filename;
            }, $attachments);
        }

        // create draft object
        $draft = new stdClass;
        $draft->idthread = $thread->id_thread;
        $draft->content = $answer->answer;
        $draft->attachments = json_encode($attachments ?: []);
        $draft->dt = JFactory::getDate()->toSql();

        // enqueue draft in the database
        JFactory::getDbo()->insertObject('#__vikchannelmanager_threads_drafts', $draft, 'id');
    }
}
