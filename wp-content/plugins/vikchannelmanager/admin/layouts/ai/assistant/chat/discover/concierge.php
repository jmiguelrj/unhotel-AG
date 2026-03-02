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

$sections = &$displayData['sections'];

// load the first 5 published listings
$listings = array_values(VikBooking::getAvailabilityInstance(true)->loadRooms([], 5));

// currency symbol
$currencysymb = VikBooking::getCurrencySymb();

// build the discover sections
$sections = [
    // statistics
    [
        'title' => JText::_('VBO_AI_DISC_FN_STATS_TITLE'),
        'description' => JText::_('VBO_AI_DISC_FN_STATS_DESCR'),
        'icon' => VikBookingIcons::i('calculator'),
        'options' => [
            // bookings count
            [
                'summary' => JText::_('VBO_AI_DISC_FN_STATS_BC_SUMM'),
                'example' => JText::_('VBO_AI_DISC_FN_STATS_BC_EXA'),
                'role'    => 'get',
            ],
            // bookings revenue
            [
                'summary' => JText::_('VBO_AI_DISC_FN_STATS_BR_SUMM'),
                'example' => JText::sprintf('VBO_AI_DISC_FN_STATS_BR_EXA', ($listings[rand(0, count($listings) - 1)]['name'] ?? 'Double Room Deluxe')),
                'role'    => 'get',
            ],
            // average los
            [
                'summary' => JText::_('VBO_AI_DISC_FN_STATS_LOS_SUMM'),
                'example' => JText::_('VBO_AI_DISC_FN_STATS_LOS_EXA'),
                'role'    => 'get',
            ],
        ],
    ],
    // bookings search
    [
        'title' => JText::_('VBO_AI_DISC_FN_BSRCH_TITLE'),
        'description' => JText::_('VBO_AI_DISC_FN_BSRCH_DESCR'),
        'icon' => VikBookingIcons::i('search'),
        'options' => [
            // find check-ins/outs
            [
                'summary' => JText::_('VBO_AI_DISC_FN_BSRCH_IO_SUMM'),
                'example' => JText::_('VBO_AI_DISC_FN_BSRCH_IO_EXA'),
                'role'    => 'get',
            ],
            // search by customer information
            [
                'summary' => JText::_('VBO_AI_DISC_FN_BSRCH_CI_SUMM'),
                'example' => JText::_('VBO_AI_DISC_FN_BSRCH_CI_EXA'),
                'role'    => 'get',
            ],
        ],
    ],
    // reminders
    [
        'title' => JText::_('VBO_AI_DISC_FN_REMIND_TITLE'),
        'description' => JText::_('VBO_AI_DISC_FN_REMIND_DESCR'),
        'icon' => VikBookingIcons::i('list-alt'),
        'options' => [
            // set a generic reminder
            [
                'summary' => JText::_('VBO_AI_DISC_FN_REMIND_GR_SUMM'),
                'example' => JText::sprintf('VBO_AI_DISC_FN_REMIND_GR_EXA', ($listings[rand(0, count($listings) - 1)]['name'] ?? 'Double Room Deluxe')),
                'role'    => 'put',
            ],
            // set a reminder for a booking
            [
                'summary' => JText::_('VBO_AI_DISC_FN_REMIND_BR_SUMM'),
                'example' => JText::_('VBO_AI_DISC_FN_REMIND_BR_EXA'),
                'role'    => 'put',
            ],
        ],
    ],
    // room rates (search and modify)
    [
        'title' => JText::_('VBO_AI_DISC_FN_RR_TITLE'),
        'description' => JText::_('VBO_AI_DISC_FN_RR_DESCR'),
        'icon' => VikBookingIcons::i('dollar-sign'),
        'options' => [
            // get room quotes
            [
                'summary' => JText::_('VBO_AI_DISC_FN_RR_RQ_SUMM'),
                'example' => JText::_('VBO_AI_DISC_FN_RR_RQ_EXA'),
                'role'    => 'get',
            ],
            // calculate pricing
            [
                'summary' => JText::_('VBO_AI_DISC_FN_RR_CP_SUMM'),
                'example' => JText::sprintf('VBO_AI_DISC_FN_RR_CP_EXA', ($listings[rand(0, count($listings) - 1)]['name'] ?? 'Double Room Deluxe')),
                'role'    => 'put',
            ],
            // modify rates and restrictions
            [
                'summary' => JText::_('VBO_AI_DISC_FN_RR_MR_SUMM'),
                'example' => JText::sprintf('VBO_AI_DISC_FN_RR_MR_EXA', ($listings[rand(0, count($listings) - 1)]['name'] ?? 'Double Room Deluxe'), $currencysymb),
                'role'    => 'put',
            ],
            // lower website rates
            [
                'summary' => JText::_('VBO_AI_DISC_FN_RR_LR_SUMM'),
                'example' => JText::sprintf(
                    'VBO_AI_DISC_FN_RR_RQ_EXA',
                    ($listings[rand(0, count($listings) - 1)]['name'] ?? 'Double Room Deluxe'),
                    $currencysymb
                ),
                'role'    => 'put',
            ],
        ],
    ],
    // room ari
    [
        'title' => JText::_('VBO_AI_DISC_FN_ARI_TITLE'),
        'description' => JText::_('VBO_AI_DISC_FN_ARI_DESCR'),
        'icon' => VikBookingIcons::i('calendar'),
        'options' => [
            // get rooms available
            [
                'summary' => JText::_('VBO_AI_DISC_FN_ARI_RA_SUMM'),
                'example' => JText::_('VBO_AI_DISC_FN_ARI_RA_EXA'),
                'role'    => 'get',
            ],
            // availability for a specific room
            [
                'summary' => JText::_('VBO_AI_DISC_FN_ARI_AV_SUMM'),
                'example' => JText::sprintf('VBO_AI_DISC_FN_ARI_AV_EXA', ($listings[rand(0, count($listings) - 1)]['name'] ?? 'Double Room Deluxe')),
                'role'    => 'get',
            ],
        ],
    ],
    // room booking (and closure)
    [
        'title' => JText::_('VBO_AI_DISC_FN_BOOK_TITLE'),
        'description' => JText::_('VBO_AI_DISC_FN_BOOK_DESCR'),
        'icon' => VikBookingIcons::i('calendar-plus'),
        'options' => [
            // make reservation
            [
                'summary' => JText::_('VBO_AI_DISC_FN_BOOK_MK_SUMM'),
                'example' => JText::sprintf(
                    'VBO_AI_DISC_FN_BOOK_MK_EXA',
                    ($listings[rand(0, count($listings) - 1)]['name'] ?? 'Double Room Deluxe'),
                    $currencysymb
                ),
                'role'    => 'post',
            ],
            // close room
            [
                'summary' => JText::_('VBO_AI_DISC_FN_BOOK_CL_SUMM'),
                'example' => JText::sprintf('VBO_AI_DISC_FN_BOOK_CL_EXA', ($listings[rand(0, count($listings) - 1)]['name'] ?? 'Double Room Deluxe')),
                'role'    => 'post',
            ],
        ],
    ],
    // modify booking
    [
        'title' => JText::_('VBO_AI_DISC_FN_MODBOOK_TITLE'),
        'description' => JText::_('VBO_AI_DISC_FN_MODBOOK_DESCR'),
        'icon' => VikBookingIcons::i('calendar-check'),
        'options' => [
            // change stay dates
            [
                'summary' => JText::_('VBO_AI_DISC_FN_MODBOOK_CS_SUMM'),
                'example' => JText::sprintf('VBO_AI_DISC_FN_MODBOOK_CS_EXA', rand(10, 999), $currencysymb),
                'role'    => 'put',
            ],
            // add extras
            [
                'summary' => JText::_('VBO_AI_DISC_FN_MODBOOK_AE_SUMM'),
                'example' => JText::sprintf('VBO_AI_DISC_FN_MODBOOK_AE_EXA', $currencysymb),
                'role'    => 'put',
            ],
        ],
    ],
    // delete booking
    [
        'title' => JText::_('VBO_AI_DISC_FN_DELBOOK_TITLE'),
        'description' => JText::_('VBO_AI_DISC_FN_DELBOOK_DESCR'),
        'icon' => VikBookingIcons::i('calendar-times'),
        'options' => [
            // search and delete
            [
                'summary' => JText::_('VBO_AI_DISC_FN_DELBOOK_SD_SUMM'),
                'example' => JText::_('VBO_AI_DISC_FN_DELBOOK_SD_EXA'),
                'role'    => 'delete',
            ],
        ],
    ],
    // notify customers
    [
        'title' => JText::_('VBO_AI_DISC_FN_NOTIFCUST_TITLE'),
        'description' => JText::_('VBO_AI_DISC_FN_NOTIFCUST_DESCR'),
        'icon' => VikBookingIcons::i('envelope'),
        'options' => [
            // notify guest from website reservation
            [
                'summary' => JText::_('VBO_AI_DISC_FN_NOTIFCUST_WB_SUMM'),
                'example' => JText::_('VBO_AI_DISC_FN_NOTIFCUST_WB_EXA'),
                'role'    => 'put',
            ],
            // notify guest from OTA reservation
            [
                'summary' => JText::_('VBO_AI_DISC_FN_NOTIFCUST_OT_SUMM'),
                'example' => JText::_('VBO_AI_DISC_FN_NOTIFCUST_OT_EXA'),
                'role'    => 'put',
            ],
        ],
    ],
];
