<?php
/** 
 * @package   	VikUpdater
 * @subpackage 	mvc (model-view-controller)
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

namespace VikWP\VikUpdater\MVC\Models;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

use VikWP\VikUpdater\Core\Keywallet;
use VikWP\VikUpdater\MVC\Model;

/**
 * Licenses model.
 * 
 * @since 2.0
 */
class LicensesModel implements Model
{
    /** @var Keywallet */
    protected $keywallet;

    /**
     * Class constructor.
     * 
     * @param  Keywallet|null  $keywallet  Where the licenses should be saved. If not provided
     *                                  the default one will be used.
     */
    public function __construct(?Keywallet $keywallet = null)
    {
        if ($keywallet)
        {
            // use provided keywallet
            $this->keywallet = $keywallet;
        }
        else
        {
            // use default keywallet
            $this->keywallet = \VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.keywallet');
        }
    }

    /**
     * @inheritDoc
     */
    public function getItem($pk)
    {
        if (!is_string($pk))
        {
            throw new \InvalidArgumentException(sprintf('Cannot fetch license: string expected, %s given.', gettype($pk)));
        }

        // search license
        $license = $this->keywallet->find($pk);

        if ($license === false)
        {
            throw new \DomainException('License not found for ' . $pk, 404);
        }

        return $license;
    }

    /**
     * @inheritDoc
     */
    public function save($data)
    {
        $data = (array) $data;

        if (empty($data['product']))
        {
            throw new \InvalidArgumentException('Missing product identifier.', 400);
        }

        if (empty($data['license']))
        {
            throw new \InvalidArgumentException('Missing license key.', 400);
        }

        // save the license
        $this->keywallet->save($data['product'], $data['license']);

        // make sure the data has been properly saved
        $license = $this->keywallet->find($data['product']);

        if ($license === false)
        {
            throw new \UnexpectedValudException('Unable to save the license key.', 500);
        }

        /**
         * Extract resource ID from slug.
         * 
         * @since 2.0.3
         */
        if (preg_match("/\/([a-z0-9_\-]+)\.php$/", $data['product'], $match))
        {
            $resourceId = $match[1];
        }
        else
        {
            $resourceId = $data['product'];
        }

        /**
         * In case WordPress found an available update before registering the license key,
         * the download URL might keep to ignore it it until the cache expires. For this reason,
         * whenever a license key is saved, the system should try to manually purge the cache
         * to force WordPress from re-prepare the update data and re-generate the download
         * URL, which should now contain the correct license information.
         * 
         * Since we don't know if we are dealing with a plugin or a theme, we should try
         * to purge the cache for both them.
         * 
         * @since 2.0.1
         */

        /** @var \VikWP\VikUpdater\Cache\CacheInterface */
        $cache = \VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.cache.engine');

        try
        {
            // attempt to delete the cache for the plugin
            $cache->delete('vikupdater.pluginapi.' . $resourceId . '.update');
        }
        catch (\VikWP\VikUpdater\Cache\Exception\CacheNotFoundException $error)
        {
            // silently catch the error in case the cache does not exist
        }

        try
        {
            // attempt to delete the cache for the theme
            $cache->delete('vikupdater.themeapi.' . $resourceId . '.update');
        }
        catch (\VikWP\VikUpdater\Cache\Exception\CacheNotFoundException $error)
        {
            // silently catch the error in case the cache does not exist
        }

        return $license;
    }

    /**
     * @inheritDoc
     */
    public function delete($pk)
    {
        $pk = (array) $pk;

        $affected = false;

        foreach ($pk as $id)
        {
            if (!is_string($id))
            {
                continue;
            }

            // attempt to delete the license
            $affected = $this->keywallet->delete($id) || $affected;
        }

        return $affected;
    }

    /**
     * Returns an array containing all the registered licenses.
     * 
     * @return  array
     */
    public function getLicenses()
    {
        $licenses = [];

        foreach ($this->keywallet as $key)
        {
            $licenses[] = $key;
        }

        // sort by descending date
        usort($licenses, function($a, $b) {
            return strtotime($b->modified ?: $b->created) - strtotime($a->modified ?: $a->created);
        });

        return $licenses;
    }

    /**
     * Returns an hashmap containing all the subscribed plugins and themes.
     * 
     * @return  array
     */
    public function getProducts()
    {
        $options = [];

        /** @var VikWP\VikUpdater\WordPress\API\UpdatesObserver */
        $updatesObserver = \VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.updates.observer');

        // get all the subscribed plugins
        $subscriptions = $updatesObserver->getPlugins();

        foreach ($subscriptions as $subscription)
        {
            // check whether the subscription supports the licensing system (true by default)
            $license = $subscription['license'] ?? true;

            if (!$license)
            {
                continue;
            }

            // get plugin identifier
            $pluginId = $subscription['plugin'] ?? null;

            if (!$pluginId || $pluginId === 'vikupdater/vikupdater.php')
            {
                continue;
            }

            // get plugin name
            $name = $subscription['name'] ?? basename($pluginId);

            // register plugin
            $options[$pluginId] = [
                'name'  => $name,
                'group' => __('Plugins'),
            ];
        }

        // get all the subscribed themes
        $subscriptions = $updatesObserver->getThemes();

        foreach ($subscriptions as $subscription)
        {
            // check whether the subscription supports the licensing system (true by default)
            $license = $subscription['license'] ?? true;

            if (!$license)
            {
                continue;
            }

            // get theme identifier
            $themeId = $subscription['theme'] ?? null;

            if (!$themeId)
            {
                continue;
            }

            // get theme name
            $name = $subscription['name'] ?? $themeId;

            // register theme
            $options[$themeId] = [
                'name'  => $name,
                'group' => __('Themes'),
            ];
        }

        return $options;
    }
}
