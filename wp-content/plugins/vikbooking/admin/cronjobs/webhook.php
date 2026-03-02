<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

class VikBookingCronJobWebhook extends VBOCronJob
{
	// do not need to track the elements
	use VBOCronTrackerUnused;

	/**
	 * This method should return all the form fields required to collect the information
	 * needed for the execution of the cron job.
	 * 
	 * @return  array  An associative array of form fields.
	 */
	public function getForm()
	{
		/**
		 * Build a list of history events that can be filtered during the cron job execution.
		 * 
		 * @since 	1.16.9 (J) - 1.6.9 (WP)
		 */
		$history_events = [];
		$parsed_events  = [];

		// history object
		$history_obj = VikBooking::getBookingHistoryInstance();

		// get all history types and groups
		$history_types  = $history_obj->getTypesMap();
		$history_groups = $history_obj->getTypeGroups();

		foreach ($history_groups as $group_key => $group)
		{
			// start group container
			$history_events[$group['name']] = [];

			foreach ($group['types'] as $ev_type) {
				// push event type
				$history_events[$group['name']][$ev_type] = $history_obj->validType($ev_type, $return = true);
				$parsed_events[] = $ev_type;
			}
		}

		// start "global" (remaining) events group container
		$remaining_ev_key = JText::_('VBOBOOKHISTORYTAB');
		$history_events[$remaining_ev_key] = [];
		foreach ($history_types as $ev_key => $ev_name)
		{
			if (in_array($ev_key, $parsed_events))
			{
				continue;
			}

			// push event type
			$history_events[$remaining_ev_key][$ev_key] = $ev_name;
		}

		return [
			'cron_lbl' => [
				'type'  => 'custom',
				'label' => '',
				'html'  => '<h4><i class="' . VikBookingIcons::i('plug') . '"></i>&nbsp;<i class="' . VikBookingIcons::i('clock') . '"></i>&nbsp;' . $this->getTitle() . '</h4>',
			],
			'type' => [
				'type'    => 'select',
				'label'   => JText::_('VBO_CRONJOB_WEBHOOK_TYPE_LABEL'),
				'help'    => JText::_('VBO_CRONJOB_WEBHOOK_TYPE_DESC'),
				'options' => [
					'url'      => JText::_('VBO_CRONJOB_WEBHOOK_TYPE_URL_OPTION'),
					'callback' => JText::_('VBO_CRONJOB_WEBHOOK_TYPE_CALLBACK_OPTION'),
					'action'   => JText::_('VBO_CRONJOB_WEBHOOK_TYPE_ACTION_OPTION'),
				],
			],
			'handler' => [
				'type'   => 'text',
				'label'   => JText::_('VBO_CRONJOB_WEBHOOK_HANDLER_LABEL'),
				'help'    => JText::_('VBO_CRONJOB_WEBHOOK_HANDLER_DESC'),
			],
			'events' => [
				'type'     => 'select',
				'multiple' => true,
				'assets'   => true,
				'label'    => JText::_('VBO_EVENTS'),
				'help'     => JText::_('VBO_CRONJOB_WEBHOOK_EVENTS_DESC'),
				'options'  => $history_events,
			],
			'help' => [
				'type'  => 'custom',
				'label' => '',
				'html'  => '<p class="vbo-cronparam-suggestion"><i class="vboicn-lifebuoy"></i>' . JText::_('VBO_CRONJOB_WEBHOOK_DESCRIPTION') . '</p>',
			],
		];
	}

	/**
	 * Returns the title of the cron job.
	 * 
	 * @return  string
	 */
	public function getTitle()
	{
		return JText::_('VBO_CRON_WEBHOOK_TITLE');
	}
	
	/**
	 * Executes the cron job.
	 * 
	 * @return  boolean  True on success, false otherwise.
	 */
	protected function execute()
	{
		// fetch the cron method to launch
		$method = 'trigger' . ucfirst($this->params->get('type', 'url'));

		if (!method_exists($this, $method))
		{
			$this->appendLog('No trigger method found for: ' . $this->params->get('type', 'url'));
			throw new RuntimeException(sprintf('Unable to launch [%s] method', $this->params->get('type', 'url')), 404);
		}

		// pull the latest orders
		$orders = $this->pullOrders();

		if (!$orders)
		{
			$this->appendLog('There are no recent orders.');
			return true;
		}

		// notify the latest orders to the registered subscribers
		foreach ($orders as $order)
		{
			try
			{
				// dispatch webhook
				$this->{$method}($order);

				// order notified successfully
				$this->appendLog('Notified order #' . $order['id']);
			}
			catch (Exception $e)
			{
				// an error has occurred, log message
				$this->appendLog('Error ' . $e->getCode() . '. Unable to notify order #' . $order['id'] . ' for this reason: ' . $e->getMessage());
			}
		}

		return true;
	}

	/**
	 * Notifies the given booking data to a specific HTTP end-point.
	 * 
	 * @param   array  $booking  The booking data.
	 * 
	 * @return  void
	 * 
	 * @throws  Exception
	 */
	protected function triggerUrl($booking)
	{
		$http = new JHttp();

		// make POST request to the specified URL
		$response = $http->post($this->params->get('handler'), json_encode($booking), [
			'Content-Type' => 'application/json'
		]);

		if ($response->code != 200)
		{
			// invalid response, throw an exception
			throw new Exception(strip_tags($response->body), $response->code);
		}

		$this->output(strip_tags($response->body));
	}

