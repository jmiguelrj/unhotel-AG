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

class VikBookingCronJobSmsReminder extends VBOCronJob
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

		return [
			'cron_lbl' => [
				'type'  => 'custom',
				'label' => '',
				'html'  => '<h4><i class="vboicn-bubble"></i><i class="vboicn-alarm"></i>&nbsp;' . $this->getTitle() . '</h4>'
			],
			'checktype' => [
				'type'    => 'select',
				'label'   => JText::_('VBOCRONSMSREMPARAMCTYPE'),
				'options' => [
					'checkin'  => JText::_('VBOCRONSMSREMPARAMCTYPEA'),
					'payment'  => JText::_('VBOCRONSMSREMPARAMCTYPEB'),
					'checkout' => JText::_('VBOCRONSMSREMPARAMCTYPEC'),
				],
			],
			'remindbefored' => [
				'type'    => 'number',
				'label'   => JText::_('VBOCRONSMSREMPARAMBEFD'),
				'help'    => JText::_('VBOCRONSMSREMPARAMCTYPECHELP'),
				'default' => 2,
				'attributes' => [
					'style' => 'width: 60px !important;',
				],
			],
			'test' => [
				'type'    => 'select',
				'label'   => JText::_('VBOCRONSMSREMPARAMTEST'),
				'help'    => JText::_('VBOCRONSMSREMPARAMTESTHELP'),
				'default' => 'OFF',
				'options' => [
					'ON'  => JText::_('VBYES'),
					'OFF' => JText::_('VBNO'),
				],
			],
			'tpl_text' => [
				'type'    => 'textarea',
				'label'   => JText::_('VBOCRONSMSREMPARAMTEXT'),
				'default' => $this->getDefaultTemplate(),
				'attributes' => [
					'id'    => 'tpl_text',
					'style' => 'width: 70%; height: 130px;',
				],
			],
			'buttons' => [
				'type'  => 'custom',
				'label' => '',
				'html'  => '<div class="btn-toolbar vbo-smstpl-toolbar vbo-cronparam-cbar" style="margin-top: -10px;">
					<div class="btn-group pull-left vbo-smstpl-bgroup">'
						. implode("\n", array_merge($special_tags_base, $condtext_tags))
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
		return JText::_('VBO_CRON_SMS_REMINDER_TITLE');
	}
	
	/**
	 * Executes the cron job.
	 * 
	 * @return  boolean  True on success, false otherwise.
	 */
	protected function execute()
	{
		$checktype = $this->params->get('checktype', 'checkin');	
		$remindbefored = (int) $this->params->get('remindbefored');

		$db = JFactory::getDbo();
		
		$start_ts = mktime( 0,  0,  0, date('n'), ($checktype == 'checkout' ? ((int) date('j') - $remindbefored) : ((int) date('j') + $remindbefored)), date('Y'));
		$end_ts   = mktime(23, 59, 59, date('n'), ($checktype == 'checkout' ? ((int) date('j') - $remindbefored) : ((int) date('j') + $remindbefored)), date('Y'));
		
		$this->output('<p>Reading bookings with ' . ($checktype == 'checkout' ? 'check-out' : 'check-in') . ' datetime between: ' . date('c', $start_ts) . ' - '.date('c', $end_ts) . '</p>');
		
		$query = $db->getQuery(true);

		$query->select('o.*');
		$query->select($db->qn('co.idcustomer'));
		$query->select($db->qn('nat.country_name'));
		$query->select($db->qn('c.pin', 'customer_pin'));
		$query->select(sprintf('CONCAT_WS(\' \', %s, %s) AS %s',
			$db->qn('c.first_name'),
			$db->qn('c.last_name'),
			$db->qn('customer_name')
		));

		$query->from($db->qn('#__vikbooking_orders', 'o'));
		$query->leftjoin($db->qn('#__vikbooking_customers_orders', 'co') . ' ON ' . $db->qn('co.idorder') . ' = ' . $db->qn('o.id'));
		$query->leftjoin($db->qn('#__vikbooking_customers', 'c') . ' ON ' . $db->qn('co.idcustomer') . ' = ' . $db->qn('c.id'));
		$query->leftjoin($db->qn('#__vikbooking_countries', 'nat') . ' ON ' . $db->qn('nat.country_3_code') . ' = ' . $db->qn('o.country'));

		$query->where($db->qn('o.' . ($checktype == 'checkout' ? 'checkout' : 'checkin')) . ' >= ' . (int) $start_ts);
		$query->where($db->qn('o.' . ($checktype == 'checkout' ? 'checkout' : 'checkin')) . ' <= ' . (int) $end_ts);
		$query->where($db->qn('o.closure') . ' = 0');

		if (in_array($checktype, ['checkin', 'checkout']))
		{
			$query->where($db->qn('o.status') . ' = ' . $db->q('confirmed'));
		}
		else
		{
			$query->where([
				$db->qn('o.status') . ' != ' . $db->q('cancelled'),
				$db->qn('o.total') . ' > 0', 
				$db->qn('o.totpaid') . ' > 0', 
				$db->qn('o.totpaid') . ' < ' . $db->qn('o.total'),
			]);
		}
		
		$db->setQuery($query);
		$bookings = $db->loadAssocList();
		
		if (!$bookings)
		{
			$this->output('<span>No bookings to notify.</span>');

			return true;
		}
		
		$this->output('<p>Bookings to be notified: '.count($bookings).'</p>');
		
		foreach ($bookings as $k => $booking)
		{
			if ($this->isTracked($booking['id']))
			{
				// the element has been already processed, skip it
				$this->output('<span>Booking ID '.$booking['id'].' ('.$booking['customer_name'].') was already notified. Skipped.</span>');
				continue;
			}
			
			$message = $this->params->get('tpl_text');

			// language translation
			$lang = JFactory::getLanguage();
			$vbo_tn = VikBooking::getTranslator();
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

			if (!trim($message))
			{
				// no message, use default template
				$message = $this->getDefaultTemplate();
			}

			//
			$send_res = $this->params->get('test', 'OFF') == 'ON' ? false : VikBooking::sendBookingSMS($booking['id'], array('admin'), array('customer'), $message);
			
			$this->output('<span>Result for sending SMS to '.$booking['phone'].' - Booking ID '.$booking['id'].' ('.$booking['customer_name'].(!empty($booking['lang']) ? ' '.$booking['lang'] : '').'): '.($send_res !== false ? '<i class="vboicn-checkmark"></i>Success' : '<i class="vboicn-cancel-circle"></i>Failure').($this->params->get('test', 'OFF') == 'ON' ? ' (Test Mode ON)' : ' (Check Phone and SMS Gateway Params)').'</span>');
			
			if ($send_res !== false) {
				$this->appendLog('SMS sent to '.$booking['phone'].' - Booking ID '.$booking['id'].' ('.$booking['customer_name'].(!empty($booking['lang']) ? ' '.$booking['lang'] : '').')');
				//store in execution flag that this booking ID was notified
				$this->track($booking['id']);
			}
		}
		
		return true;
	}

	/**
	 * Returns the default SMS template.
	 * 
	 * @return  string
	 * 
	 * @since   1.5.10
	 */
	private function getDefaultTemplate()
	{
		static $tmpl = '';

		if (!$tmpl)
		{
			$tmpl = "Dear {customer_name},\nThis is an automated message to remind you the check-in time for your stay: {checkin_date} at 13:00.";
		}

		return $tmpl;
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
}
