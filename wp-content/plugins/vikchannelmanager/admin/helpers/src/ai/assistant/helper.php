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
 * AI chat assistant helper.
 * 
 * @since 1.9
 */
class VCMAiAssistantHelper
{
    /** @var string */
    protected $uuid;

    /**
     * Class constructor.
     * 
     * @param  string|null  $uuid  The assistant request identifier
     */
    public function __construct($uuid = null)
    {
        $this->uuid = $uuid;
    }

    /**
     * Returns the unique identifier.
     * 
     * @return  string
     */
    public function getID()
    {
        return $this->uuid;
    }

    /**
     * Returns the add-on assigned to the current request, if any.
     * 
     * @param   bool    $flush  Whether the add-on should be removed from the database too.
     * 
     * @return  string  The resulting HTML.
     */
    public function getAddon(bool $flush = true)
    {
        if (!$this->uuid) {
            return '';
        }

        $config = VCMFactory::getConfig();

        // fetch the HTML of the existing add-on
        $addons = $config->getString('ai_assistant_addon_' . $this->uuid, '');

        if ($flush) {
            // permanently remove add-on from the database
            $config->remove('ai_assistant_addon_' . $this->uuid);
        }

        if ($addons) {
            $addons = unserialize($addons);
        }

        if (!is_array($addons)) {
            // unable to process the add-ons
            return '';
        }

        $renderer = new VCMAiAssistantRenderer;

        // render the add-ons one by one
        foreach ($addons as $addon) {
            try {
                $addon->render($renderer);
            } catch (Throwable $error) {
                // an error has occurred, display an error widget instead
                (new VCMAiAssistantAddonError($error))->render($renderer);
            }
        }

        return (string) $renderer;
    }

    /**
     * Registers a new add-on for the current request.
     * 
     * @param   VCMAiAssistantAddon  $addon
     * 
     * @return  self
     */
    public function createAddon(VCMAiAssistantAddon $addon)
    {
        if ($this->uuid) {
            $config = VCMFactory::getConfig();

            // recover add-ons from the configuration
            $addons = $config->getString('ai_assistant_addon_' . $this->uuid, '');

            if ($addons) {
                $addons = @unserialize($addons);
            }

            if (!is_array($addons)) {
                // something went wrong while unserializing the array or the configuration was empty
                $addons = [];
            }

            // append add-on to the list
            $addons[] = $addon;

            // temporarily save the add-ons within the configuration
            $config->set('ai_assistant_addon_' . $this->uuid, serialize($addons));
        }

        return $this;
    }
}
