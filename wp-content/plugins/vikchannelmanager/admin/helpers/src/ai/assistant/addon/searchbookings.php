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
class VCMAiAssistantAddonSearchbookings implements VCMAiAssistantAddon
{
    /** @var array */
    protected $criteria;

    /** @var array */
    protected $reservations;

    /**
     * The total number of matching rows, since the reservations array might be
     * subject to pagination limits.
     * 
     * @var int
     * @since 1.9.1
     */
    protected $totFound;

    /**
     * Class constructor.
     * 
     * @param  array  $criteria      The filters used to make the search query.
     * @param  array  $reservations  The reservations found (first page only).
     * @param  int    $totFound      The total number of matching rows (@since 1.9.1).
     */
    public function __construct(array $criteria, array $reservations, int $totFound)
    {
        $this->criteria = $criteria;
        $this->reservations = $reservations;
        $this->totFound = $totFound;
    }

    /**
     * @inheritDoc
     */
    public function render(VCMAiAssistantRenderer $renderer)
    {
        // check for date_range object filter
        if ($this->criteria['date_range'] ?? null) {
            // set scalar values
            if ($this->criteria['date_range']['type'] ?? null) {
                $this->criteria['type'] = $this->criteria['date_range']['type'];
            }
            if ($this->criteria['date_range']['start'] ?? null) {
                $this->criteria['start'] = $this->criteria['date_range']['start'];
            }
            if ($this->criteria['date_range']['end'] ?? null) {
                $this->criteria['end'] = $this->criteria['date_range']['end'];
            }
            // unset original non-scalar value
            unset($this->criteria['date_range']);
        }

        $terms = '<span class="label label-info">' . JText::_('VBO_AITOOL_SEARCH_BOOKINGS') . '</span>' . "\n";

        foreach (array_filter($this->criteria) as $arg_name => $arg_val) {
            $arg_name = ucwords(str_replace('_', ' ', $arg_name));
            $terms .= '<span class="label">' . $arg_name . ' = ' . (is_scalar($arg_val) ? (string) $arg_val : gettype($arg_val)) . '</span>' . "\n";
        }

        // create wrapper
        $html = '<div class="search-addon-wrapper">'
                . '<div class="search-terms">' . $terms . '</div>'
                . '<div class="label label-' . ($this->totFound ? 'success' : 'danger') . '">' . JText::sprintf('VBO_TOTAL_RESULTS', $this->totFound) . '</div>'
            .'</div>';

        $renderer->addSource($html);

        if ($this->totFound == 1) {
            // describe the reservation found with an apposite widget
            (new VCMAiAssistantAddonReservation($this->reservations[0]['id']))->render($renderer);
        }
    }
}
