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

/**
 * Defines the interface for a Container Aware class.
 *
 * @since  1.0
 */
interface ContainerAwareInterface
{
    /**
     * Sets the DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  static     This object to support chaining.
     */
    public function setContainer(Container $container);
}
