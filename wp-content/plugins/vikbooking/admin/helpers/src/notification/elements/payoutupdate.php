<?php
/** 
 * @package     VikBooking
 * @subpackage  core
 * @author      Alessio Gaggii - E4J s.r.l.
 * @copyright   Copyright (C) 2025 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Notification elements registry (Notification Center) of type "Payout Update".
 * 
 * @since 1.18.3 (J) - 1.8.3 (WP)
 */
class VBONotificationElementsPayoutupdate extends VBONotificationElements
{
    // update total payout and commissions during postflight
    use VBONotificationElementsTraitPayoutcompensation;
}
