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
 * Helper class to handle auto-responding features to OTA guest messages.
 * 
 * @since 	1.8.21
 */
final class VCMChatAutoresponder
{
	/**
	 * The singleton instance of the class.
	 *
	 * @var  VCMChatAutoresponder
	 */
	private static $instance = null;

	/**
	 * @var  string
	 */
	private $auto_message = '';

	/**
	 * @var  array
	 */
	private $message_record = [];

	/**
	 * Proxy for immediately getting the object.
	 * 
	 * @return 	self
	 */
	public static function getInstance()
	{
		if (is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Class constructor will ensure the autoresponder is ready.
	 */
	public function __construct()
	{
		$this->setup();
	}

	/**
	 * Monitors and eventually processes the scheduled auto responses.
	 * 
	 * @return 	int 	number of schedules processed.
	 */
	public function watchSchedules()
	{
		/**
		 * On WordPress this runs within a runtime Cron through WP-Cron,
		 * on Joomla instead this runs at any back-end execution. We need
		 * to ensure running is allowed depending on the platform.
		 */
		if (!$this->runningAllowed()) {
			return 0;
		}

		// get the autoresponder message
		$responder_message = $this->getMessage();

		if (empty($responder_message)) {
			// do not proceed in case of an empty message
			return 0;
		}

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn([
				's.id',
				's.idthread',
				's.responder_dt',
				't.idorder',
				't.idorderota',
				't.ota_thread_id',
			]))
			->from($dbo->qn('#__vikchannelmanager_threads_schedules', 's'))
			->leftJoin($dbo->qn('#__vikchannelmanager_threads', 't') . ' ON ' . $dbo->qn('s.idthread') . ' = ' . $dbo->qn('t.id'))
			->where($dbo->qn('s.responder_dt') . ' <= ' . $dbo->q(JDate::getInstance('now')->toSql()))
			->order($dbo->qn('s.id') . ' DESC');

		try {
			$dbo->setQuery($q, 0, 5);
			$schedules = $dbo->loadAssocList();
		} catch (Throwable $e) {
			// prevent any errors from being thrown
			$schedules = [];
		}

		if (!$schedules) {
			return 0;
		}

		$processed = 0;

		foreach ($schedules as $schedule) {
			// immediately delete this record for any future execution
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikchannelmanager_threads_schedules'))
				->where($dbo->qn('id') . ' = ' . (int)$schedule['id']);

			$dbo->setQuery($q);
			$dbo->execute();

			// make sure the current thread does not have a message sent by the host/hotel
			$q = $dbo->getQuery(true)
				->select(1)
				->from($dbo->qn('#__vikchannelmanager_threads_messages'))
				->where($dbo->qn('idthread') . ' = ' . (int)$schedule['idthread'])
				->where($dbo->qn('sender_type') . ' != ' . $dbo->q('guest'));

			$dbo->setQuery($q);
			if ($dbo->loadResult()) {
				// skip thread because the host/hotel did respond
				continue;
			}

			// access the booking information
			$vbo_booking = VikBooking::getBookingInfoFromID($schedule['idorder']);
			if (!$vbo_booking || !strcasecmp($vbo_booking['status'], 'cancelled')) {
				// skip non existing or cancelled reservations
				continue;
			}

			// check if the message should be translated
			$guest_message = $responder_message;
			if (!empty($vbo_booking['lang'])) {
				// get the translated message, if available
				$guest_message = $this->getMessage($vbo_booking['lang']);
			}

			// trigger event to allow third-party plugins to manipulate the automatic guest message at runtime
			VCMFactory::getPlatform()->getDispatcher()->trigger('onBeforeSendAutoresponderMessageVikChannelManager', [$schedule, $vbo_booking, &$guest_message]);

			if (empty($guest_message)) {
				continue;
			}

			// notify the guest with the auto-responder message
			$messaging = VCMChatMessaging::getInstance(array_merge($schedule, $vbo_booking));
			$messaging->markPreviousReplied(false);

			$result = $messaging
				->setMessage($guest_message)
				->sendGuestMessage('reply');

			if ($result) {
				$processed++;
			}
		}

		return $processed;
	}

	/**
	 * Schedules an auto response for a new guest message.
	 * 
	 * @param 	int 	$thread_id 	 the thread/conversation ID to schedule.
	 * @param 	string 	$message_dt  the message creation date in Y-m-d format.
	 * 
	 * @return 	bool
	 */
	public function scheduleResponse($thread_id, $message_dt = null)
	{
		$dbo = JFactory::getDbo();

		if (!$message_dt) {
			// default to right now
			$message_dt = 'now';
		}

		// build monitoring minimum date and time for this thread
		$monitor_mindt = JDate::getInstance($message_dt)->modify('+1 hour')->toSql();

		$schedule_record = new stdClass;

		$schedule_record->idthread 	   = (int)$thread_id;
		$schedule_record->responder_dt = $monitor_mindt;

		try {
			return $dbo->insertObject('#__vikchannelmanager_threads_schedules', $schedule_record, 'id');
		} catch (Throwable $t) {
			// do nothing
		}

		return false;
	}

	/**
	 * Returns the default autoresponder message, eventually translated.
	 * 
	 * @param 	string 	$lang 	optional booking language to translate the message.
	 * 
	 * @return 	string
	 */
	public function getMessage($lang = null)
	{
		if ($lang && $this->message_record) {
			// access the global translator object
			$vbo_tn = VikBooking::getTranslator();

			// force the translation to start in the given language, if available
			$vbo_tn::$force_tolang = $lang;

			// translate content
			$record_list = [$this->message_record];
			$vbo_tn->translateContents($record_list, '#__vikbooking_texts');

			return $record_list[0]['setting'];
		}

		// default message
		return $this->auto_message;
	}

	/**
	 * Sets the default autoresponder message.
	 * 
	 * @param 	string 	$msg 	the default message string.
	 * 
	 * @return 	self
	 */
	public function setMessage($msg)
	{
		$this->auto_message = (string)$msg;

		return $this;
	}

	/**
	 * Returns the default autoresponder message.
	 * 
	 * @return 	string
	 */
	public function getDefaultMessage()
	{
		$def_message = JText::_('VCM_AUTORESPONDER_DEF_MESSAGE');

		if ($def_message != 'VCM_AUTORESPONDER_DEF_MESSAGE') {
			return $def_message;
		}

		// fallback to a static string
		return 'Thanks for getting in touch. We will get back to you as soon as possible.';
	}

	/**
	 * Ensures the system is ready with the default message.
	 * 
	 * @return 	void
	 */
	private function setup()
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn([
				'id',
				'setting',
			]))
			->from($dbo->qn('#__vikbooking_texts'))
			->where($dbo->qn('param') . ' = ' . $dbo->q('messaging_autoresponder_txt'));

