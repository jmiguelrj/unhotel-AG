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

// class VikChannelManagerConfig is necessary
require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'vcm_config.php';

/**
 * Airbnb API class handler for the guest messages.
 * 
 * @since 	1.8.0
 */
class VCMChatChannelAirbnbapi extends VCMChatHandler
{
	/**
	 * The channel name.
	 *
	 * @var string
	 */
	protected $channelName = 'airbnbapi';

	/**
	 * Finds the Airbnb user ID for the given booking.
	 * 
	 * @return 	mixed 	false on failure, string otherwise.
	 */
	protected function getAccountId()
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select([
				$dbo->qn('or.idroom'),
				$dbo->qn('x.prop_params'),
			])
			->from($dbo->qn('#__vikbooking_ordersrooms', 'or'))
			->leftJoin($dbo->qn('#__vikchannelmanager_roomsxref', 'x') . ' ON ' . $dbo->qn('or.idroom') . ' = ' . $dbo->qn('x.idroomvb'))
			->where($dbo->qn('or.idorder') . ' = ' . (int)$this->id_order)
			->where($dbo->qn('x.idchannel') . ' = ' . (int)VikChannelManagerConfig::AIRBNBAPI);

		$dbo->setQuery($q);
		$rows = $dbo->loadAssocList();

		if (!$rows) {
			return false;
		}

		foreach ($rows as $row) {
			$params = !empty($row['prop_params']) ? json_decode($row['prop_params'], true) : [];
			if (!is_array($params)) {
				continue;
			}
			return !empty($params['user_id']) ? $params['user_id'] : false;
		}

