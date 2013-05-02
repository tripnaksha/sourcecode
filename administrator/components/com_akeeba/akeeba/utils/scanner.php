<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: scanner.php 158 2010-06-10 08:46:49Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

/**
 * A filesystem scanner, for internal use
 */
class AEUtilScanner
{
	public static function &getFiles($folder)
	{
		// Initialize variables
		$arr = array();
		$false = false;

		if(!is_dir($folder) && !is_dir($folder.'/')) return $false;

		$handle = @opendir($folder);
		if ($handle === FALSE) {
			$handle = @opendir($folder.'/');
		}
		// If directory is not accessible, just return FALSE
		if ($handle === FALSE) {
			return $false;
		}

		while ( (($file = @readdir($handle)) !== false) )
		{
			if (($file != '.') && ($file != '..'))
			{
				// # Fix 2.4.b1: Do not add DS if we are on the site's root and it's an empty string
				// # Fix 2.4.b2: Do not add DS is the last character _is_ DS
				$ds = ($folder == '') || ($folder == '/') || (@substr($folder, -1) == '/') || (@substr($folder, -1) == DIRECTORY_SEPARATOR) ? '' : DIRECTORY_SEPARATOR;
				$dir = $folder . $ds . $file;
				$isDir = @is_dir($dir);
				if (!$isDir) {
					$arr[] = $file;
				}
			}
		}
		@closedir($handle);

		return $arr;
	}

	public static function &getFolders($folder)
	{
		// Initialize variables
		$arr = array();
		$false = false;

		if(!is_dir($folder) && !is_dir($folder.'/')) return $false;

		$handle = @opendir($folder);
		if ($handle === FALSE) {
			$handle = @opendir($folder.'/');
		}
		// If directory is not accessible, just return FALSE
		if ($handle === FALSE) {
			return $false;
		}

		while ( (($file = @readdir($handle)) !== false) )
		{
			if (($file != '.') && ($file != '..'))
			{
				$ds = ($folder == '') || ($folder == '/') || (@substr($folder, -1) == '/') || (@substr($folder, -1) == DIRECTORY_SEPARATOR) ? '' : DIRECTORY_SEPARATOR;
				$dir = $folder . $ds . $file;
				$isDir = @is_dir($dir);
				if ($isDir) {
					$arr[] = $file;
				}
			}
		}
		@closedir($handle);

		return $arr;
	}
}
