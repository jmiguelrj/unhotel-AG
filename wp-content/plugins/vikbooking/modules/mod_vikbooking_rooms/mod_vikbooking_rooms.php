<?php
/**
 * @package     VikBooking
 * @subpackage  mod_vikbooking_rooms
 * @author      Alessio Gaggii - E4J s.r.l
 * @copyright   Copyright (C) 2018 E4J s.r.l. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

// no direct access
defined('ABSPATH') or die('No script kiddies please!');

defined('DS') or define('DS', DIRECTORY_SEPARATOR);

require_once VBO_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'lib.vikbooking.php';

if (method_exists('VikBooking', 'loadPreferredColorStyles')) {
	VikBooking::loadPreferredColorStyles();
}

// get widget id
$randid = str_replace('mod_vikbooking_horizontalsearch-', '', $params->get('widget_id', rand(1, 999)));
// get widget base URL
$baseurl = VBO_MODULES_URI;

// Include the syndicate functions only once
require_once dirname(__FILE__) . DS . 'helper.php';

$params->def('numb', 4);
$params->def('query', 'price');
$params->def('order', 'asc');
$params->def('catid', 0);
$params->def('querycat', 'price');
$params->def('currency', '&euro;');
$params->def('showcatname', 1);
$showcatname = intval($params->get('showcatname')) == 1 ? true : false;

$rooms = modvikbooking_roomsHelper::getRooms($params);

if (!$rooms) {
    return;
}

$rooms = modvikbooking_roomsHelper::limitRes($rooms, $params);

require JModuleHelper::getLayoutPath('mod_vikbooking_rooms', $params->get('layout', 'default'));
