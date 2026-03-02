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
 * Markdown text parser class.
 * 
 * @since 1.9
 */
class VCMTextMarkdownParser
{
    /** @var VCMTextMarkdownRule[] */
    protected $rules = [];

    /**
     * Class constructor.
     * 
     * @param  VCMTextMarkdownRule[]  $rules  The rules used to parse a markdown text.
     */
    public function __construct(array $rules)
    {
        $this->rules = array_filter($rules, function($rule) {
            return $rule instanceof VCMTextMarkdownRule;
        });
    }

    /**
     * @see VCMTextMarkdownRule::parse()
     */
    public function parse(string $markdown)
    {
        foreach ($this->rules as $rule) {
            $markdown = $rule->parse($markdown);
        }

        /**
         * Perform postflight actions in reverse ordering.
         * 
         * @since 1.9.16
         */
        foreach (array_reverse($this->rules) as $rule) {
            $markdown = $rule->postflight($markdown);
        }

        return $markdown;
    }
}
