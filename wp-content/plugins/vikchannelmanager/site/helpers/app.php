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
 * API Framework between E4JConnect and the Mobile App.
 * Available to third party systems with eligible App accounts.
 *
 * @since 	1.6.0
 */
class AppE4jConnect 
{
	/**
	 * The HTTP response status code.
	 *
	 * @var 	int
	 * 
	 * @since 	1.8.21
	 */
	public $statusCode = 200;

	/**
	 * The response that will be JSON encoded and returned
	 * after completing the process.
	 *
	 * @var object
	 */
	private $response;

	/**
	 * The identifier string of the requested process.
	 *
	 * @var string
	 */
	private $requestType;

	/**
	 * A string containing the last fetched error.
	 *
	 * @var string
	 */
	private $errorString;

	/**
	 * A pointer to the input handler.
	 *
	 * @var JInputJSON
	 */
	private $input;

	/**
	 * A pointer to the database handler.
	 *
	 * @var JDatabaseDriver
	 */
	private $dbo;

	/**
	 * The current API account email authenticated.
	 *
	 * @var string
	 */
	private $accountEmail = null;
	
	/**
	 * Class constructor.
	 *
	 * @uses 	getRequestType()
	 */
	public function __construct() 
	{
		// handle CORS
		VCMAuthHelper::handleCORS();

		$this->response = new stdClass();
		$this->response->res  = 'e4j.error';
		$this->response->body = null;

		$this->requestType = $this->getRequestType();
		$this->errorString = '';

		$this->input = JFactory::getApplication()->input->json;
		$this->dbo   = JFactory::getDbo();
	}

	/**
	 * All the supported request types should be returned here.
	 *
	 * @return 	mixed 	The request type if supported, null otherwise.
	 */
	private function getRequestType() 
	{
		$req = JFactory::getApplication()->input->getString('req', '');

		if (!$req)
		{
			return null;
		}
		
		switch ($req)
		{
			case 'check':
			case 'arrivals_departures':
			case 'booking_details':
			case 'booking_search':
			case 'bookings_list':
			case 'cancel_booking':
			case 'confirm_booking':
			case 'modify_booking':
			case 'create_booking':
			case 'get_graphsdata':
			case 'get_availability':
			case 'get_happening':
			case 'get_expirations':
			case 'get_notifications':
			case 'get_rooms':
			case 'get_room_rates':
			case 'modify_room_rates':
			case 'close_room':
			case 'handle_chat':
			case 'customers_search':
			case 'customer_details':
			case 'cancel_customer':
			case 'modify_customer':
			case 'get_reviews':
			case 'update_review':
			case 'host_guest_review':
			case 'calc_rateplans':
			case 'tableaux_data':
			case 'remove_fest':
			case 'create_fest':
			case 'remove_roomdaynote':
			case 'create_roomdaynote':
			case 'booking_history':
			case 'booking_paxdata':
			case 'invoices_list':
			case 'output_invoice':
			case 'remove_invoice':
			case 'send_invoice':
			case 'generate_invoice':
			case 'calc_room_options':
			case 'get_tax_rates':
			case 'get_scorecards':
			case 'get_pax_fields':
			case 'set_pax_fields':
			case 'get_geoinfo':
			case 'get_reactions':
			case 'ai_translate':
				return $req;
		}

		/**
		 * Trigger event to allow third party plugins to validate a custom request type.
		 * 
		 * @since 	1.8.11
		 */
		$validations = VCMFactory::getPlatform()->getDispatcher()->filter('onValidateAppRequestVikChannelManager', [$req]);
		if (is_array($validations) && in_array(true, $validations, true))
		{
			return $req;
		}

		return null;
	}

	/**
	 * Main method called by the controller.
	 * Execute the request and sends the response to output.
	 *
	 * @return 	void
	 *
	 * @uses 	executeRequest()
	 * @uses 	getError()
	 */
	public function processRequest() 
	{
		try {
			// execute the App request
			$this->executeRequest();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage() ?: 'An error occurred.');

			// default to error 500
			$this->statusCode = $e->getCode() ?: 500;
		}
		catch (Throwable $t)
		{
			// catch any exception or critical error
			$log = sprintf('%s in %s on line %d', $t->getMessage(), $t->getFile(), $t->getLine());

			$this->setError($log);

			if ($this->statusCode === 200)
			{
				// default to error 500
				$this->statusCode = 500;
			}
		}

		// get any execution errors
		$error = $this->getError();

		if (!empty($error))
		{
			$this->response->body = $error;
		}
		else
		{
			$this->response->res = 'e4j.ok';
		}

		// set response headers
		$app = JFactory::getApplication();
		$app->setHeader('Content-Type', 'application/json;charset=utf-8', $replace = true);

		/**
		 * Trigger event to allow third party plugins to manipulate the response object.
		 * 
		 * @since 	1.8.11
		 */
		VCMFactory::getPlatform()->getDispatcher()->trigger('onCompletedAppRequestVikChannelManager', [$this->requestType, $this->input, $this->response, &$this->statusCode]);

