<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - E4J srl
 * @copyright   Copyright (C) 2025 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

class VikBookingCronJobPrecheckinReminder extends VBOCronJob
{
	// Track the processed elements within an array.
	// Alias the "isTracked" method to allow the reusability of the latter
	// inside the method overloaded by this class.
	use VBOCronTrackerArray { isTracked as traitIsTracked; }
	use VBOCronFixerReminder;
	
	/**
	 * This method should return all the form fields required to collect the information
	 * needed for the execution of the cron job.
	 * 
	 * @return  array  An associative array of form fields.
	 */
	public function getForm()
	{
		/**
		 * Build a list of all special tags for the visual editor.
		 * 
		 * @since 	1.15.0 (J) - 1.5.0 (WP)
		 */
		$special_tags_base = [
			'{customer_name}',
			'{customer_pin}',
			'{booking_id}',
			'{checkin_date}',
			'{checkout_date}',
			'{num_nights}',
			'{rooms_booked}',
			'{tot_adults}',
			'{tot_children}',
			'{tot_guests}',
			'{total}',
			'{total_paid}',
			'{remaining_balance}',
			'{booking_link}',
		];

		/**
		 * Load all conditional text special tags.
		 * 
		 * @since 	1.14 (J) - 1.4.0 (WP)
		 */
		$condtext_tags = array_keys(VikBooking::getConditionalRulesInstance()->getSpecialTags());

		/**
		 * Load Door Access Control special tags from the configured integration providers.
		 * 
		 * @since 	1.18.4 (J) - 1.8.4 (WP)
		 */
		$dac_tags = VBOFactory::getDoorAccessControl()->getInstalledSpecialTags();

		// join special tags with conditional texts and DAC to construct a list of editor buttons,
		// displayed within the toolbar of Quill editor
		$editor_btns = array_merge($special_tags_base, $condtext_tags, $dac_tags);

		// convert special tags into HTML buttons, displayed under the text editor
		$special_tags_base = array_map(function($tag)
		{
			return '<button type="button" class="btn" onclick="setCronTplTag(\'tpl_text\', \'' . $tag . '\');">' . $tag . '</button>';
		}, $special_tags_base);

		// convert conditional texts into HTML buttons, displayed under the text editor
		$condtext_tags = array_map(function($tag)
		{
			return '<button type="button" class="btn vbo-condtext-specialtag-btn" onclick="setCronTplTag(\'tpl_text\', \'' . $tag . '\');">' . $tag . '</button>';
		}, $condtext_tags);

		// convert Door Access Control tags into HTML buttons, displayed under the text editor
		$dac_tags = array_map(function($tag)
		{
			return '<button type="button" class="btn vbo-condtext-specialtag-btn vbo-dac-specialtag-btn" onclick="setCronTplTag(\'tpl_text\', \'' . $tag . '\');">' . $tag . '</button>';
		}, $dac_tags);

		return [
			'cron_lbl' => [
				'type'  => 'custom',
				'label' => '',
				'html'  => '<h4><i class="' . VikBookingIcons::i('users') . '"></i> <i class="' . VikBookingIcons::i('bell') . '"></i>&nbsp;' . $this->getTitle() . '</h4>',
			],
			'remindbefored' => [
				'type'    => 'number',
				'label'   => JText::_('VBOCRONSMSREMPARAMBEFD'),
				'help'    => JText::_('VBOCRONSMSREMPARAMCTYPECHELP'),
				'default' => 1,
			],
			/**
			 * This parameter serves to include bookings generated less days in advance
			 * than the above parameter "remindbefored" from the check-in date. In short,
			 * if days in advance is set to 7 and a booking is made 3 days before the
			 * check-in date, with this new setting it is possible to choose if this
			 * booking should be notified or not. We keep this enabled by default.
			 * 
			 * @since 	1.16.9 (J) - 1.6.9 (WP)
			 */
			'less_days_advance' => [
				'type'    => 'select',
				'label'   => JText::_('VBO_CRON_DADV_LOWER'),
				'help'    => JText::_('VBO_CRON_DADV_LOWER_HELP'),
				'default' => 1,
				'options' => [
					1 => JText::_('VBYES'),
					0 => JText::_('VBNO'),
				],
			],
			/**
			 * Optional filter by listings.
			 * 
			 * @since 	1.17.7 (J) - 1.7.7 (WP)
			 */
			'listings' => [
				'type'     => 'listings',
				'label'    => JText::_('VBO_LISTINGS'),
				'help'     => sprintf('(%s)', JText::_('VBOFILTEISROPTIONAL')),
				'multiple' => true,
				'asset_options' => [
					'placeholder' => JText::_('VBO_LISTINGS'),
					'allowClear'  => true,
				],
			],
			'test' => [
				'type'    => 'select',
				'label'   => JText::_('VBOCRONSMSREMPARAMTEST'),
				'help'    => JText::_('VBOCRONEMAILREMPARAMTESTHELP'),
				'default' => 'OFF',
				'options' => [
					'ON'  => JText::_('VBYES'),
					'OFF' => JText::_('VBNO'),
				],
			],
			'subject' => [
				'type'    => 'text',
				'label'   => JText::_('VBOCRONEMAILREMPARAMSUBJECT'),
				'default' => JText::_('VBOCRONEMAILREMPARAMSUBJECT'),
			],
			'tpl_text' => [
				'type'    => 'visual_html',
				'label'   => JText::_('VBOCRONSMSREMPARAMTEXT'),
				'default' => $this->getDefaultTemplate(),
				'attributes' => [
					'id'    => 'tpl_text',
					'style' => 'width: 70%; height: 150px;',
				],
				'editor_opts' => [
					'modes' => [
						'visual',
						'modal-visual',
						'text',
					],
					'gen_ai' => [
						'placeholders' => 1,
						'environment' => 'cron',
					],
				],
				'editor_btns' => $editor_btns,
			],
			'buttons' => [
				'type'  => 'custom',
				'label' => '',
				'html'  => '<div class="btn-toolbar vbo-smstpl-toolbar vbo-cronparam-cbar" style="margin-top: -10px;">
					<div class="btn-group pull-left vbo-smstpl-bgroup vik-contentbuilder-textmode-sptags">'
						. implode("\n", array_merge($special_tags_base, $condtext_tags, $dac_tags))
					. '</div>
				</div>
				<script>
					function setCronTplTag(taid, tpltag) {
						var tplobj = document.getElementById(taid);
						if (tplobj != null) {
							var start = tplobj.selectionStart;
							var end = tplobj.selectionEnd;
							tplobj.value = tplobj.value.substring(0, start) + tpltag + tplobj.value.substring(end);
							tplobj.selectionStart = tplobj.selectionEnd = start + tpltag.length;
							tplobj.focus();
							jQuery("#" + taid).trigger("change");
						}
					}
				</script>',
			],
			'help' => [
				'type'  => 'custom',
				'label' => '',
				'html'  => '<p class="vbo-cronparam-suggestion"><i class="vboicn-lifebuoy"></i>' . JText::_('VBOCRONSMSREMHELP') . '</p>',
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
		return JText::_('VBO_CRON_PRECHECKIN_REMINDER_TITLE');
	}
	
	/**
	 * Executes the cron job.
	 * 
	 * @return  boolean  True on success, false otherwise.
	 */
	protected function execute()
	{	
		$remindbefored = (int) $this->params->get('remindbefored');
		$listings = (array) $this->params->get('listings');

		$db     = JFactory::getDbo();
		$vbo_tn = VikBooking::getTranslator();

		$start_ts = mktime( 0,  0,  0, date('n'), ((int) date('j') + $remindbefored), date('Y'));
		$end_ts   = mktime(23, 59, 59, date('n'), ((int) date('j') + $remindbefored), date('Y'));

		/**
		 * In case of precheck-in reminder with like 3 days in advance, we cannot allow to skip all
		 * bookings with a lower last-minute advance period, like a reservation made "today" for
		 * "tomorrow" would never be notified. For this reason, the flag for checking the bookings
		 * notified should be set to today, and so should the start date be set.
		 * 
		 * @since 	1.16.9 (J) - 1.6.9 (WP)
		 */
		if ($remindbefored > 1 && $this->params->get('less_days_advance'))
		{
			$start_ts = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
		}

		// get all bookings with no pax_data and with a customer record assigned
		$query = $db->getQuery(true);

		$query->select('o.*');
		$query->select($db->qn('co.idcustomer'));
		$query->select($db->qn('co.pax_data'));
		$query->select($db->qn('nat.country_name'));
		$query->select($db->qn('c.pin', 'customer_pin'));
		$query->select(sprintf('CONCAT_WS(\' \', %s, %s) AS %s',
			$db->qn('c.first_name'),
			$db->qn('c.last_name'),
			$db->qn('customer_name')
		));

		$query->from($db->qn('#__vikbooking_orders', 'o'));
		$query->leftJoin($db->qn('#__vikbooking_customers_orders', 'co') . ' ON ' . $db->qn('co.idorder') . ' = ' . $db->qn('o.id'));
		$query->leftJoin($db->qn('#__vikbooking_customers', 'c') . ' ON ' . $db->qn('co.idcustomer') . ' = ' . $db->qn('c.id'));
		$query->leftJoin($db->qn('#__vikbooking_countries', 'nat') . ' ON ' . $db->qn('nat.country_3_code') . ' = ' . $db->qn('o.country'));

		$query->where([
			$db->qn('o.status') . ' = ' . $db->q('confirmed'),
			$db->qn('o.closure') . ' = 0',
			$db->qn('co.idcustomer') . ' IS NOT NULL',
			$db->qn('co.pax_data') . ' IS NULL',
		]);

		/**
		 * The default time condition for fetching the bookings takes the entire check-in day
		 * of N days in advance from today. However, in order to fetch last minute bookings,
		 * in case the days in advance are 1, we also get the bookings made today.
		 * 
		 * @since 	December 29th 2020 (1.14 J - 1.4.0 WP)
		 */
		if ($remindbefored == 1)
		{
			$today_midn_from = mktime( 0,  0,  0, date('n'), date('j'), date('Y'));
			$today_midn_to 	 = mktime(23, 59, 59, date('n'), date('j'), date('Y'));

			// create a new condition and join with AND glue to the previous one
			$query->andWhere([
				// take this condition
				$db->qn('o.checkin') . ' BETWEEN ' . $start_ts . ' AND ' . $end_ts,
				// or this one
				'(' . $db->qn('o.checkin') . ' BETWEEN ' . $today_midn_from . ' AND ' . $today_midn_to . ')'
				. ' AND (' . $db->qn('o.ts') . ' BETWEEN ' . $today_midn_from . ' AND ' . $today_midn_to . ')',
			], 'OR');
			
			$this->output('<p>Last minute bookings made today for tonight will be included.</p>');
		}
		else
		{
			$query->where($db->qn('o.checkin') . ' BETWEEN ' . $start_ts . ' AND ' . $end_ts);
		}

		if ($listings)
		{
			// filter bookings by listing IDs
			$listings = array_filter(array_map('intval', $listings));
			if ($listings)
			{
				$query->leftJoin($db->qn('#__vikbooking_ordersrooms', 'or') . ' ON ' . $db->qn('or.idorder') . ' = ' . $db->qn('o.id'));
				$query->where($db->qn('or.idroom') . ' IN (' . implode(', ', $listings) . ')');
			}
		}

		$this->output('<p>Reading bookings with check-in datetime between: ' . date('c', $start_ts) . ' - ' . date('c', $end_ts) . '</p>');

		$db->setQuery($query);
		$bookings = $db->loadAssocList();

		if (!$bookings)
		{
			$this->output('<span>No bookings to notify.</span>');
			return true;
		}

		/**
		 * Check if support for OTA messaging is available.
		 * 
		 * @since 	1.16.5 (J) - 1.6.5 (WP)
		 */
		$ota_messaging_supported = class_exists('VCMChatMessaging');

		// make sure to skip placeholder email addresses for OTA bookings when OTA messaging is not supported
		foreach ($bookings as $k => $booking)
		{
			if ($ota_messaging_supported && VCMChatMessaging::getInstance($booking)->supportsOtaMessaging($mandatory = true))
			{
				// set flag to identify an OTA messaging notification over the regular email
				$bookings[$k]['cron_requires_ota_messaging'] = 1;
				continue;
			}

			if (preg_match("/^no-email-[^@]+@[a-z0-9\.]+\.com$/i", $booking['custmail']))
			{
				// false email address, typical of Airbnb, ignore reservation record
				// this statement should be entered only if VCM is outdated, or the Guest Messaging API will be used
				unset($bookings[$k]);
				continue;
			}
		}

		// check if we still got bookings left
		if (!$bookings)
		{
			$this->output('<span>No more bookings to notify.</span>');
			return true;
		}
		
		$this->output('<p>Bookings to be notified with no guests information: ' . count($bookings) . '</p>');
		
		$def_subject = $this->params->get('subject');
		foreach ($bookings as $k => $booking)
		{
			if ($this->isTracked($booking['id']))
			{
				// the element has been already processed, skip it
				$this->output('<span>Booking ID '.$booking['id'].' ('.$booking['customer_name'].') was already notified. Skipped.</span>');
				continue;
			}
			
			$message = $this->params->get('tpl_text');
			$this->params->set('subject', $def_subject);

			// language translation
			$lang = JFactory::getLanguage();
			$vbo_tn::$force_tolang = null;
			$website_def_lang = $vbo_tn->getDefaultLang();
			if (!empty($booking['lang'])) {
				if ($lang->getTag() != $booking['lang']) {
					if (VBOPlatformDetection::isWordPress()) {
						// wp
						$lang->load('com_vikbooking', VIKBOOKING_SITE_LANG, $booking['lang'], true);
						$lang->load('com_vikbooking', VIKBOOKING_ADMIN_LANG, $booking['lang'], true);
					} else {
						// J
						$lang->load('com_vikbooking', JPATH_SITE, $booking['lang'], true);
						$lang->load('com_vikbooking', JPATH_ADMINISTRATOR, $booking['lang'], true);
						$lang->load('joomla', JPATH_SITE, $booking['lang'], true);
						$lang->load('joomla', JPATH_ADMINISTRATOR, $booking['lang'], true);
					}
				}
				if ($website_def_lang != $booking['lang']) {
					// force the translation to start because contents should be translated
					$vbo_tn::$force_tolang = $booking['lang'];
				}

				// convert cron data into an array and re-encode parameters to preserve
				// the compatibility with the translator
				$cron_tn = (array) $this->getData();
				$cron_tn['params'] = json_encode($this->params->getProperties());

				$vbo_tn->translateContents($cron_tn, '#__vikbooking_cronjobs', array(), array(), $booking['lang']);
				$params_tn = json_decode($cron_tn['params'], true);
				if (is_array($params_tn) && array_key_exists('tpl_text', $params_tn)) {
					$message = $params_tn['tpl_text'];
				}
				if (is_array($params_tn) && array_key_exists('subject', $params_tn)) {
					$this->params->set('subject', $params_tn['subject']);
				}
			} else {
				// make sure to (re-)load the website default language
				if (VBOPlatformDetection::isWordPress()) {
					// wp
					$lang->load('com_vikbooking', VIKBOOKING_SITE_LANG, $website_def_lang, true);
					$lang->load('com_vikbooking', VIKBOOKING_ADMIN_LANG, $website_def_lang, true);
				} else {
					// J
					$lang->load('com_vikbooking', JPATH_SITE, $website_def_lang, true);
					$lang->load('com_vikbooking', JPATH_ADMINISTRATOR, $website_def_lang, true);
					$lang->load('joomla', JPATH_SITE, $website_def_lang, true);
					$lang->load('joomla', JPATH_ADMINISTRATOR, $website_def_lang, true);
				}
			}

			// send the notification
			$send_method = 'eMail';
			$send_res 	 = false;
			$recipient 	 = $booking['custmail'];

			if (!empty($booking['cron_requires_ota_messaging']))
			{
				// overwrite the notification method to OTA messaging
				$send_method = 'Message';
				$recipient = 'Guest';
			}

			// notify the customer
			if ($this->params->get('test', 'OFF') == 'ON')
			{
				$send_res = false;
			}
			elseif (!strcasecmp($send_method, 'email'))
			{
				// attempt to send the email message
				$send_res = $this->sendEmailReminder($booking, $message);
			}
			elseif (!strcasecmp($send_method, 'message'))
			{
				// attempt to send a guest message through the related OTA Messaging API
				$send_res = $this->sendGuestMessage($booking, $message);
			}

			// build log for output
			$output_log = "<span>Result for sending {$send_method} to {$recipient} - Booking ID {$booking['id']} ";
			$output_log .= '(' . $booking['customer_name'] . (!empty($booking['lang']) ? " [" . $booking['lang'] . "]" : '') . '): ';
			$output_log .= ($send_res !== false ? '<i class="vboicn-checkmark"></i>Success' : '<i class="vboicn-cancel-circle"></i>Failure' . ($this->params->get('test', 'OFF') == 'ON' ? ' (Test Mode ON)' : ''));
			$output_log .= '</span>';

			$this->output($output_log);

			if ($send_res !== false)
			{
				// append execution log
				$this->appendLog($send_method . ' sent to ' . $recipient . ' - Booking ID ' . $booking['id'] . ' (' . $booking['customer_name'] . (!empty($booking['lang']) ? " [" . $booking['lang'] . "]" : '') . ')');
				// store the execution flag for this notified booking ID
				$this->track($booking['id']);
			}
		}
		
		return true;
	}

	/**
	 * Checks whether the specified element has been already processed.
	 * 
	 * @param   mixed    $element  The element to check.
	 * 
	 * @return  boolean  True if already processed, false otherwise.
	 */
	protected function isTracked($element)
	{
		// launch backward compatibility process
		$this->normalizeDeprecatedFlag();
		// invoke trait method
		return $this->traitIsTracked($element);
	}

	private function sendEmailReminder($booking, $message)
	{
		$vbo_app = VikBooking::getVboApplication();
		if (empty($booking['id']) || empty($booking['custmail'])) {
			return false;
		}

		// fetch booked room details
		$booking_rooms = VikBooking::loadOrdersRoomsData($booking['id']);

		$vbo_tn = VikBooking::getTranslator();
		$vbo_tn->translateContents($booking_rooms, '#__vikbooking_rooms', ['id' => 'idroom', 'name' => 'room_name']);

		if (!trim($message))
		{
			// no message, use default template
			$message = $this->getDefaultTemplate();
		}

		$message = $this->parseCustomerEmailTemplate($message, $booking, $booking_rooms, $vbo_tn);
		if (empty($message)) {
			return false;
		}

		$is_html = (strpos($message, '<') !== false || strpos($message, '</') !== false);
		if ($is_html && !preg_match("/(<\/?br\/?>)+/", $message)) {
			// when no br tags found, apply nl2br
			$message = nl2br($message);
		}

		$admin_sendermail = VikBooking::getSenderMail();

		return $vbo_app->sendMail($admin_sendermail, $admin_sendermail, $booking['custmail'], $admin_sendermail, $this->params->get('subject'), $message, $is_html);
	}

	/**
	 * Sends a guest message through the related OTA messaging API in place of an email.
	 * 
	 * @param   array 	$booking 	booking array record.
	 * @param 	string 	$message 	the message content for the email.
	 * 
	 * @return  boolean 			True if the message was sent, false otherwise.
	 * 
	 * @since 	1.16.5 (J) - 1.6.5 (WP)
	 * 
	 * @requires VCM >= 1.8.20
	 */
	private function sendGuestMessage(array $booking, $message)
	{
		if (empty($booking['channel']) || empty($booking['idorderota'])) {
			return false;
		}

		// fetch booked room details
		$booking_rooms = VikBooking::loadOrdersRoomsData($booking['id']);

		// translate contents
		$vbo_tn = VikBooking::getTranslator();
		$vbo_tn->translateContents($booking_rooms, '#__vikbooking_rooms', ['id' => 'idroom', 'name' => 'room_name']);

		if (!trim($message)) {
			// no message, use default template
			$message = $this->getDefaultTemplate();
		}

		$message = $this->parseCustomerEmailTemplate($message, $booking, $booking_rooms, $vbo_tn);
		if (empty($message)) {
			return false;
		}

		$messaging = VCMChatMessaging::getInstance($booking);
		$result = $messaging->setMessage($message)
			->sendGuestMessage();

		if (!$result && $error = $messaging->getError()) {
			// append execution log with the error description
			$this->appendLog("Message could not be sent to guest - Booking ID {$booking['id']} ({$booking['customer_name']}): {$error}");
		}

		return $result;
	}

	private function parseCustomerEmailTemplate($message, $booking, $booking_rooms, $vbo_tn = null)
	{
		$tpl = $message;

		/**
		 * Parse all conditional text rules.
		 * 
		 * @since 	1.14 (J) - 1.4.0 (WP)
		 */
		VikBooking::getConditionalRulesInstance()
			->set(array('booking', 'rooms'), array($booking, $booking_rooms))
			->parseTokens($tpl);

		/**
		 * Parse all Door Access Control tags.
		 * 
		 * @since 	1.18.4 (J) - 1.8.4 (WP)
		 */
		VBOFactory::getDoorAccessControl()
			->parseTokens((new VBOBookingRegistry($booking, $booking_rooms)), $tpl);

		$vbo_df = VikBooking::getDateFormat();
		$df = $vbo_df == "%d/%m/%Y" ? 'd/m/Y' : ($vbo_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y-m-d');
		$tpl = str_replace('{customer_name}', $booking['customer_name'], $tpl);
		$tpl = str_replace('{booking_id}', $booking['id'], $tpl);
		$tpl = str_replace('{checkin_date}', date($df, $booking['checkin']), $tpl);
		$tpl = str_replace('{checkout_date}', date($df, $booking['checkout']), $tpl);
		$tpl = str_replace('{num_nights}', $booking['days'], $tpl);
		$rooms_booked = array();
		$tot_adults = 0;
		$tot_children = 0;
		$tot_guests = 0;
		foreach ($booking_rooms as $broom) {
			if (array_key_exists($broom['room_name'], $rooms_booked)) {
				$rooms_booked[$broom['room_name']] += 1;
			} else {
				$rooms_booked[$broom['room_name']] = 1;
			}
			$tot_adults += (int)$broom['adults'];
			$tot_children += (int)$broom['children'];
			$tot_guests += ((int)$broom['adults'] + (int)$broom['children']);
		}
		$tpl = str_replace('{tot_adults}', $tot_adults, $tpl);
		$tpl = str_replace('{tot_children}', $tot_children, $tpl);
		$tpl = str_replace('{tot_guests}', $tot_guests, $tpl);
		$rooms_booked_quant = array();
		foreach ($rooms_booked as $rname => $quant) {
			$rooms_booked_quant[] = ($quant > 1 ? $quant.' ' : '').$rname;
		}
		$tpl = str_replace('{rooms_booked}', implode(', ', $rooms_booked_quant), $tpl);
		$tpl = str_replace('{total}', VikBooking::numberFormat($booking['total']), $tpl);
		$tpl = str_replace('{total_paid}', VikBooking::numberFormat($booking['totpaid']), $tpl);

		$remaining_bal = $booking['total'] - $booking['totpaid'];
		$damage_deposit_payment = VBORoomHelper::getInstance()->getDamageDepositSplitPayment($booking, $booking_rooms);
		$prev_dd_payments = [];
		if ($damage_deposit_payment['damagedep_gross'] ?? 0) {
			$prev_dd_payments = VikBooking::getBookingHistoryInstance($booking['id'])
				->getEventsWithData('PN', function($data) {
					return (is_object($data) && !empty($data->damage_deposit));
				});
			if (!$prev_dd_payments) {
				// check also the first payment event in case of OTA bookings
				$prev_dd_payments = VikBooking::getBookingHistoryInstance($booking['id'])
					->getEventsWithData('P0', function($data) {
						return (is_object($data) && !empty($data->damage_deposit));
					});
			}
		}
		if ($prev_dd_payments && !empty($booking['tot_damage_dep'])) {
			$remaining_bal -= $booking['tot_damage_dep'];
		}
		$tpl = str_replace('{remaining_balance}', VikBooking::numberFormat($remaining_bal), $tpl);
		$tpl = str_replace('{customer_pin}', $booking['customer_pin'], $tpl);

		$use_sid = empty($booking['sid']) && !empty($booking['idorderota']) ? $booking['idorderota'] : $booking['sid'];

		$bestitemid  = VikBooking::findProperItemIdType(['booking'], (!empty($booking['lang']) ? $booking['lang'] : null));
		$lang_suffix = $bestitemid && !empty($booking['lang']) ? '&lang=' . $booking['lang'] : '';
		$book_link 	 = VikBooking::externalroute("index.php?option=com_vikbooking&view=booking&sid=" . $use_sid . "&ts=" . $booking['ts'] . $lang_suffix, false, (!empty($bestitemid) ? $bestitemid : null));

		// once the proper menu item/shortcode ID has been routed, set the correct view value
		$uri_obj = JUri::getInstance($book_link);
		$uri_obj->setVar('view', 'precheckin');
		$book_link = (string) $uri_obj;

		// access the model for shortening URLs
		$model = VBOModelShortenurl::getInstance($onlyRouted = true)->setBooking($booking);

		$tpl = str_replace('{booking_link}', $model->getShortUrl($book_link), $tpl);

		/**
		 * Rooms Distinctive Features parsing
		 * 
		 * @since 	1.10 - Patch August 29th 2018
		 */
		preg_match_all('/\{roomfeature ([a-zA-Z0-9 ]+)\}/U', $tpl, $matches);
		if (isset($matches[1]) && is_array($matches[1]) && @count($matches[1]) > 0) {
			foreach ($matches[1] as $reqf) {
				$rooms_features = array();
				foreach ($booking_rooms as $broom) {
					$distinctive_features = array();
					$rparams = json_decode($broom['params'], true);
					if (array_key_exists('features', $rparams) && count($rparams['features']) > 0 && array_key_exists('roomindex', $broom) && !empty($broom['roomindex']) && array_key_exists($broom['roomindex'], $rparams['features'])) {
						$distinctive_features = $rparams['features'][$broom['roomindex']];
					}
					if (!count($distinctive_features)) {
						continue;
					}
					$feature_found = false;
					foreach ($distinctive_features as $dfk => $dfv) {
						if (stripos($dfk, $reqf) !== false) {
							$feature_found = $dfk;
							if (strlen(trim($dfk)) == strlen(trim($reqf))) {
								break;
							}
						}
					}
					if ($feature_found !== false && @strlen($distinctive_features[$feature_found])) {
						$rooms_features[] = $distinctive_features[$feature_found];
					}
				}
				if (count($rooms_features)) {
					$rpval = implode(', ', $rooms_features);
				} else {
					$rpval = '';
				}
				$tpl = str_replace("{roomfeature ".$reqf."}", $rpval, $tpl);
			}
		}
		//

		return $tpl;
	}
	
	/**
	 * Returns the default e-mail template.
	 * 
	 * @return  string
	 * 
	 * @see 	visibility should be public to allow external usage.
	 * 
	 * @since   1.5.10
	 */
	public function getDefaultTemplate()
	{	
		static $tmpl = '';

		if (!$tmpl)
		{
			$sitelogo 	  = VBOFactory::getConfig()->get('sitelogo');
			$company_name = VikBooking::getFrontTitle();
			
			if ($sitelogo && is_file(VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources'. DIRECTORY_SEPARATOR . $sitelogo))
			{
				$tmpl .= '<p style="text-align: center;">'
					. '<img src="' . VBO_ADMIN_URI . 'resources/' . $sitelogo . '" alt="' . htmlspecialchars($company_name) . '" /></p>'
					. "\n";
			}

			$tmpl .= 
<<<HTML
<h1 style="text-align: center;">
	<span style="font-family: verdana;">$company_name</span>
</h1>
<hr class="vbo-editor-hl-mailwrapper">
<h4>Dear {customer_name},</h4>
<p><br></p>
<p>Thanks for your reservation. This is an automated message to remind you that you still need to fill in the pre check-in form for your stay.</p>
<p>Your arrival is scheduled for {checkin_date}. We kindly ask you to fill the pre check-in form at the link below before that date.</p>
<p><br></p>
<p><a href="{booking_link}">{booking_link}</a></p>
<p><br></p>
<p><br></p>
<p>Thank you.</p>
<p>$company_name</p>
<hr class="vbo-editor-hl-mailwrapper">
<p><br></p>
HTML
			;
		}

		return $tmpl;
	}
}
