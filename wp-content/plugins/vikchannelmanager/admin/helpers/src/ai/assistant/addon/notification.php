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
 * AI assistant message notification add-on.
 * 
 * @since 1.9
 */
class VCMAiAssistantAddonNotification extends VCMAiAssistantAddonwidget
{
    /** @var int */
    protected $reservationId;

    /** @var string */
    protected $method;

    /** @var string */
    protected $subject;

    /** @var string */
    protected $recipient;

    /** @var string */
    protected $message;

    /**
     * Class constructor.
     * 
     * @param  int     $reservationId  The reservation ID.
     * @param  string  $type           The reservation type.
     */
    public function __construct(int $reservationId, string $method, string $subject, string $recipient, string $message)
    {
        $this->reservationId = $reservationId;
        $this->method = $method;
        $this->subject = $subject;
        $this->recipient = $recipient;
        $this->message = $message;
    }

    /**
     * @inheritDoc 
     */
    public function getTitle()
    {
        return JText::_('VBO_AITOOL_NOTIFICATION');
    }

    /**
     * @inheritDoc 
     */
    public function getIcon()
    {
        if (!strcasecmp($this->method, 'email')) {
            $icon = 'envelope';
        } else {
            $icon = 'comment';
        }

        return VikBookingIcons::i($icon);
    }

    /**
     * @inheritDoc 
     */
    public function getSummary()
    {
        if (!strcasecmp($this->method, 'email')) {
            $summary = JText::sprintf('VCM_AITOOL_NOTIFICATION_SUMMARY_EMAIL', $this->recipient);
        } else {
            $summary = JText::sprintf('VCM_AITOOL_NOTIFICATION_SUMMARY_MESSAGING', $this->method);
        }

        return $summary;
    }

    /**
     * @inheritDoc 
     */
    public function getBody()
    {
        if (strip_tags($this->message) == $this->message) {
            // plain text, convert new lines in <br> tags
            $message = nl2br($this->message);
        } else {
            // use the provided HTML format
            $message = $this->message;
        }

        // create body with message content
        $body = '<div class="notification-message">' . $message . '</div>';

        if ($this->subject) {
            // prepend subject to message, if any
            $body = '<div class="notification-subject">' . $this->subject . '</div>' . $body;
        }

        return '<div class="notification-wrapper">' . $body . '</div>';
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

        if (!strcasecmp($this->method, 'email')) {
            $widgetId = 'booking_details';
        } else {
            $widgetId = 'guest_messages';
        }

        // add button to quickly access the details of the reservation or its chat interface
        $onClick = 'VBOCore.handleDisplayWidgetNotification({widget_id: "' . $widgetId . '"}, ' . json_encode($options) . ');';
        return '<button type="button" class="btn vbo-ai-assistant-addon-btn" onclick=\'' . $onClick . '\'>' . JText::_('VBOVIEWBOOKINGDET') . '</button>';
    }
}
