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

class VikBookingCronJobInvoicesGenerator extends VBOCronJob
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

		// join special tags with conditional texts to construct a list of editor buttons,
		// displayed within the toolbar of Quill editor
		$editor_btns = array_merge($special_tags_base, $condtext_tags);

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
				'html'  => '<h4><i class="vboicn-file-text2"></i><i class="vboicn-coin-dollar"></i>&nbsp;' . $this->getTitle() . '</h4>',
			],
			'checktype' => [
				'type'    => 'select',
				'label'   => JText::_('VBOCRONINVGENPARAMCWHEN'),
				'options' => [
					'checkin'  => JText::_('VBOCRONINVGENPARAMCWHENA'),
					'payment'  => JText::_('VBOCRONINVGENPARAMCWHENB'),
					'checkout' => JText::_('VBOCRONINVGENPARAMCWHENC'),
				],
			],
			'invdates' => [
				'type'    => 'select',
				'label'   => JText::_('VBINVUSEDATE'),
				'options' => [
					1 => JText::_('VBOCRONINVGENPARAMDGEN'),
					0 => JText::_('VBINVUSEDATEBOOKING'),
				],
			],
			'invlangtn' => [
				'type'    => 'select',
				'label'   => JText::_('VBOBOOKINGLANG'),
				'options' => [
					1 => JText::_('VBRENTALORD'),
					0 => JText::_('VBTRANSLATIONDEFLANG'),
				],
			],
			'skipotas' => [
				'type'    => 'select',
				'label'   => JText::_('VBOCRONINVGENPARAMSKIPOTAS'),
				'help'    => JText::_('VBOCRONINVGENPARAMSKIPOTASHELP'),
				'options' => [
					 'ON'  => JText::_('VBYES'),
					 'OFF' => JText::_('VBNO'),
				],
			],
			'test' => [
				'type'    => 'select',
				'label'   => JText::_('VBOCRONINVGENPARAMTEST'),
				'help'    => JText::_('VBOCRONINVGENPARAMTESTHELP'),
				'default' => 'OFF',
				'options' => [
					'ON'  => JText::_('VBYES'),
					'OFF' => JText::_('VBNO'),
				],
			],
			'emailsend' => [
				'type' => 'select',
				'label' => JText::_('VBOCRONINVGENPARAMEMAILSEND'),
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
				'label'   => JText::_('VBOCRONINVGENPARAMTEXT'),
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
							jQuery("#" + taid).trigger("change");
						}
					}
				</script>',
			],
			'help' => [
				'type'  => 'custom',
				'label' => '',
				'html'  => '<p class="vbo-cronparam-suggestion"><i class="vboicn-lifebuoy"></i>' . JText::_('VBOCRONINVGENHELP') . '</p>',
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
		return JText::_('VBO_CRON_INVOICES_GENERATOR_TITLE');
	}
	
	/**
	 * Executes the cron job.
	 * 
	 * @return  boolean  True on success, false otherwise.
	 */
	protected function execute()
	{
		$checktype = $this->params->get('checktype', 'checkin');

		$db = JFactory::getDbo();
		$lang = JFactory::getLanguage();

		$ts_limit_firstrun = 0;

		if ($this->getData()->flag_int <= 0)
		{
			// first run ever of this cron - do not generate all the invoices but just the ones of this month
			$ts_limit_firstrun = mktime(0, 0, 0, date('n'), 1, date('Y'));
			$this->output('<p>This is the first execution ever of this cron: only the bookings of the current month will be read to prevent issues. Next run will have no limits.</p>');
		}

		$start_ts = time();
		
		$this->output('<p>Reading bookings with ' . ($checktype == 'checkout' ? 'check-out' : ($checktype == 'checkin' ? 'check-in' : 'confirmation')).' datetime earlier than: ' . date('c', $start_ts) . '</p>');
		
		$query = $db->getQuery(true);

		$query->select('o.*');
		$query->select($db->qn('co.idcustomer'));
		$query->select($db->qn('nat.country_name'));
		$query->select($db->qn('c.pin', 'customer_pin'));
		$query->select($db->qn('i.number'));
		$query->select(sprintf('CONCAT_WS(\' \', %s, %s) AS %s',
			$db->qn('c.first_name'),
			$db->qn('c.last_name'),
			$db->qn('customer_name')
		));

		$query->from($db->qn('#__vikbooking_orders', 'o'));
		$query->leftjoin($db->qn('#__vikbooking_customers_orders', 'co') . ' ON ' . $db->qn('co.idorder') . ' = ' . $db->qn('o.id'));
		$query->leftjoin($db->qn('#__vikbooking_customers', 'c') . ' ON ' . $db->qn('co.idcustomer') . ' = ' . $db->qn('c.id'));
		$query->leftjoin($db->qn('#__vikbooking_countries', 'nat') . ' ON ' . $db->qn('nat.country_3_code') . ' = ' . $db->qn('o.country'));
		$query->leftjoin($db->qn('#__vikbooking_invoices', 'i') . ' ON ' . $db->qn('i.idorder') . ' = ' . $db->qn('o.id'));

		$query->where([
			$db->qn('i.number') . ' IS NULL',
			$db->qn('o.status') . ' = ' . $db->q('confirmed'),
			$db->qn('o.total') . ' > 0',
			$db->qn('o.closure') . ' = 0',
			$db->qn('o.' . ($checktype == 'checkout' ? 'checkout' : ($checktype == 'checkin' ? 'checkin' : 'ts'))) . ' <= ' . $start_ts,
		]);

		if ($ts_limit_firstrun)
		{
			$query->where(
				$db->qn('o.' . ($checktype == 'checkout' ? 'checkout' : ($checktype == 'checkin' ? 'checkin' : 'ts'))) . ' >= ' . $ts_limit_firstrun
			);
		}

		$query->order($db->qn('o.id') . ' ASC');

		$db->setQuery($query);
		$bookings = $db->loadAssocList();

		if (!$bookings)
		{
			$this->output('<span>No invoices must be generated for this time span.</span>');
			return true;
		}
		
		$this->output('<p>Creating ' . count($bookings) . ' invoices.</p>');

		// whether invoices should be translated into the booking language
		$tn_invoices = (bool)intval($this->params->get('invlangtn', 0));
		
		$invoice_num = VikBooking::getNextInvoiceNumber();
		$def_subject = $this->params->get('subject');
		foreach ($bookings as $k => $booking) {
			if ($this->params->get('skipotas', 'OFF') == 'ON') {
				if (!empty($booking['idorderota'])) {
					$this->output('<span>Skipping the Booking ID '.$booking['id'].' because transmitted by the Channel Manager ('.$booking['channel'].' '.$booking['idorderota'].')</span>');
					continue;
				}
			}
			$message = $this->params->get('tpl_text');
			$this->params->set('subject', $def_subject);

			// language translation
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

			$message = $this->parseCronEmailTemplate($message, $booking);
			$gen_res = $this->params->get('test', 'OFF') == 'ON' ? false : VikBooking::generateBookingInvoice($booking, $invoice_num, '', (int)$this->params->get('invdates'), '', $tn_invoices);

			$this->output('<span>Result for generating the invoice for Booking ID '.$booking['id'].' ('.$booking['customer_name'].(!empty($booking['lang']) && $tn_invoices ? ' '.$booking['lang'] : '').'): '.($gen_res !== false ? '<i class="vboicn-checkmark"></i>Success' : '<i class="vboicn-cancel-circle"></i>Failure').($this->params->get('test', 'OFF') == 'ON' ? ' (Test Mode ON)' : '').'</span>');
			
			if ($gen_res !== false) {
				$invoice_num++;
				$this->appendLog('Invoice #'.$gen_res.' generated for Booking ID '.$booking['id'].' ('.$booking['customer_name'].(!empty($booking['lang']) ? ' '.$booking['lang'] : '').')');
				if ($this->params->get('emailsend', 'OFF') == 'ON') {
					$send_res = VikBooking::sendBookingInvoice($gen_res, $booking, $message, $this->params->get('subject'));
					
					$this->output('<span>Result for sending the invoice for Booking ID '.$booking['id'].' via eMail to '.$booking['custmail'].' ('.$booking['customer_name'].(!empty($booking['lang']) ? ' '.$booking['lang'] : '').'): '.($send_res !== false ? '<i class="vboicn-checkmark"></i>Success' : '<i class="vboicn-cancel-circle"></i>Failure').'</span>');
					
					$this->appendLog('Invoice #'.$gen_res.' for Booking ID '.$booking['id'].' was '.($send_res === true ? 'SUCCESSFULLY' : 'NOT').' sent to '.$booking['custmail']);
				}
			}
		}

		if ($invoice_num > VikBooking::getNextInvoiceNumber())
		{
			VBOFactory::getConfig()->set('invoiceinum', $invoice_num - 1);
		}
		
		return true;
	}
	
	/**
	 * Executes after processing the cron job.
	 * 
	 * @return  void
	 */
	protected function postflight()
	{
		/**
		 * Update cron record. The field flag_int is set to 1 every time so the first run of this cron
		 * can generate the invoices only of the current month to avoid problems.
		 */
		$this->getData()->flag_int = 1;
	}

	private function parseCronEmailTemplate($message, $booking)
	{
		$dbo = JFactory::getDbo();

		if (!trim($message))
		{
			// no message, use default template
			$message = $this->getDefaultTemplate();
		}

		$tpl = $message;
		$booking_rooms = array();
		$q = "SELECT `or`.*,`r`.`name` AS `room_name` FROM `#__vikbooking_ordersrooms` AS `or` LEFT JOIN `#__vikbooking_rooms` `r` ON `r`.`id`=`or`.`idroom` WHERE `or`.`idorder`=".(int)$booking['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$booking_rooms = $dbo->loadAssocList();
		}
		$vbo_tn = VikBooking::getTranslator();
		$vbo_tn->translateContents($booking_rooms, '#__vikbooking_rooms', array('id' => 'idroom', 'name' => 'room_name'));

		/**
		 * Parse all conditional text rules.
		 * 
		 * @since 	1.14 (J) - 1.4.0 (WP)
		 */
		VikBooking::getConditionalRulesInstance()
			->set(array('booking', 'rooms'), array($booking, $booking_rooms))
			->parseTokens($tpl);
		//

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
		$tpl = str_replace('{remaining_balance}', VikBooking::numberFormat($remaining_bal), $tpl);
		$tpl = str_replace('{customer_pin}', $booking['customer_pin'], $tpl);

		$use_sid = empty($booking['sid']) && !empty($booking['idorderota']) ? $booking['idorderota'] : $booking['sid'];

		$book_link 	= '';
		if (VBOPlatformDetection::isWordPress()) {
			/**
			 * @wponly 	Rewrite order view URI
			 */
			$model 		= JModel::getInstance('vikbooking', 'shortcodes', 'admin');
			$itemid 	= $model->best('booking');
			if ($itemid) {
				$book_link = JRoute::_("index.php?option=com_vikbooking&Itemid={$itemid}&view=booking&sid={$use_sid}&ts={$booking['ts']}", false);
			}
		} else {
			// J
			$bestitemid = VikBooking::findProperItemIdType(array('booking'));
			$book_link = VikBooking::externalroute("index.php?option=com_vikbooking&view=booking&sid=" . $use_sid . "&ts=" . $booking['ts'], false, (!empty($bestitemid) ? $bestitemid : null));
		}
		$tpl = str_replace('{booking_link}', $book_link, $tpl);

		return $tpl;
	}

	/**
	 * Returns the default e-mail template.
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
<p>Attached to this message you will find the invoice for your booking.</p>
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
