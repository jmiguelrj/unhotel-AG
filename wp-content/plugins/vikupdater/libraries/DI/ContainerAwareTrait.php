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

use VikWP\VikUpdater\DI\Exception\ContainerNotFoundException;

/**
 * Defines the trait for a Container Aware Class.
 *
 * @since 2.0
 */
trait ContainerAwareTrait
{
    /** @var Container */
    private $container;

    /**
     * Gets the DI container.
     *
     * @return  Container
     *
     * @throws  ContainerNotFoundException  May be thrown if the container has not been set.
     */
    protected function getContainer()
    {
        if ($this->container)
        {
            return $this->container;
        }

        throw new ContainerNotFoundException('Container not set in ' . get_class($this));
    }

    /**
     * Sets the DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  self       This object to support chaining.
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }
}
