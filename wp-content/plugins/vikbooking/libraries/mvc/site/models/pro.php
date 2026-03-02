<?php
/** 
 * @package     VikBooking
 * @subpackage  core
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.mvc.models.form');
VikBookingLoader::import('update.license');

/**
 * VikBooking plugin License model.
 *
 * @since 1.7
 * @see   JModelForm
 */
class VikBookingModelPro extends JModelForm
{
    /**
     * Validates the provided key against the currently installed one.
     * 
     * @param   string  $key  The license key to validate.
     * 
     * @return  bool    True if equal, false otherwise.
     */
    public function validate(string $key)
    {
        if (!$key)
        {
            $this->setError(new Exception('Bad request', 400));
            return false;
        }

        if (strcmp($key, VikBookingLicense::getKey()))
        {
            $this->setError(new Exception('Forbidden', 403));
            return false;
        }

        return true;
    }

	/**
	 * Implements the request needed to downgrade the PRO version of the plugin.
	 *
	 * @return 	bool  True on success, false otherwise.
	 */
	public function downgrade()
	{
		try
        {
            JLoader::import('adapter.plugin.installer.adapter');
            $adapter = new JPluginInstallerAdapter('vikbooking', 'https://downloads.wordpress.org/plugin/vikbooking.zip');
            $adapter->install();

            VikBookingLicense::uninstall();
        }
        catch (Exception $error)
        {
            $this->setError($error);
            return false;
        }

        return true;
	}
}
