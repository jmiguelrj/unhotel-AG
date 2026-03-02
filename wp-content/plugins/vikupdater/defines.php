<?php
/** 
 * @package     VikUpdater
 * @subpackage  core
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

// Plugin Version
define('VIKUPDATER_VERSION', '2.0.5');

// Base path
define('VIKUPDATER_BASE', dirname(__FILE__));

// URI Constant
define('VIKUPDATER_URI', plugin_dir_url(__FILE__));

// Define the minimum accepted PHP version
define('VIKUPDATER_MINIMUM_PHP', '7.0');

// Define the tested up to version with WP
define('VIKUPDATER_WP_CONFIRMED_VERSION', '6.8');
