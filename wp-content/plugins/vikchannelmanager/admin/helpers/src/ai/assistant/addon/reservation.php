<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2024 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// Restricted access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * AI assistant message reservation add-on.
 * 
 * @since 1.9
 */
class VCMAiAssistantAddonReservation extends VCMAiAssistantAddonwidget
{
    /** @var array */
    protected static $cache = [];

    /** @var int */
    protected $reservationId;

    /** @var string */
    protected $type;

    /** @var array */
    protected $booking = null;

    /**
     * Class constructor.
     * 
     * @param  int     $reservationId  The reservation ID.
     * @param  string  $type           The reservation type.
     */
    public function __construct(int $reservationId, string $type = '')
    {
        $this->reservationId = $reservationId;
        $this->type = (string) $type;
    }

    /**
     * Returns the details of the registered booking.
     * 
     * @return  array
     */
    public function getBooking()
    {
        if (!$this->booking) {
            // fetch booking details
            $this->booking = VikBooking::getBookingInfoFromID($this->reservationId);

            if (!$this->booking) {
                // abort in case the booking does not exist
                throw new Exception('Booking ' . $this->reservationId . ' does not exist.', 404);
            }

            // fetch booking rooms
            $this->booking['rooms'] = VikBooking::loadOrdersRoomsData($this->reservationId);

            // fetch customer details
            $this->booking['customer'] = VikBooking::getCPinInstance()->getCustomerFromBooking($this->reservationId);
        }

        return $this->booking;
    }

    /**
     * @inheritDoc
     */
    public function getClass()
    {
        $class = parent::getClass();

        // append channel to the extra classes, if any
        if ($channel = $this->getBooking()['channel']) {
            $class .= ' ' . preg_replace("/[^a-zA-Z0-9_\-]+/", '', explode('_', $channel)[0]);
        }

        return $class;
    }

    /**
     * @inheritDoc 
     */
    public function getTitle()
    {
        // change title depending on the event type
        if (!strcasecmp($this->type, 'closure')) {
            $title = 'VBO_AITOOL_RESERVATION_CLOSURE';
        } elseif (!strcasecmp($this->type, 'new')) {
            $title = 'VBO_AITOOL_RESERVATION_NEW';
        } elseif (!strcasecmp($this->type, 'modified')) {
            $title = 'VBO_AITOOL_RESERVATION_MODIFY';
        } elseif (!strcasecmp($this->type, 'cancelled')) {
            $title = 'VBO_AITOOL_RESERVATION_CANCEL';
        } else {
            $title = 'VBO_AITOOL_RESERVATION';
        }

        return JText::_($title);
    }

    /**
     * @inheritDoc 
     */
    public function getIcon()
    {
        // fetch logo instance
        $logo = VikBooking::getVcmChannelsLogo($this->getBooking()['channel'], true);

        // use the channel logo, if any
        if ($logo && ($url = $logo->getTinyLogoURL())) {
            return '<img src="' . $url . '" />';
        }

        // fallback to a FA icon, depending on the event type
        if (!strcasecmp($this->type, 'closure')) {
            $icon = 'calendar-plus';
        } elseif (!strcasecmp($this->type, 'new')) {
            $icon = 'calendar-plus';
        } elseif (!strcasecmp($this->type, 'modified')) {
            $icon = 'calendar-check';
        } elseif (!strcasecmp($this->type, 'cancelled')) {
            $icon = 'calendar-times';
        } else {
            $icon = 'calendar-day';
        }

        return VikBookingIcons::i($icon);
    }

    /**
     * @inheritDoc 
     */
    public function getSummary()
    {
        return JLayoutHelper::render(
            'ai.assistant.addons.reservation',
            [
                'booking' => $this->getBooking(),
            ],
            null,
            [
                'component' => 'com_vikchannelmanager',
                'client' => 'admin',
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getFooter()
    {
        // set up VBO widget options
        $options = [
            'bid' => $this->reservationId,
            'modal_options' => [
                'suffix' => 'widget_modal_inner_booking_details',
            ],
        ];

        // dispatch JS event for the involved reservation
        $js_event = '';
        if ($this->type) {
            $js_event = 
<<<HTML
<script>
    setTimeout(() => {
        VBOCore.emitEvent('vbo_new_booking_created', {
            bid: $this->reservationId,
        });
    }, 200);
</script>
HTML
            ;
        }

        // add button to quickly access the details of the reservation
        $onClick = 'VBOCore.handleDisplayWidgetNotification({widget_id: "booking_details"}, ' . json_encode($options) . ');';
        return '<button type="button" class="btn vbo-ai-assistant-addon-btn" onclick=\'' . $onClick . '\'>' . JText::_('JTOOLBAR_EDIT') . '</button>' . $js_event;
    }

    /**
     * @inheritDoc
     */
    public function render(VCMAiAssistantRenderer $renderer)
    {
        if (isset(static::$cache[$this->reservationId])) {
            return '';
        }

        static::$cache[$this->reservationId] = 1;

        return parent::render($renderer);
    }
}
