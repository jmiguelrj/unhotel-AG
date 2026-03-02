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
 * AI service model.
 * 
 * @since 1.9
 */
class VCMAiModelService extends VCMAiModelAware
{
    /**
     * Translates the specified text into the given locale.
     * 
     * @param   string  $text    The text to translate.
     * @param   string  $locale  The platform locale (eg. en-US).
     * 
     * @return  string  The translated text.
     * 
     * @throws  Exception
     */
    public function translate(string $text, string $locale = '')
    {
        if (!$locale) {
            // use the current locale if not provided
            $locale = JFactory::getLanguage()->getTag();
        }

        $data = [
            'content' => $text,
            'language' => $locale,
        ];

        $supportedLanguages = VikBooking::getVboApplication()->getKnownLanguages();

        // check whether the specified locale exists
        if (isset($supportedLanguages[$locale])) {
            // use the name of the locale
            $data['language'] = $supportedLanguages[$locale]['name'];
        }

        $response = (new JHttp)->post($this->getEndPoint('services/translate'), json_encode($data), $this->getHeaders([
            'Content-Type' => 'application/json',
        ]), $this->timeout);

        if ($response->code != 200) {
            throw new Exception($response->body, $response->code);
        }

        return json_decode($response->body)->translation;
    }

    /**
     * Tries to answer to the specified text.
     * 
     * @param   string|array  $messages  The conversation so far.
     * @param   array         $options   A configuration array.
     * 
     * @return  object        An object holding the answer and the attachments list.
     * 
     * @throws  Exception
     */
    public function answer($messages, array $options = [])
    {
        $data = [
            'messages' => (array) $messages,
            'options'  => $options,
        ];

        if (!empty($options['id_order'])) {
            $data['id_order'] = (int) $options['id_order'];
        }

        if (!empty($options['id_listing'])) {
            $data['id_listing'] = (int) $options['id_listing'];
        } else if (!empty($options['id_order'])) {
            // use the first listing assigned to the specified order
            $orderRooms = VikBooking::loadOrdersRoomsData($options['id_order']);
            $data['id_listing'] = $data['options']['id_listing'] = $orderRooms[0]['idroom'] ?? null;
        }

        if (isset($options['test'])) {
            $data['test'] = (bool) $options['test'];
            unset($data['test']);
        }

        $response = (new JHttp)->post($this->getEndPoint('services/reply'), json_encode($data), $this->getHeaders([
            'Content-Type' => 'application/json',
        ]), $this->timeout);

        if ($response->code != 200) {
            throw new Exception($response->body, $response->code);
        }

        $body = json_decode($response->body);

        // get rid of markdown syntax
        $body->answer = (new VCMAiHelperMarkdown($body->answer))->toText();

        // convert a list of file names into a list of attachment objects
        $body->attachments = array_map([new VCMAiModelTraining, 'getAttachment'], $body->attachments);

        // get rid of missing attachments
        $body->attachments = array_values(array_filter($body->attachments));

        return $body;
    }

    /**
     * Talks with the AI assistant.
     * 
     * @param   object[]    $messages  The conversation so far.
     * @param   ?string     $threadId  An optional thread ID.
     * @param   ?string     $scope     An optional scope of the assistant (concierge, writer).
     * 
     * @return  object      An object holding the answer of the AI.
     * 
     * @throws  Exception
     * 
     * @since   1.9.5  Added the $scope argument.
     */
    public function assistant(array $messages, ?string $threadId = null, ?string $scope = null)
    {
        if (!$threadId) {
            // thread ID not provided, generate a new one
            $threadId = VikChannelManager::uuid();
        }

        if (count($messages))
        {
            // Check whether the last message contains some attachments.
            // If this is the case, detach them and move them on the parent node.
            $lastMessage = end($messages);
            $attachments = $lastMessage['attachments'] ?? [];
            unset($messages[count($messages) - 1]['attachments']);
        }

        $data = [
            'thread_id' => $threadId,
            'messages' => $messages,
            'attachments' => $attachments ?? null,
            'scope' => $scope,
            // generate a (probable) unique ID to check after the request whether
            // an AI tool generated some widgets to display below the message
            'uuid' => VikChannelManager::uuid(),
        ];
   
        // use an higher timeout for this request
        $response = (new JHttp)->post($this->getEndPoint('services/assistant'), json_encode($data), $this->getHeaders([
            'Content-Type' => 'application/json',
        ]), $this->timeout);

        if ($response->code != 200) {
            throw new Exception($response->body, $response->code);
        }

        $answer = json_decode($response->body);

        // convert markdown into HTML
        $markdown = new VCMAiHelperMarkdown($answer->result);
        $answer->result = $markdown->toHtml();
        $answer->text = $markdown->toText();

        $config = VCMFactory::getConfig();

        // extract add-on registered for this request
        $addon = (new VCMAiAssistantHelper($data['uuid']))->getAddon();

        if ($addon) {
            // inject add-on HTML within the assistant response
            $answer->addon = $addon;
        }

        // inject thread ID within the response
        $answer->threadId = $threadId;

        return $answer;
    }

