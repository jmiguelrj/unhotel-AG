<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com
 */

defined('_JEXEC') OR die('Restricted Area');

// auto-load chat handler
require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'chat' . DIRECTORY_SEPARATOR . 'handler.php';
require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'httperror.php';

/**
 * VikChannelManager chat controller (front-end).
 *
 * @since 1.6.13
 */
class VikChannelManagerControllerChat extends JControllerAdmin
{
	/**
	 * AJAX end-point used to returns a list of the older messages
	 * that belong to the requested thread. The messages to retrieve
	 * must be contained between the specified offsets.
	 *
	 * @return 	void
	 */
	public function load_older_messages()
	{
		$input = JFactory::getApplication()->input;

		// get chat arguments
		$id_order = $input->getUint('id_order', 0);
		$channel  = $input->getString('channel', null);

		// initialize chat handler
		$chat = VCMChatHandler::getInstance($id_order, $channel);

		if (!$chat)
		{
			throw new Exception(sprintf('Chat [%s] not found', $channel), 404);
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
			$messages = $threads[0]->messages;
		}
		else
		{
			$messages = array();
		}

		echo json_encode($messages);
		exit;
	}

	/**
	 * AJAX end-point used to keep the threads synchronized.
	 *
	 * @return 	void
	 */
	public function sync_threads()
	{
		$input = JFactory::getApplication()->input;

		// get chat arguments
		$id_order = $input->getUint('id_order', 0);
		$channel  = $input->getString('channel', null);

		// initialize chat handler
		$chat = VCMChatHandler::getInstance($id_order, $channel);

		if (!$chat)
		{
			throw new Exception(sprintf('Chat [%s] not found', $channel), 404);
		}

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
			throw new Exception($error, 502);
		}

		$threads = array();

		// check if we have something new
		if ($resp->newThreads || $resp->newMessages)
		{
			// load unread threads messages
			$threads = $chat->loadUnreadThreadsMessages();
		}

		echo json_encode($threads);
		exit;
	}

	/**
	 * AJAX end-point used to reply to an existing message wrote
	 * by the customer. This task is always submitted by the Hotel
	 * owner.
	 *
	 * @return 	void
	 */
	public function thread_message_reply()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		// get chat arguments
		$id_order = $input->getUint('id_order', 0);
		$channel  = $input->getString('channel', null);

		// initialize chat handler
		$chat = VCMChatHandler::getInstance($id_order, $channel);

		if (!$chat)
		{
			throw new Exception(sprintf('Chat [%s] not found', $channel), 404);
		}

		// get message arguments
		$id_thread   = $input->getUint('id_thread', 0);
		$content     = $input->getString('content', '');
		$datetime    = $input->getString('datetime', '');
		$attachments = $input->getString('attachments', array());

		// prepend URL to filenames
		$attachments = array_map(function($file)
		{
			return VCM_SITE_URI . 'helpers/chat/attachments/' . $file;
		}, $attachments);

		// init message object
		$message = new VCMChatMessage($content);
		$message->set('idthread', $id_thread);
		$message->set('dt', $datetime);
		// we are the guest only in case the client is SITE
		$message->set('guest', (bool) VikChannelManager::isSite());
		// set attachments
		$message->setAttachments($attachments);

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
			throw new Exception($error, 502);
		}

		echo json_encode($result);
		exit;
	}

	/**
	 * AJAX end-point used to mark the specified message as read.
	 * All the messages prior than the specified one will be affected too.
	 *
	 * @return 	void
	 */
	public function read_messages()
	{
		$input = JFactory::getApplication()->input;

		// get chat arguments
		$id_order = $input->getUint('id_order', 0);
		$channel  = $input->getString('channel', null);

		// initialize chat handler
		$chat = VCMChatHandler::getInstance($id_order, $channel);

		if (!$chat)
		{
			throw new Exception(sprintf('Chat [%s] not found', $channel), 404);
		}

		// get message
		$id_message = $input->getUint('id_message', 0);

		// read messages
		$result = $chat->readMessage($id_message);

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
			throw new Exception($error, 502);
		}

		// return number of read messages
		echo json_encode($result);
		exit;
	}

	/**
	 * AJAX end-point used to upload attachments before sending the message.
	 * Files are uploaded onto the attachments folder of the front-end and a
	 * JSON encoded objects array is returned with the details of each file.
	 *
	 * @return 	void
	 */
	public function upload_attachments()
	{
		$input = JFactory::getApplication()->input;

		// get chat arguments
		$id_order = $input->getUint('id_order', 0);
		$channel  = $input->getString('channel', null);

		// initialize chat handler
		$chat = VCMChatHandler::getInstance($id_order, $channel);

		if (!$chat)
		{
			throw new Exception(sprintf('Chat [%s] not found', $channel), 404);
		}

		// always attempt to include the File class
		jimport('joomla.filesystem.file');

		// get uploaded files array (user "raw" to avoid filtering the file to upload)
		$files = $input->files->get('attachments', array(), 'raw');

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
					// throw new Exception(sprintf('File type [%s] not supported', $file['type']), 400);
					VCMHttpError::raiseError(400, sprintf('File type [%s] not supported', $file['type']));
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
					throw new Exception(sprintf('Impossible to upload [%s] file', $file['name']), 500);
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

		if (!count($attachments))
		{
			// something went wrong, raise an exception
			throw new Exception('No files uploaded', 400);
		}

		// return array of attached file URLs
		echo json_encode($attachments);
		exit;
	}

	/**
	 * AJAX end-point used to remove the specified attachment.
	 *
	 * @return 	void
	 */
	public function remove_attachment()
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

		// return response
		echo json_encode($res);
		exit;
	}
}
