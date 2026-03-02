<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J srl
 * @copyright   Copyright (C) 2025 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// Restricted access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * AI assistant message orphan dates add-on.
 * 
 * @since 1.9.9
 */
class VCMAiAssistantAddonOrphandates implements VCMAiAssistantAddon
{
    /** @var JDate */
    protected $checkin;

    /** @var JDate */
    protected $checkout;

    /** @var array */
    protected $roomNames;

    /**
     * Class constructor.
     * 
     * @param  mixed  $checkin    The check-in date.
     * @param  mixed  $checkout   The check-out date.
     * @param  array  $roomNames  List of room names.
     */
    public function __construct($checkin, $checkout, array $roomNames)
    {
        if (!is_object($checkin)) {
            // create a date object if a string/timestamp was provided
            $checkin = JFactory::getDate($checkin);
        }

        if (!is_object($checkout)) {
            // create a date object if a string/timestamp was provided
            $checkout = JFactory::getDate($checkout);
        }

        $this->checkin   = $checkin;
        $this->checkout  = $checkout;
        $this->roomNames = $roomNames;
    }

    /**
     * @inheritDoc
     */
    public function render(VCMAiAssistantRenderer $renderer)
    {
        foreach ($this->roomNames as $roomName) {
            $html = '<span class="label label-info">' . JText::_('VBO_AITOOL_ORPHAN_DATES') . '</span>' . "\n"
                . '<span class="label">' . $roomName . '</span>' . "\n"
                . '<em class="badge badge-info vbo-ai-source-badge-details">' . $this->checkin->format('Y-m-d', true) . '</em>'
                . '<span>&nbsp;→&nbsp;</span>'
                . '<em class="badge badge-info vbo-ai-source-badge-details">' . $this->checkout->format('Y-m-d', true) . '</em>';

            $renderer->addSource($html);
        }
    }
}
