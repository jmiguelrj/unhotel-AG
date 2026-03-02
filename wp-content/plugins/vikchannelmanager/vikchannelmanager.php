<?php
/*
Plugin Name:  VikChannelManager
Plugin URI:   https://vikwp.com
Description:  Hotels Channel Manager complementary plugin of Vik Booking.
Version:      1.9.18
Author:       E4J s.r.l.
Author URI:   https://vikwp.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  vikchannelmanager
Domain Path:  /languages
*/

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

// autoload dependencies
try
{
	require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'autoload.php';
}
catch (RuntimeException $e)
{
	// VikBooking is not installed or is not active!
    add_action('admin_notices', function() use ($e)
    {
        ?>
        <div class="notice is-dismissible notice-warning">
            <p><?php echo $e->getMessage(); ?></p>
        </div>
        <?php
    });

    // return to avoid breaking the website
    return;
}

// handle install/uninstall
register_activation_hook(__FILE__, array('VikChannelManagerInstaller', 'activate'));
register_deactivation_hook(__FILE__, array('VikChannelManagerInstaller', 'deactivate'));
register_uninstall_hook(__FILE__, array('VikChannelManagerInstaller', 'uninstall'));

// init Installer
add_action('init', array('VikChannelManagerInstaller', 'onInit'));
add_action('plugins_loaded', array('VikChannelManagerInstaller', 'update'));

// init pagination layout
VikChannelManagerBuilder::setupPaginationLayout();
// init html helpers
VikChannelManagerBuilder::setupHtmlHelpers();

/**
 * Added support for screen options.
 * Parameters such as the list limit can be changed from there.
 * 
 * Due to WordPress 5.4.2 changes, we need to attach
 * VikChannelManager to a dedicated hook in order to 
 * allow the update of the list limit.
 *
 * @since 1.7.5
 */
add_action('current_screen', array('VikChannelManagerScreen', 'options'));
add_filter('set-screen-option', array('VikChannelManagerScreen', 'saveOption'), 10, 3);
add_filter('set_screen_option_vikchannelmanager_list_limit', array('VikChannelManagerScreen', 'saveOption'), 10, 3);

// init Session
add_action('init', array('JSessionHandler', 'start'), 1);
add_action('wp_logout', array('JSessionHandler', 'destroy'));

// filter page link to rewrite URI
add_action('plugins_loaded', function()
{
	global $pagenow;

	$app   = JFactory::getApplication(); 
	$input = $app->input;

	// check if the URI contains option=com_vikchannelmanager
	if ($input->get('option') == 'com_vikchannelmanager')
	{
		// make sure we are not contacting the AJAX and POST end-points
		if (!wp_doing_ajax() && $pagenow != 'admin-post.php')
		{
			/**
			 * Include page in query string only if we are in the back-end,
			 * because WordPress 5.5 seems to break the page loading in case
			 * that argument has been included in query string.
			 *
			 * It is not needed to include this argument in the front-end
			 * as the page should lean on the reached shortcode only.
			 *
			 * @since 1.7.6
			 */
			if ($app->isAdmin())
			{
				// inject page=vikchannelmanager in GET superglobal
				$input->get->set('page', 'vikchannelmanager');
			}
		}
		else
		{
			// inject action=vikchannelmanager in GET superglobal for AJAX and POST requests
			$_GET['action'] = 'vikchannelmanager';
		}
	}
	elseif ($input->get('page') == 'vikchannelmanager' || $input->get('action') == 'vikchannelmanager')
	{
		// inject option=com_vikchannelmanager in GET superglobal
		$_GET['option'] = 'com_vikchannelmanager';
	}
});

// resolve possible conflicts with malcoded Themes/Plugins like "betheme"
add_action('plugins_loaded', function()
{
    $app = JFactory::getApplication();

    if ($app->input->get->get('page') === 'vikchannelmanager' && $app->input->get->getBool('forcecheck'))
    {
        $app->input->get->delete('forcecheck');
    }
});

// process the request and obtain the response
add_action('init', function()
{
	$app 	= JFactory::getApplication();
	$input 	= $app->input;

	// process VikChannelManager only if it has been requested via GET or POST
	if ($input->get('option') == 'com_vikchannelmanager' || $input->get('page') == 'vikchannelmanager')
	{
		VikChannelManagerBody::process();
	}
});

