<?php
/** 
 * @package     VikUpdater
 * @subpackage  psr
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

namespace VikWP\VikUpdater\Psr\Container;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Describes the interface of a container that exposes methods to read its entries.
 * 
 * @since 2.0
 */
interface ContainerInterface
{
    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param   string  $id  Identifier of the entry to look for.
     * 
     * @return  mixed
     *
     * @throws  NotFoundExceptionInterface   No entry was found for this identifier.
     * @throws  ContainerExceptionInterface  Error while retrieving the entry.
     */
    public function get(string $id);

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param   string  $id  Identifier of the entry to look for.
     *
     * @return  bool
     */
    public function has(string $id);
}
