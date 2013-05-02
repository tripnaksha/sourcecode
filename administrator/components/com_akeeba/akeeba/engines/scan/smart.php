<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: smart.php 158 2010-06-10 08:46:49Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

/* Windows system detection */
if(!defined('_AKEEBA_IS_WINDOWS'))
{
	if (function_exists('php_uname'))
		define('_AKEEBA_IS_WINDOWS', stristr(php_uname(), 'windows'));
	else
		define('_AKEEBA_IS_WINDOWS', DS == '\\');
}

/**
 * A filesystem scanner which uses opendir() and is smart enough to make large directories
 * be scanned inside a step of their own.
 *
 * The idea is that if it's not the first operation of this step and the number of contained
 * directories AND files is more than double the number of allowed files per fragment, we should
 * break the step immediately.
 *
 */
class AEScanSmart extends AEAbstractScan
{
	public function &getFiles($folder)
	{
		$registry =& AEFactory::getConfiguration();
		// Was the breakflag set BEFORE starting? -- This workaround is required due to PHP5 defaulting to assigning variables by reference
		$breakflag_before_process = $registry->get('volatile.breakflag', false);

		// Reset break flag before continuing
		$breakflag = false;

		// Initialize variables
		$arr = array();
		$false = false;

		if(!is_dir($folder) && !is_dir($folder.'/')) return $false;

		$counter = 0;
		$registry =& AEFactory::getConfiguration();
		$maxCounter = $registry->get('engine.scan.smart.large_dir_threshold',100);

		$allowBreakflag = ($registry->get('volatile.operation_counter', 0) != 0) && !$breakflag_before_process;

		$handle = @opendir($folder);
		/* If opening the directory doesn't work, try adding a trailing slash. This is useful in cases
		 * like this: open_basedir=/home/user/www/ and the root is /home/user/www. Trying to scan
		 * /home/user/www results in error, trying to scan /home/user/www/ succeeds. Duh!
		 */
		if ($handle === FALSE) {
			$handle = @opendir($folder.'/');
		}
		// If directory is not accessible, just return FALSE
		if ($handle === FALSE) {
			$this->setWarning( 'Unreadable directory '.$folder);
			return $false;
		}

		while ( (($file = @readdir($handle)) !== false) && (!$breakflag) )
		{
			if (($file != '.') && ($file != '..'))
			{
				// # Fix 2.4.b1: Do not add DS if we are on the site's root and it's an empty string
				// # Fix 2.4.b2: Do not add DS is the last character _is_ DS
				$ds = ($folder == '') || ($folder == '/') || (@substr($folder, -1) == '/') || (@substr($folder, -1) == DIRECTORY_SEPARATOR) ? '' : DIRECTORY_SEPARATOR;
				$dir = $folder . $ds . $file;
				$isDir = is_dir($dir);
				if (!$isDir) {
					$data = _AKEEBA_IS_WINDOWS ? AEUtilFilesystem::TranslateWinPath($dir) : $dir;
					if($data) $arr[] = $data;
				}
			}
			$counter++;
			if($counter >= $maxCounter) $breakflag = $allowBreakflag;
		}
		@closedir($handle);

		// Save break flag status
		$registry->set('volatile.breakflag', $breakflag);

		return $arr;
	}

	public function &getFolders($folder)
	{
		// Was the breakflag set BEFORE starting? -- This workaround is required due to PHP5 defaulting to assigning variables by reference
		$registry =& AEFactory::getConfiguration();
		$breakflag_before_process = $registry->get('volatile.breakflag', false);

		// Reset break flag before continuing
		$breakflag = false;

		// Initialize variables
		$arr = array();
		$false = false;

		if(!is_dir($folder) && !is_dir($folder.'/')) return $false;

		$counter = 0;
		$registry =& AEFactory::getConfiguration();
		$maxCounter = $registry->get('engine.scan.smart.large_dir_threshold',100);

		$allowBreakflag = ($registry->get('volatile.operation_counter', 0) != 0) && !$breakflag_before_process;

		$handle = @opendir($folder);
		/* If opening the directory doesn't work, try adding a trailing slash. This is useful in cases
		 * like this: open_basedir=/home/user/www/ and the root is /home/user/www. Trying to scan
		 * /home/user/www results in error, trying to scan /home/user/www/ succeeds. Duh!
		 */
		if ($handle === FALSE) {
			$handle = @opendir($folder.'/');
		}
		// If directory is not accessible, just return FALSE
		if ($handle === FALSE) {
			$this->setWarning('Unreadable directory '.$folder);
			return $false;
		}

		while ( (($file = @readdir($handle)) !== false) && (!$breakflag) )
		{
			if (($file != '.') && ($file != '..'))
			{
				// # Fix 2.4: Do not add DS if we are on the site's root and it's an empty string
				$ds = ($folder == '') || ($folder == '/') || (@substr($folder, -1) == '/') || (@substr($folder, -1) == DIRECTORY_SEPARATOR) ? '' : DIRECTORY_SEPARATOR;
				$dir = $folder . $ds . $file;
				$isDir = is_dir($dir);
				if ($isDir) {
					$data = _AKEEBA_IS_WINDOWS ? AEUtilFilesystem::TranslateWinPath($dir) : $dir;
					if($data) $arr[] = $data;
				}
			}
			$counter++;
			if($counter >= $maxCounter) $breakflag = $allowBreakflag;
		}
		@closedir($handle);

		// Save break flag status
		$registry->set('volatile.breakflag', $breakflag);

		return $arr;
	}
}
