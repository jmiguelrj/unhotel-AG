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
 * Interface used to implement a caching system for any kind of resource.
 * 
 * @since 2.0
 */
interface CacheInterface
{
    /**
     * Returns the cached value for the matching key.
     * 
     * @param   string  $key  The cache key.
     * 
     * @return  mixed   The cached value.
     * 
     * @throws  Exception\CacheNotFoundException
     * @throws  Exception\CacheExpiredException
     */
    public function get(string $key);

    /**
     * Sets the specified value within the cache.
     * 
     * @param   string  $key    The cache key.
     * @param   mixed   $value  The value to cache.
     * 
     * @return  void
     */
    public function set(string $key, $value);

    /**
     * Deleted the cache resource matching the provided key.
     * 
     * @param   string  $key    The cache key.
     * 
     * @return  void
     * 
     * @throws  Exception\CacheNotFoundException
     */
    public function delete(string $key);

    /**
     * Checks whether there is a  cached value for the matching key.
     * 
     * @param   string  $key  The cache key.
     * 
     * @return  bool    True if available and not expired, false otherwise.
     */
    public function has(string $key);
}
