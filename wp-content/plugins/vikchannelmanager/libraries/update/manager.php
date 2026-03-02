<?php
/** 
 * @package   	VikChannelManager - Libraries
 * @subpackage 	update
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

VikChannelManagerLoader::import('adapter.database.helper');

/**
 * Class used to handle the upgrade of the plugin.
 *
 * @since 1.0
 */
class VikChannelManagerUpdateManager
{
	/**
	 * Checks if the current version should be updated.
	 *
	 * @param 	string 	 $version 	The version to check.
	 *
	 * @return 	boolean  True if should be updated, otherwise false.
	 */
	public static function shouldUpdate($version)
	{
		if (is_null($version))
		{
			return false;
		}

		return version_compare($version, VIKCHANNELMANAGER_SOFTWARE_VERSION, '<');
	}

	/**
	 * Executes the SQL file for the installation of the plugin.
	 *
	 * @return 	void
	 *
	 * @uses 	execSqlFile()
	 * @uses 	installAcl()
	 */
	public static function install()
	{
		self::execSqlFile(VIKCHANNELMANAGER_BASE . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'install.mysql.utf8.sql');
		
		// populate configuration fields upon installation
		$vb_params['currencysymb'] = "&euro;";
		$vb_params['currencyname'] = "EUR";
		$vb_params['emailadmin'] = "";
		$vb_params['dateformat'] = "%Y/%m/%d";
		if (file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'lib.vikbooking.php')) {
			require_once (VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'lib.vikbooking.php');
			$vb_params['currencysymb'] = VikBooking::getCurrencySymb(true);
			$vb_params['currencyname'] = VikBooking::getCurrencyName(true);
			$vb_params['emailadmin'] = VikBooking::getAdminMail(true);
			$vb_params['dateformat'] = VikBooking::getDateFormat(true);
		}
		$dbo = JFactory::getDbo();
		foreach ($vb_params as $k => $v) {
			$q = "UPDATE `#__vikchannelmanager_config` SET `setting`=".$dbo->quote($v)." WHERE `param`=".$dbo->quote($k)." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		//
		
		self::installAcl();
	}

	/**
	 * Executes the SQL file for the uninstallation of the plugin.
	 *
	 * @return 	void
	 *
	 * @uses 	execSqlFile()
	 * @uses 	uninstallAcl()
	 */
	public static function uninstall()
	{
		self::execSqlFile(VIKCHANNELMANAGER_BASE . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'uninstall.mysql.utf8.sql');
		self::uninstallAcl();
	}

	/**
	 * Launches the process to finalise the update.
	 *
	 * @param 	string 	$version 	The current version.
	 *
	 * @uses 	getFixer()
	 * @uses 	installSql()
	 * @uses 	installAcl()
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

		// install SQL statements

		$res = self::installSql($version);

		if ($res === false)
		{
			return false;
		}

		// install ACL

		$res = self::installAcl();

		if ($res === false)
		{
			return false;
		}

		// trigger after installation routine

		$res = $fixer->afterInstallation();

		return ($res === false ? false : true);
	}

	/**
	 * Get the script class to run the installation methods.
	 *
	 * @param 	string 	$version 	The current version.
	 *
	 * @return 	VikChannelManagerUpdateFixer
	 */
	protected static function getFixer($version)
	{
		VikChannelManagerLoader::import('update.fixer');
	
		return new VikChannelManagerUpdateFixer($version);
	}

	/**
	 * Provides the installation of the ACL routines.
	 *
	 * @return 	boolean  True on success, otherwise false.	
	 */
	protected static function installAcl()
	{
		JLoader::import('adapter.acl.access');
		$actions = JAccess::getActions('vikchannelmanager');

		$roles = array(
			get_role('administrator'),
		);

		foreach ($roles as $role)
		{
			if ($role)
			{
				foreach ($actions as $action)
				{
					$cap = JAccess::adjustCapability($action->name, 'com_vikchannelmanager');
					$role->add_cap($cap, true);
				}
			}
		}

		return true;
	}

	/**
	 * Provides the uninstallation of the ACL routines.
	 *
	 * @return 	boolean  True on success, otherwise false.	
	 */
	protected static function uninstallAcl()
	{
		JLoader::import('adapter.acl.access');
		$actions = JAccess::getActions('vikchannelmanager');

		$roles = array(
			get_role('administrator'),
		);

		foreach ($roles as $role)
		{
			if ($role)
			{
				foreach ($actions as $action)
				{
					$cap = JAccess::adjustCapability($action->name, 'com_vikchannelmanager');
					$role->remove_cap($cap);
				}
			}
		}

		return true;
	}

	/**
	 * Run all the proper SQL files.
	 *
	 * @param 	string 	 $version 	The current version.
	 *
	 * @return 	boolean  True on success, otherwise false.
	 *
	 * @uses 	execSqlFile()
	 */
	protected static function installSql($version)
	{
		$dbo = JFactory::getDbo();

		$ok = true;

		$sql_base = VIKCHANNELMANAGER_BASE . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'update' . DIRECTORY_SEPARATOR . 'mysql' . DIRECTORY_SEPARATOR;

		try
		{
			foreach (glob($sql_base . '*.sql') as $file)
			{
				$name  = basename($file);
				$sql_v = substr($name, 0, strrpos($name, '.'));

				if (version_compare($sql_v, $version, '>'))
				{
					// in case the SQL version is newer, execute the queries listed in the file
					self::execSqlFile($file, $dbo);
				}
			}
		}
		catch (Exception $e)
		{
			$ok = false;
		}

		return $ok;
	}

	/**
	 * Executes all the queries contained in the specified file.
	 *
	 * @param 	string 		$file 	The SQL file to launch.
	 * @param 	JDatabase 	$dbo 	The database driver handler.
	 *
	 * @return 	void
	 */
	protected static function execSqlFile($file, $dbo = null)
	{
		if (!is_file($file))
		{
			return;
		}

		if ($dbo === null)
		{
			$dbo = JFactory::getDbo();
		}

		$handle = fopen($file, 'r');

		$bytes = '';
		while (!feof($handle))
		{
			$bytes .= fread($handle, 8192);
		}

		fclose($handle);

		foreach (JDatabaseHelper::splitSql($bytes) as $q)
		{
			$dbo->setQuery($q);
			$dbo->execute();
		}
	}
}
