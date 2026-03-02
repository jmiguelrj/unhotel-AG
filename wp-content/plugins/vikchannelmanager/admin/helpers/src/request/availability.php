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
 * Request availability update helper mainly for pending reservations.
 * 
 * @since 		1.8.20
 * 
 * @requires 	VBO >= 1.16.5 (J) - 1.6.5 (WP)
 */
final class VCMRequestAvailability extends JObject
{
	/**
	 * The singleton instance of the class.
	 *
	 * @var  VCMRequestAvailability
	 */
	private static $instance = null;

	/**
	 * @var 	array 	list of booking IDs processed.
	 */
	private $processed = [];

	/**
	 * Proxy to construct the object and bind information.
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
	 * Tells whether the Channel Manager should run an availability update request
	 * for all channels in case of pending (stand-by) reservations.
	 * 
	 * @return 		bool 	true if pending reservations should occupy the rooms, or false.
	 */
	public static function syncWhenPending()
	{
		static $sync_pending = null;

		if ($sync_pending === null) {
			$sync_pending = (bool)VCMFactory::getConfig()->get('sync_pending', (int)VCMPlatformDetection::isWordPress());
		}

		return $sync_pending;
	}

	/**
	 * Loads, and eventually stores, the current temporarily locked records for the given room ID.
	 * The returned records, if any, will help calculate the proper number of units left by also
	 * considering the number of units temporarily locked due to pending payments. On top of that,
	 * if any temporary record is fetched, the system will store the information for eventually
	 * unlocking the rooms involved in the reservation and updating their proper availability.
	 * 
	 * @param 	int 	$id_room 	 the VBO room ID.
	 * @param 	int 	$checkin_ts  the earliest checkin timestamp.
	 * @param 	bool 	$store_lock  if true, the pending lock details will be stored.
	 * 
	 * @return 	array 				 list of temporarily locked room records.
	 */
	public function fetchTemporaryBusyRecords($id_room, $checkin_ts = 0, $store_lock = true)
	{
		if (!$checkin_ts) {
			$checkin_ts = $this->get('checkin', time());
		}

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn(['id', 'checkin']))
			->select($dbo->qn('realback', 'checkout'))
			->select($dbo->qn('until'))
			->from($dbo->qn('#__vikbooking_tmplock'))
			->where($dbo->qn('idroom') . ' = ' . (int)$id_room)
			->where($dbo->qn('checkout') . ' > ' . $checkin_ts)
			->where($dbo->qn('until') . ' > ' . time());

		$dbo->setQuery($q);
		$tmp_busy = $dbo->loadAssocList();

		if ($store_lock && $tmp_busy && $this->get('id')) {
			// store the information in order to be able to eventually unlock such rooms later on
			$this->storeTemporaryBusyRecords($tmp_busy);
		}

		/**
		 * In case of request-to-book pending reservation, map the list with the channel source
		 * and the "request-to-book" key to allow any process to identify these records.
		 * 
		 * @since 	1.8.22
		 */
		$booking_type 	 = $this->get('type', '');
		$booking_channel = $this->get('channel', '');
		if ($tmp_busy && $booking_type && !strcasecmp($booking_type, 'request') && $booking_channel) {
			// map the list by adding the channel source
			$tmp_busy = array_map(function($record) use ($booking_channel) {
				// set the "request-to-book" key
				$record['rtb'] = 1;

				// set the channel source key
				$record['channel'] = $booking_channel;

				// return the modified record
				return $record;
			}, $tmp_busy);
		}

