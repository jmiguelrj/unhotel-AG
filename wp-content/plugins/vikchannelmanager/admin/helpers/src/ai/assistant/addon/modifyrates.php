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
 * AI assistant message modify rates add-on.
 * 
 * @since 1.9
 */
class VCMAiAssistantAddonModifyrates implements VCMAiAssistantAddon
{
    /** @var JDate */
    protected $start;

    /** @var JDate */
    protected $end;

    /** @var string */
    protected $ratePlanName;

    /** @var float */
    protected $rate;

    /** @var int */
    protected $minLos;

    /** @var array */
    protected $room;

    /** @var array */
    protected $modelResults;

    /**
     * Class constructor.
     * 
     * @param  mixed   $start         The start date.
     * @param  mixed   $end           The end date.
     * @param  string  $ratePlanName  The rate plan name.
     * @param  float   $rate          The rate applied.
     * @param  int     $minLos        The minimum stay applied.
     * @param  array   $room          The room record involved.
     * @param  mixed   $model         The VBOModelPricing object.
     */
    public function __construct($start, $end, string $ratePlanName, float $rate, int $minLos, array $room, $model)
    {
        if (!is_object($start)) {
            // create a date object if a string/timestamp was provided
            $start = JFactory::getDate($start);
        }

        if (!is_object($end)) {
            // create a date object if a string/timestamp was provided
            $end = JFactory::getDate($end);
        }

        // preserve only the attributes actually needed
        if ($room) {
            $room = [
                'id' => $room['id'],
                'name' => $room['name'],
            ];
        }

        // obtain the results computed by the pricing model
        $model = [
            'channels' => $model->getChannelsUpdated(),
            'errors' => $model->getChannelErrors(),
            'warnings' => $model->getChannelWarnings(),
        ];

        $this->start        = $start;
        $this->end          = $end;
        $this->ratePlanName = $ratePlanName;
        $this->rate         = $rate;
        $this->minLos       = $minLos;
        $this->room         = $room;
        $this->modelResults = $model;
    }

    /**
     * @inheritDoc
     */
    public function render(VCMAiAssistantRenderer $renderer)
    {
        // the currency ISO code
        $currency = VikBooking::getCurrencyName();

        $html = '';

        if ($this->room) {
            $html .= '<span class="label label-info">' . $this->room['name'] . '</span>';
        }

        if ($this->ratePlanName) {
            $html .= '<span class="label label-info">' . $this->ratePlanName . '</span>';
        }

        $html .= '<em class="badge badge-info">' . $this->start->format('Y-m-d', true) . '</em>'
            . '<span>→</span>'
            . '<em class="badge badge-info">' . $this->end->format('Y-m-d', true) . '</em>';

        if ($this->rate) {
            $html .= '<span class="label">' . $currency . ' ' . VikBooking::numberFormat($this->rate) . '</span>';
        }

        if ($this->minLos) {
            $html .= '<span class="label">' . sprintf('Min LOS %d', $this->minLos) . '</span>';
        }

        foreach ($this->modelResults['channels'] as $channel) {
            if ($channel['tiny_logo']) {
                $html .= '<img src="' . $channel['tiny_logo'] . '" />';
            } elseif ($channel['small_logo']) {
                $html .= '<img src="' . $channel['small_logo'] . '" />';
            } else {
                $html .= '<span>' . $channel['name'] . '</span>';
            }
        }

        $html = '<div class="addon-modifyrate-info">' . $html . '</div>';

        if ($this->room) {
            $onClick = 'VBOCore.handleDisplayWidgetNotification({widget_id: \'bookings_calendar\'}, {id_room: ' . $this->room['id'] . ', offset: \'' . $this->start->format('Y-m-01', true) . '\', roomrates: 1, modal_options: {suffix: \'widget_modal_inner_bookings_calendar\'}});';
            $seeRates = '<button type="button" class="btn vbo-ai-assistant-addon-btn" onclick="' . $onClick . '">' . JText::_('VBO_AITOOL_MODIFY_RATES_SEE') . '</button>';

            $html = '<div class="modifyrates-addon-inner">' . $html . $seeRates . '</div>';
        }

        if ($this->modelResults['errors']) {
            $html .= '<div class="vcm-addon-modifyrates-errors">';
            $html .= implode("\n", array_map(function($err) {
                return '<p class="err">' . $err . '</p>';
            }, $this->modelResults['errors']));
            $html .= '</div>';
        }

        if ($this->modelResults['warnings']) {
            $html .= '<div class="vcm-addon-modifyrates-warnings">';
            $html .= implode("\n", array_map(function($warn) {
                return '<p class="warn">' . $warn . '</p>';
            }, $this->modelResults['warnings']));
            $html .= '</div>';
        }

        $renderer->appendBody('<div class="modifyrates-addon-wrapper">' . $html . '</div>', '<br>');
    }
}
