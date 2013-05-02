<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: escape.php 196 2010-07-25 13:39:31Z nikosdion $
 * @since 3.0.1
 */

defined('_JEXEC') or die('Restricted access');

class AkeebaHelperEscape
{

	/**
	 * Escapes a string gotten from JText::_() for use with Javascript
	 * @param $string string The string to escape
	 * @param $extras string The characters to escape
	 * @return string
	 */
	static function escapeJS($string, $extras = '')
	{
		static $gpc = null;

		if(is_null($gpc))
		{
			if(function_exists('magic_quotes_gpc')) {
				$gpc = magic_quotes_gpc();
			} else {
				$gpc = false;
			}
		}

		if(empty($extras)) $extras = "'\\";
		
		if($gpc) {
			$string = stripslashes($string);
		}
		return addcslashes($string, $extras);
	}
}