    /**
     * Creates a new review content depending on the provided information.
     * 
     * @param   array   $args  An associative array of arguments.
     * 
     * @return  string  The generated review.
     * 
     * @throws  Exception
     */
    public function review(array $args)
    {
        if (!empty($args['language'])) {
            $supportedLanguages = VikBooking::getVboApplication()->getKnownLanguages();

            // check whether the specified locale exists
            if (isset($supportedLanguages[$args['language']])) {
                // use the name of the locale
                $args['language'] = $supportedLanguages[$args['language']]['name'];
            }
        }

        // fetch listing ID on the fly if we only have the order ID
        if (!empty($args['id_order']) && empty($args['id_listing'])) {
            $roomsData = VikBooking::loadOrdersRoomsData((int) $args['id_order']);
            $args['id_listing'] = $roomsData[0]['id'] ?? null;
        }

        $response = (new JHttp)->post($this->getEndPoint('services/review'), json_encode($args), $this->getHeaders([
            'Content-Type' => 'application/json',
        ]), $this->timeout);

        if ($response->code != 200) {
            throw new Exception($response->body, $response->code);
        }

        return json_decode($response->body)->review;
    }

    /**
     * Extract the topics from the provided messages.
     * 
     * @param   string|array  $messages  Either a message or a list.
     * 
     * @return  string[]  The topics for each message.
     * 
     * @throws  Exception
     */
    public function extractTopics($messages)
    {
        $data = [
            'prompt'   => 'Extract a list of topics (in English) from the provided questions.',
            'messages' => (array) $messages,
        ];

        $response = (new JHttp)->post($this->getEndPoint('services/prompt'), json_encode($data), $this->getHeaders([
            'Content-Type' => 'application/json',
        ]), $this->timeout);

        if ($response->code != 200) {
            throw new Exception($response->body, $response->code);
        }

        // The AI might return the topics as a bullet list or a dashed list.
        // Manually convert it into an array of strings
        $topics = preg_split("/(^|\R+)(-|\d+[.\-\)])?\s*/", json_decode($response->body)->result);
        $topics = array_map('trim', $topics);

        // get rid of empty values and other phrases returned by OpenAI,
        // such as "Topics extracted from the provided questions:"
        return array_values(array_filter($topics, function($topic) {
            return $topic && !preg_match("/[.:]$/", $topic);
        }));
    }

    /**
     * Sorts an objects list by priority.
     * 
     * @param   array   $tasks  The list of objects or assoc arrays to sort.
     * 
     * @return  array           The priority sorted objects list.
     * 
     * @throws  Exception
     * 
     * @since   1.9.5
     */
    public function prioritySort(array $tasks)
    {
        // filter out invalid objects or associative arrays
        $tasks = array_filter($tasks, function($task) {
            if (!is_object($task) && !is_array($task)) {
                return false;
            }

            $task = (array) $task;

            return !empty($task['content']);
        });

        if (!$tasks) {
            // invalid argument
            throw new InvalidArgumentException('Invalid tasks argument', 400);
        }

        $response = (new JHttp)->post($this->getEndPoint('services/priority-sort'), json_encode(['tasks' => array_values($tasks)]), $this->getHeaders([
            'Content-Type' => 'application/json',
        ]), $this->timeout);

        if ($response->code != 200) {
            throw new Exception($response->body, $response->code);
        }

        return (array) json_decode($response->body)->result;
    }

    /**
     * Generates the content for a room from the given information.
     * 
     * @param   string   $type          The type of room content to generate.
     * @param   string   $information   The room keywords or informational text.
     * @param   string   $language      Optional language for the generation.
     * 
     * @return  string                  The generated room content.
     * 
     * @throws  Exception
     * 
     * @since   1.9.5
     */
    public function roomContent(string $type, string $information, string $language = '')
    {
        if (!$type || !$information) {
            // invalid argument
            throw new InvalidArgumentException('Missing description type or content information', 400);
        }

        $body = [
            'type' => $type,
            'information' => $information,
            'language' => $language,
        ];

        $response = (new JHttp)->post($this->getEndPoint('services/room-content'), json_encode($body), $this->getHeaders([
            'Content-Type' => 'application/json',
        ]), $this->timeout);

        if ($response->code != 200) {
            throw new Exception($response->body, $response->code);
        }

        return json_decode($response->body)->result;
    }

