<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2023 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Handles the update installation and assets alignment.
 * 
 * @since 	1.8.19
 */
class VCMUpdateHandler
{
	/**
	 * Gather the information to perform a request to obtain the
	 * necessary data to check if an update should be installed.
	 * 
	 * @param 	bool 	$validate 	whether the status should be updated.
	 * @param 	string 	$version    whether to force a version to update from.
	 * 
	 * @return 	array 	the update data information.
	 * 
	 * @throws 	Exception
	 * 
	 * @since 	1.9  	added argument $version.
	 */
	public static function retrieve_update_data($validate = false, $version = null)
	{
		$apikey = VikChannelManager::getApiKey();

		$platform_version = (new JVersion)->getShortVersion();

		$from_version = $version ?? VIKCHANNELMANAGER_SOFTWARE_VERSION;

		$e4jc_url = "https://e4jconnect.com/channelmanager/?r=upd&c=generic";

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<!-- VikChannelManager UPD Request e4jConnect.com - VikBooking -->
<UpdateRQ xmlns="http://www.e4jconnect.com/schemas/updrq">
	<Notify client="' . JUri::root() . '"/>
	<Api key="' . $apikey . '"/>
	<Fetch vcm_version="' . $from_version . '" joomla_version="' . $platform_version . '"/>
</UpdateRQ>';

		$e4jC = new E4jConnectRequest($e4jc_url);
		$e4jC->setPostFields($xml);
		$rs = $e4jC->exec();

		if ($validate) {
			VikChannelManager::validateChannelResponse((string) $rs);
		}

		if ($e4jC->getErrorNo() || !$rs) {
			throw new Exception(@curl_error($e4jC->getCurlHeader()), 400);
		}

		if (substr($rs, 0, 9) == 'e4j.error') {
			throw new Exception(VikChannelManager::getErrorFromMap($rs), 400);
		} elseif (substr($rs, 0, 11) == 'e4j.warning') {
			throw new Exception(VikChannelManager::getErrorFromMap($rs), 400);
		}

		$update_data = json_decode($rs, true);

		if (!is_array($update_data)) {
			throw new Exception('Could not decode update data', 500);
		}

		return $update_data;
	}