		// return the list of temporary locked records
		return $tmp_busy;
	}

	/**
	 * Monitors if any previously locked room due to pending payment should be released.
	 * If any expired reservation is still not confirmed, the involved rooms will be updated.
	 * 
	 * @param 	bool 	$force 	true to always run without checking the last execution.
	 * 
	 * @return 	int 			number of freeing records processed.
	 */
	public function monitorPendingLocks($force = false)
	{
		/**
		 * @deprecated 1.9  Without replacement from 1.10.
		 */
		if (!$force) {
			// make sure the action was not recently triggered by another process
			$last_process_dt = VCMFactory::getConfig()->get('pendinglocks_last_process_dt');

			if ($last_process_dt && (time() - strtotime($last_process_dt)) < 300) {
				// minimum seconds of interval between processes not met: do not run
				return 0;
			}
		}

		// make sure to load the Sync class
		require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'synch.vikbooking.php');

		// check if language should be loaded
		$this->checkLanguageStatus();

		$dbo = JFactory::getDbo();

		// immediately free up expired records that keep the rooms temporarily locked on VikBooking
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikbooking_tmplock'))
			->where($dbo->qn('until') . ' <= ' . time());

		$dbo->setQuery($q);
		$dbo->execute();

		// check if any previously locked room is ready to be freed up
		$q = $dbo->getQuery(true)
			->select($dbo->qn(['p.id', 'p.vbo_order_id', 'p.until', 'o.status']))
			->from($dbo->qn('#__vikchannelmanager_pendinglocks', 'p'))
			->leftJoin($dbo->qn('#__vikbooking_orders', 'o') . ' ON ' . $dbo->qn('p.vbo_order_id') . ' = ' . $dbo->qn('o.id'))
			->where($dbo->qn('p.until') . ' <= ' . time());

		try {
			$dbo->setQuery($q);
			$pending_locks = $dbo->loadAssocList();
		} catch (Exception $e) {
			// prevent database errors from crashing the execution
			$pending_locks = [];
		}

		foreach ($pending_locks as $pending) {
			// immediately delete the record so that next execution won't fetch it
			$this->deletePendingLockRecord($pending['id'], $pending['vbo_order_id']);

			if (!in_array($pending['status'], ['standby', 'cancelled'])) {
				/**
				 * Reservation could have become confirmed or it may have been removed completely, continue to next one.
				 * We can release the previously occupied rooms only if the booking still exists, and we should do it
				 * only if it's still pending or if it went cancelled. A booking confirmation would automatically update
				 * the availability for the involved rooms, but a purge removal before this process runs would keep the
				 * rooms occupied on the channels, because a reference to the booking ID and rooms booked would be missing.
				 */
				continue;
			}

			// reservation is still pending payment, or it may have become cancelled automatically: free up the room(s)
			$vcm = new SynchVikBooking($pending['vbo_order_id']);
			$result = $vcm->setSkipCheckAutoSync()
				->setFromCancellation([
					'id' 			=> $pending['vbo_order_id'],
					'notif_content' => JText::_('VCM_PAYMENT_EXPIRED'),
				])
				->sendRequest();

			if ($result) {
				// Booking History
				VikBooking::getBookingHistoryInstance()->setBid($pending['vbo_order_id'])->store('CM', JText::_('VCM_ROOMS_TMP_LOCK_RELEASED'));
			}
		}

		return count($pending_locks);
	}

	/**
	 * Returns the extra content description to be used when storing
	 * a notification for a pending lock record (pending payment).
	 * 
	 * @return 	string
	 */
	public function describePendingLockSync()
	{
		if ($this->get('status') != 'standby') {
			return '';
		}

		// check if language should be loaded
		$this->checkLanguageStatus();

		return JText::_('VCM_PENDING_PAYMENT');
	}

	/**
	 * Stores the information for the temporarily locked room records.
	 * 
	 * @param 	array 	$busy 	list of temporarily locked records involved.
	 * 
	 * @return 	bool
	 */
	private function storeTemporaryBusyRecords(array $busy)
	{
		if ($this->get('status') != 'standby' || in_array($this->get('id'), $this->processed)) {
			// do nothing if not pending status or if already processed
			return false;
		}

		// cache reservation ID
		$this->processed[] = $this->get('id');

		// lock until timestamp
		$lock_until = $busy[0]['until'];

		if (!$lock_until || $lock_until <= time() || $this->bookingHasPendingLock()) {
			// prevent expired or duplicate records to be stored
			return false;
		}

		// make sure to exclude booking inquiries, but to include request-to-book reservations
		$booking_type = $this->get('type', '');
		if ($booking_type && !strcasecmp($booking_type, 'inquiry')) {
			// do not alter the availability in case of such pending inquiries
			return false;
		}

		// store information
		$dbo = JFactory::getDbo();

		$pending = new stdClass;
		$pending->vbo_order_id = (int)$this->get('id');
		$pending->until 	   = $lock_until;
		$pending->dt 		   = JFactory::getDate()->toSql();

		try {
			// insert record for later monitoring
			$dbo->insertObject('#__vikchannelmanager_pendinglocks', $pending, 'id');

			if (isset($pending->id)) {
				// check language
				$this->checkLanguageStatus();

				// Booking History
				VikBooking::getBookingHistoryInstance()->setBid($pending->vbo_order_id)->store('CM', JText::_('VCM_PENDING_LOCK'));
			}
		} catch (Exception $e) {
			// do nothing
			return false;
		}

		return !empty($pending->id);
	}

	/**
	 * Tells whether the current booking ID was already stored for later monitoring.
	 * 
	 * @return 	bool
	 */
	private function bookingHasPendingLock()
	{
		if (!$this->get('id')) {
			return false;
		}

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select('COUNT(1)')
			->from($dbo->qn('#__vikchannelmanager_pendinglocks'))
			->where($dbo->qn('vbo_order_id') . ' = ' . (int)$this->get('id'));

		$dbo->setQuery($q);

		return (bool)$dbo->loadResult();
	}

	/**
	 * Deletes the given pending lock record ID, and eoptionally unlocks the record on VBO as well.
	 * 
	 * @param 	int 	$id 		the record ID to remove.
	 * @param 	int 	$vbo_id 	optional VikBooking reservation ID.
	 * 
	 * @return 	void
	 */
	private function deletePendingLockRecord($id, $vbo_bid = null)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikchannelmanager_pendinglocks'))
			->where($dbo->qn('id') . ' = ' . (int)$id);

		$dbo->setQuery($q);
		$dbo->execute();

		if ($vbo_bid) {
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikbooking_tmplock'))
				->where($dbo->qn('idorder') . ' = ' . (int)$vbo_bid)
				->where($dbo->qn('until') . ' <= ' . time());

			$dbo->setQuery($q);
			$dbo->execute();
		}
	}

	/**
	 * Checks if language translations are active, or they will be loaded.
	 * Useful to handle processes that may run outside the plugin (Cron).
	 * 
	 * @return 	bool 	true if language translations were available or false.
	 */
	private function checkLanguageStatus()
	{
		if (JText::_('VCM_ROOMS_TMP_LOCK_RELEASED') != 'VCM_ROOMS_TMP_LOCK_RELEASED') {
			// language translations are available
			return true;
		}

		// attempt to load the language handler
		$lang = JFactory::getLanguage();

		$lang->load('com_vikchannelmanager', (VBOPlatformDetection::isWordPress() ? VIKBOOKING_LANG : JPATH_ADMINISTRATOR), $lang->getTag(), true);

		if (VBOPlatformDetection::isWordPress()) {
			$lang->attachHandler(VIKCHANNELMANAGER_LIBRARIES . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . 'admin.php', 'vikchannelmanager');
		}

		return false;
	}
}
