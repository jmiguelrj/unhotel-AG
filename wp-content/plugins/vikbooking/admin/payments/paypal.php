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

JLoader::import('adapter.payment.payment');

/**
 * This class is used to collect payments in VikBooking plugin
 * by using the PayPal gateway.
 *
 * @since 1.0.5
 */
class VikBookingPayPalPayment extends JPayment
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

		$this->setParam('sandbox',  $this->getParam('sandbox') 	== 'ON' ? 1 : 0);
		$this->setParam('tls12', 	$this->getParam('tls12') 	== 'ON' ? 1 : 0);
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
				'type' 	=> 'custom',
				'label' => '',
				'html' 	=> '<img src="https://www.paypalobjects.com/webstatic/i/ex_ce2/logo/logo_paypal_106x29.png"/>',
			),
			'account' => array(
				'type' 	=> 'text',
				'label' => 'PayPal Account:',
			),
			'sandbox' => array(
				'type' 		=> 'select',
				'label' 	=> 'Test Mode://if ON, the PayPal Sandbox will be used',
				'options' 	=> array('OFF', 'ON'),
			),
			'tls12' => array(
				'type' 		=> 'select',
				'label' 	=> 'Force TLS 1.2://if ON, the connection to PayPal will be established only through the TLS 1.2 protocol',
				'options' 	=> array('ON', 'OFF'),
			),
			'button' => array(
				'type' => 'select',
				'label' => '&quot;Pay Now&quot; button://choose the type for the &quot;Pay Now&quot; button',
				'options' => array('PayPal Default Button', 'HTML Button'),
			),
		);
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
		$depositmess = "";

		//coupon
		if ($this->get('total_tax') < 0)
		{
			$this->set('total_tax', 0);
		}

		if (($this->get('total_net_price') + $this->get('total_tax')) > $this->get('total_to_pay'))
		{
			$this->set('total_net_price', $this->get('total_to_pay'));
			$this->set('total_tax', 0);
		}
		//

		if ($this->getParam('sandbox'))
		{
			$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
		}
		else
		{
			$paypal_url = "https://www.paypal.com/cgi-bin/webscr";
		}

		/**
		 * Safely append the PayPal status within the query string.
		 *
		 * @since 1.3.6
		 */
		$uri = JUri::getInstance($this->get('return_url'));
		$uri->setVar('paypal_status', 1);
		//

		$account_param = $this->getParam('account');

		/**
		 * The values item_name and item_number should not exceed the limit of 127 chars
		 * or a BAD_INPUT_ERROR will be raised before even starting the transaction.
		 * 
		 * @since 	1.15.4 (J) - 1.5.9 (WP)
		 */
		$tn_item_name 	= $this->get('transaction_name');
		$tn_item_number = $this->get('rooms_name');
		$multib_substr  = function_exists('mb_substr');
		if (strlen($tn_item_name) >= 127) {
			$tn_item_name = $multib_substr ? mb_substr($tn_item_name, 0, 127, 'UTF-8') : substr($tn_item_name, 0, 127);
		}
		if (strlen($tn_item_number) >= 127) {
			$tn_item_number = $multib_substr ? mb_substr($tn_item_number, 0, 127, 'UTF-8') : substr($tn_item_number, 0, 127);
		}

		$paypalaccount = !empty($account_param) ? $account_param : $this->get('account_name');
		$form = "<form action=\"".$paypal_url."\" method=\"post\">\n";
		$form .= "<input type=\"hidden\" name=\"business\" value=\"".$paypalaccount."\"/>\n";
		$form .= "<input type=\"hidden\" name=\"cmd\" value=\"_xclick\"/>\n";
		$form .= "<input type=\"hidden\" name=\"amount\" value=\"".number_format($this->get('total_net_price'), 2, '.', '')."\"/>\n";
		$form .= "<input type=\"hidden\" name=\"item_name\" value=\"" . htmlspecialchars($tn_item_name) . "\"/>\n";
		$form .= "<input type=\"hidden\" name=\"item_number\" value=\"" . htmlspecialchars($tn_item_number) . "\"/>\n";
		$form .= "<input type=\"hidden\" name=\"quantity\" value=\"1\"/>\n";
		$form .= "<input type=\"hidden\" name=\"tax\" value=\"".number_format($this->get('total_tax'), 2, '.', '')."\"/>\n";
		$form .= "<input type=\"hidden\" name=\"shipping\" value=\"0.00\"/>\n";
		$form .= "<input type=\"hidden\" name=\"currency_code\" value=\"".$this->get('transaction_currency')."\"/>\n";
		$form .= "<input type=\"hidden\" name=\"no_shipping\" value=\"1\"/>\n";
		$form .= "<input type=\"hidden\" name=\"rm\" value=\"2\"/>\n";
		$form .= "<input type=\"hidden\" name=\"notify_url\" value=\"".$this->get('notify_url')."\"/>\n";
		$form .= "<input type=\"hidden\" name=\"return\" value=\"" . (string)$uri . "\"/>\n";
		if (stripos($this->getParam('button', ''), 'html') !== false) {
			$form .= "<input type=\"submit\" name=\"submit\" class=\"vik-paynow-btn vik-paynow-btn-paypal\" value=\"" . JText::_('VBO_PAYNOW') . "\" />\n";
		} else {
			$form .= "<input type=\"image\" src=\"https://www.paypal.com/en_US/i/btn/btn_paynow_SM.gif\" name=\"submit\" alt=\"PayPal - The safer, easier way to pay online!\">\n";
		}
		$form .= "</form>\n";

		/**
		 * System message for transaction completed and auto-reload after 5 seconds.
		 * 
		 * @since 	1.3.6
		 */
		$paypal_status = JFactory::getApplication()->input->getInt('paypal_status');
		if (!empty($paypal_status))
		{
			/**
			 * @wponly 	this language definition for PayPal only, about the transaction completed, needs to be translated
			 * 			through an internal plugin, while on Joomla it is necessary to override the INI translation.
			 */
			$raw_message = __('Transaction completed. The page will be refreshed automatically in a few seconds.', 'vikbooking');
			//
			JFactory::getApplication()->enqueueMessage($raw_message);
			$form .= '<script>setTimeout(function() { document.location.href="' . $this->get('return_url') . '"; }, 5000);</script>' . "\n";
		}
		//

		if ($this->get('leave_deposit'))
		{
			$depositmess = "<p class=\"vbo-leave-deposit\"><span>".JText::_('VBLEAVEDEPOSIT')."</span>".$this->get('currency_symb')." ".number_format($this->get('total_to_pay'), 2)."</p><br/>";
		}

		$info = $this->get('payment_info');

		//output form
		echo $depositmess;

		/**
		 * @wponly 	we need to let WordPress parse the paragraphs in the message.
		 */
		if (VBOPlatformDetection::isWordPress()) {
			echo wpautop($info['note']);
		} else {
			echo $info['note'];
		}

		echo $form;
		
		return true;
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
	 * @return 	boolean
	 *
	 * @see 	JPaymentStatus
	 */
	protected function validateTransaction(JPaymentStatus &$status)
	{	
		//cURL Method HTTP1.1 October 2013
		$raw_post_data = file_get_contents('php://input');
		$raw_post_array = explode('&', $raw_post_data);
		$myPost = array();

		foreach ($raw_post_array as $keyval)
		{
			$keyval = explode ('=', $keyval);
			if (count($keyval) == 2)
			{
				$myPost[$keyval[0]] = urldecode($keyval[1]);
			}
		}

		//security measure for not letting the end-user overwrite the business name
		if (isset($myPost['business']))
		{
			$myPost['business'] = $this->getParam('account');
		}
		//

		// we cannot use array_merge as we need associative keys
		$post_vals = array('cmd' => '_notify-validate') + $myPost;
		$status->appendLog(print_r($post_vals, true));
		$req = http_build_query($post_vals);
		
		if (!function_exists('curl_init'))
		{
			$status->prependLog("FATAL ERROR: cURL is not installed on the server.");
			return false;
		}
		
		if ($this->getParam('sandbox'))
		{
			$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
		}
		else
		{
			$paypal_url = "https://www.paypal.com/cgi-bin/webscr";
		}
		
		$ch = curl_init($paypal_url);
		if (!$ch)
		{
			$status->prependLog("Curl error: ".curl_error($ch));
			return false;
		}
		
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		if (defined('CURLOPT_SSLVERSION') && ($this->getParam('sandbox') || $this->getParam('tls12') == 1))
		{
			//TLS 1.2
			curl_setopt($ch, CURLOPT_SSLVERSION, 6);
		}
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
		curl_setopt($ch, CURLOPT_USERAGENT, JUri::root());
		
		// CONFIG: Please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and copy it in the same folder as this php file
		// This is mandatory for some environments.
		//$cert = dirname(__FILE__) . "/cacert.pem";
		//curl_setopt($ch, CURLOPT_CAINFO, $cert);
		
		$res = curl_exec($ch);
		if (curl_errno($ch) != 0)
		{
			$status->appendLog(date('[Y-m-d H:i e]') . " Can't connect to PayPal to validate IPN message: " . curl_error($ch));
			curl_close($ch);
			return false;
		}
		else
		{
			$status->appendLog(date('[Y-m-d H:i e]') . " HTTP request of validation request: " . curl_getinfo($ch, CURLINFO_HEADER_OUT) . " for IPN payload.");
			$status->appendLog(date('[Y-m-d H:i e]') . " HTTP response of validation request: $res");
			curl_close($ch);
		}
		
		if (strcmp(trim($res), "VERIFIED") == 0 && strcmp(trim($res), "refund") != 0)
		{
			/**
			 * An IPN message could also be sent in case of a manual refund of a transaction.
			 * 
			 * @since 	August 31st 2020
			 */
			if ((isset($myPost['reason_code']) && stripos($myPost['reason_code'], 'refund') !== false) || (isset($myPost['mc_gross']) && (float)$myPost['mc_gross'] < 0)) {
				// this looks like a refund of a previous transaction
				$status->appendLog("The transaction was probably refunded.\n");
				return false;
			}
			//

			$status->paid($_POST['mc_gross']);
			$status->verified();
		}
		else if (strcmp($res, "INVALID") == 0)
		{
			$status->appendLog(date('[Y-m-d H:i e]') . " Invalid IPN:\n$res");
			return false;
		}
		
		//END cURL Method HTTP1.1 October 2013
		
		return true;
	}	
}
