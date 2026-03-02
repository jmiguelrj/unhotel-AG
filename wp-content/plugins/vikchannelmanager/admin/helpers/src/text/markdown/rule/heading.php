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
 * Converts # Markdown syntax into <h1> HTML syntax.
 * Multiple ## in a row denote smaller heading sizes.
 * 
 * @since 1.9
 */
class VCMTextMarkdownRuleHeading extends VCMTextMarkdownRuleaware
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
        // convert #{1,6} syntax
        $markdown = preg_replace_callback("/(\R)?^(?:(\#{1,6}) (.*?))$(\R)?/m", function($match) {
            if ($this->plain) {
                // include initial and training new lines, if any
                return $match[1] . $match[3] . $match[4];
            }

            // find heading size
            $h = strlen($match[2]);

            // build heading
            return sprintf('<h%1$d>%2$s</h%1$d>', $h, $match[3]);
        }, $markdown);

        return $markdown;
    }
}
