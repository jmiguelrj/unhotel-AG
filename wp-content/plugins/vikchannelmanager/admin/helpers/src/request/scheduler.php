<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2023 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Request scheduler for failure data transmission.
 * 
 * @since 	1.8.20
 */
final class VCMRequestScheduler extends JObject
{
	/**
	 * The singleton instance of the class.
	 *
	 * @var  VCMRequestScheduler
	 */
	private static $instance = null;

	/**
	 * @var  int  minimum execution interval in seconds.
	 */
	public $min_intval_secs = 300;

	/**
	 * Proxy to construct the object.
	 * 
	 * @param 	array|object  $data  optional data to bind.
	 * 
	 * @return 	self
	 */
	public static function getInstance($data = [])
	{
		if (is_null(static::$instance) || $data) {
			static::$instance = new static($data);
		}

		return static::$instance;
	}

	/**
	 * Stores a failure data transmission record that should be retried.
	 * 
	 * @return 	bool
	 */
	public function store()
	{
		if (!$this->get('payload') || !$this->get('request') || !$this->get('channels')) {
			return false;
		}

		$dbo = JFactory::getDbo();

		// build record object
		$record = new stdClass;
		$record->dt 		  = JFactory::getDate()->toSql();
		$record->vbo_order_id = (int)$this->get('order_id', 0);
		$record->payload 	  = $this->get('payload');
		$record->request 	  = $this->get('request', '');
		$record->channels 	  = $this->get('channels');
		$record->errno 		  = (int)$this->get('errno', 0);
		$record->errmsg 	  = $this->get('errmsg', '');

		$dbo->insertObject('#__vikchannelmanager_rqschedules', $record, 'id');

		return !empty($record->id);
	}

	/**
	 * Stores a notification in the db for VikChannelManager.
	 * 
	 * @param 	int 	$type 		type 0 (Error), 1 (Success), 2 (Warning).
	 * @param 	string 	$from 		the notification issuer.
	 * @param 	string 	$cont 		the notification message.
	 * @param 	int 	$idordervb 	the involved VBO booking ID.
	 * 
	 * @return 	mixed 				false in case of failure, notification ID stored otherwise.
	 */
	public function storeNotification($type, $from, $cont, $idordervb = 0)
	{
		$dbo = JFactory::getDbo();

		$notif = new stdClass;
		$notif->ts 		  = time();
		$notif->type 	  = (int)$type;
		$notif->from 	  = $from;
		$notif->cont 	  = $cont;
		$notif->idordervb = (int)$idordervb;
		$notif->read 	  = 0;

		$dbo->insertObject('#__vikchannelmanager_notifications', $notif, 'id');

		return isset($notif->id) ? $notif->id : false;
	}

	/**
	 * Stores multiple notifications in the db for VikChannelManager.
	 * 
	 * @param 	array 	$arr_rs 	list of response strings.
	 * @param 	int 	$idordervb 	the involved VBO booking ID.
	 * 
	 * @return 	bool
	 */
	public function storeMultipleNotifications(array $arr_rs, $idordervb = 0)
	{
		$dbo = JFactory::getDbo();

		$gen_type = 1;
		foreach ($arr_rs as $chid => $chrs) {
			if (substr($chrs, 0, 9) == 'e4j.error') {
				$gen_type = 0;
				break;
			} elseif (substr($chrs, 0, 11) == 'e4j.warning') {
				$gen_type = 2;
			}
		}

		$result = false;

		// store parent notification
		$id_parent = $this->storeNotification($gen_type, 'VCM', 'Availability Update RQ', $idordervb);

		if ($id_parent) {
			// store children notifications
			foreach ($arr_rs as $chid => $chrs) {
				// build child notification object
				$child_notif = new stdClass;
				$child_notif->id_parent = $id_parent;
				if (substr($chrs, 0, 9) == 'e4j.error') {
					$child_notif->type = 0;
				} elseif (substr($chrs, 0, 11) == 'e4j.warning') {
					$child_notif->type = 2;
				} else {
					$child_notif->type = 1;
				}
				$child_notif->cont = $chrs;
				$child_notif->channel = (int)$chid;

				// store child notification object
				$result = $dbo->insertObject('#__vikchannelmanager_notification_child', $child_notif, 'id') || $result;
			}
		}

		return $result;
	}