		$dbo->setQuery($q, 0, 1);
		$text = $dbo->loadObject();

		if (!$text) {
			// add record for the first time
			$text_record = new stdClass;

			$text_record->param   = 'messaging_autoresponder_txt';
			$text_record->exp     = 'Messaging Autoresponder Text';
			$text_record->setting = $this->getDefaultMessage();

			$dbo->insertObject('#__vikbooking_texts', $text_record, 'id');

			$this->setMessage($text_record->setting);

			return;
		}

		// store record for later translations
		$this->message_record = (array)$text;

		// set current value
		$this->setMessage($text->setting);

		return;
	}

	/**
	 * Tells whether the process should run depending on the platform.
	 * 
	 * @return 	bool
	 * 
	 * @deprecated 1.9  Without replacement from 1.10.
	 */
	private function runningAllowed()
	{
		/**
		 * @todo return true only in case VikBooking is up to date, otherwise fallback to the previous method
		 */
		if (VCMPlatformDetection::isWordPress() || method_exists('VBOFactory', 'getCrontabSimulator')) {
			return true;
		}

		// check last execution on Joomla

		$session = JFactory::getSession();
		$config  = VCMFactory::getConfig();
		$now 	 = time();

		// check last execution from session
		$last_exec = $session->get('autoresponder_last_exec', 0, 'vcm');

		if (!$last_exec) {
			// check last execution from db
			$last_exec = $config->getInt('autoresponder_last_exec', 0);
		}

		if (!$last_exec) {
			// register last execution for the first time
			$session->set('autoresponder_last_exec', $now, 'vcm');
			$config->set('autoresponder_last_exec', $now);

			// execution should not be allowed after an update
			return false;
		}

		// running is allowed once every hour
		if (($now - $last_exec) >= (60 * 60)) {
			// update last execution on session and db
			$session->set('autoresponder_last_exec', $now, 'vcm');
			$config->set('autoresponder_last_exec', $now);

			return true;
		}

		return false;
	}
}
