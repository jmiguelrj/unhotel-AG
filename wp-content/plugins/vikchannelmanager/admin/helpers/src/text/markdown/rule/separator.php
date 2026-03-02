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
 * Converts --- Markdown syntax into <hr> HTML syntax.
 * 
 * @since 1.9
 */
class VCMTextMarkdownRuleSeparator extends VCMTextMarkdownRuleaware
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
        // convert --- syntax
        $markdown = preg_replace_callback("/\R{1,}-{3,3}\R{1,}/", function($match) {
            if ($this->plain) {
                return $match[0];
            }

            return "<hr />";
        }, $markdown);

        return $markdown;
    }
}