// load language files before VikBooking does (on "init")
add_action('after_setup_theme', function()
{
	/**
	 * Language files should no longer be loaded during 'plugins_loaded'.
	 * However, we load them before "init" so that VikBooking AJAX
	 * requests will have all VCM language definitions available.
	 * 
	 * @since 1.9.6
	 */
	VikChannelManagerBuilder::loadLanguage();
});

// handle AJAX requests
add_action('wp_ajax_vikchannelmanager', 'handle_vikchannelmanager_ajax');
add_action('wp_ajax_nopriv_vikchannelmanager', 'handle_vikchannelmanager_ajax');

function handle_vikchannelmanager_ajax()
{
	VikChannelManagerBody::getHtml();

	// die to get a valid response
	wp_die();
}

// setup admin menu
add_action('admin_menu', array('VikChannelManagerBuilder', 'setupAdminMenu'));

// register widgets
add_action('widgets_init', array('VikChannelManagerBuilder', 'setupWidgets'));

// the callback is fired before the VCM controller is dispatched
add_action('vikchannelmanager_before_dispatch', function()
{
	$app 	= JFactory::getApplication();
	$user 	= Jfactory::getUser();

	// initialize timezone handler
	JDate::getDefaultTimezone();
	date_default_timezone_set($app->get('offset', 'UTC'));

	// check if the user is authorised to access the back-end (only if the client is 'admin')
	if ($app->isAdmin() && !$user->authorise('core.manage', 'com_vikchannelmanager'))
	{
		if ($user->guest)
		{
			// if the user is not logged, redirect to login page
			$app->redirect('index.php');
			exit;
		}
		else
		{
			// otherwise raise an exception
			wp_die(
				'<h1>' . JText::_('FATAL_ERROR') . '</h1>' .
				'<p>' . JText::_('RESOURCE_AUTH_ERROR') . '</p>',
				403
			);
		}
	}

	/**
	 * Normalize db driver and script declarations (if necessary).
	 * 
	 * @since 	1.7.5
	 */
	VikChannelManager::normalizeExecution();

	// require the helper file for both environments
	require_once VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'helper.php';

	/**
	 * Make sure the icons library is always loaded on any environment.
	 * 
	 * @since 	1.9.0
	 */
	VCM::requireFontAwesome();

	if ($app->isAdmin())
	{
		new OrderingManager('com_vikchannelmanager', 'vcmordcolumn', 'vcmordtype');

		// Trigger reports
		VikChannelManager::notifyReportsData();

		// Trigger reminders
		VikChannelManager::checkSubscriptionReminder();

		// Trigger auto bulk actions
		VikChannelManager::autoBulkActions();

		// Trigger schedules for failed data transmission
		VCMRequestScheduler::getInstance()->retry();
	}
});

// the callback is fired once the VCM controller has been dispatched
add_action('vikchannelmanager_after_dispatch', function()
{
	// load assets after dispatching the controller to avoid
	// including JS and CSS when an AJAX function exits or dies
	VikChannelManagerAssets::load();

	/**
	 * Load javascript core.
	 *
	 * @since 1.1.8
	 */
	JHtml::_('behavior.core');

	// restore standard timezone
	date_default_timezone_set(JDate::getDefaultTimezone());

	/**
	 * @note 	when the headers have been sent or when 
	 * 			the request is AJAX, the assets (CSS and JS) are
	 * 			appended to the document after the 
	 * 			response dispatched by the controller.
	 */
});

/**
 * Action triggered before loading the text domain.
 * For Vik Booking, VCM needs to attach both hanlders.
 *
 * @param 	string 	$domain    The plugin text domain to look for.
 * @param 	string 	$basePath  The base path containing the languages.
 * @param 	mixed   $langtag   An optional language tag to use.
 * 
 * @since   1.8.1
 */
add_action('vik_plugin_before_load_language', function($domain, $basePath, $langtag)
{
	if ($domain != 'vikbooking')
	{
		// do not proceed, as no language handlers for VCM are needed
		return;
	}

	$app 	= JFactory::getApplication();
	$input 	= $app->input;
	if ($input->get('option') != 'com_vikchannelmanager' && $input->get('page') == 'vikchannelmanager')
	{
		// do not proceed, it is not Vik Channel Manager that is loading the VBO language
		return;
	}

	// VBO base libraries path for language handlers
	$handler_base = str_replace('vikchannelmanager', $domain, VIKCHANNELMANAGER_LIBRARIES) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;

	$lang = JFactory::getLanguage();

	// load back-end language handler
	$lang->attachHandler($handler_base . 'admin.php', $domain);
	// load front-end language handler
	$lang->attachHandler($handler_base . 'site.php', $domain);
}, 10, 3);

