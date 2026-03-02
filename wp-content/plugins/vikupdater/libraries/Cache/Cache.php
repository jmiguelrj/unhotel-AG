<?php
/** 
 * @package     VikUpdater
 * @subpackage  cache
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

namespace VikWP\VikUpdater\Cache;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Generic cache object.
 * It requires a cache implementor and a factory callback.
 * 
 * @since 2.0
 */
class Cache
{
    /**
     * The provider that will be used to cache the resource.
     * 
     * @var CacheInterface
     */
    protected $provider;

    /**
     * The factory callback to use in case the cache is empty or expired.
     * 
     * @var callable
     */
    protected $factory;

    /**
     * Class constructor.
     * 
     * @param  CacheInterface  $provider  The provider used to cache the resource.
     * @param  callable        $factory   The factory used to generate the resource.
     */
    public function __construct(CacheInterface $provider, callable $factory)
    {
        $this->provider = $provider;
        $this->factory  = $factory;
    }

    /**
     * Returns the cached value for the matching key.
     * 
     * @param   string  $key  The cache key.
     * 
     * @return  mixed   The cached value.
     */
    public function get(string $key)
    {
        try
        {
            // attempt to get the cached value
            $value = $this->provider->get($key);
        }
        catch (CacheException $e)
        {
            // value missing or expired, create it now
            $value = call_user_func_array($this->factory, [$key]);

            // cache the value for later use
            $this->provider->set($key, $value);
        }

        return $value;
    }
}
