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
 * AI markdown helper.
 * 
 * @since 1.9
 */
class VCMAiHelperMarkdown
{
    /** @var string */
    protected $markdown;

    /**
     * Class constructor.
     * 
     * @param  string  $markdown  The MD string to work with.
     */
    public function __construct(string $markdown)
    {
        $this->markdown = $markdown;
    }

    /**
     * Converts the markdown string into a plain text.
     * 
     * @return  string
     */
    public function toText()
    {
        return $this->createParser($plain = true)->parse($this->markdown);
    }

    /**
     * Converts the markdown string into a HTML document.
     * 
     * @return  string
     */
    public function toHtml()
    {
        return $this->createParser($plain = false)->parse($this->markdown);
    }

    /**
     * Creates the parser instance that will be used to deal with markdown conversion.
     * 
     * @param   bool  $plain  Whether the rules should convert markdown into plain text.
     * 
     * @return  VCMTextMarkdownParser
     */
    protected function createParser(bool $plain)
    {
        return new VCMTextMarkdownParser([
            new VCMTextMarkdownRuleCodeblock($plain),
            new VCMTextMarkdownRuleCodeinline($plain),
            new VCMTextMarkdownRuleBlockquote($plain),
            new VCMTextMarkdownRuleTable($plain),
            new VCMTextMarkdownRuleBold($plain),
            new VCMTextMarkdownRuleHeading($plain),
            new VCMTextMarkdownRuleItalic($plain),
            new VCMTextMarkdownRuleLink($plain),
            new VCMTextMarkdownRuleSeparator($plain),
            new VCMTextMarkdownRuleNewline($plain),
        ]);
    }
}
