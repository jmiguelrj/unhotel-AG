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
 * AI assistant message add-on.
 * 
 * @since 1.9
 */
interface VCMAiAssistantAddon
{
    /**
     * Renders the HTML of the add-on.
     * 
     * @param   VCMAiAssistantRenderer  $renderer  The HTML renderer instance. It should be responsibility of
     *                                             the add-on to include the existing HTML within the response.
     * 
     * @return  void
     */
    public function render(VCMAiAssistantRenderer $renderer);
}
