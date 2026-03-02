<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - E4J srl
 * @copyright   Copyright (C) 2023 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.payment.payment');

/**
 * This class is used to collect payments through VikBooking
 * by using the PayPal Checkout integration (JavaScript SDK).
 *
 * @since 	1.6.1
 */
class VikBookingPayPalCheckoutPayment extends JPayment
{	
	/**
	 * @override
	 * Class constructor.
	 *
	 * @param 	string 	$alias 	 The name of the plugin that requested the payment.
	 * @param 	mixed 	$order 	 The order details to start the transaction.
	 * @param 	mixed 	$params  The configuration of the payment.
	 */
	public function __construct($alias, $order, $params = [])
	{
		parent::__construct($alias, $order, $params);
	}

	/**
	 * @override
	 * Method used to build the associative array 
	 * to allow the plugins to construct a configuration form.
	 *
	 * @return 	array 	The associative array.
	 */
	protected function buildAdminParameters()
	{
		return [
			'logo' => [
				'type' 	=> 'custom',
				'label' => '',
				'html' 	=> '<img src="https://www.paypalobjects.com/ppdevdocs/v1/img/docs/pay-later/horizontal_web.png"/>',
			],
			'environment' => [
				'type' 	  => 'select',
				'label'   => 'Environment',
				'help'    => 'Choose Sandbox to enable the test mode and perform no real transactions.',
				'default' => 'sandbox',
				'options' => [
					'live' => 'Live',
					'sandbox' => 'Sandbox (Test Mode)',
				],
			],
			'client_id' => [
				'type' 	=> 'text',
				'label' => 'Client ID (Live)',
				'help'  => 'It is not your merchant email address, it\'s a specific ID that you can find in your PayPal account.',
			],
			'client_secret' => [
				'type' 	=> 'password',
				'label' => 'Client Secret (Live)',
				'help'  => 'From the same page on your PayPal account you can find both the Client ID and Client Secret values.',
			],
			'client_id_test' => [
				'type' 	=> 'text',
				'label' => 'Client ID (Sandbox)',
				'help'  => 'It is not your merchant email address, it\'s a specific ID that you can find in your PayPal account. For Test environment only.',
			],
			'client_secret_test' => [
				'type' 	=> 'password',
				'label' => 'Client Secret (Sandbox)',
				'help'  => 'From the same page on your PayPal account you can find both the Client ID and Client Secret values. For Test environment only.',
			],
			'layout' => [
				'type' 	  => 'select',
				'label'   => 'Layout',
				'default' => 'vertical',
				'options' => [
					'vertical'   => 'Vertical',
					'horizontal' => 'Horizontal',
				],
			],
			'color' => [
				'type' 	  => 'select',
				'label'   => 'Color',
				'default' => 'gold',
				'options' => [
					'gold'   => 'Gold',
					'blue'   => 'Blue',
					'silver' => 'Silver',
					'white'  => 'White',
					'black'  => 'Black',
				],
			],
			'shape' => [
				'type' 	  => 'select',
				'label'   => 'Shape',
				'default' => 'rect',
				'options' => [
					'rect' => 'Rectangular button',
					'pill' => 'Rounded button',
				],
			],
			'tagline' => [
				'type' 	  => 'checkbox',
				'label'   => 'Display tagline text',
				'default' => 1,
			],
		];
	}

