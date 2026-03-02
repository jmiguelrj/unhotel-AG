<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2023 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Defines a registry with a set of methods to handle the guest messaging features.
 * 
 * @since   1.8.20
 */
final class VCMChatMessaging extends JObject
{
    /**
     * @var     string
     */
    private $message = '';

    /**
     * @var     array   list of OTAs that require API messaging.
     */
    private $required_otas = [
        'airbnb',
        'airbnbapi',
    ];

    /**
     * @var     bool
     * 
     * @since   1.8.27
     */
    private $mark_previous_replied = true;

    /**
     * @var     array
     * 
     * @since   1.9
     */
    private $messageData = [];

    /**
     * Proxy for immediately accessing the object and bind data. By invoking
     * the objecy through this method, the dependencies will be loaded as well.
     * 
     * @param   array|object    $data   the optional data to bind.
     * 
     * @return  self
     */
    public static function getInstance($data = null)
    {
        // load dependencies
        static::loadDependencies();

        // bind data to the object instance
        return new static($data);
    }

    /**
     * Loads the necessary dependencies for the chat handler.
     * 
     * @return  void
     */
    public static function loadDependencies()
    {
        static $loaded = null;

        if ($loaded) {
            return;
        }

        $loaded = 1;

        // chat handler
        require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'chat' . DIRECTORY_SEPARATOR . 'handler.php';
        require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'httperror.php';
    }

    /**
     * Tells if a booking has got some guest messages.
     * 
     * @param   bool    $unread     true to count only the unread messages.
     * 
     * @return  int
     */
    public function countBookingGuestMessages($unread = false)
    {
        $booking_id = $this->get('id', 0);

        if (!$booking_id) {
            return 0;
        }

        return VCMChatHandler::countUnreadMessages($booking_id, $recipient = false, $unread);
    }

    /**
     * Loads the latest thread for the given booking ID (VikBooking or OTA) with guest messages.
     * 
     * @param   int|string  $bid    the VikBooking or OTA reservation ID.
     * @param   int         $start  query start offset.
     * @param   int         $limit  query limit offset.
     * 
     * @return  array               list of object records found, if any.
     */
    public function loadBookingGuestThreads($bid, $start = 0, $limit = 5)
    {
        $dbo = JFactory::getDbo();

        $threads = [];

        // inner join query for messages
        $m = $dbo->getQuery(true)
            ->select('MAX(' . $dbo->qn('dt') . ') AS ' . $dbo->qn('lastMessage'))
            ->select($dbo->qn('idthread'))
            ->from($dbo->qn('#__vikchannelmanager_threads_messages'))
            // we only want the messages from the guest
            ->where($dbo->qn('sender_type') . ' = ' . $dbo->q('guest'))
            ->group($dbo->qn('idthread'));

        // main query for threads and messages
        $q = $dbo->getQuery(true)
            ->select($dbo->qn(['t.idorder', 't.idorderota', 't.channel', 't.subject', 't.last_updated', 't.no_reply_needed']))
            ->select($dbo->qn('m.id', 'id_message'))
            ->select($dbo->qn('m.idthread', 'id_thread'))
            ->select($dbo->qn(['m.sender_type', 'm.dt', 'm.content', 'm.attachments', 'm.read_dt', 'm.replied']))
            ->from($dbo->qn('#__vikchannelmanager_threads_messages', 'm'));

        // filter by booking ID
        if (preg_match("/^[0-9]+$/", (string)$bid)) {
            // only numbers could be both website and OTA
            $q->where([
                $dbo->qn('t.idorder') . ' = ' . (int)$bid,
                $dbo->qn('t.idorderota') . ' = ' . $dbo->q($bid),
            ], $glue = 'OR');
        } else {
            // alphanumeric IDs can only belong to an OTA reservation
            $q->where($dbo->qn('t.idorderota') . ' = ' . $dbo->q($bid));
        }

        // join threads with latest messages
        $q->innerJoin(
            '(' . $m . ') AS ' . $dbo->qn('im')
            . ' ON ' . $dbo->qn('m.idthread') . ' = ' . $dbo->qn('im.idthread')
            . ' AND ' . $dbo->qn('m.dt') . ' = ' . $dbo->qn('im.lastMessage')
        )
        // inner join threads
        ->innerJoin(
            $dbo->qn('#__vikchannelmanager_threads', 't')
            . ' ON ' . $dbo->qn('t.id') . ' = ' . $dbo->qn('im.idthread')
        )
        // order by descending received date
        ->order($dbo->qn('m.dt') . ' DESC');

        $dbo->setQuery($q, $start, $limit);
        $records = $dbo->loadObjectList();

        if ($records) {
            /**
             * It is now safe and efficient to join booking and customer tables to avoid slow queries
             * on databases with large datasets and tens of thousands of records.
             */
            $threads = $this->joinMessageDetails($records);
        }

        return $threads;
    }

