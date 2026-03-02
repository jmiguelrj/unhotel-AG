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

use VikWP\VikUpdater\DI\Exception\ContainerProviderNotFoundException;
use VikWP\VikUpdater\DI\Exception\InvalidContainerProviderException;
use VikWP\VikUpdater\Psr\Container\ContainerInterface;
use VikWP\VikUpdater\Psr\Container\NotFoundExceptionInterface;

/**
 * Implements the dependency injection pattern.
 * 
 * @since 2.0
 */
class Container implements ContainerInterface
{
    /** @var callable[] */
    protected $providers = [];

    /**
     * Holds the key aliases.
     *
     * Format:
     * 'alias' => 'key'
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * @inheritDoc
     */
    public function get(string $id, ...$args)
    {
        /** @var Resource */
        return $this->resolve($id)->provide($args);
    }

    /**
     * @inheritDoc
     */
    public function has(string $id)
    {
        try
        {
            $this->resolve($id);
        }
        catch (NotFoundExceptionInterface $e)
        {
            // entry not found
            return false;
        }

        return true;
    }

    /**
     * Registers a new provider for lazy instantiation.
     * 
     * @param   string    $id     The resource ID.
     * @param   mixed     $value  The resource value.
     * 
     * @return  Resource  The registered resource provider.
     */
    public function set(string $id, $value)
    {
        try
        {
            // try to update the existing provider
            $this->resolve($id)->setProvider($value);
        }
        catch (NotFoundExceptionInterface $e)
        {
            // provider not found, register it now
            $this->providers[$id] = new Resource($id, $value);
        }

        return $this->providers[$id];
    }

    /**
     * Creates an alias for a given key for easy access.
     *
     * @param   string  $alias  The alias name.
     * @param   string  $key    The key to alias.
     *
     * @return  self    This object to support chaining.
     */
    public function alias($alias, $key)
    {
        $this->aliases[$alias] = $key;

        return $this;
    }

    /**
     * Returns the ID of all the registered providers.
     * 
     * @return  array
     */
    public function keys()
    {
        return array_keys($this->providers);
    }

    /**
     * Register a service provider to the container.
     *
     * @param   ServiceProviderInterface  $provider  The service provider to register.
     *
     * @return  self  This object to support chaining.
     */
    public function registerServiceProvider(ServiceProviderInterface $provider)
    {
        $provider->register($this);

        return $this;
    }

    /**
     * Returns the provider details of the specified resource.
     * 
     * @param   string    $id  The resource ID.
     * 
     * @return  Resource  The registered resource provider.
     * 
     * @throws  ContainerProviderNotFoundException
     * @throws  InvalidContainerProviderException
     */
    protected function resolve(string $id)
    {
        // check if we have an alias of a registered resource
        $id = $this->aliases[$id] ?? $id;

        // make sure we have a provider able to initialize the requested resource
        if (!isset($this->providers[$id]))
        {
            throw new ContainerProviderNotFoundException('Cannot find [' . $id . '] provider');
        }

        // make sure the registered provider is a valid resource
        if (!$this->providers[$id] instanceof Resource)
        {
            throw new InvalidContainerProviderException('Registered an invalid provider for [' . $id . '] resource');
        }

        return $this->providers[$id];
    }
}
