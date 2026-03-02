<?php
/** 
 * @package     VikUpdater
 * @subpackage  bc (backward-compatibility)
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

namespace VikWP\VikUpdater\BC;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

use VikWP\VikUpdater\FileSystem\File;
use VikWP\VikUpdater\FileSystem\Folder;

/**
 * Backward compatibilities manager.
 * 
 * @since 2.0
 */
class Manager
{
    /**
     * Auto-redirects the user to the correct URL in case the current
     * page is /options-general.php?page=vikupdater (before 2.0 version).
     * 
     * @return  void
     */
    public static function redirect()
    {
        global $pagenow;
    
        if ($pagenow === 'options-general.php' && ($_GET['page'] ?? '') === 'vikupdater')
        {
            // auto-redirect to the new VikUpdater location
            wp_redirect('tools.php?page=vikupdater');
            exit;
        }
    }

    /**
     * This method helps with the migration of the plugins from the 1.x framework
     * of VikUpdater to the latest one.
     * 
     * @param   array  $subscribers  A list of subscribers.
     * 
     * @return  array  The subscribed plugins.
     */
    public static function autowirePlugins(array $subscribers)
    {
        /**
         * Do not proceed in case the function used to access the plugins list does not exist,
         * as we are probably in the front-end and we don't need to perform this kind of autowiring.
         * 
         * @since 2.0.2
         */
        if (!function_exists('get_plugins'))
        {
            return [];
        }

        // get all installed WordPress plugins
        $plugins = get_plugins();

        // get all the registered products in VikUpdater (deprecated way)
        $products = static::getProducts();

        /** @var VikWP\VikUpdater\Core\Keywallet */
        $keywallet = \VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.keywallet');

        foreach ($products as $hash => $product)
        {
            // extract order details from product hash
            list($ordnum, $ordkey, $sku) = explode('-', $hash);

            // attempt to obtain the latest version of the current product
            $version = apply_filters('vikwp_vikupdater_' . $sku . '_version', null);

            if (!$version)
            {
                // version missing, the item has been probably updated to support
                // the requirments dicted by the latest version of VikUpdater
                continue;
            }

            // recover the item folder
            $folder = rtrim($product->path, '\\/.');

            // make sure we are observing a plugin
            if (!preg_match("/[\/\\\\]wp-content[\/\\\\]plugins[\/\\\\]/", $folder))
            {
                // not a plugin
                continue;
            }

            // make sure the folder exists
            if (!Folder::exists($folder))
            {
                // malformed folder
                continue;
            }

            // search the main file of the plugin and return the text domain
            $textDomain = static::detectPluginMainFile($folder);

            if (!$textDomain)
            {
                // unable to detect the main file or the plugin doesn't use a text domain
                continue;
            }

            // search for a plugin matching the text domain of the registered product
            $search = array_filter($plugins, function($plugin) use ($textDomain) {
                return !strcasecmp($textDomain, $plugin['TextDomain']);
            });

            if (!$search)
            {
                // item not found
                continue;
            }

            $data     = reset($search);
            $pluginId = key($search);

            // extract the slug from the plugin id
            list($slug, $filename) = explode('/', $pluginId);

            // subscribe the current plugin
            $subscribers[] = [
                'name'    => $data['Name'],
                'slug'    => $slug,
                'plugin'  => $pluginId,
                'sku'     => $sku,
                'version' => $version,
            ];

            // check whether the license has been already registered within the keywallet
            if ($keywallet->find($pluginId) === false)
            {
                // register the license within the keywallet
                $keywallet->save($pluginId, $ordnum . '-' . $ordkey);
            }
        }

        return $subscribers;
    }

