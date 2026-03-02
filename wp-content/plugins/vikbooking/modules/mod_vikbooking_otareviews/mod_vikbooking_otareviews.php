<?php
/**
 * @package     VikBooking
 * @subpackage  mod_vikbooking_otareviews
 * @author      Alessio Gaggii - E4J s.r.l
 * @copyright   Copyright (C) 2018 E4J s.r.l. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */
 
// no direct access
defined('ABSPATH') or die('No script kiddies please!');

if (!class_exists('VikRequest')) {
	//the module is probably loaded in Home Page or any other page where VBO is not available, but some other modules may be calling the main library. We need to load the portability Framework
	include(JPATH_SITE . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_vikbooking" . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "adapter" . DIRECTORY_SEPARATOR . "defines.php");
	include(JPATH_SITE . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_vikbooking" . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "adapter" . DIRECTORY_SEPARATOR . "request.php");
	include(JPATH_SITE . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_vikbooking" . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "adapter" . DIRECTORY_SEPARATOR . "error.php");
}
if (!class_exists('VikBooking')) {
	//the module is probably loaded in Home Page or any other page where VBO is not available. We need to load the main VBO library
	require_once(VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php");
}

require_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'helper.php');

VikBookingOtaReviewsHelper::importVboLib();
VikBookingOtaReviewsHelper::loadFontAwesome();
VikBookingOtaReviewsHelper::getTracker();

// get widget id
$randid = str_replace('mod_vikbooking_otareviews-', '', $params->get('widget_id', rand(1, 999)));
// get widget base URL
$baseurl = VBO_MODULES_URI;

// load data
$revsdata = VikBookingOtaReviewsHelper::loadReviewsData($params);

// basic params useful for the layout file
$channelaccount = (string)$params->get('channelaccount', '');
$revorscore = (int)$params->get('revsorscore', '1');
$introtxt = (string)$params->get('introtxt', '');

require JModuleHelper::getLayoutPath('mod_vikbooking_otareviews', $params->get('layout', 'default'));