	/**
	 * Processes the retrieved update data to perform an update installation.
	 * 
	 * @param 	array 	$data 	the retrieved update information data.
	 * 
	 * @return 	bool
	 * 
	 * @throws 	Exception 		if the process fails, an Exception is thrown.
	 */
	public static function process_update_data(array $data)
	{
		// require dependencies
		require_once VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'upd.installer.php';

		// download package locally
		$package_file = VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'updater.zip';

		if (!is_file($package_file)) {
			// unlimited max execution time
			@set_time_limit(0);
			
			if (VCMPlatformDetection::isWordPress()) {
				// make connection to the VikWP servers
				(new JHttp)->get($data['url'], [
					// turn on stream to push body within a file
					'stream' => true,
					// define the filepath in which the data will be pushed
					'filename' => $package_file,
					// make sure the request is non blocking
					'blocking' => true,
					// force timeout to 120 seconds
					'timeout' => 120,
					// disable the SSL peer verification
					'sslverify' => false,
				]);
			} else {
				$curl_opt = [
					CURLOPT_FILE 	=> fopen($package_file, 'w+'),
					CURLOPT_TIMEOUT => 3600,
					CURLOPT_URL 	=> $data['url'],
				];
				$ch = curl_init();
				curl_setopt_array($ch, $curl_opt);
				curl_exec($ch);
			}
		}

		// extract archive
		$extracted = VikUpdaterInstaller::unzip($package_file, VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers');
		if (!$extracted) {
			// always unlink the old package, if exists
			if (is_file($package_file)) {
				unlink($package_file);
			}

			// raise error
			throw new Exception(JText::_('VCMDOUPDATEUNZIPERROR'), 500);
		}

		$dir_path = VCM_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . $data['upd_name'];
		if (!is_dir($dir_path)) {
			// always unlink the old package, if exists
			if (is_file($package_file)) {
				unlink($package_file);
			}

			// raise error
			throw new Exception(JText::_('VCMDOUPDATEPACKAGENOTFOUND'), 500);
		}

		// execute queries, if any
		if (!empty($data['queries'])) {
			VikUpdaterInstaller::executeQueries($data['queries']);
		}

		// process directories and files
		if (VCMPlatformDetection::isWordPress()) {
			/**
			 * @wponly  files structure and paths are totally different
			 */

			// ROOT FILES
			$root_files = ['autoload.php', 'defines.php', 'vikchannelmanager.php'];
			foreach ($root_files as $file) {
				VikUpdaterInstaller::copyFile(
					VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.$file,
					VIKCHANNELMANAGER_BASE.DIRECTORY_SEPARATOR.$file
				);
			}

			// ROOT FOLDERS
			$root_folders = [
				[VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'sql', VIKCHANNELMANAGER_BASE.DIRECTORY_SEPARATOR.'sql'],
				[VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'libraries', VIKCHANNELMANAGER_BASE.DIRECTORY_SEPARATOR.'libraries'],
			];
			
			foreach ($root_folders as $folder) {
				if (!VikUpdaterInstaller::smartCopy($folder[0], $folder[1])) {
					VikError::raiseWarning('', 'Please report to e4j: Error copying the folder: '.$folder[0].' - to: '.$folder[1]);
				}
			}
			
			// ADMIN FILES 
			$admin_files = ['controller.php', 'access.xml', 'config.xml'];
			foreach ($admin_files as $file) {
				VikUpdaterInstaller::copyFile(
					VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.$file,
					VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.$file
				);
			}
			
			$admin_folders = [
				[VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'assets', VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'assets'],
				[VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'controllers', VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'controllers'],
				[VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'helpers', VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'],
				[VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'views', VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'views'],
				[VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'layouts', VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'layouts'],
				[VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'models', VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'models'],
				[VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'language', VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'language'],
			];
			
			foreach ($admin_folders as $folder) {
				if (!VikUpdaterInstaller::smartCopy($folder[0], $folder[1])) {
					VikError::raiseWarning('', 'Please report to e4j: Error copying the folder: '.$folder[0].' - to: '.$folder[1]);
				}
			}
			
			// SITE FILES
			$site_files = ['controller.php'];
			foreach ($site_files as $file) {
				$res = VikUpdaterInstaller::copyFile(
					VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.$file,
					VCM_SITE_PATH.DIRECTORY_SEPARATOR.$file
				);
			}
			
			$site_folders = [
				[VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'assets', VCM_SITE_PATH.DIRECTORY_SEPARATOR.'assets'],
				[VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'helpers', VCM_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'],
				[VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'views', VCM_SITE_PATH.DIRECTORY_SEPARATOR.'views'],
				[VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'controllers', VCM_SITE_PATH.DIRECTORY_SEPARATOR.'controllers'],
				[VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'language', VCM_SITE_PATH.DIRECTORY_SEPARATOR.'language'],
			];
			
			foreach ($site_folders as $folder) {
				if (!VikUpdaterInstaller::smartCopy($folder[0], $folder[1])) {
					VikError::raiseWarning('', 'Please report to e4j: Error copying the folder: '.$folder[0].' - to: '.$folder[1]);
				}
			}
			
			VikUpdaterInstaller::uninstall($package_file);
			VikUpdaterInstaller::uninstall(VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name']);
			if (is_dir(VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'__MACOSX')) {
				VikUpdaterInstaller::uninstall(VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'__MACOSX');
			}
		} else {
			/**
			 * @joomlaonly  files structure and paths are totally different
			 */

			/**
			 * Copy XML manifest file from root dir, if any.
			 * 
			 * @since 	1.8.13
			 */
			VikUpdaterInstaller::copyFile(
				implode(DIRECTORY_SEPARATOR, [JPATH_ADMINISTRATOR, 'components', 'com_vikchannelmanager', 'helpers', $data['upd_name'], 'vikchannelmanager.xml']),
				implode(DIRECTORY_SEPARATOR, [JPATH_ADMINISTRATOR, 'components', 'com_vikchannelmanager', 'vikchannelmanager.xml'])
			);

			// ADMIN FILES 
			$admin_files = ['vikchannelmanager.php', 'controller.php', 'install.mysql.utf8.sql', 'uninstall.mysql.utf8.sql', 'access.xml', 'config.xml'];
			foreach ($admin_files as $file) {
				VikUpdaterInstaller::copyFile(
					JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.$file,
					JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.$file
				);
			}
			
			$admin_folders = [
				[JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'assets', JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'assets'],
				[JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'controllers', JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'controllers'],
				[JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'helpers', JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'helpers'],
				[JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'views', JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'views'],
				[JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'layouts', JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'layouts'],
				[JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'language', JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.'en-GB'],
			];
			
			foreach ($admin_folders as $folder) {
				if (!VikUpdaterInstaller::smartCopy($folder[0], $folder[1])) {
					VikError::raiseWarning('', 'Please report to e4j: Error copying the folder: '.$folder[0].' - to: '.$folder[1]);
				}
			}

			// Check Admin lang it-IT (VCM 1.6.1)
			if (file_exists(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.'it-IT')) {
				if (file_exists(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.'en-GB'.DIRECTORY_SEPARATOR.'it-IT.com_vikchannelmanager.ini')) {
					@rename(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.'en-GB'.DIRECTORY_SEPARATOR.'it-IT.com_vikchannelmanager.ini', JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.'it-IT'.DIRECTORY_SEPARATOR.'it-IT.com_vikchannelmanager.ini');
				}
				if (file_exists(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.'en-GB'.DIRECTORY_SEPARATOR.'it-IT.com_vikchannelmanager.sys.ini')) {
					@rename(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.'en-GB'.DIRECTORY_SEPARATOR.'it-IT.com_vikchannelmanager.sys.ini', JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.'it-IT'.DIRECTORY_SEPARATOR.'it-IT.com_vikchannelmanager.sys.ini');
				}
			}
			
			// SITE FILES
			$site_files = ['controller.php', 'vikchannelmanager.php'];
			foreach ($site_files as $file) {
				$res = VikUpdaterInstaller::copyFile(
					JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.$file,
					JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.$file
				);
			}
			
			$site_folders = [
				[JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'assets', JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'assets'],
				[JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'helpers', JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'helpers'],
				[JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'views', JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'views'],
				[JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'controllers', JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'controllers'],
				[JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name'].DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'language', JPATH_SITE.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.'en-GB'],
			];
			
			foreach ($site_folders as $folder) {
				if (!VikUpdaterInstaller::smartCopy($folder[0], $folder[1])) {
					VikError::raiseWarning('', 'Please report to e4j: Error copying the folder: '.$folder[0].' - to: '.$folder[1]);
				}
			}

			// Check Site lang it-IT (VCM 1.6.1)
			if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.'it-IT')) {
				if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.'en-GB'.DIRECTORY_SEPARATOR.'it-IT.com_vikchannelmanager.ini')) {
					@rename(JPATH_SITE.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.'en-GB'.DIRECTORY_SEPARATOR.'it-IT.com_vikchannelmanager.ini', JPATH_SITE.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.'it-IT'.DIRECTORY_SEPARATOR.'it-IT.com_vikchannelmanager.ini');
				}
			}

			VikUpdaterInstaller::uninstall($package_file);
			VikUpdaterInstaller::uninstall(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$data['upd_name']);
			if (is_dir(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'__MACOSX')) {
				VikUpdaterInstaller::uninstall(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikchannelmanager'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'__MACOSX');
			}
		}

		// update version/manifest cache
		if (!empty($data['latest_v'])) {
			VikChannelManager::updateManifestCacheVersion($data['latest_v']);
		}

		return true;
	}
}
