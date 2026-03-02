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
 * AI assistant aware used to render the addons with a sort of "widget" style.
 * 
 * @since 1.9
 */
abstract class VCMAiAssistantAddonwidget implements VCMAiAssistantAddon
{
    /**
     * Returns the extra classes to be used within the parent widget node.
     * By default the suffix class name of the addon inheriting this class.
     * 
     * @return  string
     */
    public function getClass()
    {
        return strtolower(str_replace('VCMAiAssistantAddon', '', get_class($this)));
    }

    /**
     * Returns the widget main title.
     * 
     * @return  string
     */
    public function getTitle()
    {
        return '';
    }

    /**
     * Returns the widget heading icon. It is possible to use either a FontAwesome icon
     * or any HTML element, such as an image.
     * 
     * Displayed only in presence of a title.
     * 
     * @return  string
     */
    public function getIcon()
    {
        return '';
    }

    /**
     * Returns the widget summary, displayed below the heading section.
     * 
     * @return  string
     */
    public function getSummary()
    {
        return '';
    }

    /**
     * Returns the widget body, displayed below the summary.
     * 
     * @return  string
     */
    public function getBody()
    {
        return '';
    }

    /**
     * Returns the widget footer, displayed below the body.
     * 
     * @return  string
     */
    public function getFooter()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function render(VCMAiAssistantRenderer $renderer)
    {
        $widget = JLayoutHelper::render(
            'ai.assistant.widget',
            [
                'widget' => $this,
            ],
            null,
            [
                'component' => 'com_vikchannelmanager',
                'client' => 'admin',
            ]
        );

        // append widget to the renderer body
        $renderer->appendBody($widget);
    }
}