	/**
	 * @override
	 * Method used to begin a payment transaction.
	 * This method usually generates the HTML form of the payment.
	 * The HTML contents can be echoed directly because this method
	 * is executed always within a buffer.
	 *
	 * @return 	void
	 */
	protected function beginTransaction()
	{
		/**
		 * Set up javascript SDK URL.
		 * 
		 * @link https://developer.paypal.com/sdk/js/configuration/
		 */
		$sdkUrl = new JUri('https://www.paypal.com/sdk/js');
		$sdkUrl->setVar('currency', $this->get('transaction_currency'));
		$sdkUrl->setVar('components', 'buttons');
		$sdkUrl->setVar('integration-date', '2022-06-14');

		if ($this->getParam('environment', 'live') === 'live') {
			$sdkUrl->setVar('client-id', $this->getParam('client_id'));
		} else {
			$sdkUrl->setVar('client-id', $this->getParam('client_id_test'));
		}

		// set up notify URL to validate the payment
		$notifyUrl = new JUri($this->get('notify_url'));
		$notifyUrl->setVar('payment_id', '{PAYMENT_ID}');

		// load JS SDK
		JHtml::_('script', (string) $sdkUrl);

		// booking record
		$booking = (array)$this->get('details', []);

		/**
		 * The values item_name and item_number should not exceed the limit of 127 chars
		 * or a BAD_INPUT_ERROR will be raised before even starting the transaction.
		 * 
		 * @since 	1.15.4 (J) - 1.5.9 (WP)
		 */
		$tn_item_name 	= $this->get('transaction_name');
		$multib_substr  = function_exists('mb_substr');
		if (strlen($tn_item_name) >= 127) {
			$tn_item_name = $multib_substr ? mb_substr($tn_item_name, 0, 127, 'UTF-8') : substr($tn_item_name, 0, 127);
		}

		$depositmess = "";
		if ($this->get('leave_deposit')) {
			$depositmess = "<p class=\"vbo-leave-deposit\"><span>" . JText::_('VBLEAVEDEPOSIT') . "</span>" . $this->get('currency_symb') . " " . VikBooking::numberFormat($this->get('total_to_pay')) . "</p><br/>";
		}

		// deposit
		echo $depositmess;

		// payment notes
		$info = $this->get('payment_info');
		if (VBOPlatformDetection::isWordPress()) {
			echo wpautop($info['note']);
		} else {
			echo $info['note'];
		}

		// render payment
		?>
		<div id="paypal-button-container">
			<!-- PayPal buttons will be rendered here -->
		</div>

		<script>
			(function($) {
				'use strict';

				let notifyUrl = '<?php echo $notifyUrl; ?>';

				$(function() {
					paypal.Buttons({
						style: {
							layout: '<?php echo $this->getParam('layout', 'vertical'); ?>',
							color:  '<?php echo $this->getParam('color', 'gold'); ?>',
							shape:  '<?php echo $this->getParam('shape', 'rect'); ?>',
							label:  'paypal',
							tagline: <?php echo $this->getParam('layout') == 'horizontal' && $this->getParam('tagline') ? 'true' : 'false'; ?>,
						},
						createOrder: (data, actions) => {
							// This function sets up the details of the transaction, including the amount and line item details.
							return actions.order.create({
								purchase_units: [{
									custom_id: '<?php echo $booking['id']; ?>',
									description: '<?php echo addslashes($tn_item_name); ?>',
									amount: {
										value: <?php echo round($this->get('total_to_pay'), 2); ?>,
										currency_code: '<?php echo $this->get('transaction_currency'); ?>',
									},
								}]
							});
						},
						onApprove: (data, actions) => {
							// This function captures the funds from the transaction.
							return actions.order.capture().then((details) => {
								document.location.href = notifyUrl.replace(/{PAYMENT_ID}/, details.id);
							});
						},
						onError: (err) => {
							// For example, redirect to a specific error page
							console.error('onError', err);
						},
					}).render('#paypal-button-container');
				});
			})(jQuery);
		</script>

		<?php

		return true;
	}

