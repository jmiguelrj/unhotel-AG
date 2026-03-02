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
 * AI assistant message reminder add-on.
 * 
 * @since 1.9
 */
class VCMAiAssistantAddonReminder extends VCMAiAssistantAddonwidget
{
    /** @var object */
    protected $reminder;

    /** @var JDate */
    protected $datetime;

    /**
     * Class constructor.
     * 
     * @param  object  $reminder  The reminder record.
     * @param  mixed   $datetime  The reminder due date.
     */
    public function __construct(object $reminder, $datetime)
    {
        if (!is_object($datetime)) {
            // create a date object if a string/timestamp was provided
            $datetime = JFactory::getDate($datetime);
        }

        $this->reminder = $reminder;
        $this->datetime = $datetime;
    }

    /**
     * @inheritDoc 
     */
    public function getTitle()
    {
        return JText::_('VBO_W_REMINDERS_TITLE');
    }

    /**
     * @inheritDoc 
     */
    public function getIcon()
    {
        return VikBookingIcons::i('list-alt');
    }

    /**
     * @inheritDoc 
     */
    public function getSummary()
    {
        $dueDateTime = JFactory::getDate($this->reminder->duedate);

        // properly format the due date according to the current time
        if ($dueDateTime->format('Y-m-d') === JFactory::getDate('today', date_default_timezone_get())->format('Y-m-d', true)) {
            $dueDateFormatted = JText::_('VBTODAY');
        } else if ($dueDateTime->format('Y-m-d') === JFactory::getDate('+1 day', date_default_timezone_get())->format('Y-m-d', true)) {
            $dueDateFormatted = JText::_('VBOTOMORROW');
        } else {
            $dueDateFormatted = $dueDateTime->format('l, d F Y', true);
        }

        if ($this->reminder->usetime) {
            // append time to formatted date
            $dueDateFormatted .= ' ' . $dueDateTime->format('H:i');
        }

        // generate summary
        return '<div style="font-weight: 500;">' . $this->reminder->title . '</div><div><small>' . $dueDateFormatted . '</small></div>';
    }

    /**
     * @inheritDoc
     */
    public function getFooter()
    {
        // set up VBO widget options
        $options = [
            'reminder_id' => $this->reminder->id,
            'bid' => $this->reminder->idorder,
            'modal_options' => [
                'suffix' => 'widget_modal_inner_reminders',
            ],
        ];

        // add button to quickly access the created reminder
        $onClick = 'VBOCore.handleDisplayWidgetNotification({widget_id: "reminders"}, ' . json_encode($options) . ');';
        return '<button type="button" class="btn vbo-ai-assistant-addon-btn" onclick=\'' . $onClick . '\'>' . JText::_('JTOOLBAR_EDIT') . '</button>';
    }
}
