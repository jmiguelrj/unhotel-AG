<?php
/** 
 * @package   	VikUpdater
 * @subpackage 	filesystem
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2023 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

namespace VikWP\VikUpdater\FileSystem;

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * This class provides a common interface for Files handling.
 *
 * @since 2.0
 */
class File
{
	/**
	 * Makes file name safe to use.
	 *
	 * @param   string  $file  The name of the file (not full path).
	 *
	 * @return  string  The sanitised string.
	 */
	public static function makeSafe(string $file): string
	{
		// remove any trailing dots, as those aren't ever valid file names.
		$file = rtrim($file, '.');

		$regex = array('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#');

		return trim(preg_replace($regex, '', $file));
	}

	/**
	 * Moves an uploaded file to a destination folder.
	 *
	 * @param   string  $src 	The name of the php (temporary) uploaded file.
	 * @param   string  $dest 	The path (including filename) to move the uploaded file to.
	 *
	 * @return  bool    True on success.
	 */
	public static function upload(string $src, string $dest): bool
	{
		$ret = false;

		// ensure that the paths are valid and clean
		$src  = Path::clean($src);
		$dest = Path::clean($dest);

		// create the destination directory if it does not exist
		$baseDir = dirname($dest);

		if (!file_exists($baseDir))
		{
			Folder::create($baseDir);
		}

		if (is_writeable($baseDir) && move_uploaded_file($src, $dest))
		{
			// short circuit to prevent file permission errors
			if (Path::setPermissions($dest))
			{
				$ret = true;
			}
		}

		return $ret;
	}

	/**
	 * Write contents to a file.
	 *
	 * @param   string  $file    The full file path.
	 * @param   string  $buffer  The buffer to write.
	 *
	 * @return  bool    True on success.
	 */
	public static function write(string $file, string $buffer): bool
	{
		@set_time_limit(0);

		$file = Path::clean($file);

		// If the destination directory doesn't exist we need to create it
		if (!file_exists(dirname($file)))
		{
			if (Folder::create(dirname($file)) == false)
			{
				return false;
			}
		}
		
		return is_int(file_put_contents($file, $buffer));
	}

	/**
	 * Copy a source file to a destination.
	 *
	 * @param   string  $src   The full file path.
	 * @param   string  $dest 	The full destination path.
	 *
	 * @return  bool    True on success.
	 */
	public static function copy(string $src, string $dest): bool
	{
		return @copy(Path::clean($src), Path::clean($dest));
	}

	/**
	 * Delete one or multiple files.
	 *
	 * @param   mixed  $file  The file path-name or array of file path-names.
	 *
	 * @return  bool   True on success.
	 */
	public static function delete($file): bool
	{
		if (is_array($file))
		{
			$files = $file;
		}
		else
		{
			$files = array($file);
		}

		foreach ($files as $file)
		{
			$file = Path::clean($file);

			if (is_file($file))
			{
				// Try making the file writable first. If it's read-only, it can't be deleted
				// on Windows, even if the parent folder is writable
				@chmod($file, 0777);

				// The file should be removable as long as the owner is www-data
				if (!@unlink($file))
				{
					// impossible to remove the file, stop the process immediatelly
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Wrapper for the standard file_exists function
	 *
	 * @param   string  $file  File path
	 *
	 * @return  bool    True if path is a file.
	 */
	public static function exists(string $file): bool
	{
		return is_file(Path::clean($file));
	}
}
