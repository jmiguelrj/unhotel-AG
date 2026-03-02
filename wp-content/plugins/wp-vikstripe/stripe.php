<?php
/**
 * @package     VikStripe
 * @subpackage  Stripe
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2019 VikWP All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.payment.payment');
if (!class_exists('Stripe\Stripe')) {
	JLoader::import('Stripe.Stripe', VIKSTRIPE_DIR);	
}

/**
 * This class is used to collect payments through the Stripe gateway.
 *
 * @since 1.0
 */
abstract class AbstractStripePayment extends JPayment
{
	/**
	 * @override
	 * Class constructor.
	 *
	 * @param 	string 	$alias 	 The name of the plugin that requested the payment.
	 * @param 	mixed 	$order 	 The order details to start the transaction.
	 * @param 	mixed 	$params  The configuration of the payment.
	 */
	public function __construct($alias, $order, $params = array())
	{
		parent::__construct($alias, $order, $params);
		
		
		$this->setParam('currency', strtolower(substr($this->getParam('currency', ''), 0, 3)));
		$this->setParam('ssl', $this->getParam('ssl') == __('Yes', 'vikstripe') ? 1 : 0);
	}

	/**
	 * @override
	 * Method used to build the associative array 
	 * to allow the plugins to construct a configuration form.
	 *
	 * In case the payment needs an API Key, the array should
	 * be built as follows:
	 *
	 * {"apikey": {"type": "text", "label": "API Key"}}
	 *
	 * @return 	array 	The associative array.
	 */
	protected function buildAdminParameters()
	{
		return array(
			'logo' => array(
				'label' => '',
				'type'  => 'custom',
				'html'  => '<img src="' . VIKSTRIPE_URI . 'Stripe/stripe_logo.png" style="margin-bottom: 15px;"/>',
			),
			'secretkey' => array(
				'label' => __('Secret Key', 'vikstripe'),
				'type'  => 'password',
			),
			'pubkey'    => array(
				'label' => __('Publishable Key', 'vikstripe'),
				'type'  => 'password',
			),
			'currency'  => array(
				'label' => __('Currency', 'vikstripe'),
				'type'  => 'select',
				'options' => array(
							'AED: United Arab Emirates Dirham',
							'AFN: Afghan Afghani',
							'ALL: Albanian Lek',
							'AMD: Armenian Dram',
							'ANG: Netherlands Antillean Gulden',
							'AOA: Angolan Kwanza',
							'ARS: Argentine Peso',
							'AUD: Australian Dollar',
							'AWG: Aruban Florin',
							'AZN: Azerbaijani Manat',
							'BAM: Bosnia & Herzegovina Convertible Mark',
							'BBD: Barbadian Dollar',
							'BDT: Bangladeshi Taka',
							'BGN: Bulgarian Lev',
							'BIF: Burundian Franc',
							'BMD: Bermudian Dollar',
							'BND: Brunei Dollar',
							'BOB: Bolivian Boliviano',
							'BRL: Brazilian Real',
							'BSD: Bahamian Dollar',
							'BWP: Botswana Pula',
							'BZD: Belize Dollar',
							'CAD: Canadian Dollar',
							'CDF: Congolese Franc',
							'CHF: Swiss Franc',
							'CLP: Chilean Peso',
							'CNY: Chinese Renminbi Yuan',
							'COP: Colombian Peso',
							'CRC: Costa Rican Colón',
							'CVE: Cape Verdean Escudo',
							'CZK: Czech Koruna',
							'DJF: Djiboutian Franc',
							'DKK: Danish Krone',
							'DOP: Dominican Peso',
							'DZD: Algerian Dinar',
							'EEK: Estonian Kroon',
							'EGP: Egyptian Pound',
							'ETB: Ethiopian Birr',
							'EUR: Euro',
							'FJD: Fijian Dollar',
							'FKP: Falkland Islands Pound',
							'GBP: British Pound',
							'GEL: Georgian Lari',
							'GIP: Gibraltar Pound',
							'GMD: Gambian Dalasi',
							'GNF: Guinean Franc',
							'GTQ: Guatemalan Quetzal',
							'GYD: Guyanese Dollar',
							'HKD: Hong Kong Dollar',
							'HNL: Honduran Lempira',
							'HRK: Croatian Kuna',
							'HTG: Haitian Gourde',
							'HUF: Hungarian Forint',
							'IDR: Indonesian Rupiah',
							'ILS: Israeli New Sheqel',
							'INR: Indian Rupee',
							'ISK: Icelandic Króna',
							'JMD: Jamaican Dollar',
							'JPY: Japanese Yen',
							'KES: Kenyan Shilling',
							'KGS: Kyrgyzstani Som',
							'KHR: Cambodian Riel',
							'KMF: Comorian Franc',
							'KRW: South Korean Won',
							'KYD: Cayman Islands Dollar',
							'KZT: Kazakhstani Tenge',
							'LAK: Lao Kip',
							'LBP: Lebanese Pound',
							'LKR: Sri Lankan Rupee',
							'LRD: Liberian Dollar',
							'LSL: Lesotho Loti',
							'LTL: Lithuanian Litas',
							'LVL: Latvian Lats',
							'MAD: Moroccan Dirham',
							'MDL: Moldovan Leu',
							'MGA: Malagasy Ariary',
							'MKD: Macedonian Denar',
							'MNT: Mongolian Tögrög',
							'MOP: Macanese Pataca',
							'MRO: Mauritanian Ouguiya',
							'MUR: Mauritian Rupee',
							'MVR: Maldivian Rufiyaa',
							'MWK: Malawian Kwacha',
							'MXN: Mexican Peso',
							'MYR: Malaysian Ringgit',
							'MZN: Mozambican Metical',
							'NAD: Namibian Dollar',
							'NGN: Nigerian Naira',
							'NIO: Nicaraguan Córdoba',
							'NOK: Norwegian Krone',
							'NPR: Nepalese Rupee',
							'NZD: New Zealand Dollar',
							'PAB: Panamanian Balboa',
							'PEN: Peruvian Nuevo Sol',
							'PGK: Papua New Guinean Kina',
							'PHP: Philippine Peso',
							'PKR: Pakistani Rupee',
							'PLN: Polish Złoty',
							'PYG: Paraguayan Guaraní',
							'QAR: Qatari Riyal',
							'RON: Romanian Leu',
							'RSD: Serbian Dinar',
							'RUB: Russian Ruble',
							'RWF: Rwandan Franc',
							'SAR: Saudi Riyal',
							'SBD: Solomon Islands Dollar',
							'SCR: Seychellois Rupee',
							'SEK: Swedish Krona',
							'SGD: Singapore Dollar',
							'SHP: Saint Helenian Pound',
							'SLL: Sierra Leonean Leone',
							'SOS: Somali Shilling',
							'SRD: Surinamese Dollar',
							'STD: São Tomé and Príncipe Dobra',
							'SVC: Salvadoran Colón',
							'SZL: Swazi Lilangeni',
							'THB: Thai Baht',
							'TJS: Tajikistani Somoni',
							'TOP: Tongan Paʻanga',
							'TRY: Turkish Lira',
							'TTD: Trinidad and Tobago Dollar',
							'TWD: New Taiwan Dollar',
							'TZS: Tanzanian Shilling',
							'UAH: Ukrainian Hryvnia',
							'UGX: Ugandan Shilling',
							'USD: United States Dollar',
							'UYU: Uruguayan Peso',
							'UZS: Uzbekistani Som',
							'VEF: Venezuelan Bolívar',
							'VND: Vietnamese Đồng',
							'VUV: Vanuatu Vatu',
							'WST: Samoan Tala',
							'XAF: Central African Cfa Franc',
							'XCD: East Caribbean Dollar',
							'XOF: West African Cfa Franc',
							'XPF: Cfp Franc',
							'YER: Yemeni Rial',
							'ZAR: South African Rand',
							'ZMW: Zambian Kwacha',
					),
			),
			'ssl' => array(
				'label'   => __('Use SSL', 'vikstripe'),
				'type'    => 'select',
				'options' => array(
					__('No', 'vikstripe'),
					__('Yes', 'vikstripe'),
				),
			),
			'transaction_type' => [
				'label'  => __('Transaction Type', 'vikstripe'),
				'help'   => __('Describes the type of transaction being performed by Checkout in order to customize relevant text on the page, such as the submit button.', 'vikstripe'),
				'type'   => 'select',
				'options' => [
					'pay'       => __('Payment', 'vikstripe'),
					'book'      => __('Booking', 'vikstripe'),
					'subscribe' => __('Subscription', 'vikstripe'),
				],
			],
			'skipbtn' => array(
				'label'   => __('Auto-redirect', 'vikstripe'),
				'help'    => __('Enable this option to auto-redirect the customers to the payment page instead of asking them to click the "PAY NOW" button.', 'vikstripe'),
				'type'    => 'select',
				'options' => array(
					1 => __('No', 'vikstripe'),
					0 => __('Yes', 'vikstripe'),
				),
			),
			'paytype' => array(
				'label'   => __('Payment Type', 'vikstripe'),
				'type'    => 'select',
				'options' => array(
				    'automatic' => __('Capture', 'vikstripe'),
					'manual' => __('Authorization', 'vikstripe'),
				),
			),
			'use_decimals' => array(
				'label'   => __('Currency has decimals?', 'vikstripe'),
				'help'    => __('For currencies supporting decimals, Stripe requires the transaction amounts to be multiplied by 100 to express them as integer values.', 'vikstripe'),
				'type'    => 'select',
				'options' => array(
					1 => __('Yes', 'vikstripe'),
					0 => __('No', 'vikstripe'),
				),
			),
			'auto_pay_meths' => array(
				'label'   => __('Enable automatic payment methods', 'vikstripe'),
				'help'    => __('This is to allow Stripe to suggest alternative payment methods to clients, in case you enabled some from your Stripe Dashboard.', 'vikstripe'),
				'type'    => 'select',
				'options' => array(
					1 => __('Yes', 'vikstripe'),
					0 => __('No', 'vikstripe'),
				),
			),
			'future_usage' => array(
				'label'   => __('Set up Future Usage', 'vikstripe'),
				'help'    => __('Leave this setting enabled if you do not offer automatic payment methods beside the credit card. Some additional payment methods, like Klarna, may require this setting to be disabled as it is not a reusable payment method. ', 'vikstripe'),
				'type'    => 'select',
				'options' => array(
					1 => __('Yes', 'vikstripe'),
					0 => __('No', 'vikstripe'),
				),
			),
			'request_extended_authorization' => array(
				'label'   => __('Extended authorization', 'vikstripe'),
				'help'    => __('Extended authorizations have a longer authorization validity period, which allows you to hold customer funds for longer than standard authorization validity windows.', 'vikstripe'),
				'type'    => 'select',
				'options' => array(
					1 => __('Yes', 'vikstripe'),
					0 => __('No', 'vikstripe'),
				),
			),
			'companyname' => array(
				'label' => __('Company Name', 'vikstripe'),
				'type'  => 'text',
			),
			'imageurl' => array(
				'label' => __('Image URL', 'vikstripe') . '//' . __('An image to be displayed during the purchase.', 'vikstripe'),
				'type'  => 'text',
			),
			'transaction_metadata' => array(
				'label' => __('Metadata', 'vikstripe'),
				'help'  => __('Additional information to pass along to the payment.', 'vikstripe'),
				'type'  => 'text',
			),
		);
	}
	
