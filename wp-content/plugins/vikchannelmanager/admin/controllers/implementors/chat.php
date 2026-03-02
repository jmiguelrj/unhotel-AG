<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

// auto-load chat handler
require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'chat' . DIRECTORY_SEPARATOR . 'handler.php';
require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'httperror.php';
require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';

/**
 * VikChannelManager chat controller implementation.
 *
 * @since 1.6.13
 */
class VCMControllerImplementorChat extends JControllerAdmin
{
	/**
	 * Flag used to check whether we should skip the authentication.
	 *
	 * @var boolean
	 */
	public $authenticated = false;

	/**
	 * AJAX end-point used to return a list of the older messages
	 * that belong to the requested thread. The messages to retrieve
	 * must be contained between the specified offsets.
	 *
	 * @param 	boolean  $return 	True to return the response instead echoing it.
	 *
	 * @return 	void
	 */
	public function load_older_messages($return = false)
	{
		$input = JFactory::getApplication()->input;

		// get chat arguments
		$id_order = $input->getString('id_order', '');
		$secret   = $input->getString('secret', '');
		$channel  = $input->getString('channel', null);

		// make sure the user is authorised to read older messages
		if (!$this->isAuthenticated())
		{
			$sign = array($id_order, $secret);
		}
		else
		{
			$sign = $id_order;
		}

		// initialize chat handler
		$chat = VCMChatHandler::getInstance($sign, $channel);

		if (!$chat)
		{
			VCMHttpDocument::getInstance()->close(404, sprintf('Chat [%s] not found', $channel));
		}

		// get thread arguments
		$id_thread = $input->getUint('id_thread', 0);
		$datetime  = $input->getString('datetime', null);

		// get pagination arguments
		$start = $input->getUint('start', 0);
		$limit = $input->getUint('limit', 20);

		// load thread messages between the specified limits
		$threads = $chat->loadThreadsMessages($start, $limit, $id_thread, $datetime);

		if ($threads)
		{
			if ($id_thread)
			{
				// return only the messages of the first (and unique) thread
				$messages = $threads[0]->messages;
			}
			else
			{
				// return all the threads in case of missing id_thread
				$messages = $threads;
			}

			/**
			 * Prevent issues with the E4jConnect App by injecting the booking "ts" as "secret" key.
			 * 
			 * @since 	1.9.6
			 */
			$booking = $chat->getBooking();
			$messages[0]->secret = $booking['ts'] ?? null;
		}
		else
		{
			$messages = array();
		}

		// check whether the response should be returned
		if ($return)
		{
			return $messages;
		}

		echo json_encode($messages);
		exit;
	}

	/**
	 * AJAX end-point used to keep the threads synchronized.
	 *
	 * @param 	boolean  $return 	True to return the response instead echoing it.
	 *
	 * @return 	void
	 */
	public function sync_threads($return = false)
	{
		$input = JFactory::getApplication()->input;

		// get chat arguments
		$id_order = $input->getString('id_order', '');
		$secret   = $input->getString('secret', '');
		$channel  = $input->getString('channel', null);

		// make sure the user is authorised to synchronize threads
		if (!$this->isAuthenticated())
		{
			$sign = array($id_order, $secret);
		}
		else
		{
			$sign = $id_order;
		}

		// initialize chat handler
		$chat = VCMChatHandler::getInstance($sign, $channel);

		if (!$chat)
		{
			VCMHttpDocument::getInstance()->close(404, sprintf('Chat [%s] not found', $channel));
		}

		/**
		 * Register user PING.
		 * If we have been authenticated through mobile APP, we are
		 * using the front-end client as administrators. So, we just
		 * need to register the ping for the opposite client: admin.
		 */
		$chat->getUser($this->isAuthenticated() ? true : false)->ping();

		// check if we should check for new threads/messages
		if (!$chat->shouldDownloadNew())
		{
			// get threshold to exclude all the messages with ID equals or lower
			$threshold = $input->getUint('threshold', 0);

			/**
			 * In case we shouldn't download something new, we could still
			 * search for most recent messages, which may have been pushed by
			 * e4jConnect. 
			 */
			$threads = $chat->loadRecentThreadsMessages($threshold);

			// check whether the response should be returned
			if ($return)
			{
				return $threads;
			}

			echo json_encode($threads);
			exit;
		}

		// download threads
		$resp = $chat->sync();

		if ($resp === false)
		{
			// get errors
			$error = $chat->getErrors();

			if (!$error)
			{
				$error = 'An error occurred while downloading the threads';
			}
			else
			{
				$error = implode("\n", $error);
			}

			// something went wrong, raise an exception
			VCMHttpDocument::getInstance()->close(502, $error);
		}

		$threads = array();

		// check if we have something new
		if ($resp->newThreads || $resp->newMessages)
		{
			// load unread threads messages
			$threads = $chat->loadUnreadThreadsMessages();
		}

		// check whether the response should be returned
		if ($return)
		{
			return $threads;
		}

		echo json_encode($threads);
		exit;
	}