	/**
	 * @override
	 * Method used to validate the payment transaction.
	 * It is usually an end-point that the providers use to POST the transaction data.
	 *
	 * @param 	JPaymentStatus 	&$status 	The status object. In case the payment was 
	 * 										successful, you should invoke: $status->verified().
	 *
	 * @return 	boolean
	 *
	 * @see 	JPaymentStatus
	 */
	protected function validateTransaction(JPaymentStatus &$status)
	{
		// fetch payment ID from request
		$paymentId = JFactory::getApplication()->input->getString('payment_id');

		if (!$paymentId)
		{
			$status->appendLog('Payment ID not found in request.');
			return false;
		}

		$http = new JHttp;

		if ($this->getParam('environment', 'live') === 'live')
		{
			$base         = 'https://api.paypal.com'; 
			$clientId     = $this->getParam('client_id');
			$clientSecret = $this->getParam('client_secret');
		}
		else
		{
			$base         = 'https://api.sandbox.paypal.com'; 
			$clientId     = $this->getParam('client_id_test');
			$clientSecret = $this->getParam('client_secret_test');
		}

		/**
		 * Obtain order details.
		 * 
		 * @link https://developer.paypal.com/docs/api/orders/v2/#orders_get
		 */
		$response = $http->get($base . '/v2/checkout/orders/' . $paymentId, [
			'Content-Type'  => 'application/json',
			'Authorization' => 'Basic ' . base64_encode($clientId . ':' . $clientSecret),
		]);

		if ($response->code != 200)
		{
			// an error has occurred, try to decode the returned error message
			$status->appendLog("PayPal Error ({$response->code}):\n{$response->body}");
			return false;
		}

		$order = json_decode($response->body);

		if (!is_object($order) || !isset($order->status))
		{
			// unexpected PayPal response
			$status->appendLog("Unexpected PayPal response:\n" . print_r($response, true));
			return false;
		}

		if ($order->status !== 'COMPLETED')
		{
			// the created payment does not seem to be paid
			$status->appendLog('Payment not verified.');
			$status->appendLog('<pre>' . print_r($order, true) . '</pre>');
			return false;
		}

		$totPaid = 0;
		$paypal_currency = $this->get('transaction_currency');

		// scan all payment items to calculate the total amount paid
		foreach ($order->purchase_units as $unit)
		{
			if ($unit->amount->currency_code != $paypal_currency)
			{
				// invalid currency, ignore payment
				continue;
			}

			// create total amount paid
			$totPaid += $unit->amount->value;
		}

		if (!$totPaid)
		{
			// the currency used for the payment is not valid
			$status->appendLog('Invalid payment currency.');
			$status->appendLog('<pre>' . print_r($order, true) . '</pre>');
			return false;
		}

		// payment verified
		$status->paid($totPaid);

		if ($fees_value = ($order->purchase_units[0]->payments->captures[0]->seller_receivable_breakdown->paypal_fee->value ?? null)) 
		{
			// set payment processing fees
			$status->setData('fees', (float) $fees_value);
		}

		$status->verified();

		return true;
	}

	/**
	 * @override
	 * Method used to finalise the payment.
	 * e.g. enter here the code used to redirect the
	 * customers to a specific landing page.
	 *
	 * @param 	boolean  $res 	True if the payment was successful, otherwise false.
	 *
	 * @return 	void
	 */
	protected function complete($res)
	{
		$app 	= JFactory::getApplication();

		$itemid = $this->getItemID();

		$use_sid = $this->get('sid', $this->get('idorderota'));

		$url  = 'index.php?option=com_vikbooking&view=booking&sid=' . $use_sid . '&ts=' . $this->get('ts') . (!empty($itemid) ? '&Itemid=' . $itemid : '');

		if ($res < 1)
		{
			$app->enqueueMessage(JText::_('VBCCPAYMENTNOTVERIFIED'), 'error');
		}
		else
		{
			$app->enqueueMessage(JText::_('VBOUPSELLRESULTOK'));
		}

		$app->redirect(JRoute::_($url, false));
		$app->close();
	}

	/**
	 * @wponly
	 * Returns the proper Item ID to use.
	 *
	 * @return 	integer  The item ID, if any.
	 */
	protected function getItemID()
	{
		$app 	= JFactory::getApplication();
		$input  = $app->input;

		$itemid = $input->getInt('Itemid');

		if (!$itemid)
		{
			$model 	= JModel::getInstance('vikbooking', 'shortcodes', 'admin');
			$itemid = $model->all('post_id', $full = true);
			
			if (count($itemid))
			{
				$itemid = $itemid[0]->post_id;
			}
		}

		return (int) $itemid;
	}
}