	/**
	 * Creates a customer in Stripe if it does not exist yet.
	 * 
	 * @param 	\Stripe\StripeClient 	$stripe 	The Stripe client instance.
	 * @param 	array 					$booking_customer 	The booking customer data.
	 * 
	 * @return 	\Stripe\Customer 	The created or found customer.
	 */
	protected function createCustomer($stripe, $booking_customer)
	{
		// search for an existing customer by email
		$already_created_customer = $stripe->customers->search([
			'query' => 'email:\''. $booking_customer['email'] . '\'',
		]);

		// if the customer already exists, return it
		if (!empty($already_created_customer['data']))
		{
			$customer = $already_created_customer['data'][0];
			return $customer;
		}

		// build the customer name as different channel can offer different fields
		$name = '';
		if ($booking_customer['guest_name']) 
		{
			$name = $booking_customer['guest_name'];
		}
		else 
		{
			$name = $booking_customer['first_name'] . " " . $booking_customer['last_name'];
		}
		
		// create a new customer
		$customer = $stripe->customers->create([
				'name'    => $name,
				'email'   => $booking_customer['email'],
				'phone'   => $booking_customer['phone'],
			]);
		
		return $customer;
	}

	/**
	 * Creates a Stripe Checkout Session.
	 * 
	 * @param 	\Stripe\StripeClient 	$stripe 	The Stripe client instance.
	 * @param 	array 					$customer 	The customer data.
	 * 
	 * @return 	\Stripe\Checkout\Session 	The created checkout session.
	 */
	protected function createSession($stripe, $customer)
	{
		// check if the session already exists
		$session = get_option("stripe_order_{$this->get('oid')}");

		if ($session) 
		{
			try 
			{
				// retrieve the session
				$checkout_session = $stripe->checkout->sessions->retrieve($session);
				// check if the session is valid
				if (($checkout_session->amount_total / ($this->getParam('use_decimals', 1) ? 100 : 1) == $this->get('total_to_pay')) && !empty($checkout_session['url']) )
				{
					return $checkout_session;	
				}
				else 
				{
					delete_option("stripe_order_{$this->get('oid')}");
				}
			} 
			catch (Exception $e)
			{
				delete_option("stripe_order_{$this->get('oid')}");
			}
		}
		
		// set the capture method
		$capture_method = $this->getParam('paytype');
		if ($capture_method != "automatic" && $capture_method != 'manual')
		{
			// default to automatic capture if the paytype is not set
			$capture_method = 'automatic';
		}

		// if the use_decimals is set, the amount should be multiplied by 100
		$amount_to_pay = round($this->get('total_to_pay'));
		if ($this->getParam('use_decimals', 1)) 
		{
			$amount_to_pay = round($this->get('total_to_pay'), 2) * 100;
		}

		// create the checkout session configuration for automatic payments
		if ($this->getParam('paytype') !== 'off_session')
		{
			$config = [
				'submit_type' => $this->getParam('transaction_type') ?? 'book',
				'success_url' => $this->get('notify_url'),
				'cancel_url'  => $this->get('return_url') . "&payment=canceled",
				//'customer'    => $customer->id,
				//'customer_email' => $this->get('custmail'),			
				'line_items'  => [[
					'price_data'   => [
						'unit_amount'  => $amount_to_pay,
						'currency'     => $this->getParam('currency'),
						'product_data' => [
							'name' => $this->get('transaction_name'),
						],
					],
					'quantity' => 1,	
				]],
				//'payment_intent' => $payment_intent->id,
				'mode'        => 'payment',
				'payment_intent_data' => [
					'capture_method'  => $capture_method,
					'description'     => __(sprintf("Reservation Number: %s", $this->get('oid')), "vikstripe"),
				],
			];

			if (!$this->getParam('auto_pay_meths'))
			{
				// set the payment methods to card only
				$config['payment_method_types'] = ['card'];
				
				// expand the authorization limits, more than 7 days
    			if ($this->getParam('request_extended_authorization')) 
    			{
    				$config['payment_method_options'] = [
    					'card' => [
    						'request_extended_authorization' => 'if_available',
    					],
    				];
    			}
			}
		}
		else
		{
			/**
			 * Adding support to off session payments.
			 * @link https://docs.stripe.com/payments/save-and-reuse
			 * 
			 * @since 2.1.2
			 */
			$config = [
				'mode'        => 'setup',
				'currency'    => $this->getParam('currency'),
				'success_url' => $this->get('notify_url'),
				'cancel_url'  => $this->get('return_url'),
			];
		}

		// set the metadata for the payment intent
		if ($this->getParam('transaction_metadata'))
		{
			$config['payment_intent_data']['metadata'] = [
				'transaction_metadata' => $this->getParam('transaction_metadata')
			];
			
			$config['metadata'] = [
				'transaction_metadata' => $this->getParam('transaction_metadata')
			];
		}

		if($this->getParam('paytype') == 'off_session' )
		{
			unset($config['payment_intent_data']);
		}

		try
		{
			// get the customer object
			$stripe_customer = $this->createCustomer($stripe, $customer);
			// set the customer ID in the session configuration
			$config['customer'] = $stripe_customer->id;
		}
		catch (Throwable $error)
		{
			if ($this->get('custmail'))
			{
				// if the customer email is set, use it as a fallback
				$config['customer_email'] = $this->get('custmail');
			}
		}

		try 
		{
			// create the checkout session
			$checkout_session = $stripe->checkout->sessions->create($config);
		}
		catch (Exception $e)
		{
			throw new Exception('Impossible to create a Stripe Session: ' . $e->getMessage() . "\n");
		}

		// store the session ID in the options table
		add_option("stripe_order_{$this->get('oid')}", $checkout_session->id);

		return $checkout_session;

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
		$customer = $this->get('customer');
		$layout = 'success';

		try 
		{
			// create a Stripe client
			$stripe = new \Stripe\StripeClient($this->getParam('secretkey') ?? '');
			// create a Stripe session
			$checkout_session = $this->createSession($stripe, $customer);
		} 
		catch (Exception $e) 
		{
			// show an error message in case of error
			$message = sprintf(__("Error while creating Stripe Session. <br/>Error code: %s <br/>Error message: %s <br/>"), $e->getCode(), $e->getMessage());
			$layout = "error";
		}
		
		// load the right layout
		include dirname(__FILE__) . DIRECTORY_SEPARATOR . "tmpl" . DIRECTORY_SEPARATOR . $layout . ".html.php" ;

		if (isset($_REQUEST['payment']) && $_REQUEST['payment'] == "canceled")
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(__("The payment has been canceled.", "vikstripe"));
			return false;
		}

