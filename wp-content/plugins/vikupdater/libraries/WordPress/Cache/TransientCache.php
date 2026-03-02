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
 * This system can be used to cache the resources through the WP transients.
 * 
 * @since 2.0
 */
class TransientCache implements CacheInterface
{
    /**
     * A configuration registry.
     * 
     * @param  array
     */
    protected $options;

    /**
     * Class constructor.
     * 
     * @param  array  $options  A configuration array.
     */
    public function __construct(array $options = [])
    {
        $this->options = wp_parse_args(
            $options,
            [
                // default expiration time (in minutes)
                'expiration' => 15,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function get(string $key)
    {
        // get transient
        $value = get_transient($key);

        if ($value === false)
        {
            // missing and expired cache are treated in the same way
            throw new CacheNotFoundException;
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value)
    {
        // save the transient by using the provided expiration (in minutes)
        set_transient(
            $key,
            $value,
            abs((int) $this->options['expiration'] * MINUTE_IN_SECONDS)
        );
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key)
    {
        // Attempt to delete the transient.
        // Do not care whether the transient exists or not.
        delete_transient($key);
    }

    /**
     * @inheritDoc
     */
    public function has(string $key)
    {
        // make sure the transient exists
        return get_transient($key) !== false;
    }
}
