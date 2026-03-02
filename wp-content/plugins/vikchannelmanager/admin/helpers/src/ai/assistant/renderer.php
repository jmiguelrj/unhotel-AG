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
 * AI assistant message add-on renderer.
 * 
 * @since 1.9
 */
class VCMAiAssistantRenderer
{
    /**
     * The body holding all the add-ons.
     * 
     * @var string 
     */
    protected $body = '';

    /**
     * A list of sources used to process the message.
     * 
     * @var string[]
     */
    protected $sources = [];

    /**
     * Returns the currently set body.
     * 
     * @return  string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Replaces the current body with the provided one.
     * 
     * @param   string  $body
     * 
     * @return  self
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Adds the provided add-on to the end of the body.
     * 
     * @param   string  $addon
     * 
     * @return  self
     */
    public function appendBody($addon, string $separator = "\n")
    {
        if ($this->body) {
            $addon = $separator . $addon;
        }

        $this->body .= $addon;

        return $this;
    }

    /**
     * Adds the provided add-on to the beginning of the body.
     * 
     * @param   string  $addon
     * 
     * @return  self
     */
    public function prependBody($addon, string $separator = "\n")
    {
        if ($this->body) {
            $addon .= $separator;
        }

        $this->body = $addon . $this->body;

        return $this;
    }

    /**
     * Returns all the registered sources.
     * 
     * @return  string[]
     */
    public function getSources()
    {
        return $this->sources;
    }

    /**
     * Replaces all the registered sources with the provided ones.
     * 
     * @param   string[]  $sources
     * 
     * @return  self
     */
    public function setSources(array $sources)
    {
        $this->sources = $sources;

        return $this;
    }

    /**
     * Append a new source to the end of the list.
     * 
     * @param   string  $source   The source HTML to add.
     * @param   bool    $prepend  Whether the source should be added at the beginning.
     * 
     * @return  self
     */
    public function addSource($source, bool $prepend = false)
    {
        if ($prepend) {
            array_unshift($this->sources, $source);
        } else {
            $this->sources[] = $source;
        }

        return $this;
    }

    /**
     * Creates the HTML result.
     * 
     * @return  string
     */
    public function render()
    {
        if (!$this->body && !$this->sources) {
            // nothing to display
            return '';
        }

        // render through an apposite layout
        return JLayoutHelper::render(
            'ai.assistant.addons',
            [
                'body' => $this->getBody(),
                'sources' => $this->getSources(),
            ],
            null,
            [
                'component' => 'com_vikchannelmanager',
                'client'    => 'admin',
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->render();
    }
}
