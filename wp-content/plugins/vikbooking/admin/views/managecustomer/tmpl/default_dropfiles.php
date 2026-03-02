<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Render the customer-dropfiles layout to handle the customer documents.
 * 
 * @since 	1.16.10 (J) - 1.6.10 (WP)  template moved to layout file.
 */
$layout_data = [
	'caller' => 'view',
	'customer' => $this->customer,
];

// render the permissions layout
echo JLayoutHelper::render('customer.dropfiles', $layout_data);
