<?php
/** 
 * @package   	VikChannelManager
 * @subpackage 	core
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

// Software version
define('VIKCHANNELMANAGER_SOFTWARE_VERSION', '1.9.18');

// Base path
define('VIKCHANNELMANAGER_BASE', dirname(__FILE__));

// Libraries path
define('VIKCHANNELMANAGER_LIBRARIES', VIKCHANNELMANAGER_BASE . DIRECTORY_SEPARATOR . 'libraries');

// Languages path
define('VIKCHANNELMANAGER_SITE_LANG', basename(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'language');
define('VIKCHANNELMANAGER_ADMIN_LANG', basename(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'language');
/**
 * The admin and site languages are no more used by the plugin.
 *
 * @deprecated 	1.6.18
 * @see 		these constants won't be removed as some classes of VCM may need them.
 */
defined('VIKBOOKING_SITE_LANG') or define('VIKBOOKING_SITE_LANG', str_replace('vikchannelmanager', 'vikbooking', VIKCHANNELMANAGER_SITE_LANG));
defined('VIKBOOKING_ADMIN_LANG') or define('VIKBOOKING_ADMIN_LANG', str_replace('vikchannelmanager', 'vikbooking', VIKCHANNELMANAGER_ADMIN_LANG));
// Languages path for VBO
defined('VIKBOOKING_LANG') or define('VIKBOOKING_LANG', str_replace('vikchannelmanager', 'vikbooking', basename(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'languages'));

// Assets URI
define('VIKCHANNELMANAGER_SITE_ASSETS_URI', plugin_dir_url(__FILE__) . 'site/assets/');
define('VIKCHANNELMANAGER_ADMIN_ASSETS_URI', plugin_dir_url(__FILE__) . 'admin/assets/');

// URI Constants for admin and site sections (with trailing slash)
defined('VCM_ADMIN_URI') or define('VCM_ADMIN_URI', plugin_dir_url(__FILE__).'admin/');
defined('VCM_SITE_URI') or define('VCM_SITE_URI', plugin_dir_url(__FILE__).'site/');
defined('VBO_ADMIN_URI') or define('VBO_ADMIN_URI', str_replace('vikchannelmanager', 'vikbooking', VCM_ADMIN_URI));
defined('VBO_SITE_URI') or define('VBO_SITE_URI', str_replace('vikchannelmanager', 'vikbooking', VCM_SITE_URI));

// Path Constants for admin and site sections (with NO trailing directory separator)
defined('VCM_ADMIN_PATH') or define('VCM_ADMIN_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'admin');
defined('VCM_SITE_PATH') or define('VCM_SITE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'site');
defined('VBO_ADMIN_PATH') or define('VBO_ADMIN_PATH', str_replace('vikchannelmanager', 'vikbooking', VCM_ADMIN_PATH));
defined('VBO_SITE_PATH') or define('VBO_SITE_PATH', str_replace('vikchannelmanager', 'vikbooking', VCM_SITE_PATH));

// Other Constants that may not be available in the framework
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

/**
 * We define the base path constant for the Vik Booking upload
 * dir used to upload the customer documents onto the sub-dirs.
 * 
 * @since 	1.7.4
 */
$customer_upload_base_path = VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources';
$customer_upload_base_uri = VBO_ADMIN_URI . 'resources/';
$upload_dir = wp_upload_dir();
if (is_array($upload_dir) && !empty($upload_dir['basedir']) && !empty($upload_dir['baseurl'])) {
	$customer_upload_base_path = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'vikbooking';
	$customer_upload_base_uri = rtrim($upload_dir['baseurl'], '/') . '/' . 'vikbooking' . '/';
}
defined('VBO_CUSTOMERS_PATH') or define('VBO_CUSTOMERS_PATH', $customer_upload_base_path);
defined('VBO_CUSTOMERS_URI') or define('VBO_CUSTOMERS_URI', $customer_upload_base_uri);

/**
 * Site pre-process flag.
 * When this flag is enabled, the plugin will try to dispatch the
 * site controller within the "init" action. This is made by 
 * fetching the shortcode assigned to the current URI.
 *
 * By disabling this flag, the site controller will be dispatched 
 * with the headers already sent.
 */
define('VIKCHANNELMANAGER_SITE_PREPROCESS', true);
