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

/**
 * VikBooking class handler for the guest messages.
 * 
 * @since 	1.6.13
 */
class VCMChatChannelVikbooking extends VCMChatHandler
{
	/**
	 * The channel name.
	 *
	 * @var string
	 */
	protected $channelName = 'vikbooking';

	/**
	 * Class constructor.
	 * 
	 * @param 	integer  $oid  VBO booking ID.
	 */
	public function __construct($oid)
	{
		parent::__construct($oid);

		// set sync time
		$this->set('syncTime', 10);
	}

	/**
	 * @override
	 * Loads the messages of a specific VCM Thread ID, or from all Threads.
	 * The messages are loaded from an optional start index and limit.
	 * 
	 * @param 	integer  $start 	 The start index for the messages query.
	 * @param 	integer  $limit 	 The limit for the messages query.
	 * @param 	integer  $thread_id  The VCM thread id for the messages to read.
	 * @param 	string 	 $datetime 	 An optional datetime to exclude all the newer messages.
	 * @param 	integer  $min_id 	 The threshold identifier. Messages with equals or
	 * 								 lower ID won't be taken. Use NULL to ignore this filter.
	 * @param 	boolean  $unread 	 True to retrieve only unread messages, false for read messages only.
	 * 								 Use null to ignore this filter. 
	 * 
	 * @return 	array 	 The list of thread-message objects loaded.
	 * 
	 * @since 	1.8.9 	 override implemented so that front-end chats will NOT display the OTA threads.
	 */
	public function loadThreadsMessages($start = 0, $limit = 20, $thread_id = null, $datetime = null, $min_id = null, $unread = null)
	{
		$threads = parent::loadThreadsMessages($start, $limit, $thread_id, $datetime, $min_id, $unread);

		foreach ($threads as $k => $thread)
		{
			if (strcasecmp($thread->channel, 'vikbooking'))
			{
				// unset this probable OTA thread
				unset($threads[$k]);
			}
		}

		// overwrite threads
		$this->threads = array_values($threads);

		return $this->threads;
	}

	/**
	 * Finds the last messgage in a thread.
	 * Used to reply to a guest message by the Hotel
	 * or to send another message to the guest.
	 * 
	 * @param 	int 	$idthread 	the VCM thread ID.
	 * 
	 * @return 	mixed 	false on failure, message object record otherwise.
	 */
	private function getLastThreadMessage($idthread)
	{
		$dbo = JFactory::getDbo();

		$q = "SELECT `m`.*, `t`.`ota_thread_id` FROM `#__vikchannelmanager_threads_messages` AS `m` LEFT JOIN `#__vikchannelmanager_threads` AS `t` ON `m`.`idthread`=`t`.`id` WHERE `m`.`idthread`=" . (int)$idthread . " ORDER BY `m`.`dt` DESC";

		$dbo->setQuery($q, 0, 1);
		$lastThread = $dbo->loadObject();

		if (!$lastThread) {
			$this->setError('No messages found in this thread.');
			return false;
		}

		return $lastThread;
	}

	/**
	 * @override
	 * Makes the MTHDRD request to e4jConnect.com to get all threads
	 * and related messages for the current Booking.com reservation ID.
	 * 
	 * @return 	mixed 	false on failure, stdClass object with stored information otherwise.
	 */
	protected function downloadThreads()
	{
		// data stored information
		$stored = new stdClass;
		$stored->newThreads  = 0;
		$stored->newMessages = 0;

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
		// prepare new thread object to be saved
		$newthread 					= new stdClass;
		$newthread->idorder 		= $this->booking['id'];
		$newthread->idorderota 		= null;
		$newthread->channel 		= $this->channelName;
		$newthread->ota_thread_id 	= null;
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
		$newmessage->ota_message_id = null;
		$newmessage->sender_id 	 	= null;
		$newmessage->sender_name 	= $message->get('sender_name', null);
		$newmessage->sender_type 	= 'Hotel';
		$newmessage->recip_type 	= 'Guest';
		$newmessage->dt 			= JDate::getInstance()->toSql();
		$newmessage->content 		= $message->getContent();
		$newmessage->attachments 	= json_encode($message->getAttachments());

		if ($message->get('guest')) {
			$newmessage->sender_type = 'Guest';
			$newmessage->recip_type  = 'Hotel';

			$customer = VikBooking::getCPinInstance()->getCustomerFromBooking($this->booking['id']);
			$newmessage->sender_name = $customer['first_name'] ?? null;
		}

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
		$lastmessage = $this->getLastThreadMessage($message->get('idthread', 0));
		if (!$lastmessage) {
			return false;
		}

		// prepare new message object for saving
		$newmessage  				= new stdClass;
		$newmessage->idthread 		= $lastmessage->idthread;
		$newmessage->ota_message_id = null;
		$newmessage->in_reply_to 	= $lastmessage->id;
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

		if ($message->get('guest')) {
			$newmessage->sender_type = 'Guest';
			$newmessage->recip_type  = 'Hotel';

			$customer = VikBooking::getCPinInstance()->getCustomerFromBooking($this->booking['id']);
			$newmessage->sender_name = $customer['first_name'] ?? null;
		}

		// save new message
		$vcm_mess_id = $this->saveMessage($newmessage, (bool) $message->markPreviousReplied());
		if ($vcm_mess_id === false) {
			$this->setError('Could not store reply message.');
			return false;
		}
		// set the new ID property for the response object
		$newmessage->id = $vcm_mess_id;

		// update thread last_updated
		$thread = array(
			'id' 			=> $lastmessage->idthread,
			'last_updated'  => $newmessage->dt,
		);
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
	 * Children classes can inherit this method and return true
	 * in case they support CHAT notifications.
	 *
	 * @return 	boolean  Always true.
	 */
	public function supportNotifications()
	{
		return true;
	}
}
