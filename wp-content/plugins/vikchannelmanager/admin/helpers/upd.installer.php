<?php
/**
 * @package     VikChannelManager
 * @subpackage  com_vikchannelmanager
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @link        https://e4jconnect.com - https://vikwp.com
 */

// No direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

abstract class VikUpdaterInstaller {

	public static function unzip($src, $dest) {
		jimport('joomla.filesystem.archive');

		if (defined('_JEXEC') && !class_exists('JArchive')) {
			/**
			 * @joomlaonly 	Joomla 4 support
			 */

			// get temporary path
			$tmp_path = JFactory::getApplication()->get('tmp_path');

			// instantiate archive class
			$archive = new Joomla\Archive\Archive(array('tmp_path' => $tmp_path));

			// extract the archive
			return $archive->extract($src, $dest);
		}

		// backward compatibility
		return JArchive::extract($src, $dest);
	}
	
	/**
	 * We wrap all queries on a try-catch statement
	 * to avoid Exceptions to be thrown and abort
	 * the entire update process.
	 * 
	 * @param 	array 	$arr 	the list of queries to execute.
	 * 
	 * @return 	void
	 * 
	 * @since 	1.6.13
	 * @since 	1.9.1   SQL errors will not stop the loop.
	 */
	public static function executeQueries($arr)
	{
		$dbo = JFactory::getDbo();

		foreach ($arr as $q) {
			$dbo->setQuery($q);
			try {
				$dbo->execute();
			} catch (Exception $e) {
				// we do not raise any error/warning messages.
			}
		}
	}
	
	public static function uninstall($root) {
		if( is_dir($root) ) {
			return self::unlinkDir($root);
		} else {
			return unlink($root);
		}
	}
	
	private static function unlinkDir($root) { 
   		$files = array_diff(scandir($root), array('.','..')); 
    	foreach( $files as $file ) { 
      		(is_dir("$root/$file")) ? self::unlinkDir("$root/$file") : unlink("$root/$file"); 
    	} 
    	return rmdir($root); 
	}
	
	public static function copyFile($src, $dest) {
		if( file_exists($src) ) {
			unlink($dest);
			return copy($src, $dest);
		}
		return false;
	}
	
	public static function smartCopy($source, $dest, $options=array('folderPermission' => 0755, 'filePermission' => 0755)) {
		$result = false;
	
		if( is_file($source) ) {
			$__dest = $dest;
			if( $dest[strlen($dest)-1] == '/' ) {
				if( !file_exists($dest) ) {
					cmfcDirectory::makeAll($dest, $options['folderPermission'], true);
				}
				$__dest = $dest . "/" . basename($source);
			}
			
			$result = copy($source, $__dest);
			chmod($__dest, $options['filePermission']);
		} else if( is_dir($source) ) {
			if( $dest[strlen($dest)-1] == '/' ) {
				if( $source[strlen($source)-1] == '/' ) {
					//Copy only contents
				} else {
					//Change parent itself and its contents
					$dest = $dest . basename($source);
					@mkdir($dest);
					chmod($dest, $options['filePermission']);
				}
			} else {
				if( $source[strlen($source)-1] == '/' ) {
					//Copy parent directory with new name and all its content
					@mkdir($dest, $options['folderPermission']);
					chmod($dest, $options['filePermission']);
				} else {
					//Copy parent directory with new name and all its content
					@mkdir($dest, $options['folderPermission']);
					chmod($dest, $options['filePermission']);
				}
			}
	
			$dirHandle = opendir($source);
			while( $file = readdir($dirHandle) ) {
				if( $file != "." && $file != ".." ) {
					if( !is_dir($source . "/" . $file) ) {
						$__dest = $dest . "/" . $file;
					} else {
						$__dest = $dest . "/" . $file;
					}
					$result = self::smartCopy($source . "/" . $file, $__dest, $options);
				}
			}
			closedir($dirHandle);
	
		} else {
			$result = false;
		}
		return $result;
	}
	
}