	/**
	 * AJAX end-point used to reply to an existing message written
	 * by the guest. This task is always submitted by the Host.
	 *
	 * @param 	bool  $return 	True to return the response rather than echoing it.
	 *
	 * @return 	void
	 */
	public function thread_message_reply($return = false)
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		// get chat arguments
		$id_order = $input->getString('id_order', '');
		$secret   = $input->getString('secret', '');
		$channel  = $input->getString('channel', null);

		// make sure the user is authorised to send messages to this thread
		if (!$this->isAuthenticated())
		{
			$sign = array($id_order, $secret);
		}
		else
		{
			$sign = $id_order;
		}

		// initialize chat handler
		$chat = VCMChatHandler::getInstance($sign, $channel);

		if (!$chat)
		{
			VCMHttpDocument::getInstance()->close(404, sprintf('Chat [%s] not found', $channel));
		}

		// get message arguments
		$id_thread   = $input->getUint('id_thread', 0);
		$content     = $input->getRaw('content', '');
		$datetime    = $input->getString('datetime', '');
		$attachments = $input->getString('attachments', array());
		$sender_name = $input->getString('sender_name', null);

		// make content HTML-safe
		$content = htmlentities($content);

		// prepend URL to filenames
		$attachments = array_map(function($file)
		{
			// check if we received a URL
			if (preg_match("/^https?:\/\//", $file)) {
				return $file;
			}

			// construct the URL by using the provided name
			return VCM_SITE_URI . 'helpers/chat/attachments/' . $file;
		}, $attachments);

		// init message object
		$message = new VCMChatMessage($content);
		$message->set('idthread', $id_thread);
		$message->set('dt', $datetime);
		// we are the guest only in case the client is SITE and it not autheticated,
		// because the APP mobile communicate through the site end-point
		$message->set('guest', (bool) VikChannelManager::isSite() && !$this->isAuthenticated());
		// set attachments
		$message->setAttachments($attachments);

		/**
		 * In case we are in the front-end and the user is currently logged in as operator,
		 * attempt to assign the reply to an internal cohost ID.
		 * 
		 * @since 1.9.7
		 */
		if (VikChannelManager::isSite() && ($operator = $this->getOperator()))
		{
			// force the operator name
			$sender_name = trim($operator['first_name'] . ' ' . $operator['last_name']) ?: $sender_name;
			
			// create co-host details
			$cohost = new stdClass;
			$cohost->ota_id = $operator['id'];
			$cohost->name = $sender_name;
			$cohost->pic = $operator['pic'];

			// check whether the cohost already exists, otherwise create a new instance
			if ($cohostId = $chat->parseCohostDetails($cohost))
			{
				// attach the message to the co-host found (operator)
				$message->set('cohost_id', $cohostId);
			}
		}

		if ($sender_name)
		{
			// use the specified sender name
			$message->set('sender_name', $sender_name);
		}

		// check if we should reply to an existing message
		if ($id_thread)
		{
			// invoke reply and return stored record
			$result = $chat->reply($message);
		}
		// otherwise we need to open a new thread
		else
		{
			// set thread subject
			$message->set('subject', $input->getString('subject', 'Thread'));
			
			// invoke send message and return stored record
			$result = $chat->send($message);
		}