		// send the JSON response to output
		VCMHttpDocument::getInstance($app)->close($this->statusCode, json_encode($this->response));
	}

	/**
	 * Checks request integrity, executes the App request and compose the response.
	 *
	 * @return void
	 *
	 * @uses 	checkRequestIntegrity()
	 */
	private function executeRequest() 
	{
		if (!$this->checkRequestIntegrity())
		{
			if ($this->statusCode === 200)
			{
				// default to Bad Request (400)
				$this->statusCode = 400;
			}

			return false;
		}

		switch ($this->requestType)
		{
			case 'check':
				$this->appCheck();
				break;

			case 'arrivals_departures':
				$this->appArrivalsDepartures();
				break;

			case 'booking_details':
				$this->appBookingDetails();
				break;

			case 'bookings_list':
				$this->appBookingsList();
				break;

			case 'get_rooms':
				$this->appGetRooms();
				break;

			case 'get_availability':
				$this->appGetAvailability();
				break;

			case 'cancel_booking':
				$this->appCancelBooking();
				break;

			case 'confirm_booking':
				$this->appConfirmBooking();
				break;

			case 'modify_booking':
				$this->appModifyBooking();
				break;

			case 'get_room_rates':
				$this->appGetRoomRates();
				break;

			case 'modify_room_rates':
				$this->appModifyRoomRates();
				break;

			case 'get_happening':
				$this->appGetHappening();
				break;

			case 'close_room':
				$this->appCloseRoom();
				break;

			case 'create_booking':
				$this->appCreateBooking();
				break;

			case 'get_graphsdata':
				$this->appGetGraphsData();
				break;

			case 'get_expirations':
				$this->appGetExpirations();
				break;

			case 'get_notifications':
				$this->appGetNotifications();
				break;

			case 'handle_chat':
				$this->handleChat();
				break;

			case 'booking_search':
				$this->bookingSearch();
				break;

			case 'customers_search':
				$this->customersSearch();
				break;

			case 'customer_details':
				$this->customerDetails();
				break;

			case 'cancel_customer':
				$this->cancelCustomer();
				break;

			case 'modify_customer':
				$this->modifyCustomer();
				break;

			case 'get_reviews':
				$this->getReviews();
				break;

			case 'update_review':
				$this->updateReview();
				break;

			case 'host_guest_review':
				$this->submitHostGuestReview();
				break;

			case 'calc_rateplans':
				$this->calculateRatePlans();
				break;

			case 'tableaux_data':
				$this->getTableauxData();
				break;

			case 'remove_fest':
				$this->removeFestivity();
				break;

			case 'create_fest':
				$this->createFestivity();
				break;

			case 'remove_roomdaynote':
				$this->removeRoomdaynote();
				break;

			case 'create_roomdaynote':
				$this->createRoomdaynote();
				break;

			case 'booking_history':
				$this->loadBookingHistory();
				break;

			case 'booking_paxdata':
				$this->loadBookingPaxData();
				break;

			case 'invoices_list':
				$this->appInvoicesList();
				break;

			case 'output_invoice':
				$this->outputInvoice();
				break;

			case 'remove_invoice':
				$this->removeInvoice();
				break;

			case 'send_invoice':
				$this->sendInvoice();
				break;

			case 'generate_invoice':
				$this->generateInvoice();
				break;

			case 'calc_room_options':
				$this->calculateRoomOptions();
				break;

			case 'get_tax_rates':
				$this->getTaxRates();
				break;

			case 'get_scorecards':
				$this->getScorecards();
				break;

			default:
				// guess the method to call for the current request
				$this->_callRequest();
				break;
		}
	}

	/**
	 * Attempts to guess the method to call for the current request type.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.8.7
	 */
	private function _callRequest()
	{
		if (empty($this->requestType)) {
			return;
		}

		// convert snake case to camel case by prepending "app"
		$guessed_mname = 'app' . str_replace(' ', '', ucwords(str_replace('_', ' ', $this->requestType)));

		if (method_exists($this, $guessed_mname) && is_callable([$this, $guessed_mname])) {
			// invoke the requested and available method
			return $this->{$guessed_mname}();
		}

		/**
		 * Trigger event to allow third party plugins to execute a custom request.
		 * 
		 * @since 	1.8.11
		 */
		$dispatcher = VCMFactory::getPlatform()->getDispatcher();

		// trigger the generic "execute app request" event
		$dispatcher->trigger('onExecuteAppRequestVikChannelManager', [$this->requestType, $this->input, $this->response, $this->accountEmail]);

		// trigger also the custom-named app request event by converting the request type from snake_case to PascalCase
		$custom_ev_name = 'on' . str_replace(['_', ' '], '', ucwords(preg_replace("/vikchannelmanager|apprequest/i", '', $this->requestType), '_')) . 'AppRequestVikChannelManager';
		$dispatcher->trigger($custom_ev_name, [$this->requestType, $this->input, $this->response, $this->accountEmail]);

		return;
	}

	/**
	 * Checks whether the request is complete and can be executed.
	 * Loads also the necessary language files for the request.
	 *
	 * @return 	boolean  Checks whether the request can be processed.
	 *
	 * @uses 	authenticateRequest()
	 * @uses 	authoriseRule()
	 * @uses 	setError()
	 */
	private function checkRequestIntegrity()
	{
		// load language depending on the value received from the request,
		// if supported, otherwise always load the lang defs of VBO
		$lang = JFactory::getLanguage();
		$load_lang = '';
		$deflang   = $this->input->getString('deflang', '');

		if (!empty($deflang))
		{
			// make language syntax safe (e.g. [az-AZ])
			$tags_data    = preg_split("/[-_]/", $deflang);
			$tags_data[0] = strtolower($tags_data[0]);
			$tags_data[1] = strtoupper($tags_data[1]);
			$attempt_tag  = implode('-', $tags_data);

			$known_langs = array();
			
			// Get site available languages.
			// We need to check whether JLanguage::getKnownLanguages() method
			// is supported because older versions of VikBooking for WP may
			// not have declared it.
			if (method_exists('JLanguage', 'getKnownLanguages'))
			{
				$known_langs = JLanguage::getKnownLanguages();
			}

			// check if language is installed
			if (array_key_exists($attempt_tag, $known_langs))
			{
				$load_lang = $attempt_tag;
			}
			else
			{
				// iterate known languages and validate lang tag against the locale
				foreach ($known_langs as $ltag => $lvals)
				{
					if (array_key_exists('locale', $lvals) && !empty($lvals['locale']))
					{
						if (stripos($lvals['locale'], $attempt_tag) !== false)
						{
							$load_lang = $ltag;
							break;
						}
					}
				}
			}
		}

		if (!empty($load_lang) && $lang->getTag() != $load_lang)
		{
			// load VBO and VCM language for the requested locale
			if (VCMPlatformDetection::isWordPress()) {
				// @wponly 	we need to use these constants
				$lang->load('com_vikbooking', VIKBOOKING_LANG, $load_lang, true);
				$lang->load('com_vikchannelmanager', VIKCHANNELMANAGER_SITE_LANG, $load_lang, true);
			} else {
				$lang->load('com_vikbooking', JPATH_SITE, $load_lang, true);
				$lang->load('com_vikchannelmanager', JPATH_SITE, $load_lang, true);
			}
		}
		else
		{
			// load only VBO language for current locale (VCM is already loaded by default)
			if (VCMPlatformDetection::isWordPress()) {
				// @wponly 	we need to use this constant
				$lang->load('com_vikbooking', VIKBOOKING_LANG, $lang->getTag(), true);
			} else {
				$lang->load('com_vikbooking', JPATH_SITE, $lang->getTag(), true);
			}
		}

		// check whether we are fetching a valid request
		if (empty($this->requestType))
		{
			$this->setError(JText::_('VCMAPPRQINVALID'));
			return false;
		}

		// request authentication
		if (!$this->authenticateRequest())
		{
			// set unauthorized status code
			$this->statusCode = 401;

			return false;
		}

		// decode the request for authentication
		$raw_req = $this->input->getRaw();

		if (empty($raw_req))
		{
			$this->setError(JText::_('VCMAPPRQEMPTY'));
			return false;
		}
		
		// check account permission
		if (!$this->authoriseRule())
		{
			// set forbidden status code
			$this->statusCode = 403;

			// set error message
			$this->setError(JText::_('VCMAPPACCDENIED'));

			return false;
		}

		return true;
	}

	/**
	 * Authenticates the App Request with email and password.
	 * Allows the authentication through 3 methods: Basic Auth,
	 * Basic Access HTTP Authentication and JSON request body.
	 * 
	 * @return 	boolean  True if authenticated, false otherwise.
	 *
	 * @uses 	loadAppCredentials()
	 * @uses 	setError()
	 */
	private function authenticateRequest()
	{
		// extract credentials from database
		$credentials = $this->loadAppCredentials();

		// check if we have at least a login
		if (!$credentials)
		{
			$this->setError(JText::_('VCMAPPMISSINGCRED'));
			return false;
		}

		// access server superglobal
		$server = JFactory::getApplication()->input->server;

		// attempt to get the Basic Auth header
		$auth_header = $server->getString('HTTP_AUTHORIZATION');

		// attempt to get the email from Basic Access HTTP Authentication
		$auth_email = $server->getString('PHP_AUTH_USER');

		// attempt to get the password from Basic Access HTTP Authentication
		$auth_pwd = $server->getString('PHP_AUTH_PW');

		// get email and password from JSON request body
		$rq_email = $this->input->getString('email', '');
		$rq_pwd   = $this->input->getString('pwd', '');

		if (!empty($auth_header))
		{
			// decode Base64 encoded basic auth header
			$auth_header = base64_decode(preg_replace("/^Basic\s?/i", '', $auth_header));

			// get auth parts
			$auth_parts = explode(':', $auth_header);

			// set auth email and pwd
			$auth_email = $auth_parts[0];
			$auth_pwd   = $auth_parts[1];
		}

		// collect non-empty field values for validation
		$validate_email = $rq_email ?: $auth_email;
		$validate_pwd   = $rq_pwd ?: $auth_pwd;

		if (empty($validate_email))
		{
			$this->setError(JText::_('VCMAPPMISSINGEMAIL'));
			return false;
		}

		if (empty($validate_pwd))
		{
			$this->setError(JText::_('VCMAPPMISSINGPASS'));
			return false;
		}

		// fetch accounts key
		$req_email = trim(strtolower($validate_email));

		// check if the account is registered
		if (!array_key_exists($req_email, $credentials))
		{
			$this->setError(JText::_('VCMAPPINVALIDEMAIL'));
			return false;
		}

		// check if the password matches
		if ($validate_pwd != $credentials[$req_email])
		{
			$this->setError(JText::_('VCMAPPINVALIDPASS'));
			return false;
		}

		// set the current App account email
		$this->accountEmail = $req_email;

		// set the request as authenticated
		return true;
	}

	/**
	 * Loads the App email and pwd sent by e4jConnect
	 * upon the App registration process.
	 * 
	 * @return 	array 	The App credentials.
	 */
	private function loadAppCredentials() 
	{
		$credentials = array();

		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param` = 'app_accounts'";

		$this->dbo->setQuery($q, 0, 1);
		$this->dbo->execute();

		if ($this->dbo->getNumRows())
		{
			$credentials = (array) json_decode($this->dbo->loadResult(), true);
		}

		return $credentials;
	}

	/**
	 * Sets execution errors.
	 * 
	 * @param 	string 	$error
	 *
	 * @return 	self
	 */
	private function setError($error) 
	{
		$this->errorString .= $error;

		return $this;
	}
	
	/**
	 * Gets current execution errors.
	 * 
	 * @return 	string
	 */
	private function getError() 
	{
		return $this->errorString;
	}

	/**
	 * Checks if the request type is allowed for the user's ACL level.
	 * 
	 * @param 	string 	 $requestType
	 *
	 * @return 	boolean
	 */
	private function authoriseRule($requestType = null)
	{
		if (!$requestType)
		{
			// use request type found
			$requestType = $this->requestType;
		}

		// define lookup to ignore certain requests
		$ignores = [
			'check',
			'arrivals_departures',
			'booking_details',
			'booking_search',
			'bookings_list',
			'get_rooms',
			'get_availability',
			'get_room_rates',
			'get_happening',
			'get_expirations',
			'get_notifications',
			'handle_chat',
			'customers_search',
			'customer_details',
			'get_reviews',
			'host_guest_review',
			'calc_rateplans',
			'tableaux_data',
			'booking_history',
			'booking_paxdata',
			'invoices_list',
			'output_invoice',
			'send_invoice',
			'generate_invoice',
			'calc_room_options',
			'get_tax_rates',
			'get_scorecards',
			'get_pax_fields',
			'get_geoinfo',
			'ai_translate',
		];

		// check if the request type should be ignored
		if (in_array($requestType, $ignores))
		{
			return true;
		}

		// get e-mail from request
		$email = $this->input->getString('email', '') ?: $this->accountEmail;

		// get user group/role to authorise
		$perms_defined = false;
		$default_role  = VCMPlatformDetection::isWordPress() ? 'subscriber' : 'registered';
		$group 		   = null;
		$magic_action  = 'core.admin';

		// load ACL from config
		$q = "SELECT `setting` FROM `#__vikchannelmanager_config` WHERE `param` = 'app_acl'";

		$this->dbo->setQuery($q, 0, 1);
		$this->dbo->execute();

		if ($this->dbo->getNumRows())
		{
			$perms_defined = true;
			$json 		   = (array) json_decode($this->dbo->loadResult(), true);
			$group 		   = isset($json[$email]) ? $json[$email] : null;
		}

		/**
		 * Trigger event to allow third party plugins to authorise a custom request type.
		 * 
		 * @since 	1.8.11
		 */
		$check_group_role = $group ? $group : $default_role;
		$auth_rules = VCMFactory::getPlatform()->getDispatcher()->filter('onAuthoriseAppRequestVikChannelManager', [$requestType, $email, $check_group_role, $magic_action]);
		if (is_array($auth_rules) && !empty($auth_rules[0]) && is_array($auth_rules[0]) && count($auth_rules[0]) === 2) {
			// get authorisation rules
			list($role, $action) = $auth_rules[0];

			// validate ACL
			return JAccess::checkGroup((string) $role, (string) $action, 'com_vikchannelmanager') || JAccess::checkGroup((string) $role, (string) $action, 'com_vikbooking');
		}

		if (!$perms_defined)
		{
			// no ACL permissions to authorise an unsafe request
			return false;
		}

		/**
		 * In order to not always add new ACL permissions for specific actions,
		 * we try to understand the type of operation, either reading or writing
		 * and we use the global ACL rules core.create, core.delete and core.edit.
		 * 
		 * @since 	1.7.4
		 */
		$request_parts = explode('_', $requestType);
		$actions_pool  = array(
			'core.create' => array(
				'save',
				'create',
				'insert',
			),
			'core.edit' => array(
				'modify',
				'edit',
				'update',
			),
			'core.delete' => array(
				'delete',
				'remove',
				'cancel',
			),
		);
		foreach ($actions_pool as $action => $types)
		{
			if (in_array($request_parts[0], $types) || (count($request_parts) > 1 && in_array($request_parts[1], $types)))
			{
				// generic action detected
				$magic_action = $action;
				break;
			}
		}
		//

		// validate ACL
		return (JAccess::checkGroup($group, 'core.admin', 'com_vikchannelmanager')
			|| JAccess::checkGroup($group, 'core.vcm.' . $requestType, 'com_vikchannelmanager')
			|| JAccess::checkGroup($group, $magic_action, 'com_vikchannelmanager')
		);
	}

	/**
	 * Cleans up some extra words added to channels like
	 * A-Hotels.com, A-Expedia, A-Expedia Affiliate Network...
	 *
	 * @param 	$source  string
	 *
	 * @return 	string
	 */
	private function clearSourceName($source)
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
	 * Loads the main library of Vik Booking
	 *
	 * @return 	boolean
	 */
	private function importVboLib()
	{
		if (class_exists('VikBooking'))
		{
			// VBO lib already loaded
			return true;
		}

		$path = VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';

		if (!is_file($path))
		{
			// missing VBO lib
			return false;
		}

		// include VBO lib and return response
		return @include_once $path;
	}

	//
	// Methods that execute the App requests
	//

	/**
	 * App Check Request
	 *
	 * Sets the response with the installed versions of VCM and VBO
	 *
	 * @return void
	 */
	private function appCheck() 
	{
		$response = new stdClass();
		$response->vcm_version = VIKCHANNELMANAGER_SOFTWARE_VERSION;

		// try loading VBO lib
		if (!$this->importVboLib())
		{
			// an error occurred
			$this->setError(JText::_('VCMAPPNOVBINSTALL'));
			return false;
		}

		// set VBO version
		if (defined('VIKBOOKING_SOFTWARE_VERSION')) {
			$response->vbo_version = VIKBOOKING_SOFTWARE_VERSION;
		} elseif (defined('E4J_SOFTWARE_VERSION')) {
			$response->vbo_version = E4J_SOFTWARE_VERSION;
		} else {
			$response->vbo_version = null;
		}

		// set the response body for the App
		$this->response->body = $response;
	}

	/**
	 * App Arrivals Departures Request
	 *
	 * This function was created to send the details of bookings whose guests
	 * arrive/depart/stay or have booked on a selected date to the e4jConnect App.
	 * The App will then process the sent data and display it.
	 *
	 * @return void
	 */
	private function appArrivalsDepartures()
	{
		$response = new stdClass();

		//Getting the date and the type of the request
		$requestDate = $this->input->getString('date', '');
		if (empty($requestDate)) {
			$requestDate = date('Y-m-d');
		}
		$requestType = $this->input->getString('type', 'arrivals');
		if (!in_array($requestType, array('arrivals', 'departures', 'stayovers', 'bookings'))) {
			$this->setError(JText::_('VCMAPPREQTYPEINCORRECT'));
			return false;
		}

		//If the date is not formatted correctly, an error is sent and false is returned
		if (count(explode('-', $requestDate)) != 3) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}

		//Getting the midnight (both the 00:00 and the 23:59) timestamp for the selected date
		$date_start_ts 	= strtotime($requestDate);
		$dateArray 		= getdate($date_start_ts);
		$date_end_ts 	= mktime(23, 59, 59, $dateArray['mon'], $dateArray['mday'], $dateArray['year']);
		$bookings 		= array();
		$bookingsData	= array();
		$clauses 		= array("`o`.`closure` = 0", "`o`.`status` = 'confirmed'");

		if ($requestType == 'arrivals') {
			$dtype = "`o`.`checkin`";
			$otype = "`o`.`checkin` ASC";
			$clauses[] = "$dtype >= $date_start_ts";
			$clauses[] = "$dtype <= $date_end_ts";
		} elseif ($requestType == 'departures') {
			$dtype = "`o`.`checkout`";
			$otype = "`o`.`checkout` ASC";
			$clauses[] = "$dtype >= $date_start_ts";
			$clauses[] = "$dtype <= $date_end_ts";
		} elseif ($requestType == 'stayovers') {
			$otype = "`o`.`checkin` ASC";
			$clauses[] = "`o`.`checkin` < $date_start_ts";
			$clauses[] = "`o`.`checkout` > $date_end_ts";
		} else {
			$dtype = "`o`.`ts`";
			$otype = "`o`.`id` DESC";
			$clauses[] = "$dtype >= $date_start_ts";
			$clauses[] = "$dtype <= $date_end_ts";
		}

		//Load all the bookings according to the params
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
		$this->dbo->setQuery($q);
		$bookingsData = $this->dbo->loadAssocList();

		foreach ($bookingsData as $book) {
			if ((int)$book['closure'] > 0) {
				if ($requestType != 'bookings') {
					// arrivals or departures should not list the closures of the rooms
					continue;
				} else {
					// when booked dates, just force the customer name to 'room closed'
					$book['nominative'] = JText::_('VCMAPPROOMCLOSED');
				}
			}
			$booking_info = new stdClass;
			$booking_info->id = $book['id'];
			$booking_info->created_on = date('Y-m-d H:i', $book['ts']);
			$booking_info->status = ucfirst($book['status']);
			$booking_info->nights = $book['days'];
			$booking_info->checkin = date('Y-m-d H:i', $book['checkin']);
			$booking_info->checkout = date('Y-m-d H:i', $book['checkout']);
			$booking_info->number_of_rooms = $book['roomsnum'];
			$booking_info->rooms = $book['room_names'];
			$booking_info->adults = $book['tot_adults'];
			$booking_info->children = $book['tot_children'];
			if (!empty($book['nominative'])) {
				$booking_info->customer_name = $book['nominative'];
			} else {
				$cust_data_lines = explode("\n", $book['custdata']);
				$first_cust_info = explode(":", $cust_data_lines[0]);
				$booking_info->customer = count($first_cust_info) > 1 ? $first_cust_info[1] : $first_cust_info[0];
			}
			$booking_info->country = $book['country'];
			$booking_info->email = $book['custmail'];
			$booking_info->phone = $book['phone'];
			$booking_info->source = 'VBO';
			if (!empty($book['channel']) && $book['channel'] != 'Channel Manager') {
				$source = explode('_', $book['channel']);
				$source = count($source) > 1 ? $source[1] : $source[0];
				$booking_info->source = $this->clearSourceName($source);
			}
			if (!empty($book['idorderota'])) {
				$booking_info->ota_id = $book['idorderota'];
			}
			array_push($bookings, $booking_info);
		}

		//Setting the response
		$response->date = $requestDate;
		$response->type = $requestType;
		$response->bookings = $bookings;

		//Set the response object as the body for the App
		$this->response->body = $response;
	}

	/**
	 * App Booking Details Request
	 *
	 * This function was created to send the details of a booking hosted on VikBooking, given its id,
	 * to the e4j App. The app will then process the data and display it on screen.
	 *
	 * @return void
	 */
	private function appBookingDetails()
	{
		$response = new stdClass();

		// get the request parameters ('bid' must be a string as it could be a non-numeric OTA Booking ID)
		$order_id = $this->input->getString('bid', '');
		$order_from = $this->input->getString('from', '');

		$where_clause = array();
		if (!empty($order_from) && stripos($order_from, 'vbo') === false) {
			$where_clause[] = "`o`.`idorderota`=" . $this->dbo->quote($order_id);
		} else {
			$where_clause[] = "`o`.`id`=" . (int)$order_id;
			// long IDs should revert to the field idorderota in case the booking cannot
			// be fetched from the ID or because the channel is unknown in the App.
			if (strlen($order_id) > 6) {
				$where_clause[] = "`o`.`idorderota`=" . $this->dbo->quote($order_id);
			}
		}

		// order ID cannot be empty (casting an OTA BID string to int may return 0)
		if (empty($order_id)) {
			$this->setError(JText::_('VCMAPPEMPTYBOOKINGID'));
			return false;
		}

		$currency_symb = VikBooking::getCurrencySymb();

		// require VBO library
		$this->importVboLib();

		// Query to select data
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
				SELECT SUM(`or`.`children`) 
				FROM `#__vikbooking_ordersrooms` AS `or` 
				WHERE `or`.`idorder` = `o`.`id`
			) AS `tot_children`, 
			(
				SELECT GROUP_CONCAT(`r`.`name` SEPARATOR ',') 
				FROM `#__vikbooking_rooms` AS `r` 
				LEFT JOIN `#__vikbooking_ordersrooms` AS `or` ON `or`.`idroom` = `r`.`id` 
				WHERE `or`.`idorder` = `o`.`id`
			) AS `room_names` 
			FROM `#__vikbooking_orders` AS `o` 
			WHERE (".implode(' OR ', $where_clause).") LIMIT 1;";
		$this->dbo->setQuery($q);
		$orderInfo = $this->dbo->loadAssoc();
		if (!$orderInfo) {
			// Error, booking not found
			$this->setError(JText::_('VCMAPPSELBOOKINGUNAV'));
			return false;
		}

		// load rooms data (never change the ordering `or`.`id` ASC for compatibility with the modify_booking RQ)
		$rooms_data = array();
		$rooms_indexes = array();
		$rooms_options = array();
		$rooms_extras = array();
		$children_age = array();
		$q = "SELECT `or`.*, `r`.`name`, `r`.`params`, `d`.`idprice` AS `rate_plan_id`, `p`.`name` AS `rate_plan_name` 
			FROM `#__vikbooking_ordersrooms` AS `or` 
			LEFT JOIN `#__vikbooking_rooms` AS `r` ON `r`.`id`=`or`.`idroom` 
			LEFT JOIN `#__vikbooking_dispcost` AS `d` ON `d`.`id`=`or`.`idtar` 
			LEFT JOIN `#__vikbooking_prices` AS `p` ON `p`.`id`=`d`.`idprice` 
			WHERE `or`.`idorder`=" . $orderInfo['id'] . " 
			ORDER BY `or`.`id` ASC;";
		$this->dbo->setQuery($q);
		$rooms_data = $this->dbo->loadAssocList();
		if ($rooms_data) {
			foreach ($rooms_data as $k => $v) {
				// children age
				if (!empty($v['childrenage'])) {
					$children_age_info = json_decode($v['childrenage']);
					if (is_object($children_age_info) || is_array($children_age_info)) {
						array_push($children_age, $children_age_info);
					}
				}
				// options and extras
				if (!empty($v['optionals'])) {
					$stepo = explode(";", $v['optionals']);
					foreach ($stepo as $roptkey => $oo) {
						if (empty($oo)) {
							continue;
						}
						$stept = explode(":", $oo);
						$q = "SELECT * FROM `#__vikbooking_optionals` WHERE `id`=" . (int)$stept[0] . ";";
						$this->dbo->setQuery($q);
						$actopt = $this->dbo->loadAssoc();
						if (!$actopt) {
							continue;
						}
						if (!isset($rooms_options[$v['name']])) {
							$rooms_options[$v['name']] = array();
						}
						array_push($rooms_options[$v['name']], strip_tags($actopt['name']));
					}
				}
				if (!empty($v['extracosts'])) {
					$cur_extra_costs = json_decode($v['extracosts'], true);
					$cur_extra_costs = !is_array($cur_extra_costs) ? array() : $cur_extra_costs;
					foreach ($cur_extra_costs as $eck => $ecv) {
						if (!isset($rooms_extras[$v['name']])) {
							$rooms_extras[$v['name']] = array();
						}
						array_push($rooms_extras[$v['name']], $ecv['name']);
					}
				}
				// Distinctive Features
				if (empty($v['roomindex'])) {
					continue;
				}
				$room_params = json_decode($v['params'], true);
				if (is_array($room_params) && array_key_exists('features', $room_params) && @count($room_params['features']) > 0) {
					foreach ($room_params['features'] as $rind => $rfeatures) {
						if ($rind != $v['roomindex']) {
							continue;
						}
						foreach ($rfeatures as $fname => $fval) {
							if(strlen($fval)) {
								$rooms_indexes[] = '#'.$rind.' - '.JText::_($fname).': '.$fval;
								break;
							}
						}
					}
				}
			}
		}
		//

		/**
		 * We use the CPin class to get the customer assigned to this booking
		 * so that we can also obtain the information about the pre-checkin.
		 * 
		 * @since 	1.7.4
		 */
		if (!method_exists('VikBooking', 'getCPinIstance')) {
			$customer = array();
		} else {
			$cpin = VikBooking::getCPinIstance();
			$cpin->is_admin = true;
			$customer = $cpin->getCustomerFromBooking($orderInfo['id']);
			if (count($customer) && isset($customer['pax_data']) && !empty($customer['pax_data'])) {
				if (is_string($customer['pax_data'])) {
					$customer['pax_data'] = json_decode($customer['pax_data']);
				}
			}
		}
		//

		$response->id = $orderInfo['id'];
		$response->ts = $orderInfo['ts'];
		$response->created_on = date('Y-m-d H:i', $orderInfo['ts']);
		$response->status = ucfirst($orderInfo['status']);
		/**
		 * we immediately inject in the very first properties the currency symbol.
		 */
		$response->currency_symbol = $currency_symb;
		//
		$response->nights = $orderInfo['days'];
		$response->checkin = date('Y-m-d H:i', $orderInfo['checkin']);
		$response->checkout = date('Y-m-d H:i', $orderInfo['checkout']);
		$response->number_of_rooms = $orderInfo['roomsnum'];
		$response->rooms = $orderInfo['room_names'];
		if (count($rooms_indexes)) {
			$response->indexes = implode(', ', $rooms_indexes);
		}
		$response->adults = $orderInfo['tot_adults'];
		$response->children = $orderInfo['tot_children'];
		$response->rooms_data = $rooms_data;
		if (count($rooms_options)) {
			$option_strings = array();
			foreach ($rooms_options as $rname => $ropts) {
				if ($orderInfo['roomsnum'] > 1) {
					array_push($option_strings, $rname . ': ' . implode(', ', $ropts));
				} else {
					array_push($option_strings, implode(', ', $ropts));
				}
			}
			$response->options = implode(' - ', $option_strings);
		}
		if (count($rooms_extras)) {
			$extras_strings = array();
			foreach ($rooms_extras as $rname => $rextras) {
				if ($orderInfo['roomsnum'] > 1) {
					array_push($extras_strings, $rname . ': ' . implode(', ', $rextras));
				} else {
					array_push($extras_strings, implode(', ', $rextras));
				}
			}
			$response->extras = implode(' - ', $extras_strings);
		}
		if (strlen($orderInfo['nominative']) > 1) {
			$response->customer_name = $orderInfo['nominative'];
		} else {
			$cust_data_lines = explode("\n", $orderInfo['custdata']);
			$first_cust_info = explode(":", $cust_data_lines[0]);
			$response->customer = count($first_cust_info) > 1 ? $first_cust_info[1] : $first_cust_info[0];
		}

		/**
		 * If available, we return the customer profile picture (avatar).
		 * 
		 * @since 	1.8.6
		 */
		if (!empty($customer['pic'])) {
			$use_customer_pic = strpos($customer['pic'], 'http') === 0 ? $customer['pic'] : VBO_SITE_URI . 'resources/uploads/' . $customer['pic'];
			$response->customer_pic = $use_customer_pic;
		}

		$response->country = $orderInfo['country'];
		$response->email = $orderInfo['custmail'];
		$response->phone = $orderInfo['phone'];
		if ((int)$orderInfo['checked'] != 0) {
			if ($orderInfo['checked'] < 0) {
				// no show
				$response->registration = JText::_('VCMAPPCHECKEDSTATUSNOS');
			} elseif ($orderInfo['checked'] == 1) {
				// checked in
				$response->registration = JText::_('VCMAPPCHECKEDSTATUSIN');
			} elseif ($orderInfo['checked'] == 2) {
				// checked out
				$response->registration = JText::_('VCMAPPCHECKEDSTATUSOUT');
			}
		}
		$response->total = $orderInfo['total'] > 0 ? $orderInfo['total'] : 0;
		if ($orderInfo['totpaid'] > 0) {
			$response->total_paid = $orderInfo['totpaid'];
		}
		if (!empty($orderInfo['channel']) && $orderInfo['channel'] != 'Channel Manager') {
			$source = explode('_', $orderInfo['channel']);
			$source = count($source) > 1 ? $source[1] : $source[0];
			$response->source = $this->clearSourceName($source);
		}
		if (!empty($orderInfo['idorderota'])) {
			$response->ota_id = $orderInfo['idorderota'];
		}
		if (!empty($orderInfo['cmms']) && $orderInfo['cmms'] > 0) {
			$response->commissions = $orderInfo['cmms'];
		}
		if (!empty($orderInfo['idpayment'])) {
			$paym_info = explode('=', $orderInfo['idpayment']);
			$paym_info = count($paym_info) > 1 ? $paym_info[1] : $paym_info[0];
			$response->payment = $paym_info;
		}
		if (!empty($orderInfo['adminnotes'])) {
			$response->notes = $orderInfo['adminnotes'];
		}

		/**
		 * Attempt to include the booking expected payout information from OTA.
		 * 
		 * @since 	1.9.16
		 */
		$ota_type_data = !empty($orderInfo['ota_type_data']) ? ((array) json_decode($orderInfo['ota_type_data'], true)) : [];
		if ($orderInfo['status'] == 'confirmed' && !empty($orderInfo['channel']) && !empty($orderInfo['idorderota']) && $orderInfo['checkout'] >= time() && !empty($ota_type_data['expected_payout'])) {
			if (method_exists('VikBooking', 'formatCurrencyNumber')) {
				$response->expected_payout = VikBooking::formatCurrencyNumber(VikBooking::numberFormat($ota_type_data['expected_payout']), (($orderInfo['chcurrency'] ?? '') ?: $currency_symb));
			}
		}

		/**
		 * We return the check-in status.
		 * 
		 * 0  = no registration
		 * 1  = checked-in
		 * 2  = checked-out
		 * -1 = no-show
		 * 10 = pre checked-in
		 * 
		 * @since 	1.7.4
		 */
		$response->checkin_status = (int)$orderInfo['checked'];
		if ($response->checkin_status === 0) {
			// no registration could be overridden with "pre checked-in" if pax_data is available
			if (count($customer) && isset($customer['pax_data']) && !empty($customer['pax_data'])) {
				// pre check-in performed via front-end (code = 10)
				$response->checkin_status = 10;
			}
		}

		/**
		 * We need to return the raw customer data and the customer record.
		 * We do it only when it is not the App calling the APIs.
		 * 
		 * @since 	1.7.0
		 */
		if (!$this->isAppE4jConnect()) {
			// raw customer data is useful for OTA bookings
			$response->customer_raw_data = $orderInfo['custdata'];

			// customer record
			$response->customer_details = new stdClass;
			if (is_array($customer) && count($customer)) {
				$response->customer_details = $customer;
			}

			/**
			 * When it is not the e4jConnect App calling this API, we also include
			 * the information about the children age for each room booked.
			 * 
			 * @since 	1.8.1
			 */
			if (count($children_age)) {
				$response->children_age = $children_age;
			}

			/**
			 * We return a new raw property when it is not the App making the call.
			 * 
			 * @since 	1.8.11
			 */
			$response->raw = $orderInfo;
		}

		/**
		 * We check if the booking has got a guest review, and if a host-to-guest review is supported.
		 * 
		 * @since 	1.8.1
		 */
		$q = "SELECT `id`, `score` FROM `#__vikchannelmanager_otareviews` WHERE `idorder`={$orderInfo['id']}";
		$this->dbo->setQuery($q, 0, 1);
		$review_data = $this->dbo->loadObject();
		if ($review_data) {
			// set review information
			$response->review_id = $review_data->id;
			$response->review_score = round($review_data->score, 2);
		}
		// check host to guest review
		if (VikChannelManager::hostToGuestReviewSupported($orderInfo)) {
			$response->host_guest_review = 1;
		}

		/**
		 * Check the invoice status.
		 * 
		 * @since 	1.8.1
		 */
		if ($orderInfo['status'] == 'confirmed' && $orderInfo['closure'] < 1) {
			$use_sid = $orderInfo['sid'] ?: $orderInfo['idorderota'];
			$invoice_exists = is_file(VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'invoices' . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR . $orderInfo['id'] . '_' . $use_sid . '.pdf');
			$invoice_url = null;
			if ($invoice_exists) {
				$invoice_url = VBO_SITE_URI . 'helpers/invoices/generated/' . $orderInfo['id'] . '_' . $use_sid . '.pdf';
			}
			$response->invoice = $invoice_exists ? $invoice_url : 0;
		}

		// Set the response body for the App
		$this->response->body = $response;
	}

	/**
	 * App Bookings List Request
	 *
	 * Method to retrieve a list of bookings according to some parameters.
	 * The number of bookings per request is limited to 20 by default.
	 * Pagination supported if there are more bookings than the lim.
	 *
	 * @return void
	 */
	private function appBookingsList()
	{
		$response = new stdClass();

		//Getting the date and the type of the request
		$requestDateFrom = $this->input->getString('fdate', '');
		if (empty($requestDateFrom)) {
			$requestDateFrom = date('Y-m-d');
		}
		$requestDateTo = $this->input->getString('tdate', '');
		$requestType = $this->input->getString('type', 'bookings');
		$requestStatus = $this->input->getString('status', '');
		$requestLim = $this->input->getInt('lim', 20);
		$requestPage = $this->input->getInt('page', 0);
		$requestIds = $this->input->getVar('ids', array());
		$limStart = $requestPage * $requestLim;
		$limStart = $limStart >= 0 ? $limStart : 0;

		//If the date is not formatted correctly, an error is sent and false is returned
		if (count(explode('-', $requestDateFrom)) != 3) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}
		if (!empty($requestDateTo) && count(explode('-', $requestDateTo)) != 3) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}

		//Getting the midnight (both the 00:00 and the 23:59) timestamp for the selected date
		$date_start_ts 	= strtotime($requestDateFrom);
		$date_to_info 	= getdate((!empty($requestDateTo) ? strtotime($requestDateTo) : $date_start_ts));
		$date_end_ts 	= mktime(23, 59, 59, $date_to_info['mon'], $date_to_info['mday'], $date_to_info['year']);
		$bookings 		= array();
		$bookingsData	= array();
		$clauses 		= array();
		$total_bookings = 0;
		$current_page 	= 0;

		if (!empty($requestStatus) && in_array($requestStatus, array('confirmed', 'standby', 'cancelled'))) {
			$clauses[] = "`o`.`status`='".$requestStatus."'" ;
		}

		$bids_filter = array();
		if (is_array($requestIds) && count($requestIds) && !empty($requestIds[0])) {
			foreach ($requestIds as $bid) {
				$bids_filter[] = intval($bid);
			}
			$clauses[] = "`o`.`id` IN (".implode(', ', $bids_filter).")";
		}
		
		$dtype = "`o`.`ts`";
		$otype = "`o`.`id` DESC";
		if (!empty($requestType)) {
			if ($requestType == 'arrivals') {
				$dtype = "`o`.`checkin`";
				$otype = "`o`.`checkin` ASC";
			} elseif ($requestType == 'departures') {
				$dtype = "`o`.`checkout`";
				$otype = "`o`.`checkout` ASC";
			}
		}
		if (!(count($bids_filter) > 0)) {
			//When filtering by IDs do not use dates filters
			$clauses[] = "$dtype >= $date_start_ts";
			$clauses[] = "$dtype <= $date_end_ts";
		}

		//Load all the bookings according to the input parameters
		$q = "SELECT SQL_CALC_FOUND_ROWS `o`.*,
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
			ORDER BY $otype";

		$this->dbo->setQuery($q, $limStart, $requestLim);
		$bookingsData = $this->dbo->loadAssocList();
		if ($bookingsData) {
			$this->dbo->setQuery('SELECT FOUND_ROWS();');
			$total_bookings = (int)$this->dbo->loadResult();
			$total_pages = ceil($total_bookings / $requestLim);
			if ($total_bookings > $requestLim) {
				if (($requestPage + 1) < $total_pages) {
					// pagination starts from 0
					$current_page = $requestPage;
				} elseif (($requestPage + 1) == $total_pages) {
					// we are on the last page (-1)
					$current_page = -1;
				}
			} else {
				// we are on the only and last page (-1)
				$current_page = -1;
			}
		} else {
			// no results and no other pages
			$current_page = -1;
		}

		foreach ($bookingsData as $book) {
			if ((int)$book['closure'] > 0) {
				// when room closed, just force the customer name to 'room closed'
				$book['nominative'] = JText::_('VCMAPPROOMCLOSED');
			}
			$booking_info = new stdClass;
			$booking_info->id = $book['id'];
			$booking_info->created_on = date('Y-m-d H:i', $book['ts']);
			$booking_info->status = ucfirst($book['status']);
			$booking_info->nights = $book['days'];
			$booking_info->checkin = date('Y-m-d H:i', $book['checkin']);
			$booking_info->checkout = date('Y-m-d H:i', $book['checkout']);
			$booking_info->number_of_rooms = $book['roomsnum'];
			$booking_info->rooms = $book['room_names'];
			$booking_info->adults = $book['tot_adults'];
			$booking_info->children = $book['tot_children'];
			if (!empty($book['nominative'])) {
				$booking_info->customer_name = $book['nominative'];
			} else {
				$cust_data_lines = explode("\n", $book['custdata']);
				$first_cust_info = explode(":", $cust_data_lines[0]);
				$booking_info->customer = count($first_cust_info) > 1 ? $first_cust_info[1] : $first_cust_info[0];
			}
			$booking_info->country = $book['country'];
			$booking_info->email = $book['custmail'];
			$booking_info->phone = $book['phone'];
			$booking_info->source = 'VBO';
			if (!empty($book['channel']) && $book['channel'] != 'Channel Manager') {
				$source = explode('_', $book['channel']);
				$source = count($source) > 1 ? $source[1] : $source[0];
				$booking_info->source = $this->clearSourceName($source);
			}
			if (!empty($book['idorderota'])) {
				$booking_info->ota_id = $book['idorderota'];
			}

			/**
			 * We return a new raw property when it is not the App making the call.
			 * 
			 * @since 	1.8.11
			 */
			if (!$this->isAppE4jConnect()) {
				$booking_info->raw = $book;
			}

			// push booking object
			array_push($bookings, $booking_info);
		}

		//Setting the response
		$response->total_bookings = $total_bookings;
		$response->page = $current_page;
		$response->bookings = $bookings;

		//Set the response object as the body for the App
		$this->response->body = $response;
	}

	/**
	 * App Booking Search Request
	 *
	 * Method to retrieve a list of bookings according to some parameters.
	 * The number of bookings per request is limited to 20 by default.
	 * Pagination supported if there are more bookings than the lim.
	 *
	 * @return void
	 */
	private function bookingSearch()
	{
		$response = new stdClass();

		// get the keyword and status filters
		$requestKeyword = $this->input->getString('keyword', '');
		$requestStatus = $this->input->getString('status', '');
		$requestLim = $this->input->getInt('lim', 20);
		$requestPage = $this->input->getInt('page', 0);
		$limStart = $requestPage * $requestLim;
		$limStart = $limStart >= 0 ? $limStart : 0;

		// if no keyword provided, an error is sent and false is returned
		if (empty($requestKeyword)) {
			$this->setError(JText::_('VCMAPPRQEMPTY'));
			return false;
		}

		// prepare variables
		$bookings 		= array();
		$bookingsData	= array();
		$clauses 		= array();
		$keyclauses 	= array();
		$total_bookings = 0;
		$current_page 	= 0;

		if (!empty($requestStatus) && in_array($requestStatus, array('confirmed', 'standby', 'cancelled'))) {
			// filter by status
			array_push($clauses, "`o`.`status`=" . $this->dbo->quote($requestStatus));
		}

		// always avoid closures
		array_push($clauses, "`o`.`closure`=0");
		
		// compose keywords clauses
		if (ctype_digit($requestKeyword)) {
			// numeric filter should seek over the booking ID, confirmation number, phone number
			array_push($keyclauses, "`o`.`id`=" . $this->dbo->quote($requestKeyword));
			array_push($keyclauses, "`o`.`confirmnumber`=" . $this->dbo->quote($requestKeyword));
			array_push($keyclauses, "`o`.`phone` LIKE " . $this->dbo->quote('%' . $requestKeyword));
		}

		if (strpos($requestKeyword, '@') !== false) {
			// probably an email address was provided
			array_push($keyclauses, "`o`.`custmail` LIKE " . $this->dbo->quote('%' . $requestKeyword . '%'));
		}

		// always seek for OTA booking ID, which may not be all numeric
		array_push($keyclauses, "`o`.`idorderota`=" . $this->dbo->quote($requestKeyword));

		// always seek for customer name, company name and VAT
		array_push($keyclauses, "CONCAT_WS(' ', `c`.`first_name`, `c`.`last_name`) LIKE " . $this->dbo->quote('%' . $requestKeyword . '%'));
		array_push($keyclauses, "`c`.`company` LIKE " . $this->dbo->quote('%' . $requestKeyword . '%'));
		array_push($keyclauses, "`c`.`vat`=" . $this->dbo->quote($requestKeyword));

		// check if the search should be made directly on the customer id
		if (strpos($requestKeyword, 'customer_id:') === 0) {
			// unset all key-clauses
			$keyclauses = array();
			// push idcustomer filter
			array_push($clauses, "`co`.`idcustomer`=" . intval(trim(str_replace('customer_id:', '', $requestKeyword))));
		}

		// merge keyword clauses to all clauses
		if (count($keyclauses)) {
			array_push($clauses, '(' . implode(' OR ', $keyclauses) . ')');
		}

		// load all the bookings according to the input parameters
		$q = "SELECT SQL_CALC_FOUND_ROWS `o`.*, `co`.`idcustomer`, `c`.`company`, `c`.`vat`,
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
			LEFT JOIN `#__vikbooking_customers_orders` AS `co` ON `co`.`idorder`=`o`.`id` 
			LEFT JOIN `#__vikbooking_customers` AS `c` ON `c`.`id`=`co`.`idcustomer` 
			WHERE ".implode(' AND ', $clauses)." 
			ORDER BY `o`.`checkin` DESC";

		$this->dbo->setQuery($q, $limStart, $requestLim);
		$bookingsData = $this->dbo->loadAssocList();
		if ($bookingsData) {
			$this->dbo->setQuery('SELECT FOUND_ROWS();');
			$total_bookings = (int)$this->dbo->loadResult();
			$total_pages = ceil($total_bookings / $requestLim);
			if ($total_bookings > $requestLim) {
				if (($requestPage + 1) < $total_pages) {
					// pagination starts from 0
					$current_page = $requestPage;
				} elseif (($requestPage + 1) == $total_pages) {
					// we are on the last page (-1)
					$current_page = -1;
				}
			} else {
				// we are on the only and last page (-1)
				$current_page = -1;
			}
		} else {
			// no results and no other pages
			$current_page = -1;
		}

		foreach ($bookingsData as $book) {
			$booking_info = new stdClass;
			$booking_info->id = $book['id'];
			$booking_info->created_on = date('Y-m-d H:i', $book['ts']);
			$booking_info->status = ucfirst($book['status']);
			$booking_info->nights = $book['days'];
			$booking_info->checkin = date('Y-m-d H:i', $book['checkin']);
			$booking_info->checkout = date('Y-m-d H:i', $book['checkout']);
			$booking_info->number_of_rooms = $book['roomsnum'];
			$booking_info->rooms = $book['room_names'];
			$booking_info->adults = $book['tot_adults'];
			$booking_info->children = $book['tot_children'];
			if (!empty($book['nominative'])) {
				$booking_info->customer_name = $book['nominative'];
			} else {
				$cust_data_lines = explode("\n", $book['custdata']);
				$first_cust_info = explode(":", $cust_data_lines[0]);
				$booking_info->customer = count($first_cust_info) > 1 ? $first_cust_info[1] : $first_cust_info[0];
			}
			$booking_info->country = $book['country'];
			$booking_info->email = $book['custmail'];
			$booking_info->phone = $book['phone'];
			$booking_info->source = 'VBO';
			if (!empty($book['channel']) && $book['channel'] != 'Channel Manager') {
				$source = explode('_', $book['channel']);
				$source = count($source) > 1 ? $source[1] : $source[0];
				$booking_info->source = $this->clearSourceName($source);
			}
			if (!empty($book['idorderota'])) {
				$booking_info->ota_id = $book['idorderota'];
			}
			array_push($bookings, $booking_info);
		}

		//Setting the response
		$response->total_bookings = $total_bookings;
		$response->page = $current_page;
		$response->bookings = $bookings;

		//Set the response object as the body for the App
		$this->response->body = $response;
	}

	/**
	 * App Get Rooms Request
	 *
	 * This function composes an array with the details for each room.
	 *
	 * @return void
	 */
	private function appGetRooms()
	{
		$response = new stdClass();

		$response->rooms = [];

		// Query to select data
		$q = "SELECT * 
			FROM `#__vikbooking_rooms`
			WHERE `avail` = 1
			ORDER BY `name` ASC;";

		$this->dbo->setQuery($q);
		$roomInfos = $this->dbo->loadAssocList();

		if (!$roomInfos) {
			$this->setError(JText::_('VCMAPPNOROOMAV'));
			return false;
		}

		foreach ($roomInfos as $roomInfo) {
			$room = new stdClass;

			$room->id 			= (int)$roomInfo['id'];
			$room->name 		= $roomInfo['name'];
			$room->image 		= VBO_SITE_URI."resources/uploads/".$roomInfo['img'];
			$room->minAdult 	= (int)$roomInfo['fromadult'];
			$room->maxAdult 	= (int)$roomInfo['toadult'];
			$room->minChild 	= (int)$roomInfo['fromchild'];
			$room->maxChild 	= (int)$roomInfo['tochild'];
			$room->minTotPeople = (int)$roomInfo['mintotpeople'];
			$room->maxTotPeople = (int)$roomInfo['totpeople'];
			$room->maxUnits 	= (int)$roomInfo['units'];
			$room->description  = $roomInfo['smalldesc'];

			$response->rooms[] = $room;
		}

		// Set the response body for the App
		$this->response->body = $response;
	}

	/**
	 * App Get Availability Request
	 * Reads the rooms availability for a certain interval of dates
	 * and sets the response to an array with the booking IDs 
	 * on the occupied dates and the remaining units for each room type.
	 *
	 * @return void
	 */
	private function appGetAvailability()
	{
		$calendar = array();

		$date_from = $this->input->getString('fdate', date('Y-m-d'));
		//If the date is not formatted correctly, an error is sent and false is returned
		if (count(explode('-', $date_from)) != 3) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}
		$next_months 	= $this->input->getInt('months', 1);
		$next_months 	= $next_months < 1 ? 1 : $next_months;
		$room_id 		= $this->input->getInt('room_id', 0);
		$from_info 		= getdate(strtotime($date_from));
		$to_info 		= getdate(mktime(23, 59, 59, ($from_info['mon'] + $next_months - 1), 1, $from_info['year']));
		$from_ts 		= mktime(0, 0, 0, $from_info['mon'], 1, $from_info['year']);
		$to_ts 			= mktime(23, 59, 59, $to_info['mon'], date('t', $to_info[0]), $to_info['year']);
		$arr_busy		= array();
		$arr_rooms 		= array();
		$total_units 	= 0;
		$dates_booked 	= (bool)$this->input->getInt('dates_booked_only', 0);

		//Count total units for the request
		if (!empty($room_id)) {
			$q = "SELECT `units` FROM `#__vikbooking_rooms` WHERE `id`=".(int)$room_id.";";
		} else {
			$q = "SELECT SUM(`units`) FROM `#__vikbooking_rooms` WHERE `avail`=1;";
		}
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() > 0) {
			$total_units = (int)$this->dbo->loadResult();
		}

		//Load busy records and some basic room details
		$q = "SELECT `b`.*, `ob`.`idorder`, `r`.`name`, `r`.`units` 
			FROM `#__vikbooking_busy` AS `b` 
			LEFT JOIN `#__vikbooking_ordersbusy` `ob` ON `ob`.`idbusy`= `b`.`id` 
			LEFT JOIN `#__vikbooking_rooms` `r` ON `b`.`idroom`=`r`.`id` 
			WHERE ".(!empty($room_id) ? "`b`.`idroom`='".$room_id."' AND " : "")."(`b`.`checkin`>=".$from_ts." OR `b`.`checkout`>=".$from_ts.") AND (`b`.`checkin`<=".$to_ts." OR `b`.`checkout`<=".$from_ts.") 
			ORDER BY `b`.`checkin` ASC;";
		$this->dbo->setQuery($q);
		$busy = $this->dbo->loadAssocList();
		if ($busy) {
			foreach ($busy as $b) {
				if(!array_key_exists($b['idroom'], $arr_busy)) {
					$arr_busy[$b['idroom']] = array();
					$arr_rooms[$b['idroom']] = array(
						'name' => $b['name'],
						'units' => $b['units']
					);
				}
				array_push($arr_busy[$b['idroom']], $b);
			}
		}
		//Count bookings and rooms remaining availability for each day
		$check_busy = (count($arr_busy) > 0);
		$nowts = getdate($from_ts);
		while ($nowts[0] < $to_ts) {
			$bids_pool = array();
			$brooms_pool = array();
			$ucount = 0;
			if ($check_busy) {
				foreach ($arr_busy as $roomid => $busy) {
					$totfound = 0;
					foreach ($busy as $b) {
						$checkin_info = getdate($b['checkin']);
						$checkin_ts = mktime(0, 0, 0, $checkin_info['mon'], $checkin_info['mday'], $checkin_info['year']);
						$checkout_info = getdate($b['checkout']);
						$checkout_ts = mktime(0, 0, 0, $checkout_info['mon'], $checkout_info['mday'], $checkout_info['year']);
						if ($nowts[0] >= $checkin_ts && $nowts[0] < $checkout_ts) {
							if (!in_array($b['idorder'], $bids_pool)) {
								$bids_pool[] = $b['idorder'];
							}
							$totfound++;
						}
					}
					if ($totfound > 0) {
						$brooms_pool[$arr_rooms[$roomid]['name']] = $arr_rooms[$roomid]['units'] - $totfound;
						$ucount += $totfound;
					}
				}
			}
			$res_key = date('Y-m-d', $nowts[0]);
			$nowts = getdate(mktime(0, 0, 0, $nowts['mon'], ($nowts['mday'] + 1), $nowts['year']));
			if ($dates_booked && count($bids_pool) < 1) {
				//do not set any value to the calendar response if 'dates_booked_only'
				continue;
			}
			$calendar[$res_key] = array(
				'bcount' => count($bids_pool),
				'ucount' => $ucount,
				'bids' => $bids_pool,
				'brooms' => $brooms_pool
			);
		}

		$response = new stdClass;
		$response->total_units = $total_units;
		$response->calendar = $calendar;

		//Set the response body for the App
		$this->response->body = $response;
	}

	/**
	 * App Cancel Booking Request
	 * Sets the booking to cancelled by freeing up the
	 * availability on the website, as well as on the channels.
	 *
	 * @return void
	 */
	private function appCancelBooking()
	{
		$bid = $this->input->getInt('bid', 0);
		if (empty($bid)) {
			$this->setError(JText::_('VCMAPPEMPTYBOOKINGID'));
			return false;
		}

		$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".(int)VikChannelManagerConfig::MOBILEAPP." LIMIT 1;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() < 1) {
			$this->setError(JText::_('VCMAPPCHREQREFUSED'));
			return false;
		}

		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".(int)$bid." AND `status`!='cancelled' LIMIT 1;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() < 1) {
			$this->setError(JText::_('VCMAPPSELBOOKINGUNAV'));
			return false;
		}
		$booking = $this->dbo->loadAssoc();

		//set status to cancelled
		$q = "UPDATE `#__vikbooking_orders` SET `status`='cancelled' WHERE `id`=".(int)$booking['id'].";";
		$this->dbo->setQuery($q);
		$this->dbo->execute();

		//remove any temporary lock
		$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `idorder`=".(int)$booking['id'].";";
		$this->dbo->setQuery($q);
		$this->dbo->execute();

		$prev_conf_ids = array();
		if ($booking['status'] == 'confirmed') {
			$prev_conf_ids[] = $booking['id'];
		}

		//free up busy records
		$q = "SELECT * FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$booking['id'].";";
		$this->dbo->setQuery($q);
		$ordbusy = $this->dbo->loadAssocList();
		if ($ordbusy) {
			foreach ($ordbusy as $ob) {
				$q = "DELETE FROM `#__vikbooking_busy` WHERE `id`='".$ob['idbusy']."';";
				$this->dbo->setQuery($q);
				$this->dbo->execute();
			}
		}
		$q = "DELETE FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$booking['id'].";";
		$this->dbo->setQuery($q);
		$this->dbo->execute();

		$this->importVboLib();
		//VBO 1.10 or higher - Booking History
		if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
			VikBooking::getBookingHistoryInstance()->setBid($booking['id'])->store('AR');
		}
		//

		$channels_updated = 0;

		//send request to e4jConnect
		if (count($prev_conf_ids) > 0) {
			if (!class_exists('SynchVikBooking')) {
				require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "synch.vikbooking.php");
			}
			$vcm = new SynchVikBooking($booking['id']);
			$vcm->setSkipCheckAutoSync();
			$vcm->setFromCancellation(array('id' => $booking['id']));
			$action_res = $vcm->sendRequest();
			if ($action_res === false && $vcm->isAvailabilityRequest()) {
				/**
				 * we set an error by returning false only in case of failure
				 * and if there is at least one active API channel to update.
				 */
				$this->setError(JText::_('VCMAPPERRE4JCSYNC'));
				return false;
			} elseif ($action_res !== false) {
				$channels_updated = 1;
			}
		}

		/**
		 * It is now possible to completely erase from the DB
		 * a specific booking ID by passing "purge" = 1.
		 * 
		 * @since 	1.6.13
		 */
		$purge = $this->input->getInt('purge', 0);
		if ($purge > 0) {
			// delete the booking completely
			$q = "DELETE FROM `#__vikbooking_customers_orders` WHERE `idorder`=".(int)$booking['id'].";";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
			$q = "DELETE FROM `#__vikbooking_ordersrooms` WHERE `idorder`=".(int)$booking['id'].";";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
			$q = "DELETE FROM `#__vikbooking_orderhistory` WHERE `idorder`=".(int)$booking['id'].";";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
			$q = "DELETE FROM `#__vikbooking_orders` WHERE `id`=".(int)$booking['id'].";";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
		}
		//

		$response = new stdClass;
		$response->website = 1;
		$response->channels = $channels_updated;

		//Set the response body for the App
		$this->response->body = $response;
	}

	/**
	 * App Confirm Booking Request
	 * Sets the booking to confirmed by reducing the
	 * availability on the website, as well as on the channels.
	 *
	 * @return void
	 */
	private function appConfirmBooking()
	{
		$bid = $this->input->getInt('bid', 0);
		if (empty($bid)) {
			$this->setError(JText::_('VCMAPPEMPTYBOOKINGID'));
			return false;
		}

		$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".(int)VikChannelManagerConfig::MOBILEAPP." LIMIT 1;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() < 1) {
			$this->setError(JText::_('VCMAPPCHREQREFUSED'));
			return false;
		}

		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".(int)$bid." AND `status`='standby' LIMIT 1;";
		$this->dbo->setQuery($q);
		$booking = $this->dbo->loadAssoc();
		if (!$booking) {
			$this->setError(JText::_('VCMAPPSELBOOKINGUNAV'));
			return false;
		}

		/**
		 * We get the original booking status if the booking comes from a channel, in order to trigger any possibly needed action.
		 * 
		 * @since 	1.8.2
		 */
		$original_book_status = null;
		if (!empty($booking['idorderota']) && !empty($booking['channel'])) {
			$original_book_status = $booking['status'];
		}

		$this->importVboLib();

		$q = "SELECT `or`.*,`r`.`id` AS `r_reference_id`,`r`.`name`,`r`.`units`,`r`.`fromadult`,`r`.`toadult`,`r`.`params` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`=".(int)$booking['id']." AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
		$this->dbo->setQuery($q);
		$ordersrooms = $this->dbo->loadAssocList();

		$notavail = array();
		foreach ($ordersrooms as $ind => $or) {
			if (!VikBooking::roomBookable($or['idroom'], $or['units'], $booking['checkin'], $booking['checkout'])) {
				$notavail[] = $or['name'];
			}
		}
		if (count($notavail) > 0) {
			//some rooms are not available and so the booking cannot be confirmed
			$this->setError(JText::sprintf('VCMAPPCONFBROOMNA', implode(', ', $notavail)));
			return false;
		}

		$rooms_booked = array();
		foreach ($ordersrooms as $ind => $or) {
			array_push($rooms_booked, (int)$or['idroom']);
			$q = "INSERT INTO `#__vikbooking_busy` (`idroom`,`checkin`,`checkout`,`realback`) VALUES('".$or['idroom']."','".$booking['checkin']."','".$booking['checkout']."','".$booking['checkout']."');";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
			$lid = $this->dbo->insertid();
			$q = "INSERT INTO `#__vikbooking_ordersbusy` (`idorder`,`idbusy`) VALUES(".(int)$booking['id'].", ".(int)$lid.");";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
		}

		//generate confirmation number and update status
		$confirmnumb = date('ym');
		$confirmnumb .= (string)rand(100, 999);
		$confirmnumb .= (string)rand(10, 99);
		$confirmnumb .= (string)$booking['id'];		
		$q = "UPDATE `#__vikbooking_orders` SET `status`='confirmed', `confirmnumber`=".$this->dbo->quote($confirmnumb)." WHERE `id`=".(int)$booking['id'].";";
		$this->dbo->setQuery($q);
		$this->dbo->execute();

		//VBO 1.10 or higher - Booking History
		if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
			VikBooking::getBookingHistoryInstance()->setBid($booking['id'])->store('AC');
		}

		if (method_exists('VikBooking', 'updateSharedCalendars')) {
			// check if some of the rooms booked have shared calendars
			VikBooking::updateSharedCalendars($booking['id'], $rooms_booked, $booking['checkin'], $booking['checkout']);
		}

		//send request to e4jConnect
		if (!class_exists('SynchVikBooking')) {
			require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "synch.vikbooking.php");
		}
		$vcm = new SynchVikBooking($booking['id']);
		$vcm->setSkipCheckAutoSync();
		if (!empty($original_book_status)) {
			$vcm->setBookingPreviousStatus($original_book_status);
		}
		$action_res = $vcm->sendRequest();
		if ($action_res === false && $vcm->isAvailabilityRequest()) {
			/**
			 * we set an error by returning false only in case of failure
			 * and if there is at least one active API channel to update.
			 */
			$this->setError(JText::_('VCMAPPERRE4JCSYNC'));
			return false;
		}

		$response = new stdClass;
		$response->website = 1;
		$response->channels = 1;

		//Set the response body for the App
		$this->response->body = $response;
	}

	/**
	 * App Modify Booking Request
	 * Modifies some information about the booking like
	 * rooms, dates, customer email, phone etc..
	 *
	 * @return void
	 */
	private function appModifyBooking()
	{
		$bdates_updated = $brooms_updated = $must_call_e4jc = false;
		$bid 			= $this->input->getInt('bid', 0);
		$checkin 		= $this->input->getString('checkin', '');
		$checkout 		= $this->input->getString('checkout', '');
		$rooms_data 	= $this->input->getVar('rooms_data', array());
		$email 			= $this->input->getString('cemail', '');
		$phone 			= $this->input->getString('cphone', '');
		$notes 			= $this->input->getString('notes', '');
		$extra_notes 	= '';
		$dates_modified = false;
		$today_ts 		= mktime(0, 0, 0, date('n'), date('j'), date('Y'));

		if (empty($bid)) {
			$this->setError(JText::_('VCMAPPEMPTYBOOKINGID'));
			return false;
		}

		$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".(int)VikChannelManagerConfig::MOBILEAPP." LIMIT 1;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() < 1) {
			$this->setError(JText::_('VCMAPPCHREQREFUSED'));
			return false;
		}

		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".(int)$bid." AND `status`!='cancelled' LIMIT 1;";
		$this->dbo->setQuery($q);
		$booking = $this->dbo->loadAssoc();
		if (!$booking) {
			$this->setError(JText::_('VCMAPPSELBOOKINGUNAV'));
			return false;
		}

		//require the VikBooking library
		$this->importVboLib();

		//If the dates are not formatted correctly, an error is sent and false is returned
		if ((!empty($checkin) && count(explode('-', $checkin)) != 3) || (!empty($checkout) && count(explode('-', $checkout)) != 3)) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}
		if (!empty($checkin) && !empty($checkout)) {
			$dates_modified = true;
		}
		if ($dates_modified) {
			//Calculate the new timestamps for check-in and check-out
			$pcheckinh = 0;
			$pcheckinm = 0;
			$pcheckouth = 0;
			$pcheckoutm = 0;
			$timeopst = VikBooking::getTimeOpenStore();
			if (is_array($timeopst)) {
				$opent = VikBooking::getHoursMinutes($timeopst[0]);
				$closet = VikBooking::getHoursMinutes($timeopst[1]);
				$pcheckinh = $opent[0];
				$pcheckinm = $opent[1];
				$pcheckouth = $closet[0];
				$pcheckoutm = $closet[1];
			}
			$checkin_ts = strtotime($checkin) + (3600 * $pcheckinh) + (60 * $pcheckinm);
			$checkout_ts = strtotime($checkout) + (3600 * $pcheckouth) + (60 * $pcheckoutm);
			
			//check if dates are in the past or equal
			if (($checkin_ts < $today_ts && $checkout_ts < $today_ts) || $checkin == $checkout) {
				$this->setError(JText::_('VCMAPPINVALIDBDATES'));
				return false;
			}
			//check if dates are different from the original ones
			if (date('Y-m-d', $booking['checkin']) == date('Y-m-d', $checkin_ts) && date('Y-m-d', $booking['checkout']) == date('Y-m-d', $checkout_ts)) {
				$dates_modified = false;
			}
		}

		//Load booking rooms
		$q = "SELECT `or`.*,`r`.`name`,`r`.`idopt`,`r`.`units`,`r`.`fromadult`,`r`.`toadult` 
			FROM `#__vikbooking_ordersrooms` AS `or`, `#__vikbooking_rooms` AS `r` 
			WHERE `or`.`idorder`=".(int)$booking['id']." AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
		$this->dbo->setQuery($q);
		$ordersrooms = $this->dbo->loadAssocList();
		if (!$ordersrooms) {
			$this->setError(JText::_('VCMAPPSELBOOKINGUNAV'));
			return false;
		}
		//memorize the current rooms of this booking
		$booking['rooms_info'] = $ordersrooms;
		//

		//switching details
		$or_set_clauses = array();
		$toswitch = array();
		$idbooked = array();
		$rooms_units = array();
		$q = "SELECT `id`,`name`,`units` FROM `#__vikbooking_rooms`;";
		$this->dbo->setQuery($q);
		$all_rooms = $this->dbo->loadAssocList();
		if (!$all_rooms) {
			$this->setError(JText::_('VCMAPPNOROOMFOUND'));
			return false;
		}
		foreach ($all_rooms as $rr) {
			$rooms_units[$rr['id']] = array(
				'name' => $rr['name'],
				'units' => $rr['units']
			);
		}

		//begin rooms switch, if necessary
		foreach ($ordersrooms as $ind => $or) {
			$or_set_clauses[$ind] = array();
			//check request parameters for adults, children, traveler first and last name
			if (isset($rooms_data[$ind]['adults']) && intval($rooms_data[$ind]['adults']) >= 0) {
				$or_set_clauses[$ind][] = "`adults`=".(int)$rooms_data[$ind]['adults'];
			}
			if (isset($rooms_data[$ind]['children']) && intval($rooms_data[$ind]['children']) >= 0) {
				$or_set_clauses[$ind][] = "`children`=".(int)$rooms_data[$ind]['children'];
			}
			if (isset($rooms_data[$ind]['t_first_name']) && !empty($rooms_data[$ind]['t_first_name'])) {
				$or_set_clauses[$ind][] = "`t_first_name`=".$this->dbo->quote($rooms_data[$ind]['t_first_name']);
			}
			if (isset($rooms_data[$ind]['t_last_name']) && !empty($rooms_data[$ind]['t_last_name'])) {
				$or_set_clauses[$ind][] = "`t_last_name`=".$this->dbo->quote($rooms_data[$ind]['t_last_name']);
			}
			//
			$switch_to_id = isset($rooms_data[$ind]['idroom']) && intval($rooms_data[$ind]['idroom']) > 0 ? (int)$rooms_data[$ind]['idroom'] : 0;
			if ($switch_to_id > 0 && $switch_to_id != $or['idroom'] && array_key_exists($switch_to_id, $rooms_units)) {
				$idbooked[$or['idroom']]++;
				$orkey = count($toswitch);
				$toswitch[$orkey]['from'] = $or['idroom'];
				$toswitch[$orkey]['to'] = $switch_to_id;
				$toswitch[$orkey]['record'] = $or;
			}
		}
		if (count($toswitch)) {
			foreach ($toswitch as $ksw => $rsw) {
				$plusunit = array_key_exists($rsw['to'], $idbooked) && !$dates_modified ? $idbooked[$rsw['to']] : 0;
				if (!VikBooking::roomBookable($rsw['to'], ($rooms_units[$rsw['to']]['units'] + $plusunit), ($dates_modified ? $checkin_ts : $booking['checkin']), ($dates_modified ? $checkout_ts : $booking['checkout']))) {
					unset($toswitch[$ksw]);
					//one room to switch is not available on these dates. Do not proceed, just return an error
					$this->setError(JText::sprintf('VCMAPPCANNOTSWITCHROOM', $rsw['record']['name'], $rooms_units[$rsw['to']]['name']));
					return false;
				}
			}
			$brooms_updated = true;
			//reset first record rate
			reset($ordersrooms);
			$q = "UPDATE `#__vikbooking_ordersrooms` SET `idtar`=NULL,`roomindex`=NULL,`room_cost`=NULL WHERE `id`=".(int)$ordersrooms[0]['id'].";";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
			//
			foreach ($toswitch as $ksw => $rsw) {
				$q = "UPDATE `#__vikbooking_ordersrooms` SET `idroom`=".(int)$rsw['to'].",`idtar`=NULL,`roomindex`=NULL,`room_cost`=NULL WHERE `id`=".(int)$rsw['record']['id'].";";
				$this->dbo->setQuery($q);
				$this->dbo->execute();
				//memorize Notes field for this booking to keep track of the previous room that was assigned
				$prev_room_name = array_key_exists($rsw['from'], $rooms_units) ? $rooms_units[$rsw['from']]['name'] : '';
				if (!empty($prev_room_name)) {
					$extra_notes .= JText::sprintf('VCMAPPPREVROOMMOVED', $prev_room_name, date('Y-m-d H:i:s'))."\n";
				}
				//
				if ($booking['status'] == 'confirmed') {
					$must_call_e4jc = true;
					//update record in _busy
					$q = "SELECT `b`.`id`,`b`.`idroom`,`ob`.`idorder` 
						FROM `#__vikbooking_busy` AS `b`, `#__vikbooking_ordersbusy` AS `ob` 
						WHERE `b`.`idroom`=" . (int)$rsw['from'] . " AND `b`.`id`=`ob`.`idbusy` AND `ob`.`idorder`=".$booking['id']." LIMIT 1;";
					$this->dbo->setQuery($q);
					$cur_busy = $this->dbo->loadAssocList();
					if ($cur_busy) {
						$q = "UPDATE `#__vikbooking_busy` SET `idroom`=".$rsw['to']." WHERE `id`=".$cur_busy[0]['id']." AND `idroom`=".$cur_busy[0]['idroom']." LIMIT 1;";
						$this->dbo->setQuery($q);
						$this->dbo->execute();
					}
				} elseif ($booking['status'] == 'standby') {
					//remove record in _tmplock
					$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `idorder`=" . (int)$booking['id'] . ";";
					$this->dbo->setQuery($q);
					$this->dbo->execute();
				}
			}
		} 
		//end rooms switch

		// make sure the room is actually available on the new dates
		if (!$toswitch && $dates_modified) {
			/**
			 * Count the units available for every room involved in the
			 * reservation in order to tell if the new dates are bookable.
			 */
			$plus_units = [];
			foreach ($ordersrooms as $ind => $or) {
				if (!isset($plus_units[$or['idroom']])) {
					$plus_units[$or['idroom']] = 0;
				}
				$plus_units[$or['idroom']]++;
			}

			// get busy records to skip for the availability check
			$skip_bids = VikBooking::loadBookingBusyIds($booking['id']);

			foreach ($ordersrooms as $ind => $or) {
				if (!VikBooking::roomBookable($or['idroom'], ($plus_units[$or['idroom']] ?? 1), $checkin_ts, $checkout_ts, $skip_bids)) {
					$this->setError(JText::sprintf('VCMAPPCONFBROOMNA', $rooms_units[$or['idroom']]['name']));
					return false;
				}
			}
		}

		// update booking rooms adults, children, traveler first and last name
		if ($or_set_clauses) {
			foreach ($or_set_clauses as $ind => $or_set_clause) {
				if (!$or_set_clause || !isset($ordersrooms[$ind]['id'])) {
					continue;
				}
				$q = "UPDATE `#__vikbooking_ordersrooms` SET ".implode(', ', $or_set_clause)." WHERE `id`=".$ordersrooms[$ind]['id']." AND `idorder`=".(int)$booking['id']." LIMIT 1;";
				$this->dbo->setQuery($q);
				$this->dbo->execute();
			}
		}

		$set_clauses = [];
		
		// update dates
		if ($dates_modified) {
			$bdates_updated = true;
			//calculate the new number of nights
			$secdiff = $checkout_ts - $checkin_ts;
			$daysdiff = $secdiff / 86400;
			if (is_int($daysdiff)) {
				$daysdiff = $daysdiff < 1 ? 1 : $daysdiff;
			} else {
				if ($daysdiff < 1) {
					$daysdiff = 1;
				} else {
					$sum = floor($daysdiff) * 86400;
					$newdiff = $secdiff - $sum;
					$maxhmore = VikBooking::getHoursMoreRb() * 3600;
					$daysdiff = $maxhmore >= $newdiff ? floor($daysdiff) : ceil($daysdiff);
				}
			}
			//
			$set_clauses[] = "`days`=".(int)$daysdiff;
			$set_clauses[] = "`checkin`=".(int)$checkin_ts;
			$set_clauses[] = "`checkout`=".(int)$checkout_ts;

			//update record in _busy
			if ($booking['status'] == 'confirmed') {
				$must_call_e4jc = true;
				$realback = VikBooking::getHoursRoomAvail() * 3600;
				$realback += $checkout_ts;
				$q = "SELECT `b`.`id` FROM `#__vikbooking_busy` AS `b`, `#__vikbooking_ordersbusy` AS `ob` WHERE `b`.`id`=`ob`.`idbusy` AND `ob`.`idorder`=".(int)$booking['id'].";";
				$this->dbo->setQuery($q);
				$allbusy = $this->dbo->loadAssocList();
				foreach ($allbusy as $bb) {
					$q = "UPDATE `#__vikbooking_busy` SET `checkin`=".(int)$checkin_ts.", `checkout`=".(int)$checkout_ts.", `realback`=".(int)$realback." WHERE `id`=".(int)$bb['id'].";";
					$this->dbo->setQuery($q);
					$this->dbo->execute();
				}
			}
		}

		//email, notes, phone
		if (!empty($email)) {
			$set_clauses[] = "`custmail`=".$this->dbo->quote($email);
		}
		$notes_parts = array();
		if (!empty($extra_notes)) {
			array_push($notes_parts, rtrim($extra_notes, "\n"));
		}
		if (!empty($notes)) {
			array_push($notes_parts, $notes);
		} elseif (!empty($booking['adminnotes'])) {
			array_push($notes_parts, $booking['adminnotes']);
		}
		if (count($notes_parts)) {
			$set_clauses[] = "`adminnotes`=".$this->dbo->quote(implode("\n", $notes_parts));
		}
		if (!empty($phone)) {
			$set_clauses[] = "`phone`=".$this->dbo->quote($phone);
		}

		/**
		 * Room extra services can be submitted through the App for each room-party.
		 * 
		 * @since 	1.8.1 - App v1.5
		 */
		$room_extras = array();
		foreach ($rooms_data as $num => $r) {
			if (isset($r['extras']) && is_array($r['extras']) && count($r['extras'])) {
				foreach ($r['extras'] as $room_extra) {
					if (empty($room_extra['name'])) {
						continue;
					}
					// push extra service
					if (!isset($room_extras[$num])) {
						$room_extras[$num] = array();
					}
					array_push($room_extras[$num], $room_extra);
				}
			}
		}
		foreach ($room_extras as $num => $extras) {
			foreach ($extras as $ek => $extra) {
				// adjust key/property name for tax_rate to idtax for compatibility (key could be null)
				if (array_key_exists('tax_rate', $extra)) {
					$room_extras[$num][$ek]['idtax'] = $extra['tax_rate'];
					unset($room_extras[$num][$ek]['tax_rate']);
				}
				$ecplustax = !empty($room_extras[$num][$ek]['idtax']) ? VikBooking::sayOptionalsPlusIva((float)$extra['cost'], $room_extras[$num][$ek]['idtax']) : (float)$extra['cost'];
				$ecminustax = !empty($room_extras[$num][$ek]['idtax']) ? VikBooking::sayOptionalsMinusIva((float)$extra['cost'], $room_extras[$num][$ek]['idtax']) : (float)$extra['cost'];
				// increase booking total
				$booking['total'] += $ecplustax;
				// increase total taxes
				$booking['tot_taxes'] += ($ecplustax - $ecminustax);
			}
		}
		if (count($room_extras)) {
			// store the new extras per room
			foreach ($ordersrooms as $ind => $or) {
				if (!empty($or['extracosts'])) {
					$prev_rextras = json_decode($or['extracosts'], true);
					if (is_array($prev_rextras) && count($prev_rextras)) {
						foreach ($prev_rextras as $prev_rextra) {
							$ecplustax = !empty($prev_rextra['idtax']) ? VikBooking::sayOptionalsPlusIva((float)$prev_rextra['cost'], $prev_rextra['idtax']) : (float)$prev_rextra['cost'];
							$ecminustax = !empty($prev_rextra['idtax']) ? VikBooking::sayOptionalsMinusIva((float)$prev_rextra['cost'], $prev_rextra['idtax']) : (float)$prev_rextra['cost'];
							// lower booking total from previous values
							$booking['total'] -= $ecplustax;
							// lower total taxes from previous values
							$booking['tot_taxes'] -= ($ecplustax - $ecminustax);
						}
					}
				}
				// update order room record
				$oroom_record = new stdClass;
				$oroom_record->id = $or['id'];
				$oroom_record->extracosts = isset($room_extras[$ind]) ? json_encode($room_extras[$ind]) : null;

				$this->dbo->updateObject('#__vikbooking_ordersrooms', $oroom_record, 'id');
			}
			// update also the booking total and total taxes
			$set_clauses[] = "`total`=".$this->dbo->quote($booking['total']);
			$set_clauses[] = "`tot_taxes`=".$this->dbo->quote($booking['tot_taxes']);
		}

		// update booking record
		if (count($set_clauses)) {
			$q = "UPDATE `#__vikbooking_orders` SET ".implode(', ', $set_clauses)." WHERE `id`=".(int)$booking['id'].";";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
		}

		// booking history
		if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
			VikBooking::getBookingHistoryInstance()->setBid($booking['id'])->setPrevBooking($booking)->store('AM');
		}

		if (method_exists('VikBooking', 'updateSharedCalendars')) {
			// unset any previously booked room due to calendar sharing
			VikBooking::cleanSharedCalendarsBusy($booking['id']);
			// check if some of the rooms booked have shared calendars
			VikBooking::updateSharedCalendars($booking['id']);
		}

		//send request to e4jConnect
		if ($must_call_e4jc) {
			if (!class_exists('SynchVikBooking')) {
				require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "synch.vikbooking.php");
			}
			$vcm = new SynchVikBooking($booking['id']);
			$vcm->setSkipCheckAutoSync();
			$vcm->setFromModification($booking);
			$action_res = $vcm->sendRequest();
			if ($action_res === false && $vcm->isAvailabilityRequest()) {
				/**
				 * we set an error by returning false only in case of failure
				 * and if there is at least one active API channel to update.
				 */
				$this->setError(JText::_('VCMAPPERRE4JCSYNC'));
				return false;
			}
		}

		$response = new stdClass;
		$response->dates_updated = (int)$bdates_updated;
		$response->rooms_updated = (int)$brooms_updated;

		//Set the response body for the App
		$this->response->body = $response;
	}

	/**
	 * App Get Room Rates Request.
	 * Returns the room rates for each rate plan in a given range of dates.
	 *
	 * @return void
	 */
	private function appGetRoomRates()
	{
		$room_id	  = $this->input->getInt('room_id', 0);
		$next_months  = $this->input->getInt('months', 0);
		$date_from 	  = $this->input->getString('fdate', date('Y-m-d'));
		$date_to 	  = $this->input->getString('tdate', '');
		$exact_day 	  = $this->input->getString('day', '');
		$bookable 	  = $this->input->getUInt('bookable', 0);
		$memory_usage = $this->input->getBool('debug_memory_usage', false);

		/**
		 * This API endpoint allows to debug the memory usage.
		 * 
		 * @since 	1.9.0
		 */
		$memory_usage_data = [
			'limit'     => ini_get('memory_limit'),
			'current'   => memory_get_usage(),
			'current_r' => $this->formatMemoryBytes(memory_get_usage()),
			'start'     => microtime(true),
			'peaks'     => [],
		];

		if (!empty($exact_day)) {
			// alternative signature to query an exact day
			$date_from = $exact_day;
			$date_to   = $exact_day;
		}

		// if the date is not formatted correctly, an error is sent and false is returned
		if (count(explode('-', $date_from)) != 3) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}

		$info_from = getdate(strtotime($date_from));
		if (empty($date_to)) {
			if ($next_months > 0) {
				// load rates for n months
				$date_to = date('Y-m-d', mktime(0, 0, 0, ($info_from['mon'] + $next_months), 0, $info_from['year']));
			} else {
				// by default, load rates until the last day of the month of the date_from
				$date_to = date('Y-m-d', mktime(0, 0, 0, ($info_from['mon'] + 1), 0, $info_from['year']));
			}
		}

		$info_to = getdate(strtotime($date_to));
		// dates_to must be after date_from
		if ($info_from[0] > $info_to[0]) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}

		// read the room rates for the lowest number of nights
		$q = "SELECT `r`.`id`,`r`.`idroom`,`r`.`days`,`r`.`idprice`,`r`.`cost`,`p`.`name`,`p`.`minlos`,`p`.`derived_id` 
			FROM `#__vikbooking_dispcost` AS `r` 
			INNER JOIN (SELECT MIN(`days`) AS `min_days` FROM `#__vikbooking_dispcost` WHERE `idroom`=".(int)$room_id." GROUP BY `idroom`) AS `r2` ON `r`.`days`=`r2`.`min_days` 
			LEFT JOIN `#__vikbooking_prices` `p` ON `p`.`id`=`r`.`idprice` 
			WHERE `r`.`idroom`=".(int)$room_id." 
			GROUP BY `r`.`id`,`r`.`idroom`,`r`.`days`,`r`.`idprice`,`r`.`cost`,`p`.`name` ORDER BY `r`.`days` ASC, `r`.`cost` ASC;";
		$this->dbo->setQuery($q);
		$roomrates = $this->dbo->loadAssocList();
		if ($roomrates) {
			foreach ($roomrates as $rrk => $rrv) {
				$roomrates[$rrk]['cost'] = round(($rrv['cost'] / $rrv['days']), 2);
				$roomrates[$rrk]['days'] = 1;
			}
		}

		if ($memory_usage) {
			// push measurement data for the completed process
			$now_memory = memory_get_usage() - ($memory_usage_data['peaks'] ? end($memory_usage_data['peaks'])['current'] : $memory_usage_data['current']);
			$memory_usage_data['peaks'][] = [
				'process'   => 'sql_read_room_rates',
				'current'   => memory_get_usage(),
				'current_r' => $this->formatMemoryBytes(memory_get_usage()),
				'start'     => microtime(true),
				'memory'    => $now_memory,
				'memory_r'  => $this->formatMemoryBytes($now_memory),
				'duration'  => microtime(true) - ($memory_usage_data['peaks'] ? end($memory_usage_data['peaks'])['start'] : $memory_usage_data['start']),
			];
		}

		if (!$roomrates) {
			$this->setError(JText::_('VCMAPPNORATESFOUND'));
			return false;
		}

		/**
		 * Sort default room rate plans.
		 * 
		 * @since 	1.9.8
		 */
		$roomrates = VikBooking::sortRatePlans($roomrates);

		// load times for check-in and check-out
		$pcheckinh = 0;
		$pcheckinm = 0;
		$pcheckouth = 0;
		$pcheckoutm = 0;
		$timeopst = VikBooking::getTimeOpenStore();
		if (is_array($timeopst)) {
			$opent = VikBooking::getHoursMinutes($timeopst[0]);
			$closet = VikBooking::getHoursMinutes($timeopst[1]);
			$pcheckinh = $opent[0];
			$pcheckinm = $opent[1];
			$pcheckouth = $closet[0];
			$pcheckoutm = $closet[1];
		}

		// daily rates pool
		$daily_rates = [];

		/**
		 * Different request signatures may include the room minimum and maximum costs for one night.
		 * Payload example for a different request signature: {room_id: 1, day: "2024-01-06", bookable: 1}
		 * 
		 * @since 	1.8.21
		 */
		$room_min_cost = null;
		$room_max_cost = null;

		// load restrictions for this room
		$all_restrictions 	= VikBooking::loadRestrictions(true, [$room_id]);
		$glob_minlos 		= VikBooking::getDefaultNightsCalendar();
		$glob_minlos 		= $glob_minlos < 1 ? 1 : $glob_minlos;

		if ($memory_usage) {
			// push measurement data for the completed process
			$now_memory = memory_get_usage() - ($memory_usage_data['peaks'] ? end($memory_usage_data['peaks'])['current'] : $memory_usage_data['current']);
			$memory_usage_data['peaks'][] = [
				'process'   => 'sql_load_restrictions',
				'current'   => memory_get_usage(),
				'current_r' => $this->formatMemoryBytes(memory_get_usage()),
				'start'     => microtime(true),
				'memory'    => $now_memory,
				'memory_r'  => $this->formatMemoryBytes($now_memory),
				'duration'  => microtime(true) - ($memory_usage_data['peaks'] ? end($memory_usage_data['peaks'])['start'] : $memory_usage_data['start']),
			];
		}

		/**
		 * Determine the way rates are fetched, either through the regular process by reading
		 * restriction and seasonal records, or by using the rates flow records in VCM, which
		 * would be useful for speeding up the requests from dynamic pricing systems.
		 * 
		 * @since 	1.9.4
		 */
		if ($this->input->getInt('use_rates_flow', 0)) {
			// rates flow records method (will only support one rate plan)

			// flow channel ID (website)
			$flow_channel_id = -1;

			// fetch all records between the requested dates for the given room
			$this->dbo->setQuery(
				$this->dbo->getQuery(true)
					->select([
						$this->dbo->qn('day_from'),
						$this->dbo->qn('day_to'),
						$this->dbo->qn('vbo_price_id'),
						$this->dbo->qn('nightly_fee'),
					])
					->from($this->dbo->qn('#__vikchannelmanager_rates_flow'))
					->where($this->dbo->qn('channel_id') . ' = ' . $flow_channel_id)
					->where($this->dbo->qn('vbo_room_id') . ' = ' . (int) $room_id)
					->where($this->dbo->qn('vbo_price_id') . ' = ' . (int) $roomrates[0]['idprice'])
					->andWhere([
						'(' . $this->dbo->qn('day_from') . ' >= ' . $this->dbo->q(date('Y-m-d', $info_from[0])) . ')',
						'(' . $this->dbo->qn('day_to') . ' <= ' . $this->dbo->q(date('Y-m-d', $info_to[0])) . ')',
					])
					->order($this->dbo->qn('id') . ' DESC')
			);

			$rates_flow_records = $this->dbo->loadAssocList();

			if ($memory_usage) {
				// push measurement data for the completed process
				$now_memory = memory_get_usage() - ($memory_usage_data['peaks'] ? end($memory_usage_data['peaks'])['current'] : $memory_usage_data['current']);
				$memory_usage_data['peaks'][] = [
					'process'   => 'sql_load_rates_flow',
					'records'   => count($rates_flow_records),
					'current'   => memory_get_usage(),
					'current_r' => $this->formatMemoryBytes(memory_get_usage()),
					'start'     => microtime(true),
					'memory'    => $now_memory,
					'memory_r'  => $this->formatMemoryBytes($now_memory),
					'duration'  => microtime(true) - ($memory_usage_data['peaks'] ? end($memory_usage_data['peaks'])['start'] : $memory_usage_data['start']),
				];
			}

			// UTC timezone
			$utc_tz = new DateTimezone('UTC');

			// date object bounds
			$start_bound = new DateTime(date('Y-m-d', $info_from[0]), $utc_tz);
			$end_bound = new DateTime(date('Y-m-d', $info_to[0]), $utc_tz);

			// scan rates flow records
			foreach ($rates_flow_records as $flow) {
				// pre-flight check for existing data
				if ($flow['day_from'] == $flow['day_to'] && ($daily_rates[$flow['day_from']] ?? [])) {
					continue;
				}

				// get flow date bounds
				$from_bound = new DateTime($flow['day_from'], $utc_tz);
				$to_bound = new DateTime($flow['day_to'], $utc_tz);

				// ensure the dates bounds fit the interval
				if ($from_bound < $start_bound) {
					// do not start in the past
					$from_bound = clone $start_bound;
				}
				if ($to_bound > $end_bound) {
					// do not go too far in the future
					$to_bound = clone $end_bound;
				}

				// build iterable dates interval (period)
				$date_range = new DatePeriod(
					// start date included by default in the result set
					$from_bound,
					// interval between recurrences within the period
					new DateInterval('P1D'),
					// end date excluded by default from the result set
					$to_bound->modify('+1 day')
				);

				// loop through the updated dates interval
				foreach ($date_range as $dt) {
					$day_key = $dt->format('Y-m-d');

					if ($daily_rates[$day_key] ?? []) {
						// got a rate for this day already
						continue;
					}

					// start the container for this day
					$daily_rates[$day_key] = [];

					// check for restrictions
					$tomorrow_midn = clone $dt;
					$restr 	= VikBooking::parseSeasonRestrictions($dt->format('U'), $tomorrow_midn->modify('+1 day')->modify('00:00:00')->format('U'), 1, $all_restrictions);
					$minlos = $restr ? $restr['minlos'] : $glob_minlos;

					// loop through all room rate plans
					foreach ($roomrates as $tar) {
						if ($tar['idprice'] != $flow['vbo_price_id']) {
							// rate plan different from the one in the flow record
							continue;
						}

						$day_minlos = $minlos;
						if (!$restr && !empty($tar['minlos'])) {
							// use rate-plan-level minimum stay
							$day_minlos = $tar['minlos'];
						}

						$room_day_rate = new stdClass;
						$room_day_rate->rate_id   	= $tar['idprice'];
						$room_day_rate->rate_name 	= $tar['name'];
						$room_day_rate->cost 	  	= round($flow['nightly_fee'], 2);
						$room_day_rate->minlos      = (int) $day_minlos;;

						if (!empty($exact_day)) {
							// set additional properties to the response body when just one nightly rate
							$room_day_rate->is_cta = $restr ? in_array('-' . $dt->format('w')  . '-', (array) $restr['cta']) : false;
							$room_day_rate->is_ctd = $restr ? in_array('-' . $dt->format('w')  . '-', (array) $restr['ctd']) : false;

							if (!$room_day_rate->is_cta && !$room_day_rate->is_ctd && $day_minlos < 2) {
								// update min/max costs for an eligible rate plan
								$room_min_cost = empty($room_min_cost) ? $flow['nightly_fee'] : min($room_min_cost, $flow['nightly_fee']);
								$room_max_cost = empty($room_max_cost) ? $flow['nightly_fee'] : min($room_max_cost, $flow['nightly_fee']);
							}
						}

						// set restriction associative or empty array
						$room_day_rate->restriction = $restr;

						$daily_rates[$day_key][] = $room_day_rate;
					}

					if (!$daily_rates[$day_key]) {
						// do not start a container if no matching rate plans
						unset($daily_rates[$day_key]);
					}
				}
			}

			// sort dates
			ksort($daily_rates);
		} else {
			// classic and default fetching method

			/**
			 * Always preload season records. Beware of the hundreds of MBs of server's memory
			 * that could be used for pre-loading and pre-caching records in favour of CPU.
			 * 
			 * @since 	1.9.8
			 * @since 	1.9.15 week-day season records are also preloaded.
			 */
			$cached_seasons = VikBooking::getDateSeasonRecords($info_from[0], ($info_to[0] + (10 * 3600)), [$room_id]);
			$cached_wdayseasons = [];
			if (method_exists('VikBooking', 'getWdaySeasonRecords')) {
				$cached_wdayseasons = VikBooking::getWdaySeasonRecords();
			}

			// loop through dates range and load daily rates
			while ($info_from[0] <= $info_to[0]) {
				$checkin_ts 	= $info_from[0] + (3600 * $pcheckinh) + (60 * $pcheckinm);
				$checkout_midn  = mktime(0, 0, 0, $info_from['mon'], ($info_from['mday'] + 1), $info_from['year']);
				$checkout_ts 	= $checkout_midn + (3600 * $pcheckouth) + (60 * $pcheckoutm);

				$tars 	= VikBooking::applySeasonsRoom($roomrates, $checkin_ts, $checkout_ts, [], $cached_seasons, $cached_wdayseasons);
				$restr 	= VikBooking::parseSeasonRestrictions($info_from[0], $checkout_midn, 1, $all_restrictions);
				$minlos = $restr ? $restr['minlos'] : $glob_minlos;

				/**
				 * Set the daily rate information for the current rate plan(s).
				 */
				$day_key = date('Y-m-d', $info_from[0]);
				foreach ($tars as $ind => $tar) {
					if (!isset($daily_rates[$day_key])) {
						$daily_rates[$day_key] = [];
					}

					$day_minlos = $minlos;
					if (!$restr && !empty($tar['minlos'])) {
						// use rate-plan-level minimum stay
						$day_minlos = $tar['minlos'];
					}

					$room_day_rate = new stdClass;
					$room_day_rate->rate_id   	= $tar['idprice'];
					$room_day_rate->rate_name 	= $tar['name'];
					$room_day_rate->cost 	  	= round($tar['cost'], 2);
					$room_day_rate->minlos      = (int) $day_minlos;

					if (!empty($exact_day)) {
						// set additional properties to the response body when just one nightly rate
						$room_day_rate->is_cta = $restr ? in_array('-' . $info_from['wday']  . '-', (array) $restr['cta']) : false;
						$room_day_rate->is_ctd = $restr ? in_array('-' . $info_from['wday']  . '-', (array) $restr['ctd']) : false;

						if (!$room_day_rate->is_cta && !$room_day_rate->is_ctd && $day_minlos < 2) {
							// update min/max costs for an eligible rate plan
							$room_min_cost = empty($room_min_cost) ? $tar['cost'] : min($room_min_cost, $tar['cost']);
							$room_max_cost = empty($room_max_cost) ? $tar['cost'] : min($room_max_cost, $tar['cost']);
						}
					}

					// set restriction associative or empty array
					$room_day_rate->restriction = $restr;

					$daily_rates[$day_key][] = $room_day_rate;
				}

				$info_from = getdate(mktime(0, 0, 0, $info_from['mon'], ($info_from['mday'] + 1), $info_from['year']));
			}

			if ($memory_usage) {
				// push measurement data for the completed process
				$now_memory = memory_get_usage() - ($memory_usage_data['peaks'] ? end($memory_usage_data['peaks'])['current'] : $memory_usage_data['current']);
				$memory_usage_data['peaks'][] = [
					'process'   => 'sql_apply_seasonal_rates',
					'current'   => memory_get_usage(),
					'current_r' => $this->formatMemoryBytes(memory_get_usage()),
					'start'     => microtime(true),
					'memory'    => $now_memory,
					'memory_r'  => $this->formatMemoryBytes($now_memory),
					'duration'  => microtime(true) - ($memory_usage_data['peaks'] ? end($memory_usage_data['peaks'])['start'] : $memory_usage_data['start']),
				];
			}
		}

		$response = new stdClass;
		$response->currency_name = VikBooking::getCurrencyName();
		$response->currency_symbol = VikBooking::getCurrencySymb();
		if (empty($response->currency_symbol)) {
			$response->currency_symbol = $response->currency_name;
		}
		$response->default_rates = [];
		$response->daily_rates = $daily_rates;

		// build the default rates for each rate plan
		foreach ($roomrates as $ind => $rate) {
			$room_rate = new stdClass;
			$room_rate->rate_id   = $rate['idprice'];
			$room_rate->rate_name = $rate['name'];
			$room_rate->cost 	  = $rate['cost'];
			$room_rate->minlos    = (int)$rate['minlos'];

			$response->default_rates[] = $room_rate;
		}

		if (!empty($exact_day)) {
			$response->room_min_cost = $room_min_cost;
			$response->room_max_cost = $room_max_cost;

			if ($bookable) {
				// query if the given number of units are bookable for this night
				$response->is_bookable = (bool)($room_min_cost && $room_max_cost && VikBooking::roomBookable($room_id, $bookable, $checkin_ts, $checkout_ts));
			}
		}

		if ($memory_usage) {
			// push measurement data for the completed process
			$now_memory = memory_get_usage() - ($memory_usage_data['peaks'] ? end($memory_usage_data['peaks'])['current'] : $memory_usage_data['current']);
			$memory_usage_data['peaks'][] = [
				'process'   => 'process_completed',
				'current'   => memory_get_usage(),
				'current_r' => $this->formatMemoryBytes(memory_get_usage()),
				'start'     => microtime(true),
				'memory'    => $now_memory,
				'memory_r'  => $this->formatMemoryBytes($now_memory),
				'duration'  => microtime(true) - ($memory_usage_data['peaks'] ? end($memory_usage_data['peaks'])['start'] : $memory_usage_data['start']),
			];

			// append the memory usage data property
			$response->memory_usage_data = $memory_usage_data;
		}

		// set the response body for the App
		$this->response->body = $response;
	}

	/**
	 * App Modify Room Rates Request.
	 * Modifies the rates for a specific room and 
	 * rate plans in a given range of dates.
	 *
	 * @return void
	 */
	private function appModifyRoomRates()
	{
		$room_id		= $this->input->getInt('room_id', 0);
		$rates_data 	= $this->input->getVar('rates_data', []);
		$date_from 		= $this->input->getString('fdate', '');
		$date_to 		= $this->input->getString('tdate', '');
		$minlos			= $this->input->getInt('minlos', 0);
		$maxlos			= $this->input->getInt('maxlos', 0);
		$upd_vbo		= $this->input->getInt('upd_vbo', 1);
		$upd_otas		= $this->input->getInt('upd_otas', 0);

		/**
		 * Added support for CTA and CTD boolean values as well as to a list
		 * of integer values to identify the week days (0 = Sun, 6 = Sat).
		 * 
		 * @since 	1.8.24
		 */
		$cta = $this->input->getBool('cta', null);
		$ctd = $this->input->getBool('ctd', null);
		$cta_wdays = (array) $this->input->getUInt('cta_wdays', []);
		$ctd_wdays = (array) $this->input->getUInt('ctd_wdays', []);

		// prevent any errors if CURL is not available
		if (!function_exists('curl_init')) {
			$this->setError('CURL not available');
			return false;
		}

		$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey` = " . (int)VikChannelManagerConfig::MOBILEAPP;
		$this->dbo->setQuery($q);
		if (!$this->dbo->loadAssoc()) {
			$this->setError(JText::_('VCMAPPCHREQREFUSED'));
			return false;
		}

		/**
		 * We dispatch the update request through the OTA RAR Update helper class.
		 * This is to allow third-party plugins to use the same technique.
		 * 
		 * @since 	1.8.24
		 */
		try {
			// obtain the execution results
			list($channels_updated, $channels_success, $channels_warnings, $channels_errors) = VCMOtaRarUpdate::getInstance([
				'room_id' 	 => $room_id,
				'rates_data' => $rates_data,
				'date_from'  => $date_from,
				'date_to' 	 => $date_to,
				'minlos' 	 => $minlos,
				'maxlos' 	 => $maxlos,
				'upd_vbo' 	 => $upd_vbo,
				'upd_otas' 	 => $upd_otas,
				'cta' 		 => $cta,
				'ctd' 		 => $ctd,
				'cta_wdays'  => $cta_wdays,
				'ctd_wdays'  => $ctd_wdays,
			])
			// set the caller to App to reduce the sleep time between the requests
			->setCaller('App')
			// set the API user with the currently authenticated account email
			->setApiUser($this->accountEmail)
			// execute the request
			->execute();
		} catch (Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}

		// build the response body object with the execution results
		$response = new stdClass;

		$response->vbo_updated 		 = $upd_vbo;
		$response->channels_updated  = $channels_updated;
		$response->channels_success  = array_values($channels_success);
		$response->channels_warnings = array_values($channels_warnings);
		$response->channels_errors 	 = array_values($channels_errors);

		// set the response body for the App
		$this->response->body = $response;
	}

	/**
	 * App Get Happening Request
	 *
	 * This method returns the number of arrivals, departures, stayovers 
	 * and bookings for a specified date. Returns the information 
	 * for the current day if no dates are specified.
	 * 
	 * This method is strictly related to the e4jConnect App and its home/welcome
	 * page, it not a task that would be used so often for the API framework. For
	 * this reason, we use it to trigger some maintenance controls of the settings.
	 *
	 * @return 	void
	 * 
	 * @since 	1.8.0 	this method triggers the checking of the auto bulk actions.
	 */
	private function appGetHappening()
	{
		// Trigger reminders
		VikChannelManager::checkSubscriptionReminder();

		// Trigger auto bulk actions
		VikChannelManager::autoBulkActions();

		$response = new stdClass();

		/**
		 * This method can also return the information about the expiration of the subscription.
		 * 
		 * @since 	1.8.1
		 */
		$get_expiration_info = $this->input->getInt('expiration_info', 0);

		// get the date
		$requestDate = $this->input->getString('date', '');
		if (empty($requestDate)) {
			$requestDate = date('Y-m-d');
		}

		// getting the midnight (both the 00:00 and the 23:59) timestamp for the selected date
		$date_start_ts 	= strtotime($requestDate);
		$date_info 		= getdate($date_start_ts);
		$date_end_ts 	= mktime(23, 59, 59, $date_info['mon'], $date_info['mday'], $date_info['year']);
		$arrivals 		= 0;
		$departures		= 0;
		$stayovers		= 0;
		$bookings 		= 0;

		// get the numbers of arrivals
		$q = "SELECT SUM(`roomsnum`) FROM `#__vikbooking_orders` WHERE `closure` = 0 AND `status`='confirmed' AND `checkin` >= $date_start_ts AND `checkin` <= $date_end_ts;";
		$this->dbo->setQuery($q);
		$arrivals = (int)$this->dbo->loadResult();

		// get the numbers of departures
		$q = "SELECT SUM(`roomsnum`) FROM `#__vikbooking_orders` WHERE `closure` = 0 AND `status`='confirmed' AND `checkout` >= $date_start_ts AND `checkout` <= $date_end_ts;";
		$this->dbo->setQuery($q);
		$departures = (int)$this->dbo->loadResult();

		// get the numbers of stayovers
		$q = "SELECT SUM(`roomsnum`) FROM `#__vikbooking_orders` WHERE `closure` = 0 AND `status`='confirmed' AND `checkin` < $date_start_ts AND `checkout` > $date_end_ts;";
		$this->dbo->setQuery($q);
		$stayovers = (int)$this->dbo->loadResult();

		// get the numbers of bookings
		$q = "SELECT COUNT(*) FROM `#__vikbooking_orders` WHERE `status`='confirmed' AND `ts` >= $date_start_ts AND `ts` <= $date_end_ts;";
		$this->dbo->setQuery($q);
		$bookings = (int)$this->dbo->loadResult();

		// expiration details
		$expiration_details = null;
		if ($get_expiration_info) {
			// make the request to e4jConnect for the subscription (not for all channels)
			$e4jc_url = "https://e4jconnect.com/channelmanager/?r=exp&c=generic";
			$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager EXP Request e4jConnect.com -->
<ExpiringRQ xmlns="http://www.e4jconnect.com/schemas/exprq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . VikChannelManager::getApiKey(true) . '"/>
	<Fetch question="subscription" channel="all"/>
</ExpiringRQ>';
			$e4jC = new E4jConnectRequest($e4jc_url);
			$e4jC->setPostFields($xml);
			$rs = $e4jC->exec();

			if (!$e4jC->getErrorNo() && substr($rs, 0, 9) != 'e4j.error' && substr($rs, 0, 11) != 'e4j.warning') {
				// parse the response
				$expiration_details = json_decode($rs);
				if (!is_object($expiration_details)) {
					$expiration_details = null;
				} else {
					// calculate the days till the expiration date
					$now_date = new DateTime(date('Y-m-d'));
					$exp_date = new DateTime($expiration_details->ymd);
					$expiration_details->expires_in_days = (int)$now_date->diff($exp_date)->format("%r%a");
				}
			}
		}

		// set the response
		$response->arrivals 	= $arrivals;
		$response->departures 	= $departures;
		$response->stayovers 	= $stayovers;
		$response->bookings 	= $bookings;
		if ($expiration_details !== null) {
			// set expiration details if requested and available
			$response->expiration = $expiration_details;
		}

		// set the response object as the body for the App
		$this->response->body = $response;
	}

	/**
	 * App Close Room Request
	 *
	 * This method closes all the remaining units of a specific 
	 * room in certain dates, by creating a 'fake' booking.
	 * Sets the response to return the ID of the generated booking.
	 *
	 * @return void
	 */
	private function appCloseRoom()
	{
		$response = new stdClass();

		$room_id		= $this->input->getInt('room_id', 0);
		$date_from 		= $this->input->getString('fdate', '');
		$date_to 		= $this->input->getString('tdate', '');

		$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".(int)VikChannelManagerConfig::MOBILEAPP." LIMIT 1;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() < 1) {
			$this->setError(JText::_('VCMAPPCHREQREFUSED'));
			return false;
		}

		//If the date is not formatted correctly, an error is sent and false is returned
		if (count(explode('-', $date_from)) != 3 || count(explode('-', $date_to)) != 3) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}
		$info_from = getdate(strtotime($date_from));
		if (empty($date_to)) {
			//if empty date_to, set it to the same day as date_from
			$date_to = $date_from;
		}
		$info_to = getdate(strtotime($date_to));
		//dates_to must be after date_from and cannot be in the past
		$today_midnight = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
		if ($info_from[0] >= $info_to[0] || $info_to[0] < $today_midnight) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}

		//load room details
		$q = "SELECT `id`,`name`,`units` FROM `#__vikbooking_rooms` WHERE `id`=".(int)$room_id.";";
		$this->dbo->setQuery($q);
		$room = $this->dbo->loadAssoc();
		if (!$room) {
			$this->setError(JText::_('VCMAPPNOROOMFOUND'));
			return false;
		}

		//require the VikBooking library
		$this->importVboLib();

		//Calculate the new timestamps for check-in and check-out
		$pcheckinh = 0;
		$pcheckinm = 0;
		$pcheckouth = 0;
		$pcheckoutm = 0;
		$timeopst = VikBooking::getTimeOpenStore();
		if (is_array($timeopst)) {
			$opent = VikBooking::getHoursMinutes($timeopst[0]);
			$closet = VikBooking::getHoursMinutes($timeopst[1]);
			$pcheckinh = $opent[0];
			$pcheckinm = $opent[1];
			$pcheckouth = $closet[0];
			$pcheckoutm = $closet[1];
		}
		$checkin_ts = $info_from[0] + (3600 * $pcheckinh) + (60 * $pcheckinm);
		$checkout_ts = $info_to[0] + (3600 * $pcheckouth) + (60 * $pcheckoutm);

		//calculate the number of nights
		$secdiff = $checkout_ts - $checkin_ts;
		$daysdiff = $secdiff / 86400;
		if (is_int($daysdiff)) {
			$daysdiff = $daysdiff < 1 ? 1 : $daysdiff;
		} else {
			if ($daysdiff < 1) {
				$daysdiff = 1;
			} else {
				$sum = floor($daysdiff) * 86400;
				$newdiff = $secdiff - $sum;
				$maxhmore = VikBooking::getHoursMoreRb() * 3600;
				$daysdiff = $maxhmore >= $newdiff ? floor($daysdiff) : ceil($daysdiff);
			}
		}

		$realback = VikBooking::getHoursRoomAvail() * 3600;
		$realback += $checkout_ts;
		$insertedbusy = array();
		for ($b = 1; $b <= $room['units']; $b++) {
			$q = "INSERT INTO `#__vikbooking_busy` (`idroom`,`checkin`,`checkout`,`realback`) VALUES(".(int)$room['id'].", ".(int)$checkin_ts.", ".(int)$checkout_ts.", ".(int)$realback.");";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
			$lid = $this->dbo->insertid();
			$insertedbusy[] = $lid;
		}
		$sid = VikBooking::getSecretLink();
		$q = "INSERT INTO `#__vikbooking_orders` 
			(`custdata`,`ts`,`status`,`days`,`checkin`,`checkout`,`sid`,`roomsnum`,`total`,`adminnotes`,`closure`) 
			VALUES(".$this->dbo->quote(JText::_('VCMAPPROOMCLOSED')).",".time().",'confirmed',".(int)$daysdiff.",".(int)$checkin_ts.",".(int)$checkout_ts.",'".$sid."','1',NULL,".$this->dbo->quote(JText::sprintf('VCMAPPROOMCLOSEDNOTES', $this->accountEmail)).", 1);";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		$newoid = $this->dbo->insertid();
		$confirmnumber = VikBooking::generateConfirmNumber($newoid, true);
		foreach ($insertedbusy as $lid) {
			$q = "INSERT INTO `#__vikbooking_ordersbusy` (`idorder`,`idbusy`) VALUES('".$newoid."','".$lid."');";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
		}
		$q = "INSERT INTO `#__vikbooking_ordersrooms` (`idorder`,`idroom`,`adults`,`children`) 
			VALUES(".(int)$newoid.", ".(int)$room['id'].", 1, 0);";
		$this->dbo->setQuery($q);
		$this->dbo->execute();

		//VBO 1.10 or higher - Booking History
		if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
			VikBooking::getBookingHistoryInstance()->setBid($newoid)->store('AN');
		}

		if (method_exists('VikBooking', 'updateSharedCalendars')) {
			// check if some of the rooms booked have shared calendars
			VikBooking::updateSharedCalendars($newoid, array($room['id']), $checkin_ts, $checkout_ts);
		}

		//send request to e4jConnect
		if (!class_exists('SynchVikBooking')) {
			require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "synch.vikbooking.php");
		}
		$vcm = new SynchVikBooking($newoid);
		$vcm->setSkipCheckAutoSync();
		$action_res = $vcm->sendRequest();
		if ($action_res === false && $vcm->isAvailabilityRequest()) {
			/**
			 * we set an error by returning false only in case of failure
			 * and if there is at least one active API channel to update.
			 */
			$this->setError(JText::_('VCMAPPERRE4JCSYNC'));
			return false;
		}

		$response->booking_id = (int)$newoid;		

		//Set the response object as the body for the App
		$this->response->body = $response;
	}

	/**
	 * App Create Booking Request
	 * Stores a new booking onto the database and 
	 * invokes the channel manager to update the availability.
	 *
	 * @return void
	 */
	private function appCreateBooking()
	{
		$bdates_updated = $brooms_updated = $must_call_e4jc = false;
		$checkin 		= $this->input->getString('checkin', '');
		$checkout 		= $this->input->getString('checkout', '');
		$status 		= strtolower($this->input->getString('status', 'Confirmed'));
		$status 		= in_array($status, array('confirmed', 'standby', 'closed')) ? $status : 'confirmed';
		$rooms_data 	= $this->input->getVar('rooms_data', array());
		$fname 			= $this->input->getString('cust_name', '');
		$lname 			= $this->input->getString('cust_lname', '');
		$email 			= $this->input->getString('cust_email', '');
		$phone 			= $this->input->getString('cust_phone', '');
		$addr 			= $this->input->getString('cust_addr', '');
		$city 			= $this->input->getString('cust_city', '');
		$zip 			= $this->input->getString('cust_zip', '');
		$country		= $this->input->getString('cust_country', '');
		$customer_id	= $this->input->getInt('customer_id', 0);
		$customer_pin 	= $this->input->getString('customer_pin', '');
		$total			= $this->input->getFloat('total', 0);
		$today_ts 		= mktime(0, 0, 0, date('n'), date('j'), date('Y'));

		/**
		 * We support "closed" status.
		 * 
		 * @since 	1.8.6
		 */
		$close_rooms = false;
		if ($status === 'closed') {
			$close_rooms = true;
			$status = 'confirmed';
		}

		/**
		 * We support the calculation of taxes.
		 * 
		 * @since 	1.8.1
		 */
		$tot_taxes = 0;
		$tot_city_taxes = 0;
		$tot_fees = 0;

		$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".(int)VikChannelManagerConfig::MOBILEAPP." LIMIT 1;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() < 1) {
			$this->setError(JText::_('VCMAPPCHREQREFUSED'));
			return false;
		}

		// dates must be formatted correctly
		if (empty($checkin) || count(explode('-', $checkin)) != 3 || empty($checkout) || count(explode('-', $checkout)) != 3) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}
		// check if dates are in the past or equal
		$date_from = getdate(strtotime($checkin));
		$date_to = getdate(strtotime($checkout));
		if ($date_from[0] >= $date_to[0] || $date_to[0] < $today_ts) {
			$this->setError(JText::_('VCMAPPINVALIDBDATES'));
			return false;
		}

		// require the VikBooking library
		$this->importVboLib();

		// Calculate the new timestamps for check-in and check-out
		$pcheckinh = 0;
		$pcheckinm = 0;
		$pcheckouth = 0;
		$pcheckoutm = 0;
		$timeopst = VikBooking::getTimeOpenStore();
		if (is_array($timeopst)) {
			$opent = VikBooking::getHoursMinutes($timeopst[0]);
			$closet = VikBooking::getHoursMinutes($timeopst[1]);
			$pcheckinh = $opent[0];
			$pcheckinm = $opent[1];
			$pcheckouth = $closet[0];
			$pcheckoutm = $closet[1];
		}
		$checkin_ts = $date_from[0] + (3600 * $pcheckinh) + (60 * $pcheckinm);
		$checkout_ts = $date_to[0] + (3600 * $pcheckouth) + (60 * $pcheckoutm);

		// calculate the number of nights
		$secdiff = $checkout_ts - $checkin_ts;
		$daysdiff = $secdiff / 86400;
		if (is_int($daysdiff)) {
			$daysdiff = $daysdiff < 1 ? 1 : $daysdiff;
		} else {
			if ($daysdiff < 1) {
				$daysdiff = 1;
			} else {
				$sum = floor($daysdiff) * 86400;
				$newdiff = $secdiff - $sum;
				$maxhmore = VikBooking::getHoursMoreRb() * 3600;
				$daysdiff = $maxhmore >= $newdiff ? floor($daysdiff) : ceil($daysdiff);
			}
		}

		// check if a default tax rate is available
		$def_tax_id = $this->getDefaultTaxID();

		// check and compose the rooms info
		$tax_included = VikBooking::ivaInclusa();
		$tot_rooms = 0;
		$rooms_ids = array();
		$rooms_units_req = array();
		if (count($rooms_data)) {
			foreach ($rooms_data as $k => $v) {
				if (!isset($v['room_id']) || empty($v['room_id']) || !isset($v['adults'])) {
					continue;
				}
				$tot_rooms++;
				$room_id = intval($v['room_id']);
				if (!in_array($room_id, $rooms_ids)) {
					$rooms_ids[] = $room_id;
				}
				if (isset($rooms_units_req[$room_id])) {
					$rooms_units_req[$room_id]++;
				} else {
					$rooms_units_req[$room_id] = 1;
				}
				$rooms_data[$k]['room_id'] = $room_id;
				$rooms_data[$k]['adults'] = intval($v['adults']);
				if (isset($v['children'])) {
					$rooms_data[$k]['children'] = intval($v['children']);
				}
				if (isset($v['cost'])) {
					$rooms_data[$k]['cost'] = floatval($v['cost']);
				}
				
				/**
				 * We now allow to set website rate plans as tariffs for the bookings.
				 * The request structure must contain the same payload returned through
				 * the call "calc_rateplans", so $rooms_data will have all data needed.
				 * 
				 * @since 	1.7.4
				 */
				if (!empty($v['rplan_id']) && is_array($v['rate_plans'])) {
					foreach ($v['rate_plans'] as $rpk => $rpv) {
						if (!is_array($rpv) || empty($rpv['idprice'])) {
							continue;
						}
						if ((int)$rpv['idprice'] == (int)$v['rplan_id'] && !empty($rpv['cost']) && (float)$rpv['cost'] > 0) {
							// matching rate plan found
							$q = "SELECT `id` FROM `#__vikbooking_dispcost` WHERE `idroom`={$room_id} AND `days`={$daysdiff} AND `idprice`={$v['rplan_id']};";
							$this->dbo->setQuery($q);
							$this->dbo->execute();
							if ($this->dbo->getNumRows()) {
								// set tariff ID found
								$rooms_data[$k]['tariff_id'] = $this->dbo->loadResult();
								// set also the corresponding room cost for this tariff, which is already following taxes included/excluded from payload
								$rooms_data[$k]['room_cost'] = (float)$rpv['cost'];
								// set taxes (if any)
								if (!empty($rpv['taxes'])) {
									$rooms_data[$k]['room_cost_taxes'] = (float)$rpv['taxes'];
								}
							}
						}
					}
				}
				//
			}
		}
		if ($tot_rooms < 1) {
			$this->setError(JText::_('VCMAPPNOROOMFOUND'));
			return false;
		}
		$q = "SELECT `id`,`name`,`units` FROM `#__vikbooking_rooms` WHERE `id` IN (".implode(', ', $rooms_ids).");";
		$this->dbo->setQuery($q);
		$all_rooms = $this->dbo->loadAssocList();
		if (!$all_rooms) {
			$this->setError(JText::_('VCMAPPNOROOMFOUND'));
			return false;
		}
		if (count($all_rooms) != count($rooms_ids)) {
			$this->setError(JText::_('VCMAPPNOROOMFOUND').' ('.(count($all_rooms) - count($rooms_ids)).')');
			return false;
		}
		$rooms_units = array();
		foreach ($all_rooms as $rr) {
			$rooms_units[$rr['id']] = array(
				'name' => $rr['name'],
				'units' => $rr['units']
			);
		}

		// check remaining availability for the requested rooms and number of units
		foreach ($rooms_ids as $room_id) {
			$check_room_units = $rooms_units[$room_id]['units'] - $rooms_units_req[$room_id] + 1;
			if (!VikBooking::roomBookable($room_id, $check_room_units, $checkin_ts, $checkout_ts)) {
				// room is fully booked
				$this->setError(JText::sprintf('VCMAPPCONFBROOMNA', $rooms_units[$room_id]['name']));
				return false;
			}
		}

		// compose customer data
		$fixed_notes = JText::sprintf('VCMAPPNEWBOOKINGNOTES', $this->accountEmail);
		$custdata = '';
		$customer_extrainfo = array();
		$cpin = VikBooking::getCPinIstance();
		$cpin->is_admin = true;
		if (!empty($fname)) {
			$custdata .= JText::_('VCMAPPNEWBOOKFNAME').': '.$fname."\n";
		}
		if (!empty($lname)) {
			$custdata .= JText::_('VCMAPPNEWBOOKLNAME').': '.$lname."\n";
		}
		if (!empty($email)) {
			$custdata .= JText::_('VCMAPPNEWBOOKEMAIL').': '.$email."\n";
		}
		if (!empty($phone)) {
			$custdata .= JText::_('VCMAPPNEWBOOKPHONE').': '.$phone."\n";
		}
		if (!empty($addr)) {
			$custdata .= JText::_('VCMAPPNEWBOOKADDR').': '.$addr."\n";
			$customer_extrainfo['address'] = $addr;
		}
		if (!empty($city)) {
			$custdata .= JText::_('VCMAPPNEWBOOKCITY').': '.$city."\n";
			$customer_extrainfo['city'] = $city;
		}
		if (!empty($zip)) {
			$custdata .= JText::_('VCMAPPNEWBOOKZIP').': '.$zip."\n";
			$customer_extrainfo['zip'] = $zip;
		}
		if (!empty($custdata)) {
			$custdata = rtrim($custdata, "\n");
			// store customer record
			$cpin->setCustomerExtraInfo($customer_extrainfo);
			$cpin->saveCustomerDetails($fname, $lname, $email, $phone, $country, array());
		} else {
			// if no customer details have been passed, default to the notes
			$custdata = $fixed_notes;
		}

		/**
		 * We make sure the total is greater than zero, otherwise we try to calculate it.
		 * 
		 * @since 	1.7.4
		 */
		$room_index_costs = array();
		if (empty($total) || $total <= 0) {
			$total = 0;
			foreach ($rooms_data as $num => $r) {
				if (!empty($r['cost']) && (float)$r['cost'] > 0) {
					// custom room cost
					$total += (float)$r['cost'];
					// store cost for this room party
					$room_index_costs[$num] = (float)$r['cost'];
					// calculate taxes (if applicable)
					if ($def_tax_id) {
						$cost_after_tax = VikBooking::sayPackagePlusIva((float)$r['cost'], $def_tax_id);
						$cost_minus_tax = VikBooking::sayPackageMinusIva((float)$r['cost'], $def_tax_id);
						$cost_tot_taxes = ($cost_after_tax - $cost_minus_tax);
						// increase taxes
						$tot_taxes += ($cost_after_tax - $cost_minus_tax);
						if (!$tax_included && $cost_tot_taxes > 0) {
							// add taxes to the total as they are not included in the room cost
							$total += $cost_tot_taxes;
						}
					}
				} elseif (!empty($r['room_cost']) && (float)$r['room_cost'] > 0) {
					// website rate plan cost (room cost)
					$total += (float)$r['room_cost'];
					// store cost for this room party
					$room_index_costs[$num] = (float)$r['room_cost'];
					// check taxes
					if (!$tax_included && !empty($r['room_cost_taxes'])) {
						// add taxes to the total as they are not included in the room cost
						$total += (float)$r['room_cost_taxes'];
						// increase taxes as well
						$tot_taxes += (float)$r['room_cost_taxes'];
					} elseif (!empty($r['room_cost_taxes'])) {
						// increase taxes
						$tot_taxes += ((float)$r['room_cost'] - (float)$r['room_cost_taxes']);
					}
				}
			}
		} else {
			// set the room cost only to the first unit
			$kcount = 0;
			foreach ($rooms_data as $num => $r) {
				if ($kcount < 1) {
					$room_index_costs[$num] = $total;
				}
				// check for custom room cost
				if (!empty($r['cost']) && (float)$r['cost'] > 0) {
					// calculate taxes (if applicable)
					if ($def_tax_id) {
						$cost_after_tax = VikBooking::sayPackagePlusIva((float)$r['cost'], $def_tax_id);
						$cost_minus_tax = VikBooking::sayPackageMinusIva((float)$r['cost'], $def_tax_id);
						$cost_tot_taxes = ($cost_after_tax - $cost_minus_tax);
						// increase taxes
						$tot_taxes += ($cost_after_tax - $cost_minus_tax);
						if (!$tax_included && $cost_tot_taxes > 0) {
							// add taxes to the total as they are not included in the room cost
							$total += $cost_tot_taxes;
						}
					}
				}
				// increase counter
				$kcount++;
			}
		}

		/**
		 * Room options and extra services can be submitted through the App for each room-party.
		 * 
		 * @since 	1.8.1 - App v1.5
		 */
		$room_options = array();
		$room_extras = array();
		foreach ($rooms_data as $num => $r) {
			if (isset($r['options']) && is_array($r['options']) && count($r['options'])) {
				foreach ($r['options'] as $room_opt) {
					if (empty($room_opt['id']) || $room_opt['quantity'] < 1) {
						continue;
					}
					$record = VikBooking::getSingleOption($room_opt['id']);
					if (!is_array($record) || !count($record)) {
						continue;
					}
					// push option in room party
					if (!isset($room_options[$num])) {
						$room_options[$num] = array();
					}
					array_push($room_options[$num], array(
						'record' 	=> $record,
						'quantity'  => (int)$room_opt['quantity'],
						// selected age band will be increased by one, as the first index should be 1, not 0
						'age_band' 	=> isset($room_opt['age_band']) ? ($room_opt['age_band'] + 1) : null,
					));
				}
			}
			if (isset($r['extras']) && is_array($r['extras']) && count($r['extras'])) {
				foreach ($r['extras'] as $room_extra) {
					if (empty($room_extra['name'])) {
						continue;
					}
					// push extra service
					if (!isset($room_extras[$num])) {
						$room_extras[$num] = array();
					}
					array_push($room_extras[$num], $room_extra);
				}
			}
		}

		/**
		 * Calculate values for options and extras, increase total and taxes.
		 * 
		 * @since 	1.8.1 - App v1.5
		 */
		$room_options_str = array();
		$children_age = array();
		foreach ($room_options as $num => $roptions) {
			// prepare room options string for this room party
			$room_option_str = '';
			foreach ($roptions as $roption) {
				if (!empty($roption['record']['ageintervals']) && !empty($rooms_data[$num]['children']) && $rooms_data[$num]['children'] > 0 && !empty($roption['age_band'])) {
					// children age option with age band selected
					$optagenames = VikBooking::getOptionIntervalsAges($roption['record']['ageintervals']);
					$optagepcent = VikBooking::getOptionIntervalsPercentage($roption['record']['ageintervals']);
					$optagecosts = VikBooking::getOptionIntervalsCosts($roption['record']['ageintervals']);
					// the age_band index is already increased by 1, so starting from 1 rather than from 0 as transmitted by the App
					$chvar = $roption['age_band'];
					// check percent costs
					if (isset($room_index_costs[$num])) {
						if (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 1) {
							// percentage value of the adults tariff (not available)
							$optagecosts[($chvar - 1)] = $room_index_costs[$num] * $optagecosts[($chvar - 1)] / 100;
						} elseif (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 2) {
							// percentage value of room base cost
							$optagecosts[($chvar - 1)] = $room_index_costs[$num] * $optagecosts[($chvar - 1)] / 100;
						}
					}

					// calculate option cost
					if (!isset($optagecosts[($chvar - 1)])) {
						// should not happen if properly configured
						$optagecosts[($chvar - 1)] = $roption['record']['cost'];
					}
					$opt_cost = (intval($roption['record']['perday']) == 1 ? (floatval($optagecosts[($chvar - 1)]) * $daysdiff * $roption['quantity']) : (floatval($optagecosts[($chvar - 1)]) * $roption['quantity']));

					// push child age
					if (!isset($children_age[$num])) {
						$children_age[$num] = array();
					}
					$children_age[$num][] = array(
						'ageinterval' => $optagenames[($chvar - 1)],
						'age' => '',
						'cost' => $opt_cost
					);

					// build room option string
					$room_option_str .= $roption['record']['id'] . ":" . $roption['quantity'] . "-" . $chvar . ";";
				} else {
					// regular option
					$deftar_basecost = isset($rooms_data[$num]['cost']) && $rooms_data[$num]['cost'] > 0 ? $rooms_data[$num]['cost'] : 0;
					$deftar_basecost = isset($rooms_data[$num]['room_cost']) && !empty($rooms_data[$num]['room_cost']) ? $rooms_data[$num]['room_cost'] : $deftar_basecost;
					$opt_cost = (int)$roption['record']['pcentroom'] ? ($deftar_basecost * $roption['record']['cost'] / 100) : $roption['record']['cost'];
					$opt_cost = ((int)$roption['record']['perday'] == 1 ? ($roption['record']['cost'] * $daysdiff * $roption['quantity']) : ($roption['record']['cost'] * $roption['quantity']));

					// build room option string
					$room_option_str .= $roption['record']['id'] . ":" . $roption['quantity'] . ";";
				}

				// apply max price, per person, and finalize calculation
				if (!empty($roption['record']['maxprice']) && $roption['record']['maxprice'] > 0 && $opt_cost > $roption['record']['maxprice']) {
					$opt_cost = $roption['record']['maxprice'];
					if (intval($roption['record']['hmany']) == 1 && intval($roption['quantity']) > 1) {
						$opt_cost = $roption['record']['maxprice'] * $roption['quantity'];
					}
				}
				$opt_cost = ($roption['record']['perperson'] == 1 ? ($opt_cost * $rooms_data[$num]['adults']) : $opt_cost);

				/**
				 * Trigger event to allow third party plugins to apply a custom calculation for the option/extra fee or tax.
				 * 
				 * @since 	1.9.9
				 */
				$custom_calc_booking = ['days' => $daysdiff];
				$custom_calc_booking_room = array_merge($rooms_data[$num], ['room_cost' => (($rooms_data[$num]['cost'] ?? 0) ?: ($rooms_data[$num]['room_cost'] ?? 0))]);
				$custom_calculation = VBOFactory::getPlatform()->getDispatcher()->filter('onCalculateBookingOptionFeeCost', [$opt_cost, &$roption['record'], $custom_calc_booking, $custom_calc_booking_room]);
				if ($custom_calculation) {
					$opt_cost = (float) $custom_calculation[0];
				}

				$opt_final_cost = VikBooking::sayOptionalsPlusIva($opt_cost, $roption['record']['idiva']);
				if ($roption['record']['is_citytax'] == 1) {
					$tot_city_taxes += $opt_final_cost;
				} elseif ($roption['record']['is_fee'] == 1) {
					$tot_fees += $opt_final_cost;
				}
				if ($opt_final_cost == $opt_cost) {
					$opt_minus_iva = VikBooking::sayOptionalsMinusIva($opt_cost, $roption['record']['idiva']);
					$tot_taxes += ($opt_final_cost - $opt_minus_iva);
				} else {
					$tot_taxes += ($opt_final_cost - $opt_cost);
				}
				// increase booking total
				$total += $opt_final_cost;
			}

			if (!empty($room_option_str)) {
				// push string of options for this room party
				$room_options_str[$num] = $room_option_str;
			}
		}
		foreach ($room_extras as $num => $extras) {
			foreach ($extras as $ek => $extra) {
				// adjust key/property name for tax_rate to idtax for compatibility (key could be null)
				if (array_key_exists('tax_rate', $extra)) {
					$room_extras[$num][$ek]['idtax'] = $extra['tax_rate'];
					unset($room_extras[$num][$ek]['tax_rate']);
				}
				$ecplustax = !empty($room_extras[$num][$ek]['idtax']) ? VikBooking::sayOptionalsPlusIva((float)$extra['cost'], $room_extras[$num][$ek]['idtax']) : (float)$extra['cost'];
				$ecminustax = !empty($room_extras[$num][$ek]['idtax']) ? VikBooking::sayOptionalsMinusIva((float)$extra['cost'], $room_extras[$num][$ek]['idtax']) : (float)$extra['cost'];
				// increase booking total
				$total += $ecplustax;
				// increase total taxes
				$tot_taxes += ($ecplustax - $ecminustax);
			}
		}

		// write records
		$realback = VikBooking::getHoursRoomAvail() * 3600;
		$realback += $checkout_ts;
		$arrbusy = array();
		$rooms_booked = array();
		if ($status == 'confirmed') {
			$fully_closed = [];
			foreach ($rooms_data as $num => $r) {
				// push room ID booked
				array_push($rooms_booked, (int)$r['room_id']);
				// calculate the number of units to book for this room (1 or all if closure)
				$nowforend = $close_rooms ? $rooms_units[$r['room_id']]['units'] : 1;
				if ($close_rooms && in_array($r['room_id'], $fully_closed)) {
					// this room was already closed within this request
					continue;
				} else {
					$fully_closed[] = $r['room_id'];
				}
				// book all the necessary units
				for ($book = 1; $book <= $nowforend; $book++) {
					$q = "INSERT INTO `#__vikbooking_busy` (`idroom`,`checkin`,`checkout`,`realback`) VALUES(".(int)$r['room_id'].", ".(int)$checkin_ts.", ".(int)$checkout_ts.", ".(int)$realback.");";
					$this->dbo->setQuery($q);
					$this->dbo->execute();
					$lid = $this->dbo->insertid();
					$arrbusy[$num] = $lid;
				}
			}
		}

		/**
		 * Attempt to guess the best language to assign to the booking.
		 * 
		 * @since 	1.8.7
		 */
		$best_lang = VikChannelManager::guessBookingLangFromCountry($country);

		$sid = VikBooking::getSecretLink();
		$nowts = time();

		// prepare and store reservation record
		$book_record = new stdClass;
		$book_record->custdata 		 = $custdata;
		$book_record->ts 			 = $nowts;
		$book_record->status 		 = $status;
		$book_record->days 			 = $daysdiff;
		$book_record->checkin 		 = $checkin_ts;
		$book_record->checkout 		 = $checkout_ts;
		$book_record->custmail 		 = $email;
		$book_record->sid 			 = $sid;
		$book_record->idpayment 	 = '';
		$book_record->roomsnum 		 = $tot_rooms;
		$book_record->total 		 = $total;
		$book_record->adminnotes 	 = $fixed_notes;
		$book_record->lang 			 = !empty($best_lang) ? $best_lang : null;
		$book_record->country 		 = !empty($country) ? $country : null;
		$book_record->tot_taxes 	 = $tot_taxes;
		$book_record->tot_city_taxes = $tot_city_taxes;
		$book_record->tot_fees 		 = $tot_fees;
		$book_record->phone 		 = $phone;
		$book_record->closure 		 = $close_rooms ? 1 : 0;

		$this->dbo->insertObject('#__vikbooking_orders', $book_record, 'id');
		$neworderid = isset($book_record->id) ? $book_record->id : null;

		if (empty($neworderid)) {
			$this->setError(JText::_('VCMAPPEMPTYBOOKINGID'));
			return false;
		}

		// finalize records
		if ($status == 'confirmed') {
			// ConfirmationNumber
			$confirmnumber = VikBooking::generateConfirmNumber($neworderid, true);
			// Assign room specific unit
			$set_room_indexes = !$close_rooms ? VikBooking::autoRoomUnit() : false;
			$room_indexes_usemap = array();
			// busy-relations
			foreach ($rooms_data as $num => $r) {
				$q = "INSERT INTO `#__vikbooking_ordersbusy` (`idorder`,`idbusy`) VALUES(".(int)$neworderid.", ".(int)$arrbusy[$num].");";
				$this->dbo->setQuery($q);
				$this->dbo->execute();

				// Assign room specific unit
				$room_indexes = $set_room_indexes === true ? VikBooking::getRoomUnitNumsAvailable(array('id' => $neworderid, 'checkin' => $checkin_ts, 'checkout' => $checkout_ts), $r['room_id']) : array();
				$use_ind_key = 0;
				if (count($room_indexes)) {
					if (!array_key_exists($r['room_id'], $room_indexes_usemap)) {
						$room_indexes_usemap[$r['room_id']] = $use_ind_key;
					} else {
						$use_ind_key = $room_indexes_usemap[$r['room_id']];
					}
					$rooms_data[$num]['roomindex'] = (int)$room_indexes[$use_ind_key];
				}
				
				// prepare order-room object
				$oroom_record = new stdClass;
				$oroom_record->idorder = (int)$neworderid;
				$oroom_record->idroom = (int)$r['room_id'];
				$oroom_record->adults = (int)$r['adults'];
				$oroom_record->children = (int)$r['children'];
				$oroom_record->idtar = !empty($r['tariff_id']) ? $r['tariff_id'] : null;
				$oroom_record->optionals = isset($room_options_str[$num]) && !empty($room_options_str[$num]) ? $room_options_str[$num] : null;
				$oroom_record->childrenage = isset($children_age[$num]) && is_array($children_age[$num]) && count($children_age[$num]) ? json_encode($children_age[$num]) : null;
				$oroom_record->t_first_name = $fname;
				$oroom_record->t_last_name = $lname;
				$oroom_record->roomindex = count($room_indexes) ? (int)$room_indexes[$use_ind_key] : null;
				$oroom_record->cust_cost = isset($r['cost']) && $r['cost'] > 0 ? $r['cost'] : null;
				$oroom_record->cust_idiva = $oroom_record->cust_cost && $def_tax_id ? $def_tax_id : null;
				$oroom_record->extracosts = isset($room_extras[$num]) && count($room_extras[$num]) ? json_encode($room_extras[$num]) : null;
				$oroom_record->room_cost = !empty($r['room_cost']) ? $r['room_cost'] : null;

				$this->dbo->insertObject('#__vikbooking_ordersrooms', $oroom_record, 'id');

				if (count($room_indexes)) {
					$room_indexes_usemap[$r['room_id']]++;
				}
			}

			if (method_exists('VikBooking', 'updateSharedCalendars')) {
				// check if some of the rooms booked have shared calendars
				VikBooking::updateSharedCalendars($neworderid, $rooms_booked, $checkin_ts, $checkout_ts);
			}
		} else {
			// booking-rooms relations
			foreach ($rooms_data as $num => $r) {
				// prepare order-room object
				$oroom_record = new stdClass;
				$oroom_record->idorder = (int)$neworderid;
				$oroom_record->idroom = (int)$r['room_id'];
				$oroom_record->adults = (int)$r['adults'];
				$oroom_record->children = (int)$r['children'];
				$oroom_record->idtar = !empty($r['tariff_id']) ? $r['tariff_id'] : null;
				$oroom_record->t_first_name = $fname;
				$oroom_record->t_last_name = $lname;
				$oroom_record->cust_cost = isset($r['cost']) && $r['cost'] > 0 ? $r['cost'] : null;
				$oroom_record->cust_idiva = $oroom_record->cust_cost && $def_tax_id ? $def_tax_id : null;
				$oroom_record->room_cost = !empty($r['room_cost']) ? $r['room_cost'] : null;

				$this->dbo->insertObject('#__vikbooking_ordersrooms', $oroom_record, 'id');
			}
		}

		// customer booking should be saved after creating the rooms relations
		if ((int)$cpin->getNewCustomerId() < 1 && !empty($customer_id) && !empty($customer_pin)) {
			// existing customer
			$cpin->setNewPin($customer_pin);
			$cpin->setNewCustomerId($customer_id);
		}
		$cpin->saveCustomerBooking($neworderid);

		// VBO 1.10 or higher - Booking History
		if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
			VikBooking::getBookingHistoryInstance()->setBid($neworderid)->store('AN');
		}

		// send request to e4jConnect
		if ($status == 'confirmed') {
			if (!class_exists('SynchVikBooking')) {
				require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "synch.vikbooking.php");
			}
			$vcm = new SynchVikBooking($neworderid);
			$vcm->setSkipCheckAutoSync();
			$action_res = $vcm->sendRequest();
			if ($action_res === false && $vcm->isAvailabilityRequest()) {
				/**
				 * we set an error by returning false only in case of failure
				 * and if there is at least one active API channel to update.
				 */
				$this->setError(JText::_('VCMAPPERRE4JCSYNC'));
				return false;
			}
		}

		$response = new stdClass;
		$response->booking_id = (int)$neworderid;
		$response->booking_sid = $sid;
		$response->booking_ts = $nowts;

		// Set the response body for the App
		$this->response->body = $response;
	}

	/**
	 * App Get Graphs Data Request
	 * Returns information for building Graphs.
	 *
	 * @return void
	 */
	private function appGetGraphsData()
	{
		$room_id		= $this->input->getInt('room_id', 0);
		$date_from 		= $this->input->getString('fdate', '');
		$date_to 		= $this->input->getString('tdate', '');
		$graph_mode		= $this->input->getString('graph_mode', 'any');
		$graph_mode 	= in_array($graph_mode, array('bookings', 'nights', 'any')) ? $graph_mode : 'any';

		$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".(int)VikChannelManagerConfig::MOBILEAPP." LIMIT 1;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() < 1) {
			$this->setError(JText::_('VCMAPPCHREQREFUSED'));
			return false;
		}

		//If the date is not formatted correctly, an error is sent and false is returned
		if (count(explode('-', $date_from)) != 3 || count(explode('-', $date_to)) != 3) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}
		$info_from = getdate(strtotime($date_from));
		$info_to = getdate(strtotime($date_to));
		//dates_to must be after date_from
		if ($info_from[0] > $info_to[0]) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}

		//require the VikBooking library
		$this->importVboLib();

		//build clauses
		$from_ts 	= $info_from[0];
		$to_ts 		= mktime(23, 59, 59, $info_to['mon'], $info_to['mday'], $info_to['year']);
		$labels 	= array();
		$datasets 	= array('total' => array(), 'website' => array(), 'channels' => array());
		$nights_labels   = array();
		$nights_datasets = array('total' => array(), 'website' => array(), 'channels' => array());
		
		if ($graph_mode == 'bookings' || $graph_mode == 'any') {
			$arr_months 	= array();
			$from_clause 	= "`o`.`ts`>=".$from_ts;
			$to_clause 		= "`o`.`ts`<=".$to_ts;
			$order_by 		= "`o`.`ts` ASC";
			$q = "SELECT `o`.* 
				FROM `#__vikbooking_orders` AS `o` 
				".($room_id > 0 ? "LEFT JOIN `#__vikbooking_ordersrooms` `or` ON `or`.`idorder`=`o`.`id` AND `or`.`idroom`=".(int)$room_id." " : "")."
				WHERE `o`.`status`='confirmed'".($room_id > 0 ? " AND `or`.`idroom`=".(int)$room_id : "")." AND ".$from_clause." AND ".$to_clause." ORDER BY ".$order_by.";";
			$this->dbo->setQuery($q);
			$bookings = $this->dbo->loadAssocList();
			if ($bookings) {
				foreach ($bookings as $bk => $o) {
					if (JText::_('VCMAPPROOMCLOSED') == $o['custdata']) {
						continue;
					}
					$from_site = (empty($o['channel']) || stripos($o['channel'], 'channel manager') !== false);
					$source = $from_site ? 'website' : 'channels';
					$info_ts = getdate($o['ts']);
					$monyear = $info_ts['mon'].'-'.$info_ts['year'];
					$total = round(((float)$o['total'] - (float)$o['cmms'] - (float)$o['tot_taxes'] - (float)$o['tot_city_taxes'] - (float)$o['tot_fees']), 2);
					if (!isset($arr_months[$monyear])) {
						$arr_months[$monyear] = array('website' => 0, 'channels' => 0);
					}
					$arr_months[$monyear]['total'] += $total;
					$arr_months[$monyear][$source] += $total;
				}
			}
			if (count($arr_months)) {
				$labels = array_keys($arr_months);
				foreach ($arr_months as $monyear => $sources) {
					foreach ($sources as $source => $tot) {
						array_push($datasets[$source], $tot);
					}
				}
			}
		}

		if ($graph_mode == 'nights' || $graph_mode == 'any') {
			$arr_months 	= array();
			$from_clause 	= "`o`.`checkout`>=".$from_ts;
			$to_clause 		= "`o`.`checkin`<=".$to_ts;
			$order_by 		= "`o`.`checkin` ASC";
			$q = "SELECT `o`.* 
				FROM `#__vikbooking_orders` AS `o` 
				".($room_id > 0 ? "LEFT JOIN `#__vikbooking_ordersrooms` `or` ON `or`.`idorder`=`o`.`id` AND `or`.`idroom`=".(int)$room_id." " : "")."
				WHERE `o`.`status`='confirmed'".($room_id > 0 ? " AND `or`.`idroom`=".(int)$room_id : "")." AND ".$from_clause." AND ".$to_clause." ORDER BY ".$order_by.";";
			$this->dbo->setQuery($q);
			$bookings = $this->dbo->loadAssocList();
			if ($bookings) {
				foreach ($bookings as $bk => $o) {
					if (JText::_('VCMAPPROOMCLOSED') == $o['custdata']) {
						continue;
					}
					$from_site = (empty($o['channel']) || stripos($o['channel'], 'channel manager') !== false);
					$source = $from_site ? 'website' : 'channels';
					$info_ts = getdate($o['checkin']);
					$monyear = $info_ts['mon'].'-'.$info_ts['year'];
					//Check and calculate average totals and effective nights
					if($o['checkin'] < $from_ts || $o['checkout'] > $to_ts) {
						$nights_in = 0;
						$oinfo_start = getdate($o['checkin']);
						$oinfo_end = getdate($o['checkout']);
						$ots_end = mktime(23, 59, 59, $oinfo_end['mon'], ($oinfo_end['mday'] - 1), $oinfo_end['year']);
						while ($oinfo_start[0] < $ots_end) {
							if($oinfo_start[0] >= $from_ts && $oinfo_start[0] <= $to_ts) {
								$nights_in++;
								if($nights_in === 1) {
									//Reset variables for the month where the booking took place, it has to be the first night considered
									$monyear = $oinfo_start['mon'].'-'.$oinfo_start['year'];
								}
							}
							if($oinfo_start[0] > $to_ts) {
								break;
							}
							$oinfo_start = getdate(mktime(0, 0, 0, $oinfo_start['mon'], ($oinfo_start['mday'] + 1), $oinfo_start['year']));
						}
						$fullo_total = $o['total'];
						$o['total'] = round(($o['total'] / $o['days'] * $nights_in), 2);
						$o['cmms'] = (float)$o['cmms'] > 0 ? round(($o['total'] * $o['cmms'] / $fullo_total), 2) : $o['cmms'];
						$o['tot_taxes'] = (float)$o['tot_taxes'] > 0 ? round(($o['total'] * $o['tot_taxes'] / $fullo_total), 2) : $o['tot_taxes'];
						$o['tot_city_taxes'] = (float)$o['tot_city_taxes'] > 0 ? round(($o['total'] * $o['tot_city_taxes'] / $fullo_total), 2) : $o['tot_city_taxes'];
						$o['tot_fees'] = (float)$o['tot_fees'] > 0 ? round(($o['total'] * $o['tot_fees'] / $fullo_total), 2) : $o['tot_fees'];
						//set new number of nights, percentage of the booked nights calculated and update booking
						$o['avg_stay_pcent'] = 100 * $nights_in / $o['days'];
						$o['days'] = $nights_in;
					}
					if (!isset($arr_months[$monyear])) {
						$arr_months[$monyear] = array('website' => 0, 'channels' => 0);
					}
					$arr_months[$monyear]['total'] += $o['days'];
					$arr_months[$monyear][$source] += $o['days'];
				}
			}
			if (count($arr_months)) {
				$nights_labels = array_keys($arr_months);
				foreach ($arr_months as $monyear => $sources) {
					foreach ($sources as $source => $tot) {
						array_push($nights_datasets[$source], $tot);
					}
				}
			}
		}

		$response = new stdClass;
		$response->bookings = new stdClass;
		$response->nights = new stdClass;
		$response->bookings->labels = $labels;
		$response->bookings->datasets = $datasets;
		$response->nights->labels = $nights_labels;
		$response->nights->datasets = $nights_datasets;
		$response->currency_symbol = VikBooking::getCurrencySymb();

		//Set the response body for the App
		$this->response->body = $response;
	}

	/**
	 * App Get Expirations Request
	 * Returns the expiration date for
	 * for each channel by sending a request
	 * to the e4jConnect servers.
	 *
	 * @return void
	 */
	private function appGetExpirations()
	{
		$session = JFactory::getSession();
		$req_cont = $session->get('exec_exp', 0, 'vcm');
		if( $req_cont >= 5 ) {
			$this->setError('Request blocked. Too many attempts');
			return false;
		}

		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=exp&c=generic";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager EXP Request e4jConnect.com - VikBooking - extensionsforjoomla.com -->
<ExpiringRQ xmlns="http://www.e4jconnect.com/schemas/exprq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.VikChannelManager::getApiKey(true).'"/>
	<Fetch question="api" channel="all"/>
</ExpiringRQ>';
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();

		if($e4jC->getErrorNo()) {
			$this->setError('Generic error ('.$e4jC->getErrorNo().')');
			return false;
		}
		if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			$this->setError(JText::_('VCMAPPEXPIREDAPI'));
			return false;
		}

		//parse the response
		$exp_channels = array();
		$channels = explode("<br/>", $rs);
		if (empty($channels[0])) {
			$this->setError(JText::_('VCMAPPEXPIREDAPI'));
			return false;
		}
		foreach ($channels as $channel) {
			$ch_parts = explode(' ', str_replace('(', '', str_replace(')', '', trim($channel))));
			if (count($ch_parts) != 2) {
				continue;
			}
			$exp_ch = new stdClass;
			$exp_ch->channel = ucfirst($ch_parts[1]);
			$exp_ch->date = $ch_parts[0];
			array_push($exp_channels, $exp_ch);
		}

		$response = new stdClass;
		$response->channels = $exp_channels;

		//Set the response body for the App
		$this->response->body = $response;
	}

	/**
	 * App Get Notifications Request
	 * Returns an array of objects with the
	 * notifications for the requested days
	 * in the past.
	 *
	 * @return void
	 */
	private function appGetNotifications()
	{
		$days 		= $this->input->getInt('days', 1);
		$last_bid 	= $this->input->getInt('last_bid', 0);
		$skip_chat 	= $this->input->getInt('skip_chat', 0);

		if ($days < 1) {
			$this->setError(JText::_('VCMAPPRQEMPTY'));
			return false;
		}
		if ($days > 31) {
			$days = 31;
		}
		$now_info = getdate();
		$from_ts = mktime(0, 0, 0, $now_info['mon'], ($now_info['mday'] - $days), $now_info['year']);

		$notifications = array();

		/**
		 * We always prepend the unread chat messages in the notifications array.
		 * Chat notifications could be by-passed by passing "skip_chat": 1 in the rq.
		 * 
		 * @since 	1.6.13
		 */
		if (!$skip_chat) {
			// require VCMChatHandler class file
			require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'chat' . DIRECTORY_SEPARATOR . 'handler.php';

			// get all unread messages for the Hotel
			$chat_notifs = VCMChatHandler::getAllUnreadMessages(true);

			// push chat notifications
			foreach ($chat_notifs as $bid => $messages) {
				foreach ($messages as $mess) {
					// skip notifications too old
					if (strtotime($mess->dt) < $from_ts) {
						continue;
					}
					// push chat message array
					array_push($notifications, array(
						'type' => 'Chat',
						'id' => $bid,
						'from' => 'guest',
						'subject' => $mess->subject,
						'preview' => $mess->content,
						'dt' => $mess->dt,
					));
				}
			}
		}
		//

		$q = "SELECT `n`.`id`,`n`.`ts`,`n`.`type`,`n`.`from`,`n`.`cont`,`n`.`idordervb`,`b`.`custdata`,`b`.`status`,`b`.`checkin`,`b`.`checkout`,`b`.`channel` 
			FROM `#__vikchannelmanager_notifications` AS `n` LEFT JOIN `#__vikbooking_orders` `b` ON `n`.`idordervb`=`b`.`id` 
			WHERE `n`.`idordervb` IS NOT NULL AND `n`.`idordervb` > 0 AND `b`.`status` IS NOT NULL AND `n`.`ts` >=".$from_ts.($last_bid > 0 ? " AND `n`.`idordervb` > ".(int)$last_bid : "")." 
			ORDER BY `n`.`ts` ASC;";
		$this->dbo->setQuery($q);
		$bookings = $this->dbo->loadAssocList();
		if ($bookings) {
			$ids_buffer = array();
			foreach ($bookings as $booking) {
				//skip room closures
				if (JText::_('VCMAPPROOMCLOSED') == $booking['custdata']) {
					continue;
				}
				//
				$type = 'Book';
				if ($booking['status'] == 'cancelled') {
					$type = 'Cancel';
				} elseif (strpos($booking['cont'], '.BookingModified') !== false) {
					$type = 'Modify';
				}
				//Skip duplicates
				if (isset($ids_buffer[$type.$booking['idordervb']])) {
					continue;
				}
				//
				$source = 'VBO';
				if (!empty($booking['channel']) && $booking['channel'] != 'Channel Manager') {
					$ch_parts = explode('_', $booking['channel']);
					if (substr($ch_parts[0], 0, 8) != 'customer') {
						if (count($ch_parts) > 1) {
							$source = $ch_parts[1];
						} else {
							$source = ucwords($ch_parts[0]);
						}
					}
				}
				$notif = array(
					'type' => $type,
					'id' => $booking['idordervb'],
					'from' => $this->clearSourceName($source),
					'checkin' => date('Y-m-d', $booking['checkin']),
					'checkout' => date('Y-m-d', $booking['checkout'])
				);
				array_push($notifications, $notif);
				//set buffer to check duplicates
				$ids_buffer[$notif['type'].$notif['id']] = 1;
			}
		}

		//read the notifications of type Info
		$q = "SELECT * FROM `#__vikchannelmanager_notifications` WHERE `ts` >= ".$from_ts." AND `from`='e4j' AND `idordervb` = 0;";
		$this->dbo->setQuery($q);
		$infos = $this->dbo->loadAssocList();
		if ($infos) {
			foreach ($infos as $info) {
				$html_parts = explode("\n", $info['cont']);
				if (count($html_parts) < 2) {
					continue;
				}
				unset($html_parts[0]);
				$notif = array(
					'type' => 'Info',
					'from' => 'e4j',
					'html' => implode("\n", $html_parts)
				);
				array_push($notifications, $notif);
			}
		}
		//

		$response = new stdClass;
		$response->from_date = date('Y-m-d', $from_ts);
		$response->notifications = $notifications;

		//Set the response body for the App
		$this->response->body = $response;
	}
	
	/**
	 * Unified controller used to dispatch requests related to the CHAT system.
	 *
	 * @return void
	 */
	private function handleChat()
	{
		// load controller back-end
		require_once VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'chat.php';

		// instantiate controller
		$controller = new VikChannelManagerControllerChat();

		// extract task from JSON body
		$task = $this->input->get('task');

		// we need to check if the method exists before checking if it is callable
		// because this last function returns always true in case the class implements
		// __call magic method
		if (!method_exists($controller, $task) || !is_callable(array($controller, $task)))
		{
			$this->setError(sprintf('Chat method [%s] not supported', $task));
			return false;
		}

		$input = &JFactory::getApplication()->input;
			
		// iterate JSON input vars and inject them within global input
		foreach ($this->input->getArray() as $k => $v)
		{
			$input->set($k, $v);
		}

		try
		{
			// authenticate user because we have already validated the app account
			$controller->authenticated = true;

			// invoke method
			$this->response->body = $controller->{$task}($return = true);
		}
		catch (Exception $e)
		{
			// catch any exceptions and register the error message
			$this->setError($e->getMessage());
		}
	}

	/**
	 * Returns a list of reactions to a thread.
	 *
	 * @return void
	 * 
	 * @since 	1.9.0
	 */
	private function appGetReactions()
	{
		$idThread = $this->input->get('idthread');

		if (!$idThread) {
			$this->setError('Missing thread ID');
			return;
		}

		$this->dbo->setQuery(
			$this->dbo->getQuery(true)
				->select('*')
				->from($this->dbo->qn('#__vikchannelmanager_threads_messages_reactions'))
				->where($this->dbo->qn('idthread') . ' = ' . $this->dbo->q($idThread))
		);

		$response = new stdClass;
		$response->reactions = $this->dbo->loadObjectList();

		//Set the response body for the App
		$this->response->body = $response;
	}

	/**
	 * Tells whether the API framework is being used by the App.
	 * 
	 * @return 	boolean 	true if the call is made by the App, false otherwise.
	 * 
	 * @since 	1.7.0
	 */
	private function isAppE4jConnect()
	{
		if (isset($_SERVER['HTTP_ORIGIN']) && stripos($_SERVER['HTTP_ORIGIN'], 'ionic') !== false) {
			// we simply check the HTTP_ORIGIN. The only alternative would be checking HTTP_USER_AGENT.
			return true;
		}

		return false;
	}

	/**
	 * App Customers Search Request
	 *
	 * Method to retrieve a list of customers according to some parameters.
	 * The number of customers per request is limited to 20 by default.
	 * Pagination supported if there are more customers than the lim.
	 *
	 * @return 	void
	 * 
	 * @since 	1.7.4 (App v1.4)
	 */
	private function customersSearch()
	{
		$response = new stdClass();

		// get the keyword filter
		$requestKeyword = $this->input->getString('keyword', '');
		$requestLim = $this->input->getInt('lim', 20);
		$requestPage = $this->input->getInt('page', 0);
		$limStart = $requestPage * $requestLim;
		$limStart = $limStart >= 0 ? $limStart : 0;

		// prepare variables
		$customers 		= array();
		$customersData 	= array();
		$clauses 		= array();
		$keyclauses 	= array();
		$tot_customers 	= 0;
		$current_page 	= 0;

		if (!empty($requestKeyword)) {
			// compose keywords clauses
			if (ctype_digit($requestKeyword)) {
				// numeric filter should seek over the booking ID, confirmation number, phone number
				array_push($keyclauses, "`c`.`id`=" . $this->dbo->quote($requestKeyword));
				array_push($keyclauses, "`c`.`pin`=" . $this->dbo->quote($requestKeyword));
				array_push($keyclauses, "`c`.`phone` LIKE " . $this->dbo->quote('%' . $requestKeyword));
			}

			if (strpos($requestKeyword, '@') !== false) {
				// probably an email address was provided
				array_push($keyclauses, "`c`.`email` LIKE " . $this->dbo->quote('%' . $requestKeyword . '%'));
				array_push($keyclauses, "`c`.`pec` LIKE " . $this->dbo->quote('%' . $requestKeyword . '%'));
			}

			// always seek for customer name, company name and VAT
			array_push($keyclauses, "CONCAT_WS(' ', `c`.`first_name`, `c`.`last_name`) LIKE " . $this->dbo->quote('%' . $requestKeyword . '%'));
			array_push($keyclauses, "`c`.`company` LIKE " . $this->dbo->quote('%' . $requestKeyword . '%'));
			array_push($keyclauses, "`c`.`vat`=" . $this->dbo->quote($requestKeyword));

			// merge keyword clauses to all clauses
			array_push($clauses, '(' . implode(' OR ', $keyclauses) . ')');
		}

		// load all the customers according to the input parameters
		$q = "SELECT SQL_CALC_FOUND_ROWS `c`.*,
			(
				SELECT COUNT(*) 
				FROM `#__vikbooking_customers_orders` AS `co` 
				WHERE `co`.`idcustomer`=`c`.`id`
			) AS `tot_bookings`,
			(
				SELECT `country_name` 
				FROM `#__vikbooking_countries` AS `ct` 
				WHERE `ct`.`country_3_code`=`c`.`country`
			) AS `country_full_name` 
			FROM `#__vikbooking_customers` AS `c` 
			" . (count($clauses) ? 'WHERE ' . implode(' AND ', $clauses) : '') . " 
			ORDER BY `c`.`first_name` ASC";

		$this->dbo->setQuery($q, $limStart, $requestLim);
		$customersData = $this->dbo->loadAssocList();
		if ($customersData) {
			$this->dbo->setQuery('SELECT FOUND_ROWS();');
			$tot_customers = (int)$this->dbo->loadResult();
			$total_pages = ceil($tot_customers / $requestLim);
			if ($tot_customers > $requestLim) {
				if (($requestPage + 1) < $total_pages) {
					// pagination starts from 0
					$current_page = $requestPage;
				} elseif (($requestPage + 1) == $total_pages) {
					// we are on the last page (-1)
					$current_page = -1;
				}
			} else {
				// we are on the only and last page (-1)
				$current_page = -1;
			}
		} else {
			// no results and no other pages
			$current_page = -1;
		}

		foreach ($customersData as $client) {
			$customer_info = new stdClass;
			$customer_info->id = $client['id'];
			$customer_info->nominative = ucwords(trim($client['first_name'] . ' ' . $client['last_name']));
			$customer_info->first_name = $client['first_name'];
			$customer_info->last_name = $client['last_name'];
			$customer_info->country = $client['country_full_name'];
			$customer_info->city = $client['city'];
			$customer_info->address = $client['address'];
			$customer_info->zip = $client['zip'];
			$customer_info->email = $client['email'];
			$customer_info->phone = $client['phone'];
			$customer_info->tot_bookings = $client['tot_bookings'];
			$customer_info->pin = $client['pin'];
			$customer_info->flaguri = (!empty($client['country']) && is_file(implode(DIRECTORY_SEPARATOR, array(VBO_ADMIN_PATH, 'resources', 'countries', $client['country'] . '.png'))) ? VBO_ADMIN_URI . 'resources/countries/' . $client['country'] . '.png' : '');
			if (!empty($client['pic'])) {
				/**
				 * Customer profile picture (avatar).
				 * 
				 * @since 	1.8.6
				 */
				$use_customer_pic = strpos($client['pic'], 'http') === 0 ? $client['pic'] : VBO_SITE_URI . 'resources/uploads/' . $client['pic'];
				$customer_info->pic = $use_customer_pic;
			}

			array_push($customers, $customer_info);
		}

		// set response
		$response->total_customers = $tot_customers;
		$response->page = $current_page;
		$response->customers = $customers;

		// set the response object as the body for the App
		$this->response->body = $response;
	}

	/**
	 * App Customer Details Request
	 *
	 * Method to retrieve the full customer details.
	 *
	 * @return 	void
	 * 
	 * @since 	1.7.4 (App v1.4)
	 */
	private function customerDetails()
	{
		// get the ID filter
		$customer_id = $this->input->getInt('customer_id', 0);

		if (empty($customer_id)) {
			$this->setError(JText::_('VCMAPPRQEMPTY'));
			return false;
		}

		// load all the customers according to the input parameters
		$q = "SELECT SQL_CALC_FOUND_ROWS `c`.*,
			(
				SELECT COUNT(*) 
				FROM `#__vikbooking_customers_orders` AS `co` 
				WHERE `co`.`idcustomer`=`c`.`id`
			) AS `tot_bookings`,
			(
				SELECT `country_name` 
				FROM `#__vikbooking_countries` AS `ct` 
				WHERE `ct`.`country_3_code`=`c`.`country`
			) AS `country_full_name` 
			FROM `#__vikbooking_customers` AS `c` 
			WHERE `c`.`id`={$customer_id}";

		$this->dbo->setQuery($q, 0, 1);
		$record = $this->dbo->loadObject();
		if (!$record) {
			$this->setError(JText::_('VCMAPPNORECORDS'));
			return false;
		}

		// require VBO library
		$this->importVboLib();

		// adjust record
		$record->nominative = ucwords(trim($record->first_name . ' ' . $record->last_name));
		if (!empty($record->docimg)) {
			$record->docimg = VBO_ADMIN_URI . 'resources/idscans/' . $record->docimg;
		}

		// country flag
		$record->flaguri = (!empty($record->country) && is_file(implode(DIRECTORY_SEPARATOR, array(VBO_ADMIN_PATH, 'resources', 'countries', $record->country . '.png'))) ? VBO_ADMIN_URI . 'resources/countries/' . $record->country . '.png' : '');
		
		// build a list of customer documents
		$record->documents = array();
		if (class_exists('VikBooking') && method_exists('VikBooking', 'getCustomerDocuments')) {
			$record->documents = VikBooking::getCustomerDocuments($record->id);
		}

		if (!empty($record->pic)) {
			/**
			 * Customer profile picture (avatar).
			 * 
			 * @since 	1.8.6
			 */
			$use_customer_pic = strpos($record->pic, 'http') === 0 ? $record->pic : VBO_SITE_URI . 'resources/uploads/' . $record->pic;
			$record->pic = $use_customer_pic;
		} else {
			// unset unnecessary property
			unset($record->pic);
		}

		// unset unnecessary properties
		unset($record->cfields);
		unset($record->ujid);
		unset($record->docsfolder);
		unset($record->country);
		unset($record->ischannel);
		unset($record->chdata);
		if (empty($record->docimg)) {
			unset($record->docimg);
		}

		// set the response object as the body for the App
		$this->response->body = $record;
	}

	/**
	 * App Remove Customer Request
	 *
	 * Method to remove one customer.
	 *
	 * @return 	void
	 * 
	 * @since 	1.7.4 (App v1.4)
	 */
	private function cancelCustomer()
	{
		// get the ID filter
		$customer_id = $this->input->getInt('customer_id', 0);

		if (empty($customer_id)) {
			$this->setError(JText::_('VCMAPPRQEMPTY'));
			return false;
		}

		$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".(int)VikChannelManagerConfig::MOBILEAPP." LIMIT 1;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() < 1) {
			$this->setError(JText::_('VCMAPPCHREQREFUSED'));
			return false;
		}

		$q = "SELECT `id` FROM `#__vikbooking_customers` WHERE `id`={$customer_id}";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if (!$this->dbo->getNumRows()) {
			$this->setError(JText::_('VCMAPPNORECORDS'));
			return false;
		}

		$q = "DELETE FROM `#__vikbooking_customers` WHERE `id`={$customer_id}";
		$this->dbo->setQuery($q);
		$this->dbo->execute();

		$q = "DELETE FROM `#__vikbooking_customers_orders` WHERE `idcustomer`={$customer_id}";
		$this->dbo->setQuery($q);
		$this->dbo->execute();

		// set the response
		$this->response->body = 'e4j.ok';
	}

	/**
	 * App Modify Customer Request
	 *
	 * Method to update one customer.
	 *
	 * @return 	void
	 * 
	 * @since 	1.7.4 (App v1.4)
	 */
	private function modifyCustomer()
	{
		// get the ID filter
		$customer_id = $this->input->getInt('customer_id', 0);

		if (empty($customer_id)) {
			$this->setError(JText::_('VCMAPPRQEMPTY'));
			return false;
		}

		$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".(int)VikChannelManagerConfig::MOBILEAPP." LIMIT 1;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() < 1) {
			$this->setError(JText::_('VCMAPPCHREQREFUSED'));
			return false;
		}

		// current customer record
		$q = "SELECT * FROM `#__vikbooking_customers` WHERE `id`={$customer_id}";
		$this->dbo->setQuery($q, 0, 1);
		$record = $this->dbo->loadObject();
		if (!$record) {
			// customer not found
			$this->setError(JText::_('VCMAPPNORECORDS'));
			return false;
		}

		// require VBO library
		$this->importVboLib();

		// get fields to update from request
		$update_fields = new stdClass;
		$update_fields->first_name = $this->input->getString('first_name', '');
		$update_fields->last_name = $this->input->getString('last_name', '');
		$update_fields->email = $this->input->getString('custemail', '');
		$update_fields->phone = $this->input->getString('phone', '');
		$update_fields->country = $this->input->getString('country', '');
		$update_fields->address = $this->input->getString('address', '');
		$update_fields->city = $this->input->getString('city', '');
		$update_fields->doctype = $this->input->getString('doctype', '');
		$update_fields->docnum = $this->input->getString('docnum', '');
		$update_fields->company = $this->input->getString('company', '');
		$update_fields->vat = $this->input->getString('vat', '');
		$update_fields->gender = $this->input->getString('gender', '');
		$update_fields->bdate = $this->input->getString('bdate', '');
		$update_fields->pbirth = $this->input->getString('pbirth', '');
		$update_fields->fisccode = $this->input->getString('fisccode', '');
		$update_fields->pec = $this->input->getString('pec', '');
		$update_fields->recipcode = $this->input->getString('recipcode', '');
		$update_fields->notes = $this->input->getString('notes', '');

		// update fields validation
		if (!empty($update_fields->gender)) {
			$update_fields->gender = strtoupper($update_fields->gender);
			if ($update_fields->gender != 'M' && $update_fields->gender != 'F') {
				$update_fields->gender = '';
			}
		}
		if (!empty($update_fields->country)) {
			try {
				$update_fields->country = VikBooking::getCPinIstance()->get3CharCountry($update_fields->country);
			} catch (Exception $e) {
				$update_fields->country = '';
			}
		}

		// merge non-empty values to the current record's properties
		foreach ($update_fields as $prop => $val) {
			if (empty($val) || !property_exists($record, $prop)) {
				continue;
			}
			// set new value from request
			$record->{$prop} = $val;
		}

		// update object
		if (!$this->dbo->updateObject('#__vikbooking_customers', $record, 'id')) {
			$this->setError('Update error');
			return false;
		}

		// set the response to the new record stored
		$this->response->body = $record;
	}

	/**
	 * App Get Reviews Request
	 *
	 * Method to retrieve the guest reviews information.
	 * The number of reviews per request is limited to 20 by default.
	 * Pagination supported if there are more reviews than the lim.
	 *
	 * @return 	void
	 * 
	 * @since 	1.7.4 (App v1.4)
	 */
	private function getReviews()
	{
		$response = new stdClass();

		// pagination/limit filters
		$requestLim  = $this->input->getInt('lim', 20);
		$requestPage = $this->input->getInt('page', 0);
		$limStart 	 = $requestPage * $requestLim;
		$limStart 	 = $limStart >= 0 ? $limStart : 0;

		// prepare variables
		$reviews 	  = array();
		$reviewsData  = array();
		$tot_reviews  = 0;
		$current_page = 0;

		// query filters
		$filters = array(
			'fromdate' 	=> $this->input->getString('fromdate', ''),
			'todate' 	=> $this->input->getString('todate', ''),
			'lang' 		=> $this->input->getString('lang', ''),
			'country' 	=> $this->input->getString('country', ''),
			'channel' 	=> $this->input->getString('channel', ''),
			'prop_name' => $this->input->getString('prop_name', ''),
			'revid' 	=> $this->input->getInt('revid', 0),
		);

		// adjust filters
		if (!empty($filters['fromdate']) && strtotime($filters['fromdate']) <= 0) {
			// prevent default dates like 0000-00-00 00:00:00
			$filters['fromdate'] = '';
		}
		if (!empty($filters['todate']) && strtotime($filters['todate']) <= 0) {
			// prevent default dates like 0000-00-00 00:00:00
			$filters['todate'] = '';
		}
		if (!empty($filters['fromdate']) && empty($filters['todate'])) {
			// single dates are unified
			$filters['todate'] = $filters['fromdate'];
		} elseif (empty($filters['fromdate']) && !empty($filters['todate'])) {
			// single dates are unified
			$filters['fromdate'] = $filters['todate'];
		}

		// prepare query clauses
		$clauses = array();
		if (!empty($filters['fromdate'])) {
			array_push($clauses, "`dt`>=".$this->dbo->quote(date('Y-m-d H:i:s', strtotime($filters['fromdate']))));
		}
		if (!empty($filters['todate'])) {
			$to_info = getdate(strtotime($filters['fromdate']));
			array_push($clauses, "`dt`<=".$this->dbo->quote(date('Y-m-d H:i:s', mktime(23, 59, 59, $to_info['mon'], $to_info['mday'], $to_info['year']))));
		}
		if (!empty($filters['channel'])) {
			if (strtolower($filters['channel']) == strtolower(JText::_('VCMWEBSITE'))) {
				// reviews coming from the website have a null "channel" property
				array_push($clauses, "`channel` IS NULL");
			} else {
				array_push($clauses, "`channel`=".$this->dbo->quote($filters['channel']));
			}
		}
		if (!empty($filters['lang'])) {
			array_push($clauses, "`lang`=".$this->dbo->quote($filters['lang']));
		}
		if (!empty($filters['country'])) {
			array_push($clauses, "`country`=".$this->dbo->quote($filters['country']));
		}
		if (!empty($filters['prop_name'])) {
			array_push($clauses, "`prop_name`=".$this->dbo->quote($filters['prop_name']));
		}
		if (!empty($filters['revid'])) {
			array_push($clauses, "`id`=".$this->dbo->quote($filters['revid']));
		}

		$q = "SELECT SQL_CALC_FOUND_ROWS * FROM `#__vikchannelmanager_otareviews` " . (count($clauses) ? 'WHERE ' . implode(' AND ', $clauses) . ' ' : '') . "ORDER BY `dt` DESC";
		$this->dbo->setQuery($q, $limStart, $requestLim);
		$reviewsData = $this->dbo->loadObjectList();
		if ($reviewsData) {
			$this->dbo->setQuery('SELECT FOUND_ROWS();');
			$tot_reviews = (int)$this->dbo->loadResult();
			$total_pages = ceil($tot_reviews / $requestLim);
			if ($tot_reviews > $requestLim) {
				if (($requestPage + 1) < $total_pages) {
					// pagination starts from 0
					$current_page = $requestPage;
				} elseif (($requestPage + 1) == $total_pages) {
					// we are on the last page (-1)
					$current_page = -1;
				}
			} else {
				// we are on the only and last page (-1)
				$current_page = -1;
			}
		} else {
			// no results and no other pages
			$current_page = -1;
		}

		foreach ($reviewsData as $grev) {
			// adjust raw content
			if (!empty($grev->content)) {
				$grev->content = json_decode($grev->content);
			}
			if (!is_object($grev->content)) {
				// raw content must default to null
				$grev->content = null;
			}

			// adjust channel name
			if ($grev->uniquekey == VikChannelManagerConfig::AIRBNBAPI) {
				$grev->channel = 'airbnb';
			}

			// set has_reply and owner_reply properties by checking the content
			$grev->has_reply = false;
			$grev->owner_reply = null;
			if (is_object($grev->content)) {
				if (isset($grev->content->reply)) {
					if (is_string($grev->content->reply) && !empty($grev->content->reply)) {
						// probably a website review
						$grev->has_reply = true;
						$grev->owner_reply = $grev->content->reply;
					} elseif (is_object($grev->content->reply) && !empty($grev->content->reply->text)) {
						// review with reply downloaded through Booking.com has got a "text" property
						$grev->has_reply = true;
						$grev->owner_reply = $grev->content->reply->text;
					}
				} elseif (!empty($grev->content->reviewee_response)) {
					// probably an Airbnb API review
					$grev->has_reply = true;
					$grev->owner_reply = $grev->content->reviewee_response;
				}
			}

			// set review content summary and content details
			$grev->summary = '';
			$grev->content_details = array();
			if (is_object($grev->content) && property_exists($grev->content, 'content') && is_object($grev->content->content)) {
				$summary_txts = array();
				foreach ($grev->content->content as $cont_prop => $cont_txt) {
					if (is_string($cont_prop) && stripos($cont_prop, 'lang') !== false) {
						// we skip this type of content
						continue;
					}
					if (!is_string($cont_txt) || strlen($cont_txt) <= 5) {
						// small, invalid or empty field
						continue;
					}
					
					// push content text in summaries
					array_push($summary_txts, $cont_txt);
					
					// push content details
					$content_detail = new stdClass;
					$content_detail->type = ucwords($cont_prop);
					$content_detail->text = $cont_txt;
					array_push($grev->content_details, $content_detail);
				}
				if (count($summary_txts)) {
					$grev->summary = strlen($summary_txts[0]) > 100 ? $summary_txts[0] : implode(' - ', $summary_txts);
				}
			}

			// compose score details array
			$grev->score_details = array();
			if (is_object($grev->content) && property_exists($grev->content, 'scoring')) {
				if (is_object($grev->content->scoring) && count(get_object_vars($grev->content->scoring))) {
					// scoring per service is available
					foreach ($grev->content->scoring as $service_name => $service_score) {
						if (!is_scalar($service_score) || is_bool($service_score) || !is_string($service_name)) {
							// we only need strings or numbers
							continue;
						}
						if ($service_name == 'review_score') {
							// we skip this information as it's the average review score again
							continue;
						}

						// count stars rating (if supported)
						$stars = 0;
						$stars_pool = array();
						if (empty($grev->channel)) {
							// website review, not an OTA review
							$stars = floor((int)$service_score / 2);
						}
						if ($stars > 0) {
							// the pool of stars to display
							$stars_pool = range(1, $stars);
						}

						// push service name and score (if any)
						$service_detail = new stdClass;
						$service_detail->type   = ucwords($service_name);
						$service_detail->score  = $service_score;
						$service_detail->stars  = $stars;
						$service_detail->astars = $stars_pool;
						array_push($grev->score_details, $service_detail);
					}
				}
			}

			// push adjusted review record object
			array_push($reviews, $grev);
		}

		// set response
		$response->total_reviews = $tot_reviews;
		$response->page = $current_page;
		$response->reviews = $reviews;

		// set the response object as the body for the App
		$this->response->body = $response;
	}

	/**
	 * App Update Reviews Request
	 *
	 * Method to update one guest review.
	 * Toggle publish status or reply to review.
	 *
	 * @return 	void
	 * 
	 * @since 	1.7.4 (App v1.4)
	 */
	private function updateReview()
	{
		// get the ID filter
		$review_id = $this->input->getInt('revid', 0);

		if (empty($review_id)) {
			$this->setError(JText::_('VCMAPPRQEMPTY'));
			return false;
		}

		$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".(int)VikChannelManagerConfig::MOBILEAPP." LIMIT 1;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() < 1) {
			$this->setError(JText::_('VCMAPPCHREQREFUSED'));
			return false;
		}

		// current review record
		$q = "SELECT * FROM `#__vikchannelmanager_otareviews` WHERE `id`={$review_id}";
		$this->dbo->setQuery($q, 0, 1);
		$record = $this->dbo->loadObject();
		if (!$record) {
			// review not found
			$this->setError(JText::_('VCMAPPNORECORDS'));
			return false;
		}

		// request vars
		$published  = $this->input->getInt('published', -1);
		$reply_text	= $this->input->getString('reply', '');

		if ($published >= 0 && $published < 2) {
			// change published status (0/1)
			$record->published = $published;

			/**
			 * Make sure we are not publishing an Airbnb API review as it's forbidden.
			 * 
			 * @since 	1.8.0
			 */
			if ($record->published && $record->uniquekey == VikChannelManagerConfig::AIRBNBAPI) {
				$this->setError('Airbnb reviews should not be published as outlined in their Terms of Service');
				return false;
			}
			//

			// update record immediately
			if (!$this->dbo->updateObject('#__vikchannelmanager_otareviews', $record, 'id')) {
				$this->setError('Update error on published');
				return false;
			}
		}

		if (!empty($reply_text)) {
			// make sure a reply does not exist
			$grev_content = null;
			if (!empty($record->content)) {
				$grev_content = json_decode($record->content);
			}
			if (!is_object($grev_content)) {
				// replying not allowed
				$this->setError('Replying to this review is not supported');
				return false;
			}

			$can_reply = 0;
			if (in_array($record->uniquekey, array(VikChannelManagerConfig::BOOKING)) && (!isset($grev_content->reply) || empty($grev_content->reply->text))) {
				// we set a flag that let us understand the reply is allowed for Booking.com
				$can_reply = 1;
			} elseif ($record->uniquekey == VikChannelManagerConfig::AIRBNBAPI && empty($grev_content->reviewee_response)) {
				// host reply to guest-to-host review is allowed
				$can_reply = 1;
			} elseif (empty($record->channel) && empty($record->uniquekey) && is_object($grev_content) && property_exists($grev_content, 'reply') && empty($grev_content->reply)) {
				// website review with no reply
				$can_reply = 1;
			}

			if (!$can_reply) {
				$this->setError('Replying not allowed or a reply already exists for this review');
				return false;
			}

			if (!empty($record->channel) && !empty($record->uniquekey)) {
				// OTA review - make the request to e4jConnect

				// load the channel for this review
				$channel = VikChannelManager::getChannel($record->uniquekey);
				$channel['params'] = json_decode($channel['params'], true);
				$channel['params'] = is_array($channel['params']) ? $channel['params'] : array();

				// get the first parameter, which may not be 'hotelid'
				$usehid = '';
				foreach ($channel['params'] as $v) {
					$usehid = $v;
					break;
				}

				// make sure the params saved for this channel match the account ID of the review
				if ((string)$usehid != (string)$record->prop_first_param) {
					// we need to find the proper params even though changing the hotel ID would be sufficient for most channels
					$q = "SELECT `prop_params` FROM `#__vikchannelmanager_roomsxref` WHERE `idchannel`=" . $this->dbo->quote($record->uniquekey) . " AND `prop_params` LIKE " . $this->dbo->quote('%' . $record->prop_first_param . '%');
					$this->dbo->setQuery($q, 0, 1);
					$this->dbo->execute();
					if (!$this->dbo->getNumRows()) {
						$this->setError('No rooms mapped for Account ID ' . $record->prop_first_param);
						return false;
					}
					// overwrite channel params with the account requested
					$channel['params'] = json_decode($this->dbo->loadResult(), true);
					$channel['params'] = is_array($channel['params']) ? $channel['params'] : array();
					// get the first parameter, which may not be 'hotelid'
					foreach ($channel['params'] as $v) {
						$usehid = $v;
						break;
					}
				}

				// required filter by hotel ID
				$filters = array('hotelid="' . trim($usehid) . '"');

				// OTA Review ID filter is mandatory
				if (empty($record->review_id)) {
					$this->setError('Missing OTA_REVIEW_ID');
					return false;
				}
				array_push($filters, 'revid="' . $record->review_id . '"');

				// define the channel name to use on e4jConnect
				$usech = $channel['name'];
				
				// make the request to e4jConnect to reply to the review
				$e4jc_url = "https://e4jconnect.com/channelmanager/?r=rprew&c=" . $usech;
				$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager RREVW Request e4jConnect.com - '.ucwords($channel['name']).' -->
<ReadReviewsRQ xmlns="http://www.e4jconnect.com/channels/rrevwrq">
	<Notify client="'.JUri::root().'"/>
	<Api key="'.VikChannelManager::getApiKey(true).'"/>
	<ReadReviews>
		<Fetch '.implode(' ', $filters).'/>
		<Reply><![CDATA['.$reply_text.']]></Reply>
	</ReadReviews>
</ReadReviewsRQ>';
				
				$e4jC = new E4jConnectRequest($e4jc_url);
				$e4jC->setPostFields($xml);
				$rs = $e4jC->exec();
				if ($e4jC->getErrorNo()) {
					$this->setError('cURL error ' . $e4jC->getErrorNo());
					return false;
				}
				if (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
					$this->setError(VikChannelManager::getErrorFromMap($rs));
					return false;
				}

				/**
				 * The response should be a string.
				 */
				if (strpos($rs, 'e4j.ok') === false) {
					$this->setError('Invalid response');
					return false;
				}
			}

			// update the content of this review with the reply message so that no other replies will be allowed
			if (empty($record->channel) && empty($record->uniquekey)) {
				// website review we update the "reply" property
				$grev_content->reply = $reply_text;
			} elseif ($record->uniquekey == VikChannelManagerConfig::AIRBNBAPI) {
				// Airbnb API OTA review, we update the "reviewee_response" property
				$grev_content->reviewee_response = $reply_text;
				if (isset($grev_content->reply)) {
					unset($grev_content->reply);
				}
			} else {
				// OTA review, we update the "text" property in "reply"
				$grev_content->reply = new stdClass;
				$grev_content->reply->text = $reply_text;
			}
			
			// update raw content object
			$record->content = json_encode($grev_content);

			// update record
			if (!$this->dbo->updateObject('#__vikchannelmanager_otareviews', $record, 'id')) {
				$this->setError('Update error on reply');
				return false;
			}
		}

		// set the response to the new record stored
		$this->response->body = $record;
	}

	/**
	 * App Submit Host to Guest Review Request
	 *
	 * Method to submit the host review for the guest.
	 *
	 * @return 	void
	 * 
	 * @since 	1.8.1 (App v1.5)
	 */
	private function submitHostGuestReview()
	{
		// get the request values
		$vbo_id = $this->input->getInt('vbo_id', 0);
		$public_review = $this->input->getString('public_review', '');
		$private_review = $this->input->getString('private_review', '');
		$review_cat_clean = $this->input->getInt('review_cat_clean', 5);
		$review_cat_clean_comment = $this->input->getString('review_cat_clean_comment', '');
		$review_cat_comm = $this->input->getInt('review_cat_comm', 5);
		$review_cat_comm_comment = $this->input->getString('review_cat_comm_comment', '');
		$review_cat_hrules = $this->input->getInt('review_cat_hrules', 5);
		$review_cat_hrules_comment = $this->input->getString('review_cat_hrules_comment', '');
		$review_host_again = $this->input->getInt('review_host_again', 1);

		if (empty($vbo_id) || empty($public_review)) {
			$this->setError(JText::_('VCMAPPRQEMPTY'));
			return false;
		}

		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=" . (int)$vbo_id . ";";
		$this->dbo->setQuery($q);
		$reservation = $this->dbo->loadAssoc();
		if (!$reservation) {
			$this->setError('Booking ID not found');
			return false;
		}

		if (!VikChannelManager::hostToGuestReviewSupported($reservation)) {
			$this->setError('Booking does not support host to guest review at this time');
			return false;
		}

		$channel = VikChannelManager::getChannel(VikChannelManagerConfig::AIRBNBAPI);
		if (!is_array($channel) || !count($channel)) {
			$this->setError('No valid channels available to review your guest');
			return false;
		}

		// find the mapping information for the room(s) booked
		$account_key = null;
		$q = "SELECT `or`.`idroom`, `x`.`idroomota`, `x`.`prop_params` FROM `#__vikbooking_ordersrooms` AS `or` LEFT JOIN `#__vikchannelmanager_roomsxref` AS `x` ON `or`.`idroom`=`x`.`idroomvb` WHERE `or`.`idorder`=" . $reservation['id'] . " AND `x`.`idchannel`=" . (int)$channel['uniquekey'] . ";";
		$this->dbo->setQuery($q);
		$rooms_assoc = $this->dbo->loadAssocList();
		if ($rooms_assoc) {
			foreach ($rooms_assoc as $rassoc) {
				if (empty($rassoc['prop_params'])) {
					continue;
				}
				$account_data = json_decode($rassoc['prop_params'], true);
				if (is_array($account_data) && count($account_data)) {
					foreach ($account_data as $acc_val) {
						// we grab the first param value
						if (!empty($acc_val)) {
							$account_key = $acc_val;
							break 2;
						}
					}
				}
			}
		}
		if (empty($account_key)) {
			// the account credentials must be present to perform the request
			$this->setError('Could not find the channel account params');
			return false;
		}

		// sanitize values for XML request
		if (defined('ENT_XML1')) {
			// only available from PHP 5.4 and on
			$public_review = htmlspecialchars($public_review, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			$private_review = htmlspecialchars($private_review, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			$review_cat_clean_comment = htmlspecialchars($review_cat_clean_comment, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			$review_cat_comm_comment = htmlspecialchars($review_cat_comm_comment, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			$review_cat_hrules_comment = htmlspecialchars($review_cat_hrules_comment, ENT_XML1 | ENT_COMPAT, 'UTF-8');
			
		} else {
			// fallback to plain all html entities
			$public_review = htmlentities($public_review);
			$private_review = htmlentities($private_review);
			$review_cat_clean_comment = htmlentities($review_cat_clean_comment);
			$review_cat_comm_comment = htmlentities($review_cat_comm_comment);
			$review_cat_hrules_comment = htmlentities($review_cat_hrules_comment);
		}

		// make the request to e4jConnect
		$api_key = VikChannelManager::getApiKey(true);
		
		$e4jc_url = "https://slave.e4jconnect.com/channelmanager/?r=htgr&c=" . $channel['name'];
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager HTGR Request e4jConnect.com - Vik Channel Manager -->
<HostToGuestReviewRQ xmlns="http://www.e4jconnect.com/schemas/htgrrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $api_key . '"/>
	<Fetch hotelid="' . $account_key . '"/>
	<HostReview otaresid="' . $reservation['idorderota'] . '">
		<Public><![CDATA[' . $public_review . ']]></Public>
		<Private><![CDATA[' . $private_review . ']]></Private>
		<Ratings>
			<Rating category="cleanliness" score="' . $review_cat_clean . '"><![CDATA[' . $review_cat_clean_comment . ']]></Rating>
			<Rating category="communication" score="' . $review_cat_comm . '"><![CDATA[' . $review_cat_comm_comment . ']]></Rating>
			<Rating category="respect_house_rules" score="' . $review_cat_hrules . '"><![CDATA[' . $review_cat_hrules_comment . ']]></Rating>
			<Rating category="host_again" score="' . $review_host_again . '" />
		</Ratings>
	</HostReview>
</HostToGuestReviewRQ>';
		
		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$e4jC->slaveEnabled = true;
		$rs = $e4jC->exec();
		if ($e4jC->getErrorNo()) {
			$this->setError('Request error: ' . VikChannelManager::getErrorFromMap($e4jC->getErrorMsg()));
			return false;
		} elseif (substr($rs, 0, 9) == 'e4j.error' || substr($rs, 0, 11) == 'e4j.warning') {
			$this->setError('Response error: ' . VikChannelManager::getErrorFromMap($rs));
			return false;
		}

		/**
		 * Response was successful, go back to the View that will try to dismiss
		 * the modal and/or redirect. Store a log in the history of VBO first.
		 */
		$say_channel_name = $channel['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? 'Airbnb' : ucfirst($channel['name']);
		
		// try to update the VBO Booking History
		try {
			// import VBO library
			$this->importVboLib();

			if (method_exists('VikBooking', 'getBookingHistoryInstance')) {
				VikBooking::getBookingHistoryInstance()->setBid($reservation['id'])->store('CM', $say_channel_name . ' - ' . JText::_('VCM_HOST_TO_GUEST_REVIEW'));
			}
		} catch (Exception $e) {
			// do nothing
		}

		// insert record in VCM so that the system will detect that a review was left already
		$transient_name = 'host_to_guest_review_' . $channel['uniquekey'] . '_' . $reservation['id'];
		// build host review object with some basic details
		$host_review_object = new stdClass;
		$host_review_object->public_review = $public_review;
		$host_review_object->private_review = $private_review;
		$host_review_object->review_cat_clean = $review_cat_clean;
		$host_review_object->review_cat_comm = $review_cat_comm;
		$host_review_object->review_cat_hrules = $review_cat_hrules;
		// store record
		$q = "INSERT INTO `#__vikchannelmanager_config` (`param`,`setting`) VALUES(" . $this->dbo->quote($transient_name) . ", " . $this->dbo->quote(json_encode($host_review_object)) . ");";
		$this->dbo->setQuery($q);
		$this->dbo->execute();

		// set the response to the current booking record
		$reservation['host_guest_review'] = 0;
		$this->response->body = $reservation;
	}

	/**
	 * App Calculate Rate Plans Costs Request
	 *
	 * Method to calculate the cost for the room-parties.
	 *
	 * @return 	void
	 * 
	 * @since 	1.7.4 (App v1.4)
	 */
	private function calculateRatePlans()
	{
		// require VBO library
		$this->importVboLib();

		// require the TAC class
		require_once(VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'tac.vikbooking.php');

		// prepare filters
		$room_id 	= $this->input->getInt('room_id', 0);
		$adults 	= $this->input->getInt('adults', 1);
		$children 	= $this->input->getInt('children', 0);
		$checkin 	= $this->input->getString('checkin', '');
		$checkout 	= $this->input->getString('checkout', '');

		if (empty($checkin) || empty($checkout)) {
			$this->setError(JText::_('VCMAPPRQEMPTY'));
			return false;
		}

		// dates must be formatted correctly
		$checkin_ts  = strtotime($checkin);
		$checkout_ts = strtotime($checkout);
		if (count(explode('-', $checkin)) != 3 || count(explode('-', $checkout)) != 3 || !$checkin_ts || !$checkout_ts) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}

		// overwrite check-in and check-out dates so that they won't contain any time
		$checkin  = date('Y-m-d', $checkin_ts);
		$checkout = date('Y-m-d', $checkout_ts);

		// count number of nights
		$in_infos  = getdate(strtotime($checkin));
		$out_infos = getdate(strtotime($checkout));
		$totnights = 0;
		while ($in_infos[0] < $checkout_ts) {
			// increase number of nights
			$totnights++;
			// go to next day
			$in_infos = getdate(mktime($in_infos['hours'], $in_infos['minutes'], $in_infos['seconds'], $in_infos['mon'], ($in_infos['mday'] + 1), $in_infos['year']));
		}

		// make sure the nights are at least 1
		$totnights = $totnights < 1 ? 1 : $totnights;

		// make sure the stay lasts for at least 1 night
		if ($checkout == $checkin) {
			$totnights = 1;
			$checkout = date('Y-m-d', mktime(0, 0, 0, $out_infos['mon'], ($out_infos['mday'] + 1), $out_infos['year']));
		}

		// prepare vars for injection
		$inject = array(
			'e4jauth' => md5('vbo.e4j.vbo'),
			'req_type' => 'hotel_availability',
			'start_date' => $checkin,
			'end_date' => $checkout,
			'nights' => $totnights,
			'adults' => array($adults),
			'children' => array($children),
		);

		// inject request vars
		$input = &JFactory::getApplication()->input;
		foreach ($inject as $k => $v) {
			$input->set($k, $v);
		}

		// turn flag on to return the results
		TACVBO::$getArray = true;

		// make the request
		$website_rates = TACVBO::tac_av_l();

		// response validation
		if (!is_array($website_rates)) {
			// error returned
			$this->setError($website_rates);
			return false;
		}
		if (isset($website_rates['e4j.error'])) {
			// another type of error returned
			$this->setError($website_rates['e4j.error']);
			return false;
		}
		if (!count($website_rates)) {
			// empty response
			$this->setError('Empty response');
			return false;
		}

		// specify whether the prices are tax included or excluded and adjust the cost accordingly
		$tax_included = VikBooking::ivaInclusa();
		foreach ($website_rates as $idr => $rrplans) {
			foreach ($rrplans as $rpk => $rrplan) {
				$website_rates[$idr][$rpk]['cost'] = $tax_included ? ($rrplan['cost'] + $rrplan['taxes']) : $rrplan['cost'];
				$website_rates[$idr][$rpk]['tax_included'] = $tax_included;
			}
		}

		// check if the rates were returned for the requested room ID
		if (!empty($room_id)) {
			if (!isset($website_rates[$room_id])) {
				// the requested room has got no rates
				$this->setError(JText::_('VCMAPPNORECORDS'));
				return false;
			}
			// we only need the rate plans for the requested room ID
			$website_rates = $website_rates[$room_id];
		}

		$response = new stdClass;
		$response->currency  = VikBooking::getCurrencySymb();
		$response->rateplans = $website_rates;

		// set the response
		$this->response->body = $response;
	}

	/**
	 * App Calculate Room Options Request.
	 *
	 * Method to calculate the options available for the room-party requested.
	 *
	 * @return 	void
	 * 
	 * @since 	1.8.1 (App v1.5)
	 */
	private function calculateRoomOptions()
	{
		// require VBO library
		$this->importVboLib();

		// prepare filters
		$room_id 	= $this->input->getInt('room_id', 0);
		$adults 	= $this->input->getInt('adults', 1);
		$children 	= $this->input->getInt('children', 0);
		$checkin 	= $this->input->getString('checkin', '');
		$checkout 	= $this->input->getString('checkout', '');

		if (empty($room_id)) {
			// the ID of the room is mandatory
			$this->setError(JText::_('VCMAPPRQEMPTY'));
			return false;
		}

		// number of nights of stay
		$num_nights = 0;

		// currency symbol
		$currencysymb = VikBooking::getCurrencySymb();

		$q = "SELECT `idopt` FROM `#__vikbooking_rooms` WHERE `id`=" . $room_id;
		$this->dbo->setQuery($q);
		$data = $this->dbo->loadAssoc();
		if (!$data) {
			$this->setError('Room not found');
			return false;
		}
		$idopt = $data['idopt'];

		$room_options = VikBooking::getRoomOptionals($idopt);
		$room_options = !is_array($room_options) ? array() : $room_options;

		if (!empty($checkin) && !empty($checkout)) {
			// calculate number of nights of stay
			$from_date  = new DateTime($checkin);
			$till_date  = new DateTime($checkout);
			$num_nights = (int)$from_date->diff($till_date)->format("%r%a");
			$num_nights = $num_nights < 1 ? 0 : $num_nights;

			// filter room options by date
			VikBooking::filterOptionalsByDate($room_options, strtotime($checkin), strtotime($checkout));
		}

		// filter the room options by party
		VikBooking::filterOptionalsByParty($room_options, $adults, $children);

		// divide room options from children age rules
		list($room_options, $ageintervals) = VikBooking::loadOptionAgeIntervals($room_options, $adults, $children);

		// make sure the modified variable is still an array
		$room_options = !is_array($room_options) ? array() : $room_options;
		$ageintervals = !is_array($ageintervals) ? array() : $ageintervals;

		// calculate the final cost (before tax, if tax excluded) for each room option, if possible
		foreach ($room_options as $k => $opt) {
			// make sure this option is not only for children, but not prefixed to their age
			if ((int)$opt['ifchildren'] == 1 && $children < 1) {
				// unset this option as it's not suited
				unset($room_options[$k]);
				continue;
			}

			// set property "final_cost" to null
			$room_options[$k]['final_cost'] = null;

			if ((int)$opt['pcentroom']) {
				// we cannot define a final cost when it has to be a percent value
				continue;
			}

			// option starting cost
			$opt_cost = $opt['cost'];

			if ((int)$opt['perday']) {
				if (empty($num_nights)) {
					// stay dates not passed, we cannot calculate the final cost
					continue;
				}
				// cost per night
				$opt_cost *= $num_nights;
			}

			// apply maximum cost
			if (!empty($opt['maxprice']) && $opt['maxprice'] > 0 && $opt_cost > $opt['maxprice']) {
				$opt_cost = $opt['maxprice'];
			}
			
			// apply cost per person
			if ($opt['perperson'] == 1) {
				$opt_cost *= $adults;
			}

			/**
			 * Trigger event to allow third party plugins to apply a custom calculation for the option/extra fee or tax.
			 * 
			 * @since 	1.9.9
			 */
			$custom_calc_booking = ['days' => $num_nights];
			$custom_calc_booking_room = ['adults' => $adults, 'children' => $children];
			$custom_calculation = VBOFactory::getPlatform()->getDispatcher()->filter('onCalculateBookingOptionFeeCost', [$opt_cost, &$opt, $custom_calc_booking, $custom_calc_booking_room]);
			if ($custom_calculation) {
				$opt_cost = (float) $custom_calculation[0];
			}
			
			// update property "final_cost"
			$room_options[$k]['final_cost'] = $opt_cost;
		}

		// check if children should pay depending on their age
		if (count($ageintervals) && $children > 0) {
			for ($ch = 1; $ch <= $children; $ch++) {
				// age intervals may be overridden per child number
				$intervals = explode(';;', (isset($ageintervals['ageintervals_child' . $ch]) ? $ageintervals['ageintervals_child' . $ch] : $ageintervals['ageintervals']));

				// override option name
				$ageintervals['name'] = JText::_('VBSEARCHRESCHILD') . ' #' . $ch;

				// set property "final_cost" to null
				$ageintervals['final_cost'] = null;

				// build age bands available
				$ageintervals['age_bands'] = array();
				foreach ($intervals as $kintv => $intv) {
					if (empty($intv)) {
						continue;
					}
					$intvparts = explode('_', $intv);
					$intvparts[2] = intval($ageintervals['perday']) == 1 && $num_nights ? ($intvparts[2] * $num_nights) : $intvparts[2];
					if (!empty($ageintervals['maxprice']) && $ageintervals['maxprice'] > 0 && $intvparts[2] > $ageintervals['maxprice']) {
						$intvparts[2] = $ageintervals['maxprice'];
					}
					$pricestr = floatval($intvparts[2]) >= 0 ? '+ '.VikBooking::numberFormat($intvparts[2]) : '- '.VikBooking::numberFormat($intvparts[2]);
					if (array_key_exists(3, $intvparts) && strpos($intvparts[3], '%') !== false) {
						// it's a percent value that cannot be calculated at this point
						$pricestr = '%';
					}
					// push age band
					array_push($ageintervals['age_bands'], array(
						'band'  => ($intvparts[0] . ' - ' . $intvparts[1]),
						'price' => $pricestr,
					));
				}

				if (!count($ageintervals['age_bands'])) {
					// we need to break the loop and unset the options per child age
					$ageintervals = array();
					break;
				}

				// push this newly built option into the pool as if it was another room option
				$next_ind = count($room_options);
				$room_options[$next_ind] = $ageintervals;
			}
		}

		// format final cost for each option and determine selection type for this option
		foreach ($room_options as $k => $v) {
			// build property "final_cost_str"
			if (!array_key_exists('final_cost', $v)) {
				$room_options[$k]['final_cost'] = null;
			}
			$room_options[$k]['final_cost_str'] = $room_options[$k]['final_cost'];
			if ($room_options[$k]['final_cost_str'] !== null) {
				$room_options[$k]['final_cost_str'] = VikBooking::numberFormat($room_options[$k]['final_cost_str']);
			}
			// determine selection type
			$room_options[$k]['_selectiontype'] = (int)$v['hmany'] == 1 ? 'multi' : 'single';
			// in case of children age bands, the type must be different
			if (isset($v['age_bands']) && is_array($v['age_bands']) && count($v['age_bands'])) {
				$room_options[$k]['_selectiontype'] = 'age_bands';
			}
		}

		// in case some options have been unset, we need to re-number (reset) the array keys to avoid getting an object encoded rather than an array
		$room_options = array_values($room_options);

		// build the response
		$response = new stdClass;
		$response->options 	 = $room_options;
		$response->tax_rates = $this->loadTaxRates();
		$response->currency  = $currencysymb;

		// set the response
		$this->response->body = $response;
	}

	/**
	 * Load all tax rates defined in VBO.
	 * 
	 * @return 	array
	 * 
	 * @since 	1.8.1
	 */
	private function loadTaxRates()
	{
		$tax_rates = array();

		$q = "SELECT `id`, `name`, `aliq` FROM `#__vikbooking_iva` ORDER BY `aliq` ASC;";
		$this->dbo->setQuery($q);
		$tax_rates = $this->dbo->loadAssocList();
		if ($tax_rates) {
			// if no aliquotes with decimals, make sure to return integers
			foreach ($tax_rates as $k => $v) {
				if ($v['aliq'] > 0 && ($v['aliq'] - abs($v['aliq'])) == 0) {
					// no decimals
					$tax_rates[$k]['aliq'] = (int)$v['aliq'];
				}
			}
		}

		return $tax_rates;
	}

	/**
	 * Attempts to see if any of the available rate plans are assigned to an existing
	 * tax rate, which can be used as default value when creating reservations with a
	 * custom rate. This way, the regular tax policies will be applied.
	 * 
	 * @param 	bool 		$get_record 	if true, the first record found will be returned.
	 * 
	 * @return 	array|int 	0 if not tax rates defined, record ID or record array.
	 * 
	 * @since 	1.8.16
	 */
	private function getDefaultTaxID($get_record = false)
	{
		$q = $this->dbo->getQuery(true);

		$q->select($this->dbo->qn([
			'p.id',
			'p.name',
			'p.idiva',
			't.aliq',
			't.breakdown',
			't.taxcap',
		]));
		$q->from($this->dbo->qn('#__vikbooking_prices', 'p'));
		$q->leftjoin($this->dbo->qn('#__vikbooking_iva', 't') . ' ON ' . $this->dbo->qn('p.idiva') . ' = ' . $this->dbo->qn('t.id'));
		$q->where($this->dbo->qn('p.idiva') . ' > 0');
		$q->where($this->dbo->qn('t.aliq') . ' > 0');

		$this->dbo->setQuery($q, 0, 1);

		$row = $this->dbo->loadAssoc();
		if (!$row) {
			return $get_record ? [] : 0;
		}

		return $get_record ? $row : $row['idiva'];
	}

	/**
	 * Get all tax rates configured in VBO.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.8.1
	 */
	private function getTaxRates()
	{
		// import VBO library
		$this->importVboLib();

		// build the response
		$response = new stdClass;
		$response->tax_rates = $this->loadTaxRates();
		$response->currency  = VikBooking::getCurrencySymb();

		// set the response for VCM
		$this->response->body = $response;
	}

	/**
	 * App Get Tableaux Data Request.
	 * 
	 * Returns an object of information about dates, rooms, bookings.
	 * 
	 * @since 	1.7.4
	 */
	private function getTableauxData()
	{
		// require VBO library
		$this->importVboLib();

		// request filters
		$date_from = $this->input->getString('fdate', '');
		if (empty($date_from)) {
			$date_from = date('Y-m-d');
		}
		$date_to = $this->input->getString('tdate', '');
		$skiplim = $this->input->getInt('skiplim', 0);
		$room_ids = $this->input->getVar('rooms', array());

		// if the date is not formatted correctly, an error is sent and false is returned
		if (count(explode('-', $date_from)) != 3) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}

		if (empty($date_to)) {
			// default end date to end of next month
			$from_info = getdate(strtotime($date_from));
			if (!$from_info) {
				$this->setError(JText::_('VCMAPPINVALIDDATE'));
				return false;
			}
			$next_month_info = getdate(mktime(0, 0, 0, ($from_info['mon'] + 1), $from_info['mday'], $from_info['year']));
			$date_to = date('Y-m-d', mktime(0, 0, 0, $next_month_info['mon'], date('t', $next_month_info[0]), $next_month_info['year']));
		}

		if (!empty($date_to) && count(explode('-', $date_to)) != 3) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}

		// today
		$todaydt = date('Y-m-d');

		// define response object properties
		$response = new stdClass;
		$response->dates  = array();
		$response->months = array();
		$response->rooms  = array();

		// build list of day objects
		$from_info = getdate(strtotime($date_from));
		$to_info   = getdate(strtotime($date_to));

		if (!$skiplim) {
			// by default, we limit the request to 3 months
			$max_ts = mktime(0, 0, 0, ($from_info['mon'] + 3), $from_info['mday'], $from_info['year']);
			if ($to_info[0] > $max_ts) {
				// limit reached, set end date information to 3 months ahead from start
				$to_info = getdate($max_ts);
			}
		}

		// get timestamps for the query
		$fromts = $from_info[0];
		$tots   = mktime(23, 59, 59, $to_info['mon'], $to_info['mday'], $to_info['year']);

		// get all rooms from filters
		$rooms = array();
		$q = "SELECT `id`,`name`,`units`,`params` FROM `#__vikbooking_rooms` WHERE " . (count($room_ids) ? "`id` IN (" . implode(', ', $room_ids) . ")" : "`avail`=1") . " ORDER BY `name` ASC;";
		$this->dbo->setQuery($q);
		$all = $this->dbo->loadAssocList();
		if ($all) {
			foreach ($all as $r) {
				$rooms[$r['id']] = $r;
			}
		}
		if (!count($rooms)) {
			// no rooms found
			$this->setError(JText::_('VCMAPPNORECORDS'));
			return false;
		}

		// get all occupied dates for these rooms
		$rooms_busy = array();
		$q = "SELECT `b`.*,`ob`.`idorder`,`o`.`custdata`,`o`.`status`,`o`.`totpaid`,`o`.`roomsnum`,`o`.`total`,`o`.`country`,`o`.`colortag`,`oc`.`idcustomer`,`c`.`first_name`,`c`.`last_name`,
			(SELECT GROUP_CONCAT(`or`.`roomindex` SEPARATOR ';') FROM `#__vikbooking_ordersrooms` AS `or` WHERE `or`.`idorder`=`ob`.`idorder`) AS `indexes`,
			(SELECT GROUP_CONCAT(`or`.`idroom` SEPARATOR ';') FROM `#__vikbooking_ordersrooms` AS `or` WHERE `or`.`idorder`=`ob`.`idorder`) AS `roomids` 
			FROM `#__vikbooking_busy` AS `b` 
			LEFT JOIN `#__vikbooking_ordersbusy` AS `ob` ON `b`.`id`=`ob`.`idbusy` 
			LEFT JOIN `#__vikbooking_orders` AS `o` ON `ob`.`idorder`=`o`.`id` 
			LEFT JOIN `#__vikbooking_customers_orders` AS `oc` ON `ob`.`idorder`=`oc`.`idorder` 
			LEFT JOIN `#__vikbooking_customers` AS `c` ON `oc`.`idcustomer`=`c`.`id` 
			WHERE `b`.`idroom` IN (" . implode(', ', array_keys($rooms)) . ") AND (`b`.`checkin`>={$fromts} OR `b`.`checkout`>={$fromts}) AND (`b`.`checkin`<={$tots} OR `b`.`checkout`<={$fromts}) AND `o`.`status`='confirmed' 
			ORDER BY `b`.`checkin` ASC, `ob`.`idorder` ASC;";
		$this->dbo->setQuery($q);
		$busy = $this->dbo->loadAssocList();
		if ($busy) {
			foreach ($busy as $b) {
				if (!isset($rooms_busy[$b['idroom']])) {
					$rooms_busy[$b['idroom']] = array();
				}
				array_push($rooms_busy[$b['idroom']], $b);
			}
		}

		/**
		 * Check and get the next festivities.
		 * 
		 * @since 	VBO 1.13.5
		 */
		$festivities = array();
		if (method_exists('VikBooking', 'getFestivitiesInstance')) {
			$fests = VikBooking::getFestivitiesInstance();
			if ($fests->shouldCheckFestivities()) {
				$fests->storeNextFestivities();
			}
			$festivities = $fests->loadFestDates(date('Y-m-d', $fromts), date('Y-m-d', $tots));
		}

		/**
		 * Load room day notes.
		 * 
		 * @since 	VBO 1.13.5
		 */
		$rdaynotes = array();
		if (method_exists('VikBooking', 'getCriticalDatesInstance')) {
			$rdaynotes = VikBooking::getCriticalDatesInstance()->loadRoomDayNotes(date('Y-m-d', $fromts), date('Y-m-d', $tots));
		}
		//

		// parse all dates in between
		$months_days = array();
		$now_info = $from_info;
		while ($now_info[0] <= $to_info[0]) {
			// build day object
			$ymd = date('Y-m-d', $now_info[0]);
			$day = new stdClass;
			$day->ymd 	= $ymd;
			$day->mon 	= $now_info['mon'];
			$day->mday 	= $now_info['mday'];
			$day->year 	= $now_info['year'];
			$day->wday 	= $now_info['wday'];
			// check if day has fests
			$day->fests = isset($festivities[$ymd]) && isset($festivities[$ymd]['festinfo']) ? $festivities[$ymd]['festinfo'] : array();

			// push day
			array_push($response->dates, $day);

			// update month info
			$mkey = $now_info['mon'] . '_' . $now_info['year'];
			if (!isset($months_days[$mkey])) {
				$months_days[$mkey] = 0;
			}
			$months_days[$mkey]++;

			// next day
			$now_info = getdate(mktime(0, 0, 0, $now_info['mon'], ($now_info['mday'] + 1), $now_info['year']));
		}

		// set months infos
		foreach ($months_days as $mkey => $totdays) {
			$mkeyparts = explode('_', $mkey);
			// prepare month object
			$mon = new stdClass;
			$mon->mon = $mkeyparts[0];
			$mon->year = $mkeyparts[1];
			$mon->days = $totdays;

			// push month object
			array_push($response->months, $mon);
		}

		// parse rooms and dates
		$rooms_features_map = array();
		$rooms_features_bookings = array();
		foreach ($rooms as $rid => $rdata) {
			// distinctive features
			$room_params = !empty($rdata['params']) ? json_decode($rdata['params'], true) : array();
			if (is_array($room_params) && array_key_exists('features', $room_params) && count($room_params['features']) > 0) {
				$rooms_features_map[$rid] = array();
				foreach ($room_params['features'] as $rind => $rfeatures) {
					foreach ($rfeatures as $fname => $fval) {
						if (strlen($fval)) {
							$rooms_features_map[$rid][$rind] = '#'.$fval;
							break;
						}
					}
				}
				if (!count($rooms_features_map[$rid])) {
					unset($rooms_features_map[$rid]);
				}
			}
			//

			// define room object
			$room_info = new stdClass;
			$room_info->id 		= $rdata['id'];
			$room_info->name 	= $rdata['name'];
			$room_info->units 	= $rdata['units'];
			$room_info->daydata = array();

			// add day data to the current room object
			$now_info = $from_info;
			$bookbuffer = array();
			$prevbuffer = array();
			$positions = array();
			$newmonth = 0;
			while ($now_info[0] <= $to_info[0]) {
				// current date and new month
				$ymd = date('Y-m-d', $now_info[0]);
				$is_new_month = (!empty($newmonth) && $now_info['mon'] != $newmonth);
				$newmonth = $now_info['mon'];
				
				// build room day object
				$roomday = new stdClass;
				$roomday->ymd 				= $ymd;
				$roomday->is_new_month 		= $is_new_month;
				$roomday->is_today 			= ($ymd == $todaydt);
				$roomday->hotdates_list 	= array();
				$roomday->hotdates_summary 	= '';
				$roomday->empty_spots 		= array();
				$roomday->bookings 			= array();

				/**
				 * Critical (hot) dates defined at room-day level, or for any sub-unit.
				 */
				$cell_rdnotes 	 = '';
				$rdnkeys_lookup  = range(0, $rdata['units']);
				$rdaynote_keyids = array();
				// find room-day note keys with some notes (room-day level or subroom-day level)
				foreach ($rdnkeys_lookup as $lookup_index) {
					$rdaynote_keyid = $ymd . '_' . $rdata['id'] . '_' . $lookup_index;
					if (isset($rdaynotes[$rdaynote_keyid])) {
						// push associative index with notes
						$rdaynote_keyids[$lookup_index] = $rdaynote_keyid;
					}
				}
				if (count($rdaynote_keyids)) {
					/**
					 * Some notes exist for this combination of date, room ID and subunit.
					 * Try to populate the notes for this room-day cell
					 * only if the previous day does not have the same note.
					 * Just the first readable room-day cell should have notes.
					 */
					$notes_titles = array();
					$yesterday_ts = mktime(0, 0, 0, $now_info['mon'], ($now_info['mday'] - 1), $now_info['year']);
					$yesterday_ymd = date('Y-m-d', $yesterday_ts);
					// loop through all the keys with notes
					foreach ($rdaynote_keyids as $lookup_index => $rdaynote_keyid) {
						$yesterday_keyid = $yesterday_ymd . '_' . $rdata['id'] . '_' . $lookup_index;
						foreach ($rdaynotes[$rdaynote_keyid]['info'] as $today_note) {
							// only manual (custom) room-day notes will be displayed
							$display_note = ($today_note->type == 'custom');
							if (isset($rdaynotes[$yesterday_keyid])) {
								// make sure the same note is not present for yesterday
								foreach ($rdaynotes[$yesterday_keyid]['info'] as $yesterday_note) {
									if ($today_note->type == $yesterday_note->type && $today_note->name == $yesterday_note->name) {
										// same note available also for yesterday
										$display_note = false;
										break;
									}
								}
							}
							
							// always push this note in the list for today even if it's available for yesterday
							array_push($roomday->hotdates_list, $today_note);
							
							if ($display_note) {
								// push just the name of the note for the summary only on the first day
								array_push($notes_titles, $today_note->name);
							}
						}
					}
					// separate all notes (if any) with comma
					$cell_rdnotes = implode(', ', $notes_titles);
					//
				}
				if (!empty($cell_rdnotes)) {
					// set critical dates summary string
					$roomday->hotdates_summary = $cell_rdnotes;
				}

				// check rooms bookings
				$room_bookings = array();
				if (isset($rooms_busy[$rid])) {
					foreach ($rooms_busy[$rid] as $b) {
						$tmpone = getdate($b['checkin']);
						$ritts = mktime(0, 0, 0, $tmpone['mon'], $tmpone['mday'], $tmpone['year']);
						$tmptwo = getdate($b['checkout']);
						$conts = mktime(0, 0, 0, $tmptwo['mon'], $tmptwo['mday'], $tmptwo['year']);
						$b['basefrom'] = $ritts;
						$b['baseto'] = $conts;
						if ($now_info[0] >= $ritts && $now_info[0] <= $conts) {
							array_push($room_bookings, $b);
						}
					}
					/**
					 * By default, we attempt to sort the bookings for this day by room index.
					 * 
					 * @since 	1.13.5
					 */
					if (count($room_bookings)) {
						$rindexes_map = array();
						foreach ($room_bookings as $rbk => $rbval) {
							$rindexes_map[$rbk] = isset($rbval['indexes']) ? $rbval['indexes'] : '';
						}
						asort($rindexes_map);
						$room_bookings_copy = array();
						foreach ($rindexes_map as $rbk => $rbval) {
							array_push($room_bookings_copy, $room_bookings[$rbk]);
						}
						$room_bookings = $room_bookings_copy;
					}
					//
				}
				if (count($room_bookings) && count($bookbuffer)) {
					// sort bookings according to the previous map
					$newbookings = array();
					foreach ($bookbuffer as $oid) {
						foreach ($room_bookings as $kb => $rbook) {
							if ($oid == $rbook['idorder']) {
								// get this key first
								$newbookings[$kb] = $rbook;
								// do not break the loop as there could be multiple idorder, just continue
								continue;
							}
						}
					}
					if (count($newbookings)) {
						// merge array by keys by unsetting double keys from second array
						$room_bookings = $newbookings + $room_bookings;
					}
					// copy buffer before reset to see whether this is the first cell displayed for the booking
					$prevbuffer = $bookbuffer;
					// reset buffer to fill the current day bookings
					$bookbuffer = array();
				}
				$indexpos = 0;
				$empty_spots = array();
				foreach ($room_bookings as $k => $rbook) {
					$booking_type = 'stay';
					if ($rbook['basefrom'] == $now_info[0]) {
						$booking_type = 'checkin';
					} elseif ($rbook['baseto'] == $now_info[0]) {
						$booking_type = 'checkout';
					}
					$is_short_stay = false;
					if (ceil(($rbook['baseto'] - $rbook['basefrom']) / 86400) < 2) {
						$is_short_stay = true;
					}
					//check position
					$pos = $indexpos;
					if (isset($positions[$rbook['idorder']]) && $indexpos < $positions[$rbook['idorder']]) {
						// print empty blocks to give the right position to this booking
						$pos = $indexpos.'-'.$positions[$rbook['idorder']];
						$looplim = ($positions[$rbook['idorder']] - $indexpos);
						for ($i = 0; $i < $looplim; $i++) { 
							$indexpos++;
							// push empty spot
							array_push($empty_spots, $indexpos);
						}
						$pos .= '-'.$indexpos;
					}
					// push position
					if (!isset($positions[$rbook['idorder']])) {
						$positions[$rbook['idorder']] = $indexpos;
					}
					// push booking to the buffer for the ordering in the next loop
					array_push($bookbuffer, $rbook['idorder']);

					// set empty spots for the room day object
					$roomday->empty_spots = $empty_spots;

					// build room-day booking object for the response
					$rday_booking = new stdClass;
					$rday_booking->id 			= $rbook['idorder'];
					$rday_booking->type 		= $booking_type;
					$rday_booking->short_stay 	= $is_short_stay;
					$rday_booking->empty_spots 	= $empty_spots;
					$rday_booking->summary 		= '';
					
					// cell content (booking summary)
					$cellcont = '';
					if (!in_array($rbook['idorder'], $prevbuffer)) {
						// first time we print the details for this booking - compose the content of the element
						
						// distinctive features
						if (!empty($rbook['indexes']) && isset($rooms_features_map[$rid])) {
							$bookindexes = explode(';', $rbook['indexes']);
							$roomindexes = explode(';', $rbook['roomids']);
							if (!isset($rooms_features_bookings[$rid.'_'.$rbook['idorder']])) {
								// the index to read of the feature depending on how many times this booking was printed (in case of multiple same rooms in one booking)
								$rooms_features_bookings[$rid.'_'.$rbook['idorder']] = 0;
							} else {
								// increment index for this room booking
								$rooms_features_bookings[$rid.'_'.$rbook['idorder']]++;
							}
							// seek for the index occurrence of this room in the list of rooms booked
							$count_pos = -1;
							$room_pos  = null;
							foreach ($roomindexes as $rk => $rv) {
								if ((int)$rv == (int)$rid) {
									$count_pos++;
									if ($count_pos == $rooms_features_bookings[$rid.'_'.$rbook['idorder']]) {
										$room_pos = $rk;
										break;
									}
								}
							}
							$nowfeatindex = isset($bookindexes[$room_pos]) ? $room_pos : null;
							if (!is_null($room_pos) && isset($rooms_features_map[$rid][$bookindexes[$nowfeatindex]])) {
								// get this room feature
								$cellcont .= $rooms_features_map[$rid][$bookindexes[$nowfeatindex]] . ' ';
							}
						}
						// customer details
						if (!empty($rbook['first_name']) || !empty($rbook['last_name'])) {
							// customer record
							$cellcont .= $rbook['first_name'] . ' ' . $rbook['last_name'];
						} else {
							// parse the customer data string
							$custdata_parts = explode("\n", $rbook['custdata']);
							$enoughinfo = false;
							if (count($custdata_parts) > 2 && strpos($custdata_parts[0], ':') !== false && strpos($custdata_parts[1], ':') !== false) {
								// get the first two fields
								$custvalues = array();
								foreach ($custdata_parts as $custdet) {
									if (strlen($custdet) < 1) {
										continue;
									}
									$custdet_parts = explode(':', $custdet);
									if (count($custdet_parts) >= 2) {
										unset($custdet_parts[0]);
										array_push($custvalues, trim(implode(':', $custdet_parts)));
									}
									if (count($custvalues) > 1) {
										break;
									}
								}
								if (count($custvalues) > 1) {
									$enoughinfo = true;
									$cellcont .= implode(' ', $custvalues);
								}
							}
							if (!$enoughinfo) {
								$cellcont .= $rbook['idorder'];
							}
						}
					}

					// update summary
					$rday_booking->summary = $cellcont;

					// push room-day booking object to the pool
					array_push($roomday->bookings, $rday_booking);
					
					// increase the positioning index
					$indexpos++;
				}

				// push room-day object to room day data array
				array_push($room_info->daydata, $roomday);

				// next day
				$now_info = getdate(mktime(0, 0, 0, $now_info['mon'], ($now_info['mday'] + 1), $now_info['year']));
			}

			// push room object
			array_push($response->rooms, $room_info);
		}

		// set the final response
		$this->response->body = $response;
	}

	/**
	 * App Remove Fest Date Request.
	 * 
	 * @since 	1.7.4
	 */
	private function removeFestivity()
	{
		// require VBO library
		$this->importVboLib();

		// request filters
		$dt 	= $this->input->getString('dt', '');
		$ind 	= $this->input->getInt('ind', 0);
		$type 	= $this->input->getString('type', '');
		$type 	= empty($type) ? 'custom' : $type;
		if (empty($dt) || !strtotime($dt)) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}

		if (!method_exists('VikBooking', 'getFestivitiesInstance')) {
			$this->setError('Vik Booking must be updated');
			return false;
		}

		$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".(int)VikChannelManagerConfig::MOBILEAPP." LIMIT 1;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() < 1) {
			$this->setError(JText::_('VCMAPPCHREQREFUSED'));
			return false;
		}

		$fests  = VikBooking::getFestivitiesInstance();
		$result = $fests->deleteFestivity($dt, $ind, $type);
		if (!$result) {
			$this->setError('Could not remove record');
			return false;
		}

		// set the response to just a success string
		$this->response->body = 'e4j.ok';
	}

	/**
	 * App Add Fest Date Request.
	 * 
	 * @since 	1.7.4
	 */
	function createFestivity()
	{
		// require VBO library
		$this->importVboLib();

		$dt 	= $this->input->getString('dt', '');
		$type 	= $this->input->getString('type', '');
		$type 	= empty($type) ? 'custom' : $type;
		$name 	= $this->input->getString('name', '');
		$descr 	= $this->input->getString('descr', '');
		if (empty($name) || empty($dt) || !strtotime($dt)) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}

		$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".(int)VikChannelManagerConfig::MOBILEAPP." LIMIT 1;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() < 1) {
			$this->setError(JText::_('VCMAPPCHREQREFUSED'));
			return false;
		}

		// build fest array
		$new_fest = array(
			'trans_name' => $name
		);

		$fests  = VikBooking::getFestivitiesInstance();
		$result = $fests->storeFestivity($dt, $new_fest, $type, $descr);
		if (!$result) {
			$this->setError('Could not create record');
			return false;
		}

		// reload all festivities for this day for the AJAX response
		$all_fests = $fests->loadFestDates($dt, $dt);
		foreach ($all_fests as $k => $v) {
			/**
			 * We expect just one record to be returned due to the from/to date limit passed to loadFestDates().
			 * Set the response to the new festivities array for this date and return true.
			 */
			$this->response->body = $v;
			return true;
		}

		// no fests found even after storing it
		$this->setError('No records available');
		return false;
	}

	/**
	 * App Remove Room Day Note Request.
	 * 
	 * @since 	1.7.4
	 */
	private function removeRoomdaynote()
	{
		// require VBO library
		$this->importVboLib();

		$dt 	 = $this->input->getString('dt', '');
		$idroom  = $this->input->getInt('idroom', 0);
		$subunit = $this->input->getInt('subunit', 0);
		$type 	 = $this->input->getString('type', '');
		$type 	 = empty($type) ? 'custom' : $type;
		$ind 	 = $this->input->getInt('ind', 0);
		if (empty($dt) || !strtotime($dt)) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}

		if (!method_exists('VikBooking', 'getCriticalDatesInstance')) {
			$this->setError('Vik Booking must be updated');
			return false;
		}

		$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".(int)VikChannelManagerConfig::MOBILEAPP." LIMIT 1;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() < 1) {
			$this->setError(JText::_('VCMAPPCHREQREFUSED'));
			return false;
		}

		$notes  = VikBooking::getCriticalDatesInstance();
		$result = $notes->deleteDayNote($ind, $dt, $idroom, $subunit, $type);
		if (!$result) {
			$this->setError('Could not remove record');
			return false;
		}

		// set the response to just a success string
		$this->response->body = 'e4j.ok';
	}

	/**
	 * App Create Room Day Note Request.
	 * 
	 * @since 	1.7.4
	 */
	private function createRoomdaynote()
	{
		// require VBO library
		$this->importVboLib();

		$dt 	 = $this->input->getString('dt', '');
		$idroom  = $this->input->getInt('idroom', 0);
		$subunit = $this->input->getInt('subunit', 0);
		$type 	 = $this->input->getString('type', '');
		$type 	 = empty($type) ? 'custom' : $type;
		$name 	 = $this->input->getString('name', '');
		$descr 	 = $this->input->getString('descr', '');
		$cdays   = $this->input->getInt('cdays', 0);
		$cdays 	 = $cdays < 0 ? 0 : $cdays;
		$cdays 	 = $cdays > 365 ? 365 : $cdays;
		if (empty($idroom) || empty($dt) || !strtotime($dt)) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}

		if (!method_exists('VikBooking', 'getCriticalDatesInstance')) {
			$this->setError('Vik Booking must be updated');
			return false;
		}

		$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".(int)VikChannelManagerConfig::MOBILEAPP." LIMIT 1;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() < 1) {
			$this->setError(JText::_('VCMAPPCHREQREFUSED'));
			return false;
		}

		// reload end date
		$end_date = $dt;
		
		// build critical date object
		$new_note = array(
			'name'  => $name,
			'type'  => $type,
			'descr' => $descr,
		);

		// get object
		$notes  = VikBooking::getCriticalDatesInstance();

		// store the notes for all consecutive dates
		for ($i = 0; $i <= $cdays; $i++) {
			$store_dt = $dt;
			if ($i > 0) {
				$dt_info = getdate(strtotime($store_dt));
				$store_dt = date('Y-m-d', mktime(0, 0, 0, $dt_info['mon'], ($dt_info['mday'] + $i), $dt_info['year']));
				$end_date = $store_dt;
			}
			$result = $notes->storeDayNote($new_note, $store_dt, $idroom, $subunit);
			if (!$result) {
				$this->setError('Could not create record');
				return false;
			}
		}

		// reload all room day notes for this day for the AJAX response
		$all_notes = $notes->loadRoomDayNotes($dt, $end_date, $idroom, $subunit);

		if (!$all_notes || !count($all_notes)) {
			// no notes found even after storing it
			$this->setError('No records available');
			return false;
		}

		// set the response to all room day notes for these dates/rooms
		$this->response->body = $all_notes;
	}

	/**
	 * App Load Booking History Request.
	 * 
	 * @since 	1.8.1
	 */
	private function loadBookingHistory()
	{
		// require VBO library
		$this->importVboLib();

		$bid = $this->input->getInt('bid', 0);
		if (empty($bid)) {
			$this->setError(JText::_('VCMAPPEMPTYBOOKINGID'));
			return false;
		}

		// load VBO back-end lang
		$lang = JFactory::getLanguage();
		if (VCMPlatformDetection::isWordPress()) {
			$lang->load('com_vikbooking', VIKBOOKING_LANG, $lang->getTag(), true);
		} else {
			$lang->load('com_vikbooking', JPATH_ADMINISTRATOR, $lang->getTag(), true);
		}

		// load booking history records
		$history_obj = VikBooking::getBookingHistoryInstance();
		$history_obj->setBid($bid);
		$history = $history_obj->loadHistory();

		if (!is_array($history) || !count($history)) {
			$this->setError('No records available');
			return false;
		}

		// get currency name
		$currencyname = VikBooking::getCurrencyName();

		// build response object
		$response = new stdClass;
		$response->cols = array(
			JText::_('VBOBOOKHISTORYLBLTYPE'),
			JText::_('VBOBOOKHISTORYLBLDATE'),
			JText::_('VBOBOOKHISTORYLBLDESC'),
			JText::_('VBOBOOKHISTORYLBLTPAID'),
			JText::_('VBOBOOKHISTORYLBLTOT'),
			'Code',
		);
		$response->rows = array();
		foreach ($history as $hist) {
			$hdescr = strpos($hist['descr'], '<') !== false ? strip_tags($hist['descr']) : $hist['descr'];
			// push row
			array_push($response->rows, array(
				$history_obj->validType($hist['type'], true),
				JHtml::_('date', $hist['dt']),
				$hdescr,
				$currencyname . ' ' . VikBooking::numberFormat($hist['totpaid']),
				$currencyname . ' ' . VikBooking::numberFormat($hist['total']),
				$hist['type'],
			));
		}

		// set the response object
		$this->response->body = $response;
	}

	/**
	 * App Load Booking Pax Data (check-in details) Request.
	 * 
	 * @since 	1.8.1
	 */
	private function loadBookingPaxData()
	{
		// require VBO library
		$this->importVboLib();

		$bid = $this->input->getInt('bid', 0);
		if (empty($bid)) {
			$this->setError(JText::_('VCMAPPEMPTYBOOKINGID'));
			return false;
		}

		// load booked rooms data
		$q = "SELECT `or`.`idroom`,`or`.`adults`,`or`.`children`,`or`.`roomindex`,`r`.`id` AS `r_reference_id`,`r`.`name`,`r`.`img` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`=" . $bid . " AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
		$this->dbo->setQuery($q);
		$orderrooms = $this->dbo->loadAssocList();
		if (!$orderrooms) {
			$this->setError(JText::_('VCMAPPSELBOOKINGUNAV'));
			return false;
		}

		/**
		 * Load VBO back-end lang only with Joomla, as on WordPress
		 * we no longer need to split front-end and back-end langs.
		 */
		$lang = JFactory::getLanguage();
		if (VCMPlatformDetection::isJoomla()) {
			$lang->load('com_vikbooking', JPATH_ADMINISTRATOR, $lang->getTag(), true);
		}

		// get pax fields (front)
		list($pax_fields, $pax_fields_attributes) = VikBooking::getPaxFields(true);

		// get the list of back-end pax fields according to settings
		list($pax_fields_back, $pax_fields_attributes_back) = VikBooking::getPaxFields();

		// get pax data
		$cpin = VikBooking::getCPinIstance();
		$cpin->is_admin = true;
		$customer = $cpin->getCustomerFromBooking($bid);
		if (count($customer) && !empty($customer['pax_data'])) {
			if (is_string($customer['pax_data'])) {
				$customer['pax_data'] = json_decode($customer['pax_data'], true);
				$customer['pax_data'] = is_array($customer['pax_data']) ? $customer['pax_data'] : [];
			}
		}

		if (!count($customer)) {
			$this->setError('No customers assigned to this reservation');
			return false;
		}

		// build admin check-in comments
		$checkin_comments = null;
		$q = "SELECT `comments` FROM `#__vikbooking_customers_orders` WHERE `idorder`=" . $bid . ";";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows()) {
			$checkin_comments = $this->dbo->loadResult();
		}

		// build rooms information and proper pax field labels
		$rooms = array();
		$pax_data = array();
		foreach ($customer['pax_data'] as $k => $room_pax) {
			// prepare container for this room-party
			$pax_data[$k] = array();
			// build room booked info
			$room_booked = new stdClass;
			$room_booked->id = isset($orderrooms[$k]) ? $orderrooms[$k]['r_reference_id'] : null;
			$room_booked->name = isset($orderrooms[$k]) ? $orderrooms[$k]['name'] : null;
			$room_booked->img = isset($orderrooms[$k]) ? $orderrooms[$k]['img'] : null;
			$room_booked->adults = isset($orderrooms[$k]) ? $orderrooms[$k]['adults'] : null;
			$room_booked->children = isset($orderrooms[$k]) ? $orderrooms[$k]['children'] : null;
			// push room booked object
			array_push($rooms, $room_booked);
			foreach ($room_pax as $pax_num => $pax_info) {
				$paxroom_guest = array();
				foreach ($pax_info as $field => $val) {
					// make sure to make the value an array, if necessary
					$type = 'string';
					if (isset($pax_fields_attributes[$field]) && $pax_fields_attributes[$field] == 'file' && is_string($val) && !empty($val)) {
						$parsed_value = explode('|', $val);
						$type = strpos($parsed_value[0], 'http') !== false ? 'url' : $type;
					} else {
						$parsed_value = array($val);
					}
					if (isset($pax_fields_back[$field])) {
						// set translation value for pax field
						$field = $pax_fields_back[$field];
					} elseif (isset($pax_fields[$field])) {
						// set translation value for pax field
						$field = $pax_fields[$field];
					}
					foreach ($parsed_value as $pvalue) {
						$paxfield_obj = new stdClass;
						$paxfield_obj->key = $field;
						$paxfield_obj->value = $pvalue;
						$paxfield_obj->type = $type;
						// push field-value object
						array_push($paxroom_guest, $paxfield_obj);
					}
				}
				// push pax room object
				array_push($pax_data[$k], $paxroom_guest);
			}
		}

		// set response
		$response = new stdClass;
		$response->rooms = $rooms;
		$response->pax_data = $pax_data;
		$response->comments = $checkin_comments;

		$this->response->body = $response;
	}

	/**
	 * App Invoices List Request
	 *
	 * Method to retrieve a list of invoices according to some parameters.
	 * The number of invoices per request is limited to 20 by default.
	 * Pagination supported if there are more invoices than the lim.
	 *
	 * @return 	void
	 * 
	 * @since 	1.8.1
	 */
	private function appInvoicesList()
	{
		$response = new stdClass();

		/**
		 * Clean up temporary files used to produce a URL to an
		 * electronic invoice so that it could be downloaded by the App.
		 */
		$tmp_einvoice = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tmp_einvoice.xml';
		if (is_file($tmp_einvoice)) {
			@unlink($tmp_einvoice);
		}

		// get request filters
		$requestDateFrom = $this->input->getString('fdate', '');
		$requestDateTo = $this->input->getString('tdate', '');
		$requestDateType = $this->input->getString('date_type', 'invoice_date');
		$requestKeyword = $this->input->getString('keyword', '');
		$requestLim = $this->input->getInt('lim', 20);
		$requestPage = $this->input->getInt('page', 0);
		$requestIds = $this->input->getVar('ids', array());
		$limStart = $requestPage * $requestLim;
		$limStart = $limStart >= 0 ? $limStart : 0;

		// if the date is not formatted correctly, an error is sent and false is returned
		if (empty($requestKeyword) && !empty($requestDateFrom) && count(explode('-', $requestDateFrom)) != 3) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}
		if (empty($requestKeyword) && !empty($requestDateTo) && count(explode('-', $requestDateTo)) != 3) {
			$this->setError(JText::_('VCMAPPINVALIDDATE'));
			return false;
		}

		// getting the midnight (both the 00:00 and the 23:59) timestamp for the selected date
		$date_start_ts 	= !empty($requestDateFrom) ? strtotime($requestDateFrom) : 0;
		$date_to_info 	= getdate((!empty($requestDateTo) ? strtotime($requestDateTo) : $date_start_ts));
		$date_end_ts 	= mktime(23, 59, 59, $date_to_info['mon'], $date_to_info['mday'], $date_to_info['year']);
		$invoices 		= array();
		$invoicesData	= array();
		$clauses 		= array();
		$keyclauses 	= array();
		$total_invoices = 0;
		$current_page 	= 0;

		$bids_filter = array();
		if (is_array($requestIds) && count($requestIds) && !empty($requestIds[0])) {
			foreach ($requestIds as $bid) {
				$bids_filter[] = intval($bid);
			}
			$clauses[] = "(`o`.`id` IN (" . implode(', ', $bids_filter) . ") OR `i`.`id` IN (" . implode(', ', $bids_filter) . "))";
		}
		
		$dtype = "`i`.`for_date`";
		$otype = "`i`.`id` DESC";
		if ($requestDateType == 'creation_date') {
			$dtype = "`i`.`created_on`";
		} elseif ($requestDateType == 'booking_date') {
			$dtype = "`o`.`ts`";
		}
		if (!empty($requestDateFrom) && !count($bids_filter)) {
			// when filtering by IDs do not use dates filters
			$clauses[] = "$dtype >= $date_start_ts";
			$clauses[] = "$dtype <= $date_end_ts";
		}

		// search invoices by keyword
		if (!empty($requestKeyword)) {
			// compose keywords clauses
			array_push($keyclauses, "`i`.`number` LIKE " . $this->dbo->quote('%' . $requestKeyword . '%'));

			if (ctype_digit($requestKeyword)) {
				// numeric filter should seek over the booking ID, invoice ID, phone number
				array_push($keyclauses, "`i`.`id`=" . $this->dbo->quote($requestKeyword));
				array_push($keyclauses, "`o`.`id`=" . $this->dbo->quote($requestKeyword));
				array_push($keyclauses, "`o`.`phone` LIKE " . $this->dbo->quote('%' . $requestKeyword));
			}

			if (strpos($requestKeyword, '@') !== false) {
				// probably an email address was provided
				array_push($keyclauses, "`o`.`custmail` LIKE " . $this->dbo->quote('%' . $requestKeyword . '%'));
				array_push($keyclauses, "`i`.`emailed_to`=" . $this->dbo->quote($requestKeyword));
			}

			// always seek for customer name, company name and VAT
			array_push($keyclauses, "CONCAT_WS(' ', `c`.`first_name`, `c`.`last_name`) LIKE " . $this->dbo->quote('%' . $requestKeyword . '%'));
			array_push($keyclauses, "`c`.`company` LIKE " . $this->dbo->quote('%' . $requestKeyword . '%'));
			array_push($keyclauses, "`c`.`vat`=" . $this->dbo->quote($requestKeyword));

			// merge keyword clauses to all clauses
			array_push($clauses, '(' . implode(' OR ', $keyclauses) . ')');
		}

		// load all invoices according to the input parameters
		$q = "SELECT SQL_CALC_FOUND_ROWS `i`.`id` AS `invoice_id`, `i`.`number`, `i`.`file_name`, `i`.`idorder`, `i`.`idcustomer`, `i`.`created_on`, `i`.`for_date`, `i`.`emailed`, 
			`i`.`emailed_to`, `i`.`rawcont`, `o`.`id`, `o`.`custdata`, `o`.`ts`, `o`.`status`, `o`.`days`, `o`.`checkin`, `o`.`checkout`, 
			`o`.`custmail`, `o`.`roomsnum`, `o`.`country`, `o`.`phone`, `o`.`closure`, `o`.`idorderota`, `o`.`channel`, 
			(
				SELECT CONCAT_WS(' ',`or`.`t_first_name`,`or`.`t_last_name`) 
				FROM `#__vikbooking_ordersrooms` AS `or` 
				WHERE `or`.`idorder` = `o`.`id` LIMIT 1
			) AS `nominative`,
			(
				SELECT CONCAT_WS(' ',`c`.`first_name`,`c`.`last_name`) 
				FROM `#__vikbooking_customers` AS `c` 
				WHERE `i`.`idcustomer` = `c`.`id` LIMIT 1
			) AS `cust_nominative`,
			(
				SELECT SUM(`or`.`adults`) 
				FROM `#__vikbooking_ordersrooms` AS `or` 
				WHERE `or`.`idorder` = `o`.`id`
			) AS `tot_adults`,
			(
				SELECT SUM(`or`.`children`) 
				FROM `#__vikbooking_ordersrooms` AS `or` 
				WHERE `or`.`idorder` = `o`.`id`
			) AS `tot_children`, 
			(
				SELECT `xml` 
				FROM `#__vikbooking_einvoicing_data` AS `ei` 
				WHERE `ei`.`idorder` = `i`.`idorder` AND `ei`.`obliterated`=0
				ORDER BY `ei`.`id` DESC LIMIT 1
			) AS `einvoice_xml` 
			FROM `#__vikbooking_invoices` AS `i` 
			LEFT JOIN `#__vikbooking_orders` AS `o` ON `o`.`id`=`i`.`idorder` 
			LEFT JOIN `#__vikbooking_customers` AS `c` ON `c`.`id`=`i`.`idcustomer` 
			WHERE " . implode(' AND ', $clauses) . " 
			ORDER BY $otype";

		$this->dbo->setQuery($q, $limStart, $requestLim);
		$invoicesData = $this->dbo->loadAssocList();
		if ($invoicesData) {
			$this->dbo->setQuery('SELECT FOUND_ROWS();');
			$total_invoices = (int)$this->dbo->loadResult();
			$total_pages = ceil($total_invoices / $requestLim);
			if ($total_invoices > $requestLim) {
				if (($requestPage + 1) < $total_pages) {
					// pagination starts from 0
					$current_page = $requestPage;
				} elseif (($requestPage + 1) == $total_pages) {
					// we are on the last page (-1)
					$current_page = -1;
				}
			} else {
				// we are on the only and last page (-1)
				$current_page = -1;
			}
		} else {
			// no results and no other pages
			$current_page = -1;
		}

		foreach ($invoicesData as $book) {
			if ((int)$book['closure'] > 0) {
				// when room closed, just force the customer name to 'room closed'
				$book['nominative'] = JText::_('VCMAPPROOMCLOSED');
			}
			$booking_info = new stdClass;
			$booking_info->id = !empty($book['id']) ? (int)$book['id'] : null;
			$booking_info->invoice_id = $book['invoice_id'];
			$booking_info->number = $book['number'];
			$booking_info->uri = VBO_SITE_URI .  'helpers/invoices/generated/' . $book['file_name'];
			$booking_info->created_on = date('Y-m-d H:i', $book['created_on']);
			$booking_info->for_date = date('Y-m-d', $book['for_date']);
			$booking_info->emailed = (int)$book['emailed'];
			$booking_info->emailed_to = $book['emailed_to'];
			$booking_info->rawcont = $book['rawcont'];
			$booking_info->einvoice_xml = $book['einvoice_xml'];
			if ($this->isAppE4jConnect() && !empty($booking_info->einvoice_xml)) {
				// for the App we do not return the raw XML content
				$booking_info->einvoice_xml = 1;
			}
			$booking_info->status = !empty($book['status']) ? ucfirst($book['status']) : null;
			$booking_info->nights = $book['days'];
			if (!empty($book['checkin'])) {
				$booking_info->checkin = date('Y-m-d H:i', $book['checkin']);
				$booking_info->checkout = date('Y-m-d H:i', $book['checkout']);
			}
			$booking_info->number_of_rooms = (int)$book['roomsnum'];
			$booking_info->adults = (int)$book['tot_adults'];
			$booking_info->children = (int)$book['tot_children'];
			if (!empty($book['cust_nominative'])) {
				$booking_info->customer_name = $book['cust_nominative'];
			} elseif (!empty($book['nominative'])) {
				$booking_info->customer_name = $book['nominative'];
			} elseif (!empty($book['custdata'])) {
				$cust_data_lines = explode("\n", $book['custdata']);
				$first_cust_info = explode(":", $cust_data_lines[0]);
				$booking_info->customer = count($first_cust_info) > 1 ? $first_cust_info[1] : $first_cust_info[0];
			}
			if (!empty($book['id'])) {
				$booking_info->country = $book['country'];
				$booking_info->email = $book['custmail'];
				$booking_info->phone = $book['phone'];
			}
			$booking_info->source = 'VBO';
			if (!empty($book['channel']) && $book['channel'] != 'Channel Manager') {
				$source = explode('_', $book['channel']);
				$source = count($source) > 1 ? $source[1] : $source[0];
				$booking_info->source = $this->clearSourceName($source);
			}
			if (!empty($book['idorderota'])) {
				$booking_info->ota_id = $book['idorderota'];
			}
			array_push($invoices, $booking_info);
		}

		// build the response
		$response->total_invoices = $total_invoices;
		$response->page = $current_page;
		$response->invoices = $invoices;

		// set the response object as the body for the App
		$this->response->body = $response;
	}

	/**
	 * Reads one invoice and sends it to output. Supports PDF or Electronic format.
	 * It is also possible to obtain a temporary URL for the electronic invoice in
	 * case the App needs it to open a final URL.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.8.1
	 */
	private function outputInvoice()
	{
		$invoice_id = $this->input->getInt('invoice_id', 0);
		$format 	= $this->input->getString('format', 'pdf');
		$get_url 	= $this->input->getInt('get_url', 0);

		if (empty($invoice_id)) {
			$this->setError('Missing invoice ID');
			return false;
		}

		$q = "SELECT * FROM `#__vikbooking_invoices` WHERE `id`=" . $invoice_id;
		$this->dbo->setQuery($q);
		$invoice_data = $this->dbo->loadAssoc();
		if (!$invoice_data) {
			$this->setError('Invoice not found');
			return false;
		}

		// validate the requested format
		$format = strtolower($format);
		$format = in_array($format, array('pdf', 'einvoice')) ? $format : 'pdf';

		if ($format == 'einvoice') {
			$q = "SELECT `eid`.*, `eic`.`driver` FROM `#__vikbooking_einvoicing_data` AS `eid` LEFT JOIN `#__vikbooking_einvoicing_config` AS `eic` ON `eid`.`driverid`=`eic`.`id` WHERE `eid`.`idorder`=" . $this->dbo->quote($invoice_data['idorder']) . " AND `eid`.`obliterated`=0 ORDER BY `eid`.`id` DESC";
			$this->dbo->setQuery($q, 0, 1);
			$einvoice = $this->dbo->loadAssoc();
			if (!$einvoice) {
				$this->setError('Electronic invoice not found');
				return false;
			}

			try {
				// require the abstract VikBookingEInvoicing class
				$driver_base = VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'einvoicing' . DIRECTORY_SEPARATOR;
				require_once $driver_base . 'einvoicing.php';
				// require the driver class file for the current electronic invoice
				$driver_file = $einvoice['driver'] . '.php';
				$driver_path = $driver_base . 'drivers' . DIRECTORY_SEPARATOR . $driver_file;
				if (!is_file($driver_path)) {
					$this->setError('Driver not found for electronic invoice');
					return false;
				}
				require_once $driver_path;
				// invoke the class
				$classname = 'VikBookingEInvoicing' . str_replace(' ', '', ucwords(str_replace('.php', '', str_replace('_', ' ', $driver_file))));
				if (!class_exists($classname)) {
					$this->setError('Driver class not found for electronic invoice');
					return false;
				}
				$driver_obj = new $classname;
				if (!method_exists($driver_obj, 'viewEInvoice')) {
					$this->setError('Driver does not support outputting for the electronic invoice');
					return false;
				}
				// inject request parameter for the electronic invoice ID
				JFactory::getApplication()->input->set('einvid', $einvoice['id']);
				
				if ($get_url) {
					// clean buffer
					while (ob_get_status()) {
						// repeat until the buffer is empty
						ob_end_clean();
					}
					// start output buffering
					ob_start();
					
					// register shutdown function that will output the actual response
					if (VCMPlatformDetection::isWordPress()) {
						// @wponly  WordPress flushes the buffer through a shutdown function, so we need to prevent that
						remove_all_actions('shutdown');
					}
					$current_response = $this->response;
					register_shutdown_function(function() use ($current_response) {
						// catch the eletronic invoice output
						$content = ob_get_contents();
						ob_end_clean();
						// write the buffer (e-invoice content) onto a temporary file
						$fp = fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tmp_einvoice.xml', 'w+');
						fwrite($fp, $content);
						fclose($fp);
						// set response result and body
						$current_response->res  = 'e4j.ok';
						$current_response->body = VCM_SITE_URI . 'helpers/tmp_einvoice.xml';
						// send headers
						header('Content-type:application/json;charset=utf-8');
						// output response
						echo json_encode($current_response);
					});

				}

				// call method that will TERMINATE the execution of the script
				$driver_obj->viewEInvoice();
			} catch (Exception $e) {
				$this->setError('Something went wrong while reading the electronic invoice');
				return false;
			}
		}

		// output the PDF version of the invoice
		$pdf_invoice = VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'invoices' . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR . $invoice_data['file_name'];
		if (!is_file($pdf_invoice)) {
			$this->setError('PDF invoice not found');
			return false;
		}
		header("Content-type:application/pdf");
		readfile($pdf_invoice);
		
		exit;
	}

	/**
	 * Deletes one invoice in any possible format.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.8.1
	 */
	private function removeInvoice()
	{
		$invoice_id = $this->input->getInt('invoice_id', 0);

		if (empty($invoice_id)) {
			$this->setError('Missing invoice ID');
			return false;
		}

		$q = "SELECT * FROM `#__vikbooking_invoices` WHERE `id`=" . $invoice_id;
		$this->dbo->setQuery($q);
		$invoice_data = $this->dbo->loadAssoc();
		if (!$invoice_data) {
			$this->setError('Invoice not found');
			return false;
		}

		$q = "SELECT `id` FROM `#__vikchannelmanager_channel` WHERE `uniquekey`=".(int)VikChannelManagerConfig::MOBILEAPP." LIMIT 1;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() < 1) {
			$this->setError(JText::_('VCMAPPCHREQREFUSED'));
			return false;
		}

		// compose data removed
		$removed = array();

		// delete analogic version
		$q = "DELETE FROM `#__vikbooking_invoices` WHERE `id`=" . (int)$invoice_data['id'] . ";";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		array_push($removed, 'invoice');

		$pdfpath = VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'invoices' . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR . $invoice_data['file_name'];
		if (is_file($pdfpath)) {
			@unlink($pdfpath);
			array_push($removed, 'pdf');
		}

		// check if an electronic version exists
		$q = "SELECT `eid`.`id` FROM `#__vikbooking_einvoicing_data` AS `eid` WHERE `eid`.`idorder`=" . $this->dbo->quote($invoice_data['idorder']) . " AND `eid`.`obliterated`=0 ORDER BY `eid`.`id` DESC";
		$this->dbo->setQuery($q, 0, 1);
		$einvoice_id = $this->dbo->loadResult();
		if ($einvoice_id) {
			// remove e-invoice
			$q = "DELETE FROM `#__vikbooking_einvoicing_data` WHERE `id`=" . (int)$einvoice_id . ";";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
			array_push($removed, 'einvoice');
		}

		// set the response for the App
		$this->response->body = $removed;
	}

	/**
	 * Sends the invoice via email to the customer.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.8.1
	 */
	private function sendInvoice()
	{
		$invoice_id = $this->input->getInt('invoice_id', 0);

		if (empty($invoice_id)) {
			$this->setError('Missing invoice ID');
			return false;
		}

		$q = "SELECT `inv`.`id`, `inv`.`idorder`, `inv`.`file_name`, `o`.`custmail` FROM `#__vikbooking_invoices` AS `inv` LEFT JOIN `#__vikbooking_orders` AS `o` ON `o`.`id`=`inv`.`idorder` WHERE `inv`.`id`=" . $invoice_id;
		$this->dbo->setQuery($q);
		$invoice_data = $this->dbo->loadAssoc();
		if (!$invoice_data) {
			$this->setError('Invoice not found');
			return false;
		}

		if (empty($invoice_data['custmail'])) {
			$this->setError('Customer email is empty');
			return false;
		}

		$pdfpath = VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'invoices' . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR . $invoice_data['file_name'];
		if (!is_file($pdfpath)) {
			$this->setError('PDF invoice not found');
			return false;
		}

		// require VBO library
		$this->importVboLib();

		/**
		 * Load VBO back-end lang only with Joomla, as on WordPress
		 * we no longer need to split front-end and back-end langs.
		 */
		$lang = JFactory::getLanguage();
		if (VCMPlatformDetection::isJoomla()) {
			$lang->load('com_vikbooking', JPATH_ADMINISTRATOR, $lang->getTag(), true);
		}

		// let VBO send the invoice via email
		$result = VikBooking::sendBookingInvoice($invoice_id, $invoice_data);
		if (!$result) {
			$this->setError('Could not send the invoice via email');
			return false;
		}

		// set the response for the App
		$this->response->body = $invoice_data['custmail'];
	}

	/**
	 * Generates an invoice for a specific booking. If one driver is enabled for
	 * the generation of the electronic invoice, this will be triggered as well.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.8.1
	 */
	private function generateInvoice()
	{
		$bid = $this->input->getInt('bid', 0);
		$invoice_num = $this->input->getInt('invoice_num', 0);
		$send = $this->input->getInt('send', 1);

		if (empty($bid)) {
			$this->setError(JText::_('VCMAPPEMPTYBOOKINGID'));
			return false;
		}

		// count vars
		$tot_generated = 0;
		$tot_sent = 0;

		// require VBO library
		$this->importVboLib();

		/**
		 * Load VBO back-end lang only with Joomla, as on WordPress
		 * we no longer need to split front-end and back-end langs.
		 */
		$lang = JFactory::getLanguage();
		if (VCMPlatformDetection::isJoomla()) {
			$lang->load('com_vikbooking', JPATH_ADMINISTRATOR, $lang->getTag(), true);
		}

		// grab default values
		$nextinvnum  = VikBooking::getNextInvoiceNumber();
		$invsuff 	 = VikBooking::getInvoiceNumberSuffix();
		$companyinfo = VikBooking::getInvoiceCompanyInfo();
		$nowdf 		 = VikBooking::getDateFormat(true);
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$inv_date = date($df);

		// when re-generating an invoice, we should not increase the invoice number
		$prev_data = null;
		$increment_inv = true;
		$q = "SELECT `number`,`for_date` FROM `#__vikbooking_invoices` WHERE `idorder`={$bid}";
		$this->dbo->setQuery($q, 0, 1);
		$prev_data = $this->dbo->loadAssoc();
		if ($prev_data) {
			$prev_inv_number = intval(str_replace($invsuff, '', $prev_data['number']));
			if ($prev_inv_number > 0) {
				$invoice_num = $prev_inv_number;
			}
			$increment_inv = false;
		}

		// make sure we have an invoice number or we get it from the configuration
		$invoice_num = empty($invoice_num) ? $nextinvnum : $invoice_num;

		// grab booking details
		$q = "SELECT `o`.*,`co`.`idcustomer`,CONCAT_WS(' ',`c`.`first_name`,`c`.`last_name`) AS `customer_name`,`c`.`pin` AS `customer_pin`,`nat`.`country_name` FROM `#__vikbooking_orders` AS `o` LEFT JOIN `#__vikbooking_customers_orders` `co` ON `co`.`idorder`=`o`.`id` LEFT JOIN `#__vikbooking_customers` `c` ON `c`.`id`=`co`.`idcustomer` LEFT JOIN `#__vikbooking_countries` `nat` ON `nat`.`country_3_code`=`o`.`country` WHERE `o`.`id`={$bid} AND `o`.`status`='confirmed' AND `o`.`total` > 0;";
		$this->dbo->setQuery($q);
		$booking = $this->dbo->loadAssoc();
		if (!$booking) {
			$this->setError(JText::_('VBOGENINVERRNOBOOKINGS'));
			return false;
		}

		// generate the invoice
		$gen_res = VikBooking::generateBookingInvoice($booking, $invoice_num, $invsuff, $inv_date, $companyinfo);
		if ($gen_res !== false && $gen_res > 0) {
			$tot_generated++;
			$invoice_num++;
			if ($send) {
				$send_res = VikBooking::sendBookingInvoice($gen_res, $booking);
				if ($send_res !== false) {
					$tot_sent++;
				}
			}
		} else {
			$this->setError(JText::sprintf('VBOGENINVERRBOOKING', $booking['id']));
			return false;
		}

		if ($tot_generated > 0 && $increment_inv === true) {
			/**
			 * IMPORTANT: update the next invoice number after calling generateBookingInvoice()
			 * to avoid conflicts with the drivers for the e-invoices generation.
			 */
			$q = "UPDATE `#__vikbooking_config` SET `setting`=" . $this->dbo->quote(($invoice_num - 1)) . " WHERE `param`='invoiceinum';";
			$this->dbo->setQuery($q);
			$this->dbo->execute();
		}

		$response = new stdClass;
		$response->generated = $tot_generated;
		$response->sent = $tot_sent;

		// set the response for the App
		$this->response->body = $response;
	}

	/**
	 * Gathers statistics tracking data from the website and the
	 * scorecard details for each available API channel.
	 * 
	 * @return 		void
	 * 
	 * @since 		1.8.6
	 * @requires 	e4jConnect App >= 1.6 (iOS and Android)
	 */
	private function getScorecards()
	{
		// require VBO library
		$this->importVboLib();

		// the pool of scorecards found
		$scorecards = [];

		// request filtering dates and options
		$rq_date_from = $this->input->getString('from_date', '');
		if (empty($rq_date_from)) {
			// default to first day of current month
			$rq_date_from = date('Y-m') . '-01';
		}
		$rq_date_to = $this->input->getString('to_date', '');
		if (empty($rq_date_from)) {
			// default to today
			$rq_date_from = date('Y-m-d');
		}
		$rq_date_type = $this->input->getInt('date_type', 1);

		// website statistics tracking

		// load the tracker object without starting to track any data
		VikBooking::getTracker(true);

		// total unique and active visitors today
		$today_from = date('Y-m-d') . ' 00:00:00';
		$today_to = date('Y-m-d') . ' 23:59:59';
		$tot_today = VikBookingTracker::countTrackedRecords($today_from, $today_to);

		// total unique and active visitors this month until the end of today
		$month_from = date('Y-m') . '-01 00:00:00';
		$tot_month = VikBookingTracker::countTrackedRecords($month_from, $today_to);

		// total unique and active visitors last month until the end of today's month day
		$now = getdate();
		$last_month_from = date('Y-m-d H:i:s', mktime(0, 0, 0, ($now['mon'] - 1), 1, $now['year']));
		$last_month_to = date('Y-m-d H:i:s', mktime(23, 59, 59, ($now['mon'] - 1), $now['mday'], $now['year']));
		$tot_last_month = VikBookingTracker::countTrackedRecords($last_month_from, $last_month_to);

		/**
		 * Get statistics data (demanded nights, conversion rates, referrers etc..).
		 * 
		 * @requires 	VBO >= 1.15.3 (J) - 1.5.5 (WP)
		 */
		$website_stats_data = [];
		if (method_exists('VikBookingTracker', 'getStatistics')) {
			// get statistics tracking for the requested dates (current month by default)
			$website_stats_data = VikBookingTracker::getStatistics($rq_date_from, $rq_date_to, $rq_date_type);
		}

		// merge data with visitors counter
		$website_stats_data['visitors_today'] 	   = $tot_today;
		$website_stats_data['visitors_month'] 	   = $tot_month;
		$website_stats_data['visitors_last_month'] = $tot_last_month;

		// push scorecard (statistics) for website (VBO)
		$scorecard = new stdClass;
		$scorecard->name 	   = 'Website';
		$scorecard->channel    = 'vbo';
		$scorecard->channel_id = null;
		$scorecard->account_id = null;
		$scorecard->account_nm = null;
		$scorecard->type  	   = 'associative';
		$scorecard->last_date  = date('Y-m-d');
		$scorecard->data 	   = $website_stats_data;

		$scorecards[] = $scorecard;

		// check for channel scorecards
		$q = "SELECT `param`, `setting` FROM `#__vikchannelmanager_config` WHERE `param` LIKE 'propscore_%';";
		$this->dbo->setQuery($q);
		$channel_scores = $this->dbo->loadAssocList();
		if ($channel_scores) {
			foreach ($channel_scores as $ch_score) {
				// get channel name, ID and account ID
				$account_parts = explode('_', str_replace('propscore_', '', $ch_score['param']));
				if (count($account_parts) < 2) {
					// invalid account
					continue;
				}
				$channel_id = $account_parts[0];
				unset($account_parts[0]);
				$account_id = implode('_', $account_parts);
				$channel_info = VikChannelManager::getChannel($channel_id);
				if (!is_array($channel_info) || !count($channel_info)) {
					// channel not found, maybe this was deleted
					continue;
				}

				// channel readable name
				$say_channel_name = $channel_info['uniquekey'] == VikChannelManagerConfig::AIRBNBAPI ? 'Airbnb' : ucfirst($channel_info['name']);
				$say_channel_name = $channel_info['uniquekey'] == VikChannelManagerConfig::GOOGLEHOTEL ? 'Google Hotel' : $say_channel_name;
				$say_channel_name = $channel_info['uniquekey'] == VikChannelManagerConfig::GOOGLEVR ? 'Google Vacation Rentals' : $say_channel_name;

				// channel account name
				$ch_accounts = VikChannelManager::getChannelAccountsMapped($channel_info['uniquekey'], $get_rooms = false);
				$account_name = $account_id;
				if (isset($ch_accounts[$account_id])) {
					$account_name = $ch_accounts[$account_id];
				}

				// check scorecard content
				if (empty($ch_score['setting'])) {
					continue;
				}
				$score_content = json_decode($ch_score['setting'], true);
				if (!is_array($score_content) || empty($score_content) || empty($score_content['last_ymd']) || empty($score_content['data'])) {
					// empty or invalid scorecard content
					continue;
				}

				// prepare channel scorecard data
				$ch_score_type = 'stars_rating';
				$ch_score_data = [];
				if (!empty($score_content['data']['overall_rating']) && !empty($score_content['data']['overall_rating']['summary'])) {
					// typical structure of Airbnb
					$ch_score_data = $score_content['data']['overall_rating']['summary'];
				} elseif (!empty($score_content['data']['review_score']) && !empty($score_content['data']['review_score']['summary'])) {
					// typical structure of Booking.com
					$ch_score_data = $score_content['data']['review_score']['summary'];
				} elseif (!empty($score_content['data']['hotel_status']) && !empty($score_content['data']['hotel_status']['report'])) {
					// typical structure of Google Hotel
					$ch_score_type = 'click_booking_link';
					$ch_score_data = $score_content['data']['hotel_status']['report'];
					// make sure there is a report indicating the free booking links clicks
					if (empty($ch_score_data['stats'])) {
						// no data for this account
						continue;
					}
				}

				if (empty($ch_score_data)) {
					// unsupported structure
					continue;
				}

				// prepare scorecard object for this channel
				$scorecard = new stdClass;
				$scorecard->name 	   = $say_channel_name;
				$scorecard->channel    = $channel_info['name'];
				$scorecard->channel_id = $channel_info['uniquekey'];
				$scorecard->account_id = $account_id;
				$scorecard->account_nm = $account_name;
				$scorecard->type  	   = $ch_score_type;
				$scorecard->last_date  = $score_content['last_ymd'];
				$scorecard->data 	   = $ch_score_data;

				// push channel scorecard
				$scorecards[] = $scorecard;
			}
		}

		// set the response for the App
		$this->response->body = $scorecards;
	}

	/**
	 * Returns the list and type of pax fields needed for the guests registration.
	 * 
	 * @return 		void
	 * 
	 * @since 		1.8.7
	 * @requires 	e4jConnect App >= 1.7 (iOS and Android)
	 */
	private function appGetPaxFields()
	{
		// require VBO library
		$this->importVboLib();

		// load VBO back-end lang
		$lang = JFactory::getLanguage();
		if (VCMPlatformDetection::isWordPress()) {
			$lang->load('com_vikbooking', VIKBOOKING_LANG, $lang->getTag(), true);
		} else {
			$lang->load('com_vikbooking', JPATH_ADMINISTRATOR, $lang->getTag(), true);
		}

		// make sure VBO is updated
		if (!class_exists('VBOCheckinPax')) {
			$this->setError('Vik Booking must be updated in order to support this feature');
			return false;
		}

		// the booking ID for which pax fields should be retrieved
		$bid = $this->input->getInt('bid', 0);
		if (empty($bid)) {
			$this->setError(JText::_('VCMAPPEMPTYBOOKINGID'));
			return false;
		}

		// get the booking details and rooms
		$booking = VikBooking::getBookingInfoFromID($bid);
		if (empty($booking)) {
			$this->setError(JText::_('VCMAPPEMPTYBOOKINGID'));
			return false;
		}
		$booking_rooms = VikBooking::loadOrdersRoomsData($booking['id']);
		if (empty($booking_rooms)) {
			$this->setError(JText::_('VCMAPPEMPTYBOOKINGID'));
			return false;
		}

		// get current registration data
		$current_pax_data = [];
		$cpin = VikBooking::getCPinIstance();
		$cpin->is_admin = true;
		$customer = $cpin->getCustomerFromBooking($booking['id']);
		if (!empty($customer) && !empty($customer['pax_data'])) {
			if (is_string($customer['pax_data'])) {
				$customer['pax_data'] = json_decode($customer['pax_data'], true);
			}
			$current_pax_data = is_array($customer['pax_data']) ? $customer['pax_data'] : $current_pax_data;
		}

		// get pax fields for pre-checkin and back-end registration
		$pax_fields = new stdClass;
		$pax_fields->precheckin   = VikBooking::getPaxFields($precheckin = true);
		$pax_fields->registration = VikBooking::getPaxFields($precheckin = false);
		$pax_fields->signature 	  = null;
		$pax_fields->comments 	  = null;
		$pax_fields->checkindoc   = null;
		$pax_fields->reg_status   = (string)$booking['checked'];

		// get signature, check-in comments and check-in document
		$q = "SELECT `signature`, `comments`, `checkindoc` FROM `#__vikbooking_customers_orders` WHERE `idorder`=" . $booking['id'] . ";";
		$this->dbo->setQuery($q);
		$checkin_extra = $this->dbo->loadAssoc();
		if ($checkin_extra) {
			if (!empty($checkin_extra['signature'])) {
				$signature_path = VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'idscans' . DIRECTORY_SEPARATOR . $checkin_extra['signature'];
				if (is_file($signature_path)) {
					$pax_fields->signature = VBO_ADMIN_URI . 'resources/idscans/' . $checkin_extra['signature'];
				}
			}
			if (!empty($checkin_extra['comments'])) {
				$pax_fields->comments = $checkin_extra['comments'];
			}
			if (!empty($checkin_extra['checkindoc'])) {
				$checkindoc_path = VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'checkins' . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR . $checkin_extra['checkindoc'];
				if (is_file($checkindoc_path)) {
					$pax_fields->checkindoc = VBO_SITE_URI . 'helpers/checkins/generated/' . $checkin_extra['checkindoc'];
				}
			}
		}

		// get the active driver instance for guests registration
		$pax_fields_obj = VBOCheckinPax::getInstance();
		$reg_children 	= $pax_fields_obj->registerChildren();

		// build an array of room-guests information to collect the data
		$room_guests_data = [];
		foreach ($booking_rooms as $ind => $booking_room) {
			// build room guests
			$room_guests = [
				'room_name'  => $booking_room['room_name'],
				'room_index' => $booking_room['roomindex'],
				'adults' 	 => (int)$booking_room['adults'],
				'children' 	 => (int)$booking_room['children'],
				'guests' 	 => [],
			];
			for ($a = 1; $a <= $booking_room['adults']; $a++) {
				// push adult
				$guest_info = new stdClass;
				$guest_info->type = 'adult';
				$guest_info->fname = $a === 1 && !empty($booking_room['t_first_name']) ? $booking_room['t_first_name'] : null;
				$guest_info->lname = $a === 1 && !empty($booking_room['t_last_name']) ? $booking_room['t_last_name'] : null;
				$guest_info->age = null;
				$guest_info->data = isset($current_pax_data[$ind]) && isset($current_pax_data[$ind][$a]) ? $current_pax_data[$ind][$a] : null;
				$room_guests['guests'][] = $guest_info;
			}
			if ($reg_children && $booking_room['children'] > 0) {
				// check children age
				$children_age = [];
				if (!empty($booking_room['childrenage'])) {
					$children_age_info = json_decode($booking_room['childrenage'], true);
					if (is_array($children_age_info)) {
						$children_age = $children_age_info;
					}
				}
				for ($c = 1; $c <= $booking_room['children']; $c++) {
					// get child age
					$child_age = null;
					if (isset($children_age[($c - 1)]) && is_array($children_age[($c - 1)]) && !empty($children_age[($c - 1)]['age'])) {
						$child_age = $children_age[($c - 1)]['age'];
					}
					$g_index = $booking_room['adults'] + $c;
					// push child
					$guest_info = new stdClass;
					$guest_info->type = 'child';
					$guest_info->fname = null;
					$guest_info->lname = null;
					$guest_info->age = $child_age;
					$guest_info->data = isset($current_pax_data[$ind]) && isset($current_pax_data[$ind][$g_index]) ? $current_pax_data[$ind][$g_index] : null;
					$room_guests['guests'][] = $guest_info;
				}
			}
			// push room guests information
			$room_guests_data[] = $room_guests;
		}

		// set room guests information
		$pax_fields->room_guests_data = $room_guests_data;

		// unset unsupported fields for precheck-in (file)
		$supported_field_types = [
			'text',
			'textarea',
			'calendar',
			'country',
		];
		foreach ($pax_fields->precheckin[1] as $attr_key => $attr_type) {
			if (is_string($attr_type) && !in_array($attr_type, $supported_field_types)) {
				unset($pax_fields->precheckin[0][$attr_key], $pax_fields->precheckin[1][$attr_key]);
			}
		}

		// append countries property
		$pax_fields->countries 	  = VikBooking::getCountriesArray();

		// set the response for the App
		$this->response->body = $pax_fields;
	}

	/**
	 * Updates the guests registration information for a booking.
	 * 
	 * @return 		void
	 * 
	 * @since 		1.8.7
	 * @requires 	e4jConnect App >= 1.7 (iOS and Android)
	 */
	private function appSetPaxFields()
	{
		// require VBO library
		$this->importVboLib();

		// make sure VBO is updated
		if (!class_exists('VBOCheckinPax')) {
			$this->setError('Vik Booking must be updated in order to support this feature');
			return false;
		}

		// the booking ID for which pax fields should be retrieved
		$bid = $this->input->getInt('bid', 0);
		if (empty($bid)) {
			$this->setError(JText::_('VCMAPPEMPTYBOOKINGID'));
			return false;
		}

		// the whole pax registration data
		$set_pax_data = $this->input->getVar('pax', array());
		$allowed_regs = ['precheckin', 'registration', 'status'];
		if (empty($set_pax_data) || empty($set_pax_data['reg_type']) || !in_array($set_pax_data['reg_type'], $allowed_regs)) {
			$this->setError('Missing or invalid guests registration data');
			return false;
		}

		// the pax extra information
		$set_pax_extra = $this->input->getVar('extra', array());

		// get the booking details and rooms
		$booking = VikBooking::getBookingInfoFromID($bid);
		if (empty($booking)) {
			$this->setError(JText::_('VCMAPPEMPTYBOOKINGID'));
			return false;
		}
		$booking_rooms = VikBooking::loadOrdersRoomsData($booking['id']);
		if (empty($booking_rooms)) {
			$this->setError(JText::_('VCMAPPEMPTYBOOKINGID'));
			return false;
		}

		// website date format
		$nowdf = VikBooking::getDateFormat(true);
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}

		// get current registration data
		$current_pax_data = [];
		$cpin = VikBooking::getCPinIstance();
		$cpin->is_admin = true;
		$customer = $cpin->getCustomerFromBooking($booking['id']);
		if (!empty($customer) && !empty($customer['pax_data'])) {
			if (is_string($customer['pax_data'])) {
				$customer['pax_data'] = json_decode($customer['pax_data'], true);
			}
			$current_pax_data = is_array($customer['pax_data']) ? $customer['pax_data'] : $current_pax_data;
		}

		if (empty($customer) || empty($customer['id'])) {
			$this->setError('Missing customer record for this booking. Unable to proceed.');
			return false;
		}

		// parse the update request type
		if ($set_pax_data['reg_type'] == 'status') {
			// update the status of the check-in information
			if (empty($set_pax_extra)) {
				$this->setError('Missing information to perform the update');
				return false;
			}

			// update comments and customer-booking related information
			$cust_book_row = new stdClass;
			$cust_book_row->idcustomer = $customer['id'];
			$cust_book_row->idorder = $booking['id'];
			if (isset($set_pax_extra['comments'])) {
				$cust_book_row->comments = $set_pax_extra['comments'];
			}
			if (!empty($set_pax_extra['signature_img'])) {
				// a new image for the signature has been created
				$signature_data = '';
				$cont_type = '';
				if (strpos($set_pax_extra['signature_img'], 'image/png') !== false || strpos($set_pax_extra['signature_img'], 'image/jpeg') !== false) {
					$parts = explode(';base64,', $set_pax_extra['signature_img']);
					$cont_type_parts = explode('image/', $parts[0]);
					$cont_type = $cont_type_parts[1];
					if (!empty($parts[1])) {
						$signature_data = base64_decode($parts[1]);
					}
				}
				if (!empty($signature_data) && !empty($cont_type)) {
					$sign_fname = $booking['id'] . '_' . $booking['sid'] . '_' . $customer['id'] . '.' . $cont_type;
					$filepath = VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'idscans' . DIRECTORY_SEPARATOR . $sign_fname;
					$fp = fopen($filepath, 'w+');
					$bytes = fwrite($fp, $signature_data);
					fclose($fp);
					if ($bytes !== false && $bytes > 0) {
						// signature image file stored correctly, so we update the value on the DB
						$cust_book_row->signature = $sign_fname;
						if (VCMPlatformDetection::isWordPress()) {
							// @wponly
							try {
								VikBookingLoader::import('update.manager');
								VikBookingUpdateManager::triggerUploadBackup($filepath);
							} catch (Exception $e) {
								// do nothing
							}
						}
					}
				}
			}
			$this->dbo->updateObject('#__vikbooking_customers_orders', $cust_book_row, ['idcustomer', 'idorder']);

			// update booking registration status
			if (isset($set_pax_extra['reg_status'])) {
				$book_row = new stdClass;
				$book_row->id = $booking['id'];
				$book_row->checked = (int)$set_pax_extra['reg_status'];

				$this->dbo->updateObject('#__vikbooking_orders', $book_row, 'id');
			}
		}

		// update the pax data information according to the type of registration
		if ($set_pax_data['reg_type'] == 'precheckin' || $set_pax_data['reg_type'] == 'registration') {
			// the type of registration
			$reg_type = $set_pax_data['reg_type'] == 'precheckin' ? 'precheckin' : 'registration';
			if (empty($set_pax_data[$reg_type]) || !is_array($set_pax_data[$reg_type])) {
				$this->setError('No values to update the registration');
				return false;
			}
			$pax_data_info = [];
			foreach ($set_pax_data[$reg_type] as $rnum => $room_guests) {
				$pax_room = [];
				foreach ($room_guests as $gnum => $guest_fields) {
					$pax_guest = [];
					foreach ($guest_fields as $guest_field) {
						if (empty($guest_field['field_key'])) {
							continue;
						}
						$field_prop = $guest_field['field_key'];
						$field_val = isset($guest_field[$guest_field['field_key']]) ? $guest_field[$guest_field['field_key']] : '';
						if (!strcasecmp($guest_field['field_type'], 'calendar') && !empty($field_val)) {
							// date submitted must be in Y-m-d format, so format it according to settings
							$field_val = date($df, strtotime($field_val));
						}
						// set property value
						$pax_guest[$field_prop] = $field_val;
					}
					// push guest data (indexed by 1)
					$pax_room[$gnum + 1] = $pax_guest;
				}
				// push room guests data
				$pax_data_info[] = $pax_room;
			}
			// make sure the current pax data are not containing some reserved properties that should not get lost
			foreach ($current_pax_data as $rnum => $room_guests) {
				if (!isset($pax_data_info[$rnum])) {
					// a room cannot be missing on a newly updated information
					continue;
				}
				foreach ($room_guests as $gnum => $guest_fields) {
					if (!isset($pax_data_info[$rnum][$gnum])) {
						// a guest cannot be missing on a newly updated information
						continue;
					}
					foreach ($guest_fields as $field_prop => $field_val) {
						if (!isset($pax_data_info[$rnum][$gnum][$field_prop])) {
							// set missing information to not lose it
							$pax_data_info[$rnum][$gnum][$field_prop] = $field_val;
						}
					}
				}
			}
			// update booking pax data
			if (!VBOCheckinPax::setBookingPaxData($booking['id'], $pax_data_info)) {
				$this->setError('Could not update booking guests registration data');
				return false;
			}
		}

		// Booking History
		$hist_type = 'RB';
		if (!empty($set_pax_extra['reg_status'])) {
			if ($pcheckin_action < 0) {
				$hist_type = 'Z';
			} elseif ($pcheckin_action == 1) {
				$hist_type = 'B';
			} elseif ($pcheckin_action == 2) {
				$hist_type = 'C';
			} else {
				$hist_type = 'A';
			}
			$hist_type = 'R' . $hist_type;
		}
		VikBooking::getBookingHistoryInstance()->setBid($booking['id'])->store($hist_type, "App ({$this->accountEmail})");

		// set the response for the App
		$this->response->body = $booking['id'];
	}

	/**
	 * Returns the geographical information about the hotel and/or listings.
	 * 
	 * @return 		void
	 * 
	 * @since 		1.8.24
	 */
	private function appGetGeoinfo()
	{
		$room_id = $this->input->getInt('room_id', 0);
		$hotel 	 = $this->input->getBool('hotel', true);

		// build the geo-info for at both property and listing levels
		$hotel_geoinfo = [];
		$rooms_geoinfo = [];

		// the VikBooking geocoding helper
		$geo = VikBooking::getGeocodingInstance();

		// fetch the hotel information, unless excluded or unavailable
		if ($hotel) {
			$this->dbo->setQuery(
				$this->dbo->getQuery(true)
					->select($this->dbo->qn(['key', 'value']))
					->from($this->dbo->qn('#__vikchannelmanager_hotel_details'))
					->where($this->dbo->qn('key') . ' != ' . $this->dbo->q('amenities'))
					->where($this->dbo->qn('value') . ' IS NOT NULL')
					->where('LENGTH(' . $this->dbo->qn('value') . ') > 0')
			);

			foreach ($this->dbo->loadAssocList() as $hotel_info) {
				if (in_array($hotel_info['key'], ['latitude', 'longitude'])) {
					$hotel_info['value'] = (float) $hotel_info['value'];
				}
				$hotel_geoinfo[$hotel_info['key']] = $hotel_info['value'];
			}
		}

		// query the involved rooms
		$this->dbo->setQuery(
			$this->dbo->getQuery(true)
				->select($this->dbo->qn(['id', 'name', 'params']))
				->from($this->dbo->qn('#__vikbooking_rooms'))
				->where(($room_id ? $this->dbo->qn('id') . ' = ' . $room_id : 1))
				->where($this->dbo->qn('avail') . ' = 1')
		);
		$rooms_data = $this->dbo->loadAssocList();

		foreach ($rooms_data as $room) {
			$geo_params = $geo->getRoomGeoParams(json_decode($room['params']));
			if (is_object($geo_params) && isset($geo_params->enabled) && $geo_params->enabled) {
				// build useful room geo info
				$room_geoinfo = [
					'name' 		=> $room['name'],
					'address'   => $geo_params->address ?? '',
					'latitude'  => $geo_params->latitude ?? '',
					'longitude' => $geo_params->longitude ?? '',
				];

				// set listing geo info
				$rooms_geoinfo[$room['id']] = $room_geoinfo;
			}
		}

		if ($rooms_data && count($rooms_geoinfo) < count($rooms_data)) {
			// attempt to gather the missing listing geo-info from some OTAs
			$missing_rids = [];
			foreach ($rooms_data as $room) {
				if (!isset($rooms_geoinfo[$room['id']])) {
					$missing_rids[$room['id']] = $room['name'];
				}
			}

			$this->dbo->setQuery(
				$this->dbo->getQuery(true)
					->select($this->dbo->qn(['idroomvb', 'idroomota', 'idchannel']))
					->from($this->dbo->qn('#__vikchannelmanager_roomsxref'))
					->where($this->dbo->qn('idroomvb') . ' IN (' . implode(', ', array_keys($missing_rids)) . ')')
			);
			$missing_xref = $this->dbo->loadAssocList();

			foreach ($missing_xref as $xref) {
				if (isset($rooms_geoinfo[$xref['idroomvb']])) {
					// this room was already parsed from a different channel
					continue;
				}

				// fetch the first available OTA listing information
				$this->dbo->setQuery(
					$this->dbo->getQuery(true)
						->select($this->dbo->qn('setting'))
						->from($this->dbo->qn('#__vikchannelmanager_otarooms_data'))
						->where($this->dbo->qn('idchannel') . ' = ' . $this->dbo->q($xref['idchannel']))
						->where($this->dbo->qn('idroomota') . ' = ' . $this->dbo->q($xref['idroomota']))
						->where($this->dbo->qn('param') . ' = ' . $this->dbo->q('listing_content'))
						->order($this->dbo->qn('last_updated') . ' DESC')
				);
				$ota_settings = $this->dbo->loadResult();
				$ota_settings = json_decode($ota_settings, true);
				if (!$ota_settings) {
					continue;
				}

				// parse the OTA listing details to see if geo-info are available
				$address_data = [];
				$latitude  	  = '';
				$longitude 	  = '';
				if (!empty($ota_settings['street'])) {
					$address_data[] = $ota_settings['street'];
				} elseif (!empty($ota_settings['addressLine1'])) {
					$address_data[] = $ota_settings['addressLine1'];
				}
				if (!empty($ota_settings['zipcode'])) {
					$address_data[] = $ota_settings['zipcode'];
				} elseif (!empty($ota_settings['postalCode'])) {
					$address_data[] = $ota_settings['postalCode'];
				}
				if (!empty($ota_settings['city'])) {
					$address_data[] = $ota_settings['city'];
				}
				if (!empty($ota_settings['state'])) {
					$address_data[] = $ota_settings['state'];
				} elseif (!empty($ota_settings['stateOrProvince'])) {
					$address_data[] = $ota_settings['stateOrProvince'];
				}
				if (!empty($ota_settings['country_code'])) {
					$address_data[] = $ota_settings['country_code'];
				} elseif (!empty($ota_settings['country'])) {
					$address_data[] = $ota_settings['country'];
				}

				if (!empty($ota_settings['lat'])) {
					$latitude = (float) $ota_settings['lat'];
				} elseif (!empty($ota_settings['latitude'])) {
					$latitude = (float) $ota_settings['latitude'];
				}
				if (!empty($ota_settings['lng'])) {
					$longitude = (float) $ota_settings['lng'];
				} elseif (!empty($ota_settings['longitude'])) {
					$longitude = (float) $ota_settings['longitude'];
				}

				// check if data is sufficient
				if ($address_data || ($latitude && $longitude)) {
					// set listing geo info
					$rooms_geoinfo[$xref['idroomvb']] = [
						'name' 		=> $missing_rids[$xref['idroomvb']],
						'address'   => implode(', ', $address_data),
						'latitude'  => $latitude,
						'longitude' => $longitude,
					];
				}
			}
		}

		// build response object
		$geo_info_levels = new stdClass;
		$geo_info_levels->hotel = $hotel_geoinfo;
		$geo_info_levels->rooms = $rooms_geoinfo;

		// set response body
		$this->response->body = $geo_info_levels;
	}

	/**
	 * Performs a translation over the AI model for a given text and locale.
	 * 
	 * @return 		void
	 * 
	 * @throws 		Exception
	 * 
	 * @since 		1.9.12
	 */
	private function appAiTranslate()
	{
		$text   = $this->input->getString('text', '');
		$locale = $this->input->getString('locale', '');

		if (!VikChannelManager::getChannel(VikChannelManagerConfig::AI)) {
			// AI channel not enabled
			throw new Exception(JText::_('VCM_AI_PAID_SERVICE_REQ'), 402);
		}

		try {
			$translated = (new VCMAiModelService)->translate($text, $locale);
		} catch (Exception $error) {
			// something went wrong, propagate the error
			throw $error;
		}

		// build response object
		$tn_response = new stdClass;
		$tn_response->text = $text;
		$tn_response->translated = $translated;
		$tn_response->locale = $locale;

		// set response body
		$this->response->body = $tn_response;
	}

	/**
	 * Helper method to format float values expressed in bytes (i.e. memory usage).
	 * 
	 * @param 	float 	$bytes 		The number of bytes to format.
	 * @param 	int 	$precision 	The precision to use for rounding.
	 * 
	 * @return 	string 				The formatted bytes string.
	 * 
	 * @since 		1.9.0
	 */
	private function formatMemoryBytes($bytes, $precision = 2)
	{
		$units = [
			'B',
			'KB',
			'MB',
			'GB',
			'TB',
		];

		$negative = (bool) ($bytes < 0);
		$bytes    = $negative ? abs($bytes) : $bytes;
		$bytes    = max($bytes, 0);
		$pow      = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow      = min($pow, count($units) - 1);
		$bytes    /= pow(1024, $pow);

		return ($negative ? '-' : '') . round($bytes, $precision) . ($units[$pow] ?? '?');
	}
}
