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
 * AI assistant message statistics add-on.
 * 
 * @since 1.9
 */
class VCMAiAssistantAddonStatistics implements VCMAiAssistantAddon
{
    /** @var JDate */
    protected $start;

    /** @var JDate */
    protected $end;

    /** @var string */
    protected $metrics;

    /** @var string */
    protected $roomName;

    /**
     * Class constructor.
     * 
     * @param  mixed   $start     The start date.
     * @param  mixed   $end       The end date.
     * @param  string  $metrics   The elaborated metrics enum.
     * @param  string  $roomName  The filtered room name.
     */
    public function __construct($start, $end, string $metrics, string $roomName = '')
    {
        if (!is_object($start)) {
            // create a date object if a string/timestamp was provided
            $start = JFactory::getDate($start);
        }

        if (!is_object($end)) {
            // create a date object if a string/timestamp was provided
            $end = JFactory::getDate($end);
        }

        $this->start    = $start;
        $this->end      = $end;
        $this->metrics  = $metrics;
        $this->roomName = $roomName;
    }

    /**
     * @inheritDoc
     */
    public function render(VCMAiAssistantRenderer $renderer)
    {
        $html = '<span class="label label-info">' . JText::_('VBO_AITOOL_STATS') . '</span>';

        if ($this->roomName) {
            $html .= '<span class="badge badge-primary">' . $this->roomName . '</span>';
        }

        $html .= '<span class="label">' . ucfirst(str_replace('_', ' ', $this->metrics)) . '</span>'
            . '<em class="badge badge-info vbo-ai-source-badge-details">' . $this->start->format('Y-m-d', true) . '</em>'
            . '<span>→</span>'
            . '<em class="badge badge-info vbo-ai-source-badge-details">' . $this->end->format('Y-m-d', true) . '</em>';

        $renderer->addSource('<div class="addon-statistics">' . $html . '</div>');
    }
}
