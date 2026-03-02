<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Require VCM source autoloader (include to avoid a fatal E_COMPILE_ERROR error for those who update).
 * 
 * @since 	1.8.4
 */
@include_once VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoload.php';

class VikChannelManager 
{
	public static function loadConfiguration()
	{
		static $config = null;

		if (!$config) {
			$dbo = JFactory::getDbo();

			$q = "SELECT * FROM `#__vikchannelmanager_config`;";
			$dbo->setQuery($q);
			$rows = $dbo->loadAssocList();

			$config = [];
			foreach ($rows as $r) {
				$config[$r['param']] = $r['setting'];
			}
		}

		return $config;
	}
	
	public static function getAdminMail($skip_session=false) 
	{
		return self::getFieldFromConfig('emailadmin', 'vcmGetAdminMail', $skip_session);
	}
	
	public static function getDateFormat($skip_session=false) 
	{
		return self::getFieldFromConfig('dateformat', 'vcmGetDateFormat', $skip_session);
	}
	
	public static function getClearDateFormat($skip_session=false) 
	{
		return str_replace('%', '', self::getFieldFromConfig('dateformat', 'vcmGetDateFormat', $skip_session));
	}
	
	public static function getCurrencySymb($skip_session=false) 
	{
		return self::getFieldFromConfig('currencysymb', 'vcmGetCurrencySymb', $skip_session);
	}
	
	public static function getCurrencyName($skip_session=false) 
	{
		return self::getFieldFromConfig('currencyname', 'vcmGetCurrencyName', $skip_session);
	}
	
	public static function getDefaultPaymentID($skip_session=false) 
	{
		return intval(self::getFieldFromConfig('defaultpayment', 'vcmGetDefaultPayment', $skip_session));
	}
	
	public static function getApiKey($skip_session=false) 
	{
		return self::getFieldFromConfig('apikey', 'vcmGetApiKey', $skip_session);
	}

	public static function getProLevel($skip_session=true) 
	{
		return intval(self::getFieldFromConfig('pro_level', 'vcmGetProLevel', $skip_session));
	}
	
	public static function getAccountStatus($skip_session=false) 
	{
		return intval(self::getFieldFromConfig('account_status', 'vcmGetAccountStatus', $skip_session));
	}
	
	public static function isNewVersionAvailable($skip_session=false) 
	{
		return intval(self::getFieldFromConfig('to_update', 'vcmGetToUpdate', $skip_session));
	}

	public static function isProgramBlocked($skip_session=false) 
	{
		return intval(self::getFieldFromConfig('block_program', 'vcmGetBlockProgram', $skip_session));
	}
	
	public static function getTripConnectPartnerID() 
	{
		return VCMFactory::getConfig()->get('tac_partner_id');
	}

	public static function getTrivagoPartnerID($skip_session=false) 
	{
		return self::getFieldFromConfig('tri_partner_id', 'vcmGetTrivagoPartnerID', $skip_session);
	}
	
	public static function getTripConnectAccountID()
	{
		return VCMFactory::getConfig()->get('tac_account_id');
	}
	
	public static function getTripConnectApiKey($skip_session=false) 
	{
		return self::readHex(self::getFieldFromConfig('tac_api_key', 'vcmGetTripConnectApiKey', $skip_session));
	}