		// nothing found
		return false;
	}

	/**
	 * Finds the last message in a thread sent by the guest/host.
	 * Used to reply to a guest message by the host.
	 * 
	 * @param 	int 	$idthread 	the VCM thread ID.
	 * @param 	string 	$reciptype 	the recipient type, defaults to host.
	 * 
	 * @return 	mixed 	false on failure, message object record otherwise.
	 */
	protected function getLastThreadMessage($idthread, $reciptype = 'host')
	{
		// adjust recipient type to 'host' if 'hotel' given
		$reciptype = strtolower($reciptype) == 'hotel' ? 'host' : $reciptype;

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('m') . '.*')
			->select($dbo->qn('t.ota_thread_id'))
			->from($dbo->qn('#__vikchannelmanager_threads_messages', 'm'))
			->leftJoin($dbo->qn('#__vikchannelmanager_threads', 't') . ' ON ' . $dbo->qn('m.idthread') . ' = ' . $dbo->qn('t.id'))
			->where($dbo->qn('m.idthread') . ' = ' . (int)$idthread)
			->where($dbo->qn('m.recip_type') . ' = ' . $dbo->q($reciptype))
			->order($dbo->qn('m.dt') . ' DESC');

		$dbo->setQuery($q, 0, 1);
		$message = $dbo->loadObject();

		if (!$message) {
			// unset the where clause and look just for any message on this thread
			$q->clear('where')->where($dbo->qn('m.idthread') . ' = ' . (int)$idthread);

			$dbo->setQuery($q, 0, 1);
			$message = $dbo->loadObject();
		}

		if (!$message) {
			$this->setError('No guest messages found in this thread.');
			return false;
		}

		return $message;
	}

	/**
	 * Finds the already stored ota thread ids from the given ota booking id.
	 * 
	 * @return 	array 	empty array or list of thread ID strings.
	 */
	protected function getBookingOtaThreads()
	{
		if (empty($this->booking['idorderota'])) {
			return [];
		}

		/**
		 * Check if the thread id is saved within the booking record.
		 * 
		 * @since 	1.8.20
		 */
		if (!empty($this->booking['ota_type_data'])) {
			$ota_type_data = is_string($this->booking['ota_type_data']) ? (array)json_decode($this->booking['ota_type_data'], true) : (array)$this->booking['ota_type_data'];
			if (!empty($ota_type_data['thread_id'])) {
				// return the already available thread id without looking for messages
				return [$ota_type_data['thread_id']];
			}
		}

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('ota_thread_id'))
			->from($dbo->qn('#__vikchannelmanager_threads'))
			->where($dbo->qn('idorderota') . ' = ' . $dbo->q($this->booking['idorderota']))
			->where($dbo->qn('channel') . ' = ' . $dbo->q($this->channelName));

		$dbo->setQuery($q);
		$threads = $dbo->loadAssocList();

		if (!$threads) {
			return [];
		}

		// build the array of thread ids found, should be just one per reservation
		$ota_thread_ids = [];
		foreach ($threads as $thread) {
			if (!empty($thread['ota_thread_id']) && !in_array($thread['ota_thread_id'], $ota_thread_ids)) {
				$ota_thread_ids[] = $thread['ota_thread_id'];
			}
		}

		return $ota_thread_ids;
	}

	/**
	 * @override
	 * We set a higher syncTime for the threads messages.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.8.0
	 */
	protected function prepareOptions()
	{
		$this->set('syncTime', 10);

		return;
	}

	/**
	 * @inheritDoc
	 * 
	 * @since 	1.9.13 Airbnb overwrites this method to lower the thread sync interval to 5 minutes.
	 */
	public function shouldDownloadNew($interval = 5)
	{
		return parent::shouldDownloadNew($interval);
	}

	/**
	 * @override
	 * Check here if we are uploading a supported attachment.
	 * We currently allow only image files.
	 *
	 * @param 	array 	 $file 	The details of the uploaded file.
	 *
	 * @return 	boolean  True if supported, false otherwise.
	 */
	public function checkAttachment(array $file)
	{
		// make sure we have a MIME type
		if (empty($file['type']))
		{
			return false;
		}

		/**
		 * Accept images (png, gif, jpg) and videos (mp4, quicktime).
		 * 
		 * @since 	1.9.18
		 */
		return preg_match("/^image\/(png|jpe?g|gif)|video\/(mp4|quicktime|hls|mov)$/", $file['type']);
	}

	/**
	 * @override
	 * Makes the MTHDRD request to e4jConnect.com to get all threads
	 * and related messages for the current Airbnb reservation ID.
	 * 
	 * @return 	mixed 	false on failure, stdClass object with stored information otherwise.
	 */
	protected function downloadThreads()
	{
		$vcm_api_key = VikChannelManager::getApiKey();
		if (empty($vcm_api_key)) {
			$this->setError('Missing API Key');
			return false;
		}

		if (empty($this->booking['idorderota'])) {
			$this->setError('Missing OTA Booking ID');
			return false;
		}

		// Airbnb User ID is mandatory for the Messaging API
		$host_user_id = $this->getAccountId();
		if (empty($host_user_id)) {
			$this->setError('Empty Airbnb User ID');
			return false;
		}

		// check if an ota thread id is available for this booking
		$thread_id = '';
		$all_threads = $this->getBookingOtaThreads();
		if ($all_threads) {
			$thread_id = $all_threads[0];
		}

		$endp_url = "https://slave.e4jconnect.com/channelmanager/?r=mthdrd&c=" . $this->channelName;

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager MTHDRD Request e4jConnect.com - Airbnb -->
<MessagingThreadReadRQ xmlns="http://www.e4jconnect.com/channels/mthdrdrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $vcm_api_key . '"/>
	<ReadThreadsMessages>
		<Fetch hotelid="' . $host_user_id . '" resid="' . $this->booking['idorderota'] . '" threadid="' . $thread_id . '"/>
	</ReadThreadsMessages>
</MessagingThreadReadRQ>';
		
		$e4jC = new E4jConnectRequest($endp_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			$this->setError('cURL error: ' . $e4jC->getErrorMsg());
			return false;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			$this->setError(VikChannelManager::getErrorFromMap($rs));
			return false;
		}

		$threads = json_decode($rs);
		if (!is_array($threads)) {
			$this->setError('Could not decode JSON response ('.(function_exists('json_last_error') ? json_last_error() : '-').'): '.$rs);
			return false;
		}

		// data stored information
		$stored = new stdClass;
		$stored->newThreads  = 0;
		$stored->newMessages = 0;

		foreach ($threads as $threadmess) {
			// compose thread object with the information to find it
			$check_thread = new stdClass;
			$check_thread->idorder = $this->booking['id'];
			$check_thread->idorderota = $this->booking['idorderota'];
			$check_thread->channel = $this->channelName;
			$check_thread->ota_thread_id = $threadmess->thread->id;
			
			// find thread last_updated date from messages
			$most_recent_ts = 0;
			$most_recent_dt = '';
			foreach ($threadmess->messages as $message) {
				// update most recent date for the thread `last_updated`
				$mess_ts = strtotime($message->created_on);
				if ($most_recent_ts < $mess_ts) {
					$most_recent_ts = $mess_ts;
					$most_recent_dt = $message->created_on;
				}
			}
			
			// check whether this thread exists
			$cur_thread_id = $this->threadExists($check_thread);
			if ($cur_thread_id !== false) {
				// set the ID property to later update the thread found
				$check_thread->id = $cur_thread_id;
			}

			// set last_updated value for thread and other properties returned
			$check_thread->subject = ucwords($threadmess->thread->topic);
			$check_thread->type = $threadmess->thread->type;
			$check_thread->last_updated = $most_recent_dt;

			// always attempt to create/update thread
			$vcm_thread_id = $this->saveThread($check_thread);
			if ($vcm_thread_id === false) {
				// go to next thread
				$this->setError('Could not store thread.');
				continue;
			}

			if ($cur_thread_id === false) {
				// a new thread was stored, increase counter
				$stored->newThreads++;
			}

			// get all new messages from this thread
			$new_messages = [];
			foreach ($threadmess->messages as $message) {
				// compose message object to find it or save it
				$check_message = new stdClass;
				$check_message->idthread = $vcm_thread_id;
				$check_message->ota_message_id = $message->id;

				/**
				 * always save or update the message because when sending
				 * a new message or a reply to the guest, we may be missing
				 * information about the sender/recipient ID/type/name or payload.
				 */
				$cur_mess_id = $this->messageExists($check_message);
				if ($cur_mess_id !== false) {
					// set the ID property for later updating the message
					$check_message->id = $cur_mess_id;
				}

				// set the rest of the properties to this message
				$check_message->in_reply_to = !empty($message->payload->in_reply_to) ? $message->payload->in_reply_to : null;
				$check_message->sender_id = !empty($message->sender->id) ? $message->sender->id : null;
				$check_message->sender_name = !empty($message->sender->name) ? $message->sender->name : null;
				$check_message->sender_type = !empty($message->sender->type) ? $message->sender->type : null;
				$check_message->recip_id = !empty($message->recipient->id) ? $message->recipient->id : null;
				$check_message->recip_name = !empty($message->recipient->name) ? $message->recipient->name : null;
				$check_message->recip_type = !empty($message->recipient->type) ? $message->recipient->type : null;
				$check_message->dt = JDate::getInstance($message->created_on)->toSql();
				$check_message->content = $message->text;
				$check_message->attachments = json_encode($message->attachments);
				$check_message->payload = (($message->payload ?? '') ? json_encode($message->payload) : null);

				/**
				 * In case the message comes from a co-host, we ensure to store the
				 * co-host details and to get the ID to be assigned to the message.
				 * 
				 * @since 	1.8.23
				 */
				if (!empty($message->cohost)) {
					// store the co-host details (if new) and get the ID
					$cohost_id = $this->parseCohostDetails($message->cohost);
					if ($cohost_id) {
						// assign the message to a co-host ID only if available, to prevent possible SQL errors
						$check_message->cohost_id = $cohost_id;
					}
				}

				/**
				 * Added support for message language (2-char lang code).
				 * 
				 * @since 	1.8.27
				 */
				if (!empty($message->lang)) {
					$check_message->lang = substr((string) $message->lang, 0, 2);
				}

				/**
				 * Detect if the guest message is actually a reaction.
				 * 
				 * @since 	1.9.0
				 */
				$is_reaction = null;
				if (($message->reaction ?? null)) {
					// find the quoted message with the reaction
					$quoted_message = $this->messageExists([
						'idthread' => $check_message->idthread,
						'content'  => $message->reaction->quoted,
					]);

					if ($quoted_message) {
						// this will be saved as a message reaction rather than as a guest message
						$is_reaction = $this->saveReaction([
							'idthread'       => $check_message->idthread,
							'idmessage'      => $quoted_message,
							'ota_message_id' => $check_message->ota_message_id,
							'emoji'          => $message->reaction->emoji,
							'user'           => $check_message->sender_name,
							'iduser'         => $check_message->sender_id,
							'dt'             => $check_message->dt,
						]);

						if ($is_reaction) {
							// do not proceed with saving the guest message record when we got a reaction
							$stored->newMessages++;
						}

						// do NOT proceed
						continue;
					}
				}

				// store or update the message
				if ($this->saveMessage($check_message) && $cur_mess_id === false) {
					$stored->newMessages++;
				}
			}
		}

		return $stored;
	}

	/**
	 * @override
	 * Sends a new message to the guest by making the request to e4jConnect.
	 * The new thread and message is immediately stored onto the db and returned.
	 * 
	 * @param 	VCMChatMessage 	$message 	the message object to be sent.
	 * 
	 * @return 	mixed 			stdClass object on success, false otherwise.
	 */
	public function send(VCMChatMessage $message)
	{
		$vcm_api_key = VikChannelManager::getApiKey();
		if (empty($vcm_api_key)) {
			$this->setError('Missing API Key');
			return false;
		}

		if (empty($this->booking['idorderota'])) {
			$this->setError('Missing OTA Booking ID');
			return false;
		}

		// Airbnb User ID is mandatory for the Messaging API
		$host_user_id = $this->getAccountId();
		if (empty($host_user_id)) {
			$this->setError('Empty Airbnb User ID');
			return false;
		}

		// check if an ota thread id is available for this booking
		$thread_id = '';
		$all_threads = $this->getBookingOtaThreads();
		if ($all_threads) {
			$thread_id = $all_threads[0];
		}

		// message attachment nodes
		$attach_node = '';
		if (count($message->getAttachments())) {
			$attach_node = "\n".'<Attachments type="' . $message->get('attachmentsType', 'AttachmentImages') . '">' . "\n";
			foreach ($message->getAttachments() as $furi) {
				$attach_node .= '<Attachment>' . $furi . '</Attachment>' . "\n";
			}
			$attach_node .= '</Attachments>';
		}

		$endp_url = "https://slave.e4jconnect.com/channelmanager/?r=msrep&c=" . $this->channelName;

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager MSREP Request e4jConnect.com - Airbnb -->
<MessagingSendReplyRQ xmlns="http://www.e4jconnect.com/channels/msreprq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $vcm_api_key . '"/>
	<SendReply>
		<Fetch hotelid="' . $host_user_id . '" resid="' . $this->booking['idorderota'] . '" threadid="' . $thread_id . '"/>
		<Message><![CDATA[' . $message->getContent() . ']]></Message>' . $attach_node . '
	</SendReply>
</MessagingSendReplyRQ>';
		
		$e4jC = new E4jConnectRequest($endp_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			$this->setError('cURL error: ' . $e4jC->getErrorMsg());
			return false;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			$this->setError(VikChannelManager::getErrorFromMap($rs));
			return false;
		}

		// we are supposed to receive the new Thread ID and Message ID
		$resp = json_decode($rs);
		if (!$resp) {
			$this->setError('Could not decode JSON response ('.(function_exists('json_last_error') ? json_last_error() : '-').'): '.$rs);
			return false;
		}

		// prepare new thread object to be saved
		$newthread 					= new stdClass;
		$newthread->idorder 		= $this->booking['id'];
		$newthread->idorderota 		= $this->booking['idorderota'];
		$newthread->channel 		= $this->channelName;
		$newthread->ota_thread_id 	= $resp->thread_id;

		// check if the thread exists at this point
		$prev_thread_id = $this->threadExists($newthread);
		if ($prev_thread_id) {
			// set the ID property for an update of the current thread
			$newthread->id = $prev_thread_id;
		}

		// subject and type are usually set to these values at the first message
		$newthread->subject 		= $message->get('subject', 'Email');
		$newthread->type 			= 'Contextual';
		// set last updated to current date time
		$newthread->last_updated 	= JDate::getInstance()->toSql();

		// save new thread or update the existing record
		$vcm_thread_id = $this->saveThread($newthread);
		if ($vcm_thread_id === false) {
			$this->setError('Could not store thread.');
			return false;
		}
		// set the new ID property for the response object
		$newthread->id = $vcm_thread_id;

		// prepare new message object
		$newmessage 				= new stdClass;
		$newmessage->idthread 		= $vcm_thread_id;
		$newmessage->ota_message_id = $resp->message_id;
		$newmessage->sender_id 	 	= null;
		$newmessage->sender_name 	= $message->get('sender_name', null);
		$newmessage->sender_type 	= 'Hotel';
		$newmessage->recip_type 	= 'Guest';
		$newmessage->dt 			= JDate::getInstance()->toSql();
		$newmessage->content 		= $message->getContent();
		$newmessage->attachments 	= json_encode($message->getAttachments());

		// save new message
		$vcm_mess_id = $this->saveMessage($newmessage, (bool) $message->markPreviousReplied());
		if ($vcm_mess_id === false) {
			$this->setError('Could not store message.');
			return false;
		}
		// set the new ID property for the response object
		$newmessage->id = $vcm_mess_id;

		// return the result object
		$result  		 = new stdClass;
		$result->thread  = $newthread;
		$result->message = $newmessage;

		return $result;
	}

	/**
	 * @override
	 * Sends a reply to the guest by making the request to e4jConnect.
	 * The new message is immediately stored onto the db and returned.
	 * 
	 * @param 	VCMChatMessage 	$message 	the message object to be sent.
	 * 
	 * @return 	mixed 			stdClass object on success, false otherwise.
	 */
	public function reply(VCMChatMessage $message)
	{
		/**
		 * Attempt to get the last guest message to which we are responding.
		 * It doesn't matter if we do not find any guest messages on this thread.
		 * 
		 * @since 	1.8.24
		 */
		$lastmessage = $this->getLastThreadMessage($message->get('idthread', 0));
		if (!$lastmessage) {
			return false;
		}

		$vcm_api_key = VikChannelManager::getApiKey();
		if (empty($vcm_api_key)) {
			$this->setError('Missing API Key');
			return false;
		}

		if (empty($this->booking['idorderota'])) {
			$this->setError('Missing OTA Booking ID');
			return false;
		}

		// Airbnb User ID is mandatory for the Messaging API
		$host_user_id = $this->getAccountId();
		if (empty($host_user_id)) {
			$this->setError('Empty Airbnb User ID');
			return false;
		}

		// message attachment nodes
		$attach_node = '';
		if (count($message->getAttachments())) {
			$attach_node = "\n".'<Attachments type="' . $message->get('attachmentsType', 'AttachmentImages') . '">' . "\n";
			foreach ($message->getAttachments() as $furi) {
				$attach_node .= '<Attachment>' . $furi . '</Attachment>' . "\n";
			}
			$attach_node .= '</Attachments>';
		}

		$endp_url = "https://slave.e4jconnect.com/channelmanager/?r=msrep&c=" . $this->channelName;

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager MSREP Request e4jConnect.com - Airbnb -->
<MessagingSendReplyRQ xmlns="http://www.e4jconnect.com/channels/msreprq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $vcm_api_key . '"/>
	<SendReply>
		<Fetch hotelid="' . $host_user_id . '" resid="' . $this->booking['idorderota'] . '" threadid="' . $lastmessage->ota_thread_id . '" inreplyto="' . $lastmessage->ota_message_id . '"/>
		<Message><![CDATA['.$message->getContent().']]></Message>'.$attach_node.'
	</SendReply>
</MessagingSendReplyRQ>';
		
		$e4jC = new E4jConnectRequest($endp_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			$this->setError('cURL error: ' . $e4jC->getErrorMsg());
			return false;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			$this->setError(VikChannelManager::getErrorFromMap($rs));
			return false;
		}

		// we are supposed to receive the Thread ID and the new Message ID
		$resp = json_decode($rs);
		if (!$resp) {
			$this->setError('Could not decode JSON response ('.(function_exists('json_last_error') ? json_last_error() : '-').'): '.$rs);
			return false;
		}

		// prepare new message object for saving
		$newmessage  				= new stdClass;
		$newmessage->idthread 		= $lastmessage->idthread;
		$newmessage->ota_message_id = $resp->message_id;
		$newmessage->in_reply_to 	= $lastmessage->ota_message_id;
		$newmessage->sender_id 	 	= null;
		$newmessage->sender_name 	= $message->get('sender_name', null);
		$newmessage->sender_type 	= 'Hotel';
		$newmessage->recip_id 	 	= null;
		$newmessage->recip_name  	= null;
		$newmessage->recip_type  	= 'Guest';
		$newmessage->dt 		 	= $message->get('dt', JDate::getInstance()->toSql());
		$newmessage->content 	 	= $message->getContent();
		$newmessage->attachments 	= json_encode($message->getAttachments());
		$newmessage->read_dt 	 	= null;
		$newmessage->payload 	 	= null;
		$newmessage->cohost_id 	    = $message->get('cohost_id', null);

		// save new message
		$vcm_mess_id = $this->saveMessage($newmessage, (bool) $message->markPreviousReplied());
		if ($vcm_mess_id === false) {
			$this->setError('Could not store reply message.');
			return false;
		}
		// set the new ID property for the response object
		$newmessage->id = $vcm_mess_id;

		// update thread last_updated
		$thread = [
			'id' 			=> $lastmessage->idthread,
			'last_updated'  => $newmessage->dt,
		];
		$this->saveThread($thread);

		// return the result object
		$result  		 = new stdClass;
		$result->thread  = (object) $thread;
		$result->message = $newmessage;
		
		return $result;
	}

	/**
	 * @override
	 * Abstract method used to inform e4jConnect that the last-read point has changed.
	 * For Airbnb there is no need to mark messages as read.
	 *
	 * @param 	object 	 $message 	The message object. Thread details
	 * 								can be accessed through the "thread" property.
	 * 
	 * @return 	boolean  True on success, false otherwise.
	 */
	protected function notifyReadingPoint($message)
	{
		return true;
	}

	/**
	 * @override
	 * Fetches the specified payload. The given data must be converted into
	 * a standard form, readable by the system.
	 *
	 * Children classes might inherit this method as every channel can
	 * implement its own "answer-prediction" service.
	 *
	 * @param 	mixed 	$data 	The payload object or a JSON string.
	 *
	 * @return 	object 	The fetched payload.
	 */
	public function fetchPayload($data)
	{
		// invoke parent first to obtain a valid structure
		$data = parent::fetchPayload($data);

		$field = new stdClass;

		/**
		 * @todo 	Look through payload object and convert it into a standard
		 * 			structure that could be used/read by the system.
		 *
		 * 			This might be a valid form for plain text:
		 *			{
		 *				type: "text",
		 * 				hint: "Type something",
		 * 				default: "",
		 *				class: "",
		 * 			}
		 *
		 * 			This is meant for dropdowns, instead:
		 *			{
		 *				type: "list",
		 * 				hint: "Please select something",
		 * 				default: null,
		 * 				multiple: false,
		 *				class: "",
		 * 				options: {
		 *					1: "Yes",
		 *	 				0: "No",
		 * 					2: "Maybe",
		 * 				},
		 * 			}
		 */

		return $field;
	}

	/**
	 * @inheritDoc
	 * 
	 * We need to locally download the attachments as the server where they are hosted
	 * seems to remove them after their expiration (3600 seconds, 1 hour).
	 * 
	 * @since 1.9.4
	 */
	public function saveMessage($data, $prev_replied = true)
	{
		// always cast to object
		$data = (object) $data;

		$destinationPath = VBO_CUSTOMERS_PATH . '/messaging';
		$destinationUri = VBO_CUSTOMERS_URI . 'messaging/';

		if (!empty($data->attachments)) {
			$http = new JHttp;

			// decode from JSON if we have a string
			if (is_string($data->attachments)) {
				$data->attachments = (array) json_decode($data->attachments, true);
			}

			$attachments = [];

			foreach ($data->attachments as $url) {
				try {
					// check whether the URL has been already saved locally
					if (strpos($url, JUri::root()) === 0) {
						// the URL contains the current host, go ahead
						throw new Exception;
					}

					// download file from URL
					$response = $http->get($url);

					if ($response->code != 200) {
						// wrong HTTP code received
						throw new Exception;
					}

					// use the same file name used by Airbnb
					$filename = explode('?', basename($url))[0];

					// if provided, prepend the thread ID
					if (!empty($data->idthread)) {
						$filename = $data->idthread . '-' . $filename;
					}

					// save file locally
					if (!JFile::write($destinationPath . '/' . $filename, $response->body)) {
						// unable to write the file
						throw new Exception;
					}

					// overwrite the default file URL with the local one
					$url = $destinationUri . $filename;
				} catch (Exception $error) {
					// unable to download the file, keep using the original one
				}

				// register the new URL within the array
				$attachments[] = $url;
			}

			// stringify the attachments array again before the saving process
			$data->attachments = json_encode($attachments);
		}

		// invoke parent to save the message
		return parent::saveMessage($data, $prev_replied);
	}

	/**
	 * @inheritDoc
	 * 
	 * @since 1.9.18
	 */
	public function sendReaction($data)
	{
		$data = (object) $data;

		if (empty($data->idthread) || empty($data->idmessage)) {
			throw new InvalidArgumentException('Missing required data.', 400);
		}

		// get all chat threads
		$threads = array_filter($this->getThreads(), function($thread) use ($data) {
			return $thread->id == $data->idthread;
		});

		// get the desired thread
		$thread = array_shift($threads);

		if (!$thread) {
			throw new Exception('Could not find the OTA thread record.', 404);
		}

		// Airbnb User ID is mandatory for the Messaging API
		$host_user_id = $this->getAccountId();
		if (empty($host_user_id)) {
			throw new Exception('Could not find the OTA user ID.', 400);
		}

		$endpoint = "https://e4jconnect.com/channelmanager/v2/airbnb/messaging/{$host_user_id}/threads/{$thread->ota_thread_id}/messages/{$data->ota_message_id}";

		$transporter = new E4jConnectRequest($endpoint, true);
        $transporter->setBearerAuth(VikChannelManager::getApiKey(true), 'application/json')
            ->setPostFields([
            	'reaction' => $data->emoji,
            ]);

        try {
            // send message reaction
            $transporter->fetch('PUT', 'json');

            // save the message reaction
			return $this->saveReaction($data);
        } catch (Exception $e) {
            // propagate the error
            throw new Exception(sprintf('Could not send or save message reaction: %s', $e->getMessage()), $e->getCode() ?: 500);
        }
	}
}