    /**
     * Loads the latest messages for the given booking ID (VikBooking or OTA).
     * 
     * @param   int|string  $bid    the VikBooking or OTA reservation ID.
     * @param   int         $start  query start offset.
     * @param   int         $limit  query limit offset.
     * 
     * @return  array               list of object records found, if any.
     * 
     * @since   1.9
     */
    public function loadBookingLatestMessages($bid, $start = 0, $limit = 5)
    {
        $dbo = JFactory::getDbo();

        $threads = [];

        // main query for threads and messages
        $q = $dbo->getQuery(true)
            ->select($dbo->qn(['t.idorder', 't.idorderota', 't.channel', 't.subject', 't.last_updated', 't.no_reply_needed']))
            ->select($dbo->qn('m.id', 'id_message'))
            ->select($dbo->qn('m.idthread', 'id_thread'))
            ->select($dbo->qn(['m.sender_type', 'm.dt', 'm.content', 'm.attachments', 'm.read_dt', 'm.replied']))
            ->from($dbo->qn('#__vikchannelmanager_threads_messages', 'm'));

        // filter by booking ID
        if (preg_match("/^[0-9]+$/", (string)$bid)) {
            // only numbers could be both website and OTA
            $q->where([
                $dbo->qn('t.idorder') . ' = ' . (int)$bid,
                $dbo->qn('t.idorderota') . ' = ' . $dbo->q($bid),
            ], $glue = 'OR');
        } else {
            // alphanumeric IDs can only belong to an OTA reservation
            $q->where($dbo->qn('t.idorderota') . ' = ' . $dbo->q($bid));
        }

        // inner join threads
        $q->leftJoin(
            $dbo->qn('#__vikchannelmanager_threads', 't')
            . ' ON ' . $dbo->qn('t.id') . ' = ' . $dbo->qn('m.idthread')
        )
        // order by descending received date
        ->order($dbo->qn('m.dt') . ' DESC');

        $dbo->setQuery($q, $start, $limit);
        $records = $dbo->loadObjectList();

        if ($records) {
            /**
             * It is now safe and efficient to join booking and customer tables to avoid slow queries
             * on databases with large datasets and tens of thousands of records.
             */
            $threads = $this->joinMessageDetails($records);
        }

        return $threads;
    }

