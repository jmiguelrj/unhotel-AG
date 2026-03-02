<?php
/** 
 * @package     VikUpdater
 * @subpackage  wordpress
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

namespace VikWP\VikUpdater\WordPress\Update;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class used to handle the upgrade of the plugin.
 *
 * @since 2.0
 */
class Manager
{
    /**
     * Checks if the current version should be updated.
     *
     * @param   string  $version  The version to check.
     *
     * @return  bool    True if should be updated, otherwise false.
     */
    public static function shouldUpdate($version)
    {
        if (is_null($version))
        {
            return false;
        }

        return version_compare($version, VIKUPDATER_VERSION, '<');
    }

    /**
     * Performs during the first installation of the plugin.
     *
     * @return  void
     */
    public static function install()
    {
        // do nothing for the moment
    }

    /**
     * Performs during the definitive uninstallation of the plugin.
     *
     * @return  void
     */
    public static function uninstall()
    {
        global $wpdb;

        // delete system options
        delete_option('vikupdater_products');
        delete_option('vikupdater_keywallet');
        delete_option('vikupdater_software_version');

        // get all the existing transients that start with "_transient_vikupdater."
        $transients = $wpdb->get_results("SELECT `option_name` FROM `{$wpdb->options}` WHERE `option_name` LIKE '_transient_vikupdater.%'");

        foreach ($transients as $transient)
        {
            // detect the real transient name from the option name
            if (!preg_match("/_transient_(.*?)$/", $transient->option_name, $match))
            {
                continue;
            }

            // safely delete the transient from the database
            delete_transient($match[1]);
        }
    }

    /**
     * Launches the process to finalise the update.
     *
     * @param   string  $version  The current version.
     * 
     * @return  bool    True on success, false otherwise.
     */
    public static function update($version)
    {
        $fixer = self::getFixer($version);

        // trigger before installation routine

        $res = $fixer->beforeInstallation();

        if ($res === false)
        {
            return false;
        }

        // update stuff goes here

        // trigger after installation routine

        $res = $fixer->afterInstallation();

        return ($res === false ? false : true);
    }

    /**
     * Get the script class to run the installation methods.
     *
     * @param   string  $version  The current version.
     *
     * @return  VikWP\VikUpdater\WordPress\Update\Fixer
     */
    protected static function getFixer($version)
    {
        return new Fixer($version);
    }
}