	/**
	 * Retries all pending data transmission records that previously failed. By default,
	 * only one failure per execution is retried, and its last_retry date is updated so
	 * that the next execution will eventually parse another failure in cascade.
	 * 
	 * @param 	bool 	$force 	true to always run without checking the last execution.
	 * @param 	int 	$lim 	the limit of records to process, defaults to 1 per execution.
	 * 
	 * @return 	int 			number of records processed, where -1 indicates no running.
	 */
	public function retry($force = false, $lim = 1)
	{
		$config = VCMFactory::getConfig();

		/**
		 * @deprecated 1.9  Without replacement from 1.10.
		 */
		if (!$force) {
			// make sure the action was not recently triggered by another process
			$last_process_dt = $config->get('schedules_last_process_dt');

			if ($last_process_dt && (time() - strtotime($last_process_dt)) < $this->min_intval_secs) {
				// minimum seconds of interval between processes not met: do not run
				return -1;
			}

			if ($config->get('schedules_disabled')) {
				// schedules have been manually disabled through a forced configuration setting
				return -1;
			}
		}

		// instantly update the last processing date
		$config->set('schedules_last_process_dt', date('Y-m-d H:i:s'));

		// number of schedules processed
		$processed = 0;

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikchannelmanager_rqschedules'))
			->where($dbo->qn('status') . ' = 0')
			->order('IFNULL(' . $dbo->qn('last_retry') . ', ' . $dbo->qn('dt') . ') ASC');

		$dbo->setQuery($q, 0, $lim);
		$schedules = $dbo->loadObjectList();

		foreach ($schedules as $schedule) {
			try {
				if ($this->retrySchedule((new JObject($schedule)))) {
					$processed++;
				}
			} catch (Exception $e) {
				// store the error message caught by updating the record
				$schedule->errmsg .= "\nError:\n" . $e->getMessage();
				$dbo->updateObject('#__vikchannelmanager_rqschedules', $schedule, 'id');
			}
		}

		return $processed;
	}

	/**
	 * Retries the execution of a data transmission record. Calling this method is
	 * sufficient to ensure the undelivered availability update requests will be retried.
	 * 
	 * @param 	JObject 	$schedule 	the record to retry wrapped in a JObject instance.
	 * 
	 * @return 	bool 					true if the execution went well, or false.
	 */
	private function retrySchedule($schedule)
	{
		$dbo = JFactory::getDbo();

		// build the endpoint URI
		$uri = 'https://e4jconnect.com/channelmanager/?r=' . $schedule->get('request', 'a') . '&c=' . $schedule->get('channels', 'channels');

		// get the attempt number
		$attempt = (int)$schedule->get('retries', 0) + 1;

		// get the involved booking ID, if any
		$vbo_order_id = $schedule->get('vbo_order_id', $this->get('order_id'));

		// raw record object
		$raw_record = (object)$schedule->getProperties();
		// always update the last_retry and retry attempts properties
		$raw_record->last_retry = JFactory::getDate()->toSql();
		$raw_record->retries 	= $attempt;

		// start transporter
		$e4jC = new E4jConnectRequest($uri);
		$e4jC->setPostFields($schedule->get('payload'));
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();

		if ($e4jC->getErrorNo()) {
			// store erroneous notification with data transmission error message
			$this->storeNotification(0, 'VCM', $e4jC->getErrorMsg() . "\nRetry #{$attempt}", $vbo_order_id);

			// update schedule object
			$dbo->updateObject('#__vikchannelmanager_rqschedules', $raw_record, 'id');

			return false;
		}

		// transmission was successful, set the record status to 1
		$raw_record->status = 1;

		// parse the response to store a new notification (request was transmitted correctly)
		if (substr($rs, 0, 4) == 'e4j.') {
			// response for a single channel request
			if (substr($rs, 0, 9) == 'e4j.error') {
				if ($rs != 'e4j.error.Skip') {
					$this->storeNotification(0, 'VCM', $rs, $vbo_order_id);
				}
			} else {
				$this->storeNotification(1, 'VCM', 'e4j.OK.Channels.AR_RQ', $vbo_order_id);
			}
		} else {
			// JSON Response for multiple channels request
			$arr_rs = json_decode($rs, true);
			if (is_array($arr_rs) && $arr_rs) {
				$this->storeMultipleNotifications($arr_rs, $vbo_order_id);
			}
		}

		// update schedule object record so that it won't be retried, no matter what was the response
		$dbo->updateObject('#__vikchannelmanager_rqschedules', $raw_record, 'id');

		return true;
	}
}