		if (!$this->getParam('skipbtn'))
		{
			JFactory::getDocument()->addScriptDeclaration(
<<<JS
	jQuery(document).ready(function() {
		var buttonWrapper = document.getElementsByClassName('stripe__payment__form__wrapper');
		var stripeCheckout = buttonWrapper[0].children[0];
		window.location.href = stripeCheckout.getAttribute('href');
	});
JS
			);
		}
		
	}
	
	/**
	 * @override
	 * Method used to validate the payment transaction.
	 * It is usually an end-point that the providers use to POST the
	 * transaction data.
	 *
	 * @param 	JPaymentStatus 	&$status 	The status object. In case the payment was 
	 * 										successful, you should invoke: $status->verified().
	 *
	 * @return 	void
	 *
	 * @see 	JPaymentStatus
	 */
	protected function validateTransaction(JPaymentStatus &$status)
	{

		// retrieve the ID of session to be validated  
		$session_id = get_option("stripe_order_{$this->get('oid')}");
				
		try 
		{
			// create a Stripe client
			$stripe = new \Stripe\StripeClient($this->getParam('secretkey'));
			// retrieve the session from the Stripe API
			$session = $stripe->checkout->sessions->retrieve($session_id);
			
			$status->appendLog(sprintf("Session ID: %s \nSession Details: %s.", $session_id, print_r($session, true)));
			// check whether the session was paid successfully and if it was not in off-session
			if(in_array($session->mode, ['payment', 'setup']) && $session->status == 'complete')
			{
				$status->verified(true);
				// check if the session is paid
				if ($session->payment_status == 'paid') 
				{
					$status->paid($session->amount_total / ($this->getParam('use_decimals', 1) ? 100 : 1));

					// save the transaction details to have them available for refund
					if (($session->payment_intent ?? '')) 
					{
						$transaction = new stdClass;
						$transaction->driver = 'stripe.php';
						$transaction->payment_intent = $session->payment_intent;
						$transaction->amount = $status->amount;
						$status->setData('transaction', $transaction);
					}

					// retrieve the payment intent to have all the charge report
					$payment_intent = $stripe->paymentIntents->retrieve($session->payment_intent);
					// retrieve the charge to have the balance report
					$charge = $stripe->charges->retrieve($payment_intent->latest_charge);
					// retrieve the paid fees to Stripe
					$balance_transaction = $stripe->balanceTransactions->retrieve($charge->balance_transaction);
					// sanitize the fees amount and save them
					$status->setData('fees', $balance_transaction->fee / 100);
					// show the paid fees in the logs
					$status->appendLog(print_r($status->fees, true));
				}

				/**
				 * Register transaction data to support off session capture.
				 * 
				 * @since 2.1.2
				 */
				if ($this->getParam('paytype') === 'off_session')
				{
					$transaction = new stdClass;
					$transaction->driver = 'stripe.php';
					$transaction->amount = round($this->get('total_to_pay') * ($this->getParam('use_decimals', 1) ? 100 : 1));
					$transaction->amount_raw = (float) $this->get('total_to_pay');
					$transaction->payment_intent = $session->setup_intent;
					$transaction->future_usage = true;
					$status->setData('transaction', $transaction);
				}

				delete_option("stripe_order_{$this->get('oid')}");
			}
		}
		catch (Exception $e) 
		{
			// log the error in a specific file
			$path = VIKSTRIPE_DIR  . DIRECTORY_SEPARATOR . "stripe_session_error.txt";
			if ($fhandle = fopen($path, 'w+'))
			{
				fwrite($fhandle, sprintf("Session ID: %s \nSession Details: %s.", $session_id, print_r($e, true)));
				fclose($fhandle);
			}
			// and append it to the Payment Logs tab as well
			$status->appendLog(print_r($e, true));
		}

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
		$app = JFactory::getApplication();

		if ($res)
		{
			$url = $this->get('return_url');

			// display successful message
			$app->enqueueMessage(__('Thank you! Payment successfully received.', 'vikstripe'));
		}
		else
		{
			$url = $this->get('error_url');

			// display error message
			$app->enqueueMessage(__('It was not possible to verify the payment. Please, try again.', 'vikstripe'));
		}

		JFactory::getApplication()->redirect($url);
		exit;
	}
	/**
	 * @override
	 * Method used to create the cart.
	 * @since 1.0.6
	 *
	 * @param 	integer  $orderid  Id of the order.
	 *
	 * @return 	array 	 The associative array containing the items booked.
	 */
	protected function loadCartItems($orderid)
	{
		$amount_to_pay = round($this->get('total_to_pay'), 2) * ($this->getParam('use_decimals', 1) ? 100 : 1);
		
		// create default array if the cart is not supported by the plugin
		$item = [
			'price_data' => [
				'currency'    => $this->getParam('currency'),
				'unit_amount' => $amount_to_pay,
				'product_data' => [
					'name'   => $this->get('transaction_name'),
					'images' => [],
				],
			],
			'quantity' => 1,
		];

		// $item = array(
		// 	'name'     => $this->get('transaction_name'),
		// 	'images'   => [],
		// 	'amount'   => $amount_to_pay,
		// 	'currency' => $this->getParam('currency'),
		// 	'quantity' => 1,
		// );

		// add image logo if specified
		if ($img = $this->getParam('imageurl'))
		{
			// $item['images'][] = $img;
			$item['price_data']['product_data']['images'][] = $img;
		}

		// return an array
		return [$item];
	}

	/**
	 * @override
	 *
	 * This Stripe integration does support refunds.
	 *
	 * @return 	boolean
	 */
	public function isRefundSupported()
	{
		return true;
	}

	/**
	 * @override
	 *
	 * Executes the refund transaction by collecting the passed data.
	 *
	 * @return 	boolean
	 */
	protected function doRefund(JPaymentStatus &$status) 
	{
		$transaction = $this->get('transaction');
		$amount 	 = $this->get('total_to_refund');

		if (!$transaction || is_scalar($transaction)) {
			$status->appendLog('No previous transactions found');
			return;
		}

		if ($amount <= 0) {
			$status->appendLog('Invalid transaction amount');
			return;
		}

		if (is_object($transaction)) {
			$transaction = array($transaction);
		}

		// seek for a valid payment intent
		$payment_intent = null;
		foreach ($transaction as $tn) {
			if (!is_object($tn) || !isset($tn->payment_intent)) {
				continue;
			}
			if ($amount <= $tn->amount) {
				$payment_intent = $tn->payment_intent;
				// do not break the loop to always use the latest transaction ID
			}
		}

		if (!$payment_intent) {
			// do not proceed if no valid payment_intent has been found
			$status->appendLog('No valid payment intent found for the amount to be refunded' . "\n" . print_r($transaction, true));
			return;
		}

		\Stripe\Stripe::setApiKey($this->getParam('secretkey'));
		$refund = \Stripe\Refund::create([
			'payment_intent' => $payment_intent,
			'amount'		 => $amount * ($this->getParam('use_decimals', 1) ? 100 : 1),
		]);

		if ($refund['status'] == 'succeeded') {
			$status->verified();
			$status->paid($amount);
			return;
		}

		// append error log
		$status->appendLog('Refund failed.' . "\n" . print_r($refund, true));
	}

	/**
	 * @override
	 *
	 * This Stripe integration does support direct charges.
	 *
	 * @return 	boolean
	 */
	public function isDirectChargeSupported()
	{
		return true;
	}

	/**
	 * @override
	 *
	 * Executes the direct charge transaction by collecting the passed data.
	 * 
	 * Refer to the link https://docs.stripe.com/payments/payment-intents/three-d-secure-import to have more information.
	 *
	 * @return 	boolean
	 */
	protected function doDirectCharge(JPaymentStatus $status)
	{
		$card = $this->get('card');

		if (!$card || !is_array($card)) 
		{
			$status->appendLog('No credit card details received');
			return;
		}

		// get the charge amount
		$amount = $card['amount'] ?? null;

		if (!$amount || $amount <= 0) 
		{
			$status->appendLog('Invalid transaction amount');
			return;
		}

		// CC fields validation
		if (empty($card['currency']) || strlen($card['currency']) != 3) 
		{
			$status->appendLog('Missing or invalid transaction currency');
			return;
		}

		if (empty($card['card_number'])) {
			$status->appendLog('Missing credit card number');
			return;
		}

		if (empty($card['expiry']) || strpos($card['expiry'], '/') === false) 
		{
			$status->appendLog('Missing or invalid card expiration date');
			return;
		}

		// format expiration date
		$exp_parts 	  = explode('/', $card['expiry']);
		$expiry_month = str_pad($exp_parts[0], 2, '0', STR_PAD_LEFT);
		$expiry_year  = substr($exp_parts[1], -2, 2);

		// build payment card object
		$payment_card = [
			'number' 	=> str_replace(' ', '', trim($card['card_number'])),
			'exp_month' => $expiry_month,
			'exp_year'  => $expiry_year,
			'cvc' 		=> $card['cvv'] ?? null,
		];

		// payment options
		$payment_options = [];

		// sanitize "number_token" with dstransid
		if (isset($card['number_token']) && !isset($card['dstransid'])) 
		{
			$card['dstransid'] = $card['number_token'];
		}

		// check for 3DS import from an OTA reservation
		if (isset($card['xid']) || isset($card['dstransid'])) {
			// 3DS transaction ID is available, determine which one should be used
			$threeds_tn_id = $card['xid'] ?? $card['dstransid'];

			// 3DS version is mandatory
			$card['threedsversion'] = $card['threedsversion'] ?? '2.1.0';

			// compose transaction ID
			$threeds_v = substr($card['threedsversion'], 0, 1);
			if ($threeds_v == '1' && !empty($card['xid'])) 
			{
				// for 3D Secure 1, the XID
				$threeds_tn_id = $card['xid'];
			} 
			elseif ($threeds_v == '2' && !empty($card['dstransid'])) 
			{
				// for 3D Secure 2, the Directory Server Transaction ID (dsTransID).
				$threeds_tn_id = $card['dstransid'];
			}

			// build cryptogram (authentication value)
			$cryptogram = null;
			if (isset($card['cavv'])) 
			{
				$cryptogram = $card['cavv'];
			} 
			elseif (isset($card['aav'])) 
			{
				$cryptogram = $card['aav'];
			} 
			elseif (isset($card['aevv'])) 
			{
				$cryptogram = $card['aevv'];
			}

			// build 3DS data for the transaction
			$payment_options['card'] = [
				'three_d_secure' => [
					'version' 						=> $card['threedsversion'],
					'electronic_commerce_indicator' => $card['eci'] ?? null,
					'cryptogram' 					=> $cryptogram,
					'transaction_id' 				=> $threeds_tn_id,
				]
			];

			// make sure there are no empty values
			foreach ($payment_options['card']['three_d_secure'] as $threedsval) 
			{
				if (empty($threedsval)) 
				{
					// unset the whole three_d_secure data to process a MOTO transaction
					unset($payment_options['card']['three_d_secure']);
					break;
				}
			}
		}

		if (!strcasecmp(($card['exceptiontype'] ?? ''), 'MAIL_ORDER_TELEPHONE_ORDER')) 
		{
			// this is a MOTO transaction
			if (!isset($payment_options['card'])) 
			{
				$payment_options['card'] = [];
			}
			$payment_options['card']['moto'] = true;
		}

		// build full payment intent payload
		$charge_payload = [
			'amount' 			  => $amount * ($this->getParam('use_decimals', 1) ? 100 : 1),
			'currency' 			  => strtolower($card['currency']),
			'payment_method_types' => ['card'],
			'payment_method_data' => [
				'type' => 'card',
				'card' => $payment_card,
			],
			'payment_method_options' => $payment_options,
			'confirm' 			  	 => true,
			'metadata'               => (array) $this->get('tn_metadata', []),
		];

		// invoke StripeClient object
		$stripe  = new \Stripe\StripeClient($this->getParam('secretkey'));

		$transaction_metadata = $this->get('tn_metadata');
		$customer = [
			'email'      => $transaction_metadata['guest_email'] ?? $this->get('custmail'),
			'guest_name' => $transaction_metadata['guest_name'] ?? '',
			'phone'      => $transaction_metadata['guest_phone'] ?? '',
		];

		try
		{
			$stripe_customer = $this->createCustomer($stripe, $customer);
			$charge_payload['customer'] = $stripe_customer->id;
		}
		catch (Throwable $error)
		{
			if ($this->get('custmail'))
			{
				$charge_payload['customer_email'] = $this->get('custmail');
			}
		}

		// create the payment intent with the given information
		$payment = $stripe->paymentIntents->create($charge_payload);

		if ($payment['status'] == 'succeeded') {
			// set payment verified flag
			$status->verified();

			// register amount paid
			$status->paid($amount);

			// register transaction data
			$transaction = new stdClass;
			$transaction->driver = 'stripe';
			$transaction->payment_intent = $payment->id;
			$transaction->amount = $payment->amount;
			$status->setData('transaction', $transaction);

			return;
		}

		// append error log
		$last_error = "generic error";
		if (isset($payment['last_payment_error']))
		{
			$error_message = $payment['last_payment_error']['message'];
			$error_code    = $payment['last_payment_error']['decline_code'];
			$error_link    = $payment['last_payment_error']['doc_url']; 
			$error_type    = $payment['last_payment_error']['type'];
			$last_error = 'Error Code: ' . $error_code .  "\nError Message: " . $error_message . "\nError Type: " . $error_type . "\nVisit the link " . $error_link . ' for more details.';
		}

		$status->appendLog(sprintf("An error occurred: %s", $last_error));
	}

	/**
	 * @override
	 * 
	 * @since 2.2
	 */
	public function isOffSessionCaptureSupported()
	{
		return true;
	}

	/**
	 * Custom method used to complete an off-session capture.
	 * 
	 * @since 2.1.2
	 */
	public function doOffSessionCapture(JPaymentStatus $status)
	{
		/**
		 * Obtain the previous transaction details by supporting multiple formats.
		 * 
		 * @since 2.2
		 */
		$tx = (object) ($status->transaction ?: $this->get('transaction'));

		/**
		 * Obtain the capture amount by supporting multiple formats.
		 * 
		 * @since 2.2
		 */
		$capture_amount = (float) ($this->get('total_to_pay') ?: ($tx->amount ?? 0));
		if ($this->get('total_to_pay'))
		{
			$capture_amount = $capture_amount * ($this->getParam('use_decimals', 1) ? 100 : 1);
		}

		if (($tx->driver ?? null) !== 'stripe.php')
		{
			// off session capture registered by a different driver
			return;
		}

		if (empty($capture_amount) || empty($tx->payment_intent) || empty($tx->future_usage))
		{
			// missing required transaction data
			return;
		}

		// invoke StripeClient object
		$stripe  = new \Stripe\StripeClient($this->getParam('secretkey'));

		$errData = [];

		try
		{
			$currency = $this->getParam('currency');

			// recover the setup payment intent
			$intent = $stripe->setupIntents->retrieve($tx->payment_intent, []);

			$status->appendLog(print_r($intent, true));

			// charge the customer
			$payment = $stripe->paymentIntents->create([
				'amount' => $capture_amount,
				'currency' => $currency,
				'customer' => $intent->customer,
				'payment_method' => $intent->payment_method,
				'off_session' => true,
				'confirm' => true,
			]);

			$status->appendLog(print_r($payment, true));

			if ($payment->status !== 'succeeded')
			{
				throw new Exception('The system was not able to charge the customer.', 401);
			}

			// register amount paid
			$status->paid(round($capture_amount / ($this->getParam('use_decimals', 1) ? 100 : 1), 2));

			// set payment verified flag
			$status->verified(true);

			// register transaction data
			$transaction = new stdClass;
			$transaction->driver = 'stripe';
			$transaction->payment_intent = $payment->id;
			$transaction->amount = $payment->amount;
			$status->setData('transaction', $transaction);
		}
		catch (Exception $error)
		{
			$status->appendLog($error->getMessage());
		}
	}
}