		if (!$result)
		{
			// get errors
			$error = $chat->getErrors();

			if (!$error)
			{
				$error = 'An error occurred while replying';
			}
			else
			{
				$error = implode("\n", $error);
			}

			// something went wrong, raise an exception
			VCMHttpDocument::getInstance()->close(502, $error);
		}

		if ($message->get('guest'))
		{
			// when the guest sends a message to the hotel from the site (vikbooking),
			// attempt to store a record in the notifications center
			try
			{
				if (!($result->thread->idorder ?? null))
				{
					$result->thread->idorder = $id_order;
				}

				VBOFactory::getNotificationCenter()
					->parseNewGuestMessage($result->thread, $result->message);
			}
			catch (Throwable $e)
			{
				// silently catch the error and do nothing
			}
		}

		/**
		 * From now on, we cannot throw exceptions anymore as the message
		 * has been stored properly, otherwise we would face unexpected 
		 * behaviors within the chat client.
		 *
		 * Any errors should be pushed within the $result object, so that
		 * they can being studied through a web inspector.
		 */

		// make sure this handler supports notifications
		if ($chat->supportNotifications())
		{
			// get synctime (10 by default)
			$sync_time = (int) $chat->get('syncTime', 10) * 2;

			/**
			 * If we have been authenticated through mobile APP, we are
			 * using the front-end client as administrators. So, we just
			 * need to send a notification to the guest user (front-end)
			 * and we don't need to specify the $recipient flag.
			 */
			$recipient = $this->isAuthenticated() ? false : true;

			// get recipient user instance
			$user = $chat->getUser($recipient);

			// check whether the recipient user is offline and it has to read at most 3 messages
			if ($user->isOffline($sync_time) && VCMChatHandler::countUnreadMessages($id_order, $recipient = true) <= 3)
			{
				// create mediator instance
				$mediator = VCMChatNotificationMediator::getInstance();
				// dispatch notification
				$res = $mediator->notify($user, $message);

				// check if something went wrong
				if (!$res)
				{
					// push fetched errors within the $result object
					$result->notificationErrors = $mediator->getErrors();
				}
			}
		}

		// check whether the response should be returned
		if ($return)
		{
			return $result;
		}

