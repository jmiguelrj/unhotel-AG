<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2019 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * This class is used to perform conversions between currencies.
 * It relies on the native currency conversion features of VBO.
 * 
 * @since 	1.8.3
 */
class VCMCurrencyConverter
{
	/**
	 * The singleton instance of the class.
	 *
	 * @var 	VCMCurrencyConverter
	 */
	protected static $instance = null;

	/**
	 * The default currency of VBO.
	 * 
	 * @var 	string
	 */
	protected $from_currency = null;

	/**
	 * The currency to convert to (3-char).
	 * 
	 * @var 	string
	 */
	protected $to_currency = null;

	/**
	 * The calculated exchange rate.
	 * 
	 * @var 	float
	 */
	protected $exchange_rate = null;

	/**
	 * The list of supported currencies.
	 * 
	 * @var 	array
	 */
	protected $all_currencies = array(
		'AED' => 'AED - United Arab Emirates Dirham',
		'AFN' => 'AFN - Afghan Afghani',
		'ALL' => 'ALL - Albanian Lek',
		'AMD' => 'AMD - Armenian Dram',
		'ANG' => 'ANG - Netherlands Antillean Gulden',
		'AOA' => 'AOA - Angolan Kwanza',
		'ARS' => 'ARS - Argentine Peso',
		'AUD' => 'AUD - Australian Dollar',
		'AWG' => 'AWG - Aruban Florin',
		'AZN' => 'AZN - Azerbaijani Manat',
		'BAM' => 'BAM - Bosnia and Herzegovina Mark',
		'BBD' => 'BBD - Barbadian Dollar',
		'BDT' => 'BDT - Bangladeshi Taka',
		'BGN' => 'BGN - Bulgarian Lev',
		'BIF' => 'BIF - Burundian Franc',
		'BMD' => 'BMD - Bermudian Dollar',
		'BND' => 'BND - Brunei Dollar',
		'BOB' => 'BOB - Bolivian Boliviano',
		'BRL' => 'BRL - Brazilian Real',
		'BSD' => 'BSD - Bahamian Dollar',
		'BWP' => 'BWP - Botswana Pula',
		'BZD' => 'BZD - Belize Dollar',
		'CAD' => 'CAD - Canadian Dollar',
		'CDF' => 'CDF - Congolese Franc',
		'CHF' => 'CHF - Swiss Franc',
		'CLP' => 'CLP - Chilean Peso',
		'CNY' => 'CNY - Chinese Renminbi Yuan',
		'COP' => 'COP - Colombian Peso',
		'CRC' => 'CRC - Costa Rican Colón',
		'CVE' => 'CVE - Cape Verdean Escudo',
		'CZK' => 'CZK - Czech Koruna',
		'DJF' => 'DJF - Djiboutian Franc',
		'DKK' => 'DKK - Danish Krone',
		'DOP' => 'DOP - Dominican Peso',
		'DZD' => 'DZD - Algerian Dinar',
		'EEK' => 'EEK - Estonian Kroon',
		'EGP' => 'EGP - Egyptian Pound',
		'ETB' => 'ETB - Ethiopian Birr',
		'EUR' => 'EUR - Euro',
		'FJD' => 'FJD - Fijian Dollar',
		'FKP' => 'FKP - Falkland Islands Pound',
		'GBP' => 'GBP - British Pound',
		'GEL' => 'GEL - Georgian Lari',
		'GIP' => 'GIP - Gibraltar Pound',
		'GMD' => 'GMD - Gambian Dalasi',
		'GNF' => 'GNF - Guinean Franc',
		'GTQ' => 'GTQ - Guatemalan Quetzal',
		'GYD' => 'GYD - Guyanese Dollar',
		'HKD' => 'HKD - Hong Kong Dollar',
		'HNL' => 'HNL - Honduran Lempira',
		'HRK' => 'HRK - Croatian Kuna',
		'HTG' => 'HTG - Haitian Gourde',
		'HUF' => 'HUF - Hungarian Forint',
		'IDR' => 'IDR - Indonesian Rupiah',
		'ILS' => 'ILS - Israeli New Sheqel',
		'INR' => 'INR - Indian Rupee',
		'ISK' => 'ISK - Icelandic Króna',
		'JMD' => 'JMD - Jamaican Dollar',
		'JPY' => 'JPY - Japanese Yen',
		'KES' => 'KES - Kenyan Shilling',
		'KGS' => 'KGS - Kyrgyzstani Som',
		'KHR' => 'KHR - Cambodian Riel',
		'KMF' => 'KMF - Comorian Franc',
		'KRW' => 'KRW - South Korean Won',
		'KYD' => 'KYD - Cayman Islands Dollar',
		'KZT' => 'KZT - Kazakhstani Tenge',
		'LAK' => 'LAK - Lao Kip',
		'LBP' => 'LBP - Lebanese Pound',
		'LKR' => 'LKR - Sri Lankan Rupee',
		'LRD' => 'LRD - Liberian Dollar',
		'LSL' => 'LSL - Lesotho Loti',
		'LTL' => 'LTL - Lithuanian Litas',
		'LVL' => 'LVL - Latvian Lats',
		'MAD' => 'MAD - Moroccan Dirham',
		'MDL' => 'MDL - Moldovan Leu',
		'MGA' => 'MGA - Malagasy Ariary',
		'MKD' => 'MKD - Macedonian Denar',
		'MNT' => 'MNT - Mongolian Tögrög',
		'MOP' => 'MOP - Macanese Pataca',
		'MRU' => 'MRU - Mauritanian Ouguiya',
		'MUR' => 'MUR - Mauritian Rupee',
		'MVR' => 'MVR - Maldivian Rufiyaa',
		'MWK' => 'MWK - Malawian Kwacha',
		'MXN' => 'MXN - Mexican Peso',
		'MYR' => 'MYR - Malaysian Ringgit',
		'MZN' => 'MZN - Mozambican Metical',
		'NAD' => 'NAD - Namibian Dollar',
		'NGN' => 'NGN - Nigerian Naira',
		'NIO' => 'NIO - Nicaraguan Córdoba',
		'NOK' => 'NOK - Norwegian Krone',
		'NPR' => 'NPR - Nepalese Rupee',
		'NZD' => 'NZD - New Zealand Dollar',
		'PAB' => 'PAB - Panamanian Balboa',
		'PEN' => 'PEN - Peruvian Nuevo Sol',
		'PGK' => 'PGK - Papua New Guinean Kina',
		'PHP' => 'PHP - Philippine Peso',
		'PKR' => 'PKR - Pakistani Rupee',
		'PLN' => 'PLN - Polish Złoty',
		'PYG' => 'PYG - Paraguayan Guaraní',
		'QAR' => 'QAR - Qatari Riyal',
		'RON' => 'RON - Romanian Leu',
		'RSD' => 'RSD - Serbian Dinar',
		'RUB' => 'RUB - Russian Ruble',
		'RWF' => 'RWF - Rwandan Franc',
		'SAR' => 'SAR - Saudi Riyal',
		'SBD' => 'SBD - Solomon Islands Dollar',
		'SCR' => 'SCR - Seychellois Rupee',
		'SEK' => 'SEK - Swedish Krona',
		'SGD' => 'SGD - Singapore Dollar',
		'SHP' => 'SHP - Saint Helenian Pound',
		'SLL' => 'SLL - Sierra Leonean Leone',
		'SOS' => 'SOS - Somali Shilling',
		'SRD' => 'SRD - Surinamese Dollar',
		'STD' => 'STD - São Tomé and Príncipe Dobra',
		'SVC' => 'SVC - Salvadoran Colón',
		'SZL' => 'SZL - Swazi Lilangeni',
		'THB' => 'THB - Thai Baht',
		'TJS' => 'TJS - Tajikistani Somoni',
		'TOP' => 'TOP - Tongan Paʻanga',
		'TRY' => 'TRY - Turkish Lira',
		'TTD' => 'TTD - Trinidad and Tobago Dollar',
		'TWD' => 'TWD - New Taiwan Dollar',
		'TZS' => 'TZS - Tanzanian Shilling',
		'UAH' => 'UAH - Ukrainian Hryvnia',
		'UGX' => 'UGX - Ugandan Shilling',
		'USD' => 'USD - United States Dollar',
		'UYU' => 'UYU - Uruguayan Peso',
		'UZS' => 'UZS - Uzbekistani Som',
		'VEF' => 'VEF - Venezuelan Bolívar',
		'VND' => 'VND - Vietnamese Đồng',
		'VUV' => 'VUV - Vanuatu Vatu',
		'WST' => 'WST - Samoan Tala',
		'XAF' => 'XAF - Central African Cfa Franc',
		'XCD' => 'XCD - East Caribbean Dollar',
		'XOF' => 'XOF - West African Cfa Franc',
		'XPF' => 'XPF - Cfp Franc',
		'YER' => 'YER - Yemeni Rial',
		'ZAR' => 'ZAR - South African Rand',
		'ZMW' => 'ZMW - Zambian Kwacha',
		'TND' => 'TND - Tunisian dinar',
	);

