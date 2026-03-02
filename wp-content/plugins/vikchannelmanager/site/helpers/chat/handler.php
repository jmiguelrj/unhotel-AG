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

// require necessary classes
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'message.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'user.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'notification' . DIRECTORY_SEPARATOR . 'mediator.php';

/**
 * Abstract parent class to handle guest messages.
 * We extend JObject to benefit of the errors handling functions.
 *
 * Children classes can use JObject methods to attach their own properties.
 * Here's a list of properties that should be used by the chat client.
 *
 * @property  syncTime 	The interval duration (in seconds) between each synchronization.
 * 						If not specified, the default value will be used (10 seconds).
 * 
 * @since 	1.6.13
 */
abstract class VCMChatHandler extends JObject
{
	/**
	 * VBO booking ID.
	 *
	 * @var int
	 */
	protected $id_order;

	/**
	 * A list of threads messages.
	 *
	 * @var array
	 */
	protected $threads = array();

	/**
	 * The users involved in the chat.
	 *
	 * @var array
	 */
	protected $users = array();

	/**
	 * The current booking record.
	 *
	 * @var array
	 */
	protected $booking = array();

	/**
	 * The channel name that children classes should inherit.
	 *
	 * @var string
	 */
	protected $channelName = '';

	/**
	 * Class constructor is protected. Use getInstance() to
	 * get an object of the extending class for this channel.
	 * 
	 * @param 	mixed   $oid  	  VBO booking ID or an array containing ID and secret key.
	 * @param   string 	$channel  The booking source channel.
	 * 
	 * @return  mixed 	The handler instance on success, null otherwise.
	 */
	public static function getInstance($oid, $channel = null)
	{
		$channel = preg_replace("/[^a-zA-Z0-9]+/", '', (string)$channel);

		/**
		 * In order to comply with some versions of the e4jConnect App, we need to perform a
		 * verification of the VBO booking ID passed, because it could be an OTA booking ID.
		 * 
		 * @since 	1.8.0
		 */
		if ($channel == 'vikbooking' && is_scalar($oid) && strlen($oid) > 5)
		{
			// check if this booking belongs to a different and supported chat handler
			$real_channel_data = self::getBookingRealChannel($oid);
			if ($real_channel_data !== false)
			{
				// we have found the real channel to which the booking belongs
				list($oid, $channel) = $real_channel_data;
			}
		}

		$ch_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'channels' . DIRECTORY_SEPARATOR . $channel . '.php';
		if (!is_file($ch_file))
		{
			/**
			 * We always allow to use the website chat handler also for OTA
			 * bookings that do not support a dedicated chat handler.
			 * 
			 * @since 	1.8.9
			 */
			$channel = 'vikbooking';
			$ch_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'channels' . DIRECTORY_SEPARATOR . 'vikbooking.php';
		}

		// require channel class file
		require_once $ch_file;

		// channel class name
		$ch_class = 'VCMChatChannel' . ucfirst($channel);

		if (!class_exists($ch_class))
		{
			return null;
		}

		// return channel class instance
		return new $ch_class($oid);
	}

