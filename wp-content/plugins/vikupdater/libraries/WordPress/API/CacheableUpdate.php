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
 * Decorator used to support a caching system for the requests
 * performed by an update interface.
 * 
 * @since 2.0
 */
class CacheableUpdate implements UpdateInterface
{
    /** @var UpdateInterface */
    protected $resource;

    /** @var string */
    protected $id;

    /**
     * Class constructor.
     * 
     * @param  UpdateInterface  $resource  The resource used to perform the requests.
     * @param  string           $id        The cache identifier (different for each resource).
     */
    public function __construct(UpdateInterface $resource, string $id)
    {
        $this->resource = $resource;

        // extract class name from the namespace of the given resource
        $chunks = explode('\\', get_class($resource));
        // construct the cache prefix
        $this->id = 'vikupdater.' . strtolower(array_pop($chunks)) . '.' . $id;
    }

    /**
     * @inheritDoc
     */
    public function getInfo()
    {
        // take advantage of the cache to save multiple requests
        return (new \VikWP\VikUpdater\Cache\Cache(
            /** @var VikWP\VikUpdater\Cache\CacheInterface */
            \VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.cache.engine', [
                // Once every 5 minutes.
                // We don't need to apply a strong cache here because this request is
                // made only while accessing the details page of a resource.
                'expiration' => 5,
            ]),
            function() {
                // access the details information in case the cache is missing or expired
                return $this->resource->getInfo();
            }
        ))->get($this->id . '.info.' . get_user_locale());
    }

    /**
     * @inheritDoc
     */
    public function checkUpdate()
    {
        // take advantage of the cache to save multiple requests
        return (new \VikWP\VikUpdater\Cache\Cache(
            /** @var VikWP\VikUpdater\Cache\CacheInterface */
            \VikWP\VikUpdater\Core\Factory::getContainer()->get('vikupdater.cache.engine', [
                // Twice daily, plus a random identifier.
                // This adds a sort of unpredictability to avoid making
                // all the requests simultaneously.
                'expiration' => 720 + rand(0, 720),
            ]),
            function() {
                // look for an available update
                return $this->resource->checkUpdate();
            }
        ))->get($this->id . '.update');
    }
}
