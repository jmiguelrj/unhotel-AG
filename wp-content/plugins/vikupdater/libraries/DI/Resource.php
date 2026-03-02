<?php
/** 
 * @package     VikUpdater
 * @subpackage  di (dependency-injection)
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

namespace VikWP\VikUpdater\DI;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

use VikWP\VikUpdater\DI\Exception\ContainerProviderOverwriteAttemptException;

/**
 * Holds the provider information to put within a DI container.
 * 
 * @since 2.0
 */
class Resource
{
    /** @var string */
    protected $id;

    /** @var callable */
    protected $provider;

    /** @var mixed */
    protected $instance;

    /**
     * Shared resources cannot be instantiated more than once.
     * 
     * @var bool
     */
    protected $isShared = false;

    /**
     * Protected resources cannot be overwritten.
     * 
     * @var bool
     */
    protected $isProtected = false;

    /**
     * Class constructor.
     * 
     * @param  string  $id        The resource identifier.
     * @param  mixed   $provider  The resource provider callback/instance.
     */
    public function __construct(string $id, $provider = null)
    {
        $this->id = $id;

        if ($provider)
        {
            $this->setProvider($provider);
        }
    }

    /**
     * Registers (or updates) the given provider.
     * 
     * @param   mixed  $provider  The resource provider callback/instance.
     * 
     * @return  self   This object to support chaining.
     * 
     * @throws  ContainerProviderOverwriteAttemptException
     */
    public function setProvider($provider)
    {
        // do not update in case a protected provider was already specified
        if ($this->provider && $this->isProtected)
        {
            throw new ContainerProviderOverwriteAttemptException('Cannot overwrite protected [' . $this->id . '] provider');
        }

        // in case the specified $provider is not a closure, make it one now for easy resolution
        if (!is_callable($provider))
        {
            $provider = function() use ($provider) {
                return $provider;
            };
        }

        $this->provider = $provider;

        return $this;
    }

    /**
     * Shared resources cannot be instantiated more than once.
     * 
     * @param   bool  $isShared  True to share a resource, false to unshare it.
     * 
     * @return  self  This object to support chaining.
     */
    public function share(bool $isShared = true)
    {
        $this->isShared = $isShared;

        return $this;
    }

    /**
     * Protected resources cannot be overwritten after the first registration.
     * 
     * @param   bool  $isProtected  True to protect a resource, false to unprotect it.
     * 
     * @return  self  This object to support chaining.
     */
    public function protect(bool $isProtected = true)
    {
        $this->isProtected = $isProtected;

        return $this;
    }

    /**
     * Creates the instance.
     * 
     * @param   array  $args  Additional arguments to be injected within the provider callback.
     * 
     * @return  mixed  The created value.
     */
    public function provide(array $args)
    {
        if ($this->instance && $this->isShared)
        {
            // shared resource, do not instantiate again
            return $this->instance;
        }

        // construct requested object by injecting the specified arguments
        $this->instance = call_user_func_array($this->provider, $args);

        return $this->instance;
    }
}
