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
 * Converts [TITLE](URI) Markdown syntax into <a href="URI">TITLE</a> HTML syntax.
 * 
 * @since 1.9
 */
class VCMTextMarkdownRuleLink extends VCMTextMarkdownRuleaware
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
            // fetch plain links
            $markdown = preg_replace_callback("/(?<=^|[^\[\(\">])(?:(?:https?|ftp):\/\/|www\.)[\x{0080}-\x{FFFF}a-z0-9\-+&@#\/%?=~_|!:,.;\[\]]*[\-a-z0-9+&@#\/%=~_|]/ui", function($match) {
                return sprintf(
                    '<a href="%s">%s</a>',
                    htmlspecialchars($match[0], ENT_COMPAT, 'UTF-8'),
                    $match[0]
                );
            }, $markdown);
        }

        // fetch MD link syntax
        $markdown = preg_replace_callback("/([^\!]|^)\[([^\[]+)\]\((.*?)\)/", function($match) {
            if ($this->plain) {
                // in case the text of the link is not a URL, include it before the URL
                if (!preg_match("/^https?:\/\//", $match[2])) {
                    // Link text (https://domain.com...)
                    return $match[1] . $match[2] . ' (' . $match[3] . ')';
                }

                // https://domain.com...
                return $match[1] . $match[3];
            }

            return sprintf(
                '%s<a href="%s">%s</a>',
                $match[1],
                htmlspecialchars($match[3], ENT_COMPAT, 'UTF-8'),
                $match[2]
            );
        }, $markdown);

        return $markdown;
    }
}