	/**
	 * The error occurred
	 * 
	 * @var 	string
	 */
	protected $error = '';

	/**
	 * Class constructor is protected.
	 * Loads the dependencies and inits some class vars.
	 *
	 * @see 	getInstance()
	 */
	protected function __construct()
	{
		if (!class_exists('VikChannelManager')) {
			// require the main VCM library as the class is probably being invoked by VBO
			require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikchannelmanager.php';
		}

		if (!class_exists('VikChannelManagerConfig')) {
			// require the config library as the class is probably being invoked by VBO and errors may occur
			require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'vcm_config.php';
		}

		if (!class_exists('VikBooking')) {
			// require the main VBO library as the class is probably being invoked by VCM
			require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';
		}

		if (!class_exists('VboCurrencyConverter')) {
			// require the VBO currency converter library
			require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'currencyconverter.php';
		}

		// make sure to set the proper "from-currency"
		$this->setFromCurrency($this->getDefaultCurrency());
	}

	/**
	 * Returns the global class object, either
	 * a new instance or the existing instance
	 * if the class was already instantiated.
	 *
	 * @return 	self 	A new instance of the class.
	 */
	public static function getInstance()
	{
		if (is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Gets the default from currency.
	 * 
	 * @return 	string 	the default from currency or null.
	 */
	public function getDefaultCurrency()
	{
		$def_currency = null;

		try {
			// get VBO currency
			$def_currency = VikBooking::getCurrencyName();
		} catch (Exception $e) {
			// do nothing
		}

		if (empty($def_currency)) {
			// revert to VCM currency
			$def_currency = VikChannelManager::getConfigurationRecord('currencyname');
		}

		return !empty($def_currency) ? $def_currency : null;
	}

	/**
	 * Sets the internal from currency.
	 * 
	 * @param 	string 	$currency 	the currency to convert from.
	 * 
	 * @return 	self
	 */
	public function setFromCurrency($currency)
	{
		if (!empty($currency)) {
			$this->from_currency = $currency;
		}

		return $this;
	}

	/**
	 * Gets the internal from currency.
	 * 
	 * @return 	string 	the currency to convert from.
	 */
	public function getFromCurrency()
	{
		return $this->from_currency;
	}

	/**
	 * Sets the internal to currency.
	 * 
	 * @param 	string 	$currency 	the currency to convert to.
	 * 
	 * @return 	self
	 */
	public function setToCurrency($currency)
	{
		if (!empty($currency)) {
			$this->to_currency = $currency;
		}

		return $this;
	}

	/**
	 * Gets the internal to currency.
	 * 
	 * @return 	string 	the currency to convert to.
	 */
	public function getToCurrency()
	{
		return $this->to_currency;
	}

	/**
	 * Applies the conversion between currencies of the given rates.
	 * 
	 * @param 	mixed 	$rates	float or array of float numbers.
	 * 
	 * @return 	mixed 			false on failure, array of exchanged rates otherwise.
	 */
	public function exchangeRates($rates)
	{
		// make sure rates is an array of numbers
		if (is_scalar($rates)) {
			$rates = array($rates);
		}

		if (!is_array($rates) || !count($rates)) {
			$this->setError('No given rates to exchange');
			return false;
		}

		// make sure from and to currencies have been set
		if (empty($this->from_currency) || empty($this->to_currency)) {
			$this->setError('From and to currencies cannot be empty');
			return false;
		}

		// get numbering format data
		$format = VikBooking::getNumberFormatData();

		// get the instance of the converter object
		$converter = new VboCurrencyConverter($this->from_currency, $this->to_currency, $rates, explode(':', $format));

		// exchange the given rates
		$exchanged = $converter->convert();

		if (!is_array($exchanged) || !count($exchanged)) {
			$converter_error = $converter->getError();
			$converter_error = empty($converter_error) ? 'Error while converting rates' : $converter_error;
			$this->setError($converter_error);
			return false;
		}

		return $exchanged;
	}

	/**
	 * Gets and sets the conversion rate between currencies.
	 * 
	 * @return 	mixed 	false on failure, float otherwise.
	 */
	public function calcExchangeRate()
	{
		// reset exchange rate if previously set
		$this->exchange_rate = null;

		// make sure from and to currencies have been set
		if (empty($this->from_currency) || empty($this->to_currency)) {
			$this->setError('From and to currencies cannot be empty');
			return false;
		}

		// get numbering format data
		$format = VikBooking::getNumberFormatData();

		// get the instance of the converter object
		$converter = new VboCurrencyConverter($this->from_currency, $this->to_currency, array(1), explode(':', $format));

		if (is_callable(array($converter, 'getConversionRate'))) {
			/**
			 * @requires 	VBO >= 1.15.0 (J) - 1.5.0 (WP)
			 */
			$this->exchange_rate = $converter->getConversionRate();
			if ($this->exchange_rate === false && $converter->getError()) {
				$this->setError($converter->getError());
			}
		} else {
			// use the ReflectionMethod class for BC compatibility
			try {
				$reflm = new ReflectionMethod('VboCurrencyConverter', 'getConversionRate');
				// make sure to make the method accessible as it used to be private
				$reflm->setAccessible(true);
				$this->exchange_rate = $reflm->invoke($converter);
			} catch (Exception $e) {
				$this->setError('ReflectionMethod error: ' . $e->getMessage());
				return false;
			}
		}

		return $this->exchange_rate;
	}

	/**
	 * Calculates the percent alteration value for a currency to be
	 * exchanged to another currency. Requires the exchange rate to
	 * be already calculated between these two currencies.
	 * Returns an array where the first element is the float number
	 * for the percent alteration, and the second element is the string
	 * indicating the operator, whether to increase or discount rates.
	 * The third array element is the formatted percent alteration.
	 * 
	 * @param 	int 	$precision 	the amount of digits for the alteration.
	 * 
	 * @return 	mixed 				false on failure, array otherwise.
	 */
	public function calcPercentAlteration($precision = null)
	{
		if ($this->exchange_rate === null) {
			// call the apposite method
			$this->calcExchangeRate();
		}
		if (!$this->exchange_rate) {
			// cannot proceed
			return false;
		}

		// default percent alteration is 0, meaning nothing should be done
		$percent_alteration   = 0;
		$operator_alteration  = '+';
		$formatted_alteration = '';

		if ($this->exchange_rate === 1) {
			// no percent alteration for the same currency
			return array($percent_alteration, $operator_alteration, $formatted_alteration);
		}

		if ($this->exchange_rate > 1) {
			/**
			 * The "to currency" is weaker than the "from currency".
			 * I.E. 90 EUR = 101,007 USD (today exchange rate = 1.1223).
			 * 
			 * Proportion to calc X, to get the percent increase, is:
			 * 90 : 100 = 101,007 : x
			 * x = (101,007 * 100 / 90) - 100
			 * x = 12,23
			 * 90 * (100 + 12,23) / 100 = 101,007
			 * 
			 * We just use a simple math operation to get the necessary
			 * percent increase value from the given exchange rate:
			 * (1,1223 * 100) - 100 = 12,23
			 */
			$percent_alteration = (($this->exchange_rate * 100) - 100);
			// rates must be increased to obtain the exchanged rate
			$operator_alteration = '+';
		} else {
			/**
			 * The "to currency" is stronger than the "from currency".
			 * I.E. 90 EUR = 75,87 GBP (today exchange rate = 0.84295).
			 * 
			 * Proportion to calc X, to get the percent discount, is:
			 * 90 : 100 = 75,87 : x
			 * x = 100 - (75,87 * 100 / 90)
			 * x = 15,7
			 * 90 * (100 - 15,7) / 100 = 75,87
			 * 
			 * We apply the following math operation to get the necessary
			 * percent discount value from the given exchange rate:
			 * 100 - (0,84295 * 100)
			 */
			$percent_alteration = (100 - ($this->exchange_rate * 100));
			// rates must be discounted to obtain the exchanged rate
			$operator_alteration = '-';
		}

		// apply precision, if requested
		if ($precision !== null && $precision >= 0) {
			// we need to apply a rounding for the best precision of the alteration
			$percent_alteration = round($percent_alteration, (int)$precision);
		}

		// build the formatted alteration string
		$format_data = explode(':', VikBooking::getNumberFormatData());
		// just apply the decimal separator string by ignoring the number of decimal digits for best precision
		$display_amount = str_replace('.', $format_data[1], (string)$percent_alteration);
		// build final string to be displayed
		$formatted_alteration = $operator_alteration . $display_amount . ' %';

		return array($percent_alteration, $operator_alteration, $formatted_alteration);
	}

	/**
	 * Returns all the currencies available for conversion.
	 * 
	 * @return 	array 	the associative list of currencies.
	 */
	public function getAllCurrencies()
	{
		return $this->all_currencies;
	}

	/**
	 * Sets an error during the execution.
	 * 
	 * @param 	string 	$mess 	the error string.
	 * 
	 * @return 	void
	 */
	protected function setError($mess)
	{
		$this->error .= (string)$mess . "\n";
	}

	/**
	 * Returns whether errors occurred.
	 * 
	 * @return 	boolean
	 */
	public function hasError()
	{
		return !empty($this->error);
	}

	/**
	 * Returns the error message set.
	 * 
	 * @return 	string 	the error message string.
	 */
	public function getError()
	{
		return rtrim($this->error, "\n");
	}
}
