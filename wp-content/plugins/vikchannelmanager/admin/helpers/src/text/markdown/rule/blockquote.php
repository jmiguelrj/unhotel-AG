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
 * Converts "> Text" Markdown syntax into <blockquote>Text</blockquote> HTML syntax.
 * 
 * @since 1.9.16
 */
class VCMTextMarkdownRuleBlockquote extends VCMTextMarkdownRuleaware
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
        if ($this->plain) {
            // ignore parser
            return $markdown;
        }

        return preg_replace_callback("/(^|\R)> (.+?)(?=\R\R|$)/s", function($match) {
            // strip all > chars placed at the beginning of a line
            $match[2] = preg_replace("/^> /m", '', $match[2]);

            if ($this->plain) {
                return $match[1] . $match[2];
            }

            // keep initial new line, if any
            return sprintf("<blockquote>%s</blockquote>", trim($match[2]));
        }, $markdown);
    }
}
