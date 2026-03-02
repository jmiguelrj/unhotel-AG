<?php
/** 
 * @package     VikUpdater
 * @subpackage  wordpress
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

namespace VikWP\VikUpdater\WordPress\API;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Helper class needed to collect all the plugins and themes that wishes to take
 * advantage of the updating function provided by this framework.
 * 
 * @since 2.0
 */
class UpdatesObserver
{
    /**
     * A cache of subscribed plugins.
     * 
     * @var array
     */
    protected $plugins = null;

    /**
     * A cache of subscribed themes.
     * 
     * @var array
     */
    protected $themes = null;

    /**
     * Returns all the subscribed plugins.
     * 
     * @return  array
     */
    public function getPlugins()
    {
        if ($this->plugins === null)
        {
            /**
             * Filter used to obtain a list of plugins that wishes to use
             * the features provided by VikUpdater.
             * 
             * @param  array  $plugins  A list of subscribers.
             * 
             * @since  2.0
             * 
             * ######################################################################################################
             * 
             * Plugins interested in subscribing themselves can attach the following
             * information to the return array.
             * 
             * @var  name            string  The name of the plugin, such as "VikUpdater" (mandatory).
             * @var  slug            string  The slug of the plugin, such as "vikupdater" (mandatory).
             * @var  plugin          string  The plugin identifier, such as "vikupdater/vikupdater.php" (mandatory).
             * @var  author          string  The author that developed the plugin (optional). If not provided
             *                               the system will assume that the manufacturer is "E4J srl".
             * @var  author_profile  string  The link of the plugin author (optional). If not provided the
             *                               system will assume that the author URL is "https://vikwp.com".
             * @var  version         string  The current plugin version (mandatory).
             * @var  tested          string  The WordPress tested-up-to version (optional).
             * @var  requires        string  The minimum required version of WordPress (optional).
             * @var  requires_php    string  The minimum required version of PHP (optional).
             * @var  url             string  The URL needed to check the latest version of the plugin (mandatory).
             * @var  download        string  The URL needed to download the latest version (mandatory).
             * @var  last_updated    string  The last updated date in military format (optional).
             * @var  contributors    array   An associative array containing the plugin contributors (optional).
             *                               If provided, the array must contain an associative array containing
             *                               the "avatar", "profile" and "display_name" attributes. If not provided
             *                               the system will take the default "VikWP" contributors.
             * @var  description     string  The main HTML description of the plugin (optional, but recommended).
             * @var  installation    string  The HTML content to be displayed under the "Installation" tab (optional).
             * @var  faq             string  The HTML content to be displayed under the "FAQ" tab (optional).
             * @var  notes           string  The HTML content to be displayed under the "Notes" tab (optional).
             * @var  changelog       mixed   An HTML string containing the changelog (optional).
             * @var  banners         array   The plugin banners array (optional). If provided, the array must
             *                               define both the "low" and "high" (resolution) attributes.
             * @var  screenshots     array   An array of screenshots (optional). If you want to specify a caption, the
             *                               list must contain associative arrays defining `src` and `caption`.
             */
            $this->plugins = (array) apply_filters('vikupdater_subscribe_plugins', []);

            // self-subscribe VikUpdater
            $this->plugins[] = [
                'name'         => 'VikUpdater',
                'slug'         => 'vikupdater',
                'plugin'       => 'vikupdater/vikupdater.php',
                'sku'          => 'vup',
                'version'      => VIKUPDATER_VERSION,
                'requires_php' => '7.0',
                'description'  => __('Plugin used to update commercial plugins that are not part of the official wordpress.org repository.', 'vikupdater'),
                'download'     => 'https://vikwp.com/api/?task=products.freedownload&sku=vup',
            ];
        }

        return $this->plugins;
    }

    /**
     * Returns all the subscribed themes.
     * 
     * @return  array
     */
    public function getThemes()
    {
        if ($this->themes === null)
        {
            /**
             * Filter used to obtain a list of themes that wishes to use
             * the features provided by VikUpdater.
             * 
             * @param  array  $themes  A list of subscribers.
             * 
             * @since  2.0
             * 
             * ######################################################################################################
             * 
             * Themes interested in subscribing themselves can attach the following
             * information to the return array.
             * 
             * @var  name          string  The name of the theme, such as "MyTheme" (mandatory).
             * @var  theme         string  The theme identifier, such as "my-theme" (mandatory).
             * @var  version       string  The current theme version (mandatory).
             * @var  tested        string  The WordPress tested-up-to version (optional).
             * @var  requires      string  The minimum required version of WordPress (optional).
             * @var  requires_php  string  The minimum required version of PHP (optional).
             * @var  url           string  The URL needed to check the latest version of the theme (mandatory).
             * @var  download      string  The URL needed to download the latest version (mandatory).
             * @var  infourl       string  The URL to the changelog of the theme (optional).
             */
            $this->themes = (array) apply_filters('vikupdater_subscribe_themes', []);
        }

        return $this->themes;
    }
}
