<?php
/**
 * @package     VikStripe
 * @subpackage  vikrestaurants
 * @author      Matteo Galletti - E4J s.r.l.
 * @copyright   Copyright (C) 2018 VikWP All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('stripe', VIKSTRIPE_DIR);

// Enhance the configuration array to include password and default values
add_action('vikrestaurants_payment_after_admin_params_stripe', function(&$payment, &$config)
{
	// make the secret key input as a password
	$config['secretkey']['type'] = 'password';

	// make default currency as EUR
	$config['currency']['default'] = 'EUR: Euro';

	/**
	 * Add OFF SESSION support for VikRestaurants only.
	 * 
	 * @since 2.2
	 */
	$config['paytype']['options']['off_session'] = __('Off Session', 'vikstripe');
}, 10, 2);

// Store the SESSION ID of the transaction for later use
add_action('vikrestaurants_payment_after_begin_transaction_stripe', function(&$payment, &$html)
{
	// save the transaction session ID within a transient (should not work on a multisite, try using `set_site_transient`)
	set_transient('vikstripe_' . $payment->get('oid') . '_' . $payment->get('sid'), $payment->get('session_id'), 1440 * MINUTE_IN_SECONDS);
}, 10, 2);

// Retrieve the total amount and the session id from the static transaction file.
add_action('vikrestaurants_payment_before_validate_transaction_stripe', function($payment)
{
	$transient = 'vikstripe_' . $payment->get('oid') . '_' . $payment->get('sid');
	$payment->set('is_transient', true);
	$payment->set('transient_name', $transient);

	// get session ID from transient (should not work on a multisite, try using `get_site_transient`)
	$session_id = get_transient($transient);

	// make sure the session ID was previously set
	if ($session_id)
	{
		// set session ID within the payment instance
		$payment->set('session_id', $session_id);
	}
});

// Retrieve the total amount and the session id from the static transaction file.
add_action('vikrestaurants_payment_after_validate_transaction_stripe', function($payment, &$status, &$response)
{
	$tx = $status->transaction;

	if (!$tx)
	{
		return;
	}

	// manually save transaction details within the reservation/order record
	JModelVRE::getInstance($payment->get('tid') == 0 ? 'reservation' : 'tkreservation')->save([
		'id' => (int) $payment->get('oid'),
		'cc_details' => json_encode($tx),
	]);
}, 10, 3);

/**
 * Trigger event to let the plugins be notified every time the status of a restaurant reservation changes.
 *
 * @param   object  $data  The order status details.
 *
 * @return  void
 * 
 * @since   1.2
 */
add_action('vikrestaurants_status_change_restaurant_reservation', 'vikrestaurants_stripe_handle_reservation_status_change');
add_action('vikrestaurants_status_change_takeaway_order', 'vikrestaurants_stripe_handle_order_status_change');

function vikrestaurants_stripe_handle_reservation_status_change($data)
{
	vikrestaurants_stripe_handle_status_change($data, 'restaurant');
}

function vikrestaurants_stripe_handle_order_status_change($data)
{
	vikrestaurants_stripe_handle_status_change($data, 'takeaway');
}

function vikrestaurants_stripe_handle_status_change($data, $group)
{
	if (!is_admin())
	{
		// ignore in case the order has been paid from the front-end
		return;
	}

	if ($data['status'] !== 'N')
	{
		// no show not selected
		return;
	}

	$order = [
		'oid'  => $data['id'],
		'type' => $group,
	];

	$model = JModelVRE::getInstance($group === 'restaurant' ? 'reservation' : 'tkreservation');

	// recover payment assigned to the updated order
	$orderDetails = $model->getItem($data['id']);

	if (!$orderDetails)
	{
		// order not found
		return;
	}

	// reload payment details to access the parameters
	$payment = JModelVRE::getInstance('payment')->getItem($orderDetails->id_payment);

	if (!$payment)
	{
		// payment not found
		return;
	}

	$status = new JPaymentStatus();
	$status->setData('transaction', $orderDetails->cc_details ? json_decode($orderDetails->cc_details) : null);

	$stripe = new VikRestaurantsStripePayment('vikrestaurants', $order, $payment->params);
	$stripe->doOffSessionCapture($status);

	$saveData = [
		'id' => $data['id'],
	];

	// get current date and time
	$config = VREFactory::getConfig();
	$timeformat = preg_replace("/:i/", ':i:s', $config->get('timeformat'));
	$now = JHtml::_('date', 'now', $config->get('dateformat') . ' ' . $timeformat, JFactory::getApplication()->get('offset', 'UTC'));

	// build log string
	$log  = str_repeat('-', strlen($now) + 4) . "\n";
	$log .= "| $now |\n";
	$log .= str_repeat('-', strlen($now) + 4) . "\n\n";

	if ($status->isVerified())
	{
		$saveData['tot_paid'] = $status->amount;
		$log .= 'Customer charged successfully.';
	}
	else
	{
		$log = $status->log;
	}

	if (!empty($orderDetails->payment_log))
	{
		// always prepend new logs at the beginning
		$log = $log . "\n\n" . $orderDetails->payment_log;
	}

	$saveData['payment_log'] = $log;

	$model->save($saveData);
}

/**
 * This class is used to collect payments in VikRestaurants plugin
 * by using the Stripe gateway.
 *
 * @since 1.0
 */
class VikRestaurantsStripePayment extends AbstractStripePayment
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

		if (!$this->get('custmail'))
		{
			$details = $this->get('details', array());
			$this->set('custmail', isset($details['purchaser_mail']) ? $details['purchaser_mail'] : '');

			$fullname = isset($details['purchaser_nominative']) ? $details['purchaser_nominative'] : '';
			$arr = array();
			$arr = explode(" ", $fullname);
			$this->set('first_name', isset($arr[0]) ? $arr[0] : '');
			$this->set('last_name', isset($arr[1]) ? $arr[1] : '');

		}

		if ($billing = $this->get('billing')) 
		{
			$this->set('customer', [
				'first_name' => $this->get('first_name', ''),
				'last_name'  => $this->get('last_name', ''),
				'email'      => $this->get('custmail', ''),
				'country'    => $billing->country_code,
				'state'      => $billing->billing_state,
				'city'       => $billing->billing_city,
				'zip'        => $billing->billing_zip,
				'address'    => $billing->billing_address,
				'phone'      => $details['purchaser_phone'],
			]);
		}

		$this->set('btnclass', 'vre-btn primary');
	}
}
