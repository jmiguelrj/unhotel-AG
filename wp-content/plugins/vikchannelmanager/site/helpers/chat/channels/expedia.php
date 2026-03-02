<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Expedia class handler for the guest messaging.
 * 
 * @since 	1.8.27
 */
class VCMChatChannelExpedia extends VCMChatHandler
{
	/**
	 * The channel name.
	 *
	 * @var string
	 */
	protected $channelName = 'expedia';

	/**
	 * Finds the Expedia Hotel ID for the given booking.
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
			->where($dbo->qn('x.idchannel') . ' = ' . (int)VikChannelManagerConfig::EXPEDIA);

		$dbo->setQuery($q);
		$rows = $dbo->loadAssocList();

		if (!$rows) {
			return false;
		}

		foreach ($rows as $row) {
			$params = !empty($row['prop_params']) ? json_decode($row['prop_params'], true) : array();
			if (!is_array($params)) {
				continue;
			}
			foreach ($params as $account) {
				if (!empty($account)) {
					/**
					 * We get the first parameter (should be 'hotelid') from the mapped rooms as
					 * multiple rooms booked should still belong to the same Expedia Hotel ID.
					 */
					return $account;
				}
			}
		}

		// nothing found
		return false;
	}

	/**
	 * Finds the last message in a thread sent by the guest/host.
	 * Used to reply to a guest message by the Hotel.
	 * 
	 * @param 	int 	$idthread 	the VCM thread ID.
	 * @param 	string 	$reciptype 	the recipient type, defaults to Hotel.
	 * 
	 * @return 	mixed 	false on failure, message object record otherwise.
	 */
	protected function getLastThreadMessage($idthread, $reciptype = 'Hotel')
	{
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
		 */
		if (!empty($this->booking['ota_type_data'])) {
			$ota_type_data = is_string($this->booking['ota_type_data']) ? json_decode($this->booking['ota_type_data'], true) : (array)$this->booking['ota_type_data'];
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
	 */
	protected function prepareOptions()
	{
		$this->set('syncTime', 10);

		return;
	}

	/**
	 * @override
	 * Check here if we are uploading a supported attachment.
	 * Expedia allow only images in PNG/JPG/GIF formats and PDF files.
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

		return preg_match("/^image\/(png|jpe?g|gif)|application\/(?:pdf)$/", $file['type']);
	}

	/**
	 * @override
	 * Makes the MTHDRD request to e4jConnect.com to get all threads
	 * and related messages for the current Expedia reservation ID.
	 * 
	 * @return 	mixed 	false on failure, stdClass object with stored information otherwise.
	 */
	protected function downloadThreads()
	{
		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			$this->setError('Missing API Key');
			return false;
		}

		if (empty($this->booking['idorderota'])) {
			$this->setError('Missing OTA Booking ID');
			return false;
		}

		// Expedia Hotel ID is mandatory for the Messaging API
		$hotelid = $this->getAccountId();
		if (empty($hotelid)) {
			$this->setError('Empty Expedia Hotel ID');
			return false;
		}

		// check if an ota thread id is available for this booking
		$thread_id = '';
		$all_threads = $this->getBookingOtaThreads();
		if ($all_threads) {
			$thread_id = $all_threads[0];
		}

		$endp_url = "https://hotels.e4jconnect.com/channelmanager/?r=mthdrd&c=" . $this->channelName;

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager MTHDRD Request e4jConnect.com - Expedia -->
<MessagingThreadReadRQ xmlns="http://www.e4jconnect.com/channels/mthdrdrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $apikey . '"/>
	<ReadThreadsMessages>
		<Fetch hotelid="' . $hotelid . '" resid="' . $this->booking['idorderota'] . '" threadid="' . $thread_id . '"/>
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
			$new_messages = array();
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
				 * Added support for message language (2-char lang code).
				 * 
				 * @since 	1.8.27
				 */
				if (!empty($message->lang)) {
					$check_message->lang = substr((string) $message->lang, 0, 2);
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
		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			$this->setError('Missing API Key');
			return false;
		}

		if (empty($this->booking['idorderota'])) {
			$this->setError('Missing OTA Booking ID');
			return false;
		}

		// Expedia Hotel ID is mandatory for the Messaging API to request the auth token
		$hotelid = $this->getAccountId();
		if (empty($hotelid)) {
			$this->setError('Empty Expedia Hotel ID');
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

		$endp_url = "https://hotels.e4jconnect.com/channelmanager/?r=msrep&c=".$this->channelName;

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager MSREP Request e4jConnect.com - Expedia -->
<MessagingSendReplyRQ xmlns="http://www.e4jconnect.com/channels/msreprq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$apikey.'"/>
	<SendReply>
		<Fetch hotelid="'.$hotelid.'" resid="'.$this->booking['idorderota'].'"/>
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
		// subject and type are usually set to these values at the first message
		$newthread->subject 		= $message->get('subject', 'Email');
		$newthread->type 			= 'Contextual';
		// set last updated to current date time
		$newthread->last_updated 	= JDate::getInstance()->toSql();

		// save new thread
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
		 */
		$lastmessage = $this->getLastThreadMessage($message->get('idthread', 0));
		if (!$lastmessage) {
			return false;
		}

		$apikey = VikChannelManager::getApiKey(true);
		if (empty($apikey)) {
			$this->setError('Missing API Key');
			return false;
		}

		if (empty($this->booking['idorderota'])) {
			$this->setError('Missing OTA Booking ID');
			return false;
		}

		// Expedia Hotel ID is mandatory for the Messaging API to request the auth token
		$hotelid = $this->getAccountId();
		if (empty($hotelid)) {
			$this->setError('Empty Expedia Hotel ID');
			return false;
		}

		// compose payload from last guest message (not needed)
		$sendpayload = [];

		// message attachment nodes
		$attach_node = '';
		if (count($message->getAttachments())) {
			$attach_node = "\n".'<Attachments type="' . $message->get('attachmentsType', 'AttachmentImages') . '">' . "\n";
			foreach ($message->getAttachments() as $furi) {
				$attach_node .= '<Attachment>' . $furi . '</Attachment>' . "\n";
			}
			$attach_node .= '</Attachments>';
		}

		$endp_url = "https://hotels.e4jconnect.com/channelmanager/?r=msrep&c=".$this->channelName;

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager MSREP Request e4jConnect.com - Expedia -->
<MessagingSendReplyRQ xmlns="http://www.e4jconnect.com/channels/msreprq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$apikey.'"/>
	<SendReply>
		<Fetch hotelid="'.$hotelid.'" resid="'.$this->booking['idorderota'].'" threadid="'.$lastmessage->ota_thread_id.'" inreplyto="'.$lastmessage->ota_message_id.'"/>
		<Message><![CDATA['.$message->getContent().']]></Message>'.$attach_node.'
		<Payload><![CDATA['.json_encode($sendpayload).']]></Payload>
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
		$newmessage->payload 	 	= json_encode($sendpayload);
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

		// fetch message payload
		$newmessage->payload = $this->fetchPayload($newmessage->payload);

		// return the result object
		$result  		 = new stdClass;
		$result->thread  = (object) $thread;
		$result->message = $newmessage;
		
		return $result;
	}

	/**
	 * @override
	 * Abstract method used to inform e4jConnect that the last-read point has changed.
	 * For Expedia there is no need to mark messages as read.
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
}