		echo json_encode($result);
		exit;
	}

	/**
	 * AJAX end-point used to send a reaction to an existing guest message.
	 * This task is always submitted by the Hotel/Host or operator.
	 *
	 * @return 	void
	 * 
	 * @since 	1.9.18
	 */
	public function thread_message_reaction()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		// get chat arguments
		$id_order = $input->getString('id_order', '');
		$secret   = $input->getString('secret', '');
		$channel  = $input->getString('channel', null);

		// make sure the user is authorised to send message reactions to this thread
		if (!$this->isAuthenticated()) {
			$sign = [$id_order, $secret];
		} else {
			$sign = $id_order;
		}

		// initialize chat handler
		$chat = VCMChatHandler::getInstance($sign, $channel);

		if (!$chat) {
			VCMHttpDocument::getInstance($app)->close(404, sprintf('Chat [%s] not found', $channel));
		}

		// get message arguments
		$id_thread  = $input->getUint('id_thread', 0);
		$id_message = $input->getUint('id_message', 0);
		$emoji      = $input->getString('emoji', '');

		// make sure the emoji is not empty, then validate it against a range of emojis
		if (!$emoji || (!preg_match('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/ui', $emoji) && !preg_match('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/ui', str_replace('"', '', json_encode($emoji))))) {
			VCMHttpDocument::getInstance($app)->close(400, 'Unsupported emoji for the message reaction' . implode(' - ', [$emoji, json_encode($emoji)]));
		}

		// confirm existing thread message ID
		$threadMessageId = $chat->messageExists([
			'id'       => $id_message,
			'idthread' => $id_thread,
		]);

		if (!$threadMessageId) {
			VCMHttpDocument::getInstance($app)->close(404, 'Thread message not found');
		}

		// fetch thread message record
		$threadMessage = $chat->getMessage($id_message);

		if (!$threadMessage) {
			VCMHttpDocument::getInstance($app)->close(404, 'Could not fetch thread message');
		}

		// access sender name and ID
		$sender_name = null;

		if (VikChannelManager::isSite() && ($operator = $this->getOperator())) {
			// force the operator name
			$sender_name = trim($operator['first_name'] . ' ' . $operator['last_name']) ?: $sender_name;
		} elseif (VikChannelManager::isAdmin()) {
			// get current user name
			$user = JFactory::getUser();
			$sender_name = $user->name;
			// convert empty value to null, if applicable
			$sender_name = $sender_name ?: null;
		}

		try {
			// send and save message reaction
			$reactionSent = $chat->sendReaction([
				'idthread'       => $id_thread,
				'idmessage'      => $id_message,
				'ota_message_id' => $threadMessage->ota_message_id ?? null,
				'emoji'          => $emoji,
				'user'           => $sender_name,
				'dt'             => JDate::getInstance()->toSql(),
			]);

			if (!$reactionSent) {
				VCMHttpDocument::getInstance($app)->close(500, 'Could not send or save message reaction');
			}
		} catch (Exception $e) {
			// display error
			VCMHttpDocument::getInstance($app)->close($e->getCode(), $e->getMessage());
		}

		// send success response to output
		VCMHttpDocument::getInstance($app)->json([
			'success' => true,
		]);
	}

	/**
	 * AJAX end-point used to mark the specified message as read.
	 * All the messages prior than the specified one will be affected too.
	 *
	 * @param 	boolean  $return 	True to return the response instead echoing it.
	 *
	 * @return 	void
	 */
	public function read_messages($return = false)
	{
		$input = JFactory::getApplication()->input;

		// get chat arguments
		$id_order = $input->getString('id_order', '');
		$secret   = $input->getString('secret', '');
		$channel  = $input->getString('channel', null);
		$read_dt  = $input->getString('datetime', '');

		// make sure the user is authorised to read this message
		if (!$this->isAuthenticated())
		{
			$sign = array($id_order, $secret);
		}
		else
		{
			$sign = $id_order;
		}

		// initialize chat handler
		$chat = VCMChatHandler::getInstance($sign, $channel);

		if (!$chat)
		{
			VCMHttpDocument::getInstance()->close(404, sprintf('Chat [%s] not found', $channel));
		}

		// get message
		$id_message = $input->getUint('id_message', 0);

		// read messages
		$result = $chat->readMessage($id_message, $read_dt ? $read_dt : 'now');

		if (isset($result->channel) && $result->channel === false)
		{
			// get errors
			$error = $chat->getErrors();

			if (!$error)
			{
				$error = 'An error occurred while replying';
			}
			else
			{
				$error = implode("\n", $error);
			}

			// something went wrong, raise an exception
			VCMHttpDocument::getInstance()->close(502, $error);
		}

		// check whether the response should be returned
		if ($return)
		{
			return $result;
		}

		// return number of read messages
		echo json_encode($result);
		exit;
	}

	/**
	 * AJAX end-point used to check the number of unread messages
	 * for one or more orders.
	 *
	 * @param 	boolean  $return 	True to return the response instead echoing it.
	 *
	 * @return 	void
	 */
	public function count_unread_messages($return = false)
	{
		$input = JFactory::getApplication()->input;

		// get order ID
		$oid = $input->get('id_order', 0, 'int');

		// count unread messages for the current client
		$count = VCMChatHandler::countUnreadMessages($oid);

		// check whether the response should be returned
		if ($return)
		{
			return $count;
		}

		echo json_encode($count);
		exit;
	}

	/**
	 * AJAX end-point used to upload attachments before sending the message.
	 * Files are uploaded onto the attachments folder of the front-end and a
	 * JSON encoded objects array is returned with the details of each file.
	 *
	 * @param 	boolean  $return 	True to return the response instead echoing it.
	 *
	 * @return 	void
	 */
	public function upload_attachments($return = false)
	{
		/**
		 * Handle CORS for the mobile App requests.
		 * 
		 * @since 	1.9.6
		 */
		VCMAuthHelper::handleCORS();

		$input = JFactory::getApplication()->input;

		// get chat arguments
		$id_order = $input->getString('id_order', '');
		$secret   = $input->getString('secret', '');
		$channel  = $input->getString('channel', null);

		// make sure the user is uploading an attachment for a valid order
		if (!$this->isAuthenticated())
		{
			$sign = array($id_order, $secret);
		}
		else
		{
			$sign = $id_order;
		}

		// initialize chat handler
		$chat = VCMChatHandler::getInstance($sign, $channel);

		if (!$chat)
		{
			VCMHttpDocument::getInstance()->close(404, sprintf('Chat [%s] not found', $channel));
		}

		// always attempt to include the File class
		jimport('joomla.filesystem.file');

		// get uploaded files array (user "raw" to avoid filtering the file to upload)
		$files = $input->files->get('attachments', array(), 'raw');

		if (isset($files['name']))
		{
			// we have a single associative array, we need to push it within a list,
			// because the upload iterates the $files array
			$files = array($files);
		}

		// attachments pool
		$attachments = array();

		// upload dir
		$base_dest = VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'chat' . DIRECTORY_SEPARATOR . 'attachments' . DIRECTORY_SEPARATOR;
		$base_uri  = VCM_SITE_URI . 'helpers/chat/attachments/';

		try
		{
			foreach ($files as $file)
			{
				// extract file extension from original file name
				if (preg_match("/\.([a-z0-9]{2,})$/i", $file['name'], $match))
				{
					$ext = end($match);
				}
				else
				{
					// we have a file w/o extension
					$ext = '';
				}

				// keep name without file extension
				$name = preg_replace("/\.$ext$/", '', $file['name']);

				do
				{
					// use a random (probable) unique ID as file name
					$filename = VikChannelManager::uuid() . ($ext ? '.' . $ext : '');
					// repeat in case we have been so unlucky
				} while (is_file($base_dest . $filename));

				// Check here if we are uploading a supported attachment.
				// Children classes might inherit this method to specify
				// their own supported types.
				if (!$chat->checkAttachment($file))
				{
					// file type not supported, abort
					VCMHttpDocument::getInstance()->close(400, sprintf('File type [%s] not supported', $file['type']));
				}

				if (JFile::upload($file['tmp_name'], $base_dest . $filename, $use_streams = false, $allow_unsafe = true))
				{
					// prepare attachment object
					$attachment = new stdClass;
					$attachment->name 	   = $name;
					$attachment->filename  = $filename;
					$attachment->extension = $ext;
					$attachment->type 	   = $file['type'];
					$attachment->url  	   = $base_uri . $filename;

					// push attachment object
					$attachments[] = $attachment;
				}
				else
				{
					VCMHttpDocument::getInstance()->close(500, sprintf('Impossible to upload [%s] file', $file['name']));
				}
			}
		}
		catch (Exception $e)
		{
			// iterate all uploaded attachments and unlink them
			foreach ($attachments as $attachment)
			{
				@unlink($base_dest . $attachment->filename);
			}

			// re-throw caught exception
			throw $e;
		}

		/**
		 * Check if a file is trying to be uploaded maybe through the App as a file data-uri.
		 * 
		 * @since 	1.9.6
		 */
		$datauri = (string) $input->get('datauri', null, 'raw');
		if (!$attachments && $datauri)
		{
			$allowed_mime_types = [
				'text/plain' => '.txt',
				'image/jpeg' => '.jpg',
				'image/jpg' => '.jpg',
				'image/png' => '.png',
				'image/webp' => '.webp',
				'image/gif' => '.gif',
				'application/pdf' => '.pdf',
			];

			// get mime-type from data-uri
			if (preg_match('/data:(.+);base64,?/', $datauri, $matches) && isset($allowed_mime_types[$matches[1]]))
			{
				do
				{
					// use a random (probable) unique ID as file name
					$filename = VikChannelManager::uuid() . $allowed_mime_types[$matches[1]];
					// repeat in case we have been so unlucky
				} while (is_file($base_dest . $filename));

				// turn data-uri into image decoded content
				$datauri = base64_decode(str_replace($matches[0], '', $datauri));
				if ($datauri === false)
				{
					// something went wrong, raise an error
					VCMHttpDocument::getInstance()->close(400, 'Could not decode file data-uri');
				}

				if (JFile::write($base_dest . $filename, $datauri))
				{
					// prepare attachment object
					$attachment = new stdClass;
					$attachment->name 	   = $filename;
					$attachment->filename  = $filename;
					$attachment->extension = str_replace('.', '', $allowed_mime_types[$matches[1]]);
					$attachment->type 	   = $matches[1];
					$attachment->url  	   = $base_uri . $filename;

					// push attachment object
					$attachments[] = $attachment;
				}
				else
				{
					// something went wrong, raise an error
					VCMHttpDocument::getInstance()->close(400, 'Could not save file data-uri');
				}
			}
			else
			{
				// something went wrong, raise an error
				VCMHttpDocument::getInstance()->close(400, 'Unsupported file data-uri format');
			}
		}

		if (!$attachments)
		{
			// something went wrong, raise an error
			VCMHttpDocument::getInstance()->close(400, 'No files uploaded');
		}

		// check whether the response should be returned
		if ($return)
		{
			return $attachments;
		}

		// return array of attached file URLs
		echo json_encode($attachments);
		exit;
	}

	/**
	 * AJAX end-point used to remove the specified attachment.
	 *
	 * @param 	boolean  $return 	True to return the response instead echoing it.
	 *
	 * @return 	void
	 */
	public function remove_attachment($return = false)
	{
		$input = JFactory::getApplication()->input;

		// get attachment filename
		$filename = $input->getString('filename', '');

		// build base path
		$path = VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'chat' . DIRECTORY_SEPARATOR . 'attachments' . DIRECTORY_SEPARATOR;

		// check whether the file exists
		if (is_file($path . $filename))
		{
			// try to unlink the file
			$res = @unlink($path . $filename);
		}
		else
		{
			$res = false;
		}

		// check whether the response should be returned
		if ($return)
		{
			return $res;
		}

		// return response
		echo json_encode($res);
		exit;
	}

	/**
	 * AJAX end-point used to retrieve all the latest threads.
	 *
	 * @param 	boolean  $return 	True to return the response instead echoing it.
	 *
	 * @return 	void
	 *
	 * @since 	1.7.4
	 */
	public function load_latest_threads($return = false)
	{
		$input = JFactory::getApplication()->input;

		// make sure the user is authenticated
		if (!$this->isAuthenticated())
		{
			// direct access not allowed
			VCMHttpDocument::getInstance()->close(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get pagination arguments
		$start = $input->getUint('start', 0);
		$limit = $input->getUint('limit', 20);

		// load latest threads
		$threads = VCMChatHandler::getLatestThreads($start, $limit);

		// check whether the response should be returned
		if ($return)
		{
			return $threads;
		}

		// return response
		echo json_encode($threads);
		exit;
	}

	/**
	 * Checks whether the user is currently authenticated on the site as administrator.
	 * NOTE: the method will always return false when connected from the back-end.
	 * 
	 * @return  bool
	 * 
	 * @since   1.9.7
	 */
	protected function isAuthenticated()
	{
		if ($this->authenticated == false && JFactory::getApplication()->isClient('site') && $this->getOperator()) {
			// logged-in as operator, mark as authenticated to speed up the loading process
			$this->authenticated = true;
		}

		return $this->authenticated;
	}

	/**
	 * In case the user is currently logged in as operator, returns the details.
	 * 
	 * @return  false|array
	 * 
	 * @since   1.9.7
	 */
	protected function getOperator()
	{
		static $operator = null;

		if ($operator === null) {
			// access the global operators object
			$operatorInstance = VikBooking::getOperatorInstance();

			// attempt to get the current operator
			$operator = $operatorInstance->getOperatorAccount();
		}

		return $operator;
	}
}
