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
 * Interface used to handle the MD-HTML conversion.
 * 
 * @since 1.9
 */
interface VCMTextMarkdownRule
{
    /** 
     * Reads the given MARKDOWN string to build a HTML text.
     *
     * @param   string  $markdown  The markdown text to parse.
     *
     * @return  string  The resulting string.
     */
    public function parse(string $markdown);

    /** 
     * Runs once all the rules have been dispatched.
     * This method will be executed in reverse ordering.
     * So, if we have the highest priority, the rule will be executed as last.
     * 
     * @param   string  $markdown  The markdown text to parse.
     *
     * @return  string  The resulting string.
     */
    public function postflight(string $markdown);
}
