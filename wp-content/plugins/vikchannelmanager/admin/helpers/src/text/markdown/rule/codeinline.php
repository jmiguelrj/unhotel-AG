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
 * Converts `CODE` Markdown syntax into <code></code> HTML syntax.
 * 
 * @since 1.9.16
 */
class VCMTextMarkdownRuleCodeinline extends VCMTextMarkdownRuleaware
{
    /** @var bool */
    protected $plain;

    /**
     * A list of placeholders.
     *
     * @var string[]
     */
    private $placeholders;

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
        // reset placeholders
        $this->placeholders = [];

        if ($this->plain) {
            // ignore parser
            return $markdown;
        }

        // backticks code block
        return preg_replace_callback("/(?<=^|[^`])`([^\n`]+)`/", function($match) {
            // Push original text within the list.
            // Get HTML entities in order to avoid displaying plain HTML.
            $this->placeholders[] = sprintf('<code>%s</code>', htmlentities($match[1]));

            // use a placeholder
            return sprintf('<code>%s</code>', 'INLINE_CODE_PLACEHOLDER_' . count($this->placeholders));
        }, $markdown);
    }

    /** 
     * @inheritDoc
     */
    public function postflight(string $markdown)
    {
        return preg_replace_callback("/<code>INLINE_CODE_PLACEHOLDER_(\d+)<\/code>/", function($match) {
            // replace the placeholders with the original code
            return $this->placeholders[(int) $match[1] - 1] ?? '';
        }, $markdown);
    }
}
