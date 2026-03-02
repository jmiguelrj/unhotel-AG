<?php
/** 
 * @package     VikUpdater
 * @subpackage  wordpress
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

namespace VikWP\VikUpdater\WordPress\API\Resources;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

use VikWP\VikUpdater\WordPress\API\CacheableUpdate;

/**
 * Decorator used to support a caching system for the requests
 * performed by a plugin update interface.
 * 
 * @since 2.0
 */
class CacheablePluginAPI extends CacheableUpdate
{
    /** @var array */
    protected $plugin;

    /**
     * Class constructor.
     * 
     * @param  UpdateInterface  $resource  The resource used to perform the requests.
     * @param  array            $plugin    The details of the observed plugin.
     */
    public function __construct(PluginAPI $resource, array $plugin)
    {
        parent::__construct($resource, $plugin['slug']);

        $this->plugin = $plugin;
    }

    /**
     * @inheritDoc
     */
    public function getInfo()
    {
        // get plugin information from parent
        $info = parent::getInfo();

        // make sure the fetched version is higher than the currently installed one
        if (version_compare($this->plugin['version'], $info->version, '<'))
        {
            // We need to manually update the transient that WordPress uses to
            // check whether there are any newer versions available.
            // This is needed to force the update button to appear, otherwise the
            // users will be able to actually see the available update only when
            // the cache expires.
            $transient = get_site_transient('update_plugins');

            if (!isset($transient->response))
            {
                $transient->response = [];
            }

            // register the update only in case it is not reported under the response property or
            // in case the previously registered version is changed
            if (!isset($transient->response[$info->plugin]) || $transient->response[$info->plugin]->new_version != $info->version)
            {
                try
                {
                    // purge the internal cache too
                    \VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.cache.engine')->delete($this->id . '.update');
                }
                catch (\VikWP\VikUpdater\Cache\Exception\CacheNotFoundException $error)
                {
                    // silently catch the error in case the cache does not exist
                }

                // get update info
                $update = $this->checkUpdate();

                if ($update)
                {
                    // commit changes
                    $transient->response[$info->plugin] = $this->checkUpdate();
                    set_site_transient('update_plugins', $transient);
                }
            }
        }

        return $info;
    }
}
