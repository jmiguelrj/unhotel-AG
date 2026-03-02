<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - E4J srl
 * @copyright   Copyright (C) 2022 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

class VikBookingCronJobCustomerDiscounts extends VBOCronJob
{
	// Track the processed elements within an array.
	// Alias the "isTracked" method to allow the reusability of the latter
	// inside the method overloaded by this class.
	use VBOCronTrackerArray { isTracked as traitIsTracked; }
	
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
		 */
		$special_tags_base = [
			'{coupon_code}',
			'{customer_name}',
			'{customer_pin}',
			'{total_bookings}',
			'{booking_url}',
			'{website_url}',
		];

		// editor buttons
		$editor_btns = $special_tags_base;

		// convert special tags into HTML buttons, displayed under the text editor
		$special_tags_base = array_map(function($tag)
		{
			return '<button type="button" class="btn" onclick="setCronTplTag(\'tpl_text\', \'' . $tag . '\');">' . $tag . '</button>';
		}, $special_tags_base);

		return [
			'cron_lbl' => [
				'type'  => 'custom',
				'label' => '',
				'html'  => '<h4><i class="' . VikBookingIcons::i('user-tag') . '"></i>&nbsp;' . $this->getTitle() . '</h4>',
			],
			'checktype' => [
				'type'    => 'select',
				'label'   => JText::_('VBCONFIGSENDEMAILWHEN'),
				'options' => [
					'checkout' => JText::_('VBOCRONSMSREMPARAMCTYPEC'),
				],
				'default' => 'checkout',
			],
			'dafter_checkout' => [
				'type'    => 'number',
				'label'   => JText::_('VBO_DAYS_AFTER_CHECKOUT'),
				'default' => 0,
				'min'     => 0,
				'max' 	  => 365,
			],
			'min_bookings' => [
				'type'    => 'number',
				'label'   => JText::_('VBCUSTOMERTOTBOOKINGS'),
				'help'    => JText::_('VBO_MIN_BOOKINGS_DISC_HELP'),
				'default' => 1,
				'min'     => 1,
				'max' 	  => 9999,
			],
			'coupon_id' => [
				'type'    => 'select',
				'label'   => JText::_('VBNEWCOUPONONE'),
				'options' => $this->getCouponsList(),
				'default' => 0,
			],
			'auto_discount' => [
				'type' => 'checkbox',
				'label'   => JText::_('VBO_APPLY_AUTOMATICALLY'),
				'help'    => JText::_('VBO_APPLY_AUTOMATICALLY_HELP'),
				'default' => 0,
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
						. implode("\n", $special_tags_base)
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

					jQuery(function() {
						jQuery("select[name*=\'coupon_id\']").select2();
					});

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
		return JText::_('VBO_CRON_CUSTOMER_DISCOUNTS_TITLE');
	}

	/**
	 * Executes the cron job.
	 * 
	 * @return  boolean  True on success, false otherwise.
	 */
	protected function execute()
	{
		$dafter_checkout = (int) $this->params->get('dafter_checkout', 0);
		$min_bookings 	 = (int) $this->params->get('min_bookings', 1);
		$coupon_id 		 = $this->params->get('coupon_id', 0);
		$auto_discount 	 = (int) $this->params->get('auto_discount', 0);
		$coupon_info 	 = $this->getCoupon($coupon_id);

		if (empty($coupon_id) || !$coupon_info)
		{
			$this->output('<span>No valid coupon code selected.</span>');

			return true;
		}

		$db = JFactory::getDbo();

		$start_ts = mktime( 0,  0,  0, date('n'), ((int) date('j') - $dafter_checkout), date('Y'));
		$end_ts   = mktime(23, 59, 59, date('n'), ((int) date('j') - $dafter_checkout), date('Y'));

		$this->output('<p>Reading bookings with check-out datetime between: ' . date('c', $start_ts) . ' - ' . date('c', $end_ts) . '</p>');

		// inner select
		$count_q = $db->getQuery(true);

		$count_q->select('COUNT(*)');

		$count_q->from($db->qn('#__vikbooking_customers_orders', 'c_co'));
		$count_q->leftjoin($db->qn('#__vikbooking_orders', 'c_o') . ' ON ' . $db->qn('c_co.idorder') . ' = ' . $db->qn('c_o.id'));

		$count_q->where($db->qn('c_co.idcustomer') . '=' . $db->qn('c.id'));
		$count_q->where($db->qn('c_o.status') . '=' . $db->q('confirmed'));
		$count_q->where($db->qn('c_o.closure') . '= 0');

		// main select
		$query = $db->getQuery(true);

		$query->select('o.*');
		$query->select('(' . $count_q . ') AS ' . $db->qn('tot_bookings'));
		$query->select($db->qn('co.idcustomer'));
		$query->select($db->qn('c.pin', 'customer_pin'));
		$query->select(sprintf('CONCAT_WS(\' \', %s, %s) AS %s',
			$db->qn('c.first_name'),
			$db->qn('c.last_name'),
			$db->qn('customer_name')
		));

		$query->from($db->qn('#__vikbooking_orders', 'o'));
		$query->leftjoin($db->qn('#__vikbooking_customers_orders', 'co') . ' ON ' . $db->qn('co.idorder') . ' = ' . $db->qn('o.id'));
		$query->leftjoin($db->qn('#__vikbooking_customers', 'c') . ' ON ' . $db->qn('co.idcustomer') . ' = ' . $db->qn('c.id'));

		$query->where($db->qn('o.checkout') . ' >= ' . (int) $start_ts);
		$query->where($db->qn('o.checkout') . ' <= ' . (int) $end_ts);
		$query->where($db->qn('o.status') . ' = ' . $db->q('confirmed'));
		$query->where($db->qn('o.closure') . ' = 0');
		$query->where($db->qn('co.idcustomer') . ' IS NOT NULL');

		$query->order($db->qn('o.id') . ' DESC');
		
		$db->setQuery($query);
		$bookings = $db->loadAssocList();

		if (!$bookings)
		{
			$this->output('<span>No bookings or customers to notify.</span>');

			return true;
		}

		/**
		 * Check if support for OTA messaging is available.
		 * 
		 * @since 	1.16.5 (J) - 1.6.5 (WP)
		 */
		$ota_messaging_supported = class_exists('VCMChatMessaging');

		// make sure to skip placeholder email addresses for OTA bookings, and validate minimum bookings
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

			if (empty($booking['idcustomer']) || $booking['tot_bookings'] < $min_bookings)
			{
				// not enough bookings made by this customer
				unset($bookings[$k]);
				continue;
			}
		}

		// check if we still got bookings left
		if (!$bookings)
		{
			$this->output('<span>No eligible customers to notify and assign the discount.</span>');
			return true;
		}

		$this->output('<p>Bookings to be notified: ' . count($bookings) . '</p>');
		
		$def_subject = $this->params->get('subject');

		foreach ($bookings as $booking)
		{
			if ($this->isTracked($booking['idcustomer']))
			{
				// the element has been already processed, skip it
				$this->output('<span>Customer ID ' . $booking['idcustomer'] . ' (' . $booking['customer_name'] . ') was already notified. Skipped.</span>');
				continue;
			}

			// get the email message
			$message = $this->params->get('tpl_text');
			$this->params->set('subject', $def_subject);

			// language translation
			$lang = JFactory::getLanguage();
			$vbo_tn = VikBooking::getTranslator();
			$vbo_tn::$force_tolang = null;
			$website_def_lang = $vbo_tn->getDefaultLang();
			if (!empty($booking['lang']))
			{
				if ($lang->getTag() != $booking['lang'])
				{
					if (VBOPlatformDetection::isWordPress())
					{
						// wp
						$lang->load('com_vikbooking', VIKBOOKING_LANG, $booking['lang'], true);
					}
					else
					{
						// J
						$lang->load('com_vikbooking', JPATH_SITE, $booking['lang'], true);
						$lang->load('com_vikbooking', JPATH_ADMINISTRATOR, $booking['lang'], true);
						$lang->load('joomla', JPATH_SITE, $booking['lang'], true);
						$lang->load('joomla', JPATH_ADMINISTRATOR, $booking['lang'], true);
					}
				}
				if ($website_def_lang != $booking['lang'])
				{
					// force the translation to start because contents should be translated
					$vbo_tn::$force_tolang = $booking['lang'];
				}

				// convert cron data into an array and re-encode parameters to preserve
				// the compatibility with the translator
				$cron_tn = (array) $this->getData();
				$cron_tn['params'] = json_encode($this->params->getProperties());

				$vbo_tn->translateContents($cron_tn, '#__vikbooking_cronjobs', array(), array(), $booking['lang']);
				$params_tn = json_decode($cron_tn['params'], true);
				if (is_array($params_tn) && array_key_exists('tpl_text', $params_tn))
				{
					$message = $params_tn['tpl_text'];
				}
				if (is_array($params_tn) && array_key_exists('subject', $params_tn))
				{
					$this->params->set('subject', $params_tn['subject']);
				}
			}
			else
			{
				if (VBOPlatformDetection::isWordPress())
				{
					// wp
					$lang->load('com_vikbooking', VIKBOOKING_LANG, $website_def_lang, true);
				}
				else
				{
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
				$send_res = $this->sendEmail($booking, $coupon_info, $message);
			}
			elseif (!strcasecmp($send_method, 'message'))
			{
				// attempt to send a guest message through the related OTA Messaging API
				$send_res = $this->sendGuestMessage($booking, $coupon_info, $message);
			}

			$this->output('<span>Result for sending ' . $send_method . ' to ' . $recipient . ' - ' . $booking['tot_bookings'] . ' Total Bookings - Customer ID ' . $booking['idcustomer'] . ' (' . $booking['customer_name'] . (!empty($booking['lang']) ? ' ' . $booking['lang'] : '') . '): ' . ($send_res !== false ? '<i class="vboicn-checkmark"></i>Success' : '<i class="vboicn-cancel-circle"></i>Failure') . ($this->params->get('test', 'OFF') == 'ON' ? ' (Test Mode ON)' : '') . '</span>');

			if ($send_res !== false)
			{
				// append execution log
				$this->appendLog($send_method . ' sent to ' . $recipient . ' - Customer ID ' . $booking['idcustomer'] . ' (' . $booking['customer_name'] . (!empty($booking['lang']) ? ' ' . $booking['lang'] : '') . ') added to coupon code ' . $coupon_info['code'] . ' - Total Bookings: ' . $booking['total_bookings']);

				// store in execution flag that this customer ID was notified
				$this->track($booking['idcustomer']);

				// assign the customer to the coupon code
				$this->assignCustomerDiscount($booking['idcustomer'], $coupon_info, $auto_discount);
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
		// invoke trait method
		return $this->traitIsTracked($element);
	}

	/**
	 * Sends an email notification to the given customer (booking).
	 * 
	 * @param   array 	$booking 	the customer (booking) information array.
	 * @param 	array 	$coupon 	the coupon record.
	 * @param 	string 	$message 	the message to send.
	 * 
	 * @return  boolean 			true if the message was sent, or false.
	 */
	private function sendEmail(array $booking, array $coupon, $message)
	{
		$vbo_app = VikBooking::getVboApplication();

		if (empty($booking['idcustomer']) || empty($booking['custmail']))
		{
			return false;
		}

		$admin_sendermail = VikBooking::getSenderMail();

		$vbo_tn = VikBooking::getTranslator();

		if (!trim($message))
		{
			// no message, use default template
			$message = $this->getDefaultTemplate();
		}

		// parse message tokens
		$message = $this->parseCustomerEmailTemplate($message, $booking, $coupon, $vbo_tn);
		if (empty($message))
		{
			return false;
		}

		$is_html = (strpos($message, '<') !== false || strpos($message, '</') !== false);
		if ($is_html && !preg_match("/(<\/?br\/?>)+/", $message))
		{
			// when no br tags found, apply nl2br
			$message = nl2br($message);
		}

		return $vbo_app->sendMail($admin_sendermail, $admin_sendermail, $booking['custmail'], $admin_sendermail, $this->params->get('subject'), $message, $is_html);
	}

	/**
	 * Sends a guest message through the related OTA messaging API in place of an email.
	 * 
	 * @param   array 	$booking 	booking array record.
	 * @param 	array 	$coupon 	the coupon record.
	 * @param 	string 	$message 	the message content for the email.
	 * 
	 * @return  boolean 			True if the message was sent, false otherwise.
	 * 
	 * @since 	1.16.5 (J) - 1.6.5 (WP)
	 * 
	 * @requires VCM >= 1.8.20
	 */
	private function sendGuestMessage(array $booking, array $coupon, $message)
	{
		if (empty($booking['channel']) || empty($booking['idorderota']))
		{
			return false;
		}

		$vbo_tn = VikBooking::getTranslator();

		if (!trim($message))
		{
			// no message, use default template
			$message = $this->getDefaultTemplate();
		}

		$message = $this->parseCustomerEmailTemplate($message, $booking, $coupon, $vbo_tn);
		if (empty($message))
		{
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

	/**
	 * Parses the email message to replace any special tag/token with the proper value.
	 * 
	 * @param 	string 	$message 	the raw message string.
	 * @param 	array 	$booking 	the booking record with the customer details.
	 * @param 	array 	$coupon 	the coupon record.
	 * @param 	object 	$vbo_tn 	the translation object.
	 * 
	 * @return 	string 				the parsed email message ready to be sent.
	 */
	private function parseCustomerEmailTemplate($message, array $booking, array $coupon, $vbo_tn = null)
	{
		$tpl = $message;

		$website_base_uri = JUri::root();

		$tpl = str_replace('{coupon_code}', $coupon['code'], $tpl);
		$tpl = str_replace('{customer_name}', $booking['customer_name'], $tpl);
		$tpl = str_replace('{customer_pin}', $booking['customer_pin'], $tpl);
		$tpl = str_replace('{total_bookings}', $booking['tot_bookings'], $tpl);
		$tpl = str_replace('{website_link}', '<a href="' . $website_base_uri . '">' . $website_base_uri . '</a>', $tpl);
		$tpl = str_replace('{website_url}', $website_base_uri, $tpl);

		// booking link
		$use_sid = empty($booking['sid']) && !empty($booking['idorderota']) ? $booking['idorderota'] : $booking['sid'];
		if (VBOPlatformDetection::isWordPress())
		{
			/**
			 * @wponly 	we let the libraries obtain the best item ID
			 */
			$bestitemid = null;
		}
		else
		{
			// J
			$bestitemid = VikBooking::findProperItemIdType(['booking']);
		}
		$book_url = VikBooking::externalroute("index.php?option=com_vikbooking&view=booking&sid=" . $use_sid . "&ts=" . $booking['ts'], false, $bestitemid);

		// access the model for shortening URLs
		$model = VBOModelShortenurl::getInstance($onlyRouted = true)->setBooking($booking);
		$book_url = $model->getShortUrl($book_url);

		$tpl = str_replace('{booking_link}', '<a href="' . $book_url . '">' . $book_url . '</a>', $tpl);
		$tpl = str_replace('{booking_url}', $book_url, $tpl);

		return $tpl;
	}

	/**
	 * Assigns the customer to the coupon list of enabled users.
	 * 
	 * @param 	int 	$idcustomer 	the customer ID.
	 * @param 	array 	$coupon 		the coupon record.
	 * @param 	int 	$automatic 		1 for automatic discount, or 0.
	 * 
	 * @return 	boolean
	 */
	private function assignCustomerDiscount($idcustomer, array $coupon = [], $automatic = 0)
	{
		if (empty($idcustomer) || empty($coupon['id']))
		{
			return false;
		}

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);

		$query->select('*');
		$query->from('#__vikbooking_customers_coupons');
		$query->where($db->qn('idcustomer') . '=' . (int) $idcustomer);
		$query->where($db->qn('idcoupon') . '=' . (int) $coupon['id']);

		$db->setQuery($query);
		$db->execute();

		if ($db->getNumRows())
		{
			// customer ID already assigned to this coupon discount
			return false;
		}

		$cust_disc = new stdClass;
		$cust_disc->idcustomer = (int) $idcustomer;
		$cust_disc->idcoupon = (int) $coupon['id'];
		$cust_disc->automatic = (int) $automatic;

		return $db->insertObject('#__vikbooking_customers_coupons', $cust_disc);
	}

	/**
	 * Returns a list of coupon codes.
	 * 
	 * @return  string
	 */
	private function getCouponsList()
	{
		$currency_symb = VikBooking::getCurrencySymb();

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);

		$query->select('*');
		$query->from($db->qn('#__vikbooking_coupons'));
		$query->order($db->qn('code') . ' ASC');

		$db->setQuery($query);

		$coupons = $db->loadAssocList();

		$list = [];
		foreach ($coupons as $coupon) {
			$list[$coupon['id']] = $coupon['code'] . ' (' . ($coupon['percentot'] == 2 ? $currency_symb : '') . $coupon['value'] . ($coupon['percentot'] == 1 ? '%' : '') . ')';
		}

		// append default value as last element to have an associative array, not numeric
		$list[] = JText::_('VBO_PLEASE_SELECT');

		return $list;
	}

	/**
	 * Gets the information about the selected coupon ID.
	 * 
	 * @param 	int 	$id 	the coupon record ID.
	 * 
	 * @return 	array 			coupon record or empty array.
	 */
	private function getCoupon($id)
	{
		$dbo = JFactory::getDbo();

		$dbo->setQuery("SELECT * FROM `#__vikbooking_coupons` WHERE `id`=" . (int) $id, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			return [];
		}

		return $dbo->loadAssoc();
	}

	/**
	 * Returns the default e-mail template.
	 * 
	 * @return  string
	 */
	private function getDefaultTemplate()
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
<p>We would like to thank you for all your reservations at $company_name.</p>
<p>We have reserved a special discount for our most loyal customers that will be applied to your next reservation.</p>
<p>Please use the code {coupon_code}.</p>
<p><br></p>
<p><br></p>
<p>Thank you!</p>
<p><a href="{website_url}">$company_name</a></p>
<hr class="vbo-editor-hl-mailwrapper">
<p><br></p>
HTML
			;
		}

		return $tmpl;
	}
}