	/**
	 * Attempts to find the true source channel from the given reservation ID.
	 * In order to comply with some versions of the e4jConnect App, if we detect
	 * that a reservation ID may be too long for the website chat handler, then
	 * we look for the proper OTA chat handler to which the booking belongs.
	 * 
	 * @param 	string 	$oid 	the presumed OTA reservation ID.
	 * 
	 * @return 	mixed 			false on failure, real channel data array otherwise.
	 * 
	 * @since 	1.8.0
	 */
	protected static function getBookingRealChannel($oid)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn(array(
				'o.id',
				'o.channel',
			)))
			->from($dbo->qn('#__vikbooking_orders', 'o'))
			->where($dbo->qn('o.idorderota') . ' = ' . $dbo->q($oid));

		$dbo->setQuery($q, 0, 1);
		$record = $dbo->loadObject();

		if (!$record || empty($record->channel))
		{
			return false;
		}

		// make sure the chat handler exists for this channel
		$channel_parts   = explode('_', $record->channel);
		$record->channel = preg_replace("/[^a-zA-Z0-9]+/", '', $channel_parts[0]);
		if (is_file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'channels' . DIRECTORY_SEPARATOR . $record->channel . '.php'))
		{
			// proper channel chat handler found
			return array(
				$record->id,
				$record->channel,
			);
		}

		return false;
	}

	/**
	 * Counts the total number of unread messages for a specific
	 * reservation ID, depending on the client and sender type.
	 * 
	 * @param   mixed    $oid 	  	 The VBO booking ID or a list of identifiers.
	 * @param 	boolean  $recipient  True to get an instance of the recipient user.
	 * @param 	boolean  $unread 	 True to count only the unread messages.
	 * 
	 * @return  mixed    The number of unread messages in case of single ID, otherwise
	 * 					 an associative array where the key is the ID and the value
	 * 					 if the count of unread messages.
	 * 
	 * @since 	1.8.20 	 Introduced 3rd argument $unread.
	 */
	public static function countUnreadMessages($oid, $recipient = false, $unread = true)
	{
		if (!$oid)
		{
			// return false in case of invalid identifier(s)
			return false;
		}

		$app = JFactory::getApplication();
		if (method_exists($app, 'isClient'))
		{
			$client = $app->isClient('administrator') ? 1 : 0;
		}
		else
		{
			$client = $app->isAdmin() ? 1 : 0;
		}

		if ($recipient)
		{
			// negate the value of the client (0: admin, 1: site)
			$client ^= 1;
		}

		$dbo   = JFactory::getDbo();
		$oper  = $client == 1 ? ' <> ' : ' = ';

		$q = $dbo->getQuery(true);

		$q->select('COUNT(1) ' . $dbo->qn('count'));
		$q->from($dbo->qn('#__vikchannelmanager_threads_messages', 'm'));
		$q->leftjoin($dbo->qn('#__vikchannelmanager_threads', 't') . ' ON ' . $dbo->qn('m.idthread') . ' = ' . $dbo->qn('t.id'));
		$q->where('(' . $dbo->qn('m.sender_type') . $oper . $dbo->q('hotel') . ' AND ' . $dbo->qn('m.sender_type') . $oper . $dbo->q('host') . ')');
		if ($unread)
		{
			$q->where($dbo->qn('m.read_dt') . ' IS NULL');
		}

		if (is_array($oid))
		{
			// take order ID in query
			$q->select($dbo->qn('t.idorder'));
			// retrieve all specified orders
			$q->where($dbo->qn('t.idorder') . ' IN (' . implode(',', array_map('intval', $oid)) . ')');
			// group counts by order ID
			$q->group($dbo->qn('t.idorder'));
		}
		else
		{
			// retrieve single order
			$q->where($dbo->qn('t.idorder') . ' = ' . (int) $oid);
		}

		$dbo->setQuery($q);

		if (is_scalar($oid))
		{
			// return count directly in case of integer
			return (int) $dbo->loadResult();
		}

		$map = array();
		$records = $dbo->loadObjectList();

		if ($records)
		{
			// iterate response and build $map assoc
			foreach ($records as $obj)
			{
				$map[$obj->idorder] = (int) $obj->count;
			}
		}

		return $map;
	}

	/**
	 * Returns all the unread messages grouped by reservation ID,
	 * depending on the client and sender type.
	 * 
	 * @param 	boolean  $recipient  True to get an instance of the recipient user.
	 * 
	 * @return  array    An associative array where the key is the booking ID and
	 * 					 the value is an objects list (messages).
	 */
	public static function getAllUnreadMessages($recipient = false)
	{
		$app = JFactory::getApplication();
		if (method_exists($app, 'isClient'))
		{
			$client = $app->isClient('administrator') ? 1 : 0;
		}
		else
		{
			$client = $app->isAdmin() ? 1 : 0;
		}

		if ($recipient)
		{
			// negate the value of the client (0: admin, 1: site)
			$client ^= 1;
		}

		$dbo   = JFactory::getDbo();
		$oper  = $client == 1 ? ' <> ' : ' = ';

		$map = array();

		$q = $dbo->getQuery(true);

		$q->select($dbo->qn(array(
			't.idorder',
			't.subject',
			'm.content',
			'm.dt',
		)));
		$q->from($dbo->qn('#__vikchannelmanager_threads_messages', 'm'));
		$q->leftjoin($dbo->qn('#__vikchannelmanager_threads', 't') . ' ON ' . $dbo->qn('m.idthread') . ' = ' . $dbo->qn('t.id'));
		$q->where('(' . $dbo->qn('m.sender_type') . $oper . $dbo->q('hotel') . ' AND ' . $dbo->qn('m.sender_type') . $oper . $dbo->q('host') . ')');
		$q->where($dbo->qn('m.read_dt') . ' IS NULL');
		$q->order($dbo->qn('m.dt') . ' DESC');

		$dbo->setQuery($q);
		$records = $dbo->loadObjectList();

		if ($records)
		{
			// iterate response and build $map assoc
			foreach ($records as $obj)
			{
				if (!isset($map[$obj->idorder]))
				{
					$map[$obj->idorder] = array();
				}

				$map[$obj->idorder][] = $obj;
			}

			// sort first level by order ID
			ksort($map);
		}

		return $map;
	}

	/**
	 * Loads all the latest threads and the related (latest) messages.
	 * The messages are loaded from an optional start index and limit.
	 * 
	 * @param  ?array  $args  An array of filters and options.
	 *                        - start        int     The start index for the messages query (0 by default).
	 *                        - limit        int     The limit for the messages query (null by default).
	 *                        - sender       mixed   Sender type, string or array (null by default).
	 *                        - join_sender  bool    True to join only the guest thread messages (false by default).
	 *                        - noreply      bool    True to take only the messages that haven't received a reply yet.
	 *                        - start_dt     string  An optional date time to filter only the messages equal or newer than the provided date (null by default).
	 *                        - end_dt       string  An optional date time to filter only the messages equal or older than the provided date (null by default).
	 *                        - rooms        int[]   An optional array of supported listings. When not empty, only the threads beloning to the mentioned IDS will be taken.
	 * 
	 * For backward compatibility, the following notation is still supported.
	 * 
	 * ```
	 * VCMChatHandler::getLatestThreads($start = 0, $limit = 20, $sender = null, $join_sender = false)
	 * ```
	 * 
	 * @return  array  The list of thread-message objects loaded.
	 *
	 * @since   1.7.4
	 * @since   1.8.0    Argument $sender was added to allow filtering.
	 * @since   1.8.9    Added new clauses to sub-query join for sender type in
	 *                   order to properly sort the latest guest thread messages.
	 * @since   1.8.9    New arg $join_sender to fetch all latest guest messages,
	 *                   not only the threads where the last message is from the guest.
	 * @since   1.8.11   Customer profile picture and other booking details are fetched.
	 *                   If $join_sender, threads with no_reply_needed will be ignored.
	 * @since   1.8.19   Refactoring of the whole query to speed up loading on large datasets
	 *                   produced an average speed difference of ~80%, from 5.70s to 1.07s.
	 * @since   1.9      The system now accepts a single filters/options array as argument.
	 * @since   1.9.7    Added rooms argument.
	 */
	public static function getLatestThreads($args = null)
	{
		if (is_array($args))
		{
			/**
			 * Array provided, extract filters and options.
			 * 
			 * @since 1.9
			 */
			$start = $args['start'] ?? 0;
			$limit = $args['limit'] ?? null;
			$sender = $args['sender'] ?? null;
			$join_sender = $args['join_sender'] ?? false;
			$noreply = $args['noreply'] ?? false;
			$begin = $args['start_dt'] ?? null;
			$end = $args['end_dt'] ?? null;
			$rooms = $args['rooms'] ?? [];
		}
		else
		{
			// keep using the deprecated function declaration
			$args = func_get_args();
			$start = $args[0] ?? 0;
			$limit = $args[1] ?? 20;
			$sender = $args[2] ?? null;
			$join_sender = $args[3] ?? false;
			$noreply = false;
			$begin = null;
			$end = null;
			$rooms = [];
		}

		$threads = [];

		$dbo = JFactory::getDbo();

		////////////////////////////
		/// LOAD LATEST MESSAGES ///
		////////////////////////////

		$m = $dbo->getQuery(true);

		$m->select('MAX(' . $dbo->qn('dt') . ') AS ' . $dbo->qn('lastMessage'));
		$m->select($dbo->qn('idthread'));

		// load messages tables
		$m->from($dbo->qn('#__vikchannelmanager_threads_messages'));

		if ($join_sender)
		{
			if (is_array($sender))
			{
				$m->where($dbo->qn('sender_type') . ' IN (' . implode(', ', array_map([$dbo, 'q'], $sender)) . ')');
			}
			else
			{
				$m->where($dbo->qn('sender_type') . ' = ' . $dbo->q($sender));
			}
		}

		if ($noreply)
		{
			$m->where($dbo->qn('replied') . ' = 0');
		}

		if ($begin)
		{
			try {
				// exclude the messages older than the provided date time
				$m->where($dbo->qn('dt') . ' >= ' . $dbo->q(JFactory::getDate($begin)->toSql()));
			} catch (Exception $error) {
				// malformed date
			}
		}

		if ($end)
		{
			try {
				// exclude the messages newer than the provided date time
				$m->where($dbo->qn('dt') . ' <= ' . $dbo->q(JFactory::getDate($end)->toSql()));
			} catch (Exception $error) {
				// malformed date
			}
		}

		$m->group($dbo->qn('idthread'));

		///////////////////////////
		/// LOAD LATEST THREADS ///
		///////////////////////////

		$q = $dbo->getQuery(true);

		$q->select($dbo->qn(['t.idorder', 't.idorderota', 't.channel', 't.subject', 't.last_updated', 't.no_reply_needed', 't.ai_stopped']));
		$q->select($dbo->qn('m.id', 'id_message'));
		$q->select($dbo->qn('m.idthread', 'id_thread'));
		$q->select($dbo->qn(['m.sender_type', 'm.dt', 'm.content', 'm.attachments', 'm.read_dt', 'm.replied', 'm.ai_replied']));

		// load threads messages table
		$q->from($dbo->qn('#__vikchannelmanager_threads_messages', 'm'));

		// join threads with latest messages
		$q->innerJoin(
			'(' . $m . ') AS ' . $dbo->qn('im')
			. ' ON ' . $dbo->qn('m.idthread') . ' = ' . $dbo->qn('im.idthread')
			. ' AND ' . $dbo->qn('m.dt') . ' = ' . $dbo->qn('im.lastMessage')
		);

		// inner join threads
		$q->innerJoin(
			$dbo->qn('#__vikchannelmanager_threads', 't')
			. ' ON ' . $dbo->qn('t.id') . ' = ' . $dbo->qn('im.idthread')
		);

		// when getting all guest messages in threads, ignore the ones with no reply needed
		if ($join_sender)
		{
			$q->where($dbo->qn('t.no_reply_needed') . ' = 0');
		}

		/**
		 * Take only the threads beloning to a room that is actually observed by the user (operator).
		 * 
		 * @since 1.9.7
		 */
		if ($rooms)
		{
			$q->innerJoin($dbo->qn('#__vikbooking_ordersrooms', 'or') . ' ON ' . $dbo->qn('or.idorder') . ' = ' . $dbo->qn('t.idorder'));
			$q->where($dbo->qn('or.idroom') . ' IN (' . implode(',', array_map('intval', (array) $rooms)) . ')');
		}

		/**
		 * Prevented the query from selecting more than one message per thread.
		 * 
		 * @since 1.9.17
		 */
		$q->group($dbo->qn('m.idthread'));

		// order by descending received date
		$q->order($dbo->qn('m.dt') . ' DESC');

		$dbo->setQuery($q, $start, $limit);
		$records = $dbo->loadObjectList();

		if ($records)
		{
			/**
			 * It is now safe and efficient to join booking and customer tables to avoid slow queries
			 * on databases with large datasets and tens of thousands of records.
			 * 
			 * @since 	1.8.12
			 */

			$orderIds = array_map(function($t)
			{
				return $t->idorder;
			}, $records);

			$dbo->setQuery(
				$dbo->getQuery(true)
					->select($dbo->qn('o.id', 'idorder'))
					->select($dbo->qn('o.status', 'b_status'))
					->select($dbo->qn('o.days', 'b_nights'))
					->select($dbo->qn('o.checkin', 'b_checkin'))
					->select($dbo->qn('o.checkout', 'b_checkout'))
					->select($dbo->qn('c.id', 'id_customer'))
					->select($dbo->qn(['c.first_name', 'c.last_name', 'c.pic']))
					->from($dbo->qn('#__vikbooking_orders', 'o'))
					->leftjoin($dbo->qn('#__vikbooking_customers_orders', 'co') . ' ON ' . $dbo->qn('o.id') . ' = ' . $dbo->qn('co.idorder'))
					->leftjoin($dbo->qn('#__vikbooking_customers', 'c') . ' ON ' . $dbo->qn('co.idcustomer') . ' = ' . $dbo->qn('c.id'))
					->where($dbo->qn('o.id') . ' IN (' . implode(',', array_unique($orderIds)) . ')')
			);

			$orderDetails = [];

			foreach ($dbo->loadObjectList() as $details)
			{
				$orderDetails[$details->idorder] = $details;
			}

			foreach ($records as $thread)
			{
				// JSON decode attachments
				$thread->attachments = $thread->attachments ? (array) json_decode($thread->attachments) : [];

				if (isset($orderDetails[$thread->idorder]))
				{
					foreach ($orderDetails[$thread->idorder] as $key => $value)
					{
						$thread->{$key} = $value;
					}
				}

				$threads[] = $thread;
			}
		}

		return $threads;
	}

	/**
	 * Statically loads the necessary JS/CSS assets for rendering the chat.
	 * 
	 * @return  void
	 * 
	 * @since 	1.8.11
	 */
	public static function loadChatAssets()
	{
		static $chat_assets_loaded = null;

		if ($chat_assets_loaded) {
			return;
		}

		// make translations available also for JS scripts
		JText::script('VCM_CHAT_TODAY');
		JText::script('VCM_CHAT_YESTERDAY');
		JText::script('VCM_CHAT_SENDING_ERR');
		JText::script('VCM_CHAT_THREAD_TOPIC');
		JText::script('VCM_CHAT_TEXTAREA_PLACEHOLDER');
		JText::script('VCM_CHAT_THREAD_SUBJECT_DEFAULT');
		JText::script('VCM_TRANSLATE');
		JText::script('VCM_TRANSLATING');
		JText::script('VCM_ATTACH');
		JText::script('VCM_AI_CHAT_TOOLTIP');
		JText::script('VCM_AI_CHAT_AUTOREPLY_BADGE');
		JText::script('VCM_AI_CHAT_AUTOREPLY_BADGE_STOPPED');
		JText::script('VCM_AI_CHAT_AUTOREPLY_RESUME');
		JText::script('VCM_AI_CHAT_AUTOREPLY_STOP');
		JText::script('VCM_AI_CHAT_DRAFT_TITLE');
		JText::script('VCM_AI_CHAT_DRAFT_SEND');
		JText::script('VCM_MESSAGE_ACCSEC_PHISHING');
		JText::script('EDIT');

		JHtml::_('script', VCM_SITE_URI . 'assets/js/chat.js', [
			'version' => defined('VIKCHANNELMANAGER_SOFTWARE_VERSION') ? VIKCHANNELMANAGER_SOFTWARE_VERSION : '',
		]);
		JHtml::_('stylesheet', VCM_SITE_URI . 'assets/css/chat.css', [
			'version' => defined('VIKCHANNELMANAGER_SOFTWARE_VERSION') ? VIKCHANNELMANAGER_SOFTWARE_VERSION : '',
		]);

		try
		{
			// try to include chosen plugin
			JHtml::_('formbehavior.chosen');
		}
		catch (Exception $e)
		{
			// CHOSEN is not supported, just catch the error
		}

		// turn flag on for assets loaded
		$chat_assets_loaded = 1;
	}

	/**
	 * Class constructor.
	 * 
	 * @param 	mixed  $oid  VBO booking ID or an array containing ID and secret key.
	 * 
	 * @uses 	prepareOptions()
	 * @uses 	loadBookingDetails()
	 * @uses 	loadThreadsMessages()
	 * @uses 	loadUsers()
	 */
	public function __construct($oid)
	{
		// make sure the main library of Vik Channel Manager is available
		if (!class_exists('VikChannelManager'))
		{
			require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikchannelmanager.php';
		}

		if (VCMPlatformDetection::isWordPress())
		{
			// load VCM site language
			$lang = JFactory::getLanguage();
			$lang->load('com_vikchannelmanager', VIKCHANNELMANAGER_SITE_LANG);
			// load language site handler too
			$lang->attachHandler(VIKCHANNELMANAGER_LIBRARIES . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . 'site.php', 'vikchannelmanager');
		}
		else
		{
			// load VCM site language
			JFactory::getLanguage()->load('com_vikchannelmanager', JPATH_SITE);
		}

		if (is_array($oid))
		{
			// extract oid and secret key from array
			list($oid, $key) = $oid;
			// always cast to string and we cannot have a NULL value here
			$key = (string) $key;
		}
		else
		{
			$key = null;
		}

		// current VBO booking ID
		$this->id_order = (int) $oid;

		// prepare options, to allow child classes to set some options
		$this->prepareOptions();

		// load the current booking record
		$this->loadBookingDetails($key);

		// load all threads and related messages for this booking
		$this->loadThreadsMessages();

		// load users involved in the conversation and their related details
		$this->loadUsers();
	}

	/**
	 * Main method called by Vik Booking to render the messaging chat.
	 * Declares CSS and JS assets to prepare the output and controls.
	 * 
	 * @param 	array 	$extra_opts 	custom environment options for the Chat handler.
	 * @param 	bool 	$load_assets 	whether to load the JS/CSS assets.
	 * 
	 * @return  string 	the HTML content to be displayed by Vik Booking.
	 * 
	 * @since 	1.8.11 	added arguments to pass extra options or load the JS/CSS assets.
	 */
	public function renderChat(array $extra_opts = [], $load_assets = true)
	{
		// fill channel options array
		$options = [
			'syncTime' => $this->get('syncTime', null),
			'ai' => [
				'autoreply' => (new VCMAiModelSettings)->isMessagingAutoResponderEnabled(),
			],
		];

		// merge channel options with extra options
		$options = array_merge($options, $extra_opts);

		$app = JFactory::getApplication();
		if (method_exists($app, 'isClient'))
		{
			$client = $app->isClient('administrator') ? 'admin' : 'site';
		}
		else
		{
			$client = $app->isAdmin() ? 'admin' : 'site';
		}

		/**
		 * Check whether the user is currenty logged in as operator.
		 * 
		 * @since 1.9.7
		 */
		if ($client === 'site')
		{
			// access the global operators object
			$oper_obj = VikBooking::getOperatorInstance();

			// attempt to get the current operator
			$operator = $oper_obj->getOperatorAccount();

			if ($operator !== false)
			{
				// operator logged in, switch client to administrator
				$client = 'admin';
			}
		}

		/**
		 * Detect AJAX base URI environment depending on the platform.
		 * 
		 * @since 	1.8.11
		 */
		$ajax_uri = VCMFactory::getPlatform()->getUri()->ajax('index.php?option=com_vikchannelmanager&tmpl=component');

		$threads_json = json_encode($this->threads);
		$users_json   = json_encode($this->users);
		$channel_json = json_encode($options);
		$drafts_json  = json_encode($this->loadDrafts());
		$react_json   = json_encode($this->loadReactions());

		$hide_threads_style = '';

		/**
		 * In case "hideThreads" is equal to "2", the threads navigation bar will be displayed
		 * only if we have 2 or more threads.
		 * 
		 * @since 1.9
		 */
		if (($options['hideThreads'] ?? null) == 2)
		{
			// hide threads nav bar only if we have a single thread
			$options['hideThreads'] = count($this->threads) == 1 ? 1 : 0;
		}

		if (!empty($options['hideThreads']))
		{
			$hide_threads_style = 'display: none;';
		}

		$message_tmpl  = '<div class="chat-message" id="{id}">\n<div class="speech-user-avatar {avatar_class}">{avatar_img}</div>\n<div class="speech-bubble {class}">\n{message}\n</div>\n</div>\n';
		$thread_tmpl   = '<div class="thread-details">\n<div class="thread-heading">\n<div class="thread-recipient">{recipient}</div>\n<div class="thread-datetime">{datetime}</div>\n</div>\n<div class="thread-message"><div class="thread-content">{message}</div><div class="thread-notif">{notifications}</div></div>\n</div>';
		$datetime_tmpl = '<div class="chat-datetime-separator {class}" data-datetime="{utc}">{datetime}</div>';

		// load the JS/CSS assets
		if ($load_assets)
		{
			static::loadChatAssets();
		}

		// define translations to be used within the HTML template
		$translations = [
			'no_threads'  => JText::_('VCM_CHAT_NO_THREADS'),
			'placeholder' => JText::_('VCM_CHAT_TEXTAREA_PLACEHOLDER'),
		];

		$orderRooms = json_encode(VikBooking::loadOrdersRoomsData($this->id_order));

		$js_decl = 
<<<JS
(function($) {
	'use strict';

	$(function() {
		VCMChat.getInstance({
			environment: {
				url: '$ajax_uri',
				threads: $threads_json,
				users: $users_json,
				idOrder: {$this->id_order},
				orderRooms: {$orderRooms},
				channel: '{$this->channelName}',
				secret: '{$this->booking['ts']}',
				client: '$client',
				options: $channel_json,
				drafts: $drafts_json,
				reactions: $react_json,
			},
			element: {
				conversation: '#chat-conversation',
				threadsList:  '#chat-threads',
				noThreads: 	  '#no-threads',
				uploadsBar:   '#chat-uploads-tab',
				progressBar:  '#chat-progress-wrap',
				inputBox: 	  '#chat-input-box',
			},
			template: {
				message:  '$message_tmpl',
				thread:   '$thread_tmpl',
				datetime: '$datetime_tmpl',
			},
			lang: {
				today: 	   'VCM_CHAT_TODAY',
				yesterday: 'VCM_CHAT_YESTERDAY',
				senderr:   'VCM_CHAT_SENDING_ERR',
				newthread: 'VCM_CHAT_THREAD_TOPIC',
				texthint:  'VCM_CHAT_TEXTAREA_PLACEHOLDER',
				defthread: 'VCM_CHAT_THREAD_SUBJECT_DEFAULT',
			}
		}).prepare();
	});
})(jQuery);
JS;

		$html = 
<<<HTML
<div class="chat-border-layout">
	
	<div class="chat-threads-panel" style="$hide_threads_style">

		<div class="no-threads-box" id="no-threads">
			{$translations['no_threads']}
		</div>

		<ul class="chat-threads-list" id="chat-threads" style="display:none;">

		</ul>
	</div>

	<div class="chat-messages-panel">

		<div class="chat-conversation" id="chat-conversation">

		</div>

		<div class="chat-input-footer">
			<div class="textarea-input" id="chat-input-box"></div>

			<div class="chat-uploads-bar" style="display:none;">
				<div class="chat-progress-wrap" id="chat-progress-wrap"></div>
				<div class="chat-uploads-tab" id="chat-uploads-tab"></div>
			</div>
		</div>

	</div>

</div>

<script type="text/javascript">
	{$js_decl}
</script>
HTML;

		return $html;
	}

	/**
	 * Loads the messages of a specific VCM Thread ID, or from all Threads.
	 * The messages are loaded from an optional start index and limit.
	 * 
	 * @param 	integer  $start 	 The start index for the messages query.
	 * @param 	integer  $limit 	 The limit for the messages query.
	 * @param 	integer  $thread_id  The VCM thread id for the messages to read.
	 * @param 	string 	 $datetime 	 An optional datetime to exclude all the newer messages.
	 * @param 	integer  $min_id 	 The threshold identifier. Messages with equals or
	 * 								 lower ID won't be taken. Use NULL to ignore this filter.
	 * @param 	boolean  $unread 	 True to retrieve only unread messages, false for read messages only.
	 * 								 Use null to ignore this filter. 
	 * 
	 * @return 	array 	 The list of thread-message objects loaded.
	 * 
	 * @see 			 Some chat handlers may override this method.
	 */
	public function loadThreadsMessages($start = 0, $limit = 20, $thread_id = null, $datetime = null, $min_id = null, $unread = null)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select(array(
				$dbo->qn('t.id'),
				$dbo->qn('t.idorder'),
				$dbo->qn('t.idorderota'),
				$dbo->qn('t.channel'),
				$dbo->qn('t.ota_thread_id'),
				$dbo->qn('t.subject'),
				$dbo->qn('t.type'),
				$dbo->qn('t.last_updated'),
				$dbo->qn('t.no_reply_needed'),
				$dbo->qn('t.ai_stopped'),
			))
			->select('COUNT(1) AS `tot_messages`')
			->from($dbo->qn('#__vikchannelmanager_threads', 't'))
			->leftjoin($dbo->qn('#__vikchannelmanager_threads_messages', 'm') . ' ON ' . $dbo->qn('t.id') . ' = ' . $dbo->qn('m.idthread'))
			->where($dbo->qn('t.idorder') . ' = ' . $this->id_order);

		if ($thread_id)
		{
			$q->where($dbo->qn('t.id') . ' = ' . (int) $thread_id);
		}

		$q->group($dbo->qn('t.id'))
			->order($dbo->qn('t.last_updated') . ' DESC');

		$dbo->setQuery($q);
		$threads = $dbo->loadObjectList();

		if ($threads)
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikchannelmanager_threads_messages'))
				// @NOTE: do not place WHERE statement here as it is cleared within the FOREACH
				->order($dbo->qn('dt') . ' DESC');

			// read messages for each thread of this booking
			foreach ($threads as &$thread)
			{
				$thread->messages = array();

				$q->clear('where')->where($dbo->qn('idthread') . ' = ' . (int) $thread->id);

				if ($datetime)
				{
					// exclude all the messages newer than the specified date time
					$q->where($dbo->qn('dt') . ' <= ' . $dbo->q(JDate::getInstance($datetime)->toSql()));
				}

				if (!is_null($unread))
				{
					$q->where($dbo->qn('read_dt') . ' IS ' . ($unread ? 'NULL' : 'NOT NULL'));
				}

				if (!is_null($min_id))
				{
					// exclude all the messages older than the specified one (included)
					$q->where($dbo->qn('id') . ' > ' . (int) $min_id);
				}

				$dbo->setQuery($q, $start, $limit ? $limit : null);
				$records = $dbo->loadObjectList();

				if ($records)
				{
					// assign $this within a temporary variable as I fear
					// PHP 5.3 or lower doesn't support $this context
					// inside an anonymous function
					$chat = $this;

					// push messages for this thread
					$thread->messages = array_map(function($message) use($chat)
					{
						// decode attachments list
						$message->attachments = (array) json_decode($message->attachments);

						// fetch payload
						$message->payload = $chat->fetchPayload($message->payload);

						return $message;
					}, $records);
				}
			}

			// unset last reference
			unset($thread);

			// set threads messages
			// @update 	we cannot merge arrays because we would have duplicated entries
			// $this->threads = array_merge($this->threads, $threads);
			$this->threads = $threads;
		}

		return $threads;
	}

	/**
	 * Loads the details of the users involved in the conversation and their related details.
	 * 
	 * @return  array  An associative array of user objects involved in the chat.
	 * 
	 * @since   1.8.16
	 * @since   1.8.22  Involved co-hosts are loaded as well, if any.
	 * @since   1.9     The method now returns the cached value, if any.
	 */
	public function loadUsers()
	{
		if ($this->users) {
			// returned cached value
			return $this->users;
		}

		$users = [];

		$hotel_details = list($hotel_name, $hotel_logo) = $this->getHotelDetails();

		// set hotel information first
		$users['Hotel'] = [
			'full_name'  => htmlspecialchars($hotel_name),
			'initials' 	 => $this->getNameInitials($hotel_name),
			'first_name' => '',
			'last_name'  => '',
			'pic' 		 => $hotel_logo,
		];

		$dbo = JFactory::getDbo();

		$dbo->setQuery(
			$dbo->getQuery(true)
				->select($dbo->qn('o.id', 'idorder'))
				->select($dbo->qn('c.id', 'id_customer'))
				->select($dbo->qn(['c.first_name', 'c.last_name', 'c.pic']))
				->from($dbo->qn('#__vikbooking_orders', 'o'))
				->leftjoin($dbo->qn('#__vikbooking_customers_orders', 'co') . ' ON ' . $dbo->qn('o.id') . ' = ' . $dbo->qn('co.idorder'))
				->leftjoin($dbo->qn('#__vikbooking_customers', 'c') . ' ON ' . $dbo->qn('co.idcustomer') . ' = ' . $dbo->qn('c.id'))
				->where($dbo->qn('o.id') . ' = ' . (int) $this->id_order)
		);

		$guest = $dbo->loadObject();

		if ($guest && (!empty($guest->first_name) || !empty($guest->pic))) {
			$guest_nominative = trim($guest->first_name . ' ' . $guest->last_name);
			$guest_pic 		  = '';
			if (!empty($guest->pic)) {
				$guest_pic = strpos($guest->pic, 'http') === 0 ? $guest->pic : VBO_SITE_URI . 'resources/uploads/' . $guest->pic;
			}

			// set guest information
			$users['Guest'] = [
				'full_name'  => htmlspecialchars($guest_nominative),
				'initials' 	 => $this->getNameInitials($guest_nominative),
				'first_name' => htmlspecialchars($guest->first_name),
				'last_name'  => htmlspecialchars($guest->last_name),
				'pic' 		 => $guest_pic,
			];
		}

		// parse threads messages to gather a list of co-host and other-user IDs, if any
		$cohost_ids = [];
		$other_user_ids = [];
		foreach ($this->threads as $thread) {
			foreach ($thread->messages as $message) {
				if (!empty($message->cohost_id) && !in_array($message->cohost_id, $cohost_ids)) {
					$cohost_ids[] = $message->cohost_id;
				} elseif (!empty($message->user_id) && !in_array($message->user_id, $other_user_ids)) {
					$other_user_ids[] = $message->user_id;
				}
			}
		}

		if ($cohost_ids) {
			// fetch the involved co-host details
			try {
				$dbo->setQuery(
					$dbo->getQuery(true)
						->select('*')
						->from($dbo->qn('#__vikchannelmanager_threads_cohosts'))
						->where($dbo->qn('channel') . ' = ' . $dbo->q($this->channelName))
						->where($dbo->qn('id') . ' IN (' . implode(', ', array_map([$dbo, 'q'], $cohost_ids)) . ')')
				);

				$cohost_details = $dbo->loadObjectList();
			} catch (Throwable $e) {
				// prevent SQL errors for this later-introduced table
				$cohost_details = [];
			}

			foreach ($cohost_details as $cohost_info) {
				// set co-host user involved
				$user_key = 'Cohost' . $cohost_info->ota_cohost_id;

				$cohost_nominative = (string) $cohost_info->nominative;
				$cohost_picture    = '';
				if (!empty($cohost_info->pic)) {
					$cohost_picture = $cohost_info->pic;
					$cohost_picture = strpos($cohost_picture, 'http') === 0 ? $cohost_picture : VBO_SITE_URI . 'resources/uploads/' . $cohost_picture;
				}

				$users[$user_key] = [
					'full_name'  => htmlspecialchars($cohost_nominative),
					'initials' 	 => $this->getNameInitials($cohost_nominative),
					'first_name' => htmlspecialchars($cohost_nominative),
					'last_name'  => '',
					'pic' 		 => $cohost_picture,
				];

				// duplicate the co-host by using the ID key, rather than just the ota_cohost_id
				$users['Cohost' . $cohost_info->id] = $users[$user_key];
			}
		}

		/**
		 * Fetch the details for "other chat users", if any.
		 * 
		 * @since 	1.9.18
		 */
		if ($other_user_ids) {
			// fetch the involved chat user details
			try {
				$dbo->setQuery(
					$dbo->getQuery(true)
						->select('*')
						->from($dbo->qn('#__vikchannelmanager_threads_users'))
						->where($dbo->qn('channel') . ' = ' . $dbo->q($this->channelName))
						->where($dbo->qn('id') . ' IN (' . implode(', ', array_map([$dbo, 'q'], $other_user_ids)) . ')')
				);

				$other_user_details = $dbo->loadObjectList();
			} catch (Throwable $e) {
				// prevent SQL errors for this later-introduced table
				$other_user_details = [];
			}

			foreach ($other_user_details as $chat_user_info) {
				// set chat user involved
				$user_key = 'User' . $chat_user_info->ota_user_id;

				$chat_user_nominative = (string) $chat_user_info->nominative;
				$chat_user_picture    = '';
				if (!empty($chat_user_info->pic)) {
					$chat_user_picture = $chat_user_info->pic;
					$chat_user_picture = strpos($chat_user_picture, 'http') === 0 ? $chat_user_picture : VBO_SITE_URI . 'resources/uploads/' . $chat_user_picture;
				}

				$users[$user_key] = [
					'full_name'  => htmlspecialchars($chat_user_nominative),
					'initials' 	 => $this->getNameInitials($chat_user_nominative),
					'first_name' => htmlspecialchars($chat_user_nominative),
					'last_name'  => '',
					'pic' 		 => $chat_user_picture,
					'type'       => ($chat_user_info->type ?? '') ?: 'user',
				];

				// duplicate the chat user by using the ID key, rather than just the ota_user_id
				$users['User' . $chat_user_info->id] = $users[$user_key];
			}
		}

		// set users involved
		$this->users = $users;

		return $users;
	}

	/**
	 * Loads the UNREAD messages of a specific VCM Thread ID, or from all Threads.
	 * 
	 * @param 	integer  $thread_id  The VCM thread id for the messages to read.
	 * 
	 * @return 	array 	 The list of thread-message objects loaded.
	 */
	public function loadUnreadThreadsMessages($thread_id = null)
	{
		// get unread thread messages without limits
		$threads = $this->loadThreadsMessages($start = 0, $limit = null, $thread_id, $datetime = null, $min_id = null, $unread = true);

		// exclude all threads without unread messages
		return array_filter($threads, function($thread)
		{
			return (bool) count($thread->messages);
		});
	}

	/**
	 * Loads the MOST RECENTE messages of a specific VCM Thread ID, or from all Threads.
	 * 
	 * @param 	integer  $thread_id  The VCM thread id for the messages to read.
	 * @param 	integer  $min_id 	 The threshold identifier. Messages with equals or
	 * 								 lower ID won't be taken.
	 * 
	 * @return 	array 	 The list of thread-message objects loaded.
	 */
	public function loadRecentThreadsMessages($min_id = null, $thread_id = null)
	{
		// get unread thread messages without limits
		$threads = $this->loadThreadsMessages($start = 0, $limit = null, $thread_id, $datetime = null, $min_id);

		// exclude all threads without unread messages
		return array_filter($threads, function($thread)
		{
			return (bool) count($thread->messages);
		});
	}

	/**
	 * Gets the message record that matches the specified ID.
	 *
	 * @param 	integer  $id_message 	The message to get.
	 *
	 * @return 	mixed 	 The message found, otherwise null.
	 */
	public function getMessage($id_message)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikchannelmanager_threads_messages'))
			->where($dbo->qn('id') . ' = ' . (int) $id_message);

		$dbo->setQuery($q, 0, 1);
		$msg = $dbo->loadObject();

		if ($msg)
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikchannelmanager_threads'))
				->where($dbo->qn('id') . ' = ' . (int) $msg->idthread)
				// make sure the thread belong to this order
				->where($dbo->qn('idorder') . ' = ' . $this->id_order);

			$dbo->setQuery($q, 0, 1);
			$thread = $dbo->loadObject();

			// assign thread details to message object
			if (!$thread)
			{
				// we are probably trying to access a thread of
				// a different order
				return null;
			}

			$msg->thread = $thread;
		}

		return $msg;
	}

	/**
	 * Allow children classes to override this method
	 * and set some properties (like syncTime).
	 * 
	 * @return 	void
	 * 
	 * @since 	1.8.0
	 */
	protected function prepareOptions()
	{
		return;
	}

	/**
	 * Returns the hotel details as a list.
	 * 
	 * @return 	array 	list of hotel name and logo.
	 * 
	 * @since 	1.8.16
	 */
	protected function getHotelDetails()
	{
		$hotel_name = VikBooking::getFrontTitle();
		$hotel_logo = '';

		if (class_exists('VBOFactory'))
		{
			$vbo_config = VBOFactory::getConfig();
			if (JFactory::getApplication()->isClient('administrator'))
			{
				$hotel_logo = $vbo_config->get('backlogo', '');
			}
			if (!$hotel_logo) {
				$hotel_logo = $vbo_config->get('sitelogo', '');
			}
		}

		if (!empty($hotel_logo))
		{
			// uploaded logo found
			$hotel_logo = VBO_ADMIN_URI . 'resources/' . $hotel_logo;
		}

		return [$hotel_name, $hotel_logo];
	}

	/**
	 * Given the guest nominative or the hotel name, extracts the initials.
	 * 
	 * @param 	string 	$full_name 	the full guest nominative or hotel name.
	 * 
	 * @return 	string 				the initials of the name, max 2 chars, min 1 char.
	 * 
	 * @since 	1.8.16
	 */
	protected function getNameInitials($full_name)
	{
		// split nominative by white space
		$parts = preg_split("/\s+/", trim($full_name));

		// get the supposingly first and last name
		$first = (string)array_shift($parts);
		$last  = (string)array_pop($parts);

		// use the first letter for both first and last name
		if (!function_exists('mb_substr'))
		{
			return strtoupper(substr($first, 0, 1) . substr($last, 0, 1));
		}

		return strtoupper(mb_substr($first, 0, 1, 'UTF-8') . mb_substr($last, 0, 1, 'UTF-8'));
	}

	/**
	 * Loads the details of the current VBO booking ID.
	 *
	 * @param 	mixed 	$key 	The secret key to check.
	 * 							If not provided, it won't be validated.
	 * 
	 * @return  void
	 *
	 * @throws 	Exception 	In case the order doesn't exist/match the key.
	 */
	protected function loadBookingDetails($key = null)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select('`o`.*')
			->select($dbo->qn('m.last_check'))
			->from($dbo->qn('#__vikbooking_orders', 'o'))
			->leftjoin($dbo->qn('#__vikchannelmanager_order_messaging_data', 'm') . ' ON ' . $dbo->qn('o.id') . ' = ' . $dbo->qn('m.idorder'))
			->where($dbo->qn('o.id') . ' = ' . $this->id_order);

		if (!is_null($key))
		{
			$q->where($dbo->qn('o.ts') . ' = ' . $dbo->q($key));
		}
		
		$dbo->setQuery($q, 0, 1);
		$booking = $dbo->loadAssoc();
		
		if (!$booking)
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$this->booking = $booking;
	}

	/**
	 * Access the current booking details.
	 * 
	 * @return 	array
	 * 
	 * @since 	1.9.6
	 */
	public function getBooking()
	{
		return $this->booking;
	}

	/**
	 * Returns the list of loaded threads and the related messages.
	 *
	 * @return 	array
	 */
	public function getThreads()
	{
		return $this->threads;
	}

	/**
	 * Checks whether a thread exists from the given information.
	 * 
	 * @param 	mixed 	$data 	The record identifier or an object/array
	 * 							containing the query parameters, where the
	 * 							key is the column name and the property is
	 * 							the column value.
	 * 
	 * @return  mixed 	The VCM thread ID if exists, false otherwise.
	 *
	 * @uses 	_exists()
	 */
	public function threadExists($data)
	{
		return $this->_exists('#__vikchannelmanager_threads', 'id', $data);
	}

	/**
	 * Checks whether a thread message exists from the given information.
	 * 
	 * @param 	mixed 	$data 	The record identifier or an object/array
	 * 							containing the query parameters, where the
	 * 							key is the column name and the property is
	 * 							the column value.
	 * 
	 * @return  mixed 	The VCM message ID if exists, false otherwise.
	 *
	 * @uses 	_exists()
	 */
	public function messageExists($data)
	{	
		return $this->_exists('#__vikchannelmanager_threads_messages', 'id', $data);
	}

	/**
	 * Checks whether a table record exists according to the specified arguments.
	 * 
	 * @param 	string 	$table 	The name of the database table (not escaped).
	 * @param 	string 	$pk 	The primary key column name.
	 * @param 	mixed 	$query 	The record identifier or an object/array
	 * 							containing the query parameters, where the
	 * 							key is the column name and the property is
	 * 							the column value.
	 * 
	 * @return  mixed 	The requested primary key if exists, false otherwise.
	 */
	protected function _exists($table, $pk, $query)
	{
		// make sure we have a valid query
		if (!$query)
		{
			return false;
		}

		$dbo = JFactory::getDbo();

		if (is_scalar($query))
		{
			// check if record exists by ID in VCM
			$query = array($pk => $query);
		}

		$q = $dbo->getQuery(true)
			->select($dbo->qn($pk))
			->from($dbo->qn($table));

		foreach ((array) $query as $k => $v)
		{
			$q->where($dbo->qn($k) . ' = ' . $dbo->q($v));
		}

		$dbo->setQuery($q, 0, 1);
		$record = $dbo->loadResult();

		if ($record)
		{
			return $record;
		}
		
		return false;
	}

	/**
	 * Creates or updates a thread onto the database.
	 * 
	 * @param 	mixed 	$data 	An object or an associative array containing 
	 * 							the properties to store.
	 * 
	 * @return  mixed 	The VCM thread ID on success, false otherwise.
	 *
	 * @uses 	_save()
	 */
	public function saveThread($data)
	{
		if (is_object($data) && !isset($data->no_reply_needed))
		{
			// make sure to (re-)flag the thread as "reply needed"
			$data->no_reply_needed = 0;
		}
		elseif (is_array($data) && !isset($data['no_reply_needed']))
		{
			// make sure to (re-)flag the thread as "reply needed"
			$data['no_reply_needed'] = 0;
		}

		return $this->_save('#__vikchannelmanager_threads', 'id', $data);
	}

	/**
	 * Inserts or updates a thread message onto the database.
	 * 
	 * @param 	mixed 	$data 			An object or an associative array containing 
	 * 									the properties to store.
	 * @param 	bool 	$prev_replied 	Whether to mark the previous guest messages
	 * 									in the same thread as replied by the Hotel.
	 * 
	 * @return  mixed 	The VCM message ID on success, false otherwise.
	 *
	 * @uses 	_save()
	 * 
	 * @since 	1.8.27 	introduced 2nd argument $prev_replied.
	 */
	public function saveMessage($data, $prev_replied = true)
	{
		// always cast to object
		$data = (object) $data;

		/**
		 * Do not update the sender name when the sender is different than the guest.
		 * This is to keep the identification of the AI sender in case of thread sync.
		 * 
		 * @since 	1.9.0
		 */
		if (strcasecmp(($data->sender_type ?? 'guest'), 'Guest') && !empty($data->id)) {
			unset($data->sender_name);
		}

		// check whether we are creating a new message or if we are updating an existing one
		$isNew = empty($data->id);

		// save or update the thread message record
		$messageId = $this->_save('#__vikchannelmanager_threads_messages', 'id', $data);

		/**
		 * If the message was sent by the Hotel (or similar) to the Guest,
		 * mark all previous guest messages in the same thread as replied.
		 * 
		 * @since 1.8.27
		 * 
		 * Flag previous messages as replied only if we are registering a new message.
		 * 
		 * @since 1.9.15 
		 */
		if ($prev_replied === true && strcasecmp(($data->sender_type ?? 'guest'), 'Guest') && $isNew)
		{
			// message was NOT sent by the guest, so we update the records
			$dbo = JFactory::getDbo();

			$dbo->setQuery(
				$dbo->getQuery(true)
					->update($dbo->qn('#__vikchannelmanager_threads_messages'))
					->set($dbo->qn('replied') . ' = 1')
					->where($dbo->qn('idthread') . ' = ' . $dbo->q(($data->idthread ?? 0)))
					->where($dbo->qn('sender_type') . ' = ' . $dbo->q('guest'))
					->where($dbo->qn('dt') . ' < ' . $dbo->q(($data->dt ?? JDate::getInstance()->toSql())))
			);

			try
			{
				// prevent any SQL errors
				$dbo->execute();
			}
			catch (Exception $e)
			{
				// do nothing
			}
		}

		if ($isNew && !empty($data->id) && !empty($data->idthread)) {
			/**
			 * Push the saved message within the queue for asynchronous processing.
			 * 
			 * @since 1.9.14
			 */
			VCMFactory::getChatAsyncMediator()->enqueue($data);
		}

		return $messageId;
	}

	/**
	 * Inserts or updates a generic table record onto the database.
	 * 
	 * @param 	string 	$table 	The name of the database table (not escaped).
	 * @param 	string 	$pk 	The primary key column name.
	 * @param 	mixed 	$data 	An object or an associative array containing 
	 * 							the properties to store. 
	 * 
	 * @return  mixed 	The VCM message ID if exists, false otherwise.
	 */
	protected function _save($table, $pk, $data)
	{
		$dbo = JFactory::getDbo();

		// make sure we are handling an object
		$data = (object) $data;

		if (empty($data->{$pk}))
		{
			// insert new record
			if (!$dbo->insertObject($table, $data, $pk))
			{
				return false;
			}
		}
		else
		{
			// update existing record
			if (!$dbo->updateObject($table, $data, $pk))
			{
				return false;
			}
		}

		return $data->{$pk};
	}

	/**
	 * Checks whether new messages should be downloaded. Last download
	 * date and time is compared to the current execution date and time.
	 * If the number of elapsed minutes from the last retrieval date is
	 * greater than or equal to the given limit interval, new threads
	 * or new messages should be downloaded to update the conversation.
	 * 
	 * @param 	int 	 $interval 	The minimum number of minutes elapsed
	 * 
	 * @return 	boolean  True if new messages should be downloaded, false otherwise.
	 */
	public function shouldDownloadNew($interval = 30)
	{
		if (empty($this->booking['last_check']))
		{
			// last download dateTime not available, download new messages
			return true;
		}

		$last = JDate::getInstance($this->booking['last_check']);
		$now  = JDate::getInstance();
		// calculate the DateInterval object with the difference between the two dates
		$diff = $last->diff($now);

		if (!$diff)
		{
			// could not calculate the difference between the dates
			return false;
		}

		// count the difference between the two dates in minutes
		$elapsed = ($diff->y * 365 * 1440) + ($diff->m * 30 * 1440) + ($diff->d * 1440) + ($diff->h * 60) + $diff->i;

		return $elapsed >= $interval;
	}

	/**
	 * Downloads all threads and their related messages for the current
	 * VBO booking ID.
	 * 
	 * @return 	mixed
	 *
	 * @uses 	downloadThreads()
	 */
	public function sync()
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select(1)
			->from($dbo->qn('#__vikchannelmanager_order_messaging_data'))
			->where($dbo->qn('idorder') . ' = ' . $this->id_order);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			// update last synchronization date time
			$q = $dbo->getQuery(true)
				->update($dbo->qn('#__vikchannelmanager_order_messaging_data'))
				->set($dbo->qn('last_check') . ' = ' . $dbo->q(JDate::getInstance()->toSql()))
				->where($dbo->qn('idorder') . ' = ' . $this->id_order);
		}
		else
		{
			// insert last synchronization date time
			$q = $dbo->getQuery(true)
				->insert($dbo->qn('#__vikchannelmanager_order_messaging_data'))
				->columns($dbo->qn(array('last_check', 'idorder', 'idorderota')))
				->values($dbo->q(JDate::getInstance()->toSql()) . ', ' . $this->id_order . ', ' . $dbo->q($this->booking['idorderota']));
		}

		$dbo->setQuery($q);
		$dbo->execute();

		// download threads
		return $this->downloadThreads();
	}

	/**
	 * Marks the specified message as read.
	 * All the unread messages, that was posted previously and 
	 * that belong to the same thread, will be read too.
	 *
	 * @param 	integer  $id_message 	The message to read.
	 * @param 	string 	 $datetime 		An optional read datetime.
	 * 									If not provided, the current time will be used.
	 *
	 * @return 	object 	 A resulting object.
	 */
	public function readMessage($id_message, $datetime = 'now')
	{
		$dbo = JFactory::getDbo();

		// get message record
		$message = $this->getMessage($id_message);

		$result = new stdClass;
		$result->count    = 0;
		$result->datetime = JDate::getInstance($datetime)->toSql();

		if (!$message)
		{
			return $result;
		}

		// read all unread messages
		$q = $dbo->getQuery(true)
			->update($dbo->qn('#__vikchannelmanager_threads_messages'))
			// set read datetime as NOW
			->set($dbo->qn('read_dt') . ' = ' . $dbo->q($result->datetime))
			// consider only the messages that belong to the same thread
			->where($dbo->qn('idthread') . ' = ' . (int) $message->idthread)
			// take only the specified message and the previous ones
			->where($dbo->qn('id') . ' <= ' . (int) $message->id)
			// ignore already read  messages
			->where($dbo->qn('read_dt') . ' IS NULL');

		if (!strcasecmp($message->sender_type, 'hotel') || !strcasecmp($message->sender_type, 'host'))
		{
			// make sure to read messages sent by HOTEL
			$q->where('(' . $dbo->qn('sender_type') . ' = ' . $dbo->q('hotel') . ' OR ' . $dbo->qn('sender_type') . ' = ' . $dbo->q('host') . ')');
		}
		else
		{
			// make sure to read messages NOT sent by HOTEL
			$q->where('(' . $dbo->qn('sender_type') . ' <> ' . $dbo->q('hotel') . ' AND ' . $dbo->qn('sender_type') . ' <> ' . $dbo->q('host') . ')');
		}

		$dbo->setQuery($q);
		$dbo->execute();

		// obtain number of updated records
		$result->count = $dbo->getAffectedRows();

		if ($result->count)
		{
			// notify reading point to the channel, if supported
			$result->channel = $this->notifyReadingPoint($message);
		}

		return $result;
	}

	/**
	 * Returns the instance of the related user.
	 *
	 * @param 	boolean  $recipient  True to get an instance of the recipient user.
	 *
	 * @return 	VCMChatUser
	 */
	public function getUser($recipient = false)
	{
		$client = null;

		if ($recipient)
		{
			// negate the value of the client (0: admin, 1: site)
			$app = JFactory::getApplication();
			if (method_exists($app, 'isClient'))
			{
				$client = $app->isClient('administrator') ? 0 : 1;
			}
			else
			{
				$client = $app->isAdmin() ? 0 : 1;
			}
		}

		return VCMChatUser::getInstance($this->id_order, $client);
	}

	/**
	 * Check here if we are uploading a supported attachment.
	 * Children classes might inherit this method to specify
	 * their own supported types.
	 *
	 * @param 	array 	 $file 	The details of the uploaded file.
	 *
	 * @return 	boolean  True if supported, false otherwise.
	 */
	public function checkAttachment(array $file)
	{
		// make sure we have a MIME type
		if (empty($file['type']))
		{
			return false;
		}

		/**
		 * In case this method is not overwritten, accept only the following types:
		 *
		 * - IMAGE (image/*)
		 * - TXT (text/plain)
		 * - MD (text/markdown)
		 * - PDF (application/pdf)
		 * - ZIP (application/zip)
		 */
		return preg_match("/^image\/.+|application\/(?:pdf|zip)|text\/(?:plain|markdown)$/", $file['type']);
	}

	/**
	 * Fetches the specified payload. The given data must be converted into
	 * a standard form, readable by the system.
	 *
	 * Children classes might inherit this method as every channel can
	 * implement its own "answer-prediction" service.
	 *
	 * @param 	mixed 	$data 	The payload object or a JSON string.
	 *
	 * @return 	object 	The fetched payload.
	 */
	public function fetchPayload($data)
	{
		if (is_string($data))
		{
			// decode JSON string
			$data = (object) json_decode($data);
		}
		else
		{
			// cast data to object as we might have an array
			$data = (object) $data;
		}

		// do nothing here

		return $data;
	}

	/**
	 * Children classes can inherit this method and return true
	 * in case they support CHAT notifications.
	 *
	 * @return 	boolean  Always false.
	 */
	public function supportNotifications()
	{
		return false;
	}

	/**
	 * Upon receiving a new thread message, it may be necessary to store
	 * the co-host details internally to support co-host messages.
	 * 
	 * @param 	object 	$cohost 	the co-host object information.
	 *
	 * @return 	int  	co-host ID if details stored or fetched, or 0.
	 * 
	 * @since 	1.8.22
	 */
	public function parseCohostDetails($cohost)
	{
		$vcm_cohost_id = 0;

		if (!is_object($cohost) || empty($cohost->ota_id) || (empty($cohost->name) && empty($cohost->pic))) {
			// unexpected co-host payload
			return $vcm_cohost_id;
		}

		// check if the co-host exists
		$data = new stdClass;
		$data->ota_cohost_id = (string) $cohost->ota_id;
		$data->channel 		 = $this->channelName;

		try {
			// check if a co-host exists with the given OTA ID
			$current_cohost_id = $this->_exists('#__vikchannelmanager_threads_cohosts', 'id', $data);

			if ($current_cohost_id) {
				// update the co-host information for the current ID found
				$data->id = $current_cohost_id;
			}

			// always attempt to insert or update the co-host details
			if (!empty($cohost->name)) {
				$data->nominative = $cohost->name;
			}
			if (!empty($cohost->pic)) {
				$data->pic = $cohost->pic;
			}

			// insert or update the record
			$this->_save('#__vikchannelmanager_threads_cohosts', 'id', $data);

			if (!empty($data->id)) {
				// assign the VCM co-host ID
				$vcm_cohost_id = $data->id;
			}
		} catch (Throwable $e) {
			// do nothing in case of probable SQL errors
			return 0;
		}

		return $vcm_cohost_id;
	}

	/**
	 * Upon receiving a new thread message, it may be necessary to store
	 * the "other chat user" details internally to support their messages.
	 * 
	 * @param 	object 	$chatUser 	The other-chat-user object information.
	 * @param 	string 	$threadId 	The OTA thread ID involved.
	 *
	 * @return 	int  	chat user ID if details stored or fetched, or 0.
	 * 
	 * @since 	1.9.18
	 */
	public function parseOtherChatUserDetails(object $chatUser, string $threadId)
	{
		$vcm_chatuser_id = 0;

		if (!is_object($chatUser) || empty($chatUser->ota_id) || (empty($chatUser->name) && empty($chatUser->pic))) {
			// unexpected chat-user payload
			return $vcm_chatuser_id;
		}

		// check if the chat-user exists
		$data = new stdClass;
		$data->ota_thread_id = $threadId;
		$data->ota_user_id   = (string) $chatUser->ota_id;
		$data->channel       = $this->channelName;

		try {
			// check if a chat-user exists with the given OTA and thread IDs
			$current_chatuser_id = $this->_exists('#__vikchannelmanager_threads_users', 'id', $data);

			if ($current_chatuser_id) {
				// update the chat-user information for the current ID found
				$data->id = $current_chatuser_id;
			}

			// always attempt to insert or update the chat-user details
			if (!empty($chatUser->type)) {
				$data->type = $chatUser->type;
			}
			if (!empty($chatUser->name)) {
				$data->nominative = $chatUser->name;
			}
			if (!empty($chatUser->pic)) {
				$data->pic = $chatUser->pic;
			}

			// insert or update the record
			$this->_save('#__vikchannelmanager_threads_users', 'id', $data);

			if (!empty($data->id)) {
				// assign the VCM chat-user ID
				$vcm_chatuser_id = $data->id;
			}
		} catch (Throwable $e) {
			// do nothing in case of probable SQL errors
			return 0;
		}

		return $vcm_chatuser_id;
	}

	/**
	 * Loads the drafts generated by the AI for the internally saved threads.
	 * 
	 * @return  object[]
	 * 
	 * @since   1.9
	 */
	protected function loadDrafts()
	{
		if (JFactory::getApplication()->isClient('site')) {
			return [];
		}

		$db = JFactory::getDbo();

		$drafts = [];

		foreach ($this->threads as $thread) {
			if (!$thread->messages) {
				continue;
			}

			// take the latest draft for this thread
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__vikchannelmanager_threads_drafts'))
				->where($db->qn('idthread') . ' = ' . (int) $thread->id)
				// make sure the creation date of the draft is after the creation date of the last message
				->where($db->qn('dt') . ' > ' . $db->q($thread->messages[0]->dt))
				->order($db->qn('dt') . ' DESC');

			$db->setQuery($query, 0, 1);

			if ($draft = $db->loadObject()) {
				$draft->attachments = $draft->attachments ? json_decode($draft->attachments) : [];

				// convert a list of file names into a list of attachment objects
		        $draft->attachments = array_map([new VCMAiModelTraining, 'getAttachment'], $draft->attachments);

		        // get rid of missing attachments
		        $draft->attachments = array_values(array_filter($draft->attachments));

				$drafts[] = $draft;
			}
		}

		// remove all the drafts older than 7 days
		$db->setQuery(
			$db->getQuery(true)
				->delete($db->qn('#__vikchannelmanager_threads_drafts'))
				->where($db->qn('dt') . ' <= ' . $db->q(JFactory::getDate('-7 days')->toSql()))
		);

		$db->execute();

		return $drafts;
	}

	/**
	 * Loads the reactions left for the messages of the internal threads.
	 * 
	 * @return  object[]
	 * 
	 * @since   1.9
	 */
	protected function loadReactions()
	{
		if (!$this->threads) {
			return [];
		}

		$threadIds = array_map(function($thread) {
			return (int) $thread->id;
		}, $this->threads);

		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__vikchannelmanager_threads_messages_reactions'))
			->where($db->qn('idthread') . ' IN (' . implode(',', $threadIds) . ')');

		$db->setQuery($query);
		return $db->loadObjectList();
	}

	/**
	 * Creates or updates a reaction onto the database.
	 * 
	 * @param   mixed  $data  An object or an associative array containing 
	 *                        the properties to store.
	 * 
	 * @return  bool   True on success, false otherwise.
	 *
	 * @throws  InvalidArgumentException
	 */
	public function saveReaction($data)
	{
		$data = (object) $data;

		if (empty($data->idthread) || empty($data->idmessage)) {
			throw new InvalidArgumentException('Missing required data.', 400);
		}

		if (empty($data->dt)) {
			$data->dt = JFactory::getDate()->toSql();
		}

		$db = JFactory::getDbo();

		// check whether the same user already left a reaction for the provided message
		$query = $db->getQuery(true)
			->select($db->qn('id'))
			->from($db->qn('#__vikchannelmanager_threads_messages_reactions'))
			->where($db->qn('idthread') . ' = ' . (int) $data->idthread)
			->where($db->qn('idmessage') . ' = ' . (int) $data->idmessage);

		if (!empty($data->iduser)) {
			$query->where($db->qn('iduser') . ' = ' . $db->q($data->iduser));
		}

		$db->setQuery($query, 0, 1);
		$reactionId = $db->loadResult();

		if ($reactionId) {
			// do an update
			$data->id = (int) $reactionId;
			$result = $db->updateObject('#__vikchannelmanager_threads_messages_reactions', $data, 'id');
		} else {
			// insert a new reaction
			$result = $db->insertObject('#__vikchannelmanager_threads_messages_reactions', $data, 'id');
		}

		return (bool) $result;
	}

	/**
	 * Sends, if needed, and saves a message reaction.
	 * 
	 * @param 	$data 	mixed 	Object or assoc array with the reaction properties.
	 * 
	 * @return 	bool 			True on success, false otherwise.
	 * 
	 * @since 	1.9.18
	 */
	public function sendReaction($data)
	{
		// by default, saves the message reaction only
		return $this->saveReaction($data);
	}

	/**
	 * Downloads all threads and their related messages for the current
	 * VBO booking ID. Children classes must declare this method.
	 * 
	 * @return 	object 	The number of new threads and new messages stored.
	 */
	abstract protected function downloadThreads();

	/**
	 * Abstract method used to inform e4jConnect that the last-read point has changed.
	 *
	 * @param 	object 	 $message 	The message object. Thread details
	 * 								can be accessed through the "thread" property.
	 * 
	 * @return 	boolean  True on success, false otherwise.
	 */
	abstract protected function notifyReadingPoint($message);

	/**
	 * Sends a new message to the recipient by making the request to e4jConnect.
	 * The new thread and message should be immediately stored onto the db.
	 * 
	 * @param 	VCMChatMessage 	$message 	The message object to be sent.
	 * 
	 * @return 	mixed 			The stored thread and message on success, false otherwise.
	 */
	abstract public function send(VCMChatMessage $message);

	/**
	 * Sends a reply to the recipient by making the request to e4jConnect.
	 * The new message should be immediately stored onto the db.
	 * 
	 * @param 	VCMChatMessage 	$message 	The message object to be sent.
	 * 
	 * @return 	mixed 			The stored thread and message on success, false otherwise.
	 */
	abstract public function reply(VCMChatMessage $message);
}
