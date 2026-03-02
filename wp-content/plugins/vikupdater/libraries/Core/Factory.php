<?php
/** 
 * @package     VikUpdater
 * @subpackage  core
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

namespace VikWP\VikUpdater\Core;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

use VikWP\VikUpdater\DI\Container;

/**
 * Platform factory class.
 *
 * @since 2.0
 */
abstract class Factory
{
    /**
     * Global container object.
     *
     * @var Container
     */
    protected static $container = null;

    /**
     * Returns the global service container object, only creating it if it doesn't already exist.
     *
     * @return  Container
     */
    public static function getContainer()
    {
        if (!static::$container)
        {
            // create the default container during the first set up
            static::$container = static::createContainer();
        }

        return static::$container;
    }

    /**
     * Creates the global container object.
     *
     * @return  Container
     */
    protected static function createContainer()
    {
        $container = (new Container())
            ->registerServiceProvider(new \VikWP\VikUpdater\Service\Provider\CacheSystem)
            ->registerServiceProvider(new \VikWP\VikUpdater\Service\Provider\Keywallet)
            ->registerServiceProvider(new \VikWP\VikUpdater\Service\Provider\MessagesQueue)
            ->registerServiceProvider(new \VikWP\VikUpdater\Service\Provider\MVCFactory)
            ->registerServiceProvider(new \VikWP\VikUpdater\Service\Provider\APIResources)
            ->registerServiceProvider(new \VikWP\VikUpdater\Service\Provider\UpdatesObserver);

        /**
         * Fires the action after completing the set up of the container used to dispatch the classes.
         * It is useful to override the default classes provided by the system.
         * 
         * @param  Container  $container  The container instance.
         * 
         * @since  2.0
         */
        do_action('vikupdater_container_setup', $container);

        return $container;
    }
}
