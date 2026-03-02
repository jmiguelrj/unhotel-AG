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
 * MD-HTML conversion rule aware.
 * 
 * @since 1.9.16
 */
abstract class VCMTextMarkdownRuleaware implements VCMTextMarkdownRule
{
    /** 
     * @inheritDoc
     */
    public function postflight(string $markdown)
    {
        return $markdown;
    }
}