    /**
     * Searches for thread messages according to filters and offsets.
     * 
     * @param   array  $filters    the VikBooking or OTA reservation ID.
     * @param   int    $start      query start offset.
     * @param   int    $limit      query limit offset.
     * 
     * @return  array              list of object records found, if any.
     * 
     * @throws  Exception
     * 
     * @since   1.8.27
     * @since   1.9.5  added support to additional filters, among which "ai_sort".
     */
    public function searchMessages(array $filters, $start = 0, $limit = 5)
    {
        $dbo = JFactory::getDbo();

        $threads = [];

        // inner join query for messages
        $m = $dbo->getQuery(true)
            ->select('MAX(' . $dbo->qn('dt') . ') AS ' . $dbo->qn('lastMessage'))
            ->select($dbo->qn('idthread'))
            ->from($dbo->qn('#__vikchannelmanager_threads_messages'))
            ->group($dbo->qn('idthread'));

        if (!strcasecmp(($filters['sender'] ?? ''), 'guest') || !($filters['sender'] ?? '')) {
            // we only want the messages from the guest (default filter)
            $m->where($dbo->qn('sender_type') . ' = ' . $dbo->q('guest'));
        } elseif (!strcasecmp(($filters['sender'] ?? ''), 'hotel')) {
            // we only want the messages from the hotel (not guest)
            $m->where($dbo->qn('sender_type') . ' != ' . $dbo->q('guest'));
        }

        // check for guest name filter
        if (($filters['guest_name'] ?? '')) {
            $m->where($dbo->qn('sender_name') . ' LIKE ' . $dbo->q('%' . $filters['guest_name'] . '%'));
        }

        // check for unread filter
        if (($filters['unread'] ?? null)) {
            $m->where($dbo->qn('read_dt') . ' IS NULL');
        }

        // check for message contains filter
        if (($filters['message'] ?? '')) {
            // select full-text match score
            $m->select('MATCH(' . $dbo->qn('content') . ') AGAINST(' . $dbo->q($filters['message']) . ') AS ' . $dbo->qn('relevance'));
            // add where statement to only include matches
            $m->where('MATCH(' . $dbo->qn('content') . ') AGAINST(' . $dbo->q($filters['message']) . ') > 0');
            // add order by match relevance
            $m->order($dbo->qn('relevance') . ' DESC');
        }

        // check for message from date filter
        if (($filters['fromdt'] ?? '')) {
            $m->where($dbo->qn('dt') . ' >= ' . $dbo->q(JDate::getInstance($filters['fromdt'])->toSql()));
        }

        // check for message to date filter
        if (($filters['todt'] ?? '')) {
            $m->where($dbo->qn('dt') . ' <= ' . $dbo->q(JDate::getInstance($filters['todt'])->toSql()));
        }

        // main query for threads and messages
        $q = $dbo->getQuery(true)
            ->select($dbo->qn(['t.idorder', 't.idorderota', 't.channel', 't.subject', 't.last_updated', 't.no_reply_needed']))
            ->select($dbo->qn('m.id', 'id_message'))
            ->select($dbo->qn('m.idthread', 'id_thread'))
            ->select($dbo->qn(['m.sender_type', 'm.dt', 'm.content', 'm.attachments', 'm.read_dt', 'm.replied']))
            ->from($dbo->qn('#__vikchannelmanager_threads_messages', 'm'));

        // join threads with latest messages
        $q->innerJoin(
            '(' . $m . ') AS ' . $dbo->qn('im')
            . ' ON ' . $dbo->qn('m.idthread') . ' = ' . $dbo->qn('im.idthread')
            . ' AND ' . $dbo->qn('m.dt') . ' = ' . $dbo->qn('im.lastMessage')
        )
        // inner join threads
        ->innerJoin(
            $dbo->qn('#__vikchannelmanager_threads', 't')
            . ' ON ' . $dbo->qn('t.id') . ' = ' . $dbo->qn('im.idthread')
        )
        // order by descending received date
        ->order($dbo->qn('m.dt') . ' DESC');

        $dbo->setQuery($q, $start, $limit);
        $records = $dbo->loadObjectList();

        /**
         * Allow to sort the messages by priority through the AI model service.
         * 
         * @since   1.9.5
         */
        if ($records && ($filters['ai_sort'] ?? null)) {
            try {
                // sort messages by priority through AI
                $records = $this->sortMessagesThroughAI($records);
            } catch (Exception $error) {
                // propagate the error caught
                throw $error;
            }
        }

        if ($records) {
            /**
             * It is now safe and efficient to join booking and customer tables to avoid slow queries
             * on databases with large datasets and tens of thousands of records.
             */
            $threads = $this->joinMessageDetails($records, $logos = true);
        }

        return $threads;
    }