    /**
     * This method helps with the migration of the themes from the 1.x framework
     * of VikUpdater to the latest one.
     * 
     * @param   array  $subscribers  A list of subscribers.
     * 
     * @return  array  The subscribed themes.
     */
    public static function autowireThemes(array $subscribers)
    {
        /**
         * Do not proceed in case the function used to access the themes list does not exist,
         * as we are probably in the front-end and we don't need to perform this kind of autowiring.
         * 
         * @since 2.0.2
         */
        if (!function_exists('wp_get_themes'))
        {
            return [];
        }

        // get all installed WordPress themes
        $themes = wp_get_themes();

        // get all the registered products in VikUpdater (deprecated way)
        $products = static::getProducts();

        /** @var VikWP\VikUpdater\Core\Keywallet */
        $keywallet = \VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.keywallet');

        foreach ($products as $hash => $product)
        {
            // extract order details from product hash
            list($ordnum, $ordkey, $sku) = explode('-', $hash);

            // attempt to obtain the latest version of the current product
            $version = apply_filters('vikwp_vikupdater_' . $sku . '_version', null);

            if (!$version)
            {
                // version missing, the item has been probably updated to support
                // the requirments dicted by the latest version of VikUpdater
                continue;
            }

            // recover the item folder
            $folder = rtrim($product->path, '\\/.');

            // make sure we are observing a theme
            if (!preg_match("/[\/\\\\]wp-content[\/\\\\]themes[\/\\\\]/", $folder))
            {
                // not a theme
                continue;
            }

            // make sure the folder exists
            if (!Folder::exists($folder))
            {
                // malformed folder
                continue;
            }

            // the theme identifier is always equal to the name of its folder
            $themeId = basename($folder);

            // search for a theme matching the ID of the registered product
            if (!isset($themes[$themeId]))
            {
                // item not found
                continue;
            }

            $data = $themes[$themeId];

            // subscribe the current theme
            $subscribers[] = [
                'name'    => $data['Name'],
                'theme'   => $themeId,
                'sku'     => $sku,
                'version' => $version,
            ];

            // check whether the license has been already registered within the keywallet
            if ($keywallet->find($themeId) === false)
            {
                // register the license within the keywallet
                $keywallet->save($themeId, $ordnum . '-' . $ordkey);
            }
        }

        return $subscribers;
    }

    /**
     * Returns a list with all the products registered in the 1.x version.
     * 
     * @return  array  An associative array where the keys are the hashes (sku-ordnum-ordpass)
     *                 of the products, and the values are objects containing the plugin details.
     */
    public static function getProducts()
    {
        static $products = null;

        if ($products === null)
        {
            // load products registered in the previous versions of VikUpdater
            $products = get_option('vikupdater_products', null);

            if (!$products)
            {
                return [];
            }

            // decode products from JSON
            $products = json_decode($products, true);

            if (!$products)
            {
                return [];
            }

            $normalized = false;

            foreach ($products as $key => $data)
            {
                // check whether we still have a double encoding
                if (is_string($data))
                {
                    // decode from JSON
                    $products[$key] = json_decode($data);

                    $normalized = true;
                }
                else
                {
                    $products[$key] = (object) $data;
                }
            }

            if ($normalized)
            {
                // fix double JSON encoding
                update_option('vikupdater_products', json_encode($products));
            }
        }

        return $products;
    }

    /**
     * Searches the main file of a plugin from the provided folder.
     * 
     * @param   string       $folder  The folder where the file should be searched.
     * 
     * @return  string|null  The text domain of the detected file, null otherwise.
     */
    protected static function detectPluginMainFile(string $folder)
    {
        // iterate all the files nested in the root of the given folder
        foreach (Folder::files($folder, '\.php$', $recursive = false, $full = true) as $file)
        {
            // load code
            $buffer = (string) @file_get_contents($file);

            // check whether the file starts with a WP plugin declaration
            if (!preg_match("/^<\?php\s*\/\*(.*?)Text Domain:\s*([a-zA-z0-9\-_]+)/s", $buffer, $match))
            {
                // nope, go ahead
                continue;
            }

            // return the plugin text domain
            return end($match);
        }

        return null;
    }
}
