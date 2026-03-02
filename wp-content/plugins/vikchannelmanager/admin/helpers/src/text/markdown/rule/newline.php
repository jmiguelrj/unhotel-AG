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
 * Converts new lines into <br> HTML syntax.
 * 
 * @since 1.9.16
 */
class VCMTextMarkdownRuleNewline extends VCMTextMarkdownRuleaware
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
        if (!$this->plain) {
            $markdown = nl2br($markdown);
        }

        return $markdown;
    }
}
