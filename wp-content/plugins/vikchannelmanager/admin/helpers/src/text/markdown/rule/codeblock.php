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
 * Converts ```\nCODE\n``` Markdown syntax into <pre><code></code></pre> HTML syntax.
 * Next to the first ``` it is also possible to specify the language of the code.
 * 
 * @since 1.9.16
 */
class VCMTextMarkdownRuleCodeblock extends VCMTextMarkdownRuleaware
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
        return preg_replace_callback("/```([\S]+\s*\R)?\R?(.+?)(\s?```|$)/s", function($match) {
            $class = $match[1] ? ' class="' . trim($match[1]) . '"' : '';

            // Push original text within the list.
            // Get HTML entities in order to avoid displaying plain HTML.
            $this->placeholders[] = sprintf('<code%s>%s</code>', $class, htmlentities($match[2]));

            // use a placeholder
            return sprintf('<pre><code>%s</code></pre>', 'BLOCK_CODE_PLACEHOLDER_' . count($this->placeholders));
        }, $markdown);
    }

    /** 
     * @inheritDoc
     */
    public function postflight(string $markdown)
    {
        return preg_replace_callback("/<code>BLOCK_CODE_PLACEHOLDER_(\d+)<\/code>/", function($match) {
            // replace the placeholders with the original code
            return $this->placeholders[(int) $match[1] - 1] ?? '';
        }, $markdown);
    }
}
