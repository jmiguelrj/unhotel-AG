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
 * Converts **ITALIC** Markdown syntax into <em>ITALIC</em> HTML syntax.
 * The rule supports also _ITALIC_ syntax.
 *
 * This function MUST be called after invoking the BOLD parser
 * as the MD syntax is very close. Otherwise we would mark the
 * same content as italic twice.
 * 
 * @since 1.9
 */
class VCMTextMarkdownRuleItalic extends VCMTextMarkdownRuleaware
{
    /** @var bool */
    protected $plain;

    /**
     * Class constructor.
     * 
     * @param  bool  $plain  Whether the resulting text should be plain rather than HTML.
     */
    public function __construct(bool $plain = false)
    {
        $this->plain = $plain;
    }

    /**
     * @inheritDoc
     */
    public function parse(string $markdown)
    {
        // An italic string can be preceeded by nothing, by a space
        // or by a closing HTML tag.

        // convert *ITALIC* syntax
        $markdown = preg_replace_callback("/(?<=^|[\s>_~\"\(])\*(.+?[^\\\\])\*(?=[^a-z0-9]|$)/i", function($match) {
            if ($this->plain) {
                return $match[1];
            }
            
            return sprintf('<em>%s</em>', $match[1]);
        }, $markdown);

        // convert _ITALIC_ syntax
        $markdown = preg_replace_callback("/(?<=^|[\s>*~\"\(])_(.+?[^\\\\])_(?=[^a-z0-9]|$)/i", function($match) {
            if ($this->plain) {
                return $match[1];
            }
            
            return sprintf('<em>%s</em>', $match[1]);
        }, $markdown);

        return $markdown;
    }
}
