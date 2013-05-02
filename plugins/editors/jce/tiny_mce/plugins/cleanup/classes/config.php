<?php
/**
* @version		$Id: config.php 48 2009-05-27 10:46:36Z happynoodleboy $
* @package      JCE
* @copyright    Copyright (C) 2005 - 2009 Ryan Demmer. All rights reserved.
* @author		Ryan Demmer
* @license      GNU/GPL
* JCE is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/
class CleanupConfig 
{
	function getConfig(&$vars)
	{
		$jce =& JContentEditor::getInstance();
		$vars['cleanup_pluginmode'] = $jce->getEditorParam('cleanup_pluginmode', 0, 0);
		$vars['cleanup_keep_nbsp'] 	= $jce->getEditorParam('cleanup_keep_nbsp', 1, 1);
	}
}
?>