    /**
     * Given a list of thread messages, returns a list
     * inclusive of booking and customer details.
     * 
     * @param   array   $records    list of objects fetched from the database.
     * @param   bool    $logos      true for attempting to set the channel logo.
     * 
     * @return  array               enhanced list of thread-message objects.
     * 
     * @since   1.8.27
     */
    protected function joinMessageDetails(array $records, $logos = false)
    {
        $dbo = JFactory::getDbo();

        $threads = [];

        $orderIds = array_map(function($t) {
            return $t->idorder;
        }, $records);

        $dbo->setQuery(
            $dbo->getQuery(true)
                ->select($dbo->qn('o.id', 'idorder'))
                ->select($dbo->qn('o.status', 'b_status'))
                ->select($dbo->qn('o.days', 'b_nights'))
                ->select($dbo->qn('o.checkin', 'b_checkin'))
                ->select($dbo->qn('o.checkout', 'b_checkout'))
                ->select($dbo->qn('c.id', 'id_customer'))
                ->select($dbo->qn(['c.first_name', 'c.last_name', 'c.pic']))
                ->from($dbo->qn('#__vikbooking_orders', 'o'))
                ->leftjoin($dbo->qn('#__vikbooking_customers_orders', 'co') . ' ON ' . $dbo->qn('o.id') . ' = ' . $dbo->qn('co.idorder'))
                ->leftjoin($dbo->qn('#__vikbooking_customers', 'c') . ' ON ' . $dbo->qn('co.idcustomer') . ' = ' . $dbo->qn('c.id'))
                ->where($dbo->qn('o.id') . ' IN (' . implode(',', array_unique($orderIds)) . ')')
        );

        $orderDetails = [];

        foreach ($dbo->loadObjectList() as $details) {
            $orderDetails[$details->idorder] = $details;
        }

        $cached_logos = [];

        foreach ($records as $thread) {
            // JSON decode attachments
            $thread->attachments = $thread->attachments ? (array) json_decode($thread->attachments) : [];

            // set additional properties
            if (isset($orderDetails[$thread->idorder])) {
                foreach ($orderDetails[$thread->idorder] as $key => $value) {
                    $thread->{$key} = $value;
                }
            }

            // set channel logo URL
            if ($logos && !empty($thread->channel) && empty($thread->channel_logo)) {
                if (isset($cached_logos[$thread->channel])) {
                    $thread->channel_logo = $cached_logos[$thread->channel];
                } else {
                    $channel_logo = VikChannelManager::getLogosInstance($thread->channel)->getSmallLogoURL();
                    if (!empty($channel_logo)) {
                        $cached_logos[$thread->channel] = $channel_logo;
                        $thread->channel_logo = $channel_logo;
                    }
                }
            }

            $threads[] = $thread;
        }

        return $threads;
    }