/**
 * Action triggered before loading the text domain.
 * Loads the language handlers when needed from a 
 * different application client.
 *
 * @param 	string 	$domain    The plugin text domain to look for.
 * @param 	string 	$basePath  The base path containing the languages.
 * @param 	mixed   $langtag   An optional language tag to use.
 *
 * @since 	1.8.11
 */
add_action('vik_plugin_before_load_language', function($domain, $basePath, $langtag)
{
	if ($domain != 'vikchannelmanager')
	{
		// do not go ahead
		return;
	}

	$app  = JFactory::getApplication();
	$lang = JFactory::getLanguage();

	$handler = VIKCHANNELMANAGER_LIBRARIES . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;

	// check if we are in the site client and the system
	// needs to load the language used in the back-end
	if ($app->isSite() && $basePath == VIKCHANNELMANAGER_ADMIN_LANG)
	{
		// load back-end language handler
		$lang->attachHandler($handler . 'admin.php', $domain);
	}
	// check if we are in the admin client and the system
	// needs to load the language used in the front-end
	else if ($app->isAdmin() && $basePath == VIKCHANNELMANAGER_SITE_LANG)
	{
		// load front-end language handler
		$lang->attachHandler($handler . 'site.php', $domain);
	}
}, 10, 3);

/**
 * Added support for Loco Translate.
 * In case some translations have been edited by using this plugin,
 * we should look within the Loco Translate folder to check whether
 * the requested translation is available.
 *
 * @param 	boolean  $loaded  True if the translation has been already loaded.
 * @param 	string 	 $domain  The plugin text domain to load.
 *
 * @return 	boolean  True if a new translation is loaded.
 *
 * @since 	1.8.11
 */
add_filter('vik_plugin_load_language', function($loaded, $domain)
{
	// proceed only in case the translation hasn't been loaded
	// and Loco Translate plugin is installed
	if (!$loaded && is_dir(WP_LANG_DIR . DIRECTORY_SEPARATOR . 'loco'))
	{
		// Build LOCO path.
		// Since load_plugin_textdomain accepts only relative paths, 
		// we should go back to the /wp-contents/ folder first.
		$loco = implode(DIRECTORY_SEPARATOR, array('..', 'languages', 'loco', 'plugins'));

		// try to load the plugin translation from Loco folder
		$loaded = load_plugin_textdomain($domain, false, $loco);
	}

	return $loaded;
}, 10, 2);

// End-point for front-end post actions.
// The end-point URL must be built as .../wp-admin/admin-post.php
// and requires $_POST['action'] == 'vikchannelmanager' to be submitted through a form or GET.
add_action('admin_post_vikchannelmanager', 'handle_vikchannelmanager_endpoint'); 			// if the user is logged in
add_action('admin_post_nopriv_vikchannelmanager', 'handle_vikchannelmanager_endpoint'); 	// if the user in not logged in

// handle POST end-point
function handle_vikchannelmanager_endpoint()
{
	// get PLAIN response
	echo VikChannelManagerBody::getResponse();
}

/**
 * Action used to register a periodic check of the failure data transmission.
 * This hook will be called by a scheduled event in WP-Cron.
 * 
 * @since 1.8.20
 */
add_action('vikchannelmanager_cron_schedules_retry', function()
{
	// retry schedules for failed data transmission
	VCMRequestScheduler::getInstance()->retry($force = true);
});

/**
 * Action used to register a periodic check of the pending lock records.
 * This hook will be called by a scheduled event in WP-Cron.
 * 
 * @since 1.8.20
 */
add_action('vikchannelmanager_cron_pending_locks', function()
{
	// monitor ongoing and expired pending lock records
	VCMRequestAvailability::getInstance()->monitorPendingLocks($force = true);
});

/**
 * Action used to register a periodic monitoring of the non-answered guest messages.
 * This hook will be called by a scheduled event in WP-Cron.
 * 
 * @since 1.8.21
 */
add_action('vikchannelmanager_cron_messaging_autoresponder', function()
{
	// monitor guest messages that require an automatic response
	VCMChatAutoresponder::getInstance()->watchSchedules();
});

/**
 * Action used to register a periodic task to extract the topics from the guest questions.
 * This hook will be called by a scheduled event in WP-Cron.
 * 
 * @since 1.9
 */
add_action('vikchannelmanager_cron_ai_extract_topics', function() {
	(new VCMAiCronTopics)->extract();
});