	/**
	 * Notifies the given booking data to a specific PHP callback.
	 * In case the callback contains a comma, the chunk before will be
	 * used as class name and the next string will be used as method.
	 * 
	 * @param   array  $booking  The booking data.
	 * 
	 * @return  void
	 * 
	 * @throws  Exception
	 */
	protected function triggerCallback($booking)
	{
		// get PHP callback from cron configuration
		$handler = $this->params->get('handler', '');

		if (strpos($handler, ',') !== false)
		{
			// comma found, extract class and method name
			$handler = preg_split("/\s*,\s*/", $handler);
		}

		// check whether the specified callback can be invoked
		if (!is_callable($handler))
		{
			if (is_array($handler))
			{
				$handler = $handler[0] . '::' . $handler[1] . '()';
			}

			throw new Exception('Cannot invoke ' . $handler . ' method', 500);
		}

		// invoke PHP callback
		$return = call_user_func_array($handler, [$booking]);

		if (!is_null($return))
		{
			if (is_array($return) || is_object($return))
			{
				$return = print_r($return, true);
			}

			$this->output('Returned value: ' . $return);
		}
	}

	/**
	 * Notifies the given booking data through a platform event.
	 * 
	 * @param   array  $booking  The booking data.
	 * 
	 * @return  void
	 * 
	 * @throws  Exception
	 */
	protected function triggerAction($booking)
	{
		$handler = $this->params->get('handler', '');

		if (!$handler)
		{
			throw new Exception('The action cannot be empty', 400);
		}

		// delegate trigger to the proper platform dispatcher
		VBOFactory::getPlatform()->getDispatcher()->trigger($handler, [$booking]);
	}

	/**
	 * Retrieves all the orders that has been recently created/updated.
	 * 
	 * @return  array  A list of downloaded orders.
	 */
	protected function pullOrders()
	{
		// check whether the threshold have been initialized yet
		if (!$this->getData()->flag_int)
		{
			$date = $this->initThreshold();

			$this->appendLog('Initialized orders threshold at ' . $date->format('Y-m-d H:i:s') . ' (UTC)');

			return [];
		}

		$db = JFactory::getDbo();

		$historyHandler = VikBooking::getBookingHistoryInstance();

		/**
		 * Check if the history events should be filtered by some specific types.
		 * 
		 * @since 	1.16.9 (J) - 1.6.9 (WP)
		 */
		$ev_types = $this->params->get('events', []);
		if ($ev_types)
		{
			$ev_types = array_map(function($type) use ($db)
			{
				return $db->q($type);
			}, array_filter($ev_types));
		}

		// take all the orders with a creation/update datetime higher than the saved threshold
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__vikbooking_orderhistory'))
			->where($db->qn('dt') . ' > ' . $db->q(JFactory::getDate($this->getData()->flag_int)->toSql()))
			->order($db->qn('dt') . ' DESC');

		if ($ev_types)
		{
			// filter by the given event types
			$query->where($db->qn('type') . ' IN (' . implode(', ', $ev_types) . ')');
		}

		$db->setQuery($query);
		$history = $db->loadAssocList();

		if (!$history)
		{
			// nothing to notify
			return [];
		}

		$tz_offset = JFactory::getApplication()->get('offset');
		$orders = [];

		foreach ($history as $status)
		{
			if (!isset($orders[$status['idorder']]))
			{
				// fetch order details
				$order = VikBooking::getBookingInfoFromID($status['idorder']);

				if (!$order)
				{
					$this->appendLog('Unable to fetch order details for booking #' . $status['idorder']);
					continue;
				}

				// extend booking details with further information
				$order['checkin_iso']  = JFactory::getDate(date('Y-m-d H:i:s', $order['checkin']), $tz_offset)->toISO8601(true);
				$order['checkout_iso'] = JFactory::getDate(date('Y-m-d H:i:s', $order['checkout']), $tz_offset)->toISO8601(true);
				$order['roomsdata']    = VikBooking::loadOrdersRoomsData($order['id']);
				$order['customer']     = VikBooking::getCPinIstance()->getCustomerFromBooking($order['id']);
				$order['booking_link'] = VikBooking::externalroute('index.php?option=com_vikbooking&view=booking&sid='.(!empty($order['sid']) ? $order['sid'] : $order['idorderota']).'&ts=' . $order['ts'], false);
				$order['history']      = [];

				// register order details only once
				$orders[$status['idorder']] = $order;
			}

			// recover type title
			$status['event'] = $historyHandler->validType($status['type'], $return = true);

			// append order status
			$orders[$status['idorder']]['history'][] = $status;
		}

		// update threshold
		$this->initThreshold();

		return array_values($orders);
	}

	/**
	 * Configures the bookings threshold.
	 * The first time this cron is executed, it will save the current time as threshold.
	 * 
	 * @return  JDate
	 */
	protected function initThreshold()
	{
		$date = JFactory::getDate();

		// register the current time
		$this->getData()->flag_int = $date->getTimestamp();

		return $date;
	}	
}
