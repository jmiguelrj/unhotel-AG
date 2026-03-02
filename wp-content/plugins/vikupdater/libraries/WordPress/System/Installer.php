<?php
/** 
 * @package     VikUpdater
 * @subpackage  wordpress
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

namespace VikWP\VikUpdater\WordPress\System;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

use VikWP\VikUpdater\WordPress\Update\Manager as UpdateManager;

/**
 * Class used to handle the activation, deactivation and 
 * uninstallation of VikUpdater plugin.
 *
 * @since 2.0
 */
class Installer
{
    /**
     * Flag used to init the class only once.
     *
     * @var bool
     */
    protected static $init = false;

    /**
     * Initialize the class attaching wp actions.
     *
     * @return  void
     */
    public static function onInit()
    {
        // init only if not done yet
        if (static::$init === false)
        {
            // attempt activation here because in case the installation failed
            // we need to execute it now
            static::activate();

            // mark flag as true to avoid init it again
            static::$init = true;
        }
    }

    /**
     * Handles the activation of the plugin.
     *
     * @return  void
     */
    public static function activate()
    {
        // get installed software version
        $version = get_option('vikupdater_software_version', null);

        // in case the version does not exist, check whether the database already
        // have some deprecated settings (used by VikUpdater 1.4.4 or lower)
        if (is_null($version) && get_option('vikupdater_update_lastcheck'))
        {
            // setting found, assume we currently have VikUpdater 1.4.4
            // to bypass the plugin installation
            $version = '1.4.4';
        }

        // check if the plugin has been already installed
        if (is_null($version))
        {
            // dispatch UPDATER to launch installation queries
            UpdateManager::install();

            // mark the plugin has installed to avoid duplicated installation queries
            update_option('vikupdater_software_version', VIKUPDATER_VERSION);
        }
    }

    /**
     * Handles the deactivation of the plugin.
     *
     * @return  void
     */
    public static function deactivate()
    {
        // do nothing for the moment
    }

    /**
     * Handles the uninstallation of the plugin.
     *
     * @return  void
     */
    public static function uninstall()
    {
        // dispatch UPDATER to complete the uninstallation
        UpdateManager::uninstall();
    }

    /**
     * Checks if the current version should be updated
     * and, eventually, processes it.
     * 
     * @return  void
     */
    public static function update()
    {
        // get installed software version
        $version = get_option('vikupdater_software_version', null);

        // in case the version does not exist, check whether the database already
        // have some deprecated settings (used by VikUpdater 1.4.4 or lower)
        if (is_null($version) && get_option('vikupdater_update_lastcheck'))
        {
            // setting found, assume we currently have VikUpdater 1.4.4
            $version = '1.4.4';
        }

        // check if we are running an older version
        if (UpdateManager::shouldUpdate($version))
        {
            // process the update (we don't need to raise an error)
            UpdateManager::update($version);

            // update cached plugin version
            update_option('vikupdater_software_version', VIKUPDATER_VERSION);
        }
    }
}