/**
 * Action used to let the AI auto-replies to the guest messages.
 * This hook will be called by a scheduled event in WP-Cron.
 * 
 * @since 1.9
 */
add_action('vikchannelmanager_cron_ai_autoreply_messages', function() {
	(new VCMAiCronMessages)->autoReply();
});

/**
 * Action used to let the AI auto-replies to the reviews left by the guests.
 * This hook will be called by a scheduled event in WP-Cron.
 * 
 * @since 1.9
 */
add_action('vikchannelmanager_cron_ai_autoreply_reviews', function() {
	(new VCMAiCronReviews)->autoReply($pastDays = 7, $maxReplies = 2);
});

/**
 * Action used to let the AI auto-reviews the guests.
 * This hook will be called by a scheduled event in WP-Cron.
 * 
 * @since 1.9
 */
add_action('vikchannelmanager_cron_ai_autoreview_guests', function() {
	(new VCMAiCronGuests)->autoReview($maxReviews = 2);
});

/**
 * Action used to process the enqueued chat messages asynchronously.
 * This hook will be called by a scheduled event in WP-Cron.
 * 
 * @since 1.9.14
 */
add_action('vikchannelmanager_cron_chat_async_processor', function() {
	VCMFactory::getChatAsyncMediator()->process($jobs = 5);
});

/**
 * Install the scheduling of the hooks within WP-Cron to retry the failure data transmissions
 * and to monitor the pending locks to eventually unlock the rooms for unpaid reservations.
 * Priority (3rd) argument must be set to PHP_INT_MAX because Vik Booking uses (PHP_INT_MAX - 1)
 * to register all the interval schedules, including the 'every_5_minutes' that we need.
 * 
 * @since 1.8.20
 * @since 1.8.21  Added new runtime cron for the guest messages autoresponder.
 * @since 1.9     Added new runtime cron for the message topics extractor.
 */
add_action('plugins_loaded', function()
{
	// Make sure the cron event hasn't been yet scheduled.
	// After its execution, wp_next_scheduled will return false and
	// we will be able to register it again.
	if (!wp_next_scheduled('vikchannelmanager_cron_schedules_retry'))
	{
		// schedule event starting from the current time for every minute, by
		// launching the cron listener hook (3rd argument)
		// the interval 'every_5_minutes' should have been installed by VikBooking already
		wp_schedule_event(time(), 'every_5_minutes', 'vikchannelmanager_cron_schedules_retry');
	}

	if (!wp_next_scheduled('vikchannelmanager_cron_pending_locks'))
	{
		wp_schedule_event(time(), 'every_5_minutes', 'vikchannelmanager_cron_pending_locks');
	}

	if (!wp_next_scheduled('vikchannelmanager_cron_messaging_autoresponder'))
	{
		// the schedule "every_15_minutes" is installed by VikBooking v >= 1.6.6
		if (!wp_schedule_event(time(), 'every_15_minutes', 'vikchannelmanager_cron_messaging_autoresponder'))
		{
			// fallback to "half_hour" schedule which has been available for longer
			wp_schedule_event(time(), 'half_hour', 'vikchannelmanager_cron_messaging_autoresponder');
		}
	}

	if (!wp_next_scheduled('vikchannelmanager_cron_chat_async_processor'))
	{
		wp_schedule_event(time(), 'every_5_minutes', 'vikchannelmanager_cron_chat_async_processor');
	}

	// AI-related tasks
	if (VikChannelManager::getChannel(VikChannelManagerConfig::AI))
	{
		if (!wp_next_scheduled('vikchannelmanager_cron_ai_extract_topics'))
		{
			wp_schedule_event(time(), 'every_5_minutes', 'vikchannelmanager_cron_ai_extract_topics');
		}

		if (!wp_next_scheduled('vikchannelmanager_cron_ai_autoreply_messages'))
		{
			wp_schedule_event(time(), 'every_5_minutes', 'vikchannelmanager_cron_ai_autoreply_messages');
		}

		if (!wp_next_scheduled('vikchannelmanager_cron_ai_autoreply_reviews'))
		{
			wp_schedule_event(time(), 'hourly', 'vikchannelmanager_cron_ai_autoreply_reviews');
		}

		if (!wp_next_scheduled('vikchannelmanager_cron_ai_autoreview_guests'))
		{
			wp_schedule_event(time(), 'hourly', 'vikchannelmanager_cron_ai_autoreview_guests');
		}
	}
}, PHP_INT_MAX);
