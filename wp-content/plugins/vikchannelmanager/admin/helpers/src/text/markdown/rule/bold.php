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
 * Converts **BOLD** Markdown syntax into <strong>BOLD</strong> HTML syntax.
 * The rule supports also __BOLD__ syntax.
 * 
 * @since 1.9
 */
class VCMTextMarkdownRuleBold extends VCMTextMarkdownRuleaware
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
        // convert **BOLD** syntax
        $markdown = preg_replace_callback("/(?<=^|[\s>_~\"\(])\*\*(.+?)\*\*(?=[^a-z0-9]|$)/i", function($match) {
            if ($this->plain) {
                return $match[1];
            }

            return sprintf('<strong>%s</strong>', $match[1]);
        }, $markdown);

        // convert __BOLD__ syntax
        $markdown = preg_replace_callback("/(?<=^|[\s>\*~\"\(])__(.+?)__(?=[^a-z0-9]|$)/i", function($match) {
            if ($this->plain) {
                return $match[1];
            }

            return sprintf('<strong>%s</strong>', $match[1]);
        }, $markdown);

        return $markdown;
    }
}
