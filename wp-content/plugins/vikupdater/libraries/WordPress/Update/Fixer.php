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
 * Implements the abstract methods to fix an update.
 *
 * Never use exit() and die() functions to stop the flow.
 * Return false instead to break the process safely.
 * 
 * @since 2.0
 */
class Fixer
{
    /**
     * The current version.
     *
     * @var string
     */
    protected $version;

    /**
     * Class constructor.
     */
    public function __construct($version)
    {
        $this->version = $version ?: '0.0.1';
    }

    /**
     * This method is called before the SQL installation.
     *
     * @return  bool  True to proceed with the update, otherwise false to stop it.
     */
    public function beforeInstallation()
    {
        return true;
    }

    /**
     * This method is called after the SQL installation.
     *
     * @return  bool  True to proceed with the update, otherwise false to stop it.
     */
    public function afterInstallation()
    {
        if (version_compare($this->version, '2.0', '<'))
        {
            // delete all the no longer used settings
            delete_option('vikupdater_update_lastcheck');
            delete_option('vikupdater_version_checks');
            delete_option('vikupdater_registration_checks');
            delete_option('vikupdater_last_version_failed_check');
        }

        return true;
    }
}
