<?php
/** 
 * @package     VikUpdater
 * @subpackage  wordpress
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

namespace VikWP\VikUpdater\WordPress\Cache;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

use VikWP\VikUpdater\Cache\CacheInterface;
use VikWP\VikUpdater\Cache\Exception\CacheNotFoundException;

/**
 * This system can be used to cache the resources in a static property.
 * 
 * @since 2.0
 */
class StaticCache implements CacheInterface
{
    /**
     * The static cache.
     * 
     * @param  array
     */
    protected static $cache = [];

    /**
     * @inheritDoc
     */
    public function get(string $key)
    {
        if ($this->has($key) === false)
        {
            // missing cache
            throw new CacheNotFoundException;
        }

        // expiration not supported, since the cache will be automatically
        // cleared at the next page refresh

        // return cached value
        return static::$cache[$key];
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value)
    {
        static::$cache[$key] = $value;
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key)
    {
        if ($this->has($key) === false)
        {
            // missing cache
            throw new CacheNotFoundException;
        }

        // delete the cached value
        unset(static::$cache[$key]);
    }

    /**
     * @inheritDoc
     */
    public function has(string $key)
    {
        // make sure the cache exists
        return isset(static::$cache[$key]);
    }
}
