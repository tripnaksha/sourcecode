<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009-2010 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: tempvars.php 240 2010-08-29 21:18:32Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

/**
 * Temporary variables management class. Everything is stored serialized in an INI
 * file on the temporary directory.
 */
class AEUtilTempvars
{

	/**
	 * Returns the fully qualified path to the storage file
	 * @return unknown_type
	 */
	static public function get_storage_filename($tag = null)
	{
		if(!empty($tag)) {
			$basename = 'akeeba_'.$tag.'.php';
		} else {
			$basename = 'akeeba_storage.php';
		}

		$registry =& AEFactory::getConfiguration();
		return $registry->get('akeeba.basic.temporary_directory').DIRECTORY_SEPARATOR.$basename;
	}

	/**
	 * Resets the storage. This method removes all stored values.
	 * @return	bool	True on success
	 */
	public static function reset($tag = null)
	{
		static $storage_filename = null;
		if(empty($storage_filename) || !empty($tag))
		{
			$storage_filename = AEUtilTempvars::get_storage_filename($tag);
		}

		return @unlink($storage_filename);
	}

	public static function set(&$value, $tag = null)
	{
		static $storage_filename = null;
		static $temporary_storage_filename = null;
		if(empty($storage_filename) || !empty($tag))
		{
			$storage_filename = AEUtilTempvars::get_storage_filename($tag);
		}

		// Remove old file (if exists)
		if(file_exists($storage_filename)) @unlink($storage_filename);

		// Open the new file
		$fp = @fopen($storage_filename, 'wb');
		if( $fp === false ) return false;

		// Add a header
		fputs($fp, "<?php die('Access denied'); ?>\n");
		fwrite($fp, self::encode($value));
		fclose($fp);

		return true;
	}

	public static function &get($tag = null)
	{
		static $storage_filename = null;
		if(empty($storage_filename) || !is_null($tag))
		{
			$storage_filename = AEUtilTempvars::get_storage_filename($tag);
		}

		$ret = false;

		// Open the file
		$fp = @fopen($storage_filename, 'rb');
		if( $fp === false ) return $ret;

		// Throw away the first line; it's just a php header to deter web access
		$ret = @fgets($fp);
		// The next line is what I need
		$ret = @fread($fp, filesize($storage_filename) );
		if($ret !== false)
		{
			$ret = self::decode($ret);
		}

		fclose($fp);
		return $ret;
	}

	public static function encode(&$data)
	{
		// Should I base64-encode?
		if( function_exists('base64_encode') && function_exists('base64_decode') ) {
			return base64_encode($data);
		} elseif( function_exists('convert_uuencode') && function_exists('convert_uudecode') ) {
			return convert_uuencode($data);
		} else return $data;
	}

	public static function decode(&$data)
	{
		if( function_exists('base64_encode') && function_exists('base64_decode') ) {
			return base64_decode($data);
		} elseif( function_exists('convert_uuencode') && function_exists('convert_uudecode') ) {
			return convert_uudecode($data);
		} else return $data;
	}
}