    /**
     * Extract the appropriate task manager tags from the provided information.
     * 
     * @param   array   $options    Associative list of task data options.
     * 
     * @return  array               List of appropriate tags map found, if any.
     * 
     * @throws  Exception
     * 
     * @since   1.9.10
     */
    public function extractTaskTags(array $options)
    {
        // access the current task information, if any
        $task = VBOTaskTaskregistry::getRecordInstance($options['task_id'] ?? 0);

        // try to access the current booking details
        $booking = $task->getBookingId() ? VikBooking::getBookingInfoFromID($task->getBookingId()) : (($options['booking_id'] ?? 0) ? VikBooking::getBookingInfoFromID($options['booking_id']) : []);

        // try to access the rooms booking information
        $bookingRooms = $booking ? VikBooking::loadOrdersRoomsData($booking['id']) : [];

        // access the current area/project ID
        $areaId = $task->getAreaID() ?: $options['area_id'] ?? 0;

        // access area and related tags
        $taskArea     = $areaId ? VBOTaskArea::getRecordInstance($areaId) : null;
        $taskAreaTags = $taskArea ? $taskArea->getTagRecords() : [];

        if (!$taskAreaTags) {
            return [];
        }

        // build the information message
        $messageDetails = [];

        if ($booking) {
            // push the involved booking details, when available
            $messageDetails[] = 'Check-in date: ' . date('Y-m-d H:i', $booking['checkin']);
            $messageDetails[] = 'Check-out date: ' . date('Y-m-d H:i', $booking['checkout']);
            $messageDetails[] = 'Adults: ' . array_sum(array_column($bookingRooms, 'adults'));
            $messageDetails[] = 'Children: ' . array_sum(array_column($bookingRooms, 'children'));
            $messageDetails[] = 'Channel source: ' . ($booking['channel'] ?: 'website');

            // build the extra (services) costs list
            foreach ($bookingRooms as $bookingRoom) {
                if ($bookingRoom['idroom'] != $task->getListingId() && $bookingRoom['idroom'] != ($options['listing_id'] ?? 0)) {
                    continue;
                }
                if (!empty($bookingRoom['extracosts'])) {
                    $messageDetails[] = 'Extra services: ' . (is_array($booking['extracosts']) || is_object($booking['extracosts']) ? json_encode($booking['extracosts']) : $booking['extracosts']);
                    break;
                }
            }
        }

        // push the relevant task details
        $messageDetails[] = 'Task project: ' . $taskArea->getName();
        $messageDetails[] = 'Task title: ' . (($options['task_title'] ?? '') ?: $task->getTitle());
        if ($task->getID()) {
            $messageDetails[] = 'Task due date: ' . $task->getDueDate(true, '');
        }
        $messageDetails[] = 'Task notes: ' . (($options['task_notes'] ?? '') ?: $task->getNotes());

        // determine the context
        $context = $booking ? '(accommodation) booking details' : 'task details';

        // build request data payload
        $data = [
            'prompt'   => 'Extract a list of appropriate tags from the provided ' . $context . ', by using the supported tags only. ' .
                          'Return the appropriate tags as a JSON list of strings. Do not use markdown.' . "\n" .
                          'Supported tags: ' . implode(', ', array_column($taskAreaTags, 'name')),
            'messages' => [implode("\n", $messageDetails)],
            'format'   => 'json',
        ];

        $response = (new JHttp)->post($this->getEndPoint('services/prompt'), json_encode($data), $this->getHeaders([
            'Content-Type' => 'application/json',
        ]), $this->timeout);

        if ($response->code != 200) {
            throw new Exception($response->body, $response->code);
        }

        $tagsMap = [];
        $appropriateTags = (array) json_decode(json_decode($response->body)->result);

        foreach ($appropriateTags as $tagName) {
            foreach ($taskAreaTags as $taskAreaTag) {
                if (!strcasecmp($taskAreaTag->name, $tagName)) {
                    $tagsMap[] = (array) $taskAreaTag;
                }
            }
        }

        return $tagsMap;
    }

