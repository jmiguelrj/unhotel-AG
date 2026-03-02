<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2023 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Push subscriptions helper class.
 * 
 * @since 	1.8.20
 */
final class VCMPushSubscription extends JObject
{
	/**
	 * @var 	int 	seconds for the interval of the key refresh.
	 */
	private static $refresh_intval_key = 604800;

	/**
	 * Proxy to construct the object.
	 * 
	 * @param 	array|object  $data  optional data to bind.
	 * @param 	boolean 	  $anew  true for forcing a new instance.
	 * 
	 * @return 	self
	 */
	public static function getInstance($data = [])
	{
		return new static($data);
	}

	/**
	 * Tells whether Push subscriptions are supported.
	 * 
	 * @return 	bool
	 */
	public static function isSupported()
	{
		$applicationKey = VCMFactory::getConfig()->get('push_application_key', '');

		return ($applicationKey && VikChannelManager::isAvailabilityRequest($api_channel = true));
	}

	/**
	 * Returns the application server key to subscribe to Push notifications.
	 * 
	 * @return 	string
	 */
	public static function getApplicationKey()
	{
		static $applicationKey = null;

		if ($applicationKey !== null) {
			return $applicationKey;
		}

		$applicationKey = VCMFactory::getConfig()->get('push_application_key', '');

		if (!$applicationKey && VikChannelManager::isAvailabilityRequest($api_channel = true)) {
			// download application key
			$applicationKey = self::requestApplicationKey();
		}

		if ($applicationKey && self::shouldRefreshApplicationKey()) {
			// re-download application key to ensure it's updated
			$applicationKey = self::requestApplicationKey();
		}

		return $applicationKey;
	}

	/**
	 * Returns a list of Push subscription registrations available.
	 * 
	 * @return 	array
	 */
	public function getRegistrations()
	{
		return VCMFactory::getConfig()->getArray('push_registrations', []);
	}

	/**
	 * Attempts to save a Push subscription registration.
	 * 
	 * @return 	void
	 * 
	 * @throws 	Exception
	 */
	public function save()
	{
		// get all active registrations
		$registrations = $this->getRegistrations();

		if (!$this->get('data')) {
			throw new Exception('Missing registration data', 500);
		}

		$subscription = [
			'data' => $this->get('data', []),
			'user' => $this->get('user', []),
			'date' => JFactory::getDate()->toISO8601(),
		];

		if (!$subscription['data'] || !$subscription['user']) {
			throw new Exception('Missing subscription details', 500);
		}

		$server_update = true;

		if ($this->get('type') == 'update') {
			// update existing registration
			$method = 'PUT';
			$server_update = $this->updateRegistrations($registrations, $subscription);
		} else {
			// push new registration
			$method = 'POST';
			$registrations[] = $subscription;
		}

		if ($server_update) {
			// start the transporter with slaves support on REST /v2 endpoint
			$transporter = new E4jConnectRequest("https://e4jconnect.com/channelmanager/v2/webpush/subscriptions", true);
			$transporter->setBearerAuth(VikChannelManager::getApiKey(), 'application/json')
				->setPostFields($subscription);

			try {
				// store Push subscription
				$transporter->fetch($method, 'json');

				// reload Push registrations
				$reloaded = $transporter->setPostFields([])->fetch('GET', 'json');
				$registrations = $reloaded->items;
			} catch (Exception $e) {
				// propagate the error
				throw $e;
			}
		}

		// update registrations internally
		VCMFactory::getConfig()->set('push_registrations', $registrations);
	}

	/**
	 * Attempts to reload all Push subscription registrations.
	 * 
	 * @return 	void
	 * 
	 * @throws 	Exception
	 */
	public function reload()
	{
		// start the transporter with slaves support on REST /v2 endpoint
		$transporter = new E4jConnectRequest("https://e4jconnect.com/channelmanager/v2/webpush/subscriptions", true);
		$transporter->setBearerAuth(VikChannelManager::getApiKey(), 'application/json');

		try {
			// reload Push registrations
			$reloaded = $transporter->fetch('GET', 'json');
			$registrations = $reloaded->items;
		} catch (Exception $e) {
			// propagate the error
			throw $e;
		}

		// update registrations internally
		VCMFactory::getConfig()->set('push_registrations', $registrations);
	}

	/**
	 * Attempts to delete a Push subscription registration.
	 * 
	 * @return 	void
	 * 
	 * @throws 	Exception
	 */
	public function delete()
	{
		// get all active registrations
		$registrations = $this->getRegistrations();

		$reg_index = (int)$this->get('index', 0);

		if (!$registrations || !isset($registrations[$reg_index])) {
			throw new Exception('Registration not found', 404);
		}

		// copy value for deletion
		$delete_data = $registrations[$reg_index];

		// delete registration internally
		unset($registrations[$reg_index]);

		// update registrations internally
		VCMFactory::getConfig()->set('push_registrations', array_values($registrations));

		// start the transporter with slaves support on REST /v2 endpoint
		$transporter = new E4jConnectRequest("https://e4jconnect.com/channelmanager/v2/webpush/subscriptions", true);
		$transporter->setBearerAuth(VikChannelManager::getApiKey(), 'application/json')
			->setPostFields($delete_data);

		try {
			// delete Push subscription
			$transporter->fetch("DELETE", 'json');
		} catch (Exception $e) {
			// propagate the error
			throw $e;
		}	
	}

