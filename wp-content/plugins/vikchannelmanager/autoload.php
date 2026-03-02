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

// include defines
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'defines.php';

// main library
require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikchannelmanager.php';

// configuration
require_once VCM_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'vcm_config.php';

// libraries autoloader is fetched from VBO
$adapters = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'vikbooking' . DIRECTORY_SEPARATOR . 'autoload.php';

if (!is_file($adapters))
{
	throw new RuntimeException('VikBooking must be installed on your website!', 404);
}

// include VikBooking's autoloader
require_once $adapters;

// import internal loader
JLoader::import('loader.loader', VIKCHANNELMANAGER_LIBRARIES);

// always load JControllerAdmin
JLoader::import('adapter.mvc.controllers.admin');

// load plugin dependencies
VikChannelManagerLoader::import('bc.mvc');
VikChannelManagerLoader::import('system.body');
VikChannelManagerLoader::import('system.builder');
VikChannelManagerLoader::import('system.install');
VikChannelManagerLoader::import('system.assets');
VikChannelManagerLoader::import('system.screen');