    /**
     * Given a list of guest message records, sorts them by priority
     * through the AI model service by eventually setting for each
     * guest message the priority enum and category.
     * 
     * @param   objects[]   $records    List of guest messages.
     * 
     * @return  objects[]               The original list, sorted.
     * 
     * @throws  Exception
     * 
     * @since   1.9.5
     */
    protected function sortMessagesThroughAI(array $records)
    {
        // maximum default priority
        $max_priority = count($records);

        // set natural priority to each record
        foreach ($records as $n => &$record) {
            $record = (object) $record;
            $record->sort_priority = $max_priority - $n;
        }

        // unset last reference
        unset($record);

        // prepare a compact list of tasks (guest messages) to be sorted
        $tasks = [];

        foreach ($records as $record) {
            // push message as a compact task for sorting
            $tasks[] = [
                'id' => $record->id_message,
                'type' => 'guest_message',
                'content' => $record->content,
            ];
        }

        try {
            // let the AI model service sort the tasks by priority
            $tasks = (new VCMAiModelService)->prioritySort($tasks);
        } catch (Exception $error) {
            // propagate the error caught
            throw $error;
        }

        // update sort priority accordingly
        foreach ($tasks as $n => $task) {
            // cast sorted task to object
            $task = (object) $task;

            // calculate the new priority
            $calc_priority = $max_priority - $n;

            // attempt to find the message ID
            foreach ($records as $k => $record) {
                if ($record->id_message == ($task->id ?? 0)) {
                    // update message priority
                    $records[$k]->sort_priority = $calc_priority;

                    // check for the AI calculated message priority
                    if ($task->ai_priority ?? null) {
                        $records[$k]->ai_priority = (string) $task->ai_priority;
                    }

                    // check for the AI calculated message category
                    if ($task->ai_category ?? null) {
                        $records[$k]->ai_category = (string) $task->ai_category;
                    }

                    // go to next sorted task
                    break;
                }
            }
        }

        // sort records by priority
        usort($records, function($a, $b) {
            // sort by priority descending
            return $b->sort_priority - $a->sort_priority;
        });

        // unset sort_priority property
        foreach ($records as &$record) {
            unset($record->sort_priority);
        }

        // unset last reference
        unset($record);

        // return the sorted list by resetting the keys
        return array_values($records);
    }

    /**
     * Normalizes the threads messages to ensure the guest messages are
     * marked as "replied" in case the Hotel sent a reply afterwards.
     * 
     * NOTICE: this method does not take into account auto-responder messages.
     * Should only be launched once after the update to VCM 1.8.27 that introduced
     * the new "replied" column in the table '#__vikchannelmanager_threads_messages'.
     * 
     * @param   bool    $set_full_text  true to add the Full-Text index to messages "content".
     * 
     * @return  int     number of rows affected by the update.
     * 
     * @since   1.8.27
     */
    public function updateRepliedGuestMessages($set_full_text = true)
    {
        $dbo = JFactory::getDbo();

        if ($set_full_text) {
            // add the full-text index to the messages "content" column to facilitate the search
            try {
                // prevent SQL errors
                $dbo->setQuery("ALTER TABLE `#__vikchannelmanager_threads_messages` ADD FULLTEXT(`content`)");
                $dbo->execute();
            } catch(Exception $e) {
                // do nothing
            }
        }

        $affected_rows = 0;

        $dbo->setQuery(
            $dbo->getQuery(true)
                ->select($dbo->qn('id'))
                ->from($dbo->qn('#__vikchannelmanager_threads'))
                ->order($dbo->qn('id') . ' ASC')
        );

        $thread_ids = array_column($dbo->loadAssocList(), 'id');

        foreach ($thread_ids as $thread_id) {
            // fetch date of the most recent message for the guest on this thread
            $q = $dbo->getQuery(true)
                ->select($dbo->qn('dt'))
                ->from($dbo->qn('#__vikchannelmanager_threads_messages'))
                ->where($dbo->qn('idthread') . ' = ' . $dbo->q($thread_id))
                ->where($dbo->qn('recip_type') . ' = ' . $dbo->q('guest'))
                ->order($dbo->qn('dt') . ' DESC');

            $dbo->setQuery($q, 0, 1);
            $guest_last_reply_dt = $dbo->loadResult();

            if (!$guest_last_reply_dt) {
                // no hotel messages on this thread
                continue;
            }

            // update all guest messages on this thread with a previous date
            $q = $dbo->getQuery(true)
                ->update($dbo->qn('#__vikchannelmanager_threads_messages'))
                ->set($dbo->qn('replied') . ' = 1')
                ->where($dbo->qn('idthread') . ' = ' . $dbo->q($thread_id))
                ->where($dbo->qn('sender_type') . ' = ' . $dbo->q('guest'))
                ->where($dbo->qn('dt') . ' < ' . $dbo->q($guest_last_reply_dt));

            $dbo->setQuery($q);
            $dbo->execute();

            $affected_rows += (int) $dbo->getAffectedRows();
        }

        return $affected_rows;
    }