	//March 2017 - Trivago tracking requires Ciphered Account ID
	public static function getTrivagoAccountID()
	{
		$dbo = JFactory::getDbo();
		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='tri_account_id' LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			return '';
		}
		return self::readHex($dbo->loadResult());
	}
	//

	public static function getBackendLogoFullPath()
	{
		//since VCM 1.6.3 we use the Back-End Logo of VBO if defined
		$def_logo_path = VCM_ADMIN_URI.'assets/css/images/vikchannelmanager-logo.png';
		$session = JFactory::getSession();
		$sval = $session->get('vcmbacklogo', '');
		if (!empty($sval)) {
			return $sval;
		} elseif (file_exists(VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php")) {
			$dbo = JFactory::getDbo();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='backlogo';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$back_logo = $dbo->loadResult();
				if (!empty($back_logo) && strpos($back_logo, 'vikbooking') === false) {
					$def_logo_path = VBO_ADMIN_URI.'resources/'.$back_logo;
				}
			}
		}
		$session->set('vcmbacklogo', $def_logo_path);
		return $def_logo_path;
	}

	/**
	 * Generates a random UUID v4.
	 *
	 * A UUID is a 16-octet (128-bit) number. In its canonical form, a UUID is represented by 32 
	 * hexadecimal digits, displayed in five groups separated by hyphens, in the form 8-4-4-4-12
	 * for a total of 36 characters (32 alphanumeric characters and four hyphens).
	 *
	 * @return 	string
	 *
	 * @since 	1.6.13
	 */
	public static function uuid() {
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),

			// 16 bits for "time_mid"
			mt_rand(0, 0xffff),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,

			// 48 bits for "node"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

	/**
	 * Returns the configuration setting for 'defaultlang' to
	 * identify what's the default language for new reservations.
	 * If the record is missing, it will be generated as empty.
	 *
	 * @return 	string 	  The key (tag) of the default language.
	 *
	 * @since 	1.6.8
	 */
	public static function getDefaultLanguage()
	{
		$dbo = JFactory::getDbo();
		$default_lang = '';
		
		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='defaultlang';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			// missing record for the defaultlang
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('defaultlang', '');";
			$dbo->setQuery($q);
			$dbo->execute();
		} else {
			$default_lang = $dbo->loadResult();
		}

		return $default_lang;
	}
	
	/**
	 * Loads the framework used to encrypt/decrypt data.
	 * This method has been changed on 2018-05-16.
	 *
	 * @param 	mixed 	$options  An array of options or the salt key.
	 *
	 * @return 	CryptoMediator 	  The object used to encrypt/decrypt data.
	 *
	 * @since 	 1.6.8
	 */
	public static function loadCypherFramework($options = '')
	{
		static $loaded = 0;

		if (!$loaded)
		{
			$cipher_base = VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'crypto';
			
			require_once $cipher_base . DIRECTORY_SEPARATOR . 'cipher.php';
			require_once $cipher_base . DIRECTORY_SEPARATOR . 'mediator.php';

			if (is_scalar($options))
			{
				$salt 	 = $options;
				$options = array('handler' => 'client');
			}
			else
			{
				$options = (array) $options;
				$salt 	 = isset($options['salt']) ? $options['salt'] : uniqid();
			}

			$instance = CryptoMediator::getInstance();

			require_once $cipher_base . DIRECTORY_SEPARATOR . 'openssl' . DIRECTORY_SEPARATOR . 'openssl.php';
			$instance->addCipher(CipherOpenSSL::getInstance($options));

			require_once $cipher_base . DIRECTORY_SEPARATOR . 'mcrypt' . DIRECTORY_SEPARATOR . 'mcrypt.php';
			$instance->addCipher(new Encryption($salt));

			$loaded = 1;
		}
		else
		{
			$instance = CryptoMediator::getInstance();
		}

		return $instance;
	}

	/**
	 * Transmits to the e4jConnect servers the OpenSSL Public Key.
	 * Returns true on success, false otherwise.
	 *
	 * @param 	string 	$publickey 	the OpenSSL Public Key
	 * @param 	array 	$errors 	the OpenSSL errors list
	 *
	 * @return 	boolean
	 *
	 * @since 	 1.6.8
	 */
	public static function transmitPublicKey($publickey, $errors = array()) 
	{
		$apikey = self::getApiKey(true);
		if (!function_exists('curl_init') || empty($apikey)) {
			return false;
		}

		if (empty($publickey)) {
			/**
			 * If the client handler could not generate the Public Key for
			 * some reasons related to the server, do not make the request.
			 * By returning false, the empty keys will be dropped allowing
			 * the system to try and re-generate them at the next attempt.
			 */
			foreach ($errors as $msg) {
				VikError::raiseWarning('', $msg);
			}
			
			return false;
		}

		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=pbk&c=generic";

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- PBK Request e4jConnect.com - VikChannelManager - VikBooking -->
<PubkeyRQ xmlns="http://www.e4jconnect.com/schemas/pbkrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.$apikey.'"/>
	<Key>'.$publickey.'</Key>
</PubkeyRQ>';
		
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();
		
		if ($e4jC->getErrorNo() || strlen($rs) < 6 || substr($rs, 0, 6) != 'e4j.ok') {
			return false;
		}

		return true;
	}

	/**
	 * Checks whether it's time to ask for updates.
	 * Checking for updates is performed at regular intervals,
	 * in case of update available, true is returned.
	 * False is returned otherwise, or if it's no time to check.
	 *
	 * @return 	boolean
	 *
	 * @since 	 1.6.8
	 */
	public static function checkUpdates()
	{
		$apikey = self::getApiKey(true);
		if (!function_exists('curl_init') || empty($apikey)) {
			return false;
		}

		$config = VCMFactory::getConfig();

		$now = time();

		$last_check = (int)$config->get('updatescheck', 0);
		if (!$last_check) {
			// return false because it's no time to check
			return false;
		}

		// one week minimum
		$lim = 86400*7;

		if (($now - $last_check) < $lim) {
			// too early to check again for updates
			return false;
		}

		// update last time it was checked
		$config->set('updatescheck', $now);

		// obtain the CMS version
		$cms_version = '0.0';
		if (VCMPlatformDetection::isJoomla()) {
			/**
			 * @joomlaonly
			 */
			if (defined('JVERSION')) {
				$cms_version = JVERSION;
			} elseif (function_exists('jimport')) {
				jimport('joomla.version');
				if (class_exists('JVersion')) {
					$version = new JVersion();
					$cms_version = $version->getShortVersion();
				}
			}
		} else {
			/**
			 * @wponly  we need to use the WP version global variable
			 */
			global $wp_version;

			// set CMS version
			$cms_version = $wp_version ?? $cms_version;
		}

		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=vcmv&c=generic";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager VCMV Request e4jConnect.com - VikBooking - extensionsforjoomla.com -->
<UpdateRQ xmlns="http://www.e4jconnect.com/schemas/updrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $apikey . '"/>
	<Fetch vcm_version="' . VIKCHANNELMANAGER_SOFTWARE_VERSION . '" joomla_version="' . $cms_version . '"/>
</UpdateRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();

		if ($e4jC->getErrorNo()) {
			echo @curl_error($e4jC->getCurlHeader());
			return false;
		}

		return (substr($rs, 0, 6) == 'e4j.ok');
	}

	public static function sleepAllowed()
	{
		$disabled_func = @ini_get('disable_functions');
		if (!empty($disabled_func)) {
			$disabled_list = explode(',', $disabled_func);
			return !in_array('sleep', $disabled_list);
		}
		return true;
	}
	
	public static function createTimestamp($date, $hour, $min, $skip_session=false) 
	{
		$formats = explode('/',self::getClearDateFormat($skip_session));
		$d_exp = explode('/',$date);
		
		if (count($d_exp) != 3) {
			return -1;
		}
		
		$_attr = array();
		for ($i = 0, $n = count($formats); $i < $n; $i++) {
			$_attr[$formats[$i]] = $d_exp[$i];
		}
		
		return mktime(intval($hour), intval($min), 0, intval($_attr['m']), intval($_attr['d']), intval($_attr['Y']));
		
	}

	/**
	 * Returns the current active channel-module details.
	 * 
	 * @param 	bool 	$skip_session 	Whether to skip session caching for fetching the db value.
	 * 
	 * @return 	array
	 */
	public static function getActiveModule($skip_session = false) 
	{
		$app = JFactory::getApplication();
		$dbo = JFactory::getDbo();

		/**
		 * We allow every administrator to work on its own channel-module.
		 * 
		 * @since 	1.8.28
		 */
		$cached_module = $app->getUserState('vcm.moduleactive', null);
		if ($cached_module) {
			// attempt to unserialize the cached module
			$cached_module = @unserialize($cached_module);
			if ($cached_module) {
				// safely return the user active module
				return $cached_module;
			}
		}

		// get the last active module
		$id = self::getFieldFromConfig('moduleactive', 'vcmGetModuleActive', $skip_session);

		/**
		 * Sub-channel for iCal is identified as "24-n"
		 * 
		 * @since 	1.7.0
		 */
		$ical_id = 0;
		if (strpos((string) $id, '-') !== false) {
			$parts = explode('-', $id);
			$id = (int) $parts[0];
			$ical_id = (int) $parts[1];
		}

		if ($id) {
			$q = "SELECT * FROM `#__vikchannelmanager_channel` WHERE `id`=" . (int)$id;
			$dbo->setQuery($q, 0, 1);
			$row = $dbo->loadAssoc();

			if ($row) {
				if (!empty($ical_id) && (int)$row['uniquekey'] == (int)VikChannelManagerConfig::ICAL) {
					// append iCal custom channel data
					$q = "SELECT * FROM `#__vikchannelmanager_ical_channels` WHERE `id`={$ical_id}";
					$dbo->setQuery($q, 0, 1);
					$ical_channel = $dbo->loadAssoc();

					if ($ical_channel) {
						$row['ical_channel'] = $ical_channel;
					}
				}

				// prepare caching
				$app->setUserState('vcm.moduleactive', serialize($row));

				return $row;
			}
		}

		$q = "SELECT * FROM `#__vikchannelmanager_channel`";
		$dbo->setQuery($q, 0, 1);
		$row = $dbo->loadAssoc();

		if ($row) {
			// update configuration value
			VCMFactory::getConfig()->set('moduleactive', $row['id']);

			// prepare caching
			$app->setUserState('vcm.moduleactive', serialize($row));

			return $row;
		}

		return [];
	}

	/**
	 * Returns the associative information of the requested channel record.
	 * 
	 * @param 	int|string 	$unique_key 	the channel unique key identifier.
	 * 
	 * @return 	array
	 */
	public static function getChannel($unique_key) 
	{
		// cache value in static var
		static $getChannelCache = null;

		if ($getChannelCache && isset($getChannelCache[$unique_key])) {
			return $getChannelCache[$unique_key];
		}

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikchannelmanager_channel'))
			->where($dbo->qn('uniquekey') . ' = ' . $dbo->q($unique_key));

		$dbo->setQuery($q, 0, 1);
		$channel = $dbo->loadAssoc();

		if (!$channel) {
			return [];
		}

		if (!$getChannelCache) {
			$getChannelCache = [];
		}

		$getChannelCache[$unique_key] = $channel;

		return $getChannelCache[$unique_key];
	}

	/**
	 * Returns the details of a mapped OTA account matching a key.
	 * Useful to get the property params by hotel/user ID.
	 * 
	 * @param 	int|string 	$unique_key 	The channel unique key identifier.
	 * @param 	string 		$mainkey 		The channel account main key value (i.e. "hotelid").
	 * 
	 * @return 	array
	 * 
	 * @since 	1.9.2
	 */
	public static function getChannelAccountData($uniquekey, $mainkey)
	{
		$dbo = JFactory::getDbo();

		$dbo->setQuery(
			$dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikchannelmanager_roomsxref'))
				->where($dbo->qn('idchannel') . ' = ' . $dbo->q($uniquekey))
				->where($dbo->qn('prop_params') . ' LIKE ' . $dbo->q("%{$mainkey}%"))
		);

		foreach ($dbo->loadObjectList() as $record) {
			$prop_params = (array) json_decode((string) $record->prop_params, true);
			$otapricing = (array) json_decode((string) $record->otapricing, true);
			foreach ($prop_params as $param_key => $param_value) {
				if ($param_value == $mainkey) {
					// match found
					return [
						'idchannel'   => $record->idchannel,
						'channel'     => $record->channel,
						'otapricing'  => $otapricing,
						'prop_name'   => $record->prop_name,
						'prop_params' => $prop_params,
					];
				}
			}
		}

		return [];
	}

	/**
	 * Returns the channel information from its name.
	 * 
	 * @param 	string 	$ch_name 	the name of the channel to get.
	 * 
	 * @return 	array
	 * 
	 * @uses 	getChannel()
	 * 
	 * @since 	1.8.3
	 */
	public static function getChannelFromName($ch_name)
	{
		if (empty($ch_name)) {
			return [];
		}

		if (strpos($ch_name, '_')) {
			$parts = explode('_', $ch_name);
			$ch_name = $parts[0];
		}

		$dbo = JFactory::getDbo();

		$q = "SELECT `uniquekey` FROM `#__vikchannelmanager_channel` WHERE `name`=" . $dbo->q($ch_name);
		$dbo->setQuery($q, 0, 1);
		$unique_key = $dbo->loadResult();

		if (!$unique_key) {
			return [];
		}

		return self::getChannel($unique_key);
	}

	public static function getChannelCredentials($unique_key) 
	{
		$row = self::getChannel($unique_key);
		if (count($row) > 0) {
			return json_decode($row['params'], true);
		}
		return array();
	}

	protected static function getFieldFromConfig($param, $session_key, $skipsession=false) 
	{
		if ($skipsession) {
			return VCMFactory::getConfig()->get($param, '');
		}

		$session = JFactory::getSession();
		$sval = $session->get($session_key, '');

		if (!empty($sval)) {
			return $sval;
		}

		$setting = VCMFactory::getConfig()->get($param, '');
		$session->set($session_key, $setting);

		return $setting;
	}

	/**
	 * Returns the information about the max dates used to push the inventory through the Bulk Actions.
	 * 
	 * @return 	array 	associative array with the information about the inventory max dates.
	 * 
	 * @since 	1.7.1
	 */
	public static function getInventoryMaxFutureDates()
	{
		static $getInventoryMaxFutureDates = null;

		if ($getInventoryMaxFutureDates) {
			return $getInventoryMaxFutureDates;
		}

		$maxdates = VCMFactory::getConfig()->getArray('inventory_max_dates', []);

		// cache value in static var and return it
		$getInventoryMaxFutureDates = $maxdates;

		return $maxdates;
	}

	/**
	 * Updates the last used max dates for the inventory.
	 * 
	 * @param 	array 	$dates 	associative array of timestamps,
	 * 							usually with 'av' and 'rates' keys.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.7.1
	 */
	public static function setInventoryMaxFutureDates($dates)
	{
		if (!is_array($dates)) {
			return;
		}

		$max_dates = self::getInventoryMaxFutureDates();
		$max_dates['av']    = $max_dates['av'] ?? 0;
		$max_dates['rates'] = $max_dates['rates'] ?? 0;

		$max_dates['av']    = isset($dates['av']) && $dates['av'] > $max_dates['av'] ? $dates['av'] : $max_dates['av'];
		$max_dates['rates'] = isset($dates['rates']) && $dates['rates'] > $max_dates['rates'] ? $dates['rates'] : $max_dates['rates'];

		VCMFactory::getConfig()->set('inventory_max_dates', json_encode($max_dates));
	}

	public static function getBulkRatesCache()
	{
		return VCMFactory::getConfig()->getArray('bulkratescache', []);
	}

	public static function getBulkRatesAdvParams()
	{
		return VCMFactory::getConfig()->getArray('bulkratesadvparams', []);
	}

	public static function updateBulkRatesAdvParams($bulk_rates_adv_params) 
	{
		if (!is_array($bulk_rates_adv_params)) {
			$bulk_rates_adv_params = [];
		}

		VCMFactory::getConfig()->set('bulkratesadvparams', json_encode($bulk_rates_adv_params));

		return $bulk_rates_adv_params;
	}

	/**
	 * Get and/or Update the last Endpoint URL sent to the e4jConnect servers for the channels credentials.
	 * This method is mainly used to display an alert to the users visiting the Settings page, whenever the
	 * current protocol or domain is different than the one that was previously submitted.
	 * 
	 * @param 	bool 	$update 	true to set the current endpoint URL as the last one used.
	 *
	 * @return 	string
	 */
	public static function getLastEndpoint($update = false) 
	{
		$current_url = JUri::root();

		$last_endpoint = VCMFactory::getConfig()->get('last_endpoint', '');

		if ($update) {
			// set new endpoint URL and return it
			VCMFactory::getConfig()->set('last_endpoint', $current_url);
			return $current_url;
		}

		return $last_endpoint;
	}

	/**
	 * This method checks if the bookings first summary request can be submitted to e4jConnect.
	 * This is only needed at the first rooms mapping procedure, when configuring the channel manager
	 * for the first time. Also, this request cannot be submitted multiple times and this method prevents that.
	 * 
	 * @param 	int 	$uniquekey 		the channel unique key identifier.
	 * @param 	int 	$set_status 	set new status for the channel first summary.
	 * @param 	int 	$checkfs 		whether first summary should check for multi-accounts mapped.
	 * 									this value is passed to 1 only after saving the rooms relations.
	 *
	 * @return 	int 	0 = cannot send the request (request sent/ignored). 1 = can send the request.
	 * 
	 * @since 	1.6.13 	we now allow a third parameter to allow the first summary for multiple accounts.
	 */
	public static function checkFirstBookingSummary($uniquekey, $set_status = -1, $checkfs = 0) 
	{
		$dbo = JFactory::getDbo();

		// check if credentials have been submitted
		$has_cred = false;
		$q = "SELECT `params` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".(int)$uniquekey." AND `av_enabled`=1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cur_params = $dbo->loadResult();
			if (!empty($cur_params)) {
				$arr = json_decode($cur_params, true);
				if (is_array($arr)) {
					foreach ($arr as $k => $v) {
						if (!empty($v)) {
							// credentials submitted for this channel
							$has_cred = true;
						}
						// we only need to check the first parameter
						break;
					}
				}
			}
		}

		// check if some rooms have already been mapped
		$rooms_mapped 	= false;
		$count_accounts = array();
		$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=".(int)$uniquekey.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$rooms_mapped = true;
			$rows = $dbo->loadAssocList();
			foreach ($rows as $row) {
				if (!in_array($row['prop_params'], $count_accounts)) {
					array_push($count_accounts, $row['prop_params']);
				}
			}
		}
		
		// check current status
		$cur_status = 0;
		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='firstbsummary".$uniquekey."';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cur_status = (int)$dbo->loadResult();
			if ($set_status >= 0) {
				$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=".$dbo->quote($set_status)." WHERE `param`='firstbsummary".$uniquekey."';";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		} else {
			$cur_status = !$rooms_mapped ? 1 : 0;
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('firstbsummary".$uniquekey."', '".$cur_status."');";
			$dbo->setQuery($q);
			$dbo->execute();
		}

		if ($checkfs > 0 && count($count_accounts) > 1 && $cur_status < 1) {
			// we have mapped or re-mapped the rooms of a new account so we allow the bookings import
			$cur_status = 1;
			// update the db value so that the import will be allowed even in case the page is reloaded
			$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=".$dbo->quote($cur_status)." WHERE `param`='firstbsummary".$uniquekey."';";
			$dbo->setQuery($q);
			$dbo->execute();
		}

		return $has_cred === true && $rooms_mapped === true && $cur_status > 0 ? 1 : 0;
	}

	/**
	 * Tells whether at least one API or non-API channel is available.
	 * 
	 * @param 	bool 	$api_channel 	true for API channel, false for non-API channel (iCal included).
	 * 
	 * @return 	bool
	 * 
	 * @since 	1.8.9 	added the argument to get non-API channels.
	 * @since 	1.8.20 	implemented caching.
	 */
	public static function isAvailabilityRequest($api_channel = true)
	{
		// cache value in static var
		static $supports_av_rqs = [];

		$av_type = (int)$api_channel;

		if (isset($supports_av_rqs[$av_type])) {
			return $supports_av_rqs[$av_type];
		}

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select('COUNT(1)')
			->from($dbo->qn('#__vikchannelmanager_channel'))
			->where($dbo->qn('av_enabled') . ' = ' . $av_type);

		$dbo->setQuery($q);

		// cache value before returning it
		$supports_av_rqs[$av_type] = (bool)$dbo->loadResult();

		return $supports_av_rqs[$av_type];
	}

	public static function retrieveNotifications($launch = false)
	{
		$fastcheck = VikRequest::getInt('fastcheck');
		if (self::isAvailabilityRequest()) {
			?>
			<script type="text/javascript">
			var vcmStopNotifications = false;
			function vcmRetrieveNotifications() {
				if (vcmStopNotifications === true) {
					return true;
				}
				var jqxhr = jQuery.ajax({
					type: "POST",
					url: "index.php",
					data: { option: "com_vikchannelmanager", task: "check_notifications", tmpl: "component" }
				}).done(function(res) {
					if (parseInt(res) > 0) {
						jQuery("#dashboard-menu").text(res).fadeIn();
						if (!(jQuery("#vcm-audio-notification").length > 0)) {
							jQuery("#dashboard-menu").after("<audio id=\"vcm-audio-notification\" preload=\"auto\"><source type=\"audio/mp3\" src=\"<?php echo VCM_ADMIN_URI; ?>assets/css/audio/new_notification.mp3\"></source></audio>");
							document.getElementById('vcm-audio-notification').play();
						}
					} else {
						jQuery("#dashboard-menu").hide();
					}
				}).fail(function() {
					jQuery("#dashboard-menu").hide(); 
				});
			}
			jQuery(function() {
				setInterval(function() {vcmRetrieveNotifications()}, <?php echo $fastcheck > 0 ? '10000' : '30000'; ?>);
			<?php
			if ($fastcheck > 0) {
				?>
				setTimeout(function() {vcmRetrieveNotifications()}, 4000);
				<?php
			}
			if ($launch) { ?>
				vcmRetrieveNotifications();
			<?php
			}
			?>
			});
			</script>
			<?php
		}
	}

	/**
	 * Reads all or the given notifications.
	 * 
	 * @param 	array 	$notifications  list of notification records to read.
	 * @param 	bool 	$read_all 		whether all notifications should be marked as read.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.8.24  introduced 2nd argument $read_all.
	 */
	public static function readNotifications($notifications, $read_all = false)
	{
		if (!$notifications && !$read_all) {
			return;
		}

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->update($dbo->qn('#__vikchannelmanager_notifications'))
			->set($dbo->qn('read') . ' = 1');

		if ($read_all) {
			$q->where($dbo->qn('read') . ' = 0');
		} else {
			$notif_ids = array_filter(array_column($notifications, 'id'));
			if ($notif_ids) {
				$q->where($dbo->qn('id') . ' IN (' . implode(', ', array_map('intval', $notif_ids)) . ')');
			}
		}

		$dbo->setQuery($q);
		$dbo->execute();
	}

	public static function generateNKey($idordervb = 0) 
	{
		$dbo = JFactory::getDbo();
		$nkey = rand(1000, 9999);
		
		$q = "INSERT INTO `#__vikchannelmanager_keys` (`idordervb`,`key`) VALUES(" . (int)$idordervb . ", " . $dbo->quote($nkey) . ");";
		$dbo->setQuery($q);
		$dbo->execute();

		return $nkey;
	}

	public static function generateSerialCode($len=12, $_TOKENS='') 
	{
		if (empty($_TOKENS)) {
			$_TOKENS = array('ABCDEFGHIJKLMNOPQRSTUVWXYZ', '0123456789');
		}
		$_key = '';
		for ($i = 0; $i < $len; $i++) {
			$_row = rand(0, count($_TOKENS)-1);
			$_col = rand(0, strlen($_TOKENS[$_row])-1);
			$_key .= '' . $_TOKENS[$_row][$_col];
		}
		return $_key;
	}
	
	public static function updateNKey($nkey, $id_notification) 
	{
		$dbo = JFactory::getDbo();
		$q = "SELECT `id` FROM `#__vikchannelmanager_keys` WHERE `idordervb`=0 AND `key`=".(int)$nkey." ORDER BY `id` DESC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$key_id = $dbo->loadResult();
		$q = "UPDATE `#__vikchannelmanager_keys` SET `id_notification`=".(int)$id_notification." WHERE `id`=".(int)$key_id.";";
		$dbo->setQuery($q);
		$dbo->execute();
		return true;
	}
	
	public static function authorizeAction($ch_key) 
	{
		$dbo = JFactory::getDbo();
		$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".$dbo->quote($ch_key)." LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		return ($dbo->getNumRows() > 0);
	}

	/**
	 * Tells whether the hotel details have been submitted.
	 * 
	 * @return 	bool 	true if no empty required fields, or false.
	 */
	public static function checkIntegrityHotelDetails()
	{
		$dbo = JFactory::getDbo();
		$q = "SELECT `id` FROM `#__vikchannelmanager_hotel_details` WHERE `required`=1 AND `value`=''";
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		return ($dbo->getNumRows() == 0);
	}

	public static function composeSelectAmenities($name, $amenities = array(), $values = array(), $class = "", $assoc = false) 
	{
		$select_amenities = '<select name="'.$name.'" multiple="multiple" size="8" class="'.$class.'">';
		foreach ($amenities as $ka => $amenity) {
			$sel = false;
			for ($i = 0; $i < count($values) && !$sel; $i++) {
				$sel = (($values[$i] == $amenity) || ($values[$i] == $ka && $assoc));
			}
			$select_amenities .= '<option value="'.($assoc === true ? $ka : $amenity).'" '.(($sel) ? 'selected="selected"' : '').'>'.JText::_($amenity).'</option>';
		}
		$select_amenities .= '</select>';
		
		return $select_amenities;
	}
	
	public static function composeSelectRoomCodes($name, $options, $value = "", $class = "", $assoc = false) 
	{
		$bed_quant = 1;
		if (strpos($value, '=') !== false) {
			list($value, $bed_quant) = explode('=', $value);
		}
		$select_codes = '<select name="'.$name.'" class="'.$class.'">';
		foreach ($options as $kc => $code) {
			$bed_code_sel = (($value == $code) || ($value == $kc && $assoc)) ? true : false;
			$select_codes .= '<option value="'.($assoc === true ? $kc : $code).'" '.($bed_code_sel ? 'selected="selected"' : '').'>'.(JFactory::getLanguage()->hasKey("ROOM_".$code) ? JText::_("ROOM_".$code) : $code).'</option>';
		}
		$select_codes .= '</select>';
		if ($assoc) {
			$select_codes .= '&nbsp;<input type="number" size="2" name="numb_'.$name.'" class="'.$class.'-numb" value="'.$bed_quant.'" min="1">';
		}

		return $select_codes;
	}

	/**
	 * Returns an associative array with the information of each
	 * supported property class types and related category.
	 * 
	 * @return  array
	 * 
	 * @see 	https://connect.booking.com/user_guide/site/en-US/codes-pct/#pct-property-class-type-codes-ota-2014b-implemented
	 * 
	 * @since 	1.7.2
	 */
	public static function getAllPropertyClassTypes()
	{
		return array(
			'Apartments' => array(
				'apartment' => 'Apartment',
				'bed and breakfast' => 'Bed and breakfast',
				'residential apartment' => 'Residential apartment',
				'self catering accommodation' => 'Self catering accommodation',
				'efficiency studio' => 'Efficiency studio',
			),
			'Holiday Homes' => array(
				'holiday home' => 'Holiday Home',
				'cabin or bungalow' => 'Cabin or bungalow',
				'condominium' => 'Condominium',
				'mobile-home' => 'Mobile-home',
				'vacation home' => 'Vacation home',
				'castle' => 'Castle',
				'manor' => 'Manor',
			),
			'Camping' => array(
				'campground' => 'Campground',
				'recreational vehicle park' => 'Recreational vehicle park',
				'holiday park' => 'Holiday Park',
			),
			'Chalet' => array(
				'chalet' => 'Chalet',
			),
			'Farm stay' => array(
				'guest farm' => 'Guest farm',
				'ranch' => 'Ranch',
				'country house' => 'Country house',
			),
			'Guest House' => array(
				'guest house' => 'Guest House',
				'pension' => 'Pension',
				'guest house limited service' => 'Guest house limited service',
			),
			'Resort' => array(
				'resort' => 'Resort',
				'holiday resort' => 'Holiday resort',
				'meeting resort' => 'Meeting resort',
				'wildlife reserve' => 'Wildlife reserve',
			),
			'Hostel' => array(
				'hostel' => 'Hostel',
			),
			'Hotel' => array(
				'hotel' => 'Hotel',
				'boutique' => 'Boutique',
				'charm hotel' => 'Charm hotel',
				'aparthotel' => 'ApartHotel',
				'riad' => 'Riad',
				'ryokan' => 'Ryokan',
				'love hotel' => 'Love Hotel',
				'japanese-style business hotel' => 'Japanese-style Business Hotel',
				'capsule hotel' => 'Capsule Hotel',
			),
			'Inn' => array(
				'inn' => 'Inn',
			),
			'Lodge' => array(
				'lodge' => 'Lodge',
			),
			'Homestay' => array(
				'homestay' => 'Homestay',
				'monastery' => 'Monastery',
			),
			'Tented' => array(
				'tent' => 'Tent',
			),
			'Villa' => array(
				'villa' => 'Villa',
			),
			'Other' => array(
				'other' => 'Other...',
			),
		);
	}

	/**
	 * Returns an array with the active Property Class Types.
	 * 
	 * @return  array
	 * 
	 * @since 	1.7.2
	 */
	public static function getActivePropertyClassTypes()
	{
		$dbo = JFactory::getDbo();
		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='active_pct';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('active_pct', ".$dbo->quote(json_encode(array())).");";
			$dbo->setQuery($q);
			$dbo->execute();
			return array();
		}

		$pct = json_decode($dbo->loadResult());
		
		return is_array($pct) ? $pct : array();
	}

	public static function getSenderMail()
	{
		$dbo = JFactory::getDbo();
		$senderemail = self::getAdminMail();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='senderemail';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadResult();
			$senderemail = !empty($s) ? $s : $senderemail;
		}
		return $senderemail;
	}
	
	public static function getRoomRatesCost($id_room) 
	{
		$dbo = JFactory::getDbo();
		
		$room_cost = "";
		
		$q = "SELECT `params` FROM `#__vikbooking_rooms` WHERE `id`=".$id_room." LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$row = $dbo->loadAssoc();
			if (!empty($row['params'])) {
				$row = json_decode($row['params'], true);
				$room_cost = (float)$row['custprice'];
			}
		}
		
		if (empty($room_cost)) {
			$q = "SELECT `days`, `cost` FROM `#__vikbooking_dispcost` WHERE `idroom`=".$id_room." ORDER BY `days` ASC LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$row = $dbo->loadAssoc();
				$room_cost = $row['cost']/$row['days'];
			} else {
				$room_cost = 0.0;
			}
		}
		
		return $room_cost;
	}
	
	public static function getDateTimestamp($date, $h, $m, $skip_session = false) 
	{
		$df = self::getClearDateFormat($skip_session);
		$df = preg_replace("/\D/", '', $df);
		$x = preg_split("/\D/", $date);
		if ($df == "dmY") {
			$year = $x[2];
			$mon  = $x[1];
			$day  = $x[0];
		} elseif ($df == "mdY") {
			$year = $x[2];
			$mon  = $x[0];
			$day  = $x[1];
		} else {
			$year = $x[0];
			$mon  = $x[1];
			$day  = $x[2];
		}

		return mktime((int) $h, (int) $m, 0, $mon, $day, $year);
	}
	
	public static function loadOrdersRoomsDataVb ($idorder) 
	{
		$dbo = JFactory::getDbo();
		$q = "SELECT * FROM `#__vikbooking_ordersrooms` WHERE `idorder`=".$idorder.";";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
		return $s;
	}
	
	public static function getPaymentVb($idp) 
	{
		if (!empty ($idp)) {
			$dbo = JFactory::getDbo();
			$q = "SELECT * FROM `#__vikbooking_gpayments` WHERE `id`=".$dbo->quote($idp)." AND `published`=1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$payment = $dbo->loadAssocList();
				return $payment[0];
			} else {
				return false;
			}
		}
		return false;
	}
	
	public static function getHoursMoreRb($skipsession = false) 
	{
		if ($skipsession) {
			$dbo = JFactory::getDbo();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='hoursmorebookingback';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getHoursMoreRb', '');
			if (strlen($sval) > 0) {
				return $sval;
			} else {
				$dbo = JFactory::getDbo();
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='hoursmorebookingback';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('getHoursMoreRb', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}
	
	public static function getPriceName($idp) 
	{
		$dbo = JFactory::getDbo();
		$q = "SELECT `id`,`name` FROM `#__vikbooking_prices` WHERE `id`='" . $idp . "';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$n = $dbo->loadAssocList();
			return $n[0]['name'];
		}
		return "";
	}

	public static function getPriceAttr($idp) 
	{
		$dbo = JFactory::getDbo();
		$q = "SELECT `id`,`attr` FROM `#__vikbooking_prices` WHERE `id`='" . $idp . "';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$n = $dbo->loadAssocList();
			return $n[0]['attr'];
		}
		return "";
	}
	
	public static function loadOrdersVbNotifications($idorder) 
	{
		$dbo = JFactory::getDbo();
		$q = "SELECT * FROM `#__vikchannelmanager_notifications` WHERE `idordervb`='" . $idorder . "';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
		return $s;
	}
	
	public static function readHex($str) 
	{
		$var = "";
		for ($i = 0; $i < strlen($str); $i += 2)
			$var .= chr(hexdec(substr($str, $i, 2)));
		return $var;
	}
	
	/**
	 * Check Expiring Date
	 */
	 
	public static function validateChannelResponse($rs) 
	{
		$dbo = JFactory::getDbo();
		
		if (substr($rs, 0, 9) == 'e4j.error') {
			if (strpos($rs, 'AuthenticationError') !== false) {
				$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=0 WHERE `param`='account_status' LIMIT 1;";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		} else {
			$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=1 WHERE `param`='account_status' LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
		}
	}
	
	public static function getErrorFromMap($string, $revert_to_plain = false) 
	{
		if (strlen($string) == 0) {
			return JText::_('UNKNOWN_ERROR');
		}
		$plain_mess = $string;
		$params = array();
		if (strpos($string, ':') !== false) {
			$string = explode(':', $string);
			$nodes = explode('.', $string[0]);
			
			unset($string[0]);
			
			$params = explode(';;', implode(':', $string));
		} else {
			$nodes = explode('.', $string);
		}
		
		$pointer = VikChannelManagerConfig::$ERRORS_MAP;
		foreach ($nodes as $node) {
			
			if (empty($pointer[$node])) {
				if (!$revert_to_plain) {
					return JText::sprintf('UNKNOWN_ERROR_MAP', $node) . (stripos($plain_mess, 'Booking') !== false && stripos($plain_mess, 'access denied') !== false ? ' ' . JText::_('VCMBCOMACCDENIEDWARN') : '');
				}
				return $plain_mess;
			}
			
			$pointer = $pointer[$node];

		}
		
		$lang_text = JText::_($pointer['_default']);
		if (count($params) == 0) {
			return $lang_text;
		}
		return vsprintf($lang_text, $params);
	}
	
	/**
	 * VikBooking calls
	 */
	 
	public static function invokeChannelImpression()
	{
		$uri = JUri::getInstance();
		
		// get channel from request, session or cookie
		$channel = self::getChannelFromRequest();
		$session = JFactory::getSession();
		$input = JFactory::getApplication()->input;
		$cookie = $input->cookie;
		if (empty($channel)) {
			$channel = $session->get('vcmChannelData', '');
			if (empty($channel)) {
				$cookie_channel = $cookie->get('vcmChannelData', '', 'string');
				$channel = !empty($cookie_channel) ? json_decode($cookie_channel, true) : '';
				// check cookie integrity
				if (!empty($channel)) {
					$channel = self::getChannelFromRequest($channel);
				}
			}
		}

		if (!empty($channel) && is_array($channel)) {
			/**
			 * Trivago only: the Tracking Image (Pixel) will fade away by the 20th of December 2019.
			 * It has been replaced by the new Conversion API, which require a JSON call to be made
			 * by the e4jConnect servers. It is now necessary to retrieve the request value "trv_reference"
			 * which is appended by Trivago to the landing page of Rate Connect deep links.
			 * 
			 * @since 	1.6.18
			 */
			$trv_reference = $input->getString('trv_reference', '');
			if (!empty($trv_reference) && !isset($channel['trv_reference'])) {
				$channel['trv_reference'] = $trv_reference;
			}
			//

			/**
			 * Trivago only: force the use of the new Conversion API no matter of the limit date.
			 * This is only useful before Dec 20th 2019 to test the Conversion API with a real hotel.
			 * 
			 * @since 	1.6.20
			 */
			$force_conversion = $input->getInt('force_conversion', 0);
			if ($force_conversion > 0 && !isset($channel['force_conversion'])) {
				$channel['force_conversion'] = $force_conversion;
			}
			//

			/**
			 * Trivago only: the new Conversion API made the field "locale" mandatory.
			 * e4jConnect is passing it through a specific request value.
			 * 
			 * @since 	1.7.5
			 * @since 	1.9.10  added support for "trv_locale" new query string value that identifies the market.
			 */
			$locale = $input->getString('locale', '');
			$trv_locale = $input->getString('trv_locale', '');
			if (!empty($locale) && !isset($channel['locale'])) {
				$channel['locale'] = $locale;
			}
			if (!empty($trv_locale)) {
				$channel['locale'] = $trv_locale;
				$channel['trv_locale'] = $trv_locale;
			}
			//

			/**
			 * TripAdvisor only: the Tracking Pixel has been replaced by the S2S Conversion Tracking.
			 * This requires a GET request with some variables appended. The request is made by the
			 * e4jConnect servers. It is now necessary to retrieve the request value "refid"
			 * which is appended by TripAdvisor to the landing page of CPC deep link URLs.
			 * 
			 * @since 	1.6.20
			 */
			$refid = $input->getString('refid', '');
			if (!empty($refid) && !isset($channel['refid'])) {
				$channel['refid'] = $refid;
			}
			//

			$session->set('vcmChannelData', $channel);
			//VCM 1.6.3 - Cookie length must be accetable to avoid 403 errors on later pages. We now also use the path, domain and secure flags
			$config = JFactory::getConfig();
			if (isset($channel['params'])) {
				unset($channel['params']);
			}
			if (isset($channel['settings'])) {
				unset($channel['settings']);
			}
			// we set a cookie with a duration of 30 days
			if (method_exists('VikRequest', 'setCookie')) {
				/**
				 * @wponly 	if VBO is not updated, this method won't exist.
				 *
				 */
				VikRequest::setCookie('vcmChannelData', json_encode($channel), (time() + (86400 * 30)), $config->get('cookie_path', '/'), $config->get('cookie_domain', ''), (strtolower($uri->getScheme()) == 'https'));
			} else {
				$cookie->set('vcmChannelData', json_encode($channel), (time() + (86400 * 30)), $config->get('cookie_path', '/'), $config->get('cookie_domain', ''), (strtolower($uri->getScheme()) == 'https'));
			}
			//
			// PIXEL IMPRESSION
			switch ($channel['uniquekey']) {
				case VikChannelManagerConfig::TRIP_CONNECT:
					self::generateTripConnectPixel($uri->getScheme().'://www.tripadvisor.com/js3/conversion/pixel.js', array(), 1, $channel);
					break;
				case VikChannelManagerConfig::TRIVAGO:
					self::generateTrivagoPixel();
					break;
				default:
					break;
			}
		}
		
	}
	
	public static function invokeChannelConversionImpression($order = array()) 
	{
		$uri = JUri::getInstance();

		$session = JFactory::getSession();
		$channel = $session->get('vcmChannelData', '');
		$cookie = JFactory::getApplication()->input->cookie;
		if (empty($channel)) {
			$cookie_channel = $cookie->get('vcmChannelData', '', 'string');
			$channel = !empty($cookie_channel) ? json_decode($cookie_channel, true) : '';
			//check cookie integrity
			if (!empty($channel)) {
				$channel = self::getChannelFromRequest($channel);
			}
			//
		}
		if (!empty($channel)) {
			// Conversion PIXEL IMPRESSION
			switch ($channel['uniquekey']) {
				case VikChannelManagerConfig::TRIP_CONNECT:
					self::generateTripConnectPixel($uri->getScheme().'://www.tripadvisor.com/js3/conversion/pixel.js', $order, 2, $channel);
					break;
				case VikChannelManagerConfig::TRIVAGO:
					self::generateTrivagoPixel($order, 2, $channel);
					break;
				default:
					break;
			}
			// we unset the session and the cookie as well, so that the conversion will not run twice
			$session->set('vcmChannelData', '');
			if (method_exists('VikRequest', 'setCookie')) {
				/**
				 * @wponly 	if VBO is not updated, this method won't exist.
				 *
				 */
				VikRequest::setCookie('vcmChannelData', json_encode($channel), (time() - (86400 * 30)), '/');
			} else {
				$cookie->set('vcmChannelData', json_encode($channel), (time() - (86400 * 30)), '/');
			}
		}
		
	}
	
	public static function generateTrivagoPixel($order = array(), $type = 1, $channel_data = array()) 
	{
		if (count(self::getChannelCredentials(VikChannelManagerConfig::TRIVAGO)) == 0) {
			return false;
		}

		if ($type == 1) {
			//nothing needs to be done in the landing page (Search Results)
			return;
		}

		$account_id = self::getTrivagoAccountID();
		$partner_id = self::getTrivagoPartnerID();
		$curr_name = self::getCurrencyName();
		$num_rooms = 1;
		// re-fetch the exact booking total from DB, including all rooms, all nights, all taxes
		$dbo = JFactory::getDbo();
		$q = "SELECT `roomsnum`, `total` FROM `#__vikbooking_orders` WHERE `id`=".(int)$order['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$oinfo = $dbo->loadAssoc();
			$num_rooms = (int)$oinfo['roomsnum'];
			$order['total'] = (float)$oinfo['total'];
			// VCM 1.6.1 Update the idorderota if empty, for the App
			if ($order['channel'] == 'trivago' && array_key_exists('idorderota', $order) && empty($order['idorderota'])) {
				$q = "UPDATE `#__vikbooking_orders` SET `idorderota`=".(int)$order['id']." WHERE `id`=".(int)$order['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			//
		}

		/**
		 * We have agreed for a limit date of December 1st 2019 to remove the old tracking pixel.
		 * The Conversion API (XML request) will be automatically used after that date.
		 * It is also possible to use "&force_conversion=1" to use the Conversion API before that date.
		 * 
		 * @since 	1.6.20
		 */
		$switch_date = mktime(0, 0, 0, 12, 1, 2019);
		if (time() < $switch_date && (!isset($channel_data['force_conversion']) || empty($channel_data['force_conversion']))) {
			/**
			 * The old Trivago Tracking Image (Pixel) will fade away by the 20th of December 2019.
			 * It has been replaced by the new Conversion API.
			 * 
			 * @since 		1.6.19
			 * @deprecated 	we use the Conversion API
			 */
			echo '<img height="1" width="1" style="border-style:none;" alt="" src="https://secde.trivago.com/page_check.php?pagetype=track&ref='.$account_id.'&hotel='.$partner_id.'&arrival='.$order['checkin'].'&departure='.$order['checkout'].'&currency='.$curr_name.'&volume='.number_format($order['total'], 2, '.', '').'&booking_id='.$order['id'].'" />';

			// break the method
			return;
		}

		/**
		 * The Trivago Tracking Image (Pixel) will fade away by the 20th of December 2019.
		 * It has been replaced by the new Conversion API, which require a JSON call to
		 * be made by the e4jConnect servers.
		 * 
		 * @since 	1.6.19
		 */
		$e4jc_url 	= "https://e4jconnect.com/channelmanager/?r=bconv&c=trivago";
		$api_key 	= self::getApiKey(true);
		$xml 		= '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager BCONV Request e4jConnect.com - VikBooking -->
<BookingConversionRQ xmlns="http://www.e4jconnect.com/schemas/bconvrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="'.$api_key.'"/>
	<Information>' . "\n";

		// include private information node
		$xml .= "\t\t" . '<PrivateDetails>
		<PrivateDetail type="ref" value="' . $account_id . '" />
		<PrivateDetail type="hotel" value="' . $partner_id . '" />
		<PrivateDetail type="trv_reference" value="' . (isset($channel_data['trv_reference']) ? $channel_data['trv_reference'] : '') . '" />
		<PrivateDetail type="tebadv" value="' . (isset($channel_data['tebadv']) && (int)$channel_data['tebadv'] > 0 ? '1' : '0') . '" />
	</PrivateDetails>' . "\n";

		/**
		 * An update of November 2020 requires the Conversion API of Trivago
		 * to pass along also the "locale" string of 2 chars, that generated
		 * originally in the landing page by passing the language.
		 * 
		 * @since 	1.7.5
		 * @since 	1.9.10  added support for "trv_locale" new query string value that identifies the market.
		 */
		$locale = strtoupper(substr(JFactory::getLanguage()->getTag(), -2));
		if (!empty($channel_data['trv_locale'])) {
			$locale = $channel_data['trv_locale'];
		} elseif (!empty($channel_data['locale'])) {
			$locale = $channel_data['locale'];
		}
		//

		// build public booking information for Conversion API request
		$pubinfo = array(
			'arrival' 		  => date('Y-m-d', $order['checkin']),
			'departure' 	  => date('Y-m-d', $order['checkout']),
			'created_on' 	  => date('Y-m-d H:i:s', $order['ts']),
			'currency' 		  => $curr_name,
			'volume' 		  => number_format($order['total'], 2, '.', ''),
			'booking_id' 	  => $order['id'],
			'locale' 		  => $locale,
			'number_of_rooms' => $num_rooms,
		);

		$xml .= "\t\t" . '<PublicDetails>' . "\n";
		foreach ($pubinfo as $k => $v) {
			$xml .= "\t\t\t" . '<PublicDetail type="' . htmlentities($k) . '" value="' . htmlentities($v) . '" />' . "\n";
		}
		$xml .= "\t\t" . '</PublicDetails>' . "\n";
		//
		$xml .= "\t" . '</Information>
</BookingConversionRQ>';

		// prepare the event data object
		$ev_data = new stdClass;
		$ev_data->channel 	 = 'trivago';
		$ev_data->bconv_type = 'Confirmation';
		$ev_data->bconv_data = $channel_data;
		// clean up useless event data object properties
		unset($ev_data->bconv_data['name'], $ev_data->bconv_data['params'], $ev_data->bconv_data['uniquekey'], $ev_data->bconv_data['av_enabled'], $ev_data->bconv_data['settings']);
		// set event description
		$ev_descr = $ev_data->channel . ' - Booking Conversion Tracking (' . $ev_data->bconv_type . ')';

		/**
		 * Try to instantiate the history object from VBO.
		 * Logs and event data may need to be stored.
		 * 
		 * @since 	1.8.3
		 */
		$history_obj = null;
		try {
			if (!class_exists('VikBooking')) {
				require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';
			}
			if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
				$history_obj = VikBooking::getBookingHistoryInstance();
				$history_obj->setBid($order['id']);
			}
		} catch (Exception $e) {
			// do nothing
		}

		// start the request
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->slaveEnabled = true;
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();

		// check any possible communication error
		if ($e4jC->getErrorNo()) {
			$error = self::getErrorFromMap($e4jC->getErrorMsg());
			if ($history_obj) {
				// log the error
				$ev_data->bconv_type = 'Error';
				$history_obj->setExtraData($ev_data)->store('CM', $ev_descr . "\n" . $error);
			}
			return false;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			$error = self::getErrorFromMap($rs);
			if ($history_obj) {
				// log the error
				$ev_data->bconv_type = 'Error';
				$history_obj->setExtraData($ev_data)->store('CM', $ev_descr . "\n" . $error);
			}
			return false;
		}

		if ($history_obj) {
			// log the successful operation
			$history_obj->setExtraData($ev_data)->store('CM', $ev_descr);
		}

		return true;
	}

	/**
	 * This method is invoked every time we need to communicate an impression for TripAdvisor
	 * or whenever a booking conversion event takes place. We used to add a JavaScript file
	 * in the document and to add different JavaScript code depending on the event.
	 * Since the end of November 2019 the new S2S Conversion Tracking method is adopted.
	 * A GET request needs to be made by the e4jConnect servers for every "PAGEVIEW" event
	 * as well as for every "BOOKING_CONFIRMATION" event.
	 * 
	 * @param 	$pixel_uri 		string 	the old tracking pixel URI	@deprecated since 1.6.20
	 * @param 	$order 			array 	the booking details array
	 * @param 	$type 			int 	1 for the landing page, 2 for booking conversion
	 * @param 	$channel_data 	array 	the impression array of information
	 * 
	 * @return 	boolean 			True on success. False otherwise.
	 * 
	 * @since 	1.6.20 (the implementation of the new S2S Conversion Tracking)
	 */
	private static function generateTripConnectPixel($pixel_uri, $order = array(), $type = 1, $channel_data = array()) 
	{
		$account_id = self::getTripConnectAccountID();
		
		if (empty($account_id) || count(self::getChannelCredentials(VikChannelManagerConfig::TRIP_CONNECT)) == 0) {
			return false;
		}

		// require main VBO lib
		if (!class_exists('VikBooking')) {
			require_once(VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php');
		}
		
		$dbo 		= JFactory::getDbo();
		$session  	= JFactory::getSession();
		$api_key 	= self::getApiKey(true);
		$partner_id = self::getTripConnectPartnerID();
		$curr_name  = self::getCurrencyName();
		$e4jc_url 	= "https://e4jconnect.com/channelmanager/?r=bconv&c=tripadvisor";
		
		/**
		 * @deprecated 	we use the S2S Conversion Tracking method
		 */
		// $document = JFactory::getDocument();
		// $vik = new VikApplication(VersionListener::getID());
		// $vik->addScript($pixel_uri);
		
		// an alphanumeric string that uniquely identifies this transaction
		$tx_id = $session->get('ta_tx_id', md5(uniqid()), 'vikchannelmanager');

		// all the fields for the Conversion Tracking request (at least one key is required by the schema)
		$xmlfields = array(
			'created_on' => date('Y-m-d H:i:s'),
			'gbv' 		 => 0,
			'currency' 	 => $curr_name,
			'order_id' 	 => $tx_id,
			'tax' 		 => 0,
			'fees' 		 => 0,
			'startDate'  => date('Y-m-d'),
			'endDate'  	 => date('Y-m-d', strtotime('tomorrow')),
			'numAdults'  => 2,
		);

		// update transaction ID
		$session->set('ta_tx_id', $tx_id, 'vikchannelmanager');
		
		if ($type == 1) {
			/**
			 * @deprecated 	we use the S2S Conversion Tracking method
			 */
			// $document->addScriptDeclaration('window.onload = function(e){TAPixel.impressionWithReferer("'.$account_id.'");}');

			// for the "PAGEVIEW" event (landing page) we use the Slave as primary endpoint
			$e4jc_url = str_replace('https://e4jconnect', 'https://slave.e4jconnect', $e4jc_url);
			$ta_event = 'PAGEVIEW';

			// attempt to get checkin and checkout dates + num adults from request values
			$rq_start_date = VikRequest::getString('start_date', '');
			$rq_end_date = VikRequest::getString('end_date', '');
			$rq_checkindate = VikRequest::getString('checkindate', '');
			$rq_checkoutdate = VikRequest::getString('checkoutdate', '');
			$rq_checkin = VikRequest::getString('checkin', '');
			$rq_checkout = VikRequest::getString('checkout', '');
			if (!empty($rq_start_date)) {
				// Y-m-d format from deeplink URL of TripAdvisor in landing page
				$xmlfields['startDate'] = $rq_start_date;
				$xmlfields['endDate'] = $rq_end_date;
			} elseif (!empty($rq_checkindate)) {
				// post value from VBO search form
				$xmlfields['startDate'] = date('Y-m-d', VikBooking::getDateTimestamp($rq_checkindate, 0, 0, 0));
				$xmlfields['endDate'] = date('Y-m-d', VikBooking::getDateTimestamp($rq_checkoutdate, 0, 0, 0));
			} elseif (!empty($rq_checkin)) {
				// post value from VBO booking process
				$xmlfields['startDate'] = date('Y-m-d', $rq_checkin);
				$xmlfields['endDate'] = date('Y-m-d', $rq_checkout);
			}
			$rq_adults = VikRequest::getVar('adults', array());
			if (count($rq_adults)) {
				$tot_adults = 0;
				foreach ($rq_adults as $adults) {
					$tot_adults += (int)$adults;
				}
				$xmlfields['numAdults'] = $tot_adults;
			}
		} else {
			// "BOOKING_CONFIRMATION" event
			$ta_event = 'BOOKING_CONFIRMATION';

			// count all adults from db
			$q = "SELECT COUNT(`adults`) AS `tot_adults` FROM `#__vikbooking_ordersrooms` WHERE `idorder`=" . (int)$order['id'] . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			$tot_adults = (int)$dbo->loadResult();

			$xmlfields['numAdults'] = $tot_adults;
			$xmlfields['startDate'] = date('Y-m-d', $order['checkin']);
			$xmlfields['endDate'] = date('Y-m-d', $order['checkout']);
			$xmlfields['gbv'] = round(($order['total'] * 100), 0);
			$xmlfields['order_id'] = $order['id'];
			$xmlfields['tax'] = round((($order['tot_taxes'] + $order['tot_city_taxes']) * 100), 0);
			$xmlfields['fees'] = round(($order['tot_fees'] * 100), 0);

			/**
			 * @deprecated 	we use the S2S Conversion Tracking method
			 */
			// $document->addScriptDeclaration('window.onload = function(e){TAPixel.conversionWithReferer(
			// 	"'.$account_id.'", "'.$partner_id.'", '.intval($order['total']*100).', "'.$curr_name.'", '.intval($order['taxes']*100).
			// 	', '.intval($order['fees']*100).', "'.date('Y-m-d', $order['checkin']).'", "'.date('Y-m-d', $order['checkout']).'", '.intval($order['tot_adults']).', "'.$order['confirmnumber'].'"
			// );}');
			
			// VCM 1.6.1 Update the idorderota if empty, for the App
			if ($order['channel'] == 'tripconnect' && array_key_exists('idorderota', $order) && empty($order['idorderota'])) {
				$dbo = JFactory::getDbo();
				$q = "UPDATE `#__vikbooking_orders` SET `idorderota`=".(int)$order['id']." WHERE `id`=".(int)$order['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			//
		}

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager BCONV Request e4jConnect.com - VikBooking -->
<BookingConversionRQ xmlns="http://www.e4jconnect.com/schemas/bconvrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="'.$api_key.'"/>
	<Information>' . "\n";
		// include private information node
		$xml .= "\t\t" . '<PrivateDetails>
			<PrivateDetail type="account_id" value="' . urlencode($account_id) . '" />
			<PrivateDetail type="partner_id" value="' . urlencode($partner_id) . '" />
			<PrivateDetail type="currency" value="' . urlencode($curr_name) . '" />
			<PrivateDetail type="refid" value="' . (isset($channel_data['refid']) ? urlencode($channel_data['refid']) : '') . '" />
			<PrivateDetail type="event" value="' . urlencode($ta_event) . '" />
		</PrivateDetails>' . "\n";
		// build public booking information for Conversion Tracking request
		$xml .= "\t\t" . '<PublicDetails>' . "\n";
		foreach ($xmlfields as $k => $v) {
			$xml .= "\t\t\t" . '<PublicDetail type="' . $k . '" value="' . urlencode($v) . '" />' . "\n";
		}
		$xml .= "\t\t" . '</PublicDetails>' . "\n";
		//
		$xml .= "\t" . '</Information>
</BookingConversionRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->slaveEnabled = true;
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();

		// for the moment we ignore any possible communication error
		if ($e4jC->getErrorNo()) {
			$error = self::getErrorFromMap($e4jC->getErrorMsg());
			return false;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			$error = self::getErrorFromMap($rs);
			return false;
		}

		return true;
	}

	/**
	 * Given specific reserved request vars, checks if the current booking link
	 * must be assigned to a meta-search service or to an external source.
	 * 
	 * @param 	mixed 	$cookie_ch 	empty string or array with decoded session/cookie channel data.
	 * 
	 * @return 	mixed 				empty string in case of failure, or associative array with channel data.
	 * 
	 * @since 	1.9.4 				added support to Google Vacation Rentals.
	 */
	private static function getChannelFromRequest($cookie_ch = []) 
	{
		$vig = new Vigenere([
			'0' => 0,
			'1' => 1,
			'2' => 2,
			'3' => 3,
			'4' => 4,
			'5' => 5,
			'6' => 6,
			'7' => 7,
			'8' => 8,
			'9' => 9,
		]);

		// TripAdvisor
		$ta_ch_enc = VikRequest::getString('ta_ch');
		if (!empty($ta_ch_enc)) {
			$dec = $vig->decrypt($ta_ch_enc, (string)self::getTripConnectPartnerID());
			$dec = substr($dec, strlen(VikChannelManagerConfig::VCM_CONNECTION_SERIAL));
			if ($dec == VikChannelManagerConfig::TRIP_CONNECT) {
				$channel = self::getChannel(VikChannelManagerConfig::TRIP_CONNECT);
				$channel['disclaimer'] = 'TRIP_CONNECT_DISCLAIMER';
				$channel['url_ch'] = $ta_ch_enc;
				return $channel;
			}

			// abort
			return '';
		}

		// Trivago
		$tri_ch_enc = VikRequest::getString('tri_ch');
		if (!empty($tri_ch_enc)) {
			$dec = $vig->decrypt($tri_ch_enc, (string)self::getTrivagoPartnerID());
			$dec = substr($dec, strlen(VikChannelManagerConfig::VCM_CONNECTION_SERIAL));
			if ($dec == VikChannelManagerConfig::TRIVAGO) {
				$channel = self::getChannel(VikChannelManagerConfig::TRIVAGO);
				$channel['url_ch'] = $tri_ch_enc;
				return $channel;
			}

			// abort
			return '';
		}

		// Google Hotel Ads (Google Hotel/VR)
		$google_ch_enc = VikRequest::getString('google_ch');
		if (!empty($google_ch_enc)) {
			/**
			 * Give priority to Google Vacation Rentals channel identifier for single accounts.
			 * 
			 * @since 	1.9.4
			 */
			$google_vr_account_key = (string) VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::GOOGLEVR, '');
			$dec = $vig->decrypt($google_ch_enc, $google_vr_account_key);
			$dec = substr($dec, strlen(VikChannelManagerConfig::VCM_CONNECTION_SERIAL));
			if ($dec == VikChannelManagerConfig::GOOGLEVR) {
				$channel = self::getChannel(VikChannelManagerConfig::GOOGLEVR);
				$channel['url_ch'] = $google_ch_enc;
				return $channel;
			}

			/**
			 * Enable tracking support for multiple hotel accounts with Google Hotel.
			 * 
			 * @since 	1.8.7
			 */
			$multi_accounts = VCMGhotelMultiaccounts::getAll(true);
			$multi_accounts[] = (string) self::getHotelInventoryID();
			foreach ($multi_accounts as $ghotel_account) {
				$dec = $vig->decrypt($google_ch_enc, $ghotel_account);
				$dec = substr($dec, strlen(VikChannelManagerConfig::VCM_CONNECTION_SERIAL));
				if ($dec == VikChannelManagerConfig::GOOGLEHOTEL) {
					$channel = self::getChannel(VikChannelManagerConfig::GOOGLEHOTEL);
					$channel['url_ch'] = $google_ch_enc;
					return $channel;
				}
			}

			// abort
			return '';
		}

		if (!empty($cookie_ch) && is_array($cookie_ch)) {
			if (!empty($cookie_ch['uniquekey'])) {
				$partner_id = '';
				$validate_ch = -1;
				if ($cookie_ch['uniquekey'] == VikChannelManagerConfig::TRIP_CONNECT) {
					$partner_id = self::getTripConnectPartnerID();
					$validate_ch = VikChannelManagerConfig::TRIP_CONNECT;
				} elseif ($cookie_ch['uniquekey'] == VikChannelManagerConfig::TRIVAGO) {
					$partner_id = self::getTrivagoPartnerID();
					$validate_ch = VikChannelManagerConfig::TRIVAGO;
				} elseif ($cookie_ch['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL) {
					$partner_id = self::getHotelInventoryID();
					$validate_ch = VikChannelManagerConfig::GOOGLEHOTEL;
				}
				if (!empty($partner_id)) {
					$dec = $vig->decrypt($cookie_ch['url_ch'], (string)$partner_id);
					$dec = substr($dec, strlen(VikChannelManagerConfig::VCM_CONNECTION_SERIAL));
					if ($dec == $validate_ch) {
						return $cookie_ch;
					}
				}
			}

			// abort
			return '';
		}

		// abort
		return '';
	}
	
	public static function storeCallStats($channel, $call, $elapsed_time) 
	{
		
		$last_call = array();
		
		$dbo = JFactory::getDbo();
		
		$q = "SELECT *  FROM `#__vikchannelmanager_call_stats` WHERE `channel`=".$dbo->quote($channel)." AND `call`=".$dbo->quote($call)." LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$last_call = $dbo->loadAssoc();
			
			$min = min(array($elapsed_time, $last_call['min_exec_time']));
			$max = max(array($elapsed_time, $last_call['max_exec_time']));
			
			$q = "UPDATE `#__vikchannelmanager_call_stats` SET `last_exec_time`=".$elapsed_time.",`min_exec_time`=".$min.",`max_exec_time`=".$max.",`last_visit`=NOW() WHERE `id`=".$last_call['id']." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
		} else {
			$q = "INSERT INTO `#__vikchannelmanager_call_stats`(`channel`,`call`,`min_exec_time`,`max_exec_time`,`last_exec_time`) VALUES (".
				$dbo->quote($channel).",".$dbo->quote($call).",".$elapsed_time.",".$elapsed_time.",".$elapsed_time.
			");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
	}

	/**
	 * This method splits and masks the credit card details received
	 * from TripConnect Instant Booking or from Trivago Express Booking.
	 * The string to be stored in the payment logs is then returned.
	 * For trivago Express Booking, the payment method has a different structure,
	 * and can contain details about Bank Account or Manual Payment rather than Credit Card.
	 *
	 * @param 	$payment 	array
	 * 
	 * @return 	string 
	 **/
	public static function getBookingSubmitPaymentLog($payment) 
	{
		$text = '';
		$cc = '';
		$cc_hidden = '';
		//detect channel
		$channel = 'tripconnect';
		if (isset($payment['method'])) {
			$channel = 'trivago';
		}

		//detect credit card array value
		if ($channel == 'tripconnect') {
			$cc = $payment['card_number'];
		} else {
			//trivagoEB may not contain information about the credit card
			if ($payment['method']['code'] == 'PaymentCard' && isset($payment['parameters']['card_number'])) {
				$cc = $payment['parameters']['card_number'];
			}
		}
		
		//split and mask credit card number
		if (!empty($cc)) {
			$cc_num_len = strlen($cc);
			if ($cc_num_len == 14) {
				// Diners Club
				$cc_hidden .= substr($cc, 0, 4)." **** **** **";
			} elseif ($cc_num_len == 15) {
				// American Express
				$cc_hidden .= "**** ****** ".substr($cc, 10, 5);
			} else {
				// Master Card, Visa, Discover, JCB
				$cc_hidden .= "**** **** **** ".substr($cc, 12, 4);
			}
		}
		
		//compose log message
		if ($channel == 'tripconnect') {
			$text = 'Credit Card: '.$payment['card_type']."\n\nCardholder Name: ".$payment['cardholder_name']."\n\n".$cc_hidden." (sent via eMail)\n\nCVV: ".$payment['cvv']."\n\nValid Thru: ".$payment['expiration_month']."/".$payment['expiration_year']."\n\n";
		} elseif ($channel == 'trivago' && !empty($cc)) {
			$text = 'Credit Card: '.$payment['method']['options'][0]['code']."\n\nCardholder Name: ".$payment['parameters']['cardholder_name']."\n\n".$cc_hidden." (sent via eMail)\n\nCVV: ".(isset($payment['parameters']['cvv']) ? $payment['parameters']['cvv'] : '')."\n\nValid Thru: ".$payment['parameters']['expiration_month']."/".$payment['parameters']['expiration_year']."\n\n";
		} elseif ($channel == 'trivago' && empty($cc)) {
			//check if we can return some logs for the non-credit-card payment
			if (isset($payment['parameters'])) {
				foreach ($payment['parameters'] as $k => $v) {
					$text .= ucwords(str_replace('_', ' ', $k)).": ".$v."\n";
				}
				$text .= "\n";
			}
		}
		
		if (isset($payment['billing_address'])) {
			foreach ($payment['billing_address'] as $k => $v) {
				$text .= ucwords(str_replace('_', ' ', $k)).": ".$v."\n";
			}
		}
		
		return rtrim($text, "\n");
	}

	// MAIL CONTENTS
	/**
	 * args - array()
	 * 'latest_version' - string
	 * 'required' - boolean
	 */
	public static function getNewVersionMailContent($args) 
	{
		$uri = VCMFactory::getPlatform()->getUri()->admin('index.php?option=com_vikchannelmanager');

		$html = "A new version of VikChannelManager was released.\nPlease execute the update from the following link:\n\n$uri";

		if (!empty($args['message'])) {
			$html .= "\n\n> " . $args['message'];
		}

		if (isset($args['updated'])) {
			if ($args['updated']) {
				$html .= "\n\nThe update was automatically and successfully installed.";
			} else {
				$html .= "\n\nThe automatic update system could not install the latest update.";

				if (!empty($args['error'])) {
					$html .= ' ' . $args['error'];
				}
			}
		}

		if (empty($args['updated']) && !empty($args['required'])) {
			$html .= "\n\nVikChannelManager has been blocked for security reasons. Please proceed with a manual update to restart using it.";
		}

		return $html;
	}
	
	/**
	 * This method splits and masks a part of the credit card details received
	 * from TripConnect Instant Booking or from Trivago Express Booking.
	 * The method returns the string to be sent via email to the administrator,
	 * that may contain the remaining part of the credit card number.
	 * The other part of the credit card number is stored in the paymentlogs (method "getBookingSubmitPaymentLog").
	 * For trivago Express Booking, the payment method has a different structure,
	 * and can contain details about Bank Account or Manual Payment rather than Credit Card.
	 *
	 * @param 	$args 	array
	 * 
	 * @return 	string 
	 **/
	public static function getBookingSubmitCCMailContent($args) 
	{
		$cc = '';
		$cc_hidden = '';
		//detect channel
		$mail_content = 'VCMTACNEWORDERMAILCONTENT';
		if ($args['channel'] == 'trivago') {
			$mail_content = 'VCMTRINEWORDERMAILCONTENT';
		}

		//detect credit card array value
		if ($args['channel'] != 'trivago') {
			$cc = $args['payment_method']['card_number'];
		} else {
			//trivagoEB may not contain information about the credit card
			if ($args['payment_method']['method']['code'] == 'PaymentCard' && isset($args['payment_method']['parameters']['card_number'])) {
				$cc = $args['payment_method']['parameters']['card_number'];
			}
		}

		//split and mask credit card number
		if (!empty($cc)) {
			$cc_num_len = strlen($cc);
			if ($cc_num_len == 14) {
				// Diners Club
				$app = "****".substr($cc, 4, 10);
				for ($i = 1; $i <= $cc_num_len; $i++) {
					$cc_hidden .= $app[$i-1].($i%4 == 0 ? ' ':'');
				}
			} elseif ($cc_num_len == 15) {
				// American Express
				$app = substr($cc, 0, 10)."*****";
				for ($i = 1; $i <= $cc_num_len; $i++) {
					$cc_hidden .= $app[$i-1].($i==4 || $i==10 ? ' ':'');
				}
			} else {
				// Master Card, Visa, Discover, JCB
				$app = substr($cc, 0, 12)."****";
				for ($i = 1; $i <= $cc_num_len; $i++) {
					$cc_hidden .= $app[$i-1].($i%4 == 0 ? ' ':'');
				}
			}
		} elseif ($args['channel'] == 'trivago' && empty($cc)) {
			//check if we can return some logs for the non-credit-card payment
			if (isset($args['payment_method']['parameters'])) {
				foreach ($args['payment_method']['parameters'] as $k => $v) {
					$cc_hidden .= ucwords(str_replace('_', ' ', $k)).": ".$v."\n";
				}
			}
		}
		
		$cust_info = '';
		foreach ($args['customer_info'] as $k => $v) {
			$cust_info .= ucwords(str_replace('_', ' ', $k)).': '.$v."\n";
		}
		
		$admin_url = JUri::root().'administrator/index.php?option=com_vikbooking&task=editorder&cid[]='.$args['response']['id'].'#paymentlog';
		$front_url = $args['response']['orderlink'];
		
		return JText::sprintf($mail_content, 
			"#".$args['response']['id'],
			JText::_('VCMTACNEWORD'.strtoupper($args['response']['status']).'STATUS'),
			$args['start_date'],
			$args['end_date'],
			$cust_info,
			$cc_hidden,
			$admin_url."\n\n".$front_url
		);
	}

	public static function parseNotificationHotelId($notif_cont, $cha_id, $ret_first = false) 
	{
		$first_hid = '';
		preg_match_all('/\{hotelid ([a-zA-Z0-9]+)\}/U', $notif_cont, $matches);
		if (is_array($matches[1]) && count($matches[1]) > 0) {
			$hids = array();
			$hname_map = array();
			foreach ($matches[1] as $hid) {
				$hids[] = $hid;
			}
			$dbo = JFactory::getDbo();
			$q = "SELECT `prop_name`,`prop_params`  FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel` = ".(int)$cha_id.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$all_rooms = $dbo->loadAssocList();
				foreach ($all_rooms as $room) {
					if (!empty($room['prop_params']) && !empty($room['prop_name'])) {
						$prop_info = json_decode($room['prop_params'], true);
						if (is_array($prop_info)) {
							if (isset($prop_info['hotelid'])) {
								$hname_map[$prop_info['hotelid']] = $room['prop_name'];
							} elseif (isset($prop_info['id'])) {
								// useful for Pitchup.com to identify multiple accounts
								$hname_map[$prop_info['id']] = $room['prop_name'];
							} elseif (isset($prop_info['apikey'])) {
								// useful for Pitchup.com, but it may be a good backup field for future channels to identify multiple accounts
								$hname_map[$prop_info['apikey']] = $room['prop_name'];
							} elseif (isset($prop_info['property_id'])) {
								// useful for Hostelworld
								$hname_map[$prop_info['property_id']] = $room['prop_name'];
							} elseif (isset($prop_info['user_id'])) {
								// useful for Airbnb API
								$hname_map[$prop_info['user_id']] = $room['prop_name'];
							}
						}
					}
				}
			}
			foreach ($hids as $k => $hid) {
				if (array_key_exists($hid, $hname_map)) {
					$notif_cont = str_replace('{hotelid '.$hid.'}', $hname_map[$hid], $notif_cont);
					$first_hid = (!($k > 0) ? $hname_map[$hid] : $first_hid);
				} else {
					$notif_cont = str_replace('{hotelid '.$hid.'}', 'Account ID '.$hid, $notif_cont);
					$first_hid = (!($k > 0) ? 'Account ID '.$hid : $first_hid);
				}
			}
		}
		return $ret_first ? $first_hid : $notif_cont;
	}

	public static function parseRestrictionsCtad($ctad) 
	{
		$ct = array();
		if (!is_array($ctad) || !(count($ctad))) {
			return $ct;
		}
		foreach ($ctad as $k => $v) {
			$ct[$k] = intval(str_replace('-', '', $v));
		}
		sort($ct);
		return $ct;
	}

	/**
	 * Returns a date formatted depending on the current time.
	 *
	 * @param 	string|JDate 	$date 		The date to format.
	 *
	 * @return 	string 	The formatted date.
	 */
	public static function formatDate($date)
	{
		// if date is an instance of JDate, convert it in a string
		if ($date instanceof JDate)
		{
			$date = $date->toSql();
		}

		// if the date is not numeric, get its timestamp
		if (!is_numeric($date))
		{
			$ts = strtotime($date);
		}
		else 
		{
			$ts = (int) $date;
		}

		// get the current UTC timestamp
		$now = strtotime(JDate::getInstance()->toSql());

		// get the difference between the 2 timestamps
		$diff = $now - $ts;

		// check if the difference is almost now
		if (abs($diff) < 60)
		{
			return JText::_('DFNOW');
		}

		// check if the difference is less than an hour
		$minutes = $diff / 60;
		if (abs($minutes) < 60)
		{
			return JText::sprintf('DFMINS' . ($diff > 0 ? 'AGO' : 'AFT'), floor(abs($minutes)));
		}
		
		// check if the difference is less than a day
		$hours = $minutes / 60;
		if (abs($hours) < 24)
		{
			$hours = floor($hours);
			
			if (abs($hours) == 1) {
				return JText::_('DFHOUR' . ($diff > 0 ? 'AGO' : 'AFT'));
			}

			return JText::sprintf('DFHOURS'.($diff > 0 ? 'AGO' : 'AFT'), abs($hours));
		}
		
		// check if the difference is less than a week
		$days = $hours / 24;
		if (abs($days) < 7)
		{
			$days = floor($days);

			if (abs($days) == 1) {
				return JText::_('DFDAY' . ($diff > 0 ? 'AGO' : 'AFT'));
			}

			return JText::sprintf('DFDAYS' . ($diff > 0 ? 'AGO' : 'AFT'), abs($days));
		}

		// check if the difference is some exact weeks, unless more than 100 days
		$days = floor($days);
		if ((abs($days) % 7) == 0 && abs($days) < 100) {
			if (abs($days) == 7) {
				return JText::_('DFWEEK' . ($diff > 0 ? 'AGO' : 'AFT'));
			}
			return JText::sprintf('DFWEEKS' . ($diff > 0 ? 'AGO' : 'AFT'), (abs($days) / 7));
		}

		// if in the future, and in less than 100 days, return the number of days
		if ($diff < 0 && abs($days) < 100) {
			return JText::sprintf('DFDAYSAFT', abs($days));
		}
		
		// no short format, return the full date
		return $date;
	}

	public static function getJoomlaUserGroups()
	{
		/**
		 * @wponly  need to get the roles as an associative array with the same structure
		 */

		$roles = array();

		foreach (wp_roles()->roles as $slug => $role)
		{
			array_push($roles, array(
				'id' 		=> $slug,
				'title' 	=> $role['name'],
				'parent_id' => 0
			));
		}

		return $roles;
	}

	public static function getRecursiveUserLevel($id_parent, $groups = null, $level = 0)
	{
		/**
		 * @wponly  no user level needed or supported
		 */

		return 0;
	}

	public static function getDefaultJoomlaUserGroup($firstAccount = false)
	{
		/**
		 * @wponly  roles are different
		 */
		if ($firstAccount)
		{
			return 'administrator';
		}
		
		return get_option('default_role');
	}

	/**
	 * Updates the manifest cache.
	 *
	 * @param 	[string] 	$newversion
	 *
	 * @return 	boolean
	 */
	public static function updateManifestCacheVersion($newversion = '')
	{
		if (empty($newversion)) {
			$newversion = VIKCHANNELMANAGER_SOFTWARE_VERSION;
		}

		/**
		 * @wponly  we need to update the option record
		 */
		update_option('vikchannelmanager_software_version', $newversion);

		return true;
	}

	/**
	 * This method should be called by the usort() function
	 * to sort the seasons by "from_ts" and by "duration_ts".
	 * These two keys must exist in the array passed to usort()
	 * so that we can make a comparison of two factors.
	 * This is used for the RatesPushSubmit view of the Bulk Action
	 * to compose the nodes of the RAR_RQ correctly.
	 *
	 * @param 	array 	$a
	 * @param 	array 	$b
	 *
	 * @return 	int
	 */
	public static function compareSeasonsDatesDurations($a, $b)
	{
		if ($a['from_ts'] == $b['from_ts']) {
			//lowest duration goes after a higher duration for comparison of large intervals.
			return $a['duration_ts'] < $b['duration_ts'] ? 1 : -1;
		}
		return $a['from_ts'] > $b['from_ts'] ? 1 : -1;
	}

	/**
	 * Returns a list of the installed languages.
	 * 
	 * @return 	array
	 * 
	 * @since 	1.6.8
	 */
	public static function getKnownLanguages()
	{
		if (class_exists('VikApplication')) {
			$vik = new VikApplication(VersionListener::getID());
			if (method_exists($vik, 'getKnownLanguages')) {
				return $vik->getKnownLanguages();
			}
		}

		return array();
	}

	public static function getVikBookingConnectorInstance()
	{
		if (!class_exists('VikBookingConnector')) {
			require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'vbo.connector.php');
		}
		return new VikBookingConnector;
	}

	public static function getSmartBalancerInstance()
	{
		if (!class_exists('SmartBalancer')) {
			require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'smartbalancer.php');
		}
		return new SmartBalancer;
	}

	public static function getFestivitiesInstance()
	{
		if (!class_exists('VCMFestivities')) {
			require_once(VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'festivities.php');
		}

		/**
		 * The class now allows to get the Singletone instance for VBO.
		 * 
		 * @since 	1.6.13
		 */
		return VCMFestivities::getInstance();
	}

	/**
	 * Returns an instance of the Opportunity class handler.
	 * 
	 * @return 	VCMOpportunityHandler
	 * 
	 * @since 	1.6.13
	 */
	public static function getOpportunityInstance()
	{
		if (!class_exists('VCMOpportunityHandler')) {
			require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'opportunity.php');
		}

		return VCMOpportunityHandler::getInstance();
	}

	/**
	 * Returns an instance of the Currency Converter helper class.
	 * 
	 * @return 	VCMCurrencyConverter
	 * 
	 * @since 	1.8.3
	 */
	public static function getCurrencyConverterInstance()
	{
		if (!class_exists('VCMCurrencyConverter')) {
			require_once(VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'currency_converter.php');
		}

		return VCMCurrencyConverter::getInstance();
	}

	/**
	 * Returns an instance of the Rates Flow helper class.
	 * 
	 * @return 	VCMRatesFlow
	 * 
	 * @since 	1.8.3
	 */
	public static function getRatesFlowInstance()
	{
		if (!class_exists('VCMRatesFlow')) {
			require_once(VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'rates_flow.php');
		}

		return VCMRatesFlow::getInstance();
	}

	/**
	 * Returns the Vik Channel Manager Logos Object.
	 * 
	 * @param 	string 	$source 	the provenience of the reservation
	 * 
	 * @return 	VikChannelManagerLogos
	 * 
	 * @since 	1.6.8
	 */
	public static function getLogosInstance($source = '')
	{
		if (!class_exists('VikChannelManagerLogos')) {
			require_once(VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'logos.php');
		}
		return new VikChannelManagerLogos($source);
	}

	/**
	 * Returns the Reservations Logger Object.
	 * 
	 * @return 	VcmReservationsLogger
	 * 
	 * @since 	1.6.8
	 */
	public static function getResLoggerInstance()
	{
		if (!class_exists('VcmReservationsLogger')) {
			require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'reslogger.php');
		}
		return new VcmReservationsLogger();
	}

	/**
	 * Returns the VikBooking Application object.
	 * May require an updated version of Vik Booking (J>=1.13 - WP>=1.3.0).
	 * Used to render for example a media-manager-field to pick an image.
	 * 
	 * @return 	mixed 	VboApplication on success, false otherwise.
	 * 
	 * @since 	1.7.0
	 */
	public static function getVboApplication()
	{
		$path_to_vbo_app = VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'jv_helper.php';
		if (!class_exists('VboApplication') && is_file($path_to_vbo_app)) {
			require_once($path_to_vbo_app);
		}
		return class_exists('VboApplication') ? new VboApplication() : false;
	}

	/**
	 * Sorts the Expedia rate plans by pushing the Derived ones at the bottom of the list.
	 * 
	 * @param 	array 	$pricing 	the channel pricing json-decoded array
	 * 
	 * @return 	array
	 * 
	 * @since 	1.6.13
	 * @since 	1.8.4 	introduced new sorting method by several factors.
	 */
	public static function sortExpediaChannelPricing($pricing)
	{
		if (!is_array($pricing) || !count($pricing)) {
			return $pricing;
		}

		$channel_pricing = array();
		foreach ($pricing as $chpk => $rateplans) {
			if ($chpk == 'RatePlan') {
				// rate plans container found
				$channel_pricing = $rateplans;
				break;
			}
		}
		if (!count($channel_pricing)) {
			return $pricing;
		}

		foreach ($channel_pricing as $rpid => $rateplan) {
			// count the sorting-score of this rate plan
			$sort_score = 0;
			if (isset($rateplan['rateAcquisitionType']) && stripos($rateplan['rateAcquisitionType'], 'Derived') === false) {
				// parent/standalone/sell rate plan, not a derived rate plan
				$sort_score += 3;
			} else {
				// derived rate plans get the lowest score
				$sort_score -= 2;
			}
			if (isset($rateplan['type']) && stripos($rateplan['type'], 'Package') === false) {
				// probably not a rate plan with a linkage rule
				$sort_score += 2;
			}
			if (isset($rateplan['status']) && !strcasecmp($rateplan['status'], 'inactive')) {
				// rate plan is inactive
				$sort_score -= 1;
			}
			if (stripos($rateplan['name'], 'Standard') !== false) {
				// "standard rate" rate plans should go first
				$sort_score++;
			}
			// set sorting score for this rate plan ID
			$channel_pricing[$rpid]['sort_score'] = $sort_score;
		}

		// apply custom sorting and keep index association
		uasort($channel_pricing, function($a, $b) {
			return $b['sort_score'] - $a['sort_score'];
		});

		// update RatePlan key
		$pricing['RatePlan'] = $channel_pricing;

		return $pricing;
	}

	/**
	 * Sorts the channel rate plans by pushing non refundable rates at the bottom of the list.
	 * 
	 * @param 	array 	$pricing 	the channel pricing json-decoded array
	 * 
	 * @return 	array
	 * 
	 * @since 	1.8.4
	 */
	public static function sortGenericChannelPricing($pricing)
	{
		if (!is_array($pricing) || !count($pricing)) {
			return $pricing;
		}

		$channel_pricing = array();
		foreach ($pricing as $chpk => $rateplans) {
			if ($chpk == 'RatePlan') {
				// rate plans container found
				$channel_pricing = $rateplans;
				break;
			}
		}
		if (!count($channel_pricing)) {
			return $pricing;
		}

		foreach ($channel_pricing as $rpid => $rateplan) {
			if (!isset($rateplan['name'])) {
				// do not proceed in case of a weird array structure
				return $pricing;
			}
			// count the sorting-score of this rate plan
			$sort_score = 0;
			if (stripos($rateplan['name'], 'Standard') !== false || stripos($rateplan['name'], 'Base') !== false) {
				// "standard rate" rate plans should go first
				$sort_score += 3;
			} else {
				$sort_score--;
			}
			if (stripos($rateplan['name'], 'Non') === false && stripos($rateplan['name'], 'Not') === false) {
				// this doesn't seem to be a "non refundable rate" so it should go first
				$sort_score += 2;
			}

			// set sorting score for this rate plan ID
			$channel_pricing[$rpid]['sort_score'] = $sort_score;
		}

		// apply custom sorting and keep index association
		uasort($channel_pricing, function($a, $b) {
			return $b['sort_score'] - $a['sort_score'];
		});

		// update RatePlan key
		$pricing['RatePlan'] = $channel_pricing;

		return $pricing;
	}

	/**
	 * This method overrides the default occupancy pricing rules for the website
	 * by using the same charges/discounts rules set up from the Bulk Action to
	 * increase the website rates to be transmitted to the channels. This is valid
	 * only for the occupancy pricing rules expressed with absolute values, not as
	 * percent values. For example, if 3 adults should pay €30 more per night, and
	 * if the rooms costs are increased by 18%, the occupancy pricing rule for 3
	 * adults will be overridden to +€35,40 just for the channels. In case 1 adult
	 * should pay €40 less, negative numbers will be lowered if there's a charge
	 * for example of +18%, and so it will be come €33,90 less.
	 * This function is enabled by ticking the apposite checkbox in the Advanced
	 * Parameters available in the page Bulk Actions - Rates Upload.
	 * 
	 * @param 	array 	$diffusageprice 	the current occupancy pricing rules of VBO
	 * 										for a specific room and number of adults.
	 * @param 	int 	$rmodop 			increase/decrease rates for channels.
	 * @param 	int 	$rmodval 			percent or absolute value.
	 * @param 	float 	$rmodamount 		the actual modifier-value for the channels.
	 * 
	 * @return 	array 	the modified array containing the different adult-usage pricing.
	 * 
	 * @since 	1.6.17
	 */
	public static function alterRoomOccupancyPricingRules($diffusageprice, $rmodop, $rmodval, $rmodamount)
	{
		if (!is_array($diffusageprice) || !count($diffusageprice)) {
			return $diffusageprice;
		}

		if ($diffusageprice['chdisc'] == 1 && $diffusageprice['valpcent'] == 1) {
			// occupancy charge, fixed value. Check how to alter the value
			if (intval($rmodop) > 0 && intval($rmodop) < 2) {
				// increase rates
				if (intval($rmodval) > 0) {
					// percent charge
					$diffusageprice['value'] = $diffusageprice['value'] * (100 + (float)$rmodamount) / 100;
				} else {
					// fixed charge
					$diffusageprice['value'] += (float)$rmodamount;
				}
			} elseif (intval($rmodop) <= 0) {
				// lower rates
				if (intval($rmodval) > 0) {
					// percent discount
					$disc_op = $diffusageprice['value'] * (float)$rmodamount / 100;
					$diffusageprice['value'] -= $disc_op;
				} else {
					// fixed discount
					$diffusageprice['value'] -= (float)$rmodamount;
				}
			}
		} elseif ($diffusageprice['chdisc'] != 1 && $diffusageprice['valpcent'] == 1) {
			// occupancy discount, fixed value. Check how to alter the value
			if (intval($rmodop) > 0 && intval($rmodop) < 2) {
				// increase rates (so we need to lower the value to get a lower discount for the channels)
				if (intval($rmodval) > 0) {
					// percent charge
					$diffusageprice['value'] = $diffusageprice['value'] / ((100 + (float)$rmodamount) / 100);
				} else {
					// fixed charge
					$diffusageprice['value'] -= (float)$rmodamount;
				}
			} elseif (intval($rmodop) <= 0) {
				// lower rates (so we need to increase the value to get a higher discount for the channels)
				if (intval($rmodval) > 0) {
					// percent discount
					$disc_op = $diffusageprice['value'] * (float)$rmodamount / 100;
					$diffusageprice['value'] += $disc_op;
				} else {
					// fixed discount
					$diffusageprice['value'] += (float)$rmodamount;
				}
			}
		}

		// make sure we did not cause a negative value
		$diffusageprice['value'] = $diffusageprice['value'] < 0 ? 0 : $diffusageprice['value'];

		return $diffusageprice;
	}

	/**
	 * Cleans up some extra words added to channels like
	 * A-Hotels.com, A-Expedia, A-Expedia Affiliate Network...
	 *
	 * @param 	$source  string
	 *
	 * @return 	string
	 * 
	 * @since 	1.7.1
	 */
	protected static function clearSourceName($source)
	{
		$lookup = array(
			'a-expedia' 					=> 'Expedia',
			'a-expedia affiliate network' 	=> 'Expedia',
			'a-hotels.com' 					=> 'Hotels.com',
		);

		foreach ($lookup as $match => $val)
		{
			if (stripos($source, $match) !== false)
			{
				return $val;
			}
		}

		return $source;
	}

	/**
	 * Helper method to generate the reports. Will run once per day at most,
	 * and it should be called at any execution of VCM.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.7.1
	 */
	public static function notifyReportsData()
	{
		$config = VCMFactory::getConfig();

		// load configuration setting to check the last check time
		$last_report_ymd = $config->get('last_report_ymd', '');

		if ($last_report_ymd == date('Y-m-d')) {
			return;
		}

		try {
			self::fetchReportsData(true);
		} catch (Exception $e) {
			// do nothing
		}

		// update last check time
		$config->set('last_report_ymd', date('Y-m-d'));
	}

	/**
	 * Main method to generate the reports data. Triggered also by the front-end controller.
	 * 
	 * @param 	boolean 	$send 		whether the send the reports via email.
	 * @param 	string 		$date_from 	the optional starting from date.
	 * @param 	string 		$date_to 	the optional end date.
	 * @param 	string 		$date_type 	what type of bookings to look for.
	 * 
	 * @return 	mixed 					void or array
	 * 
	 * @since 	1.7.1
	 */
	public static function fetchReportsData($send = false, $date_from = '', $date_to = '', $date_type = 'bookings')
	{
		$dbo = JFactory::getDbo();

		$reports_interval = VCMFactory::getConfig()->get('reports_interval', null);
		$reports_interval = $reports_interval !== null ? (int)$reports_interval : 30;
		
		// request filters
		if (!in_array($date_type, array('arrivals', 'departures', 'stayovers', 'bookings'))) {
			throw new Exception('Invalid date type', 500);
		}

		// load front-end language
		$lang = JFactory::getLanguage();
		$lang->load('com_vikchannelmanager', VIKCHANNELMANAGER_SITE_LANG, $lang->getTag(), true);

		// dates comparison array
		$dates_compare = array();

		if (empty($date_from) && empty($date_to)) {
			// check the configuration settings to see how often reports should be received (defaults to 30)
			if ($reports_interval <= 0) {
				throw new Exception('Reports have been disabled.', 500);
			}

			/**
			 * Prepare the timezone to be always in UTC
			 * 
			 * @since 	1.6.18
			 */
			$current_tz = date_default_timezone_get();
			date_default_timezone_set('UTC');
			
			// get current time information
			$now_info = getdate();
			$cur_wk = date('W');

			/**
			 * Restore the default timezone
			 * 
			 * @since 	1.6.18
			 */
			date_default_timezone_set($current_tz);

			// validate day of report
			$needs_report = false;

			switch ($reports_interval) {
				case 7:
					if ((int)$now_info['wday'] === 1) {
						// it's Monday, we can do the report
						$needs_report = true;
						// build dates for comparison
						$last_monday = mktime(0, 0, 0, $now_info['mon'], ($now_info['mday'] - 7), $now_info['year']);
						$prev_sunday = mktime(23, 59, 59, $now_info['mon'], ($now_info['mday'] - 1), $now_info['year']);
						// push very last week
						array_push($dates_compare, array(
							'date_from' => $last_monday,
							'date_to' 	=> $prev_sunday,
						));
						// get dates of two weeks ago
						$twoago_monday = mktime(0, 0, 0, $now_info['mon'], ($now_info['mday'] - 14), $now_info['year']);
						$twoago_sunday = mktime(23, 59, 59, $now_info['mon'], ($now_info['mday'] - 8), $now_info['year']);
						// push two weeks ago
						array_push($dates_compare, array(
							'date_from' => $twoago_monday,
							'date_to' 	=> $twoago_sunday,
						));
					}
					break;
				case 14:
					if ((int)$now_info['wday'] === 1 && ((int)$cur_wk % 2) == 0) {
						// it's Monday of an even week, we can do the report
						$needs_report = true;
						// build dates for comparison
						$twoago_monday  = mktime(0, 0, 0, $now_info['mon'], ($now_info['mday'] - 14), $now_info['year']);
						$prev_sunday 	= mktime(23, 59, 59, $now_info['mon'], ($now_info['mday'] - 1), $now_info['year']);
						// push last two weeks
						array_push($dates_compare, array(
							'date_from' => $twoago_monday,
							'date_to' 	=> $prev_sunday,
						));
						// get dates of four weeks ago
						$fourago_monday = mktime(0, 0, 0, $now_info['mon'], ($now_info['mday'] - 28), $now_info['year']);
						$twoago_sunday  = mktime(23, 59, 59, $now_info['mon'], ($now_info['mday'] - 15), $now_info['year']);
						// push two weeks ago
						array_push($dates_compare, array(
							'date_from' => $fourago_monday,
							'date_to' 	=> $twoago_sunday,
						));
					}
					break;
				default:
					// monthly (30)
					if ((int)$now_info['mday'] === 1) {
						// it's the first day of the month, we can do the report
						$needs_report = true;
						// build dates for comparison
						$last_month_start = mktime(0, 0, 0, ($now_info['mon'] - 1), 1, $now_info['year']);
						$last_month_end   = mktime(23, 59, 59, ($now_info['mon'] - 1), date('t', $last_month_start), $now_info['year']);
						// push last month
						array_push($dates_compare, array(
							'date_from' => $last_month_start,
							'date_to' 	=> $last_month_end,
						));
						// get two months ago
						$twoago_month_start = mktime(0, 0, 0, ($now_info['mon'] - 2), 1, $now_info['year']);
						$twoago_month_end   = mktime(23, 59, 59, ($now_info['mon'] - 2), date('t', $twoago_month_start), $now_info['year']);
						// push last month
						array_push($dates_compare, array(
							'date_from' => $twoago_month_start,
							'date_to' 	=> $twoago_month_end,
						));
					}
					break;
			}

			if (!$needs_report) {
				throw new Exception('Reports cannot be generated at the moment. Interval set to ' . $reports_interval . ' days.', 500);
			}
		} else {
			// standard external request to query the reports data
			array_push($dates_compare, array(
				'date_from' => $date_from,
				'date_to' 	=> $date_to,
			));
		}

		// always reverse the dates comparison array so that the oldest range will be first
		$dates_compare = array_reverse($dates_compare);

		/**
		 * the array $reports will be a list of report objects containing
		 * the information about the date range and the data collected.
		 */
		$reports = array();

		foreach ($dates_compare as $dates) {
			// calculate dates values
			$date_start_ts 	= strpos($dates['date_from'], '-') !== false ? strtotime($dates['date_from']) : $dates['date_from'];
			$date_start_ts 	= !$date_start_ts ? mktime(0, 0, 0, date('n'), date('j'), date('Y')) : $date_start_ts;
			$date_end_ts 	= strpos($dates['date_to'], '-') !== false ? strtotime($dates['date_to']) : $dates['date_to'];
			$date_end_ts 	= !$date_end_ts ? mktime(23, 59, 59, date('n'), date('j'), date('Y')) : $date_end_ts;
			$date_end_info 	= getdate($date_end_ts);
			$date_end_ts 	= mktime(23, 59, 59, $date_end_info['mon'], $date_end_info['mday'], $date_end_info['year']);

			$clauses = array("`o`.`closure` = 0", "`o`.`status` = 'confirmed'");
			if ($date_type == 'arrivals') {
				$dtype = "`o`.`checkin`";
				$otype = "`o`.`checkin` ASC";
				$clauses[] = "$dtype >= $date_start_ts";
				$clauses[] = "$dtype <= $date_end_ts";
			} elseif ($date_type == 'departures') {
				$dtype = "`o`.`checkout`";
				$otype = "`o`.`checkout` ASC";
				$clauses[] = "$dtype >= $date_start_ts";
				$clauses[] = "$dtype <= $date_end_ts";
			} elseif ($date_type == 'stayovers') {
				$otype = "`o`.`checkin` ASC";
				$clauses[] = "`o`.`checkin` < $date_start_ts";
				$clauses[] = "`o`.`checkout` > $date_end_ts";
			} else {
				$dtype = "`o`.`ts`";
				$otype = "`o`.`id` DESC";
				$clauses[] = "$dtype >= $date_start_ts";
				$clauses[] = "$dtype <= $date_end_ts";
			}

			// load all the bookings according to the params
			$q = "SELECT `o`.*,
				(
					SELECT CONCAT_WS(' ',`or`.`t_first_name`,`or`.`t_last_name`) 
					FROM `#__vikbooking_ordersrooms` AS `or` 
					WHERE `or`.`idorder` = `o`.`id` LIMIT 1
				) AS `nominative`,
				(
					SELECT SUM(`or`.`adults`) 
					FROM `#__vikbooking_ordersrooms` AS `or` 
					WHERE `or`.`idorder` = `o`.`id`
				) AS `tot_adults`,
				(
					SELECT GROUP_CONCAT(`r`.`name` SEPARATOR ',') 
					FROM `#__vikbooking_rooms` AS `r` 
					LEFT JOIN `#__vikbooking_ordersrooms` AS `or` ON `or`.`idroom` = `r`.`id` 
					WHERE `or`.`idorder` = `o`.`id`
				) AS `room_names`,
				(
					SELECT SUM(`or`.`children`) 
					FROM `#__vikbooking_ordersrooms` AS `or` 
					WHERE `or`.`idorder` = `o`.`id`
				) AS `tot_children` 
				FROM `#__vikbooking_orders` AS `o` 
				WHERE ".implode(' AND ', $clauses)." 
				ORDER BY $otype;";
			$dbo->setQuery($q);
			$bookingsData = $dbo->loadAssocList();

			$bookings = array();

			foreach ($bookingsData as $book) {
				$book['ts'] = date('Y-m-d H:i', $book['ts']);
				$book['checkin'] = date('Y-m-d H:i', $book['checkin']);
				$book['checkout'] = date('Y-m-d H:i', $book['checkout']);
				if (empty($book['nominative'])) {
					$cust_data_lines = explode("\n", $book['custdata']);
					$first_cust_info = explode(":", $cust_data_lines[0]);
					$book['nominative'] = count($first_cust_info) > 1 ? $first_cust_info[1] : $first_cust_info[0];
				}
				$book['source'] = 'VBO';
				if (!empty($book['channel']) && $book['channel'] != 'Channel Manager') {
					$source = explode('_', $book['channel']);
					$source = count($source) > 1 ? $source[1] : $source[0];
					$book['source'] = self::clearSourceName($source);
				}

				array_push($bookings, (object)$book);
			}

			if (!$bookings) {
				if (count($dates_compare) === 1 && !$send) {
					// return empty response
					return $bookings;
				}
				// go to next loop
				continue;
			}

			// get currency name/symbol
			$currency = VikBooking::getCurrencySymb();

			// we generate the report data to build the email message
			$sources = array();
			foreach ($bookings as $booking) {
				if (!isset($sources[$booking->source])) {
					// get channel source logo
					$logo_url = self::getLogosInstance($booking->source)->getLogoURL();
					// set default values
					$sources[$booking->source] = new stdClass;
					$sources[$booking->source]->channel_logo_url   = !$logo_url ? self::getBackendLogoFullPath() : $logo_url;
					$sources[$booking->source]->currency 		   = $currency;
					$sources[$booking->source]->reservations_count = 0;
					$sources[$booking->source]->reservations_total = 0;
					$sources[$booking->source]->reservations_cmms  = 0;
					$sources[$booking->source]->reservations_list  = [];
				}
				// increase values
				$sources[$booking->source]->reservations_count++;
				$sources[$booking->source]->reservations_total += $booking->total;
				$sources[$booking->source]->reservations_cmms  += $booking->cmms;
				// lighten the content of the reservation variables
				$sources[$booking->source]->reservations_list[] = array_filter((array) $booking, function($key) {
					// exclude heavy properties
					return !in_array($key, [
						'custdata',
						'custmail',
						'phone',
						'confirmnumber',
						'paymentlog',
						'adminnotes',
						'inv_notes',
						'colortag',
						'checked',
						'coupon',
						'ujid',
						'idpayment',
					]);
				}, ARRAY_FILTER_USE_KEY);
			}

			// push the results in the requested range of dates
			if ($sources) {
				$report_obj = new stdClass;
				$report_obj->date_from  = $date_start_ts;
				$report_obj->date_to 	= $date_end_ts;
				$report_obj->data 		= $sources;
				// push the values
				array_push($reports, $report_obj);
			}
		}

		if (!$send || !$reports) {
			// return the response
			return $reports;
		}

		// format the ranges of dates by channel and calculate its report details
		$channels_data = array();
		$channels_info = array();
		foreach ($reports as $ind => $report) {
			foreach ($report->data as $chname => $chdata) {
				if (!isset($channels_data[$chname])) {
					$channels_data[$chname] = array();
					$channels_info[$chname] = new stdClass;
					$channels_info[$chname]->channel_logo_url = $chdata->channel_logo_url;
				}
				$channels_data[$chname][$ind] = new stdClass;
				$channels_data[$chname][$ind]->currency 			= $currency;
				$channels_data[$chname][$ind]->reservations_count 	= $chdata->reservations_count;
				$channels_data[$chname][$ind]->reservations_total 	= $chdata->reservations_total;
				$channels_data[$chname][$ind]->reservations_cmms 	= $chdata->reservations_cmms;
			}
		}
		$tot_intervals = count($reports);
		$interval_keys = array_keys($reports);
		foreach ($channels_data as $chname => $report_ind) {
			if (count($report_ind) == $tot_intervals) {
				continue;
			}
			foreach ($interval_keys as $intk) {
				if (!isset($channels_data[$chname][$intk])) {
					$channels_data[$chname][$intk] = new stdClass;
					$channels_data[$chname][$intk]->currency 			= $currency;
					$channels_data[$chname][$intk]->reservations_count 	= 0;
					$channels_data[$chname][$intk]->reservations_total 	= 0;
					$channels_data[$chname][$intk]->reservations_cmms 	= 0;
				}
			}
		}
		// sort by key
		foreach ($channels_data as $chname => $report_ind) {
			ksort($channels_data[$chname]);
		}

		// sort a copy of the channels data by reservations count and total
		$counters_count_map = array();
		$counters_total_map = array();
		foreach ($channels_data as $chname => $report_ind) {
			foreach ($report_ind as $intk => $report_data) {
				// we always take the last index of the reports data (last date interval) as value for sorting
				$counters_count_map[$chname] = $report_data->reservations_count;
				$counters_total_map[$chname] = $report_data->reservations_total;
			}
		}
		// sort counters
		arsort($counters_count_map);
		arsort($counters_total_map);

		// compose the final array
		$channels_reports = new stdClass;
		$channels_reports->reservations_count = array();
		$channels_reports->reservations_total = array();
		foreach ($counters_count_map as $chname => $val) {
			$channels_reports->reservations_count[$chname] = $channels_data[$chname];
		}
		foreach ($counters_total_map as $chname => $val) {
			$channels_reports->reservations_total[$chname] = $channels_data[$chname];
		}

		/**
		 * Count occupancy stats with percent values for all ranges
		 */

		// count total units of rooms
		$total_rooms_units = 0;
		$q = "SELECT SUM(`units`) FROM `#__vikbooking_rooms` WHERE `avail`=1;";
		$dbo->setQuery($q);
		$total_rooms_units = (int)$dbo->loadResult();

		$occupancy_stats = array();
		foreach ($reports as $ind => $report) {
			$tot_days = 0;
			$range_max_occupancy = 0;
			$from_info = getdate($report->date_from);
			$to_info = getdate($report->date_to);
			if (date('Y-m-d', $from_info[0]) == date('Y-m-d', $to_info[0])) {
				$tot_days = 1;
			} else {
				$counter = 0;
				while ($from_info[0] <= $to_info[0]) {
					$counter++;
					// next date
					$from_info = getdate(mktime(0, 0, 0, $from_info['mon'], ($from_info['mday'] + 1), $from_info['year']));
				}
				$tot_days = $counter;
			}
			// maximum occupancy of this range is given by the days in the range times the total rooms units
			$range_max_occupancy = $tot_days * $total_rooms_units;
			$nights_booked = 0;
			foreach ($bookingsData as $book) {
				// count nights booked in the current range for this booking
				$booking_nights = 0;
				$from_info = getdate($report->date_from);
				$to_info = getdate($report->date_to);
				$in_info = getdate($book['checkin']);
				$out_info = getdate($book['checkout']);
				$book['from_ts'] = mktime(0, 0, 0, $in_info['mon'], $in_info['mday'], $in_info['year']);
				$book['to_ts'] = mktime(23, 59, 59, $out_info['mon'], ($out_info['mday'] - 1), $out_info['year']);
				$checkout_ymd = date('Y-m-d', $book['checkout']);
				while ($from_info[0] <= $to_info[0]) {
					if ($from_info[0] >= $book['from_ts'] && $from_info[0] <= $book['to_ts']) {
						// range day is inside booking dates
						if (date('Y-m-d', $from_info[0]) == $checkout_ymd) {
							// this is the check-out day, so it is not a night booked
							break;
						}
						$booking_nights++;
					}
					// next date
					$from_info = getdate(mktime(0, 0, 0, $from_info['mon'], ($from_info['mday'] + 1), $from_info['year']));
				}
				$nights_booked += $booking_nights * $book['roomsnum'];
			}
			$occupancy_stats[$ind] = ($nights_booked * 100 / $range_max_occupancy);
		}

		// render template file
		$tpl_file_path = VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "tmpl_reports.php";
		if (!is_file($tpl_file_path)) {
			throw new Exception('Template file not found', 404);
		}

		ob_start();
		include $tpl_file_path;
		$content = ob_get_contents();
		ob_end_clean();

		// we send the email message to the administrator
		$vbo_app = VikBooking::getVboApplication();
		$admail = VikBooking::getAdminMail();
		$sdmail = VikBooking::getSenderMail();
		if (!is_array($admail) && strpos($admail, ',') !== false) {
			$all_recipients = explode(',', $admail);
			foreach ($all_recipients as $k => $v) {
				if (!empty($v)) {
					// we take only the first valid email address
					$admail = $v;
					break;
				}
			}
		}

		$vbo_app->sendMail($sdmail, $sdmail, $admail, '', JText::_('VCM_REPORT_EMAIL_SUBJECT'), $content, true);
	}

	/**
	 * Gets a list of all channels supporting the promotions.
	 * 
	 * @param 	string 	$key 	the key of the handler.
	 * 
	 * @return 	mixed
	 * 
	 * @since 	1.7.1
	 */
	public static function getPromotionHandlers($key = null)
	{
		$class_path = VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'promo.php';
		if (!is_file($class_path)) {
			// prevent any errors in case an update could not create such files
			return array();
		}

		// require main class
		require_once $class_path;
		
		// get all the supported handlers
		return VikChannelManagerPromo::loadHandlers($key);
	}

	/**
	 * Gets the factors for suggesting the application of the promotions.
	 * 
	 * @param 	mixed 	$data 	some optional instructions to be passed as argument.
	 * 
	 * @return 	mixed 	false if method not available, associative array otherwise.
	 * 
	 * @since 	1.7.1
	 */
	public static function getPromotionFactors($data = null)
	{
		$class_path = VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'promo.php';
		if (!is_file($class_path)) {
			// prevent any errors in case an update could not create such files
			return false;
		}

		// require main class
		require_once $class_path;
		
		// get the factors
		return VikChannelManagerPromo::getFactors($data);
	}

	/**
	 * Helper method used to upload the given file (retrieved from $_FILES)
	 * into the specified destination.
	 *
	 * @param 	array 	$file 		An associative array with the file details.
	 * @param 	string 	$dest 		The destination path.
	 * @param 	string 	$filters 	A string (or a regex) containing the allowed extensions.
	 *
	 * @return 	array 	The uploading result.
	 *
	 * @throws  RuntimeException
	 * 
	 * @since 	1.7.2
	 */
	public static function uploadFileFromRequest($file, $dest, $filters = '*')
	{
		jimport('joomla.filesystem.file');

		$dest = rtrim($dest, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		
		if (empty($file['name']))
		{
			throw new RuntimeException('Missing file', 400);
		}

		$src = $file['tmp_name'];

		// extract file name and extension
		if (preg_match("/(.*?)(\.[0-9a-z]{2,})$/i", basename($file['name']), $match))
		{
			$filename = $match[1];
			$fileext  = $match[2];
		}
		else
		{
			// probably no extension provided
			$filename = basename($file['name']);
			$fileext  = '';
		}

		$j = '';
		
		if (file_exists($dest . $filename . $fileext))
		{
			$j = 2;

			while (file_exists($dest . $filename . '-' . $j . $fileext))
			{
				$j++;
			}

			$j = '-' . $j;
		}

		$finaldest = $dest . $filename . $j . $fileext;

		// make sure the file extension is supported
		if (!self::isFileTypeCompatible(basename($finaldest), $filters))
		{
			// extension not supported
			throw new RuntimeException(sprintf('Extension [%s] is not supported', $fileext), 400);
		}
		
		// try to upload the file
		if (!JFile::upload($src, $finaldest))
		{
			throw new RuntimeException(sprintf('Unable to upload the file [%s] to [%s]', $src, $finaldest), 500);
		}

		$file = new stdClass;
		$file->name     = $filename . $j;
		$file->ext      = ltrim($fileext, '.');
		$file->filename = basename($finaldest);
		$file->path     = $finaldest;
		
		return $file;
	}

	/**
	 * Helper method used to check whether the given file name
	 * supports one of the given filters.
	 *
	 * @param   mixed   $file     Either the file name or the uploaded file.
	 * @param   string  $filters  Either a regex or a comma-separated list of supported extensions.
	 *                            The regex must be inclusive of delimiters.
	 *
	 * @return  bool    True if supported, false otherwise.
	 * 
	 * @since   1.9.5
	 */
	public static function isFileTypeCompatible($file, $filters)
	{
		// make sure the filters query is not empty
		if (strlen($filters) == 0)
		{
			// cannot assert whether the file could be accepted or not
			return false;
		}

		// check whether all the files are accepted
		if ($filters == '*')
		{
			return true;
		}

		// use the file MIME TYPE in case of array
		if (is_array($file))
		{
			$file = $file['type'];
		}

		// check if we are dealing with a regex
		if (static::isRegex($filters))
		{
			return (bool) preg_match($filters, $file);
		}
		
		// fallback to comma-separated list
		$types = array_filter(preg_split("/\s*,\s*/", $filters));

		foreach ($types as $t)
		{
			// remove initial dot if specified
			$t = ltrim($t, '.');
			// escape slashes to avoid breaking the regex
			$t = preg_replace("/\//", '\/', $t);

			// check if the file ends with the given extension
			if (preg_match("/{$t}$/i", $file))
			{
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Checks whether the given string is a structured PCRE regex.
	 * It simply makes sure that the string owns valid delimiters.
	 * A delimiter can be any non-alphanumeric, non-backslash,
	 * non-whitespace character.
	 *
	 * @param   string   $str  The string to check.
	 *
	 * @return  boolean  True if a regex, false otherwise.
	 *
	 * @since   1.9.5
	 */
	public static function isRegex($str)
	{
		// first of all make sure the first character is a supported delimiter
		if (!preg_match("/^([!#$%&'*+,.\/:;=?@^_`|~\-(\[{<\"])/", $str, $match))
		{
			// no valid delimiter
			return false;
		}

		// get delimiter
		$d = $match[1];

		// lookup used to check if we should take a different ending delimiter
		$lookup = array(
			'{' => '}',
			'[' => ']',
			'(' => ')',
			'<' => '>',
		);

		if (isset($lookup[$d]))
		{
			$d = $lookup[$d];
		}

		// make sure the regex ends with the delimiter found
		return (bool) preg_match("/\\{$d}[gimsxU]*$/", $str);
	}

	/**
	 * This method checks whether the configuration can be imported from the active channel.
	 * This is only needed when configuring the channel manager for the first time.
	 * Also, this request cannot be submitted multiple times and this method prevents that.
	 * 
	 * @param 	int 	$uniquekey 		the channel unique key identifier.
	 * @param 	int 	$set_status 	set new status for the channel config import.
	 *
	 * @return 	int 	0 = cannot import config (request sent/ignored). 1 = can import config.
	 * 
	 * @since 	1.7.2
	 */
	public static function checkImportChannelConfig($uniquekey, $set_status = -1) 
	{
		$dbo = JFactory::getDbo();
		$uniquekey = (int)$uniquekey;

		// check if credentials have been submitted
		$has_cred = false;
		$q = "SELECT `params` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=" . $uniquekey . " AND `av_enabled`=1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cur_params = $dbo->loadResult();
			if (!empty($cur_params)) {
				$arr = json_decode($cur_params, true);
				if (is_array($arr)) {
					foreach ($arr as $k => $v) {
						if (!empty($v)) {
							// credentials submitted for this channel
							$has_cred = true;
						}
						// we only need to check the first parameter
						break;
					}
				}
			}
		}

		// check if some rooms have already been created in VBO
		$no_vbo_rooms = false;
		$q = "SELECT `id` FROM `#__vikbooking_rooms`;";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			$no_vbo_rooms = true;
		}
		
		// check current status
		$cur_status = 0;
		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='chimportconfig{$uniquekey}';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cur_status = (int)$dbo->loadResult();
			if ($set_status >= 0) {
				$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=".$dbo->quote($set_status)." WHERE `param`='chimportconfig{$uniquekey}';";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		} else {
			$cur_status = $no_vbo_rooms ? 1 : 0;
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('chimportconfig{$uniquekey}', '".$cur_status."');";
			$dbo->setQuery($q);
			$dbo->execute();
		}

		return $has_cred === true && $no_vbo_rooms === true && $cur_status > 0 ? 1 : 0;
	}

	/**
	 * Downloads a remote file onto a local destination path.
	 * Used mainly to download remote photos during the configuration import.
	 * 
	 * @param 	string 	$url 	the remote file to be downloaded.
	 * @param 	string 	$dest 	the destination path for downloading the file (with trailing DS).
	 *
	 * @return 	mixed 			string file name downloaded, false otherwise.
	 * 
	 * @since 	1.7.2
	 */
	public static function downloadRemoteFile($url, $dest)
	{
		if (empty($url) || substr($url, 0, 4) != 'http' || empty($dest)) {
			return false;
		}

		$url_parts = parse_url($url);
		if (!$url_parts || empty($url_parts['path'])) {
			return false;
		}

		jimport('joomla.filesystem.file');

		// find filename from URL
		$path_parts = explode('/', $url_parts['path']);
		$fname = $path_parts[(count($path_parts) - 1)];
		$fname = JFile::makeSafe(str_replace(' ', '_', strtolower($fname)));

		// make sure new file does not exist
		if (is_file($dest . $fname)) {
			$j = 1;
			while (is_file($dest . $j . $fname)) {
				$j++;
			}
			$fname = $j . $fname;
		}

		$tmpdest = $dest . rand() . $fname;
		$finaldest = $dest . $fname;

		// download the remote file onto local dir
		$filepointer = fopen($tmpdest, 'w+');
		if (!$filepointer) {
			return false;
		}

		$httpcode = 400;
		$try = 0;
		$curl_errno = 0;
		$curl_err = '';
		do {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FILE, $filepointer);
			$serverres = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if ($curl_errno = curl_errno($ch)) {
				$curl_err = curl_error($ch);
			} else {
				$curl_errno = 0;
				$curl_err = '';
			}
			curl_close($ch);
			$try++;
		} while ($try < 2 && $curl_errno > 0 && in_array($curl_errno, array(2, 6, 7, 28)));

		// close the fp
		fclose($filepointer);

		// parse response
		if ($curl_errno) {
			return false;
		}
		if ($httpcode != 200) {
			// file was not downloaded
			return false;
		}

		// copy the uploaded file by using the apposite method in VBO that will trigger any mirroring function (if needed)
		if (!class_exists('VikBooking') && file_exists(VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php')) {
			// we need the main vbo lib
			require_once (VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php");
		}
		if (!method_exists('VikBooking', 'uploadFile')) {
			// unsupported VBO version
			return false;
		}

		/**
		 * Copy only the file which has been already downloaded.
		 * Source and destination are identical, but we do it to
		 * trigger the files mirroring functions for WP compatibility.
		 */
		if (VikBooking::uploadFile($tmpdest, $finaldest, true)) {
			// unlink temporary destination as PHP's copy() would fail by passing equal src and dest
			@unlink($tmpdest);

			// return the filename uploaded
			return $fname;
		}

		// always unlink temporary destination as PHP's copy() would fail by passing equal src and dest
		@unlink($tmpdest);

		return false;
	}

	/**
	 * Gets the name of the property for the given channel and Hotel ID.
	 * 
	 * @param 	string 	$uniquekey 	the channel unique key.
	 * @param 	string 	$prop 	 	the first property param, usually the Hotel ID.
	 *
	 * @return 	mixed 				string property name, false otherwise.
	 * 
	 * @since 	1.7.2
	 */
	public static function getChannelPropertyName($uniquekey, $prop)
	{
		if (empty($uniquekey) || empty($prop)) {
			return false;
		}

		$dbo = JFactory::getDbo();
		$q = "SELECT `prop_name` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . $dbo->quote($uniquekey) . " AND `prop_params` LIKE " . $dbo->quote("%{$prop}%") . " LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();

		return $dbo->getNumRows() ? $dbo->loadResult() : false;
	}

	/**
	 * Returns an associative array of first param and prop_name pairs
	 * by reading the mapped rooms for the given channel identifier.
	 * It is also possible to get an associative array of first param
	 * and rooms mapped in case we wanted to load all rooms of a channel.
	 * 
	 * @param 	string 	$uniquekey 	the channel unique key.
	 * @param 	bool 	$get_rooms 	whether to get the OTA rooms info.
	 *
	 * @return 	array 				associative or empty array.
	 * 
	 * @since 	1.7.2
	 * @since 	1.8.3 	the arg $get_rooms was introduced (false if omitted).
	 */
	public static function getChannelAccountsMapped($uniquekey, $get_rooms = false)
	{
		$accounts = array();
		if (empty($uniquekey)) {
			return $accounts;
		}

		$dbo = JFactory::getDbo();
		$q = "SELECT `idroomota`, `otaroomname`, `prop_name`, `prop_params` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . $dbo->quote($uniquekey) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			return $accounts;
		}
		$rows = $dbo->loadAssocList();

		foreach ($rows as $row) {
			if (!$get_rooms && (empty($row['prop_name']) || empty($row['prop_params']))) {
				continue;
			}

			$params = json_decode($row['prop_params'], true);
			if (!is_array($params) || !count($params)) {
				// account params are mandatory even when getting the OTA rooms info
				continue;
			}

			// grab account main param
			$hotelid = null;
			if (isset($params['hotelid'])) {
				$hotelid = $params['hotelid'];
			} elseif (isset($params['user_id'])) {
				$hotelid = $params['user_id'];
			} else {
				foreach ($params as $pval) {
					// we grab the first parameter
					$hotelid = $pval;
					break;
				}
			}
			if (empty($hotelid) || (!$get_rooms && isset($accounts[$hotelid]))) {
				continue;
			}

			if (!$get_rooms) {
				// push account name
				$accounts[$hotelid] = $row['prop_name'];
			} else {
				// push OTA room info
				if (!isset($accounts[$hotelid])) {
					$accounts[$hotelid] = array();
				}
				$accounts[$hotelid][$row['idroomota']] = $row['otaroomname'];
			}
		}

		if (!$get_rooms && count($accounts)) {
			asort($accounts);
		}

		return $accounts;
	}

	/**
	 * Returns an associative array of channels supporting rooms mapping
	 * and availability requests, where key=uniquekey, value=channelname.
	 * 
	 * @return 	array 	associative or empty array
	 * 
	 * @since 	1.8.3
	 */
	public static function getAllAvChannels()
	{
		$av_channels = array();

		$dbo = JFactory::getDbo();
	
		$q = "SELECT `name`,`uniquekey` FROM `#__vikchannelmanager_channel` WHERE `av_enabled`=1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			return $av_channels;
		}
		$records = $dbo->loadAssocList();

		foreach ($records as $ch) {
			// push channel supporting rooms mapping and av-requests
			$av_channels[$ch['uniquekey']] = $ch['name'];
		}

		return $av_channels;
	}

	/**
	 * Checks whether the channel Booking.com is available and set up.
	 * Useful to VBO to display certain actions, such as to transmit the
	 * information about the security (damage) deposit to Booking.com.
	 *
	 * @return 	boolean 	true if available, false othewise.
	 * 
	 * @uses 	getChannelAccountsMapped()
	 * 
	 * @since 	1.7.2
	 */
	public static function isBookingcomEnabled()
	{
		if (!class_exists('VikChannelManagerConfig')) {
			// require the config library as the class is probably being invoked by VBO and errors may occur
			require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'vcm_config.php';
		}

		return (count(self::getChannelAccountsMapped(VikChannelManagerConfig::BOOKING)) > 0);
	}

	/**
	 * This method is necessary to normalize the execution for Joomla 4.
	 * We do not use the Application class of Vik Booking in order to
	 * avoid loading its main library
	 *
	 * @return 	void
	 * 
	 * @joomlaonly
	 * 
	 * @since 	1.7.5
	 */
	public static function normalizeExecution()
	{
		/**
		 * @wponly - do nothing for WordPress
		 */
	}

	/**
	 * Helper method to cope with the removal of the same method
	 * in the JApplication class introduced with Joomla 4. Using
	 * isClient() would break the compatibility with J < 3.7 so
	 * we can rely on this helper method to avoid Fatal Errors.
	 * 
	 * @return 	boolean
	 * 
	 * @since 	October 2020
	 */
	public static function isAdmin()
	{
		$app = JFactory::getApplication();
		if (method_exists($app, 'isClient')) {
			return $app->isClient('administrator');
		}

		return $app->isAdmin();
	}

	/**
	 * Helper method to cope with the removal of the same method
	 * in the JApplication class introduced with Joomla 4. Using
	 * isClient() would break the compatibility with J < 3.7 so
	 * we can rely on this helper method to avoid Fatal Errors.
	 * 
	 * @return 	boolean
	 * 
	 * @since 	October 2020
	 */
	public static function isSite()
	{
		$app = JFactory::getApplication();
		if (method_exists($app, 'isClient')) {
			return $app->isClient('site');
		}

		return $app->isSite();
	}

	/**
	 * @wponly 	this method is here only on the WordPress version.
	 *
	 */
	public static function loadPortabilityCSS($document = null) 
	{
		if ($document === null) {
			$document = JFactory::getDocument();
		}

		/**
		 * @wponly  WordPress CSS Adapter
		 */
		$file_to_load = 'wp.css';

		$document->addStyleSheet(VCM_ADMIN_URI.'assets/css/'.$file_to_load);
	}

	/**
	 * Some channels, like Airbnb API, may need custom params which are saved in the config table.
	 * 
	 * @param 	string 	$uniquekey 	the channel unique key.
	 * 
	 * @return 	array
	 * 
	 * @since 	1.8.0
	 */
	public static function getCustomChParams($uniquekey)
	{
		$dbo = JFactory::getDbo();
		$custom_ch_params = array();

		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`=" . $dbo->quote('custom_ch_params_' . $uniquekey);
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			// this is an associative array with "params" and "settings"
			$data = json_decode($dbo->loadResult(), true);
			$custom_ch_params = is_array($data) && count($data) ? $data : $custom_ch_params;
		}

		return $custom_ch_params;
	}

	/**
	 * Some channels, like Airbnb API, may need to reload custom params.
	 * 
	 * @param 	string 	$uniquekey 	the channel unique key.
	 * 
	 * @return 	bool 	 			true if reload is needed, false otherwise.
	 * 
	 * @since 	1.8.0
	 */
	public static function shouldReloadCustomChParams($uniquekey)
	{
		$dbo = JFactory::getDbo();
		$reload = true;
		// reload custom channel params every week
		$reload_lim = 86400 * 7;

		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`=" . $dbo->quote('custom_ch_params_reload_' . $uniquekey);
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$last_reload = $dbo->loadResult();
			$reload = (!empty($last_reload) && ((time() - $last_reload) > $reload_lim));
		}

		return $reload;
	}

	/**
	 * Some channels, like Airbnb API, may need to download custom params from e4jConnect and cache them.
	 * 
	 * @param 	string 	$uniquekey 	the channel unique key.
	 * 
	 * @return 	array 	 			custom channel params array, or empty array.
	 * 
	 * @since 	1.8.0
	 */
	public static function downloadCustomChParams($uniquekey)
	{
		$dbo = JFactory::getDbo();
		$create = false;
		$custom_ch_params = array();

		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`=" . $dbo->quote('custom_ch_params_' . $uniquekey);
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// create configuration record
			$create = true;
		}

		// make the request to e4jConnect to obtain the channel custom params
		$apikey = self::getApiKey(true);
		if (!function_exists('curl_init') || empty($apikey)) {
			return $custom_ch_params;
		}

		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=chcp&c=generic";
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager CHCP Request e4jConnect.com - VikBooking - extensionsforjoomla.com -->
<ChannelCustomParamsRQ xmlns="http://www.e4jconnect.com/schemas/chcprq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $apikey . '"/>
	<Fetch channel="' . (int)$uniquekey . '" platform="' . (VCMPlatformDetection::isWordPress() ? 'WP' : 'J') . '"/>
</ChannelCustomParamsRQ>';
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->slaveEnabled = true;
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();

		if ($e4jC->getErrorNo()) {
			echo @curl_error($e4jC->getCurlHeader());
			return $custom_ch_params;
		}

		// this is an associative array with "params" and "settings"
		$data = json_decode($rs, true);
		$custom_ch_params = is_array($data) ? $data : $custom_ch_params;

		if (count($custom_ch_params)) {
			// update or create configuration record to cache the channel custom params
			if ($create) {
				$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES(" . $dbo->quote('custom_ch_params_' . $uniquekey) . ", " . $dbo->quote(json_encode($custom_ch_params)) . ");";
			} else {
				$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=" . $dbo->quote(json_encode($custom_ch_params)) . " WHERE `param`=" . $dbo->quote('custom_ch_params_' . $uniquekey) . ";";
			}
			$dbo->setQuery($q);
			$dbo->execute();
			// update last reloading time
			$create = false;
			$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`=" . $dbo->quote('custom_ch_params_reload_' . $uniquekey);
			$dbo->setQuery($q);
			$dbo->execute();
			if (!$dbo->getNumRows()) {
				$create = true;
			}
			if ($create) {
				$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES(" . $dbo->quote('custom_ch_params_reload_' . $uniquekey) . ", " . $dbo->quote(time()) . ");";
			} else {
				$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=" . $dbo->quote(time()) . " WHERE `param`=" . $dbo->quote('custom_ch_params_reload_' . $uniquekey) . ";";
			}
			$dbo->setQuery($q);
			$dbo->execute();
		}

		return $custom_ch_params;
	}

	/**
	 * Checks if the given room has one option of type tourist tax assigned.
	 * 
	 * @param 	int 	$idroom 	the id of the room in VBO.
	 * 
	 * @return 	mixed 	false on failure, array with option record otherwise.
	 * 
	 * @since 	1.8.0
	 */
	public static function getRoomTouristTax($idroom)
	{
		if (empty($idroom) || !is_numeric($idroom)) {
			return false;
		}

		$dbo = JFactory::getDbo();

		// grab room information
		$q = "SELECT `id`, `idopt` FROM `#__vikbooking_rooms` WHERE `id`=" . (int)$idroom . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			return false;
		}
		$room_record  = $dbo->loadAssoc();
		$room_options = explode(';', $room_record['idopt']);

		// grab all tourist tax options
		$q = "SELECT * FROM `#__vikbooking_optionals` WHERE `is_citytax`=1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			return false;
		}
		$tourist_taxes = $dbo->loadAssocList();

		// find the first tourist tax option id assigned to the given room
		foreach ($tourist_taxes as $tax) {
			if (in_array($tax['id'], $room_options)) {
				// tourist tax found for this room
				return $tax;
			}
		}

		// nothing was found
		return false;
	}

	/**
	 * Checks if the given room has got an environmental fee configured.
	 * 
	 * @param 	int 	$idroom 	The VikBooking room ID.
	 * 
	 * @return 	array 				Empty array or list of mandatory eco-fee options.
	 * 
	 * @since 	1.9.16
	 */
	public static function getRoomEnvironmentalFee(int $idroom)
	{
		// filter all room room mandatory fees to identify an eco-fee
		$roomMandatoryFees = array_filter(self::getAllMandatoryFees([$idroom]), function($fee) {
			if (empty($fee['forcesel'])) {
				// the fee must be mandatory
				return false;
			}

			if (!empty($fee['is_citytax']) || empty($fee['is_fee'])) {
				// must be a fee, not a city/tourism tax
				return false;
			}

			if ((float) ($fee['cost'] ?? 0) <= 0) {
				// fee must be greater than zero
				return false;
			}

			// match the eco-fee by name
			$acceptedNames = [
				'eco',
				'environment',
				'climate',
				// Greek for "environmental"
				'Περιβαλλοντικό',
			];

			foreach ($acceptedNames as $acceptedName) {
				if (stripos((string) ($fee['name'] ?? ''), $acceptedName) === 0) {
					// option name starts with an accepted name
					return true;
				}
			}

			return false;
		});

		// reset array keys after filtering
		$roomMandatoryFees = array_values($roomMandatoryFees);

		// return the first eligible room option record found, if any
		return $roomMandatoryFees[0] ?? [];
	}

	/**
	 * Rather than loading the entire configuration table, we can load just one record.
	 * 
	 * @param 	string 	$param 	the param name of the configuration record.
	 * 
	 * @return 	mixed 			false on failure, setting string otherwise.
	 * 
	 * @since 	1.8.0
	 */
	public static function getConfigurationRecord($param)
	{
		if (empty($param) || !is_string($param)) {
			return false;
		}

		$dbo = JFactory::getDbo();
		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`=" . $dbo->quote($param) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			return false;
		}

		return $dbo->loadResult();
	}

	/**
	 * Checks whether a room has been configured with LOS pricing rules.
	 * 
	 * @param 	int 	$idroom 	the ID of the room in VBO.
	 * @param 	int 	$idprice 	the optional rate plan ID in VBO.
	 * @param 	bool 	$get_nights whether to return the number of nights when LOS starts.
	 * 
	 * @return 	bool 				false on failure or if no LOS prices found, true otherwise.
	 * 
	 * @since 	1.8.0
	 * @since 	1.8.16 				added argument $get_nights.
	 */
	public static function roomHasLosRecords($idroom, $idprice = 0, $get_nights = false)
	{
		if (empty($idroom)) {
			return false;
		}

		$dbo = JFactory::getDbo();
		$q = "SELECT * FROM `#__vikbooking_dispcost` WHERE `idroom`=" . (int)$idroom . (!empty($idprice) ? " AND `idprice`=" . (int)$idprice : '') . " ORDER BY `days` ASC;";
		$dbo->setQuery($q);
		$los_data = $dbo->loadAssocList();
		if (!$los_data) {
			return false;
		}

		$los_pricing = array();
		foreach ($los_data as $cost) {
			if (!isset($los_pricing[$cost['days']])) {
				$los_pricing[$cost['days']] = array();
			}
			array_push($los_pricing[$cost['days']], $cost);
		}
		// sort by number of nights
		ksort($los_pricing);

		// compose lowest costs per rate plan
		$base_costs = array();
		foreach ($los_pricing as $nights => $costs) {
			foreach ($costs as $rplan_cost) {
				$base_costs[$rplan_cost['idprice']] = ($rplan_cost['cost'] / $rplan_cost['days']);
			}
			// we take the costs for the lowest number of nights
			break;
		}

		// check if rates change depending on the number of nights of stay
		foreach ($los_pricing as $nights => $costs) {
			foreach ($costs as $rplan_cost) {
				$base_cost = ($rplan_cost['cost'] / $rplan_cost['days']);
				if (isset($base_costs[$rplan_cost['idprice']]) && round($base_costs[$rplan_cost['idprice']], 2) != round($base_cost, 2)) {
					/**
					 * Average rates should be compared after applying rounding or we may face issues.
					 * For example, 383.97 / 3 = 127.99, but it's actually = 127.99000000000001 with
					 * an absolute number for the difference with 127.99 of 1.4210854715202004E-14
					 * which results to be greater than 0 but less than 1. Therefore, we also allow
					 * an absolute number for the difference of 0.05 cents for a proper check.
					 * 
					 * @since 	1.8.4
					 */
					$price_diff = abs($base_costs[$rplan_cost['idprice']] - $base_cost);
					if ($price_diff > 0.05) {
						// this is a non-proportional cost per night, so LOS records have been defined
						return $get_nights ? $nights : true;
					}
				}
			}
		}

		// all costs per night were proportional
		return false;
	}

	/**
	 * Checks whether a reservation requires decline reasons if cancelled.
	 * 
	 * @param 	array 	$reservation 	the reservation record of VBO.
	 * 
	 * @return 	bool 	true if decline reasons are needed, false otherwise.
	 * 
	 * @since 	1.8.0
	 */
	public static function reservationNeedsDeclineReasons($reservation)
	{
		if (!is_array($reservation) || empty($reservation['type'])) {
			return false;
		}

		if (empty($reservation['idorderota']) || empty($reservation['channel']) || $reservation['status'] != 'standby') {
			return false;
		}

		if ($reservation['checkout'] < time() || stripos($reservation['channel'], 'airbnbapi') === false) {
			return false;
		}

		if (!empty($reservation['type']) && strcasecmp($reservation['type'], 'Request')) {
			// only request to book reservations support decline reasons
			return false;
		}

		return true;
	}

	/**
	 * Checks whether a website pending reservation supports a special offer
	 * to be sent to the client through a channel like Airbnb API.
	 * Even bookings of type Inquiry are eligible to send special offers (and pre-approvals).
	 * 
	 * @param 	array 	$reservation 	the reservation record of VBO.
	 * 
	 * @return 	mixed 	array if special offer is supported, false otherwise.
	 * 
	 * @since 	1.8.0
	 */
	public static function reservationSupportsSpecialOffer($reservation)
	{
		if (!is_array($reservation) || empty($reservation['type'])) {
			return false;
		}

		/**
		 * Check if pre-approval is supported, because bookings supporting
		 * pre-approvals will also support special offers.
		 */
		$pre_approval_allowed = self::reservationSupportsPreApproval($reservation);

		if ($pre_approval_allowed === false && strcasecmp((string)$reservation['type'], 'Inquiry') && (!empty($reservation['idorderota']) || !empty($reservation['channel']) || $reservation['status'] != 'standby')) {
			// must be a website booking created by the administrator manually, unless pre-approval is supported or booking type is Inquiry
			return false;
		}

		if (!class_exists('VikChannelManagerConfig')) {
			// require the config library as the class is probably being invoked by VBO and errors may occur
			require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'vcm_config.php';
		}

		// the channel Airbnb API must be available
		$channel = self::getChannel(VikChannelManagerConfig::AIRBNBAPI);
		if (!is_array($channel) || !count($channel)) {
			return false;
		}

		// check booking history record for a manually created reservation
		$history_has_event = false;
		try {
			if (!class_exists('VikBooking')) {
				require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';
			}
			$history_has_event = VikBooking::getBookingHistoryInstance()->setBid($reservation['id'])->hasEvent('NB');
		} catch (Exception $e) {
			// do nothing
		}

		if ($reservation['checkin'] < strtotime('today 00:00:00') || ($history_has_event === false && $pre_approval_allowed === false)) {
			// check-in time in the past or booking not created manually by admin via back-end (if not pre-approval allowed)
			return false;
		}

		// query the database to find more information
		$dbo = JFactory::getDbo();
		
		// make sure all rooms assigned to the booking are mapped with Airbnb, and it has to be just one room
		$channel_listing_id = null;
		try {
			$q = "SELECT `or`.`idroom`, `x`.`idroomota` FROM `#__vikbooking_ordersrooms` AS `or` LEFT JOIN `#__vikchannelmanager_roomsxref` AS `x` ON `or`.`idroom`=`x`.`idroomvb` WHERE `or`.`idorder`=" . (int)$reservation['id'] . " AND `x`.`idchannel`=" . (int)$channel['uniquekey'] . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() != 1) {
				return false;
			}
			$room_data = $dbo->loadAssoc();
			$channel_listing_id = $room_data['idroomota'];
		} catch (Exception $e) {
			// do nothing
		}
		if (empty($channel_listing_id)) {
			// room booked does not belong to Airbnb API
			return false;
		}

		// find the channel thread id
		$channel_thread_id = null;
		if ($pre_approval_allowed !== false) {
			// we already have the thread id from the booking of type Inquiry
			$channel_thread_id = $pre_approval_allowed[0];
		}

		if (empty($channel_thread_id)) {
			// the reservation must be assigned to a customer record, unless of course a thread id is already available
			$customer_id = null;
			try {
				$q = "SELECT `co`.`idcustomer` FROM `#__vikbooking_customers_orders` AS `co` WHERE `co`.`idorder`=" . (int)$reservation['id'];
				$dbo->setQuery($q, 0, 1);
				$dbo->execute();
				if (!$dbo->getNumRows()) {
					return false;
				}
				$customer_id = $dbo->loadResult();
			} catch (Exception $e) {
				// do nothing
			}
			if (empty($customer_id)) {
				return false;
			}

			// find the latest OTA Thread ID with this customer
			try {
				$q = "SELECT `co`.`idcustomer`, `co`.`idorder`, `t`.`idorderota`, `t`.`ota_thread_id`, `t`.`last_updated` FROM `#__vikbooking_customers_orders` AS `co` " .
					"LEFT JOIN `#__vikchannelmanager_threads` AS `t` ON `co`.`idorder`=`t`.`idorder` WHERE `co`.`idcustomer`=" . (int)$customer_id . " AND `t`.`channel`=" . $dbo->quote($channel['name']) . " AND `t`.`ota_thread_id` IS NOT NULL " .
					"ORDER BY `t`.`last_updated` DESC";
				$dbo->setQuery($q, 0, 1);
				$dbo->execute();
				if (!$dbo->getNumRows()) {
					return false;
				}
				$thread = $dbo->loadAssoc();
				// we use the first ota thread id returned
				$channel_thread_id = $thread['ota_thread_id'];
			} catch (Exception $e) {
				// do nothing
			}
		}
		
		if (empty($channel_thread_id)) {
			// the OTA thread id is mandatory to send a Special Offer as they rely on the Messaging API
			return false;
		}

		// channel name
		$channel_name = $channel['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? 'Airbnb' : ucwords($channel['name']);

		// check if a previous special offer id of type "special_offer" is available
		$special_offer_id = null;
		$special_offer_withdrawn = 0;
		$ota_type_data = json_decode($reservation['ota_type_data']);
		if (is_object($ota_type_data) && !empty($ota_type_data->special_offer_id) && !empty($ota_type_data->spo_type) && $ota_type_data->spo_type == 'special_offer') {
			$special_offer_id = $ota_type_data->special_offer_id;
			$special_offer_withdrawn = !empty($ota_type_data->withdrawn) ? 1 : 0;
		}

		// return an array with the needed information
		return array(
			$channel_listing_id,
			$channel_thread_id,
			$channel_name,
			$special_offer_id,
			$special_offer_withdrawn,
		);
	}

	/**
	 * Checks whether the reservation supports a pre-approval
	 * to be sent to the client through a channel like Airbnb API.
	 * 
	 * @param 	array 	$reservation 	the reservation record of VBO.
	 * 
	 * @return 	mixed 	array if pre-approval is supported, false otherwise.
	 * 
	 * @since 	1.8.0
	 */
	public static function reservationSupportsPreApproval($reservation)
	{
		if (!is_array($reservation)) {
			return false;
		}

		if (empty($reservation['idorderota']) || empty($reservation['channel']) || $reservation['status'] == 'confirmed') {
			return false;
		}

		if ($reservation['checkin'] < strtotime('today 00:00:00') || stripos($reservation['channel'], 'airbnbapi') === false) {
			return false;
		}

		if (empty($reservation['type']) || (strcasecmp($reservation['type'], 'Inquiry') && strcasecmp($reservation['type'], 'Request')) || empty($reservation['ota_type_data'])) {
			return false;
		}

		$ota_type_data = json_decode($reservation['ota_type_data']);
		if (!is_object($ota_type_data) || empty($ota_type_data->thread_id)) {
			// we do not care if "withdrawn" is set, because once withdrawn, we can send another one
			return false;
		}

		// check if a previous special offer id of type "preapproval" is available
		$special_offer_id = null;
		$special_offer_withdrawn = 0;
		$ota_type_data = json_decode($reservation['ota_type_data']);
		if (is_object($ota_type_data) && !empty($ota_type_data->special_offer_id) && !empty($ota_type_data->spo_type) && $ota_type_data->spo_type == 'preapproval') {
			$special_offer_id = $ota_type_data->special_offer_id;
			$special_offer_withdrawn = !empty($ota_type_data->withdrawn) ? 1 : 0;
		}

		// channel name (inject it statically)
		$channel_name = 'Airbnb';

		// return the ota thread id and the special offer id, if available from a previous special offer sent (of any type), + the channel name
		return array(
			$ota_type_data->thread_id,
			$channel_name,
			$special_offer_id,
			$special_offer_withdrawn,
		);
	}

	/**
	 * Checks whether the reservation allows the host to review the guest.
	 * Only some channels, like Airbnb API, may support this feature.
	 * 
	 * @param 	array 	$reservation 	the reservation record of VBO.
	 * @param 	bool 	$willbe 		true if the review will be supported.
	 * 
	 * @return 	bool 					true if supported, false otherwise.
	 * 
	 * @since 	1.8.0
	 * @since 	1.9.0 	added 2nd argument $willbe.
	 */
	public static function hostToGuestReviewSupported($reservation, $willbe = false)
	{
		if (!is_array($reservation)) {
			return false;
		}

		if (empty($reservation['idorderota']) || empty($reservation['channel']) || $reservation['status'] != 'confirmed') {
			return false;
		}

		if (stripos($reservation['channel'], 'airbnbapi') === false) {
			return false;
		}

		// check-out must be in the past, but not more than 14 days
		$now = time();
		$checkout_info = getdate($reservation['checkout']);
		$checkout_midnight = mktime(0, 0, 0, $checkout_info['mon'], $checkout_info['mday'], $checkout_info['year']);
		if ((!$willbe && $checkout_midnight > $now) || strtotime("+14 days", $reservation['checkout']) < $now) {
			return false;
		}

		// the channel Airbnb API must be available
		$channel = self::getChannel(VikChannelManagerConfig::AIRBNBAPI);
		if (!$channel) {
			return false;
		}

		/**
		 * Make sure the host did not review the guest already for this reservation.
		 * In this case, a configuration record with the data would be there.
		 */
		$transient_name = 'host_to_guest_review_' . $channel['uniquekey'] . '_' . $reservation['id'];

		if (VCMFactory::getConfig()->has($transient_name)) {
			// a review for the guest is present for this booking
			return false;
		}

		return true;
	}

	/**
	 * Checks whether an active OTA reservation can be cancelled.
	 * 
	 * @param 	array 	$reservation 	the reservation record of VBO.
	 * 
	 * @return 	bool 	true if it can be cancelled (with reasons), false otherwise.
	 * 
	 * @since 	1.8.0
	 */
	public static function cancelActiveOtaReservation($reservation)
	{
		if (!is_array($reservation)) {
			return false;
		}

		if (empty($reservation['idorderota']) || empty($reservation['channel']) || $reservation['status'] != 'confirmed') {
			return false;
		}

		if ($reservation['checkout'] < time() || stripos($reservation['channel'], 'airbnbapi') === false) {
			return false;
		}

		// both Inquiry and Request to Book reservations can be cancelled, with penalties, if they are active
		return true;
	}

	/**
	 * Gets a list of countries from the given number of chars-code.
	 * 
	 * @param 	string 	$chars 	country code version, either '2' or '3'.
	 * 
	 * @return 	array 			associative array of requested country codes length.
	 * 
	 * @since 	1.8.0
	 */
	public static function getCountryCodes($chars = '2') {
		$dbo = JFactory::getDbo();
		$all_countries = array();

		$chars = intval($chars) === 2 ? '2' : '3';
		$colnm = "country_{$chars}_code";
		
		$q = "SELECT `country_name`, `{$colnm}` FROM `#__vikbooking_countries` ORDER BY `country_name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			return $all_countries;
		}
		
		$countries = $dbo->loadAssocList();
		foreach ($countries as $v) {
			$all_countries[$v[$colnm]] = $v['country_name'];
		}
		
		return $all_countries;
	}

	/**
	 * Checks whether the system is running an outdated version of Airbnb (iCal)
	 * which will have to be dismissed soon, due to our certification with the
	 * new Airbnb API integration that supports two-way sync. All iCal calendars
	 * connected with Airbnb will have to be dismissed as soon as possible in
	 * accordance with the contract stipulated between e4jConnect and Airbnb.
	 * 
	 * @return 	mixed 	false if all is okay, true if all is bad, -1 if action is needed.
	 * 
	 * @since 	1.8.0
	 */
	public static function hasDeprecatedAirbnbVersion()
	{
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `name`, `uniquekey` FROM `#__vikchannelmanager_channel` WHERE `uniquekey` IN (" . $dbo->quote(VikChannelManagerConfig::AIRBNB) . ", " . $dbo->quote(VikChannelManagerConfig::AIRBNBAPI) . ");";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// none of the involved channels is actually available
			return false;
		}
		$rows = $dbo->loadAssocList();
		$channels = array();
		foreach ($rows as $channel) {
			$channels[$channel['uniquekey']] = $channel['name'];
		}

		if (count($channels) === 1 && isset($channels[VikChannelManagerConfig::AIRBNBAPI])) {
			// all is good, only running the latest version of Airbnb
			return false;
		}

		if (count($channels) === 1 && isset($channels[VikChannelManagerConfig::AIRBNB])) {
			// this is no good, only the old Airbnb channel is active
			return true;
		}

		// -1 means that both channels are still active, and so the old iCal version should be dismissed
		return -1;
	}

	/**
	 * Checks whether the given channel ID has got some rooms mapped.
	 * 
	 * @param 	string 	$uniquekey 	the channel unique key.
	 * 
	 * @return 	bool
	 * 
	 * @since 	1.8.0
	 */
	public static function channelHasRoomsMapped($uniquekey)
	{
		$dbo = JFactory::getDbo();

		if (empty($uniquekey)) {
			return false;
		}

		$q = "SELECT `id` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . $dbo->quote($uniquekey);
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// no rooms mapped
			return false;
		}

		return true;
	}

	/**
	 * This method can be used to detect if we are inside an AJAX request.
	 * 
	 * @return 	bool 	true if it's an AJAX request, false otherwise.
	 * 
	 * @since 	1.8.0
	 */
	public static function isAJAXRequest()
	{
		$app = JFactory::getApplication();

		$x_requested = $app->input->server->get('HTTP_X_REQUESTED_WITH');
		if (!empty($x_requested) && !strcasecmp($x_requested, 'XMLHttpRequest')) {
			return true;
		}
		if (function_exists('wp_doing_ajax') && wp_doing_ajax()) {
			return true;
		}

		return false;
	}

	/**
	 * Given the full endpoint URL for the AJAX request, it
	 * returns an appropriate URI for the current platform.
	 * 
	 * @param 	mixed 	 $query 	The query string or a routed URL.
	 * @param 	boolean  $xhtml  	Replace & by &amp; for XML compliance.
	 * 
	 * @return 	string 				The AJAX end-point URI.
	 * 
	 * @since 	1.8.11
	 */
	public static function ajaxUrl($query = '', $xhtml = false)
	{
		return VCMFactory::getPlatform()->getUri()->ajax($query, $xhtml);
	}

	/**
	 * Checks whether the availability window has been reached by checking the
	 * configuration setting for the Availability Window and the lowest date covered
	 * by the last execution of the Bulk Actions. If true, auto bulk actions can run.
	 * 
	 * @param 	array 	$forced_opts 	optional list of forced options.
	 * 
	 * @return 	mixed 	array if auto bulk actions should run, false otherwise.
	 * 
	 * @since 	1.8.0
	 * @since 	1.8.13 	added argument $forced_opts for future usage.
	 */
	public static function availabilityWindowReached($forced_opts = null)
	{
		$maxdates = self::getInventoryMaxFutureDates();
		if (!is_array($maxdates) || !count($maxdates)) {
			return false;
		}
		$av_window = self::getConfigurationRecord('av_window');
		if (empty($av_window) || $av_window == 'manual' || !is_numeric($av_window)) {
			return false;
		}
		
		$months_ahead = (int)$av_window;
		$farthest_push = min($maxdates);
		$should_cover = strtotime("+ {$months_ahead} months");

		/**
		 * Even if this process was executed every day, we make sure the timestamp
		 * difference is greater than a factor, which will indicate how often at most
		 * the auto-bulk actions will be triggered. This will be like checking when
		 * was the last time the bulk action was launched.
		 */
		$factor_interval = $months_ahead > 6 ? 14 : 7;

		if (($should_cover - $farthest_push) < (86400 * $factor_interval)) {
			// booking window is covered
			return false;
		}

		// return the range of dates to be updated
		return array(
			date('Y-m-d', $farthest_push),
			date('Y-m-d', $should_cover),
		);
	}

	/**
	 * Helper method to check if bulk actions should be executed
	 * automatically. It should be called at any execution of VCM.
	 * 
	 * @param 	?array 	$forced_opts 	Optional forced options list.
	 * 
	 * @return 	bool 	true if auto bulk actions will run.
	 * 
	 * @since 	1.8.0
	 * @since 	1.8.3 	the argument $forced_opts was introduced.
	 * @since 	1.8.13 	added support for uniquekey in $forced_opts.
	 */
	public static function autoBulkActions(?array $forced_opts = null)
	{
		$dbo = JFactory::getDbo();
		$app = JFactory::getApplication();

		$forced_opts = !is_null($forced_opts) && !is_scalar($forced_opts) ? (array)$forced_opts : null;

		// process should not run when it's not necessary or potentially dangerous for parallel processes
		$current_task = $app->input->get('task');
		$disabled_tasks = array(
			'avpush',
			'ratespush',
			'avpushsubmit',
			'ratespushsubmit',
			'exec_avpush',
			'exec_avpush_prepare',
			'exec_avpush_finalize',
			'exec_ratespush',
			'exec_ratespush_finalize',
			'chat.sync_threads',
			'check_notifications',
		);
		if (in_array($current_task, $disabled_tasks) && !is_array($forced_opts)) {
			// do not interfere with the manual Bulk Actions, or with frequent AJAX pings
			return false;
		}

		// get the range of dates to be updated (if any)
		$dates_range = self::availabilityWindowReached($forced_opts);
		if (is_array($forced_opts) && isset($forced_opts['from_date']) && isset($forced_opts['to_date'])) {
			// we need to force the dates
			$dates_range = array($forced_opts['from_date'], $forced_opts['to_date']);
		}

		if (!is_array($dates_range) || !$dates_range) {
			/**
			 * No need to run the bulk actions automatically, but we check if Google Hotel is installed and
			 * if an alignment of the ARI information is required to avoid price accuracy mismatches and to
			 * improve the property score. We also reduce the risk of penalising outdated account information.
			 * 
			 * @since 	1.8.13
			 */
			$has_google = self::hasGoogleHotelChannel();
			$last_autobulk_google_dt = VCMFactory::getConfig()->get('autobulk_google_last_dt', '');
			$last_autobulk_google_ts = $last_autobulk_google_dt ? strtotime($last_autobulk_google_dt) : strtotime('-8 days');
			if (!$forced_opts && $has_google && (time() - $last_autobulk_google_ts) > (86400 * 7)) {
				// we force an alignment of the ARI for Google Hotel so the Bulk Actions should run just for Google

				// immediately set the last flag execution datetime to right now
				VCMFactory::getConfig()->set('autobulk_google_last_dt', date('Y-m-d H:i:s'));

				// make sure this forced auto bulk action for Google was not disabled
				if (!VCMFactory::getConfig()->get('autobulk_google_disabled', 0)) {
					// inject the forced options for a range of 3 months from today, only for Google Hotel
					$forced_opts = [
						'from_date' => date('Y-m-d'),
						'to_date' 	=> date('Y-m-d', strtotime('+3 months')),
						'uniquekey' => VikChannelManagerConfig::GOOGLEHOTEL,
					];

					// set the range of dates that should be used so that the process will actually run
					$dates_range = [
						$forced_opts['from_date'],
						$forced_opts['to_date'],
					];
				}
			}

			if (!$dates_range) {
				// no need to run the bulk actions at this point
				return false;
			}
		}

		// collect range of dates
		list($from_date, $to_date) = $dates_range;

		// make sure a previous request to e4jConnect was not sent already
		$prev_auto = VCMFactory::getConfig()->get('autobulk_last_from', '');
		if (!empty($prev_auto) && $prev_auto == $from_date && !is_array($forced_opts)) {
			// the same request has already been sent to e4jConnect
			return false;
		}

		// bulk rates cache must be present
		$bulk_rates_cache = self::getBulkRatesCache();
		if (!is_array($bulk_rates_cache) || !$bulk_rates_cache) {
			if (!is_array($forced_opts)) {
				return false;
			}
		}

		/**
		 * Before even making the request, flag the execution to prevent other processes,
		 * maybe that run via AJAX, to cause a double execution of the request. In the end,
		 * this request does not need any response validation. It has to go through.
		 */
		VCMFactory::getConfig()->set('autobulk_last_from', $from_date);

		// build node attributes
		$dates_node_attr = [
			'from="' . $from_date . '"',
			'to="' . $to_date . '"',
		];
		if (is_array($forced_opts)) {
			if ($forced_opts['update'] ?? null) {
				$dates_node_attr[] = 'update="' . $forced_opts['update'] . '"';
			}
			if ($forced_opts['uniquekey'] ?? null) {
				$dates_node_attr[] = 'uniquekey="' . $forced_opts['uniquekey'] . '"';
			}
			if ($forced_opts['forced_rooms'] ?? null) {
				$dates_node_attr[] = 'forced_rooms="' . implode(',', (array) $forced_opts['forced_rooms']) . '"';
			}
			if ($forced_opts['rate_id'] ?? null) {
				$dates_node_attr[] = 'rate_id="' . (int) $forced_opts['rate_id'] . '"';
			}
		}

		// make the request to e4jConnect
		switch (($forced_opts['server'] ?? '')) {
			case 'master':
				/**
				 * @todo  use "master." subdomain once the master will be divided from the shop
				 */
				$host = '';
				break;

			case 'hotels':
				$host = 'hotels.';
				break;
			
			default:
				$host = 'slave.';
				break;
		}
		$e4jc_url = "https://{$host}e4jconnect.com/channelmanager/?r=autobulk&c=generic";

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- AUTOBULK Request e4jConnect.com - VikChannelManager - VikBooking -->
<AutoBulkActionsRQ xmlns="http://www.e4jconnect.com/schemas/autobulkrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . self::getApiKey(true) . '"/>
	<Dates ' . implode(' ', $dates_node_attr) . '/>
</AutoBulkActionsRQ>';

		/**
		 * It is fundamental to not allow recursion on another server here,
		 * because some servers may not take the "Connection: close" header
		 * as valid, and so we risk to make VCM hang for like 30 seconds or
		 * even worse, to cause double requests between servers. We also set
		 * a low timeout with no retries just to make the process as quick as
		 * possible. cURL errors 28 may occur, but we do not care.
		 */
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setTimeout(5)->setRetries(0);
		//
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();

		if ($e4jC->getErrorNo() == 28) {
			// we still consider the request as valid, the connection was closed but the header was ignored.
			return true;
		}
		
		if (strcasecmp((string)$rs, 'e4j.ok')) {
			return false;
		}
		
		return true;
	}

	/**
	 * Helper method to collect a list of the latest Guest messages and Reviews coming
	 * from the guests. Useful for the admin widget of VBO or even for the App.
	 * 
	 * @param 	array 	$type 		activity types (messages and reviews by default).
	 * @param 	int 	$offset 	the start reading point to support paging.
	 * @param 	int 	$length 	the total number of activities to return (per type.
	 * 
	 * @return 	array 	the latest activity objects from the guests.
	 * 
	 * @since 	1.8.0
	 * @since 	1.8.9 	added support to fetch all latest guest messages ($type = ['guest_messages'])
	 */
	public static function getLatestFromGuests($type, $offset = 0, $length = 10)
	{
		$dbo = JFactory::getDbo();

		// prepare values
		$activity_types = ['guest_messages', 'messages', 'reviews'];
		if (!is_array($type) || !$type) {
			$type = $activity_types;
		}
		foreach ($type as $act) {
			if (!in_array($act, $activity_types)) {
				$type = $activity_types;
				break;
			}
		}
		$offset = $offset >= 0 ? $offset : 0;
		$length = $length > 0 ? $length : 10;

		// build the activities pool
		$activities = [];

		/**
		 * We give the possibility to fetch all latest guest messages no matter if the last message
		 * in the thread was sent by the hotel. This is useful to obtain a list of all guest messages.
		 * Without passing the 4th argument $join_sender to true to VCMChatHandler::getLatestThreads()
		 * the query would fetch only the threads with no owner reply.
		 * 
		 * @since 	1.8.9
		 */
		if (in_array('guest_messages', $type)) {
			// collect all latest guest messages from the various threads (browsing method)

			// require VCMChatHandler class file
			require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'chat' . DIRECTORY_SEPARATOR . 'handler.php';

			// get latest threads messages from guest
			$activities = VCMChatHandler::getLatestThreads($offset, $length, 'guest', $join_sender = true);
		} elseif (in_array('messages', $type)) {
			// collect the latest guest messages (classic method)

			// require VCMChatHandler class file
			require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'chat' . DIRECTORY_SEPARATOR . 'handler.php';

			// get latest threads messages from guest
			$activities = VCMChatHandler::getLatestThreads($offset, $length, 'guest');
		}

		if (in_array('reviews', $type)) {
			// collect the latest guest reviews
			$q = $dbo->getQuery(true)
				->select([
					$dbo->qn('r.id', 'id_review'),
					$dbo->qn('r.channel'),
					$dbo->qn('r.idorder'),
					$dbo->qn('r.dt', 'last_updated'),
					$dbo->qn('r.customer_name'),
					$dbo->qn('r.score'),
					$dbo->qn('r.country'),
					$dbo->qn('r.content', 'raw_content'),
				])
				->from($dbo->qn('#__vikchannelmanager_otareviews', 'r'))
				->order($dbo->qn('r.dt') . ' DESC');

			$dbo->setQuery($q, $offset, $length);
			$reviews = $dbo->loadObjectList();

			// review content possible property keys
			$message_keys = [
				'message',
				'public_review',
				'positive',
				'headline',
				'private_feedback',
				'negative',
			];
			foreach ($reviews as $k => $review) {
				// parse customer name
				$reviews[$k]->first_name = '';
				$reviews[$k]->last_name = '';
				$name_parts = explode(' ', (string)$review->customer_name);
				if (count($name_parts) > 1) {
					$reviews[$k]->last_name = $name_parts[(count($name_parts) - 1)];
					unset($name_parts[(count($name_parts) - 1)]);
				}
				$reviews[$k]->first_name = implode(' ', $name_parts);
				// make sure an empty channel is converted to "vikbooking" like the guest messages
				if (empty($review->channel)) {
					$reviews[$k]->channel = 'vikbooking';
				}
				// parse raw content
				$reviews[$k]->content = '';
				$raw_content = json_decode($review->raw_content);
				if (is_object($raw_content)) {
					// grab the main review content
					if (isset($raw_content->content) && is_object($raw_content->content)) {
						foreach ($message_keys as $mess_key) {
							if (!empty($raw_content->content->{$mess_key}) && is_string($raw_content->content->{$mess_key})) {
								// review message found
								$reviews[$k]->content = $raw_content->content->{$mess_key};
								break;
							}
						}
					}
					// check if the user profile pic exists (only some channels support it)
					if (isset($raw_content->reviewer) && !empty($raw_content->reviewer->photo) && strpos($raw_content->reviewer->photo, 'http') === 0) {
						// we've got a profile picture URL of the guest
						$reviews[$k]->guest_avatar = $raw_content->reviewer->photo;
					}
				}
			}

			// merge reviews with the rest of the activities
			$activities = array_merge($activities, $reviews);
		}

		// set channel logo URL for all activities (if available)
		foreach ($activities as $k => $activity) {
			$act_type = isset($activity->id_review) ? 'review' : 'message';
			$channel_logo = self::getLogosInstance($activity->channel)->getSmallLogoURL();
			if (!empty($channel_logo)) {
				$activities[$k]->channel_logo = $channel_logo;
			}
		}

		// sort merged activities by most recent date, if necessary
		if ($activities) {
			// build a map of dates
			$dates_map = [];
			foreach ($activities as $k => $activity) {
				if (in_array('guest_messages', $type) && !empty($activity->dt)) {
					// use the date when the message was received
					$dates_map[$k] = strtotime($activity->dt);
					// set thread last update date to message date
					$activities[$k]->last_updated = $activity->dt;
				} else {
					// in case of a message, use the thread last update date, if it's a review, that'd be its date
					$dates_map[$k] = strtotime($activity->last_updated);
				}
			}
			// sort map in descending order and build new values
			arsort($dates_map);
			$sorted_activities = [];
			foreach ($dates_map as $k => $v) {
				$sorted_activities[] = $activities[$k];
			}
			// assign sorted activities
			$activities = $sorted_activities;
		}

		// return the list of activity objects, if any
		return $activities;
	}

	/**
	 * Helper method to obtain the information about the room children fees and max guests.
	 * This method was originally implemented to support the Child Rates of Booking.com
	 * through their Content APIs. Such values are defined at property level, not at room-level
	 * but we need to grab the information at room-level for the Bulk Actions.
	 * 
	 * @param 	int 	$id_room 			the Vik Booking room id.
	 * @param 	string 	$from_date 			optional date-from (Y-m-d) for the update request.
	 * @param 	string 	$to_date 			optional date-to (Y-m-d) for the update request.
	 * @param 	array 	$bulk_adv_params 	the bulk action rates upload advanced settings.
	 * 
	 * @return 	mixed 	numeric array with children fees and max guests information, or false.
	 * 
	 * @since 	1.8.1
	 * @since 	1.8.11 	added support for date-from and date-to.
	 * @since 	1.8.16 	added argument $bulk_adv_params to apply currency conversion on children rates.
	 */
	public static function getRoomChildrenFees($id_room, $from_date = null, $to_date = null, array $bulk_adv_params = [])
	{
		$dbo = JFactory::getDbo();

		if (empty($id_room)) {
			return false;
		}

		$id_options = [];
		$q = "SELECT `id`,`idopt`,`toadult`,`tochild`,`totpeople` FROM `#__vikbooking_rooms` WHERE `id`=" . (int)$id_room . ";";
		$dbo->setQuery($q);
		$room_data = $dbo->loadAssoc();
		if (!$room_data) {
			return false;
		}

		if (empty($room_data['idopt'])) {
			return false;
		}

		$r_ido = explode(';', rtrim($room_data['idopt']));
		foreach ($r_ido as $ido) {
			if (!empty($ido) && !in_array($ido, $id_options)) {
				array_push($id_options, (int) $ido);
			}
		}
		if (!$id_options) {
			return false;
		}

		// check if costs per night will have to be exchanged into another currency
		$conversion_oper = '+';
		$conversion_rate = 0;
		if (!empty($bulk_adv_params['currency_conversion_rate'])) {
			$bulk_adv_params['currency_conversion_rate'] = (string) $bulk_adv_params['currency_conversion_rate'];
			if (strlen($bulk_adv_params['currency_conversion_rate']) > 1) {
				$conversion_oper = substr($bulk_adv_params['currency_conversion_rate'], 0, 1);
				$conversion_rate = (float) substr($bulk_adv_params['currency_conversion_rate'], 1);
			}
		}

		// load appropriate records for this room by excluding those who are not of interest
		$q = "SELECT * FROM `#__vikbooking_optionals` WHERE `id` IN (" . implode(", ", $id_options) . ") AND `ifchildren`=1 AND (LENGTH(`ageintervals`) > 0 OR `ageintervals` IS NOT NULL)";
		$dbo->setQuery($q);
		$fee_records = $dbo->loadAssocList();
		if (!$fee_records) {
			// no options for children
			return false;
		}

		// the first record fetched will be the so called "general" (room-rate-level) children fees
		$ageintervals = $fee_records[0];
		$split_ages = explode(';;', $ageintervals['ageintervals']);
		$age_range = [];
		foreach ($split_ages as $kg => $spage) {
			if (empty($spage)) {
				continue;
			}
			$parts = explode('_', $spage);
			if (isset($parts[3]) && $parts[3] === '%') {
				// percent fees of the adults rate are not supported (only room rate percent is allowed)
				continue;
			}
			if (count($parts) > 2 && strlen($parts[0]) > 0 && intval($parts[1]) > 0 && floatval($parts[2]) >= 0) {
				$ind = count($age_range);
				$age_range[$ind]['from'] 	  = intval($parts[0]);
				$age_range[$ind]['to'] 		  = intval($parts[1]);
				$age_range[$ind]['cost'] 	  = round((float) $parts[2], 2);
				$age_range[$ind]['pernight']  = (int) $ageintervals['perday'];
				$age_range[$ind]['pcentroom'] = isset($parts[3]) && $parts[3] === '%b' ? 1 : 0;
				// check if currency conversion should be applied
				if (!$age_range[$ind]['pcentroom'] && $age_range[$ind]['cost'] > 0 && $conversion_rate > 0) {
					$setcost = $age_range[$ind]['cost'];
					if ($conversion_oper == '+') {
						// increase rates
						$setcost = $setcost * (100 + $conversion_rate) / 100;
					} elseif ($conversion_oper == '-') {
						// lower rates
						$disc_op = $setcost * $conversion_rate / 100;
						$setcost -= $disc_op;
					}
					// overwrite children fee
					$age_range[$ind]['cost'] = round($setcost, 2);
				}
			}
		}

		if (!$age_range) {
			// the first children fees record (room-rate-level) must be valid and correctly defined
			return false;
		}

		// build data to be returned
		$children_fees_data = [
			(int)$room_data['totpeople'],
			(int)$room_data['toadult'],
			(int)$room_data['tochild'],
			$age_range,
		];

		if (!empty($from_date) && !empty($to_date)) {
			// convert Y-m-d dates into timestamps
			$from_ts = strtotime($from_date);
			$to_ts   = strtotime($to_date);

			// build another associative list of age intervals per date
			$date_age_buckets = [];

			foreach ($fee_records as $fee_record) {
				if (empty($fee_record['alwaysav'])) {
					// no validity dates defined
					continue;
				}
				$validity_dts = explode(';', $fee_record['alwaysav']);
				if (empty($validity_dts[0]) || empty($validity_dts[1]) || $validity_dts[1] <= $from_ts || $validity_dts[0] >= $to_ts) {
					// the validity dates of this children fees exceed the updated dates
					continue;
				}
				// dates matching, check the validity of the age buckets
				$split_ages = explode(';;', $fee_record['ageintervals']);
				$age_range = [];
				foreach ($split_ages as $spage) {
					if (empty($spage)) {
						continue;
					}
					$parts = explode('_', $spage);
					if (isset($parts[3]) && $parts[3] === '%') {
						// percent fees of the adults rate are not supported (only room rate percent is allowed)
						continue;
					}
					if (count($parts) > 2 && strlen($parts[0]) && intval($parts[1]) > 0 && floatval($parts[2]) >= 0) {
						$ind = count($age_range);
						$age_range[$ind]['from'] 	  = intval($parts[0]);
						$age_range[$ind]['to'] 		  = intval($parts[1]);
						$age_range[$ind]['cost'] 	  = round((float) $parts[2], 2);
						$age_range[$ind]['pernight']  = (int) $ageintervals['perday'];
						$age_range[$ind]['pcentroom'] = isset($parts[3]) && $parts[3] === '%b' ? 1 : 0;
						// check if currency conversion should be applied
						if (!$age_range[$ind]['pcentroom'] && $age_range[$ind]['cost'] > 0 && $conversion_rate > 0) {
							$setcost = $age_range[$ind]['cost'];
							if ($conversion_oper == '+') {
								// increase rates
								$setcost = $setcost * (100 + $conversion_rate) / 100;
							} elseif ($conversion_oper == '-') {
								// lower rates
								$disc_op = $setcost * $conversion_rate / 100;
								$setcost -= $disc_op;
							}
							// overwrite children fee
							$age_range[$ind]['cost'] = round($setcost, 2);
						}
					}
				}
				if ($age_range) {
					// push valid age buckets with validity dates information
					$date_age_buckets[] = [
						'from_date' => date('Y-m-d', $validity_dts[0]),
						'to_date' 	=> date('Y-m-d', $validity_dts[1]),
						'buckets' 	=> $age_range,
					];
				}
			}

			// always push another value to be returned when args are passed
			$children_fees_data[] = $date_age_buckets;
		}

		// return a numeric array to be used with list()
		return $children_fees_data;
	}

	/**
	 * Given a country name, 3-char or 2-char code, this method attempts
	 * to guess the best language to assign to the booking according to
	 * what languages are installed on the website. This way, cron jobs
	 * and any other email notification will be correctly and automatically
	 * sent to the guest in the proper language without any manual action.
	 * The e4jConnect servers may return the guest's locale, if available.
	 * In this case, we check if the given language tag is installed.
	 * 
	 * @param 	string 	$country 	the country name, 3-char or 2-char code.
	 * @param 	string 	$locale 	optional locale lang tag of the guest.
	 * 
	 * @return 	mixed 				the best lang-tag string to use or null.
	 * 
	 * @since 	1.8.3
	 */
	public static function guessBookingLangFromCountry($country, $locale = null)
	{
		if (empty($country)) {
			return null;
		}

		// get all the available languages
		$known_langs = self::getKnownLanguages();
		if (!is_array($known_langs) || !count($known_langs)) {
			return null;
		}

		// check if the booking included a supported "locale" for the guest
		if (!empty($locale)) {
			// make the locale compatible with the language tags format
			$locale = str_replace('_', '-', $locale);
			foreach ($known_langs as $ltag => $ldet) {
				if (stripos($ltag, $locale) !== false || stripos($locale, $ltag) !== false) {
					// we support this language tag, so we just use it
					return $ltag;
				}
			}
		}

		// build similarities with country-languages
		$similarities = array(
			'AU' => 'en',
			'GB' => 'en',
			'IE' => 'en',
			'NZ' => 'en',
			'US' => 'en',
			'CA' => array(
				'en',
				'fr',
			),
			'CL' => 'es',
			'AR' => 'es',
			'PE' => 'es',
			'MX' => 'es',
			'CR' => 'es',
			'CO' => 'es',
			'EC' => 'es',
			'BO' => 'es',
			'CU' => 'es',
			'VE' => 'es',
			'BE' => 'fr',
			'LU' => 'fr',
			'CH' => array(
				'de',
				'it',
				'fr',
			),
			'AT' => 'de',
			'GR' => 'el',
			'GL' => 'dk',
		);

		// fetch values from db
		$dbo = JFactory::getDbo();
		$q = "SELECT `country_name`, `country_3_code`, `country_2_code` FROM `#__vikbooking_countries` WHERE `country_name`=" . $dbo->quote($country) . " OR `country_3_code`=" . $dbo->quote($country) . " OR `country_2_code`=" . $dbo->quote($country);
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			return null;
		}
		$country_record = $dbo->loadAssoc();

		// assign country name/code versions
		$country_name  = $country_record['country_name'];
		$country_3char = strtoupper($country_record['country_3_code']);
		$country_2char = strtoupper($country_record['country_2_code']);

		// build an associative array of language tags and related match-score
		$langtags_score = array();
		foreach ($known_langs as $ltag => $ldet) {
			// default language tag score is 0 for no matches
			$langtags_score[$ltag] = 0;
			// get language and country codes
			$lang_country_codes = explode('-', str_replace('_', '-', strtoupper($ltag)));
			
			// check matches with the installed language details
			if ($lang_country_codes[0] == $country_2char || $lang_country_codes[0] == $country_3char) {
				// increase language tag score
				$langtags_score[$ltag]++;
			}
			if (!empty($lang_country_codes[1]) && ($lang_country_codes[1] == $country_2char || $lang_country_codes[1] == $country_3char)) {
				// increase language tag score
				$langtags_score[$ltag]++;
			}
			if (!empty($ldet['locale'])) {
				// sanitize locale for matching the 2-char code safely
				$ldet['locale'] = str_replace(array('standard', 'euro', 'iso', 'utf'), '', strtolower($ldet['locale']));
				if (stripos($ldet['locale'], $country_2char) !== false || stripos($ldet['locale'], $country_name) !== false) {
					// increase language tag score
					$langtags_score[$ltag]++;
				}
			}
			if (!empty($ldet['name']) && stripos($ldet['name'], $country_name) !== false) {
				// increase language tag score
				$langtags_score[$ltag]++;
			}
			if (!empty($ldet['nativeName']) && stripos($ldet['nativeName'], $country_name) !== false) {
				// increase language tag score
				$langtags_score[$ltag]++;
			}

			// check language similarities between countries
			if (isset($similarities[$country_2char])) {
				$spoken_tags = !is_array($similarities[$country_2char]) ? array($similarities[$country_2char]) : $similarities[$country_2char];
				// check if language tag(s) is available for this spoken language
				foreach ($spoken_tags as $spoken_tag) {
					if ($lang_country_codes[0] == strtoupper($spoken_tag)) {
						// increase language tag score
						$langtags_score[$ltag]++;
					}
				}
			}
		}

		// make sure at least one language tag has got some points
		if (max($langtags_score) === 0) {
			// no languages installed to honor this country
			return null;
		}

		// sort language tag scores
		arsort($langtags_score);

		// reset array pointer to the first (highest) element
		reset($langtags_score);

		// return the language tag with the highest score
		return key($langtags_score);
	}

	/**
	 * Returns the current appearance preference.
	 * 
	 * @return 	string 	light, auto or dark.
	 * 
	 * @since 	1.8.3
	 */
	public static function getAppearancePref()
	{
		$dbo = JFactory::getDbo();

		// auto is the default appearance preference
		$default_pref = 'auto';

		// accepted preferences
		$valid_pref = array(
			'auto',
			'light',
			'dark',
		);

		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='appearance_pref';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// missing record
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('appearance_pref', " . $dbo->quote($default_pref) . ");";
			$dbo->setQuery($q);
			$dbo->execute();

			return $default_pref;
		}

		$current_pref = $dbo->loadResult();

		return in_array($current_pref, $valid_pref) ? $current_pref : $default_pref;
	}

	/**
	 * According to the appearance preferences, the apposite
	 * CSS assets are loaded within the document.
	 * 
	 * @return 	mixed 	string light, auto or dark, false otherwise.
	 * 
	 * @since 	1.8.3
	 */
	public static function loadAppearancePreferenceAssets()
	{
		// get document object
		$document = JFactory::getDocument();

		// load current color scheme preference
		$current_pref = self::getAppearancePref();

		// define caching values
		$file_opt = array('version' => (defined('VIKCHANNELMANAGER_SOFTWARE_VERSION') ? VIKCHANNELMANAGER_SOFTWARE_VERSION : date('ymd')));

		// define file attributes
		$file_attr = array('id' => 'vcm-css-appearance-' . $current_pref);

		// apposite file path and URI
		$css_path = VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'vcm-appearance-' . $current_pref . '.css';
		$css_uri  = VCM_ADMIN_URI . 'assets/css/vcm-appearance-' . $current_pref . '.css';

		if (!is_file($css_path)) {
			// this preference does not require a specific stylesheet
			return false;
		}

		// load the apposite CSS file
		$document->addStyleSheet($css_uri, $file_opt, $file_attr);

		return $current_pref;
	}

	/**
	 * Updates (or creates) the database record containing the
	 * information about the subscription expiration details.
	 * 
	 * @param 	object 	$expiration 	the object to store.
	 * 
	 * @return 	bool 	true on success, false otherwise.
	 * 
	 * @since 	1.8.3
	 */
	public static function updateExpirationDetails($expiration)
	{
		if (!is_array($expiration) && !is_object($expiration)) {
			return false;
		}

		// update expiration details
		VCMFactory::getConfig()->set('expiration_details', $expiration);

		/**
		 * Trigger event to allow plugins to detect the updated expiration details.
		 * 
		 * @since 	1.8.21
		 */
		VCMFactory::getPlatform()->getDispatcher()->trigger('onUpdateExpirationDetails', [(array) $expiration]);

		return true;
	}

	/**
	 * Gets and decoded the object containing the expiration details.
	 * 
	 * @return 	mixed 	JSON decoded object on success, false otherwise.
	 * 
	 * @since 	1.8.3
	 */
	public static function loadExpirationDetails()
	{
		// cache value in static var
		static $expiration_details = null;

		if ($expiration_details !== null) {
			return is_object($expiration_details) ? $expiration_details : false;
		}

		$dbo = JFactory::getDbo();

		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='expiration_details'";
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// missing record
			return false;
		}
		$expiration_details = json_decode($dbo->loadResult());

		return is_object($expiration_details) ? $expiration_details : false;
	}

	/**
	 * Downloads and updates the expiration details.
	 * 
	 * @return 	bool 	true on success, false otherwise.
	 * 
	 * @since 	1.8.3
	 */
	public static function downloadExpirationDetails()
	{
		$api_key = self::getApiKey(true);

		if (empty($api_key)) {
			return false;
		}

		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=exp&c=generic";

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager EXP Request e4jConnect.com - VikBooking -->
<ExpiringRQ xmlns="http://www.e4jconnect.com/schemas/exprq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch question="subscription" channel="generic"/>
</ExpiringRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();

		if ($e4jC->getErrorNo() || substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			return false;
		}

		$expiration_details = json_decode($rs);
		if (is_object($expiration_details) && isset($expiration_details->ymd)) {
			// update value on db
			return self::updateExpirationDetails($expiration_details);
		}

		return false;
	}

	/**
	 * Tells whether the expiration reminders are enabled.
	 * 
	 * @return 	bool 	true if enabled, false otherwise.
	 * 
	 * @since 	1.8.3
	 */
	public static function expirationReminders()
	{
		$dbo = JFactory::getDbo();

		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='expiration_reminders'";
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// missing record, return true (enabled) by default
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('expiration_reminders', '1');";
			$dbo->setQuery($q);
			$dbo->execute();

			return true;
		}
		
		return ((int)$dbo->loadResult() > 0);
	}

	/**
	 * Checks whether an expiration reminder is needed.
	 * 
	 * @return 	mixed 	false if reminder not needed, or info array to expiration.
	 * 
	 * @since 	1.8.3
	 */
	public static function shouldRemindExpiration()
	{
		static $close_expiration = null;

		if (is_array($close_expiration)) {
			return $close_expiration;
		}

		if (!self::expirationReminders()) {
			// reminders are disabled
			return false;
		}

		$dbo = JFactory::getDbo();

		// grab the last expiration check timestamp
		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param`='expiration_last_check';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// create missing record
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('expiration_last_check', ".$dbo->quote(time()).");";
			$dbo->setQuery($q);
			$dbo->execute();
			// never remind when never checked before
			return false;
		}
		$last_check_ts = $dbo->loadResult();

		// we check once per day, at most
		if (date('Y-m-d', $last_check_ts) == date('Y-m-d')) {
			// already checked today
			return false;
		}

		// get the expiration details object
		$expiration_details = self::loadExpirationDetails();
		if (!is_object($expiration_details) || !isset($expiration_details->ymd)) {
			// nothing to compare against
			return false;
		}

		// update last check for the next execution
		$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=" . $dbo->quote(time()) . " WHERE `param`='expiration_last_check';";
		$dbo->setQuery($q);
		$dbo->execute();

		// count days to expiration date
		$today_obj = new DateTime(date('Y-m-d'));
		$expd_obj  = new DateTime($expiration_details->ymd);

		$diff_to_exp = $today_obj->diff($expd_obj);
		if (!$diff_to_exp) {
			// invalid dates, expected was a DateInterval object
			return false;
		}

		// this would be a negative number if less than 0 (%r)
		$days_to_exp = (int)$diff_to_exp->format('%r%a');

		// start reminding two weeks before the actual expiration date
		$reminder_lim = 14;

		if ($days_to_exp > $reminder_lim) {
			// still enough time before the expiration
			return false;
		}

		// cache information in static var
		$close_expiration = array(
			'days_to_exp' 	 => $days_to_exp,
			'expiration_ymd' => $expiration_details->ymd,
		);

		return $close_expiration;
	}

	/**
	 * Tells whether the VCM API Key is expired.
	 * 
	 * @return 	bool 	true if service is expired, false otherwise.
	 * 
	 * @since 	1.8.3
	 */
	public static function isSubscriptionExpired()
	{
		// get the expiration details object
		$expiration_details = self::loadExpirationDetails();
		if (!is_object($expiration_details) || !isset($expiration_details->ymd)) {
			// nothing to check
			return false;
		}

		// convert expiration date to an UTC timestamp
		$utc_exp_ts = strtotime($expiration_details->ymd . ' UTC');

		// get current timestamp in UTC
		$utc_now_ts = strtotime('now UTC');

		return ($utc_now_ts > $utc_exp_ts);
	}

	/**
	 * Tells whether a reminder was sent for a close expiration date.
	 * This method is called at any execution of VCM.
	 * 
	 * @return 	bool 	true if email reminder was sent, false otherwise.
	 * 
	 * @since 	1.8.3
	 */
	public static function checkSubscriptionReminder()
	{
		$close_expiration = self::shouldRemindExpiration();

		if (!is_array($close_expiration)) {
			return false;
		}

		// prepare the email reminder
		$result = false;
		try {
			// require the main VBO library
			if (!class_exists('VikBooking')) {
				require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php";
			}

			// use the VBO application class to get the email address(es) to notify
			$vbo_app = VikBooking::getVboApplication();
			$admail = VikBooking::getAdminMail();
			$sdmail = VikBooking::getSenderMail();

			// grab also the VCM admin mail
			$vcm_admail = self::getConfigurationRecord('emailadmin');
			if (!empty($vcm_admail) && strpos($admail, $vcm_admail) === false) {
				// push (as a comma separated string) also the VCM admin email
				$admail .= ',' . $vcm_admail;
			}

			// send the email reminder (plain text)
			$reminder_subject = JText::_('VCM_EXPIRATION_REMINDER_MSUBJ');
			$reminder_content = JText::sprintf('VCM_EXPIRATION_REMINDER_MCONT', JUri::root(), $close_expiration['days_to_exp'], $close_expiration['expiration_ymd']);

			$result = $vbo_app->sendMail($sdmail, $sdmail, $admail, '', $reminder_subject, $reminder_content, false);
		} catch (Exception $e) {
			// do nothing
		}

		return $result;
	}

	/**
	 * Checks whether the system has got the Google Hotel channel.
	 * 
	 * @return 	bool 	whether Google Hotel Ads is available.
	 * 
	 * @since 	1.8.4
	 */
	public static function hasGoogleHotelChannel()
	{
		$dbo = JFactory::getDbo();
		
		$q = "SELECT `name`, `uniquekey` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=" . $dbo->quote(VikChannelManagerConfig::GOOGLEHOTEL);
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$dbo->getNumRows()) {
			// the channel is not available
			return false;
		}

		return true;
	}

	/**
	 * Stores in or gets from the database the Hotel Inventory ID.
	 * 
	 * @param 	int 	$setvalue 		 if specified, the method will set the given ID.
	 * @param 	array 	$send_data_opts  associative array of multiple account data.
	 * 
	 * @return 	int 	the Hotel Inventory ID, either present or updated.
	 * 
	 * @since 	1.8.4
	 * @since 	1.8.6 	added second argument for multiple hotel accounts
	 */
	public static function getHotelInventoryID($setvalue = null, $send_data_opts = [])
	{
		$dbo = JFactory::getDbo();

		$current_id = self::getConfigurationRecord('hotel_inventory_id');
		if ($current_id === false) {
			// missing configuration record
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('hotel_inventory_id', '');";
			$dbo->setQuery($q);
			$dbo->execute();
			$current_id = 0;
		}

		if ($setvalue) {
			// update configuration record
			if (empty($send_data_opts)) {
				// only if this is the main hotel account
				$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=" . $dbo->quote($setvalue) . " WHERE `param`='hotel_inventory_id';";
				$dbo->setQuery($q);
				$dbo->execute();
			}

			// check if the params for the Google's channel are still empty
			$q = "SELECT * FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=" . $dbo->quote(VikChannelManagerConfig::GOOGLEHOTEL);
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows()) {
				$google_records = $dbo->loadAssocList();

				// check if a new multiple hotel account was just submitted
				$force_new_multi_account = false;
				if (!empty($send_data_opts) && !empty($send_data_opts['multi_id']) && !empty($send_data_opts['type']) && !strcasecmp($send_data_opts['type'], 'new')) {
					// a new multi-hotel account was added
					if (!empty($send_data_opts['channel']) && $send_data_opts['channel'] == VikChannelManagerConfig::GOOGLEHOTEL) {
						// set the newly added hotel inventory ID as a multiple account
						$force_new_multi_account = true;
					}
				}

				foreach ($google_records as $record) {
					$found_empty_params = false;
					if (empty($record['params'])) {
						continue;
					}
					$params = json_decode($record['params'], true);
					if (!is_array($params) || !count($params)) {
						continue;
					}
					foreach ($params as $param_name => $param_val) {
						if (empty($param_val) || $force_new_multi_account === true) {
							// set given hotel inventory ID
							$params[$param_name] = 'G-' . $setvalue;
							// turn flag on
							$found_empty_params = true;
						}
						// we only check the very first param
						break;
					}
					if ($found_empty_params) {
						// update channel record params
						$q = "UPDATE `#__vikchannelmanager_channel` SET `params`=" . $dbo->quote(json_encode($params)) . " WHERE `id`=" . (int)$record['id'] . ";";
						$dbo->setQuery($q);
						$dbo->execute();
						// break the process because we've got one pair of credentials per channel
						break;
					}
				}
			}

			return $setvalue;
		}

		return $current_id;
	}

	/**
	 * Stores in the database the Hotel Inventory submission date.
	 * Can also be used to retrieve the current creation datetime.
	 * 
	 * @param 	string 	$dtime 	the submission date time, or null
	 * 
	 * @return 	mixed 	string with creation datetime or null
	 * 
	 * @since 	1.8.4
	 */
	public static function setHotelInventoryDate($dtime = null)
	{
		$dbo = JFactory::getDbo();

		$creation_dt = self::getConfigurationRecord('hotel_inventory_creation_dt');
		if ($creation_dt === false && !empty($dtime)) {
			// missing configuration record, create it
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('hotel_inventory_creation_dt', " . $dbo->quote($dtime) . ");";
			$dbo->setQuery($q);
			$dbo->execute();
			$creation_dt = $dtime;
		} elseif (!empty($dtime)) {
			// update configuration record
			$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=" . $dbo->quote($dtime) . " WHERE `param`='hotel_inventory_creation_dt';";
			$dbo->setQuery($q);
			$dbo->execute();
			$creation_dt = $dtime;
		}

		return $creation_dt ? $creation_dt : null;
	}

	/**
	 * Stores in the database the Property Data submission date.
	 * Can also be used to retrieve the current creation datetime.
	 * 
	 * @param 	string 	$dtime 	the submission date time, or null
	 * 
	 * @return 	mixed 	string with creation datetime or null
	 * 
	 * @since 	1.8.4
	 */
	public static function setPropertyDataDate($dtime = null)
	{
		$dbo = JFactory::getDbo();

		$creation_dt = self::getConfigurationRecord('property_data_creation_dt');
		if ($creation_dt === false && !empty($dtime)) {
			// missing configuration record, create it
			$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES('property_data_creation_dt', " . $dbo->quote($dtime) . ");";
			$dbo->setQuery($q);
			$dbo->execute();
			$creation_dt = $dtime;
		} elseif (!empty($dtime)) {
			// update configuration record
			$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=" . $dbo->quote($dtime) . " WHERE `param`='property_data_creation_dt';";
			$dbo->setQuery($q);
			$dbo->execute();
			$creation_dt = $dtime;
		}

		return $creation_dt ? $creation_dt : null;
	}

	/**
	 * Returns an instance of the VBO Geocoding class, if available.
	 * 
	 * @param 	bool 	$supported 	if true returns the instance only if configured.
	 * 
	 * @return 	mixed 	VikBookingHelperGeocoding object or null.
	 * 
	 * @since 	1.8.4
	 */
	public static function getGeocodingInstance($supported = true)
	{
		$geocoding = null;
		try {
			// require the main VBO library
			if (!class_exists('VikBooking')) {
				require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php";
			}

			if (method_exists('VikBooking', 'getGeocodingInstance')) {
				$geocoding = VikBooking::getGeocodingInstance();
				// make sure the service has been configured
				if ($supported && !$geocoding->isSupported()) {
					$geocoding = null;
				}
			}
		} catch (Exception $e) {
			// do nothing
		}

		return $geocoding;
	}

	/**
	 * Returns a list of all mandatory city taxes or fees with
	 * the information to which room IDs they are applicable.
	 * 
	 * @param 	array 	$rooms 		an optional list of room IDs to check.
	 * @param 	bool 	$pet_fees 	true for fetching also the pet fees.
	 * 
	 * @return 	array 	list of mandatory taxes and fees.
	 * 
	 * @since 	1.8.4
	 * @since 	1.8.16 	added $pet_fees argument (mainly for Vrbo API).
	 */
	public static function getAllMandatoryFees($rooms = [], $pet_fees = false)
	{
		$dbo = JFactory::getDbo();

		$mandatory_fees   = [];
		$optional_fees 	  = [];
		$room_options_rel = [];

		// load options for all rooms
		$q = "SELECT `id`, `idopt` FROM `#__vikbooking_rooms`" . (count($rooms) ? ' WHERE `id` IN (' . implode(', ', $rooms) . ')' : '') . ";";
		$dbo->setQuery($q);
		$records = $dbo->loadAssocList();
		if ($records) {
			foreach ($records as $room) {
				if (empty($room['idopt'])) {
					continue;
				}
				$r_ido = explode(';', rtrim($room['idopt']));
				foreach ($r_ido as $ido) {
					if (empty($ido)) {
						continue;
					}
					if (!isset($room_options_rel[$ido])) {
						$room_options_rel[$ido] = [];
					}
					// push room ID for the current option
					array_push($room_options_rel[$ido], $room['id']);
				}
			}
		}
		if (!$room_options_rel) {
			// no rooms with options, of any type, assigned
			return [];
		}

		if ($pet_fees) {
			/**
			 * Load all non-mandatory options and filter them by "pet_fee":1.
			 * 
			 * @since 	1.8.16
			 */
			$q = "SELECT * FROM `#__vikbooking_optionals` WHERE `forcesel`=0 AND `ifchildren`=0 AND `is_citytax`=0;";
			$dbo->setQuery($q);
			$optional_fees = $dbo->loadAssocList();
			foreach ($optional_fees as $optfk => $optfee) {
				if (empty($optfee['oparams'])) {
					// not a pet fee
					unset($optional_fees[$optfk]);
					continue;
				}
				$opt_params = json_decode($optfee['oparams'], true);
				if (!is_array($opt_params) || empty($opt_params['pet_fee'])) {
					// not a pet fee
					unset($optional_fees[$optfk]);
					continue;
				}
			}
		}

		// grab all tourist/city tax or fees (like cleaning fees) options
		// exclude fees only for children, and get only the ones always selected
		$q = "SELECT * FROM `#__vikbooking_optionals` WHERE `forcesel`=1 AND `ifchildren`=0 AND (`is_citytax`=1 OR `is_fee`=1);";
		$dbo->setQuery($q);
		$mandatory_fees = $dbo->loadAssocList();

		if ($optional_fees) {
			// merge optional fees (if any) with mandatory fees (if any)
			$mandatory_fees = array_merge($mandatory_fees, $optional_fees);
		}

		// define the applicable rooms and adjust data
		foreach ($mandatory_fees as $k => $fee) {
			if (empty($room_options_rel[$fee['id']])) {
				// no rooms are assigned to this option
				unset($mandatory_fees[$k]);
				continue;
			}
			$mandatory_fees[$k]['applicable_rooms'] = $room_options_rel[$fee['id']];
			// check availability (stay) dates
			if (!empty($fee['alwaysav'])) {
				list($stay_from_ts, $stay_to_ts) = explode(';', $fee['alwaysav']);
				// overwrite timestamps to date strings in Y-m-d format to avoid timezone issues
				$mandatory_fees[$k]['alwaysav'] = date('Y-m-d', $stay_from_ts) . ';' . date('Y-m-d', $stay_to_ts);
			}
		}

		if (!$mandatory_fees) {
			// no mandatory options assigned to at least one room
			return [];
		}

		return array_values($mandatory_fees);
	}

	/**
	 * Performs a JSON request via POST to the e4jConnect servers to transmit
	 * the Property Data feed. Supports both Google Hotel and Google Vacation Rentals.
	 * 
	 * @param 	array 	$module 	the channel record for the transmission.
	 * @param 	array 	$rooms 		list of VBO room IDs to fetch and transmit.
	 * 
	 * @return 	mixed 	string with error, false or request body object on success.
	 * 
	 * @since 	1.8.4
	 * @since 	1.8.6 	added support for multiple hotel accounts.
	 * @since 	1.8.13 	return value is now the request body object in case of success.
	 * @since 	1.9.4 	added support to Google Vacation Rentals.
	 */
	public static function transmitPropertyData($module, $rooms = [])
	{
		$dbo = JFactory::getDbo();

		$supported_channels = [
			VikChannelManagerConfig::GOOGLEHOTEL,
			VikChannelManagerConfig::GOOGLEVR,
		];

		if (!is_array($module) || empty($module['uniquekey']) || !in_array($module['uniquekey'], $supported_channels)) {
			return false;
		}

		$hinv_id = '';
		$channel_name = '';
		if ($module['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL) {
			// make sure the hotel inventory ID has been received
			$hinv_id = self::getHotelInventoryID();
			// set channel name
			$channel_name = 'Google Hotel';
		} elseif ($module['uniquekey'] == VikChannelManagerConfig::GOOGLEVR) {
			// get the host account ID
			$hinv_id = VCMFactory::getConfig()->get('account_key_' . VikChannelManagerConfig::GOOGLEVR);
			// set channel name
			$channel_name = 'Google Vacation Rentals';
		}
		
		if (empty($hinv_id)) {
			// no account ID
			return false;
		}

		if ($module['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL) {
			// the current module should also have the hotelid property
			if (empty($module['params'])) {
				return false;
			}

			// make sure the params have been decoded
			if (is_string($module['params'])) {
				$module['params'] = json_decode($module['params'], true);
				$module['params'] = is_array($module['params']) ? $module['params'] : [];
			}

			if (!is_array($module['params']) || !$module['params']) {
				// broken structure
				return false;
			}

			if (empty($module['params']['hotelid'])) {
				// missing main param
				return false;
			}

			// check if we are mapping the rooms of a multi-hotel-account
			if (strpos($module['params']['hotelid'], $hinv_id) === false) {
				$multi_hotel_data = VCMGhotelMultiaccounts::loadFromId(preg_replace("/[^0-9]+/", '', $module['params']['hotelid']), $is_remote_id = true);
				if (!$multi_hotel_data || empty($multi_hotel_data['account_id'])) {
					// what are we mapping?
					return 'Could not find a valid hotel ID contracted with Google';
				}
				// force the hotel inventory ID to the multi account
				$hinv_id = preg_replace("/[^0-9]+/", '', $multi_hotel_data['account_id']);
			}
		}

		// build necessary values
		$rplan_details = [];

		// load room details from VBO
		if (!$rooms) {
			// find rooms involved from mapping information
			$q = "SELECT `idroomvb` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`={$module['uniquekey']} AND `prop_params` LIKE " . $dbo->quote("%{$hinv_id}%");
			$dbo->setQuery($q);
			$records = $dbo->loadAssocList();
			foreach ($records as $record) {
				if (!in_array($record['idroomvb'], $rooms)) {
					array_push($rooms, $record['idroomvb']);
				}
			}
		}

		if (!$rooms) {
			return 'No room relations found for ' . $channel_name;
		}

		$q = "SELECT * FROM `#__vikbooking_rooms` WHERE `id` IN (" . implode(', ', array_map('intval', $rooms)) . ");";
		$dbo->setQuery($q);
		$room_details = $dbo->loadAssocList();
		if (!$room_details) {
			return 'No valid rooms found for ' . $channel_name;
		}

		// load rate plans information
		$q = "SELECT `p`.*, `t`.`aliq` FROM `#__vikbooking_prices` AS `p` LEFT JOIN `#__vikbooking_iva` `t` ON `p`.`idiva`=`t`.`id`;";
		$dbo->setQuery($q);
		$records = $dbo->loadAssocList();
		if (!$records) {
			return 'No rate plans found for ' . $channel_name;
		}
		foreach ($records as $rplan) {
			$rplan_details[$rplan['id']] = $rplan;
		}
		$all_rplan_ids = array_keys($rplan_details);

		// check allowable rate plans for each room-type
		foreach ($room_details as $k => $room) {
			$q = "SELECT DISTINCT `idprice` FROM `#__vikbooking_dispcost` WHERE `idroom`={$room['id']};";
			$dbo->setQuery($q);
			$tot_rplans = $dbo->loadAssocList();
			if ($tot_rplans && count($tot_rplans) < count($rplan_details)) {
				// this room is only assigned to a few rate plans
				$room_rplans = [];
				foreach ($tot_rplans as $rec) {
					array_push($room_rplans, $rec['idprice']);
				}
				// define allowable rate plans for this room
				$room_details[$k]['allowable_rplans'] = $room_rplans;
			}
		}

		// read the default language code (2-char)
		$tn_obj = null;
		$curr_code = null;
		$lang_code = JFactory::getLanguage()->getTag();
		try {
			$tn_obj = VikBooking::getTranslator();
			$lang_code = $tn_obj->getDefaultLang();
			$curr_code = VikBooking::getCurrencyName();
		} catch (Exception $e) {
			// do nothing
		}
		$lang_code = substr(strtolower($lang_code), 0, 2);

		// check if prices are inclusive of tax
		$is_tax_inclusive = (int) self::pricesTaxInclusive();
		if (!empty($module['settings'])) {
			$ch_settings = json_decode($module['settings'], true);
			if (is_array($ch_settings) && isset($ch_settings['price_compare']) && !empty($ch_settings['price_compare']['value'])) {
				// use the current channel setting, rather than the VBO setting
				$is_tax_inclusive = $ch_settings['price_compare']['value'] == 'VCM_PRICE_COMPARE_TAX_INCL' ? 1 : 0;
			}
		}

		// build the JSON request body
		$rq_body = new stdClass;
		$rq_body->property_id 	 = $hinv_id;
		$rq_body->notify_url  	 = JUri::root();
		$rq_body->api_key  	  	 = self::getApiKey(true);
		$rq_body->lang_code   	 = $lang_code;
		$rq_body->currency   	 = $curr_code;
		$rq_body->tax_inclusive  = $is_tax_inclusive;
		$rq_body->room_types  	 = [];
		$rq_body->rate_plans  	 = [];
		$rq_body->translations 	 = [];
		$rq_body->mandatory_fees = self::getAllMandatoryFees($rooms);

		// add room-types
		foreach ($room_details as $room) {
			$room_data = new stdClass;
			$room_data->id = $room['id'];
			$room_data->name = $room['name'];
			$room_data->description = strip_tags((!empty($room['smalldesc']) ? $room['smalldesc'] : (string)$room['info']));
			$room_data->max_tot_guests = (int) $room['totpeople'];
			$room_data->min_tot_guests = (int) $room['mintotpeople'];
			$room_data->max_tot_adults = (int) $room['toadult'];
			$room_data->min_tot_adults = (int) $room['fromadult'];
			$room_data->max_tot_children = (int) $room['tochild'];
			$room_data->min_tot_children = (int) $room['fromchild'];
			// check if this room-type only supports a few rate plans
			if (!empty($room['allowable_rplans'])) {
				$room_data->allowable_rplans = $room['allowable_rplans'];
			}
			// build list of photos
			$main_photo_url = !empty($room['img']) ? VBO_SITE_URI . 'resources/uploads/' . $room['img'] : null;
			$extra_images = [];
			if (!empty($room['moreimgs'])) {
				$moreimages = explode(';;', $room['moreimgs']);
				$imgcaptions = !empty($room['imgcaptions']) ? (array) json_decode($room['imgcaptions'], true) : [];
				foreach ($moreimages as $iind => $mimg) {
					if (empty($mimg)) {
						continue;
					}
					// push photo
					array_push($extra_images, [
						'url' => VBO_SITE_URI . 'resources/uploads/big_' . $mimg,
						'caption' => (!empty($imgcaptions[$iind]) ? $imgcaptions[$iind] : null),
					]);
				}
			}
			if (!$extra_images && !empty($main_photo_url)) {
				// push photo
				array_push($extra_images, [
					'url' => $main_photo_url,
					'caption' => null,
				]);
			}
			$room_data->photos = $extra_images;
			// push room-type
			array_push($rq_body->room_types, $room_data);
		}

		// add rate plans
		foreach ($rplan_details as $rplan) {
			$rplan_data = new stdClass;
			$rplan_data->id = $rplan['id'];
			$rplan_data->name = $rplan['name'];
			$rplan_data->aliq = (float) $rplan['aliq'];
			$rplan_data->breakfast_included = (int) $rplan['breakfast_included'];
			$rplan_data->free_cancellation = (int) $rplan['free_cancellation'];
			$rplan_data->canc_deadline = (int) $rplan['canc_deadline'];
			$rplan_data->canc_policy = strip_tags($rplan['canc_policy']);
			$rplan_data->minlos = (int) $rplan['minlos'];
			$rplan_data->minhadv = (int) $rplan['minhadv'];
			// push rate plan
			array_push($rq_body->rate_plans, $rplan_data);
		}

		/**
		 * Handle localizations (translations) for rooms and rate plans.
		 * This is done only if multiple content-languages are enabled.
		 */
		if ($tn_obj) {
			$all_langs = $tn_obj->getLanguagesList();
			if (count($all_langs) > 1) {
				// multiple languages available
				$room_tns  = [];
				$rplan_tns = [];
				$rplan_details_vals = array_values($rplan_details);
				foreach ($all_langs as $lang_key => $lang_data) {
					$use_lang_code = substr(strtolower($lang_key), 0, 2);
					if ($use_lang_code == $lang_code) {
						// this is the default lang
						continue;
					}
					// attempt to apply translations on rooms
					$tmp_rooms = $room_details;
					$tn_obj->translateContents($tmp_rooms, '#__vikbooking_rooms', [], [], $lang_key);
					if ($tmp_rooms != $room_details) {
						// translations found
						if (!isset($room_tns[$use_lang_code])) {
							$room_tns[$use_lang_code] = [];
						}
						foreach ($tmp_rooms as $tn_room) {
							// photo captions
							$imgcaptions = !empty($tn_room['imgcaptions']) ? json_decode($tn_room['imgcaptions'], true) : [];
							$imgcaptions = !is_array($imgcaptions) ? [] : $imgcaptions;
							// push translated values
							$room_tns[$use_lang_code][$tn_room['id']] = [
								'id' 	=> $tn_room['id'],
								'name' 	=> $tn_room['name'],
								'descr' => strip_tags((!empty($tn_room['smalldesc']) ? $tn_room['smalldesc'] : $tn_room['info'])),
								'captions' => $imgcaptions,
							];
						}
					}
					// attempt to apply translations on rate plans
					$tmp_rplans = $rplan_details_vals;
					$tn_obj->translateContents($tmp_rplans, '#__vikbooking_prices', [], [], $lang_key);
					if ($tmp_rplans != $rplan_details_vals) {
						// translations found
						if (!isset($rplan_tns[$use_lang_code])) {
							$rplan_tns[$use_lang_code] = [];
						}
						foreach ($tmp_rplans as $tn_rplan) {
							// push translated values
							$rplan_tns[$use_lang_code][$tn_rplan['id']] = [
								'id'   	=> $tn_rplan['id'],
								'name' 	=> $tn_rplan['name'],
								'descr' => strip_tags($tn_rplan['canc_policy']),
							];
						}
					}
				}
				if ($room_tns) {
					// set translations for rooms
					$rq_body->translations['rooms'] = $room_tns;
				}
				if ($rplan_tns) {
					// set translations for rate plans
					$rq_body->translations['rate_plans'] = $rplan_tns;
				}
			}
		}

		// execute the JSON request depending on the channel

		if ($module['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL) {
			// endpoint for Google Hotel
			$e4jc_url = "https://e4jconnect.com/google-hotel/property-data";

			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields(json_encode($rq_body));
			$e4jC->setHttpHeader(['Content-Type: application/json']);
			$e4jC->setTimeout(600);
			$e4jC->slaveEnabled = true;
			$rs = $e4jC->exec();

			if ($e4jC->getErrorNo()) {
				return 'cURL error: ' . @curl_error($e4jC->getCurlHeader());
			}

			// decode the response
			$result = json_decode($rs);
			if (!$result || !$result->status) {
				return $result->error;
			}

			// the request was successful, update the datetime of the property data submission
			self::setPropertyDataDate(date('Y-m-d H:i:s'));
		} elseif ($module['uniquekey'] == VikChannelManagerConfig::GOOGLEVR) {
			// endpoint for Google Vacation Rentals (one listing expected)
			$google_vr_listing_id = $rq_body->room_types[0]->id ?? 0;
			$endpoint = "https://e4jconnect.com/channelmanager/v2/google/vacation-rentals/listings/{$google_vr_listing_id}/property-data";

			// adjust payload
			unset($rq_body->api_key);

			// start the transporter
			$transporter = new E4jConnectRequest($endpoint, true);
	        $transporter->setBearerAuth(self::getApiKey(true), 'application/json')
	        	->setTimeout(600)
	            ->setPostFields($rq_body);

	        try {
	            // perform the operation
	            $transporter->fetch('POST', 'json');
	        } catch (Exception $e) {
	            // return the error
	            return sprintf('Google Vacation Rentals error (%s): %s', $e->getCode(), $e->getMessage());
	        }

	        // the request was successful, update the time for the last transaction request
	        VCMFactory::getConfig()->set('transaction_last_dt_' . VikChannelManagerConfig::GOOGLEVR, date('c'));
		}

		// success, return the request body object
		return $rq_body;
	}

	/**
	 * Transmits the listings mapping information for the relevant channel.
	 * Originally introduced for Airbnb in order to allow third-party plugins
	 * to perform this request not only after completing the rooms mapping
	 * procedure from the back-end (i.e. When importing a Trial backup).
	 * 
	 * @param 	array 	$channel 	the involved channel.
	 * @param 	array 	$listings 	the associative list of listings (ID => Name).
	 * 
	 * @return 	bool 	true if success, false otherwise.
	 * 
	 * @throws 	Exception
	 * 
	 * @since 	1.8.19
	 */
	public static function transmitListingsMapping(array $channel, array $listings)
	{
		if ($channel['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI) {
			// perform the operation for Airbnb
			$listings_mapped = [];

			foreach ($listings as $listing_id => $listing_name) {
				$listing_id   = htmlspecialchars($listing_id, ENT_XML1, 'UTF-8');
				$listing_name = htmlspecialchars($listing_name, ENT_XML1, 'UTF-8');
				array_push($listings_mapped, "\t\t" . '<Listing id="' . $listing_id . '"><![CDATA[' . $listing_name . ']]></Listing>');
			}

			// channel name
			$ch_name = !empty($channel['name']) ? $channel['name'] : 'airbnbapi';

			// grab the user_id from params
			$ch_params = json_decode($channel['params'], true);
			$host_user_id = is_array($ch_params) && !empty($ch_params['user_id']) ? $ch_params['user_id'] : '';

			// the request is only for the Master
			$e4jc_url = "https://e4jconnect.com/channelmanager/?r=lstmap&c={$ch_name}";

			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager LSTMAP Request e4jConnect.com - VikBooking -->
<ListingMappingRQ xmlns="http://www.e4jconnect.com/schemas/lstmaprq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . self::getApiKey(true) . '"/>
	<ListingMapping account="' . $host_user_id . '" lang="' . JFactory::getLanguage()->getTag() . '">
' . implode("\n", $listings_mapped) . '
	</ListingMapping>
</ListingMappingRQ>';

			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$rs = $e4jC->exec();

			if ($e4jC->getErrorNo()) {
				throw new Exception(self::getErrorFromMap($e4jC->getErrorMsg()), 500);
			}

			if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
				throw new Exception(self::getErrorFromMap($rs), 500);
			}

			return true;
		}

		// channel not supported
		throw new Exception('Channel not supported', 400);
	}

	/**
	 * Tells whether the prices in VBO are inclusive of taxes.
	 * 
	 * @return 	bool 	true if inclusive (after) of tax, or false.
	 * 
	 * @since 	1.8.4
	 */
	public static function pricesTaxInclusive()
	{
		// use Vik Booking to return the current setting
		$inclusive = true;

		try {
			if (!class_exists('VikBooking')) {
				require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';
			}
			$inclusive = VikBooking::ivaInclusa(true);
		} catch (Exception $e) {
			// do nothing
		}

		return $inclusive;
	}

	/**
	 * Wraps all layout files onto /admin/helpers/layouts.
	 * Returns an instance of JLayoutFile to render the file.
	 * 
	 * @param 	string 	$layoutId 	the identifier of the layout file to fetch.
	 * 
	 * @return 	JLayoutFile
	 * 
	 * @since 	1.8.4
	 */
	public static function getLayoutFile($layoutId)
	{
		$file = new JLayoutFile($layoutId);
		$file->addIncludePath(VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'layouts');

		return $file;
	}
}

class Vigenere 
{
	
	private $char_map;
	
	public function __construct($char_map) 
	{
		$this->char_map = $char_map;
	}
	
	public function encrypt($word, $key) 
	{
		$key = $this->prepare_key($key, strlen($word));
		
		$enc = '';
		
		for ($i = 0; $i < strlen($word); $i++) {
			$a = $this->char_map[$word[$i]];
			$b = $this->char_map[$key[$i]];
			$c = $a+$b;
			$enc .= (($c >= count($this->char_map)) ? ($c-count($this->char_map)) : $c);
		}
		
		return $enc;
	}
	
	public function decrypt($enc, $key) 
	{
		$key = $this->prepare_key($key, strlen($enc));
		
		$word = '';
		
		for ($i = 0; $i < strlen($enc); $i++) {
			$a = $this->char_map[$enc[$i]];
			$b = $this->char_map[$key[$i]];
			$c = $a-$b;
			$word .= (($c < 0) ? ($c+count($this->char_map)) : $c);
		}
		
		return $word;
	}
	
	private function prepare_key($key, $len) 
	{
		if (empty($key)) {
			$key = substr(implode('', array_keys($this->char_map)), 0, 3);
		}
		
		$i = 0;
		$n = strlen($key);
		while(strlen($key) != $len) {
			$key .= $key[$i];
			$i = ($i+1)%$n;
		}
		return $key;
	}
	
}

/**
 * HTTP Transporter for the E4jConnect central servers to securely exchange request
 * data in various formats (JSON, XML, URL-ENCODED) through authentication.
 * 
 * @since 	1.8.20 	refactoring for an easier usage for the REST /v2 endpoints as well.
 */
final class E4jConnectRequest
{
	/**
	 * @var  string  the HTTP endpoint.
	 */
	private $endpoint = '';

	/**
	 * @var  array  the request headers.
	 */
	private $httpheader = ['Content-Type: text/xml'];

	/**
	 * @var  int  connection timeout in seconds.
	 */
	private $connect_timeout = 10;

	/**
	 * @var  int  operation timeout in seconds.
	 */
	private $timeout = 20;

	/**
	 * @var  int  max number of connection retries.
	 */
	private $retries = 3;

	/**
	 * @var  array  list of connection error numbers to retry.
	 */
	private $curl_retry_errornos = [2, 6, 7, 28, 35];

	/**
	 * @var  string  request payload.
	 */
	private $postFields = '';

	/**
	 * @var  array  list of cURL extra options.
	 */
	private $curlopt_add = [];

	/**
	 * @var  bool  whether recursion on a different server is allowed.
	 */
	public $slaveEnabled = false;

	/**
	 * @var  resource  cURL connection handler.
	 */
	private $ch;

	/**
	 * @var  string|bool  server response value.
	 */
	private $result = 'e4j.error';

	/**
	 * @var  array  server response details.
	 */
	private $result_info = [];

	/**
	 * @var  int  last connection error number.
	 */
	private $error_no = 0;

	/**
	 * @var  int  last connection error description.
	 */
	private $error_msg = '';

	/**
	 * @var  	string  last connection error message.
	 * 
	 * @since 	1.8.20
	 */
	private $last_errmsg = '';

	/**
	 * @var  int  peer verification value.
	 */
	private $peer_state = 1;

	/**
	 * @var  int  host verification value.
	 */
	private $host_state = 2;

	/**
	 * Class constructor will prepare the transporter.
	 * 
	 * @param 	string 	$endpoint 	the REST or API endpoint to connect to.
	 * @param 	bool 	$slaves 	whether to enable recursion on slave servers.
	 * 
	 * @since 	1.8.20 	introduced second argument $slaves.
	 */
	public function __construct($endpoint, $slaves = false)
	{
		// prepare the endpoint depending on the platform
		$this->endpoint = $this->prepareEndpoint($endpoint);

		// set recursion flag
		$this->slaveEnabled = (bool)$slaves;
	}

	/**
	 * Build the endpoint according to the current platform.
	 * 
	 * @param 	string 	$url 	connection endpoint URL.
	 *
	 * @return 	string
	 */
	private function prepareEndpoint($url)
	{
		/**
		 * @todo once the master.e4jconnect.com server will be available, manually adjust the URL
		 *       in case it starts with https://e4jconnect.com
		 */

		if (VCMPlatformDetection::isJoomla()) {
			// do nothing
			return $url;
		}

		$kv = 'e4j_cms=wp';

		if (strpos($url, $kv) !== false) {
			return $url;
		}

		if (strpos($url, '?') !== false) {
			return $url . '&' . $kv;
		}

		return $url . '?' . $kv;
	}

	/**
	 * Resets all connection values to the default ones.
	 * 
	 * @param 	string 	$endpoint 	optional endpoint URL to set.
	 * 
	 * @return 	self
	 * 
	 * @since 	1.8.20
	 */
	public function reset($endpoint = '')
	{
		if ($endpoint) {
			$this->setEndpoint($endpoint);
		}

		// reset properties to their original state
		$this->result 		= 'e4j.error';
		$this->result_info 	= [];
		$this->error_no 	= 0;
		$this->error_msg 	= '';
		$this->last_errmsg  = '';

		return $this;
	}

	/**
	 * Updates the endpoint URL to connect to.
	 * 
	 * @param 	string 	$endpoint 	connection endpoint URL.
	 * 
	 * @return 	self
	 */
	public function setEndpoint($endpoint)
	{
		$this->endpoint = $endpoint;

		return $this;
	}

	/**
	 * Returns the current endpoint URL to connect to.
	 * 
	 * @return 	string
	 * 
	 * @since 	1.8.26
	 */
	public function getEndpoint()
	{
		return $this->endpoint;
	}

	/**
	 * Sets the HTTP headers for the connection request towards the endpoint.
	 * 
	 * @param 	string|array 	$hheader 	list of headers to set.
	 * @param 	bool 			$replace 	replace or append headers.
	 * 
	 * @return 	self
	 * 
	 * @since 	1.8.20 	introduced second argument $replace.
	 */
	public function setHttpHeader($hheader = [], $replace = true)
	{
		if (is_string($hheader)) {
			$hheader = [$hheader];
		}

		if (!is_array($hheader)) {
			return $this;
		}

		if ($replace) {
			// set headers
			$this->httpheader = $hheader;
		} else {
			// append header(s)
			$this->httpheader = array_merge($this->httpheader, $hheader);
		}

		return $this;
	}

	/**
	 * Returns the current HTTP request headers.
	 * 
	 * @return 	array
	 * 
	 * @since 	1.9.2
	 */
	public function getHttpHeaders()
	{
		return $this->httpheader;
	}

	/**
	 * Returns the last execution response.
	 * 
	 * @return 	mixed
	 * 
	 * @since 	1.9.2
	 */
	public function getLastResponse()
	{
		return $this->result;
	}

	/**
	 * Prepares the headers to establish a connection through Bearer token authentication.
	 * 
	 * @param 	string 	$token 		the Bearer token for the authentication.
	 * @param 	string 	$cont_type 	if provided, the Content-Type header will be set.
	 * @param 	bool 	$base64 	whether to encode in Base64 the Bearer token.
	 * 
	 * @return 	self
	 * 
	 * @since 	1.8.20 	method introduced to facilitate connections to the E4jConnect /v2 REST endpoints.
	 */
	public function setBearerAuth($token, $cont_type = 'application/json', $base64 = true)
	{
		$token = $base64 ? base64_encode($token) : $token;

		$headers = [
			"Authorization: Bearer {$token}",
		];

		if ($cont_type) {
			$headers[] = "Content-Type: {$cont_type}";
		}

		return $this->setHttpHeader($headers, $replace = true);
	}

	/**
	 * Sets the connection timeout seconds.
	 * 
	 * @param 	int 	$sec 	number of seconds.
	 * 
	 * @return 	self
	 */
	public function setConnectTimeout($sec)
	{
		if (intval($sec) > 0) {
			$this->connect_timeout = (int) $sec;
		}

		return $this;
	}

	/**
	 * Sets the operation timeout in seconds.
	 * 
	 * @param 	int 	$sec 	number of seconds.
	 * 
	 * @return 	self
	 */
	public function setTimeout($sec)
	{
		if (intval($sec) > 0) {
			$this->timeout = (int) $sec;
		}

		return $this;
	}

	/**
	 * Sets the max number of connection retries.
	 * 
	 * @param 	int 	$n 	max retries number.
	 * 
	 * @return 	self
	 */
	public function setRetries($n)
	{
		if (intval($n) >= 0) {
			$this->retries = (int) $n;
		}

		return $this;
	}

	/**
	 * Sets the request payload string.
	 * 
	 * @param 	mixed 	$body 	the request payload (array, object or string).
	 * 
	 * @return 	self
	 */
	public function setPostFields($body)
	{
		if (is_array($body) || is_object($body)) {
			$body = json_encode($body);
		}

		$this->postFields = $body;

		return $this;
	}

	/**
	 * Sets additional cURL options.
	 * 
	 * @param 	array 	$copt 	list of cURL custom options.
	 * 
	 * @return 	self
	 */
	public function setCurlOpt(array $copt = [])
	{
		$this->curlopt_add = $copt;

		return $this;
	}

	/**
	 * Registers a connection error message.
	 * 
	 * @param 	string 	$err 	connection error description.
	 * 
	 * @return 	self
	 */
	private function setErrorMsg($err)
	{
		$this->error_msg .= $err."\n";

		return $this;
	}

	/**
	 * Registers a connection error number.
	 * 
	 * @param 	int 	$err 	connection error number.
	 * 
	 * @return 	self
	 */
	private function setErrorNo($err)
	{
		$this->error_no = $err;

		return $this;
	}

	/**
	 * Registers an exact response detail and value.
	 * 
	 * @param 	string 	$key 	response detail name.
	 * @param 	mixed 	$param 	response detail value.
	 * 
	 * @return 	self
	 */
	private function setResultInfo($key, $param)
	{
		if (!empty($key)) {
			$this->result_info[$key] = $param;
		}

		return $this;
	}

	/**
	 * Returns the connection error message.
	 * 
	 * @return 	string
	 */
	public function getErrorMsg()
	{
		return rtrim($this->error_msg, "\n");
	}

	/**
	 * Returns the last connection error message, if any.
	 * 
	 * @return 	string 	last error message or empty string.
	 * 
	 * @since 	1.8.20 	method introduced to avoid accessing
	 * 					the cURL handler through "@curl_error()"
	 * 					when displaying errors is needed.
	 */
	public function getLastError()
	{
		return $this->last_errmsg;
	}

	/**
	 * Returns the connection error number.
	 * 
	 * @return 	int
	 */
	public function getErrorNo()
	{
		return $this->error_no;
	}

	/**
	 * Returns the cURL connection handler.
	 * 
	 * @return 	resource|null
	 */
	public function getCurlHeader()
	{
		return $this->ch;
	}

	/**
	 * Returns a specific connection result value.
	 * 
	 * @param 	string 	$key 	optional response detail name to fetch.
	 * @param 	mixed 	$def 	default value to return if the key is missing.
	 * 
	 * @return 	mixed 			requested detail value or array.
	 */
	public function getResultInfo($key = '', $def = null)
	{
		if (!empty($key)) {
			if ($this->result_info[$key] ?? null) {
				return $this->result_info[$key];
			}

			if ($this->result_info[strtoupper($key)] ?? null) {
				return $this->result_info[strtoupper($key)];
			}

			if ($def ?? null) {
				return $def;
			}
		}

		return $this->result_info;
	}

	/**
	 * Checks whether the last request execution was successful.
	 * 
	 * @return 	bool
	 * 
	 * @since 	1.8.20 	introduced method to validate a /v2 REST endpoint success response.
	 */
	public function successResponse()
	{
		$response_code = $this->getResultInfo('http_code', 0);

		return $response_code == 200;
	}

	/**
	 * Returns the response error details for an Exception, if any.
	 * 
	 * @return 	array
	 * 
	 * @since 	1.8.20 	introduced method to quickly throw an Exception.
	 */
	public function getResponseErrorData()
	{
		$message = '';
		$code 	 = 0;

		$response_code = $this->getResultInfo('http_code', 0);
		if ($response_code != 200) {
			// erroneous response
			$code = is_numeric($response_code) && $response_code > 0 ? $response_code : 500;

			// build error message
			$message = $this->result;
			if (is_bool($message) || $message === 'e4j.error') {
				$message = $this->getLastError();
				$message = $message ?: 'Unexpected error';
			}
		}

		return [$message, $code];
	}

	/**
	 * Fetches the decoded response by executing the request.
	 * 
	 * @param 	string 	$rq_type 	type of request to perform, GET by default.
	 * @param 	string 	$cont_type 	optional content type of response to fetch and decode.
	 * 
	 * @return 	mixed 				raw or decoded server response according to type.
	 * 
	 * @throws 	Exception
	 * 
	 * @since 	1.8.20 				introduced method to quickly fetch a REST response.
	 */
	public function fetch($rq_type = 'GET', $cont_type = '')
	{
		// execute the request
		$response = $this->exec($rq_type);

		// check for errors
		list($message, $code) = $this->getResponseErrorData();

		if ($code) {
			// an error occurred
			throw new Exception($message, $code);
		}

		// parse, and eventually decode, the response
		if (!strcasecmp($cont_type, 'json')) {
			// attempt to decode the JSON response
			$decoded = json_decode($response);
			if ($decoded === null || json_last_error()) {
				// an error occurred
				throw new Exception('Could not decode JSON response', 500);
			}

			// return the JSON decoded object
			return $decoded;
		}

		if (!strcasecmp($cont_type, 'xml')) {
			// attempt to parse the XML response
			$xml = simplexml_load_string($response);
			if (!$xml instanceof SimpleXMLElement) {
				// an error occurred
				throw new Exception('Could not parse XML response', 500);
			}

			// returned the parsed SimpleXMLElement object
			return $xml;
		}

		// return the raw response
		return $response;
	}

	/**
	 * Tells if the last request ended with a server error status code.
	 * 
	 * @return 	bool
	 * 
	 * @since 	1.8.23
	 */
	public function isServerError()
	{
		$last_http_code = $this->getResultInfo('http_code', 0);

		return ($last_http_code >= 500 && $last_http_code < 600);
	}

	/**
	 * Makes the request on the prepared endpoint with the data set.
	 * 
	 * @param 	string 	$rq_type 	type of request to perform, POST by default.
	 * @param 	bool 	$recursion 	whether the request was called recursively.
	 * 
	 * @return 	string 				the server raw response or an error string.
	 * 
	 * @since 	1.8.20 				added support for any request type through arg $rq_type.
	 */
	public function exec($rq_type = 'POST', $recursion = false)
	{
		// SSL and Follow location default states
		$bet_ssl 	= false;
		$follow_loc = ini_get('safe_mode') || strlen((string) ini_get('open_basedir')) ? false : true;

		// prepare request type
		$rq_type = strtoupper(($rq_type ?: 'POST'));

		// normalize various aspects in case of GET requests
		if ($rq_type === 'GET' && $this->postFields) {
			/**
			 * Make sure to append the post fields to the query string in case of GET requests.
			 * 
			 * @since 	1.8.26
			 */
			$query_fields = json_decode($this->postFields, true);
			if (is_array($query_fields)) {
				// build query string by eventually removing keys from numeric arrays
				$query_fields = preg_replace("/%5B\d+%5D/", '%5B%5D', http_build_query($query_fields));
			} elseif (is_string($this->postFields)) {
				// append the (query) string
				$query_fields = $this->postFields;
			}
			$this->endpoint .= (!strpos($this->endpoint, '?') ? '?' : '&') . $query_fields;

			// unset post fields at last
			$this->postFields = null;

			/**
			 * Make sure to unset any "Content-Type" header in case of a GET request.
			 * 
			 * @since 	1.8.26
			 */
			foreach ($this->httpheader as $ind => $header_type) {
				if (preg_match("/^Content-Type:/i", $header_type)) {
					unset($this->httpheader[$ind]);
				}
			}
			$this->httpheader = array_values($this->httpheader);
		}

		// start values for the requests cycle
		$try 		 = 0;
		$curl_errno  = 0;
		$curl_errmsg = '';
		$res 		 = 'e4j.error';

		do {
			$this->ch = curl_init($this->endpoint);
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $this->peer_state);
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $this->host_state);
			if (!$bet_ssl && defined('CURLOPT_SSLVERSION')) {
				// TLS 1.2
				curl_setopt($this->ch, CURLOPT_SSLVERSION, 6);
			}
			curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout);
			curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);
			if ($follow_loc) {
				curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
			}
			if ($rq_type == 'POST') {
				curl_setopt($this->ch, CURLOPT_POST, 1);
			} elseif ($rq_type == 'GET') {
				curl_setopt($this->ch, CURLOPT_POST, 0);
				curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');
			} else {
				curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $rq_type);
			}
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->httpheader);
			curl_setopt($this->ch, CURLOPT_HEADER, 0);
			if ($this->postFields) {
				curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->postFields);
			}
			curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
			if ($this->curlopt_add) {
				foreach ($this->curlopt_add as $curlopt => $curlval) {
					curl_setopt($this->ch, constant($curlopt), $curlval);
				}
			}
			$res = curl_exec($this->ch);
			if ($curl_errno = curl_errno($this->ch)) {
				$this->last_errmsg  = curl_error($this->ch);
				$curl_errmsg = 'e4j.error.Curl.Request' . "\n" . $this->last_errmsg;
				if ($curl_errno == 35) {
					$bet_ssl = true;
				}
			} else {
				$this->setResultInfo('http_code', curl_getinfo($this->ch, CURLINFO_HTTP_CODE));
				$curl_errno = 0;
				$curl_errmsg = '';
			}
			curl_close($this->ch);
			$try++;
		} while ($try < $this->retries && $curl_errno > 0 && in_array($curl_errno, $this->curl_retry_errornos));

		// make sure to ignore SSL peer errors
		if ($curl_errno == 60 && !$recursion) {
			// disable peer and host verification
			$this->peer_state = 0;
			$this->host_state = 0;

			return $this->exec($rq_type, true);
		}

		// attempt to use recursion in case of errors
		if (($curl_errno || $this->isServerError() || $res === false) && !$recursion && $this->slaveEnabled && strpos($this->endpoint, 'slave.e4jconnect.com') === false) {
			// recursion on Slave Cron Server
			if (strpos($this->endpoint, 'hotels.e4jconnect.com') !== false) {
				// recursion on Slave from Hotels-Slave
				$this->endpoint = str_replace('hotels.e4jconnect.com', 'slave.e4jconnect.com', $this->endpoint);
			} else {
				// recursion from Master to Slave
				/**
				 * @todo  use "master." subdomain once the master will be divided from the shop
				 */
				$this->endpoint = str_replace('e4jconnect.com', 'slave.e4jconnect.com', $this->endpoint);
			}

			return $this->exec($rq_type, true);
		}

		if (($curl_errno || $this->isServerError() || $res === false) && !$recursion && $this->slaveEnabled && strpos($this->endpoint, 'slave.e4jconnect.com') !== false) {
			// recursion on Master Server
			/**
			 * @todo  use "master." subdomain once the master will be divided from the shop
			 */
			$this->endpoint = str_replace('slave.e4jconnect.com', 'e4jconnect.com', $this->endpoint);

			return $this->exec($rq_type, true);
		}

		if ($res === false) {
			// default to an error string for the errors map
			$res = 'e4j.error';
		}

		// register execution results
		$this->setErrorNo($curl_errno);
		$this->setErrorMsg($curl_errmsg);
		$this->result = $res;

		return $this->result;
	}
}

class Crono 
{
	
	private $_time = 0.0;
	
	public function start()
	{
		$this->_time = microtime(true);
		return $this->_time;
	}
	
	public function stop()
	{
		return (float)(microtime(true)-$this->_time);
	}
	
}