	/**
	 * Builds the information for the passed desktop registration data.
	 * 
	 * @return 	array
	 */
	public function getInformation()
	{
		// default values
		$default_brws = 'Desktop';
		$default_os   = 'Unknown';

		$agent_info = [
			'browser'  => $default_brws,
			'platform' => $default_os,
		];

		// list of known browsers
		$known_browsers = [
			'Safari',
			'Firefox',
			'Chrome',
			'Edge',
			'Opera',
		];

		// get the injected registration data
		$registration = $this->get('data', []);

		if (!$registration || empty($registration['agent'])) {
			return $agent_info;
		}

		// get the browser details from user-agent name
		$os_name 	  = '';
		$browser_info = @get_browser($registration['agent']);

		if ($browser_info) {
            // data was detected
            $browser_info = (array) $browser_info;

            $browser_name = $browser_info['browser'];
            $check_os = $browser_info['platform'];
        } else {
			// fallback to quick regex for most common browsers
			$browser_name = $default_brws;
			$check_os = $registration['agent'];

			if (preg_match("/Edge?\/[0-9.]+/i", $registration['agent'])) {
				$browser_name = 'Edge';
				$os_name = 'Windows';
			} elseif (preg_match("/Opera[\/ ][0-9.]+/i", $registration['agent']) || preg_match("/OPR[\/ ][0-9.]+/", $registration['agent'])) {
				$browser_name = 'Opera';
			} elseif (preg_match("/Chrome[\/ ][0-9.]+/i", $registration['agent']) || preg_match("/CrMo[\/ ][0-9.]+/i", $registration['agent']) || preg_match("/CriOS[\/ ][0-9.]+/i", $registration['agent'])) {
				$browser_name = 'Chrome';
			} elseif (preg_match("/Android/i", $registration['agent'])) {
				$browser_name = 'Chrome';
				$os_name = 'Android';
			} elseif (preg_match("/Safari\/[0-9]+\.?[0-9]+?/i", $registration['agent'])) {
				$browser_name = 'Safari';
			} elseif (preg_match("/Firefox\/[0-9.]+/i", $registration['agent']) || preg_match("/Mozilla/i", $registration['agent'])) {
				$browser_name = 'Firefox';
			}
		}

		// detect operating system
		if (!$os_name) {
			if (preg_match("/(Win|Microsoft)/i", $check_os)) {
				$os_name = 'Windows';
			} elseif (preg_match("/Apple|Macintosh|Mac|iOS|iPhone|iPad/i", $check_os)) {
				$os_name = 'Apple';
			} elseif (preg_match("/Android|Google/i", $check_os)) {
				$os_name = 'Android';
			} elseif (preg_match("/Linux|Unix/i", $check_os)) {
				$os_name = 'Linux';
			} else {
				$os_name = $default_os;
			}
		}

		// adjust browser name, if needed
		if ($browser_name != $default_brws && !in_array($browser_name, $known_browsers)) {
			$adjusted = false;
			foreach ($known_browsers as $known_browser) {
				if (stripos($browser_name, $known_browser) !== false) {
					$browser_name = $known_browser;
					$adjusted = true;
					break;
				}
			}

			if (!$adjusted) {
				// default to unknown
				$browser_name = $default_brws;
			}
		}

		// set calculated values
		$agent_info = [
			'browser'  => $browser_name,
			'platform' => $os_name,
		];

		return $agent_info;
	}

	/**
	 * Attempts to find the existing subscription to update if anything has changed.
	 * 
	 * @param 	array 	$registrations 	list of current registrations.
	 * @param 	array 	$subscription 	the new Push subscription registration.
	 * 
	 * @return 	bool 					true if anything has changed.
	 */
	private function updateRegistrations(array &$registrations, array $subscription)
	{
		if (!$registrations) {
			// add it
			$registrations[] = $subscription;
			return true;
		}

		$updated = false;

		foreach ($registrations as $ind => $registration) {
			$matched = false;
			if ($registration['data']['endpoint'] == $subscription['data']['endpoint']) {
				$matched = true;
			} elseif ($registration['user']['email'] == $subscription['user']['email']) {
				$matched = true;
			} elseif ($registration['user']['name'] == $subscription['user']['name']) {
				$matched = true;
			}

			if ($matched && $registration['data'] != $subscription['data']) {
				// something has changed
				$updated = true;
				$registrations[$ind] = $subscription;
			}
		}

		return $updated;
	}

	/**
	 * Checks whether it's time to refresh the E4jConnect Application Server Key.
	 * 
	 * @return 	bool
	 */
	private static function shouldRefreshApplicationKey()
	{
		$last_check = VCMFactory::getConfig()->get('push_key_lastcheck');

		if (!$last_check) {
			return false;
		}

		if ((time() - strtotime($last_check)) > self::$refresh_intval_key) {
			return true;
		}

		return false;
	}

	/**
	 * Downloads the E4jConnect Application Server Key for Push notifications.
	 * 
	 * @return 	string
	 */
	private static function requestApplicationKey()
	{
		$config = VCMFactory::getConfig();

		// immediately update the last check
		$config->set('push_key_lastcheck', date('Y-m-d'));

		// start the transporter with slaves support on REST /v2 endpoint
		$transporter = new E4jConnectRequest("https://e4jconnect.com/channelmanager/v2/webpush/application-server-key", true);
		$transporter->setBearerAuth(VikChannelManager::getApiKey(), 'application/json');

		try {
			// fetch data in JSON format
			$data = $transporter->fetch('GET', 'json');

			$application_key = $data->key;
		} catch (Exception $e) {
			// do nothing
			$application_key = '';
		}

		// set and return the application key
		$config->set('push_application_key', $application_key);

		return $application_key;
	}
}