    /**
     * Tells if the current OTA booking supports/requires messaging through their APIs.
     * 
     * @param   bool    $mandatory  true to check if API messaging is mandatory.
     * 
     * @return  bool
     */
    public function supportsOtaMessaging($mandatory = false)
    {
        $ota_bid = $this->get('idorderota', '');
        $channel = $this->get('channel', '');

        if (empty($ota_bid) || empty($channel)) {
            return false;
        }

        $channel_parts = explode('_', $channel);
        $ota_name      = strtolower(trim($channel_parts[0]));

        /**
         * Ensure "booking.com_Booking.com" becomes "bookingcom" to match the driver file name.
         * 
         * @since   1.8.27
         */
        $ota_name = preg_replace("/[^a-z0-9]/i", '', $ota_name);

        $supported = is_file(implode(DIRECTORY_SEPARATOR, [VCM_SITE_PATH, 'helpers', 'chat', 'channels', "{$ota_name}.php"]));
        $required  = $supported && in_array($ota_name, $this->required_otas);

        /**
         * Trigger event to allow third-party plugins to manipulate the
         * supported or required information about the current channel.
         * 
         * @since   1.8.24
         */
        if ($supported && $mandatory && !$required) {
            $required = (bool) VCMFactory::getPlatform()->getDispatcher()->filter('onCheckChannelOtaMessagingRequired', [$this, $ota_name]);
        }

        return $mandatory ? $required : $supported;
    }

    /**
     * Attempts to send a message to the guest through the related OTA messaging API feature.
     * 
     * @param   string  $type   either "send" (default) or "reply".
     * 
     * @return  bool    true on success, false otherwise by setting errors.
     * 
     * @since   1.8.27  added argument $type.
     */
    public function sendGuestMessage($type = 'send')
    {
        if (!$this->supportsOtaMessaging($mandatory = false)) {
            $this->setError('Booking channel does not support guest messaging APIs');
            return false;
        }

        $chat = VikBooking::getVcmChatInstance($this->get('id', 0), $this->get('channel', ''));
        if (!$chat) {
            $this->setError('Could not get an instance for the messaging object');
            return false;
        }

        // get the message content
        $message = $this->getMessage();
        if (empty($message)) {
            $this->setError('Empty message content');
            return false;
        }

        // validate the the type for sending the message
        if (!in_array($type, ['send', 'reply'])) {
            // default to "send"
            $type = 'send';
        }

        // make sure the message content format is plain text
        $message = $this->convertToPlainText($message);

        // update the message property with the adjusted value
        $this->setMessage($message);

        // build the chat message object
        $chat_message = new VCMChatMessage(
            // message content
            $message,
            // no attachments
            [],
            // data to bind
            array_merge([
                'mark_previous_replied' => $this->markPreviousReplied(),
            ], $this->getMessageData())
        );

        // set the thread ID to ensure the message gets delivered
        if ($type === 'reply') {
            // the idthread is mandatory in case of a reply
            $chat_message->set('idthread', $this->get('idthread'));
        }

        // dispatch the message to the guest through the OTA provider for this booking
        $result = $chat->{$type}($chat_message);

        if (!$result) {
            $this->setError($chat->getError());
            return false;
        }

        return true;
    }