    /**
     * Processes the given conversation in search of maintenance tasks.
     * 
     * @param   string[]     $messages  The conversation so far.
     * @param   int          $roomId    The booking listing ID.
     * 
     * @return  object|null  The task object when found, null otherwise.
     * 
     * @throws  Exception
     * 
     * @since   1.9.14
     */
    public function extractTasks(array $messages, int $roomId)
    {
        // get all configured areas
        $areas = array_map(function($area) {
            return VBOTaskArea::getInstance((array) $area);
        }, VBOFactory::getTaskManager()->getAreas());

        // obtain all the eligible areas for the booking room
        $areas = array_values(array_filter($areas, function($area) use ($roomId) {
            // make sure the area supports AI capabilities
            if (!$area->isAiCapable()) {
                return false;
            }

            // make sure the booking room is supported by this area
            return !$area->getListingIds() || in_array($roomId, $area->getListingIds());
        }));

        if (!$areas) {
            // no eligible areas, immediately abort
            return;
        }

        $areasLookup = $projects = [];

        // create a reverse lookup to easily obtain the area ID from the name
        foreach ($areas as $area) {
            // create a safe alias from the area name
            $alias = JFilterOutput::stringURLSafe($area->getName());

            $areasLookup[$alias] = $area->getID();

            $projects[] = [
                'name' => $alias,
                'description' => $area->get('comments', ''),
            ];
        }

        $tagsLookup = [];

        // take all the supported tags for each area
        foreach ($areas as $area) {
            foreach ($area->getTagRecords() as $tag) {
                // create a safe alias from the tag name
                $alias = JFilterOutput::stringURLSafe($tag->name);

                // create reverse lookup to easily obtain the ID from the name
                $tagsLookup[$alias] = $tag->id;
            }
        }

        // set up post payload
        $data = [
            'messages' => $messages,
            'projects' => $projects,
            'tags' => array_keys($tagsLookup),
        ];

        // make HTTP request to extract the tasks from the conversation
        $response = (new JHttp)->post($this->getEndPoint('services/taskmanager'), json_encode($data), $this->getHeaders([
            'Content-Type' => 'application/json',
        ]), $this->timeout);

        if ($response->code != 200) {
            throw new Exception($response->body, $response->code);
        }

        // decode task from response body
        $body = json_decode($response->body)->result ?? null;

        /**
         * Prevent response format hallucinations, as we realized that in some cases the AI
         * returns an empty list, even if it should actually contain some tasks.
         * So, in case both summary and project are filled in, simulate a runtime fix.
         */
        // if (empty($body->tasks) && !empty($body->summary) && !empty($body->project)) {
        //     $body->tasks = [
        //         (object) [
        //             // include all the guest messages
        //             'trigger' => implode("<br>", $messages),
        //             // replicate task summary
        //             'task' => $body->summary,
        //         ],
        //     ];
        // }

        if (empty($body->tasks)) {
            // no maintenance tasks found within the conversation
            return;
        }

        // create task registry
        $task = new stdClass;
        $task->ai = 1;

        // use the most appropriate area detected by the AI (fallback to the first available one)
        $task->id_area = $areasLookup[$body->project ?? null] ?? reset($areasLookup);

        // insert a title for the task
        $task->title = $body->summary ?? 'Maintenance task';

        // apply a reverse-lookup to the tags to obtain the ID from the name
        $task->tags = array_map(function($tag) use ($tagsLookup) {
            return $tagsLookup[$tag] ?? null;
        }, $body->tags ?? []);

        // get rid of empty values
        $task->tags = array_values(array_filter($task->tags));

        // create HTML template with checkmarks list
        $task->notes = implode('', array_map(function($item) {
            $text = '<ul data-checked="false"><li>' . ($item->task ?? 'to do') . '</li></ul>';

            if (!empty($item->trigger)) {
                $text = '<blockquote>' . $item->trigger . '</blockquote>' . $text;
            }
            
            return $text;
        }, $body->tasks));

        return $task;
    }

    /**
     * Processes the given conversation to elaborate a list of articles
     * eligible for the learning knowledge base.
     * 
     * @param   array     $messages  The conversation so far.
     * 
     * @return  object[]  A list of articles for the knowledge base.
     * 
     * @throws  Exception
     * 
     * @since   1.9.16
     */
    public function learn(array $messages)
    {
        // set up post payload
        $data = [
            'messages' => $messages,
        ];

        // make HTTP request to extract the articles from the conversation
        $response = (new JHttp)->post($this->getEndPoint('services/learn'), json_encode($data), $this->getHeaders([
            'Content-Type' => 'application/json',
        ]), $this->timeout);

        if ($response->code != 200) {
            throw new Exception($response->body, $response->code);
        }

        // decode articles from response body
        return json_decode($response->body)->result ?? [];
    }

    /**
     * Processes the given list of image URLs to detect and extract MRZ data.
     * 
     * @param   array     $imageUrls  List of image document string URLs to process.
     * 
     * @return  ?object   MRZ data extracted (data) and check-digit validity (valid).
     * 
     * @throws  Exception
     * 
     * @since   1.9.16
     */
    public function mrzDetection(array $imageUrls)
    {
        // set up post payload
        $data = [
            'image' => $imageUrls,
        ];

        // make HTTP request to detect and extract MRZ data from the given image URL(s)
        $response = (new JHttp)->post($this->getEndPoint('services/mrz'), json_encode($data), $this->getHeaders([
            'Content-Type' => 'application/json',
        ]), $this->timeout);

        if ($response->code != 200) {
            throw new Exception($response->body, $response->code);
        }

        // decode result properties from response body ("data" and "valid" expected)
        return json_decode($response->body)->result ?? null;
    }
}
