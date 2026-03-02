<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      Alessio Gaggii - E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4jconnect.com | https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * The layout display data.
 * 
 * @var array  booking  An associative array holding the booking information.
 */
extract($displayData);

$adults = $children = 0;

// calculate total adults and children
foreach ($booking['rooms'] as $room) {
    $adults += $room['adults'];
    $children += $room['children'];
}

$config = VBOFactory::getConfig();

?>

<div class="addon-reservation-summary">

    <!-- STATUS -->

    <div class="addon-reservation-status">
        <?php if ($booking['status'] == 'confirmed'): ?>
            <span class="label label-success vbo-status-label"><?php echo JText::_('VBCONFIRMED'); ?></span>
        <?php elseif ($booking['status'] == 'standby'): ?>
            <span class="label label-warning vbo-status-label">
                <?php echo !empty($booking['type']) ? JText::_('VBO_BTYPE_' . strtoupper($booking['type'])) : JText::_('VBSTANDBY'); ?>
            </span>
        <?php else: ?>
            <span class="label label-error vbo-status-label"><?php echo JText::_('VBCANCELLED'); ?></span>
        <?php endif; ?>
    </div>

    <!-- CHECK-IN -->

    <div class="addon-reservation-checkin">
        <span class="addon-reservation-label"><?php echo JText::_('VBPVIEWORDERSFOUR'); ?></span>
        <span class="addon-reservation-value">
            <?php echo JHtml::_('date', $booking['checkin'], 'j M Y', date_default_timezone_get()); ?>
        </span>
    </div>

    <!-- ROOMS -->

    <div class="addon-reservation-rooms">
        <?php echo implode(', ', array_column($booking['rooms'], 'room_name')); ?>
    </div>

    <!-- GUESTS -->

    <div class="addon-reservation-guests">
        <span class="addon-reservation-label"><?php echo JText::_('VBPVIEWORDERSPEOPLE'); ?></span>
        <span class="addon-reservation-value">
            <?php
            if ($adults) {
                echo JText::plural('VBO_N_ADULTS', $adults);

                if ($children) {
                    echo ', ' . JText::plural('VBO_N_CHILDREN', $children);
                }
            } else {
                echo '--';
            }
            ?>
        </span>
    </div>

    <!-- NIGHTS -->

    <div class="addon-reservation-nights">
        <span class="addon-reservation-label"><?php echo JText::_('VBPVIEWORDERSSIX'); ?></span>
        <span class="addon-reservation-value"><?php echo $booking['days']; ?></span>
    </div>

    <!-- CUSTOMER -->

    <div class="addon-reservation-customer">
        <span class="addon-reservation-label"><?php echo JText::_('VBOCUSTOMER'); ?></span>
        <span class="addon-reservation-value">
            <?php
            if ($booking['closure']) {
                echo '<span style="color: var(--vbo-red-color)">' . JText::_('VBO_AITOOL_RESERVATION_CLOSURE') . '</span>';
            } else {
                $customer = trim(($booking['customer']['first_name'] ?? '') . ' ' . ($booking['customer']['last_name'] ?? ''));

                if (!$customer) {
                    $customer = $booking['customer']['email'] ?? '';
                }

                echo $customer ?: '--';

                if ($booking['customer']['pic'] ?? null) {
                    $avatar = $booking['customer']['pic'];

                    if (!preg_match("/^https?:/", $avatar)) {
                        $avatar = VBO_SITE_URI . 'resources/uploads/' . $avatar;
                    }
                    
                    ?><img src="<?php echo $avatar; ?>"><?php
                }
            }
            ?>
        </span>
    </div>

    <!-- TOTAL PRICE -->

    <div class="addon-reservation-total">
        <span class="addon-reservation-label"><?php echo JText::_('VBPVIEWORDERSSEVEN'); ?></span>
        <span class="addon-reservation-value">
            <span>
                <?php
                if (!$booking['chcurrency'] || !strcasecmp((string) $booking['chcurrency'], $config->get('currencyname'))) {
                    // display system currency symbol in case the OTA currency is not available or equal to the system one
                    echo $config->get('currencysymb');
                } else {
                    // different currency, use the one provided by the OTA
                    echo $booking['chcurrency'];
                }
                ?>
            </span>
            <span><?php echo VikBooking::numberFormat($booking['total']); ?></span>
        </span>
    </div>

</div>