    /**
     * Given the injected thread properties, checks if the reservation record
     * contains the information about the OTA thread ID to speed up subsequent
     * calls for downloading the thread messages. Most OTAs only support one
     * thread ID per reservation (excluding Booking.com).
     * 
     * @return  bool    true if the booking record was assigned to an OTA thread ID.
     * 
     * @since   1.8.27
     */
    public function storeBookingThread()
    {
        $booking_id    = $this->get('idorder', '');
        $ota_thread_id = $this->get('ota_thread_id', '');

        if (!$booking_id || !$ota_thread_id) {
            $this->setError('Missing mandatory thread properties');
            return false;
        }

        $booking = VikBooking::getBookingInfoFromID($booking_id);
        if (!$booking) {
            $this->setError('Booking record not found');
            return false;
        }

        $ota_type_data = json_decode(($booking['ota_type_data'] ?: '[]'), true);
        $ota_type_data = is_array($ota_type_data) ? $ota_type_data : [];

        if (!empty($ota_type_data['thread_id'])) {
            // do not overwrite the existing OTA thread ID
            return false;
        }

        // set the OTA thread ID value
        $ota_type_data['thread_id'] = $ota_thread_id;

        // update the booking information record
        $dbo = JFactory::getDbo();

        $record = new stdClass;
        $record->id = $booking['id'];
        $record->ota_type_data = json_encode($ota_type_data);

        return (bool) $dbo->updateObject('#__vikbooking_orders', $record, 'id');
    }

    /**
     * Gets the current message value.
     * 
     * @return  string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets the message value for evaluation.
     * 
     * @param   string  $message    the message string.
     * 
     * @return  self
     */
    public function setMessage(string $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Gets the current message data.
     * 
     * @return  array
     * 
     * @since   1.9
     */
    public function getMessageData()
    {
        return $this->messageData;
    }

    /**
     * Sets the message data to bind to the chat message.
     * 
     * @param   array  $data    The message data to bind.
     * 
     * @return  self
     * 
     * @since   1.9
     */
    public function setMessageData(array $data)
    {
        $this->messageData = $data;

        return $this;
    }

    /**
     * Tells or sets if previous guest messages should be marked as "replied".
     * 
     * @param   bool|null  $enabled   optionally set if marking or not.
     * 
     * @return  bool                  the current flag status.
     * 
     * @since   1.8.27
     */
    public function markPreviousReplied($enabled = null)
    {
        if (is_bool($enabled)) {
            $this->mark_previous_replied = $enabled;
        }

        return $this->mark_previous_replied;
    }

    /**
     * Converts a message content to plain text.
     * 
     * @param   string  $message    the message content.
     * 
     * @return  string
     */
    public function convertToPlainText(string $message)
    {
        // convert all break tags into new lines
        $message = preg_replace("/<\/?br\/?>/", "\n", $message);

        // get rid of all tags except links for now
        $allowed_tags = version_compare(PHP_VERSION, '7.4') >= 0 ? ['a'] : '<a>';
        $message = strip_tags($message, $allowed_tags);

        // convert tabs into white spaces
        $message = preg_replace("/\t/", ' ', $message);

        // get rid of new line characters used more than twice in a row
        $message = preg_replace("/[\r\n]{3,}/", "\n\n", $message);

        // get rid of white space characters (only white space, no new lines or tabs like "\s") used more than once in a row
        $message = preg_replace("/ {2,}/", ' ', $message);

        // get rid of ending new lines
        $message = preg_replace("/[\r\n]+$/", '', $message);

        // get all link URIs and tags
        preg_match_all("/href=\"(.+)\"/", $message, $hrefs);
        preg_match_all("/<a .+>(.+)<\/a>/", $message, $atags);

        if (!$hrefs || empty($hrefs[1]) || !$atags || empty($atags[1]) || count($hrefs[1]) != count($atags[1])) {
            // no link tags or malformed HTML, make sure to strip all anchor tags as well
            return strip_tags($message);
        }

        // parse what's inside each <a> tag to make sure links do not get lost
        foreach ($atags[1] as $ak => $anchor_content) {
            if (!preg_match("/^http/", $anchor_content)) {
                // the anchor tag does not contain a link, so we replace it with the href attribute
                if (isset($hrefs[1][$ak]) && preg_match("/^http/", $hrefs[1][$ak])) {
                    // replace the whole <a></a> tag with just the href attribute
                    $message = str_replace($atags[0][$ak], $hrefs[1][$ak], $message);
                }
            }
        }

        // get rid of the rest of the anchor tags with a proper URI as content
        return strip_tags($message);
    }
}
